<?php
/**
 * Created by PhpStorm.
 * User: hung5s
 * Date: 10/07/2017
 * Time: 20:33
 */

namespace core;
use core\services\Response as Response;

interface IMiddleware
{
    public function handle(Response $response);
}

abstract class Middleware extends Component implements IMiddleware
{
    protected $mapper = [];

    protected $context = 'default';

    /**
     * @var Response $response
     */
    protected $response;

    public function handle(Response $response)
    {
        $handler = 'handle'.ucfirst($this->context);
        
        $rf = new \ReflectionClass($this);
        
        if ($rf->hasMethod($handler)){
            $params = $this->injectResources($handler);
            $this->response = $response;

            App::$logger->trace("Running {$handler}()", 'Middleware');

            call_user_func_array([$this,$handler], $params);
        } else {
            throw new \Exception(get_class($this)." has no function {$handler}.");
        }
    }
    
    public function getMapper(){
        return $this->mapper;
    }
    
    public function setMapper($value){
        if (!is_array($value))
            throw new \Exception("Context mapper has to be an associative array.");
        
        $this->mapper = $value;
    }

    public function getContext(){
        return $this->context;
    }

    public function setContext($context){
        $this->context = $context;
    }
    
    public function getHeader($name){
        $name = (isset($this->mapper[$name])? $this->mapper[$name]: $name);
        return $this->response->getHeader($name);
    }
}