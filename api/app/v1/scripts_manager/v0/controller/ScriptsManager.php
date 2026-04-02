<?php

namespace app\v1\scripts_manager\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          容器 -- 集群管理
 * @author       liushuai@vinchin.com
 * @date         2023/4/13
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class ScriptsManager extends AuthBase
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取脚本列表
     * 获取单个脚本详情
     * @author       liushuai@vinchin.com
     * @date         2023/4/14
     */
    public function getScript()
    {
        //获取接受到的参数
        $data = $this->param;
        if (!empty($data['scripts_uuid'])) {
            //获取单个脚本详情
            $info = $this->logic()->getScriptThis($this->param);
            return $this->success(xphp_get_lang('WEB_SCRIPT_GET_SCRIPT_DETAIL_SUCCESS'), $info);
        }
        //获取脚本列表
        $info = $this->logic()->getScript($this->param);
        return $this->success(xphp_get_lang('WEB_SCRIPT_GET_SCRIPT_LIST_SUCCESS'), $info);
    }

    /**
     * 添加脚本
     * @author       liushuai@vinchin.com
     * @date         2023/4/14
     */
    public function addScript()
    {
        //获取接受到的参数
        $data = $this->param;

        if (!empty($data['scripts_uuid'])) {
            //修改单个脚本
            $info = $this->logic()->editScriptThis($this->param);
            return $this->success(xphp_get_lang('WEB_SCRIPT_EDIT_SCRIPT_DETAIL_SUCCESS'), $info);
        }


        //添加脚本
        $info = $this->logic()->addScript($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }

    /**
     * 删除脚本
     * @author liushuai@vinchin.com
     * @date         2023/4/14
     *
     */
    public function deleteScript()
    {
        //获取接受到的参数
        $data = $this->param;
        //删除单个脚本或批量脚本
        $info = $this->logic()->deleteScript($this->param);
        if ($info) {
            return $this->success(xphp_get_lang('WEB_SCRIPT_DELETE_SCRIPT_SUCCESS'));
        } else {
            return $this->error(xphp_get_lang('WEB_SCRIPT_DELETE_SCRIPT_FAILURE'));
        }
    }
}
