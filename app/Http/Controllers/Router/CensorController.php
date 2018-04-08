<?php

namespace App\Http\Controllers\Router;

use App\Http\Controllers\Controller;
use App\Models\Censor;
use App\Models\Finance;
use App\Models\Hotel;
use App\Models\Live;
use App\Models\Order;
use App\Models\Receipt;
use App\Models\Stock;
use App\Models\Value;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CensorController extends Controller
{

    private $auth;

    private $date;

    private $hotel;

    public function __construct()
    {
        $this->auth = Auth::user();

        $this->date = Hotel::toGetTime($this->auth['hid']);
    }


    public function censor()
    {
        $live = $this->leave();

        if (!empty($live['data'])) {
            return [
                'msg' => '含有应离未离客户, 暂不能夜审',
                'err' => 422
            ];
        }

        $Hotel = new Hotel();

        $hotel = $Hotel->toFind($this->auth['hid']);

        $this->hotel = $hotel;

        $date = $this->getCensorDate();

        if (is_array($date)) {

            return $date;
        }

        $Finance = new Finance();

        DB::beginTransaction();

        try {

            $Finance->toConfirmRMB($this->auth['hid']);

            $Finance->toConfirmWeChatOrAliPay($this->auth['hid']);

        } catch (\Exception $e) {

            DB::rollBack();
            return [
                'msg' => '现金收款确认失败',
                'err' => 500
            ];
        }

        try {

            if (empty($Hotel->toUpdateDate($this->auth['hid'], $date))) {

                DB::rollBack();
                return [
                    'msg' => '酒店系统时间更新失败',
                    'err' => 500
                ];
            }

            $hotel->time = $date;

        } catch (\Exception $e) {

            DB::rollBack();
            return [
                'msg' => '酒店系统时间更新失败',
                'err' => 500
            ];
        }

        $Live = new Live();

        $notReach = $Live->toGetNotReach($this->auth['hid'], $this->date);

        if (!empty($notReach)) {

            $Stock = new Stock();
            $dateTime = strtotime($this->date);

            $notReachOrder = [];

            foreach ($notReach as $value) {

                $notReachOrder[] = $value->oid;

                //  更新预定记录为取消

                $num = $Live->toUpdateStatus($value->lid, 3);

                if (empty($num)) {

                    DB::rollBack();
                    return [
                        'msg' => '预定取消失败',
                        'err' => 422
                    ];
                }

                if ($value->end == $this->date) {
                    continue;
                }

                //  次日及次日以上走的订单，取消库存
                $day = 0;

                $endTime = strtotime($value->end);

                for ($i = $dateTime; $i < $endTime; $i += 86400) {
                    $day++;
                }

                if ($day != $Stock->toDecrementBooking($this->auth['hid'], $value->tid, $this->date, $value->end, 1)) {

                    DB::rollBack();
                    return [
                        'msg' => '日库存更新失败',
                        'err' => 422
                    ];
                }
            }

            $quitOrder = $Live->toGetOrderOtherBooking($this->auth['hid'], $notReachOrder, $this->date);

            if (!empty($quitOrder)) {

                foreach ($notReachOrder as $key => $value) {

                    $i = false;

                    foreach ($quitOrder as $val) {

                        if ($value = $val['oid']) {
                            $i = true;
                        }
                    }

                    if ($i) {
                        unset($notReachOrder[$key]);
                    }
                }
            }

            if (!empty($notReachOrder)) {

                $Order = new Order();

                if (count($notReachOrder) != $Order->toBatchUpdateStatus(6, $notReachOrder, $this->auth['hid'])) {

                    DB::rollBack();
                    return [
                        'msg' => '订单状态更新失败',
                        'err' => 422
                    ];
                }
            }
        }

        $Value = new Value();

        $roomCharges = $Value->toGetDayReceipt($this->auth['hid'], $this->date);

        if (!empty($roomCharges)) {

            $ReceiptController = new ReceiptController();

            $Receipt = new Receipt();

            $finance = [];

            foreach ($roomCharges as $value) {

                $receipt = $ReceiptController->toGetCensorFinance($value);

                if (!isset($receipt['deduction'])) {
                    DB::rollBack();
                    return $receipt;
                } else {

                    if (!empty($receipt['pay'])) {
                        DB::rollBack();
                        return [
                            'msg' => '订单 ' . $value->oid . ' 账户余额不足, 仍需付款 ' . $receipt['pay'] / 100 . ' 元',
                            'err' => 422
                        ];
                    } else {

                        foreach ($receipt['deduction'] as $val) {

                            if (!isset($val['deduction'])) {
                                continue;
                            }

                            $num = $Receipt->toUpDeduction($val['rid'], $val['deduction']);

                            if (empty($num)) {

                                DB::rollBack();
                                return [
                                    'msg' => '账户扣款失败',
                                    'err' => 422
                                ];
                            }

                            $tmpArr = [];

                            $tmpArr['hid'] = $this->auth['hid'];
                            $tmpArr['oid'] = $value->oid;
                            $tmpArr['rid'] = $val['room_id'];
                            $tmpArr['pay'] = $val['pid'];
                            $tmpArr['price'] = $val['deduction'];
                            $tmpArr['remark'] = '夜审:' . $val['remark'];
                            $tmpArr['date'] = $val['date'];
                            $tmpArr['time'] = time();
                            $tmpArr['type'] = 1101;

                            $finance[] = $tmpArr;
                        }
                    }
                }

                $num = $Value->toUpdateStatus($this->auth['hid'], $value->vid, $this->date);

                if (empty($num)) {

                    DB::rollBack();
                    return [
                        'msg' => '收款记录更新失败',
                        'err' => 422
                    ];
                }
            }

            $inRes = $Finance->toInsert($finance);

            if (!$inRes) {

                DB::rollBack();
                return [
                    'msg' => '酒店财务写入失败',
                    'err' => 422
                ];
            }
        }

        $Censor = new Censor();

        try {

            $Censor->toCreate($this->auth['hid'], $this->date);

        } catch (\Exception $e) {

            DB::rollBack();
            return [
                'msg' => '夜审日志更新失败',
                'err' => 500
            ];
        }

        DB::commit();

        return [
            'msg' => '查询成功',
            'err' => 0
        ];
    }

    public function leave()
    {
        $Live = new Live();

        $count = $Live->toCountLeave($this->auth['hid'], $this->date);

        $live = null;

        if (!empty($count)) {

            $live = $Live->toGetLeave($this->auth['hid'], $this->date);
        }

        return [
            'msg' => '查询成功',
            'data' => $live,
            'err' => 0
        ];
    }

    private function getCensorDate()
    {
        $systemTime = time();

        $date = date('Y-m-d', strtotime($this->date));

        $date = $date . ' ' . $this->hotel->censor;

        $censorTime = strtotime($date, $systemTime);

        $hour = date('H', $censorTime);

        if ($hour <= 6) {
            $censorTime += 86400;
        }

        if ($censorTime >= $systemTime) {

            return [
                'msg' => '未到夜审时间, 不能提前夜审',
                'err' => 422
            ];
        }

        $nowDate = date('Y-m-d', $systemTime);

        $nowCensorTime = strtotime($nowDate . ' ' . $this->hotel->censor);

        $time = strtotime($nowDate);

        if (($systemTime < $time + 86400) && $systemTime > $nowCensorTime) {

            return date('Ymd', $time + 86400);
        } else {

            return date('Ymd', $systemTime);
        }

    }
}