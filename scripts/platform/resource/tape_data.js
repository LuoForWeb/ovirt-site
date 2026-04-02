var TapeData = function () {
    var table = $('#tape_data_table');
    var zTreeObj;
    var offset = 0;
    var limit = 50; // 每页数量（左边树的对象）
    var moduleType = 0;
    var allCheckedNode = []; // 左侧树所有选中的对象uuid集合
    function addListener () {
        $('#vin_monitor_toolbar').on('click', '.search-btn', function () {
            table.bootstrapTable('refresh');
        });
        // 监听模块改变事件
        $('#module_type').on('change', moduleChange);
        // 监听搜索回车事件
        $('#object_name').keypress(function (e) {
            if (e.which == 13) {
                initFileList();
            }
        });

        $(document).ready(function() {
            $('#tape_monitor').off('click.searchBtn').on('click.searchBtn', '.search-btn', function(e) {
                $('#tape_data_table').bootstrapTable('refresh');

            });
        });


        // 绑定搜索图标点击事件（使用事件委托，支持动态元素）
        $('#tage_data_div .search-trigger').on('click',  function() {
            initFileList();
        });

    }

    // 具体的导出实现
    function exportData(type = 'xlsx', all = 1) {
        // 1 先判断页面是否有选择的
        // 有就提示是否导出选中的
        // 没有就提示会导出所有的搜索结果
        var selectedRow = table.bootstrapTable('getSelections');
        let timepoint_uuids = [];
        if (selectedRow.length > 0) {
            for (var k in selectedRow) {
                timepoint_uuids.push(selectedRow[k].id);
            }
        }

        if (all == 2 && timepoint_uuids.length <= 0) {
            // 提示先选择
            return UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_TAPE_DATA_SELECT_OPTIONS);
        }

        // 2 获取表格的搜索条件
        var url = "/api/v1/tapes/timepoints/list?export=true&x-api-version=1.0-rev0&offset=0&limit=10&export_type="+type;
        let params = getParams();
        if (params.keyword != undefined && timepoint_uuids.length <= 0) {
            url += '&keyword=' + params.keyword;
        }

        if (params.uuid !=undefined) {
            url += '&uuid=' + params.uuid;
        }

        if (timepoint_uuids.length > 0) {
            url += '&timepoint_uuids=' + timepoint_uuids.join(',');
        }
        UIToastr.showInfo(LANG.UI_PUBLIC_TIPS, LANG.UI_TAPE_DATA_STARTED_DOWNLOAD_CHECK_HISTORY);
        window.location.href = url;
    }

    // 选择导出模式
    function exportDataPre(type = 'xlsx') {
        let msg = `
            <div class="custom-prompt-message" id="export-type" style="padding: 5px;">
                    <div style="margin-bottom: 12px;">
                     <label style="margin-right: 20px;">
                        <input type="radio" style="margin-right:4px;" checked class="icheck-backup-point-abnormal" name="icheckbox2" value="1">`+LANG.UI_TAPE_DATA_EXPORT_ALL+`
                    </label>
                    <label>
                        <input type="radio" style="margin-right:4px;" class="icheck-backup-point-abnormal" name="icheckbox2" value="2">`+LANG.UI_TAPE_DATA_EXPORT_SELECTED+`
                    </label>
                    </div>
                    <div class="alert alert-block alert-info fade in" id="marktips" style="margin-bottom: 0;">
                        <button type="button" class="close" data-dismiss="alert"></button>
                        <ul class="alert-ul">
                            <strong class="alert-ul-head">`+LANG.UI_PUBLIC_TIPS+`</strong>
                            <li>`+LANG.UI_TAPE_DATA_EXPORT_TIME_LONGER+`</li>
                        </ul>
                    </div>
                </div>
            `;
        bootbox.dialog({
            title: "<i class='viconfont vicon-a-Share-threefenxiang3' style='font-size:18px;margin-right:8px;'></i>"+LANG.UI_TAPE_DATA_SELECT_EXPORT_FORMAT,
            message: msg,
            buttons: {
                cancel: {
                    label: LANG.UI_PUBLIC_CANCEL,
                    className: 'btn-secondary'
                },
                confirm: {
                    label: LANG.UI_PUBLIC_CONFIRM,
                    className: 'btn-primary',
                    callback: function () {
                        let val = $('input:radio[name=icheckbox2]:checked').val();
                        exportData(type, val);
                        // 可选：阻止关闭并提示
                        //return false; // 返回 false 阻止对话框关闭
                    }
                }
            },
            onEscape: true,
            backdrop: true
        });
    }

    // 获取表格的搜索条件
    function getParams() {
        var params = {};
        if ($('.tapeMonitorSearch').val()) {
            params.keyword = $('.tapeMonitorSearch').val();
        }
        if (allCheckedNode.length > 0) {
            params.uuid = allCheckedNode;
        }
        return params;
    }

    /**
     * @function 初始化表格
     */
    function initTable() {
        let exportSettings = {};
        let showExport = false;
        if ($.inArray('p_tape_manage_data_export', CONF.PERMISSION_ARR) !== -1) {
            // 有导出权限
            exportSettings = {
                showBuiltIn: [],
                custom: [
                    {
                        label: 'EXCEL',
                        class: 'export-excel'
                    },
                    {
                        label: 'CSV',
                        class: 'export-csv'
                    }
                ]
            };
            showExport = true;
        }
        let options = {
            toolbarId: '#vin_data_toolbar',
            vin_toolbar: '.vin_data_toolbar',
            buttonsToolbar: '.vin_btnToolbar2',
            vin_url: '/api/v1/tapes/timepoints/list',
            vin_method: 'GET',
            vin_params: function () {
                return getParams();
            },
            fullPage: true,
            placeholder: LANG.UI_SEARCH_BY_TASK_NAME,
            sortName: 'timepoint',
            sortOrder: 'desc',
            searchInput: true, //搜索框
            searchClass: 'tapeMonitorSearch',
            searchSelector: '.tapeMonitorSearch',
            showSearchButton: true,
            paginationLoop: false,
            resizable: true,
            showExport: showExport, //是否开启列选择按钮
            exportSettings: exportSettings,
            toolbar: '#custom-toolbar', // 指向自定义工具栏容器
            showColumns: true, //是否开启列选择按钮
            onRefresh: function (params) {
                Metronic.blockUI({target: '#tage_data_div',animate: true});
            },

            PostBody: function (){
                Metronic.unblockUI('#tage_data_div');
                $('#vin_data_toolbar .export-excel').off('click').on('click', function (){
                    exportDataPre('xlsx');
                })
                $('#vin_data_toolbar .export-csv').off('click').on('click', function (){
                    exportDataPre('csv');
                })
            },
            columns: [
                {
                    checkbox: true,
                    sortable: false,
                },
                {
                    field: 'timepoint', //字段名
                    title: LANG.UI_TAPE_DATA_TIME_POINT,
                },
                {
                    field: 'backup_mode',
                    title: LANG.UI_TAPE_DATA_BACKUP_MODE,
                    align: 'center',
                    formatter: function (value, row, index) {
                        switch(value) {
                            case 1:
                                return LANG.UI_DATA_TYPE_FULL; // 完全备份
                            case 2:
                                return LANG.UI_DATA_TYPE_INCR; // 增量备份
                            case 3:
                                return LANG.UI_DATA_TYPE_DIFF; // 差异备份
                            case 4:
                                return LANG.UI_DRILLS_TASK_BACKUP_TIMEPOINT; // 时间点
                            case 9:
                                return LANG.UI_VM_MACHINE_LOG; // 日志
                        }
                    }
                },
                {
                    field: 'job_name',
                    title: LANG.UI_TAPE_DATA_JOB_NAME,
                },
                {
                    field: 'job_type',
                    title: LANG.UI_TAPE_DATA_JOB_TYPE,
                },
                {
                    field: 'leave_days',
                    title: LANG.UI_TAPE_DATA_LEAVE_DAYS,
                    sortable: false,
                },
                {
                    field: 'expired_time',
                    title: LANG.UI_TAPE_DATA_EXPIRED_TIME,
                    sortable: false,
                },
                {
                    field: 'tape_device',
                    title: LANG.UI_TAPE_DATA_TAPE_DEVICE,
                    sortable: false,
                },
                {
                    field: 'backup_set_name',
                    title:LANG.UI_TAPE_DATA_BACKUP_SET_NAME,
                    sortable: false,
                },
                {
                    field: 'tape_group',
                    title: LANG.UI_TAPE_DATA_TAPE_GROUP,
                    sortable: false,
                },
            ]
        }

        table.baseTableConfig().init(options);
    }

    // 初始化树
    var initFileList = function(){
        initTreeList(setFileTree, 0, 0, moduleType);
    }
    function setFileTree(data) {
        // 销毁现有树
        if (zTreeObj) {
            zTreeObj.destroy();
            // 清空容器
            $("#recoverFileTree").empty();
        }

        if (Array.isArray(data)) {
            data.forEach(node => {
                // 彻底清理：只有真有子节点才是父节点
                if (node.children && node.children.length > 0) {
                    node.isParent = true;
                    node.open = false;
                } else {
                    node.isParent = false;
                    delete node.children; // 防止 zTree 自动推断
                }
            });
        }

        var setting = {
            check: {
                enable: true, // 开启复选框
                chkboxType: {"Y": "", "N": ""}, // 父子节点不关联
                // chkStyle: "checkbox", // 设置为checkbox样式
                // radioType: "all" // 这里虽然设置了radioType，但因为开启了chkbox，所以不影响复选框功能
            },
            data: {
                simpleData: {
                    enable: true,
                    pIdKey: "pId",//节点数据中保存其父节点唯一标识的属性名称
                },
                key: {
                    title: "title",
                }
            },
            callback: {
                beforeCheck: beforeCheck,
                beforeClick: NodeClick,
                beforeExpand: NodeExpand,
                onCheck: onCheck
            },
            view: {
                addHoverDom: addHoverDom,
                removeHoverDom: removeHoverDom,
                addDiyDom: addDiyDom, // 添加自定义DOM
                expandLevel: 0
            }
        };
        zTreeObj = $.fn.zTree.init($("#recoverFileTree"), setting, data);

        requestAnimationFrame(() => {
            if (zTreeObj) {
                zTreeObj.expandAll(false);
            }
            $("#recoverFileTree").show();
        });


        // 为所有有更多数据的节点添加加载更多按钮
        var allNodes = zTreeObj.transformToArray(zTreeObj.getNodes());
        allNodes.forEach(function(node) {
            if (node.is_more) {
                addLoadMoreNode(node, offset);
            }
        });
    }

    // 自定义DOM函数
    function addDiyDom(treeId, treeNode) {
        var aObj = $("#" + treeNode.tId + "_a");
        var liObj = $("#" + treeNode.tId);

        // 判断是否为第一层节点 (level === 0)
        if (treeNode.level === 0) {
            aObj.addClass("level0");
            // 给li元素添加class
            liObj.addClass("first-level-node");

            // 给a元素添加class
            aObj.addClass("first-level-link");

            // 给展开收起图标添加class
            var switchObj = $("#" + treeNode.tId + "_switch");
            switchObj.addClass("first-level-switch");

            // 给节点图标添加class（可选）
            var icoObj = $("#" + treeNode.tId + "_ico");
            icoObj.addClass("first-level-icon");
        }
    }

    // 添加加载更多节点
    function addLoadMoreNode(node, offset) {
        var treeObj = $.fn.zTree.getZTreeObj("recoverFileTree");

        // 先移除可能已存在的加载更多节点
        removeLoadMoreNode(node);

        var parentNode = treeObj.getNodeByParam("id", node.pId);
        // 创建加载更多节点
        var id = "load-more-" + node.id;
        var loadMoreNode = {
            id: id,
            loadMoreNodeId: id,
            pId: parentNode.id,
            uuid: parentNode.uuid,
            module_type: parentNode.module_type,
            name: LANG.UI_CLIENT_PATH_TREE_MORE,
            isLoadMore: true,
            isParent: false,
            nocheck: true,
            iconSkin: "",
            open: false,
            offset: offset + limit
        };
        // 添加到父节点的最后一个
        treeObj.addNodes(parentNode, -1, loadMoreNode);
    }

    // 移除加载更多节点
    function removeLoadMoreNode(node) {
        var treeObj = $.fn.zTree.getZTreeObj("recoverFileTree");
        if (node.loadMoreNodeId) {
            treeObj.removeNode(node);
            node.loadMoreNodeId = null;
        }
    }

    // 复选框状态变化回调
    function onCheck(event, treeId, treeNode) {
        var treeObj = $.fn.zTree.getZTreeObj(treeId);

        if (treeNode.checked) {

            if (treeNode.isParent) {
                // 选中父节点 → 取消所有子节点
                cancelAllChildren(treeObj, treeNode);
            } else {
                // 选中子节点 → 取消所有祖先节点
                let parent = treeNode.getParentNode();
                while (parent) {
                    treeObj.checkNode(parent, false, false, false); // 不触发事件
                    parent = parent.getParentNode();
                }
            }
        }

        // 同步更新全局选中列表
        var allfileNodes = treeObj ? treeObj.getCheckedNodes() : [];
        allCheckedNode = allfileNodes.map(node => node.uuid);
        table.bootstrapTable('refresh');
    }
    // 取消所有子节点的选中状态
    function cancelAllChildren(treeObj, parentNode) {
        if (!parentNode || !parentNode.children) return;

        for (var i = 0; i < parentNode.children.length; i++) {
            var childNode = parentNode.children[i];
            treeObj.checkNode(childNode, false, true, false); // 取消选中子节点

            // 递归取消孙子节点
            if (childNode.children && childNode.children.length > 0) {
                cancelAllChildren(treeObj, childNode);
            }
        }
    }
    // 取消所有父节点的选中状态
    function cancelAllParents(treeObj, childNode) {
        var parentNode = childNode.getParentNode();
        while (parentNode) {
            treeObj.checkNode(parentNode, false, true, false); // 取消选中父节点
            parentNode = parentNode.getParentNode();
        }
    }
    // 在选择前的验证
    function beforeCheck(treeId, treeNode) {
        var treeObj = $.fn.zTree.getZTreeObj(treeId);

        // 只有在选中父节点时才做限制（防止父与子同时存在）
        if (!treeNode.checked && treeNode.isParent) {
            if (checkChildrenChecked(treeNode)) {
                UIToastr.showWarning(LANG.UI_PUBLIC_TIPS,LANG.UI_TAPE_DATA_CHILD_NODES_TIPS);
                return false;
            }
        }

        // 子节点一律允许选中（不再检查父节点是否选中）
        return true;
    }
    // 检查子节点是否有被选中的
    function checkChildrenChecked(parentNode) {
        if (!parentNode.children) return false;

        for (var i = 0; i < parentNode.children.length; i++) {
            var childNode = parentNode.children[i];
            if (childNode.checked) {
                return true;
            }
            // 递归检查孙子节点
            if (childNode.children && checkChildrenChecked(childNode)) {
                return true;
            }
        }
        return false;
    }
    // 检查父节点是否有被选中的
    function checkParentChecked(treeObj, childNode) {
        var parentNode = childNode.getParentNode();
        while (parentNode) {
            if (parentNode.checked) {
                return true;
            }
            parentNode = parentNode.getParentNode();
        }
        return false;
    }

    const addHoverDom = function(treeId, treeNode) {}
    const removeHoverDom = function(treeId, treeNode) {}

    // 点击节点事件处理
    // 点击节点事件处理
    // 点击节点事件处理
    var NodeClick = function(treeId, treeNode) {
        if (treeNode.isLoadMore) {
            loadMoreData(treeNode);
            return false;
        }

        //处理一级节点的高亮切换
        if (treeNode.level === 0) {
            var $li = $("#" + treeNode.tId); // 获取 <li> 元素（包含复选框）
            var treeObj = $.fn.zTree.getZTreeObj(treeId);

            if (treeNode.open) {
                // 即将收起：立即移除高亮
                $li.removeClass("node-expanded-active");
            } else {
                // 即将展开：先执行手风琴（收起其他已展开的一级节点）
                var allRootNodes = treeObj.getNodes();
                for (var i = 0; i < allRootNodes.length; i++) {
                    var node = allRootNodes[i];
                    if (node.id !== treeNode.id && node.open) {
                        treeObj.expandNode(node, false, false, false, false);
                        $("#" + node.tId).removeClass("node-expanded-active");
                    }
                }
                // 延迟一帧添加高亮
                setTimeout(function() {
                    $li.addClass("node-expanded-active");
                }, 0);
            }
        }

        return true; // 允许默认展开/收起行为
    };

    // 加载更多数据
    function loadMoreData(node) {
        var treeObj = $.fn.zTree.getZTreeObj("recoverFileTree");

        // 显示加载中状态
        if (node.loadMoreNodeId) {
            node.name = LANG.UI_TAPE_DATA_LOADING;
            treeObj.updateNode(node);
        }

        // 模拟异步加载数据
        setTimeout(function() {
            // 获取本次的 offset
            var offsets = node.offset;
            var uuid = node.uuid;
            var module_type = node.module_type;
            var parentNode = treeObj.getNodeByParam("id", node.pId);

            initTreeList(function(newData){
                // 移除加载更多节点
                removeLoadMoreNode(node);
                // 添加新数据
                if (newData && newData.length > 0) {
                    // 在父节点的倒数第二个位置开始添加子节点数据
                    treeObj.addNodes(parentNode, -1, newData);

                    var lastNode = newData[newData.length-1];
                    if (lastNode.is_more) {
                        // 如果还有更多数据
                        // 创建加载更多节点 更新 offset 的值
                        var id = "load-more-" + lastNode.id;
                        var offset = offsets + limit;
                        var loadMoreNode = {
                            id: id,
                            loadMoreNodeId: id,
                            pId: parentNode.id,
                            uuid: parentNode.uuid,
                            module_type: parentNode.module_type,
                            name: LANG.UI_CLIENT_PATH_TREE_MORE,
                            isLoadMore: true,
                            isParent: false,
                            nocheck: true,
                            iconSkin: "",
                            open: false,
                            offset: offset
                        };
                        // 添加到父节点的最后一个
                        treeObj.addNodes(parentNode, -1, loadMoreNode);
                    }
                }

                treeObj.updateNode(parentNode);
            },offsets, uuid, module_type);

        }, 1000);
    }

    var NodeExpand = function (treeId, pNode) {
        var treeObj = $.fn.zTree.getZTreeObj(treeId);

        // 仅对一级节点（根节点）生效
        if (pNode.level === 0) {
            var $li = $("#" + pNode.tId); // 获取 <li> 元素

            // 如果当前节点已经是展开状态，说明这次点击是要收起它
            if (pNode.open) {
                $li.removeClass("node-expanded-active");
                return true; // 允许收起
            }

            // 否则：展开它，并激活手风琴效果
            var allRootNodes = treeObj.getNodes();
            for (var i = 0; i < allRootNodes.length; i++) {
                var node = allRootNodes[i];
                if (node.id !== pNode.id && node.open) {
                    // 收起其他已展开的根节点
                    treeObj.expandNode(node, false, false, false, false);
                    // 移除它们的高亮
                    $("#" + node.tId).removeClass("node-expanded-active");
                }
            }

            // 给当前节点添加高亮
            $li.addClass("node-expanded-active");
        }

        return true; // 允许展开
    };

    // 加载树的数据
    var initTreeList = function(callback, offset = 0, pid = 0, module_type = 0){
        var url = "/api/v1/tapes/modules/tree";
        Metronic.blockUI({target: '#left-ztee',animate: true});
        var keyword = $('#object_name').val();
        pAjaxRequest({offset:offset, limit: limit, pid: pid, module_type: module_type, keyword: keyword}, url, "GET", function (result) {
            Metronic.unblockUI('#left-ztee');
            if (result.success) {
                callback(result.data.rows);
            } else {
                UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, result.message);
                return false;
            }
        });
    }

    // 加载所有模块类型
    var initModule = function (){
        var url = "/api/v1/tapes/modules";
        Metronic.blockUI({target: '#module_type',animate: true});
        pAjaxRequest({offset:0, limit: 100}, url, "GET", function (result) {
            Metronic.unblockUI('#module_type');
            if (result.success) {
                var data = result.data.rows;
                var nodeselect = $('#module_type');
                nodeselect.empty();
                var option = $("<option>").text(LANG.UI_TAPE_DATA_ALL_MODULES).val(0);
                nodeselect.append(option);
                for(var i=0; i<data.length; i++){
                    option = $("<option>").text(data[i].name).val(data[i].uuid);
                    nodeselect.append(option);
                }
            }
        });
    }

    var moduleChange = function (){
        if (moduleType != $('#module_type').val()) {
            // 模块类型改变，重新加载树
            moduleType = $('#module_type').val();
            // 重新加载树
            initFileList();
        }
    }

    return {
        init: function () {
            // 加载右边的表格
            initTable();
            // 加载树
            initFileList();
            // 加载所有模块类型
            initModule();
            addListener();
        }
    }
}();

$(document).ready(function () {
    TapeData.init();
});