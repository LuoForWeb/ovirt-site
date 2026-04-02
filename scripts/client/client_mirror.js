//客户端分组管理
var ClientMirror = function () {
    let mirrorCheckedUuidList = [];
    let isInCreate = false
    let changeMirrorRowHeightFlag = false;
    const MIRROR_OS_ARCH_ENUM = {
        x86_64: 9,
        aarch64: 12,
    };
    const MIRROR_OS_ARCH_MAP = {
        9: 'x86_64',
        12: 'aarch64',
    };

    /**
     * 获取查询参数
     */
    const getQueryParams = () => {
        let params = {};
        let search = $('.mirror-search').val().trim();
        if (search) {
            params.search = search;
        }
        return params;
    };

    /**
     * 自定义镜像工具
     * @returns {{beforeInput: string, afterInput: string}}
     */
    const customMirrorTool = () => {
        let beforeInput = `<div class="btn-group" style="margin: 0">`;
        // 删除
        if (CONF.PERMISSION_ARR.includes('p_client_mirror_delete')) {
            beforeInput += `
			<button type="button" id="deleteMirror" class="b-btn brr2 mr12 table-toolbar-btn">
				<i class="icon-gray-delete"></i>
			</button>
			`;
        }
        beforeInput += `</div>`;
        let afterInput = `<div class="btn-group" style="margin: 0">`;
        // 生成
        if (CONF.PERMISSION_ARR.includes('p_client_mirror_add')) {
            afterInput += `
			<button class="dropdown-toggle btn-font flex_center btn-title p-lr8 btn table-toolbar-btn" id="addShow"
				style="width: auto; height: 34px; border: 0">
				<i class="viconfont vicon-biaogetianjia"></i>
				<span class="pl2">${LANG.UI_CLIENT_CREATE}</span>
			</button>
			`;
        }
        // 清理
        if (CONF.PERMISSION_ARR.includes('p_client_mirror_modify')) {
            afterInput += `
			<button class="dropdown-toggle btn-font flex_center btn-title p-lr8 btn table-toolbar-btn" id="cancel"
				style="width: auto; height: 34px; border: 0">
				<i class="viconfont vicon-qingli"></i>
				<span class="pl2">${LANG.UI_CLIENT_CANCLEING}</span>
			</button>
			`;
        }
        afterInput += `</div`;
        return { beforeInput, afterInput };
    };

    /**
     * 自定义镜像操作事件
     */
    const initMirrorBtnOp = () => {
        return {
            'click .fileDownload': function (e, value, row, index) {
                if (parseInt(row.status) !== 2) {
                    UIToastr.showWarning(LANG.UI_CLIENT_DOWNLOAD_MIRROR, LANG.UI_CLIENT_MIRROR_NOT_DOWNLOAD);
                    return false
                }
                const reqData = {
                    filepath: row.path + '/' + row.name,  // 绝对路径
                };
                pAjaxRequest(reqData, `/api/v1/system/generate/download`, `GET`, function (res) {
                    if ("#" === res.data.url) {
                        return;
                    }
                    window.location.href = res.data.url;
                });
            },
        };
    };

    /**
     * 自定义镜像操作列格式化
     * @returns {string}
     */
    const formatterOperate = () => {
        let operate = `<div class="btn-group">`;
        operate += `
		<div class="btn_operation_vicon">
			<a class="fileDownload">
				<i class="viconfont vicon-download"></i>
			</a>
		</div>
		`;
        operate += `</div>`;
        return operate;
    };

    /**
     * 自定义镜像表格列
     * @returns {Array<Object>}
     */
    const getMirrorTableColumns = () => {
        let columns = [{
            checkbox: true,
            sortable: false,
            width: '2',
            widthUnit: '%',
        }, {
            field: 'name',
            title: LANG.UI_MIRROR_NAME,
            width: '20',
            widthUnit: '%',
            sortable: false,
            align: 'center',
        }, {
            field: 'os_type',
            title: LANG.UI_MIRROR_OS_TYPE,
            width: '8',
            widthUnit: '%',
            sortable: true,
            align: 'center',
            formatter: function (value) {
                let strategyType;
                switch (value) {
                    case 1:
                        strategyType = 'Linux'
                        break;
                    case 2:
                        strategyType = 'Windows';
                        break;
                    default:
                        break;
                }
                return '<span title="' + strategyType + '">' + strategyType + '</span>';
            }
        }, {
            field: 'os_arch',
            title: LANG.UI_MIRROR_OS_ARCH,
            width: '8',
            widthUnit: '%',
            sortable: true,
            align: 'center',
            formatter: function (value) {
                value = parseInt(value);
                let text = LANG.UI_PUBLIC_UNKNOWN;
                if (typeof MIRROR_OS_ARCH_MAP[value] !== 'undefined') {
                    text = MIRROR_OS_ARCH_MAP[value];
                }
                return `<span title="${text}">${text}</span>`;
            }
        }, {
            field: 'purpose',
            title: LANG.UI_MIRROR_TYPE,
            width: '8',
            widthUnit: '%',
            sortable: true,
            align: 'center',
            formatter: function (value) {
                let strategyType;
                switch (value) {
                    case 1:
                        strategyType = LANG.UI_CLIENT_DRIVER_COVER
                        break;
                    case 2:
                        strategyType = LANG.UI_CLIENT_SYSTEM_COVER;
                        break;
                    default:
                        break;
                }
                return '<span title="' + strategyType + '">' + strategyType + '</span>';
            }
        }, {
            field: 'ip',
            title: LANG.UI_MIRROR_IP,
            width: '10',
            widthUnit: '%',
            sortable: true,
            align: 'center',
            formatter: function (value) {
                if (!value) {
                    return '--';
                } else {
                    return `<span title="${value}">${value}</span>`;
                }
            }
        }, {
            field: 'status',
            title: LANG.UI_MIRROR_STATUS,
            width: '8',
            widthUnit: '%',
            sortable: true,
            align: 'center',
            formatter: function (value) {
                let strategyType;
                switch (value) {
                    case 1:
                        strategyType = LANG.UI_CLIENT_PRODUCTTING
                        return '<span class="label label-sm label-info status-icon">' + strategyType + '</span>';
                    case 2:
                        strategyType = LANG.UI_VISUAL_ALREADY_FINISH;
                        return '<span class="label label-sm label-success status-icon">' + strategyType + '</span>';
                    case 3:
                        strategyType = LANG.UI_BACKUP_DATA_LABEL_EXPIRED;
                        return '<span class="label label-sm label-default">' + strategyType + '</span>';
                    case 4:
                        strategyType = LANG.UI_CLIENT_CANCLE;
                        return '<span class="label label-sm label-default">' + strategyType + '</span>';
                    case 5:
                        strategyType = LANG.UI_VOL_CDP_OPERATION_FAIL;
                        return '<span class="label label-sm label-danger status-icon">' + strategyType + '</span>';
                    default:
                        return `--`;
                }
            }
        }, {
            field: 'create_date',
            title: LANG.UI_TAPE_GENERATE_TIME,
            width: '15',
            widthUnit: '%',
            sortable: true,
            align: 'center',
        }, {
            field: 'expire_date',
            title: LANG.UI_MIRROR_EXPIRE_DATE,
            width: '15',
            widthUnit: '%',
            sortable: true,
            align: 'center',
        }, {
            field: 'strategy_actions',
            title: LANG.UI_PUBLIC_OPERATION,
            width: '6',
            widthUnit: '%',
            sortable: false,
            align: 'center',
            clickToSelect: false,
            events: initMirrorBtnOp(),
            // 生成修改和删除按钮
            formatter: formatterOperate,
        }];
        if (!CONF.PERMISSION_ARR.includes('p_agent_mirror_download')) {
            let operateIndex = -1;
            for (const index in columns) {
                if (columns[index].field === 'strategy_actions') {
                    operateIndex = index;
                    break;
                }
            }
            if (operateIndex !== -1) {
                columns.splice(operateIndex, 1);
            }
        }
        return columns;
    };

    var initDataTable = function () {

        var options = {
            toolbarId: '#mirror_toolbar',
            vin_params: getQueryParams,
            vin_url: "/api/v1/mirror",
            vin_method: "GET",
            placeholder: LANG.UI_MIRROR_EXPORT_ENTER_STRATEGY,
            searchOnEnterKey: true,
            searchInput: true,
            searchAlign: 'right',
            searchClass: 'mirror-search',
            // 排序
            sortName: 'create_date',
            sortOrder: 'desc',
            changeHeightBtn: true, //改变高度按钮
            pagination: true, //分页
            fullPage: true,//表格高度适配
            PostBody: function () {
                checkRecord();
                mirrorRowCheck();
                setMirrorRowHeight();
                refreshMirrorTable();
            },
            onCheck: mirrorRowCheck,
            onUncheck: mirrorRowCheck,
            onCheckAll: mirrorRowCheck,
            onUncheckAll: mirrorRowCheck,
            onRefresh: () => {
                $('#mirror_table').bootstrapTable('hideLoading');
            },
            resizable: true, //可变宽度
            customTool: customMirrorTool(),
            columns: getMirrorTableColumns(),
        }
        $('#mirror_table').baseTableConfig().init(options);
    };

    const refreshMirrorTable = () => {
        if (timerTask.MIRROR) {
            clearTimeout(timerTask.MIRROR);
            timerTask.MIRROR = null;
        }
        timerTask.MIRROR = setTimeout(() => {
            $('#mirror_table').bootstrapTable('refresh');
        }, 5000);
    };
    const mirrorRowCheck = () => {
        checkEvent('#mirror_table', '#deleteMirror');
        let rows = $('#mirror_table').bootstrapTable('getSelections');
        mirrorCheckedUuidList = rows.map((item) => item.uuid);
    };
    const checkRecord = function () {
        $('#mirror_table').bootstrapTable('checkBy', {
            field: 'uuid',
            values: mirrorCheckedUuidList
        });
    }

    const initListeners = function () {
        $.fn.NetworkConfig.init($('#ipConfig'), {
            input_width: 'col-md-8',
        });
        $('#addShow').unbind('click').on('click', addShow);
        //清理
        $('#cancel').unbind('click').on('click', cancelMirror);
        //删除
        $('#mirror_toolbar')
            .on('click', '.search button.search-btn', () => {
                $('#mirror_table').bootstrapTable('refresh');
            })
            .on('click', '#deleteMirror', deleteMirror)
            .on('click', '.change_height', changeMirrorRowHeight);//改变高度
        //添加确认
        $('#submit_add').on('click', addSubmit);
        //选中切换
        $('#radio_group_3').on('change', osTypeChange);
        $('#coverType').unbind('change').on('change', coverType);
        $('#proxy_type').unbind('change').on('change', proxyType);
        initServerIp()
        $('#second_input').on('change', mirrorRetentionTimeChange);
        $('#mirrorRetentionTimeSpinner button').on('click', mirrorRetentionTimeChange);
    }

    /**
     * 镜像保留时间
     */
    const mirrorRetentionTimeChange = () => {
        let mirrorRetentionTime = $('#second_input');
        let value = parseInt(mirrorRetentionTime.val());
        if (!mirrorRetentionTime.val() || value < 1) {
            value = 1;
        } else if (value > 10080) {
            value = 10080;
        }
        $('#mirrorRetentionTimeSpinner').spinner('value', value);
    };

    const initServerIp = () => {
        Metronic.blockUI({target: '.server-ip', animate: true});
        pAjaxRequest({}, `/api/v1/nodes/master_network`, 'GET', res => {
            Metronic.unblockUI('.server-ip');
            if (!res.success) {
                UIToastr.showWarning(LANG.UI_CLIENT_ADD, res.message);
                return;
            }
            let options = ``;
            for (const row of res.data.rows) {
                options += `<option value="${row.network_ip}">${row.network_ip}</option>`;
            }
            $('#system_type').html(options);
        });
    };

    const addShow = function () {
        Metronic.blockUI({target: '#clientmirrordiv', animate: true});
        let reqData = {
            offset: 0,
            limit: 200,
        };
        pAjaxRequest(reqData, '/api/v1/mirror', 'get', function (res) {
            Metronic.unblockUI('#clientmirrordiv');
            isInCreate = res.data.rows.some(item => item.status == 1);
            if (isInCreate) {
                UIToastr.showWarning(LANG.UI_MIRROR_TEMPLATE_ADD, LANG.UI_MIRROR_ADDING);
                return false;
            } else {
                $('#drawer-add').drawer('show');
                $(`#radio_group_3 .radio-group__item[value="1"]`).trigger('click');
                $('#mirrorOsArch').val(MIRROR_OS_ARCH_ENUM.x86_64);
                $('#coverType').val('2');
                $('#proxy_type').val('2');
                $('#proxy_server').val('22710');
                $('#server_proxy').val('23100');
                $('#system_type option:selected').removeAttr('selected');
                $('#mirrorRetentionTimeSpinner').spinner('value', 30);
                // 重新初始化网络配置组件
                $.fn.NetworkConfig.init($('#ipConfig'), {
                    input_width: 'col-md-8',
                });
                coverType()
                proxyType();
            }
        })

    }
    const proxyType = function () {
        let selectValue = $('#proxy_type').find('option:selected').val();
        if (selectValue == '2') {
            $('.server-port').show()
            $('.client-port').hide()
        } else {
            $('.server-port').hide()
            $('.client-port').show()
        }
    }
    const coverType = function () {
        let selectValue = $('#coverType').find('option:selected').val();
        if (selectValue == 1) {
            $('.systemCover').hide()
        } else {
            $('.systemCover').show()

        }
    }

    const osTypeChange = function (e, oldVal, newVal) {
        if (newVal == 2) {
            $('#mirrorOsArch').val(MIRROR_OS_ARCH_ENUM.x86_64).attr('disabled', true);
        } else {
            $('#mirrorOsArch').attr('disabled', false);
        }
    }

    const deleteMirror = function () {
        var isFalse = getIdSelectedIdFlag('#mirror_table')
        if (isFalse) {
            return UIToastr.showWarning(LANG.UI_MIRROR_DELETE, LANG.UI_CLIENT_MIRROR_NOT_DELETE);
        }
        var template_uuid = getIdSelectedId('#mirror_table')
        var requestData = []
        //策略必选
        if (template_uuid.length == 0) {
            return UIToastr.showInfo(LANG.UI_MIRROR_DELETE, LANG.UI_MIRROR_STRATEGY_DELETE_TIPS);
        } else {
            requestData = template_uuid.map(item => {
                return {
                    uuid: item.uuid,
                    name: item.name,
                    delete_type: 1
                }
            })
        }
        bootbox.confirm({
            title: LANG.UI_MIRROR_DELETE,
            message: LANG.UI_MIRROR_TEMPLATE_DELETE_CONFIRM,
            callback: debounce(function (r) {
                if (!r) {
                    return;
                }
                Metronic.blockUI({target: '.client-mirror-table-container', animate: true});
                pAjaxRequest({mirror_list: requestData}, '/api/v1/mirror', 'delete', function (res) {
                    Metronic.unblockUI('.client-mirror-table-container');
                    if (res.success) {
                        $('#mirror_table').bootstrapTable('refresh');
                        return UIToastr.showSuccess(LANG.UI_MIRROR_DELETE, LANG.UI_MIRROR_TEMPLATE_DELETE_SUCCESS);
                    } else {
                        UIToastr.showWarning(LANG.UI_MIRROR_DELETE, LANG.UI_MIRROR_TEMPLATE_DELETE_FAIL);
                    }
                });
            }, 300)
        })
    }

    /**
     * 设置表格每行高度
     */
    const setMirrorRowHeight = () => {
        let rows = $('#mirror_table').bootstrapTable('getData');
        if (!rows.length) {
            return;
        }
        if (changeMirrorRowHeightFlag) {
            $('#mirror_table>tbody>tr>td').css({
                'padding-top': '15.25px',
                'padding-bottom': '15.25px'
            })
        } else {
            $('#mirror_table>tbody>tr>td').css({
                'padding-top': '4.25px',
                'padding-bottom': '4.25px'
            })
        }
    };

    /**
     * 改变表格每行高度
     */
    const changeMirrorRowHeight = () => {
        if (!changeMirrorRowHeightFlag) {
            changeMirrorRowHeightFlag = true;
            $('#mirror_toolbar .change_height i').addClass('icon-auto-height2');
        } else {
            changeMirrorRowHeightFlag = false;
            $('#mirror_toolbar .change_height i').removeClass('icon-auto-height2');
        }
        setMirrorRowHeight();
    };

    const cancelMirror = function () {
        var isFalse = getIdSelectedIdFlag('#mirror_table')
        if (isFalse) {
            return UIToastr.showWarning(LANG.UI_MIRROR_TEMPLATE_CANCLE, LANG.UI_MIRROR_TEMPLATE_NOT_CANCLE);
        }
        var template_uuid = getIdSelectedId('#mirror_table')
        //策略必选
        if (template_uuid.length == 0) {
            return UIToastr.showInfo(LANG.UI_MIRROR_REPORT_CANCLE, LANG.UI_MIRROR_REPORT_CANCEL_TIPS);
        } else {
            var requestData = template_uuid.map(item => {
                return {
                    uuid: item.uuid,
                    name: item.name,
                    delete_type: 2
                }
            })
        }
        bootbox.confirm({
            title: LANG.UI_MIRROR_REPORT_CANCLE,
            message: LANG.UI_MIRROR_TEMPLATE_CANCLE_CONFIRM,
            callback: debounce(function (r) {
                if (!r) {
                    return;
                }
                Metronic.blockUI({target: '.client-mirror-table-container', animate: true});
                pAjaxRequest({mirror_list: requestData}, '/api/v1/mirror', 'delete', function (res) {
                    Metronic.unblockUI('.client-mirror-table-container');
                    if (res.success) {
                        $('#mirror_table').bootstrapTable('refresh');
                        return UIToastr.showSuccess(LANG.UI_MIRROR_REPORT_CANCLE, LANG.UI_MIRROR_TEMPLATE_CANCLE_SUCCESS);
                    } else {
                        UIToastr.showWarning(LANG.UI_MIRROR_REPORT_CANCLE, LANG.UI_MIRROR_TEMPLATE_CANCLE_FAIL);
                    }
                });
            }, 300)
        })
    }

    /**
     * 显示强制添加镜像对话框
     * @param title
     * @param message
     * @return {Promise<unknown>}
     */
    const showForceAddMirrorDialog = (title, message) => {
        Metronic.blockUI({target: '#drawer-add', animate: true});
        return new Promise(resolve => {
            bootbox.confirm({
                title: `<span class="force-add-dialog">${title}</span>`,
                message: message,
                onEscape: false,
                callback: debounce(function (result) {
                    Metronic.unblockUI('#drawer-add');
                    if (!result) {
                        return;
                    }
                    resolve();
                }, 300),
                onShown: function (ev) {
                    console.log(ev, this)
                }
            }).on('shown.bs.modal', function (ev) {
                console.log(ev, this)
                // $(this).closest('.modal-scrollable').css('z-index', '10055');  // 这里是抽屉里面的弹窗
            });
        })
    };

    /**
     * 检测已经配置的IP地址是否可达
     * @param requestData {Object}
     * @return {Promise<unknown>}
     */
    const checkConfigIpReachable = requestData => {
        return new Promise(resolve => {
            let mirrorType = parseInt($('#coverType').val());
            if (mirrorType !== 2) {  // 只有系统恢复镜像才可以配置IP
                resolve(requestData);
                return;
            }
            let ipList = [];
            if (parseInt(requestData.ipv4_set.method) === $.fn.NetworkConfig.CONFIG_TYPE_ENUM.manual) {
                for (const ipv4Row of requestData.ipv4_set.ip_set) {
                    ipList.push(ipv4Row.ip);
                }
            }
            if (parseInt(requestData.ipv6_set.method) === $.fn.NetworkConfig.CONFIG_TYPE_ENUM.manual) {
                for (const ipv6Row of requestData.ipv6_set.ip_set) {
                    ipList.push(ipv6Row.ip);
                }
            }
            if (!ipList.length) {
                resolve(requestData);
                return;
            }
            Metronic.blockUI({target: '#drawer-add', animate: true});
            pAjaxRequest({ip_list: ipList}, `/api/v1/system/ip_reachable/batch_check`, `POST`, res => {
                Metronic.unblockUI('#drawer-add');
                if (!res.success) {
                    UIToastr.showWarning(LANG.UI_MIRROR_TEMPLATE_ADD, res.message);
                    return;
                }
                if (parseInt(res.data.statistics.reachable) !== 0) {
                    let reachableIpList = [];
                    for (const checkedIpItem of res.data.checked_ip_list) {
                        if (checkedIpItem.reachable) {
                            reachableIpList.push(checkedIpItem.ip);
                        }
                    }
                    let message = LANG.UI_MIRROR_CONFIG_IP_EXISTS.replace('%S', `<span style="color: red">${reachableIpList.join('、')}</span>`);
                    showForceAddMirrorDialog(LANG.UI_MIRROR_TEMPLATE_ADD, message).then(() => {
                        resolve(requestData);
                    });
                } else {
                    resolve(requestData);
                }
            });
        });
    };

    /**
     * 添加镜像请求
     * @param requestData
     */
    const addMirrorRequest = function (requestData) {
        Metronic.blockUI({target: '#drawer-add', animate: true});
        pAjaxRequest({}, '/api/v1/mirror/size', 'get', function (res) {
            if (res.success) {
                pAjaxRequest(requestData, '/api/v1/mirror', 'post', function (res) {
                    Metronic.unblockUI('#drawer-add');
                    if (res.success) {
                        $('#drawer-add').drawer('hide');
                        $('#mirror_table').bootstrapTable('refresh');
                        return UIToastr.showSuccess(LANG.UI_MIRROR_TEMPLATE_ADD, LANG.UI_MIRROR_TEMPLATE_ADD_SUCCESS);

                    } else {
                        return UIToastr.showWarning(LANG.UI_MIRROR_TEMPLATE_ADD, LANG.UI_MIRROR_TEMPLATE_ADD_FAIL);
                    }
                });
            } else {
                Metronic.unblockUI('#drawer-add');
                return UIToastr.showWarning(LANG.UI_MIRROR_TEMPLATE_ADD, LANG.UI_MIRROR_TEMPLATE_ADD_SIZE_FAIL);
            }
        });
    };

    const addSubmit = function () {
        let now = new Date();
        let year = now.getFullYear();
        let month = now.getMonth() + 1;
        month = month < 10 ? '0' + month : month; // 如果月份小于10，前面补0
        let day = now.getDate();
        day = day < 10 ? '0' + day : day; // 如果日期小于10，前面补0
        let hour = now.getHours();
        hour = hour < 10 ? '0' + hour : hour; // 如果小时小于10，前面补0
        let minute = now.getMinutes();
        minute = minute < 10 ? '0' + minute : minute; // 如果分钟小于10，前面补0
        let second = now.getSeconds();
        second = second < 10 ? '0' + second : second; // 如果秒数小于10，前面补0
        var date = year.toString() + month.toString() + day.toString() + hour.toString() + minute.toString() + second.toString()
        var requestData = {}
        requestData.os_type = '';
        requestData.purpose = '';
        requestData.name = '';
        requestData.retention_time = '';
        requestData.net_model = '';
        requestData.server_port = '';
        requestData.client_port = ''
        requestData.listen_ip = '';
        requestData.ipv4_set = {};
        requestData.ipv6_set = {};
        requestData.os_type = $('#radio_group_3').find('.radio-group__item.active').attr("value")
        if (requestData.os_type == '1') {
            requestData.name = 'Linux'
        } else {
            requestData.name = 'Windows'
        }
        requestData.purpose = $('#coverType').find('option:selected').val();
        requestData.os_arch = $('#mirrorOsArch').val();
        requestData.retention_time = Number($('#second_input').val()) * 60;
        if (!requestData.retention_time) {
            UIToastr.showWarning(LANG.UI_MIRROR_TEMPLATE_ADD, LANG.UI_TIME_NOT_EMPTY);
            return false;
        }
        requestData.net_model = $('#proxy_type').find('option:selected').val();
        requestData.server_port = $('#proxy_server').val();
        requestData.listen_ip = $('#system_type').find('option:selected').val();
        requestData.client_port = $('#server_proxy').val();
        var getConfig = $.fn.NetworkConfig.getData($('#ipConfig'))
        if (!getConfig) {
            return false;
        }
        requestData.ipv4_set.method = getConfig.ipv4_set.config_type
        if (requestData.ipv4_set.method == 2) {
            requestData.ipv4_set.ip_set = getConfig.ipv4_set.ip_set
            requestData.ipv4_set.gateway = getConfig.ipv4_set.gateway
            requestData.ipv4_set.dns1 = getConfig.ipv4_set.dns1
            requestData.ipv4_set.dns2 = getConfig.ipv4_set.dns2
        }
        requestData.ipv6_set.method = getConfig.ipv6_set.config_type
        if (requestData.ipv6_set.method == 2) {
            requestData.ipv6_set.ip_set = getConfig.ipv6_set.ip_set
            requestData.ipv6_set.gateway = getConfig.ipv6_set.gateway
            requestData.ipv6_set.dns1 = getConfig.ipv6_set.dns1
            requestData.ipv6_set.dns2 = getConfig.ipv6_set.dns2
        }
        if (requestData.purpose == 1) {
            requestData.name = requestData.name + '-Repair-' + date + '.iso'
        } else {
            requestData.name = requestData.name + '-OsRecovery-' + date + '.iso'
        }
        checkConfigIpReachable(requestData).then(addMirrorRequest);
    }

    function getIdSelectedId(select) {
        return $.map($(select).bootstrapTable('getSelections'), function (row) {
            return row;
        })
    }

    function getIdSelectedIdFlag(select) {
        let isFalse = false
        let filteredRows = $.map($(select).bootstrapTable('getSelections'), function (row) {
            if (row.status == 1) {
                isFalse = true; // 标记遇到了正在生成的镜像
            }
        });
        return isFalse
    }

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

    /**
     * 初始化数字输入框
     */
    const initSpinner = () => {
        $('#mirrorRetentionTimeSpinner').spinner({value: 30, step: 5, min: 1, max: 10080});  // 最大保留7天
    };

    return {
        //main function to initiate the module
        init: function () {
            initDataTable()
            initListeners()
            initSpinner();
            $('.client-port').hide()
        }
    };

}();

jQuery(document).ready(function () {
    ClientMirror.init();
});