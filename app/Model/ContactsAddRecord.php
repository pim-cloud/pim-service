<?php

declare (strict_types=1);

namespace App\Model;

/**
 * @property int $record_id
 * @property string $main_code
 * @property string $accept_code
 * @property string $remarks
 * @property string $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ContactsAddRecord extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'contacts_add_record';

    protected $primaryKey = 'record_id';

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
    protected $casts = ['created_at' => 'datetime', 'updated_at' => 'datetime'];

    /**
     * 查询一条申请记录
     * @param string $mainCode
     * @param string $acceptCode
     * @return mixed
     */
    public static function record(string $mainCode, string $acceptCode)
    {
        return ContactsAddRecord::where('main_code', $mainCode)->where('accept_code', $acceptCode)->first();
    }
}