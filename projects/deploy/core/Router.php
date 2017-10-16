<?php
/**
 * Created by PhpStorm.
 * User: hung5s
 * Date: 09/07/2017
 * Time: 15:53
 */

namespace core;


class Router extends Component
{
    protected $app;

    protected $controller;
    
    public function __construct(App $app)
    {
        parent::__construct($app);

        $this->app->scriptName = array_shift($_SERVER['argv']);

        App::$logger->trace("Bootstrap file is {file}", 'Router Init',['file' => $this->app->scriptName]);

        if (empty($_SERVER['argv'])){
            $this->controller = App::$params['defaultRoute'];
            App::$logger->trace('No route detected! Using default controller {default}', 'Router Init',['default' => $this->controller]);
        } else {
            $this->controller = array_shift($_SERVER['argv']);
            App::$logger->trace('Controller is {controller}', 'Router Init',['controller' => $this->controller]);
        }
    }
    
    public function route(){
        App::$logger->trace('Begin routing', 'Router');

        $tmp = explode('/',$this->controller);
        $action = array_pop($tmp);
        $file = App::$basePath.'app/controllers/'.implode('/',$tmp);

        App::$logger->trace('Loading controller from file {file}', 'Router', ['file' => $file.'.php']);
        if (file_exists($file.'.php')){
            $file .= '.php';
        } else {
            $file .= "/{$action}.php";
            $file = str_replace('//', '/', $file);
            $action = 'index';

            App::$logger->trace('File not found, using {file} as controller file. Action will be "index"', 'Router',['file' => $file]);
        }

        if (!file_exists($file)) {
            $controller = $this->createController('');
            App::$logger->trace('File not found, using {file} as controller file. Routing to core/Controller/index.', 'Router',['file' => $file]);
        } else {
            $controller = $this->createController($file);
        }
        if ($controller instanceof Controller){
            $this->run($controller,$action);
        } else {
            throw new \Exception("Unable to start ".get_class($controller).".{$action}.");
        }
    }

    protected function createController($file){
        $className = str_replace('.php','',basename($file));
        if (empty($className)){
            $className = 'core\Controller';
        } else {
            $className = '\\'.strtr(str_replace(App::$basePath, '', $file),[
                    '/'=>'\\',
                    '.php' => ''
                ]);
        }

        App::$logger->trace('Creating controller from class {class}', 'Router', ['class' => $className]);
        $controller = new $className($this->app);
        return $controller;
    }

    /**
     * @param Controller $controller
     * @param $action
     */
    protected function run($controller, $action){
        App::$logger->trace("Start running {$action}", 'Router');

        $middlewares = $controller->middlewares($action);
        if (empty($middlewares))
            $middlewares[]='_';

        App::$logger->trace("Middlewares found:\n{wares}", 'Router', ['wares' => print_r($middlewares,1)]);

        foreach($middlewares as $md){
            $contextMapper = [];
            if (is_array($md) && count($md) == 2){
                $contextMapper = $md[1];
                $md = $md[0];
            } elseif (!is_string($md)){
                throw new \Exception("Cannot create middleware with this configuration ".print_r($md,1));
            }

            App::$logger->trace('Running middleware {md} with context mapper {map}', 'Router', [
                'md' => $md,
                'map' => (empty($contextMapper)?'none':print_r($contextMapper,1))
            ]);

            if ($md == '_') {
                $controller->run($action);
            } else {
                $md = $this->app->createMiddleware($md, $contextMapper);
                $md->handle($this->app->response);              
            }

            if ($this->app->response->handled){
                App::$logger->trace('Response status changed to handled.', 'Router');
                break;
            }
        }
    }
}