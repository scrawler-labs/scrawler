<?php

it('tests if registerAutoRoute() works', function () {
    $app = new \Scrawler\App();
    $app->registerAutoRoute(__DIR__ . '/../Controllers', 'Tests\\Controllers');
    $request = \Scrawler\Http\Request::create(
        '/test',
        'GET',
    );
    $response = $app->dispatch($request);
    expect($response->getContent())->toBe('Hello World');
});

it('tests if get() works', function () {
    $app = new \Scrawler\App();
    $app->get('/test', function () {
        return 'Hello World';
    });
    $request = \Scrawler\Http\Request::create(
        '/test',
        'GET',
    );
    $response = $app->dispatch($request);
    expect($response->getContent())->toBe('Hello World');
});

it('tests if post() works', function () {
    $app = new \Scrawler\App();
    $app->post('/test/post', function () {
        return 'Hello World';
    });
    $request = \Scrawler\Http\Request::create(
        '/test/post',
        'POST',
    );
    $response = $app->dispatch($request);
    expect($response->getContent())->toBe('Hello World');
});

it('tests if put() works', function () {
    $app = new \Scrawler\App();
    $app->put('/test/put', function () {
        return 'Hello World';
    });
    $request = \Scrawler\Http\Request::create(
        '/test/put',
        'PUT',
    );
    $response = $app->dispatch($request);
    expect($response->getContent())->toBe('Hello World');
});

it('tests if delete() works', function () {
    $app = new \Scrawler\App();
    $app->delete('/test/delete', function () {
        return 'Hello World';
    });
    $request = \Scrawler\Http\Request::create(
        '/test/delete',
        'DELETE',
    );
    $response = $app->dispatch($request);
    expect($response->getContent())->toBe('Hello World');
});

it('tests if all() works', function () {
    $app = new \Scrawler\App();
    $app->all('/test/all', function () {
        return 'Hello World';
    });
    $request = \Scrawler\Http\Request::create(
        '/test/all',
        'GET',
    );
    $response = $app->dispatch($request);
    expect($response->getContent())->toBe('Hello World');

    $request = \Scrawler\Http\Request::create(
        '/test/all',
        'POST',
    );
    $response = $app->dispatch($request);
    expect($response->getContent())->toBe('Hello World');
});

it('tests if register handler() works', function () {
    $app = new \Scrawler\App();
    $app->handler('404', function () {
        return 'Its a custom 404';
    });
    $request = \Scrawler\Http\Request::create(
        '/test/something',
        'GET',
    );
    $response = $app->dispatch($request);
    expect($response->getContent())->toBe('Its a custom 404');
});

it('tests if register() works', function () {
    $app = new \Scrawler\App();
    $test = new \Tests\Controllers\Feature\Test();
    $app->register('test', $test);
    $test = app()->test()->test();
    expect($test)->toBe('test function works');
});

it('tests if register() throws error on override', function () {
    $app = new \Scrawler\App();
    $test = new \Tests\Controllers\Feature\Test();
    $app->register('test', $test);
    $app->register('test', $test);
})->throws(\Scrawler\Exception\ContainerException::class);

it('tests if register() lets force override', function () {
    $app = new \Scrawler\App();
    $test = new \Tests\Controllers\Feature\Test();
    $app->register('test', $test);
    $app->register('test', $test,true);
    $test = app()->test()->test();
    expect($test)->toBe('test function works');
});

it('tests if register() stops core override', function () {
    $app = new \Scrawler\App();
    $test = new \Tests\Controllers\Feature\Test();
    $app->register('config', $test,true);
})->throws(\Scrawler\Exception\ContainerException::class);

it('tests default 404 in api mode', function () {
    $app = new \Scrawler\App();
    $app->config()->set('api', true);
    $request = \Scrawler\Http\Request::create(
        '/test/something',
        'GET',
    );
    $response = $app->dispatch($request);
    expect($response->getContent())->toBe('{"status":404,"msg":"404 Not Found"}');
});

it('tests default 404 in web mode', function () {
    $app = new \Scrawler\App();
    $request = \Scrawler\Http\Request::create(
        '/test/something',
        'GET',
    );
    $response = $app->dispatch($request);
    expect($response->getContent())->toBe('404 Not Found');
});

it('tests default 405 in api mode', function () {
    $app = new \Scrawler\App();
    $app->config()->set('api', true);
    $app->registerAutoRoute(__DIR__ . '/../Controllers', 'Tests\\Controllers');

    $request = \Scrawler\Http\Request::create(
        '/test/test',
        'POST',
    );
    $response = $app->dispatch($request);
    expect($response->getContent())->toBe('{"status":405,"msg":"405 Method Not Allowed"}');
});

