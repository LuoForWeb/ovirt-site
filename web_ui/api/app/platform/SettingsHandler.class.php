<?php
/*******************************************
 ** 系统配置数据库处理类
 **
 ** @author       
 ** @date         2021-09-07 下午19:51:32
 ** @version      1.0.0
 ** @copyright    Copyright 2021 vinchin.com
 ********************************************/
class SettingsHandler extends OPHandler{
    /**
     * 获取某个类型的所有配置信息
     * @param int $settingsType 配置类型
     */
    public function getSettingsInfos($settingsType){
        $this->paramsCheck($settingsType);
        
        $sql = "select settings_id, settings_type, settings_content, modify_time, user_uuid from bd_system_settings where settings_type = ?";
        $data = $this->dbSelect($sql, array($settingsType), PDO::FETCH_ASSOC);
        return $data;
    }
    
    /**
     * 修改某个配置的配置信息,通过id号
     * @param int $settings_id  配置ID
     * @param int $settingsType 配置类型
     * @param string $settingsContent   配置内容,json
     */
    public function modifySettingsInfosWithID($settings_id, $settingsContent){
        $this->paramsCheck($settings_id, $settingsContent);
        
        $sql = "update bd_system_settings set settings_content = ?, modify_time = ?, user_uuid = ? where settings_id = ?";
        $sqlParams = array($settingsContent, date("Y-m-d H:i:s"), Xphp::$_user['useruuid'], $settings_id);
        $result = $this->dbExec($sql, $sqlParams);
        
        return $result;
    }
    
    /**
     * 修改某个配置的配置信息,通过type类型
     * @param int $settings_id  配置ID
     * @param int $settingsType 配置类型
     * @param string $settingsContent   配置内容,json
     */
    public function modifySettingsInfosWithType($settingsType, $settingsContent){
        $this->paramsCheck($settingsType, $settingsType);
        
        $sql = "update bd_system_settings set settings_content = ?, modify_time = ?, user_uuid = ? where settings_type = ?";
        $sqlParams = array($settingsContent, date("Y-m-d H:i:s"), Xphp::$_user['useruuid'], $settingsType);
        $result = $this->dbExec($sql, $sqlParams);
        
        return $result;
    }
    
    
    /**
     * 添加某个系统配置信息
     * @param unknown $params
     * @return boolean
     */
    public function addSettingsInfos($settingsType, $settingsContent){
        $this->paramsCheck($settingsType, $settingsContent);
        
        $sql = "insert into bd_system_settings (settings_type, settings_content, modify_time, user_uuid) values (?, ?, ?, ?)";
        $sqlParams = array($settingsType, $settingsContent, date("Y-m-d H:i:s"), Xphp::$_user['useruuid']);
        $result = $this->dbExec($sql, $sqlParams);
        
        return $result;
    }
    
}