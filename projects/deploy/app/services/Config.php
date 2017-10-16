<?php
/**
 * Created by PhpStorm.
 * User: tagremvn
 * Date: 18/07/2017
 * Time: 16:15
 */

namespace app\services;
use \core\App;
use core\services\Logger;

class Config extends \core\services\Service
{

    protected $isValid = false;

    public $errors = [];

    /**
     * @var \app\services\Project $project
     */
    protected $project;
    
    protected $vcs;
    protected $source;
    
    /**
     * Config constructor.
     * @param $app
     * @param \app\services\Project $Project
     */
    public function __construct($app, $Project = 'svc://app/services/Project')
    {
        parent::__construct($app);
        $this->loadConfigFile($this->project->getModuleConfigPath());
    }

    public function getIsValid(){
        return $this->isValid;
    }
    
    public function findModule($moduleId){
        if ($moduleId == 'root'){
            return [
                'key' => 'root',
                'path' => $this->project->env['rootDir']
            ];
        } elseif ($moduleId == 'site'){
            return [
                'key' => 'site',
                'path' => $this->project->env['codeDir']
            ];
        }
        foreach($this->source as $key => $config){
            if (strpos($key.'/','/'.$moduleId.'/')!== false){
                return [
                    'key' => $key,
                    'path' => $this->modulePath($key),
                ];
            }
        }

        return null;
    }

    protected function loadConfigFile($file){
        if (!file_exists($file)){
            $this->errors[] = $error = $file.' is not found.';
            App::$logger->write($error, Logger::ERROR);
            return false;
        }

        $config = include($file);
        if (!is_array($config) || !isset($config['source'])){
            $this->errors[] = $error = $file.' does not return an array of module config or no ["source"] element found.';
            App::$logger->write($error, Logger::ERROR);
            return false;
        }

        $validModules = [];
        foreach($config['source'] as $module => $info){
            if ($module != 'root' && strpos($module, 'root/') === false && strpos($module, 'site/') === false && strpos($module, 'themes/') === false){
                $this->errors[] = $error = $module.' is not valid. Expect root/ or site/ or themes/';
                App::$logger->write($error, Logger::ERROR);
            }

            if (!is_array($info) || !isset($info['path'])){
                $this->errors[] = $error = $module.' does not has ["path"] info.';
                App::$logger->write($error, Logger::ERROR);
            }

            $tmp = explode('/',rtrim($info['path'],'/'));
            $type = array_pop($tmp);
            if (!in_array($type,['trunk','branches','tags'])){
                $this->errors[] = $error = $module.' path is not a valid trunk or branch or tag.';
                App::$logger->write($error, Logger::ERROR);
            }

            if (in_array($type, ['branches','tags']) && trim($info['version']) == ''){
                $this->errors[] = $error = $module.' path is not using trunk but no version info found.';
                App::$logger->write($error, Logger::ERROR);
            }

            $tmp = explode('/',$module);
            if (count($tmp) >= 3 && strpos($module,'site/themes') === false){
                array_pop($tmp);
                if (!in_array(implode('/', $tmp), $validModules)){
                    $this->errors[] = $error = $module.' has no parent module or its parent is defined later in the config file.';
                    App::$logger->write($error, Logger::ERROR);
                }
            }

            $validModules[] = $module;
        }
        $this->isValid = empty($this->errors);
        
        
        $this->source = $config['source'];
        $this->vcs = $config['vcs'];
        
        return $this->isValid;
    }

    public function createDefault(){
        $sourceFile = $this->project->env['projectFolder'].'/source.json';
        if (file_exists($sourceFile)){
            $sourceInfo = json_decode(file_get_contents($sourceFile),true);
            $config = [
                'vcs' => $sourceInfo['vcs'],
                'source' => $sourceInfo['source'],
            ];
        }
        
        if (!isset($config))
            $config = App::$params['defaultModules'];
        $file = $this->project->getModuleConfigPath();

        $content = '<?php return '.var_export($config, true).';';
        file_put_contents($file, $content);
        $this->loadConfigFile($file);
    }

    public function moduleFromKey($configKey){
        $module = explode('/',$configKey);
        $module = end($module);
        return $module;
    }

    protected function modulePath($moduleKey){
        if (strpos($moduleKey,'root') === 0)
            $path = $this->project->env['rootDir'];
        elseif (strpos($moduleKey,'site/themes') === 0)
            $path = $this->project->env['projectFolder'].'/web/sites/'.$this->project->env['siteDir'];
        else
            $path = $this->project->env['codeDir'];

        $moduleTrail = explode('/',$moduleKey);
        array_shift($moduleTrail);
        foreach($moduleTrail as $moduleName)
            if ($moduleName == 'config' || strpos($moduleKey,'themes')!==false)
                $path .= '/'.$moduleName;
            else
                $path .= '/modules/'.$moduleName;

        return $path;
    }
    
    public function getSource(){
        return $this->source;
    }
    
    public function getVcs(){
        return $this->vcs;
    }
}