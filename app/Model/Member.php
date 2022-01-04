<?php

declare (strict_types=1);

namespace App\Model;

use Hyperf\ModelCache\Cacheable;
use Qbhy\HyperfAuth\Authenticatable;
use Hyperf\Snowflake\Concern\Snowflake;
use Hyperf\ModelCache\CacheableInterface;

/**
 * @property string $code
 * @property string $username
 * @property string $email
 * @property string $password
 * @property string $head_image
 * @property string $nickname
 * @property string $salt
 * @property string $autograph
 * @property string $create_at
 * @property string $updated_at
 */
class Member extends Model implements CacheableInterface, Authenticatable
{
    use Cacheable;

    use Snowflake;

    protected $hidden = ['password', 'salt'];
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'member';

    protected $primaryKey = 'code';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'username',
        'email',
        'password',
        'head_image',
        'nickname',
        'salt',
        'autograph',
    ];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

    public function getId()
    {
        // TODO: Implement getId() method.
        return $this->code;
    }

    public static function retrieveById($key): ?Authenticatable
    {
        // TODO: Implement retrieveById() method.
        return Member::findFromCache($key);
    }

    /**
     * 修改密码
     * @param $code
     * @param $oldP
     * @param $newP
     * @return bool
     */
    public static function saveP($code, $oldP, $newP): bool
    {
        $member = Member::findFromCache($code);

        if ($member->password != md5($oldP . $member->salt)) return false;

        $salt = getSnowflakeId();
        $member->password = md5($newP . $salt);
        $member->salt = $salt;
        return $member->save();
    }
}