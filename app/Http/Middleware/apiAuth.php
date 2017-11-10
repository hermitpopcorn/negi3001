<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Auth;
use App\User;
use Closure;

class apiAuth
{
    public function handle($request, Closure $next)
    {
        // Get API token from header
        $token = $request->header('auth');
        if($token) {
            $auth = User::getByToken($token);
            if($auth) {
                Auth::onceUsingId($auth->id);
                return $next($request);
            }
        }

        return abort(403);
    }
}
