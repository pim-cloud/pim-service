<?php

declare (strict_types=1);

namespace App\Model;

/**
 * @property int $session_id
 * @property string $session_type
 * @property string $uid
 * @property string $accept_uid
 * @property string $disturb_status
 * @property string $topping
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
        'uid',
        'accept_uid',
        'disturb_status',
        'topping',
        'created_at',
        'updated_at',
    ];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['session_id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];


    /**
     * 设置消息免打扰
     * @param string $uid 我的uid
     * @param string $acceptUid 接收者uid
     * @return bool
     */
    public static function saveDisturbStatus(string $uid, string $acceptUid)
    {
        $list = self::query()->where('uid', $uid)
            ->where('accept_uid', $acceptUid)
            ->first(['session_id', 'disturb_status']);
        if ($list->disturb_status === 'yes') {
            $list->disturb_status = 'no';
        } else {
            $list->disturb_status = 'yes';
        }
        return $list->save();
    }
}