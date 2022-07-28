<?php

namespace EscolaLms\HeadlessH5P\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class QueryToken
{
    public function handle(Request $request, Closure $next)
    {
        // If the URL contains a token parameter - attach it as the Authorization header
        if ($request->has('_token') && !$request->headers->has('Authorization')) {
            $request->headers->set('Authorization', 'Bearer ' . $request->_token);
        }
        return $next($request);
    }
}
