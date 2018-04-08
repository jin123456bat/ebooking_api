<?php

namespace App\Http\Controllers\Router;

use App\Http\Controllers\Controller;
use App\Models\Finance;
use App\Models\Hotel;
use App\Models\Live;
use App\Models\Receipt;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    private $date;
    private $auth;

    public function __construct()
    {
        $this->auth = Auth::user();
        $this->date = Hotel::toGetTime($this->auth['hid']);
    }

    public function create(Request $request)
    {
        $rid = (int)$request->get('rid');

        $type = (int)$request->get('type');

        $group = (int)$request->get('group');

        $date = $request->get('date');

        $price = (int)$request->get('price');

        $remark = $request->get('remark');

        if (empty($rid)) {
            return [
                'msg' => '账务所属房间不能为空',
                'err' => 42201
            ];
        }

        if ($type < 11 || $type > 27) {
            return [
                'msg' => '账务类型格式错误',
                'err' => 42201
            ];
        }

        if ($group < 1 || $group > 7) {
            return [
                'msg' => '账务分组格式错误',
                'err' => 42201
            ];
        }

        $dateNum = strtotime($date);

        if (empty($dateNum)) {
            return [
                'msg' => '账务分组格式错误',
                'err' => 42201
            ];
        }

        if ($dateNum > strtotime($this->date)) {
            return [
                'msg' => '入账最大日期即为酒店当前日期',
                'err' => 42201
            ];
        }

        if (empty($price)) {
            return [
                'msg' => '入账价格不能为空',
                'err' => 42203
            ];
        }

        if (empty($remark)) {
            return [
                'msg' => '入账原因不能为空',
                'err' => 42203
            ];
        }

        if (strlen($remark) > 200) {
            return [
                'msg' => '入账原因最多 200 个字符',
                'err' => 42203
            ];
        }

        $date = date('Ymd', $dateNum);

        $Live = new Live();

        $live = $Live->toFindLiveWithRid($this->auth['hid'], $rid);

        if (empty($live)) {
            return [
                'msg' => '未找到相关入住房间',
                'err' => 404
            ];
        }

        $Receipt = new Receipt();

        $receipt = $Receipt->toGetLast($this->auth['hid'], $live->oid);

        if (empty($receipt)) {
            return [
                'msg' => '未找到相关付款信息',
                'err' => 404
            ];
        }

        $mark = false;

        $deduction = [];

        foreach ($receipt as $key => $item) {

            if (isset($item->status) && $item->status == 1) {
                continue;
            }

            $i = $item->surplus - $price;

            if ($i < 0) {

                $receipt[$key]->deduction = $item->surplus;
                $receipt[$key]->status = 1;

                $tmpArr = [];

                $tmpArr['pay'] = $item->pay;
                $tmpArr['deduction'] = $item->surplus;
                $tmpArr['remark'] = '房间：' . $live->no . '（' . substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6, 2) . '）' . $remark . ' ' . number_format($price / 100, 2) . ' 元';

                $deduction[] = $tmpArr;

                $receipt[$key]->remark = $tmpArr['remark'];

                $price = 0 - $i;
            } else if ($i > 0) {

                $receipt[$key]->deduction = $price;
                $receipt[$key]->status = 0;

                $tmpArr = [];

                $tmpArr['pay'] = $item->pay;
                $tmpArr['deduction'] = $price;

                if ($mark) {
                    $tmpArr['remark'] = '房间(补扣)：' . $live->no . '（' . substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6, 2) . '）' . $remark . ' ' . number_format($price / 100, 2) . ' 元';
                } else {
                    $tmpArr['remark'] = '房间：' . $live->no . '（' . substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6, 2) . '）' . $remark . ' ' . number_format($price / 100, 2) . ' 元';
                }

                $receipt[$key]->remark = $tmpArr['remark'];
                $deduction[] = $tmpArr;

                $price = 0;
                break;
            } else {

                $receipt[$key]->deduction = $price;
                $receipt[$key]->status = 1;

                $tmpArr = [];

                $tmpArr['pay'] = $item->pay;
                $tmpArr['deduction'] = $price;

                if ($mark) {
                    $tmpArr['remark'] = '房间(补扣)：' . $live->no . '（' . substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6, 2) . '）' . $remark . ' ' . number_format($price / 100, 2) . ' 元';
                } else {
                    $tmpArr['remark'] = '房间：' . $live->no . '（' . substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6, 2) . '）' . $remark . ' ' . number_format($price / 100, 2) . ' 元';
                }

                $receipt[$key]->remark = $tmpArr['remark'];
                $deduction[] = $tmpArr;

                $price = 0;
                break;
            }

            $mark = true;
        }

        if (!empty($price)) {
            return [
                'msg' => '付款金额不足，暂未扣款成功',
                'err' => 422
            ];
        }

        $typeNum = $type * 100 + $group;

        DB::beginTransaction();

        try {

//            $dateFormat = substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6, 2);
            $finance = [];

            foreach ($receipt as $value) {

                if (!isset($value->deduction)) {
                    continue;
                }

                $num = $Receipt->toUpDeduction($value->rid, $value->deduction);

                if (empty($num)) {

                    DB::rollBack();
                    return [
                        'msg' => '账户扣款失败',
                        'err' => 422
                    ];
                }

                $tmpArr = [];

                $tmpArr['hid'] = $this->auth['hid'];
                $tmpArr['oid'] = $live->oid;
                $tmpArr['rid'] = $live->rid;
                $tmpArr['pay'] = $value->pid;
                $tmpArr['price'] = $value->deduction;
                $tmpArr['remark'] = $value->remark;
                $tmpArr['date'] = $date;
                $tmpArr['time'] = time();
                $tmpArr['type'] = $typeNum;

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

        DB::commit();
        return [
            'msg' => '查询成功',
            'err' => 0
        ];


    }

    public function toGetJHDB(Request $request)
    {
        $date = $request->get('date');

        $dateNum = strtotime($date);

        if (empty($dateNum)) {
            return [
                'msg' => '日期不能为空',
                'err' => 40401
            ];
        }

        if ($dateNum >= strtotime($this->date)) {
            return [
                'msg' => '查询日期不能大于酒店系统当前时间',
                'err' => 42201
            ];
        }

        $Finance = new Finance();

        $finance = $Finance->toGetWithDay(date('Ymd', $dateNum), $this->auth['hid']);

        return [
            'msg' => '查询成功',
            'data' => [
                'income' => $finance
            ],
            'err' => 0
        ];
    }

    public function toGetQTRZMXB(Request $request)
    {
        $date = $request->get('date');

        $dateNum = strtotime($date);

        if (empty($dateNum)) {
            return [
                'msg' => '日期不能为空',
                'err' => 40401
            ];
        }

        if ($dateNum >= strtotime($this->date)) {
            return [
                'msg' => '查询日期不能大于酒店系统当前时间',
                'err' => 42201
            ];
        }

        $Finance = new Finance();

        $date = date('Ymd', $dateNum);

        $consume = $Finance->toGetConsumeWithDay($date, $this->auth['hid']);

        $income = $Finance->toGetIncome($date, $this->auth['hid']);

        $ar = $Finance->toGetAR($date, $this->auth['hid']);

        return [
            'msg' => '查询成功',
            'data' => [
                'consume' => $consume,
                'income' => $income,
                'ar' => $ar
            ],
            'err' => 0
        ];
    }

    public function toGetQTSKMXB(Request $request)
    {
        $date = $request->get('date');

        $dateNum = strtotime($date);

        if (empty($dateNum)) {
            return [
                'msg' => '日期不能为空',
                'err' => 40401
            ];
        }

        if ($dateNum >= strtotime($this->date)) {
            return [
                'msg' => '查询日期不能大于酒店系统当前时间',
                'err' => 42201
            ];
        }

        $Finance = new Finance();

        $date = date('Ymd', $dateNum);

//        $consume = $Finance->toGetConsumeWithDay($date, $this->auth['hid']);

        $income = $Finance->toGetIncome($date, $this->auth['hid']);

        $ar = $Finance->toGetAR($date, $this->auth['hid']);

        return [
            'msg' => '查询成功',
            'data' => [
//                'consume' => $consume,
                'income' => $income,
                'ar' => $ar
            ],
            'err' => 0
        ];
    }

    public function toGetARRZMXB(Request $request)
    {
        $date = $request->get('date');

        $dateNum = strtotime($date);

        if (empty($dateNum)) {
            return [
                'msg' => '日期不能为空',
                'err' => 40401
            ];
        }

        if ($dateNum >= strtotime($this->date)) {
            return [
                'msg' => '查询日期不能大于酒店系统当前时间',
                'err' => 42201
            ];
        }

        $Finance = new Finance();

        $date = date('Ymd', $dateNum);

        $ArZR = [];

        $ArZR = $Finance->toGetArRZ($this->auth['hid'], $date);

        return [
            'msg' => '查询成功',
            'data' => $ArZR,
            'err' => 0
        ];
    }

    public function toGetARGZMXB(Request $request)
    {
        $start = $request->get('start');
        $end = $request->get('end');

        $startNum = strtotime($start);
        $endNum = strtotime($end);

        if (empty($startNum)) {
            return [
                'msg' => '日期不能为空',
                'err' => 40401
            ];
        }

        if ($endNum >= strtotime($this->date)) {
            return [
                'msg' => '查询日期不能大于酒店系统当前时间',
                'err' => 42201
            ];
        }

        if ($startNum >= $endNum) {
            return [
                'msg' => '查询日期不能大于酒店系统当前时间',
                'err' => 42202
            ];
        }

        $Finance = new Finance();

//        $date = date('Ymd', $dateNum);

        $ArGZ = [];

        $ArGZ = $Finance->toGetArGZ($this->auth['hid'], $startNum, $endNum + 60 * 60 * 24);

        return [
            'msg' => '查询成功',
            'data' => $ArGZ,
            'err' => 0
        ];
    }
}