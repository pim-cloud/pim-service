<?php

namespace App\Process;

use App\Redis\OnLine;
use Hyperf\Process\ProcessManager;
use Hyperf\Process\AbstractProcess;

class Clear extends AbstractProcess
{
    public $name = 'clear';

    public function handle(): void
    {
        $online = OnLine::getInstance();

        while (ProcessManager::isRunning()) {
            $data = $online->getAllFields();
            if (!empty($data)) {
                foreach ($data as $k => $v) {
                    //用值 获取 映射的值
                    $vK = $online->getVal($v);

                    if ($k != $vK) {
                        output('清除hash online k=' . $k);
                        $online->clearOnLineMember($k);
                    }
                }
            }
            sleep(60);
        }
    }
}