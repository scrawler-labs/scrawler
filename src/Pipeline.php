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

namespace Scrawler;

use Scrawler\Interfaces\MiddlewareInterface;

final class Pipeline
{
    /**
     * Create a new pipeline.
     *
     * @param array<\Closure> $middlewares
     */
    public function __construct(
        private array $middlewares = [],
    ) {
    }

    /**
     * Add middleware(s) or Pipeline.
     *
     * @template T of MiddlewareInterface
     *
     * @param array<callable|\Closure|class-string<T>> $middlewares
     *
     * @return array<\Closure>
     */
    public function validateMiddleware(array $middlewares): array
    {
        $validated = [];
        foreach ($middlewares as $middleware) {
            if (is_string($middleware)) {
                if (class_exists($middleware)) {
                    $middlewareObj = new $middleware();
                    if ($middlewareObj instanceof MiddlewareInterface) {
                        $callable = $middlewareObj->run(...);
                        $middleware = \Closure::fromCallable(callback: $callable);
                    } else {
                        throw new Exception\InvalidMiddlewareException('Middleware class does not implement MiddlewareInterface');
                    }
                } else {
                    throw new Exception\InvalidMiddlewareException('Middleware class does not exist');
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
            if ('request' === $parameter->getName() && Http\Request::class !== (string) $parameter->getType()) {
                throw new Exception\InvalidMiddlewareException('First parameter of middleware must be of type Scrawler\Http\Request');
            }
            if ('next' === $parameter->getName() && 'Closure' !== (string) $parameter->getType()) {
                throw new Exception\InvalidMiddlewareException('Second parameter of middleware must be of type Closure');
            }
            if ('request' !== $parameter->getName() && 'next' !== $parameter->getName()) {
                throw new Exception\InvalidMiddlewareException('Invalid parameter name in middleware');
            }
        }
    }

    /**
     * Add middleware(s) or Pipeline.
     *
     * @param array<\Closure> $middlewares
     */
    public function middleware(array $middlewares): self
    {
        $this->middlewares = $middlewares;

        return $this;
    }

    /**
     * Run middleware around core function and pass an
     * object through it.
     */
    public function run(mixed $object, \Closure $core): mixed
    {
        $coreFunction = $this->createCoreFunction($core);

        $middlewares = array_reverse($this->middlewares);

        $completePipeline = array_reduce($middlewares, fn ($nextMiddleware, $middleware): \Closure => $this->createMiddleware($nextMiddleware, $middleware), $coreFunction);

        return $completePipeline($object);
    }

    /**
     * The inner function of the onion.
     * This function will be wrapped on layers.
     *
     * @param \Closure $core the core function
     *
     * @return \Closure
     */
    private function createCoreFunction(\Closure $core)
    {
        return fn ($object) => $core($object);
    }

    /**
     * Get an pipeline middleware function.
     * This function will get the object from a previous layer and pass it inwards.
     */
    private function createMiddleware(\Closure $nextMiddleware, \Closure $middleware): \Closure
    {
        return fn ($object) => $middleware($object, $nextMiddleware);
    }
}
