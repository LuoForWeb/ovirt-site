<?php

namespace app\v1\resources\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          资源验证器
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/30 18:07
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Storage extends Base
{
    /**
     * 构造方法
     */
    public function __construct()
    {

        parent::__construct();

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'storages_uuid' => ['require', 'max' => 36, 'regex' => '/^[\w|\d]\w+/'],
            'offset'        => ['require', 'number', 'between' => '0,99999'],
            'limit'         => ['require', 'number', 'between' => '1,500'],
            'uuids'         => ['require', 'array'],
            'storage_type'  => ['require', 'number'],
            'use_mode'      => ['require', 'number'],
            'format'        => ['require'],
            'storage_name'  => ['require'],
            'node_uuid'     => ['require', 'regex' => '/^[\w|\d]\w+/'],
            'user_uuid'     => ['require', 'regex' => '/^[\w|\d]\w+/'],
            'vendor'        => ['require'],
            'storage_uuids' => ['require'],
            'host'          => ['require'],
            'lun'           => ['require'],
            'mount_params'  => ['require'],
            'username'     => ['require'],
            'password'      => ['require'],
            'region'        => ['require'],
            'folder'        => ['require'],
            'limit_size'    => ['require'],
            'server_node'   => ['require'],
            'dirname'      => ['require'],
            'remote_port'   => ['require', 'number'],
            'bucket'        => ['require'],
            'remoteip'      => ['require'],
            'remoteport'    => ['require', 'number'],
            'value'    => ['require', 'number'],
            'access_key_id'    => ['require'],
            'secret_access_key'    => ['require'],
            'iscsi_target'    => ['require'],
            'target_iqn'    => ['require'],
            'passwd'    => ['require'],
            'rname'    => ['require'],
            'server_list'    => ['require', 'array'],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            // 获取存储设备列表
            'storages_list' => ['offset', 'limit'],
            'auto_time' => ['value'],
            // 获取存储设备详情
            'storages_detail' => ['storages_uuid'],
            // 添加存储设备 storage_type 为 1（本地磁盘）、2（逻辑卷LVM）、3（本地分区）、4（Fibre Channel）
            'add_storages' => ['node_uuid', 'storage_type', 'storage_name', 'rname', 'use_mode', 'format'],
            // 添加存储设备 storage_type 为 5(iSCSI)
            'add_storages5' => [
                'node_uuid', 'storage_type', 'rname', 'use_mode', 'format', 'server_list', 'lun'
            ],
            // 添加存储设备 storage_type 为 6 (NFS)
            'add_storages6' => [
                'node_uuid', 'storage_type', 'use_mode', 'format', 'host'
            ],
            // 添加存储设备 storage_type 为 7(CIFS)
            'add_storages7' => [
                'node_uuid', 'storage_type', 'host', 'use_mode', 'format'
            ],
            // 添加存储设备 storage_type 为 8(异地备份系统)
            'add_storages8' => [
                'remoteip', 'storage_type', 'remote_port', 'username', 'password', 'use_mode'
            ],
            // 添加存储设备 storage_type 为 9(云存储)
            'add_storages9' => [
                'vendor', 'storage_type', 'use_mode', 'limit_size'
            ],
            // 添加存储设备 storage_type 为 11(本地目录)
            'add_storages11' => ['node_uuid', 'storage_type', 'dirname', 'use_mode', 'format'],
            // 添加存储设备 storage_type 为 16(dddb)
            'add_storages16' => ['node_uuid', 'storage_type', 'data_domain_system', 'storage_unit'],
            // 修改存储设备
            'edit_storages' => ['storages_uuid', 'storage_name'],
            // 删除单个存储设备
            'del' => ['storages_uuid'],
            // 批量删除存储设备
            'batchdel' => ['uuids'],
            // 导入数据管理列表
            'data_list' => ['offset', 'limit'],
            // 获取备份数据信息详情
            'data_detail' => ['storages_uuid'],
            // 分配导入数据到其他用户
            'distribute' => ['uuids', 'user_uuid'],
            // 获取添加存储的表格数据
            'storages_table' => ['node_uuid', 'storage_type'],
            // 获取存储WWN信息
            'storages_wwn' => ['node_uuid'],
            // 获取iscsi名称
            'storages_iscsi' => ['node_uuid'],
            // 获取IQN对应的LUN信息
            'storages_lun' => ['node_uuid', 'server_list'],
            // 获取云存储bucket folder列表
            'storages_bucket' => ['username', 'password', 'bucket'],
            // 获取云存储region
            'storages_region' => ['vendor'],
            // 获取异地备份系统存储列表
            'storages_remote' => ['username', 'password', 'remoteip', 'remoteport'],
            // 获取CBR区域
            'storages_cbr' => ['access_key_id', 'secret_access_key'],
            'storages_name' => ['storage_type'],
            // chap认证信息
            'storages_chap_auth' => ['node_uuid', 'iscsi_target', 'target_iqn', 'username', 'passwd'],
            // 添加lanfree存储 fc
            'storages_lanfree4' => ['node_uuid', 'rname', 'pathlist', 'storagename'],
            // 添加lanfree存储 ISCSI
            'storages_lanfree5' => ['node_uuid', 'rname', 'iscsi_target', 'target_iqn'],
            // 添加lanfree存储 NFS
            'storages_lanfree6' => ['node_uuid', 'host', 'rname'],
            // 添加lanfree存储 CIFS
            'storages_lanfree7' => ['node_uuid', 'rname', 'host'],
        ];
    }
}
