<?php

declare (strict_types=1);

namespace App\Model;

use Hyperf\ModelCache\Cacheable;
use Hyperf\Snowflake\Concern\Snowflake;
use Hyperf\ModelCache\CacheableInterface;

/**
 * @property string $uid
 * @property string $username
 * @property string $email
 * @property string $password
 * @property string $head_image
 * @property string $nikename
 * @property string $salt
 * @property string $autograph
 * @property string $create_at
 * @property string $updated_at
 */
class Member extends Model implements CacheableInterface
{
    use Cacheable;

    use Snowflake;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'member';

    protected $primaryKey = 'uid';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];
}