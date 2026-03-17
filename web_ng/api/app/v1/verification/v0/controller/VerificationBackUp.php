<?php

namespace app\v1\verification\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          数据验证CDM -- 之备份管理
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:15
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class VerificationBackUp extends AuthBase
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
    public function createBackupJob()
    {
        // 1 参数验证
        /** 解释:
         * list 对应当前父目录同级目录 validate下面的同名验证器 VmBackUp.php 文件
         * 可选参数 第二个和第三个 分别是class和参数数组 默认是当前同名的验证器和所有接收的参数数组
         * 注：可以自己对数据进行拆分组装
         * */
        $this->checkParams('list');

        // 2 请求转发到logic去处理
        /**
        * 解释:
         * createBackupJob 对应当前父目录同级目录 logic 下面的同名logic VmBackUp.php 文件里面的 方法名称
         * 注： $this->param; 可以接收前端传来的所有参数数组 但是这种方式只能在控制器里面调用
         */
        $return = $this->logic()->createBackupJob($this->param);

        // 3 返回结果到用户
        /**
        * $this->success() 或者 $this->error()
         */
        $this->success($return);
    }
}
