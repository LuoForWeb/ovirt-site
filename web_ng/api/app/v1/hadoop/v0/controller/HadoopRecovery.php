<?php

namespace app\v1\hadoop\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          hadoop -- 之集群恢复
 * @author       lilingyu@vinchin.com
 * @date         2023/11/13 16:15
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class HadoopRecovery extends AuthBase
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     *  获取所有集群列表
     */
    public function getClusterList(){
        $info = $this->logic()->getClusterList($this->param);
        $this->success('', $info);
    }
    
    /**
     *  获取集群列表树
     */
    public function getClusterZtree(){
        $info = $this->logic()->getClusterZtree($this->param);
        $this->success('', $info);
    }

    /**
     *  获取时间点树
     */
    public function getTimepointZtree(){
        $info = $this->logic()->getTimepointZtree($this->param);
        $this->success('', $info);
    }
    
    /**
     *  获取备份的文件列表
     */
    public function getFileZtree(){
        $info = $this->logic()->getFileZtree($this->param);
        return $this->outputMsg($info['result'], $info['msg'], $info['data'], $info['errorCode']);
    }

    /**
     *  创建搜索消息
     */
    public function createSearchJob(){
        $info = $this->logic()->createSearchJob($this->param);
        return $this->outputMsg($info['result'], $info['msg'], $info['data'], $info['errorCode']);
    }

    /**
     *  停止搜索消息
     */
    public function stopSearchJob(){
        $info = $this->logic()->stopSearchJob($this->param);
        return $this->outputMsg($info['result'], $info['msg'],  $info['data'], $info['errorCode']);
    }

    /**
     *  得到恢复搜索的结果
     */
    public function getSearchInfo(){
        $info = $this->logic()->getSearchInfo($this->param);
        return $this->outputMsg($info['result'], $info['msg'],  $info['data'], $info['errorCode']);
    }
    
    /**
     *  获取恢复任务名
     */
    public function getRecoveryTaskName(){
        $info = $this->logic()->getRecoveryTaskName($this->param);
        $this->success('', $info);
    }

    /**
     *  创建恢复任务
     */
    public function createRecoverJob(){
        $info = $this->logic()->createRecoverJob($this->param);
        return $this->outputMsg($info['result'], $info['msg'], '', $info['errorCode']);
    }

    /**
     *  获取恢复目录树
     */
    public function getRecoverPathTree(){
        $info = $this->logic()->getRecoverPathTree($this->param);
        return $this->outputMsg($info['result'], $info['msg'], $info['data'], $info['errorCode']);
    }
    
    
}