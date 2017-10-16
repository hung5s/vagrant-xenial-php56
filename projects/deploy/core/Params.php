<?php
/**
 * Created by PhpStorm.
 * User: tagremvn
 * Date: 07/07/2017
 * Time: 15:15
 */

namespace core;

/**
 * Class Params
 * @package core
 * 
 * System parameters management
 * Read the configuration and merge it with parameters passed into the `run` command.
 * Any `dev` parameter will override `prod` parameter. On production env, file `dev.php` should be deleted or renamed
 */
class Params implements \ArrayAccess {
    private $params = [];

    public function __construct() {
        $this->params = [];
        $this->loadFromFile('config/prod.php');
        if (file_exists(App::$basePath.'config/dev.php')){
            $this->params['modeDebug'] = true;
            $this->loadFromFile('config/dev.php');
        } else {
            $this->params['modeDebug'] = false;
        }
        $this->loadFromCli();
    }
    
    public function get($name, $default){
        return (isset($this->params[$name]) ? $this->params[$name] : $default);
    }

    protected function loadFromFile($file){
        if (!file_exists(App::$basePath.$file))
            return false;

        try {
            $config = include(App::$basePath.$file);
            if (!is_array($config))
                return false;

            $this->params = array_merge($this->params,$config);
        } catch (\Exception $ex){

        }
    }

    protected function loadFromCli(){
        $args = array_slice($_SERVER['argv'],2);

        foreach ($args as $arg) {
            $tmp = explode('=', $arg);
            if (count($tmp) == 2) {
                list($name, $value) = $tmp;
                $this->params[str_replace('-', '', trim($name))] = trim($value);
            }
        }
    }
    
    /** ArrayAccess implementation **/

    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->params[] = $value;
        } else {
            $this->params[$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->params[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->params[$offset]);
    }

    public function offsetGet($offset) {
        return isset($this->params[$offset]) ? $this->params[$offset] : null;
    }
}