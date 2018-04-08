<?php

namespace App\Http\Controllers\Notices;

class Wechat
{
    private $timeTmp = 1502088898;

    private $key = '08056aacac6f8525f272ca646c91887d';

    public $uri = 'https://www.mijiweb.com/api/check/';

    public $action;

    public $openid;

    public $oid;

    public function __construct($action = 'ordain')
    {
        if ($action === 'ordain') {

        }
    }

    public function ordainSuccess()
    {

    }

    public function ordainError()
    {

    }

    public function live()
    {

    }

    public function proceed()
    {

    }

    public function leave()
    {

    }

}