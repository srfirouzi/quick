<?php 


class QuickView_Scaner{
    private $source = '';
    private $index=0;
    private $compress = false;
    private static $SPACERS=array(' ',"\n","\r",',','(',')');
    private static $OPERATORS = array(
        '!' , '>' , '<' , '=' , '?' , '&' , '@' , '#' ,
        '+' , '-' , '*' , '/' , '%' , '~' , '^'
    );
    
    function __construct($source){
        $this->source=$source;
    }
    
    public function next() {
        while(true){
            if ($this->index == strlen($this->source)){ // EOF
                return null;
            }
            if (substr($this->source, $this->index, 2) == '{{') {
                $data = $this->getLexerCode();
                if(count($data)>0){
                    if ($data[0] == 'compress') {
                        $this->compress = true;
                    } elseif ($data[0] == 'decompress') {
                        $this->compress = false;
                    }else{
                        return array(
                            'type' => 'code',
                            'value' => $data
                        );
                    }
                }
                
            }else{
                $data = $this->getLexerText();
                if ($data == '') {
                    continue;
                } else {
                    return array(
                        'type' => 'text',
                        'value' => $data
                    );
                }
            }
        }
    }
    
    private function getLexerText() {
        $pos=$this->index;
        $start = strpos($this->source, '{{', $pos);
        if ($start === false) {
            $this->index = strlen($this->source);
            $substr = substr($this->source, $pos);
        }else{
            $len= $start - $this->index;
            $this->index = $start;
            $substr = substr($this->source, $pos, $len);
        }
        if ($this->compress) {
            $substr = str_replace(array("\r","\n"), ' ', $substr);
            $substr = preg_replace('/\s+/', ' ', $substr);
        }
        return $substr;
    }
    
    private function getLexerCode() {
        $out= array();
        $start =$this->index + 2;
        $end=strlen($this->source);

        for ($i = $start; $i < $end; $i++) {
            if (substr($this->source, $i, 2) == '}}') {
                $this->index = $i + 2;
                return $out;
            }
            $char = substr($this->source, $i, 1);
            if(in_array($char, self::$SPACERS)){
                continue;
            }elseif(in_array($char, self::$OPERATORS)){
                $data=$this->readOperator($i,$end);
                if($data['code']=='//'){
                    $i=$data['index'];
                    $data=$this->readComment($i,$end);
                    $i=$data['index'];
                }else{
                    $out[]=$data['code'];
                    $i=$data['index'];
                }
            }elseif ($char=='"'){
                $data=$this->readString($i,$end);
                $out[]=$data['code'];
                $i=$data['index'];
            }elseif (preg_match('/^[0-9]$/', $char)){
                $data=$this->readNumber($i,$end);
                $out[]=$data['code'];
                $i=$data['index'];
            }elseif (preg_match('/^[_a-zA-Z]$/', $char)){
                $data=$this->readVarable($i,$end);
                $out[]=$data['code'];
                $i=$data['index'];
            }
        }
        $this->index = strlen($this->source);
        return $out;
    }
    
    private function readComment($start,$end){
        $out=substr($this->source, $start, 1);
        for($i=$start+1;$i<$end;$i++){
            if (substr($this->source, $i, 2) == '}}') {
                return array(
                    'index'=>$i-1,
                    'code'=>$out
                );
            }
            
        }
        return array(
            'index'=>$end,
            'code'=>$out
        );
    }
    private function readVarable($start,$end){
        $out=substr($this->source, $start, 1);
        for($i=$start+1;$i<$end;$i++){
            $char = substr($this->source, $i, 1);
            if (preg_match('/^[_a-zA-Z0-9\\.]$/', $char)){
                $out.=$char;
            }else{
                return array(
                    'index'=>$i-1,
                    'code'=>$out
                );
            }
        }
        return array(
            'index'=>$end,
            'code'=>$out
        );
        
    }
    
    private function readNumber($start,$end){
        $out='';
        for($i=$start;$i<$end;$i++){
            $char = substr($this->source, $i, 1);
            if(is_numeric($out.$char)){
                $out.=$char;
            }else{
                return array(
                    'index'=>$i-1,
                    'code'=>$out
                );
            }
        }
        return array(
            'index'=>$end,
            'code'=>$out
        );
    }
    
