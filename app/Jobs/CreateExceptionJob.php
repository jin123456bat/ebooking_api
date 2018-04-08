<?php

namespace App\Jobs;

use App\Models\Exception;
use Illuminate\Support\Facades\Log;

class CreateExceptionJob extends Job
{
    private $hid;

    private $message;

    private $file;

    private $type;

    private $request;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($hid, $type, $message, $file, $request)
    {
        $this->hid = $hid;

        $this->type = $type;

        $this->message = $message;

        $this->file = $file;

        $this->request = json_encode($request, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $Exception = new Exception();

        $input['hid'] = $this->hid;
        $input['type'] = $this->type;
        $input['file'] = $this->file;
        $input['error'] = $this->message;
        $input['request'] = $this->request;

        $exception = $Exception->toCreate($input);

        if (empty($exception)) {
            Log::debug('异常写入失败：' . $this->message);
        }
    }

    public function failed(\Exception $e)
    {
        Log::debug('异常写入失败：' . $this->message);
    }
}
