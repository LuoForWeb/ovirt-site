<?php

namespace app\v2\system\v0\logic;

use app\v2\common\logic\Base;

/**
 * note          存放系统的一些配置
 * @author       wanggongxi@vinchin.com
 * @date         2026/2/9 14:11
 * @version      1.0.0
 * @copyright    Copyright 2026 vinchin.com
 */
class Index extends Base
{

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
        $authDbType = [];

        $sql_product_type = "select settings_content from bd_system_settings where settings_type = 24";
        $result_product_type = $this->dbSelect($sql_product_type);
        if(empty($result_product_type)){
            $login_product_type = "";
        }else{
            $login_product_type = $result_product_type[0]['settings_content'];
        }

        $function = v2_license_get_func('f', 1);
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
            'is_three_powers' => $users['isThreePowers'],
            'change_other_passwd' => getEnvs('CHANGE_OTHER_PASSWD'), // 是否不允许修改用户密码，默认false，允许
            'vendor_list' => xphp_get_config('app', 'VENDOR_LIST'), // oem枚举
        ];
    }

    /**
     * 得到extension license
     * 这个函数多处使用,请谨慎修改.
     * @return array
     */
    public function getExtensionLicense(): array
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
            'NUTANIXAHV',
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
        $data = $this->dbSelect($sql, array());
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
}