    private function readOperator($start,$end){
        $out='';
        for($i=$start;$i<$end;$i++){
            $char = substr($this->source, $i, 1);
            if(in_array($char, self::$OPERATORS)){
                $out.=$char;
            }else{
                return array(
                    'index'=>$i-1,
                    'code'=>$out
                );
            }
        }
        return array(
            'index'=>$end,
            'code'=>$out
        );
    }
    
    private function readString($start,$end){
        $out='"';
        for($i=$start+1;$i<$end;$i++){
            $char = substr($this->source, $i, 1);
            if ($char == '"') {
                return array(
                    'index'=>$i,
                    'code'=>$out.'"'
                );
            } elseif($char == "\\") {
                $char2=substr($this->source, $i+1, 1);
                $i++;
                switch ($char2) {
                    case 'n':
                        $out.= "\n";
                        break;
                    case 'r':
                        $out.= "\r";
                        break;
                    case 't':
                        $out.= "\t";
                        break;
                    case '"':
                        $out.= '"';
                        break;
                    case "\\":
                        $out.= "\\";
                        break;
                }
            } elseif($char == "\n" or $char == "\r") {
                continue;
            } else {
                $out.= $char;
            }
        }
        return array(
            'index'=>$i,
            'code'=>$out.'"'
        );
    }
}

class QuickView_Parser{
    
    private $lexer=null;
    private $item;
    private static $CONDITION=array('>','>=','==','!=','<','<=','in');
    private static $OPERATION=array('+','-','*','/','%','~');
    
    
    
    
    public function __construct() {}
    public function parse($source) {
        $this->lexer = new QuickView_Scaner($source);
        
        $this->item=$this->lexer->next();
        $code = $this->parseBlock();
        $this->lexer=null;
        
        return $code;
    }
    private function parseBlock() {
        $out = array();
        $i=0;
        while (!is_null($this->item)) {
            if ($this->item['type'] == 'text') {
                if($i!=0 and !is_array($out[$i-1])){
                    $out[$i-1]=$out[$i-1] . $this->item['value'];
                }else{
                    $out[$i] = $this->item['value'];
                    $i++;
                }
            } else {
                $code = $this->item['value'][0];
                switch ($code) {
                    case 'if':
                        $out[$i] = $this->parseIf();
                        break;
                    case 'for':
                        $out[$i] = $this->parseFor();
                        break;
                    case 'macro':
                        $out[$i] = $this->parseMacro();
                        break;
                    case 'else':
                        return $out;
                        break;
                    case 'end':
                        return $out;
                        break;
                    default:
                        $out[$i] =$this->parseCommand();
                        break;
                }
                $i++;
            }
            $this->item=$this->lexer->next();
        }
        return $out;
    }
    private function parseCommand(){
        $iscommand=false;
        $code=$this->item['value'];
        $len=count($code);
        for($i=1;$i<$len;$i=$i+2){
            if(in_array($code[$i], self::$OPERATION)){
                $iscommand=true;
            }else{
                $iscommand=false;
                break;
            }
        }
        if($iscommand){
            array_unshift($code , 'calc');
        }
        return array(
            'code' => $code
        );
    }
    
    private function parseIf() {
        $out = array(
            'code' => $this->item['value'],
            'block' => array(),
            'else' => array()
        );
        $this->item=$this->lexer->next();
        $out['block'] = $this->parseBlock();
        if (!is_null($this->item)) {
            if ($this->item['value'][0] == 'else') {
                $this->item=$this->lexer->next();
                $out['else'] = $this->parseBlock();
            }
        }
        return $out;
    }
    private function parseFor() {
        $out = array(
            'code' => $this->item['value'],
            'block' => array(),
            'else' => array()
        );
        $this->item=$this->lexer->next();
        $out['block'] = $this->parseBlock();
        if (!is_null($this->item)) {
            if ($this->item['value'][0] == 'else') {
                $this->item=$this->lexer->next();
                $out['else'] = $this->parseBlock();
            }
        }
        return $out;
    }
    
    private function parseMacro() {
        $out = array(
            'code' => $this->item['value'],
            'block' => array()
        );
        $this->item=$this->lexer->next();
        $out['block'] = $this->parseBlock();
        return $out;
    }
}


