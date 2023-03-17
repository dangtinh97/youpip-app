<?php

namespace App\Http\Middleware;

use App\Enums\EStatusApi;
use App\Http\Response\ResponseError;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuthApiMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|JsonResponse
     */
    public function handle(Request $request, Closure $next): JsonResponse|Response|RedirectResponse
    {
        if (!Auth::id()) {
            return response()->json((new ResponseError(EStatusApi::AUTHENTICATION->value,
                "Authentication !"))->toArray());
        }

        return $next($request);
    }
}
