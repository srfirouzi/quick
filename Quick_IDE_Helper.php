<?php 
/**
 * never load
 * only for autocomplate by ide
 */
class Quick{
    /**
     * default 404 method
     * @param QuickRequest $req
     * @param QuickResponse $res
     */
    public function not_found( $req, $res){}
    /**
     * @var QuickRequest requset element
     */
    public $req;
    /**
     * @var QuickResponse response element
     */
    public $res;
    /**
     * 
     * @var QuickFileSystem
     */
    public $fs;
    /**
     * 
     * @var QuickDB
     */
    public $db;
    
    /**
     * return model by name
     * <p>
     * model is model of object insert model path by class name UpperCase first charecter add concat 'Model'
     * and class extend Model(/quick.php/module/MvcModule.php Model class)
     * <br>
     * example:<br>  hello model equal <br> HelloModel extend Model{}
     * <br>
     * this method don't return new instance of Model
     * <br>
     * auto load and use in Quick engine ,and automatic add to Quick
     * </p>
     * @param string $model model name
     * @exception
     * @return object|NULL
     */
    public function model($model){}
    /**
     * render view and return data
     * @param string $name view name
     * @param array $env data to reander in view
     * @param array $layoutEnv data to reander in layout
     * @return string
     */
    public function view($name,$env=array()){}
    /**
     * add function to template engine
     * @param string $name
     * @param function(...) $func
     */
    public function add_view_function($name,$func){}
    /**
     * return controler by name
     * <p>
     * controller is model of object insert controller path by class name UpperCase first charecter add concat 'Controller'
     * and class extend Controller(/quick.php/module/MvcModule.php Controller class)
     * <br>
     * example:<br>  hello controller equal <br> HelloController extend Controller{}
     * <br>
     * this method every time return new instance of Controller
     * </p>
     * @param string $controller name of controller
     * @return object|NULL
     */
    public function controller($controller){}
    /**
     * set access function to control of return controller
     * @param callable $func callback function
     */
    public function controller_access($func){}
    /**
     * add route for routing
     *
     * add new path patern to route incoming request.
     *
     * @param string $path path pattern fo route
     * @param callable(QuickRequest,QuickResponse,Quick)  $func   callback function to call ,if request path match by pattern
     * @param string $method requet path sprate by |
     *
     * @example
     * $app->route('/path/:varable/!varable_maybe_not_exist/',function($req,$res,$app){},'POST');<br/>
     * $app->route('/path/*',function($req,$res){});<br/>
     * $app->route('/*',function($req,$res){});<br/>
     */
    public function route($path,$func,$method='GET|POST'){}
    /**
     * run micro service
     * @param string|NULL $path for route or use QuickRequest::get('request_path') use .htaccess to map
     * @return mixed callback function return,return
     */
    public function run($path=null){}
}





?>