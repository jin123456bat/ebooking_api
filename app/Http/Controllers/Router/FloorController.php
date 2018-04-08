<?php

namespace App\Http\Controllers\Router;

use App\Http\Controllers\Controller;
use App\Models\Build;
use App\Models\Floor;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FloorController extends Controller
{
    private $auth;

    public function __construct()
    {
        $this->auth = Auth::user();
    }

    public function get()
    {
        $Floor = new Floor();

        $count = $Floor->toCount($this->auth['hid']);

        $floor = null;

        if (!empty($count)) {

            $floor = $Floor->toGet($this->auth['hid']);
        }

        return [
            'msg' => '查询成功',
            'data' => $floor,
            'err' => 0,
        ];
    }

    public function create(Request $request)
    {

        $Floor = new Floor();

        $validator = $Floor->toValidator($request);

        if (!empty($validator)) {
            return $validator;
        }

        $Build = new Build();

        $build = $Build->toFind($request->get('bid'), $this->auth['hid']);

        if (empty($build)) {
            return [
                'msg' => '楼层信息不存在',
                'err' => 404,
            ];
        }

        if ($Floor->toUnique($request, $this->auth['hid'], true)) {
            return [
                'msg' => '楼层信息已存在',
                'err' => 422,
            ];
        }

        $input['hid'] = $this->auth['hid'];
        $input['bid'] = $request->get('bid');
        $input['name'] = $request->get('name');
        $input['description'] = $request->get('description', '');

        $floor = $Floor->toCreate($input);

        if (empty($floor)) {

            return [
                'msg' => '楼层添加失败',
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

        if (empty($request->get('bid'))) {

            return [
                'msg' => '楼栋号不能为空',
                'err' => 422,
            ];
        }

        $Floor = new Floor();

        $floor = $Floor->toFind($this->auth['hid'], $request->get('fid'));

        if (empty($floor)) {
            return [
                'msg' => '楼层信息不存在',
                'err' => 404,
            ];
        }

        if ($Floor->toUnique($request, $this->auth['hid'])) {
            return [
                'msg' => '楼层信息已存在',
                'err' => 422,
            ];
        }

        $validator = $Floor->toValidator($request);

        if (!empty($validator)) {
            return $validator;
        }

        $input['bid'] = $request->get('bid');
        $input['name'] = $request->get('name');
        $input['description'] = $request->get('description', '');

        try {

            $Floor->toUpdate($floor, $input);

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

    public function delete(Request $request)
    {
        $fid = $request->get('fid', 0);

        if (empty($fid)) {

            return [
                'msg' => '楼层号不能为空',
                'err' => 422,
            ];
        }


        $Room = new Room();

        $num = $Room->toCountTidRoom($this->auth['hid'], $fid);

        if (!empty($num)) {
            return [
                'msg' => '楼层下面含有房间，暂无法删除此楼层信息',
                'err' => 422,
            ];
        }

        $Floor = new Floor();

        $num = $Floor->toDelete($fid, $this->auth['hid']);

        if (empty($num)) {
            return [
                'msg' => '楼层删除失败',
                'err' => 422,
            ];
        }

        return [
            'msg' => '查询成功',
            'err' => 0,
        ];
    }
}
