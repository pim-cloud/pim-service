<?php
declare (strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Db;
use Hyperf\ModelCache\Cacheable;
use Hyperf\ModelCache\CacheableInterface;

/**
 * @property int $id
 * @property string $main_code
 * @property string $friend_code
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
     * @param string $mainCode
     * @param string $friendCode
     * @return mixed
     */
    public static function contacts(string $mainCode, string $friendCode)
    {
        return ContactsFriend::where('main_code', $mainCode)->where('friend_code', $friendCode)->first();
    }

    /**
     * 是否双向好友，返回好友信息
     * @param string $mainCode
     * @param string $friendCode
     * @return bool
     */
    public static function doubleFriend(string $mainCode, string $friendCode): bool
    {
        $main = ContactsFriend::contacts($mainCode, $friendCode);
        $friend = ContactsFriend::contacts($friendCode, $mainCode);
        return $main && $friend ? true : false;
    }

    /**
     * 双向好友删除
     * @param string $mainCode
     * @param string $friendCode
     * @return bool
     */
    public static function doubleDelete(string $mainCode, string $friendCode): bool
    {
        return Db::transaction(function ($mainCode, $friendCode) {
            Db::table($this->table)->where('main_uid', $mainCode)
                ->where('friend_code', $friendCode)
                ->delete();
            Db::table($this->table)->where('main_uid', $friendCode)
                ->where('friend_code', $mainCode)
                ->delete();
        });
    }

    /**
     * 查询联系人详情
     * @param string $mainCode
     * @param string $friendCode
     * @return array
     */
    public static function friendDetail(string $mainCode, string $friendCode)
    {
        $contactsInfo = ContactsFriend::contacts($mainCode, $friendCode);
        if (!$contactsInfo) {
            return [];
        }
        $member = Member::findFromCache($contactsInfo->friend_code);
        if (!$member) {
            return [];
        }
        $member->remarks = $contactsInfo->remarks;
        $member->head_image = picturePath($member->head_image);

        return $member;
    }
}