<?php

namespace App\Exceptions;

use GuzzleHttp\Exception\RequestException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->renderable(function (ConnectionException $e, Request $request) {
            if($request->ajax()) {
                return response()->json([
                    'error' => 'Api Request Timeout'
                ], 400);
            }
        });

        $this->renderable(function (RequestException $e, Request $request) {
            if($request->ajax()) {
                return response()->json([
                    'error' => 'Api Request Could Not Resolve'
                ], 400);
            }
        });

    }
}
