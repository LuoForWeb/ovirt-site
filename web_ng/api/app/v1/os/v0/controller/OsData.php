<?php

namespace app\v1\os\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          操作系统 -- 之数据管理
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:11
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class OsData extends AuthBase
{
    /**
    * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }
     /**
     * 获取时间点树
     */
    public function getTimepointTree(){
        $info = $this->logic()->getTimepointTree($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }
    
     /**
     * 异步获取时间点
     */
    public function getSyncTimepoint(){
        $info = $this->logic()->getSyncTimepoint($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }
     /**
     * 获取时间点数据
     */
    public function getOsTimepointGrid(){
        $info = $this->logic()->getOsTimepointGrid($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }


     /**
     * 获取时间点数据
     */
    public function searchTimepoint(){
        $info = $this->logic()->searchTimepoint($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }

    


}
