<?php

namespace App\Http\Controllers\Router;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Help\Condition;
use App\Http\Controllers\Help\Ordain;
use App\Jobs\OrdainJob;
use App\Models\Change;
use App\Models\Company;
use App\Models\Finance;
use App\Models\Guest;
use App\Models\Hotel;
use App\Models\Live;
use App\Models\Order;
use App\Models\Pay;
use App\Models\Price\Price;
use App\Models\Receipt;
use App\Models\Refund;
use App\Models\Room;
use App\Models\Stock;
use App\Models\User;
use App\Models\Value;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    private $auth;

    private $date;

    private $official;

    private $condition;

    private $Order;

    public function __construct(Request $request)
    {
        $this->auth = Auth::user();

        $this->date = Hotel::toGetTime($this->auth['hid']);

        $this->official = User::toFindOfficial($this->auth['hid']);

        $this->condition = Condition::getCondition($request, $this->auth, $this->official);

        $this->Order = new Order();
    }

    /**
     * 获取短租订单列表
     *
     * @param Request $request
     * @return array
     */
    public function getShort(Request $request)
    {
        $page = intval($request->get('page'));

        $page = ($page <= 1 ? 1 : $page) - 1;

        $num = intval($request->get('num'));

        $num = $num <= 20 ? 20 : $num;

        $count = $this->Order->toCountOrder($this->auth['hid'], 0);

        $info = null;

        if (!empty($count)) {

            $order = $this->Order->toGetOrder($this->auth['hid'], $num, $page, 0);

            $data = [];

            foreach ($order as $key => $value) {
                $tmpArr = [];

                $tmpArr['oid'] = $value->oid;

                if (empty($value->oid_other)) {
                    $tmpArr['oid_other'] = '';
                } else {
                    $tmpArr['oid_other'] = $value->oid_other;
                }

                $tmpArr['operate'] = $value->key;
                $tmpArr['init'] = $value->init;
                $tmpArr['leave'] = $value->leave;

                $tmpArr['user'] = json_decode($value->ordain);

                if (!empty($value->vip_name)) {

                    $tmpArr['channel'] = $value->vip_name;
                } else if (!empty($value->company_name)) {

                    $tmpArr['channel'] = $value->company_name;
                } else {

                    $tmpArr['channel'] = $value->channel_name;
                }

                $tmpArr['remark'] = $value->remark;
                $tmpArr['status'] = $value->status;

                $data[] = $tmpArr;
            }

            $info['info'] = $data;
            $info['count'] = $count;
            $info['num'] = $num;
            $info['page'] = $page + 1;
        }

        return [
            'msg' => '查询成功',
            'data' => $info,
            'err' => 0,
        ];
    }

    /**
     * 获取长租订单列表
     *
     * @param Request $request
     * @return array
     */
    public function getLong(Request $request)
    {
        $page = intval($request->get('page'));

        $page = ($page <= 1 ? 1 : $page) - 1;

        $num = intval($request->get('num'));

        $num = $num <= 10 ? 10 : $num;

        $count = $this->Order->toCount($this->auth['hid'], 1);

        $info = null;

        if (!empty($count)) {

            $order = $this->Order->toGet($this->auth['hid'], $num, $page, 1, 1);

            $data = [];

            foreach ($order as $key => $value) {
                $tmpArr = [];

                $tmpArr['oid'] = $value['oid'];

                if (empty($value['oid_other'])) {
                    $tmpArr['oid_other'] = '';
                } else {
                    $tmpArr['oid_other'] = $value['oid_other'];
                }

                $tmpArr['operate'] = $value['key'];
                $tmpArr['init'] = $value['init'];
                $tmpArr['leave'] = $value['leave'];

                if (!empty($value['vip_name'])) {

                    $tmpArr['channel'] = $value['vip_name'];
                } else if (!empty($value['company_name'])) {

                    $tmpArr['channel'] = $value['company_name'];
                } else {

                    $tmpArr['channel'] = $value['channel_name'];
                }

                $tmpArr['remark'] = $value['remark'];
                $tmpArr['status'] = $value['status'];

                $data[] = $tmpArr;
            }

            $info['info'] = $data;
            $info['count'] = $count;
            $info['num'] = $num;
            $info['page'] = $page + 1;
        }

        return [
            'msg' => '查询成功',
            'data' => $info,
            'err' => 0,
        ];
    }

    /**
     * 获取预定订单列表
     *
     * @param Request $request
     * @return array
     */
    public function getOrdain(Request $request)
    {
        $page = intval($request->get('page'), 1);

        $page = ($page <= 1 ? 1 : $page) - 1;

        $num = intval($request->get('num'), 20);

        $num = $num <= 20 ? 20 : $num;

        $count = $this->Order->toCountOrdain($this->auth['hid']);

        $info = null;

        if (!empty($count)) {

            $order = $this->Order->toGetOrdain($this->auth['hid'], $num, $page, 0);

            $data = [];

            foreach ($order as $key => $value) {
                $tmpArr = [];

                $tmpArr['oid'] = $value->oid;

                if (empty($value->oid_other)) {
                    $tmpArr['oid_other'] = '';
                } else {
                    $tmpArr['oid_other'] = $value->oid_other;
                }

                $tmpArr['user'] = json_decode($value->ordain);
                $tmpArr['operate'] = $value->key;
                $tmpArr['init'] = $value->init;
                $tmpArr['leave'] = $value->leave;

                if (!empty($value->vip_name)) {

                    $tmpArr['channel'] = $value->vip_name;
                } else if (!empty($value->company_name)) {

                    $tmpArr['channel'] = $value->company_name;
                } else {

                    $tmpArr['channel'] = $value->channel_name;
                }

                $tmpArr['remark'] = $value->remark;
                $tmpArr['status'] = $value->status;

                $data[] = $tmpArr;
            }

            $info['info'] = $data;
            $info['count'] = $count;
            $info['num'] = $num;
            $info['page'] = $page + 1;
        }

        return [
            'msg' => '查询成功',
            'data' => $info,
            'err' => 0,
        ];
    }

    public function findOrdainLive(Request $request)
    {
        $oid = $request->get('oid');

        if (empty($oid)) {
            return [
                'msg' => '订单号不能为空',
                'err' => 404
            ];
        }

        $Order = new Order();

        $order = $Order->toFindOfficialOrderWithOid($oid, $this->auth['hid']);

        if (empty($order)) {
            return [
                'msg' => '相关订单未找到',
                'err' => 404
            ];
        }

        $Live = new Live();

        $live = $Live->toGetLive($this->auth['hid'], $order->oid);

        if (empty($live)) {
            return [
                'msg' => '未找到相关入住或预定记录',
                'err' => 404
            ];
        }

        return [
            'msg' => '查询成功',
            'data' => $live,
            'err' => 0
        ];
    }

    /**
     * 客房预定
     *
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function ordain(Request $request)
    {
        $no = $request->get('no');                                      //  第三方订单号
        $name = $request->get('name');
        $phone = $request->get('phone');
        $remark = $request->get('remark', '');
        $channel = $request->get('channel', $this->auth['id']);
        $company = $request->get('company', 0);
        $vip = $request->get('vip', 0);
        $content = $request->get('content');
        $payment = $request->get('payment');

        $validator = $this->Order->toValidatorOrdain($request, $this->date);

        if (!empty($validator)) {
            return $validator;
        }

        $StockController = new StockController();

        foreach ($content as $key => $value) {

            $day = 0;

            if ($value['start'] != $value['end']) {

                $stock = $StockController->findStock($value['tid'], $value['start'], $value['end']);

                if ($stock['stock'] < $value['num']) {

                    return [
                        'msg' => '房型 ' . $stock['type'] . ' 库存不足',
                        'err' => 422,
                    ];
                }

                for ($i = strtotime($value['start']); $i < strtotime($value['end']); $i += 86400) {
                    $day++;
                }
            }

            $content[$key]['day'] = $day;
        }

        $receipt = [];

        if (!empty($payment)) {

            $Pay = new Pay();

            foreach ($payment as $value) {

                $pay = $Pay->toFind($this->auth['hid'], $value['pid']);

                if (empty($pay)) {

                    return [
                        'msg' => '禁止不存在的付款方式',
                        'err' => 422,
                    ];
                }
                $tmpArr['hid'] = $this->auth['hid'];
                $tmpArr['price'] = $value['price'];
                $tmpArr['pid'] = $pay->pid;
                $tmpArr['pay'] = $pay->name;
                $tmpArr['priority'] = $pay->priority;

                $receipt[] = $tmpArr;
            }
        }

        $channelName = '';
        if ($channel != $this->auth['id']) {
            $User = new User();
            $isEmptyChannel = $User->toFindUser($channel, $this->auth['hid']);

            if (empty($isEmptyChannel)) {

                return [
                    'msg' => '不存在的渠道信息',
                    'err' => 422,
                ];
            }
            $channelName = $isEmptyChannel->name;
        } else {
            $channelName = $this->auth['name'];
        }

        $companyName = '';
        if (!empty($company)) {
            $Company = new Company();

            $isEmptyCompany = $Company->toFindCompany($company, $this->auth['hid']);

            if (empty($isEmptyCompany)) {

                return [
                    'msg' => '不存在的协议公司信息',
                    'err' => 422,
                ];
            }

            $companyName = $isEmptyCompany->name;
        }

        // 打包预定信息
        $user['name'] = $name;
        $user['phone'] = $phone;

        if (empty($this->auth['official'])) {

            //  非官方人员操作, 移至队列处理订单
            dispatch(new OrdainJob($user, $no, $channel, $channelName, $company, $companyName, $vip, '', $content, $payment, $remark, $this->auth, 1));

            return [
                'msg' => '房间预定中, 请稍等',
                'err' => 0,
            ];

        } else {

            $OrdainController = new Ordain($user, $no, $channel, $channelName, $company, $companyName, $vip, '', $content, $payment, $remark, $this->auth);

            $order = $OrdainController->ordain();

            if (!empty($order)) {

                return $order;
            }

            return [
                'msg' => '查询成功',
                'err' => 0,
            ];
        }

    }

    /**
     * 预定排房
     *
     * @param Request $request
     * @return array
     */
    public function gear(Request $request)
    {
        $lid = $request->get('lid');
        $rid = $request->get('rid');

        if (empty($lid)) {
            return [
                'msg' => '预定记录不能为空',
                'err' => 404,
            ];
        }
        if (empty($rid)) {
            return [
                'msg' => '房间号不能为空',
                'err' => 404,
            ];
        }

        $Live = new Live();

        $live = $Live->toFindOrdainLive($this->auth['hid'], $lid);

        if (empty($live)) {
            return [
                'msg' => '未找到相关预定信息',
                'err' => 404
            ];
        }

        $OrderController = new StockController();

        $stock = $OrderController->findStock($live->tid, $live->start, $live->end);

        if (empty($stock['room'])) {
            return [
                'msg' => '暂未查询到可入住房',
                'err' => 422,
            ];
        }

        $mark = true;
        foreach ($stock['room'] as $value) {
            if ($value->rid == $rid) {
                $mark = false;
                break;
            }
        }

        if ($mark) {
            return [
                'msg' => '此房间暂不能排房',
                'err' => 422
            ];
        }

        $affected = $Live->toUpdateRid($this->auth['hid'], $lid, $rid);

        if (empty($affected)) {
            return [
                'msg' => '排房失败',
                'err' => 422
            ];
        }

        return [
            'msg' => '排房成功',
            'err' => 0
        ];
    }


    public function ordainLive(Request $request)
    {
        $content = $request->get('content');

        $payment = $request->get('payment');

        if (empty($content) || !is_array($content)) {
            return [
                'msg' => '排房内容格式错误',
                'err' => 422
            ];
        }

        foreach ($content as $key => $value) {

            if (!array_key_exists('lid', $value) || !array_key_exists('guest', $value)) {
                unset($content[$key]);
                continue;
            }

            if (!is_array($value['guest'])) {
                unset($content[$key]);
                continue;
            }

            foreach ($value['guest'] as $k => $v) {
                if (empty($v['uid'])) {
                    unset($content[$key]['guest'][$k]);
                    continue;
                }
            }
            if (empty($value['guest'])) {
                unset($content[$key]);
                continue;
            }
        }
        if (empty($content)) {
            return [
                'msg' => '排房内容格式错误',
                'err' => 422
            ];
        }

        $guest = [];
        $oid = 0;

        $Live = new Live();
        $Value = new Value();
        $Room = new Room();

        DB::beginTransaction();

        $Stock = new Stock();

        foreach ($content as $key => $value) {
            $live = $Live->toFindOrdainLive($this->auth['hid'], $value['lid']);

            if (empty($live)) {
                return [
                    'msg' => '未找到相关预定信息',
                    'err' => 404
                ];
            }
            if (empty($live->rid)) {
                return [
                    'msg' => '请先排房, 再行办理入住',
                    'err' => 404
                ];
            }

            $oid = $live->oid;

            $uid = 0;

            try {

                $day = 0;

                for ($i = strtotime($live->start); $i < strtotime($live->end); $i += 86400) {
                    $day++;
                }

                $stock = $Stock->toIncrementLive($this->auth['hid'], $live->tid, $live->start, $live->end, 1);

                if ($stock != $day) {

                    DB::rollBack();
                    return [
                        'msg' => '日库存更新失败',
                        'err' => 422,
                    ];
                }

                $stock = $Stock->toDecrementBooking($this->auth['hid'], $live->tid, $live->start, $live->end, 1);

                if ($stock != $day) {

                    DB::rollBack();
                    return [
                        'msg' => '日库存更新失败',
                        'err' => 422,
                    ];
                }
            } catch (\Exception $e) {

                DB::rollBack();
                return [
                    'msg' => '库存更新修改失败',
                    'err' => 500,
                ];
            }

            foreach ($value['guest'] as $val) {
                $tmpArr = [];
                $tmpArr['hid'] = $this->auth['hid'];
                $tmpArr['oid'] = $live->oid;
                $tmpArr['uid'] = $val['uid'];
                $tmpArr['user'] = json_encode([
                    'name' => $val['user'],
                    'sex' => $val['sex'],
                    'phone' => $val['phone'],
                    'remark' => $val['remark']
                ], JSON_UNESCAPED_UNICODE);
                $tmpArr['rid'] = $live->rid;
                $tmpArr['start'] = time();
                $tmpArr['end'] = $live->end;
                $tmpArr['status'] = 1;

                $guest[] = $tmpArr;

                if (empty($uid)) {
                    $uid = $val['uid'];
                }
            }

            try {

                $num = $Value->toUpdateRid($this->auth['hid'], $live->lid, $live->rid);

                if (empty($num)) {
                    DB::rollBack();
                    return [
                        'msg' => '入住房间信息更新失败',
                        'err' => 422
                    ];
                }

            } catch (\Exception $e) {

                DB::rollBack();
                return [
                    'msg' => '房间状态更新失败',
                    'err' => 500,
                ];
            }

            try {

                $num = $Room->toUpdateStatus($this->auth['hid'], $live->rid, 2);

                if (empty($num)) {
                    DB::rollBack();
                    return [
                        'msg' => '房间状态更新失败',
                        'err' => 422
                    ];
                }

            } catch (\Exception $e) {

                DB::rollBack();
                return [
                    'msg' => '房间状态更新失败',
                    'err' => 500,
                ];
            }

            try {

                $num = $Live->toUpdateOrdainLive($this->auth['hid'], $live->lid, $uid);

                if (empty($num)) {
                    DB::rollBack();
                    return [
                        'msg' => '入住房间信息更新失败',
                        'err' => 422
                    ];
                }

            } catch (\Exception $e) {

                DB::rollBack();
                return [
                    'msg' => '房间状态更新失败',
                    'err' => 500,
                ];
            }


//            if (empty($type)) {
//
//                try {
//
//                    $day = 0;
//
//                    for ($i = strtotime($start); $i < strtotime($end); $i += 86400) {
//                        $day++;
//                    }
//
//                    $Stock = new Stock();
//
//                    $stock = $Stock->toIncrementLive($this->auth['hid'], $room->tid, $start, $end, 1);
//
//                    if ($stock != $day) {
//
//                        DB::rollBack();
//                        return [
//                            'msg' => '日库存更新失败',
//                            'err' => 422,
//                        ];
//                    }
//
//
//                } catch (\Exception $e) {
//
//                    DB::rollBack();
//                    return [
//                        'msg' => '库存更新修改失败',
//                        'err' => 500,
//                    ];
//                }
//            }

        }

        $Guest = new Guest();

        $guestResult = $Guest->toInsert($guest);

        if (empty($guestResult)) {

            DB::rollBack();
            return [
                'msg' => '宾客入住信息生成失败, 请稍后重试',
                'err' => 422
            ];
        }

        if (!empty($payment)) {

            $Pay = new Pay();

            $receipt = [];

            foreach ($payment as $value) {

                $pay = $Pay->toFind($this->auth['hid'], $value['pid']);

                if (empty($pay)) {

                    return [
                        'msg' => '禁止不存在的付款方式',
                        'err' => 422,
                    ];
                }

                $tmpArr['hid'] = $this->auth['hid'];
                $tmpArr['oid'] = $oid;
                $tmpArr['pid'] = $pay->pid;
                $tmpArr['pay'] = $pay->name;
                $tmpArr['price'] = $value['price'];
                $tmpArr['time'] = time();
                $tmpArr['priority'] = $pay->type;

                $receipt[] = $tmpArr;
            }

            $Receipt = new Receipt();

            $receipt = $Receipt->toCreate($receipt);

            if (empty($receipt)) {

                DB::rollBack();
                return [
                    'msg' => '收款信息生成失败',
                    'err' => 422,
                ];
            }
        }

        $isAllLive = $Live->toCountLive($this->auth['hid'], $oid);

        $Order = new Order();

        try {

            if (empty($isAllLive)) {
                $affected = $Order->toUpdateStatus(2, $oid, $this->auth['hid']);
                if (empty($affected)) {
                    DB::rollBack();
                    return [
                        'msg' => '订单状态更新失败',
                        'err' => 500,
                    ];
                }
            } else {
                $affected = $Order->toUpdateStatus(11, $oid, $this->auth['hid']);
                if (empty($affected)) {
                    DB::rollBack();
                    return [
                        'msg' => '订单状态更新失败',
                        'err' => 500,
                    ];
                }
            }

        } catch (\Exception $e) {

            DB::rollBack();
            return [
                'msg' => '订单状态更新失败',
                'err' => 500,
            ];
        }

        DB::commit();

        return [
            'msg' => '查询成功',
            'err' => 0
        ];
    }

    /**
     * 直接办理入住
     *
     * @param Request $request
     * @return array
     */
    public function live(Request $request)
    {
        $type = 0;

        $rid = $request->get('rid');
        $no = $request->get('no');
        $start = date('Y-m-d', strtotime($this->date));
        $end = $request->get('date');
        $breakfast = $request->get('breakfast', 0);
        $remark = $request->get('remark', '');

        $condition = $this->condition;

        $guest = $request->get('guest');

        $payment = $request->get('payment');

        $validator = $this->Order->toValidatorLive($request, $this->date);

        if (!empty($validator)) {
            return $validator;
        }

        if ($start == $end) {
            $end = date('Y-m-d', strtotime($end) + 86400);

            $type = 1;  //  当天走钟点房
        }

        $Room = new Room();

        $room = $Room->toSimpleFind($rid, $this->auth['hid']);

        if (empty($room)) {
            return [
                'msg' => '客房信息不存在',
                'err' => 404
            ];
        }

        if ($room->status != 1) {
            return [
                'msg' => '客房不是空净房, 暂不能入住',
                'err' => 422
            ];
        }

        $this->condition['tid'] = $room->tid;

        $channelName = '';

        if ($this->condition['channel'] != $this->auth['id']) {
            $User = new User();
            $isEmptyChannel = $User->toFindUser($this->condition['channel'], $this->auth['hid']);

            if (empty($isEmptyChannel)) {

                return [
                    'msg' => '不存在的渠道信息',
                    'err' => 422,
                ];
            }

            $channelName = $isEmptyChannel->name;
        } else {

            $channelName = $this->auth['name'];
        }

        $company = '';
        if (!empty($this->condition['cid'])) {
            $Company = new Company();

            $isEmptyCompany = $Company->toFindCompany($this->condition->cid, $this->auth['hid']);

            if (empty($isEmptyCompany)) {

                return [
                    'msg' => '不存在的协议公司信息',
                    'err' => 422,
                ];
            }

            $company = $isEmptyCompany->name;
        }

        //  查询非当日走的订单房型是否有库存
        if (empty($type)) {

            $StockController = new StockController();

            $stock = $StockController->findStock($room->tid, $start, $end);

            if (empty($stock['stock'])) {
                return [
                    'msg' => '客房已售罄',
                    'err' => 422
                ];
            }

            $isNull = true;

            foreach ($stock['room'] as $value) {

                if ($value['rid'] == $room->rid) {
                    $isNull = false;
                }
            }

            if ($isNull) {

                return [
                    'msg' => '客房已出租',
                    'err' => 422
                ];
            }
        }

        $Price = new Price();

        //  查询每日房价
        $price = $Price->toGet($this->auth['hid'], $this->condition, $start, $end);

        if ($price->isEmpty()) {

            return [
                'msg' => '房型价格查询失败, 请检查您的房型价格是否存在',
                'err' => 404
            ];
        }

        $vip = head($guest);

        DB::beginTransaction();

        $input = [];
        $input['oid_other'] = empty($no) ? null : $no;
        $input['key'] = $this->auth['id'];
        $input['key_name'] = $this->auth['name'];
        $input['hid'] = $this->auth['hid'];
        $input['uid'] = $vip['uid'];
        $input['channel'] = $this->condition['channel'];
        $input['channel_name'] = $channelName;
        $input['company'] = $this->condition['cid'];
        $input['company_name'] = $company;
        $input['vip'] = $this->condition['vip'];
        $input['vip_name'] = '';
        $input['ordain'] = json_encode([
            'name' => $vip['user'],
            'phone' => $vip['phone']
        ], JSON_UNESCAPED_UNICODE);
        $input['remark'] = $remark;
        $input['status'] = 2;
        $input['type'] = 0;

        $order = $this->Order->toCreate($input);

        if (empty($order)) {

            DB::rollBack();
            return [
                'msg' => '订单生成失败',
                'err' => 422,
            ];
        }

        if (!empty($payment)) {

            $Pay = new Pay();

            $receipt = [];

            foreach ($payment as $value) {

                $pay = $Pay->toFind($this->auth['hid'], $value['pid']);

                if (empty($pay)) {

                    return [
                        'msg' => '禁止不存在的付款方式',
                        'err' => 422,
                    ];
                }

                $tmpArr['hid'] = $this->auth['hid'];
                $tmpArr['oid'] = $order->oid;
                $tmpArr['pid'] = $pay->pid;
                $tmpArr['pay'] = $pay->name;
                $tmpArr['price'] = $value['price'];
                $tmpArr['time'] = time();
                $tmpArr['priority'] = $pay->type;

                $receipt[] = $tmpArr;
            }

            $Receipt = new Receipt();

            $receipt = $Receipt->toCreate($receipt);

            if (empty($receipt)) {

                DB::rollBack();
                return [
                    'msg' => '收款信息生成失败',
                    'err' => 422,
                ];
            }
        }

        //  当天入住当天走, 不更新库存
        if (empty($type)) {

            try {

                $day = 0;

                for ($i = strtotime($start); $i < strtotime($end); $i += 86400) {
                    $day++;
                }

                $Stock = new Stock();

                $stock = $Stock->toIncrementLive($this->auth['hid'], $room->tid, $start, $end, 1);

                if ($stock != $day) {

                    DB::rollBack();
                    return [
                        'msg' => '日库存更新失败',
                        'err' => 422,
                    ];
                }


            } catch (\Exception $e) {

                DB::rollBack();
                return [
                    'msg' => '库存更新修改失败',
                    'err' => 500,
                ];
            }
        }

        $Live = new Live();

        $startDate = date('Ymd', strtotime($start));
        $endDate = date('Ymd', strtotime($end));

        $input = [];
        $input['hid'] = $this->auth['hid'];
        $input['oid'] = $order->oid;
        $input['tid'] = $room->tid;
        $input['uid'] = 1;
        $input['rid'] = $room->rid;
        $input['money'] = 0;
        $input['deposit'] = 0;
        $input['find_price'] = json_encode($price, JSON_UNESCAPED_UNICODE);
        $input['start'] = $startDate;
        $input['end'] = empty($type) ? $endDate : $startDate;
        $input['save'] = 0;
        $input['breakfast'] = $breakfast;
        $input['status'] = 1;
        $input['type'] = 0;

        $live = $Live->toCreate($input);

        if (empty($live)) {

            DB::rollBack();
            return [
                'msg' => '房间入住信息生成失败',
                'err' => 422,
            ];
        }

        $input = [];

        foreach ($guest as $key => $val) {
            $tmpArr = [];
            $tmpArr['hid'] = $this->auth['hid'];
            $tmpArr['oid'] = $order->oid;
            $tmpArr['uid'] = $val['uid'];
            $tmpArr['user'] = json_encode([
                'name' => $val['user'],
                'sex' => $val['sex'],
                'phone' => $val['phone'],
                'remark' => $val['remark']
            ], JSON_UNESCAPED_UNICODE);
            $tmpArr['rid'] = $room->rid;
            $tmpArr['start'] = time();
            $tmpArr['end'] = empty($type) ? $endDate : $startDate;
            $tmpArr['status'] = 1;

            $input[] = $tmpArr;
        }

        $Guest = new Guest();

        $guestResult = $Guest->toInsert($input);

        if (empty($guestResult)) {

            DB::rollBack();
            return [
                'msg' => '宾客入住信息生成失败, 请稍后重试',
                'err' => 422
            ];
        }

        $input = [];

        foreach ($price as $value) {

            $tmpArr = [];

            $tmpArr['hid'] = $this->auth['hid'];
            $tmpArr['oid'] = $order->oid;
            $tmpArr['lid'] = $live->lid;
            $tmpArr['rid'] = $room->rid;
            $tmpArr['tid'] = $room->tid;
            $tmpArr['price'] = $value['price'];
            $tmpArr['date'] = $value['date'];
            $tmpArr['type'] = empty($type) ? 0 : 1;
            $tmpArr['status'] = 0;
            $tmpArr['description'] = '散客入住';

            $input[] = $tmpArr;
        }

        $Value = new Value();

        $value = $Value->toInsert($input);

        if (empty($value)) {

            DB::rollBack();
            return [
                'msg' => '每日房价生成失败, 请稍后重试',
                'err' => 422
            ];
        }

        try {

            $num = $Room->toUpdateStatus($this->auth['hid'], $room->rid, 2);

            if (empty($num)) {
                DB::rollBack();
                return [
                    'msg' => '房型状态更新失败',
                    'err' => 422
                ];
            }

        } catch (\Exception $e) {

            DB::rollBack();
            return [
                'msg' => '房间状态更新失败',
                'err' => 500,
            ];
        }

        DB::commit();

        return [
            'msg' => '查询成功',
            'err' => 0
        ];
    }

    /**
     * 订单客房续住
     *
     * @param Request $request
     * @return array
     */
    public function proceed(Request $request)
    {
        $oid = $request->get('oid');

        if (empty($oid)) {
            return [
                'msg' => '订单号不能为空',
                'err' => 404
            ];
        }

        $order = $this->findWithOid($request, true);

        if (empty($order)) {
            return [
                'msg' => '未找到相关订单',
                'err' => 404
            ];
        }

        $rid = $request->get('rid');

        if (empty($rid)) {
            return [
                'msg' => '房间号不能为空',
                'err' => 404
            ];
        }

        $Live = new Live();

        $live = $Live->toFind($this->auth['hid'], $order['oid'], $rid);

        if (empty($live)) {
            return [
                'msg' => '未查询到相关入住记录',
                'err' => 404
            ];
        }


        $condition['tid'] = $live->tid;
        $condition['channel'] = $order->channel;
        $condition['cid'] = $order->company;
        $condition['vip'] = $order->vip;

        $date = $request->get('date');

        $dateTime = strtotime($date);

        if (empty($dateTime)) {
            return [
                'msg' => '续住日期不能为空 或 格式错误',
                'err' => 422
            ];
        }

        $date = date('Ymd', $dateTime);

        $dateTime = strtotime($date);

        $type = 0;

        if (strtotime($live->end) > $dateTime) {
            return [
                'msg' => '续住日期不能小于酒店当前日期',
                'err' => 422
            ];
        } else if ($live->end == $date) {
            $type = 1;

            $date = date('Ymd', $dateTime + 86400);
        }

        $payment = $request->get('payment');

        if (!empty($payment)) {

            $Receipt = new Receipt();
            $validator = $Receipt->toValidator($payment);

            if (!empty($validator)) {

                return $validator;
            }
        }

        $Price = new Price();

        $price = $Price->toGet($this->auth['hid'], $condition, $live->end, $date);

        DB::beginTransaction();

        //  付款信息存在, 写入付款信息
        if (!empty($payment)) {

            $Pay = new Pay();

            $receipt = [];

            foreach ($payment as $value) {

                $pay = $Pay->toFind($this->auth['hid'], $value['pid']);

                if (empty($pay)) {

                    return [
                        'msg' => '禁止不存在的付款方式',
                        'err' => 422,
                    ];
                }

                $tmpArr['hid'] = $this->auth['hid'];
                $tmpArr['oid'] = $order->oid;
                $tmpArr['pid'] = $pay->pid;
                $tmpArr['pay'] = $pay->name;
                $tmpArr['price'] = $value['price'];
                $tmpArr['time'] = time();

                $receipt[] = $tmpArr;
            }

            $Receipt = new Receipt();

            $receipt = $Receipt->toCreate($receipt);

            if (empty($receipt)) {

                DB::rollBack();
                return [
                    'msg' => '收款信息生成失败',
                    'err' => 422,
                ];
            }
        }

        //  查询是否有库存
        $StockController = new StockController();

        $stock = $StockController->findStock($live->tid, $live->end, $date);

        if (empty($stock['stock'])) {
            return [
                'msg' => '客房已售罄',
                'err' => 422
            ];
        }

        $isNull = true;

        foreach ($stock['room'] as $value) {

            if ($value['rid'] == $rid) {
                $isNull = false;
            }
        }

        if ($isNull) {

            return [
                'msg' => '客房已出租',
                'err' => 422
            ];
        }

        try {

            $day = 0;

            for ($i = strtotime($live->end); $i < strtotime($date); $i += 86400) {
                $day++;
            }

            $Stock = new Stock();

            if ($Stock->toIncrementLive($this->auth['hid'], $live->tid, $live->end, $date, 1) != $day) {

                DB::rollBack();
                return [
                    'msg' => '日库存更新失败',
                    'err' => 422,
                ];
            }


        } catch (\Exception $e) {

            DB::rollBack();
            return [
                'msg' => '库存更新修改失败',
                'err' => 500,
            ];
        }

        // 更新入住信息

        $findPrice = json_decode($live->find_price, true);

        foreach ($price as $value) {

            $findPrice[] = $value;
        }

        $input['find_price'] = json_encode($findPrice, JSON_UNESCAPED_UNICODE);
        $input['end'] = $date;

        try {

            $Live->toUpdate($live, $input);

        } catch (\Exception $e) {

            DB::rollBack();
            return [
                'msg' => '查询价格修改失败',
                'err' => 500
            ];
        }

        $input = [];

        foreach ($price as $value) {

            $tmpArr = [];

            $tmpArr['hid'] = $this->auth['hid'];
            $tmpArr['oid'] = $order->oid;
            $tmpArr['rid'] = $rid;
            $tmpArr['tid'] = $live->tid;
            $tmpArr['price'] = $value['price'];
            $tmpArr['date'] = $value['date'];
            $tmpArr['type'] = empty($type) ? 0 : 1;
            $tmpArr['status'] = 0;
            $tmpArr['description'] = '客户续住';

            $input[] = $tmpArr;
        }

        $Value = new Value();

        $value = $Value->toInsert($input);

        if (empty($value)) {

            DB::rollBack();
            return [
                'msg' => '每日房价生成失败, 请稍后重试',
                'err' => 422
            ];
        }

        DB::commit();

        return [
            'msg' => '查询成功',
            'err' => 0
        ];
    }

    /**
     * 订单查询客房续住价格
     *
     * @param Request $request
     * @return array
     */
    public function toFindProceedPrice(Request $request)
    {
        $oid = $request->get('oid');

        if (empty($oid)) {
            return [
                'msg' => '订单号不能为空',
                'err' => 404
            ];
        }

        $order = $this->findWithOid($request, true);

        if (empty($order)) {
            return [
                'msg' => '未找到相关订单',
                'err' => 404
            ];
        }

        $rid = $request->get('rid');

        if (empty($rid)) {
            return [
                'msg' => '房间号不能为空',
                'err' => 404
            ];
        }

        $Live = new Live();

        $live = $Live->toFind($this->auth['hid'], $order['oid'], $rid);

        if (empty($live)) {
            return [
                'msg' => '未查询到相关入住记录',
                'err' => 404
            ];
        }


        $condition['tid'] = $live->tid;
        $condition['channel'] = $order->channel;
        $condition['cid'] = $order->company;
        $condition['vip'] = $order->vip;

        $date = $request->get('date');

        $dateTime = strtotime($date);

        if (empty($dateTime)) {
            return [
                'msg' => '续住日期不能为空 或 格式错误',
                'err' => 422
            ];
        }

        $date = date('Ymd', $dateTime);

        $dateTime = strtotime($date);

        if (strtotime($live->end) > $dateTime) {
            return [
                'msg' => '续住日期不能小于酒店当前日期',
                'err' => 422
            ];
        } else if ($live->end == $date) {

            $date = date('Ymd', $dateTime + 86400);
        }

        $Price = new Price();

        $price = $Price->toGet($this->auth['hid'], $condition, $live->end, $date);

        $count = 0;

        foreach ($price as $key => $value) {
            $count += $value->price / 100;
            $tmpArr = [];

            $tmpArr['price'] = $value->price / 100;
            $tmpArr['date'] = mb_substr($value->date, 0, 4) . '-' . mb_substr($value->date, 4, 2) . '-' . mb_substr($value->date, 6, 2);

            $price[$key] = $tmpArr;
        }

        return [
            'msg' => '查询成功',
            'data' => [
                'count' => $count,
                'info' => $price,
                'start' => mb_substr($live->end, 0, 4) . '-' . mb_substr($live->end, 4, 2) . '-' . mb_substr($live->end, 6, 2),
                'end' => mb_substr($date, 0, 4) . '-' . mb_substr($date, 4, 2) . '-' . mb_substr($date, 6, 2)
            ],
            'err' => 0
        ];
    }

    /**
     * 订单房间换房
     *
     * @param Request $request
     * @return array
     */
    public function change(Request $request)
    {
        $date = date('Ymd', strtotime(Hotel::toGetTime($this->auth['hid'])));

        $oid = $request->get('oid');

        if (empty($oid)) {
            return [
                'msg' => '订单号不能为空',
                'err' => 404
            ];
        }

        $validator = $this->Order->toValidatorChange($request);

        if (!empty($validator)) {

            return $validator;
        }

        $order = $this->findWithOid($request, true);

        if (empty($order)) {
            return [
                'msg' => '未找到相关订单',
                'err' => 404
            ];
        }

        $before = $request->get('before');
        $after = $request->get('after');

        $Live = new Live();

        $live = $Live->toFind($this->auth['hid'], $order['oid'], $before);

        if (empty($live)) {
            return [
                'msg' => '未查询到相关入住记录',
                'err' => 404
            ];
        }

        $Room = new Room();

        $room = $Room->toSimpleFind($after, $this->auth['hid']);

        if (empty($room)) {

            return [
                'msg' => '未找到所换房间',
                'err' => 404
            ];
        }

        $condition['tid'] = $live->tid;
        $condition['channel'] = $order->channel;
        $condition['cid'] = $order->company;
        $condition['vip'] = $order->vip;

        DB::beginTransaction();

        //  查询是否有库存
        $StockController = new StockController();

        $stock = $StockController->findStock($room->tid, $date, $live->end);

        if (empty($stock['stock'])) {
            return [
                'msg' => '客房已售罄',
                'err' => 422
            ];
        }

        $isNull = true;

        foreach ($stock['room'] as $value) {

            if ($value['rid'] == $after) {
                $isNull = false;
            }
        }

        if ($isNull) {

            return [
                'msg' => '客房已出租',
                'err' => 422
            ];
        }

        $isChange = false;

        if (!empty($request->get('change_price'))) {

            $isChange = true;
        }

        $day = 0;

        for ($i = strtotime($date); $i < strtotime($live->end); $i += 86400) {
            $day++;
        }

        $beforePrice = json_decode($live->find_price, true);
        $afterPrice = [];

        $inputLive = [];

        $inputLive['tid'] = $room->tid;
        $inputLive['rid'] = $room->rid;

        $inputChange = [];

        $inputChange['hid'] = $this->auth['hid'];
        $inputChange['lid'] = $live->lid;
        $inputChange['oid'] = $order->oid;
        $inputChange['uid'] = $live->uid;
        $inputChange['before_tid'] = $live->tid;
        $inputChange['after_tid'] = $room->tid;
        $inputChange['before_rid'] = $before;
        $inputChange['after_rid'] = $room->rid;
        $inputChange['change_price'] = ($isChange && $live->tid !== $room->tid) ? 1 : 0;
        $inputChange['before_price'] = $live->find_price;
        $inputChange['after_price'] = $live->find_price;
        $inputChange['remark'] = $request->get('remark');

        $Value = new Value();

        if ($isChange && $live->tid !== $room->tid) {

            $Price = new Price();

            $price = $Price->toGet($this->auth['hid'], $condition, $date, $live->end);

            $findPrice = $beforePrice;

            foreach ($findPrice as $key => $value) {

                if ($value['date'] >= $date) {
                    unset($findPrice[$key]);
                }
            }

            $inputValue = [];

            foreach ($price as $value) {

                $findPrice[] = $value;


                $tmpArr = [];

                $tmpArr['hid'] = $this->auth['hid'];
                $tmpArr['oid'] = $order->oid;
                $tmpArr['rid'] = $room->rid;
                $tmpArr['tid'] = $room->tid;
                $tmpArr['price'] = $value['price'];
                $tmpArr['date'] = $value['date'];
                $tmpArr['type'] = 0;
                $tmpArr['status'] = 0;
                $tmpArr['description'] = '换房后价格';

                $inputValue[] = $tmpArr;
            }

            $afterPrice = $findPrice;

            $inputLive['find_price'] = json_decode($afterPrice, JSON_UNESCAPED_UNICODE);
            $inputChange['after_price'] = json_decode($afterPrice, JSON_UNESCAPED_UNICODE);

            if ($day != $Value->toDelete($live->tid, $this->auth['hid'], $before, $oid, $date)) {
                DB::rollBack();
                return [
                    'msg' => '原房价删除失败',
                    'err' => 422
                ];
            }

            $value = $Value->toInsert($inputValue);

            if (empty($value)) {

                DB::rollBack();
                return [
                    'msg' => '每日房价生成失败, 请稍后重试',
                    'err' => 422
                ];
            }

            //  更新原房型库存

            $Stock = new Stock();

            if ($day != $Stock->toDecrementLive($this->auth['hid'], $live->tid, $date, $live->end, 1)) {
                DB::rollBack();
                return [
                    'msg' => '原房型库存更新失败',
                    'err' => 422
                ];
            }

            if ($day != $Stock->toIncrementLive($this->auth['hid'], $room->tid, $date, $live->end, 1)) {

                DB::rollBack();
                return [
                    'msg' => '日库存更新失败',
                    'err' => 422,
                ];
            }

        } else {

            if ($day != $Value->toUpdate($live->tid, $this->auth['hid'], $before, $room->rid, $oid, $date)) {
                DB::rollBack();
                return [
                    'msg' => '原房价删除失败',
                    'err' => 422
                ];
            }
        }

        try {

            //  更新原房间状态
            $num = $Room->toUpdateStatus($this->auth['hid'], $before, 3);

            if (empty($num)) {
                DB::rollBack();
                return [
                    'msg' => '房型状态更新失败',
                    'err' => 422
                ];
            }
            //  更新先房间状态

            $num = $Room->toUpdateStatus($this->auth['hid'], $room->rid, 2);

            if (empty($num)) {
                DB::rollBack();
                return [
                    'msg' => '房型状态更新失败',
                    'err' => 422
                ];
            }

        } catch (\Exception $e) {

            DB::rollBack();
            return [
                'msg' => '房间状态更新失败',
                'err' => 500,
            ];
        }

        try {

            $Live->toUpdateChange($live, $inputLive);

        } catch (\Exception $e) {

            DB::rollBack();
            return [
                'msg' => '入住信息修改失败',
                'err' => 500
            ];
        }

        $Change = new Change();

        $change = $Change->toCreate($inputChange);

        if (empty($change)) {
            DB::rollBack();
            return [
                'msg' => '换房信息写入失败',
                'err' => 500
            ];
        }
        $Guest = new Guest();
        try {
            $Guest->toUpdateRid($this->auth->hid, $oid, $before, $after);
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'msg' => '宾客信息更新失败',
                'err' => 500
            ];
        }

        DB::commit();

        return [
            'msg' => '查询成功',
            'err' => 0
        ];
    }


    public function receipt(Request $request)
    {
        $oid = $request->get('oid');
        $payment = $request->get('payment');

        if (empty($oid)) {
            return [
                'msg' => '订单号不能为空',
                'err' => 404
            ];
        }

        $order = $this->findWithOid($request, true);

        if (empty($order)) {
            return [
                'msg' => '订单不存在',
                'err' => 404
            ];
        }

        if ($order->status != 2 && $order->status != 11) {
            return [
                'msg' => '订单非入住状态订单, 不能退房',
                'err' => 422
            ];
        }

        $Pay = new Pay();

        $receipt = [];

        foreach ($payment as $value) {

            $pay = $Pay->toFind($this->auth['hid'], $value['pid']);

            if (empty($pay)) {

                return [
                    'msg' => '禁止不存在的付款方式',
                    'err' => 422,
                ];
            }

            $tmpArr['hid'] = $this->auth['hid'];
            $tmpArr['oid'] = $order->oid;
            $tmpArr['pid'] = $pay->pid;
            $tmpArr['pay'] = $pay->name;
            $tmpArr['price'] = $value['price'];
            $tmpArr['time'] = time();
            $tmpArr['priority'] = $pay->type;

            $receipt[] = $tmpArr;
        }

        $Receipt = new Receipt();

        $receipt = $Receipt->toCreate($receipt);

        if (empty($receipt)) {

            DB::rollBack();
            return [
                'msg' => '入款失败',
                'err' => 422,
            ];
        }


        return [
            'msg' => '入款成功',
            'err' => 0,
        ];
    }

    /**
     * 订单挂账
     *
     * @param Request $request
     * @return array
     */
    public function debts(Request $request)
    {
        $oid = $request->get('oid');

        if (empty($oid)) {
            return [
                'msg' => '订单号不能为空',
                'err' => 404
            ];
        }

        $order = $this->Order->toFindOfficialOrderWithOid($oid, $this->auth['hid']);

        if (empty($order)) {
            return [
                'msg' => '订单不存在',
                'err' => 404
            ];
        }

        if ($order->status != 2) {
            return [
                'msg' => '订单非入住状态订单, 不能挂账',
                'err' => 422
            ];
        }

        DB::beginTransaction();

        $num = $this->Order->toUpdateLeave($oid, $this->auth['hid']);

        if (empty($num)) {
            DB::rollBack();
            return [
                'msg' => '订单挂账失败',
                'err' => 422
            ];
        }

        $num = $this->Order->toUpdateStatus(9, $oid, $this->auth['hid']);

        if (empty($num)) {
            DB::rollBack();
            return [
                'msg' => '订单挂账失败',
                'err' => 422
            ];
        }
        $free = $this->toFreeStock($oid);

        if (!empty($free)) {

            $Stock = new Stock();

            foreach ($free as $value) {

                if (empty($Stock->toDecrementLive($this->auth['hid'], $value['date'], $value['tid'], $value['free_stock']))) {

                    DB::rollBack();

                    return [
                        'msg' => '日库存释放失败',
                        'err' => 422
                    ];
                }
            }
        }

        //  房间入住状态更新为退房状态
        $Live = new Live();

        if ($Live->toCountQuit($this->auth['hid'], $oid) != $Live->toQuit($this->auth['hid'], $oid)) {

            DB::rollBack();
            return [
                'msg' => '房间退房失败',
                'err' => 422
            ];
        }

        //  更新房间状态

        $ridArr = $Live->toGetRid($this->auth['hid'], $oid);

        try {
            $Room = new Room();

            foreach ($ridArr as $value) {

                if (empty($Room->toUpdateStatus($this->auth['hid'], $value['rid'], 3))) {

                    DB::rollBack();
                    return [
                        'msg' => '房间状态更新失败',
                        'err' => 500
                    ];
                }
            }
        } catch (\Exception $e) {

            DB::rollBack();
            return [
                'msg' => '房间状态更新失败',
                'err' => 500
            ];
        }

        DB::commit();
        return [
            'msg' => '查询成功',
            'err' => 0
        ];

    }

    /**
     * 订单退房 (单间退房)
     *
     * @param Request $request
     * @return array
     */
    public function quit(Request $request)
    {
        $oid = $request->get('oid');

        $rid = $request->get('rid', 0);

        $refunds = $request->get('refunds');

        if (empty($oid)) {
            return [
                'msg' => '订单号不能为空',
                'err' => 404
            ];
        }

        $order = $this->findWithOid($request, true);

        if (empty($order)) {
            return [
                'msg' => '订单不存在',
                'err' => 404
            ];
        }

        if ($order->status != 2) {
            return [
                'msg' => '订单非入住状态订单, 不能退房',
                'err' => 422
            ];
        }

        $Live = new Live();

        $Room = new Room();

        $Value = new Value();

        DB::beginTransaction();

        if (!empty($rid)) {
            //  订单单房间退房
            $live = $Live->toFind($this->auth['hid'], $order->oid, $rid);

            if (empty($live)) {

                return [
                    'msg' => '未查询到相关房间入住记录',
                    'err' => 404
                ];
            }

            try {
                if ($live->end > $this->date) {

                    $startTime = strtotime($this->date);
                    $endTime = strtotime($live->end);

                    $num = 0;

                    for ($i = $startTime; $i < $endTime; $i += 86400) {
                        $num++;
                    }

                    $change = $Value->toBetchUpdateStatus($this->auth['hid'], $live->end, $this->date, 2);

                    if ($num != $change) {

                        DB::rollBack();
                        return [
                            'msg' => '订单状态更新失败',
                            'err' => 422
                        ];
                    }

                }

                try {
                    $Room->toUpdateStatus($this->auth['hid'], $rid, 3);
                } catch (\Exception $e) {
                    DB::rollBack();
                    return [
                        'msg' => '房间状态更新失败',
                        'err' => 422
                    ];
                }

            } catch (\Exception $e) {

                DB::rollBack();
                return [
                    'msg' => '房间状态更新失败',
                    'err' => 500
                ];
            }

        } else {
            $ridArr = $Live->toGetRid($this->auth['hid'], $oid);

            try {

                foreach ($ridArr as $value) {

                    if ($value['end'] > $this->date) {

                        $startTime = strtotime($this->date);
                        $endTime = strtotime($value['end']);

                        $num = 0;

                        for ($i = $startTime; $i < $endTime; $i += 86400) {
                            $num++;
                        }

                        $change = $Value->toBetchUpdateStatus($this->auth['hid'], $value['rid'], $this->date, 2);

                        if ($num != $change) {

                            DB::rollBack();
                            return [
                                'msg' => '订单状态更新失败',
                                'err' => 422
                            ];
                        }

                    }

                    try {
                        $Room->toUpdateStatus($this->auth['hid'], $value['rid'], 3);
                    } catch (\Exception $e) {
                        DB::rollBack();
                        return [
                            'msg' => '房间状态更新失败',
                            'err' => 422
                        ];
                    }
                }
            } catch (\Exception $e) {

                DB::rollBack();
                return [
                    'msg' => '房间状态更新失败',
                    'err' => 500
                ];
            }
        }
        //  订单所有房间退房

        $free = $this->toFreeStock($oid, $rid);

        if (!empty($free)) {

            $Stock = new Stock();

            foreach ($free as $value) {

                if (empty($Stock->toDecrementLive($this->auth['hid'], $value['date'], $value['tid'], $value['free_stock']))) {

                    DB::rollBack();

                    return [
                        'msg' => '日库存释放失败',
                        'err' => 422
                    ];
                }
            }
        }

        //  房间入住状态更新为退房状态
        if ($Live->toCountQuit($this->auth['hid'], $oid, $rid) != $Live->toQuit($this->auth['hid'], $oid, $rid)) {

            DB::rollBack();
            return [
                'msg' => '房间退房失败',
                'err' => 422
            ];
        }

        //  今日离开扣款
        $ReceiptController = new ReceiptController();

        $toGetQuitFinance = $ReceiptController->toGetQuitFinance($order->oid, $rid);

        $values = $ReceiptController->values;

        $Value = new Value();

        try {

            foreach ($values as $value) {

                $num = $Value->toUpdateStatus($this->auth['hid'], $value['vid'], $this->date);

                if (empty($num)) {
                    DB::rollBack();
                    return [
                        'msg' => '入住信息更新失败',
                        'err' => 422
                    ];
                }
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'msg' => '收款日志更新失败',
                'err' => 500
            ];
        }

        if (!empty($toGetQuitFinance['pay'])) {
            return [
                'msg' => '账户余额不足',
                'err' => 422
            ];
        }

        //  更新账户余额信息
        $Receipt = new Receipt();

        $date = date('Ymd', strtotime($this->date));

        try {

            $finance = [];

            foreach ($toGetQuitFinance['deduction'] as $value) {

                if (!isset($value['deduction'])) {
                    continue;
                }

                $num = $Receipt->toUpDeduction($value['rid'], $value['deduction']);

                if (empty($num)) {

                    DB::rollBack();
                    return [
                        'msg' => '账户扣款失败',
                        'err' => 422
                    ];
                }

                $tmpArr = [];

                $tmpArr['hid'] = $this->auth['hid'];
                $tmpArr['oid'] = $order->oid;
                $tmpArr['rid'] = $value['rid'];
                $tmpArr['pay'] = $value['pid'];
                $tmpArr['price'] = $value['deduction'];
                $tmpArr['remark'] = $value['remark'];
                $tmpArr['date'] = $date;
                $tmpArr['time'] = time();

                $finance[] = $tmpArr;
            }

            $Finance = new Finance();

            $inRes = $Finance->toInsert($finance);

            if (!$inRes) {

                DB::rollBack();
                return [
                    'msg' => '酒店财务写入失败',
                    'err' => 422
                ];
            }

        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'msg' => '账户扣款失败',
                'err' => 500
            ];
        }

        //  处理退款

        $refund = $Receipt->toGetRefunds($this->auth['hid'], $order->oid);

        $dealRefund = [];

        foreach ($refund as $value) {

            $tmpArr = [];
            if (empty($dealRefund)) {

                $tmpArr['pid'] = $value['pid'];
                $tmpArr['refunds'] = $value['refunds'];

                $dealRefund[] = $tmpArr;

                continue;
            } else {

                foreach ($dealRefund as $k => $v) {

                    if ($value['pid'] == $v['pid']) {

                        $dealRefund[$k]['refunds'] += $value['refunds'];
                    } else {

                        $tmpArr['pid'] = $value['pid'];
                        $tmpArr['refunds'] = $value['refunds'];

                        $dealRefund[] = $tmpArr;
                    }
                }
            }
        }

        $cash = [];
        $refundLog = [];

        if ($refund->isNotEmpty()) {

            try {

                foreach ($refund as $value) {

                    if (!empty($refunds)) {

                        foreach ($refunds as $val) {

                            if ($value['pid'] == $val) {

                                continue;
                            }
                        }
                    }
                    $tmpArr = [];
                    $tmpArr['hid'] = $this->auth['hid'];
                    $tmpArr['oid'] = $oid;
                    $tmpArr['pid'] = $value['pid'];
                    $tmpArr['pay'] = $value['pay'];
                    $tmpArr['refunds'] = $value['refunds'];
                    $tmpArr['remark'] = '正常退款';
                    $tmpArr['time'] = time();
                    $refundLog [] = $tmpArr;
                    $Receipt->toUpdateRefunds($this->auth['hid'], $oid, $value['pid'], $value['refunds'], $value['rid']);
                }

            } catch (\Exception $e) {
                DB::rollBack();
                return [
                    'msg' => '退款失败',
                    'err' => 500
                ];
            }
        }

        if (!empty($refunds)) {

            foreach ($dealRefund as $value) {

                foreach ($refunds as $val) {

                    if ($value['pid'] == $val) {
                        $cash[] = $value;
                        continue;
                    }
                }
            }
        }

        if (!empty($cash)) {

            $cashPay = Pay::toGetCash($this->auth['hid']);

            try {

                $finance = [];

                foreach ($cash as $value) {

                    $tmpArr = [];

                    $tmpArr['hid'] = $this->auth['hid'];
                    $tmpArr['oid'] = $order->oid;
                    $tmpArr['rid'] = 0;
                    $tmpArr['pay'] = $value['pid'];
                    $tmpArr['price'] = $value['refunds'];
                    $tmpArr['remark'] = '现金退款, 系统入账';
                    $tmpArr['date'] = $date;
                    $tmpArr['time'] = time();

                    $finance[] = $tmpArr;

                    $tmpArr['pay'] = $cashPay;
                    $tmpArr['price'] = 0 - $value['refunds'];
                    $tmpArr['remark'] = '现金退款, 系统出账';

                    $finance[] = $tmpArr;


                    $tmpArr = [];
                    $tmpArr['hid'] = $this->auth['hid'];
                    $tmpArr['oid'] = $oid;
                    $tmpArr['pid'] = $value['pid'];
                    $tmpArr['pay'] = $value['pay'];
                    $tmpArr['refunds'] = $value['refunds'];
                    $tmpArr['remark'] = '正常退款';
                    $tmpArr['time'] = time();

                    $refundLog [] = $tmpArr;
                }

                $Finance = new Finance();

                $inRes = $Finance->toInsert($finance);

                if (!$inRes) {

                    DB::rollBack();
                    return [
                        'msg' => '酒店财务写入失败',
                        'err' => 422
                    ];
                }

            } catch (\Exception $e) {
                DB::rollBack();

                return [
                    'msg' => '账户扣款失败',
                    'err' => 500
                ];
            }
        }

        if (!empty($refundLog)) {
            $Refund = new Refund();

            $inRes = $Refund->toInsert($refundLog);

            if (!$inRes) {

                DB::rollBack();
                return [
                    'msg' => '退款记录写入失败',
                    'err' => 422
                ];
            }
        }

        $isOrderLive = $Live->toCountLive($this->auth['hid'], $order->oid);

        if (empty($isOrderLive)) {

            $num = $this->Order->toUpdateStatus(7, $order->oid, $this->auth['hid']);

            if (empty($num)) {

                DB::rollBack();
                return [
                    'msg' => '订单退房失败',
                    'err' => 422
                ];
            }
        }

        DB::commit();

        return [
            'msg' => '查询成功',
            'err' => 0
        ];
    }

    /**
     * 订单查询 (第三方订单查询)
     *
     * @param Request $request
     * @param bool $isController
     * @return array|\Illuminate\Database\Eloquent\Model|null|static
     */
    public function findWithOid(Request $request, $isController = false)
    {
        $data = [];

        $oid = $request->get('oid');

        if (empty($oid)) {

            return [
                'msg' => '订单号不能为空',
                'err' => '404'
            ];
        }

        if ($this->auth['id'] == $this->official->key) {

            $channel = $request->get('channel', $this->official->key);

            if ($channel != $this->official->key) {

                $order = $this->Order->toFindOtherOrderWithOid($channel, $oid, $this->auth['hid']);

                if (empty($order)) {

                    return [
                        'msg' => '未找到相关订单',
                        'err' => 404
                    ];
                }

                if ($isController) {

                    return $order;
                } else {

                    $data['oid'] = $order->oid_other;
                    $data['operate'] = $order->key_name;
                    $data['uid'] = $order->uid;
                    $data['time'] = $order->init;
                    $data['channel'] = $order->channel;
                    $data['channel_name'] = $order->channel_name;
                    $data['company'] = $order->company;
                    $data['company_name'] = $order->company_name;
                    $data['vip'] = $order->vip;
                    $data['vip_name'] = $order->vip_name;
                    $data['ordain'] = $order->ordain;
                    $data['remark'] = $order->remark;
                    $data['status'] = $order->status;
                    $data['type'] = $order->type;
                }

            } else {

                $order = $this->Order->toFindOfficialOrderWithOid($oid, $this->auth['hid']);

                if ($isController) {

                    return $order;
                } else {

                    $data['oid'] = $order->oid;
                    $data['operate'] = $order->key_name;
                    $data['uid'] = $order->uid;
                    $data['time'] = $order->init;
                    $data['channel'] = $order->channel;
                    $data['channel_name'] = $order->channel_name;
                    $data['company'] = $order->company;
                    $data['company_name'] = $order->company_name;
                    $data['vip'] = $order->vip;
                    $data['vip_name'] = $order->vip_name;
                    $data['ordain'] = $order->ordain;
                    $data['remark'] = $order->remark;
                    $data['status'] = $order->status;
                    $data['type'] = $order->type;
                }
            }

        } else {
            //  非官方渠道查询订单

            $order = $this->Order->toFindOtherOrderWithOid($this->auth['id'], $oid, $this->auth['hid']);

            if (empty($order) && !empty(intval($oid))) {

                $order = $this->Order->toFindOfficialOrderWithOid($oid, $this->auth['hid']);

                if ($isController) {

                    return $order;
                } else {

                    $data['oid'] = $order->oid;
                    $data['oid_channel'] = $order->oid_other;
                    $data['uid'] = $order->uid;
                    $data['time'] = $order->init;
                    $data['remark'] = $order->remark;
                    $data['status'] = $order->status;
                }
            } else {

                if ($isController) {

                    return $order;
                } else {

                    $data['oid'] = $order->oid;
                    $data['oid_channel'] = $order->oid_other;
                    $data['uid'] = $order->uid;
                    $data['time'] = $order->init;
                    $data['remark'] = $order->remark;
                    $data['status'] = $order->status;
                }
            }
        }

        return [
            'msg' => '查询成功',
            'data' => $data,
            'err' => 0
        ];
    }

    /**
     * 查询订单需要释放的房型库存
     *
     * @param $oid
     * @param int $rid 可选: 存在是释放当前所选房间房型库存, 不存在时释放整个订单房型库存
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    private function toFreeStock($oid, $rid = 0)
    {
        $date = Hotel::toGetTime($this->auth['hid']);

        $Value = new Value();

        $free = $Value->toGetFreeStock($this->auth['hid'], $oid, $date, $rid);

        return $free;
    }
}