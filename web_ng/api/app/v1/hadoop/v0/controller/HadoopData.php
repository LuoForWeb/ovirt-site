<?php

namespace app\v1\hadoop\v0\controller;

use app\v1\common\controller\AuthBase;
/**
 * note          hadoop -- 备份数据
 * @author       lilingyu@vinchin.com
 * @date         2023/11/20
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class HadoopData extends AuthBase
{
    /**
     * HadoopCluster constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取时间点表格数据
     * @return unknown
     *
     * @author lilingyu@vinchin.com
     * @date 2023/9/14
     */
    public function getRestoreTimepointTable(){
        $info = $this->logic()->getRestoreTimepointTable($this->param);
        $this->success('', $info);
    }

    /**
     * 给时间点添加备注
     * @author lilingyu@vinchin.com
     * @date 2023/9/14
     */
    public function remarkTimepoint(){
        $info = $this->logic()->remarkTimepoint($this->param);
        return $this->outputMsg($info['result'], $info['msg'], '', $info['errorCode']);
    }

     /**
     * 给时间点添加星标
     * @author lilingyu@vinchin.com
     * @date 2023/9/14
     */
    public function addStar(){
        $info = $this->logic()->addStar($this->param);
        return $this->outputMsg($info['result'], $info['msg'], '', $info['errorCode']);
    }

    /**
     * 给时间点删除星标
     * @author lilingyu@vinchin.com
     * @date 2023/9/14
     */
    public function deleteStar(){
        $info = $this->logic()->deleteStar($this->param);
        return $this->outputMsg($info['result'], $info['msg'], '', $info['errorCode']);
    }


    /**
     * 删除备份时间点
     * @author lilingyu@vinchin.com
     * @date 2023/9/14
     */
    public function deleteTimepoint(){

        //获取接收到的参数
        $data =  $this->param;
        if(!empty($data['timepoint_uuid'])){
           $this->logic()->deleteTimepoint($this->param);
            
        }else{
            //获取集群列表
            $this->logic()->deleteSelectTimepoint($this->param);
            // return $this->outputMsg($info['result'], $info['msg'], '', $info['errorCode']);
        }
    }
     /**
     * 搜索时间点
     * @author lilingyu@vinchin.com
     * @date 2023/9/14
     */
    public function searchTimepoint(){
        $info = $this->logic()->searchTimepoint($this->param);
        $this->success('', $info);
    }
    
    
    
}
