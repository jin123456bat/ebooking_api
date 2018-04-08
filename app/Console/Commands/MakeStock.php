<?php

namespace App\Console\Commands;

use App\Models\Hotel;
use App\Models\Stock;
use App\Models\Type;
use Illuminate\Console\Command;

class MakeStock extends Command
{

    private $cycle;

    /**
     * 控制台命令 signature 的名称。
     *
     * @var string
     */
    protected $signature = 'make:stock {cycle=year}';

    /**
     * 控制台命令说明。
     *
     * @var string
     */
    protected $description = 'Initialize room inventory';

    /**
     * 创建一个新的命令实例。
     *
     * @param  DripEmailer $drip
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 执行控制台命令。
     *
     * @return mixed
     */
    public function handle()
    {
        $this->cycle = $this->argument('cycle');

        if ($this->cycle != 'year' && $this->cycle != 'month' && $this->cycle != 'week') {
            $this->error('Parameter is wrong, please check your command and try again');
            $this->error('php artisan make:stock year|month|week');
            return false;
        }

        $this->initStock();
    }

    private function initStock()
    {
        $Hotel = new Hotel();
        $Type = new Type();
        $Stock = new Stock();

        $hotel = $Hotel->toGet();

        foreach ($hotel as $value) {

            $type = $Type->toGet($value->hid);

            foreach ($type as $val) {

                $this->info($val->tid . ' ' . $val->stock);

                $stock = $Stock->toCount($val->tid);

                $date = empty($value->time) ? date('Ymd') : $value->time;

                if (empty($stock)) {

                    $in = $Stock->init($value->hid, $val->tid, $val->stock, $date);
                } else {

                    $stock = $Stock->toFindLast($value->hid, $val->tid);

                    $start = $stock->date;
                    $in = $Stock->init($value->hid, $val->tid, $val->stock, $start, 'week');

                    if (!$in) {
                        //  库存生成失败代码
                    }
                }
            }
        }
    }
}