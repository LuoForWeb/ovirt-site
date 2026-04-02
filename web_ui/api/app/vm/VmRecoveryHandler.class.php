<?php
/*******************************************
 ** 虚拟机高级恢复数据处理类
 **
 ** @author       xiezhuowei@vinchin.com
 ** @date         2021-3-4 上午10:24:32
 ** @version      1.0.0
 ** @copyright    Copyright 2021 vinchin.com
 ********************************************/
require_once XPHP_PATH.'utils/BLLHandler.class.php';
class VmRecoveryHandler extends BLLHandler{

    /**
     * 组合虚拟机恢复返回配置
     * @param int $hypervisor   虚拟化类型
     * @param string $vcenteruuid   虚拟化中心uuid
     * @param string $hostuuid   主机uuid
     * @param array $points     时间点数组
     * @param array $pointsDetail     时间点详情数组
     * @param array $mbMsg      后台返回消息体msg部分
     * @param bool $instantFlag  瞬时恢复标志
     * @return array
     */
    public function groupVMRecoveryPluginMsg($hypervisor, $vcenteruuid, $hostuuid, $points, $pointsDetail, $mbMsg, $instantFlag = false){
        
        $source_vm_list = $mbMsg['source_vm_list'];
        $target_vm_list = $mbMsg['target_vm_list'];
        $timepoint_uuid_list = $mbMsg['timepoint_uuid_list'];
        
        $vmConfig = include_once XPHP_PATH.'conf/vm_config.php';
        
        
        
        $utils = Xphp::instance('Utils');

        //时间点的配置
        $timepoint_uuids = implode("','", $timepoint_uuid_list);
        $sql = "select timepoint_uuid, vm_config from vm_backup_timepoint where timepoint_uuid in ('" . $timepoint_uuids . "')";
        $timepoint_info = $this->dbSelect($sql);
        $timepoint_info = array_column($timepoint_info, null, 'timepoint_uuid');

        //虚拟化中心版本
        $sql = "select version, detail from vm_vcenter where vcenter_uuid = ?";
        $vcenter_version = $this->dbSelect($sql, [$vcenteruuid])[0]['version'];
        $vcenter_detail = $this->dbSelect($sql, [$vcenteruuid])[0]['detail'];
        $vcenter_detail = json_decode($vcenter_detail, true);

        // 是否包含操作系统是windows的，vmware使用
        $windowsFlag = false;
        
        foreach ($target_vm_list as $key => $value){
            if (!$value['vm_uuid']) {
                $target_vm_list[$key]['vm_uuid'] = $pointsDetail[$key]['vmuuid'];
            }
            //设置目标虚拟化类型
            $target_vm_list[$key]['target_hypervisor_type'] = intval($hypervisor);
            //设置原虚拟化类型
            $target_vm_list[$key]['source_hypervisor_type'] = intval($pointsDetail[$key]['hypervisor']);
            //设置恢复后的虚拟机名称
            $target_vm_list[$key]['new_vm_name'] = htmlspecialchars_decode(html_entity_decode($pointsDetail[$key]['vmname']));
            //设置原本的虚拟机名称
            $target_vm_list[$key]['vm_name'] = htmlspecialchars_decode($pointsDetail[$key]['oldname']);
            //设置内存按单位显示的格式
            $target_vm_list[$key]['memory_array'] = $this->getSizeToUnit($value['memory']);
            
            //设置数据加密密码校验
            $target_vm_list[$key]['data_encrypt'] = $this->getVMDataEncrypt($pointsDetail[$key]['config']);
            //数据加密密码
            $target_vm_list[$key]['timepointuuid'] = $pointsDetail[$key]['timepointuuid'];
            
            //设置磁盘大小和控制器类型
            $diskList = $target_vm_list[$key]['disk_list'];
            
            //启动方式 1 卷启动 2镜像启动
//             $target_vm_list[$key]['openstack_start_mode'] = $value['openstack_start_mode'];
            foreach ($diskList as $dkey => $dvalue){
                $target_vm_list[$key]['disk_list'][$dkey]['virtual_size_unit'] = $utils->calSize($dvalue['virtual_size']);
                $controller_type = intval($dvalue['controller_type']);
                $target_vm_list[$key]['disk_list'][$dkey]['controller_des'] = $vmConfig['VmMiddleControllerTypeDes'][$controller_type];
                $target_vm_list[$key]['disk_list'][$dkey]['disk_key_base64'] = base64_encode($dvalue['disk_key']);

                if (Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_VMWARE'] == $pointsDetail[$key]['hypervisor']
                    && Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_VMWARE'] != $hypervisor) {
                    //从vmware恢复到其他虚拟化类型时只保留磁盘名，去除路径和后缀
                    $target_vm_list[$key]['disk_list'][$dkey]['disk_name'] = pathinfo($dvalue['disk_name'])['filename'];
                }
                if (Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_XHERE'] == $hypervisor) {
                    // 恢复到xhere时，磁盘名称的特殊字符转下划线
                    $target_vm_list[$key]['disk_list'][$dkey]['disk_name'] = preg_replace('/[^\w]|\s/', '_', $dvalue['disk_name']);
                }
            }
            
            
            //设置网卡控制器类型
            $net_list = $target_vm_list[$key]['net_list'];
            foreach ($net_list as $nkey => $nvalue){
                $controller_type = intval($nvalue['controller_type']);
                $target_vm_list[$key]['net_list'][$nkey]['controller_des'] = $vmConfig['VmMiddleControllerTypeDes'][$controller_type];
                
                //增加网络uuid字段
                $network_uuid = "";
                if(!empty($nvalue['subnet_id'])){
                    $network_uuid = $nvalue['subnet_id'];
                }
                $target_vm_list[$key]['net_list'][$nkey]['network_uuid'] = $network_uuid;
            }

            //虚拟机版本是否可选
            $target_vm_list[$key]['vm_version_enable'] = $this->getVmVersionSelectEnable($hypervisor, $vcenteruuid);
            //ics/ics-vvdk恢复版本
            $target_vm_list[$key]['vm_new_version'] = $target_vm_list[$key]['vm_version'];
            if (Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE_KVM'] == $hypervisor
                || Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_VVDK'] == $hypervisor) {
                if ($instantFlag) {
                    //瞬时恢复
                    if (version_compare($vcenter_version, '6.10.0', '>=')) {
                        //如果icenter版本大于等于6.10.0，则虚拟机仅能恢复为2.0版本虚拟机
                        $target_vm_list[$key]['vm_new_version'] = 'V2';
                    } else {
                        //如果icenter版本小于6.10.0，则虚拟机仅能恢复为1.0版本虚拟机
                        $target_vm_list[$key]['vm_new_version'] = 'V1';
                    }
                } else {
                    if (version_compare($vcenter_version, '6.10.0', '>=')) {
                        //如果icenter版本大于等于6.10.0，则虚拟机仅能恢复为2.0版本虚拟机
                        $target_vm_list[$key]['vm_new_version'] = 'V2';
                    } elseif (version_compare($vcenter_version, '6.0.0', '<=')) {
                        //如果icenter版本小于等于6.0.0，则虚拟机仅能恢复为1.0版本虚拟机
                        $target_vm_list[$key]['vm_new_version'] = 'V1';
                    } else {
                        //如果icenter版本大于6.0.0且小于6.10.0，按原版本恢复且可选
                        $target_vm_list[$key]['vm_new_version'] = $target_vm_list[$key]['vm_version'];
                        $target_vm_list[$key]['vm_version_enable'] = true;
                    }
                }
            }

            //openstack删除实例删除卷
            $timepoint_uuid = $timepoint_uuid_list[$key];
            $vm_config = json_decode($timepoint_info[$timepoint_uuid]['vm_config'], true);
            $target_vm_list[$key]['delete_on_termination'] = $vm_config['delete_on_termination'] ?? false;
            //镜像元数据
            $target_vm_list[$key]['image_metadata'] = is_array($vm_config['image_metadata'])
                ? $vm_config['image_metadata'] : json_decode($vm_config['image_metadata'], true);

            //sangfor scp 其他配置 - HA
            $target_vm_list[$key]['is_ha'] = $vm_config['is_ha_enabled'] ?? false;

            //存储类型
            $target_vm_list[$key]['storage_type'] = $this->getTimepointStorageType($pointsDetail[$key]['timepointuuid']);

            // 新增cpu型号和列表、操作系统版本和列表、操作系统内主机名
            // todo 操作系统版本列表，暂时调试用，后面从后台消息的target_vm_list获取
            $target_vm_list[$key]['os_version_list'] = [
                'Linux' => ['CentOS', 'Ubuntu', 'RedHat'],
                'Windows' => ['Win7', 'Win10', 'Win11']
            ];

            // 原机操作系统类型为windows时提示
            if (1 == $source_vm_list[$key]['win_hotadd_warn']) {
                $windowsFlag = true;
            }
        }
        
        $controlContent = $this->getVMRecoveryControlContent($hypervisor, $vcenteruuid, $hostuuid);
        //得到虚拟机名称限制条件
        $vmConfig = include XPHP_PATH.'conf/vm_config.php';
        $vmNameLimit = $vmConfig['VmNameCheck'][$hypervisor];
        
        
        $info = array(
            'hypervisor' => intval($hypervisor),
            'vcenter_detail' => $vcenter_detail,
            'old_hypervisor' => intval($pointsDetail[0]['hypervisor']),
            'config' => $target_vm_list,
            'control' => $controlContent['control'],
            'disk_bus' => $controlContent['disk_bus'],
            'network_bus' => $controlContent['network_bus'],
            'available_domain' => $controlContent['available_domain'],
            'des_dir' => $controlContent['des_dir'],
            'mirror_image' => $controlContent['mirror_image'],
            'vmNameLimit' => $vmNameLimit,
            'maintain_model' => $controlContent['maintain_model'],
            'windows_flag' => $windowsFlag,
        );
        
        return $info;
    }

