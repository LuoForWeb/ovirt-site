<?php

namespace app\v1\verification\v0\logic;

use app\v1\common\logic\Base;
use app\v1\opcode\NodeOpcode;
use app\v1\opcode\VerifyOpcode;
use app\v1\opcode\VmOpcode;
use app\v1\resources\v0\logic\Node;
use app\v1\vm\v0\logic\VmPlatform;

/**
 * note          数据验证CDM 之数据管理 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:18
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class VirtualLab extends Base
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->nodeOpcode = new NodeOpcode();
        $this->verifyOpcode = new VerifyOpcode();
        $this->vmOpcode = new VmOpcode();
    }

    /**
     * 添加虚拟演练室
     * @return json
     */
    public function addVirtualLab($params = [])
    {

        //节点uuid
        $nodeuuid = $params['hostInfo']['nodeuuid'] ?? Node::instance()->getLocalNodeUUID();
        //演练室
        $pfMsg['virtual_lab_name'] = $params['labInfo']['virtual_lab_name'];
        //代理网关
        $pfMsg['proxy_name'] = $params['labInfo']['proxy_name'];
        //文件夹
        $pfMsg['folder_name'] = $params['labInfo']['folder_name'];
        //资源池
        $pfMsg['resource_pool_name'] = $params['labInfo']['resource_pool_name'];
        //虚拟交换机
        $pfMsg['isolated_vswitch_name'] = $params['labInfo']['isolated_vswitch_name'];
        $pfMsg['vcenter_uuid'] = $params['hostInfo']['vcenter_uuid'];
        $pfMsg['host_uuid'] = $params['hostInfo']['host_uuid'];
        if ($params['hostInfo']['source_type'] == 2) {
            // 内嵌
            // 从 bd_emd 表 根据 node_uuid 获取 emd_uuid 替换
            $emdUuid = $this->dbSelect("select emd_uuid from bd_emd where node_uuid = ? limit 1", [$nodeuuid]);
            if (!empty($emdUuid)) {
                $pfMsg['vcenter_uuid'] = $emdUuid[0]['emd_uuid'];
                $pfMsg['host_uuid'] = $emdUuid[0]['emd_uuid'];
            }
        }
        $pfMsg['hypervisor_type'] = intval($params['hostInfo']['hypervisor']);
        $pfMsg['storage_uuid'] = $params['hostInfo']['storage_uuid'];
        $pfMsg['proxy_product_network_Info'] = $this->groupProxyNetworkInfo($params['hostInfo']['proxy_info']);
        $pfMsg['network_map_list'] = $this->groupIsolatedNetworkInfo($params['network_list']);

        $opName = 'SR_OP_CODE_DEPLOY_VIRTUAL_LAB';
        $msg = json_encode($pfMsg);
        $mbResult = $this->service()->mbSRMsgs($opName, $nodeuuid, $msg, intval($params['hostInfo']['hypervisor']));

        $result = $mbResult['result'];
        $operate = $this->verifyOpcode->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 修改应用组
     * @return json
     */
    public function editVirtualLab($params = [])
    {

        $opName = 'SR_OP_CODE_MODIFY_VIRTUAL_LAB';
        $operate = $this->verifyOpcode->getOpcodeDes($opName);
        //比较检测是否信息进行了修改
        $oldSettings = json_encode($params['oldSettings']);
        $newSettings = json_encode(array(
            'virtual_lab_uuid' => $params['virtual_lab_uuid'],
            'labInfo' => $params['labInfo'],
            'hostInfo' => $params['hostInfo'],
            'networkInfo' => $params['network_list'],
        ));

        if($oldSettings == $newSettings){
            //未做修改直接返回成功
            return $this->muOpResult(true, $operate);
        }
        //节点uuid
        $nodeuuid = $params['hostInfo']['nodeuuid'] ?? Node::instance()->getLocalNodeUUID();
        //演练室uuid
        $pfMsg['virtual_lab_uuid'] = $params['virtual_lab_uuid'];
        //演练室
        $pfMsg['virtual_lab_name'] = $params['labInfo']['virtual_lab_name'];
        //代理网关
        $pfMsg['proxy_name'] = $params['labInfo']['proxy_name'];
        //文件夹
        $pfMsg['folder_name'] = $params['labInfo']['folder_name'];
        //资源池
        $pfMsg['resource_pool_name'] = $params['labInfo']['resource_pool_name'];
        //虚拟交换机
        $pfMsg['isolated_vswitch_name'] = $params['labInfo']['isolated_vswitch_name'];
        $pfMsg['vcenter_uuid'] = $params['hostInfo']['vcenter_uuid'];
        $pfMsg['host_uuid'] = $params['hostInfo']['host_uuid'];
        if ($params['hostInfo']['source_type'] == 2) {
            // 内嵌
            // 从 bd_emd 表 根据 node_uuid 获取 emd_uuid 替换
            $emdUuid = $this->dbSelect("select emd_uuid from bd_emd where node_uuid = ? limit 1", [$nodeuuid]);
            if (!empty($emdUuid)) {
                $pfMsg['vcenter_uuid'] = $emdUuid[0]['emd_uuid'];
                $pfMsg['host_uuid'] = $emdUuid[0]['emd_uuid'];
            }
        }
        $pfMsg['hypervisor_type'] = intval($params['hostInfo']['hypervisor']);
        $pfMsg['storage_uuid'] = $params['hostInfo']['storage_uuid'];
        $pfMsg['proxy_product_network_Info'] = $this->groupProxyNetworkInfo($params['hostInfo']['proxy_info']);
        $pfMsg['network_map_list'] = $this->groupIsolatedNetworkInfo($params['network_list']);

        $msg = json_encode($pfMsg);
        $mbResult = $this->service()->mbSRMsgs($opName, $nodeuuid, $msg, intval($params['hostInfo']['hypervisor']));

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
     * 删除应用组
     * @return json
     */
    public function deleteVirtualLab($params = [])
    {
        $opName = 'SR_OP_CODE_UNDEPLOY_VIRTUAL_LAB';
        $operate = $this->verifyOpcode->getOpcodeDes($opName);
        //检查虚拟实验室是否在被任务使用，部署中/修改中
        $sub_module_type = $this->checkLabTaskExist($params['lab_uuid_list'], $operate);
        //获取节点uuid
        $sql = "select virtual_lab_uuid, node_uuid from sr_virtual_lab where virtual_lab_uuid = ? ";
        $labData = $this->dbSelect($sql, array($params['lab_uuid_list'][0]));
        $nodeuuid = !empty($labData[0]['node_uuid']) ? $labData[0]['node_uuid'] : Node::instance()->getLocalNodeUUID();

        $pfMsg['virtual_lab_uuid_list'] =$params['lab_uuid_list'];
        $msg = json_encode($pfMsg);
        $mbResult = $this->service()->mbSRMsgs($opName, $nodeuuid, $msg, $sub_module_type, true);

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
     * 获取演练室列表
     * @return json
     */
    public function getVirtualLabList($params = [])
    {

        //起始页
        $offset = $params['offset'];
        //每页呈现的数据量
        $limit = $params['limit'];
        //排序字段
        $sort = $params['sort'];
        //排序方式
        $order = $params['order'];
        //搜索参数
        $keyword = $params['search'];
        //获取数据
        $sql = "select distinct svl.virtual_lab_uuid, svl.hypervisor_type, svl.virtual_lab_name, svl.proxy_name, svl.proxy_ip, svl.proxy_netmask, svl.proxy_gateway, svl.storage_uuid, svl.node_uuid, svl.proxy_network_name, svl.proxy_status, svl.task_progress, unix_timestamp(svl.create_time) create_time, svl.hypervisor_type, svl.network_map, svl.error_code, vh.host_ip, vh.host_name,
                 vv.vcenter_uuid, vv.vcenter_flag, vv.detail, vv.vcenter_ip, vv.nickname, vv.vcenter_name, vv.username from sr_virtual_lab svl left join vm_host vh on svl.host_uuid = vh.host_uuid and svl.vcenter_uuid = vh.vcenter_uuid left join vm_vcenter vv on svl.vcenter_uuid = vv.vcenter_uuid where svl.virtual_lab_uuid is not null ";
        $sqlcount = "select count(distinct svl.virtual_lab_uuid) as total from sr_virtual_lab svl left join vm_host vh on svl.host_uuid = vh.host_uuid left join vm_vcenter vv on svl.vcenter_uuid = vv.vcenter_uuid where svl.virtual_lab_uuid is not null ";
        $sqlparams = array();
        //按演练室名称搜索
        if ($this->checkEmpty($keyword)) {
            $sql .= " and svl.virtual_lab_name like '%" . $keyword . "%' ";
            $sqlcount .= " and svl.virtual_lab_name like '%" . $keyword . "%' ";
        }
        //如果还有排序参数,则进行排序
        if (!empty($sort) && !empty($order)) {
            $sql .= " order by " . $sort . " " . $order;
        }
        if (!empty($offset) && !empty($limit)) {
            $sql .= " limit ?, ?";
            $sqlparams = array_merge($sqlparams, array($offset, $limit));
        }
        //处理数据
        $result = $this->dbSelect($sql, $sqlparams);
        $count = $this->dbSelect($sqlcount);
        $info = array(
            'rows' => array(),
            'total' => intval($count[0]['total']),
        );
        //如果数据为空
        if (empty($result)) {
            return $info;
        }
        foreach ($result as $each) {
            $statusDes = xphp_get_desc('Vm', 'VIRTUAL_LAB_STATUS')[intval($each['proxy_status'])];
            $popover = $statusDes;
            if($each['error_code'] != 0){
                $popover = xphp_get_lang(v1_get_error_des($each['error_code']))."," . xphp_get_lang('WEB_OPHANDLER_ERROR_CODE'). ": #". $each['error_code'];
            }
            $info['rows'][] = array(
                'lab_uuid' => $each['virtual_lab_uuid'],
                'lab_name' => $each['virtual_lab_name'],
                'hypervisor_type' => intval($each['hypervisor_type']),
                'hypervisor_type_des' => xphp_get_config('vm')['VMHYPERVISORDES'][intval($each['hypervisor_type'])],
                'proxy_gateway' => $each['proxy_name']."(".$each['proxy_ip'].")",
                'status' => intval($each['proxy_status']),
                'status_des' => $statusDes,
                'title' => $popover,
                'create_time' => date('Y-m-d H:i:s', intval($each['create_time'])),
                'node_uuid' => $each['node_uuid']
            );
        }

        return $info;
    }

    /**
     * 获取演练详情信息
     * @return json
     */
    public function getVirtualLabDetail($labuuid = "")
    {

        //获取数据
        $sql = "select distinct svl.virtual_lab_uuid, svl.virtual_lab_name, svl.proxy_name, svl.proxy_ip, svl.proxy_netmask, svl.proxy_gateway, svl.storage_uuid, svl.proxy_network_name, svl.proxy_status, svl.task_progress, unix_timestamp(svl.create_time) create_time, svl.hypervisor_type, svl.network_map, svl.error_code, vh.host_ip, vh.host_name,
                vv.vcenter_uuid, vv.vcenter_flag, vv.detail, vv.vcenter_ip, vv.nickname, vv.vcenter_name, vv.username, bsr.storage_nickname, bsr.storage_type from sr_virtual_lab svl left join vm_host vh on svl.host_uuid = vh.host_uuid left join vm_vcenter vv on svl.vcenter_uuid = vv.vcenter_uuid left join bd_storage_resource bsr on svl.storage_uuid = bsr.storage_uuid where svl.virtual_lab_uuid = ? ";
        $sqlparams = array($labuuid);
        //处理数据
        $result = $this->dbSelect($sql, $sqlparams);
        $info = array();
        //如果数据为空
        if (empty($result)) {
            return $info;
        }
        $ptDes = xphp_get_desc('Pf', 'STORAGETYPE');
        foreach ($result as $each) {
            $networkList = json_decode($each['network_map'], true);
            $vcenter_name = xphp_get_config('app','NULLSPACE');
            if($each['hypervisor_type'] != 108){
                $vcenter_name = VmPlatform::instance()->getVcenterNameInTree($each['vcenter_uuid'], $each['hypervisor_type'], $each['vcenter_flag'], $each['vcenter_ip'], $each['nickname'], $each['vcenter_name'], $each['detail'], $each['username']);
            }
            $storageName = $each['storage_uuid'];
            if(!empty($each['storage_nickname'])){
                $storageName = $each['storage_nickname'].'('.xphp_get_lang($ptDes[$each['storage_type']]).')';
            }
            $proxyInfo = array(
                'proxy_name' => $each['proxy_name'],
                'ip' => !empty($each['proxy_ip'])?$each['proxy_ip']:xphp_get_config('app','NULLSPACE'),
                'netmask' => !empty($each['proxy_netmask'])?$each['proxy_netmask']:xphp_get_config('app','NULLSPACE'),
                'gateway' => !empty($each['proxy_gateway'])?$each['proxy_gateway']:xphp_get_config('app','NULLSPACE'),
                'network_name' => $each['proxy_network_name'],
                'mount_storage' => $storageName
            );
            $info = array(
                'proxy_info' => $proxyInfo,
                'network_list' => $networkList['map_list'],
                'lab_name' => $each['virtual_lab_name'],
                'lab_uuid' => $each['virtual_lab_uuid'],
                'vcenter_name' => $vcenter_name,
                'host_name' => !empty($each['host_ip'])?$each['host_ip']:xphp_get_config('app','NULLSPACE'),
                'status' => intval($each['proxy_status']),
                'status_des' => xphp_get_desc('Vm', 'VIRTUAL_LAB_STATUS')[intval($each['proxy_status'])],
            );
        }

        return $info;
    }

    /**
     * 自动生成隔离网络
     * @return json
     */
    public function createIsolationNetwork($params = [])
    {
        $info = array();
        foreach ($params['network_list'] as $d){
            $info[] = array(
                "network_name" => $d['name'],
                "network_uuid" => $d['uuid'],
                "netmask" => $d['netmask'],
                "gateway" => $d['gateway'],
                "segment" => "",
                "dns_server" => "",
                "mac_address" => "",
                "ip_address" => ""
            );
        }
        $opName = 'SR_OP_CODE_AUTO_CACULATE_NETWORK';
        $operate = $this->verifyOpcode->getOpcodeDes($opName);
        //节点uuid
        $nodeuuid = Node::instance()->getLocalNodeUUID();
        $pfMsg['product_network_list'] = $info;
        $pfMsg['proxy_product_network_Info'] = $this->groupProxyNetworkInfo($params['proxy_info']);
        $msg = json_encode($pfMsg);

        $mbResult = $this->service()->mbSRMsgs($opName, $nodeuuid, $msg, $params['hypervisor'], true);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            return array(
                'msg' => $operate,
                'data' => $msg
            );
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 刷新虚拟演练室
     * @return json
     */
    public function refreshVirtualLab($params = [])
    {
        $opName = 'SR_OP_CODE_REFRESH_VIRTUAL_LAB';
        $operate = $this->verifyOpcode->getOpcodeDes($opName);
        //节点uuid
        $nodeuuid = Node::instance()->getLocalNodeUUID();
        $pfMsg['virtual_lab_uuid_list'] = $params['lab_list'];
        $msg = json_encode($pfMsg);
        $mbResult = $this->service()->mbSRMsgs($opName, $nodeuuid, $msg, $params['hypervisor']);

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
     * 获取创建虚拟实验室初始化名称
     * @return string|number|string
     */
    public function getCreateLabName(){
        $labName = "Verification_Lab";

        // 这里判断下存在就在编号前加上1
        $sqlParams = [$labName . '%'];
        $sql = "select virtual_lab_name from sr_virtual_lab where virtual_lab_name like ?";
        $data = $this->dbSelect($sql, $sqlParams);

        if (empty($data)) {
            $info = array(
                'lab_name' => $labName . '1'
            );
            return $info;
        }

        // 取出任务名后面的编号并降序
        $array = str_replace($labName, '', array_column($data, 'virtual_lab_name'));

        $key = array_map('intval', $array);

        $key = !empty($key) ? max($key) + 1 : 1;
        $info = array(
            'lab_name' => $labName . $key
        );
        return $info;
    }

    /**
     * 检查虚拟实验室名是否已用
     * @param string $params [username]
     * @return string
     */
    public function labnameAvailable($params = []){
        $labname = $params['lab_name'];
        $oldname = $params['old_name'];
        //修改检测没有修改名称
        if($labname == $oldname){
            $info = array(
                'available_flag' => true
            );
            return $info;
        }
        $sql = "select virtual_lab_uuid from sr_virtual_lab where virtual_lab_name = ? ";
        $sqlParams = array($labname);
        $data = parent::dbSelect($sql, $sqlParams);
        $info = array(
            'available_flag' => empty($data)
        );
        return $info;
    }

    /**
     * 获取创建虚拟实验室选择宿主机
     * @return string
     */
    public function getVirtualLabHost($params)
    {
        $editFlag = $params['editFlag'];    //修改虚拟实验室
        $vcenteruuid = $params['vcenteruuid'];  //虚拟实验室所在虚拟化中心唯一标识
        $sql = "select vcenter_uuid, vcenter_ip, nickname, vcenter_flag, hypervisor_type, vcenter_name, detail, username from vm_vcenter
                where online_flag = ? ";

        //目前只支持VMware虚拟化
        $sql .= " and hypervisor_type = ? ";
        $sqlParams = array(xphp_get_config('app','FLAG')['SET'], xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_VMWARE']);

        $sql .= " order by hypervisor_type ";

        $data = $this->dbSelect($sql, $sqlParams);
        $node = array();

        foreach ($data as $d) {
            $openFlag = false;
            if ($editFlag) {
                //修改虚拟实验室只展示当前使用的虚拟化中心
                if ($vcenteruuid != $d['vcenter_uuid'])
                    continue;
                $openFlag = true;
            }
            $node[] = array(
                "id" => $d['vcenter_uuid'],
                "pId" => 0,
                "name" => $this->getRecoverHostName($d['hypervisor_type'], $d),
                "open" => $openFlag,
                "isParent" => true,
                "nocheck" => true,
                "hypervisor" => intval($d['hypervisor_type']),
                "type" => 1,
                "iconSkin" => $this->getHypervisorIcon($d['hypervisor_type'], false),
                "vcenteruuid" => $d['vcenter_uuid']
            );
        }
        $info = array(
            'list' => $node
        );

        return $info;
    }

    /**
     * 根据虚拟化类型得到虚拟化中心名称显示
     * @param int $hypervisor   虚拟化类型
     * @param array $vcenterArr 虚拟化中心数据库信息
     */
    private function getRecoverHostName($hypervisor, $vcenterArr)
    {
        $name = $vcenterArr['vcenter_ip'] == $vcenterArr['nickname'] ? $vcenterArr['vcenter_ip'] : $vcenterArr['nickname'] . "(" . $vcenterArr['vcenter_ip'] . ")";
        if (in_array(intval($hypervisor),xphp_get_config('vm', 'VMHYPERVISORGROUP')['xenserver'])) {
            //如果是XenServer
            if ($vcenterArr['vcenter_flag'] == Xphp::$_config['FLAG']['SET']) {
                //如果是vcenter
                $name = $vcenterArr['vcenter_name'] . " (" . xphp_get_lang('WEB_VM_VCENTER_XENSERVER_MASTER_NODE') . ":" . $vcenterArr['vcenter_ip'] . ")";
            } else {
                $name = $vcenterArr['vcenter_name'] . " (" . $vcenterArr['vcenter_ip'] . ")";
            }
        } else if (in_array($hypervisor, xphp_get_config('vm', 'VMHYPERVISORGROUP')['openstack'])) {
            $detail = json_decode($vcenterArr['detail'], true);
            $name = $name . '(' . $vcenterArr['username'] . ')';
        }
        return htmlspecialchars_decode($name);
    }


    /**
     * 根据虚拟化类型得到树的图标
     * @param int $hypervisor
     * @param boolean $vcenterFlag
     */
    public function getHypervisorIcon($hypervisor, $vcenterFlag)
    {

        return VmPlatform::instance()->getHypervisorIcon($hypervisor, $vcenterFlag);
    }

    /**
     * 获取代理网关生产网络信息
     * @param unknown $proxyInfo
     * @return array
     */
    private function groupProxyNetworkInfo($proxyInfo){
        if(empty($proxyInfo)) return array();
        //代理网关网络信息
        $info = array(
            "network_name" => $proxyInfo['network_name'],
            "network_uuid" => $proxyInfo['network_uuid'],
            "ip_address" => $proxyInfo['ip_address'],
            "netmask" => $proxyInfo['netmask'],
            "gateway" => $proxyInfo['gateway'],
            "segment" => "",
            "dns_server" => "",
            "mac_address" => ""
        );

        return $info;

    }

    /**
     * 获取创建虚拟实验室隔离网络信息
     * @param unknown $networkInfo
     * @return array[]
     */
    private function groupIsolatedNetworkInfo($networkInfo){
        if(empty($networkInfo)) return array();
        $list = array();
        foreach ($networkInfo as $network){
            //生产网络
            $product = array(
                "network_name" => $network['productInfo']['name'],
                "network_uuid" => $network['productInfo']['uuid'],
                "netmask" => $network['productInfo']['netmask'],
                "gateway" => $network['productInfo']['gateway'],
                "segment" => "",
                "dns_server" => "",
                "mac_address" => "",
                "ip_address" => ""
            );
            //隔离网络
            $isolated = array(
                "network_name" => $network['isolatedInfo']['name'],
                "network_uuid" => $network['isolatedInfo']['uuid'],
                "netmask" => $network['isolatedInfo']['netmask'],
                "gateway" => $network['isolatedInfo']['gateway'],
                "segment" => "",
                "dns_server" => "",
                "mac_address" => "",
                "ip_address" => ""
            );
            $list[] = array(
                "product_network_info" => $product,
                "isolated_network_info" => $isolated
            );
        }

        return $list;
    }

    /**
     * 检查虚拟实验室是否在任务中，部署中的虚拟实验室不能删除
     * @param string $labuuid
     * @param string $operate
     */
    private function checkLabTaskExist($labList, $operate){
        $labListDes = implode("','", $labList);
        $sql = "select ssb.task_uuid, svl.hypervisor_type, svl.proxy_status from sr_virtual_lab svl left join sr_sure_backup ssb on ssb.virtual_lab_uuid = svl.virtual_lab_uuid where svl.virtual_lab_uuid in ('".$labListDes."')";
        $data = $this->dbSelect($sql);
        foreach ($data as $d) {
            //状态部署中/修改中不能删除
            if(intval($d['proxy_status']) == xphp_get_config('verification')['VIRTUAL_LAB_STATUS']['DEPLOYMENT'] || intval($d['proxy_status']) == xphp_get_config('verification')['VIRTUAL_LAB_STATUS']['MODIFY']){
                exit($this->muOpResult(false, $operate, xphp_get_lang('UI_VIRTUAL_LAB_DELETE_DEPLOY_TIPS'), "warning"));
            }

            //虚拟实验室存在任务中，无法删除
            if(!empty($d['task_uuid'])){
                exit($this->muOpResult(false, $operate, xphp_get_lang('UI_VIRTUAL_LAB_TASK_EXIST_TIPS'), "warning"));
            }
        }

        return intval($data[0]['hypervisor_type']);
    }

}
