<?php

namespace app\v1\job\v0\controller;

use app\v1\common\controller\AuthBase;
use xphp\db\Op;

/**
 * note          公共管理 -- 之任务操作
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/10 9:57
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class JobController extends AuthBase
{
    /**
    * 启动任务 批量/单个 demo
     * @return json
     */
    public function startJob()
    {
        // 参数验证
        if (!empty($this->param['start_uuid'])) {
            // 单个
            $this->checkParams('start');
        } else {
            // 批量
            $this->checkParams('startBatch');
        }

        // 逻辑层转发
        $return = $this->logic()->startJob($this->param);

        return $this->output($return);
    }

    /**
     * 停止任务 批量/单个
     * @return json
     */
    public function stopJob()
    {
        // 参数验证
        if (!empty($this->param['stop_uuid'])) {
            // 单个
            $this->checkParams('stop');
        } else {
            // 批量
            $this->checkParams('stopBatch');
        }

        // 逻辑层转发
        $return = $this->logic()->stopJob($this->param);

        return $this->output($return);
    }

    /**
     * 删除任务 批量/单个
     * @return json
     */
    public function delJob()
    {
        // 参数验证
        if (!empty($this->param['jobs_uuid'])) {
            // 单个
            $this->checkParams('del');
        } else {
            // 批量
            $this->checkParams('delBatch');
        }

        // 逻辑层转发
        $return = $this->logic()->delJob($this->param);

        return $this->output($return);
    }

    /**
     * 启动接管任务
     * @return json
     */
    public function startTakeover()
    {
        // 参数验证
        $this->checkParams('pause_task');

        // 逻辑层转发
        $return = $this->logic()->startTakeover($this->param);

        return $this->output($return);
    }

    /**
     * 停止接管任务
     * @return json
     */
    public function stopTakeover()
    {
        // 参数验证
        $this->checkParams('pause_task');

        // 逻辑层转发
        $return = $this->logic()->stopTakeover($this->param);

        return $this->output($return);
    }

    /**
     * 切换自动接管配置
     * @return json
     */
    public function switchTakeoverConfig()
    {
        // 逻辑层转发
        $return = $this->logic()->switchTakeoverConfig($this->param);

        return $this->output($return);
    }

    /**
     * 启动回切任务
     * @return json
     */
    public function startFailback()
    {
        // 参数验证
        $this->checkParams('pause_task');

        // 逻辑层转发
        $return = $this->logic()->startFailback($this->param);

        return $this->output($return);
    }

    /**
     * 暂停任务
     * @return json
     */
    public function pauseTask()
    {
        // 参数验证
        $this->checkParams('pause_task');

        // 逻辑层转发
        $return = $this->logic()->pauseTask($this->param);

        return $this->output($return);
    }

    /**
     * 继续任务 未用
     * @return json
     */
    public function continueTask()
    {
        return $this->error();
    }

    /**
     * 删除历史任务 批量/单个
     * @return json
     */
    public function delHistory()
    {
        // 参数验证
        if (!empty($this->param['history_uuid'])) {
            // 单个
            $this->checkParams('del_history');
        } else {
            $this->checkParams('batchdel_history');
        }
        // 逻辑层转发
        $return = $this->logic()->delHistory($this->param);

        if ($return) {
            $this->success();
        }
        $this->error();
    }

    /**
     * 下载历史任务日志检查
     * @return json
     */
    public function downloadHistoryCheck()
    {
        // 参数验证
        $this->checkParams('jobs_log');

        // 逻辑层转发
        $return = $this->logic()->downloadHistoryCheck($this->param['log_uuid']);

        if ($return) {
            $this->success('', $return);
        }
        $this->error();
    }

    /**
     * 下载任务告警日志
     * @return json
     */
    public function downLoadTaskLog()
    {
        // 参数验证
        $this->checkParams('jobs_down_log');

        // 逻辑层转发
        echo $this->logic()->downLoadTaskLog($this->param['log_down_uuid']);
        ob_flush(); //将数据从php的buffer中释放出来
        flush(); //将释放出来的数据发送给浏览器
    }

    /**
     * 更新挂起任务优先级
     * @return json
     */
    public function updatePendingSort()
    {

        // 逻辑层转发
        $return = $this->logic()->updatePendingSort($this->param);

        if ($return['code'] == 0) {
            return $this->success($return['msg']);
        }
        return $this->error($return['msg']);
    }

    /**
     * 统一输出
     * @param array $return 返回信息
     * @return string
     **/
    private function output($return = [])
    {
        if (empty($return)) {
            $this->error();
        }
        // 进行梳理
        $successNum = 0;
        $op = new Op();
        foreach ($return as $item) {
            $msg[] = $op->muOpResult($item[0], $item[1], $item[2], '', $item[3] ?? 0);
            if (empty($this->param['job_uuids'])) {
                // 单个
                $method = $item[0] ? 'success' : 'error';
                return $this->$method($msg[0]['message'], $msg[0]['data'], $msg[0]['code']);
            }
            // 批量
            $item[0] ? $successNum++ : '';
        }

        $meassge = array_column($msg, 'message');

        if ($successNum > 0) {
            // 有执行成功的 那么就以成功的形式返回
            $this->success('', ['info' => $meassge]);
        }
        // 以失败的形式返回
        $this->error('', ['info' => $meassge]);
    }
}
