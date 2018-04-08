<?php

namespace App\Http\Controllers\Router;

use App\Http\Controllers\Controller;
use App\Models\Pay;
use Illuminate\Support\Facades\Auth;

class PayController extends Controller
{
    private $auth;

    public function __construct()
    {
        $this->auth = Auth::user();
    }

    public function get()
    {
        $Pay = new Pay();

        $pay = $Pay->toGet($this->auth['hid']);

        return [
            'msg' => '查询成功',
            'data' => $pay,
            'err' => 0,
        ];
    }
}