<?php

namespace App\Http\Controllers\Router;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\Live;
use App\Models\Lock;
use App\Models\Room;
use App\Models\Service;
use App\Models\Stock;
use App\Models\Type;
use App\Models\Value;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockController extends Controller
{

    private $auth;

    private $date;

    public function __construct()
    {
        $this->auth = Auth::user();

        $this->date = Hotel::toGetTime($this->auth['hid']);
    }

    public function getStock(Request $request)
    {
        $Stock = new Stock();

        $tid = $request->get('tid');
        $start = $request->get('start', $this->date);
        $end = $request->get('end', date('Y-m-d', (strtotime($this->date) + 86400)));

        if ($start == $end) {
            $start = date('Y-m-d', (strtotime($this->date) + 86400));
        }

        $validator = $Stock->toValidatorStockSearch($tid, $start, $end, $this->date);

        if (!empty($validator)) {
            return $validator;
        }

        $Type = new Type();

        $type = $Type->toFind($tid, $this->auth['hid']);

        if (empty($type)) {
            return [
                'msg' => '房型信息不存在',
                'err' => 404,
            ];
        }

        $last = $Stock->hasEmpty($this->auth['hid'], $tid, $start, $end);

        if (empty($last)) {

            return [
                'msg' => '查询成功',
                'data' => null,
                'err' => 0,
            ];
        }

        $Room = new Room();

        $rooms = $Room->toSimpleGet($tid);

        $Service = new Service();

        $services = $Service->toGet($this->auth['hid'], $tid, $start, $end);

        if (!empty($services)) {

            //  释放维修房间库存

            foreach ($rooms as $key => $value) {

                foreach ($services as $k => $v) {

                    if ($value->rid == $v->rid) {

                        $rooms->forget($key);
                    }
                }
            }

            if (empty($rooms)) {

                return [
                    'msg' => '查询成功',
                    'data' => null,
                    'err' => 0,
                ];
            }
        }

        $Lock = new Lock();

        $locks = $Lock->toGet($this->auth['hid'], $tid, $start, $end);

        if (!empty($locks)) {
            //  释放锁定房间库存
            foreach ($rooms as $key => $value) {

                foreach ($locks as $k => $v) {

                    if ($value->rid == $v->rid) {

                        $rooms->forget($key);
                    }
                }
            }

            if (empty($rooms)) {

                return [
                    'msg' => '查询成功',
                    'data' => null,
                    'err' => 0,
                ];
            }
        }

        //  剔除已入住房间

        $Live = new Live();

        $values = $Live->toGetLiveRoom($this->auth['hid'], $tid, $start, $end);

        if (!empty($values)) {
            //  释放锁定房间库存
            foreach ($rooms as $key => $value) {

                foreach ($values as $k => $v) {

                    if ($value->rid == $v->rid) {

                        $rooms->forget($key);
                    }
                }
            }

            if (empty($rooms)) {

                return [
                    'msg' => '查询成功',
                    'data' => null,
                    'err' => 0,
                ];
            }
        }

        return [
            'msg' => '查询成功',
            'data' => [
                'type' => $type->name,
                'room' => $rooms,
                'stock' => $last
            ],
            'err' => 0,
        ];

    }

    public function getGear(Request $request)
    {
        $lid = $request->get('lid');

        $Live = new Live();

        $live = $Live->toFindOrdainLive($this->auth['hid'], $lid);

        if (empty($live)) {
            return [
                'msg' => '未找到相关预定信息',
                'err' => 404
            ];
        }

        $Stock = new Stock();

        $tid = $live->tid;
        $start = $live->start;
        $end = $live->end;

        if ($start == $end) {
            $start = date('Y-m-d', (strtotime($this->date) + 86400));
        }

        $validator = $Stock->toValidatorStockSearch($tid, $start, $end, $this->date);

        if (!empty($validator)) {
            return $validator;
        }

        $Type = new Type();

        $type = $Type->toFind($tid, $this->auth['hid']);

        if (empty($type)) {
            return [
                'msg' => '房型信息不存在',
                'err' => 404,
            ];
        }

        $last = $Stock->hasEmpty($this->auth['hid'], $tid, $start, $end, false);

        if (empty($last)) {

            return [
                'msg' => '查询成功',
                'data' => null,
                'err' => 0,
            ];
        }

        $Room = new Room();

        $rooms = $Room->toSimpleGet($tid);

        $Service = new Service();

        $services = $Service->toGet($this->auth['hid'], $tid, $start, $end);

        if (!empty($services)) {

            //  释放维修房间库存

            foreach ($rooms as $key => $value) {

                foreach ($services as $k => $v) {

                    if ($value->rid == $v->rid) {

                        $rooms->forget($key);
                    }
                }
            }

            if (empty($rooms)) {

                return [
                    'msg' => '查询成功',
                    'data' => null,
                    'err' => 0,
                ];
            }
        }

        $Lock = new Lock();

        $locks = $Lock->toGet($this->auth['hid'], $tid, $start, $end);

        if (!empty($locks)) {
            //  释放锁定房间库存
            foreach ($rooms as $key => $value) {

                foreach ($locks as $k => $v) {

                    if ($value->rid == $v->rid) {

                        $rooms->forget($key);
                    }
                }
            }

            if (empty($rooms)) {

                return [
                    'msg' => '查询成功',
                    'data' => null,
                    'err' => 0,
                ];
            }
        }

        $values = $Live->toGetLiveRoom($this->auth['hid'], $tid, $start, $end);

        if (!empty($values)) {
            //  释放锁定房间库存
            foreach ($rooms as $key => $value) {

                foreach ($values as $k => $v) {

                    if ($value->rid == $v->rid) {

                        $rooms->forget($key);
                    }
                }
            }

            if (empty($rooms)) {

                return [
                    'msg' => '查询成功',
                    'data' => null,
                    'err' => 0,
                ];
            }
        }

        if (!empty($live->rid)) {
            $room = $Room->toSimpleFind($live->rid, $this->auth['hid']);

            $tmpArr = [];

            $tmpArr['rid'] = $room->rid;
            $tmpArr['room_no'] = $room->room_no;
            $tmpArr['remark'] = $room->remark;

            $rooms[] = $tmpArr;
        }

        return [
            'msg' => '查询成功',
            'data' => [
                'type' => $type->name,
                'room' => $rooms
            ],
            'err' => 0,
        ];

    }

    public function changeGear(Request $request)
    {
        $rid = $request->get('rid');
        $oid = $request->get('oid');
        $tid = $request->get('tid');

        if (empty($rid)) {
            return [
                'msg' => '房号不能为空',
                'err' => 404
            ];
        }
        if (empty($oid)) {
            return [
                'msg' => '订单号不能为空',
                'err' => 404
            ];
        }

        $Live = new Live();

        $live = $Live->toFindChangeLive($this->auth['hid'], $rid, $oid);

        if (empty($live)) {
            return [
                'msg' => '未找到相关入住房间信息',
                'err' => 404
            ];
        }

        $date = date('Ymd', strtotime(Hotel::toGetTime($this->auth['hid'])));

        $Stock = new Stock();

        $start = $date;
        $tid = empty($tid) ? $live->tid : $tid;
        $end = $live->end;

        if ($start == $end) {
            $start = date('Y-m-d', (strtotime($this->date) + 86400));
        }

        $validator = $Stock->toValidatorStockSearch($tid, $start, $end, $this->date);

        if (!empty($validator)) {
            return $validator;
        }

        $Type = new Type();

        $type = $Type->toFind($tid, $this->auth['hid']);

        if (empty($type)) {
            return [
                'msg' => '房型信息不存在',
                'err' => 404,
            ];
        }

        $last = $Stock->hasEmpty($this->auth['hid'], $tid, $start, $end, false);

        if (empty($last)) {

            return [
                'msg' => '查询成功',
                'data' => null,
                'err' => 0,
            ];
        }

        $Room = new Room();

        $rooms = $Room->toSimpleGet($tid);

        $Service = new Service();

        $services = $Service->toGet($this->auth['hid'], $tid, $start, $end);

        if (!empty($services)) {

            //  释放维修房间库存

            foreach ($rooms as $key => $value) {

                foreach ($services as $k => $v) {

                    if ($value->rid == $v->rid) {

                        $rooms->forget($key);
                    }
                }
            }

            if (empty($rooms)) {

                return [
                    'msg' => '查询成功',
                    'data' => null,
                    'err' => 0,
                ];
            }
        }

        $Lock = new Lock();

        $locks = $Lock->toGet($this->auth['hid'], $tid, $start, $end);

        if (!empty($locks)) {
            //  释放锁定房间库存
            foreach ($rooms as $key => $value) {

                foreach ($locks as $k => $v) {

                    if ($value->rid == $v->rid) {

                        $rooms->forget($key);
                    }
                }
            }

            if (empty($rooms)) {

                return [
                    'msg' => '查询成功',
                    'data' => null,
                    'err' => 0,
                ];
            }
        }

        $values = $Live->toGetLiveRoom($this->auth['hid'], $tid, $start, $end);

        if (!empty($values)) {
            //  释放锁定房间库存
            foreach ($rooms as $key => $value) {

                foreach ($values as $k => $v) {

                    if ($value->rid == $v->rid) {

                        $rooms->forget($key);
                    }
                }
            }

            if (empty($rooms)) {

                return [
                    'msg' => '查询成功',
                    'data' => null,
                    'err' => 0,
                ];
            }
        }

        if (!empty($live->rid)) {
            $room = $Room->toSimpleFind($live->rid, $this->auth['hid']);

            $tmpArr = [];

            $tmpArr['rid'] = $room->rid;
            $tmpArr['room_no'] = $room->room_no;
            $tmpArr['remark'] = $room->remark;

            $rooms[] = $tmpArr;
        }

        $tmpRooms = [];

        foreach ($rooms as $value) {
            $tmpRooms[] = $value;
        }

        return [
            'msg' => '查询成功',
            'data' => [
                'type' => $type->name,
                'room' => $tmpRooms
            ],
            'err' => 0,
        ];

    }

    public function findStock($tid, $start, $end)
    {
        $Stock = new Stock();

        $start = empty(strtotime($start)) ? $this->date : $start;
        $end = empty(strtotime($end)) ? date('Y-m-d', (strtotime($this->date) + 86400)) : $end;

        if ($start == $end) {
            $start = date('Y-m-d', (strtotime($this->date) + 86400));
        }

        if (!empty($validator)) {
            return $validator;
        }

        $Type = new Type();

        $type = $Type->toFind($tid, $this->auth['hid']);

        if (empty($type)) {
            return [
                'msg' => '房型信息不存在',
                'err' => 404,
            ];
        }

        $last = $Stock->hasEmpty($this->auth['hid'], $tid, $start, $end);
        if (empty($last)) {

            return [
                'stock' => 0,
                'room' => null,
                'type' => $type->name,
            ];
        }

        $Room = new Room();

        $rooms = $Room->toSimpleGet($tid);

        $Service = new Service();

        $services = $Service->toGet($this->auth['hid'], $tid, $start, $end);

        //  释放维修房间库存
        if (!empty($services)) {

            foreach ($rooms as $key => $value) {

                foreach ($services as $k => $v) {

                    if ($value->rid == $v->rid) {

                        $rooms->forget($key);
                    }
                }
            }

            if (empty($rooms)) {

                return [
                    'stock' => 0,
                    'room' => null,
                    'type' => $type->name,
                ];
            }
        }

        $Lock = new Lock();

        $locks = $Lock->toGet($this->auth['hid'], $tid, $start, $end);

        //  释放锁定房间库存
        if (!empty($locks)) {

            foreach ($rooms as $key => $value) {

                foreach ($locks as $k => $v) {

                    if ($value->rid == $v->rid) {

                        $rooms->forget($key);
                    }
                }
            }

            if (empty($rooms)) {

                return [
                    'stock' => 0,
                    'room' => null,
                    'type' => $type->name,
                ];
            }
        }

        $Live = new Live();

        $values = $Live->toGetLiveRoom($this->auth['hid'], $tid, $start, $end);

        //  释放预定房间 或 入住房间库存
        if (!empty($values)) {

            foreach ($rooms as $key => $value) {

                foreach ($values as $k => $v) {

                    if ($value->rid == $v->rid) {

                        $rooms->forget($key);
                    }
                }
            }

            if (empty($rooms)) {

                return [
                    'stock' => 0,
                    'room' => null,
                    'type' => $type->name,
                ];
            }
        }

        return [
            'stock' => $last,
            'room' => $rooms,
            'type' => $type->name,
        ];
    }
}
