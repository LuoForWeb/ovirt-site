<?php

namespace app\v1\complete_machine_volcdp\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          卷实时 --备份数据管理
 * @author       jiangyongjie@vinchin.com
 * @date         2024-7-31
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class CmBackupSetData extends AuthBase
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取标签点信息
     * @author        jiangyongjie@vinchin.com
     * @date         2024-7-31
     */
    public function getBackupSetTagPointInfo()
    {
        $data = $this->param; //获取接受到的参数
        $info = $this->logic()->getBackupSetTagPointInfo($this->param);  //获取标签点列表
        $this->success('', $info);
    }
    
    /**
     * 获取病毒扫描点信息
     */
    public function getBackupSetSafePointInfo(){
        $data = $this->param;  //获取接受到的参数
        $info = $this->logic()->getBackupSetSafePointInfo($this->param);  //获取任务对应客户端产生的事件列表
        $this->success('', $info);
    }

    /**
     * 获取事件点信息
     * @author        jiangyongjie@vinchin.com
     * @date         2024-8-1
     */
    public function getBackupSetEventInfo(){
        $data = $this->param;  //获取接受到的参数
        $info = $this->logic()->getBackupSetEventInfo($this->param);  //获取任务对应客户端产生的事件列表
        $this->success('', $info);
    }

    /**
     * 获取当前节点下备份客户端
     */
    public function getBackupSetTree()
    {
        $data = $this->logic()->getBackupSetTree($this->param);
        return $this->success('',$data);
    }
    /**
     * 获取指定客户端对应备份集范围
     * @return void
     */
    public function getBackupSetTimeRange(){
        $data = $this->logic()->getBackupSetTimeRange($this->param);
        return $this->success('', $data);
    }
    /**
     * 获取客户端备份时间轴对应的数据
     */
    public function getBackupSetTimelineData()
    {
        $data = $this->logic()->getBackupSetTimelineData($this->param);
        return $this->success('',$data);
    }

    /**
     * 校验时间点是否有效
     */
    public function verifyTimePointIsValid(){
        $data = $this->logic()->verifyTimePointIsValid($this->param);
        return $this->success('',$data);
    }

}
