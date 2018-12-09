<?php
class DbModule{
    /**
     * 
     * @param Quick $app
     */
    public function __construct($app){
        $conf=$app->setting('db');
        $app->register('db', 'QuickDB',array(
            $conf['dsn'],
            $conf['user'],
            $conf['pass'],
            $conf['perfix'],
        ),'/quick/exteras/QuickDB.php');
        
    }
}
?>