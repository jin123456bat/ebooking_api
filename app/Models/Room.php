<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Validator;

class Room extends Model
{

    protected $primaryKey = 'rid';

    protected $fillable = [
        'hid', 'fid', 'bid', 'tid', 'room_no', 'lock_type', 'remark', 'status'
    ];

    protected $guarded = [];

    public function toCreate($floor)
    {
        return Room::create([
            'hid' => $floor['hid'],
            'fid' => $floor['fid'],
            'bid' => $floor['bid'],
            'tid' => $floor['tid'],
            'room_no' => $floor['room_no'],
            'lock_no' => $floor['lock_no'],
            'lock_type' => $floor['lock_type'],
            'wifi' => $floor['wifi'],
            'remark' => $floor['remark'],
            'status' => 1,
        ]);
    }

    public function toDelete($rid, $hid)
    {
        return Room::where('hid', $hid)
            ->where('rid', $rid)
            ->delete();
    }

    public function toUpdateStatus($hid, $rid, $status)
    {
        return Room::where('hid', $hid)
            ->where('rid', $rid)
            ->update(['status' => $status]);
    }

    public function toUpdate(Room $room, $input)
    {
        $room->fid = $input['fid'];
        $room->tid = $input['tid'];
        $room->room_no = $input['room_no'];
        $room->lock_no = $input['lock_no'];
        $room->lock_type = $input['lock_type'];
        $room->wifi = $input['wifi'];
        $room->remark = $input['remark'];

        $room->save();
    }

    public function toGet($hid, $input = [])
    {
        $sql = 'SELECT rooms.rid, rooms.bid, builds.name AS build_name, rooms.fid, floors.name AS floor_name, rooms.tid, types.name AS type_name, room_no, link, rooms.remark, rooms.status FROM `rooms` LEFT JOIN builds ON rooms.bid = builds.bid LEFT JOIN floors ON rooms.fid = floors.fid LEFT JOIN types ON rooms.tid = types.tid WHERE rooms.hid = ? ' . ((empty($input['status']) ? '' : ' AND rooms.status IN (' . implode(',', $input['status']) . ')') . (empty($input['type']) ? '' : ' AND rooms.tid IN (' . implode(',', $input['type']) . ')') . (empty($input['build']) ? '' : ' AND rooms.bid IN (' . implode(',', $input['build']) . ')')) . ' ORDER BY bid ASC, fid ASC';
        return DB::select($sql, [$hid]);
    }

    public function toGetPage($hid, $num, $page = 0, $input = [])
    {
        $sql = 'SELECT rooms.rid, rooms.bid, builds.name AS build_name, rooms.fid, floors.name AS floor_name, rooms.tid, types.name AS type_name, room_no, link, rooms.remark, rooms.status FROM `rooms` LEFT JOIN builds ON rooms.bid = builds.bid LEFT JOIN floors ON rooms.fid = floors.fid LEFT JOIN types ON rooms.tid = types.tid WHERE rooms.hid = ? ' . ((empty($input['status']) ? '' : ' AND rooms.status IN (' . implode(',', $input['status']) . ')') . (empty($input['type']) ? '' : ' AND rooms.tid IN (' . implode(',', $input['type']) . ')') . (empty($input['build']) ? '' : ' AND rooms.bid IN (' . implode(',', $input['build']) . ')')) . ' AND rid <= (SELECT rid FROM rooms WHERE rooms.hid = ? ' . ((empty($input['status']) ? '' : ' AND rooms.status IN (' . implode(',', $input['status']) . ')') . (empty($input['type']) ? '' : ' AND rooms.tid IN (' . implode(',', $input['type']) . ')') . (empty($input['build']) ? '' : ' AND rooms.bid IN (' . implode(',', $input['build']) . ')')) . ' ORDER BY rid DESC LIMIT ?,1) ORDER BY rid DESC LIMIT ?';
        return DB::select($sql, [$hid, $hid, $num * $page, $num]);
    }

    public function toSimpleGet($tid)
    {
        return Room::select('rid', 'room_no', 'remark')
//            ->leftJoin('types', 'types.tid', '=', 'rooms.tid')
            ->where('rooms.tid', $tid)
            ->orderBy('room_no', 'ASC')
            ->get();
    }

    public function toCountTidRoom($hid, $tid)
    {
        $result = DB::select('SELECT COUNT(*) AS num FROM rooms WHERE tid = ? AND hid = ? LIMIT 1', [$tid, $hid]);
        return $result[0]->num;
    }

    public function toCountFidRoom($hid, $fid)
    {
        $result = DB::select('SELECT COUNT(*) AS num FROM rooms WHERE fid = ? AND hid = ? LIMIT 1', [$fid, $hid]);
        return $result[0]->num;
    }

    public function toSimpleFind($rid, $hid, $tid = null)
    {
        return Room::select('rid', 'fid', 'bid', 'rooms.tid', 'types.name as type', 'room_no', 'rooms.remark', 'rooms.status')
            ->leftJoin('types', 'rooms.tid', '=', 'types.tid')
            ->where('rid', $rid)
            ->where('rooms.hid', $hid)
            ->where('rooms.tid', (empty($tid) ? '<>' : '='), $tid)
            ->first();
    }

    public function toFind($hid, $bid, $fid, $tid, $rid)
    {
        return Room::where('hid', $hid)
            ->where('bid', $bid)
            ->where('fid', $fid)
            ->where('tid', $tid)
            ->where('rid', $rid)
            ->first();
    }

    public function toSimpleFindAll($rid, $hid)
    {
        return Room::where('hid', $hid)
            ->where('rid', $rid)
            ->first();
    }

    public function toCount($hid)
    {
        return Room::where('hid', $hid)
            ->count();
    }

    public function toValidator(Request $request)
    {
        $input = $request->only('room_no', 'remark');

        $validator = Validator::make($input, [
            'room_no' => 'bail|required|max:12',
            'wifi' => 'bail|sometimes|max:20',
            'remark' => 'bail|sometimes|max:60',
        ], [
            'room_no.required' => '房号名称不能为空',
            'room_no.max' => '房号最多 12 个字符',
            'wifi.max' => 'WIFI 密码最多 20 个字符',
            'remark.max' => '房间备注最多 60 个字符',
        ]);

        if ($validator->fails()) {

            return [
                'msg' => $validator->errors()->first(),
                'err' => 422,
            ];
        }
    }

    public function toUniqueRoom(Request $request, $hid, $isCreate = false)
    {

        $num = Room::where('hid', $hid)
            ->where('fid', $request->get('fid', 0))
            ->where('bid', $request->get('bid', 0))
            ->where('tid', $request->get('tid', 0))
            ->where('room_no', $request->get('room_no'))
            ->where('rid', '<>', ($isCreate ? 0 : $request->get('rid')))
            ->count();

        if (empty($num)) {
            return false;
        } else {
            return true;
        }
    }

    public function toUniqueLock(Request $request, $hid)
    {
        if (empty($request->get('lock_no'))) {
            return false;
        }

        $num = Room::where('lock_no', $request->get('lock_no'))
            ->where('lock_type', $request->get('lock_type', 1))
            ->where('hid', $hid)
            ->count();

        if (empty($num)) {
            return false;
        } else {
            return true;
        }
    }
}
