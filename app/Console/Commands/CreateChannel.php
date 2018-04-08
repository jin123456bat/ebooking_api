<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateChannel extends Command
{

    private $password;

    private $User;

    private $channel;

    /**
     * 控制台命令 signature 的名称。
     *
     * @var string
     */
    protected $signature = 'create:channel';

    /**
     * 控制台命令说明。
     *
     * @var string
     */
    protected $description = 'Create a channel key and token';

    /**
     * 创建一个新的命令实例。
     *
     * @param  DripEmailer $drip
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->User = new User();
    }

    /**
     * 执行控制台命令。
     *
     * @return mixed
     */
    public function handle()
    {
        $input = [];

        $name = $this->ask('Please enter a channel name');

        $input['name'] = $name;

        $hotel = $this->ask('Please enter hotel ID');

        $input['hid'] = $hotel;

        $isOfficial = 1;

        if ($this->confirm('Whether it is added as an official channel')) {

            $isOfficial = 0;
        }

        $input['official'] = $isOfficial;

        $isEnable = 0;

        if ($this->confirm('Whether to enable this channel account immediately')) {

            $isEnable = 1;
        }

        $input['status'] = $isEnable;

        $validator = $this->User->toValidatorRegister($input);

        if (!empty($validator)) {

            $this->error($validator);
        }

        $this->create($input);

        if (empty($this->channel)) {

            $this->error('渠道信息写入失败');
        }

        $this->info('渠道信息添加成功');

        $this->info('key : ' . $this->channel->id);
        $this->info('token : ' . $this->password);
    }

    public function create($input)
    {
        $password = str_random(32);

        $this->password = $password;

        $input['password'] = Hash::make($password);

        $user = $this->User->toCreate($input);

        $this->channel = $user;
    }
}