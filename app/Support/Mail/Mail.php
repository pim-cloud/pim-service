<?php

declare(strict_types=1);

namespace App\Support\Mail;

use App\Exception\MailException;
use PHPMailer\PHPMailer\PHPMailer;

class Mail
{
    public function handler(string $accept, $conf = 'default')
    {
        try {
            if (!isset(config('mail')[$conf])) {
                throw new \Exception('mail config empty' . $conf);
            }
            $config = config('mail')[$conf];
            output($config);
            $mail = new PHPMailer();

            //邮箱服务配置
            $mail->SMTPDebug = $config['mail_smtp_debug'];
            $mail->isSMTP();
            $mail->Host = $config['mail_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['mail_username'];
            $mail->Password = $config['mail_authorization_code'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = $config['mail_port'];
            $mail->From = $config['mail_from_address'];
            $mail->FromName = $config['mail_from_nickname'];
            $mail->isHTML(true);
            $mail->setLanguage('zh_cn');

            $mail->addAddress($accept);//收件人地址
            $mail->Subject = '主题';
            $mail->Body = 'ceshi ';

            if (!$mail->send()) {
                throw new MailException('邮件发送失败:' . $accept);
            }
        } catch (\Exception $e) {
            output($e->getMessage());
        }
    }
}