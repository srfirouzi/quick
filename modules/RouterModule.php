<?php 
class RouterModule{
    /**
     * list of route roll
     * @var array
     */
    private $_routes=array();
    private $_app;
    /**
     * 
     * @param Quick $app
     */
    function __construct($app){
        $this->_app=$app;
        $app->add_function('route',array($this,'route'));
        $app->add_function('run',array($this,'run'));
    }
    
    
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
    public function route($path,$func,$method='GET|POST'){
        $pathParts=$this->_compilePath($path);
        
        $this->_routes[]=array(
            'method'=>$method,
            'func'=>$func,
            'maxlen'=>$pathParts['maxlen'],
            'fixlen'=>$pathParts['fixlen'],
            'paths'=>$pathParts['paths'],
            'extera'=>$pathParts['extera']
        );
    }
    /**
     * run micro service
     * @param string|NULL $path for route or use QuickRequest::get('request_path') use .htaccess to map
     * @return mixed callback function return,return
     */
    public function run($path=null){
        // sort route
        usort($this->_routes, function($a,$b){
            $lena=$a['fixlen'];
            $lenb=$b['fixlen'];
            
            $maxa=$a['maxlen'];
            $maxb=$b['maxlen'];
            
            $exa=$a['extera'];
            $exb=$b['extera'];
            
            if($exa!=$exb){
                if($exa)
                    return -1;
                else
                    return 1;
            }
            if ($lena==$lenb && $maxa==$maxb) {
                return 0;
            }
            if($lena>$lenb){
                return 1;
            }
            if($lena==$lenb && $maxa<$maxb){
                return 1;
            }
            return -1;
        });
            
        if(is_null($path))
            $path=$this->_app->req->path;
            
        $func=$this->_getRouteFunction($path);
        
        if(is_callable($func)){
            return call_user_func($func, $this->_app->req,$this->_app->res,$this->_app);
        }
        return $this->_app->not_found($this->_app->req,$this->_app->res,$this->_app);

    }
    
    /**
     * return matched route roll function
     * @param string $path reuest path
     * @return callback(QuickRequest,QuickResponse)
     */
    private function _getRouteFunction($path){
        $parts=explode('/',$path);
        $path_part=array();
        for($i=0;$i<count($parts);$i++){
            $part=trim($parts[$i]);
            if($part!=''){
                $path_part[]=$part;
            }
        }
        //-----------
        $len=count($path_part);
        
        for($i=0;$i<count($this->_routes);$i++){
            $route=$this->_routes[$i];
            if(strpos($this->_routes[$i]['method'], $this->_app->req->method) === false){
                continue;
            }
            if( $len>=$route['fixlen'] && ( $len<=$route['maxlen'] || $route['extera'] ) ){
                $parameter=array();
                $is_ok=true;
                for($j=0;$j<$len && $j<$route['maxlen'];$j++){
                    if(substr($route['paths'][$j], 0,1)=='!' || substr($route['paths'][$j], 0,1)==':'){
                        $parameter[substr($route['paths'][$j], 1)]=$path_part[$j];
                    }else{
                        if($route['paths'][$j]!=$path_part[$j]){
                            $is_ok=false;
                            break;
                        }
                    }
                }
                if($is_ok==false)
                    continue;
                    if($route['extera']){
                        $starpart='';
                        for($j=$route['maxlen'];$j<$len;$j++){
                            $starpart=$starpart.'/'.$path_part[$j];
                        }
                        $parameter['*']=$starpart;
                    }
                    
                    $this->_app->req->setParameter($parameter);
                    return $route['func'];
            }
        }
        return null;
    }
    
    /**
     * compile path
     *
     * this method compile path pattern to main parts
     *
     * @param string $path path patern
     * @throws Exception
     * @return array
     */
    private function _compilePath($path){
        $parts=explode('/',$path);
        $clean_parts=array();
        for($i=0;$i<count($parts);$i++){
            $part=trim($parts[$i]);
            if($part!=''){
                $clean_parts[]=$part;
            }
        }
        $maxlen=0;
        $fixlen=0;
        $isdynamic=false;
        $isextera=false;
        $used_path=array();
        
        for($i=0;$i<count($clean_parts);$i++){
            $part=$clean_parts[$i];
            if($part=='*'){
                $isextera=true;
                break;
            }
            if(substr($part, 0, 1)=='!'){
                $isdynamic=true;
            }else{
                if($isdynamic){
                    throw new Exception('after dynamic part,must use only dynamic part');
                }
                $fixlen++;
            }
            $used_path[]=$part;
            $maxlen++;
        }
        return array(
            'maxlen'=>$maxlen,
            'fixlen'=>$fixlen,
            'paths'=>$used_path,
            'extera'=>$isextera
        );
    }
    
    
    
    
}






?>