class QuickView_Compiler{
    
    private $path = '';
    private $cache = null;
    private $parser=null;
    
    public function __construct($path, $cache = null) {
        $this->path      = $path;
        $this->cache     = $cache;
        
    }
    public function compile($source,$file='') {
        if(is_null($this->parser))
            $this->parser=new QuickView_Parser();
        return $this->parser->parse($source,$file);
    }
    public function compileSource($source) {
        return $this->compile($source);
    }
    public function compileFile($name) {
        $viewfile  = $this->path . DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, $name) . '.html';
        $cachefile = '';
        if ((!is_null($this->cache))) {
            $cachefile = $this->cache . DIRECTORY_SEPARATOR . $name . '.json';
            if (file_exists($cachefile)) {
                $data = file_get_contents($cachefile);
                if ($data !== false) {
                    $code = @json_decode($data, true);
                    if ($code !== false) {
                        if (filemtime($cachefile) >= filemtime($viewfile)) {
                            return $code;
                        }
                    }
                }
            }
        }
        if (file_exists($viewfile)) {
            $data = file_get_contents($viewfile);
            if ($data !== false) {
                $code = $this->compile($data, str_replace('.', DIRECTORY_SEPARATOR, $name) );
                if ($cachefile != '' and count($code) != 0) {
                    @file_put_contents($cachefile, json_encode($code));
                }
                return $code;
            }
        }
        return array();
    }
    
}


class QuickView_VM {
    private $bultins = array();
    private $templates = array(); //iner function
    private $parent;
    private $layout = '';
    
    public function __construct($parent) {
        $this->parent             = $parent;
        
        $this->bultins['calc']       = array($this, 'fun_calc');
        $this->bultins['=']       = array($this, 'fun_calc');
        
        $this->bultins['format']       = array($this, 'fun_format');
        
        
        $this->bultins['enum']       = array($this, 'fun_enum');
        
        $this->bultins['?']       = array($this, 'fun_smallif');
        
        $this->bultins['%']       = array($this, 'fun_url');
        
        $this->bultins['!']       = array($this, 'fun_html');
        
        $this->bultins['layout']  = array($this, 'fun_layout');
        $this->bultins['import'] = array($this, 'fun_import');
        $this->bultins['value']   = array($this, 'fun_value');
        
    }
    
    public function view($name, $env = array()) {
        $code         = $this->parent->compiler->compileFile($name);
        $this->layout = '';
        $content      = $this->runBlock($code, $env);
        if ($this->layout != '') {
            $code = $this->parent->compiler->compileFile($this->layout);
            if (count($code) != 0) {
                $layoutEnv=array();
                if(isset($env['layout']) and is_array($env['layout']))
                    $layoutEnv=$env['layout'];
                    $layoutEnv['content'] = $content;
                    $content              = $this->runBlock($code, $layoutEnv);
            }
        }
        return $content;
    }
    
    public function render($source, $env = array()) {
        $code    = $this->parent->compiler->compileSource($source);
        $content = $this->runBlock($code, $env);
        return $content;
    }
    
    private function runBlock(&$code, &$env = array()) {
        $out = '';
        for ($i = 0; $i < count($code); $i++) {
            if (is_array($code[$i])) {
                $fun = $code[$i]['code'][0];
                if ($fun == 'for') {
                    $out .= $this->runFor($code[$i], $env);
                } elseif ($fun == 'macro') {
                    $this->addTemplateMacro($code[$i], $env);
                } elseif ($fun == 'if') {
                    $out .= $this->runIf($code[$i], $env);
                } else {
                    $out .= $this->runItem($code[$i], $env);
                }
            } elseif (is_string($code[$i])) {
                $out .= $code[$i];
            }
        }
        return $out;
    }
    
    private function addTemplateMacro($item, &$vm) {
        if (count($item['code']) >= 2) {
            $this->templates[$item['code'][1]] = $item;
        }
    }
    
