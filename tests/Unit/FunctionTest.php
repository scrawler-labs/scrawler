<?php

it('tests for app() function', function () {
    $app = app();
    expect($app)->toBeInstanceOf(\Scrawler\App::class);
});

it('tests for config() function', function () {
    expect(config())->toBeInstanceOf(PHLAK\Config\Config::class);
});

it('tests for url() function', function () {
    expect(url('/test'))->toBe('http://localhost/test');
});

it('tests for env() function', function () {
    $_ENV['test'] = 'test';
    expect(env('test'))->toBe('test');
    request()->server->set('test_s','test_s');
    expect(env('test_s'))->toBe('test_s');
    expect(env('random'))->toBe(null);
    putenv('test_put=test_put');
    expect(env('test_put'))->toBe('test_put');

});