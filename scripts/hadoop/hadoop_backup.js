var hadoop_backup = function () {
    var data = {src_info:{},backup_info:{},high_info:{}};
    var searchFlag =  false;
    var zTree;
    var zTreeFile = [];
    var _pageSize =  40; //集群文件列表显示条数
    var nodeSelectFlag = false; //自定义节点选择加载标志
    var initStrategyFlag = false;
    var applianceFlag =  false; //传输代理初始化标志
    var globalStrategy = [];
    var editFlag = false;
    var defaultStrategy = [];
    var selectfile =  false; //是否选择文件
    var selectdiffwild =  false; //是否通配符都是不使用通配符过滤 false是每个集群都不使用通配符过滤

    // ------------ 以下是修改备份所需参数 ----------
    var EDIT_BACKUP_FLAG = false; // 创建备份 | 修改备份标记
    var taskId = '' //修改备份任务的任务id
    var taskInfoSetting = {}; // 修改备份任务详情对象
    var speedList = [];
    var currentmax = 0;
    var pageIndex = 0; //轮播索引
    var firstflag =  false;
    var firstInitPageFlag = false; // 首次进入页面标记
    var PASSWORD_HASCHANGED_FLAG = false; // 是否改变了密码框内容标记
    var oldfilelist = [];
    var oldshowfile = [];
    var appliedGlobalStrategy = {
        flag: false,
        uuid: '',
        strategy: {}
    } // 应用的全局策略
    var APPLIANCE_AGENCY_HAS_CONFIGED = false; // 传输代理是否已配置标记
    let backupTargetInfo = ''; //备份目标信息
	var wizardInit = function(){
		if (!jQuery().bootstrapWizard) {
            return;
        }
        var form = $('#submit_form');
        var error = $('.alert-danger', form);
        var success = $('.alert-success', form);
        var handleTitle = function(tab, navigation, index) {
            var total = navigation.find('li').length;
            var current = index + 1;
            if(EDIT_BACKUP_FLAG){
                //把最大步骤存入内存中 用于判断提交的按钮显示
                if(current >= currentmax){
                    currentmax = current;
                }
            }
            jQuery('li', $('#hadoopbackupcontent')).removeClass("done");
            var li_list = navigation.find('li');
            for (var i = 0; i < index; i++) {
                jQuery(li_list[i]).addClass("done");
            }

            if (current == 1) {
                $('#hadoopbackupcontent').find('.button-previous').css('visibility', 'hidden');
                $('#hadoopbackupcontent').find('.button-next').addClass('next-btn-margin-left');
            } else {
                $('#hadoopbackupcontent').find('.button-previous').css('visibility', 'visible');
                $('#hadoopbackupcontent').find('.button-next').removeClass('next-btn-margin-left');
            }

            if (current >= total) {
                $('#hadoopbackupcontent').find('.button-next').hide();
            } else {
                $('#hadoopbackupcontent').find('.button-next').show();
                
            }
            // if(EDIT_BACKUP_FLAG){
            //     //用于判断是否展示提交按钮
            //     if(current < currentmax){
            //         $('#hadoopbackupcontent').find('.button-submit').css('visibility', 'hidden');
            //     }else{
            //         $('#hadoopbackupcontent').find('.button-submit').css('visibility', 'visible');
            //     }
            // }else{
            //     if(current >= total){
            //         $('#hadoopbackupcontent').find('.button-submit').css('visibility', 'visible');
            //     }else{
            //         $('#hadoopbackupcontent').find('.button-submit').css('visibility', 'hidden');
            //     }
            // }
            if(current < total){
                $('#hadoopbackupcontent').find('.button-submit').css('visibility', 'hidden');
            }else{
                $('#hadoopbackupcontent').find('.button-submit').css('visibility', 'visible');
            }
            Metronic.scrollTo($('.page-title'));
        }
        // default form wizard
        var wizard = $('#hadoopbackupcontent').bootstrapWizard({
            'nextSelector': '.button-next',
            'previousSelector': '.button-previous',
            onTabClick: function (tab, navigation, index, clickedIndex) {
                return false;
            },
            onNext: function (tab, navigation, index) {
                success.hide();
                error.hide();
                switch(index){
                	case 1:
                		if(step1Valid(
							() => {
                                // 第一步校验成功 那么需要判断是否开启弹窗
                                if((selectdiffwild || selectfile) && firstflag == false){
                                    //如果是选择了文件 需要给出弹窗 让用户知道快照提示
                                    bootbox.confirm({
                                        title: LANG.UI_VOL_CDP_BACKUP_TIPS,
                                        message: LANG.UI_HADOOP_BACKUP_DIR_TIP,
                                        callback: debounce(function(result) {
                                            if(result){ 
                                                firstflag =  true; //点击确认之后 
                                                wizard.bootstrapWizard('next'); //会再进一次onNext这里面去 index为1 的情况 但是设置firstflag为true 下一个next将不会出现弹窗 
                                            }
                                        },300)
                                    });
                                    // return firstflag;
                                }else{
                                    $('a[href="#tab2"]').tab('show'); // 如果不用开启弹窗则手动切换步骤页面
                                    handleTitle(tab, navigation, index);
                                }
                            }
						) == false){
                            if(EDIT_BACKUP_FLAG){
                                pageIndex = 1;
                            }
                			return false;
                		}
                        if (EDIT_BACKUP_FLAG) {
                            pageIndex = 1;
                        }
                		break;
                	case 2:
                		if(step2Valid() == false){
                			return false;
                		}
                        if(EDIT_BACKUP_FLAG){
                            pageIndex = 2;
                        }
                		break;
                	case 3:
                		if(step3Valid() == false){
                			return false;
                		}
                        if(EDIT_BACKUP_FLAG){
                            pageIndex = 3;
                        }
                		break;
                }
                handleTitle(tab, navigation, index);
            },
            onPrevious: function (tab, navigation, index) {
                success.hide();
                error.hide();
                if(index == 0){ //从第二步到第一步
                    firstflag =  false; //将第一步的flag还原
                }
                handleTitle(tab, navigation, index);
            },
            onTabShow: function (tab, navigation, index) {
                var total = navigation.find('li').length;
                var current = index + 1;
                var $percent = (current / total) * 100;
                $('#hadoopbackupcontent').find('.progress-bar').css({
                    width: $percent + '%'
                });
            }
        });
        $('#hadoopbackupcontent').find('.button-previous').css('visibility', 'hidden');
        $('#hadoopbackupcontent .button-submit').click(submit).css('visibility', 'hidden');
        // if(EDIT_BACKUP_FLAG){
        //     //修改时提交按钮默认展示
        //     $('#hadoopbackupcontent .button-submit').css('visibility', 'visible');
        // }
        
	};
    //初始化数据
    var initData =  function(){
        //step1 备份源
        data.src_info.cluster_uuid_list = [];
        data.src_info.file_info = [];
      
        //step3 策略/时间
        data.backup_info.type = 'strategy';
		//完备/增备
		data.backup_info.full_info = {};
		data.backup_info.incr_info = {};
        data.backup_info.diff_info = {};
        data.backup_info.pincr_info = {};
        //按时间备份的时间
		data.backup_info.datetime = null;
        //保留策略
        data.high_info.reserve = {};
		data.high_info.reserve.type = 1;
		data.high_info.transfer = {};
		data.high_info.store = {};

        data.high_info.node = {};
        data.high_info.newstr = {wildcard_list:[]};
        $('#encryptStorageCheck').bootstrapSwitch('state', false);  //默认关闭数据加密
		// hadoop备份不支持功能
		$('.deduplicationDiv').hide();
		$('.GFSdiv').hide();
        $('#tab_common').backupStrategy(CONF.MODULE_TYPE.FS, false);
    }
    //初始化微调器
    var initSpinner = function(){
		initReserveSpinner($('#spinnerDay'));
		initReserveSpinner($('#spinnerNum'));
        $('#speedSpinnerNum').spinner({value:10, step: 5, min: 1, max: 10000000000});
		$('.backupThreadDiv').spinner({value:3, step: 1, min: 1, max: 32});
		$('.scanThreadDiv').spinner({value:3, step: 1, min: 1, max: 32});
		$('.warnningdiv').spinner({value: 20, step: 5, min: 1,max: 100});//跳过文件告警比例
		$('.passfilenumDiv').spinner({value:10, step: 5, min: 1, max: 999999999});//跳过文件告警个数
	}
    //初始化树
    var initTree =  function(){
        //获取参数 
        let queryparams = {};
        queryparams.keyword = $('#searchCluster').val();
        queryparams.editflag =  false;
        var requestClusterZtree = function(d){
			//初始化树
            if(!d.success) return;
			setTree(d.data);
		}
        //初始化hadoop集群树
        pAjaxRequest(queryparams, "/api/v1/hadoop/backup/cluster/ztree", "GET", requestClusterZtree ,true);
    }
    //初始化监听
    var initListener =  function(){
        $('#toAdd').on('click',function(){
	    	LOCATION('./content/hadoop/hadoop_cluster.php', 'hadoop_cluster');
		});
        $('#searchCluster').on('propertychange', debouncenew).on('input', debouncenew);
        $('#allClusterTree').on('change','.wildcardmode', wildcardmodeTypeHandler);
        $('#allClusterTree').on('click','button.addInput',wildInputAdd);//添加通配符输入框
        $('#allClusterTree').on('click','button.delInput',delInput);
        //备份类型改变
        $('#backuptype').on('change', backupTypeHandler);
        //选择备份策略复选框
		$('#strategymode').find('.icheck').on('ifClicked', strategyModeClick);
        //重置时间
        $('#resetdate').on('click',function(){
			$('#oncetime').val('');
		});
        //切换限速模式
        $('#speedModeType').on('change', speedModeHandler);
        //添加限速策略确定
        $('#speed_submit').on('click', speedSubmit);
        //切换存储加密开关
		$('#encryptStorageCheck').on('switchChange.bootstrapSwitch', encryptChange);
        //切换自动选择存储加密密码
		$('#passwordAutocheck').on('switchChange.bootstrapSwitch', passwordModeChange);
        //数据加密密码确认
		$('#repassword,#password').on('input propertychange', function(){
            if (EDIT_BACKUP_FLAG) {
                $('#password').attr('placeholder', '');
                $('#repassword').attr('placeholder', '');
            }

            PASSWORD_HASCHANGED_FLAG = true; // 触发修改过密码flag
			var password = $.trim($('#password').val());
			var repassword = $.trim($('#repassword').val());
			if(password != repassword){
				$('.passwordTips').show();
			}else{
				$('.passwordTips').hide();
			}
		});
        
        //初始化存储策略配置监听
		initStoreListeners();
        //初始化保留策略配置监听
		initReserveListeners();
        //扫描文件
		// $("#scanFileNum").on('change', function(){
        //     initHighStrategyDes();
		// });
		//跳过文件告警智能判断
		$('#passfilealarmcheck').on('switchChange.bootstrapSwitch', passAlarmChange);
        // 压缩传输
        $('#compressCheck').on('switchChange.bootstrapSwitch', compressChange);
		// 压缩等级改变
        $('#compressGrade').on('change', initStoreStrategyDes);
        // 传输策略---加密传输
        $('#transport_encrypt_flag').on('switchChange.bootstrapSwitch', transferEncryptChange);
        //传输策略---传输代理
		$('#appliancecheck').on('switchChange.bootstrapSwitch', applianceChange);
        //应用到其他集群
        
        $('#allClusterTree').on('click','input.apply-to-all-cluster',applyToAllCluster); 
        if(!EDIT_BACKUP_FLAG){
            initApplianceSelect();
        }
        //快照开关事件
        $('#silentsnapshotcheck').on('switchChange.bootstrapSwitch', silentsnapChange);
    }
    // 显示加密算法
	var transferEncryptChange = function(){
		if(this.checked){
			$('.transfer-encrypt-method-form').show();
		}else{
			$('.transfer-encrypt-method-form').hide();
		}
	}
    //显示传输代理
    var applianceChange = function(){
		if(this.checked){
			$('.applianceselectdiv').show();
            $('.encrypttransferdiv').show();
		}else{
			$('.applianceselectdiv').hide();
            $('.encrypttransferdiv').hide();
            $('.transfer-encrypt-method-form').hide();	//加密传输算法
            $('#transport_encrypt_flag').bootstrapSwitch('state', false);
		}
	}
    
    // 显示压缩等级
    var compressChange = function () {
        if (this.checked) {
            $('.compressGradeDiv').show();
        } else {
            $('.compressGradeDiv').hide();
        }
    };
    //跳过文件告警
    var passAlarmChange = function() {
		var flag = $('#passfilealarmcheck').get(0).checked;
		if(flag) {
			$('.passfilenumDiv').show();
			$('.warnningdiv').show();
		} else {
			$('.passfilenumDiv').hide();
			$('.warnningdiv').hide();
		}
	}
    //快照
    var silentsnapChange = function(){
        var flag = $('#silentsnapshotcheck').get(0).checked;
        //关闭快照
        if(!flag && EDIT_BACKUP_FLAG){
            $('.wildcardmode').removeAttr('disabled');
            $('.addInput').removeAttr('disabled');
            $('.delInput').show();
        }
    }
    //初始化传输线程
	var intTransThreadNum = function () {
		var transSpeed = $("#scanThreadNum").val();
		if(transSpeed == 1) {//为1（极慢）时显示文件扫描速度
			$(".scanFileDiv").show();
		}else {
			$(".scanFileDiv").hide();
		}
		// initHighStrategyDes();
	}
    //存储策略配置监听
	var initStoreListeners = function(){
        $('#compressCheck').on("switchChange.bootstrapSwitch",function(){
            initStoreStrategyDes();
        });
        $('#encryptStorageCheck').on("switchChange.bootstrapSwitch",function(){
            initStoreStrategyDes();
        });
		// $('#backupThreadNum').on('input propertychange', function(){
        // 	initHighStrategyDes();
        // });
        // $('.backupThreadDiv .spinner-up').on('click', function(){
        // 	initHighStrategyDes();
        // });
        // $('.backupThreadDiv .spinner-down').on('click', function(){
        // 	initHighStrategyDes();
        // });
		$('#scanThreadNum').on('input propertychange', function(){
        	intTransThreadNum();
        });
		$('.scanThreadDiv .spinner-up').on('click', function(){
        	intTransThreadNum();
        });
        $('.scanThreadDiv .spinner-down').on('click', function(){
        	intTransThreadNum();
        });
        // 存储加密算法
        $('#storageEncryptMethod').on("change", function () {
            initStoreStrategyDes();
        });
    }
     //保留策略配置监听
	var initReserveListeners = function(){
        //保留类型切换
        $('#reserveMode').on('change', function(){
            initReserveStrategyDes();
        });
        //保留方式切换
		$('#reserveType').on('change', reserveTypeHandler);
        $('#spinnerNumInput').on('input propertychange', function(){
            initReserveStrategyDes();
        });
        $('#spinnerDayInput').on('input propertychange', function(){
            initReserveStrategyDes();
        });
        
        $('.reserveNum .spinner-up').on('click', function(){
			initReserveStrategyDes();
        });
        $('.reserveNum .spinner-down').on('click', function(){
			initReserveStrategyDes();
        });
        
        $('.reserveDay .spinner-up').on('click', function(){
			initReserveStrategyDes();
        });
        $('.reserveDay .spinner-down').on('click', function(){
			initReserveStrategyDes();
        });
    }
    var wildcardmodeTypeHandler = function() {
		var wildcardmode = $(this).val();
		if(wildcardmode != 0) {
			$(this).parents('.form-group.wildmode').siblings('.wildcarddiv').show();
		}else {
			$(this).parents('.form-group.wildmode').siblings('.wildcarddiv').hide();
			$(this).parents('.form-group.wildmode').siblings('.wildcarddiv').find('div.delcontent').remove();
		}
	}
    var wildInputAdd = function () {
		var content = $.trim($(this).parent('.col-md-2').prev().find('.wildcardInputdiv').val());
		if(content!='') {
			$(this).parent('.col-md-2').after('<div class="delcontent"><span type="text"class="input-sm wildcardInput">'+ content +'</span><button type="button"class="btn btn-danger delInput input-sm">×</button></div>');
		}else {
			UIToastr.showInfo(LANG.UI_FILE_WILDCARD_RULES_TIPS3);
			return false;
		}
		$(this).parent('.col-md-2').prev().find('.wildcardInputdiv').val('');
	}
    var delInput = function() {
		$(this).parents('.delcontent')[0].remove();
	}
    var initDatatimePicker = function(){
		if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
		 //英文独有的
            $(".form_datetime").datetimepicker({
            autoclose: true,
            isRTL: Metronic.isRTL(),
            format: "yyyy-mm-dd hh:ii:ss",
            pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
            startDate: new Date()
            });
		}else{
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

    //搜索集群
    var debouncenew = function () {
		var value = $('#searchCluster').val();
		// 输入验证
		if(!customInputValidate('string',value)){
			return false;
		}
		var nodes = zTree.getNodes();
		if(!nodes || nodes.length == 0) return;
		var checkNode =zTree.getCheckedNodes();
		var checkFsNode = [];
		$.each(checkNode, function (i, v) {
			checkFsNode.push(v);
		});
		var allNode = zTree.transformToArray(zTree.getNodes());
		nodeParamList = zTree.getNodesByParamFuzzy('name', value);
		if(nodeParamList.length!=0){
			zTree.hideNodes(allNode);
			$('.three_tree').show();
			$('#nosearchtips').hide();
		}else{
			$('.three_tree').hide();
			$('#nosearchtips').show();
		}
		//连接搜索的和所勾选的
		nodeParamList =nodeParamList.concat(checkFsNode);
		var nodeParamList1 = zTree.transformToArray(nodeParamList);
        for(var n in nodeParamList1){
            findParent(zTree,nodeParamList1[n]);
        }
        zTree.showNodes(nodeParamList);
		searchFlag = true;
    }
    //找到父节点
	var findParent = function(treeObj,node){
		zTree.expandNode(node,true,false,false);
		if(!node.children){
			nodeParamList.push(node);
			zTree.expandNode(node,false,false,false);
		}
		var pNode = node.getParentNode();
		if(pNode != null){
			nodeParamList.push(pNode);
			findParent(zTree, pNode);
		}
    }
    //初始化节点下拉框
	var initNodeSelect = function(){
		if(nodeSelectFlag) return; //加载一次
		$.post(CONF.AJAXPATH, {m:CONF.M.NODE,f:'getAddStorageNodeSelect',p:{}}, function(d){
			var data = JSON.parse(d);
			var softselect = $('#selectnode');
			softselect.empty();
			for(var i=0; i<data.length; i++){
				var option = $("<option>").text(data[i].text).val(data[i].uuid);
				softselect.append(option);
			}
            //如果是修改任务 这里需要给节点默认值
            if(EDIT_BACKUP_FLAG){
                $('#selectnode').val(taskInfoSetting.node.node_uuid);
            }
			nodeSelectFlag = true;
			initStorageSelect();
		});
	}
   //初始化存储下拉框
	var initStorageSelect = function(){
		var data = {};
        data.node_uuid = $('#selectnode').val();
        data.exchange_cloud_flag = true;
        pAjaxRequest(data, "/api/v1/storages/backup", "GET", function (result) {
            if (result.success) {
                var softselect = $('#selectstorage');
                softselect.empty();
                if(result.data.length > 0) {
                    let storageList = [];
                    for(var i=0; i<result.data.length; i++){
                        var option = $("<option>").text(result.data[i].text).val(result.data[i].storage_uuid).attr("storage_type",result.data[i].storage_type);
                        softselect.append(option);
                        storageList.push(result.data[i].storage_uuid);
                    }
                    if(EDIT_BACKUP_FLAG){
                        if (storageList.indexOf(taskInfoSetting.node.storage_uuid) > -1) {
                            $('#selectstorage').val(taskInfoSetting.node.storage_uuid);
                        }
                    }
                }
            }
        });
	}
    //初始化节点
    var initBackupTarget = function(){
        $('#backupTarget').backupTarget();
    }
    //备份类型
    var backupTypeHandler = function(){
		if('strategy' == this.value){
			//按策略备份
			$('.setStrategy').show();
			$('.setOnceTime').hide();
            $('#reserveType').removeAttr("disabled");
			$('#spinnerNum').spinner('enable');
			$('#spinnerDay').spinner('enable');
			$('#spinnerNum').spinner('value', 30);
			$('#spinnerDay').spinner('value', 30);
		}else if('oncetime' == this.value){
			//一次性备份
			$('.setStrategy').hide();
			$('.setOnceTime').show();
            $('#reserveType').prop("disabled","disabled");
			$('#spinnerNum').spinner('disable');
			$('#spinnerDay').spinner('disable');
			$('#spinnerNum').spinner('value', 1);
			$('#spinnerDay').spinner('value', 1);
		}else if('manual' == this.value){
            $('.setStrategy').hide();
			$('.setOnceTime').hide();
            $('#reserveType').removeAttr("disabled");
			$('#spinnerNum').spinner('enable');
			$('#spinnerDay').spinner('enable');
            $('#spinnerNum').spinner('value', 30);
			$('#spinnerDay').spinner('value', 30);
        }
		data.backup_info.type = this.value;
		$('.settimetip').hide();
		initReserveStrategyDes();
        // initTimeStrategyDes();
	}
    //保留类型改变
    var reserveTypeHandler = function(){
		if(CONF.RESERVE_TYPE.NUM == this.value){
			$('.reserveNum').show();
			$('.reserveDay').hide();
			data.high_info.reserve.type = this.value;
		}else if(CONF.RESERVE_TYPE.DAY == this.value){
			$('.reserveNum').hide();
			$('.reserveDay').show();
			data.high_info.reserve.type = this.value;
		}else if(undefined == this.value){
			$('.reserveNum').hide();
			$('.reserveDay').hide();
			data.high_info.reserve.type = 3;
		}
		//修改保留策略信息
		initReserveStrategyDes();
	}
	

    //设置集群树
    var setTree =  function(zNodes){
        var znode  =  JSON.stringify(zNodes);
        if(znode == "[]" || znode ==  "null" ){;
            $("#noagent").show();
            $('#nosearchtips').hide();
            $(".vcenter-tree").hide();
            if(searchFlag) {
				$('#nosearchtips').show();
				$("#noagent").hide();
			}
            return;
        }else{
            $("#noagent").hide();
			$(".vcenter-tree").show();
			$(".searchDiv").show();
			$('#nosearchtips').hide();
            //修改备份时回显所勾选的hadoop集群
            if(EDIT_BACKUP_FLAG){
                if (taskInfoSetting.checkedClusterList && taskInfoSetting.checkedClusterList.length > 0) {
                    let checkedClusterList = taskInfoSetting.checkedClusterList;
                    let wildcardList = taskInfoSetting.high.wild_card_info;
                    let checkedClusterInfo = [];
                    // 循环处理给checkedClusterList每项添加其对应的通配符配置，用于下一步生成回显通配符
                    checkedClusterList.forEach(item => {
                        let tmp = {};
                        wildcardList.forEach(i => {
                            if (i.agent_uuid === item) {
                                tmp =  {
                                    clusteruuid:item,
                                    wildcard_mode: i.wildcard_mode,
                                    wildcard: i.wildcard ? i.wildcard : []
                                }
                            }
                        })
                    
                        checkedClusterInfo.push(tmp);
                    });              
                    zNodes.forEach(item => {
                        checkedClusterInfo.forEach(i => {
                            if(i.clusteruuid ==  item.id){
                                item.checked = true;
                                // 手动触发查询hadoop存储目录树 TODO:每个hadoop存储对应的通配符也要给上
                                if(!item.chkDisabled){
                                    addClusterList(item,i);
                                    initFileTree('cluster_tree',item,false,'');
                                }
                            }
                        })
                    });
                }
            }
        }
        var setting = {
            check: {
                enable: true,
                nocheckInherit: false
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
                beforeClick: nodeClick,
                onCheck: nodeCheck,
                beforeExpand: nodeExpand
            },
            view: {
                fontCss: setFontCss,
                showIcon:setIcon
            }
        };
        zTree = $.fn.zTree.init($("#cluster_tree"), setting, zNodes);
		if(searchFlag) {
			zTree.expandAll(true);
		}
		searchFlag = false;   
    }
    //设置未授权或者离线节点的样式
	function setFontCss(treeId, treeNode) {
		return treeNode.chkDisabled ? {color:"grey"} : {};
	};
    //设置icon
    function setIcon(treeId, treeNode){
        return treeNode.event_type == 'hadoop_cluster' ? true : false
    }
    //勾选集群
    var nodeCheck = function(treeId, id, treeNode){
        // checkClusterOnlineTips(treeNode);
        nodeExpand(treeId,treeNode);
    }
    //点击集群
    var nodeClick  = function(treeId, treeNode){
        // checkClusterOnlineTips(treeNode);
        // if(treeNode.event_type == 'hadoop_cluster'){
        //     $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);//单击展开节点
        //     return;
        // }
        //未被禁用，点击选中或者取消选中
        if(!treeNode.chkDisabled){
            $.fn.zTree.getZTreeObj(treeId).checkNode(treeNode, !treeNode.checked, true);
        }
        nodeExpand(treeId,treeNode);
    }
    //集群展开
    var nodeExpand =  function(treeId, treeNode){
        if(treeNode.chkDisabled) {//离线客户端只能取消  不能选中
			treeNode.chkDisabled = false;
			zTree.checkNode(treeNode, false, true);
			treeNode.chkDisabled = true;
			zTree.updateNode(treeNode);
		}
        checkClusterOnlineTips(treeNode);
        var flag  = treeNode.checked;
        if(flag){
            //选中集群
            addClusterList(treeNode);
            initFileTree(treeId,treeNode,false,'');
           
        }else{
            if(treeNode.chkDisabled)return;
            // 取消勾选
            if(zTree.getCheckedNodes(true).length != 0) {
                //删除取消选中的clusteruuid
                data.src_info.cluster_uuid_list.splice(data.src_info.cluster_uuid_list.indexOf(treeNode.uuid),1);
                //删除取消选中的树对象
                delete zTreeFile[treeNode.uuid];
                $('#allClusterTree').find('#'+ treeNode.id + '.add-list').remove();
            }else{
                //一个都未选中
                data.src_info.cluster_uuid_list = [];
                zTreeFile = [];//清空树对象
				$('#allClusterTree').html('');
				$('#clusterfilediv').hide();
				$('#step1tips').show();
            }
        }
    }
    //检查集群是否离线或者未授权
    var checkClusterOnlineTips =  function(treeNode){
        if(treeNode.chkDisabled){
			UIToastr.showWarning(LANG.UI_HADOOP_CLUSTER_UNAUTH_OFFLINE,LANG.UI_HADOOP_CLUSTER_UNAUTH_OFFLINE_TIPS);
		}
    }
    //将选中的某个集群加到右边 同时配置通配符
    var addClusterList = function(treeNode, wildcardObj = {}){
        var divChildren = $('#allClusterTree').children();
		for(var i = 0; i < divChildren.length; i++) {
			if(divChildren[i].id == treeNode.id) {
				$('#allClusterTree').find('#'+treeNode.id+'.add-list' ).remove();//移除重复的
			}
		}
		var agentContent = "";
		// treeNode.path = '';
        agentContent += 
        '<div id="'+ treeNode.id +'"class="add-list">' + 
            '<div class="accordion file-accordion">' + 
                '<div class="panel panel-default panel-file">' + 
                    '<div class="panel-heading">' + 
                        '<h4 class="panel-title">' + 
                            '<a class="accordion-toggle accordion-toggle-styled popovers" style="display: inline-block; width: 99%;text-decoration: none;" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".fileAccordion" href="#fileClusterInfo_'+ treeNode.id +'" aria-expanded="true">' + 
                                '<span class="font-green-seagreen">'+ treeNode.name +'</span>' + 
                            '</a>' + 
                        '</h4>' + 
                    '</div>' + 
                    '<div id="fileClusterInfo_' + treeNode.id+'"class="panel-collapse collapse in">' + 
                        '<div class="panel-body">' + 
                            '<div class="pabel-body-btns"><input class="btn green-turquoise apply-to-all-cluster" type="button" value="'+ LANG.UI_HADOOP_APPLY_CLUSTER +'"></div>' + 
                            '<div class="nav-tabs-wrapper">' + 
                                '<ul class="nav nav-tabs nav-line-tabs">' + 
                                    '<li id="commontab'+ treeNode.uuid +'"class="active nav-item">' + 
                                        '<a class="nav-link" href="#common_tabagent_tree_'+ treeNode.uuid +'"data-toggle="tab"aria-expanded="false">'+ LANG.UI_FILE_SELECT_FILE_AND_DIR +'</a>' + 
                                    '</li>' + 
                                    '<li class="nav-item">' + 
                                        '<a class="nav-link" href="#high_tabagent_tree_'+ treeNode.uuid +'"data-toggle="tab"aria-expanded="false">'+ LANG.UI_PUBLIC_MORE +'</a>' + 
                                    '</li>' + 
                                '</ul>' + 
                                '<div class="tab-content hover-scroll-y">' + 
                                    '<div class="tab-pane active"id="common_tabagent_tree_'+ treeNode.uuid +'">' + 
                                        '<div class="row" style="margin: 0">' + 
                                            '<div class="form-group" style="margin: 0">' + 
                                                '<ul id="fileClusterTree_'+ treeNode.uuid +'"class="ztree"></ul>' + 
                                            '</div>' + 
                                        '</div>' + 
                                    '</div>' + 
                                    '<div class="tab-pane"id="high_tabagent_tree_'+ treeNode.uuid +'">' + 
                                        '<div class="row" style="margin: 0">' + 
                                            '<div class="form-group" style="margin: 0">' + 
                                                '<div class="form-group wildmode">' + 
                                                    '<label class="control-label col-md-3 wildcardmodelabel">'+ LANG.UI_FILE_WILDCARD_BAK_WAY +'</label>' + 
                                                    '<div class="col-md-5">' + 
                                                        '<select class="wildcardmode form-control select2me">' + 
                                                            '<option value="0">'+LANG.UI_FILE_WILDCARD_RULES_NO_USE+'</option>' + 
                                                            '<option value="1">'+LANG.UI_FILE_WILDCARD_BAK_FILTER+'</option>' + 
                                                            '<option value="2">'+ LANG.UI_FILE_WILDCARD_BAK_SELECT +'</option>' + 
                                                        '</select>' + 
                                                    '</div>' + 
                                                    '<div class="col-md-1 mt5">' + 
                                                        '<a class="popovers"data-container="body"data-trigger="hover"data-placement="right"data-html="true"data-content="'+ LANG.UI_FILE_WILDCARD_BAK_MODE_TIPS +'"style="margin-left: 0px"data-original-title=""title="">' + 
                                                            '<i class="viconfont vicon-tishi"></i>' + 
                                                        '</a>' + 
                                                    '</div>' + 
                                                '</div>' + 
                                                '<div class="form-group wildcarddiv display-none"style="margin-bottom: 0;">' + 
                                                    '<label class="control-label col-md-3 wildcardlabel">'+LANG.UI_FILE_WILDCARD+'</label>' + 
                                                    '<div style="padding:0;" class="col-md-5 allWildcardInput">' + 
                                                        '<div class="col-md-10">' + 
                                                            '<input type="text"class="wildcardInputdiv form-control" class="form-control input-sm wildcardInput">' + 
                                                        '</div>' + 
                                                        '<div class="col-md-2">' + 
                                                            '<button type="button"class="btn btn-primary addInput input-sm" style="height: 33px">'+ LANG.UI_BACKUP_FILE_ADD +'</button>' + 
                                                        '</div>' + 
                                                    '</div>' + 
                                                    '<div class="col-md-1 mt5">' + 
                                                        '<a class="popovers"data-container="body"data-trigger="hover"data-placement="right"data-content="'+ LANG.UI_FILE_WILDCARD_RULES_ADD_TIPS +'"style="margin-left: 0px">' + 
                                                            '<i class="viconfont vicon-tishi"></i>' + 
                                                        '</a>' + 
                                                    '</div>' + 
                                                '</div>' + 
                                            '</div>' + 
                                        '</div>' + 
                                    '</div>' + 
                                '</div>' + 
                            '</div>' + 
                        '</div>' + 
                    '</div>' + 
                '</div>' + 
            '</div>' + 
        '</div>';
        $('#allClusterTree').append(agentContent);
        $('.popovers').popover({
            html:true
        });

        //修改备份任务如果配置过通配符，应该回显出来
        if(EDIT_BACKUP_FLAG && JSON.stringify(wildcardObj) !== '{}'){
            let wildcardmode = parseInt(wildcardObj.wildcard_mode);
            // 回显通配符备份方式
            $(`#high_tabagent_tree_${treeNode.id}`).find('.wildcardmode').val(wildcardmode);
            var des = ''
            if (wildcardmode !== 0) {
                $(`#high_tabagent_tree_${treeNode.id}`).find('.wildcarddiv').show();
                // $(`#high_tabagent_tree_${treeNode.id}`).find('.wildcard-list').show();
                if( wildcardObj.wildcard && wildcardObj.wildcard.length >0 ){
                    wildcardObj.wildcard.forEach(eachwildcard => {
                        des += '<div class="delcontent"><span type="text"class="input-sm wildcardInput">'+ eachwildcard +'</span><button type="button"class="btn btn-danger delInput input-sm">×</button></div>';
                    })
                }
                $('#high_tabagent_tree_'+treeNode.id).find('.allWildcardInput').html('<div class="col-md-10"><input type="text"class="form-control wildcardInputdiv" class="form-control input-sm wildcardInput"></div><div class="col-md-2 pd0"><button type="button"class="btn btn-primary addInput">'+ LANG.UI_BACKUP_FILE_ADD +'</button></div>'+des);
            }
        }
        if(EDIT_BACKUP_FLAG){
            //修改的时候如果开启了快照 需要禁用通配符修改
            if (taskInfoSetting.high.snap_shot_flag){
                $(`#high_tabagent_tree_${treeNode.id}`).find('.wildcardmode').attr('disabled','disabled');
                $('#high_tabagent_tree_'+treeNode.id).find('.addInput').attr('disabled','disabled');
                $('#high_tabagent_tree_'+treeNode.id).find('.delInput').hide();
            }
        }
    
    }
    //初始化树
    var initFileTree  = function(treeId,treeNode,applyFlag,applyPathList){
        var div = "#clusterTree_"+treeNode.uuid;
        Metronic.blockUI({target: div,animate: true});
        //获取该集群下的目录树
        if(applyFlag){
            var params = {
                "hadoop_cluster_id":treeNode.uuid,
                "limit_count":_pageSize,
                "fetch_root_dir":"",
                "start_after":"",
                "edit_flag":true,
                "start":0,
                "taskuuid": EDIT_BACKUP_FLAG ? taskInfoSetting.taskuuid : '',
                "apply_path_list":applyPathList,
            }
            
        }else{
            var params = {
                "hadoop_cluster_id":treeNode.uuid,
                "limit_count":_pageSize,
                "fetch_root_dir":"",
                "start_after":"",
                "edit_flag":!!EDIT_BACKUP_FLAG,
                "start":0,
                "taskuuid": EDIT_BACKUP_FLAG ? taskInfoSetting.taskuuid : ''
            }
        }
        Metronic.blockUI({target: `#fileClusterTree_${treeNode.uuid}`, animate: true});
        pAjaxRequest(params, '/api/v1/hadoop/backup/cluster/file_ztree', "GET", function(d){
            Metronic.unblockUI(`#fileClusterTree_${treeNode.uuid}`);
            if(!d.success) {
                UIToastr.showWarning(LANG.UI_HADOOP_GET_FOLDER_TREE,d.message);
                return;
            }else{
                //初始化文件树
                setFileTree(d.data);
            }
        }, true);

        data.src_info.cluster_uuid_list.push(treeNode.uuid);
        $('#step1tips').hide();
		$('#clusterfilediv').show();
    }
    //设置文件树
    var setFileTree  = function(data){
        var setting = {
			check: {
				enable: true,
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
				beforeClick: fileNodeClick,
				beforeExpand: fileNodeExpand
			},
			view: {
				dblClickExpand: false
			}
		};
        var cluster_uuid =  data["file_nodes"][0].clusteruuid;
        zTreeFile[cluster_uuid]  =  $.fn.zTree.init($('#fileClusterTree_' + cluster_uuid), setting, data["file_nodes"]);
    }
    //文件点击
    var fileNodeClick =  function(treeId, pNode, clickshow){
        //1文件 2 文件夹 3 链接文件
        if(pNode.filetype == 1 || pNode.filetype == 3){ 
            return
        }else{
            //如果不是文件，就加载文件和目录树
            if(pNode.more){
                //加载更多
                getMoreTree(treeId,pNode);
                return;
            }
            _path = pNode.filepath;
            fileNodeExpand(treeId,pNode);
            $.fn.zTree.getZTreeObj(treeId).expandNode(pNode, true);
        }
    }
    //文件展开
    var fileNodeExpand =  function(treeId,pNode){
        if(pNode.children || pNode.type == 1 || pNode.type == 2) return true;
        getFileSubTree(treeId,pNode);
    }
    //文件加载更多
    var getMoreTree =  function(treeId,pNode){
        //显示更多
        //判断父节点下的子节点是否全选
        var checkeFlag = pNode.getParentNode().check_Child_State==2 ? true : false;
        var params = {
            "hadoop_cluster_id":pNode.clusteruuid,
            "limit_count":_pageSize,
            "fetch_root_dir":pNode.getParentNode().filepath, //父级路径
            "start_after":pNode.next_start == undefined ? "" : pNode.next_start, //上一个节点路径
            "edit_flag":!!EDIT_BACKUP_FLAG,
            "start":pNode.next_index == undefined ? 0 : pNode.next_index,
            "taskuuid": EDIT_BACKUP_FLAG ? taskInfoSetting.taskuuid : ''
        }
        var div = "#fileClusterTree_" + pNode.clusteruuid;
        Metronic.blockUI({target: div,animate: true});
        pAjaxRequest(params, '/api/v1/hadoop/backup/cluster/file_ztree', "GET", function(d){
            Metronic.unblockUI(div);
            if(!d.success) {
                UIToastr.showWarning(LANG.UI_HADOOP_GET_FOLDER_TREE,d.message);
                return;
            }else{
                if(checkeFlag){
                    for(var i = 0;i < d['data']['file_nodes'].length;i++) {
                        d['data']['file_nodes'][i].checked = true;
                    }
                }
                $.fn.zTree.getZTreeObj(treeId).removeNode(pNode);
                $.fn.zTree.getZTreeObj(treeId).addNodes(pNode.getParentNode(), d['data']['file_nodes'], true);
	        	$.fn.zTree.getZTreeObj(treeId).expandNode(pNode, true);
            }
        },true);
    }
    //获取文件子树
    var getFileSubTree =  function(treeId,pNode){
        var params = {
            "hadoop_cluster_id":pNode.clusteruuid,
            "limit_count":_pageSize,
            "fetch_root_dir":pNode.filepath, //父级路径
            "start_after":"", //第一次使用空字符串
            "edit_flag":false,
            "start":0
        }
        var div = "#fileClusterTree_" + pNode.clusteruuid;
        Metronic.blockUI({target: div,animate: true});
        pAjaxRequest(params, '/api/v1/hadoop/backup/cluster/file_ztree', "GET", function(d){
            Metronic.unblockUI(div);
            if(!d.success) {
                UIToastr.showWarning(LANG.UI_HADOOP_GET_FOLDER_TREE,d.message);
                return;
            }else{
                $.fn.zTree.getZTreeObj(treeId).removeChildNodes(pNode);
                $.fn.zTree.getZTreeObj(treeId).addNodes(pNode, d['data']['file_nodes'], true);
	        	$.fn.zTree.getZTreeObj(treeId).expandNode(pNode, true);
            }
            //设置勾选
            if(pNode.checked && pNode.children){
                pNode.children.forEach(item=>{
                    $.fn.zTree.getZTreeObj(treeId).checkNode(item,true);
                });
            }
        }, true);

    }
    //应用到其他集群
    var applyToAllCluster = function(){
        var str = $(this).parents('.panel-collapse')[0].id;
		var index=str.lastIndexOf("_");
    	str=str.substring(index+1,str.length);
		var clusterNodes = zTree.getCheckedNodes(true);
        //通配符信息
		var wildcardarr = [];
		var wildcardmode = $("#high_tabagent_tree_" + str).find(".wildcardmode").val();
		var inputdiv = $("#high_tabagent_tree_" + str).find(".wildcardInput");
		for(var i = 0; i < inputdiv.length; i++) {
			wildcardarr.push(inputdiv[i].innerHTML);
		}
        clusterNodes.forEach(item => {
			//循环选中的子节点，不包括当前应用的文件树
			if(item.uuid != str) {
				//得到当前树选中的节点
				var fileNodes = zTreeFile[str].getCheckedNodes(true);
				fileNodes = filterFile(fileNodes);
				if(fileNodes.length == 0) {
					UIToastr.showWarning(LANG.UI_FILE_SELECT_PLEASE, LANG.UI_FILE_NO_FILE_BE_SELECTED);
					return;
				}
				//应用选中文件到其他集群
                initFileTree(item.tId,item,true,fileNodes,true,fileNodes);
				//应用通配符到其他集群
				applyWildcard(wildcardmode,wildcardarr,item.uuid);
			}
		});
    }
    var filterFile = function (allfileNodes) {
		var allCheckedNode = [];
		//得到所有勾选状态是全选中的节点
		for(var i=0; i<allfileNodes.length; i++){
			//check_Child_State == -1不存在子节点（文件或者全选的无子节点的文件夹）  2是所有子节点被勾选
			if((allfileNodes[i].check_Child_State == 2 || allfileNodes[i].check_Child_State == -1)&&(allfileNodes[i].type == 3)) {
				allCheckedNode.push(allfileNodes[i]);
			}
		}
		//过滤掉重复的
		for(var m = 0;m<allCheckedNode.length;m++) {
			if(allCheckedNode[m].filetype == 2) {//文件夹
				for(var n = 0;n<allCheckedNode.length;n++) {
					var str = allCheckedNode[n].pId==null ?'':allCheckedNode[n].pId;
					if(str.includes(allCheckedNode[m].filepath) && allCheckedNode.indexOf(allCheckedNode[n])!=-1) {//判断全选的文件夹下是否还有文件  有则从数组中删除
						allCheckedNode.splice(allCheckedNode.indexOf(allCheckedNode[n]),1);
						n--;
					}
				}
			}
		}
		//获取pathname和pathtype
		var arr = [];
		allCheckedNode.forEach(item => {
			arr.push({
				"path_name": item.filepath,
				"path_type": item.filetype
			});
		});
		return arr;
	}
    var applyWildcard = function(wildcardmode,wildcardarr,agentuuid) {
		$("#high_tabagent_tree_" + agentuuid).find(".wildcarddiv").hide();
		$("#high_tabagent_tree_" + agentuuid).find(".addInput").parent('.col-md-2').nextAll().remove();
		$("#high_tabagent_tree_" + agentuuid).find(".wildcardmode").val(wildcardmode);
		if(wildcardmode != 0) {
			$(".wildcarddiv").show();
		}else {
			return;
		}
		wildcardarr.reverse();
		wildcardarr.forEach(item => {
			$("#high_tabagent_tree_" + agentuuid).find(".addInput").parent('.col-md-2').after('<div class="delcontent"><span type="text"class="input-sm wildcardInput">'+ item +'</span><button type="button"class="btn btn-danger delInput input-sm">×</button></div>');
		});
        //如果是修改且开启了快照 需要禁用通配符删除
        if(EDIT_BACKUP_FLAG && taskInfoSetting.high.snap_shot_flag){
            $("#high_tabagent_tree_" + agentuuid).find('.delInput').hide();
        }
		
	}

    var step1Valid =  (showTitleCallback)=>{
        if(EDIT_BACKUP_FLAG){
            var allnodes = zTree.transformToArray(zTree.getNodes());
            allnodes.forEach(item => {
                if(item.chkDisabled) {
                    item.changed =  true;
                    item.chkDisabled = false;//取消chkDisabled，使getCheckedNodes获取到离线的集群
                }
            });
        }
        var nodes = zTree.getCheckedNodes(true);
        data.src_info.file_info =  [];
        data.high_info.newstr.wildcard_list = [];
        var checkChildFileArr = [];
        var checkoutFlag =  true;
        var filecheck =  false;
        oldfilelist = [];
        oldshowfile = [];
        nodes.forEach(item=>{
            //设置第二个判断条件 备份和修改备份不一样
            var secondflag =  false;
            if(EDIT_BACKUP_FLAG){
                if((zTreeFile[item.uuid]!=undefined && zTreeFile[item.uuid].getCheckedNodes(true).length ==0) || zTreeFile[item.uuid] == undefined){
                    secondflag =  true;
                }
            }else{
                if(zTreeFile.length == 0 || (zTreeFile[item.uuid] != undefined && zTreeFile[item.uuid].getCheckedNodes(true).length == 0)){
                    secondflag =  true;
                }
            }
            //所有选中的文件(未过滤)
            if(zTreeFile[item.uuid] != undefined && zTreeFile[item.uuid].getCheckedNodes(true).length !=0 ){
                data.src_info.file_info.push(zTreeFile[item.uuid].getCheckedNodes(true));
                checkChildFileArr.push(zTreeFile[item.uuid].getCheckedNodes(true));
            }else if(secondflag){ 
                //判断每个集群是否都选择了文件
                filecheck = true;//判断每个客户端是否都选择了文件
            }else if((zTreeFile[item.uuid] == undefined) && EDIT_BACKUP_FLAG){ //保存离线的集群信息
                taskInfoSetting.fileinfo.forEach(eachfile=> {
					if(eachfile.agent_uuid == item.uuid) {
                        oldfilelist.push([eachfile.type,eachfile.path,eachfile.agent_uuid,'',eachfile.code_type]);
                        oldshowfile.push([eachfile.path,eachfile.agent_uuid]);
                    }
				});
            }
            var wildcardInput = []; //所有通配符
            if($('#high_tabagent_tree_'+item.uuid).find('span.wildcardInput').length==0 && $('#high_tabagent_tree_'+item.uuid).find('.wildcardmode').val() != 0 && $('#high_tabagent_tree_'+item.uuid).find('.wildcardmode').val() != undefined) {
                UIToastr.showInfo(LANG.UI_FILE_WILDCARD_RULES_TIPS3);
                    checkoutFlag = false;
                    return false;
            }
            for(var i=0;i<$('#high_tabagent_tree_'+item.uuid).find('span.wildcardInput').length;i++) {
                var str = $.trim($('#high_tabagent_tree_'+item.uuid).find('span.wildcardInput').eq(i)[0].innerText);
                if(str != "") {
                    //通配符输入不能包含特殊符号,不包含  /  :  "  <  >  | \
                    var specialchar = ['/', ':','"','<','>','|','\\'];
                    for (var key in specialchar) {
                        if (str.indexOf(specialchar[key]) != -1) {
                            UIToastr.showInfo(LANG.UI_FILE_WILDCARD_RULES_TIPS1);
                            checkoutFlag = false;
                            return false;
                        }
                    }
                    // 不允许*和？相邻时 输入*在前？在后的情况
                    if(str.indexOf("*?") != -1){
                        UIToastr.showInfo(LANG.UI_FILE_WILDCARD_RULES_TIPS2);
                        checkoutFlag = false;
                        return false;
                    }
                }else if($('#high_tabagent_tree_'+item.uuid).find('.wildcardmode').val()!=0){
                    UIToastr.showInfo(LANG.UI_FILE_WILDCARD_RULES_TIPS3);
                    checkoutFlag = false;
                    return false;
                }
                wildcardInput.push(str); 
            }
            //离线且选中的
            if(item.changed && item.checked && EDIT_BACKUP_FLAG){
                //通配符
                taskInfoSetting.high.wild_card_info.forEach(eachwildcard=> {
                    if(eachwildcard.agent_uuid == item.uuid) {
                        if(eachwildcard.wildcard_mode==0) {
                            data.high_info.newstr.wildcard_list.push([item.uuid,[],"0",[]]);
                        }else{
                            data.high_info.newstr.wildcard_list.push([item.uuid,eachwildcard.wildcard,eachwildcard.wildcard_mode,eachwildcard.wildcard_real_length]);
                        }
                    }
                });
            }else{
                data.high_info.newstr.wildcard_list.push([item.uuid,wildcardInput,$('#high_tabagent_tree_'+item.uuid).find('.wildcardmode').val()]);
            }
        });
           
        // 加入离线集群
        if(EDIT_BACKUP_FLAG){
            //还原chkDisabled
            allnodes.forEach(item => {
                if(item.changed) {
                    item.chkDisabled = true;
                }
            });
        }
        if(nodes.length == 0){
			UIToastr.showWarning(LANG.UI_HADOOP_NOT_SELECT_BACKUP_CLUSTER, LANG.UI_HADOOP_NOT_SELECT_BACKUP_CLUSTER_TIPS);
			return false;
		}
        if(!filecheck) {
			filecheck = checkChildFile(checkChildFileArr);
		}
        if(filecheck){//检查是否每个已选中客户端都选中了文件
			UIToastr.showWarning(LANG.UI_BACKUP_FILE_NO_SELECT_TITLE1, LANG.UI_BACKUP_FILE_NO_SELECT_VALUE1);
			return false;
		}
        if(!checkoutFlag) return false; 
        //初始化时间策略模块
        Strategy.initModule(hadoop_backup);
        //初始化策略描述
        initStrateyDes();

		showStep1();
        getHadoopCurrentUseLicense().then(showTitleCallback); 
		return false;
    }
    var checkChildFile = function(filelist) {
		var allfilestate = [];
		filelist.forEach(eachCluster => {
			var filestate = [];
			eachCluster.forEach(item => {
				if(item.check_Child_State == -1) {
					filestate.push(item.check_Child_State);
				}
			});
			allfilestate.push(filestate);
		});
		var result = allfilestate.some(eacharr=>{
			return eacharr.length == 0;
		});
		return result;
	}
    //初始化策略描述
	var initStrateyDes = function(){
		initStoreStrategyDes();
		initReserveStrategyDes();
		// initHighStrategyDes();//初始化高级策略
    }
	
    var showStep1 =  function(){
        //初始化计时器
        var showStr = '';
        var backupmode4Info = '';
        var eachwildcard2 = '';
        var nodes = zTree.getCheckedNodes(true);
        var allCheckedNode = [];
        var showFileArr = [];
        //获取任务名
        if(!EDIT_BACKUP_FLAG){
            getTaskName();
        }
        //得到所有勾选状态是全选中的节点
        for(var i=0; i<data.src_info.file_info.length; i++){
            var eachAgentNode = [];
            for(var j=0; j<data.src_info.file_info[i].length; j++) {
                //check_Child_State == -1不存在子节点（文件或者全选的无子节点的文件夹）  2是所有子节点被勾选
                if((data.src_info.file_info[i][j].check_Child_State == 2 || data.src_info.file_info[i][j].check_Child_State == -1 )&&(data.src_info.file_info[i][j].type == 3)) {
                    eachAgentNode.push(data.src_info.file_info[i][j]);
                }
            }
            //循环完一个客户端之后  过滤掉单个客户端中重复的
            for(var m = 0;m<eachAgentNode.length;m++) {
                if(eachAgentNode[m].filetype == 2) {//文件夹
                    for(var n = 0;n<eachAgentNode.length;n++) {
                        var str = eachAgentNode[n].pId==null ?'':eachAgentNode[n].pId;
                        if(str.includes(eachAgentNode[m].filepath) && eachAgentNode.indexOf(eachAgentNode[n])!=-1) {//判断全选的文件夹下是否还有文件  有则从数组中删除
                            eachAgentNode.splice(eachAgentNode.indexOf(eachAgentNode[n]),1);
                            n--;
                        }
                    }
                }
            }
            allCheckedNode.push(eachAgentNode);
        }
        //二维数组转一维数组
        allCheckedNode = [].concat.apply([], allCheckedNode);
        data.src_info.file_info = [];
        selectfile =  false;
        selectdiffwild =  false;
        allCheckedNode.forEach(item=> {
            if(item.filetype == 1 && selectfile == false ){
                selectfile =  true;
            }
            data.src_info.file_info.push([item.filetype,item.filepath,item.clusteruuid,'',item.code_type]);//组装发送给后台的文件信息
            showFileArr.push([item.filepath,item.clusteruuid]);
        })
        //获取secectdiffwild的值 //判断每个集群是否都选择的不使用通配符过滤
        data.high_info.newstr.wildcard_list.forEach(eachwildcard=> {
            if(eachwildcard[2] != 0){
                selectdiffwild =  true;
                return;
            }
        })
        if(!EDIT_BACKUP_FLAG){
            nodes.forEach((item)=>{
                //设置文件列表显示
                showStr +='<strong>'+ item.name+ ':' + '</strong><br>';
                showFileArr.forEach(eachpath=> {
                    if(item.uuid==eachpath[1]) {
                        showStr += eachpath[0]+ ';' + '<br>';
                    }
                });
                //设置通配符显示
                backupmode4Info += '<strong>'+ item.name+ ':' + '</strong><br>';
                data.high_info.newstr.wildcard_list.forEach(eachwildcard=> {
                    if(eachwildcard[0]==item.uuid) {
                        if(eachwildcard[1]!="") {
                            backupmode4Info += LANG.UI_FILE_WILDCARD +'：'+ eachwildcard[1]+';' + '<br>';
                        }
                        if(eachwildcard[2]==0) {
                            eachwildcard2 = LANG.UI_FILE_WILDCARD_RULES_NO_USE;
                        }else if(eachwildcard[2]==1) {
                            eachwildcard2 = LANG.UI_FILE_WILDCARD_BAK_FILTER;
                        }else {
                            eachwildcard2 =LANG.UI_FILE_WILDCARD_BAK_SELECT;
                        }
                        backupmode4Info += LANG.UI_FILE_WILDCARD_BAK_MODE +'：'+ eachwildcard2 +';' + '<br>';
                    }
                });
            });
        }else{
            data.src_info.file_info = data.src_info.file_info.concat(oldfilelist);
            showFileArr =  showFileArr.concat(oldshowfile);
            data.high_info.newstr.wildcard_list.forEach((item)=>{
                var node = zTree.getNodesByParam("uuid", item[0], null);
                //设置文件列表显示
                showStr +='<strong>'+ node[0].name+ ':' + '</strong><br>';
                showFileArr.forEach(eachpath=> {
                    if(node[0].uuid==eachpath[1]) {
                        showStr += eachpath[0]+ ';' + '<br>';
                    }
                });
                //设置通配符显示
                backupmode4Info += '<strong>'+ node[0].name+ ':' + '</strong><br>';
                data.high_info.newstr.wildcard_list.forEach(eachwildcard=> {
                    if(eachwildcard[0]==node[0].uuid) {
                        if(eachwildcard[1]!="") {
                            backupmode4Info += LANG.UI_FILE_WILDCARD +'：'+ eachwildcard[1]+';' + '<br>';
                        }
                        if(eachwildcard[2]==0) {
                            eachwildcard2 = LANG.UI_FILE_WILDCARD_RULES_NO_USE;
                        }else if(eachwildcard[2]==1) {
                            eachwildcard2 =LANG.UI_FILE_WILDCARD_BAK_FILTER;
                        }else {
                            eachwildcard2 =LANG.UI_FILE_WILDCARD_BAK_SELECT;
                        }
                        backupmode4Info += LANG.UI_FILE_WILDCARD_BAK_MODE+'：'+ eachwildcard2 +';' + '<br>';
                    }
                    
    
                });
            });
        }

        $('.backupmodeshow4').html($('.wildcardlabel').html()+':');
		$('.backupmodeshow4list').html(backupmode4Info);
		// $('.agentshow').html(agentInfo);
		$('#filelist').html(showStr);
        //设置step3中的快照是否禁用
        if(selectfile || selectdiffwild){
            //选择了文件就需要禁用快照（hadoop特殊性）
            $('#silentsnapshotcheck').bootstrapSwitch('state', false);  
            $('#silentsnapshotcheck').bootstrapSwitch('disabled', true);
            //还需要修改快照的提示
            $('#snapshotchecktips1').hide();
            $('#snapshotchecktips2').show();

        }else{
            //如果之前是禁用 那么需要配置成默认打开（因为如果之前是文件会禁用 返回第一步选择目录需要默认打开 如果之前选择的就是目录 那么不改变这个状态）
            var flag =  false;
            if( $('#silentsnapshotcheck').prop('disabled')){
                flag =  true;
            }
            $('#silentsnapshotcheck').bootstrapSwitch('disabled', false);
            if(flag ==  true){
                $('#silentsnapshotcheck').bootstrapSwitch('state', true);  
            }
            $('#snapshotchecktips1').show();
            $('#snapshotchecktips2').hide();
        }
        //如果是修改 如果用户在第二步提交那么需要再第一步完了之后重新设置快照的值
        if(EDIT_BACKUP_FLAG){
            data.high_info.newstr.silentsnapshotcheck = $('#silentsnapshotcheck').get(0).checked;//快照
        }
    }
    var step2Valid =  function(){
        var result = getNodeStr();
		if(result) {
			showStep2();
		}
		return result;
    }

    //得到备份节点信息
	var getNodeStr = function(){
		//TODO
        if (!$('#backupTarget').backupTarget('validateSelect')) {
			return false;
		}
       //备份目的地(节点)
		backupTargetInfo = $('#backupTarget').backupTarget('getSelect');
		data.high_info.node.nodecheck = !backupTargetInfo.node_uuid;
        data.high_info.node.storagecheck = !backupTargetInfo.storage_uuid;
        data.high_info.node.storageuuid = backupTargetInfo.storage_uuid;
        data.high_info.node.storage_pool_uuid = backupTargetInfo.storage_pool_uuid;
		data.high_info.node.nodeuuid = backupTargetInfo.node_uuid;
		data.high_info.node.node_pool_uuid = backupTargetInfo.node_pool_uuid;
		data.high_info.node.storage_type = backupTargetInfo.storage_type;
        $('.nodeInfoShow').html(backupTargetInfo.node_text);
		$('.storageInfoShow').html(backupTargetInfo.storage_text);
		// data.high_info.node.node_uuid = '';
		// data.high_info.node.storagecheck = true;
		// data.high_info.node.storage_uuid = '';
		// data.high_info.node.nodecheck = false;
		// data.high_info.node.node_uuid = $('#selectnode').val();
		// data.high_info.node.storagecheck = false;
		// data.high_info.node.storage_uuid = $('#selectstorage').val();
        //磁带不支持永久增量
		if(backupTargetInfo.storage_type == CONF.BD_STORAGE_TYPE.TAPE){
            $('.pIncr').hide();
            $('#strategymode').find('input[data-mode=9]').iCheck('uncheck');
            $('#stragegyaccordion').find('.strategy-panel[data-mode=9]').hide();
            //修改时间策略后的提示信息
			$('.all-strategy-tips').hide();
			$('.no-pIncr-tips').show();
            //磁带存储屏蔽安全策略
            $('.safetyLi').hide();
            //取消active 
            if($('.safetyLi').hasClass('active')){
                $('.safetyLi').removeClass('active');
                $('#tab_safety').removeClass('active');
                // 给通用策略添加active 返回通用策略
                $('.commonLi').addClass('active')
                $('#tab_common').addClass('active');
            }
        }else{
            $('.pIncr').show();
			$('.no-pIncr-tips').hide();
            $('.all-strategy-tips').show();
            //其他存储显示安全策略
            $('.safetyLi').show();
        }
        //初始化WORM防护
        initWormConfig();
        return true;
	}
    var initWormConfig = function(){
        // 安全策略未授权隐藏
        if (!CONF.FUNCTIONS.includes('worm')) {
            $('#wormConfig').hide();
        }
        if (!CONF.FUNCTIONS.includes('integrity')) {
            $('#completeConfig').hide();
        }
        if (!CONF.FUNCTIONS.includes('worm') && !CONF.FUNCTIONS.includes('integrity')) {
            $('.safetyLi').hide();
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
            } else {
                if(EDIT_BACKUP_FLAG){
                    let safeConfigStrategy = taskInfoSetting.safeStrategy;
                    $('#wormConfig').wormProtectionBackup($.fn.wormDefine.worm_type.normal, 'col-md-3', false, safeConfigStrategy.worm_flag, safeConfigStrategy.worm_protection_time);
                    $('#wormConfig_check').trigger('switchChange.bootstrapSwitch');
                }else{
                    $('#wormConfig').wormProtectionBackup($.fn.wormDefine.worm_type.normal);
                }
            }
        }
    }
    var showStep2 =  function(){
        //判断是否是磁带
        //获取选择的存储类型
        var selectedStorageType = data.high_info.node.storage_type;
        var storage_uuid = data.high_info.node.storageuuid;
        //判断是否是磁带，参数：存储uuid，存储类型，线程配置项div控制显示隐藏，线程输入框初始化spinner时的dom，初始化备份策略时的dom，模块类型
		getTapeStrategy(storage_uuid, selectedStorageType,'.threadDiv', '.backupThreadDiv', '#tab_common', CONF.MODULE_TYPE.HADOOP);
        initResourceLimit(backupTargetInfo.node_uuid_list);
        // if(selectedStorageType == CONF.BD_STORAGE_TYPE.CLOUD){
        //     $('#reserveMode').val(2).prop('disabled', true); // 禁用备份数据保留类型
        // }else{
        //     $('#reserveMode').removeAttr('disabled');
        // }
        if(selectedStorageType != CONF.BD_STORAGE_TYPE.TAPE) {
			initReserveStrategyDes();
		}
        //初始化传输线程
        //判断传输线程权限
		if(CONF.FUNCTIONS.includes('multithread') && selectedStorageType != CONF.BD_STORAGE_TYPE.TAPE){
			//如果授权线程
			$(".threadDiv").show();
		}else{
			//默认值为1
			$('.backupThreadDiv').spinner({value: 1, step: 1, min: 1,max: 32});//传输线程
			$(".threadDiv").hide();
		}
        // initHighStrategyDes();
    }


    var getTaskName =  function(){
        pAjaxRequest({}, '/api/v1/hadoop/backup/taskname', "GET", function(d){
            $('#jobname').val(d.data);
        },true)
    }
    
    var step3Valid = function(){
        var result = getTimeStr();
    	if(!result) return false;
		var result = getReserveStr() & getTransferStr() & getSpeedStr() & getStoreStr() & getSafeStr() & getHighConf();
		if(result){
			var strategyMode = $('#strategymode').find('input:checked');
			return showStep3(strategyMode);
		}
		return result;
	}
    var showStep3 = function(strategyMode){
		var reservetypeshow = $('.reservetypeshow');
        var selectedStorageType = data.high_info.node.storage_type;
        if (selectedStorageType != CONF.BD_STORAGE_TYPE.TAPE) { // 选的存储类型为磁带时
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
             reservetypeStr += methoddes + ': ' + data.high_info.reserve.value;
            //保留策略
            reservetypeshow.html(reservetypeStr);
        }
        // -----------------------传输策略start------------------------
		//传输策略
		let transDes = '';
        //如果开启了传输代理 需要加上加密传输
        if(data.appliancecheck){
            transDes += LANG.UI_COPY_BACK_ENCRYPT + ': ' + getSwitchDes(data.high_info.transfer.encrypt) + '<br>';
            if($('#transport_encrypt_flag').get(0).checked){
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
        }
        $('.transferinfoshow').html(transDes);

		//高级策略-快照、线程数量、通配符
		data.high_info.newstr.silentsnapshotcheck = $('#silentsnapshotcheck').get(0).checked;//快照
        data.high_info.newstr.backup_thread_num = $('#backupThreadNum').val();//线程数量
        if(data.high_info.newstr.backup_thread_num == "" || data.high_info.newstr.backup_thread_num > 32 || data.high_info.newstr.backup_thread_num <= 0
		|| !/^\d+$/.test(data.high_info.newstr.backup_thread_num)){
			//重置为默认值
			$('.backupThreadDiv').spinner("value", 3);
			// initHighStrategyDes();
			UIToastr.showWarning(LANG.UI_BACKUP_HIGH_SETTING_TITLE, LANG.UI_FILE_BACKUP_THREAD_NUM_TIPS);
			return false;
		}
		//扫描线程数量
		data.high_info.newstr.scan_thread_num = $('#scanThreadNum').val();
		if(data.high_info.newstr.scan_thread_num == "" || data.high_info.newstr.scan_thread_num > 32 || data.high_info.newstr.scan_thread_num <= 0
		|| !/^\d+$/.test(data.high_info.newstr.scan_thread_num)){
			//重置为默认值
			$('.scanThreadDiv').spinner("value", 3);
			// initHighStrategyDes();
			UIToastr.showWarning(LANG.UI_BACKUP_HIGH_SETTING_TITLE, LANG.UI_FILE_BACKUP_THREAD_NUM_TIPS);
			return false;
		}
		//扫描文件数量--扫描线程数为1时 文件扫描速度可选  其他情况默认文件扫描速度为0（无线）
        if(data.high_info.newstr.scan_thread_num == 1) {
			data.high_info.newstr.scan_file_num = $('#scanFileNum').val();
		}else {
			data.high_info.newstr.scan_file_num = 0;
		}
        
		var backupmode1Info = $('.silentsnapshotlabel').html() + ": " + getSwitchDes(data.high_info.newstr.silentsnapshotcheck);
		var backupmode3Info = $('.threadnumlabel').html() + ": " + data.high_info.newstr.backup_thread_num + "<br>";
		var backupmode5Info = $('.scanthreadlabel').html() + ": " + data.high_info.newstr.scan_thread_num + "<br>";
		if(data.high_info.newstr.scan_thread_num == 1) {
			$('.backupmodeshow6').show();
			var backupmode6Info = $('.scanfilelabel').html() + ": " + getScanSpeed(parseInt(data.high_info.newstr.scan_file_num))+ '<br>';
		}else {
			$('.backupmodeshow6').hide();
		}
		data.high_info.store.compress = $('#compressCheck').get(0).checked;//压缩
        data.high_info.store.compress_method = 0;
		// 压缩等级
        if($('#compressCheck').get(0).checked){
            data.high_info.store.compress_method = $('#compressGrade').val();
        }
		data.high_info.store.dataencrypt = $('#encryptStorageCheck').get(0).checked; //数据加密
		//存储策略
		var storeInfo = "";
		// 压缩存储
		storeInfo += $('.compressLabel').html() + ": " + getSwitchDes(data.high_info.store.compress);
		// 压缩等级
		if(data.high_info.store.compress){
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
            storeInfo +="<br>" +  LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE + ": " + grade;
		}
		storeInfo += "<br>" + $('.encryptStorageLabel').html() + ": " + getSwitchDes(data.high_info.store.dataencrypt) ;
        // 存储加密
        data.high_info.store.encrypt_method = parseInt($('#storageEncryptMethod').val());
        // 存储加密
        if($('#encryptStorageCheck').get(0).checked){
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
            storeInfo += '<br>' + encryptedMethodLabel + ": " + grade;
        }
        if(data.high_info.store.dataencrypt){
            storeInfo += "<br>" + $('.passwordAutoLabel').html() + ": " + getSwitchDes(data.high_info.store.password_auto_flag);
        }
		//归档  归档目标选择文件3  选择文件和目录1  关闭是2
		if(Object.keys($('#archivecheck')).length != 0 && $('#archivecheck').get(0).checked) {
			data.high_info.file_archive = parseInt($("#archiveSelect").val());
		}else {
			data.high_info.file_archive = 2
		}
		$('.storageinfoshow').html(storeInfo);
        //传输策略

        data.appliancecheck = $('#appliancecheck').get(0).checked;
        let applianceNode = $('#transferAgentTree').transferAgent('getSelect'); // 获取传输代理
		data.applianceuuid = data.appliancecheck ? applianceNode.agent_uuid : '';
        data.appliance_pool_uuid = data.appliancecheck ? applianceNode.agent_pool_uuid : '';
        var appliancelabel = $('.appliancelabel').html();
		var applianceInfo = appliancelabel + ": " + getSwitchDes(data.appliancecheck);
        APPLIANCE_AGENCY_HAS_CONFIGED = $('#transferAgentTree').transferAgent('validateSelect');
        if(data.appliancecheck){
			if(!APPLIANCE_AGENCY_HAS_CONFIGED){
				UIToastr.showWarning(LANG.UI_APPLIANCE_SELECT, LANG.UI_APPLIANCE_SELECT_NO_TIPS);
				return false;
			}
			applianceInfo += ', ' + applianceNode.name;
		}
		$('.applianceshow').html(applianceInfo + "<br>");
		//限速策略
		var speedlimitshow = $('.speedlimitshow');
		var speedlimitsStr = '';

		speedlimitsStr = $('.speedlimitDes').prop('title');

		if (speedlimitsStr == '') {
			speedlimitsStr = LANG.UI_PUBLIC_NOTHING;
		}
		speedlimitshow.html(speedlimitsStr);
		//时间策略
        $('.backupTypeInfoDiv').show();
		var backuptypeshow = $('.backuptypeshow'), backuptypeinfoshow = $('.backuptypeinfoshow');
		var backuptypeshowStr = '', backuptypeinfoshowStr = '';
		if("strategy" == data.backup_info.type){
			backuptypeshowStr = LANG.UI_BACKUP_USE_STRATEGY;
			for(var i=0; i<strategyMode.length; i++){
				if(1 == $(strategyMode[i]).data('mode')){
					backuptypeinfoshowStr += data.backup_info.full_info.des + "<br>";
				}else if(2 == $(strategyMode[i]).data('mode')){
					backuptypeinfoshowStr += data.backup_info.incr_info.des + "<br>";
				}else if(3 == $(strategyMode[i]).data('mode')){
					backuptypeinfoshowStr += data.backup_info.diff_info.des + "<br>";
				}else if(9 == $(strategyMode[i]).data('mode')){
					backuptypeinfoshowStr += data.backup_info.pincr_info.des + "<br>";
				}
			}
		}else if("oncetime" == data.backup_info.type){
			backuptypeshowStr = LANG.UI_BACKUP_ONCE;
			backuptypeinfoshowStr = LANG.UI_PUBLIC_START_TIME + ": " + data.backup_info.datetime;
		} else if ('manual' === data.backup_info.type) {
            backuptypeshowStr = LANG.UI_BACKUP_MANUAL;
            $('.backupTypeInfoDiv').hide();
        }
		if(Object.keys($('#archivecheck')).length != 0) {
			var archiveshowInfo = $('.archivelabel').html() + ": " + getSwitchDes($('#archivecheck').get(0).checked) ;
			var archiveselectInfo = $('.archiveselectdivlabel').html() + ": " + $("#archiveSelect").find("option:selected").text() ;
			$('.archiveshow').html(archiveshowInfo);//归档
			if($('#archivecheck').get(0).checked) {
				$('.archiveselectshow').show();
				$('.archiveselectshow').html(archiveselectInfo);//归档目标
			}
		}
		backuptypeshow.html(backuptypeshowStr);
		backuptypeinfoshow.html(backuptypeinfoshowStr);
		$('.backupmodeshow1').html(backupmode1Info);

        if(selectedStorageType == CONF.BD_STORAGE_TYPE.TAPE || !CONF.FUNCTIONS.includes('multithread')){
            $('.backupmodeshow3').hide();
        }else{
            $('.backupmodeshow3').show();
            $('.backupmodeshow3').html(backupmode3Info);
        }
		$('.backupmodeshow5').html(backupmode5Info);
		$('.backupmodeshow6').html(backupmode6Info);
        //文件权限备份
        data.high_info.permission_operate_flag = $('#file-permission').get(0).checked;
        $('.filepermissionshow').html($('.file-permission-label').html() + ": " + getSwitchDes(data.high_info.permission_operate_flag));
		//跳过文件告警
		data.high_info.skip_file_alarm_flag = $('#passfilealarmcheck').get(0).checked;
		$('.passalarmshow').html($('#tab_except_handle_conf .passfilealarmlabel').html() + ": " + getSwitchDes(data.high_info.skip_file_alarm_flag));
		if(data.high_info.skip_file_alarm_flag) {//开启
			data.high_info.skip_file_alarm_min_num = $('#passFileNum').val();
			data.high_info.skip_file_alarm_min_ratio = $('#warningpercent').val();
			$('.passalarmnumshow').show();
			$('.passalarmpercentshow').show();
			$('.passalarmnumshow').html($('.passfilenumlabel').html() + ": " + data.high_info.skip_file_alarm_min_num);
			$('.passalarmpercentshow').html($('#tab_except_handle_conf .passfilepercentlabel').html() + ": " + data.high_info.skip_file_alarm_min_ratio + '%');
			// 输入数据检测
			if(data.high_info.skip_file_alarm_min_num == "" || data.high_info.skip_file_alarm_min_num > 9999999999 || data.high_info.skip_file_alarm_min_num <= 0
			|| !/^\d+$/.test(data.high_info.skip_file_alarm_min_num)){
				//重置为默认值
				$('.passfilenumDiv').spinner('value', 10);
				UIToastr.showWarning(LANG.UI_NAS_SKIP_FILE_ALARM_NUM, LANG.UI_NAS_SKIP_FILE_ALARM_NUM_TIPS);
				return false;
			}
			if(data.high_info.skip_file_alarm_min_ratio == "" || data.high_info.skip_file_alarm_min_ratio > 100 || data.high_info.skip_file_alarm_min_ratio <= 0
			|| !/^\d+$/.test(data.high_info.skip_file_alarm_min_ratio)){
				//重置为默认值
				$('.warnningdiv').spinner('value', 20);
				UIToastr.showWarning(LANG.UI_NAS_SKIP_FILE_ALARM_RATIO, LANG.UI_NAS_SKIP_FILE_ALARM_RATIO_TIPS);
				return false;
			}
		} else {
			data.high_info.skip_file_alarm_min_num = '';
			data.high_info.skip_file_alarm_min_ratio = '';
			$('.passalarmnumshow').hide();
			$('.passalarmpercentshow').hide();
		}

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
		if ((!CONF.FUNCTIONS.includes('worm') && !CONF.FUNCTIONS.includes('integrity') ) || selectedStorageType ==  CONF.BD_STORAGE_TYPE.TAPE) {
			$('.safeDiv').hide();
		}
		$('.safeStrategyShow').html(safeInfo);


        //--------------安全策略显示end---------------
        //过载保护
        data.high_info.ignore_resource_limiting_flag = !!$('#ignoreResourceLimit').get(0).checked;
        $('.ignoreResourceLimitShow').html($('.ignoreResourceLimitLabel').html() + ": " + getSwitchDes(data.high_info.ignore_resource_limiting_flag));
		return true;
	}
    //扫描文件速度描述
	var getScanSpeed = function (level) {
		var des = "";
		switch(level) {
			case 0: 
				des = LANG.UI_FILE_BAK_SCAN_SPEED_FIVE;
				break;
			case 1000: 
				des = LANG.UI_FILE_BAK_SCAN_SPEED_FOUR;
				break;
			case 800: 
				des = LANG.UI_FILE_BAK_SCAN_SPEED_THREE;
				break;
			case 600: 
				des = LANG.UI_FILE_BAK_SCAN_SPEED_TWO;
				break;
			case 400: 
				des = LANG.UI_FILE_BAK_SCAN_SPEED_ONE;
				break;
		}
		return des;
	}

    //初始化时间策略
    var initTimeStrategy = function(){
        // 初始化任务分布区间
        var timeBack = function (res) {
            if (res.data.time_list.length != 0) {
                $('#backupCrowd').taskCrowd({ timeList: res.data.time_list, showFlag: res.data.show_flag });
            }
            var suggestInfo = res.data.suggest_time;
            defaultStrategy[0] = {
                mode: 1,
                strategy_type: 2,
                days: [0, 0, 0, 0, 1, 0, 0],
                frequency: '',
                start_time: suggestInfo.start_time,
                end_time: suggestInfo.end_time,
                roll_flag: false,
                roll_interval: '01:00:00',
                roll_end_time: suggestInfo.roll_end_time
            };
            defaultStrategy[1] = {
                mode: 2,
                strategy_type: 1,
                days: [],
                frequency: '',
                start_time: suggestInfo.start_time,
                end_time: suggestInfo.end_time,
                roll_flag: false,
                roll_interval: '01:00:00',
                roll_end_time: suggestInfo.roll_end_time
            };
            defaultStrategy[2] = {
                mode: 3,
                strategy_type: 1,
                days: [],
                frequency: '',
                start_time: suggestInfo.start_time,
                end_time: suggestInfo.end_time,
                roll_flag: false,
                roll_interval: '01:00:00',
                roll_end_time: suggestInfo.roll_end_time
            };
            defaultStrategy[3] = {
                mode: 9,
                strategy_type: 1,
                days: [],
                frequency: '',
                start_time: suggestInfo.start_time,
                end_time: suggestInfo.end_time,
                roll_flag: false,
                roll_interval: '01:00:00',
                roll_end_time: suggestInfo.roll_end_time
            };
            //延迟设置,因为这里icheck会默认修改里面的选中事件
            setTimeout(function(){
                $('#backupTimestrategy').strategy({dom: $('#backupTimestrategy'), config: defaultStrategy, display:['display-none', 'display-none', 'display-none', 'display-none'], backup_flag: 1});
            }, 2000);
        }
        pAjaxRequest({}, "/api/v1/jobs/time/crow/list", "GET", timeBack, true);
    }
    //初始化安全策略
    var initSecurityStrategy  = function(){
        //初始化病毒检测
        // $('#virusConfig').virusDetectionBackup('col-md-3', false, true, false);
        //完整性校验
        $('#completeConfig').completeDetectionBackup('col-md-3', true, CONF.MODULE_TYPE.HADOOP);
    }
    //初始化重试策略
    var initRetryStrategy = function(){
        //重试策略
        $('#retry_config').retryStrategy();
    }
    //初始化策略选择列表
	var initStrategySelect = function(){
		if(initStrategyFlag) return; //加载一次
		function initStrategyList(res){
			if(!res.success) return;
			if(res.data.length > 0){
				// 清空策略列表，再插入新的策略列表
				var data = res.data;
				var strategyselect = $('#strategySelect');
				strategyselect.empty();
				for(var i=0; i<data.length; i++){
					var option = '<option  value="' + data[i].uuid + '">' + data[i].text + '</option>';
					globalStrategy[data[i].uuid] = data[i].strategy || [];
					strategyselect.append(option);
				}
                if (taskInfoSetting.strategy_group_uuid) {
                    $('#strategySelect').val(taskInfoSetting.strategy_group_uuid)
                }
				if(!initStrategyFlag){
					$('#strategySelect').searchableSelect();
					$('.searchable-select-item').on('click', strategyHandler);
					initStrategyFlag = true;
				}
			}

		}
		
        pAjaxRequest({type: CONF.MODULE_TYPE.HADOOP}, '/api/v1/strategies/select', "GET", initStrategyList, true);
	}
    // 应用全局策略并初始化策略信息
	var strategyHandler = function(){
		editFlag = false;
		var index = $('#strategySelect option:selected').val();
        //没有应用全局策略
        if(!index){
            // 重置当前应用的全局策略对象
            appliedGlobalStrategy = {
                flag: false,
                uuid: '',
                strategy: {}
            };
            return
        }
		var strategy = globalStrategy[index];
        // 赋值当前应用的全局策略对象
        appliedGlobalStrategy.flag = true;
        appliedGlobalStrategy.uuid = index;
        appliedGlobalStrategy.strategy = strategy;
		// 初始化全局策略数据
        $('#tab_common').backupStrategy(CONF.MODULE_TYPE.FS, false, strategy, 0);
		// 初始化全局策略应用描述
		// // 时间策略
		// initTimeStrategyDes();
		// 存储策略
		initStoreStrategyDes();
		// 保留策略
		initReserveStrategyDes();
		// 限速策略
		if(index == 0) return false;
        var speedInfo = strategy.speedlimit.speedInfo;
        var check = strategy.speedlimit.check;
        if(!check || !speedInfo) return ;
		speedList = [];
		// 将限速策略放进消息中
        for(var i=0;i<speedInfo.length;i++){
			speedList.push(speedInfo[i]);
		}
        //高级策略
        // initHighStrategyDes();
	}

    var initSpeedStrategyDes = function(){
		var titleDes = "";
		var des = "";
		if(speedList.length !=0){
			des += LANG.UI_GLOBAL_STRATEGY_SPEED_NUM + ": " + speedList.length;
		}
        for(var i=0;i<speedList.length;i++){
            titleDes += speedList[i].des + '. ' + "<br>";
		}
		var strategyIndex = $('#strategySelect').val();
		if(strategyIndex && strategyIndex != "" && editFlag){
			var oldDes = globalStrategy[strategyIndex].speedlimit.des;
			initStrategyDesStyle($('.speedlimitDes'), titleDes, oldDes);
		}else{
			$('.speedlimitDes').removeClass('font-green-seagreen');
		}
        $('.speedlimitDes').html(des);
        $('.speedlimitDes').prop('title', titleDes);
        
	}
    //初始化存储策略配置信息
    var initStoreStrategyDes = function(){
		var des = "";
        // 压缩传输
        des += LANG.UI_GLOBAL_STRATEGY_COMPRESS + ": " + getSwitchDes($('#compressCheck').get(0).checked);
		// 压缩等级
        if($('#compressCheck').get(0).checked){
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
        if($('#encryptStorageCheck').get(0).checked){
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
        
    //初始化保留策略配置信息
	var initReserveStrategyDes = function(){
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
    //初始化高级策略配置信息
	// var initHighStrategyDes = function(){
	// 	var des = "";
	// 	des += $.trim($(".scanthreadlabel").text()) + ": " + $("#scanThreadNum").val();
	// 	if($("#scanThreadNum").val() == 1) {
	// 		des += ", " + $.trim($(".scanfilelabel").text()) + ": " + $("#scanFileNum").find("option:selected").text();
	// 	}
	// 	$('.higeDes').html(des);
    //     $('.higeDes').attr('title', des);
	// }
    //勾选备份策略复选框
    var strategyModeClick = function(event){
		var mode = $(this).data('mode');
		if(event.target.checked){
			//如果是取消选中
			$('#stragegyaccordion').find('.strategy-panel[data-mode=' + mode + ']').hide();
		}else{
			//如果是选中
			$('#stragegyaccordion').find('.strategy-panel[data-mode=' + mode + ']').show();
		}
        if (1 == mode) {
			//完全备份
            if (event.target.checked) {
                //取消完备同时取消增量
                $('#strategymode').find('input[data-mode=2]').iCheck('uncheck');
                $('#stragegyaccordion').find('.strategy-panel[data-mode=2]').hide();
                //取消完备同时取消差异
                $('#strategymode').find('input[data-mode=3]').iCheck('uncheck');
                $('#stragegyaccordion').find('.strategy-panel[data-mode=3]').hide();
            } else {
                //选中完备同时取消永久增量
                $('#strategymode').find('input[data-mode=9]').iCheck('uncheck');
                $('#stragegyaccordion').find('.strategy-panel[data-mode=9]').hide();
            }
		} else if (2 == mode) {
			//增量备份
			if (!event.target.checked) {
				//选中增量同时选中完备 取消差异和永久增量
				$('#strategymode').find('input[data-mode=1]').iCheck('check');
				$('#stragegyaccordion').find('.strategy-panel[data-mode=1]').show();
				$('#strategymode').find('input[data-mode=3]').iCheck('uncheck');
				$('#stragegyaccordion').find('.strategy-panel[data-mode=3]').hide();
				$('#strategymode').find('input[data-mode=9]').iCheck('uncheck');
				$('#stragegyaccordion').find('.strategy-panel[data-mode=9]').hide();
			}
		} else if (3 == mode) {
			//差异备份
			if (!event.target.checked) {
				//选中差异同时取消增量和永久增量
				$('#strategymode').find('input[data-mode=2]').iCheck('uncheck');
				$('#stragegyaccordion').find('.strategy-panel[data-mode=2]').hide();
				$('#strategymode').find('input[data-mode=9]').iCheck('uncheck');
				$('#stragegyaccordion').find('.strategy-panel[data-mode=9]').hide();
				//选中差异同时选中完备
				$('#strategymode').find('input[data-mode=1]').iCheck('check');
				$('#stragegyaccordion').find('.strategy-panel[data-mode=1]').show();
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
			}
		}
        setTimeout(initWormConfig, 0);
	}
    //限速模式改变
    var speedModeHandler = function(){
        if(this.value == 2){
            $('.setSpeedStrategy').hide();
        }else{
            $('.setSpeedStrategy').show();
        }
    }
    //添加限速策略
	var speedSubmit = function(){
        var info = {};
        var des = '';
        info.mode = $('#speedModeType').val();
        var speedUnit = getSpeedUnit();
        var speedNum = parseInt($('#speedSpinnerNumInput').val());
        if(!speedNum || speedNum<= 0){
        	UIToastr.showWarning(LANG.UI_BACKUP_SPEED_LIMIT_TIPS);
        	return false;
        }
        var unit = $('#unit').find('option:selected').text();
        var liId = getUuid();
        info.uuid = liId;
        info.value = speedNum* speedUnit;
        info.speednum = speedNum;
        info.unit = unit;
        if(info.mode == 1){
            var strategyConfig = $('#speedstrategy').getSpeedStrategyConfig();
            info.type = strategyConfig.speedInfo.type;
            info.startTime = strategyConfig.speedInfo.startTime;
            info.endTime = strategyConfig.speedInfo.endTime;
            info.days = strategyConfig.speedInfo.days;
            info.des = strategyConfig.speedInfo.des + ', ' + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_VALUE + ':' + speedNum + unit;
            des += 
			'<li class="list-group-item popovers speedTips list-group-item__speed" id="speed'+ liId +'" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="'+ info.des + '">' + 
				'<div class="col1">' + 
					'<div class="cont">' + 
						'<div class="cont-col1"></div>' + 
						'<div class="cont-col2">' + 
							'<div class="desc list-one" style="overflow:hidden;text-overflow: ellipsis;white-space: nowrap">' + info.des + '</div>' + 
						'</div>' + 
					'</div>' + 
				'</div>' + 
				'<div class="col2 pull-right delete-list">' + 
					'<a class="del'+ liId +'" >' + 
    					'<div class="label label-sm label-danger" style="padding:0;">' + 
							'<i class="viconfont vicon-cuowu"></i>' + 
						'</div>' + 
					'</a>' + 
				'</div>' + 
			'</li>';
        }else{
            if(!checkSimpleForever(info.mode)){
                UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_VALUE, LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_ADD_FOREVER_TIPS);
                return false;
            } 
            info.type = 4;
            info.startTime = '';
            info.endTime = '';
            info.days = [];
            info.des = LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_FOREVER+', '+LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_VALUE+':' + speedNum + unit;
            des += 
			'<li class="list-group-item popovers speedTips input-sm" id="speed'+ liId+'"  data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="' +info.des + '">' + 
				'<div class="col1">' + 
					'<div class="cont">' + 
						'<div class="cont-col1"></div>' + 
						'<div class="cont-col2">' + 
							'<div class="desc list-one"> '+ info.des +  '</div>' + 
						'</div>' + 
					'</div>' + 
				'</div>' + 
				'<div class="col2  pull-right delete-list">' + 
					'<a class="del'+ liId +'" >' + 
            			'<div class="label label-sm label-danger" style="padding:0;">' + 
							'<i class="viconfont vicon-cuowu"></i>' + 
						'</div>' + 
					'</a>' + 
				'</div>' + 
			'</li>';
        }
		//检测结束时间是否大于开始时间
		if(!checkTime(info.startTime,info.endTime)) return;
        $('#speedList').append(des);
        $('.speedTips').popover();	   //初始化tips
        $('.del'+ liId).on('click', function(){
            $('.popover.in').remove();
            $('#speed' + liId).remove();
            for(var i=0;i<speedList.length; i++){
                if(liId == speedList[i].uuid){
                    speedList.splice($.inArray(speedList[i],speedList),1);
                }
            }
            initSpeedStrategyDes();
        });
        speedList.push(info);
        $('#speedlimitModal').modal('hide');
        initSpeedStrategyDes();
    }
    //初始化传输代理下拉框
    var initApplianceSelect = function(){
        $('#transferAgentTree').transferAgent();
	}
    //获取UUID
    var getUuid = function() {
        var len = 36;//36长度
        var radix = 16;//16进制
        var chars = '0123456789abcdefghijklmnopqrstuvwxyz'.split('');
        var uuid = [], i;
        radix = radix || chars.length;
        if(len) {
          for(i = 0; i < len; i++)uuid[i] = chars[0 | Math.random() * radix];
        } else {
          var r;
          uuid[8] = uuid[13] = uuid[18] = uuid[23] = '-';
          uuid[14] = '4';
          for(i = 0; i < 36; i++) {
            if(!uuid[i]) {
              r = 0 | Math.random() * 16;
              uuid[i] = chars[(i == 19) ? (r & 0x3) | 0x8 : r];
            }
          }
        }
        return uuid.join('');
    }
    //获取速度单位换算大小
    var getSpeedUnit = function(){
        var type = parseInt($('#unit').val());
        var unit;
        switch(type){
            case 1:
                unit = 1024;
                break;
            case 2:
                unit = 1024 * 1024;
                break;
            case 3:
                unit = 1024 * 1024 * 1024;
                break;
        }
        
        return unit;
	}

    var checkSimpleForever = function(mode){
        for(var i=0;i<speedList.length;i++){
            if(mode == speedList[i].mode){
                return false;
            }
        }

        return true;
	}
    //切换自动选择存储加密密码
	var passwordModeChange = function(){
        if (EDIT_BACKUP_FLAG) { // 修改备份
            if ($('#passwordAutocheck').bootstrapSwitch('state')){
                $('#password').val('');
                $('#repassword').val('');
                $('.passwordDiv').hide();
            } else {
                if (firstInitPageFlag) {
                    PASSWORD_HASCHANGED_FLAG = false;
                } else {
                    $('#password').attr('placeholder', LANG.UI_PUBLIC_ENTER_PASSWORD);
                    $('#repassword').attr('placeholder', LANG.UI_PUBLIC_ENTER_REPASSWORD);
                    PASSWORD_HASCHANGED_FLAG = true;
                }

                $('.passwordDiv').show();
            }
            firstInitPageFlag = false;
        } else { // 创建备份
            if ($('#passwordAutocheck').bootstrapSwitch('state')){
                $('#password').val('');
                $('#repassword').val('');
                $('.passwordDiv').hide();
            } else {
                $('.passwordDiv').show();
            }
        }
	}
	//存储加密切换
	var encryptChange = function(){
		if(this.checked){
			$('#passwordAutocheck').bootstrapSwitch('state', true);  
			$('#password').empty();
			$('#repassword').empty();
			$('.passwordModeDiv').show();
            $('.storage-encrypt-div').show();
		}else{
			$('.passwordModeDiv').hide();
			$('.passwordDiv').hide();
            $('.storage-encrypt-div').hide();
		}
	}
    //得到开关的结果描述   开启/关闭
	var getSwitchDes = function(check){
		if(check){
			return LANG.UI_PUBLIC_ON;
		}
		return LANG.UI_PUBLIC_OFF;
	}
    //得到描述信息----
    //得到时间策略
	var getTimeStr =function () {
		var strategyConfig = $('#backupTimestrategy').getStrategyConfig();
		data.backup_info.full_info = {};
		data.backup_info.incr_info = {};
		data.backup_info.diff_info = {};
        data.backup_info.pincr_info = {};
        data.backup_info.type = $('#backuptype').val();
		var strategyMode = $('#strategymode').find('input:checked');
        //获取选择的存储类型
        var selectedStorageType = data.high_info.node.storage_type;;
		if("strategy" == data.backup_info.type){
			if(0 == strategyMode.length){
				//没有选择时间策略
                //备份到磁带不支持永久增量 先暂时做成云存储支持永久增量 后续如果云存储不支持永久增量则屏蔽
                if(selectedStorageType == CONF.BD_STORAGE_TYPE.TAPE){
                    UIToastr.showInfo(LANG.UI_GLOBAL_STRATEGY_TIME, LANG.UI_FILE_BACKUP_SET_STRATEGY_TIPS);
                }else{
                    UIToastr.showInfo(LANG.UI_GLOBAL_STRATEGY_TIME, LANG.UI_BACKUP_SET_STRATEGY_TIPS);
                }
				return false;
			}else{
				for(var i=0; i<strategyMode.length; i++){
					if(1 == $(strategyMode[i]).data('mode')){
                        if (strategyConfig.fullInfo.rollFlag && !checkTime(strategyConfig.fullInfo.startTime,strategyConfig.fullInfo.endTime)) {
                            return;
                        }
                        data.backup_info.full_info.mode = strategyConfig.fullInfo.mode;
                        data.backup_info.full_info.type = strategyConfig.fullInfo.type
                        data.backup_info.full_info.start_time = strategyConfig.fullInfo.startTime
                        data.backup_info.full_info.roll_flag = strategyConfig.fullInfo.rollFlag
                        data.backup_info.full_info.roll_interval = strategyConfig.fullInfo.rollInterval
                        data.backup_info.full_info.roll_end_time = strategyConfig.fullInfo.endTime
                        data.backup_info.full_info.days = strategyConfig.fullInfo.days
                        data.backup_info.full_info.frequency = strategyConfig.fullInfo.frequency
                        data.backup_info.full_info.full_backup_compensation_flag = strategyConfig.fullInfo.full_backup_compensation_flag
                        data.backup_info.full_info.des = strategyConfig.fullInfo.des
					}else if(2 == $(strategyMode[i]).data('mode')){
            
                        if (strategyConfig.incrInfo.rollFlag && !checkTime(strategyConfig.incrInfo.startTime,strategyConfig.incrInfo.endTime)) {
                            return;
                        }
                        data.backup_info.incr_info.mode = strategyConfig.incrInfo.mode;
                        data.backup_info.incr_info.type = strategyConfig.incrInfo.type
                        data.backup_info.incr_info.start_time = strategyConfig.incrInfo.startTime
                        data.backup_info.incr_info.roll_flag = strategyConfig.incrInfo.rollFlag
                        data.backup_info.incr_info.roll_interval = strategyConfig.incrInfo.rollInterval
                        data.backup_info.incr_info.roll_end_time = strategyConfig.incrInfo.endTime
                        data.backup_info.incr_info.days = strategyConfig.incrInfo.days
                        data.backup_info.incr_info.des = strategyConfig.incrInfo.des
					}else if(3 == $(strategyMode[i]).data('mode')){
                        if (strategyConfig.diffInfo.rollFlag && !checkTime(strategyConfig.diffInfo.startTime,strategyConfig.diffInfo.endTime)) {
                            return;
                        }
                        data.backup_info.diff_info.mode = strategyConfig.diffInfo.mode;
                        data.backup_info.diff_info.type = strategyConfig.diffInfo.type
                        data.backup_info.diff_info.start_time = strategyConfig.diffInfo.startTime
                        data.backup_info.diff_info.roll_flag = strategyConfig.diffInfo.rollFlag
                        data.backup_info.diff_info.roll_interval = strategyConfig.diffInfo.rollInterval
                        data.backup_info.diff_info.roll_end_time = strategyConfig.diffInfo.endTime
                        data.backup_info.diff_info.days = strategyConfig.diffInfo.days
                        data.backup_info.diff_info.des = strategyConfig.diffInfo.des
					}else if(9 ==  $(strategyMode[i]).data('mode')){
                        if (strategyConfig.pIncrInfo.rollFlag && !checkTime(strategyConfig.pIncrInfo.startTime,strategyConfig.pIncrInfo.endTime)) {
                            return;
                        }
                        data.backup_info.pincr_info.mode = strategyConfig.pIncrInfo.mode;
                        data.backup_info.pincr_info.type = strategyConfig.pIncrInfo.type
                        data.backup_info.pincr_info.start_time = strategyConfig.pIncrInfo.startTime
                        data.backup_info.pincr_info.roll_flag = strategyConfig.pIncrInfo.rollFlag
                        data.backup_info.pincr_info.roll_interval = strategyConfig.pIncrInfo.rollInterval
                        data.backup_info.pincr_info.roll_end_time = strategyConfig.pIncrInfo.endTime
                        data.backup_info.pincr_info.days = strategyConfig.pIncrInfo.days
                        data.backup_info.pincr_info.des = strategyConfig.pIncrInfo.des
                    }
				}
				// showStep3(strategyMode);
				return true;
			}
		}else if("oncetime" == data.backup_info.type){
			var onceTime = $('#oncetime').val();
			if("" != onceTime){
				$('.settimetip').hide();
				data.backup_info.datetime = onceTime;
				// showStep3(strategyMode);
				return true;
			}else{
				UIToastr.showInfo(LANG.UI_GLOBAL_STRATEGY_TIME,LANG.UI_BACKUP_SET_TIME_TIPS);
				return false;
			}
		}else if ('manual' === data.backup_info.type){
            return true;
        }
		return false;
	}
    //策略检查时间
    var checkTime = function (start,end) {
		var startnum = new Date("1970-01-01" + " " + start).getTime();
		var endnum = new Date("1970-01-01" + " " + end).getTime();
		if(endnum <= startnum && $("#speedModeType").val() == 1) {//按策略限速才判断
			UIToastr.showWarning(LANG.UI_FILE_CHECK_TIME_RANGE, LANG.UI_FILE_CHECK_TIME_RANGE_TIPS);
            return false;
		}else {
			return true;
		}
	}
    //得到限速策略
	var getSpeedStr = function(){
		data.speedInfo = speedList;
		return true;
	}
    //得到存储策略
	var getStoreStr = function(){
        var compressFlag = $('#compressCheck').get(0).checked; // 是否开启压缩存储
        var encryptStorageFlag = $('#encryptStorageCheck').get(0).checked; // 是否开启数据加密
        var passwordAutoFlag = $('#passwordAutocheck').get(0).checked; // 是否自动生成密码
        var password = $.trim($('#password').val()); // 密码
        var confirmPassword = $.trim($('#repassword').val()); // 确认密码

		// 非法字符串校验
		if (!isNotLatinCode(password)) {
			return false;
		}

        //密码和确认密码校验
		if(!passwordAutoFlag && encryptStorageFlag) { //开启数据加密传输 不开启自动生成密码
			if(!EDIT_BACKUP_FLAG){
                if (!password || !confirmPassword) {
                    UIToastr.showWarning(LANG.UI_BACKUP_DATA_ENCRYPT, LANG.UI_BACKUP_DATA_ENCRYPT_NO_PASSWORD_TIPS);
                    return false;
                }

                if (password !== confirmPassword) {
                    UIToastr.showWarning(LANG.UI_BACKUP_DATA_ENCRYPT, LANG.UI_BACKUP_DATA_ENCRYPT_PASSWORD_CONFIRM_TIPS);
                    return false;
                }
            }else{ //修改备份
                // 是否触发过密码输入框的change事件且密码最终值还是空,则提示 请输入数据加密的密码;没有触发过则无需校验,直接向后端提交此前接口返回的密码
                if (PASSWORD_HASCHANGED_FLAG && !password) {
                    UIToastr.showWarning(LANG.UI_BACKUP_DATA_ENCRYPT, LANG.UI_BACKUP_DATA_ENCRYPT_NO_PASSWORD_TIPS);
                    return false;
                }

                // 触发过密码输入框的change事件,但和确认密码不一致时
                if (PASSWORD_HASCHANGED_FLAG && password !== confirmPassword) {
                    UIToastr.showWarning(LANG.UI_BACKUP_DATA_ENCRYPT, LANG.UI_BACKUP_DATA_ENCRYPT_PASSWORD_CONFIRM_TIPS);
                    return false;
                }
            }
		}
		data.high_info.store.valid = true;
        data.high_info.store.compress = compressFlag; // 是否开启压缩存储
        data.high_info.store.compress_method = $('#compressGrade').val() || 0; // 压缩等级
        data.high_info.store.dataencrypt = encryptStorageFlag; // 是否开启数据加密
		data.high_info.store.password_auto_flag = $('#passwordAutocheck').get(0).checked;//自动生成密码开关
		
		//赋值密码 
        if(!EDIT_BACKUP_FLAG){ //创建备份
            if(appliedGlobalStrategy.flag){ //应用了全局策略  
                if (appliedGlobalStrategy.strategy.store.storeInfo && appliedGlobalStrategy.strategy.store.storeInfo.dataencrypt && !appliedGlobalStrategy.strategy.store.storeInfo.password_auto_flag) { // 开启了存储策略且包含自定义密码
                    if (PASSWORD_HASCHANGED_FLAG) { // 修改过密码
                        if (password === appliedGlobalStrategy.strategy.store.storeInfo.password) { // 修改过但修改后的密码恰好和原先自定义base64编码的一致，则不需要转码直接提交
                            data.high_info.store.password = password;
                        } else {
                            data.high_info.store.password = btoa(password);
                        }
                    } else { // 未修改过密码则直接提交已经是base64编码过的密码
                        data.high_info.store.password = password;
                    }
                } else { // 未开启存储策略
                    data.high_info.store.password = btoa(password);
                }
            }else{
                data.high_info.store.password = btoa($.trim($('#password').val()));
            }

        }else{ //修改备份
            //应用了全局策略
            if(appliedGlobalStrategy.flag){
                if (appliedGlobalStrategy.strategy.store.storeInfo && appliedGlobalStrategy.strategy.store.storeInfo.dataencrypt && !appliedGlobalStrategy.strategy.store.storeInfo.password_auto_flag) { // 开启了存储策略且包含自定义密码
                    if (PASSWORD_HASCHANGED_FLAG) { // 修改过密码
                        if (password === appliedGlobalStrategy.strategy.store.storeInfo.password) { // 修改过但修改后的密码恰好和原先自定义base64编码的一致，则不需要转码直接提交
                            data.high_info.store.password = password;
                        } else {
                            if ($.trim($('#password').val()) === taskInfoSetting.bss.password) { // 若修改后的密码不跟全局策略中的密码一致，但是，却恰好跟原先备份任务创建时设置的密码一致时，则直接提交原密码
                                data.high_info.store.password = password;
                            } else {
                                data.high_info.store.password  = btoa(password);
                            }
                        }
                    }else{ // 未修改过密码则直接提交已经是base64编码过的密码
                        data.high_info.store.password = password;
                    }
                } else { //虽应用全局策略，但全局策略中未开启存储策略 或 未开启手动加密
                    if (!PASSWORD_HASCHANGED_FLAG) {
                        data.high_info.store.password = password;
                    }else{
                        if ($.trim($('#password').val()) === taskInfoSetting.bss.password) {
                            data.high_info.store.password = password;
                        } else {
                            data.high_info.store.password = btoa(password);
                        }
                    }
                }
            }else{ //未应用全局策略
                if (!PASSWORD_HASCHANGED_FLAG) {
                    data.high_info.store.password = taskInfoSetting.bss.password; // 未修改过密码直接提交原先返回的
                 } else {
                     // 若密码输入框触发过change事件,判断现密码和原配置的密码_oldPassword是否相等(存在用户输的密码与返回的编码过的密码一样的场景),相等则不需要base64编码;不相等则需要编码
                     if ($.trim($('#password').val()) === taskInfoSetting.bss.password) {
                         data.high_info.store.password = password;
                     } else {
                         data.high_info.store.password = btoa(password);
                     }
                 }
            }
        }
		return true;
	}
    //判断字符传知否在Latin1字符集中
	function isNotLatinCode(string) {
		var latin1Regex = /[^\x00-\xFF]/;
		if(latin1Regex.test(string)){
			UIToastr.showWarning(LANG.UI_DB_BACKUP_PASSWORD_ILLEAGLE, LANG.UI_DB_BACKUP_PASSWORD_TIPS);
			return false;
		}
		return true;
	}
	
	
    //得到保留策略
	var getReserveStr = function(){
        data.high_info.reserve.strategyMode = $('#reserveMode').val();
		data.high_info.reserve.type = $('#reserveType').val();
		if(CONF.RESERVE_TYPE.NUM == data.high_info.reserve.type){
			data.high_info.reserve.value = $('#spinnerNumInput').val();
		}else if(CONF.RESERVE_TYPE.DAY == data.high_info.reserve.type){
			data.high_info.reserve.value = $('#spinnerDayInput').val();
		}else if(CONF.RESERVE_TYPE.PERMANENT == data.high_info.reserve.type){
			data.high_info.reserve.value = '';
		}
        // 校验保留个数 | 保留天数 是否为0
        if($('#reserveType').val() !== "3") { //不是永久保留
			if(data.high_info.reserve.value > 0){
				return true;
			}else{
				UIToastr.showInfo(LANG.UI_STRATEGY_RESERVE, LANG.UI_BACKUP_RESERVE_TIPS);
				return false;
			}
		}else {
			return true;
		}

	}

    //得到传输策略
	var getTransferStr = function(){
		data.high_info.transfer.encrypt = $('#transport_encrypt_flag').get(0).checked; //得到传输加密
        // 传输加密算法
		data.high_info.transfer.encrypt_method = parseInt($('#transferEncryptMethod').val());
		data.high_info.transport_priority = $('#transport_mode').val(); //得到传输加密
		data.high_info.transfer.network = $('#transferNetwork').val();
		return true;
	}
    //得到安全策略
    var getSafeStr  = function(){
        let wormConfig = $('#wormConfig').getWormProtectionSettings();
		// let virusConfig = $('#virusConfig').getVirusDetectionBackup();
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
	//得到高级配置
    var getHighConf =  function(){
         //重试策略
        data.retry_strategy = $('#retry_config').retryStrategy({} ,'value');
		if (!data.retry_strategy) {
			return false;
		}
        return true;
    }
    var submit = function(){
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
        data.taskName = $.trim($("#jobname").val());
        data.strategy_group_uuid = $('#strategySelect option:selected').val();
        data.speedLimit = getSpeedStrategyInfo();
        //如果存储类型是磁带,传输线程默认传1, 保留值默认为按个数保留，保留值为30 方便修改的时候改成其他存储类型，再第二步提交不是默认值
        var selectedStorageType = data.high_info.node.storage_type;
        if (selectedStorageType == CONF.BD_STORAGE_TYPE.TAPE) { // 选的存储类型为磁带时
            data.high_info.newstr.backup_thread_num = 1;
            data.high_info.reserve.type = 1;
            data.high_info.reserve.value = 30;
        }
        if(!CONF.FUNCTIONS.includes('multithread')){
			 data.high_info.newstr.backup_thread_num = 1;
		}
        
        //修改
        if(EDIT_BACKUP_FLAG){
            if (pageIndex === 0) {
               if (!step1Valid((valid) => {
                    if (valid) {
                        requestForEditBackup();
                    }
                })) {
                    return false;
                }
            } else if (pageIndex === 1) {
                var step2 = step2Valid();
			    if(!step2) return false;
            } else if (pageIndex === 2) {
                var step3 = step3Valid();
                if(!step3) return false;
            }
           requestForEditBackup();

            
        }else{
            requestForCreateBackup();
        }

        
       
    }

    /**
     * 修改备份请求
     */
    const requestForEditBackup = () => {
        //修改之前将通配符进行加密传给后端
        encrywildcard(data.high_info.newstr.wildcard_list);
        Metronic.blockUI({target: '#hadoopbackupcontent',animate: true,cenrerY: true,});
        //获取组织数据
        pAjaxRequest(data, "/api/v1/hadoop/jobs/backup", "PUT", function (result) {
            Metronic.unblockUI('#hadoopbackupcontent');
            if (result.success) {
                UIToastr.showSuccess(LANG.UI_MICROSOFT365_EDIT_BACKUP_JOB,result.message);
                LOCATION('./content/platform/jobs/jobs.php', 'task');
            }
            else{
                UIToastr.showWarning(LANG.UI_MICROSOFT365_EDIT_BACKUP_JOB,result.message);
            }
        }, false);
    }

    
    /**
     * 创建备份请求
     */
    const requestForCreateBackup = () => {
       //创建之前将通配符进行加密传给后端
        encrywildcard(data.high_info.newstr.wildcard_list);
        Metronic.blockUI({target: '#hadoopbackupcontent',animate: true,cenrerY: true,});
        //获取组织数据
        pAjaxRequest(data, "/api/v1/hadoop/jobs/backup", "POST", function (result) {
            Metronic.unblockUI('#hadoopbackupcontent');
            if (result.success) {
                UIToastr.showSuccess(LANG.UI_MICROSOFT365_CREATE_BACKUP_JOB,result.message);
                LOCATION('./content/platform/jobs/jobs.php', 'task');
            }
            else{
                UIToastr.showWarning(LANG.UI_MICROSOFT365_CREATE_BACKUP_JOB,result.message);
            }
        }, false);
    }

    //将通配符加密
    var encrywildcard = function(wildcardList){
        var newwildcardList =  wildcardList;
        var encrypt = new JSEncrypt();
        encrypt.setPublicKey(CONF.PUBLIC_KEY);
        for (let i = 0; i < newwildcardList.length; i++) {
            var wildlist = newwildcardList[i][1];
            var encrywildlist = wildlist.map((wildstr) => {
                return encrypt.encrypt(wildstr);
            })
            newwildcardList [i][1] = encrywildlist;
        }
        return newwildcardList;
    }
    // 获取设置的所有策略配置信息
	var speedSubmitInfo = function (){
		let info = {};
		info['level'] = $('#tasklevelselect').val();
		info['type'] = $('#speedtypeselect').val();
		if (info['type'] == 1) {
			// 选择策略
			var selectedRow = $('.strategy-table #table').bootstrapTable('getSelections');
			if (selectedRow.length == 1) {
				info['uuid'] = selectedRow[0].uuid
				info['name'] = selectedRow[0].name
				info['strategy_type'] = selectedRow[0].type
			}
		} else {
			// 自定义
			info['speed'] = $('#speedstrategy').getSpeedStrategyConfigFinal();
		}
		return info;
	}
    // 初始化路由参数
    var initRouteParams = function(){
        EDIT_BACKUP_FLAG = $('#edit_flag').val();
        if(EDIT_BACKUP_FLAG){
            taskId  = $('#task_uuid').val(); 
            initTaskInfoSettings(); // 获取任务信息
            //修改名称
            $("#title").text (LANG.UI_HADOOP_EDIT_BACKUP_TASK) 
        }else{
            initData();   //初始化创建备份任务的数据结构
            initTree();    //初始化备份源对象树
            // initNodeSelect(); //初始化目标节点选择
            initBackupTarget();
            initTimeStrategy();       //初始化时间策略
            initSecurityStrategy();  //初始化安全策略
            initRetryStrategy();     //初始化重试策略
        }
    }
    //初始化修改任务数据
    var  initDefaultEditData =  function(){
         // step1 备份源
        //  data.src_info.file_info = taskInfoSetting.fileInfo.map(i => { return {...i} });
        data.src_info.file_info = [];
        for(var i = 0; i<taskInfoSetting.fileinfo.length; i++){
			data.src_info.file_info.push([taskInfoSetting.fileinfo[i].filetype,
                taskInfoSetting.fileinfo[i].path,
                taskInfoSetting.fileinfo[i].name],
                taskInfoSetting.fileinfo[i].codetype);
		}
         data.src_info.cluster_uuid_list = [];

        //step2 备份目的地
        data.high_info.node =  taskInfoSetting.node;
        data.high_info.node.storagecheck = !taskInfoSetting.node.storageuuid;
        data.high_info.node.nodecheck = false;

        //step3 备份策略
        data.backup_info.type = taskInfoSetting.timestrategy.type; // 备份方式
        data.appliancecheck = false;
		data.applianceuuid = taskInfoSetting.applianceuuid;
        data.appliance_pool_uuid = taskInfoSetting.appliance_pool_uuid.agent_pool_uuid;
		if(taskInfoSetting.applianceuuid){
			data.appliancecheck = true;
		}
        //完备/增备/差备
		data.backup_info.full_info = {};
		data.backup_info.incr_info = {};
		data.backup_info.diff_info = {};
        data.backup_info.pincr_info = {};

        // 备份策略 - 时间策略
        if (taskInfoSetting.timestrategy.type === 'oncetime') {
            data.backup_info.datetime = taskInfoSetting.timestrategy.data;
        } else {
            var timeStrategys = taskInfoSetting.timestrategy.data;

            for (var i = 0; i < timeStrategys.length; i++) {
                var info = {}, days = [];
                for (var j = 0; j < timeStrategys[i].days.length; j++) {
                    if (timeStrategys[i].days.length === 1 && !timeStrategys[i].days[j]) {
                        days = [];
                    } else if (timeStrategys[i].days[j]) {
                        timeStrategys[i].days[j] = 1;
                        days.push(timeStrategys[i].days[j]);
                    } else if (!timeStrategys[i].days[j]) {
                        timeStrategys[i].days[j] = 0;
                        days.push(timeStrategys[i].days[j]);
                    }
                }

                info.days = days;
                info.mode = timeStrategys[i].mode;
                info.type = timeStrategys[i].strategy_type;
                info.start_time = timeStrategys[i].start_time;
                info.roll_flag = timeStrategys[i].roll_flag;
                info.roll_interval = timeStrategys[i].roll_interval;
                info.roll_end_time = timeStrategys[i].roll_end_time;
                info.frequency = timeStrategys[i].frequency;
                info.full_backup_compensation_flag = timeStrategys[i].full_backup_compensation_flag;
                if (timeStrategys[i].mode === 1) {
                    data.backup_info.full_info = info;
                } else if (timeStrategys[i].mode === 2) {
                    data.backup_info.incr_info = info;
                } else if (timeStrategys[i].mode === 3) {
                    data.backup_info.diff_info = info;
                } else if(timeStrategys[i].mode === 9){
                    data.backup_info.pincr_info = info;
                }
            }
        }

        // 备份策略 - 限速策略
        speedList = taskInfoSetting.speedInfo;
        data.speedInfo = taskInfoSetting.speedInfo;
        data.speedInfo.speed = taskInfoSetting.speedInfo.speedInfo;
        if (taskInfoSetting.speedInfo.type === 1) { // 全局限速
            data.speedInfo.uuid = taskInfoSetting.speedInfo.uuid;
        }

        //备份策略 - 保留策略
        data.high_info.reserve = {};
        data.high_info.reserve.strategyMode = taskInfoSetting.brs.strategy_mode;
		data.high_info.reserve.type = taskInfoSetting.brs.type;
		data.high_info.reserve.value = taskInfoSetting.brs.number;

        //备份策略 - 传输策略
        data.high_info.transfer = {};
        data.high_info.transfer.encrypt = taskInfoSetting.bts.encrypt;
        data.high_info.transfer.mode = taskInfoSetting.bts.mode;
        data.high_info.transfer.network = taskInfoSetting.bts.network;
        // data.high_info.transfer.reconnect_times = taskInfoSetting.bts.reconnect_times;
        // data.high_info.transfer.reconnect_interval = taskInfoSetting.bts.reconnect_interval;
        data.high_info.transfer.encrypt_method = taskInfoSetting.bts.encrypt_method;
    
        //备份策略 - 存储策略
        data.high_info.store = {};
        data.high_info.store.compress = taskInfoSetting.bss.compress;//压缩
        data.high_info.store.dataencrypt = taskInfoSetting.bss.encrypt; //数据加密
        data.high_info.store.password_auto_flag = taskInfoSetting.bss.password_auto_flag;//自动生成密码
        data.high_info.store.password = taskInfoSetting.bss.password;
        data.high_info.store.compress_method = taskInfoSetting.bss.compress_method;
		data.high_info.store.encrypt_method = taskInfoSetting.bss.encrypt_method;
        
        firstInitPageFlag = true;

        // 备份策略 - 高级策略
        data.high_info.newstr = {};
        data.high_info.newstr.backup_thread_num = taskInfoSetting.high.thread_num;//线程数量
        data.high_info.newstr.scan_thread_num = taskInfoSetting.high.scan_thread_num;//扫描线程
        data.high_info.newstr.scan_file_num = taskInfoSetting.high.scan_file_num;//扫描文件速度
        data.high_info.newstr.silentsnapshotcheck =  taskInfoSetting.high.snap_shot_flag;//快照
        data.high_info.permission_operate_flag = taskInfoSetting.high.permission_operate_flag;//文件权限备份
        data.high_info.skip_file_alarm_flag = taskInfoSetting.high.skip_file_alarm_flag;//跳过文件告警
        data.high_info.skip_file_alarm_min_num = taskInfoSetting.high.skip_file_alarm_min_num;
        data.high_info.skip_file_alarm_min_ratio = taskInfoSetting.high.skip_file_alarm_min_ratio;

        //备份策略 - 安全策略
        data.safe_strategy = taskInfoSetting.safeStrategy;
        data.safe_strategy.worm_flag = taskInfoSetting.safeStrategy.worm_flag? 1 : 0;;
        data.safe_strategy.virus_scan_flag = taskInfoSetting.safeStrategy.virus_scan_flag ? 1 : 0;
        // data.safe_strategy.virus_scan_flag = taskInfoSetting.safeStrategy.virus_scan_flag;
        data.safe_strategy.integrity_check_flag = taskInfoSetting.safeStrategy.integrity_check_flag ? 1 : 0;;
        
        //备份策略 - 重试策略
        data.retry_strategy =  taskInfoSetting.retry_strategy;

        //备份策略 - 过载保护
        data.high_info.ignore_resource_limiting_flag =  taskInfoSetting.high.ignore_resource_limiting_flag

        //任务信息
        data.taskName = taskInfoSetting.taskname;
        data.taskuuid = taskInfoSetting.taskuuid;

        // ('#encryptStorageCheck').bootstrapSwitch('state', false);  //默认关闭数据加密
		// 文件备份不支持功能
		$('.deduplicationDiv').hide();
		$('.GFSdiv').hide();
        // 任务名
        $('#jobname').val(taskInfoSetting.taskname);
    }
    //-----------------------------修改任务开始------------------------------------
    //初始化存储
    var initCheckdBackupTarget = function(){
        $('#backupTarget').backupTarget({
			node_uuid: taskInfoSetting.node.nodeuuid,
			node_pool_uuid: taskInfoSetting.node.node_pool_uuid,
			storage_uuid: taskInfoSetting.node.storageuuid,
			storage_pool_uuid: taskInfoSetting.node.storage_pool_uuid,
			storage_pool_type: taskInfoSetting.node.storage_pool_type,
		});
    }
    //时间策略初始化
    var initCheckedTimeStrategy =  function(){
        // 获取任务分布区间
        pAjaxRequest({}, "/api/v1/jobs/time_crow_list", "GET", (result) => {
            if (result.success) {
                let data = result.data;

                if (data.time_list.length > 0) {
                    $('#backupCrowd').taskCrowd({timeList: data.time_list, showFlag: data.show_flag});
                }
            }
        })

        // 获取timeStrategy信息
        let timeStrategy = taskInfoSetting.timestrategy;
        let des = '';
        $('#backuptype').val(timeStrategy.type); // 设置备份方式
        let timeStrategyConfigurations = [
            {
                mode: 1,
                strategy_type: 2,
                days: [0, 0, 0, 0, 1, 0, 0],
                frequency: '',
                start_time: '23:00:00',
                roll_flag: false,
                roll_interval: '01:00:00',
                roll_end_time: '23:59:59'
            },
            {
                mode: 2,
                strategy_type: 1,
                days: [],
                frequency: '',
                start_time: '23:00:00',
                roll_flag: false,
                roll_interval: '01:00:00',
                roll_end_time: '23:59:59'
            },
            {
                mode: 3,
                strategy_type: 1,
                days: [],
                frequency: '',
                start_time: '23:00:00',
                roll_flag: false,
                roll_interval: '01:00:00',
                roll_end_time: '23:59:59'
            },
            {
                mode: 9,
                strategy_type: 1,
                days: [],
                frequency: '',
                start_time: '23:00:00',
                roll_flag: false,
                roll_interval: '01:00:00',
                roll_end_time: '23:59:59'
            }
        ]; // 配置的时间策略
        let displayArr = ['display-none', 'display-none', 'display-none', 'display-none'];
        if (timeStrategy.type === 'strategy') {
            if (timeStrategy.data && timeStrategy.data.length > 0) {
                timeStrategy.data.forEach(item => {
                    if (!item.roll_flag) {
                        item.roll_interval = '01:00:00';
                    }
                    if (item.mode === 1) { // 完全备份
                        timeStrategyConfigurations[0] = { ...item };
                        displayArr[0] = '';
                        $('#fullBackup').iCheck('check');
                    } else if (item.mode === 2) { // 增量备份
                        timeStrategyConfigurations[1] = { ...item };
                        displayArr[1] = '';
                    } else if (item.mode === 3) { // 差异备份
                        timeStrategyConfigurations[2] = { ...item };
                        displayArr[2] = '';
                    } else if(item.mode === 9){ //永久增量
                        timeStrategyConfigurations[3] = { ...item };
                        displayArr[3] = '';
                    }
                });
            }

            // 初始化时间策略插件
            $('#backupTimestrategy').strategy({ dom: $('#backupTimestrategy'), config: timeStrategyConfigurations, display: displayArr, backup_flag: 1 });

            // 获取时间策略配置
            let strategyConfig = $('#backupTimestrategy').getStrategyConfig();
            // 策略 accordion 的显隐
            timeStrategy.data.forEach(item => {
                $('#stragegyaccordion').find('.strategy-panel[data-mode=' + item.mode + ']').show();
                if (item.mode === 1) {
                    des += strategyConfig.fullInfo.des + ". ";
                    $('#fullBackup').iCheck('check');
                } else if (item.mode === 2) {
                    des += strategyConfig.incrInfo.des + ". ";
                    $('#incrBackup').iCheck('check');
                    $('#fullBackup').iCheck('check');
                } else if (item.mode === 3) {
                    des += strategyConfig.diffInfo.des + ". ";
                    $('#diffBackup').iCheck('check');
                    $('#fullBackup').iCheck('check');
                } else if(item.mode == 9){
                    des += strategyConfig.pIncrInfo.des + ". ";
                    $('#pincrBackup').iCheck('check');
                }
            });
        } else if(timeStrategy.type === 'oncetime'){
            des += LANG.UI_GLOBAL_STRATEGY_ONCE_TIME_START + ": " + $('#oncetime').val();
            $('.setStrategy').hide();
            $('.setOnceTime').show();
            $('#oncetime').val(timeStrategy.data);
            $('#spinnerNum').spinner('disable');
            $('#spinnerDay').spinner('disable');
            // 初始化时间策略插件
            $('#backupTimestrategy').strategy({ dom: $('#backupTimestrategy'), config: timeStrategyConfigurations, display: displayArr });
        }else if(timeStrategy.type === 'manual'){
            $('.setStrategy').hide();
            $('.setOnceTime').hide();
            des = ""
            // 初始化时间策略插件
            $('#backupTimestrategy').strategy({ dom: $('#backupTimestrategy'), config: timeStrategyConfigurations, display: displayArr });
        }
        // 策略 accordion 的描述信息
        $('.backupTimeDes').html(des);
        $('.backupTimeDes').attr('title', des);
    }

    //修改备份 - 限速策略初始化
    var initCheckedSpeedLimitStrategy = function(){
        let titleDes = '', des = '';
        $('#tasklevelselect').val(taskInfoSetting.speedInfo.level); // 策略方式
        $('#speedtypeselect').val(taskInfoSetting.speedInfo.type); // 任务级别

        if (taskInfoSetting.speedInfo.type === 1) {
            $('#show_type_1').show();
            $('#show_type_2').hide();
            $('#task_type_global_speed_strategy').show();

            let globalSpeedLimit = taskInfoSetting.speedInfo.uuid;

            $(".strategy-table #table").bootstrapTable('checkBy', {
                field: 'uuid',
                values: [globalSpeedLimit]
            });

            // 初始化全局限速策略表格
            let rowData = $(".strategy-table #table").bootstrapTable("getData");

            // TODO:处理表格数据
            let datas = [];
            for(let j in rowData) {
                if (rowData[j].uuid == globalSpeedLimit) {
                    datas = rowData[j].detail;
                    datas = datas.split('</br>');
                }
            }

            if(datas.length > 0){
                des += LANG.UI_GLOBAL_STRATEGY_SPEED_NUM + ": " + datas.length;
            }

            for(let i in datas){
                titleDes += datas[i] + '. ' + "<br>";
            }
        } else {
            $('#show_type_2').show();
            $('#show_type_1').hide();
            $('#task_type_global_speed_strategy').hide();

            if (taskInfoSetting.speedInfo.speedInfo.length > 0) {
                des += LANG.UI_GLOBAL_STRATEGY_SPEED_NUM + ": " + taskInfoSetting.speedInfo.speedInfo.length;

                taskInfoSetting.speedInfo.speedInfo.forEach(item => {
                    titleDes += `${item.des}. <br>`;
                });

                addGlobalStrategy.init({'strategy_type': taskInfoSetting.speedInfo.strategy_type, speedInfo: taskInfoSetting.speedInfo.speedInfo, initSpeedFlag: 1});
            }
        }

        $('.speedlimitDes').html(des);
        $('.speedlimitDes').prop('title', titleDes);
        
    }

    //修改备份 - 存储策略初始化
    var initCheckedStorageStrategy = function(){
        // TODO:重复数据删除
        // $('#deduplicationCheck').bootstrapSwitch('state', taskInfoSetting.bss.compress);

        // 压缩存储
        $('#compressCheck').bootstrapSwitch('state', taskInfoSetting.bss.compress);

        if (taskInfoSetting.bss.compress) {
            $('.compressGradeDiv').show();
            // TODO:压缩等级
            $('#compressGrade').val(taskInfoSetting.bss.compress_method);
        } else {
            $('.compressGradeDiv').hide();
        }


        // 数据加密
        $('#encryptStorageCheck').bootstrapSwitch('state', taskInfoSetting.bss.encrypt);

        if (taskInfoSetting.bss.encrypt) {
            $('.storage-encrypt-div').show(); // 加密算法div
            $('.passwordModeDiv').show(); // 自动生成密码div
            // TODO:加密算法
            $('#storageEncryptMethod').val(taskInfoSetting.bss.encrypt_method)

            // 自动生成密码
            $('#passwordAutocheck').bootstrapSwitch('state', taskInfoSetting.bss.password_auto_flag);

            if (!taskInfoSetting.bss.password_auto_flag) {
                $('.passwordModeDiv').show();
                $('.passwordDiv').show();
                $('#password').attr('placeholder', LANG.UI_PUBLIC_MODIFY_PASSWORD_TIPS);
                $('#repassword').attr('placeholder', LANG.UI_PUBLIC_MODIFY_PASSWORD_TIPS);
            } else {
                // 自动生成密码默认为checked，首次进入页面不会触发change事件，因此手动触发一次从而改变firstInitPageFlag状态
                passwordModeChange();
            }
        } else {
            $('.storage-encrypt-div').hide(); // 加密算法div
            $('.passwordModeDiv').hide(); // 自动生成密码div
        }
    }

    //修改备份 - 保留策略初始化
    var initCheckedReserveStrategy =function(){
        $('.reserveModeDiv').show();
        // 备份数据保留类型
        $('#reserveMode').val(taskInfoSetting.brs.strategy_mode);
        // 数据保留方式
        $('#reserveType').val(taskInfoSetting.brs.type);
        if (taskInfoSetting.brs.type === 1) {
            $('.reserveNum').show();
            $('.reserveDay').hide();
            // 保留个数
            $('#spinnerNumInput').val(taskInfoSetting.brs.number);
        } else {
            $('.reserveNum').hide();
            $('.reserveDay').show();
            // 保留天数
            $('#spinnerDayInput').val(taskInfoSetting.brs.number);
        }

        // 获取保留策略描述信息
        initReserveStrategyDes();
    }

    //修改备份 - 高级策略初始化
    var initCheckedAdvancedStrategy = function(){
        // 传输线程
        $('#backupThreadNum').val(taskInfoSetting.high.thread_num);

        // 扫描线程
        $('#scanThreadNum').val(taskInfoSetting.high.scan_thread_num);

        if (parseInt(taskInfoSetting.high.scan_thread_num) === 1) { // 为1（极慢）时显示文件扫描速度
            $(".scanFileDiv").show();
            // 扫描文件速度
            $('#scanFileNum').val(taskInfoSetting.high.scan_file_num);
        } else {
            $(".scanFileDiv").hide();
        }
        // 初始化高级策略描述信息
        // initHighStrategyDes();
    }

    //修改备份 - 传输策略初始化
    var initCheckedTransmitStrategy = function(){
        // 传输网络
        $('#transferNetwork').val(taskInfoSetting.bts.network);
        //传输代理
        $('#appliancecheck').bootstrapSwitch('state', data.appliancecheck);

        if (data.appliancecheck) {
            $('.applianceselectdiv').show();
        } else {
            $('.applianceselectdiv').hide();
        }
        // 传输代理资源池
        $('#transferAgentTree').transferAgent({
            agent_uuid: taskInfoSetting.applianceuuid,
            agent_pool_uuid: taskInfoSetting.appliance_pool_uuid.agent_pool_uuid,
        })
        // 加密传输
        $('#transport_encrypt_flag').bootstrapSwitch('state', taskInfoSetting.bts.encrypt);
        if (taskInfoSetting.bts.encrypt) {
            $('.transfer-encrypt-method-form').show();

            $('#transferEncryptMethod').val(taskInfoSetting.bts.encrypt_method);
        } else {
            $('.transfer-encrypt-method-form').hide();
        }
    }

    //修改备份 - 高级配置
    var initCheckedAdvancedConfig = function(){
        // 对象权限备份
        $('#file-permission').bootstrapSwitch('state', taskInfoSetting.high.permission_operate_flag);

        // 跳过文件告警智能判断
        $('#passfilealarmcheck').bootstrapSwitch('state', taskInfoSetting.high.skip_file_alarm_flag);

        //快照
        $('#silentsnapshotcheck').bootstrapSwitch('state', taskInfoSetting.high.snap_shot_flag);

        if (taskInfoSetting.high.skip_file_alarm_flag) {
            $('.passfilenumDiv').show();
            $('.warnningdiv').show();

            $('#passFileNum').val(taskInfoSetting.high.skip_file_alarm_min_num); // 跳过文件告警个数
            $('#warningpercent').val(taskInfoSetting.high.skip_file_alarm_min_ratio); // 跳过文件告警比例
        } else {
            $('.passfilenumDiv').hide();
            $('.warnningdiv').hide();
        }

        //过载保护
        $('#ignoreResourceLimit').bootstrapSwitch('state',taskInfoSetting.high.ignore_resource_limiting_flag)

    }
    //布尔类型转成int1和2
	var boolToInt = function(thisbool){
		if(thisbool){
			return 1;
		}else{
			return 2;
		}
		
	}

    //修改备份 - 安全策略
    var initCheckedSafeStrategy  = function(){
        let complate_info = [];
        complate_info.push(taskInfoSetting.safeStrategy.integrity_check_config.check_strategy);
        complate_info.push(taskInfoSetting.safeStrategy.integrity_check_config.full_error_policy);
        complate_info.push(taskInfoSetting.safeStrategy.integrity_check_config.inc_error_policy);
        let integrityCheckFlag = taskInfoSetting.safeStrategy.integrity_check_flag;
        // let virusScanFlag = taskInfoSetting.safeStrategy.virus_scan_flag === 1 ? true : false;

        
        
        // 初始化安全策略
		$('#completeConfig').completeDetectionBackup('col-md-3', integrityCheckFlag,CONF.MODULE_TYPE.HADOOP, false, complate_info[0], complate_info[1], complate_info[2]);
        
    }
    //修改备份 - 重试策略
    var initCheckedRetryStrategy = function(){
        $('#retry_config').retryStrategy({'retry_strategy': taskInfoSetting.retry_strategy},'edit');
    }
    //获取备份任务信息
    var  initTaskInfoSettings =  function(){
        pAjaxRequest({taskuuid: taskId}, '/api/v1/hadoop/jobs/backup/info', 'get', (res) => {
            if (res.success) {
                taskInfoSetting = { ...res.data, checkedClusterList: res.data.checkedClusterList };
                initDefaultEditData(); // 初始化修改的数据
                initTree(); // 初始化备份源 hadoop集群树
                // initNodeSelect(); // 初始化目标节点选择
                initCheckdBackupTarget(); //初始化节点
                initCheckedTimeStrategy(); // 初始化修改时选择的时间策略
                initCheckedSpeedLimitStrategy(); // 初始化修改时选择的限速策略
                initCheckedStorageStrategy(); // 初始化修改时选择的存储策略
                initCheckedReserveStrategy(); // 初始化修改时选择的保留策略
                initCheckedAdvancedStrategy(); // 初始化修改时选择的高级策略
                initCheckedTransmitStrategy(); // 初始化修改时选择的传输策略
                initCheckedAdvancedConfig(); // 初始化修改时选择的高级配置
                initCheckedSafeStrategy(); // 初始化修改时选择的安全策略
                initCheckedRetryStrategy(); //初始化修改时选择的重试策略
            }
        });

    }
    var getHadoopCurrentUseLicense = () => {
		let uuids = data.src_info.cluster_uuid_list;
		let currentUse = uuids.length;
        return new Promise((resolve) => {
            let params = {
                module: 'hadoop',
                currentUse,
                showMetronic: true,
                judge: true,
                showAlertMsg: true,
				uuids: uuids,
                task_uuid: EDIT_BACKUP_FLAG ? taskInfoSetting.taskuuid : '',
            }
            getModuleAuthInfo(params).then(result => {
                if (result) {
                    resolve(true);
                }
            });
        });
    };
    return {
        init: function(){
            initRouteParams();
            wizardInit();
            initSpinner();
            initDatatimePicker();
            initListener();
            initStrategySelect(); //初始化策略选择
        }
    }
}();

jQuery(document).ready(function() {   
	hadoop_backup.init();
});