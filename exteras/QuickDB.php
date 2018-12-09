<?php 

class QuickDB {
    
    const RETURN_SUCCESSFUL=0;
    const RETURN_AFFECTED=1;
    const RETURN_INSERT_ID=2;
    /**
     * 
     * @var PDO connection to db
     */
    private $_link;
    
    public $table_perfix='';
    /**
     * 
     * @param string $dsn
     * @param string $user
     * @param string $pass
     * @param string $perfix
     */
    function __construct($dsn,$user,$pass,$perfix='') {
        $this->_link=new PDO($dsn,$user,$pass);
        $this->_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->_link->exec("set names utf8");
        $this->table_perfix=$perfix;
    }
    
    function execute($sql,$params=array(),$returnmode=self::RETURN_SUCCESSFUL){
        try{
            $stmt =$this->_link->prepare($sql);
            foreach ($params as $key => $value){
                $stmt->bindValue(':'.$key, $value);
            }
            $stmt->execute();
            if($returnmode==self::RETURN_SUCCESSFUL){
                return true;
            }elseif ($returnmode==self::RETURN_AFFECTED){
                return $stmt->rowCount();
            }else{
                return $this->_link->lastInsertId();
            }
        }catch (Exception $e){
            if($returnmode==QuickDB::RETURN_SUCCESSFUL){
                return false;
            }else{
                return 0;
            }
        }
    }
    function query($sql,$params=array()){
        
        try{
            $stmt =$this->_link->prepare($sql);
            foreach ($params as $key => $value){
                $stmt->bindValue(':'.$key, $value);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch (Exception $e){
            return [];
        }
    }
    
    /**
     * run insert command
     * @param string $table table name
     * @param mixed $data data of row
     * @return number inserted id
     */
    function insert($table, $data) {
        $sql="INSERT INTO " .  $this->table_perfix . $table .' ';
        $varname=array();
        $varvalue=array();
        
        foreach ($data as $key=>$val ) {
            $varname[] = '`' . $key . '`';
            $varvalue[] = ' :'.$key.' ';
        }
        $sql.='('. implode ( ', ', $varname) .' ) VALUES ( '.implode ( ', ', $varvalue).')';
        return $this->execute($sql,$data,self::RETURN_INSERT_ID);
    }
    /**
     * 
     * @param string $table
     * @param array $data
     * @param string $where
     * @param array $param
     * @return number
     */
    function update($table, $data, $where = '',$param=array()) {
        $vars=array();
        $params=array();
        
        
        foreach ( $data as $key => $val ) {
            $vars[] = '`' . $key . '`' . " = " . ' :_'.$key.' ';
            $params['_'.$key]=$val;
        }
        
        if(is_array ( $where )){
            $Wherestr = array ();
            foreach ( $where as $key => $val ) {
                $Wherestr [] = '`' . $key . '`' . " = " . ' :'.$key.' ';
                $params[$key]=$val;
            }
            $wh = implode ( ' AND ', $Wherestr );
        }elseif (is_numeric($where)){
            $wh=" `id` =  :id ";
            $params['id']=$where;
        }else{
            //string
            if (count($param)!=0){
                $wh=$where;
                foreach ( $param as $key => $val ) {
                    $params[$key]=$val;
                    
                }
            }else{
                $wh=$where;
            }
        }
        
        
        $sql = 'UPDATE ' . $this->table_perfix .$table  . ' SET ' . implode ( ', ', $vars );
        $sql .= ($wh != '') ? ' WHERE ' . $wh : '';
        
        return $this->execute($sql,$params,self::RETURN_AFFECTED);
    }
    
    function delete($table, $where = '',$param=array(), $limit = -1) {
        
        $params=array();
        
        if(is_array ( $where )){
            $Wherestr = array ();
            foreach ( $where as $key => $val ) {
                $Wherestr [] = '`' . $key . '`' . " = " . ' :'.$key.' ';
                $params[$key]=$val;
            }
            $wh = implode ( ' AND ', $Wherestr );
        }elseif (is_numeric($where)){
            $wh=" `id` =  :id ";
            $params['id']=$where;
        }else{
            //string
            if (count($param)!=0){
                $wh=$where;
                foreach ( $param as $key => $val ) {
                    $params[$key]=$val;
                    
                }
            }else{
                $wh=$where;
            }
        }
        
        
        $lim = ( $limit==-1) ? '' : ' LIMIT ' . $limit;
        $sql = "DELETE FROM " .  $this->table_perfix .$table  ;
        $sql .= ($wh != '') ? ' WHERE ' . $wh : '';
        $sql .=' '.$lim;
        
        
        return $this->execute($sql,$params,self::RETURN_AFFECTED);
    }
    
    function select($table,$where='',$param=array(), $offset = 0, $limit = 0, $by = '', $order = 'ASC'){
        
        $params=array();
        if(is_array ( $where )){
            $Wherestr = array ();
            foreach ( $where as $key => $val ) {
                $Wherestr [] = '`' . $key . '`' . " = " . ' :'.$key.' ';
                $params[$key]=$val;
            }
            $wh = implode ( ' AND ', $Wherestr );
        }elseif (is_numeric($where)){
            $wh=" `id` =  :id ";
            $params['id']=$where;
        }else{
            //string
            if (count($param)!=0){
                $wh=$where;
                foreach ( $param as $key => $val ) {
                    $params[$key]=$val;
                    
                }
            }else{
                $wh=$where;
            }
        }
        
        $sql = 'SELECT * FROM '.$this->table_perfix .$table;
        $sql .= ($wh != '') ? ' WHERE ' . $wh : '';
        
        if(!is_array($by)){
            if(is_string($by)){
                if($by != ''){
                    $by=explode ( ',', $by );
                }else{
                    $by=array();
                }
            }else{
                $by=array();
            }
        }
        
        if (count($by)>0) {
            $bya=array();
            for($i = 0; $i < count ( $by ); $i ++) {
                $bya [] = ' `' . $by [$i] . '` ' . $order . ' ';
            }
            $sql .=  ' ORDER BY '.implode ( ',', $bya );;
        }
        
        if ($limit != 0){
            $sql .= ' LIMIT ' . $offset . ' , ' . $limit;
        }
        return $this->query($sql,$params);
    }
    function get($table,$where='',$params=array(), $by = '', $order = 'ASC'){
        $a=$this->select($table,$where,$params,0,1,$by,$order);
        if(count($a)>0){
            return $a[0];
        }
        return null;
    }
    
    function count($table, $where='',$param=array()) {
        $params=array();
        if(is_array ( $where )){
            $Wherestr = array ();
            foreach ( $where as $key => $val ) {
                $Wherestr [] = '`' . $key . '`' . " = " . ' :'.$key.' ';
                $params[$key]=$val;
            }
            $wh = implode ( ' AND ', $Wherestr );
        }elseif (is_numeric($where)){
            $wh=" `id` =  :id ";
            $params['id']=$where;
        }else{
            //string
            if (count($param)!=0){
                $wh=$where;
                foreach ( $param as $key => $val ) {
                    $params[$key]=$val;
                    
                }
            }else{
                $wh=$where;
            }
        }
        
        $sql = 'SELECT count(*) FROM '.$this->table_perfix .$table;
        $sql .= ($wh != '') ? ' WHERE ' . $wh : '';
        
        
        try{
            $stmt =$this->_link->prepare($sql);
            foreach ($params as $key => $value){
                $stmt->bindValue(':'.$key, $value);
            }
            $stmt->execute();
            return $stmt->fetchColumn();
        }catch (Exception $e){
            return 0;
        }
    }
    
    function backUp($tables = '*', $fileName = '',$compression=true) {
        // only modify http://www.matteomattei.com/how-to-backup-mysql-data-and-schema-in-php/
        $lastMode=$this->_link->getAttribute(PDO::ATTR_ORACLE_NULLS);
        $this->_link->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_NATURAL );
        if($fileName=='')
            $fileName = '/backup-' . date('d-m-Y');
            
        if ($compression){
            $fileName .= '.sql.gz';
            $zp = gzopen(BASEPATH.$fileName, "a9");
        }else{
            $fileName .= '.sql';
            $handle = fopen(BASEPATH.$fileName,'a+');
        }
        //array of all database field types which just take numbers
        $numtypes=array('tinyint','smallint','mediumint','int','bigint','float','double','decimal','real');
        //get all of the tables
        if($tables=='*'){
            $tables=[];
            $pstm1 = $this->_link->query('SHOW TABLES');
            while ($row = $pstm1->fetch(PDO::FETCH_NUM)){
                $tables[] = $row[0];
            }
        }else{
            $tables = is_array($tables) ? $tables : explode(',',$tables);
        }
        //cycle through the table(s)
        foreach($tables as $table){
            $result = $this->_link->query('SELECT * FROM '.$table);
            $num_fields = $result->columnCount();
            $num_rows = $result->rowCount();
            $return="";
            //uncomment below if you want 'DROP TABLE IF EXISTS' displayed
            //$return.= 'DROP TABLE IF EXISTS `'.$table.'`;';
            //table structure
            $pstm2 = $this->_link->query('SHOW CREATE TABLE '.$table);
            $row2 = $pstm2->fetch(PDO::FETCH_NUM);
            $ifnotexists = str_replace('CREATE TABLE', 'CREATE TABLE IF NOT EXISTS', $row2[1]);
            $return.= "\n\n".$ifnotexists.";\n\n";
            if ($compression){
                gzwrite($zp, $return);
            }else{
                fwrite($handle,$return);
            }
            $return = "";
            //insert values
            if ($num_rows)
            {
                $return= 'INSERT INTO `'.$table.'` (';
                $pstm3 = $this->_link->query('SHOW COLUMNS FROM '.$table);
                $count = 0;
                $type = array();
                while ($rows = $pstm3->fetch(PDO::FETCH_NUM)){
                    if (stripos($rows[1], '(')){
                        $type[$table][] = stristr($rows[1], '(', true);
                    }else{
                        $type[$table][] = $rows[1];
                    }
                    $return.= '`'.$rows[0].'`';
                    $count++;
                    if ($count < ($pstm3->rowCount())){
                        $return.= ", ";
                    }
                }
                $return.= ')'.' VALUES';
                if ($compression){
                    gzwrite($zp, $return);
                }else{
                    fwrite($handle,$return);
                }
                $return = "";
            }
            $count =0;
            while($row = $result->fetch(PDO::FETCH_NUM)){
                $return= "\n(";
                for($j=0; $j<$num_fields; $j++){
                    if (isset($row[$j])){
                        //if number, take away "". else leave as string
                        if ((in_array($type[$table][$j], $numtypes)) && $row[$j]!==''){
                            $return.= $row[$j];
                        }else{
                            $return.= $this->_link->quote($row[$j]);
                        }
                    }else{
                        $return.= 'NULL';
                    }
                    if ($j<($num_fields-1)){
                        $return.= ',';
                    }
                }
                $count++;
                if ($count < ($result->rowCount())){
                    $return.= "),";
                }else{
                    $return.= ");";
                }
                if ($compression){
                    gzwrite($zp, $return);
                }else{
                    fwrite($handle,$return);
                }
                $return = "";
            }
            $return="\n\n-- ------------------------------------------------ \n\n";
            if ($compression){
                gzwrite($zp, $return);
            }else{
                fwrite($handle,$return);
            }
            $return = "";
        }
        if ($compression){
            gzclose($zp);
        }else{
            fclose($handle);
        }
        
        $this->_link->setAttribute(PDO::ATTR_ORACLE_NULLS,$lastMode);
    }
    
    
    
    
    
    
}







?>