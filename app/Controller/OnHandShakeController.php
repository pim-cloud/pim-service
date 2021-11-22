<?php

namespace App\Controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\OnOpenInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Dispatcher\HttpDispatcher;
use Hyperf\ExceptionHandler\ExceptionHandlerDispatcher;
use Hyperf\HttpMessage\Server\Request as Psr7Request;
use Hyperf\HttpServer\Contract\CoreMiddlewareInterface;
use Hyperf\HttpServer\ResponseEmitter;
use Hyperf\Server\Event;
use Hyperf\Server\Server as AsyncStyleServer;
use Hyperf\WebSocketServer\Exception\WebSocketHandeShakeException;
use Hyperf\WebSocketServer\Sender;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Qbhy\HyperfAuth\AuthManager;
use Swoole\Http\Request;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response;
use Hyperf\Utils\Context;
use Hyperf\Server\ServerManager;
use Swoole\Http\Response as SwooleResponse;
use Swoole\Server as SwooleServer;
use Swoole\WebSocket\CloseFrame;
use Hyperf\WebSocketServer\Security;
use Hyperf\Utils\Coordinator\Constants;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\HttpServer\MiddlewareManager;
use Hyperf\Contract\OnHandShakeInterface;
use Hyperf\WebSocketServer\CoreMiddleware;
use Hyperf\WebSocketServer\Context as WsContext;
use Hyperf\Utils\Coordinator\CoordinatorManager;
use Hyperf\WebSocketServer\Collector\FdCollector;
use Hyperf\HttpMessage\Server\Response as Psr7Response;
use Swoole\WebSocket\Server as WebSocketServer;

class OnHandShakeController implements OnHandShakeInterface
{

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var HttpDispatcher
     */
    protected $dispatcher;

    /**
     * @var ExceptionHandlerDispatcher
     */
    protected $exceptionHandlerDispatcher;

    /**
     * @var CoreMiddlewareInterface
     */
    protected $coreMiddleware;

    /**
     * @var array
     */
    protected $exceptionHandlers;

    /**
     * @var ResponseEmitter
     */
    protected $responseEmitter;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    protected $middlewares = [];

    /**
     * @var string
     */
    protected $serverName = 'websocket';

    /**
     * @var null|\Swoole\Coroutine\Http\Server|WebSocketServer
     */
    protected $server;


    /**
     * @Inject
     * @var AuthManager
     */
    protected $auth;

    public function __construct(
        ContainerInterface $container,
        HttpDispatcher $dispatcher,
        ExceptionHandlerDispatcher $exceptionHandlerDispatcher,
        ResponseEmitter $responseEmitter,
        StdoutLoggerInterface $logger
    )
    {
        $this->container = $container;
        $this->dispatcher = $dispatcher;
        $this->exceptionHandlerDispatcher = $exceptionHandlerDispatcher;
        $this->responseEmitter = $responseEmitter;
        $this->logger = $logger;
    }

    /**
     * @return \Swoole\Coroutine\Http\Server|WebSocketServer
     */
    public function getServer()
    {
        if ($this->server) {
            return $this->server;
        }
        $config = $this->container->get(ConfigInterface::class);

        $type = $config->get('server.type', AsyncStyleServer::class);

        if ($type === AsyncStyleServer::class) {
            return $this->container->get(SwooleServer::class);
        }

        [, $server] = ServerManager::get($this->serverName);

        return $this->server = $server;
    }

    public function getSender(): Sender
    {
        return $this->container->get(Sender::class);
    }

