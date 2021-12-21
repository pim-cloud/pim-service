<?php
declare(strict_types=1);

namespace App\Controller;

use App\Exception\BusinessException;
use Hyperf\Contract\ConfigInterface;
use App\Middleware\Auth\AuthMiddleware;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\PostMapping;

/**
 * @Controller(prefix="file")
 * @Middleware(AuthMiddleware::class)
 */
class FileController extends AbstractController
{
    /**
     * @PostMapping(path="upload")
     */
    public function upload(\Hyperf\Filesystem\FilesystemFactory $factory, ConfigInterface $config)
    {
        $fileObj = $this->request->file('file');

        if (!$fileObj) {
            throw new BusinessException('上传文件key错误');
        }
        if (!$config->has('file.storage.qiniu.domain')) {
            throw new BusinessException('请配置七牛云cdn地址');
        }

        //目前支持的媒体类型
        $type = ['image/png', 'image/gif', 'image/jpeg'];

        if (in_array($fileObj->getClientMediaType(), $type)) {

            if (!isset(pathinfo($fileObj->getClientFilename())['extension'])) {
                throw new  BusinessException('获取图片后缀失败');
            }
            //后缀
            $extension = pathinfo($fileObj->getClientFilename())['extension'];
            //读文件
            $files = file_get_contents($fileObj->getRealPath());
            //获取七牛云驱动
            $qiniu = $factory->get('qiniu');
            //组装图片名
            $filename = md5($files) . '.' . $extension;

            if (!$qiniu->fileExists($filename)) {
                //上传图片
                $qiniu->write($filename, $files);
            }

            return $this->apiReturn(['path' => config('file.storage.qiniu.domain') . '/' . $filename]);
        }
        throw new BusinessException('暂不支持该类型文件');
    }
}