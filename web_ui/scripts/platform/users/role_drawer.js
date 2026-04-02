var AddEditRole = function(){
    var zTree;
    var roleUuid = '';
    var _permissionUuid = '';
    var _isUsed = false;
    var _permission = [];

    // 新增函数：获取同一层级的兄弟节点
    var getSiblingNodes = function(node) {
        if (!node || !node.getParentNode) return [];

        var parentNode = node.getParentNode();
        if (!parentNode) return [];

        return parentNode.children || [];
    };

    // 新增函数：检查同一层级是否还有其他被选中的is_operate节点
    var hasOtherOperateNodeChecked = function(node) {
        if (!node) return false;

        var siblingNodes = getSiblingNodes(node);
        if (siblingNodes.length === 0) return false;

        for (var i = 0; i < siblingNodes.length; i++) {
            var sibling = siblingNodes[i];
            // 跳过当前节点
            if (sibling.id === node.id) continue;

            // 检查是否是is_operate节点且被选中
            if (sibling.is_operate && sibling.checked) {
                return true;
            }
        }

        return false;
    };

    // 新增函数：处理选中节点的联动逻辑（双向联动）
    var handleNodeCheck = function(node, checked) {
        if (!node) return;

        var siblingNodes = getSiblingNodes(node);
        if (siblingNodes.length === 0) return;

        var isOperateNode = node.is_operate;

        for (var i = 0; i < siblingNodes.length; i++) {
            var sibling = siblingNodes[i];
            // 跳过自己
            if (sibling.id === node.id) continue;

            // 检查兄弟节点是否被禁用
            if (sibling.chkDisabled) continue;

            if (isOperateNode) {
                // 如果当前节点有is_operate属性
                if (checked) {
                    // 选中时：选中没有is_operate属性的兄弟节点
                    if (!sibling.is_operate) {
                        zTree.checkNode(sibling, true, true);
                    }
                } else {
                    // 取消选中时：只有当没有其他is_operate节点被选中时，才取消没有is_operate属性的兄弟节点
                    if (!sibling.is_operate && !hasOtherOperateNodeChecked(node)) {
                        zTree.checkNode(sibling, false, true);
                    }
                }
            } else {
                // 如果当前节点没有is_operate属性
                if (!checked) {
                    // 取消选中时：取消选中有is_operate属性的兄弟节点
                    if (sibling.is_operate) {
                        zTree.checkNode(sibling, false, true);
                    }
                }
                // 选中时：不需要联动选中有is_operate属性的兄弟节点
            }
        }
    };

    //修改nodeSelect函数，增加双向联动逻辑
    var nodeSelect = function(treeId, treeNode, clickFlag){
        var checked = !treeNode.checked;
        var treeObj = $.fn.zTree.getZTreeObj(treeId);

        // 先执行正常的选中/取消选中
        treeObj.checkNode(treeNode, checked, true, true);

        // 处理联动逻辑（无论是否有is_operate属性都需要处理）
        handleNodeCheck(treeNode, checked);
    }

    //修改设置，增加checkNode回调
    var initUserTree = function(d){
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
                // 新增：处理通过API直接checkNode的情况
                onCheck: function(event, treeId, treeNode) {
                    // 处理联动逻辑
                    handleNodeCheck(treeNode, treeNode.checked);
                }
            }
        };
        var userInfo = d.data;

        // 默认关闭并且隐藏
        $('#div_global_auth').hide();
        $('#choose_global_flag').bootstrapSwitch('state', false);
        $('#permissionTree').html('');

        if (!userInfo.global_read) {
            $('#global_read').hide();
        }
        if (!userInfo.global_write) {
            $('#global_write').hide();
        }

        zTree = $.fn.zTree.init($("#permissionTree"), setting, userInfo.nodes);
        var nodes = zTree.transformToArray(zTree.getNodes());

        // 新增：用于跟踪已经处理过的节点，避免重复触发联动
        var processedNodes = {};

        if (roleUuid != '') {
            // 首先选中所有应该选中的节点（不触发联动）
            for(var i=0; i<nodes.length; i++){
                zTree.setChkDisabled(nodes[i], false, false, false);
                if($.inArray(nodes[i].id, _permission) >= 0){
                    if(nodes[i].type >= 1){
                        zTree.setChkDisabled(nodes[i], false, false, false);
                        if($.inArray(nodes[i].id , _permission) >= 0 ){
                            // 标记为已处理，避免onCheck回调触发
                            processedNodes[nodes[i].id] = true;
                            // 只选中用户之前有的权限
                            zTree.checkNode(nodes[i], true, true, false); // 不触发回调
                        }
                    }
                }else{
                    zTree.checkNode(nodes[i], false, true, false); // 不触发回调
                }
            }

            // 然后手动处理联动逻辑
            for(var i=0; i<nodes.length; i++){
                if($.inArray(nodes[i].id, _permission) >= 0 && nodes[i].is_operate){
                    // 处理有is_operate属性的选中节点的联动
                    handleNodeCheck(nodes[i], true);
                }
            }
        }

        if (userInfo.global_read || userInfo.global_write) {
            $('#div_global_auth').show();
            $('#choose_global_flag').bootstrapSwitch('disabled', false);  // 可操作
            $('#choose_global_flag').bootstrapSwitch('state', false);  // 关闭
            $('.show-role-used').addClass('display-none');
            if (roleUuid != '') {
                if ($.inArray('global_observer', _permission) !== -1) {
                    $('#choose_global_flag').bootstrapSwitch('state', true);
                    $('#show_global_auth').show();
                }
                if ($.inArray('global_read', _permission) !== -1) {
                    $('#show_global_auth input[type=radio][value="global_read"]').iCheck('check');
                    setDefaultTree('global_read');
                }
                if ($.inArray('global_write', _permission) !== -1) {
                    $('#show_global_auth input[type=radio][value="global_write"]').iCheck('check');
                    $('#global_write').iCheck('check');
                    setDefaultTree('global_write');
                }
                if (_isUsed) {
                    $('.show-role-used').removeClass('display-none');
                    // 已经被使用了，那么不能更改角色的类型
                    $('#choose_global_flag').bootstrapSwitch('disabled', true);
                }
            }
        }

        //需要默认选中切不可修改的概况
        for(var i=0; i<nodes.length; i++){
            if($.inArray(nodes[i].id, ['homepage', 'p_homepage']) !== -1){
                zTree.setChkDisabled(nodes[i], true, false, false);
            }
        }
        Metronic.unblockUI('#role_add_edit_drawer');
    }

    // 修改setDefaultTree函数，避免触发联动
    var setDefaultTree = function (val) {
        var nodes = zTree.transformToArray(zTree.getNodes());
        if (val == 'global_write') {
            // 查看并操作
            for (var i = 0; i<nodes.length; i++) {
                zTree.setChkDisabled(nodes[i], false, false, false);
                //需要默认选中切不可修改的概况
                if($.inArray(nodes[i].id, ['homepage', 'p_homepage']) !== -1){
                    zTree.setChkDisabled(nodes[i], true, false, false);
                }
            }
        } else {
            // 仅查看
            for(var i=0; i<nodes.length; i++){
                if (nodes[i].is_operate) {
                    // 取消选中（不触发回调，避免联动）
                    zTree.checkNode(nodes[i], false, true, false);
                    // 置灰
                    zTree.setChkDisabled(nodes[i], true, false, false);
                }
                //需要默认选中切不可修改的概况
                if($.inArray(nodes[i].id, ['homepage', 'p_homepage']) !== -1){
                    zTree.setChkDisabled(nodes[i], true, false, false);
                }
            }
        }
    }

    // ... 其余函数保持不变 ...

    var initViews = function(){
        if (roleUuid != '') {
            $('.role_drawer_title_i').removeClass('vicon-ge_add_task').addClass('vicon-ge_modify');
            $('#role_drawer_title').html(LANG.UI_ROLE_MODIFY);
            initOldRoleInfo(roleUuid);
        } else {
            $('.role_drawer_title_i').removeClass('vicon-ge_modify').addClass('vicon-ge_add_task');
            $('#role_drawer_title').html(LANG.UI_ROLE_ADD);
            $('#role_name').val('');
            initUserAuth();
        }

    };

    // 转义函数
    function htmlUnescape(escapedStr) {
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = escapedStr;
        return tempDiv.textContent;
    }

    //获取该角色配置信息
    var initOldRoleInfo = function(roleuuid){
        Metronic.blockUI({target: '#role_add_edit_drawer', animate: true, cenrerY: true});
        pAjaxRequest({},'/api/v1/roles/' + roleuuid,'GET',function (res){
            Metronic.unblockUI('#role_add_edit_drawer');
            var data = res.data;
            $('#role_name').val(htmlUnescape(data.role_name));
            _permission = data.permission;
            _permissionUuid = data.permission_uuid;
            _isUsed = data.is_used;
            initUserAuth();
        })
    }

    // 获取用户默认拥有的权限树
    var initUserAuth = function(){
        Metronic.blockUI({target: '#role_add_edit_drawer', animate: true, cenrerY: true});
        pAjaxRequest({},'/api/v1/users/permission','GET',initUserTree)
    };

    //初始化响应事件
    var initListener = function(){
        $('#roleSubmit').off('click').on('click', function (){
            var data = {};
            var method = 'POST';
            if (roleUuid != '') {
                //获取角色标识
                data.roleuuid = roleUuid;
                data.permissionuuid = _permissionUuid;
                method = 'PUT';
            }
            //获取角色名称
            data.rolename = $("#role_name").val();
            if (data.rolename == '') {
                return UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_ROLE_NAME_NEED);
            }
            //获取权限
            data.permission = getPermission();

            pAjaxRequest(data,'/api/v1/roles', method,function (d){
                if(operateResponseList(d)){
                    $('#role_table').bootstrapTable('refresh');
                    $('#role_add_edit_drawer').drawer('hide');
                }
            })
        });
        $('#choose_global_flag').bootstrapSwitch('onSwitchChange', function (e, data) {
            if(data){
                $('#show_global_auth').show();	//开
                var val = $('input:radio[name=global_auth]:checked').val();
                var $radio = $('#show_global_auth input[type=radio][value="'+val+'"]');
                $radio.iCheck('check');           // 设置选中
                $radio.trigger('ifChanged');     // 手动触发 iCheck 的 change 事件
                if (val == 'global_read') {
                    setDefaultTree('global_read');
                }
            }else{
                $('#show_global_auth').hide();	//关
                setDefaultTree('global_write');
            }
        });
        // 初始化单选框组
        $('#show_global_auth input[type=radio]').iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue',
            increaseArea: '20%' // optional
        });

        $('#show_global_auth input[type=radio][name="global_auth"]').on('ifChanged', function(event) {
            var selectedValue = $(this).val();
            if ($(this).is(':checked')) {
                var nodes = zTree.transformToArray(zTree.getNodes());
                // 如果是新建的话，需要默认把所有的全部选中
                if (roleUuid == '') {
                    for (var i = 0; i<nodes.length; i++) {
                        zTree.setChkDisabled(nodes[i], false, false, false);
                        zTree.checkNode(nodes[i], true, true, false); // 不触发回调
                        // 处理有is_operate属性的节点的联动
                        if (nodes[i].is_operate) {
                            handleNodeCheck(nodes[i], true);
                        }
                    }
                }
                setDefaultTree(selectedValue);
            }
        });
    }

    //获取选中全部权限
    var getPermission = function(){
        var allCheckNodes = zTree.getCheckedNodes(true);
        var permission = ['homepage', 'p_homepage'];
        for(var i=0; i<allCheckNodes.length; i++){
            permission.push(allCheckNodes[i].id);
        }
        // 这里判断下是否选中了全局观察者权限
        if ($('#choose_global_flag').get(0).checked) {
            var val = $('input:radio[name=global_auth]:checked').val();
            if (val != '') {
                permission.push('global_observer');
                permission.push(val);
            }
        }
        return permission;
    }

    // 计算下权限的高度
    function initTableHeight() {
        //拿到父窗口的高度
        var height;
        var panelH = window.innerHeight;

        height = panelH - 362;

        $("#permissionTree").css({
            "height": height
        });
    }

    return {
        //main function to initiate the module
        init: function (options) {
            if (options.role_uuid != undefined) {
                roleUuid = options.role_uuid;
            } else {
                roleUuid = '';
            }
            initViews();
            initListener();
            initTableHeight();
        }

    };
}();