<?php

namespace app\v2\system\v0\logic;

use app\v2\common\logic\Base;

/**
 * note          menu desc
 * @author       wanggongxi@vinchin.com
 * @date         2026/1/28 18:58
 * @version      1.0.0
 * @copyright    Copyright 2026 vinchin.com
 */
class Menu extends Base
{
    /**
     * 获取菜单列表
     * @param array $params 请求参数
     * @return array|void
     */
    public function getMenuList(array $params)
    {
        $user = xphp_get_user_info();
        $permission = $user['permission'];
        // test
        $permission = [ "homepage", "p_homepage", "p_visual_screen", "monitor", "task", "current_job", "history_job", "task_orchestration", "alarm", "task_alarm", "system_alarm", "log", "job_log", "system_log", "ha_log", "report", "vm_report", "storage_report", "system", "p_system_list", "p_system_log_download", "p_system_rule", "backup", "vmprotect", "p_vmbackup_list", "prcloud_protect", "p_prcloud_backup_list", "awsprotect", "p_awsbackup_list", "complete_machine", "p_complete_machine_list", "osbackup", "p_osbackup_list", "fileprotect", "filebackup", "nas_protect", "obs_protect", "hadoop_protect", "db_protect", "p_db_backup_list", "office365_protect", "p_exchange_backup_list", "k8s_protect", "p_k8s_backup_list", "cbrbackup", "p_cbrbackup_list", "vol_cdp_protect", "complete_cdp_backup", "p_vol_cdp_backup_list", "vol_cdp_backup", "p_vol_cdp_backup_list", "dbprotect", "dbcdpbackup", "dbdataRecovery", "dbdata", "dbhost", "copy", "machine_copy", "p_machine_os_backup_list", "vol_cdp_copy", "p_machine_os_backup_list", "file_copy_protect", "p_file_copy_list", "dbcdpcopy", "p_dbcdp_backup_list", "data_manager", "backup_data", "p_backup_data_list", "p_backup_data_operate", "recovery", "vmprotect_recover", "prcloud_recover", "awsprotect_recover", "k8s_recover", "file_recover", "nasrecover", "obs_recover", "hadoop_recover", "db_recover", "exchange_recover", "machine_complete_recover", "osrecover", "machine_complete_volcdp_recover", "vol_cdp_recovery", "dbcdprecover", "cdp_takeover", "vol_cdp_complete_takeover", "vol_cdp_takeover", "data_verification", "add_verification_job", "virtual_lab_manager", "appgroup", "copy_protect", "archive_new", "resmanagement", "infrastructure", "vcenter_manager", "cloud_platform_private", "cloud_platform", "client", "nasmanager", "obsmanager", "k8s_cluster", "exchange_organization", "hadoop_cluster", "production_storage_manager", "storage_lanfree", "storage_manager", "manager", "tape_manage", "backup_manager", "node", "cluster_manager", "driver_manager", "scripts_manager", "global_strategy", "global_speed_strategy", "resource_group", "virus", "vm_machine_manager", "vm_machine_network", "vm_machine_list", "vm_machine_proxy_gateway", "vm_machine_operate_log", "sysmanagement", "setting_manager", "system_network", "set_time", "system_notice", "system_safe", "system_poweroff", "system_upgrade", "message_push", "visual_config", "system_service", "system_br", "exercise_platform", "black_white_list", "api_key", "carbon_monitor_platform", "safety", "safety_user", "safety_usergroup", "safety_role", "safety_domain", "tenant_manager", "p_tenant_manager_list", "p_tenant_manager_add", "p_tenant_manager_edit", "p_tenant_manager_delete", "p_tenant_manager_enable", "p_tenant_manager_disable", "authorization_module", "p_authorization_module_list", "p_authorization_module_download", "p_authorization_module_upload", "global_observer", "global_read", "global_write" ];

        return xphp_get_menu('', false, $permission, $user['tenantuuid'], $this->getHomePage()['url']);
    }

