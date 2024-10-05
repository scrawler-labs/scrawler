<?php
namespace Scrawler\Traits;
trait Container
{
    /**
     * Register a new service in the container, set force to true to override existing service
     * @param string $name
     * @param mixed $value
     * @param bool $force
     */
    public function register($name, $value,bool $force = false): void
    {
        if($this->container->has($name) && !$force){
            throw new \Scrawler\Exception\ContainerException('Service with this name already registered, please set $force = true to override');
        }
        if($this->container->has($name) && ($name == 'config' || $name == 'pipeline')){
            throw new \Scrawler\Exception\ContainerException('Service with this name cannot be overridden');
        }
        $this->container->set($name, $value);
    }


    /**
     * Create a new definition helper
     * @param string $class
     * @return \DI\Definition\Helper\CreateDefinitionHelper
     */
    public function create(string $class): \DI\Definition\Helper\CreateDefinitionHelper
    {
        return \DI\create($class);
    }


    /**
     * Make a new instance of class rather than getting same instance
     * use it before registering the class to container
     * app()->register('MyClass',app()->make('App\Class'));
     * 
     * @param string $class
     * @param array<mixed> $params
     * @return mixed
     */
    public function make(string $class, array $params = []): mixed
    {
        return $this->container->make($class, $params);
    }

     /**
     * Check if a class is registered in the container
     * @param string $class
     * @return bool
     */
    public function has(string $class): bool
    {
        return $this->container->has($class);
    }

    /**
     * Call given function , missing params will be resolved from container
     * @param string|callable $class
     * @param array<mixed> $params
     * @return mixed
     */
    public function call(string|callable $class, array $params = []): mixed
    {
        return $this->container->call($class, $params);
    }


}