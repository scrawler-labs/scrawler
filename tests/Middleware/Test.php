<?php

namespace Tests\Middleware;

use Scrawler\Interfaces\MiddlewareInterface;

class Test implements MiddlewareInterface
{
    #[\Override]
    public function run(\Scrawler\Http\Request $request, \Closure $next)
    {
        $response = $next($request);
        $response->setStatusCode(200);
        $response->setContent('Overtaken by middleware');

        return $response;
    }
}