    /**
     * 获取时间点的存储类型
     * @param $timepointUuid
     * @return int
     */
    public function getTimepointStorageType($timepointUuid)
    {
        $sql = "select bsr.storage_type from bd_backup_timepoint bbt join bd_storage_resource bsr on bbt.storage_uuid = bsr.storage_uuid
                    where bbt.timepoint_uuid = ?";
        $data = $this->dbSelect($sql, [$timepointUuid]);
        return $data[0]['storage_type'] ?? 0;
    }
    
    /**
     *
     * 得到虚拟机恢复控制内容,主要是控制恢复插件内容显示
     * @param unknown $hypervisor
     */
    public function getVMRecoveryControlContent($hypervisor, $vcenteruuid, $hostuuid){
        $this->paramsCheck($hypervisor);
        $hypervisor = intval($hypervisor);
        $hypervisorConf = Xphp::$_config['VMHYPERVISORTYPE'];
        
        //可用域
        $available_domain = array();
        
        //目标目录
        $des_dir = array();
        
        //镜像
        $mirror_image = array();
        
        //置备模式 默认3个
        $maintain_model = array(
            '',
            Xphp::$_lang['UI_PLATFORM_FINE_EQUIP'], 
            Xphp::$_lang['UI_PLATFORM_THICK_DELAY_0'], 
            Xphp::$_lang['UI_PLATFORM_THICK_0'],
        );
        
        switch ($hypervisor){
            //vmware系列
            case $hypervisorConf['VM_HYPERVISOR_TYPE_VMWARE']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_SUGON_CLOUD_VIEW_SRM']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_CLOUD_VIEW_SVM_VMWARE']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_AWS']:
                //显示控制
                $control = array(
                    'other_nav' => true,                //其他,横选项, 如果引导模式,可用域,root密码设置,目标资源池/目录,镜像,ha,虚拟化类型全部是false,这里就是false,表示界面不显示其他
                    'vm_version' => false,               //虚拟机版本
                    'boot_type' => false,               //引导模式
                    'available_domain' => false,        //可用域
                    'root_pass' => false,               //root密码设置
                    'des_dir' => true,                  //目标资源池/目录
                    'mirror_image' => false,            //镜像
                    'HA' => false,                      //ha
                    'virtual_type' => false,            //虚拟化类型
                    
                    'cpu_general' => true,              //cpu,插槽和核心显示
                    'cpu_openstack' => false,           //cpu,OpenStack特殊显示,显示vcpu
                    'cpu_xsky_zstack' => false,         //cpu,zstack和xsky特殊显示,显示xzcpu
                    
                    'disk_high_config' => true,         //磁盘是否显示高级配置
                    'disk_name_modify_flag' => false,   //磁盘名称是否可以修改
                    'disk_setting_mode' => true,        //磁盘置备模式
                    'disk_bus_type' => true,            //磁盘总线类型
                    'disk_cluster_size' => false,       //磁盘分块大小
                    'disk_type_hyperv' => false,         //磁盘类型，hyperv特殊显示，显示disktypehyperv
                    
                    'network_high_config' => true,      //网络是否显示高级配置
                    'network_bus_type' => true,         //网卡类型
                    'network_ip_setting' => false,      //网卡IP设置
                    'os_type'=>true,                    //跨平台恢复是否支持操作系统选择
                    'image_metadata' => false,          //镜像元数据，OpenStack特殊显示
                );
                
                //磁盘控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $disk_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_LSI_LOGIC' => 'LSI Logic',
                    'VM_MIDDLE_CONTROLLER_TYPE_LSI_LOGIC_SAS' => 'LSI Logic SAS',
                    'VM_MIDDLE_CONTROLLER_TYPE_BUS_LOGIC' => Xphp::$_lang['UI_PLATFORM_BUS_PARALLEL'],
                    'VM_MIDDLE_CONTROLLER_TYPE_PARA_VIRTUAL_SCSI' => Xphp::$_lang['UI_PLATFORM_VM_PARAVIRTU_SCSI'],
                    'VM_MIDDLE_CONTROLLER_TYPE_AHCI' => 'AHCI SATA',
                    'VM_MIDDLE_CONTROLLER_TYPE_IDE' => 'IDE',
                    'VM_MIDDLE_CONTROLLER_TYPE_NVME' => 'NVME',
                );
                $disk_bus = $this->getControllerTypeDesArr($disk_bus);
                
