<?php

namespace App\Exceptions;

use App\Jobs\SendWeChatJob;
use App\Models\Develop;
use Exception;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception $e
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if ($e instanceof \ErrorException) {

            $message = $e->getMessage();
            $file = basename($e->getFile()) . ':' . $e->getLine() . ' 行';

            if (config('app.push_bug')) {

                $Develop = new Develop();

                $develop = $Develop->toGetPush();

                if (empty($develop)) {

                    Log::debug('暂无开发人员信息, 无法推送');
                } else {

                    dispatch(new SendWeChatJob('ErrorException', $message, $file, json_encode($request->all(), JSON_UNESCAPED_UNICODE), $develop));
                }
            }

            return response()->json([
                'msg' => '服务器开小差儿了, 请稍后重试',
                'err' => 500
            ], 500);
        } else if ($e instanceof UnauthorizedHttpException) {

            return response()->json([
                'msg' => '授权失败, 请检查您的令牌',
                'err' => 401
            ], 401);
        } else if ($e instanceof MethodNotAllowedHttpException) {

            return response()->json([
                'msg' => '路由错误, 请稍后重试',
                'err' => 405
            ]);
        } else if ($e instanceof NotFoundHttpException) {

            return response()->json([
                'msg' => '路由错误, 请稍后重试',
                'err' => 405
            ]);
        } else if ($e instanceof MassAssignmentException) {

            $message = $e->getMessage();
            $file = basename($e->getFile()) . ':' . $e->getLine() . ' 行';

            if (config('app.push_bug')) {

                $Develop = new Develop();

                $develop = $Develop->toGetPush();

                if (empty($develop)) {

                    Log::debug('暂无开发人员信息, 无法推送');
                } else {

                    dispatch(new SendWeChatJob('MassAssignmentException', $message, $file, json_encode($request->all(), JSON_UNESCAPED_UNICODE), $develop));
                }
            }

//            dispatch(new CreateExceptionJob($auth['hid'], 'MassAssignmentException', $message, $file, $request));

            return response()->json([
                'msg' => '参数错误, 请稍后重试',
                'err' => 405
            ]);
        } else if ($e instanceof QueryException) {

            $message = $e->getMessage();
            $file = basename($e->getFile()) . ':' . $e->getLine() . ' 行';

            if (config('app.push_bug')) {

                $Develop = new Develop();

                $develop = $Develop->toGetPush();

                if (empty($develop)) {

                    Log::debug('暂无开发人员信息, 无法推送');
                } else {
                    dispatch(new SendWeChatJob('QueryException', $message, $file, json_encode($request->all(), JSON_UNESCAPED_UNICODE), $develop));
                }
            }

            return response()->json([
                'msg' => '服务器开小差儿了, 请稍后重试',
                'err' => 500
            ], 500);
        } else if ($e instanceof \ReflectionException) {

            $message = $e->getMessage();
            $file = basename($e->getFile()) . ':' . $e->getLine() . ' 行';

            if (config('app.push_bug')) {

                $Develop = new Develop();

                $develop = $Develop->toGetPush();

                if (empty($develop)) {

                    Log::debug('暂无开发人员信息, 无法推送');
                } else {

                    dispatch(new SendWeChatJob('ReflectionException', $message, $file, json_encode($request->all(), JSON_UNESCAPED_UNICODE), $develop));
                }
            }

            return response()->json([
                'msg' => '服务器开小差儿了, 请稍后重试',
                'err' => 500
            ], 500);
        }

        return parent::render($request, $e);
    }
}
