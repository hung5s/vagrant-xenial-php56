<?php
/**
 * Created by PhpStorm.
 * User: tagremvn
 * Date: 18/07/2017
 * Time: 10:45
 */

namespace core;

/**
 * Class Component represents an application's component
 * A component can only be created by the application object (App) and can refer to its app by $this->app
 * Component can use its __construct() to ask for services and resources from its application.
 *
 * @package core
 */
class Component extends Obj
{
    /**
     * @var \core\App $app
     */
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
        self::injectResources('__construct');
    }

    protected function createResourceForParam(\ReflectionParameter $param, $func){
        if (!$param->isDefaultValueAvailable())
            return null;

        $name = $param->getName();
        $default = $param->getDefaultValue();
        $protocol = substr($default, 0, 6);

        if ($protocol == 'res://'){
            $path = str_replace('/','\\',substr($default,6));
            $resource = $this->app->getResponse()->getHeader($path);
        } elseif ($protocol == 'svc://') {
            $class = str_replace('/', '\\', substr($default, 6));
            App::$logger->trace(get_class($this).".{$func}.\${$name} <= {$class}", 'Object Loader');

            $resource = $this->app->getService($name, $class);
        } elseif(isset(App::$params[$name])) {
            $resource = App::$params[$name];
        } else {
            $resource = $default;
        }

        if (!$resource && in_array($protocol, ['res://','svc://']))
            throw new \Exception("Unable to load required resource {$name} from {$default}");

        return $resource;
    }


    /**
     * Inject services into an object or a function. If function is __construct(), services are injected into object's
     * properties who must have the same name with service class (first character must be lowercase). Otherwise, the
     * function return an associative array $params which can be use with call_user_func_array() to call the $func
     *
     * @param $obj
     * @param $func
     * @return array
     * @throws \Exception
     */
    public function injectResources($func){
        $rf = new \ReflectionClass($this);
        if (!$rf->hasMethod($func))
            throw new \Exception("Class ".get_class($this)." does not has method {$func}");

        $rfMethod = new \ReflectionMethod($this,$func);
        $rfParams = $rfMethod->getParameters();

        $params = [];
        foreach($rfParams as $rfParam){
            $name = $rfParam->getName();
            $resource = $this->createResourceForParam($rfParam,$func);
            if ($resource !== null){
                if ($func == '__construct'){
                    $name = lcfirst($name);
                    $this->$name = $resource;
                } else {
                    $params[$name] = $resource;
                }
            } elseif (isset(App::$params[$name])) {
                $params[$name] = App::$params[$name];
            } else {
                //throw new \Exception(get_class($obj).".{$func} requires parameter {$name}.");
            }
        }

        return $params;
    }
}