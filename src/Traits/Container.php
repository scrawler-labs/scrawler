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

trait Container
{
    /**
     * Register a new service in the container, set force to true to override existing service.
     */
    public function register(string $name, mixed $value, bool $force = false): void
    {
        if ($this->container->has($name) && !$force) {
            throw new \Scrawler\Exception\ContainerException('Service with this name already registered, please set $force = true to override');
        }
        if ($this->container->has($name) && ('config' === $name || 'pipeline' === $name)) {
            throw new \Scrawler\Exception\ContainerException('Service with this name cannot be overridden');
        }
        $this->container->set($name, $value);
    }

    /**
     * Create a new definition helper.
     */
    public function create(string $class): \DI\Definition\Helper\CreateDefinitionHelper
    {
        return \DI\create($class);
    }

    /**
     * Make a new instance of class rather than getting same instance
     * use it before registering the class to container
     * app()->register('MyClass',app()->make('App\Class'));.
     *
     * @param array<mixed> $params
     */
    public function make(string $class, array $params = []): mixed
    {
        return $this->container->make($class, $params);
    }

    /**
     * Check if a class is registered in the container.
     */
    public function has(string $class): bool
    {
        return $this->container->has($class);
    }

    /**
     * Call given function , missing params will be resolved from container.
     *
     * @param array<mixed> $params
     */
    public function call(string|callable $class, array $params = []): mixed
    {
        return $this->container->call($class, $params);
    }
}
