<?php

namespace App\Http\Middleware;

use Closure;

class RenameAuthorizationHeader
{
    public function handle($request, Closure $next)
    {
        if($request->headers->get('Auth')) {
            $request->headers->set('Authorization','Bearer '.$request->headers->get('Auth'));
            $request->headers->remove('Auth');
        }

        return $next($request);
    }
}
