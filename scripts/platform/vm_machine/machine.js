var vm_Mchine = function (){
    // 定义下ip和dns的默认元素
    var cardTypeInit;
    var is_open = false; // 标记是否需要初始化
    var is_init_network = false; // 标记是否需要初始化
    var initDatas = {};// 记录当前的信息
    var initCardList = {};// 记录当前的网卡信息
    var uuid1,uuid2,uuid3;
    var initNetworkNum = 0;// 创建的时候默认的网卡初始化个数
    var memType = 1; // 物理内存最大的展示单位 默认是MB ,2 表示GB
    var initNode = '';// 创建的时候携带的nodeuuid的值
    var taskTypeArr = [CONF.TASK_TYPE.VOL_CDP_BACKUP, CONF.TASK_TYPE.VOL_CDP_TAKEOVER]; // 接管任务标识数组
    var taskTypeArr2 = [CONF.TASK_TYPE.VOL_CDP_BACKUP]; // 自动接管任务标识数组
    var taskType = ''; // 任务类型
    var networkInfo = []; // 源主机ip信息
    var initScened; // 场景值

    // 初始化数据并渲染
    function initData(data){
        if ($.inArray(data.task_type, taskTypeArr) !== -1) {
            // 创建的时候初始化如果携带了并且是接管类型就表示默认不能修改网络接口 并且指定为 1
            $('#modal_model_type').val(1);
            $('#modal_model_type').prop('disabled', true);
        } else {
            $('#modal_model_type').prop('disabled', false);
        }
        $('#modal_vm_name').val(data.vm_name);
        $('#modal_v_cpu').val(data.vcpu_num);
        $('#modal_least_cpu').text(data.total_cpu);
        $('#modal_v_memory').val(data.memory_total); // 默认返回的都是兆

        $('.backupThreadDiv').spinner({ value: data.memory_total, step: 1, min: 1, max: 999999 });
        var total_mems = data.total_mems / 1024;
        total_mems = Math.floor(total_mems * 100) / 100
        memType = 2;
        if (total_mems < 1) {
            total_mems = data.total_mems
            memType = 1;
        }
        $('#modal_least_memory').text(total_mems); // 默认返回的都是兆
        if (memType == 2) {
            $('#modal_least_memory_unit').text(LANG.UI_VOL_CDP_JOB_DETAILS_SIZE_GB);
        }
        $('#modal_boot_firmware').val(data.boot_firmware); // 引导固件
        $('#modal_boot_mode').val(data.boot_mode); // 启动方式
        if (data.boot_mode == 1) {
            // 显示iso
            $('#modal_cd_dvd_show').show();
            $('#cddvdSelect').val(data.iso_path); // iso路径
        } else {
            $('#modal_cd_dvd_show').hide();
        }
        // 虚拟机开机状态下 某些信息不能更改
        if (data.vm_status == 1) {
            $('#modal_v_cpu').prop('disabled', true);
            $('#modal_v_memory').prop('disabled', true);
            $('#modal_v_memory_type').prop('disabled', true);
            $('#modal_boot_firmware').prop('disabled', true);
            $('#modal_boot_mode').prop('disabled', true);
            $('#nodeSelect_vm').prop('disabled', true);
        } else {
            $('#modal_v_cpu').prop('disabled', false);
            $('#modal_v_memory').prop('disabled', false);
            $('#modal_v_memory_type').prop('disabled', false);
        }
        // 节点不能编辑
        $('#nodeSelect_vm').prop('disabled', true);
        // cpu模式不能编辑
        $('#modal_cpu_mode').val(parseInt(data.cpu_mode));
        $('#modal_cpu_mode').prop('disabled', true);

        // 名称不能修改，现阶段。只能修改cpu和内存，关机情况下，开机情况下，什么都不能修改
        $('#modal_vm_name').prop('disabled', true);
        $('#modal_boot_firmware').prop('disabled', true);
        $('#networkInfo').find('.right_btn').remove();

        // 网卡信息
        // 第一层是默认的 先渲染第一层
        if (data.communicate.length) {
            // 初始化prop
            propNetCardList(data.communicate);
            let communicate0 = data.communicate[0];
            $('#show_card_list select[name=modal_network_list]').eq(0).val(communicate0.network_type);
            $('#show_card_list input[name=modal_network_uuid]').eq(0).val(communicate0.uuid);
            $('#show_card_list input[name=modal_gateway_address]').eq(0).val(communicate0.gateway);
            $('#show_card_list input[name=modal_network_name]').eq(0).val(communicate0.name);
            $('#show_card_list select[name=modal_model_type]').eq(0).val(communicate0.model_type);

        }

        // 如果多个通信连接 那么需要循环渲染从第二层开始
        if (data.communicate.length > 1) {
            var cardlist = data.communicate;
            cardlist.shift(); // 移除数组的第一个元素
            for (var m in cardlist) {
                // 处理多个通信连接
                var cardTypeTemp = cardTypeInit.clone();
                cardTypeTemp.find('select[name=modal_network_list]').val(cardlist[m].network_type);
                cardTypeTemp.find('input[name=modal_network_uuid]').val(cardlist[m].uuid);
                cardTypeTemp.find('input[name=modal_gateway_address]').val(cardlist[m].gateway);
                cardTypeTemp.find('input[name=modal_network_name]').val(cardlist[m].name);
                cardTypeTemp.find('select[name=modal_model_type]').val(cardlist[m].model_type);
                $('#show_card_list').append(cardTypeTemp); // 将修改后的内容追加到目标元素中
            }
        }
        $('select[name=modal_network_list]').prop('disabled', true); // 通信链接方式不能修改
        $('select[name=modal_model_type]').prop('disabled', true); // 网络接口不能修改
        $('input[name=modal_network_name]').prop('disabled', true); // 网卡名称不能修改
        $('#vm-machine-modal-body').find(".popovers").popover(); // 初始化tips

        // 再次的给定监听事件
        addListeners();
    }

    // 初始化事件绑定监听之prop删除操作
    function initListener() {
        $('.del_card').on('click', function (){
            if ($(this).attr('data-num') == 0) {
                return;
            }
            if (initNetworkNum > 1 && $(this).hasClass('del_network_card')) {
                // 需要判断下当前剩余的网卡数量
                var data = getNetCardList();
                if (data.length <= initNetworkNum) {
                    // 提示最少要保留 n个
                    UIToastr.showInfo(LANG.UI_VM_MACHINE_DEL_TIPS, LANG.UI_VM_RESOURCE_LEAST_VALID.replace(/s%/g, initNetworkNum));
                    return;
                }
            }
            $(this).parent().find('.alert_card_del').show();
        })
        $('.alert_card_cacel').on('click', function (){
            $(this).parent().parent().hide();
        });
        $('.alert_card_submit').on('click', function (){
            $(this).parent().parent().parent().parent().find('input').off();
            $(this).parent().parent().parent().parent().remove();
            propNetCardList();
        });
    }
    // CD/DVD 驱动器列表
    function getCdDvdList() {
        pAjaxRequest({offset:0,limit:50}, "/api/v1/virtual/tree", "GET", function (result) {
            if (result.code == 0) {
                var Select = $('#highInfo #cddvdSelect');
                Select.empty();
                var option = $("<option>").text(LANG.UI_NIC_TEAMING_SELECT).val('0');
                Select.append(option);
                let data = result.data.rows;
                for(var i=0; i<data.length; i++){
                    option = $("<option>").text(data[i].name).val(data[i].path);
                    Select.append(option);
                }
                Select.val(initDatas.iso_path);
            } else {
                UIToastr.showError(LANG.UI_PUBLIC_UNKNOWN_ERROR, result.message);
            }
        });
    }
    // 初始化网卡信息列表
    function getCardList() {
        var product_network = 0;
        if ($.inArray(taskType, taskTypeArr2) !== -1) {
            // 接管任务， 那么只能获取桥接网络作为通信连接
            // 因为不能创建隔离网络和隔离桥接网卡 所以只需要判断 name != isolate_network 即可
            product_network = 1;
        } else if (initScened == CONF.EMD_VM_ROLE.EMD_VM_ROLE_DRILL) {
            // 整机验证场景，只能显示隔离网络
            product_network = 2;
        }
        var param = {
            offset:0,
            limit:20,
            product_network:product_network,
            node_uuid:$('#vm_machine_modal #nodeSelect_vm').val()
        };
        pAjaxRequest(param, "/api/v1/virtual/network", "GET", function (result) {
            Metronic.unblockUI('#vm_machine_modal');
            if (result.code == 0) {
                let data = result.data.rows;
                initCardList = data;
                var Select = $('#show_card_list select[name=modal_network_list]').eq(0);
                Select.empty();
                var option = '';
                for(var i=0; i<data.length; i++){
                    var name = data[i].name;
                    option = $("<option>").text(name).val(data[i].name);
                    Select.append(option);
                }

                // 先记录下初始化的两个ip html元素
                rememberIp();
                if (initDatas.vm_uuid != undefined) {
                    initData(initDatas);
                } else {
                    // 重新获取宿主机的总内存和cpu大小限制
                    getResource();
                }
                if (!is_init_network) {
                    makeIpInfo($('#show_card_list .network_card').eq(0), 0);
                    is_init_network = true;
                }

                if (initNetworkNum > 1) {
                    // 还需要手动的触发网卡添加事件
                    for (var j = 2; j <= initNetworkNum; j ++) {
                        $('#networkInfo .right_btn').click();
                    }
                }
            } else {
                Metronic.unblockUI('#vm_machine_modal');
                UIToastr.showError(LANG.UI_PUBLIC_UNKNOWN_ERROR, result.message);
            }
        });
    }
    //初始化所有备份节点
    function initNodeSelect(){
        $.post(CONF.AJAXPATH, {m:CONF.M.NODE,f:'getAddStorageNodeSelect',p:{}}, function(d){
            var data = JSON.parse(d);
            var nodeSelect = $('#vm_machine_modal #nodeSelect_vm');
            nodeSelect.empty();
            for(var i=0; i<data.length; i++){
                var option = $("<option>").text(data[i].text).val(data[i].uuid).attr('data-vt_flag', data[i].vt_flag);
                nodeSelect.append(option);
            }

            if (initNode != '') {
                nodeSelect.val(initNode);
                nodeSelect.prop('disabled', true);
                changeVT();
            }

            // 初始化网络
            getCardList();
        });
    }
    // 初始化事件绑定
    function inits() {
        $('#networkInfo .right_btn').off().on('click', function (){
            // 添加网卡，需要判断当前页面的通信连接是否小于网络个数
            /*var uuidCheck = checkCardNetwork();
            if (uuidCheck == false) {
                UIToastr.showWarning(LANG.UI_VM_MACHINE_ADD_NETWORK, LANG.UI_VM_MACHINE_ADD_NETWORK_NO_MORE);
                return;
            }*/
            Metronic.blockUI({target: '#vm_machine_modal',animate: true,cenrerY: true,});
            pAjaxRequest({}, "/api/v1/virtual/uuid", "GET", function (result) {
                Metronic.unblockUI('#vm_machine_modal');
                if (result.code == 0) {
                    let uuid = result.data.value[0];
                    // 还需要更改其他值
                    var html = cardTypeInit.clone();
                    html = $(html);
                    html.find('input[name="modal_network_uuid"]').val(uuid);
                    var cardName = $('#show_card_list input[name=modal_network_name]').eq(0).val();
                    var num = $('#show_card_list input[name=modal_network_name]').length;
                    html.find('input[name="modal_network_name"]').val(cardName + (num));
                    html.find('select[name="modal_network_list"]').val();
                    makeIpInfo(html, $('#show_card_list .network_card').length);
                    // 这里需要手动的调接口生成一个uuid地址
                    $('#show_card_list').append(html); // 将修改后的内容追加到目标元素中
                    $('#vm-machine-modal-body').find(".popovers").popover(); // 初始化tips
                    initListener();
                    propNetCardList();
                } else {
                    UIToastr.showError(LANG.UI_VM_MACHINE_ADD_NETWORK, result.message);
                    return;
                }
            });
        })
        // 监听改变
        $('#modal_boot_mode').on('change', function (){
            var value = $('#modal_boot_mode').val();
            if (value == 1) {
                $('#modal_cd_dvd_show').show();
            } else {
                $('#modal_cd_dvd_show').hide();
            }
        })
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
    }

    // 比对当前的网络通信连接和网卡个数
    function checkCardNetwork() {
        // 遍历每个input元素并获取配置信息
        var data = [];
        $('#networkInfo select[name="modal_network_list"]').each(function() {
            data.push($(this).val())
        });
        if (data.length >= initCardList.length) {
            return false;
        }
        // 不然给一个未选中的通信连接的uuid，为了添加网卡的时候默认给的不是重复的
        var uuid = '';
        for (var i in initCardList) {
            if (data.indexOf(initCardList[i].uuid) === -1) {
                // 表示不在里面
                uuid = initCardList[i].name;
                break;
            }
        }
        return uuid;
    }

    // 节点改变事件动态的更改cpu模式的内容
    function changeVT(){
        var data = [
            {'value': 0, 'name': LANG.UI_VERIFY_CPU_MODE_CUSTOM},
            {'value': 2, 'name': 'host-model'},
            {'value': 3, 'name': 'host-passthrough'},
            {'value': 4, 'name': 'EPYC'}
        ];
        // var attr = $('#vm_machine_modal #nodeSelect_vm option:selected').attr('data-vt_flag');
        var proper = LANG.UI_VM_MACHINE_VT_CPU_CUSTOM + '</br>';
        proper += LANG.UI_VM_MACHINE_VT_CPU_PASSTHROUGH + '</br>';
        proper += LANG.UI_VM_MACHINE_VT_CPU_MODEL;
        /*if (attr == 1) {
            // 开启 custom/ host_passthrough
            data.push({'value': 3, 'name': 'host-passthrough'});
            proper += LANG.UI_VM_MACHINE_VT_CPU_PASSTHROUGH;
        } else {
            data.push({'value': 2, 'name': 'host-model'});
            proper += LANG.UI_VM_MACHINE_VT_CPU_MODEL;
        }*/

        var nodeSelect = $('#vm_machine_modal #modal_cpu_mode');
        nodeSelect.empty();
        for(var i=0; i<data.length; i++){
            var option = $("<option>").text(data[i].name).val(data[i].value);
            nodeSelect.append(option);
        }
        $('.modal_cpu_mode').attr('data-content', proper);
        $('#vm-machine-modal-body').find(".popovers").popover(); // 初始化tips
    }

    // 初始化监听事件
    function addListeners() {
        inits();
        initListener();
    }

    // 记住网卡的初始html信息
    function rememberIp() {
        if (is_open) {
            return;
        }

        var html = $('#show_card_list .network_card').eq(0).html();
        html = '<div class="network_card">' + html + '</div>';
        var $html = $(html); // 将获取到的 HTML 内容转换为 jQuery 对象
        $html.find('select[name="source_ip_info"]').parent().parent().remove();
        $html.find('.del_network_card').attr('data-num', 1); // 修改属性值
        $html.find('.del_network_card').html('<i class="viconfont vicon-ge_delete font-green-seagreen" style="font-size: 18px;"></i>');
        // $html.find('.del_network_card i').addClass('font-green-seagreen'); // 增加class
        cardTypeInit = $html;
        is_open = true;
    }
    // 重复渲染去除多余的html元素
    function removeHtml() {
        // 移除除了第一个之外的所有元素
        $('.network_card:gt(0)').remove(); // 通信连接
    }
    // 获取当前配置的网卡信息
    // @param isCheck 默认false 如果是true则会校验是否正确
    function getNetCardList(isCheck = false) {
        var data = [];
        var hasError = false; // 新增标志变量
        // 遍历每个input元素并获取配置信息
        $('#networkInfo select[name="modal_network_list"]').each(function() {
            if (hasError) return false; // 如果已经出错，停止进一步循环
            var elem = $(this);

            var uuidTemp = elem.parent().parent().parent().find('input[name="modal_network_uuid"]').val();
            var card_tmp = {
                network_type: elem.val(),
                uuid: uuidTemp == '' ? uuid3 : uuidTemp,
                gateway: elem.parent().parent().parent().find('input[name="modal_gateway_address"]').val(),
                name: elem.parent().parent().parent().find('input[name="modal_network_name"]').val(),
                model_type: parseInt(elem.parent().parent().parent().find('select[name="modal_model_type"]').val()),
                network_name: elem.find('option:selected').text(),
                model_type_name: elem.parent().parent().parent().find('select[name="modal_model_type"] option:selected').text(),
                source_ip_info: ''
            };
            if (networkInfo.length > 0) {
                var index = elem.parent().parent().parent().find('select[name="source_ip_info"]').val();
                card_tmp.source_ip_info = networkInfo[index] != undefined ? networkInfo[index] : [];
            }
            data.push(card_tmp);
        });
        // console.log(data)
        return hasError ? [] : data;
    }
    // prop网络配置
    function propNetCardList(data = []) {
        if (data.length == 0) {
            data = getNetCardList();
        }
        // 计算个数
        var network = '';
        for (var z in data) {
            network += data[z].name + ',';
        }
        var des2 = LANG.UI_VM_MACHINE_NETWORK_NUM + '：' + data.length;
        var titleDes = '（' + network + '）';

        // 渲染下proper的网络个数
        $('.vmMachineDes').html(des2);
        $('.vmMachineDes').prop('title', titleDes);

        $('#vm-machine-modal-body').find(".popovers").popover(); // 初始化tips
    }

    // 添加通信网卡提示
    function checkNetwork() {
        // 假设 #networkInfo 是已经存在于页面上的父元素
        $('#networkInfo').on('blur', 'input[name="modal_network_name"]', function () {
            propNetCardList();
        });
    }

    // 获取系统的资源配置
    var getResource = function (){
        var node_uuid = $("#vm_machine_modal #nodeSelect_vm").val();
        // 获取系统的资源配置
        Metronic.blockUI({target: '#vm_machine_modal',animate: true,cenrerY: true,});
        pAjaxRequest({node_uuid: node_uuid}, "/api/v1/virtual/resources", "GET", function (result) {
            Metronic.unblockUI('#vm_machine_modal');
            if (result.code == 0) {
                let data = result.data
                // 这里的可用cpu和核心数都要减少本机使用的
                var cpus = $('#modal_v_cpu').val();
                var mems = $('#modal_v_memory').val();
                if (initDatas.vm_uuid == undefined) {
                    cpus = mems = 0;
                }
                var total_cpu = Math.max(parseInt(data.cpus) - parseInt(data.used_cpu), 0) + parseInt(cpus);
                var total_mems = Math.max(parseInt(data.mems) - parseInt(data.used_memory), 0) + parseInt(mems);
                $('#modal_least_cpu').text(total_cpu);
                if (total_cpu < $('#modal_v_cpu').val()) {
                    $('#modal_v_cpu').val(total_cpu);
                }

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
                $('.backupThreadDiv').spinner({ value: data.mems, step: 1, min: 1, max: 999999 });
            }
        });
    }

    // 加载默认的uuid集合
    var getUuid = function (num = 3) {
        pAjaxRequest({num: num}, "/api/v1/virtual/uuid", "GET", function (result) {
            if (result.code == 0) {
                let uuidArr = result.data.value;
                uuid1 = uuidArr[0] ?? '';
                uuid2 = uuidArr[1] ?? '';
                uuid3 = uuidArr[2] ?? '';

            }
        });
    }

    // 获取创建虚拟机默认的一些信息
    var getDefault = function () {
        pAjaxRequest({}, "/api/v1/virtual/default", "GET", function (result) {
            if (result.code == 0) {
                let data = result.data;
                $('#modal_vm_name').val(data.name);
                $('#modal_v_cpu').val(data.cpu);
                $('#modal_v_memory').val(data.mems);
                $('#modal_v_memory_type').val(2);
                $('#show_card_list input[name=modal_network_name]').eq(0).val(data.card);

                $('#vm-machine-modal-body').find(".popovers").popover(); // 初始化tips
            }
        });
    }

    // 添加网卡名称校验
    function checkCard() {
        // 假设 #networkInfo 是已经存在于页面上的父元素
        $('#networkInfo').off('input', 'input[name="modal_network_name"]').on('input', 'input[name="modal_network_name"]', function () {
            var value = $(this).val();
            if (value !== '') {
                var regex = /^[a-zA-Z0-9_-]+$/;
                if(!regex.test(value)) {
                    UIToastr.showWarning(LANG.UI_SYSTEM_MONITOR_NET_NAME, LANG.UI_VM_MACHINE_NETWORK_VALID);
                    value = value.replace(/[^a-zA-Z0-9_-]/g, '');
                    $(this).val(value);
                }
            }
        });
    }

    function makeIpInfo(elem, index){
        if (networkInfo.length > 0) {
            var html_ip_header = '<div class="form-group">\n' +
                '             <label class="control-label col-md-3">'+LANG.UI_JOB_HIS_SRC_HOST_IP+'</label>\n' +
                '             <div class="control-label col-md-6">' +
                '               <select class="form-control select2me" name="source_ip_info">';

            var html_ip_bottom = '</select></div></div>';
            if (networkInfo.length > index) {
                var option = '<option value="">'+LANG.UI_NIC_TEAMING_SELECT+'</option>';
                for (var k in networkInfo) {
                    var value = '';
                    for (var j in networkInfo[k]) {
                        value = value + networkInfo[k][j].ip_addr + ',';
                    }
                    value = value.replace(/^,+|,+$/g, '');
                    var checked = '';
                    if (index == k) {
                        checked = 'selected="true"';
                    }
                    option += '<option value="'+k+'" '+ checked+'>'+value+'</option>';
                }
                $(elem).find('.show_card_del').after(html_ip_header + option + html_ip_bottom);
            }

            // 监听改变事件
            $('#show_card_list').off('change', 'select[name="source_ip_info"]').on('change', 'select[name="source_ip_info"]', function () {
                var chosenValue = $(this).val(); // 获取当前选择的值
                var chooseIpArr = [];
                var isAgain = false;
                var that = this;

                // 遍历所有同名的 select 元素
                $('#show_card_list').find('select[name="source_ip_info"]').each(function (index, element) {
                    // 排除当前正在改变的 select 元素
                    if (this !== event.target) {
                        if ($.inArray(chosenValue, chooseIpArr) === -1 && $(element).val() === chosenValue && chosenValue != '') {
                            // 发现重复
                            isAgain = true;
                            $(that).val('');
                            return false; // 退出循环
                        }
                        chooseIpArr.push($(element).val());
                    }
                });

                if (isAgain) {
                    UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS, LANG.UI_VM_SOURCE_IP_TIPS1);
                }
            });
        }
    }

    return {
        init: function (options) {
            is_open = false; // 标记是否需要初始化
            if (options.network_num != undefined) {
                // 创建的时候初始化如果携带了就表示默认添加几个网卡配置
                initNetworkNum = Math.max(options.network_num, 0);
            }

            if (options.network_info != undefined) {
                // 源主机ip 数组信息
                networkInfo = options.network_info;
            } else {
                networkInfo = [];
            }

            if (options.node_uuid != undefined) {
                // 创建的时候初始化如果携带了就表示默认添加几个网卡配置
                initNode = options.node_uuid;
            }

            if (options.app_scened != undefined) {
                // 应用场景，如果是 CONF.EMD_VM_ROLE.EMD_VM_ROLE_DRILL 验证场景 只能显示隔离网络；
                initScened = options.app_scened;
            } else {
                initScened = '';
            }

            if (options.data != undefined) {
                initDatas = options.data;
                if (initDatas.task_type != null) {
                    taskType = initDatas.task_type;
                }
                initNode = initDatas.node_uuid;
            } else {
                // 获取默认的虚拟机名称，cpu，内存和默认的通信连接的网卡名称
                getDefault();
            }

            if (options.task_type != undefined && $.inArray(options.task_type, taskTypeArr) !== -1) {
                // 创建的时候初始化如果携带了并且是接管类型就表示默认不能修改网络接口 并且指定为 1
                // 只需要product_network 桥接网络类型
                $('#modal_model_type').val(1);
                $('#modal_model_type').prop('disabled', true);
                taskType = options.task_type;
            }

            // 获取iso列表
            getCdDvdList();
            // 获取节点
            initNodeSelect();
            // 清除多余的html元素 因为多次点击是不会刷新页面的
            removeHtml();
            addListeners();
            // 节点事件
            $("#nodeSelect_vm").on("change", function (){
                // 重新获取宿主机的总内存和cpu大小限制
                getResource();
                // 初始化网络
                is_open = false;
                getCardList();
                changeVT();
            });
            checkNetwork();
            // 获取四个uuid备用
            getUuid(3);
            checkCard();
            propNetCardList();
        },
        // 对外提供获取虚拟机页面的所有信息
        getMachineInfo: function (){
            var data = {};
            data.uuid = initDatas.vm_uuid != undefined ? initDatas.vm_uuid : uuid1; // 新建需要php生成
            var node_uuid = $('#nodeSelect_vm').val();
            if (node_uuid == '') {
                UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS, LANG.UI_VM_MACHINE_NETWORK_CHOOSE_NODE);
                return false;
            }
            data.node_uuid = node_uuid;
            data.hypervisor_type = 51; // 虚拟化类型
            // 网卡信息
            var communicate = getNetCardList(true);
            if (communicate.length == 0) {
                return false;
            }
            let config = {};
            config.vm_uuid = data.uuid;
            config.vm_name = $('#modal_vm_name').val(); // 虚拟机名称
            config.cpu_mode = parseInt($('#modal_cpu_mode').val()); //cpu模式
            if (config.vm_name.trim() == '') {
                UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS, LANG.UI_VM_MACHINE_NAME_TIPS);
                return false;
            }

            var modal_v_cpu_val = $('#modal_v_cpu').val();
            if (modal_v_cpu_val == '') {
                modal_v_cpu_val = 0;
            }
            config.num_cpus = parseInt(modal_v_cpu_val); // cpu
            var modal_v_memory_val = $('#modal_v_memory').val();
            if (modal_v_memory_val == '') {
                modal_v_memory_val = 0;
            }
            config.memoryMB = parseFloat(modal_v_memory_val); // 内存 m

            if (config.memoryMB <= 0) {
                UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS, LANG.UI_VM_MACHINE_MEMS_CHECK);
                return false;
            }
            var memory_type = parseInt($('#modal_v_memory_type').val()); // 内存单位 1MB 2GB 3TB
            config.mems_type = memory_type;

            // 内存和cpu大小限制
            var cpu_max = parseInt($('#modal_least_cpu').text());
            if (config.num_cpus > cpu_max) {
                UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS, LANG.UI_VM_MACHINE_MAX_CPU_NUM + ':'+cpu_max+ LANG.UI_VM_MACHINE_CPU_UNIT);
                return false;
            }

            var new_memory = memory_type > 1 ?  Math.pow(1024, memory_type - 1) * config.memoryMB : config.memoryMB;

            config.memoryMB = new_memory;
            config.mems_type = 1;
            if (config.memoryMB == '' || config.num_cpus == '' || config.memoryMB == 0 || config.num_cpus == 0) {
                UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS, LANG.UI_VM_MACHINE_MEMS_CPUS);
                return false;
            }

            // 判断下如果单位是mb的时候是不是4的倍数
            if (config.memoryMB % 4 != 0) {
                UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS, LANG.UI_VM_MACHINE_MEMS_CHECK);
                return false;
            }

            var memory_max = parseFloat($('#modal_least_memory').text());
            if (memType == 2) {
                memory_max = memory_max * 1024;
            }
            if (memory_max < new_memory) {
                var msg = memory_max+'MB';
                if (memory_type == 2) {
                    msg = memory_max / 1024 + 'GB';
                } else if (memory_type == 3) {
                    msg = memory_max / 1024 / 1024 + 'TB';
                }
                UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS,LANG.UI_VM_MACHINE_MAX_MEMS_NUM + ':'+ msg);
                return false;
            }

            // 判断下，如果是大于1tb的就显示tb单位，反正gb，最终mb
            var mems_str = '';
            if ((new_memory / 1024 / 1024) >= 1) {
                mems_str = (new_memory / 1024 / 1024) + 'TB';
            } else if ((new_memory / 1024) >= 1) {
                mems_str = (new_memory / 1024) + 'GB';
            } else {
                mems_str = new_memory + 'MB';
            }

            config.mems_str = mems_str;
            config.os = {
                arch_type: 1, // 架构类型 默认x86
                firmware: parseInt($('#modal_boot_firmware').val()), // 引导固件
                boot_dev: parseInt($('#modal_boot_mode').val()), // 启动方式
            };

            config.cdroms = [];
            if (config.boot_dev == 1) {
                var cdroms = {}; // 启动设备配置
                cdroms.uuid = initDatas.cdroms != undefined ? initDatas.cdroms.uuid : uuid2; // 新增和编辑都需要web这边给一个
                // 光盘启动方式
                cdroms.operation = initDatas.cdroms != undefined ? initDatas.cdroms.operation : 1; // 默认都是新增，编辑后面再改结构
                cdroms.source_path = $('#cddvdSelect').val(); // iso启动方式的路径
                if (cdroms.source_path == 0) {
                    UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS, LANG.UI_VM_MACHINE_ISO_PATH);
                    return false;
                }
                config.cdroms.push(cdroms);
            }

            if (networkInfo.length > 0) {
                // 遍历所有同名的 select 元素
                var chooseIpArr = [];
                var isAgain = false;
                var isEmpty = false;
                $('#show_card_list').find('select[name="source_ip_info"]').each(function (index, element) {
                    if ($(element).val() == '') {
                        isEmpty = true;
                        return false;
                    }
                    if ($.inArray($(element).val(), chooseIpArr) !== -1) {
                        // 发现重复
                        isAgain = true;
                        $(element).val('');
                        return false; // 退出循环
                    }
                    chooseIpArr.push($(element).val());
                });
                if (isEmpty) {
                    UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS, LANG.UI_VM_SOURCE_IP_TIPS2);
                    return false;
                }
                if (isAgain) {
                    UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS, LANG.UI_VM_SOURCE_IP_TIPS1);
                    return false;
                }
            }

            var interfaces = [];// [{operation:'',type:'',model_type:'',source_network:'',source_bridge:'',mac_address:''}]; // 通信链接配置 以mac地址和ip匹配
            // operation 1add 2edit 3del, type 1隔离 2桥接，model_type 虚拟网络接口模式类型(e1000/vitio)web暂时不管，source_network 源隔离网名称，source_bridge源桥接口名称
            var special = {};// {business_nic_set:[{mac_address:'',gateway_address:'',ip_set:[{ip_type:'',ip_addr:'',netmask:''}]}]}; // 通信链接的ip配置 以mac地址和通信连接匹配
            var operation = data.temp_agent_uuid ? 2 : 1; // 如果是编辑的情况下，需要再后台判断下当前的uuid和之前的是否一致
            var business_nic_set_list = [];
            var card_list = [];// 校验是否重复选择了相同的通信连接网卡
            for (var i in communicate) {
                if (card_list.indexOf(communicate[i].name) !== -1) {
                    UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS, LANG.UI_VM_MACHINE_NETCARD_AGAIN);
                    return false;
                }
                card_list.push(communicate[i].name);
                var business_nic_set = {mac_address:'',uuid:communicate[i].uuid,gateway_address:communicate[i].gateway};

                business_nic_set.ip_set = [];
                business_nic_set.name = communicate[i].name;
                business_nic_set_list.push(business_nic_set);
                // 通信连接
                interfaces.push(
                    {
                        operation:operation,
                        uuid:communicate[i].uuid,
                        interface_type:2, // 固定写死，隔离网络
                        model_type:communicate[i].model_type,
                        source_network:communicate[i].network_type,
                        source_bridge: '',
                        mac_address:'',
                        device_name:communicate[i].name,
                        name:communicate[i].name,
                        network_name: communicate[i].network_name, // 通信网络名称
                        model_type_name: communicate[i].model_type_name, // 网络接口
                        source_ip_info: communicate[i].source_ip_info, // 传递过来的ip数组子元素
                    }
                );
            }
            special.business_nic_set = business_nic_set_list;
            config.interfaces = interfaces;

            data.config = config;

            return {temp_agent:data};
        }
    }
}();
