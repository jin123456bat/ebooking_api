<?php

namespace App\Models;

use Validator;
use Illuminate\Auth\Authenticatable;
use Illuminate\Http\Request;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * App\Models\User
 *
 * @mixin \Eloquent
 */
class User extends Model implements JWTSubject, AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'password', 'hid', 'official', 'status'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password'
    ];

    public function toCreate($input)
    {
        return User::create([
            'name' => $input['name'],
            'password' => $input['password'],
            'hid' => $input['hid'],
            'official' => $input['official'],
            'status' => $input['status'],
        ]);
    }

    public function toFindUser($key, $hid = 0)
    {
        return User::where('id', $key)
            ->where('hid', (empty($hid) ? '<>' : '='), $hid)
            ->first();
    }

    static function toFindOfficial($hid)
    {
        return User::select('id as key', 'name', 'description')
            ->where('hid', $hid)
            ->where('official', 1)
            ->first();


    }

    public function toGet($hid)
    {
        return User::select('id', 'name', 'official')
            ->where('hid', $hid)
            ->where('status', 1)
            ->get();
    }

    public function validateAuth(Request $request)
    {

        $input['id'] = $request->get('key');
        $input['password'] = $request->get('token');

        $validator = Validator::make($input, [
            'id' => 'bail|required|min:10000|integer',
            'password' => 'bail|required|min:32|alpha_num'
        ], [
            'id.required' => 'key 必须存在',
            'id.min' => 'key 格式错误',
            'id.integer' => 'key 格式错误',
            'password.required' => 'token 必须存在',
            'password.min' => 'token 格式错误',
            'password.alpha_num' => 'token 格式错误'
        ]);

        if ($validator->fails()) {

            return [
                'msg' => $validator->errors()->first(),
                'err' => 403
            ];
        }
    }

    public function toValidatorRegister($input)
    {
        $validator = Validator::make($input, [
            'name' => 'bail|required|max:15',
            'hid' => 'bail|required|integer|exists:hotels,hid',
            'official' => 'bail|required|boolean',
            'status' => 'bail|required|boolean',
        ], [
            'name.required' => 'Channel name can not be empty',
            'name.max' => 'Channel name up to 15 characters',
            'hid.required' => 'Hotel ID can not be empty',
            'hid.integer' => 'Hotel ID is malformed',
            'hid.exists' => 'The hotel does not exist',
            'official.required' => 'Please choose whether to be the official channel',
            'official.boolean' => 'Whether the official channel format is wrong',
            'status.required' => 'Please choose whether to enable it immediately',
            'status.boolean' => 'Whether formatting is enabled immediately',

        ]);

        if ($validator->fails()) {

            return $validator->errors()->first();
        }
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
