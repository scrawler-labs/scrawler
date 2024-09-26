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
