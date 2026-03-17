<?php
// phpcs:ignoreFile -- 框架类
/*********************************************************************************
 *  扩展类库-邮件发送类
 ***********************************************************************************/

namespace xphp;

use PHPMailer\PHPMailer\PHPMailer;
use xphp\db\Op;

class Email
{
    //phpmailer实例
    protected $mail;
    protected $debugLog = '';
    protected $isSsl = false;

    /**
     * 实例化phpmailer
     * @param string    $host 邮件服务器
     * @param int       $port 端口号
     * @param bool      $auth  是否auth认证
     * @param string    $username 发件人邮件
     * @param string    $password 发件人密码
     * @param string    $encryption 加密方式 无、ssl、tls
     * @param bool      $isSsl ssl需要校验证书
     * @return void
     */
    public function config(string $host, int $port, bool $auth, string $username, string $password, string $encryption, bool $isSsl = false)
    {
        if (empty($password)) {
            $auth = false;
        }
        $this->mail = new PHPMailer();
        $this->mail->isSMTP();                                      // Set mailer to use SMTP
        $this->mail->Host = $host;                                  // Specify main and backup SMTP servers
        $this->mail->SMTPAuth = $auth;                              // Enable SMTP authentication
        $this->mail->Username = $username;                          // SMTP username
        $this->mail->Password = $password;                          // SMTP password
        $this->mail->SMTPSecure = $encryption;                      // Enable TLS encryption, `ssl` also accepted
        $this->mail->Port = $port;                                  // TCP port to connect to
        $this->mail->CharSet = 'UTF-8';
        $this->mail->Timeout = 10; // 设置超时时间为 10 秒

        // 设置自定义的 edebug 函数来捕获调试信息
        $this->mail->edebug = function ($msg) {
            $this->debugLog .= $msg . "\n";
        };

        $this->isSsl = $isSsl;

        $userArr = explode('@', $username);
        $name = $userArr[0];
        //英文版单独设置发件邮箱名
        $systemInfo = getConfig('app')['SYSTEM_INFO'];
        $enterprise = getConfig('app')['ENTERPRISE'];
        if ($systemInfo['enterprise'] == $enterprise['enterprise_en']) {
            $name = "Vinchin";
        }
        $this->mail->setFrom($username, $name);   //设置发件人
        $this->mail->isHTML(true);                                  // Set email format to HTML
    }

    /**
     * 发送邮件
     * @param array $recEmail 收件人Email地址(可以多个)
     * @param string $subject 主题
     * @param string $body 内容
     * @param array $attachment 附件全路径(可以多个)
     * @param array $cc 添加抄送(可以多个)
     * @param array $innerContent 添加内联图片附件
     * @param int  $back back error info
     */
    public function sendmail(array $recEmail, string $subject, string $body, array $attachment = [], array $cc = [], $innerContent = [], $back = 0)
    {
        $localIp = $this->getBaseUrl();
        $allMsg = [];
        foreach ($localIp as $item) {
            $result = $this->sendMails($recEmail, $subject, $body, $attachment, $cc, $innerContent, $item, $back);
            if (($back && $result['code'] == 0) || (!$back && !empty($result))) {
                // 发送成功
                return $result;
            }
            $back && $allMsg[] = $result['info'];
        }
        // 全部失败
        $msg = "All local export IPs are unable to send SMTP emails";
        return $back ? ['code' => -1, 'msg' => $msg, 'error' => $msg, 'info' => implode("\n", $allMsg)] : false;
    }

