/*
 * @Author: ChengJiaFu
 * @Date: 2026-01-28 16:15:40
 * @Description: 报表logic
 * @version: 1.0
 */
var Report = function () {
    const REPORT_TABLE_FILTER_OPTIONS = [
        {
            label: '模板类型',
            field: 'template_type',
            value: [
                {
                    id: 'backup_resource',
                    value: 1,
                    text: '备份资源'
                },
                {
                    id: 'production_resource',
                    value: 2,
                    text: '生产资源'
                },
                {
                    id: 'data_protection',
                    value: 3,
                    text: '数据保护'
                },
                {
                    id: 'task',
                    value: 4,
                    text: '任务'
                },
                {
                    id: 'user',
                    value: 5,
                    text: '用户'
                },
            ]
        }
    ];
    let reportFormValidator = null; // 报表表单校验器
    let noticeFormValidator = null; // 报表通知表单校验器
    let $reportTree = $('#report_tree'); // 报表树JQ对象
    let $resportPathTree = $('.report-path-tree'); // 报表路径树JQ对象
    let $reportTable = $('#report_table'); // 报表表格JQ对象
    let $reportOffcanvas = $('#report_offcanvas'); // 报表表单抽屉JQ对象
    let $reportOffcanvasTitle = $('#report_offcanvas_title'); // 报表表单抽屉标题JQ对象
    let $notifyOffcanvas = $('#notify_offcanvas'); // 通知表单抽屉JQ对象
    let $openNotifyOffcanvasBtn = $('#open_notify_offcanvas_btn'); // 打开通知表单抽屉按钮JQ对象
    let $templateTypeSelect = $('#template_type_select'); // 模板类型选择框JQ对象
    let $templateNameInput = $('#report_name'); // 模板名输入框JQ对象
    let $resourceTypeRadioGroup = $('#resource_type_radio_group'); // 备份资源类型单选框JQ对象
    let $customizeDataSelect = $('#customized_data_select2'); // 定制数据多选框JQ对象
    let $resourceListTable = $('#resource_list_table'); // 表单中的表格JQ对象
    let $validateResourceListTip = $('.validate-reousrce-list-tip'); // 资源列表校验提示JQ对象
    let $overviewSwitch = $('#overview_switch'); // 概览开关JQ对象
    let $tendencySwitch = $('#tendency_switch'); // 趋势开关JQ对象
    let $availabilityForecastSwitch = $('#availability_forecast_switch'); // 存储使用天数预测开关JQ对象
    let $timeRangeInlineSelect = $('#time_range_inline_select'); // 时间范围内联选择框JQ对象
    let $timeRangePicker = $('#time_range_picker'); // 时间范围选择器JQ对象
    let $reportPathTree = $('#report_path_tree'); // 报表路径树JQ对象
    let $deleteCustomReportBtn = $('#delete_custom_report_btn'); // 删除自定义报表按钮JQ对象
    let $notifyEmailSwitch = $('#email_notify_switch'); // 邮件通知开关JQ对象
    let $timeStrategyTabs = $('#time_strategy_tabs'); // 时间策略标签页JQ对象
    let $perdayNotifySwitch = $('#daily_notify_switch');
    let $perweekNotifySwitch = $('#weekly_notify_switch');
    let $permonthNotifySwitch = $('#monthly_notify_switch');
    let $peryearNotifySwitch = $('#yearly_notify_switch');
    let $weekCheckboxGroup = $('#week_checkbox_group'); // 每周 checkbox group JQ对象
    let $monthCheckboxGroup = $('#month_checkbox_group'); // 每月 checkbox group JQ对象
    let $noticeDayTime = $('#notice_time_day'); // 通知时间每天 flatpickr JQ对象
    let $noticeWeekTime = $('#notice_time_week'); // 通知时间每周 flatpickr JQ对象
    let $noticeMonthTime = $('#notice_time_month'); // 通知时间每月 flatpickr JQ对象
    let $vmwareTree = $('#vmware_tree'); // 虚拟机树JQ对象
    let $vmwareTreeNoData = $('#vmware_tree_no_data'); // 虚拟机树无数据JQ对象
    let CURRENT_TEMPLATE_TYPE = 1; // 当前选中的模版类型
    let CURRENT_BACKUP_SOURCE_TYPE = 1; // 当前选中的备份资源类型
    let CURRENT_REPORT_UUID = ''; // 当前报表UUID
    let CURRENT_REPORT_GROUP_UUID = '';
    let CURRENT_SELECTED_TIME_RANGE_TYPE = 0; // 当前选中的时间范围类型
    let FILTER_PARAMS = {
        group_uuid: ''
    };
    let COPY_NEW_REPORT_FALG = true; // 复制新建报表标记    
    let ADD_CUSTOM_REPORT_FLAG = true; // 添加/修改自定义报表标记
    let CURRENT_CUSTOM_TIME_RANGE = { startTime: '', endTime: '' };
    let originalTreeData; // 用于树的懒加载回调函数
    let isSearchingTreeFlag = false; // 正在搜索树的标记
    let isInitializingForEdit = false; // 标识当前是否处于初始化编辑场景的过程中
    // 用于 默认报表 - 备份资源报表的资源类型的单选按钮组
    const backupSourceTypeData = [
        { useIcon: true, iconClass: 'vicon-cunchushebei', text: '存储', value: '1' },
        { useIcon: true, iconClass: 'vicon-cidai', text: '磁带', value: '2' },
        { useIcon: true, iconClass: 'vicon-beifenjiedian', text: '节点', value: '3' }
    ];
    let SELECTED_REPORT_TEMPLATE_LIST = []; // 当前选中的报表对象
    let CURRENT_SELECTED_MODULE_TYPE = ''; // 当前选中的模块类型

    // <----------------------------- BEGIN REPORT TREE LOGIC ------------------------------->

    /**
     * 初始化报表树的搜索输入框
     */
    const initSearchInput = () => {
         $('#report_search_container').initSearchInput({
            placeholder: '输入关键词搜索',
            onSearch: function(value) {
                const tree = $reportTree.jstree(true);
                const searchTerm = value ? value.trim() : '';

                if (searchTerm) {
                    isSearchingTreeFlag = true;
                    
                    $('.tree-wrapper__tree').block();
                    axiosGet('report/tree', { search: searchTerm }).then(res => {
                        $('.tree-wrapper__tree').unblock();

                        if (res && res.success) {
                            // 搜索成功后，将返回的数据作为树的新数据源，并刷新树
                            tree.settings.core.data = res.data;

                            // refresh(skip_loading, forget_state)
                            // skip_loading：在刷新时是否要重新从服务器获取数据。默认为false，即会重新加载数据；true为不请求新数据，直接用内存里已经加载的节点来重绘
                            // forget_state：是否要忘记当前树的状态（如展开/折叠状态）。默认为false，即会保留当前的展开/折叠状态；true为不保留，会重新展开所有节点
                            tree.refresh(false, true);
                        } else {
                            UIToastr.showWarning('搜索报表失败');
                        }
                    });
                }
            },
            onClear: function() {
                isSearchingTreeFlag = true; // 标记为true，避免触发到树的change回调从而触发表格的刷新
                const tree = $reportTree.jstree(true);

                // 清除搜索框后，恢复原始的懒加载数据源并刷新
                tree.settings.core.data = originalTreeData; // 将树的数据源配置切换回最初保存的那个懒加载函数

                // refresh(skip_loading, forget_state)
                // skip_loading：在刷新时是否要重新从服务器获取数据。默认为false，即会重新加载数据；true为不请求新数据，直接用内存里已经加载的节点来重绘
                // forget_state：是否要忘记当前树的状态（如展开/折叠状态）。默认为false，即会保留当前的展开/折叠状态；true为不保留，会重新展开所有节点
                tree.refresh(false, true);
            }
        });
    }

    /**
     * 生成自定义报表节点
     * @param {*} node 节点
     * @param {*} operatePathTreeFlag 操作路径树标记
     * @returns 
     */
    const generateCustomTreeNodes = (node, operatePathTreeFlag) => {
        const customItems = {}; // 如果节点不属于模版，则显示自定义菜单项

        const createAction = function(data) {
            const inst = $.jstree.reference(data.reference);
            const obj = inst.get_node(data.reference);
            inst.create_node(obj, { type: 'folder' }, 'last', function(new_node) {
                setTimeout(function() { inst.edit(new_node); }, 0);
            });
        };

        const renameAction = function(data) {
            const inst = $.jstree.reference(data.reference);
            inst.edit(inst.get_node(data.reference));
        };

        const isRootNode = node.parent === '#';
        const isTemplateFolder = node.original.type === 'folder' && node.original.templateFlag;
        const isTemplateFile = node.original.type === 'file' && node.original.templateFlag;
        const isCustomFolder = node.original.type === 'folder' && !node.original.templateFlag;
        const isCustomFile = node.original.type === 'file' && !node.original.templateFlag;

        // 根节点 "所有报表"：只能新建子文件夹
        if (isRootNode) {
            customItems.create = { label: '新建子文件夹', action: createAction };
            return customItems;
        }

        // 报表模板文件夹和文件：无任何操作
        if (isTemplateFolder || isTemplateFile) {
            return {};
        }

        // 自定义文件夹可以操作：新建子文件夹 重命名 删除
        if (isCustomFolder) {
            customItems.create = { label: '新建子文件夹', action: createAction };
            customItems.rename = { label: '重命名', action: renameAction };
            customItems.delete = {
                label: '删除',
                action: function(data) {
                    const inst = $.jstree.reference(data.reference);
                    const obj = inst.get_node(data.reference);
                    const confirmMsg = node.type === 'folder' ? 
                        `您确定要删除 ${obj.text} 吗？<br/><b>注意：会一并删除其下面的所有报表。</b>此操作不可恢复。` 
                        : `您确定要删除 ${obj.text} 吗？此操作不可恢复。`;

                    bootbox.confirm({
                        message: confirmMsg,
                        buttons: {
                            confirm: { label: '确认删除', className: 'btn-danger' },
                            cancel: { label: '取消', className: 'btn-secondary me-10' }
                        },
                        callback: function(result) {
                            if (result) {
                                $('.tree-wrapper__tree').block();

                                axiosDelete('report/group', { group_uuid: obj.id }).then(res => {
                                    $('.tree-wrapper__tree').unblock();

                                    if (res && res.success) {
                                        inst.delete_node(obj);
                                        UIToastr.showSuccess('删除成功');

                                        if (operatePathTreeFlag) {
                                            // 报表路径树节点删除成功后，刷新主报表树并更新表格
                                            $reportTree.jstree(true).refresh();
                                        }

                                        // 刷新报表表格(group_uuid置为空)
                                        $reportTable.bootstrapTable('refresh', { query: { group_uuid: '' } });
                                        // 高亮 报表模板文件夹
                                        $reportTree.jstree(true).select_node('0');
                                    } else {
                                        UIToastr.showWarning('重命名失败');
                                    }
                                });
                            }
                        }
                    });
                }
            };
        }

        // 自定义文件可以操作：修改 重命名 删除
        if (isCustomFile) {
            customItems.modify = {
                label: '修改',
                action: function(data) {
                    const inst = $.jstree.reference(data.reference);
                    const obj = inst.get_node(data.reference);
                    // 此处为修改操作的占位符
                    alert('修改报表：' + obj.text + ' (ID: ' + obj.id + ')');
                }
            };
            customItems.rename = { label: '重命名', action: renameAction };
            customItems.delete = {
                label: '删除',
                action: function(data) {
                    const inst = $.jstree.reference(data.reference);
                    const obj = inst.get_node(data.reference);
                    const confirmMsg = `您确定要删除 ${obj.text} 吗？此操作不可恢复。`;

                    bootbox.confirm({
                        message: confirmMsg,
                        buttons: {
                            confirm: { label: '确认删除', className: 'btn-danger' },
                            cancel: { label: '取消', className: 'btn-secondary me-10' }
                        },
                        callback: function(result) {
                            if (result) {
                                $('.tree-wrapper__tree').block();
                                axiosDelete('report', { templateUuids: obj.id }).then(res => {
                                    $('.tree-wrapper__tree').unblock();
                                    if (res && res.success) {
                                        inst.delete_node(inst.get_node(data.reference));
                                        $reportTable.bootstrapTable('refresh');
                                        UIToastr.showSuccess('删除成功');
                                    } else {
                                        UIToastr.showWarning('删除失败');
                                    }
                                });
                            }
                        }
                    });
                }
            };
        }

        return customItems;
    }

    /**
     * 重命名文件夹或文件节点
     * @param {*} tree 
     * @param {*} data 
     */
    const renameTreeNods = (tree, data, operatePathTreeFlag) => {
        // 通过判断旧名称或临时ID，确定是新建节点操作
        if (data.old === 'New node' && data.node.id.indexOf('j') === 0) {
            axiosPost('report/group', { parent_uuid: data.node.parent, group_name: data.text }).then(res => {
                if (res && res.success) {
                    UIToastr.showSuccess('新建子文件夹成功');
                    // 接口成功后，用后端返回的真实ID更新临时节点
                    tree.set_id(data.node, res.data.id);
                    // 可以在这里给新节点附加其他属性
                    const newNode = tree.get_node(res.data.id);
                    newNode.original.li_attr = res.data.li_attr;

                    if (operatePathTreeFlag) {
                        // 路径树节点新建成功后，刷新主报表树
                        $reportTree.jstree(true).refresh();
                    }
                } else {
                    UIToastr.showWarning('新建子文件夹失败');
                    // 如果创建失败，刷新父节点以移除临时节点
                    tree.refresh_node(data.node.parent);
                }
            });
        } else {
            const renameFolderFlag = data.node.type === 'folder';
            if (renameFolderFlag) { // 重命名文件夹
                axiosPut('report/group', { group_uuid: data.node.id, group_name: data.text }).then(res => {
                    if (res && res.success) {
                        UIToastr.showSuccess('重命名成功');

                        if (operatePathTreeFlag) {
                            // 路径树节点修改成功后，刷新主报表树
                            $reportTree.jstree(true).refresh();
                        }
                    } else {
                        UIToastr.showWarning('重命名失败');
                        tree.refresh_node(data.node.parent);
                    }
                });
            } else { // 重命名文件
                let params = {
                    templateUuid: data.node.id,
                    templateName: data.text,
                    onlyRenameReportName: true
                }

                axiosPut('report', params).then(res => {
                    if (res && res.success) {
                        $reportTable.bootstrapTable('refresh');
                        UIToastr.showSuccess('重命名成功');
                    } else {
                        UIToastr.showWarning('重命名失败');
                        tree.refresh_node(data.node.parent);
                    }
                });
            }
        }
    }

    /**
     * 跳转到报表详情页
     * @param {*} routeKey 
     * @param {*} reportUuid 
     */
    const goToReportDetail = (routeKey, reportUuid) => { 
        const url = `${REPORT_DETAIL_ROUTE[routeKey]}?uuid=${reportUuid}`;

        LOCATION(url, 'parent_report', 'storage_report');
    }
    
    /**
     * 初始化报表树
     */
    const initReportTree = () => {
        // 存储原始的懒加载数据处理函数
        originalTreeData = function (node, cb) {
            const nodeId = node.id;

            axiosGet('report/tree', { id: nodeId }).then(res => {
                if (res && res.success) {
                    cb(res.data);
                } else {
                    UIToastr.showWarning('获取报表树失败');
                    cb([]);
                } 
            });
        };

        $reportTree.jstree({
            core: {
                data: originalTreeData,
                check_callback: true,
                themes: {
                    responsive: false
                },
            },
            contextmenu: {
                items: function (node) {
                    return generateCustomTreeNodes(node, false);
                }
            },
            types: {
                folder: {
                    icon: 'viconfont vicon-a-Folder-closewenjianjia-guan'
                },
                file: {
                    icon: 'viconfont vicon-overview-file'
                }
            },
            plugins: ['wholerow', 'changed', 'contextmenu', 'types']
        }).on('ready.jstree', function(e, data) {
            // 4. 树加载完成后，手动选择“报表模版”节点以应用高亮
            data.instance.select_node('0');
        }).on('changed.jstree', function (e, data) {
            if (isSearchingTreeFlag) {
                return;
            }

            if (data.selected.length) {
                const selectedNode = data.instance.get_node(data.selected[0]);
                if (selectedNode.type === 'folder') {
                    let checkCountText = $('#report_table_filter_btn').find('.check-count').text();
                    let checkedCount = parseInt(checkCountText.split('/')[0]);

                    if (checkedCount > 0) { // 过滤器若已筛选需要重置
                        $('#report_table_filter_wrapper').resetFilter(
                            {
                                filterSlotId: 'report_table_filter_wrapper',
                                filterBtnId: 'report_table_filter_btn',
                                filters: REPORT_TABLE_FILTER_OPTIONS
                            }
                        );
                    }

                    CURRENT_REPORT_GROUP_UUID = selectedNode.id;
                    FILTER_PARAMS = {
                        group_uuid: CURRENT_REPORT_GROUP_UUID,
                        template_type: []
                    }

                    // 初始化报表列表
                    initReportTable();
                } else {
                    if (data.event && data.event.which === 3) { // 如果点的是鼠标右键，不执行跳转，只用于弹出操作项弹窗
                        return;
                    }

                    // 当选择一个报表时，可以在这里处理，例如显示报表详情
                    const reportId = selectedNode.id;
                    const routeKey = `${selectedNode.original.templateType}-${selectedNode.original.subType}`;

                    goToReportDetail(routeKey, reportId);
                }
            }
        }).on('open_node.jstree', function(e, data) {
            // 节点展开时，切换为打开状态的图标
            if (data.node.type === 'folder') {
                data.instance.set_icon(data.node, 'viconfont vicon-a-Folder-openwenjianjia-kai');
            }
        }).on('close_node.jstree', function(e, data) {
            // 节点关闭时，切换为关闭状态的图标
            if (data.node.type === 'folder') {
                data.instance.set_icon(data.node, 'viconfont vicon-a-Folder-closewenjianjia-guan');
            }
        }).on('rename_node.jstree', function (e, data) {
            const tree = $(this).jstree(true);
            renameTreeNods(tree, data, false);
        }).on('refresh.jstree', function () {
            isSearchingTreeFlag = false; // 搜索完成或清空搜索后重置标记
        });
    }

    // <----------------------------- END REPORT TREE LOGIC --------------------------------->


    // <----------------------------- BEGIN REPORT TABLE LOGIC ------------------------------>

    /**
     * 删除自定义报表
     */
    const handleDeleteCustomReport = () => {
        $deleteCustomReportBtn.on('click', () => {
            const selectedRows = $reportTable.bootstrapTable('getSelections');
            const templateUuids = selectedRows.map(i => { return i.templateUuid });
            const confirmMsg = `您确定要删除勾选的报表吗？此操作不可恢复。`;

            bootbox.confirm({
                message: confirmMsg,
                buttons: {
                    confirm: { label: '确认删除', className: 'btn-danger' },
                    cancel: { label: '取消', className: 'btn-secondary me-10' }
                },
                callback: function(result) {
                    if (result) {
                        $('.report-table-content-wrapper').block();

                        axiosDelete('report', { templateUuids: templateUuids.join(',') }).then(res => {
                            $('.report-table-content-wrapper').unblock();

                            if (res && res.success) {
                                // 删除树上的节点
                                templateUuids.forEach(templateUuid => {
                                    const treeNode = $reportTree.jstree(true).get_node(templateUuid);
                                    if (treeNode) {
                                        $reportTree.jstree(true).delete_node(templateUuid);
                                    }
                                });

                                $reportTable.bootstrapTable('refresh');
                                UIToastr.showSuccess('删除成功');
                            } else {
                                UIToastr.showWarning('删除失败');
                            }
                        });
                    }
                }
            });
        });
    }

    const operateEvents = {
        // 复制新建报表
        'click .copy-new-report-btn': function (e, value, row, index) {
            ADD_CUSTOM_REPORT_FLAG = false;
            COPY_NEW_REPORT_FALG = true;
            $reportOffcanvasTitle.empty().html('复制新建');

            initReportOffcanvas(row);
        },

        // 修改自定义报表
        'click .edit-report-btn': function (e, value, row, index) {
            ADD_CUSTOM_REPORT_FLAG = false;
            COPY_NEW_REPORT_FALG = false;

            $reportOffcanvasTitle.empty().html('修改报表');
            CURRENT_REPORT_UUID = row.templateUuid;

            initReportOffcanvas(row);
        },

        // 删除自定义报表
        'click .delete-report-btn': function (e, value, row, index) {
            const templateUuid = row.templateUuid;
            const confirmMsg = `您确定要删除勾选的报表吗？此操作不可恢复。`;

            bootbox.confirm({
                message: confirmMsg,
                buttons: {
                    confirm: { label: '确认删除', className: 'btn-danger' },
                    cancel: { label: '取消', className: 'btn-secondary me-10' }
                },
                callback: function(result) {
                    if (result) {
                        $('.report-table-content-wrapper').block();

                        axiosDelete('report', { templateUuids: templateUuid }).then(res => {
                            $('.report-table-content-wrapper').unblock();
                            if (res && res.success) {
                                // 删除树上的节点
                                const treeNode = $reportTree.jstree(true).get_node(templateUuid);
                                if (treeNode) {
                                    $reportTree.jstree(true).delete_node(templateUuid);
                                }

                                $openNotifyOffcanvasBtn.prop('disabled', true);
                                $deleteCustomReportBtn.prop('disabled', true);
                                $reportTable.bootstrapTable('refresh');
                                UIToastr.showSuccess('删除成功');
                            } else {
                                UIToastr.showWarning('删除失败');
                            }
                        });
                    }
                }
            });
        },
    }

    const operateFormatter = function (index, row) {
        if (row.defaultTemplateFlag === CONF.FLAG.SET) { // 默认报表展示 复制新建 按钮
           return `<button class="btn btn-icon-primary btn-icon-sm copy-new-report-btn btn-sm" type="button" data-report-id="${row.templateUuid}"><i class="viconfont vicon-copy-and-new"></i></button>`
        } else { // 非默认报表展示 修改和删除 按钮
            return `
                <div class="btn-group">
                    <button class="btn btn-icon-primary btn-icon-sm edit-report-btn btn-sm me-4" type="button" data-report-id="${row.templateUuid}"><i class="viconfont vicon-xiugai"></i></button>
                    <button class="btn btn-icon-danger btn-icon-sm delete-report-btn btn-sm" type="button" data-report-id="${row.templateUuid}"><i class="viconfont vicon-a-Deleteshanchu1"></i></button>
                </div>
            `;
        }
    }

    const initReportTableFilter = () => {
        $('#report_table_filter_wrapper').initFilter({
            filterSlotId: 'report_table_filter_wrapper',
            filterBtnId: 'report_table_filter_btn',
            filters: REPORT_TABLE_FILTER_OPTIONS
        });
    }
    
    const initReportTable = () => {
        if ($reportTable.children().length === 0) {
            const options = {
                url: 'report',
                filterBtnId: '',
                rightCustomToolbar: 'report-right-toolbar-wrapper', // toolbar 右侧区域class
                buttonsToolbar: '.toolbar-buttons-wrapper.report-toolbar-buttons', // 自定义按钮工具栏class
                search: true, // 是否启用搜索
                searchPlaceholder: '按报表名搜索', // search input placeholder
                showColumns: true, // 是否启用列筛选
                showExport: true, // 是否启用导出功能
                tableContentWrapper: '.table-content-wrapper.report-table-content-wrapper',
                pageList: [10, 25, 50, 100],
                fixedColumns: true,
                fixedNumber: 0,
                fixedRightNumber: 1,
                columns: [
                    {
                        checkbox: true,
                        sortable: false,
                        width: 1,
                        widthUnit: '%',
                        formatter: function stateFormatter(value, row, index) {
                            if (row.defaultTemplateFlag === CONF.FLAG.SET) { // 默认报表禁用勾选
                                return { disabled: true }
                            }
                        }
                    },
                    {
                        field: 'templateName',
                        title: '报表名',
                        sortable: true,
                        formatter: (value, row, index) => {
                            const reportUuid = row.templateUuid;
                            const routeKey = `${row.templateType}-${row.subType}`;
                            const url = `${REPORT_DETAIL_ROUTE[routeKey]}?uuid=${reportUuid}`;

                            return `
                                <a href="${url}" class="ajaxify" name="storage_report" route-id="parent_report" data-bs-toggle="tooltip" data-bs-placement="top" title="${value}">
                                    <span class="display-inline-block vertical-align-middle text-overflow-ellipsis" >${value}</span>
                                </a>
                            `;
                        }
                    },
                    {
                        field: 'templateType',
                        title: '模板类型',
                        sortable: true,
                        width: 10,
                        widthUnit: '%',
                        formatter: function (value) {
                            return TEMPLATE_TYPE_DES[value];
                        }
                    },
                    {
                        field: 'noticeFlag',
                        title: '通知',
                        sortable: true,
                        formatter: function (value) {
                            switch (value) {
                                case 0:
                                    return `<span class="badge badge-secondary">未知</span>`;
                                case 1:
                                    return `<span class="badge badge-success">开启</span>`;
                                case 2:
                                    return `<span class="badge badge-secondary">关闭</span>`;
                                default:
                                    break;
                            }
                        }
                    },
                    {
                        field: 'description',
                        title: '描述',
                        sortable: true,
                        formatter: function (value) {
                            return `<span class="display-inline-block vertical-align-middle text-overflow-ellipsis" data-bs-toggle="tooltip" data-bs-placement="top" title="${value}">${value}</span>`;
                        }
                    },
                    {
                        title: '操作',
                        sortable: false,
                        clickToSelect: false, //不可通过点击行选中
                        events: operateEvents,
                        formatter: operateFormatter
                    }
                ],
                onCheck: function (row) {
                    $deleteCustomReportBtn.prop('disabled', false);
                    $openNotifyOffcanvasBtn.prop('disabled', false);

                    let exists = SELECTED_REPORT_TEMPLATE_LIST.some(function(item) {
                        return item.templateUuid === row.templateUuid;
                    });

                    if (!exists) {
                        SELECTED_REPORT_TEMPLATE_LIST.push({
                            templateUuid: row.templateUuid,
                            noticeConfig: row.noticeConfig
                        });
                    }
                },
                onUncheck: function(row) {
                    let index = SELECTED_REPORT_TEMPLATE_LIST.findIndex(function(item) {
                        return item.templateUuid === row.templateUuid;
                    });

                    if (index > -1) {
                        SELECTED_REPORT_TEMPLATE_LIST.splice(index, 1);
                    }

                    // 根据剩余选项更新按钮状态
                    if (SELECTED_REPORT_TEMPLATE_LIST.length === 0) {
                        $deleteCustomReportBtn.prop('disabled', true);
                        $openNotifyOffcanvasBtn.prop('disabled', true);
                    }
                },
                onCheckAll: function (rows) {
                    $deleteCustomReportBtn.prop('disabled', false);
                    $openNotifyOffcanvasBtn.prop('disabled', false);
                    rows.forEach(function (row) {
                        let exists = SELECTED_REPORT_TEMPLATE_LIST.some(function(item) {
                            return item.templateUuid === row.templateUuid;
                        });

                        if (!exists) {
                            SELECTED_REPORT_TEMPLATE_LIST.push({
                                templateUuid: row.templateUuid,
                                noticeConfig: row.noticeConfig
                            });
                        }
                    });
                },
                onUncheckAll: function (rows) {
                    $deleteCustomReportBtn.prop('disabled', true);
                    $openNotifyOffcanvasBtn.prop('disabled', true);

                    let uncheckedUuids = rows.map(function (row) {
                        return row.templateUuid;
                    });

                    SELECTED_REPORT_TEMPLATE_LIST = SELECTED_REPORT_TEMPLATE_LIST.filter(function (item) {
                        return !uncheckedUuids.includes(item.templateUuid);
                    });
                },
            };

            $reportTable.baseTableConfig().init(options);
        } else {
            $reportTable.bootstrapTable('refresh', { query: { ...FILTER_PARAMS }});
        }
    }

    /**
     * 监听报表过滤器按钮更新事件
     *
     */
    const watchReportTableFilter = () => {
        window.$off('report_table_filter_btn-updateFilterEvent'); // 移除所有旧的过滤器监听

        window.$on('report_table_filter_btn-updateFilterEvent', (filterData) => {
            if (Array.isArray(filterData) && filterData.length > 0) {
                FILTER_PARAMS.template_type = filterData[0].value.map(v => parseInt(v)).join(',');
            } else {
                FILTER_PARAMS.template_type = [];
            }

            $reportTable.bootstrapTable('refresh', { pageNumber: 1, query: { ...FILTER_PARAMS } });
        });
    }
    
    // <----------------------------- END REPORT TABLE LOGIC -------------------------------->


    // <----------------------------- BEGIN REPORT FORM LOGIC ------------------------------->

    const handleTemplateTypeChange = () => {
        $templateTypeSelect.on('change', function () {
            CURRENT_TEMPLATE_TYPE = parseInt($(this).val());

            switch (CURRENT_TEMPLATE_TYPE) {
                case TEMPLATE_TYPE.BACKUP_RESOURCE: // 备份资源
                    $('.backup-resource-form-group').removeClass('display-none');
                    $('.module-type-form-group').addClass('display-none');
                    break;
                case TEMPLATE_TYPE.PRODUCTION_RESOURCE: // 生产资源
                    $('.backup-resource-form-group').addClass('display-none');
                    $('.module-type-form-group').addClass('display-none');

                    break;
                case TEMPLATE_TYPE.DATA_PROTECTION: // 数据保护
                    initModuleTypeCascader();
                    $('.backup-resource-form-group').addClass('display-none');
                    $('.module-type-form-group').removeClass('display-none');
                    break;
                case TEMPLATE_TYPE.TASK: // 任务
                    $('.backup-resource-form-group').addClass('display-none');
                    $('.module-type-form-group').addClass('display-none');
                    break;
                case TEMPLATE_TYPE.USER: // 用户
                    $('.backup-resource-form-group').addClass('display-none');
                    $('.module-type-form-group').addClass('display-none');
                    break;
                default:
                    break;
            }
        });
    }

    // <===================== BEGIN BACKUP RESOURCE REPORT LOGIC =========================>

    /**
     * 初始化备份资源类型单选框组
     */
    const initBackupResourceRadioGroup = () => {
        $resourceTypeRadioGroup.initRadioButtonGroup({
            radioGroupId: 'resource_type_radio_group', // 自定义一个唯一的ID，用于事件监听
            data: backupSourceTypeData,
            defaultValue: '', // 设置默认选中的值
            onchange: function (selectedValue, extraParam = {}) {
                CURRENT_BACKUP_SOURCE_TYPE = parseInt(selectedValue); // 记录当前选中的备份资源类型

                // 显示公共表单项
                $('.report-name-form-group').removeClass('display-none');
                $('.overview-form-group').removeClass('display-none');
                $('.tendency-form-group').removeClass('display-none');
                $('.customized-data-form-group').removeClass('display-none');
                $('.report-path-form-group').removeClass('display-none');
                $('.report-description-form-group').removeClass('display-none');

                // 修改或复制新建回显公共表单数据
                if (JSON.stringify(extraParam) !== '{}') {
                    $templateNameInput.val(extraParam.templateName); // 报表名称
                    $overviewSwitch.prop('checked', extraParam.detail.viewOverview); // 数据概览
                    $tendencySwitch.prop('checked', extraParam.detail.viewUsageTendency); // 数据趋势

                    if (extraParam.detail.viewUsageTendency) {
                        $('.tendency-wrap').addClass('show');
                        $timeRangeInlineSelect.setInlineSelectValue(extraParam.detail.timeRangeType); // 时间范围类型
                        if (extraParam.detail.timeRangeType === RUNNING_TIME_TYPE.CUSTOM) { // 自定义时间范围还需回显开始时间和结束时间
                            $('.time-range-picker-form-group').removeClass('display-none');
                            
                            let startTime = extraParam.detail.timeRange.split(' ⇀ ')[0];
                            let endTime = extraParam.detail.timeRange.split(' ⇀ ')[1];
                            $timeRangePicker.val(`${startTime} ⇀ ${endTime}`);

                            // 更新 CURRENT_CUSTOM_TIME_RANGE 对象
                            CURRENT_CUSTOM_TIME_RANGE.startTime = startTime;
                            CURRENT_CUSTOM_TIME_RANGE.endTime = endTime;

                            // 设置日期范围选择器的内部选中状态
                            const picker = $timeRangePicker.data('daterangepicker'); // 获取 daterangepicker 实例
                            if (picker) {
                                // 将开始和结束时间转换为 moment 对象
                                picker.setStartDate(moment(startTime, 'YYYY-MM-DD'));
                                picker.setEndDate(moment(endTime, 'YYYY-MM-DD'));
                            }
                            
                            // 更新校验样式
                            $('.time-range-tip').addClass('display-none');
                            $('#form_group_timerange_picker .form-daterangepicker').removeClass('is-invalid').addClass('is-valid');
                        } else {
                            $('.time-range-picker-form-group').addClass('display-none');
                        }
                    } else {
                        $('.tendency-wrap').removeClass('show');
                    }

                    // 路径树回显 勾选过的 节点，且勾选的节点的父节点要展开
                    let checkedPath = extraParam.detail.path.split('/');
                    initReportPathTree(checkedPath);

                    $('#description').val(extraParam.description);
                }

                switch (CURRENT_BACKUP_SOURCE_TYPE) {
                    case BACKUP_SOURCE_TYPE.STORAGE: //存储
                        // 存储设备
                        $('.resource-list-form-group').removeClass('display-none');
                        $('#resource_list_form_group_label').html('存储设备');
                        $('#resource_list_form_group_accordion_btn').html(`<i class="viconfont vicon-cunchushebei me-10"></i>选择存储设备`);
                        initResourceListTable(STORAGE_LIST_TABLE_OPTIONS, extraParam);

                        $('.inteligentize-forecast-form-group').removeClass('display-none');

                        $('.overview-tips-info').empty().html('开启将展示存储总数、在线存储、离线存储、存储使用率、存储容量统计和存储使用排行的数据');

                        $('.modules-checkbox-group').removeClass('display-none');

                        initCustomizedDataSelect(STORAGE_REPORT_MULTIPLE_OPTIONS, extraParam); // 定制数据

                        // 修改或复制新建回显数据
                        if (JSON.stringify(extraParam) !== '{}') {
                            $resourceTypeRadioGroup.setRadioButtonGroupDisabled([BACKUP_SOURCE_TYPE.TAPE, BACKUP_SOURCE_TYPE.NODE], true); // 禁用 磁带和节点 项

                            if (extraParam.detail.viewUsageTendency) {
                                // 回显模块类型级联型复选框组
                                let moduleGroups = [];
                                if (extraParam.detail.modules) {
                                    let checkedModules = [];
                                    if (extraParam.detail.modules.scheduled_backup && extraParam.detail.modules.scheduled_backup.length > 0) { // 勾选了定时保护模块
                                        checkedModules = checkedModules.concat(extraParam.detail.modules.scheduled_backup);
                                    }

                                    if (extraParam.detail.modules.real_time_backup && extraParam.detail.modules.real_time_backup.length > 0) { // 勾选了实时保护模块
                                        checkedModules = checkedModules.concat(extraParam.detail.modules.real_time_backup);
                                    }

                                    // 递归 MODULE_CASCADER_GROUPS 得到带有 checked 标记的新数组
                                    moduleGroups = setCheckedByValues(MODULE_CASCADER_GROUPS, checkedModules);
                                } else {
                                    moduleGroups = MODULE_CASCADER_GROUPS;
                                }
                                
                                initModuleCheckboxGroups(moduleGroups);
                            }

                            $availabilityForecastSwitch.prop('checked', extraParam.detail.availabilityForecast); // 存储使用天数预测
                        } else { // 新建报表
                            initModuleCheckboxGroups(MODULE_CASCADER_GROUPS); // 初始化模块类型级联复选框组
                        }

                        break;
                    case BACKUP_SOURCE_TYPE.TAPE: // 磁带
                        // 磁带设备
                        $('.resource-list-form-group').removeClass('display-none');
                        $('#resource_list_form_group_label').html('磁带组');
                        $('#resource_list_form_group_accordion_btn').html(`<i class="viconfont vicon-cidai me-10"></i>选择磁带组`);
                        initResourceListTable(TAPE_LIST_TABLE_OPTIONS, extraParam);
                        
                        $('.inteligentize-forecast-form-group').addClass('display-none');

                        $('.overview-tips-info').empty().html('开启将展示磁带库总数、驱动器总数、离线磁带、已使用磁带、装载率、磁带容量统计');

                        $('.modules-checkbox-group').removeClass('display-none');
                        
                        initCustomizedDataSelect(TAPE_REPORT_MULTIPLE_OPTIONS, extraParam); // 初始化定制数据下拉选择器

                        if (JSON.stringify(extraParam) !== '{}') {
                            $resourceTypeRadioGroup.setRadioButtonGroupDisabled([BACKUP_SOURCE_TYPE.STORAGE, BACKUP_SOURCE_TYPE.NODE], true); // 禁用 存储和节点 项

                            if (extraParam.detail.viewUsageTendency) {
                                // 回显模块类型级联型复选框组
                                let moduleGroups = [];
                                if (extraParam.detail.modules) {
                                    let checkedModules = [];
                                    if (extraParam.detail.modules.scheduled_backup && extraParam.detail.modules.scheduled_backup.length > 0) { // 勾选了定时保护模块
                                        checkedModules = checkedModules.concat(extraParam.detail.modules.scheduled_backup);
                                    }

                                    if (extraParam.detail.modules.real_time_backup && extraParam.detail.modules.real_time_backup.length > 0) { // 勾选了实时保护模块
                                        checkedModules = checkedModules.concat(extraParam.detail.modules.real_time_backup);
                                    }

                                    // 递归 MODULE_CASCADER_GROUPS 得到带有 checked 标记的新数组
                                    moduleGroups = setCheckedByValues(MODULE_CASCADER_GROUPS, checkedModules);
                                } else {
                                    moduleGroups = MODULE_CASCADER_GROUPS;
                                }
                                
                                initModuleCheckboxGroups(moduleGroups);
                            }
                        } else { // 新建报表
                            initModuleCheckboxGroups(MODULE_CASCADER_GROUPS); // 初始化模块类型级联复选框组
                        }

                        break;
                    case BACKUP_SOURCE_TYPE.NODE: // 节点
                        // 节点
                        $('.resource-list-form-group').removeClass('display-none');
                        $('#resource_list_form_group_label').html('节点');
                        $('#resource_list_form_group_accordion_btn').html(`<i class="viconfont vicon-node_manager me-10"></i>选择节点`);
                        initResourceListTable(NODE_LIST_TABLE_OPTIONS, extraParam);

                        // 模块类型级联组
                        $('.modules-checkbox-group').addClass('display-none');
                        $('.inteligentize-forecast-form-group').addClass('display-none');

                        $('.overview-tips-info').empty().html('开启将展示节点总数、在线节点、离线节点、异常节点');

                        initCustomizedDataSelect(NODE_REPORT_MULTIPLE_OPTIONS, extraParam); // 初始化定制数据下拉选择器

                        if (JSON.stringify(extraParam) !== '{}') {
                            $resourceTypeRadioGroup.setRadioButtonGroupDisabled([BACKUP_SOURCE_TYPE.STORAGE, BACKUP_SOURCE_TYPE.TAPE], true); // 禁用 存储和磁带 项
                        }

                        break;
                    default:
                        break;
                }
                
            }
        });
    }

    /**
     * 初始化资源列表表格
     * @param {*} tableOptions - 表格配置选项
     * @param {*} echoData - 用于回显的数据
     */
    const initResourceListTable = (tableOptions, echoData) => {
        if ($resourceListTable.children().length > 0) {
            // 销毁上一个
            $resourceListTable.bootstrapTable('destroy');
        }

        const options = {
            url: tableOptions.url,
            filterBtnId: '',
            rightCustomToolbar: 'resource-list-right-toolbar-wrapper',
            buttonsToolbar: '.toolbar-buttons-wrapper.resource-list-toolbar-buttons', // 自定义按钮工具栏class
            search: true, // 是否启用搜索
            searchPlaceholder: tableOptions.searchPlaceholder, // search input placeholder
            showColumns: false, // 是否启用列筛选
            showExport: false, // 是否启用导出功能
            showRefresh: false, // 是否启用刷新功能
            tableContentWrapper: '.table-content-wrapper.resource-list-content-wrapper',
            pageList: [10, 25, 50, 100],
            onPostBody: function() {
                let tableData = $resourceListTable.bootstrapTable('getData');

                if (tableData.length === 0) {
                    $('.resource-list-form-group-accordion .accordion-panel').addClass('nodata');
                } else {
                    console.log(echoData, 'echoData');
                    if (echoData && echoData.detail) {
                        let resources = [];

                        switch (CURRENT_TEMPLATE_TYPE) {
                            case TEMPLATE_TYPE.BACKUP_RESOURCE: // 备份资源报表模板
                                switch (CURRENT_BACKUP_SOURCE_TYPE) {
                                    case BACKUP_SOURCE_TYPE.STORAGE: // 存储报表
                                        resources = Array.isArray(echoData.detail.storages) && echoData.detail.storages.length > 0 ? echoData.detail.storages : [];
                                        break;
                                    case BACKUP_SOURCE_TYPE.TAPE: // 磁带报表
                                        resources = Array.isArray(echoData.detail.tapes) && echoData.detail.tapes.length > 0 ? echoData.detail.tapes : [];
                                        break;
                                    case BACKUP_SOURCE_TYPE.NODE: // 节点报表
                                        resources = Array.isArray(echoData.detail.nodes) && echoData.detail.nodes.length > 0 ? echoData.detail.nodes : [];
                                        break;
                                    default:
                                        break;
                                }
                                break;
                            case TEMPLATE_TYPE.PRODUCTION_RESOURCE: // 生产资源报表模板
                                break;
                            case TEMPLATE_TYPE.DATA_PROTECTION: // 数据保护报表模板
                                break;
                            case TEMPLATE_TYPE.TASK: // 任务报表模板
                                break;
                            case TEMPLATE_TYPE.USER: // 用户报表模板
                                break;
                            default:
                                break;
                        }

                        console.log(resources, 'resources');
                        $resourceListTable.bootstrapTable('checkBy', {
                            field: 'id', // 用来匹配的字段是 'id'
                            values: resources.map(item => item.id)
                        });
                    }
                    $('.resource-list-form-group-accordion .accordion-panel').removeClass('nodata');
                }
            },
            onCheck: function (row) {
                // 隐藏校验提示
                $validateResourceListTip.addClass('display-none');
            },
            onUncheck: function (row) {
                let selectedRows = $resourceListTable.bootstrapTable('getSelections');
                if (selectedRows.length === 0) {
                    $validateResourceListTip.removeClass('display-none');
                } else {
                    $validateResourceListTip.addClass('display-none');
                }
            },
            onCheckAll: function (rows) {
                // 隐藏校验提示
                $validateResourceListTip.addClass('display-none');
            },
            onUncheckAll: function (rows) {
                // 增加校验提示
                $validateResourceListTip.removeClass('display-none');
            },
            columns: tableOptions.columns
        }

        $resourceListTable.baseTableConfig().init(options);
    }

    /**
     * 初始化时间范围内嵌下拉
     */
    const initTimeRangeSelect = () => {
        $timeRangeInlineSelect.initInlineLabelSelect({
            label: '时间范围',
            name: 'timeRangeType',
            defaultValue: 4,
            options: [
                { value: 1, text: '近一天' },
                { value: 2, text: '近三天' },
                { value: 3, text: '近一周' },
                { value: 4, text: '近一月' },
                { value: 5, text: '自定义' }
            ],
            onchange: function(value) {
                CURRENT_SELECTED_TIME_RANGE_TYPE = parseInt(value);

                if (CURRENT_SELECTED_TIME_RANGE_TYPE === RUNNING_TIME_TYPE.CUSTOM) {
                    $('.time-range-picker-form-group').removeClass('display-none');
                } else {
                    $('.time-range-picker-form-group').addClass('display-none');
                }
            }
        });
    }

    /**
     * 初始化日期范围选择器
     */
    const initTimeRangePicker = () => {
        $timeRangePicker.daterangepicker({
            autoUpdateInput: false,
            maxDate: moment(), // 禁用今天之后的日期
            locale: {
            format: 'YYYY-MM-DD',
            separator: ' ⇀ ',
            applyLabel: '确认',
            cancelLabel: '取消',
            fromLabel: 'From',
            toLabel: 'To',
            customRangeLabel: '自定义',
            weekLabel: 'W',
            daysOfWeek: ['日', '一', '二', '三', '四', '五', '六'],
            monthNames: [
            '一月',
            '二月',
            '三月',
            '四月',
            '五月',
            '六月',
            '七月',
            '八月',
            '九月',
            '十月',
            '十一月',
            '十二月'
            ],
            firstDay: 1
            },
            parentEl: $('#form_group_timerange_picker')
        }).on('apply.daterangepicker', function(ev, picker) {
            // 当用户应用选择时，手动更新输入框的值
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' ⇀ ' + picker.endDate.format('YYYY-MM-DD'));
            CURRENT_CUSTOM_TIME_RANGE.startTime = picker.startDate.format('YYYY-MM-DD');
            CURRENT_CUSTOM_TIME_RANGE.endTime = picker.endDate.format('YYYY-MM-DD');

            $('.time-range-tip').addClass('display-none');
            $('#form_group_timerange_picker .form-daterangepicker').removeClass('is-invalid').addClass('is-valid');
        }).on('cancel.daterangepicker', function(ev, picker) {
            // 当用户取消时，清空输入框和值
            $(this).val('');
            CURRENT_CUSTOM_TIME_RANGE.startTime = '';
            CURRENT_CUSTOM_TIME_RANGE.endTime = '';

            $('.time-range-tip').removeClass('display-none');
            $('#form_group_timerange_picker .form-daterangepicker').addClass('is-invalid');
        });
    }

    /**
     * 初始化模块多选框组
     * @param {*} moduleGroups 
     */
    const initModuleCheckboxGroups = (moduleGroups) => {
        $('#module_checkbox_groups_wrap').initCascaderCheckboxGroup({
            slotId: 'module_checkbox_groups_wrap',
            checkboxGroupsId: 'module_checkbox_groups',
            checkboxGroups: moduleGroups
        });
    }

    /**
     * 初始化定制数据多选框
     * @param {*} options - select2 的数据源
     * @param {*} extraParam - 用于回显的额外数据
     * @returns 
     */
    const initCustomizedDataSelect = (options, extraParam) => {
        if (!Array.isArray(options) || options.length === 0) {
            return;
        }

        if ($customizeDataSelect.children().length > 0) {
            // 清空上一个定制数据多选框
            $customizeDataSelect.empty();
        }

        let result = '';

        options.forEach((item, index) => {
            let html = '';

            // 检查 extraParam 是否为有效对象且包含回显所需的数据
            if (extraParam && extraParam.detail && Array.isArray(extraParam.detail.customFields)) {
                if (extraParam.detail.customFields.includes(item.id)) {
                    html = `<option value="${item.id}" selected="selected">${item.text}</option>`;
                } else {
                    html = `<option value="${item.id}">${item.text}</option>`;
                }
            } else { // 新建模式：默认勾选前五项
                if (index <= 5) {
                    html = `<option value="${item.id}" selected="selected">${item.text}</option>`;
                } else {
                    html = `<option value="${item.id}">${item.text}</option>`;
                }
            }
            
            result += html;
        });

        $customizeDataSelect.append(result);

        // 放在抽屉底部区域时
        $customizeDataSelect.select2({
            theme: 'bootstrap-5',
            closeOnSelect: false,
            multiple: true,
            maximumSelectionDisplay: 5,
            dropdownParent: $reportOffcanvas
        });
    }

    /**
     * 初始化自定义报表文件夹路径树
     * @param {string[]} [pathToRestore=null] - 需要回显的路径ID数组
     */
    const initReportPathTree = (pathToRestore = null) => {
        // 销毁已存在的实例，防止重复初始化导致报错
        if ($reportPathTree.jstree(true)) {
            $reportPathTree.jstree(true).destroy();
        }

        $reportPathTree.jstree({
            core: {
                data: function (node, cb) {
                    const nodeId = node.id;
                    axiosGet('report/path_tree', { id: nodeId }).then(res => {
                        if (res && res.success) {
                            if (nodeId === '#') {
                                const rootNode = res.data.find(n => n.id === 'root');
                                if (rootNode) {
                                    // 根节点不可勾选
                                    rootNode.state.checkbox_disabled = true;
                                }
                            }
                            cb(res.data)
                        } else {
                            UIToastr.showWarning('获取保存路径树失败');
                            cb([]);
                        }
                    });
                },
                check_callback: true,
                themes: {
                    responsive: false
                },
            },
            types: {
                folder: {
                    icon: 'viconfont vicon-a-Folder-closewenjianjia-guan'
                },
                file: {
                    icon: 'viconfont vicon-overview-file'
                }
            },
            plugins: ['wholerow', 'changed', 'contextmenu', 'types', 'checkbox'],
            checkbox: {
                three_state: false, // 父子节点状态不关联
                whole_node: false,
                tie_selection: false // 将勾选与选中分离，使用 get_checked() 获取勾选节点
            },
            contextmenu: {
                 items: function (node) {
                    return generateCustomTreeNodes(node, true);
                }
            }
        }).on('open_node.jstree', function(e, data) {
            // 节点展开时，切换为打开状态的图标
            if (data.node.type === 'folder') {
                data.instance.set_icon(data.node, 'viconfont vicon-a-Folder-openwenjianjia-kai');
            }
        }).on('close_node.jstree', function(e, data) {
            // 节点关闭时，切换为关闭状态的图标
            if (data.node.type === 'folder') {
                data.instance.set_icon(data.node, 'viconfont vicon-a-Folder-closewenjianjia-guan');
            }
        }).on('check_node.jstree', function (e, data) {
            const instance = data.instance;
            const checkedNodes = instance.get_checked();
            for (const nodeId of checkedNodes) {
                // 每次只能勾选一个节点
                if (nodeId !== data.node.id) {
                    instance.uncheck_node(nodeId);
                }
            }

            if (!isInitializingForEdit) { // 修改报表初始化状态不触发校验提示
                // 当勾选一个节点时，隐藏路径校验提示
                $('.validate-customized-path-tip').addClass('display-none');
                $resportPathTree.removeClass('is-invalid').addClass('is-valid');
            }
        }).on('uncheck_node.jstree', function (e, data) {
            const instance = data.instance;
            // 当取消勾选后，如果没有任何节点被选中，则显示路径校验提示
            if (instance.get_checked().length === 0) {
                $('.validate-customized-path-tip').removeClass('display-none');
                $resportPathTree.removeClass('is-valid').addClass('is-invalid');
            }
        }).on('rename_node.jstree', function (e, data) {
            const tree = $(this).jstree(true);
            renameTreeNods(tree, data, true);
        }).on('ready.jstree', function () {
            if (pathToRestore && pathToRestore.length > 0) { // 修改时回显对应节点
                isInitializingForEdit = true; // 开始初始化编辑场景

                const treeInstance = $reportPathTree.jstree(true);
                // 复制数组以防修改原始数据
                const pathToOpen = [...pathToRestore];
                // 最后一个ID是需要勾选的目标节点
                const targetNodeId = pathToOpen.pop();

                const openNodesSequentially = (nodes) => {
                    // 当所有父节点都已展开
                    if (nodes.length === 0) {
                        // 勾选目标节点
                        treeInstance.check_node(targetNodeId);
                        // 将节点滚动到可视区域（暂不需要滚动）
                        // const targetElement = document.getElementById(targetNodeId);
                        // if (targetElement) {
                        //     targetElement.scrollIntoView({ block: 'center' });
                        // }

                        // 编辑场景初始化完成，重置标志
                        setTimeout(() => {
                            isInitializingForEdit = false;
                        }, 0);

                        return;
                    }

                    const nodeId = nodes.shift();
                    // 根节点'root'通常是默认可见的，无需手动打开
                    if (nodeId === 'root') {
                        openNodesSequentially(nodes);
                        return;
                    }

                    // 展开当前节点，并在完成后递归调用自身以展开下一个节点
                    treeInstance.open_node(nodeId, () => openNodesSequentially(nodes));
                };

                openNodesSequentially(pathToOpen);
            } else { // 新建时每次重置树的状态
                $reportPathTree.jstree(true).deselect_all();
            }
        });
    }

    /**
     * 校验选择的存储条数
     */
    const validateSelectedResources = () => {
        let selectedRows = $resourceListTable.bootstrapTable('getSelections');

        if (selectedRows.length === 0) {
            $('.custom-validate-tip.validate-reousrce-list-tip').removeClass('display-none');
            return false;
        } else {
            $('.custom-validate-tip.validate-reousrce-list-tip').addClass('display-none');

            return true;
        }
    }

    /**
     * 自定义时间校验
     * @returns 
     */
    const validateCustomizedTime = () => {
        let tendencyIsChecked = $tendencySwitch.get(0).checked;

        if (tendencyIsChecked && CURRENT_SELECTED_TIME_RANGE_TYPE === RUNNING_TIME_TYPE.CUSTOM) { // 已开启使用趋势且时间范围类型选的是自定义
            if (!CURRENT_CUSTOM_TIME_RANGE.startTime || !CURRENT_CUSTOM_TIME_RANGE.endTime) {
                $('.time-range-tip').removeClass('display-none');
                $('#form_group_timerange_picker .form-daterangepicker').addClass('is-invalid');
                return false;
            }
        }

        $('.time-range-tip').addClass('display-none');
        $('#form_group_timerange_picker .form-daterangepicker').removeClass('is-invalid').addClass('is-valid');
        return true;
    }

    /**
     * 保存路径校验
     * @returns 
     */
    const validateReportPath = () => {
        let selectedPathNodes = $reportPathTree.jstree(true).get_checked();

        if (selectedPathNodes.length === 0) {
            $('.validate-customized-path-tip').removeClass('display-none');
            $resportPathTree.removeClass('is-valid').addClass('is-invalid');
            
            return false;
        } else {
            $('.validate-customized-path-tip').addClass('display-none');
            $resportPathTree.removeClass('is-invalid').addClass('is-valid');
            
            return true;
        }
    }

    /**
     * 初始化报表表单校验器
     */
    const initReportFormValidator = () => {
        reportFormValidator = $('#report_form').jbvalidator({
            errorMessage: true,
            successClass: true
        });

        // 自定义表单校验
        reportFormValidator.validator.custom = (el) => {
            
        }
    }

    /**
     * 重置表单
     */
    const handleResetReportForm = () => {
        $templateTypeSelect.prop('disabled', false);
        $templateTypeSelect.val(TEMPLATE_TYPE.BACKUP_RESOURCE).trigger('change');

        $('.backup-resource-form-group').removeClass('display-none');
        $resourceTypeRadioGroup.setRadioButtonGroupValue(null); // 设置null来清除 active 的项目
        $resourceTypeRadioGroup.setRadioButtonGroupDisabled([BACKUP_SOURCE_TYPE.STORAGE, BACKUP_SOURCE_TYPE.TAPE, BACKUP_SOURCE_TYPE.NODE], false); // 取消禁用备份资源单选框组

        $('.report-name-form-group').addClass('display-none');
        $templateNameInput.val('');

        $('.resource-list-form-group').addClass('display-none');

        $('.overview-form-group').addClass('display-none');
        $overviewSwitch.prop('checked', false);
        
        $('.tendency-form-group').addClass('display-none');
        $tendencySwitch.prop('checked', false);
        $('#tendency_wrap').removeClass('show');
        $timeRangeInlineSelect.setInlineSelectValue(RUNNING_TIME_TYPE.LAST_MONTH); // 重置时间范围类型值为近一月
        $('.time-range-picker-form-group').addClass('display-none');
        $timeRangePicker.val('');
        // 更新 CURRENT_CUSTOM_TIME_RANGE 对象
        CURRENT_CUSTOM_TIME_RANGE.startTime = '';
        CURRENT_CUSTOM_TIME_RANGE.endTime = '';
        // 重置日期范围选择器
        const picker = $timeRangePicker.data('daterangepicker'); // 获取 daterangepicker 实例
        if (picker) {
            picker.setStartDate(moment().startOf('day')); // 或者设置为空
            picker.setEndDate(moment().startOf('day'));
            // 触发取消事件来清除选择
            picker.element.trigger('cancel.daterangepicker');
        }

        $('.modules-checkbox-group').addClass('display-none');
        $('.inteligentize-forecast-form-group').addClass('display-none');
        $availabilityForecastSwitch.prop('checked', false);

        $('.customized-data-form-group').addClass('display-none');

        $('.report-path-form-group').addClass('display-none');
        initReportPathTree(); // 初始化报表路径树

        $('.report-description-form-group').addClass('display-none');
        $('#description').val('');
    }

    /**
     * 初始化报表表单
     * @param {*} currentReport 当前报表
     */
    const initReportOffcanvas = (currentReport = {}) => {
        if (ADD_CUSTOM_REPORT_FLAG) { // 新建表单重置：模板类型重置为备份资源，资源类型取消选中，其他项均隐藏
            handleResetReportForm();
        } else { // 修改或复制新建都是回显表单
            const { templateType, subType, templateName, defaultTemplateFlag, detail, description } = currentReport;

            $templateTypeSelect.val(templateType).trigger('change');
            $templateTypeSelect.prop('disabled', true); // 禁用 模版类型
            switch (templateType) {
                case TEMPLATE_TYPE.BACKUP_RESOURCE: // 备份资源报表模板
                    $('.backup-resource-form-group').removeClass('display-none');

                    $resourceTypeRadioGroup.setRadioButtonGroupValue(subType, currentReport); // 回显备份资源类型radio group
                    break;
                case TEMPLATE_TYPE.PRODUCTION_RESOURCE: // 生产资源报表模板
                    $('.backup-resource-form-group').addClass('display-none');    

                    break;
                case TEMPLATE_TYPE.DATA_PROTECTION: // 数据保护报表模板
                    $('.backup-resource-form-group').addClass('display-none');

                    break;
                case TEMPLATE_TYPE.TASK: // 任务报表模板
                    $('.backup-resource-form-group').addClass('display-none');

                    break;
                case TEMPLATE_TYPE.USER: // 用户报表模板
                    $('.backup-resource-form-group').addClass('display-none');

                    break;
                default:
                    break;
            }
        }

        $reportOffcanvas.offcanvas('show');
    }

    const initPageSelect2 = () => {
        $templateTypeSelect.select2({
            minimumResultsForSearch: Infinity,
            theme: 'bootstrap-5'
        });
    }

    /**
     * 使用情况开关change
     */
    const handleChangeTendencySwitch = () => {
        $tendencySwitch.on('change', () => {
            let tendencyIsChecked = $tendencySwitch.get(0).checked;

            if (!tendencyIsChecked) { // 关闭后重置值与校验
                $timeRangeInlineSelect.setInlineSelectValue(RUNNING_TIME_TYPE.LAST_MONTH); // 重置时间范围类型值为近一月
                
                // 重置自定义日期范围
                CURRENT_CUSTOM_TIME_RANGE.startTime = '';
                CURRENT_CUSTOM_TIME_RANGE.endTime = '';
                $timeRangePicker.val('');
                $('.time-range-tip').addClass('display-none');
                $('#form_group_timerange_picker .form-daterangepicker').removeClass('is-invalid').removeClass('is-valid');

                // 重置模块类型级联复选框组（如果是存储报表或磁带报表）
                initModuleCheckboxGroups(MODULE_CASCADER_GROUPS); // 初始化模块类型级联复选框组
            }
        });
    }

    // <============================== END BACKUP RESOURCE REPORT LOGIC ==============================>


    // <============================== BEGIN PRODUCT RESOURCE REPORT LOGIC ==============================>

    // <============================== END PRODUCT RESOURCE REPORT LOGIC ================================>


    // <============================== BEGIN DATA PROTECTION REPORT LOGIC ==============================>

    let vmTreeObj;
    let selectedVmIds = [];

    /**
     * jstree 的 "changed" 事件回调函数。
     * 当用户勾选或取消勾选节点时，此函数会被触发，并更新 selectedVmIds 数组。
     * @param {Event} e - 事件对象
     * @param {object} data - jstree 提供的事件数据
     */
    function onVmTreeChange(e, data) {
        if (!vmTreeObj) return;

        // 获取所有被选中的节点对象
        const selectedNodes = vmTreeObj.get_selected(true);

        // 筛选出所有叶子节点（即真正的虚拟机），并提取它们的ID
        // jstree 的 is_parent() 方法可以准确判断一个节点是否为父节点
        selectedVmIds = selectedNodes
            .filter(node => !vmTreeObj.is_parent(node))
            .map(node => node.id);
    }

    const initVmwareTree = (vcenterPlatformType) => {
        // 如果已存在一个 jstree 实例，先销毁它以防止冲突
        if (vmTreeObj) {
            // jstree V3.x 使用 get_instance().destroy()
            $.jstree.reference($vmwareTree).destroy();
        }

        // 显示加载状态
        $('.tree-list-container__tree').block();

        // 初始化 jstree
        $vmwareTree.jstree({
            core: {
                // jstree 的核心数据源配置
                data: function (node, cb) {
                    // jstree 在请求根节点时，node.id 为 '#'
                    const nodeId = node.id === '#' ? '#' : node.id;
                    
                    // 从父节点的 data 属性中获取 vcenter_uuid (如果存在)
                    // 这是我们后端代码在第二层节点上附加的
                    const vcenterUuid = (node.data && node.data.vcenter_uuid) ? node.data.vcenter_uuid : null;

                    // 使用封装好的 axiosGet 发起请求
                    axiosGet('report/vm_tree', {
                        id: nodeId,
                        module_type: vcenterPlatformType,
                        vcenter_uuid: vcenterUuid
                    }).then(res => {
                        if (res.success) {
                            // 仅在初次加载时检查“无数据”状态
                            if (nodeId === '#' && res.data.length === 0) {
                                $('.tree-list-no-data').removeClass('display-none');
                                $('.tree-list-container__tree').addClass('display-none');
                            } else {
                                $('.tree-list-no-data').addClass('display-none');
                                $('.tree-list-container__tree').removeClass('display-none');
                            }
                            // 通过回调函数将数据传递给 jstree
                            console.log(res.data, '返回数据');
                            cb(res.data);
                        } else {
                            UIToastr.showWarning(res.message || '获取虚拟机树数据失败');
                            cb([]); // 出错时返回空数组
                        }
                    }).catch(err => {
                        console.error('获取虚拟机树数据失败:', err);
                        UIToastr.showWarning('获取虚拟机树数据失败');
                        cb([]);
                    }).finally(() => {
                        // 仅在初次加载（请求根节点）后解除 UI 锁定
                        if (nodeId === '#') {
                            $('.tree-list-container__tree').unblock();
                        }
                    });
                },
                themes: {
                    'responsive': true, // 响应式主题
                }
            },
            plugins: ['checkbox', 'wholerow'], // 启用复选框和整行选中插件
            checkbox: {
                'keep_selected_style': false,
                'three_state': true // 启用父子节点联动勾选
            }
        }).on('changed.jstree', onVmTreeChange) // 绑定 'changed' 事件
        .on('ready.jstree', function () {
            // 树加载完成后，将实例存入全局变量
            vmTreeObj = $.jstree.reference($vmwareTree);
        });
    }

    // /**
    //  * 获取数据保护报表树数据
    //  * @param {*} vcenterPlatformType 虚拟化平台类型
    //  */
    // const getVmwareTreeData = (vcenterPlatformType) => {
    //     axiosGet('report/vm_tree', {vcenterPlatformType}).then(res => {
    //         try {
    //             if (res.success) {
    //                 const { data } = res;

    //                 if (data.length > 0) {
    //                     $('.tree-list-no-data').addClass('display-none');
    //                     $('.tree-list-container').removeClass('display-none');

    //                     initVmwareTree(data);
    //                 } else {
    //                     $('.tree-list-container').addClass('display-none');
    //                     $('.tree-list-no-data').removeClass('display-none');
    //                 }
    //             } else {
    //                 UIToastr.showWarning('获取树数据失败');
    //             }
    //         } catch (error) {
    //             UIToastr.showWarning('获取树数据失败');
    //         } finally {
    //             $('.tree-list-container__tree').unblock();
    //         }
    //     });
    // }

    const initModuleTypeCascader = () => {
        if ($('#module_type_cascader').children().length === 0) {
            const cascader = new Cascader({
                container: "#module_type_cascader",
                data: BUSINESS_TYPE_MODULE_TYPE_TREE,
                placeholder: '请选择对象类型',
                clearable: true,
                selectFn: (moduls) => {
                    console.log(moduls, '级联选择的对象类型');
                    CURRENT_SELECTED_MODULE_TYPE = moduls.length === 2 ? moduls[1].value : '';

                    switch (CURRENT_SELECTED_MODULE_TYPE) {
                        case MODULE_TYPE_MAP.TIMING_BACKUP.VM:
                            $('.tree-list-form-group').removeClass('display-none');
                            $('#tree_list_form_group_label').html('虚拟化');
                            $('#tree_list_form_group_accordion_btn').html(`<i class="viconfont vicon-overview-vm me-10"></i>选择虚拟机`);

                            initVmwareTree(VCENTER_PLATFORM_TYPE.VM);
                            
                            break;
                        case MODULE_TYPE_MAP.TIMING_BACKUP.PRIVATE_CLOUD:
                            $('.tree-list-form-group').removeClass('display-none');
                            $('#tree_list_form_group_label').html('私有云');
                            $('#tree_list_form_group_accordion_btn').html(`<i class="viconfont vicon-overview-vm me-10"></i>选择虚拟机`);

                            initVmwareTree(VCENTER_PLATFORM_TYPE.PRIVATE_CLOUD);

                            break;
                        case MODULE_TYPE_MAP.TIMING_BACKUP.PUBLIC_CLOUD:
                            $('.tree-list-form-group').removeClass('display-none');
                            $('#tree_list_form_group_label').html('公有云');
                            $('#tree_list_form_group_accordion_btn').html(`<i class="viconfont vicon-overview-vm me-10"></i>选择虚拟机`);

                            initVmwareTree(VCENTER_PLATFORM_TYPE.PUBLIC_CLOUD);

                            break;
                        case MODULE_TYPE_MAP.TIMING_BACKUP.COMPLETE_MACHINE: 
                            break;
                        case MODULE_TYPE_MAP.TIMING_BACKUP.VOLUME: 
                            break;
                        case MODULE_TYPE_MAP.TIMING_BACKUP.FILE: 
                            break;
                        case MODULE_TYPE_MAP.TIMING_BACKUP.NAS: 
                            break;
                        case MODULE_TYPE_MAP.TIMING_BACKUP.HADOOP: 
                            break;
                        case MODULE_TYPE_MAP.TIMING_BACKUP.OBS: 
                            break;
                        case MODULE_TYPE_MAP.TIMING_BACKUP.DB: 
                            break;
                        case MODULE_TYPE_MAP.TIMING_BACKUP.M365: 
                            break;
                        case MODULE_TYPE_MAP.TIMING_BACKUP.KUBERNETES: 
                            break;
                        default:
                            break;
                    }
                }
            });
        }
    }

    // <============================== END DATA PROTECTION REPORT LOGIC ================================>

    /**
     * 当前报表表单校验是否通过
     */
    const reportFormIsValid = () => {
        // 获取公共的自定义校验：保存路径、自定义时间，其中 报表名 和 自定义数据 是reportFormValidator设置的校验，调用checkAll()方法，为0表示校验通过
        let isReportPathValid = validateReportPath(); // 校验保存路径
        let isCustomizedTimeValid = validateCustomizedTime(); // 校验自定义时间

        switch (CURRENT_TEMPLATE_TYPE) {
            case TEMPLATE_TYPE.BACKUP_RESOURCE: // 备份资源报表模板
                switch (CURRENT_BACKUP_SOURCE_TYPE) {
                    case BACKUP_SOURCE_TYPE.STORAGE: // 存储报表
                        let isSelectedStoragesValid = validateSelectedResources(); // 校验存储条数

                        return reportFormValidator.checkAll() === 0 && isSelectedStoragesValid && isCustomizedTimeValid && isReportPathValid;
                    case BACKUP_SOURCE_TYPE.TAPE: // 磁带报表
                        let isSelectedTapesValid = validateSelectedResources(); // 校验磁带条数

                        return reportFormValidator.checkAll() === 0 && isSelectedTapesValid && isCustomizedTimeValid && isReportPathValid;
                    case BACKUP_SOURCE_TYPE.NODE: // 节点报表
                        let isSelectedNodessValid = validateSelectedResources(); // 校验节点个数

                        return reportFormValidator.checkAll() === 0 && isSelectedNodessValid && isCustomizedTimeValid && isReportPathValid;
                    default:
                        break;
                }
                break;
            case TEMPLATE_TYPE.PRODUCTION_RESOURCE: // 生产资源报表模板
                break;
            case TEMPLATE_TYPE.DATA_PROTECTION: // 数据保护报表模板
                break;
            case TEMPLATE_TYPE.TASK: // 任务报表模板
                break;
            case TEMPLATE_TYPE.USER: // 用户报表模板
                break;
            default:
                break;
        }
    }

    /**
     * 获取存储报表特定参数
     * @returns 
     */
    const getStorageReportParams = () => {
        const storageSelections = $resourceListTable.bootstrapTable('getSelections');

        return {
            storages: storageSelections.map(item => ({ id: item.id, storageUuid: item.storageUuid, name: item.name })),
            modules: $('#module_checkbox_groups_wrap').getCascaderCheckboxGroupValues('module_checkbox_groups'),
            availabilityForecast: $availabilityForecastSwitch.get(0).checked,
        };
    }

    /**
     * 获取磁带报表特定参数
     * @returns 
     */
    const getTapeReportParams = () => {
        const tapeSelections = $resourceListTable.bootstrapTable('getSelections');

        return {
            tapes: tapeSelections.map(item => ({ id: item.id, name: item.name, groupUuid: item.groupUuid })),
            modules: $('#module_checkbox_groups_wrap').getCascaderCheckboxGroupValues('module_checkbox_groups'),
        };
    }

    /**
     * 获取节点报表特定参数
     * @returns 
     */
    const getNodeReportParams = () => {
        const nodeSelections = $resourceListTable.bootstrapTable('getSelections');

        return {
            nodes: nodeSelections.map(item => ({ id: item.id, nodeUuid: item.node_uuid, ip: item.ip }))
        };
    }

    /**
     * 使用策略模式定义处理器映射表对象，将报表类型映射到相应的处理函数，以取代胖到的 switch 语句
     */
    const reportTemplateHandlers = {
        /**
         * 备份资源报表处理器
         */
        [TEMPLATE_TYPE.BACKUP_RESOURCE]: () => {
            const params = {
                detail: {},
                subType: CURRENT_BACKUP_SOURCE_TYPE,
            };

            // 收集此类型下的通用参数
            params.detail.viewOverview = $overviewSwitch.get(0).checked;
            params.detail.viewUsageTendency = $tendencySwitch.get(0).checked;
            if (params.detail.viewUsageTendency) {
                params.detail.timeRangeType = CURRENT_SELECTED_TIME_RANGE_TYPE;
                if (params.detail.timeRangeType === RUNNING_TIME_TYPE.CUSTOM) {
                    params.detail.timeRange = `${CURRENT_CUSTOM_TIME_RANGE.startTime} ⇀ ${CURRENT_CUSTOM_TIME_RANGE.endTime}`;
                }
            }
            params.detail.customFields = $customizeDataSelect.select2('val');

            // 根据子类型（存储、磁带）调用对应的参数收集函数
            const subTypeHandlers = {
                [BACKUP_SOURCE_TYPE.STORAGE]: getStorageReportParams,
                [BACKUP_SOURCE_TYPE.TAPE]: getTapeReportParams,
                [BACKUP_SOURCE_TYPE.NODE]: getNodeReportParams,
            };

            const subTypeHandler = subTypeHandlers[CURRENT_BACKUP_SOURCE_TYPE];
            if (subTypeHandler) {
                // 将子类型收集的参数合并到 detail 对象中
                Object.assign(params.detail, subTypeHandler());
            }

            return params;
        },

        /**
         * 生产资源报表处理器 (待实现)
         */
        [TEMPLATE_TYPE.PRODUCTION_RESOURCE]: () => {
            // 未来在此实现生产资源报表的参数收集逻辑
            return {};
        },

        /**
         * 数据保护报表处理器 (待实现)
         */
        [TEMPLATE_TYPE.DATA_PROTECTION]: () => {
            return {};
        },

        /**
         * 任务报表处理器 (待实现)
         */
        [TEMPLATE_TYPE.TASK]: () => {
            return {};
        },

        /**
         * 用户报表处理器 (待实现)
         */
        [TEMPLATE_TYPE.USER]: () => {
            return {};
        },
    }

    /**
     * 处理报表表单提交
     */
    const handleReportFormSubmit = () => {
        $('#report_form_submit').on('click', () => {
            console.log(reportFormIsValid(), '表单校验是否通过');
            if (reportFormIsValid()) { // 判断表单是否检验通过
                const templateType = CURRENT_TEMPLATE_TYPE; // 模板类型

                // 获取所有报表类型都需要的公共参数
                const checkedPathNodes = $reportPathTree.jstree(true).get_checked();
                console.log(checkedPathNodes, 'checkedPathNodes');
                const firstCheckedNodeId = checkedPathNodes[0];
                const node = $reportPathTree.jstree(true).get_node(firstCheckedNodeId);
                const pathIds = node.parents.slice().reverse();
                pathIds.shift(); // 移除 '#' 根节点
                pathIds.push(firstCheckedNodeId); // 添加当前节点ID

                const params = {
                    templateType: templateType,
                    templateName: $templateNameInput.val(),
                    groupUuid: firstCheckedNodeId, // 路径是单选
                    description: $('#description').val(),
                    detail: {
                        path: pathIds.join('/'), // 保存路径
                    }
                };

                // 根据 templateType 从处理器映射表中获取对应的处理器
                const handler = reportTemplateHandlers[templateType];

                if (handler) {
                    // 执行处理器，获取特定类型的参数
                    const specificParams = handler();

                    // 将特定参数深度合并到主 params 对象中
                    // 特别注意：这里需要合并 detail 对象，而不是覆盖它
                    const detail = params.detail;
                    Object.assign(params, specificParams);
                    if (specificParams.detail) {
                        Object.assign(detail, specificParams.detail);
                    }
                    params.detail = detail;
                }

                if (ADD_CUSTOM_REPORT_FLAG || COPY_NEW_REPORT_FALG) { // 新建或复制新建报表
                    $reportOffcanvas.block();

                    axiosPost('report', params).then(res => {
                        $reportOffcanvas.unblock();
                        if (res.success) {
                            UIToastr.showSuccess('新建报表', '新建报表成功');

                            // 高亮并展开对应新建报表的父文件夹
                            $reportTree.jstree(true).deselect_all(); // 取消之前的高亮
                            // TODO：如果报表树没有展开过作为保存路径的节点，select_node 将不会生效，就不会高亮并选中
                            $reportTree.jstree(true).select_node(firstCheckedNodeId);
                            $reportTree.jstree(true).open_node(firstCheckedNodeId, function() {
                                // 节点展开后刷新该节点的子节点列表
                                $reportTree.jstree(true).refresh_node(firstCheckedNodeId);
                            });

                            $deleteCustomReportBtn.prop('disabled', true);
                            $openNotifyOffcanvasBtn.prop('disabled', true);
                            $reportOffcanvas.offcanvas('hide');
                        } else {
                            UIToastr.showWarning('新建报表', '新建报表失败');
                        }
                    });
                } else { // 修改报表
                    $reportOffcanvas.block();

                    params.onlyRenameReportName = false;
                    params.templateUuid = CURRENT_REPORT_UUID;
                    axiosPut('report', params).then(res => {
                        $reportOffcanvas.unblock();
                        if (res.success) {
                            UIToastr.showSuccess('修改报表', '修改报表成功');

                            // 高亮并展开对应新建报表的父文件夹
                            $reportTree.jstree(true).deselect_all(); // 取消之前的高亮
                            // TODO：如果报表树没有展开过作为保存路径的节点，select_node 将不会生效，就不会高亮并选中
                            $reportTree.jstree(true).select_node(firstCheckedNodeId);
                            $reportTree.jstree(true).open_node(firstCheckedNodeId, function() {
                                // 节点展开后刷新该节点的子节点列表
                                $reportTree.jstree(true).refresh_node(firstCheckedNodeId);
                            });

                            $deleteCustomReportBtn.prop('disabled', true);
                            $openNotifyOffcanvasBtn.prop('disabled', true);
                            $reportOffcanvas.offcanvas('hide');
                        } else {
                            UIToastr.showWarning('修改报表', '修改报表失败');
                        } 
                    });
                }
            }
        });
    }

    const watchReportOffcanvasShow = () => {
        $reportOffcanvas.on('show.bs.offcanvas', () => {
            // 一键清除校验样式
            reportFormValidator.resetStyle();
            $('#form_group_timerange_picker .form-daterangepicker').removeClass('is-invalid').removeClass('is-valid');
            $resportPathTree.removeClass('is-invalid').removeClass('is-valid');
        });
    }

    // <----------------------------- END REPORT FORM LOGIC --------------------------------->

    // <----------------------------- BEGIN NOTIFY FORM LOGIC ------------------------------->

    const initNotifyOffcanvas = () => {
        if (SELECTED_REPORT_TEMPLATE_LIST.length === 1) { // 仅勾选一个报表时，需要回显配置数据
            const { noticeConfig } = SELECTED_REPORT_TEMPLATE_LIST[0];

            if (JSON.stringify(noticeConfig) === '{}') { // 未配置通知
                $notifyEmailSwitch.prop('checked', false);
                $('#notify_strategy_wrap').removeClass('show');
                handleEmailSwitchChange();
            } else { // 开启了通知则回显表单
                $notifyEmailSwitch.prop('checked', true);
                $('#notify_strategy_wrap').addClass('show');

                const { receiveEmail, reportConfig } = noticeConfig;
                const { timeStrategy, noticeContentTypes, attachmentFormats} = reportConfig;

                let timeStrategyList = JSON.parse(timeStrategy);
                timeStrategyList.forEach(item => {
                    switch (parseInt(item.type)) {
                        case NOTIFY_TIMESTRATEGY_TYPE.DAILY: // 日报
                            $perdayNotifySwitch.prop('checked', true);
                            $('#perday_strategy_wrap').addClass('show');
                            $noticeDayTime.val(item.noticeTime);
                            $timeStrategyTabs.find(':first .nav-link').addClass('checked');
                            break;
                        case NOTIFY_TIMESTRATEGY_TYPE.WEEKLY: // 周报
                            console.log(item.days, 'item.days');
                            $perweekNotifySwitch.prop('checked', true);
                            $('#perweek_strategy_wrap').addClass('show');
                            $weekCheckboxGroup.setCheckboxGroupValues(item.days);
                            $noticeWeekTime.val(item.noticeTime);
                            $timeStrategyTabs.find(':nth-child(2) .nav-link').addClass('checked');   
                            break;
                        case NOTIFY_TIMESTRATEGY_TYPE.MONTHLY: // 月报
                            $permonthNotifySwitch.prop('checked', true);
                            $('#permonth_strategy_wrap').addClass('show');
                            $monthCheckboxGroup.setCheckboxGroupValues(item.days);
                            $noticeMonthTime.val(item.noticeTime);
                            $timeStrategyTabs.find(':nth-child(3) .nav-link').addClass('checked');
                            break;
                        case NOTIFY_TIMESTRATEGY_TYPE.YEARLY: // 年报
                            $peryearNotifySwitch.prop('checked', true);
                            $timeStrategyTabs.find(':nth-child(4) .nav-link').addClass('checked');
                            break;
                        default:
                            break;
                    }
                });

                $('#receive_emails').val(receiveEmail.join('\n'));

                let noticeContentList = JSON.parse(noticeContentTypes);
                $('#notify_content_checkbox_group').setCheckboxGroupValues(noticeContentList);

                if (noticeContentList.includes(NOTIFY_CONTENT.DETAIL)) { // 包含数据明细
                    $('.detail-export-form-group').removeClass('display-none');
                    $('.detail-export-content-form-group').removeClass('display-none');

                    $('#export_detail_radio_group').setRadioGroupValue(reportConfig.detailExportRange);

                    if (reportConfig.detailExportRange === EXPORT_DETAIL_TYPE.CUSTOM) { // 选择的是自定义条数
                        $('.export-nums-spinner').removeClass('display-none');
                        $('#export_nums').val(parseInt(reportConfig.exportNums));
                    } else {
                        $('.export-nums-spinner').addClass('display-none');
                        $('#export_nums').val(10);
                    }
                } else {
                    $('.detail-export-form-group').removeClass('display-none');
                    $('.detail-export-content-form-group').addClass('display-none');
                }

                if (reportConfig.singleObjectDetailFlag === true) {
                    $('#single_object_switch').prop('checked', true);
                    $('#single_object_config_wrap').addClass('show');

                    let singleObjectList = JSON.parse(reportConfig.singleObjectTypes);

                    $('#single_object_checkbox_group').setCheckboxGroupValues(singleObjectList);
                    if (singleObjectList.includes(SINGLE_TASK_OBJECT_TYPE.HISTORY_RUN_RECORD)) { // 勾选了历史运行记录
                        $('.history-detail-export-form-group').removeClass('display-none');
                        $('.history-detail-export-content-form-group').removeClass('display-none');

                        $('#single_detail_radio_group').setRadioGroupValue(reportConfig.historyRunRecordExportRange);

                        if (reportConfig.historyRunRecordExportRange === EXPORT_DETAIL_TYPE.CUSTOM) { // 选择的是自定义条数
                            $('.export-single-nums-spinner').removeClass('display-none');
                            $('#single_spinner_num').val(parseInt(reportConfig.exportHistoryNums));
                        } else {
                            $('.export-single-nums-spinner').addClass('display-none');
                        }
                    } else {
                        $('.history-detail-export-form-group').addClass('display-none');
                        $('.history-detail-export-content-form-group').addClass('display-none');
                    }
                } else {
                    $('#single_object_switch').prop('checked', false);
                    $('#single_object_config_wrap').removeClass('show');
                }

                $('#attachment_format_checkbox_group').setCheckboxGroupValues(JSON.parse(attachmentFormats));
            }
        } else { // 勾选多个报表，相当于重置配置表单
            $notifyEmailSwitch.prop('checked', false);
            $('#notify_strategy_wrap').removeClass('show');
            handleEmailSwitchChange();
        }

        $notifyOffcanvas.offcanvas('show');
    }

    const initSpinnerGroup = () => {
        $('#export_nums').inputSpinner({
            groupClass: 'spinner-group export-nums-spinner'
        });

        // 初始化完后先隐藏，等待选择自定义条数时再显示
        $('.export-nums-spinner').addClass('display-none');

        $('#single_spinner_num').inputSpinner({
            groupClass: 'spinner-group export-single-nums-spinner'
        });

        // 初始化完后先隐藏，等待选择自定义条数时再显示
        $('.export-single-nums-spinner').addClass('display-none');
    }

    /**
     * 初始化通知表单校验器
     */
    const initNoticeFormValidator = () => {
        // 初始化表单校验器
        noticeFormValidator = $('#notify_form').jbvalidator({
            errorMessage: true,
            successClass: true
        });

        // 自定义表单校验
        noticeFormValidator.validator.custom = (el) => {

            // 多邮箱校验
            if ($(el).is('[name=emails]')) {
                let text = $(el).val();

                // eslint-disable-next-line no-undef
                let result = illeagalEmailsCheck(text);

                if (result.flag) {
                    return `第${result.index}行邮箱格式不正确，请重新输入`;
                }
            }
        };
    }

    /**
     * 处理通知表单提交
     *
     */
    const handleNoticeFormSubmit = () => {
        $('#notice_form_submit').on('click', () => {
            let templateUuids = JSON.stringify(SELECTED_REPORT_TEMPLATE_LIST.map(i => i.templateUuid));
            let emailNotifyFlag = $notifyEmailSwitch.get(0).checked;

            let params = {};
            params.emailNotifyFlag = emailNotifyFlag;
            params.templateUuids = templateUuids;

            if (emailNotifyFlag) { // 开启了邮件通知，进行表单校验和参数获取
                const noticeFormIsValid = noticeFormValidator.checkAll() === 0;
                const notifyStrategyIsValid = checkNoticeStrategyIsValid();

                if (noticeFormIsValid && notifyStrategyIsValid) {
                    // 获取邮件通知策略
                    let timeStrategy = [];
                    let checkedTabItems = $('#time_strategy_tabs').find('.nav-item .nav-link.checked').map(function() {
                        return $(this).attr('href');
                    }).get();

                    checkedTabItems.forEach(item => {
                        switch (item) {
                            case '#tab_perday': // 日报
                                timeStrategy.push({ type: 1, noticeTime: $noticeDayTime.val() });
                                break;
                            case '#tab_perweek': // 周报
                                timeStrategy.push({ type: 2, noticeTime: $noticeWeekTime.val(), days: $weekCheckboxGroup.getCheckboxGroupValues() });
                                break;
                            case '#tab_permonth': // 月报
                                timeStrategy.push({ type: 3, noticeTime: $noticeMonthTime.val(), days: $monthCheckboxGroup.getCheckboxGroupValues() });
                                break;
                            case '#tab_peryear': // 年报
                                timeStrategy.push({ type: 4, noticeTime: '' });
                                break;
                            default:
                                break;
                        }
                    });

                    params.timeStrategy = JSON.stringify(timeStrategy);

                    let recEmails = $('#receive_emails').val().split('\n'); // 获取通知邮箱
                    params.recEmails = JSON.stringify(recEmails);

                    let noticeContentTypes = $('#notify_content_checkbox_group').getCheckboxGroupValues(); // 获取通知内容
                    params.noticeContentTypes = JSON.stringify(noticeContentTypes);

                    if (noticeContentTypes.includes(NOTIFY_CONTENT.DETAIL)) { // 包含数据明细通知内容类型
                        // 获取数据明细导出范围
                        let detailExportRange = $('#export_detail_radio_group').getRadioGroupValue();
                        params.detailExportRange = detailExportRange;

                        if (detailExportRange === EXPORT_DETAIL_TYPE.CUSTOM) { // 自定义条数
                            let exportNums = $('#export_nums').val();
                            params.exportNums = exportNums;
                        }
                    }

                    let singleObjectDetailFlag = $('#single_object_switch').get(0).checked;
                    params.singleObjectDetailFlag = singleObjectDetailFlag;

                    if (singleObjectDetailFlag) { // 开启单对象详情
                        let singleObjectTypes = $('#single_object_checkbox_group').getCheckboxGroupValues(); // 获取单对象详情类型
                        params.singleObjectTypes = JSON.stringify(singleObjectTypes);

                        if (singleObjectTypes.includes(SINGLE_TASK_OBJECT_TYPE.HISTORY_RUN_RECORD)) { // 包含历史运行记录
                            // 获取历史运行记录导出范围
                            let historyRunRecordExportRange = $('#single_detail_radio_group').getRadioGroupValue();
                            params.historyRunRecordExportRange = historyRunRecordExportRange;

                            if (historyRunRecordExportRange === EXPORT_DETAIL_TYPE.CUSTOM) { // 自定义条数
                                let exportHistoryNums = $('#single_spinner_num').val();
                                params.exportHistoryNums = exportHistoryNums;
                            }
                        }
                    }
                    
                    let attachmentFormats = $('#attachment_format_checkbox_group').getCheckboxGroupValues(); // 获取附件格式
                    params.attachmentFormats = JSON.stringify(attachmentFormats);

                    $('.report-offcanvas show .offcanvas-body').block();
                    axiosPost('report/notice', params).then(res => {
                        if (res.success) {
                            UIToastr.showSuccess('通知配置成功');

                            $openNotifyOffcanvasBtn.prop('disabled', true);
                            $deleteCustomReportBtn.prop('disabled', true);
                            SELECTED_REPORT_TEMPLATE_LIST = []; // 清空选中的报表模板 uuids
                            $notifyOffcanvas.offcanvas('hide');
                            $reportTable.bootstrapTable('refresh');
                        } else {
                            UIToastr.showWarning('通知配置失败');
                        }
                    });
                }
            } else { // 未开启邮件通知，不需要检验直接调接口
                $('.report-offcanvas show .offcanvas-body').block();
                axiosPost('report/notice', params).then(res => {
                    if (res.success) {
                        UIToastr.showSuccess('通知配置成功');

                        $openNotifyOffcanvasBtn.prop('disabled', true);
                        $deleteCustomReportBtn.prop('disabled', true);
                        SELECTED_REPORT_TEMPLATE_LIST = []; // 清空选中的报表模板 uuids
                        $notifyOffcanvas.offcanvas('hide');
                        $reportTable.bootstrapTable('refresh');
                    } else {
                        UIToastr.showWarning('通知配置失败');
                    }
                });
            }
        })
    }

    /**
     * 处理邮件通知开关change（同时应用于单个报表打开通知配置时的回显）
     */
    const handleEmailSwitchChange = () => {
        let emailNotifyFlag = $notifyEmailSwitch.get(0).checked;

        if (!emailNotifyFlag) { // 关闭时重置表单
            // 一键清除表单校验样式
            noticeFormValidator.resetStyle();

            // 重置日报
            $perdayNotifySwitch.prop('checked', false);
            $('#perday_strategy_wrap').removeClass('show');
            $noticeDayTime.val('');
            $timeStrategyTabs.find(':first .nav-link').removeClass('checked').addClass('active');

            // 重置周报
            $perweekNotifySwitch.prop('checked', false);
            $('#perweek_strategy_wrap').removeClass('show');
            $weekCheckboxGroup.setCheckboxGroupValues([]); // 重置checkbox group
            $noticeWeekTime.val('');
            $timeStrategyTabs.find(':nth-child(2) .nav-link').removeClass('checked').removeClass('active');

            // 重置月报
            $permonthNotifySwitch.prop('checked', false);
            $('#permonth_strategy_wrap').removeClass('show');
            $monthCheckboxGroup.setCheckboxGroupValues([]); // 重置checkbox group
            $noticeMonthTime.val('');
            $timeStrategyTabs.find(':nth-child(3) .nav-link').removeClass('checked').removeClass('active');

            // 重置年报
            $peryearNotifySwitch.prop('checked', false);
            $timeStrategyTabs.find(':nth-child(4) .nav-link').removeClass('checked').removeClass('active');

            $('#time_strategy_tab_content').find('.tab-pane').removeClass('show active');
            $('#time_strategy_tab_content').find(':first.tab-pane').addClass('show active');

            // 重置邮箱
            $('#receive_emails').val('');

            // 重置通知内容 checkbox group
            $('#notify_content_checkbox_group').setCheckboxGroupValues([]);
            $('.detail-export-form-group').addClass('display-none');
            $('.detail-export-content-form-group').addClass('display-none');
            $('#export_detail_radio_group').setRadioGroupValue(EXPORT_DETAIL_TYPE.ALL);
            $('#export_nums').val(10);
            
            // 重置单对象详情
            $('#single_object_switch').prop('checked', false);
            $('#single_object_config_wrap').removeClass('show');

            // 重置单对象详情 checkbox group
            $('#single_object_checkbox_group').setCheckboxGroupValues([]);
            $('.history-detail-export-form-group').addClass('display-none');
            $('.history-detail-export-content-form-group').addClass('display-none');
            $('#single_detail_radio_group').setRadioGroupValue(EXPORT_DETAIL_TYPE.ALL);
            $('#single_spinner_num').val(10);

            // 重置附件格式 checkbox group
            $('#attachment_format_checkbox_group').setCheckboxGroupValues([]);
        } else {
            // 这两项如果填过数字值，在关闭时 display-none class不知道为什么加不上，但这两行代码放在开启这是生效的
            $('.export-nums-spinner').addClass('display-none');
            $('.export-single-nums-spinner').addClass('display-none');
        }
    }

    const watchEmailSwitchChange = () => {
        $notifyEmailSwitch.on('change', () => {
            handleEmailSwitchChange();
        });
    }

    const hanldeNotifySwitchChange = () => {
       $perdayNotifySwitch.on('change', function() {
            if ($(this).is(':checked')) {
                if ($noticeDayTime.val() !== '') {
                    $timeStrategyTabs.find(':first .nav-link').addClass('checked');
                } else {
                    $timeStrategyTabs.find(':first .nav-link').removeClass('checked');
                }
            } else {
                $timeStrategyTabs.find(':first .nav-link').removeClass('checked');

                $noticeDayTime.val('');
                $noticeDayTime.removeClass('is-invalid').removeClass('is-valid');
            }

            $('.validate-notice-strategy-tip').css({'top': '174px'});
       });

       $perweekNotifySwitch.on('change', function() {
            if ($(this).is(':checked')) {
                let checkedWeeks = $weekCheckboxGroup.getCheckboxGroupValues();
                let noticeWeekTime = $noticeWeekTime.val();

                if (checkedWeeks.length > 0 && noticeWeekTime !== '') {
                    $timeStrategyTabs.find(':nth-child(2) .nav-link').addClass('checked');
                } else {
                    $timeStrategyTabs.find(':nth-child(2) .nav-link').removeClass('checked');
                }

                $('.validate-notice-strategy-tip').css({'top': '217px'});
            } else {
                $timeStrategyTabs.find(':nth-child(2) .nav-link').removeClass('checked');
                $noticeWeekTime.val('');
                $noticeWeekTime.removeClass('is-invalid').removeClass('is-valid');
                $('#week_checkbox_group .checkbox-group-wrapper').find('.check-group-wrapper__item .form-check-input').removeClass('is-valid').removeClass('is-invalid');

                $('.validate-notice-strategy-tip').css({'top': '174px'});
            }
       });

       $permonthNotifySwitch.on('change', function() {
            if ($(this).is(':checked')) {
                let checkedMonths = $monthCheckboxGroup.getCheckboxGroupValues();
                let noticeMonthTime = $noticeMonthTime.val();

                if (checkedMonths.length > 0 && noticeMonthTime !== '') {
                    $timeStrategyTabs.find(':nth-child(3) .nav-link').addClass('checked');
                } else {
                    $timeStrategyTabs.find(':nth-child(3) .nav-link').removeClass('checked');
                }

                $('.validate-notice-strategy-tip').css({'top': '337px'});
            } else {
                $timeStrategyTabs.find(':nth-child(3) .nav-link').removeClass('checked');
                $noticeMonthTime.val('');
                $noticeMonthTime.removeClass('is-invalid').removeClass('is-valid');
                $('#month_checkbox_group .checkbox-group-wrapper').find('.check-group-wrapper__item .form-check-input').removeClass('is-valid').removeClass('is-invalid');

                $('.validate-notice-strategy-tip').css({'top': '174px'});
            }
       });

       $peryearNotifySwitch.on('change', function() {
            if ($(this).is(':checked')) {
                $timeStrategyTabs.find(':nth-child(4) .nav-link').addClass('checked');
            } else {
                $timeStrategyTabs.find(':nth-child(4) .nav-link').removeClass('checked');
            }
       });
    }

    const handleNoticeTimeChange = () => {
        $noticeDayTime.on('change', function() {
            if ($(this).val() !== '') {
                $timeStrategyTabs.find(':first .nav-link').addClass('checked');
            } else {
                $timeStrategyTabs.find(':first .nav-link').removeClass('checked');
            }
        });

        $noticeWeekTime.on('change', function() {
            let checkedWeeks = $weekCheckboxGroup.getCheckboxGroupValues();

            if (checkedWeeks.length > 0 && $(this).val() !== '') {
                $timeStrategyTabs.find(':nth-child(2) .nav-link').addClass('checked');
            } else {
                $timeStrategyTabs.find(':nth-child(2) .nav-link').removeClass('checked');
            }
        });

        $noticeMonthTime.on('change', function() {
            let checkedMonths = $monthCheckboxGroup.getCheckboxGroupValues();
            if (checkedMonths.length > 0 && $(this).val() !== '') {
                $timeStrategyTabs.find(':nth-child(3) .nav-link').addClass('checked');
            } else {
                $timeStrategyTabs.find(':nth-child(3) .nav-link').removeClass('checked');
            }
        });
    }

    const checkNoticeStrategyIsValid = () => {
        let valid = true;
        let checkedTabItems = $('#time_strategy_tabs').find('.nav-item .nav-link.checked');

        $('.validate-notice-strategy-tip').empty();
        
        if (checkedTabItems.length === 0) { // 策略未配置
            $('.validate-notice-strategy-tip').html('邮件通知已开启，请配置通知策略');
            $('.validate-notice-strategy-tip').removeClass('display-none');

            valid =  false;
        } else {
            $('.validate-notice-strategy-tip').addClass('display-none');
        }

        return valid;
    }

    /**
     * 初始化报表通知表单
     */
    const initReportNoticeForm = () => {
        // 生成每周 checkbox group
        $weekCheckboxGroup.initCheckboxGroup({
            checkboxData: WEEKS, 
            checkedValues: [], 
            onChange: function(allValues) {
                if (allValues.length > 0 && $noticeWeekTime.val() !== '') {
                    $timeStrategyTabs.find(':nth-child(2) .nav-link').addClass('checked');
                } else {
                    $timeStrategyTabs.find(':nth-child(2) .nav-link').removeClass('checked');
                }
            }
        });

        // 生成每月 checkbox group
        $monthCheckboxGroup.initCheckboxGroup({
            checkboxData: MONTHS,
            checkedValues: [],
            onChange: function(allValues) {
                if (allValues.length > 0 && $noticeMonthTime.val() !== '') {
                    $timeStrategyTabs.find(':nth-child(3) .nav-link').addClass('checked');
                } else {
                    $timeStrategyTabs.find(':nth-child(3) .nav-link').removeClass('checked');
                }
            }
        });

        // 生成通知内容 checkbox group
        $('#notify_content_checkbox_group').initCheckboxGroup({
            checkboxData: NOTIFY_CONTENT_TYPES,
            checkedValues: [],
            onChange: function(allValues) {
                if (allValues.includes(NOTIFY_CONTENT.DETAIL)) { // 如果选择了数据明细则显示数据明细导出范围
                    $('.detail-export-form-group').removeClass('display-none');
                    $('.detail-export-content-form-group').removeClass('display-none');
                } else {
                    $('.detail-export-form-group').addClass('display-none');
                    $('.detail-export-content-form-group').addClass('display-none');
                }
            }
        });

        // 数据明细通知内容 radio group
        $('#export_detail_radio_group').initRadioGroup({
            name: 'detailRange',
            radioData: EXPORT_DETAIL_RADIO_TYPES,
            selectedValue: 1,
            onChange: function (currentValue) {
                if (currentValue === EXPORT_DETAIL_TYPE.CUSTOM) { // 选择自定义条数时显示自定义条数输入框
                    $('.export-nums-spinner').removeClass('display-none');
                } else {
                    $('.export-nums-spinner').addClass('display-none');
                }
            }
        });

        // 附件格式 checkbox group
        $('#attachment_format_checkbox_group').initCheckboxGroup({checkboxData: ATTACHMENT_FORMATS});

        // 单对象详情 checkbox group (以任务报表为例)
        $('#single_object_checkbox_group').initCheckboxGroup({
            checkboxData: SINGLE_TASK_OBJECT_TYPES,
            checkedValues: [],
            onChange: function(allValues) {
                if (allValues.includes(SINGLE_TASK_OBJECT_TYPE.HISTORY_RUN_RECORD)) { // 勾选了历史运行记录时显示自定义条数输入框
                    $('.history-detail-export-form-group').removeClass('display-none');
                    $('.history-detail-export-content-form-group').removeClass('display-none');
                } else {
                    $('.history-detail-export-form-group').addClass('display-none');
                    $('.history-detail-export-content-form-group').addClass('display-none');
                }
            }
        });

        // 历史运行记录导出范围
        $('#single_detail_radio_group').initRadioGroup({
            name: 'singleDetailRange',
            radioData: EXPORT_HISTORY_RECOEDS_RADIO_TYPES,
            selectedValue: 1,
            onChange: function (currentValue) {
                if (currentValue === EXPORT_DETAIL_TYPE.CUSTOM) {
                    $('.export-single-nums-spinner').removeClass('display-none');
                } else {
                    $('.export-single-nums-spinner').addClass('display-none');
                }
            }
        });

        initSpinnerGroup();

        // 初始化表单校验器
        initNoticeFormValidator();

        // 邮件通知开关 switch change
        watchEmailSwitchChange();

        // 配置日报、周报、月报、年报通知开关change
        hanldeNotifySwitchChange();

        // 处理通知时间 change
        handleNoticeTimeChange();

        // 处理表单提交
        handleNoticeFormSubmit();
    }

    // <----------------------------- END NOTIFY FORM LOGIC --------------------------------->

    const initListeners = () => {
        $('#open_report_drawer_btn').on('click', () => {
            ADD_CUSTOM_REPORT_FLAG = true;
            COPY_NEW_REPORT_FALG = false;
            $reportOffcanvasTitle.empty().html('新建');

            initReportOffcanvas();
        });

        $openNotifyOffcanvasBtn.on('click', () => {
            initNotifyOffcanvas();
        });

        initSearchInput(); // 初始化搜索输入框插件

        initPageSelect2(); // 初始化页面Select2插件

        initReportFormValidator(); // 初始化报表表单校验器

        initBackupResourceRadioGroup(); // 初始化备份资源类型单选框组

        handleDeleteCustomReport(); // 处理删除自定义报表

        handleTemplateTypeChange(); // 模板类型下拉change

        handleChangeTendencySwitch(); // 处理趋势开关变化

        handleReportFormSubmit(); // 处理报表表单提交

        watchReportOffcanvasShow(); // 监听报表新建/修改弹窗显示事件

        watchReportTableFilter(); // 监听报表表格过滤器事件

        initReportNoticeForm(); // 初始化系统通知表单
    }

    return{
        init: function () {
            initReportTree(); // 初始化报表树
            initReportTableFilter(); // 初始化报表表格过滤器
            initTimeRangeSelect(); // 初始化时间范围内嵌下拉
            initTimeRangePicker(); // 初始化自定义时间范围组件
            initListeners();
        }
    };
}();
jQuery(document).ready(function () {
    Report.init();
});