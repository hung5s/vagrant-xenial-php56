<?php
/**
 * Created by PhpStorm.
 * User: hung5s
 * Date: 09/07/2017
 * Time: 14:57
 */

namespace core\services;


class Logger extends \core\Obj
{
    const   INFO='Info',
            WARN='Warning',
            ERROR='Error',
            DEBUG='Debug';
    
    protected $writers = [];
    
    public function __construct()
    {
        //parent::__construct();
        $this->addWriter(new LogWriters\LogFileWriter());
    }

    public function write($message, $type, $category = '', array $params = []){
        if (!empty($params))
            $message = $this->parseMessage($message, $params);
        
        foreach($this->writers as $writer)
            $writer->write($message, $type, $category);
    }
    
    public function addWriter($writer){
        $this->writers[] = $writer;
    }
    
    public function trace($message, $category, array $params = []){
        $this->write($message, Logger::DEBUG, $category, $params);
    }
    
    protected function  parseMessage($message, array $params){
        foreach($params as $k => $v)
            $message = str_replace('{'.$k.'}',$v,$message);
        
        return $message;
    }
}