<?php

namespace App\Http\Controllers\Router;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\JWTAuth;
use Tymon\JWTAuth\Manager;

class AuthController extends Controller
{

    private $jwt;

    private $manager;

    public function __construct(JWTAuth $jwt, Manager $manager)
    {
        $this->jwt = $jwt;
        $this->manager = $manager;
    }

    /**
     * 登陆验证, 生成 JWT 令牌
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function auth(Request $request)
    {

        $User = new User();

        $validator = $User->validateAuth($request);

        if (!empty($validator)) return $validator;

        $key = $request->get('key');
        $token = $request->get('token');

        $user = $User->toFindUser($key);

        if (empty($user)) {
            return response()->json([
                'msg' => '配置错误',
                'err' => 422
            ]);
        }

        if (Hash::check($token, $user->passwrod)) {
            return response()->json([
                'msg' => '授权失败',
                'err' => 422
            ]);
        }

        $authToken = $this->jwt->fromSubject($user);

        return response()->json([
            'token' => $authToken,
            'err' => 0
        ]);
    }

    /**
     * 刷新令牌
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        try {

            $oldToken = $this->jwt->getToken();

            $token = $this->manager->refresh($oldToken);

            return response()->json([
                'token' => $token->get(),
                'err' => 0
            ]);

        } catch (TokenExpiredException $e) {

            throw new AuthException(
                Constants::get('error_code.refresh_token_expired'),
                trans('errors.refresh_token_expired'), $e);
        } catch (JWTException $e) {

            throw new AuthException(
                Constants::get('error_code.token_invalid'),
                trans('errors.token_invalid'), $e);
        }

    }

    public function logout()
    {

        $token = $this->jwt->getToken();
        $this->jwt->invalidate($token);

        return response()->json([
            'msg' => '退出成功',
            'err' => '0'
        ]);

    }

}