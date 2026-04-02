<?php

namespace app\v1\mirror\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          镜像管理
 * @author       wangru
 * @date         2024/07/15 11:48
 * @version      1.0.0
 */
class Mirror extends AuthBase
{
    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取镜像管理列表
     * @return void
     */
    public function getMirrorList()
    {
        $info = $this->logic()->getMirrorList($this->param);
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }

    /**
     * 删除资源
     * @return json
     */
    public function deleteMrriorList()
    {
        $result = $this->logic()->deleteMrriorList($this->param);
        $this->outputHandle($result);
    }

    public function sizeMirror()
    {
        $result = $this->logic()->sizeMirror($this->param);
        $this->outputHandle($result);
    }

    /**
     * 添加镜像
     * @return json
     */
    public function addMirror()
    {
        $result = $this->logic()->addMirror($this->param);
        $this->outputHandle($result);
    }


    /**
     * 清理镜像
     * @return json
     */
    public function clearMrriorList()
    {
        $result = $this->logic()->deleteMrriorList($this->param);
        return $result;
    }

}
