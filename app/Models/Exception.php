<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Exception extends Model
{

    protected $primaryKey = 'eid';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'key', 'type', 'error', 'request', 'file'
    ];

    protected $guarded = [];

    public function toCreate($input)
    {
        $result = Exception::create([
            'hid' => $input['hid'],
            'type' => $input['type'],
            'file' => $input['file'],
            'error' => $input['error'],
            'request' => $input['request'],
        ]);

        return $result->eid;
    }

    public function toFind($eid)
    {
        $result = DB::select('SELECT `hid`, `type`, `file`, `error`, `request`, `created_at` AS `time` FROM `exceptions` WHERE eid = ? LIMIT 1', [$eid]);
        return head($result);
    }
}
