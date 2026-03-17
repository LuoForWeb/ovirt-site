/**
 * 恢复到公有云的配置
 */
(function($){
    var instance_configs; // 实例配置的最终参数
    var volume_configs; // 卷配置的最终参数
    var _platformUuid = '', _platformName; //目标云平台uuid和名称
    var _platformData;
    var _currentInstanceConfig = {}; //当前处理的实例配置
    var _currentNetworkConfig = {}; //当前处理的网络配置
    var _currentVolConfig = {}; //当前处理的卷配置
    var _regionInfo = []; //区域信息，通过平台uuid获取 {region_uuid:'', region_name:'', available_zone:['']}
    var initRegionFlag = false;
    var configRegionUuid = ''; //获取配置的区域uuid，用于判断是否需要重新获取
    var configAvailableZone = ''; //获取配置的可用区，用于判断是否需要重新获取
    var configOsType = ''; //获取配置的操作系统，用于判断是否需要重新获取
    var _security = []; //所有安全组，通过区域获取
    var timepoint_uuids = []; //已选时间点uuid
    var currentPasswordFlag = false; //当前实例是否数据加密
    var currentVolPasswordFlag = false; //当前卷是否数据加密
    var _crossFlag = false; //是否跨平台，即虚拟机恢复到公有云
    var crossPlatformFlag; // 跨平台恢复标记，即虚拟化类型不同
    var _VMNAMEREG = new RegExp("[ \n\r\t\f`~!@#$^&*()=|{}':;',\\[\\].<>/?~！@#￥……&*（）&;|{}【】‘；：”“'。，、？+-]");

    //实例配置选择器
    var _IName = $('#drawer-1').find('[name=instance_name]');
    var _IRegion = $('#drawer-1').find('[name=region]');
    var _IAZ = $('#drawer-1').find('[name=az]');
    var _IProject = $('#drawer-1').find('[name=project]');
    var _IType = $('#drawer-1').find('[name=instance_type]');
    var _IVPC = $('#drawer-1').find('[name=vpc]');
    var _ISubnet = $('#drawer-1').find('[name=subnet]');
    var _ISG = $('#drawer-1').find('[name=security]');
    var _IPassword = $('#drawer-1').find('[name=encrypt_password]');
    var _ICPU = $('#drawer-1').find('[name=cpu_arch]');
    var _IOS = $('#drawer-1').find('[name=os_type]');
    var _IOSV = $('#drawer-1').find('[name=os_version]');
    var _IBillingType = $('#drawer-1').find('[name=billing_type]');
    var _IBillingValue = $('#drawer-1').find('[name=billing_value]');
    var _IPublicIP = $('#drawer-1').find('[name=is_public_ip]');
    var _IStart = $('#drawer-1').find('[name=start_flag]');
    var _IVols = $('#drawer-1').find('#instance-vol-table');
    //卷配置选择器
    var _VName = $('#drawer-2').find('[name=vol_name]');
    var _VType = $('#drawer-2').find('[name=volume_disk_type]');
    var _VIOPS = $('#drawer-2').find('[name=disk_iops]');
    var _VRegion = $('#drawer-2').find('[name=region]');
    var _VAZ = $('#drawer-2').find('[name=az]');
    var _VKMS = $('#drawer-2').find('[name=kms]');
    var _VPassword = $('#drawer-2').find('[name=encrypt_password]');
    var _VDiskDel = $('#drawer-2').find('[name=disk_delete_on_termination]');

    var initInstanceTableFlag = false;
    var initVolTableFlag = false;
    var source_hypervisor;
    var target_hypervisor;
    var recover_type; // 恢复类型：1-实例，2-卷

    const BILLING_BANDWIDTH = 'bandwidth';
    const BILLING_TRAFFIC = 'traffic';
    var DEFAULT_ARCHITECTURE = 'x86';
    // 病毒扫描状态
    const VIRUS_NO_SCAN = 0;
    const VIRUS_HEALTH = 2;
    const VIRUS_INFECT = 3;
    const VIRUS_INFECT_BUT_UNFINISHED = 4;
    var isNoScan = false;
    var isHeathy = false;
    var isInfect = false;
    var isInfectButUnfinished = false;
    const validZone = '可用区'; //这个地方不能提语言包，因为英文版数据库这里存的就是"可用区"这个中文，提了语言包会识别不到
    const initOption = '<option value="">' + LANG.UI_RECOVERY_AWS_PLEASE_SELECT_TIPS + '</option>';
    var unSelectedVolUuids = []; // 保存排除卷的uuid
    var cpu_info_list; // 从supportInfo接口获取
    var os_info_list; // 从supportInfo接口获取

    const CPU_ARCH = {
        x86: 1,
        x86_64: 2,
        ARM: 3,
        ARM64: 4
    };

    //第二步初始化区域选择框
    const initRegionInfo = function (platformChangeFlag = false) {
        //未初始化或切换云平台才重新获取
        if ((!initRegionFlag || platformChangeFlag) && _platformUuid) {
            pAjaxRequest({platform_uuid: _platformUuid}, "/api/v1/cloud/platform/regions", "GET", function (d) {
                if (d.success) {
                    _regionInfo = d.data.rows;
                    initRegionFlag = true;

                    if (platformChangeFlag) {
                        //更新卷恢复的区域、可用区 todo:原配置可选时默认选中
                        let option = '';
                        $.each(_regionInfo, function (i, v) {
                            let selected = '';
                            option += '<option value="' + v.region_name + '" ' + selected + '>' + v.region_cn_name ?? v.region_name + '</option>';
                        });
                        $('.vol-region-select').empty();
                        $('.vol-region-select').append(option);

                        let azOption = '';
                        $.each(_regionInfo[0].available_zone, function (idx, val) {
                            let selected = '';
                            azOption += '<option value="' + val.zone + '" ' + selected + '>' + (val.zone_cn_name ? val.zone_cn_name : val.zone) + '</option>';
                        });
                        $('.vol-az-select').empty();
                        $('.vol-az-select').append(azOption);
                    }
                } else {
                    operateResponseList(d);
                }
            }, false);
        }
    }

    //初始化实例恢复配置表格
    const initInstanceTable = function () {
        instance_configs = [];
        volume_configs = [];
        if (!initInstanceTableFlag) {
            let operates = {
                'click .edit-instance': function (event, value, row, index) {
                    initEditInstanceRecoverInfo(row);
                },
            }
            let options = {
                vin_url: '/api/v1/cloud/timepoints/instance_config',
                vin_method: 'GET',
                queryParamsType: '',
                queryParams: {
                    source_hypervisor: source_hypervisor,
                    target_hypervisor: target_hypervisor,
                    config_type: 1, //获取实例配置
                    timepoint_uuids: timepoint_uuids
                },
                pagination: false,
                sidePagination: 'server',
                pageNumber: 1,
                pageSize: 20,
                pageList: [10, 20, 50, 100],
                paginationLoop: false,
                uniqueId: 'instance_uuid',
                changeHeightBtn: true, //改变高度按钮
                batchOperation: false, // 批量操作
                sortable: false,
                // sortName: 'id',
                // sortOrder: 'desc',
                resizable: true,
                singleSelect: true,
                detailView: true,
                detailFormatter: initInsVolTable,
                onRefresh: function (params) {
                    $("#instance-table").bootstrapTable('hideLoading');
                },
                // responseHandler: function (res) {
                //
                // },
                onLoadSuccess: function () {
                    $('#recovertype-select').prop('disabled', false);
                    $('#instance-table').find('th.detail').css({width: '2%'});
                },
                columns: [ //列定义
                    {
                        field: 'vm_name',
                        title: LANG.UI_RECOVERY_AWS_ORIGINAL_INSTANCE,
                        sortable: false,
                        width: '15',
                        widthUnit: '%',
                        formatter: function (value, rowData) {
                            let savedFlag = false;
                            // 跨平台恢复标志
                            _crossFlag = rowData.cross_flag;
                            $.each(instance_configs, function (i, v) {
                                if (v.timepoint_uuid == rowData.timepoint_uuid) {
                                    savedFlag = true;
                                    return true;
                                }
                            })
                            //初始化保存，只保存已选时间点的
                            if (!savedFlag && -1 < $.inArray(rowData.timepoint_uuid, timepoint_uuids)) {
                                let security = [];
                                if (undefined !== rowData.networks && undefined !== rowData.networks.security) {
                                    $.each(rowData.networks.security, function (i, v) {
                                        security.push({
                                            sg_id: v.security_uuid,
                                            sg_name: v.security_name
                                        });
                                    });
                                }
                                // let retain_vols = []; // 保留的卷id
                                // $.each(rowData.disk_list, function (i, v) {
                                // 	retain_vols.push(v.uuid);
                                // });
                                let arch = rowData.architecture ?? DEFAULT_ARCHITECTURE;
                                instance_configs.push({
                                    instance_name: rowData.vm_name + '_' + getDatetime(rowData.timepoint),
                                    instance_type: rowData.instance_type,
                                    ori_region: rowData.region,
                                    region_uuid: rowData.region_uuid,
                                    region: rowData.region,
                                    region_cn_name: rowData.region_cn_name,
                                    ori_available_zone: rowData.available_zone,
                                    available_zone: rowData.available_zone,
                                    zone_cn_name: rowData.zone_cn_name,
                                    enterprise_project_id: '0',
                                    vpc_uuid: rowData.networks.vpc_uuid,
                                    vpc_name: rowData.networks.vpc_name ?? '',
                                    subnet_uuid: rowData.networks.subnet_uuid,
                                    subnet_ip_number: rowData.networks.subnet_ip_number ?? '',
                                    security: security,
                                    is_public_ip: true,
                                    start_flag: false,
                                    timepoint_uuid: rowData.timepoint_uuid,
                                    ami_id: rowData.ami_id,
                                    instance_uuid: rowData.instance_uuid,
                                    vm_name: rowData.vm_name,
                                    pass_flag: rowData.encrypted_flag,
                                    encrypt_pass: btoa(''),
                                    ori_hypervisor_type: rowData.hypervisor_type,
                                    os_type: rowData.os_type ?? '',
                                    os_version: rowData.os_version ?? '',
                                    // retain_vols: retain_vols,
                                    platform_uuid: rowData.platform_uuid,
                                    billing_type: BILLING_TRAFFIC,
                                    billing_value: 300,
                                    architecture: arch,
                                    cpu_arch: CPU_ARCH[rowData.cpu_arch] ?? CPU_ARCH[arch], // 转数字
                                });
                                $.each(rowData.disk_list, function (i, v) {
                                    saveVolRowData(v);
                                });

                                // 统计时间点病毒扫描状态，确定扫描策略的显示
                                switch (rowData.virus_scan_status) {
                                    case VIRUS_NO_SCAN:
                                        isNoScan = true;
                                        break;
                                    case VIRUS_HEALTH:
                                        isHeathy = true;
                                        break;
                                    case VIRUS_INFECT:
                                        isInfect = true;
                                        break;
                                    case VIRUS_INFECT_BUT_UNFINISHED:
                                        isInfect = true;
                                        isInfectButUnfinished = true;
                                        break;
                                    default:
                                        break;
                                }
                            }

                            // 存在未备份根卷的实例则禁用实例恢复，只能卷恢复
                            if (!_crossFlag) {
                                let rootFlag = false;
                                $.each(rowData.disk_list, function (i, v) {
                                    if (1 == v.root_disk_flag) {
                                        rootFlag = true;
                                        return true;
                                    }
                                });
                                if (!rootFlag) {
                                    $('#recovertype-select').val(2).change().prop('disabled', true);
                                }
                            }

                            return rowData.instance_uuid ? value + '(' + rowData.instance_uuid + ')' : value;
                        }
                    },
                    {
                        field: 'new_name',
                        title: LANG.UI_RECOVERY_AWS_INSTANCE_NAME_AFTER_RECOVERY,
                        width: '15',
                        widthUnit: '%',
                        formatter: function (value, data) {
                            return data.vm_name + '_' + getDatetime(data.timepoint);
                        }
                    },
                    {
                        field: 'region',
                        title: LANG.UI_RECOVERY_AWS_REGION_NEW,
                        formatter: function (value, data) {
                            return data.region_cn_name ? data.region_cn_name : value;
                        }
                        // width: '120px',
                    },
                    {
                        field: 'instance_type',
                        title: LANG.UI_RECOVERY_AWS_INSTANCE_TYPE_NEW,
                        formatter: function (value, data) {
                            return value ?? '--';
                        }
                        // width: '100px',
                    },
                    // {
                    // 	field: 'ami_id',
                    // 	title: 'AMI',
                    // 	width: '200px',
                    // },
                    {
                        field: 'vpc_uuid',
                        title: 'VPC',
                        // width: '120px',
                        formatter: function (value, data) {
                            if (undefined === data.networks || undefined === data.networks.vpc_uuid) {
                                return '--';
                            }
                            switch (data.hypervisor_type) {
                                case CONF.VM_TYPE.AWS:
                                    return data.networks.vpc_uuid;
                                case CONF.VM_TYPE.HUAWEICLOUD:
                                    let vpc_name = data.networks.vpc_name ?? '';
                                    return vpc_name + '(' + data.networks.vpc_uuid + ')';
                                default:
                                    return '--';
                            }
                        }
                    },
                    {
                        field: 'subnet_uuid',
                        title: LANG.UI_RECOVERY_AWS_SUBNET,
                        // width: '150px',
                        formatter: function (value, data) {
                            if (undefined === data.networks || undefined === data.networks.subnet_uuid) {
                                return '';
                            }
                            switch (data.hypervisor_type) {
                                case CONF.VM_TYPE.AWS:
                                    return data.networks.subnet_uuid;
                                case CONF.VM_TYPE.HUAWEICLOUD:
                                    let subnet_ip = data.networks.subnet_ip_number ?? '';
                                    return subnet_ip + '(' + data.networks.subnet_uuid + ')';
                                default:
                                    return '';
                            }
                        }
                    },
                    {
                        field: 'security',
                        title: LANG.UI_RECOVERY_AWS_SECURITY_NEW,
                        // width: '100px',
                        formatter: function (value, data) {
                            let security = data.networks.security;
                            // 3个以内的安全组显示名称，超过则显示为...，没有显示--
                            if (security[0].security_uuid) {
                                let sgNameArr = [];
                                let count = 0;
                                $.each(security, function (i, v) {
                                    if (count >= 3) return;
                                    sgNameArr.push(v.security_name);
                                    count++;
                                });
                                let sgName = sgNameArr.join();
                                if (security.length > 3) {
                                    sgName += '...';
                                }
                                return sgName;
                            } else {
                                return '--';
                            }
                        }
                    },
                    {
                        field: 'is_public_ip',
                        title: LANG.UI_RECOVERY_AWS_PUBLIC_IP,
                        // width: '100px',
                        formatter: function (value, data) {
                            return LANG.UI_RECOVERY_AWS_ENABLE;
                        }
                    },
                    {
                        field: 'start_flag',
                        title: LANG.UI_RECOVERY_AWS_START_AFTER_RECOVERY,
                        // width: '100px',
                        formatter: function (value, data) {
                            return LANG.UI_RECOVERY_AWS_NO;
                        }
                    },
                    {
                        field: 'operations',
                        title: LANG.UI_PUBLIC_OPERATION,
                        sortable: false,
                        width: 10,
                        widthUnit: '%',
                        events: operates,
                        formatter: function (value, data) {
                            let disabled = !_platformUuid ? 'disabled' : '';
                            return '<button type="button" class="btn green-turquoise btn-sm edit-instance" data-toggle="drawer" data-target="#drawer-1" ' + disabled + '>' +
                                '<i class="viconfont vicon-ge_modify"></i></button>';
                        },
                    }
                ]
            }
            $('#instance-table').bootstrapTable('destroy');
            $('#instance-table').baseTableConfig().init(options);
            initInstanceTableFlag = true;
        } else {
            $('#instance-table').bootstrapTable('refreshOptions', {
                queryParams: {
                    source_hypervisor: source_hypervisor,
                    target_hypervisor: target_hypervisor,
                    config_type: 1,
                    timepoint_uuids: timepoint_uuids
                }
            });
        }
    }

    //初始化卷恢复配置表格
    const initVolTable = function () {
        volume_configs = [];
        if (!initVolTableFlag) {
            let operates = {
                'click .edit-vol': function (event, value, row, index) {
                    initEditVolRecoverInfo(row);
                },
            }
            let options = {
                vin_url: '/api/v1/cloud/timepoints/instance_config',
                vin_method: 'GET',
                queryParamsType: '',
                queryParams: {
                    source_hypervisor: source_hypervisor,
                    target_hypervisor: target_hypervisor,
                    config_type: 2, //获取卷配置
                    timepoint_uuids: timepoint_uuids
                },
                pagination: false,
                sidePagination: 'server',
                pageNumber: 1,
                pageSize: 20,
                pageList: [10, 20, 50, 100],
                paginationLoop: false,
                uniqueId: 'uuid',
                changeHeightBtn: true, //改变高度按钮
                batchOperation: false, // 批量操作
                sortable: false,
                // sortName: 'id',
                // sortOrder: 'desc',
                resizable: true,
                clickToSelect: false,
                onRefresh: function (params) {
                    $("#vol-table").bootstrapTable('hideLoading');
                },
                onCheck: function (rowData, ele) {
                    saveVolRowData(rowData);
                    unSelectedVolUuids = unSelectedVolUuids.filter(id => id !== rowData.uuid);
                    // 启用操作按钮
                    $(ele).closest(`tr`).find(`button`).prop('disabled', false);
                },
                onUncheck: function (rowData, ele) {
                    $.each(volume_configs, function (i, v) {
                        if (undefined != v && v.volume_uuid == rowData.uuid && v.instance_uuid == rowData.instance_uuid) {
                            volume_configs.splice(i, 1);
                            return true;
                        }
                    });
                    unSelectedVolUuids.push(rowData.uuid);
                    // 禁用操作按钮
                    $(ele).closest(`tr`).find(`button`).prop('disabled', true);
                },
                onCheckAll: function (rowsData) {
                    $.each(rowsData, function (i, rowData) {
                        saveVolRowData(rowData);
                    });
                    unSelectedVolUuids = [];
                    // 启用操作按钮
                    $(`#vol-table`).find(`button`).prop('disabled', false);
                },
                onUncheckAll: function (rowsData) {
                    volume_configs = [];
                    $.each(rowsData, function (i, rowData) {
                        unSelectedVolUuids.push(rowData.uuid);
                    });
                    // 禁用操作按钮
                    $(`#vol-table`).find(`button`).prop('disabled', true);
                },
                columns: [ //列定义
                    {
                        field: 'checkbox',
                        checkbox: true,
                        sortable: false, //默认可排序，禁用排序才写此项
                        formatter: function (value, data) {
                            // 跨平台时默认禁用勾选，恢复所有的卷
                            return {checked: !unSelectedVolUuids.includes(data.uuid), disabled: _crossFlag};
                        }
                    },
                    {
                        field: 'vol_new_name',
                        title: LANG.UI_RECOVERY_AWS_VOLUME_NAME_AFTER_RECOVERY,
                        sortable: false,
                        width: '15',
                        widthUnit: '%',
                    },
                    {
                        field: 'uuid', //字段名
                        title: LANG.UI_RECOVERY_AWS_ORIGINAL_VOLUME_ID,
                        sortable: false,
                        width: '15',
                        widthUnit: '%',
                        formatter: function (value, rowData) {
                            //初始化保存
                            let savedFlag = false
                            $.each(volume_configs, function (i, v) {
                                if (undefined != v && v.volume_uuid == rowData.uuid && v.instance_uuid == rowData.instance_uuid) {
                                    savedFlag = true;
                                    return true;
                                }
                            });
                            if (!savedFlag) {
                                saveVolRowData(rowData);
                            }
                            return value;
                        }
                    },
                    {
                        field: 'type',
                        title: LANG.UI_RECOVERY_AWS_ROOT_OR_DATA_VOLUME,
                        formatter: function (value, data) {
                            //判断是根卷还是数据卷
                            return isRootVol(data.root_disk_flag) ? LANG.UI_RECOVERY_AWS_ROOT_VOLUME : LANG.UI_RECOVERY_AWS_DATA_VOLUME;
                        }
                    },
                    {
                        field: 'volume_disk_type',
                        title: LANG.UI_TASK_AWS_VOL_TYPE,
                        formatter: function (value) {
                            return value ? value : '--';
                        }
                    },
                    {
                        field: 'disk_iops',
                        title: LANG.UI_RECOVERY_CLOUD_IOPS,
                        formatter: function (value, data) {
                            return data.disk_iops ? data.disk_iops : '--';
                        }
                    },
                    {
                        field: 'totalSizeDes',
                        title: LANG.UI_TASK_AWS_VOL_SIZE,
                    },
                    {
                        field: 'instance_name',
                        title: LANG.UI_RECOVERY_AWS_INSTANCE
                    },
                    {
                        field: 'region',
                        title: LANG.UI_RECOVERY_AWS_REGION_NEW,
                        formatter: function (value, data) {
                            return data.region_cn_name ? data.region_cn_name : value;
                        }
                    },
                    {
                        field: 'available_zone',
                        title: LANG.UI_RECOVERY_AWS_AVAILABLE_REGION,
                        formatter: function (value, data) {
                            // return data.zone_cn_name ? data.zone_cn_name : value;
                            let zone ='';
                            if(data.zone_cn_name){
                                zone = data.zone_cn_name;
                                if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
                                    zone = zone.replace(validZone,'AZ');
                                }
                            }else {
                                zone = value
                            }
                            return zone;
                        }
                    },
                    {
                        field: 'encrypt_kms_alias',
                        title: LANG.UI_RECOVERY_AWS_KMS_KEY,
                        formatter: function (value) {
                            return value ? value : LANG.UI_PUBLIC_NOTHING;
                        }
                    },
                    {
                        field: 'encrypted_flag',
                        title: LANG.UI_BACKUP_DATA_ENCRYPT,
                        formatter: function (value) {
                            return value ? LANG.UI_PUBLIC_YES : LANG.UI_PUBLIC_NO;
                        }
                    },
                    {
                        field: 'operations',
                        title: LANG.UI_PUBLIC_OPERATION,
                        sortable: false,
                        events: operates,
                        formatter: function (value, data) {
                            let disabled = !_platformUuid ? 'disabled' : '';
                            return '<button type="button" class="btn green-turquoise btn-sm edit-vol" data-toggle="drawer" data-target="#drawer-2" ' + disabled + '>' +
                                '<i class="viconfont vicon-ge_modify"></i></button>';
                        },
                    }
                ]
            }
            $('#vol-table').bootstrapTable('destroy');
            $('#vol-table').baseTableConfig().init(options);
            initVolTableFlag = true;
        } else {
            $('#vol-table').bootstrapTable('refreshOptions', {
                queryParams: {
                    source_hypervisor: source_hypervisor,
                    target_hypervisor: target_hypervisor,
                    config_type: 2,
                    timepoint_uuids: timepoint_uuids
                }
            });
        }
    }

    //初始化编辑实例配置表单
    const initEditInstanceRecoverInfo = function (rowData) {
        $.each(instance_configs, function (i, v) {
            if (v.timepoint_uuid == rowData.timepoint_uuid) {
                _currentInstanceConfig = v;
                return true;
            }
        });

        //当前实例网络配置已加载的实例类型
        _currentInstanceConfig.networkLoadInstanceType = '';

        //实例名称
        _IName.val(_currentInstanceConfig.instance_name);

        //区域
        _IRegion.empty();
        let option = '';
        let regionMatchFlag = false; // 区域列表是否能匹配原配置
        $.each(_regionInfo, function (i, v) {
            let selected = v.region_name == _currentInstanceConfig.region ? 'selected' : '';
            if (selected) {
                regionMatchFlag = true;
            }
            let showregion = '';
            if (CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw") {
                showregion = v.region_cn_name ? v.region_cn_name : v.region_name
            }else{
                showregion = v.region_en_name ? v.region_en_name : v.region_name
            }
            option += '<option value="' + v.region_uuid + '" data-region_name="' + v.region_name + '" data-region_cn_name="' + showregion + '" ' + selected + '>'
                + showregion
                + '</option>';
        })
        _IRegion.append(option);

        //可用区
        initAvailableZone();
        if (CONF.VM_TYPE.HUAWEICLOUD == target_hypervisor) {
            _IAZ.closest('.form-group').show();
        } else {
            _IAZ.closest('.form-group').hide();
        }

        // 企业项目
        _IProject.val(_currentInstanceConfig.enterprise_project_id);
        if (CONF.VM_TYPE.HUAWEICLOUD == target_hypervisor) {
            _IProject.closest('.form-group').show();
        } else {
            _IProject.closest('.form-group').hide();
        }

        _IOSV.selectpicker('refresh');
        // 跨平台恢复时初始化操作系统版本
        if (crossPlatformFlag) {
            getCpuAndOS(target_hypervisor, _platformUuid);
        }


        //实例类型
        _IType.empty();
        _IType.append('<option value="' + _currentInstanceConfig.instance_type + '" selected>' + _currentInstanceConfig.instance_type + '</option>');
        _IType.selectpicker('refresh');
        _IType.next('.bootstrap-select').find('.dropdown-menu .inner').css({height: '400px', width: 'auto'});

        //vpc
        _IVPC.empty();
        let vpcOption = '';
        if (_currentInstanceConfig.vpc_uuid) {
            if (_currentInstanceConfig.vpc_name) {
                vpcOption = '<option value="' + _currentInstanceConfig.vpc_uuid + '" data-name="' + _currentInstanceConfig.vpc_name + '" selected>' +
                    _currentInstanceConfig.vpc_name + '(' + _currentInstanceConfig.vpc_uuid + ')' + '</option>'
            } else {
                vpcOption = '<option value="' + _currentInstanceConfig.vpc_uuid + '" selected>' +
                    _currentInstanceConfig.vpc_uuid + '</option>';
            }
        } else {
            vpcOption = '<option value=""> -- </option>';
        }
        _IVPC.append(vpcOption);

        //子网
        _ISubnet.empty();
        let subnetOption = '';
        if (_currentInstanceConfig.subnet_uuid) {
            if (_currentInstanceConfig.subnet_ip_number) {
                subnetOption = '<option value="' + _currentInstanceConfig.subnet_uuid + '" data-name="' + _currentInstanceConfig.subnet_ip_number + '" selected>' +
                    _currentInstanceConfig.subnet_ip_number + '(' + _currentInstanceConfig.subnet_uuid + ')' + '</option>';
            } else {
                subnetOption = '<option value="' + _currentInstanceConfig.subnet_uuid + '" selected>' +
                    _currentInstanceConfig.subnet_uuid + '</option>';
            }
        } else {
            subnetOption = '<option value=""> -- </option>';
        }
        _ISubnet.append(subnetOption);

        //安全组
        _security = [];
        _ISG.empty();
        $.each(_currentInstanceConfig.security, function (idx, val) {
            _security.push({sg_name: val.sg_name});
            let option = '<option value="' + val.sg_name + '" selected>' + val.sg_name + '</option>';
            _ISG.append(option);
        });
        _ISG.selectpicker('refresh');
        //跨平台时必选
        if (_crossFlag) {
            _ISG.closest('.form-group').find('.required').show();
        } else {
            _ISG.closest('.form-group').find('.required').hide();
        }

        // 区域不匹配时不显示相关的默认配置，必须手动选择
        if (!regionMatchFlag) {
            _IType.empty().append(initOption);
            _IType.selectpicker('refresh');
            _IVPC.empty().val('');
            _ISubnet.empty().val('');
            _ISG.val('');
            _ISG.selectpicker('refresh');
        }

        //数据加密密码，备份点有数据加密则显示密码输入框
        if (rowData.encrypted_flag) {
            _IPassword.val(atob(_currentInstanceConfig.encrypt_pass));
            // _IPassword.closest('.encrypt-password').show();
            currentPasswordFlag = true;
        } else {
            _IPassword.val('');
            _IPassword.closest('.encrypt-password').hide();
            currentPasswordFlag = false;
        }

        // 跨平台时显示cpu架构、操作系统
        _ICPU.val(_currentInstanceConfig.cpu_arch); // 转数字
        _IOS.val(_currentInstanceConfig.os_type.toLowerCase());
        reloadOSVersionSelect(os_info_list, `select[name=os_type]`);
        _IOSV.val(_currentInstanceConfig.os_version);
        if (!crossPlatformFlag) {
            _ICPU.closest('.cpu-arch').hide();
            _IOS.closest('.os-type').hide();
            _IOSV.closest('.os-version').hide();
        } else {
            _ICPU.closest('.cpu-arch').show();
            _IOS.closest('.os-type').show();
            _IOSV.closest('.os-version').show();
        }

        //分配公有ip
        _IPublicIP.prop('checked', _currentInstanceConfig.is_public_ip);
        if (_currentInstanceConfig.is_public_ip) {
            _IPublicIP.closest('span').addClass('checked');
        } else {
            _IPublicIP.closest('span').removeClass('checked');
        }

        // 公有ip计费方式，华为云使用
        if (_currentInstanceConfig.is_public_ip && CONF.VM_TYPE.HUAWEICLOUD == target_hypervisor) {
            _IBillingType.val(_currentInstanceConfig.billing_type);
            _IBillingValue.val(_currentInstanceConfig.billing_value);
            $('.public-ip-billing').show();
            if (BILLING_BANDWIDTH === _IBillingType.val()) {
                // 按带宽
                _IBillingValue.attr('max', 2000);
                _IBillingValue.attr('maxlength', 4);
            } else {
                // 按流量
                _IBillingValue.attr('max', 300);
                _IBillingValue.attr('maxlength', 3);
            }
        } else {
            $('.public-ip-billing').hide();
        }

        //自动启动
        _IStart.prop('checked', _currentInstanceConfig.start_flag);
        if (_currentInstanceConfig.start_flag) {
            _IStart.closest('span').addClass('checked');
        } else {
            _IStart.closest('span').removeClass('checked');
        }
    }

    // 初始化实例展开的卷表格，数据源是实例配置的disk_list
    const initInsVolTable = function (index, instanceRowData) {
        let _e = $('#instance-table').find(`tr[data-uniqueid="${instanceRowData.instance_uuid}"]`).next('.detail-view').find('td');
        _e.empty();
        // 转换特殊字符
        let volTableId = 'ins-vol-table_' + clearString(instanceRowData.instance_uuid);
        _e.append(`<table class="table-dark" id="${volTableId}"></table>`);
        let _table = $(`#${volTableId}`);

        let operates = {
            'click .edit-vol': function (event, value, row, index) {
                initEditVolRecoverInfo(row, true);
            },
        }

        $.each(instanceRowData.disk_list, function (i, v) {
            if (target_hypervisor != source_hypervisor) {
                // 切换云平台后重新初始化配置和表格显示
                v.region_uuid = '';
                v.region = '';
                v.region_cn_name = '';
                v.available_zone = '';
                v.zone_cn_name = '';
                v.encrypted_flag = false;
                v.encrypt_kms_key = '';
                v.encrypt_kms_alias = '';
                v.volume_disk_type = '';
                v.disk_iops = '';
            }
        });
        let options = {
            data: instanceRowData.disk_list,
            pagination: false,
            uniqueId: 'uuid',
            changeHeightBtn: true, //改变高度按钮
            batchOperation: false, // 批量操作
            sortable: false,
            resizable: true,
            clickToSelect: false,
            onRefresh: function (params) {
                _table.bootstrapTable('hideLoading');
            },
            onCheck: function (rowData, ele) {
                saveVolRowData(rowData);
                // 启用操作按钮
                $(ele).closest(`tr`).find(`button`).prop('disabled', false);
            },
            onUncheck: function (rowData, ele) {
                $.each(volume_configs, function (i, v) {
                    if (undefined != v && v.volume_uuid == rowData.uuid && v.instance_uuid == rowData.instance_uuid) {
                        volume_configs.splice(i, 1);
                        return true;
                    }
                });
                // 禁用操作按钮
                $(ele).closest(`tr`).find(`button`).prop('disabled', true);
            },
            onCheckAll: function (rowsData) {
                $.each(rowsData, function (i, rowData) {
                    saveVolRowData(rowData);
                    // 数据卷启用操作按钮
                    if (0 == rowData.root_disk_flag) {
                        $(`tr[data-uniqueid=${rowData.uuid}]`).find(`button`).prop('disabled', false);
                    }
                });
            },
            onUncheckAll: function (rowsData) {
                // 清空数据卷的
                $.each(volume_configs, function (i, v) {
                    if (undefined != v && v.volume_type != 'root') {
                        volume_configs.splice(i, 1);
                        // 数据卷禁用操作按钮
                        $(`tr[data-uniqueid=${v.volume_uuid}]`).find(`button`).prop('disabled', true);
                    }
                });
            },
            columns: [ //列定义
                {
                    field: 'checkbox',
                    checkbox: true,
                    sortable: false, //默认可排序，禁用排序才写此项
                    width: '2',
                    widthUnit: '%',
                    formatter: function (value, data) {
                        // 检查之前已取消勾选的则不再选中
                        let checked = false;
                        $.each(volume_configs, function (i, v) {
                            if (undefined != v && v.volume_uuid == data.uuid && v.instance_uuid == data.instance_uuid) {
                                checked = true;
                                return true;
                            }
                        });
                        // 实例恢复根卷强制勾选，数据卷可排除
                        return {checked: checked, disabled: isRootVol(data.root_disk_flag)};
                    }
                },
                {
                    field: 'vol_new_name',
                    title: LANG.UI_RECOVERY_AWS_VOLUME_NAME_AFTER_RECOVERY,
                    sortable: false,
                    width: '15',
                    widthUnit: '%',
                },
                {
                    field: 'uuid', //字段名
                    title: LANG.UI_RECOVERY_AWS_ORIGINAL_VOLUME_ID,
                    sortable: false,
                    width: '15',
                    widthUnit: '%',
                },
                {
                    field: 'type',
                    title: LANG.UI_RECOVERY_AWS_ROOT_OR_DATA_VOLUME,
                    formatter: function (value, data) {
                        //判断是根卷还是数据卷
                        return isRootVol(data.root_disk_flag) ? LANG.UI_RECOVERY_AWS_ROOT_VOLUME : LANG.UI_RECOVERY_AWS_DATA_VOLUME;
                    }
                },
                {
                    field: 'volume_disk_type',
                    title: LANG.UI_TASK_AWS_VOL_TYPE,
                    formatter: function (value) {
                        return value ? value : '--';
                    }
                },
                {
                    field: 'disk_iops',
                    title: LANG.UI_RECOVERY_CLOUD_IOPS,
                    formatter: function (value, data) {
                        return data.disk_iops ? data.disk_iops : '--';
                    }
                },
                {
                    field: 'totalSizeDes',
                    title: LANG.UI_TASK_AWS_VOL_SIZE,
                },
                {
                    field: 'available_zone',
                    title: LANG.UI_RECOVERY_AWS_AVAILABLE_REGION,
                    formatter: function (value, rowData) {
                        let zone = rowData.zone_cn_name ? rowData.zone_cn_name : value;
                        $.each(instance_configs, function (i, v) {
                            if (v.instance_uuid == instanceRowData.instance_uuid) {
                                // zone = v.zone_cn_name ? v.zone_cn_name : v.available_zone;
                                if (v.zone_cn_name) {
                                    zone = v.zone_cn_name;
                                    if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
                                        zone = zone.replace(validZone,'AZ')
                                    }
                                }else {
                                    zone = v.available_zone;
                                }
                                return true;
                            }
                        })
                        return zone ? zone : '--';
                    }
                },
                {
                    field: 'encrypt_kms_alias',
                    title: LANG.UI_RECOVERY_AWS_KMS_KEY,
                    formatter: function (value) {
                        return value ? value : LANG.UI_PUBLIC_NOTHING;
                    }
                },
                {
                    field: 'operations',
                    title: '',
                    sortable: false,
                    width: 10,
                    widthUnit: '%',
                    events: operates,
                    formatter: function (value, data) {
                        let disabled = !_platformUuid ? 'disabled' : '';
                        return '<button type="button" class="btn green-turquoise btn-sm edit-vol" data-toggle="drawer" data-target="#drawer-2" ' + disabled + '>' +
                            '<i class="viconfont vicon-ge_modify"></i></button>';
                    },
                }
            ]
        }
        _table.bootstrapTable('destroy');
        _table.baseTableConfig().init(options);
    }

    /**
     * 初始化编辑卷配置表单
     * @param rowData
     * @param instanceFlag 是否实例详情下操作
     */
    const initEditVolRecoverInfo = function (rowData, instanceFlag = false) {
        $.each(volume_configs, function (i, v) {
            if (v.volume_uuid == rowData.uuid && v.instance_uuid == rowData.instance_uuid) {
                _currentVolConfig = v;
                return true;
            }
        });

        //卷名称
        _VName.val(_currentVolConfig.vol_new_name);


        //IOPS
        $('#diskIopsDiv').spinner({value:  _currentVolConfig.disk_iops, step: 1, min: 1, max: 999999, maxlength: 6});

        //区域
        _VRegion.empty();
        let option = '';
        $.each(_regionInfo, function (i, v) {
            let selected = v.region_name == _currentVolConfig.region ? 'selected' : '';
            let showregion = '';
            if (CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw") {
                showregion = v.region_cn_name ? v.region_cn_name : v.region_name
            }else{
                showregion = v.region_en_name ? v.region_en_name : v.region_name
            }
            option += '<option value="' + v.region_uuid + '" data-region_name="' + v.region_name + '" data-region_cn_name="' + showregion + '" ' + selected + '>'
                + showregion
                + '</option>';
        })
        _VRegion.append(option);

        //可用区
        initVolAvailableZone();

        //卷类型
        initVolumeTypes();

        //KMS密钥
        initKmsList(_currentVolConfig.disk_encrypt_key_alias, _currentVolConfig.disk_encrypt_key, false);

        //数据加密密码，备份点有数据加密则显示密码输入框
        if (_currentVolConfig.pass_flag) {
            _VPassword.val(atob(_currentVolConfig.encrypt_pass));
            // _VPassword.closest('.encrypt-password').show();
            currentVolPasswordFlag = true;
        } else {
            _VPassword.val('');
            _VPassword.closest('.encrypt-password').hide();
            currentVolPasswordFlag = false;
        }

        if (instanceFlag) {
            // 实例下的卷不能配置区域、可用区、加密密码
            _VRegion.closest('.form-group').hide();
            _VAZ.closest('.form-group').hide();
            _VPassword.closest('.form-group').hide();
            // AWS实例下的卷可配置实例终止时删除卷
            if (CONF.VM_TYPE.AWS === target_hypervisor) {
                $('#diskDeleteOnTermination').bootstrapSwitch('state', _currentVolConfig.disk_delete_on_termination);
                _VDiskDel.closest('.form-group').find('.control-label').text(LANG.UI_RECOVERY_CLOUD_VOLUME_DELETE_ON_TERMINATION);
                _VDiskDel.closest('.form-group').find('.ddot-tips').hide();
                _VDiskDel.closest('.form-group').show();
            }
            // 华为云实例下的数据卷可配置实例终止时删除卷（描述改为：卷随实例释放）
            if (CONF.VM_TYPE.HUAWEICLOUD === target_hypervisor) {
                if (_currentVolConfig.volume_type === 'data') {
                    $('#diskDeleteOnTermination').bootstrapSwitch('state', _currentVolConfig.disk_delete_on_termination);
                    _VDiskDel.closest('.form-group').find('.control-label').text(LANG.UI_RECOVERY_CLOUD_VOLUME_RELEASE_WITH_INSTANCE);
                    _VDiskDel.closest('.form-group').find('.ddot-tips').show();
                    _VDiskDel.closest('.form-group').show();
                } else {
                    $('#diskDeleteOnTermination').bootstrapSwitch('state', false);
                    _VDiskDel.closest('.form-group').hide();
                }
            }
        } else {
            _VRegion.closest('.form-group').show();
            _VAZ.closest('.form-group').show();
            _VDiskDel.closest('.form-group').hide();
        }
    }

    // 初始化可用区
    const initAvailableZone = function () {
        let azs = [];
        $.each(_regionInfo, function (i, v) {
            if (_IRegion.val() == v.region_uuid) {
                azs = v.available_zone;
                return true;
            }
        });
        _IAZ.empty();
        let azOption = '';
        $.each(azs, function (i, v) {
            let selected = v.zone == _currentInstanceConfig.available_zone ? 'selected' : '';
            let zone = v.zone_cn_name ? v.zone_cn_name : v.zone;
            if(CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw"){
                azOption += `<option value="${v.zone}" data-zone_cn_name="${zone}" ${selected}>${zone}</option>`;
            }else{
                //这个地方不能提语言包，因为英文版数据库这里存的就是"可用区"这个中文，提了语言包会识别不到
                let new_en_zone = v.zone_cn_name ? v.zone_cn_name.replace(validZone,'AZ'): v.zone;
                azOption += `<option value="${v.zone}" data-zone_cn_name="${zone}" ${selected}>${new_en_zone}</option>`;
            }
        });
        _IAZ.append(azOption);
    }

    // 初始化卷的可用区
    const initVolAvailableZone = function () {
        let azs = [];
        $.each(_regionInfo, function (i, v) {
            if (_VRegion.val() == v.region_uuid) {
                azs = v.available_zone;
                return true;
            }
        });
        _VAZ.empty();
        let azOption = '';
        $.each(azs, function (i, v) {
            let selected = v.zone == _currentVolConfig.available_zone ? 'selected' : '';
            let zone = v.zone_cn_name ? v.zone_cn_name : v.zone;
            if(CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw"){
                azOption += `<option value="${v.zone}" data-zone_cn_name="${zone}" ${selected}>${zone}</option>`;
            }else{
                //这个地方不能提语言包，因为英文版数据库这里存的就是"可用区"这个中文，提了语言包会识别不到
                let new_en_zone = v.zone_cn_name ? v.zone_cn_name.replace(validZone,'AZ'): v.zone;
                azOption += `<option value="${v.zone}" data-zone_cn_name="${zone}" ${selected}>${new_en_zone}</option>`;
            }
        });
        _VAZ.append(azOption);
    }

    // 获取cpu架构和操作系统
    const getCpuAndOS = function (hypervisor, platform_uuid) {
        let p = {};
        p.hypervisor_type = hypervisor;
        p.platform_uuid = platform_uuid;
        pAjaxRequest(p, "/api/v1/vm/platforms/support_info", "GET", function (d) {
            if (d.success) {
                cpu_info_list = d.data.cpu_info_list;
                os_info_list = d.data.os_info_list;
            } else {
                operateResponseList(d);
            }
        }, false);
    }

    // 初始化kms密钥列表
    const initKmsList = function (alias, value = '', needLoad = true) {
        let volRegion = _VRegion.val();
        let region_uuid = '';
        let loadedFlag = false; // 当前区域是否已加载过kms
        let currentRegionInfo = {};
        $.each(_regionInfo, function (i, v) {
            if (volRegion == v.region_uuid) {
                region_uuid = v.region_uuid;
                if (typeof v.kms_info !== 'undefined') {
                    loadedFlag = true;
                }
                currentRegionInfo = v;
                return true;
            }
        });
        // 已加载过不再重复调用接口
        if (needLoad && !loadedFlag) {
            let p = {platform_uuid: _platformUuid, region_uuid: region_uuid};
            Metronic.blockUI({target: '#drawer-2',animate: true});
            pAjaxRequest(p, "/api/v1/cloud/kms_info", "GET", function (d) {
                Metronic.unblockUI('#drawer-2');
                if (d.success) {
                    // 写入_regionInfo
                    $.each(_regionInfo, function (i, v) {
                        if (v.region_uuid == region_uuid) {
                            v.kms_info = d.data.kms_info;
                            currentRegionInfo = v;
                            return;
                        }
                    });
                    loadKmsList(currentRegionInfo, value, alias);
                } else {
                    operateResponseList(d);
                }
            }, true);
        } else {
            loadKmsList(currentRegionInfo, value, alias);
        }
    }

    // 更新卷恢复的kms列表
    const loadKmsList = function (currentRegionInfo, value, alias) {
        let kmsOption = '';
        if (currentRegionInfo.kms_info) {
            kmsOption = '<option value="">' + LANG.UI_PUBLIC_NOTHING + '</option>';
            $.each(currentRegionInfo.kms_info, function (idx, val) {
                let selected = val.kms_id == value ? 'selected' : '';
                kmsOption += '<option value="' + val.kms_id + '" ' + selected + '>' + val.kms_alias + '</option>';
            });
        } else {
            if (value) {
                kmsOption = '<option value="' + value + '">' + alias + '</option>';
            } else {
                kmsOption = '<option value="">' + LANG.UI_PUBLIC_NOTHING + '</option>';
            }
        }
        _VKMS.empty();
        _VKMS.append(kmsOption);
    }

    // 保存卷配置
    const saveVolRowData = function (rowData) {
        // let savedFlag = false;
        $.each(volume_configs, function (i, v) {
            if (undefined !== v && v.volume_uuid == rowData.uuid && v.instance_uuid == rowData.instance_uuid) {
                // savedFlag = true;
                volume_configs.splice(i, 1);
                return true;
            }
        });
        if (-1 < $.inArray(rowData.timepoint_uuid, timepoint_uuids)) {
            let disk_encrypt_flag = undefined !== rowData.encrypt_kms_key && '' !== rowData.encrypt_kms_key;
            volume_configs.push({
                instance_uuid: rowData.instance_uuid,
                volume_type: isRootVol(rowData.root_disk_flag) ? 'root' : 'data',
                volume_uuid: rowData.uuid,
                region_uuid: rowData.region_uuid,
                region: rowData.region ? rowData.region : '',
                region_cn_name: rowData.region_cn_name ? rowData.region_cn_name : '',
                available_zone: rowData.available_zone ? rowData.available_zone : '',
                zone_cn_name: rowData.zone_cn_name ? rowData.zone_cn_name : '',
                vol_new_name: rowData.vol_new_name,
                disk_encrypt_flag: disk_encrypt_flag,
                disk_encrypt_key: rowData.encrypt_kms_key ? rowData.encrypt_kms_key : '',
                disk_encrypt_key_alias: rowData.encrypt_kms_alias,
                pass_flag: rowData.encrypted_flag,
                encrypt_pass: btoa(''),
                volume_disk_type: rowData.volume_disk_type ? rowData.volume_disk_type : '',
                disk_iops: rowData.disk_iops ? rowData.disk_iops : '',
                totalSize: rowData.totalSize,
                disk_delete_on_termination: _crossFlag ? 0 : rowData.disk_delete_on_termination,
            });
        }
    }

    // ----- 事件 -----
    const initListener = function(){
        //初始化多选下拉框
        $(".selectpicker").selectpicker({
            noneSelectedText: LANG.BILLING_PLEASE_SELECT,
            deselectAllText: LANG.BILLING_DESELECT_ALL,
            selectAllText: LANG.BILLING_SELECT_ALL,
            liveSearchPlaceholder: LANG.BILLING_SEARCH,
            countSelectedText: function(){}
        });

        //切换云平台
        $('#platform-select').on('change', function () {
            _platformUuid = this.value;
            _platformName = $('#platform-select option:selected').text();
            target_hypervisor = $('#platform-select option:selected').data('hypervisor');
            recover_type = $('#recovertype-select').val();
            selectPlatformHandler(target_hypervisor);
        });

        //切换恢复类型：实例恢复/卷恢复
        $('#recovertype-select').on('change', function () {
            recover_type = parseInt(this.value);
            if (1 == this.value) {
                initInstanceTable();
                $('#instance-div').show();
                $('#vol-div').hide();
            } else if (2 == this.value) {
                initVolTable();
                $('#instance-div').hide();
                $('#vol-div').show();
            }
        });

        _IName.on('keyup', function () {
            // 华为云输入实例名时去掉非法字符
            if (CONF.VM_TYPE.HUAWEICLOUD == target_hypervisor) {
                _IName.val(this.value.replace(/[^\u4e00-\u9fa5a-zA-Z0-9._-]/g, ''));
            }
        });

        //切换实例配置的区域
        _IRegion.on('change', function () {
            initAvailableZone();
            switch (target_hypervisor) {
                case CONF.VM_TYPE.AWS:
                    getTypeConfigs($(this).find('option:selected').val(), true, '', _currentInstanceConfig.architecture);
                    break;
                case CONF.VM_TYPE.HUAWEICLOUD:
                    getAllConfigsByRegion($(this).find('option:selected').val(), _IAZ.find('option:selected').val(), _currentInstanceConfig.architecture, _IOS.find('option:selected').val());
                    break;
                default:
                    break;
            }
        });

        //切换实例配置的可用区，华为云使用
        _IAZ.on('change', function () {
            switch (target_hypervisor) {
                case CONF.VM_TYPE.HUAWEICLOUD:
                    getTypeConfigs(_IRegion.find('option:selected').val(), false, $(this).find('option:selected').val(), _currentInstanceConfig.architecture, _IOS.find('option:selected').val());
                    break;
                default:
                    break;
            }
        });

        //获取实例配置的实例类型
        _IType.next('.bootstrap-select').find('.dropdown-toggle').on('click', function () {
            getTypeConfigs(_IRegion.val(), false, _IAZ.val() ?? '', _currentInstanceConfig.architecture, _IOS.val() ?? '');
        });

        //切换实例类型重新获取网络配置
        _IType.on('change', function () {
            switch (target_hypervisor) {
                case CONF.VM_TYPE.AWS:
                    getNetworkConfigs(_IRegion.val(), this.value);
                    break;
                default:
                    break;
            }
        });

        //首次点击安全组获取
        _ISG.next('.bootstrap-select').find('.dropdown-toggle').on('click', function () {
            getNetworkConfigs(_IRegion.val(), _IType.val());
        });

        //获取实例配置的VPC
        _IVPC.on('click', function () {
            switch (target_hypervisor) {
                case CONF.VM_TYPE.HUAWEICLOUD:
                    getNetworkConfigs(_IRegion.val(), _IType.val());
                    break;
                default:
                    break;
            }
        });

        //获取实例配置的子网
        _ISubnet.on('click', function () {
            switch (target_hypervisor) {
                case CONF.VM_TYPE.AWS:
                    getNetworkConfigs(_IRegion.val(), _IType.val());
                    break;
                default:
                    break;
            }
        });

        //获取实例配置的安全组
        _ISG.on('click', function () {
            getTypeConfigs(_IRegion.val(), true, _IAZ.val(), _currentInstanceConfig.architecture);
        });

        // 切换vpc 重新获取子网和安全组
        _IVPC.on('change', function () {
            networkHandler(_IRegion.val(), false, _currentNetworkConfig);
        });

        // 切换子网，vpc跟随变动
        _ISubnet.on('change', function () {
            switch (target_hypervisor) {
                case CONF.VM_TYPE.AWS:
                    let subnet;
                    $.each(_currentNetworkConfig, function (i, v) {
                        if (v.network_vpc == _IVPC.val()) {
                            subnet = v.network_subnet;
                            return true;
                        }
                    });
                    $.each(subnet, function (i, v) {
                        if (v.network_subnet_id == _ISubnet.val()) {
                            _IAZ.val(v.network_subnet_zone);
                            return true;
                        }
                    });
                    break;
                default:
                    break;
            }
        });

        // 勾选分配公有ip
        _IPublicIP.on('change', function () {
            if (CONF.VM_TYPE.HUAWEICLOUD == target_hypervisor) {
                if (this.checked) {
                    $('.public-ip-billing').show();
                } else {
                    $('.public-ip-billing').hide();
                }
            }
        });

        // 切换付费方式
        _IBillingType.on('change', function () {
            if (BILLING_BANDWIDTH === this.value) {
                _IBillingValue.attr('max', 2000);
                _IBillingValue.attr('maxlength', 4);
            } else {
                _IBillingValue.attr('max', 300);
                _IBillingValue.attr('maxlength', 3);
            }
        });

        //确定保存实例配置
        $('#recover-ins-submit').on('click', saveInstanceConfigTemp);

        //取消保存实例配置时重置标记
        $('#recover-ins-cancel').on('click', function () {
            setInstanceInitFlags('', '', '');
        });

        // 切换卷类型
        _VType.on('change', function () {
            changeVolumeType();
        })

        //卷恢复切换区域
        _VRegion.on('change', function () {
            volRegionChange(this.value);
        });

        //加载卷KMS密钥列表
        _VKMS.on('click', function() {
            initKmsList($(this).find('option:selected').text(), this.value);
        });

        _VName.on('keyup', function () {
            // 华为云输入卷名时去掉非法字符
            if (CONF.VM_TYPE.HUAWEICLOUD == target_hypervisor) {
                _VName.val(this.value.replace(/[^\u4e00-\u9fa5a-zA-Z0-9._-]/g, ''));
            }
        });

        //确定保存卷配置
        $('#recover-vol-submit').on('click', saveVolConfigTemp);

        // 切换操作系统类型
        _IOS.on('change', function () {
            reloadOSVersionSelect(os_info_list, this);
        });
    }

    // 选中或切换平台的处理
    const selectPlatformHandler = function (target_hypervisor) {
        //重新初始化区域信息
        initRegionInfo(true);
        if (1 == recover_type) {
            //根据获取的第一个区域重新初始化实例类型、VPC等
            switch (target_hypervisor) {
                case CONF.VM_TYPE.AWS:
                    // AWS
                    DEFAULT_ARCHITECTURE = 'x86_64';
                    break;
                case CONF.VM_TYPE.HUAWEICLOUD:
                    // 华为云
                    DEFAULT_ARCHITECTURE = 'x86';
                    // getAllConfigsByRegion(_regionInfo[0].region_uuid, _regionInfo[0].available_zone[0]);
                    break;
                default:
                    break;
            }
        }
        if (CONF.VM_TYPE.HUAWEICLOUD == target_hypervisor) {
            $('.kms-tips').show();
            loadEnterpriseProjectList(_platformData[_platformUuid].enterprise_projects);
            $(`.huweicloud-vol-tips`).show();
            _IName.attr('maxlength', 64);
            _VName.attr('maxlength', 64);
        } else {
            $('.kms-tips').hide();
            $(`.huweicloud-vol-tips`).hide();
            _IName.attr('maxlength', 256);
            _VName.attr('maxlength', 256);
        }
        // 重新初始化实例和卷配置，需要手动选择后才能下一步
        platformChangeInitConfig();
        $('.edit-instance').attr('disabled', false);
        $('.edit-vol').attr('disabled', false);
    }

    // 企业项目下拉框加载
    const loadEnterpriseProjectList = function (data) {
        _IProject.empty();
        let options = '';
        $.each(data, function (i, v) {
            let selected = v.project_id == _currentInstanceConfig.enterprise_project_id ? 'selected' : '';
            options += `<option value="${v.project_id}" ${selected}>${v.project_name}</option>`;
        });
        _IProject.append(options);
    }

    // 切换云平台后重新初始化配置和表格显示
    const platformChangeInitConfig = function () {
        $.each(instance_configs, function (i, v) {
            v.instance_type = '';
            v.region_uuid = '';
            v.region = '';
            v.region_cn_name = '';
            v.available_zone = '';
            v.zone_cn_name = '';
            v.vpc_uuid = '';
            v.vpc_name = '';
            v.subnet_uuid = '';
            v.subnet_ip_number = '';
            v.security = [];
            v.is_public_ip = true;
            v.start_flag = false;
            v.encrypt_pass = btoa('');
            if (_crossFlag) {
                v.os_type = '';
            }
            v.billing_type = BILLING_TRAFFIC;
            v.billing_value = 300;
            v.architecture = DEFAULT_ARCHITECTURE;
            v.cpu_arch = CPU_ARCH[DEFAULT_ARCHITECTURE];
        });
        $.each(volume_configs, function (i, v) {
            v.region_uuid = '';
            v.region = '';
            v.region_cn_name = '';
            v.available_zone = '';
            v.zone_cn_name = '';
            v.disk_encrypt_flag = false;
            v.disk_encrypt_key = '';
            v.disk_encrypt_key_alias = '';
            v.pass_flag = false;
            v.encrypt_pass = btoa('');
            v.volume_disk_type = '';
            v.disk_iops = '';
        });

        // 更新实例配置表格显示
        for (let i = 0; i < instance_configs.length; i++) {
            let _tr = $('#instance-table').find('tr[data-index=' + i + ']');
            _tr.find('td').eq(3).text('--');
            _tr.find('td').eq(4).text('--');
            _tr.find('td').eq(5).text('--');
            _tr.find('td').eq(6).text('--');
            _tr.find('td').eq(7).text('--');
            // 公有ip默认启用
            _tr.find('td').eq(8).text(LANG.UI_RECOVERY_AWS_ENABLE);
            // 恢复后启动默认否
            _tr.find('td').eq(9).text(LANG.UI_PUBLIC_NO);

            // 更新实例下的卷配置表格显示
            let instanceUuid = instance_configs[i].instance_uuid;
            for (let j = 0; j < volume_configs.length; j++) {
                if (instanceUuid == volume_configs[j].instance_uuid) {
                    $(`#ins-vol-table_` + clearString(instanceUuid)).bootstrapTable('updateRow', {
                        index: j,
                        row: {
                            volume_disk_type: '--',
                            disk_iops: '--',
                            available_zone: '--',
                            encrypt_kms_alias: LANG.UI_PUBLIC_NOTHING
                        }
                    })
                }
            }
        }

        // 更新卷配置表格显示
        for (let i = 0; i < volume_configs.length; i++) {
            $('#vol-table').bootstrapTable('updateRow', {
                index: i,
                row: {
                    volume_disk_type: '--',
                    disk_iops: '--',
                    region: '--',
                    available_zone: '--',
                    encrypt_kms_alias: LANG.UI_PUBLIC_NOTHING
                }
            });
        }
    }

    //异步获取实例类型等并初始化，loadNetwork是否需要加载网络配置
    const getTypeConfigs = function (region_uuid, loadNetwork = true, available_zone = '', architecture = DEFAULT_ARCHITECTURE, os_type = '') {
        switch (target_hypervisor) {
            case CONF.VM_TYPE.AWS:
                if (region_uuid == configRegionUuid) return; //只加载一次
                break;
            case CONF.VM_TYPE.HUAWEICLOUD:
                if (region_uuid == configRegionUuid && available_zone == configAvailableZone && os_type == configOsType) return; //只加载一次
                break;
            default:
                break;
        }
        let p = {
            platform_uuid: _platformUuid,
            region_uuid: region_uuid,
            available_zone: available_zone,
            architecture: architecture,
            os_type: os_type,
            cross_flag: crossPlatformFlag
        };
        Metronic.blockUI({target: '#drawer-1',animate: true});
        pAjaxRequest(p, "/api/v1/cloud/instances/type_configs", "GET", function (d) {
            Metronic.unblockUI('#drawer-1');
            setInstanceInitFlags(region_uuid, available_zone, os_type);
            if (d.success) {
                let instanceTypes = d.data.instance_types;

                let oriRegionFlag = true; //是否原区域恢复
                if (region_uuid != _currentInstanceConfig.region_uuid) {
                    oriRegionFlag = false;
                }

                //清空选项后再加载
                _IType.empty();
                if (!oriRegionFlag) {
                    // 跨区域
                    _IType.append(initOption);
                }

                //实例类型
                $.each(instanceTypes, function (i, v) {
                    let vcpu = v.instance_type_vcpu;
                    let memStr = storageCalculateSize(1024 * 1024 * parseInt(v.instance_type_memry));
                    let selected = '';
                    if (oriRegionFlag) {
                        // 原区域恢复有原实例类型则选中
                        selected = v.instance_type_name == _currentInstanceConfig.instance_type ? 'selected' : '';
                    }
                    let option = '<option value="' + v.instance_type_name + '" ' + selected + '>' +
                        v.instance_type_name + '(' + vcpu + 'vCPU,' + memStr + ')</option>';
                    _IType.append(option);
                });
                _IType.selectpicker('refresh');

                if (oriRegionFlag) {
                    // _IType.val(_currentInstanceConfig.instance_type);
                    if (loadNetwork) {
                        getNetworkConfigs(region_uuid, _currentInstanceConfig.instance_type);
                    }
                } else{
                    // 跨区域需手动选择
                    _IType.val('');
                    if (loadNetwork) {
                        _IVPC.empty();
                        _IVPC.append(initOption);
                        _IVPC.val('');
                        _ISubnet.empty();
                        _ISubnet.append(initOption);
                        _ISubnet.val('');
                        _ISG.empty();
                        _ISG.selectpicker('refresh');
                    }
                }
            } else {
                operateResponseList(d);
            }
        });
    }

    //异步获取实例类型等并初始化，华为云使用
    const getAllConfigsByRegion = function (region_uuid, available_zone, architecture = DEFAULT_ARCHITECTURE, os_type = '') {
        let p = {
            platform_uuid: _platformUuid,
            region_uuid: region_uuid,
            available_zone: available_zone,
            architecture: architecture,
            os_type: os_type
        };
        Metronic.blockUI({target: '#drawer-1',animate: true});
        Metronic.blockUI({target: '#awsrecovercontent', animate: true});
        pAjaxRequest(p, "/api/v1/cloud/instances/all_configs", "GET", function (d) {
            Metronic.unblockUI('#drawer-1');
            Metronic.unblockUI('#awsrecovercontent');
            if (d.success) {
                let instanceTypes = d.data.instance_types;
                let networkList = d.data.network_list;
                let network = {}; //当前网络

                let oriRegionFlag = true; //是否原区域恢复
                if (region_uuid != _currentInstanceConfig.region_uuid) {
                    oriRegionFlag = false;
                }

                //清空选项后再加载
                _IType.empty();
                if (!oriRegionFlag) {
                    _IType.append(initOption);
                }

                _IVPC.empty();
                _ISubnet.empty();
                _ISG.empty();

                //实例类型
                $.each(instanceTypes, function (i, v) {
                    let vcpu = v.instance_type_vcpu;
                    let memStr = storageCalculateSize(1024 * 1024 * parseInt(v.instance_type_memry));
                    let option = '<option value="' + v.instance_type_name + '">' +
                        v.instance_type_name + '(' + vcpu + 'vCPU,' + memStr + ')</option>';
                    _IType.append(option);
                });
                if (oriRegionFlag) {
                    _IType.val(_currentInstanceConfig.instance_type);
                } else{
                    _IType.val('');
                }
                _IType.selectpicker('refresh');

                if (!networkList) {
                    // 没有vpc则提示
                    UIToastr.showWarning(LANG.UI_RECOVERY_AWS_VPC, LANG.UI_RECOVERY_AWS_VPC_NOT_EXIST_TIPS);
                    return false;
                }
                //vpc
                $.each(networkList, function (i, v) {
                    let option = '<option value="' + v.network_vpc + '" data-name="' + v.network_vpc_name + '">' + v.network_vpc_name + '(' + v.network_vpc + ')' + '</option>';
                    _IVPC.append(option);
                    if (_currentInstanceConfig.vpc_uuid == v.network_vpc) {
                        network = v;
                        oriRegionFlag = true;
                    }
                });
                if (oriRegionFlag) {
                    _IVPC.val(_currentInstanceConfig.vpc_uuid);
                } else {
                    //跨区域恢复时默认选中第一个
                    network = networkList[0];
                    _IVPC.val(networkList[0].network_vpc);
                }

                //子网
                let subnetList = network.network_subnet;
                $.each(subnetList, function (i, v) {
                    let option = '<option value="' + v.network_subnet_id + '" data-name="' + v.network_subnet_ip_number + '">' + v.network_subnet_ip_number + '(' + v.network_subnet_id + ')' + '</option>';
                    _ISubnet.append(option);
                });
                if (oriRegionFlag) {
                    _ISubnet.val(_currentInstanceConfig.subnet_uuid);
                } else {
                    _ISubnet.val(subnetList[0].network_subnet_id);
                }

                //安全组 下拉多选
                _security = [];
                $.each(network.network_security, function (i, v) {
                    _security.push({sg_name: v.network_security_name});

                    let selected = '';
                    $.each(_currentInstanceConfig.security, function (idx, val) {
                        if (v.network_security_name == val.sg_name) {
                            selected = 'selected';
                            return true;
                        }
                    });
                    let option = '<option value="' + v.network_security_name + '" ' + selected + '>' + v.network_security_name + '</option>';
                    _ISG.append(option);
                });
                _ISG.selectpicker('refresh');

                setInstanceInitFlags(region_uuid, available_zone, os_type);
            } else {
                operateResponseList(d);
            }
        });
    }

    //异步获取网络配置，增加实例类型参数
    const getNetworkConfigs = function (region_uuid, instance_type, reloadVpcFlag = true) {
        if (_currentInstanceConfig.networkLoadInstanceType == instance_type) return; //只加载一次
        if ('' == instance_type) return;
        _currentInstanceConfig.networkLoadInstanceType = instance_type;
        let p = {
            platform_uuid: _platformUuid,
            region_uuid: region_uuid,
            instance_type: instance_type
        };
        Metronic.blockUI({target: '#drawer-1',animate: true});
        pAjaxRequest(p, "/api/v1/cloud/instances/network_configs", "GET", function (d) {
            Metronic.unblockUI('#drawer-1');
            if (d.success) {
                _currentNetworkConfig = d.data.network_list;
                networkHandler(region_uuid, reloadVpcFlag, _currentNetworkConfig);
            } else {
                operateResponseList(d);
            }
        });
    }

    // 处理网络配置的加载
    const networkHandler = function (region_uuid, reloadVpcFlag, networkList) {
        let network = {}; //当前网络

        let oriRegionFlag = true; //是否原区域恢复
        if (region_uuid != _currentInstanceConfig.region_uuid) {
            oriRegionFlag = false;
        }

        //清空选项后再加载
        if (reloadVpcFlag) {
            _IVPC.empty();
        }

        _ISubnet.empty();
        _ISG.empty();
        _ISG.selectpicker('refresh');

        //vpc
        let vpc_uuid;
        if (reloadVpcFlag) {
            vpc_uuid = _currentInstanceConfig.vpc_uuid;
            if (!oriRegionFlag) {
                //跨区域恢复时默认选中第一个
                network = networkList[0];
                vpc_uuid = networkList[0].network_vpc;
            }
            $.each(networkList, function (i, v) {
                let option = '';
                switch (target_hypervisor) {
                    case CONF.VM_TYPE.AWS:
                        option = '<option value="' + v.network_vpc + '">' + v.network_vpc + '</option>';
                        break;
                    case CONF.VM_TYPE.HUAWEICLOUD:
                        option = '<option value="' + v.network_vpc + '" data-name="' + v.network_vpc_name + '">'
                            + v.network_vpc_name + '(' + v.network_vpc + ')' + '</option>';
                        break;
                }
                _IVPC.append(option);
                if (vpc_uuid == v.network_vpc) {
                    network = v;
                    oriRegionFlag = true;
                }
            });
            _IVPC.val(vpc_uuid);
        } else {
            // 切换vpc
            $.each(networkList, function (i, v) {
                if (_IVPC.val() == v.network_vpc) {
                    network = v;
                    oriRegionFlag = true;
                }
            });
        }

        //子网
        let subnetList = network.network_subnet;
        $.each(subnetList, function (i, v) {
            let option = '';
            switch (target_hypervisor) {
                case CONF.VM_TYPE.AWS:
                    option = '<option value="' + v.network_subnet_id + '">' + v.network_subnet_id + '</option>';
                    break;
                case CONF.VM_TYPE.HUAWEICLOUD:
                    option = '<option value="' + v.network_subnet_id + '" data-name="' + v.network_subnet_ip_number + '">'
                        + v.network_subnet_ip_number + '(' + v.network_subnet_id + ')' + '</option>';
                    break;
            }
            _ISubnet.append(option);
            if (_currentInstanceConfig.subnet_uuid == v.network_subnet_id) {
                _ISubnet.val(_currentInstanceConfig.subnet_uuid);
                if (CONF.VM_TYPE.AWS == target_hypervisor) {
                    _IAZ.val(v.network_subnet_zone);
                }
            }
        });

        //安全组 下拉多选
        _security = [];
        $.each(network.network_security, function (i, v) {
            _security.push({sg_name: v.network_security_name});

            let selected = '';
            $.each(_currentInstanceConfig.security, function (idx, val) {
                if (v.network_security_name == val.sg_name) {
                    selected = 'selected';
                    return true;
                }
            });
            let option = '<option value="' + v.network_security_name + '" ' + selected + '>' + v.network_security_name + '</option>';
            _ISG.append(option);
        });
        _ISG.selectpicker('refresh');
    }

    // 设置实例初始化标记
    const setInstanceInitFlags = function (region_uuid, available_zone, os_type) {
        configRegionUuid = region_uuid;
        configAvailableZone = available_zone;
        configOsType = os_type
    }

    //保存实例配置
    const saveInstanceConfigTemp = function () {
        //检查字段
        if (!_IName.val()) {
            UIToastr.showWarning(LANG.UI_RECOVERY_AWS_INSTANCE_NAME, LANG.UI_RECOVERY_AWS_INSTANCE_NAME_EMPTY_TIPS);
            return false;
        }
        if (!_IRegion.val()) {
            UIToastr.showWarning(LANG.UI_RECOVERY_AWS_REGION, LANG.UI_RECOVERY_AWS_REGION_EMPTY_TIPS);
            return false;
        }
        if (crossPlatformFlag && !_ICPU.val()) {
            UIToastr.showWarning(LANG.UI_RECOVERY_CLOUD_CPU_ARCH, LANG.UI_RECOVERY_CLOUD_CPU_ARCH_TIPS);
            return false;
        }
        if (crossPlatformFlag && !_IOS.val()) {
            UIToastr.showWarning(LANG.UI_RECOVERY_AWS_OS_TYPE, LANG.UI_RECOVERY_AWS_OS_TYPE_TIPS);
            return false;
        }
        if (crossPlatformFlag && !_IOSV.val()) {
            UIToastr.showWarning(LANG.UI_RECOVERY_CLOUD_OS_VERSION, LANG.UI_RECOVERY_CLOUD_OS_VERSION_TIPS);
            return false;
        }
        if (!_IType.val()) {
            UIToastr.showWarning(LANG.UI_RECOVERY_AWS_INSTANCE_TYPE, LANG.UI_RECOVERY_AWS_INSTANCE_TYPE_EMPTY_TIPS);
            return false;
        }
        if (!_IVPC.val()) {
            UIToastr.showWarning(LANG.UI_RECOVERY_AWS_VPC, LANG.UI_RECOVERY_AWS_VPC_EMPTY_TIPS);
            return false;
        }
        if (_crossFlag && 0 == _ISG.val().length) {
            UIToastr.showWarning(LANG.UI_RECOVERY_AWS_SECURITY, LANG.UI_RECOVERY_AWS_SECURITY_TIPS);
            return false;
        }

        if (CONF.VM_TYPE.HUAWEICLOUD == target_hypervisor) {
            // 华为云公有ip计费信息
            if (BILLING_BANDWIDTH === _IBillingType.val() && _IBillingValue.val() > 2000) {
                UIToastr.showWarning(LANG.UI_RECOVERY_CLOUD_BANDWIDTH_SIZE, LANG.UI_RECOVERY_CLOUD_BILLING_BANDWIDTH_TIPS);
                return false;
            }
            if (BILLING_TRAFFIC === _IBillingType.val() && _IBillingValue.val() > 300) {
                UIToastr.showWarning(LANG.UI_RECOVERY_CLOUD_BANDWIDTH_SIZE, LANG.UI_RECOVERY_CLOUD_BILLING_TRAFFIC_TIPS);
                return false;
            }
            if (_IPublicIP.prop('checked') && (!_IBillingValue.val() || _IBillingValue.val() == 0)) {
                UIToastr.showWarning(LANG.UI_RECOVERY_CLOUD_BANDWIDTH_SIZE, LANG.UI_RECOVERY_CLOUD_BANDWIDTH_SIZE_EMPTY_TIPS);
                return false;
            }
        }

        //获取选中的安全组名称数组转为对象数组
        let sg_names = _ISG.selectpicker('val'); //选中的安全组
        let security = []; //安全组对象
        $.each(_security, function (i, v) {
            if (-1 != $.inArray(v.sg_name, sg_names)) {
                security.push(v);
            }
        })

        let insConfig = {
            instance_name: _IName.val(),
            instance_type: _IType.val(),
            region_uuid: _IRegion.val(),
            region: _IRegion.find("option:selected").data('region_name'),
            region_cn_name: _IRegion.find("option:selected").data('region_cn_name'),
            available_zone: _IAZ.val(),
            // zone_cn_name: _IAZ.find("option:selected").data('zone_cn_name'),
            zone_cn_name: _IAZ.find("option:selected").text(),
            enterprise_project_id: _IProject.val(),
            vpc_uuid: _IVPC.val(),
            vpc_name: _IVPC.find('option:selected').data('name'),
            subnet_uuid: _ISubnet.val(),
            subnet_ip_number: _ISubnet.find('option:selected').data('name'),
            security: security,
            is_public_ip: _IPublicIP.prop('checked'),
            start_flag: _IStart.prop('checked'),
            timepoint_uuid: _currentInstanceConfig.timepoint_uuid,
            ami_id: _currentInstanceConfig.ami_id,
            instance_uuid: _currentInstanceConfig.instance_uuid,
            vm_name: _currentInstanceConfig.vm_name,
            ori_region: _currentInstanceConfig.ori_region,
            ori_available_zone: _currentInstanceConfig.ori_available_zone,
            pass_flag: currentPasswordFlag,
            encrypt_pass: btoa(_IPassword.val()),
            ori_hypervisor_type: _currentInstanceConfig.ori_hypervisor_type,
            os_type: _IOS.val() ?? '',
            os_version: _IOSV.val() ?? '',
            // retain_vols: retain_vols,
            billing_type: _IBillingType.val(),
            billing_value: parseInt(_IBillingValue.val()),
            architecture: _currentInstanceConfig.architecture,
            cpu_arch: _ICPU.val(),
        }
        let savedFlag = false; //是否已保存配置
        $.each(instance_configs, function (i, v) {
            if (v.timepoint_uuid == _currentInstanceConfig.timepoint_uuid) {
                //已保存则更新
                instance_configs[i] = insConfig;
                savedFlag = true;
                return true;
            }
        })
        if (!savedFlag) {
            //未保存则添加
            instance_configs.push(insConfig);
        }
        // 更新卷的配置
        let currentRegionInfo = {};
        $.each(_regionInfo, function (i, v) {
            if (insConfig.region_uuid == v.region_uuid) {
                currentRegionInfo = v;
                return true;
            }
        });
        let _tr = $('#instance-table').find('tr[data-uniqueid="' + _currentInstanceConfig.instance_uuid + '"]');
        $.each(volume_configs, function (i, v) {
            if (v.instance_uuid == _currentInstanceConfig.instance_uuid) {
                volume_configs[i].region = insConfig.region;
                volume_configs[i].region_cn_name = insConfig.region_cn_name;
                volume_configs[i].region_uuid = insConfig.region_uuid;
                volume_configs[i].available_zone = insConfig.available_zone ?? currentRegionInfo.available_zone[0].zone;
                volume_configs[i].zone_cn_name = insConfig.zone_cn_name ?? '';
                // 更新展开后卷的表格显示
                let updateObj = {};
                updateObj.available_zone = insConfig.zone_cn_name ? insConfig.zone_cn_name : insConfig.available_zone;
                if (insConfig.region != insConfig.ori_region) {
                    // 区域变更重置kms
                    volume_configs[i].disk_encrypt_flag = false;
                    volume_configs[i].disk_encrypt_key = '';
                    volume_configs[i].disk_encrypt_key_alias = '';
                    updateObj.encrypt_kms_alias = LANG.UI_PUBLIC_NOTHING;
                }
                let instanceUuid = clearString(v.instance_uuid);
                $(`#ins-vol-table_${instanceUuid}`).bootstrapTable('updateRow', {
                    index: $(`#ins-vol-table_${instanceUuid}`).find(`tr[data-uniqueid=${v.volume_uuid}]`).data('index'),
                    row: updateObj
                });
            }
        });

        //更新表格显示
        _tr.find('td').eq(2).text(insConfig.instance_name);
        _tr.find('td').eq(3).text(insConfig.region_cn_name ? insConfig.region_cn_name : insConfig.region);
        _tr.find('td').eq(4).text(insConfig.instance_type);
        switch (target_hypervisor) {
            case CONF.VM_TYPE.AWS:
                _tr.find('td').eq(5).text(insConfig.vpc_uuid);
                _tr.find('td').eq(6).text(insConfig.subnet_uuid);
                break;
            case CONF.VM_TYPE.HUAWEICLOUD:
                _tr.find('td').eq(5).text(insConfig.vpc_name + '(' + insConfig.vpc_uuid + ')');
                _tr.find('td').eq(6).text(insConfig.subnet_ip_number + '(' + insConfig.subnet_uuid + ')');
                break;
        }
        // 3个以内的安全组显示名称，超过则显示为...，没有显示--
        if (insConfig.security.length) {
            let sgNameArr = [];
            let count = 0;
            $.each(security, function (i, v) {
                if (count >= 3) return;
                sgNameArr.push(v.sg_name);
                count++;
            });
            let sgName = sgNameArr.join();
            if (insConfig.security.length > 3) {
                sgName += '...';
            }
            _tr.find('td').eq(7).text(sgName);
        } else {
            _tr.find('td').eq(7).text('--');
        }
        let is_public_ip = insConfig.is_public_ip ? LANG.UI_RECOVERY_AWS_ENABLE :  LANG.UI_RECOVERY_AWS_DISABLE;
        _tr.find('td').eq(8).text(is_public_ip);
        let start_flag = insConfig.start_flag ? LANG.UI_PUBLIC_YES : LANG.UI_PUBLIC_NO;
        _tr.find('td').eq(9).text(start_flag);

        //重置获取标记
        setInstanceInitFlags('', '', '');
    }

    //保存卷配置
    const saveVolConfigTemp = function () {
        //检查字段
        if (!_VName.val()) {
            UIToastr.showWarning(LANG.UI_RECOVERY_AWS_VOL_NAME, LANG.UI_RECOVERY_AWS_VOL_NAME_EMPTY_TIPS);
            return false;
        }
        if (!_VRegion.val()) {
            UIToastr.showWarning(LANG.UI_RECOVERY_AWS_REGION, LANG.UI_RECOVERY_AWS_REGION_EMPTY_TIPS);
            return false;
        }
        if (!_VAZ.val()) {
            UIToastr.showWarning(LANG.UI_RECOVERY_AWS_AVAILABLE_ZONE, LANG.UI_RECOVERY_AWS_AVAILABLE_ZONE_EMPTY_TIPS);
            return false;
        }
        // IOPS
        let iops_min = _VType.find('option:selected').data('iops_min');
        let iops_max = _VType.find('option:selected').data('iops_max');
        // 可修改时才检测
        if (_VIOPS.attr('readonly') != 'readonly' && (parseInt(_VIOPS.val()) < iops_min || parseInt(_VIOPS.val()) > iops_max)) {
            UIToastr.showWarning(LANG.UI_RECOVERY_CLOUD_IOPS, LANG.UI_RECOVERY_CLOUD_IOPS_ERROR_TIPS);
            return false;
        }

        let volConfig = {
            instance_uuid: _currentVolConfig.instance_uuid,
            volume_uuid: _currentVolConfig.volume_uuid,
            region_uuid: _VRegion.val(),
            region: _VRegion.find("option:selected").data('region_name'),
            region_cn_name: _VRegion.find("option:selected").data('region_cn_name'),
            available_zone: _VAZ.val(),
            // zone_cn_name: _VAZ.find("option:selected").data('zone_cn_name'),
            zone_cn_name: _VAZ.find("option:selected").text(),
            vol_new_name: _VName.val(),
            disk_encrypt_flag: '' !== _VKMS.val(),
            disk_encrypt_key: _VKMS.val(),
            disk_encrypt_key_alias: _VKMS.find("option:selected").text(),
            pass_flag: currentVolPasswordFlag,
            encrypt_pass: btoa(_VPassword.val()),
            volume_type: _currentVolConfig.volume_type,
            volume_disk_type: _VType.val(),
            disk_iops: _VIOPS.val(),
            totalSize: _currentVolConfig.totalSize,
            disk_delete_on_termination: (1 == recover_type && _VDiskDel.bootstrapSwitch('state')) ? 1 : 0, // 仅实例恢复时生效
        }
        let savedFlag = false; //是否已保存配置
        $.each(volume_configs, function (i, v) {
            if (v.volume_uuid == _currentVolConfig.volume_uuid && v.instance_uuid == _currentVolConfig.instance_uuid) {
                //已保存则更新
                volume_configs[i] = volConfig;
                savedFlag = true;
                return true;
            }
        })
        if (!savedFlag) {
            //未保存则添加
            volume_configs.push(volConfig);
        }

        //更新实例的数据加密密码
        $.each(instance_configs, function (i, v) {
            if (v.instance_uuid == volConfig.instance_uuid && btoa('') != volConfig.encrypt_pass) {
                v.encrypt_pass = volConfig.encrypt_pass;
                return true;
            }
        });

        //同一个实例的其他卷同步区域和可用区
        $.each(volume_configs, function (i, v) {
            if (v.instance_uuid == volConfig.instance_uuid && v.volume_uuid != volConfig.volume_uuid) {
                volume_configs[i].region = volConfig.region;
                volume_configs[i].region_cn_name = volConfig.region_cn_name;
                volume_configs[i].region_uuid = volConfig.region_uuid;
                volume_configs[i].available_zone = volConfig.available_zone;
                volume_configs[i].zone_cn_name = volConfig.zone_cn_name;
                //更新表格显示
                if (2 == $('#recovertype-select').val()) {
                    $('#vol-table').bootstrapTable('updateRow', {
                        index: $('#vol-table').find('tr[data-uniqueid=' + v.volume_uuid + ']').data('index'),
                        row: {
                            region: volConfig.region_cn_name ? volConfig.region_cn_name : volConfig.region,
                            available_zone: volConfig.zone_cn_name ? volConfig.zone_cn_name : volConfig.available_zone
                        }
                    });
                } else {
                    // 实例恢复时更新实例下卷的表格显示
                    let instanceUuid = clearString(volConfig.instance_uuid);
                    $(`#ins-vol-table_${instanceUuid}`).bootstrapTable('updateRow', {
                        index: $(`#ins-vol-table_${instanceUuid}`).find('tr[data-uniqueid="' + v.volume_uuid + '"]').data('index'),
                        row: {
                            available_zone: volConfig.zone_cn_name ? volConfig.zone_cn_name : volConfig.available_zone
                        }
                    });
                }
            }
        });

        //更新实例的区域和可用区
        $.each(instance_configs, function (i, v) {
            if (v.instance_uuid == volConfig.instance_uuid) {
                instance_configs[i].region = volConfig.region;
                instance_configs[i].region_cn_name = volConfig.region_cn_name;
                instance_configs[i].region_uuid = volConfig.region_uuid;
                instance_configs[i].available_zone = volConfig.available_zone;
                instance_configs[i].zone_cn_name = volConfig.zone_cn_name;
            }
        });

        //更新表格显示
        let region = volConfig.region_cn_name ? volConfig.region_cn_name : volConfig.region;
        let zone = volConfig.zone_cn_name ? volConfig.zone_cn_name : volConfig.available_zone;
        let kms = volConfig.disk_encrypt_key ? volConfig.disk_encrypt_key_alias : LANG.UI_PUBLIC_NOTHING;
        if (2 == $('#recovertype-select').val()) {
            $('#vol-table').bootstrapTable('updateRow', {
                index: $('#vol-table').find('tr[data-uniqueid=' + _currentVolConfig.volume_uuid + ']').data('index'),
                row: {
                    vol_new_name: volConfig.vol_new_name,
                    volume_disk_type: volConfig.volume_disk_type,
                    disk_iops: volConfig.disk_iops != 0 ? volConfig.disk_iops : '--',
                    region: region,
                    available_zone: zone,
                    encrypt_kms_alias: kms,
                }
            });
            // 处理更新失效
            let _tr = $('#vol-table').find('tr[data-uniqueid=' + _currentVolConfig.volume_uuid + ']');
            _tr.find('td').eq(8).text(region);
            _tr.find('td').eq(9).text(zone);
        } else {
            let instanceUuid = clearString(volConfig.instance_uuid);
            $(`#ins-vol-table_${instanceUuid}`).bootstrapTable('updateRow', {
                index: $(`#ins-vol-table_${instanceUuid}`).find('tr[data-uniqueid="' + volConfig.volume_uuid + '"]').data('index'),
                row: {
                    vol_new_name: volConfig.vol_new_name,
                    volume_disk_type: volConfig.volume_disk_type,
                    disk_iops: volConfig.disk_iops != 0 ? volConfig.disk_iops : '--',
                    available_zone: zone,
                    encrypt_kms_alias: kms,
                }
            });
        }
    }

    // 加载卷类型
    const initVolumeTypes = function () {
        let p = {};
        p.hypervisor_type = target_hypervisor;
        p.platform_uuid = _platformUuid;
        p.region_uuid = _VRegion.find(`option:selected`).val();
        p.region = _VRegion.find(`option:selected`).data('region_name');
        p.available_zone = _VAZ.find(`option:selected`).val();
        p.root_disk_size = parseInt(_currentVolConfig.totalSize) / (1024 * 1024 * 1024); // 转为GB
        Metronic.blockUI({target:'#drawer-2', animate: true});
        pAjaxRequest(p, "/api/v1/cloud/volume/types", "GET", function (d) {
            Metronic.unblockUI('#drawer-2');
            if (d.success) {
                let list = d.data.volume_type_list;
                _VType.empty();
                let oriMatchFlag = false; // 是否能匹配到原配置
                $.each(list, function(i, v) {
                    // 如果是根卷且该类型无法用于根卷则跳过
                    if ('root' === _currentVolConfig.volume_type && 'true' !== v.need_to_root) {
                        return;
                    }
                    let selected = _currentVolConfig.volume_disk_type === v.disk_type_name ? 'selected' : '';
                    if (selected) {
                        oriMatchFlag = true;
                    }
                    let iopsStr = `${LANG.UI_RECOVERY_CLOUD_MIN_IOPS}: ${v.iops_min},${LANG.UI_RECOVERY_CLOUD_MAX_IOPS}: ${v.iops_max}`;
                    if (0 == v.iops_default) {
                        // 如果是0， 表示此卷类型iops不适用
                        iopsStr = LANG.UI_RECOVERY_CLOUD_IOPS_NOT_USE;
                    }
                    let diskTypeDesKey = `UI_RECOVERY_CLOUD_DISK_TYPE_${v.disk_type_name}`;
                    let diskTypeName = 'undefined' !== typeof LANG[diskTypeDesKey] ? LANG[diskTypeDesKey] : v.disk_type_name;
                    let option = `<option value="${v.disk_type_name}" data-iops_modify="${v.modify_flag}" data-iops_default="${v.iops_default}" data-iops_min="${v.iops_min}" data-iops_max="${v.iops_max}" ${selected}>
						${diskTypeName} (${iopsStr})
						</option>`;
                    _VType.append(option);
                });
                changeVolumeType();
                if (oriMatchFlag && _currentVolConfig.disk_iops != 0) {
                    // 覆盖iops的默认值
                    $('#diskIopsDiv').spinner('value', _currentVolConfig.disk_iops);
                }
            } else {
                operateResponseList(d);
            }
        });
    }

    // 切换卷类型
    const changeVolumeType = function () {
        let val = _VType.val();
        let _option = _VType.find(`option[value=${val}]`);
        let iops_modify = _option.data('iops_modify');
        let iops = _option.data('iops_default');
        let iops_min = _option.data('iops_min');
        let iops_max = _option.data('iops_max');
        if (0 == iops) {
            // 不适用则隐藏
            $('.disk-iops-div').hide();
        } else {
            $('.disk-iops-div').show();
        }
        if (iops_modify) {
            $('#diskIops').attr('readonly', false);
            $('#diskIops').next('.spinner-buttons').show();
        } else {
            // 不能修改则显示为只读，且隐藏加减按钮
            $('#diskIops').attr('readonly', true);
            $('#diskIops').next('.spinner-buttons').hide();
        }
        $('#diskIopsDiv').spinner({min: iops_min, max: iops_max});
        $('#diskIopsDiv').spinner('value', iops);
    }

    //卷恢复切换区域
    const volRegionChange = function (new_region) {
        //加载对应可用区
        let option = '';
        $.each(_regionInfo, function (i, v) {
            if (v.region_uuid == new_region) {
                $.each(v.available_zone, function (i, v) {
                    let selected = v.zone == _currentVolConfig.available_zone ? 'selected' : '';
                    let zone = v.zone_cn_name ? v.zone_cn_name : v.zone;
                    if(CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw"){
                        option += `<option value="${v.zone}" data-zone_cn_name="${zone}" ${selected}>${zone}</option>`;
                    }else{
                        //这个地方不能提语言包，因为英文版数据库这里存的就是"可用区"这个中文，提了语言包会识别不到
                        let new_en_zone = v.zone_cn_name ? v.zone_cn_name.replace(validZone,'AZ'): v.zone;
                        option += `<option value="${v.zone}" data-zone_cn_name="${zone}" ${selected}>${new_en_zone}</option>`;
                    }
                    // option += `<option value="${v.zone}" data-zone_cn_name="${zone}" ${selected}>${zone}</option>`;
                });
            }
        });
        _VAZ.empty();
        _VAZ.append(option);

        //刷新kms列表
        initKmsList(_currentVolConfig.disk_encrypt_key_alias, _currentVolConfig.disk_encrypt_key);
    }

    // 重新加载操作系统版本
    const reloadOSVersionSelect = (os_info_list, _el, osVersion = 0) => {
        let osType = $(_el).find('option:selected').data('os_value');
        let cpuArch = $(`select[name=cpu_arch]`).val();
        let option = getOSVersionSelect(os_info_list, cpuArch, osType, osVersion);
        _IOSV.empty().append(option);
        _IOSV.selectpicker('refresh');
        _IOSV.next(`div`).find(`ul`).css({'height': '400px!important'});
    }

    // 获取操作系统版本选项
    const getOSVersionSelect = (os_info_list, cpu_arch, osType, osVersion) => {
        if (![1, 2, 3, 4].includes(cpu_arch) || typeof cpu_arch === 'undefined') {
            // 未知时先赋值为第一个
            cpu_arch = cpu_info_list[0].arch_type;
        }
        let os_version_list = [];
        for (let i in os_info_list) {
            if (cpu_arch == os_info_list[i].arch_type) {
                os_version_list = os_info_list[i].os_list;
            }
        }
        let option = `<option value="0"> ${LANG.UI_JOB_SELECT} </option>`;
        $.each(os_version_list, function (i, v) {
            if (0 == v.os_version) {
                return;
            }
            if (v.os_type == osType) {
                if (v.os_version == osVersion) {
                    //默认选中
                    option += `<option value="${v.os_version}" selected="selected">${v.os_version_name}(${interfacesbak})</option>`;
                } else {
                    option += `<option value="${v.os_version}">${v.os_version_name}</option>`;
                }
            }
        });
        return option;
    }

    // ----- 工具函数 -----
    const isRootVol = function (root_disk_flag) {
        return root_disk_flag == 1;
    }

    //替换特殊字符为下划线
    const clearString = function (s){
        var rs = "";
        for (var i = 0; i < s.length; i++) {
            rs = rs+s.substr(i, 1).replace(_VMNAMEREG, '_');
        }
        return rs;
    }

    const getDatetime = function (time) {
        let polishing = function (d) {
            return d < 10 ? '0' + d : d;
        }
        let date = new Date(time);
        let Y = date.getFullYear().toString();
        let M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1).toString();
        let D = polishing(date.getDate()).toString();
        let h = polishing(date.getHours()).toString();
        let m = polishing(date.getMinutes()).toString();
        let s = polishing(date.getSeconds()).toString();
        return Y + M + D + h + m + s;
    }

    /**
     * 初始化配置表格，调用方式如下：
     * $.fn.initAwsRecoveryConfig({
     *                 source_hypervisor: point.point_info[0].hypervisor, // 时间点的源虚拟化类型
     *                 target_hypervisor: node.hypervisor, // 目标虚拟化类型
     *                 timepoint_uuids: timepoints_uuids, // 时间点uuid的数组
     *                 platform_uuid: node.vcuuid, // 目标平台uuid
     *                 recover_type: 1, // 恢复类型：1-实例，2-卷
     *                 platform_data: _platformData, // 全部云平台信息，原平台恢复传入
     *             });
     * @param params
     */
    $.fn.initAwsRecoveryConfig = function (params) {
        source_hypervisor = params.source_hypervisor;
        target_hypervisor = params.target_hypervisor;
        crossPlatformFlag = source_hypervisor !== target_hypervisor;
        timepoint_uuids = params.timepoint_uuids;
        _platformUuid = params.platform_uuid;
        recover_type = params.recover_type;
        _platformData = params.platform_data;

        // 跨平台获取_platformData
        if (!_platformData) {
            pAjaxRequest({}, "/api/v1/cloud/all_platforms", "GET", function (d) {
                _platformData = d.data.rows;
                let options = '';
                $.each(_platformData, function (i, v) {
                    options += '<option value="' + v.platform_uuid + '" data-hypervisor="' + v.hypervisor_type + '">' + v.nickname + '</option>';
                    if (_platformUuid == v.platform_uuid && CONF.VM_TYPE.HUAWEICLOUD == v.hypervisor_type) {
                        loadEnterpriseProjectList(v.enterprise_projects);
                    }
                })
            }, false);
        }

        switch (target_hypervisor) {
            case CONF.VM_TYPE.AWS:
                DEFAULT_ARCHITECTURE = 'x86_64';
                break;
            case CONF.VM_TYPE.HUAWEICLOUD:
                DEFAULT_ARCHITECTURE = 'x86';

                break;
            default:
                break;
        }
        if (1 == recover_type) {
            initInstanceTable(); //初始化实例恢复配置表格
        } else {
            initVolTable(); //初始化卷恢复配置表格
        }
        selectPlatformHandler(target_hypervisor);
        initListener();
        $('[data-toggle="tooltip"]').tooltip();
    }

    /**
     * 获取所需参数，可直接赋值给data.recover_info
     */
    $.fn.getAwsRecoveryConfig = function () {
        //未初始化恢复目的云平台
        if ('' === _platformUuid) {
            UIToastr.showWarning(LANG.UI_RECOVERY_SELECT_CLOUD_PLATFORM);
            return false;
        }
        // 检测恢复配置
        let verifyFlag = true;
        if (1 == recover_type) {
            if (!instance_configs.length) {
                UIToastr.showWarning(LANG.UI_RECOVERY_AWS_INSTANCE_RECOVER, LANG.UI_RECOVERY_AWS_INSTANCE_RECOVER_NULL_TIPS);
                return false;
            }
            $.each(instance_configs, function (i, v) {
                let value = $.trim(v.instance_name);
                if ('' === value) {
                    UIToastr.showWarning(LANG.UI_RECOVERY_AWS_INSTANCE_RECOVER, LANG.UI_RECOVERY_AWS_INSTANCE_NAME_EMPTY_TIPS);
                    verifyFlag = false;
                    return false;
                }

                //跨平台实例恢复时安全组不能为空
                if (_crossFlag && !v.security.length) {
                    UIToastr.showWarning(LANG.UI_RECOVERY_AWS_SECURITY, LANG.UI_RECOVERY_AWS_SECURITY_TIPS);
                    verifyFlag = false;
                    return false;
                }
                //跨平台实例恢复时cpu架构不能为空
                if (crossPlatformFlag && (!v.cpu_arch || '' === v.cpu_arch)) {
                    UIToastr.showWarning(LANG.UI_RECOVERY_CLOUD_CPU_ARCH, LANG.UI_RECOVERY_CLOUD_CPU_ARCH_TIPS);
                    verifyFlag = false;
                    return false;
                }
                //跨平台实例恢复时操作系统不能为空
                if (crossPlatformFlag && (!v.os_type || '' === v.os_type)) {
                    UIToastr.showWarning(LANG.UI_RECOVERY_AWS_OS_TYPE, LANG.UI_RECOVERY_AWS_OS_TYPE_TIPS);
                    verifyFlag = false;
                    return false;
                }
                if (crossPlatformFlag && (!v.os_version || '' === v.os_version)) {
                    UIToastr.showWarning(LANG.UI_RECOVERY_CLOUD_OS_VERSION, LANG.UI_RECOVERY_CLOUD_OS_VERSION_TIPS);
                    verifyFlag = false;
                    return false;
                }
                // 区域
                if ('' === v.region) {
                    UIToastr.showWarning(LANG.UI_RECOVERY_AWS_INSTANCE_RECOVER, LANG.UI_RECOVERY_AWS_INSTANCE_RECOVER_REGION_NULL_TIPS);
                    verifyFlag = false;
                    return false;
                }
                // 实例类型
                if ('' === v.instance_type) {
                    UIToastr.showWarning(LANG.UI_RECOVERY_AWS_INSTANCE_RECOVER, LANG.UI_RECOVERY_AWS_INSTANCE_RECOVER_TYPE_NULL_TIPS);
                    verifyFlag = false;
                    return false;
                }
                // VPC
                if ('' === v.vpc_uuid) {
                    UIToastr.showWarning(LANG.UI_RECOVERY_AWS_INSTANCE_RECOVER, LANG.UI_RECOVERY_AWS_INSTANCE_RECOVER_VPC_NULL_TIPS);
                    verifyFlag = false;
                    return false;
                }
            });
        } else {
            $.each(volume_configs, function (i, v) {
                let value = $.trim(v.vol_new_name);
                if ("" === value) {
                    UIToastr.showWarning(LANG.UI_RECOVERY_AWS_VOL_NAME, LANG.UI_RECOVERY_AWS_VOL_NAME_EMPTY_TIPS);
                    verifyFlag = false;
                    return false;
                }
                // 检查区域
                if ('' === v.region) {
                    UIToastr.showWarning(LANG.UI_RECOVERY_AWS_REGION, LANG.UI_RECOVERY_AWS_REGION_EMPTY_TIPS);
                    verifyFlag = false;
                    return false;
                }
                // 检查可用区
                if ('' === v.available_zone) {
                    UIToastr.showWarning(LANG.UI_RECOVERY_AWS_AVAILABLE_ZONE, LANG.UI_RECOVERY_AWS_AVAILABLE_ZONE_EMPTY_TIPS);
                    verifyFlag = false;
                    return false;
                }
            });
        }
        if (!verifyFlag) {
            return false;
        }
        if (2 == recover_type && !volume_configs.length) {
            UIToastr.showWarning(LANG.UI_RECOVERY_AWS_VOL_RECOVER, LANG.UI_RECOVERY_AWS_VOL_RECOVER_NULL_TIPS);
            return false;
        }

        return {
            platform_uuid: _platformUuid,
            recover_type: recover_type,
            hypervisor_type: target_hypervisor,
            instance_configs: instance_configs,
            volume_configs: volume_configs,
            platform: _platformName
        }
    }

    /**
     * 获取当前操作实例的配置
     */
    $.fn.getAwsRecoveryCurrentInstanceConfig = function () {
        return _currentInstanceConfig;
    }

    /**
     * 获取时间点病毒扫描状态
     */
    $.fn.getAwsRecoveryVirusScanStatus = function () {
        return {
            isNoScan: isNoScan,
            isHeathy: isHeathy,
            isInfect: isInfect,
            isInfectButUnfinished: isInfectButUnfinished
        };
    }
})(jQuery)