it('tests default 405 in web mode', function () {
    $app = new \Scrawler\App();
    $app->registerAutoRoute(__DIR__ . '/../Controllers', 'Tests\\Controllers');
    $request = \Scrawler\Http\Request::create(
        '/test/test',
        'POST',
    );
    $response = $app->dispatch($request);
    expect($response->getContent())->toBe('405 Method Not Allowed');
});

it('tests default 500 in api mode', function () {
    $app = new \Scrawler\App();
    $app->config()->set('api', true);
    $app->registerAutoRoute(__DIR__ . '/../Controllers', 'Tests\\Controllers');
    $request = \Scrawler\Http\Request::create(
        '/test/exception',
        'GET',
    );
    $response = $app->dispatch($request);
    expect($response->getContent())->toBe('{"status":500,"msg":"500 Internal Server Error"}');
});

it('tests default 500 in web mode', function () {
    $app = new \Scrawler\App();
    $app->registerAutoRoute(__DIR__ . '/../Controllers', 'Tests\\Controllers');
    $request = \Scrawler\Http\Request::create(
        '/test/exception',
        'GET',
    );
    $response = $app->dispatch($request);
    expect($response->getContent())->toBe('500 Internal Server Error');
});

it('tests for ContainerException', function () {
    $app = new \Scrawler\App();
    $app->someClass();

})->throws(\Scrawler\Exception\ContainerException::class);

it('tests for NotFoundException', function () {
    $app = new \Scrawler\App();
    $app->config()->set('debug', true);
    $request = \Scrawler\Http\Request::create(
        '/notfound',
        'GET',
    );
    $app->dispatch($request);
})->throws(\Scrawler\Exception\NotFoundException::class);

it('tests for MethodNotAllowedException', function () {
    $app = new \Scrawler\App();
    $app->registerAutoRoute(__DIR__ . '/../Controllers', 'Tests\\Controllers');
    $app->config()->set('debug', true);
    $request = \Scrawler\Http\Request::create(
        '/test/test',
        'POST',
    );
    $response = $app->dispatch($request);
})->throws(\Scrawler\Exception\MethodNotAllowedException::class);


it('tests for json response in api mode ', function () {
    $app = new \Scrawler\App();
    $app->config()->set('api', true);
    $app->get('/test', function () {
        return ['data' => 'Hello World'];
    });
    $request = \Scrawler\Http\Request::create(
        '/test',
        'GET',
    );
    $app->register('request', $request);
    $response = $app->dispatch();
    expect($response->getContent())->toBe('{"data":"Hello World"}');
    $app->get('/test/json', function () {
        return json_encode(['data' => 'Hello World']);
    });
    $request = \Scrawler\Http\Request::create(
        '/test/json',
        'GET',
    );
    $response = $app->dispatch($request);
    expect($response->getContent())->toBe('{"data":"Hello World"}');
});

it('tests when response is already a response object', function () {
    $app = new \Scrawler\App();
    $app->get('/test', function () {
        $response = new \Scrawler\Http\Response();
        $response->setContent('Hello World');
        return $response;
    });
    $request = \Scrawler\Http\Request::create(
        '/test',
        'GET',
    );
    $response = $app->dispatch($request);
    expect($response->getContent())->toBe('Hello World');
});

it('tests function being called on __call()', function () {
    $app = new \Scrawler\App();
    $request = $app->request();
    expect($request)->toBeInstanceOf(\Scrawler\Http\Request::class);
});

it('tests for make() function', function () {
    $app = new \Scrawler\App();
    $app->register('test', 'Tests\Controllers\Feature\Test');
    $test = $app->make('Tests\Controllers\Feature\Test');
    $test = $test->test();
    expect($test)->toBe('test function works');
});

it('tests getVersion function', function () {
    $app = new \Scrawler\App();
    $version = $app->getVersion();
    $this->assertStringContainsString('2024', $version);
});

it('tests for run() function ', function () {
    $app = new \Scrawler\App();
    $app->get('/test', function () {
        $response = new \Scrawler\Http\Response();
        $response->setContent('Hello World');
        return $response;
    });
    $request = \Scrawler\Http\Request::create(
        '/test',
        'GET',
    );
    $app->register('request', $request);
    ob_start();
    $app->run();
    $output = ob_get_clean();
    expect($output)->toBe('Hello World');
});