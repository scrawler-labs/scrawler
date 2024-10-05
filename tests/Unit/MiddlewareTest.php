<?php

it('tests for middleware ', function () {

    $app = new \Scrawler\App();
    $app->middleware(function (\Scrawler\Http\Request $request,\Closure $next) {
        $response = $next($request);
        $response->setStatusCode(200);
        $response->setContent('Overtaken by middleware');
        return $response;
    });
    $request = \Scrawler\Http\Request::create(
        '/',
        'GET',
    );
    $response = $app->dispatch($request);
    expect($response->getContent())->toBe('Overtaken by middleware');
});

it('tests for middlewareInterface ', function () {

    $app = new \Scrawler\App();
    $app->middleware(\Tests\Middleware\Test::class);
    $request = \Scrawler\Http\Request::create(
        '/',
        'GET',
    );
    $response = $app->dispatch($request);
    expect($response->getContent())->toBe('Overtaken by middleware');
});

it('tests for middlewareInterface failure', function () {

    $app = new \Scrawler\App();
    $app->middleware(\Tests\Middleware\TestError::class);
    $request = \Scrawler\Http\Request::create(
        '/',
        'GET',
    );
    $response = $app->dispatch($request);
})->throws(\Scrawler\Exception\InvalidMiddlewareException::class);

it('tests for middlewareInterface class do not exist', function () {

    $app = new \Scrawler\App();
    $app->middleware(\Tests\Middleware\Unknown::class);
    $request = \Scrawler\Http\Request::create(
        '/',
        'GET',
    );
    $response = $app->dispatch($request);
})->throws(\Scrawler\Exception\InvalidMiddlewareException::class);

it('test for invalid middleware extra parameter',function(){
    $app = new \Scrawler\App();
    $app->middleware(function (\Scrawler\Http\Request $request,\Closure $next,\Scrawler\Http\Response $response) {
        $response = $next($request);
        $response->setStatusCode(200);
        $response->setContent('Overtaken by middleware');
        return $response;
    });
    $request = \Scrawler\Http\Request::create(
        '/',
        'GET',
    );
    $response = $app->dispatch($request);
})->throws(\Scrawler\Exception\InvalidMiddlewareException::class);

it('test for invalid middleware wrong first parameter',function(){
    $app = new \Scrawler\App();
    $app->middleware(function (\Scrawler\Http\Response $request,\Closure $next) {
        $response = $next($request);
        $response->setStatusCode(200);
        $response->setContent('Overtaken by middleware');
        return $response;
    });
    $request = \Scrawler\Http\Request::create(
        '/',
        'GET',
    );
    $response = $app->dispatch($request);
})->throws(\Scrawler\Exception\InvalidMiddlewareException::class);

it('test for invalid middleware wrong second parameter',function(){
    $app = new \Scrawler\App();
    $app->middleware(function (\Scrawler\Http\Request $request,callable $next) {
        $response = $next($request);
        $response->setStatusCode(200);
        $response->setContent('Overtaken by middleware');
        return $response;
    });
    $request = \Scrawler\Http\Request::create(
        '/',
        'GET',
    );
    $response = $app->dispatch($request);
})->throws(\Scrawler\Exception\InvalidMiddlewareException::class);