<?php

namespace App\Http\Controllers\Router;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\Stock;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TypeController extends Controller
{
    private $auth;

    public function __construct()
    {
        $this->auth = Auth::user();
    }

    public function create(Request $request)
    {

        $tags = $request->get('tags');

        if (empty($tags)) {
            return [
                'msg' => '房型免费服务不能为空',
                'err' => 404,
            ];
        }

        $pictures = $request->get('pictures');

        if (empty($pictures)) {
            return [
                'msg' => '房型图集不能为空',
                'err' => 404,
            ];
        }

        $Type = new Type();
        $validator = $Type->toValidator($request);

        if (!empty($validator)) {
            return $validator;
        }

        if ($Type->toUnique($request, $this->auth['hid'], true)) {
            return [
                'msg' => '房型信息已存在',
                'err' => 422,
            ];
        }

        $input['hid'] = $this->auth['hid'];
        $input['name'] = $request->get('name');
        $input['vr'] = $request->get('vr');
        $input['tags'] = implode('|||', $tags);
        $input['pictures'] = implode('|||', $pictures);
        $input['area'] = $request->get('area');
        $input['width'] = $request->get('width');
        $input['window'] = $request->get('window');
        $input['bed'] = $request->get('bed');
        $input['people'] = $request->get('people');
        $input['remark'] = $request->get('remark');
        $input['description'] = $request->get('description', '');
        $input['breakfast'] = json_encode([
            'one' => (int)$request->get('one', 0),
            'two' => (int)$request->get('two', 0),
            'three' => (int)$request->get('three', 0),
        ]);
        $input['code'] = $request->get('code', '');
        $discount = (int)$request->get('discount');
        $input['discount'] = $discount == 1 ? 1 : 0;

        $type = $Type->toCreate($input);

        if (empty($type)) {

            return [
                'msg' => '房型添加失败',
                'err' => 422,
            ];
        }

        return [
            'msg' => '查询成功',
            'err' => 0,
        ];
    }

    public function delete(Request $request)
    {
        $tid = $request->get('tid');

        if (empty($tid)) {
            return [
                'msg' => '房型不能为空',
                'err' => 422,
            ];
        }

        $Room = new Room();

        $num = $Room->toCountTidRoom($this->auth['hid'], $tid);

        if (!empty($num)) {
            return [
                'msg' => '房型下面含有房间，暂无法删除此房型信息',
                'err' => 422,
            ];
        }


        DB::beginTransaction();

        $Type = new Type();

        $num = $Type->toDelete($tid, $this->auth['hid']);

        if (empty($num)) {
            DB::rollBack();
            return [
                'msg' => '房型删除失败',
                'err' => 422,
            ];
        }


        try {

            $Stock = new Stock();

            $Stock->toUpdateStock($this->auth['hid'], $tid);

        } catch (\Exception $e) {

            DB::rollBack();
            return [
                'msg' => '库存更新修改失败',
                'err' => 500,
            ];
        }

        DB::commit();
        return [
            'msg' => '查询成功',
            'err' => 0,
        ];
    }

    public function update(Request $request)
    {

        if (empty($request->get('tid'))) {

            return [
                'msg' => '房型不能为空',
                'err' => 422,
            ];
        }

        $Type = new Type();

        $type = $Type->toFind($request->get('tid'), $this->auth['hid']);

        if (empty($type)) {
            return [
                'msg' => '房型信息不存在',
                'err' => 404,
            ];
        }

        if ($Type->toUnique($request, $this->auth['hid'])) {
            return [
                'msg' => '房型信息已存在',
                'err' => 422,
            ];
        }

        $validator = $Type->toValidator($request);

        if (!empty($validator)) {
            return $validator;
        }

        $tags = $request->get('tags');

        if (!empty($tags)) {
            $input['tags'] = implode('|||', $tags);
        } else {
            $input['tags'] = '';
        }

        $pictures = $request->get('pictures');

        if (!empty($pictures)) {
            $input['pictures'] = implode('|||', $pictures);
        } else {
            $input['pictures'] = '';
        }

        $input['hid'] = $this->auth['hid'];
        $input['tid'] = $request->get('tid');
        $input['name'] = $request->get('name');
        $input['vr'] = $request->get('vr');
        $input['area'] = (int)$request->get('area');
        $input['width'] = $request->get('width');
        $input['window'] = (int)$request->get('window');
        $input['bed'] = (int)$request->get('bed');
        $input['people'] = $request->get('people');
        $input['remark'] = $request->get('remark');
        $input['description'] = $request->get('description', '');
        $input['breakfast'] = json_encode([
            'one' => (int)$request->get('one', 0),
            'two' => (int)$request->get('two', 0),
            'three' => (int)$request->get('three', 0),
        ]);
        $input['code'] = $request->get('code', '');
        $discount = (int)$request->get('discount');
        $input['discount'] = $discount == 1 ? 1 : 0;

        try {

            $Type->toUpdate($input);

        } catch (\Exception $e) {

            return [
                'msg' => '修改失败',
                'err' => 500,
            ];
        }

        return [
            'msg' => '查询成功',
            'err' => 0,
        ];
    }

