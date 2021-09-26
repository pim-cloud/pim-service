<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property string $group_number 
 * @property string $group_name 
 * @property string $group_head_image 
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

    protected $primaryKey = 'group_number';

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
    protected $casts = ['member_num' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}