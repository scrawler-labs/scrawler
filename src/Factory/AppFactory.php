<?php

declare(strict_types=1);

namespace Scrawler\Factory;

use Scrawler\App;

final class AppFactory
{
    private static ?\DI\Container $container = null;

    public static function create(): App
    {
        if (is_null(self::$container)) {
            self::$container = new \DI\Container();
            self::$container->set('config', new \PHLAK\Config\Config());
            self::$container->set('pipeline', new \Scrawler\Pipeline());
            self::$container->set('router', new \Scrawler\Router\Router());
        }

        $app = new App(self::$container);
        self::$container->set('app', fn (): App => $app);

        return $app;
    }

    public static function getApp(): App
    {
        if (is_null(self::$container) || !self::$container->has('app')) {
            self::create();
        }

        return self::$container instanceof \DI\Container ? self::$container->get('app') : null;
    }
}
