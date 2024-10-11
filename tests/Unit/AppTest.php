<?php

it('tests if registerAutoRoute() works', function (): void {
    $app = new Scrawler\App();
    $app->registerAutoRoute(__DIR__.'/../Controllers', 'Tests\\Controllers');
    $request = Scrawler\Http\Request::create(
        '/test',
        'GET',
    );
    $response = $app->dispatch($request);
    expect($response->getContent())->toBe('Hello World');
});

it('test container() function', function (): void {
    $app = new Scrawler\App();
    $container = $app->container();
    expect($container)->toBeInstanceOf(\DI\Container::class);
});

it('tests if get() works', function (): void {
    $app = new Scrawler\App();
    $app->get('/test', fn(): string => 'Hello World');
    $request = Scrawler\Http\Request::create(
        '/test',
        'GET',
    );
    $response = $app->dispatch($request);
    expect($response->getContent())->toBe('Hello World');
});

it('tests if post() works', function (): void {
    $app = new Scrawler\App();
    $app->post('/test/post', fn(): string => 'Hello World');
    $request = Scrawler\Http\Request::create(
        '/test/post',
        'POST',
    );
    $response = $app->dispatch($request);
    expect($response->getContent())->toBe('Hello World');
});

it('tests if put() works', function (): void {
    $app = new Scrawler\App();
    $app->put('/test/put', fn(): string => 'Hello World');
    $request = Scrawler\Http\Request::create(
        '/test/put',
        'PUT',
    );
    $response = $app->dispatch($request);
    expect($response->getContent())->toBe('Hello World');
});

it('tests if delete() works', function (): void {
    $app = new Scrawler\App();
    $app->delete('/test/delete', fn(): string => 'Hello World');
    $request = Scrawler\Http\Request::create(
        '/test/delete',
        'DELETE',
    );
    $response = $app->dispatch($request);
    expect($response->getContent())->toBe('Hello World');
});

it('tests if all() works', function (): void {
    $app = new Scrawler\App();
    $app->all('/test/all', fn(): string => 'Hello World');
    $request = Scrawler\Http\Request::create(
        '/test/all',
        'GET',
    );
    $response = $app->dispatch($request);
    expect($response->getContent())->toBe('Hello World');

    $request = Scrawler\Http\Request::create(
        '/test/all',
        'POST',
    );
    $response = $app->dispatch($request);
    expect($response->getContent())->toBe('Hello World');
});

it('tests if register handler() works', function (): void {
    $app = new Scrawler\App();
    $app->handler('404', fn(): string => 'Its a custom 404');
    $request = Scrawler\Http\Request::create(
        '/test/something',
        'GET',
    );
    $response = $app->dispatch($request);
    expect($response->getContent())->toBe('Its a custom 404');
});

it('tests if  getHandler() works', function (): void {
    $app = new Scrawler\App();
    $app->handler('404', fn(): string => 'Its a custom 404');
    $handler = $app->getHandler('404');

    expect($handler)->toBeCallable();
});

it('tests if register() works', function (): void {
    $app = new Scrawler\App();
    $test = new Tests\Service\Test();
    $app->register('test', $test);
    $test = app()->test()->test();
    expect($test)->toBe('test function works');
});

it('tests if register() throws error on override', function (): void {
    $app = new Scrawler\App();
    $test = new Tests\Service\Test();
    $app->register('test', $test);
    $app->register('test', $test);
})->throws(Scrawler\Exception\ContainerException::class);

it('tests if register() lets force override', function (): void {
    $app = new Scrawler\App();
    $test = new Tests\Service\Test();
    $app->register('test', $test);
    $app->register('test', $test, true);
    $test = app()->test()->test();
    expect($test)->toBe('test function works');
});

it('tests if register() stops core override', function (): void {
    $app = new Scrawler\App();
    $test = new Tests\Service\Test();
    $app->register('config', $test, true);
})->throws(Scrawler\Exception\ContainerException::class);

it('tests default 404 in api mode', function (): void {
    $app = new Scrawler\App();
    $app->config()->set('api', true);
    $request = Scrawler\Http\Request::create(
        '/test/something',
        'GET',
    );
    $response = $app->dispatch($request);
    expect($response->getContent())->toBe('{"status":404,"msg":"404 Not Found"}');
});

it('tests default 404 in web mode', function (): void {
    $app = new Scrawler\App();
    $request = Scrawler\Http\Request::create(
        '/test/something',
        'GET',
    );
    $response = $app->dispatch($request);
    expect($response->getContent())->toBe('404 Not Found');
});

