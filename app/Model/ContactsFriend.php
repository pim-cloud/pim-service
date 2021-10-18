<?php
declare (strict_types=1);

namespace App\Model;

use Hyperf\ModelCache\Cacheable;
use Hyperf\ModelCache\CacheableInterface;

/**
 * @property int $id
 * @property string $main_uid
 * @property string $friend_uid
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
     * 是否双向好友，返回好友信息
     * @param string $mainUid
     * @param string $friendUid
     * @return bool
     */
    public static function twoWayFriend(string $mainUid, string $friendUid): bool
    {
        $main = self::where('main_uid', $mainUid)->where('friend_uid', $friendUid)->first();
        $friend = self::where('main_uid', $friendUid)->where('friend_uid', $mainUid)->first();

        if ($main && $friend) {
            return true;
        }
        return false;
    }
}