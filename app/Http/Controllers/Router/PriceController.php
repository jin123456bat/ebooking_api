<?php

namespace App\Http\Controllers\Router;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Help\Condition;
use App\Models\Company;
use App\Models\Hotel;
use App\Models\Price\Price;
use App\Models\Price\PriceBase;
use App\Models\Price\PriceDay;
use App\Models\Price\PriceWeek;
use App\Models\Type;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PriceController extends Controller
{

    private $condition;

    private $auth;

    private $official;

    public function __construct(Request $request)
    {
        $this->auth = Auth::user();

        $this->official = User::toFindOfficial($this->auth['hid']);

//        $this->condition = $this->getCondition($request);
        $this->condition = Condition::getCondition($request, $this->auth, $this->official);
    }

    public function get(Request $request)
    {
        $date = Hotel::toGetTime($this->auth['hid']);

        $input = $this->condition;
        $input['start'] = $request->get('start');
        $input['end'] = $request->get('end');

        $Price = new Price();

        $validator = $Price->toValidator($input, $date);

        if (!empty($validator)) {
            return $validator;
        }

        $Type = new Type();

        $type = $Type->toFind($input['tid'], $this->auth['hid']);

        if (empty($type)) {
            return [
                'msg' => '房型信息不存在',
                'err' => 404,
            ];
        }

        $start = date('Ymd', strtotime($input['start']));
        $end = date('Ymd', strtotime($input['end']));

        $startYear = date('Y', strtotime($input['start']));
        $startMonth = date('m', strtotime($input['start']));
        $startDay = date('d', strtotime($input['start']));

        $endDate = ($startYear + 1) . $startMonth . $startDay;

        if ($end > intval($endDate)) {

            return [
                'msg' => '最多查询相关房型一年的价格',
                'err' => '403'
            ];
        }

        $price = $Price->toGet($this->auth['hid'], $this->condition, $start, $end);

        $count = 0;

        foreach ($price as $value) {

            $count += $value->price;
        }

        return [
            'msg' => '查询成功',
            'data' => [
                'type' => $type->name,
                'info' => $price,
                'count' => $count,
                'start' => $input['start'],
                'end' => $input['end'],
            ],
            'err' => 0,
        ];
    }

    public function init()
    {
//        $Week = new PriceWeek();
        $Day = new PriceDay();

        $start = strtotime(Hotel::toGetTime($this->auth['hid']));

        $year = date('Y', $start);
        $month = date('m', $start);
        $day = date('d', $start);

        $endDate = ($year + 2) . '-' . $month . '-' . $day . ' 00:00:00';
        $end = strtotime($endDate);

        $date = [];

        for ($i = $start; $i < $end; $i += 86400) {
            $date[] = date('Ymd', $i);
        }

        //  查询房型基价
        $Base = new PriceBase();
        $base = $Base->toGet($this->auth['hid'], true);

        if (empty($base)) {
            return [
                'msg' => '酒店不存在相关基础价格, 请先设置基本价格',
                'err' => 404,
            ];
        }

        $price = [];

        foreach ($base as $value) {

            $value->cid = $value->cid ?? 0;

            foreach ($date as $val) {
                $tmpArr['hid'] = $this->auth['hid'];
                $tmpArr['channel'] = $value->key;
                $tmpArr['channel_name'] = $value->channel;
                $tmpArr['vip'] = $value->lv;
                $tmpArr['vip_name'] = $value->vip;
                $tmpArr['tid'] = $value->tid;
                $tmpArr['type'] = $value->type;
                $tmpArr['cid'] = $value->cid;
                $tmpArr['price'] = $value->base;
                $tmpArr['date'] = $val;

                $week = date('w', strtotime($val));

                if (!empty($value->monday)) {

                    if ($week == 1) {
                        $tmpArr['price'] = $value->monday;
                    }
                }
                if (!empty($value->tuesday)) {

                    if ($week == 2) {
                        $tmpArr['price'] = $value->tuesday;
                    }
                }
                if (!empty($value->wednesday)) {

                    if ($week == 3) {
                        $tmpArr['price'] = $value->wednesday;
                    }
                }
                if (!empty($value->thursday)) {

                    if ($week == 4) {
                        $tmpArr['price'] = $value->thursday;
                    }
                }
                if (!empty($value->friday)) {

                    if ($week == 5) {
                        $tmpArr['price'] = $value->friday;
                    }
                }
                if (!empty($value->saturday)) {

                    if ($week == 6) {
                        $tmpArr['price'] = $value->saturday;
                    }
                }
                if (!empty($value->sunday)) {

                    if ($week == 0) {
                        $tmpArr['price'] = $value->sunday;
                    }
                }

                $price[] = $tmpArr;
            }
        }

        //  更新周价周价

//        foreach ($base as $value) {
//
//            $value->cid = $value->cid ?? 0;
//
//            $week = $Week->toFind($this->auth['hid'], $value->key, $value->lv, $value->tid, $value->cid);
//
//            if (!empty($week)) {
//
//                foreach ($week as $val) {
//
//                    $val['week'] = $val['week'] == 7 ? 0 : $val['week'];
//
//                    foreach ($price as $k => $v) {
//
//                        if ($v['hid'] == $this->auth['hid'] && $v['channel'] == $value->key && $v['vip'] == $value->lv && $v['tid'] == $value->tid && $v['cid'] == $value->cid) {
//
//                            $date = date('w', strtotime($v['date']));
//
//                            if ($date == $val['week'] && !empty($val['price'])) {
//                                $price[$k]['price'] = $val['price'];
//                            }
//                        }
//                    }
//                }
//            }
//        }

        //  查询房型日价
        foreach ($base as $value) {

            $value->cid = $value->cid ?? 0;

            $day = $Day->toFind($this->auth['hid'], $value->key, $value->lv, $value->tid, $value->cid);

            if (!empty($day)) {

                foreach ($day as $val) {

                    foreach ($price as $k => $v) {

                        if ($v['hid'] == $this->auth['hid'] && $v['channel'] == $value->key && $v['vip'] == $value->lv && $v['tid'] == $value->tid && $v['cid'] == $value->cid && $v['date'] == $val['date'] && !empty($val['price'])) {

                            $price[$k]['price'] = $val['price'];

                        }
                    }
                }
            }
        }

        $Price = new Price();

        $count = $Price->toCount($this->auth['hid']);

        DB::beginTransaction();

        if (!empty($count)) {

            $num = $Price->toDelete($this->auth['hid']);

            if (empty($num)) {

                DB::rollBack();

                return [
                    'msg' => '陈旧价格数据清除失败',
                    'err' => 422
                ];
            }
        }

        try {

            $price = $Price->toInsert($price);

            if (empty($price)) {

                DB::rollBack();

                return [
                    'msg' => '价格写入失败',
                    'err' => 422
                ];
            }

        } catch (\Exception $e) {

            DB::rollBack();

            return [
                'msg' => '价格写入失败',
                'err' => 422
            ];
        }

        DB::commit();
        return [
            'msg' => '查询成功',
            'err' => 0,
        ];

    }

    public function create(Request $request)
    {
        $input['hid'] = $this->auth['hid'];
        $input['channel'] = $this->condition['channel'];
        $input['channel_name'] = $this->official->name;
        $input['vip'] = $this->condition['vip'];
        $input['vip_name'] = '';
        $input['cid'] = $this->condition['cid'];
        $input['tid'] = $this->condition['tid'];
        $input['type'] = '';
        $input['base'] = $request->get('base', 0);
        $input['monday'] = $request->get('monday', 0);
        $input['tuesday'] = $request->get('tuesday', 0);
        $input['wednesday'] = $request->get('wednesday', 0);
        $input['thursday'] = $request->get('thursday', 0);
        $input['friday'] = $request->get('friday', 0);
        $input['saturday'] = $request->get('saturday', 0);
        $input['sunday'] = $request->get('sunday', 0);

        if (empty($input['base'])) {
            return [
                'msg' => '相关价格基本价不能为空',
                'err' => 40401,
            ];
        }

        $PriceBase = new PriceBase();

        $validator = $PriceBase->toValidator($input);

        if (!empty($validator)) {
            return $validator;
        }

        if ($input['channel'] != $this->official->key) {
            $User = new User();

            $isEmptyChannel = $User->toFindUser($input['channel'], $this->auth['hid']);

            if (empty($isEmptyChannel)) {
                return [
                    'msg' => '渠道信息不存在',
                    'err' => 404,
                ];
            }

            $input['channel_name'] = $isEmptyChannel->name;
        }

//        if (empty($input['vip'])) {
//            //  验证会员等级是否存在
//        }

        if (!empty($input['cid'])) {

            $Company = new Company();

            $isEmptyCompany = $Company->toFindCompany($input['cid'], $this->auth['hid']);

            if (empty($isEmptyCompany)) {
                return [
                    'msg' => '协议公司信息不存在',
                    'err' => 404,
                ];
            }
        }

        if (!empty($input['tid'])) {

            $Type = new Type();

            $isEmptyType = $Type->toFind($input['tid'], $this->auth['hid']);

            if (empty($isEmptyType)) {
                return [
                    'msg' => '房型信息不存在',
                    'err' => 404,
                ];
            }

            $input['type'] = $isEmptyType->name;
        }

        if ($PriceBase->toUniquePrice($input)) {
            return [
                'msg' => '价格信息已存在',
                'err' => 422,
            ];
        }

        try {

            $base = $PriceBase->toCreate($input);

            if (empty($base)) {

                return [
                    'msg' => '价格添加失败',
                    'err' => 422,
                ];
            }
        } catch (\Exception $e) {

            return [
                'msg' => '价格添加失败',
                'err' => 422,
            ];
        }

        return [
            'msg' => '查询成功',
            'err' => 0,
        ];
    }

    public function edit(Request $request)
    {
        $input['pbid'] = $request->get('pbid');
        $input['hid'] = $this->auth['hid'];
        $input['channel'] = $this->condition['channel'];
        $input['channel_name'] = $this->official->name;
        $input['vip'] = $this->condition['vip'];
        $input['vip_name'] = '';
        $input['cid'] = $this->condition['cid'];
        $input['tid'] = $this->condition['tid'];
        $input['type'] = '';
        $input['base'] = $request->get('base', 0);
        $input['monday'] = $request->get('monday', 0);
        $input['tuesday'] = $request->get('tuesday', 0);
        $input['wednesday'] = $request->get('wednesday', 0);
        $input['thursday'] = $request->get('thursday', 0);
        $input['friday'] = $request->get('friday', 0);
        $input['saturday'] = $request->get('saturday', 0);
        $input['sunday'] = $request->get('sunday', 0);

        if (empty($input['base'])) {
            return [
                'msg' => '相关价格基本价不能为空',
                'err' => 40401,
            ];
        }

        if (empty($input['pbid'])) {
            return [
                'msg' => '价格号不能为空',
                'err' => 40402,
            ];
        }

        $PriceBase = new PriceBase();

        $base = $PriceBase->toSimpleFind($input['pbid'], $this->auth['hid']);

        if (empty($base)) {
            return [
                'msg' => '价格号不存在，请先添加价格',
                'err' => 42201,
            ];
        }

        if ($input['channel'] != $this->official->key) {
            $User = new User();

            $isEmptyChannel = $User->toFindUser($input['channel'], $this->auth['hid']);

            if (empty($isEmptyChannel)) {
                return [
                    'msg' => '渠道信息不存在',
                    'err' => 404,
                ];
            }
            $input['channel_name'] = $isEmptyChannel->name;
        }

//        if (empty($input['vip'])) {
//            //  验证会员等级是否存在
//        }

        if (!empty($input['cid'])) {

            $Company = new Company();

            $isEmptyCompany = $Company->toFindCompany($input['cid'], $this->auth['hid']);

            if (empty($isEmptyCompany)) {
                return [
                    'msg' => '协议公司信息不存在',
                    'err' => 404,
                ];
            }
        }

        if (!empty($input['tid'])) {

            $Type = new Type();

            $isEmptyType = $Type->toFind($input['tid'], $this->auth['hid']);

            if (empty($isEmptyType)) {
                return [
                    'msg' => '房型信息不存在',
                    'err' => 404,
                ];
            }

            $input['type'] = $isEmptyType->name;
        }

        if ($PriceBase->toUniquePrice($input, $input['pbid'], false)) {
            return [
                'msg' => '价格信息已存在',
                'err' => 422,
            ];
        }

        try {

            $PriceBase->toUpdate($base, $input);

        } catch (\Exception $e) {

            return [
                'msg' => '价格更新失败',
                'err' => 422,
            ];
        }

        return [
            'msg' => '查询成功',
            'err' => 0,
        ];
    }

    public function find()
    {
        $PriceBase = new PriceBase();

        $price = $PriceBase->toFind($this->condition, $this->auth['hid']);

        return [
            'msg' => '查询成功',
            'data' => $price,
            'err' => 0,
        ];
    }

    public function updateBase(Request $request)
    {
        $pbid = $request->get('pbid');

        if (empty($pbid)) {

            return [
                'msg' => '未选择基本价格',
                'err' => 404,
            ];
        }

        $price = $request->get('price', 0);

        if (empty($price)) {

            return [
                'msg' => '价格不能为空',
                'err' => 404,
            ];
        }

        $PriceBase = new PriceBase();


        $base = $PriceBase->toSimpleFind($pbid, $this->auth['hid']);

        if (empty($base)) {
            return [
                'msg' => '价格信息不存在',
                'err' => 404,
            ];
        }

        $input['price'] = $price;

        try {

            $PriceBase->toUpdate($base, $input);

        } catch (\Exception $e) {
            return [
                'msg' => '价格修改失败',
                'err' => 500,
            ];
        }

        return [
            'msg' => '查询成功',
            'err' => 0,
        ];
    }

    public function getBase(Request $request)
    {
        $PriceBase = new PriceBase();

        $count = $PriceBase->toCount($this->auth['hid']);

        $base = null;

        if (!empty($count)) {


            $page = intval($request->get('page'));

            $page = ($page <= 1 ? 1 : $page) - 1;

            $num = intval($request->get('num'));

            $num = $num <= 10 ? 10 : $num;

            $base['info'] = $PriceBase->toGet($this->auth['hid'], false, $num, $page);
            $base['count'] = $base['info']->count();
            $base['num'] = $num;
            $base['page'] = $page + 1;
        }

        return [
            'msg' => '查询成功',
            'data' => $base,
            'err' => 0,
        ];

    }

    public function createWeek(Request $request)
    {
        $input['hid'] = $this->auth['hid'];
        $input['channel'] = $this->condition['channel'];
        $input['channel_name'] = $this->official->name;
        $input['vip'] = $this->condition['vip'];
        $input['vip_name'] = '';
        $input['cid'] = $this->condition['cid'];
        $input['tid'] = $this->condition['tid'];
        $input['type'] = '';
        $input['price'] = $request->get('price', 0);
        $input['week'] = $request->get('week', 0);

        $PriceWeek = new PriceWeek();

        $validator = $PriceWeek->toValidator($input);

        if (!empty($validator)) {
            return $validator;
        }

        if ($input['channel'] != $this->official->key) {
            $User = new User();

            $isEmptyChannel = $User->toFindUser($input['channel'], $this->auth['hid']);

            if (empty($isEmptyChannel)) {
                return [
                    'msg' => '渠道信息不存在',
                    'err' => 404,
                ];
            }

            $input['channel_name'] = $isEmptyChannel->name;
        }

//        if (empty($input['vip'])) {
//            //  验证会员等级是否存在
//        }

        if (!empty($input['cid'])) {

            $Company = new Company();

            $isEmptyCompany = $Company->toFindCompany($input['cid'], $this->auth['hid']);

            if (empty($isEmptyCompany)) {
                return [
                    'msg' => '协议公司信息不存在',
                    'err' => 404,
                ];
            }
        }

        if (!empty($input['tid'])) {

            $Type = new Type();

            $isEmptyType = $Type->toFind($input['tid'], $this->auth['hid']);

            if (empty($isEmptyType)) {
                return [
                    'msg' => '房型信息不存在',
                    'err' => 404,
                ];
            }

            $input['type'] = $isEmptyType->name;
        }

        if ($PriceWeek->toUniquePrice($input)) {
            return [
                'msg' => '价格信息已存在',
                'err' => 422,
            ];
        }

        try {

            $week = $PriceWeek->toCreate($input);

            if (empty($week)) {

                return [
                    'msg' => '价格添加失败',
                    'err' => 422,
                ];
            }

        } catch (\Exception $e) {
            return [
                'msg' => '价格添加失败',
                'err' => 422,
            ];
        }

        return [
            'msg' => '查询成功',
            'err' => 0,
        ];
    }

    public function deleteWeek(Request $request)
    {
        $pwid = $request->get('pwid');

        if (empty($pwid)) {

            return [
                'msg' => '未选择基本价格',
                'err' => 404,
            ];
        }

        $PriceWeek = new PriceWeek();

        $num = $PriceWeek->toDelete($pwid, $this->auth['hid']);

        if (empty($num)) {
            return [
                'msg' => '价格删除失败',
                'err' => 422,
            ];
        }

        return [
            'msg' => '查询成功',
            'err' => 0,
        ];
    }

    public function updateWeek(Request $request)
    {
        $pwid = $request->get('pwid');

        if (empty($pwid)) {

            return [
                'msg' => '未选择基本价格',
                'err' => 404,
            ];
        }

        $price = $request->get('price', 0);

        if (empty($price)) {

            return [
                'msg' => '价格不能为空',
                'err' => 404,
            ];
        }

        $PriceWeek = new PriceWeek();


        $week = $PriceWeek->toSimpleFind($pwid, $this->auth['hid']);

        if (empty($week)) {
            return [
                'msg' => '价格信息不存在',
                'err' => 404,
            ];
        }

        $input['price'] = $price;

        try {

            $PriceWeek->toUpdate($week, $input);

        } catch (\Exception $e) {
            return [
                'msg' => '价格修改失败',
                'err' => 500,
            ];
        }

        return [
            'msg' => '查询成功',
            'err' => 0,
        ];
    }

    public function getWeek(Request $request)
    {
        $PriceWeek = new PriceWeek();

        $count = $PriceWeek->toCount($this->auth['hid']);

        $week = null;

        if (!empty($count)) {


            $page = intval($request->get('page'));

            $page = ($page <= 1 ? 1 : $page) - 1;

            $num = intval($request->get('num'));

            $num = $num <= 10 ? 10 : $num;

            $week['info'] = $PriceWeek->toGet($this->auth['hid'], false, $num, $page);
            $week['count'] = $week['info']->count();
            $week['num'] = $num;
            $week['page'] = $page + 1;
        }

        return [
            'msg' => '查询成功',
            'data' => $week,
            'err' => 0,
        ];
    }

    public function createDay(Request $request)
    {
        $input['hid'] = $this->auth['hid'];
        $input['channel'] = $this->condition['channel'];
        $input['channel_name'] = $this->official->name;
        $input['vip'] = $this->condition['vip'];
        $input['vip_name'] = '';
        $input['cid'] = $this->condition['cid'];
        $input['tid'] = $this->condition['tid'];
        $input['type'] = '';
        $input['price'] = $request->get('price', 0);
        $input['date'] = $request->get('date', 0);

        $PriceDay = new PriceDay();

        $date = Hotel::toGetTime($this->auth['hid']);

        $validator = $PriceDay->toValidator($input, $date);

        if (!empty($validator)) {
            return $validator;
        }

        if ($input['channel'] != $this->official->key) {
            $User = new User();

            $isEmptyChannel = $User->toFindUser($input['channel'], $this->auth['hid']);

            if (empty($isEmptyChannel)) {
                return [
                    'msg' => '渠道信息不存在',
                    'err' => 404,
                ];
            }

            $input['channel_name'] = $isEmptyChannel->name;
        }

//        if (empty($input['vip'])) {
//            //  验证会员等级是否存在
//        }

        if (!empty($input['cid'])) {

            $Company = new Company();

            $isEmptyCompany = $Company->toFindCompany($input['cid'], $this->auth['hid']);

            if (empty($isEmptyCompany)) {
                return [
                    'msg' => '协议公司信息不存在',
                    'err' => 404,
                ];
            }
        }

        if (!empty($input['tid'])) {

            $Type = new Type();

            $isEmptyType = $Type->toFind($input['tid'], $this->auth['hid']);

            if (empty($isEmptyType)) {
                return [
                    'msg' => '房型信息不存在',
                    'err' => 404,
                ];
            }

            $input['type'] = $isEmptyType->name;
        }

        $input['date'] = date('Ymd', strtotime($input['date']));

        if ($PriceDay->toUniquePrice($input)) {

            return [
                'msg' => '价格信息已存在',
                'err' => 422,
            ];
        }

        try {

            $day = $PriceDay->toCreate($input);

            if (empty($day)) {

                return [
                    'msg' => '价格添加失败',
                    'err' => 422,
                ];
            }

        } catch (\Exception $e) {
            return [
                'msg' => '价格添加失败',
                'err' => 422,
            ];
        }

        return [
            'msg' => '查询成功',
            'err' => 0,
        ];
    }

    public function deleteDay(Request $request)
    {
        $pdid = $request->get('pdid');

        if (empty($pdid)) {

            return [
                'msg' => '未选择基本价格',
                'err' => 404,
            ];
        }

        $PriceDay = new PriceDay();

        $num = $PriceDay->toDelete($pdid, $this->auth['hid']);

        if (empty($num)) {
            return [
                'msg' => '价格删除失败',
                'err' => 422,
            ];
        }

        return [
            'msg' => '查询成功',
            'err' => 0,
        ];
    }

    public function UpdateDayPrice(Request $request)
    {
        $date = $request->get('date');

        $price = (int)$request->get('price');

        if (empty($this->condition['tid'])) {
            return [
                'msg' => '日价房型不能为空',
                'err' => 404,
            ];
        }

        if (empty($date)) {
            return [
                'msg' => '价格日期不能为空',
                'err' => 404,
            ];
        }

        $dateNum = strtotime($date);

        if (empty($dateNum)) {
            return [
                'msg' => '价格日期格式错误',
                'err' => 404,
            ];
        }

        $date = date('Ymd', $dateNum);

        $PriceDay = new PriceDay();

        $priceDay = $PriceDay->toFindPrice($this->auth['hid'], $this->condition, $date);

        DB::beginTransaction();

        if (empty($priceDay)) {

            if (empty($price)) {
                return [
                    'msg' => '禁止添加无意义日价',
                    'err' => 422,
                ];
            }

            $input = [];

            $input['hid'] = $this->auth['hid'];
            $input['channel'] = $this->condition['channel'];
            $input['channel_name'] = '';
            $input['vip'] = $this->condition['vip'];
            $input['vip_name'] = '';
            $input['tid'] = $this->condition['tid'];
            $input['type'] = '';
            $input['cid'] = $this->condition['cid'];
            $input['price'] = $price;
            $input['date'] = $date;

            $create = $PriceDay->toCreate($input);

            if (empty($create)) {
                DB::rollBack();
                return [
                    'msg' => '日价添加失败',
                    'err' => 422,
                ];
            }

        } else {

            try {
                $PriceDay->toUpdatePrice($this->auth['hid'], $priceDay->pdid, $price);
            } catch (\Exception $e) {
                DB::rollBack();
                return [
                    'msg' => '日价更新失败，请稍后重试',
                    'err' => 500,
                ];
            }
        }

        $Price = new Price();

        try {

            $Price->toUpdateDay($this->auth['hid'], $this->condition['channel'], $this->condition['vip'], $this->condition['tid'], $this->condition['cid'], $price, $date);

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'msg' => '日价更新失败，请稍后重试1',
                'err' => 500,
            ];
        }

        DB::commit();
        return [
            'msg' => '查询成功',
            'err' => 0
        ];
    }

    public function updateDay(Request $request)
    {
        $pdid = $request->get('pdid');

        if (empty($pdid)) {

            return [
                'msg' => '未选择基本价格',
                'err' => 404,
            ];
        }

        $price = $request->get('price', 0);

        if (empty($price)) {

            return [
                'msg' => '价格不能为空',
                'err' => 404,
            ];
        }

        $PriceDay = new PriceDay();


        $day = $PriceDay->toSimpleFind($pdid, $this->auth['hid']);

        if (empty($day)) {
            return [
                'msg' => '价格信息不存在',
                'err' => 404,
            ];
        }

        $input['price'] = $price;

        try {

            $PriceDay->toUpdate($day, $input);

        } catch (\Exception $e) {
            return [
                'msg' => '价格修改失败',
                'err' => 500,
            ];
        }

        return [
            'msg' => '查询成功',
            'err' => 0,
        ];
    }

    public function getDay(Request $request)
    {
        $PriceDay = new PriceDay();

        $count = $PriceDay->toCount($this->auth['hid']);

        $day = null;

        if (!empty($count)) {


            $page = intval($request->get('page'));

            $page = ($page <= 1 ? 1 : $page) - 1;

            $num = intval($request->get('num'));

            $num = $num <= 10 ? 10 : $num;

            $day['info'] = $PriceDay->toGet($this->auth['hid'], false, $num, $page);
            $day['count'] = $day['info']->count();
            $day['num'] = $num;
            $day['page'] = $page + 1;
        }

        return [
            'msg' => '查询成功',
            'data' => $day,
            'err' => 0,
        ];
    }

    public function getCondition(Request $request)
    {
        $tid = intval($request->get('tid'));

        if ($this->official->key != $this->auth['id']) { //    非官方渠道查询价格

            return [
                'channel' => $this->auth['id'],
                'vip' => 0,
                'cid' => 0,
                'tid' => $tid,
            ];
        } else {

            $channel = intval($request->get('channel', $this->auth['id']));
            $vip = intval($request->get('vip'));
            $cid = intval($request->get('cid'));

            if ($channel == $this->auth['id']) {
                if (!empty($vip)) {
                    return [
                        'channel' => $channel,
                        'vip' => $vip,
                        'cid' => 0,
                        'tid' => $tid,
                    ];
                } else if (!empty($cid)) {
                    return [
                        'channel' => $channel,
                        'vip' => 0,
                        'cid' => $cid,
                        'tid' => $tid,
                    ];
                } else {

                    return [
                        'channel' => $this->auth['id'],
                        'vip' => 0,
                        'cid' => 0,
                        'tid' => $tid,
                    ];
                }
            } else {

                return [
                    'channel' => $channel,
                    'vip' => 0,
                    'cid' => 0,
                    'tid' => $tid,
                ];
            }

        }
    }

}