var vmRecoverConfigs = function () {
    return function (){
        var vmRecoveryhostTree;
        var _VMNAMEREG = new RegExp("[ \n\r\t\f`~!@#$^&*()=|{}':;',\\[\\].<>/?~！@#￥……&*（）&;|{}【】‘；：”“'。，、？+-]");
        var _hypervisor;		// 恢复目标虚拟化类型
        var vmuuidList = [];
        var _HOSTSTORAGE = [];	//目的宿主机存储信息
        var point; // 选择的时间点信息
        var showDriver = true; // 是否需要驱动检测
        var showDriverTemp; // 是否需要驱动检测-临时存储
        var jobType = 1;// 任务类型 默认是跨平台恢复 2瞬时恢复 3迁移
        var params = {}; // 获取无代理的配置参数
        var chooseNode = ''; // 选择的虚拟化节点类型
        var isLoading = false; // 判断是否已经加载完了信息
        var isPlatformType = false; // 判断是否异构平台并且未驱动检测通过
        var isSupportOs = false; // 判断是否不支持替换驱动
        // 获取虚拟化类型的时候需要判断，
        // 瞬时恢复的时候：源是有代理，那么虚拟化类型只能是容灾演练平台；源是无代理，那么不能恢复到有代理
        // 跨平台恢复的时候：源是无代理，那么获取的虚拟化类型不能包括和源的一致
        var pointType = 1; // 时间点来源类型 默认无代理 2操作系统（有代理）,3实时
        var driverCheck = []; // 驱动检测结果存储
        var sub_module_type = 1; // 1是虚拟机 2是私有云（openstack） 2是公有云
        var policy_list; //xhere块存储策略
        var currentPlatformUuid = ""; //当前恢复目标虚拟化中心
        var vmPlugnInfoData;
        var _vcenterType;
        var source_list = []; // 虚拟机的cp和网卡列表
        var source_list_old = []; // 虚拟机的默认的cp和网卡列表
        var source_list_check = false; // 驱动检测的时候虚拟机的cp和网卡列表
        var passCheck = []; // 密码验证结果存储
        var hostCpuArch = 0; // 恢复目标主机的cpu架构
        var vmNameLimit = {}; //虚拟机名称长度限制
        var showType = 1; // 返回虚拟化类型默认1虚拟机 2私有云 3公有云，4容灾演练平台
        var instantTaskUuid; // 瞬时恢复任务uuid

        //事件监听
        var initListener = function(){
            //跳转到添加虚拟化中心
            $('#toaddvcenter').on('click',function(){
                LOCATION('./content/vm/add_vcenter.php', 'add_vcenter');
            });
            //选择存储用途复选框
            $('#continue_driver_fail').find('.icheck').on('ifClicked', dirverContinueClick);

            //选择批量配置虚拟机
            $('#vmssetting').on('switchChange.bootstrapSwitch',function(){
                if(this.checked){
                    $(".vmssettingdiv").show();
                    $(`.configsdiv`).find(`select[name=hoststorage]`).val($(`#vmsstorage`).val()).selectpicker('refresh');
                    $(`.configsdiv`).find(`select[name=hoststorage]`).trigger('change');
                    $(`.configsdiv`).find(`select[name=hostnetwork]`).val($(`#vmsnetwork`).val()).selectpicker('refresh');
                    $(`.configsdiv`).find(`select[name=hostnetwork]`).trigger('change');
                }else{
                    $(".vmssettingdiv").hide();
                }
            })

            //批量配置虚拟机选择存储
            $('#vmsstorage').on('change', function(){
                $(`.configsdiv`).find(`select[name=hoststorage]`).val($(this).val()).selectpicker('refresh');
                $(`.configsdiv`).find(`select[name=hoststorage]`).trigger('change');
            })

            //批量配置虚拟机选择网络
            $('#vmsnetwork').on('change', function(){
                $('select[name=hostnetwork]').val($(this).val()).selectpicker('refresh');
            })

            //批量配置虚拟机选择可用域
            $('#vmUsedomain').on('change', function(){
                $('select[name=usedomain]').val($(this).val());
            })

            //批量配置虚拟机恢复完成后开机
            $('#powercheck').on('switchChange.bootstrapSwitch',function(){
                $('input[name=vmpower]').bootstrapSwitch('state', this.checked);
            })
        }
        var dirverContinueClick = function(event){
            var mode = $(this).data('mode');
            if(event.target.checked){
                //如果是取消选中
                $('#continue_driver_fail').find('input').iCheck("uncheck");
            }else{
                $('#continue_driver_fail').find('input').iCheck("uncheck");
                $('#continue_driver_fail').find('input[data-mode='+ mode +']').iCheck("check");
            }
        }

        //初始化宿主机树
        function initHostTree(){
            // 默认就直接取时间点里面的所有
            var hypervisorArr = [];
            for (var kk in point.point_info) {
                var hypervisor = point.point_info[kk].hypervisor == 0 ? 1001 : point.point_info[kk].hypervisor;
                hypervisorArr.push(hypervisor);
            }
            var show_types = showType;
            if (showType == 4) {
                show_types = 1;
            }
            var data = {hypervisor_type: hypervisorArr.join(','), sub_module_type: show_types, emd_flag: false};
            if (showType == 4) {
                data.emd_flag = true; // 返回所有虚拟化类型包括容灾演练主机
                data.target_hypervisor_type = [108]; // 只返回容灾演练平台的
            }
            if (jobType == 2) {
                // 瞬时恢复
                data.instant_flag = true;
                //data.emd_flag = true; // 返回所有虚拟化类型包括容灾演练主机
                // data.hypervisor_type = point.point_info[0].hypervisor; // 备份时间点的虚拟化类型
            } else if (jobType == 1 && pointType == 1) {
                // 跨平台恢复,并且源是无代理，需要获取选择的时间点的虚拟化类型
                // 需要排除时间点的虚拟化类型 需要获取时间的所有的虚拟化类型...
                var hypervisor_type = [];
                for (var j in point.point_info) {
                    hypervisor_type.push(point.point_info[j].hypervisor);
                }
                data.exclude_hypervisor_type = hypervisor_type;
            } else if (jobType == 3){
                var hypervisor_new = point.point_info[0].hypervisor_new; // 瞬时恢复任务的虚拟化类型
                var hypervisor = point.point_info[0].hypervisor; // 备份时间点的虚拟化类型
                data.hypervisor_type = hypervisor_new == 0 ? 1001 : hypervisor_new;
                data.motion_flag = true;
                // 迁移任务：
                var target_hypervisor_type = [];
                if (pointType == 1) {
                    // 一：时间点是无代理
                    if (hypervisor_new != hypervisor) {
                        // 备份点和瞬时恢复的不一致：
                        if (hypervisor_new == 108) {
                            // 1如果瞬时恢复到内嵌，那么目标只能是和备份点的一致
                            target_hypervisor_type = [hypervisor];
                        } else {
                            // 2如果不是内嵌，那么目标是备份点或瞬时恢复的类型
                            target_hypervisor_type = [hypervisor, hypervisor_new];
                        }
                    } else {
                        // 备份点和瞬时恢复的一致：那么目标只能是备份点的一致
                        target_hypervisor_type = [hypervisor];
                    }
                } else {
                    // 二：时间点是有代理
                    // 1.瞬时恢复的是内嵌,那么就只能是有代理
                    if (hypervisor_new != 108) {
                        // 2.瞬时恢复的不是内嵌，那么目标只能是和瞬时恢复的保持一致
                        target_hypervisor_type = [hypervisor_new];
                    } else {
                        // 如果是内嵌。那么就不能迁移恢复到无代理
                    }
                }
                if (point.point_info[0].hypervisor_new == 47) {
                    // scp的目标虚拟化类型只能是scp
                    target_hypervisor_type = [47];
                }

                data.target_hypervisor_type = target_hypervisor_type;
            }

            if (showType == 2) {
                // 私有云
                $('#source_title2').show();
                $('#source_title').hide();
                $('#source_title3').hide();

                $('#source_desc2').show();
                $('#source_desc').hide();
                $('#source_desc3').hide();

                $('#source_titles2').show();
                $('#source_titles').hide();
                $('#source_titles3').hide();

                $('#show_tips_2').show();
                $('#show_tips_3').hide();
                $('#show_tips_').hide();
                $('#awsconfigs').hide();
            } else if (showType == 3) {
                // 公有云
                $('#source_title3').show();
                $('#source_title').hide();
                $('#source_title2').hide();

                $('#source_desc3').show();
                $('#source_desc').hide();
                $('#source_desc2').hide();

                $('#source_titles3').show();
                $('#source_titles').hide();
                $('#source_titles2').hide();

                $('#show_tips_3').show();
                $('#show_tips_2').hide();
                $('#show_tips_').hide();
            } else {
                // 虚拟化 和 容灾演练平台
                $('#source_title').show();
                $('#source_title2').hide();
                $('#source_title3').hide();

                $('#source_desc').show();
                $('#source_desc2').hide();
                $('#source_desc3').hide();

                $('#source_titles').show();
                $('#source_titles2').hide();
                $('#source_titles3').hide();

                $('#show_tips_').show();
                $('#show_tips_3').hide();
                $('#show_tips_2').hide();
                $('#awsconfigs').hide();
            }
            pAjaxRequest(data, '/api/v1/vm/jobs/restore/platforms', 'GET', setHostTree)
        }
        //初始化宿主机树
        var setHostTree = function(datas){
            var zNodes = datas.data;
            if(!checkHostNodeInfo(zNodes, 'selectHost')) return;
            var setting = {
                check: {
                    enable: true,
                    nocheckInherit: false
                },
                data: {
                    simpleData: {
                        enable: true
                    }
                },
                view: {
                    removeHoverDom: removeHoverDom,
                },
                callback: {
                    beforeClick: hostNodeSelect,
                    onCheck: hostOnCheck,
                    beforeExpand: hostNodeExpand,
                }
            };
            vmRecoveryhostTree = $.fn.zTree.init($("#host_tree"), setting, zNodes.rows);
            chooseNode = '';
        };
        // 迁移的时候scp处理逻辑
        var motionScp = function (){
            if (point.point_info[0].hypervisor_new == 47 && jobType == 3) {
                selectAndCheckNodeById(point.point_info[0].host_uuid);
            }
        }

        function backOpen(nodes, treeObj){
            if (!nodes || !Array.isArray(nodes)) {
                return; // 如果 nodes 不存在或不是数组，则直接返回
            }
            // 保存当前的 beforeExpand 回调函数
            var originalBeforeExpand = treeObj.setting.callback.beforeExpand;

            // 暂时移除 beforeExpand 回调函数
            treeObj.setting.callback.beforeExpand = null;
            for (var j in nodes) {
                var node = nodes[j];
                if (
                    (node.vcuuid == point.point_info[0].target_uuid || node.id == point.point_info[0].target_uuid) &&
                    node.children && node.children.length > 0
                ) {
                    if (node.children && node.children.length > 0) {
                        treeObj.expandNode(node, true, false, false);
                        backOpen(node.children, treeObj);
                    }
                }
            }

            // 恢复原来的 beforeExpand 回调函数
            treeObj.setting.callback.beforeExpand = originalBeforeExpand;
        }
        // 查找并选中指定 ID 的节点
        function selectAndCheckNodeById(nodeId) {
            var treeObj = $.fn.zTree.getZTreeObj("host_tree");
            var node = treeObj.getNodeByParam("id", nodeId);
            if (node) {
                // 选中并触发 onCheck 事件
                treeObj.checkNode(node, true, true);
                hostOnCheck(event, nodeId, node);

                // 禁用其他所有节点
                disableOtherNodesExcept(treeObj, node);

                backOpens();
            }
        }

        function backOpens() {
            var treeObj = $.fn.zTree.getZTreeObj('host_tree');
            var nodes = treeObj.getNodes();
            backOpen(nodes, treeObj);
        }

        // 禁用其他所有节点
        function disableOtherNodesExcept(treeObj, exceptNode) {
            var nodes = treeObj.transformToArray(treeObj.getNodes());
            for (var i = 0; i < nodes.length; i++) {
                if (nodes[i].id !== exceptNode.id) {
                    treeObj.setChkDisabled(nodes[i], true, false, false);
                }
            }
        }

        //选择宿主机/集群/区域节点事件绑定
        var hostNodeSelect = function(treeId, treeNode, clickFlag){
            if(2 == treeNode.type || 3 == treeNode.type){
                vmRecoveryhostTree.checkNode(treeNode, !treeNode.checked, false, true);
            }else{
                hostNodeExpand(treeId, treeNode);
                vmRecoveryhostTree.expandNode(treeNode, true);
            }
            addHoverDom(treeId, treeNode);
        }
        var hostOnCheck = function(e, id, node){
            var allNodes = vmRecoveryhostTree.getCheckedNodes(true);
            for(var i = 0; i < allNodes.length; i++){
                vmRecoveryhostTree.checkNode(allNodes[i], false, false, false);
            }
            vmRecoveryhostTree.checkNode(node, true, false, false);
            $('#show_instant_msg').hide();

            if (jobType == 2 && CONF.VMTYPE_GROUP.OPENSTACK.includes(node.hypervisor)) {
                $('#show_instant_msg').show();
            }

            if (CONF.VMTYPE_GROUP.PUBLICCLOUD.includes(node.hypervisor)) {
                // todo
                let timepoints_uuids = [];
                $.each(point.point_info, function (i, v) {
                    timepoints_uuids.push(v.timepoint_uuid);
                });
                $.fn.initAwsRecoveryConfig({
                    source_hypervisor: point.point_info[0].hypervisor,
                    target_hypervisor: node.hypervisor,
                    timepoint_uuids: timepoints_uuids,
                    platform_uuid: node.vcuuid,
                    recover_type: 1, // 实例恢复
                });
                $('#vmconfigs').hide();
                $('#awsconfigs').show();
                $('#unifysettingdiv').hide();
                isLoading = true;
            } else {
                $('#awsconfigs').hide();
                // 跨平台恢复时显示统一配置
                if (jobType == 1) {
                    $('#unifysettingdiv').show();
                }
                // 初始化虚拟机配置信息
                getHostCommonInfo(node);
                initVMconfigV2(node, point);
            }


            if (jobType == 2) {
                if (point.point_info[0].hypervisor == node.hypervisor) {
                    // 如果是瞬时恢复任务。并且源和目标是一样的虚拟化类型，就不需要驱动检测
                    showDriverTemp = showDriver;
                    showDriver = false;
                }

                if (0 == point.point_info[0].hypervisor && 108 == node.hypervisor) {
                    // 整机瞬时恢复到内嵌先不显示驱动检测
                    showDriver = false;
                }
            } else {
                if (showDriverTemp != undefined) {
                    showDriver = showDriverTemp;
                }
            }
            isPlatformType = false;
            isSupportOs = false;

            if (node.id != chooseNode.id) {
                // $('#vmconfigs').hide();
                $('#accordionvm').html('');
                $('#driverCheck_vm').html('');
                $('#driverCheck_vm_button').hide();
                $('#driverCheckFailContinue').hide();
            }

            if (showDriver) {
                driverCheck = [];
                $('#driverCheck_vm').parent().show();
                // 驱动检测和初始化虚拟机配置
                initDriver(node);
            } else {
                // 不需要驱动检测
                $('#driverCheck_vm').parent().hide();
            }

            chooseNode = node;
            chooseNode.checkedFlag = true; // 已选中
            vcenterNetworkFlag = false;
            isExpend = true;
        }

        // 获取恢复目标主机信息：cpu架构
        var getHostCommonInfo = function (node) {
            if (CONF.VM_TYPE.HYPERV == node.hypervisor) {
                // hyperv获取时会超时报错暂不处理
                return;
            }
            let p = {
                hypervisor_type: node.hypervisor,
                platform_uuid: node.vcuuid,
                host_uuid: node.groupuuid ?? node.id,
                region: CONF.VMTYPE_GROUP.OPENSTACK.includes(node.hypervisor) ? node.name : '', // 区域名
            }
            Metronic.blockUI({target: '#show_vmRecovery_next',animate: true});
            pAjaxRequest(p, "/api/v1/vm/host/common_info", "GET", function (d) {
                Metronic.unblockUI('#show_vmRecovery_next');
                if (d.success) {
                    hostCpuArch = d.message.cpu_arch;
                }
            });
        }

        //添加虚拟化中心鼠标指上去事件
        var addHoverDom = function(treeId, treeNode) {
            if(1 != treeNode.type) return;
            var nodeID = escapeJquery(treeNode.id);
            var nodeTID = escapeJquery(treeNode.tId);
            var aObj = $("#" + nodeTID + "_a");
            if ($("#diyHref_" + nodeTID + nodeID + "_2").length>0) return;

            var hypervisor = treeNode.hypervisor;
            var str = "";
            if (108 == hypervisor) {
                // 没有同步按钮
                str = "";
            } else {
                str = '<a id="diyHref_' + treeNode.tId + treeNode.id + '_1" title="' + LANG.UI_BACKUP_TREE_REFRESH_DES + '" class="treehref">' + LANG.UI_VCENTER_SYNC + '</a>';
            }

            str += '<a id="diyHref_' + treeNode.tId + treeNode.id + '_2" title="' + LANG.UI_BACKUP_TREE_EXPAND_ALL_DES + '" class="treehref">' + LANG.UI_BACKUP_TREE_EXPAND_ALL + '</a>' +
                '<a id="diyHref_' + treeNode.tId + treeNode.id + '_3" title="' + LANG.UI_BACKUP_TREE_COLLAPSE_ALL_DES + '" class="treehref">' + LANG.UI_BACKUP_TREE_COLLAPSE_ALL + '</a>';
            aObj.after(str);

            var hrefRefresh = $("#diyHref_" + nodeTID + nodeID + "_1");
            var hrefExpand = $("#diyHref_" + nodeTID + nodeID + "_2");
            var hrefCollapse = $("#diyHref_" + nodeTID + nodeID + "_3");

            if (hrefRefresh) hrefRefresh.bind("click", function(){
                if(CONF.VMTYPE_GROUP.OPENSTACK.includes(treeNode.hypervisor)){
                    vcenterRefresh(treeId, treeNode);
                }else{
                    hostRefresh(treeId, treeNode);
                }
            });
            if (hrefExpand) hrefExpand.bind("click", function(){
                $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true, true, true);
            });
            if (hrefCollapse) hrefCollapse.bind("click", function(event){
                $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, false, true, true, false);
            });
        };
        //选择宿主机节点展开事件绑定
        var hostRefresh = function(treeId, treeNode){
            if(1 == treeNode.type){
                let p = {
                    platform_uuid: treeNode.id,
                    pid: treeNode.hypervisor,
                    nocheck_flag: false,
                    refresh_flag: true
                }
                Metronic.blockUI({target: '#host_tree',animate: true});
                pAjaxRequest(p, "/api/v1/vm/jobs/restore/hosts", "GET", function (d) {
                    Metronic.unblockUI('#host_tree');
                    if (d.success) {
                        $.fn.zTree.getZTreeObj(treeId).removeChildNodes(treeNode);
                        $.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, d.data.rows, true);
                        $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
                        //隐藏隐藏项目
                        $('.proxySettingsDiv').hide();
                        isExpend = false;
                        chooseNode.checkedFlag = false;
                    } else {
                        operateResponseList(d);
                    }
                }, true);
            }else{
                return true;
            }
        }
        //恢复目的分组展开
        var vcenterRefresh = function(treeId, treeNode){
            if(1 == treeNode.type){
                let p = {
                    platform_uuid: treeNode.id,
                    hypervisor_type: treeNode.hypervisor,
                }
                Metronic.blockUI({target: '#host_tree',animate: true});
                pAjaxRequest(p, "/api/v1/vm/platforms/groups", "GET", function (d) {
                    Metronic.unblockUI('#host_tree');
                    if (d.success) {
                        $.fn.zTree.getZTreeObj(treeId).removeChildNodes(treeNode);
                        $.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, d.data.rows, true);
                        $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
                        chooseNode.checkedFlag = false;
                    } else {
                        operateResponseList(d);
                    }
                }, true);
            }else{
                return true;
            }
        }
        //恢复目标树统一展开
        var hostNodeExpand = function(treeId, treeNode){
            var hypervisor = treeNode.hypervisor;
            if (CONF.VMTYPE_GROUP.OPENSTACK.includes(hypervisor)) {
                //openstack按租户显示恢复目标
                return vcenterNodeExpand(treeId, treeNode);
            } else {
                //按宿主机显示
                return getSyncHostNode(treeId, treeNode);
            }
        }
        //恢复目的分组展开
        var vcenterNodeExpand = function(treeId, treeNode){
            if(1 == treeNode.type){
                if(treeNode.children) return true;
                let p = {
                    hypervisor_type: treeNode.hypervisor,
                    platform_uuid: treeNode.id
                }
                Metronic.blockUI({target: '#host_tree',animate: true});
                pAjaxRequest(p, "/api/v1/vm/platforms/groups", "GET", function (d) {
                    Metronic.unblockUI('#host_tree');
                    if (d.success) {
                        //success
                        vmRecoveryhostTree.addNodes(treeNode, d.data.rows, true);
                    } else {
                        operateResponseList(d);
                    }
                })
            }else{
                return true;
            }
        }
        //选择宿主机节点展开事件绑定
        var getSyncHostNode = function(treeId, treeNode){
            if(1 == treeNode.type){
                if(treeNode.children) return true;
                var data = {
                    platform_uuid: treeNode.id,
                    pid: treeNode.hypervisor,
                    nocheck_flag: false,
                    refresh_flag: false
                };
                Metronic.blockUI({target: '#host_tree',animate: true});
                pAjaxRequest(data, '/api/v1/vm/jobs/restore/hosts', 'GET', function (d) {
                    Metronic.unblockUI('#host_tree');
                    if (d.success) {
                        // 处理下到内嵌的，过滤掉其它节点的
                        if (data.pid == 108) {
                            var datas = d.data.rows;
                            for (var k in datas) {
                                if (datas[k].id == point.node_uuid) {
                                    d.data.rows = [datas[k]];
                                    break;
                                }
                            }
                        }
                        vmRecoveryhostTree.addNodes(treeNode, d.data.rows, true);
                        motionScp();
                    } else {
                        operateResponseList(d);
                    }
                }, false)
            }else{
                return true;
            }
        }
        //检查没有宿主机切换提示信息
        var checkHostNodeInfo = function(zNodes, id){
            if(zNodes.rows.length == 0){
                $("#nohosttips").show();
                $('#'+id).hide();
                return false;
            }else{
                $("#nohosttips").hide();
                $('#'+id).show();
                return true;
            }
        }
        //添加虚拟化中心鼠标移除事件
        var removeHoverDom = function(treeId, treeNode) {
            if(1 != treeNode.type) return ;
            var nodeID = escapeJquery(treeNode.id);
            var nodeTID = escapeJquery(treeNode.tId);
        };
        var escapeJquery = function(srcString){
            // 转义之后的结果
            var escapseResult = srcString.toString();
            // javascript正则表达式中的特殊字符
            var jsSpecialChars = ["\\", "^", "$", "*", "?", ".", "+", "(", ")", "[",
                "]", "|", "{", "}"];
            // jquery中的特殊字符,不是正则表达式中的特殊字符
            var jquerySpecialChars = ["~", "`", "@", "#", "%", "&", "=", "'", "\"",
                ":", ";", "<", ">", ",", "/"];
            for (var i = 0; i < jsSpecialChars.length; i++) {
                escapseResult = escapseResult.replace(new RegExp("\\"
                    + jsSpecialChars[i], "g"), "\\"
                    + jsSpecialChars[i]);
            }
            for (var i = 0; i < jquerySpecialChars.length; i++) {
                escapseResult = escapseResult.replace(new RegExp(jquerySpecialChars[i],
                    "g"), "\\" + jquerySpecialChars[i]);
            }
            return escapseResult;
        }

        // 驱动检测初始化
        var initDriver = function (node) {
            // 驱动检测
            $('#driverCheck_vm').show();
            $('#driverCheck_vm_button').show();
            var vm_info = {};
            vm_info.vm_type = node.hypervisor;
            vm_info.vm_uuid = node.vcuuid;
            if(CONF.VMTYPE_GROUP.OPENSTACK.includes(node.hypervisor)){
                vm_info.host_uuid = node.groupuuid;
            }
            getDriver(vm_info);
            $('#driverCheck_vm_button_checkDriver').off().on('click', function (){
                // 绑定驱动检测事件
                $('#driverCheck_vm').hide();
                $('#driverCheckFailContinue').hide();

                var title = LANG.UI_DRIVER_CHECK;
                var msg1 = LANG.UI_PLATFORM_RECOVERY_CPU_TIPS; // 请选择宿主机：s%对应的CPU架构
                var msg2 = LANG.UI_PLATFORM_RECOVERY_OS_TYPE_TIPS; // 请选择宿主机：s%对应的操作系统类型
                var msg3 = LANG.UI_PLATFORM_RECOVERY_OS_VSERSION_TIPS; // 请选择宿主机：s%对应的操作系统版本
                // 获取时间点信息
                var checkss = true;
                if (!CONF.VMTYPE_GROUP.PUBLICCLOUD.includes(vm_info.vm_type)) {
                    for (var j in point.point_info) {
                        if (!checkss) {
                            return false;
                        }
                        var os_arch = getVmConfig(point.point_info[j].timepoint_uuid,'cpu_type');
                        if (parseInt(os_arch) == 0) {
                            let newString = msg1.replace(/s%/, point.point_info[j].host_name);
                            UIToastr.showWarning(title, newString);
                            checkss = false;
                            return false;
                            break;
                        }
                        var os_type = getVmConfig(point.point_info[j].timepoint_uuid,'os_type');
                        if (parseInt(os_type) == 0) {
                            let newString = msg2.replace(/s%/, point.point_info[j].host_name);
                            UIToastr.showWarning(title, newString);
                            checkss = false;
                            return false;
                            break;
                        }
                        var os_version = getVmConfig(point.point_info[j].timepoint_uuid,'os_version');
                        if (parseInt(os_version) == 0) {
                            let newString = msg3.replace(/s%/, point.point_info[j].host_name);
                            UIToastr.showWarning(title, newString);
                            checkss = false;
                            return false;
                            break;
                        }
                        var reset_hostname = $(`.configsdiv[data-timepointuuid=${point.point_info[j].timepoint_uuid}]`).find(`input[name='reset_hostname']`).val();
                        var host_name_limit = {
                            "Linux":{
                                'len': '63',
                                'limit':'^(?![0-9]+$)[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?$',
                                'msg':LANG.UI_MACHINE_OS_HOSTNAME_MSG_63
                            },
                            "Windows":{
                                'len': '15',
                                'limit':'^(?![0-9]+$)(?!(CON|AUX|COM[1-9]|LPT[1-9]|PRN|NUL)$)[A-Za-z0-9](?:[A-Za-z0-9-]{0,13}[A-Za-z0-9])?$',
                                'msg':LANG.UI_MACHINE_OS_HOSTNAME_MSG_15
                            }
                        }

                        var reset_hostname_switch =  $(`.configsdiv[data-timepointuuid=${point.point_info[j].timepoint_uuid}]`).find(`input[name='reset_hostname_input']`).bootstrapSwitch('state');

                        if(reset_hostname_switch && host_name_limit[os_type] != undefined){
                            let hostReg = new RegExp(host_name_limit[os_type].limit);
                            if(reset_hostname.length> host_name_limit[os_type].len){
                                UIToastr.showWarning(LANG.UI_OS_DETAILS_HOST_NAME, LANG.UI_OS_DETAILS_HOST_NAME + host_name_limit[os_type].msg);
                                return false;
                            }
                            if(reset_hostname == "" || !hostReg.test(reset_hostname)){
                                UIToastr.showWarning(LANG.UI_OS_DETAILS_HOST_NAME, LANG.UI_MACHINE_OS_HOSTNAME_TIPS);
                                return false;
                            }
                        }

                    }
                }

                if (checkss) {
                    $('#driverCheck_vm').show();
                    $.fn.driverCheck.check($('#driverCheck_vm'));
                }
            });
        }
        // 获取虚拟机的一些配置信息
        function getVmConfig(timepoint_uuid, field) {
            return $(`.configsdiv[data-timepointuuid=${timepoint_uuid}]`).find(`select[name=${field}]`).val();
        }

        // 获取公有云的一些配置信息
        function getAwsConfig(timepoint_uuid, field) {
            let allConfig = $.fn.getAwsRecoveryConfig().instance_configs;
            let config;
            allConfig.map(item => {
                if (timepoint_uuid === item.timepoint_uuid) {
                    config = item;
                    return true;
                }
            });
            console.log(field)
            console.log(config[field])
            return config[field];
        }

        function capitalize(str) {
            if (!str) return str;
            return str.charAt(0).toUpperCase() + str.slice(1);
        }

        // 这里改为动态的去获取虚拟机配置里面的磁盘和网线的类型对应值
        function getDiskAndNetwork(vm_info) {
            source_list = [];
            var moduleTypeArr = {
                1:CONF.MODULE_TYPE.VM,
                2:CONF.MODULE_TYPE.OS,
                3:CONF.MODULE_TYPE.VOL_CDP,
            };
            if (CONF.VMTYPE_GROUP.PUBLICCLOUD.includes(chooseNode.hypervisor)) {
                // 公有云获取时间点信息
                const cpuArchValue = {
                    x86: 1,
                    x86_64: 2,
                    ARM: 3,
                    ARM_64: 4
                }
                let disk_bus = [];
                let net_bus = [];
                switch (chooseNode.hypervisor) {
                    case CONF.VM_TYPE.AWS:
                        // aws：默认网络驱动 ena ；默认磁盘驱动 aws nvme
                        disk_bus = 37;
                        net_bus = 36;
                        break;
                    case CONF.VM_TYPE.HUAWEICLOUD:
                        // 华为云：默认网络驱动和磁盘驱动：virtio
                        disk_bus = 4;
                        net_bus = 4;
                        break;
                    default:
                        break;
                }
                for (let j in point.point_info) {
                    source_list.push({
                        name: point.show_str[j].replace(/<i[^>]*>.*?<\/i>|<br\s*\/?>/gi, ''),
                        timepoint_uuid:point.point_info[j].timepoint_uuid,
                        module_type: moduleTypeArr[pointType],
                        source_hypervisor_disk_bus_list: [],
                        source_hypervisor_net_bus_list: [],
                        target_hypervisor_disk_bus_list: [disk_bus],
                        target_hypervisor_net_bus_list: [net_bus],
                        os_type: capitalize(getAwsConfig(point.point_info[j].timepoint_uuid,'os_type')),
                        os_version: getAwsConfig(point.point_info[j].timepoint_uuid,'os_version'),
                        os_arch: cpuArchValue[getAwsConfig(point.point_info[j].timepoint_uuid,'architecture')],
                    });
                }
            } else {
                var returns = true;
                // 获取时间点信息
                for (let j in point.point_info) {
                    let source_disk_bus = $.fn.getVmRecoverySourceDiskBus(point.point_info[j].timepoint_uuid);
                    let source_net_bus = $.fn.getVmRecoverySourceNetBus(point.point_info[j].timepoint_uuid);
                    var disk_bus = $.fn.getVmRecoveryDiskBus(point.point_info[j].timepoint_uuid);
                    var net_bus = $.fn.getVmRecoveryNetBus(point.point_info[j].timepoint_uuid);

                    for (var k in disk_bus) {
                        if (disk_bus[k] == LANG.UI_JOB_SELECT) {
                            returns = false;
                            break;
                        }
                    }
                    if (!returns) {
                        var msg = LANG.UI_PLATFORM_RECOVERY_DRIVER_CHECK_TIPS2.replace('%s', point.point_info[j].path);
                        return UIToastr.showWarning(LANG.UI_DRIVER_CHECK, msg);
                        break;
                    }

                    for (var k in net_bus) {
                        if (net_bus[k] == LANG.UI_JOB_SELECT) {
                            returns = false;
                            break;
                        }
                    }
                    if (!returns) {
                        var msg = LANG.UI_PLATFORM_RECOVERY_DRIVER_CHECK_TIPS.replace('%s', point.point_info[j].path);
                        return UIToastr.showWarning(LANG.UI_DRIVER_CHECK, msg);
                        break;
                    }

                    source_list.push({
                        name: point.show_str[j].replace(/<i[^>]*>.*?<\/i>|<br\s*\/?>/gi, ''),
                        timepoint_uuid:point.point_info[j].timepoint_uuid,
                        module_type: moduleTypeArr[pointType], // OS
                        source_hypervisor_disk_bus_list: source_disk_bus,
                        source_hypervisor_net_bus_list: source_net_bus,
                        target_hypervisor_disk_bus_list: disk_bus,
                        target_hypervisor_net_bus_list: net_bus,
                        os_type: getVmConfig(point.point_info[j].timepoint_uuid,'os_type'),
                        os_version: getVmConfig(point.point_info[j].timepoint_uuid,'os_version'),
                        os_arch: getVmConfig(point.point_info[j].timepoint_uuid,'cpu_type'),
                    });
                }

            }

            // console.log('source_list', source_list)
            // 注意。点击下一步的时候需要判读此时的虚拟机配置和检测的时候是一致的
            source_list_check = source_list;
            return {
                source_list:source_list,
                target_info:{
                    target_type: $.fn.driverCheck.DRIVER_TARGET_TYPE.VM, // CLIENT
                    vm_info: vm_info
                }
            }
        }
        // 获取驱动信息
        let getDriver = (vm_info) => {
            $.fn.driverCheck.init($('#driverCheck_vm'), {
                width: {
                    config_content: 'col-md-7',
                    check_name: 'col-md-5',
                    check_platform: 'col-md-2',
                    check_driver: 'col-md-5',
                },
                getCheckObject:() => {
                    // 这里改为动态的去获取虚拟机配置里面的磁盘和网线的类型对应值
                    return getDiskAndNetwork(vm_info)
                },
                show_check_btn:false,
                afterCheck:function (result, data){
                    // 根据返回结果为true的时候显示下面的虚拟机信息
                    // console.log('driver_data',result, data)
                    // 隐藏是否继续恢复
                    $('#driverCheckFailContinue').hide();
                    isPlatformType = false;
                    isSupportOs = false;
                    if (result) {
                        /*// 测试代码
                        // 显示是否继续恢复
                        $('#driverCheckFailContinue').show();
                        isPlatformType = true;*/
                        driverCheck = data;
                        // 此时可以下一步了
                    } else {
                        // 还有个操作系统版本不支持添加驱动的情况
                        if (data[0].driver_check_status == 8 && areFieldValuesSame(data, 'driver_check_status')) {
                            // 所有的检查结果都是不支持添加驱动，给个提示，可以继续下去
                            for (var ks in data) {
                                data[ks].driver_hw_id_map = ''; // 驱动信息设置为空
                                data[ks].platform_type = 1; // 改为非异构。不用替换驱动
                            }
                            driverCheck = data;
                            isSupportOs = true;
                        } else {
                            // 有个特殊处理，如果是异构平台，用户可决定是否继续恢复
                            for(var k in data) {
                                if (data[k].platform_type == 2) {
                                    isPlatformType = true;
                                } else {
                                    isPlatformType = false;
                                }
                            }
                            if (isPlatformType) {
                                // 显示是否继续恢复
                                $('#driverCheckFailContinue').show();
                                driverCheck = data;
                            } else {
                                driverCheck = [];
                            }
                        }
                    }
                }
            });
        }

        // 检查某个数组里面的某个字段的值是否都是一样的
        function areFieldValuesSame(arr, field) {
            if (!Array.isArray(arr) || arr.length === 0) return true; // 空数组或非数组直接返回 true
            const values = arr.map(item => item[field]); // 提取指定字段的值
            return new Set(values).size === 1; // 使用 Set 检查唯一性
        }

        //初始化虚拟机配置V2,跨平台
        var initVMconfigV2 = function(node, pointData){
            isLoading = false;
            currentPlatformUuid = node.vcuuid;
            var p = {};
            p.hypervisor_type = node.hypervisor;
            p.platform_uuid = node.vcuuid;
            p.host_uuid = node.id;
            console.log(node)

            if(CONF.VMTYPE_GROUP.OPENSTACK.includes(p.hypervisor_type)){
                sub_module_type = 2;
                p.host_uuid = node.groupuuid ?? node.id;
            } else if(CONF.VMTYPE_GROUP.PUBLICCLOUD.includes(p.hypervisor_type)){
                sub_module_type = 3;
            } else {
                sub_module_type = 1;
            }

            var allPoints = pointData.point_info;
            var vmOldName = pointData.vmOldName;
            var srcVmType = pointData.type;
            var points = [];
            var pointsDetail = [];
            for(var i=0; i<allPoints.length; i++){
                var point = {};
                point.hypervisor = allPoints[i]['hypervisor'];
                point.timepointuuid = allPoints[i]['timepoint_uuid'];
                point.vcenteruuid = allPoints[i]['vcenteruuid'];
                point.vmuuid = allPoints[i]['uuid'];
                point.vmname = vmOldName[i][0] + "_" + clearStringEmpty(vmOldName[i][1]);
                point.oldname = vmOldName[i][0];
                point.timepoint = vmOldName[i][1];
                point.nodeuuid = allPoints[i]['node_uuid'];
                point.config = allPoints[i]['config'];
                if (0 === point.hypervisor) {
                    point.config = {};
                    point.config.password = allPoints[i]['encrypted_flag']; // 是否加密
                    point.config.password_auto_flag = 2; // 整机的时间点都需要输入密码，所以写死为2
                }
                points.push(allPoints[i]['timepoint_uuid']);
                pointsDetail.push(point);
            }
            p.points = points;
            p.points_detail = pointsDetail;
            p.instant_flag = jobType === 2;
            p.motion_flag = jobType === 3;

            //xhere块存储策略
            if (CONF.VM_TYPE.XHERE == node.hypervisor) {
                initXhereVolumePolicyList(node);
            }

            let apiUrl = "/api/v1/vm/timepoints/config";
            let httpType = "POST";
            if (jobType === 3) {
                // let timepointPlatformUuid = allPoints[0].vcenter_uuid;
                // let instantPlatformUuid = allPoints[0].target_uuid;
                // let motionPlatformUuid = node.vcuuid;
                // if (timepointPlatformUuid === instantPlatformUuid && instantPlatformUuid === motionPlatformUuid
                //     || timepointPlatformUuid !== instantPlatformUuid && instantPlatformUuid === motionPlatformUuid
                //     || timepointPlatformUuid !== instantPlatformUuid && timepointPlatformUuid === motionPlatformUuid
                // ) {
                // 原平台瞬时恢复且原平台迁移，走迁移接口获取
                // 跨平台瞬时恢复，迁移平台不变，走迁移接口获取
                // 跨平台瞬时恢复，迁移回时间点所在平台，走迁移接口获取（总线类型改为时间点的）
                apiUrl = "/api/v1/vm/jobs/motion/config";
                httpType = "GET";
                p.job_uuid = instantTaskUuid;
                // }
            }

            Metronic.blockUI({target: '#show_vmRecovery',animate: true});
            pAjaxRequest(p, apiUrl, httpType, function (d) {
                Metronic.unblockUI('#show_vmRecovery');
                var data = d.data;
                if(!data.flag){
                    //时间点不存在/磁带被占用
                    if (50 == data.errorCode || 14025 == data.errorCode) {
                        return UIToastr.showWarning(sub_module_type == 2 ? LANG.UI_INSTANCE_GET_VIRTUAL_CONFIG_INFO : LANG.UI_VM_GET_VIRTUAL_CONFIG_INFO, data.errorMsg);
                    }
                    //如果获取失败
                    return UIToastr.showWarning(sub_module_type == 2 ? LANG.UI_INSTANCE_GET_VIRTUAL_CONFIG_INFO : LANG.UI_VM_GET_VIRTUAL_CONFIG_INFO,
                        sub_module_type == 2 ? LANG.UI_INSTANCE_GETINFO_FAIL_TRY_AGIN : LANG.UI_VM_GETINFO_FAIL_TRY_AGIN);
                }
                isLoading = true;
                vmPlugnInfoData = data;
                vmNameLimit = data.vmNameLimit;

                // 遍历时间点检测目标主机cpu架构和时间点的是否一致，不一致提示无法恢复
                let x86Arch = [1, 2];
                let armArch = [3, 4];
                for (let i in data.source_config) {
                    if (data.source_config[i].cpu_arch != 0 && hostCpuArch != 0
                        && (x86Arch.includes(data.source_config[i].cpu_arch) && !x86Arch.includes(hostCpuArch)
                            || armArch.includes(data.source_config[i].cpu_arch) && !armArch.includes(hostCpuArch))
                    ) {
                        return UIToastr.showWarning(LANG.UI_VM_CHECK_CPU_ARCH, LANG.UI_VM_TIMEPOINT_CPU_ARCH_DIFF_WITH_HOST);
                    }
                }

                //xhere块存储策略
                data.volume_policy = policy_list;

                if (jobType === 3) {
                    // 迁移
                    data.vmotionFlag = true
                } else if (jobType == 2) {
                    data.instantFlag = true;
                    if (0 == pointsDetail[0].hypervisor && 108 == node.hypervisor) {
                        // 整机瞬时恢复到内嵌判断preinstall_driver_success，为0需要驱动检测
                        if (0 == vmPlugnInfoData.config[0].preinstall_driver_success) {
                            showDriver = true;
                            isPlatformType = false;
                            driverCheck = [];
                            $('#driverCheck_vm').parent().show();
                            // 驱动检测和初始化虚拟机配置
                            initDriver(chooseNode);
                        } else {
                            showDriver = false;
                            isPlatformType = false;
                            driverCheck = [];
                            $('#driverCheck_vm').html('');
                            $('#driverCheckFailContinue').hide();
                            $('#driverCheck_vm_button').hide();
                        }
                    }
                }

                data.platform_uuid = p.platform_uuid;
                data.host_uuid = p.host_uuid;
                data.region = CONF.VMTYPE_GROUP.OPENSTACK.includes(node.hypervisor) ? node.name : '';
                // 初始化无代理的组件配置
                $('#accordionvm').vmRecoveryConfig(data);

                // 添加事件
                $(`select[name=disk_bus_type]`).on('change', function () {
                    let timepoint_uuid = $(this).closest('.configsdiv').data('timepointuuid');
                    if (jobType == 2) {
                        initDriverAgain();
                        initDriverAgainByBusType('disk', $(this).val());
                    }
                    $.each(source_list, function (i, v) {
                        if (timepoint_uuid == v.timepoint_uuid) {
                            v.target_hypervisor_disk_bus_list = $.fn.getVmRecoveryDiskBus(timepoint_uuid);
                            source_list_check = [];
                        }
                    })
                });
                $(`select[name=network_bus_type]`).on('change', function () {
                    let timepoint_uuid = $(this).closest('.configsdiv').data('timepointuuid');
                    if (jobType == 2) {
                        initDriverAgain();
                        initDriverAgainByBusType('net', $(this).val());
                    }
                    $.each(source_list, function (i, v) {
                        if (timepoint_uuid == v.timepoint_uuid) {
                            v.target_hypervisor_net_bus_list = $.fn.getVmRecoveryNetBus(timepoint_uuid);
                            source_list_check = [];
                        }
                    })
                });
                $(`select[name=os_type]`).on('change', function () {
                    let timepoint_uuid = $(this).closest('.configsdiv').data('timepointuuid');
                    if (jobType == 2) {
                        initDriverAgain();
                    }
                    $.each(source_list, function (i, v) {
                        if (timepoint_uuid == v.timepoint_uuid) {
                            v.os_type = getVmConfig(timepoint_uuid, 'os_type');
                            source_list_check = [];
                        }
                    })
                });
                $(`select[name=os_version]`).on('change', function () {
                    let timepoint_uuid = $(this).closest('.configsdiv').data('timepointuuid');
                    if (jobType == 2) {
                        initDriverAgain();
                    }
                    $.each(source_list, function (i, v) {
                        if (timepoint_uuid == v.timepoint_uuid) {
                            v.os_version = getVmConfig(timepoint_uuid, 'os_version');
                            source_list_check = [];
                        }
                    })
                });
                $(`select[name=cpu_type]`).on('change', function () {
                    let timepoint_uuid = $(this).closest('.configsdiv').data('timepointuuid');
                    if (jobType == 2) {
                        initDriverAgain();
                    }
                    $.each(source_list, function (i, v) {
                        if (timepoint_uuid == v.timepoint_uuid) {
                            v.os_arch = getVmConfig(timepoint_uuid, 'cpu_type');
                            source_list_check = [];
                        }
                    })
                });

                params = data;
                if(_hypervisor != node.hypervisor && jobType == 1){
                    // 跨平台恢复才会用到虚拟机的传输代理插件
                    $.fn.vmRecoveryResetOptions();	//切换虚拟化类型重置高级配置默认选项
                }
                _hypervisor = node.hypervisor;

                $('#vmconfigs').show();

                if (CONF.VM_TYPE.INCLOUDKVM == _hypervisor || CONF.VM_TYPE.INSPURVVDK == _hypervisor) {
                    _VMNAMEREG = new RegExp("[ \n\r\t\f`~!@#$^&*()=|{}':;',\\[\\]<>/?~！@#￥……&*（）&;|{}【】‘；：”“'。，、？+]");
                }
                //初始化网络和存储
                if(sub_module_type == 2){
                    initNetworkAndStoreGroupUser(node);
                    //其他虚拟化恢复到openstack虚拟化 隐藏删除实例删除卷选项，openstack到openstack才显示该选项
                    if (!CONF.VMTYPE_GROUP.OPENSTACK.includes(srcVmType)) {
                        // 这里有点疑问，如果是跨平台恢复，会有多个时间点，但是这个是取的最后的那个时间点的虚拟化类型
                        $('.delDiskDiv').hide();
                    }
                }else{
                    initNetworkAndStore(node);
                }
                backOpens();
            });
        }

        var initDriverAgain = function (){
            // 这里需要单端判断下瞬时恢复的情况
            if (chooseNode.hypervisor == point.point_info[0].hypervisor) {
                // 是同平台，看看更改了配置没
                if (!arraysDeepEqual(source_list_old, getDefaults(true))) {
                    showDriver = true;
                    isPlatformType = false;
                    driverCheck = [];
                    $('#driverCheck_vm').parent().show();
                    // 驱动检测和初始化虚拟机配置
                    initDriver(chooseNode);
                } else {
                    showDriver = false;
                    driverCheck = [];
                    isPlatformType = false;
                    $('#driverCheck_vm').html('');
                    $('#driverCheckFailContinue').hide();
                    $('#driverCheck_vm_button').hide();
                }
            }

        }

        const initDriverAgainByBusType = (type, val) => {
            if (0 == point.point_info[0].hypervisor && 108 == chooseNode.hypervisor && 1 == vmPlugnInfoData.config[0].preinstall_driver_success) {
                // 整机瞬时恢复到内嵌判断preinstall_driver_success，为1需要再判断磁盘或网卡总线是否修改，若修改则需要驱动检测
                // 默认virtio磁盘总线和RTL8139网卡总线
                if ('disk' === type && 4 != val || 'net' === type && 7 != val) {
                    showDriver = true;
                    isPlatformType = false;
                    driverCheck = [];
                    $('#driverCheck_vm').parent().show();
                    // 驱动检测和初始化虚拟机配置
                    initDriver(chooseNode);
                } else {
                    showDriver = false;
                    isPlatformType = false;
                    driverCheck = [];
                    $('#driverCheck_vm').html('');
                    $('#driverCheckFailContinue').hide();
                    $('#driverCheck_vm_button').hide();
                }
            }
        }

        // 递归的去比较数组元素
        function arraysDeepEqual(arr1, arr2) {
            if (!Array.isArray(arr1) || !Array.isArray(arr2) || arr1.length !== arr2.length) {
                return false;
            }
            for (let i = 0; i < arr1.length; i++) {
                if (!deepEqual(arr1[i], arr2[i])) {
                    return false;
                }
            }
            return true;
        }
        // 比较数组
        function deepEqual(obj1, obj2) {
            if (obj1 === obj2) return true;

            if (typeof obj1 !== 'object' || obj1 === null || typeof obj2 !== 'object' || obj2 === null) {
                return false;
            }

            const keys1 = Object.keys(obj1);
            const keys2 = Object.keys(obj2);

            if (keys1.length !== keys2.length) {
                return false;
            }

            for (let key of keys1) {
                if (!keys2.includes(key) || !deepEqual(obj1[key], obj2[key])) {
                    return false;
                }
            }

            return true;
        }

        //初始化xhere块存储策略
        var initXhereVolumePolicyList = function (node) {
            let p = {platform_uuid: node.vcuuid};
            pAjaxRequest(p, "/api/v1/vm/xhere/volume_policy", "GET", function (d) {
                policy_list = d.data.rows;
            }, false);
        }

        //初始化网络和存储(flexcloud,openstack)
        var initNetworkAndStoreGroupUser = function(node){
            let p = {
                platform_uuid: node.vcuuid,
                hypervisor_type: node.hypervisor,
                group_name: node.groupname,
                group_uuid: node.groupuuid,
                username: node.username,
                password: node.password,
                region: node.name,
            };
            Metronic.blockUI({target: '#vmrecovercontent',animate: true});
            pAjaxRequest(p, "/api/v1/vm/openstack/configs", "GET", function (d) {
                Metronic.unblockUI('#vmrecovercontent');
                var data = d.data;
                _HOSTSTORAGE = data;
                var hoststorage = $('select[name=hoststorage]');
                var hostnetwork = $('select[name=hostnetwork]');
                var useDomain = $('select[name=available_domain_select]');
                var diskDomain = $('select[name=disk_available_domain_select]');
                var instance = $('select[name=instance_socket]');
                hoststorage.empty();
                hostnetwork.empty();
                useDomain.empty();
                diskDomain.empty();
                instance.empty();
                var network = [];
                var storage = [];
                var instanceList = [];
                for(var i=0; i<data.storage.length; i++){
                    var option = $("<option>").text(data.storage[i].text).val(data.storage[i].uuid);
                    hoststorage.append(option);
                }

                //虚拟机可用域
                for(var i=0; i<data.domain.length; i++){
                    var option = $("<option>").text(data.domain[i].text).val(data.domain[i].uuid);
                    useDomain.append(option);
                }

                //磁盘可用域
                for(var i=0; i<data.domain.length; i++){
                    var option = $("<option>").text(data.domain[i].text).val(data.domain[i].uuid);
                    diskDomain.append(option);
                }

                //实例
                for(var i=0; i<data.instance.length; i++){
                    var option = $("<option>").text(data.instance[i].text).val(data.instance[i].uuid);
                    var instanceInfo = data.instance[i].uuid.split('_');
                    var instanceRootDisk = instanceInfo[2];
                    instance.append(option);
                    instanceList.push({
                        uuid: data.instance[i].uuid,
                        root_disk: instanceRootDisk,
                        flavor_id: data.instance[i].flavor_id
                    });
                }

                // 网络统一初始化
                for (let i = 0; i < data.network.length; i++) {
                    hostnetwork.append($("<option>").text(data.network[i].text).val(data.network[i].uuid));
                }

                //选择虚拟机加载原来选择的网络
                var vmList = vmPlugnInfoData.config;
                for(var i=0;i<vmList.length;i++){
                    var uuid = vmList[i].vm_uuid;
                    var netList = vmList[i].net_list;
                    //加载原虚拟机实例类型
                    var diskList = vmList[i].disk_list;
                    var rootDisk = parseInt(diskList[0].virtual_size_unit);
                    //实例类型匹配规则：实例类型id相等
                    $.each(instanceList, function (idx, v) {
                        if (vmList[i].flavor_id == v.flavor_id) {
                            $('.'+clearString(uuid) + " select[name=instance_socket]").val(v.uuid);
                            return true;
                        }
                    })
                    for (let j = 0; j < diskList.length; j++) {
                        hoststorage = $('#accordionvm').find('.panel').eq(i).find('select[name=hoststorage]').eq(j);
                        hoststorage.empty();
                        for (let k = 0; k < data.storage.length; k++) {
                            if (jobType === 3 && data.storage[k].text.includes('ir_nas_store')) {
                                // 迁移时不显示备份系统创建的nfs存储，即ir_nas_store开头的
                                continue;
                            }
                            let selected = '';
                            let oriStorageDes = '';
                            // 当前磁盘是原存储时显示提示
                            if (diskList[j].src_storage_uuid == data.storage[k].uuid) {
                                selected = 'selected';
                                oriStorageDes = `(${LANG.UI_RECOVERY_ORIGINAL_STORAGE})`;
                            }
                            let option = $(`<option ${selected}>`).text(data.storage[k].text + oriStorageDes).val(data.storage[k].uuid);
                            hoststorage.append(option);
                            storage.push(data.storage[k].uuid);
                        }
                        if ($.inArray(diskList[j].src_storage_uuid, storage) != -1) {
                            //如果原来存储在当前宿主机里则复用原来的存储
                            hoststorage.val(diskList[j].src_storage_uuid);
                        }
                        // 资源池的存储
                        if (diskList[j].storage_pool.pool_uuid && storage.includes(diskList[j].storage_pool.pool_uuid)) {
                            hoststorage.val(diskList[j].storage_pool.pool_uuid);
                        }
                    }
                    for(var j=0;j<netList.length;j++){
                        // hostnetwork = $('.'+clearString(uuid + netList[j].network_name) + " select[name=hostnetwork]");
                        hostnetwork = $('#accordionvm').find('.panel').eq(i).find('select[name=hostnetwork]').eq(j);
                        hostnetwork.empty();
                        for (var k = 0; k < data.network.length; k++) {
                            // 有多个网卡且当前网卡是原网卡时显示提示
                            let selected = '';
                            let oriNetworkDes = '';
                            if (netList[j].src_network_uuid == data.network[k].uuid) {
                                selected = 'selected';
                                oriNetworkDes = `(${LANG.UI_RECOVERY_ORIGINAL_NETWORK})`;
                            }
                            var option = $("<option ${selected}>").text(data.network[k].text + oriNetworkDes).val(data.network[k].uuid);
                            hostnetwork.append(option);
                            network.push(data.network[k].uuid);
                        }
                        if($.inArray(netList[j].src_network_uuid, network) != -1){
                            //如果原来网络在当前宿主机里则复用原来的网络
                            hostnetwork.val(netList[j].src_network_uuid);
                        }
                    }
                }
                //显示隐藏项目
                $('#vmconfigs').show();
                $('.dndiv').show();
                $('#vmrecovercontent').find('.button-next').prop('disabled', false);
                $('#nodeconfigs').hide();
                $(`select[name=instance_socket]`).selectpicker('refresh');

                // 获取默认配置
                getDefaults();

                $('select[name=hoststorage]').selectpicker('refresh');
                $('select[name=hostnetwork]').selectpicker('refresh');
            }, false);
        }

        //初始化网络和存储(一般情况)
        var initNetworkAndStore = function(node){
            var p = {};
            p.hypervisor_type = node.hypervisor;
            p.platform_uuid = node.vcuuid;
            p.host_uuid = node.id;
            Metronic.blockUI({target: '#vmrecovercontent',animate: true});
            _HOSTSTORAGE = [];
            $('select[name=hoststorage]').empty();
            $('select[name=hostnetwork]').empty();
            pAjaxRequest(p, "/api/v1/vm/host/network_storage", "GET", function (d) {
                Metronic.unblockUI('#vmrecovercontent');
                var data = d.data;
                _vcenterType = data.vcenter_type;
                _HOSTSTORAGE = data;

                // 目标存储
                var hoststorage = $('select[name=hoststorage]');
                hoststorage.empty();
                for(var i=0; i<data.storage.length; i++){
                    var option = $("<option>").text(data.storage[i].text).val(data.storage[i].uuid);
                    hoststorage.append(option);
                }

                // 统一配置处的目标网络
                var hostnetworkUnify = $('#vmsnetwork');
                hostnetworkUnify.empty();
                for(var i=0; i<data.network.length; i++){
                    var option = $("<option>").text(data.network[i].text);
                    if (CONF.VM_TYPE.VMWARE == _hypervisor) {
                        //vmware传网卡名称
                        if (0 == data.network[i].uuid) {
                            option.val('');
                        } else {
                            option.val(data.network[i].text);
                        }
                    } else {
                        option.val(data.network[i].uuid);
                    }
                    hostnetworkUnify.append(option);
                }
                hostnetworkUnify.selectpicker('refresh');

                // 每个虚拟机的目标存储和目标网络
                var vmList = vmPlugnInfoData.config;
                $.each(vmList, function (idx, val) {
                    $.each(val.disk_list,function (idxDisk, valDisk) {
                        let hoststorage = $('#accordionvm').find('.panel').eq(idx).find('select[name=hoststorage]').eq(idxDisk);
                        hoststorage.empty();
                        let storage = [];
                        for (let i = 0; i < data.storage.length; i++) {
                            let oriStorageDes = '';
                            let option = $(`<option data-type="${data.storage[i].driver_type}">`);
                            option.val(data.storage[i].uuid);
                            storage.push(data.storage[i].uuid);
                            // 当前磁盘是原存储时显示提示
                            if (valDisk.src_storage_uuid == data.storage[i].uuid) {
                                oriStorageDes = `(${LANG.UI_RECOVERY_ORIGINAL_STORAGE})`;
                                option.attr('selected', true);
                            }
                            option.text(data.storage[i].text + oriStorageDes);
                            hoststorage.append(option);
                        }
                        // 资源池的存储
                        if (valDisk.storage_pool.pool_uuid && storage.includes(valDisk.storage_pool.pool_uuid)) {
                            hoststorage.val(valDisk.storage_pool.pool_uuid);
                        }

                        if (CONF.VMTYPE_GROUP.HUAWEIKVM.includes(node.hypervisor)) {
                            // 恢复到华为kvm，目标存储为nfs时置备模式锁定为【精简】
                            if (hoststorage.find(`option:selected`).data('type') === 'netfs') {
                                hoststorage.closest('tbody').find('select[name=setting_mode]').val(1).prop('disabled', true);
                            } else {
                                hoststorage.closest('tbody').find('select[name=setting_mode]').prop('disabled', false);
                            }
                        }
                    });
                    $.each(val.net_list, function (idxNet, valNet) {
                        let hostnetwork = $('#accordionvm').find('.panel').eq(idx).find('select[name=hostnetwork]').eq(idxNet);
                        hostnetwork.empty();
                        for (var i = 0; i < data.network.length; i++) {
                            // 有多个网卡且当前网卡是原网卡时显示提示
                            let oriNetworkDes = '';
                            var option = $("<option>");
                            if (CONF.VM_TYPE.VMWARE == _hypervisor) {
                                //vmware传网卡名称
                                if (0 == data.network[i].uuid) {
                                    option.val('');
                                } else {
                                    option.val(data.network[i].text);
                                }
                            } else {
                                option.val(data.network[i].uuid);
                            }
                            if (valNet.src_network_uuid == data.network[i].uuid) {
                                oriNetworkDes = `(${LANG.UI_RECOVERY_ORIGINAL_NETWORK})`;
                                option.attr('selected', true);
                            }
                            option.text(data.network[i].text + oriNetworkDes);
                            hostnetwork.append(option);
                        }
                        hostnetwork.selectpicker('refresh');
                    })
                });


                //批量配置虚拟机选择存储
                for(var i=0;i<vmuuidList.length;i++){
                    var id = escapeJquery(vmuuidList[i]);
                    $('#vmstorage'+id+' select[name=hoststorage]').on('change', {id: id},function(e){
                        if(_hypervisor == CONF.VM_TYPE.HYPERV && _vcenterType == 1){
                            $('#vmstorage'+ e.data.id+' select[name=hoststorage]').val($(this).val());
                        }
                    });
                }

                //如果虚拟化是SCP，禁止选择网络，暂不支持
                // if(node.hypervisor == CONF.VM_TYPE.SANGFORVVDK){
                // 	$('select[name="hostnetwork"]').prop('disabled', true);
                // }else{
                // 	$('select[name="hostnetwork"]').prop('disabled', false);
                // }

                //			$('.button-next').prop('disabled', false);
                $('#vmrecovercontent').find('.button-next').prop('disabled', false);

                // 获取默认配置
                getDefaults();

                hoststorage.selectpicker('refresh');
            });
        }

        // 获取机器默认的一些配置存储
        var getDefaults = function (flag = false){
            var temp = [];
            for (var j in point.point_info) {
                let source_disk_bus = $.fn.getVmRecoverySourceDiskBus(point.point_info[j].timepoint_uuid);
                let source_net_bus = $.fn.getVmRecoverySourceNetBus(point.point_info[j].timepoint_uuid);
                var disk_bus = $.fn.getVmRecoveryDiskBus(point.point_info[j].timepoint_uuid);
                var net_bus = $.fn.getVmRecoveryNetBus(point.point_info[j].timepoint_uuid);
                switch (chooseNode.hypervisor) {
                    case CONF.VM_TYPE.AWS:
                        // aws：默认网络驱动 ena ；默认磁盘驱动 aws_nvme
                        disk_bus = [37];
                        net_bus = [36];
                        break;
                    case CONF.VM_TYPE.HUAWEICLOUD:
                        // 华为云：默认网络驱动和磁盘驱动：virtio
                        disk_bus = [4];
                        net_bus = [4];
                        break;
                    default:
                        break;
                }
                temp.push({
                    name: point.show_str[j],
                    timepoint_uuid:point.point_info[j].timepoint_uuid,
                    module_type: pointType == 1 ? CONF.MODULE_TYPE.VM : CONF.MODULE_TYPE.OS, // OS
                    source_hypervisor_disk_bus_list: source_disk_bus,
                    source_hypervisor_disk_net_list: source_net_bus,
                    target_hypervisor_disk_bus_list: disk_bus,
                    target_hypervisor_net_bus_list: net_bus,
                    // os_type: getVmConfig(point.point_info[j].timepoint_uuid,'os_type'),
                    //  os_version: getVmConfig(point.point_info[j].timepoint_uuid,'os_version'),
                    os_arch: getVmConfig(point.point_info[j].timepoint_uuid,'cpu_type'),
                });
            }
            if (!flag) {
                source_list_old = temp;
            }

            return temp;
        }

        //替换特殊字符
        var clearString = function (s){
            var rs = "";
            for (var i = 0; i < s.length; i++) {
                rs = rs+s.substr(i, 1).replace(_VMNAMEREG, '_');
            }
            return rs;
        }

        //替换特殊字符为空
        var clearStringEmpty = function (s){
            var rs = "";
            for (var i = 0; i < s.length; i++) {
                rs = rs+s.substr(i, 1).replace(_VMNAMEREG, '');
            }
            return rs;
        }

        // 检测虚拟机名称
        const vmNameValid = function (vmconfigs) {
            var flag = true;
            var vmnameFlag = true; //检查恢复后虚拟机名是否规范
            var vmNameLengthFlag = true; //检查恢复后虚拟机名长度是否规范
            var reg = new RegExp(vmNameLimit.limit);
            var diskReg = new RegExp(vmNameLimit.disk_limit);
            //得到虚拟机恢复名
            let warningNameTitle = sub_module_type == 2 ? LANG.UI_VM_RESTORE_INSTANCE_NAME_TITLE : LANG.UI_VM_RESTORE_NAME_TITLE;
            let warningNameNull = sub_module_type == 2 ? LANG.UI_RECOVERY_INSTANCE_NAME_NOT_NULL : LANG.UI_RECOVERY_NAME_NOT_NULL;
            let warningNameInvalid = sub_module_type == 2 ? LANG.UI_TOOLS_INSTANCE_NAME_TIPS : LANG.UI_TOOLS_VMNAME_TIPS;
            let warningNameRecovered = sub_module_type == 2 ? LANG.UI_VM_SETTING_REC_INSTANCE_NAME : LANG.UI_VM_SETTING_REC_NAME;
            let warningDiskTitle = sub_module_type == 2 ? LANG.UI_INSTANCE_RESTORE_DISK_NAME_TITLE : LANG.UI_VM_RESTORE_DISK_NAME_TITLE;
            $.each(vmconfigs, function (i, d) {
                var value = $.trim(d.vmname);
                if ("" == value) {
                    //$(".setrecover2tip").html(LANG.UI_RECOVERY_NAME_NOT_NULL).show();
                    UIToastr.showWarning(warningNameTitle, warningNameNull);
                    flag = false;
                    return false;
                }
                if ('' == value || !reg.test(value)) {
                    //$(".setrecover2tip").html(_VMNAMETIPS).show();
                    UIToastr.showWarning(warningNameTitle, warningNameInvalid);
                    vmnameFlag = false;
                    return false;
                }
                if (value.length > vmNameLimit.len) {
                    //$(".setrecover2tip").html(LANG.UI_VM_SETTING_REC_NAME + vmNameLimit.msg).show();
                    UIToastr.showWarning(warningNameTitle, warningNameRecovered + vmNameLimit.msg);
                    vmNameLengthFlag = false;
                    return false;
                }

                // 校验磁盘名称
                let diskNameList = [];
                $.each(d.storage, function (idx, disk) {
                    let diskName = $.trim(disk.disk_name);
                    if ('' == diskName || !diskReg.test(diskName)) {
                        UIToastr.showWarning(warningDiskTitle, LANG.UI_TOOLS_DISK_NAME_TIPS);
                        flag = false;
                        return false;
                    }
                    // 瞬时恢复不能有重复磁盘名
                    if (jobType == 2 && $.inArray(diskName, diskNameList) != -1) {
                        $(`.configsdiv[data-timepointuuid=${d.timepointuuid}]`).find(`input[name=diskname]`).valid('uniquename');
                        UIToastr.showWarning(warningDiskTitle, LANG.UI_INSTANT_DISK_NAME_UNIQUE_TIPS);
                        flag = false;
                        return false;
                    }
                    diskNameList.push(diskName);
                });

                // 校验重置主机名
                if (d.other.reset_hostname && '' == $.trim(d.other.new_hostname)) {
                    UIToastr.showWarning(LANG.UI_VM_RESET_HOST_NAME, LANG.UI_MACHINE_OS_PLEASE_INPUT_RESET_HOSTNAME);
                    flag = false;
                    return false;
                }

                if (!flag) {
                    return false;
                }
            });
            if (!flag) return false;
            if (!vmnameFlag) return false;
            if (!vmNameLengthFlag) return false;

            //验证虚拟机配置,主要是存储使用
            return $('#accordionvm').vmConfigValidate({
                vmconfig: vmconfigs,
                hoststorage: _HOSTSTORAGE,
                hypervisor: _hypervisor,
                nameLimit: vmNameLimit,
                instantFlag: jobType == 2,
            });
        }

        // check is choose must item
        var checkIsChooseItem = function () {
            var title = LANG.UI_DRIVER_CHECK;
            var msg1 = LANG.UI_PLATFORM_RECOVERY_CPU_TIPS;
            var msg2 = LANG.UI_PLATFORM_RECOVERY_OS_TYPE_TIPS;
            var msg3 = LANG.UI_PLATFORM_RECOVERY_OS_VSERSION_TIPS;

            for (var j in point.point_info) {
                var item = point.point_info[j];
                var uuid = item.timepoint_uuid;
                var hostName = item.host_name;

                var os_arch = getVmConfig(uuid, 'cpu_type');
                if (!os_arch || parseInt(os_arch) === 0) {
                    UIToastr.showWarning(title, msg1.replace(/s%/, hostName));
                    return false;
                }

                var os_type = getVmConfig(uuid, 'os_type');
                if (!os_type || parseInt(os_type) === 0) {
                    UIToastr.showWarning(title, msg2.replace(/s%/, hostName));
                    return false;
                }

                var os_version = getVmConfig(uuid, 'os_version');
                if (!os_version || parseInt(os_version) === 0) {
                    UIToastr.showWarning(title, msg3.replace(/s%/, hostName));
                    return false;
                }
            }

            return true;
        };

        return {
            init: function (options = {}) {
                if (options.point != undefined) {
                    point = options.point;
                } else {
                    point = RecoverTimepoint.getInfo();
                }

                if (options.showType != undefined) {
                    // 渲染的类型 1虚拟化2私有云3公有云，4容灾演练平台
                    showType = options.showType
                }

                // 是否需要驱动检测流程
                if (options.showDriver != undefined) {
                    showDriver = options.showDriver;
                } else {
                    showDriver = true;
                }

                // 测试。都不要驱动检测
                // showDriver = false;

                if (options.jobType != undefined) {
                    jobType = options.jobType;
                }

                if (options.pointType != undefined) {
                    pointType = options.pointType;
                }

                if (options.instantTaskUuid != undefined) {
                    instantTaskUuid = options.instantTaskUuid;
                }
                initHostTree();		//初始化宿主机树
                initListener();
            },
            getInfo: function (){
                // 对外提供获取虚拟机配置信息
                if (typeof chooseNode === "object" && isLoading != false) {
                    var config = false;
                    if (CONF.VMTYPE_GROUP.PUBLICCLOUD.includes(chooseNode.hypervisor)) {
                        // 公有云获取
                        config = $.fn.getAwsRecoveryConfig();
                    } else {
                        config = $('#accordionvm').getvmRecoveryConfig(params)
                        if (!vmNameValid(config)) {
                            config = false;
                        }
                    }
                    var info = {
                        config: config,
                        // config: [1],
                        title: chooseNode.name + '('+ chooseNode.hypervisor_des +')',
                        node: chooseNode,
                        sub_module_type: sub_module_type,
                        _hypervisor: chooseNode.hypervisor,
                        continue_driver: false, // 标记是否异构平台检测未通过的情况
                        driver_replace_flag: false, // 标记是否继续驱动替换
                    }
                    // 宿主机或项目未选中弹出提示
                    if (CONF.VMTYPE_GROUP.OPENSTACK.includes(chooseNode.hypervisor)) {
                        if (!chooseNode.checkedFlag) {
                            UIToastr.showWarning(LANG.UI_MOTION_RECOVERY_HOST, LANG.UI_MOTION_RECOVERY_HOST_TIPS);
                            info.config = false;
                        }
                        if (!chooseNode.username) {
                            UIToastr.showWarning(LANG.UI_MOTION_VALIDATE_ADMIN, LANG.UI_MOTION_VALIDATE_ADMIN_TIPS);
                            info.config = false;
                        }
                        info.node.id = chooseNode.groupuuid;
                    } else {
                        if (!chooseNode.checkedFlag) {
                            $(".setrecover2tip").html(LANG.UI_RECOVERY_SELECT_HOST).show();
                            info.config = false;
                        }
                    }
                    // 这里判断下是否需要返回驱动检测的结果
                    if (showDriver) {
                        // console.log('source_list', source_list)
                        // console.log('source_list_check', source_list_check)
                        if (source_list != source_list_check) {
                            if ($.isArray(source_list_check)) {
                                // 表示驱动检测后又改动了虚拟机的磁盘或网卡的配置 那么需要重新检测
                                info.config = false;
                                UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_PLATFORM_RECOVERY_CONFIG_CHANGE_TIPS);
                            } else {
                                driverCheck = [];
                            }
                        }
                        info.driverCheck = driverCheck;
                        info.driver_replace_flag = true;
                    } else {
                        if (!CONF.VMTYPE_GROUP.PUBLICCLOUD.includes(chooseNode.hypervisor) && !checkIsChooseItem()) {
                            // not 公有云获取
                            return {config:false};
                        }
                    }

                    if (showDriver && driverCheck.length <= 0) {
                        info.show_fail = true;
                    }
                    // 直接在这里给出提示，然后赋值 config为false
                    if (isPlatformType == true) {
                        // 标记是否异构平台未检测通过
                        if($('#continueCheck').is(':checked')){
                            // 选择继续
                            info.continue_driver = true; // 继续恢复 等后台给出新的消息结构字段
                        } else {
                            // 如果页面的 checkbox的值不是继续恢复，那么就阻止并给出提示
                            UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_PLATFORM_RECOVERY_DRIVER_NO_CONTINUE_TIPS);
                            info.config = false;
                        }
                    }
                    // 不支持替换驱动
                    if (isSupportOs == true) {
                        UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_DRIVER_CHECK_RESULT_NONSUPPORT_OS_INSTALL_DRIVER);
                    }
                    return info;
                }

                return false;
            },
            getVmNameLimit: function () {
                return vmNameLimit;
            }
        };
    }
}();
