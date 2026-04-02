<?php

namespace app\v1\opcode;

use xphp\OpcodeHandler;

class VerifyOpcode extends OpcodeHandler
{
    private static $instance = null;

    /**
     * 构造方法
     */
    public function __construct()
    {
        $this->initOpcodeLevel();
        $this->initOpcodeType();
        $this->initOpcode();
        $this->initOpcodeDes();
    }

    /**
     * 获取单例实例
     * @return VmOpcode|null
     */
    public static function instance(): ?VmOpcode
    {
        if (null == static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * 初始化opcode level
     * @return void
     */
    private function initOpcodeLevel()
    {
        $this->opcodeLevel = xphp_get_config('app')['OPCODE_LEVEL']['PRIVATE'];
    }

    /**
     * 初始化opcode type
     * @return void
     */
    private function initOpcodeType()
    {
        $this->opcodeType = array(
            'SR_OP_TYPE_UNKNOWN',
            'SR_OP_TYPE_VIRTUAL_LAB',   //虚拟演练室
            'SR_OP_TYPE_SUREBACKUP',    //数据验证任务
            'SR_OP_TYPE_APPLICATION_GROUP', //应用组
        );
    }

    /**
     * 初始化opcode
     * @return void
     */
    private function initOpcode()
    {
        $this->opcode = array(
            'SR_OP_TYPE_UNKNOWN' => array(),

            //opcode
            'SR_OP_TYPE_VIRTUAL_LAB' => array(
                'SR_OP_TYPE_UNKNOWN',   //unknown opcode
                'SR_OP_CODE_DEPLOY_VIRTUAL_LAB',
                'SR_OP_CODE_UNDEPLOY_VIRTUAL_LAB',
                'SR_OP_CODE_MODIFY_VIRTUAL_LAB',
                'SR_OP_CODE_REFRESH_VIRTUAL_LAB',
                'SR_OP_CODE_QUERY_VCENTER_NET_LIST',
                'SR_OP_CODE_AUTO_CACULATE_NETWORK',
            ),

            'SR_OP_TYPE_SUREBACKUP' => array(
                'SR_OP_TYPE_UNKNOWN',   //unknown opcode type
                'SR_OP_CODE_CREATE_SUREBACKUP',
                'SR_OP_CODE_START_SUREBACKUP',
                'SR_OP_CODE_STOP_SUREBACKUP',
                'SR_OP_CODE_EDIT_SUREBACKUP',
                'SR_OP_CODE_DELETE_SUREBACKUP',
                'SR_OP_CODE_VERIFY_SCREEN_COMPARE', // 截屏
                'SR_OP_CODE_SUBMIT_SUREBACKUP_GMP_REPORT', // 停止任务并启动策略
            ),
            'SR_OP_TYPE_APPLICATION_GROUP' => array(
                'SR_OP_TYPE_UNKNOWN',   //unknown opcode type
                'SR_OP_CODE_CREATE_APP_GROUP',
                'SR_OP_CODE_EDIT_APP_GROUP',
                'SR_OP_CODE_DELETE_APP_GROUP',

            ),
        );
    }

    /**
     * 初始化操作描述
     * @return void
     */
    private function initOpcodeDes()
    {
        $this->opDes = array(
            'SR_OP_CODE_DEPLOY_VIRTUAL_LAB' => xphp_get_lang('WEB_SR_OP_CODE_DEPLOY_VIRTUAL_LAB'),
            'SR_OP_CODE_UNDEPLOY_VIRTUAL_LAB' => xphp_get_lang('WEB_SR_OP_CODE_UNDEPLOY_VIRTUAL_LAB'),
            'SR_OP_CODE_MODIFY_VIRTUAL_LAB' => xphp_get_lang('WEB_SR_OP_CODE_MODIFY_VIRTUAL_LAB'),
            'SR_OP_CODE_REFRESH_VIRTUAL_LAB' => xphp_get_lang('WEB_SR_OP_CODE_REFRESH_VIRTUAL_LAB'),
            'SR_OP_CODE_QUERY_VCENTER_NET_LIST' => xphp_get_lang('WEB_SR_OP_CODE_QUERY_VCENTER_NET_LIST'),
            'SR_OP_CODE_AUTO_CACULATE_NETWORK' => xphp_get_lang('WEB_SR_OP_CODE_AUTO_CACULATE_NETWORK'),
            'SR_OP_CODE_CREATE_SUREBACKUP' => xphp_get_lang('WEB_SR_OP_CODE_CREATE_SUREBACKUP'),
            'SR_OP_CODE_START_SUREBACKUP' => xphp_get_lang('WEB_SR_OP_CODE_START_SUREBACKUP'),
            'SR_OP_CODE_STOP_SUREBACKUP' => xphp_get_lang('WEB_SR_OP_CODE_STOP_SUREBACKUP'),
            'SR_OP_CODE_EDIT_SUREBACKUP' => xphp_get_lang('WEB_SR_OP_CODE_EDIT_SUREBACKUP'),
            'SR_OP_CODE_DELETE_SUREBACKUP' => xphp_get_lang('WEB_SR_OP_CODE_DELETE_SUREBACKUP'),
            'SR_OP_CODE_CREATE_APP_GROUP' => xphp_get_lang('WEB_SR_OP_CODE_CREATE_APP_GROUP'),
            'SR_OP_CODE_EDIT_APP_GROUP' => xphp_get_lang('WEB_SR_OP_CODE_EDIT_APP_GROUP'),
            'SR_OP_CODE_DELETE_APP_GROUP' => xphp_get_lang('WEB_SR_OP_CODE_DELETE_APP_GROUP'),

        );
    }
}
