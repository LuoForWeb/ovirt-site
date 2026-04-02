var Obsdata = function () {
    //时间点树
    let zTree = null;
	let zNodes = [];
    let _daterangepicker_starttime, _daterangepicker_endtime, _daterangepicker_range;
    let agent_uuid, task_uuid;
    let _UserPassword;
    let initErrorFlag = false;
    let grid, gridInitFlag = false
    let gfsList = {}; // 主要用于判断是否修改了GFS,如果没修改则不提交后台
    let showNode;//搜索到的时间点
	let currentNode = {}; // 记录当前选中的树节点
	let $dateRangePicker = $('#daterangepicker');
	let taskKeyWord = '';
	const FULL_BACKUP_OPERATION_HTML =
		'<div class="btn-group dropdown-wrapper">' +
			'<button type="button" class="btn btn-dropdown-operate dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true">'
				+ LANG.UI_PUBLIC_OPERATION + ' <i class="fa fa-angle-down"></i></button>' +
			'<ul class="dropdown-menu" role="menu">' +
				'<li><button class="btn dropdown-menu__item me-0 remark" type="button"><i class="viconfont vicon-ge_modify me-4"></i> ' + LANG.UI_PUBLIC_REMARK + '</button></li>' +
				'<li><button class="btn dropdown-menu__item me-0 delete" type="button"><i class="viconfont vicon-ge_delete me-4"></i> ' + LANG.UI_PUBLIC_DELETE + '</button></li>' +
				'<li><button class="btn dropdown-menu__item me-0 setmark" type="button"><i class="viconfont vicon-ge_sign me-4"></i> ' + LANG.UI_SETTING_VM_DATA_SET_MARK + '</button></li>' +
			'</ul>' +
		'</div>';
	const FULL_BACKUP_WITHOUT_FOREVER_MARK_OPTION_HTML = 
		'<div class="btn-group dropdown-wrapper">' +
			'<button type="button" class="btn btn-dropdown-operate dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true">'
				+ LANG.UI_PUBLIC_OPERATION + ' <i class="fa fa-angle-down"></i></button>' +
			'<ul class="dropdown-menu" role="menu">' +
				'<li><button class="btn dropdown-menu__item me-0 remark" type="button"><i class="viconfont vicon-ge_modify me-4"></i> ' + LANG.UI_PUBLIC_REMARK + '</button></li>' +
				'<li><button class="btn dropdown-menu__item me-0 delete" type="button"><i class="viconfont vicon-ge_delete me-4"></i> ' + LANG.UI_PUBLIC_DELETE + '</button></li>' +
			'</ul>' +
		'</div>';
	const FULL_BACKUP_ONLY_REMARK_HTML = 
		'<div class="btn-group dropdown-wrapper">' +
			'<button type="button" class="btn btn-dropdown-operate dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true">'
				+ LANG.UI_PUBLIC_OPERATION + ' <i class="fa fa-angle-down"></i></button>' +
			'<ul class="dropdown-menu" role="menu">' +
				'<li><button class="btn dropdown-menu__item me-0 remark" type="button"><i class="viconfont vicon-ge_modify me-4"></i> ' + LANG.UI_PUBLIC_REMARK + '</button></li>' +
			'</ul>' +
		'</div>';
	const NOT_FULL_BACKUP_OPERATION_HTML =
		'<div class="btn-group dropdown-wrapper">' +
			'<button type="button" class="btn btn-dropdown-operate dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true">'
				+ LANG.UI_PUBLIC_OPERATION + ' <i class="fa fa-angle-down"></i></button>' +
			'<ul class="dropdown-menu" role="menu">' +
				'<li class="remark"><button class="btn dropdown-menu__item me-0" type="button" ><i class="viconfont vicon-ge_modify me-4"></i> ' + LANG.UI_PUBLIC_REMARK + '</button></li>' +
			'</ul>' +
		'</div>';

	//<-----------------------------    BEGIN BACKUP TIME POINT TREE TOOLBAR   -------------------------------------->

	/**
	 * 初始化当前用户密码用于删除二次确认
	 */
	const initUserPassword = () => {
		pAjaxRequest({}, "/api/v1/users/password", "GET", function (result) {
			if (result.success) {
				_UserPassword = result.data.password;
			}
		});
	}

	/**
	 * tree 删除后刷新
	 * @param nodeid
	 */
	const refreshAllTree = (nodeid) => {

		let nodes = zTree.getNodesByParam("id", nodeid, null);
		if(0 === nodes.length){
			return;
		}

		//要删除节点的父节点,如果父节点只有1个孩子节点,把父节点也要删除,父节点是任务名,不再处理任务名的父节点
		let parent = nodes[0].getParentNode();
		let childrenNum = 0;

		if (parent !== null) {
			childrenNum = parent.children.length;
		}

		if (1 === childrenNum) {
			zTree.removeNode(parent);
		} else {
			zTree.removeNode(nodes[0]);
		}
	}

	const removeParentNode = function(node){
		let parent = node.getParentNode();
		if(!parent) return;

		let childrenNum = parent.children.length;
		if (0 === childrenNum) {
			zTree.removeNode(parent);
			removeParentNode(parent);
			$('#filetablediv').hide();
			$('#tabletips').show();
		} else {
			return;
		}

	}

	/**
	 * 表格单项时间点删除刷新tree
	 * @param point
	 */
	const refreshTree = function(point){
		let nodes = zTree.getNodesByParam("id", point.timepoint_uuid, null);

		if(0 === nodes.length){
			return;
		}

		let fsNode = nodes[0]; // 要删除的节点
		//要删除节点的父节点,如果父节点只有1个孩子节点,把父节点也要删除,父节点是任务名,不再处理任务名的父节点
		let parent = fsNode.getParentNode();
		if(!parent) return;

		let childrenNum = parent.children.length;
		if (1 === childrenNum && point.type === 3) {
			zTree.removeNode(parent);
			removeParentNode(parent);
		} else {
			zTree.removeNode(fsNode);
		}
	}

	/**
	 * 提交删除时间点请求
	 * @param storageUUID 存储uuid
	 * @param agentList 对象存储list
	 * @param pointList 时间点list
	 * @param flag 标记是来自左侧删除按钮还是来自右侧表格的单项删除按钮，true 为来自左侧，false为来自表格
	 */
	const submitSelectDelete = (storageUUID, agentList, pointList, flag) => {
		let params = {
			agent_list: agentList,
			point_list: pointList,
			storage_uuid: storageUUID
		}

		Metronic.blockUI({target: '#timepointdiv',animate: true});

		pAjaxRequest(params, '/api/v1/s3/batch_timepoint', 'DELETE', (result) => {
			if (operateResponseList(result, LANG.UI_MICROSOFT365_DELETE_TIME_POINT)) {
				if (flag) {
					let node = zTree.getCheckedNodes(true);
					for (let i = 0; i < node.length; i++) {
						let halfCheck = node[i].getCheckStatus();
						
						if (!halfCheck.half) {
							refreshAllTree(node[i].id);
						} else {
							zTree.checkNode(node[i], !node[i].checked, false, false);
						}
					}
				} else {
					refreshTree(pointList[0]);
				}

				$('#datatable').bootstrapTable('refresh');
			}

			Metronic.unblockUI('#timepointdiv');
		})
	}

	/**
	 * 删除时间节点二次弹窗
	 */
	const deleteSelectPoint = () => {
		if (!zTree) {
			return;
		}

		let nodes = zTree.getCheckedNodes(true);
		let pointList = [];
		let agentList = [];
		let storageUUID = $('#storage_select').val();

		if (nodes.length === 0){
			UIToastr.showInfo(LANG.UI_DATA_BATCH_DELETE_NOT_CHECK,LANG.UI_DATA_BATCH_DELETE_NOT_CHECK_TIPS);
			return;
		}

		nodes.forEach(item => {
			if (item.type === 2) { // 只选中对象存储层级
				if (!item.children) {
					agentList.push({
						agent_uuid: item.agent_uuid,
						job_uuid: item.job_uuid,
						node_uuid: item.node_uuid,
						type: item.type,
						src_data_deleted_flag: item.src_data_deleted_flag // 备份时间点的数据是否在生产中被删除（归档）
					});
				}
			} else if (item.type === 3) { // 选中时间点层级
				pointList.push({
					agent_uuid: item.agent_uuid,
					job_uuid: item.taskuuid,
					node_uuid: item.nodeuuid,
					type: item.type,
					src_data_deleted_flag: item.src_data_deleted_flag, // 备份时间点的数据是否在生产中被删除（归档
					timepoint_uuid: item.timepoint_uuid
				})

				if (item.isParent) { // 带增倍点或者差异点的完备点场景
					item.children.forEach(i => {
						pointList.push({
							agent_uuid: i.agent_uuid,
							job_uuid: i.job_uuid,
							node_uuid: i.node_uuid,
							type: i.type,
							src_data_deleted_flag: i.src_data_deleted_flag, // 备份时间点的数据是否在生产中被删除（归档
							timepoint_uuid: i.timepoint_uuid
						})
					})
				}
			}
		});

		bootbox.confirm({
			title: LANG.UI_DATA_DELETE_TIMEPOINT,
			message: LANG.UI_OBS_DELETE_TIMEPOINT_TIPS,
			callback: function(r) {
				if(!r) return;
				initErrorFlag = false;
				bootbox.prompt({
					title: LANG.UI_TENANT_DELETE_COFIRM_INPUT_YES,
					inputType: 'password',
					callback: function (result) {
						if(result === null) return;

						if (hex_md5(result) === _UserPassword) {
							submitSelectDelete(storageUUID, agentList, pointList, true);
							return true;
						} else {
							$('.bootbox-input').css('border-color', "#a94442");
							if(!initErrorFlag){
								let des = '<p class="password-error" style="margin-top:5px;color:#a94442">'+LANG.UI_DB_DM_CORRECT_PASSWORD_INPUT_TIPS+'</p>';

								$('.bootbox-input').after(des);

								initErrorFlag = true;
							}
							return false;
						}
					}
				});
			}
		});
	}

	/**
	 * 初始化节点下拉列表
	 */
	const initPointShowType = () => {
		pAjaxRequest({backupDataFlag: true}, '/api/v1/storages/type', "GET", (result) => {
			if (result.success) {
				let data = result.data;
				let storageSelect = $('#storage_select');
				storageSelect.empty();

				for (let i = 0; i < data.length; i++) {
					let option = $("<option>").text(data[i].text).val(data[i].storageid);
					storageSelect.append(option);
				}

				initBackupTimePointTree();
			} else {
				UIToastr.showWarning(LANG.UI_OBS_GET_STORAGE_FAILED, res.message);
			}
		})
	}

	/**
	 * 同步修改tree节点name
	 * @param timepointUUID
	 * @param $Wflag
	 * @param $Mflag
	 * @param $Yflag
	 * @param $Fflag
	 * @param remark
	 */
	const editZtreeName = function(timepointUUID, $Wflag, $Mflag, $Yflag, $Fflag, remark){
		//根据timeUUID得到ztree的node数据，没有搜到返回null
		let node = zTree.getNodeByParam('timepoint_uuid', timepointUUID, null);
		if (!node) {
			return;
		}
		//获得原来的name
		let oldName = node.oldname;
		let newName = '';

		let markStr = '';
		if($Wflag){
			markStr +='<i title="'+LANG.UI_SETTING_VM_GFS_WEEK_POINT+'" class="viconfont vicon-remark-week"></i>';
		}
		if($Mflag){
			markStr +='<i title="'+LANG.UI_SETTING_VM_GFS_MONTH_POINT+'" class="viconfont vicon-remark-month"></i>';
		}
		if($Yflag){
			markStr +='<i title="'+LANG.UI_SETTING_VM_GFS_YEAR_POINT+'" class="viconfont vicon-remark-year"></i>';
		}
		if($Fflag){
			markStr +='<i title="'+LANG.UI_SETTING_VM_GFS_FOREVER_POINT+'" class="viconfont vicon-remark-forever"></i>';
		}
		// if(remark && remark != ""){
		// 	markStr += '<a style="display:inline-block;color: #5b9bd1;position: relative;top:5px;left:-4px;" class="popovers remarktips" data-container="body" data-trigger="hover" data-placement="right" data-content="'+ remark +'"><i class="fa fa-info-circle fa-lg" style="font-size: 21px !important; position:relative;top:-4px;"></i></a>';
		// }
		if(node.src_data_deleted_flag) {
			newName = oldName+ `(${LANG.UI_VIRTUAL_ARCHIVE_DATA})` + " "+markStr;
		} else {
			newName = oldName + " " + markStr;
		}
		node.name = newName;
		zTree.updateNode(node);
	}

	/**
	 * 展示筛选条件内容
	 * @param s 搜索关键词
	 * @param f 永久标记点
	 */
	const setFilterContent = (s, f) => {
		if (!s && !f) {
			$("#markStr").empty();
			return;
		}

		let markStr = LANG.UI_SETTING_VM_DATA_SCREEN + ": ";

		if (s) {
			markStr += s;
		}

		if (f) {
			markStr += '<i class="viconfont vicon-remark-forever"></i>';
		}

		markStr += '<a id="clearGFS" style="text-decoration: underline;margin-left: 10px;">'+ LANG.UI_SETTING_VM_DATA_SCREEN_CLEAN +'</a>';
		$("#markStr").html(markStr);

		$("#clearGFS").on('click',function(){
			$('#nosearchtips').hide();
			initBackupTimePointTree();
			$("#markStr").empty();
		})
	}

	const searchByTree = () => {
		if (!taskKeyWord) {
			var keyword = $.trim($('#searchfs').val());
		} else {
			var keyword = taskKeyWord;
			taskKeyWord = '';
		}

		var allNodes = zTree.transformToArray(zTree.getNodes());
		zTree.hideNodes(allNodes);    //当开始搜索时，先将所有节点隐藏
		var nodeList = [];
		if(keyword != '') {
			nodeList = zTree.getNodesByParamFuzzy('name', keyword, 0);    //通过关键字模糊搜索
		}
		//搜gfs标记点
		if($("input[name=foreverFilter]").get(0).checked) {
			nodeList =  zTree.getNodesByParamFuzzy('name', 'viconfont vicon-remark-forever', 0);
		}
 		if(nodeList.length == 0) {
			$('#nosearchtips').show();
			return;
		}
		var arr = new Array();
		for(var i=0; i<nodeList.length; i++){
			arr = $.merge(arr,nodeList[i].getPath());    //找出节点的所有父节点（包括自己）
			if(nodeList[i].children) {//展开过，有子节点，把子节点加进去
				arr = $.merge(arr,nodeList[i].children);
			}
		}
		var firstlevel = [];
		var otherlevel = [];
		arr.forEach(item=>{
			if(item.level != 0) {
				otherlevel.push(item);
			}else {
				firstlevel.push(item);
			}
		});
		var arr = zTree.transformToArray(otherlevel);//避免获取到第一层下的其他任务
		arr = arr.concat(firstlevel);
		zTree.showNodes($.unique(arr));    //显示所有要求的节点及其路径节点
		//展开显示的所有节点
		arr.forEach(item => {
			if(item.children) {
				zTree.expandNode(item, true);
			}
		});
	}

	/**
	 * 筛选树节点
	 */
	const searchFS = () => {
		let search = $.trim($('#searchfs').val());
		let forever = $("input[name=foreverFilter]").get(0).checked;

		if (!search && !forever) {
			return;
		}

		setFilterContent(search, forever);

		// 重置备份时间点树
		if (zNodes.length === 0){
			$('#filetimepointtree').hide();
			$("#nopointtips").show();

			Metronic.unblockUI('.tree-wrapper__ztree');
			return;
		}

		$("#nopointtips").hide();
		$('#filetimepointtree').show();

		let setting = {
			check: {
				enable: true,
				nocheckInherit: false
			},
			view: {
				nameIsHTML: true
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
				onCheck: pointOnCheck,
				beforeClick: nodeSelect,
				beforeExpand: nodeExpand
			}
		};

		zTree = $.fn.zTree.init($("#filetimepointtree"), setting, zNodes);

		Metronic.blockUI({target: '.tree-wrapper__ztree', animate: true, cenrerY: true,});
		
		let params = {
			data_flag: true,
			storage_uuid: $('#storage_select').val(),
			search: search,
			forever: forever
		}

		pAjaxRequest(params, '/api/v1/s3/search_timepoint', "GET", (result) => {
			if (result.success) {
				if (result.data.length > 0) {
					$('#nosearchtips').hide();
					$('#filetimepointtree').show();
					showNode = [];

					result.data.forEach(item => {
						// 在当前树节点中搜索异步请求得到的时间点，如果当前树不存在，加入showNode数组
						let nodeExist = zTree.getNodesByParam("id", item.id, null)[0];
						if(nodeExist == null) {
							showNode.push(item);
						}
					});

					if (showNode.length > 0) { // 把showNode中时间点加到对应父节点下
						showNode.forEach(item => {
							//先把完备点加进去，再放增量差异等，避免先搜增量差异等搜不到父节点
							if(item.type == 3) {
								let parentNode = zTree.getNodesByParam("id", item.pId, null)[0];
								zTree.addNodes(parentNode, item, true);
								// 找到任务名，如果任务名不同，把关键词改了
								if(parentNode) {
									let taskNode = parentNode.getParentNode();
									if (taskNode.task_name != item.task_name) {
										taskKeyWord = taskNode.task_name;
									}
								}
							}
						});

						showNode.forEach(item => {
							if(item.type == 4) {//增量差异等
								let parentNode = zTree.getNodesByParam("id", item.pId, null)[0];
								zTree.addNodes(parentNode, item, true);
								if(parentNode) {
									let taskNode = parentNode.getParentNode();
									if (taskNode.task_name != item.task_name) {
										taskKeyWord = taskNode.task_name;
									}
								}
							}
						});
					}
					
					searchByTree();
				} else {
					$('#filetimepointtree').hide();
					$('#nosearchtips').show();
				}
			} else {
				UIToastr.showWarning(LANG.UI_OBS_GET_BACKUP_DATA_FAILED, result.message);
			}

			Metronic.unblockUI('.tree-wrapper__ztree');
		})
	}

	//<-----------------------------    END BACKUP TIME POINT TREE TOOLBAR   ---------------------------------------->

	//<-----------------------------    BEGIN BACKUP TIME POINT TABLE TOOLBAR    ------------------------------------>

	/**
	 * 初始化日期范围选择器
	 */
	const initDateTimePicker = () => {

		//初始化日期时间选择控件
		$dateRangePicker.daterangepicker({
			"autoUpdateInput": false,											//是否自动填充input
			"startDate": moment().subtract(6, 'days').startOf('day'),			//默认开始时间
			"endDate": moment({hour: 23, minute: 59}),	//默认结束时间
			"minDate": moment().subtract(1, 'month'), //最早可以选的日期
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
		$dateRangePicker.on('apply.daterangepicker', function(ev, picker) {
			//给全局变量赋值,然后设置input
			_daterangepicker_starttime = picker.startDate.format('YYYY-MM-DD HH:mm:ss');
			_daterangepicker_endtime = picker.endDate.format('YYYY-MM-DD HH:mm:ss');
			_daterangepicker_range = picker.chosenLabel;
			$(this).val(picker.startDate.format('YYYY-MM-DD HH:mm:ss') + ' - ' + picker.endDate.format('YYYY-MM-DD HH:mm:ss'));
		});

		$dateRangePicker.on('cancel.daterangepicker', function(ev, picker) {
			//清除全局变量,然后设置input
			_daterangepicker_starttime = "";
			_daterangepicker_endtime = "";
			_daterangepicker_range = "";
			$(this).val('');
		});

		//input右侧的图标事件
		$('.daterangepickerdiv i').click(function() {
			$(this).parent().find('input').click();
		});
	}

	const boolToInt = function(thisbool){
		if (thisbool) {
			return 1;
		} else {
			return 2;
		}
	}

	/**
	 * 高级搜索条件展示内容
	 * @param p
	 */
	const addSearchContent = (p) => {
		let info = "";
		$('.searchContent').text('');

		if (p.start_time && p.end_time) {
			info += '<span id="time" title="' + p.start_time + "~" + p.end_time + '"> '+ LANG.UI_SEARCH_TIME_RANGE +': <i>' + p.start_time + "~" + p.end_time + '</i><em>X</em></span>';
		}

		if (p.timepoint_type !== 0) {
			info += '<span id="timepointType" title="' + $('#timepointType').find("option:selected").text() + '"> '+ LANG.UI_SEARCH_TYPE +': <i>' + $('#timepointType').find("option:selected").text() + '</i><em>X</em></span>';
		}

		if (p.forever !== 2) {
			info += '<span id="forever" title="' + LANG.UI_SETTING_VM_GFS_FOREVER_POINT + '"> '+ LANG.UI_SEARCH_STAR +': <i>' + LANG.UI_SETTING_VM_GFS_FOREVER_POINT + '</i><em>X</em></span>';
		} else {
			info += '<span id="impermanence" title="' + LANG.UI_FILE_NOT_PERMANENT_MARK + '"> '+ LANG.UI_SEARCH_STAR +': <i>' + LANG.UI_FILE_NOT_PERMANENT_MARK + '</i><em>X</em></span>';
		}

		$('.searchContent').append(info);
		$('#searchDiv').removeClass('opacity-0');
		$('#searchDiv').addClass('opacity-100');

		// 清除单项
		$('#searchDiv .searchContent em').on('click', function(){
			$(this).parent().remove();

			let searchContent = $('#searchDiv .searchContent');

			if (searchContent[0].children.length === 0){
				$('#searchDiv').removeClass('opacity-100');
				$('#searchDiv').addClass('opacity-0');
			}

			let parent  = $(this).parent();
			let id = parent[0].id;

			switch (id) {
				case 'time': // 清除时间单项
					p.start_time = '';
					p.end_time = '';
					break;
				case 'timepointType': // 清除类型单项
					p.timepoint_type = 0;
					break;
				case 'forever': // 清除永久标记点单项
					delete p.forever
					break;
				case 'impermanence': // 清除非永久标记点单项
					delete p.forever
					break;
				default:
					break;
			}

			initFileDataTable(p);
		});

		// 清除所有
		$('#searchDiv .clearSearch').on('click',function(){
			$('#searchDiv .searchContent').text('');
			$('#searchDiv').removeClass('opacity-100');
			$('#searchDiv').addClass('opacity-0');

			let params = {
				agent_uuid: currentNode.agent_uuid,
				job_uuid: currentNode.job_uuid,
				storage_uuid: $('#storage_select').val()
			}

			initFileDataTable(params);
		});

		//如果没搜索条件，先隐藏div
		if(!info){
			$('#searchDiv').removeClass('opacity-100');
			$('#searchDiv').addClass('opacity-0');
		}
	}

	/**
	 * 时间点列表高级搜索
	 */
	const advancedSearchTimePoint = () => {
		$('#serach_submit').on('click', function(){
			let params = {
				agent_uuid: currentNode.agent_uuid,
				job_uuid: currentNode.job_uuid,
				storage_uuid: $('#storage_select').val(),
				data_flag: 1,
				start_time : _daterangepicker_starttime ?  _daterangepicker_starttime : '', //任务开始时间查询范围开头
				end_time : _daterangepicker_endtime ?  _daterangepicker_endtime : '',      //任务开始时间查询范围结尾
				timepoint_type : parseInt($('#searchmodal #timepointType').val()),
				forever: boolToInt($("input[name=foreverCheck1]").get(0).checked)
			};

			// 添加搜索条件显示
			addSearchContent(params);

			// 获取时间点表格数据
			initFileDataTable(params);

			$('#searchmodal').modal('hide');
		});
	}

	//<-----------------------------    END BACKUP TIME POINT TABLE TOOLBAR    -------------------------------------->

	//<-----------------------------    BEGIN BACKUP TIME POINT TABLE    -------------------------------------------->

	/**
	 * 提交设置标记
	 * @param remark
	 * @param row
	 */
	const submitRemark = function(remark, row){
		Metronic.blockUI({target: '#datatable',animate: true});

		pAjaxRequest({remark: remark, time_point_uuid: row.timepoint_uuid}, '/api/v1/s3/manage_data', 'PUT', (result) => {
			if (operateResponseList(result, LANG.UI_MICROSOFT365_ADD_REMARK)) {
				$('#datatable').bootstrapTable('refresh');
			}

			Metronic.unblockUI('#datatable');
		});
	}

	/**
	 * 打开设置标记弹窗
	 * @param row
	 */
	const remarkPoint = function (row) {
		let value = row.remark;
		bootbox.prompt({
			title: LANG.UI_DATA_ADD_REMARK,
			value: value,
			callback: function (result) {
				if (result === null || $.trim(result) === value) {
					return;
				}
				submitRemark($.trim(result), row);
			}
		});
	}

	/**
	 * 删除单个时间点
	 */
	const deletePoint = function(row){
		let pointList = [];
		let agentList = [];

		pointList.push({
			agent_uuid: row.agent_uuid,
			job_uuid: row.job_uuid,
			node_uuid: row.node_uuid,
			type: 3,
			src_data_deleted_flag: row.src_data_deleted_flag, // 备份时间点的数据是否在生产中被删除（归档
			timepoint_uuid: row.timepoint_uuid
		})
		
		bootbox.confirm({
			title: LANG.UI_DATA_DELETE_TIMEPOINT,
			message: LANG.UI_OBS_DELETE_TIMEPOINT_TIPS,
			callback: function(r) {
				if(!r) return;
				initErrorFlag = false;
				bootbox.prompt({
					title: LANG.UI_TENANT_DELETE_COFIRM_INPUT_YES,
					inputType: 'password',
					callback: function (result) {
						if(result === null) return;

						if (hex_md5(result) === _UserPassword) {
							submitSelectDelete(row.storage_uuid, agentList, pointList, false);
							return true;
						} else {
							$('.bootbox-input').css('border-color', "#a94442");
							if(!initErrorFlag){
								let des = '<p class="password-error" style="margin-top:5px;color:#a94442">'+LANG.UI_DB_DM_CORRECT_PASSWORD_INPUT_TIPS+'</p>';

								$('.bootbox-input').after(des);

								initErrorFlag = true;
							}
							return false;
						}
					}
				});
			}
		});
	}

	const markSubmit = function(){
		//先得到所有信息
		let forever = $("input[name=foreverCheck]").get(0).checked;
		//得到信息后先判断是否有做修改 如果没有做修改则不提交后台
		if(gfsList.forever === forever){
			$('#setAllMark').modal('hide');
			gfsList = {};
			return;
		}

		//设置永久标记
		if(gfsList.forever !== forever){
			if(forever){
				Metronic.blockUI({target: '#setAllMark',animate: true});
				pAjaxRequest({time_point_uuid: gfsList.timepoint_uuid}, '/api/v1/s3/star', 'POST', (result) => {
					if (operateResponseList(result, LANG.UI_SETTING_VM_DATA_SET_MARK)) {
						$('#datatable').bootstrapTable('refresh');
					}

					Metronic.unblockUI('#setAllMark');
				});
			} else {
				Metronic.blockUI({target: '#setAllMark',animate: true});
				pAjaxRequest({time_point_uuid: gfsList.timepoint_uuid}, '/api/v1/s3/star', 'DELETE', (result) => {
					if (operateResponseList(result, LANG.UI_MICROSOFT365_CANCEL_MARK)) {
						$('#datatable').bootstrapTable('refresh');
					}

					Metronic.unblockUI('#setAllMark');
				});
			}
		}
		$('#setAllMark').modal('hide');
		editZtreeName(gfsList.timepoint_uuid,false,false,false, forever, gfsList.remark);
	}

	const setMark = function(row){
		gfsList = {
			forever: row.importance_flag,
			timepoint_uuid: row.timepoint_uuid,
			node_uuid: row.node_uuid,
			remark: row.remark
		};

		$("input[name=foreverCheck]").iCheck('uncheck'); // 初始化清空所有勾选项


		if(gfsList.forever){
			$("input[name=foreverCheck]").iCheck('check');
		}

		$('#setAllMark').modal('show');
	}

	/**
	 * 初始化备份时间点表格
	 * @param params
	 */
	const initFileDataTable = (params) => {
		let operates = {
			'click .remark': function (event, value, row, index) {
				remarkPoint(row);
			},
			'click .delete': function (event, value, row, index) {
				deletePoint(row);
			},
			'click .setmark': function (event, value, row, index) {
				setMark(row);
			}
		}

		let tableOptions = {
			pagination:true,
			pageList: [5, 10, 25, 50],
			vin_url: "/api/v1/s3/timepoint_grid",
			vin_params: function () {
				return params
			},
			vin_method: "GET",
			sortName: 'timepoint',
            sortOrder: 'desc',
			resizable: true,
			PostBody: function () {
				$('#datatable .remarktips').popover();     //初始化tips
				$('#datatable th[data-field="timepoint"]').css('width', '25%');
				$('#datatable th[data-field="backup_mode"]').css('width', '10%');
				$('#datatable th[data-field="total_size"]').css('width', '10%');
				$('#datatable th[data-field="write_size"]').css('width', '10%');
				$('#datatable th[data-field="storage_name"]').css('width', '25%');
				$('#datatable th[data-field="user_uuid"]').css('width', '10%');
				$('#datatable th[data-field="operate"]').css('width', '10%');
			},
			columns: [
				{
					field: 'timepoint',
					title: LANG.UI_RECOVERY_TIMEPOINT,
					sortable: true,
					align: 'center',
					formatter: function (timepoint, row) {
						return '<span title="' + row.timepoint_uuid + '"> ' + timepoint + ' ' + '<span>' + row.remarks_span + row.star_span + '</span>' + '</span>';
					}
				},
				{
					field: 'backup_mode',
					title: LANG.UI_STORAGE_TYPE,
					sortable: true,
					align: 'center',
					formatter: function(index, row) {
						return `<span>${row.timepoint_type_desc}</span>`;
					}
				},
				{
					field: 'total_size',
					title: LANG.UI_COPY_DATA_SIZE,
					sortable: true,
					align: 'center',
				},
				{
					field: 'write_size',
					title: LANG.UI_PUBLIC_REAL_SIZE,
					sortable: true,
					align: 'center',
				},
				{
					field: 'storage_name',
					title: LANG.UI_BACKUP_FILE_STORAGE,
					sortable: false,
					align: 'center',
				},
				{
					field: 'user_uuid',
					title: LANG.UI_CLIENT_OWNER,
					sortable: true,
					align: 'center',
					formatter: function(index, row) {
						return `<span>${row.owner}</span>`;
					}
				},
				{
					field: 'operate',
					title: LANG.UI_PUBLIC_OPERATION,
					sortable: false,
					align: 'center',
					opButton: true,
					clickToSelect: false, //不可以通过点击列选中
					events: operates,
					formatter: function (index, row) {
						let html = '';
						html += FULL_BACKUP_OPERATION_HTML;
						if (row.backup_mode === 1) {
							let op = row.op.join('');
							let html = '';
							
							switch (op) {
								case '123':
									html = FULL_BACKUP_OPERATION_HTML;
									break;
								case '12':
									html = FULL_BACKUP_WITHOUT_FOREVER_MARK_OPTION_HTML;
									break;
								case '1':
									html = FULL_BACKUP_ONLY_REMARK_HTML;
									break;
								default:
									break;
							}

							return html;
						} else {
							return NOT_FULL_BACKUP_OPERATION_HTML;
						}
					}
				}
			]
		}

		$('#tabletips').hide();
		$('#filetablediv').show();

		$('#datatable').bootstrapTable('destroy');
		$('#datatable').baseTableConfig().init(tableOptions);
	}

	//<-----------------------------    END BACKUP TIME POINT TABLE    ---------------------------------------------->


	//<-----------------------------    BEGIN BACKUP TIME POINT TREE    --------------------------------------------->

	/**
	 * 获取文件备份时间点数据
	 * @param {*} treeId
	 * @param {*} treeNode
	 * @param {*} refreshFlag
	 * @param {*} expandFlag
	 */
	const getSyncVcenterInfo = (treeId, treeNode, refreshFlag, expandFlag) => {
		currentNode = treeNode;
		let params = {
			job_uuid: treeNode.job_uuid,
			id: treeNode.id,
			refresh: refreshFlag,
			storage_uuid: $('#storage_select').val(),
			agent_uuid: treeNode.agent_uuid,
			recover_flag: true,
			data_flag: true
		}

		let checkeFlag = treeNode.checked;

		Metronic.blockUI({target: '.ztree',animate: true});
		pAjaxRequest(params, '/api/v1/s3/sync_backup_timepoint', 'GET', (result) => {
			if (result.success) {
				if (checkeFlag && result.data.length > 0) {
					result.data.forEach(item => {
						item.checked = true;
					})
				}
				
				$.fn.zTree.getZTreeObj(treeId).removeChildNodes(treeNode);
				$.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, result.data, true);
				$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);

				if(expandFlag){
					$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true, true, true);
				}

			} else {
				UIToastr.showWarning(LANG.UI_OBS_GET_BACKUP_DATA_FAILED, result.message);
			}

			Metronic.unblockUI('.ztree');
		});
	}

	const pointOnCheck = function(e, treeId, treeNode) {
		
	}

	/**
	 * 节点展开事件
	 * @param {*} treeId
	 * @param {*} treeNode
	 * @returns
	 */
	const nodeExpand = function(treeId, treeNode) {
		if (treeNode.level === 1) {
			getSyncVcenterInfo(treeId, treeNode, false, false);
		}
	}

	/**
	 * 节点选择
	 * @param {*} treeId
	 * @param {*} treeNode
	 */
	const nodeSelect = function(treeId, treeNode) {
		if(2 === treeNode.type) { // 选中第二层及得对象存储时
			$('#fsdataUrl').text('');

			let parent = treeNode.getParentNode();
			let info = parent.name + "---" + treeNode.name;
			$('#fsdataUrl').append(info);

			let params = {
				agent_uuid: treeNode.agent_uuid,
				job_uuid: treeNode.job_uuid,
				storage_uuid: $('#storage_select').val()
			}

			initFileDataTable(params);
		}
		//异步加载时间点
		nodeExpand(treeId, treeNode);
		zTree.expandNode(treeNode, true);
	}

	/**
	 * 初始化时间点列表树
	 */
	const initBackupTimePointTree = () => {
		let params = {
			data_flag: true,
			storage_uuid: $('#storage_select').val() || ''
		}

		Metronic.blockUI({target: '.tree-wrapper__ztree', animate: true, cenrerY: true,});
		pAjaxRequest(params, '/api/v1/s3/recover_data_tree', 'GET', (result) => {
			if (result.success) {
				zNodes = result.data;
				if (zNodes.length === 0){
					$('#filetimepointtree').hide();
					$("#nopointtips").show();

					Metronic.unblockUI('.tree-wrapper__ztree');
					return;
				}

				$("#nopointtips").hide();
				$('#filetimepointtree').show();

				let setting = {
					check: {
						enable: true,
						nocheckInherit: false
					},
					view: {
						nameIsHTML: true
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
						onCheck: pointOnCheck,
						beforeClick: nodeSelect,
						beforeExpand: nodeExpand
					}
				};

				zTree = $.fn.zTree.init($("#filetimepointtree"), setting, zNodes);
			} else {
				UIToastr.showWarning(LANG.UI_OBS_GET_BACKUP_TASK_DATA_FAILED, result.message);
			}

			Metronic.unblockUI('.tree-wrapper__ztree');
		})
	}

	//<-----------------------------    END BACKUP TIME POINT TREE    -------------------------------->

	/**
	 * 初始化事件监听
	 */
	const initListener = () => {
		// 节点下拉 change 监听
		$('#storage_select').on('change', () => {
			initBackupTimePointTree();
		});

		// 树节点删除 click 监听
		$('#allDelete').on('click', deleteSelectPoint);

		// 搜索模态框 click 监听
		$('#filterSearch').click(function(){
			$('#filterSearch').popModal({
				html : $('#filter-content'),
				placement : 'bottomLeft',
				showCloseBut : true,
				onDocumentClickClose : true,
				onOkBut : searchFS,
				onCancelBut : function(){},
				onLoad : function(){
					// 重置表单
					$('#filter-content').find('.icheck').iCheck('uncheck');
					$('#filter-content').find('#searchfs').val('');
				},
				onClose : function(){},
				maxWidth: 300,
				maxHeight: 'auto',
			});
		});

		// 弹出高级搜索模态框
		$('#searchAll').on('click', function(){
			$dateRangePicker.val('');
			$dateRangePicker.data('daterangepicker').setStartDate(moment().subtract(6, 'days').startOf('day'));
			$dateRangePicker.data('daterangepicker').setEndDate(moment({hour: 23, minute: 59}));

			$('#searchmodal').find('#timepointType').val(0);

			$('#searchmodal').find('.icheck').iCheck('uncheck');

			$('#searchmodal').modal({'width':'800px'});
		});


		// 监听高级搜索submit
		advancedSearchTimePoint();

		//设置GFS标记
		$("#mark_submit").on('click',function(){
			markSubmit();
		});

		$('.icheck').iCheck({
			checkboxClass: 'icheckbox_square-blue',
			radioClass: 'iradio_square-blue',
		});

		$('#mark_tips_close').on('click', () => {
			$('.tabledata-wrapper__content__table .table-container').css('height', 'calc(100% - 60px)');
		})
	}

    return {
        init: () =>{
			initPointShowType();
			initListener();
			initDateTimePicker();
            initUserPassword();
        }
    }
}();

jQuery(document).ready(function() {
    Obsdata.init();
});