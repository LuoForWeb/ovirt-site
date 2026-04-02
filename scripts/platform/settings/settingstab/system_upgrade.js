//系统升级
var Settings_System_Upgrade = function () {
	let patchGrid = $('#patchTable'), patchGridFlag = false;
	let historyGrid = $('#patchHistoryTable'), historyGridFlag = false, historyGridInitFlag = false;
	let initFlag = false;
	let updateFlag = false;
	let masterFlag = false;
	let _DEFAULT_CONF = {};
	let updateNodeFlag = false;	//升级子节点
	let CURRENT_TO_BE_UPGRADED_FILE_NAME = ''; // 当前待升级的文件名
	let CHECK_STATUS_TIMER = null;
	const UPGRADE_FAILED_STATUS_ARR = [
		CONF.UPDATE_PATCH_STATUS.BD_PATCH_STATUS_PATCH_FAILED,
		CONF.UPDATE_PATCH_STATUS.BD_PATCH_STATUS_UPLOAD_FAILD,
		CONF.UPDATE_PATCH_STATUS.BD_PATCH_STATUS_PATCH_INVALID,
		CONF.UPDATE_PATCH_STATUS.BD_PATCH_STATUS_SPACE_NOT_ENOUGH_TO_UNCOMPRESS,
		CONF.UPDATE_PATCH_STATUS.BD_PATCH_STATUS_SPACE_NOT_ENOUGH_TO_DOWNLOAD,
		CONF.UPDATE_PATCH_STATUS.BD_PATCH_STATUS_DOWNLOAD_PATCH_FAILED
	]; // 升级失败的状态集合
	const UPGRADE_FAILED_STATUS_MAP = {
		2: LANG.UPGRADE_FAILED_STATUS_TIPS1,
		3: LANG.UPGRADE_FAILED_STATUS_TIPS2,
		8: LANG.UPGRADE_FAILED_STATUS_TIPS3,
		11: LANG.UPGRADE_FAILED_STATUS_TIPS4,
		12: LANG.UPGRADE_FAILED_STATUS_TIPS5,
		13: LANG.UPGRADE_FAILED_STATUS_TIPS6
	}; // 升级失败状态对应描述

	// <------------------------- BEGIN SYSTEM UPGRADE MANAGER ------------------------->

	/**
	 * 调用接口获取存储保护功能的状态
	 * @returns
	 */
	const storageCheck = function(){
		var result = true;
		$.ajax({
			type: "post",
			url: CONF.AJAXPATH,
			async: false,
			data:{m:CONF.M.SYSTEM,f:'getStorageProtect',p:{}},
			success: function(d){
				var dataSt = JSON.parse(d);
				if(dataSt.storageprotectcheck){
					result = false;
				}
			}
		});
		return result;
	}

	/**
	 * 系统升级
	 * @returns
	 */
	const upgradeSubmit = function(){
		var nodeuuids = [];
		var initflag = false;
		var select = patchGrid.bootstrapTable('getSelections');
		var patchuuid = select[0].uuid;
		var name = $('#patchName').text();
		var md5 = select[0].md5;
		var patch_options = {};
		patch_options.update_web_config = $('#update_web_config').is(':checked');
		patch_options.update_web_cert = $('#update_web_cert').is(':checked');
		patch_options.update_firewall_rule = $('#update_firewall_rule').is(':checked');
		CURRENT_TO_BE_UPGRADED_FILE_NAME = $('#patchName').text();
		if($('#nodeSelect').val()!=2){
			masterFlag = true;
			nodeuuids.push($('#nodeSelect').val());
		}else{
			masterFlag = false;
			$("#childNodes input:checked").each(function(i){
				nodeuuids[i] = $(this).val();
			});
		}
		if(nodeuuids.length==0){
			return UIToastr.showInfo(LANG.UI_SETTINGS_UPDATE_SELECT_NODE, LANG.UI_SETTINGS_UPDATE_SELECT_NODE_TIPS);
		}
		var params = {
			'node_uuids': nodeuuids,
			'uuid': patchuuid,
			'master_flag': masterFlag,
			'name': name,
			'md5': md5,
			'patch_options': patch_options
		}
		$('#upgradeSubmit').prop('disabled', true);
		$('#cancelUpdate').prop('disabled', true);
		if(!updateFlag){
			pAjaxRequest(params, '/api/v1/system/upgrade/check', 'POST', (res) => {
				if (operateResponseList(res, res.title)) {
					startUpdate(params, nodeuuids, initflag); //启动升级
					$('#nodeSelect').prop('disabled', true);
				} else {
					$('#upgradeModal').modal('hide');
				}
			});
		}else{
			$('#upgradeModal').modal('hide');
		}
	}

	/**
	 * 轮询获取升级日志
	 */
	const pollingGetUpgradeLog = (node_uuid, needBreak) => {
		pAjaxRequest({ node_uuid: node_uuid }, '/api/v1/system/upgrade/log', 'GET', (res) => {
			if (res.code === 0) {
				let logItemHtml = ''; // 升级日志子项renewal-log-wrap__item集合
				let logData = res.data;
				let breakHtml = ''; // 在某条errorCode不为0或者progress为100 都是break之后，需要打印的HTML

				if (logData.length > 0) {
					let length = logData.length;
					let displayLogData = [];

					// 取logData最新前三条日志作为展示，不满三条则取前两条或第一条
					if (length === 1) {
						displayLogData = logData.slice(0, 1);
					} else if (length === 2) {
						displayLogData = logData.slice(0, 2);
					} else {
						displayLogData = logData.slice(0, 3);
					}

					for (let i = 0; i < displayLogData.length; i++) {
						const logItem = displayLogData[i];

						logItemHtml += generateLogItemHtml(logItem);

						if (logItem.errorCode !== 0) {
							// 若某条日志 error_code 不为 0，则升级失败
							UIToastr.showError(LANG.UI_PLATFORM_GET_SYSTEM_UPGRADE_LOG, logItem.description);

							// 若某条日志 error_code 不为 0时，此时要打印全部日志，不然就直接break了
							// const allDescriptions = displayLogData.map(m => m.description);
							// breakHtml = allDescriptions.map(desc => `<span class="upgrade-wrapper-log__item">${desc}</span>`).join('');
							breakHtml = displayLogData.map(m => generateLogItemHtml(m)).join('');

							// 如果只有一个节点，或者已经是最后一个节点，某条日志errorCode不是0，就清除定时器
							if (needBreak) {
								clearInterval(CHECK_STATUS_TIMER);
							}

							break; // 终止循环
						}

						if (logItem.progress === 100) { // 升级完毕
							// 如果只有一个节点，或者已经循环到了最后一个节点就清除定时器
							if (needBreak) {
								clearInterval(CHECK_STATUS_TIMER);
							}

							// 进度100%时，第一条日志就是progress 100，此时要打印全部日志，不然就直接break了只会有一条日志
							// const allDescriptions = displayLogData.map(m => m.description);
							// breakHtml = allDescriptions.map(desc => `<span class="upgrade-wrapper-log__item">${desc}</span>`).join('');
							breakHtml = displayLogData.map(m => generateLogItemHtml(m)).join('');

							break; // 终止循环
						}
					}

					if (!breakHtml) {
						$(`#upgrade_log_${node_uuid}`).html(logItemHtml);
					} else {
						$(`#upgrade_log_${node_uuid}`).html(breakHtml);
					}
				}
			}
		})
	}

	/**
	 * 生成单条升级运行日志的html
	 */
	const generateLogItemHtml = (logItem) => {
		return `
			<span class="upgrade-wrapper-log__item" style="justify-content: space-between">
				<span>
					<label class="label ${logItem.errorCode === 0 ? 'label-success' : 'label-danger'} mr5" style="height: 20px">
						<i class="fa ${logItem.errorCode === 0 ? 'fa-check' : 'fa-times'}" style="width: 14px"></i>
					</label>
					${logItem.description}
				</span>
				<span style="font-style: italic">
					${logItem.logTime}
				</span>
			</span>
		`;
	}

	/**
	 * 获取升级包状态
	 * @param {*} nodeuuids
	 */
	const initUpgradeStatus = (nodeuuids) => {
		let isThrottled = false; // 节流阀
		let errorShown = false; // 错误信息是否已经显示

		const checkStatus = () => {
			// 使用节流写法保证 2 秒内checkStatus函数只会执行一次
			if (isThrottled) {
				return;
			}

			isThrottled = true;

			pAjaxRequest({ node_uuids: nodeuuids }, '/api/v1/system/packet_status', 'GET', (res) => {
				if (res.code === 0) {
					let statusArr = res.data;

					statusArr.forEach((item, index) => {
						let status = item.status;

						if (UPGRADE_FAILED_STATUS_ARR.indexOf(status) > -1) {
							// 如果是只有一个节点或最后一个节点的状态失败，则清除定时器
							if (index === statusArr.length - 1) {
								clearInterval(CHECK_STATUS_TIMER);
							}

							// 如果错误信息没有显示过，则显示错误信息并设置标志位
							if (!errorShown) {
								UIToastr.showError(LANG.SYSTEM_UPGRADE_FAILED, UPGRADE_FAILED_STATUS_MAP[status]);
								errorShown = true;
							}

							return;
						}

						// 状态为升级中或升级完毕的时候读数据库的升级日志，因为升级将要完成时，bd_update_file的status会率先变为4，只是 7 的时候读会读不到，因此4的状态时也需要去读最后几步的日志
						if (status === CONF.UPDATE_PATCH_STATUS.BD_PATCH_STATUS_PATCH_UPDATING || status === CONF.UPDATE_PATCH_STATUS.BD_PATCH_STATUS_PATCH_DONE) {
							// 只有一个节点或者最后一个节点，则清除定时器
							let breakFlag = index === statusArr.length - 1;
							pollingGetUpgradeLog(item.node_uuid, breakFlag);
						}
					});
				}

				// 重置标志位
				isThrottled = false;
			});
		}

		CHECK_STATUS_TIMER = setInterval(checkStatus, 2000); // 每2秒检查一次状态
	}

	/**
	 * 开始更新
	 * @param {*} p
	 * @param {*} nodeuuids
	 * @param {*} initflag
	 */
	const startUpdate = function(p, nodeuuids, initflag){
		$('#updateAlarm').show();
		$('#progressDiv').show();
		//如果在升级，停止界面超时检测
		UIIdleTimeout.stop();
		$('#updateList').empty();
		var li = "";
		for(var i=0;i<nodeuuids.length;i++){
			li = 	'<div id="'+nodeuuids[i]+'">' +
				'<div>' +
				'<span class="nodeName"></span>' +
				'<span class="updateStatus" style="float:right;"></span>' +
				'</div>' +
				'<div class="progress progress-striped mb-10 active">' +
				'<div class="progress-bar progress-bar-success total-progress" role="progressbar" aria-valuemin="0" aria-valuemax="90" style="width: 0%"></div>' +
				'<span  style="float: right;"class=" progressright"></span>' +
				'</div>' +
				'<div class="upgrade-wrapper-log" id="upgrade_log_' + nodeuuids[i] + '"></div>' +
				'</div>';
			$('#updateList').append(li);
		}

		setTimeout(function(){$('#progressDiv').show();}, 2000);
		Metronic.blockUI({target: '.upgrade-form',animate: true});
		pAjaxRequest(p, '/api/v1/system/upgrade', 'POST', (res) => {
			// 获取异常节点uuid
			let errNodeUuids = res.data.info;
			// 如果全部异常则解锁动画
			if (res.code === -1) {
				Metronic.unblockUI('.upgrade-form');
			}

			// 轮询获取进度
			initProgress(nodeuuids, initflag, errNodeUuids);

			// 轮询获取升级日志
			initUpgradeStatus(nodeuuids);
		});
	}

	const setBasicInfo = function(data,timeoutID, nodeuuids, errNodeUuids){
		var currentNodeList = [];
		var successFlag = true;		//升级成功标志
		for (var i=0;i<data.length;i++){
			var progress = data[i].progress;
			//将后台传过来的"x%",计算一下,换成90%大小
			var progressWidth = (parseInt(progress.substr(0, progress.length -1)) * 90 / 100) + "%";
			$('#'+data[i].node_uuid +' .nodeName').html(data[i].node_name);
			let errNodeFlag = false;
			if (errNodeUuids.includes(data[i].node_uuid)) {
				errNodeFlag = true;
				$('#'+data[i].node_uuid +' .updateStatus').html(`<span class="update-error">${LANG.UI_SETTINGS_UPDATE_NODE_EXCEPTION}</span>`);
			} else {
				$('#'+data[i].node_uuid +' .updateStatus').html(data[i].statusDes);
			}
			$('#'+data[i].node_uuid +' .total-progress').css({width: progressWidth});
			$('#'+data[i].node_uuid +' .progressright').html(data[i].progress);
			setStatusClass(data[i]);
			//获取正在升级的节点信息
			var info = {
				uuid: data[i].node_uuid,
				status: data[i].status
			};
			currentNodeList.push(info);

			successFlag = successFlag && (!errNodeFlag && data[i].status == 4 || errNodeFlag);
		}

		var statusList = [];//初始化所有节点当前状态列表
		var nodeuuidList = [];
		//子节点已上传升级包，等待发送升级命令
		if(currentNodeList.length != 0){
			for(var i=0;i<currentNodeList.length;i++){
				statusList.push(currentNodeList[i].status);
				//获取需要下载升级包或等待升级的子节点，且排除异常状态的
				if ((currentNodeList[i].status == 1 || currentNodeList[i].status == 6) && !errNodeUuids.includes(currentNodeList[i].uuid)) {
					nodeuuidList.push(currentNodeList[i].uuid);
				}
			}
			//向子节点发送升级命令
			if(nodeuuidList.length != 0){
				var select = patchGrid.bootstrapTable('getSelections');
				var patchuuid = select[0].uuid;
				var name = $('#patchName').text();
				if($('#nodeSelect').val() != 2){
					//主节点,直接返回,这里处理的子节点
					return;
				}else{
					//获取需要下载升级包的子节点列表
					masterFlag = false;
				}
				var patch_options = {};
				patch_options.update_web_config = $('#update_web_config').is(':checked');
				patch_options.update_web_cert = $('#update_web_cert').is(':checked');
				patch_options.update_firewall_rule = $('#update_firewall_rule').is(':checked');
				// var params = {
				// 	'nodeuuids': nodeuuidList,
				// 	'uuid': patchuuid,
				// 	'masterFlag': masterFlag,
				// 	'name': name,
				// 	'patch_options': patch_options
				// }
				// var p = JSON.stringify(params);
				// $.post(CONF.AJAXPATH,{m:CONF.M.SYSTEM, f: "upgradeChildNode", p:p}, function(d){
				// });
				let params = {
					'node_uuids': nodeuuidList,
					'uuid': patchuuid,
					'masterFlag': masterFlag,
					'name': name,
					'patch_options': patch_options
				}
				pAjaxRequest(params, '/api/v1/system/upgrade/child', 'POST', (res) => {
					// console.log('发送子节点升级消息', params)
				});
			}

			//升级失败，更新升级历史
			if(nodeuuids.length == statusList.length && $.inArray(6, statusList) == -1 && $.inArray(5, statusList) == -1
				&& $.inArray(1, statusList) == -1 && $.inArray(7, statusList) == -1 && $.inArray(9, statusList) == -1
				&& $.inArray(10, statusList) == -1){

				Metronic.unblockUI('.upgrade-form');
				$('#upgradeSubmit').removeAttr("disabled");
				$('#cancelUpdate').removeAttr("disabled");
				clearTimeout(timerTask.SystemUpgrade_RunningInfo);
				historyGrid.bootstrapTable('refresh');
			}

			//升级成功
			if(nodeuuids.length == currentNodeList.length && successFlag){
				Metronic.unblockUI('.upgrade-form');
				//升级成功或失败，开启界面超时检测
				UIIdleTimeout.start();
				$('#updateAlarm').hide();
				$('#cancelUpdate').hide();
				$('#upgradeSubmit').hide();
				$('#upgradeModal .close').hide();

				var des = "";
				if(masterFlag){
					des += '<p>' + LANG.UI_SETTINGS_UPDATE_PRIMARY_NODE_SUCCESS_TIPS + '</p>';
				}else{
					des += '<p>' + LANG.UI_SETTINGS_UPDATE_CHILD_NODE_SUCCESS_TIPS + '</p>';
				}
				if (errNodeUuids.length < nodeuuids.length) {
					$('#updateSuccess').html(des);
					$('#updateSuccess').show();
				}
				$('#closeBtn').show();
			}

		}
	}

	const setStatusClass = function(data){
		var status = data.status;
		var statusDiv = $('#'+data.node_uuid +' .updateStatus');
		var levelClass = ""
		switch(status){
			case 1:
				levelClass = "update-info";
				break;
			case 2:
				levelClass = "update-error";
				break;
			case 3:
				levelClass = "update-error";
				break;
			case 4:
				levelClass = "update-success";
				break;
			case 5:
				levelClass = "update-info";
				break;
			case 6:
				levelClass = "update-info";
				break;
			case 7:
				levelClass = "update-info";
				break;
			case 8:
				levelClass = "update-error";
				break;
			case 9:
				levelClass = "update-info";
				break;
			case 10:
				levelClass = "update-info";
				break;
			default:
				levelClass = "update-info";
				break;
		}
		statusDiv.removeClass('update-error update-info update-success').addClass(levelClass);
		return;
	}

	/**
	 * 升级进度条
	 * @param {*} nodeuuids
	 * @param {*} initflag
	 * @param {*} errNodeUuids
	 */
	const initProgress = function(nodeuuids, initflag, errNodeUuids){
		var updateInterval = 2000;
		var init = function(){
			if(0 == $('#patch_uuid').size()){
				clearTimeout(timerTask.SystemUpgrade_RunningInfo);
				return;
			}
			var data = {};
			data.uuid = $("#patch_uuid").val();
			data.node_uuids = nodeuuids;
			data.init_flag = initflag;
			pAjaxRequest(data, '/api/v1/system/upgrade', 'GET', (res) => {
				if (!res || 910087 === res.code) {
					// 升级过程中替换该接口对应的php文件时会无响应或重启数据库会话超时导致showWarning，直接return不显示提示
					return;
				}
				if (0 !== res.code) {
					UIToastr.showWarning(LANG.UI_SETTINGS_UPDATE_SYSTEM, res.message);
				} else {
					setBasicInfo(res.data, timerTask.SystemUpgrade_RunningInfo, nodeuuids, errNodeUuids);
				}
			});

			timerTask.SystemUpgrade_RunningInfo = setTimeout(init, updateInterval);
			initflag = true;
		}
		init();
	}

	/**
	 * 检测是否有升级中的升级包
	 * @returns
	 */
	const checkIsUpgrading = function () {
		var re = false;
		pAjaxRequest({}, '/api/v1/system/upgrade/check', 'GET', (res) => {
			re = res.data.isUpgrading;
		});
		return re;
	}

	/**
	 * 初始化升级模态框
	 * @param {*} select
	 */
	const initUpgradeModal = function(select){
		var params = {
			"uuid": select[0].uuid,
		}
		pAjaxRequest(params, '/api/v1/system/upgrade/patch', 'GET', (res) => {
			$('#patchName').html(res.data.name);
			$('#patch_uuid').prop("value", select[0].uuid);

			//处理自定义选项
			let options = res.data.options;
			if (options && options.length != 0) {

				//web配置文件
				if (options.update_web_conf == "true") {
					$('#update_web_config').iCheck('check');
				} else {
					$('#update_web_config').iCheck('uncheck');
				}

				//web证书文件
				if (options.update_web_cert  == "true") {
					$('#update_web_cert').iCheck('check');
				} else {
					$('#update_web_cert').iCheck('uncheck');
				}

				//防火墙规则
				if (options.update_firewall_rule  == "true") {
					$('#update_firewall_rule').iCheck('check');
				} else {
					$('#update_firewall_rule').iCheck('uncheck');
				}
			}
		});
	}

	const tipDelete = function(){
		UIToastr.showInfo(LANG.UI_SETTINGS_UPDATE_DELETE_PATCH, LANG.UI_SETTINGS_UPDATE_DELETE_PATCH_NO_SELECT);
	}

	const deletePatch = function(patchGrid){
		var select = patchGrid.bootstrapTable('getSelections');
		if(!select.length){
			return tipDelete();
		}
		bootbox.confirm({
			title: LANG.UI_SETTINGS_UPDATE_DELETE_PATCH,
			message: LANG.UI_SETTINGS_UPDATE_DELETE_PATCH_CONFIRM,
			callback: debounce(function(r) {
				if(!r) return;
				submitDelete(select);
			}, 300)
		});
	}

	const submitDelete = function(select){
		var data = {};
		data.uuids = [];
		$.each(select, function (i, v) {
			data.uuids.push(v.uuid);
		});
		Metronic.blockUI({target: '.upgrade-manager-table-container',animate: true});
		pAjaxRequest(data, '/api/v1/system/upgrade/patches', 'DELETE', (res) => {
			Metronic.unblockUI('.upgrade-manager-table-container');
			if (operateResponseList(res, res.title)) {
				patchGrid.bootstrapTable('refresh');
			}
		});
	}

	/**
	 * 初始化上传插件
	 */
	const initUploader = function(){
		$list = $('#thelist');
		var flie_count = 0;
		var uploader = WebUploader.create({
			//设置选完文件后是否自动上传
			auto: false,
			//swf文件路径
			swf: '../img/Uploader.swf',
			// 文件接收服务端。
			server: '/api/?m=8&f=uploadPatch',
			// server: '/api/v1/system/upgrade/upload',
			// 选择文件的按钮。可选。
			// 内部根据当前运行是创建，可能是input元素，也可能是flash.
			pick: '#picker',
			accept: {
				title: 'upgrade package file',
				extensions: 'tar.gz,update',
				mimeTypes: ''
			},
			timeout: 0,//设置超时时间
			chunked: true, //分片上传大文件
			chunkSize: 5 * 1024 * 1024,
			chunkRetry: 3,//如果某个分片由于网络问题出错，允许自动重传多少次
			resize: false//不压缩
		});
		// 添加上传接口的header
		// uploader.on('uploadBeforeSend', function(object, data, headers) {
		// 	headers['Authorization'] = window.localStorage.getItem('csrf_token');
		// 	headers['X-Csrf-Token'] = window.localStorage.getItem('access_token');
		// 	headers['x-api-version'] = '1.0-rev0';
		// });
		//当文件被添加到队列之前
		uploader.on('beforeFileQueued', function (file) {
			var name = $.trim(file.name);
			var txt = name.substring(name.length-6, name.length); //只允许tar.gz添加
			if((txt != "tar.gz" && txt != "update") || file.size == 0 || name.indexOf(" ")!=-1){
				UIToastr.showWarning(LANG.UI_SETTINGS_UPDATE_ADD_QUEUE_ERROR, LANG.UI_SETTINGS_UPDATE_ADD_QUEUE_ERROR_TIPS);
				return false;
			}
			var checkFlag = true;
			//检查系统空间是否足够
			Metronic.blockUI({target: '#uploadModal',animate: true});
			pAjaxRequest({file_size: file.size, file_name: name}, '/api/v1/system/upgrade/upload', 'GET', (res) => {
				Metronic.unblockUI('#uploadModal');
				if (res.success) return true;
				if (!operateResponseList(res, res.title)) {
					checkFlag = false;
				}
			});
			if(!checkFlag){
				return false;
			}
		});

		// 当有文件被添加进队列的时候
		uploader.on( 'fileQueued', function(file) {
			$list.append( '<div id="' + file.id + '" class="item">' +
				'<h4 class="info">' + file.name + '<a id="delete'+file.id+'" style="margin-left:20px;font-size:14px;">'+LANG.UI_PUBLIC_DELETE+'</a></h4>' +
				'<p class="state">'+LANG.UI_SETTINGS_UPDATE_WAIT_UPLOAD+'</p>' +
				'</div>' );
			$('#delete'+file.id).on('click',function(){
				$('#'+file.id).remove();
				uploader.removeFile(uploader.getFile(file.id), true); //把对应文件从队列中删除
			});

		});
		$('#ctlBtn').on('click',function(){
			uploader.upload();
		});
		$('#pause').on('click',function(){
			uploader.stop(true);
			$('.pauseDiv').hide();
			$('.startDiv').show();
//        	$('.retryDiv').hide();
		});

		$('#continue').on('click',function(){
			uploader.upload();
			$('.pauseDiv').show();
			$('.startDiv').hide();
//        	$('.retryDiv').hide();
		});

		$('#retry').on('click',function(){
			uploader.retry();
			$('.pauseDiv').show();
			$('.startDiv').hide();
			$('.retryDiv').hide();
		});

		// 文件上传过程中创建进度条实时显示。
		uploader.on( 'uploadProgress', function( file, percentage ) {
			//如果在上传途中，停止界面超时检测
			UIIdleTimeout.stop();
			var $li = $( '#'+file.id ),
				$percent = $li.find('.progress .progress-bar');

			// 避免重复创建
			if ( !$percent.length ) {
				$percent = $('<div class="progress progress-striped active">' +
					'<div class="progress-bar progress-bar-success" role="progressbar" style="width: 0%">' +
					'</div>' +
					'</div>').appendTo( $li ).find('.progress-bar');
			}


			$li.find('p.state').text(LANG.UI_SETTINGS_UPDATE_UPLOADING);
			$percent.css( 'width', percentage * 100 + '%' );
			$('.pauseDiv').show();
			$('.startDiv').hide();
			$('.retryDiv').hide();
		});

		//文件上传成功回调
		uploader.on( 'uploadSuccess', function( file,res) {
			//重启界面超时检测
			UIIdleTimeout.start();
			$('.pauseDiv').hide();
			$('.startDiv').hide();
			$('.retryDiv').hide();
			if(res.error){
				$( '#'+file.id ).find('p.state').text(LANG.UI_SETTINGS_UPDATE_UPLOAD_ERROR +"(" + res.error.message +")");
				UIToastr.showWarning(LANG.UI_SETTINGS_UPDATE_SYSTEM_PACKAGE, LANG.UI_SETTINGS_UPDATE_SYSTEM_PACKAGE_TIPS);
				return;
			}

			//把数据插入数据库
			var name = file.name;
			var fileSize = file.size;
			var data = {
				'name': name,
				'size': fileSize
			}
			pAjaxRequest(data, '/api/v1/system/upgrade/patches', 'POST', (res) => {
				if (operateResponseList(res, res.title)) {
					$( '#'+file.id ).find('p.state').text(LANG.UI_SETTINGS_UPDATE_UPLOAD_SUCCESS);
					$( '#'+file.id ).find('.progress').fadeOut();
					patchGrid.bootstrapTable('refresh');
					$('#uploadModal').modal('hide');
					uploader.reset();
					$('#thelist').empty();
					$('#updateList').empty();
					$('#progressDiv').hide();
					$('#updateAlarm').hide();
				} else {
					$( '#'+file.id ).find('p.state').text(LANG.UI_SETTINGS_UPDATE_UPLOAD_ERROR);
				}
			});

		});

		//文件上传错误回调

		uploader.on( 'uploadError', function( file, res) {
			//重启界面超时检测
			UIIdleTimeout.start();
			$( '#'+file.id ).find('p.state').text(LANG.UI_SETTINGS_UPDATE_UPLOAD_ERROR +"(" + res.error.message +")");
			$('.pauseDiv').hide();
			$('.startDiv').hide();
			$('.retryDiv').show();
			$( '#'+file.id ).find('.progress').remove();
		});

		//文件上传完回调
		uploader.on( 'uploadComplete', function( file ) {
			$('.close').on('click', function () {
				uploader.reset();
				$('#thelist').empty();
				$('#updateList').empty();
				$('#progressDiv').hide();
				$('#updateAlarm').hide();
			});
		});

		//当所有文件上传结束时关闭模态框
		uploader.on('uploadFinished', function(){
		});

	}

	/**
	 * 初始化升级包管理表格
	 */
	const handleRecords = function () {
		if (!patchGridFlag) {
			let options = {
				vin_url: '/api/v1/system/upgrade/patches',
				vin_method: 'GET',
				queryParamsType: 'limit',
				queryParams: function (p) {
					return {
						limit: p.limit,
						offset: p.offset,
						order: p.order,
						sort: p.sort
					}
				},
				pagination: true,
				sidePagination: 'server',
				pageNumber: 1,
				pageSize: 10,
				pageList: [10, 20, 50, 100],
				paginationLoop: false,
				uniqueId: 'uuid',
				batchOperation: true, // 批量操作
				lineHeight: '60px',
				sortable: true,
				sortName: 'upload_time',
				sortOrder: 'desc',
				resizable: true,
				PostBody: function () {
					patchGrid.find('th[data-field="name"]').css('width', '45%');
				},
				onRefresh: function (params) {
					patchGrid.bootstrapTable('hideLoading');
				},
				onCheck: function (res) {
					var selectedRow = patchGrid.bootstrapTable("getSelections");
					$('#patchTable thead .bs-checkbox input[type=checkbox]').css("background-image", "url('./assets/global/img/checksome.svg')");
					if (selectedRow.length == 0) {
						$('#patchTable .fixed-table-pagination .pull-left .pagination-info span').html('');
						deleteBtnDisabled('#deletePatch');
					} else if (selectedRow.length > 0) {
						$('#patchTable .fixed-table-pagination .pull-left .pagination-info span').html('<span class="m-lr2">|</span>选中' + selectedRow.length + '');
						deleteBtnEnabled('#deletePatch');
					}
				},
				onUncheck: function (row, $element) {
					var selectedRow = patchGrid.bootstrapTable("getSelections");
					if (selectedRow.length == 0) {
						$('#patchTable thead .bs-checkbox input[type=checkbox]').css("background-image", "url('./assets/global/img/checkbox.svg')");
						$('#patchTable .fixed-table-pagination .pull-left .pagination-info span').html('');
						deleteBtnDisabled('#deletePatch');
					} else if (selectedRow.length > 0) {
						$('#patchTable .fixed-table-pagination .pull-left .pagination-info span').html('<span class="m-lr2">|</span>选中' + selectedRow.length + '');
						deleteBtnEnabled('#deletePatch');
					}
				},
				onCheckAll: function () {
					var selectedRow = patchGrid.bootstrapTable("getSelections");
					$('#patchTable thead .bs-checkbox input[type=checkbox]').css("background-image", "url('./assets/global/img/checked.svg')");
					$('#patchTable .fixed-table-pagination .pull-left .pagination-info span').html('<span class="m-lr2">|</span>选中' + selectedRow.length + '');
					deleteBtnEnabled('#deletePatch');
				},
				onUncheckAll: function () {
					$('#patchTable thead .bs-checkbox input[type=checkbox]').css("background-image", "url('./assets/global/img/checkbox.svg')");
					deleteBtnDisabled('#deletePatch');
				},

				columns: [
					{
						field: 'checkbox',
						checkbox: true,
						sortable: false, //默认可排序，禁用排序才写此项
					},
					{
						field: 'name',
						title: LANG.UI_SETTINGS_UPDATE_PACKAGE_NAME,
						sortable: false
					},
					{
						field: 'md5',
						title: 'MD5',
						sortable: false
					},
					{
						field: 'size',
						title: LANG.UI_SETTINGS_UPDATE_PACKAGE_SIZE,
						sortable: false
					},
					{
						field: 'upload_time',
						title: LANG.UI_SETTINGS_UPDATE_UPLOAD_TIME,
						sortable: true
					}
				]
			};
			patchGrid.baseTableConfig().init(options);
			patchGridFlag = true;
			$('#marktips').hide();
			$('#patchTable').show();
			$('#marktips').show();
		} else {
			patchGrid.bootstrapTable('refreshOptions', {
				queryParams: function (queryParams) {
					return {
						limit: queryParams.limit,
						offset: queryParams.offset,
						order: queryParams.order,
						sort: queryParams.sort
					};
				}
			});
		}
	}

	const deleteBtnEnabled = function (btnId) {
		$(btnId).removeClass('cancel-delete-btn');
		$(btnId).addClass('btn-primary');
		$(btnId + ' i').removeClass('icon-gray-delete');
		$(btnId + ' i').addClass('icon-white-delete');
		$(btnId).css('cursor', 'pointer');
	}

	const deleteBtnDisabled = function (btnId) {
		$(btnId).removeClass('btn-primary');
		$(btnId).addClass('cancel-delete-btn');
		$(btnId + ' i').removeClass('icon-white-delete');
		$(btnId + ' i').addClass('icon-gray-delete');
		$(btnId).css('cursor', 'not-allowed');
	}

	/**
	 * 初始化所有备份节点
	 */
	const initNodeSelect = function(){
		$('#upgradeSubmit').removeAttr("disabled");
		$('#cancelUpdate').removeAttr("disabled");
		var select = patchGrid.bootstrapTable('getSelections');
		var params = {
			'uuid': select[0].uuid
		}
		pAjaxRequest(params, '/api/v1/system/upgrade/nodes', 'GET', (res) => {
			var data = res.data;
			var nodeSelect = $('#nodeSelect');
			nodeSelect.empty();
			var option1 = $("<option>").text(LANG.UI_SETTINGS_UPDATE_MASTER_NODE +data[0].node_name).val(data[0].node_uuid);
			nodeSelect.append(option1);

			if(data.length > 1){
				var option2 = $("<option>").text(LANG.UI_SETTINGS_UPDATE_CHILD_NODE).val(2);
				nodeSelect.append(option2);
				$('#childNodes').empty();
				var list ="";
				for(var i=1;i<data.length;i++){
					if(data[i].update_flag){
						list = 	'<div class="childnode-wrapper__item" style="height: auto">' +
							'<label>' +
							'<input disabled="true" data-checkbox="icheckbox_square-blue" class="icheck" style="margin-right:4px;" type="checkbox" id="icheck'+ data[i].node_uuid +'" value="'+ data[i].node_uuid +'">' +
							data[i].node_name +
							'<span style="color:#34D48D;">' + LANG.UI_TENANT_UPGRADED + '</span>' +
							'</label>' +
							'</div>';
					}else{
						if(!data[i].use_flag.flag){
							list = '<div class="childnode-wrapper__item" style="height: auto">' +
								'<label>' +
								'<input disabled="true" data-checkbox="icheckbox_square-blue" class="icheck" style="margin-right:4px;" type="checkbox" value="'+ data[i].node_uuid +'">' +
								data[i].node_name +
								'<span style="color:#dfba49;">' + LANG.UI_TENANT_UPGRADE_ERROE + '</span>' +
								'</label>' +
								'</div>';
						}else{
							list = '<div class="childnode-wrapper__item" style="height: auto">' +
								'<label>' +
								'<input data-checkbox="icheckbox_square-blue" class="icheck" style="margin-right:4px;" type="checkbox" value="'+ data[i].node_uuid +'">' +
								data[i].node_name +
								'</label>' +
								'</div>';
						}
					}
					$('#childNodes').append(list);
				}
			}
			$('#childNodes .icheck').iCheck({
				labelHover : true,
				cursor : true,
				checkboxClass : 'icheckbox_square-blue',
				radioClass : 'iradio_square-blue',
				increaseArea : '20%'
			});
			if(!initFlag){
				$('#nodeSelect').on('change', function(){
					if(this.value == 2){
						var select = patchGrid.bootstrapTable('getSelections');
						var params = {};
						params.node_uuid = data[0].node_uuid;
						params.patch_uuid = select[0].uuid;
						pAjaxRequest(params, '/api/v1/system/upgrade/check/master', 'GET', (res) => {
							if (res && !res.success) {
								operateResponseList(res, res.title);
								$('#upgradeSubmit').prop('disabled',true);
								$('#cancelUpdate').prop('disabled', true);
							}
						});
						$('#childDiv').show();
					}else{
						$('#childDiv').hide();
						$('#upgradeSubmit').removeAttr("disabled");
						$('#cancelUpdate').removeAttr("disabled");
					}
				});
				initFlag = true;
			}

		});
	}

	const setUpdateDefault = function(){
		var updateConf = _DEFAULT_CONF.update;
		if(updateConf.length == 0 || updateConf == null || updateConf == undefined || updateConf == ""){
			//改变检查更新开关时触发
			$('#updatecheck').bootstrapSwitch('onSwitchChange', function(){
				//得到开关状态
				var updatecheck = $('#updatecheck').bootstrapSwitch('state');
				//如果是关掉的则退出
				if(!updatecheck){
					return;
				}
				//如果是打开则打开个人信息授权书
				$('#authDetails').modal({'width':'630px', 'height':'500px'});

			})
			return;
		}

		//设置检查更新
		$('#updatecheck').bootstrapSwitch('state', updateConf.checkflag);
		$("#updateTCP").prop("checked",true);
		$("#updateTCP").uniform.update();
		$("#updatetime").val(updateConf.timeperiod);

		//改变检查更新开关时触发
		$('#updatecheck').bootstrapSwitch('onSwitchChange', function(){
			//得到开关状态
			var updatecheck = $('#updatecheck').bootstrapSwitch('state');
			//如果是关掉的则退出
			if(!updatecheck){
				return;
			}
			//如果是打开则打开个人信息授权书
			$('#authDetails').modal({'width':'630px', 'height':'500px'});

		})
	}

	const initDefaultConf = function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM, f:'getDefaultNoticeConf', p:{}}, function(d){
			var data = JSON.parse(d);
			_DEFAULT_CONF = data;
			setUpdateDefault();
		});
	}

	// <------------------------- END SYSTEM UPGRADE MANAGER ------------------------->


	// <------------------------- BEGIN SYSTEM UPGRADE HISTORY ------------------------->

	/**
	 * 删除升级历史日志
	 * @param {*} historyGrid
	 * @returns
	 */
	const deletePatchLog = function(historyGrid){
		var select = historyGrid.bootstrapTable('getSelections');
		if(!select.length){
			return UIToastr.showInfo(LANG.UI_SETTINGS_UPDATE_DELETE_HISTORY, LANG.UI_SETTINGS_UPDATE_DELETE_HISTORY_NO_SELECT);
		}
		bootbox.confirm({
			title: LANG.UI_SETTINGS_UPDATE_DELETE_HISTORY,
			message: LANG.UI_SETTINGS_UPDATE_DELETE_HISTORY_CONFIRM,
			callback: debounce(function(r) {
				if(!r) return;
				submitDeleteHistory(select);
			}, 300)
		});
	}

	const downloadHistor = function(historyGrid){
		var select = historyGrid.bootstrapTable('getSelections');
		if(select.length != 1){
			return UIToastr.showInfo(LANG.UI_SETTINGS_UPDATE_DOWNLOAD_HISTORY_LOG, LANG.UI_SETTINGS_UPDATE_DOWNLOAD_HISTORY_LOG_NULL);
		}
		var data = {};
		data.id = select[0].id;
		data = JSON.stringify(data);
		pAjaxRequest({uuid: select[0].id}, '/api/v1/system/upgrade/history/download', 'GET', (res) => {
			if (res.data.length > 200) {
				window.location.href = CONF.AJAXPATH + '?m=' + CONF.M.SYSTEM + '&f=downloadHistory&p=' + data;
			} else {
				operateResponseList(res);
			}
		});
	}

	const submitDeleteHistory = function(select){
		var data = {};
		data.ids = [];
		$.each(select, function (i, v) {
			data.ids.push(v.id);
		});
		Metronic.blockUI({target: '#patchHistory',animate: true});
		pAjaxRequest(data, '/api/v1/system/upgrade/history', 'DELETE', (res) => {
			Metronic.unblockUI('#patchHistory');
			if (operateResponseList(res, res.title)) {
				historyGrid.bootstrapTable('refresh');
			}
		});
	}

	/**
	 * 初始化升级历史表格
	 */
	const historyRecords = function () {
		if (!historyGridFlag) {
			let options = {
				vin_url: '/api/v1/system/upgrade/history',
				vin_method: 'GET',
				queryParamsType: 'limit',
				queryParams: function (p) {
					return {
						limit: p.limit,
						offset: p.offset,
						order: p.order,
						sort: p.sort
					}
				},
				pagination: true,
				sidePagination: 'server',
				pageNumber: 1,
				pageSize: 10,
				pageList: [10, 20, 50, 100],
				paginationLoop: false,
				uniqueId: 'id',
				lineHeight: '60px',
				sortable: true,
				sortName: 'log_time',
				sortOrder: 'desc',
				resizable: true,
				onRefresh: function (params) {
					historyGrid.bootstrapTable('hideLoading');
				},
				onCheck: function (res) {
					var selectedRow = historyGrid.bootstrapTable("getSelections");
					$('#patchTable thead .bs-checkbox input[type=checkbox]').css("background-image", "url('./assets/global/img/checksome.svg')");
					if (selectedRow.length == 0) {
						$('#patchTable .fixed-table-pagination .pull-left .pagination-info span').html('');
						deleteBtnDisabled('#deleteHistoryLog');
					} else if (selectedRow.length > 0) {
						$('#patchTable .fixed-table-pagination .pull-left .pagination-info span').html('<span class="m-lr2">|</span>选中' + selectedRow.length + '');
						deleteBtnEnabled('#deleteHistoryLog');
					}
				},
				onUncheck: function (row, $element) {
					var selectedRow = historyGrid.bootstrapTable("getSelections");
					if (selectedRow.length == 0) {
						$('#patchTable thead .bs-checkbox input[type=checkbox]').css("background-image", "url('./assets/global/img/checkbox.svg')");
						$('#patchTable .fixed-table-pagination .pull-left .pagination-info span').html('');
						deleteBtnDisabled('#deleteHistoryLog');
					} else if (selectedRow.length > 0) {
						$('#patchTable .fixed-table-pagination .pull-left .pagination-info span').html('<span class="m-lr2">|</span>选中' + selectedRow.length + '');
						deleteBtnEnabled('#deleteHistoryLog');
					}
				},
				onCheckAll: function () {
					var selectedRow = historyGrid.bootstrapTable("getSelections");
					$('#patchTable thead .bs-checkbox input[type=checkbox]').css("background-image", "url('./assets/global/img/checked.svg')");
					$('#patchTable .fixed-table-pagination .pull-left .pagination-info span').html('<span class="m-lr2">|</span>选中' + selectedRow.length + '');
					deleteBtnEnabled('#deleteHistoryLog');
				},
				onUncheckAll: function () {
					$('#patchTable thead .bs-checkbox input[type=checkbox]').css("background-image", "url('./assets/global/img/checkbox.svg')");
					deleteBtnDisabled('#deleteHistoryLog');
				},

				columns: [
					{
						field: 'checkbox',
						checkbox: true,
						sortable: false, //默认可排序，禁用排序才写此项
					},
					{
						field: 'no',
						title: LANG.UI_PUBLIC_TABLE_ID,
						sortable: false,
						width: '100px'
					},
					{
						field: 'node_name',
						title: LANG.UI_NODE_NODE_NAME,
						sortable: false
					},
					{
						field: 'node_ip',
						title: LANG.UI_NODE_IP_ADDRESS,
						sortable: false
					},
					{
						field: 'patch_file_name',
						title: LANG.UI_SETTINGS_UPDATE_PACKAGE_NAME,
						sortable: false
					},
					{
						field: 'log_time',
						title: LANG.UI_SETTINGS_UPDATE_UPGRADE_TIME,
						sortable: true
					},
					{
						field: 'status',
						title: LANG.UI_SETTINGS_UPDATE_UPGRADE_RESULT,
						sortable: true,
						formatter: function (value, row) {
							if (0 === row.errno) {
								return '<span class="label label-sm label-success">' + value + '</span>';
							} else {
								return '<span class="label label-sm label-danger">' + value + '</span>';
							}
						}
					}
				]
			};
			historyGrid.baseTableConfig().init(options);
			historyGridFlag = true;
			historyGridInitFlag = true;
			$('#patchHistoryTable').show();
		} else {
			historyGrid.bootstrapTable('refreshOptions', {
				queryParams: function (queryParams) {
					return {
						limit: queryParams.limit,
						offset: queryParams.offset,
						order: queryParams.order,
						sort: queryParams.sort
					};
				}
			});
		}
	}

	// <------------------------- END SYSTEM UPGRADE HISTORY ------------------------->

	const addListeners = function(){
		//检测最新版本
		$('#upgrade').on('click', function(){
			if(checkIsUpgrading()){
				return UIToastr.showInfo(LANG.UI_SETTINGS_UPDATE_SYSTEM, LANG.UI_SETTINGS_UPDATE_SYSTEM_IS_UPGRADING);
			}
			$('#cancelUpdate').show();
			$('#upgradeSubmit').show();
			$('#upgradeModal .close').show();
			$('#nodeSelect').prop('disabled', false);

			var select = patchGrid.bootstrapTable('getSelections');
			if(!select.length){
				return UIToastr.showInfo(LANG.UI_SETTINGS_UPDATE_SYSTEM, LANG.UI_SETTINGS_UPDATE_SYSTEM_NO_SELECT);
			}

			if(select.length > 1){
				return UIToastr.showInfo(LANG.UI_SETTINGS_UPDATE_SYSTEM, LANG.UI_SETTINGS_UPDATE_SYSTEM_SELECT_TOO_MANY);
			}
			//增加对存储保护功能是否开启的判断.
			var storage_check = storageCheck();
			if(!storage_check){
				return UIToastr.showInfo(LANG.UI_SETTINGS_UPDATE_SYSTEM, LANG.UI_SETTING_OFF_STORAGE_PROTECTION);
			}
			$('#upgradeModal').modal({'width':'630px', 'height':'500px'});
			initUpgradeModal(select);

			initNodeSelect();
			$('#childDiv').hide();
			$('#progressDiv').hide();
			updateFlag = false;
			updateNodeFlag = false;
		});

		$('#fileupload').on('click',function(){
			$('#uploadModal').modal({'width':'500px', 'height': '500px'});
			$("#picker div:eq(1)").attr("style","position: absolute; top: 0px; left: 0px; width: 72px; height: 28px; overflow: hidden; bottom: auto; right: auto;");
		});

		$("#deletePatch").on('click', function(){
			deletePatch(patchGrid);
		});

		$('#deleteHistoryLog').on('click', function(){
			deletePatchLog(historyGrid);
		});

		$('#upgradeSubmit').on('click', upgradeSubmit);

		$('#cancelUpdate').on('click',function(){
			$('#updateList').empty();
			$('#progressDiv').hide();
			$('#updateAlarm').hide();
		});

		$('#closeBtn').on('click', function(){
			$('#upgradeModal').modal('hide');
			$('#updateList').empty();
			$('#progressDiv').hide();
			$('#updateAlarm').hide();
			$('#updateSuccess').hide();
			$('#closeBtn').hide();
			if(masterFlag){
				UIToastr.showSuccess(LANG.UI_SETTINGS_UPDATE_SYSTEM, LANG.UI_SETTINGS_UPDATE_SYSTEM_SUCCESS_TIPS)
				setTimeout(function(){window.location = './loginout.php'}, 5000);
			}
		});
		//在线升级包
		$("#upgrade_cloud").on('click',function(){
			//检查升级包之前要先检查在线升级包是否已经打开
			$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM, f:'getDefaultNoticeConf', p:{}}, function(d){
				var data = JSON.parse(d);
				var updateConf = data.update;
				//得到检查更新的开关
				var updateflag = updateConf.checkflag;
				if(updateflag =="" || updateflag == null || updateflag == undefined || updateflag =="[]"){
					updateflag = false;
				}
				if(updateflag){
					LOCATION('./content/platform/settings/settingstab/checkupdate.php','setting_manager');
				}else{
					//如果没打开就转到检查更新去
					UIToastr.showWarning(LANG.UI_SETTINGS_UPDATE_FILE, LANG.UI_SETTINGS_UPDATE_FILE_TIPS);
					$("#updateUL a:last").tab('show');
				}
			});
		});


		//取消检查更新
		$("#updatecancel").click(function(){
			LOCATION('./content/platform/settings/setting_manager.php?tab=2', 'setting_manager');
		});

		//确认检测更新
		$('#updatesubmit').on('click', function(){
			Metronic.blockUI({target: '#updatetab',animate: true});
			//先判断授权是否勾选,如果未勾选给出提示并退出
			//获取协议开关
			var updateTCP = $("#updateTCP").get(0).checked;
			//获取检查更新开关
			var updatecheck = $('#updatecheck').bootstrapSwitch('state');
			if(updatecheck && !updateTCP){
				UIToastr.showWarning(LANG.UI_SETTINGS_UPDATE_FILE, LANG.UI_SETTINGS_UPDATE_AGREEN_TIPS);
				Metronic.unblockUI('#updatetab');
				return;
			}
			var data = {};
			data.updateTCP = updateTCP;
			data.updatecheck = updatecheck;
			data.updatetime = $("#updatetime").val();
			jsonData = JSON.stringify(data);
			$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM, f:'setUpdateConf', p:jsonData}, function(d){
				Metronic.unblockUI('#updatetab');
				if(OPREL(d)){
					//如果修改成功就转到升级包管理tab去
					$("#updateUL a:first").tab('show');
				}
			});
		});


		//授权详情
		$("#TCPdetails").on('click',function(){
			$('#authDetails').modal({'width':'630px', 'height':'500px'});
		})

		//授权并继续
		$("#TCPsubmit").on('click',function(){
			$('#updatecheck').bootstrapSwitch('state', true);
			$("#updateTCP").prop("checked",true);
			$("#updateTCP").uniform.update();
			$('#authDetails').modal('hide');
		})

		//取消授权
		$("#TCPcancel").on('click',function(){
			$('#updatecheck').bootstrapSwitch('state', false);
			$("#updateTCP").prop("checked",false);
			$("#updateTCP").uniform.update();
			$('#authDetails').modal('hide');
		})

		//下载历史日志
		$("#downloadHistoryLog").on('click',function(){
			downloadHistor(historyGrid);
		})


		// 切换到升级历史tab时显示
		$('.vicon-pt_setting_history').closest('a').on('click', function () {
			if (!historyGridInitFlag) {
				historyRecords();
			}
		})
	}

	return {
		init: function () {
			//添加事件
			addListeners();
			initUploader();
			handleRecords();
			initDefaultConf(); //得到检查更新的默认配置
		}
	};

}();

jQuery(document).ready(function(){
	Settings_System_Upgrade.init();
})