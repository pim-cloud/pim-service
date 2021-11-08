<?php

declare (strict_types=1);

namespace App\Model;

/**
 * @property int $session_id
 * @property string $session_type
 * @property string $uid
 * @property string $accept_uid
 * @property string $disturb_status
 * @property string $on_line_status
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
        'uid',
        'accept_uid',
        'disturb_status',
        'on_line_status',
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

    /**
     * 获取会话列表
     * @param string $uid
     * @return array
     */
    public static function sessionList(string $uid): array
    {
        $session = MessageSessionList::where('uid', $uid)->get();

        $data = [];
        if ($session) {
            foreach ($session as $item) {
                if ($item->session_type === 'group') {
                    $group = Group::findFromCache($item->accept_uid);
                    $nickname = $group->group_name;
                    $headImage = picturePath($group->group_head_image);
                } else {
                    $member = Member::findFromCache($item->accept_uid);
                    $nickname = $member->nickname;
                    $headImage = picturePath($member->head_image);
                }

                $data[] = [
                    'accept_info' => [
                        'nickname' => $nickname,
                        'head_image' => $headImage,
                    ],
                    'topping' => $item->topping,
                    'last_time' => $item->last_time,
                    'session_id' => $item->session_id,
                    'accept_code' => $item->accept_uid,
                    'session_type' => $item->session_type,
                    'last_message' => $item->last_message,
                    'disturb_status' => $item->disturb_status,
                    'last_message_type' => $item->last_message_type,
                ];
            }
        }
        return $data;
    }

    /**
     * 会话置顶
     * @param int $sessionId
     * @return bool
     */
    public static function sessionTop(int $sessionId): bool
    {
        $session = MessageSessionList::find($sessionId);
        if (!$session) {
            return false;
        }
        $topping = $session->topping === 'yes' ? 'no' : 'yes';
        $session->topping = $topping;
        return $session->save();
    }
}