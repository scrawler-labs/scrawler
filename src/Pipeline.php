<?php
/**
 * Pipeline for middleware
 *
 * @package: Scrawler
 * @author: Pranjal Pandey
 */
namespace Scrawler;

use PhpParser\Error;
use Scrawler\Interfaces\MiddlewareInterface;

final class Pipeline
{

    /**
     * The array of middleware
     * @var array<\Closure>
     */
    private array $middlewares;

    /**
     * Create a new pipeline
     * @param array<\Closure> $middlewares
     */
    public function __construct(array $middlewares = [])
    {
        $this->middlewares = $middlewares;
    }

    /**
     * Add middleware(s) or Pipeline
     * @template T of MiddlewareInterface
     * @param array<callable|\Closure|class-string<T>> $middlewares
     * @return array<\Closure>
     */
    public function validateMiddleware(array $middlewares): array
    {
        $validated = [];
        foreach ($middlewares as $middleware) {
            if (is_string($middleware)) {
                if (class_exists($middleware)) {
                    $middlewareObj = new $middleware;
                    if ($middlewareObj instanceof MiddlewareInterface) {
                        $callable = [$middlewareObj, 'run'];
                        $middleware = \Closure::fromCallable(callback: $callable);
                    } else {
                        throw new \Scrawler\Exception\InvalidMiddlewareException('Middleware class does not implement MiddlewareInterface');
                    }
                } else {
                    throw new \Scrawler\Exception\InvalidMiddlewareException('Middleware class does not exist');
                }
            }
            $middleware = \Closure::fromCallable(callback: $middleware);
            $this->validateClosure($middleware);
            $validated[] = $middleware;
        }



        return $validated;
    }


    private function validateClosure(\Closure $middleware): void
    {
        $refFunction = new \ReflectionFunction($middleware);
        $parameters = $refFunction->getParameters();
        foreach ($parameters as $parameter) {
            if ($parameter->getName() == 'request' && $parameter->getType() != 'Scrawler\Http\Request') {
                throw new \Scrawler\Exception\InvalidMiddlewareException('First parameter of middleware must be of type Scrawler\Http\Request');
            }
            if ($parameter->getName() == 'next' && $parameter->getType() != 'Closure') {
                throw new \Scrawler\Exception\InvalidMiddlewareException('Second parameter of middleware must be of type Closure');
            }
            if ($parameter->getName() != 'request' && $parameter->getName() != 'next') {
                throw new \Scrawler\Exception\InvalidMiddlewareException('Invalid parameter name in middleware');
            }
        }
    }

    /**
     * Add middleware(s) or Pipeline
     * @param array<\Closure> $middlewares
     * @return \Scrawler\Pipeline
     */
    public function middleware(array $middlewares): self
    {
        $this->middlewares = $middlewares;
        return $this;
    }

    /**
     * Run middleware around core function and pass an
     * object through it
     * @param  mixed  $object
     * @param  \Closure $core
     * @return mixed         
     */
    public function run($object, $core)
    {
        $coreFunction = $this->createCoreFunction($core);


        $middlewares = array_reverse($this->middlewares);


        $completePipeline = array_reduce($middlewares, function ($nextMiddleware, $middleware) {
            return $this->createMiddleware($nextMiddleware, $middleware);
        }, $coreFunction);


        return $completePipeline($object);
    }


    /**
     * The inner function of the onion.
     * This function will be wrapped on layers
     * @param  \Closure $core the core function
     * @return \Closure
     */
    private function createCoreFunction(\Closure $core)
    {
        return function ($object) use ($core) {
            return $core($object);
        };
    }

    /**
     * Get an pipeline middleware function.
     * This function will get the object from a previous layer and pass it inwards
     * @param  \Closure $nextMiddleware
     * @param  \Closure $middleware
     * @return \Closure
     */
    private function createMiddleware(\Closure $nextMiddleware, \Closure $middleware): \Closure
    {
        return function ($object) use ($nextMiddleware, $middleware) {
            return $middleware($object, $nextMiddleware);
        };
    }


}