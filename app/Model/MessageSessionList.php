<?php

declare (strict_types=1);

namespace App\Model;

use App\Exception\BusinessException;

/**
 * @property int $session_id
 * @property string $session_type
 * @property string $main_code
 * @property string $accept_code
 * @property string $disturb
 * @property string $online
 * @property int $unread
 * @property string $topping
 * @property string $last_message
 * @property string $last_message_type
 * @property string $last_time
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class MessageSessionList extends Model
{

    protected $primaryKey = 'session_id';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'message_session_list';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'session_type',
        'main_code',
        'accept_code',
        'disturb',
        'online',
        'unread',
        'topping',
        'last_message',
        'last_time',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['session_id' => 'integer', 'unread' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    /**
     * 设置会话
     * @param int $sessionId
     * @param string $field
     * @param $value
     * @throws BusinessException
     */
    public static function setSession(int $sessionId, $field, $value)
    {
        try {
            $session = MessageSessionList::find($sessionId);
            if ($session) {
                $session->$field = $value;
                $session->save();
            }
            return;
        } catch (\Exception $e) {
            throw new BusinessException($e->getMessage());
        }
    }

}