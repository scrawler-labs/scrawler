<?php

namespace Scrawler\Interfaces;

interface MiddlewareInterface
{
    public function run(\Scrawler\Http\Request $request, \Closure $next): \Scrawler\Http\Response;
}
