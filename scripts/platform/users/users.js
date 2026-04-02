var TableAjax = function () {
    var _UserPassword;
    var initErrorFlag = false;
    var userUuid;
    var userName;
    var oldPass;
    var oldCustomPass;
    var oldRoleList = [],
        oldUsergroupList = [];
    var _userTenantuuid = "";
    var passTips = "";
    var startText;
    var edit_pwd_flag = true;
    var concat_user_flag = true;
    // var resourceData = [];
    // var resourceGroupData = [];
    // var resourceAllocationData = [];
    var resourceData = [], userData = [];
    var resourceGroupData = [];
    var resourceAllocationData = [];
    var resourceGroupAllocationData = [];
    var getRoleList = []; // 存储的角色列表
    var _isHomepage = true; // 记录分配角色的时候是否有首页
    var _permission;
    var _permissions;
    var zTree;
    var nowUserUuid = ''; // 标记选择的用户id
    var nowUserRoleUuid = ''; // 标记选择的用户的角色uuid
    var nowUserName = ''; // 标记选择的用户名
    var emailHtml = $("input[name=email]").parent().parent().parent().html();
    var numberHtml = $("input[name=number]").parent().parent().parent().html();

    var passwordFlag = true;  // 域用户不校验密码
    var addtype = 3;
    var addData = [];
    var vm_type = 0; // 虚拟化类型
    var is_super_role = 1; // 1表示其它角色，2表示全局观察者
    var isMaster = false; // 标记是否是超级管理员
    var selectType = 0; // ---------初始化页面-------

    /**
     * @function 事件监听器
     */

//初始化虚拟化类型
    var initVMType = function(){
        var data = {};
        pAjaxRequest(data, "/api/v1/vm/platforms/hypervisors", "GET", function (d) {
            let hypervisors = d.data.hypervisors;
            var vmtypeselect = $('#vmtype');
            vmtypeselect.empty();
            var option = $("<option>").text(LANG.UI_SEARCH_ALL_HYPERVISOR).val('0');
            vmtypeselect.append(option);
            for (var i = 0; i < hypervisors.length; i++) {
                option = $("<option>").text(hypervisors[i].text).val(hypervisors[i].value);
                vmtypeselect.append(option);
            }
            vmtypeselect.val('0');
        });
    }
    var initPrivateType = function(){
        var data = {};
        data.cloud_flag = true;
        data.cloud_type = 'private';
        pAjaxRequest(data, "/api/v1/vm/platforms/hypervisors", "GET", function (d) {
            let hypervisors = d.data.hypervisors;
            var vmtypeselect = $('#vmPrivatetype');
            vmtypeselect.empty();
            var option = $("<option>").text(LANG.UI_SEARCH_ALL_CLOUD_PLATFORM).val('0');
            vmtypeselect.append(option);
            for (var i = 0; i < hypervisors.length; i++) {
                option = $("<option>").text(hypervisors[i].text).val(hypervisors[i].value);
                vmtypeselect.append(option);
            }
            vmtypeselect.val('0');
        });
    }
    var initPublicType = function(){
        var data = {};
        data.cloud_flag = true;
        data.cloud_type = 'public';
        pAjaxRequest(data, "/api/v1/vm/platforms/hypervisors", "GET", function (d) {
            let hypervisors = d.data.hypervisors;
            var vmtypeselect = $('#vmPublictype');
            vmtypeselect.empty();
            var option = $("<option>").text(LANG.UI_SEARCH_ALL_CLOUD_PLATFORM).val('0');
            vmtypeselect.append(option);
            for (var i = 0; i < hypervisors.length; i++) {
                option = $("<option>").text(hypervisors[i].text).val(hypervisors[i].value);
                vmtypeselect.append(option);
            }
            vmtypeselect.val('0');
        });
    }
    //添加里面的筛选
    $('#initAllocationResource').unbind('change').on('change', function (){initAllocationResource();});


    // 虚拟化类型改变事件
    $('#vmtype').on('change', function (){
        initAllocationResource(1)
    });

    // 私有云类型改变事件
    $('#vmPrivatetype').on('change', function (){
        initAllocationResource(2)
    });

    // 公有云类型改变事件
    $('#vmPublictype').on('change', function (){
        initAllocationResource(3)
    });

    var addListeners = function () {
        $('#unlock').on('click', function (){
            if (checkAuth('unlock')) {
                checkOperateAuth(checkAuth('unlock'), unlockUsers)
            }
        });
        $('#lock').on('click', function (){
            if (checkAuth('lock')) {
                checkOperateAuth(checkAuth('lock'), lockUsers)
            }
        });
        $('#vin_user_toolbar #delete_user').on('click', function (){
            if (checkAuth('delete')) {
                checkOperateAuth(checkAuth('delete'), delUsers)
            }
        });

        $('#resource_allocation_modal #select_resource').on('change', function (){
            initAllocationResource(0)
        });
        $('#resource_allocation_modal #resource_type').on('change', function (){
            initAllocationResource(0)
        });

        //初始化添加用户
        $('#add_user').on('click', initAddUser);

        // 分配角色初始化
        $('#vin_user_toolbar #role_allot').on('click', function (){
            if (checkAuth('role_allot')) {
                checkOperateAuth(checkAuth('role_allot'), allotUsersRole)
            }
        });

        // 修改独立密码
        $('#resetCustomePassword').on('click', function (){
            editCustomePassword();
        });

        $('#manager_allocation_modal #select_manager_user').on('change', function () {
            let user_uuid = $('#manager_allocation_modal #select_manager_user').val();
            if (user_uuid != '0') {
                pAjaxRequest({}, '/api/v1/users/' + user_uuid + '/manager', 'GET', function (res) {
                    $('#select_permission tbody input[type=checkbox]').each(function (k, v) {
                        $(this).prop('checked', false);
                        if ($.inArray($(this).val(), res.data.auth_list) != -1) {
                            $(this).prop('checked', true);
                        }
                    })
                })
            } else {
                $('#select_permission tbody input[type=checkbox]').each(function (k, v) {
                    $(this).prop('checked', false);
                })
            }
        })

        $('#manager_allocation_modal .add_submit').on('click', function () {
            managerAllocation(userUuid);
        })

        // 点击删除按钮事件
        $('#vin_user_toolbar .user_tableclear .icon-close-small').on('click', function (){
            $(this).parent().parent().parent().find('input').val('');
            $('#user_table').bootstrapTable('refresh');
        })

        $('#choose_global_flag').bootstrapSwitch('onSwitchChange', function (e, data) {
            if(data){
                //开
                is_super_role = 2;
            }else{
                //关
                is_super_role = 1;
            }
            // 重新渲染角色列表
            initRoleList();
            initEditRoleList();
            initUsergroupList();
            initEditUsergroupList();
        });
        $('#close_reset_customepassword_modal').off().on('click',function (){
            $('#user_table').bootstrapTable('refresh');
            $('#reset_customepassword_modal').modal('hide');
        })
    }

    // 操作权限校验
    var checkAuth = function(operationType,row) {
        let user_uuid = '';
        let select = '';
        if(!!row){
            user_uuid = row.user_uuid;
        }else{
            select = $('#user_table').bootstrapTable('getSelections');
            user_uuid = [...new Set(select.map(item => item.user_uuid))].join(',');
        }
        if (select !== '' && !select.length) {
            switch (operationType){
                case 'unlock':
                    tipUnlockUser();
                    break;
                case 'lock':
                    tiplockUser();
                    break;
                case 'delete':
                    tipDeleteUser();
                    break;
                case 'role_allot':
                    tipallotRole();
                    break;
                case 'edit_custome_password':
                    tipeditCustomePassword();
                    break;
            }
            return false;
        }
        return {
            type: 1,
            user_uuid: user_uuid,
            auth: ''
        };
    }

    var allotUsersRole = function () {
        clickEffect(this);
        var select = $('#user_table').bootstrapTable('getSelections');
        if (select.length != 1) {
            UIToastr.showInfo(LANG.UI_USER_AUTH_MANAGE, LANG.UI_ROLE_ALLOCATION_SELECT_USER);
            return;
        }
        var user_uuid = select[0]['user_uuid'];
        var role_uuid = select[0]['role_uuid'];

        nowUserUuid = user_uuid;
        pAjaxRequest({},'/api/v1/users/roles/list','GET',function (d){
            const getRoleList = d.data;
            if(getRoleList.length == 0){
                UIToastr.showInfo(LANG.UI_ROLE_ALLOCATION, LANG.UI_ROLE_ALLOCATION_FIRST);
                return;
            }
            // 渲染数据
            // 获取 select 元素
            let select = $('#select_role_user');
            select.html('');
            var option = $("<option>").text(LANG.UI_NIC_TEAMING_SELECT).val('0');
            select.append(option);
            for (var i = 0; i < getRoleList.length; i++) {
                var option = $("<option>").text(getRoleList[i].role_name).val(getRoleList[i].role_uuid);
                select.append(option);
            }

            if (role_uuid.length == 1) {
                // 只有一个角色，那么赋值给对应的就行
                select.val(role_uuid[0]).change();
                nowUserRoleUuid = role_uuid[0];
            } else {
                nowUserRoleUuid = '';
            }

            // 打开弹窗
            $('#role_allocation_modal').modal({
                'width': '693px',
                'height': '398px'
            });

        });
        $('#role_allocation_modal .add_submit').unbind('click').click(function () {
            doAllotUsersRole();
        })
        $('#role_allocation_modal .cancel').on('click', function () {
            $('#role_allocation_modal').modal('hide');
        })
        $('#permissionTree').html('<div class="show-info">' + LANG.UI_ROLE_ALLOCATION_FIRST + '</div>');
    }
    // 角色二次分配
    var doAllotUsersRole = function () {
        var role_uuid = $('#select_role_user').val();
        if (role_uuid == '') {
            UIToastr.showInfo(LANG.UI_ROLE_ALLOCATION, LANG.UI_ROLE_ALLOCATION_SELECT_ONE);
            return;
        }
        let params = {};
        params.role_uuid = role_uuid;
        params.source_list = getPermission();
        pAjaxRequest(params, '/api/v1/users/' + nowUserUuid + '/roles', 'POST', (res) => {
            if (operateResponseList(res, LANG.UI_ROLE_ALLOCATION)) {
                $('#user_table').bootstrapTable('refresh');
                $('#user_table').bootstrapTable('uncheckAll');
                $('#role_allocation_modal').modal('hide');
            }
        });
    }

    // 修改独立密码
    var editCustomePassword = function () {
        clickEffect(this);
        var data = {};
        data.user_uuid = userUuid;
        var encrypt = new JSEncrypt();
        encrypt.setPublicKey(CONF.PUBLIC_KEY);
        data.customePassword = encrypt.encrypt('User@3R');
        let msg = LANG.UI_USER_RESET_CUSTOME_PASSWORD_TIP;

        msg = msg.replace(/%A%/g, userName);
        bootbox.confirm({
            title: LANG.UI_USER_RESET_CUSTOME_PASSWORD,
            message: msg,
            callback: debounce(function (r) {
                if (!r) return;
                pAjaxRequest(data,'/api/v1/users/edit/customepass','PUT',function (res){
                    if(res.success){
                        $('#user_name').html(userName);
                        $('#custome_password').html('<span id="custome_password" class="pr10">User@3R</span>' +
                            '<a class="btn pd0 colorgray" href="javascript:;" data-clipboard-target="#custome_password" title="">' +
                            '<i class="viconfont vicon-a-Copyfuzhi"></i>' +
                            '</a>');
                        $('#reset_customepassword_modal').modal({'width':'690px', 'height': '290px'});
                        if(clipboard){
                            //销毁上一次
                            clipboard.destroy();
                        }
                        clipboard = new ClipboardJS('.btn').on('success', function(e) {
                            UIToastr.showSuccess(LANG.UI_PUBLIC_ALREADY_COPY);
                            e.clearSelection();　　//取消选择节点
                        });
                    }
                });
            },300)
        })

    }

    // 分配角色选项改变事件触发重新加载当前角色的所有权限数组
    $('#select_role_user').on('change', function () {
        let role_uuid = $(this).val();
        if (role_uuid == 0) {
            $('#permissionTree').html('<div class="show-info">' + LANG.UI_ROLE_ALLOCATION_FIRST + '</div>');
            return;
        }
        var params = {roleuuid: role_uuid, user_uuid: nowUserUuid};
        Metronic.blockUI({target: '#permissionTree',animate: true,cenrerY: true});
        pAjaxRequest(params,'/api/v1/roles/oldInfo','GET',function (d){
            var res = d.data;
            _permission = res.permission;
            _permissions = res.user_auth;
            var params = {permission: _permission};
            pAjaxRequest(params,'/api/v1/users/permission/role','POST',initUserTree);
        })

    })

    //获取选中全部权限
    var getPermission = function(){
        var allCheckNodes = zTree.getCheckedNodes(true);
        if (_isHomepage) {
            // 把首页加上
            var permission = ['homepage', 'p_homepage'];
        } else {
            var permission = [];
        }

        for(var i=0; i<allCheckNodes.length; i++){
            permission.push(allCheckNodes[i].id);
        }
        return permission;
    }

    var nodeSelect = function(treeId, treeNode, clickFlag){
        $.fn.zTree.getZTreeObj(treeId).checkNode(treeNode, !treeNode.checked, true, true);
    }

    //初始化权限树
    var initUserTree = function(userInfo){
        Metronic.unblockUI('#permissionTree');
        var setting = {
            check: {
                enable: true,
                nocheckInherit: false,
                chkStyle: "checkbox"
            },
            data: {
                simpleData: {
//						enable: true,
                    idKey: "id",
                    pIdKey: "pid",
                    rootPId: 0
                },
                key: {
                    title: "title"
                }
            },
            view: {
                showIcon : false,
                nameIsHTML: true
            },
            callback: {
                beforeClick: nodeSelect,
            }
        };
        zTree = $.fn.zTree.init($("#permissionTree"), setting, userInfo.data.nodes);
        //需要默认选中切不可修改的概况
        var nodes = zTree.transformToArray(zTree.getNodes());
        _isHomepage = false;
        for(var i=0; i<nodes.length; i++){
            if(nodes[i].id == "homepage" || nodes[i].id == "p_homepage"){
                _isHomepage = true;
            }
            zTree.setChkDisabled(nodes[i], false, false, false);
            if(
                ($.inArray(nodes[i].id, _permissions) >= 0 && _permissions.length > 0 && nowUserRoleUuid == $('#select_role_user').val())
                || (nowUserRoleUuid != $('#select_role_user').val()) || _permissions.length == 0
            ){
                //如果有这个权限
                if(nodes[i].type >= 1){
                    zTree.setChkDisabled(nodes[i], false, false, false);
                    if(
                        ($.inArray(nodes[i].id, _permissions) >= 0 && _permissions.length > 0 && nowUserRoleUuid == $('#select_role_user').val())
                        || (nowUserRoleUuid != $('#select_role_user').val()) || _permissions.length == 0
                    ){
                        //只选中用户之前有的权限
                        zTree.checkNode(nodes[i], true, true);
                    }
                }
            }else{
                zTree.checkNode(nodes[i], false, true);
            }
            if(nodes[i].id == "homepage" || nodes[i].id == "p_homepage"){
                zTree.setChkDisabled(nodes[i], true, false, false);
            }
        }
    }

    // 按钮点击效果
    var clickEffect = function (element) {
        $(element).addClass('btn-hover');
        setTimeout(function() {
            $(element).removeClass('btn-hover');
        }, 300); // 0.3秒后恢复原样
    }

    var initAddUser = function () {
        clickEffect(this);

        var addManager = $('#manager');
        removeValidateStyle();
        initUser(addManager);
        // 因为编辑可能会涉及给隐藏，所以添加默认都给放出来
        $('#password').closest('.form-group').show();
        $('input[name="rpassword"]').closest('.form-group').show();
        $('#customePassword_div').hide();
        $('#add_user_drawer #drawer-1-title-1').html('<i class="viconfont vicon-danchuangtianjia1"></i>');
        $('#add_user_drawer #drawer-1-title-2').html(LANG.UI_USER_ADD);
        $('#submit').prop('data-action', 'add');
        $('#add_user_drawer .cancel').on('click', function () {
            $('#add_user_drawer').drawer('hide');
        });
        $('.locationDiv').show();
        $('.domainDiv ').hide();
        $('#force-edit-pwd').show();
        initPassComplexity();
        clearAllOption(); //清除form表单
        //            initTenantList();
        initUsergroupList();
        initRoleList();
        initDomainList();
        handleAddValidation();
    }

    /**
     * 移除所有错误提示信息
     */
    var removeValidateStyle = ()=>{
        $('.alert-danger').hide();
        $(".fa-warning").hide();
        $(".form-group").removeClass('has-error');
        $(".fa-check").hide();
        $(".form-group").removeClass('has-success');
    }

    var submit = function () {
        var action = $('#submit').prop('data-action');
        if (action === 'add') {
            //提交添加用户时
            addSubmit();
        }
        if (action === 'edit') {
            //提交修改用户时
            editSubmit(userUuid);
        }
    }

    //清除所有options
    var clearAllOption = function () {
        if ($('#is_three_powers').val()) {
            $('#usertype').val(1).prop('disabled', true);
        } else {
            $('#usertype').val('1').prop('disabled', false);
        }
        $("input[name=name]").val('').prop('disabled', false);
        $("input[name=password]").val('').prop('disabled', false);
        $("input[name=password]").attr('placeholder', '');
        $("input[name=rpassword]").val('').prop('disabled', false);
        $("input[name=rpassword]").val('');
        $('input[name=customePassword]').val('');
        $('#customePassword').bootstrapSwitch('state', false);
        $('input[name=position]').val('');

        $("input[name=email]").val('');
        $("input[name=number]").val('');
        $('#domainlist').val('');
        $('#user_Role').selectpicker('');
        $('#user_Group').selectpicker('');
        $('#storageMode').val('1');
        $('#custom').hide();

        if ($('#is_three_powers').val()) {
            // 不显示用户组和角色
            $('#user_Group').parent().parent().parent().hide();
            $('#user_Role').parent().parent().parent().hide();
        } else {
            $('#user_Group').parent().parent().parent().show();
            $('#user_Role').parent().parent().parent().show();
        }

        if ($.inArray('p_safety_user_manager_allocation', CONF.PERMISSION_ARR) !== -1) {
            $('#concat_users').show();
            //$('#manage_user_div').show();
        }

        $('#spinnerNumInput').val('1');
        $('#select_manager_permission input[type="checkbox"]').prop('checked', false);

        // 重置全局观察者角色为关闭
        $('#noticeswitch').bootstrapSwitch('state', false);
        is_super_role = 1;
    }

    //回填表单
    var backfillForm = function (row) {
        pAjaxRequest({}, '/api/v1/users/' + row.user_uuid + '', 'GET', function (res) {
            // console.log(res.data);
            let data = res.data;
            oldPass = data.password;
            oldCustomPass = data.custome_password;
            //加载租户列表
            oldRoleList = data.role_list;
            oldUsergroupList = data.usergroup_list;
            if (data.userLevel > 1) {
                // 不显示用户组和角色
                $('#user_Group').parent().parent().parent().hide();
                $('#user_Role').parent().parent().parent().hide();
            } else if(data.userLevel == 1){
                // admin屏蔽角色、用户组和备份存储容量
                $('#user_Group').parent().parent().parent().hide();
                $('#user_Role').parent().parent().parent().hide();
            } else {
                $('#user_Group').parent().parent().parent().show();
                $('#user_Role').parent().parent().parent().show();
            }
            if((data.userLevel != 5 && data.userLevel != 0) || data.tenantuuid != ''){
                $('#storageMode').parent().parent().hide();
            }else {
                $('#storageMode').parent().parent().show();
            }
            $('#usertype').val('' + data.usertype).prop('disabled', true);
            $("input[name=name]").val(data.user_name).prop('disabled', true);
            $("input[name=password]").val(oldPass);
            $("input[name=rpassword]").val(oldPass);
            $("input[name=email]").val(data.email);
            $("input[name=number]").val(data.telephone);
            $("input[name=customePassword]").val(data.custome_password);
            $('#customePassword').bootstrapSwitch('state', false);
            if(!!data.custome_password){
                $('#customePassword').bootstrapSwitch('state', true);
            }
            $("input[name=position]").val(data.position);
            // $('#domainlist').val('');

            $('#force-edit-pwd').hide();
            $('#concat_users').hide();
            $('#manage_user_div').hide();

            // 全局观察者开关判读
            $('#choose_global_flag').bootstrapSwitch('state', data['is_super_role']);
            if (data['is_super_role']) {
                is_super_role = 2;
            }

            initEditRoleList();
            initEditUsergroupList();
            $('#storageMode').val('1');
            $('#custom').hide();
            $('#spinnerNumInput').val('1');
            if(data.quota !== -1){
                $('#storageMode').val('2');
                var j= 0;
                while(data.quota >= 1024){
                    j++;
                    data.quota = data.quota / 1024;
                }
                $('#spinnerNumInput').val(data.quota);  // 获取到数值
                var unitDom = document.getElementById('unit');
                for(var k =0;k < unitDom.length;k++){
                    if(Number(unitDom.options[k].value) == j-1){
                        unitDom.selectedIndex = k;
                        break;
                    }
                }
                $('#custom').show();
            }
            $('#select_manager_permission input[type="checkbox"]').prop('checked', false);
            if(row.user_uuid == row.login_user_uuid){
                // 只能改自己的用户名
                $('#name').prop('disabled',false);
            }else{
                $('#name').prop('disabled',true);
            }
        });
    }

    //初始化修改角色列表
    var initEditRoleList = function () {
        pAjaxRequest({'offset': 0, 'limit': 1000, role_type: is_super_role},'/api/v1/roles','GET',function (d){
            var rows = d.data.rows;
            var roleList = $("#user_Role");
            roleList.empty();
            for(var i=0;i<rows.length;i++){
                var option = $("<option>").text(rows[i].role_name).val(rows[i].role_uuid);
                roleList.append(option);
            }
            //加载角色列表
            $('#user_Role').selectpicker('val', oldRoleList);
            roleList.selectpicker('refresh');
        });
    }

    //初始化用户组列表
    var initEditUsergroupList = function () {
        pAjaxRequest({group_type: is_super_role},'/api/v1/users/group/list','GET',function (d){
            var res = d.data;
            var usergroupList = $('#user_Group');
            usergroupList.empty();
            for (var i = 0; i < res.length; i++) {
                var option = $('<option>').text(res[i].usergroup_name).val(res[i].usergroup_uuid);
                usergroupList.append(option);
            }
            //加载用户组列表
            $('#user_Group').selectpicker('val', oldUsergroupList);

            if (_userTenantuuid && _userTenantuuid != "") {
                usergroupList.prop('disabled', true);
            }
            usergroupList.selectpicker('refresh');
        })

    }

    //添加用户提交
    var addSubmit = function () {
        var userType = $('#usertype').val();
        var data = {};
        var manage_list = [];
        data.username = $("input[name=name]").val();
        var encrypt = new JSEncrypt();
        encrypt.setPublicKey(CONF.PUBLIC_KEY);
        data.password = encrypt.encrypt($("input[name=password]").val());
        data.email = $("input[name=email]").val();
        data.phone = $("input[name=number]").val();
        data.usertype = $("#usertype").val();
        data.domainuuid = $('#domainlist').val();
        if (data.usertype == 1) {
            data.domainuuid = "";
            data.force_edit_pass = edit_pwd_flag;
        } else if (data.usertype == 2) {
            data.force_edit_pass = false;
        }
        if (concat_user_flag === true) { //开启关联用户
            data.manage_uuid = $('#manager').val();
            if(data.manage_uuid == 0){
                UIToastr.showWarning(LANG.UI_USER_MANAGE_USER_TITLE, LANG.UI_USER_MANAGE_USER_MESSAGE);
                return;
            }
        }
        $('#select_manager_permission tbody input[type=checkbox]').each(function (k, v) {
            if ($(this).is(':checked')) {
                manage_list.push($(this).val());
            }
        });
        data.manage_list = manage_list;
        data.role_list = $('#user_Role').selectpicker('val');
        data.user_group_list = $('#user_Group').selectpicker('val');
        //    	data.tenantuuid = $('#user_Tenant').val();
        if ($('#storageMode').val() == 1) {
            data.quota = -1;
        } else {
            data.quota = parseInt($('#spinnerNumInput').val()) * parseInt(calSize($('#unit option:selected').text()));
        }
        data.customePassword = '';
        data.position = $("input[name=position]").val();
        $('#add_user_drawer').drawer('hide');
        Metronic.blockUI({
            target: '#userMangerDiv',
            animate: true,
            cenrerY: true,
        });
        pAjaxRequest(data, '/api/v1/users', 'POST', function (res) {
            Metronic.unblockUI('#userMangerDiv');
            if (operateResponseList(res, LANG.UI_USER_ADD)) {
                $("input[name=email]").parent().parent().parent().html(emailHtml);
                $("input[name=number]").parent().parent().parent().html(numberHtml);
                $('#user_table').bootstrapTable('refresh');
            }
        });
    }

    //修改用户提交
    var editSubmit = function (id) {
        var userType = $('#usertype').val();
        var data = {};
        data.username = $("input[name=name]").val();
        var editUsernameFlag = data.username !== userName;
        data.password = hex_md5($("input[name=password]").val());
        data.email = $("input[name=email]").val();
        data.phone = $("input[name=number]").val();
        data.usertype = $("#usertype").val();
        data.domainuuid = $('#domainlist').val();
        if (data.usertype == 1) {
            data.domainuuid = "";
            data.force_edit_pass = edit_pwd_flag;
        } else if (data.usertype == 2) {
            data.force_edit_pass = false;
        }
        data.role_list = $('#user_Role').selectpicker('val');
        data.user_group_list = $('#user_Group').selectpicker('val');
        //    	data.tenantuuid = $('#user_Tenant').val();
        if ($('#storageMode').val() == 1) {
            data.quota = -1;
        } else {
            data.quota = parseInt($('#spinnerNumInput').val()) * parseInt(calSize($('#unit option:selected').text()));
        }
        data.position = $("input[name=position]").val();
        if(editUsernameFlag){
            bootbox.prompt({
                title: LANG.UI_PLATFORM_TENANT_INPUT_PSW,
                inputType: 'password',
                callback: function (result) {
                    if(result == null) return;
                    if(hex_md5(result) == _UserPassword){
                        // 进行保存修改
                        Metronic.blockUI({target:".drawer-body",animate: true});
                        pAjaxRequest(data, '/api/v1/users/' + id + '', 'PUT', function (res) {
                            Metronic.unblockUI('#userMangerDiv');
                             res['message'] += "," + LANG.UI_USER_RELOGIN_TIPS;
                            if (operateResponseList(res, LANG.UI_USER_MODIFY)) {
                                $("input[name=email]").parent().parent().parent().html(emailHtml);
                                $("input[name=number]").parent().parent().parent().html(numberHtml);
                                $('#user_table').bootstrapTable('refresh');
                                if ($('#is_three_powers').val()) {
                                    // 不显示用户组和角色
                                    $('#user_Group').parent().parent().parent().hide();
                                    $('#user_Role').parent().parent().parent().hide();
                                } else {
                                    $('#user_Group').parent().parent().parent().show();
                                    $('#user_Role').parent().parent().parent().show();
                                }
                                //如果修改了用户名,5秒后跳转到登录页面
                                setTimeout(function(){window.location.href='/loginout.php';}, 5000);
                            }
                        });
                    }else{
                        $('.bootbox-input').css('border-color', "#a94442");
                        if(!initErrorFlag){
                            var des = '<p class="password-error" style="margin-top:5px;color:#a94442">'+ LANG.UI_DB_DM_CORRECT_PASSWORD_INPUT_TIPS +'</p>';
                            $('.bootbox-input').after(des);
                            initErrorFlag = true;
                        }
                        return false;
                    }
                }
            });
        }else{
            $('#add_user_drawer').drawer('hide');
            Metronic.blockUI({
                target: '#userMangerDiv',
                animate: true,
                cenrerY: true,
            });
            pAjaxRequest(data, '/api/v1/users/' + id + '', 'PUT', function (res) {
                Metronic.unblockUI('#userMangerDiv');
                if (operateResponseList(res, LANG.UI_USER_MODIFY)) {
                    $("input[name=email]").parent().parent().parent().html(emailHtml);
                    $("input[name=number]").parent().parent().parent().html(numberHtml);
                    $('#user_table').bootstrapTable('refresh');
                    if ($('#is_three_powers').val()) {
                        // 不显示用户组和角色
                        $('#user_Group').parent().parent().parent().hide();
                        $('#user_Role').parent().parent().parent().hide();
                    } else {
                        $('#user_Group').parent().parent().parent().show();
                        $('#user_Role').parent().parent().parent().show();
                    }
                }
            });
        }
    }

    $.validator.addMethod(
        "usernameAvailable",
        function (value, element, param) {
            if(value === userName){
                // 原用户名不检测
                return true;
            }
            var data = {
                user_name: value,
                tenantuuid: ""
            };

            var result = false;
            pAjaxRequest(data, '/api/v1/users/check', 'GET', function (res) {
                result = res.data.value;
            }, async = false);
            return result;
        },
        LANG.UI_USER_NAME_EXISTS
    );

    $.validator.addMethod("username", function (value, element) {
        return this.optional(element) || /^[a-zA-Z\u4e00-\u9fa5][a-zA-Z0-9_\u4e00-\u9fa5@.\\-]{0,63}$/i.test(value);
    }, LANG.UI_USER_NAME_RULE);

    //删除用户
    var delUsers = function () {
        clickEffect(this);
        let select = $('#user_table').bootstrapTable('getSelections');
        let ids = [];
        for (let i = 0; i < select.length; i++) {
            ids.push(select[i].user_uuid);
        }
        if (!select.length) {
            return tipDeleteUser();
        }
        bootbox.prompt({
            title: LANG.UI_PLATFORM_TENANT_INPUT_PSW,
            inputType: 'password',
            callback: debounce(function (result) {
                if (result == null) return;
                if (hex_md5(result) == _UserPassword) {
                    var data = {};
                    data.users = select;
                    data = JSON.stringify(data);
                    Metronic.blockUI({
                        target: "#user_table",
                        animate: true
                    });

                    let that = this; // 保留指向 bootbox 的this引用，用于在密码校验成功后关闭弹窗
                    pAjaxRequest({
                        'users': ids
                    }, '/api/v1/users', 'DELETE', function (res) {
                        Metronic.unblockUI('#user_table');
                        $(that).modal('hide');
                        if (operateResponseList(res, LANG.UI_USER_DELETE_SELECT)) {
                            $('#user_table').bootstrapTable('refresh');
                            checkEvent('#user_table', '#delete_user');
                        }
                    })
                } else {
                    $('.bootbox-input').css('border-color', "#a94442");
                    if (!initErrorFlag) {
                        var des = '<p class="password-error" style="margin-top:5px;color:#a94442">' + LANG.UI_DB_DM_CORRECT_PASSWORD_INPUT_TIPS + '</p>';
                        $('.bootbox-input').after(des);
                        initErrorFlag = true;
                    }
                }
            },300, false)
        });
    }

    //启用
    var unlockUsers = function (grid) {
        clickEffect(this);
        var select = $('#user_table').bootstrapTable('getSelections');
        var user_uuids = [];
        if (!select.length) {
            return tipUnlockUser();
        }
        var selectArr = select;
        var count =0;
        for (let i = 0; i < selectArr.length; i++) {
            if (selectArr[i].lock_flag == CONF.FLAG.SET) {
                count++;
            }
            if(count == selectArr.length){
                return UIToastr.showInfo(LANG.UI_USER_ENABLE, LANG.UI_USER_UNLOCKED);
            }
        }
        var data = {};
        for (let i = 0; i < select.length; i++) {
            user_uuids.push(select[i].user_uuid);
        }
        data.users = user_uuids;
        // data = JSON.stringify(data);
        pAjaxRequest(data, '/api/v1/users/unlock', 'POST', function (res) {
            if (operateResponseList(res, LANG.UI_USER_ENABLE)) {
                $('#user_table').bootstrapTable('refresh');
            }
        });
    }

    //禁用
    var lockUsers = function () {
        clickEffect(this);
        var select = $('#user_table').bootstrapTable('getSelections');
        if (!select.length) {
            return UIToastr.showInfo(LANG.UI_USER_DISABLE, LANG.UI_USER_DISABLE_NO_SELECT);
        }
        var selectArr = select;
        var count =0;
        for (let i = 0; i < selectArr.length; i++) {
            if (selectArr[i].lock_flag == CONF.FLAG.UNSET) {
                count++;
            }
            if(count == selectArr.length){
                return UIToastr.showInfo(LANG.UI_USER_ENABLE, LANG.UI_USER_LOCKED);
            }
        }

        bootbox.confirm({
            title: LANG.UI_USER_DISABLE,
            message: LANG.UI_USER_DISABLE_CONFIRM,
            callback: debounce(function (r) {
                if (!r) return;
                initErrorFlag = false;
                bootbox.prompt({
                    title: LANG.UI_PLATFORM_TENANT_INPUT_PSW,
                    inputType: 'password',
                    callback: debounce(function (result) {
                        if (result == null) return;
                        if (hex_md5(result) == _UserPassword) {
                            var select = $('#user_table').bootstrapTable('getSelections');
                            var data = {};
                            var user_uuids = [];
                            for (let i = 0; i < select.length; i++) {
                                user_uuids.push(select[i].user_uuid);
                            }
                            data.users = user_uuids;
                            Metronic.blockUI({
                                target: "#userMangerDiv",
                                animate: true
                            });
                            let that = this; // 保留指向 bootbox 的this引用，用于在密码校验成功后关闭弹窗
                            pAjaxRequest(data, '/api/v1/users/lock', 'POST', function (res) {
                                Metronic.unblockUI('#userMangerDiv');
                                $(that).modal('hide');
                                if (operateResponseList(res, LANG.UI_USER_DISABLE)) {
                                    $('#user_table').bootstrapTable('refresh');
                                }
                            });
                        } else {
                            $('.bootbox-input').css('border-color', "#a94442");
                            if (!initErrorFlag) {
                                var des = '<p class="password-error" style="margin-top:5px;color:#a94442">' + LANG.UI_DB_DM_CORRECT_PASSWORD_INPUT_TIPS + '</p>';
                                $('.bootbox-input').after(des);
                                initErrorFlag = true;
                            }
                        }
                    },300,false)
                });
            })
        });
    }

    //初始化当前用户密码用于删除二次确认
    var initUserPassword = function () {
        pAjaxRequest({},'/api/v1/users/password','GET',function (d){
            var res = d.data;
            _UserPassword = res.password;
        });
    }

    var tipEditUser = function () {
        UIToastr.showInfo(LANG.UI_USER_EDIT_SELECT, LANG.UI_USER_EDIT_SELECT_TIPS);
    }

    var tipDeleteUser = function () {
        UIToastr.showInfo(LANG.UI_USER_DELETE_SELECT, LANG.UI_USER_DELETE_SELECT_TIPS);
    }

    var tipDeleteResource = function () {
        UIToastr.showInfo(LANG.UI_RESOURCE_DELETE_SELECT, LANG.UI_RESOURCE_DELETE_NO_SELECT);
    }

    var tipDeleteResourceGroup = function () {
        UIToastr.showInfo(LANG.UI_RESOURCE_GROUP_DELETE, LANG.UI_RESOURCE_GROUP_DELETE_NO_SELECT);
    }

    var tipUnlockUser = function () {
        UIToastr.showInfo(LANG.UI_USER_ENABLE, LANG.UI_USER_ENABLE_NO_SELECT);
    }

    var tiplockUser = function () {
        UIToastr.showInfo(LANG.UI_USER_DISABLE, LANG.UI_USER_DISABLE_NO_SELECT);
    }

    var tipallotRole = function () {
        UIToastr.showInfo(LANG.UI_USER_AUTH_MANAGE, LANG.UI_ROLE_ALLOCATION_SELECT_USER);
    }

    var tipeditCustomePassword = function () {
        UIToastr.showInfo(LANG.UI_USER_INFO_MODIFY_CUSTOME_PASSWORD, LANG.UI_ROLE_ALLOCATION_SELECT_USER);
    }

    var checkEvent = function (tableId, btnId) {
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

    //加载关联资源
    var initResourceTable = function (id,row) {
        let beforeInput = '';
        $('#resource_tab #vin_resource_toolbar .customBtn1').empty();
        if ($.inArray('p_safety_user_unbind_allocation_resource', CONF.PERMISSION_ARR) !== -1 && ((!row.is_global_observer_flag) || (row.is_global_observer_flag && row.is_global_observer_manager_flag))) {
            beforeInput = `<button class="btn-font flex_center btn-title p-lr8" id="delete_resource" style="width:auto;height:34px;border:0px">
                                <i class="viconfont vicon-a-Unlockjiesuo-0111"></i>
                                <span style="margin-left: 5px;">`+LANG.UI_USER_RESOURCE_UNBIND_ALLOCATION_RESOURCE+`</span>
                            </button>`;
        }
        var options = {
            toolbarId: '#vin_resource_toolbar',
            buttonsToolbar: '#vin_resource_toolbar .vin_btnToolbar',
            vin_url: '/api/v1/users/' + id + '/resource',
            vin_method: 'GET',
            showExport: false, //是否开启导出按钮
            showColumns: false, //是否开启列选择按钮
            // onResetView: initTableHeight,
            customTool: {
                beforeInput: beforeInput,
            },

            onCheck: function (row) {
                // checkEvent('#resource_table', '#delete_resource');
                resourceData.push(row.source_uuid)
            },
            onCheckAll: function (row) {
                // checkEvent('#resource_table', '#delete_resource');

                for (var i = 0; i < row.length; i++) {
                    var index = resourceData.indexOf(row[i].source_uuid); // 查找元素的索引
                    if (index == -1) {
                        resourceData.push(row[i].source_uuid)
                    }
                }
            },
            onUncheckAll: function (a, row) {
                // checkEvent('#resource_table', '#delete_resource');
                for (var i = 0; i < row.length; i++) {
                    var index = resourceData.indexOf(row[i].source_uuid); // 查找元素的索引
                    if (index != -1) {
                        resourceData.splice(index, 1); // 从数组中删除一个元素
                    }
                }
            },
            onUncheck: function (row) {
                //  checkEvent('#resource_table', '#delete_resource');
                var index = resourceData.indexOf(row.source_uuid); // 查找元素的索引
                if (index !== -1) {
                    resourceData.splice(index, 1); // 从数组中删除一个元素
                }
            },


            columns: [ //列定义
                {
                    checkbox: true,
                    sortable: false,
                    formatter:function (index, row) {
                        for(var i=0; i<resourceData.length; i++) {
                            if(row.is_active == false) {
                                return {
                                    disabled: true
                                }
                            }
                            if(row.source_uuid == resourceData[i]){
                                return true
                            }
                        }
                    }
                },
                {
                    field: 'source_name', //字段名
                    title: LANG.UI_USER_RESOURCE_NAME,
                },
                {
                    field: 'source_type',
                    title: LANG.UI_USER_RESOURCE_TYPE,
                }
            ]
        }
        $('#resource_table').bootstrapTable('destroy');
        sessionStorage.removeItem("resource_table_pageRecord");
        $('#resource_table').baseTableConfig().init(options);
        $('#delete_resource').unbind('click').click(function () {
            // let select = $('#resource_table').bootstrapTable('getSelections');
            if (!resourceData.length) {
                return tipDeleteResource();
            }
            // 提示是否要解绑
            bootbox.confirm({
                title: LANG.UI_USER_RESOURCE_UNBIND_ALLOCATION_RESOURCE,
                message: LANG.UI_USER_RESOURCE_UNBIND_ALLOCATION_RESOURCE_CONFIRM,
                callback: debounce(function (r) {
                    if (!r) return;
                    let params = {};
                    params.type = 1;
                    params.source_list = resourceData;
                    pAjaxRequest(params, '/api/v1/users/' + id + '/allocation', 'DELETE', (res) => {
                        if (res.code == 200 && res.data.info.length > 0) {
                            var html = LANG.UI_RESOURCE_ALLOCATION_CONFIRM + '</br>';
                            for(var k in res.data.info) {
                                html += res.data.info[k] + '</br>';
                            }
                            // 二次弹窗确定
                            // 提示是否要解绑
                            bootbox.confirm({
                                title: LANG.UI_USER_RESOURCE_UNBIND_ALLOCATION_RESOURCE,
                                message: html,
                                callback: debounce(function (r) {
                                    if (!r) return;
                                    params.force = 1;
                                    pAjaxRequest(params, '/api/v1/users/' + id + '/allocation', 'DELETE', (res) => {
                                        if (operateResponseList(res, LANG.UI_RESOURCE_GROUP_ALLOCATION)) {
                                            // 重新赋值
                                            resourceData = [];
                                            $('#resource_table').bootstrapTable('refresh');
                                            $('#resourcegroup_table').bootstrapTable('refresh');
                                        }
                                    });
                                },300)
                            })
                            return;
                        }
                        if (operateResponseList(res, LANG.UI_RESOURCE_GROUP_ALLOCATION)) {
                            // 重新赋值
                            resourceData = [];
                            $('#resource_table').bootstrapTable('refresh');
                        }
                    });
                },300)
            })
        })
    }

    //加载关联资源组
    var initResourcegroupTable = function (id,row) {
        let beforeInput = '';
        $('#resourcegroup_tab #vin_resource_group_toolbar .customBtn1').empty();
        if ($.inArray('p_safety_user_unbind_allocation_resource', CONF.PERMISSION_ARR) !== -1 && ((!row.is_global_observer_flag) || (row.is_global_observer_flag && row.is_global_observer_manager_flag))) {
            beforeInput = `<button class="btn-font flex_center btn-title p-lr8" id="delete_resource_group" style="width:auto;height:34px;border:0px">
                                <i class="viconfont vicon-a-Unlockjiesuo-0111"></i>
                                <span style="margin-left: 5px;">` + LANG.UI_USER_RESOURCE_UNBIND_ALLOCATION_RESOURCE + `</span>
                            </button>`;
        }
        var options = {
            toolbarId: '#vin_resource_group_toolbar',
            buttonsToolbar: '#vin_resource_group_toolbar .vin_btnToolbar',
            vin_url: '/api/v1/users/' + id + '/resource',
            vin_method: 'GET',
            vin_params: function () {
                var param = {};
                param.type = 2;
                param.offset = 0;
                return param;
            },
            showExport: false, //是否开启导出按钮
            showColumns: false, //是否开启列选择按钮
            // onResetView: initTableHeight,
            customTool: {
                beforeInput: beforeInput,
            },
            onCheck: function (row) {
                // checkEvent('#resourcegroup_table', '#delete_resource_group');
                resourceGroupData.push(row.uuid);
            },
            onUncheck: function (row) {
                //  checkEvent('#resourcegroup_table', '#delete_resource_group');
                var index = resourceGroupData.indexOf(row.uuid); // 查找元素的索引
                if (index !== -1) {
                    resourceGroupData.splice(index, 1); // 从数组中删除一个元素
                }
            },
            onCheckAll: function (row) {
                //  checkEvent('#resourcegroup_table', '#delete_resource_group');
                for (var i = 0; i < row.length; i++) {
                    var index = resourceGroupData.indexOf(row[i].uuid); // 查找元素的索引
                    if (index == -1) {
                        resourceGroupData.push(row[i].uuid)
                    }
                }
            },
            onUncheckAll: function (a, row) {
                //  checkEvent('#resourcegroup_table', '#delete_resource_group');
                for (var i = 0; i < row.length; i++) {
                    var index = resourceGroupData.indexOf(row[i].uuid); // 查找元素的索引
                    if (index != -1) {
                        resourceGroupData.splice(index, 1); // 从数组中删除一个元素
                    }
                }
            },


            columns: [ //列定义
                {
                    checkbox: true,
                    sortable: false
                },
                {
                    field: 'source_name', //字段名
                    title: LANG.UI_USER_RESOURCE_NAME,
                    sortable: false
                },
                {
                    field: 'source_type',
                    title: LANG.UI_USER_RESOURCE_TYPE,
                    sortable: false
                }
            ]
        }
        $('#resourcegroup_table').bootstrapTable('destroy');
        sessionStorage.removeItem("resourcegroup_table_pageRecord");
        $('#resourcegroup_table').baseTableConfig().init(options);

        $('#delete_resource_group').unbind('click').on('click', function () {
            let select = $('#resourcegroup_table').bootstrapTable('getSelections');
            if (!select.length) {
                return tipDeleteResourceGroup();
            }

            // 提示是否要解绑
            bootbox.confirm({
                title: LANG.UI_USER_RESOURCE_UNBIND_ALLOCATION_RESOURCE,
                message: LANG.UI_USER_RESOURCE_GROUP_UNBIND_ALLOCATION_RESOURCE_CONFIRM,
                callback: debounce(function (r) {
                    if (!r) return;
                    let params = {};
                    params.type = 2;
                    let source_list = [];

                    for (let i = 0; i < select.length; i++) {
                        source_list.push(select[i].source_uuid);
                    }
                    params.source_list = source_list;
                    pAjaxRequest(params, '/api/v1/users/' + id + '/allocation', 'DELETE', (res) => {
                        if (res.code == 200 && res.data.info.length > 0) {
                            var html = LANG.UI_RESOURCE_ALLOCATION_CONFIRM + '</br>';
                            for(var k in res.data.info) {
                                html += res.data.info[k] + '</br>';
                            }
                            // 二次弹窗确定
                            // 提示是否要解绑
                            bootbox.confirm({
                                title: LANG.UI_USER_RESOURCE_UNBIND_ALLOCATION_RESOURCE,
                                message: html,
                                callback: debounce(function (r) {
                                    if (!r) return;
                                    params.force = 1;
                                    pAjaxRequest(params, '/api/v1/users/' + id + '/allocation', 'DELETE', (res) => {
                                        if (operateResponseList(res, LANG.UI_RESOURCE_GROUP_ALLOCATION)) {
                                            $('#resourcegroup_table').bootstrapTable('refresh');
                                            $('#resource_table').bootstrapTable('refresh');
                                        }
                                    });
                                },300)
                            })
                            return;
                        }
                        if (operateResponseList(res, LANG.UI_RESOURCE_GROUP_ALLOCATION)) {
                            $('#resourcegroup_table').bootstrapTable('refresh');
                        }
                    });
                },300)
            })


        })
    }

    //加载关联权限树
    var initPermissionTree = function (id) {
        pAjaxRequest({}, '/api/v1/users/' + id + '/roles', 'GET', function (res) {
            var zNodes = res.data;
            if (zNodes.length == 0) {
                $('#permission_tree').hide();
                $('#nodatatips').show();
            } else {
                $('#permission_tree').show();
                $('#nodatatips').hide();
            }
            for (let i = 0; i < zNodes.length; i++) {
                zNodes[i].name = '<i class="' + zNodes[i].class + '"></i>' + zNodes[i].name;
                zNodes[i].open = true;
            }
            var setting = {
                check: {
                    enable: false,
                    nocheckInherit: false,
                    chkDisabled: false
                },
                data: {
                    simpleData: {
                        enable: true,
                        idKey: "id",
                        pIdKey: "pid",
                        rootPId: 0
                    },
                    key: {
                        title: "title"
                    }
                },
                view: {
                    showIcon: false,
                    nameIsHTML: true
                },
                callback: {}
            };
            permissionTree = $.fn.zTree.init($("#permission_tree"), setting, zNodes);
        });
    }

    //加载资源分配
    var initAllocationResource = function (types = 0) {
        sourceType = $('#initAllocationResource').find('option:selected').val();
        addtype = sourceType;
        // clickEffect(this);
        var type = $('#resource_allocation_modal #select_resource').val();
        var sourceType = $('#resource_allocation_modal #resource_type').val();
        var select = $('#select_resource_table').bootstrapTable('getSelections');
        $('#vin_select_resource_toolbar .resourceSearch').val('');
        $('#vmtype').hide();
        $('#vmPrivatetype').hide();
        $('#vmPublictype').hide();

        if (type == 1) {

            if (sourceType == 3) {
                // 虚拟化
                $('#vmtype').show();
            }
            if (sourceType == 61) {
                // 私有云
                $('#vmPrivatetype').show();
            }
            if (sourceType == 58) {
                //公有云
                $('#vmPublictype').show();
            }
            vm_type = 0;

            if (types == 1) {
                // 虚拟化的类型切换事件
                vm_type = $('#vmtype').find('option:selected').val();
            }
            if (types == 2) {
                //私有云的类型切换事件
                vm_type = $('#vmPrivatetype').find('option:selected').val();
            }
            if (types == 3) {
                //公有云的类型切换事件
                vm_type = $('#vmPublictype').find('option:selected').val();
            }

            if (types == 0) {
                $('#vmtype').val('0');
                $('#vmPrivatetype').val('0');
                $('#vmPublictype').val('0');
            }
            addData = [];
            queryParams = {};
            queryParams.source_type = addtype;
            queryParams.vm_type = vm_type;
            queryParams.offset = 0;
            sessionStorage.removeItem("backupReport_pageRecord");
            $('#backupReport').bootstrapTable('selectPage', 1);
            $('#backupReport').bootstrapTable('refresh', {query: queryParams});


            $('#initAllocationResource').val(addtype);

            switch (addtype) {
                case 3:
                    $('#vmtype').show();
                    $('#vmtype').val(0);
                    $('#vmPrivatetype').hide();
                    $('#vmPublictype').hide();
                    break;
                case 61:
                    $('#vmtype').hide();
                    $('#vmPrivatetype').show();
                    $('#vmPrivatetype').val(0);
                    $('#vmPublictype').hide();
                    break;
                case 58:
                    $('#vmtype').hide();
                    $('#vmPrivatetype').hide();
                    $('#vmPublictype').show();
                    $('#vmPublictype').val(0);
                    break;
                default:
                    $('.sub-type-select').hide(); // 所有子类型隐藏
                    break;
            }
            initSourceAllocationTable(type, sourceType);
        }

        if (type == 2) {
            initResourceGroupAllocationTable(type, sourceType);
        }

        $('#resource_allocation_modal .add_submit').unbind('click').click(() => {
            allocationResource(type, sourceType);
        })
    }

    var initSourceAllocationTable = function (type, sourceType) {
        var options = {
            pagination: true, //分页
            pageList: [5, 10, 25, 50], //每页数量
            vin_url: '/api/v1/resources/source',
            vin_method: 'GET',
            toolbarId: '#vin_select_resource_toolbar',
            // vin_toolbar: '.vin_resource_toolbar',
            searchInput: true, //搜索框
            searchClass: 'resourceSearch',
            searchSelector: '.resourceSearch',
            placeholder: LANG.UI_USER_SEARCH_BY_RESOURCE_NAME,
            searchOnEnterKey: true,
            paginationSuccessivelySize: 1,
            paginationPagesBySide: 1,
            showJumpTo: false,
            vin_params: function () {
                let params = {
                    source_type: addtype,
                    vm_type: vm_type,
                };
                params.type = type;
                params.user_uuid = nowUserUuid;
                params.source_type = sourceType;
                params.search = $('#vin_select_resource_toolbar .resourceSearch').val();
                return params;
            },
            onCheck: function (row) {
                resourceAllocationData.push(row.uuid)
            },
            onCheckAll: function (row) {
                for (var i = 0; i < row.length; i++) {
                    var index = resourceAllocationData.indexOf(row[i].uuid); // 查找元素的索引
                    if (index == -1) {
                        resourceAllocationData.push(row[i].uuid)
                    }
                }
            },
            onUncheckAll: function (a, row) {
                for (var i = 0; i < row.length; i++) {
                    var index = resourceAllocationData.indexOf(row[i].uuid); // 查找元素的索引
                    if (index != -1) {
                        resourceAllocationData.splice(index, 1); // 从数组中删除一个元素
                    }
                }
            },
            onUncheck: function (row) {
                var index = resourceAllocationData.indexOf(row.uuid); // 查找元素的索引
                if (index !== -1) {
                    resourceAllocationData.splice(index, 1); // 从数组中删除一个元素
                }
            },
            onPostBody: function () {
                $('.resourceSearch').off().on('keypress', function (e) {
                    if (e.which == 13) {
                        $('#select_resource_table').bootstrapTable('refresh');
                    }
                })
                $('#select_resource_table th[data-field="user"]').css('width', '10%');
                $('#select_resource_table th[data-field="status"]').css('width', '10%');

                $('#vin_select_resource_toolbar .search-btn').off().on('click', function () {
                    $('#select_resource_table').bootstrapTable('refresh');
                })

            },
            columns: [ //列定义
                {
                    checkbox: true,
                    sortable: false,
                    formatter: function (value, row, index) {
                        if (row.is_active === false) {
                            return {
                                disabled: true
                            };
                        }
                        for (var i = 0; i < resourceAllocationData.length; i++) {

                            if (row.uuid == resourceAllocationData[i]) {
                                return true
                            }
                        }
                    }
                },
                {
                    field: 'name', //字段名
                    title: LANG.UI_USER_RESOURCE_NAME,
                    sortable: false,
                },
                {
                    field: 'user', //字段名
                    title: LANG.UI_CLIENT_OWNER,
                    sortable: false,
                },
                {
                    field: 'status',
                    title: LANG.UI_PUBLIC_STATUS,
                    sortable: false,
                    formatter: function (value, row, index, field) {
                        return '<span class="label label-sm label-' + row.class + ' status-icon">' + value + '</span>'
                    }
                },
            ]
        }
        sessionStorage.removeItem("select_resource_table_pageRecord");
        $('#select_resource_table').bootstrapTable('destroy');
        sessionStorage.removeItem("select_resource_table_pageRecord");
        $('#select_resource_table').baseTableConfig().init(options);
    }

    var initResourceGroupAllocationTable = function (type, sourceType) {
        var options = {
            pagination: true, //分页
            pageList: [5, 10, 25, 50], //每页数量
            vin_url: '/api/v1/resources/source',
            vin_method: 'GET',
            toolbarId: '#vin_select_resource_toolbar',
            placeholder: LANG.UI_USER_SEARCH_BY_RESOURCE_NAME,
            searchOnEnterKey: true,
            paginationSuccessivelySize:1,
            paginationPagesBySide:1,
            showJumpTo: false,
            vin_params: function () {
                let params = {};
                params.type = type;
                params.user_uuid = nowUserUuid;
                params.source_type = sourceType;
                params.search = $('#vin_select_resource_toolbar .resourceSearch').val();
                return params;
            },
            showExport: false, //是否开启导出按钮
            showColumns: false, //是否开启列选择按钮
            // onResetView: initTableHeight,
            // customTool: {
            //     beforeInput: `<button class="icon-gray-delete b-btn brr2 mr12" style="background-color:#F4F4F5" id="delete_resource"></button>`,
            // },
            onCheck: function (row) {
                resourceGroupAllocationData.push(row.uuid)
            },
            onCheckAll: function (row) {
                for (var i = 0; i < row.length; i++) {
                    var index = addData.indexOf(row[i].uuid); // 查找元素的索引
                    if (index == -1) {
                        resourceGroupAllocationData.push(row[i].uuid)
                    }
                }
            },
            onUncheckAll: function (a, row) {
                for (var i = 0; i < row.length; i++) {
                    var index = resourceGroupAllocationData.indexOf(row[i].uuid); // 查找元素的索引
                    if (index != -1) {
                        resourceGroupAllocationData.splice(index, 1); // 从数组中删除一个元素
                    }
                }
            },
            onUncheck: function (row) {
                var index = resourceGroupAllocationData.indexOf(row.uuid); // 查找元素的索引
                if (index !== -1) {
                    resourceGroupAllocationData.splice(index, 1); // 从数组中删除一个元素
                }
            },
            onPostBody: function () {
                $('.resourceSearch').off().on('keypress', function (e) {
                    if (e.which == 13) {
                        $('#select_resource_table').bootstrapTable('refresh');
                    }
                })
                $('#select_resource_table th[data-field="user"]').css('width', '10%');
                $('#select_resource_table th[data-field="status"]').css('width', '10%');
            },
            columns: [ //列定义
                {
                    checkbox: true,
                    sortable: false,
                    formatter: function (value, row, index) {
                        if (row.is_active === false) {
                            return {
                                disabled: true
                            };
                        }
                        for (var i = 0; i < resourceGroupAllocationData.length; i++) {

                            if (row.uuid == resourceGroupAllocationData[i]) {
                                return true
                            }
                        }
                        return false
                    }
                },
                {
                    field: 'name', //字段名
                    title: LANG.UI_USER_RESOURCE_NAME,
                    sortable: false,
                },
                {
                    field: 'user', //字段名
                    title: LANG.UI_CLIENT_OWNER,
                    sortable: false,
                },
                {
                    field: 'status',
                    title: LANG.UI_PUBLIC_STATUS,
                    sortable: false,
                    formatter: function (value, row, index, field) {
                        return '<span class="label label-sm label-' + row.class + ' status-icon">' + value + '</span>'
                    }
                },
            ]
        }
        sessionStorage.removeItem("select_resource_table_pageRecord");
        $('#select_resource_table').bootstrapTable('destroy');
        sessionStorage.removeItem("select_resource_table_pageRecord");
        $('#select_resource_table').baseTableConfig().init(options);
    }
    //给用户分配资源
    var allocationResource = function (type, sourceType) {
        var params = {};
        params.type = type;
        params.source_type = sourceType;

        var select = $('#select_resource_table').bootstrapTable('getSelections');
        if (!select.length) {
            return UIToastr.showInfo(LANG.UI_USER_GROUP_RESOURCE_ALLOCATION, LANG.UI_RESOURCE_ALLOCATION_SELECT_ONE);
        }
        var source_list = [];
        for (let i = 0; i < select.length; i++) {
            if ((sourceType == 3 || sourceType == 58 || sourceType == 61) && type == 1) {
                // 虚拟机资源的话需要跟上 _vcenteruuid
                source_list.push(select[i].uuid + '|' + select[i].vcenter_uuid);
            } else {
                source_list.push(select[i].uuid);
            }
        }
        params.source_list = source_list;

        // 提示是否要完成转移
        let msg = LANG.UI_USER_RESOURCE_ALLOCATION_CONFIRM;
        msg = msg.replace(/%A%/g, nowUserName);
        bootbox.confirm({
            title: LANG.UI_RESOURCE_GROUP_ALLOCATION,
            message: msg,
            callback: debounce(function (r) {
                if (!r) return;
                pAjaxRequest(params, '/api/v1/users/' + nowUserUuid + '/allocation', 'POST', (res) => {
                    if (operateResponseList(res, LANG.UI_RESOURCE_GROUP_ALLOCATION)) {
                        $('#resource_allocation_modal').modal('hide');
                    }
                });
            },300)
        })
    }

    /**
     * @function 初始化用户
     * @param select 下拉框dom
     */
    var initUser = function (select, user_uuid = '') {
        pAjaxRequest({allocation:1,limit:100}, '/api/v1/users', 'get', function (res) {
            select.empty();
            var option = $("<option>").text(LANG.UI_USER_ALLOCATION_SELECT).val('0');
            select.append(option);
            for (var i = 0; i < res.data.rows.length; i++) {
                if (user_uuid == res.data.rows[i].user_uuid) {
                    continue;
                }
                var option = $("<option>").text(res.data.rows[i].user_name).val(res.data.rows[i].user_uuid);
                select.append(option);
            }
        });
    }

    //初始化资源转移权限资源列表
    var initTransferResource = function () {
        $('#select_transfer_resource').bootstrapTable('destroy');
        pAjaxRequest({user_uuid:nowUserUuid, transfer: 1}, '/api/v1/users/auth', 'GET', function (res) {
            var data = res.data;
            let body1 = '';
            let body2 = '';
            let body3 = '';
            for (let i = 0; i < data.rows.length; i++) {
                if (data.rows[i].type == 1) {
                    // 模块数据
                    body1 += '<label style="width:50%;margin-bottom:12px"><input type="checkbox" data-checkbox="icheckbox_square-blue" data-mode="1" class="icheck" value="' + data.rows[i].uuid + '"><span style="vertical-align: top;margin-left: 8px;">' + data.rows[i].name + '</span></label>'
                } else if (data.rows[i].type == 2) {
                    // 资源
                    let tips = '';
                    if (data.rows[i].uuid == 'resmanagement') {
                        tips = '<a class="popovers show_resource_tips ml15" data-container="body" data-trigger="hover" data-placement="right" data-content="'+ LANG.UI_USER_GROUP_INCLUDE_VM_CLIENT + '\n' +
                            LANG.UI_USER_GROUP_INCLUDE_STRATEGY + '" data-original-title="" title="" style="">\n' +
                            '                        <i class="viconfont vicon-tishi"></i>\n' +
                            '                    </a>';
                    }
                    body2 += '<label style="width:50%;margin-bottom:12px"><input type="checkbox" data-checkbox="icheckbox_square-blue" data-mode="1" class="icheck" value="' + data.rows[i].uuid + '"><span style="vertical-align: top;margin-left: 8px;">' + data.rows[i].name + '</span>' + tips + '</label>'
                } else {
                    // 其它
                    body3 += '<label style="width:50%;margin-bottom:12px"><input type="checkbox" data-checkbox="icheckbox_square-blue" data-mode="1" class="icheck" value="' + data.rows[i].uuid + '"><span style="vertical-align: top;margin-left: 8px;">' + data.rows[i].name + '</span></label>'
                }
            }
            if (body1 != '') {
                body1 = '<div class="div_item"><span class="title">' + LANG.UI_USER_GROUP_MODULE_DATA + '</span><span class="des">' + LANG.UI_USER_GROUP_MODULE_DATA_DESC +'</span></div>' + body1;
            }
            if (body2 != '') {
                body2 = '<div class="div_item"><span class="title">' + LANG.UI_USER_GROUP_RESOURCE + '</span><span class="des">' + LANG.UI_USER_GROUP_RESOURCE_DESC +'</span></div>' + body2;
            }
            if (body3 != '') {
                body3 = '<div class="div_item"><span class="title">' + LANG.UI_USER_GROUP_OTHER + '</span><span class="des">'+ LANG.UI_USER_GROUP_OTHER_DESC + '</span></div>' + body3;
            }

            let body = body1 + body2 + body3;
            $('#resource_div').empty();
            $('#resource_div').append(body);
            $('.show_resource_tips').popover(); //初始化tips
        });
    }

    //转移资源
    var transferResource = function () {
        var params = {};
        params.manage_uuid = $('#resource_transfer_modal #select_receive_user').val();
        if (params.manage_uuid === '0') {
            return UIToastr.showInfo(LANG.UI_USER_GROUP_RESOURCE_ALLOCATION, LANG.UI_USER_RESOURCE_ALLOCATION_SELECT_ONE);
        }

        var source_list = [];
        $('#resource_div input[type="checkbox"]').each(function () {
            if ($(this).is(':checked')) {
                source_list.push(($(this).val()));
            }
        })
        params.source_list = source_list;
        if (!source_list.length) {
            return UIToastr.showInfo(LANG.UI_USER_GROUP_RESOURCE_ALLOCATION, LANG.UI_RESOURCE_ALLOCATION_SELECT_ONE);
        }

        // 提示是否要完成转移
        let msg = LANG.UI_USER_RESOURCE_TRANSFERING_CONFIRM;
        var selectedText = $("#resource_transfer_modal #select_receive_user option:selected").text();
        msg = msg.replace(/%A%/g, nowUserName).replace(/%B%/g, selectedText);
        bootbox.confirm({
            title: LANG.UI_USER_RESOURCE_TRANSFER,
            message: msg,
            callback: debounce(function (r) {
                if (!r) return;
                pAjaxRequest(params, '/api/v1/users/' + nowUserUuid + '/transfer', 'POST', (res) => {
                    if (operateResponseList(res, LANG.UI_USER_RESOURCE_TRANSFER)) {
                        $('#resource_transfer_modal').modal('hide');
                    }
                });
            },300)
        })
    }

    //初始化管理用户
    var initManagerUser = function (user_uuid) {
        $('#select_permission').bootstrapTable('destroy');


        let selectPermissionOp = {
            'click .operate':(event, value, row, index)=>{
                $('#select_permission .operate').each(function (k, v) {
                    if(index == k){
                        // 获取点击checkbox的状态
                        let isChecked = $(this).is(':checked');
                        $('#select_permission .look' + index).prop('checked', isChecked);
                    }
                })
            }
        }

        pAjaxRequest({user_uuid: user_uuid}, '/api/v1/users/auth', 'GET', function (res) {
            var options = {
                data: res.data,
                pagination: false,
                resizable: false,
                columns: [ //列定义
                    {
                        field: 'name',
                        sortable: false,
                        title: LANG.UI_USER_PERMISSION_TYPE,
                    },
                    {
                        field: 'look_auth',
                        sortable: false,
                        title: '<label><input type = "checkbox" class="check-all-look"><span class="look-btn">' + LANG.UI_PUBLIC_LOOK + '</span></label>',
                        formatter: checkboxLook
                    },
                    {
                        field: 'operate_auth',
                        title: '<label><input type = "checkbox" class="check-all-op"><span class="look-btn">' + LANG.UI_USER_MANAGEMENT + '</span></label>',
                        sortable: false,
                        formatter: checkboxOp,
                        events: selectPermissionOp
                    }
                ]
            };
            sessionStorage.removeItem("select_permission_pageRecord");
            $('#select_permission').baseTableConfig().init(options);
        }, async = false);

        $('#select_permission').on('change', '.check-all-look', function () {
            let isChecked = $(this).is(':checked');

            // 将头部复选框的状态应用到所有下方的复选框
            $('#select_permission .look').prop('checked', isChecked);
        })

        $('#select_permission').on('change', '.check-all-op', function () {
            let isChecked = $(this).is(':checked');

            // 将头部复选框的状态应用到所有下方的复选框
            $('#select_permission .operate').prop('checked', isChecked);

            // 选择了操作权限 需要一并勾选查看权限
            $('#select_permission .check-all-look').prop('checked', isChecked);
            $('#select_permission .look').prop('checked', isChecked);
        })

    }

    //初始化管理用户旧信息
    var initManagerOldInfo = function (user_uuid, managerSelect) {
        pAjaxRequest({}, '/api/v1/users/' + user_uuid + '/manager', 'GET', function (res) {
            managerSelect.val('' + res.data.manage_uuid + '');
            $('#select_permission tbody input[type=checkbox]').each(function (k, v) {
                $(this).prop('checked', false);
                if ($.inArray($(this).val(), res.data.auth_list) != -1) {
                    $(this).prop('checked', true);
                }
            })
        })
    }
    //分配管理用户
    var managerAllocation = function (userId) {
        var params = {};

        var auth_list = [];
        $('#manager_allocation_modal .look').each(function () {
            if ($(this).is(':checked')) {
                auth_list.push($(this).val());
            }
        })

        $('#manager_allocation_modal .operate').each(function () {
            if ($(this).is(':checked')) {
                auth_list.push($(this).val());
            }
        })
        var managerUserUuid =  $('#manager_allocation_modal #select_manager_user').val();
        let msg = '';
        if(managerUserUuid == 0 || auth_list.length == 0){
            msg = LANG.UI_USER_RESOURCE_CANCEL_MANAGER_CONFIRM;
        }else{
            // 提示是否要分配管理
            msg = LANG.UI_USER_RESOURCE_MANAGER_CONFIRM;
        }
        var selectedText = $("#manager_allocation_modal #select_manager_user option:selected").text();
        msg = msg.replace(/%A%/g, userName).replace(/%B%/g, selectedText);
        params.manage_uuid = auth_list.length == 0 ? '' : managerUserUuid;
        params.auth_list = auth_list;
        bootbox.confirm({
            title: LANG.UI_USER_LAST_ALLOCATION_ADMIN_USER,
            message: msg,
            callback: debounce(function (r) {
                if (!r) return;
                pAjaxRequest(params, '/api/v1/users/' + userId + '/manager', 'POST', (res) => {
                    if (operateResponseList(res, LANG.UI_USER_LAST_ALLOCATION_ADMIN_USER)) {
                        $('#manager_allocation_modal').modal('hide');
                        $('#user_table').bootstrapTable('refresh');
                    }
                });
            },300)
        })

    }

    var checkboxLook = function (value, row, index, field) {
        return `<input type="checkbox" class="look look${index}" value="${value}">`;
    }

    var checkboxOp = function (value, row, index, field) {
        return `<input type="checkbox" class="operate operate${index}" value="${value}">`;
    }

    function initTableHeight() {
        //拿到父窗口的高度
        var height;
        var panelH = window.innerHeight;

        height = panelH - 381;

        $("#userMangerDiv .fixed-table-body").css({
            "height": height
        });
    }

    /**
     * @function 初始化用户表格
     */
    var handleRecords = function () {

        var operationFormatter = function (value, row, index, field) {
            var button = '<div class="btn-group dropdown-wrapper">';
            if (index > 5) {
                button = '<div class="btn-group dropdown-wrapper dropup">';
            }

            button += '<button style="line-height:16px" type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown" ' +
                'data-hover="dropdown" data-delay="1000" data-close-others="true">' +
                '' + LANG.UI_PUBLIC_OPERATION + ' <i class="fa fa-angle-down"></i>' +
                '</button>' +
                '<ul class="dropdown-menu min-width100" role="menu">';

            button += '<li class="view"><a href="javascript:;"><i class="viconfont vicon-a-Eyesyanjing"></i> ' + LANG.UI_USER_LOOK_MANAGER_INFO + ' </a></li>';

            if ($.inArray('p_safety_user_edit', CONF.PERMISSION_ARR) !== -1 && ((!row.is_global_observer_flag && (isMaster || !row.is_global)) || (row.is_global_observer_flag && row.is_global_observer_manager_flag))) {
                button += '<li class="edit"><button class="btn dropdown-menu__item me-0" data-toggle="drawer" data-target="#add_user_drawer" aria-haspopup="true" aria-expanded="false" ><i class="viconfont vicon-edit-new"></i> ' + LANG.UI_USER_EDIT_SELECT + ' </button></li>';
            }else{
                button += '<li class="edit"><button class="btn dropdown-menu__item me-0" data-toggle="drawer" data-target="#add_user_drawer" aria-haspopup="true" aria-expanded="false" disabled><i class="viconfont vicon-edit-new"></i> ' + LANG.UI_USER_EDIT_SELECT + ' </button></li>';
            }

            if ($.inArray('p_safety_user_allocation_resource', CONF.PERMISSION_ARR) !== -1 && ((!row.is_global_observer_flag) || (row.is_global_observer_flag && row.is_global_observer_manager_flag))) {
                // 资源分配
                button += '<li class="allocation-resource"><button class="btn dropdown-menu__item me-0"><i class="viconfont vicon-a-Pie-twojindu2"></i> ' + LANG.UI_RESOURCE_GROUP_ALLOCATION + ' </a></li>';
            }else{
                button += '<li class="allocation-resource"><button class="btn dropdown-menu__item me-0" disabled><i class="viconfont vicon-a-Pie-twojindu2"></i> ' + LANG.UI_RESOURCE_GROUP_ALLOCATION + ' </a></li>';
            }

            if ($.inArray('p_safety_user_storage_transfer', CONF.PERMISSION_ARR) !== -1 && ((!row.is_global_observer_flag && row.tenant_uuid == '') || (row.is_global_observer_flag && row.is_global_observer_manager_flag))) {
                // 资源转移
                button += '<li class="transfer"><button class="btn dropdown-menu__item me-0"><i class="viconfont vicon-a-Nested-arrowsqiantaojiantou"></i> ' + LANG.UI_RESOURCE_GROUP_TRANSFER + ' </button></li>';
            }else{
                button += '<li class="transfer"><button class="btn dropdown-menu__item me-0" disabled><i class="viconfont vicon-a-Nested-arrowsqiantaojiantou"></i> ' + LANG.UI_RESOURCE_GROUP_TRANSFER + ' </button></li>';
            }

            if ($.inArray('p_safety_user_manager_allocation', CONF.PERMISSION_ARR) !== -1 && ((!row.is_global_observer_flag) || (row.is_global_observer_flag && row.is_global_observer_manager_flag))) {
                // 分配管理用户
                button += '<li class="allocation-user"><button class="btn dropdown-menu__item me-0"><i class="viconfont vicon-a-People-bottom-cardrenxiangkapianxia"></i> ' + LANG.UI_USER_LAST_ALLOCATION_ADMIN_USER + ' </button></li>';
            }else{
                button += '<li class="allocation-user"><button class="btn dropdown-menu__item me-0" disabled><i class="viconfont vicon-a-People-bottom-cardrenxiangkapianxia"></i> ' + LANG.UI_USER_LAST_ALLOCATION_ADMIN_USER + ' </button></li>';
            }

            button += '</ul></div>';
            return button;
        }

        var userOp = {
            'click .view': function (event, value, row, index) {
                let user_uuid = row.user_uuid;

                initResourceTable(user_uuid,row);
                initResourcegroupTable(user_uuid,row);
                initPermissionTree(user_uuid);
                initGridFlag = true;
                $('#user_info_modal').modal({
                    'width': '693px'
                });
                $('#user_info_modal .cancel').unbind('click').click(function () {
                    $('#user_info_modal').modal('hide');
                })
            },
            'click .edit': function (event, value, row, index) {
                if (checkAuth('edit',row)) {
                    checkOperateAuth(checkAuth('edit',row), function (){
                        $('#add_user_drawer #drawer-1-title-1').html('<i class="viconfont vicon-edit-new"></i>');
                        $('#add_user_drawer #drawer-1-title-2').html(LANG.UI_USER_MODIFY);
                        let addManager = $('#manager');
                        userUuid = row.user_uuid;
                        userName = row.user_name;
                        removeValidateStyle();
                        initPassComplexity();
                        handleEditValidation();
                        initUser(addManager);
                        clearAllOption(); //清除form表单
                        backfillForm(row); //回填form
                        $('#add_user_drawer #submit').prop('data-action', 'edit');
                        $('#add_user_drawer .cancel').on('click', function () {
                            $('#add_user_drawer').drawer('hide');
                        })
                        $('.locationDiv').show();
                        if(row.user_type == CONF.FLAG.UNSET){
                            $('.locationDiv').hide();
                        }
                        if(CONF.VENDOR == 'vdms'){
                            $('#customePassword_div').show();
                        }else{
                            $('#customePassword_div').hide();
                        }
                        // 租户隐藏备份存储容量
                        if(row.tenant_uuid != ''){
                            $('#storageMode').parent().parent().hide();
                        }else{
                            $('#storageMode').parent().parent().show()
                        }
                        if ($('#is_three_powers').val() && CONF.CHANGE_OTHER_PASSWD) {
                            // 三权模式下，并且设置不可修改别人的密码，那么隐藏修改密码的选项
                            $('#password').closest('.form-group').hide();
                            $('input[name="rpassword"]').closest('.form-group').hide();
                        }
                        if(row.user_type == CONF.FLAG.SET || row.user_type == 3){
                            $('.domainDiv').hide();
                        }
                    })
                }
            },
            'click .allocation-resource': function (event, value, row, index) {
                if (checkAuth('allocation-resource',row)) {
                    checkOperateAuth(checkAuth('allocation-resource',row), function (){
                        let user_uuid = row.user_uuid;
                        nowUserUuid = user_uuid;
                        nowUserName = row.user_name;
                        initAllocationResource();
                        $('#resource_allocation_modal').modal({
                            'width': '800px'
                        });
                        $('#resource_allocation_modal .cancel').unbind('click').click(function () {
                            $('#resource_allocation_modal').modal('hide');
                        })
                    })
                }
            },
            'click .transfer': function (event, value, row, index) {
                if (checkAuth('transfer',row)) {
                    checkOperateAuth(checkAuth('transfer',row), function (){
                        if (row.is_transfer) {
                            // 如果在转移中 那么就不能再次重复转移
                            UIToastr.showInfo(LANG.UI_RESOURCE_GROUP_TRANSFER, LANG.UI_USER_RESOURCE_TRANSFERING);
                            return;
                        }
                        let user_uuid = row.user_uuid;
                        nowUserUuid = user_uuid;
                        nowUserName = row.user_name;
                        let select = $('#resource_transfer_modal #select_receive_user');
                        initUser(select, user_uuid);
                        initTransferResource();
                        $('#resource_transfer_modal').modal({
                            'width': '693px',
                            'height': '510px'
                        });
                        $('#resource_transfer_modal .add_submit').unbind('click').click(function () {
                            transferResource();
                        })
                        $('#resource_transfer_modal .cancel').unbind('click').click(function () {
                            $('#resource_transfer_modal').modal('hide');
                        })
                    })

                }
            },
            'click .allocation-user': function (event, value, row, index) {
                if (checkAuth('allocation-user',row)) {
                    checkOperateAuth(checkAuth('allocation-user',row), function (){
                        $('#manager_allocation_modal').modal({
                            'width': '693px',
                            'height': '420px'
                        });
                        let user_uuid = row.user_uuid;
                        userUuid = row.user_uuid;
                        userName = row.user_name;
                        nowUserUuid = user_uuid;
                        nowUserName = row.user_name;
                        let managerSelect = $('#manager_allocation_modal #select_manager_user');
                        initUser(managerSelect, user_uuid);


                        initManagerUser(user_uuid);
                        initManagerOldInfo(user_uuid, managerSelect);

                        $('#manager_allocation_modal .cancel').unbind('click').click(function () {
                            $('#manager_allocation_modal').modal('hide');
                        })
                    })
                }
            },
        }
        // 根据授权来控制按钮的显示和隐藏
        var beforeInput = '';
        if ($.inArray('p_safety_user_delete', CONF.PERMISSION_ARR) !== -1) {
            // 删除
            beforeInput = `<div style="cursor:not-allowed;"><button class="btn viconfont vicon-a-Deleteshanchu1 b-btn brr2 mr12 exch-forbid-event" id="delete_user"></button></div>`
        }

        var afterInput = '';
        if ($.inArray('p_safety_user_add', CONF.PERMISSION_ARR) !== -1) {
            // 新建
            afterInput += `<div><button class="btn table-toolbar-btn" id="add_user" data-toggle="drawer" data-target="#add_user_drawer" aria-haspopup="true" aria-expanded="false" >
                                <i class="viconfont vicon-biaogetianjia mr4"></i>
                                <span>` + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_ADD + `</span>
                            </button>`;
        }
        if ($.inArray('p_safety_user_enable', CONF.PERMISSION_ARR) !== -1) {
            // 启用
            afterInput += `<button class="btn table-toolbar-btn" id="unlock">
                                <i class="viconfont vicon-a-Unlockjiesuo-0111 mr4"></i>
                                <span>` + LANG.BILLING_ON_LOCK + `</span>
                            </button>`;
        }
        if ($.inArray('p_safety_user_disable', CONF.PERMISSION_ARR) !== -1) {
            // 禁用
            afterInput += `<button class="btn table-toolbar-btn" id="lock">
                                <i class="viconfont vicon-a-Unlockjiesuo-011 mr4"></i>
                                <span>` + LANG.BILLING_OFF_LOCK + `</span>
                            </button>`;
        }

        if ($.inArray('p_safety_user_role_allot', CONF.PERMISSION_ARR) !== -1) {
            // 分配角色
            afterInput += `<button class="btn table-toolbar-btn" id="role_allot" >
                                <i class="viconfont vicon-fenfa mr4"></i>
                                <span>` + LANG.UI_USER_AUTH_MANAGE + `</span>
                            </button>`;
        }

        if (afterInput != '') {
            afterInput = '<div>' + afterInput + '</div>';
        }

        //表格初始化配置项
        let options = {
            toolbarId: '#vin_user_toolbar',
            buttonsToolbar: '#vin_user_toolbar .vin_btnToolbar',
            // vin_toolbar: '.vin_user_toolbar',
            vin_url: '/api/v1/users',
            vin_method: 'GET',
            vin_params: function () {
                var params = {};
                params.search = params.search = $('#vin_user_toolbar .userSearch').val();
                return params;
            },
            placeholder: LANG.UI_USER_SEARCH, //搜索框的placeholder
            searchInput: true, //搜索框
            searchClass: 'userSearch', //自定义的搜索框类名
            searchSelector: '.userSearch', //选择使用自定义搜索框
            showColumns: true,
            showExport: true,
            paginationLoop: false,
            fileName:LANG.UI_COPY_DETAIL_USER_LIST_LABEL,
            sortName: 'create_time',
            sortOrder: 'desc',
            onResetView: initTableHeight,
            onCheck: function (row) {
                modifyDelStyle('user_table', 'delete_user');
                userData.push(row.user_uuid)
            },
            onUncheck: function (row) {
                modifyDelStyle('user_table', 'delete_user');
                var index = userData.indexOf(row.user_uuid); // 查找元素的索引
                if (index !== -1) {
                    userData.splice(index, 1); // 从数组中删除一个元素
                }
            },
            onCheckAll:function (row) {
                modifyDelStyle('user_table', 'delete_user');
                for (var i = 0; i < row.length; i++) {
                    var index = userData.indexOf(row[i].user_uuid); // 查找元素的索引
                    if (index == -1) {
                        userData.push(row[i].user_uuid)
                    }
                }
            },
            onUncheckAll: function (row) {
                modifyDelStyle('user_table', 'delete_user');
                for (var i = 0; i < row.length; i++) {
                    var index = userData.indexOf(row[i].user_uuid); // 查找元素的索引
                    if (index != -1) {
                        userData.splice(index, 1); // 从数组中删除一个元素
                    }
                }
            },
            PostBody: function () {
                $('#vin_user_toolbar .search-btn').off().on('click', function () {
                    $('#user_table').bootstrapTable('refresh');
                })
            },
            customTool: {
                beforeInput: beforeInput,
                afterInput: afterInput,
            },

            columns: [ //列定义
                {
                    checkbox: true,
                    sortable: false, //默认可排序，禁用排序才写此项
                    formatter: function (value, row, index, field) {
                        if (row.checked === false || (row.is_global && !isMaster) || (row.is_global_observer_flag && !row.is_global_observer_manager_flag)) {
                            // 如果 is_global == true and isMaster == false，也要禁用，不能操作
                            return {
                                disabled: true
                            };
                        }
                    }
                },
                {
                    field: 'num',
                    title: LANG.UI_PUBLIC_TABLE_ID,
                    sortable: false, //默认可排序，禁用排序才写此项
                },
                {
                    field: 'user_name',
                    title: LANG.UI_STORAGE_DETAIL_USERNAME,
                },
                {
                    field: 'user_type_des',
                    title: LANG.UI_USER_TYPE,
                    sortable: false, //默认可排序，禁用排序才写此项
                },
                {
                    field: 'tenant_name',
                    title: LANG.UI_TENANT_NAME,
                    sortable: false, //默认可排序，禁用排序才写此项
                },
                {
                    field: 'create_time',
                    title: LANG.UI_JOB_CREATE_OR_MODIFI_TIME
                },
                {
                    field: 'create_user_name',
                    title: LANG.UI_REPORT_BUILDER,
                },
                {
                    field: 'manager_name',
                    title: LANG.UI_USER_ADMIN,
                    sortable: false, //默认可排序，禁用排序才写此项
                },
                {
                    field: 'email',
                    title: LANG.UI_USER_EMAIL_ADDR,
                },
                {
                    field: 'telephone',
                    title: LANG.UI_USER_PHONE_NUMBER,
                },
                {
                    field: 'last_login_time',
                    title: LANG.UI_USER_LAST_LOGIN_TIME,
                },
                {
                    field: 'lock_flag',
                    title: LANG.UI_PUBLIC_STATUS,
                    formatter: function (value, row, index, field) {
                        if (value == 1) {
                            return '<span class="label label-sm label-success status-icon">' + LANG.UI_BLACK_WHITE_ENABLE + '</span>'
                        } else {
                            return '<span class="label label-sm label-danger status-icon"  style="width:auto; min-width:40px">' + LANG.UI_BLACK_WHITE_DISABLE + '</span>';
                        }
                    }
                },
                {
                    field: 'is_transfer',
                    title: LANG.UI_USER_RESOURCE_TRANSFER,
                    sortable: false, //默认可排序，禁用排序才写此项
                    formatter: function (value, row, index, field) {
                        if (value == true) {
                            return '<span class="label label-sm label-danger status-icon" style="width:auto; min-width:40px">' + LANG.UI_USER_RESOURCE_TRANSFERING +  '</span>'
                        } else {
                            return '<span class="label label-sm label-success status-icon"  style="width:auto; min-width:40px">' + LANG.UI_JOB_CROWD_NORMAL + '</span>';
                        }
                    }
                },
                {
                    field: 'backup_data',
                    title: LANG.UI_HOMEPAGEPRO_BACKUP_DATA,
                    sortable: false,
                    align: 'center'
                },
                {
                    title: LANG.UI_PUBLIC_OPERATION,
                    formatter: operationFormatter,
                    events: userOp,
                    opButton: true,
                    clickToSelect: false, //不可通过点击行选中
                    sortable: false, //默认可排序，禁用排序才写此项
                }
            ]
        }
        sessionStorage.removeItem('user_table_pageRecord')
        $('#user_table').baseTableConfig().init(options);
    }

    //初始化权限分配表格
    var initPermissionTable = function () {
        pAjaxRequest({}, '/api/v1/users/auth', 'GET', function (res) {
            var options = {
                data: res.data,
                pagination: false,
                // showExport: false, //是否开启导出按钮
                // showColumns: false, //是否开启列选择按钮
                resizable: false,
                columns: [ //列定义
                    {
                        field: 'name',
                        sortable: false,
                        title: LANG.UI_USER_PERMISSION_TYPE,
                    },
                    {
                        field: 'look_auth',
                        sortable: false,
                        title: '<label><input type = "checkbox" class="check-all-look"><span class="look-btn">' + LANG.UI_PUBLIC_LOOK + '</span></label>',
                        formatter: checkboxLook,
                        events:permissionLook
                    },
                    {
                        field: 'operate_auth',
                        title: '<label><input type = "checkbox" class="check-all-op"><span class="look-btn">' + LANG.UI_USER_MANAGEMENT + '</span></label>',
                        sortable: false,
                        formatter: checkboxOp,
                        events: permissionOp
                    }
                ]
            };
            sessionStorage.removeItem("select_manager_permission_pageRecord");
            $('#select_manager_permission').baseTableConfig().init(options);
        });

        $('#select_manager_permission').on('change', '.check-all-look', function () {
            let isChecked = $(this).is(':checked');

            // 将头部复选框的状态应用到所有下方的复选框
            $('#select_manager_permission .look').prop('checked', isChecked);
            if(!isChecked){
                // 取消了查看权限 需要一并勾选操作权限
                $('#select_manager_permission .check-all-op').prop('checked', isChecked);
                $('#select_manager_permission .operate').prop('checked', isChecked);
            }
        })

        $('#select_manager_permission').on('click', '.check-all-op', function () {
            let isChecked = $(this).is(':checked');

            // 将头部复选框的状态应用到所有下方的复选框
            $('#select_manager_permission .operate').prop('checked', isChecked);
            // 取消了操作权限，不一定取消查看权限
            if(isChecked){
                $('#select_manager_permission .check-all-look').prop('checked', isChecked);
                $('#select_manager_permission .look').prop('checked', isChecked);
            }
        })

        let permissionLook = {
            'click .look':(event, value, row, index)=>{
                // 获取所有.look元素的数量
                const $lookCheckboxes = $('#select_manager_permission .look');
                const totalLook = $lookCheckboxes.length; // 总数
                let count = 0;
                $('#select_manager_permission .look').each(function (k, v) {
                    if(index == k){
                        // 获取点击checkbox的状态
                        let isChecked = $(this).is(':checked');
                        if(!isChecked){
                            // 取消
                            $('#select_manager_permission .operate' + index).prop('checked', isChecked);
                            $('#select_manager_permission .check-all-look').prop('checked', isChecked);
                            $('#select_manager_permission .check-all-op').prop('checked', isChecked);
                        }
                    }
                    // 获取到checkbox的选中状态
                    let lookChecked = v.checked;
                    if(lookChecked){
                        count++;
                    }
                    if(count == totalLook){
                        $('#select_manager_permission .check-all-look').prop('checked', true);
                        // 获取操作checkbox的状态
                        const $opCheckboxes = $('#select_manager_permission .operate');
                        for(let i =0;i<$opCheckboxes.length;i++){
                            let checked = $opCheckboxes[i].checked;
                            if(!checked){
                                $('#select_manager_permission .check-all-op').prop('checked', checked);
                            }
                        }
                    }
                })
            },
        }

        let permissionOp = {
            'click .operate':(event, value, row, index)=>{
                // 获取所有.op元素的数量
                const $opCheckboxes = $('#select_manager_permission .operate');
                const totalOp = $opCheckboxes.length; // 总数
                let count = 0;
                $('#select_manager_permission .operate').each(function (k, v) {
                    if(index == k){
                        // 获取点击checkbox的状态
                        let isChecked = $(this).is(':checked');
                        if(!isChecked){
                            // 取消
                            $('#select_manager_permission .check-all-op').prop('checked', isChecked);
                        }else{
                            // 选中
                            $('#select_manager_permission .look' + index).prop('checked', isChecked);
                        }
                    }
                    // 获取到checkbox的选中状态
                    let opChecked = v.checked;
                    if(opChecked){
                        count++;
                    }
                    if(count == totalOp){
                        $('#select_manager_permission .check-all-op').prop('checked', true);
                        $('#select_manager_permission .check-all-look').prop('checked', true);
                        // 获取查看checkbox的状态
                        const $lookCheckboxes = $('#select_manager_permission .look');
                        for(let i =0;i<$lookCheckboxes.length;i++){
                            let checked = $lookCheckboxes[i].checked;
                            if(!checked){
                                $('#select_manager_permission .check-all-look').prop('checked', checked);
                            }
                        }
                    }
                })
            }
        }
    }

    //add user 初始化
    var initPassComplexity = function () {
        switch (CONF.PASS_COMPLEXITY) {
            case 1: //弱(包含字母(不区分大小写),数字)
                passTips = LANG.UI_USER_PASSWORD_STRENGTH_WEAK_COMMIT + CONF.PASS_LENGTH + LANG.UI_USER_PASSWORD_STRENGTH_WEAK;
                break;
            case 2: //中(必须包含字母(不区分大小写),数字)
                passTips = LANG.UI_USER_PASSWORD_STRENGTH_COMMIT + CONF.PASS_LENGTH + LANG.UI_USER_PASSWORD_STRENGTH_MEDIUM;
                break;
            case 3: //强(必须包含大小写字母,数字)
                passTips = LANG.UI_USER_PASSWORD_STRENGTH_COMMIT + CONF.PASS_LENGTH + LANG.UI_USER_PASSWORD_STRENGTH_STRONG;
                break;
            default:
                passTips = LANG.UI_USER_PASSWORD_STRENGTH_COMMIT + CONF.PASS_LENGTH + LANG.UI_USER_PASSWORD_STRENGTH_WEAK;
                break;
        }
    }

    var handleAddValidation = function () {
        // for more info visit the official plugin documentation:
        // http://docs.jquery.com/Plugins/Validation

        var form2 = $('#form_sample_2');
        var error2 = $('.alert-danger', form2);
        var success2 = $('.alert-success', form2);
        var userType = $('#usertype').val();
        if (userType == 2) {
            passwordFlag = false;
        }
        userForm = form2.validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "", // validate all fields including form hidden input
            rules: {
                name: {
                    minlength: 2,
                    required: true,
                    usernameAvailable: true,
                    username: true
                },
                password: {
                    required: passwordFlag,
                    passcomplexity: true,
                    compareOldcode: true
                },
                rpassword: {
                    required: passwordFlag,
                    equalTo: "#password",
                    compareOldcode: true
                },
                email: {
                    //                        required: true,
                    email: true
                },
                number: {
                    //                        required: true,
                    number: true
                },

                usertype: {
                    required: true
                },
                customePassword:{
                    passcomplexity: true
                }
            },



            invalidHandler: function (event, validator) { //display error alert on form submit
                success2.hide();
                error2.show();
                Metronic.scrollTo(error2, -200);
            },

            errorPlacement: function (error, element) { // render error placement for each input type
                var icon = $(element).parent('.input-icon').children('i');
                icon.removeClass('fa-check').addClass("fa-warning");
                icon.attr("data-original-title", error.text()).tooltip({
                    'container': 'body'
                });
                icon.show();
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
                icon.show();
            },

            submitHandler: function (form) {
                success2.show();
                error2.hide();
            }

        });

        //添加用户
        $('#submit').unbind('click').click(function () {
            var userType = $('#usertype').val();
            if (userType == '2') {
                passwordFlag = false;
            }
            var currentVal = 0;
            if ($('#storageMode').val() == 2) {
                currentVal = parseInt($('#spinnerNumInput').val()) * parseInt(calSize($('#unit option:selected').text()));
            }
            if (form2.validate().form()) {
                //选择不限制容量，容量无限制，配额小于总配额允许提交
                if ($('#storageMode').val() == 1 || currentVal <= storage || storage == -1) {
                    submit();
                } else {
                    UIToastr.showWarning(LANG.UI_USER_QUOTA_EXCEPTION, LANG.UI_USER_QUOTA_EXCEPTION_EXCEED);
                }
            }
        });

        $.validator.addMethod("passcomplexity", function (value, element) {
            if(!passwordFlag){
                return true;
            }
            var match = "";
            switch (CONF.PASS_COMPLEXITY) {
                case 1: //弱(包含字母(不区分大小写),数字,特殊字符(不是必须))
                    var pattern = "^[\\S]{" + CONF.PASS_LENGTH + ",}$";
                    match = this.optional(element) || new RegExp(pattern).test(value);
                    break;
                case 2: //中(必须包含字母(不区分大小写),数字,特殊字符(不是必须))
                    var pattern = "^(?=.*[0-9])(?=.*[A-Za-z])[\\S]{" + CONF.PASS_LENGTH + ",}$";
                    match = this.optional(element) || new RegExp(pattern).test(value);
                    break;
                case 3: //强(必须包含大小写字母,数字,特殊字符(不是必须))
                    var pattern = "^(?=.*[0-9])(?=.*[A-Z])(?=.*[a-z])[\\S]{" + CONF.PASS_LENGTH + ",}$"
                    match = this.optional(element) || new RegExp(pattern).test(value);
                    break;

            }
            return match;
        }, passTips);
        $.validator.addMethod("compareOldcode",function (value, element) {
                var password =hex_md5(value);
                if(oldPass == password){
                    return false;
                }
                return true;
            },
            LANG.UI_USER_NOT_SAME_OLD_PASSWORD
        );
    }

    var handleEditValidation = function () {
        // for more info visit the official plugin documentation:
        // http://docs.jquery.com/Plugins/Validation
        var form2 = $('#form_sample_2');
        var error2 = $('.alert-danger', form2);
        var success2 = $('.alert-success', form2);

        var userType = $('#usertype').val();
        if (userType == 2) {
            passwordFlag = false;
        }

        userForm = form2.validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "", // validate all fields including form hidden input
            rules: {
                name: {
                    minlength: 2,
                    required: true,
                    usernameAvailable: true
                },
                password: {
                    minlength: CONF.PASS_LENGTH,
                    required: true,
                    passcomplexity: true,
                    compareOldcode: true
                },
                rpassword: {
                    minlength: CONF.PASS_LENGTH,
                    required: true,
                    equalTo: "#password",
                    compareOldcode: true
                },
                email: {
                    //                        required: true,
                    email: true
                },
                number: {
                    //                        required: true,
                    number: true
                },

                usertype: {
                    required: true
                },
            },

            invalidHandler: function (event, validator) { //display error alert on form submit
                success2.hide();
                error2.show();
                Metronic.scrollTo(error2, -200);
            },

            errorPlacement: function (error, element) { // render error placement for each input type
                var icon = $(element).parent('.input-icon').children('i');
                icon.removeClass('fa-check').addClass("fa-warning");
                icon.attr("data-original-title", error.text()).tooltip({
                    'container': 'body'
                });
                icon.show();
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
                icon.show();
            },

            submitHandler: function (form) {
                success2.show();
                error2.hide();
            }

        });

        //添加用户
        $('#submit').unbind('click').click(function () {
            var currentVal = 0;
            if ($('#storageMode').val() == 2) {
                currentVal = parseInt($('#spinnerNumInput').val()) * parseInt(calSize($('#unit option:selected').text()));
            }
            if (form2.validate().form()) {
                //选择不限制容量，容量无限制，配额小于总配额允许提交
                if ($('#storageMode').val() == 1 || currentVal <= storage || storage == -1) {
                    submit();
                } else {
                    UIToastr.showWarning(LANG.UI_USER_QUOTA_EXCEPTION, LANG.UI_USER_QUOTA_EXCEPTION_EXCEED);
                }
            }
        });

        // $("#cancel").click(function () {
        //     //            	CTLSIDEBAR('users');
        //     LOCATION('./content/platform/users/users.php');
        // });

        $.validator.addMethod("passcomplexity", function (value, element) {
            //如果是旧密码md5直接返回
            if (oldPass == value || oldCustomPass == value) return true;
            var match = "";
            if (value !== "") {
                switch (CONF.PASS_COMPLEXITY) {
                    case 1: //弱(包含字母(不区分大小写),数字)
                        var pattern = "^[\\S]{" + CONF.PASS_LENGTH + ",}$";
                        match = this.optional(element) || new RegExp(pattern).test(value);
                        break;
                    case 2: //中(必须包含字母(不区分大小写),数字)
                        var pattern = "^(?=.*[0-9])(?=.*[A-Za-z])[\\S]{" + CONF.PASS_LENGTH + ",}$";
                        match = this.optional(element) || new RegExp(pattern).test(value);
                        break;
                    case 3: //强(必须包含大小写字母,数字)
                        var pattern = "^(?=.*[0-9])(?=.*[A-Z])(?=.*[a-z])[\\S]{" + CONF.PASS_LENGTH + ",}$"
                        match = this.optional(element) || new RegExp(pattern).test(value);
                        break;
                }
                return match;
            } else {
                //密码为空不验证(未生效)
                return true;
            }
        }, passTips);
        $.validator.addMethod("compareOldcode",function (value, element) {
                var password =hex_md5(value);
                if(oldPass == password){
                    return false;
                }
                return true;
            },
            LANG.UI_USER_NOT_SAME_OLD_PASSWORD
        );
    };

    // 字节转换
    var calSize = function (result) {
        var type = ["B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB", "BB", "NB", "DB"];
        var i = 1;
        var j = 0;
        while (type[j] != result) {
            i = i * 1024;
            j++;
        }
        return i;
    }

    var initAddUserListener = function () {
        //默认修改密码开关开启
        $('#editPassWord').bootstrapSwitch('state', true);
        //
        $('#editPassWord').on('switchChange.bootstrapSwitch', function () {
            if (this.checked) {
                edit_pwd_flag = true;
            } else {
                edit_pwd_flag = false;
            }
        })

        //默认关联管理用户开关关闭
        $('#concatUsers').bootstrapSwitch('state', false);
        concat_user_flag = false;
        $('#manage_user_div').hide();
        //
        $('#concatUsers').on('switchChange.bootstrapSwitch', function () {
            if (this.checked) {
                concat_user_flag = true;
                $('#manage_user_div').show();
            } else {
                concat_user_flag = false;
                $('#manage_user_div').hide();
            }
        })
        //初始化多选下拉框
        $(".selectpicker").selectpicker({
            noneSelectedText: LANG.BILLING_PLEASE_SELECT,
            deselectAllText: LANG.BILLING_DESELECT_ALL,
            selectAllText: LANG.BILLING_SELECT_ALL,
            liveSearchPlaceholder: LANG.BILLING_SEARCH,
            countSelectedText: function () {}
        });

        $('#usertype').on('change', initUserDiv);

        //活动目录选择切换事件
        $('#domainlist').on('change', initDomainUserHandler);
        //切换存储方式选择事件
        $('#custom').hide();
        $('#storageMode').on('change', modeSelect);
        initspinner();
        initQuota();
        $('#name').on('keyup', function () {
            //如果不是以startText开头的，就把文本框内的值设为startText
            var userType = $('#usertype').val();
            if (userType == 1 || userType == null) return;
            (this.value.indexOf(startText) === 0) || (this.value = startText);
        });
    };

    //初始化用户组列表
    var initUsergroupList = function () {
        pAjaxRequest({group_type: is_super_role},'/api/v1/users/group/list','GET',function (d){
            var res = d.data;
            var usergroupList = $('#user_Group');
            usergroupList.empty();
            for (var i = 0; i < res.length; i++) {
                var option = $('<option>').text(res[i].usergroup_name).val(res[i].usergroup_uuid);
                usergroupList.append(option);
            }
            usergroupList.selectpicker('refresh');
        });
    }

    //初始化角色列表
    var initRoleList = function () {
        pAjaxRequest({'offset': 0, 'limit': 1000, role_type: is_super_role},'/api/v1/roles','GET',function (d){
            var rows = d.data.rows;
            var roleList = $("#user_Role");
            roleList.empty();
            for(var i=0;i<rows.length;i++){
                var option = $("<option>").text(rows[i].role_name).val(rows[i].role_uuid);
                roleList.append(option);
            }
            roleList.selectpicker('refresh');
        });
    }

    //初始化AD域供应商列表
    var initDomainList = function () {
        pAjaxRequest({},'/api/v1/domains/list','GET',function (d){
            var res = d.data;
            var domainlist = $('#domainlist');
            domainlist.empty();
            var option = $('<option>').text(LANG.UI_DOMAIN_SERVER_SELECT_PROVIDER).val("");
            domainlist.append(option);
            for (var i = 0; i < res.length; i++) {
                var option = $('<option>').text(res[i].text).val(res[i].value);
                domainlist.append(option);
            }
        })
    }

    var initUserDiv = function () {
        $('#name').val('');
        if (this.value == "1") {
            $('.domainDiv').hide();
            $('.locationDiv').show();
            $('#force-edit-pwd').show();
            $('#password').val('');
            $('input[name=rpassword]').val('');
            $('#name').prop("disabled", false);
            if (CONF.TENANTUUID == "") {
                $('.tenantSelectDiv').show();
            }
        } else {
            $('.locationDiv').hide();
            $('#force-edit-pwd').hide();
            $('.domainDiv').show();
            $('#name').prop("disabled", true);
            $('#domainlist').val('');
            $('#password').val('Admin@3R');
            $('input[name=rpassword]').val('Admin@3R');
            //隐藏租户关联
            $('.tenantSelectDiv').hide();
        }
        startText = $('#name').val(); //获取用户名开头字符串
    }

    var initDomainUserHandler = function () {
        var domainuuid = this.value;
        var domainname = $('#domainlist option:selected').text();
        if (domainuuid != "") {
            var des = domainname + "\\";
            $('#name').val(des);
            $('#name').prop("disabled", false);
        } else {
            $('#name').empty();
            $('#name').prop("disabled", true);
        }
        startText = $('#name').val(); //获取用户名开头字符串
        var params = JSON.stringify({
            domainuuid: domainuuid
        });
        $.post(CONF.AJAXPATH, {
            m: CONF.M.DOMAINSERVER,
            f: "getDomainUsers",
            p: params
        }, function (d) {

        });
    }

    //存储空间方式选择
    var modeSelect = function () {
        if ($('#storageMode').val() == 2) {
            $('#custom').show();
        } else {
            $('#custom').hide();
        }
    }

    //初始化spinner
    var initspinner = function () {
        //post 请求数据库备份存储总大小
        //初始化存储单位
        $('#spinnerNum').spinner({
            value: 20,
            step: 5,
            min: 1,
            max: 9999
        });
    }

    var initQuota = function () {
        pAjaxRequest({},'/api/v1/storages/max_resource','GET',function (d) {
            var res = d.data;
            var text = res.stext;
            storage = res.svalue;
            if (text.substr(-2) == 'TB') {
                $('#unit').val(2);
            }
            $('#maxNum').append(text);
        });
    }

    // 初始化用户信息
    var initUserInfo = function () {
        pAjaxRequest({},'/api/v1/users/self/info','GET',function (d){
            if (d.code == 0) {
                isMaster = d.data.user_level == 1;
            }
            if (!isMaster) {
                $('#div_global_auth').hide();
            }
        });
    }

    return {
        //main function to initiate the module
        init: function () {
            handleAddValidation();
            initUserInfo();
            handleRecords();
            initUserPassword();
            addListeners();
            initAddUserListener();
            initTableHeight();
            initPermissionTable();
            initVMType(); //初始化虚拟化类型
            initPublicType(); //初始化虚拟化类型
            initPrivateType(); //初始化虚拟化类型
        }
    };
}();

jQuery(document).ready(function () {
    TableAjax.init();
});