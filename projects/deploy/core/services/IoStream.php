<?php
namespace core\services;
/**
 * @author TaiTran
 * Class IOStream
 * class helper for input/output stream
 */
class IoStream
{
    /**
     * @constant: Max times allow user confirm for a message
     */
    const MAX_CONFIRM_TIMES = 3;

    /**
     * @var $handle : The file pointer must be valid, and must point to a file successfully opened
     */
    private $handle;

    public function __construct()
    {
        $this->handle = fopen("php://stdin", "r");
    }

    /**
     * Use to confirm information from user
     * @param $message
     * @return boolean
     */
    public function confirm($message)
    {
        $count = 0;
        do {
            $this->write($message . ': ');
            $input = $this->readline();
            ++$count;
        } while (!in_array(strtolower($input), ['yes', 'no', 'y', 'n']) || $count > self::MAX_CONFIRM_TIMES);
        return in_array(strtolower($input), ['yes', 'y']);
    }

    /**
     * Print message and get response from user
     * @param boolean $allowEmpty . If set if false, the screen will wait for user input data not empty
     * @return string
     */
    public function readline($allowEmpty = false, $removeBreakLine = true)
    {
        $newLine = Shell::isWin() ? "\r\n" : "\n";
        do {
            if ($removeBreakLine) {
                $data = str_replace($newLine, '', fgets($this->handle));
            } else
                $data = fgets($this->handle);
        } while (!$allowEmpty && empty(str_replace($newLine, '', $data)));
        return trim($data);
    }

    /**
     * Print message to console screen
     * @param $content
     */
    public function write($content)
    {
        print $content;
    }

    /**
     * Write message with new line
     * @param $content content to print to console
     */
    public function writeln($content = '')
    {
        print $content . "\r\n"; //(Console::isWin()? "\r\n" : "\n");
    }

    public function getData(&$info, $field, $message, $default = null, $isBoolean = false)
    {
        if ($info[$field] != null) {
            $text = 'current is';
            $default = $info[$field];
        } else {
            $text = 'default is';
        }

        if ($default == null) {
            $this->write($message . ': ');
            $data = $this->readline();
            if ($isBoolean) {
                $data = in_array(strtolower($data), ['y', 'yes']) ? true : false;
            }
            $info[$field] = $data;
        } else {
            $this->write($message . ": ({$text} {$default}): ");
            $data = $this->readline(true);
            if ($isBoolean) {
                $data = in_array(strtolower($data), ['y', 'yes']) ? true : false;
            }
            if (empty($data))
                $info[$field] = $default;
        }
    }
}