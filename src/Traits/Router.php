<?php

declare(strict_types=1);

/*
 * This file is part of the Scrawler package.
 *
 * (c) Pranjal Pandey <its.pranjalpandey@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scrawler\Traits;

trait Router
{
    /**
     * Register controller directory and namespace for autorouting.
     */
    public function registerAutoRoute(string $dir, string $namespace): void
    {
        $this->router()->register($dir, $namespace);
    }

    /**
     * Register a new get route with the router.
     */
    public function get(string $route, \Closure|callable $callback): void
    {
        $callback = \Closure::fromCallable(callback: $callback);
        $this->router()->get($route, $callback);
    }

    /**
     * Register a new post route with the router.
     */
    public function post(string $route, \Closure|callable $callback): void
    {
        $callback = \Closure::fromCallable(callback: $callback);
        $this->router()->post($route, $callback);
    }

    /**
     * Register a new put route with the router.
     */
    public function put(string $route, \Closure|callable $callback): void
    {
        $callback = \Closure::fromCallable(callback: $callback);
        $this->router()->put($route, $callback);
    }

    /**
     * Register a new delete route with the router.
     */
    public function delete(string $route, \Closure|callable $callback): void
    {
        $callback = \Closure::fromCallable(callback: $callback);
        $this->router()->delete($route, $callback);
    }

    /**
     * Register a new all route with the router.
     */
    public function all(string $route, \Closure|callable $callback): void
    {
        $callback = \Closure::fromCallable(callback: $callback);
        $this->router()->all($route, $callback);
    }
}
