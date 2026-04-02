<?php

namespace app\v1\resources\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          客户端管理处理类
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/29 15:19
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Client extends AuthBase
{
    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
    * 获取客户端列表 / 详情
     * @return void
     */
    public function getClientInfo()
    {
        // 根据参数判断是获取列表还是详情
        $scene = !empty($this->param['agents_uuid']) ? 'client_detail' : 'client_list';

        // 参数验证
        $this->checkParams($scene);

        if (empty($this->param['agents_uuid'])) {
            // 获取客户端列表
            $records = $this->logic()->getClientList($this->param);
        } else {
            // 获取客户端详情
            $records = $this->logic()->getClientDetail($this->param);
        }

        $this->outputHandle($records);
    }

    /**
     * 删除客户端 / 批量删除客户端
     * @return void
     */
    public function deleteClient()
    {
        if (isset($this->param['agents_uuid'])) {
            // 删除客户端
            $this->checkParams('delete_client');
            $result = $this->logic()->deleteClient(
                $this->param['agents_uuid'],
                intval($this->param['uninstall_plugin_flag'])
            );
        } else {
            // 批量删除客户端
            $this->checkParams('delete_clients');
            $result = $this->logic()->deleteClientList(
                $this->param['agent_uuids'],
                intval($this->param['uninstall_plugin_flag'])
            );
        }
        $this->outputHandle($result);
    }

    /**
     * 修改客户端
     * @return void
     */
    public function updateClient()
    {
        $this->checkParams('update_client');
        $result = $this->logic()->updateClient($this->param);
        $this->outputHandle($result);
    }

    /**
     * 添加客户端
     * @return void
     */
    public function addClient()
    {
        $this->checkParams('add_client');
        $result = $this->logic()->addClient(
            $this->param['add_mode'],
            $this->param['manual'] ?? null,
            $this->param['deployment'] ?? null
        );
        $this->outputHandle($result);
    }

    /**
     * 客户端授权/取消授权
     * @return void
     */
    public function authClient()
    {
        $this->checkParams('auth_client');
        $result = $this->logic()->authClient($this->param['auth_flag'] ?: false, $this->param['agent_list']);
        $this->outputHandle($result);
    }

    /**
     * 客户端升级
     * @return void
     */
    public function upgradeClient()
    {
        $this->checkParams('upgrade_client');
        $result = $this->logic()->upgradeClient($this->param['upgrade_data']);
        $this->outputHandle($result);
    }

    /**
     * 获取客户端升级列表
     * @return void
     */
    public function getUpgradeClientList()
    {
        $this->checkParams('get_upgrade_list');
        $result = $this->logic()->getUpgradeClientList($this->param['agent_uuid_list']);
        $this->outputHandle($result);
    }

    /**
     * 获取客户端日志列表
     * @return void
     */
    public function getClientLogList()
    {
        $this->checkParams('get_client_log_list');
        $result = $this->logic()->getClientLogList($this->param['agents_uuid']);
        $this->outputHandle($result);
    }

    /**
     * 下载客户端日志
     * @return void
     */
    public function downloadClientLog()
    {
        $this->checkParams('download_client_log');
        $result = $this->logic()->downloadClientLog($this->param['agents_uuid'], $this->param['log_path_list']);
        $this->outputHandle($result);
    }

    /**
     * 获取客户端授权信息
     * @return void
     */
    public function getClientAuthInfo()
    {
        $this->success('', $this->logic()->getClientAuthInfo());
    }

    /**
    * 获取客户端应用列表 / 详情
     * @return void
     */
    public function getClientAppInfo()
    {
        if (!isset($this->param['applications_uuid'])) {
            // 获取客户端应用列表
            $this->checkParams('application_list');
            $result = $this->logic()->getClientAppList($this->param);
        } else {
            // 获取客户端应用详情
            $this->checkParams('application_detail');
            $result = $this->logic()->getClientAppDetail(
                $this->param['agents_uuid'],
                $this->param['applications_uuid']
            );
        }
        $this->outputHandle($result);
    }

    /**
     * (批量)删除客户端应用
     * @return void
     */
    public function deleteClientApp()
    {
        if (isset($this->param['applications_uuid'])) {
            // 单个删除
            $this->checkParams('delete_client_app');
            $result = $this->logic()->deleteClientApp(
                $this->param['agents_uuid'],
                $this->param['applications_uuid']
            );
        } else {
            // 批量删除
            $this->checkParams('delete_client_apps');
            $result = $this->logic()->deleteClientAppList(
                $this->param['agents_uuid'],
                $this->param['app_uuid_list']
            );
        }
        $this->outputHandle($result);
    }

    /**
     * 添加应用/实例认证
     * @return void
     */
    public function addClientApp()
    {
        $this->checkParams('addClientApp');
        $result = $this->logic()->addClientApp($this->param);
        $this->outputHandle($result);
    }

    /**
     * 获取数据库集群别名
     * @return void
     */
    public function getClientAppClusterName()
    {
        $this->checkParams('get_client_app_cluster_name');
        $result = $this->logic()->getClientAppClusterName($this->param['db_type']);
        $this->outputHandle($result);
    }

    /**
    * 获取客户端集群信息
     * @return void
     */
    public function getAppCluster()
    {
        // 参数验证
        $this->checkParams('app_cluster');

        $records = $this->logic()->getAppCluster($this->param);

        $this->success('', $records);
    }

    /**
    * 获取客户端的磁盘信息
     * @return void
     */
    public function getClientDiskInfo(): array
    {
        $this->checkParams('disk_info');
        $data = $this->logic()->getClientDiskInfo($this->param['agents_uuid']);
        $this->outputHandle($data);
    }

    /**
    * 获取客户端网卡信息
     * @return void
     */
    public function getClientNetworkCard()
    {
        $this->checkParams('network_card');
        $records = $this->logic()->getClientNetworkCard($this->param);
        $this->outputHandle($records);
    }

    /**
     * 分配客户端的所有者用户
     * @return void
     */
    public function allocateClientUser()
    {
        $this->checkParams('allocate_client_user');
        $return = $this->logic()->allocateClientUser(
            $this->param['agent_uuids'],
            $this->param['user_uuid']
        );
        $this->outputHandle($return);
    }

    /**
     * 调整客户端所处分组
     * @return void
     */
    public function adjustClientGroup()
    {
        $this->checkParams('adjust_client_group');
        $return = $this->logic()->adjustClientGroup(
            $this->param['adjust_mode'],
            $this->param['agent_uuids'],
            $this->param['agent_group_uuid']
        );
        $this->outputHandle($return);
    }

    /**
     * 获取客户端分组
     * @return void
     */
    public function getClientGroupInfo()
    {
        $return = $this->logic()->getClientGroupInfo();
        $this->outputHandle($return);
    }

    /**
     * 添加客户端分组
     * @return void
     */
    public function addClientGroup()
    {
        $this->checkParams('add_client_group');
        $return = $this->logic()->addClientGroup($this->param['group_name'], $this->param['remark'] ?? '');
        $this->outputHandle($return);
    }

    /**
     * 修改客户端分组
     * @return void
     */
    public function updateClientGroup()
    {
        $this->checkParams('update_client_group');
        $return = $this->logic()->updateClientGroup(
            $this->param['groups_uuid'],
            $this->param['group_name'],
            $this->param['remark'] ?? ''
        );
        $this->outputHandle($return);
    }

    /**
     * 删除客户端分组
     * @return void
     */
    public function deleteClientGroup()
    {
        if (isset($this->param['groups_uuid'])) {
            // 删除单个
            $this->checkParams('delete_client_group');
            $return = $this->logic()->deleteClientGroup($this->param['groups_uuid']);
        } else {
            // 批量删除
            $this->checkParams('delete_client_groups');
            $return = $this->logic()->deleteClientGroupList($this->param['agent_group_uuids']);
        }
        $this->outputHandle($return);
    }

    /**
     * 解析上传的客户端部署文件
     * @return void
     */
    public function resolveClientDeployFile()
    {
        $result = $this->logic()->resolveClientDeployFile($_FILES['files'] ?? null);
        $this->outputHandle($result);
    }

    /**
     * 加载客户端实例
     * @return void
     */
    public function loadClientInstance()
    {
        $this->checkParams('load_client_instance');
        $result = $this->logic()->loadClientInstance(
            $this->param['agents_uuid'],
            $this->param['db_type'],
            $this->param['agent_uuid_list'] ?? []
        );
        $this->outputHandle($result);
    }

    /**
     * 刷新客户端
     * @return void
     */
    public function refreshClient()
    {
        $this->checkParams('refresh_client');
        $result = $this->logic()->refreshClient($this->param['agents_uuid']);
        $this->outputHandle($result);
    }

    /**
     * 获取客户端下载列表
     * @return void
     */
    public function getClientDownloadPackage()
    {
        $result = $this->logic()->getDownloadPackage();
        $this->outputHandle($result);
    }

    /**
     * 获取代理域名解析配置
     * @return void
     */
    public function queryAgentDomainConfig()
    {
        $result = $this->logic()->queryAgentDomainConfig($this->param['agent_uuid']);
        $this->outputHandle($result);
    }

    /**
     * 更新传输代理域名解析配置
     * @return void
     */
    public function updateAgentDomainConfig()
    {
        $result = $this->logic()->updateAgentDomainConfig($this->param);
        $this->outputHandle($result);
    }

    /**
     * 获取虚拟化插件版本
     * @return void
     */
    public function getAgentVersions()
    {
        $data = $this->logic()->getAgentVersions($this->param);
        $this->success('', $data);
    }

    /**
     * 扫描客户端磁盘文件
     * @return void
     */
    public function scanAgentDiskFiles()
    {
        $this->checkParams('scan_agent_disk_files');
        $result = $this->logic()->scanAgentDiskFiles($this->param['agents_uuid'], $this->param);
        $this->outputHandle($result);
    }

    /**
     * 在指定目录下搜索客户端的磁盘文件
     * @return void
     */
    public function searchAgentDiskFiles()
    {
        $this->checkParams('scan_agent_disk_files');
        $result = $this->logic()->searchAgentDiskFiles($this->param['agents_uuid'], $this->param);
        $this->outputHandle($result);
    }

    /**
     * 获取下载链接
     * @return void
     */
    public function getDownloadLink()
    {
        $result = $this->logic()->getDownloadLink($this->param);
        $this->success('', $result);
    }

    /**
     * 得到可下载的代理地址
     */
    public function getDoloadAgentName()
    {
        $result = $this->logic()->getDoloadAgentName($this->param);
        $this->success('', $result);
    }

    /**
     * 获取应用类型
     * @return void
     */
    public function getAgentAppType()
    {
        $result = $this->logic()->getAgentAppType();
        $this->outputHandle($result);
    }
}
