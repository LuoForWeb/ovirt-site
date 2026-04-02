//客户端分组管理
var ClientGroup = function () {
    let groupTree; //分组树结构

    //////////////////// 开始-事件监听 ////////////////////

    //初始化事件
    const initListeners = () => {
        initOrganEvents();

        //添加客户端分组
        $('#addGroup').on('click', addClientGroup);

        //移动客户端到分组
        $('#moveClient').on('click', moveClient);


        //添加客户端分组确认
        $('#add_submit').on('click', addGroupSubmit);

        //修改客户端分组确认
        $('#edit_submit').on('click', editGroupSubmit);

        //移动客户端到分组
        $('#move_submit').on('click', moveClientSubmit);
        //按主机名搜索
        $('#clientgroupdiv #searchbtn').on('click', function () {
            $('#clientTable2').bootstrapTable('refresh');
        });
        $('#vin_client_toolbar2 .rightTool .searchinput').on('keyup', function (e) {
            if (e.keyCode === 13) {
                $('#clientTable2').bootstrapTable('refresh');
            }
        });

        // client_group_table_tip 关闭时动态设置表格样式
        $('#client_group_table_tip_close').on('click', () => {
            $('.client-group-table-wrap .table-container').css('height', 'calc(100% - 44px)');
        });
    };

    /**
     * 操作权限校验
     * @returns {{type: number, user_uuid: string, auth: string}|boolean}
     */
    const checkAuth = function(row) {
        return {
            type: 1,
            user_uuid: row.user_uuid,
            auth: 'resmanagement',
        };
    };

    /**
     * 根据客户端操作权限校验
     * @param rows
     * @param sourceType 资源类型【2代理 10客户端】
     * @returns {{type: number, source_uuid, source_type: number}|boolean}
     */
    const checkAuthByAgent = function(rows, sourceType = 10) {
        if (!rows.length) {
            return false;
        }

        return {
            type: 2,
            source_uuid: rows.map(row => row.agent_uuid).join(','),
            source_type: sourceType,
        };
    };

    //////////////////// 结束-事件监听 ////////////////////

    const initOpButtonByPermission = () => {
        // 没有分组基础权限，所有按钮都隐藏
        if (
            !CONF.PERMISSION_ARR.includes('p_client_group_add') &&
            !CONF.PERMISSION_ARR.includes('p_client_group_modify') &&
            !CONF.PERMISSION_ARR.includes('p_client_group_delete')
        ) {
            $('#organMenuWrapper').hide();
        } else {
            $('#organMenuWrapper').show();
            // 添加客户端分组
            if (!CONF.PERMISSION_ARR.includes('p_client_group_add')) {
                $('#addGroup').hide();
            } else {
                $('#addGroup').show();
            }
            // 修改客户端分组
            if (!CONF.PERMISSION_ARR.includes('p_client_group_modify')) {
                $('#editGroup').hide();
            } else {
                $('#editGroup').show();
            }
            // 删除客户端分组
            if (!CONF.PERMISSION_ARR.includes('p_client_group_delete')) {
                $('#deleteGroup').hide();
            } else {
                $('#deleteGroup').show();
            }
        }

        // 没有移动权限
        if (
            !CONF.PERMISSION_ARR.includes('p_client_group_add_to_group') &&
            !CONF.PERMISSION_ARR.includes('p_client_group_delete_from_group')
        ) {
            $('#moveMenuWrapper').hide();
        } else {
            $('#moveMenuWrapper').show();
            // 移动客户端到分组
            if (!CONF.PERMISSION_ARR.includes('p_client_group_add_to_group')) {
                $('#moveClient').hide();
            } else {
                $('#moveClient').show();
            }
            // 从分组删除客户端
            if (!CONF.PERMISSION_ARR.includes('p_client_group_delete_from_group')) {
                $('#removeClient').hide();
            } else {
                $('#removeClient').show();
            }
        }
    };

    //////////////////// 开始-客户端分组树 ////////////////////

    const getClientGroupTreeSetting = () => {
        return {
            check: {
                enable: true,
                nocheckInherit: false
            },
            data: {
                simpleData: {
                    enable: true
                },
                key: {
                    title: "title"
                }
            },
            callback: {
                onClick: nodeClick
            },
            view: {
                selectedMulti: false,
            }
        };
    };

    const buildClientGroupTreeNode = ({group_uuid, group_name, remark, create_time, group_type = 2, user_uuid}) => {
        return {
            id: group_uuid,
            pId: '',
            name: group_name,
            title: remark ? remark : group_name,
            eventtype: 'agent_group',
            icon: './img/platform/flag.png',
            nocheck: true,
            isParent: false,
            create_time,
            group_type,
            group_uuid,
            group_name,
            user_uuid,
        };
    }

    //获取客户端分组结构消息
    const initClientGroupTree = () => {
        Metronic.blockUI({target: '.client-group-wrap__content', animate: true});
        pAjaxRequest({}, `/api/v1/agents/groups`, 'GET', res => {
            Metronic.unblockUI('.client-group-wrap__content');
            if (!res.success) {
                UIToastr.showWarning(LANG.UI_CLIENT_GROUP, res.message);
                return;
            }

            let nodes = res.data.rows.map(row => buildClientGroupTreeNode(row));
            if (!nodes.length) {
                UIToastr.showWarning(LANG.UI_CLIENT_GROUP, res.message);
                return;
            }
            groupTree = $.fn.zTree.init($("#group_tree"), getClientGroupTreeSetting(), nodes);
            nodes = groupTree.getNodes();
            groupTree.selectNode(nodes[0]);
            nodeClick("", "group_tree", nodes[0], 1, false);
            initClientTable2();
        });
    };

    //点击节点的时候触发
    const nodeClick = (event, treeId, treeNode, clickFlag, refresh = true) => {
        //默认分组不能修改和删除
        if (parseInt(treeNode.group_type) === 1) {
            addForbidButton('editGroup');
            addForbidButton('deleteGroup');
            addForbidButton('removeClient');
        } else {
            deleteForbidButton('editGroup');
            deleteForbidButton('deleteGroup');
            deleteForbidButton('removeClient');
        }
        if (refresh) {
            $('#clientTable2').bootstrapTable('refresh');
        }
    };

    //添加禁止点击的按钮样式
    const addForbidButton = id => {
        $('#' + id).unbind();
        $('#' + id + ' button').css({
            "opacity": ".4",
            "cursor": "default"
        });
    };

    //移除禁止点击的按钮样式
    const deleteForbidButton = id => {
        initOrganEvents();
        $('#' + id + ' button').css({
            "opacity": "1",
            "cursor": "pointer"
        });
    };

    //////////////////// 结束-客户端分组树 ////////////////////

    //////////////////// 开始-事件处理 ////////////////////

    //添加客户端分组
    const addClientGroup = () => {
        //清空输入框值
        $('#groupName').val('');
        $('#remark').val('');
        $('.addDiv').show();
        $('.editDiv').hide();
        $('#groupModal').modal({'width': "600px", 'height': "200px"});
    };

    //修改客户端分组名
    const editClientGroup = () => {
        if (!groupTree || !groupTree.getSelectedNodes().length) {
            UIToastr.showInfo(LANG.UI_CLIENT_GROUP_EDIT, LANG.UI_CLIENT_GROUP_EDIT_SELECT_TIPS);
            return;
        }
        //获取选中的树节点
        let nodes = groupTree.getSelectedNodes();
        checkOperateAuth(checkAuth(nodes[0]), () => {
            $('#groupName').val(nodes[0].name);
            $('#remark').val(nodes[0].title);
            $('#groupuuid').val(nodes[0].id);
            $('.addDiv').hide();
            $('.editDiv').show();
            $('#groupModal').modal({'width': "600px", 'height': "200px"});
        });
    };

    //删除客户端分组
    const deleteClientGroup = () => {
        if (!groupTree || !groupTree.getSelectedNodes().length) {
            UIToastr.showInfo(LANG.UI_CLIENT_GROUP_DELETE, LANG.UI_CLIENT_GROUP_DELETE_SELECT_TIPS);
            return;
        }
        //获取选中的树节点
        let nodes = groupTree.getSelectedNodes();
        if (!nodes.length) {
            UIToastr.showInfo(LANG.UI_CLIENT_GROUP_DELETE, LANG.UI_CLIENT_GROUP_DELETE_SELECT_TIPS);
            return;
        }

        checkOperateAuth(checkAuth(nodes[0]), () => {
            showDeleteClientGroup(nodes[0]);
        });
    };

    /**
     * 显示删除客户端分组弹窗
     * @param row
     */
    const showDeleteClientGroup = (row) => {
        bootbox.confirm({
            title: LANG.UI_CLIENT_GROUP_DELETE,
            message: LANG.UI_CLIENT_GROUP_DELETE_CONFIRM_TIPS,
            callback: debounce(function (r) {
                if (!r) return;
                Metronic.blockUI({target: '#clientgroupdiv', animate: true});
                pAjaxRequest({}, `/api/v1/agents/groups/${row.group_uuid}`, 'DELETE', res => {
                    Metronic.unblockUI('#clientgroupdiv');
                    if (!res.success) {
                        UIToastr.showWarning(LANG.UI_CLIENT_GROUP_DELETE, res.message);
                        return;
                    }
                    UIToastr.showSuccess(LANG.UI_CLIENT_GROUP_DELETE, res.message);
                    groupTree.removeNode(row);
                    nodes = groupTree.getNodes();
                    groupTree.selectNode(nodes[0]);
                    nodeClick("", "group_tree", nodes[0], 1, true);
                });
            }, 300)
        });
    };

    /**
     * 检查移动客户端权限
     * @param clientRows
     * @param agentRows
     * @param callback
     */
    const checkMoveClientBoxAuth = (clientRows, agentRows, callback) => {
        if (clientRows.length && agentRows.length) {
            checkOperateAuth(checkAuthByAgent(clientRows), () => {
                checkOperateAuth(checkAuthByAgent(agentRows, 2), () => {
                    callback();
                });
            });
        } else if (clientRows.length) {
            checkOperateAuth(checkAuthByAgent(clientRows), callback);
        } else {
            checkOperateAuth(checkAuthByAgent(agentRows, 2), callback);
        }
    };

    /**
     * 移动客户端操作权限判断
     * @returns {Promise<unknown>}
     */
    const judgeMoveClientOperateAuth = () => {
        return new Promise(resolve => {
            let rows = $('#clientTable2').bootstrapTable('getSelections');
            const clientRows = rows.filter(row => parseInt(row.agent_type) !== 4);
            const agentRows = rows.filter(row => parseInt(row.agent_type) === 4);
            checkMoveClientBoxAuth(clientRows, agentRows, () => {
                resolve();
            });
        });
    };

    //移动客户端到分组
    const moveClient = () => {
        let rows = $('#clientTable2').bootstrapTable('getSelections');
        if (!rows.length) {
            UIToastr.showInfo(LANG.UI_CLIENT_GROUP_MOVE_CLIENT_TO, LANG.UI_CLIENT_GROUP_MOVE_CLIENT_TO_TIPS);
            return;
        }
        judgeMoveClientOperateAuth().then(doMoveClient);
    };

    /**
     * 执行移动客户端
     */
    const doMoveClient = () => {
        //获取所有分组
        let nodes = groupTree.getNodes();
        //获取选中的分组
        let selectNodes = groupTree.getSelectedNodes();
        let options = '';
        for (const node of nodes) {
            if (node.group_uuid === selectNodes[0].group_uuid) {
                continue;
            }
            options += `<option value="${node.group_uuid}">${node.group_name}</option>`;
        }
        $('#groupList').html(options);
        if(CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw"){
			$('#moveClientModal').modal({'width': "600px", 'height': "200px"});
		}else{
			$('#moveClientModal').modal({'width': "650px", 'height': "200px"});
		}
    };

    //从分组移除客户端
    const removeClient = () => {
        //不能从默认分组移除
        let nodes = groupTree.getSelectedNodes();
        let rows = $('#clientTable2').bootstrapTable('getSelections');
        if (!rows.length) {
            UIToastr.showInfo(LANG.UI_CLIENT_GROUP_REMOVE_CLIENT, LANG.UI_CLIENT_GROUP_REMOVE_CLIENT_SELECT_TIPS);
            return;
        }
        if (parseInt(nodes[0].group_type) === 1) {
            UIToastr.showInfo(LANG.UI_CLIENT_GROUP_REMOVE_CLIENT, LANG.UI_CLIENT_GROUP_REMOVE_CLIENT_DEFAULT_TIPS);
            return;
        }
        judgeMoveClientOperateAuth().then(() => {
            doRemoveClient(nodes, rows);
        });
    };

    /**
     * 执行从分组移除客户端
     * @param nodes
     * @param rows
     */
    const doRemoveClient = (nodes, rows) => {
        if (parseInt(nodes[0].group_type) === 1) {
            UIToastr.showInfo(LANG.UI_CLIENT_GROUP_REMOVE_CLIENT, LANG.UI_CLIENT_GROUP_REMOVE_CLIENT_DEFAULT_TIPS);
            return;
        }

        bootbox.confirm({
            title: LANG.UI_CLIENT_GROUP_REMOVE_CLIENT,
            message: LANG.UI_CLIENT_GROUP_REMOVE_CLIENT_CONFIRM_TIPS,
            callback: debounce(function (r) {
                if (!r) return;
                let reqData = {
                    adjust_mode: 2,
                    agent_uuids: rows.map(row => row.agent_uuid),
                    agent_group_uuid: nodes[0].group_uuid,
                };
                Metronic.blockUI({target: '#clientgroupdiv', animate: true});
                pAjaxRequest(reqData, `/api/v1/agents/groups/adjust`, 'PATCH', res => {
                    Metronic.unblockUI('#clientgroupdiv');
                    if (!res.success) {
                        UIToastr.showWarning(LANG.UI_CLIENT_GROUP_REMOVE_CLIENT, res.message);
                        return;
                    }
                    UIToastr.showSuccess(LANG.UI_CLIENT_GROUP_REMOVE_CLIENT, res.message);
                    $('#clientTable2').bootstrapTable('refresh');
                });
            }, 300)
        });
    };

    //添加分组确认
    const addGroupSubmit = () => {
        let groupNameTag = $('#groupName');
        let reqData = {
            group_name: $.trim(groupNameTag.val()),
            remark: $('#remark').val(),
        };
        if (!reXssEncode(reqData.group_name)) {
            groupNameTag.val('');
            return false;
        }
        if (!reqData.group_name) {
            UIToastr.showInfo(LANG.UI_AGENT_GROUP_ADD, LANG.UI_CLIENT_GROUP_NAME_IS_EMPTY);
            return false;
        }

        Metronic.blockUI({target: '#groupModal', animate: true});
        pAjaxRequest(reqData, `/api/v1/agents/groups`, 'POST', res => {
            Metronic.unblockUI('#groupModal');
            if (!res.success) {
                UIToastr.showWarning(LANG.UI_AGENT_GROUP_ADD, res.message);
                return;
            }
            UIToastr.showSuccess(LANG.UI_AGENT_GROUP_ADD, res.message);
            $('#groupModal').modal('hide');
            let newNode = buildClientGroupTreeNode(res.data);
            let parentNode = groupTree.getNodeByParam("id", newNode.pId, null);
            groupTree.addNodes(parentNode, newNode);
        });
    };

    //修改分组确认
    const editGroupSubmit = () => {
        let reqData = {
            group_name: $.trim($('#groupName').val()),
            remark: $('#remark').val(),
        };
        if (!reqData.group_name) {
            UIToastr.showInfo(LANG.UI_CLIENT_GROUP_EDIT, LANG.UI_CLIENT_GROUP_NAME_IS_EMPTY);
            return false;
        }

        Metronic.blockUI({target: '#groupModal', animate: true});
        pAjaxRequest(reqData, `/api/v1/agents/groups/${$('#groupuuid').val()}`, 'PATCH', res => {
            Metronic.unblockUI('#groupModal');
            if (!res.success) {
                UIToastr.showWarning(LANG.UI_CLIENT_GROUP_EDIT, res.message);
                return;
            }
            UIToastr.showSuccess(LANG.UI_CLIENT_GROUP_EDIT, res.message);
            $('#groupModal').modal('hide');
            let nodes = groupTree.getSelectedNodes();
            //更新备注和提示信息
            nodes[0].remark = reqData.remark;
            nodes[0].title = reqData.remark ? reqData.remark : reqData.group_name;
            nodes[0].name = reqData.group_name;
            groupTree.updateNode(nodes[0]);
            $(`#${nodes[0].tId}`).find(`[data-toggle="tooltip"]`)
                .attr('data-original-title', nodes[0].title)
                .tooltip();
        });
    };

    //移动客户端到分组
    const moveClientSubmit = () => {
        let rows = $('#clientTable2').bootstrapTable('getSelections');
        let reqData = {
            adjust_mode: 1,
            agent_uuids: rows.map(row => row.agent_uuid),
            agent_group_uuid: $('#groupList').val(),
        };
        if (!reqData.agent_group_uuid) {
            UIToastr.showInfo(LANG.UI_CLIENT_GROUP_MOVE_CLIENT_TO, LANG.UI_CLIENT_GROUP_SELECT_TIPS);
            return false;
        }

        // 不允许移动不同类型的代理，移动多个类型时提示
        let agentTypes = {};
        rows.map(row => {
            agentTypes[row.agent_type] = 1;
        });
        if (Object.keys(agentTypes).length > 1) {
            UIToastr.showWarning(LANG.UI_CLIENT_GROUP_MOVE_CLIENT_TO, LANG.UI_CLIENT_GROUP_MOVE_CLIENT_MULTI_TYPE_TIPS);
            $('#moveClientModal').modal('hide');
            return false;
        }
        Metronic.blockUI({target: '#moveClientModal', animate: true});
        pAjaxRequest(reqData, `/api/v1/agents/groups/adjust`, 'PATCH', res => {
            Metronic.unblockUI('#moveClientModal');
            if (!res.success) {
                UIToastr.showWarning(LANG.UI_CLIENT_GROUP_MOVE_CLIENT_TO, res.message);
                return;
            }
            UIToastr.showSuccess(LANG.UI_CLIENT_GROUP_MOVE_CLIENT_TO, res.message);
            $('#moveClientModal').modal('hide');
            $('#clientTable2').bootstrapTable('refresh');
        });
    };

    //加载需要禁用启用的回调函数
    const initOrganEvents = () => {
        //修改客户端分组
        $('#editGroup').unbind().on('click', editClientGroup);
        //删除客户端分组
        $('#deleteGroup').unbind().on('click', deleteClientGroup);
        //从分组删除客户端
        $('#removeClient').unbind().on('click', removeClient);
    };

    //////////////////// 结束-事件处理 ////////////////////

    //////////////////// 开始-客户端表格 ////////////////////

    const getQueryParams = () => {
        let treeNodes = groupTree.getSelectedNodes();
        let params = {
            agent_group_uuid: treeNodes[0].group_uuid,
        };
        let name = $.trim($('#clientgroupdiv .searchinput').val());
        if (name) {
            params.host_ip = name;
        }
        return params;
    };

    // const initClientTable2Height = () => {
    // 	let toolbarHeight = 57;
    // 	let paginationHeight = 52;
    // 	let alertHeight = 140;
    // 	let tabTitleHeight = 48;
    // 	let navTitleHeight = 46;
    // 	// page-content有40px的内边距
    // 	// portlet-body有10px的内边距
    // 	// tab-content有20px的外边距离
    // 	let otherHeight = 40 + 10 + 20;
    // 	let height = window.innerHeight - toolbarHeight - paginationHeight - alertHeight - tabTitleHeight - navTitleHeight - otherHeight;

    // 	$("#clientTable2Wrapper .fixed-table-body").css({
    // 		'height': height,
    // 	});
    // };

    /**
     * 主机名别名
     * @param hostname
     * @param row
     * @return {`<span title="${string}">${string}</span>`}
     */
    const hostnameFormatter = (hostname, row) => {
        let showName = `${hostname}/${row.alias}`;
        return `<span title="${showName}">${showName}</span>`;
    };

    const moduleFormatter = (module) => {
        try {
            module = JSON.parse(module);
        } catch (e) {
            return '--';
        }
        let html = '';
        //文件
        if (module.file) {
            html += '&nbsp;&nbsp;<a title="' + LANG.UI_AGENT_MODULE_FILE + '"><img class="modulefilecls" alt=""></a>';
        }
        //数据库
        if (module.database) {
            html += '&nbsp;&nbsp;<a title="' + LANG.UI_AGENT_MODULE_DB + '"><img class="moduledbcls"  alt=""></a>';
        }
        //操作系统
        if (module.os) {
            html += '&nbsp;&nbsp;<a title="' + LANG.UI_PLATFORM_DES_OS + '"><img class="moduleoscls"  alt=""></a>';
        }
        return `<span style="white-space: nowrap">${html ? html : '--'}</span>`;
    };

    /**
     *
     * @param value
     * @param {Object} row
     * @param {Boolean} row.online_status
     * @param {String} row.online_status_des
     * @param {String} row.deploy_status_des
     * @return string
     */
    const statusFormatter = (value, row) => {
        let labelClass = !!row.online_status ? 'label-success' : 'label-default';
        return `<span class="label label-sm ${labelClass}" title="${row.online_status_des}(${row.deploy_status_des})">
			${row.online_status_des}(${row.deploy_status_des})
		</span>`;
    };

    const getClientTable2Columns = () => {
        return [{
            checkbox: true,
            width: '2',
            widthUnit: '%',
            sortable: false,
        }, {
            title: LANG.UI_CLIENT_ALIAS,
            field: 'alias',
            width: '12',
            widthUnit: '%',
            // formatter: hostnameFormatter,
        }, {
            title: LANG.UI_CLIENT_HOST_NAME,
            field: 'hostname',
            width: '12',
            widthUnit: '%',
            // formatter: hostnameFormatter,
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
            title: LANG.UI_CLIENT_TYPE,
            field: 'agent_type_des',
            width: '5',
            widthUnit: '%',
        }, {
            title: LANG.UI_PUBLIC_ADD_TIME,
            field: 'register_time',
            width: '10',
            widthUnit: '%',
        }, {
            title: LANG.UI_CLIENT_APP_CONFIG,
            field: 'app_des',
            width: '10',
            widthUnit: '%',
        }, {
            title: LANG.UI_PUBLIC_STATUS,
            field: 'online_status',
            formatter: statusFormatter,
            width: '8',
            widthUnit: '%',
        }];
    };

    const getClientTable2Option = () => {
        return {
            vin_url: '/api/v1/agents',
            vin_params: getQueryParams,
            vin_method: 'get',
            toolbarId: '#vin_client_toolbar2',
            vin_toolbar: '.vin_toolbar',
            buttonsToolbar: '.vin_btnToolbar2',
            // 排序
            sortName: 'register_time',
            sortOrder: 'desc',
            // 隐藏行
            hideColumns: '',
            showButtonText: false,
            clickToSelect: true,
            pagination: true,
            pageList: [10, 20, 50, 100, 150, 200],
            pageSize: 20,
            resizable: true,
            showRefresh: false,
            showExport: false,
            showColumns: false,
            // onResetView: initClientTable2Height,
            onRefresh: () => {
                $("#clientDatatable").bootstrapTable('hideLoading');
            },
            columns: getClientTable2Columns(),
        };
    };

    const initClientTable2 = () => {
        // 移除分页缓存
        window.sessionStorage.removeItem('clientTable2_pageRecord');
        $('#clientTable2').bootstrapTable('destroy').baseTableConfig().init(getClientTable2Option());
    };

    //////////////////// 结束-客户端表格 ////////////////////

    return {
        //main function to initiate the module
        init: function () {
            initListeners();	//初始化事件操作
            initOpButtonByPermission();	//根据权限初始化按钮权限
            initClientGroupTree();	//初始化客户端组树
        }
    };

}();

jQuery(document).ready(function () {
    ClientGroup.init();
});
