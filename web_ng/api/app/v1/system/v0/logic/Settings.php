<?php

namespace app\v1\system\v0\logic;

use app\v1\common\logic\Base;
use app\v1\user\v0\logic\Role;

/**
 * note          系统配置处理类
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/10 14:38
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Settings extends Base
{
    /**
     * 获取某个类型的所有配置信息
     * @param int $settingsType 配置类型
     * @return array
     */
    public function getSettingsInfos($settingsType)
    {
        $this->paramsCheck($settingsType);

        $sql = "select settings_id, settings_type, settings_content, modify_time, user_uuid
                from bd_system_settings where settings_type = ?";
        return $this->dbSelect($sql, array($settingsType), \PDO::FETCH_ASSOC);
    }

    /**
     * 修改某个配置的配置信息,通过id号
     * @param int    $settingsid      配置ID
     * @param string $settingsContent 配置内容,json
     * @return string
     */
    public function modifySettingsInfosWithID($settingsid, $settingsContent)
    {
        $this->paramsCheck($settingsid, $settingsContent);

        $sql = "update bd_system_settings set settings_content = ?, modify_time = ?, user_uuid = ?
                where settings_id = ?";
        $dateformat = xphp_get_config('special', 'dateformat') ?? 'Y-m-d H:i:s';
        $sqlParams = array($settingsContent, date($dateformat), xphp_get_user_info()['userUuid'], $settingsid);
        return $this->dbExec($sql, $sqlParams);
    }

    /**
     * 修改某个配置的配置信息,通过type类型
     * @param int    $settingsType    配置类型
     * @param string $settingsContent 配置内容,json
     * @return string
     */
    public function modifySettingsInfosWithType($settingsType, $settingsContent)
    {
        $this->paramsCheck($settingsType, $settingsType);

        $sql = "update bd_system_settings set settings_content = ?, modify_time = ?, user_uuid = ?
                where settings_type = ?";
        $dateformat = xphp_get_config('special', 'dateformat') ?? 'Y-m-d H:i:s';
        $sqlParams = array($settingsContent, date($dateformat), xphp_get_user_info()['userUuid'], $settingsType);
        return $this->dbExec($sql, $sqlParams);
    }

    /**
     * 添加某个系统配置信息
     * @param string $settingsType    type
     * @param string $settingsContent content
     * @return boolean
     */
    public function addSettingsInfos($settingsType, $settingsContent)
    {
        $this->paramsCheck($settingsType, $settingsContent);

        $sql = "insert into bd_system_settings (settings_type, settings_content, modify_time, user_uuid)
                values (?, ?, ?, ?)";
        $dateformat = xphp_get_config('special', 'dateformat') ?? 'Y-m-d H:i:s';
        $sqlParams = array($settingsType, $settingsContent, date($dateformat), xphp_get_user_info()['userUuid']);
        return $this->dbExec($sql, $sqlParams);
    }

    /**
     * 获取系统授权状态
     * @return string
     */
    public function getSystemAuthorizationStatus()
    {
        $sql = "select authorized_flag from bd_system ";
        $data = $this->dbSelect($sql);
        if ($data) {
            return intval($data[0]['authorized_flag']);
        }
    }

    /**
     * 上传系统配置
     * @param array $param
     * @return string
     */
    public function uploadSysSetting($param)
    {
        $sql = "select settings_content from bd_system_settings where settings_type = 31"; //查询是否有type为31的设置信息
        $time = date( xphp_get_config('special', 'dateformat'));
        $user = xphp_get_user_info()['userUuid'];
        $data = $this->dbSelect($sql);
        $content = [];
        $content['logo'] = $param['logo'];
        $content['sys_name'] = $param['sys_name'];
        $content = json_encode($content);
        // dump($content);
        if ($data) {
            $opSql = "update bd_system_settings set settings_content = ?, modify_time = '{$time}', user_uuid = '{$user}' where settings_type = 31";
            $opSqlParam = [$content];
            $result = $this->dbExec($opSql, $opSqlParam);
        } else {
            $opSql = "insert into bd_system_settings(settings_type,settings_content,modify_time,user_uuid) values(31, ?, '{$time}', '{$user}')";
            $opSqlParam = [$content];
            $result = $this->dbExec($opSql, $opSqlParam);
        }
        return $result;
    }

    /**
     * 获取系统配置
     * @param array $param
     * @return string
     */
    public function getSysSetting($param)
    {
        $sql = "select settings_content from bd_system_settings where settings_type = 31";
        $data = $this->dbSelect($sql);
        $data = $data[0]['settings_content'];
        $data = json_decode($data, true);
        return $data ?? ['logo' => '', 'sys_name' => ''];
    }

    /**
     * 设置可视化配置
     * @param array $param
     * @return string
     */
    public function getDefaultVisualInfo($param)
    {
        $visualConf = $this->getSettingsInfos(xphp_get_config('app', 'SETTINGS_CONF')['VISUAL']);
        $visualConf = json_decode($visualConf[0]['settings_content'], true);
        return $visualConf;
    }

    /**
     * 自定义设置大屏标题
     * @param unknown $params
     * @return string
     */
    public function setVisualInfo($params)
    {
        //权限检查
        (new Role())->pOperationPermissionCheckExit("p_setting_manager_visualization");
        $title = $params['title'];
        $taskAlertCheck = $params['taskAlertCheck'];
        $systemAlertCheck = $params['systemAlertCheck'];

        $visualConf = array(
            "config" => array(
                'title' => $title,
                'taskAlertCheck' => $taskAlertCheck,
                'systemAlertCheck' => $systemAlertCheck,
            )
        );
        $visualConf = json_encode($visualConf);
        $result = $this->modifySettingsInfosWithType(xphp_get_config('app', 'SETTINGS_CONF')['VISUAL'], $visualConf);
        if (!$result) {
            return $this->muOpResult(false, xphp_get_lang('UI_VISUAL_CONFIG_SETTING'), "", 'warning');
        }
        $this->systemLog('SYSTEM_SETTING_CONFIG_VISUAL_NAME', array("$title"));
        return $this->muOpResult(true, xphp_get_lang('UI_VISUAL_CONFIG_SETTING'));
    }
}
