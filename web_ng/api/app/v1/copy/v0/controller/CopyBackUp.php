<?php
/*
 * @note: 
 * @author: chenyunfeng@vinchin.com
 * @Description: 副本容灾 --- 新建/修改任务控制
 * @Date: 2023-08-23 09:53:59
 * @LastEditTime: 2026-01-22 11:31:38
 * @Version: 1.0
 * @copyright: Copyright 2023 vinchin.com
 */

namespace app\v1\copy\v0\controller;

use app\v1\common\controller\AuthBase;

class CopyBackUp extends AuthBase
{
    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 创建备份任务 demo
     * @return json
     */
    public function createCopyJob()
    {
        // 1 参数验证
        /** 解释:
         * list 对应当前父目录同级目录 validate下面的同名验证器 VmBackUp.php 文件
         * 可选参数 第二个和第三个 分别是class和参数数组 默认是当前同名的验证器和所有接收的参数数组
         * 注：可以自己对数据进行拆分组装
         * */
        // $this->checkParams('list');

        // 2 请求转发到logic去处理
        /**
         * 解释:
         * createBackupJob 对应当前父目录同级目录 logic 下面的同名logic VmBackUp.php 文件里面的 方法名称
         * 注： $this->param; 可以接收前端传来的所有参数数组 但是这种方式只能在控制器里面调用
         */
        $info = $this->logic()->createCopyJob($this->param);

        // 3 返回结果到用户
        /**
         * $this->success() 或者 $this->error()
         */
        return $this->success('',$info);
    }
    public function editCopyJob()
    {
        // 1 参数验证
        /** 解释:
         * list 对应当前父目录同级目录 validate下面的同名验证器 VmBackUp.php 文件
         * 可选参数 第二个和第三个 分别是class和参数数组 默认是当前同名的验证器和所有接收的参数数组
         * 注：可以自己对数据进行拆分组装
         * */
        // $this->checkParams('list');

        // 2 请求转发到logic去处理
        /**
         * 解释:
         * createBackupJob 对应当前父目录同级目录 logic 下面的同名logic VmBackUp.php 文件里面的 方法名称
         * 注： $this->param; 可以接收前端传来的所有参数数组 但是这种方式只能在控制器里面调用
         */
        $info = $this->logic()->editCopyJob($this->param);

        // 3 返回结果到用户
        /**
         * $this->success() 或者 $this->error()
         */
        return $this->success('', $info);
    }

    /**
     * @description: 获取副本源树
     */
    public function getCopySrcTask()
    {
        $info = $this->logic()->getCopySrcTask($this->param);
        return $this->success('', $info);
    }
    /**
     * @description: 获取副本源树--对象节点
     */
    public function getCopySrcItem()
    {
        $info = $this->logic()->getCopySrcItem($this->param);
        return $this->success('', $info);
    }

    /**
     * @description: 异步回去时间点
     */
    public function getSyncCopyTimePoint()
    {
        $info = $this->logic()->getSyncCopyTimePoint($this->param);
        return $this->success('', $info);
    }

    /**
     * @description: 获取修改前的副本任务信息
     */
    public function getCopyTaskInfo()
    {
        $info = $this->logic()->getCopyTaskInfo($this->param);
        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }

    /**
     * @description: 获取源任务的数据类型 备份|副本  任务|时间点
     */
    public function getSourceDataType()
    {
        $info = $this->logic()->getSourceDataType($this->param);
        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }

    /**
     * @description: 获取任务名
     * @return {*}
     */    
    public function getCopyTaskName()
    {
        $info = $this->logic()->getCopyTaskName($this->param);
        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    /**
     * @description: 获取异地存储数据树—任务信息
     * @return {*}
     */    
    public function getRemoteTreeTask()
    {
        $info = $this->logic()->getRemoteTreeTask($this->param);
        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    /**
     * @description: 获取异地存储数据树-主机信息
     * @return {*}
     */    
    public function getRemoteTreeHost()
    {
        $info = $this->logic()->getRemoteTreeHost($this->param);
        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    /**
     * @description: 获取异地存储任务数据树-时间点信息
     * @return {*}
     */    
    public function getRemoteTreeTimePOint()
    {
        $info = $this->logic()->getRemoteTreeTimePOint($this->param);
        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    /**
     * @description: 获取节点信息
     * @return {*}
     */    
    public function getNode()
    {
        $info = $this->logic()->getNode($this->param);
        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    /**
     * @description: 创建回传任务
     * @return {*}
     */    
    public function createCopyBackJob()
    {
        $info = $this->logic()->createCopyBackJob($this->param);
        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    /**
     * @description: 获取异地存储网络
     * @return {*}
     */
    public function getRemoteNetInfo()
    {
        $info = $this->logic()->getRemoteNetInfo($this->param);
        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    public function getItemListInTask()
    {
        $info = $this->logic()->getItemListInTask($this->param);
        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    /**
     * @description: 搜索源
     * @return {*}
     */
    public function searchCopySource()
    {
        $info = $this->logic()->searchCopySource($this->param);
        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
}
