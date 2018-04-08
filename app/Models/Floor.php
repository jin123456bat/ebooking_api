<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class Floor extends Model
{

    protected $primaryKey = 'fid';

    protected $fillable = [
        'hid', 'bid', 'name', 'description',
    ];

    protected $guarded = [];

    public function toCreate($floor)
    {
        return Floor::create([
            'hid' => $floor['hid'],
            'bid' => $floor['bid'],
            'name' => $floor['name'],
            'description' => $floor['description'],
        ]);
    }

    public function toDelete($fid, $hid)
    {
        return Floor::where('hid', $hid)
            ->where('fid', $fid)
            ->delete();
    }

    public function toUpdate(Floor $floor, $input)
    {

        $floor->bid = $input['bid'];
        $floor->name = $input['name'];
        $floor->description = $input['description'];

        $floor->save();
    }

    public function toGet($hid)
    {
        return Floor::select('fid', 'floors.hid', 'floors.bid', 'builds.name as building', 'floors.name', 'floors.description')
            ->leftJoin('builds', 'floors.bid', '=', 'builds.bid')
            ->where('floors.hid', $hid)
            ->get();
    }

    public function toFind($hid, $fid)
    {
        return Floor::where('hid', $hid)
            ->where('fid', $fid)
            ->first();
    }

    public function toCountBid($hid, $bid)
    {
        $result = DB::select('SELECT COUNT(*) AS num FROM floors WHERE bid = ? AND hid = ? LIMIT 1', [$bid, $hid]);
        return $result[0]->num;
    }

    public function toCount($hid)
    {
        return Floor::where('hid', $hid)
            ->count();
    }

    public function toValidator(Request $request)
    {
        $input = $request->only('bid', 'name', 'description');

        $validator = Validator::make($input, [
            'name' => 'bail|required|max:12',
            'description' => 'bail|sometimes|max:60',
        ], [
            'name.required' => '楼栋名称不能为空',
            'name.max' => '楼栋名称最多 12 个字符',
            'description.max' => '楼层描述最多 60 个字符',
        ]);

        if ($validator->fails()) {

            return [
                'msg' => $validator->errors()->first(),
                'err' => 422,
            ];
        }
    }

    public function toUnique(Request $request, $hid, $isCreate = false)
    {

        $num = Floor::where('hid', $hid)
            ->where('bid', $request->get('bid', 0))
            ->where('name', $request->get('name'))
            ->where('fid', '<>', ($isCreate ? 0 : $request->get('fid')))
            ->count();

        if (empty($num)) {
            return false;
        } else {
            return true;
        }

    }
}
