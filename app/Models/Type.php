<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class Type extends Model
{
    protected $primaryKey = 'tid';

    protected $fillable = [
        'hid', 'name', 'vr', 'tags', 'pictures', 'area', 'width', 'window', 'bed', 'people', 'remark', 'description', 'breakfast', 'code', 'discount', 'time', 'sort'
    ];

    public $timestamps = false;

    public function toCreate($input)
    {
        return Type::create([
            'hid' => $input['hid'],
            'name' => $input['name'],
            'vr' => $input['vr'],
            'tags' => $input['tags'],
            'pictures' => $input['pictures'],
            'area' => $input['area'],
            'width' => $input['width'],
            'window' => $input['window'],
            'bed' => $input['bed'],
            'people' => $input['people'],
            'remark' => $input['remark'],
            'description' => $input['description'],
            'breakfast' => $input['breakfast'],
            'code' => $input['code'],
            'discount' => $input['discount'],
            'time' => time(),
        ]);
    }

    public function toDelete($tid, $hid)
    {
        return Type::where('tid', $tid)
            ->where('hid', $hid)
            ->delete();
    }

    public function toUpdate($input)
    {
        return DB::update('UPDATE types SET tid = ?, `name` = ?, vr = ?, tags = ?, pictures = ?, area = ?, width = ?, window = ?, bed = ?, people = ?, remark = ?, description = ?, breakfast = ?, code = ?, discount = ? WHERE tid = ? AND hid = ?', [
            $input['tid'], $input['name'], $input['vr'], $input['tags'], $input['pictures'], $input['area'], $input['width'], $input['window'], $input['bed'], $input['people'], $input['remark'], $input['description'], $input['breakfast'], $input['code'], $input['discount'], $input['tid'], $input['hid']
        ]);
    }

    public function toUpdateStatus($hid, $tid)
    {
        return DB::update('UPDATE types SET `status` = !`status` WHERE tid = ? AND hid = ?', [$tid, $hid]);
    }

    public function toUpdateSort($hid, $tid, $sort)
    {
        return DB::update('UPDATE types SET `sort` = ? WHERE tid = ? AND hid = ?', [$sort, $tid, $hid]);
    }

    public function toFind($tid, $hid)
    {
        $result = DB::select('SELECT `tid`, `name`, `vr`, `tags`, `pictures`, `area`, `width`, `window`, `bed`, `people`, `remark`, `description`, `breakfast`, `code`, `discount`, `sort` FROM `types` WHERE tid = ? AND hid = ? LIMIT 1', [$tid, $hid]);
        return head($result);
    }

    public function toGet($hid)
    {
        return DB::select('SELECT `tid`,`name`, `vr`, `tags`, `pictures`, `area`, `width`, `window`, `bed`, `people`, `stock`, `remark`, `breakfast`, `code`, `discount`, `sort` FROM `types` WHERE hid = ? AND `status` = 1 ORDER BY `sort` DESC, `tid` ASC', [$hid]);
    }

    public function toList($hid)
    {
        return DB::select('SELECT `tid`,`name`, `stock`, `remark`, `status`, `sort` FROM `types` WHERE hid = ?', [$hid]);
    }

    public function toGetAll($hid)
    {
        return Type::select('tid', 'stock')
            ->where('hid', $hid)
            ->get();
    }

    public function upStock($tid)
    {
        Type::where('tid', $tid)
            ->increment('stock', 1);
    }

    public function downStock($tid)
    {
        Type::where('tid', $tid)
            ->decrement('stock', 1);
    }

    public function toCount($hid)
    {
        return Type::where('hid', $hid)
            ->count();
    }

    public function toValidator(Request $request)
    {
        $input = $request->only('name', 'description');

        $validator = Validator::make($input, [
            'name' => 'bail|required|max:20',
            'vr' => 'bail|sometimes|active_url',
            'tags' => 'bail|array',
            'tags.*' => 'bail|required|string',
            'pictures' => 'bail|array',
            'pictures.*' => 'bail|required|active_url',
            'area' => 'bail|integer',
            'description' => 'bail|sometimes|max:1000',
            'remark' => 'bail|sometimes|max:255',
        ], [
            'name.required' => '房型名称不能为空',
            'name.max' => '房型名称最多 20 个字符',
            'vr.active_url' => '房型 VR 链接错误',
            'tags.required' => '房型免费提供服务不能为空',
            'tags.array' => '房型免费提供服务格式错误',
            'tags.*.required' => '房型免费提供服务内容不能为空',
            'tags.*.string' => '房型免费提供服务内容格式错误',
            'pictures.required' => '房型图集不能为空',
            'pictures.array' => '房型图集格式错误',
            'pictures.*.required' => '房型图集内容不能为空',
            'pictures.*.active_url' => '房型图集内容格式错误',
            'area.required' => '房型面积不能为空',
            'area.integer' => '房型面积格式错误',
            'remark.max' => '房型描述最多 255 个字符',
            'description.max' => '房型详细信息最多 1000 个字符',
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

        $num = Type::where('hid', $hid)
            ->where('name', $request->get('name'))
            ->where('tid', '<>', ($isCreate ? 0 : $request->get('tid')))
            ->count();

        if (empty($num)) {
            return false;
        } else {
            return true;
        }

    }
}
