<?php

namespace App\Http\Controllers\Router;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\Receipt;
use App\Models\Value;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReceiptController extends Controller
{
    private $auth;

    private $date;

    public $values;

    public function __construct()
    {
        $this->auth = Auth::user();

        $this->date = Hotel::toGetTime($this->auth['hid']);
    }

    public function quit(Request $request, $isController = false)
    {
        $oid = $request->get('oid');

        $rid = $request->get('rid', 0);

        if (empty($oid)) {
            return [
                'msg' => '订单号不能为空',
                'err' => 404
            ];
        }

        $OrderController = new OrderController($request);

        $order = $OrderController->findWithOid($request, true);

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

        $Receipt = new Receipt();

        $receipt = $Receipt->toGet($this->auth['hid'], $order->oid, $rid);

        if ($receipt->isEmpty()) {

            return [
                'msg' => '未找到相关收款信息',
                'err' => 422
            ];
        }

        $Value = new Value();

        $values = $Value->toGetNowLeavePrice($this->auth['hid'], $order->oid, $this->date, $rid);

        $needPay = 0;

        $exes = 0;

        $deduction = [];

        $surplus = [];

        $date = date('Y-m-d', strtotime($this->date));

        foreach ($values as $val) {

            $exes += $val['price'];
            $price = $val['price'];

            $mark = false;

            foreach ($receipt as $key => $item) {

                if (isset($item['status']) || $item['status'] == 1) {
                    continue;
                }

                $i = $item['surplus'] - $price;

                if ($i < 0) {

                    $receipt[$key]['deduction'] = $item['surplus'];
                    $receipt[$key]['status'] = 1;

                    $tmpArr = [];

                    $tmpArr['pay'] = $item['pay'];
                    $tmpArr['deduction'] = $item['surplus'];
                    $tmpArr['remark'] = '房间: ' . $val['room_no'] . ' 扣除 ' . substr($val['date'], 0, 4) . '-' . substr($val['date'], 4, 2) . '-' . substr($val['date'], 6, 2) . ' 房费 ' . number_format($item['surplus'] / 100, 2) . ' 元';

                    $deduction[] = $tmpArr;

                    $price = 0 - $i;
                } else if ($i > 0) {

                    $receipt[$key]['deduction'] = $price;
                    $receipt[$key]['status'] = 0;

                    $tmpArr = [];

                    $tmpArr['pay'] = $item['pay'];
                    $tmpArr['deduction'] = $price;

                    if ($mark) {
                        $tmpArr['remark'] = '房间: ' . $val['room_no'] . ' 补扣 ' . substr($val['date'], 0, 4) . '-' . substr($val['date'], 4, 2) . '-' . substr($val['date'], 6, 2) . ' 房费 ' . number_format($price / 100, 2) . ' 元';
                    } else {
                        $tmpArr['remark'] = '房间: ' . $val['room_no'] . ' 扣除 ' . substr($val['date'], 0, 4) . '-' . substr($val['date'], 4, 2) . '-' . substr($val['date'], 6, 2) . ' 房费 ' . number_format($price / 100, 2) . ' 元';
                    }

                    $deduction[] = $tmpArr;

                    $price = 0;
                    break;
                } else {

                    $receipt[$key]['deduction'] = $price;
                    $receipt[$key]['status'] = 1;

                    $tmpArr = [];

                    $tmpArr['pay'] = $item['pay'];
                    $tmpArr['deduction'] = $price;

                    if ($mark) {
                        $tmpArr['remark'] = '房间: ' . $val['room_no'] . ' 补扣 ' . substr($val['date'], 0, 4) . '-' . substr($val['date'], 4, 2) . '-' . substr($val['date'], 6, 2) . ' 房费 ' . number_format($price / 100, 2) . ' 元';
                    } else {
                        $tmpArr['remark'] = '房间: ' . $val['room_no'] . ' 扣除 ' . substr($val['date'], 0, 4) . '-' . substr($val['date'], 4, 2) . '-' . substr($val['date'], 6, 2) . ' 房费 ' . number_format($price / 100, 2) . ' 元';
                    }

                    $deduction[] = $tmpArr;

                    $price = 0;
                    break;
                }

                $mark = true;
            }

            if ($price > 0) {
                $needPay += $price;
            }
        }

        foreach ($receipt as $item) {

            if (isset($item['status']) && $item['status'] == 1) {
                continue;
            }
            $tmpArr = [];
            if (isset($item['deduction'])) {
                $tmpArr['name'] = $item['pay'];
                $tmpArr['price'] = $item['surplus'] - $item['deduction'];

                $surplus[] = $tmpArr;
            } else {

                $tmpArr['name'] = $item['pay'];
                $tmpArr['price'] = $item['surplus'];

                $surplus[] = $tmpArr;
            }
        }

        return [
            'msg' => '财务查询成功',
            'data' => [
                'surplus' => $surplus,
                'exes' => $exes,
                'deduction' => $deduction,
                'pay' => $needPay,
            ],
            'err' => 0
        ];
    }

    public function toGetQuitFinance($oid, $rid = 0)
    {
        $Receipt = new Receipt();

        $receipt = $Receipt->toGet($this->auth['hid'], $oid);

        if ($receipt->isEmpty()) {

            return [
                'msg' => '未找到相关收款信息',
                'err' => 422
            ];
        }

        $Value = new Value();

        $values = $Value->toGetNowLeavePrice($this->auth['hid'], $oid, $this->date, $rid);

        $this->values = $values;

        $needPay = 0;

        $exes = 0;

        $date = date('Y-m-d', strtotime($this->date));

        foreach ($values as $val) {

            $exes += $val['price'];
            $price = $val['price'];

            $mark = false;

            foreach ($receipt as $key => $item) {

                if (isset($item['status']) || $item['status'] == 1) {
                    continue;
                }

                $i = $item['surplus'] - $price;

                if ($i < 0) {

                    $receipt[$key]['room_id'] = $val['rid'];
                    $receipt[$key]['deduction'] = $item['surplus'];
                    $receipt[$key]['status'] = 1;
                    $receipt[$key]['remark'] = '房间: ' . $val['room_no'] . ' 收取 ' . substr($val['date'], 0, 4) . '-' . substr($val['date'], 4, 2) . '-' . substr($val['date'], 6, 2) . ' 房费 ' . number_format($item['surplus'] / 100, 2) . ' 元';

                    $price = 0 - $i;
                } else if ($i > 0) {

                    $receipt[$key]['room_id'] = $val['rid'];
                    $receipt[$key]['deduction'] = $price;
                    $receipt[$key]['status'] = 0;

                    if ($mark) {
                        $receipt[$key]['remark'] = '房间: ' . $val['room_no'] . ' 补收 ' . substr($val['date'], 0, 4) . '-' . substr($val['date'], 4, 2) . '-' . substr($val['date'], 6, 2) . ' 房费 ' . number_format($price / 100, 2) . ' 元';
                    } else {
                        $receipt[$key]['remark'] = '房间: ' . $val['room_no'] . ' 收取 ' . substr($val['date'], 0, 4) . '-' . substr($val['date'], 4, 2) . '-' . substr($val['date'], 6, 2) . ' 房费 ' . number_format($price / 100, 2) . ' 元';
                    }

                    $price = 0;
                    break;
                } else {

                    $receipt[$key]['room_id'] = $val['rid'];
                    $receipt[$key]['deduction'] = $price;
                    $receipt[$key]['status'] = 1;

                    if ($mark) {
                        $receipt[$key]['remark'] = '房间: ' . $val['room_no'] . ' 补收 ' . substr($val['date'], 0, 4) . '-' . substr($val['date'], 4, 2) . '-' . substr($val['date'], 6, 2) . ' 房费 ' . number_format($price / 100, 2) . ' 元';
                    } else {
                        $receipt[$key]['remark'] = '房间: ' . $val['room_no'] . ' 收取 ' . substr($val['date'], 0, 4) . '-' . substr($val['date'], 4, 2) . '-' . substr($val['date'], 6, 2) . ' 房费 ' . number_format($price / 100, 2) . ' 元';
                    }

                    $price = 0;
                    break;
                }

                $mark = true;
            }

            if ($price > 0) {
                $needPay += $price;
            }
        }

        return [
            'exes' => $exes,
            'deduction' => $receipt,
            'pay' => $needPay,
        ];
    }

    public function toGetCensorFinance($value)
    {
        $Receipt = new Receipt();

        $receipt = $Receipt->toGet($this->auth['hid'], $value->oid);

        if ($receipt->isEmpty()) {

            return [
                'msg' => '订单 ' . $value->oid . ' 未找到相关收款信息',
                'err' => 422
            ];
        }

        $needPay = 0;

        $date = date('Y-m-d', strtotime($this->date));

        $price = $value->price;

        $mark = false;

        foreach ($receipt as $key => $item) {

            if (isset($item['status']) || $item['status'] == 1) {
                continue;
            }

            $i = $item['surplus'] - $price;

            if ($i < 0) {

                $receipt[$key]['room_id'] = $value->rid;
                $receipt[$key]['deduction'] = $item['surplus'];
                $receipt[$key]['status'] = 1;
                $receipt[$key]['date'] = $value->date;
                $receipt[$key]['remark'] = '房间: ' . $value->room_no . ' 收取 ' . substr($value->date, 0, 4) . '-' . substr($value->date, 4, 2) . '-' . substr($value->date, 6, 2) . ' 房费 ' . number_format($item['surplus'] / 100, 2) . ' 元';

                $price = 0 - $i;
            } else if ($i > 0) {

                $receipt[$key]['room_id'] = $value->rid;
                $receipt[$key]['deduction'] = $price;
                $receipt[$key]['status'] = 0;
                $receipt[$key]['date'] = $value->date;

                if ($mark) {
                    $receipt[$key]['remark'] = '房间: ' . $value->room_no . ' 补收 ' . substr($value->date, 0, 4) . '-' . substr($value->date, 4, 2) . '-' . substr($value->date, 6, 2) . ' 房费 ' . number_format($price / 100, 2) . ' 元';
                } else {
                    $receipt[$key]['remark'] = '房间: ' . $value->room_no . ' 收取 ' . substr($value->date, 0, 4) . '-' . substr($value->date, 4, 2) . '-' . substr($value->date, 6, 2) . ' 房费 ' . number_format($price / 100, 2) . ' 元';
                }

                $price = 0;
                break;
            } else {

                $receipt[$key]['room_id'] = $value->rid;
                $receipt[$key]['deduction'] = $price;
                $receipt[$key]['status'] = 1;
                $receipt[$key]['date'] = $value->date;

                if ($mark) {
                    $receipt[$key]['remark'] = '房间: ' . $value->room_no . ' 补收 ' . substr($value->date, 0, 4) . '-' . substr($value->date, 4, 2) . '-' . substr($value->date, 6, 2) . ' 房费 ' . number_format($price / 100, 2) . ' 元';
                } else {
                    $receipt[$key]['remark'] = '房间: ' . $value->room_no . ' 收取 ' . substr($value->date, 0, 4) . '-' . substr($value->date, 4, 2) . '-' . substr($value->date, 6, 2) . ' 房费 ' . number_format($price / 100, 2) . ' 元';
                }

                $price = 0;
                break;
            }

            $mark = true;
        }

        if ($price > 0) {
            $needPay += $price;
        }

        return [
            'exes' => $value->price,
            'deduction' => $receipt,
            'pay' => $needPay,
        ];
    }
}