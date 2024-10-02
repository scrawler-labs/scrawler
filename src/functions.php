<?php
declare(strict_types=1);


if(!function_exists('app')){
    /**
     * Get the app instance
     * 
     * @return \Scrawler\App
     */
    function app(): \Scrawler\App{

        return \Scrawler\App::engine();
    }
   
}

if(!function_exists('config')){
    /**
     * Get the config instance
     * 
     * @return \PHLAK\Config\Config
     */
    function config():\PHLAK\Config\Config{
        return \Scrawler\App::engine()->config();
    }
}

if (! function_exists('url')) {
    /**
     * Generate a url
     * 
     * @param string $path
     * @return string
     */
    function url(string $path='')
    {
        if (\Scrawler\App::engine()->config()->has('https') && \Scrawler\App::engine()->config()->get('https')) {
            return 'https://'.\Scrawler\App::engine()->request()->getHttpHost().\Scrawler\App::engine()->request()->getBasePath().$path;
        }
        return \Scrawler\App::engine()->request()->getSchemeAndHttpHost().\Scrawler\App::engine()->request()->getBasePath().$path;
    }
}

if (! function_exists('env')) {
    /**
     * Get the value of an environment variable
     * 
     * @param string $key
     * @return mixed|null
     */
    function env(string $key):mixed
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
