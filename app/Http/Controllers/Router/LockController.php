<?php

namespace App\Http\Controllers\Router;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\Lock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LockController extends Controller
{
    private $date;

    private $auth;

    public function __construct()
    {
        $this->auth = Auth::user();

        $this->date = Hotel::toGetTime($this->auth['hid']);
    }

    public function get()
    {
        $Service = new Lock();

        $count = $Service->toCount($this->auth['hid']);

        $service = null;

        if (!empty($count)) {

            $service = $Service->toGetLock($this->auth['hid']);
        }

        return [
            'msg' => '查询成功',
            'data' => $service,
            'err' => 0,
        ];
    }

    public function create(Request $request)
    {

        $Lock = new Lock();

        $validator = $Lock->toValidator($request, $this->date);

        if (!empty($validator)) {
            return $validator;
        }

        $Room = new Room();

        $room = $Room->toSimpleFind($request->get('rid'), $this->auth['hid'], $request->get('tid'));

        if (empty($room)) {
            return [
                'msg' => '房间信息不存在',
                'err' => 404,
            ];
        }

        $start = $request->get('start');
        $end = $request->get('end');

        $input['hid'] = $this->auth['hid'];
        $input['tid'] = $room->tid;
        $input['type'] = $room->type;
        $input['rid'] = $room->rid;
        $input['room_no'] = $room->room_no;
        $input['start'] = date('Ymd', strtotime($start));
        $input['end'] = date('Ymd', strtotime($end));

        $lock = $Lock->toCreate($input);

        if (empty($lock)) {

            return [
                'msg' => '维修信息添加失败',
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
        $lid = $request->get('lid', 0);

        if (empty($lid)) {

            return [
                'msg' => '维修单号不能为空',
                'err' => 422,
            ];
        }

        $Lock = new Lock();

        $lock = $Lock->toSimpleFind($lid, $this->auth['hid']);

        if (!empty($lock)) {

            return [
                'msg' => '未找到相关维修单',
                'err' => 404,
            ];
        }

        try {

            $Lock->toUpdate($lock, time());

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
}
