<?php

namespace app\v1\system\v0\logic;

use app\v1\common\logic\Base;
use app\v1\resources\v0\logic\Node;

/**
 * note          系统配置之时间配置 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/14 10:54
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Time extends Base
{
    /**
     * 获取所有时区配置文件
     * @param array $parmas 数组
     * @return string
     */
    public function getAllTimezone($parmas = [])
    {
        $cmd = 'timedatectl list-timezones';
        exec($cmd, $data);
        return $data;
    }

    /**
     * 获取系统默认的时区和时间
     * @param array $params 数据
     * @return array
     */
    public function getDefaultTimeInfo($params = []): array
    {
        $timeInfo = array();
        $cmd = "timedatectl |grep Timezone|awk '{print $2}'";
        exec($cmd, $data);
        if (!$data[0]) {
            $cmd = "timedatectl |grep Time\\ zone|awk '{print $3}'";
            exec($cmd, $data);
        }
        $timeInfo['timezone'] = $data[0];
        $cmd = "timedatectl |grep Local |awk '{print $4,$5}'";
        exec($cmd, $data);
        $timeInfo['date'] = $data[1];

        $timeInfo['authFlag'] = (new Settings())->getSystemAuthorizationStatus();

        $settingsHandler = new Settings();
        $ntpConf = $settingsHandler->getSettingsInfos(xphp_get_config('app', 'SETTINGS_CONF')['NTP']);
        if (!empty($ntpConf)) {
            $ntpConf = json_decode($ntpConf[0]['settings_content'], true);
        }
        $timeInfo['ntp'] = $ntpConf;

        return $timeInfo;
    }

    /**
     * 立即同步ntp时间
     * @param array $params 数据
     * @return array
     */
    public function syncNtpTime($params = [])
    {
        $ntphost = $params['ntphost'];

        //停止NTP服务,不停止手动同步会报正在运行
        $cmd = 'systemctl stop ntpd';
        $opName = 'PT_SYSTEM_BACKGROUND_OP_DO_COMMAND';
        $msg = array('command' => $cmd);
        $mbResult = $this->mbPFMsg($opName, json_encode($msg), true);
        if (!$mbResult['result']) {
            // 尝试用rocky同步
            return $this->syncNtpTimeRocky($params);
        }
        //手动同步
        $cmd = 'ntpdate ' . $ntphost;
        $opName = 'PT_SYSTEM_BACKGROUND_OP_DO_COMMAND';
        $msg = array('command' => $cmd);
        $mbResult = $this->mbPFMsg($opName, json_encode($msg), true);
        if ($mbResult['result']) {
            $cmd = 'hwclock -w';
            $opName = 'PT_SYSTEM_BACKGROUND_OP_DO_COMMAND';
            $msg = array('command' => $cmd);
            $this->mbPFMsg($opName, json_encode($msg), true);
            $dateformat = xphp_get_config('special', 'dateformat') ?? 'Y-m-d H:i:s';
            $ext = array(
                'newTime' => date($dateformat, $this->getSystemTime())
            );
        }
        if ($mbResult['result']) {
            //重启服务,连接要断开,无法判断,不管结果
            (new Index())->restartService((new Node())->getMasterNodeUuid());
        }
        return [$mbResult['result'], xphp_get_lang('WEB_SYSTEM_SETTING_TIME_NTP_SYNC'), null, null, 0, $ext ?? ''];
    }

    /**
     * 判断时间服务是否已经存在
     * @param string $serverIp 服务域名
     * @return boolean
     */
    private function checkServerExists($serverIp)
    {

        $sourcesOutput = shell_exec('chronyc sources');
        $lines = explode("\n", $sourcesOutput);

        foreach ($lines as $line) {
            if (strpos(trim($line), $serverIp) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * 立即同步ntp时间 rocky
     * @param array $params 参数
     * @return array
     */
    public function syncNtpTimeRocky(array $params): array
    {
        $ntphost = $params['ntphost'];
        // 启用并启动 Chrony 服务
        $cmd = 'systemctl enable --now chronyd';
        $opName = 'PT_SYSTEM_BACKGROUND_OP_DO_COMMAND';
        $msg = array('command' => $cmd);
        $mbResult = $this->mbPFMsg($opName, json_encode($msg), true);
        if (!$mbResult['result']) {
            return [false, xphp_get_lang('WEB_SYSTEM_SETTING_TIME_NTP_STOP')];
        }

        // 先判断是否存在
        if (!$this->checkServerExists($ntphost)) {
            // 添加 NTP 服务器
            $cmd = "chronyc add server {$ntphost} iburst";
            $msg = array('command' => $cmd);
            $mbResult = $this->mbPFMsg($opName, json_encode($msg), true);
        }

        // 添加配置文件 /etc/chrony.conf
        //  server $ntphost iburst
        // 如果是ip的话需要 allow ip
        $cmd = $this->makeChronydConf($ntphost);
        $msg = array('command' => $cmd);
        $nodeUuid = (new Node())->getLocalNodeUUID();
        $mbResult = $this->mbNodeMsg(
            'NODE_SYS_OP_DO_CMD',
            $nodeUuid,
            json_encode($msg),
            true,
            false
        );

        if ($mbResult['result']) {
            // 立即同步
            $cmd = 'systemctl restart chronyd;chronyc -a makestep';
            $msg = array('command' => $cmd);
            $this->mbPFMsg($opName, json_encode($msg), true);
            $ext = array(
                'newTime' => date('Y-m-d H:i:s', $this->getSystemTime())
            );
            // 同步到所有节点
            $timezone = str_replace(' ', '', $params['timezone']);
            $timeinput = $ext['newTime'];

            //同步所有备份节点
            $nodes = (new Node())->getAddStorageNodeSelect();
            $nodes = v1_array_sort($nodes, 'type', 'desc', 0, -1);
            foreach ($nodes as $node) {
                //只有一次机会发送消息到后台,所以要整成一个命令执行
                //关闭chrony时间同步服务器,避免和NTP冲突
                $cmd = 'systemctl disable chronyd;';
                $cmd .= 'systemctl stop chronyd;';
                //设置时间命令
                $cmd .= 'timedatectl set-timezone ' . $timezone;
                if (!empty($timeinput)) {
                    $cmd .= ";date -s '" . $timeinput . "'";
                }
                $cmd .= ';hwclock -w;';
                /*******设置NTP等命令***/
                //如果修改成功,需要停止NTP服务器,禁用开机启动
                $cmd .= 'timedatectl set-ntp no;';
                $cmd .= 'systemctl disable ntpd;';
                $cmd .= 'systemctl stop ntpd;';

                $opName = 'NODE_SYS_OP_DO_CMD';
                $msg = array('command' => $cmd);
                $this->mbNodeMsg($opName, $node['uuid'], json_encode($msg), false, true);
            }
        }

        return [$mbResult['result'], xphp_get_lang('WEB_SYSTEM_SETTING_TIME_NTP_SYNC'), null, null, 0, $ext ?? ''];
    }

    /**
     * 组合时间同步配置信息
     * @param string $ntphost 时间服务器地址
     * @return string
     */
    private function makeChronydConf($ntphost)
    {
        $conf = "# Use public servers from the pool.ntp.org project.
# Please consider joining the pool (https://www.pool.ntp.org/join.html).
pool 2.rocky.pool.ntp.org iburst

# Use NTP servers from DHCP.
sourcedir /run/chrony-dhcp

# Record the rate at which the system clock gains/losses time.
driftfile /var/lib/chrony/drift

# Allow the system clock to be stepped in the first three updates
# if its offset is larger than 1 second.
makestep 1.0 3

# Enable kernel synchronization of the real-time clock (RTC).
rtcsync

# Enable hardware timestamping on all interfaces that support it.
#hwtimestamp *

# Increase the minimum number of selectable sources required to adjust
# the system clock.
#minsources 2

# Allow NTP client access from local network.
#allow 192.168.0.0/16

# Serve time even if not synchronized to a time source.
#local stratum 10

# Require authentication (nts or key option) for all NTP sources.
#authselectmode require

# Specify file containing keys for NTP authentication.
keyfile /etc/chrony.keys

# Save NTS keys and cookies.
ntsdumpdir /var/lib/chrony

# Insert/delete leap seconds by slewing instead of stepping.
#leapsecmode slew

# Get TAI-UTC offset and leap seconds from the system tz database.
leapsectz right/UTC

# Specify directory for log files.
logdir /var/log/chrony

# Select which information is logged.
#log measurements statistics tracking";
        $conf .= PHP_EOL . "server {$ntphost} iburst " . PHP_EOL;
        if (filter_var($ntphost, FILTER_VALIDATE_IP) !== false) {
            // 是ip
            $conf .= "allow {$ntphost} " . PHP_EOL;
        }
        return "echo '" . $conf . "' > /etc/chrony.conf";
    }

    /**
     * 设置时间
     * @param array $params 数据
     * @return void
     */
    public function setTimeInfo($params = []): array
    {
        $timezone = str_replace(' ', '', $params['timezone']);
        $timeinput = $params['timeinput'];
        $ntpcheck = $params['ntpcheck'];
        $ntphost = str_replace(' ', '', $params['ntphost']);

        //检测系统任务状态
        $operate = xphp_get_lang('WEB_SYSTEM_SETTING_TIME');
        (new Network())->checkTaskStatus($operate);

        if ($ntpcheck) {
            //如果是配置NTP服务器,直接到配置NTP服务器处
            return $this->setNtpServer($ntphost);
        }

        //同步所有备份节点
        $nodes = (new Node())->getAddStorageNodeSelect();
        //必须先修改子节点,再修改主节点,不然服务重启的时候web请求会失败
        $nodes = v1_array_sort($nodes, 'type', 'desc', 0, -1);
        foreach ($nodes as $node) {
            //只有一次机会发送消息到后台,所以要整成一个命令执行
            //关闭chrony时间同步服务器,避免和NTP冲突
            $cmd = 'systemctl disable chronyd;';
            $cmd .= 'systemctl stop chronyd;';
            //设置时间命令
            $cmd .= 'timedatectl set-timezone ' . $timezone;
            if (!empty($timeinput)) {
                $cmd .= ";date -s '" . $timeinput . "'";
            }
            $cmd .= ';hwclock -w;';
            /*******设置NTP等命令***/
            //如果修改成功,需要停止NTP服务器,禁用开机启动
            $cmd .= 'timedatectl set-ntp no;';
            $cmd .= 'systemctl disable ntpd;';
            $cmd .= 'systemctl stop ntpd;';
            //重启服务,连接要断开,无法判断,不管结果
            (new Index())->restartService($node['uuid'], $cmd, $node['node_type']);
        }
        //修改配置文件
        $settingConf = xphp_get_config('app', 'SETTINGS_CONF');
        $settingsHandler = new Settings();
        $ntpConf = $settingsHandler->getSettingsInfos($settingConf['NTP']);
        $ntpConf = json_decode($ntpConf[0]['settings_content'], true);
        $ntpConf['ntpFlag'] = false;
        $ntpConf = json_encode($ntpConf);
        $result = $settingsHandler->modifySettingsInfosWithType($settingConf['NTP'], $ntpConf);

        //写系统日志
        $this->unifyWriteSystemLog($result, 'SYSTEM_SETTING_TIME');

        //修正发送到后台,往后修改时间一直不成功的BUG.
        $nowtime = $this->getSystemTime();
        $setTime = strtotime($timeinput);
        if (abs($nowtime - $setTime) <= 10) {
            return [true, $operate];
        }
        return [$result, $operate];
    }

    /**
     * 配置NTP服务器
     * NTP配置信息存储在主节点,其他节点只有NTP服务
     * 从主节点读取配置,然后同步包括主节点的所有节点
     * @param string $ntphost host
     * @return array
     */
    private function setNtpServer(string $ntphost)
    {

        $operate = xphp_get_lang('WEB_SYSTEM_SETTING_TIME');

        $settingsHandler = new Settings();
        $ntpConf = $settingsHandler->getSettingsInfos(xphp_get_config('app', 'SETTINGS_CONF')['NTP']);
        $ntpConf = json_decode($ntpConf[0]['settings_content'], true);

        $oldNtpServer = $ntpConf['ntpServers'][0];
        //修改NTP服务器配置文件
        $confFile = xphp_get_config('app', 'NTP_SERVERS_FILE');
        $conf = file_get_contents($confFile);
        $conf = str_replace($oldNtpServer, $ntphost, $conf);
        $cmd = "echo '" . $conf . "' > " . $confFile;
        $opName = 'PT_SYSTEM_BACKGROUND_OP_DO_COMMAND';
        $msg = array('command' => $cmd);
        $mbResult = $this->mbPFMsg($opName, json_encode($msg), true);

        //同步所有备份节点
        $nodeHandler = new Node();
        $nodes = $nodeHandler->getAddStorageNodeSelect();
        //必须先修改子节点,再修改主节点,不然服务重启的时候web请求会失败

        $nodes = v1_array_sort($nodes, 'type', 'desc', 0, -1);
        foreach ($nodes as $node) {
            //关闭chrony时间同步服务器
            $cmd = 'systemctl disable chronyd;';
            $cmd .= 'systemctl stop chronyd;';

            //停止NTP服务,手动同步的时候需要停止,不然要报错.
            $cmd .= 'systemctl stop ntpd;';
            //手动从时间服务器同步
            $cmd .= 'ntpdate ' . $ntphost . ';';
            //开启NTP时间配置NTP enabled
            $cmd .= 'timedatectl set-ntp yes;';
            //设置开机启动NTP服务
            $cmd .= 'systemctl enable ntpd;';
            //重启NTP服务器
            $cmd .= 'systemctl restart ntpd;';

            $opName = 'NODE_SYS_OP_DO_CMD';
            $msg = array('command' => $cmd);
            $mbResult = $this->mbNodeMsg($opName, $node['uuid'], json_encode($msg), true, false);
            if (!$mbResult['result']) {
                // 那么尝试用rocky的chronyd同步
                $cmd = "sudo chronyc -a 'burst 4/4';sudo chronyc -a makestep;";
                $msg = array('command' => $cmd);
                $mbResult = $this->mbNodeMsg($opName, $node['uuid'], json_encode($msg), true, false);
                if (!$mbResult['result']) {
                    $msg = sprintf(xphp_get_lang('UI_PLATFORM_BAKNODE_SUNC_NTR_FAIL'), $node['text']);
                    return [false, $operate, $msg, 'warning'];
                }
            }

            //重启服务,连接要断开,无法判断,不管结果
            (new Index())->restartService($node['uuid'], '', $node['node_type']);
        }

        //写入新的NTP配置文件(备份系统显示用),配置存储在主节点,子节点没有配置信息
        if (in_array($ntphost, $ntpConf['ntpServers'])) {
            //如果在列表中,提前到第一个,系统默认为第一个为设置项
            $key = array_search($ntphost, $ntpConf['ntpServers']);
            array_splice($ntpConf['ntpServers'], $key, 1);
        }
        array_unshift($ntpConf['ntpServers'], $ntphost);
        $ntpConf['ntpFlag'] = true;
        $ntpConf = json_encode($ntpConf);

        $result = $settingsHandler->modifySettingsInfosWithType(
            xphp_get_config('app', 'SETTINGS_CONF')['NTP'],
            $ntpConf
        );
        $flag = (bool)$result;
        $result && $this->unifyWriteSystemLog(true, 'SYSTEM_SETTING_TIME');

        return [$flag, $operate];
    }

    /**
     * 统一写系统日志
     * @param boolean $result           result
     * @param string  $descriptionKey   desc_key
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
     * 获取系统时间
     * @return string
     */
    public function getSystemTime()
    {
        $cmd = "timedatectl |grep Local |awk '{print $4,$5}'";
        exec($cmd, $data);
        return strtotime($data[0]);
    }
}