    private function runFor(&$item, &$env) {
        if (count($item['code']) == 4) {
            $var_name = $item['code'][1];
            $arr_name = $item['code'][3];
        } else {
            return '';
        }
        
        $arr = $this->getValue($arr_name, $env);
        if (is_array($arr)) {
            $arrayLen=count($arr);
            if (count($arr) == 0) {
                return $this->runBlock($item['else'], $env);
            } else {
                $out = '';
                $id = 0;
                $oldFor=null;
                if(isset($env['for']))
                    $oldFor=$env['for'];
                $env['for']=array();
                $env['for']['count']=$arrayLen;
                
                foreach ($arr as $ai => $av) {
                    $env[$var_name] = $av;
                    $env['for']['index']=$ai;
                    $env['for']['id']=$id;
                    if($id==0)
                        $env['for']['first']=true;
                    else
                        $env['for']['first']=false;
                    if($id+1==$arrayLen)
                        $env['for']['last']=true;
                    else
                        $env['for']['last']=false;
                    $out .= $this->runBlock($item['block'], $env);
                    $id++;
                }
                unset($var_name);
                if(is_null($oldFor))
                    unset($env['for']);
                else
                    $env['for']=$oldFor;
                return $out;
            }
        }
        return $this->runBlock($item['else'], $env);
    }
    private function runIf(&$item, &$env) {
        if (count($item['code']) == 2) {
            $var = $this->getValue($item['code'][1], $env);
            if ($var) {
                return $this->runBlock($item['block'], $env);
            } else {
                return $this->runBlock($item['else'], $env);
            }
        } elseif (count($item['code']) == 4) {
            $var1 = $this->getValue($item['code'][1], $env);
            $oper = $item['code'][2];
            $var2 = $this->getValue($item['code'][3], $env);
            $con  = false;
            switch ($oper) {
                case '>':
                    $con = ($var1 > $var2);
                    break;
                case '<':
                    $con = ($var1 < $var2);
                    break;
                case '==':
                    $con = ($var1 == $var2);
                    break;
                case '!=':
                    $con = ($var1 != $var2);
                    break;
                case '>=':
                    $con = ($var1 >= $var2);
                    break;
                case '<=':
                    $con = ($var1 <= $var2);
                    break;
                case 'in':
                    $con = in_array($var1, $var2);
                    break;
            }
            if ($con) {
                return $this->runBlock($item['block'], $env);
            } else {
                return $this->runBlock($item['else'], $env);
            }
        } else {
            return '';
        }
    }
    
    private function runItem(&$item, &$env) {
        $fun = $item['code'][0];
        if (isset($this->parent->functions[$fun])) {
            return $this->runFunction($item, $env);
        } elseif (isset($this->bultins[$fun])) {
            return $this->runBultin($item, $env);
        } elseif (isset($this->templates[$fun])) {
            return $this->runTemplate($item, $env);
        } elseif ($fun == 'layout') {
            return $this->setlayout($item, $env);
        } else {
            $out = array();
            for ($i = 0; $i < count($item['code']); $i++) {
                $a = $this->getValue($item['code'][$i], $env);
                if (!is_array($a))
                    $out[] = $a;
            }
            return implode(' ', $out);
        }
    }
    
    private function runBultin(&$item, &$env) {
        $par = array();
        $par[]=&$env;
        for ($i = 1; $i < count($item['code']); $i++) {
            $par[] = $this->getValue($item['code'][$i], $env);
        }
        $fun = $this->bultins[$item['code'][0]];
        if (is_callable($fun)){
            return call_user_func_array($fun, $par);
        }
        return '';
    }
    
    private function runFunction(&$item, &$env) {
        $par = array();
        for ($i = 1; $i < count($item['code']); $i++) {
            $par[] = $this->getValue($item['code'][$i], $env);
        }
        $fun = $this->parent->functions[$item['code'][0]];
        if (is_callable($fun))
            return call_user_func_array($fun, $par);
            return '';
    }
    
    private function runTemplate(&$item, &$env) {
        $func = $this->templates[$item['code'][0]];
        if ((count($func['code']) - 1) < count($item['code'])) {
            return '';
        }
        $par = array();
        for ($i = 1; $i < count($item['code']); $i++) {
            $par[$func['code'][$i + 1]] = $this->getValue($item['code'][$i], $env);
        }
        return $this->runBlock($func['block'], $par);
    }
    
