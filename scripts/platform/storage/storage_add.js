var StorageAdd = function () {
	var initResourcetableFlag = false;
	var initWwntableFlag = false;
	var initLuntableFlag = false;
	var zTree,currnettreeNode,targetIqn;
	var allIscsiList = []; // 缓存所有扫描出来的二级列表
	var scanTargetFlag = false;	//扫描iscsi target
	var scanRemoteFlag = false;	//扫描异地存储
	var resultData = {};
	var iscsiData = {}; // 存储选中的iscsi
	var i = 0;
	var selectFolderFlag = false, diyRegionFlag = false;
	var vendorValidata;
	var cbrvendorValidata;
	var init_remote_flag = false; //任务表格初始化标志
	var init_offsite_flag = false; //异地存储表格初始化标志
	var chatArr = [];
	var showWormSize = true; // 是否显示worm配置的大小

	//初始化节点选择
	var initNodeSelect = function(){
		var params = {
			offset:0,
			limit:100,
			node_function: 2
		};
		pAjaxRequest(params, "/api/v1/nodes/", "GET", function (result) {
			if(0 == result.data.total){
				$('select[name=storagetype]').prop('disabled', true);
				return;
			}
			var data = result.data.rows;
			var nodeselect = $('select[name=nodeselect]');
			nodeselect.empty();
			for(var i=0; i<data.length; i++){
				var name = getNodeName(data[i].ip, data[i].node_nickname, data[i].host_name, data[i].online_flag);
				var option = $("<option>").text(name).val(data[i].node_uuid);
				nodeselect.append(option);
			}

			var nodeselect2 = $('#nodeselect2');
			nodeselect2.empty();
			for (var i = 0; i < data.length; i++) {
				var name = getNodeName(data[i].ip, data[i].node_nickname, data[i].host_name, data[i].online_flag);
				// 不允许勾选离线节点
				var option = $("<option>")
					.text(name)
					.val(data[i].node_uuid)
					.prop('selected', data[i].online_flag)
					.prop('disabled', !data[i].online_flag)
				;
				nodeselect2.append(option);
			}
			// 设置所有选项默认选中
			// nodeselect2.find('option').prop('selected', true);
			nodeselect2.selectpicker('refresh');
			// 再次设置选中状态并刷新，有时需要这样做以确保插件正确识别
			// nodeselect2.selectpicker('selectAll'); // 使用此方法选择所有选项
		});
	}

	var getNodeName = function (ip, nickname = '', hostname = '', online = true){
		let name = '';
		if (ip == nickname || (nickname == '')) {
			name = hostname;
		} else {
			name = nickname;
		}
		if (!online) {
			name = '(' + LANG.UI_STORAGE_STATUS_OFFLINE + ')' + name;
		}
		return name + '(' + ip + ')';
	}

	//添加一个ISCSI地址,多路径
	var moreiscsi = function(){
		i++;
		var html = '<div class="form-group" id="target'+ i +'">' +
			'<div class="col-md-offset-3 col-md-3">' +
			'<div class="input-icon right">' +
			'<i class="fa"></i>' +
			'<input type="text" maxlength="15" class="form-control" name="iscsiip" placeholder="192.168.1.10"/>' +
			'</div>' +
			'</div>' +
			'<div class="col-md-1">' +
			'<div class="input-icon right">' +
			'<i class="fa"></i>' +
			'<input type="text" maxlength="5" class="form-control" value="3260" name="iscsiport" placeholder="3260"/>' +
			'</div>' +
			'</div>' +
			'<button type="button" style="margin-top: 3px;" id="delete'+ i +'" class="btn btn-sm green-haze">' +
			'<i class="viconfont vicon-ge_delete"></i> ' + LANG.UI_PUBLIC_DELETE + '</button>' +
			'</div>';
		$(this).closest('.form-group').after(html);
		$('#delete' + i).on('click', {id: i}, deleteTarget);

	}

	//删除iscsi target
	var deleteTarget = function(e){
		$('#target' + e.data.id).remove();
	}

	var morenfsConfig = function(){
		$('.nfsConfigDiv').show();
	}

	var morecifsConfig = function(){
		$('.cifsConfigDiv').show();
	}
	var moreselectConfig = function(){
		$('#customParamdiv').show();
	}


	var addListeners = function(){
		$('#vendorSelect').on('change', vendorChange);
		$('select[name=nodeselect]').on('change', nodeChange);
		$('select[name=storagetype]').on('change', storageTypeChange);
		$('#cancelBut').on('click', function(){
			LOCATION('./content/platform/storage/storage.php', 'storage_manager');
		});
		$('#addsubmit').on('click', addStorageModal);
		$('#submit').on('click', addStorage);
		$('#iscsiscan').on('click', scanISCSI);
		$('#moreiscsi').on('click', moreiscsi);
		$('#morenfsparams').on('click', morenfsConfig);
		$('#morecifsparams').on('click', morecifsConfig);
		$('.moreselectparams').on('click', moreselectConfig);
		globalCheck();
		nfsDivCheck();
		cifsDivCheck();
		dddbDivCheck();
		scanISCSICheck();
		remoteDivCheck();

		cloudDivCheck();
		huaweicbrDivCheck();
		initspinner();
		$('#noticeswitch').bootstrapSwitch('onSwitchChange', function (e, data) {
			if(data){
				$('.warnningdiv').show();	//开
				noticeTypeChange();
			}else{
				$('.warnningdiv').hide();	//关
			}
		});

		$('#wormswitch').bootstrapSwitch('onSwitchChange', function (e, data) {
			if(data){
				if (showWormSize == true) {
					$('.wormdiv').show();	//开
					wormTypeChange();
				}
			}else{
				$('.wormdiv').hide();	//关
			}
		});
		$('#wormswitch').bootstrapSwitch('state', false);

		$('select[name=noticetype]').on('change', noticeTypeChange);
		$('select[name=wormtype]').on('change', wormTypeChange);

		//归档目录自定义和选择切换事件
		$('#diyTab').on('click', diyFolderDiv);
		$('#selectTab').on('click',selectFolderDiv);

		//地区自定义和选择切换事件
		$('#diyRegion').on('click', diyRegionDiv);
		$('#diyhwRegion').on('click', diyRegionhwDiv);
		$('#selectRegion').on('click',selectRegionDiv);
		$('#selecthwRegion').on('click',selecthwRegionDiv);

		//扫描Bucket
		$('#bucketscan').on('click', getFolderList);

		//扫描Container
		$('#containerscan').on('click', getFolderList);

		//选择存储用途复选框
		$('#useMode').find('.icheck').on('ifClicked', useModeClick);


		$('#datascanswitch').bootstrapSwitch('onSwitchChange', function (e, data) {
			if(data){
				$('.datascandiv').show();	//开
			}else{
				$('.datascandiv').hide();	//关
			}
		});
		//华为CBR获取区域
		$('#cbrGetArea').on('click',initHuaweiRegion);

		//扫描异地备份系统
		$('#scanRemoteStorage').on('click', getRemoteStorageList);

		// 处理云存储的功能授权控制
		if (!CONF.FUNCTIONS.includes('cloudstorage')) {
			// 没得云存储，那么去掉
			$('select[name=storagetype] option[value="9"]').remove();
		}

		// 处理WORM的功能授权控制
		if (!CONF.FUNCTIONS.includes('worm')) {
			// 没得worm，那么去掉
			$('.storagewormdiv').remove();
		}

		limitMin('#wormgpercent', 20, 99);
		limitMin('#wormsize', 1, 9999999);
	}
	function limitMin(dom, minVal, maxValue = 99) {
		let $doms = $(dom);

		$doms.each(function() {
			let $this = $(this);

			// 输入时只过滤非数字字符
			$this.off('input.limitMin').on('input.limitMin', function() {
				let value = $(this).val();
				let filteredValue = value.replace(/\D/g, '');

				// 限制长度
				let maxLength = maxValue.toString().length;
				if (filteredValue.length > maxLength) {
					filteredValue = filteredValue.substring(0, maxLength);
				}

				if (value !== filteredValue) {
					$(this).val(filteredValue);
				}
			});

			// 失去焦点时进行范围验证
			$this.off('blur.limitMin').on('blur.limitMin', function() {
				let value = $(this).val();

				if (value === '') {
					$(this).val(minVal.toString());
					$(this).trigger('change');
					return;
				}

				let numValue = parseInt(value, 10);

				if (isNaN(numValue) || numValue < minVal) {
					$(this).val(minVal.toString());
				} else if (numValue > maxValue) {
					$(this).val(maxValue.toString());
				}

				$(this).trigger('change');
			});

			// 初始验证
			let currentValue = $this.val();
			if (currentValue === '' || parseInt(currentValue) < minVal) {
				$this.val(minVal.toString());
			}
		});

		return $doms;
	}

	var useModeClick = function(event){
		var mode = $(this).data('mode');
		if(event.target.checked){
			//如果是取消选中
			$('#useMode').find('input').iCheck("uncheck");
		}else{
			$('#useMode').find('input').iCheck("uncheck");
			$('#useMode').find('input[data-mode='+ mode +']').iCheck("check");
		}
	}

	var getFolderList = function(){
		if(!$('#cloudDiv').validate().form()) return;
		var data = {};
		var vendor =  parseInt($('#vendorSelect').val());
		data.vendor = vendor;
		//如果是可选择地区
		if(!diyRegionFlag){
			data.region = $('#regionSelect').val();
		}else{
			//如果是自定义输入地区
			data.region = $('#cloudDiv').find('input[name=regiondiy]').val();
		}
		data.username= $('input[name=cloudname]').val();
		data.password = $('#cloudDiv').find('input[name=cloudpassword]').val();
		data.bucket = $('#cloudDiv').find('input[name=bucketname]').val();
		data.server_node = "";
		data.ssl_flag = false;
		//云存储类型
		switch(vendor){
			case 1:		//AWS
				data.server_node = $('#cloudDiv').find('input[name=servernode]').val();
				data.ssl_flag = $('#sslConnect').get(0).checked;
				break;
			case 2:		//Azure
				data.region = "default";
				data.username= "default";
				data.password = $('#cloudDiv').find('input[name=cloudstring]').val();
				data.bucket = $('#cloudDiv').find('input[name=containername]').val();
				break;
			case 3:		//阿里云
				data.server_node = $('#cloudDiv').find('input[name=servernode]').val();
				data.ssl_flag = $('#sslConnect').get(0).checked;
				break;
			case 4:		//华为云

				break;
			case 5:		//腾讯云

				break;
			case 10: // 其它（S3）
			case 6:		//Ceph S3
				data.server_node = $('#cloudDiv').find('input[name=servernode]').val();
				data.ssl_flag = $('#sslConnect').get(0).checked;
				data.region = $('#regioninput').val();
				break;
			case 7:		//Wasabi
				// data.region = "";
				data.server_node = $('#regionSelect').val();
				break;
			case 8:		//minio
				data.server_node = $('#cloudDiv').find('input[name=servernode]').val();
				data.ssl_flag = $('#sslConnect').get(0).checked;
				data.region = $('#regioninput').val();
				break;
			case 9:		//Huawei OceanStor Pacific
				data.server_node = $('#cloudDiv').find('input[name=servernode]').val();
				data.ssl_flag = $('#sslConnect').get(0).checked;
				break;
		}
		Metronic.blockUI({target: '#addcontent',animate: true, cenrerY: true,});
		pAjaxRequest(data, "/api/v1/storages/bucket", "GET", function (result) {
			Metronic.unblockUI('#addcontent');
			if (result.code == 0) {
				// 启用
				$('#wormswitch').bootstrapSwitch("disabled", false);
				// 判断下 azure、阿里云和腾讯云不支持worm开关 增加ceph也不支持
				// 其它云都是扫描bucket后如果是关闭的可以开启，（华为云不可更改 增加minmio和wasabi也不可更改）
				// 不管扫描出来的是什么状态，都是默认关闭的
				if ($.inArray(vendor, [2, 3, 5, 6]) === -1) {
					$('.storagewormdiv').show();
					showWormSize = false; // 不显示worm大小配置
					$('#wormswitch').bootstrapSwitch('state', false);
					// $('#wormswitch').bootstrapSwitch('state', result.data.immutable_flag);
					if ($.inArray(vendor, [4, 7, 8, 9]) !== -1 && result.data.immutable_flag == false) {
						// 华为云、minmio以及Huawei OceanStor Pacific的如果是关闭的，那么就不能开启
						$('#wormswitch').bootstrapSwitch("disabled", true);
					} else if (result.data.immutable_flag == true) {
						// 其它的如果是开启的，那么就不能关闭 Bug #27878
						$('#wormswitch').bootstrapSwitch('state', true);
						$('#wormswitch').bootstrapSwitch('disabled', true);
					}
				}

				var data = result.data.rows
				if (data.length == 0) {
					$('.selectdiv').hide();
					$('.inputdiv').show();
					selectFolderFlag = false;
					$('#addsubmit').prop('disabled', false);
					return;
				}
				var folderSelect = $('#folderSelect');
				folderSelect.empty();
				for (var i=0; i<data.length; i++) {
					var option = $("<option>").text(data[i].text).val(data[i].value);
					folderSelect.append(option);
				}
				$('.inputdiv').hide();
				$('.selectdiv').show();
				$('#addsubmit').prop('disabled', false);
				selectFolderFlag = true;
			} else {
				UIToastr.showError(LANG.UI_VOL_CDP_BACKUP_TIPS, result.message);
			}
		});
	}

	//自定义归档目录
	var diyFolderDiv = function(){
		$('.selectdiv').hide();
		$('.inputdiv').show();
		selectFolderFlag = false;
	}

	//选择归档目录
	var selectFolderDiv = function(){
		$('.inputdiv').hide();
		$('.selectdiv').show();
		selectFolderFlag = true;
	}

	//自定义地区
	var diyRegionDiv = function(){
		$('.selectRegionDiv').hide();
		$('.inputRegionDiv').show();
		diyRegionFlag = true;
	}
	var diyRegionhwDiv = function(){
		$('.selectRegionhwDiv').hide();
		$('.inputRegionhwDiv').show();
		diyRegionFlag = true;
	}

	//选择地区
	var selectRegionDiv = function(){
		$('.inputRegionDiv').hide();
		$('.selectRegionDiv').show();
		diyRegionFlag = false;
	}

	var selecthwRegionDiv = function(){
		$('.inputRegionhwDiv').hide();
		$('.selectRegionhwDiv').show();
		diyRegionFlag = false;
	}

	var noticeTypeChange = function(){
		var noticeType = $('select[name=noticetype]').val();
		if('1' == noticeType){
			$('#percentdiv').show();
			$('#sizediv').hide();
		}else if('2' == noticeType){
			$('#percentdiv').hide();
			$('#sizediv').show();
		}
	}
	var wormTypeChange = function(){
		var wormType = $('select[name=wormtype]').val();
		if('1' == wormType){
			$('#worm_percentdiv').show();
			$('#worm_sizediv').hide();
		}else if('2' == wormType){
			$('#worm_percentdiv').hide();
			$('#worm_sizediv').show();
		} else {
			$('#worm_percentdiv').hide();
			$('#worm_sizediv').hide();
		}
	}

	var initspinner = function(){
		$('#spinnerpercent').spinner({value: 20, step: 5, min: 1,max: 99});
		$('#spinnersize').spinner({value: 10, step: 10, min: 1,max: 9999999});

		$('#worm_spinnerpercent').spinner({value: 20, step: 5, min: 20,max: 99});
		$('#worm_spinnersize').spinner({value: 10, step: 10, min: 1,max: 9999999});

		$('#cloudsize').spinner({value: 10, step: 10, min: 1,max: 9999999});
		$('#cbrscantime').spinner({value:15,step:5,min:1,max:9999999});
	}

	var nodeChange = function(){
		var type = $('select[name=storagetype]').val();
		if ($.inArray(parseInt(type), [1,2,3,4]) !== -1) {
			initResourcetable();
		}
		switch(parseInt(type)){
			case 4:
				initWwnNum();
				break;
			case 5:
				initISCSIDiv(); //切换节点时重新加载iscsi信息
				break;
		}
	}

	var vendorChange = function(){
		var value = parseInt(this.value);
		$('.awsDiv').show();
		$('.regionDiv').show();
		$('.azureDiv').hide();
		$('.cephDiv').hide();
		$('.servernodeDiv').hide();
		$('.selectRegionDiv').show();
		$('.inputRegionDiv').hide();
		$('.inputRegionTips').show();
		initCloudRegion();
		switch(value){
			case 1:		//AWS
				$('.servernodeDiv').show();
				break;
			case 2:		//Azure
				$('.awsDiv').hide();
				$('.azureDiv').show();
				break;
			case 3:		//阿里云
				$('.servernodeDiv').show();
				break;
			case 4:		//华为云

				break;
			case 5:		//腾讯云

				break;
			case 10:		// 其它（S3）
			case 6:		//Ceph S3
				$('.regionDiv').hide();
				$('.cephDiv').hide();
				$('.servernodeDiv').show();
				break;
			case 7:		//Wasabi

				break;
			case 8:		//minio
				$('.regionDiv').hide();
				$('.cephDiv').hide();
				$('.servernodeDiv').show();
				break;
			case 9: //Huawei OceanStor Pacific
				$('.servernodeDiv').show();
				$('.selectRegionDiv').hide();
				$('.inputRegionDiv').show();
				$('.inputRegionTips').hide();
				break;

		}
		$('#addsubmit').prop('disabled', true);
		cloudDivCheck(value);
	}

	var getWarningSettings = function(){
		var warningSettings = {};
		warningSettings.power = $('#noticeswitch').get(0).checked;
		warningSettings.type = $('select[name=noticetype]').val();
		if('1' == warningSettings.type){
			warningSettings.value = $('#warningpercent').val();
		}else{
			warningSettings.value = $('#warningsize').val();
		}
		return warningSettings;
	}

	// worm配置获取
	var getWormSettings = function(){

		if (!CONF.FUNCTIONS.includes('worm')) {
			// 没得worm，那么去掉
			return {
				power: false,
				type: 3,
				value: 0
			};
		}

		var wormSettings = {
			power: $('#wormswitch').get(0).checked,
			type: 3,
			value: 0
		};

		if (parseInt($('select[name=storagetype]').val()) == 9) {
			// 云存储
			var vendor = parseInt($('#vendorSelect').val());
			// 判断下 azure、阿里云和腾讯云不支持worm开关  增加ceph也不支持
			if ($.inArray(vendor, [2, 3, 5, 6]) !== -1) {
				wormSettings.power = false;
			}
		} else {
			// 其它存储
			wormSettings.type = $('select[name=wormtype]').val();
			if('1' == wormSettings.type){
				wormSettings.value = $('#wormgpercent').val();
			} else if('2' == wormSettings.type) {
				wormSettings.value = $('#wormsize').val();
			} else {
				wormSettings.value = 0;
			}
		}

		return wormSettings;
	}

	//添加存储
	var addStorage = function(){
		var storageType = parseInt($('select[name=storagetype]').val());
		if(storageType == 8){
			return addCopyStorageConfirm();
		}else if(storageType == 9){
			return addCloudStorageConfirm();
		}else if (storageType == 13) {
			return addCloudhwStorageConfirm();
		}
		resultData.warning_setting = getWarningSettings();
		resultData.worm_setting = getWormSettings();
		if(resultData.warning_setting.power && resultData.warning_setting.value == 0){
			return UIToastr.showWarning(LANG.UI_STORAGE_WARNING_VALUE_EMPTY, LANG.UI_STORAGE_WARNING_VALUE_EMPTY_TIPS);
		}
		resultData.import = $('#import').is(':checked');
		resultData.format = $('#format').is(':checked');
		//resultData.allocate = $('#allocate').is(':checked');
		resultData.allocate = false;
		//resultData.autoscan = $('#autoscan').is(':checked');
		resultData.autoscan = false;
		// 如果是5 那么还需要判断下是否填写了认证信息
		if (storageType == 5) {
			// 这里处理下门户chap认证和chap认证填写的信息
			if (chatArr[iscsiData.iscsi_target] != undefined) {
				resultData.discover_chap_username = chatArr[iscsiData.iscsi_target]['username'];
				resultData.discover_chap_password = chatArr[iscsiData.iscsi_target]['password'];
			}

			if (chatArr[iscsiData.pid] != undefined) {
				resultData.chap_username = chatArr[iscsiData.pid]['username'];
				resultData.chap_password = chatArr[iscsiData.pid]['password'];
			}

			// 取出所有未选择的iqn 列表
			for (var j in allIscsiList) {
				if (allIscsiList[j].target_iqn == iscsiData.pid) {
					// 删除选中的iqn
					allIscsiList.splice(j, 1);
				}
			}
			resultData.logout_iscsi_list = allIscsiList;
			resultData.iscsi_target = iscsiData.iscsi_target;
			resultData.target_iqn = iscsiData.pid;
		}

		Metronic.blockUI({target: '#modaldiv',animate: true});
		pAjaxRequest(resultData, "/api/v1/storages/common", "POST", function (result) {
			Metronic.unblockUI('#modaldiv');
			$('#modaldiv').modal('hide');
			if (result.code == 0 || result.code == 200) {
				DataBackupCenter.updateTopAlarmTips();
				UIToastr.showSuccess(LANG.UI_STORAGE_ADD, result.message);
				LOCATION('./content/platform/storage/storage.php', 'storage_manager');
			} else {
				UIToastr.showError(LANG.UI_STORAGE_ADD, result.message);
			}
		});
	}

	//添加NAS存储
	var addNasStorage = function(){
		resultData.warning_setting = getWarningSettings();
		resultData.worm_setting = getWormSettings();
		if(resultData.warning_setting.power && resultData.warning_setting.value == 0){
			return UIToastr.showWarning(LANG.UI_STORAGE_WARNING_VALUE_EMPTY, LANG.UI_STORAGE_WARNING_VALUE_EMPTY_TIPS);
		}
		resultData.import = $('#import').is(':checked');
		resultData.format = $('#format').is(':checked');
		//resultData.allocate = $('#allocate').is(':checked');
		resultData.allocate = false;
		//resultData.autoscan = $('#autoscan').is(':checked');
		resultData.autoscan = false;

		Metronic.blockUI({target: '#addcontent',animate: true});
		pAjaxRequest(resultData, "/api/v1/storages/nas", "POST", function (result) {
			Metronic.unblockUI('#addcontent');
			if (result.code == 0 || result.code == 200) {
				var data = result.data
				if(data.hasOwnProperty('info') && data.info.hasOwnProperty('timepoint')){
					//如果有备份点信息,需要选择是否导入数据
					initImportMoadl(data.info.timepoint);
					return;
				}
				if(data.hasOwnProperty('info') && data.info.hasOwnProperty('readonly')){
					// not choose readonly and just readonly
					return UIToastr.showWarning(LANG.UI_STORAGE_ADD, LANG.UI_STORAGE_USE_MODE_READONLY_TIPS);
				}
				UIToastr.showSuccess(LANG.UI_STORAGE_ADD, result.message);
				DataBackupCenter.updateTopAlarmTips();
				LOCATION('./content/platform/storage/storage.php', 'storage_manager');
			} else {
				UIToastr.showError(LANG.UI_STORAGE_ADD, result.message);
			}
		});
	}

	// 添加并行文件系统
	var addCloudhwStorage = function (){
		resultData.warning_setting = getWarningSettings();
		resultData.worm_setting = getWormSettings();
		if(resultData.warning_setting.power && resultData.warning_setting.value == 0){
			return UIToastr.showWarning(LANG.UI_STORAGE_WARNING_VALUE_EMPTY, LANG.UI_STORAGE_WARNING_VALUE_EMPTY_TIPS);
		}

		if (resultData.username == '') {
			return UIToastr.showWarning(LANG.UI_STORAGE_FILE_SYSTEM_NAME, LANG.UI_STORAGE_FILE_SYSTEM_USERNAME_TIPS);
		}
		if (resultData.password == '') {
			return UIToastr.showWarning(LANG.UI_STORAGE_FILE_SYSTEM_NAME, LANG.UI_STORAGE_FILE_SYSTEM_PASSWORD_TIPS);
		}
		if (resultData.system_name == '') {
			return UIToastr.showWarning(LANG.UI_STORAGE_FILE_SYSTEM_NAME, LANG.UI_STORAGE_FILE_SYSTEM_NAME_TIPS);
		}

		Metronic.blockUI({target: '#addcontent',animate: true});
		pAjaxRequest(resultData, "/api/v1/storages/parallel", "POST", function (result) {
			Metronic.unblockUI('#addcontent');
			if (result.code == 0 || result.code == 200) {
				var data = result.data
				if(data.hasOwnProperty('info') && data.info.hasOwnProperty('timepoint_count')){
					//如果有备份点信息,需要选择是否导入数据
					initCloudwhModal(data.info);
					return;
				}
				DataBackupCenter.updateTopAlarmTips();
				UIToastr.showSuccess(LANG.UI_STORAGE_ADD, result.message);
				LOCATION('./content/platform/storage/storage.php', 'storage_manager');
			} else {
				UIToastr.showError(LANG.UI_STORAGE_ADD, result.message);
			}
		});
	}

	//添加云存储
	var addCloudStorage = function(){
		resultData.warning_setting = getWarningSettings();
		resultData.worm_setting = getWormSettings();
		if(resultData.warning_setting.power && resultData.warning_setting.value == 0){
			return UIToastr.showWarning(LANG.UI_STORAGE_WARNING_VALUE_EMPTY, LANG.UI_STORAGE_WARNING_VALUE_EMPTY_TIPS);
		}
		//resultData.allocate = $('#allocate').is(':checked');
		resultData.allocate = false;
		//resultData.autoscan = $('#autoscan').is(':checked');
		resultData.autoscan = false;
		Metronic.blockUI({target: '#addcontent',animate: true});
		pAjaxRequest(resultData, "/api/v1/storages/cloud", "POST", function (result) {
			Metronic.unblockUI('#addcontent');
			$('#modaldiv').modal('hide');
			if (result.code == 0 || result.code == 200) {
				var data = result.data
				if(data.hasOwnProperty('info') && data.info.hasOwnProperty('timepoint_count')){
					//如果有备份点信息,需要选择是否导入数据
					initCloudModal(data.info);
					return;
				}
				DataBackupCenter.updateTopAlarmTips();
				UIToastr.showSuccess(LANG.UI_STORAGE_ADD_CLOUD, result.message);
				LOCATION('./content/platform/storage/storage.php', 'storage_manager');
			} else {
				UIToastr.showError(LANG.UI_STORAGE_ADD_CLOUD, result.message);
			}
		});
	}

	//添加COPY存储
	var addCopyStorage = function(){
		resultData.warning_setting = getWarningSettings();
		resultData.worm_setting = getWormSettings();
		if(resultData.warning_setting.power && resultData.warning_setting.value == 0){
			return UIToastr.showWarning(LANG.UI_STORAGE_WARNING_VALUE_EMPTY, LANG.UI_STORAGE_WARNING_VALUE_EMPTY_TIPS);
		}
		//resultData.allocate = $('#allocate').is(':checked');
		resultData.allocate = false;
		//resultData.autoscan = $('#autoscan').is(':checked');
		resultData.autoscan = false;
		Metronic.blockUI({target: '#addcontent',animate: true});
		pAjaxRequest(resultData, "/api/v1/storages/remote", "POST", function (result) {
			Metronic.unblockUI('#addcontent');
			if (result.code == 0 || result.code == 200) {
				var data = result.data
				if(data.hasOwnProperty('info') && data.info.hasOwnProperty('timepoint_count')){
					//如果有备份点信息,需要选择是否导入数据
					initCopyModal(data.info);
					return;
				}

				DataBackupCenter.updateTopAlarmTips();
				UIToastr.showSuccess(LANG.UI_STORAGE_ADD_REMOTE, result.message);
				LOCATION('./content/platform/storage/storage.php', 'storage_manager');
			} else {
				UIToastr.showError(LANG.UI_STORAGE_ADD_REMOTE, result.message);
			}
		});
	}

	//确认添加 导入数据的COPY存储
	var addCopyStorageConfirm = function(){
		resultData.warning_setting = getWarningSettings();
		resultData.worm_setting = getWormSettings();
		resultData.import = $('#copyImport').is(':checked');
		//resultData.allocate = $('#allocate').is(':checked');
		resultData.allocate = false;
		//resultData.autoscan = $('#autoscan').is(':checked');
		resultData.autoscan = false;

		var remoteData = resultData;
		remoteData.confirm = 1; // 表示确定添加
		Metronic.blockUI({target: '#modaldiv',animate: true});
		pAjaxRequest(remoteData, "/api/v1/storages/remote", "POST", function (result) {
			Metronic.unblockUI('#modaldiv');
			$('#modaldiv').modal('hide');
			if (result.code == 0 || result.code == 200) {
				DataBackupCenter.updateTopAlarmTips();
				UIToastr.showSuccess(LANG.UI_STORAGE_ADD_REMOTE, result.message);
				LOCATION('./content/platform/storage/storage.php', 'storage_manager');
			} else {
				UIToastr.showError(LANG.UI_STORAGE_ADD_REMOTE, result.message);
			}
		});
	}

	//添加华为云CBR存储
	var addHuaweiCBRStorage  = function(){
		Metronic.blockUI({target: '#addcontent',animate: true});
		pAjaxRequest(resultData, "/api/v1/storages/hwcbr", "POST", function (result) {
			Metronic.unblockUI('#addcontent');
			if (result.code == 0 || result.code == 200) {
				DataBackupCenter.updateTopAlarmTips();
				UIToastr.showSuccess(LANG.UI_STORAGE_ADD, result.message);
				LOCATION('./content/platform/storage/storage.php', 'storage_manager');
			} else {
				UIToastr.showError(LANG.UI_STORAGE_ADD, result.message);
			}
		});
	}
	//初始化异地备份系统添加模态框
	var initCopyModal  = function(data){
		$('#childrendiv').hide();
		$('#formatdiv').hide();
		$('#importdiv').hide();
		//$('#autodiv').hide();
		var html = LANG.UI_STORAGE_ADD_TIPS1 + '<strong>' + data.timepoint_count + '</strong>' + LANG.UI_COPY_STORAGE_ADD_TIPS1;
		$('#copytimepointtip').html(html);
		$('#copyimportdiv').show();
		$('#submit').prop('disabled', false);
		//定义iCheck样式
		$('#modaldiv').find('input').iCheck({
			checkboxClass: 'icheckbox_square-blue',
			radioClass: 'iradio_square-blue',
			increaseArea: '20%' // optional
		});
		$('#modaldiv').modal();
	}

	//初始化云存储添加模态框
	var initCloudModal  = function(data){
		$('#childrendiv').hide();
		$('#formatdiv').hide();
		$('#importdiv').hide();
		//$('#autodiv').hide();
		var html = LANG.UI_STORAGE_ADD_TIPS1 + '<strong>' + data.timepoint_count + '</strong>' + LANG.UI_ARCHIVE_STORAGE_ADD_TIPS1;
		$('#cloudtimepointtip').html(html);
		$('#cloudimportdiv').show();
		$('#submit').prop('disabled', false);
		//定义iCheck样式
		$('#modaldiv').find('input').iCheck({
			checkboxClass: 'icheckbox_square-blue',
			radioClass: 'iradio_square-blue',
			increaseArea: '20%' // optional
		});
		$('#modaldiv').modal();
	}

	// 初始化并行文件系统添加模态框
	var initCloudwhModal = function (data){
		$('#childrendiv').hide();
		$('#formatdiv').hide();
		$('#importdiv').hide();
		//$('#autodiv').hide();
		$('#formatdiv').hide();
		var html = LANG.UI_STORAGE_ADD_TIPS1 + '<strong>' + data.timepoint_count + '</strong>' + LANG.UI_ARCHIVE_STORAGE_ADD_TIPS1;
		$('#cloudtimepointtip').html(html);
		$('#cloudimportdiv').show();
		$('#submit').prop('disabled', false);
		//定义iCheck样式
		$('#modaldiv').find('input').iCheck({
			checkboxClass: 'icheckbox_square-blue',
			radioClass: 'iradio_square-blue',
			increaseArea: '20%' // optional
		});
		$('#modaldiv').modal();
	}

	//确认添加 导入数据的云存储
	var addCloudStorageConfirm = function(){
		resultData.warning_setting = getWarningSettings();
		resultData.worm_setting = getWormSettings();
		resultData.import = $('#cloudImport').is(':checked');

		//resultData.allocate = $('#allocate').is(':checked');
		resultData.allocate = false;
		//resultData.autoscan = $('#autoscan').is(':checked');
		resultData.autoscan = false;

		var cloudData = resultData;
		cloudData.confirm = 1;// 表示确认
		Metronic.blockUI({target: '#modaldiv',animate: true});
		pAjaxRequest(cloudData, "/api/v1/storages/cloud", "POST", function (result) {
			Metronic.unblockUI('#modaldiv');
			$('#modaldiv').modal('hide');
			if (result.code == 0 || result.code == 200) {
				DataBackupCenter.updateTopAlarmTips();
				UIToastr.showSuccess(LANG.UI_STORAGE_ADD, result.message);
				LOCATION('./content/platform/storage/storage.php', 'storage_manager');
			} else {
				UIToastr.showError(LANG.UI_STORAGE_ADD, result.message);
			}
		});
	}

	//确认添加 导入数据的并行文件系统
	var addCloudhwStorageConfirm = function(){
		resultData.warning_setting = getWarningSettings();
		resultData.worm_setting = getWormSettings();
		resultData.import = $('#cloudImport').is(':checked');

		//resultData.allocate = $('#allocate').is(':checked');
		resultData.allocate = false;
		//resultData.autoscan = $('#autoscan').is(':checked');
		resultData.autoscan = false;

		var parallelData = resultData;
		parallelData.confirm = 1; // 表示确定
		Metronic.blockUI({target: '#modaldiv',animate: true});
		pAjaxRequest(parallelData, "/api/v1/storages/parallel", "POST", function (result) {
			Metronic.unblockUI('#modaldiv');
			$('#modaldiv').modal('hide');
			if (result.code == 0 || result.code == 200) {
				DataBackupCenter.updateTopAlarmTips();
				UIToastr.showSuccess(LANG.UI_STORAGE_ADD, result.message);
				LOCATION('./content/platform/storage/storage.php', 'storage_manager');
			} else {
				UIToastr.showError(LANG.UI_STORAGE_ADD, result.message);
			}
		});

	}

	//添加存储按钮事件
	var addStorageModal = function(){
		var checkGlobalResult = $('#addnodeform').validate().form();
		if(!checkGlobalResult) return false;
		var storageType = parseInt($('select[name=storagetype]').val());
		resultData.node_uuid = $('select[name=nodeselect]').val();
		resultData.storage_type = storageType;
		//适配华为OceanProtect备份存储传值
		if(storageType == 101){
			//nfs
			resultData.storage_type = 6;
		}else if(storageType == 102){
			//cifs
			resultData.storage_type = 7;
		}
		if ($.inArray(resultData.storage_type, [6, 7, 9]) !== -1) {
			// nfs 和cifs有节点选择 还有云存储
			resultData.node_list = $('#nodeselect2').selectpicker('val');
			if (resultData.node_list.length <= 0) {
				UIToastr.showInfo(LANG.UI_STORAGE_ADD, LANG.UI_NAS_MANAGE_AT_LEAST_ONE_NODE);
				return;
			}
		}
		resultData.rname = $('input[name=rname]').val();
		resultData.use_mode = 1; // 默认为1，因为屏蔽了备份和副本归档的用途
		//如果不是华为CBR，则需要判断存储用途
		if(storageType != 12){
			/*if($('#backupCheck').is(':checked')){
				resultData.use_mode = 1;
			}else if($('#copyCheck').is(':checked')){
				resultData.use_mode = 2;
			}else if($('#archiveCheck').is(':checked')){
				resultData.use_mode = 3;
			}else if($('#readonlyCheck').is(':checked')){
				resultData.use_mode = 5;
			}else if(!$('#backupCheck').is(':checked') && !$('#copyCheck').is(':checked') && !$('#archiveCheck').is(':checked')){
				UIToastr.showWarning(LANG.UI_STORAGE_USE_MODE_SELECT, LANG.UI_STORAGE_USE_MODE_SELECT_TIPS);
				return false;
			}*/
			if($('#readonlyCheck').is(':checked')){
				resultData.use_mode = 5;
			}
		}
		switch(storageType){
			case 1:
			case 2:
			case 3:
			case 4:
				if(!moreDivCheck()) return;
				var select = $('#resourcetable').bootstrapTable('getSelections');
				resultData.storage_name = select[0]['storage_name'];
				resultData.mount_params = $('#customConfig').val();
				initModal(select, 4);
				break;
			case 5:
				if(!iscsiDivCheck()) return;
				if(!checkAllIscsiIpPort()) return;
				var serverList = [];
				var ipInput = $('input[name=iscsiip]');
				var portInput = $('input[name=iscsiport]');
				for(var i=0; i<ipInput.length; i++){
					serverList[i] = {ip:$(ipInput[i]).val(), port:$(portInput[i]).val()};
				}
				resultData.server_list = serverList;
				resultData.lun = iscsiData.id;
				$('#import').iCheck('uncheck');
				$('#format').iCheck('uncheck');
				resultData.mount_params = $('#customConfig').val();
				initModal(iscsiData, 5);
				break;
			case 6:
			case 101:
				if(!$('#nfsdiv').validate().form()) return;
				resultData.host = $('#nfsdiv').find('input[name=host]').val();
				resultData.mount_params = $('#nfsConfig').val();
				$('#import').iCheck('uncheck');
				$('#format').iCheck('uncheck');
				addNasStorage();
				break;
			case 7:
			case 102:
				if(!$('#cifsdiv').validate().form()) return;
				resultData.host = $('#cifsdiv').find('input[name=host]').val();
				resultData.mount_params = $('#cifsConfig').val();
				resultData.username = $('#cifsdiv').find('input[name=username]').val();
				resultData.password = $('#cifsdiv').find('input[name=password]').val();
				$('#import').iCheck('uncheck');
				$('#format').iCheck('uncheck');
				addNasStorage();
				break;
			case 8:
				//TODO,检查输入
				if(!$('#copydiv').validate().form()) return;
				//获取异地存储的信息
				var select = getIdSelectedId('#remote_storage_table');
				if (!select.length || select.length > 1) {
					UIToastr.showInfo(LANG.UI_STORAGE_SELECT_REMOTE, LANG.UI_STORAGE_SELECT_REMOTE_TIPS);
					return;
				}
				if(!dataTableSelectCheck()) return;
				resultData.remoteip = $('#copydiv').find('input[name=remoteip]').val();
				resultData.remote_port = $('#copydiv').find('input[name=remoteport]').val();
				resultData.username = $('#copydiv').find('input[name=username]').val();
				var password = $('#copydiv').find('input[name=password]').val();
				resultData.password = btoa(password);
				var storage_list = {};
				storage_list.storage_uuid = select[0]
				resultData.storage_list_uuid = [];
				resultData.storage_list_uuid.push(storage_list);
				$('#import').iCheck('uncheck');
				$('#format').iCheck('uncheck');
				addCopyStorage();
				break;
			case 9:
				if(!$('#cloudDiv').validate().form()) return;
				var vendor = parseInt($('#vendorSelect').val());
				resultData.vendor = vendor;
				//如果是可选择地区
				if(!diyRegionFlag){
					resultData.region = $('#regionSelect').val();
				}else{
					//如果是自定义输入地区
					resultData.region = $('#cloudDiv').find('input[name=regiondiy]').val();
				}
				resultData.username = $('#cloudDiv').find('input[name=cloudname]').val();
				resultData.password = $('#cloudDiv').find('input[name=cloudpassword]').val();
				resultData.bucket = $('#cloudDiv').find('input[name=bucketname]').val();
				resultData.server_node = "";
				resultData.ssl_flag = false;
				resultData.limit_size = $('#limitsize').val();
				if(resultData.limit_size == 0 || resultData.limit_size == ''){
					UIToastr.showInfo(LANG.UI_STORAGE_ADD, LANG.UI_TENANT_CAPACITY_NOT_NULL);
					return false;
				}
				if (resultData.limit_size > 8388608) {
					UIToastr.showInfo(LANG.UI_STORAGE_ADD, LANG.UI_TENANT_CAPACITY_MAX_NUM);
					return false;
				}
				//云存储类型
				switch(vendor){
					case 1:		//AWS
						resultData.server_node = $('#cloudDiv').find('input[name=servernode]').val();
						resultData.ssl_flag = $('#sslConnect').get(0).checked;
						break;
					case 2:		//Azure
						//Azure用默认地区和用户名
						resultData.region = "default";
						resultData.username = "default";
						resultData.password = $('#cloudDiv').find('input[name=cloudstring]').val();
						resultData.bucket = $('#cloudDiv').find('input[name=containername]').val();
						break;
					case 3:		//阿里云
						resultData.server_node = $('#cloudDiv').find('input[name=servernode]').val();
						resultData.ssl_flag = $('#sslConnect').get(0).checked;
						break;
					case 4:		//华为云

						break;
					case 5:		//腾讯云

						break;
					case 10:		//其它（S3）
					case 6:		//Ceph S3
						resultData.region = $('#regioninput').val();
						resultData.server_node = $('#cloudDiv').find('input[name=servernode]').val();
						resultData.ssl_flag = $('#sslConnect').get(0).checked;
						break;
					case 7:		//Wasabi
						// resultData.region = "";
						resultData.server_node = $('#regionSelect').val();
						break;
					case 8:		//minio
						resultData.server_node = $('#cloudDiv').find('input[name=servernode]').val();
						resultData.ssl_flag = $('#sslConnect').get(0).checked;
						break;
					case 9:		//Huawei OceanStor Pacific
						resultData.server_node = $('#cloudDiv').find('input[name=servernode]').val();
						resultData.ssl_flag = $('#sslConnect').get(0).checked;
						break;
				}
				if(selectFolderFlag){
					resultData.folder = $('#folderSelect').val();
					resultData.diy_flag  = false;
				}else{
					resultData.folder = $('#cloudDiv').find('input[name=folderinput]').val();
					resultData.diy_flag  = true;
				}
				if(!resultData.folder || resultData.folder == ""){
					UIToastr.showInfo(LANG.UI_STORAGE_ADD, LANG.UI_STORAGE_ADD_CLOUD_NO_FOLDER);
					return false;
				}
				//if(resultData.folder.includes('/') || resultData.folder.includes('\\')){
				if (selectFolderFlag == false) {
					// 表示自定义 那么就要校验
					if(/^\//.test(resultData.folder) || /\/$/.test(resultData.folder) || resultData.folder == '/' || resultData.folder.includes('\\')){
						UIToastr.showInfo(LANG.UI_STORAGE_ADD, LANG.UI_STORAGE_ADD_CLOUD_NO_FOLDER2);
						return false;
					}
				}

				$('#import').iCheck('uncheck');
				$('#format').iCheck('uncheck');
				addCloudStorage();
				break;
			case 11:
				// if(!$('#nfsdiv').validate().form()) return;
				resultData.dirname = $('#dirName').val();
				// 本地目录 不允许使用 / /boot /sys 等系统目录不应该作为备份目录添加
				if ($.inArray(resultData.dirname, ['/', '/boot', '/sys', '/bin', ' /dev', '/etc', '/var', '/run', '/proc', '/lib']) !== -1){
					UIToastr.showInfo(LANG.UI_STORAGE_ADD, LANG.UI_STORAGE_ADD_LOCAL_DIR_TIPS);
					return false;
				}
				$('#import').iCheck('uncheck');
				$('#format').iCheck('uncheck');
				addNasStorage();
				break;
			case 13: // 并行文件系统
				if(!$('#cloudhwDiv').validate().form()) return;
				var vendor = parseInt($('#vendorhwSelect').val());
				resultData.vendor = vendor;
				//如果是可选择地区
				if(!diyRegionFlag){
					resultData.region = $('#regionhwSelect').val();
				}else{
					//如果是自定义输入地区
					resultData.region = $('#cloudhwDiv').find('input[name=regionhwdiy]').val();
				}
				resultData.username = $('#cloudhwDiv').find('input[name=cloudname]').val();
				resultData.password = $('#cloudhwDiv').find('input[name=cloudpassword]').val();
				resultData.system_name = $('#cloudhwDiv').find('input[name=system_name]').val();
				resultData.server_node = "";
				resultData.ssl_flag = false;
				resultData.limit_size = $('#limitsize').val();
				if(resultData.limit_size == 0 || resultData.limit_size == ''){
					UIToastr.showInfo(LANG.UI_STORAGE_ADD, LANG.UI_TENANT_CAPACITY_NOT_NULL);
					return false;
				}

				addCloudhwStorage();
				break;
			//华为CBR
			case 12:
				//用弹窗提示是否选择了区域
				huaweicbrDivCheck(2);
				var cbrarealist =  $('select[name=hwregionlist]').val();
				if(cbrarealist == null || cbrarealist.length == 0){
					UIToastr.showInfo(LANG.UI_STORAGE_ADD, LANG.UI_STORAGE_SELECT_REGION);
					return false;
				}
				if(!$('#huaweicbrdiv').validate().form()) return;

				resultData.cbraccessid = $('#huaweicbrdiv').find('input[name=cbraccessid]').val();
				resultData.cbraccesskey = btoa($('#huaweicbrdiv').find('input[name=cbraccesskey]').val());
				resultData.cbrarea = $('select[name=hwregionlist]').val();
				resultData.cbrstorageid = $('#huaweicbrdiv').find('input[name=cbrstorageid]').val();
				//用户管理员ak ,sk ,授权的用户名
				resultData.cbruserak = $('#huaweicbrdiv').find('input[name=huaweiuserak]').val();
				resultData.cbrusersk = btoa($('#huaweicbrdiv').find('input[name=huaweiusersk]').val());
				var cbrusername =   $('#huaweicbrdiv').find('input[name=huaweiusername]').val();
				var cbrusernamelist =  cbrusername.split(",")
				resultData.cbrusername =  cbrusernamelist;
				resultData.scandata_settings ={};
				resultData.scandata_settings.power =  $('#datascanswitch').get(0).checked;
				resultData.scandata_settings.value  = $('#cbrscantimevalue').val();
				if(!resultData.scandata_settings.power){
					resultData.scandata_settings.value  =0;
				}
				if(resultData.scandata_settings.power && resultData.scandata_settings.value == 0){
					return UIToastr.showWarning(LANG.UI_STORAGE_SCAN_DATA_INTERVAL,LANG.UI_STORAGE_SCAN_DATA_INTERVAL_TIPS);
				}
				addHuaweiCBRStorage();
				break;
			case 16:
				if(!$('#dddbdiv').validate().form()) return;
				resultData.data_domain_system = $('#dddbdiv').find('input[name=data_domain_system]').val();
				resultData.storage_unit = $('#dddbdiv').find('input[name=storage_unit]').val();
				// user/kerberos 字符串，目前只传入user即通过用户名密码认证
				resultData.auth_type = 'user';
				resultData.username = $('#dddbdiv').find('input[name=username]').val();
				resultData.password = $('#dddbdiv').find('input[name=password]').val();
				$('#import').iCheck('uncheck');
				$('#format').iCheck('uncheck');
				addNasStorage();
				break;
		}
	}

	//初始化NAS有数据的模态框
	var initImportMoadl = function(timepoint){
		$('#childrendiv').hide();
		$('#formatdiv').hide();
		$('#submit').prop('disabled', false);
		var html = LANG.UI_STORAGE_ADD_TIPS1 + '<strong>' + timepoint + '</strong>' + LANG.UI_STORAGE_ADD_TIPS2;
		$('#timepointtip').html(html);
		$('#importdiv').show();
		$('#autodiv').show();
		//$('#allocate').iCheck('disable');
		$('#modaldiv').modal();
		//定义iCheck样式
		$('#modaldiv').find('input').iCheck({
			checkboxClass: 'icheckbox_square-blue',
			radioClass: 'iradio_square-blue',
			increaseArea: '20%' // optional
		});
	}

	//初始化模态框
	var initModal = function(storage, num){
		if (num == 5) {
			var name = storage['name'];
			var moreInfo = storage;
		} else {
			var moreInfo = storage[0];
			var name = storage[0]['storage_name'];
		}

		//hypervisor提示,如果检测到存储被生产系统使用过
		$('#format').prop('disabled', false);
		if(moreInfo.hypervisor_used){
			var html = '<i class="fa fa-warning "></i> ';
			html += LANG.UI_STORAGE_INSPECT_ADD_BACKUP + ' <strong>' + name + '</strong> <br>';
			html += LANG.UI_STORAGE_PRODUCT_ENVIRONMENT  + LANG.UI_STORAGE_USED;
			html += LANG.UI_STORAGE_CONFIRM_ADD;
			html += '<br><br>' + LANG.UI_STORAGE_CONFIG_LAN;
			$('#hypervisortip').html(html);
			$('#hypervisortip').show();
			$('#format').prop('disabled', true);
		}else{
			$('#hypervisortip').hide();
		}

		//子设备提示
		var children = moreInfo.childrens != undefined ? moreInfo.childrens : moreInfo.children;

		if(0 == children.length){
			$('#childrentip').hide();
		}else{
			var html = '<i class="viconfont vicon-ge_summary"></i> ';
			html += LANG.UI_STORAGE_ADD_TIPS3 + ' <strong>' + name + '</strong> ';
			html += LANG.UI_STORAGE_ADD_TIPS4 + '<br>';
			for(var i=0; i<children.length; i++){
				html += '<strong>' + children[i] + '</strong><br>';
			}
			html += "<br>" + LANG.UI_STORAGE_ADD_TIPS6;
			$('#childrentip').html(html);
			$('#childrentip').show();
		}
		//备份数据导入
		if(moreInfo.timepointcount > 0){
			var html = LANG.UI_STORAGE_ADD_TIPS1 + '<strong>' + moreInfo.timepointcount + '</strong>';
			if($('input[name=storagetype]').val() == 8){
				html += LANG.UI_COPY_STORAGE_ADD_TIPS2;
			}else{
				html += LANG.UI_STORAGE_ADD_TIPS2;
			}
			$('#timepointtip').html(html);
			$('#importdiv').show();
			$('#autodiv').show();
			//$('#allocate').iCheck('disable');
		}else{
			$('#importdiv').hide();
			//$('#autodiv').hide();
		}
		//格式化存储
		$('#format').iCheck('uncheck');
		$('#submit').prop('disabled', false);
		//定义iCheck样式
		$('#modaldiv').find('input').iCheck({
			checkboxClass: 'icheckbox_square-blue',
			radioClass: 'iradio_square-blue',
			increaseArea: '20%' // optional
		});
		if(!moreInfo.initflag){
			$('#submit').prop('disabled', true);
			$('#format').on('ifChecked', function(event){
				$('#submit').prop('disabled', false);
			});
			$('#format').on('ifUnchecked', function(event){
				$('#submit').prop('disabled', true);
			});
		}
		$('#formatdiv').show();
		$('#copyimportdiv').hide();
		$('#modaldiv').modal();

		$('#import').on('ifChecked', function(event){
			$('#format').iCheck('uncheck');
			// $('#allocate').iCheck('enable');
		});
		$('#import').on('ifUnchecked', function(event){
			// 	$('#format').iCheck('enable');
			// $('#allocate').iCheck('disable');
			// $('#allocate').iCheck('uncheck');
		});
		$('#format').on('ifChecked', function(event){
			$('#import').iCheck('uncheck');
		});
	}

	//全局输入验证
	var globalCheck = function(){
		$('#addnodeform').validate({
			errorElement: 'span', //default input error message container
			errorClass: 'help-block help-block-error', // default input error message class
			focusInvalid: false, // do not focus the last invalid input
			ignore: "",  // validate all fields including form hidden input
			rules: {
				nodeselect: {
					required: true,
				},
				storagetype:{
					required: true,
					storageType: true,
				}
			},

			errorPlacement: function (error, element) { // render error placement for each input type
				var icon = $(element).parent('.input-icon').children('i');
				icon.removeClass('fa-check').addClass("fa-warning");
				icon.attr("data-original-title", error.text()).tooltip({'container': 'body'});
			},

			highlight: function (element) { // hightlight error inputs
				$(element)
					.closest('.form-group').removeClass("has-success").addClass('has-error'); // set error class to the control group
			},

			success: function (label, element) {
				var icon = $(element).parent('.input-icon').children('i');
				$(element).closest('.form-group').removeClass('has-error').addClass('has-success'); // set success class to the control group
				icon.removeClass("fa-warning").addClass("fa-check");
			}
		});
	}

	//通用存储输入验证
	var moreDivCheck = function(){
		var select = $('#resourcetable').bootstrapTable('getSelections');
		if(!select.length){
			UIToastr.showInfo(LANG.UI_STORAGE_SELECT_RESOURCE, LANG.UI_STORAGE_SELECT_RESOURCE_TIPS);
			return false;
		}
		return true;
	}

	//ISCSI输入验证
	var iscsiDivCheck = function(){
		if(!$('#iscsidiv').validate().form()) return false;

		if(!scanTargetFlag){
			//没有扫描
			UIToastr.showInfo(LANG.UI_STORAGE_ADD_ISCSI_TARGET, LANG.UI_STORAGE_ADD_TARGET_IQN);
			return false;
		}

		if(!iscsiData.id){
			UIToastr.showInfo(LANG.UI_STORAGE_ADD_TARGET_LUN, LANG.UI_STORAGE_ADD_TARGET_SELECT);
			return false;
		}
		resultData.lun = iscsiData.id;
		return true;
	}

	//nfs输入验证
	var nfsDivCheck = function(){
		$('#nfsdiv').validate({
			errorElement: 'span', //default input error message container
			errorClass: 'help-block help-block-error', // default input error message class
			focusInvalid: false, // do not focus the last invalid input
			ignore: "",  // validate all fields including form hidden input
			rules: {
				host: {
					required: true,
					nfspath: true,
				}
			},

			errorPlacement: function (error, element) { // render error placement for each input type
				var icon = $(element).parent('.input-icon').children('i');
				icon.removeClass('fa-check').addClass("fa-warning");
				icon.attr("data-original-title", error.text()).tooltip({'container': 'body'});
			},

			highlight: function (element) { // hightlight error inputs
				$(element)
					.closest('.form-group').removeClass("has-success").addClass('has-error'); // set error class to the control group
			},

			success: function (label, element) {
				var icon = $(element).parent('.input-icon').children('i');
				$(element).closest('.form-group').removeClass('has-error').addClass('has-success'); // set success class to the control group
				icon.removeClass("fa-warning").addClass("fa-check");
			}
		});

		$.validator.addMethod("nfspath", function(value, element) {
			// 匹配 :/ （NFS 路径标志）
			var reSeparator = ':\\/';

			// 后面任意内容（允许空）
			var rePathTail = '.*';

			// 精确匹配 IPv4 地址
			var reIP = '(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)';

			// 匹配主机名（字母、数字、-、. 组成）
			var reHost = '[a-zA-Z0-9]([a-zA-Z0-9\\-]*[a-zA-Z0-9])?(\\.[a-zA-Z0-9]([a-zA-Z0-9\\-]*[a-zA-Z0-9])?)*';

			// 前半部分：IP 或 host
			var reFront = '(?:' + reIP + '|' + reHost + ')';

			// 组合成完整正则字符串
			var patternStr = '^' + reFront + reSeparator + rePathTail + '$';

			// 创建正则对象
			var regex = new RegExp(patternStr, 'i');

			// 执行测试
			return regex.test(value);
		}, LANG.UI_STORAGE_ADD_PATH_TIPS);
	}

	//cifs输入验证
	var cifsDivCheck = function(){
		$('#cifsdiv').validate({
			errorElement: 'span', //default input error message container
			errorClass: 'help-block help-block-error', // default input error message class
			focusInvalid: false, // do not focus the last invalid input
			ignore: "",  // validate all fields including form hidden input
			rules: {
				host: {
					required: true,
					cifspath: true,
				}
			},

			errorPlacement: function (error, element) { // render error placement for each input type
				var icon = $(element).parent('.input-icon').children('i');
				icon.removeClass('fa-check').addClass("fa-warning");
				icon.attr("data-original-title", error.text()).tooltip({'container': 'body'});
			},

			highlight: function (element) { // hightlight error inputs
				$(element)
					.closest('.form-group').removeClass("has-success").addClass('has-error'); // set error class to the control group
			},

			success: function (label, element) {
				var icon = $(element).parent('.input-icon').children('i');
				$(element).closest('.form-group').removeClass('has-error').addClass('has-success'); // set success class to the control group
				icon.removeClass("fa-warning").addClass("fa-check");
			}
		});
		$.validator.addMethod("cifspath", function(value, element) {
			// 正则说明：
			// ^\/\/            : 必须以 // 开头
			// (                : 开始分组（主机部分）
			//   (?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)  // IPv4
			//   |              : 或
			//   [a-zA-Z0-9]([a-zA-Z0-9\-]*[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9\-]*[a-zA-Z0-9])?)*  // 域名
			// )
			// \/               : 必须跟一个 /
			// .*               : 后面任意内容（不限制）
			// $                : 结束

			var pattern = /^\/\/(?:(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)|[a-zA-Z0-9]([a-zA-Z0-9\-]*[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9\-]*[a-zA-Z0-9])?)*)\/.*$/i;

			return pattern.test(value);
		}, LANG.UI_STORAGE_ADD_PATH_TIPS);
	}

	//dddb输入验证
	var dddbDivCheck = function(){
		$('#dddbdiv').validate({
			errorElement: 'span', //default input error message container
			errorClass: 'help-block help-block-error', // default input error message class
			focusInvalid: false, // do not focus the last invalid input
			ignore: "",  // validate all fields including form hidden input
			rules: {
				data_domain_system: {
					required: true
				},
				storage_unit: {
					required: true
				},
				username: {
					required: true
				},
				password: {
					required: true
				},
			},

			errorPlacement: function (error, element) { // render error placement for each input type
				var icon = $(element).parent('.input-icon').children('i');
				icon.removeClass('fa-check').addClass("fa-warning");
				icon.attr("data-original-title", error.text()).tooltip({'container': 'body'});
			},

			highlight: function (element) { // hightlight error inputs
				$(element)
					.closest('.form-group').removeClass("has-success").addClass('has-error'); // set error class to the control group
			},

			success: function (label, element) {
				var icon = $(element).parent('.input-icon').children('i');
				$(element).closest('.form-group').removeClass('has-error').addClass('has-success'); // set success class to the control group
				icon.removeClass("fa-warning").addClass("fa-check");
			}
		});
	}
	//选择存储类型事件
	var storageTypeChange = function(){
		var value = parseInt($(this).val());
		//切换存储还原初始选项配置
		$('.wwndiv').hide();
		$('#selecttips').hide();
		$('#usemodeDiv').show();
		$('#backupCheck').iCheck('enable');
		$('#copyCheck').iCheck('enable');
		$('#backupCheck').iCheck('uncheck');
		$('#copyCheck').iCheck('uncheck');
		$('#autoscan').iCheck('enable');
		$('#allocate').iCheck('enable');
		$('#autoscan').iCheck('uncheck');
		$('#allocate').iCheck('uncheck');

		$('.nfsConfigDiv').hide();
		$('.cifsConfigDiv').hide();

		$('#customParamdiv').hide();

		$('.servernodeDiv').hide();
		$('.cloudwarningdiv').hide();
		$('.storagewarningdiv').show();
		$('.hwdatascandiv').hide();
		$('.readonlyDiv').hide();
		$('#addsubmit').prop('disabled', false);

		// 显示worm配置
		$('.storagewormdiv').show();
		showWormSize = true; // 显示worm大小配置
		// 启用
		$('#wormswitch').bootstrapSwitch("disabled", false);

		$('#node2Div').hide();
		$('#cloudDiv').hide();

		switch(value){
			case 0:
				UIToastr.showInfo(LANG.UI_STORAGE_SELECT_TYPE, LANG.UI_STORAGE_SELECT_TYPE_TIPS);
				return;
			case 1:
				$('#nodeDiv').show();
				initMoreStorageDiv();
				initResourcetable();
				$('#selecttips').show();
				//$('#customParamdiv').show();
				break;
			case 2:
			case 3:
				$('#nodeDiv').show();
				initMoreStorageDiv();
				initResourcetable();
				//$('#customParamdiv').show();
				break;
			case 4:
				$('#nodeDiv').show();
				initWwnNum();
				initMoreStorageDiv();
				initResourcetable();
				$('#selecttips').show();
				//$('#customParamdiv').show();
				break;
			case 5:
				$('#nodeDiv').show();
				initISCSIDiv();
				//$('#customParamdiv').show();
				break;
			case 6:
			case 101:
				$('#nodeDiv').hide();
				$('#node2Div').show();
				$('.readonlyDiv').show();
				initNFSDiv();
				break;
			case 7:
			case 102:
				$('#nodeDiv').hide();
				$('#node2Div').show();
				$('.readonlyDiv').show();
				initCIFSDiv();
				break;
			case 8:
				$('#addsubmit').prop('disabled', true);
				$('#backupCheck').iCheck('uncheck');
				$('#backupCheck').iCheck('disable');
				$('#copyCheck').iCheck('check');
				$('#copyCheck').iCheck('disable');
				$('#autoscan').iCheck('uncheck');
				$('#autoscan').iCheck('disable');
				$('#allocate').iCheck('uncheck');
				$('#allocate').iCheck('disable');
				$('#nodeDiv').hide();
				initCOPYDiv();
				if (init_offsite_flag) {
					$('#addsubmit').prop('disabled', false);
				}
				break;
			case 9:
				$('#addsubmit').prop('disabled', true);
				initCloudDiv();
				// $('#backupCheck').iCheck('uncheck');
				// $('#backupCheck').iCheck('disable');
				$('#backupCheck').iCheck('enable');
				$('#copyCheck').iCheck('uncheck');
				$('#copyCheck').iCheck('enable');
				$('#nodeDiv').hide();
				$('.storagewarningdiv').hide();
				$('.cloudwarningdiv').show();
				$('#node2Div').show();
				initCloudRegion();
				//AWS、Ceph S3
				var type = $('#vendorSelect').val();
				if(type == 1 || type == 6 || type == 10){
					$('.servernodeDiv').show();	//显示服务终端节点
				}
				// 云存储先隐藏worm配置 根据扫描的bucket控制显示
				$('.storagewormdiv').hide();
				break;
			case 11:
				$('#nodeDiv').show();
				$('.readonlyDiv').show();
				initLocaldirDiv();
				break;
			case 13:
				initCloudhwDiv();
				$('#nodeDiv').show();
				$('.storagewarningdiv').hide();
				$('.cloudwarningdiv').show();
				initCloudhwRegion();
				break;
			case 12:
				$('#addsubmit').prop('disabled', true);
				$('#nodeDiv').hide();
				$('#usemodeDiv').hide();
				$('.storagewarningdiv').hide();
				$('.hwdatascandiv').show();
				initHuaweiCBRDiv();
				initHuaweiScanTime();
				initHuaweiCBRArea();
				break;
			case 16: // DELL Data Domain Boost
				$('#nodeDiv').show();
				$('#node2Div').hide();
				$('.readonlyDiv').show();
				initDddbDiv();
				break;
		}
		getStorageName();
	}
	//初始化华为region
	var initHuaweiRegion =  function(){
		huaweicbrDivCheck();//改变华为CBR校验规则
		var idflag = $('#huaweicbrdiv').validate().element($('input[name=cbraccessid]'));
		var nameflag = $('#huaweicbrdiv').validate().element($('input[name=cbraccesskey]'));
		if(!idflag || !nameflag) return;

		var regionSelect = $('#hwregion_list');
		var data = {
			access_key_id :$('#huaweicbrdiv').find('input[name=cbraccessid]').val(),
			secret_access_key:btoa($('#huaweicbrdiv').find('input[name=cbraccesskey]').val())
		}
		regionSelect.empty();
		regionSelect.html('');
		Metronic.blockUI({target: '#addcontent',animate: true});
		pAjaxRequest(data, "/api/v1/storages/cbr", "GET", function (result) {
			Metronic.unblockUI('#addcontent');
			if (result.code == 0) {
				var data = result.data.rows
				$('#addsubmit').prop('disabled', false);
				for(var i=0;i<data.length;i++){
					var option  =""
					if(CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw"){
						option = $('<option>').text(data[i].namecn).val(data[i].id);
					}else{
						option = $('<option>').text(data[i].nameen).val(data[i].id);
					}
					regionSelect.append(option);
				}
				regionSelect.selectpicker({
					noneSelectedText: LANG.BILLING_PLEASE_SELECT,
					deselectAllText: LANG.BILLING_DESELECT_ALL,
					selectAllText: LANG.BILLING_SELECT_ALL,
					liveSearchPlaceholder: LANG.BILLING_SEARCH,
					countSelectedText: function(){}
				});
				regionSelect.selectpicker('refresh');
				huaweicbrDivCheck(2);//改变华为CBR校验规则
			} else {
				$('#addsubmit').prop('disabled', true);
				regionSelect.selectpicker({
					noneSelectedText: LANG.BILLING_PLEASE_SELECT,
					deselectAllText: LANG.BILLING_DESELECT_ALL,
					selectAllText: LANG.BILLING_SELECT_ALL,
					liveSearchPlaceholder: LANG.BILLING_SEARCH,
					countSelectedText: function(){}
				});
				regionSelect.selectpicker('refresh');
				UIToastr.showError(LANG.UI_STORAGE_TYPE_HUAWEI_CBR, result.message);
			}
		});
	}
	//初始化华为数据扫描间隔时间插件
	var initHuaweiScanTime =  function(){
		$('.datascanTime').timepicker({
			autoclose: true,
			minuteStep: 5,
			showSeconds: true,
			showMeridian: false
		});
		$('.datascanTime').parent('.input-group').on('click', '.input-group-btn', function(e){
			e.preventDefault();
			$(this).parent('.input-group').find('.timepicker').timepicker('showWidget');
		});
	}
	//初始化华为区域
	var initHuaweiCBRArea  = function(){
		var regionSelect = $('#hwregion_list');
		regionSelect.empty()
	}

	//初始化region
	var initCloudRegion = function(id = 'regionSelect', opid = 'vendorSelect'){
		var vendor = $('#'+opid).val();
		pAjaxRequest({vendor: vendor}, "/api/v1/storages/region", "GET", function (result) {
			if (result.code == 0) {
				var data = result.data.rows;
				var regionSelect = $('#'+id);
				regionSelect.empty();
				for(var i=0; i<data.length; i++){
					var option = $("<option>").text(data[i].text).val(data[i].value);
					regionSelect.append(option);
				}
				if(vendor == 3){	//阿里云
					regionSelect.val('oss-cn-beijing.aliyuncs.com');
				}
			}
		});
	}

	// 初始化华为region
	var initCloudhwRegion = function (){
		initCloudRegion('regionhwSelect', 'vendorhwSelect');
	}

	//得到存储的名字
	var getStorageName = function(){
		var data = {storage_type:$('select[name=storagetype]').val()};
		pAjaxRequest(data, "/api/v1/storages/name", "GET", function (result) {
			if (result.code == 0) {
				$('input[name=rname]').val(result.data.name);
			} else {
				UIToastr.showError(LANG.UI_STORAGE_ADD, result.message);
			}
		});
	}

	//初始化节点WWN号
	var initWwnNum = function(){
		$('.wwndiv').show();
		var table = $("#wwntable");
		if(!initWwntableFlag){
			initWwntableFlag = true;
			var options = {
				pagination:false,
				resizable: false,
				singleSelect:true,
				vin_url:"/api/v1/storages/wwn",
				vin_method:"GET",
				vin_params: function () {
					let params = {};
					params.node_uuid = $('select[name=nodeselect]').val();
					return params;
				},
				columns:[{
					checkbox:true,
					sortable: false,
				},
					{
						field: 'num',
						title: LANG.UI_PUBLIC_TABLE_ID,
						sortable: false,
						align: 'center',
					},
					{
						field: 'host_name',
						title: LANG.UI_STORAGE_CHANNEL,
						sortable: false,
						align: 'center',
					},
					{
						field: 'wwnn',
						title: LANG.UI_STORAGE_FC_WWNN,
						sortable: false,
						align: 'center',
					},
					{
						field: 'wwpn',
						title: LANG.UI_STORAGE_FC_WWPN,
						sortable: false,
						align: 'center',
					},
					{
						field: 'speed',
						title: LANG.UI_STORAGE_FC_SPEED,
						sortable: false,
						align: 'center',
					},
					{
						field: 'status',
						title: LANG.UI_PUBLIC_STATUS,
						sortable: false,
						formatter: function (index, row) {
							var levelClass = '';
							switch (row.status) {
								case 1:
									levelClass = "label-success";
									break;
								case 2:
									levelClass = "label-warning";
									break;
								default:
									levelClass = "label-info";
									break;
							}

							return '<span class="label label-sm '+ levelClass +' "> ' + row.status_des + ' </span>';
						}
					},
				],
				onPostBody: function () {
					var tableData = table.bootstrapTable('getData');

					// 动态的给表格高度
					var height = tableData.length * 40 + 80;
					$(".wwndiv .fixed-table-body").css({
						"height": height
					});
				}
			}
			table.baseTableConfig().init(options);
		}else{
			table.bootstrapTable('refresh');
		}
	}

	//初始化和更新存储资源表格
	var initResourcetable = function(){
		$('#addsubmit').prop('disabled', true);
		var table = $("#resourcetable");
		if(!initResourcetableFlag){
			Metronic.blockUI({target: '#addcontent',animate: true,cenrerY: true});
			initResourcetableFlag = true;
			var options = {
				pagination:false,
				resizable: false,
				singleSelect:true,
				vin_url:"/api/v1/storages/table",
				vin_method:"GET",
				vin_params: function () {
					let params = {};
					params.node_uuid = $('select[name=nodeselect]').val();
					params.storage_type = $('select[name=storagetype]').val();
					return params;
				},
				columns:[
					{
						checkbox:true,
						sortable: false,
					},
					{
						field: 'storage_name',
						title: LANG.UI_STORAGE_NAME,
						sortable: false,
						align: 'center',
					},
					{
						field: 'description',
						title: LANG.UI_STORAGE_TYPE,
						sortable: false,
						align: 'center',
					},
					{
						field: 'size',
						title: LANG.UI_STORAGE_SIZE,
						sortable: false,
						align: 'center',
					},
				],
				onRefresh: function (params) {
					Metronic.blockUI({target: '#addcontent',animate: true,cenrerY: true});
				},
				onPostBody: function () {
					var tableData = table.bootstrapTable('getData');
					if (tableData.length > 0) {
						$('#addsubmit').prop('disabled', false);
					}
					// 动态的给表格高度
					if (tableData.length > 0) {
						var height = tableData.length * 40 + 80;
					} else {
						var height = 200;
					}

					$("#morestoragediv .fixed-table-body").css({
						"height": height
					});
				},
				onLoadSuccess: function (){
					Metronic.unblockUI('#addcontent');
				}
			}
			table.baseTableConfig().init(options);
		}else{
			table.bootstrapTable('refresh');
		}
	}

	//检查所有的iscsi ip和端口是否符合规则
	var checkAllIscsiIpPort = function(){
		var ipInput = $('input[name=iscsiip]');
		var portInput = $('input[name=iscsiport]');
		var ip = [];
		var result = true;
		for(var i=0; i<ipInput.length; i++){
			result = result && $(ipInput[i]).valid();
			result = result && $(portInput[i]).valid();
			if(!result) return false;
			if(-1 == $.inArray($(ipInput[i]).val(), ip)){
				ip.push($(ipInput[i]).val());
			}else{
				//如果IP有重复的,提示并退出
				UIToastr.showWarning(LANG.UI_STORAGE_ISCSI_IP_TITLE, LANG.UI_STORAGE_ISCSI_IP_TIPS);
				return false;
			}
		}
		return result;
	}

	//初始化和更新lun表格
	var initTargetLuntable = function(){
		if(!checkAllIscsiIpPort()) return false;
		var p = {};
		p.server_list = [];
		var ipInput = $('input[name=iscsiip]');
		var portInput = $('input[name=iscsiport]');
		for(var i=0; i<ipInput.length; i++){
			p.server_list[i] = {ip:$(ipInput[i]).val(), port:$(portInput[i]).val()};
		}
		p.node_uuid = $('select[name=nodeselect]').val();
		p.type = $('select[name=storagetype]').val();
		//$('#iscsiscan').prop('disabled', true);
		Metronic.blockUI({
			target: '#addcontent',
			animate: true,
			cenrerY: true,
		});
		initLuntableFlag = false;

		pAjaxRequest(p, "/api/v1/storages/lun", "POST", function (result) {
			if (result.code == 0) {
				initUserTree(result.data);
			} else {
				Metronic.unblockUI('#addcontent');
				UIToastr.showError(LANG.UI_STORAGE_TYPE_ISCSI, result.message);
			}
		});
	}

	//初始化权限树
	var initUserTree = function(userInfo){
		Metronic.unblockUI('#addcontent');
		if(!initLuntableFlag){
			var setting = {
				check: {
					enable: true,
					chkStyle: "radio",
					radioType: "all",
					nocheckInherit: false,
					chkboxType: {
						"Y": "",
						"N": ""
					}
				},
				data: {
					simpleData: {
						enable: true,
						idKey: "id",
						pIdKey: "pid",
						rootPId: 0
					},
					key: {
						title: "name"
					}
				},
				callback: {
					beforeClick: nodeSelect,
					onCheck: vmOnCheck,
					beforeExpand: nodeExpand
				}
			};
			var nodes = userInfo.rows;
			for (var j in nodes) {
				nodes[j]['isParent'] = nodes[j]['is_parent'];
			}
			$('.target').show();
			if (userInfo.total > 0) {
				$("#permissionTree").html('');

				zTree = $.fn.zTree.init($("#permissionTree"), setting, nodes);
				initLuntableFlag = true;
				// 这里处理下所有的 desc下面的iscsi_target 和 pid
				for (var j in nodes) {
					if (nodes[j].pid == 0) {
						let temp = {
							target_iqn: nodes[j].id,
							iscsi_target: nodes[j].iscsi_target
						};
						allIscsiList.push(temp);
					}
				}
			} else {
				$("#permissionTree").html('<li style="\n' +
					'    line-height: 30px;\n' +
					'    padding-left: 10px;\n' +
					'">'+LANG.UI_TOOLS_NO_DATA +'</li>');

				$('#iscsiscan').prop('disabled', false);
				return UIToastr.showInfo(LANG.UI_STORAGE_TYPE_ISCSI, LANG.UI_SYSTEM_MONITOR_NULL_DATA);
			}

		}
		$('.target').show();
		scanTargetFlag = true;
	}

	var nodeExpand = function(treeId, treeNode){
		if(treeNode.clickshow && !treeNode.children){
			// 异步更新子节点
			// 显示chap认证
			currnettreeNode = treeNode;
			$('#modal-iscsi-chap-div').modal();
			if (currnettreeNode.pid == 0) {
				// 第一次认证
				$('#modal-iscsi-chap-div .modal-header h4').html(LANG.UI_STORAGE_CHAP_AUTH_SET);
			} else {
				$('#modal-iscsi-chap-div .modal-header h4').html(LANG.UI_STORAGE_CHAP_AUTH);
			}
		}else{
			return true;
			zTree.expandNode(treeNode, true);
		}

	}
	$('#iscsi-chap-submit').on('click',function (){
		var username = $('#iscsi-chap-username').val();
		var passwd = $('#iscsi-chap-userpwd').val();
		if (username == '') {
			return UIToastr.showInfo(LANG.UI_ISCSI_CHAT_AUTH, LANG.UI_ISCSI_CHAT_USERNAME_TIPS);
		}
		if (passwd == '') {
			return UIToastr.showInfo(LANG.UI_ISCSI_CHAT_AUTH, LANG.UI_ISCSI_CHAT_PASSWORD_TIPS);
		}

		var p = {};
		p.username = username;
		p.passwd = passwd;
		p.iscsi_target = currnettreeNode.iscsi_target;
		p.target_iqn = currnettreeNode.id;
		p.node_uuid = $('select[name=nodeselect]').val();
		getChap(p);
	});
	var getChap = function (p){
		Metronic.blockUI({
			target: '#modal-iscsi-chap-div',
			animate: true,
			cenrerY: true,
		});
		// 存储下本次输入的信息，后续需要传给后台
		var message = {
			'username': $('#iscsi-chap-username').val(),
			'password': $('#iscsi-chap-userpwd').val()
		};
		var func = 'chap_auth_again';
		var keys = currnettreeNode.id;
		if (currnettreeNode.pid == 0) {
			// 第一次认证
			func = 'chap_auth';
			keys = currnettreeNode.iscsi_target;
		}
		chatArr[keys] = message;

		pAjaxRequest(p, "/api/v1/storages/"+func, "GET", function (result) {
			Metronic.unblockUI('#modal-iscsi-chap-div');
			if (result.code == 0) {
				targetIqn = currnettreeNode.id; // 记住当前请求的iqn
				currnettreeNode.clickshow  = false;
				var nodes = result.data.rows;
				for(var k in nodes){
					nodes[k].isParent = nodes[k].is_parent;
				}
				zTree.addNodes(currnettreeNode, nodes, true);
				zTree.expandNode(currnettreeNode, true);
				for (var j in nodes) {
					if (nodes[j].pid != 0 && nodes[j].is_parent == true) {
						let temp = {
							target_iqn: nodes[j].id,
							iscsi_target: nodes[j].iscsi_target
						};
						allIscsiList.push(temp);
					}
				}
				// 清空信息
				$('#iscsi-chap-username').val('');
				$('#iscsi-chap-userpwd').val('');
				$('#modal-iscsi-chap-div').modal('hide');
			} else {
				UIToastr.showError(LANG.UI_STORAGE_CHAP_AUTH, result.message);
			}
		});
	}

	var nodeSelect = function(treeId, treeNode, clickFlag){
		if(!treeNode.is_parent) return;//不存在子节点不展开
		nodeExpand(treeId, treeNode);
	}
	var vmOnCheck = function(e, id, node){
		iscsiData = node; // 存储选中的iscsi信息
	}

	//初始化iscsi名称
	var initISCSIName = function(){
		var data = {node_uuid:$('select[name=nodeselect]').val(), type:$('select[name=storagetype]').val()};
		pAjaxRequest(data, "/api/v1/storages/iscsi", "GET", function (result) {
			if (result.code == 0) {
				$('#iscsiname').html(result.data.name);
			} else {
				UIToastr.showError(LANG.UI_VOL_CDP_BACKUP_TIPS, result.message);
			}
		});
	}

	//扫描ISCSI检查
	var scanISCSICheck = function(){
		$('#iscsidiv').validate({
			errorElement: 'span', //default input error message container
			errorClass: 'help-block help-block-error', // default input error message class
			focusInvalid: false, // do not focus the last invalid input
			ignore: "",  // validate all fields including form hidden input
			rules: {
				iscsiip: {
					required: true,
					ipv4Ordomain: true,
				},
				iscsiport:{
					required: true,
					iscsiport: true,
				}
			},

			errorPlacement: function (error, element) { // render error placement for each input type
				var icon = $(element).parent('.input-icon').children('i');
				icon.removeClass('fa-check').addClass("fa-warning");
				icon.attr("data-original-title", error.text()).tooltip({'container': 'body'});
			},

			highlight: function (element) { // hightlight error inputs
				$(element)
					.closest('.form-group').removeClass("has-success").addClass('has-error'); // set error class to the control group
			},

			success: function (label, element) {
				var icon = $(element).parent('.input-icon').children('i');
				$(element).closest('.form-group').removeClass('has-error').addClass('has-success'); // set success class to the control group
				icon.removeClass("fa-warning").addClass("fa-check");
			}
		});

		$.validator.addMethod("ipv4Ordomain", function(value, element) {
			var domain = this.optional( element ) || /^(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test( value );
			var ipv4 = this.optional(element) || ipV4V6(value) || /^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$/i.test(value);
			return domain || ipv4;
		}, LANG.UI_TOOLS_IP_OR_DOMAIN);

		$.validator.addMethod("iscsiport", function(value, element) {
			if(!$(element).closest('.form-group').hasClass('has-success'))return false;
			if(value >= 0 && value <= 65535){
				return true;
			}
			return false;
		}, LANG.UI_NODE_PORT_TIPS);
	}

	var remoteDivCheck = function(){
		$('#copydiv').validate({
			errorElement: 'span', //default input error message container
			errorClass: 'help-block help-block-error', // default input error message class
			focusInvalid: false, // do not focus the last invalid input
			ignore: "",  // validate all fields including form hidden input
			rules: {
				remoteip: {
					required: true,
					copyip: true,
				},
				remoteport:{
					required: true,
					copyport: true,
				},
				username: {
					required: true,
				},
				password: {
					required: true,
				}
			},

			errorPlacement: function (error, element) { // render error placement for each input type
				var icon = $(element).parent('.input-icon').children('i');
				icon.removeClass('fa-check').addClass("fa-warning");
				icon.attr("data-original-title", error.text()).tooltip({'container': 'body'});
			},

			highlight: function (element) { // hightlight error inputs
				$(element)
					.closest('.form-group').removeClass("has-success").addClass('has-error'); // set error class to the control group
			},

			success: function (label, element) {
				var icon = $(element).parent('.input-icon').children('i');
				$(element).closest('.form-group').removeClass('has-error').addClass('has-success'); // set success class to the control group
				icon.removeClass("fa-warning").addClass("fa-check");
			}
		});

		$.validator.addMethod("copyip", function(value, element) {
			var domain = this.optional( element ) || /^(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test( value );
			var ipv4 = this.optional(element) || ipV4V6(value) || /^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$/i.test(value);
			return domain || ipv4;
		}, LANG.UI_TOOLS_IP_OR_DOMAIN);

		$.validator.addMethod("copyport", function(value, element) {
//			if(!$(element).closest('.form-group').hasClass('has-success'))return false;
			if(value >= 0 && value <= 65535 && Number.isInteger(Number(value))){
				return true;
			}
			return false;
		}, LANG.UI_NODE_PORT_TIPS);

	}

	var changeRules = function(val){
		var rules = {};
		if(val == 2){
			rules = {
				cloudstring: {
					required: true,
				},
				containername: {
					required: true,
				},
			}
		}else{
			rules = {
				cloudname: {
					required: true,
				},
				cloudpassword: {
					required: true,
				},
				bucketname: {
					required: true,
				},
			};
		}
		return rules;
	}

	var cloudDivCheck = function(value){
		var rules = changeRules(value);
		//改变validate的rules
		if(vendorValidata){
			$('input[name=cloudstring]').rules('remove');
			$('input[name=containername]').rules('remove');
			$('input[name=cloudname]').rules('remove');
			$('input[name=cloudpassword]').rules('remove');
			$('input[name=bucketname]').rules('remove');
			if(value == 2){
				$('input[name=cloudstring]').rules('add', rules.cloudstring);
				$('input[name=containername]').rules('add', rules.containername);
			}else{
				$('input[name=cloudname]').rules('add', rules.cloudname);
				$('input[name=cloudpassword]').rules('add', rules.cloudpassword);
				$('input[name=bucketname]').rules('add', rules.bucketname);
			}
		}else{
			vendorValidata = $('#cloudDiv').validate({
				errorElement: 'span', //default input error message container
				errorClass: 'help-block help-block-error', // default input error message class
				focusInvalid: false, // do not focus the last invalid input
				ignore: "",  // validate all fields including form hidden input
				rules: rules,

				errorPlacement: function (error, element) { // render error placement for each input type
					var icon = $(element).parent('.input-icon').children('i');
					icon.removeClass('fa-check').addClass("fa-warning");
					icon.attr("data-original-title", error.text()).tooltip({'container': 'body'});
				},

				highlight: function (element) { // hightlight error inputs
					$(element)
						.closest('.form-group').removeClass("has-success").addClass('has-error'); // set error class to the control group
				},

				success: function (label, element) {
					var icon = $(element).parent('.input-icon').children('i');
					$(element).closest('.form-group').removeClass('has-error').addClass('has-success'); // set success class to the control group
					icon.removeClass("fa-warning").addClass("fa-check");
				}
			});
		}
	}

	var hwcbrChangeRules = function(val){
		var rules = {};
		if(val == 2){
			rules = {
				hwregionlist:{
					required:true,
				},
				cbraccessid:{
					required:true,
				},
				cbraccesskey:{
					required:true,
				},
				huaweiuserak:{
					required:true,
				},
				huaweiusersk:{
					required:true,
				},
				huaweiusername:{
					required:true,
					// cbruserlist:true
				}
			}
		}else{
			rules = {
				cbraccessid:{
					required:true,
				},
				cbraccesskey:{
					required:true,
				}
			};
		}
		return rules;
	}

	//华为CBR输入验证
	var huaweicbrDivCheck =  function(value){
		var rules  =  hwcbrChangeRules(value);
		if(cbrvendorValidata){
			$('input[name=cbraccessid]').rules('remove');
			$('input[name=cbraccesskey]').rules('remove');
			$('input[name=hwregionlist]').rules('remove');
			$('input[name=huaweiuserak]').rules('remove');
			$('input[name=huaweiusersk]').rules('remove');
			$('input[name=huaweiusername]').rules('remove');
			if(value == 2){
				$('input[name=cbraccessid]').rules('add', rules.cbraccessid);
				$('input[name=cbraccesskey]').rules('add', rules.cbraccesskey);
				$('select[name=hwregionlist]').rules('add', rules.hwregionlist);
				$('input[name=huaweiuserak]').rules('add', rules.huaweiuserak);
				$('input[name=huaweiusersk]').rules('add', rules.huaweiusersk);
				$('input[name=huaweiusername]').rules('add', rules.huaweiusername);
			}else{
				$('input[name=cbraccessid]').rules('add', rules.cbraccessid);
				$('input[name=cbraccesskey]').rules('add', rules.cbraccesskey);
			}
		}
		else{
			cbrvendorValidata = $('#huaweicbrdiv').validate({
				errorElement: 'span', //default input error message container
				errorClass: 'help-block help-block-error', // default input error message class
				focusInvalid: false, // do not focus the last invalid input
				ignore: "",  // validate all fields including form hidden input
				rules:rules,
				errorPlacement: function (error, element) { // render error placement for each input type
					var icon = $(element).parent('.input-icon').children('i');
					icon.removeClass('fa-check').addClass("fa-warning");
					icon.attr("data-original-title", error.text()).tooltip({'container': 'body'});
				},
				highlight: function (element) { // hightlight error inputs
					$(element)
						.closest('.form-group').removeClass("has-success").addClass('has-error'); // set error class to the control group
				},
				success: function (label, element) {
					var icon = $(element).parent('.input-icon').children('i');
					$(element).closest('.form-group').removeClass('has-error').addClass('has-success'); // set success class to the control group
					icon.removeClass("fa-warning").addClass("fa-check");
				}
			});
			//针对CBR授权用户做校验
			$.validator.addMethod("cbruserlist",function(value,element){
				var cbruserlist =  /^[0-9\a-z\A-Z\u4e00-\u9fa5]+(,[0-9\a-z\A-Z\u4e00-\u9fa5]+)*$/g.test(value);
				return cbruserlist;
			},LANG.UI_STORAGE_INPUT_USER_NAME_TIPS);
		}
	}

	//获取异地备份系统存储列表
	var getRemoteStorageList = function(){
		if(!$('#copydiv').validate().form()) return;
		Metronic.blockUI({target: '#addcontent',animate: true, cenrerY: true});
		var remoteip = $('#copydiv').find('input[name=remoteip]').val();
		var remoteport = $('#copydiv').find('input[name=remoteport]').val();
		var username = $('#copydiv').find('input[name=username]').val();
		var password = $('#copydiv').find('input[name=password]').val();
		password = btoa(password);
		scanRemoteFlag = true;
		var options = {
			vin_url: "/api/v1/storages/remote",
			vin_method: "GET",
			queryParams: {remoteip: remoteip, remoteport: remoteport, username: username, password: password},
			resizable: false, //拖拽列
			singleSelect:true,
			pagination: false, //页标签
			onLoadSuccess: function() {
				Metronic.unblockUI('#addcontent');
			},
			onLoadError: function() {
				Metronic.unblockUI('#addcontent');
			},
			onPostBody: function (){
				//Metronic.unblockUI('#addcontent');
				var s = $('#remote_storage_table').bootstrapTable('getData');
				if (s.length) {
					init_offsite_flag = true;
					$('#addsubmit').prop('disabled', false);
				} else {
					init_offsite_flag = false;
					$('#addsubmit').prop('disabled', true);
				}
				// 动态的给表格高度
				var height = s.length * 40 + 80;
				$("#copydiv .fixed-table-body").css({
					"height": height
				});
			},

			columns: [{
				checkbox: true,
				sortable: false,
			},
				{
					field: 'id',
					title: LANG.UI_PUBLIC_TABLE_ID,
					sortable: true,
					align: 'center',
				},
				{
					field: 'storage_name',
					title: LANG.UI_STORAGE_NAME,
					sortable: true,
					align: 'center',
				},
				{
					field: 'storage_type',
					title: LANG.UI_STORAGE_TYPE,
					sortable: true,
					align: 'center',
				},
				{
					field: 'storage_size',
					title: LANG.UI_STORAGE_SIZE,
					sortable: true,
					align: 'center',
				},
				{
					field: 'storage_node',
					title: LANG.UI_STORAGE_NODE,
					sortable: true,
					align: 'center',
				},
			],
		}
		// 未初始化过则初始一个新表
		if (!init_remote_flag) {
			$('#remote_storage_table').baseTableConfig().init(options);
			$('.storage-list').show();
			init_remote_flag = true;
		} else {
			// 如果已经初始化销毁表格再初始化新表
			// 销毁表格
			$('#remote_storage_table').bootstrapTable('destroy');
			$('#remote_storage_table').baseTableConfig().init(options);
		}
	}

	//扫描ISCSI
	var scanISCSI = function(){
		if(!$('#iscsidiv').validate().form()) return;
		initTargetLuntable()
	}

	//初始化通用存储DIV
	var initMoreStorageDiv = function(){
		$('.asdiv').hide();
		$('#morestoragediv').show();
	}

	//初始化ISCSI DIV
	var initISCSIDiv = function(){
		initISCSIName();
		$('.asdiv').hide();
		$('#iscsidiv').show();

		$('#iscsiip').val('').prop('disabled', false);
		$('#iscsiport').val('').prop('disabled', false);
		$('.target').hide();
		scanTargetFlag = false;
	}

	// 初始化dddb存储
	var initDddbDiv = function (){
		$('#morestoragediv').hide();
		$('#dddbdiv').show();
	}

	//初始化NFS DIV
	var initNFSDiv = function(){
		$('.asdiv').hide();
		$('#nfsdiv').show();
	}

	//初始化CIFS DIV
	var initCIFSDiv = function(){
		$('.asdiv').hide();
		$('#cifsdiv').show();
	}

	//初始化COPY DIV
	var initCOPYDiv = function(){
		$('.asdiv').hide();
		$('#copydiv').show();
		scanRemoteFlag = false;
	}

	//初始化CLOUD DIV
	var initCloudDiv = function(){
		$('.asdiv').hide();
		$('#cloudDiv').show();
	}

	// 初始化CLOUDHW DIV
	var initCloudhwDiv = function (){
		$('.asdiv').hide();
		$('#cloudhwDiv').show();
	}

	//初始化LOCAL DIR DIV
	var initLocaldirDiv = function(){
		$('.asdiv').hide();
		$('#localdiv').show();
	}
	//初始化华为CBR DIV
	var initHuaweiCBRDiv =  function(){
		$('.asdiv').hide();
		$('#huaweicbrdiv').show();
	}
	$.validator.addMethod("storageType", function(value, element) {
		if(value > 0){
			return true;
		}
		return false;
	}, LANG.UI_STORAGE_SELECT_TYPE);

	//存储资源选择检测
	var dataTableSelectCheck = function(){
		if(!$('#copydiv').validate().form()) return false;
		return true;
	}

	//获取选中项uuid
	function getIdSelectedId(select) {//select = table id
		return $.map($(select).bootstrapTable('getSelections'), function (row) {
			return row.uuid;
		})
	}

	return {
		//main function to initiate the module
		init: function () {
			//初始化多选下拉框
			$(".selectpicker").selectpicker({
				noneSelectedText: LANG.BILLING_PLEASE_SELECT,
				deselectAllText: LANG.BILLING_DESELECT_ALL,
				selectAllText: LANG.BILLING_SELECT_ALL,
				liveSearchPlaceholder: LANG.BILLING_SEARCH,
				countSelectedText: function () {}
			});
			initNodeSelect();
			addListeners();
		}
	};
}();

jQuery(document).ready(function() {
	StorageAdd.init();
});