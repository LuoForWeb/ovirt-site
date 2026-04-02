var organizationManager = function () {
    'use strict';
    var verifyFlag = false;//身份验证结果
    var op_id = '';
    var editFlag = false;//是否是修改组织信息
    var edit_uuid = '';//修改组织uuid
    var base64String = '';//证书的base64字符串
    var m365Data = [];//定义勾选资源存放
    var allPassword = {};//存放online的证书密码、Azure AD应用程序密码以及server的用户密码
    var initListener = function () {
        //点击复制按钮
        $('#addVerify .copy').on('click',function () {
            copyToClipboard($('#vertifyCode').val());
            //显示复制成功的提示信息，1s后隐藏提示信息
            $('.copy-tips').show();
            setTimeout(function () {
                $('.copy-tips').hide();
            },1000)
        });
        // 点击身份认证确认按钮
        $('#verify_submit').on('click',function () {
            verifySubmit();
        })
        //选择地区切换
        $('#regionType').on('change',function () {
            changeRegionType()
        });
        //选择类型切换
        $('#organizationType').on('change',function () {
            changeOrganizationType()
        });
        //连接组织的方式切换
        $('#conectionType').on('change',function () {
            changeConectionType()
        });
        //认证方式切换
        $('#authentication').on('change',function () {
            changeAuthentication()
        });
        //选择证书类型切换
        $('#certSelect').on('change',function () {
            changeCertSelect()
        });
        //上传证书
        $('#pfxCert').on('change',function () {
            for (var i of document.getElementById('pfxCert').files) {
                const reader = new FileReader();
                reader.readAsDataURL(i);
                reader.onload = () => {
                    base64String = reader.result.split(',')[1];//得到证书的base64编码
                };
                $('#fileList').html(i.name).attr('title',i.name);
            }
        });
        //去身份验证
        $('.verifyLink').on('click',function () {
            $('.vertify-waiting').show();
            $('#verify_submit').addClass('disabled');
            var region = parseInt($('#regionType').val());
            getAuthInfo(region);
        });
        //刷新验证码
        $('.vicon-a-Redozhongxin').on('click',function () {
            op_id = ''
            getVertifyCode();
        });
        $('#configSubmmit').on('click',function () {
            toRefreshTime();
        });
        //同步
        $('.m365-table').on('click', '.sync', syncOrganization);
        initServerAgent();
        $('#ADname').on('change',function () {
            if ($('#conectionType').val() == 1) {
                $('#nickname').val(this.value);
            }
        });
        $('#ADdomain').on('change',function () {
            if ($('#regionType').val() == 100) {//本地版填充AD域
                $('#nickname').val(this.value);
            }
        });

        // m365_tip 关闭时动态设置表格高度
        $('#m365_tip_close').on('click', () => {
            $('.resource-manager-wrap__content').css('padding-bottom', 0);
            $('.resource-manager-wrap .resource-manager-wrap__content .table-container.m365-table-contanier').css('height', 'calc(100% - 46px)');
        });
    }
    var syncOrganization = function () {
        let organization_uuid = $(this).attr('name');
        checkOperateAuth({
            type: 2,
            source_uuid: organization_uuid,
            source_type: 56
        },function(){
            var data = {};
            data.organization_uuid = organization_uuid;
            data.auto_refresh_flag = 2;
            data.m365_refresh_interval = parseInt($('#timeConfig').val())//刷新间隔时间,
            Metronic.blockUI({target: '.m365-table',animate: true});
            pAjaxRequest({"info": data}, "/api/v1/office365/organization/sync", "POST", function (result) {
                Metronic.unblockUI('.m365-table');
                operateResponseList(result,LANG.UI_MICROSOFT365_SYNC_ORGANIZATION)
                $('#table').bootstrapTable('refresh');
            });
        });
        
    }
    var toSearchOrganization = function () {
        $('#table').bootstrapTable('refresh', {
            query: {
                "search":$.trim($('#vin_m365_toolbar .m365Search').val())
            }
        });
    }
    var initServerAgent = function () {
        pAjaxRequest({}, "/api/v1/office365/organization/agent", "GET", function (result) {
            $('#agentConnect').html(result.data);
            //初始化exchangeserver客户端下拉框
            $(".selectpicker").selectpicker({
                liveSearch: true, //是否显示搜索框,
                actionsBox: false,//是否显示全部
                noneSelectedText: LANG.BILLING_PLEASE_SELECT,
                deselectAllText: LANG.BILLING_DESELECT_ALL,
                selectAllText: LANG.BILLING_SELECT_ALL,
                liveSearchPlaceholder: LANG.BILLING_SEARCH,
            });
        });
    }

    //复制内容到剪切板
    var copyToClipboard = function (str) {
        var el = document.createElement("textarea");
        el.value = str;
        el.setAttribute("readonly","");
        document.body.appendChild(el);
        el.select();
        document.execCommand("copy");
        document.body.removeChild(el);
    }

    var verifySubmit = function () {
        $('.verify-box').show();
        $('.vertify-outtime').hide();
        $('.vertify-success').hide();
        if (verifyFlag) {//身份验证成功
            $('#addVerify').modal('hide');
            if (editFlag) {
                toEditOrganization();// 提交修改组织的消息
            } else {
                toAddOrganization();// 提交添加组织的消息
            }
        }
    }
    var changeRegionType = function () {
        var type = parseInt($('#regionType').val());
        switch (type) {
            // 国际版和中国版类型只有online
            case 1:
            case 2:
                $("#organizationType option[value='2']").hide();
                $("#organizationType option[value='1']").show();
                $("#organizationType").val(1);
                changeOrganizationType();
                break;
                // 本地版只有server
            case 100:
                $("#organizationType option[value='2']").show();
                $("#organizationType option[value='1']").hide();
                $("#organizationType").val(2);
                changeOrganizationType();
                break;
        }
    }

    //组织类型切换 1： online    2： server
    var changeOrganizationType = function () {
        var type = parseInt($('#organizationType').val());
        switch (type) {
            case 1:
                $('.conectionTypeDiv').show();
                $('.authenticationDiv').show();
                $('.skip-cert-auth-form').hide();
                // $('.ADnameDiv').show();
                // $('.certSelectDiv').show();
                $('.ADdomainDiv').hide();
                $('.managerNameDiv').hide();
                $('.managerPwdDiv').hide();
                $('.agentConnectDiv').hide();
                changeConectionType();
                changeAuthentication();
                break;
            case 2:
                $('.conectionTypeDiv').hide();
                $('.authenticationDiv').hide();
                $('.skip-cert-auth-form').show();
                $('.ADnameDiv').hide();
                $('.certSelectDiv').hide();
                $('.pfxCertDiv').hide();
                $('.pfxCertPswDiv').hide();
                $('.ADdomainDiv').show();
                $('.managerNameDiv').show();
                $('.managerPwdDiv').show();
                $('.agentConnectDiv').show();
                $('.usernameDiv').hide();
                $('.tenantIdDiv').hide();
                $('.AzureIdDiv').hide();
                $('.AzurePswDiv').hide();
                break;
        }
    }
    //连接组织的方式切换   1: 自动注册    2: 使用现有的
    var changeConectionType = function () {
        var type = parseInt($('#conectionType').val());
        $('#certSelect option').show();
        $('#certSelect').val(1);
        changeCertSelect();
        switch (type) {
            case 1:
                $('.usernameDiv').hide();
                $('.tenantIdDiv').hide();
                $('.AzureIdDiv').hide();
                $('.ADnameDiv').show();
                $('.AzurePswDiv').hide();
                $('#marktips1').hide();
                $('#marktips2').hide();
                break;
            case 2:
                $('.usernameDiv').show();
                $('.tenantIdDiv').show();
                $('.AzureIdDiv').show();
                $('.ADnameDiv').hide();
                changeAuthentication();
                break;
        }
    }
    //认证方式方式切换   1: 基于密码    2: 基于证书
    var changeAuthentication = function () {
        var type = parseInt($('#authentication').val());
        switch (type) {
            case 2:
                $('.certSelectDiv').show();
                $('.AzurePswDiv').hide();
                if ($('#conectionType').val() == 2) {//使用现有的，证书方式
                    $('#marktips2').hide();
                    $('#marktips1').show();
                    $('#certSelect option[value="1"]').hide();//使用现有的，证书方式，暂时去掉自动生成证书
                    $('#certSelect').val(2);
                }
                if ($('#certSelect').val() != 2) {
                    $('.pfxCertDiv').hide();
                    $('.pfxCertPswDiv').hide();
                } else {
                    $('.pfxCertDiv').show();
                    $('.pfxCertPswDiv').show();
                }
                break;
            case 1:
                if ($('#conectionType').val() == 2) {//使用现有的，密码方式
                    $('.AzurePswDiv').show();
                    $('#marktips1').hide();
                    $('#marktips2').show();
                } else {
                    $('.AzurePswDiv').hide();
                }
                $('.pfxCertDiv').hide();
                $('.pfxCertPswDiv').hide();
                $('.certSelectDiv').hide();
                break;
        }
    }
    //选择证书类型切换   1: 自动生成    2: 导入pfx证书
    var changeCertSelect = function () {
        var type = parseInt($('#certSelect').val());
        switch (type) {
            case 1:
                $('.pfxCertDiv').hide();
                $('.pfxCertPswDiv').hide();
                break;
            case 2:
                if ($('#authentication').val() == 2) {
                    $('.pfxCertDiv').show();
                    $('.pfxCertPswDiv').show();
                    $('.AzurePswDiv').hide();
                }
                break;
        }
    }

    //初始化表格
    var initDataTable = function () {
        var afterDiv = '';
        var beforeDiv = '';
        if ($.inArray('p_exchange_organization_delete', CONF.PERMISSION_ARR) !== -1 ) {
            beforeDiv = '<div style="cursor:not-allowed;"><button class="btn viconfont vicon-a-Deleteshanchu1 b-btn brr2 mr12 exch-forbid-event" id="delete-organization"></button></div>';
        }
        if ($.inArray('p_exchange_organization_add', CONF.PERMISSION_ARR) !== -1 ){
            afterDiv += `<button class="btn table-toolbar-btn" id="add-organization" data-toggle="drawer" data-target="#add_user_drawer" aria-haspopup="true" aria-expanded="false" >
                                <i class="viconfont vicon-biaogetianjia mr4"></i>
                                <span>` + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_ADD + `</span>
                            </button>`;
        }
        if ($.inArray('p_exchange_organization_modify', CONF.PERMISSION_ARR) !== -1 ){
            afterDiv += `<button class="btn table-toolbar-btn" id="edit-organization" >
                                <i class="viconfont vicon-xiugai mr4"></i>
                                <span>` + LANG.UI_JOB_MODIFY + `</span>
                            </button>`;
        }
        if ($.inArray('p_exchange_organization_refresh', CONF.PERMISSION_ARR) !== -1 ) {
            afterDiv += `<button class="btn table-toolbar-btn" id="refresh">
                                <i class="viconfont vicon-ge_refresh mr4"></i>
                                <span>` + LANG.UI_MICROSOFT365_AUTO_REFRESH_CONFIGURE + `</span>
                            </button>`;
        }
        afterDiv = afterDiv != '' ? '<div>' + afterDiv + '</div>' : '';
        var lastIndex = [-1, -1];
        var options = {
            detailFormatter:function (row,data,div) {
                if (row != lastIndex[1]) {//只展开一行
                    lastIndex.push(row);
                    $('#table').bootstrapTable('collapseRow', lastIndex[lastIndex.length - 2]);
                    lastIndex.splice(0, 1);
                }
                var html = '';
                if (data.region == 100) {//本地版
                    html += '<div class="col-md-3 detail-padding">' +
                                '<div>'+ LANG.UI_MICROSOFT365_AD_DOMAIN +'：' + data.server_info[0].app_detail + '</div>' +
                            ' </div>';
                    html += '<div class="col-md-3 detail-padding">' +
                                '<div>'+ LANG.UI_MICROSOFT365_ADMINISTRATOR_ACCOUNT +'：' + data.server_info[0].app_username + '</div>' +
                            '</div>';
                } else {//online
                    html += '<div class="col-md-2 detail-padding">' +
                                '<div>'+ LANG.UI_MICROSOFT365_VERIFY_WAY +'：' + data.verify_way + '</div>' +
                            ' </div>';
                    html += '<div class="col-md-3 detail-padding">' +
                                '<div>'+ LANG.UI_MICROSOFT365_USER_NAME +'：' + data.username + '</div>' +
                            ' </div>';
                }
                return html;
            },
            toolbarId: '#vin_m365_toolbar',
            buttonsToolbar: '#vin_m365_toolbar .vin_btnToolbar',
            placeholder: LANG.BILLING_SEARCH, //搜索框的placeholder
            searchInput: true, //搜索框
            searchClass: 'm365Search', //自定义的搜索框类名
            searchSelector: '.m365Search', //选择使用自定义搜索框
            pagination:true,
            pageList:[10,20,50,100,150,200],
            detailView:true,
            fullPage:true, //全屏表格高度适配
            // pa:{},
            vin_url:"/api/v1/office365/organization",
            vin_method:"GET",
            customTool: {
                beforeInput: beforeDiv,
                afterInput: afterDiv,
            },
            columns:[{
                checkbox:true,
                sortable: false,
                formatter: function (value, row, index, field) {
                    if (row.op_flag === false) {//创建者等不是当前用户，不能操作
                        return {
                            disabled: true
                        };
                    }
                    for(var i=0; i<m365Data.length; i++) {
                        if(row.organization_uuid == m365Data[i]){
                            return true;
                        }
                    }
                }
            },
            {
                field: 'organization_name',
                title: LANG.UI_MICROSOFT365_ORGANIZATION_NAME,
                sortable: true,
                align: 'center',
            },
            {
                field: 'nickname',
                title: LANG.UI_MICROSOFT365_NICK_NAME,
                sortable: true,
                align: 'center',
            },
            {
                field: 'agent_list',
                title: LANG.UI_MICROSOFT365_CLIENT_IP,
                sortable: true,
                align: 'center',
            },
            {
                field: 'app_auth',
                title: LANG.UI_CLIENT_APP_CONFIG,
                sortable: true,
                align: 'center',
                formatter: function (value,row,index,field) {
                    if (value == null) {
                        return '--';
                    }
                    var div = '';
                    if (value['is_exch_auth'] == '1') {
                        div += '<i class="viconfont vicon-module-exchange c0FBF98" style="font-size: 22px;"></i>';
                    }
                    if (value['is_onedrive_auth'] == '1') {
                        div += '<i class="viconfont vicon-module-exchange c0FBF98" style="font-size: 22px;"></i>';
                    }
                    if (value['is_sharepoint_auth'] == '1') {
                        div += '<i class="viconfont vicon-module-exchange c0FBF98" style="font-size: 22px;"></i>';
                    }
                    if (value['is_teams_auth'] == '1') {
                        div += '<i class="viconfont vicon-module-exchange c0FBF98" style="font-size: 22px;"></i>';
                    }
                    return div == '' ? '--' : div;
                }
            },
            {
                field: 'create_time',
                title: LANG.UI_PUBLIC_ADD_TIME,
                sortable: true,
                align: 'center',
            },
            {
                field: 'type',
                title: LANG.UI_COPY_TYPE,
                sortable: true,
                align: 'center',
            },
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
                field: 'online_flag',
                title: LANG.UI_MICROSOFT365_ONLINE_FLAG,
                sortable: true,
                align: 'center',
                formatter: function (value,data,row) {
                    let des = '';
                    switch (value) {
                        case 1:
                            des = '<span class="label label-sm label-success status-icon">'+ LANG.UI_VISUAL_ONLINE +'</span>';
                            break;
                        case 2:
                            des = '<span class="label label-sm label-default status-icon">'+ LANG.UI_VISUAL_OFF_LINE +'</span>';
                            break;
                    }
                    return des;
                }
            },
                {
                    field: 'refresh_status',
                    title: LANG.UI_MICROSOFT365_SYNC_FLAG,
                    sortable: true,
                    align: 'center',
                    formatter: function (value,data,row) {
                        if (value == 1) {
                            return '<span class="label label-sm label-default status-icon">'+ LANG.UI_MICROSOFT365_NOT_SYNC +'</span>';
                        } else if (value == 2) {
                            return '<span class="label label-sm label-info status-icon">'+ LANG.UI_MICROSOFT365_SYNCING +'</span>';
                        } else if (value == 3) {
                            return '<span class="label label-sm label-success status-icon">'+ LANG.UI_MICROSOFT365_SYNC_END +'</span>';
                        }
                    }
            },
                {
                    field: 'sync_time',
                    title: LANG.UI_MICROSOFT365_SYNC_TIME,
                    sortable: true,
                    align: 'center',
            },
                {
                    field: 'operate',
                    title: LANG.UI_PUBLIC_OPERATION,
                    sortable: false,
                    align: 'center',
                    formatter: function (value,data,row) {
                        //有操作权限、创建者是当前用户才显示操作
                        if ($.inArray('p_exchange_organization_sync', CONF.PERMISSION_ARR) !== -1 && data.op_flag) {
                            return '<button style="padding: 5px 8px;" type="button" class="btn green-haze btn-sm sync" name="' + data.organization_uuid + '"><i style="font-size: 14px;" class="viconfont vicon-ge_refresh"></i> '+ LANG.UI_MICROSOFT365_SYNC+'</button>';
                        } else {
                           return '-';
                        }

                    }
            },
            ],
            onCheck: function (row) {
                modifyDelStyle('table', 'delete-organization');
                m365Data.push(row.organization_uuid);
            },
            onUncheck: function (row) {
                modifyDelStyle('table', 'delete-organization');
                var index = m365Data.indexOf(row.organization_uuid); // 查找元素的索引
                if (index !== -1) {
                    m365Data.splice(index, 1); // 从数组中删除一个元素
                }
            },
            onCheckAll:function (row) {
                modifyDelStyle('table', 'delete-organization');
                for (var i = 0; i < row.length; i++) {
                    var index = m365Data.indexOf(row[i].organization_uuid); // 查找元素的索引
                    if (index == -1) {
                        m365Data.push(row[i].organization_uuid)
                    }
                }
            },
            onUncheckAll: function (row) {
                modifyDelStyle('table', 'delete-organization');
                for (var i = 0; i < row.length; i++) {
                    var index = m365Data.indexOf(row[i].organization_uuid); // 查找元素的索引
                    if (index != -1) {
                        m365Data.splice(index, 1); // 从数组中删除一个元素
                    }
                }
            },
            onPostBody: function () {
                let tableData = $('#table').bootstrapTable('getData');

                if (tableData.length === 0) { // 空data保证fixed-table-container高度100%，以消除 bs-table 中计算的乱七八糟的错误高度
                    $('.resource-manager-wrap .resource-manager-wrap__content .table-container .bootstrap-table .fixed-table-container').css('height', '100%');
                } else {
                    // 52px 是 分页 fixed-table-pagination 的高度
                    $('.resource-manager-wrap .resource-manager-wrap__content .table-container .bootstrap-table .fixed-table-container').css('height', 'calc(100% - 52px)');
                }
            }
        }
        sessionStorage.removeItem("table_pageRecord");
        $('.m365-table #table').baseTableConfig().init(options);
        modifyDelStyle('table', 'delete-organization');
        //绑定事件
        toBindEvent();
    }
    var toBindEvent = function () {
        //添加组织
        $('#add-organization').on('click', function () {
            addOrganization();
        });
        //修改组织
        $('#edit-organization').on('click', function () {
            var selectedRow = $('.m365-table #table').bootstrapTable('getSelections');
            if (selectedRow.length > 1 || selectedRow.length == 0) {
                UIToastr.showWarning(LANG.UI_MICROSOFT365_EDIT_ORGANIZATION_INFO, LANG.UI_MICROSOFT365_EDIT_ORGANIZATION_INFO_TIPS);
                return;
            }
            checkOperateAuth({
                type: 2,
                source_uuid: selectedRow[0].organization_uuid,
                source_type: 56
            },function(){
                editOrganization(selectedRow);
            });
        });
        //删除组织
        $('#delete-organization').on('click', function () {
            var selectedRow = $('.m365-table #table').bootstrapTable('getSelections');
            if (selectedRow.length == 0) {
                UIToastr.showWarning(LANG.UI_MICROSOFT365_DELETE_ORGANIZATION, LANG.UI_MICROSOFT365_DELETE_ORGANIZATION_TIPS);
                return;
            }
            checkOperateAuth({
                type: 2,
                source_uuid: selectedRow.map(row => row.organization_uuid).join(','),
                source_type: 56
            },function(){
                deleteOrganization(selectedRow);
            });
        });
        //自动刷新时间配置
        $('#refresh').on('click', function () {
            $('#refreshModal').modal({'width': '550px', 'height': '100%'});
            //自动刷新间隔时间
            refreshOganization();
        });
        //搜索
        $('#vin_m365_toolbar').on('click', ' .b-btn.search-btn', function () {
            toSearchOrganization()
        });

        $('#vin_m365_toolbar .clear').on('click',function(){
            $('#vin_m365_toolbar .m365Search').val('');
            $('#table').bootstrapTable('refresh', {
                query: {
                    "search": ''
                }
            });
        });
    }
    //添加组织
    var addOrganization = function () {
       //英文版需要特殊处理宽度
        if (CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw") {
            $('#m365AddModal').modal({'width': '760px', 'height': '100%'});
        }else{
            $('#m365AddModal').modal({'width': '810px', 'height': '100%'});
        }
        //清空输入框
        $('#regionType').val(1);
        $('#regionType').attr('disabled',false);//添加组织移除禁用
        $('#conectionType').val(1).attr("disabled",false);
        $('#ADdomain').attr("disabled",false);
        $('#organizationType').val(1)
        $('#conectionType').val(1);//是否选择自动注册应用: 1是  2否
        $('#authentication').val(1);//密码1 证书2
        $('#userName').val('');
        $('#tenantId').val('');
        $('#AzureId').val('');
        $('#AzurePsw').val('');
        $('#nickname').val('');
        $('#nickname').css({"border-color":"#E6E6E6"});
        $('#ADname').val('');//Azure AD应用程序名
        $('#certSelect').val(1);//自动生成证书1  导入2
        changeCertSelect();
        $("#fileList").text('');
        $('#pfxCert').val('');
        $("#pfxCertPsw").val('');
        $('#ADdomain').val('');
        $('#managerName').val('');
        $('#managerPwd').val('');
        $('#agentConnect').selectpicker('val','');
        $('.edit-title').hide();
        $('.add-title').show();
        editFlag = false;
        changeOrganizationType();
        //初始化显示第一步
        $('#m365AddModal').bootstrapWizard('first');
    }
    //修改组织
    var editOrganization = function (selectedRow) {
        //英文版需要特殊处理宽度
        if (CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw") {
            $('#m365AddModal').modal({'width': '760px', 'height': '100%'});
        }else{
            $('#m365AddModal').modal({'width': '810px', 'height': '100%'});
        }
        editFlag = true;
        $('.edit-title').show();
        $('.add-title').hide();
        //初始化显示第一步
        $('#m365AddModal').bootstrapWizard('first');
        edit_uuid = selectedRow[0].organization_uuid;
        $('#regionType').val(selectedRow[0].region);
        $('#regionType').attr('disabled',true);//修改组织不允许修改地区
        $('#organizationType').val(selectedRow[0].type_value);
        changeRegionType();
        $('#nickname').val(selectedRow[0].nickname == '--' ? '' : selectedRow[0].nickname);
        switch (selectedRow[0].type_value) {
            case 1://online
                $('#conectionType').val(2).attr("disabled",true);//修改--默认是使用现有的AD
                changeConectionType();
                $('#authentication').val(selectedRow[0].verify_value);
                changeAuthentication();
                $('#ADname').val(selectedRow[0].app_name);
                $('#userName').val(selectedRow[0].username);
                $('#tenantId').val(selectedRow[0].tenant_uuid);
                $('#AzureId').val(selectedRow[0].app_uuid);
                $('#AzurePsw').val('').attr("placeholder",LANG.UI_PUBLIC_MODIFY_PASSWORD_TIPS);
                allPassword.app_secret = btoa(selectedRow[0].app_secret);
                $('#pfxCertPsw').val('').attr("placeholder",LANG.UI_PUBLIC_MODIFY_PASSWORD_TIPS);
                allPassword.cert_password = btoa(selectedRow[0].cert_password);
                if (selectedRow[0].verify_value == 1) {//密码方式
                    changeCertSelect();
                } else {//证书方式
                    $('.certSelectDiv').show();
                    $('#certSelect').val(2);//修改--默认是导入证书
                    changeCertSelect();
                    $('#fileList').html(selectedRow[0].cert_name).attr('title',selectedRow[0].cert_name);
                    base64String = selectedRow[0].cert_content;
                }
                break;
            case 2://server
                $('#ADdomain').val(selectedRow[0].server_info[0].app_detail);
                $('#managerName').val(selectedRow[0].server_info[0].app_username);
                $('#managerPwd').val('').attr("placeholder",LANG.UI_PUBLIC_MODIFY_PASSWORD_TIPS);;
                allPassword.password = btoa(selectedRow[0].server_info[0].app_password);
                $('#agentConnect').selectpicker('val',selectedRow[0].agentConnect_value);
                $('#ADdomain').attr("disabled",true);
                $('#skip_cert_auth_flag').bootstrapSwitch('state', selectedRow[0].skip_cert_auth_flag); 
                break;
        }

    }
    //修改组织提交
    var editSubmit = function () {
        if (!checkParams()) {//参数检测
            return;
        }
        Metronic.blockUI({target: '#m365AddModal',animate: true,cenrerY: true,});
        verifyFlag = false
        toEditOrganization();// 提交添加组织的消息
    }
    //修改组织接口
    var toEditOrganization = function () {
        var params = {}
        params.type = $('#organizationType').val();
        params.add_op_id = '';
        params.organization_uuid = edit_uuid;
        if ($('#organizationType').val() == 1) {//online
            params.online_info = {};
            params.server_info = [];
            params.online_info.auth_apps = {};
            params.online_info.app_cert_info = {};
            params.online_info.auto_create_azure_ad_app_info = {};
            params.online_info.username = $('#userName').val();
            params.online_info.region = parseInt($('#regionType').val());
            params.online_info.tenant_uuid = $('#tenantId').val();
            params.online_info.app_uuid = $('#AzureId').val();
            params.online_info.app_secret = $('#AzurePsw').val().trim() == "" ? "" : btoa($('#AzurePsw').val());
            params.online_info.app_cert_info.cert_name = $("#fileList").text();
            params.online_info.app_cert_info.cert_finger = '';
            params.online_info.app_cert_info.cert_content = base64String;
            params.online_info.app_cert_info.cert_password = $('#pfxCertPsw').val().trim() == "" ? "" : btoa($('#pfxCertPsw').val());//证书密码
            params.online_info.auth_apps.is_exch_auth = 1;
            params.online_info.auto_create_azure_ad_app_info.app_name = btoa(encodeURIComponent($('#ADname').val()));//Azure AD应用程序名
            params.online_info.auto_create_azure_ad_app_info.auto_create_app = parseInt($('#conectionType').val());//是否选择自动注册应用: 1是  2否
            params.online_info.auto_create_azure_ad_app_info.use_secret = parseInt($('#authentication').val());//密码1 证书2
            params.online_info.auto_create_azure_ad_app_info.auto_generate_cert = parseInt($('#certSelect').val());//自动生成证书1  导入2
            params.nickname = $('#nickname').val().replace(/\s+/g, '');
            params.nicknameFlag = getNicknameFlag('online');
        } else {
            params.server_info = {};
            params.online_info = [];
            params.server_info.agent_uuid_list = $('#agentConnect').selectpicker('val');
            params.server_info.domain = $('#ADdomain').val();
            params.server_info.username = $('#managerName').val();
            params.server_info.password = $('#managerPwd').val().trim() == "" ? "" : signEncrypt(btoa($('#managerPwd').val()));
            params.nickname = $('#nickname').val().replace(/\s+/g, '');
            params.nicknameFlag = getNicknameFlag('server');
            params.server_info.skip_cert_auth_flag = $('#skip_cert_auth_flag').get(0).checked ? 1 : 2;
        }
        params.all_password = allPassword;
        pAjaxRequest(params, "/api/v1/office365/organization/" + edit_uuid, "PUT", function (result) {
            if (result.data.result) {
                $('#m365AddModal').modal('hide');
                $('#addVerify').modal('hide');
                UIToastr.showSuccess(LANG.UI_MICROSOFT365_EDIT_ORGANIZATION_INFO, LANG.UI_MICROSOFT365_EDIT_ORGANIZATION_INFO_SUCCESS);
                $('.m365-table #table').bootstrapTable('refresh');
            } else {
                UIToastr.showWarning(LANG.UI_MICROSOFT365_EDIT_ORGANIZATION_INFO, LANG.UI_MICROSOFT365_EDIT_ORGANIZATION_INFO_FAIL);
            }
            Metronic.unblockUI('#m365AddModal');
        });
    }

    //判断是否只修改了别名
    var getNicknameFlag = function (type) {
        // 获取修改之前的值
        var selectedRow = $('.m365-table #table').bootstrapTable('getSelections');
        if (type == 'online') {
            if ($('#authentication').val() != selectedRow[0].verify_value) {
                return false;
            }
            if ($('#ADname').val() != selectedRow[0].app_name) {
                return false;
            }
            if ($('#userName').val() != selectedRow[0].username) {
                return false;
            }
            if ($('#tenantId').val() != selectedRow[0].tenant_uuid) {
                return false;
            }
            if ($('#AzureId').val() != selectedRow[0].app_uuid) {
                return false;
            }
            if (selectedRow[0].verify_value == 1) {//密码方式
                if ($('#AzurePsw').val().trim() != '') {
                    return false;
                }
            } else {//证书方式
                if ($('#fileList').val() != selectedRow[0].cert_name) {
                    return false;
                }
                if ($('#pfxCertPsw').val().trim() != "") {
                    return false;
                }
                if (base64String != selectedRow[0].cert_content) {
                    return false;
                }
            }
        }
        if (type == 'server') {
            if ($('#ADdomain').val() != selectedRow[0].server_info[0].app_detail) {
                return false;
            }
            if ($('#managerName').val() != selectedRow[0].server_info[0].app_username) {
                return false;
            }
            if ($('#managerPwd').val().trim() != "") {
                return false;
            }
            if (!arraysEqual($('#agentConnect').selectpicker('val'), selectedRow[0].agentConnect_value)) {
                return false;
            }
            if ($('#skip_cert_auth_flag').get(0).checked != selectedRow[0].skip_cert_auth_flag) {
                return false;
            }
        }
        return true;
    }

    //判断两个数组是否相等
    var arraysEqual = function (a, b) {
        if (a === b) {
            return true;
        }
        if (a == null || b == null) {
            return false;
        }
        if (a.length !== b.length) {
            return false;
        }

        for (let i = 0; i < a.length; ++i) {
            if (a[i] !== b[i]) {
                return false;
            }
        }
        return true;
    }

    //删除组织
    var deleteOrganization = function (selectedRow) {
        var uuid = [];
        var des = LANG.UI_MICROSOFT365_DELETE_ORGANIZATION_TIPS1 + '<br>'+ LANG.UI_MICROSOFT365_ORGANIZATION_NAME +'：'
        selectedRow.forEach(item => {
            des += item.organization_name + ';&nbsp;';
            uuid.push(item.organization_uuid);
        });
        bootbox.confirm({
            title: LANG.UI_MICROSOFT365_DELETE_ORGANIZATION,
            message: des,
            callback: debounce(function (r) {
                if (!r) {
                    return;
                }
                Metronic.blockUI({target: '.m365-table #table',animate: true,cenrerY: true,});
                pAjaxRequest({organization_uuid:uuid}, "/api/v1/office365/organization", "DELETE", function (result) {
                    if (operateResponseList(result,LANG.UI_MICROSOFT365_DELETE_ORGANIZATION)) {
                        $('#delete-organization').addClass('exch-forbid-event');
                        $('.del-parent-div').css({"cursor": "not-allowed"});
                    }
                    $('.m365-table #table').bootstrapTable('refresh');
                    Metronic.unblockUI('.m365-table #table');
                });
            }, 300),
        });
    }
    //获取Microsoft365组织自动刷新间隔时间
    var refreshOganization = function () {
        Metronic.blockUI({target: '#refreshModal',animate: true});
        pAjaxRequest({}, "/api/v1/office365/organization/refresh", "GET", function (result) {
            if (result.success) {
                $('#timeConfig').val(parseInt(result.data / 60))
            }
            Metronic.unblockUI('#refreshModal');
        });
    }
    //添加组织的步骤
    var wizardInit = function () {
        if (!jQuery().bootstrapWizard) {
            return;
        }
        var form = $('#submit_form');
        var error = $('.alert-danger', form);
        var success = $('.alert-success', form);
        var handleTitle = function (tab, navigation, index) {
            var total = navigation.find('li').length;//总共的步骤数
            var current = index + 1;      //当前步骤
            // set wizard title
//            $('.step-title', $('#vmbackupcontent')).text('Step ' + (index + 1) + ' of ' + total);
            // set done steps
            jQuery('li', $('#m365AddModal')).removeClass("done");
            var li_list = navigation.find('li');
            for (var i = 0; i < index; i++) {
                jQuery(li_list[i]).addClass("done");
            }

            //如果第一步 上一步按钮隐藏
            if (current == 1) {
                $('#m365AddModal').find('.button-previous').hide();
                $('#m365AddModal').find('.button-next').addClass('next-btn-margin-left');
            } else {
                $('#m365AddModal').find('.button-previous').show();
                $('#m365AddModal').find('.button-next').removeClass('next-btn-margin-left');
            }
            //如果是最后一步
            if (current >= total) {
                $('#m365AddModal').find('.button-next').hide();
                if (editFlag) {
                    $('#m365AddModal').find('#edit_submit').show();
                    $('#m365AddModal').find('#add_submit').hide();
                } else {
                    $('#m365AddModal').find('#add_submit').show();
                    $('#m365AddModal').find('#edit_submit').hide();
                }
            } else {
                $('#m365AddModal').find('.button-next').show();
                $('#m365AddModal').find('.button-submit').hide();
            }
            Metronic.scrollTo($('.page-title'));
        }
        // default form wizard
        $('#m365AddModal').bootstrapWizard({
            'nextSelector': '.button-next,#btInstance',
            'previousSelector': '.button-previous',
            onTabClick: function (tab, navigation, index, clickedIndex) {
                return false;
            },
            //下一步
            onNext: function (tab, navigation, index) {
                success.hide();
                error.hide();
                switch (index) {
                    case 1:
                        if (step1Valid() == false) {
                            return false;
                        }
                        break;
                }
                handleTitle(tab, navigation, index);
            },
            //上一步
            onPrevious: function (tab, navigation, index) {
                success.hide();
                error.hide();
                handleTitle(tab, navigation, index);
            },
            //进度条显示
            onTabShow: function (tab, navigation, index) {
                var total = navigation.find('li').length;
                var current = index + 1;
                var $percent = (current / total) * 100;
                $('#m365AddModal').find('.progress-bar').css({
                    width: $percent + '%'
                });
            },
            //回退到第一步
            onFirst: function (tab, navigation, index) {
                success.hide();
                error.hide();
                handleTitle(tab, navigation, index);
            },
        });
        $('#m365AddModal').find('.button-previous').hide();
        $('#m365AddModal #add_submit').click(submit).hide();
        $('#m365AddModal #edit_submit').click(editSubmit).hide();
    };

    //第一步
    var step1Valid = function () {
        return true;
    }
    var submit = function () {
        if (!checkParams()) {//参数检测
            return;
        }
        if ($('#organizationType').val() == 2) {//server
            toAddOrganization();// 提交添加组织的消息
            return;
        }
        var conectionType = parseInt($('#conectionType').val());
        switch (conectionType) {
            case 1:// 连接方式是自动注册新的AzureAD应用程序，需要进行身份验证
                $('#addVerify').modal({'width': '760px', 'height': '100%'});
                setTimeout(function () {
                    getVertifyCode();
                }, 1000); // 延迟1秒钟执行
                break;
            case 2:// 连接方式是使用已有的AzureAD应用程序
                toAddOrganization();// 提交添加组织的消息
                break;
        }

    }

    //调用添加接口
    var toAddOrganization = function () {
        Metronic.blockUI({target: '#m365AddModal',animate: true,cenrerY: true,});
        var params = {}
        params.type = $('#organizationType').val();
        params.add_op_id = '';
        if ($('#organizationType').val() == 1) {//online
            params.online_info = {};
            params.server_info = [];
            params.online_info.auth_apps = {};
            params.online_info.app_cert_info = {};
            params.online_info.auto_create_azure_ad_app_info = {};
            params.online_info.username = $('#userName').val();
            params.online_info.region = parseInt($('#regionType').val());
            params.online_info.tenant_uuid = $('#tenantId').val();
            params.online_info.app_uuid = $('#AzureId').val();
            params.online_info.app_secret = btoa($('#AzurePsw').val());
            params.online_info.app_cert_info.cert_name = $("#fileList").text();
            params.online_info.app_cert_info.cert_finger = '';
            params.online_info.app_cert_info.cert_content = base64String;
            params.online_info.app_cert_info.cert_password = btoa($('#pfxCertPsw').val());//证书密码
            params.online_info.auth_apps.is_exch_auth = 1;
            params.online_info.auto_create_azure_ad_app_info.app_name = btoa(encodeURIComponent($('#ADname').val()));//Azure AD应用程序名
            params.online_info.auto_create_azure_ad_app_info.auto_create_app = parseInt($('#conectionType').val());//是否选择自动注册应用: 1是  2否
            params.online_info.auto_create_azure_ad_app_info.use_secret = parseInt($('#authentication').val());//密码1 证书2
            params.online_info.auto_create_azure_ad_app_info.auto_generate_cert = parseInt($('#certSelect').val());//自动生成证书1  导入2
            params.online_info.nickname = $('#nickname').val().replace(/\s+/g, '');
        } else {
            params.server_info = {};
            params.online_info = [];
            params.server_info.agent_uuid_list = $('#agentConnect').selectpicker('val');
            params.server_info.domain = $('#ADdomain').val();
            params.server_info.username = $('#managerName').val();
            params.server_info.password = signEncrypt(btoa($('#managerPwd').val()));
            params.server_info.nickname = $('#nickname').val().replace(/\s+/g, '');
            params.server_info.skip_cert_auth_flag = $('#skip_cert_auth_flag').get(0).checked ? 1 : 2;
        }
        addOrganiazation(params);
    }

    var addOrganiazation = function (params) {
        pAjaxRequest(params, "/api/v1/office365/organization", "POST", function (result) {
            if (result.data.op_status == 1) {//运行中
                params.add_op_id = result.data.add_op_id;
                setTimeout(function () {
                    addOrganiazation(params);
                },3000)
            } else if (result.data.op_status == 2) {//添加成功
                Metronic.unblockUI('#m365AddModal');
                $('.m365-table #table').bootstrapTable('refresh');
                UIToastr.showSuccess(LANG.UI_MICROSOFT365_ADD_ORGANIZATION, LANG.UI_MICROSOFT365_ADD_ORGANIZATION_SUCCESS);
                $('#m365AddModal').modal('hide');
            } else if (result.data.op_status == 3) {//失败
                Metronic.unblockUI('#m365AddModal');
                $('.m365-table #table').bootstrapTable('refresh');
                UIToastr.showWarning(LANG.UI_MICROSOFT365_ADD_ORGANIZATION, LANG.UI_MICROSOFT365_ADD_ORGANIZATION_FAIL + result.data.des);
                $('#m365AddModal').modal('hide');
            }
        }, false);
    }

    //请求用于身份验证opid和验证码
    var getVertifyCode = function () {
            Metronic.blockUI({target: '#addVerify',animate: true, cenrerY: true,});
            var region = parseInt($('#regionType').val());
            pAjaxRequest({'region':region,'op_id_flag':true }, "/api/v1/office365/organization/auth_code", "GET", function (result) {
                //获取到opid
                if (result.data.op_status == 0) {
                    verifyFlag = false;
                    op_id = result.data.op_id;
                    pAjaxRequest({'region':region,'vertify_code_flag':true,'op_id':op_id }, "/api/v1/office365/organization/auth_code", "GET", function (result) {
                        Metronic.unblockUI('#addVerify');
                        //获取验证码
                        if (result.data.op_status == 1) {
                            $('#vertifyCode').val(result.data.vertify_code);
                        } else {
                            UIToastr.showWarning(LANG.UI_MICROSOFT365_AUTH_FAIL);
                        }
                    });
                }
            }, false);
    }
    //进行身份验证
    var getAuthInfo = function (region) {
        pAjaxRequest({'region':region,'auth_flag':true,'op_id':op_id }, "/api/v1/office365/organization/auth_code", "GET", function (result) {
            //进行身份验证
            if (result.data.op_status == 2) {
                $('.verify-box').hide();
                $('.vertify-waiting').hide();
                $('#userName').val(result.data.user);
                $('#tenantId').val(result.data.tenant_uuid);
                $('.vertify-success').html('<i class="viconfont vicon-danchuangshouquan c0FBF98"></i><br><div class="success-text">' + result.data.user + '<br>' + LANG.UI_MICROSOFT365_VERIFY_THROUGH_AUTHENTICATION +'</div>').show();
                verifyFlag = true;
                $('#verify_submit').removeClass('disabled');
            } else if (result.data.op_status == 3) {
                //失败
                $('.vertify-outtime').show();
                $('.vertify-waiting').hide();
                $('.vertify-success').hide();
                UIToastr.showWarning(LANG.UI_MICROSOFT365_AUTH, result.message);
                verifyFlag = false;
            } else if (result.data.op_status == 1) {
                //请求中
                setTimeout(function () {
                    getAuthInfo(region, op_id);
                },2000)
                verifyFlag = false;
            }

        });
    }
    //更新Microsoft365组织自动刷新间隔时间
    var toRefreshTime = function () {
        var refresh_time = parseFloat($.trim($('#timeConfig').val()));
        if (!refresh_time || refresh_time < 30) {
            UIToastr.showWarning(LANG.UI_MICROSOFT365_CONFIGURE_INTERVAL_FAIL,LANG.UI_MICROSOFT365_CONFIGURE_INTERVAL_FAIL_TIPS1);
            $('#timeConfig').val('60');
            return;
        }
        if (!Number.isInteger(refresh_time) || refresh_time.toString().length > 4) {
            UIToastr.showWarning(LANG.UI_MICROSOFT365_CONFIGURE_INTERVAL_FAIL,LANG.UI_MICROSOFT365_CONFIGURE_INTERVAL_FAIL_TIPS2);
            $('#timeConfig').val('60');
            return;
        }
        Metronic.blockUI({target: '#refreshModal',animate: true});
        pAjaxRequest({"refresh_time": refresh_time}, "/api/v1/office365/organization/refresh", "PUT", function (result) {
            if (result.data) {
                $('#refreshModal').modal('hide');
                UIToastr.showSuccess(LANG.UI_MICROSOFT365_CONFIGURE_INTERVAL,LANG.UI_MICROSOFT365_CONFIGURE_INTERVAL_SUCCESS);
            }
            Metronic.unblockUI('#refreshModal');
        });
    }

    var checkParams = function () {
        var nickname = $.trim($('#nickname').val());
        var ADname = $.trim($('#ADname').val());
        if (nickname == "") {
            $('#nickname').css({"border-color":"red"});
            UIToastr.showWarning(LANG.UI_MICROSOFT365_ADD_ORGANIZATION,LANG.UI_MICROSOFT365_FILL_ALIAS_TIPS);
            return false;
        } else {
            $('#nickname').css({"border-color":"#E6E6E6"});
        }
        //online
        if ($('#organizationType').val() == 1) {
            if ($('#conectionType').val() == 1) {//自动注册新的AzureAD应用程序
                if (ADname == "" || ADname.toLowerCase().includes("exchange")) {
                    $('#ADname').css({"border-color":"red"});
                    UIToastr.showWarning(LANG.UI_MICROSOFT365_ADD_ORGANIZATION,LANG.UI_MICROSOFT365_FILL_AZURE_TIPS);
                    return false;
                } else {
                    $('#ADname').css({"border-color":"#E6E6E6"});
                }
            } else {//使用现有的
                // 用户名
                var userName = $.trim($('#userName').val());
                if (userName == "") {
                    $('#userName').css({"border-color":"red"});
                    UIToastr.showWarning(LANG.UI_MICROSOFT365_ADD_ORGANIZATION,LANG.UI_MICROSOFT365_FILL_USER_NAME_TIPS);
                    return false;
                } else {
                    $('#userName').css({"border-color":"#E6E6E6"});
                }
                // 租户ID
                var tenantId = $.trim($('#tenantId').val());
                if (tenantId == "") {
                    $('#tenantId').css({"border-color":"red"});
                    UIToastr.showWarning(LANG.UI_MICROSOFT365_ADD_ORGANIZATION,LANG.UI_MICROSOFT365_FILL_TENANT_ID_TIPS);
                    return false;
                } else {
                    $('#tenantId').css({"border-color":"#E6E6E6"});
                }
                // Azure AD应用程序ID
                var AzureId = $.trim($('#AzureId').val());
                if (AzureId == "") {
                    $('#AzureId').css({"border-color":"red"});
                    UIToastr.showWarning(LANG.UI_MICROSOFT365_ADD_ORGANIZATION,LANG.UI_MICROSOFT365_FILL_AZURE_ID_TIPS);
                    return false;
                } else {
                    $('#AzureId').css({"border-color":"#E6E6E6"});
                }
            }
            // 证书参数验证
            if ($('#authentication').val() == 2 && $('#certSelect').val() == 2) {
                var fileList = $('#fileList').text();
                if (fileList == "" || !fileList.toLowerCase().endsWith('.pfx')) {
                    $('#fileList').css({"border-color":"red"});
                    $('.lookBtn').css({"border-color":"red","border-left":"none"});
                    UIToastr.showWarning(LANG.UI_MICROSOFT365_ADD_ORGANIZATION,LANG.UI_MICROSOFT365_IMPORT_PFX_CERTIFICATE_TIPS);
                    return false;
                } else {
                    $('#fileList').css({"border-color":"#E6E6E6"});
                    $('.lookBtn').css({"border-color":"#E6E6E6","border-left":"none"});
                }
                //密码
                var pfxCertPsw = $('#pfxCertPsw').val();
                if (!editFlag && pfxCertPsw == "") {
                    $('#pfxCertPsw').css({"border-color":"red"});
                    UIToastr.showWarning(LANG.UI_MICROSOFT365_ADD_ORGANIZATION,LANG.UI_MICROSOFT365_FILL_CERTIFICATE_PASSWORD_TIPS);
                    return false;
                } else {
                    $('#pfxCertPsw').css({"border-color":"#E6E6E6"});
                }
            } else if ($('#conectionType').val() == 2 && $('#authentication').val() == 1) {
                // Azure AD应用程序密码
                var AzurePsw = $.trim($('#AzurePsw').val());
                if (!editFlag && AzurePsw == "") {
                    $('#AzurePsw').css({"border-color":"red"});
                    UIToastr.showWarning(LANG.UI_MICROSOFT365_ADD_ORGANIZATION,LANG.UI_MICROSOFT365_FILL_AZURE_PASSWORD_TIPS);
                    return false;
                } else {
                    $('#AzurePsw').css({"border-color":"#E6E6E6"});
                }
            }
        }

        //本地版
        if ($('#organizationType').val() == 2) {
            // AD域
            var ADdomain = $('#ADdomain').val();
            if (ADdomain == "") {
                $('#ADdomain').css({"border-color":"red"});
                UIToastr.showWarning(LANG.UI_MICROSOFT365_ADD_ORGANIZATION,LANG.UI_MICROSOFT365_FILL_AD);
                return false;
            } else {
                $('#ADdomain').css({"border-color":"#E6E6E6"});
            }
            // 管理员账户
            var managerName = $('#managerName').val();
            if (managerName == "") {
                $('#managerName').css({"border-color":"red"});
                UIToastr.showWarning(LANG.UI_MICROSOFT365_ADD_ORGANIZATION,LANG.UI_MICROSOFT365_FILL_ADMINISTRATOR_ACCOUNT);
                return false;
            } else {
                $('#managerName').css({"border-color":"#E6E6E6"});
            }
            // 管理员密码
            var managerPwd = $('#managerPwd').val();
            if (!editFlag && managerPwd == "") {
                $('#managerPwd').css({"border-color":"red"});
                UIToastr.showWarning(LANG.UI_MICROSOFT365_ADD_ORGANIZATION,LANG.UI_MICROSOFT365_FILL_ADMINISTRATOR_PASSWORD);
                return false;
            } else {
                $('#managerPwd').css({"border-color":"#E6E6E6"});
            }
            // 客户端关联
            var agentConnect = $('#agentConnect').selectpicker('val');
            if (agentConnect == "") {
                $('.agentConnectDiv .bootstrap-select').css({"border":"1px solid red"});
                UIToastr.showWarning(LANG.UI_MICROSOFT365_ADD_ORGANIZATION,LANG.UI_MICROSOFT365_ADD_CLIENT_ASSOCIATE);
                return false;
            } else {
                $('.agentConnectDiv .bootstrap-select').css({"border":"1px solid #E6E6E6"});
            }
        }

        return true;
    }


    var isEmailFormat = function (email) {
        // 邮件格式的正则表达式
        var emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
        // 使用正则表达式进行匹配
        if (emailRegex.test(email)) {
            return true;
        } else {
            return false;
        }
    }


    return {
        //main function to initiate the module
        init: function () {
            wizardInit();
            initListener();
            initDataTable();

        }

    };

}();

jQuery(document).ready(function () {
    organizationManager.init();
});
