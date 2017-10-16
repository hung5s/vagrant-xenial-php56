<?php
/**
 * Created by PhpStorm.
 * User: tagremvn
 * Date: 11/07/2017
 * Time: 16:17
 */

namespace core;


class ServiceProvider extends Component
{
    protected $services = [];
    
    public function get($name, $defaultClass = null){
        if (isset($this->services[$name]))
            return $this->services[$name];
        
        
        if (!isset(App::$params['services']) || !isset(App::$params['services'][$name])){
            App::$logger->trace("No service configuration is found for {$name}", 'Service Provider');
            if (!$defaultClass){
                return null;
            } else {
                App::$logger->trace("Creating service from {$defaultClass}", 'Service Provider');
                $service = new $defaultClass($this->app);
            }
        } else {
            $config = App::$params['services'][$name];
            $class = array_shift($config);

            App::$logger->trace("Creating service for {$name} from {$class}", 'Service Provider');
            App::$logger->trace("Service configuration:{config}", 'Service Provider',['config' => print_r($config,1)]);

            $service = new $class($this->app);
            $service->init($config);
        }
        
        
        $this->services[$name] = $service;
        
        return $service;
    }
}