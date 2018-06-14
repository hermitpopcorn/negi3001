<?php

namespace App\Http\Middleware;

use Closure;

class RenameAuthorizationHeader
{
    public function handle($request, Closure $next)
    {
        if($request->headers->get('Auth')) {
            $token = preg_replace('/^Bearer /i', '', $request->headers->get('Auth'));
            $request->headers->set('Authorization', 'Bearer ' . $token);
            $request->headers->remove('Auth');
        }

        return $next($request);
    }
}
