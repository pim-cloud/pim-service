[comment]: <> ([English]&#40;./README.md&#41; | 中文)

<p align="center"><a href="https://jksusu.cn" target="_blank" rel="noopener noreferrer"><img width="75" height="70" src="http://cdn.jksusu.cn/logo.jpg"></a></p>

[comment]: <> (<p align="center">)

[comment]: <> (  <a href="https://github.com/pim-cloud/pim-web/releases"><img src="https://poser.pugx.org/pim-cloud/pim-web/v/stable"></a>)

[comment]: <> (  <a href="https://www.php.net"><img src="https://img.shields.io/badge/php-%3E=7.4-brightgreen.svg?maxAge=2592000"></a>)

[comment]: <> (  <a href="https://github.com/swoole/swoole-src"><img src="https://img.shields.io/badge/swoole-%3E=4.5-brightgreen.svg?maxAge=2592000"></a>)

[comment]: <> (  <a href="https://github.com/pim-cloud/pim-web/blob/main/LICENSE"><img src=""></a>)

[comment]: <> (</p>)

# pim-service

> 后端基于 hyperf2.2* swoole4.6*

<a href='http://jksusu.cn/passport/login' target="_blank">点我在线体验</a>

## 快速开始

### 运行完整的pim系统

```bash
docker-compose 方式运行
1. docker-compose  -v         检查环境
2. docker-compose  up         启动系统
3. 创建数据库im导入 pim.sql文件  访问80端口
```
```bash
下载源代码运行
1. git clone https://github.com/pim-cloud/pim-service.git 下载后端代码
2. composer install                                       安装依赖
3. php bin/hyperf.php start                               启动后端服务(需要自己配置mysql,redis)
4. https://github.com/pim-cloud/pim-web.git               下载前端代码
5. npm install                                            安装依赖
6. npm run serve                                          启动（需要配置后端http，websocket地址）
```
### 下载最新的 pim-service 后端代码

```bash
git clone https://github.com/pim-cloud/pim-service.git
```

### 镜像维护地址

```
docker pull jksusu/pim-service  后端镜像下载  
docker pull jksusu/pim-web      前端镜像下载 
docker pull mysql:5.7.29        mysql镜像下载
docker pull redis:6.0.6         redis镜像下载
```

### 开源协议

> pim-web 是一个基于 Apache2.0 协议 开源的软件。
