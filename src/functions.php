<?php

/*
 * This file is part of the Scrawler package.
 *
 * (c) Pranjal Pandey <its.pranjalpandey@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

if (!function_exists('app')) {
    /**
     * Get the app instance.
     */
    function app(): Scrawler\App
    {
        return Scrawler\App::engine();
    }
}

if (!function_exists('config')) {
    /**
     * Get the config instance.
     */
    function config(): PHLAK\Config\Config
    {
        return Scrawler\App::engine()->config();
    }
}

if (!function_exists('url')) {
    /**
     * Generate a url.
     */
    function url(string $path = ''): string
    {
        if (Scrawler\App::engine()->config()->has('https') && Scrawler\App::engine()->config()->get('https')) {
            if (null == Scrawler\App::engine()->request()->getHttpHost() || ':' == Scrawler\App::engine()->request()->getHttpHost()) {
                return 'https://localhost'.Scrawler\App::engine()->request()->getBasePath().$path;
            }

            return 'https://'.Scrawler\App::engine()->request()->getHttpHost().Scrawler\App::engine()->request()->getBasePath().$path;
        }

        if (null == Scrawler\App::engine()->request()->getHttpHost() || ':' == Scrawler\App::engine()->request()->getHttpHost()) {
            return 'http://localhost'.Scrawler\App::engine()->request()->getBasePath().$path;
        }

        return Scrawler\App::engine()->request()->getSchemeAndHttpHost().Scrawler\App::engine()->request()->getBasePath().$path;
    }
}

if (!function_exists('env')) {
    /**
     * Get the value of an environment variable.
     *
     * @return mixed|null
     */
    function env(string $key): mixed
    {
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }
        if (getenv($key)) {
            return getenv($key);
        }

        if (app()->request()->server->has($key)) {
            return app()->request()->server->get($key);
        }

        return null;
    }
}
