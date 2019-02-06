<?php
class Controller{
    /**
     *
     * @var Quick but relay
     */
    protected $app;
    /**
     *
     * @param Quick $eng
     */
    public function __construct($app){
        $this->app=$app;
    }
    /**
     * for standardization request/respond function API
     * 
     * @param QuickRequest $req
     * @param QuickResponse $res
     * @param Quick $app
     * @return string
     */
    public function index($req,$res,$app){
        return 'this controller';
    }
}

class Model{
    /**
     *
     * @var Quick but relay
     */
    protected $app;
    /**
     *
     * @param Quick $eng
     */
    public function __construct($app){
        $this->app=$app;
    }
}


class MvcModule{
    private $_model_path;
    private $models=array();
    private $app;
    
    private $_view_path='';
    /**
     * 
     * @var Boof
     */
    private $_view=null;
    
    private $_controller_path;
    private $_controller_assecc_fun;

    /**
     * 
     * @param Quick $app
     */
    public function __construct($app){
        $this->app=$app;
        //model

        
        $this->_model_path=$app->setting('mvc.model.path');
        
        $app->add_function('model',array($this,'model'));
        $app->add_function('__get',array($this,'model'));
        //view
        
        $this->_view_path=BASEPATH.$app->setting('mvc.view.path');
        
        $app->add_function('view',array($this,'view'));
        $app->add_function('add_view_function',array($this,'add_view_function'));
        //controller
        $this->_controller_path=$app->setting('mvc.controller.path');
        $app->add_function('controller',array($this,'controller'));
        $app->add_function('controller_access',array($this,'controller_access'));
        
    }
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
    public function model($model){
        if(isset($this->models[$model]))
            return $this->models[$model];
        $name=ucfirst($model).'Model';
        $path=$this->_model_path.'/'.$name.'.php';
        
        if(load_file( $path)){
            $obj=new $name($this->app);
            $this->models[$model]=$obj;
            return $obj;
        }
        throw new Exception('model '.$name.' dont found');
    }
    /**
     * render view and return data
     * @param string $name view name
     * @param array $env data to reander in view
     * @param array $layoutEnv data to reander in layout
     * @return string
     */
    public function view($name,$env=array()){
        if(is_null($this->_view))
            $this->makeView();
        return $this->_view->view($name,$env);
    }
    /**
     * add function to template engine
     * @param string $name
     * @param function(...) $func
     */
    public function add_view_function($name,$func){
        if(is_null($this->_view))
            $this->makeView();
         $this->_view->addFunction($name,$func);
    }
    private function makeView(){
        load_file('/quick/exteras/boof.php/Boof.php');
        $this->_view=new Boof($this->_view_path);
    }
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
    public function controller($controller){
        $access=true;
        if(is_callable($this->_controller_assecc_fun)){
            $access=call_user_func_array( $this->_controller_assecc_fun, array($this->app,$controller));
        }
        if($access){
            $name=ucfirst($controller).'Controller';
            $path=$this->_controller_path.'/'.$name.'.php';
            if(load_file ($path)){
                $obj=new $name($this->app);
                return $obj;
            }
        }
        return null;
    }
    /**
     * set access function to control of return controller
     * @param callable $func callback function
     */
    public function controller_access($func){
        $this->_controller_assecc_fun=$func;
    }
    
    
}




?>