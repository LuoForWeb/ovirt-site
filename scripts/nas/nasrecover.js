var NasRecover = function () {
	const STORAGE_DEVICE_TYPE = {
        FILE: 1,
        NAS: 2,
        HADOOP: 3,
        OBS: 4
    }; // 存储设备类型
	let ajaxData = {
		pointInfo: {
			nasuuid: '', // nas uuid
			agentUUID: '', // 代理端UUID
            pointUUID: '', // 时间点UUID
            taskUUID: '', // 任务UUID
            fileInfo: []
		},
		recoverInfo: {
			agentUUID: '',
			password: ''
		},
		typeInfo: { 
			high: { 
				trasfer: {}
			},
			strategy: {}
		},
		taskName:'',
		storeInfo: {},
		highInfo: {
			dir_tree_recovery_flag: false,
            same_file_strategy: 1,
            link_file_pass_flag: false,
            permission_operate_flag: false
		}
	};
	let share_path = ''//共享路径
	let initTreeFlag = false; //切换节点初始化时间点数flag
	let timepointTree, hostTree, hostInitFlag = false, pathTree;
	let vmOldName = [];
	let flagDay = false;
	let filenameFlag = true;
	let _pageSize = 20;	//文件每次请求条数
	let allpointlist;  //保存一条链的时间点
	let allpointPsw;    //保存一条链的时间点的密码
	let networkFlag = false; //是否显示传输网络
	let timepointNode;
	//备份的文件列表和恢复文件列表
	let _rBackupFileListDatatable, _recoverFileListDatatable;
	//最后一步验证恢复文件列表
	let _filelistShow;
	let _sclass = 's';		//文件类型大小
	let _fileList = [];		//可选择文件列表
	//代理名称,代理IP,时间点任务名,时间点, 当前文件路径,路径头
	let _nasName, _pointTaskName, _pointName, _path = '', _pathArr = [];
	//md5标志,高八位MD5,第八位MD5,只用于显示更多
	let  _md5Flag = 1, _md5High = '', _md5Low = '';
	let timer = null;
	let newCount = 1;
	let initSpeedFlag = false;
	let speedList = [];
	let zTreeFile;
	let clearNum = 1;//点击了清除搜索就+1
	let global_thread_uuid = '';//恢复搜索线程uuid
	let req_num = 1000;//恢复搜索，一次请求的搜索结果条数
	let oldSetting = {};
	let oldTreeNode;//用于保存搜索前的树
	let APPLIANCE_AGENCY_HAS_CONFIGED = false; // 传输代理是否已配置标记
	let storage_type; //恢复源存储类型
	var _daterangepicker_starttime = "";
	var _daterangepicker_endtime = "";
	// 从备份数据跳转恢复页面
	const externalPointUuid = $('#externalPointUuid').val();
	const externalTaskUuid = $('#externalTaskUuid').val();
	const externalItemUuid = $('#externalItemUuid').val();

	//<----------------------------- BEGIN STEP ONE BACKUP POINT ------------------------------------>

	const findParent = function(treeObj,node){
		timepointTree.expandNode(node,true,false,false);
		if(!node.children){
			nodeParamList.push(node);
			timepointTree.expandNode(node,false,false,false);
		}
		let pNode = node.getParentNode();
		if(pNode != null){
			nodeParamList.push(pNode);
			findParent(timepointTree, pNode);
		}
   	}

	/**
	 * 备份文件树 - 渲染子节点树
	 * @param {*} treeId 
	 * @param {*} treeNode 
	 * @param {*} expendFlag 
	 */
   	const setBakSonTree = function(treeId,treeNode,expendFlag) {
		let params = {
			pid: treeNode.path,
			timepoint_uuid: treeNode.pointuuid, 
			root_flag: 0, 
			start: 0, 
			number: _pageSize, 
			path: treeNode.path, 
			md5_flag: treeNode.md5Flag, 
			md5_high: treeNode.md5High, 
			md5_low: treeNode.md5Low, 
			sclass: _sclass,
			subFlag: true
		};

		params = JSON.stringify(params);

		let div = "#recoverNasTree";
		Metronic.blockUI({target: div,animate: true});
		$.ajax({
			type: "post",
			url: CONF.AJAXPATH,
			async:true,
			data:{m:CONF.M.NAS,f:'getRecoveryNasDir',p:params},
			success: function(data){
				Metronic.unblockUI(div);
				result = JSON.parse(data);
				if(result.re){
					//success
					$.fn.zTree.getZTreeObj(treeId).removeChildNodes(treeNode);
					$.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, result.filelist, true);
					$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
					if(expendFlag == true){
						$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true, true, true);
					}
					if(treeNode.checked && treeNode.children) {
						treeNode.children.forEach(item=>{
							$.fn.zTree.getZTreeObj(treeId).checkNode(item,true);
						});
					}
				}else{
					OPREL(data);
				}
			}
		});
	}

	/**
	 * 备份文件树 - 获取更多
	 * @param {*} treeId 
	 * @param {*} pNode 
	 */
	const getMoreTree = function(treeId, pNode){
		// 加载更多
		//判断父节点下的子节点是否全选
		let checkeFlag = pNode.getParentNode().check_Child_State==2 ? true : false;
		let params = {
			pid: pNode.pId,
			timepoint_uuid: pNode.pointuuid, 
			root_flag: 0, 
			start: pNode.next_index, 
			number: _pageSize,
			path: pNode.pId, 
			md5_flag: pNode.md5Flag, 
			md5_high: pNode.md5High, 
			md5_low: pNode.md5Low, 
			sclass: _sclass,
			subFlag: true
		};

		params = JSON.stringify(params);
		Metronic.blockUI({target: '#recoverNasTree',animate: true});
		$.ajax({
			type: "post",
			url: CONF.AJAXPATH,
			async:true,
			data:{m:CONF.M.NAS,f:'getRecoveryNasDir',p:params},
			success: function(data){
				Metronic.unblockUI('#recoverNasTree');
				result = JSON.parse(data);
				if(result.re){
					//success
					if(checkeFlag) {
						for(let i=0;i<result['filelist'].length;i++) {
							result['filelist'][i].checked = true;
						}
					}
					$.fn.zTree.getZTreeObj(treeId).removeNode(pNode);
					$.fn.zTree.getZTreeObj(treeId).addNodes(pNode.getParentNode(), result['filelist'], true);
					$.fn.zTree.getZTreeObj(treeId).expandNode(pNode, true);
				}else{
					OPREL(data);
				}
			}
		});
	
	}

	/**
	 * 备份文件树 - 节点expand
	 * @param {*} treeId 
	 * @param {*} treeNode 
	 * @returns 
	 */
   	const fileNodeExpand = function(treeId, treeNode){
		if(treeNode.children) return true;
		setBakSonTree(treeId,treeNode,false);
	}

	/**
	 * 备份文件树 - 节点clck
	 * @param {*} treeId 
	 * @param {*} treeNode 
	 * @returns 
	 */
   	const fileNodeClick = function (treeId,treeNode) {
		if(treeNode.more) {//加载更多
			getMoreTree(treeId, treeNode);
			return;
		}
		if(treeNode.isParent) {
			fileNodeExpand(treeId,treeNode);
			zTreeFile.expandNode(treeNode, true);
		}
	}

	/**
	 * 渲染备份文件树
	 * @param {*} d 
	 * @param {*} treeNode 
	 * @returns 
	 */
   	const setBackupFileTree = function(d,treeNode){
		let arr = JSON.parse(d);

		if(!arr['re']){
			Metronic.unblockUI("#recoverNasTree");
			//如果获取消息失败,返回错误提示
			return OPREL(d);
		}

		//第一次请求目录时加一层父节点 可支持全选
		arr.filelist.push({
			"pId": 0,
			"name": treeNode.agent_ip + '('+ treeNode.agent_name +')',
			"title": treeNode.agent_ip + '('+ treeNode.agent_name +')',
			"isParent": true,
			"open": true,
			"icon": treeNode.ostype == 6 ? "./img/nas/nfs.png" : "./img/nas/cifs.png",
			"type": "0",
		});

		if(arr['filelist'][0].path == "/") {//整个共享目录，显示共享目录名
			arr['filelist'][0].name = treeNode.agent_name;
		}
		
		let setting = {
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
				beforeClick: fileNodeClick,
				// onCheck: fileNodeCheck,
				beforeExpand: fileNodeExpand
			},
			view: {
				dblClickExpand: false,
			}
		};
		zTreeFile = $.fn.zTree.init($("#recoverNasTree"), setting, arr['filelist']);
		$('#recoverNasTree').show();
		$('.reco-search-group').show();
		oldTreeNode = arr['filelist'];
		oldSetting = setting;
	}

   	/**
	* 获取恢复文件列表
	* @param {*} treeNode 
	*/
   	const getRecoverDir = function (treeNode) {
		initHostTree('',treeNode.point_uuid);// 初始化nas设备树
		$('#selectPath').hide();//初始化设备树之后隐藏选择路径
		ajaxData.pointInfo = {
            agentUUID: treeNode.agent_uuid,
            pointUUID: treeNode.point_uuid,
            taskUUID: treeNode.taskuuid,
            nodeuuid: treeNode.nodeuuid
        }
		
		//用于最后一步显示用
		_nasName = treeNode.nasname;
		_pointTaskName = treeNode.task_name;
		_pointName = treeNode.name;
		_fileList = [];

		//		1.初始展示[timepoint_uuid, 1, 0, N, '', 0, '', '']
		// subFlag php那边是否截取路径字符串  初始不截取  第二次以及以后截取
		let params = {
			pid: 0,
			timepoint_uuid: treeNode.point_uuid, 
			root_flag: 1, 
			start: 0,
			number: _pageSize,
			path: '/', 
			md5_flag: 0,
			md5_high: '', 
			md5_low: '', 
			sclass: _sclass,
			subFlag: false
		};
		params = JSON.stringify(params);

		let div = "#recoverNasTree";
		Metronic.blockUI({target: div,animate: true});
		$.post(CONF.AJAXPATH, {m:CONF.M.NAS,f:'getRecoveryNasDir',p:params}, function(data){
			$('#step1tips').hide();
			$('#backupfilediv').show();
			$('#filelistdiv').show();

			setBackupFileTree(data,treeNode);
		});
		timepointTree.expandNode(treeNode, true);
	}