    public function onHandShake(Request $request, Response $response): void
    {
        try {
            if (is_null($request->server['query_string'])) {
                $this->logger->warning('WebSocket hande shake failed, because the token null');
                return;
            }
            $member = $this->auth->getPayload($request->server['query_string']);
            if (isset($member['exp']) && $member['exp'] <= time()) {
                $this->logger->warning('WebSocket hande shake failed, because the token be overdue');
                return;
            }
            CoordinatorManager::until(Constants::WORKER_START)->yield();
            $fd = $request->fd;
            Context::set(WsContext::FD, $fd);
            $security = $this->container->get(Security::class);

            $psr7Request = $this->initRequest($request);
            $psr7Response = $this->initResponse();

            $this->logger->debug(sprintf('WebSocket: fd[%d] start a handshake request.', $fd));

            $key = $psr7Request->getHeaderLine(Security::SEC_WEBSOCKET_KEY);
            if ($security->isInvalidSecurityKey($key)) {
                throw new WebSocketHandeShakeException('sec-websocket-key is invalid!');
            }

            $psr7Request = $this->coreMiddleware->dispatch($psr7Request);
            $middlewares = $this->middlewares;
            /** @var Dispatched $dispatched */
            $dispatched = $psr7Request->getAttribute(Dispatched::class);
            if ($dispatched->isFound()) {
                $registeredMiddlewares = MiddlewareManager::get($this->serverName, $dispatched->handler->route, $psr7Request->getMethod());
                $middlewares = array_merge($middlewares, $registeredMiddlewares);
            }

            /** @var \Hyperf\HttpMessage\Base\Response $psr7Response */
            $psr7Response = $this->dispatcher->dispatch($psr7Request, $middlewares, $this->coreMiddleware);

            $class = $psr7Response->getAttribute(CoreMiddleware::HANDLER_NAME);

            if (empty($class)) {
                $this->logger->warning('WebSocket hande shake failed, because the class does not exists.');
                return;
            }

            FdCollector::set($fd, $class);
            $server = $this->getServer();
            if ($server instanceof \Swoole\Coroutine\Http\Server) {
                $response->upgrade();
                $this->getSender()->setResponse($fd, $response);
                $this->deferOnOpen($request, $class, $response);

                [, , $callbacks] = ServerManager::get($this->serverName);

                [$onMessageCallbackClass, $onMessageCallbackMethod] = $callbacks[Event::ON_MESSAGE];
                $onMessageCallbackInstance = $this->container->get($onMessageCallbackClass);

                [$onCloseCallbackClass, $onCloseCallbackMethod] = $callbacks[Event::ON_CLOSE];
                $onCloseCallbackInstance = $this->container->get($onCloseCallbackClass);

                while (true) {
                    $frame = $response->recv();
                    if ($frame === false || $frame instanceof CloseFrame || $frame === '') {
                        wait(static function () use ($onCloseCallbackInstance, $onCloseCallbackMethod, $response, $fd) {
                            $onCloseCallbackInstance->{$onCloseCallbackMethod}($response, $fd, 0);
                        });
                        break;
                    }

                    wait(static function () use ($onMessageCallbackInstance, $onMessageCallbackMethod, $response, $frame) {
                        $onMessageCallbackInstance->{$onMessageCallbackMethod}($response, $frame);
                    });
                }
            } else {
                $this->deferOnOpen($request, $class, $server);
            }
        } catch (\Throwable $throwable) {
            // Delegate the exception to exception handler.
            $psr7Response = $this->exceptionHandlerDispatcher->dispatch($throwable, $this->exceptionHandlers);
            FdCollector::del($request->fd);
            WsContext::release($request->fd);
        } finally {
            isset($fd) && $this->getSender()->setResponse($fd, null);
            // Send the Response to client.
            if (!isset($psr7Response) || !$psr7Response instanceof Psr7Response) {
                return;
            }
            $this->responseEmitter->emit($psr7Response, $response, true);
        }
    }


    /**
     * @param SwooleResponse|WebSocketServer $server
     */
    protected function deferOnOpen(SwooleRequest $request, string $class, $server)
    {
        $onOpen = function () use ($request, $class, $server) {
            $instance = $this->container->get($class);
            if ($instance instanceof OnOpenInterface) {
                $instance->onOpen($server, $request);
            }
        };

        if ($server instanceof SwooleResponse) {
            wait($onOpen);
        } else {
            defer($onOpen);
        }
    }


    /**
     * Initialize PSR-7 Request.
     */
    protected function initRequest(SwooleRequest $request): ServerRequestInterface
    {
        Context::set(ServerRequestInterface::class, $psr7Request = Psr7Request::loadFromSwooleRequest($request));
        WsContext::set(ServerRequestInterface::class, $psr7Request);
        return $psr7Request;
    }

    /**
     * Initialize PSR-7 Response.
     */
    protected function initResponse(): ResponseInterface
    {
        Context::set(ResponseInterface::class, $psr7Response = new Psr7Response());
        return $psr7Response;
    }
}