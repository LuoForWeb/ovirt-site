<?php
/*
 * @note: 
 * @author: chenyunfeng@vinchin.com
 * @Description: 
 * @Date: 2025-04-22 17:53:20
 * @LastEditTime: 2026-02-27 10:03:37
 * @Version: 2.0
 * @copyright: Copyright 2025 vinchin.com
 */

use xphp\db\Op;

class SystemBakMonitor extends Op
{
    public function __construct()
    {
        while (true) {
            $settings = $this->getBakSettings();
            if($settings['auto_flag']){
                //如果开启了自动备份,检查备份时间和当前时间对应关系
                $settingsTime = strtotime(date("Y-m-d ", time()) . $settings['backup_time']);
                $currentTime = time();
                $result = false;
                // 达到配置的备份时间启动备份
                if($currentTime > $settingsTime && abs($currentTime - $settingsTime) < 31){
                    $result = $this->doAutoBakSystem($settings);
                    // 如果没到配置的时间
                } else {
                    // 如果过了配置的时间
                    if($currentTime > $settingsTime){
                        $backupName = $this->getBakNewList();
                        // 如果数据库没有备份记录，则需要启动重试
                        $today = date("Ymd");
                        if(($backupName == false || !strpos($backupName, $today))){
                            // 添加循环重试机制
                            do {
                                // 系统备份恢复日志：开始重试
                                $result = $this->doAutoBakSystem($settings);
                                if ($result == true) {
                                    break; // 成功则退出循环
                                }
                                sleep(3600);
                            } while ($result == false);
                        }
                    }
                }
            }
            sleep(30);
        }
    }
    /**
     * 获取最新的备份文件名
     *
     * 该函数用于从数据库中查询最新的备份记录，并返回对应的备份文件名。
     * 查询结果按备份时间降序排列，只取第一条记录。
     *
     * @return string|bool 如果查询到数据，返回备份文件名；如果无数据，返回 false
     */
    private function getBakNewList(){
        // 构造SQL查询语句，获取最新的一条备份记录
        $sql = "SELECT
                    * 
                FROM
                    bd_system_backup_data 
                ORDER BY
                    backup_time DESC 
                    LIMIT 1";
        
        // 执行数据库查询
        $data = $this->dbSelect($sql);
        
        // 如果查询结果为空，返回 false
        if(empty($data)){
            return false;
        }
        
        // 返回查询结果中的文件名
        return $data[0]['file_name'];
    }
    /**
     * 进入自动备份流程
     * @param {} $settings
     */
    private function doAutoBakSystem($settings)
    {
        $this->writeLog("do auto backup system.");
        // 调用web_ng的方法
        $SystemBackup = new \app\v1\system\v0\logic\SystemBackup();
        $result = $SystemBackup->systemBackupAuto($settings);
        if ($result) {
            $this->writeLog("do auto backup system success!");
        } else {
            $this->writeLog("do auto backup system failure!");
        }
    }


    /**
     * 获取自动备份配置文件
     */
    private function getBakSettings()
    {
        // 自动备份配置文件路径
        $filePath = xphp_get_config('app', 'TMP_PATH') . "autobak.config";
        if (!file_exists($filePath)) {
            //如果配置文件不存在
            $info = ["auto_flag" => false,];
            return $info;
        }
        //如果存在 返回配置信息
        $fileInfo = file_get_contents($filePath);
        return json_decode($fileInfo, true);
    }

    public function __destruct()
    {
        $this->writeLog("stop auto backup system monitor!");
    }
}
