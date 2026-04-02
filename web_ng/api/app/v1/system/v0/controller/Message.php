<?php

namespace app\v1\system\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          系统安全配置 controller
 * @author       zhengxiangqin@vinchin.com
 * @date         2024/3/18 11:06
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */

class Message extends AuthBase
{
    /**
     * 获取推送列表
     */
    public function getMessagePush()
    {
        if(!empty($this->param['message_uuid'])){
            // 获取单个详情
            $result = $this->logic()->getMessagePush($this->param);
        }else{
            // 获取列表
            $result = $this->logic()->getMessagePushList($this->param);
        }
        $this->success('', $result);

    }

    /**
     * 添加消息推送
     * @return json
     */
    public function addMessagePush()
    {
//        $this->checkParams('add_message_push');
        $result = $this->logic()->addMessagePush($this->param);
        if(!$result){
            $this->error();
        }
        $this->success($result);
    }

    /**
     * 修改消息推送
     * @return json
     */
    public function editMessagePush()
    {
//        $this->checkParams('add_message_push');
        $result = $this->logic()->editMessagePush($this->param);
        if(!$result){
            $this->error();
        }
        $this->success($result);
    }

    /**
     * 删除消息推送
     */
    public function deleteMessagePush()
    {
        $result = $this->logic()->deleteMessagePush($this->param);
        if(!$result){
            $this->error();
        }
        $this->success($result);
    }

    /**
     * 启用消息推送
     * @return json
     */
    public function unlcokMessagePush()
    {
        $result = $this->logic()->unlcokMessagePush($this->param);
        if(!$result){
            $this->error();
        }
        $this->success($result);
    }

    /**
     * 检测监控平台别名是否重名
     */
    public function checkPushStrategyName()
    {
        $result = $this->logic()->checkPushStrategyName($this->param);
        if(!$result){
            $this->error(xphp_get_lang('WEB_MANUAL_PUSH_ALIAS_EXIST'),$result);
        }
        $this->success(xphp_get_lang('WEB_MANUAL_PUSH_ALIAS_NO_EXIST'),$result);
    }


    /**
     * 禁用消息推送
     * @return json
     */
    public function lcokMessagePush()
    {
        $result = $this->logic()->lcokMessagePush($this->param);
        if(!$result){
            $this->error();
        }
        $this->success($result);
    }

    /**
     * 获取关联任务
     */
    public function getAssociatedTask()
    {
        $result = $this->logic()->getAssociatedTask($this->param);
        if(!$result && $result != []){
            $this->error();
        }
        $this->success('',$result);
    }

    /**
     * 获取监控平台列表
     * @return json
     */
    public function getMessageMonitorPlatform()
    {
        if(!empty($this->param['monitor_platform_uuid'])){
            // 获取单个详情
            $result = $this->logic()->getMessageMonitorPlatform($this->param);
        }else{
            // 获取列表
            $result = $this->logic()->getMessageMonitorPlatformList($this->param);
        }
        $this->success('', $result);
    }

    /**
     * 添加监控平台
     * @return json
     */
    public function addMessageMonitorPlatform()
    {
//        $this->checkParams('add_message_monitor_platform');
        $result = $this->logic()->addMessageMonitorPlatform($this->param);
        if(!$result){
            $this->error();
        }
        $this->success($result);
    }

    /**
     * 修改监控平台
     * @return json
     */
    public function editMessageMonitorPlatform()
    {
//        $this->checkParams('add_message_monitor_platform');
        $result = $this->logic()->editMessageMonitorPlatform($this->param);
        if(!$result){
            $this->error();
        }
        $this->success($result);
    }

    /**
     * 删除监控平台
     * @return json
     */
    public function deleteMessageMonitorPlatform()
    {
        $result = $this->logic()->deleteMessageMonitorPlatform($this->param);
        if(!$result){
            $this->error(xphp_get_lang('WEB_MANUAL_PUSH_STRATEGY_TIPS'));
        }
        $this->success($result);
    }

    /**
     * 检测监控平台别名是否重名
     */
    public function checkMonitorPlatformName()
    {
        $result = $this->logic()->checkMonitorPlatformName($this->param);
        if(!$result){
            $this->error(xphp_get_lang('WEB_MANUAL_PUSH_ALIAS_EXIST'),$result);
        }
        $this->success(xphp_get_lang('WEB_MANUAL_PUSH_ALIAS_NO_EXIST'),$result);
    }

    /**
     * 测试IP是否能ping通
     * @return json
     */
    public function testIp()
    {
        $result = $this->logic()->testIp($this->param);
        if(!$result){
            $this->error();
        }
        $this->success($result);
    }


    /**
     * 手动推送告警信息
     * @return json
     */
    public function pushMessageAlarm()
    {
        $result = $this->logic()->pushMessageAlarm($this->param);
        return $this->success('',$result);
    }

    /**
     * 手动推送响应信息
     * @return json
     */
    public function pushMessageResponse()
    {
        $result = $this->logic()->pushMessageResponse($this->param);
        return $this->success('',$result);
    }

    /**
     * 获取默认推送名
     * @return json
     */
    public function getMessageDefaultName()
    {
        $return = $this->logic()->getMessageDefaultName($this->param);
        $this->success('',$return);
    }

    /**
     * 获取默认监控名
     * @return json
     */
    public function getMonitorDefaultName()
    {
        $return = $this->logic()->getMonitorDefaultName($this->param);
        $this->success('',$return);
    }

    /**
     * 重复IP
     */
    public function isSameIP()
    {
        $return = $this->logic()->isSameIP($this->param);
        $this->success('',$return);
    }

    /**
     * 重复url
     */
    public function isSameUrl()
    {
        $return = $this->logic()->isSameUrl($this->param);
        $this->success('',$return);
    }
}