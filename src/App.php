<?php
declare(strict_types=1);
namespace Scrawler;
use \Scrawler\Router\Router;

/**
 * @method \PHLAK\Config\Config config()
 * @method \Scrawler\Http\Request request()
 * @method \Scrawler\Http\Response response()
 */
class App
{

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
     * @var array<\Closure>
     */
    private array $handler = [];

    /**
     * @var int
     */
    private int $version;



    public function __construct()
    {
        self::$app = $this;
        $this->router = new Router();
        $this->container = new \DI\Container();
        $this->register('config', $this->create(\PHLAK\Config\Config::class));
        $this->config()->set('debug', false);
        $this->config()->set('api', false);

        $this->registerHandler('404', function () {
            if ($this->config()->get('api')) {
                return ['status' => 404, 'msg' => '404 Not Found'];
            }
            return '404 Not Found';
        });
        $this->registerHandler('405', function () {
            if ($this->config()->get('api')) {
                return ['status' => 405, 'msg' => '405 Method Not Allowed'];
            }
            return '405 Method Not Allowed';
        });
        $this->registerHandler('500', function () {
            if ($this->config()->get('api')) {
                return ['status' => 500, 'msg' => '500 Internal Server Error'];
            }
            return '500 Internal Server Error';
        });
        $this->version = 27092024;

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
     * Register controller directory and namespace for autorouting
     * @param string $dir 
     * @param string $namespace
     */
    public function registerAutoRoute(string $dir, string $namespace):void
    {
        $this->router->register($dir, $namespace);
    }

    /**
     * Register a new get route with the router
     * @param string $route
     * @param callable $callback
     */
    public function get(string $route, callable $callback):void
    {
        $callable = \Closure::fromCallable(callback: $callback);
        $this->router->get($route, $callable);
    }

    /**
     * Register a new post route with the router
     * @param string $route
     * @param callable $callback
     */
    public function post(string $route,callable $callback):void
    {
        $callable = \Closure::fromCallable(callback: $callback);
        $this->router->post($route, $callable);
    }

    /**
     * Register a new put route with the router
     * @param string $route
     * @param callable $callback
     */
    public function put(string $route,callable $callback): void
    {
        $callable = \Closure::fromCallable(callback: $callback);
        $this->router->put($route, $callable);
    }

    /**
     * Register a new delete route with the router
     * @param string $route
     * @param callable $callback
     */
    public function delete(string $route,callable $callback): void
    {
        $callable = \Closure::fromCallable(callback: $callback);
        $this->router->delete($route, $callable);
    }

    /**
     * Register a new all route with the router
     * @param string $route
     * @param callable $callback
     */
    public function all($route, $callback): void
    {
        $callable = \Closure::fromCallable(callback: $callback);
        $this->router->all($route, $callable);
    }

    /**
     * Register a new handler in scrawler
     * currently uselful hadlers are 404,405,500 and exception
     * @param string $name
     * @param callable $callback
     */
    public function registerHandler(string $name,callable $callback): void
    {
        $callable = \Closure::fromCallable(callback: $callback);
        if ($name == 'exception') {
            set_error_handler($callable);
            set_exception_handler($callable);
        }
        $this->handler[$name] = $callable;
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

        $httpMethod = $request->getMethod();
        $uri = $request->getPathInfo();
        $response = $this->makeResponse('', 200);

        try {
            [$status, $handler, $args, $debug] = $this->router->dispatch($httpMethod, $uri);
            switch ($status) {
                case Router::NOT_FOUND:
                    if ($this->config()->get('debug')) {
                        throw new \Scrawler\Exception\NotFoundException($debug);
                    }
                    $response = $this->container->call($this->handler['404']);
                    $response = $this->makeResponse($response, 404);
                    break;
                case Router::METHOD_NOT_ALLOWED:
                    if ($this->config()->get('debug')) {
                        throw new \Scrawler\Exception\MethodNotAllowedException($debug);
                    }
                    $response = $this->container->call($this->handler['405']);
                    $response = $this->makeResponse($response, 405);
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
        $this->register('response', $response);

        return $this->response();
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
     * Register a new service in the container
     * @param string $name
     * @param mixed $value
     */
    public function register($name, $value): void
    {
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
    public function make(string $class,array $params = []): mixed
    {
        return $this->container->make($class, $params);
    }

    /**
     * Check if a class is registered in the container
     * @param string $class
     * @return bool
     */
    public function has(string $class):bool
    {
        return $this->container->has($class);
    }

    /**
     * Get the build version of scrawler
     * @return int
     */
    public function getVersion(): int
    {
        return $this->version;
    }





}