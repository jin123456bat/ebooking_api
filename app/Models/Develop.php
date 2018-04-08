<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Develop extends Model
{
    public function toGet()
    {
        return Develop::select('did', 'name', 'name_english', 'phone', 'openid', 'git', 'uri', 'is_push', 'description');
    }

    public function toGetPush()
    {
        $develop = Develop::select('openid')
            ->where('is_push', 1)
            ->get();

        $tmpArr = [];

        foreach ($develop as $value) {
            $tmpArr[] = $value['openid'];
        }

        return $tmpArr;
    }
}