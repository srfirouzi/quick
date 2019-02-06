# quick micro framework (alpha Version )

fast,dynamic,simplex,module base micro framework for php

---
# install

only git clone on root project and update summodule

```bash
git clone https://github.com/srfirouzi/quick
cd quick
git submodule init
git submodule update

```

,tham config .htaccess by this format, and add setting.php in project root

## .htaccess

```
Options -Indexes

RewriteEngine On
RewriteRule ^(.*)$ index.php?request_path=$0 [QSA,L]
```
(by this config for .htaccess,can route all element exist and don't exist,you must route static file) 

other form

```
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?request_path=$0 [QSA,L]
```
(by thiss config for .htaccess,only don't exist element route,you must define other config to securty)

- request_path is $_GET element content request path,can change it in setting.php

## setting.php

all your micro framework config set in

```php
$settings=[
    'base.url'=>'http://127.0.0.1', // your site url
    'base.rewrite.url.name'=>'request_path', //define in htaccess for send request path
    'base.modules'=>['fs','db','mvc','router'], //module must be loaded
    
    // db module config
    'db.dsn'=>'mysql:host=127.0.0.1;dbname=quick', 
    'db.user'=>'root',
    'db.pass'=>'',
    'db.perfix'=>'db_',

	// mvc module config
    'mvc.model_path'=>'/models',
    'mvc.controller.path'=>'/controllers',
    'mvc.view.path'=>'/views',
    'mvc.view.cache.path'=>'/views/cache'

];
```

important part of setting is 'base.module' ,array of modules must be loaded,this setting only used, time of make new instance of core(Quick class)

## load_file("path")

for load php file ,path is  relative from root of project

(in quick microframework ,all path is  relative from root of project)

---
# Quick

main class of quick framework, this is a dynamic element for loaded module part in it,can add new method and property to instance of this class

first must be make new instance

```php
include 'quick.php/Quick.php';

$app=new Quick();

//then change it

```
you can redefine setting,if not define part of setting ,core use setting.php (in quick directory have template of setting file)

```php
include 'quick.php/Quick.php';

$app=new Quick([
	'base.url'=>'http://srfirouzi.github.io',
   'base.modules'=>['router'],
]);


```


# static parts

main part of core never change it

- setting($name,$def='')

get setting of instance (if don't found,return $def)

- set_setting($name,$value='')

set setting on instance

- add_function($name,$func)

add new method to instance of core 

( to manage of dynamic property, add function by name '__get' by function(string $propertyName) return object style ,and return null for manage by other __get function  )

- register($name, $class,$params = [],$loadpath='')

add new property to instance of core 

(only used time make new instance for object)

```php
include 'quick.php/Quick.php';

$app=new Quick();

// setting
$app->set_setting('my.name','seyed rahim firouzi');
$app->res->write('Hello ,'. $app->setting('my.name','unknow'));

//add function
// can add any callable element like [$obj,'method'] for call $obj->method
$app->add_function('hello',function($name){return 'Hello ,'.$name ;});
$app->res->write( $app->hello('seyed rahim firouzi'));

//add element for core
$app->register('pdo', 'PDO',[$dsn,$user,$pass]);
$app->pdo->exec("INSERT INTO User (name, pass) VALUES ('srf', '123')");

//dynamic peroperty
$app->add_function('__get',	
	function($name){
		if($name=='book')
			return new Book()
		elseif($name=='ali')
			return ['name'=>'seyed ali',age=>2];
		else
			return null
	}
);

$app->res->write($app->book->title	);
$app->res->write($app->ali['age']);								

```
# dynamic part

- not_found

this dynamoc method by seted first time,used for 404 error

## req

the instance of quick/exteras/QuickRequest, for request mapping

### property

- method = request http method

- POST = _POST elements is stripslashes

- GET = _GET elements is stripslashes

- COOKIE = _COOKIE elements is stripslashes

- FILE = _FILES

- ip = client ip

- agent = client web browser agent

- secure = is https

- path = request file path

### method

- server($key,$def='')

get _SERVER data if not found return $def

- setParameter($param)

set param array ,url router module used

- get($key,$def='')

get _GET data if not found return $def

- post($key,$def='')

get _POST data if not found return $def

- postJson($key,$def=array())

get _POST data and decodeJson if not found return $def

- cookie($key,$def='')

get _COOKIE data if not found return $def

- param($key,$def='')

get param of router data if not found return $def

## res

the instance of quick/exteras/QuickResponse, for response mapping

### property

- static codes = http code map to description
- static mimes = type map to mime

### method

- header($key,$value) 

write http header to output

- write($content)

write content to output

- writeJson($table)

write content to output after encode json

- setOutCode($number=200){

set http response code

- url($path='')

get absolate url by relative url 

- redirect($path='')

redirect page

- file($file,$name='')

send file to output http 1.0 mode

- flash()

send data from cache to client

- cookie($name, $value, $expire = 0)

send cookie for client

- output($code,$header,$body)

flash data by code and  header and content
    
 

---


# module

all part of quick micro framework,design modular 

module exist in 'quick/modules' directory, by uppercase first character and add 'Module'.for example [router => RouterModule]

,must of module used extera object in 'quick/exteras' directory,and low depandacy lib

## router

router tools for route url to function

add two function to core 

- route($path,$func,$method='GET|POST')
- run($path=null)

### route(pattern,function,method='GET|POS')

* pattern of request path
* function to callback
* method of request sprate by | (example 'GET|POST')

pattern make by different part separate by /,every part maybe start by special character,special character is:

1. ':' must exist and save data in request parameters ( :name = define part must exist and save in param['name']
2. '!' mybe exist and save data in request parameters ( !name = define part mybe exist and save in param['name']
3. '*' other parts of path mybe exist or not
4. other  part is static

#### example
```
pattern  "/hello/:woman/!and/!man/!other/*"
 

request path=/hello/sun

param=array(
	'woman'=>'sun'
)

request path=/hello/ladies/and/gentleman
param=array(
	'woman'=>'ladies','and'=>'and','man'=>'gentleman'
)


request path=/hello/ata/or/micro/for/ever/and/everywhere
param=array(
	'womman'=>'ata','and'=>'or','man'=>'micro','other'=>'for','*'=>'/ever/and/everywhere'
)

```

### run

execue router selector

```php
$app->run('/my/path');
```


```php

include 'quick/Quick.php';

$app=new Quick();

$app->route('/hello/:name', function($req,$res,$app){
    $name=$req->param('name','world');
    $res->write('hello '.$name);
});
    
$app->run();

```

## fs

fs is module for work by file ,in real only add instanse of 'quick/exteras/QuickFileSystem' by fs name for core


## methods

- read($file)

read all content of file

- exists($file) 

check file exit or no

- md($directory)

create new directore

- del($sorce)

dele directory or file

- copyDir($sorce, $path)

copy direcotry

- write($file, $data, $append = false)

write data of file 

- type($file)

check is file type is directory or file ,by relatve parent

- files($dir, $type = '', $mode = 'all')

return list of directory by type=txt|html|... and mode=all|dir|file

```php
include 'quick.php/Quick.php';

$app=new Quick();

$source =$app->fs->read("/quick/Quick.php");

$app->res->write($source);

```

## db

db is module for work by database ,in real only add instanse of 'quick/exteras/QuickDB' by db name for core

setting

```
'db.dsn'   // dsn of database

'db.user'  // user for connection

'db.pass'  // pass for 

'db.perfix' => 'db_', perfix for tables

```
## methods

- execute($sql,$params=array(),$returnmode=self::RETURN_SUCCESSFUL)

execute sql by parameter and return data by returnmode data, parameter is pdo format

- query($sql,$params=array())

query sql by parameter and return array of data(item is associative array )

- insert($table, $data)

insert new element in table (table name without perfix part)

- update($table, $data, $where = '',$param=array())

update data of element in table by where condition (where have to part $whrer and param *). return insert id


- delete($table, $where = '',$param=array(), $limit = -1) 

delete of element in table by where condition (where have to part $whrer and param *).return count of effected element

- select($table,$where='',$param=array(), $offset = 0, $limit = 0, $by = '', $order = 'ASC')

return array of element in table by where condition (where have to part $whrer and param *) (item is associative array )

- get($table,$where='',$params=array(), $by = '', $order = 'ASC')

return first element in table by where condition (where have to part $whrer and param *) (item is associative array )

- count($table, $where='',$param=array())

return count of element in table by where condition (where have to part $whrer and param *)
 
- backUp($tables = '*', $fileName = '',$compression=true)

get backup from db $table is array of table name or string name of table seprate by ',' or all by * . table name with perfix part



* if where is number condtion is  id=number
* if where is array  condtion is  i1=v1 and i2=v2 and ...
* if where is string condtion is  where by parameter of param

```php

include 'quick.php/Quick.php';

$app=new Quick();

//use on user table
// insert new item and get id
$id=$app->db->insert('user',['name'=>'ali','pass'=>'123]);

//change name to 'seyed ali' if id = $id
$app->db->update('user',['name'=>'seyed ali'],$id);

// delete one element by name='rahim' and pass='456'
$app->db->delete('user','name = :a and pass :=b',['a':'rahim','b':'456'],1);

// list of all element by name 'ali' sort by pass
$out=$app->select('user',['name'=>'ali'],[],0,0,'pass');

```

## mvc

simple mvc engine by,mvc contain three part .controller, model and view

add multi function to core

- controller(controllername)
- controllerAccess(fun)

- model(name)

- add_view_function($name,$func)
- view(name,data,layoutdata)



## controller

### controller(controllername)

return controller ,controller define in controller path,controller path define in setting 'mvc.controller.path'  in constractor of quick engine . Controller class equal uppercase of controller name concat 'Controller',and extend /quick/module/MvcModule Controller class. every call this function make new object of Controller

#### /contollers/HelloController.php

```php
class HelloController extends Controller{
    function say(){
        // use active app
        $param=$this->req->post("param");
        retrn "hello world".$param;
    }
}
```
```php
$controller=$app->controller('hello');
echo $controller->say();
```


### controllerAccess(fun)

define function to access method for controller,

```php

$app->controllerAccess(funcction($app,$name){
    if($name=='admin')
        return false;//dont access
    return true;

})

$controller=$app->controller('admin');
if(is_null($controller))
    echo 'dont access';
```


for standardization request/response function API,better define controller function this format

```php
public function method_name($req,$res){
    return 'writable string in response object or "" ';
}
```

## model

### model(name)

return model ,model define in model path,modle path define in setting 'mvc.model.path'  in constractor of quick engine . model class equal uppercase of model name concat 'Model',and extend /quick/modules/MvcModule  Model class. every call this function return on object,con't make new object



/models/HelloModel.php

```php
class HelloModel extends Mode{
    function say(){
        retrn "hello model";
    }
}
```


```php
$model=$app->model('hello');
// is equal $model=$app->hello;
echo $model->say();
```

## view

view is QuickView template engine

### add_view_function($name,$func)

add function to quick view module 

### view(name,data,layoutdata)

reander view by data and return this,

---

# view template language


### help
quickview code in side 'tagstart' ,'tagend' and space is spaser 
default tagstart and tagend equal '{{','}}'

```
{{ func a b c }}



```
### static value
to use static value define this mode

string = start and end by " and use \n \r \t \" \\

boolean = true , false

number = write number
 
### varable
to access value only write name
to access array item use "." 

(if code not command print value)
```
{{var}}
{{array.item}}
```
### if
if have two style,
first :with secend part
```
{{if a == 12}}
    <html>
{{else}}
    <xml>
{{end}}
```
second :without second part
```
{{if a}}
    <html>
{{else}}
    <xml>
{{end}}
```

### for 
loop in template by list of elements , for_index equal by index of elements or key

```
{{for v in array}}
	{{for.index}} => PRINT {{V}}
{{else}}
	ARRAY IS EMPTY
{{end}}
```
### call function
function define in php and by template function command and used by name and parameter after name sprate by space
```
{{func p1 p2 }}

{{= 5 + 7}}
{{= 5 - 7}}
{{= 5 - 7}}
{{= 5 - 7}}
{{"hello " ~ "world"}}
{{format "%s:%s:%s" h m s}}
```
#### perdefine function

1. '!' => html coding
2. '=' by '+','-','*','/' ,'~' => math function for 2 part (~ for join string)
3. '?' => inline if p1 is condition p2 ture return p3 false return 
4. 'layout' =>define layout
5. 'value' =>equal in . in varable name ,but dynamic form
6. 'compress' =>after this command html compree(meta function,run in compile time)
7. 'decompree' =>after this command html don't compress(meta function,run is compile time)
8. 'include' => add other template in this template 
9. 'format' => used format by %s=value ,%h html encode value , %% for % ,%u url encode value 
10. '//' => comment

```
{{! '<' }}<br/> 
{{= 2+2}},{{= 2-2}},{{= 2*2}},{{= 2/2}} ,{{= 'hellow ' ~ 'world' }} <br/>

{{? true 'true text' 'false text'}}
{{value myArray 0 }}

{{layout 'main'}}<br/> 
{{compress}} 
a       b      c       d      e
{{decompree}} 
{{include 'functions'}}


```


### define function
```
{{macro fun p1 p2 p3}}
	body of function and use only parameters(p1,p2,p3)

{{end }}

{{fun 1 2 3}}
```

---
# TODO

- [ ] make other repo for testing
- [ ] write and complate Document and wiki




- sorry ,my language is not good





