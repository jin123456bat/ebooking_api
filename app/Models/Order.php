<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class Order extends Model
{
    protected $primaryKey = 'oid';

    protected $fillable = [
        'oid_other', 'key', 'key_name', 'hid', 'uid', 'init', 'channel', 'channel_name', 'company', 'company_name', 'vip', 'vip_name', 'ordain', 'remark', 'status', 'type'
    ];

    protected $guarded = [];

    public function toCreate($input)
    {
        return Order::create([
            'oid_other' => $input['oid_other'],
            'key' => $input['key'],
            'key_name' => $input['key_name'],
            'hid' => $input['hid'],
            'uid' => $input['uid'],
            'init' => time(),
            'channel' => $input['channel'],
            'channel_name' => $input['channel_name'],
            'company' => $input['company'],
            'company_name' => $input['company_name'],
            'vip' => $input['vip'],
            'vip_name' => $input['vip_name'],
            'ordain' => $input['ordain'],
            'remark' => $input['remark'],
            'status' => $input['status'],
            'type' => $input['type']
        ]);
    }

    public function toUpdateStatus($status, $oid, $hid)
    {
        return DB::update("UPDATE `orders` SET `status` = ?, `updated_at` = ? WHERE `hid` = ? AND `oid` = ?", [$status, date('Y-m-d H:i:s'), $hid, $oid]);
    }

    public function toUpdateQuit($status, $oid, $hid)
    {
        return DB::update("UPDATE `orders` SET `status` = ?, `leave` = ?, `updated_at` = ? WHERE `hid` = ? AND `oid` = ?", [$status, time(), date('Y-m-d H:i:s'), $hid, $oid]);
    }

    public function toBatchUpdateStatus($status, $oidArr, $hid)
    {
        return DB::update("UPDATE `orders` SET `status` = ?, `updated_at` = ? WHERE `hid` = ? AND `oid` IN (?)",
            [
                $status, date('Y-m-d H:i:s'), $hid, implode(',', $oidArr)
            ]);
    }

    public function toUpdateLeave($oid, $hid)
    {
        return Order::where('hid', $hid)
            ->where('oid', $oid)
            ->update(['leave' => time()]);
    }

    public function toGet($hid, $num, $page = 0, $type = 0, $status = 2)
    {
        return Order::select('oid', 'oid_other', 'key_name as key', 'init', 'leave', 'channel_name', 'company_name', 'vip_name', 'ordain', 'remark', 'status')
            ->where('type', $type)
            ->where('hid', $hid)
            ->where('status', $status)
            ->skip($num * $page)
            ->take($num)
            ->get();
    }

    public function toGetOrdain($hid, $num, $page = 0, $type = 0)
    {
        return DB::select('SELECT `oid`, `oid_other`, `key_name` AS `key`, `init`, `leave`, `channel_name`, `company_name`, `vip_name`, `ordain`, `remark`, `status` FROM `orders` WHERE `type` = ? AND `hid` = ? AND (`status` = 0 OR `status` = 11) AND `oid` >= (SELECT `oid` FROM `orders` WHERE `type` = ? AND `hid` = ? AND (`status` = 0 OR `status` = 11) ORDER BY  oid DESC LIMIT ?,1) LIMIT ?', [
            $type, $hid, $type, $hid, $num * $page, $num
        ]);
    }

    public function toGetOrder($hid, $num, $page = 0, $type = 0)
    {
        return DB::select('SELECT `oid`, `oid_other`, `key_name` AS `key`, `init`, `leave`, `channel_name`, `company_name`, `vip_name`, `ordain`, `remark`, `status` FROM `orders` WHERE `type` = ? AND `hid` = ? AND (`status` = 2 OR `status` = 7) AND `oid` >= (SELECT `oid` FROM `orders` WHERE `type` = ? AND `hid` = ? AND (`status` = 7 OR `status` = 2) ORDER BY  oid ASC LIMIT ?,1) ORDER BY oid DESC LIMIT ?', [
            $type, $hid, $type, $hid, $num * $page, $num
        ]);
    }

    public function toFindCondition($oid, $hid)
    {
        return Order::select('channel', 'company', 'vip')
            ->where('hid', $hid)
            ->where('oid', $oid)
            ->first();
    }

    public function toFindOfficialOrderWithOid($oid, $hid)
    {
        return Order::select('*')
            ->where('oid', $oid)
            ->where('hid', $hid)
            ->first();
    }

    public function toFindOtherOrderWithOid($channel, $oid, $hid)
    {
        return Order::select('*')
            ->where('channel', $channel)
            ->where('oid_other', $oid)
            ->where('hid', $hid)
            ->first();
    }

    public function toCountOrdain($hid, $type = 0)
    {
        $result = DB::select('SELECT COUNT(*) AS num FROM `orders` WHERE type = ? AND hid = ? AND (`status` = 0 OR `status` = 11)', [$type, $hid]);
        $tmp = head($result);
        return $tmp->num;
    }

    public function toCountOrder($hid, $type = 0)
    {
        $result = DB::select('SELECT COUNT(*) AS num FROM `orders` WHERE type = ? AND hid = ? AND (`status` = 2 OR `status` = 7)', [$type, $hid]);
        $tmp = head($result);
        return $tmp->num;
    }

    public function toCount($hid, $type = 0, $status = 0)
    {
        return Order::where('hid', $hid)
            ->where('type', $type)
            ->where('status', $status)
            ->count();
    }

    public function toValidatorOrdain(Request $request, $date)
    {
        //  证件类型： 0: 身份证/驾驶证 1: 士兵证 2: 台湾居民来往通行证 3: 外籍护照 4: 外交护照 5: 港澳居民来往通行证 6: 公务护照 7: 因公普通护照 8: 军官证 9: 其他

        $input = $request->only('name', 'no', 'phone', 'remark', 'channel', 'company', 'vip', 'content', 'payment');

        $validator = Validator::make($input, [
            'name' => 'bail|required|max:12',
            'no' => 'bail|sometimes|min:3',
            'phone' => 'bail|required|tel_phone',
            'remark' => 'bail|sometimes|max:60',
            'channel' => 'bail|sometimes|exists:users,id',
            'content' => 'bail|required|array',
            'payment' => 'bail|sometimes|array'
        ], [
            'name.required' => '姓名不能为空',
            'name.max' => '姓名最多 12 个字符',
            'no.min' => '订单号过短',
            'phone.required' => '手机号不能为空',
            'phone.tel_phone' => '手机号格式错误',
            'remark.max' => '备注最多 60 个字符',
            'channel.exists' => '渠道信息不存在',
            'content.required' => '预定详情不能为空',
            'content.array' => '预定详情格式错误',
        ]);

        if ($validator->fails()) {

            return [
                'msg' => $validator->errors()->first(),
                'err' => 422,
            ];
        }
        // 验证预定详情
        foreach ($input['content'] as $value) {

            if (!is_array($value)) {
                return [
                    'msg' => '预定详情内容格式错误',
                    'err' => 422,
                ];
            }

            if (!array_key_exists('tid', $value) || !array_key_exists('num', $value) || !array_key_exists('start', $value) || !array_key_exists('end', $value) || !array_key_exists('save', $value)) {

                return [
                    'msg' => '预定详情内容格式错误',
                    'err' => 422,
                ];
            }

            if (is_int($value['tid'])) {
                return [
                    'msg' => '预定房型格式错误',
                    'err' => 422,
                ];
            }

            if (empty($value['tid'])) {
                return [
                    'msg' => '预定房型格式错误',
                    'err' => 422,
                ];
            }

            if (is_int($value['num'])) {
                return [
                    'msg' => '预定房型数量格式错误',
                    'err' => 422,
                ];
            }

            if ($value['num'] <= 0) {
                return [
                    'msg' => '预定房型数量最低为 1',
                    'err' => 422,
                ];
            }

            if (empty(strtotime($value['start']))) {
                return [
                    'msg' => '预定开始日期不存在 或 开始日期格式错误',
                    'err' => 422,
                ];
            }

            if (strtotime($value['start']) < strtotime($date)) {
                return [
                    'msg' => '预定开始日期不能小于酒店当前日期',
                    'err' => 422,
                ];
            }

            if (empty(strtotime($value['end']))) {
                return [
                    'msg' => '预定结束日期不存在 或 结束日期格式错误',
                    'err' => 422,
                ];
            }

            if (strtotime($value['end']) < strtotime($value['start'])) {
                return [
                    'msg' => '预定结束日期不能小于开始日期',
                    'err' => 422,
                ];
            }

            $save = strtotime($value['save']);
            if (empty($save)) {
                return [
                    'msg' => '最低保留时间不能为空',
                    'err' => 422,
                ];
            }

            $start = strtotime(date('Y-m-d', strtotime($value['start'])));

            if ($save <= $start || $save >= ($start + 86400)) {
                return [
                    'msg' => '最低保留时间只能在入住当天',
                    'err' => 422,
                ];
            }
        }

        // 如果付款信息存在, 验证付款信息
        if (!empty($input['payment'])) {

            $Receipt = new Receipt();

            $receipt = $Receipt->toValidator($input['payment']);

            if (!empty($receipt)) {

                return $receipt;
            }
        }
    }

    public function toValidatorLive(Request $request, $date)
    {
        $input = $request->only('rid', 'date', 'breakfast', 'remark', 'guest', 'payment');
        //  证件类型： 0: 身份证/驾驶证 1: 士兵证 2: 台湾居民来往通行证 3: 外籍护照 4: 外交护照 5: 港澳居民来往通行证 6: 公务护照 7: 因公普通护照 8: 军官证 9: 其他

        $validator = Validator::make($input, [
            'rid' => 'bail|required|integer|min:1',
            'date' => 'bail|required|date|after_or_equal:' . $date,
//            'end' => 'bail|required|date|after_or_equal:start',
            'breakfast' => 'bail|integer|min:0|max:4',
            'remark' => 'bail|sometimes|max:60',
            'guest' => 'bail|required|array',
            'payment' => 'bail|sometimes|array'
        ], [
            'rid.required' => '房号不能为空',
            'rid.integer' => '房号格式错误',
            'rid.min' => '房号格式错误',
//            'date.required' => '开始日期不能为空',
//            'date.date' => '开始日期格式错误',
//            'date.after_or_equal' => '开始日期不能小于酒店当前日期',
            'date.required' => '结束日期不能为空',
            'date.date' => '结束日期格式错误',
            'date.after_or_equal' => '结束日期不能小于开始日期',
            'breakfast.integer' => '早餐信息格式错误',
            'breakfast.min' => '早餐最低 0 份',
            'breakfast.max' => '早餐最多 4 份',
            'remark.max' => '员工备注最多 60 个字符',
            'guest.array' => '宾客详情格式错误',
            'payment.array' => '付款详情格式错误',
        ]);

        if ($validator->fails()) {

            return [
                'msg' => $validator->errors()->first(),
                'err' => 422,
            ];
        }
        // 验证宾客

        // 如果付款信息存在, 验证付款信息
        if (!empty($input['payment'])) {

            $Receipt = new Receipt();
            $validator = $Receipt->toValidator($input['payment']);

            if (!empty($validator)) {

                return $validator;
            }
        }
    }

    public function toValidatorChange(Request $request)
    {
        $input = $request->only('rid', 'before', 'after', 'remark', 'payment');
        //  证件类型： 0: 身份证/驾驶证 1: 士兵证 2: 台湾居民来往通行证 3: 外籍护照 4: 外交护照 5: 港澳居民来往通行证 6: 公务护照 7: 因公普通护照 8: 军官证 9: 其他

        $validator = Validator::make($input, [
            'before' => 'bail|required|integer|min:1',
            'after' => 'bail|required|integer|min:1',
            'change_price' => 'bail|sometimes|boolean',
            'remark' => 'bail|required|max:60',
            'payment' => 'bail|sometimes|array'
        ], [
            'before.required' => '将换房号不能为空',
            'before.integer' => '将换房号格式错误',
            'before.min' => '将换房号格式错误',
            'after.required' => '所换房号不能为空',
            'after.integer' => '所换房号格式错误',
            'after.min' => '所换房号格式错误',
            'change_price.boolean' => '是否更新价格格式错误',
            'remark.required' => '换房备注不能为空',
            'remark.max' => '换房备注最多 60 个字符',
            'payment.array' => '付款详情格式错误',
        ]);

        if ($validator->fails()) {

            return [
                'msg' => $validator->errors()->first(),
                'err' => 422,
            ];
        }
    }

}