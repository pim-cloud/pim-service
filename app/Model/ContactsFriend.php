<?php
declare (strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Db;
use Hyperf\ModelCache\Cacheable;
use Hyperf\ModelCache\CacheableInterface;

/**
 * @property int $id
 * @property string $main_code
 * @property string $accept_code
 * @property string $remarks
 * @property int $topping
 * @property int $disturb
 * @property int $star
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
    protected $casts = ['id' => 'integer', 'config' => 'json', 'created_at' => 'datetime', 'updated_at' => 'datetime'];


    /**
     * 创建一条好友关系信息
     * @param string $mainCode
     * @param string $acceptCode
     * @return ContactsFriend|\Hyperf\Database\Model\Model
     */
    public static function createA(string $mainCode, string $acceptCode)
    {
        return ContactsFriend::create(['main_code' => $mainCode, 'accept_code' => $acceptCode]);
    }

    /**
     * 查询一条联系人
     * @param string $mainCode
     * @param string $acceptCode
     * @return mixed
     */
    public static function contacts(string $mainCode, string $acceptCode)
    {
        return ContactsFriend::where('main_code', $mainCode)->where('accept_code', $acceptCode)->first();
    }

    /**
     * 是否双向好友，返回好友信息
     * @param string $mainCode
     * @param string $acceptCode
     * @return bool
     */
    public static function doubleFriend(string $mainCode, string $acceptCode): bool
    {
        $main = ContactsFriend::contacts($mainCode, $acceptCode);
        $friend = ContactsFriend::contacts($acceptCode, $mainCode);
        return $main && $friend ? true : false;
    }

    /**
     * 双向好友删除
     * @param string $mainCode
     * @param string $acceptCode
     * @return bool
     */
    public static function doubleDelete(string $mainCode, string $acceptCode): bool
    {
        return Db::transaction(function ($mainCode, $acceptCode) {
            Db::table($this->table)->where('main_uid', $mainCode)
                ->where('accept_code', $acceptCode)
                ->delete();
            Db::table($this->table)->where('main_uid', $acceptCode)
                ->where('accept_code', $mainCode)
                ->delete();
        });
    }

    /**
     * 查询联系人详情
     * @param string $mainCode
     * @param string $acceptCode
     * @return array
     */
    public static function friendDetail(string $mainCode, string $acceptCode)
    {
        $contactsInfo = ContactsFriend::contacts($mainCode, $acceptCode);
        if (!$contactsInfo) {
            return [];
        }
        $member = Member::findFromCache($contactsInfo->accept_code);
        if (!$member) {
            return [];
        }
        $member->remarks = $contactsInfo->remarks;
        $member->head_image = picturePath($member->head_image);

        return $member;
    }
}