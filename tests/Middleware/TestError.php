<?php
namespace Tests\Middleware;
use Scrawler\Interfaces\MiddlewareInterface;

class TestError
{
    public function run(\Scrawler\Http\Request $request, \Closure $next)
    {
        $response = $next($request);
        $response->setStatusCode(200);
        $response->setContent('Overtaken by middleware');
        return $response;
    }
}