<?php

namespace app\v1\filecopy\v0\service;

use app\v1\common\service\Base;
use app\v1\resources\v0\logic\Node;

/**
 * note          文件同步 -- 服务通信
 * @author       wuxian@vinchin.com
 * @date         2024/7/17 16:15
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */
class Service extends Base
{
    /**
     * 创建备份任务
     * @param string $nodeuuid 节点uuid
     * @param array $msg   传给后台的参数
     * @param string $opName 操作码
     * @return unknown
     */
    public function createFileCopyJob($nodeuuid, $msg, $opName)
    {
        $result = $this->mbFCMsg($nodeuuid, $opName, json_encode($msg));
        return $result;
    }

    /**
     * 创建备份任务
     * @param string $nodeuuid 节点uuid
     * @param array $msg   传给后台的参数
     * @param string $opName 操作码
     * @return unknown
     */
    public function opUnifyMsg(string $taskUuid, string $opName, array $msg, $sync = false, $command = false)
    {
        $nodeuuid = (new Node())->getNodeUUIDWithTaskUUID($taskUuid);   //任务UUID对应的存储节点uuid
        return $this->mbFCMsg($nodeuuid, $opName, json_encode($msg), $sync, $command);
    }

    /**
     * 获取某个回收站中的文件路径
     * @param $params 参数
     * @return object 返回文件路径
     */
    public function getCollectionPath($params)
    {
        $msg = array(
            'recycle_bin_uuid' => $params['recycle_bin_uuid'],//回收站的uuid
            'file_path' => $params['file_path'],//需要展开的目录的路径
            'code_type' => $params['code_type'],//编码类型
            'offset' => $params['offset'],//请求的偏移
            'number' => $params['number'],//请求的个数
        );
        $opName = '';
        $result = array(
            'result' => true,
            'data' => array(
                [
                    "name" => '/E:/RecycleBin',
                    "file_path" => '/E:/RecycleBin',
                    "file_type" => 2,
                    "code_type" => 1,
                    "id" => '/E:/RecycleBin',
                    "pId" => 0,
                    "job_uuid" => 'taskuuid1',
                    "task_name" => 'filecopytask1',
                    "recycle_bin_uuid" => 'recycle_bin_uuid1',
                ],
                [
                    "name" => '111.txt',
                    "file_path" => '/E:/RecycleBin/111.txt',
                    "file_type" => 1,
                    "code_type" => 1,
                    "id" => '/E:/RecycleBin/111.txt',
                    "pId" => '/E:/RecycleBin',
                    "job_uuid" => 'taskuuid1',
                    "recycle_bin_uuid" => 'recycle_bin_uuid1',
                ],
                [
                    "name" => 'hhhhh.pdf',
                    "file_path" => '/E:/RecycleBin/hhhhh.pdf',
                    "file_type" => 1,
                    "code_type" => 1,
                    "id" => '/E:/RecycleBin/hhhhh.pdf',
                    "pId" => '/E:/RecycleBin',
                    "job_uuid" => 'taskuuid1',
                    "recycle_bin_uuid" => 'recycle_bin_uuid1',
                ],
            ),
        );
        //发送消息给后台
        // $result = $this->mbFCMsg($params['node_uuid'], $opName, json_encode($msg), true);
        return $result;
    }
    /**
     * 删除回收站数据(清空回收站也用此接口)
     * @param $params 参数
     * @return object 删除的结果
     */
    public function deleteCollectionData($params = []) {
        $msg = array(
            'recycle_bin_uuid' => $params['recycle_bin_uuid'],//回收站的uuid
            'delete_list' => $params['delete_list'],//需要删除的文件或目录信息
        );
        $opName = '';
        //发送消息给后台
        $result = $this->mbFCMsg($params['node_uuid'], $opName, json_encode($msg), true);
        return $result;
    }

    /**
     * 还原回收站数据
     * @param $params 参数
     * @return object 还原的结果
     */
    public function restoreCollectionData($params = []) {
        $opName = '';
        $msg = array(
            'recycle_bin_uuid' => $params['recycle_bin_uuid'],//回收站的uuid
            'restore_list' => $params['restore_list'],//需要还原的文件或目录信息
        );
        //发送消息给后台
        $result = $this->mbFCMsg($params['node_uuid'], $opName, json_encode($msg), true);
        return $result;
    }

    /**
     * 绝对路径访问
     * @param unkown $params 参数
     * @return string 返回结果
     */
    public function accessByAbsolutePath($params = [])
    {
        $opName = '';
        $msg = array(
            'target_uuid' => $params['recycle_bin_uuid'],//目标uuid
            'file_path' => $params['restore_list'],//文件路径
            'file_type' => $params['file_type'],//文件类型
            'proxy_uuid' => $params['proxy_uuid'],//代理uuid
        );
        $result = $this->mbFCMsg($params['node_uuid'], $opName, json_encode($msg), true);
        if (!$result['result']) {
            return $this->muOpResult($result['result'], $opName, $result['msg'], '', $result['errorCode']);
        }
        return $result;
    }

    /**
     * 获取比对结果（任务详情使用）
     * @param unkown $params 参数
     * @return string 返回结果
     */
    public function getCompareResult($params = [])
    {
        $opName = 'SYNC_FS_OP_CODE_LIST_COMPARE_RESULT';
        $nodeuuid = $params['node_uuid'] ?? (new Node())->getNodeUUIDWithTaskUUID($params['info']['task_uuid']);
        return $this->mbFCMsg($nodeuuid, $opName, json_encode($params['info']), true);
    }

    /**
     * 复制比对结果（任务详情使用）
     * @param unkown $params 参数
     * @return string 返回结果
     */
    public function copyCompareResult($params = [])
    {
        $opName = 'SYNC_FS_OP_CODE_SYNC_COMPARE_RESULT';
        $msg = $params['info'];
        $msg['backup_mode'] = xphp_get_config('task', 'TASKTYPE')['FILE_COPY'];
        $msg['time_strategy_id'] = 0;
        $msg['auto_start_flag'] = xphp_get_config('app', 'FLAG')['UNSET'];
        $msg['sync_mode'] = xphp_get_config('file_copy')['SYNC_MODE']['SYNC_FS_TASK_MODE_WAIT_FILE_SYNC'];
        return $this->opUnifyMsg($msg['task_uuid'], $opName, $msg, false, true);
    }

    /**
     * 下载跳过文件
     * @param $nodeUuid nodeuuid
     * @param $msg      组合消息
     * @return object下载结果
     */
    public function downLoadPassData($nodeUuid, $msg)
    {
        $opName = "SYNC_FS_OP_CODE_FILE_DOWNLOAD";
        //发送消息给后台
        $result = $this->mbFCMsg($nodeUuid, $opName, json_encode($msg), true);
        //返回结果到UI
        if (is_array($result) && $result['errorCode']) {
            echo $result['errorMsg'];
            exit();
        } else {
            return $result['msg']['data'];
        }
    }
}
