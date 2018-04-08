<?php

namespace App\Http\Controllers\Help;

use Illuminate\Http\Request;

class Condition
{

    static function getCondition(Request $request, $auth, $official)
    {
        $tid = intval($request->get('tid'));

        if ($official->key != $auth['id']) { //    非官方渠道查询价格

            return [
                'channel' => $auth['id'],
                'vip' => 0,
                'cid' => 0,
                'tid' => $tid,
            ];
        } else {

            $channel = intval($request->get('channel'));
            $vip = intval($request->get('vip'));
            $cid = intval($request->get('cid'));

            $channel = empty($channel) ? $auth['id'] : $channel;

            if ($channel == $auth['id']) {
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
                        'channel' => $auth['id'],
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