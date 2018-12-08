<?php
class FsModule{
    /**
     * 
     * @param Quick $app
     */
    public function __construct($app){
        $app->register('fs', 'QuickFileSystem',array(BASEPATH),'/quick/exteras/QuickFileSystem.php');
    } 
}
?>