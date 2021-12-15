<?php
declare (strict_types=1);

namespace App\Model;

use App\Exception\BusinessException;
use Hyperf\DbConnection\Db;
use Hyperf\ModelCache\Cacheable;
use Hyperf\ModelCache\CacheableInterface;

/**
 * @property int $id
 * @property string $main_code
 * @property string $accept_code
 * @property string $remarks
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
    protected $casts = ['id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];


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
     * @return mixed
     */
    public static function doubleDelete(string $mainCode, string $acceptCode)
    {
        return Db::transaction(function () use ($mainCode, $acceptCode) {
            Db::table('contacts_friend')->where('main_code', $mainCode)
                ->where('accept_code', $acceptCode)
                ->delete();
            Db::table('contacts_friend')->where('main_code', $acceptCode)
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
        $session = MessageSessionList::where('main_code', $mainCode)->where('accept_code', $acceptCode)->first();

        $member->head_image = picturePath($member->head_image);
        $member->id = $contactsInfo->id;
        $member->config = [
            'remarks' => $contactsInfo->remarks,
            'topping' => isset($session->topping) ? $session->topping : 0,
            'disturb' => $contactsInfo->disturb,
            'star' => $contactsInfo->star,
        ];

        return $member;
    }

    /**
     * 修改好友配置信息
     * @param int $id 主键id
     * @param string $field 需要修改的字段
     * @param $value 需要修改的值
     * @throws BusinessException
     */
    public static function setConfig(int $id, string $field, $value)
    {
        try {
            $contacts = ContactsFriend::find($id);
            if ($contacts) {
                $contacts->$field = $value;
                $contacts->save();
            }
            return;
        } catch (\Exception $e) {
            throw new BusinessException($e->getMessage());
        }
    }
}