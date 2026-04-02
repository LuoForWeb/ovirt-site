<?php
/*********************************************************************************
 *  扩展类库-邮件发送类
***********************************************************************************/
require XPHP_PATH . 'libs/Mailer.class.php';
require XPHP_PATH . 'libs/Smtp.class.php';
require XPHP_PATH . 'libs/Pop3.class.php';
class Email {
    //phpmailer实例
    protected $mail;
    
    /**
     * 实例化phpmailer
     * @param string $host      
     * @param bool   $auth
     * @param string $username
     * @param string $password
     * @param int    $port
     * @param string $encryption
     */
    public function config($host, $port, $auth, $username, $password, $encryption){
        if(empty($password)){
            $auth = false;
        }
        $this->mail = new PHPMailer;
        $this->mail->isSMTP();                                      // Set mailer to use SMTP
        $this->mail->Host = $host;                                  // Specify main and backup SMTP servers
        $this->mail->SMTPAuth = $auth;                              // Enable SMTP authentication
        $this->mail->Username = $username;                          // SMTP username
        $this->mail->Password = $password;                          // SMTP password
        $this->mail->SMTPSecure = $encryption;                      // Enable TLS encryption, `ssl` also accepted
        $this->mail->Port = $port;                                  // TCP port to connect to
        
        $userArr = explode('@', $username);
        $this->mail->setFrom($username, $userArr[0]);   //设置发件人
        $this->mail->isHTML(true);                                  // Set email format to HTML
    }
    
    /**
     * 发送邮件
     * @param array  $recEmail      收件人Email地址(可以多个)
     * @param string $subject       主题
     * @param string $body          内容
     * @param array  $attachment    附件全路径(可以多个)
     */
    public function sendmail($recEmail, $subject, $body, $attachment){
        
        //添加收件人
        foreach ($recEmail as $email){
            $userArr = explode('@', $email);
            $this->mail->addAddress($email, $userArr[0]);     
        }
        
        //添加附件
        foreach ($attachment as $att){
            $this->mail->addAttachment($att);
        }
        
        //添加主题
        $this->mail->Subject = $subject;
        
        //添加内容
        $this->mail->Body = $body;
        if(!$this->mail->send()) {
//             var_dump($this->mail->ErrorInfo);
            return false;
//             echo 'Mailer Error: ' . $mail->ErrorInfo;
        } else {
            return true;
        }
        
    }
}
