<?php

namespace app\v1\dbcdp\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          数据库实时 -- 之数据管理
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:11
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class DbcdpData extends AuthBase
{
    /**
     * DbcdpData constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     *  获取可恢复数据
     * @return $dataList 恢复数据列表
     */
    public function getRestoreData()
    {
        if (!empty($this->param['restore_data_uuid'])) {  // 获取指定客户端备份数据
            // 参数验证
            $this->checkParams('standby_restore_data');
            $dataList = $this->logic()->getStandbyRestoreData($this->param);
        } else {  // 获取客户端列表
            // 参数验证
            $this->checkParams('restore_data');
            $dataList = $this->logic()->getRestoreDataList($this->param);
        }

        if (!$dataList) {
            $this->error();
        }
        $this->success('', $dataList);
    }

    /**
     * 获取可恢复的实例
     */
    public function getRestoreInstances()
    {
        $ret = $dataList = $this->logic()->getRestoreInstances();
        $this->outputHandle($ret);
    }

    /**
     * 获取与恢复源相关联的主机信息
     * @return $agentOInfo 主机信息
     */
    public function getRestoreDataAgentInfo()
    {
        $this->checkParams('restore_data');
        $agentInfo = $this->logic()->getRestoreDataAgentInfo($this->param['restore_data_uuid']);
        if (!$agentInfo) {
            $this->error();
        }
        $this->success('', $agentInfo);
    }

    /**
     * 获取与恢复源相关联的主机信息
     * @return $agentOInfo 主机信息
     */
    public function getRestoreDataSourceInfo()
    {
        $this->checkParams('restore_data');
        $agentInfo = $this->logic()->getRestoreDataSourceInfo($this->param['restore_data_uuid']);
        if (!$agentInfo) {
            $this->error();
        }
        $this->success('', $agentInfo);
    }
}
