<?php
declare(strict_types=1);
/**
 * The main App class for scrawler
 *
 * @package: Scrawler
 * @author: Pranjal Pandey
 */
namespace Scrawler;
use \Scrawler\Router\Router;

/**
 * @method \PHLAK\Config\Config config()
 * @method \Scrawler\Http\Request request()
 * @method \Scrawler\Http\Response response()
 * @method \Scrawler\Pipeline pipeline()
 */
class App
{
    use Traits\Container;
    use Traits\Router;

    /**
     * @var App
     */
    public static App $app;

    /**
     * @var Router
     */
    private Router $router;

    /**
     * @var \DI\Container
     */
    private \DI\Container $container;

    /**
     * @var array<\Closure|callable>
     */
    private array $handler = [];

    /**
     * @var string
     */
    private string $version;



    public function __construct()
    {
        self::$app = $this;
        $this->router = new Router();
        $this->container = new \DI\Container();
        $this->register('config', value: $this->create(\PHLAK\Config\Config::class));
        $this->register('pipeline', value: $this->create(\Scrawler\Pipeline::class));
        $this->config()->set('debug', false);
        $this->config()->set('api', false);
        $this->config()->set('middlewares', []);

        $this->handler('404', function () {
            if ($this->config()->get('api')) {
                return ['status' => 404, 'msg' => '404 Not Found'];
            }
            return '404 Not Found';
        });
        $this->handler('405', function () {
            if ($this->config()->get('api')) {
                return ['status' => 405, 'msg' => '405 Method Not Allowed'];
            }
            return '405 Method Not Allowed';
        });
        $this->handler('500', function () {
            if ($this->config()->get('api')) {
                return ['status' => 500, 'msg' => '500 Internal Server Error'];
            }
            return '500 Internal Server Error';
        });
        $this->version = "03082024";

    }
    /**
     * @return \Scrawler\App
     */
    public static function engine(): self
    {
        if (self::$app == null) {
            self::$app = new self();
        }
        return self::$app;
    }


    /**
     * Register a new handler in scrawler
     * currently uselful hadlers are 404,405,500 and exception
     * @param string $name
     * @param \Closure|callable $callback
     */
    public function handler(string $name, \Closure|callable $callback): void
    {
        if(is_callable($callback)){
            $callback = \Closure::fromCallable(callback: $callback);

        }
        if ($name == 'exception') {
            set_error_handler($callback);
            set_exception_handler($callback);
        }
        $this->handler[$name] = $callback;
    }

    /**
     * Get the handler by key
     * @param string $key
     * @return \Closure|callable
     */
    public function getHandler($key): \Closure|callable
    {
        return $this->handler[$key];

    }

    /**
     * Dispatch the request to the router and create response
     * @param \Scrawler\Http\Request|null $request
     * @return \Scrawler\Http\Response
     */
    public function dispatch(\Scrawler\Http\Request $request = null): \Scrawler\Http\Response
    {
        if (is_null($request)) {
            $request = $this->request();
        }
        $pipeline = new Pipeline();
        $response = $pipeline->middleware($this->config()->get('middlewares'))->run($request, function ($request) {
            return $this->dispatchRouter($request);
        });
        return $response;
    }

    /**
     * Dispatch the request to the router and create response
     * @param \Scrawler\Http\Request $request
     * @return \Scrawler\Http\Response
     */
    private function dispatchRouter(\Scrawler\Http\Request $request): \Scrawler\Http\Response{
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
                    //call the handler
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
     * Handle 404 error
     * @return \Scrawler\Http\Response
     * @param string $debug
     * @throws \Scrawler\Exception\NotFoundException
     */
    private function handleNotFound(string $debug): \Scrawler\Http\Response
    {
        if ($this->config()->get('debug')) {
            throw new \Scrawler\Exception\NotFoundException($debug);
        }
        $response = $this->container->call($this->handler['404']);
        return $this->makeResponse($response, 404);
    }

    /**
     * Handle 405 error
     * @return \Scrawler\Http\Response
     * @param string $debug
     * @throws \Scrawler\Exception\MethodNotAllowedException
     */
    private function handleMethodNotAllowed(string $debug): \Scrawler\Http\Response
    {
        if ($this->config()->get('debug')) {
            throw new \Scrawler\Exception\MethodNotAllowedException($debug);
        }
        $response = $this->container->call($this->handler['405']);
        return $this->makeResponse($response, 405);
    }

    /**
     * Dipatch request and send response on screen
     */
    public function run(): void
    {
        $response = $this->dispatch();
        $response->send();
    }

    /**
     * Builds response object from content
     * @param array<string,mixed>|string|\Scrawler\Http\Response $content
     * @param int $status
     * @return \Scrawler\Http\Response
     */
    private function makeResponse(array|string|\Scrawler\Http\Response $content, int $status = 200): \Scrawler\Http\Response
    {
        if (!$content instanceof \Scrawler\Http\Response) {
            $response = new \Scrawler\Http\Response();
            $response->setStatusCode($status);

            if (is_array($content)) {
                $this->config()->set('api', true);
                $response->json(\json_encode($content));

            } else {
                if ($this->config()->get('api')) {
                    $response->json($content);
                } else {
                    $response->setContent($content);
                }
            }

        } else {
            $response = $content;
        }

        return $response;
    }

    /**
     * Magic method to call container functions
     * @param string $function
     * @param array<mixed> $args
     * @return mixed
     */
    public function __call($function, $args): mixed
    {
        try {
            if (!$this->container->has($function) && function_exists($function)) {
                return $this->container->call($function, $args);
            }

            return $this->container->get($function);
        } catch (\DI\NotFoundException $e) {
            throw new \Scrawler\Exception\ContainerException($e->getMessage());
        }
    }

    /**
     * Add middleware(s)
     * @param \Closure|callable|array<callable>|string $middlewares
     */
    public function middleware(\Closure|callable|array|string $middlewares): void{
        $this->config()->append('middlewares',$middlewares);
        $middlewares = $this->pipeline()->validateMiddleware(middlewares: $this->config()->get('middlewares'));
        $this->config()->set('middlewares',$middlewares);
    }


    /**
     * Get the build version of scrawler
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }





}