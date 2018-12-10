<?php
class QuickFileSystem{
    private $basePath;
    /**
     * file system constractor
     * @param string $basepath base path address
     */
    function __construct($basepath) {
        $this->basePath=$basepath; 
    }
    /**
     * change / to system directory seprator
     * @param string $file
     * @return string
     */
    private function _name($file) {
        if (DIRECTORY_SEPARATOR == '/')
            return $file;
        return str_replace ( '/', DIRECTORY_SEPARATOR, $file );
    }
    /**
     * read all file content
     * @param string $file file name
     * @return boolean|string file content or false
     */
    function read($file) {
        $file = $this->_name ( $file );
        $f = $this->basePath . $file;
        if (! file_exists ( $f )) {
            return false;
        }
        return file_get_contents ( $f );
    }
    /**
     * return file is exist or not
     * @param string $file file name
     * @return boolean 
     */
    function exists($file) {
        $file = $this->_name ( $file );
        $f = $this->basePath . $file;
        if (file_exists ( $f )) {
            return true;
        }
        return false;
    }
    /**
     * make directory
     * @param string $directory directory name
     * @return boolean
     */
    function md($directory) {
        $directory = $this->_name ( $directory );
        $d = $this->basePath . $directory;
        return mkdir ( $d );
    }
    /**
     * delete file or directory
     * @param string $sorce file or directory path
     * @return boolean
     */
    function del($sorce) {
        $sorce = $this->_name ( $sorce );
        $s = $this->basePath . $sorce;
        if (is_dir ( $s )) {
            $dh = opendir ( $s );
            if ($dh) {
                $file = readdir ( $dh );
                while ( $file ) {
                    if ($file != '.' && $file != '..') {
                        $nn = $s . DIRECTORY_SEPARATOR . $file;
                        if (is_dir ( $nn )) {
                            $this->del ( $sorce . DIRECTORY_SEPARATOR . $file );
                        } else {
                            @unlink ( $nn );
                        }
                    }
                    $file = readdir ( $dh );
                }
            }
            closedir ( $dh );
            return @rmdir ( $s );
        } else {
            return @unlink ( $s );
        }
    }
    /**
     * copy directory
     * @param string $sorce source path
     * @param string $path dist path
     */
    function copyDir($sorce, $path) {
        $path = $this->_name ( $path );
        $sorce = $this->_name ( $sorce );
        $s = $this->basePath . $sorce;
        $p = $this->basePath . $path;
        if (is_dir ( $s ) && is_dir ( $p ) && file_exists ( $p )) {
            $dh = opendir ( $s );
            if ($dh) {
                $file = readdir ( $dh );
                while ( $file ) {
                    if ($file != '.' && $file != '..') {
                        $nn = $s . DIRECTORY_SEPARATOR . $file;
                        if (is_dir ( $nn )) {
                            mkdir ( $p . DIRECTORY_SEPARATOR . $file );
                            $this->copyDir ( $sorce . DIRECTORY_SEPARATOR . $file, $path . DIRECTORY_SEPARATOR . $file );
                        } else {
                            copy ( $nn, $p . DIRECTORY_SEPARATOR . $file );
                        }
                    }
                    $file = readdir ( $dh );
                }
            }
            closedir ( $dh );
        }
    }
    /**
     * write or append data to file 
     * @param string $file file name
     * @param string $data data
     * @param boolean $append append or not
     */
    function write($file, $data, $append = false) {
        $file = $this->_name ( $file );
        $f = $this->basePath . $file;
        if ($append)
            file_put_contents ( $f, $data, FILE_APPEND );
            else {
                @unlink ( $f );
                file_put_contents ( $f, $data );
            }
    }
    /**
     * return type of file -> 'dir' or 'file'
     * @param string $file
     * @return string
     */
    function type($file){
        return @($this->basePath . $this->_name ( $file ) == 'file')? 'file' : 'dir';
    }
    /**
     * get list of file
     * @param string $dir path of directory
     * @param string $type file type
     * @param string $mode mod of files = 'add'|'file'|'dir'
     * @return mixed[]
     */
    function files($dir, $type = '', $mode = 'all') {
        $d = $this->basePath . $this->_name ( $dir );
        $back = array ();
        $dh = opendir ( $d );
        $size = - (strlen ( $type ) + 1);
        if ($dh) {
            $file = readdir ( $dh );
            while ( $file ) {
                if ($file != '.' && $file != '..' && ($type == '' || strtolower ( substr ( $file, $size ) ) == ('.' . $type))) {
                    if ($mode == 'file' && ($this->isFile ( $file, $dir ))) {
                        $back [] = $file;
                    } elseif ($mode == 'dir' && ($this->isDir ( $file, $dir ))) {
                        $back [] = $file;
                    } else {
                        $back [] = $file;
                    }
                }
                $file = readdir ( $dh );
            }
            closedir ( $dh );
        }
        return $back;
    }
    
}


?>