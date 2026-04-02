<?php

namespace app\v1\copy\v0\service;

use app\v1\common\service\Base;
use app\v1\resources\v0\logic\Node;

/**
 * note           副本 服务通信 service
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/10 10:52
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Service extends Base
{
  /**
   * 统一发送消息到后台  -- 任务相关
   * @param string $taskUuid  任务uuid
   * @param int    $subModule 子模块编号
   * @param string $opName    操作码
   * @param array  $msg       消息
   * @param bool   $sync      是否同步
   * @param bool   $command   是否命令
   * @return void
   */
  public function opUnifyMsg(
    string $taskUuid,
    int $subModule,
    string $opName,
    array $msg,
    $sync = false,
    $command = false
  ) {

    $nodeuuid = (new Node())->getNodeUUIDWithTaskUUID($taskUuid);   //任务UUID对应的存储节点uuid

    return $this->mbCopyMsg($nodeuuid, $subModule, $opName, json_encode($msg), $sync, $command);
  }

  /**
   * @description: 创建副本任务
   * @param {*} $node_uuid 节点uuid
   * @param {*} $submodule_type 模块类型
   * @param {*} $opName 操作码
   * @param {*} $msg 创建消息
   * @return {*}
   */  
  public function createCopyJob($node_uuid, $submodule_type, $opName, $msg)
  {
    $result = $this->mbCopyMsg($node_uuid, $submodule_type, $opName, $msg);
    return $result;
  }
  /**
   * @description: 修改副本任务
   * @param {*} $node_uuid 节点uuid
   * @param {*} $submodule_type 模块类型
   * @param {*} $opName 操作码
   * @param {*} $msg 修改消息
   * @return {*}
   */  
  public function editCopyJob($node_uuid, $submodule_type, $opName, $msg)
  {
    $result = $this->mbCopyMsg($node_uuid, $submodule_type, $opName, $msg);
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
   * @description: 公共添加星标
   * @param {*} $params
   * @return {*}
   */  
  public function addStar($params)
  {
    $time_point_uuid = $params['time_point_uuid'];
    $this->paramsCheck($time_point_uuid);
    $msg = array($time_point_uuid);
    $opName = 'BD_BACKUP_POINT_OP_MAKR';
    $msg = json_encode(array('timepoint_uuids' => $msg));
    //给后台发消息
    //---测试结果----
    $mbResult = $this->mbPFMsg($opName, $msg);
    return $mbResult;
  }

  /**
   * @description: 公共删除星标
   * @return {*}
   */
  public function deleteStar($params)
  {

    $time_point_uuid = $params['time_point_uuid'];
    $this->paramsCheck($time_point_uuid);
    $msg = array($time_point_uuid);
    $opName = 'BD_BACKUP_POINT_OP_UNMARK';
    $msg = json_encode(array('timepoint_uuids' => $msg));
    $mbResult = $this->mbPFMsg($opName, $msg);
    return $mbResult;
  }
  /**
   * @description: 获取异地存储节点网络
   * @param {*} $storage_uuid 存储uuid
   * @param {*} $node_uuid 节点uuid
   * @return {*}
   */
  public function getRemoteNet($storage_uuid, $node_uuid)
  {
    $opName = 'COPY_SERVER_BASIC_OP_CODE_GET_NETWORK_INFO';
    $msg = array(
      'storage_uuid' => $storage_uuid,
    );
	// dump($node_uuid,'',$opName, json_encode($msg));
	$mbResult = $this->mbCopyMsg($node_uuid, 1,$opName, json_encode($msg), true);
	return $mbResult;
  }
}
