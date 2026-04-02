<?php

namespace app\v1\exchange\v0\service;

use app\v1\common\service\Base;
use app\v1\resources\v0\logic\Node;

/**
 * note           office365（exchange） 服务通信 service
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/10 10:52
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Service extends Base
{
    /**
     * 创建备份任务
     * @param string $nodeuuid 节点uuid
     * @param $params   传给后台的参数
     * @return unknown
     */
    public function createBackupJob($nodeuuid, $params, $opName)
    {
        $msg = json_encode($params);
        //发送消息给后台
        $result = $this->mbM365Msg($nodeuuid, 1, $opName, $msg);
        return $result;
    }

    /**
     * 创建恢复任务
     * @param string $nodeuuid 节点uuid
     * @param $msg      传给后台的参数
     * @param $opName   操作码
     * @return unknown
     */
    public function createRecoverJob($nodeuuid, $msg, $opName)
    {
        //发送消息给后台
        $result = $this->mbM365Msg($nodeuuid, 1, $opName, json_encode($msg));
        return $result;
    }

    /**
     * 修改备份任务
     * @param string $nodeuuid 节点uuid
     * @param $params   传给后台的参数
     * @return unknown
     */
    public function editBackupJob($nodeuuid, $params)
    {
        $opName = 'BD_TASK_OP_BACKUP_MODIFY';
        $msg = json_encode($params);
        //发送消息给后台
        $result = $this->mbM365Msg($nodeuuid, 1, $opName, $msg);
        return $result;
    }

    /**
     * 添加组织
     * @param string $nodeuuid 节点uuid
     * @param object $params   传给后台的参数
     * @param number $type     添加方式
     * @return unknown
     */
    public function addOrganization($nodeuuid, $params, $type)
    {
        //添加方式：1：exchangeonline，2：exchangeserver
        if ($type == 1) {
            $opName = 'M365_COMMON_OP_CODE_ADD_ORGANIZATION';
        } elseif ($type == 2) {
            $opName = 'M365_ON_PREMISES_OP_CODE_ADD_ORGANIZATION';
        }
        $msg = json_encode($params);
        //发送消息给后台
        $result = $this->mbM365Msg($nodeuuid, 1, $opName, $msg);
        return $result;
    }

    /**
     * 修改组织
     * @param string $nodeuuid 节点uuid
     * @param $params   传给后台的参数
     * @param number $type     添加方式
     * @return unknown
     */
    public function editOrganization($nodeuuid, $params, $type)
    {
        //添加方式：1：exchangeonline，2：exchangeserver
        if ($type == 1) {
            $opName = 'M365_COMMON_OP_CODE_EDIT_ORGANIZATION';
        } elseif ($type == 2) {
            $opName = 'M365_ON_PREMISES_COMMON_OP_CODE_EDIT_ORGANIZATION';
        }
        $msg = json_encode($params);
        //发送消息给后台
        $result = $this->mbM365Msg($nodeuuid, 1, $opName, $msg);
        return $result;
    }

    /**
     * 删除组织
     * @param string $nodeuuid 节点uuid
     * @param $params   传给后台的参数
     * @return unknown
     */
    public function deleteOrganization($nodeuuid, $params)
    {
        $opName = 'M365_COMMON_OP_CODE_DELETE_ORGANIZATION';
        $msg = json_encode($params['organization_uuid']);
        //发送消息给后台
        $result = $this->mbM365Msg($nodeuuid, 1, $opName, $msg);
        return $result;
    }

    /**
     * 获取用于身份验证的代码
     * 判断登录是否成功
     * @param string  $nodeuuid 节点uuid
     * @param unknown $params   参数
     * @return string op_id
     */
    public function getVertifyCode($nodeuuid, $params)
    {
        $opName = 'M365_COMMON_OP_CODE_LOGIN_AZURE_AD';
        $msg = json_encode($params);
        //发送消息给后台
        $result = $this->mbM365Msg($nodeuuid, 1, $opName, $msg);
        return $result;
    }

    /**
     * 获取时间点下的用户列表
     * @param $params 参数
     * @return object 用户列表
     */
    public function getRestoreUsers($params)
    {
        //假数据
        $result = array();
        $opName = 'EXCH_BACKUP_POINT_OP_CODE_SCAN_USER';
        $msg = array(
            'timepoint_uuid' => $params['timepoint_uuid'],
            'organization_uuid' => $params['organization_uuid'],
            'next_start' => $params['next_start'],//偏移
            'page_size' => $params['page_size'],//要读取多少个用户
            'current_num' => $params['current_num'],//已加载出来的用户数
        );
        //发送消息给后台
        $result = $this->mbM365Msg($params['node_uuid'], 1, $opName, json_encode($msg), true);
        return $result;
    }

    /**
     * 得到顶级目录
     * @param $params 参数
     * @return object 顶级目录
     */
    public function getRootDir($params)
    {
        //假数据
        $result = array();
        $opName = 'EXCH_BACKUP_POINT_OP_CODE_SCAN_ROOT_FOLDER';
        $msg = array(
            'timepoint_uuid' => $params['timepoint_uuid'],
            'organization_uuid' => $params['organization_uuid'],
            'user_uuid' => $params['user_uuid'],
            'index_container_id' => $params['index_container_id'],
            'table_id' => $params['table_id'],
        );
        //发送消息给后台
        $result = $this->mbM365Msg($params['node_uuid'], 1, $opName, json_encode($msg), true);
        return $result;
    }

    /**
     * 得到子目录
     * @param $params 参数
     * @return object 子目录
     */
    public function getChildDir($params)
    {
        //假数据
        $opName = 'EXCH_BACKUP_POINT_OP_CODE_SCAN_CHILD_FOLDER';
        $msg = array(
            'timepoint_uuid' => $params['timepoint_uuid'],
            'organization_uuid' => $params['organization_uuid'],
            'user_uuid' => $params['user_uuid'],
            'index_container_id' => $params['index_container_id'],
            'table_id' => $params['table_id'],
            'folder_id' => $params['folder_id'],
        );
        //发送消息给后台
        $result = $this->mbM365Msg($params['node_uuid'], 1, $opName, json_encode($msg), true);
        return $result;
    }

    /**
     * 得到目录下的元数据
     * @param $params 参数
     * @return object 元数据
     */
    public function getMetadata($params)
    {
        $msg = array(
            'timepoint_uuid' => $params['timepoint_uuid'],
            'organization_uuid' => $params['organization_uuid'],
            'user_uuid' => $params['user_uuid'],
            'index_container_id' => $params['index_container_id'],
            'table_id' => $params['table_id'],
            'folder_id' => $params['folder_id'],
            'type' => $params['type'],
            'next_start' => intval($params['next_start']),
            'page_size' => intval($params['page_size']),
            'current_num' => intval($params['current_num']),
        );
        $opName = 'EXCH_BACKUP_POINT_OP_CODE_SCAN_ITEM';
        //发送消息给后台
        $result = $this->mbM365Msg($params['node_uuid'], 1, $opName, json_encode($msg), true);
        return $result;
    }

    /**
     * 得到目录下的元数据的详细信息
     * @param $params 参数
     * @return object 元数据的详细信息
     */
    public function getMetadataDetail($params)
    {
        $msg = array(
            'timepoint_uuid' => $params['timepoint_uuid'],
            'organization_uuid' => $params['organization_uuid'],
            'user_uuid' => $params['user_uuid'],
            'index_container_id' => $params['index_container_id'],
            'table_id' => $params['table_id'],
            'folder_id' => $params['folder_id'],
            'type' => $params['type'],
            'item_id' => $params['item_id'],
            'item_uuid' => $params['item_uuid'],
        );
        $opName = 'EXCH_BACKUP_POINT_OP_CODE_GET_ITEM_DETAIL';
        //发送消息给后台
        $result = $this->mbM365Msg($params['node_uuid'], 1, $opName, json_encode($msg), true);
        return $result;
    }

    /**
     * 导出压缩包
     * @param $params 参数
     * @return object 压缩包信息
     */
    public function getRestoreZip($params)
    {
        $opName = 'EXCH_BACKUP_POINT_OP_CODE_EXPORT_ITEM';
        //发送消息给后台
        $result = $this->mbM365Msg($params['node_uuid'], 1, $opName, json_encode($params), true);
        return $result;
    }

    /**
     * 导出压缩包中的数据
     * @param $params 参数
     * @return object 压缩包中的数据
     */
    public function getRestoreZipData($params)
    {
        $opName = 'EXCH_BACKUP_POINT_OP_CODE_READ_EXPORT_ITEM_DATA';
        //发送消息给后台
        $result = $this->mbM365Msg($params['node_uuid'], 1, $opName, json_encode($params), true);
        return $result;
    }

    /**
     * 发送邮件（单个/批量发送）
     * @param $params 参数
     * @return object 发送邮件的结果
     */
    public function sendRestoreEmail($params)
    {
        //假数据
        $opName = 'EXCH_BACKUP_POINT_OP_CODE_SEND_EMAIL';
        //发送消息给后台
        $result = $this->mbM365Msg($params['node_uuid'], 1, $opName, json_encode($params));
        if (!$result['result']) {
            return $this->muOpResult($result['result'], xphp_get_lang('WEB_EXCH_BACKUP_POINT_OP_CODE_SEND_EMAIL'), '', '', $result['errorCode']);
        } else {
            $result['success'] = true;
        }
        return $result;
    }

    /**
     * 统一发送消息到后台  -- 任务相关
     * @param string $taskUuid 任务uuid
     * @param string $opName   操作码
     * @param  array  $msg      消息
     * @param bool   $sync     是否同步
     * @param bool   $command  是否命令
     * @return void
     */
    public function opUnifyMsg(string $taskUuid, string $opName, array $msg, $sync = false, $command = false)
    {
        $nodeuuid = (new Node())->getNodeUUIDWithTaskUUID($taskUuid);   //任务UUID对应的存储节点uuid
        return $this->mbM365Msg($nodeuuid, 0, $opName, json_encode($msg), $sync, $command);
    }

    /**
     * 高级搜索
     * @param $params 参数
     * @return object 搜索结果
     */
    public function getSearchInfo($params)
    {
        //假数据
        $opName = 'EXCH_BACKUP_POINT_OP_CODE_ADVANCED_SEARCH';
        $msg = array(
            'timepoint_uuid' => $params['timepoint_uuid'],
            'organization_uuid' => $params['organization_uuid'],
            'exch_advanced_search_condition' => $params['exch_advanced_search_condition'],
            'last_search_info' => $params['last_search_info'],
            'next_start' => $params['next_start'],
            'page_size' => $params['page_size'],
        );
        //发送消息给后台
        $result = $this->mbM365Msg($params['node_uuid'], 1, $opName, json_encode($msg), true);
        return $result;
    }

    /**
     * 高级搜索
     * @param $params 参数
     * @return object 搜索结果
     */
    public function getNormalSearch($params)
    {
        $opName = 'EXCH_BACKUP_POINT_OP_CODE_SEARCH';
        $msg = array(
            'timepoint_uuid' => $params['timepoint_uuid'],
            'organization_uuid' => $params['organization_uuid'],
            'exch_search_condition' => $params['exch_search_condition'],
            'last_search_info' => $params['last_search_info'],
            'next_start' => $params['next_start'],
            'page_size' => $params['page_size'],
        );
        //发送消息给后台
        $result = $this->mbM365Msg($params['node_uuid'], 1, $opName, json_encode($msg), true);
        return $result;
    }

    /**
     * 删除备份数据
     * @param string $nodeuuid 节点uuid
     * @param $params   传给后台的参数
     * @return unknown
     */
    public function deleteTimePoint($nodeuuid, $params)
    {
        $msg = json_encode($params);
        //发送消息给后台
        $result = $this->mbM365Msg($nodeuuid, 0, 'BD_BACKUP_POINT_OP_BATCH_DELETE', $msg);
        return $result;
    }

    /**
     * 同步
     * @param $nodeuuid 节点uuid
     * @param $params   同步
     * @return unknown 同步结果
     */
    public function syncOrganization($nodeuuid, $params)
    {
        $msg = json_encode($params['info']);
        //发送消息给后台
        $result = $this->mbM365Msg($nodeuuid, 1, 'M365_COMMON_OP_CODE_REFRESH_ORGANIZATION', $msg);
        return $result;
    }

    /**
     * 云存储上的完备点索引数据同步
     * @param $params 完备点
     * @return object同步结果
     */
    public function syncPointData($params)
    {
        //发送消息给后台
        $msg = array(
            'timepoint_uuid' => $params['timepoint_uuid'],
        );
        $result = $this->mbM365Msg($params['node_uuid'], 1, 'M365_COMMON_OP_CODE_SYNC_META_FILE', json_encode($msg));
        return $result;
    }

    /**
     * 统一发送平台消息到后台
     * @param string $opName  操作码
     * @param string $msg     消息json
     * @param string $operate 操作描述
     * @return array
     */
    public function opUnifyPfMsg(string $opName, string $msg, string $operate): array
    {
        $mbResult = $this->mbPFMsg($opName, $msg);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 下载跳过文件
     * @param $nodeUuid nodeuuid
     * @param $msg      组合消息
     * @return object下载结果
     */
    public function downLoadPassData($nodeUuid, $msg)
    {
        $opName = 'M365_COMMON_OP_CODE_READ_DATA_FROM_STORAGE';
        //发送消息给后台
        $result = $this->mbM365Msg($nodeUuid, 1, $opName, json_encode($msg), true);
        //返回结果到UI
        if (is_array($result) && $result['errorCode']) {
            echo $result['errorMsg'];
            exit();
        } else {
            return $result;
        }
    }
}
