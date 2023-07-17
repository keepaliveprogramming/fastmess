<?php

namespace App\Http\Middleware;

use Closure;
use HTMLPurifier;
use HTMLPurifier_Config;

class HtmlPurifierMiddleware
{
    public function handle($request, Closure $next)
    {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed', 'p,b,strong,i,em,a[href|title]');
        $purifier = new HTMLPurifier($config);

        $input = $request->all();
        $filteredInput = [];

        foreach ($input as $key => $value) {
            $filteredInput[$key] = $purifier->purify($value);
        }

        $request->replace($filteredInput);

        return $next($request);
    }
}
