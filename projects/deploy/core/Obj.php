<?php
/**
 * Created by PhpStorm.
 * User: tagremvn
 * Date: 07/07/2017
 * Time: 14:35
 */

namespace core;

/**
 * Class Obj
 * @package core
 * 
 * Base object class for any other class in framework
 */
class Obj
{
    public function __get($name){
        $getter = 'get'.$name;
        if (!$this->getRf()->hasMethod($getter))
            throw new \Exception(get_class($this)." does not has property {$name} or {$getter}() method.");
        
        return $this->$getter();
    }
    
    public function __set($name, $val){
        $setter='set'.$name;
        if (!$this->getRf()->hasMethod($setter))
            throw new \Exception(get_class($this)." does not has property {$name} or {$setter}() method.");
        try {
            $this->$setter($val);
        } catch (\Exception $ex){
            throw new \Exception(__CLASS__." does not has function {$setter}()");
        }
        $this->$name = $val;
    }

    protected function getRf(){
        return new \ReflectionClass($this);
    }
}