    /**
     * 获取系统首页url
     * @param array $params 请求参数
     * @return array
     */
    public function getHomePage(array $params = [])
    {
        $user = xphp_get_user_info();

        // 根据授权模式 如果三权模式并且是安全员和审计员的情况，是没有首页的
        if ($user['userLevel'] == 3) {
            return [
                'url' => 'users.html',
                'menuId' => 'parent_sysmanagement',
                'name' => 'safety',
            ];
        }

        if ($user['userLevel'] == 4) {
            return [
                'url' => 'log.html',
                'menuId' => 'parent_monitor',
                'name' => 'log',
            ];
        }

        if (!empty($user['tenantuuid'])) {
            // 租户
            return [
                'url' => 'tenantManager.html',
                'menuId' => 'parent_sysmanagement',
                'name' => 'tenant_manager',
            ];
        }

        // 标品
        $systemInfo = xphp_get_config('app', 'SYSTEM_INFO');
        $vendorList = xphp_get_config('app', 'VENDOR_LIST');
        if ($systemInfo['enterprise'] == $vendorList['inspur']) {
            //默认新版路径(浪潮首页只有一个，databackup_center_vinchin.php页面)
            return  [
                'url' => 'tenantCenter.html',
                'menuId' => 'parent_homepage',
                'name' => 'homepage',
            ];
        }

        $route = [
            'url' => '',
            'menuId' => 'parent_homepage',
            'name' => 'homepage',
        ];
        //先获取版本是哪种
        //获取版本信息
        $settingConf = xphp_get_config('app', 'SETTINGS_CONF');
        $sqlProductType = "select settings_content
                                    from bd_system_settings
                                    where settings_type = {$settingConf['CUSTOM_VERSION']}";
        $resultType = $this->dbSelect($sqlProductType);
        if(!empty($resultType)){
            $productType = $resultType[0]['settings_content'];
            //默认新版路径
            $route['url'] = "dataBackupCenter.html";
            switch ($productType) {
                //专业版
                case 'professional':
                    //如果是专业版: 老版路径
                    // 如果有全局观察者权限，那么也默认和admin一样的
                    $route['url'] = 'vinchinCenter.html';
                    $userLevelPath = [
                        // 标品的普通用户是个人版首页
                        0 => 'center.tml',
                        // admin是全局的
                        1 => 'vinchinCenter.html',
                        // 系统管理员是全局的
                        2 => 'vinchinCenter.html',
                        // 三权操作员是个人版
                        5 => 'center.html',
                    ];
                    if (!empty($userLevelPath[$user['userLevel']])) {
                        $route['url'] = $userLevelPath[$user['userLevel']];
                    }
                    if (in_array('global_observer', $user['permission'])) {
                        // 如果有全局观察者权限，那么也默认和admin一样的
                        $route['url'] = 'vinchinCenter.html';
                    }
                    break;
                //基础版: 显示新版首页
                case 'basic':
                //白牌版: 显示新版首页
                case 'special':
                    break;
                //项目版: 显示新版首页
                case 'project':
                    $route['url'] = 'centerPro.html';
                    break;
                //标准版
                case 'standard':
                    $route['url'] = 'centerStandard.html';
                    break;
            }
        }

        return $route;
    }

    /**
     * down菜单
     * @return array|void
     */
    public function downMenuList()
    {
        $menu = xphp_get_menu();

        // 将菜单转换为无数字索引的格式
        $menu = $this->convertMenuToZeroIndexed($menu);

        // 使用自定义的PHP代码生成器，确保没有数字索引
        $phpCode = $this->generateCleanPhpCode($menu);

        return $this->outputDownload($phpCode, 'menu.php');
    }

    /**
     * 递归将菜单数组转换为无数字索引的格式
     * @param array $array 原始数组
     * @return array 处理后的数组
     */
    private function convertMenuToZeroIndexed(array $array): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if ($this->isAssocArray($value)) {
                    $result[$key] = $this->convertMenuToZeroIndexed($value);
                } else {
                    $result[$key] = array_values(array_map(
                        fn($item) => is_array($item) ? $this->convertMenuToZeroIndexed($item) : $item,
                        $value
                    ));
                }
            } else {
                $result[$key] = $value;
            }
        }

        if (!$this->isAssocArray($array)) {
            return array_values($result);
        }

        return $result;
    }

    /**
     * 判断数组是否为关联数组（非数字索引）
     * @param array $array 要检查的数组
     * @return bool
     */
    private function isAssocArray(array $array): bool
    {
        if (empty($array)) {
            return false;
        }

        $keys = array_keys($array);
        return $keys !== array_keys($keys);
    }

    /**
     * 生成干净的PHP代码（无数字索引）
     * @param mixed $data 数据
     * @param int $indent 缩进级别
     * @param bool $isListItem 是否是列表项
     * @return string PHP代码
     */
    private function generateCleanPhpCode($data, int $indent = 0, bool $isListItem = false): string
    {
        $indentStr = str_repeat('    ', $indent);

        if (!is_array($data)) {
            return var_export($data, true);
        }

        if (empty($data)) {
            return '[]';
        }

        $isAssoc = $this->isAssocArray($data);
        $result = "[\n";

        foreach ($data as $key => $value) {
            $result .= str_repeat('    ', $indent + 1);

            // 只有在关联数组中才显示键名
            if ($isAssoc) {
                $result .= var_export($key, true) . ' => ';
            }

            if (is_array($value)) {
                $result .= $this->generateCleanPhpCode($value, $indent + 1, !$isAssoc);
            } else {
                $result .= var_export($value, true);
            }

            $result .= ",\n";
        }

        $result .= $indentStr . ']';
        return $result;
    }

    /**
     * 发送下载头信息
     * @param string $filename 文件名
     * @param int $contentLength 内容长度
     * @return void
     */
    private function sendDownloadHeaders(string $filename, int $contentLength): void
    {
        if (ob_get_length()) {
            ob_end_clean();
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . $contentLength);
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Content-Transfer-Encoding: binary');
    }

    /**
     * 输出下载文件
     * @param string $content 文件内容
     * @param string $filename 文件名
     * @return void
     */
    private function outputDownload(string $content, string $filename = 'menu.php'): void
    {
        $this->sendDownloadHeaders($filename, strlen($content));
        echo $content;
        exit;
    }
}