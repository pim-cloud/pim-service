<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property string $msg_id
 * @property string $content 
 * @property string $send_uid 
 * @property string $accept_type
 * @property string $accept_uid
 * @property string $content_type
 * @property string $created_at
 * @property string $updated_at
 */
class Message extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'message';
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