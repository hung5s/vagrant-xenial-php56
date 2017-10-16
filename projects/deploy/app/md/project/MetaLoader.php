<?php
/**
 * Created by PhpStorm.
 * User: tagremvn
 * Date: 12/07/2017
 * Time: 15:44
 */

namespace app\md\project;


use core\services\Response;

class MetaLoader extends \core\Middleware
{
    /**
     * @param \app\services\Project $Project
     */
    public function handleDefault($Project = 'svc://app/services/Project'){
        $path = $this->response->getHeader('path','.');
        $file = $path.'/project.json';

        if (file_exists($file)){
            $Project->loadJsonFile($file);
            $this->response->setHeader('projectMeta', json_decode(file_get_contents($file), true));
        } else {
            $this->response->send('project.json file is not found at '.$file);
            $envs = $this->getAvailableEnvs();

            if (count($envs)){
                $this->response->send("Found environment file(s):");
                for($i=1;$i<=count($envs);$i++)
                    $this->response->send("[{$i}] {$envs[$i-1]}");
                $i = $this->response->readLine("Select file to use (0 = exist) [1]: ",1);
                if ($i == 0)
                    die(0);
                if ($i > count($envs))
                    die('Sorry, cannot load the selected environment.');
                $env = $path.'/web/env/'.$envs[$i-1];
                $Project->loadEnv($env);
            }
            $this->response->setHeader('projectMeta', $this->createDefaultMeta($Project, $file));
        }
    }

    protected function getAvailableEnvs(){
        $envDir = $this->response->getHeader('path','.').'/web/env/';
        if (!file_exists($envDir) || !is_dir($envDir)) {
            return [];
        }

        $envDir = dir($envDir);
        $envs = [];
        while(($file = $envDir->read()) != null){
            if (strpos($file,'config.') === 0 && strpos($file,'.php')!==false)
                $envs[] = $file;
        }
        return $envs;
    }
    
    /**
     * @param \app\services\Project $project
     * @param $file
     * @return array
     */
    protected function createDefaultMeta($project, $file){
        $meta = $project->getEnv();
        
        file_put_contents($file, json_encode($meta, JSON_PRETTY_PRINT));
        return $meta;
    }
}