<?php
/*********************************************************************************
 *  扩展类库-PDO扩展类
 ***********************************************************************************/
class VPDO extends PDO{
    protected $_dsn;
    protected $_name;
    protected $_pass;
    protected $statement;
    protected $reconectCount = 0;   //重连次数
    protected $reconectTime;
    
    /**
     * pdo __construct
     */
    public function __construct($options=null) {
        $dbInfo = Xphp::$_config['DB_INFO'];
        $this->_dsn = $dbInfo['dbtype'] . ":host=" . $dbInfo['host'] . ";dbname=" . $dbInfo['dbname'];
        $this->_name = $dbInfo['user'];
        $this->_pass = $dbInfo['pass'];
        $this->pdo_connect($options);
        
        $this->reconectTime = time();
    }
    
    
     
    /**
     *pdo connect
     */
    private function pdo_connect($options) {
        try{
            parent::__construct($this->_dsn,$this->_name,$this->_pass,$options);
            parent::setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch(PDOException $e) {
            echo "Error message is ".$e->getMessage()."<br /> The Error in ".$e->getLine()." line <br /> This file dir on ".$e->getFile();
            exit();
        }
    }
    
    /**
     * 重连数据库
     * 如果重连次数不超过5次,可以重连
     * 如果大于5次,判断上次重连时间,如果超过了半个小时,重置次数,然后重连,如果在半个小时之内,不重连
     */
    private function reConnectDb(){
        if($this->reconectCount < 5){
            //如果重连次数不超过5次,可以重连
            $this->pdo_connect(null);
            sleep(1);
            $this->reconectCount++;
        }else{
            if(time() - $this->reconectTime > 300){
                //如果大于5次,判断上次重连时间,如果超过了半个小时,重置次数,然后重连
                $this->reconectCount = 0;
                $this->reconectTime = time();
                $this->reConnectDb();
            }else{
                return false;
            }
        }
        return true;
    }
    
    /**
     * sql query
     * @param string $sql
     * @param array $params
     */
    public function sqlQuery($sql, $params = array(), $flag, $fetch_style = PDO::FETCH_BOTH){
        parent::query("SET NAMES 'UTF8'");
        $this->statement = parent::prepare($sql);
        if(false === $this->statement){
            $result = $this->reConnectDb();
            if($result){
                return $this->sqlQuery($sql, $params, $flag, $fetch_style);
            }
        }
        if(!$this->statement){
            return false;
        }
        $executeResult = $this->statement->execute($params);
        if(!$flag){
            //如果不需要结果，只返回查询结果true or false
            return $executeResult;
        }
        $result = $this->statement->fetchAll($fetch_style);
        return $result;
    }
    
    /**
     * sql exec 返回受影响的行数
     * @param string $sql
     * @param array $params
     */
    public function newExec($sql, $params = array(), $flag){
        parent::query("SET NAMES 'UTF8'");
        $this->statement = parent::prepare($sql);
        if(false === $this->statement){
            $result = $this->reConnectDb();
            if($result){
                return $this->exec($sql, $params, $flag);
            }
        }
        if(!$this->statement){
            return false;
        }
        $executeResult = $this->statement->execute($params);
        if($executeResult){
            return $this->statement->rowCount();
        }
    }
    
}

?>