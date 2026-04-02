var ClientManager = function () {
    var clientList = [];
    var clientform = $('#clientForm'), addform = $('#addForm'), editform = $('#editForm');
    var intFlag = false;
    var _net_model;
    // 时间选择器全局变量,方便提交搜索的时候直接使用
    var _dateRangePicker_startTime, _dateRangePicker_endTime, _dateRangePicker_range;
    var agentType = $('#agentType').val();
    var agentTypeRemote = $('#agentTypeRemote').val();
    var _OS;
    let advancedSearch = {};
    let initOldTableCondition = false;
    var applianceFlag = false; // 当前操作的是否是传输代理，添加、修改传输代理初始化使用
    const TIMEZONE_DES_ENUM = {
        '0': LANG.UI_CLIENT_TIMEZONE_0,
        '1': LANG.UI_CLIENT_TIMEZONE_EAST_1,
        '2': LANG.UI_CLIENT_TIMEZONE_EAST_2,
        '3': LANG.UI_CLIENT_TIMEZONE_EAST_3,
        '4': LANG.UI_CLIENT_TIMEZONE_EAST_4,
        '5': LANG.UI_CLIENT_TIMEZONE_EAST_5,
        '6': LANG.UI_CLIENT_TIMEZONE_EAST_6,
        '7': LANG.UI_CLIENT_TIMEZONE_EAST_7,
        '8': LANG.UI_CLIENT_TIMEZONE_EAST_8,
        '9': LANG.UI_CLIENT_TIMEZONE_EAST_9,
        '10': LANG.UI_CLIENT_TIMEZONE_EAST_10,
        '11': LANG.UI_CLIENT_TIMEZONE_EAST_11,
        '12': LANG.UI_CLIENT_TIMEZONE_EAST_12,
        '13': LANG.UI_CLIENT_TIMEZONE_EAST_13,
        '14': LANG.UI_CLIENT_TIMEZONE_EAST_14,
        '-1': LANG.UI_CLIENT_TIMEZONE_WEST_1,
        '-2': LANG.UI_CLIENT_TIMEZONE_WEST_2,
        '-3': LANG.UI_CLIENT_TIMEZONE_WEST_3,
        '-4': LANG.UI_CLIENT_TIMEZONE_WEST_4,
        '-5': LANG.UI_CLIENT_TIMEZONE_WEST_5,
        '-6': LANG.UI_CLIENT_TIMEZONE_WEST_6,
        '-7': LANG.UI_CLIENT_TIMEZONE_WEST_7,
        '-8': LANG.UI_CLIENT_TIMEZONE_WEST_8,
        '-9': LANG.UI_CLIENT_TIMEZONE_WEST_9,
        '-10': LANG.UI_CLIENT_TIMEZONE_WEST_10,
        '-11': LANG.UI_CLIENT_TIMEZONE_WEST_11,
        '-12': LANG.UI_CLIENT_TIMEZONE_WEST_12,
    };
    // 客户端表格定时刷新
    const CLIENT_TABLE_INTERVAL = 5000;
    let clientCheckedUuidList = [];  // 客户端选中的行
    const DRIVER_STATUS_ENUM = {
        HEALTH: 1,  // 健康
        LACK_DRIVER: 2,  // 缺失驱动
        LACK_DRIVER_LIB: 3,  // 缺失驱动库
    };
    /**
     * 代理类型枚举
     */
    const AGENT_TYPE_ENUM = {
        CLIENT: 1,
        MEMORY_OS: 2,
        NAS: 3,
        TRANSFER_AGENT: 4,  // 传输代理
    };

    //初始化日期选择插件
    var initDatetimePicker = function () {
        //初始化日期时间选择控件
        var dataRangePicker = $('#advanceSearchDateRangePicker');
        dataRangePicker.daterangepicker({
            "autoUpdateInput": false,											//是否自动填充input
            "startDate": moment().subtract(6, 'days').startOf('day'),			//默认开始时间
            "endDate": moment({hour: 23, minute: 59}),												//默认结束时间
            "maxDate": moment({hour: 23, minute: 59}),												//最大可用时间
            "timePicker": true,													//是否显示时间,时分
            "timePicker24Hour": true,											//是否是24小时制
            "alwaysShowCalendars": true,										//是否总是显示日期选择
            "ranges": DateRangePickerLocales.getRangesConfig(CONF.LANGUAGE),	//根据语言定义默认ranges,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
            "locale": DateRangePickerLocales.getLocalConfig(CONF.LANGUAGE),		//根据语言定义默认local,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
        }, function (start, end, label) {
//			console.log('New date range selected: ' + start.format('YYYY-MM-DD HH:mm:ss') + ' to ' + end.format('YYYY-MM-DD HH:mm:ss') + ' predefined range: ' + label);
        });

        //如果不是选择后自动填充input(autoUpdateInput:true),需要监听下面两个方法apply.daterangepicker和cancel.daterangepicker
        dataRangePicker.on('apply.daterangepicker', function (ev, picker) {
            //给全局变量赋值,然后设置input
            _dateRangePicker_startTime = picker.startDate.format('YYYY-MM-DD HH:mm:ss');
            _dateRangePicker_endTime = picker.endDate.format('YYYY-MM-DD HH:mm:ss');
            _dateRangePicker_range = picker.chosenLabel;
            $(this).val(picker.startDate.format('YYYY-MM-DD HH:mm') + ' - ' + picker.endDate.format('YYYY-MM-DD HH:mm'));
        });

        dataRangePicker.on('cancel.daterangepicker', function (ev, picker) {
            //清除全局变量,然后设置input
            _dateRangePicker_startTime = "";
            _dateRangePicker_endTime = "";
            _dateRangePicker_range = "";
            $(this).val('');
        });

        //input右侧的图标事件
        $('.daterangepickerdiv i').click(function () {
            $(this).parent().find('input').click();
        });
    }

    /**
     * @param agent_ip
     * @param {object} row
     * @param {object} row.add_ip
     * @return string
     */
    const ipFormatter = (agent_ip, row) => {
        let name = '--/';
        if (typeof row.add_ip === 'string' && row.add_ip.length) {
            name = row.add_ip + '/';
        }
        name += agent_ip;
        return `<span title="${name}">${name}</span>`;
    };

    const osFormatter = (os_type,row) => {
        if(os_type == ''){
            return '--'
        }
        return os_type + "(" + row.os_version + ")";
    }

    const hostnameFormatter = (hostname, row) => {
        let showName = `${hostname}/${row.alias}`;
        return `<span title="${showName}">${showName}</span>`;
    };

    const agentPoolNameFormatter = (agentPoolName) => {
        if (!Array.isArray(agentPoolName)|| !agentPoolName.length) {
            return '--';
        }
        return `<span title="${agentPoolName.join('\n')}">${agentPoolName.join('<br>')}</span>`;
    };

    /**
     * 版本格式化
     * @param version
     * @param {{upgrade_flag: '', upgrade_version: ''}} row
     */
    const versionFormatter = (version, row) => {
        let content = ``;
        let title = version;
        if (row.upgrade_flag) {
            let popoverContent = `<span class=\'agent_upgrade_popover\'>${LANG.UI_CLIENT_PLUGIN_VERSION_TIPS1 + row.upgrade_version}</span>`;
            content = `
            <span class="popovers" data-container="body" data-trigger="hover"
                data-html="true" data-placement="right" data-content="${popoverContent}">
                <i class="viconfont vicon-a-Frame1000002986" style="color: #0fbf98"></i>
            </span>`;
            title += ', ' + LANG.UI_CLIENT_PLUGIN_VERSION_TIPS1 + row.upgrade_version;
        }
        return `
        <span title="${title}">
            <span>${version}</span>
            ${content}
        </span>`;
    };

    /**
     * 状态格式化
     */
    const statusFormatter = (value, row) => {
        let labelClass = !!row.online_status ? 'label-success' : 'label-default';
        if (parseInt(row.agent_type) !== AGENT_TYPE_ENUM.MEMORY_OS) {
            return `<span class="label label-sm ${labelClass}" title="${row.online_status_des}(${row.deploy_status_des})">
                ${row.online_status_des}(${row.deploy_status_des})
            </span>`;
        }
        let driverStatus = parseInt(row.driver_status);
        if (driverStatus === DRIVER_STATUS_ENUM.HEALTH) {
            return `<span class="label label-sm ${labelClass}" title="${row.online_status_des}(${row.deploy_status_des})">
                ${row.online_status_des}(${row.deploy_status_des})
            </span>`;
        }
        let text = ``;
        let content = ``;
        labelClass = 'label-warning';
        if (driverStatus === DRIVER_STATUS_ENUM.LACK_DRIVER_LIB) {  // 驱动库缺失
            text = LANG.UI_CLIENT_DRIVER_STATUS_ABNORMAL;
            content = '<span class=\'driver_lack_popover\'>' + LANG.UI_CLIENT_DRIVER_STATUS_ABNORMAL_LACK_DRIVER_LIB + '<br><br>';
            content += LANG.UI_CLIENT_DRIVER_STATUS_ABNORMAL_LACK_HARDWARE + '<br>';
            let lack_hardware_list = [];
            if (Array.isArray(row.hardware_info)) {
                for (const hardwareInfo of row.hardware_info) {
                    if (hardwareInfo['status'] !== 'unknown') {
                        continue;
                    }
                    lack_hardware_list.push(hardwareInfo['description'] + '<br>' + hardwareInfo['hardware_id_list'][0]);
                }
            } else {
                content = '<span class=\'driver_lack_popover\'>' + LANG.UI_CLIENT_DRIVER_STATUS_ABNORMAL_FAILED_OBTAIN_DATA + '</span>';
            }
            content += lack_hardware_list.join('<br><br>');
            content += `</span>`;
        } else {  // 驱动缺失
            text = LANG.UI_CLIENT_DRIVER_STATUS_ABNORMAL;
            content = '<span class=\'driver_lack_popover\'>' + LANG.UI_CLIENT_DRIVER_STATUS_ABNORMAL_LACK_CLIENT + '<br><br>';
            content += LANG.UI_CLIENT_DRIVER_STATUS_ABNORMAL_LACK_HARDWARE + '<br>';
            let lack_hardware_list = [];
            if (Array.isArray(row.hardware_info)) {
                for (const hardwareInfo of row.hardware_info) {
                    if (hardwareInfo['status'] !== 'unknown') {
                        continue;
                    }
                    lack_hardware_list.push(hardwareInfo['description'] + '<br>' + hardwareInfo['hardware_id_list'][0]);
                }
            } else {
                content = '<span class=\'driver_lack_popover\'>' + LANG.UI_CLIENT_DRIVER_STATUS_ABNORMAL_FAILED_OBTAIN_DATA + '</span>';
            }
            content += lack_hardware_list.join('<br><br>');
            content += `</span>`;
        }
        return `
        <span class="label label-sm ${labelClass}">
            <a style="background: transparent" class="popovers" data-container="body" data-trigger="hover"
                data-html="true" data-placement="left" data-content="${content}">
                ${row.online_status_des}(${text})
            </a>
        </span>
        `;
    };

    // GMP状态格式化
    const statusGMPFormatter = (value, row) => {
        let labelClass = !!row.online_status ? 'label-success' : 'label-default';
        return `
        <span class="label label-sm ${labelClass}">
            ${row.online_status_des}
        </span>
        `;
    }
    const backupStatusFormatter = (value,row) => {
        let labelClass = row.backup_flag ? 'label-success' : 'label-default';
        let status = '';
        let title = '';
        if(row.backup_flag){
            status = LANG.UI_CLIENT_BACKED;
            title = row.backup_time;
        }else{
            status = LANG.UI_CLIENT_NOT_BACK;
        }
        return `
        <span class="label label-sm ${labelClass}" title="${title}">
            ${status}
        </span>
        `;
    }
    const verifyStatusFormatter = (value,row) => {
        let labelClass = row.verify_flag ? 'label-success' : 'label-default';
        let status = '';
        let title = '';
        if(row.verify_flag){
            status = LANG.UI_CLIENT_VERIFYED;
            title = row.verify_time
        }else{
            status = LANG.UI_CLIENT_NOT_VERIFY;
        }
        return `
        <span class="label label-sm ${labelClass}" title="${title}">
            ${status}
        </span>
        `;
    }

    const clientOpFormatter = function (value, row, index) {
        let button = '<div class="btn-group dropdown-wrapper">';
        if (row.total >= 6) {
            let rowNum = Math.ceil(row.total / 2) - 1;
            if (rowNum < index) {
                button = '<div class="btn-group dropup dropdown-wrapper">';
            }
        }
        button += `<button type="button" class="btn btn-dropdown-operate dropdown-toggle" data-toggle="dropdown"
						data-hover="dropdown" aria-haspopup="true" data-delay="1000" data-close-others="true" aria-expanded="false">
						${LANG.UI_PUBLIC_OPERATION}
						<i class="fa fa-angle-down"></i>
					</button>
					<ul class="dropdown-menu">`;
        // 传输代理-代理配置
        if (row.op.includes(6)) {
            button += '<li class="agentConfig"><button class="btn dropdown-menu__item me-0" type="button"" ><i class="viconfont vicon-ge_configuration me-4"></i> ' + LANG.UI_CLIENT_AGENT_CONFIG + '</a></li>';
        }

        //1 引用配置  2 修改授权
        for (let i = 0; i < row.op.length; i++) {
            switch (row.op[i]) {
                case 1:
                    button += '<li class="appconfig"><button class="btn dropdown-menu__item me-0" type="button"" ><i class="viconfont vicon-ge_configuration me-4 mt-0"></i> ' + LANG.UI_CLIENT_APP_CONFIG + '</button></li>';
                    break;
                case 3:
                    button += '<li class="refreshClient"><button class="btn dropdown-menu__item me-0" type="button""><i class="viconfont vicon-ge_refresh me-4 mt-0"></i> ' + LANG.UI_CLIENT_REFRESH + '</button></li>';
                    break;
                case 4:
                    button += '<li class="clientDetail"><button class="btn dropdown-menu__item me-0" type="button""><i class="viconfont vicon-tenant-detail me-4 mt-0"></i> ' + LANG.UI_ALARM_DETAILS + '</button></li>';
                    break;
                case 5:
                    button += `<li class="clientLogDownload"><button class="btn dropdown-menu__item me-0" type="button""><i class="viconfont vicon-baogaoxiazaishijian me-4 mt-0"></i> ${LANG.UI_CLIENT_LOG_DOWNLOAD}</button></li>`;
                    break;
            }
        }
        button += '</ul></div>';
        // 开启以下会导致页面卡顿
        // $('[data-hover="dropdown"]').dropdownHover();
        return button;
    };

    const initClientBtnOp = () => {
        return {
            'click .appconfig': (e, value, row) => {
                checkOperateAuth(checkAuth([row]), () => {
                    addAppConfig(e, value, row);
                });
            },
            'click .refreshClient': (e, value, row) => {
                checkOperateAuth(checkAuth([row], parseInt(row.agent_type) === 4 ? 2 : 10), () => {
                    refreshClient(e, value, row);
                });
            },
            'click .clientDetail': initDetails,
            'click .clientLogDownload': (e, value, row) => {
                checkOperateAuth(checkAuth([row], parseInt(row.agent_type) === 4 ? 2 : 10), () => {
                    initLogDownload(e, value, row);
                });
            },
            'click .agentConfig': (e, value, row) => {
                checkOperateAuth(checkAuth([row], 2), () => {
                    initAgentConfig(e, value, row);
                });
            },
        };
    };

    const getClientTableColumns = () => {
        let versionWidth = 8;
        if (window.innerWidth <= 1440) {
            versionWidth = 10;
        }
        let columns = [{
            checkbox: true,
            width: '2',
            widthUnit: '%',
            sortable: false,
            formatter: function (value, row, index, field) {
                if (row.op_flag === false) {//创建者等不是当前用户，不能操作
                    return {
                        disabled: true
                    };
                }
            }
        }, {
            title: LANG.UI_CLIENT_ADD_IP + '/' + LANG.UI_CLIENT_CONNECT_IP,
            field: 'agent_ip',
            width: '12',
            widthUnit: '%',
            formatter: ipFormatter,
        }, {
            title: LANG.UI_CLIENT_HOST_NICKNAME,  // #21366: 拆分为主机名、别名
            field: 'hostname',
            width: '12',
            widthUnit: '%',
            formatter: hostnameFormatter,
        }, {
            title: LANG.UI_CLIENT_OS,
            field: 'os_version',
            width: '15',
            widthUnit: '%',
        }, {
            title: LANG.UI_CLIENT_TYPE,
            field: 'agent_type_des',
            width: '5',
            widthUnit: '%',
        }, {
            title: LANG.UI_CLIENT_APP_CONFIG,
            field: 'app_des',
            width: '10',
            widthUnit: '%',
        }, {
            title: LANG.UI_AGENT_POOL,
            field: 'agent_pool_name',
            sortable: false,
            width: '10',
            widthUnit: '%',
            formatter: agentPoolNameFormatter,
        }, {
            title: LANG.UI_CLIENT_VERSION,
            field: 'agent_version',
            width: versionWidth,
            widthUnit: '%',
            formatter: versionFormatter,
        }, {
            title: LANG.UI_PUBLIC_ADD_TIME,
            field: 'register_time',
            width: '10',
            widthUnit: '%',
        }, {
            title: LANG.UI_PUBLIC_STATUS,
            field: 'online_status',
            formatter: statusFormatter,
            width: '8',
            widthUnit: '%',
        }, {
            title: LANG.UI_CLIENT_CREATOR,
            field: 'creator',
            width: '5',
            widthUnit: '%',
        }, {
            title: LANG.UI_CLIENT_OWNER,
            field: 'owner',
            width: '5',
            widthUnit: '%',
            sortable: false,
        }, {
            title: LANG.UI_PUBLIC_OPERATION,
            width: '5',
            widthUnit: '%',
            sortable: false,
            events: initClientBtnOp(),
            clickToSelect: false,
            formatter: clientOpFormatter,
            opButton: true,
        }];

        // 如果没有授权代理资源池，这里不显示代理资源池列
        if (!CONF.PERMISSION.includes('agent_pool')) {
            let agentPoolIndex = -1;
            for (const index in columns) {
                if (columns[index].field === 'agent_pool_name') {
                    agentPoolIndex = index;
                    break;
                }
            }
            if (agentPoolIndex !== -1) {
                columns.splice(agentPoolIndex, 1);
            }
        }

        return columns;
    };

    //获取GMP设备管理页面表格列
    const getGMPClientTableColumns = function () {
        return [{
            checkbox: true,
            width: '2',
            widthUnit: '%',
            sortable: false,
            formatter: function (value, row, index, field) {
                if (row.op_flag === false) {//创建者等不是当前用户，不能操作
                    return {
                        disabled: true
                    };
                }
            }
        },
        {
            title: LANG.UI_CLIENT_HOST_NAME,
            field: 'hostname',
            width: '10',
            widthUnit: '%',
            // formatter: hostnameFormatter,
        },
        {
            title: LANG.UI_CLIENT_ALIAS,
            field: 'alias',
            width: '10',
            widthUnit: '%',
        },
        {
            title: LANG.UI_CLIENT_IP_ADDRESS,
            field: 'agent_ip',
            width: '10',
            widthUnit: '%',
        },
        {
            title: LANG.UI_CLIENT_OS,
            field: 'os_type',
            width: '14',
            widthUnit: '%',
            formatter:osFormatter
        },
        {
            title: LANG.UI_PUBLIC_STATUS,
            field: 'online_status',
            formatter: statusFormatter,
            width: '9',
            widthUnit: '%',
        },
        {
            title: LANG.UI_SEARCH_BACKUP_STATUS,
            field: 'backup_flag',
            formatter: backupStatusFormatter,
            width: '6',
            widthUnit: '%',
        },
        {
            title: LANG.UI_CLIENT_VERIFY_STATUS,
            field: 'verify_flag',
            formatter: verifyStatusFormatter,
            width: '6',
            widthUnit: '%',
        },
        {
            title: LANG.UI_CLIENT_DEVICE_CODE,
            field: 'agent_uuid',
            width: '28',
            widthUnit: '%',
        },
        {
            title: LANG.UI_PUBLIC_OPERATION,
            width: '5',
            widthUnit: '%',
            sortable: false,
            events: initClientBtnOp(),
            clickToSelect: false,
            formatter: clientOpFormatter,
            opButton: true,
        }];
    };
    /**
     * 设置选中事件
     */
    const checkEvent = function (tableId, btnId) {
        let select = $('' + tableId + '').bootstrapTable('getSelections');
        if (select.length == 0) {
            $('' + btnId + ' i').addClass('icon-gray-delete');
            $('' + btnId + ' i').removeClass('icon-white-delete');
            $('' + btnId + '').removeClass('select-delete-btn');
            $('' + btnId + '').addClass('cancel-delete-btn');
        } else {
            $('' + btnId + ' i').removeClass('icon-gray-delete');
            $('' + btnId + ' i').addClass('icon-white-delete');
            $('' + btnId + '').removeClass('cancel-delete-btn');
            $('' + btnId + '').addClass('select-delete-btn');
        }
    }

    const clientRowCheck = () => {
        checkEvent('#clientDatatable', '#deleteClient');
        let rows = $('#clientDatatable').bootstrapTable('getSelections');
        clientCheckedUuidList = rows.map((item) => item.agent_uuid);
    };

    /**
     * 自定义按钮
     */
    const clientCustomTool = () => {
        let beforeInput = `<div class="btn-group" style="margin: 0">`;
        // 删除客户端
        if (CONF.PERMISSION_ARR.includes('p_agent_manager_delete')) {
            beforeInput += `
            <button type="button" id="deleteClient" class="b-btn brr2 mr12 table-toolbar-btn">
                <i class="icon-gray-delete"></i>
            </button>
            `;
        }
        beforeInput += `</div>`;

        let afterInput = `<div class="btn-group" style="margin: 0">`;
        // 添加客户端
        if (CONF.PERMISSION_ARR.includes('p_agent_manager_register')) {
            afterInput += `
            <button class="dropdown-toggle btn-font flex_center btn-title p-lr8 btn table-toolbar-btn" id="addClient"
                style="width: auto; height: 34px; border: 0">
                <i class="viconfont vicon-biaogetianjia"></i>
                <span class="pl2">${LANG.UI_PUBLIC_ADD}</span>
            </button>
            `;
        }
        // 修改客户端
        if (CONF.PERMISSION_ARR.includes('p_agent_manager_modify')) {
            afterInput += `
            <button class="dropdown-toggle btn-font flex_center btn-title p-lr8 btn table-toolbar-btn" id="editClient"
                style="width: auto; height: 34px; border: 0">
                <i class="viconfont vicon-xiugai"></i>
                <span class="pl2">${LANG.UI_PUBLIC_EDIT}</span>
            </button>
            `;
        }
        // 下载客户端
        afterInput += `
        <button class="dropdown-toggle btn-font flex_center btn-title p-lr8 btn table-toolbar-btn" id="downloadClient"
            style="width: auto; height: 34px; border: 0">
            <i class="viconfont vicon-xiazai"></i>
            <span class="pl2">${LANG.UI_PUBLIC_DOWNLOAD}</span>
        </button>
        `;
        // 升级客户端
        if (CONF.PERMISSION_ARR.includes('p_agent_manager_upgrade')) {
            afterInput += `
            <button class="dropdown-toggle btn-font flex_center btn-title p-lr8 btn table-toolbar-btn" id="upgradeClient"
                style="width: auto; height: 34px; border: 0">
                <i class="viconfont vicon-shengji"></i>
                <span class="pl2">${LANG.UI_CLIENT_UPGRADE_BTN}</span>
            </button>
            `;
        }
        // 驱动安装
        if (CONF.PERMISSION_ARR.includes('p_agent_manager_driver_install')) {
            afterInput += `
            <button class="dropdown-toggle btn-font flex_center btn-title p-lr8 btn table-toolbar-btn" id="installDriver"
                style="width: auto; height: 34px; border: 0">
                <i class="viconfont vicon-a-Hunting-gearcongdongzhuangzhi"></i>
                <span class="pl2">${LANG.UI_CLIENT_INSTALL_DRIVER_BTN}</span>
            </button>
            `;
        }
        afterInput += `</div>`;

        let beforeAdvance = `
        <button type="button" class="btn btn-primary adv_btn brr2 p-lr8 agentAdvanced">
            <i class="adv-search"></i>
            ${LANG.UI_JOB_SEARCH_EXP}
        </button>
        `;
        let afterAdvance = ``;
        return {beforeInput, afterInput, beforeAdvance, afterAdvance};
    };

    /**
     * 初始化旧过滤数据
     * @param filterCache
     */
    const initOldTableFilter = (filterCache) => {
        if (typeof filterCache.host_ip_alias !== 'undefined') {  // 普通搜索
            $('#clientmanagerdiv .clientSearch').val(filterCache.host_ip_alias);
        }
        let h_status = '';
        if (typeof filterCache.h_online_status !== 'undefined' && typeof filterCache.h_deploy_status !== 'undefined') {
            h_status = `${filterCache.h_online_status}-${filterCache.h_deploy_status}`;
        }
        advancedSearch = {
            start_time: filterCache?.h_start_time,
            end_time: filterCache?.h_end_time,
            ip: filterCache?.h_ip,
            hostname: filterCache?.h_hostname,
            alias: filterCache?.h_alias,
            app_name: filterCache?.h_app_name,
            owner: filterCache?.h_owner,
            os_version: filterCache?.h_os_version,
            status: h_status,
        };
        // 初始化页面元素
        if (advancedSearch.ip) {
            $('#advanceSearchIp').val(advancedSearch.ip);
        }
        if (advancedSearch.hostname) {
            $('#advanceSearchHostname').val(advancedSearch.hostname);
        }
        if (advancedSearch.alias) {
            $('#advanceSearchAlias').val(advancedSearch.alias);
        }
        if (advancedSearch.app_name) {
            $('#advanceSearchAppName').val(advancedSearch.app_name);
        }
        if (advancedSearch.owner) {
            $('#advanceSearchOwner').val(advancedSearch.owner);
        }
        if (advancedSearch.os_version) {
            $('#advanceSearchOsVersion').val(advancedSearch.os_version);
        }
        if (advancedSearch.status) {
            $('#advanceSearchStatus').val(advancedSearch.status);
        }
        if (advancedSearch.start_time && advancedSearch.end_time) {
            _dateRangePicker_startTime = advancedSearch.start_time;
            _dateRangePicker_endTime = advancedSearch.end_time;
            let showTime = moment(_dateRangePicker_startTime).format('YYYY-MM-DD HH:mm')
                + ' - ' + moment(_dateRangePicker_endTime).format('YYYY-MM-DD HH:mm');
            let timePickerTag = $('#advanceSearchDateRangePicker');
            timePickerTag.val(showTime);
            timePickerTag.data('daterangepicker').setStartDate(_dateRangePicker_startTime);  // 这里设置最新时间
            timePickerTag.data('daterangepicker').setEndDate(_dateRangePicker_endTime);  // 这里设置最新时间
        }
        addSearchContent();
    };

    /**
     * 获取表格查询
     * @returns {Object}
     */
    const getQueryParams = () => {
        let params = {};
        if (initOldTableCondition) {
            initOldTableCondition = false;
            let filterCache = window.sessionStorage.getItem('clientDatatable_filterRecord');
            if (null !== filterCache && typeof filterCache === 'string') {
                initOldTableFilter(JSON.parse(filterCache));
            }
        }
        // 按主机名/IP/别名搜索
        let searchValue = $('#clientmanagerdiv .clientSearch').val().trim();
        if (searchValue) {
            params.host_ip_alias = searchValue;
        }

        // 高级搜索
        // 添加时间
        if (
            typeof advancedSearch.start_time !== 'undefined' && typeof advancedSearch.end_time !== 'undefined' &&
            advancedSearch.start_time && advancedSearch.end_time
        ) {
            params.h_start_time = advancedSearch.start_time;
            params.h_end_time = advancedSearch.end_time;
        }
        // IP地址
        if (typeof advancedSearch.ip !== 'undefined' && advancedSearch.ip) {
            params.h_ip = advancedSearch.ip;
        }
        // 主机名
        if (typeof advancedSearch.hostname !== 'undefined' && advancedSearch.hostname) {
            params.h_hostname = advancedSearch.hostname;
        }
        // 别名
        if (typeof advancedSearch.alias !== 'undefined' && advancedSearch.alias) {
            params.h_alias = advancedSearch.alias;
        }
        // 操作系统
        if (typeof advancedSearch.os_version !== 'undefined' && advancedSearch.os_version) {
            params.h_os_version = advancedSearch.os_version;
        }
        // 状态
        if (typeof advancedSearch.status !== 'undefined' && advancedSearch.status) {
            let statusArr = advancedSearch.status.split('-');
            params.h_online_status = parseInt(statusArr[0]);
            params.h_deploy_status = parseInt(statusArr[1]);
        }
        // 应用名
        if (typeof advancedSearch.app_name !== 'undefined' && advancedSearch.app_name) {
            params.h_app_name = advancedSearch.app_name;
        }
        // 所有者
        if (typeof advancedSearch.owner !== 'undefined' && advancedSearch.owner) {
            params.h_owner = advancedSearch.owner;
        }
        if (Object.keys(params).length) {
            window.sessionStorage.setItem('clientDatatable_filterRecord', JSON.stringify(params));
        } else {
            window.sessionStorage.removeItem('clientDatatable_filterRecord');
        }
        return params;
    };

    // const initClientTableHeight = () => {
    //     let toolbarHeight = 47;
    //     let paginationHeight = 52;
    //     let alertHeight = 178;
    //     let tabTitleHeight = 48;
    //     let navTitleHeight = 46;
    //     // page-content有40px的内边距
    //     // portlet-body有10px的内边距
    //     // tab-content有20px的外边距离
    //     let otherHeight = 40 + 10 + 20;
    //     let height = window.innerHeight - toolbarHeight - paginationHeight - alertHeight - tabTitleHeight - navTitleHeight - otherHeight;

    //     $("#clientlist .fixed-table-body").css({
    //         "height": height
    //     });
    // };

    /**
     * 刷新客户端表格数据
     */
    const refreshClientTable = () => {
        if (timerTask.ClientTableTimer) {
            clearTimeout(timerTask.ClientTableTimer);
            timerTask.ClientTableTimer = null;
        }
        timerTask.ClientTableTimer = setTimeout(() => {
            $('#clientDatatable').bootstrapTable('refresh');
        }, CLIENT_TABLE_INTERVAL);
    };

    const getClientTableOption = (defaultOption) => {
        return {
            vin_url: '/api/v1/agents',
            vin_params: getQueryParams,
            vin_method: 'get',
            toolbarId: '#vin_client_toolbar',
            vin_toolbar: '.vin_client_toolbar',
            buttonsToolbar: '.vin_client_btnToolbar',
            // 搜索相关
            searchInput: true,
            placeholder: LANG.UI_CLIENT_SEARCH_PLACEHOLDER,
            searchClass: 'clientSearch',
            searchSelector: '.clientSearch',
            showSearchButton: true,
            // 排序
            sortName: defaultOption.sortName,
            sortOrder: defaultOption.sortOrder,
            // 隐藏行
            hideColumns: 'agent_pool_name,driver_status',
            showButtonText: false,
            clickToSelect: true,
            pagination: true,
            pageList: [10, 20, 50, 100, 150, 200],
            pageSize: defaultOption.pageSize,
            pageNumber: defaultOption.pageNumber,
            resizable: true,
            showRefresh: false,
            showExport: false,
            // onResetView: initClientTableHeight,
            customSearch: (data, text, filters) => {  // 这里会影响到高级搜索
                return data.filter(() => true);
            },
            onRefresh: () => {
                $("#clientDatatable").bootstrapTable('hideLoading');
            },
            onPostBody: () => {
                $('span.driver_lack_popover').closest('div.popover.in').remove();
                $('span.agent_upgrade_popover').closest('div.popover.in').remove();
                $('#clientDatatable .popovers').popover();
                refreshClientTable();
                if (clientCheckedUuidList.length) {
                    $('#clientDatatable').bootstrapTable('checkBy', {field: 'agent_uuid', values: clientCheckedUuidList});
                }
                clientRowCheck();
            },
            onCheck: clientRowCheck,
            onUncheck: clientRowCheck,
            onCheckAll: clientRowCheck,
            onUncheckAll: clientRowCheck,
            onSort: (sortName, sortOrder) => {
                window.sessionStorage.setItem('clientDatatable_sortRecord', JSON.stringify({sortName, sortOrder}));
            },
            customTool: clientCustomTool(),
            columns: CONF.VENDOR == CONF.VENDOR_LIST.gmp? getGMPClientTableColumns():getClientTableColumns(),
        };
    };

    /**
     * 加载保存到sessionStorage的数据
     * 1. 分页
     * 2. 高级搜索
     *  a. 设置高级搜索状态
     * 3. 搜索
     * 4. 排序
     * bug#16951: 需求
     *  1. 从应用配置、日志下载点击面包屑导航会来的要保持搜索状态和分页状态
     */
    const getTableDefaultData = () => {
        // 分页bss
        let pageCache = window.sessionStorage.getItem('clientDatatable_pageRecord');
        if (null !== pageCache && typeof pageCache === 'string') {
            pageCache = JSON.parse(pageCache);
        } else {
            pageCache = {offset: 0, limit: 20};
        }
        // 排序
        let sortCache = window.sessionStorage.getItem('clientDatatable_sortRecord');
        if (null !== sortCache && typeof sortCache === 'string') {
            sortCache = JSON.parse(sortCache);
        } else {
            sortCache = {sortName: 'register_time', sortOrder: 'desc'};
        }
        // 搜索处理
        return {
            sortName: sortCache.sortName,
            sortOrder: sortCache.sortOrder,
            pageSize: pageCache.limit,
            pageNumber: pageCache.offset + 1,
        }
    };

    /**
     * 初始化客户端表格
     */
    const initClientTable = () => {
        initOldTableCondition = parseInt($('#loadOldTableFlag').val()) === 1;
        if (!initOldTableCondition) {
            window.sessionStorage.removeItem('clientDatatable_pageRecord');
            window.sessionStorage.removeItem('clientDatatable_sortRecord');
            window.sessionStorage.removeItem('clientDatatable_filterRecord');
        }
        let defaultOption = getTableDefaultData();
        $('#clientDatatable').bootstrapTable('destroy').baseTableConfig().init(getClientTableOption(defaultOption));
    };

    //得到开启和关闭的HTML内容
    const getFlagLevelInfo = function(flag){
        var html = '<span class="label label-success">' + LANG.UI_PUBLIC_ON + '</span>';
        if(!flag){
            html = '<span class="label label-warning">' + LANG.UI_PUBLIC_OFF + '</span>';
        }
        return html;
    }

    /**
     * 根据授权显示不同的详情项
     */
    const showDetailItemByPermission = () => {
        // 代理资源池
        if (!CONF.PERMISSION.includes('agent_pool')) {
            $('#detailAgentPoolDiv').hide();
        } else {
            $('#detailAgentPoolDiv').show();
        }
    };

    /**
     * 初始化客户端详情信息
     * @param e
     * @param value
     * @param {Object} row
     * @param {String} row.agent_ip
     * @param {String} row.register_time
     * @param {String} row.hostname
     * @param {String} row.alias
     * @param {String} row.add_ip
     * @param {String} row.os_version
     * @param {String} row.agent_version
     * @param {String} row.module
     * @param {String} row.net_model_des
     * @param {String} row.app_des
     * @param {Number} row.net_model
     * @param {String} row.agent_port
     * @param {String} row.auto_change_network_flag
     * @param {String} row.agent_uuid
     */
    const initDetails = function (e, value, row) {
        $('#detailIp').html(ipFormatter(row.agent_ip, row));
        $('#detailHostname').html(row.hostname + "/" + row.alias);
        $('#detailOs').html(row.os_version);
        $('#detailVersion').html(row.agent_version);
        $('#detailStatus').html(statusFormatter('', row));
        $('#detailCreatetime').html(row.register_time);
        $('#detailMode').html(row.net_model_des);
        $('#detailapp').html(row.app_des);
        $('#detailAgentType').html(row.agent_type_des);
        $('#detailAgentPool').html(agentPoolNameFormatter(row.agent_pool_name));
        $('#detailCreator').html(row.creator);
        $('#detailOwner').html(row.owner);
        let timezone = typeof TIMEZONE_DES_ENUM[row.time_zone_offset] !== 'undefined' ? TIMEZONE_DES_ENUM[row.time_zone_offset] : row.time_zone_offset;
        $('#detailTimezone').html(timezone);
        if (row.net_model === 1) {
            $('#detailClientport').html(row.agent_port);
            $('.serverportdiv').hide();
            $('.clientportdiv').show();
            $('.detailportDiv').show();
            $('.detailAutoChangeNetworkFlagDiv').show();
            $('#detailAutoChangeNetworkFlag').html(getFlagLevelInfo(row.auto_change_network_flag));
        } else {
            $('.detailportDiv').hide();
            $('.serverportdiv').show();
            $('.clientportdiv').hide();
            $('.detailAutoChangeNetworkFlagDiv').hide();
        }
        if (parseInt(row.agent_type) === 4) {
            $('#detailClientTitle').hide();
            $('#detailProxyTitle').show();
            $('#detailClientVersion').hide();
            $('#detailProxyVersion').show();
            $('.serverportdiv').hide();
            $('.clientportdiv').hide();
            $('.proxyportdiv').show();
        } else {
            $('#detailClientTitle').show();
            $('#detailProxyTitle').hide();
            $('#detailClientVersion').show();
            $('#detailProxyVersion').hide();
            $('.proxyportdiv').hide();
        }
        //初始化网卡列表
        initNetworkTable(row.agent_uuid);
    }

    // 初始化传输代理配置模态框
    var initAgentConfig = function (e, value, row) {
        let agentModal = $('#agentModal');
        Metronic.blockUI({target: '#agentModal',animate: true});
        pAjaxRequest({agent_uuid: row.agent_uuid}, "/api/v1/agents/domain_config", "GET", function (d) {
            Metronic.unblockUI('#agentModal');
            if (d.success) {
                agentModal.find('#dnslist').text(d.data.domain_config);
            } else {
                agentModal.find('#dnslist').text(row.domain_config);
            }
        }, false);
        agentModal.find('#agent_uuid').val(row.agent_uuid);
        agentModal.find('#ipaddr').val(row.agent_ip);
        agentModal.find('#net_model').val(row.net_model);
        agentModal.find('#port').val(row.agent_port);
        agentModal.find('#nickname').val(row.alias);
        agentModal.find('#auto_change_network').val(row.auto_change_network_flag);
        agentModal.modal({'width': "700px"});
        agentModal.on('shown', function () {
            agentModal.find(".modal-body").css("height", "500px");
            agentModal.find(".modal-body").css("overflow", "auto");
        });

        // 已分配vcenter
        initOldVcenters(row.applied_vcenters, 'applied_vcenters_config')
        // 根据授权显示不同的详情项
        showDetailItemByPermission();
    }

    // 提交传输代理配置
    var submitAgentConfig = function () {
        let params = {
            agent_uuid: $('#agent_uuid').val(),
            domain_config: $('#dnslist').val(),
            ip: $('#ipaddr').val(),
            net_model: parseInt($('#net_model').val()),
            port: parseInt($('#port').val()),
            nickname: $('#agent_nickname').val(),
            auto_change_network_flag: 'true' == $('#auto_change_network').val(),
            // 传输代理应用到虚拟化
            applied_vcenters: $('#applied_vcenters_config').selectpicker('val')
        };
        Metronic.blockUI({target: '#agentModal',animate: true});
        pAjaxRequest(params, "/api/v1/agents/domain_config", "PUT", function (d) {
            Metronic.unblockUI('#agentModal');
            if (d.success) {
                UIToastr.showSuccess(LANG.UI_CLIENT_AGENT_CONFIG, LANG.UI_CLIENT_AGENT_CONFIG_SUCCESS_TIPS);
                $('#clientDatatable').bootstrapTable('refresh');
                $('#agentModal').modal('hide');
            } else {
                UIToastr.showWarning(LANG.UI_CLIENT_AGENT_CONFIG, LANG.UI_CLIENT_AGENT_CONFIG_FAIL_TIPS);
            }
        }, true);
    }

    const initLogDownload = function (e, value, row) {
        let agentName = `${row.alias}(${row.agent_ip})`;
        if (row.alias === row.agent_ip) {
            agentName = `${row.hostname}(${row.agent_ip})`;
        }
        let url = './content/client/log_download.php?uuid=' + row.agent_uuid + '&name=' + agentName + '&ip=' + row.agent_ip + '&os_type=' + row.os_type + '&agent_type=' + row.agent_type;
        LOCATION(url,CONF.VENDOR == CONF.VENDOR_LIST.gmp ? "clients" : "infrastructure");
    };

    const getClientDetailNetworkTableOption = (agentUuid) => {
        return {
            vin_url: `/api/v1/agents/${agentUuid}/network_card`,
            vin_method: 'GET',
            toolbarId: '#vin_client_detail_network_toolbar',  // 占位
            vin_toolbar: '#vin_client_detail_network_toolbar',
            sortable: false,
            pagination: false,
            // pageList: [10, 20, 100, 150, 200],
            // pageSize: 20,
            onResetView: () => {
                $("#networkTableWrapper .fixed-table-body").css({
                    "max-height": 300,
                    'height': 'auto',
                });
            },
            columns: [{
                title: LANG.UI_PUBLIC_TABLE_ID,
                formatter: (value, row, index) => {
                    return index + 1;
                }
            }, {
                title: LANG.UI_CLIENT_NETWORK_NIC_NAME,
                field: 'network_name',
            }, {
                title: LANG.UI_CLIENT_NETWORK_MAC,
                field: 'mac',
            }, {
                title: LANG.UI_CLIENT_NETWORK_IP,
                field: 'ip',
            }, {
                title: LANG.UI_CLIENT_NETWORK_NETMASK,
                field: 'netmask',
            }, {
                title: LANG.UI_CLIENT_NETWORK_GATEWAY,
                field: 'gateway',
            }],
        }
    };

    //初始化网卡列表
    const initNetworkTable = (agentUuid) => {
        // 清除分页缓存
        window.sessionStorage.removeItem('networkTable2_pageRecord');
        $('#networkTable2').bootstrapTable('destroy').baseTableConfig().init(getClientDetailNetworkTableOption(agentUuid));
        $('#detailsDrawer').drawer('show');
    }

    //跳转到应用配置页面
    var addAppConfig = function (e, value, row) {
        let agentName = `${row.alias}(${row.agent_ip})`;
        if (row.alias === row.agent_ip) {
            agentName = `${row.hostname}(${row.agent_ip})`;
        }
        let url = './content/client/application_config.php?uuid=' + row.agent_uuid + '&name=' + agentName + '&ip=' + row.agent_ip + '&os_type=' + row.os_type;
        LOCATION(url,CONF.VENDOR == CONF.VENDOR_LIST.gmp ? "clients" : "infrastructure");
    }

    //刷新客户端
    var refreshClient = function (e, value, row) {
        Metronic.blockUI({target: '.client-manager-table-container', animate: true});
        pAjaxRequest({}, `/api/v1/agents/${row.agent_uuid}/refresh`, 'POST', res => {
            Metronic.unblockUI('.client-manager-table-container');
            let title = LANG.UI_CLIENT_REFRESH_TITLE;
            let message = LANG.UI_CLIENT_REFRESH_SUCCESS;
            if (parseInt(row.agent_type) === 4) {
                title = LANG.UI_CLIENT_REFRESH_PROXY_TITLE;
                message = LANG.UI_CLIENT_REFRESH_PROXY_SUCCESS;
            }
            if (!res.success) {
                UIToastr.showWarning(title, res.message);
                return;
            }
            UIToastr.showSuccess(title, message);
            $('#clientDatatable').bootstrapTable('refresh');
        });
    }
    //删除客户端
    var deleteSubmit = function (rows, flag) {
        let reqData = {
            agent_uuids: rows.map(v => v.agent_uuid),
            uninstall_plugin_flag: flag,
        };
        Metronic.blockUI({target: '.client-manager-table-container', animate: true});
        pAjaxRequest(reqData, `/api/v1/agents`, 'DELETE', res => {
            Metronic.unblockUI('.client-manager-table-container');
            if (!res.success) {
                UIToastr.showWarning(LANG.UI_CLIENT_DELETE, res.message);
                return;
            }
            UIToastr.showSuccess(LANG.UI_CLIENT_DELETE, res.message);
            $('#clientDatatable').bootstrapTable('refresh');
        });
    }

    //添加事件
    var addListeners = function () {
        //初始化三个表单验证
        clientValidate(1, true);	//手动添加
        clientValidate(2, true);	//修改
        clientValidate(3, false);	//远程部署
        //切换页数保存到cookie
        $('select[name=datatable_length]').on('change', function () {
            pageLength.agent = this.value;
            var data = JSON.stringify(pageLength);
            $.cookie("pageLength", data);
        });

        //切换代理类型
        $('#agentType').on('change', function () {
            agentType = this.value;
            if (4 == agentType) {
                $('.appliedVcentersDiv').show();
                applianceFlag = true;
                $('#ipaddress1Label').text(LANG.UI_APPLIANCE_IP_DOMAIN);
                $('#clientConnectPortLabel').hide();
                $('#proxyConnectPortLabel').show();
            } else {
                $('.appliedVcentersDiv').hide();
                applianceFlag = false;
                $('#ipaddress1Label').text(LANG.UI_CLIENT_NETWORK_IP);
                $('#clientConnectPortLabel').show();
                $('#proxyConnectPortLabel').hide();
            }
        });

        $('#agentTypeRemote').on('change', function () {
            agentTypeRemote = this.value;
            if (4 == agentTypeRemote) {
                // 传输代理锁定为服务端连接客户端
                $('#connectType').val(1).prop('disabled', true);
                $('.clientlabel').show();
                $('.serverlabel').hide();
                $('#connectport').val(23100).prop('disabled', false);
                $('.portshowDiv').show();

                // 操作系统只有windows和redhat7选项
                $('#osType option').hide();
                $('#osType option[value="WINDOWS"]').show();
                $('#osType option[value="RHEL7"]').show();
            } else {
                $('#connectType').prop('disabled', false);
                $('#osType option').show();
            }
        });

        // 点击高级搜索
        $('#clientlist').on('click', '.agentAdvanced', initAdvanceSearchModal)
            .on('click', '.vin_toolbar .search button.search-btn', () => {  // 搜索
                $('#current_searchDiv .searchContent').text('');  // 搜索要清除之前的高级搜索
                $('#current_searchDiv').hide();
                // 动态设置 table-container 高度
                if ($('#client_manager_tip').length > 0) { // 提示信息存在 196px = table-toolbar高度34px + mb 12px +  alert 135px
                    $('#clientmanagerdiv .client-manager-table-container').css('height', 'calc(100% - 185px)');
                } else { // 提示信息关闭 46px = table-toolbar高度34px + mb 12px
                    $('#clientmanagerdiv .client-manager-table-container').css('height', 'calc(100% - 50px)');
                }

                for (const id of Object.keys(advancedSearch)) {
                    clearAdvancedSearch(id);
                }
                $('#clientDatatable').bootstrapTable('refresh');
            })
            .on('keydown', '.clientSearch', ev => {
                if (ev.keyCode === 13 && ev.key === 'Enter') {
                    $('#current_searchDiv .searchContent').text('');  // 搜索要清除之前的高级搜索
                    $('#current_searchDiv').hide();
                    // 动态设置 table-container 高度
                    if ($('#client_manager_tip').length > 0) { // 提示信息存在 196px = table-toolbar高度34px + mb 12px +  alert 135px
                        $('#clientmanagerdiv .client-manager-table-container').css('height', 'calc(100% - 185px)');
                    } else { // 提示信息关闭 46px = table-toolbar高度34px + mb 12px
                        $('#clientmanagerdiv .client-manager-table-container').css('height', 'calc(100% - 50px)');
                    }

                    for (const id of Object.keys(advancedSearch)) {
                        clearAdvancedSearch(id);
                    }
                    $('#clientDatatable').bootstrapTable('refresh');
                }
            })
            .on('click', '#deleteClient', deleteClientBox) // 删除客户端
            .on('click', '#addClient', () => {  // 添加客户端
                initModal("", true);
            })
            .on('click', '#editClient', () => {  // 修改客户端
                let select = $('#clientDatatable').bootstrapTable('getSelections');
                if (select.length !== 1) {
                    UIToastr.showInfo(LANG.UI_CLIENT_EDIT_CLIENT_PROXY, LANG.UI_CLIENT_EDIT_SELECT_TIPS);
                    return false;
                }
                checkOperateAuth(checkAuth(select, parseInt(select[0].agent_type) === 4 ? 2 : 10), () => {
                    initModal(select[0].agent_uuid, false);
                });
            })
            .on('click', '#upgradeClient', () => {  // 客户端升级
                let rows = $('#clientDatatable').bootstrapTable('getSelections');
                if (!rows.length) {
                    UIToastr.showInfo(LANG.UI_AGENT_UPGRADE, LANG.UI_AGENT_UPGRADE_TIPS1);
                    return false;
                }
                const agentRows = rows.filter(row => parseInt(row.agent_type) === 4);
                if (agentRows.length) {
                    UIToastr.showInfo(LANG.UI_AGENT_UPGRADE, LANG.UI_AGENT_UPGRADE_TIPS2);
                    return false;
                }
                checkOperateAuth(checkAuth(rows), () => {
                    //初始化升级模态框
                    initUpgradeModal(rows);
                });
            })
            .on('click', '#installDriver', () => {  // 驱动安装
                let rows = $('#clientDatatable').bootstrapTable('getSelections');
                if (!rows.length) {
                    UIToastr.showInfo(LANG.UI_AGENT_INSTALL_DRIVER, LANG.UI_AGENT_INSTALL_DRIVER_TIPS1);
                    return false;
                }
                for (const row of rows) {
                    if (parseInt(row.agent_type) !== AGENT_TYPE_ENUM.MEMORY_OS) {
                        UIToastr.showInfo(LANG.UI_AGENT_INSTALL_DRIVER, LANG.UI_AGENT_INSTALL_DRIVER_TIPS2);
                        return false;
                    }
                }
                checkOperateAuth(checkAuth(rows), () => {
                    //初始化安装驱动模态框
                    initInstallDriverModal(rows);
                });
            })
            .on('click', '#downloadClient', () => {  // 下载插件
                $('#downloadModal').modal({'width': "700px", 'height': "200px"});
            });

        //切换添加方式
        $('#addType').on('change', addTypeChange);

        //定义iCheck样式
        $('#addModal .icheckbox').iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue',
            increaseArea: '20%' // optional
        });

        //修改客户端确认
        $('#editsubmit').on('click', editClientSubmit);

        //升级确认
        $('#upgradesubmit').on('click', doUpgradeClient);
        // 安装驱动
        $('#installDriverSubmit').on('click', doInstallDriver);

        //获取添加上传模板
        $('#downloadTemp').on('click', function () {
            //获取模板函数
            initClientTemp();
        });

        //下载软件包
        $('#download').on('click', function () {
            var selectedText = $('#downloadModal .filesystem_select option:selected').text();
            if (!selectedText) { // 检查 selectedText 是否为假值（包括空字符串）
                selectedText = 'Windows';
            }
            var type = 2;
            var version = $('.downloadFilesystemType').find('option:selected').text();
            //获取下载链接
            var data = {selectedText: selectedText, type: type, version:version};
            pAjaxRequest(data,'/api/v1/agents/download/plugins','GET',(d) => {
                var res = d.data;
                if(res.linkStr == '#'){
                    UIToastr.showInfo(LANG.UI_CLIENT_DOWNLOAD_CLIENT,LANG.UI_CLIENT_DOWNLOAD_CLIENT_TIP);
                    return;
                }
                var data = {};
                data.filepath = res.path+res.linkStr;  // 绝对路径
                data.filename = res.showName;
                pAjaxRequest(data,'/api/v1/system/generate/download','GET',function (res){
                    downloadPackage(res.data.url);
                });
            });
        });

        // 高级搜索
        $('#advanceSearchSubmit').on('click', function () {
            advancedSearch = {
                start_time: _dateRangePicker_startTime,
                end_time: _dateRangePicker_endTime,
                ip: $('#advanceSearchIp').val().trim(),
                hostname: $('#advanceSearchHostname').val().trim(),
                alias: $('#advanceSearchAlias').val().trim(),
                app_name: $('#advanceSearchAppName').val().trim(),
                owner: $('#advanceSearchOwner').val().trim(),
                os_version: $('#advanceSearchOsVersion').val().trim(),
                status: $('#advanceSearchStatus').val(),
            };
            addSearchContent();  // 显示搜索项
            $('#clientDatatable').bootstrapTable('refresh');
            $('#advanceSearchModal').modal('hide');
        });

        // 清除高级搜索内容
        $('#current_searchDiv .clearSearch').on('click', function () {
            $('#current_searchDiv .searchContent').text('');
            $('#current_searchDiv').hide();
            // 动态设置 table-container 高度
            if ($('#client_manager_tip').length > 0) { // 提示信息存在 196px = table-toolbar高度34px + mb 12px +  alert 135px
                $('#clientmanagerdiv .client-manager-table-container').css('height', 'calc(100% - 185px)');
            } else { // 提示信息关闭 46px = table-toolbar高度34px + mb 12px
                $('#clientmanagerdiv .client-manager-table-container').css('height', 'calc(100% - 50px)');
            }

            for (const id of Object.keys(advancedSearch)) {
                clearAdvancedSearch(id);
            }
            $('#clientDatatable').bootstrapTable('refresh');
        });

        //批量添加上传Excel
        $('input[name=files]').fileupload({
            url: `/api/v1/agents/file`,
            dataType: 'json',
            acceptFileTypes: 'xls',
            method: 'POST',
            headers: {
                'x-api-version': '1.0-rev0',
            },
            done: function (e, data) {
                if (!data.result.success || !data.result.data.rows.length) {
                    UIToastr.showWarning(LANG.UI_CLIENT_BATCH_ADD, LANG.UI_CLIENT_UPDATE_LIST_EMPTY_TIPS);
                    return false;
                } else {
                    clientList = data.result.data.rows;
                    let reqData = {
                        add_mode: 2,
                        manual: {},
                        deployment: {
                            add_type: 2,
                            server_port: 22710,
                            server_ip: $('#addServerIp').val(),
                            single: {},
                            multiple: clientList.map(clientInfo => {
                                let port = clientInfo.port;
                                if (parseInt(clientInfo.net_model) !== 1) {
                                    port = 23100;
                                }
                                return {
                                    os_type: clientInfo.os_type,
                                    ip: clientInfo.ip,
                                    alias: clientInfo.nickname,
                                    username: clientInfo.username,
                                    password: btoa(clientInfo.password),
                                    net_model: clientInfo.net_model,
                                    port,
                                    client_transport_port: clientInfo.client_transport_port,
                                    agent_type: 1,
                                    auto_change_network_flag: !!clientInfo.auto_change_network_flag,
                                };
                            }),
                        }
                    };
                    Metronic.blockUI({target: '#addModal', animate: true});
                    pAjaxRequest(reqData, `/api/v1/agents`, 'POST', res => {
                        Metronic.unblockUI('#addModal');
                        if (!res.success) {
                            UIToastr.showWarning(LANG.UI_CLIENT_BATCH_ADD, res.message);
                            return;
                        }
                        UIToastr.showSuccess(LANG.UI_CLIENT_BATCH_ADD, res.message);
                        $('#addModal').modal('hide');
                        $('#clientDatatable').bootstrapTable('refresh');
                    });
                }
            },
            start: function (e) {

            },
            stop: function (e) {

            },
            fail: function (e, data) {
                UIToastr.showWarning(LANG.UI_CLIENT_BATCH_ADD, LANG.UI_CLIENT_BATCH_ADD_UPLOAD_ERROR);
                return false;
            },
        }).prop('disabled', !$.support.fileInput)
            .parent().addClass($.support.fileInput ? undefined : 'disabled');

        $('#ipaddress').on('change', function () {
            $('#nickname').val(this.value);
        });

        $('#ipaddress1').on('change', function () {
            $('#nickname1').val(this.value);
        });

        //切换网络模式
        $('#connectType').on('change', connectChange);

        //选择操作系统
        $('.ossystem_select').on('change', function () {
            var value = this.value;
            var deploymentText = $('#deploymentdiv .ossystem_select').find("option:selected").text();
            var downloadText = $('#downloadModal .ossystem_select').find("option:selected").text();
            if (deploymentText == "Windows") {
                $('#deploymentdiv .fsagent').hide();
                $('#adminname').val('Administrator');
            } else {
                $('#deploymentdiv .fsagent').show();
                $('#adminname').val('root');
            }
            if (downloadText == "Windows") {
                $('#downloadModal .fsagent').hide();
            } else {
                $('#downloadModal .fsagent').show();
            }
            var filesystem = $('.filesystem_select');
            filesystem.empty();
            var eachVersion = _OS[value].version;
            for (var i = 0; i < eachVersion.length; i++) {
                var option = $("<option>").text(eachVersion[i].text).val('#');
                filesystem.append(option);
            }
        });

        //提交传输代理配置
        $('#agentConfigSubmit').on('click', submitAgentConfig);

        //初始化多选下拉框
        $(".selectpicker").selectpicker({
            noneSelectedText: LANG.BILLING_PLEASE_SELECT,
            deselectAllText: LANG.BILLING_DESELECT_ALL,
            selectAllText: LANG.BILLING_SELECT_ALL,
            liveSearchPlaceholder: LANG.BILLING_SEARCH,
            countSelectedText: function(){}
        });

        //传输代理分配虚拟化
        $('#applied_vcenters').next('.bootstrap-select').find('.dropdown-menu .inner').css({height: '200px', width: 'auto'});
        $('#applied_vcenters_edit').next('.bootstrap-select').find('.dropdown-menu .inner').css({height: '200px', width: 'auto'});
        $('#applied_vcenters_config').next('.bootstrap-select').find('.dropdown-menu .inner').css({height: '200px', width: 'auto'});

        // 跳转到驱动库管理
        $('#toDriverManager').on('click', jumpToDriverManager);

        // client_manager_tip_close 关闭
        $('#client_manager_tip_close').on('click', () => {
            $('.resource-manager-wrap .resource-manager-wrap__content').css('padding-bottom', 0);
            // 动态设置 table-container 高度
            if ($('#current_searchDiv').is(':visible')) { // 提示信息存在 86px = table-toolbar高度34px + mb 12px + search-content 40px
                $('#clientmanagerdiv .client-manager-table-container').css('height', 'calc(100% - 94px)');
            } else { // 提示信息关闭 46px = table-toolbar高度34px + mb 12px
                $('#clientmanagerdiv .client-manager-table-container').css('height', 'calc(100% - 50px)');
            }
        });
    }

    /**
     * 操作权限校验
     * @param rows
     * @param sourceType 资源类型【2代理 10客户端】
     * @returns {{type: number, source_uuid, source_type: number}|boolean}
     */
    const checkAuth = function(rows, sourceType = 10) {
        if (!rows.length) {
            return false;
        }

        return {
            type: 2,
            source_uuid: rows.map(row => row.agent_uuid).join(','),
            source_type: sourceType,
        };
    };

    /**
     * 跳转到驱动库管理
     */
    const jumpToDriverManager = () => {
        $('#installDriverModal').modal('hide');
        LOCATION('./content/driver/driver_manager.php', 'backup_manager');
    };

    /**
     * 删除按钮提示狂
     */
    const deleteClientBox = () => {
        let rows = $('#clientDatatable').bootstrapTable('getSelections');
        if (!rows.length) {
            UIToastr.showInfo(LANG.UI_CLIENT_DELETE, LANG.UI_CLIENT_DELETE_SELECT_TIPS);
            return false;
        }
        const clientRows = rows.filter(row => parseInt(row.agent_type) !== 4);
        const agentRows = rows.filter(row => parseInt(row.agent_type) === 4);
        checkDeleteClientBoxAuth(clientRows, agentRows, () => {
            doDeleteClientBox(rows);
        });
    };

    /**
     * 检查删除客户端权限
     * @param clientRows
     * @param agentRows
     * @param callback
     */
    const checkDeleteClientBoxAuth = (clientRows, agentRows, callback) => {
        if (clientRows.length && agentRows.length) {
            checkOperateAuth(checkAuth(clientRows), () => {
                checkOperateAuth(checkAuth(agentRows, 2), () => {
                    callback();
                });
            });
        } else if (clientRows.length) {
            checkOperateAuth(checkAuth(clientRows), callback);
        } else {
            checkOperateAuth(checkAuth(agentRows, 2), callback);
        }
    };

    /**
     * 执行删除客户端
     * @param rows
     */
    const doDeleteClientBox = (rows) => {
        let message = '';

        // 有任务占用则提示无法删除
        let taskCount = 0;
        let taskName = '';
        for (const row of rows) {
            taskCount += parseInt(row.task_count);
            taskName += row.all_task_name ? row.all_task_name + '<br>' : '';
        }
        if (taskCount > 0) {
            message = `
                <div class="modal-delete">
                    <div class="alert alert-warning">
                        <button type="button" class="close" data-dismiss="alert"></button>
                        <ul class="alert-ul">
                            <strong class="alert-ul-head">${LANG.UI_PUBLIC_TIPS}:</strong>
                            <li>
                                <i class="fa fa-info-circle "></i> ${LANG.UI_APPLIANCE_DELETE_TASK_OCCUPIED_TIP}
                            </li>
                        </ul>
                    </div>
                    <div class="modal-delete__content">
                        <div class="row static-info">
                            <div class="col-md-4 name textalignr">${LANG.UI_PUBLIC_TASK_COUNT}:</div>
                            <div class="col-md-8 value" id="taskcount">${taskCount}</div>
                        </div>
                        <div class="row static-info">
                            <div class="col-md-4 name textalignr">${LANG.UI_PUBLIC_TASK_NAME}:</div>
                            <div class="col-md-8 value" id="taskname" style="max-height:150px;overflow-y:scroll;">${taskName}</div>
                        </div>
                    </div>
                </div>
            `;

            bootbox.dialog({
                title: LANG.UI_CLIENT_DELETE,
                message: message,
                buttons: {
                    cancel: {
                        label: LANG.UI_GLOBAL_STRATEGY_DISPENSE_CANCEL,
                        className: 'btn-default',
                        callback: function () {
                        }
                    },
                }
            });
            return;
        }

        // NOTE: 删除客户端添加客户端主机和ip的显示提示
        message =
            '<div class="modal-delete">' +
            '<div class="modal-delete__icon">' +
            '<i class="viconfont vicon-a-Close-oneguanbi"></i>' +
            '</div>' +
            '<div class="modal-delete__info">' +
            `${LANG.UI_CLIENT_DELETE_CONFIRM_TIPS}` +
            '</div>' +
            '<div class="modal-delete__content">' +
            '<div class="modal-delete__content__title">' + `${LANG.UI_CLIENT_HOSTNAME_IP}` + '</div>';

        let showDeleteUninstallFlag = false;
        for (const row of rows) {
            message += `<div class="modal-delete__content__item">${row.hostname}/${row.agent_ip}</div> `;
            if (parseInt(row.agent_type) === 1) {
                showDeleteUninstallFlag = true;
            }
        }

        let buttons = {
            cancel: {
                label: LANG.UI_GLOBAL_STRATEGY_DISPENSE_CANCEL,
                className: 'btn-default',
                callback: function () {
                }
            },
            noclose: {
                label: LANG.UI_CLIENT_DELETE_NEW,
                className: 'btn-primary',
                callback: debounce(function () {
                    deleteSubmit(rows, 2);
                }, 300)
            },
        };
        if (showDeleteUninstallFlag) {
            buttons.ok = {
                label:LANG.UI_CLIENT_DELETE_UNINSTALL,
                className: 'btn-danger',
                callback: debounce(function () {
                    deleteSubmit(rows, 1);
                }, 300),
            };
        }

        message += '</div></div>';
        bootbox.dialog({
            title: LANG.UI_CLIENT_DELETE,
            message: message,
            buttons,
        });
    };

    const clearAdvancedSearch = (id) => {
        if (typeof advancedSearch[id] !== 'undefined') {
            advancedSearch[id] = undefined;
        }
        switch (id) {
            case 'start_time':
            case 'end_time':
            case 'time':
                advancedSearch.start_time = undefined;
                advancedSearch.end_time = undefined;
                $('#advanceSearchDateRangePicker').val('');
                _dateRangePicker_startTime = '';
                _dateRangePicker_endTime = '';
                break;
            case 'ip':
                $('#advanceSearchIp').val('');
                break;
            case 'hostname':
                $('#advanceSearchHostname').val('');
                break;
            case 'alias':
                $('#advanceSearchAlias').val('');
                break;
            case 'os_version':
                $('#advanceSearchOsVersion').val('');
                break;
            case 'status':
                $('#advanceSearchStatus option').removeAttr('selected');
                break;
            case 'app_name':
                $('#advanceSearchAppName').val('');
                break;
            case 'owner':
                $('#advanceSearchOwner').val('');
                break;
            default:
                advancedSearch[id] = '';
        }
    };

    var addSearchContent = function () {
        var contents = [];
        $('#current_searchDiv .searchContent').text('');
        if (advancedSearch.start_time && advancedSearch.end_time) {  // 添加时间
            contents.push('<span id="time" title="' + advancedSearch.start_time + "~" + advancedSearch.end_time + '"> '
                + LANG.UI_PUBLIC_ADD_TIME + ': <i>' + advancedSearch.start_time + "~" + advancedSearch.end_time + '</i><em>X</em></span>');
        }

        if (advancedSearch.ip) {  // IP地址
            contents.push(`<span id="ip" title="${advancedSearch.ip}">${LANG.UI_CLIENT_IP_ADDRESS}: `
                + `<i>${advancedSearch.ip}</i><em>X</em></span>`);
        }

        if (advancedSearch.hostname) {  // 主机名
            contents.push(`<span id="hostname" title="${advancedSearch.hostname}">${LANG.UI_CLIENT_HOST_NAME}: `
                + `<i>${advancedSearch.hostname}</i><em>X</em></span>`);
        }

        if (advancedSearch.alias) {  // 别名
            contents.push(`<span id="alias" title="${advancedSearch.alias}">${LANG.UI_CLIENT_ALIAS}: `
                + `<i>${advancedSearch.alias}</i><em>X</em></span>`);
        }

        if (advancedSearch.os_version) {  // 操作系统
            contents.push(`<span id="os_version" title="${advancedSearch.os_version}">${LANG.UI_CLIENT_OS}: `
                + `<i>${advancedSearch.os_version}</i><em>X</em></span>`);
        }

        if (advancedSearch.app_name) {  // 应用配置
            contents.push(`<span id="app_name" title="${advancedSearch.app_name}">${LANG.UI_CLIENT_APP_NAME}: `
                + `<i>${advancedSearch.app_name}</i><em>X</em></span>`);
        }

        if (advancedSearch.status) {  // 状态
            var onlineStatus = $('#advanceSearchStatus').find("option:selected").text();
            contents.push(`<span id="status" title="${onlineStatus}">${LANG.UI_PUBLIC_STATUS}: `
                + `<i>${onlineStatus}</i><em>X</em></span>`);
        }

        if (advancedSearch.owner) {  // 所有者
            contents.push(`<span id="owner" title="${advancedSearch.owner}">${LANG.UI_CLIENT_OWNER}: `
                + `<i>${advancedSearch.owner}</i><em>X</em></span>`);
        }
        $('#current_searchDiv .searchContent').append(contents.join(''));
        $('#current_searchDiv .searchContent em').on('click', function () {
            // 这里的监听只能放在这里, 因为em元素是动态生成的
            // 点击x
            $(this).parent().remove();
            var searchContent = $('#current_searchDiv .searchContent');
            if (!searchContent[0].children.length) {
                $('#current_searchDiv').hide();
                // 动态设置 table-container 高度
                if ($('#client_manager_tip').length > 0) { // 提示信息存在 196px = table-toolbar高度34px + mb 12px +  alert 135px
                    $('#clientmanagerdiv .client-manager-table-container').css('height', 'calc(100% - 185px)');
                } else { // 提示信息关闭 46px = table-toolbar高度34px + mb 12px
                    $('#clientmanagerdiv .client-manager-table-container').css('height', 'calc(100% - 50px)');
                }
            }
            var parent = $(this).parent();
            var id = parent[0].id;
            clearAdvancedSearch(id);
            $('#clientDatatable').bootstrapTable('refresh');
        });
        if (contents.length) {
            $('#current_searchDiv').show();
            // 动态设置 table-container 高度
            if ($('#client_manager_tip').length > 0) { // 提示信息存在 236px = table-toolbar高度34px + mb 12px + search-content 40px +  alert 135px
                $('#clientmanagerdiv .client-manager-table-container').css('height', 'calc(100% - 229px)');
            } else { // 提示信息关闭 86px = table-toolbar高度34px + mb 12px + search-content 40px
                $('#clientmanagerdiv .client-manager-table-container').css('height', 'calc(100% - 94px)');
            }
        }
    }

    //切换网络模式
    var connectChange = function () {
        if (parseInt(this.value) === 1) {
            //服务端连客户端
            $('.clientlabel').show();
            $('.serverlabel').hide();
            $('#connectport').val(23100).prop('disabled', false);
            $('.portshowDiv').show();
            $('.addServerIpDiv').hide();
            $('.autoChangeNetworkFlagDiv').show();
        } else {
            //客户端连服务端
            $('.clientlabel').hide();
            $('.serverlabel').show();
            $('#connectport').val(22710).prop('disabled', true);
            $('.portshowDiv').hide();
            $('.addServerIpDiv').show();
            $('.autoChangeNetworkFlagDiv').hide();
        }
    }

    //初始化升级模态框
    var initUpgradeModal = function (rows) {
        initUpgradeClientTable(rows);
        $('#upgradeModal').modal({'width': "700px", 'height': "450px"});
    }

    // 初始化安装驱动模态框
    const initInstallDriverModal = rows => {
        initInstallDriverTable(rows);
        $('#installDriverModal').modal({'width': "700px", 'height': "450px"});
    };

    var initAdvanceSearchModal = function () {
        $('#advanceSearchModal').modal({'width': "851px", 'height': "450px"});
    }

    //切换添加方式
    var addTypeChange = function () {
        var type = parseInt(this.value);
        if (type === 1) {
            $('.simpleDiv').show();
            $('.batchDiv').hide();
            $('.batchTableDiv').hide(); //隐藏批量添加表格
            $('.configDiv').show();
            var deploymentText = $('#deploymentdiv .ossystem_select').find("option:selected").text();
            if (deploymentText === "Windows") {
                $('#deploymentdiv .fsagent').hide();
            } else {
                $('#deploymentdiv .fsagent').show();
            }
            if (1 === parseInt($('#connectType').val())) {
                $('.addServerIpDiv').hide();
                $('.autoChangeNetworkFlagDiv').show();
            } else {
                $('.addServerIpDiv').show();
                $('.autoChangeNetworkFlagDiv').hide();
            }
        } else {
            $('.simpleDiv').hide();
            $('.batchDiv').show();
            $('.configDiv').hide();
            $('#deploymentdiv .fsagent').hide();
            $('.addServerIpDiv').show();
            $('.autoChangeNetworkFlagDiv').hide();
        }
    }

    //初始化添加/修改模态框
    var initModal = function (uuid, addFlag) {
        $('.simpleDiv').show();	//显示单个添加表单
        $('.batchDiv').hide();	//显示批量添加控件
        //添加
        if (addFlag) {
            //初始化显示手动添加
            $('.addli').removeClass('active').addClass('active');
            $('#adddiv').removeClass('active').addClass('active');
            $('.deploymentli').removeClass('active');
            $('#deploymentdiv').removeClass('active');
            //默认显示单个添加
            $('#osType').val("WINDOWS");
            $('#osType').unbind('change').on('change', function () {
                if (this.value === 'WINDOWS') {
                    $('#adminname').val('Administrator');
                } else {
                    $('#adminname').val('root');
                }
            });
            $('#addType').val(1);
            $('#ipaddress').val("");
            $('#ipaddress1').val("");
            $('#nickname').val("");
            $('#nickname1').val("");
            $('#adminname').val("Administrator");
            $('#password').val("");
            //默认服务端连接客户端
            $('#connectType').val(2);
            $('#connectport').val(22710).prop('disabled', true);
            $('.portshowDiv').hide();
            $('#connectport1').val(23100);
            $('#clientport').val(23101);
            $('.addTypeDiv').show();
            $('#agentType').trigger('change');
            $('.batchTableDiv').hide();
            $('.serverlabel').show();
            $('.clientlabel').hide();
            $('.configDiv').show();
            $('.autoChangeNetworkFlagDiv').hide();  // 自动切换可用网络仅在远程部署的服务端连接代理显示
            if (CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw") {
                $('#addModal').modal({'width': "700px", 'height': "450px"});
            }else{
                $('#addModal').modal({'width': "840px", 'height': "450px"});
            }

            $('#autoChangeNetworkFlag').bootstrapSwitch('state', false);
            $('#manualAutoChangeNetworkFlag').bootstrapSwitch('state', false);
            // 获取操作系统
            initDownloadClient();
            // 隐藏window操作系统的架构显示
            $('#deploymentdiv .fsagent').hide();
            initServerIp();
        } else {
            //初始化修改客户端信息,走修改模态框显示
            initClientOldInfo(uuid);
        }
    }

    /**
     * 初始化备份服务器地址
     */
    const initServerIp = () => {
        Metronic.blockUI({target: '.addServerIpDiv', animate: true});
        pAjaxRequest({}, `/api/v1/nodes/master_network`, 'GET', res => {
            Metronic.unblockUI('.addServerIpDiv');
            if (!res.success) {
                UIToastr.showWarning(LANG.UI_CLIENT_ADD, res.message);
                return;
            }
            let options = ``;
            for (const row of res.data.rows) {
                options += `<option value="${row.network_ip}">${row.network_ip}</option>`;
            }
            $('#addServerIp').html(options);
        });
    };

    //初始化修改客户端信息
    var initClientOldInfo = function (uuid) {
        let row = $('#clientDatatable').bootstrapTable('getSelections')[0];
        _net_model = parseInt(row.net_model);	//通信模式
        $('#edit_client_uuid').val(uuid);
        $('#ipaddress2').val(row.agent_ip);
        $('#nickname2').val(row.alias);
        $('#connectport2').val(row.agent_port);
        if (_net_model === 1) {
            //服务端连客户端
            $('.clientlabel').show();
            $('.serverlabel').hide();
            $('.clientportDiv').show();
            $('#ipaddress2').prop('disabled', false);
            $('#connectport2').prop('disabled', false);
            $('.editAutoChangeNetworkFlagDiv').show();
            $('#editAutoChangeNetworkFlag').bootstrapSwitch('state', !!row.auto_change_network_flag);
        } else {
            //客户端连服务端
            $('.clientlabel').hide();
            $('.serverlabel').show();
            $('.clientportDiv').hide();
            $('#ipaddress2').prop('disabled', true);
            $('#connectport2').prop('disabled', true);
            $('.editAutoChangeNetworkFlagDiv').hide();
            $('#editAutoChangeNetworkFlag').bootstrapSwitch('state', false);
        }

        if (4 === row.agent_type) {
            $('#ipaddress2').prop('disabled', true);
            initOldVcenters(row.applied_vcenters, 'applied_vcenters_edit');
            $('.editAppliedVcentersDiv').show();
            applianceFlag = true;
            $('#ipaddress2Label').text(LANG.UI_APPLIANCE_IP_DOMAIN);
            $('#clientEditConnectPortTips').hide();
            $('#proxyEditConnectPortTips').show();
            $('#editClientTitle').hide();
            $('#editProxyTitle').show();
            $('.clientlabel').hide();
            $('.serverlabel').hide();
            $('.clientlabelproxy').show();
        } else {
            $('.editAppliedVcentersDiv').hide();
            applianceFlag = false;
            $('#ipaddress2Label').text(LANG.UI_CLIENT_NETWORK_IP);
            $('#clientEditConnectPortTips').show();
            $('#proxyEditConnectPortTips').hide();
            $('#editClientTitle').show();
            $('#editProxyTitle').hide();
            $('.clientlabelproxy').hide();
        }

        $('#editModal').modal({'width': "700px", 'height': "250px"});
    }


    //添加客户端确认 flag 1 手动添加 2远程部署
    var addClientSubmit = function (flag) {
        let reqData = {
            add_mode: 1,
            manual: {},
            deployment: {
                add_type: 1,
                server_port: 22710,
                single: {},
                multiple: [],
            },
        };
        if (1 === flag) {  // 手动添加
            reqData.manual.ip = $('#ipaddress1').val();
            reqData.manual.alias = $('#nickname1').val();
            reqData.manual.port = $('#connectport1').val();
            reqData.manual.agent_type = parseInt(agentType);
            reqData.manual.auto_change_network_flag = !!$('#manualAutoChangeNetworkFlag').get(0).checked;
            // 传输代理应用到虚拟化
            if (4 === reqData.manual.agent_type) {
                reqData.manual.applied_vcenters = $('#applied_vcenters').selectpicker('val');
            }
        } else {  // 远程部署
            reqData.add_mode = 2;
            let deploymentText = $('#deploymentdiv .ossystem_select').find('option:selected').text();
            if (deploymentText === 'Windows') {
                reqData.deployment.single.os_type = deploymentText;
            } else {
                reqData.deployment.single.os_type = $('#deploymentdiv .filesystem_select').find("option:selected").text();
            }
            reqData.deployment.single.ip = $('#ipaddress').val();
            reqData.deployment.single.alias = $('#nickname').val();
            reqData.deployment.single.username = $('#adminname').val();
            reqData.deployment.single.password = btoa($('#password').val());
            reqData.deployment.single.net_model = parseInt($('#connectType').val());
            if (1 === reqData.deployment.single.net_model) {
                reqData.deployment.single.port = parseInt($('#connectport').val());
                reqData.deployment.single.client_transport_port = 0;
                if (4 === parseInt(agentTypeRemote)) {
                    reqData.deployment.single.client_transport_port = 23101;
                }
                reqData.deployment.single.auto_change_network_flag = !!$('#autoChangeNetworkFlag').get(0).checked;
            } else {
                reqData.deployment.single.port = 0;
                reqData.deployment.single.client_transport_port = parseInt($('#connectport').val());
                reqData.deployment.single.auto_change_network_flag = false;
            }
            reqData.deployment.server_ip = $('#addServerIp').val();
            reqData.deployment.single.agent_type = parseInt(agentTypeRemote);
        }
        Metronic.blockUI({target: '#addModal', animate: true});
        pAjaxRequest(reqData, `/api/v1/agents`, 'POST', res => {
            Metronic.unblockUI('#addModal');
            let title = LANG.UI_CLIENT_ADD;
            if (4 === parseInt($('#agentType').val())) {
                title = LANG.UI_CLIENT_ADD_PROXY;
            }
            if (!res.success) {
                UIToastr.showWarning(title, res.message);
                return;
            }
            UIToastr.showSuccess(title, res.message);
            $('#addModal').modal('hide');
            $('#clientDatatable').bootstrapTable('refresh');
        });
    }

    //批量添加客户端
    var addBatchClient = function () {
        if (clientList.length == 0) {
            //未上传excel文件，返回错误
            UIToastr.showInfo(LANG.UI_CLIENT_BATCH_ADD, LANG.UI_CLIENT_BATCH_ADD_UPLOAD_ERROR);
            return false;
        }
        $('#addModal').modal('hide');
    }

    //修改客户端确认
    var editClientSubmit = function () {
        let nickname = $('#nickname2').val();
        if (!reXssEncode(nickname)) {
            $('#nickname2').val('');
            return false;
        }
        if (!editform.validate().form()) {
            return false;
        }
        let reqData = {
            ip: $('#ipaddress2').val(),
            nickname: nickname,
            port: $('#connectport2').val(),
            net_model: _net_model,
            auto_change_network_flag: !!$('#editAutoChangeNetworkFlag').get(0).checked,
            appliance_flag: applianceFlag,
        };
        // 传输代理应用到虚拟化
        reqData.applied_vcenters = $('#applied_vcenters_edit').selectpicker('val');

        Metronic.blockUI({target: '#editModal', animate: true});
        pAjaxRequest(reqData, `/api/v1/agents/${$('#edit_client_uuid').val()}`, 'PATCH', res => {
            Metronic.unblockUI('#editModal');
            let title = LANG.UI_CLIENT_EDIT;
            if (applianceFlag) {
                title = LANG.UI_CLIENT_EDIT_PROXY;
            }
            if (!res.success) {
                UIToastr.showWarning(title, res.message);
                return;
            }
            UIToastr.showSuccess(title, res.message);
            $('#editModal').modal('hide');
            $('#clientDatatable').bootstrapTable('refresh');
        });
    }

    //添加/修改客户端数据格式校验
    var clientValidate = function (id, flag) {
        var rules = {
            ipaddress: {
                required: true,
                ipv4: true
            },
            adminname: {
                required: true,
            },
            password: {
                required: true,
            },
            nickname: {
                required: false,
            },
            connectport: {
                required: true,
                port: true
            },
            clientport: {
                required: true,
                port: true
            }

        };
        //手动添加减少部分参数
        if (flag) {
            rules = {
                ipaddress: {
                    required: true,
                    ipv4: true
                },
                connectport: {
                    required: true,
                    port: true
                },

            };
        }
        var info = {
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "",  // validate all fields including form hidden input
            rules: rules,

            invalidHandler: function (event, validator) { //display error alert on form submit
            },

            errorPlacement: function (error, element) { // render error placement for each input type
                var icon = $(element).parent('.input-icon').children('i');
                icon.removeClass('fa-check').addClass("fa-warning");
                icon.attr("data-original-title", error.text()).tooltip({'container': 'body'});
            },

            highlight: function (element) { // hightlight error inputs
                $(element)
                    .closest('.form-group').removeClass("has-success").addClass('has-error'); // set error class to the control group
            },

            unhighlight: function (element) { // revert the change done by hightlight

            },

            success: function (label, element) {
                var icon = $(element).parent('.input-icon').children('i');
                $(element).closest('.form-group').removeClass('has-error').addClass('has-success'); // set success class to the control group
                icon.removeClass("fa-warning").addClass("fa-check");
            },

            submitHandler: function (form) {

            }

        };
        switch (id) {
            case 1:
                addform.validate(info);
                break;
            case 2:
                editform.validate(info);
                break;
            case 3:
                clientform.validate(info);
                break;
        }

        //IP验证格式
        $.validator.addMethod("ipv4", function (value, element) {
            if (applianceFlag) {
                // 传输代理验证ip和域名
                $.validator.messages.ipv4 = LANG.UI_TOOLS_IP_OR_DOMAIN;
                return this.optional(element) || ipV4V6(value)
                    || /^(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test( value );
            }
            $.validator.messages.ipv4 = LANG.UI_SETTING_INPUT_IP;
            return this.optional(element) || ipV4V6(value);
        });

        //端口验证格式
        $.validator.addMethod("port", function (value, element) {
//			if(!$(element).closest('.form-group').hasClass('has-success'))return false;
            if (value >= 0 && value <= 65535) {
                return true;
            }
            return false;
        }, LANG.UI_NODE_PORT_TIPS);

        if (!intFlag) {
            //添加客户端确认
            $('#addsubmit').on('click', function () {
                var addType = $('#addType').val();
                var deployFlag = $('#deploymentdiv').hasClass('active');
                if (deployFlag) {
                    //远程部署
                    if (addType == 1) {
                        //单个添加
                        if (clientform.validate().form()) {
                            addClientSubmit(2);
                        }
                    } else {
                        //批量添加
                        addBatchClient();
                    }
                } else {
                    //手动
                    if (addform.validate().form()) {
                        addClientSubmit(1);
                    }
                }
            });

            intFlag = true;
        }

    }

    //下载批量添加客户端模板
    var initClientTemp = function () {
        //执行获取模板url
        if (CONF.LANGUAGE === "zh-cn") {
            window.location = '/download/Client_Template_CN.xls';
        } else {
            window.location = '/download/Client_Template_EN.xls';
        }

    }

    //////////////////// 开始-客户端升级 ////////////////////

    /**
     * 执行升级代理
     */
    const doUpgradeClient = () => {
        let rows = $('#upgradeClientTable').bootstrapTable('getSelections');
        if (!rows.length) {
            UIToastr.showWarning(LANG.UI_AGENT_UPGRADE, LANG.UI_AGENT_UPGRADE_TIPS1);
            return;
        }
        let reqData = {
            upgrade_data: [],
        };
        for (const row of rows) {
            if (!!row.upgrade_flag) {
                reqData.upgrade_data.push({
                    agent_uuid: row.agent_uuid,
                    agent_path: row.package_path,
                    agent_version: row.agent_newest_version,
                });
            }
        }
        Metronic.blockUI({target: '#upgradeModal', animate: true});
        pAjaxRequest(reqData, `/api/v1/agents/upgrade`, 'POST', res => {
            Metronic.unblockUI('#upgradeModal');
            if (!res.success) {
                UIToastr.showWarning(LANG.UI_CLIENT_UPGRADE, res.message);
                return;
            }
            UIToastr.showSuccess(LANG.UI_CLIENT_UPGRADE, res.message);
            $('#upgradeModal').modal('hide');
            $('#clientDatatable').bootstrapTable('refresh');
        });
    };

    /**
     * 执行安装驱动
     */
    const doInstallDriver = () => {
        let rows = $('#installDriverTable').bootstrapTable('getSelections');
        if (!rows.length) {
            UIToastr.showWarning(LANG.UI_AGENT_INSTALL_DRIVER, LANG.UI_AGENT_INSTALL_DRIVER_TIPS1);
            return;
        }
        let reqData = {
            agent_uuid_list: rows.map(row => row.agent_uuid),
        };
        Metronic.blockUI({target: '#installDriverModal', animate: true});
        pAjaxRequest(reqData, `/api/v1/drivers/agent/install`, 'POST', res => {
            Metronic.unblockUI('#installDriverModal');
            if (!res.success) {
                UIToastr.showWarning(LANG.UI_AGENT_INSTALL_DRIVER, res.message);
                return;
            }
            UIToastr.showSuccess(LANG.UI_AGENT_INSTALL_DRIVER, res.message);
            $('#installDriverModal').modal('hide');
            $('#clientDatatable').bootstrapTable('refresh');
        });
    };

    /**
     * 获取升级客户端表格的列定义
     * @returns {Object}
     */
    const getUpgradeClientTableColumns = () => {
        return [{
            checkbox: true,
            width: '2',
            widthUnit: '%',
            sortable: false,
            formatter: (value, row) => {
                if (!row.online_flag || !row.upgrade_flag) {
                    return {disabled: true, checked: false};
                }
                return value;
            },
        }, {
            title: LANG.UI_CLIENT_IP_ADDRESS,
            field: 'agent_ip',
            width: '10',
            widthUnit: '%',
        }, {
            title: LANG.UI_CLIENT_OS,
            field: 'os_version',
            width: '10',
            widthUnit: '%',
        }, {
            title: LANG.UI_CLIENT_CURRENT_VERSION,
            field: 'agent_version',
            width: '10',
            widthUnit: '%',
        }, {
            title: LANG.UI_CLIENT_NEWEST_VERSION,
            field: 'agent_newest_version',
            width: '10',
            widthUnit: '%',
            formatter: (value, row) => {
                if (!value) {
                    return '--';
                }
                if (!row.upgrade_flag) {
                    return value;
                }
                return `<a href="${row.download_url}" target="_blank">${value}</a>`;
            },
        }, {
            title: LANG.UI_CLIENT_UPGRADE_FLAG,
            field: 'upgrade_flag',
            width: '7',
            widthUnit: '%',
            formatter: value => {
                let labelClass = !!value ? 'label-success' : 'label-default';
                let text = !!value ? LANG.UI_PUBLIC_YES : LANG.UI_PUBLIC_NO;
                return `<span class="label label-sm ${labelClass}" title="${text}">
		        	${text}
		        </span>`;
            },
        }, {
            title: LANG.UI_PUBLIC_STATUS,
            field: 'online_flag',
            width: '5',
            widthUnit: '%',
            formatter: value => {
                let labelClass = !!value ? 'label-success' : 'label-default';
                let text = !!value ? LANG.UI_VISUAL_ONLINE : LANG.UI_VISUAL_OFF_LINE;
                return `<span class="label label-sm ${labelClass}" title="${text}">
		        	${text}
		        </span>`;
            },
        }];
    };

    /**
     * 获取升级客户端表格的配置选项
     * @param rows
     * @returns {Object}
     */
    const getUpgradeClientTableOption = (rows) => {
        return {
            toolbarId: '#vin_client_upgrade_toolbar',  // 占位
            vin_toolbar: '#vin_client_upgrade_toolbar',
            sortable: false,
            clickToSelect: true,
            pagination: true,
            sidePagination: 'client',
            onResetView: () => {
                $("#upgradeClientTableWrapper .fixed-table-body").css({
                    "height": 200,
                });
            },
            columns: getUpgradeClientTableColumns(),
            data: rows,
        };
    };

    /**
     * 初始化升级客户端表格
     * @param rows
     */
    const initUpgradeClientTable = (rows) => {
        let reqData = {
            agent_uuid_list: rows.map(row => row.agent_uuid),
        };
        Metronic.blockUI({target: '#upgradeModal', animate: true});
        pAjaxRequest(reqData, `/api/v1/agents/upgrade`, 'GET', res => {
            Metronic.unblockUI('#upgradeModal');
            if (!res.success) {
                res.data = {
                    total: 0,
                    rows: [],
                };
            }
            window.sessionStorage.removeItem('upgradeClientTable_pageRecord');
            $('#upgradeClientTable').bootstrapTable('destroy').baseTableConfig().init(getUpgradeClientTableOption(res.data.rows));
        });
    };

    /**
     * 获取安装驱动表格的列定义
     * @returns {Object}
     */
    const getInstallDriverTableColumns = () => {
        return [{
            checkbox: true,
            width: '2',
            widthUnit: '%',
            sortable: false,
            formatter: (value, row) => {
                if (!row.online_flag) {
                    return {disabled: true, checked: false};
                }
                if (parseInt(row.driver_status) === DRIVER_STATUS_ENUM.HEALTH) {
                    return {disabled: true, checked: false};
                }
                return value;
            },
        }, {
            title: LANG.UI_CLIENT_IP_ADDRESS,
            field: 'agent_ip',
            width: '10',
            widthUnit: '%',
        }, {
            title: LANG.UI_CLIENT_OS,
            field: 'os_version',
            width: '15',
            widthUnit: '%',
        }, {
            title: LANG.UI_CLIENT_INSTALL_DRIVER_TABLE_DETAIL,
            field: 'driver_status',
            width: '15',
            widthUnit: '%',
            formatter: (driverStatus, row) => {
                let hardwareInfo;
                try {
                    hardwareInfo = JSON.parse(row.hardware_info);
                } catch (e) {
                    hardwareInfo = [];
                }
                if (parseInt(driverStatus) === DRIVER_STATUS_ENUM.HEALTH) {
                    return `<span title="${LANG.UI_PUBLIC_NOTHING}">${LANG.UI_PUBLIC_NOTHING}</div>`;
                }
                let content = `<span class=\'driver_instanll_popover\'>${LANG.UI_CLIENT_DRIVER_STATUS_ABNORMAL_LACK_HARDWARE}<br><br>`;
                let lack_hardware_list = [];
                for (const hardwareRow of hardwareInfo) {
                    if (hardwareRow['status'] !== 'unknown') {
                        continue;
                    }
                    lack_hardware_list.push(hardwareRow['description'] + '<br>' + hardwareRow['hardware_id_list'][0]);
                }
                content += lack_hardware_list.join('<br><br>');
                content += `</span>`;
                let text = LANG.UI_CLIENT_DRIVER_STATUS_ABNORMAL_LACK_DRIVER_LIB
                if (parseInt(driverStatus) === DRIVER_STATUS_ENUM.LACK_DRIVER) {
                    text = LANG.UI_CLIENT_DRIVER_STATUS_ABNORMAL_LACK_CLIENT;
                }
                return `
                <span style="background: transparent" class="popovers" data-container="body" data-trigger="hover"
                    data-html="true" data-placement="left" data-content="${content}">
                    ${text}
                </span>
                `;
            },
            cellStyle: (value, row, index) => {
                return {
                    css: {
                        'word-break': 'break-all',
                        'white-space': 'normal',
                        'vertical-align': 'middle',
                    }
                }
            },
        }, {
            title: LANG.UI_PUBLIC_STATUS,
            field: 'online_flag',
            width: '5',
            widthUnit: '%',
            formatter: value => {
                let labelClass = !!value ? 'label-success' : 'label-default';
                let text = !!value ? LANG.UI_VISUAL_ONLINE : LANG.UI_VISUAL_OFF_LINE;
                return `<span class="label label-sm ${labelClass}" title="${text}">
		        	${text}
		        </span>`;
            },
        }];
    };

    /**
     * 获取安装驱动表格的配置选项
     * @param rows
     * @returns {Object}
     */
    const getInstallDriverTableOption = (rows) => {
        return {
            toolbarId: '#vin_client_install_driver_toolbar',  // 占位
            vin_toolbar: '#vin_client_install_driver_toolbar',
            sortable: true,
            clickToSelect: true,
            pagination: true,
            sidePagination: 'client',
            onResetView: () => {
                $("#installDriverTableWrapper .fixed-table-body").css({
                    "height": 200,
                });
            },
            onPostBody: () => {
                $('span.driver_instanll_popover').closest('div.popover.in').remove();
                $('#installDriverTableWrapper .popovers').popover();
            },
            columns: getInstallDriverTableColumns(),
            data: rows,
        };
    };

    /**
     * 初始化安装驱动表格
     * @param rows
     */
    const initInstallDriverTable = rows => {
        let reqData = {
            agent_uuid_list: rows.map(row => row.agent_uuid),
        };
        Metronic.blockUI({target: '#installDriverModal', animate: true});
        pAjaxRequest(reqData, `/api/v1/drivers/agent/install`, 'GET', res => {
            Metronic.unblockUI('#installDriverModal');
            if (!res.success) {
                res.data = {
                    total: 0,
                    rows: [],
                };
            }
            window.sessionStorage.removeItem('installDriverTable_pageRecord');
            $('#installDriverTable').bootstrapTable('destroy').baseTableConfig().init(getInstallDriverTableOption(res.data.rows));
        });
    };

    //////////////////// 结束-客户端升级 ////////////////////

    //初始化客户端下载
    var initDownloadClient = function () {
        pAjaxRequest({},'/api/v1/agents/download/name','GET',(d)=>{
            var agentInfo = JSON.parse(d.data);
            //操作系统
            var clientOsInfo = agentInfo.clientOs;
            _OS = clientOsInfo;
            var ossystem = $('.ossystem_select');
            ossystem.empty();
            for (var j = 0; j < clientOsInfo.length; j++) {
                var option = $("<option>").text(clientOsInfo[j].text).val(j);
                ossystem.append(option);
            }
            ossystem.trigger('change');

            //客户端
            var clientInfo = agentInfo.client;
            var filesystem = $('.filesystem_select');
            filesystem.empty();
            for (var j = 0; j < clientInfo.length; j++) {
                var option = $("<option>").text(clientInfo[j].text).val('#');
                filesystem.append(option);
            }
        });
    }


    //下载包
    var downloadPackage = function (href) {
        if ("#" == href) {
            return;
        }
        window.location = href;
    }

    var initVcenters = function () {
        pAjaxRequest({all_type_flag: true}, "/api/v1/vm/overview/platforms", "GET", function (d) {
            let list = d.data.rows;
            $('#applied_vcenters').empty();
            $.each(list, function (i, v) {
                let option = $("<option>").text('(' + v.hypervisor_text + ') ' + v.platform_ip).val(v.platform_uuid);
                $('#applied_vcenters').append(option);
            });
            $('#applied_vcenters').selectpicker('refresh');
        }, false);
    }

    var initOldVcenters = function (applied_vcenters, selectorID) {
        pAjaxRequest({all_type_flag: true}, "/api/v1/vm/overview/platforms", "GET", function (d) {
            let list = d.data.rows;
            $('#' + selectorID).empty();
            $.each(list, function (i, v) {
                let selected = applied_vcenters.includes(v.platform_uuid) ? 'selected' : '';
                let option = $("<option " + selected + ">").text('(' + v.hypervisor_text + ') ' + v.platform_ip).val(v.platform_uuid);
                $('#' + selectorID).append(option);
            });
            $('#' + selectorID).selectpicker('refresh');
        }, false);
    }

    return {
        //main function to initiate the module
        init: function () {
            console.log('client-manager');  // 这里打印是为了方便debug
            initDatetimePicker();
            initClientTable();
            addListeners();
            initDownloadClient();
            initVcenters();
        }

    };

}();

jQuery(document).ready(function () {
    ClientManager.init();
});