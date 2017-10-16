<?php
/**
 * Created by PhpStorm.
 * User: tagremvn
 * Date: 11/07/2017
 * Time: 11:56
 */

namespace core\services;


abstract class Response extends Service
{
    protected $header = [];
    protected $body;
    protected $handled = false;
    
    public function setHeader($key, $value){
        $this->header[$key] = $value;
    }
    
    public function getHeader($key, $default = null){
        return (isset($this->header[$key])? $this->header[$key] : (\core\App::$params->get($key, $default)));
    }
    
    public function getBody(){
        return $this->body;
    }
    
    public function setBody($body){
        if ($this->handled)
            throw new \Exception("Response is finalized.");
        $this->body = $body;
    }

    public abstract function send($content);

    public function end(){
        $this->handled = true;
    }
    
    public function getHandled(){
        return $this->handled;
    }
    
    
}