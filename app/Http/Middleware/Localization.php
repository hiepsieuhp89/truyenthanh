<?php

namespace App\Http\Middleware;

use Closure;
use Request;
use Illuminate\Support\Facades\Session;
use App;
use Config;
use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class Localization extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // if(!is_numeric(strpos(Request::fullUrl(), '?redirect',1)) && !is_numeric(strpos(Request::fullUrl(), '&redirect',1)))
            
        //     return redirect(Request::fullUrl().'?redirect');

        return $next($request);
    }
}
