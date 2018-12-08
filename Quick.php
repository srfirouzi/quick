<?php
/**
 * Quick.php micro framework
 * @author Seyed Rahim Firouzi <seyed.rahim.firouzi@gmail.com>
 * @version 1.0
 * @license MIT
 * @copyright 2018 Seyed Rahim Firouzi
 */
/**
 * @var string web root path
 */
define ('BASEPATH',dirname ( dirname ( __FILE__ ) ));
/**
 * load php file
 * @param string $path path to the file base of web root
 * @return boolean if file exist and load return true or return false
 */
function load_file($path){
    $filename=BASEPATH . $path;
    if(file_exists($filename)){
        include_once $filename;
        return true;
    }
    return false;
}
/**
 * main quick.php application class
 */
class Quick{
    /**
     * @var array setting of application
     */
    private $_setting=[];
    /**
     * @var array list of magic extera method __get function
     */
    private $_gets=[];
    /**
     * @var array list of magic method
     */
    private $_functions=[];
    /**
     * @var array list of registered magic property
     */
    private $_classes=[];
    /**
     * @var array list of magic property
     */
    private $_objects=[];
    /**
     * @var array modules loaded
     */
    private $_modules=[];
    /**
     * constructor
     * @param array $setting setting for app
     */
    function __construct($setting=[]){
        //load /setting.php ->have template in quick/setting.php
        $file=BASEPATH.'/setting.php';
        if(file_exists($file)){
            include $file ;
            foreach ($settings as $key => $value){
                $this->_setting[$key]=$value;
            }
        }
        //set setting parameter
        foreach ($setting as $key => $value){
            $this->_setting[$key]=$value;
        }
        $this->add_function('not_found', array($this,'_not_found'));
        
        $mo=$this->setting('modules');

        for($i=0;$i<count($mo);$i++){
            $name=ucfirst($mo[$i]).'Module';
            $filename='/quick/modules/'.$name.'.php';
            if(load_file($filename)){
                $obj=new $name($this);
                $this->_modules[$mo[$i]]=$obj;
            }
        }
        
        //register req ,res
        $this->register('req', 'QuickRequest',[$this],'/quick/exteras/QuickRequest.php');
        $this->register('res', 'QuickResponse',[$this],'/quick/exteras/QuickResponse.php');
        
        
    }
    /**
     * get application setting
     * @param string $name
     * @param mixed $def
     * @return mixed
     */
    public function setting($name,$def='') {
        if(isset($this->_setting[$name]))
            return $this->_setting[$name];
            return $def;
    }
    /**
     * set application seting
     * @param string $name
     * @param mixed $value
     */
    public function set_setting($name,$value=''){
        $this->_setting[$name]=$value;
    }
    /**
     * add new method to engine
     *
     * @param string $name method name
     * @param function(...) $func
     */
    public function add_function($name,$func){
        if($name=='__get'){
            $this->_gets[]=$func;
        }else{
            $this->_functions[$name]=$func;
        }
    }
    /**
     * magic method to return defined method
     * @param string $name name of method
     * @param array   $params call parameters
     * @throws Exception
     * @return mixed
     */
    public function __call($name, $params) {
        if(isset($this->_functions[$name])){
            $func=$this->_functions[$name];
            if(is_callable($func)){
                return call_user_func_array($func, $params);
            }
        }
        throw new Exception('method '.$name.' is not exist');
    }
    /**
     * add new property for application
     * @param string $name name of peroperty
     * @param string $class name of class for make peropert
     * @param array $params parameter to constructor of class
     * @param string $loadpath file path content class defined for loaded
     */
    public function register($name, $class,$params = [],$loadpath=''){
        $this->_classes[$name]=[
            'class'=>$class,
            'params'=>$params,
            'loadpath'=> $loadpath
        ];
    }
    /**
     * magic method to return model
     * @param string $name
     * @throws Exception
     * @return object
     */
    public function __get($name){
        if(isset($this->_objects[$name])){
            return $this->_objects[$name];
        }
        if(isset($this->_classes[$name])){
            $class=$this->_classes[$name];
            if($class['loadpath']!=''){
                load_file($class['loadpath']);
            }
            $reflection =new ReflectionClass($class['class']);
            $obj = $reflection->newInstanceArgs($class['params']);
            $this->_objects[$name]=$obj;
            return  $obj;
        }
        
        
        for($i=0;$i<count($this->_gets);$i++){
            $func=$this->_gets[$i];
            if(is_callable($func)){
                $obj=call_user_func($func,$name);
                if(!is_null($obj)){
                    $this->_objects[$name]=$obj;
                    return  $obj;
                }
            }
        }
        throw new Exception($name.' is not exist');
    }
    /**
     * default 404 route function
     *
     * @param QuickRequest $req
     * @param QuickResponse $res
     */
    public function _not_found($req,$res) {
        $res->setOutCode(404);
        $res->write('Page not found');
    }
}
?>