<?php
declare (strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Db;
use Hyperf\ModelCache\Cacheable;
use Hyperf\ModelCache\CacheableInterface;

/**
 * @property int $id
 * @property string $main_uid
 * @property string $friend_uid
 * @property string $remarks
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ContactsFriend extends Model implements CacheableInterface
{
    use Cacheable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'contacts_friend';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];


    /**
     * 查询一条联系人
     * @param string $mainUid
     * @param string $friendUid
     * @return ContactsFriend
     */
    public static function contacts(string $mainUid, string $friendUid): ContactsFriend
    {
        return ContactsFriend::where('main_uid', $mainUid)->where('friend_uid', $friendUid)->first();
    }

    /**
     * 是否双向好友，返回好友信息
     * @param string $mainUid
     * @param string $friendUid
     * @return bool
     */
    public static function doubleFriend(string $mainUid, string $friendUid): bool
    {
        $main = ContactsFriend::contacts($mainUid, $friendUid);
        $friend = ContactsFriend::contacts($friendUid, $mainUid);
        return $main && $friend ? true : false;
    }

    /**
     * 双向好友删除
     * @param string $mainUid
     * @param string $friendUid
     * @return bool
     */
    public static function doubleDelete(string $mainUid, string $friendUid): bool
    {
        return Db::transaction(function ($mainUid, $friendUid) {
            Db::table($this->table)->where('main_uid', $mainUid)
                ->where('friend_uid', $friendUid)
                ->delete();
            Db::table($this->table)->where('main_uid', $friendUid)
                ->where('friend_uid', $mainUid)
                ->delete();
        });
    }

    /**
     * 查询联系人详情
     * @param string $mainUid
     * @param string $friendUid
     * @return array
     */
    public static function friendDetail(string $mainUid, string $friendUid): array
    {
        $contactsInfo = ContactsFriend::contacts($mainUid, $friendUid);
        if (!$contactsInfo) {
            return [];
        }
        $member = Member::findFromCache($contactsInfo->friend_uid);
        if (!$member) {
            return [];
        }
        $member->remarks = $contactsInfo->remarks;
        $member->head_image = picturePath($member->head_image);
        return $member->toArray();
    }
}