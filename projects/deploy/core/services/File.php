<?php
/**
 * Created by PhpStorm.
 * User: tagremvn
 * Date: 07/07/2017
 * Time: 14:15
 */

namespace core\services;


class File {
    public static function mkdir($path){
        mkdir($path,0755,true);
    }

    /**
     * create a link folder $target that link with the $source
     * @param string $source the real folder
     * @param string $target the link folder
     */
    public static function link($source, $target){
        $cmd = "ln -s {$source} {$target}";
        if (self::isWin()) {
            $target = str_replace("/","\\",$target);
            $source = str_replace("/","\\",$source);
            $cmd = "mklink /D {$target} {$source}";
        }
        self::runCommand($cmd);
    }
    
    /**
     * Check path is directory
     * @param $path : string: folder path
     * @return boolean
     */
    public static function isDirectory($path){
        return file_exists($path);
    }


    /**
     * Remove directories recursively
     * @param $path
     */
    function rmDirRecursive($path)
    {
        $runningOnWindows = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');

        if ($runningOnWindows) {
            $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
            exec("rd /S /Q " . $path);
        } else {
            exec("/bin/rm -rf " . $path);
        }
    }

    function cpDirRecursive($from, $to){
        $runningOnWindows = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
//    rmDirRecursive($to);
        if ($runningOnWindows){
            $from =str_replace("/",DIRECTORY_SEPARATOR,$from);
            $to =str_replace("/",DIRECTORY_SEPARATOR,$to);
            exec("echo d | xcopy /E /S /Y /H {$from} {$to}");
        } else {
            exec("cp -rf {$from} {$to}");
        }
    }
}