                //网卡控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $network_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_E1000' => 'E1000',
                    'VM_MIDDLE_CONTROLLER_TYPE_E1000E' => 'E1000E',
                    'VM_MIDDLE_CONTROLLER_TYPE_SRIOV' => Xphp::$_lang['UI_PLATFORM_SRIOV_PASSTHROUGH'],
                    'VM_MIDDLE_CONTROLLER_TYPE_PCNET32' => Xphp::$_lang['UI_PLATFORM_VARIABLE'],
                    'VM_MIDDLE_CONTROLLER_TYPE_VMXNET' => 'VMXNET',
                    'VM_MIDDLE_CONTROLLER_TYPE_VMXNET2' => 'VMXNET 2',
                    'VM_MIDDLE_CONTROLLER_TYPE_VMXNET3' => 'VMXNET 3',
                    'VM_MIDDLE_CONTROLLER_TYPE_VMXNET3_VRDMA' => 'PVRDMA',
                );
                $network_bus = $this->getControllerTypeDesArr($network_bus);
                
                //目标目录
                $des_dir = $this->getDesDir($hostuuid);
                break;
                
            //XenServer/XCP-NG/vGate等
            case $hypervisorConf['VM_HYPERVISOR_TYPE_XENSERVER']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_HALSIGN_VGATE']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_DONGCHEN_DSERVER']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_XCP_NG']:
                //显示控制
                $control = array(
                    'other_nav' => true,                //其他,横选项
                    'vm_version' => false,               //虚拟机版本
                    'boot_type' => false,               //引导模式
                    'available_domain' => false,        //可用域
                    'root_pass' => false,               //root密码设置
                    'des_dir' => false,                  //目标资源池/目录
                    'mirror_image' => false,            //镜像
                    'HA' => true,                      //ha
                    'virtual_type' => false,            //虚拟化类型
                    
                    'cpu_general' => true,              //cpu,插槽和核心显示
                    'cpu_openstack' => false,              //cpu,OpenStack特殊显示,显示vcpu
                    'cpu_xsky_zstack' => false,         //cpu,zstack和xsky特殊显示,显示xzcpu
                    
                    'disk_high_config' => true,         //磁盘是否显示高级配置
                    'disk_name_modify_flag' => true,   //磁盘名称是否可以修改
                    'disk_setting_mode' => false,        //磁盘置备模式
                    'disk_bus_type' => false,            //磁盘总线类型
                    'disk_cluster_size' => false,        //磁盘分块大小
                    'disk_type_hyperv' => false,         //磁盘类型，hyperv特殊显示，显示disktypehyperv
                    
                    'network_high_config' => true,      //网络是否显示高级配置
                    'network_bus_type' => false,         //网卡类型
                    'network_ip_setting' => false,       //网卡IP设置
                    'os_type'=>true,                    //跨平台恢复是否支持操作系统选择
                    'image_metadata' => false,          //镜像元数据，OpenStack特殊显示
                );
                //磁盘控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $disk_bus = array();
                
                //网卡控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $network_bus = array();
                
                break;
                
            //云宏Xen
            case $hypervisorConf['VM_HYPERVISOR_TYPE_WINHONG_WINSERVER']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_DIY_WINGHONG']:
                //显示控制
                $control = array(
                    'other_nav' => false,                //其他,横选项
                    'vm_version' => false,               //虚拟机版本
                    'boot_type' => false,               //引导模式
                    'available_domain' => false,        //可用域
                    'root_pass' => false,               //root密码设置
                    'des_dir' => false,                  //目标资源池/目录
                    'mirror_image' => false,            //镜像
                    'HA' => false,                      //ha
                    'virtual_type' => false,            //虚拟化类型
                    
                    'cpu_general' => true,              //cpu,插槽和核心显示
                    'cpu_openstack' => false,              //cpu,OpenStack特殊显示,显示vcpu
                    'cpu_xsky_zstack' => false,         //cpu,zstack和xsky特殊显示,显示xzcpu
                    
                    'disk_high_config' => true,         //磁盘是否显示高级配置
                    'disk_name_modify_flag' => true,   //磁盘名称是否可以修改
                    'disk_setting_mode' => false,        //磁盘置备模式
                    'disk_bus_type' => false,            //磁盘总线类型
                    'disk_cluster_size' => false,        //磁盘分块大小
                    'disk_type_hyperv' => false,         //磁盘类型，hyperv特殊显示，显示disktypehyperv
                    
                    'network_high_config' => true,      //网络是否显示高级配置
                    'network_bus_type' => false,         //网卡类型
                    'network_ip_setting' => false,       //网卡IP设置
                    'os_type'=>true,                    //跨平台恢复是否支持操作系统选择
                    'image_metadata' => false,          //镜像元数据，OpenStack特殊显示
                );
                
                //磁盘控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $disk_bus = array();
                
                //网卡控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $network_bus = array();
                break;

            //Proxmox
            case $hypervisorConf['VM_HYPERVISOR_TYPE_PROXMOX']:
                //显示控制
                $control = array(
                    'other_nav' => false,                //其他,横选项
                    'vm_version' => false,               //虚拟机版本
                    'boot_type' => false,               //引导模式
                    'available_domain' => false,        //可用域
                    'root_pass' => false,               //root密码设置
                    'des_dir' => false,                  //目标资源池/目录
                    'mirror_image' => false,            //镜像
                    'HA' => false,                      //ha
                    'virtual_type' => false,            //虚拟化类型

                    'cpu_general' => true,              //cpu,插槽和核心显示
                    'cpu_openstack' => false,              //cpu,OpenStack特殊显示,显示vcpu
                    'cpu_xsky_zstack' => false,         //cpu,zstack和xsky特殊显示,显示xzcpu

                    'disk_high_config' => true,         //磁盘是否显示高级配置
                    'disk_name_modify_flag' => true,   //磁盘名称是否可以修改
                    'disk_setting_mode' => false,        //磁盘置备模式
                    'disk_bus_type' => true,            //磁盘总线类型
                    'disk_cluster_size' => false,        //磁盘分块大小
                    'disk_type_hyperv' => false,         //磁盘类型，hyperv特殊显示，显示disktypehyperv

                    'network_high_config' => true,      //网络是否显示高级配置
                    'network_bus_type' => true,         //网卡类型
                    'network_ip_setting' => false,       //网卡IP设置
                    'os_type'=>true,                    //跨平台恢复是否支持操作系统选择
                    'image_metadata' => false,          //镜像元数据，OpenStack特殊显示
                );

                //磁盘控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $disk_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_IDE' => 'IDE',
                    'VM_MIDDLE_CONTROLLER_TYPE_SATA' => 'SATA',
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => 'VIRTIO',
                    'VM_MIDDLE_CONTROLLER_TYPE_SCSI' => 'SCSI',
                );

                $disk_bus = $this->getControllerTypeDesArr($disk_bus);

                //网卡控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $network_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => 'virtio',
                    'VM_MIDDLE_CONTROLLER_TYPE_E1000' => 'E1000',
                    'VM_MIDDLE_CONTROLLER_TYPE_RTL8139' => 'rtl8139',
                    'VM_MIDDLE_CONTROLLER_TYPE_VMXNET3' => 'vmxnet3',
                );

                $network_bus = $this->getControllerTypeDesArr($network_bus);
                break;

            // Lenovo AIO
            case $hypervisorConf['VM_HYPERVISOR_TYPE_LENOVO_AIO']:
                //显示控制
                $control = array(
                    'other_nav' => false,                //其他,横选项
                    'vm_version' => false,               //虚拟机版本
                    'boot_type' => false,               //引导模式
                    'available_domain' => false,        //可用域
                    'root_pass' => false,               //root密码设置
                    'des_dir' => false,                  //目标资源池/目录
                    'mirror_image' => false,            //镜像
                    'HA' => false,                      //ha
                    'virtual_type' => false,            //虚拟化类型

                    'cpu_general' => true,              //cpu,插槽和核心显示
                    'cpu_openstack' => false,              //cpu,OpenStack特殊显示,显示vcpu
                    'cpu_xsky_zstack' => false,         //cpu,zstack和xsky特殊显示,显示xzcpu

                    'disk_high_config' => true,         //磁盘是否显示高级配置
                    'disk_name_modify_flag' => false,   //磁盘名称是否可以修改
                    'disk_setting_mode' => false,        //磁盘置备模式
                    'disk_bus_type' => true,            //磁盘总线类型
                    'disk_cluster_size' => false,        //磁盘分块大小
                    'disk_type_hyperv' => false,         //磁盘类型，hyperv特殊显示，显示disktypehyperv

                    'network_high_config' => true,      //网络是否显示高级配置
                    'network_bus_type' => true,         //网卡类型
                    'network_ip_setting' => false,       //网卡IP设置
                    'os_type'=>true,                    //跨平台恢复是否支持操作系统选择
                    'image_metadata' => false,          //镜像元数据，OpenStack特殊显示
                );

                //磁盘控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $disk_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_IDE' => 'IDE',
                    'VM_MIDDLE_CONTROLLER_TYPE_SATA' => 'SATA',
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => 'VIRTIO',
                    'VM_MIDDLE_CONTROLLER_TYPE_SCSI' => 'SCSI',
                );

                $disk_bus = $this->getControllerTypeDesArr($disk_bus);

                //网卡控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $network_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => 'virtio',
                    'VM_MIDDLE_CONTROLLER_TYPE_E1000' => 'E1000',
                    'VM_MIDDLE_CONTROLLER_TYPE_RTL8139' => 'rtl8139',
                    'VM_MIDDLE_CONTROLLER_TYPE_VMXNET3' => 'vmxnet3',
                );

                $network_bus = $this->getControllerTypeDesArr($network_bus);
                break;
                
            //华为FusionCompute(Xen)
            case $hypervisorConf['VM_HYPERVISOR_TYPE_HUAWEI_FUSION_SPHERE_XEN']:
                //显示控制
                $control = array(
                    'other_nav' => false,                //其他,横选项
                    'vm_version' => false,               //虚拟机版本
                    'boot_type' => false,               //引导模式
                    'available_domain' => false,        //可用域
                    'root_pass' => false,               //root密码设置
                    'des_dir' => false,                  //目标资源池/目录
                    'mirror_image' => false,            //镜像
                    'HA' => false,                      //ha
                    'virtual_type' => false,            //虚拟化类型
                    'cpu_general' => true,              //cpu,插槽和核心显示
                    'cpu_openstack' => false,              //cpu,OpenStack特殊显示,显示vcpu
                    'cpu_xsky_zstack' => false,         //cpu,zstack和xsky特殊显示,显示xzcpu
                    
                    'disk_high_config' => true,         //磁盘是否显示高级配置
                    'disk_name_modify_flag' => true,   //磁盘名称是否可以修改
                    'disk_setting_mode' => false,        //磁盘置备模式
                    'disk_bus_type' => false,            //磁盘总线类型
                    'disk_cluster_size' => false,        //磁盘分块大小
                    'disk_type_hyperv' => false,         //磁盘类型，hyperv特殊显示，显示disktypehyperv
                    
                    'network_high_config' => true,      //网络是否显示高级配置
                    'network_bus_type' => false,         //网卡类型
                    'network_ip_setting' => false,       //网卡IP设置
                    'os_type'=>true,                    //跨平台恢复是否支持操作系统选择
                    'image_metadata' => false,          //镜像元数据，OpenStack特殊显示
                );
                //磁盘控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $disk_bus = array();
                
                //网卡控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $network_bus = array();
                break;
                
            //华为FusionCompute(KVM)
            case $hypervisorConf['VM_HYPERVISOR_TYPE_HUAWEI_FUSION_SPHERE_KVM']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_XFUSION_KVM']:
                //显示控制
                $control = array(
                    'other_nav' => false,                //其他,横选项
                    'vm_version' => false,               //虚拟机版本
                    'boot_type' => false,               //引导模式
                    'available_domain' => false,        //可用域
                    'root_pass' => false,               //root密码设置
                    'des_dir' => false,                  //目标资源池/目录
                    'mirror_image' => false,            //镜像
                    'HA' => false,                      //ha
                    'virtual_type' => false,            //虚拟化类型
                    'cpu_general' => true,              //cpu,插槽和核心显示
                    'cpu_openstack' => false,              //cpu,OpenStack特殊显示,显示vcpu
                    'cpu_xsky_zstack' => false,         //cpu,zstack和xsky特殊显示,显示xzcpu
                    
                    'disk_high_config' => true,         //磁盘是否显示高级配置
                    'disk_name_modify_flag' => true,   //磁盘名称是否可以修改
                    'disk_setting_mode' => true,        //磁盘置备模式
                    'disk_bus_type' => true,            //磁盘总线类型
                    'disk_cluster_size' => false,        //磁盘分块大小
                    'disk_type_hyperv' => false,         //磁盘类型，hyperv特殊显示，显示disktypehyperv
                    
                    'network_high_config' => true,      //网络是否显示高级配置
                    'network_bus_type' => true,         //网卡类型
                    'network_ip_setting' => false,       //网卡IP设置
                    'os_type'=>true,                    //跨平台恢复是否支持操作系统选择
                    'image_metadata' => false,          //镜像元数据，OpenStack特殊显示
                );
                //磁盘控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $disk_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_SCSI' => 'SCSI',
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => 'VIRTIO',
                    'VM_MIDDLE_CONTROLLER_TYPE_IDE' => 'IDE',
                );
                
                $disk_bus = $this->getControllerTypeDesArr($disk_bus);
                
                //网卡控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $network_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => 'virtio',
                );
                
                $network_bus = $this->getControllerTypeDesArr($network_bus);
                //置备模式
                $maintain_model = array(
                    '',
                    Xphp::$_lang['UI_PLATFORM_THIN_EQUIP'],
                    Xphp::$_lang['UI_PLATFORM_NORMAL_DELAY_0'],
                    Xphp::$_lang['UI_PLATFORM_NORMAL'],
                );
                
                break;
                
            //H3C CAS/UIS
            case $hypervisorConf['VM_HYPERVISOR_TYPE_H3C_KVM']:
                //显示控制
                $control = array(
                    'other_nav' => false,                //其他,横选项
                    'vm_version' => false,               //虚拟机版本
                    'boot_type' => false,               //引导模式
                    'available_domain' => false,        //可用域
                    'root_pass' => false,               //root密码设置
                    'des_dir' => false,                  //目标资源池/目录
                    'mirror_image' => false,            //镜像
                    'HA' => false,                      //ha
                    'virtual_type' => false,            //虚拟化类型
                    'cpu_general' => true,              //cpu,插槽和核心显示
                    'cpu_openstack' => false,              //cpu,OpenStack特殊显示,显示vcpu
                    'cpu_xsky_zstack' => false,         //cpu,zstack和xsky特殊显示,显示xzcpu

                    'disk_high_config' => true,         //磁盘是否显示高级配置
                    'disk_name_modify_flag' => false,   //磁盘名称是否可以修改
                    'disk_setting_mode' => false,        //磁盘置备模式
                    'disk_bus_type' => true,            //磁盘总线类型
                    'disk_cluster_size' => false,        //磁盘分块大小
                    'disk_type_hyperv' => false,         //磁盘类型，hyperv特殊显示，显示disktypehyperv

                    'network_high_config' => true,      //网络是否显示高级配置
                    'network_bus_type' => true,         //网卡类型
                    'network_ip_setting' => false,       //网卡IP设置
                    'os_type'=>true,                    //跨平台恢复是否支持操作系统选择
                    'image_metadata' => false,          //镜像元数据，OpenStack特殊显示
                );
                //磁盘控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $disk_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_IDE' => Xphp::$_lang['UI_PLATFORM_IDE_HARDDISK'],
                    'VM_MIDDLE_CONTROLLER_TYPE_SCSI' => Xphp::$_lang['UI_PLATFORM_SCSI_HARDDISK'],
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => Xphp::$_lang['UI_PLATFORM_HSPEED_HARDDISK'],
                    'VM_MIDDLE_CONTROLLER_TYPE_VSCSI' => Xphp::$_lang['UI_PLATFORM_HSPEED_SCSI_HARDDISK'],
                    'VM_MIDDLE_CONTROLLER_TYPE_USB' => Xphp::$_lang['UI_PLATFORM_USB_HARDDISK'],
                );
                $disk_bus = $this->getControllerTypeDesArr($disk_bus);

                //网卡控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $network_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_E1000' => Xphp::$_lang['UI_PLATFORM_IE_CARD'],
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => Xphp::$_lang['UI_PLATFORM_HSPEED_CARD'],
                    'VM_MIDDLE_CONTROLLER_TYPE_RTL8139' => Xphp::$_lang['UI_PLATFORM_NORMAL_CARD'],
                );

                $network_bus = $this->getControllerTypeDesArr($network_bus);
                break;
            case $hypervisorConf['VM_HYPERVISOR_TYPE_H3C_CAS_CVD']:
                //显示控制
                $control = array(
                    'other_nav' => false,                //其他,横选项
                    'vm_version' => false,               //虚拟机版本
                    'boot_type' => false,               //引导模式
                    'available_domain' => false,        //可用域
                    'root_pass' => false,               //root密码设置
                    'des_dir' => false,                  //目标资源池/目录
                    'mirror_image' => false,            //镜像
                    'HA' => false,                      //ha
                    'virtual_type' => false,            //虚拟化类型
                    'cpu_general' => true,              //cpu,插槽和核心显示
                    'cpu_openstack' => false,              //cpu,OpenStack特殊显示,显示vcpu
                    'cpu_xsky_zstack' => false,         //cpu,zstack和xsky特殊显示,显示xzcpu
                    
                    'disk_high_config' => true,         //磁盘是否显示高级配置
                    'disk_name_modify_flag' => true,   //磁盘名称是否可以修改
                    'disk_setting_mode' => false,        //磁盘置备模式
                    'disk_bus_type' => true,            //磁盘总线类型
                    'disk_cluster_size' => false,        //磁盘分块大小
                    'disk_type_hyperv' => false,         //磁盘类型，hyperv特殊显示，显示disktypehyperv
                    
                    'network_high_config' => true,      //网络是否显示高级配置
                    'network_bus_type' => true,         //网卡类型
                    'network_ip_setting' => false,       //网卡IP设置
                    'os_type'=>true,                    //跨平台恢复是否支持操作系统选择
                    'image_metadata' => false,          //镜像元数据，OpenStack特殊显示
                );
                //磁盘控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $disk_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_IDE' => Xphp::$_lang['UI_PLATFORM_IDE_HARDDISK'],
                    'VM_MIDDLE_CONTROLLER_TYPE_SCSI' => Xphp::$_lang['UI_PLATFORM_SCSI_HARDDISK'],
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => Xphp::$_lang['UI_PLATFORM_HSPEED_HARDDISK'],
                    'VM_MIDDLE_CONTROLLER_TYPE_VSCSI' => Xphp::$_lang['UI_PLATFORM_HSPEED_SCSI_HARDDISK'],
                    'VM_MIDDLE_CONTROLLER_TYPE_USB' => Xphp::$_lang['UI_PLATFORM_USB_HARDDISK'],
                );
                $disk_bus = $this->getControllerTypeDesArr($disk_bus);
                
                //网卡控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $network_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_E1000' => Xphp::$_lang['UI_PLATFORM_IE_CARD'],
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => Xphp::$_lang['UI_PLATFORM_HSPEED_CARD'],
                    'VM_MIDDLE_CONTROLLER_TYPE_RTL8139' => Xphp::$_lang['UI_PLATFORM_NORMAL_CARD'],
                );
                
                $network_bus = $this->getControllerTypeDesArr($network_bus);
                break;
                
            //RHV/Ovirt/OLVM/易讯通/zVirt
            case $hypervisorConf['VM_HYPERVISOR_TYPE_KVM']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_NEOKYLIN_KVM']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_RHV_KVM']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_OVIRT_KVM']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_KVM_ZVIRT']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_EASTED_VSERVER']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_OLVM']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_HOSTVM']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_RED_VIRT']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_ROSA_VIRT']:
                //显示控制
                $control = array(
                    'other_nav' => false,                //其他,横选项
                    'vm_version' => false,               //虚拟机版本
                    'boot_type' => false,               //引导模式
                    'available_domain' => false,        //可用域
                    'root_pass' => false,               //root密码设置
                    'des_dir' => false,                  //目标资源池/目录
                    'mirror_image' => false,            //镜像
                    'HA' => false,                      //ha
                    'virtual_type' => false,            //虚拟化类型
                    'cpu_general' => true,              //cpu,插槽和核心显示
                    'cpu_openstack' => false,              //cpu,OpenStack特殊显示,显示vcpu
                    'cpu_xsky_zstack' => false,         //cpu,zstack和xsky特殊显示,显示xzcpu

                    'disk_high_config' => true,         //磁盘是否显示高级配置
                    'disk_name_modify_flag' => true,   //磁盘名称是否可以修改
                    'disk_setting_mode' => false,        //磁盘置备模式
                    'disk_bus_type' => true,            //磁盘总线类型
                    'disk_cluster_size' => false,        //磁盘分块大小
                    'disk_type_hyperv' => false,         //磁盘类型，hyperv特殊显示，显示disktypehyperv

                    'network_high_config' => true,      //网络是否显示高级配置
                    'network_bus_type' => true,         //网卡类型
                    'network_ip_setting' => false,       //网卡IP设置
                    'os_type'=>true,                    //跨平台恢复是否支持操作系统选择
                    'image_metadata' => false,          //镜像元数据，OpenStack特殊显示
                );
                //磁盘控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $disk_bus = $this->getRhvOvirtDiskBus($vcenteruuid);
                $disk_bus = $this->getControllerTypeDesArr($disk_bus);
                
                //网卡控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $network_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_E1000' => 'e1000',
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => 'VirtIO',
                    'VM_MIDDLE_CONTROLLER_TYPE_RTL8139' => 'rtl8139',
                    'VM_MIDDLE_CONTROLLER_TYPE_RTL8139_VIRTIO' => Xphp::$_lang['UI_PLATFORM_DUALMODE_RV'],
                );
                $network_bus = $this->getControllerTypeDesArr($network_bus);
                break;

            case $hypervisorConf['VM_HYPERVISOR_TYPE_WINHONG_KVM']:
                //显示控制
                $control = array(
                    'other_nav' => true,                //其他,横选项
                    'vm_version' => false,               //虚拟机版本
                    'boot_type' => false,               //引导模式
                    'available_domain' => false,        //可用域
                    'root_pass' => false,               //root密码设置
                    'des_dir' => false,                  //目标资源池/目录
                    'mirror_image' => false,            //镜像
                    'HA' => true,                      //ha
                    'virtual_type' => false,            //虚拟化类型
                    'cpu_general' => true,              //cpu,插槽和核心显示
                    'cpu_openstack' => false,              //cpu,OpenStack特殊显示,显示vcpu
                    'cpu_xsky_zstack' => false,         //cpu,zstack和xsky特殊显示,显示xzcpu

                    'disk_high_config' => true,         //磁盘是否显示高级配置
                    'disk_name_modify_flag' => true,   //磁盘名称是否可以修改
                    'disk_setting_mode' => false,        //磁盘置备模式
                    'disk_bus_type' => true,            //磁盘总线类型
                    'disk_cluster_size' => false,        //磁盘分块大小
                    'disk_type_hyperv' => false,         //磁盘类型，hyperv特殊显示，显示disktypehyperv

                    'network_high_config' => true,      //网络是否显示高级配置
                    'network_bus_type' => true,         //网卡类型
                    'network_ip_setting' => false,       //网卡IP设置
                    'os_type'=>true,                    //跨平台恢复是否支持操作系统选择
                    'image_metadata' => false,          //镜像元数据，OpenStack特殊显示
                );
                //磁盘控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $disk_bus = $this->getRhvOvirtDiskBus($vcenteruuid);
                $disk_bus = $this->getControllerTypeDesArr($disk_bus);

                //网卡控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $network_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_E1000' => 'e1000',
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => 'VirtIO',
                    'VM_MIDDLE_CONTROLLER_TYPE_RTL8139' => 'rtl8139',
                    'VM_MIDDLE_CONTROLLER_TYPE_RTL8139_VIRTIO' => Xphp::$_lang['UI_PLATFORM_DUALMODE_RV'],
                );
                $network_bus = $this->getControllerTypeDesArr($network_bus);
                break;

            case $hypervisorConf['VM_HYPERVISOR_TYPE_SMARTX_KVM']:
                //显示控制
                $control = array(
                    'other_nav' => false,                //其他,横选项
                    'vm_version' => false,               //虚拟机版本
                    'boot_type' => false,               //引导模式
                    'available_domain' => false,        //可用域
                    'root_pass' => false,               //root密码设置
                    'des_dir' => false,                  //目标资源池/目录
                    'mirror_image' => false,            //镜像
                    'HA' => false,                      //ha
                    'virtual_type' => false,            //虚拟化类型
                    'cpu_general' => true,              //cpu,插槽和核心显示
                    'cpu_openstack' => false,              //cpu,OpenStack特殊显示,显示vcpu
                    'cpu_xsky_zstack' => false,         //cpu,zstack和xsky特殊显示,显示xzcpu
                    
                    'disk_high_config' => true,         //磁盘是否显示高级配置
                    'disk_name_modify_flag' => true,   //磁盘名称是否可以修改
                    'disk_setting_mode' => false,        //磁盘置备模式
                    'disk_bus_type' => true,            //磁盘总线类型
                    'disk_cluster_size' => false,        //磁盘分块大小
                    'disk_type_hyperv' => false,         //磁盘类型，hyperv特殊显示，显示disktypehyperv
                    
                    'network_high_config' => true,      //网络是否显示高级配置
                    'network_bus_type' => true,         //网卡类型
                    'network_ip_setting' => false,       //网卡IP设置
                    'os_type'=>true,                    //跨平台恢复是否支持操作系统选择
                    'image_metadata' => false,          //镜像元数据，OpenStack特殊显示
                );
                //磁盘控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $disk_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => 'virtio',
                    'VM_MIDDLE_CONTROLLER_TYPE_IDE' => 'ide',
                    'VM_MIDDLE_CONTROLLER_TYPE_SCSI' => 'scsi'
                );
                $disk_bus = $this->getControllerTypeDesArr($disk_bus);
                
                //网卡控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $network_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_E1000' => 'e1000',
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => 'virtio',
                    'VM_MIDDLE_CONTROLLER_TYPE_SRIOV' => 'sriov'
                );
                $network_bus = $this->getControllerTypeDesArr($network_bus);
                break;
                
            //Inspur ICS
            case $hypervisorConf['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE_KVM']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_INSPUR_VVDK']:
                //显示控制
                $control = array(
                    'other_nav' => true,                //其他,横选项
                    'vm_version' => true,               //虚拟机版本
                    'boot_type' => false,               //引导模式
                    'available_domain' => false,        //可用域
                    'root_pass' => false,               //root密码设置
                    'des_dir' => false,                  //目标资源池/目录
                    'mirror_image' => false,            //镜像
                    'HA' => false,                      //ha
                    'virtual_type' => false,            //虚拟化类型
                    'cpu_general' => true,              //cpu,插槽和核心显示
                    'cpu_openstack' => false,              //cpu,OpenStack特殊显示,显示vcpu
                    'cpu_xsky_zstack' => false,         //cpu,zstack和xsky特殊显示,显示xzcpu
                    
                    'disk_high_config' => true,         //磁盘是否显示高级配置
                    'disk_name_modify_flag' => true,   //磁盘名称是否可以修改
                    'disk_setting_mode' => false,        //磁盘置备模式
                    'disk_bus_type' => true,            //磁盘总线类型
                    'disk_cluster_size' => false,        //磁盘分块大小
                    'disk_type_hyperv' => false,         //磁盘类型，hyperv特殊显示，显示disktypehyperv
                    
                    'network_high_config' => true,      //网络是否显示高级配置
                    'network_bus_type' => true,         //网卡类型
                    'network_ip_setting' => false,       //网卡IP设置
                    'os_type'=>true,                    //跨平台恢复是否支持操作系统选择
                    'image_metadata' => false,          //镜像元数据，OpenStack特殊显示
                );
                //磁盘控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $disk_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_IDE' => 'IDE',
                    'VM_MIDDLE_CONTROLLER_TYPE_SCSI' => 'SCSI',
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => 'VIRTIO',
                    'VM_MIDDLE_CONTROLLER_TYPE_SATA' => 'SATA',
                );
                
                $disk_bus = $this->getControllerTypeDesArr($disk_bus);
                
                //网卡控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $network_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_E1000' => 'e1000',
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => 'virtio',
                    'VM_MIDDLE_CONTROLLER_TYPE_RTL8139' => 'rtl8139',
                );
                $network_bus = $this->getControllerTypeDesArr($network_bus);
                break;
                
            //OpenStack
            case $hypervisorConf['VM_HYPERVISOR_TYPE_FLEX_CLOUD_KVM']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_OPENSTACK_KVM']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_FLEX_HCS_KVM']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_OPENSTACK']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_SUGON_CLOOUD_OPENSTACK']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_INSPUR_CLOUD_PLATFORM']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_EASYSTACK']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_FIBERHOME_OPENSTACK']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_CTSI_OPENSTACK']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_AW_CLOUD']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_HUAWEI_CLOUD_STACK']:
                //显示控制
                $control = array(
                    'other_nav' => true,                //其他,横选项
                    'vm_version' => false,               //虚拟机版本
                    'boot_type' => false,               //引导模式
                    'available_domain' => true,        //可用域
                    'root_pass' => false,               //root密码设置
                    'des_dir' => false,                  //目标资源池/目录
                    'mirror_image' => false,            //镜像
                    'HA' => false,                      //ha
                    'virtual_type' => false,            //虚拟化类型
                    'cpu_general' => false,              //cpu,插槽0和核心显示
                    'cpu_openstack' => true,              //cpu,OpenStack特殊显示,显示vcpu
                    'cpu_xsky_zstack' => false,         //cpu,zstack和xsky特殊显示,显示xzcpu
                    
                    'disk_high_config' => true,         //磁盘是否显示高级配置
                    'disk_name_modify_flag' => false,   //磁盘名称是否可以修改
                    'disk_setting_mode' => false,        //磁盘置备模式
                    'disk_bus_type' => true,            //磁盘总线类型
                    'disk_cluster_size' => false,        //磁盘分块大小
                    'disk_type_hyperv' => false,         //磁盘类型，hyperv特殊显示，显示disktypehyperv
                    
                    'network_high_config' => true,      //网络是否显示高级配置
                    'network_bus_type' => true,         //网卡类型
                    'network_ip_setting' => false,       //网卡IP设置
                    
                    'os_type' => true,               //操作系统类型
                    'image_metadata' => true,          //镜像元数据，OpenStack特殊显示
                );
            if ($hypervisorConf['VM_HYPERVISOR_TYPE_HUAWEI_CLOUD_STACK'] == $hypervisor) {
                // 华为云stack在插件中隐藏可用域
                $control['available_domain'] = false;
            }
                
                //磁盘控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $disk_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => 'virtio',
                    'VM_MIDDLE_CONTROLLER_TYPE_SCSI' => 'SCSI',
                    'VM_MIDDLE_CONTROLLER_TYPE_UML' => 'uml',
                    'VM_MIDDLE_CONTROLLER_TYPE_XEN' => 'xen',
                    'VM_MIDDLE_CONTROLLER_TYPE_IDE' => 'ide',
                    'VM_MIDDLE_CONTROLLER_TYPE_USB' => 'USB',
                    'VM_MIDDLE_CONTROLLER_TYPE_FDC' => 'fdc',
                    'VM_MIDDLE_CONTROLLER_TYPE_SATA' => 'sata',
                    'VM_MIDDLE_CONTROLLER_TYPE_LXC' => 'lxc',
                );
                $disk_bus = $this->getControllerTypeDesArr($disk_bus);
                
                //网卡控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $network_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => 'virtio',
                    'VM_MIDDLE_CONTROLLER_TYPE_E1000' => 'e1000',
                    'VM_MIDDLE_CONTROLLER_TYPE_E1000E' => 'e1000e',
                    'VM_MIDDLE_CONTROLLER_TYPE_NE2K_PCI' => 'ne2k_pci',
                    'VM_MIDDLE_CONTROLLER_TYPE_NETFRONT' => 'netfront',
                    'VM_MIDDLE_CONTROLLER_TYPE_PCNET' => 'pcnet',
                    'VM_MIDDLE_CONTROLLER_TYPE_RTL8139' => 'etl8139',
                    'VM_MIDDLE_CONTROLLER_TYPE_SPAPR_VLAN' => 'spapr_vlan',
                );
                $network_bus = $this->getControllerTypeDesArr($network_bus);
                
                //可用域
                $available_domain = array();
                
                
                break;
                
            //噢易
            case $hypervisorConf['VM_HYPERVISOR_TYPE_OS_EASY_V_SERVER']:
                //显示控制
                $control = array(
                    'other_nav' => false,                //其他,横选项
                    'vm_version' => false,               //虚拟机版本
                    'boot_type' => false,               //引导模式
                    'available_domain' => false,        //可用域
                    'root_pass' => false,               //root密码设置
                    'des_dir' => false,                  //目标资源池/目录
                    'mirror_image' => false,            //镜像
                    'HA' => false,                      //ha
                    'virtual_type' => false,            //虚拟化类型
                    'cpu_general' => true,              //cpu,插槽和核心显示
                    'cpu_openstack' => false,              //cpu,OpenStack特殊显示,显示vcpu
                    'cpu_xsky_zstack' => false,         //cpu,zstack和xsky特殊显示,显示xzcpu
                    
                    'disk_high_config' => true,         //磁盘是否显示高级配置
                    'disk_name_modify_flag' => true,   //磁盘名称是否可以修改
                    'disk_setting_mode' => false,        //磁盘置备模式
                    'disk_bus_type' => false,            //磁盘总线类型
                    'disk_cluster_size' => false,        //磁盘分块大小
                    'disk_type_hyperv' => false,         //磁盘类型，hyperv特殊显示，显示disktypehyperv
                    
                    'network_high_config' => true,      //网络是否显示高级配置
                    'network_bus_type' => false,         //网卡类型
                    'network_ip_setting' => false,       //网卡IP设置
                    'os_type'=>true,                    //跨平台恢复是否支持操作系统选择
                    'image_metadata' => false,          //镜像元数据，OpenStack特殊显示
                );
                //磁盘控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $disk_bus = array();
                
                //网卡控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $network_bus = array();
                break;
                
            //Zstack,CloudView KVM
            case $hypervisorConf['VM_HYPERVISOR_TYPE_ZSTACK']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_CLOUDVIEW_KVM']:
                //显示控制
                $control = array(
                    'other_nav' => false,                //其他,横选项
                    'vm_version' => false,               //虚拟机版本
                    'boot_type' => false,               //引导模式
                    'available_domain' => false,        //可用域
                    'root_pass' => false,               //root密码设置
                    'des_dir' => false,                  //目标资源池/目录
                    'mirror_image' => false,            //镜像
                    'HA' => false,                      //ha
                    'virtual_type' => false,            //虚拟化类型
                    'cpu_general' => false,              //cpu,插槽和核心显示
                    'cpu_openstack' => false,              //cpu,OpenStack特殊显示,显示vcpu
                    'cpu_xsky_zstack' => true,         //cpu,zstack和xsky特殊显示,显示xzcpu

                    'disk_high_config' => true,         //磁盘是否显示高级配置
                    'disk_name_modify_flag' => true,   //磁盘名称是否可以修改
                    'disk_setting_mode' => false,        //磁盘置备模式
                    'disk_bus_type' => true,            //磁盘总线类型
                    'disk_cluster_size' => false,        //磁盘分块大小
                    'disk_type_hyperv' => false,         //磁盘类型，hyperv特殊显示，显示disktypehyperv

                    'network_high_config' => true,      //网络是否显示高级配置
                    'network_bus_type' => true,         //网卡类型
                    'network_ip_setting' => false,       //网卡IP设置
                    'os_type'=>true,                    //跨平台恢复是否支持操作系统选择
                    'image_metadata' => false,          //镜像元数据，OpenStack特殊显示
                );
                //磁盘控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $disk_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_IDE' => Xphp::$_lang['UI_PLATFORM_NORMAL_CLOUD_DISK'],
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => Xphp::$_lang['UI_PLATFORM_VIR_DISK'],
                );

                $disk_bus = $this->getControllerTypeDesArr($disk_bus);

                //网卡控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $network_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => 'VirtIO',
                    'VM_MIDDLE_CONTROLLER_TYPE_E1000' => 'e1000',
                    'VM_MIDDLE_CONTROLLER_TYPE_RTL8139' => 'rtl8139'
                );
                $network_bus = $this->getControllerTypeDesArr($network_bus);

                //可用域
                $available_domain = array();

                //目标目录
                $des_dir = $this->getDesDir($hostuuid);

                break;

            case $hypervisorConf['VM_HYPERVISOR_TYPE_XSKY']:
                //显示控制
                $control = array(
                    'other_nav' => false,                //其他,横选项
                    'vm_version' => false,               //虚拟机版本
                    'boot_type' => false,               //引导模式
                    'available_domain' => false,        //可用域
                    'root_pass' => false,               //root密码设置
                    'des_dir' => false,                  //目标资源池/目录
                    'mirror_image' => false,            //镜像
                    'HA' => false,                      //ha
                    'virtual_type' => false,            //虚拟化类型
                    'cpu_general' => false,              //cpu,插槽和核心显示
                    'cpu_openstack' => false,              //cpu,OpenStack特殊显示,显示vcpu
                    'cpu_xsky_zstack' => true,         //cpu,zstack和xsky特殊显示,显示xzcpu
                    
                    'disk_high_config' => true,         //磁盘是否显示高级配置
                    'disk_name_modify_flag' => true,   //磁盘名称是否可以修改
                    'disk_setting_mode' => false,        //磁盘置备模式
                    'disk_bus_type' => true,            //磁盘总线类型
                    'disk_cluster_size' => false,        //磁盘分块大小
                    'disk_type_hyperv' => false,         //磁盘类型，hyperv特殊显示，显示disktypehyperv
                    
                    'network_high_config' => true,      //网络是否显示高级配置
                    'network_bus_type' => false,         //网卡类型
                    'network_ip_setting' => false,       //网卡IP设置
                    'os_type'=>true,                    //跨平台恢复是否支持操作系统选择
                    'image_metadata' => false,          //镜像元数据，OpenStack特殊显示
                );
                //磁盘控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $disk_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_IDE' => Xphp::$_lang['UI_PLATFORM_NORMAL_CLOUD_DISK'],
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => Xphp::$_lang['UI_PLATFORM_VIR_DISK'],
                );
                
                $disk_bus = $this->getControllerTypeDesArr($disk_bus);
                
                //网卡控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $network_bus = array(
                    
                );
                $network_bus = $this->getControllerTypeDesArr($network_bus);
                
                //可用域
                $available_domain = array();
                
                //目标目录
                $des_dir = $this->getDesDir($hostuuid);
                
                break;
                
            //深信服HCI/VVDK
            case $hypervisorConf['VM_HYPERVISOR_TYPE_SANGFOR_KVM']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_SANGFOR_VVDK_KVM']:
                //显示控制
                $control = array(
                    'other_nav' => true,                //其他,横选项
                    'vm_version' => false,               //虚拟机版本
                    'boot_type' => false,               //引导模式
                    'available_domain' => false,        //可用域
                    'root_pass' => false,               //root密码设置
                    'des_dir' => false,                  //目标资源池/目录
                    'mirror_image' => false,            //镜像
                    'HA' => true,                      //ha
                    'virtual_type' => false,            //虚拟化类型
                    'cpu_general' => true,              //cpu,插槽和核心显示
                    'cpu_openstack' => false,              //cpu,OpenStack特殊显示,显示vcpu
                    'cpu_xsky_zstack' => false,         //cpu,zstack和xsky特殊显示,显示xzcpu
                    
                    'disk_high_config' => true,         //磁盘是否显示高级配置
                    'disk_name_modify_flag' => false,   //磁盘名称是否可以修改
                    'disk_setting_mode' => false,        //磁盘置备模式
                    'disk_bus_type' => true,            //磁盘总线类型
                    'disk_cluster_size' => false,        //磁盘分块大小
                    'disk_type_hyperv' => false,         //磁盘类型，hyperv特殊显示，显示disktypehyperv
                    
                    'network_high_config' => true,      //网络是否显示高级配置
                    'network_bus_type' => true,         //网卡类型
                    'network_ip_setting' => false,       //网卡IP设置
                    'os_type'=>true,                    //跨平台恢复是否支持操作系统选择
                    'image_metadata' => false,          //镜像元数据，OpenStack特殊显示
                );
                //磁盘控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $disk_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_IDE' => Xphp::$_lang['UI_PLATFORM_NORMAL_DISK'],
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => Xphp::$_lang['UI_PLATFORM_VIR_DISK'],
                );
                $disk_bus = $this->getControllerTypeDesArr($disk_bus);
                
                //网卡控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $network_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => 'Virtio',			// virtio
                    'VM_MIDDLE_CONTROLLER_TYPE_E1000' => 'e1000',
                    'VM_MIDDLE_CONTROLLER_TYPE_RTL8139' => 'rtl8139',
                );
                $network_bus = $this->getControllerTypeDesArr($network_bus);
                
                //目标目录
                $des_dir = array();
                break;
                
            //Hyper-v
            case $hypervisorConf['VM_HYPERVISOR_TYPE_HYPERV']:
                //显示控制
                $control = array(
                    'other_nav' => false,                //其他,横选项
                    'vm_version' => false,               //虚拟机版本
                    'boot_type' => false,               //引导模式
                    'available_domain' => false,        //可用域
                    'root_pass' => false,               //root密码设置
                    'des_dir' => false,                  //目标资源池/目录
                    'mirror_image' => false,            //镜像
                    'HA' => false,                      //ha
                    'virtual_type' => false,            //虚拟化类型
                    'cpu_general' => false,              //cpu,插槽和核心显示
                    'cpu_openstack' => false,              //cpu,OpenStack特殊显示,显示vcpu
                    'cpu_xsky_zstack' => false,         //cpu,zstack和xsky特殊显示,显示xzcpu
                    'cpu_hyperv' => true,           //cpu,hyperv特殊显示,显示hypervcpu
                    
                    'disk_high_config' => true,         //磁盘是否显示高级配置
                    'disk_name_modify_flag' => false,   //磁盘名称是否可以修改
                    'disk_setting_mode' => false,        //磁盘置备模式
                    'disk_bus_type' => false,            //磁盘总线类型
                    'disk_cluster_size' => false,        //磁盘分块大小
                    'disk_type_hyperv' => true,         //磁盘类型，hyperv特殊显示，显示disktypehyperv
                    
                    'network_high_config' => true,      //网络是否显示高级配置
                    'network_bus_type' => false,         //网卡类型
                    'network_ip_setting' => false,       //网卡IP设置
                    'os_type'=>true,                    //跨平台恢复是否支持操作系统选择
                    'image_metadata' => false,          //镜像元数据，OpenStack特殊显示
                );
                //磁盘控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                //针对hyper-v特殊处理 因为还使用的老版本的数据 所以数据格式按照老版本来,把磁盘类型的值存在磁盘总线类型里
                $disk_bus = array(
                    array(
                        'key' =>0,
                        'value' => Xphp::$_lang['UI_PLATFORM_ORIGINNAL_CONF'],
                    ),
                    array(
                        'key' =>1,
                        'value' => Xphp::$_lang['UI_PLATFORM_VHD_FIXED'],
                    ),
                    array(
                        'key' =>2,
                        'value' => Xphp::$_lang['UI_PLATFORM_VHD_DYNAMIC'],
                    ),
                    array(
                        'key' =>3,
                        'value' => Xphp::$_lang['UI_PLATFORM_VHDX_FIXED'],
                    ),
                    array(
                        'key' =>4,
                        'value' => Xphp::$_lang['UI_PLATFORM_VHDX_DYNAMIC'],
                    ),
                );
                
                //网卡控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $network_bus = array();
                break;

            case $hypervisorConf['VM_HYPERVISOR_TYPE_XHERE']:
                //显示控制
                $control = array(
                    'other_nav' => false,                //其他,横选项, 如果引导模式,可用域,root密码设置,目标资源池/目录,镜像,ha,虚拟化类型全部是false,这里就是false,表示界面不显示其他
                    'vm_version' => false,               //虚拟机版本
                    'boot_type' => false,               //引导模式
                    'available_domain' => false,        //可用域
                    'root_pass' => false,               //root密码设置
                    'des_dir' => false,                  //目标资源池/目录
                    'mirror_image' => false,            //镜像
                    'HA' => false,                      //ha
                    'virtual_type' => false,            //虚拟化类型

                    'cpu_general' => true,              //cpu,插槽和核心显示
                    'cpu_openstack' => false,           //cpu,OpenStack特殊显示,显示vcpu
                    'cpu_xsky_zstack' => false,         //cpu,zstack和xsky特殊显示,显示xzcpu

                    'disk_high_config' => true,         //磁盘是否显示高级配置
                    'disk_name_modify_flag' => true,   //磁盘名称是否可以修改
                    'disk_setting_mode' => false,        //磁盘置备模式
                    'disk_bus_type' => true,            //磁盘总线类型
                    'disk_cluster_size' => false,       //磁盘分块大小
                    'disk_type_hyperv' => false,         //磁盘类型，hyperv特殊显示，显示disktypehyperv
                    'disk_type_xhere' => true,          //磁盘块存储策略，xhere特殊显示

                    'network_high_config' => true,      //网络是否显示高级配置
                    'network_bus_type' => true,         //网卡类型
                    'network_ip_setting' => false,      //网卡IP设置
                    'os_type'=>true,                    //跨平台恢复是否支持操作系统选择
                    'image_metadata' => false,          //镜像元数据，OpenStack特殊显示
                );

                //磁盘控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $disk_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_IDE' => 'IDE',				// IDE
                    'VM_MIDDLE_CONTROLLER_TYPE_SCSI' => 'SCSI',				// SCSI
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => 'Virtio',			// virtio
                );
                $disk_bus = $this->getControllerTypeDesArr($disk_bus);

                //网卡控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $network_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => 'Virtio',			// virtio
                    'VM_MIDDLE_CONTROLLER_TYPE_E1000' => 'E1000',			// e1000
                    'VM_MIDDLE_CONTROLLER_TYPE_SRIOV' => 'VF',			// sriov
                );
                $network_bus = $this->getControllerTypeDesArr($network_bus);

                //目标目录
                $des_dir = $this->getDesDir($hostuuid);
                break;
        }
        $control['data_encrypt'] = false;           //数据加密校验
        $info = array(
            'control' => $control,
            'disk_bus' => $disk_bus,
            'network_bus' => $network_bus,
            'available_domain' => $available_domain,
            'des_dir' => $des_dir,
            'mirror_image' => $mirror_image,
            'maintain_model' => $maintain_model,  //置备模式
        );
        return $info;
        
    }
    
    /**
     * 得到宿主机下的所有目录(排除虚拟机)
     * @param unknown $hostuuid
     */
    private function getDesDir($hostuuid){
        //先获取宿主机信息,主要是取到dir_path,然后通过dir_path去查找,只查找主机和集群模式
        $sql = "select dir_path from vm_tree where host_uuid = ? and type = ? and display_mode = ?";
        $sqlParams = array($hostuuid, Xphp::$_config['VM_TREE_TYPE']['HOST'], Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER']);
        $data = $this->dbSelect($sql, $sqlParams);
        $dirPath = $data[0]['dir_path'];
        
        //获取所有宿主机下的目录
        $sql = "select uuid, dir_path from vm_tree where dir_path like '%" . $dirPath . "%' and display_mode = ? and type != ?";
        $sqlParams = array(Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER'], Xphp::$_config['VM_TREE_TYPE']['VM']);
        $data = $this->dbSelect($sql, $sqlParams);
        
        $dirArr = array();
        foreach ($data as $d){
            $dirArr[] = array(
                'key' => $d['uuid'],
                'value' => $d['dir_path']
            );
        }
        return $dirArr;
    }
    
    /**
     * 得到RHV/Ovirt/OLVM/易讯通等的磁盘控制器类型,
     * <4.4：IDE/VIRTIO/VIRTIO-SCSI
     * >=4.4：SATA/VIRTIO/VIRTIO-SCSI
     * @param unknown $vcenteruuid
     */
    private function getRhvOvirtDiskBus($vcenteruuid){
        $bus_type = array();
        $sql = "select version from vm_vcenter where vcenter_uuid = ?";
        $data = $this->dbSelect($sql, array($vcenteruuid));
        
        $version = $data[0]['version'];
        if(substr($version,0,3) < "4.4"){
            $bus_type = array(
                'VM_MIDDLE_CONTROLLER_TYPE_IDE' => 'IDE',
                'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => 'VirtIO',
                'VM_MIDDLE_CONTROLLER_TYPE_VSCSI' => 'VirtIO-SCSI',
            );
        }else{
            $bus_type = array(
                'VM_MIDDLE_CONTROLLER_TYPE_IDE' => 'IDE',
                'VM_MIDDLE_CONTROLLER_TYPE_SATA' => 'SATA',
                'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => 'VirtIO',
                'VM_MIDDLE_CONTROLLER_TYPE_VSCSI' => 'VirtIO-SCSI',
                'VM_MIDDLE_CONTROLLER_TYPE_SCSI' => 'SCSI'
            );
        }
        return $bus_type;
    }
    
    /**
     * 得到控制器描述数组
     * @param unknown $typeArr
     */
    private function getControllerTypeDesArr($typeArr){
        $vmConfig = include XPHP_PATH.'conf/vm_config.php';
        
        $vmMiddleControllerType = $vmConfig['VmMiddleControllerType'];
        $vmMiddleControllerTypeDes = $vmConfig['VmMiddleControllerTypeDes'];
        $info = array();

        foreach ($typeArr as $key => $value){
            $confNum = array_search($key, $vmMiddleControllerType);
            if(empty($value)){
                //如果没有键值,就使用配置文件默认配置的名称
                $typeDes = $vmMiddleControllerTypeDes[$confNum];
            }else{
                //如果有键值,就是用这个键值
                $typeDes = $value;
            }
            $info[] = array(
                'key' => $confNum,
                'value' => $typeDes
            );
        }
        return $info;
    }
    
    /**
     * 组合处理恢复虚拟机的网卡数据,适应后台数据结构
     * @param unknown $networkList
     */
    public function processRecoveryVmNetwork($networkList){
        foreach ($networkList as $key => $value){
            if(empty($value['ipaddr'])){
                $ipv4_list = array();
            }else{
                $ipv4_list = explode(",", $value['ipaddr']);
            }
            $networkList[$key]['ipv4_list'] = $ipv4_list;
            $networkList[$key]['ipv6_list'] = array();  //保留字段,设置成空数组
            
        }
        return $networkList;
    }
    
    /**
     * 组合处理恢复虚拟机的磁盘数据,适应后台数据结构
     * @param unknown $networkList
     */
    public function processRecoveryVmDisk($diskList){
        foreach ($diskList as $key => $value){
            $diskList[$key]['src_disk_uuid'] = base64_decode($value['src_disk_uuid']);
            $diskList[$key]['size'] = $value['size'] ;
            $diskList[$key]['cache_type'] = 0;  //缓存类型,保留字段
            $diskList[$key]['preallocated_type'] = 0;  // 预分配，针对vmware等,保留字段
            $diskList[$key]['policy_id'] = $value['policy_id'] ?: ''; //xhere块存储策略id

            // 处理hyper-v路径和uuid带"\"
            $diskList[$key]['disk_name'] = $value['disk_name'] ? str_replace('\\\\', '\\', $value['disk_name']) : '';
            $diskList[$key]['target_storage_uuid'] = $value['target_storage_uuid'] ? str_replace('\\\\', '\\', $value['target_storage_uuid']) : '';
        }
        return $diskList;
    }
    
    /**
     * 组合处理恢复虚拟机的可用域,适应后台数据结构
     * @param unknown $domain
     */
    public function processRecoveryVmZoneConfig($domain){
        if(empty($domain)){
            $zoneConfig = array(
                'zone_name' => '',
                'auto_conf_flag' => Xphp::$_config['FLAG']['SET'],
            );
        }else {
            $zoneConfig = array(
                'zone_name' => $domain,
                'auto_conf_flag' => Xphp::$_config['FLAG']['UNSET'],
            );
        }
        return $zoneConfig;
    }
    
    /**
     * 组合处理恢复虚拟机的其他配置,适应后台数据结构
     * @param unknown $networkList
     */
    public function processRecoveryVmOtherConfig($other){
        $config = array(
            'root_password' => $other['root_pass_input'],               //root密码
            'target_resource_pool_or_dir' => $other['des_dir_select'],  //恢复目标池或者目录
            'image_uuid' => $other['mirror_image_select'],              //镜像UUID
            'is_ha' => $other['ha'],                                    //是否设置HA开关，针对ovirt、深信服等
            'is_cluster' => false,                                      //集群开关，预留
            'cpu_type' => "",                                           //cpu类型，针对ovirt，预留
            'emulator_type' => "",                                      //仿真机类型，针对ovirt，预留
            'vm_flavor_id' => "",                                       //虚拟机flavor，预留
            'virtualization_type' => $other['virtual_type_select']      //虚拟化类型,XenServer/XCP-NG/vGate使用,HVM/PV
        );
        return $config;
    }
    
    /**
     * 转化计算单位为数字和单位组合形式
     * @param unknown $num
     * @param string $valueFlag
     * @return mixed|string|number[]|string[]
     */
    public function getSizeToUnit($num) {
        $num = floatval($num);
        $type = array( "B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB", "BB", "NB", "DB");
        $j = 0;
        while($num >= 1024) {
            if( $j >= 11 ) return $num.$type[$j];
            $num = $num / 1024;
            $j++;
        }
        $num = round($num, 2);
        
        $info = array(
            'num' => $num,
            'unit' => $type[$j]
        );
        return $info;
    }
    
    /**
     * 转化数字和单位组合形式为数字
     * @param unknown $num
     * @param unknown $unit
     * @return mixed|string
     */
    public function calUnitToSize($num, $unit) {
        $type = array( "B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB", "BB", "NB", "DB");
        $size = 0;
        foreach ($type as $key => $value){
            if($value == $unit){
                $size = $num * pow(1024, $key) ;
                break;
            }
        }
        return $size;
    }
    
    
    /**
     * 获取时间点是否加密密码
     * @param unknown $config
     */
    private function getVMDataEncrypt($config){
        if(empty($config)) return false;
        if(!empty($config['password']) && $config['password_auto_flag'] == 2){
            return true;
        }
        
        return false;
    }

    /**
     * 获取虚拟机版本是否可选
     * @param $hypervisor
     * @param $vcenteruuid
     * @return bool
     */
    public function getVmVersionSelectEnable($hypervisor, $vcenteruuid) {
        $enable = true;
        if (Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE_KVM'] == $hypervisor
            || Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_VVDK'] == $hypervisor) {
            $enable = false;
        }
        return $enable;
    }
    
}