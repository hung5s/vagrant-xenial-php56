<?php
/**
 * Created by PhpStorm.
 * User: hung5s
 * Date: 09/07/2017
 * Time: 21:36
 */

namespace core;

class Controller extends Component
{
    /**
     * @var \core\App $app
     */
    protected $app;

    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    /**
     * Shortcut to App's response object
     * @return \core\services\Response
     */
    protected function getResponse(){
        return $this->app->getResponse();
    }

    public function middlewares($action){
        switch ($action){
            case '':
                return [];
            default:
                return [];
        }
    }

    public function run($action){
        $action = 'action'.ucfirst($action);
        $params = $this->injectResources($action);

        /** validate parameters passed into action*/
        $rf = new \ReflectionMethod($this, $action);
        $missing = [];
        foreach($rf->getParameters() as $p){
            if (!isset($params[$p->name]))
                $missing[] = $p->name;
        }
        if (count($missing)){
            $this->showHelpFromDoc($rf->getDocComment(), $missing);
        }

        call_user_func_array([$this,$action], $params);
    }

    public function actionIndex(){
        $controllers = $this->findControllers();
        $this->response->send("No action specified.\n
        \rYou can run an action with this command:
        \r\tphp {$this->app->scriptName} path/to/action [--param=value,...]
        \r\tphp {$this->app->scriptName} <controller>/Help [--action=action]
        \r
        \rAvailable controllers:\n".implode("\n\r",$controllers));
    }

    public function actionHelp($action = ''){
        $rf = new \ReflectionClass($this);
        foreach($rf->getMethods(\ReflectionMethod::IS_PUBLIC) as $method){
            if ($method->getName() == 'actionHelp')
                continue;
            if (strpos(strtolower($method->getName()),'action'.strtolower($action))!==0)
                continue;

            $name = '/'.lcfirst(str_replace('action','',$method->getName()));
            echo $name,"\n",str_repeat('-',strlen($name)),"\n";
            $this->showHelpFromDoc($method->getDocComment(),[]);
            echo "\n";
        }
    }

    protected function findControllers(){
        $dir = App::$basePath.'/app/controllers/';
        $files = scandir($dir);
        $controllers = [];
        foreach($files as $file){
            if (strpos($file,'.php') === false)
                continue;
            $controllers[] = str_replace('.php','',$file);
        }
        return $controllers;
    }

    protected function showHelpFromDoc($doc, $missing){
        $doc = trim($doc);
        $doc = strtr($doc,[
            '/**' => '',
            '*/' => '',
            "\t" => '',
            '  ' => ' '
        ]);
        $lines = explode("\n",$doc);
        foreach($lines as $k => $l){
            $l = substr($l,strpos($l,'*')+1);
            $l = trim($l);
            /** remove empty line*/
            if ($l==''){
                unset($lines[$k]);
                continue;
            }

            /** exclude typed param as user cannot pass it to the action*/
            if (strpos($l,'@param \\')!== false){
                unset($lines[$k]);
                continue;
            }

            /** remove @param ...$ from the param's document */
            if (strpos($l,'@param') !== false)
                $l = "  -".substr($l,strpos($l,'$')+1);

            $lines[$k] = $l;
        }
        echo implode("\n", $lines),"\n";
        if (count($missing))
            echo "Missing parameter(s): ".implode(', ',$missing),"\n";
    }
}