    /**
     * 发送邮件
     * @param array $recEmail 收件人Email地址(可以多个)
     * @param string $subject 主题
     * @param string $body 内容
     * @param array $attachment 附件全路径(可以多个)
     * @param array $cc 添加抄送(可以多个)
     * @param array $innerContent 添加内联图片附件
     * @param string  $localIp 出口ip地址
     * @param int  $back back error info
     */
    protected function sendMails(array $recEmail, string $subject, string $body, array $attachment = [], array $cc = [], $innerContent = [], $localIp = '', $back = 0)
    {
        $this->debugLog = '';
        // 关键：克隆原始配置，避免污染
        $mail = clone $this->mail;
        $mail->clearAllRecipients();   // 清空 To/CC/BCC
        $mail->clearAttachments();     // 清空附件
        $mail->clearCustomHeaders();   // 清自定义头
        $mail->Body = '';              // 重置 body
        $mail->Subject = '';           // 重置 subject
        ob_start(); // 开启输出缓冲区
        try {
            //添加收件人
            if (!empty($cc)) {
                foreach ($cc as $eachCc) {
                    $userArr = explode('@', $eachCc);
                    $mail->addCC($eachCc, $userArr[0]);
                }
            }

            //添加收件人
            foreach ($recEmail as $email) {
                $userArr = explode('@', $email);
                $mail->addAddress($email, $userArr[0]);
            }

            //添加附件
            foreach ($attachment as $att) {
                $mail->addAttachment($att);
            }

            //添加内联图片附件
            foreach ($innerContent as $i){
                $mail->addEmbeddedImage($i['img_src'], $i['id']);
            }

            //添加主题
            $mail->Subject = $subject;

            //添加内容
            $mail->Body = $body;
            if ($back) {
                $mail->SMTPDebug = 2; // 调试级别 2 显示客户端与服务器之间的交互详情
            }

            $options = [];
            if (empty($this->isSsl)) {
                // 忽略证书
                $options['ssl'] = [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ];
            }

            if (!empty($localIp) && filter_var($localIp, FILTER_VALIDATE_IP)) {
                // 创建 socket 上下文，绑定本地地址
                $options['socket'] = [
                    'bindto' => $localIp . ':0'  // :0 表示随机端口
                ];
            }
            if (!empty($options)) {
                $mail->SMTPOptions = $options;
            }

            if (!$mail->send()) {
                (new Op())->writeLog("SMTP failed via {$localIp}: " . $mail->ErrorInfo);
                $output = ob_get_clean(); // 获取当前缓冲区内容并关闭缓冲
                $errorInfo = $this->getError($output);
                return $back ? ['code' => -1, 'msg' => $mail->ErrorInfo, 'error' => $errorInfo, 'info' =>  $this->debugLog . "\n" . $output] : false;
            } else {
                $output = ob_get_clean(); // 获取当前缓冲区内容并关闭缓冲
                $errorInfo = $this->getError($output);
                return $back ? ['code' => 0, 'msg' => 'success', 'error' => $errorInfo, 'info' => "SMTP succeeded via {$localIp}: " .  $this->debugLog . "\n" . $output] : true;
            }
        } catch (\Exception $e) {
            $output = ob_get_clean(); // 确保在异常情况下也获取缓冲区内容
            $errorInfo = $this->getError($output);
            (new Op())->writeLog($e->getMessage());
            return $back ? ['code' => -1, 'msg' => $e->getMessage(), 'error' => $errorInfo, 'info' => "SMTP Exception via {$localIp}: " .  $output] : false;
        }
    }

    /**
     * 获取备份系统主节点所有的ip地址
     */
    private function getBaseUrl()
    {

        $ipList = dbSelect('SELECT bnn.ip FROM bd_node bn JOIN bd_node_network bnn ON bn.node_uuid = bnn.node_uuid
                                    WHERE bn.node_type = 1 AND bnn.type = 1 order by bnn.network_order asc');
        $ips = array_column($ipList, 'ip');
        // 过滤有效 IP
        return array_filter($ips, fn($ip) => filter_var($ip, FILTER_VALIDATE_IP));
    }

    private function getError($debugLog): string
    {
        // 第一步：将 HTML 实体转换为正常文本
        $debugLog = html_entity_decode($debugLog);

        // 第二步：替换 <br> 标签为换行符，并清理多余的空白行
        $debugLog = str_replace('<br>', "\n", $debugLog);
        $debugLog = preg_replace("/\n+/", "\n", trim($debugLog));

        // 第三步：提取所有包含 "SMTP ERROR" 或 "Error:" 的行
        preg_match_all('/(?:SMTP\s+ERROR|Error:)\s*(.*)$/im', $debugLog, $matches);
        // 提取匹配到的错误信息
        $errors = $matches[1];
        $errorInfo = '';
        foreach ($errors as $error) {
            $errorInfo .= trim($error) . "\n";
        }

        return $errorInfo;
    }
}
