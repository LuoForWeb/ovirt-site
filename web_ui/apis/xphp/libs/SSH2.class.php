<?php
/*********************************************************************************
 *  扩展类库-SSH2操作
 ***********************************************************************************/
class SSH2 {
    private $host;
    private $user;
    private $pass;
    private $port;
    private $conn = false;
    private $stream;
    private $stream_timeout = 120;
    private $log;
    private $lastLog;
    private $debug;
 
    /**
     * 构造函数
     * @param string $host  
     * @param string $user
     * @param string $pass
     * @param int $port
     * @param bool $debug
     * @return boolean
     */
    public function __construct($host, $user, $pass, $port, $debug = false){
        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        $this->port = $port;
        $this->debug = $debug;
        
        if($this->connect()){
            $this->authenticate();
        }
    }
    
    public function __destruct(){
        $this->disconnect();
    }
 
    /**
     * 是否连接成,成功返回资源,失败返回false
     * @return boolean
     */
    public function isConnected(){
        return ( boolean ) $this->conn;
    }
 
    public function __get($name){
        return $this->$name;
    }
    
    /**
     * 打印日志,如果是调试模式,就输入日志
     * @param unknown $msg
     */
    public function logAction($msg){
        if($this->debug){
            echo $msg . PHP_EOL;
        }
    }
 
    /**
     * 连接
     * @return Ambigous <boolean, resource>
     */
    public function connect(){
        if($this->conn = ssh2_connect($this->host, $this->port)){
            $this->logAction("Connecting to {$this->host} success ");
        }else{
            $this->logAction ( "Connection to {$this->host} failed " );
        }
        return $this->conn;
    }
 
    /**
     * 验证账号密码
     * @return SSH2
     */
    public function authenticate(){
        $this->logAction( "Authenticating to {$this->host}");
        if (ssh2_auth_password( $this->conn, $this->user, $this->pass )) {
            $this->logAction( "Authentication to {$this->host} success");
            return $this;
        }else{
            $this->logAction("Authentication to {$this->host} failed ");
            $this->conn = false;
        }
    }
 
    /**
     * 从本地传输一个文件到远程(不支持中文文件!!)
     * @param string $localFile     本地文件路径
     * @param string $remoteFile    远程文件路径
     * @param number $permision     远程文件权限
     * @return boolean
     */
    public function sendFile($localFile, $remoteFile, $permision = 0775) {
        if (!is_file($localFile)) return false;
        $this->logAction("Sending file $localFile to $remoteFile");
 
        if(ssh2_scp_send($this->conn, $localFile, $remoteFile, $permision)){
            $this->logAction("ssh2_scp_send sending file $localFile to $remoteFile succeeded");
            return true;
        }
        
        //如果上面失败了,再尝试用另外一种方式传输,这总方式和PHP配置最大 Allowed memory size(file_get_contents)有关系,
        //现在备份系统配置为128MB,第一种方式测试可以传送上2.6GB大小的东西,满足目前需要了.
        $sftp = ssh2_sftp($this->conn);
        $sftpStream = fopen('ssh2.sftp://' . $sftp . $remoteFile, 'w');
        if (!$sftpStream){
            $this->logAction("Could not open remote file: $remoteFile");
            return false;
        }
 
        $data_to_send = file_get_contents($localFile);
        if (fwrite($sftpStream, $data_to_send ) === false) {
            $this->logAction("Could not send data from file: $localFile.");
            fclose($sftpStream);
            return false;
        }
        fclose($sftpStream);
        $this->logAction("Sending file $localFile as $remoteFile succeeded");
        return true;
    }
 
    /**
     * 获取远程文件到本地
     * @param string $remoteFile    远程路径
     * @param string $localFile     本地路径(注意本地路径需要有nginx:nginx权限!!!!)
     * @return boolean
     */
    public function getFile($remoteFile, $localFile){
        $this->logAction("Receiving file $remoteFile as $localFile");
        if(ssh2_scp_recv($this->conn, $remoteFile, $localFile)){
            $this->logAction("Receiving file $remoteFile to $localFile succeeded");
            return true;
        }
        $this->logAction("Receiving file $remoteFile as $localFile failed");
        return false;
    }
 