//找到一条链所有的时间点，把密码也对应存进去
const getAllPoints = function (treeNode,password) {
	let info = {};
	info.allPoints = [];
	info.pointPasswords = [];
	if(treeNode.type == 4) {//增量差异点
		let pNode = treeNode.getParentNode();
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

	/**
	 * 时间点onCheck
	 * @param {*} e 
	 * @param {*} treeId 
	 * @param {*} treeNode 
	 */
   	const timepointOnCheck = function(e, treeId, treeNode) {
		if(treeNode.chkDisabled){
			return;
		}
		storage_type = treeNode.storage_type;
		// 单选
		$('#recoverNasTree').hide();
		$('.reco-search-group').hide();
		if(zTreeFile != undefined) {
			zTreeFile.checkAllNodes(false);
		}
		let allNodes = timepointTree.getCheckedNodes(true);
		for(let i = 0; i < allNodes.length; i++){
			timepointTree.checkNode(allNodes[i], false, false, false);
		}
		timepointTree.checkNode(treeNode, true, false, false);
		// 切换时间点，清空筛选停止搜索
		clearSearchVal();
		global_thread_uuid = '';
		//数据加密开  自动生成密码关
		if('encrypted_flag' in treeNode && treeNode.encrypted_flag && 'password_auto_flag' in treeNode && !treeNode.password_auto_flag) {
			if($.inArray(treeNode.point_uuid,allpointlist)==-1) {//判断是否和之前点击过的是一条链的
				bootbox.prompt({
					title: LANG.UI_VM_INPUT_DB_ENCRY_PWD,
					inputType: 'password',
					callback: debounce(function (pwdResult) {
						//同步ajax校验密码
						if(pwdResult == null) return;
						let checkflag = true;
						let params = {};
						params.timepointuuid = treeNode.timepointuuid;
						params.inputpass =  btoa($.trim(pwdResult));

							let p = JSON.stringify(params);
						$.ajax({
							type: "post",
							url: CONF.AJAXPATH,
							async: false,
							data:{m:CONF.M.FILE,f:'checkFSEncryptPass',p:p},
							success: function(d){
								let result = JSON.parse(d);
								if(result.flag){
									//密码输入正确
									ajaxData.recoverInfo.password = btoa($.trim(pwdResult));
									var pointInfo = getAllPoints(treeNode,ajaxData.recoverInfo.password);
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
							return true;
					}, 300),
				});
			}else {
				allpointPsw.forEach(item => {
					if(treeNode.timepointuuid == item.point_uuid) {
						ajaxData.recoverInfo.password = item.password;
					}
				});
				getRecoverDir(treeNode);
			}

		}else {
			ajaxData.recoverInfo.password = '';
			getRecoverDir(treeNode);
		}
	}

	/**
	 * 异步获取文件备份数据  refreshFlag是否重新刷新
	 * @param {*} treeId 
	 * @param {*} treeNode 
	 * @param {*} refreshFlag 
	 * @param {*} expendFlag 
	 */
	const getSyncVcenterInfo = function(treeId, treeNode, refreshFlag, expendFlag, chooseFlag = false){
		let storage_uuid = $('#storageSelect').val();
		let data = JSON.stringify({
			taskuuid: treeNode.taskuuid, 
			id: treeNode.id,
			refresh: refreshFlag,
			storage_uuid: storage_uuid,
			agentuuid: treeNode.agentuuid,
			recoverflag: false
		});

		let div = ".nas_tree";
		Metronic.blockUI({target: div,animate: true});
		$.ajax({
			type: "post",
	        url: CONF.AJAXPATH,
	        async:true,
	        data:{m:CONF.M.NAS,f:'getSyncNasTimepoint',p:data},
	        success: function(data){
	        	Metronic.unblockUI(div);
	        	result = JSON.parse(data);
	        	if(result.re){
	        		//success
	        		$.fn.zTree.getZTreeObj(treeId).removeChildNodes(treeNode);
	        		$.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, result.msg, true);
	        		$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
	        		if(expendFlag == true){
	        			$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true, true, true);
	        		}
					// 从备份数据跳转恢复页面，匹配时间点并勾选
					let targetNode = $.fn.zTree.getZTreeObj(treeId).getNodeByParam('id', externalPointUuid);
					if(targetNode && chooseFlag){
						// 时间点备份的展开文件列表
						timepointOnCheck('', treeId, targetNode);
					}
	        	}else{
	        		OPREL(data);
	        	}
	        }
		});
	}

	/**
	 * 节点展开异步添加时间点
	 * @param {*} treeId 
	 * @param {*} treeNode 
	 * @returns 
	 */
	const nodeExpand = function(treeId, treeNode){
		if(treeNode.clickshow){
			if(treeNode.children) return true;
			getSyncVcenterInfo(treeId, treeNode, false, false);
		}else{
			return true;
		}

	}

	const nodeSelect = function(treeId, treeNode, clickFlag){
		if(3 != treeNode.type && 4 != treeNode.type){
			timepointTree.expandNode(treeNode, true);
			//异步加载时间点
			nodeExpand(treeId, treeNode);
			return;
		}
		timepointOnCheck(event, treeId, treeNode);
	}

	/**
	 * 渲染时间点树
	 * @param {*} zNodes 
	 * @returns 
	 */
   	const setPointTree = function(zNodes){
		if(zNodes == "[]"){
			$("#nopointtips").show();
			$("#nas_point_tree").hide();
			return;
		}else{
			$("#nopointtips").hide();
			$("#nas_point_tree").show();
		}
		let setting = {
				check: {
					enable: true,
				},
				data: {
					simpleData: {
						enable: true
					},
					key:{
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
						fontCss: function(treeId, treeNode) {
                    	let css = {};
		            	if (treeNode.point_status == 1) {
		            		css = { color: "#F19F00 " };
		            	}
		            	if (treeNode.point_status == 3) {
		            		css = { color: "#F1416C " };
		            	}
		            	return css;
                	},
					addHoverDom: addPointTreeHoverDom,
					removeHoverDom: removePointTreeHoverDom,
				},
			};
		timepointTree = $.fn.zTree.init($("#nas_point_tree"), setting, JSON.parse(zNodes));
		// 从备份数据跳转恢复页面，展开对象下的时间点
		let targetNode = timepointTree.getNodeByParam('id', externalItemUuid + '_' + externalTaskUuid);
		// 第一次初始化，且有目标节点
		if(targetNode && !initTreeFlag){
			// 异步获取时间点
			getSyncVcenterInfo('nas_point_tree',targetNode, true, true, true)
		}
		initTreeFlag = true;
	};

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


	/**
	 * 初始化时间点树
	 * @param {*} type 
	 */
	const initPointTree = function(type) {
		let data = {};
		data.type = type;
		data.storage_uuid = $('#storageSelect').val();
		data = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.NAS,f:'getNasDataTree',p:data}, setPointTree);
	};

	/**
	 * 获取恢复的节点列表
	 */
	var initStorageSelect = function(){
		pAjaxRequest({}, '/api/v1/storages/type', "GET", (result) => {
			if (result.success) {
				let data = result.data;
				let storageSelect = $('#storageSelect')
				storageSelect.empty();
				for (let i = 0; i < data.length; i++) {
					let option = $("<option>").text(data[i].text).val(data[i].storageid);
					storageSelect.append(option);
				}
			} else {
				UIToastr.showWarning(LANG.UI_OBS_GET_STORAGE_FAILED, res.message);
			}
		})
		//绑定事件
		$('#storageSelect').on('change', storageSelectChange);
	}

	const storageSelectChange = function(){
		initTreeFlag = false;
		$('#recoverNasTree').hide();
		initPointTree();
		if (zTreeFile) {
			zTreeFile.checkAllNodes(false);
		}
	}

	/**
	 * 搜索备份时间点
	 */
	const searchFS = function(){
		searchTimeRange();
    }

	/**
	 * 文件恢复源 - 处理数据
	 * @param {*} startIndex 
	 * @param {*} count 
	 * @param {*} treeNode 
	 * @param {*} checkflag 
	 */
	const handleData  =  function(startIndex, count,treeNode,checkflag) {
		// 截取需要处理的数据
		for (let i = 0; i < treeNode.slice(startIndex, startIndex + count).length; i++) {
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

	/**
	 * 文件恢复源 - 全选
	 * @returns 
	 */
	const selectAllResult = function() {
		let allNodes = zTreeFile.transformToArray(zTreeFile.getNodes());
		if(allNodes.length==0) {
			return;
		}
		Metronic.blockUI({target: ".addIt-list",animate: true});
		handleData(0, getSelectNum(allNodes),allNodes,true);
	}

	/**
	 * 文件恢复源 - 获取搜索结果
	 * @param {*} offset 
	 * @param {*} thread_uuid 
	 * @param {*} stopNum 
	 */
	const getSearchInfo = function (offset,thread_uuid,stopNum) {
		let params = {info:{}};
		params.search_timepoint = timepointTree.getCheckedNodes(true)[0].timepointuuid;;
		params.info.task_uuid = thread_uuid;
		params.info.number = req_num;
		params.info.offset = offset;
		params = JSON.stringify(params);
		$.post(CONF.AJAXPATH, {m:CONF.M.FILE,f:'getRecoSearchInfo',p:params}, function(d){
			let data = JSON.parse(d);
			if(clearNum != stopNum) return;
			if(data.re) {//未点击清除或停止
				current_total_num += parseInt(data.current_total_num);
				current_dir_num += parseInt(data.current_dir_num);
				current_file_num += parseInt(data.current_file_num);
				if(data.search_finish_flag == 2) {//未结束
					$("#recoverNasTree").show();
					$("#seachdes .countnum").html(LANG.UI_FILE_FOUND + current_total_num + LANG.UI_FILE_SEARCH_FILE_NUM + current_file_num +LANG.UI_FILE_SEARCH_FLODER_NUM + current_dir_num + LANG.UI_PUBLIC_STRIP);
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
					// Metronic.unblockUI('#recoverNasTree');
					if(current_total_num == 0) {
						$("#seachdes .countnum").html(LANG.UI_FILE_SEARCH_NO_DATA);
					}else {
						$("#recoverNasTree").show();
					}
				}
			}else {
				//获取搜索结果失败，自动变成未搜索前的状态并报错
				$('.ispinner').css("display","none");
				$('#reco-search-input').prop("disabled",false);
				$('#reco-search-btn').html(LANG.BILLING_SEARCH).css({"background-color":"#47b5a0","color": "white"});
				$('#reco-search-btn').attr("name","start");
				OPREL(d);
			}
		});
	}

	/**
	 * 文件恢复源 - 开始搜索
	 * @returns 
	 */
	const startSearch = function () {
		let inputname = $('#reco-search-btn').attr("name");
		if(inputname == "start") {
			let searchval = $.trim($("#reco-search-input").val());
			if(searchval == "") return;
			// 动态设置ztree的高度
			$('.addIt-list .ztree').css('height', 'calc(100% - 60px)')
			//先创建搜索消息，创建成功发送获取搜索结果的消息
			let search_timepoint = timepointTree.getCheckedNodes(true)[0].timepointuuid;
			let md5_list = [];
			let arr = [];
			let search_mode = 1;//1全文搜索  2选中部分目录目录搜索
			if(zTreeFile.getCheckedNodes(true).length != 0) {
				arr = zTreeFile.getCheckedNodes(true)
			} else {
				arr = zTreeFile.getNodes();
			}
			arr.forEach(item => {
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
			for(let i = 0; i < md5_list.length; i++) {
				if(md5_list[i+1] && md5_list[i+1].filepath.indexOf(md5_list[i].filepath) !=-1) {
					md5_list.splice(i+1,1)
					i--;
				}
			}
			//删除filepath,只保留MD5
			md5_list.map(item=>delete item.filepath);
			if (md5_list.length != 0) {
				search_mode = 2;
			}
			// 各种显示样式
			$('#reco-search-input').prop("disabled",true);//开始搜索禁用输入框
			$('#reco-search-btn').attr("name","stop").attr("disabled",true);
			$('#reco-search-btn').html(LANG.UI_JOB_STOP).css({"background-color":"red","color": "white"});
			$('.ispinner').css("display","inline-block");
			$("#seachdes").show();
			$("#seachdes .countnum").html(LANG.UI_FILE_SEARCH_INIT_TIPS);

			$("#recoverNasTree").hide();
			zTreeFile.checkAllNodes(false);//取消全部勾选
			srcAllNode = [];
			srcAllNode = zTreeFile.transformToArray(zTreeFile.getNodes());
			srcAllNode.map(item=> {
				zTreeFile.removeNode(item);
			});
			let stopNum = clearNum;//每次点击开始重新赋值，保证这一次能走下去
			// 参数
			let params = {info:{}};
			params.info.search_mode = search_mode;
			params.info.md5_list = md5_list;
			params.info.path_name = searchval;
			params.info.timepoint_uuid = search_timepoint;
			params.info.req_total_num = 5000;//总共请求多少条数据,0不限制
			params.info.module_type = 11;//文件模块类型
			params = JSON.stringify(params);
			$.post(CONF.AJAXPATH, {m:CONF.M.FILE,f:'createSearchJob',p:params}, function(d){
				let data = JSON.parse(d);
				if(clearNum != stopNum) return;//让此次操作不能继续进行
				if(data.re) {
					//如果在搜索，停止界面超时检测
					// UIIdleTimeout.stop();
					current_total_num = 0;
					current_dir_num = 0;
					current_file_num = 0;
					getSearchInfo(0,data.thread_uuid,stopNum)
					global_thread_uuid = data.thread_uuid;
					$('#reco-search-btn').attr("disabled",false);
					// Metronic.blockUI({target: '#recoverNasTree',animate: true});
				} else {
					//获取搜索结果失败，自动变成未搜索前的状态并报错
					$('.ispinner').css("display","none");
					$('#reco-search-input').prop("disabled",false);
					$('#reco-search-btn').html(LANG.BILLING_SEARCH).css({"background-color":"#47b5a0","color": "white"});
					$('#reco-search-btn').attr("name","start");
					OPREL(d);
				}
			});
		} else if(inputname == "stop") {
			stopSearch();
		}
	}

	/**
	 * 文件恢复源 - 停止搜索
	 * @returns 
	 */
	const stopSearch = function () {
		//停止搜索，开启界面超时检测
		// UIIdleTimeout.start();
		$('.ispinner').css("display","none");
		$('#reco-search-btn').html(LANG.BILLING_SEARCH).css({"background-color":"#47b5a0","color": "white"});
		$('#reco-search-btn').attr("name","start");
		$('#reco-search-input').prop("disabled",false);
		// Metronic.unblockUI('#recoverNasTree');
		clearNum ++;//点击停止，+1,让此次操作不能继续进行
		//搜索任务创建成功了才发停止的消息给后台
		if(global_thread_uuid == '')return;
		let params = {info:{}};
		params.search_timepoint = timepointTree.getCheckedNodes(true)[0].timepointuuid;;
		params.info.task_uuid = global_thread_uuid;
		params = JSON.stringify(params);
		$.post(CONF.AJAXPATH, {m:CONF.M.FILE,f:'stopSearchJob',p:params}, function(d){
			let data = JSON.parse(d);
			if(data.re) {
				return;
			}
		});
	}

	/**
	 * 判断一次勾选的条数
	 * @param {*} allNodes 
	 * @returns 
	 */
	const getSelectNum = function(allNodes) {
		let num = 0;
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

	/**
	 * 文件恢复源 - 取消选择
	 * @returns 
	 */
	const cancelSelect = function () {
		Metronic.blockUI({target: ".addIt-list",animate: true});
		let allNodes = zTreeFile.transformToArray(zTreeFile.getCheckedNodes(true));
		if(allNodes.length==0){
			Metronic.unblockUI(".addIt-list");
			return;
		}
		handleData(0, getSelectNum(allNodes),allNodes,false);
	}

	/**
	 * 文件恢复源 - 清空搜索
	 */
	const clearSearchVal = function() {
		$('.addIt-list .ztree').css('height', 'calc(100% - 34px)');
		$("#recoverNasTree").show();
		zTreeFile = $.fn.zTree.init($("#recoverNasTree"), oldSetting, oldTreeNode);
		$('#reco-search-input').val("");
		$("#seachdes").hide();
		$('#reco-search-btn').html(LANG.BILLING_SEARCH).css({"background-color":"#47b5a0","color": "white"});
		$('#reco-search-btn').attr("name","start");
		$('#reco-search-input').prop("disabled",false);
		stopSearch();
	}

	/**
	 * 步骤一校验通过 - 接口参数赋值 & 确认表单配置
	 * @returns 
	 */
	const showStep1 = function(){
		// 设置任务名
		if($.trim($('#jobname').val()) == "") {
			$.post(CONF.AJAXPATH, {m:CONF.M.NAS,f:'getNasRecoverTaskName',p:{}}, function(data){
				$('#jobname').val(data);
			});
		}

		// 设置时间点信息
		$('.srcagentinfo').html(_nasName);
		$('.srctaskname').html(_pointTaskName);
		$('.srctimepoint').html(_pointName);

		//设置文件列表
		let showStr = '';
		let allCheckedNode = [];

		// 得到所有勾选状态是全选中的节点
		for(let i=0; i<ajaxData.pointInfo.fileInfo.length; i++){
			//check_Child_State == -1不存在子节点（文件或者全选的无子节点的文件夹）  2是所有子节点被勾选
			if(ajaxData.pointInfo.fileInfo[i].type != 0 && ajaxData.pointInfo.fileInfo[i].check_Child_State == 2 || ajaxData.pointInfo.fileInfo[i].check_Child_State == -1) {
				allCheckedNode.push(ajaxData.pointInfo.fileInfo[i]);
			}
		}

		// 过滤掉重复的
		for(let m = 0;m<allCheckedNode.length;m++) {
			if(allCheckedNode[m].btype != 1) {//磁盘或文件夹
				for(let n = 0;n<allCheckedNode.length;n++) {
					let str = allCheckedNode[n].pId==null ?'':allCheckedNode[n].pId;
					if(str.includes(allCheckedNode[m].path) && allCheckedNode.indexOf(allCheckedNode[n])!=-1) { // 判断全选的文件夹下是否还有文件  有则从数组中删除
						allCheckedNode.splice(allCheckedNode.indexOf(allCheckedNode[n]),1);
						n--;
					}
				}
			}
		}

		// 设置文件显示列表和提交的列表
		ajaxData.pointInfo.fileInfo = [];
		allCheckedNode.forEach(item=> {
			ajaxData.pointInfo.fileInfo.push([item.btype,item.path,item.name,item.md5High,item.md5Low]); // 组装发送给后台的文件信息
			showStr += item.path + '<br>';
		})

		// 判断恢复选择的节点是否是搜索出来的
		if(allCheckedNode[0].searchNode != undefined) {
			ajaxData.distinct_flag = 1;
		}else {
			ajaxData.distinct_flag = 2;
		}

		$('#filelist').html(showStr);
		if (storage_type == 10) {
			//磁带只支持单线程，不显示线程配置
			$('#recoveryThreadDiv').spinner("value", 1);
			//磁带屏蔽安全策略
			$('.safeLi').removeClass('active').hide();
            $('#tab_safety').removeClass('active');
			$('.safeDiv').hide();
		} else {
			$('#recoveryThreadDiv').spinner("value", 3);
			$('.safeLi').show();
			$('.safeDiv').show();
		}
		if (!CONF.FUNCTIONS.includes('integrity')) {
            $('.safeLi').hide();
			$('.safeDiv').hide();
        }
	}

	/**
	 * 步骤一 - 备份时间点校验
	 * @returns 
	 */
	const step1Valid = function(){
		//检查时间点
		if(timepointTree.getCheckedNodes(true).length == 0){
			UIToastr.showWarning(LANG.UI_RECOVERY_FILE_NO_TIMEPOINT_TITLE, LANG.UI_RECOVERY_FILE_NO_TIMEPOINT_VALUE);
			return false;
		}

		timepointNode = ajaxData.pointInfo.nodeuuid;
		//检查恢复文件列表
		ajaxData.pointInfo.fileInfo = zTreeFile.getCheckedNodes(true);
		if(!ajaxData.pointInfo.fileInfo.length){
			UIToastr.showWarning(LANG.UI_RECOVERY_FILE_NO_REC_FILE_TITLE, LANG.UI_RECOVERY_FILE_NO_REC_FILE_VALUE);
			return false;
		}

		stopSearch();//点击下一步停止搜索
		showStep1();

		return true;
	}

	//<----------------------------- END STEP ONE BACKUP POINT -------------------------------------->




	//<----------------------------- BEGIN STEP TWO RECOVER TARGET ---------------------------------->

	/**
	 * 步骤二：恢复目标 - 初始化 文件客户端/NAS设备/对象存储/Hadoop 树
	 * @param {*} zNodes 
	 */
	const setHostTree = function(zNodes){
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
	};

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
    const initNasClientTree = (keyword = '', timepointUUID) => {
        let params = {
            keyword: keyword,
            timepoint_uuid: timepointUUID,
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
                    $('#noagenttips p').html(LANG.UI_OBS_NO_USEFULL_HADOOP);
                } else {
                    $('#noagenttips').hide();
                    $('#host_tree').show();

                    // 初始化Hadoop集群树
                    setHostTree(result.data);
                }
            } else {
                $('#noagenttips').show();
                $('#host_tree').hide();
                $('#noagenttips p').html(LANG.UI_OBS_NO_USEFULL_HADOOP);

                UIToastr.showWarning(LANG.UI_OBS_GET_HADOOP_CLUSTER_FAILED, result.message);
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

	/**
	 * 初始化客户端tree
	 * @param {*} keyword 
	 */
	const initHostTree = function(keyword,pointUUID = ''){
		let deviceClient = parseInt($("#deviceSelect").val()); // 存储设备
		switch (deviceClient) {
            case STORAGE_DEVICE_TYPE.FILE: // 文件客户端
                initFileClientTree(keyword);
                break;
            case STORAGE_DEVICE_TYPE.NAS: // NAS设备
                initNasClientTree(keyword, pointUUID);
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

	const debounceFS = function () {
		clearTimeout(timer)
		timer = setTimeout(function () {
			let params = $('#searchHost').val();
			initHostTree(params, ajaxData.pointInfo.pointUUID);
		}, 500)
	}

	let className = "dark";
	const beforeEditName = function(treeId, treeNode) {
		className = (className === "dark" ? "":"dark");
		pathTree.selectNode(treeNode);
		pathTree.editName(treeNode);
		return false;
	}

	const addHoverDom = function(treeId, treeNode) {
		let sObj = $("#" + treeNode.tId + "_span");

		if (treeNode.more || treeNode.editNameFlag || $("#addBtn_"+treeNode.tId).length > 0) return;

		if(!filenameFlag) return;

		let addStr = '<span class="button add" id="addBtn_'+ treeNode.tId + '" title="'+ LANG.UI_FILE_NEW_DIR +'" onfocus="this.blur();"></span>';

		sObj.after(addStr);

		let btn = $("#addBtn_"+treeNode.tId);

		// 点击添加节点
		if (btn) btn.bind("click", function(){
			if(treeNode.noRemoveBtn) {//不是新增的节点
				pathNodeExpand(treeId, treeNode);
			}

			if(treeNode.children != undefined && treeNode.children.length != 0) {
				pathTree.addNodes(
					treeNode, 
					-1, 
					{
						id: (treeNode.id + '_' + treeNode.children.length),
						icon: './img/fs/wenjianjia.png',
						iconOpen: "./img/fs/wenjianjiaopen.png", 
						iconClose: "./img/fs/wenjianjia.png",
						title: '', 
						isParent:false, 
						pId: treeNode.id,
						name: 'recovery-destination' + (newCount++),
						isnew: true,
						new_dir_create: CONF.FLAG.SET,
						code_type: treeNode.code_type
					},
					false);
				// let newnode = pathTree.getNodeByParam("id", treeNode.id+'_' + 0, null);
				let newnode = pathTree.getNodeByParam("isnew", true, null);
				beforeEditName(treeId,newnode);
				newnode.isnew = false;
			}else {
				pathTree.addNodes(
					treeNode, 
					-1, 
					{
						id: (treeNode.id + '_' + 0),
						icon: './img/fs/wenjianjia.png',
						iconOpen: "./img/fs/wenjianjiaopen.png", 
						iconClose: "./img/fs/wenjianjia.png", 
						title: '', 
						isParent: false, 
						pId: treeNode.id, 
						name: 'recovery-destination' + (newCount++),
						isnew: true,
						new_dir_create: CONF.FLAG.SET,
						code_type: treeNode.code_type
					},
					false);
				let newnode = pathTree.getNodeByParam("isnew", true, null);
				beforeEditName(treeId,newnode);
				newnode.isnew = false;
			}
			filenameFlag = false;
			return false;
		});
	};

	const removeHoverDom = function(treeId, treeNode) {
		$("#addBtn_"+treeNode.tId).unbind().remove();
	};

	const onRename = function(event, treeId, treeNode, isCancel) {
		//选中新增的节点
		let allNodes = pathTree.getCheckedNodes(true);

		for(let i = 0; i < allNodes.length; i++){
			pathTree.checkNode(allNodes[i], false, false, false);
		}

		pathTree.checkNode(treeNode, true, false, false);

		//给新增的节点设置title
		let pnode = treeNode.getParentNode();
		//去掉sharepath最前面的斜杠
		let newpath = share_path.substring(1, share_path.length);
		let parentPath = pnode.dir_path.replace(newpath, '');
		treeNode.title = treeNode.name;
		treeNode.dir_path = parentPath + treeNode.name + '/';

		let str = $.trim(treeNode.name);
		if(str.length==0) {
			UIToastr.showInfo(LANG.UI_NAS_RECOVERY_FILENAME_NO_NULL);
			filenameFlag = false;
			pathTree.editName(treeNode);
			return;
		}else {
			filenameFlag = true;
		}
		let nodes = hostTree.getCheckedNodes();
		let specialchar = ['/', ':','"','<','>','|','\\','?','*','&'];
		let des = LANG.UI_FILE_FILENAME_NO_CONTAIN + ' &';
		if(nodes[0].os_type == 'Windows')  {//linux不判断文件夹名称
			//文件夹名称不能包含特殊符号,不包含  /  :  "  <  >  | \
			specialchar = ['/', ':','"','<','>','|','\\','?','*'];
			des = LANG.UI_FILE_FILENAME_NO_CONTAIN;
		}
		for (let key in specialchar) {
			if (str.indexOf(specialchar[key]) != -1) {
				UIToastr.showInfo(des);
				filenameFlag = false;
				pathTree.editName(treeNode);
				return;
			}else {
				filenameFlag = true;
			}
		}
	}
	
	const onRemove = function(event, treeId, treeNode){
		//获取删除节点的父节点
		let parentNode = treeNode.getParentNode();
		//如果父节点下的子节点不存在
		if(parentNode.children.length == 0) {
			//修改父节点图标为文件夹样式
			parentNode.isParent = true;
			// parentNode.open = true;
			pathTree.updateNode(parentNode);
		}

	}
	
	const showRenameBtn = function(treeId, treeNode){
	    //获取节点所配置的noEditBtn属性值
	    if(treeNode.noEditBtn != undefined && treeNode.noEditBtn){
	        return false;
	    }else{
	        return true;
	    }
	}
	
	const showRemoveBtn = function(treeId, treeNode){
	    //获取节点所配置的noRemoveBtn属性值
	    if(treeNode.noRemoveBtn != undefined && treeNode.noRemoveBtn){
	        return false;
	    }else{
	        return true;
	    }
	}

	/**
	 * 恢复路径树节点 - select
	 * @param {*} treeId 
	 * @param {*} treeNode 
	 * @param {*} clickFlag 
	 * @returns 
	 */
	const pathNodeSelect = function(treeId, treeNode, clickFlag){
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
                    UIToastr.showWarning(LANG.UI_HADOOP_RECOVERY_LOAD_MORE_FAILED, '');
                }

                Metronic.unblockUI('#path_tree');
            })
		} else {
			pathTree.checkNode(treeNode, !treeNode.checked, false, true);
		}
	}

	/**
	 * 恢复路径树节点 - onCheck
	 * @param {*} e 
	 * @param {*} id 
	 * @param {*} node 
	 */
	const pathOnCheck = function(e, id, node){
		//单选
		let allNodes = pathTree.getCheckedNodes(true);
		for(let i = 0; i < allNodes.length; i++){
			pathTree.checkNode(allNodes[i], false, false, false);
		}
		pathTree.checkNode(node, true, false, false);
	}

	/**
	 * 恢复路径树expand
	 * @param {*} treeId 
	 * @param {*} treeNode 
	 * @returns 
	 */
	const pathNodeExpand = function(treeId, treeNode){
		if(treeNode.children) return true;

		let agentuuid = treeNode.event_type === 'hadoop_cluster' ? treeNode.clusteruuid : treeNode.uuid;
        if (!agentuuid)	return false;
		
		let dir = treeNode.dir_path;

        if (treeNode.title === "/" && treeNode.event_type === undefined) { // nas新增的根目录
            dir = treeNode.id;
        }

		let params = {};
        let api = '';

		switch (treeNode.event_type) {
            case 'agent':
                params = {
                    agent_uuid: agentuuid,
                    start: 0,
                    limit: _pageSize,
                    filename: '',
                    dir: dir,
                    pid: treeNode.id,
                    code_type: treeNode.code_type,
                    start_after: ''
                }
                api = '/api/v1/files/recover_path_tree';
                break;
            case 'nas':
                params = {
                    agent_uuid: agentuuid,
                    start: 0,
                    limit: _pageSize,
                    filename: '',
                    dir: dir,
                    pid: treeNode.id,
                    code_type: treeNode.code_type,
                    start_after: ''
                }
                api = '/api/v1/nas/recover_path_tree';
                break;
            case 'obs_agent':
                params = {
                    agent_uuid: agentuuid,
                    start: 0,
                    limit: _pageSize,
                    filename: '',
                    dir: dir,
                    pid: treeNode.id,
                    code_type: treeNode.code_type,
                    start_after: ''
                }
                api = '/api/v1/s3/recover_path_tree';
                break;
            case 'hadoop_cluster':
                params = {
                    hadoop_cluster_id: agentuuid,
                    limit_count: _pageSize,
                    fetch_root_dir: dir,
                    code_type: treeNode.code_type,
                    start: 0,
                    start_after: '',
                    edit_flag: false
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

	/**
	 * 步骤二：恢复目标 - 恢复路径 - 初始化恢复路径树
	 * @param {*} treeData 
	 */
	const setAgentPathTree = function(treeData){
		newCount = 1;
		
		let setting = {
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
				removeTitle: LANG.UI_PUBLIC_DELETE,
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
                UIToastr.showWarning(LANG.UI_OBS_GET_RECOVER_HADOOP_CLUSTER_DIR_FAILED, result.message);
            }

            Metronic.unblockUI('#path_tree');
        })
    }

	/**
	 * 初始化节点传输网络列表
	 */
	const initNetworkList = function(){
		let data = {};
		data.nodeuuid = timepointNode;
		$('#transferNetworkTree').transferNetwork({node_uuid: data.nodeuuid});
	}

	const getNetworkFlag = function(agent){
		for(let i=0;i<agent.length;i++){
			//客户端连接服务端
			if(agent[i].net_model == 2){
				networkFlag = true;
			}
		}
		return networkFlag;
	}

	/**
	 * 初始化网络传输节点
	 * @param {*} treeNode 
	 */
	const initNetwork = function(treeNode) {
		//是否需要显示传输网络标记
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
	 * 客户端树节点onCheck
	 * @param {*} e 
	 * @param {*} treeId 
	 * @param {*} treeNode 
	 */
	const hostOnCheck = function(e, treeId, treeNode){
		if(treeNode.chkDisabled) return;

		if (treeNode.checked) {
			hostTree.checkAllNodes(false); // 取消所有节点的选中状态
        	hostTree.checkNode(treeNode, true, false, false); // 重新选中被勾选的节点

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
						ajaxData.recoverInfo.cross_platform_transform = 1; // 是否跨平台
                        break;
                    case 'nas': // nas设备
                        initNasFilePathTree(treeNode);
                        ajaxData.recoverInfo.cross_platform_transform = 2; // 是否跨平台
                        break;
                    case 'obs_agent': // 对象存储
                        initObsFilePathTree(treeNode);
                        ajaxData.recoverInfo.cross_platform_transform = 1; // 是否跨平台
                        break;
                    case 'hadoop_cluster': // hadoop
                        initHdoopFilePathTree(treeNode);
                        ajaxData.recoverInfo.cross_platform_transform = 1; // 是否跨平台
                        break;
                    default:
                        break;
                }
			}
		} else {
			$('#selectPath').hide();
            $('#pathtreediv').hide();

			if (treeNode.event_type === 'nas') {
				$('#pathtype option[value=1]').show();
			} else {
				$('#pathtype option[value=1]').hide();
			}
		}

		initNetwork(treeNode);
	}

	/**
	 * 客户端树节点select
	 * @param {*} treeId 
	 * @param {*} treeNode 
	 * @param {*} clickFlag 
	 * @returns 
	 */
	const hostNodeSelect = function(treeId, treeNode, clickFlag){
		if(treeNode.type === 1) {
			hostTree.expandNode(treeNode);
			return;
		}
		
		if(treeNode.chkDisabled) return;

		hostTree.checkNode(treeNode, !treeNode.checked, false, true);
	}

	/**
	 * 步骤二 - 恢复目标校验
	 * @returns 
	 */
	const step2Valid = function(){
		let agentShowInfo = '', pathShowInfo = '';
		let deviceClient = parseInt($("#deviceSelect").val()); // 存储设备

		// 1.校验
		if(!hostTree){
			$('#noagenttips').show();
			return false;
		}

		let nodes = hostTree.getCheckedNodes();
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

		// 2.ajax data赋值
		ajaxData.recoverInfo.agentUUID = nodes[0].uuid;
		ajaxData.recoverInfo.type == 2;//默认2异机恢复
		ajaxData.recoverInfo.pathType = parseInt($('#pathtype').val());
		
		if(nodes[0].event_type === "nas") {
			ajaxData.pointInfo.nasuuid = nodes[0].uuid;
		}

		if (ajaxData.recoverInfo.pathType === 1) { // 恢复到原路径
			ajaxData.recoverInfo.path = '';
			ajaxData.recoverInfo.code_type = 2;
			pathShowInfo = LANG.UI_RECOVERY_FILE_REC_OLD_PATH;
		} else { // 手动选择路径
			let pathNodes = pathTree.getCheckedNodes();

			if (pathNodes.length === 0) {
                UIToastr.showWarning(LANG.UI_RECOVERY_FILE_NO_REC_PATH_TITLE, LANG.UI_RECOVERY_FILE_NO_REC_PATH_VALUE);
                return false;
            }
			var dir_path = pathNodes[0].dir_path;
			if(nodes[0].event_type === "nas") {
                // 去掉sharepath最前面的斜杠
                let newpath = share_path.substring(1, share_path.length);
				dir_path = pathNodes[0].dir_path.replace(newpath, '');
            }

			// 选择nas根目录
            if(pathNodes[0].parentFlag !== undefined) {
                pathNodes[0].dir_path = "/";
            }

			ajaxData.recoverInfo.path = dir_path;
			ajaxData.recoverInfo.new_dir_create = pathNodes[0].new_dir_create;
			ajaxData.recoverInfo.code_type = pathNodes[0].code_type;

			// 如果恢复客户端是对象存储则需要处理 |
            if (deviceClient === STORAGE_DEVICE_TYPE.OBS) {
                pathShowInfo = replaceBetweenStartEnd(dir_path, '|', '/', '').replace('|', '');
            } else {
                pathShowInfo = dir_path;
            }
		}

		// 3.表单确认回显
		$('.transferLi').hide();
		$('.transferDiv').hide();
		switch (deviceClient) {
            case STORAGE_DEVICE_TYPE.FILE: // 恢复到文件
				// 重置传输代理
				$('#appliance_agency_flag').bootstrapSwitch('state', false);

				// 重置传输加密
				$('#transport_encrypt_flag').bootstrapSwitch('state', false);
                
                $('.transfer-agency-form-group').hide(); // 隐藏传输代理
				$('.advanced-config-obs-form-group').hide(); // 隐藏对象存储所属form-group项
				$('.applianceselectdiv').hide(); // 隐藏传输代理选择框
				$('.encrypt-transfer-method-form-group').hide(); // 隐藏传输加密算法选择框

				$('.encrypt-transfer-form-group').show(); // 显示传输加密
                $('.advanced-config-file-form-group').show(); // 显示 文件、NAS和Hadoop所属form-group项
				$('.file-permission-div').hide();//跨平台屏蔽文件权限恢复
				$('.permissionLi').hide(); //隐藏权限侧边栏
				$('.transferLi').show(); // 传输策略 tab
				$('.transferDiv').show();
                break;
            case STORAGE_DEVICE_TYPE.NAS: // 恢复到NAS
				// 重置传输代理
				$('#appliance_agency_flag').bootstrapSwitch('state', false);

				// 重置传输加密
				$('#transport_encrypt_flag').bootstrapSwitch('state', false);
				$('.transfer-agency-form-group').hide(); // 隐藏传输代理
				$('.advanced-config-obs-form-group').hide(); // 隐藏对象存储所属form-group项
				$('.encrypt-transfer-form-group').hide(); // 隐藏传输加密
				$('.applianceselectdiv').hide(); // 隐藏传输代理选择框
				$('.encrypt-transfer-method-form-group').hide(); // 隐藏传输加密算法选择框

                $('.advanced-config-file-form-group').show(); // // 显示 文件、NAS和Hadoop所属form-group项
				if (ajaxData.recoverInfo.pathType === 1) { // 恢复到原路径不显示跳过目录树恢复
					$('.file-skip-dir-tree-div').hide();
				}
				$('.file-permission-div').show();
				$('.permissionLi').show();
				//如果多线程已授权，显示传输策略
				if(CONF.FUNCTIONS.includes('multithread')){
					$('.transferLi').show();
					$('.transferDiv').show();
				}
                break;
            case STORAGE_DEVICE_TYPE.HADOOP: // 恢复到Hadoop
				// 重置传输代理
				$('#appliance_agency_flag').bootstrapSwitch('state', false);

				// 重置传输加密
				$('#transport_encrypt_flag').bootstrapSwitch('state', false);
				$('.encrypt-transfer-form-group').hide(); // 隐藏传输加密
				$('.applianceselectdiv').hide(); // 隐藏传输代理选择框
				$('.advanced-config-obs-form-group').hide(); // 隐藏对象存储所属form-group项
				$('.encrypt-transfer-method-form-group').hide(); // 隐藏传输加密算法选择框

                $('.transfer-agency-form-group').show(); // 显示传输代理
				$('.advanced-config-file-form-group').show(); // 显示 文件、NAS和Hadoop所属form-group项
				$('.file-permission-div').hide();//跨平台屏蔽文件权限恢复
				$('.clear-shortcut-div').hide();//屏蔽无效快捷方式清理
				$('.permissionLi').hide(); //隐藏权限侧边栏
				$('.transferLi').show(); // 传输策略 tab
				$('.transferDiv').show();
                break;
            case STORAGE_DEVICE_TYPE.OBS: // 恢复到对象存储
				// 重置传输代理
				$('#appliance_agency_flag').bootstrapSwitch('state', false);

				// 重置传输加密
				$('#transport_encrypt_flag').bootstrapSwitch('state', false);
                

				$('.encrypt-transfer-form-group').hide(); // 隐藏传输加密
				$('.advanced-config-file-form-group').hide(); // 隐藏 文件、NAS和Hadoop所属form-group项
				$('.applianceselectdiv').hide(); // 隐藏传输代理选择框
				$('.encrypt-transfer-method-form-group').hide(); // 隐藏传输加密算法选择框

                $('.transfer-agency-form-group').show(); // 显示传输代理
                $('.advanced-config-obs-form-group').show(); // 显示对象存储所属form-group项
				$('.file-permission-div').hide();//跨平台屏蔽文件权限恢复
				$('.clear-shortcut-div').hide();//屏蔽无效快捷方式清理
				$('.permissionLi').hide(); //隐藏权限侧边栏
				$('.transferLi').show(); // 传输策略 tab
				$('.transferDiv').show();
                break;
            default:
                break;
        }
		//高级策略选中第一个li 和 tab-pane
		$('#tab_other .nav-tabs').find('li').removeClass('active');
		$('#tab_other .nav-tabs').find('li').eq(0).addClass('active');
		$('#tab_other .tab-content').find('.tab-pane').removeClass('active in');
		$('#tab_other .tab-content').find('.tab-pane').eq(0).addClass('active in');
		// 重置恢复方式tabs
        $('#nas_recovery_mode_tabs').find('li.active').removeClass('active');
        $('#nas_recovery_mode_tab_content').find('.tab-pane.nas-tab-content__pane.active').removeClass('active');

        // 显示通用策略tab
        $('#nas_recovery_mode_tabs').find(':first').addClass('active');
        $('#nas_recovery_mode_tab_content').find(':first.nas-tab-content__pane').addClass('active');

		agentShowInfo = nodes[0].name;
		$('.recover2agent').html(agentShowInfo);
        //恢复路径
        $('.recover2path').html(pathShowInfo);
        $('#recoveryThreadNum').prop('disabled', false); //启用线程数选择
		getTimeDes();
		//初始化重试策略
		$('#retry_config').retryStrategy();
		$('.network-retry-wrap').hide();
		//初始化安全策略
		var integrity_check_flag =  timepointTree.getCheckedNodes(true)[0].integrity_check_flag;
		$('#completeConfig').completeStrategyCovery(CONF.MODULE_TYPE.NAS, 0 , !integrity_check_flag );
		initResourceLimit([ajaxData.pointInfo.nodeuuid]);
		return true;
	}

	//<----------------------------- END STEP TWO RECOVER TARGET ------------------------------------>




	//<----------------------------- BEGIN STEP THREE RECOVER WAY ----------------------------------->

	/**
     * 初始化传输代理列表
     */
    const initApplianceAgency = () => {
		$('#transferAgentTree').transferAgent();
		// if(APPLIANCE_AGENCY_HAS_CONFIGED) return; //只加载一次
		// $('#transferAgentTree').transferAgent();
		// APPLIANCE_AGENCY_HAS_CONFIGED = true;
    }

	/**
     * 传输代理 switch change
     */
    const applianceSwitchChange = () => {
        let flag = $('#appliance_agency_flag').get(0).checked;

        if (flag) {
            $('.applianceselectdiv').show();
            $('.encrypt-transfer-form-group').show(); // 显示加密传输
        } else {
            $('.applianceselectdiv').hide();
            $('.encrypt-transfer-form-group').hide(); // 显示加密传输
            $('#transport_encrypt_flag').bootstrapSwitch('state', false);
            $('.encrypt-transfer-method-form-group').hide();
        }
    };

	/**
	 * 传输加密change
	 */
	const transferEncryptSwitchChange = function () {
		if (this.checked) {
			$('.encrypt-transfer-method-form-group').show();
		} else {
			$('.encrypt-transfer-method-form-group').hide();
		}
	}

	const getSpeedUnit = function(){
        let type = parseInt($('#unit').val());
        let unit;
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

    const getUuid = function() {
        let len = 36;//36长度
        let radix = 16;//16进制
        let chars = '0123456789abcdefghijklmnopqrstuvwxyz'.split('');
        let uuid = [], i;
        radix = radix || chars.length;
        if(len) {
          for(i = 0; i < len; i++)uuid[i] = chars[0 | Math.random() * radix];
        } else {
          let r;
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
	const speedSubmit = function(){
        let info = {};
        let des = '';
        info.mode = $('#speedModeType').val();
        let speedUnit = getSpeedUnit();
        let speedNum = parseInt($('#speedSpinnerNumInput').val());
        if(!speedNum || speedNum<= 0){
        	UIToastr.showWarning(LANG.UI_BACKUP_SPEED_LIMIT_TIPS);
        	return false;
        }
        let unit = $('#unit').find('option:selected').text();
        let liId = getUuid();
        info.uuid = liId;
        info.value = speedNum* speedUnit;
        info.speednum = speedNum;
        info.unit = unit;
        if(info.mode == 1){
            let strategyConfig = $('#speedstrategy').getSpeedStrategyConfig();
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
            for(let i=0;i<speedList.length; i++){
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

	/**
	 * 时间校验
	 * @param {*} start 
	 * @param {*} end 
	 * @returns 
	 */
	const checkTime = function (start,end) {
		let startnum = new Date("1970-01-01" + " " + start).getTime();
		let endnum = new Date("1970-01-01" + " " + end).getTime();
		if(endnum <= startnum && $("#speedModeType").val() == 1) {//按策略限速才判断
			UIToastr.showWarning(LANG.UI_FILE_CHECK_TIME_RANGE, LANG.UI_FILE_CHECK_TIME_RANGE_TIPS);
            return false;
		}else {
			return true;
		}
	}

	/**
	 * 初始化时间策略描述
	 */
	const initSpeedStrategyDes = function(){
		let titleDes = "";
		let des = "";
		if(speedList.length !=0){
			des += LANG.UI_GLOBAL_STRATEGY_SPEED_NUM + ": " + speedList.length;
		}
		for(let i=0;i<speedList.length;i++){
			titleDes += speedList[i].des + '. ';
		}
		$('.speedlimitDes').html(des);
		$('.speedlimitDes').prop('title', titleDes);
	}



	const speedModeHandler = function(){
        if(this.value == 2){
            $('.setSpeedStrategy').hide();
        }else{
            $('.setSpeedStrategy').show();
        }
	}

	const checkSimpleForever = function(mode){
        for(let i=0;i<speedList.length;i++){
            if(mode == speedList[i].mode){
                return false;
            }
        }

        return true;
	}

	const initSpeedTimeStrategy = function(){
		let strategy = [];
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

	const setStrategyInfo = function(info){
		ajaxData.typeInfo.strategy = info;
		return true;
	}

	//得到strategy_each信息
	const getStrategyEach = function(type, tabpane){
		let info = {};
		//type:每天/每周/每月
		info.type = type;
		info.startTime = tabpane.find('.starttime').val();
		info.rollFlag = tabpane.find('.make-switch').get(0).checked;
		info.rollInterval = tabpane.find('.rolltime').val();
		info.endTime = tabpane.find('.endtime').val();

		return info;
	}

	const getDays = function(type, tabpane){
		let days = [];
		let input;
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

	const daySelect = function(tabpane){
		let info = {};
		info = getStrategyEach(CONF.STRATEGY_TYPE.DAY, tabpane);

		return setStrategyInfo(info);
	}
	
	const weekSelect = function(tabpane){
		let info = {}, days = [];
		info = getStrategyEach(CONF.STRATEGY_TYPE.WEEK, tabpane);
		info.days = getDays(CONF.STRATEGY_TYPE.WEEK, tabpane);
		if(!flagDay) return false;
		return setStrategyInfo(info);
	}
	
	const monthSelect = function(tabpane){
		let info = {}, days = [];
		info = getStrategyEach(CONF.STRATEGY_TYPE.MONTH, tabpane);
		info.days = getDays(CONF.STRATEGY_TYPE.MONTH, tabpane);
		if(!flagDay) return false;
		return setStrategyInfo(info);
	}
	
	const globalSelect = function(tabpane, type){

	}

	const eachButtonClick = function(){
		let tabpane = $(this).closest('.tab-pane');
		let panelDefault = tabpane.closest('.panel-default');
		let panelHeading = panelDefault.children('.panel-heading');
		let i = panelHeading.find('i');
		//完全/增量/差异
		let setFlag = true;
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

	/**
     * 获取选择的限速策略
     */
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

	const getSwitchDes = function(check){
		if(check){
			return LANG.UI_PUBLIC_ON;
		}
		return LANG.UI_PUBLIC_OFF;
	}

	/**
     * 确认表单配置
     */
	const confirmRecoverForm = function(){
		let transferHtml = '';
        let deviceClient = parseInt($("#deviceSelect").val()); // 存储设备
        let pathType = parseInt($('#pathtype').val()); // 手动选择路径 | 原路径
        // let reconnectTimes = parseInt($('#reconnect_time').val()); // 重连次数
        // let reconnectInterval = parseInt($('#reconnect_interval').val()); // 重连时间间隔

		// 恢复方式
        let reservetypeshow = '';
        reservetypeshow = $('.recoveryTimeDes').text();
        $('.reservetypeshow').html(reservetypeshow);

		// 限速策略
        let speedlimitStr = $('.speedlimitDes').prop('title') === '' ? LANG.UI_PUBLIC_NOTHING : $('.speedlimitDes').prop('title');
        $('.speedlimitshow').html(speedlimitStr);

		// if (reconnectTimes === 0) {
        //     transferHtml += $('.reconnect-times-Label').text() + ": " + LANG.UI_GLOBAL_STRATEGY_RECONNECT_TIMES_INFINITE +"<br>"; // 重连次数
        // } else {
        //     transferHtml += $('.reconnect-times-Label').text() + ": " + reconnectTimes + LANG.UI_VOL_CDP_BACKUP_TIMES + "<br>"; // 重连次数
        // }

		// transferHtml += $('.reconnect-Interval-Label').text() + ": " + reconnectInterval + LANG.UI_PUBLIC_SECOND + " <br>"; // 重连时间间隔

		if (ajaxData.typeInfo.high.trasfer.encrypt) { // 开启加密传输
            transferHtml += LANG.UI_COPY_BACK_ENCRYPT + ": " + LANG.UI_PUBLIC_ON + '<br>';
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
                default:
                    break;
            }

            transferHtml += encryptedMethodLabel + ": " + grade + "<br>";
        } else {
			//如果是恢复到nas 则不显示传输加密
			if(deviceClient !=  STORAGE_DEVICE_TYPE.NAS){
				transferHtml += LANG.UI_COPY_BACK_ENCRYPT + ": " + LANG.UI_PUBLIC_OFF + '<br>';
			}
        }

		if (deviceClient === STORAGE_DEVICE_TYPE.OBS || deviceClient === STORAGE_DEVICE_TYPE.HADOOP) { // 恢复到对象存储和hadoop才有传输代理
            transferHtml += LANG.UI_PUBLIC_APPLICE + ': ' + getSwitchDes(ajaxData.typeInfo.high.trasfer.appliance_agency_flag) + '<br>';
            if (ajaxData.typeInfo.high.trasfer.appliance_agency_flag) { // 开启传输代理
				let applianceNode = $('#transferAgentTree').transferAgent('getSelect');
                transferHtml += $('.applianceselectlabel').text() + ': ' + applianceNode.name + '<br>';
            }
        }


		switch (deviceClient) {
            case STORAGE_DEVICE_TYPE.FILE: // 文件客户端
				// 传输网络
				if (networkFlag) {
					let networkNode = $('#transferNetworkTree').transferNetwork('getSelect');
					transferHtml += LANG.UI_VOL_CDP_RECOVER_TRANSPORT_NET + ': ' + networkNode.str + '<br>';
				}

                if (pathType === 1) { // 原路径恢复
                    $('.dirtreeshow').hide();
                } else {
                    $('.dirtreeshow').html($('.file-skip-dir-tree-label').html() + ": " + getSwitchDes(ajaxData.highInfo.dir_tree_recovery_flag));
                    $('.dirtreeshow').show();
                }

                $('.samenameshow').html($('.file-same-name-label').html() + ": " + $('#file_same_name_handle_type option:selected').text());
                $('.clearshortcutshow').html($('.clear-shortcut-label').html() + ": " + getSwitchDes(ajaxData.highInfo.link_file_pass_flag));
                $('.permissionrecoveryshow').html($('.file-permission-recovery-label').html() + ": " + getSwitchDes(ajaxData.highInfo.permission_operate_flag));
                break;
            case STORAGE_DEVICE_TYPE.NAS: // NAS设备

                if (pathType === 1) { // 原路径恢复
                    $('.dirtreeshow').hide();
                } else {
                    $('.dirtreeshow').html($('.file-skip-dir-tree-label').html() + ": " + getSwitchDes(ajaxData.highInfo.dir_tree_recovery_flag));
                    $('.dirtreeshow').show();
                }

                $('.samenameshow').html($('.file-same-name-label').html() + ": " + $('#file_same_name_handle_type option:selected').text());
                $('.clearshortcutshow').html($('.clear-shortcut-label').html() + ": " + getSwitchDes(ajaxData.highInfo.link_file_pass_flag));
                $('.permissionrecoveryshow').html($('.file-permission-recovery-label').html() + ": " + getSwitchDes(ajaxData.highInfo.permission_operate_flag));
                break;
            case STORAGE_DEVICE_TYPE.HADOOP: // Hadoop

                if (pathType === 1) { // 原路径恢复
                    $('.dirtreeshow').hide();
                } else {
                    $('.dirtreeshow').html($('.file-skip-dir-tree-label').html() + ": " + getSwitchDes(ajaxData.highInfo.dir_tree_recovery_flag));
                    $('.dirtreeshow').show();
                }

                $('.samenameshow').html($('.file-same-name-label').html() + ": " + $('#file_same_name_handle_type option:selected').text());
                $('.permissionrecoveryshow').hide();
                break;
            case STORAGE_DEVICE_TYPE.OBS: // 对象存储


                if (pathType === 1) { // 原路径恢复
                    $('.dirtreeshow').hide();
                } else {
                    $('.dirtreeshow').html($('.obs-skip-dir-tree-label').html() + ": " + getSwitchDes(ajaxData.highInfo.dir_tree_recovery_flag));
                    $('.dirtreeshow').show();
                }

                $('.samenameshow').html($('.obs-same-name-label').html() + ": " + $('#obs_same_name_handle_type option:selected').text());
				$('.permissionrecoveryshow').hide();
                break;
            default:
                break;
        }
		// 传输策略
		if(CONF.FUNCTIONS.includes('multithread')){
			transferHtml += $('.threadnumlabel').html() + ':' +  $('#recoveryThreadNum').val();
		}
		$('.transfershow').html(transferHtml);
	}


	/**
	 * 步骤三 - 恢复方式校验
	 * @returns 
	 */
	const step3Valid = function(){
		let encryptMethod = parseInt($('#transferEncryptMethod').val());
		if(CONF.FUNCTIONS.includes('multithread')){
			let threadNum = parseInt($('#recoveryThreadNum').val());
			if (threadNum > 32 || threadNum < 1 || !threadNum) { // 校验传输线程
				// 重置为默认值
				$('#recoveryThreadDiv').spinner("value", 3);
				$('.recoveryHighDes').html(LANG.UI_GLOBAL_STRATEGY_THREAD_NUM + ": " + $('#recoveryThreadNum').val());
				UIToastr.showWarning(LANG.UI_BACKUP_HIGH_SETTING_TITLE, LANG.UI_FILE_BACKUP_THREAD_NUM_TIPS);
				return false;
			}
			ajaxData.thread_num = threadNum; // 传输线程
		} else { 
			ajaxData.thread_num = 1; // 默认1
		}
        
		APPLIANCE_AGENCY_HAS_CONFIGED = $('#transferAgentTree').transferAgent('validateSelect');
        if ($('#appliance_agency_flag').get(0).checked && !APPLIANCE_AGENCY_HAS_CONFIGED) { // 开启传输代理但传输代理未预先配置时
            UIToastr.showWarning(LANG.UI_APPLIANCE_SELECT, LANG.UI_APPLIANCE_SELECT_NO_TIPS);
            return false;
        }
		// 2.ajax data 赋值
        let deviceClient = parseInt($("#deviceSelect").val()); // 存储设备

		ajaxData.typeInfo.type = parseInt($('#recovertype').val());//恢复方式
		ajaxData.typeInfo.strategy.start_time = $('#oncetime').val(); //定时恢复时间
		ajaxData.typeInfo.strategy.type = ajaxData.typeInfo.type;
		if (ajaxData.typeInfo.type == 2 && ajaxData.typeInfo.strategy.start_time == '') {//1立即恢复  2定时恢复
			UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_TIME,LANG.UI_BACKUP_SET_TIME_TIPS);
			return false;
		}
		ajaxData.speedInfo = speedSubmitInfo();// 限速策略
		let applianceNode = $('#transferAgentTree').transferAgent('getSelect'); // 获取传输代理
		switch (deviceClient) {
            case STORAGE_DEVICE_TYPE.FILE: // 文件客户端
                ajaxData.typeInfo.high.trasfer.encrypt = $('#transport_encrypt_flag').get(0).checked; // 是否加密传输
                ajaxData.typeInfo.high.trasfer.encrypt_method = encryptMethod; // 传输加密算法
                if (networkFlag) {
					let networkNode = $('#transferNetworkTree').transferNetwork('getSelect');
					ajaxData.typeInfo.high.trasfer.network = networkNode.network_uuid;
				}
                ajaxData.highInfo.dir_tree_recovery_flag = $('#file_skip_dir_tree_flag').get(0).checked; // 目录树恢复
                ajaxData.highInfo.same_file_strategy = $('#file_same_name_handle_type').val(); // 同名文件处理
                ajaxData.highInfo.link_file_pass_flag = $('#clear-shortcut').get(0).checked; // 无效快捷方式清理
                ajaxData.highInfo.permission_operate_flag = $('#file_permission_recovery_flag').get(0).checked; // 文件权限恢复

                break;
            case STORAGE_DEVICE_TYPE.NAS: // NAS设备
                ajaxData.highInfo.dir_tree_recovery_flag = $('#file_skip_dir_tree_flag').get(0).checked; // 目录树恢复
                ajaxData.highInfo.same_file_strategy = $('#file_same_name_handle_type').val(); // 同名文件处理
                ajaxData.highInfo.link_file_pass_flag = $('#clear-shortcut').get(0).checked; // 无效快捷方式清理
                ajaxData.highInfo.permission_operate_flag = $('#file_permission_recovery_flag').get(0).checked; // 文件权限恢复
                
                break;
            case STORAGE_DEVICE_TYPE.HADOOP: // Hadoop
                ajaxData.typeInfo.high.trasfer.encrypt = $('#transport_encrypt_flag').get(0).checked; // 是否加密传输
                ajaxData.typeInfo.high.trasfer.encrypt_method = encryptMethod; // 传输加密算法
                ajaxData.typeInfo.high.trasfer.appliance_agency_flag = $('#appliance_agency_flag').get(0).checked; // 是否开启传输代理
                //传输代理、传输代理资源池
				ajaxData.typeInfo.high.trasfer.appliance_uuid = ajaxData.typeInfo.high.trasfer.appliance_agency_flag ? applianceNode.agent_uuid : ''; // 传输代理
                ajaxData.typeInfo.high.appliance_pool_uuid = ajaxData.typeInfo.high.trasfer.appliance_agency_flag ? applianceNode.agent_pool_uuid : ''; // 传输代理资源池uuid
                ajaxData.highInfo.dir_tree_recovery_flag = $('#file_skip_dir_tree_flag').get(0).checked; // 目录树恢复
                ajaxData.highInfo.same_file_strategy = $('#file_same_name_handle_type').val(); // 同名文件处理
                ajaxData.highInfo.link_file_pass_flag = $('#clear-shortcut').get(0).checked; // 无效快捷方式清理
                ajaxData.highInfo.permission_operate_flag = $('#file_permission_recovery_flag').get(0).checked; // 文件权限恢复
                ajaxData.recoveryType = 1; // 对齐hadoop 创建恢复任务接口

                break;
            case STORAGE_DEVICE_TYPE.OBS: // 对象存储
                ajaxData.typeInfo.high.trasfer.encrypt = $('#transport_encrypt_flag').get(0).checked; // 是否加密传输
                ajaxData.typeInfo.high.trasfer.encrypt_method = encryptMethod; // 传输加密算法
                ajaxData.typeInfo.high.trasfer.appliance_agency_flag = $('#appliance_agency_flag').get(0).checked; // 是否开启传输代理
                //传输代理、传输代理资源池
				ajaxData.typeInfo.high.trasfer.appliance_uuid = ajaxData.typeInfo.high.trasfer.appliance_agency_flag ? applianceNode.agent_uuid : ''; // 传输代理
                ajaxData.typeInfo.high.appliance_pool_uuid = ajaxData.typeInfo.high.trasfer.appliance_agency_flag ? applianceNode.agent_pool_uuid : ''; // 传输代理资源池uuid
                ajaxData.highInfo.dir_tree_recovery_flag = $('#obs_skip_dir_tree_flag').get(0).checked; // 去除前缀
                ajaxData.highInfo.same_file_strategy = $('#obs_same_name_handle_type').val(); // 同名文件处理
                ajaxData.highInfo.permission_operate_flag = $('#obs_permission_recovery_flag').get(0).checked; // 对象权限恢复

                break;
            default:
                break;
        }
		// 忽略节点资源限制
		ajaxData.highInfo.ignore_resource_limiting_flag = !!$('#ignoreResourceLimit').get(0).checked;
		$('.ignoreResourceLimitShow').html($('.ignoreResourceLimitLabel').html() + ": " + getSwitchDes(ajaxData.highInfo.ignore_resource_limiting_flag));
		//重试策略
		ajaxData.retry_strategy = $('#retry_config').retryStrategy({} ,'value');
		$('.network_retry_times_show').hide();
       	$('.network_retry_interval_show').hide();
		if (!ajaxData.retry_strategy) {
			return false;
		}
		//安全策略
		let completeConfig = $('#completeConfig').getCompleteStrategyCovery();
		if (!CONF.FUNCTIONS.includes('integrity')) {
            completeConfig = '';
        }
		ajaxData.safe_strategy = safeData('', '', completeConfig);
		$('.safeStrategyShow').html(completeConfig.str);
        // 3.确认表单回显
        confirmRecoverForm();

        return true;
	}

	//<----------------------------- END STEP THREE RECOVER WAY ------------------------------------->




	//<----------------------------- BEGIN STEP FOUR CONFIRM CONFIGURATION -------------------------->

	/**
	 * 恢复任务提交
	 * @returns 
	 */
	const submit = function(){
		let deviceClient = parseInt($("#deviceSelect").val()); // 存储设备
        let api = '';

		if('' === $.trim($("#jobname").val())){
            $('.jobnametip').html(LANG.UI_BACKUP_NAME_TIPS).show();
            return;
        }
		let jobName = $.trim($("#jobname").val());
		// 输入验证
		if(!customInputValidate('string',jobName)){
			return false;
		}

		ajaxData.job_name = $.trim($("#jobname").val());

		switch (deviceClient) {
            case STORAGE_DEVICE_TYPE.FILE: // 文件客户端
                api = '/api/v1/files/recover_job';
                break;
            case STORAGE_DEVICE_TYPE.NAS: // NAS设备
                api = '/api/v1/nas/recover_job';
                break;
            case STORAGE_DEVICE_TYPE.HADOOP: // Hadoop
                api = '/api/v1/hadoop/jobs/restore';
                break;
            case STORAGE_DEVICE_TYPE.OBS: // 对象存储
                api = '/api/v1/s3/recover_job';
                break;
            default:
                break;
        }

		Metronic.blockUI({target: '#nasrecovercontent',animate: true, cenrerY: true,});

		pAjaxRequest(ajaxData, api, 'POST', (result) => {
            if (result.success) {
				UIToastr.showSuccess(LANG.UI_OBS_CREATE_RECOVER_TASK_SUCCESS);

                LOCATION('./content/platform/jobs/jobs.php', 'task');
            } else {
				UIToastr.showWarning(LANG.UI_OBS_CREATE_RECOVER_TASK_FAILED, result.message);
            }

            Metronic.unblockUI('#nasrecovercontent');
        })
	}

	//<----------------------------- END STEP FOUR CONFIRM CONFIGURATION ---------------------------->
	
	/**
	 * 步骤条插件初始化
	 * @returns 
	 */
	const wizardInit = function(){
		if (!jQuery().bootstrapWizard) {
            return;
        }
        let form = $('#submit_form');
        let error = $('.alert-danger', form);
        let success = $('.alert-success', form);
        let handleTitle = function(tab, navigation, index) {
            let total = navigation.find('li').length;
            let current = index + 1;
            jQuery('li', $('#nasrecovercontent')).removeClass("done");
            let li_list = navigation.find('li');
            for (let i = 0; i < index; i++) {
                jQuery(li_list[i]).addClass("done");
            }

            if (current == 1) {
                $('#nasrecovercontent').find('.button-previous').css('visibility', 'hidden');
                $('#nasrecovercontent').find('.button-next').addClass('next-btn-margin-left');
            } else {
                $('#nasrecovercontent').find('.button-previous').css('visibility', 'visible');
                $('#nasrecovercontent').find('.button-next').removeClass('next-btn-margin-left');
            }

            if (current >= total) {
                $('#nasrecovercontent').find('.button-next').hide();
                $('#nasrecovercontent').find('.button-submit').css('visibility', 'visible');
            } else {
                $('#nasrecovercontent').find('.button-next').show();
                $('#nasrecovercontent').find('.button-submit').css('visibility', 'hidden');
            }
            Metronic.scrollTo($('.page-title'));
        }

        // default form wizard
        $('#nasrecovercontent').bootstrapWizard({
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
                let total = navigation.find('li').length;
                let current = index + 1;
                let $percent = (current / total) * 100;
                $('#nasrecovercontent').find('.progress-bar').css({
                    width: $percent + '%'
                });
            }
        });

        $('#nasrecovercontent').find('.button-previous').css('visibility', 'hidden');
        $('#nasrecovercontent .button-submit').click(submit).css('visibility', 'hidden');
	};
	
	/**
	 * 初始化监听
	 */
	const initListener = function(){
		// <-------------	步骤一：备份时间点	----------------------->
		// 搜索时间点树
		$('#searchfs').on('input propertychange',function(){
			searchFS();
		});

		// 跳转到nas备份
		$('#tobackup').on('click',function(){
	    	LOCATION('./content/nas/nasbackup.php', 'nasbackup');
		});

		// 点击全选
		$(".selectall").click(function () {
			selectAllResult();
		});

		// 点击取消选择
		$(".cancelselect").click(function () {
			cancelSelect();
		});

		// 开始恢复搜索
		$('#reco-search-btn').on("click", startSearch);
		
		// 点击清除筛选
		$(".clearval").click(function () {
			clearSearchVal();
		});

		// <------------	步骤二：恢复目标	------------------------>

		// 选择恢复到的类型是nas还是文件
		$('#deviceSelect').on('change',function(){
			$('#pathtype').val(2);//切换后默认选中手动选择路径
			if(hostTree) {
				hostTree.checkAllNodes(false); // 取消选择
			}

			let $deviceLabel = $(".devicelabel");

			switch (parseInt(this.value)) {
                case STORAGE_DEVICE_TYPE.FILE: // fs
                    $deviceLabel.html(LANG.UI_VOL_CDP_BACKUP_CLIENT_SELECT);
                    $('#pathtype option[value=1]').hide();
                    initFileClientTree();
                    break;
                case STORAGE_DEVICE_TYPE.NAS: // nas
                    $deviceLabel.html(LANG.UI_NAS_DEVICE_SELECT);
                    $('#pathtype option[value=1]').show();
                    initNasClientTree('', ajaxData.pointInfo.pointUUID);
                    break;
                case STORAGE_DEVICE_TYPE.HADOOP: // hadoop
                    $deviceLabel.html(LANG.UI_OBS_SELECT_HADOOP);
                    $('#pathtype option[value=1]').hide();
                    initHadoopClientTree();
                    break;
                case STORAGE_DEVICE_TYPE.OBS: // obs
                    $deviceLabel.html(LANG.UI_OBS_SELECT_OBS);
                    $('#pathtype option[value=1]').hide();
                    initObsClientTree();
                    break;
                default:
                    break;
            }

			$('#searchHost').val('');
			$('#selectPath').hide();
            $('#pathtreediv').hide();
		});

		// 选择恢复路径
		$('#pathtype').on('change',function(){
			let treeNodes = hostTree.getCheckedNodes();
			let selectPath = $('#selectPath');
			if('1' == this.value){
				//原路径恢复
				UIToastr.showInfo(LANG.UI_FILE_RECOVERY,LANG.UI_FILE_OLD_PATH_RECOVERY_TIPS);
				selectPath.hide();
			}else if('2' == this.value && treeNodes.length > 0){
				//手动选择路径恢复并且有选中的客户端树节点
				selectPath.show();
				initNasFilePathTree(treeNodes[0]);
			}
		});
		
		// 选择恢复方式
		$('#recovertype').on('change',function(){
			if('1' == this.value){
			    ajaxData.typeInfo.strategy = {};
				$('.setOnceTime').hide();
			}else if("4" == this.value){
				$('.setOnceTime').show();
			}
			getTimeDes();
		});

		// 恢复目标客户端搜索
		$('#searchHost').on('keydown', debounceFS);

		// <------------------	步骤三：恢复方式  ----------------------->

		initApplianceAgency(); // 获取代理传输列表

		// 传输代理switch change
        $('#appliance_agency_flag').on('switchChange.bootstrapSwitch', applianceSwitchChange);

		// 传输策略---加密传输
		$('#transport_encrypt_flag').on('switchChange.bootstrapSwitch', transferEncryptSwitchChange);

		// 时间策略确定
		$('.each-button').on("click",eachButtonClick);

		// 初始化添加限速策略模态框
		$('#addSpeedlimit').on('click', function(){
			$('#speedlimitModal').modal({'width':'800px', 'height':'380px'});
			if(!initSpeedFlag){
				initSpeedTimeStrategy();
			}
		});

		// 切换限速模式
        $('#speedModeType').on('change', speedModeHandler);

        // 添加限速策略确定
        $('#speed_submit').on('click', speedSubmit);

        // 初始化限速大小设置值
        $('#speedSpinnerNum').spinner({value:10, step: 5, min: 1, max: 10000000000});
		$('#recoveryThreadDiv').spinner({value:3, step: 1, min: 1, max: 32});
	}

	// 获取设置的所有策略配置信息
	var speedSubmitInfo = function () {
		let info = {};
		info['level'] = parseInt($('#tasklevelselect').val());
		info['type'] = parseInt($('#speedtypeselect').val());
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

	//初始化日期选择插件
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

	var searchTimeRange = function () {
		if ($('#daterangepicker').val() == '') {
			searchByTree();
			return;
		}
		var p = JSON.stringify({
			storage_uuid: $('#storageSelect').val(),
			dataflag: false
		});
		$.post(CONF.AJAXPATH, {m:CONF.M.NAS,f:'getNasDataTree',p: p}, function (d) {
			setPointTree(d);
			var data = {}
			data.storage = $('#storageSelect').val();
			data.recovery_range = true;
			data.startTime = _daterangepicker_starttime;
			data.endTime = _daterangepicker_endtime;
			data.dataFlag = false;
			data = JSON.stringify(data);
			Metronic.blockUI({target:'.src-wrap__content__itree',animate: true});
			$.post(CONF.AJAXPATH, {m:CONF.M.NAS,f:'searchNasTimepoint',p:data}, function(d) {
				Metronic.unblockUI('.src-wrap__content__itree');
				var pointNode = JSON.parse(d);
				var showNode = [];
				if(pointNode) {
					pointNode.forEach(item => {
						//在当前树节点中搜索异步请求得到的时间点，如果当前树不存在，加入showNode数组
						var nodeExist = timepointTree.getNodesByParam("id", item.id, null)[0];
						if(nodeExist == null) {
							showNode.push(item);
						}
					});
					//把showNode中时间点加到对应父节点下
					if(showNode.length != 0) {
						showNode.forEach(item => {
							//先把完备点加进去，再放增量差异等，避免先搜增量差异等搜不到父节点
							if(item.type == 3) {
								var parentNode = timepointTree.getNodesByParam("id", item.pId, null)[0];
								timepointTree.addNodes(parentNode, item, true);
							}
						});
						showNode.forEach(item => {
							if(item.type == 4) {//增量差异等
								var parentNode = timepointTree.getNodesByParam("id", item.pId, null)[0];
								timepointTree.addNodes(parentNode, item, true);
							}
						});
					}
				}
				searchByTree();
			});
		});
	}

	const searchByTree = function() {
		//时间范围
		timepointTree.hideNodes(timepointTree.transformToArray(timepointTree.getNodes()));    //当开始搜索时，先将所有节点隐藏
		let startTime = new Date(_daterangepicker_starttime).getTime(); // 开始时间的时间戳
		let endTime = new Date(_daterangepicker_endtime).getTime(); // 结束时间的时间戳
		// 用于存储匹配时间范围的节点
		let matchNodes = [];
		// 遍历所有节点
		let nodes = timepointTree.transformToArray(timepointTree.getNodes());
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
		nodeParamList =nodeParamList.concat(checkNode);
		let nodeParamList1 = timepointTree.transformToArray(nodeParamList);
		for(let n in nodeParamList1){
			findParent(timepointTree,nodeParamList1[n]);
		}
		//都不为空取搜索框和时间范围的交集
		if (value != "" && $('#daterangepicker').val() != '') {
			matchNodes = matchNodes.filter(item => nodeParamList.includes(item));
		} else if (value == "" && $('#daterangepicker').val() == '') {//相当于清空搜索
			$('#nosearchtips').hide();
			$('.nas_tree').show();
			timepointTree.showNodes(timepointTree.transformToArray(timepointTree.getNodes()));
			return;
		} else {//有一个为空的取全集
			matchNodes = [...matchNodes, ...nodeParamList];
		}

		if(matchNodes.length == 0){
			$('.nas_tree').hide();
			$('#nosearchtips').show();
		}else{
			$('.nas_tree').show();
			$("#nosearchtips").hide();
		}
		let arr = new Array();
		for(let i=0; i<matchNodes.length; i++){
			arr = $.merge(arr,matchNodes[i].getPath());    //找出节点的所有父节点（包括自己）
			if(matchNodes[i].children) {//展开过，有子节点，把子节点加进去
				arr = $.merge(arr,matchNodes[i].children);
			}
		}
		let firstlevel = [];
		let otherlevel = [];
		arr.forEach(item=>{
			if(item.level != 0) {
				otherlevel.push(item);
			}else {
				firstlevel.push(item);
			}
		});
		let newArr = timepointTree.transformToArray(otherlevel);//避免获取到第一层下的其他任务
		newArr = newArr.concat(firstlevel);
		timepointTree.showNodes($.unique(newArr));    //显示所有要求的节点及其路径节点
		//展开显示的所有节点
		newArr.forEach(item => {
			if(item.children) {
				timepointTree.expandNode(item, true);
			}
		});
	}

    return {
        init: function () {
			inintDatatimePicker();
			initStorageSelect();
        	wizardInit();
        	initPointTree(CONF.VM_TYPE.VMWARE);
        	initListener();
        },
    };
}();

jQuery(document).ready(function() {
	NasRecover.init();
});