<?php

it('tests for middleware ', function (): void {
    $app = new Scrawler\App();
    $app->middleware(function (Scrawler\Http\Request $request, Closure $next) {
        $response = $next($request);
        $response->setStatusCode(200);
        $response->setContent('Overtaken by middleware');

        return $response;
    });
    $request = Scrawler\Http\Request::create(
        '/',
        'GET',
    );
    $response = $app->dispatch($request);
    expect($response->getContent())->toBe('Overtaken by middleware');
});

it('tests for middlewareInterface ', function (): void {
    $app = new Scrawler\App();
    $app->middleware(Tests\Middleware\Test::class);
    $request = Scrawler\Http\Request::create(
        '/',
        'GET',
    );
    $response = $app->dispatch($request);
    expect($response->getContent())->toBe('Overtaken by middleware');
});

it('tests for middlewareInterface failure', function (): void {
    $app = new Scrawler\App();
    $app->middleware(Tests\Middleware\TestError::class);
    $request = Scrawler\Http\Request::create(
        '/',
        'GET',
    );
    $response = $app->dispatch($request);
})->throws(Scrawler\Exception\InvalidMiddlewareException::class);

it('tests for middlewareInterface class do not exist', function (): void {
    $app = new Scrawler\App();
    $app->middleware(Tests\Middleware\Unknown::class);
    $request = Scrawler\Http\Request::create(
        '/',
        'GET',
    );
    $response = $app->dispatch($request);
})->throws(Scrawler\Exception\InvalidMiddlewareException::class);

it('test for invalid middleware extra parameter', function (): void {
    $app = new Scrawler\App();
    $app->middleware(function (Scrawler\Http\Request $request, Closure $next, Scrawler\Http\Response $response) {
        $response = $next($request);
        $response->setStatusCode(200);
        $response->setContent('Overtaken by middleware');

        return $response;
    });
    $request = Scrawler\Http\Request::create(
        '/',
        'GET',
    );
    $response = $app->dispatch($request);
})->throws(Scrawler\Exception\InvalidMiddlewareException::class);

it('test for invalid middleware wrong first parameter', function (): void {
    $app = new Scrawler\App();
    $app->middleware(function (Scrawler\Http\Response $request, Closure $next) {
        $response = $next($request);
        $response->setStatusCode(200);
        $response->setContent('Overtaken by middleware');

        return $response;
    });
    $request = Scrawler\Http\Request::create(
        '/',
        'GET',
    );
    $response = $app->dispatch($request);
})->throws(Scrawler\Exception\InvalidMiddlewareException::class);

it('test for invalid middleware wrong second parameter', function (): void {
    $app = new Scrawler\App();
    $app->middleware(function (Scrawler\Http\Request $request, callable $next) {
        $response = $next($request);
        $response->setStatusCode(200);
        $response->setContent('Overtaken by middleware');

        return $response;
    });
    $request = Scrawler\Http\Request::create(
        '/',
        'GET',
    );
    $response = $app->dispatch($request);
})->throws(Scrawler\Exception\InvalidMiddlewareException::class);
