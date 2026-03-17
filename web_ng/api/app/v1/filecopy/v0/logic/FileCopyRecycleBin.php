<?php

namespace app\v1\filecopy\v0\logic;

use app\v1\common\logic\Base;
use app\v1\opcode\PfOpcode;
use app\v1\resources\v0\logic\Node;
use app\v1\tenant\v0\logic\Index;
use app\v1\user\v0\logic\User;

/**
 * note          文件同步 -- 回收站 logic
 * @author       wuxian@vinchin.com
 * @date         2024/7/17 16:15
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */
class FileCopyRecycleBin extends Base
{
    /**
     * 获取回收站展示数据
     * @param $params 参数
     * @return object 回收站展示数据的任务、agent、时间
     */
    public function getCollectionTask($params = [])
    {
        // $sql = 'select scl.task_uuid, scl.path, scl.same_file_keep_number, scl.file_keep_time, scl.use_storage_size,
        // scl.recycle_bin_uuid, scl.storage_current_use_size, scl.object_uuid, st.aim_proxy_uuid from 
        // sync_cache_list scl left join sync_task st on st.task_uuid = scl.task_uuid';
        // $data = $this->dbSelect($sql, array());
        // $info = array();
        // $result = $this->service()->getCollectionTask($params);
        // if (!empty($data)) {
        //     $taskList = array(); //用于保存任务数据
        //     $agentList = array(); //用于保存agent数据
        //     $recycleBinList = array(); //用于保存回收站数据
        //     foreach ($data as $each) {
        //         //任务
        //         if(!in_array($each['task_uuid'], $taskList)) {
        //             $taskList[] = $each['task_uuid'];
        //             $info[] = array(
        //                 'task_uuid' => $each['task_uuid'],
        //                 'task_name' => $each['task_name'],//需要后台存
        //             );
        //         }
        //         //目标端agent
        //         if(!in_array($each['task_uuid'] . '_' . $each['aim_proxy_uuid'], $agentList)) {
        //             $agentList[] = $each['task_uuid'] . '_' . $each['aim_proxy_uuid'];
        //             $info[] = array(
        //                 'aim_proxy_uuid' => $each['aim_proxy_uuid'],
        //                 'agent_name' => $each['agent_name'],//需要后台存类型去查或者直接存名字
        //             );
        //         }
        //         //回收站
        //         if(!in_array($each['recycle_bin_uuid'], $recycleBinList)) {
        //             $recycleBinList[] = $each['recycle_bin_uuid'];
        //             $info[] = array(
        //                 'recycle_bin_uuid' => $each['recycle_bin_uuid'],
        //                 'path' => $each['path'],
        //             );
        //         }
        //     }
        // } else {
        //     return $this->muOpResult(false,'操作码', '获取回收站任务和同步对象', '', $result['errorCode']);
        // }
        $info = array(
            [
                "name" => 'filecopytask1',
                "id" => 'taskuuid1',
                "pId" => 0,
                "job_uuid" => 'taskuuid1',
                "task_name" => 'filecopytask1',
            ],
            [
                "name" => 'WINDOWS_INFJS(192.168.54.2)',
                "id" => 'taskuuid1_aim_proxy_uuid',
                "pId" => 'taskuuid1',
                "job_uuid" => 'taskuuid1',
            ],
            [
                "name" => '/E:/RecycleBin',
                "id" => 'taskuuid1_aim_proxy_uuid_' . '/E:/RecycleBin',
                "pId" => 'taskuuid1_aim_proxy_uuid',
                "job_uuid" => 'taskuuid1',
                "recycle_bin_uuid" => 'recycle_bin_uuid1',
            ],
        );

        return $info;
    }

    /**
     * 获取某个回收站中的文件路径
     * @param $params 参数
     * @return object 返回文件路径
     */
    public function getCollectionPath($params = [])
    {
        $info = array();
        $result = $this->service()->getCollectionPath($params);
        if ($result['result']) {
            $info = $result['data'];
        } else {
            return $this->muOpResult(false,'operate',xphp_get_lang('UI_FILE_COPY_GET_RECYCLE_BIN_PATH'), '', $result['errorCode']);
        }
        return $info;
    }

    /**
     * 删除回收站数据(清空回收站也用此接口)
     * @param $params 参数
     * @return object 删除的结果
     */
    public function deleteCollectionData($params = []) {
        return $this->muOpResult(true,'operate', xphp_get_lang('UI_FILE_COPY_DEL_RECYCLE_BIN_DATA'), '', 0);
        $result = $this->service()->deleteCollectionData($params);
        return $this->muOpResult(false,'operate', xphp_get_lang('UI_FILE_COPY_DEL_RECYCLE_BIN_DATA'), '', $result['errorCode']);
    }

    /**
     * 还原回收站数据
     * @param $params 参数
     * @return object 还原的结果
     */
    public function restoreCollectionData($params = []) {
        $result = $this->service()->restoreCollectionData($params);
        return $this->muOpResult(false,'operate', xphp_get_lang('UI_FILE_COPY_RESTORE_RECYCLE_BIN_DATA'), '', $result['errorCode']);
    }
}
