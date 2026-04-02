<?php
/*
 * @note: 备份数据管理 服务通信 service
 * @author: chenyunfeng@vinchin.com
 * @Description: 
 * @Date: 2024-08-06 15:00:14
 * @LastEditTime: 2025-07-18 15:22:36
 * @Version: 2.0
 * @copyright: Copyright 2024 vinchin.com
 */

namespace app\v1\backupData\v0\service;

use app\v1\common\service\Base;


class Service extends Base
{
    /**
     * 设置时间点备注
     * @param mixed $params
     * @return {}
     */
    public function updateRemarks($params)
    {
        $msg = [
            "timepoint_uuid" => $params['time_point_uuid'],
            "remarks" => $params['remarks'],
            'op_user_uuid' => xphp_get_user_info()['userUuid'],
        ];
        $opName = 'TIMEPOINT_WEB_OP_UPDATE_BACKUP_POINT_REMARKS';
        //给后台发消息
        return $this->mbPointMsg($params['node_uuid'], $opName, json_encode($msg), 0, true);
    }
    /**
     * 设置gfs标记
     * @param mixed $params
     * @return {}
     */
    public function setGfsMark($params)
    {
        $msg = array(
            'timepoint_uuid' => $params['timepoint_uuid'],
            'item_list' => $params['item_list'],
            'op_user_uuid' => xphp_get_user_info()['userUuid'],
        );
        $opName = 'TIMEPOINT_WEB_OP_UPDATE_BACKUP_POINT_GFS_FLAG';
        //给后台发消息
        return $this->mbPointMsg($params['node_uuid'], $opName, json_encode($msg), 0, true);
    }
    /**
     * 设置永久标记
     * @param mixed $params
     * @return {}
     */
    public function addStar($params)
    {
        $msg = [
            'timepoint_uuid' => $params['timepoint_uuid'],
            'module_type' => $params['module_type'],
            'task_type' => $params['task_type'],
            'importance_flag' => xphp_get_config('app','FLAG')['SET'],
            'op_user_uuid' => xphp_get_user_info()['userUuid'],
        ];
        $opName = 'TIMEPOINT_WEB_OP_UPDATE_BACKUP_POINT_IMPORTANCE_FLAG';
        //给后台发消息
        return $this->mbPointMsg($params['node_uuid'], $opName, json_encode($msg), 0, true);
    }

    /**
     * 删除永久标记
     * @param mixed $params
     * @return {}
     */
    public function deleteStar($params)
    {
        $msg = [
            'timepoint_uuid' => $params['timepoint_uuid'],
            'module_type' => $params['module_type'],
            'task_type' => $params['task_type'],
            'op_user_uuid' => xphp_get_user_info()['userUuid'],
            'importance_flag' => xphp_get_config('app','FLAG')['UNSET'],
        ];
        $opName = 'TIMEPOINT_WEB_OP_UPDATE_BACKUP_POINT_IMPORTANCE_FLAG';
        return $this->mbPointMsg($params['node_uuid'], $opName, json_encode($msg), 0, true);
    }
    /**
     * 删除时间点
     * @param string $node_uuid
     * @param {json} $msg
     * @param string $opName
     * @return {}
     */
    public function deletePoints($node_uuid, $msg, $opName)
    {
        return $this->mbPointMsg($node_uuid, $opName, $msg, 0, true);
    }
    /**
     * 获取异地数据列表
     * @param string $node_uuid
     * @param int $submodule_type
     * @param string $opName
     * @param {json} $msg
     * @return {}
     */
    public function getRemoteData($node_uuid, $submodule_type, $opName, $msg)
    {
        $msg = json_encode($msg);
        return $this->mbCopyMsg($node_uuid, $submodule_type, $opName, $msg, true);
    }
    /**
     * 获取异地数据对象列表
     * @param string $node_uuid
     * @param int $submodule_type
     * @param string $opName
     * @param {json} $msg
     * @return {}
     */
    public function getRemoteItem($node_uuid, $submodule_type, $opName, $msg)
    {
        $msg = json_encode($msg);
        return $this->mbCopyMsg($node_uuid, $submodule_type, $opName, $msg, true);
    }
    /**
     * 设置WORM保护期限
     * @param string $node_uuid
     * @param array $msg
     * @param string $opName
     * @return {}
     */
    public function configWormTime($node_uuid, $msg, $opName)
    {
        $msg = json_encode($msg);
        $result = $this->mbPointMsg($node_uuid, $opName, $msg, 0 , true);
        return $result;
    }
    /**
     * @description: 获取异地副本数据树
     * @param {*} $node_uuid 节点uuid
     * @param {*} $submodule_type 模块类型
     * @param {*} $opName 操作码
     * @param {*} $msg 消息
     * @return {*}
     */  
    public function getRemoteTree($node_uuid, $submodule_type, $opName, $msg)
    {
      $msg = json_encode($msg);
      $result = $this->mbCopyMsg($node_uuid, $submodule_type, $opName, $msg, true);
      return $result;
    }
    /**
     * 获取sqlite上的感染文件列表
     * @param mixed $node_uuid
     * @param mixed $opName
     * @param mixed $msg
     * @return array|string
     */
    public function getVirusInfo($msg)
    {
      return $this->mbToolMsg($this->getLocalNodeUuid(), 'TOOL_VIRUS_OP_GET_VIRUS_SCAN_RESULT', json_encode($msg), true);
    }

    /**
     * 共享存储离线---根据存储获取可访问节点
     * @param mixed $params
     * @return array|string
     */
    public function getClusterAccessableNode($params)
    {
        if(empty($params['storage_uuid'])){
            return '';
        }
        $msg = [
            'shared_storage_uuid' => $params['storage_uuid'],
        ];
        // dump($msg);
        $data = $this->mbClusterMsg($this->getLocalNodeUuid(), 'BD_CLUSTER_FAULT_OP_TYPE_SHARED_STORAGE_NODE_FAULT', json_encode($msg), true);
        // dump($data);
        if($data['result']){
            return ["node_uuid" => $data['msg']];
        }
        return ["node_uuid" => ''];
    }
}
