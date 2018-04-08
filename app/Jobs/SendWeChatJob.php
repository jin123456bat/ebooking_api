<?php

namespace App\Jobs;

use App\Http\Controllers\Help\ExceptionNotice;
use Illuminate\Support\Facades\Log;

class SendWeChatJob extends Job
{

    private $type;

    private $message;

    private $file;

    private $request;

    private $develop;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($type, $message, $file, $request, $develop)
    {
        $this->type = $type;

        $this->message = $message;

        $this->file = $file;

        $this->request = $request;

        $this->develop = $develop;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $ExceptionNotice = new ExceptionNotice($this->type, $this->message, $this->file, $this->request, $this->develop);

        $ExceptionNotice->toSendWeChat();
    }

    public function failed(\Exception $e)
    {
        Log::debug('微信 bug 推送失败：' . $this->message);
    }
}
