<?php

namespace app\v1\system\v0\logic;

use app\v1\common\logic\Base;
use app\v1\db\v0\logic\DbCdp;
use app\v1\db\v0\logic\DbRpc;
use app\v1\db\v0\logic\Dbtiming as DbTiming;
use app\v1\opcode\NodeOpcode;
use app\v1\opcode\PfOpcodePrivate;
use app\v1\resources\v0\logic\Client;
use app\v1\resources\v0\logic\Node;
use app\v1\user\v0\logic\User;
use phpseclib3\File\ASN1\Maps\Extension;
use app\v1\tenant\v0\logic\Tenant;
use app\v1\resources\v0\logic\Index as ResourcesIndex;

class Index extends Base
{
    /**
     * 获取系统授权类型
     * @return false|string
     */
    public function getSystemLicenseType()
    {
        $sql = "select license_type, storage_count from bd_license";
        $data = dbSelect($sql);
        $type = 0;
        $storageCount = 0;
        if (!empty($data)) {
            $type = intval($data[0]['license_type']);
            $storageCount = intval($data[0]['storage_count']);
        }
        return [
            'licensetype' => $type,
            'storage_size' => $storageCount
        ];
    }

    /**
     * 得到系统授权信息
     * (!!!!!注意)Vmhandler checkTaskLegal有调用
     * @return json
     */
    public function getSystemLisenceInfo()
    {
        $systemStatus = $this->getSystemAuthorizationStatus();
        $systemStatusDes = $this->getSystemAuthorizationDes($systemStatus);
        //调用新的获取授权信息方法
        return $this->getAuthorizedInfoV2(intval($systemStatus), $systemStatusDes);
    }

    /**
     * 获取安全配置信息
     * @param int $userLevel 用户级别
     * @return array
     */
    public function getAccountSafe($userLevel = 0): array
    {
        $sql = "select login_timeout, login_failure, pass_timeout, pass_length, pass_complexity,login_failed_lock_time
                    from bd_account_safe";
        $data = dbSelect($sql . ' where create_user_level = ?', [$userLevel]);

        if (!empty($data)) {
            $timeout = intval($data[0]['login_timeout']);
            $loginMax = intval($data[0]['login_failure']);
            $passTimeount = intval($data[0]['pass_timeout']);
            $passLength = intval($data[0]['pass_length']);
            $passComplexity = intval($data[0]['pass_complexity']);
            $faildlocktime = intval($data[0]['login_failed_lock_time']);
        }

        return array(
            'out_time' => $timeout ?? 600,
            'faild_count' => $loginMax ?? 5,
            'password_time' => $passTimeount ?? 7,
            'passlength' => $passLength ?? 8,
            'passcomplexity' => $passComplexity ?? 2,
            'faild_lock_time' => $faildlocktime ?? 1800,
        );
    }

    /**
     * 得到软件版本,
     * 1标准版,2企业版,3企业增强版,4免费版本
     * OEM版本暂时划归为企业版,企业增强版暂时没有
     * @return string
     */
    public function getSoftwareType()
    {
        $systemInfo = xphp_get_config('app', 'SYSTEM_INFO');
        $enterprise = xphp_get_config('app', 'ENTERPRISE');
        $sql = "select software_type from bd_license";
        $data = dbSelect($sql, array());
        if (empty($data)) {
            //如果没有记录,就是还没有授权,需要获取配置文件的软件版本
            $sortwareversion = xphp_get_config('app', 'SOFTWARE_VERSION');
            if ($systemInfo['enterprise'] == $enterprise['standard']) {
                $softwareVersion = $sortwareversion['STANDARD'];
            } elseif ($systemInfo['enterprise'] == $enterprise['enterprise']) {
                $softwareVersion = $sortwareversion['ENTERPRISE'];
            } elseif ($systemInfo['enterprise'] == $enterprise['enterprise_en']) {
                $softwareVersion = $sortwareversion['ENTERPRISE_EN'];
            } else {
                //如果是其他OEM版本,默认为企业版
                $softwareVersion = $sortwareversion['ENTERPRISE'];
            }
        } else {
            //有记录,获取数据库的记录
            $softwareVersion = intval($data[0]['software_type']);
        }
        return $softwareVersion;
    }

    /**
     * 判断当前访问地址是否是属于子节点
     * @return bool
     */
    public function getNodeType()
    {
        // 读取 /etc/backup_system/common_server.conf.xml 里面的 node_role的值 1是主节点 2是子节点
        $data = file_get_contents('/etc/backup_system/common_server.conf.xml');
        // 清理 ^M 符号
        $xmlStringCleaned = str_replace('^M', '', $data);

        // 使用 SimpleXML 加载 XML 数据
        $xml = simplexml_load_string($xmlStringCleaned);

        if ($xml === false) {
            /*foreach(libxml_get_errors() as $error) {
                echo "\t", $error->message;
            }*/
            // 解析失败就默认是主节点了
            return true;
        } else {
            // 访问特定节点的数据
            return $xml->node_role == 1;
        }
    }

    /**
     * 得到extension license
     * 这个函数多处使用,请谨慎修改.
     * @return json
     */
    public function getExtensionLicense()
    {

        $sql = "select extension from bd_license";
        $data = dbSelect($sql, array());
        if (empty($data)) {
            return [];
        }
        $data = v1_decrypt($data[0]['extension']);
        return json_decode($data, true);
    }

    /**
     * 得到软件是否是OEM版本,云祺版本暂时就只有三个,其他的都是OEM版本.
     * 界面会根据这个判断来做一些特别处理
     * @return bool
     */
    public function getSoftwareIsOem()
    {
        $systemInfoEnterprise = xphp_get_config('app', 'SYSTEM_INFO')['enterprise'];
        $enterprise = xphp_get_config('app', 'ENTERPRISE');
        $vinchinVersions = array(
            $enterprise['vinchin_standard'],
            $enterprise['vinchin_enterprise'],
            $enterprise['vinchin_advance_enterprise'],
        );
        return !in_array($systemInfoEnterprise, $vinchinVersions);
    }

    /**
     * 获取系统授权状态
     * @return int
     */
    public function getSystemAuthorizationStatus(): int
    {
        $sql = "select authorized_flag from bd_system ";
        $data = $this->dbSelect($sql);

        return !empty($data) ? intval($data[0]['authorized_flag']) : 2;
    }

    /**
     * 获取授权描述
     * @param int $status 授权状态标志
     * @return string
     */
    public function getSystemAuthorizationDes(int $status): string
    {
        $pfDes = xphp_get_desc('Pf', 'LISENCE_STATUS_DES');
        return $pfDes[$status];
    }

    /**
     * 得到已授权的信息2.0优化版本
     * @date  2022-02-28 15:23:44
     * @param int    $status    授权状态
     * @param string $statusDes 授权描述
     * @return json
     */
    public function getAuthorizedInfoV2(int $status, string $statusDes)
    {

        $info = array(
            'status' => $status,
        );
        $sql = "SELECT unix_timestamp(register_time) AS register_time, license_type, node_count,
                    desktop_max, days, trial_type, software_type, user_name, extension, cdp_max,
                    cdp_takeover_max_num, cdp_capacity_max
                FROM bd_license ";
        $data = $this->dbSelect($sql);

        //获取授权状态描述和超时时间
        $statusInfo = $this->getSystemStatusInfo($data[0], $status, $statusDes);
        //授权状态描述
        $info['statusDes'] = $statusInfo['statusDes'];
        //超时时间
        $info['expireTime'] = $statusInfo['expireTime'];
        //剩余天数
        $info['expireDays'] = $statusInfo['expireDays'];
        //1试用 2永久
        $info['trial'] = intval($data[0]['trial_type']);
        //用户名
        $info['customer'] = empty($data[0]['user_name']) ? '----' : $data[0]['user_name'];
        //软件版本
        $info['software'] = intval($data[0]['software_type']);
        if (empty($data)) {
            $info['software'] = $this->getSoftwareType();
        }
        //授权模块以及自定义版本信息
        if (json_decode(v1_decrypt($data[0]['extension']), true) == null) {
            $info['extension'] = [];
        } else {
            $info['extension'] = json_decode(v1_decrypt($data[0]['extension']), true);
        }

        //新的授权方式6.0
        $info['newStorageInfo'] = $this->getLicenseInfoDetials($info['extension']);

        $user = xphp_get_user_info();
        //授权功能
        $info['authfun'] = $user['authfun'];

        //虚拟机要根据授权类型得到使用量
        //授权类型  host|cpu|storage|vm
        $info['vminfo'] = $this->getAuthVmInfo($data[0]);
        //跨平台虚拟化
        $info['v2v'] = $this->getAuthV2VInfo($data[0]);

        //文件保护
        $info['fileinfo'] = $this->getOneModuleLisenceInfo('file');

        //数据库保护
        $info['dbinfo'] = $this->getOneModuleLisenceInfo('database');

        //操作系统保护
        $info['osinfo'] = $this->getOneModuleLisenceInfo('os');

        //nas保护
        $info['nasinfo'] = $this->getOneModuleLisenceInfo('nas');
        //exhcnage
        $info['exchangeinfo'] = $this->getOneModuleLisenceInfo('exchange');

        //hadoop保护
        $info['hadoopinfo'] = $this->getOneModuleLisenceInfo('hadoop');

        //实时容灾
        $info['volcdpinfo'] = $this->getVolcdpLisenceInfo($data[0]);

        //对象存储保护
        $info['obsinfo'] = $this->getOneModuleLisenceInfo('obs');

        //文件保护|数据库|操作系统保护的无限制
        $info['fileinfo']['des'] = $this->getNumProtect($info['fileinfo'], $info['vminfo']['type']);
        $info['dbinfo']['des'] = $this->getNumProtect($info['dbinfo'], $info['vminfo']['type']);
        $info['osinfo']['des'] = $this->getNumProtect($info['osinfo'], $info['vminfo']['type']);
        $info['nasinfo']['des'] = $this->getNumProtect($info['nasinfo'], $info['vminfo']['type']);
        $info['exchangeinfo']['des'] = $this->getNumProtect($info['exchangeinfo'], $info['vminfo']['type']);
        $info['hadoopinfo']['des'] = $this->getNumProtect($info['hadoopinfo'], $info['vminfo']['type']);
        $info['obsinfo']['des'] = $this->getNumProtect($info['obsinfo'], $info['vminfo']['type']);
        //虚拟机保护，文件保护,数据库保护,跨平台保护,nas在存储模式下是否显示
        $info['extension']['p'] = $info['extension']['p'] ?? [];
        in_array('db_protect', $info['extension']['p']) ?
            $info['dbinfo']['showFlag'] = true : $info['dbinfo']['showFlag'] = false;
        in_array('fileprotect', $info['extension']['p']) ?
            $info['fileinfo']['showFlag'] = true : $info['fileinfo']['showFlag'] = false;
        in_array('vmprotect', $info['extension']['p']) ?
            $info['vminfo']['showFlag'] = true : $info['vminfo']['showFlag'] = false;
        $info['extension']['f']['crossHypervisorRecovery'] ?
            $info['v2v']['showFlag'] = true : $info['v2v']['showFlag'] = false;
        in_array('os_protect', $info['extension']['p']) ?
            $info['osinfo']['showFlag'] = true : $info['osinfo']['showFlag'] = false;
        in_array('nas_protect', $info['extension']['p']) ?
            $info['nasinfo']['showFlag'] = true : $info['nasinfo']['showFlag'] = false;
        in_array('vol_cdp_protect', $info['extension']['p']) ?
            $info['volcdpinfo']['showFlag'] = true : $info['volcdpinfo']['showFlag'] = false;
        in_array('office365_protect', $info['extension']['p']) ?
            $info['exchangeinfo']['showFlag'] = true : $info['exchangeinfo']['showFlag'] = false;
        in_array('hadoop_protect', $info['extension']['p']) ?
            $info['hadoopinfo']['showFlag'] = true : $info['hadoopinfo']['showFlag'] = false;
        in_array('obs_protect', $info['extension']['p']) ?
            $info['obsinfo']['showFlag'] = true : $info['obsinfo']['showFlag'] = false;

        //数存主机保护
        $info['dbtiming'] = $this->getAuthDatappInfo($status, $info['extension']);
        $info['dbtiming'] = $info['dbtiming'] ?? [];
        in_array('dbtiming', $info['extension']['p']) ?
            $info['dbtiming']['showFlag'] = true : $info['dbtiming']['showFlag'] = false;
        //数据库CDP和文件同步
        $info['file_cdp_auth'] = $this->getAuthCDPInfo($status, $info['extension'], 'file');
        $info['data_cdp_auth'] = $this->getAuthCDPInfo($status, $info['extension'], 'data');
        in_array('filecdpbackup', $info['extension']['p']) ?
            $info['file_cdp_auth']['showFlag'] = true : $info['file_cdp_auth']['showFlag'] = false;
        in_array('dbcdpbackup', $info['extension']['p']) ?
            $info['data_cdp_auth']['showFlag'] = true : $info['data_cdp_auth']['showFlag'] = false;

        //授权服务信息
        $info['server_auth'] = $this->getAuthServerInfo($info['extension']);

        //主控节点信息,主要用于国内展示用,合同里面会签
        $info['mastinfo'] = array(
            'mastflag' => true,
            'master_num' => 1,  //主节点默认1个
            'node_num' => intval($data[0]['node_count']),  //节点个数
            'mastdes' => xphp_get_lang('UI_SETTINGS_AUTH_NUM_DES') . '1',
        );

        //添加EDS容量授权标志
        $info['edslic'] = $this->getEDSLicenseFlag();
        $info['sncode'] = $this->getMachinSNCode();
        $info['edsShowFlag'] = true;    //是否显示EDS的内容
        //如果不显示标记文件存在，返回未授权，JS要用这个控制显示不显示
        $flagFilePath = xphp_get_config('app', 'TMP_PATH') . 'noeds';
        if (!file_exists($flagFilePath)) {
            $info['edsShowFlag'] = false;    //是否显示EDS的内容
        }
        return json_encode($info);
    }

