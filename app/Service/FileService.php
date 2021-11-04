<?php

declare(strict_types=1);

namespace App\Service;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Contract\ConfigInterface;
use App\Exception\BusinessException;
use Hyperf\Filesystem\FilesystemFactory;

class FileService
{

    /**
     * @Inject
     * @var FilesystemFactory
     */
    private $factory;

    /**
     * @Inject
     * @var ConfigInterface
     */
    private $config;


    /**
     * 上传图片
     * @param $file
     * @return array
     * @throws BusinessException
     */
    public function picture($file)
    {
        if (!$this->config->has('file.storage.qiniu.domain')) {
            throw new BusinessException('请配置七牛云cdn地址');
        }

        if (!isset(pathinfo($file->getClientFilename())['extension'])) {
            throw new  BusinessException('获取图片后缀失败');
        }
        //后缀
        $extension = pathinfo($file->getClientFilename())['extension'];
        //读文件
        $files = file_get_contents($file->getRealPath());
        //获取七牛云驱动
        $qiniu = $this->factory->get('qiniu');
        //组装图片名
        $filename = md5($files) . '.' . $extension;

        //不存在则上传
        if (!$qiniu->fileExists($filename)) {
            //上传图片
            $qiniu->write($filename, $files);
        }

        return $filename;
    }
}