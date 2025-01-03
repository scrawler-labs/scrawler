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

namespace Scrawler;

use Scrawler\Router\Router;

/**
 * @method \PHLAK\Config\Config    config()
 * @method \Scrawler\Http\Request  request()
 * @method \Scrawler\Http\Response response()
 * @method \Scrawler\Pipeline      pipeline()
 */
class App
{
    use Traits\Container;
    use Traits\Router;

    public static ?App $app = null;

    private Router $router;

    private \DI\Container $container;

    /**
     * @var array<\Closure|callable>
     */
    private array $handler = [];

    private string $version = '2.x';

    public function __construct()
    {
        self::$app = $this;
        $this->router = new Router();
        $this->container = new \DI\Container();
        $this->register('config', value: $this->create(\PHLAK\Config\Config::class));
        $this->register('pipeline', value: $this->create(Pipeline::class));
        $this->config()->set('debug', false);
        $this->config()->set('api', false);
        $this->config()->set('middlewares', []);

        $this->handler('404', function (): array|string {
            if ($this->config()->get('api')) {
                return ['status' => 404, 'msg' => '404 Not Found'];
            }

            return '404 Not Found';
        });
        $this->handler('405', function (): array|string {
            if ($this->config()->get('api')) {
                return ['status' => 405, 'msg' => '405 Method Not Allowed'];
            }

            return '405 Method Not Allowed';
        });
        $this->handler('500', function (): array|string {
            if ($this->config()->get('api')) {
                return ['status' => 500, 'msg' => '500 Internal Server Error'];
            }

            return '500 Internal Server Error';
        });
    }

    public static function engine(): self
    {
        if (null == self::$app) {
            self::$app = new self();
        }

        return self::$app;
    }

    /**
     * Register a new service in the container.
     */
    public function container(): \DI\Container
    {
        return $this->container;
    }

    /**
     * Register a new handler in scrawler
     * currently uselful hadlers are 404,405,500 and exception.
     */
    public function handler(string $name, \Closure|callable $callback): void
    {
        //@codeCoverageIgnoreStart
        $callback = \Closure::fromCallable(callback: $callback);
        if ('exception' === $name) {
            set_error_handler($callback);
            set_exception_handler($callback);
        }
        //@codeCoverageIgnoreEnd
        $this->handler[$name] = $callback;
    }

    /**
     * Get the handler by key.
     */
    public function getHandler(string $key): \Closure|callable
    {
        return $this->handler[$key];
    }

    /**
     * Dispatch the request to the router and create response.
     */
    public function dispatch(?Http\Request $request = null): Http\Response
    {
        if (is_null($request)) {
            $request = $this->request();
        }
        $pipeline = new Pipeline();

        return $pipeline->middleware($this->config()->get('middlewares'))->run($request, fn ($request): \Scrawler\Http\Response => $this->dispatchRouter($request));
    }

    /**
     * Dispatch the request to the router and create response.
     */
    private function dispatchRouter(Http\Request $request): Http\Response
    {
        $httpMethod = $request->getMethod();
        $uri = $request->getPathInfo();
        $response = $this->makeResponse('', 200);

        try {
            [$status, $handler, $args, $debug] = $this->router->dispatch($httpMethod, $uri);
            switch ($status) {
                case Router::NOT_FOUND:
                    $response = $this->handleNotFound($debug);
                    break;
                case Router::METHOD_NOT_ALLOWED:
                    $response = $this->handleMethodNotAllowed($debug);
                    break;
                case Router::FOUND:
                    // call the handler
                    $response = $this->container->call($handler, $args);
                    $response = $this->makeResponse($response, 200);
                    // Send Response
            }
        } catch (\Exception $e) {
            if ($this->config()->get('debug', false)) {
                throw $e;
            } else {
                $response = $this->container->call($this->handler['500']);
                $response = $this->makeResponse($response, 500);
            }
        }

        return $response;
    }

    /**
     * Handle 404 error.
     *
     * @throws Exception\NotFoundException
     */
    private function handleNotFound(string $debug): Http\Response
    {
        if ($this->config()->get('debug')) {
            throw new Exception\NotFoundException($debug);
        }
        $response = $this->container->call($this->handler['404']);

        return $this->makeResponse($response, 404);
    }

    /**
     * Handle 405 error.
     *
     * @throws Exception\MethodNotAllowedException
     */
    private function handleMethodNotAllowed(string $debug): Http\Response
    {
        if ($this->config()->get('debug')) {
            throw new Exception\MethodNotAllowedException($debug);
        }
        $response = $this->container->call($this->handler['405']);

        return $this->makeResponse($response, 405);
    }

    /**
     * Dipatch request and send response on screen.
     */
    public function run(): void
    {
        $response = $this->dispatch();
        $response->send();
    }

    /**
     * Builds response object from content.
     *
     * @param array<string,mixed>|string|\Scrawler\Http\Response $content
     */
    private function makeResponse(array|string|Http\Response $content, int $status = 200): Http\Response
    {
        if (!$content instanceof Http\Response) {
            $response = new Http\Response();
            $response->setStatusCode($status);

            if (is_array($content)) {
                $this->config()->set('api', true);
                $response->json($content);
            } elseif ($this->config()->get('api')) {
                $response->json($content);
            } else {
                $response->setContent($content);
            }
        } else {
            $response = $content;
        }

        return $response;
    }

    /**
     * Magic method to call container functions.
     *
     * @param array<mixed> $args
     */
    public function __call(string $function, mixed $args): mixed
    {
        try {
            if (!$this->container->has($function) && function_exists($function)) {
                return $this->container->call($function, $args);
            }

            return $this->container->get($function);
        } catch (\DI\NotFoundException $e) {
            throw new Exception\ContainerException($e->getMessage());
        }
    }

    /**
     * Add middleware(s).
     *
     * @param \Closure|callable|array<callable>|string $middlewares
     */
    public function middleware(\Closure|callable|array|string $middlewares): void
    {
        $this->config()->append('middlewares', $middlewares);
        $middlewares = $this->pipeline()->validateMiddleware(middlewares: $this->config()->get('middlewares'));
        $this->config()->set('middlewares', $middlewares);
    }

    /**
     * Get the build version of scrawler.
     */
    public function getVersion(): string
    {
        return $this->version;
    }
}
