<?php
class DbModule{
    /**
     * 
     * @param Quick $app
     */
    public function __construct($app){
        $app->register('db', 'QuickDB',array(
            $app->setting('db.dsn'),
            $app->setting('db.user'),
            $app->setting('db.pass'),
            $app->setting('db.perfix')
        ),'/quick/exteras/QuickDB.php');
        
    }
}
?>