<?php

namespace Tests\Middleware;

use Scrawler\Interfaces\MiddlewareInterface;

class Test implements MiddlewareInterface
{
    #[\Override]
    public function run(\Scrawler\Http\Request $request, \Closure $next): \Scrawler\Http\Response
    {
        $response = $next($request);
        $response->setStatusCode(200);
        $response->setContent('Overtaken by middleware');

        return $response;
    }
}
