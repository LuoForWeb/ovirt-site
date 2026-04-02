<?php

namespace app\v1\system\v0\logic;

use app\v1\common\logic\Base;
use app\v1\opcode\PfOpcodePrivate;
use app\v1\resources\v0\logic\Node;
use app\v1\resources\v0\logic\Storage;
use xphp\Curl;
use xphp\Email;
use xphp\Outlook;

/**
 * note          系统配置之系统通知 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/14 14:18
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Notice extends Base
{
    /**
     * 获取邮件通知配置
     * @param array $params 数据
     * @return array
     */
    public function getEmailConf($params = []): array
    {

        $sql = "select * from bd_email_notice where email_notice_type = 1";
        $data = $this->dbSelect($sql);

        $smtpConfig = json_decode($data[0]['smtp_config'], true);

        if (empty($smtpConfig['pass'])) {
            $pass = '';
        } else {
            $pass = md5($smtpConfig['pass']);
        }

        $conf = [
            'email_flag' => v1_parse_flag_to_bool($data[0]['email_notice_flag']),
            'system_flag' => v1_parse_flag_to_bool($data[0]['system_notice_flag']),
            'system_level' => explode(',', $data[0]['system_notice_level']),
            'task_flag' => v1_parse_flag_to_bool($data[0]['task_notice_flag']),
            'task_level' => explode(',', $data[0]['task_notice_level']),
            'report_conf' => json_decode($data[0]['report_config'], true),
            'rec_email' => json_decode($data[0]['receive_email'], true),
            'host' => $smtpConfig['host'] ?? '',
            'port' => $smtpConfig['port'] ?? '',
            'user' => $smtpConfig['email'],
            'is_ssl' => $smtpConfig['is_ssl'] ?? false,
            'is_debug' => $smtpConfig['is_debug'] ?? false,
            'client_id' => $smtpConfig['client_id'] ?? '',
            'client_secret' => $smtpConfig['client_secret'] ?? '',
            'tenant_id' => $smtpConfig['tenant_id'] ?? '',
            'encryption' => $smtpConfig['encryption'] ?? 0,
            'email_model' => $smtpConfig['email_model'] ? intval($smtpConfig['email_model']) : 1,
            'pass' => $pass,
            'database_flag' => v1_parse_flag_to_bool($data[0]['db_drill_report_flag']),
            'verify_flag' => v1_parse_flag_to_bool($data[0]['verify_report_flag']),
            'verify_level' => explode(',', $data[0]['verify_report_level']),
        ];

        $sql = "select email, telephone from bd_user where user_uuid = ?";
        $data = $this->dbSelect($sql, [xphp_get_user_info()['userUuid']]);
        if (!empty($data)) {
            $conf['email'] = empty($data[0]['email']) ?
                xphp_get_lang('WEB_SYSTEM_SETTING_NO_SET') : $data[0]['email'];
            $conf['email_enabled'] = !empty($data[0]['email']);
        } else {
            $conf['email'] = xphp_get_lang('WEB_SYSTEM_SETTING_NO_SET');
            $conf['email_enabled'] = false;
        }

        $default = xphp_get_config('email', 'EMAIL');
        if (
            $default['from_email'] == $conf['user'] && $default['smpt_host'] == $conf['host']
            && $default['port'] == $conf['port']
        ) {
            $conf['is_default'] = 1; // 是默认的
        } else {
            $conf['is_default'] = 2;
        }
        // 只有vinchin中文版才会有默认发件人，其他OEM版本都是默认为空
        $systemInfo = xphp_get_config('app', 'SYSTEM_INFO');
        if (
            $systemInfo['vendor'] == 'vinchin' && $systemInfo['enterprise'] != 'vinchin_enterprise_en'
        ) {
            $conf['is_enterprise_en'] = 2;
        } else {
            // 没有默认的配置
            $conf['is_enterprise_en'] = 1;
            $conf['is_default'] = 2;
            $conf['host'] = '';
            $conf['port'] = '';
            $conf['user'] = '';
            $conf['pass'] = '';
        }

        $conf['default'] = [
            'host' => $default['smpt_host'],
            'port' => $default['port'],
            'user' => $default['from_email'],
            'encryption' => 1,
            'is_ssl' => true,
            'is_debug' => false,
            'pass' => md5(v1_encrype($default['from_email_pass'])),
        ];

        return $conf;
    }

    /**
     * 配置邮件通知
     * @param array $params 数据
     * @return array
     */
    public function setEmailConf($params = []): array
    {

        $flag = v1_parse_bool_to_flag($params['flag']);
        $systemFlag = v1_parse_bool_to_flag($params['system_flag']);
        $taskFlag = v1_parse_bool_to_flag($params['task_flag']);
        $verifyFlag = v1_parse_bool_to_flag($params['verify_flag']);
        $databaseFlag = v1_parse_bool_to_flag($params['database_flag']);
        $systemLevel = $this->getNoticeSettingLevelStr($params['system_level']);
        $taskLevel = $this->getNoticeSettingLevelStr($params['task_level']);
        $verifyLevel = $this->getNoticeSettingLevelStr($params['verify_level']);
        $reportConf = $params['report_conf'];
        $recevieEmail = $params['rec_email'];

        $settings = json_decode($reportConf, true);
        $strategy = &$settings['time_strategy'];
        $currentTime = strtotime(date('H:i:s'));
        $currentDate = strtotime(date('Y-m-d H:i:s'));
        foreach ($strategy as &$s) {
            $senddate = ''; //最后发送日期作为已发送标记，未发送设为空，当前时间超过设置时间就设为已发送
            $type = $s['type'];
            $time = strtotime($s['notice_time']);
            switch ($type) {
                case 1:
                    if ($currentTime >= $time) {
                        $senddate = date('Y-m-d');
                    }
                    break;
                case 2:
                    $weekDay = date('w');
                    if ($weekDay == 0) {
                        $weekDay = 7;
                    }
                    $dayList = [];
                    for ($i = 0; $i < count($s['days']); $i++) {
                        if ($s['days'][$i] == 1) {
                            $dayList[] = $i + 1;
                        }
                    }
                    if ($weekDay == $dayList[0] && $currentTime >= $time || $weekDay > $dayList[0]) {
                        $senddate = date('Y-m-d');
                    }
                    break;
                case 3:
                    $monthDay = date('j');
                    $dayList = [];
                    for ($i = 0; $i < count($s['days']); $i++) {
                        if ($s['days'][$i] == 1) {
                            $dayList[] = $i + 1;
                        }
                    }
                    if ($monthDay == $dayList[0] && $currentTime >= $time || $monthDay > $dayList[0]) {
                        $senddate = date('Y-m-d');
                    }
                    break;
                case 4:
                    if ($currentDate >= $time) {
                        $senddate = date('Y-m-d');
                    }
                    break;
            }
            $s['send_date'] = $senddate;
        }
        $reportConf = json_encode($settings);

        //邮件通知
        $sql = "update bd_email_notice set email_notice_flag = ?, system_notice_flag = ?, system_notice_level = ?, 
            task_notice_flag = ?, task_notice_level = ?, report_config = ?, receive_email = ?,db_drill_report_flag = ?,
                           verify_report_flag = ?, verify_report_level = ? where email_notice_type = 1";
        $sqlParams = [
            $flag, $systemFlag, $systemLevel, $taskFlag, $taskLevel,
            $reportConf, $recevieEmail, $databaseFlag, $verifyFlag, $verifyLevel
        ];

        $result = $this->dbExec($sql, $sqlParams);

        $systemLogKey = 'SYSTEM_SETTING_EMAIL_NOTICE';
        $this->unifyWriteSystemLog($result, $systemLogKey);

        $operate = xphp_get_lang('WEB_SYSTEM_SETTING_EMAIL_NOTICE');
        if ($result) {
            return [
                'code' => 0,
                'msg' => $operate . xphp_get_lang('WEB_PUBLIC_SUCCESS')
            ];
        } else {
            return [
                'code' => 1,
                'msg' => $operate . xphp_get_lang('WEB_PUBLIC_FAILURE')
            ];
        }
    }

    /**
     * 测试发送邮件
     * @param array $params 数据
     * @return array
     */
    public function sendEmailTest($params = []): array
    {

        $host = $params['host'];
        $port = $params['port'];
        $user = $params['user'];
        $isSsl = boolval($params['is_ssl']);
        $isDebug = boolval($params['is_debug']);
        $pass = base64_decode($params['pass']);
        $encryption = intval($params['encryption']);
        $recEmail = [$params['rec_email']];

        $title = xphp_get_lang('WEB_SYSTEM_SETTING_TEST_EMAIL_TITLE');
        $info = xphp_get_lang('WEB_SYSTEM_SETTING_TEST_EMAIL_CONTENT');

        //判断此次传入邮箱信息是否和之前一样
        //从数据库获取邮件信息
        $sql = "select smtp_config from bd_email_notice where email_notice_type = 1";
        $data = $this->dbSelect($sql);
        $smtpConfig = json_decode($data[0]['smtp_config'], true);

        $default = xphp_get_config('email', 'EMAIL');

        if ($user == $default['from_email'] && $host == $default['smpt_host'] && $port == $default['port']) {
            //默认邮件,设置
            $pass = $default['from_email_pass'];
        } elseif ($pass == md5($smtpConfig['pass'])) {
            // 未修改密码
            $pass = v1_decrypt($smtpConfig['pass']);
        }

        //直接调用发送邮件接口
        if (!in_array(xphp_get_config('app', 'SYSTEM_INFO')['enterprise'], xphp_get_config('app', 'ENTERPRISE'))) {
            $message = file_get_contents(DATA_PATH . 'email/email-test-oem.html');
        } elseif (xphp_get_config('app', 'lang') == 'en-us') {
            $message = file_get_contents(DATA_PATH . 'email/email-test-en.html');
        } else {
            $message = file_get_contents(DATA_PATH . 'email/email-test.html');
        }
        $dateformat = xphp_get_config('special', 'dateformat') ?? 'Y-m-d H:i:s';
        $message = str_replace('reportTime', date($dateformat), $message);
        $message = str_replace('content', $info, $message);
        $message = str_replace(
            'backupServerHost',
            $_SERVER['REQUEST_SCHEME'] . '://' . $this->getMasterNodeIp(),
            $message
        );
        $companyEmail = $this->getCompanyEmail();
        $message = str_replace(
            'supportEmailHref',
            'mailto: ' . $companyEmail,
            $message
        );
        $message = str_replace('supportEmail', $companyEmail, $message);
        if ($params['email_model'] != 2) {
            $email = new Email();
            $emailConfig = xphp_get_config('email', 'EMAIL');
            $email->config(
                $host,
                $port,
                $emailConfig['authentication'],
                $user,
                $pass,
                xphp_get_config('email', 'EMAIL_ENCRYPTION_TYPE')[$encryption],
                $isSsl
            );
            $result = $email->sendmail($recEmail, $title, $message, [], [], [], $isDebug);
        } else {
            // outlook
            $email = new Outlook();
            $result = $email->sendmail(
                $params['client_id'],
                $params['client_secret'],
                empty($params['tenant_id']) ? 'common' : $params['tenant_id'],
                $user,
                $recEmail,
                $title,
                $message,
                1
            );
        }

        $recEmailStr = implode(',', $recEmail);
        $operate = xphp_get_lang('WEB_SYSTEM_SETTING_SEND_EMAIL_TO') . $recEmailStr;

        if (($isDebug && $result['code'] == 0) || (empty($isDebug) && $result)) {
            return [
                'code' => 0,
                'msg' => $operate . xphp_get_lang('WEB_PUBLIC_SUCCESS')
            ];
        } else {
            return [
                'code' => 1,
                //'msg' => $operate . xphp_get_lang('WEB_PUBLIC_FAILURE'),
                'msg' => $result['info'] ?? $operate . xphp_get_lang('WEB_PUBLIC_FAILURE'),
                'info' => $result
            ];
        }
    }

    /**
     * 保存SMTP配置信息
     * @param array $params 数据
     * @return array
     */
    public function setEmailSmtp($params = []): array
    {
        $host = $params['host'];
        $port = $params['port'];
        $user = $params['user'];
        $isSsl = boolval($params['is_ssl']);
        $isDebug = boolval($params['is_debug']);
        $pass = base64_decode($params['pass']);
        $encryption = intval($params['encryption']);

        $operate = xphp_get_lang('WEB_SYSTEM_SAVE_SMTP_INFO');
        $emailConf = xphp_get_config('email', 'EMAIL');

        //从数据库获取邮件信息
        $sql = "select smtp_config from bd_email_notice where email_notice_type = 1";
        $data = $this->dbSelect($sql);
        $smtpConfig = json_decode($data[0]['smtp_config'], true);

        if (
            $emailConf['from_email'] == $user && $emailConf['smpt_host'] == $host
            && $emailConf['port'] == $port
        ) {
            // 恢复系统默认
            $pass = v1_encrype($emailConf['from_email_pass']);
            $encryption = 1;
            //$isSsl = true;
        } elseif ($pass == md5($smtpConfig['pass'])) {
            // 未修改密码
            $pass = $smtpConfig['pass'];
        } else {
            $pass = v1_encrype($pass);
        }

        $smtpConfig = array(
            'email_model' => $params['email_model'],
            'client_id' => $params['client_id'],
            'client_secret' => $params['client_secret'],
            'tenant_id' => $params['tenant_id'],
            'host' => $host,
            'port' => $port,
            'email' => $user,
            'pass' => $pass,
            'encryption' => $encryption,
            'is_ssl' => $isSsl,
            'is_debug' => $isDebug
        );

        $sql = "update bd_email_notice set smtp_config = ? where email_notice_type = 1";
        $result = $this->dbExec($sql, array(json_encode($smtpConfig, true)));
        if ($result) {
            return [
                'code' => 0,
                'msg' => $operate . xphp_get_lang('WEB_PUBLIC_SUCCESS')
            ];
        } else {
            return [
                'code' => 1,
                'msg' => $operate . xphp_get_lang('WEB_PUBLIC_FAILURE')
            ];
        }
    }

    /**
     * 获取短信通知配置
     * @param array $params 数据
     * @return array
     */
    public function getSmsConf($params = []): array
    {

        $sql = "select sms_notice_flag, sms_quantity, system_notice_flag, system_notice_level, task_notice_flag, 
                task_notice_level, sms_send_type, sms_device, sms_device_config from bd_sms_notice";
        $data = $this->dbSelect($sql);

        $deviceConfig = json_decode($data[0]['sms_device_config'], true);
        $deviceConfig['pass'] = base64_encode(v1_decrypt($deviceConfig['pass']));
        $conf = [
            'sms_flag' => v1_parse_flag_to_bool($data[0]['sms_notice_flag']),
            'system_flag' => v1_parse_flag_to_bool($data[0]['system_notice_flag']),
            'system_level' => explode(',', $data[0]['system_notice_level']),
            'task_flag' => v1_parse_flag_to_bool($data[0]['task_notice_flag']),
            'task_level' => explode(',', $data[0]['task_notice_level']),
            'quantity' => $data[0]['sms_quantity'],
            'send_type' => intval($data[0]['sms_send_type']),
            'device' => $data[0]['sms_device'],
            'device_config' => $deviceConfig
        ];

        $sql = "select telephone from bd_user where user_uuid = ?";
        $data = $this->dbSelect($sql, [xphp_get_user_info()['userUuid']]);
        if (!empty($data)) {
            $conf['telephone'] = empty($data[0]['telephone']) ?
                xphp_get_lang('WEB_SYSTEM_SETTING_NO_SET') : $data[0]['telephone'];
            $conf['telephone_enabled'] = !empty($data[0]['telephone']);
        } else {
            $conf['telephone'] = xphp_get_lang('WEB_SYSTEM_SETTING_NO_SET');
            $conf['telephone_enabled'] = false;
        }

        return $conf;
    }

    /**
     * 配置短信通知
     * @param array $params 数据
     * @return array
     */
    public function setSmsConf($params = []): array
    {

        $flag = v1_parse_bool_to_flag($params['flag']);
        $systemFlag = v1_parse_bool_to_flag($params['system_flag']);
        $taskFlag = v1_parse_bool_to_flag($params['task_flag']);
        $systemLevel = $this->getNoticeSettingLevelStr($params['system_level']);
        $taskLevel = $this->getNoticeSettingLevelStr($params['task_level']);
        $telphoneList = $params['rec_telphone_list'];
        //短信通知
        $sql = "update bd_sms_notice set sms_notice_flag = ?, system_notice_flag = ?, system_notice_level = ?, 
            task_notice_flag = ?, task_notice_level = ?";
        $sqlParams = array($flag, $systemFlag, $systemLevel, $taskFlag, $taskLevel);
        $result = $this->dbExec($sql, $sqlParams);

        $sql = 'select sms_device_config from bd_sms_notice';
        $smsNoticeConfigResult = $this->dbSelect($sql);
        $smsDeviceConfig = json_decode($smsNoticeConfigResult[0]['sms_device_config'],true);
        $smsDeviceConfig['receive_telephone'] = $telphoneList;
        $updatedConfigJson = json_encode($smsDeviceConfig,true);
        $updateSql = 'update bd_sms_notice set sms_device_config = ?';
        $sqlParams = array($updatedConfigJson);
        $this->dbExec($updateSql, $sqlParams);

        $systemLogKey = 'SYSTEM_SETTING_SMS_NOTICE';
        $this->unifyWriteSystemLog($result, $systemLogKey);

        $operate = xphp_get_lang('WEB_SYSTEM_SETTING_SMS_NOTICE');
        if ($result) {
            return [
                'code' => 0,
                'msg' => $operate . xphp_get_lang('WEB_PUBLIC_SUCCESS')
            ];
        } else {
            return [
                'code' => 1,
                'msg' => $operate . xphp_get_lang('WEB_PUBLIC_FAILURE')
            ];
        }
    }

    /**
     * 测试短信猫发送
     * @param array $params 数据
     * @return array
     */
    public function sendCatSmsTest($params = [])
    {

        $ip = $params['ip'];
        $database = $params['database'];
        $port = $params['port'];
        $user = $params['user'];
        $pass = v1_encrype(base64_decode($params['pass']));
        $phone = $params['rec_phone'];
        $dateformat = xphp_get_config('special', 'dateformat') ?? 'Y-m-d H:i:s';
        $smsParams = array(
            'tels' => $phone,
            'msg' => xphp_get_lang('WEB_SYSTEM_SETTING_SEND_SMS_SUCCESS') . date($dateformat),
        );
        $config = array(
            'ip' => $ip,
            'port' => $port,
            'user' => $user,
            'pass' => $pass,
            'database' => $database
        );

        $sendResult = $this->sendSmsModem($smsParams, $config);
        $this->updateModemSettings($sendResult, $config);

        return $sendResult;
    }

    /**
     * 短信猫发送短信(直接插入短信猫设备MySQL数据库,并检查发送结果)
     * @param array $params 数据
     * @param $config 短信猫的数据库配置,这里注意传入的所有参数都参照数据库配置,特别注意存入数据库的密码是加密处理的
     * @return json|array
     */
    private function sendSmsModem($params = [], $config = [])
    {

        $server = $config['ip'] . ':' . $config['port'];
        $username = $config['user'];

        $password = v1_decrypt($config['pass']);
        $database = $config['database'];

        $tels = $params['tels'];
        $msg = $params['msg'];
        $operate = xphp_get_lang('WEB_USERS_SEND_SMS_TO') . $tels;

        try {
            $conn = new \PDO("mysql:host={$server};dbname={$database}", $username, $password);
        } catch (\PDOException $e) {
            $errorNum = v1_get_error_num('PF_SETTING_NOTICE_SMSMODEM_CONNECT_DB_ERROR');
            return $this->muOpResult(false, $operate, 'Connection failed: ' . $e->getMessage(), '', $errorNum);
        }
        $conn->query('set names utf8;');

        $sql = "insert into smsserver_out (type,recipient,text,encoding,create_date,gateway_id) values 
                ('O', '" . $tels . "', '" . $msg . "', 'U',now(),'*');";
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute();
        if (!$result) {
            $errorNum = v1_get_error_num('PF_SETTING_NOTICE_SMSMODEM_INSERT_DB_ERROR');
            return $this->muOpResult(false, $operate, '', '', $errorNum);
        }
        $id = $conn->lastInsertId();

        $flag = true;
        $i = 0;
        $smsconfig = xphp_get_config('sms', 'SMS_CONFIG');
        $sleepTime = $smsconfig['SLEEPTIME'];
        $checkTime = $smsconfig['CHECKTIME'];

        $sendResult = false;
        while ($flag) {
            //循环检查发送结果
            $sql = "select status from smsserver_out where id = $id";

            $result = $conn->query($sql);
            $row = $result->fetchAll();

            if ($row[0] != 'S' && $i < $checkTime) {
                //如果发送失败,并且检查次数少于设定的阈值,睡眠几秒后继续检查
                sleep($sleepTime);
                $i++;
                continue;
            }
            $flag = false;
            if ($row[0] == 'S') {
                //发送成功
                $sendResult = true;
            }
        }
        return [
            'code' => $sendResult ? 0 : 1,
            'mag' => $operate .
                ($sendResult ? xphp_get_lang('WEB_PUBLIC_SUCCESS') : xphp_get_lang('WEB_PUBLIC_FAILURE'))
        ];
    }

    /**
     * 测试短信发送
     * @param array $params 数据
     * @return array
     */
    public function sendSmsTest($params = []): array
    {
        $sms_mode = $params['sms_mode'];
        $phone = $params['telephone'];
        $dateformat = xphp_get_config('special', 'dateformat') ?? 'Y-m-d H:i:s';
        if($sms_mode == xphp_get_config('app', 'FLAG')['SET']){
            $smsParams = array(
                'tels' => $phone,
                'msg' => xphp_get_lang('WEB_SYSTEM_SETTING_SEND_SMS_SUCCESS') . date($dateformat),
                'sendTime' => '',
                'sms_mode' => $sms_mode
            );
            $sendResult = $this->sendSmsInernet($smsParams);
            $this->updateInternetSettings();
            $this->updateSmsNoticeConfig($params);
        }elseif($sms_mode == xphp_get_config('app', 'FLAG')['UNSET']){
            // 自定义
            $appId = $params['appId'];
            $appsecret = $params['appsecret'];
            $company = $params['company'];
            $url = $params['url'];
            $body = array(
                'applicationid' => $appId,
                'company_id_' => $company,
                'destaddr'=> $phone,
                'extcode' => '',
                'messagecontent' => xphp_get_lang('WEB_SYSTEM_SETTING_SEND_SMS_SUCCESS') . date($dateformat),
                'msgfmt' => 0,
                'reqdeliveryreport' => 0,
                'requesttime' => date('Y-m-d H:i:s', time()),
                'sendmethod' => 0,
                'sismsid' => xphp_uuid(),
                'appsecret' => $appsecret,
                'url' => $url,
                'sms_mode' => $sms_mode
            );
            $p = array(
                'tels' => $phone,        //电话号码,用英文逗号分隔,支持多个
                'msg' => xphp_get_lang('WEB_SYSTEM_SETTING_SEND_SMS_SUCCESS') . date($dateformat),          //短信内容
                'sendTime' => date('Y-m-d H:i:s', time()),//发送时间 ,没有的话为空
            );
            $sendResult = $this->smsRemoteApi($body,$p);
            if($sendResult['data']['code'] == 0){
                $this->updateInternetSettings();
                $this->updateSmsNoticeConfig($body);
            }
        }
        return $sendResult;
    }

    /**
     * 获取微信通知配置
     * @param array $params 数据
     * @return array
     */
    public function getWechatConf($params = []): array
    {
        // 获取微信公众号的配置 从bd_system_settings 的 settings_type 为 SYSTEM_WECHAT_OPENID 10 的默认值
        $setttingType = xphp_get_config('app', 'SETTINGS_CONF');
        $sqlupdate = "select settings_content from bd_system_settings where settings_type = ?";
        $wechats = $this->dbSelect($sqlupdate, [$setttingType['SYSTEM_WECHAT_OPENID']]);
        $conf = [];
        if (!empty($wechats)) {
            $conf = json_decode($wechats[0]['settings_content'], true);
        }

        $wechat = xphp_get_config('notice', 'WECHAT_CONFIG');
        $wechatConf = [
            'data' => [
                'flag' => $conf['wechatFlag'] && v1_parse_flag_to_bool($conf['wechatFlag']),
                'system_flag' => $conf['systemFlag'] && v1_parse_flag_to_bool($conf['systemFlag']),
                'system_level' => explode(',', $conf['systemLevel'] ?? ''),
                'task_flag' => $conf['taskFlag'] && v1_parse_flag_to_bool($conf['taskFlag']),
                'task_level' => explode(',', $conf['taskLevel'] ?? ''),
                'config' => [
                    'gh_id' => $conf['gh_id'] ?? $wechat['gh_id'],
                    'appid' => $conf['appid'] ?? $wechat['appid'],
                    'wechat_mode' => $conf['wechat_mode'] ?? $wechat['wechat_mode'], // 默认是系统出厂模式
                    'appsecret' => $conf['appsecret'] ?? $wechat['appsecret'],
                    'template_id' => $conf['template_id'] ?? $wechat['template_id'],
                    'template_param1' => $conf['template_param1'] ?? $wechat['template_param1'],
                    'template_param2' => $conf['template_param2'] ?? $wechat['template_param2'],
                    'template_url' => $conf['template_url'] ?? '',
                    // 授权二维码地址
                    'auth_qrcode' => '',
                    // 微信二维码地址
                    'wechat_qrcode' => '',
                ],
            ],
            'conf' => $wechat // 返回默认的配置，为了前端的模式切换有数据替换
        ];

        $url = $conf['template_url'] ?? $this->getCurrntUrl();

        $qrparam = xphp_short_encrypt(json_encode(
            [
                'userUuid' => xphp_get_user_info()['userUuid'],
                'appid' => $wechatConf['data']['config']['appid'],
                'appsecret' => $wechatConf['data']['config']['appsecret'],
                'template_url' => $url,
            ]
        ));

        $wechaturl = 'https://open.weixin.qq.com/qr/code?username=' . $wechatConf['data']['config']['gh_id'];
        if ($wechatConf['data']['config']['wechat_mode'] == 1) {
            // 中转服务
            $baseurl = xphp_get_config('notice', 'WECHAT_TRANSFER_URL');
            $thumb = $this->getThumb();
            $qrurl = $baseurl . '/api/v1/system/wechat_transfer?x-api-version=1.0-rev0&param=' . $qrparam
                . '&thumb=' . $thumb;
        } else {
            $qrurl = $url . '/wechat.php?param=' . $qrparam;
        }

        $outfile = md5($qrurl) . '.png';
        $qrcode = xphp_qrcode($qrurl, $outfile);

        // 从URL获取图片内容
        $imageContent = file_get_contents($wechaturl);
        // 将图片内容转换为Base64编码
        $base64Image = base64_encode($imageContent);
        $qrcode2 = 'data:image/jpeg;base64,' . $base64Image;

        $wechatConf['data']['config']['auth_qrcode'] = $qrcode;
        $wechatConf['data']['config']['wechat_qrcode'] = $qrcode2;

        return $wechatConf;
    }

    /**
     * 配置微信通知
     * @param array $params 请求参数
     * @return array
     */
    public function setWechatConf($params = []): array
    {

        // 需要先校验下 bd_system_settings 表里面是否存在了openid参数值
        $setttingType = xphp_get_config('app', 'SETTINGS_CONF');
        $sqlupdate = "select settings_content from bd_system_settings where settings_type = ?";
        $wechats = $this->dbSelect($sqlupdate, [$setttingType['SYSTEM_WECHAT_OPENID']]);

        if (!empty($wechats)) {
            $wechatContent = json_decode($wechats[0]['settings_content'], true);

            if (!empty($wechatContent['openid']) || !$params['flag']) {
                $wechatContent['wechatFlag'] = v1_parse_bool_to_flag($params['flag']);
                $wechatContent['systemFlag'] = v1_parse_bool_to_flag($params['system_flag']);
                $wechatContent['taskFlag'] = v1_parse_bool_to_flag($params['task_flag']);
                $systemLevel = [];
                foreach ($params['system_level'] as $key => $item) {
                    if ($item) {
                        $systemLevel[] = $key + 1;
                    }
                }
                $wechatContent['systemLevel'] = implode(',', $systemLevel);

                $taskLevel = [];
                foreach ($params['task_level'] as $key => $item) {
                    if ($item) {
                        $taskLevel[] = $key + 1;
                    }
                }
                $wechatContent['taskLevel'] = implode(',', $taskLevel);

                // 保存更新数据
                $sql = "update bd_system_settings set settings_content = ?, modify_time = ? where settings_type = ?";
                $this->dbExec(
                    $sql,
                    [
                        json_encode($wechatContent),
                        date('Y-m-d H:i:s'),
                        $setttingType['SYSTEM_WECHAT_OPENID']
                    ]
                );

                // 删除data下面的所有二维码资源
                $qrparam =  xphp_short_encrypt(json_encode(
                    [
                        'userUuid' => xphp_get_user_info()['userUuid'],
                        'appid' => $params['appid'],
                        'appsecret' => $params['appsecret'],
                        'template_url' => $params['template_url'],
                    ]
                ));
                $baseurl = xphp_get_config('notice', 'WECHAT_TRANSFER_URL');
                $thumb = $this->getThumb();
                $qrurl = $baseurl . '/api/v1/system/wechat_transfer?x-api-version=1.0-rev0&param=' . $qrparam
                    . '&thumb=' . $thumb;
                // 获取授权二维码
                $outfile = md5($qrurl) . '.png';

                $path = DATA_PATH . 'qrcode/';
                $mydir = dir($path);
                while ($file = $mydir->read()) {
                    if (($file != '.') and ($file != '..')) {
                        if ($outfile != $file) {
                            unlink($path . '/' . $file);
                        }
                    }
                }
                $mydir->close();
                return [
                    'code' => 0,
                    'msg' => xphp_get_lang('UI_EMERGENCY_PLAN_CONFIGURE') .
                        xphp_get_lang('UI_SETTINGS_NOTICE_WECHAT') . xphp_get_lang('WEB_ERROR_BD_GENERIC_SUCCESS')
                ];
            }
        }
        if (!$params['flag']) {
            // 表示关闭
            return [
                'code' => 0,
                'msg' => xphp_get_lang('UI_EMERGENCY_PLAN_CONFIGURE') .
                    xphp_get_lang('UI_SETTINGS_NOTICE_WECHAT') . xphp_get_lang('WEB_ERROR_BD_GENERIC_SUCCESS')
            ];
        }
        return [
            'code' => 1,
            'msg' => xphp_get_lang('UI_SETTINGS_NOTICE_WECHAT_POWER_ON_TIP')
        ];
    }

    /**
     * 获取微信通知的二维码
     * @param array $params 请求参数
     * @return array
     */
    public function getWechatQrcode($params = []): array
    {
        $qrparam = xphp_short_encrypt(json_encode(
            [
                'userUuid' => xphp_get_user_info()['userUuid'],
                'appid' => $params['appid'],
                'appsecret' => $params['appsecret'],
                'template_url' => $params['template_url'],
            ]
        ));

        if ($params['wechat_mode'] == 1) {
            // 中转服务
            $baseurl = xphp_get_config('notice', 'WECHAT_TRANSFER_URL');
            $thumb = $this->getThumb();
            $qrurl = $baseurl . '/api/v1/system/wechat_transfer?x-api-version=1.0-rev0&param=' . $qrparam
                . '&thumb=' . $thumb;
        } else {
            $qrurl = $params['template_url'] . '/wechat.php?param=' . $qrparam;
        }
        $wechaturl = 'https://open.weixin.qq.com/qr/code?username=' . $params['gh_id'];
        // 获取授权二维码
        $outfile = md5($qrurl) . '.png';
        $qrcode = xphp_qrcode($qrurl, $outfile);
        // 从URL获取图片内容
        $imageContent = file_get_contents($wechaturl);
        // 将图片内容转换为Base64编码
        $base64Image = base64_encode($imageContent);
        $qrcode2 = 'data:image/jpeg;base64,' . $base64Image;

        return ['wechat_qrcode' => $qrcode2,'auth_qrcode' => $qrcode];
    }

    /**
    * 获取指纹信息
     * @return string
     */
    public function getThumb(): string
    {
        $opName = 'PT_LICENSE_OP_QUERY_THUMBPRINT';
        $mbResult = $this->mbPFMsg($opName, '', true);
        return $mbResult['msg']['thumbprint'] ?? '';
    }

    /**
     * 发送测试微信通知 / 保存微信通知配置
     * @param array $params 请求参数
     * @return array
     */
    public function sendWechatSet($params = []): array
    {
        // 需要先校验下 bd_system_settings 表里面是否存在了openid参数值
        $setttingType = xphp_get_config('app', 'SETTINGS_CONF');
        $sqlupdate = "select settings_content from bd_system_settings where settings_type = ?";
        $wechats = $this->dbSelect($sqlupdate, [$setttingType['SYSTEM_WECHAT_OPENID']]);

        if (!empty($wechats)) {
            $wechatContent = json_decode($wechats[0]['settings_content'], true);
            // 这里判断下，如果更换了appid，那么之前的授权用户都要删掉
            if (!empty($wechatContent['openid']) && $params['appid'] == $wechatContent['appid']) {
                $wechatContent = array_merge($wechatContent, $params);

                // 保存更新数据
                $sql = "update bd_system_settings set settings_content = ?, modify_time = ? where settings_type = ?";
                $this->dbExec(
                    $sql,
                    [json_encode($wechatContent), date('Y-m-d H:i:s'), $setttingType['SYSTEM_WECHAT_OPENID']]
                );

                if (!empty($params['test_notice'])) {
                    // 发送测试微信通知
                    $return = $this->sendWechat(
                        [
                            'title' => xphp_get_lang('UI_TEST_WECHAT_TITLE'),
                            'content' => xphp_get_lang('UI_TEST_WECHAT_DESCTION')
                        ]
                    );

                    if ($return) {
                        return [
                            'code' => 0,
                            'msg' => xphp_get_lang('UI_TEST_DESCTION_SUCCESS')
                        ];
                    }
                    return [
                        'code' => 1,
                        'msg' => xphp_get_lang('UI_SETTINGS_NOTICE_WECHAT_POWER_ON_TIP')
                    ];
                }

                return [
                    'code' => 0,
                    'msg' => xphp_get_lang('UI_SETTINGS_NOTICE_WECHAT') . xphp_get_lang('WEB_ERROR_BD_GENERIC_SUCCESS')
                ];
            }
        }
        return [
            'code' => 1,
            'msg' => xphp_get_lang('UI_SETTINGS_NOTICE_WECHAT_POWER_ON_TIP')
        ];
    }

    /**
     * 获取微信通知用户列表
     * @param array $params 请求参数
     * @return array
     */
    public function getWechatUser($params = []): array
    {

        $start = intval($params['offset']);
        $length = $params['limit'];

        // 获取微信公众号的配置 从bd_system_settings 的 settings_type 为 SYSTEM_WECHAT_OPENID 10 的默认值
        $setttingType = xphp_get_config('app', 'SETTINGS_CONF');
        $sqlupdate = "select settings_content from bd_system_settings where settings_type = ?";
        $wechats = $this->dbSelect($sqlupdate, [$setttingType['SYSTEM_WECHAT_OPENID']]);

        $data = [];
        if (!empty($wechats)) {
            $wechatContent = json_decode($wechats[0]['settings_content'], true);
            $data = $wechatContent['openid'] ?? [];
        }

        $total = count($data);
        if (!empty($data)) {
            $data = array_slice($data, $start, $length, true);
        }
        $records = [];
        foreach ($data as $d) {
            $headimgurl = $d['headimgurl'];
            // 从URL获取图片内容
            $imageContent = file_get_contents($headimgurl);
            // 将图片内容转换为Base64编码
            $base64Image = 'data:image/jpeg;base64,' . base64_encode($imageContent);

            $records[] = [
                'openid' => $d['openid'],
                'user_name' => $d['nickname'],
                'headimg_url' => $base64Image,
                'href_url' => $headimgurl,
            ];
        }
        return [
            'rows' => $records,
            'total' => $total
        ];
    }

    /**
     * 删除微信授权用户
     * @param array $params 请求参数
     * @return array
     */
    public function delWechatUser($params = []): array
    {

        // 获取微信公众号的配置 从bd_system_settings 的 settings_type 为 SYSTEM_WECHAT_OPENID 10 的默认值
        $setttingType = xphp_get_config('app', 'SETTINGS_CONF');
        $sqlupdate = "select settings_content from bd_system_settings where settings_type = ?";
        $wechats = $this->dbSelect($sqlupdate, [$setttingType['SYSTEM_WECHAT_OPENID']]);

        $data = [];
        if (!empty($wechats)) {
            $wechatContent = json_decode($wechats[0]['settings_content'], true);
            $data = $wechatContent['openid'] ?? [];
        }

        if (!empty($data)) {
            // 进行数组剔除
            foreach ($data as $key => $val) {
                if (in_array($val['openid'], $params['openid'])) {
                    unset($data[$key]);
                }
            }
            $wechatContent['openid'] = array_values($data);
            // 保存更新数据
            $sql = "update bd_system_settings set settings_content = ?, modify_time = ? where settings_type = ?";
            $this->dbExec(
                $sql,
                [json_encode($wechatContent), date('Y-m-d H:i:s'), $setttingType['SYSTEM_WECHAT_OPENID']]
            );
            return [
                'code' => 0,
                'msg' => xphp_get_lang('UI_PUBLIC_DELETE') . xphp_get_lang('WEB_PUBLIC_SUCCESS')
            ];
        }
        return [
            'code' => 1,
            'msg' => xphp_get_lang('UI_FORGETPWD_USER_NOT_EXIST_ERROR')
        ];
    }

    /**
     * 获取企业微信通知配置
     * @param array $params 数据
     * @return array
     */
    public function getWecomConf($params = []): array
    {
        // 获取企业微信公众号的配置 从bd_system_settings 的 settings_type 为 SYSTEM_WECHAT_OPENID 11 的默认值
        $setttingType = xphp_get_config('app', 'SETTINGS_CONF');
        $sqlupdate = "select settings_content from bd_system_settings where settings_type = ?";
        $wechats = $this->dbSelect($sqlupdate, [$setttingType['SYSTEM_ENTERPRISE_WECHAT']]);
        $conf = [];
        if (!empty($wechats)) {
            $conf = json_decode($wechats[0]['settings_content'], true);
        }

        return [
            'flag' => $conf['wechatFlag'] && v1_parse_flag_to_bool($conf['wechatFlag']),
            'system_flag' => $conf['systemFlag'] && v1_parse_flag_to_bool($conf['systemFlag']),
            'system_level' => explode(',', $conf['systemLevel'] ?? ''),
            'task_flag' => $conf['taskFlag'] && v1_parse_flag_to_bool($conf['taskFlag']),
            'task_level' => explode(',', $conf['taskLevel'] ?? ''),
            'config' => [
                // 某个自建应用的id及secret, 在管理端 -> 企业应用 -> 自建应用, 点进相应应用可以看到
                'wechat_app_id'     => $conf['wechat_app_id'] ?? '',
                'wechat_url'        => $conf['wechat_url'] ?? '',
                // 企业微信配置信息
                'wechat_core_id'    => $conf['wechat_core_id'] ?? '', // 企业的id，在管理端->"我的企业" 可以看到
                'wechat_app_secret' => $conf['wechat_app_secret'] ?? '',
            ]
        ];
    }

    /**
     * 配置企业微信通知
     * @param array $params 请求参数
     * @return array
     */
    public function setWecomConf($params = []): array
    {

        $config = [
            // 企业的id，在管理端->"我的企业" 可以看到
            'CORP_ID'               => $params['wechat_core_id'],
            // 某个自建应用的id及secret, 在管理端 -> 企业应用 -> 自建应用, 点进相应应用可以看到
            'APP_ID'                => $params['wechat_app_id'],
            'APP_SECRET'            => $params['wechat_app_secret'],
        ];
        $chk = xphp_send_wework_api($config, [], 1);

        if ($chk) {
            $params['wechatFlag'] = v1_parse_bool_to_flag($params['flag']);
            $params['systemFlag'] = v1_parse_bool_to_flag($params['system_flag']);
            $params['taskFlag'] = v1_parse_bool_to_flag($params['task_flag']);
            $systemLevel = [];
            foreach ($params['system_level'] as $key => $item) {
                if ($item) {
                    $systemLevel[] = $key + 1;
                }
            }
            $params['systemLevel'] = implode(',', $systemLevel);

            $taskLevel = [];
            foreach ($params['task_level'] as $key => $item) {
                if ($item) {
                    $taskLevel[] = $key + 1;
                }
            }
            $params['taskLevel'] = implode(',', $taskLevel);

            $setttingType = xphp_get_config('app', 'SETTINGS_CONF');
            $sqlupdate = "select settings_content from bd_system_settings where settings_type = ?";
            $wechats = $this->dbSelect($sqlupdate, [$setttingType['SYSTEM_ENTERPRISE_WECHAT']]);
            if (!empty($wechats)) {
                $wechatContent = json_decode($wechats[0]['settings_content'], true);
                // 保存更新数据
                $wechatContent = array_merge($wechatContent, $params);
                $sql = "update bd_system_settings set settings_content = ?, modify_time = ? where settings_type = ?";
                $this->dbExec(
                    $sql,
                    [json_encode($wechatContent), date('Y-m-d H:i:s'), $setttingType['SYSTEM_ENTERPRISE_WECHAT']]
                );
            } else {
                // 插入数据
                $sql = "INSERT INTO `bd_system_settings`
                        (`settings_type`, `settings_content`, `modify_time`, `user_uuid`)
                        VALUES (?, ?, ?, ?)";
                $this->dbExec(
                    $sql,
                    [
                        $setttingType['SYSTEM_ENTERPRISE_WECHAT'],
                        json_encode($params),
                        date('Y-m-d H:i:s'),
                        xphp_get_user_info()['userUuid']
                    ]
                );
            }
            return [
                'code' => 0,
                'msg' => xphp_get_lang('UI_SETTINGS_NOTICE_WECHAT') . xphp_get_lang('WEB_ERROR_BD_GENERIC_SUCCESS')
            ];
        }

        return [
            'code' => 1,
            'msg' => xphp_get_lang('UI_SETTINGS_NOTICE_WECHAT_TPS')
        ];
    }

    /**
     * 发送企业微信测试通知 / 保存企业微信通知配置
     * @param array $params 请求参数
     * @return array
     */
    public function sendWecomSet(array $params): array
    {
        // 企业微信通知 ，测试是否能够获取accesstoken
        $config = [
            // 企业的id，在管理端->"我的企业" 可以看到
            'CORP_ID'               => $params['wechat_core_id'],
            // 某个自建应用的id及secret, 在管理端 -> 企业应用 -> 自建应用, 点进相应应用可以看到
            'APP_ID'                => $params['wechat_app_id'],
            'APP_SECRET'            => $params['wechat_app_secret'],
        ];
        if (!empty($params['test_notice'])) {
            // 发送测试的企业微信通知
            $chk = xphp_send_wework_api($config, [
                'title' => xphp_get_lang('UI_TEST_WECHAT2_TITLE'),
                'content' => xphp_get_lang('UI_TEST_WECHAT2_DESCTION'),
                //'url' => $params['wechat_url'],
            ]);
            if ($chk) {
                return [
                    'code' => 0,
                    'msg'  => xphp_get_lang('UI_SETTINGS_NOTICE_WECHAT') . xphp_get_lang('WEB_ERROR_BD_GENERIC_SUCCESS')
                ];
            }
            return [
                'code' => 1,
                'msg'  => xphp_get_lang('UI_SETTINGS_NOTICE_WECHAT_TPS')
            ];
        }
        $chk = xphp_send_wework_api($config, [], 1);
        if ($chk) {
            $setttingType = xphp_get_config('app', 'SETTINGS_CONF');
            $sqlupdate = "select settings_content from bd_system_settings where settings_type = ?";
            $wechats = $this->dbSelect($sqlupdate, [$setttingType['SYSTEM_ENTERPRISE_WECHAT']]);
            if (!empty($wechats)) {
                $wechatContent = json_decode($wechats[0]['settings_content'], true);
                // 保存更新数据
                $wechatContent = array_merge($wechatContent, $params);
                $sql = "update bd_system_settings set settings_content = ?, modify_time = ? where settings_type = ?";
                $this->dbExec(
                    $sql,
                    [json_encode($wechatContent), date('Y-m-d H:i:s'), $setttingType['SYSTEM_ENTERPRISE_WECHAT']]
                );
            } else {
                $user = xphp_get_user_info();
                // 插入数据
                $sql = "INSERT INTO `bd_system_settings` 
                                    (`settings_type`, `settings_content`, `modify_time`, `user_uuid`)
                                     VALUES (?, ?, ?, ?)";
                $this->dbExec(
                    $sql,
                    [
                        $setttingType['SYSTEM_ENTERPRISE_WECHAT'],
                        json_encode($params),
                        date('Y-m-d H:i:s'),
                        $user['userUuid']
                    ]
                );
            }
            return [
                'code' => 0,
                'msg'  => xphp_get_lang('UI_SETTINGS_NOTICE_WECHAT') . xphp_get_lang('WEB_ERROR_BD_GENERIC_SUCCESS')
            ];
        }
        return [
            'code' => 1,
            'msg'  => xphp_get_lang('UI_SETTINGS_NOTICE_WECHAT_TPS')
        ];
    }

    /**
     * 发送邮件接口 monitor 报表进程会使用
     * @param array $params 请求参数
     * @return array
     */
    public function sendEmail(array $params): array
    {
        $title = $params['title'];
        $email = array_filter($params['email']);
        $info = $params['info'] ?? '';
        $attachment = $params['attachment'] ?? [];
        $emailStr = implode(',', $email);
        $cc = $params['cc'] ?? [];
        //获取邮件配置
        $sql = "select smtp_config from bd_email_notice where email_notice_type = 1";
        $data = $this->dbSelect($sql);
        if (empty($data)) {
            return [
                'code' => 1,
                'msg' => xphp_get_lang('UI_SETTINGS_NOTICE_SMTP') . xphp_get_lang('WEB_DRILLS_NOT_CONFIG')
            ];
        }

        $smtpConfig = json_decode($data[0]['smtp_config'], true);

        if ($smtpConfig['email_model'] != 2) {
            $pass = v1_decrypt($smtpConfig['pass']);
            $encryption = intval($smtpConfig['encryption']);
            $emailConfig = xphp_get_config('email', 'EMAIL');
            $emailEncrypType = xphp_get_config('email', 'EMAIL_ENCRYPTION_TYPE');
            $encryption = $emailEncrypType[$encryption] ?? 'ssl';
            //直接调用发送邮件接口
            $emailUtils = new Email();

            $emailUtils->config(
                $smtpConfig['host'],
                $smtpConfig['port'],
                $emailConfig['authentication'],
                $smtpConfig['email'],
                $pass,
                $encryption,
                $smtpConfig['is_ssl'] ?? true
            );
            $result = $emailUtils->sendmail($email, $title, $info, $attachment, $cc);
        } else {
            // outlook
            $emails = new Outlook();
            $result = $emails->sendmail(
                $smtpConfig['client_id'],
                $smtpConfig['client_secret'],
                empty($smtpConfig['tenant_id']) ? 'common' : $smtpConfig['tenant_id'],
                $smtpConfig['email'],
                $email,
                $title,
                $info
            );
        }


        $operate = xphp_get_lang('WEB_USERS_SEND_EMAIL_TO') . $emailStr;

        if ($result) {
            $this->writeLog('send Email success!!!!!!!!!!');
            return [
                'code' => 0,
                'msg' => $operate . xphp_get_lang('WEB_PUBLIC_SUCCESS')
            ];
        } else {
            $this->writeLog('send Email failed!!!!!!!!!!');
            return [
                'code' => 1,
                'msg' => $operate . xphp_get_lang('WEB_PUBLIC_FAILURE')
            ];
        }
    }

    /**
     * 发送短信接口
     * @param array $params 请求参数
     * @return array
     */
    public function sendSms(array $params): array
    {
        $sql = "select sms_send_type, sms_device, sms_device_config from bd_sms_notice";
        $data = $this->dbSelect($sql);
        if (empty($data)) {
            return [
                'code' => 1,
                'msg' => xphp_get_lang('UI_SETTINGS_NOTICE_SMS_SENDTYPE') . xphp_get_lang('WEB_DRILLS_NOT_CONFIG')
            ];
        }

        $smsSendType = intval($data[0]['sms_send_type']);
        $smsSendTypeConf = xphp_get_config('sms', 'SMS_CONFIG')['SEND_TYPE'] ?? '';
        if ($smsSendTypeConf['INTERNET'] == $smsSendType) {
            //短信平台发送
            $return = $this->sendSmsInernet($params);
            if (!empty($return[0])) {
                return [
                    'code' => 0,
                    'msg' => $return[0] . xphp_get_lang('WEB_SYSTEM_SETTING_SEND_SMS_SUCCESS')
                ];
            }
        } elseif ($smsSendTypeConf['MODEM'] == $smsSendType) {
            //短信猫发送
            return $this->sendSmsModem($params, json_decode($data[0]['sms_device_config'], true));
        }
        return [
            'code' => 1,
            'msg' => xphp_get_lang('UI_SETTINGS_NOTICE_SMS_SENDTYPE') . xphp_get_lang('WEB_DRILLS_NOT_CONFIG')
        ];
    }

    /**
     * 发送微信通知
     * @param  array $params 请求参数
     *                       title、content,
     *                       url非必须(相对的路径即可)
     * @return bool
     */
    public function sendWechat(array $params): bool
    {

        // 需要先校验下 bd_system_settings 表里面是否存在了openid参数值
        $setttingType = xphp_get_config('app', 'SETTINGS_CONF');
        $sqlupdate = "select settings_content from bd_system_settings where settings_type = ?";
        $wechats = $this->dbSelect($sqlupdate, [$setttingType['SYSTEM_WECHAT_OPENID']]);
        if (empty($wechats)) {
            return false;
        }
        $wechatContent = json_decode($wechats[0]['settings_content'], true);

        $title = $params['title'];
        $content = $params['content'];
        $openid = array_column($wechatContent['openid'], 'openid'); // 消息接收人的openid

        $url = (!empty($params['url']) ? $wechatContent['template_url'] . $params['url'] : '');

        $data = array (
            $wechatContent['template_param1'] => array (
                'value' => $title,
                'color' => '#000000'
            ),
            $wechatContent['template_param2'] => array (
                'value' => $content,
                'color' => '#666666'
            ),
        );
        $config = [
            'appid' => $wechatContent['appid'],
            'appsecret' => $wechatContent['appsecret'],
        ];
        return xphp_send_wechat_template(
            $openid,
            $wechatContent['template_id'],
            $data,
            $url,
            $config,
            $wechatContent['wechat_mode'] ?? 1
        );
    }

    /**
     * 发送企业微信通知
     * @param array $params 请求参数
     * @return array
     */
    public function sendWecom(array $params): array
    {
        // 获取企业微信公众号的配置 从bd_system_settings 的 settings_type 为 SYSTEM_WECHAT_OPENID 11 的默认值
        $setttingType = xphp_get_config('app', 'SETTINGS_CONF');
        $sqlupdate = "select settings_content from bd_system_settings where settings_type = ?";
        $wechats = $this->dbSelect($sqlupdate, [$setttingType['SYSTEM_ENTERPRISE_WECHAT']]);
        if (empty($wechats)) {
            return [
                'code' => 1,
                'msg' => xphp_get_lang('UI_SETTINGS_NOTICE_WECHAT_INTERNET2')
                    . xphp_get_lang('WEB_SYSTEM_SETTING_NO_SET')
            ];
        }
        $configArr = json_decode($wechats[0]['settings_content'], true);

        // 企业微信通知 ，测试是否能够获取accesstoken
        $config = [
            // 企业的id，在管理端->"我的企业" 可以看到
            'CORP_ID'               => $configArr['wechat_core_id'],
            // 某个自建应用的id及secret, 在管理端 -> 企业应用 -> 自建应用, 点进相应应用可以看到
            'APP_ID'                => $configArr['wechat_app_id'],
            'APP_SECRET'            => $configArr['wechat_app_secret'],
        ];
        $url = (!empty($params['url']) ? $configArr['template_url'] . $params['url'] : '');
        // 发送企业微信通知
        $chk = xphp_send_wework_api($config, [
            'title' => $params['title'],
            'content' => $params['content'],
            'url' => $url,
        ]);
        if ($chk) {
            return [
                'code' => 0,
                'msg'  => xphp_get_lang('UI_SETTINGS_NOTICE_WECHAT_INTERNET2')
                    . xphp_get_lang('WEB_ERROR_BD_GENERIC_SUCCESS')
            ];
        }
        return [
            'code' => 1,
            'msg'  => xphp_get_lang('WEB_UTILS_ENTERPRISE_WECHAT_SEND_INFO_ERROR')
        ];
    }

    /**
     * 发送存储报表通知
     * @param array $strategy 请求参数
     * @return bool
     */
    public function sendStorageReport($strategy): bool
    {

        $type = $strategy['type'];
        $flag = xphp_get_config('app', 'FLAG');
        $sql = "select count(bsr.storage_id) as total, sum(bsr.total_size) as total_size,
                        sum(bsr.free_size) as free_size
                    from bd_storage_resource bsr where bsr.lan_free_flag = ?";
        $data = $this->dbSelect($sql, array($flag['UNSET']));
        $totalSize = v1_calsize($data[0]['total_size'] ?? 0, true);
        $freeSize = v1_calsize($data[0]['free_size'] ?? 0, true);

        $sqlReport = "select count(distinct bbt.storage_uuid) as storage_num, sum(bbt.write_size) as storage_size
                            from bd_backup_timepoint bbt,bd_storage_resource bsr
                            where bsr.storage_uuid = bbt.storage_uuid and bsr.lan_free_flag = ? ";
        $sqlStorageSize = "select bsr.storage_nickname, sum(bbt.write_size) current_size
                            from bd_storage_resource bsr,bd_backup_timepoint bbt
                            where bsr.storage_uuid = bbt.storage_uuid and bsr.lan_free_flag = ? ";

        switch ($type) {
            case 1:
                $sqlReport .= ' and to_days(now()) - to_days(bbt.timepoint) = 1';  //昨天
                $sqlStorageSize .= ' and to_days(now()) - to_days(bbt.timepoint) = 1';

                $timeRange = date('Y/m/d', strtotime('-1 day'));
                $reportTitle = $timeRange;
                $reportType = xphp_get_lang('UI_REPORT_DAILY');
                $timeDes = xphp_get_lang('UI_REPORT_THIS_DAY');
                break;
            case 2:
                $sqlReport .= " and  YEARWEEK(date_format(bbt.timepoint,'%Y-%m-%d'),1) = YEARWEEK(now(),1) - 1"; // 上一周
                $sqlStorageSize .= " and  YEARWEEK(date_format(bbt.timepoint,'%Y-%m-%d'),1) = YEARWEEK(now(),1) - 1";
                $timeRangeStart = date('w', time()) == 1 ? '-1 monday' : '-2 monday';//当天是周一
                $timeRange = date('Y/m/d', strtotime($timeRangeStart, time())) . '--'
                    . date('Y/m/d', strtotime('-1 sunday', time()));
                $reportTitle = $timeRange;
                $reportType = xphp_get_lang('UI_REPORT_WEEKLY');
                $timeDes = xphp_get_lang('UI_REPORT_THIS_WEEK');
                break;
            case 3:
                //上个月
                $sqlReport .= " and PERIOD_DIFF(date_format(now(), '%Y%m'), date_format(bbt.timepoint, '%Y%m')) =1";
                $sqlStorageSize .= " and PERIOD_DIFF(date_format(now(), '%Y%m'), date_format(bbt.timepoint, '%Y%m'))=1";

                $timeRange = date('Y/m/01', strtotime('-1 month')) . '--' . date('Y/m/t', strtotime('-1 month'));
                $reportTitle = $timeRange;
                $reportType = xphp_get_lang('UI_REPORT_MONTHLY');
                $timeDes = xphp_get_lang('UI_REPORT_THIS_MONTH');
                break;
            case 4:
                $sqlReport .= ' and year(bbt.timepoint)=year(date_sub(now(),interval 1 year))'; //上一年
                $sqlStorageSize .= ' and year(bbt.timepoint)=year(date_sub(now(),interval 1 year))';

                $timeRange = date('Y/01/01', strtotime('-1 year')) . '--' . date('Y/12/31', strtotime('-1 year'));
                $reportTitle = $timeRange;
                $reportType = xphp_get_lang('UI_REPORT_ANNALS');
                $timeDes = xphp_get_lang('UI_REPORT_THIS_YEAR');
                break;
        }
        $emailTitle = $reportType . $timeRange;

        $dataReport = $this->dbSelect($sqlReport, array($flag['UNSET']));
        $dataStorageSize = $this->dbSelect($sqlStorageSize, array($flag['UNSET']));
        $currentSize = v1_calsize(intval($dataReport[0]['storage_size']), true);
        $currentNum = intval($dataReport[0]['storage_num']);

        $sqlStorage = "select bn.ip, bn.node_nickname, bn.host_name, bsr.storage_uuid, bsr.storage_nickname,
                            bsr.storage_type, bsr.node_uuid, bsr.total_size, bsr.free_size, bsr.storage_config
                        from bd_storage_resource bsr, bd_node bn
                        where bn.node_uuid = bsr.node_uuid and bsr.lan_free_flag = ? order by bsr.free_size desc";
        $dataStorage = $this->dbSelect($sqlStorage, array($flag['UNSET']));
        $storageList = array();
        $storageHandler = new Storage();
        $nodeHandler = new Node();
        foreach ($dataStorage as $storage) {
            foreach ($dataStorageSize as $d) {
                $currentStorageSize = 0;
                if ($d['storage_nickname'] && $storage['storage_nickname'] == $d['storage_nickname']) {
                    $currentStorageSize = $d['current_size'];
                }
                $usedSize = $storage['total_size'] - $storage['free_size'];
                $usage = $storage['total_size'] ?
                    round(intval($currentStorageSize) / intval($storage['total_size']) * 100, 2) : 0;
                $nodeName = $nodeHandler->getNodeGridName(
                    $storage['ip'],
                    $storage['node_nickname'],
                    $storage['host_name']
                ) . '(' . $storage['ip'] . ')';
                //异地备份系统节点显示
                if ($storage['storage_type'] == xphp_get_config('resource', 'BD_STORAGE_TYPE')['REMOTE']) {
                    $storageConfig = json_decode($storage['storage_config'], true);
                    $nodeName = $storageConfig['remote_ip'];
                    if (!empty($storageConfig['remote_name'])) {
                        $nodeName = $storageConfig['remote_name'] . '(' . $storageConfig['remote_ip'] . ')';
                    }
                }
                $storageList[] = array(
                    'storage_name' => $storage['storage_nickname'],
                    'storage_type' => $storageHandler->getStorageTypeDes(intval($storage['storage_type'])),
                    'node' =>  $nodeName,
                    'total_size' => v1_calsize($storage['total_size'] ?? 0, true),
                    'used_size' => v1_calsize($usedSize ?? 0, true),
                    'free_size' => v1_calsize($storage['free_size'] ?? 0, true),
                    'useage' => $usage . '%'
                );
            }
        }
        $reportTime = date('Y-m-d H:i:s');

        if (!in_array(xphp_get_config('app', 'SYSTEM_INFO')['enterprise'], xphp_get_config('app', 'ENTERPRISE'))) {
            $message = file_get_contents(DATA_PATH . '/email/email-storage-report-oem.html');
        } elseif (xphp_get_config('app', 'lang') == 'en-us') {
            $message = file_get_contents(DATA_PATH . '/email/email-storage-report-en.html');
        } else {
            $message = file_get_contents(DATA_PATH . '/email/email-storage-report.html');
        }

        $content = '';
        if ($storageList) {
            foreach ($storageList as $s) {
                $info = '<tr><td>' . $s['storage_name'] . '</td>' .
                    '<td>' . $s['storage_type'] . '</td>' .
                    '<td>' . $s['node'] . '</td>' .
                    '<td>' . $s['total_size'] . '</td>' .
                    '<td>' . $s['used_size'] . '</td>' .
                    "<td class='font-success'>" . $s['free_size'] . '</td>' .
                    '<td>' . $s['useage'] . '</td></tr>';
                $content .= $info;
            }
            $message = str_replace('display-hide', 'display-show', $message);
            $message = str_replace('<tr><td>tableTd</td></tr>', $content, $message);
        }
        $message = str_replace('reportType', $reportType, $message);
        $message = str_replace('hostIp', $this->getMasterNodeIp(), $message);
        $message = str_replace('timeRange', $reportTitle, $message);
        $message = str_replace('reportTime', $reportTime, $message);

        $message = str_replace('totalSize', $totalSize, $message);
        $message = str_replace('freeSize', $freeSize, $message);
//      $message = str_replace('currentNum', $currentNum, $message);
        $message = str_replace('currentSize', $currentSize, $message);
        $message = str_replace('timeDes', $timeDes, $message);

        $message = str_replace('backupServerHost', $this->getMasterNodeIpLink(), $message);

        $email = $this->getCompanyEmail();
        $message = str_replace('supportEmailHref', 'mailto: ' . $email, $message);
        $message = str_replace('supportEmail', $email, $message);

        $title =  xphp_get_lang('UI_REPORT_STORAGE') . '—— ' . $emailTitle;
        $this->writeLog('send storage report email!!!!!!!!!!!' . $reportTime);
        return $this->sendUnifyEmail($title, $message);
    }

    /**
     * 发送虚拟机报表通知
     * @param array $strategy 请求参数
     * @return bool
     */
    public function sendVmReport(array $strategy): bool
    {

        $type = $strategy['type'];
        $totalSql = "select count(vt.dir_path) as total_vm_num
                            from vm_tree vt, vm_vcenter vv
                            where vt.vcenter_uuid = vv.vcenter_uuid and vt.type = ? and vt.display_mode =?";
        $dataTotalVm = $this->dbSelect(
            $totalSql,
            [7, xphp_get_config('vm', 'VM_TREE_DISPLAY_MODE')['HOST_AND_CLUSTER']]
        );
        $protectedSql = "select count(distinct vbt.dir_path) as protected_vm_num, sum(bbt.write_size) as storage_size
                            from vm_backup_timepoint vbt, bd_backup_timepoint bbt
                            where bbt.timepoint_uuid = vbt.timepoint_uuid";
        $dataProtect = $this->dbSelect($protectedSql);
        $totalVms = intval($dataTotalVm[0]['total_vm_num']);
        $protectVms = intval($dataProtect[0]['protected_vm_num']);
        $totalSize = v1_calsize($dataProtect[0]['storage_size'] ?? 0, true);

        $sqlBackupNum = "select count(timepoint_uuid) as current_success_num ,sum(write_size) as storage_size
                            from bd_backup_timepoint ";
        $sqlvm = "select distinct vbt.dir_path
                    from vm_backup_timepoint vbt, bd_backup_timepoint bbt
                    where bbt.timepoint_uuid = vbt.timepoint_uuid ";
        $sqlCurrentVm = "select count(distinct vbt.dir_path) as current_vms
                            from vm_backup_timepoint vbt, bd_backup_timepoint bbt
                            where bbt.timepoint_uuid = vbt.timepoint_uuid ";
        $sqlDayInfo = "select unix_timestamp(finish_time) finish_time, details, submodule_type, task_name
                        from bd_history_task where task_type = ? and module_type = ? ";

        switch ($type) {
            case 1:
                $sqlDayInfo .= " and to_days(now()) - to_days(date_format(finish_time, '%Y-%m-%d')) = 1";
                $sqlBackupNum .= " where to_days(now()) - to_days(date_format(timepoint, '%Y-%m-%d')) = 1";  //昨天
                $sqlCurrentVm .= " and to_days(now()) - to_days(date_format(bbt.timepoint, '%Y-%m-%d')) = 1";  //昨天
                $sqlvm .= " and to_days(now()) - to_days(date_format(bbt.timepoint, '%Y-%m-%d')) = 1";  //昨天

                $timeRange = date('Y/m/d', strtotime('-1 day'));
                $reportTitle = $timeRange;
                $reportType = xphp_get_lang('UI_REPORT_DAILY');
                $timeDes = xphp_get_lang('UI_REPORT_THIS_DAY');
                break;
            case 2:
                $sqlDayInfo .= " and YEARWEEK(date_format(date_format(finish_time, '%Y-%m-%d'),'%Y-%m-%d'),1)
                 = YEARWEEK(now(),1) - 1";
                $sqlBackupNum .= " where YEARWEEK(date_format(date_format(timepoint, '%Y-%m-%d'),'%Y-%m-%d'),1)
                 = YEARWEEK(now(),1) - 1"; // 上一周
                $sqlCurrentVm .= " and YEARWEEK(date_format(date_format(bbt.timepoint, '%Y-%m-%d'),'%Y-%m-%d'),1)
                 = YEARWEEK(now(),1) - 1";
                $sqlvm .= " and YEARWEEK(date_format(date_format(bbt.timepoint, '%Y-%m-%d'),'%Y-%m-%d'),1)
                 = YEARWEEK(now(),1) - 1";
                $timeRangeStart = date('w', time()) == 1 ? '-1 monday' : '-2 monday';
                $timeRange = date('Y/m/d', strtotime($timeRangeStart, time())) . '--' .
                    date('Y/m/d', strtotime('-1 sunday', time()));
                $reportTitle = $timeRange;
                $reportType = xphp_get_lang('UI_REPORT_WEEKLY');
                $timeDes = xphp_get_lang('UI_REPORT_THIS_WEEK');
                break;
            case 3:
                $sqlDayInfo .= " and PERIOD_DIFF(date_format(now(), '%Y%m'), date_format(finish_time, '%Y%m')) =1";
                $sqlBackupNum .= " where PERIOD_DIFF(date_format(now(), '%Y%m'), date_format(timepoint, '%Y%m')) =1";
                $sqlCurrentVm .= " and PERIOD_DIFF(date_format(now(), '%Y%m'), date_format(bbt.timepoint, '%Y%m')) =1";
                $sqlvm .= " and PERIOD_DIFF(date_format(now(), '%Y%m'), date_format(bbt.timepoint, '%Y%m')) =1";

                $timeRange = date('Y/m/01', strtotime('-1 month')) . '--' . date('Y/m/t', strtotime('-1 month'));
                $reportTitle = $timeRange;
                $reportType = xphp_get_lang('UI_REPORT_MONTHLY');
                $timeDes = xphp_get_lang('UI_REPORT_THIS_MONTH');
                break;
            case 4:
                $sqlDayInfo .= ' and year(finish_time)=year(date_sub(now(),interval 1 year))';
                $sqlBackupNum .= " where year(timepoint)=year(date_sub(now(),interval 1 year))"; //上一年
                $sqlCurrentVm .= ' and year(bbt.timepoint)=year(date_sub(now(),interval 1 year))';
                $sqlvm .= ' and year(bbt.timepoint)=year(date_sub(now(),interval 1 year))';

                $timeRange = date('Y/01/01', strtotime('-1 year')) . '--' . date('Y/12/31', strtotime('-1 year'));
                $reportTitle = $timeRange;
                $reportType = xphp_get_lang('UI_REPORT_ANNALS');
                $timeDes = xphp_get_lang('UI_REPORT_THIS_YEAR');
                break;
        }
        $emailTitle = $reportType . $timeRange;

        $dataVm = $this->dbSelect($sqlvm);
        $vmList = [];
        if ($dataVm) {
            foreach ($dataVm as $d) {
                $vmList[]  = $this->getReportVmInfo($type, $d['dir_path']); //虚拟机列表信息
            }
        }
        $dataCurrent = $this->dbSelect($sqlBackupNum, array());
        $dataCurrenVms = $this->dbSelect($sqlCurrentVm);
        $dataInfo = $this->dbSelect(
            $sqlDayInfo,
            array(xphp_get_config('task', 'TASKTYPE')['BACKUP'], xphp_get_config('module', 'MODULE_TYPE')['VM'])
        );
        $currentNum = $failedNum = $currentSuccessNum = 0;
        $successList = $failedList = [];
        foreach ($dataInfo as $d) {
            $details = json_decode($d['details'], true);
            $vmsdetails = $details['vms_details'] ?? $details;
            foreach ($vmsdetails as $detail) {
                $currentNum++;
                if ($detail['task_status'] == xphp_get_config('vm', 'VmTaskStatus')['FINISH']) {
                    $currentSuccessNum++;
                } else {
                    $failedNum++;
                }
                $storageSize = v1_calsize(intval($detail['write_size']), true);
                if (intval($detail['task_status']) != 3) {
                    //失败无存储空间占用
                    $storageSize = xphp_get_config('app', 'NULLSPACE');
                }
                $daysVmList = array(
                    'vm_name' => $detail['vm_name'],
                    'hypervisor' =>  xphp_get_config('vm', 'VMHYPERVISORDES')[intval($d['submodule_type'])],
                    'task_name' => $d['task_name'],
                    'backup_time' => date('Y-m-d H:i:s', $d['finish_time']),
                    'status' => intval($detail['task_status']),
                    'statusDes' => $this->getVmBackupStatus(intval($detail['task_status'])),
                    'storage_size' =>  $storageSize
                );
                if (intval($detail['task_status']) == 3) {
                    $successList[] = $daysVmList;
                } else {
                    $failedList[] = $daysVmList;
                }
            }
        }

        $vmInfoList = array_merge($successList, $failedList);
        $currentVms = $dataCurrenVms[0]['current_vms'];
        $currentSize = v1_calsize($dataCurrent[0]['storage_size'] ?? 0, true);
        $failedNum = str_replace('-', '', $failedNum);
        $content = '';
        if (!in_array(xphp_get_config('app', 'SYSTEM_INFO')['enterprise'], xphp_get_config('app', 'ENTERPRISE'))) {
            $message = file_get_contents(DATA_PATH . '/email/email-vm-report-oem.html');
        } elseif (xphp_get_config('app', 'lang') == 'en-us') {
            $message = file_get_contents(DATA_PATH . '/email/email-vm-report-en.html');
        } else {
            $message = file_get_contents(DATA_PATH . '/email/email-vm-report.html');
        }
        if ($vmInfoList) {
            foreach ($vmInfoList as $vmInfo) {
                if ($vmInfo['status'] == 3) {
                    $status = "<td><span class='vm-status-success'>" . $vmInfo['statusDes'] . '</span></td>';
                } elseif ($vmInfo['status'] == 1) {
                    $status = "<td><span class='vm-status-waiting'>" . $vmInfo['statusDes'] . '</span></td>';
                } else {
                    $status = "<td><span class='vm-status-error'>" . $vmInfo['statusDes'] . '</span></td>';
                }
                $info = '<tr><td>' . $vmInfo['vm_name'] . '</td>' .
                    '<td>' . $vmInfo['hypervisor'] . '</td>' .
                    '<td>' . $vmInfo['task_name'] . '</td>' .
                    '<td>' . $vmInfo['backup_time'] . '</td>' .
                    $status .
                    '<td>' . $vmInfo['storage_size'] . '</td></tr>';
                $content .= $info;
            }
            $message = str_replace('display-hide', 'display-show', $message);
            $message = str_replace('<tr><td>tableTd</td></tr>', $content, $message);
        }

        $reportTime = date('Y-m-d H:i:s');
        $message = str_replace('reportType', $reportType, $message);
        $message = str_replace('hostIp', $this->getMasterNodeIp(), $message);
        $message = str_replace('timeRange', $reportTitle, $message);
        $message = str_replace('reportTime', $reportTime, $message);
        $message = str_replace('timeDes', $timeDes, $message);

        $message = str_replace('totalVms', $totalVms, $message);
        $message = str_replace('protectVms', $protectVms, $message);
        $message = str_replace('currentVms', $currentVms, $message);

        $message = str_replace('currentNum', $currentNum, $message);
        $message = str_replace('successNum', $currentSuccessNum, $message);
        $message = str_replace('failedNum', $failedNum, $message);
        $message = str_replace('totalSize', $totalSize, $message);
        $message = str_replace('currentSize', $currentSize, $message);
        $message = str_replace('backupServerHost', $this->getMasterNodeIpLink(), $message);

        $email = $this->getCompanyEmail();
        $message = str_replace('supportEmailHref', 'mailto: ' . $email, $message);
        $message = str_replace('supportEmail', $email, $message);

        $title =  xphp_get_lang('UI_REPORT_VM') . '—— ' . $emailTitle;
        $this->writeLog('send VM report email!!!!!!!!!!!' . $reportTime);
        return $this->sendUnifyEmail($title, $message);
    }

    /**
     * 获取虚拟机报表虚拟机备份状态描述
     * @param int $status 状态
     * @return string
     */
    private function getVmBackupStatus(int $status): string
    {

        if ($status == 3) {
            $statusDes = xphp_get_lang('WEB_PUBLIC_SUCCESS');
        } elseif ($status == 1) {
            $statusDes = xphp_get_lang('WEB_PLATFORM_DES_WAITING');
        } else {
            $statusDes = xphp_get_lang('WEB_PUBLIC_FAILURE');
        }
        return $statusDes;
    }

    /**
     * 获取报表信息
     * @param int    $type    类型
     * @param string $dirPath 路径
     * @return array
     */
    private function getReportVmInfo(int $type, string $dirPath): array
    {
        $sqlVMInfo = "select vbt.vm_name, vbt.hypervisor_type, bbt.task_name
                        from vm_backup_timepoint vbt, bd_backup_timepoint bbt
                        where bbt.timepoint_uuid = vbt.timepoint_uuid and vbt.dir_path = ? ";
        $dataInfo = $this->dbSelect($sqlVMInfo, array($dirPath));

        $sql = "select count(bbt.timepoint_uuid) as total, sum(bbt.write_size) as storage_size
                    from vm_backup_timepoint vbt, bd_backup_timepoint bbt
                    where bbt.timepoint_uuid = vbt.timepoint_uuid and vbt.dir_path = ? ";
        $sqlTime = "select bbt.timepoint
                        from bd_backup_timepoint bbt, vm_backup_timepoint vbt
                        where bbt.timepoint_uuid = vbt.timepoint_uuid and vbt.dir_path = ? ";
        $sqlParams = array($dirPath);
        switch ($type) {
            case 1:
                $sql .= " and to_days(now()) - to_days(date_format(bbt.timepoint, '%Y-%m-%d')) = 1";  //昨天
                $sqlTime .= " and to_days(now()) - to_days(date_format(bbt.timepoint, '%Y-%m-%d')) = 1 ";
                break;
            case 2:
                $sql .= " and  YEARWEEK(date_format(date_format(bbt.timepoint, '%Y-%m-%d'),'%Y-%m-%d'),1)
                        = YEARWEEK(now(),1) - 1"; // 上一周
                $sqlTime .= " and  YEARWEEK(date_format(date_format(bbt.timepoint, '%Y-%m-%d'),'%Y-%m-%d'),1)
                            = YEARWEEK(now(),1) - 1";
                break;
            case 3:
                $sql .= " and PERIOD_DIFF(date_format(now(), '%Y%m'), date_format(bbt.timepoint, '%Y%m')) =1"; //上个月
                $sqlTime .= " and PERIOD_DIFF(date_format(now(), '%Y%m'), date_format(bbt.timepoint, '%Y%m')) =1";
                break;
            case 4:
                $sql .= ' and year(bbt.timepoint)=year(date_sub(now(),interval 1 year))'; //上一年
                $sqlTime .= ' and year(bbt.timepoint)=year(date_sub(now(),interval 1 year))';
                break;
        }

        $sqlTime .= " order by bbt.timepoint desc";
        $data = $this->dbSelect($sql, $sqlParams);
        $dataTime = $this->dbSelect($sqlTime, $sqlParams);
        $lastTime = $dataTime ? $dataTime[0]['timepoint'] : '--';
        return [
            'vm_name' => $dataInfo[0]['vm_name'],
            'hypervisor' => xphp_get_config('vm', 'VMHYPERVISORDES')[intval($dataInfo[0]['hypervisor_type'])],
            'task_name' => $dataInfo[0]['task_name'],
            'last_backup_time' => $lastTime,
            'backup_num' => $data[0]['total'],
            'storage_size' => v1_calsize($data[0]['storage_size'] ?? 0, true)
        ];
    }

    /**
     * 统一发送报表通知邮件
     * @param string $title   标题
     * @param string $content 内容
     * @return bool
     */
    private function sendUnifyEmail(string $title, string $content): bool
    {

        $userConf = $this->getManagerEmailAndTelephone();
        $email = $userConf['email'];
        $email = $this->getAllEmail($email); //管理员邮件累加额外添加邮件地址
        $params = array(
            'email' => array_filter($email),
            'title' => $title,
            'info' => $content ?? '',
            'attachment' => []
        );
        $result = $this->sendEmail($params);
        return $result['code'] == 0;
    }

    /**
     * 得到所有管理员的邮件和电话
     * @return array(email=>array, telephone=>array)
     */
    private function getManagerEmailAndTelephone(): array
    {
        $sql = "select email, telephone from bd_user where user_type = ? or user_level = ?";
        $data = $this->dbSelect(
            $sql,
            [
                xphp_get_config('user', 'USERTYPE')['manager'],
                xphp_get_config('three_powers', 'THREE_POWERS_USER')['sysadmin']
            ]
        );
        $email = $telephone = [];
        foreach ($data as $d) {
            $email[] = $d['email'];
            $telephone[] = $d['telephone'];
        }
        return [
            'email' => $email,
            'telephone' => $telephone
        ];
    }

    /**
     * 得到所有的通知邮件,
     * @param array $email 要通知的邮件
     *                     在要通知的邮件基础上添加系统配置处的邮件
     * @return array
     */
    private function getAllEmail(array $email): array
    {
        $sql = "select receive_email from bd_email_notice where email_notice_type = 1";
        $data = $this->dbSelect($sql, array());
        $setEmail = json_decode($data[0]['receive_email'], true);
        return array_unique(array_merge($email, $setEmail));
    }

    /**
     * 获取配置文件的company_email
     * @return string
     */
    private function getCompanyEmail(): string
    {

        $configFile = xphp_get_config('app', 'SPECIAL_DIR') . xphp_get_config('app', 'SPECIAL_CONFIG');
        $systemInfo = xphp_get_config('app', 'SYSTEM_INFO');
        $email = $systemInfo['company_email'];
        if (file_exists($configFile)) {
            $content = file_get_contents($configFile);
            $content = json_decode($content, true);
            $email = !empty($content['SYSTEM_INFO']['company_email']) ?
                $content['SYSTEM_INFO']['company_email'] : $systemInfo['company_email'];
        }
        return $email;
    }

    /**
     * 获取备份系统IP链接，邮件使用
     * @return string
     */
    private function getMasterNodeIpLink(): string
    {
        $ip = $this->getMasterNodeIp();
        $serverIpArr = explode(' ', $ip);
        $host = 'https://' . $serverIpArr[0];
        return '<a class="font-success" style="text-decoration: none" href="' . $host . '">' . $host . '</a>  ';
    }

    /**
     * 获取当前浏览器的完整域名
     * @return string
     */
    private function getCurrntUrl(): string
    {

        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        // 输出类似 "https://example.com" 或 "http://example.com:8080"
        return $protocol . '://' . $_SERVER['HTTP_HOST'];
    }

    /**
     * 调用远程API(自定义通知)
     */
    private function smsRemoteApi($params, $info){
        // 自定义
        $appId = $params['applicationid'];
        $appsecret = $params['appsecret'];
        $company = $params['company_id_'];
        $timestamp = (int)(microtime(true) * 1000);
        $sign = sha1($appId.$appsecret.$timestamp);
        $url = $params['url'];
        $body = array(
            'applicationid' => $appId,
            'company_id_' => $company,
            'destaddr'=> $info['tels'],
            'extcode' => '',
            'messagecontent' => $info['msg'],
            'msgfmt' => 0,
            'reqdeliveryreport' => 0,
            'requesttime' => date('Y-m-d H:i:s', time()),
            'sendmethod' => 0,
            'sismsid' => xphp_uuid(),
            'appsecret' => $appsecret,
            'url' => $url
        );
        $header = array(
            'appId: ' . $appId,
            'timestamp: ' . $timestamp,
            'sign: ' . $sign,
            'company: ' . $company,
            'Content-Type: application/json'
        );
        $Curl = new Curl();
        $sendResult = $Curl->post_json($url,$body,$header);
        if(!$sendResult['result']){
            return $this->muOpResult(false, '',xphp_get_lang('WEB_ALARM_SEND_NOTICE_FAILURE'),'warning');
        }else{
            if($sendResult['data']['code'] != 0){
                return $this->muOpResult(false, $sendResult['data']['message'],xphp_get_lang('WEB_ALARM_SEND_NOTICE_FAILURE'),'warning');
            }else{
                return $sendResult;
            }
        }
    }

    /**
     * 短信平台发送短信
     * @param array $params 数据
     * @return array
     */
    private function sendSmsInernet(array $params)
    {
        $tels = $params['tels'];
        $msg = $params['msg'];
        $sendTime = $params['sendTime'];    //定时发送时间,传入时间戳
        $sms_mode = $params['sms_mode'];
        if (empty($sendTime)) {
            $sendTime = '';
        } else {
            $sendTime = date('YmdHis', $sendTime);
        }

        $sql = "select sms_device_config from bd_sms_notice";
        $data = $this->dbSelect($sql);
        $deviceConfig = json_decode($data[0]['sms_device_config'], true);

        // 加上短信配置的收件人
        $configTels = $deviceConfig['receive_telephone'] ?? [];
        // 确保配置中的电话号码是数组格式
        if (is_string($configTels)) {
            $configTels = array_filter(array_map('trim', explode(',', $configTels)));
        } elseif (!is_array($configTels)) {
            $configTels = [];
        } else {
            $configTels = array_filter(array_map('trim', $configTels));
        }

        // 合并、去重并再次过滤空值
        $telArr = array_filter(explode(',', $tels));
        $telArr = array_filter(array_unique(array_merge($telArr, $configTels)));
        $tels = implode(',', $telArr);

        $p = array(
            'tels' => $tels,        //电话号码,用英文逗号分隔,支持多个
            'msg' => $msg,          //短信内容
            'sendTime' => $sendTime,//发送时间 ,没有的话为空
        );
        $operate = xphp_get_lang('WEB_USERS_SEND_SMS_TO') . $tels;
        $remoteApiResult = '';
        if($sms_mode == xphp_get_config('app','FLAG')['UNSET']){
            // 自定义
            $telArr = array_filter(explode(',', $tels));
            foreach ($telArr as $item) {
                $smsOp = array(
                    'tels' => $item,        //电话号码
                    'msg' => $msg,          //短信内容
                );
                $remoteApiResult = $this->smsRemoteApi($deviceConfig, $smsOp);
            }
            if ($remoteApiResult['data']['code'] == 0) {
                $remoteApiResult = json_encode(['re' => true]);
            }
        }else{
            // 系统默认
            $remoteApiResult = $this->remoteApi($operate, $p);
            $this->updateSmsQuantity($remoteApiResult);
        }

        return $remoteApiResult;
    }

    /**
     * 调用远程API
     * @param sting $operate 用户操作描述
     * @param $params  参数
     * @return array
     */
    private function remoteApi($operate, $params)
    {
        $opName = 'PT_LICENSE_OP_QUERY_THUMBPRINT';
        $mbResult = $this->mbPFMsg($opName, '', true);
        $result = $mbResult['result'];
        $thumbprint = $mbResult['msg']['thumbprint'];
        if (!$result) {
            $operate = (new PfOpcodePrivate())->getOpcodeDes($opName);
            return $this->muOpResult($result, $operate, $thumbprint, '', $mbResult['errorCode']);
        }
        $params['thumbprint'] = $thumbprint;
        $remote = xphp_get_config('sms', 'REMOTE');
        $params['user'] = $remote['API_USER'];
        $params['pass'] = md5($remote['API_PASS']);
        $params['enterprise'] = xphp_get_config('app', 'SYSTEM_INFO')['enterprise'];

        $curl = new Curl();
        $result = $curl->oldPost($remote['API_URL'] . '&' . http_build_query($params), $params);

        $result = (json_decode($result, true));

        if ($result['re']) {
            //成功
            return [$operate, $result['ext']['quantity'] ?? ''];
        } else {
            $msg = '';
            //失败
            if (!empty($result['code'])) {
                $msg = $operate . xphp_get_lang('WEB_PUBLIC_FAILURE') . ',' .
                    xphp_get_lang('WEB_OPHANDLER_ERROR_CODE') . ':' . $result['code'];
            }
            if (!empty($result['ext']['info'])) {
                $msg .= ',' . xphp_get_lang('WEB_USERS_EXTEND_INFO') . ':' . $result['ext']['info'];
            }
            return $this->muOpResult(false, $operate, $msg, '', 0, $result['ext']);
        }
    }

    /**
     * 更新短信剩余量
     * @param object $params 数据
     * @return bool
     */
    private function updateSmsQuantity($params)
    {

        if (empty($params[1])) {
            return true;
        }
        $quantity = $params[1]['quantity'];
        if ($quantity >= 0) {
            $sql = "update bd_sms_notice set sms_quantity = ?";
            $this->dbQuery($sql, array($quantity));
        }
        return true;
    }

    /**
     * 互联网发送成功配置
     * @return int
     */
    private function updateInternetSettings()
    {

        $sql = "update bd_sms_notice set sms_send_type = ?";
        $sqlParams = array(xphp_get_config('sms', 'SMS_CONFIG')['SEND_TYPE']['INTERNET']);
        return $this->dbExec($sql, $sqlParams);
    }

    /**
     * 更新自定义短信通知配置
     * @return boolean
     */
    private function updateSmsNoticeConfig($config = [])
    {
        $sms_mode = $config['sms_mode'] ?? xphp_get_config('app','FLAG')['SET'];
        $sql = 'select sms_device_config from bd_sms_notice';
        $smsNoticeConfigResult = $this->dbSelect($sql);
        $smsDeviceConfig = json_decode($smsNoticeConfigResult[0]['sms_device_config'],true);
        if($sms_mode == xphp_get_config('app','FLAG')['UNSET']){
            // 自定义
            $smsDeviceConfig['applicationid'] = $config['applicationid'];
            $smsDeviceConfig['company_id_'] = $config['company_id_'];
            $smsDeviceConfig['appsecret'] = $config['appsecret'];
            $smsDeviceConfig['url'] = $config['url'];
            $smsDeviceConfig['sms_mode'] = $sms_mode;
        }else{
            $smsDeviceConfig['sms_mode'] = $sms_mode;
        }
        $updatedConfigJson = json_encode($smsDeviceConfig,true);
        $updateSql = 'update bd_sms_notice set sms_device_config = ?';
        $sqlParams = array($updatedConfigJson);
        $this->dbExec($updateSql, $sqlParams);
    }

    /**
     * 更新短信猫配置(测试发送成功后)
     * @param array $sendResult 返回结果
     * @param array $config     array 配置
     * @return bool
     */
    private function updateModemSettings(array $sendResult, array $config): bool
    {

        if ($sendResult['code']) {
            return true;
        }
        $sql = "update bd_sms_notice set sms_send_type = ?, sms_device_config = ?";
        $sqlParams = array(xphp_get_config('sms', 'SMS_CONFIG')['SEND_TYPE']['MODEM'], json_encode($config));
        return $this->dbExec($sql, $sqlParams);
    }

    /**
     * 统一写系统日志
     * @param boolean $result           结果
     * @param string  $descriptionKey   key
     * @param array   $descriptionParam desc
     * @return void
     */
    private function unifyWriteSystemLog($result, $descriptionKey, $descriptionParam = array())
    {
        if ($result) {
            $this->systemLog($descriptionKey, $descriptionParam);
        } else {
            $this->systemLog($descriptionKey, $descriptionParam, xphp_get_config('log', 'LOGLEVEL')['WARN']);
        }
    }

    /**
     * 得到通知的等级设置字符串,直接可以插入到数据库的格式     1,2,3
     * @param array $levleArr 数组
     * @return string
     */
    private function getNoticeSettingLevelStr(array $levleArr): string
    {
        $levelFlag = [];
        $i = 1;
        foreach ($levleArr as $level) {
            if ($level) {
                $levelFlag[] = $i;
            }
            $i++;
        }
        return implode(',', $levelFlag);
    }

    /**
     * 获取主节点IP即备份系统IP
     * @return string
     */
    public function getMasterNodeIp(): string
    {

        $sql = "select ip from bd_node where node_type = ? limit 1";
        $data = $this->dbSelect($sql, [xphp_get_config('app', 'NODETYPE')['MASTER']]);

        return !empty($data) ? $data[0]['ip'] : '';
    }
}