it('tests default 405 in api mode', function (): void {
    $app = new Scrawler\App();
    $app->config()->set('api', true);
    $app->registerAutoRoute(__DIR__.'/../Controllers', 'Tests\\Controllers');

    $request = Scrawler\Http\Request::create(
        '/test/test',
        'POST',
    );
    $response = $app->dispatch($request);
    expect($response->getContent())->toBe('{"status":405,"msg":"405 Method Not Allowed"}');
});

it('tests default 405 in web mode', function (): void {
    $app = new Scrawler\App();
    $app->registerAutoRoute(__DIR__.'/../Controllers', 'Tests\\Controllers');
    $request = Scrawler\Http\Request::create(
        '/test/test',
        'POST',
    );
    $response = $app->dispatch($request);
    expect($response->getContent())->toBe('405 Method Not Allowed');
});

it('tests default 500 in api mode', function (): void {
    $app = new Scrawler\App();
    $app->config()->set('api', true);
    $app->registerAutoRoute(__DIR__.'/../Controllers', 'Tests\\Controllers');
    $request = Scrawler\Http\Request::create(
        '/test/exception',
        'GET',
    );
    $response = $app->dispatch($request);
    expect($response->getContent())->toBe('{"status":500,"msg":"500 Internal Server Error"}');
});

it('tests default 500 in web mode', function (): void {
    $app = new Scrawler\App();
    $app->registerAutoRoute(__DIR__.'/../Controllers', 'Tests\\Controllers');
    $request = Scrawler\Http\Request::create(
        '/test/exception',
        'GET',
    );
    $response = $app->dispatch($request);
    expect($response->getContent())->toBe('500 Internal Server Error');
});

it('tests for ContainerException', function (): void {
    $app = new Scrawler\App();
    $app->someClass();
})->throws(Scrawler\Exception\ContainerException::class);

it('tests for NotFoundException', function (): void {
    $app = new Scrawler\App();
    $app->config()->set('debug', true);
    $request = Scrawler\Http\Request::create(
        '/notfound',
        'GET',
    );
    $app->dispatch($request);
})->throws(Scrawler\Exception\NotFoundException::class);

it('tests for MethodNotAllowedException', function (): void {
    $app = new Scrawler\App();
    $app->registerAutoRoute(__DIR__.'/../Controllers', 'Tests\\Controllers');
    $app->config()->set('debug', true);
    $request = Scrawler\Http\Request::create(
        '/test/test',
        'POST',
    );
    $response = $app->dispatch($request);
})->throws(Scrawler\Exception\MethodNotAllowedException::class);

it('tests for json response in api mode ', function (): void {
    $app = new Scrawler\App();
    $app->config()->set('api', true);
    $app->get('/test', fn(): array => ['data' => 'Hello World']);
    $request = Scrawler\Http\Request::create(
        '/test',
        'GET',
    );
    $app->register('request', $request);
    $response = $app->dispatch();
    expect($response->getContent())->toBe('{"data":"Hello World"}');
    $app->get('/test/json', fn() => json_encode(['data' => 'Hello World']));
    $request = Scrawler\Http\Request::create(
        '/test/json',
        'GET',
    );
    $response = $app->dispatch($request);
    expect($response->getContent())->toBe('{"data":"Hello World"}');
});

it('tests when response is already a response object', function (): void {
    $app = new Scrawler\App();
    $app->get('/test', function (): \Scrawler\Http\Response {
        $response = new Scrawler\Http\Response();
        $response->setContent('Hello World');

        return $response;
    });
    $request = Scrawler\Http\Request::create(
        '/test',
        'GET',
    );
    $response = $app->dispatch($request);
    expect($response->getContent())->toBe('Hello World');
});

it('tests function being called on __call()', function (): void {
    $app = new Scrawler\App();
    $request = $app->request();
    expect($request)->toBeInstanceOf(Scrawler\Http\Request::class);
});

it('tests for make() function', function (): void {
    $app = new Scrawler\App();
    $app->register('test', \Tests\Service\Test::class);
    $test = $app->make(\Tests\Service\Test::class);
    $test = $test->test();
    expect($test)->toBe('test function works');
});

it('tests if call() works', function (): void {
    $app = new Scrawler\App();
    $result = $app->call(fn(): string => 'test function works');
    expect($result)->toBe('test function works');
});

it('tests getVersion function', function (): void {
    $app = new Scrawler\App();
    $version = $app->getVersion();
    $this->assertStringContainsString('2024', $version);
});

it('tests for run() function ', function (): void {
    $app = new Scrawler\App();
    $app->get('/test', function (): \Scrawler\Http\Response {
        $response = new Scrawler\Http\Response();
        $response->setContent('Hello World');

        return $response;
    });
    $request = Scrawler\Http\Request::create(
        '/test',
        'GET',
    );
    $app->register('request', $request);
    ob_start();
    $app->run();
    $output = ob_get_clean();
    expect($output)->toBe('Hello World');
});

