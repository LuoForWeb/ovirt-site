var obsManager = function() {
    const publicVendors = [0, 1, 2, 3, 5];
    const privateVendors = [4, 6, 8];
    const VENDOR_TYPE = {
        AWS: 0, // 亚马逊云
        OSS: 1, // 阿里云
        COS: 2, // 腾讯云
        OBS: 3, // 华为云
        CEPH: 4, // 分布式云
        WASABI: 5, // 监控云
        MINIO: 6, // 对象存储
        AZURE: 7, // 微软云
        HOP: 8, // 华为OceanStor Pacific
        OTHER_S3: 9
    };
    const VENDOR_TYPE_DES = {
        0: LANG.UI_OBS_VENDOR_AWS,
        1: LANG.UI_OBS_VENDOR_OSS,
        2: LANG.UI_OBS_VENDOR_COS,
        3: LANG.UI_OBS_VENDOR,
        4: 'Ceph S3',
        5: 'Wasabi',
        6: 'MinIO',
        7: LANG.UI_OBS_VENDOR_AZURE,
        8: 'Huawei OceanStor Pacific',
        9: LANG.UI_OBS_VENDOR_OTHER
    };
    const OBS_STATUS_DESC = {
        0: LANG.UI_VOL_CDP_JOB_DETAILS_OFF_LINE,
        1: LANG.UI_JOB_CROWD_NORMAL
    };
    const AWS_ACCOUNT_TYPE_DES = {
        1: 'AWS China',
        2: 'AWS Global'
    };
    const AUTH_TYPE = {
        CAPACITY: 3 // 容量授权
    }
    let obsFormValidateOptions = {
        errorElement: 'span', //default input error message container
        errorClass: 'help-block help-block-error', // default input error message class
        focusInvalid: true, // do not focus the last invalid input
        ignore: "",  // validate all fields including form hidden input
        rules: {
            admin_name: {
                required: true
            },
            password: {
                required: true
            },
            nickname: {
                required: true
            }
        },
        messages: {
            admin_name: {
                required: LANG.UI_OBS_USERNAME_CANNOT_NULL
            },
            password: {
                required: LANG.UI_OBS_PASSWORD_CANNOT_NULL
            },
            nickname: {
                required: LANG.UI_OBS_NAME_CANNOT_NULL
            }
        },
        errorPlacement: function (error, element) { // render error placement for each input type
            let icon = $(element).parent('.input-icon').children('i');
            icon.removeClass('fa-check').addClass("fa-warning");
            icon.attr("data-original-title", error.text()).tooltip({'container': 'body'});
        },
        highlight: function (element) { // hightlight error inputs
            $(element)
                .closest('.form-group').removeClass("has-success").addClass('has-error'); // set error class to the control group
        },
        success: function (label, element) {
            let icon = $(element).parent('.input-icon').children('i');
            $(element).closest('.form-group').removeClass('has-error').addClass('has-success'); // set success class to the control group
            icon.removeClass("fa-warning").addClass("fa-check");
        }
    }
    let obsAddFormValidator = $('#obs_form').validate(obsFormValidateOptions);
    let addObsFormFlag = false; // 标记 add form / edit form
    let ajaxData = {
        obs_uuid: '',
        vendor: 0,
        aws_account_type: 0,
        endpoint_override: '',
        ssl_verify_flag: 0,
        access_key_id: '',
        access_key_secret: '',
        appid: '',
        nickname: '',
        appliance_agency_flag: false,
        appliance_uuid: ''  
    };
    let currentObsObj = {}; // 要修改的当前对象存储
    let cacheObj = {}
    let selectedRows = [];
    let APPLIANCE_AGENCY_HAS_CONFIGED = false; // 传输代理是否已配置标记
    let refreshTime = 0;
    let _dateRangePicker_startTime = '', _dateRangePicker_endTime = '';
    let advancedSearch = {};
    let normalTag =
        '<div class="tag tag-normal tag_en">' +
            '<span>' + LANG.UI_JOB_CROWD_NORMAL + '</span>'
        '</div>';
    let unnormalTag =
        '<div class="tag tag-error tag_en">' +
            '<span>' + LANG.UI_CLOUD_PLATFORM_OFFLINE + '</span>'
        '</div>';
    let successTag =
        '<div class="tag tag-success tag_en">' +
            '<span>' + LANG.UI_REPORT_YES + '</span>'
        '</div>';
    let errorTag =
        '<div class="tag tag-error tag_en">' +
            '<span>' + LANG.UI_REPORT_NO + '</span>'
        '</div>';
    let licenceInfo = {}; // 对象存储授权信息

    const operateEvents =  {
        // 刷新对象存储
        'click .refresh_obs': (e, value, row, index) => {
            // 校验全局观察者操作权限，type为2表示校验分配的权限，需要传source_uuid（资源的主键uuid）和 source_type: 对象存储obs（59）
            checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.ASSIGN_PERMISSION, source_uuid: row.obs_uuid, source_type: 59 }, () => {
                let params = {
                    obs_uuid: row.obs_uuid,
                    vendor: row.vendor,
                    access_key_id: row.access_key_id,
                    access_key_secret: row.access_key_secret,
                    nickname: row.nickname,
                    endpoint_override: row.endpoint_override,
                    ssl_verify_flag: row.ssl_verify_flag
                }

                Metronic.blockUI({target: '.table-container', animate: true, cenrerY: true,});

                pAjaxRequest(params, "/api/v1/s3/obs_refresh", "POST", function (result) {
                    if (result.success) {
                        UIToastr.showSuccess(LANG.UI_OBS_REFRESH_OBS_SUCCESS);
                        // 刷新表格
                        $('#obstorage_table').bootstrapTable('refresh');
                    } else {
                        UIToastr.showWarning(LANG.UI_OBS_REFRESH_OBS, result.message);
                    }

                    Metronic.unblockUI('.table-container');
                });
            });
        },
        // 查看对象存储详情
        'click .detail_obs': (e, value, row, index) => {
            $('.drawer-vendor').html(VENDOR_TYPE_DES[row.vendor]);
            $('.drawer-create-time').html(row.obs_create_time);
            let detail = JSON.parse(row.detail);

            if (row.status === 1) {
                $('.drawer-status').html(normalTag);
            } else {
                $('.drawer-status').html(unnormalTag);
            }

            if (row.vendor === VENDOR_TYPE.AZURE) { // 微软云
                $('.drawer-public-private-cloud').hide();
                $('.from-item-account-type').hide();
                $('.drawer-azure-cloud').show();
                // 抽屉表单赋值
                $('.drawer-connect-str').html(row.access_key_id);
            } else if (publicVendors.indexOf(row.vendor) > -1) { // 公有云
                // 公有云增加APPID
                $('.drawer-azure-cloud').hide();
                $('.drawer-public-private-cloud').show();
                // 抽屉表单赋值
                $('.drawer-username').html(row.access_key_id);
                $('.drawer-username').prop('title', row.access_key_id);

                if (row.vendor === VENDOR_TYPE.AWS) {
                    $('.from-item-account-type').show();
                    $('.drawer-account-type').html(AWS_ACCOUNT_TYPE_DES[parseInt(detail.aws_account_type)]);
                } else {
                    $('.from-item-account-type').hide();
                }
            } else if (privateVendors.indexOf(row.vendor) > -1) { // 私有云
                $('.drawer-azure-cloud').hide();
                $('.from-item-account-type').hide();
                $('.drawer-public-private-cloud').show();
                // 抽屉表单赋值
                $('.drawer-username').html(row.access_key_id);
                $('.drawer-username').prop('title', row.access_key_id);
            } else if (row.vendor === VENDOR_TYPE.OTHER_S3) {
                $('.drawer-azure-cloud').hide();
                $('.from-item-account-type').hide();
                $('.drawer-public-private-cloud').hide();

                // 抽屉表单赋值
                $('.drawer-username').html(row.access_key_id);
                $('.drawer-username').prop('title', row.access_key_id);
            }

            if (row.endpoint_override) { // 终端节点
                $('.drawer-private-cloud').show();
                $('.drawer-endpoint').html(row.endpoint_override);
            } else {
                $('.drawer-private-cloud').hide();
            }

            if (row.ssl_verify_flag === 1) { // SSL
                $('.drawer-ssl-flag').html(successTag);
            } else {
                $('.drawer-ssl-flag').html(errorTag);
            }

            if (row.appliance_uuid) { // 代理
                $('.appliance-agency-flag').html(successTag);

                $('.appliance-agency-item').show();
                $('.appliance-agency').html(row.appliance_agency);
            } else {
                $('.appliance-agency-flag').html(errorTag);
                $('.appliance-agency-item').hide();
            }
        }
    }

    // -----------------------------    BEGIN OBS TABLE  -------------------------------------

    const getQueryParams = () => {
        let params = {};

        // 高级搜索
        // 添加时间
        if (_dateRangePicker_startTime && _dateRangePicker_endTime) {
            params.start_time = _dateRangePicker_startTime;
            params.end_time = _dateRangePicker_endTime;
        }

        if ($('#advanced_search_vendor').val()) {
            params.vendor = parseInt($('#advanced_search_vendor').val());
        }

        if ($('#advanced_search_status').val()) {
            params.status = parseInt($('#advanced_search_status').val());
        }

        if ($('#advanced_search_endpoint_override').val()) {
            params.endpoint_override = $('#advanced_search_endpoint_override').val();
        }

        if ($('#advanced_search_nickname').val()) {
            params.nickname = $('#advanced_search_nickname').val();
        }

        if ($('#advanced_search_creator').val()) {
            params.creator = $('#advanced_search_creator').val();
        }

        if ($('#advanced_search_owner').val()) {
            params.owner = $('#advanced_search_owner').val();
        }

        return params;
    }

    const initDataTable = () => {
        const SYNC_VISIBLE = CONF.PERMISSION_ARR.indexOf('p_obsmanager_sync') > -1;

        let options = {
            vin_url: "/api/v1/s3",
            vin_params: getQueryParams,
            vin_method: "GET",
            placeholder: LANG.UI_OBS_SELECT_BY_OBS_NAME,
            toolbarId: '#obs_manager_toolbar',
            vin_toolbar: '.vin_toolbar',
            uniqueId: 'obs_uuid',
            paginationLoop: false,
            pagination: true, //分页
            pageList:[10,20,50,100,150,200], //每页数量
            resizable: true, //可变宽度
            fullPage:true,
            searchSelector: '.currentSearch',
            sortName: 'obs_create_time',
            sortOrder: 'desc',
            onCheck: (row) => {
                if ($('.btn.btn-toolbar-delete').hasClass('disabled')) {
                    $('#deleteOBStorage').removeAttr("disabled");
                    $('.btn.btn-toolbar-delete').removeClass('disabled');
                }
            },
            onUncheck: (row) => {
                selectedRows = $('#obstorage_table').bootstrapTable('getSelections');
                if (selectedRows.length === 0) {
                    $('#deleteOBStorage').attr("disabled", true);
                    $('.btn.btn-toolbar-delete').addClass('disabled');
                }
            },
            onUncheckAll: () => {
                $('#deleteOBStorage').attr("disabled", true);
                $('.btn.btn-toolbar-delete').addClass('disabled');
            },
            onCheckAll: () => {
                if ($('.btn.btn-toolbar-delete').hasClass('disabled')) {
                    $('#deleteOBStorage').removeAttr("disabled");
                    $('.btn.btn-toolbar-delete').removeClass('disabled');
                }
            },
            onPostBody: function () {
                let tableData = $('#obstorage_table').bootstrapTable('getData');

                if (tableData.length === 0) { // 空data保证fixed-table-container高度100%，以消除 bs-table 中计算的乱七八糟的错误高度
                    $('.resource-manager-wrap .resource-manager-wrap__content .table-container .bootstrap-table .fixed-table-container').css('height', '100%');
                } else {
                    // 52px 是 分页 fixed-table-pagination 的高度
                    $('.resource-manager-wrap .resource-manager-wrap__content .table-container .bootstrap-table .fixed-table-container').css('height', 'calc(100% - 52px)');
                }
            },
            columns: [
                {
                    checkbox: true,
                    sortable: false,
                    formatter: function (value, row, index) {
                        if (row.op_flag === false) { // 创建者等不是当前用户，不能操作
                            return {
                                disabled: true
                            };
                        }
                    }
                },
                {
                    field: 'vendor',
                    title: LANG.UI_OBS_CLOUD_VENDOR,
                    sortable: true,
                    align: 'center',
                    formatter: (val) => {
                        return VENDOR_TYPE_DES[val]
                    }
                },
                {
                    field: 'endpoint_override',
                    title: LANG.UI_OBS_SERVER_ENDPOINT,
                    sortable: false,
                    align: 'center'
                },
                {
                    field: 'nickname',
                    title: LANG.UI_PLATFORM_DES_OBS_NAME,
                    sortable: false,
                    align: 'center',
                },
                {
                    field: 'obs_create_time',
                    title: LANG.UI_PUBLIC_ADD_TIME,
                    sortable: true,
                    align: 'center',
                },
                {
                    field: 'status',
                    title: LANG.UI_PUBLIC_STATUS,
                    sortable: false,
                    align: 'center',
                    formatter: (val) => {
                        return val === 1 ? normalTag : unnormalTag;
                    }
                },
                {
                    field: 'refresh_time',
                    title: LANG.UI_CLOUD_PLATFORM_SYNC_TIME,
                    sortable: false,
                    align: 'center'
                },
                // {
                //     field: 'authorization',
                //     title: LANG.UI_CLOUD_PLATFORM_AUTHORIZE_STATUS,
                //     sortable: true,
                //     align: 'center',
                //     formatter:function(value){
                //         let labelHtml = "";
                //         switch(value){
                //             case 1:
                //                 labelHtml = '<span class="label label-sm label-success status-icon table-label_en width80_en">' +LANG.UI_CLOUD_PLATFORM_AUTHORIZED + '</span>';
                //                 break;
                //             case 2:
                //             case 0:
                //                 labelHtml = '<span class="label label-sm label-default status-icon table-label_en width80_en">' + LANG.UI_CLOUD_PLATFORM_UNAUTHORIZED + '</span>';
                //                 break;
                //         }
                //         return labelHtml;
                //     }
                // },
                {
					field: 'creator',
					title: LANG.UI_REPORT_BUILDER,
					sortable: false,
					align: 'center',
				},
				{
					field: 'owner',
					title: LANG.UI_CLIENT_OWNER,
					sortable: false,
					align: 'center',
				},
                {
                    field: 'strategy_actions',
                    title: LANG.UI_USER_MANAGEMENT,
                    sortable: false,
                    align: 'center',
                    clickToSelect: false,
                    events: operateEvents,
                    // 生成修改和删除按钮
                    formatter: (value, row, index) => {
                        let buttonGroupEle = '<div class="btn-group">';
                        if (SYNC_VISIBLE) {
                            buttonGroupEle +=
                                '<div class="btn_operation_vicon">' +
                                    '<a class="refresh_obs"><i class="viconfont vicon-biaogeshuaxin" title="' + LANG.UI_BACKUP_TREE_REFRESH + '"></i></a>' +
                                    '<a class="detail_obs" data-target="#obs_detail_drawer" data-toggle="drawer"><i class="viconfont vicon-tenant-detail" title="' + LANG.UI_ALARM_DETAILS + '"></i></a>' +
                                '</div>';
                        } else {
                            buttonGroupEle +=
                                '<div class="btn_operation_vicon">' +
                                    '<a class="detail_obs" data-target="#obs_detail_drawer" data-toggle="drawer"><i class="viconfont vicon-tenant-detail" title="' + LANG.UI_ALARM_DETAILS + '"></i></a>' +
                                '</div>';
                        }
                        
                        buttonGroupEle += '</div>';
                        return buttonGroupEle;
                    }
                }
            ]
        }

        $('#obstorage_table').baseTableConfig().init(options);
    }

    // -----------------------------    END OBS TABLE  ---------------------------------------



    // -----------------------------    BEGIN ADD OBS FORM  ----------------------------------

    /**
     * 判断要提交的对象存储是否有做修改，除用户名/密码/SSL认证/终端节点 四者之一有修改就认为被修改过
     * @param curObj 存储在缓存中的对象存储
     * @param finalObj 最终要传给接口的对象存储
     * @returns {number} 1：已修改 0：未修改
     */
    const hasChangedCurrentObs = (curObj, finalObj) => {
        const curObjKeys = Object.keys(curObj);
        const finalObjKeys = Object.keys(finalObj);

        if (curObjKeys.length !== finalObjKeys.length) {
            return 1;
        }

        for (let key of curObjKeys) {
            if (curObj[key] !== finalObj[key] && key !== 'nickname') {
                return 1;
            }
        }

        return 0;
    }

    const utf8ToBase64 = (str) => {
        return btoa(
            new Uint8Array(
                new TextEncoder('utf-8').encode(str)
            ).reduce((data, byte) => data + String.fromCharCode(byte), '')
        );
    }

    /**
     * 添加/修改对象存储
     */
    const obsFormSubmit = () => {
        let vendor = parseInt($('#vendor').val());
        let endpoint = $('#endpoint_override').val();
        let encrypt = new JSEncrypt();
		encrypt.setPublicKey(CONF.PUBLIC_KEY);

        ajaxData.vendor = vendor;
        ajaxData.nickname = $('#nickname').val();

        // 修改时，对于公有云服务商，需要从缓存的对象存储中拿到终端节点信息
        currentObsObj = JSON.parse(sessionStorage.getItem('currentObsObj'));

        if (vendor === VENDOR_TYPE.AZURE) { // 微软云
            ajaxData.access_key_id = $('#connect_str').val();
            ajaxData.access_key_secret = '';
            ajaxData.endpoint_override = endpoint;
            ajaxData.ssl_verify_flag = $('#sslConnect').bootstrapSwitch('state') ? 1 : 0;
            ajaxData.aws_account_type = 0;
        } else if (publicVendors.indexOf(vendor) > -1) { // 公有云
            if (vendor === VENDOR_TYPE.COS) {
                ajaxData.appid = '';
            }

            if (vendor === VENDOR_TYPE.AWS) {
                ajaxData.aws_account_type = parseInt($('#account_subzone').val());
            } else {
                ajaxData.aws_account_type = 0;
            }

            ajaxData.access_key_id = $('#admin_name').val();
            ajaxData.access_key_secret = encrypt.encrypt($('#password').val());
            ajaxData.endpoint_override = addObsFormFlag ? endpoint : (endpoint ? endpoint : cacheObj.endpoint_override);
            ajaxData.ssl_verify_flag = $('#sslConnect').bootstrapSwitch('state') ? 1 : 0;
        } else if (privateVendors.indexOf(vendor) > -1) { // 私有云
            ajaxData.access_key_id = $('#admin_name').val();
            ajaxData.access_key_secret = encrypt.encrypt($('#password').val());
            ajaxData.endpoint_override = $('#endpoint_override').val();
            ajaxData.ssl_verify_flag = $('#sslConnect').bootstrapSwitch('state') ? 1 : 0;
            ajaxData.aws_account_type = 0;
        } else if (vendor === VENDOR_TYPE.OTHER_S3) {
            ajaxData.access_key_id = $('#admin_name').val();
            ajaxData.access_key_secret = encrypt.encrypt($('#password').val());
            ajaxData.endpoint_override = $('#endpoint_override').val();
            ajaxData.ssl_verify_flag = $('#sslConnect').bootstrapSwitch('state') ? 1 : 0;
            ajaxData.aws_account_type = 0;
        }

        // 获取传输代理
        let applianceAgencyFlag = $('#appliance_agency_flag').get(0).checked;
        
        if (applianceAgencyFlag) {
            ajaxData.appliance_agency_flag = true;
            ajaxData.appliance_uuid = $('#applianceSelect').val();
        } else {
            ajaxData.appliance_agency_flag = false;
            ajaxData.appliance_uuid = '';
        }

        if (addObsFormFlag) {
            Metronic.blockUI({target: '.modal-body',animate: true});
            pAjaxRequest(ajaxData, "/api/v1/s3", "POST", (result) => {
                if (result.success) {
                    $('#obs_modal').modal('hide');
                    UIToastr.showSuccess(LANG.UI_OBS_MANAGER_ADD, LANG.UI_OBS_MANAGER_ADD_SUCCESS);
                    // 刷新表格
                    $('#obstorage_table').bootstrapTable('refresh');
                } else {
                    UIToastr.showWarning(LANG.UI_OBS_MANAGER_ADD, result.message);
                }

                Metronic.unblockUI('.modal-body');
            });
        } else {
            let finalObsObj = JSON.parse(JSON.stringify(ajaxData));
            // new_connect_flag 赋值
            ajaxData.new_connect_flag = hasChangedCurrentObs(currentObsObj, finalObsObj);

            if (ajaxData.new_connect_flag === 1 || currentObsObj['nickname'] !== finalObsObj['nickname'])  {
                Metronic.blockUI({target: '.modal-body',animate: true});
                pAjaxRequest(ajaxData, "/api/v1/s3", "PATCH", (result) => {
                    if (result.success) {
                        $('#obs_modal').modal('hide');
                        UIToastr.showSuccess(LANG.UI_OBS_MANAGER_EDIT, LANG.UI_OBS_MANAGER_EDIT_SUCCESS);
                        // 刷新表格
                        $('#obstorage_table').bootstrapTable('refresh');
                    } else {
                        UIToastr.showWarning(LANG.UI_OBS_MANAGER_EDIT, result.message);
                    }

                    Metronic.unblockUI('.modal-body');
                });
            } else {
                // 未修改不和后端交互，直接关闭弹窗
                $('#obs_modal').modal('hide');
            }
        }
    }

    /**
     * 初始化添加对象存储模态框
     */
    const initAddObsModal = () => {
        $('.private-cloud .required').hide();
        $('.azure-cloud').hide();
        $('.cos-cloud').hide();
        $('#obs_modal_title').html('');
        let modalHeaderHtml = '<i class="viconfont vicon-danchuangtianjia1"></i><span style="margin-left: 5px">' + LANG.UI_OBS_MANAGER_ADD + '</span>';
        $('#obs_modal_title').append(modalHeaderHtml);
        // 清空上一次的数据和校验
        clearLastForm();
        // 默认设置为云服务商AWS
        $('#vendor').val('0');
        $('#vendor').prop('disabled', false);
        $('#account_subzone').prop('disabled', false);
        $('#endpoint_override').prop('disabled', false);
        $('.public-private-cloud').show();
        $('.from-group-aws').show();
        $('#password_required').show();
        $('#password').attr('placeholder', 'secret access key');
        $('#appliance_agency_flag').bootstrapSwitch('state', false);
        // 勾选SSL认证
        $('#sslConnect').bootstrapSwitch('state', true);

        $('#obs_modal').modal({'width': "900px"});

        obsAddFormValidator.destroy(); // destroy上一个validator

        // 增加 用户名 和 密码 非空校验(修改时不校验密码非空)
        obsFormValidateOptions.rules = {
            admin_name: {
                required: true
            },
            password: {
                required: true
            },
            nickname: {
                required: true
            }
        };
        obsFormValidateOptions.messages = {
            admin_name: {
                required: LANG.UI_OBS_USERNAME_CANNOT_NULL
            },
            password: {
                required: LANG.UI_MOTION_VALIDATE_ADMIN_NOT_NULL_TIPS
            },
            nickname: {
                required: LANG.UI_OBS_NAME_CANNOT_NULL
            }
        };

        obsAddFormValidator = $('#obs_form').validate(obsFormValidateOptions);

        // 增加默认名
        pAjaxRequest({job_name: LANG.UI_VISUAL_OBS}, '/api/v1/s3/default', "GET", (result) => {
            if (result.success) {
                $('#nickname').val(result.data.value);
            }
        })
    };

    /**
     * 传输代理 switch change
     */
    const applianceSwitchChange = () => {
        let flag = $('#appliance_agency_flag').get(0).checked;

        if (flag) {
            $('.applianceselectdiv').show();
        } else {
            $('.applianceselectdiv').hide();
        }
    };

    // -----------------------------    END ADD OBS FORM    ----------------------------------


    // -----------------------------    BEGIN EDIT OBS FORM  ---------------------------------

    /**
     * 初始化修改对象存储模态框
     * @param row 选中的行
     */
    const initEditModal = (row) => {
        let vendor = row[0].vendor;
        let detail = JSON.parse(row[0].detail);

        ajaxData.obs_uuid = row[0].obs_uuid;

        // 禁用云服务商
        $('#vendor').prop('disabled', true);
        // 禁用AWS账户分区
        $('#account_subzone').prop('disabled', true);
        // 禁用终端节点
        $('#endpoint_override').prop('disabled', true);

        // 缓存当前要修改的对象存储到sessionStorage中
        cacheObj = {
            obs_uuid: row[0].obs_uuid,
            vendor: row[0].vendor, // 云服务商类型
            aws_account_type: detail.aws_account_type, // AWS 账户分区
            endpoint_override: row[0].endpoint_override, // 服务端终端节点
            ssl_verify_flag: row[0].ssl_verify_flag, // 是否开启SSL认证
            access_key_id: row[0].access_key_id, // 访问密钥ID
            access_key_secret: '', // 私密访问密钥
            appid: '', // 腾讯云APPID
            nickname: row[0].nickname, // 对象存储名
            appliance_agency_flag: row[0].appliance_uuid ? true : false, // 是否开启传输代理
			appliance_uuid: row[0].appliance_uuid, // 传输代理uuid
        }

        sessionStorage.setItem('currentObsObj', JSON.stringify(cacheObj));

        if (vendor === VENDOR_TYPE.AZURE) {
            $('.public-private-cloud').hide();
            $('.cos-cloud').hide();
            $('.from-group-aws').hide();
            $('#password_required').hide();
            $('.other-obs-des').hide();
            $('.private-cloud .required').show();
            $('.azure-cloud').show();

            // 表单赋值
            $('#vendor').val(row[0].vendor);
            $('#connect_str').val(row[0].access_key_id);
            $('#nickname').val(row[0].nickname);
            $('#endpoint_override').val(row[0].endpoint_override);

            // 表单校验
            obsAddFormValidator.destroy(); // destroy上一个validator
            // 增加 连接字符串 非空校验
            obsFormValidateOptions.rules = {
                connect_str: {
                    required: true
                },
                nickname: {
                    required: true
                },
                endpoint_override: {
                    required: true
                },
            };
            obsFormValidateOptions.messages = {
                connect_str: {
                    required: LANG.UI_OBS_CONNECT_STR_CANNOT_NULL
                },
                nickname: {
                    required: LANG.UI_OBS_NAME_CANNOT_NULL
                },
                endpoint_override: {
                    require: LANG.UI_OBS_SERVER_ENDPOINT_CANNOT_NULL
                },
            };
            obsAddFormValidator = $('#obs_form').validate(obsFormValidateOptions);
        } else if (publicVendors.indexOf(vendor) > -1) {
            $('.azure-cloud').hide();
            $('.private-cloud .required').show();
            $('#password_required').hide();
            $('.other-obs-des').hide();
            $('.public-private-cloud').show();

            if (vendor === VENDOR_TYPE.AWS) {
                $('.from-group-aws').show();

                $('#account_subzone').val(detail.aws_account_type);
            } else {
                $('.from-group-aws').hide();
            }

            if (vendor === VENDOR_TYPE.COS) {
                $('.cos-cloud').show();

                // $('#appid').val(detail.APPID);
            } else {
                $('.cos-cloud').hide();
            }

            // 表单赋值
            $('#vendor').val(row[0].vendor);
            $('#endpoint_override').val(row[0].endpoint_override);
            if (row[0].ssl_verify_flag === 0) {
                $('#sslConnect').bootstrapSwitch('state', false);
            } else {
                $('#sslConnect').bootstrapSwitch('state', true);
            }
            $('#admin_name').val(row[0].access_key_id);
            $('#password').val('');
            $('#password').attr('placeholder', LANG.UI_PUBLIC_MODIFY_PASSWORD_TIPS);
            $('#nickname').val(row[0].nickname);

            // 表单校验
            obsAddFormValidator.destroy(); // destroy上一个validator

            // 增加 用户名 和 密码 非空校验(修改时不校验密码非空)
            obsFormValidateOptions.rules = {
                admin_name: {
                    required: true
                },
                nickname: {
                    required: true
                },
                endpoint_override: {
                    required: true
                },
            };

            obsFormValidateOptions.messages = {
                admin_name: {
                    required: LANG.UI_OBS_USERNAME_CANNOT_NULL
                },
                nickname: {
                    required: LANG.UI_OBS_NAME_CANNOT_NULL
                },
                endpoint_override: {
                    require: LANG.UI_OBS_SERVER_ENDPOINT_CANNOT_NULL
                },
            };

            obsAddFormValidator = $('#obs_form').validate(obsFormValidateOptions);
        } else if (privateVendors.indexOf(vendor) > -1) {
            $('.azure-cloud').hide();
            $('.cos-cloud').hide();
            $('.from-group-aws').hide();
            $('.public-private-cloud').show();
            $('.private-cloud .required').show();
            $('#password_required').hide();
            $('.other-obs-des').hide();

            // 表单赋值
            $('#vendor').val(row[0].vendor);
            $('#endpoint_override').val(row[0].endpoint_override);
            if (row[0].ssl_verify_flag === 0) {
                $('#sslConnect').bootstrapSwitch('state', false);
            } else {
                $('#sslConnect').bootstrapSwitch('state', true);
            }
            $('#admin_name').val(row[0].access_key_id);
            $('#password').val('');
            $('#password').attr('placeholder', LANG.UI_PUBLIC_MODIFY_PASSWORD_TIPS);
            $('#nickname').val(row[0].nickname);

            // 表单赋值
            obsAddFormValidator.destroy(); // destroy上一个validator

            // 增加 服务端终端节点 用户名 密码 非空校验(修改时不校验密码非空)
            obsFormValidateOptions.rules = {
                admin_name: {
                    required: true
                },
                endpoint_override: {
                    required: true
                },
                nickname: {
                    required: true
                }
            };

            obsFormValidateOptions.messages = {
                admin_name: {
                    required: LANG.UI_OBS_USERNAME_CANNOT_NULL
                },
                endpoint_override: {
                    require: LANG.UI_OBS_SERVER_ENDPOINT_CANNOT_NULL
                },
                nickname: {
                    required: LANG.UI_OBS_NAME_CANNOT_NULL
                }
            };

            obsAddFormValidator = $('#obs_form').validate(obsFormValidateOptions);
        } else if (vendor === VENDOR_TYPE.OTHER_S3) {
            $('.azure-cloud').hide();
            $('.from-group-aws').hide();
            $('.cos-cloud').hide();
            $('.public-private-cloud').show();
            $('.private-cloud .required').show();
            $('#password_required').hide();
            $('.other-obs-des').show();

            // 表单赋值
            $('#vendor').val(row[0].vendor);
            $('#endpoint_override').val(row[0].endpoint_override);
            if (row[0].ssl_verify_flag === 0) {
                $('#sslConnect').bootstrapSwitch('state', false);
            } else {
                $('#sslConnect').bootstrapSwitch('state', true);
            }
            $('#admin_name').val(row[0].access_key_id);
            $('#password').val('');
            $('#password').attr('placeholder', LANG.UI_PUBLIC_MODIFY_PASSWORD_TIPS);
            $('#nickname').val(row[0].nickname);

            // 表单赋值
            obsAddFormValidator.destroy(); // destroy上一个validator

            // 增加 服务端终端节点 用户名 密码 非空校验(修改时不校验密码非空)
            obsFormValidateOptions.rules = {
                admin_name: {
                    required: true
                },
                endpoint_override: {
                    required: true
                },
                nickname: {
                    required: true
                }
            };

            obsFormValidateOptions.messages = {
                admin_name: {
                    required: LANG.UI_OBS_USERNAME_CANNOT_NULL
                },
                endpoint_override: {
                    require: LANG.UI_OBS_SERVER_ENDPOINT_CANNOT_NULL
                },
                nickname: {
                    required: LANG.UI_OBS_NAME_CANNOT_NULL
                }
            };

            obsAddFormValidator = $('#obs_form').validate(obsFormValidateOptions);
        }

        // 代理赋值
        if (row[0].appliance_uuid) {
            $('#appliance_agency_flag').bootstrapSwitch('state', true);
            $('#applianceSelect').val(row[0].appliance_uuid);
        } else {
            $('#appliance_agency_flag').bootstrapSwitch('state', false);
        }

        $('#obs_modal_title').html('');
        let modalHeaderHtml = '<i class="viconfont vicon-xiugai"></i><span style="margin-left: 5px">' + LANG.UI_OBS_MANAGE_MODIFY + '</span>';
        $('#obs_modal_title').append(modalHeaderHtml);
        // 清空上一次的数据和校验
        clearLastForm();
        delete ajaxData.new_connect_flag;

        $('#obs_modal').modal({'width': "900px"});
    }
    // -----------------------------    END EDIT OBS FORM  -----------------------------------


    // -----------------------------    BEGIN DELETE OBS FORM  -------------------------------

    /**
     * 清空上一次的表单和校验
     */
    const clearLastForm = () => {
        if (addObsFormFlag) {
            $('#endpoint_override').val("").siblings("i").removeClass().addClass("fa").parents(".form-group").removeClass().addClass("form-group private-cloud");
            $('#admin_name').val("").siblings("i").removeClass().addClass("fa").parents(".form-group").removeClass().addClass("form-group");
            $('#password').val("").siblings("i").removeClass().addClass("fa").parents(".form-group").removeClass().addClass("form-group");
            $('#connect_str').val("").siblings("i").removeClass().addClass("fa").parents(".form-group").removeClass().addClass("form-group azure-cloud");
            // $('#appid').val("").siblings("i").removeClass().addClass("fa").parents(".form-group").removeClass().addClass("form-group cos-cloud");
            $('#nickname').val("").siblings("i").removeClass().addClass("fa").parents(".form-group").removeClass().addClass("form-group");
        } else {
            $('#endpoint_override').siblings("i").removeClass().addClass("fa").parents(".form-group").removeClass().addClass("form-group private-cloud");
            $('#admin_name').siblings("i").removeClass().addClass("fa").parents(".form-group").removeClass().addClass("form-group");
            $('#password').siblings("i").removeClass().addClass("fa").parents(".form-group").removeClass().addClass("form-group");
            $('#connect_str').siblings("i").removeClass().addClass("fa").parents(".form-group").removeClass().addClass("form-group azure-cloud");
            // $('#appid').siblings("i").removeClass().addClass("fa").parents(".form-group").removeClass().addClass("form-group cos-cloud");
            $('#nickname').siblings("i").removeClass().addClass("fa").parents(".form-group").removeClass().addClass("form-group");
        }
    }

    /**
     * 提交删除对象存储列表
     * @param {*} rows
     */
    const obsDelete = (rows) => {
        ajaxData.obs_uuids = rows.map(i => {return i.obs_uuid});

        Metronic.blockUI({target: '.modal-body',animate: true});

        pAjaxRequest(ajaxData, "/api/v1/s3", "DELETE", (result) => {
            if (result.success) {
                UIToastr.showSuccess(LANG.UI_OBS_MANAGER_DELETE_SUCCESS);
                // 刷新表格
                $('#obstorage_table').bootstrapTable('refresh');

                // 重置删除按钮样式
                $('#deleteOBStorage').attr("disabled", true);
                $('.btn.btn-toolbar-delete').addClass('disabled');
            } else {
                UIToastr.showWarning(LANG.UI_OBS_MANAGER_DELETE_FAILED, result.message);
            }

            Metronic.unblockUI('.modal-body');
        })
    }

    // -----------------------------    END EDIT OBS FORM  -----------------------------------

    /**
     * 初始化日期范围选择组件
     */
    const initDatatimePicker = () => {
        //初始化日期时间选择控件
        let dataRangePicker = $('#advanced_search_daterangepicker');
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

    const clearAdvancedSearch = (id) => {
        switch(id) {
            case 'start_time':
            case 'end_time':
            case 'label_time':
                advancedSearch.start_time = '';
                advancedSearch.end_time = '';
                $('#advanced_search_daterangepicker').val('');
                _dateRangePicker_startTime = '';
                _dateRangePicker_endTime = '';
                break;
            case 'label_vendor':
                $('#advanced_search_vendor').val('');
                advancedSearch.vendor = '';
                break;
            case 'label_status':
                advancedSearch.status = '';
                $('#advanced_search_status').val('');
                break;
            case 'label_endpoint_override':
                advancedSearch.endpoint_override = '';
                $('#advanced_search_endpoint_override').val('');
                break;
            case 'label_nickname':
                advancedSearch.nickname = '';
                $('#advanced_search_nickname').val('');
                break;
            case 'label_creator':
                advancedSearch.creator = '';
                $('#advanced_search_creator').val('');
                break;
            case 'label_owner':
                advancedSearch.owner = '';
                $('#advanced_search_owner').val('');
                break;
            default:
                break;
        }
    }

    /**
     * 生成高级搜索内容
     */
    const addSearchContent = () => {
        let contents = [];

        $('#current_searchDiv .searchContent').text('');

        if (advancedSearch.start_time && advancedSearch.end_time) {  // 添加时间
            contents.push('<span id="label_time" title="' + advancedSearch.start_time + "~" + advancedSearch.end_time + '"> '
                + LANG.UI_PUBLIC_ADD_TIME + ': <i>' + advancedSearch.start_time + "~" + advancedSearch.end_time + '</i><em>X</em></span>');
        }

        if (advancedSearch.vendor) {  // 云服务商
            contents.push(`<span id="label_vendor" value="${advancedSearch.vendor}" title="${VENDOR_TYPE_DES[advancedSearch.vendor]}">${LANG.UI_OBS_CLOUD_VENDOR}: `
                + `<i>${VENDOR_TYPE_DES[advancedSearch.vendor]}</i><em>X</em></span>`);
        }

        if (advancedSearch.status) {  // 状态
            contents.push(`<span id="label_status" value="${advancedSearch.status}" title="${OBS_STATUS_DESC[advancedSearch.status]}">${LANG.UI_PUBLIC_STATUS}: `
                + `<i>${OBS_STATUS_DESC[advancedSearch.status]}</i><em>X</em></span>`);
        }

        if (advancedSearch.endpoint_override) { // 终端节点
            contents.push(`<span id="label_endpoint_override" value="${advancedSearch.endpoint_override}" title="${advancedSearch.endpoint_override}">${LANG.UI_OBS_SERVER_ENDPOINT}: `
                + `<i>${advancedSearch.endpoint_override}</i><em>X</em></span>`);
        }

        if (advancedSearch.nickname) { // 别名
            contents.push(`<span id="label_nickname" value="${advancedSearch.nickname}" title="${advancedSearch.nickname}">${LANG.UI_PLATFORM_DES_OBS_NAME}: `
                + `<i>${advancedSearch.nickname}</i><em>X</em></span>`);
        }

        if (advancedSearch.creator) { // 创建者
            contents.push(`<span id="label_creator" value="${advancedSearch.creator}" title="${advancedSearch.creator}">${LANG.UI_CLIENT_CREATOR}: `
                + `<i>${advancedSearch.creator}</i><em>X</em></span>`);
        }

        if (advancedSearch.owner) { // 拥有者
            contents.push(`<span id="label_owner" value="${advancedSearch.owner}" title="${advancedSearch.owner}">${LANG.UI_OBS_OWNER}: `
                + `<i>${advancedSearch.owner}</i><em>X</em></span>`);
        }

        $('#current_searchDiv .searchContent').append(contents.join(''));

        $('#current_searchDiv .searchContent em').on('click', function () {
            // 这里的监听只能放在这里, 因为em元素是动态生成的
            // 点击x
            $(this).parent().remove();
            let searchContent = $('#current_searchDiv .searchContent');

            if (!searchContent[0].children.length) {
                $('#current_searchDiv').hide();

                // 动态设置 table-container高度
                if ($('#obs_manager_tip').length > 0) { // 提示存在
                    $('.resource-manager-wrap .resource-manager-wrap__content .table-container.obs-manager-table-container').css('height', 'calc(100% - 146px)');
                } else {
                    $('.resource-manager-wrap .resource-manager-wrap__content .table-container.obs-manager-table-container').css('height', 'calc(100% - 46px)');
                }
            }

            let parent = $(this).parent();
            let id = parent[0].id;

            clearAdvancedSearch(id);

            $('#obstorage_table').bootstrapTable('refresh');
        });

        if (contents.length) {
            $('#current_searchDiv').show();

            // 动态设置 table-container高度
            if ($('#obs_manager_tip').length > 0) { // 提示存在
                $('.resource-manager-wrap .resource-manager-wrap__content .table-container.obs-manager-table-container').css('height', 'calc(100% - 186px)');
            } else {
                $('.resource-manager-wrap .resource-manager-wrap__content .table-container.obs-manager-table-container').css('height', 'calc(100% - 86px)');
            }
        }
    }

    const addListeners = () => {
        // 打开添加对象存储弹窗
        $('#addOBStorage').on('click', () => {
            // 判断授权数量是否充足
            // if (licenceInfo.licensetype !== AUTH_TYPE.CAPACITY && licenceInfo.obs.valid <= 0) { // 非容量授权判断剩余授权个数
            //     UIToastr.showWarning(LANG.UI_OBS_UNDERAUTHORIZED);

            //     return;
            // }

            addObsFormFlag = true;
            // 初始化添加的模态框
            initAddObsModal();
        });

        // 打开修改对象存储弹窗
        $('#editOBStorage').on('click', function() {
            selectedRows = $('#obstorage_table').bootstrapTable('getSelections');

            if (selectedRows.length === 0) {
                UIToastr.showInfo(LANG.UI_OBS_MANAGE_MODIFY, LANG.UI_OBS_MANAGE_SELECT_TO_MODIFY);
                return;
            } else if (selectedRows.length !== 1) {
                UIToastr.showInfo(LANG.UI_OBS_MANAGE_MODIFY_ONLY_ONE, LANG.UI_OBS_MANAGE_SELECT_ONLY_ONE_TO_MODIFY);
                return;
            }

            let obsUuids = selectedRows.map(row => row.obs_uuid).join(',');

            // 校验全局观察者操作权限，type为2表示校验分配的权限，需要传source_uuid（资源的主键uuid）和 source_type: 对象存储obs（59）
            checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.ASSIGN_PERMISSION, source_uuid: obsUuids, source_type: 59 }, () => {
                addObsFormFlag = false;

                // 初始化修改的模态框
                initEditModal(selectedRows);
            });
        })

        // 监听云服务商change
        $('#vendor').on('change', () => {
            // 无论是新增表单还是修改表单，change时都要清空上一次的数据和校验
            $('#endpoint_override').val("").siblings("i").removeClass().addClass("fa").parents(".form-group").removeClass().addClass("form-group private-cloud");
            $('#admin_name').val("").siblings("i").removeClass().addClass("fa").parents(".form-group").removeClass().addClass("form-group");
            $('#password').val("").siblings("i").removeClass().addClass("fa").parents(".form-group").removeClass().addClass("form-group");
            $('#connect_str').val("").siblings("i").removeClass().addClass("fa").parents(".form-group").removeClass().addClass("form-group azure-cloud");
            $('#nickname').siblings("i").removeClass().addClass("fa").parents(".form-group").removeClass().addClass("form-group");

            // 勾选SSL认证
            $('#sslConnect').bootstrapSwitch('state', true);

            let vendor = parseInt($('#vendor').val());

            if (vendor === VENDOR_TYPE.AZURE) { // 微软云
                $('.public-private-cloud').hide();
                $('.cos-cloud').hide();
                $('.from-group-aws').hide();
                $('.private-cloud .required').hide();
                $('.other-obs-des').hide();
                $('.azure-cloud').show();

                obsAddFormValidator.destroy(); // destroy上一个validator
                // 增加 连接字符串 非空校验
                obsFormValidateOptions.rules = {
                    connect_str: {
                        required: true
                    },
                    nickname: {
                        required: true
                    }
                };
                obsFormValidateOptions.messages = {
                    connect_str: {
                        required: LANG.UI_OBS_CONNECT_STR_CANNOT_NULL
                    },
                    nickname: {
                        required: LANG.UI_OBS_NAME_CANNOT_NULL
                    }
                };
                obsAddFormValidator = $('#obs_form').validate(obsFormValidateOptions);
            } else if (publicVendors.indexOf(vendor) > -1) { // 公有云
                $('.azure-cloud').hide();
                $('.private-cloud .required').hide();
                $('.other-obs-des').hide();
                $('.public-private-cloud').show();

                if (vendor === VENDOR_TYPE.AWS) {
                    $('.from-group-aws').show();
                } else {
                    $('.from-group-aws').hide();
                }

                if (vendor === VENDOR_TYPE.COS) { // 腾讯云显示APPID输入框
                    $('.cos-cloud').show();
                } else {
                    $('.cos-cloud').hide();
                }

                obsAddFormValidator.destroy(); // destroy上一个validator

                // 增加 用户名 和 密码 非空校验(修改时不校验密码非空)
                obsFormValidateOptions.rules = {
                    admin_name: {
                        required: true
                    },
                    password: {
                        required: true
                    },
                    nickname: {
                        required: true
                    }
                };
                obsFormValidateOptions.messages = {
                    admin_name: {
                        required: LANG.UI_OBS_USERNAME_CANNOT_NULL
                    },
                    password: {
                        required: LANG.UI_MOTION_VALIDATE_ADMIN_NOT_NULL_TIPS
                    },
                    nickname: {
                        required: LANG.UI_OBS_NAME_CANNOT_NULL
                    }
                };

                obsAddFormValidator = $('#obs_form').validate(obsFormValidateOptions);
            } else if (privateVendors.indexOf(vendor) > -1) { // 私有云
                $('.azure-cloud').hide();
                $('.cos-cloud').hide();
                $('.from-group-aws').hide();
                $('.public-private-cloud').show();
                $('.private-cloud .required').show();
                $('.other-obs-des').hide();

                obsAddFormValidator.destroy(); // destroy上一个validator

                // 增加 服务端终端节点 用户名 密码 非空校验(修改时不校验密码非空)
                obsFormValidateOptions.rules = {
                    admin_name: {
                        required: true
                    },
                    password: {
                        required: true
                    },
                    endpoint_override: {
                        required: true
                    },
                    nickname: {
                        required: true
                    }
                };

                obsFormValidateOptions.messages = {
                    admin_name: {
                        required: LANG.UI_OBS_USERNAME_CANNOT_NULL
                    },
                    password: {
                        required: LANG.UI_MOTION_VALIDATE_ADMIN_NOT_NULL_TIPS
                    },
                    endpoint_override: {
                        require: LANG.UI_OBS_SERVER_ENDPOINT_CANNOT_NULL
                    },
                    nickname: {
                        required: LANG.UI_OBS_NAME_CANNOT_NULL
                    }
                };

                obsAddFormValidator = $('#obs_form').validate(obsFormValidateOptions);
            } else if (vendor === VENDOR_TYPE.OTHER_S3) {
                $('.azure-cloud').hide();
                $('.cos-cloud').hide();
                $('.from-group-aws').hide();
                $('.public-private-cloud').show();
                $('.private-cloud .required').show();
                $('.other-obs-des').show();

                obsAddFormValidator.destroy(); // destroy上一个validator

                // 增加 服务端终端节点 用户名 密码 非空校验(修改时不校验密码非空)
                obsFormValidateOptions.rules = {
                    admin_name: {
                        required: true
                    },
                    password: {
                        required: true
                    },
                    endpoint_override: {
                        required: true
                    },
                    nickname: {
                        required: true
                    }
                };

                obsFormValidateOptions.messages = {
                    admin_name: {
                        required: LANG.UI_OBS_USERNAME_CANNOT_NULL
                    },
                    password: {
                        required: LANG.UI_OBS_PASSWORD_CANNOT_NULL
                    },
                    endpoint_override: {
                        require: LANG.UI_OBS_SERVER_ENDPOINT_CANNOT_NULL
                    },
                    nickname: {
                        required: LANG.UI_OBS_NAME_CANNOT_NULL
                    }
                };

                obsAddFormValidator = $('#obs_form').validate(obsFormValidateOptions);
            }
        });

        // 添加/修改对象存储
        $('#obs_submit').on('click', () => {
            if ($('#obs_form').valid()) {
                let flag = $('#appliance_agency_flag').get(0).checked;

                if (flag && !APPLIANCE_AGENCY_HAS_CONFIGED) {
                    UIToastr.showWarning(LANG.UI_OBS_NO_TRANSPORT_PROXY, LANG.UI_OBS_NO_TRANSPORT_PROXY_TIP);

                    return;
                }

                obsFormSubmit();
            }
        });

        // 打开删除对象存储弹窗
        $('#deleteOBStorage').on('click', () => {
            selectedRows = $('#obstorage_table').bootstrapTable('getSelections');

            if (selectedRows.length === 0) {
                UIToastr.showInfo(LANG.UI_OBS_MANAGE_DELETE, LANG.UI_OBS_MANAGE_SELECT_TO_DELETE);
                return;
            }

            let obsUuids = selectedRows.map(row => row.obs_uuid).join(',');

            // 校验全局观察者操作权限，type为2表示校验分配的权限，需要传source_uuid（资源的主键uuid）和 source_type: 对象存储obs（59）
            checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.ASSIGN_PERMISSION, source_uuid: obsUuids, source_type: 59 }, () => {
                let messageHtml = `
                    <div class="modal-delete">
                        <div class="modal-delete__icon"><i class="viconfont vicon-a-Close-oneguanbi"></i></div>
                        <div class="modal-delete__info">${LANG.WEB_OBS_DELETE_OBS_TIP}</div>
                        <div class="modal-delete__content">
                            <div class="modal-delete__content__title">${LANG.UI_PLATFORM_OBS_NAME_OR_VENDOR}</div>
                `;

                for (let i = 0; i < selectedRows.length; i++) {
                    messageHtml += `<div class="modal-delete__content__item">${selectedRows[i].vendorDesc}/${selectedRows[i].nickname}</div> `;
                }

                messageHtml += '</div></div>';
                
                let buttons = {
                    cancel: {
                        label: LANG.UI_PUBLIC_CANCEL,
                        className: 'btn-default',
                        callback: function () {
                        }
                    },
                    ok: {
                        label:LANG.UI_PUBLIC_CONFIRM,
                        className: 'btn-danger',
                        callback: debounce(function () {
                            obsDelete(selectedRows);
                        }, 300),
                    }
                };

                bootbox.dialog({
                    title: LANG.UI_OBS_MANAGE_DELETE,
                    message: messageHtml,
                    buttons,
                });
            });
        });

        // 搜索对象存储
        $('#obs_search').off().on('click', () => {
            let searchVal = $('#search').val();

            if (searchVal) {
                $('#obstorage_table').bootstrapTable('refresh', {query: {search: searchVal}})
            }
        });

        $('#search').on('focus', () => {
            $('#obs_clear_search').removeClass('hide');
        })

        // 清空对象存储搜索
        $('#obs_clear_search').on('click', () => {
            $('#search').val('');
            $('#obs_clear_search').addClass('hide');
            $('#obstorage_table').bootstrapTable('refresh', {query: {search: ''}})
            $('#obstorage_table').bootstrapTable('resetSearch');
        })

        // 打开自动刷新间隔配置modal
        $('#autoRefreshInterval').on('click', () => {
            $('#auto_refresh_obs_modal').modal('show');
        });

        // 传输代理switch change
        $('#appliance_agency_flag').on('switchChange.bootstrapSwitch', applianceSwitchChange);

        // 提交配置的自动刷新间隔
        $('#auto_refresh_obs_submit').on('click', () => {
            let refresh = parseInt($('#refreshValue').val());

            if(!refresh || refresh < 5){
                UIToastr.showWarning(LANG.UI_OBS_MANAGER_REFRESH_INTERVAL_FAILED_TITLE,LANG.UI_OBS_MANAGER_REFRESH_INTERVAL_FAILED_TIP);
                $('#refreshValue').val(refreshTime);
                
                return;
            }

            bootbox.confirm({
                title: LANG.UI_OBS_EDIT_REFRESH_INTERVAL_TIME,
                message: LANG.UI_OBS_EDIT_REFRESH_INTERVAL_TIME_TIP,
                callback: function(r) {
                    if(!r) return;
                    
                    let params = {
                        refresh_interval: parseInt($('#refreshValue').val())
                    }

                    Metronic.blockUI({target: '.auto-refresh-form',animate: true});

                    pAjaxRequest(params, 'api/v1/s3/auto_refresh_interval', 'POST', (result) => {
                        if (result.success) {
                            UIToastr.showSuccess(LANG.UI_OBS_EDIT_REFRESH_INTERVAL_TIME_SUCCESS, '');

    			            $('#auto_refresh_obs_modal').modal('hide');
                        } else {
                            UIToastr.showWarning(LANG.UI_OBS_EDIT_REFRESH_INTERVAL_TIME, result.message);
                        }

                        Metronic.unblockUI('.auto-refresh-form');
                    });
                }
            });
        });

        // 点击高级搜索打开弹窗
        $('#advanceSearchBtn').on('click', function () {
            $('#advanced_search_vendor').val('');
            $('#advanced_search_status').val('');
            $('#advanced_search_endpoint_override').val('');
            $('#advanced_search_nickname').val('');
            $('#advanced_search_creator').val('');
            $('#advanced_search_owner').val('');
            $('#advanced_search_daterangepicker').val('');
            initDatatimePicker();

            $('#advanced_search_modal').modal('show');
        });

        // 高级搜索提交
        $('#advanceSearchSubmit').on('click', function () {
            advancedSearch = {
                start_time: _dateRangePicker_startTime,
                end_time: _dateRangePicker_endTime,
                vendor: $('#advanced_search_vendor').val(),
                status: $('#advanced_search_status').val(),
                endpoint_override: $('#advanced_search_endpoint_override').val(),
                nickname: $('#advanced_search_nickname').val(),
                creator: $('#advanced_search_creator').val(),
                owner: $('#advanced_search_owner').val()
            };

            addSearchContent();  // 显示搜索项

            $('#obstorage_table').bootstrapTable('refresh');
            $('#advanced_search_modal').modal('hide');
        });

        // 清除高级搜索内容
        $('#current_searchDiv .clearSearch').on('click', function () {
            $('#current_searchDiv .searchContent').text('');
            $('#current_searchDiv').hide();
            // 动态设置 table-container高度
            if ($('#obs_manager_tip').length > 0) { // 提示存在
                $('.resource-manager-wrap .resource-manager-wrap__content .table-container.obs-manager-table-container').css('height', 'calc(100% - 146px)');
            } else {
                $('.resource-manager-wrap .resource-manager-wrap__content .table-container.obs-manager-table-container').css('height', 'calc(100% - 46px)');
            }

            _dateRangePicker_startTime = '';
            _dateRangePicker_endTime = '';
            advancedSearch = {
                start_time: '',
                end_time: '',
                vendor: '',
                status: '',
                endpoint_override: '',
                nickname: '',
                creator: '',
                owner: ''
            };
            $('#advanced_search_daterangepicker').val('');
            $('#advanced_search_vendor').val('');
            $('#advanced_search_status').val('');
            $('#advanced_search_endpoint_override').val('');
            $('#advanced_search_nickname').val('');
            $('#advanced_search_creator').val('');
            $('#advanced_search_owner').val('');

            $('#obstorage_table').bootstrapTable('refresh');
        });

        // 修改对象存储密码输入框监听
        $('#password').on('input', () => {
            if (!addObsFormFlag) {
                let val = $('#password').val();

                if (val) {
                    $('#password_required').show();
                } else {
                    $('#password_required').hide();
                }
            }
        });

        // 打开授权modal
        $('#authOBStoage').on('click', () => {
            if ($('#auth_table').children().length > 0) {
                $('#auth_table').bootstrapTable('refresh');
            } else {
                initAuthTable();
            }

            initObsAuthInfo();
            $('#obsAuthModal').modal('show');
        });

        // 添加对象存储授权 
        $('#addObsAuth').on('click', () => {
            let selectedRows = $('#auth_table').bootstrapTable("getSelections");
            if (selectedRows.length == 0) {
                UIToastr.showInfo(LANG.UI_OBS_ADD_AUTH, LANG.UI_OBS_ADD_AUTH_TIPS);
                return;
            }

            let obs_ids = selectedRows.filter(i => i.authorization === 2).map(i => {return i.id});

            Metronic.blockUI({target: '.modal-body',animate: true});

            pAjaxRequest({obs_ids: obs_ids}, "/api/v1/s3/auth", "POST", (result) => {
                if (result.success) {
                    $('#obsAuthModal').modal('hide');
                    UIToastr.showSuccess(LANG.UI_OBS_ADD_AUTH_SUCCESS);
                    // 刷新表格
                    $('#obstorage_table').bootstrapTable('refresh');
                } else {
                    UIToastr.showWarning(LANG.UI_OBS_ADD_AUTH_FAILED, result.message);
                }
    
                Metronic.unblockUI('.modal-body');
            })
        });

        // 取消对象存储授权
        $('#cancelObsAuth').on('click', () => {
            let selectedRows = $('#auth_table').bootstrapTable("getSelections");
            if (selectedRows.length == 0) {
                UIToastr.showInfo(LANG.UI_OBS_CANCEL_AUTH, LANG.UI_OBS_CANCEL_AUTH_TIPS);
                return;
            }

            let obsids = selectedRows.filter(i => i.authorization === 1).map(i => {return i.id});

            Metronic.blockUI({target: '.modal-body',animate: true});

            pAjaxRequest({obsids: obsids}, "/api/v1/s3/auth", "DELETE", (result) => {
                if (result.success) {
                    $('#obsAuthModal').modal('hide');
                    UIToastr.showSuccess(LANG.UI_OBS_REMOVE_AUTH_SUCCESS);
                    // 刷新表格
                    $('#obstorage_table').bootstrapTable('refresh');
                } else {
                    UIToastr.showWarning(LANG.UI_OBS_REMOVE_AUTH_FAILED, result.message);
                }
    
                Metronic.unblockUI('.modal-body');
            })
        });

        $('#refreshValue').on('keydown', function(event) {
            if (event.key === 'Enter' || event.keyCode === 13) {
                event.preventDefault();
            }
        });

        // obs_manager_tip 关闭时动态设置表格高度
        $('#obs_manager_tip_close').on('click', () => {
            $('.resource-manager-wrap .resource-manager-wrap__content').css('padding-bottom', 0);

            if ($('#searchDiv').is(':visible')) { // search-content 存在
                // 86px = table-toolbar-wrapper 高度 34px + marin-bottom 12px + search-content的高度 40px
                $('.resource-manager-wrap .resource-manager-wrap__content .table-container.obs-manager-table-container').css('height', 'calc(100% - 86px)');
            } else {
                // 46px = table-toolbar-wrapper 高度 34px + marin-bottom 12px
                $('.resource-manager-wrap .resource-manager-wrap__content .table-container.obs-manager-table-container').css('height', 'calc(100% - 46px)');
            }
        });

        $('#refreshValue').spinner({value: 60, step: 1, min: 5, max: 9999});
    }

    /**
     * 初始化传输代理列表
     */
    const initApplianceAgency = () => {
        pAjaxRequest({offset: 0, limit: 1000, transfer_agent_flag: 1, h_online_status: 1}, 'api/v1/agents', 'GET', (result) => {
             if (result.success) {
                if (result.data.rows.length > 0) {
                     let data = result.data.rows;
                     let applianceSelect = $('#applianceSelect');
 
                     applianceSelect.empty();
             
                     for (let i = 0; i < data.length; i++){
                         let option = $("<option>").text(data[i].agent_ip).val(data[i].agent_uuid);
                         applianceSelect.append(option);
                     }

                     APPLIANCE_AGENCY_HAS_CONFIGED = true;
                } else {
                    APPLIANCE_AGENCY_HAS_CONFIGED = false;
                }
             } else {
                APPLIANCE_AGENCY_HAS_CONFIGED = false;

                UIToastr.showWarning(LANG.UI_OBS_GET_APPLIANCE_AGENCY_FAILED, result.message);
             }
        });
    }

    /**
     * 初始化获取自动刷新间隔时间
     */
    const initAutoRefreshInterval = () => {
        pAjaxRequest({}, 'api/v1/s3/auto_refresh_interval', 'GET', (result) => {
            if (result.success) {
                refreshTime = parseInt(result.data.obs_refresh_interval) / 60;

                $('#refreshValue').val(refreshTime);
                //初始化加减控件
                $('.obs-autorefresh-input-group').spinner({value: refreshTime, step: 1, min: 1,max: 9999});
            }
        });
    }

    /**
     * 初始化对象存储授权信息
     */
    const initObsAuthInfo = () => {
        pAjaxRequest({}, 'api/v1/s3/lisence_info', 'GET', (result) => {
            if (result.success) {
                licenceInfo = {...result.data};

                initObsAuthDes(licenceInfo);

                // if (result.data.licensetype === AUTH_TYPE.CAPACITY) { // 容量授权隐藏授权按钮
                //     $('#authOBStoage').hide();
                // } else {
                //     $('#authOBStoage').show();
                // }
            }
        });
    }

    const initObsAuthDes = (lisence) => {
        let des = '';
        if (lisence.licensetype === AUTH_TYPE.CAPACITY) { // 容量授权显示无限制
            des = LANG.UI_NAS_MANAGE_AUTH_DES + ": " + LANG.UI_SETTING_AUTH_UNLIMITED;
        } else {
            des = LANG.UI_CLIENT_AUTH_USED_TOTAL + ": " + (lisence.obs.valid < 0 ? '--' : lisence.obs.valid) + "/" + lisence.obs.used + "/" + lisence.obs.total;
        }

        $('#obsAuthDes').html(des);
    }

    const btnDisplayClass = () => {
        $("input[name='btSelectItem'], input[name='btSelectAll']").on('change',function (){
            let selectedRow = $('#obstorage_table').bootstrapTable("getSelections");
            if(selectedRow.length != 0){
                //如果有勾选 则改变图标颜色
                $(".grey_box_btn").addClass('grey_box_btn_hover');
            }else{
                $(".grey_box_btn").removeClass('grey_box_btn_hover');
            }
        })
    }

    const initAuthTable = () => {
        let options = {
            vin_url: "/api/v1/s3/auth",
            vin_method: "GET",
            searchInput: false,
            search: false,
            pagination: true,
            pageList: [10,20,50,100,150,200],
            showJumpTo: true,
            fullPage: true, //全屏表格高度适配
            sortName: 'obs_create_time',
            sortOrder: 'desc',
            onPostBody:function (){
                btnDisplayClass();
                $('[data-toggle="tooltip"]').tooltip();
            },
            onCheckAll: function (res) {
				modifyDelStyle('obstorage_table', 'deleteSelect');
			},
            onCheck: function (res) {
				modifyDelStyle('obstorage_table', 'deleteSelect');
			},
			onUncheck: function () {
				modifyDelStyle('obstorage_table', 'deleteSelect');
			},
            onUncheckAll: function () {
				modifyDelStyle('obstorage_table', 'deleteSelect');
			},
            columns:[
                {
                    checkbox: true,
                    sortable: false,
                    formatter: function (value, row, index) {
                        if (row.op_flag === false) { // 创建者等不是当前用户，不能操作
                            return {
                                disabled: true
                            };
                        }
                    }
                },
                {
                    field: "obs_nickname",
                    title: LANG.UI_PLATFORM_DES_OBS_NAME,
                    sortable:true,
                    align: "left"
                },
                {
                    field: "authorization",
                    title: LANG.UI_CLOUD_PLATFORM_AUTHORIZE_STATUS,
                    sortable: true,
                    align: "left",
                    formatter:function(value){
                        let labelHtml = "";
                        switch(value){
                            case 1:
                                labelHtml = '<span class="label label-sm label-success status-icon table-label_en width80_en">' +LANG.UI_CLOUD_PLATFORM_AUTHORIZED + '</span>';
                                break;
                            case 2:
                            case 0:
                                labelHtml = '<span class="label label-sm label-default status-icon table-label_en width80_en">' + LANG.UI_CLOUD_PLATFORM_UNAUTHORIZED + '</span>';
                                break;
                        }
                        return labelHtml;
                    }
                },
            ]
        }

        $('#auth_table').baseTableConfig().init(options);
    }

    return {
        init: function() {
            initObsAuthInfo();
            initDataTable(); // 初始化表格
            addListeners();
            initApplianceAgency();
            initAutoRefreshInterval();
            initDatatimePicker();
        }
    }
}();

$(document).ready(function () {
	obsManager.init();
});