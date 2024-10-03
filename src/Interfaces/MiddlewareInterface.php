<?php

namespace Scrawler\Interfaces;

use \Closure;

interface MiddlewareInterface
{   
    /** 
     * @param  \Scrawler\Http\Request  $request
     * @param  Closure $next
     * @return mixed         
     */
    public function run(\Scrawler\Http\Request $request,Closure $next);
}