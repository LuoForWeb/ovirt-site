(($) => {
    /**
     * 用法：
     * 1、php文件中：
     * <?php include ('./cm_volcdp_vm_temp_config.php');?>
     * 再引入cm_volcdp_vm_temp_config.js
     *2、 js中初始化
     *  $.fn.takeoverConfig.init({
     *             agent_uuid: "",  // 源机agent_uuid
     *             node_uuid:"",  // 节点nodeuuid
     *             source_netcard_conf: _agentNetworkInfo,  // 源机网卡信息
     *         });
     * 3、获取返回值
     *   getData:(data) =>{
     *     return data;
     *  }
     *
     *
     */

    let initTaskTableFlag = false;
    let virtualization_status = false;
    let isStorageResourcePool = false;
    let old_slot_core_num = 0;
    let vm_object = {
        "vm_temp_object":{
            "takeover_vm":{
                "uuid":"",
                "hypervisor_type":"108",
                "node_uuid":"",
                "config":{
                    "vm_name":"",
                    "vm_uuid":"",   // 要和uuid一致
                    "cpu_mode":"",
                    "cpu_slot_num":"",
                    "cpu_slot_core_num":"",
                    "memoryMB":"20",
                    "os":{
                        "firmware_type":"1",
                    },
                    "interface_model_type":"1",
                    "disk_target_bus":"1",
                    "interfaces":[],
                    "disks":[],
                    "floppys":[],
                    "cdroms":[],
                    "hostdevs":[],
                }
            },
            "host_reset_name":"",
            "cross_platform_flag":2,
            "cross_platform_conf":"",
            "disk_config_str":"",
        },
        "vm_temp_des":"",
        "takeover_business_ip_map":[]
    };

    // 源机uuid
    let agent_uuid = '';
    //
    let timepoint_uuid = '';
    // 节点uuid
    let node_uuid = '';
    let task_type = '';
    let isEfi = 1;
    let disk_config_params = {};
    let disk_uuids = [];
    let os_type = '';
    let node_uuid_list = [];
    // 源机网卡信息
    let source_netcard_conf;
    // 网络列表
    let networkList;
    let defaultNetwork;

    // 重置主机标志
    let renameFlag = false;

    let memory_size; // 内存
    let memory_size_des = '';
    let memType = 1; // 物理内存最大的展示单位 默认是MB ,2 表示GB

    let hardDiskBusType = ['ide','virtio','sata','scsi'];
    let nodeuuid_selected = '';
    let node_uuid_list_all = [];
    let node_uuid_list_checked = [];
    let nodeNetworkInitFlag = {};

    /**
     * 主机名称等默认信息
     */
    let getVirtualDefault = () => {
        pAjaxRequest({}, "/api/v1/virtual/default", "GET", (result) => {
            if (result.code == 0) {
                let data = result.data;
                $('input[name=modal_vm_name]').val(data.name);   // 主机名称
            }
        });
    }

    /**
     * CPU配置
     */
    let getHardwareConfig = (agent_uuid,task_type) => {
        let params = {
            'agent_uuid':agent_uuid,
            'task_type':task_type,
            'timepoint_uuid':timepoint_uuid
        };
        pAjaxRequest(params,'/api/v1/complete_machine_volcdp/hardware_config','GET',(result) => {
            let data = result.data;
            getSelected('slots_num_select',data.slots_num);
            getMemoryUnit(data.mem_total_size.unit);
            old_slot_core_num = data.slot_core_num;
            $('#modal_v_cpu').val(data.slot_core_num);
            $('#modal_v_memory').val(data.mem_total_size.value);
            memory_size = parseInt(data.mem_total_size_value);
            memory_size_des = data.mem_total_size;
            // 1表示 bios，2表示 efi
            isEfi = data.boot_type
            if(!!isEfi){
                initModalBootFirmware(isEfi);
            }else{
                initModalBootFirmware(1);
            }
        })
    }

    /**
     * 插槽数
     */
    let getSelected = (id,value) => {
        let selectEle = document.getElementById(id);
        for(let i =0;i<selectEle.options.length;i++){
            if(selectEle.options[i].value == value){
                selectEle.options[i].selected = true;
            }
        }
    }

    /**
     * 内存大小单位
     */
    let getMemoryUnit = (unit) => {
        let selectEle = document.getElementById('modal_v_memory_type');
        for(let i =0;i<selectEle.options.length;i++){
            if(selectEle.options[i].text == unit){
                selectEle.options[i].selected = true;
            }
        }
    }


    let getDiskInfo = () => {
        Metronic.blockUI({target:'#standby_map_conf_drawer', animate: true, cenrerY: true,});
        pAjaxRequest(disk_config_params,'/api/v1/complete_machine_os/get_recover_target_info','POST',(d)=>{
            Metronic.unblockUI("#standby_map_conf_drawer");
            let data = [];
            let diskList = {};
            if(task_type == CONF.TASK_TYPE.VOL_CDP_BACKUP){
                // 备份
                let target_data = d.data.backup_target_data;
                data = target_data.filter(item => disk_uuids.includes(item.dev_uuid));
            }else if(task_type == CONF.TASK_TYPE.VOL_CDP_TAKEOVER){
                // 接管
                data = d.data.origin_data;
            }
            diskList.rows = data.filter(item=>item.is_disk_flag == true);
            initDiskConfigTable(diskList);
        })
    }

    /**
     * 磁盘配置-表格
     */
    let initDiskConfigTable = (diskList) => {
        let options = {
            data:diskList,
            buttonsToolbar: '', // 自定义按钮工具栏class
            rightToolbarClass: '',
            tableContentWrapper: '',
            toolbarId: '',
            vin_toolbar: '',
            // 搜索
            searchInput: false,
            showSearchButton: false,
            searchAlign: 'right',
            // 排序
            sortable: false,
            showButtonText: false,
            clickToSelect: true,
            // 分页
            pagination: false,
            sidePagination: 'client',
            showExport: false,
            showColumns: false,
            columns:[
                {
                    title: LANG.UI_VM_SETTING_V2_RECOVERY_DISK_NAME,
                    field: 'dev_name',
                    width: '30',
                    widthUnit: '%',
                    formatter:(value)=>{
                        return value;
                    }
                },{
                    title: LANG.UI_PUBLIC_TOTAL_SIZE,
                    field: 'total_size',
                    width: '30',
                    widthUnit: '%',
                    formatter:(bytes,decimals = 2) => {
                        bytes = parseInt(bytes);
                        if (bytes === 0) return '0 Bytes';

                        const k = 1024;
                        const dm = decimals < 0 ? 0 : decimals;
                        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

                        const i = Math.floor(Math.log(bytes) / Math.log(k));

                        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
                    }
                },{
                    title: LANG.UI_VIRTUAL_MACHINE_HARD_DISK_BUS_TYPE,
                    field: 'hard_disk_bus_type',
                    width: '50',
                    widthUnit: '%',
                    clickToSelect: false,
                    formatter: (value,row,index) => {
                        return '<select name="hard_disk_bus_type" class="form-control select2me option-select" data-row-index="' + index + '">' +
                            '<option value="1">ide</option>' +
                            '<option value="2" selected="">virtio</option>' +
                            '<option value="3">sata</option>' +
                            '<option value="4">scsi</option>' +
                            '</select>';
                    },
                }],
        }
        if (!initTaskTableFlag) {
            $('#diskConfigTable').baseTableConfig().init(options);
            initTaskTableFlag = true;
        } else {
            // 如果已经初始化销毁表格再初始化新表
            // 销毁表格
            $('#diskConfigTable').bootstrapTable('destroy');
            $('#diskConfigTable').baseTableConfig().init(options);
        }
    }

    let getDiskData = () => {
        // 获取选中的行
        var selectedRows = $('#diskConfigTable').bootstrapTable('getData');
        var selectedValues = [];
        if(selectedRows.length == 0){
            return [{'uuid':'','target_bus':0}];
        }
        selectedRows.forEach(function (row, index) {
            let selectedObj = {};
            // 获取当前行在表格数据中的索引
            var rowDataIndex = $('#diskConfigTable').bootstrapTable('getData').indexOf(row);
            // 根据索引找到对应的下拉框
            var selectElement = $('.option-select[data-row-index="' + rowDataIndex + '"]');
            // 获取下拉框选中的值
            var selectedValue = selectElement.val();
            selectedObj.dev_name = row.dev_name;
            selectedObj.uuid = row.dev_uuid;
            selectedObj.target_bus = selectedValue;
            selectedObj.total_size = row.total_size;
            selectedValues.push(selectedObj);
        });
        return selectedValues;
    }

    /**
     * 网卡信息-表格
     */
    let initnetworkCardTable = () => {
        // 初始化网络配置插件结构体
        var networkInit = [];
        //遍历nic_list数组
        for (var i = 0; i < source_netcard_conf.length; i++) {
            var eachNetwork = {};

            eachNetwork.checked = true;
            eachNetwork.chk_disabled = false;
            eachNetwork.net_uuid = getUuid();
            eachNetwork.net_name = source_netcard_conf[i]['name'];
            eachNetwork.net_mac = source_netcard_conf[i]['mac_address'];
            eachNetwork.mac_address = source_netcard_conf[i]['mac_address']; // 源MAC地址
            eachNetwork.show_advance_flag = true;
            eachNetwork.change_net_flag = false;
            eachNetwork.show_ip_switch_nav_tabs_flag = false;
            eachNetwork.network_list = networkList;
            eachNetwork.network = defaultNetwork;
            eachNetwork.net_bus_list = [{  // 网卡类型下拉框值
                name: 'VirtIO',
                value: '1',
            }, {
                name: 'e1000',
                value: '2',
            },{
                name: 'rtl8139',
                value: '3',
            }];
            eachNetwork.net_bus ='3';  // 网卡类型的默认选中值
            eachNetwork.change_net_bus_flag = true;  // 是否允许修改网卡类型【true允许 false不允许】
            eachNetwork.mac_type = 1;  // MAC地址的默认选中值【1自动生成 2自定义MAC 3保留MAC】，枚举值从 $.fn.NetworkConfig.MAC_TYPE_ENUM 中获取

            eachNetwork.ipv6_set = {
                config_type: $.fn.NetworkConfig.CONFIG_TYPE_ENUM.auto,
                ip_set: [],
                dns1: '',
                dns2: ''
            };
            eachNetwork.ipv4_set = {
                config_type: $.fn.NetworkConfig.CONFIG_TYPE_ENUM.auto,
                ip_set: [],
                dns1: '',
                dns2: ''
            };
            //获取ip类型
            let ip_set = source_netcard_conf[i]['ip_set'];
            for (var j = 0; j < ip_set.length; j++) {
                let eachIp_set = {};
                eachIp_set.ip = ip_set[j].ip_addr;
                eachIp_set.netmask = ip_set[j].netmask;
                if(ip_set[j].ip_type == 2){
                    // ipv6
                    eachNetwork.ipv6_set.gateway = source_netcard_conf[i]['ipv6_gateway_address'];
                    eachNetwork.ipv6_set.ip_set.push(eachIp_set);
                } else {
                    eachNetwork.ipv4_set.dns1 = source_netcard_conf[i]['dns'];
                    eachNetwork.ipv4_set.dns2 = source_netcard_conf[i]['dns2'];
                    eachNetwork.ipv4_set.gateway = source_netcard_conf[i]['gateway_address'];
                    eachNetwork.ipv4_set.ip_set.push(eachIp_set);
                }
            }

            networkInit.push(eachNetwork);
        }
        $('.node-network-config-cls').hide();
        $('#networkConfig_'+nodeuuid_selected).show();
        if (nodeNetworkInitFlag[nodeuuid_selected]) {
            return;
        }
        nodeNetworkInitFlag[nodeuuid_selected] = true;
        $.fn.NetworkConfig.init($('#networkConfig_'+nodeuuid_selected), {
            vm_net_list: networkInit,
            show_advance_flag: true,  // 是否显示高级
        }, $.fn.NetworkConfig.USAGE_TYPE_ENUM.VM);
        setTimeout(()=>{
            $(`#networkConfig_${nodeuuid_selected} .networkConfig_recoveryToNetwork .th-inner`).html(LANG.UI_VIRTUAL_MACHINE_TAKE_OVER_TO_THE_NETWORK);
        },0)
    }

    /**
     * 获取网络列表
     */
    let getNetworkList = () => {
        pAjaxRequest({'offset':0,'limit':10,'node_uuid':nodeuuid_selected},'/api/v1/virtual/network','GET',(res)=>{
            let rows = res.data.rows;
            rows.unshift({'name':LANG.UI_MACHINE_OS_PLEASE_SELECT,'uuid':''});
            if(rows.length == 0){
                UIToastr.showWarning('',LANG.UI_VIRTUAL_MACHINE_PLEASE_ADD_NETWORK_FIRST);
                return;
            }
            networkList = rows.filter((item)=>item.name != 'isolate_network');  // 暂时先写死
            defaultNetwork = networkList[0].uuid;
            networkList = networkList.map(row => {
                return {
                    ...row,
                    value: row.uuid,
                }
            });
        },false)
    }

    /**
     * 初始化spinner
     */
    let initSpinner = () => {
        $('#max_wait_time').spinner({
            value: 15,
            step: 5,
            min: 5,
            max: 60
        });
    }

    /**
     *
     */
    let addListeners = () => {
        $("#modal_vm_name").on("input", function(e) {
            // 获取当前输入框的值
            var value = $(this).val();

            var newValue = value.replace(/[^a-zA-Z0-9_-]/g, '');
            if (newValue != value) {
                // 给出提示
                UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS, LANG.UI_VM_MACHINE_NAME_LIMIT_TIPS);
            }
            $(this).val(newValue);

        });
        $('#rename').on('switchChange.bootstrapSwitch',rename);
        $('input[name=rehostname]').on('blur',blurHostName);
        $('#node_select').on('change',changeNodeSelect);
        $('#standby_map_conf_drawer .cancel').on('click', function () {
            $('#standby_map_conf_drawer').drawer('hide');
        });
    }

    /**
     * @function 生成uuid
     */
    let getUuid = () => {
        let len = 36;//36长度
        let radix = 16;//16进制
        let chars = '0123456789abcdefghijklmnopqrstuvwxyz'.split('');
        let uuid = [], i;
        radix = radix || chars.length;
        let r;
        uuid[8] = uuid[13] = uuid[18] = uuid[23] = '-';
        uuid[14] = '4';
        for(i = 0; i < 36; i++) {
            if(!uuid[i]) {
                r = 0 | Math.random() * 16;
                uuid[i] = chars[(i == 19) ? (r & 0x3) | 0x8 : r];
            }
        }
        return uuid.join('');
    }

    /**
     * 配置的内嵌虚拟主机提交
     */
    let getVmSubmitInfo = () => {
        // 主机名称
        let modalVmNameStr = $('input[name=modal_vm_name]').val();
        vm_object.vm_temp_object.takeover_vm.config.vm_name = modalVmNameStr;
        // 重置主机名
        let rehostname = $('input[name=rehostname]').val();
        vm_object.vm_temp_object.host_reset_name = renameFlag ? rehostname : '';
        // 插槽数
        let slotsnumStr = $('#slots_num_select option:selected').text();
        let cpuSlotNum = $('#slots_num_select option:selected').val();
        vm_object.vm_temp_object.takeover_vm.config.cpu_slot_num = cpuSlotNum;
        // 每个插槽核心数
        let slotcorenumStr = $('#modal_v_cpu').val() + LANG.UI_VM_MACHINE_CPU_UNIT;
        let slotCoreNum = $('#modal_v_cpu').val();
        vm_object.vm_temp_object.takeover_vm.config.cpu_slot_core_num = slotCoreNum;

        // cpu大小限制
        var cpu_max = parseInt($('#modal_least_cpu').text());
        if (slotCoreNum * cpuSlotNum > cpu_max && !isStorageResourcePool) {
            UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS, LANG.UI_VM_MACHINE_MAX_CPU_NUM + ':'+ cpu_max + LANG.UI_VM_MACHINE_CPU_UNIT);
            return false;
        }
        if(slotCoreNum > old_slot_core_num && isStorageResourcePool){
            UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS, LANG.UI_VM_MACHINE_MAX_CPU_NUM + ':'+ old_slot_core_num + LANG.UI_VM_MACHINE_CPU_UNIT);
            return false;
        }
        // CPU类型
        let cputypeStr = $('#cpu_type_select option:selected').text();
        let cpuMode = $('#cpu_type_select option:selected').val();
        vm_object.vm_temp_object.takeover_vm.config.cpu_mode =cpuMode;
        // 内存
        let unitVal = $('#modal_v_memory_type option:selected').val();
        let memoryStr = $('#modal_v_memory').val() + $('#modal_v_memory_type option:selected').text();
        let memoryMB = Math.pow(1024,unitVal)* parseFloat($('#modal_v_memory').val());
        vm_object.vm_temp_object.takeover_vm.config.memoryMB = memoryMB;

        // 内存大小限制
        var memory_max = parseFloat($('#modal_least_memory').text());
        let memory_type = parseInt($('#modal_v_memory_type option:selected').val() + 1);
        var new_memory = memoryMB;  // 单位为MB
        if (memType == 2) {
            memory_max = memory_max * 1024;  // 单位为MB
        }
        if (memory_max < new_memory) {
            var memory_max_text = parseFloat($('#modal_least_memory').text());
            let memory_max_unit = $('#modal_least_memory_unit').text();
            let msg = memory_max_text + memory_max_unit;
            UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS,LANG.UI_VM_MACHINE_MAX_MEMS_NUM + ':'+ msg);
            return false;
        }

        // 磁盘配置
        vm_object.vm_temp_object.takeover_vm.config.disks = getDiskData();
        // 网络配置

        let interfaces = vm_object.vm_temp_object.takeover_vm.config.interfaces;
        vm_object.vm_temp_object.takeover_vm.uuid = getUuid();
        vm_object.vm_temp_object.takeover_vm.config.vm_uuid = vm_object.vm_temp_object.takeover_vm.uuid;
        let netConfigStr ='';
        // 网络配置
        let networkSettings = [];
        // IPV4配置
        let ipv4Config = [];
        // IPV6配置
        let ipv6Config = [];
        // 网络配置
        vm_object.takeover_business_ip_map = [];
        // 获取选中的网络配置
        const arr = node_uuid_list_checked.filter((item, index) => node_uuid_list_checked.indexOf(item) === index); // 去重
        let networkConfigs = {};
        for(const node of arr){
            let network_selected = [];
            network_selected = $.fn.NetworkConfig.getData($('#networkConfig_'+node)).filter(item => item.checked);
            if(network_selected.length > 0){
                networkConfigs[node] = network_selected;
            }
        }
        if(JSON.stringify(networkConfigs) === '{}'){
            UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS,LANG.UI_VIRTUAL_MACHINE_SELECT_NETWORK_CONFIG);
            return false;
        }
        let networkConfigs_valid = {};
        for(const node of arr){
            let network_selected = [];
            network_selected = networkConfigs[node].filter(item => item.network != '');
            if(network_selected.length > 0){
                networkConfigs_valid[node] = network_selected;
            }
        }
        if(JSON.stringify(networkConfigs_valid) === '{}'){
            UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS,LANG.UI_VIRTUAL_MACHINE_SELECT_NETWORK_CONFIG_TIPS);
            return false;
        }
        for(const item of node_uuid_list_all){
            if(networkConfigs_valid[item] == undefined){
                continue;
            }
            if(networkConfigs_valid[item].length != 0){
                let networkConfig = networkConfigs_valid[item];
                for(let i=0;i<networkConfig.length;i++){
                    if(networkConfig[i].checked){
                        // 选中网卡
                        let interfacesList = {};
                        interfacesList.uuid = getUuid();  // 随机生成uuid
                        interfacesList.source_network = '';
                        interfacesList.source_network_set = {
                            [item]: networkConfig[i].network_name
                        };
                        interfacesList.model_type = networkConfig[i].net_bus;
                        interfacesList.nic_mac_type = networkConfig[i].mac_type;
                        interfacesList.nic_mac = networkConfig[i].mac_address;

                        interfacesList.nic_name = networkConfig[i].net_name;                    // 目标虚拟机网卡名称
                        interfacesList.name = networkConfig[i].net_name;                        // 目标虚拟机网卡名称
                        // ipv4
                        interfacesList.DNS1 = networkConfig[i].ipv4_set.dns1;
                        interfacesList.DNS2 = networkConfig[i].ipv4_set.dns2;
                        interfacesList.gateway_address = networkConfig[i].ipv4_set.gateway;     // 目标网关地址
                        let nic_info = {};
                        nic_info.nic_gateway = networkConfig[i].ipv4_set.gateway;
                        nic_info.nic_mac = networkConfig[i].net_mac;
                        nic_info.nic_name = networkConfig[i].net_name;
                        nic_info.nic_ip_info = [];
                        // 源网卡
                        let source_ip_set = networkConfig[i].ipv4_set.ip_set
                        for(var n=0;n<source_ip_set.length;n++){
                            let nic_ip_info = {};
                            nic_ip_info.ip = source_ip_set[n].ip;
                            nic_ip_info.netmask = source_ip_set[n].netmask;
                            nic_ip_info.target_gateway = networkConfig[i].ipv4_set.gateway;
                            nic_ip_info.target_nic_name = networkConfig[i].target_net_name;
                            nic_ip_info.target_nic_mac = networkConfig[i].mac_address;
                            if(networkConfig[i].mac_type == CONF.FLAG.SET){
                                // MAC地址自动生成
                                nic_ip_info.target_nic_mac = '';
                            }
                            nic_info.nic_ip_info.push(nic_ip_info);
                        }
                        vm_object.takeover_business_ip_map.push(nic_info);
                        let ipv4IPconfig = {};
                        if(networkConfig[i].change_net_flag && networkConfig[i].ipv4_set.config_type == CONF.FLAG.UNSET){
                            // 手动修改IPV4配置
                            ipv4IPconfig.dns1 = networkConfig[i].ipv4_set.dns1;
                            ipv4IPconfig.dns2 = networkConfig[i].ipv4_set.dns2;
                            ipv4IPconfig.gateway = networkConfig[i].ipv4_set.gateway;
                            ipv4IPconfig.ip_set = networkConfig[i].ipv4_set.ip_set;
                        }
                        ipv4Config.push(ipv4IPconfig);
                        // ipv6
                        interfacesList.ipv6_DNS1 = networkConfig[i].ipv6_set.dns1;
                        interfacesList.ipv6_DNS2 = networkConfig[i].ipv6_set.dns2;
                        interfacesList.ipv6_gateway_address = networkConfig[i].ipv6_set.gateway; // 目标网关地址
                        let ipv6IPconfig = {};
                        if(networkConfig[i].change_net_flag && networkConfig[i].ipv6_set.config_type == CONF.FLAG.UNSET){
                            // 手动修改IPV6配置
                            ipv6IPconfig.dns1 = networkConfig[i].ipv6_set.dns1;
                            ipv6IPconfig.dns2 = networkConfig[i].ipv6_set.dns2;
                            ipv6IPconfig.gateway = networkConfig[i].ipv6_set.gateway;
                            ipv6IPconfig.ip_set = networkConfig[i].ipv6_set.ip_set;
                            // let nic_ip_info = {};
                            // nic_ip_info.ip = networkConfig[i].ipv6_set.ip_set[0].ip;
                            // nic_ip_info.netmask = networkConfig[i].ipv6_set.ip_set[0].netmask;
                            // nic_ip_info.target_gateway = networkConfig[i].ipv6_set.gateway;
                            // nic_ip_info.target_nic_mac = networkConfig[i].mac_address;
                            // if(networkConfig[i].mac_type == CONF.FLAG.SET){
                            //     // MAC地址自动生成
                            //     nic_ip_info.target_nic_mac = '';
                            // }
                            // nic_ip_info.target_nic_name = networkConfig[i].target_net_name;
                            // ipMap.nic_ip_info.push(nic_ip_info);
                        }
                        ipv6Config.push(ipv6IPconfig);

                        interfacesList.ip_set = [];
                        for(let j=0;j<networkConfig[i].ipv4_set.ip_set.length;j++){
                            interfacesList.ip_set.push(
                                {
                                    "ip_addr":networkConfig[i].ipv4_set.ip_set[j].ip,
                                    "ip_type":1,
                                    "netmask":networkConfig[i].ipv4_set.ip_set[j].netmask
                                }
                            )
                        }

                        // ipv6
                        for(let j=0;j<networkConfig[i].ipv6_set.ip_set.length;j++){
                            interfacesList.ip_set.push(
                                {
                                    "ip_addr":networkConfig[i].ipv6_set.ip_set[j].ip,
                                    "ip_type":2,
                                    "netmask":networkConfig[i].ipv6_set.ip_set[j].netmask
                                }
                            )
                        }
                        interfacesList.origin_nic_name = networkConfig[i].net_name;  // 源虚拟机网卡名称
                        interfacesList.origin_nic_mac = networkConfig[i].net_mac;    // 源虚拟机网卡mac

                        interfaces.push(interfacesList);
                        let networkSetting = {};
                        networkSetting.net_name = networkConfig[i].net_name;
                        networkSetting.network_name = networkConfig[i].network_name;
                        networkSettings.push(networkSetting);
                    }
                };
            }
        }
        // 系统固件类型
        vm_object.vm_temp_object.takeover_vm.config.os.firmware_type = $('select[name=modal_boot_firmware]').val();
        let modalBootFirmwareStr = $('select[name=modal_boot_firmware]').find("option:selected").text();
        // 最大开机等待时间
        vm_object.vm_temp_object.takeover_vm.config.max_boot_wait_time = parseInt($('input[name=max_wait_time_input]').val());
        let maxWaitTimeStr = parseInt($('input[name=max_wait_time_input]').val()) + LANG.UI_MICROSOFT365_MINUTE;
        // 网络配置
        let disks = vm_object.vm_temp_object.takeover_vm.config.disks;
        let diskConfigStr = '';
        for(let j =0;j<disks.length;j++){
            diskConfigStr += disks[j].dev_name + '->' +hardDiskBusType[parseInt(disks[j].target_bus)-1] + ',';
        }
        vm_object.vm_temp_object.disk_config_str = diskConfigStr;
        let des = `<div class="content alert alert-block pd0 mb0"><div class="circle" style="width: 20px;
        height: 20px;background: #FFFFFF;position: absolute; border-radius: 10px !important;
        right: -7px;top: -10px; box-shadow: 5px 5px 10px rgba(4, 13, 45, 0.1);"><button type="button" class="close vmConfCloseBtn" data-dismiss="alert" style="
        position: absolute;right: 2px;top: -1px;"></button></div>`;

        // 网络配置
        for(var i =0;i<networkSettings.length;i++){
            // ipv4配置
            let ipv4_dns1Str = '';
            let ipv4_dns2Str = '';
            let ipv4_gatewayStr = '';
            let ipv4_ipStr = '';
            let num = i+1;
            netConfigStr += '<ol class="plr15 pt-2">' + LANG.UI_VM_SETTING_V2_NETWORK + num + '：' + networkSettings[i].net_name + '->' + networkSettings[i].network_name + '</ol>';

            if(Object.keys(ipv4Config[i]).length != 0){
                ipv4_dns1Str = ipv4Config[i].dns1 !== '' ? LANG.UI_PUBLIC_IP_DNS1 + ':' + ipv4Config[i].dns1 +';  ': '';
                ipv4_dns2Str = ipv4Config[i].dns2 !== '' ? LANG.UI_PUBLIC_IP_DNS2 + ':' + ipv4Config[i].dns2 +';  ': '';
                ipv4_gatewayStr = ipv4Config[i].gateway !== '' ? LANG.UI_PUBLIC_IP_GATEWAY + ':' + ipv4Config[i].gateway +';  ': '';
                let ip_set = ipv4Config[i].ip_set;
                for(var k=0;k<ip_set.length;k++){
                    ipv4_ipStr += ipv4Config[i].ip_set.length !== 0 ? LANG.UI_PUBLIC_IP_ADDRESS + ':' + ipv4Config[i].ip_set[k].ip +';  ': '';
                    ipv4_ipStr += ipv4Config[i].ip_set.length !== 0 ? LANG.UI_PUBLIC_IP_NETMASK + ':' + ipv4Config[i].ip_set[k].netmask +';  ': '';
                }
            }

            if(!!ipv4_dns1Str || !!ipv4_dns2Str || !!ipv4_gatewayStr || !!ipv4_ipStr) {
                netConfigStr += '<ol class="plr15 pt-2">' + LANG.UI_PUBLIC_IPV4_CONFIG + '：' + ipv4_dns1Str + ipv4_dns2Str + ipv4_gatewayStr + ipv4_ipStr + '</ol>';
            }
            // ipv6配置
            let ipv6_dns1Str = '';
            let ipv6_dns2Str = '';
            let ipv6_gatewayStr = '';
            let ipv6_ipStr = '';
            if(Object.keys(ipv6Config[i]).length != 0){
                ipv6_dns1Str = ipv6Config[i].dns1 !== '' ? LANG.UI_PUBLIC_IP_DNS1 + ':' + ipv6Config[i].dns1 +';  ': '';
                ipv6_dns2Str = ipv6Config[i].dns2 !== '' ? LANG.UI_PUBLIC_IP_DNS2 + ':' + ipv6Config[i].dns2 +';  ': '';
                ipv6_gatewayStr = ipv6Config[i].gateway !== '' ? LANG.UI_PUBLIC_IP_GATEWAY + ':' + ipv6Config[i].gateway +';  ': '';
                let ip_set = ipv6Config[i].ip_set;
                for(var k=0;k<ip_set.length;k++){
                    ipv6_ipStr += ipv6Config[i].ip_set.length !== 0 ? LANG.UI_PUBLIC_IP_ADDRESS + ':' + ipv6Config[i].ip_set[k].ip +';  ': '';
                    ipv6_ipStr += ipv6Config[i].ip_set.length !== 0 ? LANG.UI_PUBLIC_IP_NETMASK + ':' + ipv6Config[i].ip_set[k].netmask +';  ': '';
                }
            }
            if(!!ipv6_dns1Str || !!ipv6_dns2Str || !!ipv6_gatewayStr || !!ipv6_ipStr) {
                netConfigStr += '<ol class="plr15 pt-2">' + LANG.UI_PUBLIC_IPV6_CONFIG + '：' + ipv6_dns1Str + ipv6_dns2Str + ipv6_gatewayStr + ipv6_ipStr + '</ol>';
            }
        }
        des += `<ul id="takeoverBuiltInVmConfDes" class="pl0" style="background: #FAFAFA;border-radius: 4px !important;border: 1px dotted #D9D9D9;padding: 12px 16px;color: #718096;">
            <ol class="plr15 pt-2">`+LANG.UI_VOL_CDP_TAKEOVER_BACKUP_MACHINE_NAME+`：${modalVmNameStr}</ol>
            <ol class="plr15 pt-2">`+LANG.UI_VM_SETTING_V2_SOCKET+`：${slotsnumStr}</ol>
            <ol class="plr15 pt-2">`+LANG.UI_VM_SETTING_V2_SCCKET_CORE+`：${slotcorenumStr}</ol>
            <ol class="plr15 pt-2">`+LANG.UI_VM_SETTING_CPU_TYPE+`：${cputypeStr}</ol>
            <ol class="plr15 pt-2">`+LANG.UI_PUBLIC_MEMORY+`：${memoryStr}</ol>
            <ol class="plr15 pt-2">`+LANG.UI_VIRTUAL_MACHINE_SYSTEM_FIRMWARE_TYPE+`：${modalBootFirmwareStr}</ol>
            <ol class="plr15 pt-2">`+LANG.UI_VERIFY_EFFECTIVE_TIME+`：${maxWaitTimeStr}</ol>
            <ol class="plr15 pt-2">`+LANG.UI_VM_SETTING_V2_DISK+`：${diskConfigStr}</ol>
           `;
        if(renameFlag){
            des += `<ol class="plr15">`+LANG.UI_VM_RESET_HOST_NAME+`：${rehostname}</ol>`;
        }
        des += `${netConfigStr}</ul></div>`;


        let text =`<ul class="pl0"><ol class="plr15 pt-2">`+LANG.UI_VOL_CDP_TAKEOVER_BACKUP_MACHINE_NAME+`：${modalVmNameStr}</ol>`+
            `<ol class="plr15 pt-2">`+LANG.UI_VM_SETTING_V2_SOCKET+`：${slotsnumStr}</ol>`+
            `<ol class="plr15 pt-2">`+LANG.UI_VM_SETTING_V2_SCCKET_CORE+`：${slotcorenumStr}</ol>`+
            `<ol class="plr15 pt-2">`+LANG.UI_VM_SETTING_CPU_TYPE+`：${cputypeStr}</ol>`+
            `<ol class="plr15 pt-2">`+LANG.UI_PUBLIC_MEMORY+`：${memoryStr}</ol>`+
            `<ol class="plr15 pt-2">`+LANG.UI_VIRTUAL_MACHINE_SYSTEM_FIRMWARE_TYPE +`：${modalBootFirmwareStr}</ol>`+
            `<ol class="plr15 pt-2">`+LANG.UI_VERIFY_EFFECTIVE_TIME +`：${maxWaitTimeStr}</ol>`+
            `<ol class="plr15 pt-2">`+LANG.UI_VM_SETTING_V2_DISK +`：${diskConfigStr}</ol>`;
        if(renameFlag){
            text += LANG.UI_VM_RESET_HOST_NAME + `：${rehostname}`;
        }
        text += `${netConfigStr}` + '</ul>';

        if (modalVmNameStr.trim() == '') {
            UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS, LANG.UI_VIRTUAL_MACHINE_STANDBY_NAME_CANNOT_BE_EMPTY);
            return false;
        }

        // 重置主机名
        if(renameFlag && rehostname == ''){
            // 重置开关打开但是没输入重置名
            UIToastr.showWarning(LANG.UI_VIRTUAL_MACHINE_CONFIGURE_STANDBY_MACHINE_MAPPING,LANG.UI_VIRTUAL_MACHINE_PLEASE_INPUT_RESET_HOSTNAME);
            return false;
        }

        // VT状态
        if(!virtualization_status){
            UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS, LANG.UI_VIRTUAL_MACHINE_VT_TIPS);
        }

        $('#standby_map_conf_drawer').drawer('hide');
        vm_object.vm_temp_des = des;
        vm_object.vm_temp_text = text;
        vm_object.modal_vm_name = modalVmNameStr;
        return vm_object;
    }

    /**
     * 重置主机名
     */
    let rename = function (event,state){
        renameFlag = this.checked;
        $('input[name=rehostname]').val('');
        if(state){
            $('#builtInVmSubmit').prop('disabled', true);
            $('.hostname').show();
        }else{
            $('#builtInVmSubmit').prop('disabled', false);
            $('.hostname').hide();
        }
    }

    let blurHostName = function (){
        let reset_hostname = $('input[name=rehostname]').val();
        let validateFlag = validateHostName('string',reset_hostname,os_type);
        $('#builtInVmSubmit').prop('disabled', !validateFlag);
    }

    let changeNodeSelect = function (){
        nodeuuid_selected = $('#node_select option:selected').val();
        node_uuid_list_checked.push(nodeuuid_selected);
        getNetworkList();
        initnetworkCardTable();
    }

    /**
     * 驱动检测
     */
    let initDriver = () => {
        let check_agent_flag;
        if(task_type == CONF.TASK_TYPE.VOL_CDP_BACKUP){
            check_agent_flag = true;
            agent_uuid = agent_uuid;
            timepoint_uuid = '';
        }else if(task_type == CONF.TASK_TYPE.VOL_CDP_TAKEOVER){
            check_agent_flag = false;
            agent_uuid = '';
            timepoint_uuid = timepoint_uuid;
        }
        $.fn.driverCheck.init($('#driverCheck'), {
            getCheckObject: () => {
                return {
                    source_list: [{
                        agent_uuid:agent_uuid,
                        check_agent_flag:check_agent_flag,
                        timepoint_uuid: timepoint_uuid,
                    }],
                    target_info: {
                        target_type: $.fn.driverCheck.DRIVER_TARGET_TYPE.VM,
                    }
                };
            },
            driver_usage: $.fn.driverCheck.DRIVER_USAGE_ENUM.TAKEOVER,
            afterCheck: (result, data) => {  // 检测结果的回调函数
                vm_object.vm_temp_object.cross_platform_flag = data[0].diff_platform_type;
                vm_object.vm_temp_object.cross_platform_conf = data[0].driver_hw_id_map;
                // result始终是true的，要通过判断data.driver_check_status是否为1($.fn.driverCheck.DRIVER_USAGE_ENUM.DRIVER_CHECK_RESULT.PASS)
                // 如果data.driver_check_status不为1，那么可以从$.fn.driverCheck.DRIVER_USAGE_ENUM.DRIVER_CHECK_RESULT.DRIVER_CHECK_RESULT_LANG[data.driver_check_status]获取错误信息
            },
        });
        $.fn.driverCheck.check($('#driverCheck'));  // 手动调用检测
    }

    /**
     * 内存大小
     */
    let getResource = () => {
        vm_object.vm_temp_object.takeover_vm.config.interfaces = [];  // 每次都需要清空
        
        let params = {
            node_uuid:node_uuid
        };
        vm_object.vm_temp_object.takeover_vm.node_uuid = node_uuid;
        pAjaxRequest(params, "/api/v1/virtual/resources", "GET",  (result) => {
            if (result.code == 0) {
                let data = result.data
                // 这里的可用cpu和核心数都要减少本机使用的
                var total_mems = Math.max(parseInt(data.mems) - parseInt(data.used_memory), 0);  // 单位：MB
                var total_mems2 = total_mems / 1024;
                total_mems2 = Math.floor(total_mems2 * 100) / 100
                memType = 2;
                if (total_mems2 < 1) {
                    total_mems2 = total_mems
                    memType = 1;
                }
                $('#modal_least_memory').text(total_mems2);
                if (total_mems2 < $('#modal_v_memory').val()) {
                    $('#modal_v_memory').val(total_mems2);
                }
                if (memType == 2) {
                    $('#modal_least_memory_unit').text(LANG.UI_VOL_CDP_JOB_DETAILS_SIZE_GB);
                }

                var total_cpu = Math.max(parseInt(data.cpus) - parseInt(data.used_cpu), 0);
                $('#modal_least_cpu').text(total_cpu);
                if (total_cpu < $('#modal_v_cpu').val()) {
                    $('#modal_v_cpu').val(total_cpu);
                }
                // VT状态
                virtualization_status = data.virtualization_status;
            }
        })
    }

    let initModalBootFirmware = (isEfi) => {
        $('select[name=modal_boot_firmware]').val(isEfi);
    }

    let initNodeList = () => {
        let param = {'offset': 0, 'limit': 20};
        pAjaxRequest(param, '/api/v1/nodes', 'GET',  (d) => {
            let dataRows = d.data.rows;
            const result = dataRows.filter(item => node_uuid_list.includes(item.node_uuid));
            let node_select = $('#node_select');
            node_select.empty();
            const nodeDiv = document.getElementById('node_div');
            node_uuid_list_all = [];
            node_uuid_list_checked = [];
            for (let i = 0; i < result.length; i++) {
                nodeuuid_selected = result[0]['node_uuid'];
                let option = $("<option>").text(result[i]['host_name'] + '(' + result[i]['ip'] + ')').val(result[i]['node_uuid']);
                node_select.append(option);
                node_uuid_list_all.push(result[i]['node_uuid']);
                const newDivAfter = document.createElement('div');
                newDivAfter.id = 'networkConfig'+'_'+result[i]['node_uuid'];
                newDivAfter.className= 'node-network-config-cls';
                nodeDiv.after(newDivAfter);
                nodeNetworkInitFlag[result[i]['node_uuid']] = false;
            }
            node_uuid_list_checked.push(nodeuuid_selected);
            getNetworkList();
            initnetworkCardTable();
        });
    }

    let initCpuMode = () => {
        const cpu_mode_ele = $('#cpu_type_select'); // Using jQuery selector
        let options = $("<option>").text(LANG.UI_PUBLIC_DEFAULT).val(0); // Create first option
        let data = CONF.CPU_MODE;
        for(let i in data){
            options = options.add($("<option>").text(data[i]).val(i));
        }
        cpu_mode_ele.append(options);
    }

    $.fn.vmConfig =  {
        init:function (options){
            // options是页面传的参数
            // 源机uuid
            // 节点uuid
            // 源机网卡信息
            // 目标网卡
            if(options.agent_uuid != undefined){
                agent_uuid = options.agent_uuid;
            }
            if(options.node_uuid != undefined){
                node_uuid = options.node_uuid;
                if(node_uuid != ''){
                    $('.cores-tip').show();
                    isStorageResourcePool = false;
                }else{
                    // 选择了存储资源池
                    $('.cores-tip').hide();
                    isStorageResourcePool = true;
                }
            }
            if(options.source_netcard_conf != undefined){
                source_netcard_conf = options.source_netcard_conf;
            }
            if(options.timepoint_uuid != undefined){
                timepoint_uuid = options.timepoint_uuid;
            }
            if(options.task_type != undefined){
                task_type = options.task_type;
            }
            if(options.disk_config_params != {}){
                disk_config_params = options.disk_config_params;
            }
            if(options.disk_uuids != []){
                disk_uuids = options.disk_uuids;
            }
            if(options.os_type != undefined){
                os_type = options.os_type;
            }
            if(options.node_uuid_list){
                node_uuid_list = options.node_uuid_list;
            }
            getVirtualDefault();
            getHardwareConfig(options.agent_uuid,options.task_type);
            getDiskInfo();
            initSpinner();
            addListeners();
            initDriver();
            getResource();
            initModalBootFirmware();
            initNodeList();
            initCpuMode();
        },
        getData:function (){
            return getVmSubmitInfo();
        }
    }

})(jQuery);