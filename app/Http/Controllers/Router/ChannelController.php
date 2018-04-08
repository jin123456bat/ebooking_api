<?php

namespace App\Http\Controllers\Router;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class ChannelController extends Controller
{
    private $auth;

    public function __construct()
    {
        $this->auth = Auth::user();
    }

    public function get()
    {
        $User = new  User();

        $channel = $User->toGet($this->auth['hid']);

        foreach ($channel as $key => $value) {
            if ($value->official == 1) {
                $channel[$key]->official = true;
            } else {
                $channel[$key]->official = false;
            }
        }

        return [
            'msg' => '查询成功',
            'data' => $channel,
            'err' => 0,
        ];
    }

    public function system()
    {
        $Hotel = new Hotel();

        $hotel = $Hotel->toFind($this->auth['hid']);

//        $hotel->hid = Crypt::encrypt($hotel->hid);

        if (empty($hotel)) {
            return [
                'msg' => '查询成功',
                'data' => null,
                'err' => 0,
            ];
        }

        $hotel->time = date('Y-m-d', strtotime($hotel->time));

        return [
            'msg' => '查询成功',
            'data' => $hotel,
            'err' => 0,
        ];
    }
}