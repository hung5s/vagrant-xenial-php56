<?php
/**
 * Created by PhpStorm.
 * User: tagremvn
 * Date: 12/07/2017
 * Time: 14:56
 */

namespace app\services;


use core\services\Logger;

class Project extends \core\services\Service
{
    protected $env = [
        'projectName' => 'My project',
        'projectFolder' => '.',
        'domain' => 'www.domain.com',
        'siteDir' => '',
        'siteId' => 'my_indition',
        'siteOwner' => 'my',
        'dbHost' => 'localhost',
        'dbPort' => '5432',
        'dbName' => 'db',
        'dbUser' => 'user',
        'dbPassword' => 'password',
    ];

    /**
     * Project constructor.
     * @param string $config file path to the project env file
     */
    public function __construct($app)
    {
        parent::__construct($app);
        $this->loadEnv();
    }

    public function getEnv(){
        return $this->env;
    }
    
    public function loadEnv($file = null){
        if ($file && !file_exists($file))
            throw new \Exception("File not found {$file}");

        if (!$file)
            $file = $this->env['projectFolder'].'/web/env/config.'.$this->env['domain'].'.php';

        if (file_exists($file)){
            include $file;

            $this->env['projectFolder'] = dirname(ROOT_DIR);
            $this->env['rootDir'] = ROOT_DIR;
            $this->env['codeDir'] = CODE_DIR;
            $this->env['siteDir'] = SITE_DIR;
            $this->env['siteId'] = SITE_ID;
            $this->env['siteOwner'] = SITE_OWNER;
            $this->env['domain'] = $this->getDomainFromEnvFilename($file);
        } else {
            $this->env['rootDir'] = $this->env['projectFolder'].'/core/';
            $this->env['codeDir'] = $this->env['projectFolder'].'/site/';
            $this->env['siteDir'] = $this->env['siteOwner'].'/'.$this->env['domain'];

            /** get SITE_X constant from source.json */
            $sourceFile = \core\App::$params['path'].'/source.json';
            if (file_exists($sourceFile)){
                $sourceInfo = json_decode(file_get_contents($sourceFile),true);
                $this->env['siteDir'] = $sourceInfo['env']['siteDir'];
                $this->env['siteId'] = $sourceInfo['env']['siteId'];
                $this->env['siteOwner'] = $sourceInfo['env']['siteOwner'];
            }

        }

        /** make sure there is no // in the ...dir or ...path*/
        $this->env['rootDir'] = str_replace('//','/',$this->env['rootDir']);
        $this->env['codeDir'] = str_replace('//','/',$this->env['codeDir']);
        $this->env['siteDir'] = str_replace('//','/',$this->env['siteDir']);
        $this->env['projectFolder'] = str_replace('//','/',$this->env['projectFolder']);
    }
    
    public function loadJsonFile($file){
        if (!file_exists($file))
            throw new \Exception("File not found {$file}");

        \core\App::$logger->write('Load project config file from '.$file,Logger::INFO);
        $meta = json_decode(file_get_contents($file), true);
        if (is_array($meta)){
            $this->env = array_merge($this->env, $meta);
            $this->loadEnv();
        } else {
            throw new \Exception("Invalid format for project metadata file {$file}");
        }
    }

    public function getEnvPath(){
        return $this->env['projectFolder'].'/web/env/config.'.$this->env['domain'].'.php';
    }

    public function getModuleConfigPath(){
        return $this->env['codeDir'].'config.modules.php';
    }

    protected function getDomainFromEnvFilename($file){
        $name = basename($file,'.php');
        return str_replace('config.', '', $name);
    }

    /*************************************************/
//    protected function loadEnv($checkFolders){
//        if (!file_exists($this->env)) {
//            $lookups = ['/web/env/','/env/','/../web/env/'];
//            if (strpos($this->env,'config')!==0) // assume that this is a domain
//                $this->env = "config.{$this->env}.php";
//            echo "Searching for env file:\n";
//            $cwd = getcwd();
//            foreach($lookups as $dir){
//                echo $cwd.$dir.$this->env,"\n";
//                if (file_exists($cwd.$dir.$this->env)){
//                    $this->env = $cwd.$dir.$this->env;
//                    break;
//                }
//            }
//        }
//
//        if (!file_exists($this->env)) {
//            throw new Exception($this->env.' environment file does not exist.');
//        }
//        include($this->env);
//
//        if ($checkFolders){
//            /** make sure that ROOT and CODE dirs are not part of each other */
//            if (strpos(ROOT_DIR,CODE_DIR) !== false)
//                throw new Exception('Custom code dir is part of the Root (core) code dir which is not allowed.');
//            if (strpos(CODE_DIR,ROOT_DIR) !== false)
//                throw new Exception('Custom code dir is part of the Root (core) code dir which is not allowed.');
//            /** make sure ROOT and CODE dirs existed*/
//            if (!file_exists(ROOT_DIR))
//                throw new Exception(ROOT_DIR.' does not exist.');
//            if (!file_exists(CODE_DIR))
//                throw new Exception(CODE_DIR.' does not exist.');
//
//            /** load source config file*/
//            if (!file_exists(CODE_DIR.'config.modules.php'))
//                throw new Exception(CODE_DIR.'config.modules.php does not exist.');
//            $this->config = include(CODE_DIR.'config.modules.php');
//        }
//
//    }
    

//
//    public function linkThemes(){
//        $webRoot = dirname($this->env).'/../';
//        $siteRoot = $webRoot.'/sites/'.SITE_DIR;
//
//        $adminTheme = $webRoot.'admin/themes/indition';
//        if (!file_exists($adminTheme)){
//            $src = ROOT_DIR.'themes/indition4';
//            Console::link($src,$adminTheme);
//        }
//
//        $siteTheme = $siteRoot.'/themes';
//        if (!file_exists($siteTheme)){
//            Console::link(CODE_DIR.'themes',$siteTheme);
//        }
//    }
}