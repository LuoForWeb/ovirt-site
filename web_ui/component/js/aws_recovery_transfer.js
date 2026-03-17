/**
 * 公有云恢复第三步传输策略
 */
(function($){
    var transport_strategy = {};
    var target_hypervisor;
    var instance_configs; // 实例配置的最终参数
    var _platformUuid;
    var applianceRegionUuid = '';
    var applianceAz = '';
    var initPublicIpFlag = false; // 公有IP初始化标志
    // 传输代理相关
    var _AS = $('#awsApplianceSelect');
    var currentAgentInstanceType = ''; // 当前传输代理实例类型
    var agentNetworkList = []; // 传输代理网络
    var regionUuids = [], regions = [], regionsFlag = []; // 传输代理网络区域是否加载flag
    var authFun = [];

    const _AgentBillingType = $('select[name=agent_billing_type]');
    const _AgentBillingValue = $('input[name=agent_billing_value]');
    const BILLING_BANDWIDTH = 'bandwidth';
    const BILLING_TRAFFIC = 'traffic';

    //版本差异处理,主要是处理标准版本功能限制
    // 中文标准版 1
    // 中文企业版 2
    // 英文免费版 4
    // 英文基础版 9
    // 英文标准版 6
    // 英文企业版 7
    const initSoftwareVersionDiff = function () {
        $.post(CONF.AJAXPATH, {m: CONF.M.USER, f: 'getAuthFunc', p: {}}, function (d) {
            authFun = JSON.parse(d);
            //判断是否有多线程授权
            if (typeof authFun === "undefined" || !authFun.multithread) {
                $('#awsRecoveryThreadNum').val(1).attr("disabled", true); //单线程
                $('#awsRecoveryThreadDiv .spinner-group-btn button').prop('disabled', true);
            } else {
                $('#awsRecoveryThreadDiv').spinner({value:3, step: 1, min: 1, max: 8});
            }
        });
    }

    // 初始化传输代理网络信息
    const initAgentNetwork = function () {
        regionUuids = [];
        regions = [];
        regionsFlag = [];
        $.each(instance_configs, function (i, v) {
            if ($.inArray(v.region_uuid, regionUuids) < 0) {
                regionUuids.push(v.region_uuid);
                regions.push(v.region);
                regionsFlag[v.region_uuid] = false;
            }
        })

        $('.applianceNetwork').empty();
        for (let i = 0; i < regionUuids.length; i++) {
            let html = `
				<div class="form-group">
					<label class="control-label col-md-3 applianceVpcLabel">${LANG.UI_BACKUP_AWS_APPLIANCE_VPC}(${regions[i]})</label>
					<div class="col-md-4">
						<div class="applianceNetworkDiv_${regionUuids[i]} applianceVpcDiv_${regionUuids[i]}">
							<select class="form-control select2me applianceVpc" id="applianceVpc_${regionUuids[i]}" data-regionuuid="${regionUuids[i]}"></select>
						</div>
					</div>
				</div>
				<div class="form-group applianceSgDiv">
					<label class="control-label col-md-3 applianceSgLabel">${LANG.UI_BACKUP_AWS_APPLIANCE_SG}(${regions[i]})</label>
					<div class="col-md-4">
                        <div class="applianceNetworkDiv_${regionUuids[i]}">
                            <select class="form-control select2me applianceSg" id="applianceSg_${regionUuids[i]}" data-regionuuid="${regionUuids[i]}"></select>
                        </div>
					</div>
				</div>
			`;

            $('.applianceNetwork').append(html);
            $(`#applianceVpc_${regionUuids[i]}`).append(`<option value="">${LANG.UI_BACKUP_AWS_AUTO_SELECT}</option>`);
            $(`#applianceSg_${regionUuids[i]}`).append(`<option value="">${LANG.UI_BACKUP_AWS_AUTO_SELECT}</option>`);
        }

        // 点击传输代理vpc获取网络配置
        $('.applianceVpc').on('click', function () {
            loadAgentNetwork($(this).attr('data-regionuuid'), _AS.val());
        });

        // 切换传输代理vpc重新加载安全组
        $('.applianceVpc').on('change', function () {
            switch (target_hypervisor) {
                case CONF.VM_TYPE.AWS:
                    agentNetworkHandler($(this).attr('data-regionuuid'), 2, agentNetworkList, this.value);
            }
        });

        // 点击传输代理安全组获取网络配置
        $('.applianceSg').on('click', function () {
            switch (target_hypervisor) {
                case CONF.VM_TYPE.HUAWEICLOUD:
                    loadAgentNetwork($(this).attr('data-regionuuid'), _AS.val());
            }
        });
    }

    // 加载传输代理网络信息
    const loadAgentNetwork = function (region_uuid, instance_type = '') {
        if (currentAgentInstanceType == instance_type && regionsFlag[region_uuid]) return; // 只加载一次
        if (!instance_type) {
            UIToastr.showWarning(LANG.UI_BACKUP_AWS_PLEASE_SELECT_APPLIANCE);
            return;
        }
        currentAgentInstanceType = instance_type;
        regionsFlag[region_uuid] = true;
        let p = {
            platform_uuid: _platformUuid,
            region_uuid: region_uuid,
            instance_type: instance_type
        };

        let blockUITarget;
        switch (target_hypervisor) {
            case CONF.VM_TYPE.AWS:
                blockUITarget = `.applianceVpcDiv_${region_uuid}`;
                break;
            case CONF.VM_TYPE.HUAWEICLOUD:
                blockUITarget = `.applianceNetworkDiv_${region_uuid}`;
                break;
        }
        Metronic.blockUI({target: blockUITarget,animate: true});
        pAjaxRequest(p, "/api/v1/cloud/instances/network_configs", "GET", function (d) {
            Metronic.unblockUI(blockUITarget);
            if (d.success) {
                agentNetworkList = d.data.network_list;
                switch (target_hypervisor) {
                    case CONF.VM_TYPE.AWS:
                        agentNetworkHandler(region_uuid, 1, agentNetworkList, '', '');
                        break;
                    case CONF.VM_TYPE.HUAWEICLOUD:
                        // 华为云vpc和安全组无绑定关系，取第一个vpc的
                        agentNetworkHandler(region_uuid, 3, agentNetworkList, agentNetworkList[0].network_vpc, '');
                        break;
                }
            } else {
                operateResponseList(d);
            }
        });
    }

    // reloadType:1-仅加载vpc，2-仅加载安全组，3-都加载
    const agentNetworkHandler = function (region_uuid, reloadType, networkList, vpc_uuid = '', sg_name = '') {
        let network = null; // 当前网络

        // vpc
        if (1 == reloadType || 3 == reloadType) {
            $(`#applianceVpc_${region_uuid}`).empty();
            $(`#applianceVpc_${region_uuid}`).append(`<option value="">${LANG.UI_BACKUP_AWS_AUTO_SELECT}</option>`);
            $.each(networkList, function (i, v) {
                let option;
                if (v.network_vpc_name) {
                    option = `<option value="${v.network_vpc}">${v.network_vpc_name}(${v.network_vpc})</option>`;
                } else {
                    option = `<option value="${v.network_vpc}">${v.network_vpc}</option>`;
                }
                $(`#applianceVpc_${region_uuid}`).append(option);
                if (vpc_uuid == v.network_vpc) {
                    network = v;
                }
            });
        }
        if (2 == reloadType || 3 == reloadType) {
            $.each(networkList, function (i, v) {
                if (vpc_uuid == v.network_vpc) {
                    network = v;
                }
            });

            // 安全组
            $(`#applianceSg_${region_uuid}`).empty();
            $(`#applianceSg_${region_uuid}`).append(`<option value="">${LANG.UI_BACKUP_AWS_AUTO_SELECT}</option>`);
            if (network) {
                $.each(network.network_security, function (i, v) {
                    let sgValue = '';
                    switch (target_hypervisor) {
                        case CONF.VM_TYPE.AWS:
                            sgValue = v.network_security_name;
                            break;
                        case CONF.VM_TYPE.HUAWEICLOUD:
                            sgValue = v.network_security_id;
                            break;
                    }
                    let option = `<option value="${sgValue}">${v.network_security_name}</option>`;
                    $(`#applianceSg_${region_uuid}`).append(option);
                });
            }
        }
    }

    const initAgentSelect = function () {
        $('#awsApplianceSelect').empty();
        switch (target_hypervisor) {
            case CONF.VM_TYPE.AWS:
                $('#awsApplianceSelect').append('<option value="t3.small">t3.small(2vCPU,2.00 GB)</option>');
                $('.agent-public-ip').hide();
                $('.agent-public-ip-billing').hide();
                break;
            case CONF.VM_TYPE.HUAWEICLOUD:
                // 华为云需手动选择
                $('#awsApplianceSelect').append('<option value="ac7.large.2">ac7.large.2(2vCPU,4.00 GB)</option>');
                $('.agent-public-ip').show();
                $('.agent-public-ip-billing').show();
                break;
            default:
                break;
        }
        $('#awsApplianceSelect').selectpicker('refresh');
    }

    // ----- 事件 -----
    const initListener = function () {

        //传输代理
        $('#awsApplianceSelect').next('.bootstrap-select').find('.dropdown-menu .inner').css({height: '400px', width: 'auto'});
        $('#awsApplianceSelect').next('.bootstrap-select').find('.dropdown-toggle').on('click', function () {
            initApplianceSelect(instance_configs[0].region_uuid, instance_configs[0].available_zone); //通过平台和区域初始化传输代理实例类型
        });

        // 传输策略---加密传输
        $('#awsencrypttransfer').on('switchChange.bootstrapSwitch', transferEncryptChange);

        // 切换付费方式
        _AgentBillingType.on('change', function () {
            if (BILLING_BANDWIDTH === this.value) {
                _AgentBillingValue.attr('max', 2000);
                _AgentBillingValue.attr('maxlength', 4);
            } else {
                _AgentBillingValue.attr('max', 300);
                _AgentBillingValue.attr('maxlength', 3);
            }
        });

        $('select[name=agent_public_ip]').on('click', initPublicIp);
    }

    //初始化传输代理实例下拉框
    const initApplianceSelect = function (regionUuid, availableZone) {
        if (applianceRegionUuid == regionUuid && applianceAz == availableZone) return; //只加载一次
        $('#awsApplianceSelect').next('div').find('.open').addClass('applianceMenu');
        Metronic.blockUI({target: '#awsrecovercontent', animate: true});
        Metronic.blockUI({target: '.applianceMenu', animate: true});
        pAjaxRequest({
            platform_uuid: _platformUuid,
            region_uuid: regionUuid,
            available_zone: availableZone
        }, "/api/v1/cloud/instance/types", "GET", function (d) {
            Metronic.unblockUI('#awsrecovercontent');
            Metronic.unblockUI('.applianceMenu');
            applianceRegionUuid = regionUuid;
            applianceAz = availableZone;
            let data = d.data;
            if (!data.length) return;
            var applianceSelect = $('#awsApplianceSelect');
            applianceSelect.empty();
            for (var i = 0; i < data.length; i++) {
                let vcpu = data[i].instance_type_vcpu;
                let mem = 0;
                switch (target_hypervisor) {
                    case CONF.VM_TYPE.AWS:
                        mem = 1024 * 1024 * parseInt(data[i].instance_type_memry);
                        break;
                    case CONF.VM_TYPE.HUAWEICLOUD:
                        mem = 1024 * 1024 * 1024 * parseInt(data[i].instance_type_memry);
                        break;
                }
                let memStr = storageCalculateSize(mem);
                var option = $("<option>").text(data[i].instance_type_name + '(' + vcpu + 'vCPU,' + memStr + ')').val(data[i].instance_type_name);
                applianceSelect.append(option);
            }
            applianceSelect.selectpicker('refresh');
        });
    }

    // 显示加密算法
    const transferEncryptChange = function () {
        if (this.checked) {
            $('.transfer-encrypt-method-form').show();
        } else {
            $('.transfer-encrypt-method-form').hide();
        }
    }

    // 初始化公有ip选择，华为云使用
    const initPublicIp = function (ip_uuid = '') {
        if (initPublicIpFlag) {
            return;
        }
        initPublicIpFlag = true;
        $('select[name=agent_public_ip]').empty();
        $('select[name=agent_public_ip]').append('<option value="" data-address="">' + LANG.UI_JOB_SELECT + '</option>');
        let platform_uuid = '', region_uuid = '';
        platform_uuid = _platformUuid;
        region_uuid = instance_configs[0].region_uuid;
        let div = $(`#publicIpSelect`).closest(`.tab-content`);
        Metronic.blockUI({target: div, animate: true});
        pAjaxRequest({platform_uuid: platform_uuid, region_uuid: region_uuid},
            "/api/v1/cloud/platform/ip_list", "GET", function (d) {
                Metronic.unblockUI(div);
                let ipList = d.data.ip_list;
                if (ipList.length) {
                    let option = '';
                    $.each(ipList, function (i, v) {
                        let selected = v.ip_uuid == ip_uuid ? 'selected' : '';
                        option += `<option value="${v.ip_uuid}" data-address="${v.ip_address}" ${selected}>${v.ip_name}(${v.ip_address}, ${v.ip_size}Mbps)</option>`;
                    });
                    $('select[name=agent_public_ip]').append(option);
                }
            }, true);
    }

    //得到开关的结果描述   开启/关闭
    const getSwitchDes = function(check){
        if(check){
            return LANG.UI_PUBLIC_ON;
        }
        return LANG.UI_PUBLIC_OFF;
    }

    const initHighListeners = function () {
        $('#awsRecoveryThreadNum').on('input propertychange', function () {
            initHighStrategyDes();
        });
        $('.spinner-up').on('click', function () {
            initHighStrategyDes();
        });
        $('.spinner-down').on('click', function () {
            initHighStrategyDes();
        });
    }

    const initHighStrategyDes = function () {
        var des = "";
        des += LANG.UI_GLOBAL_STRATEGY_THREAD_NUM + ": " + $('#awsRecoveryThreadNum').val();
        $('.recoveryHighDes').html(des);
        $('.recoveryHighDes').prop('title', des);

    }

    /**
     * 初始化
     * @param params
     */
    $.fn.initAwsRecoveryTransferStrategy = function (params) {
        target_hypervisor = params.target_hypervisor;
        instance_configs = params.instance_configs;
        _platformUuid = params.platform_uuid;

        //初始化多选下拉框
        $(".selectpicker").selectpicker({
            noneSelectedText: LANG.BILLING_PLEASE_SELECT,
            deselectAllText: LANG.BILLING_DESELECT_ALL,
            selectAllText: LANG.BILLING_SELECT_ALL,
            liveSearchPlaceholder: LANG.BILLING_SEARCH,
            countSelectedText: function(){}
        });

        initSoftwareVersionDiff();
        initAgentSelect();
        if (CONF.VM_TYPE.AWS == target_hypervisor || CONF.VM_TYPE.HUAWEICLOUD == target_hypervisor) {
            initAgentNetwork(); // 初始化传输代理网络
        }

        // 传输线程
        if (CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw") {
            $('#awsRecoveryThreadNum').val(3);
        } else {
            if (4 == CONF.SOFTWARE) {
                $('.ipSegmentDiv').hide();  //免费版隐藏数据传输网段
            }
        }

        initListener();
        initHighListeners();
        $('[data-toggle="tooltip"]').tooltip();
    }

    /**
     * 获取参数，data.transfer_strategy
     * @param params
     */
    $.fn.getAwsRecoveryTransferData = function (params) {
        transport_strategy.mode = $('#transport_mode').val();
        //appliance
        transport_strategy.appliance_uuid = $('#awsApplianceSelect').find('option:selected').val();
        transport_strategy.appliance_type = $('#awsApplianceSelect').find('option:selected').val();
        if (!transport_strategy.appliance_uuid || !transport_strategy.appliance_type) {
            UIToastr.showWarning(LANG.UI_APPLIANCE_SELECT, LANG.UI_APPLIANCE_SELECT_NO_TIPS);
            return false;
        }
        // 传输线程
        transport_strategy.thread_num = parseInt($('#awsRecoveryThreadNum').val());
        let thread = transport_strategy.thread_num;
        if (thread == "" || thread > 8 || thread <= 0) {
            UIToastr.showWarning(LANG.UI_BACKUP_HIGH_SETTING_TITLE, LANG.UI_BACKUP_THREAD_NUM_TIPS);
            return false;
        }
        // 传输代理网络配置
        transport_strategy.agent_network = [];
        $.each(regionUuids, function (i, v) {
            let networkItem = {};
            // 传输代理vpc
            networkItem.vpc_id = $(`#applianceVpc_${v}`).find('option:selected').val();
            // 传输代理安全组
            networkItem.security_id = $(`#applianceSg_${v}`).find('option:selected').val();
            // 传输代理所在区域uuid
            networkItem.region_uuid = v;
            // 传输代理所在区域
            networkItem.region = regions[i];
            // 传输代理网络设置模式
            networkItem.set_mode = 'default';
            if (networkItem.vpc_id || networkItem.security_id) {
                networkItem.set_mode = 'modify';
            }
            if (CONF.VM_TYPE.AWS == target_hypervisor && !networkItem.security_id) {
                // aws安全组为自动选择时传"aws_agent_security"
                networkItem.security_id = 'aws_agent_security';
            }
            transport_strategy.agent_network.push(networkItem);
        });
        // 传输代理公有ip
        transport_strategy.agent_public_ip = {
            ip_uuid: $('select[name=agent_public_ip]').val(),
            ip_address: $('select[name=agent_public_ip] option:selected').attr('data-address'),
            chargemode: $('select[name=agent_billing_type]').val(),
            size: parseInt($('input[name=agent_billing_value]').val())
        }
        if (CONF.VM_TYPE.HUAWEICLOUD == target_hypervisor) {
            // 华为云公有ip计费信息
            if (BILLING_BANDWIDTH === _AgentBillingType.val() && _AgentBillingValue.val() > 2000) {
                UIToastr.showWarning(LANG.UI_RECOVERY_CLOUD_BANDWIDTH_SIZE, LANG.UI_RECOVERY_CLOUD_BILLING_BANDWIDTH_TIPS);
                return false;
            }
            if (BILLING_TRAFFIC === _AgentBillingType.val() && _AgentBillingValue.val() > 300) {
                UIToastr.showWarning(LANG.UI_RECOVERY_CLOUD_BANDWIDTH_SIZE, LANG.UI_RECOVERY_CLOUD_BILLING_TRAFFIC_TIPS);
                return false;
            }
            if (!_AgentBillingValue.val()  || _AgentBillingValue.val() == 0) {
                UIToastr.showWarning(LANG.UI_RECOVERY_CLOUD_BANDWIDTH_SIZE, LANG.UI_RECOVERY_CLOUD_BANDWIDTH_SIZE_EMPTY_TIPS);
                return false;
            }
        }
        // 传输加密开关
        transport_strategy.encrypt = $('#awsencrypttransfer').get(0).checked;
        // 传输加密算法
        transport_strategy.encrypt_method = parseInt($('#awsTransferEncryptMethod').val());
        // 重连次数
        transport_strategy.reconnect_times = parseInt($('#reconnect_time').val());
        // 重连间隔时间
        transport_strategy.reconnect_interval = parseInt($('#reconnect_interval').val());
        // 重连次数数字检测
        if(!Number.isInteger(transport_strategy.reconnect_times)){
            UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_RECONNECT_TIMES_TIPS);
            return false;
        }
        // 重连次数大小1 - 999
        if (transport_strategy.reconnect_times < 1) {
            UIToastr.showWarning(LANG.UI_STRATEGY_TRANSFER, LANG.UI_GLOBAL_STRATEGY_RECONNECT_TIMES_MIN_TIPS);
            return false;
        }
        // 重连间隔时间最小5
        if(transport_strategy.reconnect_interval < 5){
            UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_RECONNECT_INTERVAL_MIN_TIPS);
            return false;
        }
        // 重连间隔时间最大60
        if(transport_strategy.reconnect_interval > 60){
            UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_RECONNECT_INTERVAL_MAX_TIPS);
            return false;
        }
        // 重连间隔数字检测
        if(!Number.isInteger(transport_strategy.reconnect_interval)){
            UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_RECONNECT_INTERVAL_TIPS);
            return false;
        }

        return transport_strategy;
    }

    /**
     * 获取描述信息
     * @param params
     */
    $.fn.getAwsRecoveryTransferDes = function (params) {
        let storage_type = params.storage_type;

        let des = '';
        des += $('.awsapplianceselectlabel').text() + ": " + $('#awsApplianceSelect').find("option:selected").text(); //传输代理实例类型
        var threadnumlabel = $('.threadnumlabel').html();
        // 非磁带存储标题显示传输线程
        if (CONF.BD_STORAGE_TYPE.TAPE != storage_type) {
            des += "<br>" + threadnumlabel + ": " + $('#awsRecoveryThreadNum').val();
        }
        // 加密传输开关
        var encryptlabel = $('.encrypttransferlabel').html();
        des += "<br>" + encryptlabel + ": " + getSwitchDes(transport_strategy.encrypt);
        // 传输加密算法
        if ($('#awsencrypttransfer').get(0).checked) {
            let encryptedMethodLabel = $('.transfer-encrypt-method-label').html();
            let method = parseInt($('#awsTransferEncryptMethod').val());
            let grade = '';
            switch (method) {
                case 1:
                    grade = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_RSA;
                    break;
                case 2:
                    grade = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_SM;
                    break;
            }
            des += "<br>" + encryptedMethodLabel + ": " + grade;
        }

        return des;
    }
})(jQuery)