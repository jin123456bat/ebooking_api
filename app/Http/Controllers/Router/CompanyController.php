<?php

namespace App\Http\Controllers\Router;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Overtrue\Pinyin\Pinyin;

class CompanyController extends Controller
{
    private $auth;

    private $date;

    public function __construct()
    {
        $this->auth = Auth::user();

        $this->date = Hotel::toGetTime($this->auth['hid']);
    }

    public function get()
    {
        $Company = new Company();

        $num = $Company->toCount($this->auth['hid']);

        $company = null;

        if (!empty($num)) {
            $company = $Company->toGet($this->auth['hid']);
        }

        return [
            'msg' => '查询成功',
            'data' => $company,
            'err' => 0,
        ];
    }

    public function line()
    {
        $Company = new Company();

        $num = $Company->toCount($this->auth['hid']);

        $company = null;

        if (!empty($num)) {
            $company = $Company->toLine($this->auth['hid']);
            foreach ($company as $key => $value) {
                if ($value->end < $this->date) {
                    $company[$key]->status = 2;
                }
            }
        }

        return [
            'msg' => '查询成功',
            'data' => $company,
            'err' => 0,
        ];
    }

    public function create(Request $request)
    {

        $name = $request->get('name');
        $start = $request->get('start', $this->date);
        $end = $request->get('end');
        $description = $request->get('description');

        //  开始验证
        if (empty($name)) {
            return [
                'msg' => '协议公司名称不能为空',
                'err' => 40401
            ];
        }

        if ($start != $this->date && strtotime($start) < strtotime($this->date)) {
            return [
                'msg' => '协议协议生效时间不能小于酒店当前时间',
                'err' => 42201
            ];
        }

        if (!empty($end) && strtotime($end) < strtotime($start)) {
            return [
                'msg' => '协议协议失效时间不能小于生效时间',
                'err' => 42202
            ];
        }

        if (!empty($description) && mb_strlen($description) > 255) {
            return [
                'msg' => '协议备注最多 255 个字符',
                'err' => 42203
            ];
        }

        $Company = new Company();

        if ($Company->toUnique($this->auth['hid'], $name)) {
            return [
                'msg' => '协议公司信息已存在',
                'err' => 42204,
            ];
        }

        $pinyin = new Pinyin('Overtrue\Pinyin\MemoryFileDictLoader');

        $input['hid'] = $this->auth['hid'];
        $input['name'] = $name;
        $input['name_pinyin'] = $pinyin->abbr($name);
        $input['description'] = $description;
        $input['start'] = date('Ymd', strtotime($start));
        $input['end'] = empty($end) ? '' : date('Ymd', strtotime($end));

        $company = $Company->toCreate($input);

        if (empty($company)) {

            return [
                'msg' => '协议公司添加失败',
                'err' => 42205,
            ];
        }

        return [
            'msg' => '查询成功',
            'err' => 0,
        ];
    }

    public function edit(Request $request)
    {
        $cid = $request->get('cid');
        $name = $request->get('name');
        $start = $request->get('start', $this->date);
        $end = $request->get('end');
        $description = $request->get('description');

        //  开始验证
        if (empty($cid)) {
            return [
                'msg' => '协议公司不能为空',
                'err' => 40401
            ];
        }

        if (empty($name)) {
            return [
                'msg' => '协议公司名称不能为空',
                'err' => 40402
            ];
        }

        $Company = new Company();

        $company = $Company->toFindCompany($cid, $this->auth['hid']);

        if (empty($company)) {
            return [
                'msg' => '未找到相关协议信息',
                'err' => 40403
            ];
        }

        $intStart = strtotime($start);
        $intDate = strtotime($company->start);
        $intEnd = strtotime($end);

        if ($intStart < $intDate) {
            return [
                'msg' => '协议协议生效时间不能小于原签署当前时间',
                'err' => 42201
            ];
        }

        if (!empty($intEnd) && $intEnd < $intStart) {
            return [
                'msg' => '协议协议失效时间不能小于生效时间',
                'err' => 42202
            ];
        }

        if (mb_strlen($description) > 255) {
            return [
                'msg' => '协议备注最多 255 个字符',
                'err' => 42203
            ];
        }

        if ($Company->toUnique($this->auth['hid'], $name, $company->cid, true)) {
            return [
                'msg' => '协议公司信息已存在',
                'err' => 42204,
            ];
        }

        $pinyin = new Pinyin('Overtrue\Pinyin\MemoryFileDictLoader');

        $input['name'] = $name;
        $input['name_pinyin'] = $pinyin->abbr($name);
        $input['description'] = $description;
        $input['start'] = date('Ymd', $intStart);
        $input['end'] = empty($intEnd) ? '' : date('Ymd', $intEnd);

        $affected = $Company->toUpdate($input, $this->auth['hid'], $company->cid);

        if (empty($affected)) {

            return [
                'msg' => '协议公司更新失败',
                'err' => 42205,
            ];
        }

        return [
            'msg' => '查询成功',
            'err' => 0,
        ];
    }

    public function change(Request $request)
    {
        $cid = $request->get('cid');

        //  开始验证
        if (empty($cid)) {
            return [
                'msg' => '协议公司不能为空',
                'err' => 40401
            ];
        }

        $Company = new Company();

        $company = $Company->toFindCompany($cid, $this->auth['hid']);

        if (empty($company)) {
            return [
                'msg' => '未找到相关协议信息',
                'err' => 40402
            ];
        }

        $status = $company->status == 0 ? 1 : 0;

        $affected = $Company->toUpdateStatus($status, $this->auth['hid'], $company->cid);

        if (empty($affected)) {

            return [
                'msg' => '协议状态更新失败',
                'err' => 42205,
            ];
        }

        return [
            'msg' => '查询成功',
            'err' => 0,
        ];
    }

    public function search(Request $request)
    {
        $key = $request->get('key');

        if (empty($key)) {
            return [
                'msg' => '筛选项不能为空',
                'err' => 40401
            ];
        }

        $Company = new Company();

        $company = $Company->toSearchPinyin($key, $this->auth['hid'], $this->date);

        return [
            'msg' => '查询成功',
            'data' => $company,
            'err' => 0,
        ];
    }
}