<?php
/**
 * Created by PhpStorm.
 * User: tagremvn
 * Date: 12/07/2017
 * Time: 08:26
 */

namespace core\services\responses;


use core\services\IoStream;

class CliResponse extends \core\services\Response
{
    /**
     * @var IoStream
     */
    protected $ioStream;
    
    public function __construct($app, $IoStream = 'svc://core/services/IoStream')
    {
        parent::__construct($app);
    }

    public function send($content)
    {
        echo $content,"\n";
    }

    /**
     * Read input from console
     * @param string $message prompting message
     * @param mixed $default if not NULL, user can press Enter to use default value
     * @return string
     */
    public function readLine($message, $default){
        $this->ioStream->write($message);
        $input = $this->ioStream->readline($default!==null);
        
        if ($input == '' && $default)
            $input = $default;

        return $input;
    }
}