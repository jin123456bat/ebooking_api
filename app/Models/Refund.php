<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    protected $primaryKey = 'rid';

    protected $fillable = [
        'hid', 'oid', 'pid', 'pay', 'refunds', 'remark', 'time',
    ];

    protected $guarded = [];

    public function toInsert($input)
    {
        return Refund::insert($input);
    }
}