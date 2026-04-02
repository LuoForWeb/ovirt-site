<!-- 在恢复配置外层容器的div中引入该文件 -->
<script type="text/javascript" src="./assets/global/plugins/ace/src-min-noconflict/ace.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/select2/select2-4.1.0/js/select2.min.js"></script>
<script type="text/javascript" src="./scripts/platform/component/network_config.js"></script>
<script type="text/javascript" src="./scripts/platform/component/vinScript.js"></script>
<script type="text/javascript" src="./scripts/platform/component/targetPlug.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.vmRecoveryConfig.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.vmConfigValidate.js"></script>
<script>
    /**
     * 虚拟机恢复配置
     * 恢复/瞬时恢复 从getVMConfigInfoV2接口获取
     * 迁移 从getMotionVMConfigsV2接口获取
     * 参数:
     *      instantFlag: 瞬时恢复标志
     *      vmotionFlag: 迁移标志
     *      old_hypervisor: 源虚拟化类型，有代理传0
     *      hypervisor: 目标虚拟化类型
     *      volume_policy: [可选]xhere块存储策略，选中xhere目标主机时从getXhereVolumePolicyList获取的
     *      disk_bus: 磁盘总线类型
     *      network_bus: 网卡总线类型
     *      des_dir: [可选]目标目录
     *      mirror_image: [可选]镜像
     *      config: 每个时间点的配置
     *      control: 目标虚拟化的显示控制配置，从getVMRecoveryControlContent方法获取
     *      vmNameLimit: 虚拟机名限制规则
     */
    // var params = {
    //     instantFlag: false,
    //     old_hypervisor: 1,
    //     hypervisor: 3,
    //     volume_policy: [
    //         {
    //             burst_total_bps: "0"
    //             burst_total_iops: "99999999"
    //             crc_check: false
    //             max_total_bps: "0"
    //             max_total_iops: "99999999"
    //             name: "test-by-tian"
    //             uuid: "2"
    //         }
    //     ],
    //     disk_bus: [{key: 1, value: "IDE"}, {key: 2, value: "SCSI"}, {key: 4, value: "Virtio"}],
    //     network_bus: [{key: 4, value: "Virtio"}, {key: 6, value: "E1000"}, {key: 20, value: "VF"}],
    //     des_dir: [{key: 1, value: "abc"}],
    //     mirror_image: [{key: 1, value: "abc"}],
    //     config: [
    //         {
    //             "boot_mode":"1",
    //             "cores":"1",
    //             "disk_list":[
    //                 {
    //                     "cache_type":"1",
    //                     "controller_type":"1",
    //                     "disk_format":"0",
    //                     "disk_key":"6000c299-9594-4461-8cd4-b868ffa69bac",
    //                     "disk_name":"backup-node_rocky9_(16.31)-000002",
    //                     "disk_type":"1",
    //                     "is_backup_or_recovery":true,
    //                     "is_bootable":true,
    //                     "policy_id":"",
    //                     "source_disk_name":"[raid5-43T] backup-node_rocky9_(16.31)\/backup-node_rocky9_(16.31)-000002.vmdk",
    //                     "virtual_size":"53687091200",
    //                     "virtual_size_unit":"50GB",
    //                     "controller_des":"ide",
    //                     "disk_key_base64":"NjAwMGMyOTktOTU5NC00NDYxLThjZDQtYjg2OGZmYTY5YmFj"
    //                 }
    //             ],
    //             "flavor_id":"",
    //             "memory":"2147483648",
    //             "net_list":[
    //                 {
    //                     "controller_type":"1",
    //                     "mac_address":"00:50:56:95:94:7F",
    //                     "network_name":"VM Network",
    //                     "network_type":"0",
    //                     "subnet_id":"",
    //                     "controller_des":"ide",
    //                     "network_uuid":""
    //                 }
    //             ],
    //             "openstack_start_mode":0,
    //             "os_type":"20000",
    //             "sockets":"1",
    //             "source_hypervisor_type":1,
    //             "video":{
    //                 "memory":"0",
    //                 "video_type":"0"
    //             },
    //             "vm_name":"A1_LYF_BackupNode_603_rocky9_16_31",
    //             "vm_uuid":"42150fe7-56e5-e164-5836-29d4663d79fb",
    //             "vm_version":"",
    //             "target_hypervisor_type":3,
    //             "new_vm_name":"A1_LYF_BackupNode_603_rocky9_16_31_20240730172047",
    //             "memory_array":{
    //                 "num":2,
    //                 "unit":"GB"
    //             },
    //             "data_encrypt":false,
    //             "timepointuuid":"ec2947d4-193e-4fac-b4a2-a3f8f007f88d",
    //             "vm_version_enable":true,
    //             "vm_new_version":"",
    //             "delete_on_termination":false,
    //             "image_metadata":null,
    //             "is_ha":false,
    //             "storage_type":11
    //         }
    //     ],
    //     control: {
    //         HA: true
    //         available_domain: false
    //         boot_type: false
    //         cpu_general: true
    //         cpu_openstack: false
    //         cpu_xsky_zstack: false
    //         data_encrypt: false
    //         des_dir: false
    //         disk_bus_type: false
    //         disk_cluster_size: false
    //         disk_high_config: true
    //         disk_name_modify_flag: true
    //         disk_setting_mode: false
    //         disk_type_hyperv: false
    //         image_metadata: false
    //         mirror_image: false
    //         network_bus_type: false
    //         network_high_config: true
    //         network_ip_setting: false
    //         os_type: true
    //         other_nav: true
    //         root_pass: false
    //         virtual_type: false
    //         vm_version: false
    //     },
    //     vmNameLimit: {
    //         len: "128"
    //         limit: ""
    //         msg: "输入长度最大为128字符"
    //     }
    // }
    // $('#containerDiv').vmRecoveryConfig(params);
    //
    // // 获取配置信息 示例
    // setTimeout(function(){
    //     var info = $('#containerDiv').getvmRecoveryConfig(params);  //根据实际使用情况，调用该方法
    //     console.log(info);
    // },5000);

</script>