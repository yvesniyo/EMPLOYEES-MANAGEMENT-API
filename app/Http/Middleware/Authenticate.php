<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Facades\Response;

class Authenticate
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {

        if (!FacadesAuth::guard($guard)->check()) {
            return Response::json([
                "error" => "Unauthorized.",
                "status" => 401
            ], 401);
        }


        /** @var \App\Models\Employee */
        $user = FacadesAuth::guard($guard)->user();
        if (!$user->isActive()) {
            return Response::json([
                "error" => "This account was suspended.",
                "status" => 403
            ], 403);
        }


        return $next($request);
    }
}
