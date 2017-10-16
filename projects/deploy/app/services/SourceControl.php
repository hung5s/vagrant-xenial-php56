<?php
/**
 * Created by PhpStorm.
 * User: tagremvn
 * Date: 18/07/2017
 * Time: 15:44
 */

namespace app\services;


use core\App;

class SourceControl extends \core\services\Service
{
    /**
     * @var \app\services\Config $config
     */
    protected $config;

    /**
     * @var \core\services\Shell $shell
     */
    protected $shell;

    /**
     * @var \app\services\Project $project
     */
    protected $project;

    protected $svnUser;
    protected $svnPassword;
    protected $authString;

    /**
     * SourceControl constructor.
     * @param $app
     * @param \app\services\Config $Config
     */
    public function __construct($app,
                                $Config = 'svc://app/services/Config',
                                $Shell = 'svc://core/services/Shell',
                                $Project = 'svc://app/services/Project')
    {
        parent::__construct($app);

        $this->svnUser = App::$params['svnUser'];
        $this->svnPassword = App::$params['svnPassword'];
        
        $this->authString = " --username {$this->svnUser} --password {$this->svnPassword} --non-interactive --no-auth-cache";
    }

    public function svnInfo($moduleId){
        $module = $this->config->findModule($moduleId);
        $path = $module['path'];
        $rawInfo = $this->shell->runCommand('svn info '.$path.$this->authString, false, false);

        $svnInfo = [];
        foreach($rawInfo as $line){
            $pos = strpos($line,':');
            if (!$pos)
                continue;
            $svnInfo[substr($line,0,$pos)] = trim(substr($line,$pos+1));
        }

        return $svnInfo;
    }

    public function svnRevisionInfo($svnInfo){
        $info = ['brandType' => '','brandName' => '', 'rev' => ''];
        if (isset($svnInfo['URL'])){
            $info = $this->svnBrandInfo($svnInfo['URL']);
            $info['rev'] = $svnInfo['Last Changed Rev'];
        }
        return $info;
    }

    protected function svnBrandInfo($url){
        $paths = explode('/',$url);
        $info = [];
        for ($i=0;$i<count($paths);$i++){
            if ($paths[$i] == 'trunk'){
                $info['brandType'] = 'trunk';
                $info['brandName'] = '';
            } elseif ($paths[$i] == 'tags') {
                $info['brandType'] = 'tagged';
                $info['brandName'] = $paths[$i+1];
            } elseif ($paths[$i] == 'branches') {
                $info['brandType'] = 'branch';
                $info['brandName'] = $paths[$i+1];
            }
        }
        return $info;
    }
    public function status($moduleId = '', $withDiff = true){
        $config = $this->config;
        $vcs = $config['vcs'];

        $targetModule = $moduleId;
        foreach($config['source'] as $name => $mConfig){
            $moduleId = $this->config->moduleFromKey($name);
            if ($targetModule != '' && $targetModule == $moduleId){
                echo $this->moduleStatus($moduleId, $withDiff);
                break;
            } elseif ($targetModule == '') {
                $result = $this->moduleStatus($moduleId, $withDiff);
                if (trim($result)!=''){
                    echo $moduleId,"\n",str_pad('',strlen($moduleId),'-'),"\n",$result,"\n\n";
                }
            }
        }
    }

    public function moduleStatus($moduleId, $withDiff = true){
        $tmp = $this->config->findModule($moduleId);
        $result = $this->shell->runCommand("svn status ".$tmp['path'].$this->authString, false,false);
        foreach($result as $i => $line){
            if ($line == '')
                continue;
            if ($line[0] == '?' && is_dir(trim(substr($line,1))))
                unset($result[$i]);
            if ($withDiff && $line[0] == 'M' && is_file(trim(substr($line,1)))){
                $diff = $this->shell->runCommand("svn diff ".trim(substr($line,1)).$this->authString, false,false);
                $result[$i] = $line."\n".implode("\n",$diff);
            }
        }
        return implode("\n",$result);
    }

    public function sync($moduleId = ''){
//        if (!$this->config->isValid)
//            throw new \Exception("Module configuration file is invalid.\n".implode("\n",$this->config->errors));

        $vcs = $this->config->getVcs();

        $targetModule = $moduleId;
        foreach($this->config->getSource() as $name => $mConfig){
            $moduleId = $this->config->moduleFromKey($name);
            if ($targetModule != '' && $targetModule == $moduleId){
                $this->syncModule($moduleId, $mConfig, $vcs);
                break;
            } elseif ($targetModule == '') {
                $this->syncModule($moduleId, $mConfig, $vcs);
            }
        }
    }

    public function syncModule($moduleId, $config, $svnRoot, $cleanCheckout = false){
        $localSvn = $this->svnRevisionInfo($this->svnInfo($moduleId));

        $sourceSvnPath = $svnRoot.$config['path'];
        if (strpos($sourceSvnPath,'trunk') === false)
            $sourceSvnPath.=$config['version'];
        $sourceSvn = $this->svnBrandInfo($sourceSvnPath);

        if ($localSvn['brandType'] == $sourceSvn['brandType'] && $localSvn['brandName'] == $sourceSvn['brandName'])
            $svnAction = 'update';
        elseif ($localSvn['rev'] != '')
            $svnAction = 'switch';
        else
            $svnAction = 'checkout';

        $path = strtoupper($svnAction)." {$localSvn['brandType']}/{$localSvn['brandName']} <<< {$sourceSvn['brandType']}/{$sourceSvn['brandName']}";
        echo "\n\n".str_pad('',strlen($path),'-')."\n",
        "[{$moduleId}]\n",
        $path,
        "\n",str_pad('',strlen($path),'-'),"\n";

        $tmp = $this->config->findModule($moduleId);
        switch($svnAction){
            case "update":
                $this->shell->run("svn up ".$tmp['path'].$this->authString);
                break;
            case "switch":
                $this->shell->run("svn switch {$sourceSvnPath} {$tmp['path']}".$this->authString);
                break;
            case "checkout":
                $this->shell->run("svn checkout {$sourceSvnPath} {$tmp['path']}".$this->authString);
                break;
        }
    }

    /**
     * Run an 'svn ...' command
     * @param $cmd
     */
    public function svnCmd($cmd){
        $this->shell->run($cmd.$this->authString);
    }
}