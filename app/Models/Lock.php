<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class Lock extends Model
{
    public function toGet($hid, $tid, $start, $end)
    {
        $start = date('Ymd', strtotime($start));
        $end = date('Ymd', strtotime($end));

        return Lock::select('rid')
            ->where('hid', $hid)
            ->where('tid', $tid)
            ->where(DB::raw('( ' . $start . ' <= `start` AND ' . $end . ' >= `end` ) OR ( ' . $start . ' >= `start` AND ' . $end . ' <= `end` ) OR ( ' . $start . ' >= `start` AND ' . $start . ' < `end` AND `end` >= ' . $end . ' ) OR ( ' . $start . ' <= `start` AND ' . $start . ' >= `start` AND ' . $end . ' <= `end` )'))
            ->where('status', 0)
            ->get();
    }

    public function toGetLock($hid)
    {
        return Lock::select('lid', 'type', 'room_no', 'start', 'end', 'complete', 'remark', 'status')
            ->where('hid', $hid)
            ->orderBy('sid', 'DESC')
            ->get();
    }

    public function toCreate($input)
    {
        return Lock::create([
            'hid' => $input['hid'],
            'tid' => $input['tid'],
            'type' => $input['type'],
            'rid' => $input['rid'],
            'room_no' => $input['room_no'],
            'start' => $input['start'],
            'end' => $input['end'],
        ]);
    }

    public function toUpdate($lock, $complete = 0)
    {

        $lock->complete = $complete;
        $lock->status = 1;

        $lock->save();
    }

    public function toSimpleFind($lid, $hid)
    {
        return Lock::where('hid', $hid)
            ->where('lid', $lid)
            ->where('status', 0)
            ->first();
    }

    public function toCount($hid)
    {
        return Lock::where('hid', $hid)
            ->count();
    }

    public function toValidator(Request $request, $date)
    {
        $input = $request->only('tid', 'rid', 'start', 'end');

        $validator = Validator::make($input, [
            'tid' => 'bail|required|integer',
            'rid' => 'bail|required|integer',
            'start' => 'bail|required|date|after_or_equal:' . $date,
            'end' => 'bail|required|date|after_or_equal:start',
        ], [
            'tid.required' => '房型不能为空',
            'tid.integer' => '房型格式错误',
            'rid.required' => '房间不能为空',
            'rid.integer' => '房号格式错误',
            'start.required' => '开始日期不能为空',
            'start.data' => '开始日期格式错误',
            'start.after_or_equal' => '开始日期不能小于酒店当前时间',
            'end.required' => '结束日期不能为空',
            'end.data' => '结束日期格式错误',
            'end.after_or_equal' => '结束日期不能小于开始日期',
        ]);

        if ($validator->fails()) {

            return [
                'msg' => $validator->errors()->first(),
                'err' => 422,
            ];
        }
    }
}