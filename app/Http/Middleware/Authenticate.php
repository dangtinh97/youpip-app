<?php

namespace App\Http\Middleware;

use App\Enums\EStatusApi;
use App\Http\Response\ResponseError;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if ($request->expectsJson()) {
            return response()->json((new ResponseError(EStatusApi::AUTHENTICATION->value,"Authentication !")));
        }
    }
}