    /**
     * 执行一个远程命令
     * @param string $cmd   
     * @param string $returnOutput
     */
    public function cmd($cmd, $returnOutput = false){
        if(false === $this->conn) return false;
        
        $this->logAction("Executing command $cmd");
        $this->stream = ssh2_exec($this->conn, $cmd);
 
        if (FALSE === $this->stream){
            $this->logAction("Unable to execute command $cmd");
            return false;
        }
        $this->logAction("$cmd was executed");
 
        stream_set_blocking($this->stream, true);
        stream_set_timeout($this->stream, $this->stream_timeout);
        $this->lastLog = stream_get_contents($this->stream);
 
        $this->logAction("$cmd output: {$this->lastLog}");
        fclose($this->stream);
        $this->log .= $this->lastLog . "\n";
        return ($returnOutput) ? $this->lastLog : $this;
    }
 
    /**
     * 连续执行命令脚本
     * @param string $cmds           命令数组
     * @return string $cmdOutAllInfo 返回所有输出信息
     */
    public function shellCmd($cmds = array()){
        if(false === $this->conn) return false;
        
        $this->logAction ( "Openning ssh2 shell" );
        $this->shellStream = ssh2_shell($this->conn);
 
        //这一段主要是输出并清除登录信息如Last login: Thu Jul  6 16:15:52 2017 from 192.168.8.8
        $out = '';
        usleep(100000);
        while($line = fgets($this->shellStream)){
            $out .= $line;
        }
        $this->logAction("login info: $out");
        
        $cmdOutAllInfo = "";
        //连续运行命令并获取输出
        foreach ($cmds as $cmd){
            $out = '';
            $this->logAction("Writing ssh2 shell command: $cmd");
            fwrite($this->shellStream, $cmd . PHP_EOL );
            usleep(100000);
            while($line = fgets($this->shellStream)){
                $out .= $line;
            }
            $this->logAction("ssh2 shell command $cmd output: $out");
            $cmdOutAllInfo .= $out . PHP_EOL;
        }
 
        $this->logAction("Closing shell stream");
        fclose($this->shellStream);
        
        return $cmdOutAllInfo;
    }
 
    public function getLastOutput(){
        return $this->lastLog;
    }
 
    public function getOutput(){
        return $this->log;
    }
 
    /**
     * 断开连接
     */
    public function disconnect(){
        if(false === $this->conn) return false;
        $this->logAction ( "Disconnecting from {$this->host}" );
        ssh2_exec($this->conn, 'exit');
        unset($this->conn);
    }
 
    /**
     * 检查文件是否存在
     * @param string $path  文件路径
     * @return boolean
     */
    public function fileExists($path) {
        $output = $this->cmd("[ -f $path ] && echo 1 || echo 0", true);
        return (bool)trim($output);
    }
    
    /**
     * 检查目录是否存在
     * @param string $path  目录路径
     * @return boolean
     */
    public function dirExists($path){
        $output = $this->cmd("[ -d $path ] && echo 1 || echo 0", true);
        return (bool)trim($output);
    }
}

// $test = new SSH2("192.168.8.102", "root", "12345678", 22, true);
// var_dump($test->isConnected());

// $t = $test->cmd("php -v", true);
// $t = $test->cmd("cd /root/test", true);
// $t = $test->cmd("mkdir tt", true);
// $t = $test->shellCmd(array("cd /root/test", "mkdir tt", "php -v"));
// $t = $test->shellCmd(array("mkdir tt"));
// $t = $test->dirExists("/root/test1/");
// $t = $test->sendFile("/var/www/html/vxe-backup-agent-4.0-3622.xe.6.5.0.and.7.x.x86_64.rpm",
//     "/root/test/vxe-backup-agent-4.0-3622.xe.6.5.0.and.7.x.x86_64.rpm");
// $t = $test->sendFile("/var/www/html/vinchin_enterprise-4.0.3576.tar.gz",
//     "/root/test/vinchin_enterprise-4.0.3576.tar.gz");
// $t = $test->sendFile("/var/www/html/vinchin_server_enterprise-4.0.3624.iso",
//     "/root/test/vinchin_server_enterprise-4.0.3624.iso");
// $t = $test->getFile("/root/test/vinchin_server_enterprise-4.0.3624.iso", 
//     "/var/www/html/test/vinchin_server_enterprise-4.0.3624.iso");
// var_dump($t);



