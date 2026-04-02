var ExchBackupEdit = function () {
    var data = {src_info:{}};
    var zTree;
    var _timeStamp = '';    //时钟时间戳,全局
    var nodeSelectFlag = true; //第一次加载节点
    var initSpeedFlag = false;
    var speedList = [];
    var oldNode, networkFlag = false;   //用于比对加载传输网络的节点
    var currentmax = 0;
    var globalStrategy = [];
    var pageIndex = 0; //轮播索引
    var initStrategyFlag = false;
    var _oldPassword = '';
    var passwordChangeFlag = false; // 是否改变了密码框内容标记
    var recheckedFlag = false;//是否重新选择了备份对象
    var oldBakObj = [];//原任务的备份对象
    var backupStrategy = {};
    let firstInitPageFlag = false;
    var limit_num = 50;//加载更多条数
    var searchFlag = false;//在搜索
    var batchFlag = false;//批量勾选
    var recursionFlag = false;// 是否在递归请求树节点
    var userGroupTotal = userTotal = 0;//临时存目录下的用户、用户组总数
    var modifyData;//修改任务的所有数据
    var firstSelectAgent = true;//是否是第一次选择agent
    let firstInitStep2 = false;
    let backupTargetInfo = '';
    let firstInitWorm = true;//是否是修改任务第一次初始化worm
    var initData = function () {
        //setp2
        data.high_info = {}
        data.high_info.node = {};
        data.high_info.node = SETTINGS.node;
        //step3
        //备份方式:策略/时间
        data.backup_info = {}
        data.backup_info.type = 'strategy';
        let time = {};
        time.fullInfo = {};
        time.incrInfo = {};
        time.pIncrInfo = {};
        time.datetime = null;
        //完备/增备/永久增量
        data.backup_info.full_info = {};
        data.backup_info.incr_info = {};
        data.backup_info.pincr_info = {};
        //按时间备份的时间
        data.backup_info.datetime = null;
        var timestrategy = SETTINGS.time_strategy.data;
        //按时间备份的时间
        if (SETTINGS.time_strategy.type == "oncetime") {
            data.backup_info.type = 'oncetime';
            data.backup_info.datetime = SETTINGS.time_strategy.data;
            time.datetime = SETTINGS.time_strategy.data;
            time.type = "oncetime";
        } else if (SETTINGS.time_strategy.type == "manual") {
            time.type = "manual";
            data.backup_info.type = "manual";
        } else {
            time.type = "strategy";
            for (var i = 0; i < timestrategy.length; i++) {
                var info = {};
                var timeInfo = {};
                var days = [];
                for (var j = 0; j < timestrategy[i].days.length; j++) {
                    if (timestrategy[i].days.length == 1 && timestrategy[i].days[j] == false) {
                        days = [];
                    } else if (timestrategy[i].days[j] == true) {
                        timestrategy[i].days[j] = 1;
                        days.push(timestrategy[i].days[j]);
                    } else if (timestrategy[i].days[j] == false) {
                        timestrategy[i].days[j] = 0;
                        days.push(timestrategy[i].days[j]);
                    }
                }
                info.days = days;
                info.mode = timestrategy[i].mode;
                info.type = timestrategy[i].strategy_type;
                info.start_time = timestrategy[i].start_time;
                info.roll_flag = timestrategy[i].roll_flag;
                info.roll_interval = timestrategy[i].roll_interval;
                info.end_time = timestrategy[i].roll_end_time;
                info.roll_end_time = timestrategy[i].roll_end_time;
                info.frequency = timestrategy[i].frequency;
                timeInfo.days = days;
                timeInfo.mode = timestrategy[i].mode;
                timeInfo.type = timestrategy[i].strategy_type;
                timeInfo.startTime = timestrategy[i].start_time;
                timeInfo.rollFlag = timestrategy[i].roll_flag;
                timeInfo.rollInterval = timestrategy[i].roll_interval;
                timeInfo.endTime = timestrategy[i].roll_end_time;
                timeInfo.roll_end_time = timestrategy[i].roll_end_time;
                timeInfo.frequency = timestrategy[i].frequency;
				timeInfo.full_backup_compensation_flag = timestrategy[i].full_backup_compensation_flag;
                if (timestrategy[i].mode == '1') {
                    data.backup_info.full_info = info;
                    time.fullInfo = timeInfo;
                } else if (timestrategy[i].mode == '2' || timestrategy[i].mode == '9') {
                    //只有一条则是永久增量
                    if (1 == timestrategy.length) {
                        data.backup_info.pincr_info = info;
                        data.backup_info.pincr_info.mode = '9';
                        time.pIncrInfo = timeInfo;
                        time.pIncrInfo.mode = '9';
                    } else {
                        data.backup_info.incr_info = info;
                        time.incrInfo = timeInfo;
                    }
                }
            }
        }
        //限速策略
        data.speedLimit = {};
        speedList = SETTINGS.speedInfo;
        //存储策略
        data.high_info.store = SETTINGS.bss;
        data.high_info.store.valid = true;
        //保留策略
        data.high_info.reserve = {};
        data.high_info.reserve.type = SETTINGS.brs.type;
        data.high_info.reserve.value = SETTINGS.brs.number;
        data.high_info.reserve.strategyMode = SETTINGS.brs.strategy_mode;
        // 高级策略
        data.high_info.newstr = {};
        data.high_info.newstr.backupThreadNum = SETTINGS.high.thread_num;//线程数量
        // 传输策略
        data.high_info.transfer = {};
        data.high_info.transfer = SETTINGS.bts;
        // 高级配置
        data.high_conf = {};
        data.retry_strategy = SETTINGS.retry_strategy;
        //客户端
        data.high_conf.agent_uuid = SETTINGS.agent_uuid;
        $('#agentConfig').bootstrapSwitch('state', SETTINGS.agent_config_flag);
        $('#agentList').val(SETTINGS.agent_uuid);

        //备份策略
        backupStrategy.time = {};
        backupStrategy.store = {};
        backupStrategy.reserve = {};
        backupStrategy.time.timeInfo = time;
        backupStrategy.time.type = time.type;
        backupStrategy.speedlimit = SETTINGS.speedInfo;
        backupStrategy.store.storeInfo = data.high_info.store;
        backupStrategy.reserve.reserveInfo = data.high_info.reserve;
        firstInitPageFlag = true;
        $('#tab_common').backupStrategy(CONF.MODULE_TYPE.M365, true, backupStrategy);
        //重试策略
		data.retry_strategy = SETTINGS.retry_strategy;
		//安全策略
		data.safe_strategy = SETTINGS.safe_strategy;
        data.safe_strategy.integrity_check_flag = SETTINGS.safe_strategy.integrity_check_flag ? 1 : 0;
		data.safe_strategy.virus_scan_flag = SETTINGS.safe_strategy.virus_scan_flag ? 1 : 0;
		data.safe_strategy.worm_flag = SETTINGS.safe_strategy.worm_flag ? 1 : 0;
		data.safe_strategy.virus_scan_config_list = "";
		data.safe_strategy.integrity_check_config.recovery_error_policy = -1;
		//过载保护
        data.high_info.ignore_resource_limiting_flag = SETTINGS.ignore_resource_limiting_flag
    }

    var initListener = function () {
        $('#toAdd').on('click',function () {
            LOCATION('./content/exchange/exchange_organization.php', 'exchange_organization');
        });
        $('#searchInput').on('keydown', debounce);
        //选择备份策略复选框
        $('#strategymode').find('.icheck').on('ifClicked', strategyModeClick);
        //切换自动重试开关
        $('#autoRetry').on('switchChange.bootstrapSwitch', autoRetryChange);
        //数据加密密码确认
        $('#repassword,#password').on('input propertychange', function () {
            passwordChangeFlag = true;
        });
        //排除目录
        $('#addsubmit').on('click',function () {
            var flag  = toExcludeFolder();
            if (flag) {
                return true;
            } else {
                return false;
            };
        });
        // 压缩传输
        $('#compressCheck').on('switchChange.bootstrapSwitch', compressChange);
        $('#excludeBtn').on('click', function () {
            //勾选排除目录，未点击确定，勾选框需要清除勾选
            $('#drawer-1').find('.icheck').iCheck('uncheck');
            for (var i = 0; i < data.src_info.exclude_folder_list.length; i++) {
                $('#drawer-1').find('.icheck[data-mode="' + data.src_info.exclude_folder_list[i] + '"]').iCheck('check');
            }
        });
        // 传输策略---加密传输
        $('#transport_encrypt_flag').on('switchChange.bootstrapSwitch', transferEncryptChange);
		//切换自动选择存储加密密码
		// $('#passwordAutocheck').on('switchChange.bootstrapSwitch', passwordModeChange);
        $('#batchSubmit').on('click', function () {
            batchSelectObj();
        })
        $('#agentConfig').on('switchChange.bootstrapSwitch', agentConfigChange);
        $('#agentList').on('change', agentListChange);
        //切换时间策略
        $('#backuptype').on('change', backupTypeHandler);
    }
    const backupTypeHandler = function () { 
        if ('oncetime' == this.value) {
            //一次性备份只是按个数保留
            $('#reserveType').prop("disabled", "disabled");
            $('#reserveType').val(1);
            $('.reserveDay').hide();
            $('.reserveNum').show();
            $('#spinnerNum').spinner('value', 1);
            $('#spinnerDay').spinner('value', 1);
            $('#spinnerNum').spinner('disable');
            $('#spinnerDay').spinner('disable');
        }
        initReserveStrategyDes();
    }
    //指定客户端切换
    const agentListChange = function () {
        var selectNetModel = $('#agentList').find("option:selected").attr('net_model');
        if (selectNetModel == 2 && data.high_info.node.node_uuid != "") {
            $('.transfernetworkDiv').show();
        } else {
            $('.transfernetworkDiv').hide();
        }
    }
    // 显示客户端配置
    var agentConfigChange = function () {
        if (this.checked) {
            $('.agentListDiv').show();
            agentListChange();
        } else {
            $('.agentListDiv').hide();
            //指定客户端关闭，根据networkFlag判断是否显示传输网络
            if (networkFlag && data.high_info.node.node_uuid != "") {
                $('.transfernetworkDiv').show();
            } else {
                $('.transfernetworkDiv').hide();
            }
        }
    }
    //批量选中用户 用户组
    var batchSelectObj = function () {
        batchFlag = false;
        var input = $('#batchInput').val().trim();
        var selectNodes = zTree.getCheckedNodes(true);
        var count = 0;//统计选中的节点个数
        // 1. 输入校验增强：增加数值合法性校验，避免非数字输入
        var targetCount = 0;
        if(input === '' || isNaN(parseInt(input)) || (targetCount = parseInt(input)) <= 0 || selectNodes.length <= 0) {
            UIToastr.showWarning(LANG.UI_MICROSOFT365_BATCH_SELECT,LANG.UI_MICROSOFT365_INPUT_VALID);
            return;
        }
        Metronic.blockUI({target: '.exchange_tree_div',animate: true});
        // 2. 改用异步处理，避免主线程阻塞（替换setTimeout，增加错误捕获）
        setTimeout(async function () {
            try {
                batchFlag = true;
                recursionFlag = true;
                recheckedFlag = true;//重新选择了备份对象
                var organizationUuid = selectNodes[0].organization_uuid;
                // 调用修复后的getAllNodes，传入目标数量
                var allNodes = await getAllNodes(targetCount, organizationUuid);
                zTree.checkAllNodes(false);
                // 3. 遍历节点时增加边界保护，避免越界
                var validNodes = allNodes.filter(node => 
                    node.level === 2 && 
                    !node.chkDisabled && 
                    node.organization_uuid === organizationUuid
                );
                // 4. 只选目标数量的节点，避免多余循环
                var selectLimit = Math.min(targetCount, validNodes.length);
                for (var i = 0; i < selectLimit; i++) {
                    var node = validNodes[i];
                    zTree.checkNode(node, true, true);
                    if (!node.getParentNode().open) {
                        zTree.expandNode(node.getParentNode(), true, false, true);
                    }
                    count++;
                }
                //重新计算右边的数量
                showAllBakObj();
            } catch (e) {
                // 增加错误捕获，避免崩溃
                console.error('fail', e);
            } finally {
                recursionFlag = false;
                Metronic.unblockUI('.exchange_tree_div');
            }
        }, 500);
    };
    
    var getAllNodes = async function (targetCount, organizationUuid) {
        var allNodes = zTree.transformToArray(zTree.getNodes());
        var levelTwoNodes = [];
        // 初始筛选level2节点
        levelTwoNodes = allNodes.filter(node => 
            node.level === 2 && !node.more && node.organization_uuid === organizationUuid && !node.chkDisabled
        );
        var userOrganizationExpand = false;//用户组是否展开过
        while (levelTwoNodes.length < targetCount) {
            var hasMoreData = false;
            // 1. 先处理加载更多节点
            const moreNodes = allNodes.filter(node => node.more === true && node.organization_uuid === organizationUuid);
            if (moreNodes.length > 0) {
                // 异步处理加载更多（如果getMoreNode是异步的，需await）
                await getMoreNode(moreNodes[0]);// 假设getMoreNode返回Promise，若不是则改为同步调用
                allNodes = zTree.transformToArray(zTree.getNodes());
                levelTwoNodes = allNodes.filter(node => 
                    node.level === 2 && node.organization_uuid === organizationUuid && !node.chkDisabled
                );
                // 节点足够则退出
                if (levelTwoNodes.length >= targetCount) break;
                hasMoreData = true;
            }
            // 2. 加载更多无数据，处理展开父节点
            if (!hasMoreData && levelTwoNodes.length < targetCount && !userOrganizationExpand) {
                const parentNodes = allNodes.filter(node => 
                    node.level === 1 && !node.children && node.organization_uuid === organizationUuid && !node.chkDisabled
                );
                if (parentNodes.length > 0) {
                    // 异步展开节点（如果nodeExpand是异步的，需await）
                    await nodeExpand(zTree.expandNode, 'exchange_tree', parentNodes[0]);
                    allNodes = zTree.transformToArray(zTree.getNodes());
                    levelTwoNodes = allNodes.filter(node => 
                        node.level === 2 && node.organization_uuid === organizationUuid && !node.chkDisabled
                    );
                    hasMoreData = true;
                    userOrganizationExpand = true;
                } else {
                    break;
                }
            }
            // 无更多数据可加载，退出循环
            if (!hasMoreData) break;
        }
        return allNodes;
    };

	//切换自动选择存储加密密码
	var passwordModeChange = function(){
		if($('#passwordAutocheck').bootstrapSwitch('state')){
		}else {
			if (firstInitPageFlag) {
				passwordChangeFlag = false;
			} else {
				passwordChangeFlag = true;
			}
		}
		firstInitPageFlag = false;
	}
    // 显示加密算法
    var transferEncryptChange = function () {
        if (this.checked) {
            $('.transfer-encrypt-method-form').show();
        } else {
            $('.transfer-encrypt-method-form').hide();
        }
    }
    //初始化保留策略配置信息
    var initReserveStrategyDes = function () {
        var des = "";
        var reserveModeType = parseInt($('#reserveMode').val());
        var type = $('#reserveType').val();
        var value = 0;
        if (reserveModeType === CONF.RESERVE_STRATEGY_MODE.POINT){ // 按备份点保留
            des += LANG.UI_RESERVE_RETENTION_TYPE +'：' + LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_POINT + '，';
        } else { // 按备份链保留
            des += LANG.UI_RESERVE_RETENTION_TYPE +'：' + LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_CHAIN + '，';
        }
        des += LANG.UI_RESERVE_RETENTION_MODE + '：';
        if(CONF.RESERVE_TYPE.NUM == type){
            des += $('#reserveType option:selected').text();
            value = $('#spinnerNumInput').val();
		}else if(CONF.RESERVE_TYPE.DAY == type){
            des += $('#reserveType option:selected').text();
            value = $('#spinnerDayInput').val();
        }else if(CONF.RESERVE_TYPE.PERMANENT == type) {
			des += LANG.UI_FILE_PERMANENT;
            value = '';
		}
        if($('#reserveType').val() !== "3") { //不是永久保留
            if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw" && CONF.RESERVE_TYPE.DAY == type){
                des += "，" + LANG.UI_GLOBAL_STRATEGY_RESERVE_DAY +'：' + value;
            }else{
                des += "，" + LANG.UI_STRATEGY_VALUE +'：' + value;
            }
        }
        $('.reserveDes').html(des);
        $('.reserveDes').attr('title', des);
    }

    var toExcludeFolder = function () {
        var flag = false;//是否有选中的
        var des = '';
        //排除目录
        data.src_info.exclude_folder_list = [];
        var icheckArr = $('#drawer-1').find('.icheck');
        for (var i = 0; i < icheckArr.length; i++) {
            if ($(icheckArr[i]).is(':checked')) {
                data.src_info.exclude_folder_list.push(parseInt($(icheckArr[i]).attr('data-mode')));
                des += $(icheckArr[i]).attr('name') + ';';
                flag = true;
            }
        }
        if (icheckArr.length == data.src_info.exclude_folder_list.length) {
            UIToastr.showWarning(LANG.UI_MICROSOFT365_EXCLUDE_DIR,LANG.UI_MICROSOFT365_EXCLUDE_DIR_TIPS);
            return false;
        }
        $('.excludeDes').html(des);
        if (flag) {
            $('.excludeDiv').show();
        } else {
            $('.excludeDiv').hide();
        }
        return true;
    }

    // 显示压缩等级
    var compressChange = function () {
        if (this.checked) {
            $('.compressGradeDiv').show();
        } else {
            $('.compressGradeDiv').hide();
        }
    };
    /*******************************备份源*********************************/

        //exchange组织树
    var setTree = function (result) {
        Metronic.unblockUI('#exchangebackupcontent');
        //如果结果为空
        if (!result.success) {
            $("#noagent").show();
            $("#exchange_tree").hide();
            return;
        } else {
            $("#noagent").hide();
            $("#exchange_tree").show();
            $(".searchDiv").show();
            $('#nosearchtips').hide();
        }

            var setting = {
                check: {
                    enable: true,
                    nocheckInherit: false,
                    // chkboxType: { Y: 's', N: 's' }//取消选中和选中子节点不联动父节点
                },
                data: {
                    simpleData: {
                        enable: true,
                    },
                    key:{
                        title: "title"
                    }
                },
                callback: {
                    onClick: nodeClick,
                    onCheck: nodeCheck,
                    onExpand: nodeExpand,
                },
                view: {
                    fontCss: setFontCss,
                }
        };
        //预处理修改的数据
        modifyData = result.data;
        zTree = $.fn.zTree.init($('#exchange_tree'), setting, result.data.info);
        userTotal = result.data.user_total ? result.data.user_total : 0;
        userGroupTotal = result.data.user_group_total ? result.data.user_group_total : 0;
        oldBakObj = result.data.edit_bak_info;
        showAllBakObj();
        for (var i =0; i < oldBakObj.length;i++) {
            var node = zTree.getNodesByParam("backup_object_uuid", oldBakObj[i]['backup_object_uuid'], null);
            if (node != null && node.length > 0) {
                zTree.expandNode(node[0].getParentNode(), true, false, true);
                return;
            }
        }
    };

    //设置未授权或者离线节点的样式
    function setFontCss(treeId, treeNode)
    {
        var color = {};
        if (treeNode.bak_exit && !treeNode.forbid_flag) {//在任务中
            color = {"color" : "green","font-weight" : "bold",};
        } else if (treeNode.chkDisabled && treeNode.forbid_flag) {//离线
            color = {"color" : "grey"};
        }
        return color;
    };
    //勾选代理端节点
    var nodeCheck = function (e, treeId, treeNode) {
        if (!checkOrganizationStatus(treeNode)) {
            return;
        }
        diyCheckWay(treeNode)
        recheckedFlag = true;//重新选择了备份对象
        //不能跨组织选择备份对象
        var allNodes = zTree.getCheckedNodes(true);
        for (var i = 0; i < allNodes.length; i++) {
            if (treeNode.organization_uuid != allNodes[i].organization_uuid) {
                zTree.checkNode(allNodes[i], false, false, false);
            }
        }
        batchFlag = false
        nodeExpand(e, treeId, treeNode);
    }
    var diyCheckWay = function (treeNode) {
        var parentNode = treeNode.getParentNode();
        if (!parentNode) {
            return; // 如果没有父节点，则不需要进一步处理
        }
        // 获取所有子节点的勾选状态
        var children = parentNode.children;
        var allChecked = children.every(function (child) {
            return child.checked; });
        var allUnchecked = children.every(function (child) {
            return !child.checked; });
        // 如果所有子节点都被勾选，则勾选父节点；如果所有子节点都未被勾选，则取消勾选父节点
        if (allChecked) {
            zTree.checkNode(parentNode, true, false, false);
        } else if (allUnchecked) {
            zTree.checkNode(parentNode, false, false, false);
        }
        // 递归处理更上一层的父节点
        diyCheckWay(parentNode);
    }

    //点击代理端节点
    var nodeClick = function (e, treeId, treeNode) {
        if (!checkOrganizationStatus(treeNode)) {
            return;
        }
        batchFlag = false
        if (treeNode.more) {//加载更多
            getMoreNode(treeNode);
            return;
        }
        nodeExpand(e, treeId, treeNode);
    }
    var nodeExpand = function (e, treeId, treeNode) {
        showAllBakObj();
        return;
    }
    var getMoreNode = function (treeNode) {
        if (recheckedFlag) {//重新选择了备份对象
            var pNode = treeNode.getParentNode();
            //用户
            if((pNode.check_Child_State == 2 || pNode.check_Child_State == -1) && treeNode.event_type == 1000) {
                modifyData.user_nodes.forEach((item,i) => {
                    modifyData.user_nodes[i].checked = true;
                });
            } else if(treeNode.event_type == 1000) {
                modifyData.user_nodes.forEach((item,i) => {
                    modifyData.user_nodes[i].checked = false;
                });
            }
            //用户组
            if((pNode.check_Child_State == 2 || pNode.check_Child_State == -1) && treeNode.event_type == 1001) {
                modifyData.user_group_nodes.forEach((item,i) => {
                    modifyData.user_group_nodes[i].checked = true;
                });
            } else if(treeNode.event_type == 1001) {
                modifyData.user_group_nodes.forEach((item,i) => {
                    modifyData.user_group_nodes[i].checked = false;
                });
            }
        }
        var addNodes = [];
        switch (treeNode['event_type']) {
            case 1000:
                addNodes = modifyData.user_nodes.slice(0, limit_num);
                modifyData.user_nodes = modifyData.user_nodes.slice(limit_num)
                if(modifyData.user_nodes.length != 0){
                    addNodes = addNodes.concat(treeNode);
                }
                break;
            case 1001:
                addNodes = modifyData.user_group_nodes.slice(0, limit_num);
                modifyData.user_group_nodes = modifyData.user_group_nodes.slice(limit_num)
                if(modifyData.user_group_nodes.length != 0){
                    addNodes = addNodes.concat(treeNode);
                }
                break;
        }
        zTree.removeNode(treeNode);
        zTree.addNodes(treeNode.getParentNode(), addNodes, true);
    }
    //检查组织是否离线或者未授权
    var checkOrganizationStatus = function (treeNode) {
        if (treeNode.chkDisabled) {//离线或者在任务中
            if (treeNode.forbid_flag) {//离线
                return false;
            }
            if (treeNode.bak_exit) {//存在于备份中
                UIToastr.showWarning(LANG.UI_MICROSOFT365_SELECT_BACKUP_RESOURCE, LANG.UI_MICROSOFT365_BACKUP_RESOURCE_IN_TASK);
            } else {
                UIToastr.showWarning(LANG.UI_MICROSOFT365_SELECT_BACKUP_RESOURCE, LANG.UI_MICROSOFT365_CANNOT_EDIT_ORGANIZATION_TIPS);
            }
            return false;
        }
        return true;
    }
    // 统计选中的备份对象
    var showAllBakObj = function () {
        data.src_info.backup_object_info = []
        var organizationNum = 0,userNum = 0, groupNum = 0;
        if (!recheckedFlag) {//没有重新选择备份对象
            var allNodes = oldBakObj;
        } else {
            var allNodes = zTree.getCheckedNodes(true);
        }
        let userDirChecked = false;//用户目录是否全选
        let userGroupDirChecked = false;//用户组目录是否全选
        let userDirCount = 0, userGroupDirCount = 0;//用户目录和用户组目录的全选中数量
        data.count = 0;//已选备份对象总数目
        //默认没全选用户目录和用户组目录
        data.src_info.user_dir_flag = false;
        data.src_info.user_group_dir_flag = false;
        if (allNodes.length == 0) {
            $("#contentdiv").hide();
            $("#step1tips").show();
            $('#step1tips_2').hide();
        } else {
            $("#contentdiv").show();
            $("#step1tips").hide();
            $('#step1tips_2').show();
        }
        // type  0组织  1000用户  1001用户组
        for (var i = 0; i < allNodes.length; i++) {
            data.src_info.organization_uuid = allNodes[i].organization_uuid;
            if (allNodes[i].backup_object_type == 10000) {
                organizationNum++;
                if ((!recheckedFlag || allNodes[i].check_Child_State == 2 || allNodes[i].check_Child_State == -1) && !searchFlag) {//如果组织是全选  就只传组织信息
                    var des = LANG.UI_MICROSOFT365_ORGANIZATION + ": " + 1;
                    $(".selectedDes").html(des);
                    data.src_info.backup_object_info.push({
                        "backup_object_name":allNodes[i].backup_object_name,
                        "backup_object_type":allNodes[i].backup_object_type,
                        "backup_object_uuid":allNodes[i].backup_object_uuid,
                        "backup_object_mail":"",
                        "members":(allNodes[i].members == "[]" || (allNodes[i].members != null && allNodes[i].members.length == 0)) ? "[]" : allNodes[i].members,
                    });
                    data.count = 1;
                    return;
                }
            } else if (!searchFlag && allNodes[i].type == 1 && allNodes[i].event_type == 1000 &&
                (allNodes[i].check_Child_State == 2 || allNodes[i].check_Child_State == -1)) {//选中用户目录
                userDirChecked = true;
                userDirCount = userTotal;
                data.src_info.user_dir_flag = true;
            } else if (!searchFlag && allNodes[i].type == 1 && allNodes[i].event_type == 1001 &&
                (allNodes[i].check_Child_State == 2 || allNodes[i].check_Child_State == -1)) {//选中用户组目录
                userGroupDirChecked = true;
                userGroupDirCount = userGroupTotal;
                data.src_info.user_group_dir_flag = true;
            } else if (allNodes[i].backup_object_type == 1000) {
                userNum++;
                data.src_info.backup_object_info.push({
                    "backup_object_name":allNodes[i].backup_object_name,
                    "backup_object_mail":allNodes[i].backup_object_mail,
                    "backup_object_type":allNodes[i].backup_object_type,
                    "backup_object_uuid":allNodes[i].backup_object_uuid,
                    "members":(allNodes[i].members == "[]" || (allNodes[i].members != null && allNodes[i].members.length == 0)) ? "[]" : allNodes[i].members,
                });
            } else if (allNodes[i].backup_object_type == 1001) {
                if (!Array.isArray(allNodes[i].members)) {
                    allNodes[i].members = JSON.parse(allNodes[i].members);
                }
                groupNum++;
                data.src_info.backup_object_info.push({
                    "backup_object_name":allNodes[i].backup_object_name,
                    "backup_object_mail":allNodes[i].backup_object_mail,
                    "backup_object_type":allNodes[i].backup_object_type,
                    "backup_object_uuid":allNodes[i].backup_object_uuid,
                    "members":(allNodes[i].members == "[]" || (allNodes[i].members != null && allNodes[i].members.length == 0)) ? "[]" : allNodes[i].members,
                });
            }
        }
        var des = '';
        var batchNum = $('#batchInput').val().trim();//批量勾选的数目
        if (userNum != 0 && !userDirChecked) {//选中部分
            des += LANG.UI_MICROSOFT365_USER + ": " + userNum + LANG.UI_MICROSOFT365_USER_UNIT;
            data.count += userNum;
        } else if (userDirChecked) {//选中全部
            if(batchNum != "" && batchNum> 0 && batchFlag) {
                userDirCount = parseInt(batchNum) <= userDirCount ? parseInt(batchNum) : userDirCount;
            }
            des += LANG.UI_MICROSOFT365_USER + ": " + userDirCount + LANG.UI_MICROSOFT365_USER_UNIT;
            data.count += userDirCount;
        }
        if (groupNum != 0 && !userGroupDirChecked) {//选中部分
            des += LANG.UI_MICROSOFT365_USER_GROUP + ": " + groupNum + LANG.UI_MICROSOFT365_USER_UNIT;
            data.count += groupNum;
        } else if (userGroupDirChecked) {//选中全部
            if(batchNum != "" && batchNum> 0 && batchFlag) {
                userGroupDirCount = parseInt(batchNum) <= userGroupDirCount ? parseInt(batchNum) : userGroupDirCount;
            }
            des += LANG.UI_MICROSOFT365_USER_GROUP + ": " + userGroupDirCount + LANG.UI_MICROSOFT365_USER_UNIT;
            data.count += userGroupDirCount;
        }
        $(".selectedDes").html(des);
    }



    function executeAsyncRequest1()
    {
        return new Promise(function (resolve, reject) {
            // 执行第一个异步请求
            // ...

            // 异步请求完成后调用resolve()
            resolve();
        });
    }

    function executeAsyncRequest2()
    {
        // 执行第二个异步请求
        // ...
    }

    executeAsyncRequest1().then(function () {
        // 第一个异步请求完成后执行第二个异步请求
        executeAsyncRequest2();
    });

    //把getTree方法作为参数传入initTree，避免Metronic.blockUI与
    var initTree = function () {
        Metronic.blockUI({target: '#exchangebackupcontent',animate: true,cenrerY: true,});
        var job_uuid = $('#task_uuid').val();
        //获取组织数据
        setTimeout(function (){
            pAjaxRequest({"job_uuid":job_uuid}, "/api/v1/exchange/jobs/" + job_uuid + "/organization" , "GET", setTree, true);
        },500)
    };

    // 防抖
    var timer = null
    var nodeParamList;
    var debounce = function () {
        clearTimeout(timer)
        timer = setTimeout(function () {
            var value = $('#searchInput').val();
            // 输入验证
            if(!customInputValidate('string',value)){
                return false;
            }
            var nodes = zTree.getNodes();
            if (!nodes || nodes.length == 0) {
                return;
            }
            //获取所勾选的
            var checkNode = zTree.getCheckedNodes();
            var checkFsNode = [];
            $.each(checkNode, function (i, v) {
                if (v.type == 1000 || v.type == 1001) {
                    checkFsNode.push(v);
                }
            });
            var allNode = zTree.transformToArray(zTree.getNodes());
            nodeParamList = zTree.getNodesByParamFuzzy('name', value);
            if (nodeParamList.length != 0) {
                zTree.hideNodes(allNode);
                $('#exchange_tree').show();
                $('#nosearchtips').hide();
            } else {
                $('#exchange_tree').hide();
                $('#nosearchtips').show();
            }
            //连接搜索的和所勾选的
            nodeParamList = nodeParamList.concat(checkFsNode);
            nodeParamList = zTree.transformToArray(nodeParamList);
            for (var n in nodeParamList) {
                findParent(zTree,nodeParamList[n], value);
            }
            nodeParamList = $.unique(nodeParamList.sort());
            if (nodeParamList.length == 0) {
                $('#exchange_tree').hide();
                $('#nosearchtips').show();
            }
            zTree.showNodes(nodeParamList);
            showAllBakObj();
        }, 500)
    }
    //找到父节点
    var findParent = function (treeObj,node) {
        var pNode = node.getParentNode();
        if (pNode != null) {
            nodeParamList.push(pNode);
        } else {
            return;
        }
        var pNode = pNode.getParentNode();
        if (pNode != null) {
            nodeParamList.push(pNode);
        }
    }

    //排除目录
    data.src_info.exclude_folder_list = [];

    //初始化历史任务信息
    var initOldSettings = function () {
        var jobs_uuid = $('#task_uuid').val();
        pAjaxRequest({}, "/api/v1/exchange/jobs/" + jobs_uuid + "/backup/info", "GET", function (result) {
            SETTINGS = result.data;
            initData();
            initStep1Settings();
            initStep2Settings();
            initStep3Settings();
            initSpinner();
            initStrategySelect(); //初始化全局策略列表
            initBackupTarget();
        }, false);
    }

    //初始化第一步任务信息
    var initStep1Settings = function () {
        //选中的备份对象加入统计
        for (var i = 0; i < SETTINGS.exclude_folders.length; i++) {
            $('#drawer-1').find('.icheck[data-mode="' + SETTINGS.exclude_folders[i] + '"]').iCheck('check');
        }
        toExcludeFolder();
    }

    //初始化第二步任务信息
    var initStep2Settings = function () {

    }

    //初始化第三步任务信息
    var initStep3Settings = function () {
		if(SETTINGS.bss.encrypt) {
			if (!SETTINGS.bss.password_auto_flag) {
			} else {
				// 自动生成密码默认为checked，首次进入页面不会触发change事件，因此手动触发一次从而改变firstInitPageFlag状态
				passwordModeChange();
			}
		}
        //传输策略bts
        $('#transport_encrypt_flag').bootstrapSwitch('state', SETTINGS.bts.encrypt);
        // 传输加密算法
        if (SETTINGS.bts.encrypt) {
            $('.transfer-encrypt-method-form').show();
        }
        if (SETTINGS.bts.encrypt_method) {
            $('#transferEncryptMethod').val(SETTINGS.bts.encrypt_method);
        }
        //重试策略
        $('#retry_config').retryStrategy({'retry_strategy': SETTINGS.retry_strategy},'edit');
        //高级策略
        $('.backupThreadDiv').spinner("value", SETTINGS.high.thread_num);
        //任务名
        $('#jobname').val(SETTINGS.job_name);
        //任务UUID
        data.job_uuid = SETTINGS.job_uuid;

        //限速策略
        initSpeedStrategyDes();
       
        //保留策略brs
        $('#reserveType').val(SETTINGS.brs.type);
        data.high_info.reserve.type = SETTINGS.brs.type;
        if (CONF.RESERVE_TYPE.PERMANENT == data.high_info.reserve.type) {
            $('.reserveNum').hide();
            $('.reserveDay').hide();
            $('#reserveType').append('<option value="3" selected>' + LANG.UI_FILE_PERMANENT + '</option>');
            $('#reserveType').prop("disabled","disabled");
            //描述修改为增量的
            $('.reserveTips1').hide();
            $('.reserveTips2').show();
        }
        initReserveStrategyDes();
        //安全策略
        let complate_info = [];
        complate_info.push(SETTINGS.safe_strategy.integrity_check_config.check_strategy);
        complate_info.push(SETTINGS.safe_strategy.integrity_check_config.full_error_policy);
        complate_info.push(SETTINGS.safe_strategy.integrity_check_config.inc_error_policy);
        // 初始化安全策略
		$('#completeConfig').completeDetectionBackup('col-md-3', SETTINGS.safe_strategy.integrity_check_flag,CONF.MODULE_TYPE.M365, false, complate_info[0], complate_info[1], complate_info[2]);
        //过载保护-忽略节点资源限制
		$('#ignoreResourceLimit').bootstrapSwitch('state', SETTINGS.ignore_resource_limiting_flag);
    }

    //初始化时间策略描述
    var initSpeedStrategyDes = function () {
        var titleDes = "";
        var des = "";
        $('#tasklevelselect').val(speedList.level);
        $('#speedtypeselect').val(speedList.type);
        if (speedList.type == 1) {
            $('#show_type_1').show();
            $('#show_type_2').hide();
            $('#task_type_global_speed_strategy').show();
        } else {
            $('#show_type_2').show();
            $('#show_type_1').hide();
            $('#task_type_global_speed_strategy').hide();
        }

        // 如果是之前的自定义的 方式不变 但是如果是选择的全局限速策略的话，那么需要读取出所有的全局限速策略列表，然后根据列表的id取出限速信息
        if (speedList.type == 1) {
            var global_speed_limit = speedList.uuid
            setTimeout(function () {
                $(".strategy-table #table").bootstrapTable('checkBy', {
                    field: 'uuid',
                    values: [global_speed_limit]
                })
                // 下面的需要初始化全局限速策略表格并携带参数
                let rowData = $(".strategy-table #table").bootstrapTable("getData");

                var datas = [];
                for (var j in rowData) {
                    if (rowData[j].uuid == global_speed_limit) {
                        datas = rowData[j].detail;
                        datas = datas.split('</br>');
                    }
                }
                if (datas.length != 0) {
                    des += LANG.UI_GLOBAL_STRATEGY_SPEED_NUM + ": " + datas.length;
                }

                for (var i in datas) {
                    titleDes += datas[i] + '. ';
                }

                $('.speedlimitDes').html(des);
                $('.speedlimitDes').prop('title', titleDes);
            },50);
        } else {
            var speedInfo = speedList.speedInfo;
            // 自定义
            if (speedInfo.length > 0) {
                des += LANG.UI_GLOBAL_STRATEGY_SPEED_NUM + ": " + speedInfo.length;
                var strategy_type = speedList.strategy_type;
                for (var i in speedInfo) {
                    titleDes += speedInfo[i].des + '. ';
                }
                addGlobalStrategy.init({'strategy_type':strategy_type,speedInfo:speedInfo,initSpeedFlag:1});
            }
        }
        $('.speedlimitDes').html(des);
        $('.speedlimitDes').prop('title', titleDes);
    }

    //  初始化存储策略配置信息
    var initStoreStrategyDes = function () {
        var des = "";
//      if(authFun.length != 0 && authFun.dedupication){
        //  des += LANG.UI_GLOBAL_STRATEGY_DEDUPULICATION + ": " + getSwitchDes($('#deduplicationcheck').get(0).checked) + ", ";
//      }
        des += LANG.UI_GLOBAL_STRATEGY_COMPRESS + ": " + getSwitchDes($('#compressCheck').get(0).checked) + ", ";
        // 压缩等级
        if ($('#compressCheck').get(0).checked) {
            var gradeValue = $('#compressGrade').val();
            var grade = '';
            switch (parseInt(gradeValue,10)) {
                case 1:
                    grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_FAST;
                    break;
                case 2:
                    grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_NORMAL;
                    break;
                case 3:
                    grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_BETTER;
                    break;
                case 4:
                    grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_BEST;
                    break;
            };
            des += "," + LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE + ": " + grade + ",";
        }
        des += LANG.UI_BACKUP_DATA_ENCRYPT + ": " + getSwitchDes($('#encryptStorageCheck').get(0).checked);
        // 存储加密算法
        if ($('#encryptStorageCheck').get(0).checked) {
            var encryptedMethodLabel = $('.storage-encrypt-label').html();
            let method = $('#storageEncryptMethod').val();
            var grade = '';
            switch (parseInt(method)) {
                case 1:
                    grade = LANG.UI_GLOBAL_STRATEGY_STORAGE_ENCRYPT_AES;
                    break;
                case 2:
                    grade = LANG.UI_GLOBAL_STRATEGY_STORAGE_ENCRYPT_SM;
                    break;
            };
            des += "," + encryptedMethodLabel + ": " + grade;
        }
        $('.storeDes').html(des);
        $('.storeDes').attr('title', des);
    }


    //第一步数据检测以及一些数据的初始化
    var step1Valid = function (showTitleCallback) {
        //exchange仅重试任务中失败的对象
        $('#task_retry_object').find("option[value=1]").remove();
        var nodes = zTree.getCheckedNodes(true);
        var agentList = [];
        data.showAgentFlag = false;//是否显示选择客户端
        if (!data.count) {
            UIToastr.showWarning(LANG.UI_MICROSOFT365_NO_BAK_RESOURCE, LANG.UI_MICROSOFT365_PLEASE_SELECT);
            return false;
        }
        $('.transport-encrypt-div').hide();
        $('.transferinfoshow').hide();
        $('.transfer-encrypt-method-form').hide();
        $('.transferLi').hide();
		$('.transferDiv').hide();
        data.transferlishow  = false;
        data.region = 'exchange_online';//组织的类型，授权使用
        for (var i = 0; i < nodes.length; i++) {
            if (nodes[i].type == 10000 && nodes[i].region == 100) {//组织
                if (nodes[i].agent_list > 1) {//多个客户端直接不显示传输网络
                    networkFlag = false;
                } else {
                    networkFlag = nodes[i].net_model;   //是否显示传输网络
                }
                //显示加密传输
                $('.transport-encrypt-div').show();
                $('.transferinfoshow').show();
                if ($('#transport_encrypt_flag').get(0).checked) {
                    $('.transfer-encrypt-method-form').show();
                }
                //显示选择客户端
                data.showAgentFlag = true;//是否显示选择客户端
                agentList = nodes[i].agent_list;
                data.transferlishow  = true;
                data.region = 'exchange';//组织的类型，授权使用
            } else {
                if (nodes[i].type == 1000 || nodes[i].type == 1001) {//用户或者用户组
                    var pNode = nodes[i].getParentNode().getParentNode()
                    data.region = 'exchange';//组织的类型，授权使用
                    if (pNode.region == 100) {
                        //显示加密传输
                        $('.transport-encrypt-div').show();
                        $('.transferinfoshow').show();
                        if ($('#transport_encrypt_flag').get(0).checked) {
                            $('.transfer-encrypt-method-form').show();
                        }
                        //显示选择客户端
                        data.showAgentFlag = true;//是否显示选择客户端
                        agentList = pNode.agent_list;
                        data.transferlishow  = true;
                    }
                } else if (nodes[i].type == 1) {
                    var pNode = nodes[i].getParentNode();
                    if (pNode.region == 100) {
                        //显示加密传输
                        $('.transport-encrypt-div').show();
                        if ($('#transport_encrypt_flag').get(0).checked) {
                            $('.transfer-encrypt-method-form').show();
                        }
                        //显示选择客户端
                        data.showAgentFlag = true;//是否显示选择客户端
                        agentList = pnode.agent_list;
                    }
                }
            }
            // 传输网络
            if (nodes[i].type == 10000) {//组织
                if (nodes[i].agent_list > 1) {//多个客户端直接不显示传输网络
                    networkFlag = false;
                } else {
                    networkFlag = nodes[i].net_model;   //是否显示传输网络
                }
                break;
            } else if (nodes[i].type == 1000 || nodes[i].type == 1001) {
                var pnode = nodes[i].getParentNode().getParentNode();
                if (pnode.agent_list > 1) {//多个客户端直接不显示传输网络
                    networkFlag = false;
                } else {
                    networkFlag = pnode.net_model;   //是否显示传输网络
                }
                break;
            }
        }
         //如果多线程已授权或者其他传输策略信息是显示的，显示传输策略
		if(CONF.FUNCTIONS.includes('multithread') || data.transferlishow){
			$('.transferLi').show();
			$('.transferDiv').show();
		}
        $('.tab3-nav > li').removeClass('active');
		$('.tab3-nav > li').eq(0).addClass('active');
		$('.tab3-content > .tab-pane').removeClass('active in');
		$('.tab3-content > .tab-pane').eq(0).addClass('active in');
        //初始化时间策略模块
        Strategy.initModule(ExchBackupEdit);
        //初始化策略描述
        if (data.showAgentFlag) {
            initServerAgent(agentList);
        }
        showStep1();
        if (data.src_info.backup_object_info[0].backup_object_type == 10000 || data.src_info.backup_object_info[0].backup_object_type == 100) { 
            pAjaxRequest({
                "organization_uuid": data.src_info.backup_object_info[0].backup_object_uuid,
                "job_uuid": data.job_uuid
            },
            "/api/v1/exchange/user_num", "GET", function (result) {//获取组织下用户
                if (result.success) {
                    let currentUse = result.data;
                    getM365CurrentUseLicense(currentUse).then(showTitleCallback); 
                }
            }, true);
        } else {
            currentUse = data.src_info.backup_object_info.length;
            getM365CurrentUseLicense(currentUse).then(showTitleCallback); 
        }
		return false;
    }

    //第一步数据展示
    var showStep1 = function () {
        //任务名
        if ($.trim($('#jobname').val()) == "") {
            pAjaxRequest({}, "/api/v1/exchange/jobs/backup/task_name", "GET", function (result) {
                if (result.success) {
                    $('#jobname').val(result.data);
                }
            }, false);
        }
        //数据源
        var excludeDes = LANG.UI_PUBLIC_NOTHING;
        if ($('.excludeDes').html() != '') {
            excludeDes = $('.excludeDes').html();
        }
        var datades = LANG.UI_MICROSOFT365_SELECTED + $('.selectedDes').html() + '<br>' + LANG.UI_MICROSOFT365_EXCLUDE_DIR_NEW + excludeDes;
        $("#srcDatalist").html(datades);
        //初始化计时器
        initServerTime();
    }

    var initServerAgent = function (agentList) {
        $('.agentConfigDiv').show();
        pAjaxRequest({}, "/api/v1/office365/organization/agent", "GET", function (result) {
            $('#agentList').html(result.data);
            $('#agentList option').filter(function() {
                return !agentList.includes($(this).val());
            }).remove();
            if (firstSelectAgent) {
                $('#agentList').val(SETTINGS.agent_uuid);
            }
            firstSelectAgent = false;
            if (SETTINGS.agent_config_flag) {
                $('.agentListDiv').show();
            } else {
                $('.agentListDiv').hide();
                $("#agentList option").eq(0).prop('selected', true);
            }
        });
    }

    /*******************************备份目的地*********************************/


    /*******************************备份策略*********************************/

    var strategyModeClick = function (event) {
        var mode = $(this).data('mode');
        if (event.target.checked) {
            //如果是取消选中
            $('#stragegyaccordion').find('.strategy-panel[data-mode=' + mode + ']').hide();
        } else {
            //如果是选中
            $('#stragegyaccordion').find('.strategy-panel[data-mode=' + mode + ']').show();
        }
        $('.reserveNum').show();
        $('.reserveDay').hide();
        $('#reserveType').removeAttr("disabled");
        $('#reserveType option[value="3"]').remove();
        $('#reserveType').val(1);
        if (1 == mode) {
            //完全备份
            if (event.target.checked) {
                //取消完备同时取消增量
                $('#strategymode').find('input[data-mode=2]').iCheck('uncheck');
                $('#stragegyaccordion').find('.strategy-panel[data-mode=2]').hide();
            } else {
                //选中完备同时取消永久增量
                $('#strategymode').find('input[data-mode=9]').iCheck('uncheck');
                $('#stragegyaccordion').find('.strategy-panel[data-mode=9]').hide();
                $('.reserveTips1').show();
                $('.reserveTips2').hide();
            }
        } else if (2 == mode) {
            //增量备份
            if (!event.target.checked) {
                //选中增量同时选中完备 取消永久增量
                $('#strategymode').find('input[data-mode=1]').iCheck('check');
                $('#stragegyaccordion').find('.strategy-panel[data-mode=1]').show();
                $('#strategymode').find('input[data-mode=9]').iCheck('uncheck');
                $('#stragegyaccordion').find('.strategy-panel[data-mode=9]').hide();
                $('.reserveTips1').show();
                $('.reserveTips2').hide();
            }
        } else if (9 == mode) {
            //永久增量
            if (!event.target.checked) {
                //选中永久增量其他全部取消
                $('#strategymode').find('input[data-mode=1]').iCheck('uncheck');
                $('#stragegyaccordion').find('.strategy-panel[data-mode=1]').hide();
                $('#strategymode').find('input[data-mode=2]').iCheck('uncheck');
                $('#stragegyaccordion').find('.strategy-panel[data-mode=2]').hide();
                $('#strategymode').find('input[data-mode=3]').iCheck('uncheck');
                $('#stragegyaccordion').find('.strategy-panel[data-mode=3]').hide();
                //保留策略只能选永久增量
                $('.reserveNum').hide();
                $('.reserveDay').hide();
                // 默认保留策略为永久保留
                $('#reserveType').append('<option value="3" selected>' + LANG.UI_FILE_PERMANENT + '</option>');
                $('#reserveType').prop("disabled","disabled");
                //描述修改为增量的
                $('.reserveTips1').hide();
                $('.reserveTips2').show();
            }
        }
        initReserveStrategyDes();
        setTimeout(initWormConfig, 0);
    }


    var autoRetryChange = function () {
        if (this.checked) {
            $('.retryTimeDiv').show();
            $('.retryNumDiv').show();
        } else {
            $('.retryTimeDiv').hide();
            $('.retryNumDiv').hide();
        }
    }

    var checkTime = function (start,end) {
        var startnum = new Date("1970-01-01" + " " + start).getTime();
        var endnum = new Date("1970-01-01" + " " + end).getTime();
        if (endnum <= startnum && $("#speedModeType").val() == 1) {//按策略限速才判断
            UIToastr.showWarning(LANG.UI_FILE_CHECK_TIME_RANGE, LANG.UI_FILE_CHECK_TIME_RANGE_TIPS);
            return false;
        } else {
            return true;
        }
    }

    /**
	 * 初始化备份目的地
	 */
	const initBackupTarget = () => {
        if(firstInitStep2) return;
		$('#backupTarget').backupTarget({
			node_uuid: SETTINGS.node.node_uuid,
			node_pool_uuid: SETTINGS.node.node_pool_uuid,
			storage_uuid: SETTINGS.node.storage_uuid,
			storage_pool_uuid: SETTINGS.node.storage_pool_uuid,
			storage_pool_type: SETTINGS.node.storage_pool_type,
		});
        firstInitStep2 = true;
	};

    var initSpinner = function () {
        $('.backupThreadDiv').spinner({value:3, step: 1, min: 1, max: 16});
        $('#retryTime').spinner({value:5, step: 5, min: 1, max: 30});
        $('#retryNum').spinner({value:3, step: 1, min: 1, max: 5});
    }

    //初始化时间计时器
    var initServerTime = function (data) {
        var getDate = function (unix) {
            var polishing = function (d) {
                return d < 10 ? '0' + d : d;
            }
            var date = new Date(parseInt(unix) * 1000);
            Y = date.getFullYear() + '-';
            M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '-';
            D = polishing(date.getDate()) + ' ';
            h = polishing(date.getHours()) + ':';
            m = polishing(date.getMinutes()) + ':';
            s = polishing(date.getSeconds());
            return Y + M + D + h + m + s;
        }
        var servertime = $('#servertime');
        var updateInterval = 1000;
        var startClock = function () {
            if (0 == $('#servertime').size()) {
                clearTimeout(timerTask.VMBackup_serverTime);
                return;
            }
            servertime.html(getDate(_timeStamp++));
            timerTask.VMBackup_serverTime = setTimeout(startClock, updateInterval);
        }
        if (_timeStamp) {
            return;
        }
        $.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM,f:'getSystemTime',p:{}}, function (data) {
            _timeStamp = data;
            startClock();
        });
    }



    var step2Valid = function () {
        if (!$('#backupTarget').backupTarget('validateSelect')) {
			return false;
		}
		showStep2();
		return true;
    }

    var showStep2 = function () {
        //备份目的地(节点)
        backupTargetInfo = $('#backupTarget').backupTarget('getSelect');
		data.high_info.node.nodecheck = !backupTargetInfo.node_uuid;
		data.high_info.node.storagecheck = !backupTargetInfo.storage_uuid;
		data.high_info.node.storage_uuid = backupTargetInfo.storage_uuid;
		data.high_info.node.storage_pool_uuid = backupTargetInfo.storage_pool_uuid;
		data.high_info.node.node_uuid = backupTargetInfo.node_uuid;
		data.high_info.node.node_pool_uuid = backupTargetInfo.node_pool_uuid;
		data.high_info.node.storage_type = backupTargetInfo.storage_type;
        initResourceLimit(backupTargetInfo.node_uuid_list);
		$('.nodeInfoShow').html(backupTargetInfo.node_text);
		$('.storageInfoShow').html(backupTargetInfo.storage_text);
        //初始化传输网络
		if(networkFlag && data.high_info.node.node_uuid != ""){
			$('.transfernetworkDiv').show();
			initNetworkList(backupTargetInfo.node_uuid);
		}else{
			$('.transfernetworkDiv').hide();
		}
        //判断是否是磁带，参数：存储uuid，存储类型，线程配置项div控制显示隐藏，线程输入框初始化spinner时的dom，初始化备份策略时的dom，模块类型
        getTapeStrategy(data.high_info.node.storage_uuid, data.high_info.node.storage_type, '.threadDiv', '.backupThreadDiv');
        if(data.high_info.node.storage_type == CONF.BD_STORAGE_TYPE.TAPE) {//磁带隐藏永久增量选项
            data.high_info.newstr.backupThreadNum = 1;
            //取消选中永久增量并隐藏策略显示
            $('#strategymode').find('input[data-mode=9]').iCheck('uncheck');
            $('#backupTimestrategy').find('.strategy-panel[data-mode=9]').hide();
            $('.pIncr').hide();
            $('.time-strategy-tips').hide();
            $('.no-full-pIncr-tips').show();
            //磁带屏蔽安全策略
			$('.safeLi').removeClass('active').hide();
            $('#tab_safety').removeClass('active');
			$('.transferLi').removeClass('active');
            $('#tab_transfer').removeClass('active');
            $('.highLi').removeClass('active');
            $('#tab_other').removeClass('active');
		    $('.commonLi').removeClass('active').addClass('active');
            $('#tab_common').removeClass('active').addClass('active');
			$('.safeDiv').hide();
            if (!data.transferlishow) {
                $('.transferLi').hide();
                $('.transferDiv').hide();  
            }
        } else {
            $('.pIncr').show();
            $('.time-strategy-tips').hide();
            $('.no-diff-tips').show();
            $('.safeLi').show();
			$('.safeDiv').show();
        }
        initWormConfig();
    }
    var initWormConfig = function() {
        // 安全策略未授权隐藏
        if (!CONF.FUNCTIONS.includes('worm')) {
            $('#wormConfig').hide();
        }
        if (!CONF.FUNCTIONS.includes('integrity')) {
            $('#completeConfig').hide();
        }
        if (!CONF.FUNCTIONS.includes('worm') && !CONF.FUNCTIONS.includes('integrity')) {
            $('.safeLi').hide();
        }
		let pIncrBackup = $('#pincrBackup').prop('checked');  // 永久增量是否选中
        if ($('#backuptype').val() === 'strategy' && pIncrBackup) {  // 按策略备份并选择了永久增量
            $('#wormConfig').wormProtectionBackup($.fn.wormDefine.worm_type.p_incr_backup);
            return;
        }
		if (!backupTargetInfo.storage_uuid) {  // 没有选择存储
            $('#wormConfig').wormProtectionBackup($.fn.wormDefine.worm_type.no_storage);
        } else {
            if (!backupTargetInfo.storage_worm_config.flag) {  // 存储未开启worm
                $('#wormConfig').wormProtectionBackup($.fn.wormDefine.worm_type.no_worm);
            }else if (firstInitWorm){
				$('#wormConfig').wormProtectionBackup(true, 'col-md-3', false, SETTINGS.safe_strategy.worm_flag, SETTINGS.safe_strategy.worm_protection_time);
				firstInitWorm = false;
			} else {
				$('#wormConfig').wormProtectionBackup($.fn.wormDefine.worm_type.normal);
			}
        } 
	}

    var step3Valid = function () {
        var result = getTimeStr() & getTransferStr() & getSpeedStr() & getStoreStr() & getHighConf() & getReserveStr() & getSafeStr();
        if (result) {
            var strategyMode = $('#strategymode').find('input:checked');
            return showStep3(strategyMode);
        }
        return result;
    }

    //得到保留策略
    var getReserveStr = function () {
        data.high_info.reserve.type = $('#reserveType').val();
        data.high_info.reserve.strategyMode = $('#reserveMode').val();
        if (CONF.RESERVE_TYPE.NUM == data.high_info.reserve.type) {
            data.high_info.reserve.value = $('#spinnerNumInput').val();
        } else if (CONF.RESERVE_TYPE.DAY == data.high_info.reserve.type) {
            data.high_info.reserve.value = $('#spinnerDayInput').val();
        } else if (CONF.RESERVE_TYPE.PERMANENT == data.high_info.reserve.type) {
            data.high_info.reserve.value = '';
        }
        if (CONF.RESERVE_TYPE.PERMANENT != data.high_info.reserve.type) {
            if (data.high_info.reserve.value > 0) {
                $('.setreservtip').hide();
                return true;
            } else {
                UIToastr.showInfo(LANG.UI_STRATEGY_RESERVE, LANG.UI_BACKUP_RESERVE_TIPS);
                return false;
            }
        } else {
            return true;
        }
    }

    var showStep3 = function (strategyMode) {
        //传输策略
        let transDes = '';
        transDes += LANG.UI_COPY_BACK_ENCRYPT + ': ' + getSwitchDes(data.high_info.transfer.encrypt) + "<br>";
        // 传输加密算法
        if ($('#transport_encrypt_flag').get(0).checked) {
            let encryptedMethodLabel = $('.transfer-encrypt-method-label').html();
            let method = parseInt($('#transferEncryptMethod').val());
            let grade = '';
            switch (method) {
                case 1:
                    grade = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_RSA;
                    break;
                case 2:
                    grade = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_SM;
                    break;
            };
            transDes += encryptedMethodLabel + ": " + grade + "<br>";
        }
        $('.transferinfoshow').html(transDes);
        //节点传输网络信息显示
        if ($('.transfernetworkDiv').css('display') == 'block') {
            let networkNode = $('#transferNetworkTree').transferNetwork('getSelect');
			$('.applianceshow').html($('.transfernetworklabel').html() + ": " + networkNode.str + '<br>');
            $('.applianceshow').show();
        } else {
            $('.applianceshow').hide();
        }
        if(CONF.FUNCTIONS.includes('multithread')){
            //传输线程
            data.high_info.newstr.backupThreadNum = $('#backupThreadNum').val();//线程数量
            if (data.high_info.newstr.backupThreadNum == "" || data.high_info.newstr.backupThreadNum > 16 || data.high_info.newstr.backupThreadNum <= 0
            || !/^\d+$/.test(data.high_info.newstr.backupThreadNum)) {
                //重置为默认值
                $('.backupThreadDiv').spinner("value", 3);
                UIToastr.showWarning(LANG.UI_BACKUP_HIGH_SETTING_TITLE, LANG.UI_MICROSOFT365_THREAD_TIPS);
                return false;
            }
            var backupmode3Info = $('.threadnumlabel').html() + ": " + data.high_info.newstr.backupThreadNum;
            $('.backupmodeshow3').html(backupmode3Info);
       } else {
           data.high_info.newstr.backupThreadNum = 1;
       }
        data.high_info.store.compress = $('#compressCheck').get(0).checked;//压缩
        // 压缩等级
        if ($('#compressCheck').get(0).checked) {
            data.high_info.store.compress_method = parseInt($('#compressGrade').val());
        }
        data.high_info.store.dataencrypt = $('#encryptStorageCheck').get(0).checked; //数据加密
        //存储策略
        var storeInfo = "";
        // 压缩存储
        storeInfo += $('.compressLabel').html() + ": " + getSwitchDes(data.high_info.store.compress) + "<br>";
        // 压缩等级
        if (data.high_info.store.compress) {
            let gradeValue = $('#compressGrade').val();
            let grade = '';
            switch (parseInt(gradeValue,10)) {
                case 1:
                    grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_FAST;
                    break;
                case 2:
                    grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_NORMAL;
                    break;
                case 3:
                    grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_BETTER;
                    break;
                case 4:
                    grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_BEST;
                    break;
            };
            storeInfo += LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE + ": " + grade + "<br>";
        }
        storeInfo += $('.encryptStorageLabel').html() + ": " + getSwitchDes(data.high_info.store.dataencrypt) ;
        // 存储加密
        if ($('#encryptStorageCheck').get(0).checked) {
            data.high_info.store.encrypt_method = parseInt($('#storageEncryptMethod').val());
            let encryptedMethodLabel = $('.storage-encrypt-label').html();
            let method = $('#storageEncryptMethod').val();
            let grade = '';
            switch (parseInt(method)) {
                case 1:
                    grade = LANG.UI_GLOBAL_STRATEGY_STORAGE_ENCRYPT_AES;
                    break;
                case 2:
                    grade = LANG.UI_GLOBAL_STRATEGY_STORAGE_ENCRYPT_SM;
                    break;
            };
            storeInfo += "<br>" + encryptedMethodLabel + ": " + grade;
        } else {
            data.high_info.store.encrypt_method = "";
        }
        if (data.high_info.store.dataencrypt) {
            storeInfo += "<br>" + $('.passwordAutoLabel').html() + ": " + getSwitchDes(data.high_info.store.password_auto_flag);
        }
        $('.storageinfoshow').html(storeInfo);
        //限速策略
        var speedlimitshow = $('.speedlimitshow');
        var speedLimitsStr = '';
        if (data.speedLimit.speedInfo.length != 0) {
            speedLimitsStr = '';
            for (let i = 0; i < data.speedLimit.speedInfo.length; i++) {
                speedLimitsStr += data.speedLimit.speedInfo[i].des + '<br>';
            }
        }
        if (speedLimitsStr == '') {
            speedLimitsStr = LANG.UI_PUBLIC_NOTHING;
        }
        speedlimitshow.html(speedLimitsStr);
        //时间策略
        $('.backupTypeInfoDiv').show();
        var backuptypeshow = $('.backuptypeshow'), backuptypeinfoshow = $('.backuptypeinfoshow');
        var backuptypeshowStr = '', backuptypeinfoshowStr = '';
        if ("strategy" == data.backup_info.type) {
            backuptypeshowStr = LANG.UI_BACKUP_USE_STRATEGY;
            for (var i = 0; i < strategyMode.length; i++) {
                if (1 == $(strategyMode[i]).data('mode')) {
                    backuptypeinfoshowStr += data.backup_info.full_info.des + "<br>";
                } else if (2 == $(strategyMode[i]).data('mode')) {
                    backuptypeinfoshowStr += data.backup_info.incr_info.des + "<br>";
                } else if (9 == $(strategyMode[i]).data('mode')) {
                    backuptypeinfoshowStr += data.backup_info.pincr_info.des + "<br>";
                }
            }
        } else if ("oncetime" == data.backup_info.type) {
            backuptypeshowStr = LANG.UI_BACKUP_ONCE;
            backuptypeinfoshowStr = LANG.UI_PUBLIC_START_TIME + ": " + data.backup_info.datetime;
        } else if ('manual' === data.backup_info.type) {
            backuptypeshowStr = LANG.UI_BACKUP_MANUAL;
            $('.backupTypeInfoDiv').hide();
        }
        //高级配置
        // if (data.high_conf.retry_flag) {
        //     $('.retrytimeshow').show();
        //     $('.retrynumshow').show();
        //     $('.retrytimeshow').html($('.retrytimelabel').html() + '：' + data.high_conf.retry_delay_time / 60 + LANG.UI_PUBLIC_MINUTE);
        //     $('.retrynumshow').html($('.retrynumlabel').html() + '：' + data.high_conf.retry_count + LANG.UI_VOL_CDP_BACKUP_TIMES);
        // } else {
        //     $('.retrytimeshow').hide();
        //     $('.retrynumshow').hide();
        // }
        //客户端配置
        if (data.showAgentFlag && data.high_conf.agent_uuid != "") {
            $('.agentconfigshow').show();
            $('.agentlistshow').show();
            $('.agentconfigshow').html($('.agentConfiglabel').html() + '：' + LANG.UI_PUBLIC_ON + '<br>');
            $('.agentlistshow').html($('.agentListLabel').html() + '：' + $('#agentList option:selected').text() + '<br>');
        } else if (data.showAgentFlag && data.high_conf.agent_uuid == "") {
            $('.agentconfigshow').show();
            $('.agentlistshow').hide();
            $('.agentconfigshow').html($('.agentConfiglabel').html() + '：' + LANG.UI_PUBLIC_OFF + '<br>');
        } else {
            $('.agentconfigshow').hide();
            $('.agentlistshow').hide();
        }
        $('.autoretryshow').html($('.autoretrylabel').html() + '：' + getSwitchDes(data.high_conf.retry_flag));
        backuptypeshow.html(backuptypeshowStr);
        backuptypeinfoshow.html(backuptypeinfoshowStr);
        //保留策略
        if(data.high_info.node.storage_type != 10) {
//---------------------------------保留策略----------------------------------------------------
			var reservetypeStr = '';
            if(CONF.RESERVE_STRATEGY_MODE.POINT == data.high_info.reserve.strategyMode){
				reservetypeStr = LANG.UI_RESERVE_RETENTION_TYPE + ': '+ LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_POINT + '<br>';
			}else if(CONF.RESERVE_STRATEGY_MODE.CHIAN == data.high_info.reserve.strategyMode){
				reservetypeStr = LANG.UI_RESERVE_RETENTION_TYPE + ': ' + LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_CHAIN + '<br>';
			}
            if(CONF.RESERVE_TYPE.NUM == data.high_info.reserve.type){
                reservetypeStr += LANG.UI_RESERVE_RETENTION_MODE + ': ' + LANG.UI_BACKUP_NUM + '<br>';
            }else if(CONF.RESERVE_TYPE.DAY == data.high_info.reserve.type){
                reservetypeStr +=  LANG.UI_RESERVE_RETENTION_MODE + ': ' + LANG.UI_BACKUP_DAY + '<br>';
            }else if(CONF.RESERVE_TYPE.PERMANENT == data.high_info.reserve.type) {
                reservetypeStr += LANG.UI_FILE_PERMANENT + '<br>';
            }
            var methoddes = LANG.UI_STRATEGY_VALUE;
            if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw" && CONF.RESERVE_TYPE.DAY == data.high_info.reserve.type){
                methoddes = LANG.UI_GLOBAL_STRATEGY_RESERVE_DAY
            }
            if (data.high_info.reserve.type != CONF.RESERVE_TYPE.PERMANENT) {
                reservetypeStr +=  methoddes + ': ' + data.high_info.reserve.value;
            }
			$('.reservetypeshow').html(reservetypeStr);
//---------------------------------保留策略----------------------------------------------------
            $('.threadDiv').show();
        } else {
            $('.threadDiv').hide();
        }
        // 忽略节点资源限制
		data.high_info.ignore_resource_limiting_flag = !!$('#ignoreResourceLimit').get(0).checked;
		$('.ignoreResourceLimitShow').html($('.ignoreResourceLimitLabel').html() + ": " + getSwitchDes(data.high_info.ignore_resource_limiting_flag));
        //----------------安全策略显示start----------------
		let safeInfo = '';
		if (CONF.FUNCTIONS.includes('worm')) {
			safeInfo = LANG.UI_SAFE_STRATEGY_WORM_PROTECT + ": " + getSwitchDes(data.safe_strategy.worm_flag);
			if (data.safe_strategy.worm_flag) {
				safeInfo += '<br>' + LANG.UI_SAFE_STRATEGY_WORM_PROTECT_PERIOD + ": " + data.safe_strategy.worm_protection_time + LANG.UI_PUBLIC_UNIT_DAY;
			}
			safeInfo += '<br>'
		}
		if (CONF.FUNCTIONS.includes('integrity')) {
			let integrityCheck = $('#completeConfig').getCompleteDetectionBackup();
            safeInfo += integrityCheck.des + '<br>';
		}
		if (!CONF.FUNCTIONS.includes('worm') && !CONF.FUNCTIONS.includes('integrity')) {
			$('.safeDiv').hide();
		}
		$('.safeStrategyShow').html(safeInfo);
		//--------------安全策略显示end---------------
        return true;
    }
    //得到开关的结果描述   开启/关闭
    var getSwitchDes = function (check) {
        if (check) {
            return LANG.UI_PUBLIC_ON;
        }
        return LANG.UI_PUBLIC_OFF;
    }


    //得到时间策略
    var getTimeStr = function () {
        data.backup_info.full_info = {};
        data.backup_info.incr_info = {};
        data.backup_info.pincr_info = {};
        data.backup_info.type = $('#backuptype').val();
        var strategyConfig = $('#backupTimestrategy').getStrategyConfig();
        var strategyMode = $('#strategymode').find('input:checked');
        if ("strategy" == data.backup_info.type) {
            if (0 == strategyMode.length) {
                //没有选择时间策略
                UIToastr.showInfo(LANG.UI_GLOBAL_STRATEGY_TIME, LANG.UI_MICROSOFT365_SET_STRATEGY_TIPS);
                return false;
            } else {
                for (var i = 0; i < strategyMode.length; i++) {
                    if (1 == $(strategyMode[i]).data('mode')) {
                        data.backup_info.full_info.mode = strategyConfig.fullInfo.mode;
                        data.backup_info.full_info.type = strategyConfig.fullInfo.type
                        data.backup_info.full_info.start_time = strategyConfig.fullInfo.startTime
                        data.backup_info.full_info.roll_flag = strategyConfig.fullInfo.rollFlag
                        data.backup_info.full_info.roll_interval = strategyConfig.fullInfo.rollInterval
                        data.backup_info.full_info.roll_end_time = strategyConfig.fullInfo.endTime
                        data.backup_info.full_info.days = strategyConfig.fullInfo.days
                        data.backup_info.full_info.des = strategyConfig.fullInfo.des
                        data.backup_info.full_info.frequency = strategyConfig.fullInfo.frequency
                        data.backup_info.full_info.full_backup_compensation_flag = strategyConfig.fullInfo.full_backup_compensation_flag
                        if (strategyConfig.fullInfo.rollFlag && !checkTime(strategyConfig.fullInfo.startTime,strategyConfig.fullInfo.endTime)) {
                            return;
                        }
                    } else if (2 == $(strategyMode[i]).data('mode')) {
                        data.backup_info.incr_info.mode = strategyConfig.incrInfo.mode;
                        data.backup_info.incr_info.type = strategyConfig.incrInfo.type
                        data.backup_info.incr_info.start_time = strategyConfig.incrInfo.startTime
                        data.backup_info.incr_info.roll_flag = strategyConfig.incrInfo.rollFlag
                        data.backup_info.incr_info.roll_interval = strategyConfig.incrInfo.rollInterval
                        data.backup_info.incr_info.roll_end_time = strategyConfig.incrInfo.endTime
                        data.backup_info.incr_info.days = strategyConfig.incrInfo.days
                        data.backup_info.incr_info.des = strategyConfig.incrInfo.des
                        if (strategyConfig.incrInfo.rollFlag && !checkTime(strategyConfig.incrInfo.startTime,strategyConfig.incrInfo.endTime)) {
                            return;
                        }
                    } else if (9 == $(strategyMode[i]).data('mode')) {
                        data.backup_info.pincr_info.mode = strategyConfig.pIncrInfo.mode;
                        data.backup_info.pincr_info.type = strategyConfig.pIncrInfo.type
                        data.backup_info.pincr_info.start_time = strategyConfig.pIncrInfo.startTime
                        data.backup_info.pincr_info.roll_flag = strategyConfig.pIncrInfo.rollFlag
                        data.backup_info.pincr_info.roll_interval = strategyConfig.pIncrInfo.rollInterval
                        data.backup_info.pincr_info.roll_end_time = strategyConfig.pIncrInfo.endTime
                        data.backup_info.pincr_info.days = strategyConfig.pIncrInfo.days
                        data.backup_info.pincr_info.des = strategyConfig.pIncrInfo.des
                        if (strategyConfig.pIncrInfo.rollFlag && !checkTime(strategyConfig.pIncrInfo.startTime,strategyConfig.pIncrInfo.endTime)) {
                            return;
                        }
                    }
                }
                // showStep3(strategyMode);
                return true;
            }
        } else if ("oncetime" == data.backup_info.type) {
            var onceTime = $('#oncetime').val();
            if ("" != onceTime) {
                $('.settimetip').hide();
                data.backup_info.datetime = onceTime;
                // showStep3(strategyMode);
                return true;
            } else {
                UIToastr.showInfo(LANG.UI_GLOBAL_STRATEGY_TIME,LANG.UI_BACKUP_SET_TIME_TIPS);
                return false;
            }
        } else if ('manual' === data.backup_info.type) {
            return true;
        }
        return false;
    }

    //得到限速策略
    var getSpeedStr = function () {
        data.speedLimit = getSpeedStrategyInfo();
        return true;
    }
    //得到安全策略
    var getSafeStr  = function(){
        let wormConfig = $('#wormConfig').getWormProtectionSettings();
        let completeConfig = $('#completeConfig').getCompleteDetectionBackup();
        // 安全策略未授权隐藏
        if (!CONF.FUNCTIONS.includes('worm')) {
            wormConfig = '';
        }
        if (!CONF.FUNCTIONS.includes('integrity')) {
            completeConfig = '';
        }
        data.safe_strategy = safeData(wormConfig, '', completeConfig);
        return true;
    }
    //得到传输策略
    var getTransferStr = function () {
        data.high_info.transfer.encrypt = $('#transport_encrypt_flag').get(0).checked; //得到传输加密
        // 传输加密算法
        data.high_info.transfer.encrypt_method = parseInt($('#transferEncryptMethod').val());
        data.high_info.transfer.network = '';
        if ($('.transfernetworkDiv').css('display') == 'block') {
            let networkNode = $('#transferNetworkTree').transferNetwork('getSelect');
            data.high_info.transfer.network = networkNode.network_uuid;
        }
        return true;
    }
    //得到存储策略
    var getStoreStr = function () {
        data.high_info.store.valid = true;
        data.high_info.store.password_auto_flag = $('#passwordAutocheck').get(0).checked;//自动生成密码开关
        data.high_info.store.password = btoa(getPassword());
        data.high_info.store.dataencrypt = $('#encryptStorageCheck').get(0).checked; //数据加密

        var repassword = $.trim($('#repassword').val());
        // 开启数据加密
        if (data.high_info.store.dataencrypt) {
            // 开启自动生成密码
            if (data.high_info.store.password_auto_flag) {
                data.high_info.store.password = "";
            } else {
                var tmp = $.trim($('#password').val());
                // 未应用备份策略配置的密码
                if (!_oldPassword) {
                    // 是否触发过密码输入框的change事件且密码最终值还是空,则提示 请输入数据加密的密码;没有触发过则无需校验,直接向后端提交此前接口返回的密码
                    if (passwordChangeFlag && !data.high_info.store.password) {
                        UIToastr.showWarning(LANG.UI_BACKUP_DATA_ENCRYPT, LANG.UI_BACKUP_DATA_ENCRYPT_NO_PASSWORD_TIPS);
                        return false;
                    }
                    // 触发过密码输入框的change事件,但和确认密码不一致时
                    if (passwordChangeFlag && tmp !== repassword) {
                        UIToastr.showWarning(LANG.UI_BACKUP_DATA_ENCRYPT, LANG.UI_BACKUP_DATA_ENCRYPT_PASSWORD_CONFIRM_TIPS);
                        return false;
                    }
                    if (!data.high_info.store.password) {
                        UIToastr.showWarning(LANG.UI_BACKUP_DATA_ENCRYPT, LANG.UI_BACKUP_DATA_ENCRYPT_NO_PASSWORD_TIPS);
                        return false;
                    }
                } else {// 应用备份策略配置的密码
                    // 触发过密码框的change事件,且密码为空时
                    if (passwordChangeFlag && !tmp) {
                        UIToastr.showWarning(LANG.UI_BACKUP_DATA_ENCRYPT, LANG.UI_BACKUP_DATA_ENCRYPT_NO_PASSWORD_TIPS);
                        return false;
                    }
                    // 没有触发过密码输入框的change事件,则是备份策略配置过的原密码和原确认密码;触发过,就要比对改变后的密码和确认密码是否一致
                    if (passwordChangeFlag && tmp !== repassword) {
                        UIToastr.showWarning(LANG.UI_BACKUP_DATA_ENCRYPT, LANG.UI_BACKUP_DATA_ENCRYPT_PASSWORD_CONFIRM_TIPS);
                        return false;
                    }
                }
            }
        }
        return true;
    }

    // 得到存储加密密码
    var getPassword = function () {
        var now_password = $.trim($('#password').val().replace(/\s+/g, ''));
        // 备份策略已配置密码时
        if (_oldPassword != '') {
            // 若密码输入框未触发过change事件,现密码和原配置的密码_oldPassword必然相等,则返回解码后的密码
            if (!passwordChangeFlag) {
                return atob(_oldPassword);
            } else { // 若密码输入框触发过change事件,判断现密码和原配置的密码_oldPassword是否相等,相等则返回解码后的密码;不相等则返回现密码
                if (now_password === _oldPassword) {
                    return atob(now_password);
                }
            }
        } else {
            // 未应用备份策略且未触发过密码输入框的change事件,则return之前接口返回的密码
            if (!passwordChangeFlag) {
                return atob(SETTINGS.bss.password);
            } else {
                // 触发过密码输入框的change事件则return密码输入框本身的值
                return now_password;
            }
        }
    }

    //得到高级配置信息
    var getHighConf = function () {
        if ($('#retryTimeInput').val() < 1 || $('#retryTimeInput').val() > 30) {
            $('#retryTimeInput').val(5);
            UIToastr.showWarning(LANG.UI_BR_AUTOBAK_SETTING,LANG.UI_MICROSOFT365_RETRY_TIME_TIPS1);
            return false;
        }
        if ($('#retryNumInput').val() < 1 || $('#retryNumInput').val() > 5) {
            $('#retryNumInput').val(3)
            UIToastr.showWarning(LANG.UI_BR_AUTOBAK_SETTING,LANG.UI_MICROSOFT365_RETRY_TIME_TIPS2);
            return false;
        }
       //重试策略
		data.retry_strategy = $('#retry_config').retryStrategy({} ,'value');
		if (!data.retry_strategy) {
			return false;
		}
        //客户端配置
        if ($('#agentConfig').get(0).checked) {
            var agentUuid = $('#agentList').val();
            if (agentUuid == '' || agentUuid == null) {
                UIToastr.showWarning(LANG.UI_MICROSOFT365_BAK_SPECIFY_AGENT,LANG.UI_MICROSOFT365_BAK_SPECIFY_AGENT_TIPS);
                return false;
            }
            data.high_conf.agent_uuid = agentUuid;
        } else {
            data.high_conf.agent_uuid = "";
        }
        return true;
    }


    var wizardInit = function () {
        if (!jQuery().bootstrapWizard) {
            return;
        }
        var form = $('#submit_form');
        var error = $('.alert-danger', form);
        var success = $('.alert-success', form);
        var handleTitle = function (tab, navigation, index) {
            var total = navigation.find('li').length;
            var current = index + 1;
            //把最大步骤存入内存中 用于判断提交的按钮显示
            if (current >= currentmax) {
                currentmax = current;
            }
            // set wizard title
//            $('.step-title', $('#exchangebackupcontent')).text('Step ' + (index + 1) + ' of ' + total);
            // set done steps
            jQuery('li', $('#exchangebackupcontent')).removeClass("done");
            var li_list = navigation.find('li');
            for (var i = 0; i < index; i++) {
                jQuery(li_list[i]).addClass("done");
            }

            if (current == 1) {
                $('#exchangebackupcontent').find('.button-previous').css('visibility', 'hidden');
                $('#exchangebackupcontent').find('.button-next').addClass('next-btn-margin-left');
            } else {
                $('#exchangebackupcontent').find('.button-previous').css('visibility', 'visible');
                $('#exchangebackupcontent').find('.button-next').removeClass('next-btn-margin-left');
            }
            if (current >= total) {
                $('#exchangebackupcontent').find('.button-next').hide();
                $('#exchangebackupcontent').find('.button-submit').css('visibility', 'visible');
            } else {
                $('#exchangebackupcontent').find('.button-next').show();
                $('#exchangebackupcontent').find('.button-submit').css('visibility', 'hidden');
            }
            //用于判断是否展示提交按钮
            // if (current < currentmax) {
            //     $('#exchangebackupcontent').find('.button-submit').css('visibility', 'hidden');
            // } else {
            //     $('#exchangebackupcontent').find('.button-submit').css('visibility', 'visible');
            // }
            Metronic.scrollTo($('.page-title'));
        }

        // default form wizard
        $('#exchangebackupcontent').bootstrapWizard({
            'nextSelector': '.button-next',
            'previousSelector': '.button-previous',
            onTabClick: function (tab, navigation, index, clickedIndex) {
                return false;
                /*
                success.hide();
                error.hide();
                if (form.valid() == false) {
                    return false;
                }
                handleTitle(tab, navigation, clickedIndex);
                */
            },
            onNext: function (tab, navigation, index) {
                success.hide();
                error.hide();
                switch (index) {
                    case 1:
                        if(!step1Valid(() => {
                            $('a[href="#tab2"]').tab('show'); // 手动切换步骤页面
                            handleTitle(tab, navigation, index);
							pageIndex = 1;
                        })){
	            			return false;
	            		}
	            		pageIndex = 1;
	            		break;
                    case 2:
                        if (step2Valid() == false) {
                            return false;
                        }
                        pageIndex = 2;
                        break;
                    case 3:
                        if (step3Valid() == false) {
                            return false;
                        }
                        pageIndex = 3;
                        break;
                }
                handleTitle(tab, navigation, index);
            },
            onPrevious: function (tab, navigation, index) {
                pageIndex = index;
                success.hide();
                error.hide();

                handleTitle(tab, navigation, index);
            },
            onTabShow: function (tab, navigation, index) {
                var total = navigation.find('li').length;
                var current = index + 1;
                var $percent = (current / total) * 100;
                $('#exchangebackupcontent').find('.progress-bar').css({
                    width: $percent + '%'
                });
            }
        });

        $('#exchangebackupcontent').find('.button-previous').css('visibility', 'hidden');
        $('#exchangebackupcontent .button-submit').click(() => {
            if (pageIndex == 0) {
                if (!step1Valid(() => {
                    submit();
                })) {
                    return false;
                }
            } else if (pageIndex == 1) {
                var step2 = step2Valid();
                if (!step2) {
                    return false;
                }
            } else if (pageIndex == 2) {
                var step3 = step3Valid();
                if (!step3) {
                    return false;
                }
            }
            submit();
        }).css('visibility', 'hidden');
    };

    var submit = function () {
        //存储为磁带不能选择永久增量
        if (data.backup_info.pincr_info && data.backup_info.pincr_info.mode == 9 && $("#selectstorage option:selected").attr("data-type") == CONF.BD_STORAGE_TYPE.TAPE) {
            UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_TIME,LANG.UI_MICROSOFT365_TIME_TAPE_TIPS);
            return;
        }
        if ('' == $.trim($("#jobname").val())) {
            $('.jobnametip').html(LANG.UI_BACKUP_NAME_TIPS).show();
            return;
        }
        $('.jobnametip').hide();
        let jobName = $.trim($("#jobname").val());
        // 输入验证
        if(!customInputValidate('string',jobName)){
            return false;
        }
        getSpeedStr();
        data.job_name = $.trim($("#jobname").val());
        data.strategygroupuuid = $('#strategySelect option:selected').val();
        //提交的时候重新调用下这个方法
        data.speedLimit = getSpeedStrategyInfo();
        data = deepCloneObject(data);
        Metronic.blockUI({target: '#exchangebackupcontent',animate: true,cenrerY: true,});
        //获取组织数据
        pAjaxRequest(data, "/api/v1/exchange/jobs/backup", "PUT", function (data) {
            if (operateResponseList(data,LANG.UI_MICROSOFT365_EDIT_BACKUP_JOB)) {
                LOCATION('./content/platform/jobs/jobs.php', 'task');
            }
            Metronic.unblockUI('#exchangebackupcontent');
        }, true);
    }

    var inintDatatimePicker = function () {
        if (CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw") {
            //英文独有的
            $(".form_datetime").datetimepicker({
                autoclose: true,
                isRTL: Metronic.isRTL(),
                format: "yyyy-mm-dd hh:ii:ss",
                pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
                startDate: new Date()
            });
        } else {
            $(".form_datetime").datetimepicker({
                language:  'zh-CN',
                autoclose: true,
                isRTL: Metronic.isRTL(),
                format: "yyyy-MM-dd hh:ii:ss",
                pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
                startDate: new Date()
            });
        }
    }


    //初始化节点传输网络列表
    var initNetworkList = function (nodeUuid) {
        if (oldNode === nodeUuid) {
            return;
        }
 
        $('#transferNetworkTree').transferNetwork({
            node_uuid: nodeUuid,
            network_uuid: SETTINGS.bts.network,
			network_pool_uuid: SETTINGS.bts.network_pool_uuid
        });
        oldNode = nodeUuid;
    }

    //初始化策略选择列表
    var initStrategySelect = function () {
        if (initStrategyFlag) {
            return; //加载一次
        }
        function initStrategyList(res)
        {
            if (!res.success) {
                return;
            }
            if (res.data.length > 0) {
                // 清空策略列表，再插入新的策略列表
                var data = res.data;
                var strategyselect = $('#strategySelect');
                strategyselect.empty();
                for (var i = 0; i < data.length; i++) {
                    var option = '<option  value="' + data[i].uuid + '">' + data[i].text + '</option>';
                    globalStrategy[data[i].uuid] = data[i].strategy;
                    strategyselect.append(option);
                }
                // 是否初始化策略
                if (SETTINGS.strategy_uuid) {
                    // 默认选中选中所使用策略组
                    strategyselect.val(SETTINGS.strategy_uuid);
                }
                if (!initStrategyFlag) {
                    //初始化前先清除一遍
                    $('.searchable-select').remove();
                    $('#strategySelect').searchableSelect();
                    $('.searchable-select-item').on('click', strategyHandler);
                    initStrategyFlag = true;
                }
            }

        }

        pAjaxRequest({type: CONF.MODULE_TYPE.M365}, '/api/v1/strategies/select', "GET", initStrategyList, true);
    }

    // 应用全局策略并初始化策略信息
    var strategyHandler = function () {
        // 全局策略选中
        var index = $('#strategySelect option:selected').val();
        var strategy = globalStrategy[index];
        // 初始化全局策略数据
        $('#tab_common').backupStrategy(CONF.MODULE_TYPE.M365, true, strategy, 0);
        data.backup_info.type = $('#backuptype').val();
        // 限速策略
        if (index == 0) {
            return false;
        }
		_oldPassword = strategy?.store?.storeInfo?.password ?? '';;
        var speedInfo = strategy.speedlimit.speedInfo;
        var check = strategy.speedlimit.check;
        if (!check || !speedInfo) {
            return ;
        }
        speedList = [];
        // 将限速策略放进消息中
        for (var i = 0; i < speedInfo.length; i++) {
            speedList.push(speedInfo[i]);
        }
    }
    var getM365CurrentUseLicense = (currentUse) => {
        return new Promise((resolve) => {
            let params = {
                module: data.region,
                currentUse,
                showMetronic: true,
                judge: true,
                showAlertMsg: true,
                uuids: [],
                task_uuid: data.job_uuid,
            }
            getModuleAuthInfo(params).then(result => {
                if (result) {
                    resolve();
                }
            });
        });
    };

    return {
        //main function to initiate the module
        init: function () {
            wizardInit();
            // initDrawer();//初始化抽屉插件
            initTree();
            inintDatatimePicker();
            // initData();
            initSpinner();
            initListener();
            initOldSettings();
        },

    };
}();

jQuery(document).ready(function () {
    ExchBackupEdit.init();
});