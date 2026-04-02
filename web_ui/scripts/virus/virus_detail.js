//客户端分组管理
var VirusDetail = function () {
    let lastApply = '';
    let dropzone = null;
    let driverFileInfo = null;
    let authorizationFileInfo = null;
    let authorizationFileDropZone = null;
    let virusName = '';
    let virusPath = ''
    let virusAuthorizationFileName = '';
    let virusAuthorizationFilePath = '';
    let changeVirusDetailRowHeightFlag = false;
    const VIRUS_TYPE_ENUM = {
        kav: 1,
        clamav: 2,
    };
    const VIRUS_AUTHORIZED_FLAG_ENUM = {
        unknown: 0, // 未知
        authorized: 1,  // 已授权
        unauthorized: 2,  // 未授权
        expired: 3,  // 授权已过期
    };
    const VIRUS_OP_TYPE_ENUM = {
        unknown: 0, // 未知
        set_default: 1,  // 设置默认
        refresh: 3,  // 刷新
    };

    /**
     * 获取病毒库详情表的查询参数
     * @returns {{type: (*|string|jQuery)}}
     */
    const getQueryParams = function () {
        let params = {
            type: $('#virus_type').val(),
        };
        // 按主机名/IP/别名搜索
        let searchValue = $('#virusDetail__wrapper .virusDetailSearch').val().trim();
        if (searchValue) {
            params.name = searchValue;
        }
        return params;
    };

    /**
     * 格式化授权状态
     * @param value
     */
    const formatAuthorizedFlag = (value) => {
        let strategyType;
        switch (value) {
            case VIRUS_AUTHORIZED_FLAG_ENUM.authorized:
                strategyType = LANG.UI_CLOUD_PLATFORM_AUTHORIZED;
                break;
            case VIRUS_AUTHORIZED_FLAG_ENUM.unauthorized:
                strategyType = LANG.UI_CLOUD_PLATFORM_UNAUTHORIZED;
                break;
            case VIRUS_AUTHORIZED_FLAG_ENUM.expired:
                strategyType = LANG.UI_BACKUP_DATA_LABEL_EXPIRED;
                break;
            default:
                strategyType = LANG.UI_PUBLIC_UNKNOWN
                break;
        }
        return `<span title="${strategyType}">${strategyType}</span>`;
    }

    /**
     * 格式化授权过期时间
     * @param value
     * @param row
     */
    const formatAuthorizedExpireTime = (value, row) => {
        let virusType = parseInt($('#virus_type').val());
        if (virusType !== VIRUS_TYPE_ENUM.kav) {
            return `----`;
        }
        const authorizedFlag = parseInt(row.authorized_flag);
        if (
            authorizedFlag !== VIRUS_AUTHORIZED_FLAG_ENUM.authorized &&
            authorizedFlag !== VIRUS_AUTHORIZED_FLAG_ENUM.expired
        ) {
            return `----`;
        }
        return `<span title="${value}">${value}</span>`
    };

    /**
     * 获取病毒库详情表的操作事件
     * @returns {Object}
     */
    const getVirusDetailTableOperationEvent = () => {
        return {
            'click .apply': function (event, value, row, index) {
                initSetDefaultVirus(row);
            },

            'click .updata': function (event, value, row, index) {
                initRefreshVirus(row);
            },
            'click .renew': function (event, value, row, index) {
                initRenew(row);
            },
            'click .upload-authorization-file': function (event, value, row, index) {
                initUploadAuthorizationFile(row);
            }
        };
    };

    /**
     * 获取病毒库详情表的操作列
     * @param value
     * @param row
     * @param index
     * @returns {string}
     */
    const getVirusDetailTableOperation = (value, row, index) => {
        if (!CONF.PERMISSION_ARR.includes('p_virus_operate')) {
            return ``;
        }
        let authorizedFlag = parseInt(row.authorized_flag);
        let button = '<div class="btn-group dropdown-wrapper">';
        if (index > 5) {
            button = '<div class="btn-group dropup">';
        }

        button += '<button style="line-height:16px" type="button" class="btn btn-dropdown-operate dropdown-toggle" data-toggle="dropdown" ' +
            'data-hover="dropdown" data-delay="1000" data-close-others="true">' +
            '' + LANG.UI_PUBLIC_OPERATION + ' <i class="fa fa-angle-down"></i>' +
            '</button>' +
            '<ul class="dropdown-menu min-width100" role="menu">';

        if (row.op_list.includes(1)) {
            button += `
            <li class="apply">
                <button class="btn dropdown-menu__item me-0" type="button" ${authorizedFlag !== VIRUS_AUTHORIZED_FLAG_ENUM.authorized ? 'disabled' : ''}>
                    <i class="viconfont vicon-a-Group1000002950 me-4 mt-0"></i>
                    <span>${LANG.UI_VIRUS_APPLY}</span>
                </button>
            </li>
            `;
        }
        if (row.op_list.includes(2)) {
            button += `
            <li class="updata">
                <button class="btn dropdown-menu__item me-0" type="button" ${authorizedFlag !== VIRUS_AUTHORIZED_FLAG_ENUM.authorized ? 'disabled' : ''}>
                    <i class="viconfont vicon-a-Group2 me-4 mt-0"></i>
                    <span>${LANG.UI_BACKUP_TREE_REFRESH}</span>
                </button>
            </li>
            `;
        }
        if (row.op_list.includes(3)) {
            button += `
            <li class="renew">
                <button class="btn dropdown-menu__item me-0" type="button" ${authorizedFlag !== VIRUS_AUTHORIZED_FLAG_ENUM.authorized ? 'disabled' : ''}>
                    <i class="viconfont vicon-shangchuan me-4 mt-0"></i>
                    <span>${LANG.UI_VIRUS_GENXIN_VIRUS}</span>
                </button>
            </li>
            `;
        }
        if (row.op_list.includes(4)) {
            button += `
            <li class="upload-authorization-file">
                <button class="btn dropdown-menu__item me-0" type="button">
                    <i class="viconfont vicon-gongnengshouquan me-4 mt-0"></i>
                    <span>${LANG.UI_VIRUS_UPLOAD_AUTHORIZATION_FILE}</span>
                </button>
            </li>
            `;
        }
        button += '</ul></div>';
        return button;
    };

    /**
     * 获取病毒库详情表的列定义
     */
    const getVirusDetailTableColumns = () => {
        let virusType = parseInt($('#virus_type').val());
        let columns = [{
            checkbox: true,
            sortable: false,
            width: '2',
            widthUnit: '%',
            forceHide: true, //隐藏掉导出该列数据
        }, {
            field: 'name',
            title: LANG.UI_VIRUS_NAME,
            width: '10',
            widthUnit: '%',
            sortable: false,
            align: 'center',
        }, {
            field: 'vendor',
            title: LANG.UI_VIRUS_VENDOR,
            width: '10',
            widthUnit: '%',
            sortable: false,
            align: 'center',
        }, {
            field: 'version',
            title: virusType === VIRUS_TYPE_ENUM.clamav ? LANG.UI_VIRUS_VERSION : LANG.UI_VIRUS_SDK_VERSION,
            width: '10',
            widthUnit: '%',
            sortable: true,
            align: 'center',
        }, {
            field: 'register_time',
            title: LANG.UI_PUBLIC_ADD_TIME,
            width: '15',
            widthUnit: '%',
            sortable: true,
            align: 'center',
        }, {
            field: 'last_update_time',
            title: LANG.UI_VIRUS_LAST_UPDATA_TIME,
            width: '15',
            widthUnit: '%',
            sortable: true,
            align: 'center',
        }, {
            field: 'authorized_flag',
            title: LANG.UI_VIRUS_AUTHORIZED_FLAG,
            width: '10',
            widthUnit: '%',
            sortable: true,
            align: 'center',
            formatter: formatAuthorizedFlag,
        }, {
            field: 'authorized_expire_time',
            title: LANG.UI_VIRUS_AUTHORIZED_EXPIRE_TIME,
            width: '10',
            widthUnit: '%',
            sortable: true,
            align: 'center',
            formatter: formatAuthorizedExpireTime,
        }, {
            field: 'description',
            title: LANG.UI_PUBLIC_DESCRIPTION,
            width: '5',
            widthUnit: '%',
            sortable: false,
            align: 'center',
            formatter: function (value) {
                if (!value) {
                    return '--';
                }
                return '<span title="' + value + '">' + value + '</span>';
            }
        }, {
            field: 'apply_status',
            title: LANG.UI_VIRUS_APPLY_STATUS,
            width: '8',
            widthUnit: '%',
            sortable: true,
            align: 'center',
            formatter: function (value, row) {
                let strategyType;
                if (parseInt(row.apply_status) !== 1) {
                    strategyType = '<span class="label label-sm label-default">' + LANG.UI_VIRUS_NOT_APPLY + '</span>';
                } else {
                    strategyType = '<span class="label label-sm label-success status-icon">' + LANG.UI_VIRUS_HAVE_APPLY + '</span>';
                    lastApply = row.name
                }
                return strategyType;
            }
        }, {
            title: LANG.UI_PUBLIC_OPERATION,
            width: '10',
            widthUnit: '%',
            forceHide: true, //隐藏掉导出该列数据
            formatter: getVirusDetailTableOperation,
            events: getVirusDetailTableOperationEvent(),
            opButton: true,
            clickToSelect: false, //不可通过点击行选中
            sortable: false, //默认可排序，禁用排序才写此项
        }];
        if (virusType === VIRUS_TYPE_ENUM.kav) {
            // let lastUpdateTimeIndex = -1
            // for (const index in columns) {
            //     if (columns[index].field === 'last_update_time') {
            //         lastUpdateTimeIndex = index;
            //         break;
            //     }
            // }
            // if (lastUpdateTimeIndex !== -1) {
            //     columns.splice(lastUpdateTimeIndex, 1);
            // }
        } else {
            let authorizedExpireTimeIndex = -1
            for (const index in columns) {
                if (columns[index].field === 'authorized_expire_time') {
                    authorizedExpireTimeIndex = index;
                    break;
                }
            }
            if (authorizedExpireTimeIndex !== -1) {
                columns.splice(authorizedExpireTimeIndex, 1);
            }
        }
        return columns;
    };

    var initDataTable = function () {
        $('#virus_table').baseTableConfig().init({
            vin_url: "/api/v1/virus",
            vin_method: "GET",
            vin_params: getQueryParams,
            searchOnEnterKey: true,
            searchInput: true,
            searchAlign: 'right',
            placeholder: LANG.UI_BACKUP_EXPORT_SEARCH_VIRUS,
            searchClass: 'virusDetailSearch',
            searchSelector: '.virusDetailSearch',
            // 排序
            sortName: 'last_update_time',
            sortOrder: 'desc',
            changeHeightBtn: true, //改变高度按钮
            pagination: true, //分页
            detailView: false,
            fullPage: true,
            PostBody: function () {
                setVirusRowHeight();
            },
            onCheck: function () {
                checkEvent('#report_table', '#delete');
            },
            onUncheck: function () {
                checkEvent('#report_table', '#delete');
            },
            onCheckAll: function () {
                checkEvent('#report_table', '#delete');
            },
            onUncheckAll: function () {
                checkEvent('#report_table', '#delete');
            },
            resizable: true, //可变宽度
            customTool: {
                // afterInput:'<button type = "button" id="add" class="dropdown-toggle btn-font btn-title btn table-toolbar-btn" style="margin-left: 12px" data-toggle="drawer">'+
                //     '<i class="viconfont vicon-ge_add_task mr5 c0FBF98"></i>'+ LANG.UI_BACKUP_FILE_ADD +
                //     '</button>'

            },
            columns: getVirusDetailTableColumns(),
        });
    };

    /**
     * 设置节点网络高度
     */
    const setVirusRowHeight = () => {
        let rows = $('#virus_table').bootstrapTable('getData');
        if (!rows.length) {
            return;
        }
        if (changeVirusDetailRowHeightFlag) {
            $('#virus_table>tbody>tr>td').css({
                'padding-top': '15.25px',
                'padding-bottom': '15.25px'
            });
        } else {
            $('#virus_table>tbody>tr>td').css({
                'padding-top': '4.25px',
                'padding-bottom': '4.25px'
            });
        }
    };

    /**
     * 修改病毒库高度
     */
    const changeVirusDetailRowHeight = () => {
        if (!changeVirusDetailRowHeightFlag) {
            changeVirusDetailRowHeightFlag = true;
            $('#virus_toolbar .change_height i').addClass('icon-auto-height2');
        } else {
            changeVirusDetailRowHeightFlag = false;
            $('#virus_toolbar .change_height i').removeClass('icon-auto-height2');
        }
        setVirusRowHeight();
    };

    const initListeners = function () {
        // $('#add').unbind('click').on('click', addShow);
        // $('#delete').on('click', deleteVirus);
        //添加确认
        // $('#add_submit').on('click', addSubmit);
        // $('#updataType').unbind('change').on('change', updataFile);
        $('#uploadDriverBtn').on('click', uploadDriver);
        $('#uploadAuthorizationFileBtn').on('click', uploadAuthorizationFile);
        // 更新病毒库确定
        $('#renew_submit').on('click', renewSubmit);
        // 上传病毒库授权文件
        $('#uploadAuthorizationFileSubmit').on('click', uploadAuthorizationFileSubmit);
        $('#virusDetail__wrapper')
            .on('click', 'button.search-btn', function () {
                $('#virus_table').bootstrapTable('refresh');
            })
            .on('click', '.change_height', changeVirusDetailRowHeight); // 修改资源池高度;

    }
    /**
     * 初始化上传病毒库组件
     */
    const initDriverUpload = () => {
        $('.upload-driver-result-div').hide();
        dropzone = new Dropzone('#uploadDriverResult', {
            url: `/api/v1/virus/file`,
            paramName: 'files',
            method: 'POST',
            addRemoveLinks: true,
            maxFilesize: 10000, // MB
            chunking: true, // 启用分段上传
            forceChunking: true,
            chunkSize: 10 * 1024 * 1024, // 每段的大小，这里设置为2MB
            headers: {
                'x-api-version': '1.0-rev0'
            },
            clickable: false,
            acceptedFiles: '.tar.gz',
            autoProcessQueue: true,
            autoQueue: true,
            sending: (file, xhr, data) => {
                data.append('lib_type', parseInt($('#drawer-updata').data('virus-type')));
                $('#uploadDriverResult .dz-filename span[data-dz-name]').html(LANG.UI_VIRUS_UPDATAING);
                $('#uploadDriverResult .dz-details .dz-size').hide();
            },
            removedfile: function (file) {
                $('.upload-driver-result-div').hide();
                driverFileInfo = null;
                virusName = '';
                virusPath = '';
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
                    virusName = '';
                    virusPath = '';
                    alertDanger.show();
                    $('#addDriverForm .alert.alert-danger .message').html(response.message);
                    this.removeAllFiles();
                }
            },
            canceled: () => {
                driverFileInfo = null;
                virusName = '';
                virusPath = '';
            },
            complete: function (file) {
                if (driverFileInfo !== null) {
                    virusName = driverFileInfo.name;
                    virusPath = driverFileInfo.path;
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
     * 初始化上传授权文件组件
     */
    const initAuthorizationFileUpload = () => {
        $('.upload-authorization-file-result-div').hide();
        authorizationFileDropZone = new Dropzone('#uploadAuthorizationFileResult', {
            url: `/api/v1/virus/file`,
            paramName: 'files',
            method: 'POST',
            addRemoveLinks: true,
            maxFilesize: 10000, // MB
            chunking: true, // 启用分段上传
            forceChunking: true,
            chunkSize: 10 * 1024 * 1024, // 每段的大小，这里设置为2MB
            headers: {
                'x-api-version': '1.0-rev0'
            },
            clickable: false,
            acceptedFiles: '.key',
            autoProcessQueue: true,
            autoQueue: true,
            sending: (file, xhr, data) => {
                $('#uploadAuthorizationFileResult .dz-filename span[data-dz-name]').html(LANG.UI_VIRUS_UPLOAD_AUTHORIZATION_FILE_UPLOADING);
                $('#uploadAuthorizationFileResult .dz-details .dz-size').hide();
            },
            removedfile: function (file) {
                $('.upload-authorization-file-result-div').hide();
                authorizationFileInfo = null;
                virusAuthorizationFileName = '';
                virusAuthorizationFilePath = '';
                let _ref;
                if (file.previewElement) {
                    if ((_ref = file.previewElement) != null) {
                        _ref.parentNode.removeChild(file.previewElement);
                    }
                }
                return this._updateMaxFilesReachedClass();
            },
            success: function (file) {
                let xhr = file.xhr;
                let response = JSON.parse(xhr.responseText);
                if (response.success) {
                    authorizationFileInfo = response.data;
                    $('#uploadAuthorizationFileResult .dz-filename span[data-dz-name]').html(file.upload.filename);
                    $('#uploadAuthorizationFileResult .dz-details .dz-size').show();
                    if (file.previewElement) {
                        return file.previewElement.classList.add("dz-success");
                    }
                } else {
                    authorizationFileInfo = null;
                    virusAuthorizationFileName = '';
                    virusAuthorizationFilePath = '';
                    this.removeAllFiles();
                }
            },
            canceled: () => {
                authorizationFileInfo = null;
                virusAuthorizationFileName = '';
                virusAuthorizationFilePath = '';
            },
            complete: function (file) {
                if (authorizationFileInfo !== null) {
                    virusAuthorizationFileName = authorizationFileInfo.name;
                    virusAuthorizationFilePath = authorizationFileInfo.path;
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
     * 设置默认病毒库
     * @param row
     */
    const initSetDefaultVirus = function (row) {
        var space = '';
        if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
            space = ' ';
        }
        bootbox.confirm({
            title: LANG.UI_VIRUS_APPLY_VIRUS_SET,
            message: LANG.UI_VIRUS_APPLY_VIRUS + space + row.name + space + LANG.UI_VIRUS_CANCLE_CURENT_VIRUS + space + lastApply + LANG.UI_VIRUS_JIXU,
            callback: debounce(function (r) {
                if (!r) {
                    return;
                }
                let reqData = {
                    manage_virus_lib_op: VIRUS_OP_TYPE_ENUM.set_default,
                    lib_type: row.type,
                };
                Metronic.blockUI({target: '#virus_table', animate: true});
                pAjaxRequest(reqData, '/api/v1/virus/operate', 'post', function (res) {
                    Metronic.unblockUI('#virus_table');
                    if (res.success) {
                        $('#virus_table').bootstrapTable('refresh');
                        return UIToastr.showSuccess(LANG.UI_VIRUS_APPLY_VIRUS, LANG.UI_VIRUS_APPLY_SUCCESS);
                    } else {
                        UIToastr.showWarning(LANG.UI_VIRUS_APPLY_VIRUS, LANG.UI_VIRUS_APPLY_FAIL);
                    }
                });
            }, 300)
        })
    }

    /**
     * 刷新
     * @param row
     */
    const initRefreshVirus = function (row) {
        bootbox.confirm({
            title: LANG.UI_VIRUS_FLASH_VIRUS,
            message: LANG.UI_VIRUS_FLASH_VIRUS_IS,
            callback: debounce(function (r) {
                if (!r) {
                    return;
                }
                let reqData = {
                    manage_virus_lib_op: VIRUS_OP_TYPE_ENUM.refresh,
                    lib_type: row.type,
                };
                Metronic.blockUI({target: '#virus_table', animate: true});
                pAjaxRequest(reqData, '/api/v1/virus/operate', 'POST', function (res) {
                    Metronic.unblockUI('#virus_table');
                    if (res.success) {
                        $('#virus_table').bootstrapTable('refresh');
                        return UIToastr.showSuccess(LANG.UI_VIRUS_FLASH_VIRUS, LANG.UI_VIRUS_FLASH_SUCCESS);
                    } else {
                        UIToastr.showWarning(LANG.UI_VIRUS_FLASH_VIRUS, LANG.UI_VIRUS_FLASH_FAIL);
                    }
                });
            }, 300)
        })
    }

    /**
     * 更新病毒库
     * @param row
     */
    const initRenew = function (row) {
        if (parseInt(row.type) === VIRUS_TYPE_ENUM.clamav) {
            $('#clamavTips').show();
            $('#kavTips').hide();
        } else {
            $('#clamavTips').hide();
            $('#kavTips').show();
        }
        $('#drawer-updata').data('virus-type', row.type).drawer('show');
        $('#virusType').closest('div.detail-task-right').hide();
    }

    /**
     * 初始化上传授权文件
     * @param row
     */
    const initUploadAuthorizationFile = row => {
        $('#uploadAuthorizationFileDrawer').data('virus-type', row.type).drawer('show');
    };

    /**
     * 上传病毒库事件
     */
    const uploadDriver = () => {
        let fileInput = document.createElement('input');
        fileInput.type = 'file';
        fileInput.accept = '.tar.gz';
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

    const uploadAuthorizationFile = () => {
        let fileInput = document.createElement('input');
        fileInput.type = 'file';
        fileInput.accept = '.key';
        fileInput.multiple = false;
        fileInput.click();

        fileInput.onchange = function () {
            let files = this.files;
            if (files.length === 1) {
                let file = files[0];
                authorizationFileDropZone.removeAllFiles();
                $('.upload-authorization-file-result-div').show();
                authorizationFileDropZone.addFile(file);
            }
        };
    };

    /**
     * 更新病毒库提交
     * @returns {boolean}
     */
    const renewSubmit = function () {
        if (!virusName) {
            UIToastr.showWarning(LANG.UI_VIRUS_GENXIN_VIRUS, LANG.UI_VIRUS_PLEASE_UPDATA);
            return false;
        }
        let $drawerUpdata = $('#drawer-updata');
        let requestData = {
            virus_lib_upload_path: virusPath,
            virus_lib_file_name: virusName,
            lib_type: parseInt($drawerUpdata.data('virus-type')),
        };
        Metronic.blockUI({target: '#drawer-updata', animate: true});
        pAjaxRequest(requestData, '/api/v1/virus/update', 'post', function (res) {
            Metronic.unblockUI('#drawer-updata');
            if (res.success) {
                $('#virus_table').bootstrapTable('refresh');
                $drawerUpdata.drawer('hide');
                return UIToastr.showSuccess(LANG.UI_VIRUS_GENXIN_VIRUS, LANG.UI_VIRUS_GENXIN_SUCCESS);

            } else {
                UIToastr.showWarning(LANG.UI_VIRUS_GENXIN_VIRUS, res.message);
            }
        });
    };

    /**
     * 上传病毒库授权文件
     */
    const uploadAuthorizationFileSubmit = () => {
        if (!virusAuthorizationFileName) {
            UIToastr.showWarning(LANG.UI_VIRUS_UPLOAD_AUTHORIZATION_FILE, LANG.UI_VIRUS_PLEASE_UPLOAD_AUTHORIZATION_FILE);
            return false;
        }

        let $uploadAuthorizationFileDrawer = $('#uploadAuthorizationFileDrawer');
        let requestData = {
            key_file_upload_path: virusAuthorizationFilePath,
            key_file_name: virusAuthorizationFileName,
            lib_type: parseInt($uploadAuthorizationFileDrawer.data('virus-type')),
        }
        Metronic.blockUI({target: '#uploadAuthorizationFileDrawer', animate: true});
        pAjaxRequest(requestData, '/api/v1/virus/authorization_file', `POST`, res => {
            Metronic.unblockUI('#uploadAuthorizationFileDrawer');
            if (res.success) {
                $('#virus_table').bootstrapTable('refresh');
                $uploadAuthorizationFileDrawer.drawer('hide');
                return UIToastr.showSuccess(LANG.UI_VIRUS_UPLOAD_AUTHORIZATION_FILE, LANG.UI_VIRUS_UPLOAD_AUTHORIZATION_FILE_SUCCESS);
            } else {
                UIToastr.showWarning(LANG.UI_VIRUS_UPLOAD_AUTHORIZATION_FILE, LANG.UI_VIRUS_UPLOAD_AUTHORIZATION_FILE_FAILED);
            }
        });
    };

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
    return {
        //main function to initiate the module
        init: function () {
            initDriverUpload()
            initAuthorizationFileUpload();
            initDataTable()
            initListeners()
        }
    };

}();

jQuery(document).ready(function () {
    VirusDetail.init();
});