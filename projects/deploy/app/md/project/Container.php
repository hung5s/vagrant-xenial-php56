<?php
/**
 * Created by PhpStorm.
 * User: tagremvn
 * Date: 14/07/2017
 * Time: 10:33
 */

namespace app\md\project;


use core\App;
use core\services\Logger;
use core\services\Response;

class Container extends \core\Middleware
{
    /**
     * @var \core\services\File $file
     */
    protected $file;


    /**
     * @var \core\services\Shell $shell
     */
    protected $shell;

    /**
     * @var \app\services\SourceControl $sourceControl
     */
    protected $sourceControl;

    public function __construct($app,
                                $File = 'svc://core/services/File',
                                $Shell = 'svc://core/services/Shell',
                                $SourceControl = 'svc://app/services/SourceControl')
    {
        parent::__construct($app);
    }

    /**
     * @param \app\services\Project $Project
     */
    public function handleCreate($Project = 'svc://app/services/Project'){
        $this->createFolders($Project->getEnv());
    }

    /**
     * @param \app\services\Project $Project
     */
    public function handleTheme($Project = 'svc://app/services/Project'){
        $env =$Project->getEnv();
        $webRoot = $env['projectFolder'].'/web/';
        $siteDir = $env['siteDir'];
        if ($siteDir == '')
            $siteDir = $env['siteOwner'].'/'.$env['siteId'];
        $siteRoot = $webRoot.'/sites/'.$siteDir.'/';

        $basicThemePath = $env['rootDir'].'data/demo/themes/basic';
        if (file_exists($basicThemePath)) {
            $this->file->cpDirRecursive($basicThemePath, $siteRoot.'/themes/basic');
        }
    }

    /**
     * @param \app\services\Project $Project
     */
    public function handleUploads($Project = 'svc://app/services/Project'){
        $env =$Project->getEnv();
        $webRoot = $env['projectFolder'].'/web/';
        $siteDir = $env['siteDir'];
        if ($siteDir == '')
            $siteDir = $env['siteOwner'].'/'.$env['siteId'];
        $siteRoot = $webRoot.'/sites/'.$siteDir.'/';

        $uploadsPath = $env['rootDir'].'data/demo/uploads';
        if (file_exists($uploadsPath)) {
            $this->file->cpDirRecursive($uploadsPath, $siteRoot);
        }
    }

    public function createFolders($env){
        $webRoot = $env['projectFolder'].'/web/';
        /** checkout /web folder before creating the sub folders */
        $sourceFile = $env['projectFolder'].'/source.json';
        if (file_exists($sourceFile)){
            $sourceInfo = json_decode(file_get_contents($sourceFile),true);
            $webSvn = $sourceInfo['webSvn']['url'];
        }
           
        if (!isset($webSvn))
            $webSvn = App::$params['webSvn'];
        $this->sourceControl->svnCmd("svn co {$webSvn} {$webRoot}");
        
        
        $siteDir = $env['siteDir'];
        if ($siteDir == '')
            $siteDir = $env['siteOwner'].'/'.$env['siteId'];
        $siteRoot = $webRoot.'/sites/'.$siteDir.'/';

        $dirs = [
            $env['rootDir'],
            $env['codeDir'],
            $webRoot,
            $siteRoot,
            $env['codeDir'].'modules',
            $env['codeDir'].'themes',
            $webRoot.'admin',
            $webRoot.'admin/assets',
            $webRoot.'admin/themes',
            $webRoot.'env',
            $siteRoot,
            $siteRoot.'runtime',
            $siteRoot.'assets',
            $siteRoot.'uploads',
        ];

        foreach($dirs as $dir){
            if (!file_exists($dir))
                $this->file->mkdir($dir);
            if ((strpos($dir,'assets') !== false || strpos($dir,'runtime') !== false)) {
                $this->shell->runCommand("chmod -R 777 {$dir}");
            }
        }

        foreach($dirs as $dir){
            if (!file_exists($dir))
                echo "Unable to create dir: {$dir}\n";
            if ((strpos($dir,'assets') !== false || strpos($dir,'runtime') !== false) && !is_writable($dir)) {
                echo "Unable to make writable dir: {$dir}\n";
            }
        }

        // Create Site module
        $path = $env['codeDir'].'modules/Site';
        $this->createSiteModule($path);
    }

    protected function createSiteModule($path){
        if (file_exists($path))
            return;

        mkdir($path);
        $php = "<?php\nclass SiteModule extends XWebModule{\n}";
        try {
            file_put_contents($path.'/SiteModule.php',$php);
        } catch (\Exception $ex){
            App::$logger->write($ex->getMessage(),Logger::ERROR);
        }
        
    }
}