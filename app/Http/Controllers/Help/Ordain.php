<?php

namespace App\Http\Controllers\Help;

use App\Models\Live;
use App\Models\Order;
use App\Models\Price\Price;
use App\Models\Receipt;
use App\Models\Stock;
use App\Models\Value;
use Illuminate\Support\Facades\DB;

class Ordain
{
    private $user;

    private $no;

    private $channel;

    private $channel_name;

    private $company;

    private $company_name;

    private $vip;

    private $vip_name;

    private $content;

    private $payment;

    private $remark;

    private $operate;

    private $uid;

    private $order;

    private $Price;

    private $value = [];

    public function __construct($user, $no, $channel, $channel_name, $company, $company_name, $vip, $vip_name, $content, $payment, $remark, $operate, $uid = null)
    {
        $this->user = $user;

        $this->no = $no;

        $this->channel = $channel;

        $this->channel_name = $channel_name;

        $this->company = $company;

        $this->company_name = $company_name;

        $this->vip = $vip;

        $this->vip_name = $vip_name;

        $this->content = $content;

        $this->payment = $payment;

        $this->remark = $remark;

        $this->operate = $operate;

        $this->uid = $uid;

        $this->Price = new Price();

    }

    public function ordain()
    {
        DB::beginTransaction();         //  开启事务

        //  生成订单
        $this->order = $this->toCreateOrder();

        if (empty($this->order)) {

            DB::rollBack();
            return [
                'msg' => '订单生成失败',
                'err' => 422,
            ];
        }

        $receipt = $this->toCreateReceipt();

        if (!$receipt) {

            DB::rollBack();
            return [
                'msg' => '收款信息生成失败',
                'err' => 422,
            ];
        }

        //  生成房型预定记录
        try {

            $live = $this->toCreateLive();

            if (!empty($live)) {

                DB::rollBack();
                return $live;
            }

        } catch (\Exception $e) {

            DB::rollBack();
            return [
                'msg' => '预定失败',
                'err' => 500,
            ];
        }

        //  更新库存
        try {

            $stock = $this->toUpdateStock();

            if (!empty($stock)) {

                return $stock;
            }

        } catch (\Exception $e) {

            DB::rollBack();
            return [
                'msg' => '库存更新修改失败',
                'err' => 500,
            ];
        }

        try {

            $Value = new Value();

            $value = $Value->toInsert($this->value);

            if (empty($value)) {

                DB::rollBack();
                return [
                    'msg' => '每日房价生成失败, 请稍后重试',
                    'err' => 422
                ];
            }

        } catch (\Exception $e) {

            DB::rollBack();
            return [
                'msg' => '库存更新修改失败',
                'err' => 500,
            ];
        }

        DB::commit();
    }

    public function toCreateOrder()
    {
        $input['oid_other'] = empty($this->no) ? null : $this->no;
        $input['key'] = $this->operate['id'];
        $input['key_name'] = $this->operate['name'];
        $input['hid'] = $this->operate['hid'];
        $input['uid'] = $this->uid ?? 0;
        $input['channel'] = $this->channel;
        $input['channel_name'] = $this->channel_name;
        $input['company'] = $this->company;
        $input['company_name'] = $this->company_name;
        $input['vip'] = $this->vip;
        $input['vip_name'] = $this->vip_name;
        $input['ordain'] = json_encode($this->user, JSON_UNESCAPED_UNICODE);
        $input['remark'] = $this->remark;
        $input['status'] = 0;
        $input['type'] = 0;

        $Order = new Order();

        return $order = $Order->toCreate($input);
    }

    public function toCreateReceipt()
    {
        if (!empty($this->payment)) {

            $Receipt = new Receipt();

            foreach ($this->payment as $key => $value) {
                $this->payment[$key]['oid'] = $this->order->oid;
                $this->payment[$key]['time'] = time();
            }

            return $Receipt->toCreate($this->payment);
        } else {

            return true;
        }
    }

    public function toUpdateStock()
    {
        $Stock = new Stock();

        foreach ($this->content as $value) {

            if (isset($value['type']) && $value['type'] == 1) {
                continue;   //  跳过库存
            }

            $stock = $Stock->toUpdateBooking($this->operate['hid'], $value['tid'], $value['start'], $value['end'], $value['num']);

            if ($stock != $value['day']) {

                DB::rollBack();
                return [
                    'msg' => '日库存更新失败',
                    'err' => 422,
                ];
            }
        }
    }

    public function toCreateLive()
    {
        $Live = new Live();

        foreach ($this->content as $key => $value) {

            $start = date('Ymd', strtotime($value['start']));
            $end = date('Ymd', strtotime($value['end']));
            $save = strtotime($value['save']);

            if ($start == $end) {

                $end = date('Ymd', strtotime($value['end']) + 86400);

                $this->content[$key]['end'] = $end;

                $this->content[$key]['type'] = 1;
                $value['type'] = 1;
                $this->content[$key]['day'] = 1;
                $value['day'] = 1;
            }

            $condition = [];

            $condition['channel'] = $this->channel;
            $condition['vip'] = $this->vip;
            $condition['tid'] = $value['tid'];
            $condition['cid'] = $this->company;

            $price = $this->toGetPrice($condition, $start, $end);

            if (is_string($price)) {

                return [
                    'msg' => $price,
                    'err' => 422,
                ];
            }
            for ($i = 0; $i < $value['num']; $i++) {

                $input['hid'] = $this->operate['hid'];
                $input['oid'] = $this->order->oid;
                $input['tid'] = $value['tid'];
                $input['uid'] = $this->uid;
                $input['rid'] = null;
                $input['money'] = 0;
                $input['deposit'] = 0;
                $input['find_price'] = json_encode($price, JSON_UNESCAPED_UNICODE);
                $input['start'] = $start;
                $input['end'] = empty($value['type']) ? $end : date('Ymd', strtotime($value['end']));
                $input['save'] = $save;
                $input['breakfast'] = 0;
                $input['status'] = 0;
                $input['type'] = 0;

                $live = $Live->toCreate($input);

                if (empty($live)) {

                    return [
                        'msg' => '预定详情信息生成失败',
                        'err' => 422,
                    ];
                }

                $input = [];

                foreach ($price as $val) {

                    $tmpArr = [];

                    $tmpArr['hid'] = $this->operate['hid'];
                    $tmpArr['oid'] = $this->order->oid;
                    $tmpArr['lid'] = $live->lid;
                    $tmpArr['rid'] = 0;
                    $tmpArr['tid'] = $value['tid'];
                    $tmpArr['price'] = $val['price'];
                    $tmpArr['date'] = $val['date'];
                    $tmpArr['type'] = empty($value['type']) ? 0 : 1;
                    $tmpArr['status'] = 3;
                    $tmpArr['description'] = '客房预定';

                    $this->value[] = $tmpArr;
                }
            }
        }
    }

    public function toGetPrice($condition, $start, $end)
    {
        $price = $this->Price->toGet($this->operate['hid'], $condition, $start, $end);

        if ($price->isEmpty()) {

            return '房型价格查询失败, 请检查您的房型价格是否存在';
        } else {

            return $price;
        }
    }
}