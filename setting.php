<?php 
//only template
die("is only template");

$settings=[
    'base.url'=>'http://127.0.0.1',
    'base.rewrite.url.name'=>'request_path', //define in htaccess for send request path
    'base.modules'=>['fs','db','mvc','router'],
    
    'db.dsn'=>'mysql:host=127.0.0.1;dbname=quick',
    'db.user'=>'root',
    'db.pass'=>'',
    'db.perfix'=>'db_',

    'mvc.model_path'=>'/models',
    'mvc.controller.path'=>'/controllers',
    'mvc.view.path'=>'/views',
    'mvc.view.cache.path'=>'/views/cache'

];




?>