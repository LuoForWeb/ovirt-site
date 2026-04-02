<?php

namespace app\v1\resources\v0\controller;

use app\v1\common\controller\AuthBase;
use app\v1\opcode\NodeOpcode;

/**
 * note          节点管理处理类
 * @author       xiezhuowei@vinchin.com
 * @date         2016-05-23 下午15:06:44
 * @version      1.0.0
 * @copyright    Copyright 2016 vinchin.com
 */
class Node extends AuthBase
{
    protected $opcodeHandler;

    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
        //初始化消息等级
        $this->opcodeHandler = new NodeOpcode();
    }

    /**
     * 获取节点列表/获取节点列表
     * @return void
     */
    public function getNodes()
    {
        if (isset($this->param['nodes_uuid'])) {
            // 只验证nodes_uuid
            $this->checkParams('getNode', '', ['nodes_uuid' => $this->param['nodes_uuid']]);
            // 获取单个节点信息
            $data = $this->logic()->getNode($this->param['nodes_uuid']);
        } else {
            // 获取节点列表
            $this->checkParams('getNodes');
            $data = $this->logic()->getNodes($this->param);
        }
        if ($data === false) {
            $this->error();
        }
        $this->success('', $data, 200);
    }

    /**
     * 添加节点
     * @return void
     */
    public function addNode()
    {
        $this->checkParams('add_node');
        $return = $this->logic()->addNode($this->param);
        $this->outputHandle($return);
    }

    /**
     * 修改节点
     * @return void
     */
    public function editNode()
    {
        $this->checkParams('edit_node');
        $return = $this->logic()->editNode($this->param['nodes_uuid'], $this->param);
        $this->outputHandle($return);
    }

    /**
     * 删除节点
     * @return void
     */
    public function deleteNode()
    {
        if (isset($this->param['nodes_uuid'])) {
            $this->checkParams('delete_node');
            $return = $this->logic()->deleteNode($this->param['nodes_uuid']);
        } else {
            $this->checkParams('delete_node_list');
            $return = $this->logic()->deleteNodeList($this->param['node_uuid_list']);
        }
        $this->outputHandle($return);
    }

    /**
     * 获取节点的网络信息
     * @return array
     */
    public function getNodesNetworkCard(): array
    {
        $this->checkParams('getNodesNetworkCard');
        $data = $this->logic()->getNodesNetworkCard($this->param);
        if ($data === false) {
            return $this->error();
        }
        return $this->success('', $data);
    }

    /**
     * 得到某个节点下的可用存储(新建备份任务,修改备份任务使用)
     * @return array
     */
    public function getBackupStorageList()
    {
        $data = $this->logic()->getBackupStorageList($this->param);
        if ($data === false) {
            return $this->error();
        }
        return $this->success('', $data);
    }

    /**
     * 获取节点的网络信息
     * @return array
     */
    public function getAddStorageNodeSelect(): array
    {

        $data = $this->logic()->getAddStorageNodeSelect($this->param);

        return $this->success('', $data);
    }

    /**
     * 得到某个节点下的可用存储(新建备份任务,修改备份任务使用)
     * @return array
     */
    public function getTimepointAllNode()
    {
        $this->checkParams('nodes_get_timepoint');
        $data = $this->logic()->getTimepointAllNode($this->param);
        return $this->success('', $data);
    }

    /**
     * 获取节点缓存
     * @return void
     */
    public function getNodeCache()
    {
        $this->checkParams('get_node_cache');
        $return = $this->logic()->getNodeCache($this->param['nodes_uuid']);
        $this->outputHandle($return);
    }

    /**
     * 设置节点缓存
     * @return void
     */
    public function setNodeCache()
    {
        $this->checkParams('set_node_cache');
        $return = $this->logic()->setNodeCache($this->param['nodes_uuid'], $this->param);
        $this->outputHandle($return);
    }

    //////////////// 节点网络 ///////////////////
    /**
     * 设置节点的资源限制
     * @return void
     */
    public function setNodeResourcesLimit()
    {
        $this->checkParams('set_node_resources_limit');
        $return = $this->logic()->setNodeResourcesLimit($this->param);
        $this->outputHandle($return);
    }

    /**
     * 获取节点的资源限制
     * @return void
     */
    public function getNodeResourcesLimit()
    {
        $this->checkParams('get_node_resources_limit');
        $return = $this->logic()->getNodeResourcesLimit($this->param['node_uuid']);
        $this->outputHandle($return);
    }

    /**
     * 批量获取节点的资源限制
     * @return void
     */
    public function getNodeResourcesLimitByNodeUuidList()
    {
        $this->checkParams('get_node_resources_limit_batch');
        $return = $this->logic()->getNodeResourcesLimitByNodeUuidList($this->param['node_uuid_list']);
        $this->outputHandle($return);
    }

    /**
     * 获取节点的网络列表/详情
     * @return void
     */
    public function getNodeNetwork()
    {
        if (isset($this->param['network_uuid'])) {
            $this->checkParams('get_node_network_detail');
            $return = $this->logic()->getNodeNetworkDetail(
                $this->param['nodes_uuid'],
                $this->param['network_uuid']
            );
        } else {
            $this->checkParams('get_node_network_list');
            $return = $this->logic()->getNodeNetworkList($this->param['nodes_uuid'], $this->param);
        }
        $this->outputHandle($return);
    }

    /**
     * 添加节点网络
     * @return void
     */
    public function addNodeNetwork()
    {
        $this->checkParams('add_node_network');
        $return = $this->logic()->addNodeNetwork($this->param);
        $this->outputHandle($return);
    }

    /**
     * 编辑节点网络
     * @return void
     */
    public function editNodeNetwork()
    {
        $this->checkParams('edit_node_network');
        $return = $this->logic()->editNodeNetwork($this->param);
        $this->outputHandle($return);
    }

    /**
     * 删除节点网络
     * @return void
     */
    public function deleteNodeNetwork()
    {
        if (isset($this->param['network_uuid'])) {
            $this->checkParams('delete_node_network');
            $return = $this->logic()->deleteNodeNetwork($this->param['nodes_uuid'], $this->param['network_uuid']);
        } else {
            $this->checkParams('batch_delete_node_network');
            $return = $this->logic()->batchDeleteNodeNetwork(
                $this->param['nodes_uuid'],
                $this->param['network_uuid_list']
            );
            $this->outputHandle($return);
        }
        $this->outputHandle($return);
    }

    /**
     * 排序节点网络
     * @return void
     */
    public function sortNodeNetwork()
    {
        $this->checkParams('sort_node_network');
        $return = $this->logic()->sortNodeNetwork($this->param['nodes_uuid'], $this->param['network_uuid_list']);
        $this->outputHandle($return);
    }

    /**
     * 获取节点分配列表
     * @return string
     */
    public function getNodeAllocationList()
    {
        $this->checkParams('allocationList');
        $data = $this->logic()->getNodeAllocationList($this->param);
        $this->success('', $data);
    }

    /**
     * 分配备份节点到虚拟化平台
     * @return string
     */
    public function allocationNodePlatform()
    {
        $this->checkParams('allocation');
        return $this->logic()->allocationNodePlatform($this->param);
    }

    /**
     * 获取虚拟机传输代理列表
     * @return void
     */
    public function getApplianceSelect()
    {
        $data = $this->logic()->getApplianceSelect($this->param);
        $this->success('', $data);
    }

    /**
     * 扫描本地节点的IP列表
     * @return void
     */
    public function scanLocalNodeIPList()
    {
        $return = $this->logic()->scanLocalNodeIPList($this->param);
        $this->outputHandle($return);
    }

    /**
     * 获取备份系统单个节点的所有IP
     * @return void
     */
    public function getBackupServerIp()
    {
        $data = $this->logic()->getBackupServerIp($this->param);
        $this->success('', $data);
    }

    /**
     * 获取资源池名称
     * @return string
     */
    public function getResourcePoolName()
    {
        $this->checkParams('get_resource_pool_name');
        $return = $this->logic()->getResourcePoolName($this->param);
        $this->outputHandle($return);
    }

    /**
     * 根据备份点获取节点UUID
     * @return void
     */
    public function getNodeUUIDWithTimepointUUID(): void
    {
        $nodeUuid = $this->logic()->getNodeUUIDWithTimepointUUID($this->param['timepoints_uuid']);
        $this->outputHandle([
            'success' => true,
            'code' => 200,
            'message' => '',
            'data' => [
                'node_uuid' => $nodeUuid,
            ]
        ]);
    }

    /**
     * 获取主节点网络信息
     */
    public function getNodesMasterNetwork()
    {
        $return = $this->logic()->getNodesMasterNetwork($this->param);
        $this->outputHandle($return);
    }

    /**
     * 根据存储UUI获取节点集合
     * @return array
     */
    public function getNodesByStorageUUID()
    {
        $return = $this->logic()->getNodesByStorageUUID($this->param);
        $this->outputHandle([
            'success' => true,
            'code' => 200,
            'message' => '',
            'data' => $return
        ]);
    }
}