    /**
     * 获取系统授权状态信息及授权到期时间
     * @param array  $data      //系统授权信息
     * @param int    $status    //授权状态
     * @param string $statusDes //授权状态描述
     * @return string[]|mixed[]
     */
    public function getSystemStatusInfo(array $data, int $status, string $statusDes)
    {
        //授权时间
        $registerTime = $this->parseDate($data['register_time']);
        //授权天数
        $days = $data['days'];
        //到期时间
        $expireTime = '----';
        //剩余天数
        $expireDays = 0;
        $dateformat = xphp_get_config('special', 'dateformat') ?? 'Y-m-d H:i:s';

        //授权状态
        $authFlag = xphp_get_config('auth', 'LISENCE_INFO')['authflag'];
        if ($status == $authFlag['authorized']) {
            //已授权
            $trialType = intval($data['trial_type']);
            if ($trialType == xphp_get_config('auth', 'TRIAL_TYPE')['TRIAL']) {
                $statusDes = xphp_get_lang('WEB_SYSTEM_TRIAL_AUTHIORIZED');
            } elseif ($trialType == xphp_get_config('auth', 'TRIAL_TYPE')['NORMAL']) {
                $statusDes = xphp_get_lang('WEB_SYSTEM_FORMAL_AUTHIORIZED');
                //海外版正式授权+有时间限制描述改成订阅授权
                $user = xphp_get_user_info();
                if ($user['language'] != 'zh-cn' && $user['language'] != 'zh-tw' && '-1' != $days) {
                    $statusDes = xphp_get_lang('WEB_SYSTEM_SUBSCRIPTION_AUTHIORIZED');
                }
            }

            //剩余天数 等于 授权天数 - 授权时间到当前时间的天数
            if ('-1' == $days) {
                $expireDays = xphp_get_lang('WEB_SYSTEM_AUTH_FOREVER');
                $expireTime = xphp_get_config('app', 'TIMESPACE');
            } else {
                $dayInterval = round((time() - strtotime($registerTime)) / 3600 / 24);
                $expireDays = ($days - $dayInterval) <= 0 ? 0 : ($days - $dayInterval);
                //到期天数等于注册时间+授权天数转换成时间
                $endTimeStamp = strtotime($registerTime) + ($days * 24 * 3600);
                $expireTime = date($dateformat, $endTimeStamp);
            }
        } elseif ($status == $authFlag['expire'] || $status == $authFlag['invalid']) {
            //到期天数等于注册时间+授权天数转换成时间
            $endTimeStamp = strtotime($registerTime) + ($days * 24 * 3600);
            $expireTime = date($dateformat, $endTimeStamp);
        }

        return array(
            'expireTime' => $expireTime,
            'statusDes' => $statusDes,
            'expireDays' => $expireDays
        );
    }

    /**
     * 得到页面授权信息显示信息
     * @param array $extension 数据
     * @return json
     */
    private function getLicenseInfoDetials($extension = []): array
    {
        $licenseBigTypeConf = xphp_get_config('auth', 'LICENSE_BIG_TYPE');
        $volcdpLisenceTypeConf = xphp_get_config('auth', 'VOLCDP_LISENCE_TYPE');

        //这里需要判断只有定时备份，和只有实时容灾的情况，两者都有就需要不同的显示
        //先检查是哪种显示方式
        $timingPageArr = array('vmprotect', 'fileprotect', 'db_protect', 'os_protect', 'nas_protect', 'office365_protect', 'hadoop_protect', 'obs_protect');
        $realtimePageArr = array('vol_cdp_protect');

        $allPage = $extension['p'];                             //所有授权的页面
        $licenseBigType = $extension['licenseBigType'];         //授权容量/数量类型
        $volcdpLisenceType = $extension['volcdpLisenceType'];  //CDP授权方式

        $timingFlag = false;
        $reltimeFlag = false;

        //检查是否有定时,有一个就算有
        foreach ($timingPageArr as $timingPage) {
            if (in_array($timingPage, $allPage)) {
                $timingFlag = true;
                break;
            }
        }
        //检查是否有实时,目前只有一个,后面可能会扩展,所以也做成了数组
        foreach ($realtimePageArr as $realtimePage) {
            if (in_array($realtimePage, $allPage)) {
                $reltimeFlag = true;
                break;
            }
        }

        $sql = "select cdp_max, license_type, storage_count, cdp_takeover_max_num, cdp_capacity_max from bd_license";
        $data = $this->dbSelect($sql, array());
        $timingStorage = $data[0]['storage_count'];
        $realtimeStorage = $data[0]['cdp_capacity_max'];
        $realtimeNum = $data[0]['cdp_max'];
        $realtimeTakeover = $data[0]['cdp_takeover_max_num'];

        $timingStorageDes = '';
        $realtimeStorageDes = '';

        if ($timingFlag && !$reltimeFlag) {
            //只有定时,用定时存储容量当总容量
            if ($licenseBigTypeConf['NUM'] == $licenseBigType || null == $licenseBigType) {
                //数量授权,null值兼容6.0tmp2以前授权数据
                $timingStorageDes = xphp_get_lang('UI_SETTINGS_AUTH_UNLIMITED');
                $timingUsedSizeInt = '-1';
                $timingTotalSize = '-1';
            } elseif ($licenseBigTypeConf['STORAGE'] == $licenseBigType) {
                //容量授权
                $usedSize = $this->getAllTimingUseStorageSize();
                $totalSize = v1_calsize($timingStorage, true);
                $timingStorageDes = $usedSize['des'] . '/' . $totalSize;
                $timingUsedSizeInt = intval($usedSize['value']);
                $timingTotalSize = intval($timingStorage);
            }
        }
        if (!$timingFlag && $reltimeFlag) {
            //只有实时,用实时存储容量当总容量,判断CDP的授权方式显示
            if ($volcdpLisenceTypeConf['NUM'] == $volcdpLisenceType || null == $volcdpLisenceType) {
                //数量授权
                $realtimeStorageDes = xphp_get_lang('UI_SETTINGS_AUTH_UNLIMITED');
                $realUsedSizeInt = '-1';
                $realTotalSize = '-1';
            } else {
                //容量授权
                $usedSize = $this->getVolcdpProtectData();
                $totalSize = v1_calsize($realtimeStorage, true);
                $realtimeStorageDes = $usedSize['des'] . '/' . $totalSize;
                $realUsedSizeInt = intval($usedSize['size']);
                $realTotalSize = intval($realtimeStorage);
            }
        }
        if ($timingFlag && $reltimeFlag) {
            //两者都有
            if ($licenseBigTypeConf['NUM'] == $licenseBigType || null == $licenseBigType) {
                //数量授权
                $timingStorageDes = xphp_get_lang('UI_SETTINGS_AUTH_UNLIMITED');
                $timingUsedSizeInt = '-1';
                $timingTotalSize = '-1';
                if ($volcdpLisenceTypeConf['NUM'] == $volcdpLisenceType || null == $volcdpLisenceType) {
                    //数量授权
                    $realtimeStorageDes = xphp_get_lang('UI_SETTINGS_AUTH_UNLIMITED');
                    $realUsedSizeInt = '-1';
                    $realTotalSize = '-1';
                } else {
                    //容量授权
                    $usedSize = $this->getVolcdpProtectData();
                    $totalSize = v1_calsize($realtimeStorage, true);
                    $realtimeStorageDes = $usedSize['des'] . '/' . $totalSize;
                    $realUsedSizeInt = intval($usedSize['size']);
                    $realTotalSize = intval($realtimeStorage);
                }
            } elseif ($licenseBigTypeConf['STORAGE'] == $licenseBigType) {
                //容量授权
                $usedSize = $this->getAllTimingUseStorageSize();
                $totalSize = v1_calsize($timingStorage, true);
                $timingStorageDes = $usedSize['des'] . '/' . $totalSize;
                $timingUsedSizeInt = intval($usedSize['value']);
                $timingTotalSize = intval($timingStorage);
                if ($volcdpLisenceTypeConf['NUM'] == $volcdpLisenceType || null == $volcdpLisenceType) {
                    //数量授权
                    $realtimeStorageDes = xphp_get_lang('UI_SETTINGS_AUTH_UNLIMITED');
                    $realUsedSizeInt = '-1';
                    $realTotalSize = '-1';
                } else {
                    //容量授权
                    $usedSize = $this->getVolcdpProtectData();
                    $totalSize = v1_calsize($realtimeStorage, true);
                    $realtimeStorageDes = $usedSize['des'] . '/' . $totalSize;
                    $realUsedSizeInt = intval($usedSize['size']);
                    $realTotalSize = intval($realtimeStorage);
                }
            }
        }

        return array(
            'timingFlag' => $timingFlag,
            'reltimeFlag' => $reltimeFlag,
            'timingStorageDes' => $timingStorageDes,
            'realtimeStorageDes' => $realtimeStorageDes,
            'licenseBigType' => $licenseBigType,
            'totalSize' => $totalSize,
            'realUsedSizeInt' => $realUsedSizeInt,
            'realTotalSize' => $realTotalSize,
            'timingUsedSizeInt' => $timingUsedSizeInt,
            'timingTotalSize' => $timingTotalSize,
        );
    }

    /**
     * 获取卷CDP模块保护数据
     * @return json
     */
    public function getVolcdpProtectData()
    {

        $sql = "SELECT SUM(cvbvs.backup_file_size + cvbvs.log_file_total_size) total 
                FROM cdp_vol_backup_vol_set cvbvs,cdp_vol_backup_agent cvba,bd_backup_timepoint bbt where
        cvbvs.backup_agent_id = cvba.id and cvba.timepoint_uuid = bbt.timepoint_uuid and bbt.user_uuid = ? and cvba.storage_location !=0";
        $data = $this->dbSelect($sql, array(xphp_get_user_info()['userUuid']));
        $total = $data[0]['total'];
        return $this->pGetIntToSizeInfo(intval($total));
    }

    /**
     * 获取大小转换成界面显示信息
     * @param unknown $size 大小
     * @return array
     */
    private function pGetIntToSizeInfo($size)
    {
        $writeSize = v1_calsize($size, true);
        $writeInfo = v1_calsize_to_value_and_unit($size, true);
        return array(
            'size' => $size,
            'value' => $writeInfo['value'],
            'unit' => $writeInfo['unit'],
            'des' => $writeSize
        );
    }

    /**
     * 获取所有定时备份使用的备份空间,不算副本和归档
     * @return array
     */
    private function getAllTimingUseStorageSize()
    {
        //         'BACKUP' => 1,              //备份
//         'DB_BACKUP' => 28,              //数据库备份
//         'VOL_CDP_BACKUP' => 32,      //卷CDP备份
//         'OS_BACKUP' => 35,  //OS备份

        $sql = "select sum(write_size) as size from bd_backup_timepoint 
                    where task_type = ? or task_type = ? or task_type = ?";
        $taskTypeConf = xphp_get_config('task', 'TASKTYPE');
        $sqlParams = array(
            $taskTypeConf['BACKUP'],
            $taskTypeConf['DB_BACKUP'],
            $taskTypeConf['OS_BACKUP'],
        );
        $data = $this->dbSelect($sql, $sqlParams);

        $size = $data[0]['size'] ?? 0;

        return array(
            'value' => $size,
            'des' => v1_calsize($size, true)
        );
    }

    /**
     * 获取虚拟机授权信息
     * @param array $data 数据
     * @return array
     */
    public function getAuthVmInfo($data = []): array
    {

        $licenseType = intval($data['license_type']);
        $vmTypeName = array('', 'host', 'cpu', 'storage', 'vm');
        $info = $this->getOneModuleLisenceInfo($vmTypeName[$licenseType], $licenseType);
        $info = $this->filterModuleInfo($licenseType, $info);
        $info['des'] = $info['used'] . '/' . $info['total'];

        //如果是容量授权,直接不限制
        if ($licenseType == xphp_get_config('auth', 'LISENCE_INFO')['type']['storage']) {
            $info['des'] = xphp_get_lang('UI_SETTINGS_AUTH_UNLIMITED');
        }

        return $info;
    }

    /**
     * 得到某个模块的授权信息
     * @param string $moduleName 模块名
     * @return array [total, used, valid]
     */
    public function getOneModuleLisenceInfo(string $moduleName): array
    {
        $moduleInfo = array(
            'total' => 0,
            'used' => 0,
            'valid' => 0,
        );
        $moduleNames = array(
            'file',
            'vm',
            'oracle',
            'sqlserver',
            'os',
            'cdp',
            'desktop',
            'host',
            'cpu',
            'storage',
            'node',
            'database',
            'nas',
            'exchange',
            'hadoop',
            'obs'
        );
        if (!in_array($moduleName, $moduleNames)) {
            //非法请求
            return $moduleInfo;
        }

        $total = $this->getModulesLisenceTotal();
        if ($total['authFlag'] != xphp_get_config('auth', 'LISENCE_INFO')['authflag']['authorized']) {
            //未授权或授权异常
            return $moduleInfo;
        }
        $used = $this->getModulesLisenceUsed();
        $valid = $this->getModulesLisenceValid();
        return array(
            'total' => intval($total['module'][$moduleName]),
            'used' => $used[$moduleName],
            'valid' => $valid[$moduleName],
        );
    }

