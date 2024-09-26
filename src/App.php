<?php

namespace Scrawler;
use \Scrawler\Router\Router;
class App
{
    public static $app;

    private $router;

    private $container;

    private $handler = [];


    public function __construct()
    {
         self::$app = $this;
         $this->router = new Router();
         $this->container =  new \DI\Container();
         $this->register('config',self::create(\PHLAK\Config\Config::class));
         $this->config()->set('debug',false);
         $this->registerHandler('404',function(){
            return '404 Not Found';
         });
         $this->registerHandler('405',function(){
            return '405 Method Not Allowed';
         });
        $this->registerHandler('500',function(){
                return '500 Internal Server Error';
        });

         
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
        $this->handler[$name] = $callback;
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
        $uri = $request->getRequestUri();

        [$status,$handler,$args,$debug] = $this->router->dispatch($httpMethod,$uri);
        switch ($status){
            case Router::NOT_FOUND:
                if($this->config()->get('debug')){
                    $response = $this->makeResponse($debug,404);
                    break;
                }
              $response = $this->container->call($this->handler['404']);
              $response = $this->makeResponse($response,404);
              break;
            case Router::METHOD_NOT_ALLOWED:
                if($this->config()->get('debug')){
                    $response = $this->makeResponse($debug,405);
                    break;
                }
                $response = $this->container->call($this->handler['405']);
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
        try{
        $response = $this->dispatch();
        }catch(\Exception $e){
            if($this->config()->get('debug')){
                throw $e;
            }else{
                $response = $this->container->call($this->handler['500']);
                $response = $this->makeResponse($response,500);
            }
        }
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
        if(!$this->container->has($function) && function_exists($function)){
            return call_user_func_array($function,$args);
        }
        return $this->container->get($function);
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