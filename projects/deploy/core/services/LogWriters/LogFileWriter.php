<?php
/**
 * Created by PhpStorm.
 * User: hung5s
 * Date: 09/07/2017
 * Time: 15:21
 */

namespace core\services\LogWriters;
use \core\App as App;
use \core\services\File as File;

class LogFileWriter extends \core\Obj
{
    protected $file = 'logs.txt';
    
    public function __construct($file = '')
    {
        $base = App::$basePath.'/logs/';
        if (!file_exists($base)){
            File::mkdir($base);
        }
        
        if ($file)
            $this->file = $file;
        $this->file = $base.$this->file;
        
        /** In development env cleanup the log*/
        if (file_exists(App::$basePath.'config/dev.php') && file_exists($this->file))
            unlink($this->file);
    }
    
    public function write($message, $type, $category){
        $category = str_pad($category,16,' ',STR_PAD_LEFT);
        $message = date('Y/m/d h:i:s')."\t{$type}\t{$category}\t{$message}\n";
        
        file_put_contents($this->file,$message,FILE_APPEND | FILE_TEXT);
    }
}