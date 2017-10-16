<?php
/**
 * Created by PhpStorm.
 * User: tagremvn
 * Date: 14/07/2017
 * Time: 10:33
 */

namespace app\md\project;


use core\services\Response;

class Env extends \core\Middleware
{
    /**
     * @param \app\services\Project $Project
     */
    public function handleCreate($Project = 'svc://app/services/Project'){
        $content = file_get_contents(\core\App::$basePath.'/env_template.tpl');
        $env = $Project->getEnv();
        foreach($env as $k => $v){
            $content = str_replace('{'.$k.'}', $v, $content);
        }
        
        file_put_contents($Project->envPath, $content);
    }
}