    /**
     * 得到所有模块授权总数
     * @return array
     */
    public function getModulesLisenceTotal(): array
    {
        $authFlag = $this->getSystemAuthorizationStatus();
        $total = array(
            'authFlag' => $authFlag
        );
        if ($authFlag == xphp_get_config('auth', 'LISENCE_INFO')['authflag']['authorized']) {
            //如果是已授权状态
            $sql = "select file_max_num, vm_max_num, mysql_max_num, oracle_max_num,
                            sqlserver_max_num, os_max_num, nas_max_num, 
                                cdp_max, desktop_max, cpu_count, storage_count, node_count, exchange_user_max_num,hadoop_cluster_max_num, obs_max_num
                        from bd_license";
            $data = $this->dbSelect($sql);
            foreach ($data as $d) {
                $total['module'] = array(
                    'file' => $d['file_max_num'],
                    'vm' => $d['vm_max_num'],
                    'mysql' => $d['mysql_max_num'],
                    'oracle' => $d['oracle_max_num'],
                    'sqlserver' => $d['sqlserver_max_num'],
                    'os' => $d['os_max_num'],
                    'cdp' => $d['cdp_max'],
                    'desktop' => $d['desktop_max'],
                    'host' => $d['vm_max_num'],     //根据类型和虚拟机共用
                    'cpu' => $d['cpu_count'],
                    'storage' => $d['storage_count'],
                    'node' => $d['node_count'],
                    'database' => $d['oracle_max_num'],
                    'nas' => $d['nas_max_num'],
                    'exchange' => $d['exchange_user_max_num'],
                    'hadoop' => $d['hadoop_cluster_max_num'],
                    'obs' => $d['obs_max_num'],
                );
            }
        }
        //租户内部授权总数
        if (!empty($_SESSION['tenantuuid'])) {
            $tenantHandler = new Tenant();
            $config = $tenantHandler->pGetTenantSettings($_SESSION['tenantuuid']);
            $total['module']['nas'] = $config['nas_num'];
            $total['module']['vm'] = $config['vm_num'];
            $total['module']['file'] = $config['file_num'];
            $total['module']['db'] = $config['db_num'];
            $total['module']['exchange'] = $config['m365_num'];
            $total['module']['hadoop'] = $config['hadoop_num'];
            $total['module']['obs'] = $config['obs_num'];
            $total['module']['os'] = $config['os_num'];
        }

        return $total;
    }

    /**
     * 得到所有模块使用总数
     * @return array
     */
    public function getModulesLisenceUsed(): array
    {
        $used = array(
            'file' => 0,
            'vm' => 0,
            'mysql_max_num' => 0,
            'oracle' => 0,
            'sqlserver' => 0,
            'os' => 0,
            'cdp' => 0,
            'desktop' => 0,
            'host' => 0,
            'cpu' => 0,
            'storage' => 0,
            'node' => 0,
            'database' => 0,
            'nas' => 0,
            'exchange' => 0,
            'hadoop' => 0,
            'obs' => 0,
        );

        $sql = "select authorization_module from bd_agent";
        $data = $this->dbSelect($sql);
        //nas已授权的单独从nas_storage_resource中计算
        $nasUsed = $this->getNasUsedInfo();
        $used['nas'] = $nasUsed;
        //exchange授权需要从时间点中计算
        $used['exchange'] = $this->getExchangeAuth('', '');
        //hadoop授权需要从hadoop_cluster中计算
        $used['hadoop'] = $this->getHadoopAuth();
        //obs授权需要从obs_resource中计算
        $used['obs'] = $this->getObsAuth();
        foreach ($data as $d) {
            if (!empty($d['authorization_module'])) {
                $authInfo = json_decode($d['authorization_module'], true);
                $used['file'] += $authInfo['file'];
                $used['mysql'] += $authInfo['mysql'];
                $used['oracle'] += $authInfo['oracle'];
                $used['sqlserver'] += $authInfo['sqlserver'];
                $used['os'] += $authInfo['os'];
                $used['desktop'] += $authInfo['desktop'];
                $used['database'] += $authInfo['database'];
            }
        }
        //获取虚拟机模块的使用量
        $vmModuleUsed = $this->getVMModuleUsedArr();
        $used['vm'] = $vmModuleUsed['vm'];
        $used['cpu'] = $vmModuleUsed['cpu'];
        $used['host'] = $vmModuleUsed['host'];
        $used['storage'] = $vmModuleUsed['storage'];

        return $used;
    }

    /**
     * 获取exchange已使用用户数
     * @return int 已使用用户总数
     */
    public function getExchangeAuth($taskUuid = '', $userDes = '')
    {
        $userNum = 0;
        //获取m365所有备份任务
        $sql = "select mt.task_uuid from m365_task mt,bd_task bt where m365_type = 1 and bt.task_uuid = mt.task_uuid";
        if ($userDes != '') {
            $sql .= " and bt.user_uuid in('" . $userDes . "')";
        }
        if (!empty($taskUuid)) {
            $sql .= ' and mt.task_uuid != ? ';
            $taskList = $this->dbSelect($sql, array($taskUuid));
        } else {
            $taskList = $this->dbSelect($sql);
        }
        if (!empty($taskList)) {
            foreach ($taskList as $task) {
                //获取每个任务最新的时间点,解析出每个任务的最新时间点的授权用户数，然后相加
                $userNum += $this->getNewestTimepoit($task['task_uuid']);
            }
        }
        return $userNum;
    }

    /**
     * 获取hadoop已使用集群数
     * @return int 已使用集群数
     */
    private function getHadoopAuth()
    {
        $sql = "select distinct hadoop_cluster_uuid from hadoop_cluster where authorization = 1";
        if (!empty($_SESSION['tenantuuid'])) {
            $user = xphp_get_user_info();
            $hadoopUuids = (new ResourcesIndex())->pGetUserAllResource($user['userUuid'], 60);
            $hadoopUuids = array_column($hadoopUuids, 'resource_uuid');
            if (empty($hadoopUuids)) {
                return 0;
            } else {
                // $uuidArr 是一个一维数组
                $hadoopUuids = "'" . implode("','", $hadoopUuids) . "'";
                $sql .= " and hadoop_cluster_uuid in ($hadoopUuids)";
            }
        }
        $data = $this->dbSelect($sql);
        $count = count($data);
        return $count;
    }

    /**
     * 获取obs已使用授权数
     * @return int 已使用集群数
     */
    private function getObsAuth()
    {
        $sql = "select distinct obs_uuid from obs_resource where authorization = 1";

        if (!empty($_SESSION['tenantuuid'])) {
            $user = xphp_get_user_info();
            $obsUuids = (new ResourcesIndex())->pGetUserAllResource($user['userUuid'], 59);
            $obsUuids = array_column($obsUuids, 'resource_uuid');

            if (empty($obsUuids)) {
                return 0;
            } else {
                // $uuidArr 是一个一维数组
                $obsUuids = "'" . implode("','", $obsUuids) . "'";
                $sql .= " and obs_uuid in ($obsUuids)";
            }
        }

        $data = $this->dbSelect($sql);
        $count = count($data);
        return $count;
    }

    /**
     * 根据taskuuid获取授权用户数
     * @param string $taskUuid 任务uuid
     * @return int 用户数
     */
    public function getNewestTimepoit($taskUuid)
    {
        $sql = "select mbt.organization_info from m365_backup_timepoint mbt, bd_backup_timepoint bbt where bbt.available_flag = 1
        and bbt.timepoint_uuid = mbt.m365_timepoint_uuid and bbt.task_uuid = ? order by timepoint desc limit 0 , 1";
        $data = $this->dbSelect($sql, array($taskUuid));
        if (!empty($data)) {
            foreach ($data as $d) {
                $organizationInfo = json_decode($d['organization_info'], true);
                return intval($organizationInfo['current_reserverd_user_num']);
            }
        }
    }

    /**
     * 得到nas模块的使用量
     * @return int
     */
    private function getNasUsedInfo(): int
    {
        $sql = "select distinct ip from nas_storage_resource where authorization_status = 1";
        return count($this->dbSelect($sql));
    }

