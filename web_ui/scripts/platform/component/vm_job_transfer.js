/**
 * 虚拟机任务详情 - 传输策略
 */
(function ($) {
    var hypervisor;

    // 任务类型
    const JOB_TYPE_BACKUP = 1; // 备份
    const JOB_TYPE_RECOVERY = 2; // 恢复/跨平台恢复
    const JOB_TYPE_MOTION = 3; // 迁移

    //得到开启和关闭的HTML内容
    const getFlagLevelInfo = function (flag) {
        var html = '<span class="label label-success">' + LANG.UI_PUBLIC_ON + '</span>';
        if (!flag) {
            html = '<span class="label label-warning">' + LANG.UI_PUBLIC_OFF + '</span>';
        }
        return html;
    }

    /**
     * 显示传输策略
     * 注意：以下非传输策略对象中的参数需统一合并到transportStrategy对象中
     * data.thread_num => transportStrategy.thread_num
     * data.applianceDes => transportStrategy.applianceDes
     * data.agent_pool_info => transportStrategy.agent_pool_info
     * data.transport_ip_segment => transportStrategy.transport_ip_segment
     * data.backupNodeIp => transportStrategy.backupNodeIp
     * */
    $.fn.showVmJobTransferStrategy = (transportStrategy, jobType = JOB_TYPE_BACKUP) => {
        hypervisor = transportStrategy.hypervisor;

        $('#threadCount').html(transportStrategy.thread_num);
        if ('unzip' === transportStrategy.transfer_compress) {
            // unzip显示为关闭
            $('#transferCompress').html(getFlagLevelInfo(false));
        } else {
            $('#transferCompress').html(transportStrategy.transfer_compress);
        }
        $('#asyncTransfer').html(getFlagLevelInfo(transportStrategy.async_transfer));
        $('#ptVmCount').html(transportStrategy.parallel_transfer_vm_count);
        $('#ptDiskCount').html(transportStrategy.single_vm_parallel_disk_transfer_count);
        $('#ptSegmentCount').html(transportStrategy.vm_single_disk_parallel_transfer_count);

        //数据传输网段
        $('.ipSegmentDiv').hide();
        if (transportStrategy.transport_ip_segment != "") {
            $('.ipSegmentDiv').show();
            $('#ipSegment').html(transportStrategy.transport_ip_segment);
        }

        $('.parallelTransferDiv').hide(); // 并行传输
        $('.transferCompressDiv').hide(); // 传输压缩
        $('.asyncTransferDiv').hide(); // 异步传输

        // 区分任务类型
        switch (jobType) {
            case JOB_TYPE_BACKUP:
                /***** 备份 *****/
                $('#appliance').html(transportStrategy.applianceDes);
                if (transportStrategy.agent_pool_info.agent_pool_uuid) {
                    $('#agentPool').html(transportStrategy.agent_pool_info.agent_pool_nickname);
                } else {
                    $('#agentPool').html(`--`);
                }

                //传输策略,XenServer显示加密传输,VMware显示传输模式
                $("#transportMode").html(transportStrategy.mode);
                $("#transportEncrypt").html(getFlagLevelInfo(transportStrategy.encrypt));
                // 传输加密算法
                if (transportStrategy.encrypt) {
                    let encryptMethod = transportStrategy.encrypt_method;
                    let method = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_RSA;
                    if (encryptMethod == 2) {
                        method = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_SM;
                    }
                    $('#transportEncrypt').html(method);
                }
                $('.applianceDiv').hide();	// 传输代理
                $('.agentPoolDiv').hide();  // 传输代理资源池

                switch (hypervisor) {
                    case CONF.VM_TYPE.VMWARE:
                    case CONF.VM_TYPE.CLOUDVIEW:
                    case CONF.VM_TYPE.CLOUDVIEWSVM:
                        $('.applianceDiv').show();	//显示传输代理
                        $('.agentPoolDiv').show();  // 显示传输代理资源池
                        if (CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw") {
                            if (9 == CONF.SOFTWARE || 4 == CONF.SOFTWARE) {
                                $('.applianceDiv').hide();			     //essential 和free隐藏代理模块
                                $('.agentPoolDiv').hide();
                            }
                        }
                        let transmode = transportStrategy.mode_index.split(':');
                        if ('nbd' === transmode[1] || 'nbdssl' === transmode[1]) {
                            $('.transferCompressDiv').show(); // 显示传输压缩
                            $('.asyncTransferDiv').show(); // 显示异步传输
                        }
                        break;
                    case CONF.VM_TYPE.HYPERV:
                    case CONF.VM_TYPE.H3C:
                    case CONF.VM_TYPE.H3CCASCVD:
                    case CONF.VM_TYPE.SDCOS:
                    case CONF.VM_TYPE.SANGFOR:
                    case CONF.VM_TYPE.KVM:
                        $('.transportModediv').hide(); //隐藏传输模式
                        break;
                    case CONF.VM_TYPE.FUSIONXEN:
                        $('.transportEncryptdiv').hide();
                        break;
                    case CONF.VM_TYPE.FUSIONKVM:
                    case CONF.VM_TYPE.XFUSIONKVM:
                        //APL_LAN传输不显示加密传输
                        if (parseInt(transportStrategy.mode_index) == 5) {
                            $('.transportEncryptdiv').hide();
                        }
                        if (CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw") {
                            $('.silentSnapshotdiv .name').html(LANG.UI_BACKUP_HUAWEI_KVM_SILENTSNAPSHOT + ':');
                            //显示备份节点IP
                            let str = '';
                            $.each(transportStrategy.backupNodeIp, function (i, v) {
                                str += v.node_ip + ' - ' + v.transfer_ip + "<br>";
                            })
                            $('#baknodeIp').html(str);
                            $('.baknodeIpDiv').show();
                        } else {
                            $('.silentSnapshotdiv .name').html(LANG.UI_BACKUP_HUAWEI_KVM_SILENTSNAPSHOT);
                        }

                        $('.threadCountDiv').hide();
                        $('.parallelTransferDiv').show();
                        break;
                    case CONF.VM_TYPE.RHV:
                    case CONF.VM_TYPE.OVIRT:
                    case CONF.VM_TYPE.OLVM:
                    case CONF.VM_TYPE.ZVIRT:
                    case CONF.VM_TYPE.HOSTVM:
                    case CONF.VM_TYPE.REDVIRT:
                    case CONF.VM_TYPE.ROSAVIRT:
                        //不显示加密传输
                        if (parseInt(transportStrategy.mode_index) == 5) {
                            $('.transportEncryptdiv').hide();
                        }
                        break;
                    case CONF.VM_TYPE.SMARTX:
                    case CONF.VM_TYPE.ARCFRA:
                    case CONF.VM_TYPE.INSPURVVDK:
                    case CONF.VM_TYPE.KSPHERE:
                    case CONF.VM_TYPE.FLEXCLOUD:
                    case CONF.VM_TYPE.OPENSTACK:
                    case CONF.VM_TYPE.FLEXHCS:
                    case CONF.VM_TYPE.INCLOUDOPENSTACK:
                    case CONF.VM_TYPE.SUGONCLOUDVIEW: //34
                    case CONF.VM_TYPE.INSPURCLOUDPLATFORM: //35
                    case CONF.VM_TYPE.EASYSTACK: //36
                    case CONF.VM_TYPE.FIBERHOMEOPENSTACK: //37
                    case CONF.VM_TYPE.CTSIOPENSTACK: //38
                    case CONF.VM_TYPE.AWCLOUD: //39
                    case CONF.VM_TYPE.HUAWEICLOUDSTACK: //54
                    case CONF.VM_TYPE.SANGFORVVDK:
                    case CONF.VM_TYPE.LENOVOAIO:
                    case CONF.VM_TYPE.VOLC:
                        if (3 == transportStrategy.mode_index) {
                            $('.applianceDiv').show();	//显示传输代理
                            $('.agentPoolDiv').show();  // 显示传输代理资源池
                        }
                        break;
                }

                if (CONF.VM_TYPE.SMARTX == hypervisor
                    || CONF.VM_TYPE.INSPURVVDK == hypervisor
                    || CONF.VM_TYPE.SANGFORVVDK == hypervisor
                    || CONF.VM_TYPE.VOLC == hypervisor
                    || CONF.VM_TYPE.KSPHERE == hypervisor
                    || CONF.VM_TYPE.ARCFRA == hypervisor
                ) {
                    // smartx/ics vvdk/scp仅传输代理模式显示加密传输
                    if (parseInt(transportStrategy.mode_index) == 3) {
                        $('.transportEncryptdiv').show();
                    } else {
                        $('.transportEncryptdiv').hide();
                        $('.transfer-encrypt-method-div').hide();
                    }
                } else if (CONF.VM_TYPE.VMWARE == hypervisor) {
                    // vmware有传输代理则显示加密传输
                    if (transportStrategy.applianceFlag) {
                        $('.transportEncryptdiv').show();
                    } else {
                        $('.transportEncryptdiv').hide();
                        $('.transfer-encrypt-method-div').hide();
                    }
                } else {
                    //除smartx/ics vvdk/scp外如果不是网络传输不显示加密传输
                    if (parseInt(transportStrategy.mode_index) != 1) {
                        $('.transportEncryptdiv').hide();
                        $('.transfer-encrypt-method-div').hide();
                    }
                }
                $('.recoveryDiv').hide();	//隐藏恢复模块
                break;
            case JOB_TYPE_RECOVERY:
            case JOB_TYPE_MOTION:
                /***** 恢复 *****/
                $('#applianceRec').html(transportStrategy.applianceDes);
                if (transportStrategy.agent_pool_info.agent_pool_uuid) {
                    $('#agentPoolRec').html(transportStrategy.agent_pool_info.agent_pool_nickname);
                } else {
                    $('#agentPoolRec').html(`--`);
                }

                $('.backup_style').hide();
                $('#recoveryTransport').html(transportStrategy.mode);	//恢复传输策略
                $("#recoveryEncrypt").html(getFlagLevelInfo(transportStrategy.encrypt));
                // 传输加密算法
                if (transportStrategy.encrypt) {
                    let encryptMethod = transportStrategy.encrypt_method;
                    let method = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_RSA;
                    if (encryptMethod == 2) {
                        method = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_SM;
                    }
                    $('#recoveryEncrypt').html(method);
                }
                $('.recoveryDiv').show();
                $('.applianceRecDiv').hide(); //隐藏Appliance
                $('.agentPoolRecDiv').hide(); // 隐藏代理池

                switch (hypervisor) {
                    case CONF.VM_TYPE.VMWARE:
                    case CONF.VM_TYPE.CLOUDVIEW:
                    case CONF.VM_TYPE.CLOUDVIEWSVM:
                        //隐藏加密传输
                        $('.recoveryEncryptdiv').show();
                        $('.applianceRecDiv').show(); //显示Appliance
                        $('.agentPoolRecDiv').show(); // 显示代理池
                        let transmode = transportStrategy.mode_index.split(':');
                        if ('nbd' === transmode[1] || 'nbdssl' === transmode[1]) {
                            $('.transferCompressDiv').show(); // 显示传输压缩
                        }
                        break;
                    case CONF.VM_TYPE.HYPERV:
                        $('.threadCountDiv').hide();	//隐藏线程量
                        break;
                    case CONF.VM_TYPE.FUSIONXEN:
                        //隐藏加密传输
                        $('.recoveryEncryptdiv').hide();
                        $('.threadCountDiv').hide();	//隐藏线程量
                        break;
                    case CONF.VM_TYPE.FLEXCLOUD:
                    case CONF.VM_TYPE.OPENSTACK:
                    case CONF.VM_TYPE.FLEXHCS:
                    case CONF.VM_TYPE.INCLOUDOPENSTACK:
                    case CONF.VM_TYPE.SUGONCLOUDVIEW: //34
                    case CONF.VM_TYPE.INSPURCLOUDPLATFORM: //35
                    case CONF.VM_TYPE.EASYSTACK: //36
                    case CONF.VM_TYPE.FIBERHOMEOPENSTACK: //37
                    case CONF.VM_TYPE.CTSIOPENSTACK: //38
                    case CONF.VM_TYPE.AWCLOUD: //39
                    case CONF.VM_TYPE.HUAWEICLOUDSTACK: //54
                        $('.applianceRecDiv').show(); //显示Appliance
                        $('.agentPoolRecDiv').show(); // 显示代理池
                        break;
                    case CONF.VM_TYPE.RHV:
                    case CONF.VM_TYPE.OVIRT:
                    case CONF.VM_TYPE.OLVM:
                    case CONF.VM_TYPE.ZSTACK:
                    case CONF.VM_TYPE.NEXAVMNCSSV:
                    case CONF.VM_TYPE.XSKY:
                    case CONF.VM_TYPE.WINHONGKVM:
                    case CONF.VM_TYPE.PROXMOX:
                    case CONF.VM_TYPE.ZVIRT:
                    case CONF.VM_TYPE.HOSTVM:
                    case CONF.VM_TYPE.CLOUDVIEWKVM:
                    case CONF.VM_TYPE.XHERE:
                    case CONF.VM_TYPE.REDVIRT:
                    case CONF.VM_TYPE.ROSAVIRT:
                        //仅网络传输显示加密传输，迁移无加密传输
                        if (parseInt(transportStrategy.mode_index) != 1 || JOB_TYPE_MOTION == jobType) {
                            $('.recoveryEncryptdiv').hide();
                        } else {
                            $('.recoveryEncryptdiv').show();
                        }
                        break;
                    case CONF.VM_TYPE.ZSTACKZSPHERE:
                    case CONF.VM_TYPE.NEXAVM:
                        //仅网络传输显示加密传输
                        if (parseInt(transportStrategy.mode_index) != 1) {
                            $('.recoveryEncryptdiv').hide();
                        } else {
                            $('.recoveryEncryptdiv').show();
                        }
                        break;
                    case CONF.VM_TYPE.FUSIONKVM:
                    case CONF.VM_TYPE.XFUSIONKVM:
                        //仅网络传输显示加密传输
                        if (parseInt(transportStrategy.mode_index) != 1) {
                            $('.recoveryEncryptdiv').hide();
                        } else {
                            $('.recoveryEncryptdiv').show();
                        }
                        if (JOB_TYPE_RECOVERY == jobType) {
                            $('.parallelTransferDiv').show();
                            $('.ptVmCountDiv').hide();
                            $('.threadCountDiv').hide();
                        } else if (JOB_TYPE_MOTION == jobType) {
                            $('.parallelTransferDiv').hide();
                            $('.threadCountDiv').show();
                        }
                        break;
                    case CONF.VM_TYPE.SMARTX:
                    case CONF.VM_TYPE.ARCFRA:
                    case CONF.VM_TYPE.INSPURVVDK:
                    case CONF.VM_TYPE.SANGFORVVDK:
                    case CONF.VM_TYPE.VOLC:
                    case CONF.VM_TYPE.KSPHERE:
                        // 仅传输代理模式显示加密传输
                        if (parseInt(transportStrategy.mode_index) == 3) {
                            $('.recoveryEncryptdiv').show();
                            $('.applianceRecDiv').show(); //显示Appliance
                            $('.agentPoolRecDiv').show(); // 显示代理池
                        } else {
                            $('.recoveryEncryptdiv').hide();
                            $('.recovery-transfer-encrypt-method-div').hide(); // 迁移用
                        }
                        break;
                    case CONF.VM_TYPE.SANGFOR:
                        // 隐藏传输模式
                        $('.recoveryTransportDiv').hide();
                        // 迁移无加密传输
                        if (JOB_TYPE_MOTION == jobType) {
                            $('.recoveryEncryptdiv').hide();
                        } else {
                            $('.recoveryEncryptdiv').show();
                        }
                        break;
                }

                //数据传输网段
                if (transportStrategy.transport_ip_segment != "") {
                    $('.reIpSegmentDiv').show();
                    $('#reIpSegment').html(transportStrategy.transport_ip_segment);
                } else {
                    $('.reIpSegmentDiv').hide();
                }
                break;
        }
    }
})(jQuery)