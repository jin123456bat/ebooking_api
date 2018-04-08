<?php

namespace App\Http\Controllers\Help;

use App\Models\Exception;
use Illuminate\Support\Facades\Log;

class ExceptionNotice
{

    private $type;

    private $message;

    private $file;

    private $request;

    private $develop;

    private $exception;

    public function __construct($type, $message, $file, $request, $develop)
    {
        $this->type = $type;

        $this->message = $message;

        $this->file = $file;

        $this->request = $request;

        $this->develop = $develop;
    }

    public function toSendWeChat()
    {
        $this->toCreateException();

        $app = app('wechat.official_account');

        $notice = $app->template_message;

        $data = [

            'first' => array("Pms 系统异常 - " . config('app.name'), "#0745CA"),
            'keyword1' => array($this->file, "#E50606"),
            'keyword2' => array(str_limit($this->message, 110), "#E50606"),
            'remark' => 'Power By Uper - ' . (date('H:i:s')),
        ];

        foreach ($this->develop as $value) {

            $messageId = $notice->send([
                'touser' => $value,
                'template_id' => 'OwCYi1jW31HSP0-sV29NWhR5gWgO-ZkTVgFzNY9Xjmk',
                'url' => 'https://ebooking.mijiweb.com/api/exception/' . $this->exception,
                'data' => $data,
            ]);

            if ($messageId['errcode'] != 0) {

                Log::debug('微信 bug 推送失败：' . $this->message);
            }
        }
    }

    private function toCreateException()
    {
        $Exception = new Exception();

        $input['hid'] = 0;
        $input['type'] = $this->type;
        $input['file'] = $this->file;
        $input['error'] = $this->message;
        $input['request'] = empty($this->request) ? '' : $this->request;

        $exception = $Exception->toCreate($input);

        if (empty($exception)) {
            Log::debug('异常写入失败：' . $this->message);
        }

        $this->exception = $exception;
    }
}