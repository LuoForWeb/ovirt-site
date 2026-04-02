/**
 * 指定虚拟机/实例恢复的恢复目标组件
 */
(($, Metronic, UIToastr, LANG) => {
    let zTreeHC;
    let sub_module_type = CONF.VM_SUB_MODULE.PRIVATE_CLOUD;
    let hypervisorType;
    let timepointUuid; // 当前处理的时间点uuid
    let pointsDetail;
    let nodeLoaded = []; // 记录已加载过的虚拟化中心
    let selectedVm = []; // 记录已选择的目标虚拟机 [{"timepoint_uuid":"", "platform_uuid":"", "vm_uuid":""}]

    // 返回的数据
    let targetData;

    // 初始化所需参数
    const initParams = (params) => {
        hypervisorType = params.hypervisorType;
        timepointUuid = params.timepointUuid;
        pointsDetail = params.pointsDetail;
    }

    // 注册事件
    const initListeners = () => {
        $(`#recover-target-submit`).on('click', () => {
            if (!targetData.vmUuid) {
                return;
            }
            // 保存已选择的目标虚拟机，有的话先删除再加
            $.each(selectedVm, (i, v) => {
                if (v.timepoint_uuid === timepointUuid) {
                    selectedVm.splice(i, 1);
                }
            });
            selectedVm.push({timepoint_uuid: timepointUuid, platform_uuid: targetData.vcUuid, vm_uuid: targetData.vmUuid});
        });
    }

    // 初始化恢复目标的树
    const initTree = () => {
        let cloudFlag = false;
        let cloudType = '';
        if (CONF.VM_SUB_MODULE.PRIVATE_CLOUD === sub_module_type) {
            cloudFlag = true;
            cloudType = 'private';
        }
        pAjaxRequest({display_mode: 1, cloud_flag: cloudFlag, cloud_type: cloudType, hypervisor_type: hypervisorType},
            "/api/v1/vm/platforms/tree", "GET", function (data) {
            setTree(data.data.info);
        }, false);
        nodeLoaded = [];
    }

    //初始化主机和集群模式
    const setTree = function (zNodes) {
        if (zNodes.length === 0) {
            $("#vmtargettips").hide();
            $("#nodatatips").show();
            $(".vm_tree_div").hide();
            return;
        } else {
            $("#vmtargettips").show();
            $("#nodatatips").hide();
            $(".vm_tree_div").show();
        }
        let setting = {
            check: {
                enable: true,
                nocheckInherit: false,
                chkboxType: {
                    "Y": "",
                    "N": ""
                },
                chkStyle: "radio",
                radioType: "all"
            },
            data: {
                simpleData: {
                    enable: true,
                    idKey: "treeId",
                    pIdKey: "pid",
                    rootPId: 0
                },
                key: {
                    title: "title"
                }
            },
            view: {
                fontCss: getFontCss,
                addDiyDom: addHoverDom,
            },
            callback: {
                beforeClick: nodeSelect,
                onCheck: (e, id, node) => {
                    if (node.checked) {
                        targetData.vcUuid = node.vcenteruuid;
                        targetData.hostUuid = node.hostuuid;
                        targetData.hostName = node.hostName;
                        targetData.region = node.parentName;
                        targetData.vmUuid = node.id;
                        targetData.vmPath = node.path;
                        targetData.vmName = node.name;
                        targetData.username = node.username;
                        targetData.password = node.password;
                        targetData.passwordDe = node.passwordDe;
                    } else {
                        targetData = {
                            vcUuid: '',
                            hostUuid: '',
                            hostName: '',
                            region: '',
                            vmUuid: '',
                            vmPath: '',
                            vmName: '',
                            username: '',
                            password: '',
                            passwordDe: '',
                        };
                    }
                    if ('vm' === node.eventtype) {
                        $(`#verify_pwd`).show(); // 显示平台密码验证
                    }
                }
            }
        };
        //拿到备份节点
        zTreeHC = $.fn.zTree.init($("#vm_tree_hc"), setting, zNodes);
    }

    const getFontCss = (treeId, treeNode) => {
        let css = {color: "#333", "font-weight": "normal"};
        if (!!treeNode.inbackup) {
            css = {color: "green", "font-weight": "bold"};
        }
        return css;
    }

    //添加虚拟化中心鼠标指上去事件
    const addHoverDom = function(treeId, treeNode) {
        let nodeID = escapeJquery(treeNode.id);
        let nodeTID = escapeJquery(treeNode.tId);

        // 添加vcenter的操作按钮
        if (treeNode.platform_flag) {
            let aObj = $("#" + nodeTID + "_a");
            if ($("#diyHref_" + nodeTID + nodeID + "_1").length > 0) return;
            let langExpandAllDes = CONF.VM_SUB_MODULE.PRIVATE_CLOUD === sub_module_type ? LANG.UI_BACKUP_TREE_EXPAND_ALL_DES2 : LANG.UI_BACKUP_TREE_EXPAND_ALL_DES;
            let langCollapseAllDes = CONF.VM_SUB_MODULE.PRIVATE_CLOUD === sub_module_type ? LANG.UI_BACKUP_TREE_COLLAPSE_ALL_DES2 : LANG.UI_BACKUP_TREE_COLLAPSE_ALL_DES;
            let str = '<a id="diyHref_' + treeNode.tId + treeNode.id + '_1" title="' + LANG.UI_VCENTER_SYNC + '" class="treehref">' + LANG.UI_VCENTER_SYNC + '</a>' +
                '<a id="diyHref_' + treeNode.tId + treeNode.id + '_2" title="' + langExpandAllDes + '" class="treehref">' + LANG.UI_BACKUP_TREE_EXPAND_ALL + '</a>' +
                '<a id="diyHref_' + treeNode.tId + treeNode.id + '_3" title="' + langCollapseAllDes + '" class="treehref">' + LANG.UI_BACKUP_TREE_COLLAPSE_ALL + '</a>';
            aObj.after(str);
            let hrefRefresh = $("#diyHref_" + nodeTID + nodeID + "_1");
            let hrefExpand = $("#diyHref_" + nodeTID + nodeID + "_2");
            let hrefCollapse = $("#diyHref_" + nodeTID + nodeID + "_3");
            if (hrefRefresh) hrefRefresh.bind("click", function () {
                getSyncVcenterInfo(treeId, treeNode, true, false);
            });
            if (hrefExpand) hrefExpand.bind("click", function () {
                if (treeNode.children) {
                    $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true, true, true);
                } else {
                    getSyncVcenterInfo(treeId, treeNode, false, true);
                }
            });
            if (hrefCollapse) hrefCollapse.bind("click", function (event) {
                $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, false, true, true, false);
            });
        }

        //点击+号展开
        let nodeSwitch = $("#" + nodeTID + "_switch");
        nodeSwitch.bind("click", function(){
            if (nodeLoaded[nodeID]) return;
            if(treeNode.children){
                $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, false, true, true);
            }else{
                getSyncVcenterInfo(treeId, treeNode, false, false);
            }
            nodeLoaded[nodeID] = true;
        });
    }

    const escapeJquery = function (srcString) {
        // 转义之后的结果  
        let escapseResult = srcString.toString();
        // javascript正则表达式中的特殊字符  
        let jsSpecialChars = ["\\", "^", "$", "*", "?", ".", "+", "(", ")", "[",
            "]", "|", "{", "}"];
        // jquery中的特殊字符,不是正则表达式中的特殊字符  
        let jquerySpecialChars = ["~", "`", "@", "#", "%", "&", "=", "'", "\"",
            ":", ";", "<", ">", ",", "/"];
        for (let i = 0; i < jsSpecialChars.length; i++) {
            escapseResult = escapseResult.replace(new RegExp("\\"
                + jsSpecialChars[i], "g"), "\\"
                + jsSpecialChars[i]);
        }
        for (let i = 0; i < jquerySpecialChars.length; i++) {
            escapseResult = escapseResult.replace(new RegExp(jquerySpecialChars[i],
                "g"), "\\" + jquerySpecialChars[i]);
        }
        return escapseResult;
    }

    const nodeSelect = function (treeId, treeNode) {
        if ('vm' === treeNode.eventtype) {
            $.fn.zTree.getZTreeObj(treeId).checkNode(treeNode, !treeNode.checked, true, true);
            $(`#verify_pwd`).show(); // 显示平台密码验证
        } else {
            if (!treeNode.isParent) return;//不存在子节点不展开
            nodeExpand(treeId, treeNode);
            $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
        }
    }

    const nodeExpand = function (treeId, treeNode) {
        if (treeNode.clickshow) {
            if (treeNode.children) return true;
            getSyncVcenterInfo(treeId, treeNode, false);
        } else {
            return true;
        }
    }

    //异步获取虚拟化中心/宿主机的信息  refreshFlag是否重新刷新
    const getSyncVcenterInfo = function (treeId, treeNode, refreshFlag, expendFlag) {
        let div = "#vm_tree_div";
        let p = {
            platform_uuid: treeNode.platform_uuid.replace(/_$/, ''),
            platform_flag: treeNode.platform_flag,
            hypervisor_type: treeNode.hypervisor_type,
            display_mode: 1,
            refresh_flag: refreshFlag,
            show_vm_flag: true,
            aws_flag: false,
            allocated_vm_flag: treeNode.allocated_vm_flag ?? false,
            specific_vm_recovery_flag: true
        };
        Metronic.blockUI({target: div, animate: true});
        pAjaxRequest(p, "/api/v1/vm/platforms/vm_tree", "GET", function (data) {
            Metronic.unblockUI(div);
            //检测是否为同一事件返回
            if (zTreeHC != $.fn.zTree.getZTreeObj(treeId)) return;
            if (data.success) {
                let nodes = data.data.rows;

                // 处理已经被其他时间点选中的目标虚拟机为不可选状态
                let currentSelectedNodeId = '';
                $.each(nodes, (n, v) => {
                    if (v.type !== 7) {
                        return;
                    }
                    if (v.inrecovery) {
                        nodes[n].chkDisabled = true;
                        nodes[n].name += `(${LANG.UI_RECOVERY_TARGET_VM_OCCUPIED})`;
                        return;
                    }
                    for (let i in selectedVm) {
                        if (v.vcenteruuid === selectedVm[i].platform_uuid && v.id === selectedVm[i].vm_uuid) {
                            if (timepointUuid === selectedVm[i].timepoint_uuid) {
                                nodes[n].checked = true;
                                currentSelectedNodeId = nodes[n].id;
                            } else {
                                nodes[n].chkDisabled = true;
                                nodes[n].name += `(${LANG.UI_RECOVERY_TARGET_VM_OCCUPIED})`;
                            }
                            break;
                        }
                    }
                });

                let allNodes = zTreeHC.getCheckedNodes(true);
                if (refreshFlag && allNodes.length != 0 && allNodes[0].hypervisor_type == treeNode.hypervisor_type && allNodes[0].platform_uuid == treeNode.platform_uuid) {
                    $('.popover.in').remove();
                    $('.addVMList').empty();
                    zTreeHC.checkAllNodes(false);
                }
                zTreeHC.removeChildNodes(treeNode);
                zTreeHC.addNodes(treeNode, nodes, true);
                if (currentSelectedNodeId) {
                    // 触发当前已选的在初始化后重新选中
                    let currentSelectedNode = zTreeHC.getNodeByParam("id", currentSelectedNodeId);
                    zTreeHC.checkNode(currentSelectedNode, currentSelectedNode.checked, true, true);
                }
                if (nodes.length === 0) {
                    $("#vmtargettips").hide();
                    $("#novmdatatips").show();
                }
                if (expendFlag) {
                    zTreeHC.expandNode(treeNode, true, true, true);
                }
            } else {
                operateResponseList(data);
            }
        }, true);
    }

    $.fn.specificVmRecovery = {
        initTarget: (params = {}) => {
            targetData = {
                vcUuid: '',
                hostUuid: '',
                hostName: '',
                region: '',
                vmUuid: '',
                vmPath: '',
                vmName: '',
                username: '',
                password: '',
                passwordDe: '',
            }
            initParams(params);
            initListeners();
            initTree();
            // 隐藏密码输入框
            $(`#verify_pwd`).hide();
        },
        getTargetData: () => {
            return $.extend(targetData, {timepointUuid: timepointUuid});
        },
        cleanCache: () => {
            // 清除组件内缓存数据
            nodeLoaded = [];
            selectedVm = [];
        }
    }
})(jQuery, Metronic, UIToastr, LANG);