    private function getValue($name, $envs) {
        $first = substr($name, 0, 1);
        if ($first == '"') {
            return substr($name, 1, -1);
        }elseif(is_numeric($name)){
            return $name+0;
        }
        if ($name == 'true') {
            return true;
        }
        if ($name == 'false') {
            return false;
        }
        
        if (!preg_match('/[a-zA-Z][a-zA-Z0-9\\.]*/', $name)) {
            return $name;//for operator
        }
        $parts  = explode('.', $name);
        $parent = $envs;
        for ($i = 0; $i < count($parts); $i++) {
            if (isset($parent[$parts[$i]])) {
                $parent = $parent[$parts[$i]];
            } else {
                return '';
            }
        }
        return $parent;
    }
    
    public function fun_url($env,$a = '') {
        return urlencode($a);
    }
    
    public function fun_html($env,$a = '') {
        return htmlentities($a, ENT_QUOTES, "UTF-8");
    }
    public function fun_enum($env,$id = '') {
        $code=func_get_args();
        $cycle=$id % (count($code)-2);
        return $code[2+$cycle];
    }
    /*
     * format start by %
     * %% is %
     * %s type pure string
     * %h html encode
     * %u url encode
     */
    public function fun_format($env,$format = '') {
        $code=func_get_args();
        $formatlen=strlen($format);
        $datacount=count($code)-2;
        $out='';
        $dataid=0;
        for($i=0;$i<$formatlen;$i++){
            $char=substr($format, $i, 1);
            if($char=='%'){
                if($i+1<$formatlen){
                    $char2=substr($format, $i+1, 1);
                    $data='';
                    if($dataid<$datacount){
                        if(!is_array($code[$dataid+2]))
                            $data=$code[$dataid+2];
                    }
                    switch ($char2) {
                        case '%':
                            $out.='%';
                            break;
                        case 's':
                            $out.=$data;
                            $dataid++;
                            break;
                        case 'h':
                            $out.=htmlentities($data, ENT_QUOTES, "UTF-8");
                            $dataid++;
                            break;
                        case 'u':
                            $out.=urlencode($data);
                            $dataid++;
                            break;
                    }
                    $i++;
                }
            }else{
                $out.=$char;
            }
        }
        return $out;
    }
    
    public function fun_calc($env,$a = '') {
        
        
        $code=func_get_args();
        $len=count($code);
        $out = '';
        for ($i = 1; $i < $len ; $i++) {
            $a = $code[$i].'';
            switch ($a) {
                case '+':
                    if(($i+1) < $len){
                        $out= $out + $code[$i+1];
                        $i++;
                    }
                    break;
                case '-':
                    if(($i+1) < $len){
                        $out= $out - $code[$i+1];
                        $i++;
                    }
                    break;
                case '*':
                    if(($i+1) < $len){
                        $out= $out * $code[$i+1];
                        $i++;
                    }
                    break;
                case '/':
                    if(($i+1) < $len){
                        $out= $out / $code[$i+1];
                        $i++;
                    }
                    break;
                case '~':
                    if(($i+1) < $len){
                        $out= $out . $code[$i+1];
                        $i++;
                    }
                    break;
                default:
                    if(! is_array($a))
                        $out.= $a;
                        break;
            }
        }
        return $out;
    }
    
    public function fun_smallif($env,$con = false, $is = '', $els = '') {
        if ($con) {
            return $is;
        } else {
            return $els;
        }
    }
    public function fun_layout($env,$layoutname = '') {
        $this->layout = $layoutname;
    }
    
    public function fun_value($env,$obj, $value = '', $def = '') {
        if (isset($obj[$value]))
            return $obj[$value];
            return $def;
    }
    
    public function fun_import($env,$obj) {
        $code = $this->parent->compiler->compileFile($obj);
        return $this->runBlock($code, $env);
    }
}

class QuickView {
    public $functions = array();
    public $compiler;
    
    public function __construct($path = '', $cache = null) {
        $this->compiler = new QuickView_Compiler($path, $cache);
    }
    
    public function view($name, $env = array()) {
        $vm = new QuickView_VM($this);
        return $vm->view($name, $env);
    }
    
    public function reander($source, $env) {
        $vm = new QuickView_VM($this);
        return $vm->render($source, $env);
    }
    
    public function addFunction($name, $func) {
        $this->functions[$name] = $func;
    }
}




?>