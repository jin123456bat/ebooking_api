<?php

namespace App\Http\Controllers\Router;

use App\Http\Controllers\Controller;
use App\Models\Build;
use App\Models\Floor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BuildController extends Controller
{
    private $auth;

    public function __construct()
    {
        $this->auth = Auth::user();
    }

    public function get()
    {

        $Build = new Build();

        $count = $Build->toCount($this->auth['hid']);

        $build = null;

        if (!empty($count)) {
            $build = $Build->toGet($this->auth['hid']);
        }

        return response()->json([
            'msg' => '查询成功',
            'data' => $build,
            'err' => 0,
        ]);
    }

    public function line()
    {

        $Build = new Build();

        $count = $Build->toCount($this->auth['hid']);

        $build = null;

        if (!empty($count)) {
            $build = $Build->toLine($this->auth['hid']);
        }

        return response()->json([
            'msg' => '查询成功',
            'data' => $build,
            'err' => 0,
        ]);
    }

    public function create(Request $request)
    {
        $Build = new Build();

        $validator = $Build->toValidate($request);

        if (!empty($validator)) {
            return $validator;
        }

        if ($Build->toUnique($request, $this->auth['hid'], true)) {
            return [
                'msg' => '楼栋信息已存在',
                'err' => 422,
            ];
        }

        $input['name'] = $request->get('name');
        $input['coordinate'] = $request->get('coordinate', '');
        $input['description'] = $request->get('description', '');
        $input['hid'] = $this->auth['hid'];

        $build = $Build->toCreate($input);

        if (empty($build)) {
            return [
                'msg' => '楼栋添加失败',
                'err' => 422,
            ];
        }

        return [
            'msg' => '查询成功',
            'err' => 0,
        ];
    }

    public function update(Request $request)
    {

        $Build = new Build();

        $validator = $Build->toValidate($request);
        if (!empty($validator)) {
            return $validator;
        }

        if ($Build->toUnique($request, $this->auth['hid'])) {
            return [
                'msg' => '楼栋信息已存在',
                'err' => 422,
            ];
        }

        $build = $Build->toFind($request->get('bid'), $this->auth['hid']);

        if (empty($build)) {
            return [
                'msg' => '楼栋不存在',
                'err' => 404,
            ];
        }

        $input['name'] = $request->get('name');
        $input['coordinate'] = $request->get('coordinate', '');
        $input['description'] = $request->get('description', '');

        try {

            $Build->toUpdate($build, $input);
        } catch (\Exception $e) {
            return [
                'msg' => '楼栋信息修改失败',
                'err' => 500,
            ];
        }

        return [
            'msg' => '查询成功',
            'err' => 0,
        ];
    }

    public function delete(Request $request)
    {

        $bid = $request->get('bid');

        if (empty($bid)) {

            return [
                'msg' => '楼栋号不能为空',
                'err' => 404,
            ];
        }



        $floor = new Floor();

        $num = $floor->toCountBid($this->auth['hid'], $bid);

        if (!empty($num)) {
            return [
                'msg' => '楼栋下面含有楼层，暂无法删除此楼栋信息',
                'err' => 422,
            ];
        }


        $Build = new Build();

        $num = $Build->toDelete($bid, $this->auth['hid']);

        if (empty($num)) {
            return [
                'msg' => '楼栋删除失败',
                'err' => 422,
            ];
        }

        return [
            'msg' => '查询成功',
            'err' => 0,
        ];
    }
}
