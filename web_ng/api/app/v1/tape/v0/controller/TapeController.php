<?php

namespace app\v1\tape\v0\controller;

use app\v1\common\controller\Base;
use app\v1\opcode\NodeOpcode;
use xphp\db\Op;

/**
 * note          磁带设备控制 
 * @author       wuyihang@vinchin.com
 * @date         2023/9/1 16:20
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class TapeController  extends Base
{
    /**
     * 扫描磁带库
     * @return json
     */
    public function scanTapeLib()
    {
        $logic = new \app\v1\tape\v0\logic\TapeController();
        // 逻辑层转发
        //$result = $this->logic()->scanTapeLib($this->param);
        $result = $logic->scanTapeLib($this->param);

        return $this->success('', $result, 200);
    }

    /**
     * 添加磁带组
     * @return json
     */
    public function addTapeGroup()
    {
        // 逻辑层转发
        $result = $this->logic()->addTapeGroup($this->param);

        return $this->success('', $result, 200);
    }

    /**
     * 修改磁带组
     * @return json
     */
    public function modifyTapeGroup()
    {
        // 逻辑层转发
        $result = $this->logic()->modifyTapeGroup($this->param);

        return $this->success('', $result, 200);
    }

    /**
     * 删除磁带组
     * @return json
     */
    public function delTapeGroup()
    {
        // 逻辑层转发
        $result = $this->logic()->delTapeGroup($this->param['group_uuid']);

        if ($result['result']) {
            return $this->success('', $result, 200);
        }
        return $this->error('', $result, $result['errorCode']);
    }

    
    /**
     * 删除备份集
     */
    public function delTapeBackupSet()
    {
        // 逻辑层转发
        $result = $this->logic()->delTapeBackupSet($this->param);

        if ($result['result']) {
            return $this->success('', $result, 200);
        }
        return $this->error('', $result, $result['errorCode']);
    }

    /**
     * 冻结/解冻 磁带备份集
     */
    public function opTapeBackupSet()
    {
        // 逻辑层转发
        $this->logic()->opTapeBackupSet($this->param);

        return $this->success('');
    }

    /**
     * 导入磁带
     * @return json
     */
    public function importTape()
    {
        // 逻辑层转发
        $result = $this->logic()->importTape($this->param);
        
        return $this->success('', $result, 200);
    }

    /**
     * 弹出磁带
     * @return json
     */
    public function exportTape()
    {
        // 逻辑层转发
        $result = $this->logic()->exportTape($this->param);

        return $this->success('', $result, 200);
    }
    
    /**
     * 导入磁带组
     * @return json
     */
    public function importTapeGroup()
    {
        // 逻辑层转发
        $result = $this->logic()->importTapeGroup($this->param);
        if ($result['result']) {
            return $this->success('', $result, 200);
        }
        return $this->error('', $result, $result['errorCode']);
    }

    /**
     * 检索磁带
     * @return json
     */
    public function retrievalTape()
    {
        // 逻辑层转发
        $result = $this->logic()->retrievalTape($this->param);

        return $this->success('', $result, 200);
    }

    /**
     * 提高优先级
     * @return json
     */
    public function increasePriority()
    {
        // 逻辑层转发
        $result = $this->logic()->increasePriority($this->param);

        return $this->success('', $result, 200);
    }

    /**
     * 降低优先级
     * @return json
     */
    public function decreasePriority()
    {
        // 逻辑层转发
        $result = $this->logic()->decreasePriority($this->param);

        return $this->success('', $result, 200);
    }
}