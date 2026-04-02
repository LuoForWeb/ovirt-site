<?php

namespace app\v1\exchange\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          office365（exchange） -- 之数据管理
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:11
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class ExchangeData extends AuthBase
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 删除备份数据
     * @return object 组织相关信息
     */
    public function deleteTimePoint()
    {
        $result = $this->logic()->deleteTimePoint($this->param);
        if ($result['result']) {
            return $this->success(xphp_get_lang('WEB_M365_SERVER_DELETE_TIME_POINT'), $result);
        } else {
            return $this->error(xphp_get_lang('WEB_M365_SERVER_DELETE_TIME_POINT'), $result);
        }
    }

    /**
     * 添加备注
     * @return object 添加备注结果
     */
    public function remarkTimepoint()
    {
        $result = $this->logic()->remarkTimepoint($this->param);
        if ($result) {
            return $this->success(xphp_get_lang('UI_COPY_ADD_REMARK_SUCCESS'), $result);
        } else {
            return $this->error(xphp_get_lang('UI_COPY_ADD_REMARK_FAILED'), $result);
        }
    }

    /**
     * 云存储上的完备点索引数据同步
     * @return object同步结果
     */
    public function syncPointData()
    {
        $result = $this->logic()->syncPointData($this->param);
        if ($result) {
            return $this->success(xphp_get_lang('WEB_M365_SERVER_SYNC_DATA_SUCCESS'), $result);
        } else {
            return $this->error(xphp_get_lang('WEB_M365_SERVER_SYNC_DATA_ERROR'), $result);
        }
    }

    /**
     * 添加星标
     * @return 添加星标结果
     */
    public function addStar(){

        $return = $this->logic()->addStar($this->param);
        if ($return) {
            return  $this->success(xphp_get_lang('WEB_M365_SERVER_ADD_FOREVER_MARK'), $return);
        }
        return $this->error(xphp_get_lang('WEB_M365_SERVER_ADD_FOREVER_MARK'), $return);

    }

    /**
     * 删除星标
     * @return 删除星标结果
     */
    public function deleteStar() {
        $return = $this->logic()->deleteStar($this->param);
        if ($return) {
            return  $this->success(xphp_get_lang('WEB_M365_SERVER_DELETE_FOREVER_MARK'), $return);
        }
        return $this->error(xphp_get_lang('WEB_M365_SERVER_DELETE_FOREVER_MARK'), $return);
    }
}
