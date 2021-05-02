<?php

namespace App\Http\Middleware;

use Closure;
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

        $lang = Session::has('lan')?Session::get('lan'):'vn';

        //dd(Session::get('lan'));

        //App::setLocale($lang);

        return $next($request);
    }
}
