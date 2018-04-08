<?php

namespace App\Http\Controllers\Router;

use App\Http\Controllers\Controller;
use App\Models\Finance;
use App\Models\Floor;
use App\Models\Guest;
use App\Models\Hotel;
use App\Models\Live;
use App\Models\Receipt;
use App\Models\Room;
use App\Models\Stock;
use App\Models\Type;
use App\Models\Value;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RoomController extends Controller
{
    private $auth;

    private $date;

    public function __construct()
    {
        $this->auth = Auth::user();

        $this->date = Hotel::toGetTime($this->auth['hid']);
    }

    public function create(Request $request)
    {
        $Room = new Room();

        $validator = $Room->toValidator($request);

        if (!empty($validator)) {
            return $validator;
        }

        $Floor = new Floor();

        $floor = $Floor->toFind($this->auth['hid'], $request->get('bid'), $request->get('fid'));

        if (empty($floor)) {
            return [
                'msg' => '楼层信息不存在',
                'err' => 404,
            ];
        }

        if ($Room->toUniqueRoom($request, $this->auth['hid'], true)) {
            return [
                'msg' => '房间信息已存在',
                'err' => 422,
            ];
        }

        if ($Room->toUniqueLock($request, $this->auth['hid'])) {
            return [
                'msg' => '智能锁信息已存在',
                'err' => 422,
            ];
        }

        $input['hid'] = $this->auth['hid'];
        $input['fid'] = $request->get('fid');
        $input['tid'] = $request->get('tid');
        $input['bid'] = $request->get('bid');
        $input['room_no'] = $request->get('room_no');
        $input['lock_no'] = $request->get('lock_no', '');
        $input['lock_type'] = $request->get('lock_type', 0);
        $input['wifi'] = $request->get('wifi', '');
        $input['remark'] = $request->get('remark', '');

        DB::beginTransaction();

        $room = $Room->toCreate($input);

        if (empty($room)) {

            DB::rollBack();
            return [
                'msg' => '房间添加失败',
                'err' => 422,
            ];
        }

        $Type = new Type();

        try {

            $Type->upStock($request->get('tid'));

        } catch (\Exception $e) {

            DB::rollBack();
            return [
                'msg' => '房型库存更新失败',
                'err' => 422,
            ];
        }

        $Stock = new Stock();

        try {

            $Stock->toIncrementStock($this->date, $request->get('tid'));
        } catch (\Exception $e) {

            DB::rollBack();
            return [
                'msg' => '房型日库存更新失败',
                'err' => 422,
            ];
        }

        DB::commit();
        return [
            'msg' => '查询成功',
            'err' => 0,
        ];
    }

    public function delete(Request $request)
    {
        $rid = $request->get('rid', 0);

        if (empty($rid)) {

            return [
                'msg' => '房间不能为空',
                'err' => 422,
            ];
        }

        $Room = new Room();

        $room = $Room->toSimpleFind($request->get('rid'), $this->auth['hid']);

        DB::beginTransaction();

        $num = $Room->toDelete($rid, $this->auth['hid']);

        if (empty($num)) {
            DB::rollBack();
            return [
                'msg' => '房间删除失败',
                'err' => 422,
            ];
        }

        $Type = new Type();

        try {

            $Type->downStock($room->tid);

        } catch (\Exception $e) {

            DB::rollBack();
            return [
                'msg' => '房型库存更新失败',
                'err' => 422,
            ];
        }

        $Stock = new Stock();

        try {

            $Stock->toDecrementStock($this->auth['hid'], $this->date, $room->tid);

        } catch (\Exception $e) {

            DB::rollBack();
            return [
                'msg' => '房型日库存更新失败',
                'err' => 422,
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
        $rid = $request->get('rid');

        if (empty($request->get('rid'))) {

            return [
                'msg' => '房间不能为空',
                'err' => 422,
            ];
        }

        $Room = new Room();

        $room = $Room->toSimpleFind($rid, $this->auth['hid']);

        if (empty($room)) {
            return [
                'msg' => '房间信息不存在',
                'err' => 404,
            ];
        }

        if ($Room->toUniqueRoom($request, $this->auth['hid'])) {
            return [
                'msg' => '房间信息已存在',
                'err' => 422,
            ];
        }

        $validator = $Room->toValidator($request);

        if (!empty($validator)) {
            return $validator;
        }

        $input['fid'] = $request->get('fid');
        $input['tid'] = $request->get('tid');
        $input['room_no'] = $request->get('room_no');
        $input['lock_no'] = $request->get('lock_no', '');
        $input['lock_type'] = $request->get('lock_type', 0);
        $input['wifi'] = $request->get('wifi', '');
        $input['remark'] = $request->get('remark', '');

        try {

            $Room->toUpdate($room, $input);

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

    public function get(Request $request)
    {
        $page = intval($request->get('page', 1));

        $page = ($page <= 1 ? 1 : $page) - 1;

        $num = intval($request->get('num', 20));

        $num = $num <= 20 ? 20 : $num;

        $Room = new Room();

        $count = $Room->toCount($this->auth['hid']);

        $data = null;

        if (!empty($count)) {

            $room = $Room->toGetPage($this->auth['hid'], $num, $page);

            $data['info'] = $room;
            $data['count'] = $count;
            $data['num'] = $num;
            $data['page'] = $page + 1;
        }

        return [
            'msg' => '查询成功',
            'data' => $data,
            'err' => 0,
        ];
    }

    public function find(Request $request)
    {
        $rid = $request->get('rid', 0);

        if (empty($rid)) {
            return [
                'msg' => '房号不能为空',
                'data' => null,
                'err' => 404,
            ];
        }

        $Room = new Room();

        $room = $Room->toSimpleFindAll($rid, $this->auth['hid']);

        return [
            'msg' => '查询成功',
            'data' => $room,
            'err' => 0,
        ];
    }

    public function status(Request $request)
    {
        $status = $request->get('status');

        $type = $request->get('type');

        $build = $request->get('build');

        $input = [
            'status' => $status,
            'type' => $type,
            'build' => $build,
        ];

        $Room = new Room();

        $room = $Room->toGet($this->auth['hid'], $input);

        $info = [];

        foreach ($room as $value) {

            $info[$value->bid]['name'] = $value->build_name;
            $info[$value->bid]['bid'] = $value->bid;
        }

        foreach ($room as $value) {

            $info[$value->bid]['info'][$value->fid]['name'] = $value->floor_name;
            $info[$value->bid]['info'][$value->fid]['fid'] = $value->fid;
        }

        foreach ($room as $value) {

            $tmpArr = [];

            $tmpArr['rid'] = $value->rid;
            $tmpArr['name'] = $value->room_no;
            $tmpArr['tid'] = $value->tid;
            $tmpArr['type'] = $value->type_name;
            $tmpArr['link'] = $value->link;
            $tmpArr['remark'] = $value->remark;
            $tmpArr['status'] = $value->status;

            $info[$value->bid]['info'][$value->fid]['info'][] = $tmpArr;
        }

        $data = [];

        foreach ($info as $key => $value) {     //  楼栋
            $data[] = $value;
        }

        foreach ($data as $key => $value) {
            $tmpArr = [];
            foreach ($value['info'] as $k => $v) {
                $tmpArr[] = $v;
            }
            $data[$key]['info'] = $tmpArr;
        }


        return [
            'msg' => '查询成功',
            'data' => $data,
            'err' => 0,
        ];

    }

    public function change(Request $request)
    {
        $sid = $request->get('sid');
        $rid = $request->get('rid');

        if (empty($sid)) {
            return [
                'msg' => '更新状态不能为空',
                'err' => 404
            ];
        }

        if (empty($rid)) {
            return [
                'msg' => '房号不能为空',
                'err' => 404
            ];
        }

        $Room = new Room();

        $room = $Room->toSimpleFind($rid, $this->auth['hid']);

        if ($room->status == 1) {
            $status = [3, 7, 8];
        } else if ($room->status == 2) {
            $status = [4];
        } else if ($room->status == 3) {
            $status = [1, 8];
        } else if ($room->status == 4) {
            $status = [2];
        } else if ($room->status == 7) {
            $status = [1];
        } else if ($room->status == 8) {
            $status = [1, 3];
        } else {
            return [
                'msg' => '未知的房间状态',
                'err' => 422
            ];
        }

        if (!in_array($sid, $status)) {
            return [
                'msg' => '错误的房间更新状态'
            ];
        }

        $upRes = $Room->toUpdateStatus($this->auth['hid'], $room->rid, $sid);

        if (empty($upRes)) {
            return [
                'msg' => '房间状态更新失败',
                'err' => 422
            ];
        }

        return [
            'msg' => '查询成功',
            'err' => 0
        ];
    }

    public function info(Request $request)
    {
        $rid = $request->get('rid');

        if (empty($rid)) {
            return [
                'msg' => '房号不能为空',
                'err' => 404
            ];
        }

        $Room = new Room();

        $room = $Room->toSimpleFind($rid, $this->auth['hid']);

        if ($room->status != 2) {
            return [
                'msg' => '房间不是入住房，禁止其他操作',
                'err' => 422
            ];
        }

        $Live = new Live();

        $live = $Live->toFindLiveRoom($this->auth['hid'], $rid);

        $Guest = new Guest();

        $guest = $Guest->toGet($this->auth['hid'], $rid, $live->oid);

        foreach ($guest as $key => $value) {
            $tmpArr = [];
            $user = json_decode($value->user, true);
            $tmpArr['name'] = $user['name'];
            $tmpArr['sex'] = $user['sex'];
            $tmpArr['phone'] = $user['phone'];
            $tmpArr['remark'] = $user['remark'];
            $guest[$key] = $tmpArr;
        }

        //  获取每日房价
        $Value = new Value();
        $value = $Value->toGet($this->auth['hid'], $rid, $live->oid);

        foreach ($value as $key => $val) {
            $value[$key]->price = $val->price / 100;
            $value[$key]->date = mb_substr($val->date, 0, 4) . '-' . mb_substr($val->date, 4, 2) . '-' . mb_substr($val->date, 6, 2);
        }

        $Finance = new Finance();

        $finance = $Finance->toGetRoom($this->auth['hid'], $live->oid, $rid);

        $Receipt = new Receipt();

        $receipt = $Receipt->toGetAll($this->auth['hid'], $live->oid);


        return [
            'msg' => '查询成功',
            'data' => [
                'info' => [
                    'rid' => $rid,
                    'no' => $room->room_no,
                    'type' => $room->type,
                    'tid' => $room->tid,
                    'oid' => $live->oid,
                    'start' => $live->init,
                    'end' => $live->end,
                    'breakfast' => $live->breakfast
                ],
                'guest' => $guest,
                'finance' => $finance,
                'price' => $value,
                'receipt' => $receipt
            ],
            'err' => 0
        ];
    }

    public function upPrice(Request $request)
    {
        $vid = $request->get('vid');
        $price = $request->get('price');
        $description = $request->get('description');

        if (empty($vid)) {
            return [
                'msg' => '未找到相关价格',
                'err' => 404
            ];
        }

        if (empty($price)) {
            return [
                'msg' => '禁止更新零价格',
                'err' => 422
            ];
        }

        if (empty($description)) {
            return [
                'msg' => '价格修改理由不能为空',
                'err' => 422
            ];
        }

        $Value = new Value();

        $value = $Value->toFindValue($this->auth['hid'], $vid);

        if (empty($value)) {
            return [
                'msg' => '未找到相关房价, 请确定房价是否存在',
                'err' => 404
            ];
        } else if ($value->date < $this->date) {
            return [
                'msg' => '房价已过期，禁止修改',
                'err' => 422
            ];
        }

        $time = date('Y-m-d H:i:s');

        $description = $value->description . '|||' . $time . '-' . $description;

        $affected = $Value->toUpdateValue($vid, $price, $description, $this->auth['hid']);

        if (empty($affected)) {
            return [
                'msg' => '房价更新失败, 请稍后重试',
                'err' => 422
            ];
        }

        return [
            'msg' => '查询成功',
            'err' => 0
        ];
    }

}
