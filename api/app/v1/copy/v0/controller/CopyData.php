<?php

namespace app\v1\copy\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          副本 -- 之数据管理
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:11
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class CopyData extends AuthBase
{
    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function remarkTimePoint()
    {
        $info = $this->logic()->remarkTimePoint($this->param);
        if ($info) {
            return $this->success(xphp_get_lang('UI_COPY_ADD_REMARK_SUCCESS'));
        }
        return $this->error(xphp_get_lang('UI_COPY_ADD_REMARK_FAILED'));
    }
    public function addStar()
    {
        return $this->logic()->addStar($this->param);
    }
    public function deleteStar()
    {
        return $this->logic()->deleteStar($this->param);
    }
    public function searchCopyTimePoint()
    {
        $info = $this->logic()->searchCopyTimePoint($this->param);
        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    public function deleteBatchCopyPoint()
    {
        $info = $this->logic()->deleteBatchCopyPoint($this->param);
        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
}
