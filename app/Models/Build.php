<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class Build extends Model
{

    protected $primaryKey = 'bid';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'hid', 'name', 'coordinate', 'description',
    ];

    protected $guarded = [];

    public function toCreate($build)
    {
        return Build::create([
            'hid' => $build['hid'],
            'name' => $build['name'],
            'coordinate' => $build['coordinate'],
            'description' => $build['description'],
        ]);
    }

    public function toUpdate(Build $build, $input)
    {

        $build->name = $input['name'];
        $build->coordinate = $input['coordinate'];
        $build->description = $input['description'];

        $build->save();
    }

    public function toDelete($bid, $hid)
    {
        return Build::where('hid', $hid)
            ->where('bid', $bid)
            ->delete();
    }

    public function toFind($bid, $hid)
    {
        return Build::where('bid', $bid)
            ->where('hid', $hid)
            ->first();
    }

    public function toGet($hid)
    {
        return Build::select('bid', 'name')
            ->where('hid', $hid)
            ->get();
    }

    public function toLine($hid)
    {
        return DB::select('SELECT `bid`, `name`, `coordinate`, `description` FROM `builds` WHERE hid = ?', [$hid]);
    }

    public function toCount($hid)
    {
        return Build::where('hid', $hid)
            ->count('bid');
    }

    public function toValidate(Request $request)
    {
        $input = $request->only('name', 'description');

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

        $num = Build::where('hid', $hid)
            ->where('name', $request->get('name'))
            ->where('bid', '<>', ($isCreate ? 0 : $request->get('bid')))
            ->count();

        if (empty($num)) {
            return false;
        } else {
            return true;
        }

    }
}