    public function get()
    {
        $Type = new  Type();

        $count = $Type->toCount($this->auth['hid']);

        $type = null;
        if (!empty($count)) {
            $type = $Type->toGet($this->auth['hid']);

            foreach ($type as $key => $value) {
                if (!empty($value->tags)) {
                    $type[$key]->tags = explode('|||', $value->tags);
                } else {
                    $type[$key]->tags = [];
                }
                if (!empty($value->pictures)) {
                    $type[$key]->pictures = explode('|||', $value->pictures);
                } else {
                    $type[$key]->pictures = [];
                }

                $type[$key]->breakfast = json_decode($value->breakfast);
            }
        }

        return [
            'msg' => '查询成功',
            'data' => $type,
            'err' => 0,
        ];
    }

    public function updateStatus(Request $request)
    {
        $tid = $request->get('tid');

        if (empty($tid)) {
            return [
                'msg' => '房型号不能为空',
                'err' => 404
            ];
        }

        $Type = new Type();

        try {

            $affected = $Type->toUpdateStatus($this->auth->hid, $tid);

            if (empty($affected)) {
                return [
                    'msg' => '房型状态更新失败',
                    'err' => 422
                ];
            }
        } catch (\Exception $e) {
            return [
                'msg' => '房型状态更新失败',
                'err' => 500
            ];
        }

        return [
            'msg' => '查询成功',
            'err' => 0
        ];
    }

    public function updateSort(Request $request)
    {
        $tid = $request->get('tid');

        if (empty($tid)) {
            return [
                'msg' => '房型号不能为空',
                'err' => 404
            ];
        }

        $sort = (int)$request->get('sort');

        if ($sort < 1 || $sort > 99) {
            return [
                'msg' => '超过排序范围',
                'err' => 422
            ];
        }

        $Type = new Type();

        try {

            $affected = $Type->toUpdateSort($this->auth->hid, $tid, $sort);

            if (empty($affected)) {
                return [
                    'msg' => '序号更新失败',
                    'err' => 422
                ];
            }
        } catch (\Exception $e) {
            return [
                'msg' => '序号更新失败',
                'err' => 500
            ];
        }

        return [
            'msg' => '查询成功',
            'err' => 0
        ];
    }

    public function list()
    {
        $Type = new  Type();

        $count = $Type->toCount($this->auth['hid']);

        $type = null;
        if (!empty($count)) {
            $type = $Type->toList($this->auth['hid']);

//            foreach ($type as $key => $value) {
//                if (!empty($value->tags)) {
//                    $type[$key]->tags = explode('|||', $value->tags);
//                } else {
//                    $type[$key]->tags = [];
//                }
//                if (!empty($value->pictures)) {
//                    $type[$key]->pictures = explode('|||', $value->pictures);
//                } else {
//                    $type[$key]->pictures = [];
//                }
//            }
        }

        return [
            'msg' => '查询成功',
            'data' => $type,
            'err' => 0,
        ];
    }

    public function find(Request $request)
    {
        $tid = $request->get('tid');

        if (empty($tid)) {
            return [
                'msg' => '房型号不能为空',
                'err' => 40401,
            ];
        }

        $Type = new Type();

        $type = $Type->toFind($tid, $this->auth['hid']);

        if (empty($type)) {
            return [
                'msg' => '查询成功',
                'err' => 0,
            ];
        }

        $type->tags = explode('|||', $type->tags);
        $type->pictures = explode('|||', $type->pictures);

        foreach ($type->tags as $key => $value) {
            if (empty($value)) {
                unset($type->tags[$key]);
            }
        }

        foreach ($type->pictures as $key => $value) {
            if (empty($value)) {
                unset($type->pictures[$key]);
            }
        }

        $type->breakfast = json_decode($type->breakfast);

        return [
            'msg' => '查询成功',
            'data' => $type,
            'err' => 0,
        ];
    }

    public function init()
    {
        $date = Hotel::toGetTime($this->auth->hid);

        if (empty($date)) {
            $date = date('Ymd');
        }

        $Type = new Type();
        $Stock = new Stock();

        $type = $Type->toGet($this->auth->hid);

        foreach ($type as $val) {

            $stock = $Stock->toCount($val->tid);

            if (empty($stock)) {

                $in = $Stock->init($this->auth->hid, $val->tid, $val->stock, $date);
            } else {

                $stock = $Stock->toFindLast($this->auth->hid, $val->tid);

                $start = $stock->date;
                $in = $Stock->init($this->auth->hid, $val->tid, $val->stock, $start);
            }
            if (!$in) {
                return [
                    'msg' => '库存生成失败',
                    'err' => 422
                ];
            }
        }

        return [
            'msg' => '查询成功',
            'err' => 0
        ];
    }
}