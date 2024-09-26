<?php

if(!function_exists('app')){
    if(is_null(\Scrawler\App::engine())){
        new \Scrawler\App();
    }
    return \Scrawler\App::engine();
}

if(!function_exists('config')){
    function app(){
        return \Scrawler\App::engine()->config();
    }
}

if (! function_exists('url')) {
    function url($path='')
    {
        if (\Scrawler\App::engine()->config()->has('https') && \Scrawler\App::engine()->config()->get('https')) {
            return 'https://'.\Scrawler\App::engine()->request()->getHttpHost().\Scrawler\App::engine()->request()->getBasePath().$path;
        }
        return \Scrawler\App::engine()->request()->getSchemeAndHttpHost().\Scrawler\App::engine()->request()->getBasePath().$path;
    }
}

if (! function_exists('env')) {
    function env($key)
    {
         if (isset($_ENV[$key])) {
            return $_ENV[$key];
         }
         if(getenv($key)){
            return getenv($key);
         }
       
         if(request()->server->has($key)){
            return request()->server->get($key);
         }

         return null;
    }
}
