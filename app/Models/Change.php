<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Change extends Model
{
    protected $primaryKey = 'cid';

    protected $fillable = [
        'hid', 'lid', 'oid', 'uid', 'before_tid', 'after_tid', 'before_rid', 'after_rid', 'change_price', 'before_price', 'after_price', 'remark', 'time'
    ];

    protected $guarded = [];

    public $timestamps = false;

    public function toCreate($input)
    {
        return Change::create([
            'hid' => $input['hid'],
            'lid' => $input['lid'],
            'oid' => $input['oid'],
            'uid' => $input['uid'],
            'before_tid' => $input['before_tid'],
            'after_tid' => $input['after_tid'],
            'before_rid' => $input['before_rid'],
            'after_rid' => $input['after_rid'],
            'change_price' => $input['change_price'],
            'before_price' => $input['before_price'],
            'after_price' => $input['after_price'],
            'remark' => $input['remark'],
            'time' => time(),
        ]);
    }
}