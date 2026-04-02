var LogDownload = function () {
    let startTime = '';
    let endTime = '';
    let timePickTypeDes = '';

    const initListener = () => {
        $('#log-download').on('click', '.agentLogAdvance', () => { // 高级搜索按钮
            $('#advanceSearchModal').modal({width: '700px', height: '450px'});
        }).on('click', '#downloadAgentLog', downloadLog);  // 下载日志按钮
        $('#advanceSearchSubmit').on('click', advanceSearchSubmit);
        $('.advancedSearchShow').addClass('__hide').hide();
        // 清除高级搜索内容
        $('#log_searchDiv .clearSearch').on('click', function () {
            $('#log_searchDiv .searchContent').text('');
            $('#log_searchDiv').hide();
            $('#advanceSearchModule option').removeAttr('selected');
            startTime = endTime = '';
            $('#advanceSearchDateRangePicker').val('');
            advanceSearchSubmit();
        });
    };

    /**
     * 操作权限校验
     * @returns {{type: number, source_uuid, source_type: number}|boolean}
     */
    const checkAuth = function() {
        return {
            type: 2,
            source_uuid: $('#clientUUID').val().trim(),
            source_type: parseInt($('#clientAgentType').val()) === 4 ? 2 : 10,
        };
    };

    const downloadLog = () => {
        checkOperateAuth(checkAuth(), () => {
            doDownloadLog();
        });
    };

    /**
     * 执行下载日志
     */
    const doDownloadLog = () => {
        let selectedRows = $('#logDownloadTable').bootstrapTable('getSelections');
        if (!selectedRows.length) {
            UIToastr.showWarning(LANG.UI_CLIENT_LOG_DOWNLOAD, LANG.UI_CLIENT_LOG_DOWNLOAD_TIPS);
            return;
        }
        let reqData = {
            log_path_list: selectedRows.map(row => row['log_path']),
        };
        Metronic.blockUI({target: '#log-download', animate: true});
        pAjaxRequest(reqData, `/api/v1/agents/${$('#clientUUID').val()}/log`, 'POST', res => {
            Metronic.unblockUI('#log-download');
            if (!res.success) {
                UIToastr.showWarning(LANG.UI_CLIENT_LOG_DOWNLOAD, res.message);
                return;
            }
            window.location.href = res.data.file_path;
        });
    };

    const advanceSearchSubmit = () => {
        let moduleType = parseInt($('#advanceSearchModule').val());
        let filters = {};
        if (moduleType) {
            filters.log_module = moduleType;
        }
        if (startTime && endTime) {
            filters.start_time = startTime
            filters.end_time = endTime;
        }
        $('#advanceSearchModal').modal('hide');
        addAdvancedSearchContent(filters);
    };

    const addAdvancedSearchContent = (filters) => {
        let contents = [];
        if (typeof filters.log_module !== 'undefined') {
            let logModule = $('#advanceSearchModule option:selected').html().trim();
            contents.push(`<span id="logModuleShow" title="${logModule}">
                ${LANG.UI_CLIENT_LOG_MODULE}:
                <i>${logModule}</i>
                <em>X</em>
            </span>`);
        }
        if (typeof filters.start_time !== 'undefined' && typeof filters.end_time !== 'undefined') {
            contents.push(`<span id="logTimeShow" title="${filters.start_time}~${filters.end_time}">
                ${LANG.UI_CLIENT_LOG_TIME}:
                <i>${filters.start_time}~${filters.end_time}</i>
                <em>X</em>
            </span>`);
        }
        if (contents.length) {
            $('.advancedSearchShow').removeClass('__hide').show();
            $('#log_searchDiv').show();
            $('#log_searchDiv .searchContent').html(contents.join('')).on('click', 'em', function () {
                let parent = $(this).parent();
                let id = parent[0].id;
                switch (id) {
                    case 'logModuleShow':
                        $('#advanceSearchModule option').removeAttr('selected');
                        break;
                    case 'logTimeShow':
                        startTime = endTime = '';
                        $('#advanceSearchDateRangePicker').val('');
                        break;
                }
                advanceSearchSubmit();
            });
        } else {
            $('.advancedSearchShow').addClass('__hide').hide();
        }
        $('#logDownloadTable').bootstrapTable('filterBy', filters);
    };

    const initLogTable = () => {
        Metronic.blockUI({target: '#log-download', animate: true});
        pAjaxRequest({}, `/api/v1/agents/${$('#clientUUID').val()}/log`, 'GET', res => {
            Metronic.unblockUI('#log-download');
            if (!res.success) {
                UIToastr.showWarning(LANG.UI_CLIENT_LOG_DOWNLOAD, res.message);
                return;
            }
            setLogTable(res.data.rows);
        });
    };

    const getLogColumns = () => {
        return [{
            checkbox: true,
            sortable: false,
            width: '2',
            widthUnit: '%',
        }, {
            title: LANG.UI_CLIENT_LOG_NAME,
            field: 'log_name',
            sortable: true,
            width: '20',
            widthUnit: '%',
        }, {
            title: LANG.UI_CLIENT_LOG_MODULE,
            field: 'log_module_des',
            sortable: true,
            width: '8',
            widthUnit: '%',
        }, {
            title: LANG.UI_CLIENT_SUB_LOG_MODULE,
            field: 'sub_module_name',
            sortable: true,
            width: '10',
            widthUnit: '%',
        }, {
            title: LANG.UI_CLIENT_LOG_TIME,
            field: 'log_time',
            sortable: true,
            width: '10',
            widthUnit: '%',
        }, {
            title: LANG.UI_CLIENT_LOG_PATH,
            field: 'log_path',
            sortable: true,
            width: '30',
            widthUnit: '%',
        }];
    };

    const initLogDownloadTableHeight = () => {
        let toolbarHeight = 57;
        let paginationHeight = 52;
        let tabTitleHeight = 48;
        let navTitleHeight = 46;
        // page-content有40px的内边距
        // portlet-body有10px的内边距
        // tab-content有20px的外边距离
        let otherHeight = 40 + 10 + 20;
        // 面包屑导航
        let breadHeight = 38;
        // 高级搜索高度
        let advancedTag = $('.advancedSearchShow');
        let advanceHeight = Math.ceil(advancedTag.outerHeight(true));
        if (advancedTag.hasClass('__hide')) {
            advanceHeight = 0;
        }
        let height = window.innerHeight - toolbarHeight - paginationHeight - tabTitleHeight
            - navTitleHeight - otherHeight - breadHeight - advanceHeight;

        $("#log-download .fixed-table-body").css('height', height);
    };

    /**
     * 自定义按钮
     */
    const customTool = () => {
        let afterAdvance = ``;
        if (CONF.PERMISSION_ARR.includes('p_agent_log_download')) {
            afterAdvance += `
            <button class="btn btn-primary b-btn btn-table ms-8" id="downloadAgentLog">
                <i class="log_download"></i>
            </button>
            `;
        }
        let beforeAdvance = `
        <button type="button" class="btn btn-primary adv_btn brr2 p-lr8 agentLogAdvance">
            <i class="adv-search"></i>
            ${LANG.UI_JOB_SEARCH_EXP}
        </button>
        `;
        return {beforeAdvance, afterAdvance};
    };

    const setLogTable = (result) => {
        for (const index in result) {
            result[index]['log_module_des'] = CONF.MODULE_TYPE_DES[result[index]['log_module']];
        }
        // 清除分页缓存
        window.sessionStorage.removeItem('logDownloadTable_pageRecord');
        $('#logDownloadTable').bootstrapTable('destroy').baseTableConfig().init({
            buttonsToolbar: '.vin_btnLogToolbar', // 自定义按钮工具栏class
            rightToolbarClass: 'rightTool',
            tableContentWrapper: '#vin_client_log_toolbar',
            toolbarId: '#vin_client_log_toolbar',
            vin_toolbar: '#vin_client_log_toolbar',
            // 搜索
            searchInput: true,
            searchOnEnterKey: false,
            placeholder: LANG.UI_CLIENT_LOG_NAME_SEARCH,
            searchClass: 'clientLogSearch',
            searchSelector: '.clientLogSearch',
            showSearchButton: true,
            searchAlign: 'right',
            // 排序
            sortName: 'log_module_des',
            sortOrder: 'desc',
            showButtonText: false,
            clickToSelect: true,
            // 分页
            pagination: true,
            pageSize: 20,
            sidePagination: 'client',
            pageList: [10, 20, 50, 100, 150, 200],
            showExport: false,
            showColumns: true,
            onResetView: initLogDownloadTableHeight,
            /**
             * @param {Array<Object>} data
             * @param {String} data[].log_time
             * @param {String} data[].log_name
             * @param text
             * @param filters
             * @returns {*}
             */
            customSearch: (data, text, filters) => {  // 这里会影响到高级搜索
                return data.filter(row => {
                    let flag = true;
                    if (typeof filters !== 'undefined') {
                        if (typeof filters.log_module !== 'undefined') {
                            let logModule = parseInt(filters.log_module);
                            if (logModule === 16) {
                                flag = flag && [8, 9, 16].includes(parseInt(row.log_module));
                            } else {
                                flag = flag && row.log_module === filters.log_module;
                            }
                        }
                        if (typeof filters.start_time !== 'undefined' && typeof filters.end_time !== 'undefined') {
                            let startTime = Date.parse(filters.start_time);
                            let endTime = Date.parse(filters.end_time);
                            let logTime = Date.parse(row.log_time);
                            flag = flag && startTime <= logTime && logTime <= endTime;
                        }
                    }
                    if (text) {
                        flag = flag && row.log_name.indexOf(text) > -1
                    }
                    return flag;
                });
            },
            customTool: customTool(),
            columns: getLogColumns(),
            data: result,
        });
    };

    const initAdvanceSearch = () => {
        initDatetimePicker();
        initModuleSelect();
        initDbTypeSelect();
    };

    const initDatetimePicker = () => {
        //初始化日期时间选择控件
        const dataRangePicker = $('#advanceSearchDateRangePicker');
        dataRangePicker.daterangepicker({
            "autoUpdateInput": false,                                           //是否自动填充input
            "startDate": moment().subtract(6, 'days').startOf('day'),           //默认开始时间
            "endDate": moment({hour: 23, minute: 59}),                                              //默认结束时间
            "maxDate": moment({hour: 23, minute: 59}),                                              //最大可用时间
            timePicker: true,                                                  //是否显示时间,时分
            timePicker24Hour: true,                                            //是否是24小时制
            "alwaysShowCalendars": true,                                        //是否总是显示日期选择
            timePickerSeconds: true,
            drops: 'auto',
            "ranges": DateRangePickerLocales.getRangesConfig(CONF.LANGUAGE),    //根据语言定义默认ranges,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
            "locale": DateRangePickerLocales.getLocalConfig(CONF.LANGUAGE),     //根据语言定义默认local,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
        }, function (start, end, label) {
//          console.log('New date range selected: ' + start.format('YYYY-MM-DD HH:mm:ss') + ' to ' + end.format('YYYY-MM-DD HH:mm:ss') + ' predefined range: ' + label);
        });

        //如果不是选择后自动填充input(autoUpdateInput:true),需要监听下面两个方法apply.daterangepicker和cancel.daterangepicker
        dataRangePicker.on('apply.daterangepicker', function (ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD HH:mm') + ' - ' + picker.endDate.format('YYYY-MM-DD HH:mm'));
            startTime = picker.startDate.format('YYYY-MM-DD HH:mm:ss');
            endTime = picker.endDate.format('YYYY-MM-DD HH:mm:ss');
            timePickTypeDes = picker.chosenLabel;
        });

        dataRangePicker.on('cancel.daterangepicker', function () {
            $(this).val('');
            startTime = '';
            endTime = '';
            timePickTypeDes = '';
        });

        //input右侧的图标事件
        $('.daterangepickerdiv i').click(function () {
            $(this).parent().find('input').click();
        });
    };

    const initModuleSelect = () => {
        let options = `<option value="0"> ${LANG.UI_PUBLIC_ALL} </option>`
        for (let moduleType in CONF.MODULE_TYPE_DES) {
            moduleType = parseInt(moduleType);
            if (moduleType === 0 || moduleType === 8 || moduleType === 9) {
                continue;
            }
            options += `<option value="${moduleType}"> ${CONF.MODULE_TYPE_DES[moduleType]} </option> `
        }
        $('#advanceSearchModule').html(options);
    };

    const initDbTypeSelect = () => {
        let options = ` <option value="0"> ${LANG.UI_PUBLIC_ALL} </option> `
        for (const dbType in CONF.DB_DES) {
            if (parseInt(dbType) === 0) {
                continue;
            }
            options += ` <option value="${dbType}"> ${CONF.DB_DES[dbType]} </option> `
        }
        $('#advanceSearchDbType').html(options).parents('div.form-group').hide();
    };

    return {
        init: () => {
            initListener();
            initLogTable();
            initAdvanceSearch();
        },
    };
}();

jQuery(document).ready(function () {
    LogDownload.init();
});
