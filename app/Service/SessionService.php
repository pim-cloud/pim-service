<?php
declare (strict_types=1);

namespace App\Service;

use App\Model\MessageSessionList;

class SessionService
{
    public function sessionEditService(array $params)
    {
        switch ($params['type']) {
            case 'topping':
                MessageSessionList::setSession($params['sessionId'], 'topping', $params['value']);
                break;
            case 'online':

                break;
        }
        return ['code' => 200];
    }
}