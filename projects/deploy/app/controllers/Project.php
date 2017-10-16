<?php
/**
 * Created by PhpStorm.
 * User: tagremvn
 * Date: 12/07/2017
 * Time: 15:09
 */

namespace app\controllers;

use core\App;
use core\services\IoStream;

class Project extends \core\Controller
{
    public function middlewares($action)
    {
        switch ($action){
            case 'install':
                return [
                    '\app\md\project\MetaLoader',
                    '_',
                    '\app\md\project\Container:create',
                    '\app\md\project\Env:create',
                    '\app\md\project\Svn:checkout',
                    '\app\md\project\Container:theme',
                    '\app\md\project\Container:uploads',
                ];
                break;
            case 'index':
                return [
                    '\app\md\project\MetaLoader',
                    '_',
                ];
                break;
            case 'deploy':
                return [
                    '\app\md\project\MetaLoader',
                    '_',
                ];
                break;
            case 'nginx':
                return [
                    '\app\md\project\MetaLoader',
                    '_',
                ];
                break;
        }
    }

    /**
     * Create project structure and checkout code for a new project
     * 
     * @param \app\services\Project $Project
     * @param string $path path to project root folder
     */
    public function actionInstall($Project = 'svc://app/services/Project', $path){
        /** Let user modify project meta values before installing */
        $meta = $this->response->getHeader('projectMeta');
        if (!$meta)
            throw new \Exception("No project metadata!");

        $asks = [
            'projectName' => 'Project Name',
            'domain' => 'Domain (for production site)',
            'siteOwner' => 'Site Owner or Company Name (alphanumeric characters only, no space)',
            'siteId' => 'Table Prefix',
            'dbHost' => 'DB Host',
            'dbPort' => 'DB Port',
            'dbName' => 'DB Name',
            'dbUser' => 'DB User',
            'dbPassword' => 'DB Password',
        ];
        foreach($asks as $k => $ask){
            $meta[$k] = $this->response->readLine($ask." [{$meta[$k]}]: ", $meta[$k]);
        }

        $meta['projectFolder'] = realpath($path).'/';
        
        /** Update meta file */
        $file = $meta['projectFolder'].'/project.json';
        file_put_contents($file, json_encode($meta, JSON_PRETTY_PRINT));
        
        $Project->loadJsonFile($file);
        
        $this->response->setHeader('projectMeta',$meta);
    }

    /**
     * Sync code for modules 
     * 
     * @param \app\services\SourceControl $SourceControl
     * @param \app\services\Config $Config
     * @param string $path path to project root folder
     * @param string $modules modules to sync code, separated by comma (,). If not specified all modules will be synced
     */
    public function actionIndex($path, $modules='',
                                $SourceControl = 'svc://app/services/SourceControl',
                                $Config = 'svc://app/services/Config')
    {
        if (!$Config->isValid)
            $Config->createDefault();

        $modules = explode(',',$modules);
        if (empty($modules))
            $SourceControl->sync();
        else
            foreach($modules as $m)
                $SourceControl->sync($m);
    }

    /**
     * Sync code for modules
     *
     * @param \app\services\SourceControl $SourceControl
     * @param \app\services\Config $Config
     * @param string $path path to project root folder
     * @param string $modules modules to sync code, separated by comma (,). If not specified all modules will be synced
     */
    public function actionDeploy($path, $modules='',
                                 $SourceControl = 'svc://app/services/SourceControl',
                                 $Config = 'svc://app/services/Config') {
        $this->actionIndex($path, $modules, $SourceControl, $Config);
    }

    /**
     * Generate NginX configuration file for the virtual host
     * 
     * @param $path
     * @param \app\services\Project $Project
     */
    public function actionNginx($path,
                                $Project = 'svc://app/services/Project'){
        $env =$Project->getEnv();
        $webRoot = $env['projectFolder'].'/web/';
        $file = $webRoot.'/env/'.$env['domain'].'.nginx';

        $config = "server {\n\tinclude snippets/indition.conf;\n\n\troot {$webRoot};\n\tserver_name {$env['domain']};\n}";

        file_put_contents($file,$config);
        copy(App::$basePath.'indition.conf',$webRoot.'/env/indition.conf');

        $this->getResponse()->send("NginX configuration file for {$env['domain']} is generated into {$webRoot}/env/");
        $this->getResponse()->send("You can run this command to add it to Nginx:\n
        sudo ln -s {$file} /etc/nginx/sites-enabled/{$env['domain']}\n
        sudo systemctl restart nginx\n\n");
        $this->getResponse()->send("Make sure your hosts file has {$env['domain']} points to 127.0.0.1\nNOTE: If you are using a Vagrant box, you may need to use another port than port 80,i.e. http://{$env['domain']}:8080");
    }

}