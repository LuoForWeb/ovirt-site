var DriverManager = (() => {
    let changeDriverRowHeightFlag = false;
    let dropzone = null;
    /**
     * 上传驱动的信息
     * @type {null|{date: '', driver_name: '', hardware_type: '', provider: '', upload_path: '', version: ''}}
     */
    let driverFileInfo = null;
    const OS_TYPE_ENUM = {
        WINDOWS: 2,
        LINUX: 1,
    };
    const OS_TYPE_MAP = {
        2: 'Windows',
        1: 'Linux',
    };
    const OS_TYPE_IMG_MAP = {
        2: './img/platform/windows-24.svg',
        1: './img/platform/linux-24.svg',
    };

    /**
     * 初始化驱动上传组件
     */
    const initDriverUpload = () => {
        $('.upload-driver-result-div').hide();
        dropzone = new Dropzone('#uploadDriverResult', {
            url: `/api/v1/drivers/file`,
            paramName: 'files',
            method: 'POST',
            maxFiles: 1,
            maxFilesize: 10, // MB
            addRemoveLinks: true,
            dictFileTooBig: LANG.UI_DRIVER_UPLOAD_FILE_TOO_BIG,
            dictInvalidFileType: LANG.UI_DRIVER_UPLOAD_FILE_TYPE_ERROR,
            headers: {
                'x-api-version': '1.0-rev0'
            },
            clickable: false,
            acceptedFiles: '.zip',
            autoProcessQueue: true,
            autoQueue: true,
            sending: (file, xhr, data) => {
                data.append('os_type', parseInt($('#driverOsType .radio-group__item.active').attr('value')));
                $('#uploadDriverResult .dz-filename span[data-dz-name]').html(LANG.UI_DRIVER_UPLOADING);
                $('#uploadDriverResult .dz-details .dz-size').hide();
            },
            removedfile: function (file) {
                $('.upload-driver-result-div').hide();
                driverFileInfo = null;
                $('#driverName').val('');
                $('#driverHardwareType').val('');
                $('#driverProvider').val('');
                $('#driverDate').val('');
                $('#driverVersion').val('');
                let _ref;
                if (file.previewElement) {
                    if ((_ref = file.previewElement) != null) {
                        _ref.parentNode.removeChild(file.previewElement);
                    }
                }
                return this._updateMaxFilesReachedClass();
            },
            success: function (file) {
                let alertDanger = $('#addDriverForm .alert.alert-danger');
                alertDanger.hide();
                let xhr = file.xhr;
                let response = JSON.parse(xhr.responseText);
                if (response.success) {
                    driverFileInfo = response.data;
                    $('#uploadDriverResult .dz-filename span[data-dz-name]').html(file.upload.filename);
                    $('#uploadDriverResult .dz-details .dz-size').show();
                    if (file.previewElement) {
                        return file.previewElement.classList.add("dz-success");
                    }
                } else {
                    driverFileInfo = null;
                    alertDanger.show();
                    $('#addDriverForm .alert.alert-danger .message').html(response.message);
                    this.removeAllFiles();
                }
            },
            canceled: () => {
                driverFileInfo = null;
            },
            complete: function (file) {
                if (driverFileInfo !== null) {
                    $('#driverName').val(driverFileInfo.driver_name);
                    $('#driverHardwareType').val(driverFileInfo.hardware_type);
                    $('#driverProvider').val(driverFileInfo.provider);
                    $('#driverDate').val(driverFileInfo.date);
                    $('#driverVersion').val(driverFileInfo.version);
                    if (file.previewElement) {
                        file.previewElement.classList.add("dz-complete");
                    }
                    if (file._removeLink) {
                        return file._removeLink.textContent = this.options.dictRemoveFile;
                    }
                }
            },
        });
    };

    /**
     * 事件监听
     */
    const initListener = () => {
        // 表格事件 - 改变高度
        $('#driverContent').on('click', '.change_height', changeDriverRowHeight)
            .on('click', '.vin_toolbar .search button.search-btn', () => {  // 搜索
                $('#driverTable').bootstrapTable('refresh');
            })
            .on('click', '#addDriver', clickAddDriverBtn)
            .on('click', '#deleteDriver', clickDeleteDriverBtn);

        // 改变适用操作系统
        $('#driverOsType').on('change', changeOsType);

        // 上传驱动
        $('#uploadDriverBtn').on('click', uploadDriver);

        // 添加驱动事件 - 取消按钮
        $('#addDriverDrawer .drawer-footer button.cancel').on('click', cancelAddDriverDrawer);
        // 添加驱动事件 - 确定按钮
        $('#addDriverDrawer .drawer-footer button.btn-confirm').on('click', confirmAddDriverDrawer);
        // 驱动详情事件 - 取消/确定按钮
        $('#driverDetailDrawer .drawer-footer button.cancel, #driverDetailDrawer .drawer-footer button.btn-confirm').on('click', hideDriverDetail);
    };

    //////////////////// 开始-注册事件 ////////////////////

    /**
     * 设置驱动管理高度
     */
    const setDriverRowHeight = () => {
        let rows = $('#driverTable').bootstrapTable('getData');
        if (!rows.length) {
            return;
        }
        if (changeDriverRowHeightFlag) {
            $('#driverTable>tbody>tr>td').css({
                'padding-top': '15.25px',
                'padding-bottom': '15.25px'
            });
        } else {
            $('#driverTable>tbody>tr>td').css({
                'padding-top': '4.25px',
                'padding-bottom': '4.25px'
            });
        }
    };

    /**
     * 改变表格的高度
     */
    const changeDriverRowHeight = () => {
        if (!changeDriverRowHeightFlag) {
            changeDriverRowHeightFlag = true;
            $('#vinDriverToolbar .change_height i').addClass('icon-auto-height2');
        } else {
            changeDriverRowHeightFlag = false;
            $('#vinDriverToolbar .change_height i').removeClass('icon-auto-height2');
        }
        setDriverRowHeight();
    };

    /**
     * 添加驱动
     */
    const clickAddDriverBtn = () => {
        $('#addDriverDrawer').drawer('show');
        // 清除旧数据
        dropzone.removeAllFiles();
        $(`#driverOsType .radio-group__item[value="${OS_TYPE_ENUM.WINDOWS}"]`).trigger('click');
        $('#driverRemark').val('');
        $('#addDriverForm .alert.alert-danger').hide();
    };

    /**
     * 删除驱动
     */
    const clickDeleteDriverBtn = () => {
        let rows = $('#driverTable').bootstrapTable('getSelections');
        if (!rows.length) {
            UIToastr.showWarning(LANG.UI_DRIVER_DELETE, LANG.UI_DRIVER_DELETE_EMPTY);
            return;
        }
        showDeleteDriverDialog(rows);
    };

    /**
     * 显示删除驱动的确认窗口
     * @param {Array<{driver_uuid: '', driver_name: ''}>} rows
     */
    const showDeleteDriverDialog = (rows) => {
        let title = `
        <div>
            <i class="viconfont vicon-a-Deleteshanchu1"></i>
            <span class="pl2">${LANG.UI_DRIVER_DELETE}</span>
        </div>
        `;
        let message = `
        <div class="delete-tips-div">
            <div class="delete-tips__wrapper">
                <div class="delete-tips">
                    <div class="delete-tips__icon">
                        <i class="viconfont vicon-a-Close-oneguanbi"></i>
                    </div>
                    <div class="delete-tips__text">
                        ${LANG.UI_DRIVER_DELETE_TIPS.replace('%s', rows.map(row => row.driver_name).join('、'))}
                    </div>
                </div>
            </div>
        </div>
        `;
        bootbox.confirm({
            title,
            message,
            callback: debounce(result => {
                if (!result) {
                    return;
                }
                deleteDriver(rows.map(row => row.driver_uuid));
            }, 300),
        });
    };

    /**
     * 删除驱动
     * @param driverUuidList
     */
    const deleteDriver = driverUuidList => {
        let reqData = {
            driver_uuid_list: driverUuidList,
        };
        Metronic.blockUI({ target: '#driverContent', animate: true });
        pAjaxRequest(reqData, `/api/v1/drivers`, 'DELETE', res => {
            Metronic.unblockUI('#driverContent');
            if (!res.success) {
                UIToastr.showError(LANG.UI_DRIVER_DELETE, res.message);
                return;
            }
            UIToastr.showSuccess(LANG.UI_DRIVER_DELETE, res.message);
            // 刷新驱动列表
            $('#driverTable').bootstrapTable('refresh');
        });
    };

    /**
     * 改变适用操作系统
     */
    const changeOsType = (ev, oldValue, newValue) => {
        let driverOsType = parseInt(newValue);
        if (driverOsType === OS_TYPE_ENUM.LINUX) {
            $('.driverHardwareTypeDiv').hide();
            $('.driverDateDiv').hide();
        } else {
            $('.driverHardwareTypeDiv').show();
            $('.driverDateDiv').show();
        }
        dropzone.removeAllFiles();
    };

    /**
     * 上传驱动事件
     */
    const uploadDriver = () => {
        let fileInput = document.createElement('input');
        fileInput.type = 'file';
        fileInput.accept = '.zip';
        fileInput.multiple = false;
        fileInput.click();

        fileInput.onchange = function () {
            let files = this.files;
            if (files.length === 1) {
                let file = files[0];
                dropzone.removeAllFiles();
                $('.upload-driver-result-div').show();
                dropzone.addFile(file);
            }
        };
    };

    /**
     * 取消添加驱动
     */
    const cancelAddDriverDrawer = () => {
        $('#addDriverDrawer').drawer('hide');
    };

    /**
     * 确定添加驱动
     */
    const confirmAddDriverDrawer = () => {
        let alertDanger = $('#addDriverForm .alert.alert-danger');
        alertDanger.hide();
        if (driverFileInfo === null) {
            alertDanger.show();
            $('#addDriverForm .alert.alert-danger .message').html(LANG.UI_DRIVER_UPLOAD_FILE_NOT_EXIST);
            return;
        }
        let driverOsType = parseInt($('#driverOsType .radio-group__item.active').attr('value'));
        let driverRemark = $('#driverRemark').val();

        let reqData = {
            os_type: driverOsType,
            remark: driverRemark,
            upload_path: driverFileInfo.upload_path,
        };

        // 添加驱动
        Metronic.blockUI({ target: '#addDriverDrawer', animate: true });
        pAjaxRequest(reqData, `/api/v1/drivers`, 'POST', res => {
            Metronic.unblockUI('#addDriverDrawer');
            if (!res.success) {
                $('#addDriverForm .alert.alert-danger').show();
                $('#addDriverForm .alert.alert-danger .message').html(res.message);
                return;
            }
            UIToastr.showSuccess(LANG.UI_DRIVER_ADD, res.message);
            $('#addDriverDrawer').drawer('hide');
            // 刷新驱动列表
            $('#driverTable').bootstrapTable('refresh');
        });
    };

    /**
     * 隐藏驱动详情
     */
    const hideDriverDetail = () => {
        $('#driverDetailDrawer').drawer('hide');
    };

    //////////////////// 结束-注册事件 ////////////////////

    //////////////////// 开始-驱动表格 ////////////////////

    /**
     * 初始化驱动详情表格
     * @param detailList
     */
    const initDriverDetailTable = (detailList) => {
        // 清除分页缓存
        window.sessionStorage.removeItem('driverDetailTable_pageRecord');
        $('#driverDetailTable').bootstrapTable('destroy').baseTableConfig().init({
            toolbarId: '#vin_driver_detial_toolbar',  // 占位
            vin_toolbar: '#vin_driver_detial_toolbar',
            pagination: true,
            sidePagination: 'client',
            columns: [{
                title: LANG.UI_DRIVER_DETAIL_TABLE_DESCRIPTION,
                field: 'description',
                width: '90',
                widthUnits: 'px',
                formatter: formatValue,
            }, {
                title: LANG.UI_DRIVER_DETAIL_TABLE_ARCH,
                field: 'arch',
                width: '120',
                widthUnits: 'px',
                formatter: formatValue,
            }, {
                title: LANG.UI_DRIVER_DETAIL_TABLE_MIN_VER,
                field: 'min_ver',
                width: '135',
                widthUnits: 'px',
                formatter: formatValue,
            }, {
                title: LANG.UI_DRIVER_DETAIL_TABLE_MAX_VER,
                field: 'max_ver',
                width: '135',
                widthUnits: 'px',
                formatter: formatValue,
            }, {
                title: LANG.UI_DRIVER_DETAIL_TABLE_SPECIFIED_VERS,
                field: 'specified_vers',
                sortable: false,
                formatter: value => {
                    return value.join(', ');
                },
                width: '150',
                widthUnits: 'px',
                formatter: formatValue,
            }, {
                title: LANG.UI_DRIVER_DETAIL_TABLE_HW_OR_COM_ID,
                field: 'hw_or_com_id',
                sortable: false,
                width: '130',
                widthUnits: 'px',
                formatter: formatValue,
            }],
            data: detailList,
        });
    };

    /**
     * 显示驱动详情
     * @param e
     * @param value
     * @param {{driver_uuid: ''}} row
     */
    const showDriverDetail = (e, value, row) => {
        $('#driverDetailDrawer').drawer('show');
        Metronic.blockUI({ target: '#driverDetailDrawer', animate: true });
        pAjaxRequest({}, `/api/v1/drivers/${row.driver_uuid}`, 'GET', res => {
            Metronic.unblockUI('#driverDetailDrawer');
            if (!res.success) {
                UIToastr.showError(LANG.UI_DRIVER_DETAIL, res.message);
                return;
            }
            /**
             * @type {{driver_name: '', os_type: '', version: '', details: []}}
             */
            let driverInfo = res.data;
            $('#detailDriverName').html(driverInfo.driver_name);
            $('#detailOsImg').attr('src', OS_TYPE_IMG_MAP[parseInt(driverInfo.os_type)]);
            $('#detailOsName').html(OS_TYPE_MAP[parseInt(driverInfo.os_type)]);
            $('#detailDriverVersion').html(driverInfo.version);
            initDriverDetailTable(driverInfo.details);
        });
    };

    /**
     * 初始化操作事件
     */
    const initDriverBtnOp = () => {
        return {
            'click .driverDetail': showDriverDetail,
        };
    };

    /**
     * 格式化表格值
     * @param {*} value 
     */
    const formatValue = value => {
        if (!value.length) {
            return '--';
        }
        return `<span title="${value}">${value}</span>`;
    };

    /**
     * 驱动操作
     */
    const driverOpFormatter = () => {
        return `<button class="btn btn-operate-primary driverDetail">${LANG.UI_DRIVER_TABLE_BTN_DETAIL}</button>`;
    };

    /**
     * 定义表格列
     */
    const getDriverTableColumns = () => {
        return [{
            checkbox: true,
            width: '2',
            widthUnit: '%',
            sortable: false,
            forceHide: true, //隐藏掉导出该列数据
        }, {
            title: LANG.UI_DRIVER_TABLE_NAME,
            field: 'driver_name',
            width: '15',
            widthUnit: '%',
            formatter: formatValue,
        }, {
            title: LANG.UI_DRIVER_TABLE_TYPE,
            field: 'hardware_type',
            width: '12',
            widthUnit: '%',
            formatter: formatValue,
        }, {
            title: LANG.UI_DRIVER_TABLE_PROVIDER,
            field: 'provider',
            width: '15',
            widthUnit: '%',
            formatter: formatValue,
        }, {
            title: LANG.UI_DRIVER_TABLE_DATE,
            field: 'date',
            width: '8',
            widthUnit: '%',
            formatter: formatValue,
        }, {
            title: LANG.UI_DRIVER_TABLE_VERSION,
            field: 'version',
            width: '8',
            widthUnit: '%',
            formatter: formatValue,
        }, {
            title: LANG.UI_DRIVER_TABLE_CREATE_TIME,
            field: 'create_time',
            width: '10',
            widthUnit: '%',
            formatter: value => {
                if (!value.length) {
                    return value;
                }
                value = new Date(value);
                const year = value.getFullYear();
                const month = String(value.getMonth() + 1).padStart(2, '0');
                const day = String(value.getDate()).padStart(2, '0');
                const hour = String(value.getHours()).padStart(2, '0');
                const minute = String(value.getMinutes()).padStart(2, '0');
                const second = String(value.getSeconds()).padStart(2, '0');
                let datetimeStr = `${year}-${month}-${day} ${hour}:${minute}:${second}`;
                return `<span title="${datetimeStr}">${datetimeStr}</span>`;
            },
        }, {
            title: LANG.UI_DRIVER_TABLE_REMARK,
            field: 'remark',
            sortable: false,
            width: '10',
            widthUnit: '%',
            formatter: formatValue,
        }, {
            title: LANG.UI_PUBLIC_OPERATION,
            width: '5',
            widthUnit: '%',
            sortable: false,
            events: initDriverBtnOp(),
            clickToSelect: false,
            formatter: driverOpFormatter,
            opButton: true,
        }];
    };

    /**
     * 自定义表格按钮
     */
    const driverCustomTool = () => {
        let beforeInput = `<div class="btn-group" style="margin: 0">`;
        // 删除驱动
        if (CONF.PERMISSION_ARR.includes('p_driver_manager_delete')) {
            beforeInput += `
            <button type="button" id="deleteDriver" class="b-btn brr2 mr12 table-toolbar-btn">
                <i class="icon-gray-delete"></i>
            </button>
            `;
        }
        beforeInput += `</div>`;

        let afterInput = `<div class="btn-group" style="margin: 0">`;
        // 添加驱动
        if (CONF.PERMISSION_ARR.includes('p_driver_manager_add')) {
            afterInput += `
            <button class="dropdown-toggle btn-font flex_center btn-title p-lr8 btn table-toolbar-btn" id="addDriver"
                style="width: auto; height: 34px; border: 0">
                <i class="viconfont vicon-biaogetianjia"></i>
                <span class="pl2">${LANG.UI_PUBLIC_ADD}</span>
            </button>
            `;
        }
        afterInput += `</div>`;

        let beforeAdvance = ``;
        let afterAdvance = ``;
        return { beforeInput, afterInput, beforeAdvance, afterAdvance };
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

    /**
     * 选择了每一行
     */
    const driverRowCheck = () => {
        checkEvent('#driverTable', '#deleteDriver');
    };

    /**
     * 初始化驱动表格高度
     */
    const initDriverTableHeight = () => {
        let toolbarHeight = Math.ceil($('#driverContent .vin_toolbar').outerHeight(true));
        let paginationHeight = Math.ceil($('#driverContent .fixed-table-pagination').outerHeight(true));
        let tabTitleHeight = Math.ceil($('#driverContent .portlet-title').outerHeight(true));
        // 顶部导航栏
        let navTitleHeight = 46;
        // page-content有40px的内边距
        // portlet-body有20px的内边距 20px的外边距
        let otherHeight = 40 + 40;
        let height = window.innerHeight - toolbarHeight - paginationHeight - navTitleHeight - tabTitleHeight - otherHeight;

        $("#driverContent .fixed-table-body").css({
            "height": height
        });
    };

    /**
     * 获取请求的参数
     * @return {Object}
     */
    const getQueryParams = () => {
        let params = {};
        let searchValue = $('#vinDriverToolbar .driverSearch').val().trim();
        if (searchValue.length) {
            params['name'] = searchValue;
        }
        return params;
    };

    /**
     * 获取驱动表格选项
     */
    const getDriverTableOption = () => {
        return {
            vin_url: '/api/v1/drivers',
            vin_params: getQueryParams,
            vin_method: 'get',
            toolbarId: '#vinDriverToolbar',
            vin_toolbar: '.vin_toolbar',
            // 搜索相关
            searchInput: true,
            placeholder: LANG.UI_DRIVER_TABLE_SEARCH_PLACEHOLDER,
            searchClass: 'driverSearch',
            searchSelector: '.driverSearch',
            showSearchButton: true,
            // 排序
            sortName: 'create_time',
            sortOrder: 'desc',
            // 隐藏行
            hideColumns: '',
            clickToSelect: true,
            pagination: true,
            resizable: true,
            showExport: true,
            changeHeightBtn: true,
            onResetView: initDriverTableHeight,
            onRefresh: () => {
                $("#driverTable").bootstrapTable('hideLoading');
            },
            onPostBody: () => {
                driverRowCheck();
                setDriverRowHeight();
            },
            onCheck: driverRowCheck,
            onUncheck: driverRowCheck,
            onCheckAll: driverRowCheck,
            onUncheckAll: driverRowCheck,
            customTool: driverCustomTool(),
            columns: getDriverTableColumns(),
        };
    };

    /**
     * 初始化驱动表格
     */
    const initDriverTable = () => {
        window.sessionStorage.removeItem('driverTable_pageRecord');
        $('#driverTable').bootstrapTable('destroy').baseTableConfig().init(getDriverTableOption());
    };

    //////////////////// 结束-驱动表格 ////////////////////

    return {
        init: () => {
            initDriverUpload();
            initListener();
            initDriverTable();
        },
    }
})();

jQuery(document).ready(() => {
    DriverManager.init();
});
