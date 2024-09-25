<?php

namespace Scrawler;
use \Scrawler\Router\Router;
class App
{
    public static $app;

    private $router;

    private $container;

    private $handeler = [];


    public function __construct()
    {
         self::$app = $this;
         $this->router = new Router();
         $this->container =  new \DI\Container();
         $this->register('config',self::create(\PHLAK\Config\Config::class));
    }

    public static function engine(){
        return self::$app;
    }

    public function registerAutoRoute($dir,$namespace){
        $this->router->register($dir,$namespace);
    }

    public function get($route,$callback){
        $this->router->get($route,$callback);
    }

    public function post($route,$callback){
        $this->router->post($route,$callback);
    }

    public function put($route,$callback){
        $this->router->put($route,$callback);
    }

    public function delete($route,$callback){
        $this->router->delete($route,$callback);
    }

    public function all($route,$callback){
        $this->router->all($route,$callback);
    }

    public function registerHandler($name,$callback){
        $this->handeler[$name] = $callback;
    }

    public function dispatch($request = null){
        if(is_null($request)){
            if(function_exists('request')){
                $request = request();
            }else{
                $request = $this->request();
            }
        }
       
        $httpMethod = $request->getMethod();
        $uri = $request->getUri();

        [$status,$handler,$args,$debug] = $this->router->dispatch($httpMethod,$uri);
        switch ($status){
            case Router::NOT_FOUND:
              $response = $this->container->call($this->handeler['404']);
              $response = $this->makeResponse($response,404);
              break;
            case Router::METHOD_NOT_ALLOWED:
                $response = $this->container->call($this->handeler['405']);
                $response = $this->makeResponse($response,405);
              break;
            case Router::FOUND:
              //call the handler
              $response = $this->container->call($handler,$args);
              $response = $this->makeResponse($response,200);
              // Send Response
          }
          return $response;
    }

    public function run(){
        $response = $this->dispatch();
        $response->send();
    }

    private function makeResponse($content,$status = 200)
    {
        if (!$content instanceof \Symfony\Component\HttpFoundation\Response) {
            if (is_array($content)) {
                $content = \json_encode($content);
                $type = ['content-type' => 'application/json'];
            }

            $type = ['content-type' => 'text/html'];

            $response = new \Scrawler\Http\Response(
                $content,
                $status,
                $type
            );
        } else {
            $response = $content;
        }
        $this->register('response',$response);

        return $this->response();
    }

    public function __call($function,$args){
        if(function_exists($function)){
            return call_user_func_array($function,$args);
        }
        $this->container->get($function);
    }

    public function register($name,$value){
        $this->container->set($name,$value);
    }

    public static function create($class){
        return \DI\create($class);
    }

    public function make($class,$params){
        return $this->container->make($class,$params);
    }

    public function has($class){
        return $this->container->has($class);
    }

    



    
}