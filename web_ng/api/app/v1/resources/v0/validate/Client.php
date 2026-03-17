<?php

namespace app\v1\resources\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          客户端管理处理类 验证器
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/29 15:56
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Client extends Base
{
    /**
     * 构造方法
     */
    public function __construct()
    {

        parent::__construct();

        $allDbType = xphp_get_config('db', 'DB_TYPE');
        unset($allDbType['UNKNOWN']);
        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'agents_uuid'   => ['require', 'max' => 36, 'regex' => '/^[\w|\d]\w+/'],
            'applications_uuid'   => ['require', 'max' => 36, 'regex' => '/^[\w|\d]\w+/'],
            'offset'        => ['number', 'between' => '0,99999'],
            'limit'         => ['number', 'between' => '1,5000'],
            'agent_uuids' => ['array', 'require'],
            'user_uuid'   => ['require', 'max' => 36, 'regex' => '/^[\w|\d]\w+/'],
            'adjust_mode' => ['require', 'in' => '1,2'],
            'agent_group_uuid' => ['require', 'max' => 36, 'regex' => '/^[\w|\d]\w+/'],
            'db_type' => ['require', 'number', 'in:' . implode(',', $allDbType)],
            'add_mode' => ['require', 'checkAddClient'],  // 自定义验证规则checkAddClient
            'auth_flag' => ['require', 'in:0,1'],
            'agent_list' => ['require', 'array', 'checkAuthClient'],  // 自定义验证规则checkAuthClient
            'group_name' => ['require'],
            'groups_uuid' => ['require', 'max' => 36, 'regex' => '/^[\w|\d]\w+/'],
            'agent_group_uuids' => ['require', 'array'],
            'app_uuid_list' => ['require', 'array'],
            'instance_name' => ['require'],
            'app_type' => ['number', 'in:' . implode(',', $allDbType)],
            'ip' => ['checkClientIp'],
            'port' => ['number', 'between' => '0,65535'],
            'net_model' => ['number', 'in:1,2'],
            'uninstall_plugin_flag' => ['number', 'in:1,2'],
            'upgrade_data' => ['require', 'array'],
            'agent_uuid_list' => ['require', 'array'],
            'log_path_list' => ['require', 'array'],
            'name' => ['require'],
            'search_index' => ['require'],
            'limit_count' => ['require'],
            'select_mode' => ['require'],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            // 获取客户端列表
            'client_list' => ['offset', 'limit', 'app_type'],
            // 客户端详情
            'client_detail' => ['agents_uuid'],
            // 删除客户端
            'delete_client' => ['agents_uuid', 'uninstall_plugin_flag'],
            // 批量删除客户端
            'delete_clients' => ['agent_uuids', 'uninstall_plugin_flag'],
            // 修改客户端
            'update_client' => ['agents_uuid', 'ip', 'port', 'net_model'],
            // 添加客户端
            'add_client' => ['add_mode'],

            // 获取单个客户端应用列表
            'application_list' => ['agents_uuid', 'offset', 'limit', 'app_type'],
            // 单个客户端应用详情
            'application_detail' => ['agents_uuid', 'applications_uuid'],
            // 删除单个客户端应用
            'delete_client_app' => ['agents_uuid', 'applications_uuid'],
            // 批量删除客户端应用
            'delete_client_apps' => ['agents_uuid', 'app_uuid_list'],
            // 获取数据库集群别名
            'get_client_app_cluster_name' => ['db_type'],

            // 获取客户端集群信息
            'app_cluster'   => ['agents_uuid', 'offset', 'limit'],

            // 获取客户端的磁盘信息
            'disk_info' => ['agents_uuid'],
            // 获取客户端网卡信息
            'network_card' => ['agents_uuid', 'offset', 'limit'],

            // 分配客户端的所有者用户
            'allocate_client_user' => ['agent_uuids', 'user_uuid'],

            // 调整客户端所处的分组
            'adjust_client_group' => ['adjust_mode', 'agent_uuids', 'agent_group_uuid'],

            // 加载客户端实例
            'load_client_instance' => ['agents_uuid', 'db_type'],

            // 刷新客户端
            'refresh_client' => ['agents_uuid'],

            // 客户端授权/取消授权
            'auth_client' => ['auth_flag', 'agent_list'],

            // 客户端升级
            'upgrade_client' => ['upgrade_data'],
            // 获取客户端升级列表
            'get_upgrade_list' => ['agent_uuid_list'],
            // 获取客户端日志列表
            'get_client_log_list' => ['agents_uuid'],
            // 下载客户端日志
            'download_client_log' => ['agents_uuid', 'log_path_list'],

            // 添加客户端分组
            'add_client_group' => ['group_name'],
            // 修改客户端分组
            'update_client_group' => ['groups_uuid', 'group_name'],
            // 删除客户端分组
            'delete_client_group' => ['groups_uuid'],
            // 批量删除客户端分组
            'delete_client_groups' => ['agent_group_uuids'],
            // 获取虚拟化插件版本
            'get_versions' => ['name'],
            // 扫描客户端磁盘文件
            'scan_agent_disk_files' => [
                'search_index', 'limit_count', 'select_mode',
            ],
            // 在指定目录下搜索客户端的磁盘文件
            'search_agent_disk_files' => [
                'search_index', 'limit_count', 'select_mode',
            ],
        ];
    }

    /**
     * 验证客户端的ip
     * @param ?string $ip
     * @param string $rules 定义的验证规则的参数
     * @param array $data 请求的全部数据
     * @param string $field 验证字段
     * @return bool|string
     */
    protected function checkClientIp(?string $ip, string $rules, array $data, string $field)
    {
        if ($data['appliance_flag']) {
            // 传输代理
            $ret = $this->validateIpDomain($ip, '', [], $field);
        } else {
            $ret = $this->validateIp($ip, '', [], $field);
        }
        if (true !== $ret) {
            return $ret;
        }
        return true;
    }

    /**
     * 验证添加客户端
     * @param ?int   $addMode 添加模式是必须的
     * @param string $rules   定义的验证规则的参数，当前规则没有参数。如in:1,2的参数是1,2
     * @param array  $data    请求的全部数据
     * @param string $field   验证字段
     * @return bool|string
     */
    protected function checkAddClient(?int $addMode, string $rules, array $data, string $field)
    {
        $clientAddMode = xphp_get_config('client', 'AGENT_ADD_MODE', 'resources');
        // 分发验证
        if ($addMode == $clientAddMode['manual']) {
            // 手动添加
            // $data需要manual，且类型是array
            if (!isset($data['manual'])) {
                return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'manual');
            }
            if (!is_array($data['manual'])) {
                return sprintf(xphp_get_lang('WEB_VALIDATE_ARRAY'), $field . '.manual');
            }
            $checkManual = $this->checkAddClientManual($data['manual']);
            if (true !== $checkManual) {
                return sprintf($checkManual, $field . '.manual');
            }
        } elseif ($addMode == $clientAddMode['deployment']) {
            // 远程部署
            // $data需要deployment, 且类型是array
            if (!isset($data['deployment'])) {
                return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'deployment');
            }
            if (!is_array($data['deployment'])) {
                return sprintf(xphp_get_lang('WEB_VALIDATE_ARRAY'), $field . '.deployment');
            }
            $checkDeployment = $this->checkAddClientDeployment($data['deployment']);
            if (true !== $checkDeployment) {
                return sprintf($checkDeployment, $field . '.deployment');
            }
        } else {
            // 不支持的添加方式
            return sprintf(xphp_get_lang('WEB_VALIDATE_IN'), $field, implode(',', $clientAddMode));
        }
        return true;
    }

    /**
     * 验证手动添加客户端参数
     * ip: ip
     * alias: string
     * port: int
     * agent_type: int
     * auto_change_network_flag: boolean
     * @param array $manual 手动添加客户端的参数
     * @return bool|string
     */
    private function checkAddClientManual(array $manual)
    {
        // 验证ip
        if (!isset($manual['ip'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'ip');
        }
        if (xphp_get_config('client', 'AGENT_TYPE', 'resources')['APPLIANCE'] == $manual['agent_type']) {
            // 传输代理
            $ret = $this->validateIpDomain($manual['ip'], '', [], '%s.ip');
        } else {
            $ret = $this->validateIp($manual['ip'], '', [], '%s.ip');
        }
        if (true !== $ret) {
            return $ret;
        }

        // 验证alias
        if (!isset($manual['alias'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'alias');
        }

        // 验证port
        if (!isset($manual['port'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'port');
        }

        $allAgentType = xphp_get_config('client', 'AGENT_TYPE', 'resources');
        unset($allAgentType['UNKNOWN']);
        // 验证agent_type
        if (!isset($manual['agent_type'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'agent_type');
        }
        if (!in_array($manual['agent_type'], $allAgentType)) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_IN'), '%s.agent_type', implode(',', $allAgentType));
        }
        // 验证auto_change_network_flag
        if (!isset($manual['auto_change_network_flag'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'auto_change_network_flag');
        }
        return true;
    }

    /**
     * 验证远程部署客户端参数
     * add_type: int
     * server_port: int
     * server_ip: ip
     * single: object
     * multiple: array
     * @param array $deployment 远程部署客户端参数
     * @return bool|string
     */
    private function checkAddClientDeployment(array $deployment)
    {
        $clientAddType = xphp_get_config('client', 'AGENT_ADD_TYPE', 'resources');
        // 验证add_type
        if (!isset($deployment['add_type'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'add_type');
        }

        // 验证server_port
        if (!isset($deployment['server_port'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'server_port');
        }

        // 验证server_ip
        if (!isset($deployment['server_ip'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'server_ip');
        }
        $ret = $this->validateIp($deployment['server_ip'], '', [], '%s.server_ip');
        if (true !== $ret) {
            return $ret;
        }

        // 分发验证
        if ($deployment['add_type'] == $clientAddType['single']) {
            // 单个添加
            // $deployment需要single，且类型是array
            if (!isset($deployment['single'])) {
                return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'single');
            }
            if (!is_array($deployment['single'])) {
                return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT'), '%s.single');
            }
            $checkSingle = $this->checkAddClientSingle($deployment['single']);
            if (true !== $checkSingle) {
                return sprintf($checkSingle, '%s.single');
            }
        } elseif ($deployment['add_type'] == $clientAddType['multiple']) {
            // 多个添加
            // $deployment需要multiple，且类型是array
            if (!isset($deployment['multiple'])) {
                return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'multiple');
            }
            if (!is_array($deployment['multiple'])) {
                return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT'), '%s.multiple');
            }
            if (!$deployment['multiple']) {
                return sprintf(xphp_get_lang('WEB_VALIDATE_EMPTY'), '%s.multiple');
            }
            $checkMultiple = $this->checkAddClientMultiple($deployment['multiple']);
            if (true !== $checkMultiple) {
                return sprintf($checkMultiple, '%s.multiple');
            }
        } else {
            return sprintf(xphp_get_lang('WEB_VALIDATE_IN'), '%s.add_type', implode(',', $clientAddType));
        }
        return true;
    }

    /**
     * 验证单个添加部署参数
     * os_type: string
     * ip: ip
     * alias: string
     * username: string
     * password: string
     * net_model: int;1,2
     * port: int
     * client_transport_port: int
     * agent_type: int
     * auto_change_network_flag: boolean
     * @param array $single 单个部署参数
     * @return bool|string
     */
    private function checkAddClientSingle(array $single)
    {
        // 验证os_type
        $clientOsType = xphp_get_config('vendor', 'CLIENT_VENDOR');
        $clientOsType = array_column($clientOsType, 'text');
        if (!isset($single['os_type'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'os_type');
        }
        if (!in_array($single['os_type'], $clientOsType)) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_IN'), '%s.os_type', implode(',', $clientOsType));
        }

        // 验证ip
        if (!isset($single['ip'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'ip');
        }
        $ret = $this->validateIp($single['ip'], '', [], '%s.ip');
        if (true !== $ret) {
            return $ret;
        }

        // 验证alias
        if (!isset($single['alias'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'alias');
        }

        // 验证username
        if (!isset($single['username'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'username');
        }

        // 验证password
        if (!isset($single['password'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'password');
        }

        // 验证net_model
        $clientNetModel = xphp_get_config('client', 'AGENT_NET_MODEL', 'resources');
        if (!isset($single['net_model'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'net_model');
        }
        if (!in_array($single['net_model'], $clientNetModel)) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_IN'), '%s.net_model', implode(',', $clientNetModel));
        }

        // 验证port
        if (!isset($single['port'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'port');
        }

        // 验证client_transport_port
        if (!isset($single['client_transport_port'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'client_transport_port');
        }

        $allAgentType = xphp_get_config('client', 'AGENT_TYPE', 'resources');
        unset($allAgentType['UNKNOWN']);
        // 验证agent_type
        if (!isset($single['agent_type'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'agent_type');
        }
        if (!in_array($single['agent_type'], $allAgentType)) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_IN'), '%s.agent_type', implode(',', $allAgentType));
        }
        // 验证auto_change_network_flag
        if (!isset($single['auto_change_network_flag'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'auto_change_network_flag');
        }
        return true;
    }

    /**
     * 验证多个添加参数
     * @param array $multiple 验证多个添加参数
     * @return bool|string
     */
    private function checkAddClientMultiple(array $multiple)
    {
        foreach ($multiple as $index => $single) {
            if (!is_array($single)) {
                return sprintf(xphp_get_lang('WEB_VALIDATE_ARRAY'), "%s[$index]");
            }
            $checkSingle = $this->checkAddClientSingle($single);
            if (true !== $checkSingle) {
                return sprintf($checkSingle, "%s[$index]");
            }
        }
        return true;
    }

    /**
     * 验证authClient的agent_list
     * @param array  $agentList 授权的客户端列表
     * @param string $rule      验证规则
     * @param array  $data      全部请求数据
     * @param string $field     验证字段
     * @return bool|string
     */
    protected function checkAuthClient(array $agentList, string $rule, array $data, string $field)
    {
        foreach ($agentList as $index => $agent) {
            $checkAgent = $this->checkAuthClientAgent($agent);
            if (true !== $checkAgent) {
                return sprintf($checkAgent, $field . "[$index]");
            }
        }
        return true;
    }

    /**
     * 验证authClient的单个客户端信息
     * agent_uuid: string
     * hostname: string
     * auth_module: object
     * old_module: object
     * @param array $agent 单个客户端信息
     * @return bool|string
     */
    private function checkAuthClientAgent(array $agent)
    {
        // 验证agent_uuid
        if (!isset($agent['agent_uuid'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s.agent_uuid');
        }

        // 验证hostname
        if (!isset($agent['hostname'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s.hostname');
        }

        // 验证auth_module
        if (!isset($agent['auth_module'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s.auth_module');
        }
        if (!is_array($agent['auth_module'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT'), '%s.auth_module');
        }
        $checkAuthModule = $this->checkAuthClientModule($agent['auth_module']);
        if (true !== $checkAuthModule) {
            return sprintf($checkAuthModule, '%s.auth_module');
        }

        // 验证old_module
        if (!isset($agent['old_module'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s.old_module');
        }
        if (!is_array($agent['old_module'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT'), '%s.old_module');
        }
        $checkOldModule = $this->checkAuthClientModule($agent['old_module']);
        if (true !== $checkOldModule) {
            return sprintf($checkOldModule, '%s.old_module');
        }
        return true;
    }

    /**
     * 验证authClient的auth_module
     * file: bool
     * database: bool
     * os: bool
     * cdp: bool
     * @param array $authModule 授权的模块
     * @return bool|string
     */
    private function checkAuthClientModule(array $authModule)
    {
        // NOTE: bool类型不能用is_bool验证,因为框架会将true和false转为""和"1"
        // 验证file
        if (!isset($authModule['file'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'file');
        }

        // 验证database
        if (!isset($authModule['database'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'database');
        }

        // 验证os
        if (!isset($authModule['os'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'os');
        }

        // 验证cdp
        if (!isset($authModule['cdp'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'cdp');
        }
        return true;
    }

    /**
     * 验证添加MySQL应用
     * <p>config_path: string</p>
     * <p>username: string</p>
     * <p>password: string</p>
     * <p>auth_type: int; 1,2</p>
     * <p>tcp: object</p>
     * <p>sock: object</p>
     * @param mixed $mysql mysql数据
     * @return bool|string
     */
    protected function checkAddMySQL($mysql)
    {
        $field = 'mysql';
        if (null === $mysql) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_REQUIRE'), $field);
        }
        if (!is_array($mysql)) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_ARRAY'), $field);
        }
        if (!$mysql) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_EMPTY'), $field);
        }

        // 验证config_path
        if (!isset($mysql['config_path'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'config_path');
        }

        // 验证username
        if (!isset($mysql['username'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'username');
        }

        // 验证password
        if (!isset($mysql['password'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'password');
        }

        // 认证auth_type
        $mysqlAuthType = xphp_get_config('client', 'MYSQL_AUTH_TYPE', 'resources');
        if (!isset($mysql['auth_type'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'auth_type');
        }

        if ($mysql['auth_type'] == $mysqlAuthType['tcp']) {
            // 认证tcp
            if (!isset($mysql['tcp'])) {
                return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'tcp');
            }
            if (!is_array($mysql['tcp'])) {
                return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT'), $field . '.tcp');
            }
            $tcpResult = $this->checkAddMySQLTcp($mysql['tcp']);
            if (true !== $tcpResult) {
                return sprintf($tcpResult, $field . '.tcp');
            }
        } elseif ($mysql['auth_type'] == $mysqlAuthType['sock']) {
            // 认证sock
            if (!isset($mysql['sock'])) {
                return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'sock');
            }
            if (!is_array($mysql['sock'])) {
                return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT'), $field . '.sock');
            }
            $sockResult = $this->checkAddMySQLSock($mysql['sock']);
            if (true !== $sockResult) {
                return sprintf($sockResult, $field . '.sock');
            }
        } else {
            return sprintf(xphp_get_lang('WEB_VALIDATE_IN'), $field . '.auth_type', implode(',', $mysqlAuthType));
        }
        return true;
    }

    /**
     * 验证MySQL的tcp参数
     * <p>ip: ip</p>
     * <p>port: int</p>
     * @param array $tcp 添加MySQL应用的tcp参数
     * @return bool|string
     */
    private function checkAddMySQLTcp(array $tcp)
    {
        // 验证ip
        if (!isset($tcp['ip'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'ip');
        }
        $ret = $this->validateIp($tcp['ip'], '', [], '%s.ip');
        if (true !== $ret) {
            return $ret;
        }

        // 验证port
        if (!isset($tcp['port'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'port');
        }
        return true;
    }

    /**
     * 验证MySQL的sock参数
     * <p>host: string</p>
     * <p>sock_path: sting</p>
     * @param array $sock 添加MySQL应用的sock参数
     * @return bool|string
     */
    private function checkAddMySQLSock(array $sock)
    {
        // 验证host
        if (!isset($sock['host'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'host');
        }

        // 验证sock_path
        if (!isset($sock['sock_path'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'sock_path');
        }
        return true;
    }

    /**
     * 验证添加SQLServer应用
     * <p>auth_type: int;1,2</p>
     * <p>user: object {password, username}</p>
     * <p>is_cluster: boolean</p>
     * <p>cluster_ip: ip</p>
     * <p>cluster_info: array</p>
     * @param mixed $sqlServer sql_server数据
     * @return bool|string
     */
    protected function checkAddSQLServer($sqlServer)
    {
        $field = 'sql_server';
        if (null === $sqlServer) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_REQUIRE'), $field);
        }
        if (!is_array($sqlServer)) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_ARRAY'), $field);
        }
        if (!$sqlServer) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_EMPTY'), $field);
        }

        // 验证auth_type
        if (!isset($sqlServer['auth_type'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'auth_type');
        }

        // 验证user
        if (!isset($sqlServer['user'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'auth_type');
        }
        if (!is_array($sqlServer['user'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT'), $field . '.auth_type');
        }
        $user = $sqlServer['user'];

        // 验证username
        if (!isset($user['username'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field . '.user', 'username');
        }

        // 验证password
        if (!isset($user['password'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field . '.user', 'password');
        }

        // 验证is_cluster
        if (!isset($sqlServer['is_cluster'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'is_cluster');
        }

        if ($sqlServer['is_cluster']) {
            // 验证cluster_name
            if (!isset($sqlServer['cluster_name'])) {
                return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'cluster_name');
            }
            // 验证cluster_ip
            if (!isset($sqlServer['cluster_ip'])) {
                return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'cluster_ip');
            }
            $ret = $this->validateIp($sqlServer['cluster_ip'], '', [], '%s.cluster_ip');
            if (true !== $ret) {
                return $ret;
            }

            // 验证cluster_info
            $ret = $this->checkAddClusterInfo($sqlServer['cluster_info'] ?? null);
            if (true !== $ret) {
                return sprintf($ret, $field);
            }
        }
        return true;
    }

    /**
     * 验证添加oracle应用
     * <p>auth_type: int</p>
     * <p>username: string</p>
     * <p>password: string</p>
     * <p>install_db_username: string</p>
     * <p>is_cluster: bool</p>
     * <p>cluster_info: array</p>
     * @param mixed $oracle oracle数据
     * @return bool|string
     */
    private function checkAddOracle($oracle)
    {
        $field = 'oracle';
        if (null === $oracle) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_REQUIRE'), $field);
        }
        if (!is_array($oracle)) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_ARRAY'), $field);
        }
        if (!$oracle) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_EMPTY'), $field);
        }

        // 验证username
        if (!isset($oracle['username'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'username');
        }

        // 验证password
        if (!isset($oracle['password'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'password');
        }

        // 验证install_db_name
        if (!isset($oracle['install_db_username'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'install_db_username');
        }

        // 验证auth_type
        $oracleAuthType = xphp_get_config('client', 'ORACLE_AUTH_TYPE', 'resources');
        unset($oracleAuthType['UNKNOWN']);
        if (!isset($oracle['auth_type'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'auth_type');
        }
        if (!in_array($oracle['auth_type'], $oracleAuthType)) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_IN'), $field . '.auth_type', implode(',', $oracleAuthType));
        }

        // 验证is_cluster
        if (!isset($oracle['is_cluster'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'is_cluster');
        }

        if ($oracle['is_cluster'] && $oracle['auth_type'] == $oracleAuthType['DATABASE_AUTH']) {
            // 验证cluster_name
            if (!isset($oracle['cluster_name'])) {
                return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'cluster_name');
            }

            // 验证cluster_service_ip
            // if (!isset($oracle['cluster_service_ip'])) {
            //     return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'cluster_service_ip');
            // }
            // $ret = $this->validateIp($oracle['cluster_service_ip'], '', [], '%s.cluster_service_ip');
            // if (true !== $ret) {
            //     return $ret;
            // }

            // 验证cluster_info
            $ret = $this->checkAddClusterInfo($oracle['cluster_info'] ?? null);
            if (true !== $ret) {
                return sprintf($ret, $field);
            }
        }
        return true;
    }

    /**
     * @param mixed $clusterInfo 集群信息
     * @return string|boolean
     */
    private function checkAddClusterInfo($clusterInfo)
    {
        if (null === $clusterInfo) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'cluster_info');
        }
        if (!is_array($clusterInfo)) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_ARRAY'), '%s.cluster_info');
        }
        if (!$clusterInfo) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_EMPTY'), '%s.cluster_info');
        }
        foreach ($clusterInfo as $index => $cluster) {
            if (!is_array($cluster)) {
                return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT'), "%s.cluster_info[$index]");
            }
            $clusterResult = $this->checkAddCluster($cluster);
            if (true !== $clusterResult) {
                return sprintf($clusterResult, "%s.cluster_info[$index]");
            }
        }
        return true;
    }

    /**
     * 验证oracle的cluster
     * <p>agent_uuid: string</p>
     * <p>listen_ip: ip</p>
     * <p>listen_port: int</p>
     * @param array $cluster 添加oracle应用的cluster_info参数的子项
     * @return bool|string
     */
    private function checkAddCluster(array $cluster)
    {
        // 验证agent_uuid
        if (!isset($cluster['agent_uuid'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'agent_uuid');
        }

        // 验证listen_ip
        if (!isset($cluster['listen_ip'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'listen_ip');
        }

        // 验证instance_name
        if (!isset($cluster['instance_name'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'instance_name');
        }
        return true;
    }

    /**
     * 验证添加Postgres应用
     * <p>db_name: string</p>
     * <p>db_bin_path: string</p>
     * <p>username: string</p>
     * <p>password: string</p>
     * <p>is_cluster: bool</p>
     * <p>cluster_info: array</p>
     * @param mixed $postgres postgres数据
     * @return bool|string
     */
    protected function checkAddPostgres($postgres)
    {
        $field = 'postgres';
        if (null === $postgres) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_REQUIRE'), $field);
        }
        if (!is_array($postgres)) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_ARRAY'), $field);
        }
        if (!$postgres) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_EMPTY'), $field);
        }

        // 验证db_name
        if (!isset($postgres['db_name'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'db_name');
        }

        // 验证db_bin_path
        if (!isset($postgres['db_bin_path'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'db_bin_path');
        }

        // 验证username
        if (!isset($postgres['username'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'username');
        }

        // 验证password
        if (!isset($postgres['password'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'password');
        }

        // 验证is_cluster
        if (!isset($postgres['is_cluster'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'is_cluster');
        }

        if ($postgres['is_cluster']) {
            // 验证cluster_name
            if (!isset($postgres['cluster_name'])) {
                return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'cluster_name');
            }
            // 验证cluster_info
            $ret = $this->checkAddClusterInfo($postgres['cluster_info'] ?? null);
            if (true !== $ret) {
                return sprintf($ret, $field);
            }
        }
        return true;
    }

    /**
     * 验证添加DM应用
     * <p>install_db_username: string</p>
     * <p>username: string</p>
     * <p>password: string</p>
     * @param mixed $dm dm数据
     * @return bool|string
     */
    protected function checkAddDM($dm)
    {
        $field = 'dm';
        if (null === $dm) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_REQUIRE'), $field);
        }
        if (!is_array($dm)) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_ARRAY'), $field);
        }
        if (!$dm) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_EMPTY'), $field);
        }

        // 验证install_db_username
        if (!isset($dm['install_db_username'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'install_db_username');
        }

        // 验证username
        if (!isset($dm['username'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'username');
        }

        // 验证password
        if (!isset($dm['password'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'password');
        }

        // 验证is_cluster
        if (!isset($dm['is_cluster'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'is_cluster');
        }

        if ($dm['is_cluster']) {
            // 验证cluster_name
            if (!isset($dm['cluster_name'])) {
                return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'cluster_name');
            }
            // 验证cluster_info
            $ret = $this->checkAddClusterInfo($dm['cluster_info'] ?? null);
            if (true !== $ret) {
                return sprintf($ret, $field);
            }
        }
        return true;
    }

    /**
     * 验证添加MongoDB应用
     * <p>username: string</p>
     * <p>password: string</p>
     * <p>is_cluster: bool</p>
     * <p>cluster_name: string</p>
     * <p>cluster_info: array</p>
     * @param mixed $mongoDB mongoDB数据
     * @return bool|string
     */
    protected function checkAddMongoDB($mongoDB)
    {
        $field = 'mongodb';
        if (null === $mongoDB) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_REQUIRE'), $field);
        }
        if (!is_array($mongoDB)) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_ARRAY'), $field);
        }
        if (!$mongoDB) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_EMPTY'), $field);
        }

        // 验证db_bin_path
        if (!isset($mongoDB['db_bin_path'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'db_bin_path');
        }

        // 验证username
        if (!isset($mongoDB['username'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'username');
        }

        // 验证password
        if (!isset($mongoDB['password'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'password');
        }

        // 验证is_cluster
        if (!isset($mongoDB['is_cluster'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'is_cluster');
        }

        if ($mongoDB['is_cluster']) {
            // 验证cluster_name
            if (!isset($mongoDB['cluster_name'])) {
                return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'cluster_name');
            }

            // 验证cluster_info
            $ret = $this->checkAddClusterInfo($mongoDB['cluster_info'] ?? null);
            if (true !== $ret) {
                return sprintf($ret, $field);
            }
        }
        return true;
    }

    /**
     * 验证添加TiDB应用
     * <p>username: string</p>
     * <p>password: string</p>
     * <p>is_cluster: bool</p>
     * <p>cluster_service_ip: ip</p>
     * <p>cluster_info: array</p>
     * @param mixed $tidb TiDB数据
     * @return bool|string
     */
    protected function checkAddTiDB($tidb)
    {
        $field = 'tidb';
        if (null === $tidb) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_REQUIRE'), $field);
        }
        if (!is_array($tidb)) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_ARRAY'), $field);
        }
        if (!$tidb) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_EMPTY'), $field);
        }

        // 验证username
        if (!isset($tidb['username'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'username');
        }

        // 验证password
        if (!isset($tidb['password'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'password');
        }

        // 验证is_cluster
        if (!isset($tidb['is_cluster'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'is_cluster');
        }

        if ($tidb['is_cluster']) {
            // 验证cluster_info
            $ret = $this->checkAddClusterInfo($tidb['cluster_info'] ?? null);
            if (true !== $ret) {
                return sprintf($ret, $field);
            }
        }
        return true;
    }

    /**
     * 验证添加SAP HANA应用
     * <p>username: string</p>
     * <p>password: string</p>
     * <p>is_cluster: bool</p>
     * <p>cluster_service_ip: ip</p>
     * <p>cluster_info: array</p>
     * @param mixed $sapHana SAP HANA数据
     * @return bool|string
     */
    protected function checkAddSapHana($sapHana)
    {
        $field = 'sap_hana';
        if (null === $sapHana) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_REQUIRE'), $field);
        }
        if (!is_array($sapHana)) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_ARRAY'), $field);
        }
        if (!$sapHana) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_EMPTY'), $field);
        }

        // 验证username
        if (!isset($sapHana['username'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'username');
        }

        // 验证password
        if (!isset($sapHana['password'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'password');
        }

        // 验证is_cluster
        if (!isset($sapHana['is_cluster'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'is_cluster');
        }

        if ($sapHana['is_cluster']) {
            // 验证cluster_name
            if (!isset($sapHana['cluster_name'])) {
                return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'cluster_name');
            }
            // 验证cluster_service_ip
            if (!isset($sapHana['cluster_service_ip'])) {
                return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'cluster_service_ip');
            }
            $ret = $this->validateIp($sapHana['cluster_service_ip'], '', [], '%s.cluster_service_ip');
            if (true !== $ret) {
                return $ret;
            }

            // 验证cluster_info
            $ret = $this->checkAddClusterInfo($sapHana['cluster_info'] ?? null);
            if (true !== $ret) {
                return sprintf($ret, $field);
            }
        }
        return true;
    }

    /**
     * 验证实例认证的数据库类别
     * @param int    $dbType 数据库累呗
     * @param string $rules  验证规则
     * @param array  $data   所有请求数据
     * @return boolean|string
     */
    protected function checkAddDbType(int $dbType, string $rules, array $data)
    {
        $allDbType = xphp_get_config('db', 'DB_TYPE');
        switch ($dbType) {
            case $allDbType['ORACLE']:
                $ret = $this->checkAddOracle($data['oracle'] ?? null);
                break;
            case $allDbType['SQLSERVER']:
                $ret = $this->checkAddSQLServer($data['sql_server'] ?? null);
                break;
            case $allDbType['MYSQL']:
            case $allDbType['MARIA']:
                $ret = $this->checkAddMySQL($data['mysql'] ?? null);
                break;
            case $allDbType['DM']:
                $ret = $this->checkAddDM($data['dm'] ?? null);
                break;
            case $allDbType['POSTGRE']:
            case $allDbType['KINGBASE']:
            case $allDbType['UXDB']:
            case $allDbType['HIGHGO']:
            case $allDbType['OPENGAUSS']:
            case $allDbType['VASTBASE']:
            case $allDbType['ANTDB']:
                $ret = $this->checkAddPostgres($data['postgres'] ?? null);
                break;
            case $allDbType['MONGODB']:
                $ret = $this->checkAddMongoDB($data['mongodb'] ?? null);
                break;
            case $allDbType['TIDB']:
                $ret = $this->checkAddTiDB($data['tidb'] ?? null);
                break;
            case $allDbType['CACHE']:
            case $allDbType['IRIS']:
                $ret = true;
                break;
            case $allDbType['SAPHANA']:
                $ret = $this->checkAddSapHana($data['sap_hana'] ?? null);
                break;
            default:
                $ret = "Nonsupport db type: $dbType";
                break;
        }
        return $ret;
    }

    /**
     * 实例认证验证
     * @return Client
     */
    protected function sceneAddClientApp(): Client
    {
        return $this->only(['db_type', 'instance_name', 'agents_uuid', 'listen_ip'])
            ->append('db_type', ['checkAddDbType']);
    }
}
