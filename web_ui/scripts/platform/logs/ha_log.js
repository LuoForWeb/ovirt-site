var HaLog = (() => {
    /**
     * 高可用类型
     */
    const HA_TYPE_DES = {
        0: LANG.UI_PUBLIC_UNKNOWN,
        1: LANG.UI_CLUSTER_HA_TYPE_1,
        2: LANG.UI_CLUSTER_HA_TYPE_2,
    };

    /**
     * 资源调度类型
     */
    const HA_RR_TYPE_DES = {
        0: LANG.UI_PUBLIC_UNKNOWN,
        1: LANG.UI_CLUSTER_HA_RR_TYPE_1,
        2: LANG.UI_CLUSTER_HA_RR_TYPE_2,
        3: LANG.UI_CLUSTER_HA_RR_TYPE_3,
        4: LANG.UI_CLUSTER_HA_RR_TYPE_4,
    };

    /**
     * 日志级别
     */
    const LOG_LEVEL_ENUM = {
        UNKNOWN: 0,
        INFO: 1,
        WARN: 2,
        ERROR: 3,
    };
    let initLogListFlag = true;

    /**
     * 初始化监听器
     */
    const initListener = () => {
        $('#logs_nav').on('click', function(event) {
            if (event.target.tagName === 'A') { // 检查点击的元素是否是一个 <a> 标签
                // 获取父元素 li.nav-item
                let clickedItem = event.target.parentElement;
                let navType = clickedItem.getAttribute('data-type');

                if (navType === 'ha_log') {
                    initHaLogTable();
                }
            }
        });
        $('#haLogWrapper').on('click', '#deleteHaLog', deleteHaLog);
    };
    let deleteBox = () => {
        bootbox.confirm({
            title: LANG.UI_LOG_DELETE,
            message: LANG.UI_LOG_DELETE_TIPS,
            callback: function (res) {
                if (!res) {
                    return;
                }
                let rows = $('#haLogTable').bootstrapTable('getSelections');
                let ids = rows.map(item => item.log_id);
                Metronic.blockUI({ target: '#haLogWrapper', animate: true });
                pAjaxRequest({ ha_log_id_list: ids }, `/api/v1/logs/ha_logs`, 'DELETE', res => {
                    Metronic.unblockUI('#haLogWrapper');
                    if (!res.success) {
                        UIToastr.showInfo(LANG.UI_LOG_DELETE, res.message);
                        return;
                    }
                    UIToastr.showSuccess(LANG.UI_LOG_DELETE, res.message);
                    $('#haLogTable').bootstrapTable('refresh');
                });
            },
        });
    }

    /**
     * 删除高可用日志
     */
    const deleteHaLog = () => {
        // 操作权限判断，需要传归属用户的user_uuid，多个user_uuid用逗号隔开
        let user_uuid_arr = $.map($('#haLogTable').bootstrapTable('getSelections'), function (row) {
            return row.user_uuid;
        });
        // 集群日志表没有user_uuid,需要查询历史任务
        if (user_uuid_arr.join(',') != '') {
            checkOperateAuth({ type: 1, user_uuid: user_uuid_arr.join(','), auth: 'log' }, () => {
                deleteBox();
            });
        } else {
            deleteBox();
        }
    };

    //////////////////// 开始-表格 ////////////////////

    /**
     * 获取日志级别样式
     * @param {*} logLevel
     */
    const getLogLevelClass = logLevel => {
        logLevel = parseInt(logLevel);
        switch (logLevel) {
            case LOG_LEVEL_ENUM.WARN:
                return 'label-warning';
            case LOG_LEVEL_ENUM.ERROR:
                return 'label-danger';
            case LOG_LEVEL_ENUM.INFO:
                return 'label-success';
            default:
                return 'label-info';
        }
    };

    /**
     * 获取表格列头
     */
    const getHaTableColumns = () => {
        let columns = [{
            checkbox: true,
            width: 1,
            widthUnit: '%',
            sortable: false,
        }, {
            title: LANG.UI_PUBLIC_TABLE_ID,
            field: 'log_id',
        }, {
            title:LANG.UI_PUBLIC_TASK_NAME,
            field: 'task_name',
            formatter: (value) => {
                value = value.length ? value : '--';
                return `<span title="${value}">${value}</span>`;

            },
        }, {
            title: LANG.UI_CLUSTER_HA_TYPE,
            field: 'ha_type',
            formatter: (value) => {
                value = typeof HA_TYPE_DES[parseInt(value)] !== 'undefined' ? HA_TYPE_DES[parseInt(value)] : HA_TYPE_DES[0];
                return `<span title="${value}">${value}</span>`;
            },

        }, {
            title: LANG.UI_CLUSTER_HA_RR_TYPE,
            field: 'ha_rr_type',
            formatter: (value) => {
                value = typeof HA_RR_TYPE_DES[parseInt(value)] !== 'undefined' ? HA_RR_TYPE_DES[parseInt(value)] : HA_RR_TYPE_DES[0];
                return `<span title="${value}">${value}</span>`;
            },
        }, {
            title: LANG.UI_PUBLIC_STATUS,
            field: 'log_level',
            formatter: (value, row) => {
                return `<span class="label label-sm ${getLogLevelClass(value)}">${row.log_level_des}</span>`;
            },

        }, {
            title: LANG.UI_LOG_TIME,
            field:'ha_time',
        }, {
            title: LANG.UI_PUBLIC_DESCRIPTION,
            field: 'description',
            width: '30',
            widthUnit: '%',
            sortable: false,
            formatter: (value, row) => {
                const newRegex = /<span[^>]*>|<\/span>/gi;
                return `<span title = "${value.replace(newRegex, '')}">${value}</span>`;
            }
        }];
        //如果是GMP 屏蔽了删除按钮 那么这个勾选框就没用了 需要一并删除
        if(CONF.ENTERPRISE == "vdms_enterprise"){
            columns.shift();
        }
        return columns;
    };

    /**
     * 自定义工具栏
     */
    const haCustomTool = () => {
        let beforeInput = `<div class="btn-group" style="margin: 0">`;
        if (CONF.PERMISSION_ARR.includes('p_ha_log_delete')) {
            beforeInput += `<button class="btn viconfont vicon-a-Deleteshanchu1 b-btn brr2 mr12 exch-forbid-event" id="deleteHaLog"></button>`;
        }
        beforeInput += `</div>`;
        if(CONF.ENTERPRISE == "vdms_enterprise"){ //GMP 屏蔽了删除按钮
            beforeInput = ``;
        }
        return {beforeInput};
    };

    /**
     * 表格行选中了
     */
    const haRowCheck = () => {
        modifyDelStyle('haLogTable', 'deleteHaLog');
    };

    /**
     * 获取查询参数
     */
    const getQueryParams = () => {
        let params = {};
        // 按任务名搜索
        let searchValue = $('#vin_ha_toolbar .haSearch').val().trim();
        if (searchValue) {
            params.task_name = searchValue;
        }

        return params;
    };

    /**
     * 获取表格高度
     */
    const initHaTableHeight = () => {
        let toolbarHeight = 50;
        let paginationHeight = 52;
        let tabTitleHeight = 48;
        let navTitleHeight = 46;
        // page-content有40px的内边距
        // portlet-body有10px的内边距
        // tab-content有20px的外边距离
        let otherHeight = 40 + 10 + 20 + 18;
        let height = window.innerHeight - toolbarHeight - paginationHeight - tabTitleHeight - navTitleHeight - otherHeight;

        $("#halogdiv .fixed-table-body").css({
            "height": height,
        });
    };

    /**
     * 获取表格配置
     */
    const getHaLogTableOption = () => {
        return {
            vin_url: '/api/v1/logs/ha_logs',
            vin_params: getQueryParams,
            vin_method: 'get',
            toolbarId: '#vin_ha_toolbar',
            vin_toolbar: '.vin_ha_toolbar',
            buttonsToolbar: '.vin_ha_btnToolbar',
            // 搜索相关
            searchInput: true,
            placeholder: LANG.UI_SEARCH_BY_TASK_NAME,
            searchClass: 'haSearch',
            searchSelector: '.haSearch',
            showSearchButton: true,
            // 排序
            sortName:'ha_time',
            sortOrder: 'desc',
            // 隐藏行
            hideColumns: '',
            showButtonText: false,
            clickToSelect: true,
            pagination: true,
            resizable: true,
            showRefresh: false,
            showExport: !!CONF.PERMISSION_ARR.includes('p_ha_log_export'),
            showColumns: true,
            exportSettings: {
                showBuiltIn: ['json', 'xml', 'csv', 'txt', 'sql', 'excel'], // 需要显示的默认导出项
                custom: [
                    {
                        label: LANG.UI_TOOLS_TABLE_EXPORT_ALL_EXCEL,
                        class: 'export-all-excel' 
                    }
                ]
            },
            onResetView: initHaTableHeight,
            onRefresh: () => {
                $("#haLogTable").bootstrapTable('hideLoading');
            },
            onPostBody: () => {
                $('div.popover.in').hide();
                $('.popovers').popover();
                haRowCheck();

                const exportOptions = {
                    toolbarId: 'vin_ha_toolbar',
                    url: '/api/v1/logs/ha_logs',
                    fileName: LANG.UI_CLUSTER_HA_LOG,
                }
                
                // 监听导出全部数据
                exportAllTableData(exportOptions);
            },
            onCheck: haRowCheck,
            onUncheck: haRowCheck,
            onCheckAll: haRowCheck,
            onUncheckAll: haRowCheck,
            customTool: haCustomTool(),
            LoadSuccess: () => {
				if(initLogListFlag){
                    $('#taskLog th[data-field="log_id"]').css('width', '5%');
                    $('#taskLog th[data-field="task_name"]').css('width', '15%');
                    $('#taskLog th[data-field="ha_type"]').css('width', '10%');
                    $('#taskLog th[data-field="ha_rr_type"]').css('width', '10%');
                    $('#taskLog th[data-field="log_level"]').css('width', '8%');
                    $('#taskLog th[data-field="ha_time"]').css('width', '15%');
                    $('#taskLog th[data-field="description"]').css('width', '30%');
                    initLogListFlag = false;
                }
            },
            columns: getHaTableColumns(),
        };
    };

    /**
     * 初始化表格
     */
    const initHaLogTable = () => {
        window.sessionStorage.removeItem('haLogTable_pageRecord');
        $('#haLogTable').bootstrapTable('destroy').baseTableConfig().init(getHaLogTableOption());
        // 搜索图标按钮事件
		$(`#vin_ha_toolbar .search-btn`).on('click', (e) => {
			$('#haLogTable').bootstrapTable('refresh');
		});
    };

    //////////////////// 结束-表格 ////////////////////

    return {
        init: () => {
            initListener();
        }
    }
})();


jQuery(document).ready(function() {
    HaLog.init();
});