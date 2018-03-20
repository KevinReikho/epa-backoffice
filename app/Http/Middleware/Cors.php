<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
     //protected $languages = ['fr','en'];
     //protected $languages = explode(",", env('LANGUAGES_ACCEPTED','fr'));
    public function handle($request, Closure $next)
    {
        $headers = [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Origin, Content-Type, Accept, Authorization, X-Request-With'
        ];

        $language_selected=$request->getPreferredLanguage(explode(",", env('LANGUAGES_ACCEPTED','fr')));
        App::setLocale($language_selected);
        $response = $next($request);
        foreach ($headers as $key => $value) {
            $response->headers->set($key, $value);
        }

        return $response;
    }
}
