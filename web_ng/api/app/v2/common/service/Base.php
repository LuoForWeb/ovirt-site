<?php

namespace app\v2\common\service;

use app\v2\resources\v0\logic\Node;
use xphp\db\Op;

/**
 * note          基础的服务层 service
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/2 16:26
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Base extends Op
{
    /**
    * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
        // 如果安装了memcached的服务，我们就执行释放操作
        if (!empty(setMemcached())) {
            // 现在我们想要关闭session的写操作，释放锁
            session_write_close();
        }
    }

    /**
     * 获取本地节点的uuid, 用于子类使用
     * @return string
     */
    protected function getLocalNodeUuid(): string
    {
        return (new Node())->getLocalNodeUUID();
    }

    /**
     * 获取主节点uuid, 用于子类使用
     * @return string
     */
    protected function getMasterNodeUuid(): string
    {
        return (new Node())->getMasterNodeUuid();
    }

    /**
     * 读取文件内容服务
     * @param string $filePath 文件路径
     * @param string $nodeUuid 节点uuid，从哪个节点读取文件
     * @return array
     */
    public function readFileContentService(string $filePath, string $nodeUuid): array
    {
        $opName = 'BD_SYSTEM_OP_READ_FILE';
        $msg = [
            'file_path' => $filePath,
        ];
        return $this->mbNodeMsg($opName, $nodeUuid, json_encode($msg), true);
    }

    /**
     * 删除资源关系服务
     * @param array $resourceUuidList 资源uuid列表
     * @param array $resourceTypeList 资源类型列表
     * @return bool
     */
    public function deleteResourceAssociationService(array $resourceUuidList = [], array $resourceTypeList = []): bool
    {
        /**
         * 1. 删除直接以资源的方式分配给用户的资源
         * 2. 删除直接以资源的方式分配给用户组的资源
         * 3. 删除以资源组里面的资源
         */
        // 1. 删除直接以资源的方式分配给用户的资源
        $resourceUuids = "'" . implode("','", $resourceUuidList) . "'";
        $resourceTypes = "'". implode("','", $resourceTypeList) . "'";
        $sql = "DELETE FROM mt_user_resource WHERE resource_uuid IN ($resourceUuids) AND resource_type IN ({$resourceTypes})";
        $this->dbExec($sql);

        // 2. 删除直接以资源的方式分配给用户组的资源
        $sql = "DELETE FROM mt_user_group_resource WHERE resource_uuid IN ($resourceUuids) AND resource_type IN ({$resourceTypes})";
        $this->dbExec($sql);

        // 3. 删除以资源组里面的资源
        $sql = "DELETE FROM mt_resource_resource_group WHERE resource_uuid IN ($resourceUuids) AND resource_type IN ({$resourceTypes})";
        $this->dbExec($sql);
        return true;
    }
}
