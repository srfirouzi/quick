<?php
/**
 * Quick.php micro framework
 * @author Seyed Rahim Firouzi <seyed.rahim.firouzi@gmail.com>
 * @version 1.0
 * @license MIT
 * @copyright 2018 Seyed Rahim Firouzi
 */
/**
 * quick.php QuickResponse class
 */
class QuickResponse{
    /**
     * @var Quick parent Application
     */
    private $_app=null;
    /**
     * @var string output http code
     */
    private $_outcode='200';
    /**
     * @var array list of header
     */
    private $_headers=array();
    /**
     * @var array list of cookie
     */
    private $_cookies=array();
    /**
     * @var bool header sended 
     */
    private $_sended_header=false;
    /**
     * @var string body content
     */
    private $_body='';
    /**
     * @var bool send complate
     */
    private $_finish=false;
    /**
     * constructor
     * @param Quick $app
     */
    public function __construct($app){
        $this->_app=$app;
    }
    /**
     * destructor
     */
    public function __destruct(){
        $this->flash();
    }
    /**
     * add http header to response
     * @param string $key
     * @param string $value
     */
    public function header($key,$value){
        $this->_headers[$key]=$value;
    }
    /**
     * write data to response
     * @param string $content
     */
    public function write($content){
        $this->_body .=$content;
    }
    /**
     * write json to output
     * @param mixed $table
     */
    public function writeJson($table){
        $this->_body.=json_encode($table);
        
    }
    /**
     * set http response code
     * @param number $number
     */
    public function setOutCode($number=200){
        $this->_outcode=$number;
    }
    /**
     * return full url for app
     * @param $path string relative path start by '/'
     * @return string full path (relative path ref site or full path)
     */
    public function url($path=''){
        return $this->_app->setting('base.url').$path;
    }
    /**
     * set redirect path
     * @param string $url
     */
    public function redirect($path=''){
        $this->header('Location', $this->url($path));
        //$this->_flash(303, $this->_cookies, array('Location'=>$url), '');
    }
    /**
     * response file to client
     * @param string $file file path
     * @param string $name 
     */
    public function file($file,$name=''){
        $file=BASEPATH.$file;
        $header=array();
        if($name==''){
            $name=basename($file);
        }
        $parts=explode(".", $name);
        $extension = end($parts);
        $extension= $extension ? $extension : '';
        $mime='';
        if(isset( QuickResponse::$mimes[$extension])){
            $mime=QuickResponse::$mimes[$extension];
        }
        if($mime!=''){
            $header['Content-Type']=$mime;
        }
        $size = filesize($file);
        $header['Content-length']= $size;
        $header['Content-Disposition']= 'attachment; filename="'.$name.'"';
        $this->_flash(200, array(), $header, '');
        readfile($file);
    }
    /**
     * send data to client
     */
    public function flash(){
        $this->_flash($this->_outcode,$this->_cookies,$this->_headers, $this->_body);
        $this->_body='';
        $this->_outcode=200;
        $this->_cookie=array();
        $this->_headers=array();
        
    }
    /**
     * set cookie
     * @param string $name
     * @param string $value
     * @param number $expire
     */
    public function cookie($name, $value, $expire = 0) {
        if ($expire != 0)
            $this->_cookies[$name]=array('value'=>$value,'expire'=>$expire + time ());
        else
            $this->_cookies[$name]=array('value'=>$value,'expire'=>0);
            
    }
    private function _flash($code,$cookies,$header,$body){
        if($this->_finish){
            return;
        }
        if(!$this->_sended_header){
            //code
            $codetxt=isset(QuickResponse::$codes[$code])?QuickResponse::$codes[$code]:'unKnown';
            $protocol=isset($_SERVER[ "SERVER_PROTOCOL" ])?$_SERVER[ "SERVER_PROTOCOL" ]:'HTTP/1.1';
            if($codetxt!=''){
                header($protocol.' '.$code.' '.$codetxt);
            }
            //cookie
            foreach ($cookies as $key => $value) {
                setcookie ( $key, $value['value'],$value['expire'] , '/' );
            }
            //header
            foreach ($header as $key => $value) {
                header($key.':'.$value);
            }
            $this->_sended_header=true;
        }
        //body
        echo $body;
        $this->_finish=true;
    }
    /* maybe remove*/
    private function output($code,$header,$body){
        return $this->_flash($code, array(), $header, $body);
    }
    public static $codes = array(
        100=>'Continue',
        101=>'Switching Protocols',
        200=>'OK',
        201=>'Created',
        202=>'Accepted',
        203=>'Non-Authoritative Information',
        204=>'No Content',
        205=>'Reset Content',
        206=>'Partial Content',
        300=>'Multiple Choices',
        301=>'Moved Permanently',
        302=>'Moved Temporarily',
        303=>'See Other',
        304=>'Not Modified',
        305=>'Use Proxy',
        400=>'Bad Request',
        401=>'Unauthorized',
        402=>'Payment Required',
        403=>'Forbidden',
        404=>'Not Found',
        405=>'Method Not Allowed',
        406=>'Not Acceptable',
        407=>'Proxy Authentication Required',
        408=>'Request Time-out',
        409=>'Conflict',
        410=>'Gone',
        411=>'Length Required',
        412=>'Precondition Failed',
        413=>'Request Entity Too Large',
        414=>'Request-URI Too Large',
        415=>'Unsupported Media Type',
        500=>'Internal Server Error',
        501=>'Not Implemented',
        502=>'Bad Gateway',
        503=>'Service Unavailable',
        504=>'Gateway Time-out',
        505=>'HTTP Version not supported'
    );
    public static $mimes=array(
        'css' => 'text/css',
        'jpg' => 'image/jpeg',
        'png' => 'image/png',
        'htm' => 'text/html',
        'html' => 'text/html',
        'gif' => 'image/gif',
        'sql'=>'application/sql',
        'js' => 'application/x-javascript',
        'ttf'=>'font/ttf',
        'woff'=>'font/woff',
        'woff2'=>'font/woff2',
        'svg'=>'application/svg+xml',
        'otf'=>'font/otf',
        'csv'=>'text/csv',
        'txt'=>'text/txt',
    );  
}
?>