<?php
/**
 * Created by PhpStorm.
 * User: tagremvn
 * Date: 12/07/2017
 * Time: 08:40
 */

namespace core\services;


class Service extends \core\Component
{
    public function init($config){
        foreach($config as $k => $v){
            $setter = 'set'.$k;
            try {
                $this->$setter($v);
            }catch (\Exception $ex){
                
            }
        }
    }
}