<?php

namespace App\Jobs;

use App\Http\Controllers\Help\Ordain;
use Exception;

class OrdainJob extends Job
{
    private $user;

    private $no;

    private $channel;

    private $channel_name;

    private $company;

    private $company_name;

    private $vip;

    private $vip_name;

    private $content;

    private $payment;

    private $remark;

    private $operate;

    private $uid;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user, $no, $channel, $channel_name, $company, $company_name, $vip, $vip_name, $content, $payment, $remark, $operate, $uid)
    {
        $this->user = $user;

        $this->no = $no;

        $this->channel = $channel;

        $this->channel_name = $channel_name;

        $this->company = $company;

        $this->company_name = $company_name;

        $this->vip = $vip;

        $this->vip_name = $vip_name;

        $this->content = $content;

        $this->payment = $payment;

        $this->remark = $remark;

        $this->operate = $operate;

        $this->uid = $uid;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public
    function handle()
    {
        $OrdainController = new Ordain($this->user, $this->no, $this->channel, $this->channel_name, $this->company, $this->company_name, $this->vip, $this->vip_name, $this->content, $this->payment, $this->remark, $this->operate);

        $order = $OrdainController->ordain();

        if (!empty($order)) {

//            return $order;    //  通知预定错误信息
        }

        //  通知预定成功消息


    }

    /**
     * 队列执行失败执行函数
     *
     * @param Exception $e 队列执行失败的异常信息
     */
    public
    function failed(Exception $e)
    {
        //  通知用户预定失败
    }
}
