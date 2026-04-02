<?php

namespace app\v1\verification\v0\logic;

use app\v1\common\logic\Base;

/**
 * note          数据验证CDM 备份管理 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:18
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class VerificationBackUp extends Base
{
    /**
     * 创建备份任务 demo
     * @param array $params 参数
     * @return string
     */
    public function createBackupJob($params = [])
    {

        // 这边处理控制器 转发过来的逻辑处理 这个里面只跟数据库交互和逻辑处理
        // 比如数据库交互
        // 查询列表 $this->dbSelect();
        // 执行sql $this->dbQuery();
        /**
        * 事务操作
             $this->dbBeginTransaction();
             $this->dbRollBack();
             $this->dbCommit();
         */


        // 需要和后台通信的 需要再次转发到 service 服务层去处理
        $return = $this->service()->createBackupJob($params);
        // 选择是否接受服务通信结果 然后返回到控制器
        if ($return) {
            // 根据服务返回的结果 进行一系列的操作等等
            // ...

        }
        return $return;
    }
}
