<?php
namespace core;

require_once 'Obj.php';

class App extends Obj
{
    public static $basePath = '';

    public static $logger;
    public static $exHandler;
    public static $params;
    public $scriptName;

    protected $serviceProvider;

    protected $router;

    protected $response;
    
    public function __construct()
    {
        self::$basePath = dirname(__FILE__) . '/../';
        spl_autoload_register([$this, 'autoload']);

        self::$params = new Params();
        self::$logger = new services\Logger();
        self::$exHandler = new services\ExceptionHandler();
        $this->serviceProvider = new ServiceProvider($this);

        self::$logger->trace("Core services params, logger, exHandler, serviceProvider are loaded.", 'App Init');

        $this->router = new Router($this); 
        $this->response = $this->serviceProvider->get('Response');
        self::$logger->trace("Response object is {class}", 'App Init',['class' => get_class($this->response)]);
    }

    public function run()
    {
        $this->router->route();
    }

    public function createMiddleware($class, $mapper){
        if (strpos($class,':') !== false){
            list($class,$context) = explode(':', $class);
        } else {
            $context = 'default';
        }
        $md = new $class($this);
        $md->context = $context;
        if (is_array($mapper))
            $md->setMapper($mapper);
        return $md;
    }

    public function autoload($class)
    {
        $class = str_replace('\\', '/', $class).'.php';
        require_once self::$basePath.$class;
    }

    public function getService($name, $default){
        return $this->serviceProvider->get($name, $default);
    }

    public function getRouter()
    {
        return $this->router;
    }

    /**
     * Return the response service of the application
     * 
     * @return \core\services\Response
     */
    public function getResponse(){
        return $this->response;
    }
    
}