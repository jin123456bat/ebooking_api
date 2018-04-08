<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Company extends Model
{
    protected $primaryKey = 'cid';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'hid', 'name', 'name_pinyin', 'description', 'init', 'start', 'end'
    ];

    protected $guarded = [];

    public function toCreate($input)
    {
        return Company::create([
            'hid' => $input['hid'],
            'name' => $input['name'],
            'name_pinyin' => $input['name_pinyin'],
            'description' => $input['description'],
            'init' => time(),
            'start' => $input['start'],
            'end' => $input['end'],
        ]);
    }

    public function toUpdate($input, $hid, $cid)
    {
        return DB::update('UPDATE companies SET `name` = ?, description = ?, `start` = ?, `end` = ? WHERE hid = ? AND cid = ?',
            [
                $input['name'], $input['description'], $input['start'], $input['end'], $hid, $cid
            ]);
    }

    public function toUpdateStatus($sid, $hid, $cid)
    {
        return DB::update('UPDATE companies SET `status` = ? WHERE hid = ? AND cid = ?',
            [
                $sid, $hid, $cid
            ]);
    }

    public function toFindCompany($cid, $hid)
    {
        return Company::where('cid', $cid)
            ->where('hid', $hid)
            ->first();
    }

    public function toLine($hid)
    {
        return DB::select('SELECT `cid`, `name`, `description`, `init`, `start`, `end`, `status` FROM `companies` WHERE `hid` = ?', [$hid]);
    }

    public function toGet($hid)
    {
        return DB::select('SELECT `cid`, `name` FROM `companies` WHERE `hid` = ? AND `status` = 1', [$hid]);
    }

    public function toSearchPinyin($key, $hid, $date)
    {
        return DB::select('SELECT `cid`,`name` FROM `companies` WHERE (name_pinyin LIKE \'%' . $key . '\' OR name_pinyin LIKE \'' . $key . '%\') AND hid = ? AND status = 0 AND `end` < ?', [$hid, $date]);
    }

    public function toCount($hid)
    {
        $result = DB::select('SELECT COUNT(*) AS `num` FROM `companies` WHERE `hid` = ?', [$hid]);
        $tmp = head($result);
        return $tmp->num;
    }

    public function toUnique($hid, $name, $cid = 0, $isCreate = false)
    {
        $result = DB::select('SELECT COUNT(*) AS `num` FROM `companies` WHERE `hid` = ? AND name = ? AND cid ' . ($isCreate ? "<>" : "=") . ' ?', [$hid, $name, $cid]);
        $tmp = head($result);

        if (empty($tmp->num)) {
            return false;
        } else {
            return true;
        }
    }
}