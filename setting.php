<?php 
//only template
die("is only template");

$settings=[
    'base_url'=>'http://127.0.0.1',
    'modules'=>['fs','db','mvc','router'],
    'db'=>[
        'dsn'=>'mysql:host=127.0.0.1;dbname=quick',
        'user'=>'root',
        'pass'=>'',
        'perfix'=>'db_'
        
    ],
    'mvc'=>[
        'model_path'=>'/models',
        'controller_path'=>'/controllers',
        'view_path'=>'/views',
        'view_cache_path'=>'/views/cache'
    ]
];




?>