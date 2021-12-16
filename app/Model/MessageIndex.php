<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property string $main_code
 * @property string $accept_code
 * @property int $msg_id
 * @property string $read_state 
 * @property string $created_at
 * @property string $updated_at
 */
class MessageIndex extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'message_index';

    protected $primaryKey = 'msg_id';

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


    /**
     * 一对一 消息索引表关联消息
     * @return \Hyperf\Database\Model\Relations\HasOne
     */
    public function messageOne()
    {
        return $this->hasOne(Message::class,'msg_id','msg_id');
    }

    /**
     * 多对多 消息管理索引
     * @return \Hyperf\Database\Model\Relations\BelongsToMany
     */
    public function messageMany()
    {
        return $this->belongsToMany(Message::class,'msg_id','msg_id');
    }
}