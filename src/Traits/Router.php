<?php
namespace Scrawler\Traits;

trait Router
{
     /**
     * Register controller directory and namespace for autorouting
     * @param string $dir 
     * @param string $namespace
     */
    public function registerAutoRoute(string $dir, string $namespace): void
    {
        $this->router->register($dir, $namespace);
    }

     /**
     * Register a new get route with the router
     * @param string $route
     * @param \Closure|callable $callback
     */
    public function get(string $route, \Closure|callable $callback): void
    {
        if(is_callable($callback)){
            $callback = \Closure::fromCallable(callback: $callback);
        }
        $this->router->get($route, $callback);
    }

    /**
     * Register a new post route with the router
     * @param string $route
     * @param \Closure|callable $callback
     */
    public function post(string $route, \Closure|callable $callback): void
    {
        if(is_callable($callback)){
            $callback = \Closure::fromCallable(callback: $callback);
        }
        $this->router->post($route, $callback);
    }

    /**
     * Register a new put route with the router
     * @param string $route
     * @param \Closure|callable $callback
     */
    public function put(string $route, \Closure|callable $callback): void
    {
        if(is_callable($callback)){
            $callback = \Closure::fromCallable(callback: $callback);
        }
        $this->router->put($route, $callback);
    }

    /**
     * Register a new delete route with the router
     * @param string $route
     * @param \Closure|callable $callback
     */
    public function delete(string $route, \Closure|callable $callback): void
    {
        if(is_callable($callback)){
            $callback = \Closure::fromCallable(callback: $callback);
        }
        $this->router->delete($route, $callback);
    }

    /**
     * Register a new all route with the router
     * @param string $route
     * @param \Closure|callable $callback
     */
    public function all(string $route,\Closure|callable $callback): void
    {
        if(is_callable($callback)){
            $callback = \Closure::fromCallable(callback: $callback);
        }
        $this->router->all($route, $callback);
    }

}