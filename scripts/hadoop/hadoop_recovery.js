var  hadoop_recovery =  function(){
    var data = {pointInfo:{ pointUUID: ''},recoverInfo:{},typeInfo:{high:{trasfer:{}},strategy: {}},job_name:'',storeInfo:{},highInfo:{}};
    var initTreeFlag = false; //切换节点初始化时间点树flag
    var timepointTree,hostTree,pathTree;
    var allpointlist;  //保存一条链的时间点
	let allpointPsw;    //保存一条链的时间点的密码
    var _pageSize = 20;	//文件每次请求条数
    var _sclass = 's';		//文件类型大小
    var oldSetting  = {};
    var oldTreeNode;//用于保存搜索前的树
    var zTreeFile;
    var nodeParamList = []; //保存搜索到的树节点
    var share_path = ''//共享路径,用于文件恢复到nas 
	var req_num = 1000;//恢复搜索，一次请求的搜索结果条数
	var global_thread_uuid = '';//恢复搜索线程uuid
	var current_total_num = 0,current_dir_num = 0,current_file_num = 0;//搜索到的文件、文件夹、总数
	var srcAllNode;//搜索之前的所有节点
	var clearNum = 1;//点击了清除搜索就+1
    var timepointNode;
    var _agentName, _agentIP, _pointTaskName, _pointName, _pathArr = [];
    var _fileList = [];		//可选择文件列表
    var timer = null;
    var ostype = '';//用于判断新建文件夹的操作系统类型
    var filenameFlag = true;
    var className = "dark";
	var applianceFlag = false;
	let networkFlag = false; // 是否显示传输网络
	let selectedBackupVendor = 0; // 选择的备份时间点所属的对象存储vendor
	let recoverTargetVendor = 0; // 恢复目标所属的对象存储vendor（前提是选中的恢复客户端为对象存储）
	let _daterangepicker_starttime = "";
	let _daterangepicker_endtime = "";
	let APPLIANCE_AGENCY_HAS_CONFIGED = false; // 传输代理是否已配置标记
	let isSearch = false; // 是否正在搜索
    //鼠标进入增加添加按钮
	var newCount = 1;
	const STORAGE_DEVICE_TYPE = {
		HADOOP: 1,
        FILE: 2,
        NAS: 3,
        OBS: 4
    }; // 存储设备类型
	var storage_type; //恢复源存储类型
	// 从备份数据跳转恢复页面
	const externalPointUuid = $('#externalPointUuid').val();
	const externalTaskUuid = $('#externalTaskUuid').val();
	const externalItemUuid = $('#externalItemUuid').val();
    //初始化进度条
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
            jQuery('li', $('#hadooprecoverycontent')).removeClass("done");
            var li_list = navigation.find('li');
            for (var i = 0; i < index; i++) {
                jQuery(li_list[i]).addClass("done");
            }

            if (current == 1) {
                $('#hadooprecoverycontent').find('.button-previous').css('visibility', 'hidden');
                $('#hadooprecoverycontent').find('.button-next').addClass('next-btn-margin-left');
            } else {
                $('#hadooprecoverycontent').find('.button-previous').css('visibility', 'visible');
                $('#hadooprecoverycontent').find('.button-next').removeClass('next-btn-margin-left');
            }

            if (current >= total) {
                $('#hadooprecoverycontent').find('.button-next').hide();
                $('#hadooprecoverycontent').find('.button-submit').css('visibility', 'visible');
            } else {
                $('#hadooprecoverycontent').find('.button-next').show();
                $('#hadooprecoverycontent').find('.button-submit').css('visibility', 'hidden');
            }
            Metronic.scrollTo($('.page-title'));
        }

        // default form wizard
        $('#hadooprecoverycontent').bootstrapWizard({
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
                		if(step1Valid() == false){
                			return false;
                		}
                		break;
                	case 2:
                		if(step2Valid() == false){
                			return false;
                		}
                		break;
                	case 3:
                		if(step3Valid() == false){
                			return false;
                		}
                		break;
                }
                handleTitle(tab, navigation, index);
            },
            onPrevious: function (tab, navigation, index) {
                success.hide();
                error.hide();

                handleTitle(tab, navigation, index);
            },
            onTabShow: function (tab, navigation, index) {
                var total = navigation.find('li').length;
                var current = index + 1;
                var $percent = (current / total) * 100;
                $('#hadooprecoverycontent').find('.progress-bar').css({
                    width: $percent + '%'
                });
            }
        });

        $('#hadooprecoverycontent').find('.button-previous').css('visibility', 'hidden');
        $('#hadooprecoverycontent .button-submit').click(submit).css('visibility', 'hidden');
	};

    //初始化监听
    var initListener =  function(){
        //集群选择
        $('#storageselect').on('change', storageselectChange);
        $('#tobackup').on('click',function(){
	    	LOCATION('./content/hadoop/hadoop_backup.php', 'hadoop_backup');
		});
        
        //搜索
		$('#searchfs').on('input propertychange',function(){
			searchFS();
		})
		//开始恢复搜索
        $('span[name="start"]').on("click",startSearch);
        //停止搜索
        $('span[name="stop"]').on("click",stopSearch);
        //点击清除筛选
		$(".clearval").click(function () {
			clearSearchVal();
		});
		//点击全选
		$(".selectall").click(function () {
			selectAllResult();
		});
		//点击取消选择
		$(".cancelselect").click(function () {
			cancelSelect();
		});
        
        //选择恢复到的类型是hadoop集群还是文件客户端
		$('#deviceSelect').on('change',function(){
			if (hostTree) {
				hostTree.checkAllNodes(false);
			}
            if (pathTree) {
                pathTree.checkAllNodes(false);
            }
			switch(parseInt(this.value)){
				case 1:
					$(".devicelabel").html(LANG.UI_HADOOP_SELECT_CLUSTER);
					$('#pathtype option[value=1]').show();
					initHadoopClientTree();
					break;
				case 2:
					$(".devicelabel").html(LANG.UI_VOL_CDP_BACKUP_CLIENT_SELECT);
					$('#pathtype').val('2');
					$('#pathtype option[value=1]').hide();
					initFileClientTree();
					break;
				case 3:
					$(".devicelabel").html(LANG.UI_NAS_DEVICE_SELECT);
					$('#pathtype').val('2');
					$('#pathtype option[value=1]').hide();
					initNasClientTree();
					break;
				case 4:
					$(".devicelabel").html(LANG.UI_HADOOP_SELECT_OBS);
					$('#pathtype').val('2');
					$('#pathtype option[value=1]').hide();
					initObsClientTree();
					break;
			}
			$('#searchHost').val('');
			$('#selectPath').hide();
            $('#pathtreediv').hide();
		});
        //选择恢复路径
		$('#pathtype').on('change',function(){
			var selectPath = $('#selectPath');
			if('1' == this.value){
				//原路径恢复
				UIToastr.showWarning(LANG.UI_FILE_RECOVERY,LANG.UI_FILE_OLD_PATH_RECOVERY_TIPS);
				selectPath.hide();
				$('#pathtreediv').hide();
			}else if('2' == this.value){
				//手动选择路径恢复
				let treenode = hostTree.getCheckedNodes();
                if (treenode.length > 0) {
                    selectPath.show();
                    $('#pathtreediv').show();

                    initHdoopFilePathTree(treenode[0]);
                } else {
                    selectPath.hide();
                    $('#pathtreediv').hide();
                }
				
			}
		});

        $('#recovertype').on('change',function(){
			if('1' == this.value){
			    data.typeInfo.strategy = {};
				$('.setOnceTime').hide();
			}else if("4" == this.value){
				$('.setOnceTime').show();
			}
			getTimeDes();
		});
		//时间策略确定
		$('.each-button').on("click",eachButtonClick);
		
		
		//初始化添加限速策略模态框
		$('#addSpeedlimit').on('click', function(){
			$('#speedlimitModal').modal({'width':'800px', 'height':'380px'});
			if(!initSpeedFlag){
				initSpeedTimeStrategy();
			}
			
		});
		//切换限速模式
        $('#speedModeType').on('change', speedModeHandler);
        
        //添加限速策略确定
        $('#speed_submit').on('click', speedSubmit);
        //初始化限速大小设置值
        $('#speedSpinnerNum').spinner({value:10, step: 5, min: 1, max: 10000000000});
		$('#recoveryThreadDiv').spinner({value:3, step: 1, min: 1, max: 32});
        //恢复目标客户端搜索
		$('#searchHost').on('keydown', debouncenew);
		// 传输策略---加密传输
        $('#encrypt').on('switchChange.bootstrapSwitch', transferEncryptChange);
		//传输策略---传输代理
		$('#appliancecheck').on('switchChange.bootstrapSwitch', applianceChange);
		initApplianceSelect()
        $('#retry_config').retryStrategy();//初始化重试策略
    }

    //集群选择改变
    var storageselectChange = function(){
        initTreeFlag = false;
        $('#recoverFileTree').hide();
		initTree();
		if(zTreeFile){
			zTreeFile.checkAllNodes(false);
		}
    }

    //初始化集群列表
    var initClusterList =  function(){
        var requestClusterList =  function(d){
            var clusterselect = $('#clusterselect');
			clusterselect.empty();
            var  clusterlist =  d.data;
            for(var i = 0; i < clusterlist.length; i++){
                var option = $("<option>").text(clusterlist[i].cluster_name).val(clusterlist[i].cluster_uuid);
                clusterselect.append(option);
            }
            
        }
        pAjaxRequest({}, "/api/v1/hadoop/recovery/cluster/list", "GET", requestClusterList ,true);
    }

	//初始化存储列表
	var initStorageList =  function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.STORAGE,f:'getStorageType',p:{}}, function(d){
			var data = JSON.parse(d);
			var stroageselect = $('#storageselect')
			stroageselect.empty();
			for(var i  = 0;i<data.length;i++){
				var option =  $("<option>").text(data[i].text).val(data[i].storageid);
				stroageselect.append(option);
			}
		});
	}
	
	//初始化时间选择器
	var inintDatatimePicker = function(){
		//初始化日期时间选择控件
		$('#daterangepicker').daterangepicker({
			"autoUpdateInput": false,											//是否自动填充input
			"startDate": moment().subtract(6, 'days').startOf('day'),			//默认开始时间
			"endDate": moment({hour: 23, minute: 59}),	//默认结束时间
			"maxDate": moment({hour: 23, minute: 59}),												//最大可用时间
			"timePicker": true,													//是否显示时间,时分
			"timePicker24Hour": true,											//是否是24小时制
			"alwaysShowCalendars": true,										//是否总是显示日期选择
			"ranges": DateRangePickerLocales.getRangesConfig(CONF.LANGUAGE),	//根据语言定义默认ranges,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
			"locale": DateRangePickerLocales.getLocalConfig(CONF.LANGUAGE),		//根据语言定义默认local,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
		}, function(start, end, label) {
//			console.log('New date range selected: ' + start.format('YYYY-MM-DD HH:mm:ss') + ' to ' + end.format('YYYY-MM-DD HH:mm:ss') + ' predefined range: ' + label);
		});

		//如果不是选择后自动填充input(autoUpdateInput:true),需要监听下面两个方法apply.daterangepicker和cancel.daterangepicker
		$('#daterangepicker').on('apply.daterangepicker', function(ev, picker) {
			//给全局变量赋值,然后设置input
			_daterangepicker_starttime = picker.startDate.format('YYYY-MM-DD HH:mm:ss');
			_daterangepicker_endtime = picker.endDate.format('YYYY-MM-DD HH:mm:ss');
			// _daterangepicker_range = picker.chosenLabel;
			$(this).val(picker.startDate.format('YYYY-MM-DD HH:mm:ss') + ' - ' + picker.endDate.format('YYYY-MM-DD HH:mm:ss'));
			searchTimeRange();
		});

		$('#daterangepicker').on('cancel.daterangepicker', function(ev, picker) {
			//清除全局变量,然后设置input
			_daterangepicker_starttime = "";
			_daterangepicker_endtime = "";
			// _daterangepicker_range = "";
			$(this).val('');
			searchTimeRange();
		});
		//input右侧的图标事件
		$('.daterangepickerdiv i').click(function() {
			$(this).parent().find('input').click();
		});
		//设置时间
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
		$('#resetdate').on('click', function () {
			$('#oncetime').val('');
			getTimeDes();
		});
		$('#oncetime').on('change', getTimeDes);
	}
	// 获取时间策略信息描述
	var getTimeDes = function () {
		var des = '';
		des = $('#recovertype').find("option:selected").text();
		if ($('#recovertype').val() == 4) {
			des += '，' + LANG.UI_JOB_TIMING_RECOVER_TIME + "：" + $('#oncetime').val();
		}
		$('.recoveryTimeDes').html(des);
		$('.recoveryTimeDes').prop('title', des);
	}

	// ========================
	// 异步批量添加函数
	// ========================
	function addNodesAsync(nodes, batchSize = 60) {
		return new Promise((resolve) => {
			if (!nodes || nodes.length === 0) {
				resolve();
				return;
			}

			let index = 0;
			const total = nodes.length;

			function processBatch() {
				const batch = nodes.slice(index, index + batchSize);
				if (batch.length === 0) {
					resolve();
					return;
				}

				// 同步添加当前批次（zTree 内部是同步的，但批次小就不会卡）
				batch.forEach(item => {
					const parentNode = timepointTree.getNodesByParam("id", item.pId, null)[0];
					if (parentNode) {
						timepointTree.addNodes(parentNode, item, true); // silent: true 提升性能
					}
				});

				index += batch.length;
				// 让出主线程，避免阻塞 UI
				setTimeout(processBatch, 0);
			}

			processBatch();
		});
	}
	/**
	 * 搜索指定时间范围内的备份时间点树
	 * @returns 
	 */
	var searchTimeRange = function () {
		if ($('#daterangepicker').val() == '') {
			searchByTree();
			return;
		}

		let params = {
            data_flag: false, // 是否为备份数据页面标记
            recovery_range: true, // 是否搜索时间范围标记
            startTime: _daterangepicker_starttime,
            endTime: _daterangepicker_endtime,
            storage_uuid: $('#storageselect').val() || ''
        }
        Metronic.blockUI({target: '.src-wrap__content', animate: true, cenrerY: true,});
        pAjaxRequest(params, '/api/v1/hadoop/restore/data/search', 'GET', async(result) => {
            if (result.success) {
                if (result.data.length > 0) {
                    $('.fs_tree').show();
			        $("#nosearchtips").hide();

                    let pointNode = result.data;
                    let showNode = [];

                    pointNode.forEach(item => {
						// 在当前树节点中搜索异步请求得到的时间点，如果当前树不存在，加入showNode数组
						let nodeExist = timepointTree.getNodesByParam("id", item.id, null)[0];
						if(nodeExist == null) {
							showNode.push(item);
						}
					});


					const type3Nodes = showNode.filter(item => item.type === 3);
                	const type4Nodes = showNode.filter(item => item.type === 4);
					// 异步分批添加
					if (type3Nodes.length > 0) {
						await addNodesAsync(type3Nodes, 60);
					}
					if (type4Nodes.length > 0) {
						await addNodesAsync(type4Nodes, 60);
					}
					searchByTree();
					
                    // // 把showNode中时间点加到对应父节点下
					// if(showNode.length != 0) {
					// 	showNode.forEach(item => {
					// 		// 先把完备点加进去，再放增量差异等，避免先搜增量差异等搜不到父节点
					// 		if(item.type == 3) {
					// 			let parentNode = timepointTree.getNodesByParam("id", item.pId, null)[0];
					// 			if (parentNode){
					// 				timepointTree.addNodes(parentNode, item, true);
					// 			}
					// 		}
					// 	});
					// 	showNode.forEach(item => {
					// 		if(item.type == 4) {//增量差异等
					// 			let parentNode = timepointTree.getNodesByParam("id", item.pId, null)[0];
					// 			if(parentNode) {
					// 				timepointTree.addNodes(parentNode, item, true);
					// 			}
					// 		}
					// 	});
					// }
                    // searchByTree();
                } else {
                    $('.fs_tree').hide();
			        $('#nosearchtips').show();
                }
            }
            Metronic.unblockUI('.src-wrap__content');
        })

	}
	//搜索时间点树
	var searchByTree = function() {
		let nodes = timepointTree.transformToArray(timepointTree.getNodes());
		//时间范围
		timepointTree.hideNodes(nodes);    //当开始搜索时，先将所有节点隐藏
		let startTime = new Date(_daterangepicker_starttime).getTime(); // 开始时间的时间戳
		let endTime = new Date(_daterangepicker_endtime).getTime(); // 结束时间的时间戳
		// 用于存储匹配时间范围的节点
		let matchNodes = [];
		// 遍历所有节点
		for (let i = 0; i < nodes.length; i++) {
			let node = nodes[i];
			// 检查 mode 属性是否存在（存在就是时间点），并且值在指定的时间范围内
			if (node.mode && new Date(node.timepoint).getTime() >= startTime && new Date(node.timepoint).getTime() <= endTime) {
				matchNodes.push(node);
			}
		}
		//搜索框
		let value = $.trim($('#searchfs').val());
		// 输入验证
		if(!customInputValidate('string',value)){
			return false;
		}
		if (value == '') {//搜索为空时
			nodeParamList = [];
		} else {
			nodeParamList = timepointTree.getNodesByParamFuzzy('name', value);
		}
		//连接搜索的和所勾选的
		let checkNode =timepointTree.getCheckedNodes();

		// 合并搜索结果和勾选节点（用于后续交集/并集）
		let combinedSearchNodes = [...nodeParamList, ...checkNode];
		nodeParamList = nodeParamList.concat(checkNode);
		let nodeParamList1 = timepointTree.transformToArray(nodeParamList);
		for(let n in nodeParamList1){
			findParent(timepointTree,nodeParamList1[n]);
		}
		//都不为空取搜索框和时间范围的交集
		if (value != "" && $('#daterangepicker').val() != '') {
			// 交集：时间范围内 AND (搜索命中 OR 勾选)
			const searchSet = new Set(combinedSearchNodes.map(n => n.tId));
			matchNodes = matchNodes.filter(node => searchSet.has(node.tId))
		} else if (value == "" && $('#daterangepicker').val() == '') {//相当于清空搜索
			$('#nosearchtips').hide();
			$('.fs_tree').show();
			timepointTree.showNodes(nodes); //显示所有节点
			return;
		} else {//有一个为空的取全集
			matchNodes = [...matchNodes, ...combinedSearchNodes];
		}
		const uniqueMatchSet = new Set(matchNodes.map(n => n.tId));
		matchNodes = matchNodes.filter(node => uniqueMatchSet.delete(node.tId) || true); // 巧妙去重
		if(matchNodes.length == 0){
			$('.fs_tree').hide();
			$('#nosearchtips').show();
			return;
		}
		$('.fs_tree').show();
		$("#nosearchtips").hide();
		 // 收集所有需要显示的节点：自身 + 路径 + 子节点
		let displayNodes = [];
		for (let node of matchNodes) {
			displayNodes.push(...node.getPath()); // 包含自己和所有父节点
			if (node.children) {
				displayNodes.push(...node.children);
			}
		}
		// 去重（基于引用，zTree 节点是单例）
		const displaySet = new Set(displayNodes);
		let finalNodes = Array.from(displaySet);
		// 显示并展开
		// timepointTree.showNodes(finalNodes);
		// finalNodes.forEach(item => {
		// 	if (item.children) {
		// 		timepointTree.expandNode(item, true, false, false);
		// 	}
		// });
		showAndExpandNodesAsync(finalNodes);
	}

	// 显示并展开（异步分批处理，无数量限制）
	async function showAndExpandNodesAsync(nodes, batchSize = 30) {
		return new Promise((resolve) => {
			let index = 0;

			function processBatch() {
				const batch = nodes.slice(index, index + batchSize);
				if (batch.length === 0) {
					resolve();
					return;
				}

				// 显示当前批次的节点
				timepointTree.showNodes(batch);

				// 展开当前批次中有子节点的节点
				batch.forEach(item => {
					if (item.children && item.children.length > 0) {
						timepointTree.expandNode(item, true, false, false);
					}
				});

				index += batchSize;

				// 继续处理下一批次
				setTimeout(processBatch, 0);
			}

			processBatch();
		});
	}


	
    //获取时间点树
    var  initTree = function(){
        var data = {
            "storage_uuid":$('#storageselect').val() ==  null ? "": $('#storageselect').val(),
			"dataflag":true
        }
        var requestPointTree =  function(d){
            //初始化树
            if(!d.success) return;
			setTree(d.data);
        }
        pAjaxRequest(data, "/api/v1/hadoop/recovery/cluster/ztree", "GET", requestPointTree ,true);
    }

    //初始化时间点树
    var setTree = function(zNodes){
        if(zNodes.length == 0){
			$("#nopointtips").show();
			$('.fs_tree').hide();
			$("#agent_point_tree").hide();
			return;
		}else{
			$("#nopointtips").hide();
			$('.fs_tree').show();
			$("#agent_point_tree").show();
		}
        var setting = {
            check: {
                enable: true,
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
                beforeClick: nodeSelect,
                beforeExpand: nodeExpand,
                onCheck: timepointOnCheck,
            },
            view: {
                nameIsHTML: true,
				fontCss: getFontCss,
				addHoverDom:addPointTreeHoverDom,
				removeHoverDom:removePointTreeHoverDom
            },
        };
        timepointTree = $.fn.zTree.init($("#agent_point_tree"), setting, zNodes);
		// 从备份数据跳转恢复页面，展开对象下的时间点
		let targetNode = timepointTree.getNodeByParam('id', externalItemUuid + '_' + externalTaskUuid);
		// 第一次初始化，且有目标节点
		if(targetNode && !initTreeFlag){
			// 异步获取时间点
			getRecoveryTimepoint('agent_point_tree',targetNode, true, true, true)
		}
        initTreeFlag = true;

    }

	 // 初始化时渲染树节点样式
	const getFontCss = function (treeId, treeNode) {
		let css = { color: "#333", "font-weight": "normal" };
		if (treeNode.point_status == 1) {
			css = { color: "#F19F00 " };
		}
		if (treeNode.point_status == 3) {
			css = { color: "#F1416C " };
		}
		return css;
	}
	/**
     * 添加时间点树的hover dom
     * @param treeId
     * @param treeNode
     */
	  const addPointTreeHoverDom = (treeId, treeNode) => {
        if (treeNode.type !== 3 && treeNode.type !== 4) {//不是时间点
            return;
        }
        if ($(`#${treeNode.tId}_${treeNode.point_uuid}`).length) {
            return;
        }
        let sObj = $(`#${treeNode.tId}_span`);
        sObj.after(`<span id="${treeNode.tId}_${treeNode.point_uuid}"><i class='viconfont vicon-Frame11'></i></span>`);
        // 注册点击事件
        $(`#${treeNode.tId}_${treeNode.point_uuid}`).on("click", (ev) => {
            ev.stopPropagation();  // 阻止click事件向上冒泡
            $('.page-content').initPointDetailDrawer({timepoint_uuid: treeNode.point_uuid});
        });
    };

    /**
     * 移除时间点树的hover dom
     * @param treeId
     * @param treeNode
     */
    const removePointTreeHoverDom = (treeId, treeNode) => {
        if (treeNode.type !== 3 && treeNode.type !== 4) {//不是时间点
            return;
        }
        $(`#${treeNode.tId}_${treeNode.point_uuid}`).off().remove();
    };
    //选择时间点节点事件绑定
	var nodeSelect = function(treeId, treeNode, clickFlag){
		if(3 != treeNode.type && 4 != treeNode.type){
			timepointTree.expandNode(treeNode, true);
			//异步加载时间点
			nodeExpand(treeId, treeNode);
			return;
		}
		timepointOnCheck(event, treeId, treeNode);
		
	}

    //节点展开异步添加时间点
	var nodeExpand = function(treeId, treeNode){
		if(treeNode.clickshow){
			if(treeNode.children) return true;
			getRecoveryTimepoint(treeId, treeNode, false, false);
		}else{
			return true;
		}
	}
    //得到整条链
	var getAllPoints = function (treeNode,password) {
		let info = {};
		info.allPoints = [];
		info.pointPasswords = [];
		if(treeNode.type == 4) {//增量差异点
			var pNode = treeNode.getParentNode();
			pNode.children.forEach(item=> {
				info.allPoints.push(item.point_uuid);
				info.allPoints.push(pNode.point_uuid);
				info.pointPasswords.push({
					'point_uuid':item.point_uuid,
					'password':password,
				});
				info.pointPasswords.push({
					'point_uuid':pNode.point_uuid,
					'password':password,
				});
			});
			
		}else if(treeNode.type == 3) {//完备点
			if(treeNode.children) {
				treeNode.children.forEach(item=> {
					info.allPoints.push(item.point_uuid);
					info.allPoints.push(treeNode.point_uuid);
					info.pointPasswords.push({
						'point_uuid':item.point_uuid,
						'password':password,
					});
					info.pointPasswords.push({
						'point_uuid':treeNode.point_uuid,
						'password':password,
					});
				});
			}else {
				info.allPoints.push(treeNode.point_uuid);
				info.pointPasswords.push({
					'point_uuid':treeNode.point_uuid,
					'password':password,
			});
			}
		}
		return info;
	}


    //异步获取备份的时间点  refreshFlag是否重新刷新
	var getRecoveryTimepoint = function(treeId, treeNode, refreshFlag, expendFlag, chooseFlag = false){
        var data = {
            taskuuid:treeNode.taskuuid,
            id:treeNode.id,
            // refreshFlag:refreshFlag,
            clusteruuid:treeNode.clusteruuid,
            recoverflag:true,
			storageuuid:$('#storageselect').val()
        }
        var div = ".fs_tree";
		Metronic.blockUI({target: div,animate: true});
        var requestTimeTree =  function(d){
            Metronic.unblockUI(div);
            if(!d.success) {
                UIToastr.showInfo(LANG.UI_HADOOP_GET_TIMEPOINT,d.message);
                return;
            }else{
                //获取到了时间点
                $.fn.zTree.getZTreeObj(treeId).removeChildNodes(treeNode);
                $.fn.zTree.getZTreeObj(treeId).addNodes(treeNode,d.data, true);
                $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
                if(expendFlag == true){
                    $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true, true, true);
                }
				// 从备份数据跳转恢复页面，匹配时间点并勾选
				let targetNode = $.fn.zTree.getZTreeObj(treeId).getNodeByParam('id', externalPointUuid);
				if(targetNode && chooseFlag){
					$.fn.zTree.getZTreeObj(treeId).checkNode(targetNode, true, true, true);
				}
            }
        }
        pAjaxRequest(data, "/api/v1/hadoop/recovery/timepoint/ztree", "GET", requestTimeTree ,true);
    }

    //时间点勾选
    var timepointOnCheck = function(e, treeId, treeNode) {
		if(treeNode.chkDisabled){
			return;
		}
		storage_type = treeNode.storagetype;
        // 单选
		$('#recoverFileTree').hide();
		$('.reco-search-group').hide();
        var allNodes = timepointTree.getCheckedNodes(true);
		for(var i = 0; i < allNodes.length; i++){
			timepointTree.checkNode(allNodes[i], false, false, false);	
		}
		timepointTree.checkNode(treeNode, true, false, false);
        // 切换时间点，清空筛选停止搜索
		clearSearchVal();
        global_thread_uuid = ''
        // 是否手动加密
		if('encrypted_flag' in treeNode && treeNode.encrypted_flag && 'password_auto_flag' in treeNode && !treeNode.password_auto_flag) {
			if($.inArray(treeNode.point_uuid,allpointlist)==-1) {//判断是否和之前点击过的是一条链的
				bootbox.prompt({ 
					title: LANG.UI_VM_INPUT_DB_ENCRY_PWD, 
					inputType: 'password',
					callback: debounce(function (pwdResult) {
						//同步ajax校验密码
						if(pwdResult == null){
							//点击取消初始化右边那棵树
							$("#recoverFileTree").hide();
							return;
						}
						$("#recoverFileTree").show();
						var checkflag = true;
						var params = {};
						params.timepointuuid = treeNode.timepointuuid;
						params.inputpass =  btoa($.trim(pwdResult));
						 var p = JSON.stringify(params);
						$.ajax({ 
							type: "post", 
							url: CONF.AJAXPATH, 
							async: false, 
							data:{m:CONF.M.FILE,f:'checkFSEncryptPass',p:p},
							success: function(d){ 
								var result = JSON.parse(d);
								if(result.flag){
									data.recoverInfo.password = btoa($.trim(pwdResult));
									var pointInfo = getAllPoints(treeNode,data.recoverInfo.password);
									allpointlist = pointInfo['allPoints'];
									allpointPsw = pointInfo['pointPasswords'];
									getRecoverDir(treeNode);
								}else{
									OPREL(d);
									checkflag = false;
								}
							} 
						});
						if(!checkflag) return false;
							$(this).modal('hide');
							return true;
					},300, false)
				});
			}else {
				allpointPsw.forEach(item => {
					if(treeNode.timepointuuid == item.point_uuid) {
						data.recoverInfo.password = item.password;
					}
				});
				getRecoverDir(treeNode);
			}
			
		}else {
			data.recoverInfo.password = '';
            getRecoverDir(treeNode);
		}

    }

    //获取备份的文件目录
    var getRecoverDir = function (treeNode) {
        //密码输入正确
		_pathArr = [];
		//恢复到的UUID
		data.pointInfo.cluster_uuid = treeNode.cluster_uuid;
		//时间点UUID
		data.pointInfo.pointUUID = treeNode.point_uuid;
		// 任务UUID
		data.pointInfo.taskUUID = treeNode.taskuuid;
		data.pointInfo.nodeuuid = treeNode.nodeuuid;
        data.pointInfo.ostype = treeNode.ostype;
		//用于最后一步显示用
		_agentName = treeNode.cluster_name;
		_agentIP = treeNode.cluster_ip;
		_pointTaskName = treeNode.task_name;
		_pointName = treeNode.name;
		_fileList = [];
		//		1.初始展示[timepoint_uuid, 1, 0, N, '', 0, '', '']
		// subFlag php那边是否截取路径字符串  初始不截取  第二次以及以后截取
		var params = {
            pid:treeNode.cluster_uuid,
            timepoint_uuid:treeNode.point_uuid, 
            root_flag:1,
            start:"", 
            number:_pageSize,
            path:'', 
            md5_flag:0, 
            md5_high:'', 
            md5_low:'', 
            sclass:_sclass,
            subFlag:false,
			isSearch:isSearch
        };
		params =  {"info":JSON.stringify(params)};
		var div = "#recoverFileTree";
        var requestFileTree =  function(d){
            Metronic.unblockUI(div);
            $('#step1tips').hide();
			$('#backupfilediv').show();
			$('#filelistdiv').show();
            setBackupFileTree(d,treeNode);
        }
		Metronic.blockUI({target: div,animate: true});
        pAjaxRequest(params, "/api/v1/hadoop/recovery/file/ztree", "GET", requestFileTree ,true);
		timepointTree.expandNode(treeNode, true);
    }
    
    //设置文件树
	var setBackupFileTree = function(d,treeNode){
        //如果失败 
        if(!d.success){
            UIToastr.showInfo(LANG.UI_HADOOP_GET_FOLDER_TREE,d.message);
            return;
        }
        var data  = d.data;

        //第一次请求目录时加一层父节点 可支持全选
        //IP需要去拿父级的cluster_ip
		data.filelist.push({
			"pId": 0,
			"name": treeNode.cluster_name+ '('+ treeNode.cluster_ip +')',
			"title": treeNode.cluster_name + '('+ treeNode.cluster_ip +')',
			"isParent": true,
			"open": true,
			"icon": "./img/hadoop/hadoop.png",
			"type": "0",
			"id":treeNode.cluster_uuid
		});
        var setting = {
			check: {
				enable: true,
				nocheckInherit: false,
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
				beforeExpand: fileNodeExpand,
			},
			view: {
				dblClickExpand: false,
			},
			
		};
		zTreeFile = $.fn.zTree.init($("#recoverFileTree"), setting, data['filelist']);
		$('#recoverFileTree').show();
		$('.reco-search-group').show();
		oldTreeNode = data['filelist'];
		oldSetting = setting;
    }

    //点击文件节点
    var fileNodeClick = function(treeId,treeNode) {
        if(treeNode.more) {//加载更多
			getMoreTree(treeId, treeNode);
			return;
		}
		if(treeNode.isParent) {
			fileNodeExpand(treeId,treeNode);
			zTreeFile.expandNode(treeNode, true);
		}
    }
    
    //处理数据
    var handleData  =  function(startIndex, count,treeNode,checkflag) {
		// 截取需要处理的数据
		for (var i = 0; i < treeNode.slice(startIndex, startIndex + count).length; i++) {
			if(checkflag) {
				zTreeFile.checkNode(treeNode.slice(startIndex, startIndex + count)[i],true,true,false)
			}else {
				zTreeFile.checkNode(treeNode.slice(startIndex, startIndex + count)[i],false,true,false)
			}
		}
		// 如果还有未处理的数据，则继续处理
		if (startIndex + count < treeNode.length) {
			handleData(startIndex + count, count,treeNode,checkflag);
		}else{
			Metronic.unblockUI(".addIt-list");
		}
	}

    //文件节点展开
    var fileNodeExpand = function(treeId, treeNode){
        if(treeNode.children) return true;
		setBakSonTree(treeId,treeNode,false);
    }

    //获取文件夹下备份的文件树
    var setBakSonTree = function(treeId,treeNode,expendFlag) {
        var params = {
            pid:treeNode.path,
            timepoint_uuid:treeNode.pointuuid, 
            root_flag:0,
            start:0,
            number:_pageSize,
            path: treeNode.path,
            md5_flag:treeNode.md5Flag, 
            md5_high:treeNode.md5High, 
            md5_low:treeNode.md5Low, 
            sclass:_sclass,
            subFlag:true,
			isSearch:isSearch
        };
		params =  {"info":JSON.stringify(params)};
        var requestSonFileTree =  function(d){
            Metronic.unblockUI('#recoverFileTree');
            if(!d.success){
                UIToastr.showInfo(LANG.UI_HADOOP_GET_FOLDER_TREE,d.message);
                return;
            }
            $.fn.zTree.getZTreeObj(treeId).removeChildNodes(treeNode);
            $.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, d.data.filelist, true);
            $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
            if(expendFlag == true){
                $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true, true, true);
            }
            if(treeNode.checked && treeNode.children) {
                treeNode.children.forEach(item=>{
                    $.fn.zTree.getZTreeObj(treeId).checkNode(item,true);
                });
            }
        }
		Metronic.blockUI({target: '#recoverFileTree',animate: true});
        pAjaxRequest(params, "/api/v1/hadoop/recovery/file/ztree", "GET", requestSonFileTree ,true);
	}

    //点击右边文件列表加载更多
    var getMoreTree = function(treeId, pNode){
        //判断父节点下的子节点是否全选
        var checkeFlag = pNode.getParentNode().check_Child_State==2 ? true : false;
        var params = {
            pid:pNode.pId,
            timepoint_uuid:pNode.pointuuid, 
            root_flag:0,
            start:pNode.next_index,
            number:_pageSize,
            path: pNode.pId,
            md5_flag:pNode.md5Flag, 
            md5_high:pNode.md5High, 
            md5_low:pNode.md5Low, 
            sclass:_sclass,
            subFlag:true,
			isSearch:isSearch
        };
		params =  {"info":JSON.stringify(params)};
        var requestMoreFileTree =  function(d){
            Metronic.unblockUI('#recoverFileTree');
            if(!d.success){
                UIToastr.showInfo(LANG.UI_HADOOP_GET_FOLDER_TREE,d.message);
                return;
            }
            if(checkeFlag) {
                for(var i=0;i<d.data['filelist'].length;i++) {
                    d.data['filelist'][i].checked = true;
                }
            }
            $.fn.zTree.getZTreeObj(treeId).removeNode(pNode);
            $.fn.zTree.getZTreeObj(treeId).addNodes(pNode.getParentNode(), d.data['filelist'], true);
            $.fn.zTree.getZTreeObj(treeId).expandNode(pNode, true);

        }
        Metronic.blockUI({target: '#recoverFileTree',animate: true});
        pAjaxRequest(params, "/api/v1/hadoop/recovery/file/ztree", "GET", requestMoreFileTree ,true);
    }

    //恢复目标集群搜索
    var debouncenew = function () {
        clearTimeout(timer)
        timer = setTimeout(function () {
          var params = $('#searchHost').val();
          initHostTree(params);
        }, 500)
    }

    //开始搜索
    var startSearch = function () {
		// 动态设置ztree的高度
		$('.recover-source-wrapper__ztree').css('height', 'calc(100% - 60px)');
		var inputname = $('#reco-search-btn').attr("name");
		if(inputname == "start") {
			var searchval = $.trim($("#reco-search-input").val());
			if(searchval == "") return;
			//先创建搜索消息，创建成功发送获取搜索结果的消息
			if(timepointTree.getCheckedNodes(true)[0] == null){
				//如果没有勾选左边任何时间点
				return;
			}
			var search_timepoint = timepointTree.getCheckedNodes(true)[0].timepointuuid;
			var md5_list = [];
			var search_mode = 1;//1全文搜索  2选中部分目录目录搜索
			if(zTreeFile.getCheckedNodes(true).length != 0) {
				var arr = zTreeFile.getCheckedNodes(true)
				arr.forEach(item => {
					//根目录不是全选
					if(item.level==0 && item.check_Child_State != 2) {
						search_mode = 2;
					}
					//已勾选的文件夹
					if(item.check_Child_State == -1 || (item.level != 0 && item.check_Child_State == 2)) {
						md5_list.push({
							"filepath":item.path,
							"md5_high":item.md5High,
							"md5_low":item.md5Low
						});
					}
				});
				//去除全选目录下的子目录
				for(var i = 0; i < md5_list.length; i++) {
					if(md5_list[i+1] != undefined && md5_list[i+1].filepath.indexOf(md5_list[i].filepath) !=-1) {
						md5_list.splice(i+1,1)
						i--;
					}
				}
			}
			//删除filepath,只保留MD5
			md5_list.map(item=>delete item.filepath);
			// 各种显示样式
			$('#reco-search-input').prop("disabled",true);//开始搜索禁用输入框
			$('#reco-search-btn').attr("name","stop").attr("disabled",true);
			$('#reco-search-btn').html(LANG.UI_JOB_STOP).css({"background-color":"red","color": "white"});
			$('.ispinner').css("display","inline-block");
			$("#seachdes").show();
			$("#seachdes .countnum").html(LANG.UI_FILE_SEARCH_INIT_TIPS);
			$("#recoverFileTree").hide();

			zTreeFile.checkAllNodes(false);//取消全部勾选
			srcAllNode = [];
			srcAllNode = zTreeFile.transformToArray(zTreeFile.getNodes());
			srcAllNode.map(item=> {
				zTreeFile.removeNode(item);
			});
			var stopNum = clearNum;//每次点击开始重新赋值，保证这一次能走下去
			// 参数
			var params = {};
			params.search_mode = search_mode;
			params.md5_list = md5_list;
			params.path_name = searchval;
			params.timepoint_uuid = search_timepoint;
			params.req_total_num = 5000;//总共请求多少条数据,0不限制
			params.module_type = 3;//文件模块类型;
            var createSearchJob  = function(d){
                if(clearNum != stopNum) return;//让此次操作不能继续进行
                if(!d.success){
                    //获取搜索结果失败，自动变成未搜索前的状态并报错
                    $('#reco-search-input').prop("disabled",false);
					$('#reco-search-btn').html(LANG.BILLING_SEARCH).css({"background-color":"#47b5a0","color": "white"});
					$('#reco-search-btn').attr("name","start");
                    UIToastr.showInfo(LANG.UI_HADOOP_GET_SEARCH_RESULT,d.message);
                }
                var task_uuid =  d.message.task_uuid;
                //如果在搜索，停止界面超时检测
                // UIIdleTimeout.stop();
                current_total_num = 0;
                current_dir_num = 0;
                current_file_num = 0;
                getSearchInfo(0,task_uuid,stopNum)
                global_thread_uuid = task_uuid;
                $('#reco-search-btn').attr("disabled",false);
				isSearch = true;
            }
            pAjaxRequest(params, "api/v1/hadoop/recovery/create/search", "POST", createSearchJob ,true);
		} 
        else if(inputname == "stop") {
			stopSearch();
		}
		
	}

    // 得到恢复搜索的结果
	var getSearchInfo = function (offset,thread_uuid,stopNum) {
		var params = {info:{}};
		params.search_timepoint = timepointTree.getCheckedNodes(true)[0].timepointuuid;;
		params.info.task_uuid = thread_uuid;
		params.info.number = req_num;
		params.info.offset = parseInt(offset);
        var getRecoSearchInfo =  function(d){
            if(clearNum != stopNum) return;
            if(!d.success){
                //获取搜索结果失败，自动变成未搜索前的状态并报错
				$('.ispinner').css("display","none");
				$('#reco-search-input').prop("disabled",false);
				$('#reco-search-btn').html(LANG.BILLING_SEARCH).css({"background-color":"#47b5a0","color": "white"});
				$('#reco-search-btn').attr("name","start");
                UIToastr.showInfo(LANG.UI_HADOOP_GET_SEARCH_RESULT,d.message);
            }
            var data =  d.data;
            //成功
            current_total_num += parseInt(data.current_total_num);
            current_dir_num += parseInt(data.current_dir_num);
            current_file_num += parseInt(data.current_file_num);
            if(data.search_finish_flag == 2) {//未结束
                $("#recoverFileTree").show();
                $("#seachdes .countnum").html(LANG.UI_FILE_FOUND + current_total_num + LANG.UI_FILE_SEARCH_FILE_NUM + current_file_num + LANG.UI_FILE_SEARCH_FLODER_NUM + current_dir_num +LANG.UI_PUBLIC_STRIP);
                //把搜到的节点加在根节点下面
                zTreeFile.addNodes(null,-1,data.filenode);
                //搜索结果数据过大提示信息
                if(current_total_num > 5000) {
					UIToastr.showWarning(LANG.UI_FILE_RECOVERY_SEARCH,LANG.UI_FILE_RECOVERY_SEARCH_LARGE_DATA_TIPS);
                    stopSearch();
                    return;
                }
                getSearchInfo(data.offset,thread_uuid,stopNum);
            }else {
                $('.ispinner').css("display","none");
                $('#reco-search-input').prop("disabled",false);
                $('#reco-search-btn').html(LANG.BILLING_SEARCH).css({"background-color":"#47b5a0","color": "white"});
                $('#reco-search-btn').attr("name","start");
                // Metronic.unblockUI('#recoverFileTree');
                if(current_total_num == 0) {
                    $("#seachdes .countnum").html(LANG.UI_FILE_SEARCH_NO_DATA);
                }else {
                    $("#recoverFileTree").show();
                }
            }

        }
        pAjaxRequest(params, "api/v1/hadoop/recovery/get/search", "POST", getRecoSearchInfo ,true);
    }

    //清空搜索值
    var clearSearchVal = function() {
		$('.recover-source-wrapper__ztree').css('height', 'calc(100% - 34px)');
		$("#recoverFileTree").show();
		zTreeFile = $.fn.zTree.init($("#recoverFileTree"), oldSetting, oldTreeNode);
		$('#reco-search-input').val("");
		$("#seachdes").hide();
		$('#reco-search-btn').html(LANG.BILLING_SEARCH).css({"background-color":"#47b5a0","color": "white"});
		$('#reco-search-btn').attr("name","start");
		$('#reco-search-input').prop("disabled",false);
		isSearch =  false;
		stopSearch();
	}
    //筛选之后点击全选
    var selectAllResult = function() {
		var allNodes = zTreeFile.transformToArray(zTreeFile.getNodes());
		if(allNodes.length==0) {
			return; 
		}
		Metronic.blockUI({target: ".addIt-list",animate: true});
		handleData(0, getSelectNum(allNodes),allNodes,true);
	}
     //筛选之后取消选择
	var cancelSelect = function () {
		Metronic.blockUI({target: ".addIt-list",animate: true});
		var allNodes = zTreeFile.transformToArray(zTreeFile.getCheckedNodes(true));
		if(allNodes.length==0){
			Metronic.unblockUI(".addIt-list");
			return; 
		}
		handleData(0, getSelectNum(allNodes),allNodes,false);
	}
    //判断一次勾选的条数
	var getSelectNum = function(allNodes) {
		var num = 0;
		if(allNodes.length < 100) {
			num = 1;
		}else if(allNodes.length < 1000) {
			num = 50;
		}else if(allNodes.length < 5000) {
			num = 100;
		}else {
			num = 500;
		}
		return num;
	}
    //停止搜索
    var stopSearch = function() {
        $('.ispinner').css("display","none");
		$('#reco-search-btn').html(LANG.BILLING_SEARCH).css({"background-color":"#47b5a0","color": "white"});
		$('#reco-search-btn').attr("name","start");
		$('#reco-search-input').prop("disabled",false);
		clearNum++;//点击停止，+1,让此次操作不能继续进行
        if(global_thread_uuid == '')return;
		var params = {info:{}};
		params.search_timepoint = timepointTree.getCheckedNodes(true)[0].timepointuuid;;
		params.info.task_uuid = global_thread_uuid;
        var stopSearchJob  = function(d){
            if(!d.success) {
                UIToastr.showInfo(LANG.UI_HADOOP_STOP_SEARCH,d.message);
            }
        }
        pAjaxRequest(params, "api/v1/hadoop/recovery/stop/search", "POST", stopSearchJob ,true);
    }

    //搜索集群
	var searchFS = function(){
		// var value = $('#searchfs').val();
		// var checkNode =timepointTree.getCheckedNodes();
		// var allNode = timepointTree.transformToArray(timepointTree.getNodes());
		// nodeParamList = timepointTree.getNodesByParamFuzzy('name', value);
		// timepointTree.hideNodes(allNode);
		// if(nodeParamList.length == 0){
		// 	$('.fs_tree').hide();
		// 	$('#nosearchtips').show();
		// }else{
		// 	$('.fs_tree').show();
		// 	$("#nosearchtips").hide();
		// }
		// //连接搜索的和所勾选的
		// nodeParamList = nodeParamList.concat(checkNode);
		// var nodeParamList1 = timepointTree.transformToArray(nodeParamList);
		// for(var n in nodeParamList1){
		// 	findParent(timepointTree,nodeParamList1[n]);
		// }
		// timepointTree.showNodes(nodeParamList);
		searchTimeRange();
    }
    //找到父节点
	var findParent = function(treeObj,node){
		timepointTree.expandNode(node,true,false,false);
		if(!node.children){
			nodeParamList.push(node);
			timepointTree.expandNode(node,false,false,false);
		}
		var pNode = node.getParentNode();
		if(pNode != null){
			nodeParamList.push(pNode);
			findParent(timepointTree, pNode);
		}
   }



    var step1Valid = function(){
        //检查时间点

		if(timepointTree.getCheckedNodes(true).length == 0){
            UIToastr.showWarning(LANG.UI_RECOVERY_FILE_NO_TIMEPOINT_TITLE, LANG.UI_RECOVERY_FILE_NO_TIMEPOINT_VALUE);
            return false;
        }
        timepointNode = data.pointInfo.nodeuuid;
        //检查恢复文件列表
        data.pointInfo.fileInfo = zTreeFile.getCheckedNodes(true);
        if(!data.pointInfo.fileInfo.length){
            UIToastr.showWarning(LANG.UI_RECOVERY_FILE_NO_REC_FILE_TITLE, LANG.UI_RECOVERY_FILE_NO_REC_FILE_VALUE);
            return false;
        }
        stopSearch();//点击下一步停止搜索
        showStep1();
        return true;
    }

    //展示第一步信息
    var showStep1 = function(){
		//设置任务名
		if($.trim($('#jobname').val()) == "") {
            //获取恢复任务名
            pAjaxRequest({}, '/api/v1/hadoop/recovery/taskname', "GET", function(d){
                $('#jobname').val(d.data);
            },true)
		}
		//设置时间点信息
		$('.srcagentinfo').html(_agentName + "(" + _agentIP + ")");
		$('.srctaskname').html(_pointTaskName);
		$('.srctimepoint').html(_pointName);
		
		//设置文件列表
		var showStr = '';
		var allCheckedNode = [];
		//得到所有勾选状态是全选中的节点
		for(var i=0; i<data.pointInfo.fileInfo.length; i++){
			//check_Child_State == -1不存在子节点（文件或者全选的无子节点的文件夹）  2是所有子节点被勾选
			if(data.pointInfo.fileInfo[i].type != 0 && data.pointInfo.fileInfo[i].check_Child_State == 2 || data.pointInfo.fileInfo[i].check_Child_State == -1) {
				allCheckedNode.push(data.pointInfo.fileInfo[i]);
			}
		}
		//过滤掉重复的
		for(var m = 0;m<allCheckedNode.length;m++) {
			if(allCheckedNode[m].btype ==2) {//文件夹
				for(var n = 0;n<allCheckedNode.length;n++) {
					var str = allCheckedNode[n].pId==null ?'':allCheckedNode[n].pId;
					if(str.includes(allCheckedNode[m].path) && allCheckedNode.indexOf(allCheckedNode[n])!=-1) {//判断全选的文件夹下是否还有文件  有则从数组中删除
						allCheckedNode.splice(allCheckedNode.indexOf(allCheckedNode[n]),1);
						n--;
					}
				}
			}
		}
		//设置文件显示列表和提交的列表
		data.pointInfo.fileInfo = [];
		allCheckedNode.forEach(item=> {
			data.pointInfo.fileInfo.push([item.btype,item.path,item.name,item.md5High,item.md5Low]);//组装发送给后台的文件信息
			showStr += item.path + '<br>';
		})
		//判断恢复选择的节点是否是搜索出来的
		if(allCheckedNode[0].searchNode) {
			data.distinct_flag = 1;
		}else {
			data.distinct_flag = 2;
		}
		$('#filelist').html(showStr);
		if (storage_type == CONF.BD_STORAGE_TYPE.TAPE || !CONF.FUNCTIONS.includes('multithread')) {
			//磁带只支持单线程，不显示线程配置
			$('#recoveryThreadDiv').spinner("value", 1);
			$('.threadDiv').hide();
			$('#highshowDiv').hide();
        } else {
			$('#recoveryThreadDiv').spinner("value", 3);
			$('.threadDiv').show();
			$('#highshowDiv').show();
		}
		//磁带存储屏蔽安全策略
		if(storage_type == CONF.BD_STORAGE_TYPE.TAPE){
            $('.safetyLi').hide();
			$('.safeDiv').hide();
		}else{
			 $('.safetyLi').show();
			 $('.safeDiv').show();
		}
		if (!CONF.FUNCTIONS.includes('integrity')) {
            $('.safetyLi').hide();
			$('.safeDiv').hide();
        }
		// 恢复到hadoop集群且集群树tree未被初始化时才调接口默认获取hadoop集群树
		if (parseInt($('#deviceSelect').val()) === 1 && $('#host_tree').children().length === 0) {
			initHadoopClientTree();
		}

		return;
	}

    var step2Valid = function(){
		//第四布验证显示
		var agentShowInfo = '', pathShowInfo = '';
		// 表单确认回显
		let deviceClient = parseInt($("#deviceSelect").val()); // 存储设备
		if(!hostTree){
			$('#noagenttips').show();
			return false;
		}
		var nodes = hostTree.getCheckedNodes();
		if(!nodes.length){
			switch(deviceClient) {
				case STORAGE_DEVICE_TYPE.FILE: // 文件
					UIToastr.showWarning(LANG.UI_RECOVERY_FILE_NO_REC_HOST_TITLE, LANG.UI_RECOVERY_FILE_NO_REC_HOST_VALUE);
					return false;
				case STORAGE_DEVICE_TYPE.NAS: //NAS
					UIToastr.showWarning(LANG.UI_RECOVERY_HADOOP_NO_REC_NAS_TITLE, LANG.UI_RECOVERY_HADOOP_NO_REC_NAS_VALUE);
					return false;
				case STORAGE_DEVICE_TYPE.HADOOP: // hadoop
					UIToastr.showWarning(LANG.UI_RECOVERY_HADOOP_NO_REC_HADOOP_TITLE, LANG.UI_RECOVERY_HADOOP_NO_REC_HADOOP_VALUE);
					return false;
				case STORAGE_DEVICE_TYPE.OBS: // OBS
					UIToastr.showWarning(LANG.UI_RECOVERY_HADOOP_NO_REC_OBS_TITLE, LANG.UI_RECOVERY_HADOOP_NO_REC_OBS_VALUE);
					return false;
				default:
					return false
			}
		}
		if(!filenameFlag) {
			return false;
		}
		agentShowInfo = nodes[0].name
		//赋值
		data.recoverInfo.agentUUID = nodes[0].uuid;
		data.recoverInfo.type == 2;//默认2异机恢复
		data.recoverInfo.pathType = $('#pathtype').val();
		if(1 == data.recoverInfo.pathType){
			//原路径恢复
			pathShowInfo = LANG.UI_RECOVERY_FILE_REC_OLD_PATH;
			data.recoverInfo.code_type = 2;
			data.recoverInfo.path = '';
		}else if(2 == data.recoverInfo.pathType){
			//新路径恢复
			var pathnodes = pathTree.getCheckedNodes();
			if(!pathnodes.length){
				UIToastr.showWarning(LANG.UI_RECOVERY_FILE_NO_REC_PATH_TITLE, LANG.UI_RECOVERY_FILE_NO_REC_PATH_VALUE);
				return false;
			}
			if(nodes[0].event_type == "nas") {
				//去掉sharepath最前面的斜杠
				var newpath = share_path.substring(1,share_path.length);
				pathnodes[0].dir_path = pathnodes[0].dir_path.replace(newpath,'');
			}
			//选择nas根目录
			if(pathnodes[0].parentFlag != undefined) {
				pathnodes[0].dir_path = "/"
			}
			data.recoverInfo.path = pathnodes[0].dir_path; 
			data.recoverInfo.new_dir_create = pathnodes[0].new_dir_create;
			data.recoverInfo.code_type = pathnodes[0].code_type;
			// 如果恢复客户端是对象存储则需要处理 |
			if (deviceClient === STORAGE_DEVICE_TYPE.OBS) {
                pathShowInfo = replaceBetweenStartEnd(pathnodes[0].dir_path, '|', '/', '').replace('|', '');
            } else {
                pathShowInfo = pathnodes[0].dir_path;
            }
		}
		switch (deviceClient) {
            case 1: //  恢复到Hadoop
				// 重置传输代理
				$('#appliancecheck').bootstrapSwitch('state', false);
				// 重置传输加密
				$('#encrypt').bootstrapSwitch('state', false);

				$('.appliancediv').show(); // 显示传输代理
				$('.advanced-config-obs-form-group').hide(); // 隐藏对象存储所属form-group项
				$('.applianceselectdiv').hide(); // 隐藏传输代理选择框
				$('.transfer-encrypt-method-form').hide(); // 隐藏传输加密算法选择框
				$('.encryptdiv').hide(); // 隐藏传输加密
				$('.advanced-config-file-form-group').show(); // 跳过目录树恢复、同名文件处理、无效快捷方式处理、文件权限恢复
                // 原路径恢复无跳过目录树恢复
				if (parseInt($('#pathtype').val()) == 1) {
					$('.dir-tree-div').hide();
				} else {
					$('.dir-tree-div').show();
				}
				$('.network-retry-wrap').show();  //显示网络重连
				clearactivetab();
				$('.permissionLi').addClass('active').show();
				$('#tab_permission_conf').addClass('active');
				break;
            case 2: // 恢复到文件
				// 重置传输代理
				$('#appliancecheck').bootstrapSwitch('state', false);
				// 重置传输加密
				$('#encrypt').bootstrapSwitch('state', false);
				//重置文件权限恢复
				$('#permission-recovery').bootstrapSwitch('state', false);

				$('.appliancediv').hide(); // 隐藏传输代理
				$('.advanced-config-obs-form-group').hide();
				$('.applianceselectdiv').hide(); // 隐藏传输代理选择框
				$('.transfer-encrypt-method-form').hide(); // 隐藏传输加密算法选择框
				$('.encryptdiv').show(); // 隐藏传输加密
				$('.advanced-config-file-form-group').show();
				$('.permission-recovery-div').hide();//隐藏文件权限恢复
				$('.network-retry-wrap').show();  //显示网络重连
				clearactivetab();
				$('.permissionLi').hide(); //隐藏权限那一栏
				$('.exceptionLi').addClass('active');
				$('#tab_except_handle_conf').addClass('active');
                break;
            case 3: // 恢复到NAS
				// 重置传输代理
				$('#appliancecheck').bootstrapSwitch('state', false);
				// 重置传输加密
				$('#encrypt').bootstrapSwitch('state', false);
				//重置文件权限恢复
				$('#permission-recovery').bootstrapSwitch('state', false);
				$('.appliancediv').hide(); // 隐藏传输代理
				$('.advanced-config-obs-form-group').hide();
				$('.applianceselectdiv').hide();// 隐藏传输代理选择框
				$('.transfer-encrypt-method-form').hide(); // 隐藏传输加密算法选择框
				$('.encryptdiv').hide(); // 隐藏传输加密

				$('.advanced-config-file-form-group').show();
				$('.permission-recovery-div').hide();//隐藏文件权限恢复
				$('.network-retry-wrap').hide();  //隐藏网络重连
				clearactivetab();
				$('.permissionLi').hide(); //隐藏权限侧边栏
				$('.exceptionLi').addClass('active');
				$('#tab_except_handle_conf').addClass('active');
                break;
            case 4: // 恢复到对象存储
				// 重置传输代理
				$('#appliancecheck').bootstrapSwitch('state', false);
				// 重置传输加密
				$('#encrypt').bootstrapSwitch('state', false);
				//重置文件权限恢复
				$('#permission-recovery').bootstrapSwitch('state', false);
				$('.appliancediv').show(); // 显示传输代理
				$('.advanced-config-file-form-group').hide();
				$('.applianceselectdiv').hide();// 隐藏传输代理选择框
				$('.transfer-encrypt-method-form').hide(); // 隐藏传输加密算法选择框
				$('.encryptdiv').hide(); // 显示加密传输
				$('.advanced-config-obs-form-group').show();
				$('.permission-recovery-div').hide();//隐藏文件权限恢复
				$('.network-retry-wrap').show();  //显示网络重连
				clearactivetab();
				$('.permissionLi').hide(); //隐藏权限侧边栏
				$('.exceptionLi').addClass('active');
				$('#tab_except_handle_conf').addClass('active');
                break;
            default:
                break;
        }

		// 重置恢复方式tabs
		$('#fs_recovery_mode_tabs').find('li.active').removeClass('active');
		$('#fs_recovery_mode_tab_content').find('.tab-pane.fs-tab-content__pane.active').removeClass('active');

		// 显示通用策略tab
		$('#fs_recovery_mode_tabs').find(':first').addClass('active');
		$('#fs_recovery_mode_tab_content').find(':first.fs-tab-content__pane').addClass('active');

		agentShowInfo = nodes[0].name;
		showStep2(agentShowInfo, pathShowInfo);
		return true;
	}
	var clearactivetab = function(){
		$('.permissionLi').removeClass('active'); 
		$('#tab_permission_conf').removeClass('active');
		$('.exceptionLi').removeClass('active'); 
		$('#tab_except_handle_conf').removeClass('active');
		$('.retryLi').removeClass('active'); 
		$('#tab_retry_conf').removeClass('active');
		$('.overLoadLi').removeClass('active'); 
		$('#tab_over_load_conf').removeClass('active');
	}
    var showStep2 = function(agentInfo, pathInfo){
		//恢复目标
		$('.recover2agent').html(agentInfo);
		//恢复路径
		$('.recover2path').html(pathInfo);
		//初始化恢复时间策略
		getTimeDes();
		$('#recoveryThreadNum').prop('disabled', false); //启用线程数选择
		//初始化安全策略
		var integrity_check_flag =  timepointTree.getCheckedNodes(true)[0].integrity_check_flag
		$('#completeConfig').completeStrategyCovery(CONF.MODULE_TYPE.HADOOP, 0 , !integrity_check_flag );
		initResourceLimit([data.pointInfo.nodeuuid]);
	}

	const getSpeedLimitStrategy = () => {
		let info = {
            level: parseInt($('#tasklevelselect').val()),
            type: parseInt($('#speedtypeselect').val()),
            uuid: '',
            name: ''
        }

        if (info.type === 1) { // 选择全局限速策略
            let selectedRows = $('.strategy-table #table').bootstrapTable('getSelections');

            if (selectedRows.length === 1) {
                info.uuid = selectedRows[0].uuid;
                info.name = selectedRows[0].name;
                info.strategyType = selectedRows[0].type;
            }
        } else { // 自定义策略
            info.speed = $('#speedstrategy').getSpeedStrategyConfigFinal();
            
            // 处理参数名字与接口统一
            if (info.speed.length > 0) {
                info.speed = info.speed.map(item => {
                    return {
                        ...item,
                        start_time: item.startTime,
                        end_time: item.endTime
                    }
                })
            }
        }
        return info;
	}

    var step3Valid = function(){
		//时间策略
		data.typeInfo.type = $('#recovertype').val();//恢复方式
		data.typeInfo.strategy.start_time = $('#oncetime').val(); //定时恢复时间
		data.typeInfo.strategy.type = data.typeInfo.type;
		if (data.typeInfo.type == 4 && data.typeInfo.strategy.start_time == '') {//1立即恢复  4定时恢复
			UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_TIME,LANG.UI_BACKUP_SET_TIME_TIPS);
			return false;
		}
		data.typeInfo.high.trasfer.encrypt = $('#encrypt').get(0).checked;
		// 传输加密算法
		data.typeInfo.high.trasfer.encrypt_method = parseInt($('#transferEncryptMethod').val());
		let deviceClient = parseInt($("#deviceSelect").val()); // 存储设备
		//目录树恢复/去除前缀
		if(deviceClient == STORAGE_DEVICE_TYPE.OBS){
			data.highInfo.dir_tree_recovery_flag =  $('#obs_skip_dir_tree_flag').get(0).checked; // 去除前缀
			//同名对象处理
			data.highInfo.same_file_strategy = $('#obs_same_name_handle_type').val();
		}else{
			data.highInfo.dir_tree_recovery_flag = $('#dir-tree').get(0).checked;
			//同名文件处理
			data.highInfo.same_file_strategy = $('#same-name').val();
		}
		

		//无效快捷方式清理
		data.highInfo.link_file_pass_flag = $('#clear-shortcut').get(0).checked;
		//文件权限恢复
		data.highInfo.permission_operate_flag = $('#permission-recovery').get(0).checked;
		data.speedInfo = getSpeedLimitStrategy();// 限速策略
		data.thread_num = $('#recoveryThreadNum').val();
		if(data.thread_num > 32 || data.thread_num < 1 || data.thread_num == ""){
			//重置为默认值
			$('#recoveryThreadDiv').spinner("value", 3);
			UIToastr.showWarning(LANG.UI_BACKUP_HIGH_SETTING_TITLE, LANG.UI_FILE_BACKUP_THREAD_NUM_TIPS);
			return false;
		}
		//限速策略
		var speedlimitshow = $('.speedlimitshow');
		var speedlimitsStr = '';

		speedlimitsStr = $('.speedlimitDes').prop('title');

		if (speedlimitsStr == '') {
			speedlimitsStr = LANG.UI_PUBLIC_NOTHING;
		}
		speedlimitshow.html(speedlimitsStr);
		//传输代理
		data.appliancecheck = $('#appliancecheck').get(0).checked;
		let applianceNode = $('#transferAgentTree').transferAgent('getSelect'); // 获取传输代理
		APPLIANCE_AGENCY_HAS_CONFIGED = $('#transferAgentTree').transferAgent('validateSelect');
		if(!data.appliancecheck ){
			data.appliance_uuid = "";
			data.appliance_pool_uuid = "";
		}else if(data.appliancecheck  && !APPLIANCE_AGENCY_HAS_CONFIGED){
			UIToastr.showWarning(LANG.UI_APPLIANCE_SELECT, LANG.UI_APPLIANCE_SELECT_NO_TIPS);
			return false;
		}else{
			data.appliance_uuid = applianceNode.agent_uuid;
			data.appliance_pool_uuid = applianceNode.agent_pool_uuid;
		}
		//传输网络
		if(deviceClient == STORAGE_DEVICE_TYPE.FILE){
			if (networkFlag) {
				let select_transfer_network = $('#transferNetworkTree').transferNetwork('validateSelect');
				if(!select_transfer_network){
					return false;
				}
			}
		}
		//安全策略
		let completeConfig = $('#completeConfig').getCompleteStrategyCovery();
		if (!CONF.FUNCTIONS.includes('integrity')) {
            completeConfig = '';
        }
		data.safe_strategy = safeData('', '', completeConfig);
		$('.safeStrategyShow').html(completeConfig.str);
		//重试策略
		data.retry_strategy = $('#retry_config').retryStrategy({} ,'value');
		if (!data.retry_strategy) {
			return false;
		}
		//忽略节点资源限制
		data.highInfo.ignore_resource_limiting_flag = !!$('#ignoreResourceLimit').get(0).checked;
		if('1' == data.typeInfo.type){
			//立即恢复
			showStep3();
			return true;
		}
		if($.isEmptyObject(data.typeInfo.strategy)){
			$('.setstrategytip').html(LANG.UI_STRATEGY_PLEASE_SET).show();
			Metronic.scrollTo($('.setstrategytip'));
			return false;
		}
		showStep3();
		return true;
	}
	
	var showStep3 = function(){
		var showStr1 = '', showStr2 = '', transferdes = '';
		// showStr1 = $('#recovertype').find("option:selected").text();
		showStr1 = $('.recoveryTimeDes').text();
		var deviceClient = parseInt($("#deviceSelect").val()); // 存储设备

		// 传输线程
		var threadnumlabel = $('.threadnumlabel').html();
		showStr2 += threadnumlabel + ": " + $('#recoveryThreadNum').val();
		$('.reservetypeshow').html(showStr1);
		$('.transthreadshow').html(showStr2);
		if(CONF.FUNCTIONS.includes('multithread')){
			$('.transthreadshow').show();
		}else{
			$('.transthreadshow').hide();
		}
		// // 重连次数
		// if(parseInt($('#reconnect_time').val()) == 0){
		// 	transferdes += $('.reconnect-times-Label').text() + ": " + LANG.UI_GLOBAL_STRATEGY_RECONNECT_TIMES_INFINITE +"<br>";
		// }else{
		// 	transferdes += $('.reconnect-times-Label').text() + ": " + $('#reconnect_time').val() + LANG.UI_VOL_CDP_BACKUP_TIMES + "<br>";
		// }
		// // 重连时间间隔
		// transferdes += $('.reconnect-Interval-Label').text() + ": " + $('#reconnect_interval').val() + LANG.UI_PUBLIC_SECOND + " <br>";
		//如果开启了传输代理 需要加上加密传输
		if( (data.appliancecheck && (deviceClient === STORAGE_DEVICE_TYPE.OBS || deviceClient === STORAGE_DEVICE_TYPE.HADOOP)) || (deviceClient ==  STORAGE_DEVICE_TYPE.FILE) ){
            transferdes += LANG.UI_COPY_BACK_ENCRYPT + ': ' + getSwitchDes(data.typeInfo.high.trasfer.encrypt) + '<br>';
            if($('#encrypt').get(0).checked){
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
                transferdes += encryptedMethodLabel + ": " + grade + "<br>";
            }
        }
		if(deviceClient == 1){
			$('.permissionrecoveryshow').show();
		}else{
			$('.permissionrecoveryshow').hide();
		}
		if ($('#pathtype').val() == 1) {//原路径恢复
			$('.dirtreeshow').hide();
		} else {
			$('.dirtreeshow').show();
			if(deviceClient ==  4){
				$('.dirtreeshow').html($('.obs-skip-dir-tree-label').html() + ": " + getSwitchDes(data.highInfo.dir_tree_recovery_flag));
			}else{
				$('.dirtreeshow').html($('.dir-tree-label').html() + ": " + getSwitchDes(data.highInfo.dir_tree_recovery_flag));
			}
		}
		// if(deviceClient !=  4){
		// 	$('.clearshortcutshow').html($('.clear-shortcut-label').html() + ": " + getSwitchDes(data.highInfo.link_file_pass_flag));
		// }else{
		// 	$('.clearshortcutshow').html('');
		// }
		if(deviceClient ==  4){
			$('.samenameshow').html($('.obs-same-name-label').html() + ": " + $('#obs_same_name_handle_type option:selected').text());

			$('.permissionrecoveryshow').html($('.obs-permission-recovery-label').html() + ": " + getSwitchDes(data.highInfo.permission_operate_flag));

		}else{
			$('.samenameshow').html($('.same-name-label').html() + ": " + $('#same-name option:selected').text());
			$('.permissionrecoveryshow').html($('.permission-recovery-label').html() + ": " + getSwitchDes(data.highInfo.permission_operate_flag));
		}
		//proxy代理
		if (deviceClient === 1 || deviceClient ===4) {
			 // 恢复到对象存储和hadoop才有传输代理
			 var appliancelabel = $('.appliancelabel').html();
			 var applianceInfo = appliancelabel + ": " + getSwitchDes(data.appliancecheck);
			 let applianceNode = $('#transferAgentTree').transferAgent('getSelect');
			 if(data.appliancecheck){
				 var applianceName = applianceNode.name;
				 applianceInfo += ', ' + applianceName;
			 }
			 transferdes += applianceInfo + "<br>";
		}

		if (deviceClient === 2) { // 恢复到文件客户端且配置传输网络
			// if ($('#transferNetwork').val()) {
			// 	transferdes += LANG.UI_VOL_CDP_RECOVER_TRANSPORT_NET + ': ' + $('#transferNetwork').find("option:selected").text();
			// 	data.typeInfo.high.trasfer.network = $('#transferNetwork').val();
			// }
			if (networkFlag) {
				let networkNode = $('#transferNetworkTree').transferNetwork('getSelect');
				transferdes +=  LANG.UI_VOL_CDP_RECOVER_TRANSPORT_NET + ': ' + networkNode.str;
				data.typeInfo.high.trasfer.network = networkNode.network_uuid;
			}
		}
		$('.transfershow').html(transferdes);
		//恢复到nas隐藏网络重连 
		if(deviceClient == 3){
			$('.network_retry_times_show').hide();
       		$('.network_retry_interval_show').hide();
		}else{
			$('.network_retry_times_show').show();
       		$('.network_retry_interval_show').show();
		}
		// 忽略节点资源限制
		$('.ignoreResourceLimitShow').html($('.ignoreResourceLimitLabel').html() + ": " + getSwitchDes(data.highInfo.ignore_resource_limiting_flag));
	}
    //初始化客户端树
    var initHostTree = function(keyword){
		let deviceClient = parseInt($("#deviceSelect").val()); // 存储设备

		switch (deviceClient) {
            case STORAGE_DEVICE_TYPE.FILE: // 文件客户端
                initFileClientTree(keyword);
                break;
            case STORAGE_DEVICE_TYPE.NAS: // NAS设备
                initNasClientTree(keyword);
                break;
            case STORAGE_DEVICE_TYPE.HADOOP: // Hadoop集群
                initHadoopClientTree(keyword);
                break;
            case STORAGE_DEVICE_TYPE.OBS: // 对象存储
                initObsClientTree(keyword);
                break;
            default:
                break;
        }
		
	}

    var setHostTree = function(zNodes){
		let setting = {
            check: {
                enable: true,
            },
            data: {
                simpleData: {
                    enable: true
                }
            },
            callback: {
                beforeClick: hostNodeSelect,
                onCheck: hostOnCheck,
            }
        };

        hostTree = $.fn.zTree.init($("#host_tree"), setting, zNodes);
    }


	/**
     * 步骤二：恢复目标 - 恢复路径 - 获取 文件客户端 树
     */
    const initFileClientTree = (keyword = '') => {
        let params = {
            keyword: keyword
        }

        Metronic.blockUI({target: '#host_tree',animate: true});
        pAjaxRequest(params, '/api/v1/files/recover_host_tree', 'GET', (result) => {
            if (result.success) {
                if (result.data.length === 0) {
                    $('#noagenttips').show();
                    $('#host_tree').hide();
                    $('#noagenttips p').html(LANG.UI_FILE_RECOVERY_NO_AGENT);
                } else {
                    $('#noagenttips').hide();
                    $('#host_tree').show();

                    // 初始化文件客户端树
                    setHostTree(result.data);
                }
            } else {
                $('#noagenttips').show();
                $('#host_tree').hide();
                $('#noagenttips p').html(LANG.UI_FILE_RECOVERY_NO_AGENT);

                UIToastr.showWarning(LANG.UI_HADOOP_GET_CLIENT_FAILED, result.message);
            }

            Metronic.unblockUI('#host_tree');
        })
    }

	/**
     * 步骤二：恢复目标 - 恢复路径 - 获取 NAS 设备树
     */
    const initNasClientTree = (keyword = '') => {
        let params = {
            keyword: keyword,
            timepoint_uuid: data.pointInfo.pointUUID ,
            host_flag: true // 只显示当前勾选的备份数据的节点挂载过的nas设备，防止用户勾选未挂载的设备导致恢复失败
        }

        Metronic.blockUI({target: '#host_tree',animate: true});
        pAjaxRequest(params, '/api/v1/nas/backup_tree', 'GET', (result) => {
            if (result.success) {
                if (result.data.length === 0) {
                    $('#noagenttips').show();
                    $('#host_tree').hide();
                    $('#noagenttips p').html(LANG.UI_FILE_RECOVERY_NO_DEVICE);
                } else {
                    $('#noagenttips').hide();
                    $('#host_tree').show();
                    // 初始化NAS设备树
                    setHostTree(result.data);
                }
            } else {
                $('#noagenttips').show();
                $('#host_tree').hide();
                $('#noagenttips p').html(LANG.UI_FILE_RECOVERY_NO_DEVICE);

                UIToastr.showWarning(LANG.UI_HADOOP_GET_NAS_FAILED, result.message);
            }

            Metronic.unblockUI('#host_tree');
        })
    }

	/**
     * 步骤二：恢复目标 - 恢复路径 - 获取 Hadoop 树
     */
    const initHadoopClientTree = (keyword = '') => {
        let params = {
            keyword: keyword,
			recoverflag:true
        }

        Metronic.blockUI({target: '#host_tree',animate: true});
        pAjaxRequest(params, '/api/v1/hadoop/backup/cluster/ztree', 'GET', (result) => {
            if (result.success) {
                if (result.data.length === 0) {
                    $('#noagenttips').show();
                    $('#host_tree').hide();
                    $('#noagenttips p').html(LANG.UI_HADOOP_NO_AVAILABLE_CLUSTER);
                } else {
                    $('#noagenttips').hide();
                    $('#host_tree').show();

                    // 初始化Hadoop集群树
                    setHostTree(result.data);
                }
            } else {
                $('#noagenttips').show();
                $('#host_tree').hide();
                $('#noagenttips p').html(LANG.UI_HADOOP_NO_AVAILABLE_CLUSTER);

                UIToastr.showWarning(LANG.UI_HADOOP_GET_CLUSTER_FAILED, result.message);
            }

            Metronic.unblockUI('#host_tree');
        })
    }

	/**
     * 步骤二：恢复目标 - 恢复路径 - 获取 对象存储 树
     */
    const initObsClientTree = (keyword = '') => {
        let params = {
            keyword: keyword
        }

        Metronic.blockUI({target: '#host_tree',animate: true});
        pAjaxRequest(params, "/api/v1/s3/recover_obs_tree", "GET", function (result) {
            if (result.success) {
                if (result.data.length === 0) {
                    $('#noagenttips').show();
                    $('#host_tree').hide();
                    $('#noagenttips p').html(LANG.UI_OBS_RECOVERY_NO_STORAGE);
                } else {
                    $('#noagenttips').hide();
                    $('#host_tree').show();

                    // 初始化对象存储树
                    setHostTree(result.data);
                }
            } else {
                $('#noagenttips').show();
                $('#host_tree').hide();
                $('#noagenttips p').html(LANG.UI_OBS_RECOVERY_NO_STORAGE);

                UIToastr.showWarning(LANG.UI_HADOOP_GET_OBS_FAILED, result.message);
            }

            Metronic.unblockUI('#host_tree');
        });
    }
    //选择代理端节点事件绑定
	var hostNodeSelect = function(treeId, treeNode, clickFlag){
		ostype = treeNode.ostype;

        if(treeNode.type === 1) {
			hostTree.expandNode(treeNode);
			return;
		}

		if(treeNode.chkDisabled) return;

		hostTree.checkNode(treeNode, !treeNode.checked, false, true);
	}

	var hostOnCheck = function(e, treeId, treeNode){
		if (treeNode.chkDisabled) return;

        if (treeNode.checked) {
            hostTree.checkAllNodes(false);
            hostTree.checkNode(treeNode,true,false,false);

            if (parseInt($('#pathtype').val()) === 2) { // 勾选且手动选择恢复路径则显示 选择路径
                $('#selectPath').show();
                $('#pathtreediv').show();
                // 获取选择客户端的高度后动态计算选择路径的fom-group的高度，以适应分辨率变化
                let clientTreeFormGropHeight = $('.form-group-group-client-tree').height() + 15;
                // 162: (form-group高度 34 + margin-bottom高度 15) * 3 + form-recover-path mb15px
                $('.form-recover-path').css('height', `calc(100% - ${clientTreeFormGropHeight + 162}px)`);
    
                switch (treeNode.event_type) {
                    case 'agent': // 文件客户端
                        initAgentPathTree(treeNode);
						data.recoverInfo.cross_platform_transform = 1; // 是否跨平台
                        break;
                    case 'nas': // nas设备
                        initNasFilePathTree(treeNode);
						data.recoverInfo.cross_platform_transform = 1; // 是否跨平台
                        break;
                    case 'obs_agent': // 对象存储
                        initObsFilePathTree(treeNode);
                        data.recoverInfo.cross_platform_transform = 2; // 是否跨平台
                        break;
                    case 'hadoop_cluster': // hadoop
                        initHdoopFilePathTree(treeNode);
                        data.recoverInfo.cross_platform_transform = 1; // 是否跨平台
                        break;
                    default:
                        break;
                }
            }
        } else { // 未勾选不显示 选择路径
            $('#selectPath').hide();
            $('#pathtreediv').hide();
            if (treeNode.event_type === 'hadoop_cluster') {
				$('#pathtype option[value=1]').show();
			} else {
				$('#pathtype option[value=1]').hide();
			}
        }
        // 初始化传输网络
        initNetwork(treeNode);
	}
	
	/**
     * 步骤二：恢复目标 - 获取文件客户端下的目录树
     * @param treeNode
     * @returns {boolean}
     */
		const initAgentPathTree = function(treeNode){
			filenameFlag = true;
			let params = {
				agent_uuid: treeNode.uuid,
				offset: 0,
				limit: _pageSize,
				filename: '',
				dir: '',
				pid: 0,
				startAfter: ''
			}
			Metronic.blockUI({target: '#path_tree',animate: true});
	
			pAjaxRequest(params, '/api/v1/files/recover_path_tree', 'GET', (result) => {
				if (result.success) {
					setAgentPathTree(result.data);
				} else {
					UIToastr.showWarning(LANG.UI_HADOOP_GET_CLIENT_FILE_FAILED, '');
				}
	
				Metronic.unblockUI('#path_tree');
			});
		}
	
		/**
		 * 步骤二：恢复目标 - 获取NAS设备下的目录树
		 * @param treeNode
		 */
		const initNasFilePathTree = (treeNode) => {
			filenameFlag = true;
			share_path = treeNode.path;
			let params = {
				agent_uuid: treeNode.agent_uuid,
				share_path: treeNode.path,
				offset: 0,
				limit: _pageSize,
				filename: '',
				dir: treeNode.path,
				pid: 0,
				startAfter: ''
			}
	
			Metronic.blockUI({target: '#path_tree',animate: true});
	
			pAjaxRequest(params, '/api/v1/nas/recover_path_tree', 'GET', (result) => {
				if (result.success) {
					result.data.push({
						"id": 0,
						"name": treeNode.name,
						"title": "/",
						"dir_path": "/",
						"isParent": true,
						"open": true,
						"icon": "./img/fs/wenjianjia.png",
						"icon_close": "./img/fs/wenjianjia.png",
						"icon_open": "./img/fs/wenjianjiaopen.png",
						"type": "0",
						"path": treeNode.path,
						"new_dir_create": 2,
						"parentFlag": true,
						"noRemoveBtn": true,
						"noEditBtn": true,
						"event_type": "nas"
					});
	
					setAgentPathTree(result.data);
				} else {
					UIToastr.showWarning(LANG.UI_HADOOP_GET_NAS_FILE_FAILED, '');
				}
	
				Metronic.unblockUI('#path_tree');
			})
		}
	
		/**
		 * 步骤二：恢复目标 - 获取对象存储下的目录树
		 * @param treeNode
		 */
		const initObsFilePathTree = (treeNode) => {
			filenameFlag = true;
			let params = {
				agent_uuid: treeNode.id,
				offset: 0,
				limit: _pageSize,
				filename: '',
				dir: '',
				pid: 0,
				code_type: 2,
				startAfter: ''
			}
	
			Metronic.blockUI({target: '#path_tree',animate: true});
	
			pAjaxRequest(params, '/api/v1/s3/recover_path_tree', 'GET', (result) => {
				if (result.success) {
					if (result.data && result.data.length > 0) {
						let treeData = result.data.map(item => {
							return {
								...item,
								name: item.name.split('|')[0],
								title: item.title.split('|')[0],
								dir_path: item.dir_path + '/',
								vendor: treeNode.vendor
							}
						});
	
						setAgentPathTree(treeData);
					}
				} else {
					UIToastr.showWarning(LANG.UI_HADOOP_GET_OBS_FILE_FAILED, result.message);
				}
	
				Metronic.unblockUI('#path_tree');
			})
		}
	
		/**
		 * 步骤二：恢复目标 - 获取hadoop集群下的目录树
		 * @param {*} treeNode 
		 */
		const initHdoopFilePathTree = (treeNode) => {
			filenameFlag = true;
	
			let params = {
				hadoop_cluster_id: treeNode.uuid, 
				limit_count: _pageSize, 
				fetch_root_dir: treeNode.getParentNode() ==  undefined? "/": treeNode.getParentNode().filepath,
				start_after: '', //上一个节点路径
				edit_flag: false
			};
	
			Metronic.blockUI({target: '#path_tree',animate: true});
	
			pAjaxRequest(params, '/api/v1/hadoop/recover_path_tree', 'GET', (result) => {
				if (result.success) {
					let treeData = result.data.file_nodes;
	
					treeData.push({
						"id": '/',
						"name": '(/)',
						"title": "/",
						"dir_path": "/",
						"isParent": true,
						"open": false,
						"icon": "./img/fs/wenjianjia.png",
						"icon_close": "./img/fs/wenjianjia.png",
						"icon_open": "./img/fs/wenjianjiaopen.png",
						"type": "0",
						"path": treeNode.path,
						"parentFlag": true,
						"noRemoveBtn": true,
						"noEditBtn": true,
						"event_type": "hadoop_cluster",
						"clusteruuid": treeNode.uuid
					})
	
					setAgentPathTree(treeData);
				} else {
					UIToastr.showWarning(LANG.UI_HADOOP_GET_FOLDER_TREE, result.message);
				}
	
				Metronic.unblockUI('#path_tree');
			})
		}
	
	//设置第二步恢复路径
    var setAgentPathTree = function(treeData,treeNode){
		newCount = 1;
		var setting = {
				view: {
					addHoverDom: addHoverDom,
					removeHoverDom: removeHoverDom,
					selectedMulti: false
				},
				edit: {
					enable: true,
					drag:false,//禁用拖拽
					editNameSelectAll: true,
					showRenameBtn:showRenameBtn,
            		showRemoveBtn:showRemoveBtn,
					removeTitle: LANG.BILLING_DELETE,
					renameTitle: LANG.UI_FILE_EDIT
					
				},
				check: {
					enable: true,
				},
				data: {
					simpleData: {
						enable: true
					}
				},
				callback: {
					beforeClick: pathNodeSelect,
					onCheck: pathOnCheck,
					beforeExpand: pathNodeExpand,
					beforeEditName: beforeEditName,
					onRemove: onRemove, //移除事件 
					onRename: onRename,
				}
			};
		pathTree = $.fn.zTree.init($("#path_tree"), setting, treeData);
	}
	//初始化网络
	var initNetwork = (treeNode) => {
        let nodes = [];
        nodes.push(treeNode);
        networkFlag = false;
        networkFlag = getNetworkFlag(nodes);
        //初始化传输网络
        if(networkFlag){
            $('.transfernetworkDiv').show();
            initNetworkList();
        }else{
            $('.transfernetworkDiv').hide();
        }
    }
	/**
     * 节点传输网络DIV是否显示标记
     * @param agent
     * @returns {boolean}
     */
	var getNetworkFlag = (agent) => {
		for(let i= 0; i < agent.length; i++){
			//客户端连接服务端
			if (agent[i].net_model == 2){
				networkFlag = true;
			}
		}
		return networkFlag;
	}
	/**
     * 初始化节点传输网络列表
     */
    var initNetworkList = () => {
		
        let requestdata = {
            nodes_uuid: data.pointInfo.nodeuuid
        }
		$('#transferNetworkTree').transferNetwork({node_uuid:  data.pointInfo.nodeuuid});
        // pAjaxRequest(requestdata, '/api/v1/nodes/network_card', 'GET', (result) => {
        //     if (result.success) {
        //         $('#transferNetwork').empty();
        //         let networkList = result.data;

        //         if (networkList && networkList.length > 0) {
        //             networkList.forEach(item => {
        //                 let name = `${item.ip}:${item.port}`;

        //                 if (item.alias_name) {
        //                     name += `(${item.alias_name})`;
        //                 }

        //                 let option = `<option value="${item.network_uuid}">${name}</option>`;
        //                 $('#transferNetwork').append(option);
        //             });
        //         }
        //     }
        // })
    }
    //得到手动路径的参数UUID
	var getAgentPathTreeAgentUUID = function(){
		var agentuuid = false;
		if(!hostTree){
			$('#noagenttips').show();
			return false;
		}
		var nodes = hostTree.getCheckedNodes();
		if(!nodes.length){
			return false;
		}
		if($("#deviceSelect").val() == 1) {
			agentuuid = nodes[0].uuid || nodes[0].clusteruuid;
			data.pointInfo.clusteruuid = nodes[0].uuid;
		}else {
			agentuuid = nodes[0].uuid;
		}
		return agentuuid;
	}

    //改完名字之后
	function onRename(event, treeId, treeNode, isCancel) {
		//选中新增的节点
		var allNodes = pathTree.getCheckedNodes(true);
		for(var i = 0; i < allNodes.length; i++){
			pathTree.checkNode(allNodes[i], false, false, false);
		}
		pathTree.checkNode(treeNode, true, false, false);
		//给新增的节点设置title
		var pnode = treeNode.getParentNode();
		//去掉sharepath最前面的斜杠
		let newpath = share_path.substring(1, share_path.length);
		let parentPath = pnode.dir_path.replace(newpath, '');
		treeNode.title = treeNode.name;
		treeNode.dir_path = parentPath + treeNode.name + '/';
		var str = $.trim(treeNode.name);
		if(str.length==0) {
			UIToastr.showInfo(LANG.UI_NAS_RECOVERY_FILENAME_NO_NULL);
			filenameFlag = false;
			pathTree.editName(treeNode);
			return;
		}else {
			filenameFlag = true;
		}
		//文件夹名称不能包含特殊符号,不包含  /  :  "  <  >  | \
		var specialchar = ['/', ':','"','<','>','|','\\','?','*'];
		for (var key in specialchar) {
			if (str.indexOf(specialchar[key]) != -1) {
				UIToastr.showInfo(LANG.UI_FILE_FILENAME_NO_CONTAIN);
				filenameFlag = false;
				pathTree.editName(treeNode);
				return;
			}else {
				filenameFlag = true;
			}
		}
	}
	// 删除节点
	function  onRemove(event, treeId, treeNode){
		//获取删除节点的父节点
		var parentNode = treeNode.getParentNode();
		//如果父节点下的子节点不存在
		if(parentNode.children.length == 0) {
			//修改父节点图标为文件夹样式
			parentNode.isParent = true;
			// parentNode.open = true;
			pathTree.updateNode(parentNode);
		}

	}
	//是否显示编辑按钮
	function  showRenameBtn(treeId, treeNode){
	    //获取节点所配置的noEditBtn属性值
	    if(treeNode.noEditBtn != undefined && treeNode.noEditBtn){
	        return false;
	    }else{
	        return true;
	    }
	}
	//是否显示删除按钮
	function showRemoveBtn(treeId, treeNode){
	    //获取节点所配置的noRemoveBtn属性值
	    if(treeNode.noRemoveBtn != undefined && treeNode.noRemoveBtn){
	        return false;
	    }else{
	        return true;
	    }
	}

    //选择路径
	var pathNodeSelect = function(treeId, treeNode, clickFlag){
		if(treeNode.more){
			let params = {}

            let api = '';
            switch (treeNode.event_type) {
                case 'agent':
                    params = {
                        agent_uuid: treeNode.uuid,
                        offset: treeNode.next_index,
                        limit: _pageSize,
                        filename: treeNode.search_file_name,
                        dir: treeNode.dir_path,
                        pid: treeNode.pid
                    }
                    api = '/api/v1/files/recover_path_tree';
                    break;
                case 'nas':
                    params = {
                        agent_uuid: treeNode.uuid,
                        offset: treeNode.next_index,
                        limit: _pageSize,
                        filename: treeNode.search_file_name,
                        dir: treeNode.dir_path,
                        pid: treeNode.pid
                    }
                    api = '/api/v1/nas/recover_path_tree';
                    break;
                case 'obs_agent':
                    params = {
                        agent_uuid: treeNode.uuid,
                        offset: treeNode.next_index,
                        limit: _pageSize,
                        filename: treeNode.search_file_name,
                        dir: treeNode.dir_path,
                        pid: treeNode.pid,
						start_after: treeNode.search_file_name
                    }
                    api = '/api/v1/s3/recover_path_tree';
                    break;
                case 'hadoop_cluster':
                    params = {
                        hadoop_cluster_id: treeNode.uuid,
                        start: treeNode.next_index,
                        limit_count: _pageSize,
                        fetch_root_dir: treeNode.dir_path,
                        start_after: treeNode.next_start
                    }
                    api = '/api/v1/hadoop/recover_path_tree';
                    break;
                default:
                    break;
            }

            Metronic.blockUI({target: '#path_tree',animate: true});
            pAjaxRequest(params, api, 'GET', (result) => {
                if (result.success) {
                    pathTree.removeNode(treeNode);
					if(treeNode.event_type == "hadoop_cluster"){
						pathTree.addNodes(treeNode.getParentNode(), result.data.file_nodes, true);
					}else{
						pathTree.addNodes(treeNode.getParentNode(), result.data, true);
					}
                } else {
                    UIToastr.showWarning(LANG.UI_HADOOP_GET_FOLDER_TREE, result.message);
                }

                Metronic.unblockUI('#path_tree');
            })
		}else{
			pathTree.checkNode(treeNode, !treeNode.checked, false, true);
		}
	}
	//文件加载更多
	var getBackupMoreTree =  function(treeId,pNode){
		//显示更多
		//判断父节点下的子节点是否全选
		var checkeFlag = pNode.getParentNode().check_Child_State==2 ? true : false;
		var params = {
			"hadoop_cluster_id":pNode.clusteruuid,
			"limit_count":_pageSize,
			"fetch_root_dir":pNode.getParentNode().filepath, //父级路径
			"start_after":pNode.next_start == undefined ? "" : pNode.next_start, //上一个节点路径
			"edit_flag":false,
			"start":pNode.next_index == undefined ? 0 : pNode.next_index,
		}
		var div = "#fileClusterTree_" + pNode.clusteruuid;
		Metronic.blockUI({target: div,animate: true});
		pAjaxRequest(params, '/api/v1/hadoop/recover_path_tree', "GET", function(d){
			Metronic.unblockUI(div);
			if(!d.success) {
				UIToastr.showInfo(LANG.UI_HADOOP_GET_FOLDER_TREE,d.message);
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
	
	//选中路径
	var pathOnCheck = function(e, id, node){
		//单选
		var allNodes = pathTree.getCheckedNodes(true);
		for(var i = 0; i < allNodes.length; i++){
			pathTree.checkNode(allNodes[i], false, false, false);	
		}
		pathTree.checkNode(node, true, false, false);	
	}
	
	//路径展开
	var pathNodeExpand = function(treeId, treeNode){
		if(treeNode.children) return true;
		let agentuuid = treeNode.event_type === 'hadoop_cluster' ? treeNode.clusteruuid : treeNode.uuid;
		if(!agentuuid)	return false;
		let dir = treeNode.dir_path;
		if(treeNode.title == "/" && treeNode.event_type != undefined) {//nas新增的根目录
			dir = treeNode.id
		}
		var params = {
			agent_uuid:agentuuid, 
			start:0, 
			limit:_pageSize, 
			filename:'',
			dir:dir, 
			pid:treeNode.id,
			code_type:treeNode.code_type
		};
		let api = '';
		switch (treeNode.event_type) {
			case 'agent':
				api = '/api/v1/files/recover_path_tree';
				break;
			case 'nas':
				api = '/api/v1/nas/recover_path_tree';
				break;
			case 'obs_agent':
				api = '/api/v1/s3/recover_path_tree';
				break;
			case 'hadoop_cluster':
				params = {
					hadoop_cluster_id:agentuuid,
					limit_count:_pageSize,
					fetch_root_dir:dir, //父级路径
					code_type: treeNode.code_type,
					start_after:"", //第一次使用空字符串
					start: 0,
					edit_flag:false
				}
				api = '/api/v1/hadoop/recover_path_tree';
				break;
			default:
				break;
		}
		Metronic.blockUI({target: '#path_tree', animate: true});
		pAjaxRequest(params, api, 'GET', (result) => {
			if (result.success) {
				let treeData = treeNode.event_type === 'hadoop_cluster' ? result.data.file_nodes : result.data
				pathTree.addNodes(treeNode, treeData, true);
			} else {
				UIToastr.showWarning(LANG.UI_HADOOP_RECOVERY_GET_SUBDIR_FAILED, '');
			}

			Metronic.unblockUI('#path_tree');
		})
	}

	function beforeEditName(treeId, treeNode) {
		className = (className === "dark" ? "":"dark");
		pathTree.selectNode(treeNode);
		pathTree.editName(treeNode);
		return false;
	}


	function addHoverDom(treeId, treeNode) {
		var sObj = $("#" + treeNode.tId + "_span");
		if (treeNode.more || treeNode.editNameFlag || $("#addBtn_"+treeNode.tId).length>0) return;
		if(!filenameFlag) return;
		var addStr = '<span class="button add" id="addBtn_'+ treeNode.tId + '" title="'+ LANG.UI_FILE_NEW_DIR +'" onfocus="this.blur();"></span>';
		sObj.after(addStr);
		var btn = $("#addBtn_"+treeNode.tId);
		// 点击添加节点
		if (btn) btn.bind("click", function(){
			if(treeNode.noRemoveBtn) {//不是新增的节点
				pathNodeExpand(treeId, treeNode);
			}
			if(treeNode.children != undefined && treeNode.children.length != 0) {
				pathTree.addNodes(treeNode, -1,
				{   id:(treeNode.id+'_'+treeNode.children.length),
					icon:'./img/fs/wenjianjia.png',
					iconOpen:"./img/fs/wenjianjiaopen.png",
					iconClose:"./img/fs/wenjianjia.png",
					title:'', 
					isParent:false, 
					pId:treeNode.id, 
					name:'recovery-destination' + (newCount++),
					isnew:true,
					new_dir_create:CONF.FLAG.SET,
					code_type:treeNode.code_type},
				false);
				var newnode = pathTree.getNodeByParam("isnew", true, null);
				beforeEditName(treeId,newnode);
				newnode.isnew = false;
			}else {
				pathTree.addNodes(
					treeNode, 
					-1, 
					{id:(treeNode.id+'_' + 0),
					icon:'./img/fs/wenjianjia.png',
					iconOpen:"./img/fs/wenjianjiaopen.png", 
					iconClose:"./img/fs/wenjianjia.png", 
					title:'', 
					isParent:false, 
					pId:treeNode.id, 
					name:'recovery-destination' + (newCount++),
					isnew:true,
					new_dir_create:CONF.FLAG.SET,
					code_type:treeNode.code_type},false);
				var newnode = pathTree.getNodeByParam("isnew", true, null);
				beforeEditName(treeId,newnode);
				newnode.isnew = false;
			}
			filenameFlag = false;
			return false;
		});
	};

	// 鼠标离开移除按钮
	function removeHoverDom(treeId, treeNode) {
		$("#addBtn_"+treeNode.tId).unbind().remove();
	};

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
					'<div class="cont ">' + 
						'<div class="cont-col1"></div>' + 
						'<div class="cont-col2">' + 
							'<div class="desc list-one" style="overflow:hidden;text-overflow: ellipsis;white-space: nowrap">' + info.des + '</div>' + 
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
			'<li class="list-group-item popovers speedTips list-group-item__speed" id="speed'+ liId+'"  data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="' +info.des + '">' + 
				'<div class="col1">' + 
					'<div class="cont ">' + 
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

    var initSpeedStrategyDes = function(){
        var titleDes = "";
        var des = "";
        if(speedList.length !=0){
            des += LANG.UI_GLOBAL_STRATEGY_SPEED_NUM + ": " + speedList.length;
        }
        for(var i=0;i<speedList.length;i++){
            titleDes += speedList[i].des + '. \n';
        }
        $('.speedlimitDes').html(des);
        $('.speedlimitDes').prop('title', titleDes);
        
    }



    var speedModeHandler = function(){
        if(this.value == 2){
            $('.setSpeedStrategy').hide();
        }else{
            $('.setSpeedStrategy').show();
        }
	}
	
	var checkSimpleForever = function(mode){
        for(var i=0;i<speedList.length;i++){
            if(mode == speedList[i].mode){
                return false;
            }
        }

        return true;
	}

	var initSpeedTimeStrategy = function(){
		var strategy = [];
		strategy[0] = {
			mode: 1,
			strategy_type: 2,
			days: [0, 0, 0, 0, 1, 0, 0],
			start_time: '23:00:00',
			end_time: '23:30:00',
		};
		//延迟设置,因为这里icheck会默认修改里面的选中事件
        $('#speedstrategy').speedstrategy({config: strategy});
        initSpeedFlag = true;
	}

    var setStrategyInfo = function(info){
		data.typeInfo.strategy = info;
		return true;
	}
	
	//得到strategy_each信息
	var getStrategyEach = function(type, tabpane){
		var info = {};
		//type:每天/每周/每月
		info.type = type;
		info.startTime = tabpane.find('.starttime').val();
		info.rollFlag = tabpane.find('.make-switch').get(0).checked;
		info.rollInterval = tabpane.find('.rolltime').val();
		info.endTime = tabpane.find('.endtime').val();
		
		return info;
	}
	
	var getDays = function(type, tabpane){
		var days = [];
		var input;
		if(type == CONF.STRATEGY_TYPE.WEEK){
			input = tabpane.find('.weekcheck').find('input');
		}else if(type == CONF.STRATEGY_TYPE.MONTH){
			input = tabpane.find('.monthcheck').find('input');
		}else{
			return days;
		}
		flagDay = false;
		input.each(function(i, d){
			if(d.checked){
				days[i] = 1;
				flagDay = true;
			}else{
				days[i] = 0;
			}
		});
		
		if(!flagDay){
			if(type == CONF.STRATEGY_TYPE.WEEK){
				$('.selectweektip').html(LANG.UI_STRATEGY_SELECT_WEEK).show();
			}else if(type == CONF.STRATEGY_TYPE.MONTH){
				$('.selectmonthtip').html(LANG.UI_STRATEGY_SELECT_DATE).show();
			}
			setStrategyInfo({});
		}else{
			$('.selectweektip').hide();
			$('.selectmonthtip').hide();
		}
		
		return days;
	}
	
	//每天
	var daySelect = function(tabpane){
		var info = {};
		info = getStrategyEach(CONF.STRATEGY_TYPE.DAY, tabpane);

		return setStrategyInfo(info);
	}
	//每周
	var weekSelect = function(tabpane){
		var info = {}, days = [];
		info = getStrategyEach(CONF.STRATEGY_TYPE.WEEK, tabpane);
		info.days = getDays(CONF.STRATEGY_TYPE.WEEK, tabpane);
		if(!flagDay) return false;
		return setStrategyInfo(info);
	}
	//每月
	var monthSelect = function(tabpane){
		var info = {}, days = [];
		info = getStrategyEach(CONF.STRATEGY_TYPE.MONTH, tabpane);
		info.days = getDays(CONF.STRATEGY_TYPE.MONTH, tabpane);
		if(!flagDay) return false;
		return setStrategyInfo(info);
	}
	//全局
	var globalSelect = function(tabpane, type){
		
	}

    var eachButtonClick = function(){
		var tabpane = $(this).closest('.tab-pane');
		var panelDefault = tabpane.closest('.panel-default');
		var panelHeading = panelDefault.children('.panel-heading');
		var i = panelHeading.find('i');
		//完全/增量/差异
		var setFlag = true;
		if(tabpane.hasClass('day')){
			setFlag = daySelect(tabpane);
		}else if(tabpane.hasClass('week')){
			setFlag = weekSelect(tabpane);
		}else if(tabpane.hasClass('month')){
			setFlag = monthSelect(tabpane);
		}else if(tabpane.hasClass('global')){
			setFlag = globalSelect(tabpane);
		}
		if(setFlag){
			i.prop('class', 'fa fa-check');
			$('#recover').collapse('hide');
		}else{
			i.prop('class', '');
		}
	}

    //得到开关的结果描述   开启/关闭
	var getSwitchDes = function(check){
		if(check){
			return LANG.UI_PUBLIC_ON;
		}
		return LANG.UI_PUBLIC_OFF;
	}

    var getStrategyDes = function(strategy){
		//每天12:12:12开始,不滚动
		//每天12:12:12开始,滚动间隔01:11:11,滚动结束时间23:11:11
		//每周1,2,3,4,5,6,
		var des = "";
		if(CONF.STRATEGY_TYPE.DAY == strategy.type){
			des += LANG.UI_STRATEGY_DAY + getEachStrategy(strategy); 
		}else if(CONF.STRATEGY_TYPE.WEEK == strategy.type){
			if(CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw"){
				des += LANG.UI_STRATEGY_WEEK + getStrategyDays(strategy.days) + getEachStrategy(strategy);
			}else{
				des += LANG.UI_STRATEGY_WEEK + getStrategyWeek(strategy.days) + getEachStrategy(strategy);
			}
		}else if(CONF.STRATEGY_TYPE.MONTH == strategy.type){
			des += LANG.UI_STRATEGY_MONTH + getStrategyDays(strategy.days) + getEachStrategy(strategy);
		}else if(CONF.STRATEGY_TYPE.GLOBAL == strategy.type){
			des += LANG.UI_STRATEGY_GLOBAL;
		}else{
			des += LANG.UI_PUBLIC_NOTHING + "<br><br>";
		}
		return des;
	}
	
	var getEachStrategy = function(strategy){
		var desEach = '';
		desEach += strategy.startTime;
		desEach += LANG.UI_STRATEGY_START + ", ";
		if(strategy.rollFlag){
			desEach += LANG.UI_STRATEGY_ROLL_INTERVAL + strategy.rollInterval + ", " + LANG.UI_STRATEGY_ROLL_OVER_TIME + strategy.endTime;
		}else{
			desEach += LANG.UI_STRATEGY_ROLL_NO;
		}
		desEach += "<br><br>";
		return desEach;
	}
	

    var getStrategyDays = function(days){
		var desDays = '';
		$.each(days, function(i,d){
			if(1 == d){
				var day = i+1;
				if(CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw"){
					desDays += day + ", ";
				}else{
					desDays += "Day" + day + ", ";
				}
				
			}
		});
		return desDays;
	}
	//获取每周显示日期
	var getStrategyWeek = function(days){
		var desDays = '';
		$.each(days, function(i,d){
			if(1 == d){
				desDays += CONF.WEEK[i] + ", ";
			}
		});
		return desDays;
	}
	// 显示加密算法
	var transferEncryptChange = function(){
		if(this.checked){
			$('.transfer-encrypt-method-form').show();
		}else{
			$('.transfer-encrypt-method-form').hide();
		}
	}
	//传输代理是否开启
	var applianceChange = function(){
		if(this.checked){
			$('.applianceselectdiv').show();
			$('.encryptdiv').show();
		}else{
			$('.applianceselectdiv').hide();
			$('.encryptdiv').hide();
            $('.transfer-encrypt-method-form').hide();	//加密传输算法
            $('#encrypt').bootstrapSwitch('state', false);
		}
	}
	//初始化appliance下拉框
	var initApplianceSelectold = function(){
		if(applianceFlag) return; //只加载一次
		$.post(CONF.AJAXPATH, {m:CONF.M.NODE,f:'getApplianceSelect',p:{}}, function(d){
			applianceFlag = true;
			var data = JSON.parse(d);
			if(!data.length) return;
			var applianceSelect = $('#applianceSelect');
			applianceSelect.empty();
			for(var i=0;i<data.length;i++){
				var option ='<option value="'+data[i].value+'">'+data[i].text+'</option>';
				applianceSelect.append(option);
			}
		});
	}
	var initApplianceSelect = function(){
	    $('#transferAgentTree').transferAgent();
	}


    //提交按钮
    var submit = function(){
		if('' == $.trim($("#jobname").val())){
			$('.jobnametip').html(LANG.UI_BACKUP_NAME_TIPS).show();
			return;
		}
		$('.jobnametip').hide();
		let jobName = $.trim($("#jobname").val());
		// 输入验证
		if(!customInputValidate('string',jobName)){
			return false;
		}
		data.job_name = $.trim($("#jobname").val());
		data.strategygroupuuid = $('#strategySelect option:selected').val();
		data.speedInfo = speedSubmitInfo();
		//恢复到哪儿
		data.recoveryType =  parseInt($("#deviceSelect").val());
		Metronic.blockUI({target: '#hadooprecoverycontent',animate: true,cenrerY: true,});
        //获取组织数据
        pAjaxRequest(data, "/api/v1/hadoop/jobs/restore", "POST", function (result) {
            Metronic.unblockUI('#hadooprecoverycontent');
            if (result.success) {
                UIToastr.showSuccess(LANG.UI_HADOOP_CREATE_RECOVERY_TASK,result.message);
                LOCATION('./content/platform/jobs/jobs.php', 'task');
            }
            else{
                UIToastr.showWarning(LANG.UI_HADOOP_CREATE_RECOVERY_TASK,result.message);
            }
        }, false);
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
				info['speedInfo'] = [{'des': selectedRow[0].detail}]
			}
		} else {
			// 自定义
			info['speedInfo'] = $('#speedstrategy').getSpeedStrategyConfigFinal();
		}
		return info;
	}
   
	
   return { 
        init:function(){
            // initClusterList();  //初始化集群列表
			inintDatatimePicker(); //初始化时间选择器
			initStorageList(); //初始化存储列表
            wizardInit();
            initListener(); 
            initTree();
        }
   }
}();

jQuery(document).ready(function() {   
	hadoop_recovery.init();
});