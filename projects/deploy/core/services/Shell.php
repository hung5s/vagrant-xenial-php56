<?php
/**
 * Created by PhpStorm.
 * User: tagremvn
 * Date: 07/07/2017
 * Time: 14:16
 */

namespace core\services;


class Shell
{
    /**
     * Check if the system is Windows or Unix
     * @return bool
     */
    public static function isWin(){
        $isWin = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
        return $isWin;
    }

    /**
     * run a command and output command result immediately
     * @param string $command
     */
    public static function run($command){
        echo system($command),"\n";
    }

    /**
     * Run a shell command and out result after command is done
     *
     * @param string $command
     * @param bool $showCommand
     * @param bool $showOutput
     */
    public static function runCommand($command, $showCommand = true, $showOutput = true)
    {
        if (!self::isWin()) {
            $command .= ' 2>&1';
        }

        if ($showCommand)
            echo "\nExecuting: $command \n";
        $result = array();
        exec($command, $result);
        if ($showOutput){
            foreach ($result as $row)
                echo $row, "\n";
        }
        return $result;
    }

}