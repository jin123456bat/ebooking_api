<?php

namespace App\Models;

use function foo\func;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Receipt extends Model
{
    protected $primaryKey = 'rid';

    protected $fillable = [
        'hid', 'oid', 'pid', 'pay', 'price', 'remark', 'time', 'priority'
    ];

    protected $guarded = [];

    public $timestamps = false;

    public function toCreate($input)
    {
        return Receipt::insert($input);
    }

    public function toGet($hid, $oid)
    {
        return Receipt::select('rid', 'pid', 'pay', DB::raw('(price - deduction) as surplus'), 'remark')
            ->where('hid', $hid)
            ->where('oid', $oid)
            ->where(DB::raw('(price - deduction)'), '>', 0)
            ->orderBy('priority', 'DESC')
            ->orderBy('rid', 'DESC')
            ->get();
    }

    public function toGetAll($hid, $oid)
    {
        return DB::select('SELECT `rid`, `pay`, `price`, `deduction`, `time`, `remark` FROM `receipts` WHERE hid = ? AND oid = ? ORDER BY priority DESC, rid  DESC', [$hid, $oid]);
    }

    public function toGetRefunds($hid, $oid)
    {
        return Receipt::select('rid', 'pid', 'pay', DB::raw('(price - deduction) as refunds'))
            ->where('hid', $hid)
            ->where('oid', $oid)
            ->where(DB::raw('(price - deduction)'), '>', 0)
//            ->groupBy('pid')
            ->get();
    }

    public function toGetLast($hid, $oid)
    {
        return DB::select('SELECT `rid`, `pid`, `pay`, (price - deduction) as `surplus` FROM `receipts` WHERE `hid` = ? AND `oid` = ? AND (`price` - `deduction` > 0)', [$hid, $oid]);
    }

    public function toUpdateRefunds($hid, $oid, $pid, $refund, $rid)
    {
        return Receipt::where('hid', $hid)
            ->where('rid', $rid)
            ->where('oid', $oid)
            ->where('pid', $pid)
            ->where(DB::raw('(price - deduction)'), '=', $refund)
            ->groupBy('pid')
            ->increment('deduction', $refund);
    }

    public function toUpDeduction($rid, $deduction)
    {
        return Receipt::where('rid', $rid)
            ->where(DB::raw('price - deduction - ' . $deduction), '>=', 0)
            ->increment('deduction', $deduction);
    }

    public function toValidator($payment)
    {
        foreach ($payment as $value) {

            if (!is_array($value)) {
                return [
                    'msg' => '收款格式错误',
                    'err' => 422,
                ];
            }

            if (!array_key_exists('pid', $value) || !array_key_exists('price', $value)) {
                return [
                    'msg' => '收款格式错误',
                    'err' => 422,
                ];
            }

            if (is_int($value['pid'])) {
                return [
                    'msg' => '收款方式格式错误',
                    'err' => 422,
                ];
            }

            if (empty($value['pid'])) {
                return [
                    'msg' => '收款方式不能为空',
                    'err' => 422,
                ];
            }

            if (!is_numeric($value['price'])) {
                return [
                    'msg' => '收款金额格式错误',
                    'err' => 422,
                ];
            }

            if (empty($value['price'])) {
                return [
                    'msg' => '收款金额不能为空',
                    'err' => 422,
                ];
            }
        }
    }
}