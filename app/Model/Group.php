<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property string $code
 * @property string $nickname
 * @property string $head_image
 * @property string $introduction 
 * @property int $member_num 
 * @property string $extra 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 */
class Group extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'group';

    protected $primaryKey = 'code';

    protected $guarded  = [];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['member_num' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}