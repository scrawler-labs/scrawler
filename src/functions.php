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
