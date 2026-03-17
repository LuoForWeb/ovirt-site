<?php

namespace app\v1\volcdp\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          卷实时 -- 之数据管理
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:11
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class VolcdpData extends AuthBase
{
    public function __construct()
    {
        parent::__construct();
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
     * 删除选中备份点
     */
    public function deleteSelectBackupSet()
    {
        $data = $this->logic()->deleteSelectBackupSet($this->param);
        return $this->success('',$data);
    }

    /**
     * 修改指定客户端标签点备注信息
     */
    public function remarkTagPoint()
    {
        $data = $this->logic()->remarkTagPoint($this->param);
        return $this->success('',$data);
    }

    /**
     * 删除选中的事件信息
     */
    public function deleteSelectEventInfo()
    {
        $data = $this->logic()->deleteSelectEventInfo($this->param);
        return $this->success('',$data);
    }

    /**
     * 删除标签点
     */
    public function deleteSelectLablePoint()
    {
        $data = $this->logic()->deleteSelectLablePoint($this->param);
        return $this->success(xphp_get_lang('UI_VOL_CDP_DELETE_TAG_SUCCESS'),$data);
    }

}
