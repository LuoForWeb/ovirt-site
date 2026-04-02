var lunStorageDetail = function () {
    var dragState;

    // 初始化事件监听
    function initListeners() {
        $('#resize_div').on('mousedown', handleMouseDown);
    }

    /**
     * @param {MouseEvent} 鼠标事件
     */
    function handleMouseDown(event) {
        // 禁止用户选择网页中文字
        document.onselectstart = () => false
        // 禁止用户拖动元素
        document.ondragstart = () => false

        // 保存鼠标最后移动的位置（Y轴）
        dragState = {
            // 鼠标开始移动的位置（Y轴）
            startMouseTop: event.clientY,
            // 鼠标最后移动的位置（Y轴）
            endMouseTop: event.clientY,
        }
        // // 绑定鼠标移动事件
        // $('.bottom-drawer').on('mousemove', handleMouseMove)
        // // 绑定鼠标放开事件
        // $('.bottom-drawer').on('mouseup', handleMouseUp)
        // 绑定鼠标移动事件
        document.addEventListener('mousemove', handleMouseMove)
        // 绑定鼠标放开事件
        document.addEventListener('mouseup', handleMouseUp)
    }

    /**
     * @param {MouseEvent} 鼠标事件
     */
    function handleMouseMove(event) {
        let dragBox = document.getElementById('drag-box');
        let dragTop = document.getElementById('drag-top');
        let dragDown = document.getElementById('drag-down');
        // let dragBox = $('.bottom-drawer');
        // let dragTop = $('#drag-top');
        // let dragDown = $('#drag-down');
        const {
            endMouseTop
        } = dragState
        // 计算鼠标移动的距离
        const distance = Math.abs(parseInt(((endMouseTop - event.clientY) * 100).toString(), 10) / 100)
        // 最小高度为60， 最大高度为第一次设置高度
        // 获取当前的文本框高度
        const topHeight = dragTop.getBoundingClientRect().height
        const downHeight = dragDown.getBoundingClientRect().height
        const boxHeight = dragBox.getBoundingClientRect().height
        // 若鼠标向上移动
        if (endMouseTop > event.clientY) {
            if (topHeight <= 65 || downHeight <= 65) return
            dragTop.style.height = topHeight - distance + 'px'
            dragDown.style.height = boxHeight - topHeight - distance - 5 + 'px'
        } else {
            // 若鼠标向下移动
            if (topHeight <= 65 || downHeight <= 65) {
                dragDown.style.height = '65px'
                return
            }
            dragTop.style.height = topHeight + distance + 'px'
            dragDown.style.height = boxHeight - topHeight - distance - 5 + 'px'
        }
        // 更新鼠标最后移动的位置（Y轴）
        dragState.endMouseTop = event.clientY
    }

    /**
     * 处理鼠标放开事件
     */
    function handleMouseUp() {
        // 移除鼠标移动事件
        document.removeEventListener('mousemove', this.handleMouseMove)
        // 移除鼠标放开事件
        document.removeEventListener('mouseup', this.handleMouseUp)
        // 允许用户选择网页中文字
        document.onselectstart = null
        // 允许用户拖动元素
        document.ondragstart = null
    };

    /**
     * @function 获取节点数据并初始化zTree
     */
    function getStorageInfo() {
        let type = $('#storage_type').val();
        let params = {};
        params.offset = 0;
        params.limit = 20;
        Metronic.blockUI({
            target: '#lun_storage_tree',
            animate: true
        });
        // pAjaxRequest(params, '/api/v1/nodes', 'GET', function (res) {
        // 目前生产存储跟节点暂时无关
        let treeNode = [
            {
                icon: '/img/vm/Huawei_FusionCompute/Huawei_FusionCompute.png',
                name: 'HUAWEI OceanStor',
                nodeType: 'node',
                nodeVal: 14,
                isParent: true,
                nocheck: true,
            },
            {
                icon: '/img/vm/Huawei_FusionCompute/Huawei_FusionCompute.png',
                name: 'HUAWEI Fusion Storage',
                nodeType: 'node',
                nodeVal: 15,
                isParent: true,
                nocheck: true,
            },
            {
                icon: '',
                iconSkin: 'vm_inspur_vvdk_vcenter',
                name: 'Inspur HF18000G6',
                nodeType: 'node',
                nodeVal: 17,
                isParent: true,
                nocheck: true,
            }
        ];

        if (type != '') {
            // 类型与上面的枚举映射
            let configArr = {
                14:0,
                15:1
            };
            treeNode = treeNode[configArr[type]];
        }

        var settings = {
            check: {
                enable: true,
                nocheckInherit: false,
            },
            data: {
                simpleData: {
                    enable: true,
                },
                key: {
                    idKey: 'ip',
                    pIdKey: '',
                    rootPid: 0
                }
            },
            callback: {
                beforeClick: getChildNode,
                beforeExpand: getChildNode,
            },
            view: {
                dblClickExpand: true,
                nameIsHTML: true,
            }
        }

        zTree = $.fn.zTree.init($('#lun_storage_tree'), settings, treeNode);
        Metronic.unblockUI('#lun_storage_tree');
        // });
    }

    /**
     * @function 初始化存储树
     * @param {} treeNode
     */
    // function initStorageTree(treeNode) {

    // }

    /**
     * @function 获取节点下的存储
     * @param
     */
    function getChildNode(treeId, treeNode) {
        $('.bottom-drawer').hide();
        $('#lun_table_div .fixed-table-body').css('height', '300px');
        $('.vcenter-tree').css('height', '475px');
        if (treeNode.nodeType == 'node') {
            getNode(treeNode);
        }

        if (treeNode.nodeType == 'storage') {
            getLunStorage(treeNode.storage_uuid);
            $('#storage_data_url').empty().text(treeNode.name);
            // <span class="display-none" style="margin-left: 10px; color: rgb(74, 209, 205); font-size: 14px; display: inline;" id="copyDataUrl">副本任务15---A1_CYF_BackupServer_31_120(VMware vSphere)</span>
        }

    }

    /**
     * @function 获取节点下的可用生产存储
     * @param
     */
    function getNode(treeNode) {
        let params = {};
        params.node_uuid = treeNode.node_uuid;
        params.lun_flag = true;
        params.type = treeNode.nodeVal;
        Metronic.blockUI({
            target: '#lun_storage_tree',
            animate: true
        });
        let icon = '/img/vm/Huawei_FusionCompute/Huawei_FusionCompute.png';
        let iconSkin = '';
        if (params.type == 17) {
            iconSkin = 'vm_inspur_vvdk_vcenter';
            icon = '';
        }
        pAjaxRequest(params, '/api/v1/storages/backup', 'GET', function (res) {
            let data = res.data;
            for (let i = 0; i < data.length; i++) {
                data[i].icon = icon;
                data[i].iconSkin = iconSkin;
                // data[i].name = data[i].storage_nickname;
                data[i].pId = treeNode.ip;
                // data[i].id = data[i].storage_uuid;
                data[i].isParent = false;
                data[i].nodeType = 'storage';
            }
            zTree.removeChildNodes(treeNode);
            zTree.addNodes(treeNode, data);
            Metronic.unblockUI('#lun_storage_tree');
        })
    }

    /**
     * @function 获取存储下的Lun存储
     * @param
     */
    function getLunStorage(storage_uuid) {
        let params = {};
        params.storage_uuid = storage_uuid;
        Metronic.blockUI({
            target: '#lun_storage_tree',
            animate: true
        });

        let options = {
            vin_url: '/api/v1/storages/lun_list',
            vin_method: 'GET',
            showExport: false, //是否开启导出按钮
            showColumns: false, //是否开启列选择按钮
            vin_params: function () {
                return params;
            },
            columns: [{
                field: 'num', //字段名
                title: LANG.UI_PUBLIC_TABLE_ID,
                sortable: false, //默认可排序，禁用排序才写此项
            },
                {
                    field: 'lun_name',
                    title: LANG.UI_STORAGE_LUN_NAME,
                    formatter: function (index, row) {
                        return '<a class="snap" href="javascript:;" data-toggle="drawer" data-target="#snap_drawer" aria-haspopup="true" aria-expanded="false"> ' + row.lun_name + ' </a>'
                    },
                    events: toSnapDetail,
                    sortable: false, //默认可排序，禁用排序才写此项
                },
                {
                    field: 'total_size',
                    title: LANG.UI_JOB_TOTAL_SIZE,
                },
                {
                    field: 'free_size',
                    title: LANG.BILLING_USE_CAPACITY,
                },
            ]
        }

        $('#lun_table').bootstrapTable('destroy');
        $('#lun_table').baseTableConfig().init(options);
        Metronic.unblockUI('#lun_storage_tree');
        // pAjaxRequest(params, '/api/v1/storages/lun_list', 'GET', function (res) {

        // });
    }

    function initSnapDetailTable(row) {
        var options = {
            vin_url: '/api/v1/storages/lun_snap',
            vin_method: 'GET',
            showExport: false, //是否开启导出按钮
            showColumns: false, //是否开启列选择按钮
            vin_params: function () {
                let params = {};
                params.storage_uuid = row.storage_uuid;
                params.parent_id = row.lun_uuid;
                return params;
            },
            columns: [{
                field: 'num', //字段名
                title: LANG.UI_PUBLIC_TABLE_ID,
                sortable: false, //默认可排序，禁用排序才写此项
            },
                {
                    field: 'snapshot_name',
                    title: LANG.UI_STORAGE_LUN_SNAP_SHOT_NAME,
                    sortable: false, //默认可排序，禁用排序才写此项
                },
                {
                    field: 'total_size',
                    title: LANG.UI_STORAGE_LUN_SNAP_SHOT_CAPACITY,
                },
                {
                    field: 'free_size',
                    title: LANG.UI_STORAGE_LUN_SNAP_SHOT_ALLOC_CAPACITY,
                },
                {
                    field: 'create_time',
                    title: LANG.UI_STORAGE_LUN_CREATE_TIME,
                },
                {
                    field: 'status',
                    title: LANG.UI_PUBLIC_STATUS,
                    formatter: function (index, row) {
                        switch (row.status) {
                            case 0:
                                return '<span class="label label-sm label-default "> ' + LANG.UI_PUBLIC_UNKNOWN + ' </span>';
                                break;
                            case 1:
                                return '<span class="label label-sm label-success "> ' + LANG.UI_STORAGE_LUN_STATUS_ACTIVE + '  </span>';
                                break;
                            case 2:
                                return '<span class="label label-sm label-default "> ' + LANG.UI_STORAGE_LUN_STATUS_NOT_ACTIVE + ' </span>';
                                break;
                            case 3:
                                return '<span class="label label-sm label-info "> ' + LANG.UI_STORAGE_LUN_STATUS_ROLL_BACK + ' </span>';
                                break;
                            case 3:
                                return '<span class="label label-sm label-info "> ' + LANG.UI_STORAGE_LUN_STATUS_CREATING + ' </span>';
                                break;
                        }
                    }
                },
            ]
        }

        $('#lun_snap_table').bootstrapTable('destroy');
        $('#lun_snap_table').baseTableConfig().init(options);
    }

    var toSnapDetail = {
        'click .snap': function (event, value, row, index) {
            // event.preventDefault();
            // event.stopPropagation();
            $('.bottom-drawer').show();
            $('#lun_table_div .fixed-table-body').css('height', '230px');
            $('.vcenter-tree').css('height', '310px');
            initSnapDetailTable(row);
        }
    }



    return {
        init: function () {
            //main function
            getStorageInfo();
            initListeners();
        }
    }
}();


$(document).ready(function () {
    lunStorageDetail.init();
});