    /**
     * 得到虚拟机模块的使用量
     * @return string
     */
    private function getVMModuleUsedArr(): array
    {
        $vmUsed = array(
            'vm' => 0,
            'host' => 0,
            'storage' => 0,
            'cpu' => 0,
        );
        $authFlag = $this->getSystemAuthorizationStatus();
        if ($authFlag != xphp_get_config('auth', 'LISENCE_INFO')['authflag']['authorized']) {
            return $vmUsed;
        }
        $sql = "select license_type  from bd_license";
        $data = $this->dbSelect($sql);
        $licenseType = intval($data[0]['license_type']);
        $licenseTypeConf = xphp_get_config('auth', 'LISENCE_INFO')['type'];
        $sql = '';
        $sqlParams = array();
        $key = '';
        // 获取私有云(ZstackCloud除外)+公有云的虚拟化类型，按cpu或宿主机授权时排除掉这些只获取虚拟化的
        $cloudHypervisorArr = array_merge(xphp_get_config('vm', 'VMHYPERVISORGROUP')['openstack'], xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud']);
        $cloudHypervisorArr = array_diff($cloudHypervisorArr, [
            xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_ZSTACK'],
            xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_NEXAVM_NCSSV'],
        ]);
        $cloudHypervisorStr = implode(',', $cloudHypervisorArr);
        switch ($licenseType) {
            case $licenseTypeConf['host']:
                $sql = "select count(host_id) as total from vm_host vh join vm_vcenter vv on vh.vcenter_uuid = vv.vcenter_uuid 
                               where authorization_flag = ? and vv.hypervisor_type not in ({$cloudHypervisorStr})";
                $sqlParams = array(xphp_get_config('app', 'FLAG')['SET']);
                $key = 'host';
                break;
            case $licenseTypeConf['cpu']:
                $sql = "select sum(cpu_count) as total from vm_host vh join vm_vcenter vv on vh.vcenter_uuid = vv.vcenter_uuid 
                               where authorization_flag = ? and vv.hypervisor_type not in ({$cloudHypervisorStr})";
                $sqlParams = array(xphp_get_config('app', 'FLAG')['SET']);
                $key = 'cpu';
                break;
            case $licenseTypeConf['storage']:
                $sql = "select sum(write_size) as total from bd_backup_timepoint where module_type != ?";
                $sqlParams = array(xphp_get_config('module', 'MODULE_TYPE')['BACKUP_COPY_CLIENT']);
                $key = 'storage';
                break;
            case $licenseTypeConf['vm']:
                $sql = "select count(vml.vm_uuid) as total from vm_machine_list vml, bd_task bt 
                        where vml.task_uuid = bt.task_uuid and bt.task_type = ?";
                $sqlParams = array(xphp_get_config('task', 'TASKTYPE')['BACKUP']);
                $key = 'vm';
                break;
        }
        $data = $this->dbSelect($sql, $sqlParams);
        $count = $data[0]['total'];
        if (empty($count)) {
            $count = 0;
        }
        $vmUsed[$key] = $count;
        return $vmUsed;
    }

    /**
     * 得到所有模块可用总数
     * @return string
     */
    public function getModulesLisenceValid(): array
    {
        $valid = array(
            'file' => 0,
            'vm' => 0,
            'mysql' => 0,
            'oracle' => 0,
            'sqlserver' => 0,
            'os' => 0,
            'cdp' => 0,
            'desktop' => 0,
            'host' => 0,
            'cpu' => 0,
            'storage' => 0,
            'node' => 0,
            'database' => 0,
            'nas' => 0,
            'exchange' => 0,
            'hadoop' => 0,
            'obs' => 0,
        );
        $totalInfo = $this->getModulesLisenceTotal();
        if ($totalInfo['authFlag'] != xphp_get_config('auth', 'LISENCE_INFO')['authflag']['authorized']) {
            return $valid;
        }
        $used = $this->getModulesLisenceUsed();
        $total = $totalInfo['module'];
        return array(
            'file' => $total['file'] - $used['file'],
            'vm' => $total['vm'] - $used['vm'],
            'mysql' => $total['mysql'] - $used['mysql'],
            'oracle' => $total['oracle'] - $used['oracle'],
            'sqlserver' => $total['sqlserver'] - $used['sqlserver'],
            'os' => $total['os'] - $used['os'],
            'cdp' => $total['cdp'] - $used['cdp'],
            'desktop' => $total['desktop'] - $used['desktop'],
            'host' => $total['host'] - $used['host'],
            'cpu' => $total['cpu'] - $used['cpu'],
            'storage' => $total['storage'] - $used['storage'],
            'node' => $total['node'] - $used['node'],
            'database' => $total['database'] - $used['database'],
            'nas' => $total['nas'] - $used['nas'],
            'exchange' => $total['exchange'] - $used['exchange'],
            'hadoop' => $total['hadoop'] - $used['hadoop'],
            'obs' => $total['obs'] - $used['obs'],
        );
    }

    /**
     * 如果是虚拟机授权,需要转换存储类型的格式,如1TB
     * @param int   $licenseType 类型
     * @param array $moduleInfo  info
     * @return array
     */
    private function filterModuleInfo($licenseType, array $moduleInfo)
    {
        $moduleInfo['total_int'] = intval($moduleInfo['total']);
        $moduleInfo['used_int'] = intval($moduleInfo['used']);
        $moduleInfo['valid_int'] = intval($moduleInfo['valid']);
        if ($licenseType == xphp_get_config('auth', 'LISENCE_INFO')['type']['storage']) {
            $moduleInfo['total'] = v1_calsize($moduleInfo['total'], true);
            $moduleInfo['used'] = v1_calsize($moduleInfo['used'], true);
            $moduleInfo['valid'] = v1_calsize($moduleInfo['valid'], true);
        }

        $moduleInfo['type'] = $licenseType;
        return $moduleInfo;
    }

    /**
     * 获取跨平台恢复虚拟化授权信息
     * @param array $data 授权表信息
     * @return mixed|number[]|string[]
     */
    private function getAuthV2VInfo(array $data)
    {

        //跨平台虚拟化
        $v2vMaxNum = intval($data['desktop_max']); //跨平台虚拟化最多数量
        $v2vUsedNum = $this->getV2VUsedNum();
        $info = array(
            'total' => $v2vMaxNum,
            'used' => $v2vUsedNum,
            'valid' => $v2vMaxNum - $v2vUsedNum,
            'v2vdes' => xphp_get_lang('UI_SETTINGS_AUTH_NUM_DES') . $v2vUsedNum . '/' . $v2vMaxNum
        );
        //如果是不限制，修改描述
        if ('-1' == $v2vMaxNum) {
            $info['des'] = xphp_get_lang('UI_SETTINGS_AUTH_UNLIMITED');
        } else {
            $info['des'] = $info['used'] . '/' . $info['total'];
        }

        return $info;
    }

    /**
     * 得到v2v的使用量
     * @return string
     */
    public function getV2VUsedNum()
    {
        $sql = "select vc_lic_total_num from bd_system";
        $data = $this->dbSelect($sql);
        return !empty($data) ? intval($data[0]['vc_lic_total_num']) : 0;
    }

    /**
     * 获取实时容灾的授权显示
     * @param array $data 数据
     * @return string[]
     */
    private function getVolcdpLisenceInfo($data = []): array
    {

        $extension = json_decode(v1_decrypt($data['extension']), true);
        $volcdpLisenceType = $extension['volcdpLisenceType'];

        //获取接管使用量
        $takeoverUsedNum = $this->getVolcdpTakeoverNum();

        $volcdpLisenceTypeConf = xphp_get_config('auth', 'VOLCDP_LISENCE_TYPE');
        if ($volcdpLisenceType == $volcdpLisenceTypeConf['NUM']) {
            //数量授权
            $module = xphp_get_config('module', 'MODULE_TYPE')['VOL_CDP'];
            $productUsed = $this->getVolcdpProtectHost($module);      //使用的主机数
            $productDes = $productUsed . '/' . $data['cdp_max'];
        } else {
            //容量授权
            $productDes = xphp_get_lang('UI_SETTINGS_AUTH_UNLIMITED');
        }

        return array(
            'productDes' => $productDes,
            'takeoverDes' => $takeoverUsedNum . '/' . $data['cdp_takeover_max_num'],
        );
    }

    /**
     * 获取卷CDP模块已配置的接管机器
     * @return int
     */
    public function getVolcdpTakeoverNum(): int
    {
        $tasktype = xphp_get_config('task', 'TASKTYPE')['VOL_CDP_BACKUP'];
        $sql = 'SELECT COUNT(*) total FROM bd_task BT, cdp_vol_task CVL 
            WHERE CVL.task_uuid = BT.task_uuid and BT.task_type = ?
              and (CVL.standby_agent_uuid <> "" or CVL.auto_takeover_flag = ?)
              and BT.user_uuid = ?';
        $data = $this->dbSelect(
            $sql,
            array($tasktype, xphp_get_config('app', 'FLAG')['SET'], xphp_get_user_info()['userUuid'])
        );
        return intval($data[0]['total']);
    }

    /**
     * 获取卷CDP模块的主机数
     * @param unknown $module 模块
     * @return int
     */
    public function getVolcdpProtectHost($module): int
    {
        $tasktype = xphp_get_config('task', 'TASKTYPE')['VOL_CDP_BACKUP'];
        $sql = "SELECT count(task_uuid) as total 
                from bd_task where task_type = ? and module_type = ? and user_uuid = ?";
        $data = $this->dbSelect($sql, array($tasktype, $module, xphp_get_user_info()['userUuid']));
        return intval($data[0]['total']);
    }

    /**
     * 如果授权是存储模式 文件保护与数据库保护为无限制
     * @param array $data //文件保护或数据库数据
     * @param int   $flag //授权状态
     * @return string[]|mixed[]
     */
    private function getNumProtect(array $data, $flag)
    {
        if ($flag == 3) {
            $des = xphp_get_lang('UI_SETTINGS_AUTH_UNLIMITED');
        } else {
            $des = $data['used'] . '/' . $data['total'];
        }
        return $des;
    }

    /**
     * 获取数存主机保护返回授权信息
     * @param intval $status    备份系统授权状态
     * @param $extension Extension
     * @return array|string[]
     */
    private function getAuthDatappInfo($status, $extension)
    {
        $info = [];

        //获取 数据库定时信息
        if ($extension['dbtiminglic'] && $status == xphp_get_config('auth', 'LISENCE_INFO')['authflag']['authorized']) {
            $dbTimingHandler = new DbTiming();
            if (!$dbTimingHandler->authKey || time() > $dbTimingHandler->authTime) {
                $dbTimingHandler->getDBAuth(); //获取datapp认证
            }
            $dbTimingInfo = $dbTimingHandler->getDBLisenceInfo(); //获取datapp授权信息
            if (!empty($dbTimingInfo['agent'])) {
                $databasesize = 0;
                $databasefree = 0;
                foreach ($dbTimingInfo['agent'] as $agent) {
                    $databasesize += $agent['size'];
                    $databasefree += $agent['free'];
                }
                if ($databasesize != 0) {
                    $database = ($databasesize - $databasefree) . '/' . $databasesize;
                    $info = array(
                        'database' => $database
                    );
                }
            }
        }

        return $info;
    }

    /**
     * 获取数据库CDP和文件同步信息
     * @param intval $status    备份系统授权状态
     * @param $extension extension
     * @param $flag      flag
     * @return array|string[]|mixed[]|number[][]|unknown[]
     */
    public function getAuthCDPInfo($status, $extension, $flag)
    {
        $info = [];
        //获取数据库实时信息

        if ($extension['dbcdplic'] && $status == xphp_get_config('auth', 'LISENCE_INFO')['authflag']['authorized']) {
            $hostip = xphp_get_config('app', 'DB_CDP_LICENSE_IP');
            $rpc = new DbRpc();
            $data = array();
            //是否获取cdp授权信息
            $result = $rpc->getLicenseCenterAuthorInfo($hostip, $data);
            if ($result['result']) {
                $cdpInfo = $this->getDBcdpLicenseInfo();
                $producthost = $standbyhost = $fsproducthost = $fsstandbyhost = xphp_get_config('app', 'NULLSPACE');

                if ($cdpInfo['total']['producthost'] != 0) {
                    $producthost = $cdpInfo['used']['producthost'] . '/' . $cdpInfo['total']['producthost'];
                }
                if ($cdpInfo['total']['standbyhost'] != 0) {
                    $standbyhost = $cdpInfo['used']['standbyhost'] . '/' . $cdpInfo['total']['standbyhost'];
                }
                if ($cdpInfo['total']['takeover'] != 0) {
                    $takeover = $cdpInfo['used']['takeover'] . '/' . $cdpInfo['total']['takeover'];
                }
                if ($cdpInfo['total']['fsproducthost'] != 0) {
                    $fsproducthost = $cdpInfo['used']['fsproducthost'] . '/' . $cdpInfo['total']['fsproducthost'];
                }
                if ($cdpInfo['total']['fsstandbyhost'] != 0) {
                    $fsstandbyhost = $cdpInfo['used']['fsstandbyhost'] . '/' . $cdpInfo['total']['fsstandbyhost'];
                }
                $cdpendtime = $cdpInfo['endtime'];
                if (!empty($cdpendtime)) {
                    if ($flag == 'file') {
                        $info = array(
                            'fsproducthost' => $fsproducthost,
                            'fsstandbyhost' => $fsstandbyhost,
                        );
                    } elseif ($flag == 'data') {
                        $info = array(
                            'endtime' => $cdpendtime,
                            'producthost' => $producthost,
                            'standbyhost' => $standbyhost,
                            'takeover' => $takeover,
                        );
                    }
                }
            }
        }

        return $info;
    }

    /**
     * 获取数据库CDP授权信息
     * @return array
     */
    public function getDBcdpLicenseInfo()
    {
        $dbCDPHandler = new DbCdp();
        $result = $dbCDPHandler->getLicenseCenterAuthorInfo();
        $totalArr = $result[0]['units'];
        $usedArr = $result[0]['alloced'];
        $info = array(
            'endtime' => '',
            'total' => array(
                'producthost' => 0,
                'standbyhost' => 0,
                'takeover' => 0,
                'fsproducthost' => 0,
                'fsstandbyhost' => 0,
            ),
            'used' => array(
                'producthost' => 0,
                'standbyhost' => 0,
                'takeover' => 0,
                'fsproducthost' => 0,
                'fsstandbyhost' => 0,
            )
        );
        if (!empty($result)) {
            $dbcdpsyscode = xphp_get_config('db', 'DB_CDP_SYSCODE');
            foreach ($totalArr as $total) {
                if ($total['syscode'] == $dbcdpsyscode['producthost']) {
                    $info['total']['producthost'] = $total['authors'];
                }
                if ($total['syscode'] == $dbcdpsyscode['standbyhost']) {
                    $info['total']['standbyhost'] = $total['authors'];
                }
                if ($total['syscode'] == $dbcdpsyscode['takeover']) {
                    $info['total']['takeover'] = $total['authors'];
                }
                if ($total['syscode'] == $dbcdpsyscode['fileproduct']) {
                    $info['total']['fsproducthost'] = $total['authors'];
                }
                if ($total['syscode'] == $dbcdpsyscode['filestandby']) {
                    $info['total']['fsstandbyhost'] = $total['authors'];
                }
                $info['endtime'] = $total['etime'];
            }

            foreach ($usedArr as $used) {
                if ($used['syscode'] == $dbcdpsyscode['producthost']) {
                    $info['used']['producthost'] = $info['used']['producthost'] + 1;
                }
                if ($used['syscode'] == $dbcdpsyscode['standbyhost']) {
                    $info['used']['standbyhost'] = $info['used']['standbyhost'] + 1;
                }
                if ($used['syscode'] == $dbcdpsyscode['takeover']) {
                    $info['used']['takeover'] = $info['used']['takeover'] + 1;
                }
                if ($used['syscode'] == $dbcdpsyscode['fileproduct']) {
                    $info['used']['fsproducthost'] = $info['used']['fsproducthost'] + 1;
                }
                if ($used['syscode'] == $dbcdpsyscode['filestandby']) {
                    $info['used']['fsstandbyhost'] = $info['used']['fsstandbyhost'] + 1;
                }
            }
        }
        return $info;
    }

    /**
     * 获取服务授权信息
     * @param $extension extension
     * @return boolean[]|NULL[]|mixed[]|unknown[]
     */
    public function getAuthServerInfo($extension)
    {
        $settingsHandler = new Settings();
        //获取服务授权文件信息
        $data = $settingsHandler->getSettingsInfos(xphp_get_config('app', 'SETTINGS_CONF')['SERVICE']);
        if (!empty($data)) {
            $serverInfo = json_decode(v1_decrypt($data[0]['settings_content']), true);
        }
        $info = array(
            'serverType' => xphp_get_config('app', 'NULLSPACE'),
            'serverTime' => xphp_get_config('app', 'NULLSPACE'),
            'serviceFlag' => false,
        );
        if (!empty($serverInfo)) {
            $serverDes = xphp_get_config('auth', 'LISENCE_INFO')['servertype'][$serverInfo['serviceType']];
            if (!empty($serverInfo['serviceDiyName'])) {
                $serverDes = $serverInfo['serviceDiyName'];
            }
            $info = array(
                'serverType' => $serverDes,
                'serverTime' => $serverInfo['serviceTime'],
                'serviceFlag' => $serverInfo['serviceFlag'],
            );
        }

        return $info;
    }

    /**
     * 得到EDS授权标志,
     * 已授权  true
     * 未授权 false
     * @return bool
     */
    public function getEDSLicenseFlag()
    {
        //文件存在即表示已经授权
        $filePath = xphp_get_config('app', 'TMP_PATH') . 'eds.lic';
        return file_exists($filePath);
    }

    /**
     * 得到机器SN号
     * @return string
     */
    public function getMachinSNCode()
    {
        $cmd = 'cat /sys/class/dmi/id/chassis_vendor';
        exec($cmd, $info, $returnval);
        //先判断是否是SANGFOR
        if (false === strpos($info[0], 'SANGFOR')) {
            //如果不是深信服,返回空
            return 'unknown';
        }
        //如果是深信服,再获取
        $cmd = 'cat /sys/class/dmi/id/board_serial';
        exec($cmd, $info, $returnval);
        if (10 == strlen($info[0])) {
            //如果是10个字符,这个就是SN号
            return $info[0];
        } else {
            //如果不是10个字符,从board_asset_tag获取
            $cmd = 'cat /sys/class/dmi/id/board_asset_tag';
            exec($cmd, $info, $returnval);
            if ('None' != $info[0]) {
                return $info[0];
            } else {
                return '';
            }
        }
        return '';
    }

    /**
     * 添加宿主机授权
     * @param array $params 参数
     * @return string
     */
    public function addHostAuth(array $params): string
    {
        $hosts = [];
        foreach ($params['host_uuids'] as $hostUuid) {
            $hosts[] = [
                'vcenter_uuid' => $params['platform_uuid'],
                'vm_host_uuid' => $hostUuid
            ];
        }
        $this->paramsCheck($hosts);
        $msg = array('host_list' => $hosts);
        $opName = 'PT_LICENSE_OP_ADD_VM_LICENSE';
        $unifyMsg = $this->unifyMsg($opName, json_encode($msg));
        foreach ($hosts as $host) {
            $this->writeSystemLogHostAuth(
                'BD_SYSTEMLOG_DESC_KEY_LISENCE_HOST',
                $unifyMsg,
                $host['vm_host_uuid'],
                $host['vcenter_uuid']
            );
        }
        return $unifyMsg;
    }

    /**
     * 取消宿主机授权
     * @param array $params 参数
     * @return string
     */
    public function deleteHostAuth(array $params): string
    {
        $hosts = [];
        foreach ($params['host_uuids'] as $hostUuid) {
            $hosts[] = [
                'vcenter_uuid' => $params['platform_uuid'],
                'vm_host_uuid' => $hostUuid
            ];
        }
        $this->paramsCheck($hosts);
        $msg = array('host_list' => $hosts);
        $opName = 'PT_LICENSE_OP_DEL_VM_LICENSE';
        $unifyMsg = $this->unifyMsg($opName, json_encode($msg));
        foreach ($hosts as $host) {
            $this->writeSystemLogHostAuth(
                'BD_SYSTEMLOG_DESC_KEY_LISENCE_UNHOST',
                $unifyMsg,
                $host['vm_host_uuid'],
                $host['vcenter_uuid']
            );
        }
        return $unifyMsg;
    }

    /**
     * 统一消息处理结果,流程为:发送消息到SOCKET,接收SOCKET消息,处理接收到的SOCEKT消息,统一接口发送到UI
     * @param string $opName  操作码
     * @param string $jsonMsg json消息
     * @param bool   $sync    是否同步，默认异步
     * @return string
     */
    private function unifyMsg(string $opName, string $jsonMsg, $sync = false): string
    {
        $mbResult = $this->mbPFMsg($opName, $jsonMsg, $sync);
        $result = $mbResult['result'];
        $operate = (new PfOpcodePrivate())->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 添加/取消宿主机授权写系统日志
     * @param string $descriptionKey 日志键名
     * @param string $result         操作结果
     * @param string $hostUuid       宿主机UUID
     * @param string $platformUuid   虚拟化平台UUID
     * @return void
     */
    private function writeSystemLogHostAuth(
        string $descriptionKey,
        string $result,
        string $hostUuid,
        string $platformUuid
    ): void {
        $sql = "select host_name, host_ip from vm_host where vcenter_uuid = ? and host_uuid = ?";
        $data = $this->dbSelect($sql, array($platformUuid, $hostUuid));
        $descriptionParam = array($data[0]['host_name'], $data[0]['host_ip']);
        $result = json_decode($result, true);
        if ($result['re']) {
            $this->systemLog($descriptionKey, $descriptionParam);
        } else {
            $this->systemLog($descriptionKey, $descriptionParam, xphp_get_config('log')['LOGLEVEL']['ERROR']);
        }
    }

    /**
     * 获取机器指纹(无权限检查,慎用)
     * @return string
     */
    public function getThumbprintIDNoPermission()
    {
        $opName = 'PT_LICENSE_OP_QUERY_THUMBPRINT';
        $mbResult = $this->mbPFMsg($opName, json_encode(''), true);
        $result = $mbResult['result'];
        if ($result) {
            return $mbResult['msg']['thumbprint'];
        } else {
            return '--';
        }
    }

    /**
     * 上传LISENCE文件
     * @param array $params 参数
     * @return array
     */
    public function uploadLisence($params)
    {
        if (empty($params['content'])) {
            //需要检查路径是否对的
//            $key = file_get_contents($_FILES['files']['tmp_name']);
        } else {
            $key = $params['content'];
        }
        $info = $this->submitLisenceKey($key);
        return $info;
    }

    /**
     * 提交授权码
     * @param string $key key
     * @return array
     */
    public function submitLisenceKey($key)
    {
        $key = v1_decrypt($key);
        $key = json_decode($key, true);
        //vinchin license,数据库定时license,数据库实时license
        $filetype = $key['filetype'];
        $vinchinLic = $key['v'];
        $dbtimingLic = $key['d'];
        $dbcdpLic = $key['c'];
        $serviceLic = $key['s'];
        $opName = 'PT_LICENSE_OP_ADD_LICENSE';
        $opcodeHandler = new NodeOpcode();
        $operate = $opcodeHandler->getOpcodeDes($opName);
        $info = array(
            'success' => true,
            'code' => 0,
            'message' => $operate,
            'data' => array(),
        );
        //如果只是上传的服务授权文件,直接到服务授权文件处理.
        if ($filetype == xphp_get_config('auth', 'LISENCE_INFO')['filetype']['service']) {
            $result = $this->serviceLisenceKey($serviceLic, $operate);
            if ($result) {
                $info['message'] = $operate;
                return $info;
            } else {
                $info['success'] = false;
                $info['message'] = xphp_get_lang('UI_SH_AUTHORIZATION_FAIL');
                return $info;
            }
        }
        //提交数据库实时
        $cmdStr = 'ps aux|grep lzbackupsys';
        exec($cmdStr, $info);
        if (count($info) > 2) {
            //授权数据库实时,先看是否有
            if (!empty($dbcdpLic)) {
                $result = (new DbCdp())->upLicenseFile($dbcdpLic);
                if (!$result['result']) {
                    //授权失败
                    $info['success'] = false;
                    $info['message'] = $result['errorMsg'];
                    $info['code'] = $result['errorCode'];
                    return $info;
                }
            }
        }
        //提交数据库定时
        $cmdStr = 'ps aux|grep daserver';
        exec($cmdStr, $info);
        $dbTimingHandler = new DbTiming();
        if (count($info) > 2) {
            if (!empty($dbtimingLic)) {
                if (!$dbTimingHandler->authKey || time() > $dbTimingHandler->authTime) {
                    $dbTimingHandler->getDBAuth(); //获取datapp认证
                }
                $result = $dbTimingHandler->uploadDBLicense($dbtimingLic); //授权datapp
                $result = json_decode($result, true);
                if (0 != $result['code']) {
                    //授权失败
                    $info['success'] = false;
                    $info['message'] = $result['info'];
                    $info['code'] = 60000 + $result['code'];
                    return $info;
                }
            }
        }
        $msg = array('license' => $key['v']);
        $mbResult = $this->mbPFMsg($opName, json_encode($msg), false);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            $extension = $this->getExtensionLicense();
            if (!empty($extension['f'])) {
                session_start();
                $_SESSION['authfun'] = $extension['f'];
                session_commit();
            }
            $this->systemLog('PT_SYSTEMLOG_DESC_KEY_UPDATE_LICENSE');
            //系统授权成功后,处理服务授权
            $result = $this->serviceLisenceKey($serviceLic, $operate);
            if (!$result) {
                $info['success'] = false;
                $info['code'] = 0;
                $info['message'] = xphp_get_lang('UI_SH_AUTHORIZATION_FAIL');
                return $info;
            }
            $this->updateUserPermissionAndSoftwareType();
            $info['success'] = true;
            $info['message'] = $msg;
            return $info;
        } else {
            $info['success'] = false;
            $info['code'] = $mbResult['errorCode'];
            $info['message'] = $msg;
            return $info;
        }
    }

    /**
     * 处理服务授权文件
     * @param mixed $serviceLic lic
     * @param mixed $operate    未知
     * @return boolean
     */
    private function serviceLisenceKey($serviceLic, $operate)
    {
        //检查机器指纹,保存文件
        $opName = 'PT_LICENSE_OP_QUERY_THUMBPRINT';
        //        $mbResult = $this->mbPFMsg($opName, null, false);
//        exit();
//        $result = $mbResult['result'];
//        $msg = $mbResult['msg']['thumbprint'];
//        if($msg != $serviceLic['thumbprint'] && $serviceLic['thumbprint'] != "-1"){
//            $info = array(
//                'success' => false,
//                'code' => 0,
//                'message' => xphp_get_lang('UI_SH_FINGERPRINT_NO_SAME'),
//                'data' => array(),
//            );
//            //机器指纹不一致
//            exit($info);
//        }
        //加密存储服务授权信息到文件
        $serviceLic = v1_encrype(json_encode($serviceLic, true));
        //配置文件保存到数据库
        //获取服务授权文件信息
        $data = $this->getSettingsInfos(xphp_get_config('app', 'SETTINGS_CONF')['SERVICE']);
        if (empty($data)) {
            //如果数据库没有
            $result = $this->addSettingsInfos(xphp_get_config('app', 'SETTINGS_CONF')['SERVICE'], $serviceLic);
        } else {
            $result = $this->modifySettingsInfosWithType(
                xphp_get_config('app', 'SETTINGS_CONF')['SERVICE'],
                $serviceLic
            );
        }
        return $result;
    }

    /**
     * 获取某个类型的所有配置信息
     * @param int $settingsType 配置类型
     * @return mixed
     */
    public function getSettingsInfos($settingsType)
    {

        $sql = "SELECT settings_id, settings_type, settings_content, modify_time, user_uuid
                FROM bd_system_settings
                WHERE settings_type = ?";
        $data = $this->dbSelect($sql, array($settingsType));
        return $data;
    }

    /**
     * 添加某个系统配置信息
     * @param int    $settingsType    配置类型
     * @param string $settingsContent 配置内容,json
     * @return boolean
     */
    public function addSettingsInfos($settingsType, $settingsContent)
    {

        $sql = "INSERT INTO bd_system_settings (settings_type, settings_content, modify_time, user_uuid)
                VALUES (?, ?, ?, ?) ";
        $sqlParams = array($settingsType, $settingsContent, date('Y-m-d H:i:s'), xphp_get_user_info()['userUuid']);
        $result = $this->dbExec($sql, $sqlParams);

        return $result;
    }

    /**
     * 修改某个配置的配置信息,通过type类型
     * @param int    $settingsType    配置类型
     * @param string $settingsContent 配置内容,json
     * @return mixed
     */
    public function modifySettingsInfosWithType($settingsType, $settingsContent)
    {
        $sql = "UPDATE bd_system_settings
                SET settings_content = ?, modify_time = ?, user_uuid = ?
                WHERE settings_type = ? ";
        $sqlParams = array($settingsContent, date('Y-m-d H:i:s'), xphp_get_user_info()['userUuid'], $settingsType);
        $result = $this->dbExec($sql, $sqlParams);

        return $result;
    }

    /**
     * 系统信息改变后需要更新admin 的 session信息
     * 软件通过授权后可以在版本见切换
     * @return boolean
     */
    public function updateUserPermissionAndSoftwareType()
    {
        $extension = $this->getExtensionLicense();
        $pagelist = array();
        $authFun = array(
            'visualization' => false,
            'lanfree' => false,
            'dedupication' => false,
            'vcbt' => false,
            'nodeExtend' => false,
            'grain' => false,
            'copy' => false,
            'archive' => false,
        );
        if (!empty($extension)) {
            $pagelist = $extension['p'];
            $authFun = $extension['f'];
        }
        session_start();
        $userType = intval($_SESSION['userType']);
        if ($userType < xphp_get_config('user', 'USERTYPE')['manager']) {
            //如果是操作员和审计员,不需要做更新
            session_commit();
            return true;
        }
        $softwareType = $this->getSoftwareType();
        $roleHandler = new User();
        $_SESSION['permission'] = $roleHandler->pGetUserAllPermission($_SESSION['userUuid'], true);
        $_SESSION['softwareType'] = $softwareType;
        session_commit();
        return true;
    }

    /**
     * 获取license的extension解密后的数据
     * (!!!!!注意)后台有调用
     * @param array $params 参数
     * @return array
     */
    public function getDecryptLisence(array $params)
    {

        $return = ['code' => 1, 'msg' => xphp_get_lang('WEB_LINCESE_VERIFY_FAIL'), 'data' => 2];
        if (empty($params['k']) || $params['k'] != xphp_get_config('app', 'API_MAGIC')) {
            // 参数校验
            return $return;
        }
        $type = $params['type'] ?? 1; // 默认为三权
        $configArr = [
            1 => [ // 三权
                'type' => 'f', // 所在位置
                'name' => 'threepowers' // 标识名称
            ],
            2 => [ // 内嵌虚拟化
                'type' => 'p',
                'name' => 'vm_machine_manager',
            ],
        ];
        if (empty($configArr[$type])) {
            return $return;
        }
        $return['code'] = 0;
        $return['msg'] = xphp_get_lang('WEB_LINCESE_GET_SUCCESS');
        $sql = "select extension from bd_license";
        $data = dbSelect($sql, array());
        if (!empty($data)) {
            $data = json_decode(v1_decrypt($data[0]['extension']), true);
            $f = $configArr[$type];
            if ($f['type'] == 'p') {
                // 要判断是否在里面
                if (in_array($f['name'], $data[$f['type']])) {
                    $return['data'] = 1;
                }
            } elseif (!empty($data[$f['type']][$f['name']])) {
                $return['data'] = 1;
            }
        }
        return $return;
    }

    /**
     * 生成文件的下载路径
     * @param array $params 参数
     * @return array
     */
    public function generateDownloadFilepath(array $params): array
    {
        $nodeHandle = new Node();
        return $this->sendResult('', true, 200, [
            'url' => $this->groupUnifyDownloadUrl(
                $nodeHandle->getLocalNodeUUID(),
                $params['filepath'],
                $params['delete_flag'] ?? false,
                $params['filename'] ?? false
            )
        ]);
    }

    /**
     * 生成统一下载链接
     * @param string  $nodeUuid   节点uuid
     * @param string  $filepath   文件路径
     * @param boolean $deleteFlag 是否删除
     * @param string  $filename   下载显示的文件名
     * @link \app\v1\resources\v0\logic\Client::downloadClientLog
     * @return string
     */
    public function groupUnifyDownloadUrl(
        string $nodeUuid,
        string $filepath,
        bool $deleteFlag = false,
        string $filename = ''
    ): string {
        //目前只检查用户名和文件名,之后还可以加上下载有效期等其他信息
        $ivc = array(
            // 'username' => xphp_get_user_info()['userName'],
            'node_uuid' => $nodeUuid,
            'filepath' => $filepath,
            'deleteFlag' => $deleteFlag,
            'filename' => $filename,
        );

        $ivc = v1_encrype(json_encode($ivc));
        return '/api/v1/system/download?p=' . urlencode($ivc) . '&x-api-version=1.0-rev0';
    }

    /**
     * 下载文件统一入口，功能为获取到ivc校验码，然后解析校验码，判断校验码的用户和文件，然后输出文件
     * @param string $p 下载参数
     * @return void
     */
    public function downloadFile(string $p)
    {
        $ivc = v1_decrypt($p);
        $ivc = json_decode($ivc, true);

        //检查用户名是否匹配, 免登录接口不能检测用户
        // if ($ivc['username'] != xphp_get_user_info()['userName']) {
        //     return;
        // }

        //检查文件是否存在
        if (!file_exists($ivc['filepath'])) {
            return;
        }

        //下载文件
        if (!empty($ivc['filename'])) {
            $filename = $ivc['filename'];
        } else {
            $filename = basename($ivc['filepath']);
        }
        $filesize = filesize($ivc['filepath']);

        //下载文件到本地
        Header('Content-type: application/octet-stream');
        Header('Accept-Ranges: bytes');
        Header('Accept-Length: ' . $filesize);
        Header('Content-Disposition: attachment; filename=' . $filename);

        $blockSize = xphp_get_config('task', 'GRAIN_FILE_BLOCK_SIZE');
        //如果大于分块大小,分块下载
        for ($i = 0; $i < $filesize; $i = $i + $blockSize) {
            if ($filesize - $i <= $blockSize) {
                $readLen = $filesize - $i;
            } else {
                $readLen = $blockSize;
            }
            $mbResult = $this->service()->pReadFileService($ivc['filepath'], $i, $readLen, $ivc['node_uuid']);
            echo $mbResult;
            ob_flush(); //将数据从php的buffer中释放出来
            flush(); //将释放出来的数据发送给浏览器
        }

        if (isset($ivc['deleteFlag']) && $ivc['deleteFlag']) {
            unlink($ivc['filepath']);
        }
    }

    /**
     * 得到虚拟机授权的基本信息
     * @return mixed
     */
    public function getVMLicenseInfo()
    {
        $systemLisence = json_decode($this->getSystemLisenceInfo(), true);
        return $systemLisence['vminfo'];
    }

    /**
     * 重启后台服务
     * @param string $nodeuuid uuid
     * @param string $extraCMD 信息
     * @param int    $type     节点类型 1主节点
     * @return boolean
     */
    public function restartService(string $nodeuuid, $extraCMD = '', $type = 1)
    {

        $cmd = '';
        if (!empty($extraCMD)) {
            $cmd .= $extraCMD;
            $cmd .= 'sleep 5s;';
        }
        $this->restartDbcdpServer($nodeuuid);
        $cmd .= 'systemctl restart mysql;systemctl restart NetworkManager;' .
            'systemctl restart network;';

        $vendor = xphp_get_config('app', 'SYSTEM_INFO')['vendor'];
        // 子节点
        $cmd .= '/bin/bash /etc/' . $vendor . '/service_controller.sh restart all_service';

        // 主节点多个master
        $sql = "select node_type from bd_node where node_uuid = ?";
        $data = $this->dbSelect($sql, array($nodeuuid));
        if ($data[0]['node_type'] == xphp_get_config('app')['NODETYPE']['MASTER']) {
            $cmd .= ' master';
        }
        $cmd .= ';';

        $opName = 'NODE_SYS_OP_DO_CMD';
        $msg = array('command' => $cmd);
        return $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), false, true);
    }

    /**
     * 重启数据库实时和定时服务
     * @param string $nodeuuid uuid
     * @return string
     */
    private function restartDbcdpServer(string $nodeuuid)
    {
        $cmd = 'ps aux|grep lzbackupsys';
        $opName = 'NODE_SYS_OP_DO_CMD';
        $msg = array('command' => $cmd);
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), false, true);
        $countDb = !empty($mbResult['msg']['details']) ? count($mbResult['msg']['details']) : 0;
        $cmd = '';
        if ($countDb > 2) {
            $list = explode(' ', $mbResult['msg']['details'][0]);
            $proceID = $list[4];
            $cmd .= 'kill -9 ' . $proceID;
            $cmd .= ';systemctl restart daserver;systemctl restart dastorage;';
        } else {
            $cmd .= 'systemctl restart daserver;systemctl restart dastorage;';
        }
        $msg = array('command' => $cmd);
        return $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), false, true);
    }

    /**
     * 得到虚拟化类型配置
     * @return array
     */
    private function getConfigVMType(): array
    {
        //name 是在JS里面的键名
        $config = xphp_get_config('vm', 'VMHYPERVISORTYPE');
        $name = array(
            'UNKNOWN',
            'VMWARE',
            'HYPERV',
            'CITRIX',
            'KVM',
            'XEN',
            'ORACLEVM',
            'CLOUDVIEW',
            'INCLOUD',
            'VGATE',
            'NEOKYLIN',
            'H3C',
            'SANGFOR',
            'SDCOS',
            'FLEXCLOUD',
            'OPENSTACK',
            'FUSIONKVM',
            'FUSIONXEN',
            'WINSERVER',
            'RHV',
            'DSERVER',
            'CLOUDVIEWSVM',
            'FLEXHCS',
            'OSEASYVSERVER',
            'INCLOUDKVM',
            'WINDIY',
            'ZSTACK',
            'EASTEDVSERVER',
            'XCPNG',
            'OLVM',
            'XSKY',
            'INCLOUDOPENSTACK',
            'WINHONGKVM',
            'SMARTX',
            'SUGONCLOUDVIEW',
            'INSPURCLOUDPLATFORM',
            'EASYSTACK',
            'FIBERHOMEOPENSTACK',
            'CTSIOPENSTACK',
            'AWCLOUD',
            'INSPURVVDK',
            'ZVIRT',
            'PROXMOX',
            'XFUSIONKVM',
            'XHERE',
            'HOSTVM',
            'HUAWEICBR',
            'SANGFORVVDK',
            'CLOUDVIEWKVM',
            'REDVIRT',
            'ROSAVIRT',
            'H3CCASCVD',
            'OVIRT',
            'LENOVOAIO',
            'HUAWEICLOUDSTACK',
            'VOLC',
            'ZSTACKZSPHERE',
            'KSPHERE',
            'ARCFRA',
            'NEXAVM',
            'NEXAVMNCSSV',
            100 => 'AWS',
            'HUAWEICLOUD'
        );
        $newConfig = [];
        foreach ($config as $value) {
            $newConfig[$name[$value]] = $value;
        }
        return $newConfig;
    }

    /**
     * 得到虚拟化名称
     * @return array
     */
    private function getConfigVMDes()
    {
        $config = xphp_get_config('vm', 'VMHYPERVISORDES');
        $newConfig = [];
        foreach ($config as $hypervisor => $value) {
            $newConfig[$hypervisor] = $value;
        }
        return $newConfig;
    }

    /**
     * 得到数据库名称
     * @return array
     */
    private function getConfigDBDes()
    {
        $newConfig = [];
        $config = xphp_get_config('db', 'DB_TYPE_DES');
        foreach ($config as $dbType => $value) {
            $newConfig[$dbType] = $value;
        }
        return $newConfig;
    }

    /**
     * 获取系统的基本信息
     * @return array
     */
    public function getConfig()
    {
        $users = xphp_get_user_info();
        $loginUserLevel = 0;
        if (xphp_three_powers()) {
            // 如果是三权模式
            // 这里需要校验下是否达到锁定的限制
            $loginUserLevel = $users['userLevel'] > 3 ? 2 : ($users['userLevel'] == 2 ? 3 : 0);
        }
        $safeInfo = $this->getAccountSafe($loginUserLevel);
        $sysinfo = xphp_get_config('app', 'SYSTEM_INFO');
        $clientHandler = new Client();
        $dbTypeData = $clientHandler->getAgentAppType();
        $authDbType = [];
        if (isset($dbTypeData['data']['rows'])) {
            foreach ($dbTypeData['data']['rows'] as $row) {
                $authDbType[] = $row['app_type'];
            }
        }

        $sql_product_type = "select settings_content from bd_system_settings where settings_type = 24";
        $result_product_type = $this->dbSelect($sql_product_type);
        if(empty($result_product_type)){
            $login_product_type = "";
        }else{
            $login_product_type = $result_product_type[0]['settings_content'];
        }

        $function = v1_license_get_func('f', 1);
        // 兼容处理下，如果病毒检测，授权了任意一个，都返回 virusKill，因为页面都是这个key判断的
        if (in_array('virusKillKaspersky', $function) && !in_array('virusKill', $function)) {
            $function[] = 'virusKill';
        }
        // 还要判断下，如果page未授权病毒查杀，那么也需要屏蔽这个
        if (in_array('virusKill', $function) && !in_array('virus', $users['permission'])) {
            // 找到 'virusKill' 的键名并删除
            $key = array_search('virusKill', $function);
            if ($key !== false) {
                unset($function[$key]);
            }
        }
        // 重新索引数组，保持键连续
        $function = array_values($function);

        return [
            'vm_type' => $this->getConfigVMType(),
            'vm_des' => $this->getConfigVMDes(),
            'system_name' => $sysinfo['system_name'],
            'software' => $this->getSoftwareType(),
            'db_type' => xphp_get_config('db', 'DB_TYPE'),
            'db_des' => $this->getConfigDBDes(),
            'idletime_out' => $safeInfo['out_time'],
            'pass_length' => $safeInfo['passlength'],
            'pass_complexity' => $safeInfo['passcomplexity'],
            'permission' => $users['permission'],
            'permission_arr' => $users['permissionArr'],
            'host_name' => xphp_get_config('app', 'HOSTNAME'),
            'task_type' => xphp_get_config('task', 'TASKTYPE'),
            'task_type_des' => xphp_get_desc('Pf', 'TASKTYPEDES'),
            'task_status' => xphp_get_config('task', 'TASKSTATUS'),
            'task_status_des' => xphp_get_desc('Pf', 'TASKSTATUSDES'),
            'module_type' => xphp_get_config('module', 'MODULE_TYPE'),
            'module_type_des' => xphp_get_desc('Pf', 'MODULE_TYPE_DES'),
            'fs_submodule_type_des' => xphp_get_desc('Pf', 'FS_SUBMODULE_TYPE_DES'),
            'vm_submodule_type_des' => xphp_get_desc('Pf', 'VM_SUBMODULE_TYPE_DES'),
            'enterprise' => $sysinfo['enterprise'],
            'vendor' => $sysinfo['vendor'],
            'storage_type_des' => xphp_get_desc('Pf', 'STORAGETYPE'),
            'real_protect_stage_list' => xphp_get_config('task', 'REAL_PROTECT_STAGE_LIST'),
            'common_stage_list' => xphp_get_config('task', 'COMMON_STAGE_LIST'),
            'function' => $function,
            'language' => $users['language'],
            'tenant_uuid' => $users['tenantuuid'],
            'auth_db_type' => $authDbType,
            'prefix_status' => xphp_get_config('tempagent', 'PREFIX_STATUS'),
            'user_level' => $users['userLevel'],
            'product_type' => $login_product_type,
            'is_three_powers' => $_SESSION['isThreePowers'],
            'vendor_list' => xphp_get_config('app', 'VENDOR_LIST'), // oem枚举
            'change_other_passwd' => getEnvs('CHANGE_OTHER_PASSWD'), // 是否不允许修改用户密码，默认false，允许
        ];
    }

    /**
     * 判断是否启用数据库演练入口
     * @return bool
     */
    private function judgeEnableDbDrill(): bool
    {
        /**
         * 有在线的客户端应用
         */
        $sql = "SELECT baa.app_uuid, baa.app_name, ba.agent_uuid
                FROM bd_agent_app baa
                    INNER JOIN bd_agent ba ON baa.agent_uuid = ba.agent_uuid
                WHERE ba.online_flag = ? ";
        $appData = $this->dbSelect($sql, [xphp_get_config('app', 'FLAG')['SET']]);
        if (is_array($appData) && $appData) {
            return true;
        }
        return false;
    }

    /**
     * 获取恢复权限模块权限配置
     * @return void
     */
    public function getRecoverPermission(): array
    {
        $users = xphp_get_user_info();
        $moduleType = xphp_get_config('module', 'MODULE_TYPE');
        $fsSubModuleType = xphp_get_config('module', 'SUBMODULE_TYPE');
        $osSubModuleType = xphp_get_config('module', 'OS_SUBMODULE_TYPE');
        $vmSubModuleType = xphp_get_config('module', 'VM_SUB_MODULE');
        $permission = $users['permission'];

        $recoverPermission = array();
        $sql = "SELECT task_type, module_type, sub_module_type FROM bd_backup_timepoint WHERE import_flag != 1 GROUP BY sub_module_type,module_type ";
        $data = $this->dbSelect($sql);

        // 虚拟化
        if (in_array('vmprotect', $permission)) {
            if (empty($data)) { // 没有数据全置灰
                $recoverPermission = array_merge($recoverPermission, [
                    ['id' => 'vmprotect', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_VM_VIRTUAL')],
                    ['id' => 'vmrecover', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_VM_VIRTUAL')],
                    ['id' => 'vm_grain_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_VM_VIRTUAL')],
                    ['id' => 'vm_instant_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_VM_VIRTUAL')],
                    ['id' => 'vm_instant_recovery_point', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_VM_VIRTUAL')],
                    ['id' => 'vm_platform_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_VM_VIRTUAL')],
                ]);
            } else {
                $hasData = false;
                foreach ($data as $row) {
                    if ($row['module_type'] == $moduleType['VM'] && $row['sub_module_type'] == $vmSubModuleType['VM']) {
                        $hasData = true;
                        break;
                    }
                }

                if ($hasData) {
                    $recoverPermission = array_merge($recoverPermission, [
                        ['id' => 'vmprotect', 'hasBackupData' => true, 'moduleDes' => xphp_get_lang('UI_PLATFORM_VM_VIRTUAL')],
                        ['id' => 'vmrecover', 'hasBackupData' => true, 'moduleDes' => xphp_get_lang('UI_PLATFORM_VM_VIRTUAL')],
                        ['id' => 'vm_grain_recovery', 'hasBackupData' => true, 'moduleDes' => xphp_get_lang('UI_PLATFORM_VM_VIRTUAL')],
                        ['id' => 'vm_instant_recovery', 'hasBackupData' => true, 'moduleDes' => xphp_get_lang('UI_PLATFORM_VM_VIRTUAL')],
                        ['id' => 'vm_instant_recovery_point', 'hasBackupData' => true, 'moduleDes' => xphp_get_lang('UI_PLATFORM_VM_VIRTUAL')],
                        ['id' => 'vm_platform_recovery', 'hasBackupData' => true, 'moduleDes' => xphp_get_lang('UI_PLATFORM_VM_VIRTUAL')],
                    ]);
                } else { // $data有数据但是没有虚拟化的备份时间点
                    $recoverPermission = array_merge($recoverPermission, [
                        ['id' => 'vmprotect', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_VM_VIRTUAL')],
                        ['id' => 'vmrecover', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_VM_VIRTUAL')],
                        ['id' => 'vm_grain_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_VM_VIRTUAL')],
                        ['id' => 'vm_instant_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_VM_VIRTUAL')],
                        ['id' => 'vm_instant_recovery_point', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_VM_VIRTUAL')],
                        ['id' => 'vm_platform_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_VM_VIRTUAL')],
                    ]);
                }
            }
        }

        // 私有云
        if (in_array('prcloud_protect', $permission)) {
            if (empty($data)) {
                $recoverPermission = array_merge($recoverPermission, [
                    ['id' => 'prcloud_protect', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_PRIVATE_CLOUD')],
                    ['id' => 'vm_prcloud_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_PRIVATE_CLOUD')],
                    ['id' => 'vm_prcloud_graininess_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_PRIVATE_CLOUD')],
                    ['id' => 'vm_prcloud_instant_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_PRIVATE_CLOUD')],
                    ['id' => 'vm_prcloud_instant_recovery_point', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_PRIVATE_CLOUD')],
                    ['id' => 'vm_prcloud_platform_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_PRIVATE_CLOUD')],
                ]);
            } else {
                $hasData = false;
                foreach ($data as $row) {
                    if ($row['module_type'] == $moduleType['VM'] && $row['sub_module_type'] == $vmSubModuleType['PRIVATE_CLOUD']) {
                        $hasData = true;
                        break;
                    }
                }

                if ($hasData) {
                    $recoverPermission = array_merge($recoverPermission, [
                        ['id' => 'prcloud_protect', 'hasBackupData' => true, 'moduleDes' => xphp_get_lang('UI_PLATFORM_PRIVATE_CLOUD')],
                        ['id' => 'vm_prcloud_recovery', 'hasBackupData' => true, 'moduleDes' => xphp_get_lang('UI_PLATFORM_PRIVATE_CLOUD')],
                        ['id' => 'vm_prcloud_graininess_recovery', 'hasBackupData' => true, 'moduleDes' => xphp_get_lang('UI_PLATFORM_PRIVATE_CLOUD')],
                        ['id' => 'vm_prcloud_instant_recovery', 'hasBackupData' => true, 'moduleDes' => xphp_get_lang('UI_PLATFORM_PRIVATE_CLOUD')],
                        ['id' => 'vm_prcloud_instant_recovery_point', 'hasBackupData' => true, 'moduleDes' => xphp_get_lang('UI_PLATFORM_PRIVATE_CLOUD')],
                        ['id' => 'vm_prcloud_platform_recovery', 'hasBackupData' => true, 'moduleDes' => xphp_get_lang('UI_PLATFORM_PRIVATE_CLOUD')],
                    ]);
                } else {
                    $recoverPermission = array_merge($recoverPermission, [
                        ['id' => 'prcloud_protect', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_PRIVATE_CLOUD')],
                        ['id' => 'vm_prcloud_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_PRIVATE_CLOUD')],
                        ['id' => 'vm_prcloud_graininess_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_PRIVATE_CLOUD')],
                        ['id' => 'vm_prcloud_instant_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_PRIVATE_CLOUD')],
                        ['id' => 'vm_prcloud_instant_recovery_point', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_PRIVATE_CLOUD')],
                        ['id' => 'vm_prcloud_platform_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_PRIVATE_CLOUD')],
                    ]);
                }
            }
        }

        // 公有云
        if (in_array('awsprotect', $permission)) {
            if (empty($data)) {
                $recoverPermission = array_merge($recoverPermission, [
                    ['id' => 'awsprotect', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_PUBLIC_CLOUD')],
                    ['id' => 'vm_awsprotect_recover', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_PUBLIC_CLOUD')],
                    ['id' => 'vm_awsprotect_grain_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_PUBLIC_CLOUD')],
                    ['id' => 'vm_awsprotect_instant_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_PUBLIC_CLOUD')],
                    ['id' => 'vm_awsprotect_platform_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_PUBLIC_CLOUD')],
                ]);
            } else {
                $hasData = false;
                foreach ($data as $row) {
                    if ($row['module_type'] == $moduleType['VM'] && $row['sub_module_type'] == $vmSubModuleType['PUBLIC_CLOUD']) {
                        $hasData = true;
                        break;
                    }
                }

                if ($hasData) {
                    $recoverPermission = array_merge($recoverPermission, [
                        ['id' => 'awsprotect', 'hasBackupData' => true, 'moduleDes' => xphp_get_lang('UI_PLATFORM_PUBLIC_CLOUD')],
                        ['id' => 'vm_awsprotect_recover', 'hasBackupData' => true, 'moduleDes' => xphp_get_lang('UI_PLATFORM_PUBLIC_CLOUD')],
                        ['id' => 'vm_awsprotect_grain_recovery', 'hasBackupData' => true, 'moduleDes' => xphp_get_lang('UI_PLATFORM_PUBLIC_CLOUD')],
                        ['id' => 'vm_awsprotect_instant_recovery', 'hasBackupData' => true, 'moduleDes' => xphp_get_lang('UI_PLATFORM_PUBLIC_CLOUD')],
                        ['id' => 'vm_awsprotect_platform_recovery', 'hasBackupData' => true, 'moduleDes' => xphp_get_lang('UI_PLATFORM_PUBLIC_CLOUD')],
                    ]);
                } else {
                    $recoverPermission = array_merge($recoverPermission, [
                        ['id' => 'awsprotect', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_PUBLIC_CLOUD')],
                        ['id' => 'vm_awsprotect_recover', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_PUBLIC_CLOUD')],
                        ['id' => 'vm_awsprotect_grain_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_PUBLIC_CLOUD')],
                        ['id' => 'vm_awsprotect_instant_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_PUBLIC_CLOUD')],
                        ['id' => 'vm_awsprotect_platform_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_PUBLIC_CLOUD')],
                    ]);
                }
            }
        }

        // K8s
        if (in_array('k8s_protect', $permission)) {
            if (empty($data)) {
                $recoverPermission = array_merge($recoverPermission, [
                    ['id' => 'k8s_protect', 'hasBackupData' => false, 'moduleDes' => 'kubernetes'],
                    ['id' => 'k8s_recovery', 'hasBackupData' => false, 'moduleDes' => 'kubernetes'],
                ]);
            } else {
                $hasData = false;
                foreach ($data as $row) {
                    if ($row['module_type'] == $moduleType['KUBERNETES']) {
                        $hasData = true;
                        break;
                    }
                }

                if ($hasData) {
                    $recoverPermission = array_merge($recoverPermission, [
                        ['id' => 'k8s_protect', 'hasBackupData' => true, 'moduleDes' => 'kubernetes'],
                        ['id' => 'k8s_recovery', 'hasBackupData' => true, 'moduleDes' => 'kubernetes'],
                    ]);
                } else {
                    $recoverPermission = array_merge($recoverPermission, [
                        ['id' => 'k8s_protect', 'hasBackupData' => false, 'moduleDes' => 'kubernetes'],
                        ['id' => 'k8s_recovery', 'hasBackupData' => false, 'moduleDes' => 'kubernetes'],
                    ]);
                }
            }
        }

        // 文件
        if (in_array('filebackup', $permission)) {
            if (empty($data)) {
                $recoverPermission = array_merge($recoverPermission, [
                    ['id' => 'filebackup', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('WEB_PLATFORM_DES_FS')],
                    ['id' => 'file_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('WEB_PLATFORM_DES_FS')],
                ]);
            } else {
                $hasData = false;
                foreach ($data as $row) {
                    if ($row['module_type'] == $moduleType['FS'] && $row['sub_module_type'] == $fsSubModuleType['FS']) {
                        $hasData = true;
                        break;
                    }
                }

                if ($hasData) {
                    $recoverPermission = array_merge($recoverPermission, [
                        ['id' => 'filebackup', 'hasBackupData' => true, 'moduleDes' => xphp_get_lang('WEB_PLATFORM_DES_FS')],
                        ['id' => 'file_recovery', 'hasBackupData' => true, 'moduleDes' => xphp_get_lang('WEB_PLATFORM_DES_FS')],
                    ]);
                } else {
                    $recoverPermission = array_merge($recoverPermission, [
                        ['id' => 'filebackup', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('WEB_PLATFORM_DES_FS')],
                        ['id' => 'file_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('WEB_PLATFORM_DES_FS')],
                    ]);
                }
            }
        }

        // NAS
        if (in_array('nas_protect', $permission)) {
            if (empty($data)) {
                $recoverPermission = array_merge($recoverPermission, [
                    ['id' => 'nas_protect', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('WEB_PLATFORM_DES_NAS')],
                    ['id' => 'nas_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('WEB_PLATFORM_DES_NAS')],
                ]);
            } else {
                $hasData = false;
                foreach ($data as $row) {
                    if ($row['module_type'] == $moduleType['NAS']) {
                        $hasData = true;
                        break;
                    }
                }

                if ($hasData) {
                    $recoverPermission = array_merge($recoverPermission, [
                        ['id' => 'nas_protect', 'hasBackupData' => true, 'moduleDes' => xphp_get_lang('WEB_PLATFORM_DES_NAS')],
                        ['id' => 'nas_recovery', 'hasBackupData' => true, 'moduleDes' => xphp_get_lang('WEB_PLATFORM_DES_NAS')],
                    ]);
                } else {
                    $recoverPermission = array_merge($recoverPermission, [
                        ['id' => 'nas_protect', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('WEB_PLATFORM_DES_NAS')],
                        ['id' => 'nas_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('WEB_PLATFORM_DES_NAS')],
                    ]);
                }
            }
        }

        // 对象存储
        if (in_array('obs_protect', $permission)) {
            if (empty($data)) {
                $recoverPermission = array_merge($recoverPermission, [
                    ['id' => 'obs_protect', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('WEB_PLATFORM_DES_OBS')],
                    ['id' => 'obs_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('WEB_PLATFORM_DES_OBS')],
                ]);
            } else {
                $hasData = false;
                foreach ($data as $row) {
                    if ($row['module_type'] == $moduleType['FS'] && $row['sub_module_type'] == $fsSubModuleType['OBS']) {
                        $hasData = true;
                        break;
                    }
                }

                if ($hasData) {
                    $recoverPermission = array_merge($recoverPermission, [
                        ['id' => 'obs_protect', 'hasBackupData' => true, 'moduleDes' => xphp_get_lang('WEB_PLATFORM_DES_OBS')],
                        ['id' => 'obs_recovery', 'hasBackupData' => true, 'moduleDes' => xphp_get_lang('WEB_PLATFORM_DES_OBS')],
                    ]);
                } else {
                    $recoverPermission = array_merge($recoverPermission, [
                        ['id' => 'obs_protect', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('WEB_PLATFORM_DES_OBS')],
                        ['id' => 'obs_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('WEB_PLATFORM_DES_OBS')],
                    ]);
                }
            }
        }

        // Hadoop
        if (in_array('hadoop_protect', $permission)) {
            if (empty($data)) {
                $recoverPermission = array_merge($recoverPermission, [
                    ['id' => 'hadoop_protect', 'hasBackupData' => false, 'moduleDes' => 'hadoop'],
                    ['id' => 'hadoop_recovery', 'hasBackupData' => false, 'moduleDes' => 'hadoop'],
                ]);
            } else {
                $hasData = false;
                foreach ($data as $row) {
                    if ($row['module_type'] == $moduleType['FS'] && $row['sub_module_type'] == $fsSubModuleType['HADOOP']) {
                        $hasData = true;
                        break;
                    }
                }

                if ($hasData) {
                    $recoverPermission = array_merge($recoverPermission, [
                        ['id' => 'hadoop_protect', 'hasBackupData' => true, 'moduleDes' => 'hadoop'],
                        ['id' => 'hadoop_recovery', 'hasBackupData' => true, 'moduleDes' => 'hadoop'],
                    ]);
                } else {
                    $recoverPermission = array_merge($recoverPermission, [
                        ['id' => 'hadoop_protect', 'hasBackupData' => false, 'moduleDes' => 'hadoop'],
                        ['id' => 'hadoop_recovery', 'hasBackupData' => false, 'moduleDes' => 'hadoop'],
                    ]);
                }
            }
        }

        // 数据库
        if (in_array('db_protect', $permission)) {
            $enableDbDrill = $this->judgeEnableDbDrill();
            if (empty($data)) {
                $recoverPermission = array_merge($recoverPermission, [
                    ['id' => 'db_protect', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('WEB_PLATFORM_DES_DB')],
                    ['id' => 'db_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('WEB_PLATFORM_DES_DB')],
                    ['id' => 'db_drill', 'hasBackupData' => $enableDbDrill, 'moduleDes' => xphp_get_lang('WEB_PLATFORM_DES_DB')],
                ]);
            } else {
                $hasData = false;
                foreach ($data as $row) {
                    if ($row['module_type'] == $moduleType['DB']) {
                        $hasData = true;
                        break;
                    }
                }

                if ($hasData) {
                    $recoverPermission = array_merge($recoverPermission, [
                        ['id' => 'db_protect', 'hasBackupData' => true, 'moduleDes' => xphp_get_lang('WEB_PLATFORM_DES_DB')],
                        ['id' => 'db_recovery', 'hasBackupData' => true, 'moduleDes' => xphp_get_lang('WEB_PLATFORM_DES_DB')],
                        ['id' => 'db_drill', 'hasBackupData' => $enableDbDrill, 'moduleDes' => xphp_get_lang('WEB_PLATFORM_DES_DB')],
                    ]);
                } else {
                    $recoverPermission = array_merge($recoverPermission, [
                        ['id' => 'db_protect', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('WEB_PLATFORM_DES_DB')],
                        ['id' => 'db_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('WEB_PLATFORM_DES_DB')],
                        ['id' => 'db_drill', 'hasBackupData' => $enableDbDrill, 'moduleDes' => xphp_get_lang('WEB_PLATFORM_DES_DB')],
                    ]);
                }
            }
        }

        // MS365
        if (in_array('office365_protect', $permission)) {
            if (empty($data)) {
                $recoverPermission = array_merge($recoverPermission, [
                    ['id' => 'office365_protect', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('WEB_PLATFORM_DES_EXCHANGE')],
                    ['id' => 'exchange_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('WEB_PLATFORM_DES_EXCHANGE')],
                ]);
            } else {
                $hasData = false;
                foreach ($data as $row) {
                    if ($row['module_type'] == $moduleType['M365']) {
                        $hasData = true;
                        break;
                    }
                }

                if ($hasData) {
                    $recoverPermission = array_merge($recoverPermission, [
                        ['id' => 'office365_protect', 'hasBackupData' => true, 'moduleDes' => xphp_get_lang('WEB_PLATFORM_DES_EXCHANGE')],
                        ['id' => 'exchange_recovery', 'hasBackupData' => true, 'moduleDes' => xphp_get_lang('WEB_PLATFORM_DES_EXCHANGE')],
                    ]);
                } else {
                    $recoverPermission = array_merge($recoverPermission, [
                        ['id' => 'office365_protect', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('WEB_PLATFORM_DES_EXCHANGE')],
                        ['id' => 'exchange_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('WEB_PLATFORM_DES_EXCHANGE')],
                    ]);
                }
            }
        }

        // 整机（磁盘）
        if (in_array('complete_machine', $permission)) {
            if (empty($data)) {
                $recoverPermission = array_merge($recoverPermission, [
                    ['id' => 'complete_machine', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_MACHINE_COMPLETE_BACKUP')],
                    ['id' => 'machine_complete_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_MACHINE_COMPLETE_BACKUP')],
                    ['id' => 'machine_complete_grain_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_MACHINE_COMPLETE_BACKUP')],
                    ['id' => 'machine_complete_instant_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_MACHINE_COMPLETE_BACKUP')],
                    ['id' => 'machine_complete_instant_recovery_point', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_MACHINE_COMPLETE_BACKUP')],
                    ['id' => 'machine_complete_platform_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_MACHINE_COMPLETE_BACKUP')],
                ]);
            } else {
                $hasData = false;
                foreach ($data as $row) {
                    if ($row['module_type'] == $moduleType['OS'] && $row['sub_module_type'] == $osSubModuleType['MACHINE_OS']) {
                        $hasData = true;
                        break;
                    }
                }

                if ($hasData) {
                    $recoverPermission = array_merge($recoverPermission, [
                        ['id' => 'complete_machine', 'hasBackupData' => true, 'moduleDes' => xphp_get_lang('UI_PLATFORM_MACHINE_COMPLETE_BACKUP')],
                        ['id' => 'machine_complete_recovery', 'hasBackupData' => true, 'moduleDes' => xphp_get_lang('UI_PLATFORM_MACHINE_COMPLETE_BACKUP')],
                        ['id' => 'machine_complete_grain_recovery', 'hasBackupData' => true, 'moduleDes' => xphp_get_lang('UI_PLATFORM_MACHINE_COMPLETE_BACKUP')],
                        ['id' => 'machine_complete_instant_recovery', 'hasBackupData' => true, 'moduleDes' => xphp_get_lang('UI_PLATFORM_MACHINE_COMPLETE_BACKUP')],
                        ['id' => 'machine_complete_instant_recovery_point', 'hasBackupData' => true, 'moduleDes' => xphp_get_lang('UI_PLATFORM_MACHINE_COMPLETE_BACKUP')],
                        ['id' => 'machine_complete_platform_recovery', 'hasBackupData' => true, 'moduleDes' => xphp_get_lang('UI_PLATFORM_MACHINE_COMPLETE_BACKUP')],
                    ]);
                } else {
                    $recoverPermission = array_merge($recoverPermission, [
                        ['id' => 'complete_machine', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_MACHINE_COMPLETE_BACKUP')],
                        ['id' => 'machine_complete_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_MACHINE_COMPLETE_BACKUP')],
                        ['id' => 'machine_complete_grain_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_MACHINE_COMPLETE_BACKUP')],
                        ['id' => 'machine_complete_instant_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_MACHINE_COMPLETE_BACKUP')],
                        ['id' => 'machine_complete_instant_recovery_point', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_MACHINE_COMPLETE_BACKUP')],
                        ['id' => 'machine_complete_platform_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_MACHINE_COMPLETE_BACKUP')],
                    ]);
                }
            }
        }

        // 整机（卷）
        if (in_array('osbackup', $permission)) {
            if (empty($data)) {
                $recoverPermission = array_merge($recoverPermission, [
                    ['id' => 'osbackup', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_MACHINE_REEL_BACKUP')],
                    ['id' => 'os_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_MACHINE_REEL_BACKUP')],
                ]);
            } else {
                $hasData = false;
                foreach ($data as $row) {
                    if ($row['module_type'] == $moduleType['OS'] && $row['sub_module_type'] == $osSubModuleType['UNKNOWN']) {
                        $hasData = true;
                        break;
                    }
                }

                if ($hasData) {
                    $recoverPermission = array_merge($recoverPermission, [
                        ['id' => 'osbackup', 'hasBackupData' => true, 'moduleDes' => xphp_get_lang('UI_PLATFORM_MACHINE_REEL_BACKUP')],
                        ['id' => 'os_recovery', 'hasBackupData' => true, 'moduleDes' => xphp_get_lang('UI_PLATFORM_MACHINE_REEL_BACKUP')],
                    ]);
                } else {
                    $recoverPermission = array_merge($recoverPermission, [
                        ['id' => 'osbackup', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_MACHINE_REEL_BACKUP')],
                        ['id' => 'os_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_MACHINE_REEL_BACKUP')],
                    ]);
                }
            }
        }

        // 整机实时（磁盘）特殊处理，查 cdp_vol_backup_agent
        if (in_array('complete_cdp_backup', $permission)) {
            $volSql = "SELECT dev_type FROM cdp_vol_backup_agent GROUP BY dev_type";
            $volData = $this->dbSelect($volSql);

            if (empty($volData)) {
                $recoverPermission = array_merge($recoverPermission, [
                    ['id' => 'complete_cdp_backup', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_COPY_COMPLETE_BACKUP')],
                    ['id' => 'machine_complete_volcdp_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_COPY_COMPLETE_BACKUP')],
                    ['id' => 'machine_complete_volcdp_grain_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_COPY_COMPLETE_BACKUP')],
                    ['id' => 'machine_complete_volcdp_platform_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_COPY_COMPLETE_BACKUP')],
                ]);
            } else {
                $hasData = false;
                foreach ($volData as $row) {
                    if ($row['dev_type'] == 2) { // 1 卷 2 磁盘
                        $hasData = true;
                        break;
                    }
                }

                if ($hasData) {
                    $recoverPermission = array_merge($recoverPermission, [
                        ['id' => 'complete_cdp_backup', 'hasBackupData' => true, 'moduleDes' => xphp_get_lang('UI_PLATFORM_COPY_COMPLETE_BACKUP')],
                        ['id' => 'machine_complete_volcdp_recovery', 'hasBackupData' => true, 'moduleDes' => xphp_get_lang('UI_PLATFORM_COPY_COMPLETE_BACKUP')],
                        ['id' => 'machine_complete_volcdp_grain_recovery', 'hasBackupData' => true, 'moduleDes' => xphp_get_lang('UI_PLATFORM_COPY_COMPLETE_BACKUP')],
                        ['id' => 'machine_complete_volcdp_platform_recovery', 'hasBackupData' => true, 'moduleDes' => xphp_get_lang('UI_PLATFORM_COPY_COMPLETE_BACKUP')],
                    ]);
                } else {
                    $recoverPermission = array_merge($recoverPermission, [
                        ['id' => 'complete_cdp_backup', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_COPY_COMPLETE_BACKUP')],
                        ['id' => 'machine_complete_volcdp_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_COPY_COMPLETE_BACKUP')],
                        ['id' => 'machine_complete_volcdp_grain_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_COPY_COMPLETE_BACKUP')],
                        ['id' => 'machine_complete_volcdp_platform_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_COPY_COMPLETE_BACKUP')],
                    ]);
                }
            }
        }

        // 整机实时（卷）特殊处理，查 cdp_vol_backup_agent
        if (in_array('vol_cdp_backup', $permission)) {
            $volSql = "SELECT dev_type FROM cdp_vol_backup_agent GROUP BY dev_type";
            $volData = $this->dbSelect($volSql);

            if (empty($volData)) {
                $recoverPermission = array_merge($recoverPermission, [
                    ['id' => 'vol_cdp_backup', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_CDP_REEL_BACKUP')],
                    ['id' => 'p_vol_cdp_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_CDP_REEL_BACKUP')],
                ]);
            } else {
                $hasData = false;
                foreach ($volData as $row) {
                    if ($row['dev_type'] == 1) { // 1 卷 2 磁盘
                        $hasData = true;
                        break;
                    }
                }

                if ($hasData) {
                    $recoverPermission = array_merge($recoverPermission, [
                        ['id' => 'vol_cdp_backup', 'hasBackupData' => true, 'moduleDes' => xphp_get_lang('UI_PLATFORM_CDP_REEL_BACKUP')],
                        ['id' => 'p_vol_cdp_recovery', 'hasBackupData' => true, 'moduleDes' => xphp_get_lang('UI_PLATFORM_CDP_REEL_BACKUP')],
                    ]);
                } else {
                    $recoverPermission = array_merge($recoverPermission, [
                        ['id' => 'vol_cdp_backup', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_CDP_REEL_BACKUP')],
                        ['id' => 'p_vol_cdp_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_PLATFORM_CDP_REEL_BACKUP')],
                    ]);
                }
            }
        }

        // 数据库实时
        if (in_array('dbcdpcopy', $permission)) {
            if (empty($data)) {
                $recoverPermission = array_merge($recoverPermission, [
                    ['id' => 'dbcdpcopy', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_HOMEPAGE_DB_CDP')],
                    ['id' => 'dbcdp_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_HOMEPAGE_DB_CDP')],
                ]);
            } else {
                $hasData = false;
                foreach ($data as $row) {
                    if ($row['module_type'] == $moduleType['DB_CDP']) {
                        $hasData = true;
                        break;
                    }
                }

                if ($hasData) {
                    $recoverPermission = array_merge($recoverPermission, [
                        ['id' => 'dbcdpcopy', 'hasBackupData' => true, 'moduleDes' => xphp_get_lang('UI_HOMEPAGE_DB_CDP')],
                        ['id' => 'dbcdp_recovery', 'hasBackupData' => true, 'moduleDes' => xphp_get_lang('UI_HOMEPAGE_DB_CDP')],
                    ]);
                } else {
                    $recoverPermission = array_merge($recoverPermission, [
                        ['id' => 'dbcdpcopy', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_HOMEPAGE_DB_CDP')],
                        ['id' => 'dbcdp_recovery', 'hasBackupData' => false, 'moduleDes' => xphp_get_lang('UI_HOMEPAGE_DB_CDP')],
                    ]);
                }
            }
        }

        return array(
            'permissions' => $permission,
            'permissionToHasDatas' => $recoverPermission
        );
    }

    /**
     * 批量检查IP是否可达
     * @param array $params 请求参数
     * @return array
     */
    public function batchCheckIpReachable(array $params): array
    {
        $checkedResult = [
            'statistics' => [
                'total' => count($params['ip_list']),
                'reachable' => 0,
                'unreachable' => count($params['ip_list']),
            ],
            'checked_ip_list' => [],
        ];
        foreach ($params['ip_list'] as $ip) {
            $isReachable = v1_check_ip_exists(
                $ip,
                $params['timeout'] ?: 1,
                $params['retries'] ?: 3,
                $params['interface'] ?: ''
            );
            $checkedResult['checked_ip_list'][] = [
                'ip' => $ip,
                'reachable' => $isReachable,
            ];
            if ($isReachable) {
                $checkedResult['statistics']['reachable']++;
                $checkedResult['statistics']['unreachable']--;
            }
        }
        return $this->sendResult('', true, 200, $checkedResult);
    }

    /**
     * 发送消息
     * @param array $params 请求参数
     * @return array
     */
    public function sendMessages(array $params): array
    {
        $messageType = intval($params['message_type']);
        $allMessageType = xphp_get_config('system', 'MESSAGE_TYPE');
        switch ($messageType) {
            case $allMessageType['DB_DRILL']:
                return (new \app\v1\db\v0\logic\DbJobInfo())->sendDrillEmailByHistoryUuid($params['object_id']);
            default:
                break;
        }
        return $this->sendResult('');
    }
}
