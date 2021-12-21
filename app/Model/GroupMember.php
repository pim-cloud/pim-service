<?php

declare (strict_types=1);

namespace App\Model;

/**
 * @property int $id
 * @property string $code
 * @property string $m_code
 * @property string $type
 * @property string $extra
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class GroupMember extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'group_member';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['code', 'm_code', 'type', 'extra'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}