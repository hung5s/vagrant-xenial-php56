<?php
/**
 * Created by PhpStorm.
 * User: hung5s
 * Date: 09/07/2017
 * Time: 15:46
 */

namespace core\services;


class ExceptionHandler extends \core\Obj
{
    public function __construct()
    {
        //parent::__construct();
        set_exception_handler([$this,'exceptionHandler']);
    }
    
    public function exceptionHandler(\Exception $ex){
        echo "\nException: ",$ex->getMessage(),"\n",$ex->getFile().'('.$ex->getLine().')',"\n\n";
        if (\core\App::$params['modeDebug']){
            echo str_repeat('>', 20), ' Stack Trace ',str_repeat('<', 20),"\n\n";
            echo $ex->getTraceAsString(),"\n";
            echo "\n>>>>> Debug Mode is ON as config/dev.php exists <<<<<\n";
        }
    }
}