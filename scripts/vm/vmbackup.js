var VMBackup = function () {
	var sub_module_type = $('#subModuleType').val();
	const SUB_MODULE_TYPE_PRIVATE_CLOUD = 2;
	var data = {
		src_info:{},
		time_strategy:{},
		storage_strategy: {},
		reserved_strategy: {},
		advanced_strategy: {},
		transport_strategy: {},
		speed_strategy:{},
		verify_info:{}
	};
	//定义三棵树:主机和虚拟机,主机和集群,虚拟机和模板,搜索到的树节点
	var zTree, zTreeHC, zTreeVT, nodeParamList;
	var currentTree, zTreeLoad = zTreeHCLoad = zTreeVTLoad = false;	//当前显示的树,三棵树是否已经加载过的标志
	var currentTreeDivId;	//用于搜索时候定位
	var currentListId = 'addHCList'; // 当前已选备份对象的列表ID
	var searchVmTreeId; // 当前搜索的树id
	var searchVmTreeWord; // 当前搜索的树的关键词
	var _timeStamp = '';	//时钟时间戳,全局
	var applianceFlag = false; //自定义节点选择加载和加载appliance选择标志
	var _VMNAMEREG = new RegExp("[ \n\r\t\passwordAutocheckf`~!@#$^&*()=|{}':;',\\[\\].<>/?~！@#￥……&*（）&;|{}【】‘；：”“'。，、？+-]");
	var speedList = [];
	var initStrategyFlag = false;
	var globalStrategy = [];
	var editFlag = false;
	var authFun = [];
	var _oldPassword = '';
	var passwordChangeFlag = false; // 是否改变了密码框内容标记
	var initConfigFlag = false;
	var hypervisorType = '';
	var nodeLoaded = []; //记录已加载过的虚拟化中心
	var speedFlag = false;//是否是快速创建
	var oldVcenter;
	var backupObjList = {}; //备份对象，{objId:{"object_uuid":"","vcenter_uuid":"","type":"","exclude_vm_list":[{"vm_uuid":"","vm_path":""}]}...}
	var globalSelect = {}; //全局筛选条件，{"select_type":"","select_value":["", ""]}
	var showType = 1; //VMware显示方式
	var selectType = 1; //前缀过滤方式
	var selectValue = []; //前缀
	var imageIOFlag = false; //传输模式是否已选中imageIO
	var cloudTargetFlag = false;
	var currentPlatformUuid = '';
	var _backupConfig;
	var backupTargetInfo; // 备份目的地信息，从插件获取
	var backupServerIpInfo;
	var isLicense = false; // 标记下授权是否有效
	var submittedFlag = false; // 是否已提交

	// 虚拟机名称过滤方式
	const VM_FILTER_PREFIX_EXCLUDE = 0;
	const VM_FILTER_PREFIX_MATCH = 1;
	const VM_FILTER_KEYWORD_EXCLUDE = 2;
	const VM_FILTER_KEYWORD_MATCH = 3;

	const LIMIT = 100; // 每次加载的条数
	var vmNodeListCache = [];
	
	var initData = function(){
		//setp1
		data.src_info.vm_info = [];
		//disklist
		data.src_info.exclude_disk_list =[];
		//setp2
		//备份方式:策略/时间
		data.time_strategy.type = 'strategy';
		//完备/增备/差备/永久增量
		data.time_strategy.full_info = {};
		data.time_strategy.incr_info = {};
		data.time_strategy.diff_info = {};
		data.time_strategy.pincr_info = {};
		//按时间备份的时间
		data.time_strategy.datetime = null;
		//setp3
		//保留策略
		data.reserved_strategy = {};
		data.reserved_strategy.reserved_type = 1;
		data.transport_strategy = {};
		data.storage_strategy = {};
		data.advanced_strategy.node = {};
		data.advanced_strategy.mode = {};
		data.src_info.auto_join_flag = false;
		$('#password').attr('placeholder', LANG.UI_PUBLIC_ENTER_PASSWORD);
		$('#repassword').attr('placeholder', LANG.UI_PUBLIC_ENTER_REPASSWORD);
		
        // 初始化备份策略
        $('#tab_common').backupStrategy(CONF.MODULE_TYPE.VM, false);

		// 初始化重试策略
		$('#retry_config').retryStrategy();
	}
	
	//初始化监听事件
	var initListener = function(){
		$('#backuptype').on('change', backupTypeHandler);
		$('#toaddvcenter').on('click',function(){
			if (SUB_MODULE_TYPE_PRIVATE_CLOUD == sub_module_type) {
				LOCATION('./content/vm/cloudplatform/add_cloud_platform.php?cloudType=private', 'infrastructure');
			} else {
				LOCATION('./content/vm/add_vcenter.php', 'infrastructure');
			}
		});

		//选择备份策略复选框
		$('#strategymode').find('.icheck').on('ifClicked', strategyModeClick);
		//XenServer传输模式和增量模式关联
		$('#xentransmode').on('change', xentransModeChange);
		
		//openstack传输模式切换显示appliance
		$('#openstacktransmode').on('change', openstackModeChange);
		

		//appliance开关切换回调
		$('#appliancecheck').on('switchChange.bootstrapSwitch', applianceChange);
		
		 //启动内联数据消重
		 $('#parsefscheck').on('switchChange.bootstrapSwitch', parsefsChange);

		$('#autoJoinFlag').on('switchChange.bootstrapSwitch', autoJoinChange);

		//初始化虚拟机过滤模态框
		$('#vmFilterBtn').on('click', function() {
			$('#vmFilterModal').modal({'width':'800px'});
		});
		
		//切换存储加密开关
		$('#encryptStorageCheck').on('switchChange.bootstrapSwitch', encryptChange);
		
		//切换自动选择存储加密密码
		$('#passwordAutocheck').on('switchChange.bootstrapSwitch', passwordModeChange);
		
		//数据加密密码确认
		$('#repassword').on('input propertychange', function(){
			checkPassword();
		});
		//数据加密密码输入
		$('#password').on('input propertychange', function(){
			checkPassword();
		});
		
		//华为KVM传输模式切换
		$('#huaweikvmtransport_mode').on('change', huaweikvmChange);
		
		//红帽传输模式切换
		$('#redhattransmode').on('change', redhatTransChange);
		
		//切换增量模式
		$('#incmodel').on('change', incmodelChange);
		
		//切换类xen传输模式
		$('#leixentransmode').on('change', leixentransChange);

		//切换smartx传输模式
		$('#smartxtransmode').on('change', smartxtransChange);

		//切换icsvvdk传输模式
		$('#icsvvdktransport_mode').on('change', icsvvdktransChange);

		//切换sangforvvdk传输模式
		$('#sangforvvdktransmode').on('change', sangforvvdktransChange);

		// 切换vmware传输模式
		$('#vmwaretransport_mode').on('change', vmwaretransChange);
		// 切换vmware OEM传输模式
		$('#transport_mode').on('change', vmwaretransChange);
		
		//输入线程数量检测
		// $('#backupThreadNum').blur(threadChange);
		//切换GFS保留策略开关
		$('#GFSflag').on('switchChange.bootstrapSwitch', GFSChange);
		//切换完整性校验开关
		$('#verifyflag').on('switchChange.bootstrapSwitch', verifyChange);


		// 传输策略---加密传输
        $('#encrypttransfer').on('switchChange.bootstrapSwitch', transferEncryptChange);

		// 选中永久增量隐藏且关闭高速磁盘CBT
		$('#pincrBackup').on('ifChecked', function(){
			$('#highdiskcbtcheck').bootstrapSwitch('state', false);
			$('.highdiskcbtdiv').hide();
		});
		// 取消选中永久增量显示高速磁盘CBT
		$('#pincrBackup').on('ifUnchecked', function(){
			if (CONF.VM_TYPE.H3CCASCVD == data.hypervisor_type) {
				$('.highdiskcbtdiv').show();
			}
		});

		// 保留类型是按备份链时，隐藏高级配置-存储-合并模式，且没有数据块大小时隐藏存储tab
		$(`#reserveMode`).on('change', function () {
			if (CONF.RESERVE_STRATEGY_MODE.CHIAN === parseInt(this.value)) {
				if (!_backupConfig.initConfig.block_size) {
					$(`#li_high_storage`).removeClass('active').hide();
					$(`#tab_high_backupdata`).removeClass('active');
					// 隐藏后切换到快照显示
					$(`#li_high_snapshot`).addClass('active');
					$(`#tab_high_snapshot`).addClass('active');
				}
				$(`.mergemodediv`).hide();
				$(`.mergemodeshow`).hide();
			} else {
				$(`#li_high_storage`).show();
				$(`.mergemodediv`).show();
				$(`.mergemodeshow`).show();
			}
		});

		// 添加过滤的前缀
		$(`#addPrefixBtn`).on('click', addVmFilterPrefix);

		// 传输压缩开关
		$(`#transferCompressSwitch`).on('switchChange.bootstrapSwitch', function () {
			if (this.checked) {
				$('#transferCompress').val('fastlz');
				$(`.transferCompressDiv`).show();
			} else {
				// 关闭传输压缩选中unzip
				$(`#transferCompress`).val('unzip');
				$(`.transferCompressDiv`).hide();
			}
		});

		// 选中或取消永久增量后重新初始化worm
		$('#pincrBackup').on('ifChanged', initWormConfig);

		$(`#hypervisortype`).on('change', function () {
			hypervisorType = this.value;
			if (CONF.VMTYPE_GROUP.VMWARE.includes(parseInt(hypervisorType))) {
				$(`#vmshowtype`).val(1).prop('disabled', false);
			} else {
				$(`#vmshowtype`).val(1).prop('disabled', true);
			}
			if (sub_module_type == 1) {
				$(`#vmshowtype`).selectpicker('refresh');
				vmShowTypeChange();
			}

			initTree();

			// 重置备份对象列表
			backupObjList = {};
			// 清空右侧已选对象
			if(currentTreeDivId=="vm_tree_hc"){
				$('#addHCList li').remove();
			}
			if(currentTreeDivId=="vm_tree_vt"){
				$('#addVTList li').remove();
			}
			if(currentTreeDivId=="vm_tree"){
				$('#addHVList li').remove();
			}
			$('.diskList').remove();
		});
	}

	const addVmFilterPrefix = function () {
		let html = `
			<div class="form-group filter-input">
				<div class="col-md-9">
					<input class="form-control inline-block vm_prefix_add" name="vm_prefix">
				</div>
				<div class="col-md-3">
					<button type="button" class="btn btn-default delPrefixBtn" style="width: 50px;">
						<i class="viconfont vicon-ge_delete"></i>
					</button>
				</div>
			</div>
		`;
		$(this).closest('.portlet-body').find(`.filter-input-container`).append(html);

		// 删除过滤的前缀，如果只有一个输入框则清空
		$(`.delPrefixBtn`).on('click', function () {
			if ($(`.filter-input`).length > 1) {
				$(this).closest('.filter-input').remove();
			} else {
				$(this).closest('.filter-input').find(`input[name=vm_prefix]`).val('');
			}
		});
	}

	// 显示加密算法
	var transferEncryptChange = function(){
		if(this.checked){
			$('.transfer-encrypt-method-form').show();
		}else{
			$('.transfer-encrypt-method-form').hide();
		}
	}

	//自动加入备份开关切换
	var autoJoinChange = function () {
		if (this.checked) {
			data.src_info.auto_join_flag = true;
		} else {
			data.src_info.auto_join_flag = false;
			// 虚拟机上级粒度触发展开
			$('.objCheck').closest(`a[aria-expanded=false]`).trigger('click');
		}
	}
	
	//数据加密密码确认检测
	var checkPassword = function(){
		passwordChangeFlag = true;
	}
	
	//保留策略改变事件
	var GFSChange = function(){
		//如果勾选
		if(this.checked){
			$("#GFSDiv").show();
		}else{
			$("#GFSDiv").hide();
		}
	}
	//数据完整性校验改变事件
	var verifyChange = function(){
		//如果勾选
		if(this.checked){
			$(".verifytypeDiv").show();
		}else{
			$(".verifytypeDiv").hide();
		}
	}
	
	var leixentransChange = function(){
		var value = parseInt(this.value);
		//网络传输支持支持数据传输网段和加密传输
		if(value == 1){
			$('.ipSegmentDiv').show();	//显示数据传输网段
			$('.encrypttransferdiv').show();	//加密传输
		}else{
			$('.ipSegmentDiv').hide();	//隐藏数据传输网段
			$('#ipSegment').val('');
			$('.encrypttransferdiv').hide();	//加密传输
			$('.transfer-encrypt-method-form').hide();	//加密传输算法
			$('#encrypttransfer').bootstrapSwitch('state', false);
		}
	}
	
	var smartxtransChange = function () {
		var value = parseInt(this.value);
		$('.ipSegmentDiv').hide();  //smartx屏蔽数据传输网段
		$('#ipSegment').val('');
		if(3 == value){
			$('.applianceselectdiv').show();
			$('.applianceshow').show();
			if(!applianceFlag){
				initApplianceSelect();
			}
			$('.encrypttransferdiv').show();	//加密传输
			$('#encrypttransfer').bootstrapSwitch('state', false);
		}else{
			$('.applianceselectdiv').hide();
			$('.applianceshow').hide();
			$('.encrypttransferdiv').hide();	//加密传输
			$('.transfer-encrypt-method-form').hide();	//加密传输算法
		}
	}

	var icsvvdktransChange = function () {
		var value = parseInt(this.value);
		$('.ipSegmentDiv').hide();  //icsvvdk屏蔽数据传输网段
		$('#ipSegment').val('');
		if(3 == value){
			$('.applianceselectdiv').show();
			$('.applianceshow').show();
			if(!applianceFlag){
				initApplianceSelect();
			}
			$('.encrypttransferdiv').show();	//加密传输
			$('#encrypttransfer').bootstrapSwitch('state', false);
		}else{
			$('.applianceselectdiv').hide();
			$('.applianceshow').hide();
			$('.encrypttransferdiv').hide();	//加密传输
			$('.transfer-encrypt-method-form').hide();	//加密传输算法
		}
	}

	var sangforvvdktransChange = function () {
		var value = parseInt(this.value);
		$('.ipSegmentDiv').hide();  //sangforvvdk屏蔽数据传输网段
		$('#ipSegment').val('');
		if(3 == value){
			$('.applianceselectdiv').show();
			$('.applianceshow').show();
			if(!applianceFlag){
				initApplianceSelect();
			}
			$('.encrypttransferdiv').show();
			$('#encrypttransfer').bootstrapSwitch('state', false);
		}else{
			$('.applianceselectdiv').hide();
			$('.applianceshow').hide();
			$('.encrypttransferdiv').hide();	//加密传输
			$('.transfer-encrypt-method-form').hide();	//加密传输算法
		}
	}

	var vmwaretransChange = function () {
		if (['nbd', 'nbdssl'].includes(this.value)) {
			// nbd模式显示传输压缩并关闭异步传输
			$('.transferCompressSwitchDiv').show();
			$('#transferCompressSwitch').bootstrapSwitch('state', false);
			$('.transferCompressDiv').hide();
			$('#transferCompress').val('unzip');
			$('#asyncTransfer').bootstrapSwitch('state', false);
			$('.asyncTransferDiv').show();
		} else {
			$('.transferCompressSwitchDiv').hide();
			$('.transferCompressDiv').hide();
			$('#transferCompress').val('unzip');
			$('#asyncTransfer').bootstrapSwitch('state', false);
			$('.asyncTransferDiv').hide();
		}
	}
	
	var incmodelChange = function(){
		var value = parseInt(this.value);
		var transmode = $('#redhattransmode').val();
		if(value == 3){
			if(CONF.VMTYPE_GROUP.REDHAT.includes(data.hypervisor_type)){
				$('#redhattransmode').val(5).prop('disabled', true);	//红帽传输模式
				$('.encrypttransferdiv').hide();	//加密传输
				$('.transfer-encrypt-method-form').hide();	//加密传输算法
				$('#encrypttransfer').bootstrapSwitch('state', false);
				$('.ipSegmentDiv').show(); //显示传输网段
			}
			$('#preSnapshotcheck').bootstrapSwitch('state', false).prop('disabled', true);	//提前创建快照

			if (_backupConfig.initConfig.reset_cbt) {
				// 显示重置CBT
				$('#resetcbtLevel').val(2);
				$('.resetcbtdiv').show();
				$('.resetcbtshow').show();
			}
		}else{
			//如果是imageIO才切换传输模式
			if(transmode == 5){
				$('#redhattransmode').val(1);
			}
			if($('#redhattransmode').val() == 2 || $('#redhattransmode').val() == 5){
				$('.encrypttransferdiv').hide();
				$('.transfer-encrypt-method-form').hide();	//加密传输算法
				$('#encrypttransfer').bootstrapSwitch('state', false);
			}else{
				$('.encrypttransferdiv').show();	//加密传输
			}
			$('#redhattransmode').prop('disabled', false);	//红帽传输模式
			$('.resetcbtdiv').hide();
			$('.resetcbtshow').hide();
		}
	}
	
	var redhatTransChange = function(){
		var value = parseInt(this.value);
		$('.ipSegmentDiv').show();				//显示传输网段	
		if(value == 5){
			$('.encrypttransferdiv').hide();	//加密传输
			$('.transfer-encrypt-method-form').hide();	//加密传输算法
			$('#encrypttransfer').bootstrapSwitch('state', false);
			$('#incmodel').val(3).prop('disabled', true);	//CBT模式
			$('#preSnapshotcheck').bootstrapSwitch('state', false).prop('disabled', true);	//提前创建快照
			imageIOFlag = true;
		}else{
			$('.encrypttransferdiv').show();	//加密传输
			if (imageIOFlag) {
				$('#incmodel').val(2).prop('disabled', false);
			}
			imageIOFlag = false;
		}

		if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
			if(value == 1) {
				$('#incmodel').val(1);
			}
			//SAN传输不显示传输网段
			if(value == 2 || 4 == CONF.SOFTWARE ){
				$('.ipSegmentDiv').hide();	//隐藏数据传输网段
				$('#ipSegment').val('');
			}
		}
		//SAN传输不显示传输网段
		if(value == 2){
			$('.ipSegmentDiv').hide();	//隐藏数据传输网段
			$('.encrypttransferdiv').hide();	//隐藏加密传输
			$('.transfer-encrypt-method-form').hide();	//加密传输算法
			$('#encrypttransfer').bootstrapSwitch('state', false);
			$('#ipSegment').val('');
		}
	}
	var huaweikvmChange = function(){
		var value = parseInt(this.value);
		//API LAN无加密传输
		$('.encrypttransferdiv').hide();				//隐藏加密传输
		$('.transfer-encrypt-method-form').hide();	//加密传输算法
		$('#encrypttransfer').bootstrapSwitch('state', false);
		//API LAN只支持CBT模式
		$('#incmodel').prop('disabled', false);
		$('.systemIpdiv').hide();	//备份系统IP
		switch(value){
			case 1:
				$('.encrypttransferdiv').show();		//显示加密传输
				$('.ipSegmentDiv').show();				//显示传输网段		
				break;
			case 5:
				$('.systemIpdiv').show();	//备份系统IP
				$('#incmodel').val(3);	//CBT模式
				$('#incmodel').prop('disabled', true);
				$('.ipSegmentDiv').hide();				//隐藏传输网段		
				$('#ipSegment').val('');
				break;
		}
	}
	
	//切换自动选择存储加密密码
	var passwordModeChange = function(){
		if(this.checked){
			$('#password').val('');
			$('#repassword').val('');
			$('.passwordDiv').hide();
		}else{
			$('.passwordDiv').show();
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
    
    //添加虚拟机过滤条件
	var vmFilterSubmit = function () {
		Metronic.blockUI({target: '#vmFilterModal', animate: true, cenrerY: true});
		selectType = parseInt($('#vmMatchType').val());
		selectValue = [];
		$(`input[name=vm_prefix]`).each(function () {
			let selectValueItem = $.trim($(this).val());
			if (selectValueItem) {
				selectValue.push(selectValueItem);
			}
		});
		globalSelect = {"select_type": selectType, "select_value": selectValue};
		$('.filter-items').empty();
		if (selectValue.length) {
			// 虚拟机上级粒度触发展开
			$('.objCheck').closest(`a[aria-expanded=false]`).trigger('click');
			$('.filter-items').show();
		} else {
			$('.filter-items').hide();
		}

		let lang = '';
		switch (selectType) {
			case VM_FILTER_PREFIX_EXCLUDE:
				lang = SUB_MODULE_TYPE_PRIVATE_CLOUD == sub_module_type ? LANG.UI_BACKUP_INSTANCE_NAME_PREFIX_FILTER : LANG.UI_BACKUP_VM_NAME_PREFIX_FILTER;
				break;
			case VM_FILTER_PREFIX_MATCH:
				lang = SUB_MODULE_TYPE_PRIVATE_CLOUD == sub_module_type ? LANG.UI_BACKUP_INSTANCE_NAME_PREFIX_MATCH : LANG.UI_BACKUP_VM_NAME_PREFIX_MATCH;
				break;
			case VM_FILTER_KEYWORD_EXCLUDE:
				lang = SUB_MODULE_TYPE_PRIVATE_CLOUD == sub_module_type ? LANG.UI_BACKUP_INSTANCE_NAME_KEYWORD_FILTER : LANG.UI_BACKUP_VM_NAME_KEYWORD_FILTER;
				break;
			case VM_FILTER_KEYWORD_MATCH:
				lang = SUB_MODULE_TYPE_PRIVATE_CLOUD == sub_module_type ? LANG.UI_BACKUP_INSTANCE_NAME_KEYWORD_MATCH : LANG.UI_BACKUP_VM_NAME_KEYWORD_MATCH;
				break;
			default:
				break;
		}
		//列表上方显示
		var prefixInfo =
			'<div class="filter-item inline-block mr10">' +
			'<label>' + lang + '：</label>' +
			'<span>' + selectValue.join(', ') + '</span>' +
			'<div class="close filter-item-close" data-select-type="wildcard" style="margin: -2px -5px 0 5px;"></div>' +
			'</div>';
		$('.filter-items').append(prefixInfo);
		$('#vmFilterModal').modal('hide');

		$('.filter-item-close').on('click', deleteFilterItem);

		handlerFilterVmListCheck();
		Metronic.unblockUI('#vmFilterModal');
	}

	//处理有过滤规则后对象下虚拟机列表的勾选
	var handlerFilterVmListCheck = function (enableFlag = true) {
		var vmCheck = $('.vmTips').find('.vmCheck');
		$.each(vmCheck, function (i, v) {
			let checked;
			if (enableFlag) {
				// 启用规则
				if (selectValue.length) {
					let matchFlag = false;
					let thisValue = decodeURIComponent($(this).val());
					if (VM_FILTER_PREFIX_EXCLUDE === selectType || VM_FILTER_PREFIX_MATCH === selectType) {
						// 过滤规则-按前缀
						$.each(selectValue, function (i, selectValueItem) {
							if (0 === thisValue.indexOf(selectValueItem)) {
								matchFlag = true;
							}
						});
						if (matchFlag) {
							// 匹配的：匹配时选中，排除时取消选中
							checked = VM_FILTER_PREFIX_MATCH === selectType;
						} else {
							// 不匹配的：排除时选中，匹配时取消选中
							checked = VM_FILTER_PREFIX_EXCLUDE === selectType;
						}
					} else {
						// 过滤规则-按关键词
						$.each(selectValue, function (i, selectValueItem) {
							if (-1 !== thisValue.indexOf(selectValueItem)) {
								matchFlag = true;
							}
						});
						if (matchFlag) {
							// 匹配的：匹配时选中，排除时取消选中
							checked = VM_FILTER_KEYWORD_MATCH === selectType;
						} else {
							// 不匹配的：排除时选中，匹配时取消选中
							checked = VM_FILTER_KEYWORD_EXCLUDE === selectType;
						}
					}
				} else {
					// 无前缀/关键词：排除时选中，匹配时取消选中
					checked = VM_FILTER_PREFIX_EXCLUDE === selectType || VM_FILTER_KEYWORD_EXCLUDE === selectType;
				}
			} else {
				// 取消规则默认全选
				checked = true;
			}
			$(this).prop('checked', checked);
			if (checked) {
				$(this).parent('.icheckbox_square-blue').addClass('checked');
			} else {
				$(this).parent('.icheckbox_square-blue').removeClass('checked');
			}
			checkVmHandle(v);
		});

		// 统计实际选中的个数
		$.each($(`.list-type-obj`).next(`ul:has(li)`).prev(`li`), function (i, v)  {
			let count = 0;
			if (enableFlag) {
				let liId = $(v).attr('id');
				let checked;
				let rows = vmNodeListCache[liId];
				$.each(rows, function (idx, row) {
					// 启用规则
					if (selectValue.length) {
						let matchFlag = false;
						let thisValue = row.name;
						if (VM_FILTER_PREFIX_EXCLUDE === selectType || VM_FILTER_PREFIX_MATCH === selectType) {
							// 过滤规则-按前缀
							$.each(selectValue, function (i, selectValueItem) {
								if (0 === thisValue.indexOf(selectValueItem)) {
									matchFlag = true;
								}
							});
							if (matchFlag) {
								// 匹配的：匹配时选中，排除时取消选中
								checked = VM_FILTER_PREFIX_MATCH === selectType;
							} else {
								// 不匹配的：排除时选中，匹配时取消选中
								checked = VM_FILTER_PREFIX_EXCLUDE === selectType;
							}
						} else {
							// 过滤规则-按关键词
							$.each(selectValue, function (i, selectValueItem) {
								if (-1 !== thisValue.indexOf(selectValueItem)) {
									matchFlag = true;
								}
							});
							if (matchFlag) {
								// 匹配的：匹配时选中，排除时取消选中
								checked = VM_FILTER_KEYWORD_MATCH === selectType;
							} else {
								// 不匹配的：排除时选中，匹配时取消选中
								checked = VM_FILTER_KEYWORD_EXCLUDE === selectType;
							}
						}
					} else {
						// 无前缀/关键词：排除时选中，匹配时取消选中
						checked = VM_FILTER_PREFIX_EXCLUDE === selectType || VM_FILTER_KEYWORD_EXCLUDE === selectType;
					}
					if (checked) {
						++count;
					}
				});
			} else {
				// 取消规则默认全选
				count = $(v).find(`.count-vm`).attr('data-total');
			}
			updateObjVMCount(v, 1, count);
		});
	}

	//删除虚拟机过滤条件
	var deleteFilterItem = function () {
		$(this).parent('.filter-item').remove();
        handlerFilterVmListCheck(false);
		$('#vmMatchWildcard').val(''); //重置输入框
		$(`.vm_prefix_add`).closest(`.form-group`).remove();
		selectValue = [];
		$('.filter-items').hide();
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
	
	// 应用全局策略并初始化策略信息
	var strategyHandler = function(){
		editFlag = false;
		// 全局策略选中
		var index = $('#strategySelect option:selected').val();
		var strategy = globalStrategy[index];
		if(cloudTargetFlag){
			$('#tab_common').backupStrategy(CONF.MODULE_TYPE.VM, true, strategy, 'cloud');
		}else{
			$('#tab_common').backupStrategy(CONF.MODULE_TYPE.VM, true, strategy, data.hypervisor_type);
		}
		// 限速策略
		if(index == 0) return false;
		_oldPassword = strategy?.store?.storeInfo?.password ?? '';;
        var speedInfo = strategy.speedlimit.speedInfo;
        var check = strategy.speedlimit.check;
        if(!check || !speedInfo) return ;
		speedList = [];
		// 将限速策略放进消息中
        for(var i=0;i<speedInfo.length;i++){
			speedList.push(speedInfo[i]);
		}
	}

	var applianceChange = function(){
		if(this.checked){
			initApplianceSelect();
			$('.applianceselectdiv').show();
			if (CONF.VM_TYPE.VMWARE == data.hypervisor_type) {
				// vmware开启传输代理可配置加密传输
				$('.encrypttransferdiv').show();	//加密传输
			}
		}else{
			$('.applianceselectdiv').hide();
			if (CONF.VM_TYPE.VMWARE == data.hypervisor_type) {
				$('#encrypttransfer').bootstrapSwitch('state', false);
				$('.encrypttransferdiv').hide();	//加密传输
				$('.transfer-encrypt-method-form').hide();	//加密传输算法
			}
		}
	}
	
	//版本差异处理,主要是处理标准版本功能限制
	//标准版本限制功能重复数据删除,深度有效数据提取
	// 中文标准版 1
	// 中文企业版 2
	// 英文免费版 4
	// 英文基础版 9
	// 英文标准版 6
	// 英文企业版 7
	var initSoftwareVersionDiff = function(){
		pAjaxRequest({}, "/api/v1/users/auth_func", "GET", function (d) {
			let data = d.data;
			authFun = data;
			if(!data.dedupication){
				//设置重删为关闭并不可用再加隐藏
				$('.deduplicationdiv').hide();
			}
			if(!data.lanfree){
				//删除transport_mode不可用项
				$("#transport_mode option[value='san']").remove();
				//删除vmwaretransport_mode不可用项
				$("#vmwaretransport_mode option[value='san']").remove();
				//删除xentransmode不可用项
				$("#xentransmode option[value='2']").remove();
				$("#xentransmode option[value='4']").remove();
				
				//删除类xen不可用项
				$("#leixentransmode option[value='2']").remove();

				//删除smartx不可用项
				$("#smartxtransmode option[value='2']").remove();
				
				//删除华为传输模式不可用项
				$("#huaweitransport_mode option[value='san']").remove();
				
				//华为KVM
				$("#huaweikvmtransport_mode option[value='2']").remove();
				//红帽传输模式
				$("#redhattransmode option[value='2']").remove();

				//openstack
				$("#openstacktransmode option[value='4']").remove();

				// 提示信息显示没有lanfree的
				$(`.transmode-tips`).hide();
				$(`.transmode-no-lanfree-tips`).show();
			}
			if(!data.vcbt){
				//设置有效数据提取关闭并不可用再加隐藏
				$('#parsefscheck').bootstrapSwitch('state', false);
				$('#parsefscheck').bootstrapSwitch('disabled', true);
				$('.parsefsDiv').hide();
				$(`#li_high_vcbt`).hide();
				
				//深度有效数据提取结果显示隐藏
				$('.parsefsmodeshow').hide();
				$('.swapmodeshow').hide();
				$('.deletefilemodeshow').hide();
				$('.gapmodeshow').hide();
			}
			//中文标准版不支持重删
			if(1 == CONF.SOFTWARE){
				//设置重删为关闭并不可用再加隐藏
				$('.deduplicationdiv').hide();
			}
			if(1 == CONF.SOFTWARE || 4 == CONF.SOFTWARE){
				//传输模式只支持网络传输
				//删除transport_mode不可用项
				$("#transport_mode option[value='san']").remove();
				$("#transport_mode option[value='hotadd']").remove();
				//删除vmwaretransport_mode不可用项
				$("#vmwaretransport_mode option[value='san']").remove();
				//删除xentransmode不可用项
				$("#xentransmode option[value='2']").remove();
				$("#xentransmode option[value='4']").remove();
				
				//删除类xen不可用项
				$("#leixentransmode option[value='2']").remove();

				//删除smartx不可用项
				$("#smartxtransmode option[value='2']").remove();
				
				//删除华为传输模式不可用项
				$("#huaweitransport_mode option[value='san']").remove();
				//红帽传输模式
				$("#redhattransmode option[value='2']").remove();

				//openstack
				$("#openstacktransmode option[value='4']").remove();

				// 提示信息显示没有lanfree的
				$(`.transmode-tips`).hide();
				$(`.transmode-no-lanfree-tips`).show();
				
				//设置有效数据提取关闭并不可用再加隐藏
				$('#parsefscheck').bootstrapSwitch('state', false);
				$('#parsefscheck').bootstrapSwitch('disabled', true);
				$('.parsefsDiv').hide();
				$(`#li_high_vcbt`).hide();
				
				//深度有效数据提取结果显示隐藏
				$('.parsefsmodeshow').hide();
				$('.swapmodeshow').hide();
				$('.deletefilemodeshow').hide();
				$('.gapmodeshow').hide();
			}
			if(4 == CONF.SOFTWARE || 9 == CONF.SOFTWARE){
				$("#incmodel option[value='2']").remove();      //隐藏高速模式
			}

			// 隐藏安全策略配置和描述
			if (!authFun.worm) {
				$(`#wormConfig`).hide();
			}
			if (!authFun.integrity) {
				$(`#completeConfig`).hide();
			}
			if (!CONF.FUNCTIONS.includes('virusKill')) {
				$(`#virusConfig`).hide();
			}
			if (!authFun.worm && !authFun.integrity && !CONF.FUNCTIONS.includes('virusKill')) {
				// 全部隐藏
				$(`.safeLi`).hide();
				$(`.safeDiv`).hide();
			}
		}, false);
		
	}
	
	//XenServer传输模式改变
	var xentransModeChange = function(){
		var value = $(this).val();
		if(3 == value || 4 == value){
			$('#incmodel').val(3).prop('disabled', true);
		}else{
			$('#incmodel').prop('disabled', false);
		}
		
		//网络传输支持支持数据传输网段和加密传输
		if(value == 1){
			$('.ipSegmentDiv').show();	//显示数据传输网段
			$('.encrypttransferdiv').show();	//加密传输
		}else{
			$('.ipSegmentDiv').hide();	//隐藏数据传输网段
			$('#ipSegment').val('');
			$('.encrypttransferdiv').hide();	//加密传输
			$('.transfer-encrypt-method-form').hide();	//加密传输算法
			$('#encrypttransfer').bootstrapSwitch('state', false);
		}
		
	}
	
	//openstack传输模式改变
	var openstackModeChange = function(){
		var value = $(this).val();
		if(3 == value){
			$('.applianceselectdiv').show();
			$('.applianceshow').show();
			if(!applianceFlag){
				initApplianceSelect();
			}
		}else{
			$('.applianceselectdiv').hide();
			$('.applianceshow').hide();
		}
		
		//网络传输支持支持数据传输网段和加密传输
		if(value == 1){
			$('.ipSegmentDiv').show();	//显示数据传输网段
			$('.encrypttransferdiv').show();	//加密传输
		}else{
			$('.ipSegmentDiv').hide();	//隐藏数据传输网段
			$('#ipSegment').val('');
			$('.encrypttransferdiv').hide();	//加密传输
			$('.transfer-encrypt-method-form').hide();	//加密传输算法
			$('#encrypttransfer').bootstrapSwitch('state', false);
		}
	}
	
	//选中或取消某个时间策略
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
				//取消完备同时取消增量和差异
				$('#strategymode').find('input[data-mode=2]').iCheck('uncheck');
				$('#stragegyaccordion').find('.strategy-panel[data-mode=2]').hide();
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
				//选中差异同时选中完备 取消增量和永久增量
				$('#strategymode').find('input[data-mode=1]').iCheck('check');
				$('#stragegyaccordion').find('.strategy-panel[data-mode=1]').show();
				$('#strategymode').find('input[data-mode=2]').iCheck('uncheck');
				$('#stragegyaccordion').find('.strategy-panel[data-mode=2]').hide();
				$('#strategymode').find('input[data-mode=9]').iCheck('uncheck');
				$('#stragegyaccordion').find('.strategy-panel[data-mode=9]').hide();
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

		//永久增量屏蔽GFS
		if (9 == mode && !event.target.checked) {
			$('#GFSflag').bootstrapSwitch("state", false); //关闭
			$('#GFSflag').bootstrapSwitch("disabled", true);//禁用
		} else {
			$('#GFSflag').bootstrapSwitch("disabled", false);
		}

		setTimeout(initWormConfig, 0);
	}
	
	//是否启动V-CBT
	var parsefsChange = function(){
		if(this.checked){
			$('#swapcheck').bootstrapSwitch('state', true);  
			$('#deletefilecheck').bootstrapSwitch('state', false);  
			$('#gapcheck').bootstrapSwitch('state', true);  
			
			$('.swapdiv').show();
			$('.deletefilediv').hide();
			$('.gapdiv').show();
			
			$('.swapmodeshow').show();
			$('.deletefilemodeshow').hide();
			$('.gapmodeshow').show();
		}else{
			$('#swapcheck').bootstrapSwitch('state', false);  
			$('#deletefilecheck').bootstrapSwitch('state', false);  
			$('#gapcheck').bootstrapSwitch('state', false);  
			$('.swapdiv').hide();
			$('.deletefilediv').hide();
			$('.gapdiv').hide();
			
			$('.swapmodeshow').hide();
			$('.deletefilemodeshow').hide();
			$('.gapmodeshow').hide();
		}
	}
	
	var initSpinner = function(){
		initReserveSpinner($('#spinnerDay'));
		initReserveSpinner($('#spinnerNum'));
        $('#speedSpinnerNum').spinner({value:10, step: 5, min: 1, max: 10000000000});
        $('.backupThreadDiv').spinner({value:3, step: 1, min: 1, max: 8});
        $('.ptVmInputDiv').spinner({value:1, step: 1, min: 1});
        $('.ptDiskInputDiv').spinner({value:1, step: 1, min: 1});
        $('.ptSegmentInputDiv').spinner({value:3, step: 1, min: 1, max: 8});
		$('.snapshotDelSpeedInputDiv').spinner({value:30, step: 5, min: 10, max: 500});
	}
	
	//备份类型
	var backupTypeHandler = function(){
		if('strategy' == this.value){
			//GFS
			$('#GFSflag').bootstrapSwitch('disabled',false);
			//按策略备份
			$('.setStrategy').show();
			$('.setOnceTime').hide();
			$('#reserveType').removeAttr("disabled");
			data.reserved_strategy.reserved_type = parseInt($('#reserveType').val());
			$('#spinnerNum').spinner('enable');
			$('#spinnerDay').spinner('enable');
			$('#spinnerNum').spinner('value', 30);
			$('#spinnerDay').spinner('value', 30);
		}else if('oncetime' == this.value){
			//GFS
			$('#GFSflag').bootstrapSwitch("state",false); //关闭
			$('#GFSflag').bootstrapSwitch("disabled",true);//禁用
			//一次性备份
			$('.setStrategy').hide();
			$('.setOnceTime').show();
			//一次性备份只是按个数保留
			$('#reserveType').prop("disabled","disabled");
			$('#reserveType').val(1);
			data.reserved_strategy.reserved_type = parseInt($('#reserveType').val());
			$('.reserveDay').hide();
			$('.reserveNum').show();
			$('#spinnerNum').spinner('disable');
			$('#spinnerDay').spinner('disable');
			$('#spinnerNum').spinner('value', 1);
			$('#spinnerDay').spinner('value', 1);
		}
		data.time_strategy.type = this.value;
		$('.settimetip').hide();
		kvmOncetimeSnapShotCheck(data.hypervisor_type, this.value);

		// 切换备份方式后重新初始化worm
		initWormConfig();
	}
	
	//初始化时间计时器
	var initServerTime = function(data){
		var getDate = function(unix){ 
			var polishing = function(d){
				return d < 10 ? '0' + d : d;
			}
			var date = new Date(parseInt(unix));
			Y = date.getFullYear() + '-';
			M = (date.getMonth()+1 < 10 ? '0'+(date.getMonth()+1) : date.getMonth()+1) + '-';
			D = polishing(date.getDate()) + ' ';
			h = polishing(date.getHours()) + ':';
			m = polishing(date.getMinutes()) + ':';
			s = polishing(date.getSeconds());
			return Y+M+D+h+m+s;
		} 
		var servertime = $('#servertime');
		var updateInterval = 1000;
		var startClock = function(){
			if(0 == $('#servertime').size()){
        		clearTimeout(timerTask.VMBackup_serverTime);
        		return;
        	}
			servertime.html(getDate(_timeStamp));
			_timeStamp += 1000;
			timerTask.VMBackup_serverTime = setTimeout(startClock, updateInterval);
		}
		if(_timeStamp) return;
		$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM,f:'getSystemTimeJSFormat',p:{}}, function(data){
			_timeStamp = new Date(data).getTime();
			startClock();
		});
	}
	
	var step1Valid = function(){
		// 授权检测
		licenseCheck();
		if (!isLicense) {
			// 授权失效。不允许下一步
			UIToastr.showWarning(LANG.UI_LICENSE_AUTH_INFO_TITLE,  LANG.UI_LICENSE_AUTH_INFO_EXPIRED);
			return false;
		}

		//var selectNodes = currentTree.getCheckedNodes();
		var nodes = currentTree.getCheckedNodes();
		//console.log(nodes);return false;
		// for(var i=0;i<selectNodes.length;i++){
		// 	if(selectNodes[i].eventtype == "vm"){
		// 		nodes.push(selectNodes[i]);
		// 	}
		// }
		if(!nodes.length){
			let lang = SUB_MODULE_TYPE_PRIVATE_CLOUD == sub_module_type ? LANG.UI_BACKUP_SELECT_INSTANCE_TIPS : LANG.UI_BACKUP_SELECT_VM_TIPS;
			$(".selectvmtip").html(lang).show();
			// 动态设置tab-pane的高度
			$(".tab-pane__row").css('height', 'calc(100% - 80px)');
			return false;
		}
		var showStr = '';
		var showExcludeVmStr = ''; //排除虚拟机描述
		//添加disk_list和最后一步显示已排除磁盘列表
		for(var i=0;i<nodes.length;i++){
			data.src_info.exclude_disk_list[i] = [];
			if('vm' !== nodes[i].eventtype){
				if(nodes[i].diskChecked){
					data.src_info.exclude_disk_list[i] = {
						'disk_list' : nodes[i].diskChecked.disk_list
					};
				}
			} else {
				data.src_info.exclude_disk_list[i] = {
					'disk_list' : []
				};
				if (nodes[i].diskChecked) {
					// 原有任务排除的
					$.merge(data.src_info.exclude_disk_list[i].disk_list, nodes[i].diskChecked.disk_list);
				}
				if (nodes[i].detail && nodes[i].detail.disk_list) {
					// 已展开磁盘的以展开后的排除结果为准
					var diskCount =nodes[i].detail['disk_list'].length;
					var disk_list = [];
					for(var j=0;j<diskCount;j++){
						var diskInfo = {};
						if(nodes[i].initDisk && !$('#disCheck'+j+ escapeJquery(nodes[i].id) + escapeJquery(nodes[i].vcenteruuid)).is(':checked')){
							diskInfo.disk_name = $('#disCheck' + j + escapeJquery(nodes[i].id) + escapeJquery(nodes[i].vcenteruuid)).val();
							diskInfo.disk_uuid = $('#disuuid' +  j + escapeJquery(nodes[i].id) + escapeJquery(nodes[i].vcenteruuid)).text();
							disk_list.push(diskInfo);
						}
					}
					data.src_info.exclude_disk_list[i] ={
						'disk_list' : disk_list,
					};
				}
			}
		}
		data.src_info.type = showType;
		data.src_info.global_select = globalSelect;
		data.src_info.vm_info = [];
		showStr += CONF.VM_DES[nodes[0].hypervisor] + LANG.UI_PUBLIC_BACKUP + "<br>";
		var count = 0;
		$.each(nodes, function(i, d){
			if(data.hypervisor_type != d.hypervisor){
				initConfigFlag = false;	//切换虚拟化类型重置高级配置默认选项
			}
			data.hypervisor_type = d.hypervisor;
			hypervisorType = d.hypervisor;
			if('vm' == d.eventtype){
				data.src_info.vm_info[count] = {
					vm_uuid: d.id,
					platform_uuid: d.vcenteruuid,
					type: d.type,
					path: d.path,
					config: {
						disk_list: data.src_info.exclude_disk_list[count].disk_list
					}
				};
				//虚拟机显示虚拟机+排除列表信息
				let excludeStr = '';
				$.each(data.src_info.exclude_disk_list[count].disk_list, function (i, v) {
					excludeStr += "<br>"+ LANG.UI_BACKUP_EXCLUDE_DISK + v.disk_name;
				});
				showStr +=  d.path + excludeStr + "<br>";
			} else {
				//其他对象显示对象信息+排除虚拟机信息
				showStr += d.path + "<br>";
				var objId = getObjId(d);
				var excludeVmList = backupObjList[objId].exclude_vm_list;
				$.each(excludeVmList, function (i, v) {
					showExcludeVmStr += v.vm_path + "<br>";
				});
				data.src_info.vm_info[count] = {
					vm_uuid: d.id,
					platform_uuid: d.vcenteruuid,
					type: d.type,
					path: d.path,
					config: {
						disk_list: data.src_info.exclude_disk_list[count].disk_list
					},
					exclude_vm_list: excludeVmList
				};
			}
			count++;
		});

		// 最终选择的对象个数
		let selectObjCount = $(`.list-type-obj`).length;
		// 最终选择的vm个数=粒度下vm+单选的vm
		let selectVmCount = $(`.vmCheck:checked`).length + data.src_info.vm_info.filter(item => item.type === 7).length;
		// 关闭自动加入时，判断虚拟机个数不能为0
		if (!data.src_info.auto_join_flag && !selectVmCount && !selectObjCount) {
			let lang = SUB_MODULE_TYPE_PRIVATE_CLOUD == sub_module_type ? LANG.UI_BACKUP_SELECT_INSTANCE_TIPS : LANG.UI_BACKUP_SELECT_VM_TIPS;
			$(".selectvmtip").html(lang).show();
			// 动态设置tab-pane的高度
			$(".tab-pane__row").css('height', 'calc(100% - 80px)');
			return false;
		}

		$(".selectvmtip").hide();
		
		//默认带静默快照选项
		
		$('.snapshotdiv').show();	
		$('.backupmodeshow2').show();

		$('.showBackupmode').show();						//默认显示备份模式tab
		$('#backupmodeshowdiv').show();                     //默认显示备份模式信息
		
		//先隐藏传输模块
		$('.transportdiv').hide();
		$('.huaweitransportdiv').hide();                    //华为xen传输模式类型
		$('.leixentransdiv').hide();                           //类xen传输模式
		$('.smartxtransdiv').hide();                           //smartx传输模式
		$('.sangforvvdktransdiv').hide();                           //sangfor vvdk传输模式
		$('.openstacktransdiv').hide();                           //openstack传输模式
		$('.xentransdiv').hide();                           //XenServer传输模式
		$('.huaweikvmtransportdiv').hide();					//华为kvm传输模式
		$('.redhattransdiv').hide();						//红帽传输模式
		$('.vmwaretransportdiv').hide();					//vmware传输模式
		$('.icsvvdktransportdiv').hide();					//icsvvdk传输模式

		$('.encrypttransferdiv').hide();					//隐藏加密传输
		$('.transfer-encrypt-method-form').hide();			//加密传输算法
		
		$('.cbtdiv').hide();								//隐藏cbt
		$('.resetcbtdiv').hide();								//隐藏cbt
		$('.cbtmodeshow').hide();                            //隐藏cbt信息 
		$('.resetcbtshow').hide();                            //隐藏cbt信息 
		$('#incmodeldiv').hide();							//隐藏增量模式
		$('.appliancediv').hide();			     //隐藏proxy代理模块
		//$('.applianceselectdiv').hide();			     //隐藏proxy代理模块
		$('.applianceshow').hide();				 //隐藏proxy代理描述
		$('.highdiskcbtdiv').hide();	// 隐藏高速磁盘CBT
		$('.highdiskcbtshow').hide();	// 隐藏高速磁盘CBT描述

		$('.transferLi').show();
		$('.highLi').show();
		$('.commonLi').removeClass('active').addClass('active');
		$('.transferLi').removeClass('active');
		$('.safeLi').removeClass('active');
		$('.highLi').removeClass('active');
		$('#tab_common').removeClass('active').addClass('active');
		$('#tab_transfer').removeClass('active');
		$('#tab_safe').removeClass('active');
		$('#tab_other').removeClass('active');

		
		$('.speedlimitDiv').show(); 			//显示限速策略
		$('.threadDiv').show();					//显示多线程
		
		//屏蔽数据加密
		$('.encryptStorageDiv').hide();
//		$('.passwordModeDiv').hide();
//		$('.passwordDiv').hide();
		
		$('.systemIpdiv').hide();	//备份系统IP
		$('.silentDiv').show();									
		$('.uniformDiv').hide();
		if(!initConfigFlag){
			$('.ipSegmentDiv').hide();	//隐藏数据传输网段
			$('#ipSegment').val('');
			$('#parsefscheck').bootstrapSwitch('state', true); //内联数据消重默认开启
			$('#appliancecheck').bootstrapSwitch('state', false); //Appliance默认关闭
			$('#leixentransmode').prop('disabled', false);     //初始化传输模式状态   
			$('#parsefscheck').bootstrapSwitch('state', false);   	//深度有效提取默认关闭
			$('#deletefilecheck').bootstrapSwitch('state', false);  //排除删除文件块默认关闭
			$('#cbtcheck').bootstrapSwitch('disabled', false);  	//cbt默认关闭
			$('#resetcbtdiv').hide();								//修复cbt默认隐藏
			$("#incmodel option[value='3']").remove();
			var option = $("<option>").val(3).text("CBT");
			$('#incmodel').append(option);
			
			$('#incmodel').prop('disabled', false);			//增量模式默认可选择
			$('#preSnapshotcheck').prop('disabled', false);	//提前创建快照
			$('#storageSnapshotCheck').prop('disabled', false);	//创建存储快照
			$('#silentsnapshotcheck').bootstrapSwitch('state', false);	//关闭静默快照
			//默认描述为静默快照
			$('.silentsnapshotlabel').html(LANG.UI_BACKUP_SILENTSNAPSHOT);	
			$('.preSnapshotDiv').show();			//显示提前创建快照
			$('.presnapshotshow').show();

			$("#incmodel option[value='2']").hide();      //隐藏高速模式
			$('.diff').show();
			$('.all-strategy-tips').show();
			$('.icsDiv').hide();
			$('.cbrDiv').hide();

		}
		// $('.all-strategy-tips').show();
		$('.no-diff-tips').hide();
		//初始化高速模式描述
		$('.highSpeed').attr('data-content', LANG.UI_BACKUP_MODE_HIGH_SPEED_TIPS);
		var a = $('.highSpeed');

		//设置重连时间和次数的默认值
		$('#reconnect_time').val(5);
		$('#reconnect_interval').val(5);

		// 统一各虚拟化的初始化配置，优化下面switch那块，特殊判断需保留
		initBackupConfig(data.hypervisor_type);

		switch(data.hypervisor_type){
			case CONF.VM_TYPE.VMWARE:
				if (!authFun.lanfree) {
					$('#vmwaretransport_mode').val('nbd');
					$('.transferCompressSwitchDiv').show();
					$('#transferCompressSwitch').bootstrapSwitch('state', false);
					$('.transferCompressDiv').hide();
					$('#transferCompress').val('unzip');
					$('#asyncTransfer').bootstrapSwitch('state', false);
					$('.asyncTransferDiv').show();
				}
				if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
					if(!initConfigFlag){
						$('#vmwaretransport_mode').val('hotadd');	//默认热添加传输
						$('.transferCompressSwitchDiv').hide();
						$('.transferCompressDiv').hide();
						$('#transferCompress').val('unzip');
						$('#asyncTransfer').bootstrapSwitch('state', false);
						$('.asyncTransferDiv').hide();
					}
					if(9 == CONF.SOFTWARE || 4 == CONF.SOFTWARE) {//essential 和free版本
						$('#vmwaretransport_mode').val('nbd');//默认nbd
						$('.appliancediv').hide();			     //隐藏代理模块
						$('.applianceshow').hide();				 //隐藏proxy配置描述
						$('.transferCompressSwitchDiv').show();
						$('#transferCompressSwitch').bootstrapSwitch('state', false);
						$('.transferCompressDiv').hide();
						$('#transferCompress').val('unzip');
						$('#asyncTransfer').bootstrapSwitch('state', false);
						$('.asyncTransferDiv').show();
					}
				}
				break;
			case CONF.VM_TYPE.CLOUDVIEW:
			case CONF.VM_TYPE.CLOUDVIEWSVM:
				if (!authFun.lanfree) {
					$('#transport_mode').val('nbd');
					$('.transferCompressSwitchDiv').show();
					$('#transferCompressSwitch').bootstrapSwitch('state', false);
					$('.transferCompressDiv').hide();
					$('#transferCompress').val('unzip');
					$('#asyncTransfer').bootstrapSwitch('state', false);
					$('.asyncTransferDiv').show();
				}
				if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
					if(!initConfigFlag){
						$('#transport_mode').val('hotadd');	//默认热添加传输
						$('.transferCompressSwitchDiv').hide();
						$('.transferCompressDiv').hide();
						$('#transferCompress').val('unzip');
						$('#asyncTransfer').bootstrapSwitch('state', false);
						$('.asyncTransferDiv').hide();
					}
					if(9 == CONF.SOFTWARE || 4 == CONF.SOFTWARE) {//essential 和free版本
						$('#transport_mode').val('nbd');//默认nbd
						$('.appliancediv').hide();			     //隐藏代理模块
						$('.applianceshow').hide();				 //隐藏proxy配置描述
						$('.transferCompressSwitchDiv').show();
						$('#transferCompressSwitch').bootstrapSwitch('state', false);
						$('.transferCompressDiv').hide();
						$('#transferCompress').val('unzip');
						$('#asyncTransfer').bootstrapSwitch('state', false);
						$('.asyncTransferDiv').show();
					}
				}
				break;
			case CONF.VM_TYPE.HYPERV:
				$(`#incmodel`).find(`option[value=1]`).text(LANG.UI_PUBLIC_OFF);
				break;
			case CONF.VM_TYPE.CITRIX:
			case CONF.VM_TYPE.XCPNG:
				if(CONF.SOFTWARE == 9){ //如果是英文基础版
					$('#cbtcheck').bootstrapSwitch('state', false);
					$('#cbtcheck').bootstrapSwitch('disabled', true);
					$('.cbtdiv').hide();
					$('.cbtmodeshow').hide();
					$('.resetcbtshow').hide();
				}
				break;
			case CONF.VM_TYPE.FUSIONKVM:
			case CONF.VM_TYPE.XFUSIONKVM:
				if(!initConfigFlag){
					$('#huaweikvmtransport_mode').val('5').prop('disabled', true);				//默认AP_LAN
					$('#incmodel').val('3')                 			//默认CBT
					$('#incmodel').prop('disabled', true);
					$('.encrypttransferdiv').hide();				//隐藏加密传输
					$('.transfer-encrypt-method-form').hide();	//加密传输算法
					$('#encrypttransfer').bootstrapSwitch('state', false);
				}
				//NBD传输显示备份系统IP参数
				var  mode = $('#huaweikvmtransport_mode').val();
				if(mode == 5){
					$('.systemIpdiv').show();	//备份系统IP
					$('.encrypttransferdiv').hide();				//隐藏加密传输
					$('.transfer-encrypt-method-form').hide();	//加密传输算法
					$('#encrypttransfer').bootstrapSwitch('state', false);
				}else{
					$('.systemIpdiv').hide();	//备份系统IP
					$('.encrypttransferdiv').show();				//显示加密传输
				}
				break;
			case CONF.VM_TYPE.H3CCASCVD:
					//增量模式锁定CBT，显示为自适应
					$('#incmodel').find('option[value="3"]').text(LANG.UI_BACKUP_INCMODE_ADAPT);
				break;
			case CONF.VM_TYPE.INCLOUDKVM:
				if(!initConfigFlag){
					$('#incmodel').val(3);	//默认CBT
				}
				break;
			case CONF.VM_TYPE.SANGFORVVDK:
				var mode = $('#sangforvvdktransmode').val();
				if (3 == mode) {
					$('.applianceselectdiv').show();
					$('.applianceshow').show();
					$('.encrypttransferdiv').show();
				} else {
					$('.applianceselectdiv').hide();
					$('.applianceshow').hide();
					$('.encrypttransferdiv').hide();					//隐藏加密传输
					$('.transfer-encrypt-method-form').hide();	//加密传输算法
				}
				break;
			case CONF.VM_TYPE.FLEXCLOUD:
			case CONF.VM_TYPE.OPENSTACK:
			case CONF.VM_TYPE.FLEXHCS:
			case CONF.VM_TYPE.INCLOUDOPENSTACK:
			case CONF.VM_TYPE.SUGONCLOUDVIEW: //34
			case CONF.VM_TYPE.INSPURCLOUDPLATFORM: //35
			case CONF.VM_TYPE.EASYSTACK: //36
			case CONF.VM_TYPE.FIBERHOMEOPENSTACK: //37
			case CONF.VM_TYPE.CTSIOPENSTACK: //38
			case CONF.VM_TYPE.AWCLOUD: //39
			case CONF.VM_TYPE.HUAWEICLOUDSTACK: //54
				if (!initConfigFlag) {
					// 初始化
					if (authFun.lanfree) {
						$('#openstacktransmode').val(4); //默认lanfree传输
					} else {
						$('#openstacktransmode').val(1);
						$('.encrypttransferdiv').show();
						$('.ipSegmentDiv').show();
					}
				}
				//easystack屏蔽一致性快照
				if (CONF.VM_TYPE.EASYSTACK == data.hypervisor_type || CONF.VM_TYPE.HUAWEICLOUDSTACK == data.hypervisor_type) {
					$('.snapshotdiv').hide();
				} else {
					//描述为一致性快照
					$('.silentsnapshotlabel').html(LANG.UI_BACKUP_HUAWEI_KVM_SILENTSNAPSHOT);
				}
				break;
			case CONF.VM_TYPE.ZSTACK:
			case CONF.VM_TYPE.ZSTACKZSPHERE:
			case CONF.VM_TYPE.NEXAVM:
			case CONF.VM_TYPE.NEXAVMNCSSV:
			case CONF.VM_TYPE.XSKY:
			case CONF.VM_TYPE.CLOUDVIEWKVM:
				if(!initConfigFlag){
					if (authFun.lanfree) {
						$('#leixentransmode').val(2);
						$('.ipSegmentDiv').hide();	//数据传输网段
						$('.encrypttransferdiv').hide();			//加密传输
					} else {
						$('#leixentransmode').val(1);
						$('.ipSegmentDiv').show();	//数据传输网段
						$('.encrypttransferdiv').show();			//加密传输
					}
					$('.transfer-encrypt-method-form').hide();	//加密传输算法
					$('#encrypttransfer').bootstrapSwitch('state', false);
				}
				break;
			case CONF.VM_TYPE.WINHONGKVM:
				if(!initConfigFlag){
					if (authFun.lanfree) {
						$("#leixentransmode").val(2);	//默认SAN传输
					} else {
						$("#leixentransmode").val(1);
					}
				}
				var mode = $('#leixentransmode').val();
				if(mode == 2){
					$('.encrypttransferdiv').hide();					//隐藏加密传输
					$('.transfer-encrypt-method-form').hide();	//加密传输算法
					$('#encrypttransfer').bootstrapSwitch('state', false);
				}else{
					$('.encrypttransferdiv').show();					//显示加密传输
				}
				break;
			case CONF.VM_TYPE.PROXMOX:
			case CONF.VM_TYPE.LENOVOAIO:
				if(!initConfigFlag){
					$("#leixentransmode").val(1);	//默认网络传输
				}
				var mode = $('#leixentransmode').val();
				if(mode == 2){
					$('.encrypttransferdiv').hide();					//隐藏加密传输
					$('.transfer-encrypt-method-form').hide();	//加密传输算法
					$('#encrypttransfer').bootstrapSwitch('state', false);
				}else{
					$('.encrypttransferdiv').show();					//显示加密传输
				}
				break;
			case CONF.VM_TYPE.SMARTX:
			case CONF.VM_TYPE.ARCFRA:
				if (!initConfigFlag) {
					// 初始化
					if (authFun.lanfree) {
						$('#smartxtransmode').val(2); //传输模式默认SAN
					} else {
						$('#smartxtransmode').val(1);
					}
				}
				var mode = $('#smartxtransmode').val();
				if (3 == mode) {
					$('.applianceselectdiv').show();
					$('.applianceshow').show();
					$('.encrypttransferdiv').show();
				} else {
					$('.applianceselectdiv').hide();
					$('.applianceshow').hide();
					$('.encrypttransferdiv').hide();					//隐藏加密传输
					$('.transfer-encrypt-method-form').hide();	//加密传输算法
				}
				break;
			case CONF.VM_TYPE.RHV:
			case CONF.VM_TYPE.OVIRT:
			case CONF.VM_TYPE.ZVIRT:
			case CONF.VM_TYPE.OLVM:
			case CONF.VM_TYPE.HOSTVM:
			case CONF.VM_TYPE.REDVIRT:
			case CONF.VM_TYPE.ROSAVIRT:
				//ImageIO传输隐藏加密传输
				var  mode = $('#redhattransmode').val();
				if(mode == 5){
					$('.encrypttransferdiv').hide();					//隐藏加密传输
					$('.transfer-encrypt-method-form').hide();	//加密传输算法
					$('#encrypttransfer').bootstrapSwitch('state', false);
				}else{
					$('.encrypttransferdiv').show();					//显示加密传输
				}
				break;
			case CONF.VM_TYPE.INSPURVVDK:
			case CONF.VM_TYPE.VOLC:
			case CONF.VM_TYPE.KSPHERE:
				var mode = $('#icsvvdktransport_mode').val();
				if (3 == mode) {
					$('.applianceselectdiv').show();
					$('.applianceshow').show();
					$('.encrypttransferdiv').show();
				} else {
					$('.applianceselectdiv').hide();
					$('.applianceshow').hide();
					$('.encrypttransferdiv').hide();					//隐藏加密传输
					$('.transfer-encrypt-method-form').hide();	//加密传输算法
				}
				break;
		}
		
		//初始化数据加密开关
		if(!initConfigFlag){
			$('#encryptStorageCheck').bootstrapSwitch('state', false);  //默认关闭数据加密
			initConfigFlag = true;
		}
		if(1 == CONF.SOFTWARE || 4 == CONF.SOFTWARE || 9 == CONF.SOFTWARE){
			$("#incmodel option[value='2']").hide();      //隐藏高速模式
			$('#backupmodeshowdiv').hide();						//隐藏备份模式确认
		}

		//判断是否有多线程授权
		if (!authFun.multithread) {
			$('#backupThreadNum').val(1).attr("disabled", true); //单线程
			$('.backupThreadDiv .spinner-group-btn button').prop('disabled', true);
		}

		if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
			if (4 == CONF.SOFTWARE) {
				$('.ipSegmentDiv').hide();  //免费版隐藏数据传输网段
				$('.encryptStorageDiv').hide();//免费版隐藏数据加密
			}

			//英文版判断不支持高速模式
			if (false === authFun.speedkit) {
				$("#incmodel option[value='2']").hide();      //隐藏高速模式
			}
		}
		showStep1(showStr, showExcludeVmStr);


		return true;
	}

	const licenseCheck = function () {
		let p = {
			type: 'a',
			module: SUB_MODULE_TYPE_PRIVATE_CLOUD === parseInt(sub_module_type) ? 'private_cloud' : 'vm'
		};
		pAjaxRequest(p, '/api/v1/system/auth/base_info', "GET", function (result) {
			isLicense = result.data.license_flag;
		}, false);
	}

	/**
	 * 初始化虚拟化的备份配置
	 * @param hypervisor_type
	 */
	const initBackupConfig = (hypervisor_type) => {
		pAjaxRequest({hypervisor_type: hypervisor_type}, "/api/v1/vm/backup_configs", "GET", function (d) {
			_backupConfig = d.data;
			let initConfig = d.data.initConfig;
			// let linkageConfig = d.data.linkageConfig;

			// 传输策略tab
			if (initConfig.tab_transfer) {
				$('.transferLi').show();
			} else {
				$('.transferLi').hide();
			}

			// 高级策略tab
			if (initConfig.tab_high) {
				$('.highLi').show();
			} else {
				$('.highLi').hide();
			}

			// 支持差异备份
			if (initConfig.diff_backup) {
				$('.diff').show();
			} else {
				$('.diff').hide();
			}

			// 传输模式
			let _transportSelector = $(`#${initConfig.transport_mode}`);
			if (initConfig.transport_mode) {
				_transportSelector.closest('.form-group').show(); // 显示div
				if (!initConfigFlag) {
					_transportSelector.val(initConfig.transport_mode_value); // 默认值
				}
				_transportSelector.attr('disabled', initConfig.transport_mode_disable); // 锁定
				$('.transportinfoshow').show(); //显示传输模式描述
			} else {
				_transportSelector.closest('.form-group').hide();
			}
			if (!initConfig.transport_mode && !initConfig.transport_segment) {
				$('.transportinfoshow').hide();
			}

			// 传输压缩
			if (initConfig.transfer_compress) {
				$('.transferCompressSwitchDiv').show();
				if (!initConfigFlag) {
					$('#transferCompressSwitch').bootstrapSwitch('state', 'unzip' !== initConfig.transfer_compress_value);
					$('#transferCompress').val(initConfig.transfer_compress_value);
					if ('unzip' === initConfig.transfer_compress_value) {
						$('.transferCompressDiv').hide();
					} else {
						$('.transferCompressDiv').show();
					}
				}
			} else {
				$('.transferCompressSwitchDiv').hide();
				$('.transferCompressDiv').hide();
				$('#transferCompress').val('unzip');
			}

			// 传输代理
			if (initConfig.transport_agent) {
				$('.appliancediv').show(); // 显示div
				$('.applianceshow').show(); // 显示描述信息
				if (!initConfigFlag) {
					$('#appliancecheck').bootstrapSwitch('state', initConfig.transport_agent_open);
				}
			} else {
				$('.appliancediv').hide();
				$('.applianceselectdiv').hide();
				$('.applianceshow').hide();
			}

			// 传输加密
			if (initConfig.transport_encrypt) {
				$('.encrypttransferdiv').show(); // 显示div
				if (!initConfigFlag) {
					$('#encrypttransfer').bootstrapSwitch('state', initConfig.transport_encrypt_open);
					$('#transferEncryptMethod').val(initConfig.transport_encrypt_value);
				}
			} else {
				$('.encrypttransferdiv').hide();
				$('#encrypttransfer').bootstrapSwitch('state', false);
				$('.transfer-encrypt-method-form').hide();
			}

			// 备份系统IP
			if (initConfig.system_ip) {
				$('.systemIpdiv').show();
			} else {
				$('.systemIpdiv').hide();
			}

			// 数据传输网段
			if (initConfig.transport_segment) {
				$('.ipSegmentDiv').show();
			} else {
				$('.ipSegmentDiv').hide();
			}

			// 异步传输
			if (initConfig.async_transfer) {
				$('.asyncTransferDiv').show();
				if (!initConfigFlag) {
					$('#asyncTransfer').bootstrapSwitch('state', initConfig.async_transfer_open);
				}
			} else {
				$('.asyncTransferDiv').hide();
				$('#asyncTransfer').bootstrapSwitch('state', false);
			}

			// 并行传输相关
			if (initConfig.parallel_transport) {
				$('.parallelTransferDiv').show();
				$('.ptVmDiv').show();
				$('.ptDiskDiv').show();
				$('.ptSegmentDiv').show();
			} else {
				$('.parallelTransferDiv').hide();
				$('.ptVmDiv').hide();
				$('.ptDiskDiv').hide();
				$('.ptSegmentDiv').hide();
			}

			// 数据块大小
			$('#blocksize').val(initConfig.block_size_value);
			if (initConfig.block_size) {
				$('.blocksizediv').show();
			} else {
				$('.blocksizediv').hide();
			}

			// 不支持增量模式、重置CBT、高速磁盘CBT则隐藏左侧增量的tab
			if (!initConfig.inc_mode && !initConfig.reset_cbt && !initConfig.highspeed_disk_cbt) {
				$('#li_high_inc').hide();
			} else {
				$('#li_high_inc').show();
			}

			// 增量模式
			if (initConfig.inc_mode) {
				$('#incmodeldiv').show();
				$('.incmodeshow').show();
			} else {
				$('#incmodeldiv').hide();
				$('.incmodeshow').hide();
			}
			if (initConfig.inc_mode_disable) {
				$('#incmodel').attr('disabled', true);
			} else {
				$('#incmodel').attr('disabled', false);
			}
			// 增量模式是否支持高速模式
			if (initConfig.inc_mode_option_high) {
				$('#incmodel').find('option[value=2]').show();
			} else {
				$('#incmodel').find('option[value=2]').hide();
			}
			// 增量模式是否支持CBT模式
			if (initConfig.inc_mode_option_cbt) {
				$('#incmodel').find('option[value=3]').show();
			} else {
				$('#incmodel').find('option[value=3]').hide();
			}
			if (!initConfigFlag) {
				$('#incmodel').val(initConfig.inc_mode_value); // 默认值
			}
			// 增量模式的气泡提示
			$(`.incmodel-tips-div`).find(`a`).hide();
			$(`.${initConfig.inc_mode_tips_class}`).show();

			// 静默快照
			if (initConfig.silent_snapshot) {
				$('.snapshotdiv').show();
				$('.backupmodeshow2').show();
				$('.silentDiv').show();
				$('.uniformDiv').hide();
			} else {
				$('.snapshotdiv').hide();
				$('.backupmodeshow2').hide();
				$('#silentsnapshotcheck').bootstrapSwitch('state', false);  //静默快照关闭
			}

			// 一致性快照，复用静默快照，只改描述和提示信息
			if (initConfig.consistent_snapshot) {
				$('.snapshotdiv').show();
				$('.silentsnapshotlabel').text(LANG.UI_BACKUP_HUAWEI_KVM_SILENTSNAPSHOT);
				$('.uniformDiv a').attr('data-content', LANG.UI_BACKUP_HUAWEI_KVM_SILENTSNAPSHOT_TIPS);
				if (CONF.VMTYPE_GROUP.OPENSTACK.includes(hypervisor_type)) {
					$('.uniformDiv a').attr('data-content', LANG.UI_BACKUP_PRIVATE_CLOUD_SILENTSNAPSHOT_TIPS);
				}
				$('.silentDiv').hide();
				$('.uniformDiv').show();
			} else {
				$('.snapshotdiv').hide();
			}

			// 快照删除速度
			if (initConfig.snapshot_del_speed) {
				$('.snapshotDelSpeedDiv').show();
				$('.snapshotdelspeedshow').show();
			} else {
				$('.snapshotDelSpeedDiv').hide();
				$('.snapshotdelspeedshow').hide();
			}

			// 重置CBT
			$('#resetcbtLevel').val(initConfig.reset_cbt_value);
			if (initConfig.reset_cbt) {
				$('.resetcbtdiv').show();
				$('.resetcbtshow').show();
			} else {
				$('.resetcbtdiv').hide();
				$('.resetcbtshow').hide();
			}

			// 高速磁盘CBT
            if (initConfig.highspeed_disk_cbt && $.isEmptyObject(data.src_info.pincr_info)) {
				$('.highdiskcbtdiv').show();
				$('.highdiskcbtshow').show();
				if (!initConfigFlag) {
					$('#highdiskcbtcheck').bootstrapSwitch('state', false);  	//高速磁盘cbt默认关闭
				}
			} else {
				$('.highdiskcbtdiv').hide();
				$('.highdiskcbtshow').hide();
			}

			// 合并冗余数据比例
			if (initConfig.merge_mode) {
				$('.mergemodediv').show();
			} else {
				$('.mergemodediv').hide();
			}
		}, false);
	}

	//kvm系（除Sangfor HCI/Sangfor VVDK）和xhere一次性备份默认关闭高速模式，且不可选
	var kvmOncetimeSnapShotCheck = function (vmType, backupType) {
		vmType = parseInt(vmType);
		if ((CONF.VMTYPE_GROUP.KVM.includes(vmType) && CONF.VM_TYPE.SANGFOR != vmType && CONF.VM_TYPE.SANGFORVVDK != vmType || CONF.VM_TYPE.XHERE == vmType) && 'oncetime' == backupType) {
			$("#incmodel option[value='2']").hide();
		} else {
			$("#incmodel option[value='2']").show();
		}
	}
	
	var showStep1 = function(showStr, showExcludeVmStr){
		//初始化计时器
		initServerTime();
		var info = {};
		info.hypervisor_type = data.hypervisor_type;
		pAjaxRequest(info, "/api/v1/vm/jobs/backup/job_name", "GET", function (d) {
			$('#jobname').val(d.data);
		}, false);
		$('.vmshow').html(showStr);
		if (showExcludeVmStr) {
			$('.excludevmshow').html(showExcludeVmStr);
			$('.excludevmshow').closest('.form-group').show();
		} else {
			$('.excludevmshow').html('');
			$('.excludevmshow').closest('.form-group').hide();
		}
	}
	
	var step2Valid = function(){
		if (!$('#backupTarget').backupTarget('validateSelect')) {
			return false;
		}
		var results = getNodeStr();
		initBackupServerAddr();	//初始化备份系统节点IP
		if(results){
			showStep2();
			setStrategyTime(); //设置默认时间策略
			initStrategyDes(); //初始化策略描述
			//如果是华为CBR 设置传输模式为网络传输并禁用 （如果之前没有传输模式 则加上传输模式并设置传输模式为网络传输 如果之前有传输模式 禁止其他传输模式 只有网络传输可选
			setStrategyTransfer(); //设置传输策略
			setHighStrategy(); //设置高级策略
		}
		return results;
	}
	
	var showStep2 = function(){
		// 支持并行传输的虚拟化隐藏传输线程
		if (_backupConfig.initConfig.parallel_transport) {
			$('.threadDiv').hide();
		} else {
			$('.threadDiv').show();
		}

		//备份目的地(节点)
		$('.nodeinfoshow').html(backupTargetInfo.node_text);
		$('.storeinfoshow').html(backupTargetInfo.storage_text);
		// var storageType = $('#selectstorage').find("option:selected").attr('data-type');
		// if (storageType == 10) { //如果是磁带, 不展示保留策略, 线程只能为1
		// 	$('#reserveShowDiv').hide();
		// 	$('.reserverDiv').hide();
		// 	$('#backupThreadNum').val(1).prop('disabled', true);
		// }
		//存储为磁带时，屏蔽保留策略，传输线程禁用，默认是1，屏蔽提示信息
		var storage_type = backupTargetInfo.storage_type;
		var storage_uuid = backupTargetInfo.storage_uuid;
		//判断是否是磁带，参数：存储uuid，存储类型，线程配置项div控制显示隐藏，线程输入框初始化spinner时的dom，初始化备份策略时的dom，模块类型
		getTapeStrategy(storage_uuid, storage_type, '.threadDiv', '.backupThreadDiv', '#tab_common', CONF.MODULE_TYPE.VM);
		// 存储为磁带时，并行传输的线程数全部固定为1
		if (CONF.BD_STORAGE_TYPE.TAPE === parseInt(storage_type)) {
			$(`#ptVmCount`).val(1).prop('disabled', true);
			$(`#ptVmCount`).next().find(`button`).prop('disabled', true);
			$(`#ptDiskCount`).val(1).prop('disabled', true);
			$(`#ptDiskCount`).next().find(`button`).prop('disabled', true);
			$(`#ptSegmentCount`).val(1).prop('disabled', true);
			$(`#ptSegmentCount`).next().find(`button`).prop('disabled', true);
			$(`.parallelTransferDiv`).hide();
		} else {
			$(`#ptVmCount`).prop('disabled', false);
			$(`#ptVmCount`).next().find(`button`).prop('disabled', false);
			$(`#ptDiskCount`).prop('disabled', false);
			$(`#ptDiskCount`).next().find(`button`).prop('disabled', false);
			$(`#ptSegmentCount`).prop('disabled', false);
			$(`#ptSegmentCount`).next().find(`button`).prop('disabled', false);
			if (CONF.VM_TYPE.FUSIONKVM == hypervisorType || CONF.VM_TYPE.XFUSIONKVM == hypervisorType) {
				$(`.parallelTransferDiv`).show();
				$(`.threadDiv`).hide();
			}
		}

		initResourceLimit(backupTargetInfo.node_uuid_list);

		// 初始化安全策略
		initWormConfig();
		$('#virusConfig').virusDetectionBackup('col-md-3', false, true, false);
		$('#completeConfig').completeDetectionBackup('col-md-3', authFun.integrity, CONF.MODULE_TYPE.VM, true);
	}

	const initWormConfig = () => {
		$('#wormConfig').wormProtectionBackup(getWormType(), 'col-md-3');
	}

	// wormType: 1为正常配置worm，2为备份存储没有开启worm保护，3为选择的是存储资源池，4为永久增量备份
	const getWormType = () => {
		if ($('#backuptype').val() === 'strategy' && $('#pincrBackup').prop('checked')) {
			// 按策略备份并选择了永久增量
			return $.fn.wormDefine.worm_type.p_incr_backup;
		} else if (!data.advanced_strategy.node.storage_uuid) {
			// 没有选择存储
			return $.fn.wormDefine.worm_type.no_storage;
		} else if (!backupTargetInfo.storage_worm_config.flag) {
			// 存储未开启worm
			return $.fn.wormDefine.worm_type.no_worm;
		} else {
			return $.fn.wormDefine.worm_type.normal;
		}
	}
	
	var step3Valid = function(){
		var result = getTimeStr();
		if(!result) return false;
		var result = getSpeedStr() & getReserveStr() & getAchiveStr() & getTransferStr() & getStoreStr() & getSafeStr() & getHighStr() & getRetryStr() & proxystate() & proxystateOther() & getVerifyStr();
		if(result){
			result = result && showStep3();
		}
		return result;
	}
	
	var proxystate = function(){
		var enc_trans = $("#encrypttransfer").bootstrapSwitch('state');
		var openstacktransmode =$("#openstacktransmode").val();
		if(enc_trans && openstacktransmode == 3){
			let applianceNode = $('#transferAgentTree').transferAgent('getSelect');
			if (false === applianceNode) {
				UIToastr.showWarning(LANG.UI_APPLIANCE_SELECT, LANG.UI_APPLIANCE_SELECT_NO_TIPS);
				return false;
			}
		}
		return true;
	}
	
	var proxystateOther = function(){
		var enc_trans = $("#appliancecheck").bootstrapSwitch('state');
		if(enc_trans){
			let applianceNode = $('#transferAgentTree').transferAgent('getSelect');
			if (false === applianceNode) {
				UIToastr.showWarning(LANG.UI_APPLIANCE_SELECT, LANG.UI_APPLIANCE_SELECT_NO_TIPS);
				return false;
			}
		}
		return true
	}

	var getTimeStr = function(){
		var strategyConfig = $('#backupTimestrategy').getStrategyConfig();
		data.time_strategy.type = $('#backuptype').val();
		var strategyMode = $('#strategymode').find('input:checked');
		if("strategy" == data.time_strategy.type){
			if(0 == strategyMode.length){
				//没有选择时间策略
				if(data.advanced_strategy.node.storage_type == 9 || data.advanced_strategy.node.storage_type == 12 || data.advanced_strategy.node.storage_type == 10){
					UIToastr.showInfo(LANG.UI_GLOBAL_STRATEGY_TIME, LANG.UI_VM_BACKUP_SET_STRATEGY_TIPS);
				}else{
					UIToastr.showInfo(LANG.UI_GLOBAL_STRATEGY_TIME, LANG.UI_BACKUP_SET_STRATEGY_TIPS);
				}
				return false;
			}else{
				for(var i=0; i<strategyMode.length; i++){
					if(1 == $(strategyMode[i]).data('mode')){
						if(strategyConfig.fullInfo.rollFlag && !checkTime(strategyConfig.fullInfo.startTime,strategyConfig.fullInfo.endTime)) return;
						data.time_strategy.full_info = groupTimeStrategy(strategyConfig.fullInfo);
					}else if(2 == $(strategyMode[i]).data('mode')){
						if(strategyConfig.incrInfo.rollFlag && !checkTime(strategyConfig.incrInfo.startTime,strategyConfig.incrInfo.endTime)) return;
						data.time_strategy.incr_info = groupTimeStrategy(strategyConfig.incrInfo);
					}else if(3 == $(strategyMode[i]).data('mode')){
						if(strategyConfig.diffInfo.rollFlag && !checkTime(strategyConfig.diffInfo.startTime,strategyConfig.diffInfo.endTime)) return;
						data.time_strategy.diff_info = groupTimeStrategy(strategyConfig.diffInfo);
					}else if(9 == $(strategyMode[i]).data('mode') && !cloudTargetFlag){
						if(strategyConfig.pIncrInfo.rollFlag && !checkTime(strategyConfig.pIncrInfo.startTime,strategyConfig.pIncrInfo.endTime)) return;
						data.time_strategy.pincr_info = groupTimeStrategy(strategyConfig.pIncrInfo);
					}
				}
				$('.settimetip').hide();
				return true;
			}
		}else if("oncetime" == data.time_strategy.type){
			var onceTime = $('#oncetime').val();
			if("" != onceTime){
				$('.settimetip').hide();
				var systemTime = $('#servertime').text();
				var onceTimeSize = new Date(onceTime).getTime();
				var systemTimeSize = new Date(systemTime).getTime();
				if(onceTimeSize <= systemTimeSize){
					UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_TIME,LANG.UI_BACKUP_SET_CORRENT_TIME_TIPS);
					return false;
				}
				data.time_strategy.datetime = onceTime;
				return true;
			}else{
				UIToastr.showInfo(LANG.UI_GLOBAL_STRATEGY_TIME,LANG.UI_BACKUP_SET_TIME_TIPS);
				return false;
			}
		} else if ('manual' === data.time_strategy.type) {
			return true;
		}
		UIToastr.showInfo(LANG.UI_GLOBAL_STRATEGY_TIME, 'Time Strategy Type Error');
		return false;
	}

	//组合时间策略
	var groupTimeStrategy = function (strategyConfigItem) {
		return {
			type: strategyConfigItem.type,
			start_time: strategyConfigItem.startTime,
			roll_flag: strategyConfigItem.rollFlag,
			roll_interval: strategyConfigItem.rollInterval,
			roll_end_time: strategyConfigItem.endTime,
			days: strategyConfigItem.days,
			frequency: strategyConfigItem.frequency,
			des: strategyConfigItem.des,
			full_backup_compensation_flag: strategyConfigItem.full_backup_compensation_flag
		}
	}
	
	var getSpeedStr = function(){
		data.speed_strategy = getSpeedStrategyInfo();
		return true;
	}
	
	var showStep3 = function(){
		//时间策略
		$('.backupTypeInfoDiv').show();
		var strategyMode = $('#strategymode').find('input:checked');
		var backuptypeshow = $('.backuptypeshow'), backuptypeinfoshow = $('.backuptypeinfoshow');
		var backuptypeshowStr = '', backuptypeinfoshowStr = '';
		if("strategy" == data.time_strategy.type){
			backuptypeshowStr = LANG.UI_BACKUP_USE_STRATEGY;
			for(var i=0; i<strategyMode.length; i++){
				if(1 == $(strategyMode[i]).data('mode')){
					backuptypeinfoshowStr += data.time_strategy.full_info.des + "<br>";
				}else if(2 == $(strategyMode[i]).data('mode')){
					backuptypeinfoshowStr += data.time_strategy.incr_info.des;
				}else if(3 == $(strategyMode[i]).data('mode')){
					backuptypeinfoshowStr += data.time_strategy.diff_info.des;
				}else if(9 == $(strategyMode[i]).data('mode') && !cloudTargetFlag){
					backuptypeinfoshowStr += data.time_strategy.pincr_info.des;
				}
			}
		}else if("oncetime" == data.time_strategy.type){
			backuptypeshowStr = LANG.UI_BACKUP_ONCE;
			backuptypeinfoshowStr = LANG.UI_PUBLIC_START_TIME + ": " + data.time_strategy.datetime;
		} else if ('manual' === data.time_strategy.type) {
			backuptypeshowStr = LANG.UI_BACKUP_MANUAL;
			$('.backupTypeInfoDiv').hide();
		}
		backuptypeshow.html(backuptypeshowStr);
		backuptypeinfoshow.html(backuptypeinfoshowStr);

		//限速策略
		var speedlimitshow = $('.speedlimitshow');
		var speedLimitsStr = '';
		if (data.speed_strategy.speedInfo.length != 0) {
			speedLimitsStr = '';
			for (let i = 0; i < data.speed_strategy.speedInfo.length; i++) {
				speedLimitsStr += data.speed_strategy.speedInfo[i].des + '<br>';
			}
		}
		if (speedLimitsStr == '') {
			speedLimitsStr = LANG.UI_PUBLIC_NOTHING;
		}
		speedlimitshow.html(speedLimitsStr);

		var reservetypeshow = $('.reservetypeshow');
		var reservetypeStr = '';
		// 保留类型
		if(CONF.RESERVE_STRATEGY_MODE.POINT == data.reserved_strategy.strategyMode){
			reservetypeStr = LANG.UI_RESERVE_RETENTION_TYPE + ': ' + LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_POINT + '<br>';
		}else if(CONF.RESERVE_STRATEGY_MODE.CHIAN == data.reserved_strategy.strategyMode){
			reservetypeStr = LANG.UI_RESERVE_RETENTION_TYPE + ': ' + LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_CHAIN + '<br>';
		}
		if(CONF.RESERVE_TYPE.NUM == data.reserved_strategy.reserved_type){
			reservetypeStr += LANG.UI_RESERVE_RETENTION_MODE + ': ' + LANG.UI_BACKUP_NUM + '<br>';
		}else if(CONF.RESERVE_TYPE.DAY == data.reserved_strategy.reserved_type){
			reservetypeStr += LANG.UI_RESERVE_RETENTION_MODE + ': ' + LANG.UI_BACKUP_DAY + '<br>';
		}
		var methoddes = LANG.UI_STRATEGY_VALUE;
		if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw" && CONF.RESERVE_TYPE.DAY == data.reserved_strategy.reserved_type){
			methoddes = LANG.UI_GLOBAL_STRATEGY_RESERVE_DAY
		}
		reservetypeStr += methoddes + ': ' + data.reserved_strategy.value;
		// //确认页面GFS描述
		// var GFSstr = $(".GFSLable").html();
		// reservetypeStr +="</br>"+ GFSstr + ': ' + getSwitchDes($('#GFSflag').get(0).checked);
		// if($('#GFSflag').get(0).checked){
		// 	reservetypeStr +="</br>"+ + LANG.UI_JOB_GFS_CONFIGURATION + ":" + $("#GFSDiv").getGFSDes();
		// }
		
		
		
		//保留策略
		if($("#selectstorage option:selected").attr("data-type") != 10) {
			//不是磁带，显示以前的保留策略
			$('.reservetypeshow').html(reservetypeStr);
		}
		
		//存储策略
		var deduplicationLabel = $('.deduplicationLabel').html();
		var blocksizelabel = $('.blocksizelabel').html();
		var compressLabel = $('.compressLabel').html();
		var encryptlabel = $('.encryptlabel').html();
		var storeInfo = "";
		if(CONF.FUNCTIONS.includes('dedupication') && 1 != CONF.SOFTWARE && 4 != CONF.SOFTWARE){
			storeInfo += deduplicationLabel + ": " + getSwitchDes(data.storage_strategy.deduplication_flag) + "<br>";
		}
		storeInfo += compressLabel + ": " + getSwitchDes(data.storage_strategy.compress_flag) + "<br>";
		// 压缩等级
		if(data.storage_strategy.compress_flag){
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
            storeInfo += LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE + ": " + grade;
		}

		// 安全策略
		let safeInfo = '';
		if (authFun.worm && backupTargetInfo.storage_worm_config.flag) {
			safeInfo += LANG.UI_BACKUP_DATA_DETAIL_ACTION_WORM + ": " + getSwitchDes(data.safe_strategy.worm_flag);
			if (data.safe_strategy.worm_flag) {
				safeInfo += '<br>' + LANG.UI_SAFE_STRATEGY_WORM_PROTECT_PERIOD + ": " + data.safe_strategy.worm_protection_time + LANG.UI_PUBLIC_UNIT_DAY;
			}
			safeInfo += '<br>';
		}
		if (CONF.FUNCTIONS.includes('virusKill')) {
			safeInfo += $('#virusConfig').getVirusDetectionBackup().str; // 病毒检测的描述
		}
		if (authFun.integrity) {
			if (data.safe_strategy.integrity_check_flag) {
				safeInfo += LANG.UI_VM_INTEGRITY_VERIFY + ": "
				safeInfo += $('#completeConfig').getCompleteDetectionBackup().str;
			} else {
				safeInfo += LANG.UI_VM_INTEGRITY_VERIFY + ": " + LANG.UI_GLOBAL_STRATEGY_EMPTY; // 未配置
			}
		}
		$('.safeStrategyShow').html(safeInfo);

		
		//备份模式
		var backupmode2Info = $('.silentsnapshotlabel').html() + ": " + getSwitchDes(data.advanced_strategy.mode.silent_snapshot_flag);
		var incmodeInfo = $('.incmodellabel').html() + ": " + $('#incmodel option:selected').text();
		//CBT
		var cbtmodeInfo = $('.cbtlabel').html() + ": " + getSwitchDes(data.advanced_strategy.mode.cbt_flag);
		var resetcbtInfo = $('.resetcbtlabel').html() + ": " + $('#resetcbtLevel option:selected').text();
		// 高速磁盘CBT
		var diskcbtInfo = $('.highdiskcbtlabel').html() + ": " + getSwitchDes(data.advanced_strategy.mode.highspeed_disk_cbt_flag);
		
		//内联数据消重
		var parsefsmodeInfo = $('.parsefslabel').html() + ": " + getSwitchDes(data.advanced_strategy.mode.parse_fs_flag);
		//排除交换文件块
		var swapmodeInfo = $('.swaplabel').html() + ": " + getSwitchDes(data.advanced_strategy.mode.swap_flag);
		//排除删除文件块
		var deletefilemodeInfo = $('.deletefilelabel').html() + ": " + getSwitchDes(data.advanced_strategy.mode.delete_file_flag);
		//排除分区间隙
		var gapmodeInfo = $('.gaplabel').html() + ": " + getSwitchDes(data.advanced_strategy.mode.gap_flag);
		//线程数量
		var threadnumInfo = $('.threadnumlabel').html() + ": " + $('#backupThreadNum').val();
		// 并行传输
		let ptVmCountInfo = $('.ptVmLabel').html() + ": " + $('#ptVmCount').val();
		let ptDiskCountInfo = $('.ptDiskLabel').html() + ": " + $('#ptDiskCount').val();
		let ptSegmentCountInfo = $('.ptSegmentLabel').html() + ": " + $('#ptSegmentCount').val();

		//快照模式
		var snapshotmodeInfo = $('.snapshotTypelabel').html() + ": " + $('#snapshottype').find("option:selected").text();
		//提前创建虚拟机快照
		var presnapshotInfo = $('.preSnapshotlabel').html() + ": " + getSwitchDes(data.advanced_strategy.mode.pre_snapshot_flag);
        // 数据块大小
        var blocksizeInfo = blocksizelabel + ": " + $('#blocksize option:selected').text();
		// 数据文件大小
		var datacontainersizeInfo = $('.datacontainersizelabel').html() + ": " + $('#data_container_size option:selected').text();
		// 合并冗余数据比例
		var mergemodeInfo = $('.mergemodelabel').html() + ": " + $(`#redundant_data_proportion option:selected`).html();
		//创建存储快照
		var storagesnapshotInfo = $('.storageSnapshotLabel').html() + ": " + getSwitchDes(data.advanced_strategy.mode.storage_snapshot_flag);
		// 快照删除速度
		var snapshotdelspeedInfo = $('.snapshotDelSpeedLabel').html() + ": " + data.advanced_strategy.mode.snapshot_del_speed + " MB/s";
		// 忽略资源限制
		var ignoreresourcelimitInfo = $('.ignoreResourceLimitLabel').html() + ": " + getSwitchDes(data.advanced_strategy.mode.ignore_resource_limiting_flag);

		$('.presnapshotshow').html(presnapshotInfo);
		if($('#snapshottype').val() == 2){
			$('.presnapshotshow').hide();
		}else{
			$('.presnapshotshow').show();
		}
		$('.incmodeshow').html(incmodeInfo);

		$('.backupmodeshow2').html(backupmode2Info);
		$('.cbtmodeshow').html(cbtmodeInfo);
		$('.resetcbtshow').html(resetcbtInfo);
		$('.highdiskcbtshow').html(diskcbtInfo);
		
		$('.parsefsmodeshow').html(parsefsmodeInfo);
		$('.swapmodeshow').html(swapmodeInfo);
		$('.deletefilemodeshow').html(deletefilemodeInfo);
		$('.gapmodeshow').html(gapmodeInfo);
		$('.snapshotmodeshow').html(snapshotmodeInfo);
		// $('.storagesnapshotshow').html(storagesnapshotInfo);
		$('.snapshotdelspeedshow').html(snapshotdelspeedInfo);
		$('.threadnumshow').html(threadnumInfo);
		if (CONF.BD_STORAGE_TYPE.TAPE != backupTargetInfo.storage_type) {
			$('.ptVmShow').html(ptVmCountInfo);
			$('.ptDiskShow').html(ptDiskCountInfo);
			$('.ptSegmentShow').html(ptSegmentCountInfo);
		}
		$('.blocksizeshow').html(blocksizeInfo);
		$('.datacontainersizeshow').html(datacontainersizeInfo);
		$('.mergemodeshow').html(mergemodeInfo);
		$('.ignoreresourcelimitshow').html(ignoreresourcelimitInfo);

		var des = "";

		// 统一处理各虚拟化的配置和描述，优化原来的switch那块
		let initConfig = _backupConfig.initConfig;
		let linkageConfig = _backupConfig.linkageConfig;
		let _transportMode = $(`#${initConfig.transport_mode}`);
		data.transport_strategy.mode = _transportMode.val() ?? 1;
		if (data.appliancecheck && linkageConfig.transport_agent_encrypt
			|| data.transport_strategy.mode == 3 && linkageConfig.transport_proxy_encrypt
			|| data.transport_strategy.mode == 1 && linkageConfig.transport_nbd_encrypt
		) {
			// 开启传输代理开关支持加密传输
			// 选中传输代理支持加密传输
			// 网络传输支持加密传输
			des += getEncryptDes();
		}
		// 传输模式描述
		if (initConfig.transport_mode) {
			switch (data.hypervisor_type) {
				case CONF.VM_TYPE.H3C:
				case CONF.VM_TYPE.H3CCASCVD:
				case CONF.VM_TYPE.KVM:
				case CONF.VM_TYPE.SANGFOR:
				case CONF.VM_TYPE.SDCOS:
					//如果存储类型为华为CBR 则显示传输模式为网络传输
					if (data.advanced_strategy.node.storage_type == 12) {
						let leixentranslabel = $('.leixentranslabel').html();
						des += "  " + leixentranslabel + ": " + $('#leixentransmode').find("option:selected").text() + "<br>";
					}
					break;
				default:
					let transferLabel = _transportMode.closest('.form-group').find('.control-label').html();
					des += transferLabel + ": " + _transportMode.find("option:selected").text() + "<br>";
			}
		}
		// 数据加密配置描述
		let encryptStorageLabel = $('.encryptStorageLabel').html();
		let passwordAutoLabel = $('.passwordAutoLabel').html();
		storeInfo += "<br>" + encryptStorageLabel + ": " + getSwitchDes(data.storage_strategy.encrypt_flag);
		if (data.storage_strategy.encrypt_flag) {
			storeInfo += "<br>" + passwordAutoLabel + ": " + getSwitchDes(data.storage_strategy.auto_create_password_flag);
		}
		// 选中传输代理模式时开启传输代理开关
		if (data.transport_strategy.mode == 3) {
			data.appliancecheck = true;
		}

		switch(data.hypervisor_type){
			case CONF.VM_TYPE.VMWARE:
			case CONF.VM_TYPE.CLOUDVIEW:
			case CONF.VM_TYPE.CLOUDVIEWSVM:
				// NBD传输模式显示传输压缩
				if (['nbd', 'nbdssl'].includes(data.transport_strategy.mode)) {
					des += $('.transferCompressSwitchLabel').html() + ": " + getSwitchDes($('#transferCompressSwitch').bootstrapSwitch('state')) + "<br>";
					if ($('#transferCompressSwitch').bootstrapSwitch('state')) {
						des += $('.transferCompressLabel').html() + ": " + $('#transferCompress').find('option:selected').html() + "<br>";
					}
				}
				// 异步传输
				des += $('.asyncTransferLabel').html() + ": " + getSwitchDes(data.transport_strategy.async_transfer)+ "<br>";
				break;
			case CONF.VM_TYPE.FUSIONKVM:
			case CONF.VM_TYPE.XFUSIONKVM:
				//华为KVM NBD传输模式参数备份系统IP
				if(data.transport_strategy.mode == 5){
					//NBD传输+备份系统IP
					des +=	backupServerIpInfo.des;
				}
				break;
		}

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
            storeInfo += "<br>" + encryptedMethodLabel + ": " + grade + "<br>";
		}
		
		//存在数据传输网段
		if(data.transport_ip_segment != ""){
			var ipsegmentlabel = $('.ipsegmentlabel').html();
			des += " " + ipsegmentlabel + ": " + data.transport_ip_segment + "<br>";
		}

		//传输策略信息展示
		$('.transportinfoshow').html(des);
		//检查备份系统节点IP
		if((data.hypervisor_type == CONF.VM_TYPE.FUSIONKVM || data.hypervisor_type == CONF.VM_TYPE.XFUSIONKVM)
			&& data.transport_strategy.mode == 5 && "" == data.backup_server_ip){
			UIToastr.showInfo(LANG.UI_STRATEGY_TRANSFER,LANG.UI_BACKUP_SERVER_IP_EMPTY_TIPS);
			return false;
		}
		var appliancelabel = $('.appliancelabel').html();
		var applianceInfo = appliancelabel + ": " + getSwitchDes(data.appliancecheck);
		if(data.appliancecheck){
			let applianceNode = $('#transferAgentTree').transferAgent('getSelect');
			if (false === applianceNode) {
				return false;
			}
			if (applianceNode.agent_uuid) {
				applianceInfo += ', ' + applianceNode.name + '(' + LANG.UI_AGENT_POOL_TABLE_AGENT + ')';
			} else {
				applianceInfo += ', ' + applianceNode.name + '(' + LANG.UI_AGENT_POOL + ')';
			}
			data.transport_strategy.agent_uuid = applianceNode.agent_uuid;
			data.transport_strategy.agent_pool_uuid = applianceNode.agent_pool_uuid;
		}else{
			data.transport_strategy.agent_uuid = "";
			data.transport_strategy.agent_pool_uuid = '';
		}
		$('.applianceshow').html(applianceInfo);
		$('.storageinfoshow').html(storeInfo);
		$('#strategydesbr').show();
		//如果类型为华为CBR 存储策略只显示数据块大小
		if(data.advanced_strategy.node.storage_type == 12){
			$('.storageinfoshow').html("");
			storeInfo =  blocksizelabel + ": " + $('#blocksize option:selected').text();
			$('.storageinfoshow').html(storeInfo);
			$('#verifymodeshowdiv').show();
			var verifymodeshowStr = $('.verifyLable').html() + ": " +  getSwitchDes($('#verifyflag').get(0).checked);
			if($('#verifyflag').get(0).checked){
				verifymodeshowStr += "<br>"+ $('.verifyTypeLable').html()+": "+$('#verifytype option:selected').text();
			}
			//显示校验策略
			$('.verifymodeshow').html(verifymodeshowStr);
			//确认配置界面隐藏快照模式 提前创建快照 深度有效数据提取
			$('.snapshotmodeshow').hide();
			$('.presnapshotshow').hide();
			//隐藏深度有效数据提取
			$('.parsefsmodeshow').hide();
			//高级配置在step2中校验了
			$('#strategydesbr').hide();
		}
		return true;
	}

	// 获取确认配置的加密传输描述
	var getEncryptDes = function () {
		let encryptDes = $('.encrypttransferlabel').html() + ": " + getSwitchDes(data.transport_strategy.encrypt) + "<br>";
		// 传输加密算法
		if ($('#encrypttransfer').get(0).checked) {
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
			}
			encryptDes += encryptedMethodLabel + ": " + grade + "<br>";
		}
		return encryptDes;
	}

	//设置默认时间策略
	var setStrategyTime =  function(){
		//默认设置
		$('.verifyDiv').hide();
		$(".storeDiv").show();
		$('.diff').show();
		$('.all-strategy-tips').show();
		$('.no-pIncr-tips').hide();
		$('.no-diff-tips').hide();
		$('.no-full-pIncr-tips').hide();
		$('.icsDiv').hide();
		$('.cbrDiv').hide();
		$('.fullincrDiv').hide();
		$(".deduplicationdiv").show(); 
		$("#backupSnapshot .parsefsDiv").show();
		$("#backupSnapshot .SnapshotDiv").show();
		$("#backupSnapshot .preSnapshotDiv").show();
		if(1 == CONF.SOFTWARE || 4 == CONF.SOFTWARE){
			$('.parsefsDiv').hide();
		}
		switch(data.advanced_strategy.node.storage_type){
			case "9":
				//备份到云存储不支持永久增量
				$(".pIncr").hide();
				//修改时间策略后的提示信息
				$('.no-pIncr-tips').show();
				$('.all-strategy-tips').hide();
				$('.icsDiv').hide();
				$('.cbrDiv').show();
				//不支持重删
				$(".deduplicationdiv").hide();
				break;
			case "10":
				//备份到磁带不支持永久增量
				$(".pIncr").hide();
				//修改时间策略后的提示信息
				$('.no-pIncr-tips').show();
				$('.all-strategy-tips').hide();
				break;
			case "12":
				//备份到华为CBR无永久增量
				$(".pIncr").hide();
				//修改时间策略后的提示信息
				$('.no-pIncr-tips').show();
				$('.all-strategy-tips').hide();
				$('.icsDiv').hide();
				$('.cbrDiv').show();
				//备份到华为CBR不支持整个存储策略
				$(".storeDiv").hide();
				//备份到CBR高级策略只支持传输线程数配置
				$("#backupSnapshot .SnapshotDiv").hide();
				$("#backupSnapshot .parsefsDiv").hide();
				$("#backupSnapshot .preSnapshotDiv").hide();
				$("#backupSnapshot .storageSnapshotDiv").hide();
				//备份到华为CBR支持校验策略
				$('.verifyDiv').show();
				break;
		}

		//如果虚拟化不支持差异备份 还要做特殊处理
		if(data.hypervisor_type ==  CONF.VM_TYPE.INSPURVVDK
			|| data.hypervisor_type ==  CONF.VM_TYPE.LENOVOAIO
			|| data.hypervisor_type ==  CONF.VM_TYPE.VOLC
			|| data.hypervisor_type ==  CONF.VM_TYPE.KSPHERE
		){ //全备份 增量 永久增量
			$('.diff').hide();
			$('#backupTimestrategy').find('.strategy-panel[data-mode=3]').hide();	//隐藏差备时间策略
			$('.icsDiv').show();
			$('.cbrDiv').hide();
			$('#diffBackup').iCheck('uncheck');
			$('.no-diff-tips').show();
			$('.all-strategy-tips').hide();
			if(data.advanced_strategy.node.storage_type == 9 || data.advanced_strategy.node.storage_type == 12 || data.advanced_strategy.node.storage_type == 10){
				//如果是云存储或者CBR 需要去掉去掉永久增量 只有全备份和增量
				// $("#strategymode > label:last").hide();
				$('.no-full-pIncr-tips').show();
				$('.no-diff-tips').hide();
				$('.no-pIncr-tips').hide();
				$(".pIncr").hide();
				$('.icsDiv').hide();
				$('.fullincrDiv').show();
			}
		}
	}
	//设置传输策略
	//如果是华为CBR 设置传输模式为网络传输并禁用 （如果之前没有传输模式 则显示传输模式并设置传输模式为网络传输 其他的禁用 如果之前有传输模式 把其他的禁用 只让选网络传输）
	var setStrategyTransfer =  function(){
		//如果是华为CBR存储
		if(data.advanced_strategy.node.storage_type ==  12){
			//允许修改数据块大小
			$('.blocksizediv').show();	
			switch(data.hypervisor_type){
				case CONF.VM_TYPE.VMWARE: //vmware展示了传输模式 ----
					$("#vmwaretransport_mode option").attr("disabled","disabled");
					$("#vmwaretransport_mode option[value = 'nbd']").attr("disabled",false);
					break;
				case CONF.VM_TYPE.CLOUDVIEW:
				case CONF.VM_TYPE.CLOUDVIEWSVM: //展示的是默认传输模式
					$("#transport_mode option").attr("disabled","disabled");
					$("#transport_mode option[value = 'nbd']").attr("disabled",false);
					break;
				case CONF.VM_TYPE.HYPERV:  //使用的是类xen传输模式
					//$("#leixentransmode option[value = '1']").attr("disabled","disabled");	
					$("#leixentransmode option").attr("disabled","disabled");
					$("#leixentransmode option[value = '1']").attr("disabled",false);
					break;
				case CONF.VM_TYPE.CITRIX:
				case CONF.VM_TYPE.XCPNG:    //XenServer传输模式
					$("#xentransmode option").attr("disabled","disabled");
					$("#xentransmode option[value = '1']").attr("disabled",false);
					break;
				case CONF.VM_TYPE.FUSIONXEN: //华为传输模式类型
					$("#huaweitransport_mode option").attr("disabled","disabled");
					$("#huaweitransport_mode option[value = 'nbd']").attr("disabled",false);  
					break;

				case CONF.VM_TYPE.INCLOUD://leixentransmode
				case CONF.VM_TYPE.VGATE:
				case CONF.VM_TYPE.WINSERVER:
				case CONF.VM_TYPE.WINDIY:
				case CONF.VM_TYPE.DSERVER:
				case CONF.VM_TYPE.NEOKYLIN:
				case CONF.VM_TYPE.OSEASYVSERVER:
				case CONF.VM_TYPE.EASTEDVSERVER:
				case CONF.VM_TYPE.ZSTACK:
				case CONF.VM_TYPE.ZSTACKZSPHERE:
				case CONF.VM_TYPE.NEXAVM:
				case CONF.VM_TYPE.NEXAVMNCSSV:
				case CONF.VM_TYPE.XSKY:
				case CONF.VM_TYPE.WINHONGKVM:
				case CONF.VM_TYPE.PROXMOX:
				case CONF.VM_TYPE.SMARTX:
				case CONF.VM_TYPE.ARCFRA:
				case CONF.VM_TYPE.CLOUDVIEWKVM:
				case CONF.VM_TYPE.LENOVOAIO:
					$("#leixentransmode option").attr("disabled","disabled");
					$("#leixentransmode option[value = '1']").attr("disabled",false);
					break;
				case CONF.VM_TYPE.OLVM://redhattransmode
				case CONF.VM_TYPE.RHV:
				case CONF.VM_TYPE.OVIRT:
				case CONF.VM_TYPE.ZVIRT:
				case CONF.VM_TYPE.REDVIRT:
				case CONF.VM_TYPE.ROSAVIRT:
					$("#redhattransmode option").attr("disabled","disabled");
					$("#redhattransmode option[value = '1']").attr("disabled",false);
					break;
				case CONF.VM_TYPE.FUSIONKVM: //#huaweikvmtransport_mode
				case CONF.VM_TYPE.XFUSIONKVM:
					$("#huaweikvmtransport_mode option").attr("disabled","disabled");
					$("#huaweikvmtransport_mode option[value = '1']").attr("disabled",false);
					break;
				case CONF.VM_TYPE.FLEXCLOUD:  //openstacktransmode
				case CONF.VM_TYPE.OPENSTACK:
				case CONF.VM_TYPE.FLEXHCS:
				case CONF.VM_TYPE.INCLOUDOPENSTACK:
				case CONF.VM_TYPE.SUGONCLOUDVIEW: //34
				case CONF.VM_TYPE.INSPURCLOUDPLATFORM: //35
				case CONF.VM_TYPE.EASYSTACK: //36
				case CONF.VM_TYPE.FIBERHOMEOPENSTACK: //37
				case CONF.VM_TYPE.CTSIOPENSTACK: //38
				case CONF.VM_TYPE.AWCLOUD: //39
				case CONF.VM_TYPE.HUAWEICLOUDSTACK: //54
					$("#openstacktransmode option").attr("disabled","disabled");
					$("#openstacktransmode option[value = '1']").attr("disabled",false);
					break;
				case CONF.VM_TYPE.H3C: //之前传输模式隐藏，设置为只有网络传输显示
				case CONF.VM_TYPE.H3CCASCVD:
					$('.leixentransdiv').show();
					$("#leixentransmode option[value = '2']").hide();
					break;
				case CONF.VM_TYPE.INCLOUDKVM: //leixentransdiv 传输模式显示
					$("#leixentransmode option").attr("disabled","disabled");
					$("#leixentransmode option[value = '1']").attr("disabled",false);
					break;
				case CONF.VM_TYPE.KVM://leixentransmode
				case CONF.VM_TYPE.SDCOS:
				case CONF.VM_TYPE.SANGFOR: 
					$('.leixentransdiv').show(); //之前传输模式隐藏
					$("#leixentransmode option[value = '2']").hide();
					break;
				case CONF.VM_TYPE.INSPURVVDK: //icsvvdktransport_mode
				case CONF.VM_TYPE.VOLC:
				case CONF.VM_TYPE.KSPHERE:
					$("#icsvvdktransport_mode option").attr("disabled","disabled");
					$("#icsvvdktransport_mode option[value = '1']").attr("disabled",false);
					break;
			}
		}else{
			//恢复默认
			if (_backupConfig.initConfig.transport_mode) {
				$(`#${_backupConfig.initConfig.transport_mode} option`).attr('disabled', false);
			}
			switch(data.hypervisor_type){
				case CONF.VM_TYPE.H3C:
				case CONF.VM_TYPE.H3CCASCVD:
				case CONF.VM_TYPE.KVM://leixentransmode
				case CONF.VM_TYPE.SDCOS:
				case CONF.VM_TYPE.SANGFOR:
					$(".leixentransdiv").hide();
					break;
			}
		}
	}
	//设置高级策略
	var setHighStrategy =  function(){
		$('.blocksizeshow').hide();
		if(data.advanced_strategy.node.storage_type ==  12){
			$('.snapshotdiv').hide();	
			$('.backupmodeshow2').hide();
			$('.cbtdiv').hide();								//隐藏cbt
			$('.cbtmodeshow').hide();                            //隐藏cbt信息
		}else{
			//应该还原
			$('.snapshotdiv').show();	
			$('.backupmodeshow2').show();
			// 云存储、磁带隐藏合并冗余数据比例
			if (CONF.BD_STORAGE_TYPE.CLOUD === backupTargetInfo.storage_type || CONF.BD_STORAGE_TYPE.TAPE === backupTargetInfo.storage_type) {
				$(`.mergemodediv`).hide();
				$(`.mergemodeshow`).hide();
			} else {
				// 显示高级配置 - 存储
				$(`#li_high_storage`).show();
				$(`.mergemodediv`).show();
				$(`.mergemodeshow`).show();
			}
			switch(data.hypervisor_type){
				case CONF.VM_TYPE.HYPERV:
				case CONF.VM_TYPE.H3C:
				case CONF.VM_TYPE.H3CCASCVD:
				case CONF.VM_TYPE.KVM:
				case CONF.VM_TYPE.SDCOS:
				case CONF.VM_TYPE.SANGFOR:
				case CONF.VM_TYPE.SANGFORVVDK:
				case CONF.VM_TYPE.EASYSTACK: //36
				case CONF.VM_TYPE.HUAWEICLOUDSTACK: //54
				case CONF.VM_TYPE.NEOKYLIN:
				case CONF.VM_TYPE.OSEASYVSERVER:
				case CONF.VM_TYPE.EASTEDVSERVER:
				case CONF.VM_TYPE.ZSTACK:
				case CONF.VM_TYPE.ZSTACKZSPHERE:
				case CONF.VM_TYPE.NEXAVM:
				case CONF.VM_TYPE.NEXAVMNCSSV:
				case CONF.VM_TYPE.XSKY:
				case CONF.VM_TYPE.XHERE:
				case CONF.VM_TYPE.WINHONGKVM:
				case CONF.VM_TYPE.PROXMOX:
				case CONF.VM_TYPE.RHV:
				case CONF.VM_TYPE.OVIRT:
				case CONF.VM_TYPE.ZVIRT:
				case CONF.VM_TYPE.OLVM:
				case CONF.VM_TYPE.CLOUDVIEWKVM:
				case CONF.VM_TYPE.REDVIRT:
				case CONF.VM_TYPE.ROSAVIRT:
				case CONF.VM_TYPE.LENOVOAIO:
					$('.snapshotdiv').hide();							//隐藏静默快照
					$('.backupmodeshow2').hide();	
					break;
				case CONF.VM_TYPE.CITRIX:
				case CONF.VM_TYPE.XCPNG:
					//判断是否有静默快照
					var nodes = currentTree.getCheckedNodes();
					var nosnapshot = nodes[0].nosnapshot;
					if(nosnapshot){
						$('.snapshotdiv').hide();		//隐藏静默快照	
						$('.backupmodeshow2').hide();	//隐藏静默快照的描述
					}
				break;
				case CONF.VM_TYPE.VMWARE:
					$('.blocksizeshow').show();
				case CONF.VM_TYPE.CLOUDVIEW:
				case CONF.VM_TYPE.CLOUDVIEWSVM:
					$('#incmodeldiv').show();                     //显示cbt
					$('.cbtmodeshow').show();                //显示cbt信息
					break;

			}
			

		}
	}
	
	//得到开关的结果描述   开启/关闭
	var getSwitchDes = function(check){
		if(check){
			return LANG.UI_PUBLIC_ON;
		}
		return LANG.UI_PUBLIC_OFF;
	}
	
	//得到保留策略
	var getReserveStr = function(){
		data.reserved_strategy.strategyMode = parseInt($('#reserveMode').val());
		data.reserved_strategy.reserved_type = parseInt($('#reserveType').val());
		if(CONF.RESERVE_TYPE.NUM == data.reserved_strategy.reserved_type){
			data.reserved_strategy.value = $('#spinnerNumInput').val();
		}else if(CONF.RESERVE_TYPE.DAY == data.reserved_strategy.reserved_type){
			data.reserved_strategy.value = $('#spinnerDayInput').val();
		}
		if(!(data.reserved_strategy.value > 0)){
			UIToastr.showInfo(LANG.UI_STRATEGY_RESERVE, LANG.UI_BACKUP_RESERVE_TIPS);
			return false;
		}
		//得到完全备份的勾选类型
		var checkInfo = "";
		if(data.time_strategy.full_info){
			checkInfo = data.time_strategy.full_info.type;
		}else{
			checkInfo = false;
		}
		//如果GFS做修改了给个提示
		var newGFSDes = $(".GFSstrategydes").text();
		data.reserved_strategy.gfs_modify_flag = false;
		//如果开启GFS保留策略
		if($('#GFSflag').get(0).checked){
			var gfsData = $("#GFSDiv").getGFSData(checkInfo);
			//如果返回是false  则退出
			if(!gfsData){
				return false;
			}
			data.reserved_strategy.gfs_reserved_flag = true;
			data.reserved_strategy.gfs_reserved_strategy = groupGfsStrategy(gfsData);
		}else{
			data.reserved_strategy.gfs_modify_flag = true;
			data.reserved_strategy.gfs_reserved_flag = false;
			data.reserved_strategy.gfs_reserved_strategy = [];
		}
		
        return true;
		
	}

	//组合GFS保留策略
	var groupGfsStrategy = function (data) {
		let gfsStrategy = [];
		$.each(data, function (i, v) {
			let item = {
				gfs_reserved_type: v.level1_type,
				gfs_reserved_start: v.level2_type,
				gfs_reserved_value: v.retention_num
			}
			gfsStrategy.push(item);
		})
		return gfsStrategy;
	}

	//得到归档策略
	var getAchiveStr = function(){return true;}
	//得到传输策略
	var getTransferStr = function(){
		data.transport_strategy.encrypt =  $('#encrypttransfer').get(0).checked;
		// 传输加密算法
		data.transport_strategy.encrypt_method = parseInt($('#transferEncryptMethod').val());
		data.transport_strategy.mode = $('#transport_mode').val();
		data.appliancecheck = $('#appliancecheck').get(0).checked;
		if (data.appliancecheck) {
			let applianceNode = $('#transferAgentTree').transferAgent('getSelect');
			if (false === applianceNode) {
				return false;
			}
			if (applianceNode.eventtype === 'agent_pool') {
				data.transport_strategy.agent_pool_uuid = applianceNode.agent_pool_uuid;
			} else if (applianceNode.eventtype === 'agent') {
				data.transport_strategy.agent_uuid = applianceNode.agent_uuid;
			}
		}
		data.transport_ip_segment = $('#ipSegment').val();
		//正则检查数据传输网段内容,如果不为空则进行正则检查
		if(!(data.transport_ip_segment =='' || data.transport_ip_segment == null || data.transport_ip_segment ==undefined)){
			var ipv4CheckRul = new RegExp(/^((2(5[0-5]|[0-4]\d))|[0-1]?\d{1,2})(\.((2(5[0-5]|[0-4]\d))|[0-1]?\d{1,2})){3}\/\d{1,2}$/);
			var ipv6CheckRul = new RegExp(/(([a-f0-9]{1,4}:){1,6}:|:(:[a-f0-9]{1,4}){1,6}|::)\/\d{1,2}$/);
			if(!ipv4CheckRul.test(data.transport_ip_segment) && !ipv6CheckRul.test(data.transport_ip_segment)){
				UIToastr.showInfo(LANG.UI_STRATEGY_TRANSFER,LANG.UI_BACKUP_TRANSPORT_IP_TIPS);
				return false;
			}
		}
		//其余虚拟化备份系统IP设为空
		data.backup_server_ip = '';
		//华为kvm检测备份系统IP是否为空
		if (CONF.VM_TYPE.FUSIONKVM == data.hypervisor_type  || CONF.VM_TYPE.XFUSIONKVM == data.hypervisor_type) {
			backupServerIpInfo = getBackupServerIp();
			if (!backupServerIpInfo) {
				UIToastr.showWarning(LANG.UI_STRATEGY_TRANSFER,LANG.UI_BACKUP_SERVER_IP_INVALID_TIPS);
				return false;
			}
			if (!backupServerIpInfo.data) {
				UIToastr.showInfo(LANG.UI_STRATEGY_TRANSFER,LANG.UI_BACKUP_SERVER_IP_EMPTY_TIPS);
				return false;
			}
			data.backup_server_ip = JSON.stringify(backupServerIpInfo.data);
		}
		data.transport_strategy.transfer_compress = $('#transferCompress').find('option:selected').val();
		data.transport_strategy.async_transfer = $('#asyncTransfer').get(0).checked;
		// 并行传输
		data.transport_strategy.parallel_transfer_vm_count = parseInt($('#ptVmCount').val());
		data.transport_strategy.single_vm_parallel_disk_transfer_count = parseInt($('#ptDiskCount').val());
		data.transport_strategy.vm_single_disk_parallel_transfer_count = parseInt($('#ptSegmentCount').val());
		if (!data.transport_strategy.parallel_transfer_vm_count) {
			UIToastr.showWarning(LANG.UI_STRATEGY_TRANSFER, LANG.UI_VM_BACKUP_PARALLEL_TRANSFER_VM_COUNT_ERROR_TIPS);
			return false;
		}
		if (!data.transport_strategy.single_vm_parallel_disk_transfer_count) {
			UIToastr.showWarning(LANG.UI_STRATEGY_TRANSFER, LANG.UI_VM_BACKUP_PARALLEL_TRANSFER_DISK_COUNT_ERROR_TIPS);
			return false;
		}
		let thread = data.transport_strategy.vm_single_disk_parallel_transfer_count;
		if (!thread || thread > 8 || thread <= 0) {
			//重置为默认值
			$('.ptSegmentDiv').spinner("value", 3);
			UIToastr.showWarning(LANG.UI_STRATEGY_TRANSFER, LANG.UI_VM_BACKUP_PARALLEL_TRANSFER_SEGMENT_COUNT_ERROR_TIPS);
			return false;
		}
		return true;
	}

	// 获取备份系统ip和描述信息
	const getBackupServerIp = function () {
		let backup_server_ip = [];
		let des = '';
		let emptyFlag = false; // 判断不能为空值
		let validFlag = true;
		$.each($('.systemIpdivItem'), function (i, v) {
			let node_uuid = $(v).find(`input[name=node_uuid]`).val();
			let selectIPFlag = $(v).find(`input[name=input_type]`).val() === 'select';
			let transfer_ip = '';
			if (selectIPFlag) {
				transfer_ip = $(v).find(`select[name=systemIp]`).val();
			} else {
				transfer_ip = $.trim($(v).find(`input[name=inputIp]`).val());
				if (transfer_ip && !ipV4V6(transfer_ip)) {
					validFlag = false;
					return;
				}
			}
			backup_server_ip.push({node_uuid: node_uuid, transfer_ip: transfer_ip});
			des += "<br>" + $(v).find(`.serveriplabel`).html() + ": " + transfer_ip;
			if ('' === transfer_ip) {
				emptyFlag = true;
			}
		});
		if (!validFlag) {
			return false;
		}
		if (emptyFlag) {
			backup_server_ip = ''; // 设为空字符串阻止检测通过
		}
		return {data: backup_server_ip, des: des};
	}

	//得到存储策略
	var getStoreStr = function(){
        data.storage_strategy.compress_method = 0;
		data.storage_strategy.deduplication_flag = $('#deduplicationCheck').get(0).checked;
		data.storage_strategy.block_size = $('#blocksize').val();
		data.storage_strategy.compress_flag = $('#compressCheck').get(0).checked;
		// 压缩等级
        if($('#compressCheck').get(0).checked){
            data.storage_strategy.compress_method = parseInt($('#compressGrade').val());
        }
		// data.storage_strategy.valid = $('#validcheck').get(0).checked;
		data.storage_strategy.encrypt_flag = $('#encryptStorageCheck').get(0).checked;
        // 存储加密
        data.storage_strategy.encrypt_method = parseInt($('#storageEncryptMethod').val());
		data.storage_strategy.auto_create_password_flag = $('#passwordAutocheck').get(0).checked;
		// 非法字符串校验
		if (!isNotLatinCode($.trim($('#repassword').val()))) {
			return false;
		}
		//得到密码
		data.storage_strategy.password = btoa(getPassword());
		var repassword = $.trim($('#repassword').val());

		// 开启数据加密
		if (data.storage_strategy.encrypt_flag) {
			// 开启自动生成密码
			if (data.storage_strategy.auto_create_password_flag) {
				data.storage_strategy.password = "";
			} else {
				var tmp = $.trim($('#password').val());
				// 未应用备份策略配置的密码
				if (!_oldPassword) {
					// 是否触发过密码输入框的change事件且密码最终值还是空,则提示 请输入数据加密的密码;没有触发过则无需校验,直接向后端提交此前接口返回的密码
					if (passwordChangeFlag && !data.storage_strategy.password) {
						UIToastr.showWarning(LANG.UI_BACKUP_DATA_ENCRYPT, LANG.UI_BACKUP_DATA_ENCRYPT_NO_PASSWORD_TIPS);
						return false;
					} 
					// 触发过密码输入框的change事件,但和确认密码不一致时
					if (passwordChangeFlag && tmp !== repassword) {
						UIToastr.showWarning(LANG.UI_BACKUP_DATA_ENCRYPT, LANG.UI_BACKUP_DATA_ENCRYPT_PASSWORD_CONFIRM_TIPS);
						return false;
					}
					if(!data.storage_strategy.password){
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
		data.storage_strategy.data_container_size = parseInt($('#data_container_size').val());
		data.storage_strategy.redundant_data_proportion = parseInt($('#redundant_data_proportion').val());

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
	
	//存储之前检查是否有修改过密码
	var getPassword = function(){
		var now_password = $.trim($('#password').val());
		// 备份策略已配置密码时
		if(_oldPassword != ''){
			// 若密码输入框未触发过change事件,现密码和原配置的密码_oldPassword必然相等,则返回解码后的密码
			if (!passwordChangeFlag) {
				return atob(_oldPassword);
			} else { // 若密码输入框触发过change事件,判断现密码和原配置的密码_oldPassword是否相等,相等则返回解码后的密码;不相等则返回现密码
				if (now_password === _oldPassword) {
					return atob(now_password);
				} else {
					return now_password;
				}
			}
		} else {
			// 未应用备份策略且未触发过密码输入框的change事件,则当前输入框的密码
			return now_password;
		}
	}
	
	//得到备份节点信息
	var getNodeStr = function(){
		backupTargetInfo = $('#backupTarget').backupTarget('getSelect');
		data.advanced_strategy.node.node_uuid = backupTargetInfo.node_uuid;
		data.advanced_strategy.node.node_pool_uuid = backupTargetInfo.node_pool_uuid;
		data.advanced_strategy.node.storage_uuid = backupTargetInfo.storage_uuid;
		data.advanced_strategy.node.storage_pool_uuid = backupTargetInfo.storage_pool_uuid;
		data.advanced_strategy.node.storage_type = backupTargetInfo.storage_type;

		//云存储不支持永久增量
		let storageType = backupTargetInfo.storage_type;
		if(storageType == 9 || storageType == 10){
			cloudTargetFlag = true;
			$('.pIncr').hide();
			$('.no-pIncr-tips').show();
			$('.all-strategy-tips').hide();
			$('#strategymode').find('input[data-mode=9]').iCheck('uncheck');
			$('#stragegyaccordion').find('.strategy-panel[data-mode=9]').hide();
			initTimeStrategyDes();
			$('#tab_common').backupStrategy(CONF.MODULE_TYPE.VM, false, {}, 'cloud');
		}else{
			cloudTargetFlag = false;
			$('.pIncr').show();
			$('.no-pIncr-tips').hide();
			$('.all-strategy-tips').show();
			$('#reserveMode').val(1).removeAttr('disabled');
		}
		if (CONF.BD_STORAGE_TYPE.TAPE == storageType) {
			// 目标存储为磁带时隐藏安全策略
			$('.safeLi').removeClass('active');
			$('.safeLi').hide();
			$('.safeDiv').hide();

			$('.commonLi a').trigger('click');
			$('.commonLi').addClass('active');
		} else {
			if (authFun.worm || authFun.integrity || CONF.FUNCTIONS.includes('virusKill')) {
				$('.safeLi').show();
				$('.safeDiv').show();
			}
		}
		if (!CONF.FUNCTIONS.includes('dedupication')) {
			//设置重删为关闭并不可用再加隐藏
			$('.deduplicationDiv').hide();
			$('.deduplicationdiv').hide();
		}
		return true;
	}

	// 得到安全策略
	var getSafeStr = function () {
		let wormConfig = $('#wormConfig').getWormProtectionSettings();
		let virusConfig = $('#virusConfig').getVirusDetectionBackup();
		if (virusConfig === false) {  // 验证病毒扫描配置内容是否符合要求
			return false;
		}
		let completeConfig = $('#completeConfig').getCompleteDetectionBackup();
		data.safe_strategy = safeData(wormConfig, virusConfig, completeConfig);
		// 备份存储为磁带时完整性校验策略flag设为false
		if (CONF.BD_STORAGE_TYPE.TAPE === backupTargetInfo.storage_type) {
			data.safe_strategy.integrity_check_flag = 0;
		}
		return true;
	}
	
	//得到备份模式
	var getHighStr = function(){
		data.advanced_strategy.mode.snapshot_flag = 2 == $('#incmodel').val();
		data.advanced_strategy.mode.inc_mode = parseInt($('#incmodel').val());
		data.advanced_strategy.mode.silent_snapshot_flag = $('#silentsnapshotcheck').get(0).checked;
		data.advanced_strategy.mode.cbt_flag = 3 == $('#incmodel').val();
		data.advanced_strategy.mode.reset_cbt_level = parseInt($('#resetcbtLevel').val());
		data.advanced_strategy.mode.parse_fs_flag = $('#parsefscheck').get(0).checked;
		data.advanced_strategy.mode.swap_flag = $('#swapcheck').get(0).checked;
		data.advanced_strategy.mode.delete_file_flag = $('#deletefilecheck').get(0).checked;
		data.advanced_strategy.mode.gap_flag = $('#gapcheck').get(0).checked;
		data.advanced_strategy.mode.snapshot_type = parseInt($('#snapshottype').val());
		data.advanced_strategy.mode.thread_num = parseInt($('#backupThreadNum').val());
		data.advanced_strategy.mode.pre_snapshot_flag = $('#preSnapshotcheck').get(0).checked;
		data.advanced_strategy.mode.storage_snapshot_flag = $('#storageSnapshotCheck').get(0).checked;
		data.advanced_strategy.mode.snapshot_del_speed = parseInt($('#snapshotDelSpeed').val());
		data.advanced_strategy.mode.highspeed_disk_cbt_flag = $('#highdiskcbtcheck').get(0).checked;
		data.advanced_strategy.mode.ignore_resource_limiting_flag = $('#ignore_resource_limit').get(0).checked;
		var thread = parseInt($('#backupThreadNum').val());
		if(!thread || thread > 8 || thread <= 0){
			//重置为默认值
			$('.backupThreadDiv').spinner("value", 3);
			UIToastr.showWarning(LANG.UI_BACKUP_HIGH_SETTING_TITLE, LANG.UI_BACKUP_THREAD_NUM_TIPS);
			return false;
		}
		if (data.advanced_strategy.mode.snapshot_del_speed < 10 || data.advanced_strategy.mode.snapshot_del_speed > 500) {
			UIToastr.showWarning(LANG.UI_BACKUP_SNAPSHOT_DELETE_SPEED, LANG.UI_BACKUP_SNAPSHOT_DELETE_SPEED_INVALID_TIPS);
			return false;
		}
		return true;
	}

	// 得到重试策略
	var getRetryStr = function () {
		data.retry_strategy = $('#retry_config').retryStrategy({} ,'value');
		return false !== data.retry_strategy;
	}

	//得到校验策略
	var getVerifyStr =  function(){
		data.verify_info.is_check = $('#verifyflag').get(0).checked;
		//如果开启校验策略
		if($('#verifyflag').get(0).checked){
			// data.verify_info.is_check =  1;
			data.verify_info.verify_type = $('#verifytype option:selected').val();
		}else{
			// data.verify_info.is_check =  2;
			data.verify_info.verify_type  = 0;
		}
		return true;
	}
	
	
	//备份任务步骤
	var wizardInit = function(){
		if (!jQuery().bootstrapWizard) {
            return;
        }
        var form = $('#submit_form');
        var error = $('.alert-danger', form);
        var success = $('.alert-success', form);
        var handleTitle = function(tab, navigation, index) {
            var total = navigation.find('li').length;//总共的步骤数
            var current = index + 1;      //当前步骤
            // set wizard title
//            $('.step-title', $('#vmbackupcontent')).text('Step ' + (index + 1) + ' of ' + total);
            // set done steps
            jQuery('li', $('#vmbackupcontent')).removeClass("done");
            var li_list = navigation.find('li');
            for (var i = 0; i < index; i++) {
                jQuery(li_list[i]).addClass("done");
            }
            
            //如果第一步 上一步按钮隐藏
            if (current == 1) {
                $('#vmbackupcontent').find('.button-previous').css('visibility', 'hidden');
                $('#vmbackupcontent').find('.button-next').addClass('next-btn-margin-left');
            } else {
                $('#vmbackupcontent').find('.button-previous').css('visibility', 'visible');
                $('#vmbackupcontent').find('.button-next').removeClass('next-btn-margin-left');
            }
            
            //如果是最后一步
            if (current >= total) {
                $('#vmbackupcontent').find('.button-next').hide();
                $('#vmbackupcontent').find('.button-submit').css('visibility', 'visible');
            } else {
                $('#vmbackupcontent').find('.button-next').show();
                $('#vmbackupcontent').find('.button-submit').css('visibility', 'hidden');
            }
            
            
            Metronic.scrollTo($('.page-title'));
        }

        // default form wizard
        $('#vmbackupcontent').bootstrapWizard({
            'nextSelector': '.button-next',
            'previousSelector': '.button-previous',
            onTabClick: function (tab, navigation, index, clickedIndex) {
                return false;
            },
            
            //下一步
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
            
            //上一步
            onPrevious: function (tab, navigation, index) {
                success.hide();
                error.hide();
				// 还原tab-pane的高度
				$(".tab-pane__row").css('height', '100%');
                handleTitle(tab, navigation, index);
            },
            
            //进度条显示
            onTabShow: function (tab, navigation, index) {
                var total = navigation.find('li').length;
                var current = index + 1;
                var $percent = (current / total) * 100;
                $('#vmbackupcontent').find('.progress-bar').css({
                    width: $percent + '%'
                });
            }
        });

        $('#vmbackupcontent').find('.button-previous').css('visibility', 'hidden');
        $('#vmbackupcontent .button-submit').click(debounce(submit, 200)).css('visibility', 'hidden');
	};
	
	//提交
	var submit = function(){
		if (submittedFlag) {
			return; // 防止重复提交
		}

		let jobName = $.trim($("#jobname").val());
		if('' == jobName){
			$('.jobnametip').html(LANG.UI_BACKUP_NAME_TIPS).show();
			return;
		}
		$('.jobnametip').hide();
		// 输入验证
		let jobName2 = jobName.replace('/', '');
		if(!customInputValidate('string',jobName2)){
			return false;
		}
		data.job_name = jobName;
		data.strategy_group_uuid = $('#strategySelect option:selected').val();
		submittedFlag = true;
		Metronic.blockUI({target: '#vmbackupcontent',animate: true,cenrerY: true,});
		pAjaxRequest(data, "/api/v1/vm/jobs/backup", "POST", function (d) {
			Metronic.unblockUI('#vmbackupcontent');
			if (operateResponseList(d)) {
				LOCATION('./content/platform/jobs/jobs.php', 'task');
			} else {
				submittedFlag = false; // 创建失败则重置
			}
		}, true);
	}


	//初始化主机和集群模式
	var setTree = function(zNodes){
		currentTreeDivId = 'vm_tree_hc';
		if(zNodes.length == 0){
			$("#nodatatips").show();
			$(".three_tree").hide();
			$(".vm_tree_div").hide();
			return;
		}else{
			$("#nodatatips").hide();
			$(".three_tree").show();
			$(".vm_tree_div").show();
		}
		var setting = {
				check: {
					enable: true,
					nocheckInherit: false,
					chkboxType: {
						"Y": "",
						"N": ""
					}
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
					onCheck: vmOnCheck,
					beforeExpand: nodeExpand
				}
			};
		var nodes = zNodes;//拿到备份节点
		zTreeHC = $.fn.zTree.init($("#vm_tree_hc"), setting, nodes);//第一种
		for(var i=0;i<nodes.length;i++){
			if(nodes[i].checked){
				var checkNode = zTreeHC.getNodesByParam("id", nodes[i].id, null);
				addVMList('vm_tree_hc',checkNode[0]);
				//初始化对应虚拟化平台管理的备份节点
				if(oldVcenter != nodes[i].vcenteruuid){
					oldVcenter = nodes[i].vcenteruuid;
					initAllocationNodes();
				}
			}
		}
		currentTree = zTreeHC;
	};
	
	//初始化其他模式
	var setOtherTree = function(zNodes){
		var setting = {
				check: {
					enable: true,
					nocheckInherit: false,
					chkboxType: {
						"Y": "",
						"N": ""
					}
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
					onCheck: vmOnCheck,
					beforeExpand: nodeExpand
				}
			};
		zTreeVT = $.fn.zTree.init($("#vm_tree_vt"), setting, zNodes);//第二种
	}
	
	//初始化其他模式
	var setThirdTree = function(zNodes){
		var setting = {
				check: {
					enable: true,
					nocheckInherit: false,
					chkboxType: {
						"Y": "",
						"N": ""
					},
				},
				data: {
					simpleData: {
						enable: true,
						idKey: "treeId",
						pIdKey: "pid",
						rootPId: 0
					},
					key: {
						title: "title",
					}
				},
				view: {
					fontCss: getFontCss,
					addDiyDom: addHoverDom,
				},
				callback: {
					beforeClick: nodeSelect,
					onCheck: vmOnCheck,
					beforeExpand: nodeExpand
				}
			};
		zTree = $.fn.zTree.init($("#vm_tree"), setting, zNodes);//第三种
	}
	
	//添加虚拟化中心鼠标指上去事件
	var addHoverDom = function(treeId, treeNode) {
		if (!speedFlag && treeNode.vcenteruuid && treeNode.id != treeNode.vcenteruuid) return;
		if (speedFlag && treeNode.vcenteruuid && treeNode.uuid != treeNode.vcenteruuid) return; //快速添加时id不适用
		if(1 != treeNode.type) return;
		var nodeID = escapeJquery(treeNode.id);
		var nodeTID = escapeJquery(treeNode.tId);
		var aObj = $("#" + nodeTID + "_a");
		if ($("#diyHref_" + nodeTID + nodeID + "_1").length>0) return;
		let langExpandAllDes = SUB_MODULE_TYPE_PRIVATE_CLOUD == sub_module_type ? LANG.UI_BACKUP_TREE_EXPAND_ALL_DES2 : LANG.UI_BACKUP_TREE_EXPAND_ALL_DES;
		let langCollapseAllDes = SUB_MODULE_TYPE_PRIVATE_CLOUD == sub_module_type ? LANG.UI_BACKUP_TREE_COLLAPSE_ALL_DES2 : LANG.UI_BACKUP_TREE_COLLAPSE_ALL_DES;
		var str =  '';
		if (CONF.VM_SUB_MODULE.PRIVATE_CLOUD == sub_module_type && CONF.PERMISSION_ARR.includes('p_cloud_platform_private_manager_sync')
			|| CONF.VM_SUB_MODULE.VM == sub_module_type && CONF.PERMISSION_ARR.includes('p_vcenter_manager_sync')) {
			str += '<a id="diyHref_' + treeNode.tId + treeNode.id + '_1" title="' + LANG.UI_VCENTER_SYNC + '" class="treehref">' + LANG.UI_VCENTER_SYNC + '</a>';
		}
		str += '<a id="diyHref_' + treeNode.tId + treeNode.id + '_2" title="' + langExpandAllDes + '" class="treehref">' + LANG.UI_BACKUP_TREE_EXPAND_ALL + '</a>' +
			'<a id="diyHref_' + treeNode.tId + treeNode.id + '_3" title="' + langCollapseAllDes + '" class="treehref">' + LANG.UI_BACKUP_TREE_COLLAPSE_ALL + '</a>';
		aObj.after(str);
		var hrefRefresh = $("#diyHref_" + nodeTID + nodeID + "_1");
		var hrefExpand = $("#diyHref_" + nodeTID + nodeID + "_2");
		var hrefCollapse = $("#diyHref_" + nodeTID + nodeID + "_3");
		
		if (hrefRefresh) hrefRefresh.bind("click", function(){
			getSyncVcenterInfo(treeId, treeNode, true, false);
		});
		if (hrefExpand) hrefExpand.bind("click", function(){
			// if(treeNode.children){
			// 	$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true, true, true);
			// }else{
				getSyncVcenterInfo(treeId, treeNode, false, true);
			// }
		});
		if (hrefCollapse) hrefCollapse.bind("click", function(event){
			$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, false, true, true, false);
		});

		//快速创建任务的特殊处理
		if (!speedFlag) return;
		aObj.bind("click", function(){
			if (nodeLoaded[nodeID]) return;
			if(treeNode.children){
				$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, false, true, true);
			}else{
				getSyncVcenterInfo(treeId, treeNode, false, false);
			}
			nodeLoaded[nodeID] = true;
		});
		//点击+号展开
		var nodeSwitch = $("#" + nodeTID + "_switch");
		nodeSwitch.bind("click", function(){
			if (nodeLoaded[nodeID]) return;
			if(treeNode.children){
				$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, false, true, true);
			}else{
				getSyncVcenterInfo(treeId, treeNode, false, false);
			}
			nodeLoaded[nodeID] = true;
		});
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
	
	
	var getFontCss = function(treeId, treeNode) {
		var css = {color:"#333", "font-weight":"normal"};
		if(!!treeNode.inbackup){
			css = {color:"green", "font-weight":"bold"};
		}
		if(!!treeNode.highlight){
			//搜索使用的样式
			css = {color:"#A60000", "font-weight":"bold"};
		}
		return css;
	}
	
	//添加虚拟机到右边列表
	var addChirdVMList = function(id, children){
		if(!children && children.length == 0) return;
		for(var i=0;i<children.length;i++){
			if(children[i].eventtype == "vm"){
				addVMList(id,children[i]);
			}else if(children[i].eventtype == ""){
				var chirdList = children[i].children;
				if(!chirdList) continue; 
				addChirdVMList(id, chirdList);
			}
		}
		return;
	}
	
	//勾选虚拟机
	var vmOnCheck = function(e, id, node){
		currentPlatformUuid = node.vcenteruuid;
		checkVMOnlineAndTips(node);
		var tree = $.fn.zTree.getZTreeObj(id);
		var allNodes = tree.getCheckedNodes(true);
		//只添加对象自身
		addVMList(id, node);

		//初始化对应虚拟化平台管理的备份节点
		if(oldVcenter != node.vcenteruuid && node.checked){
			oldVcenter = node.vcenteruuid;
			initAllocationNodes();
		}
		for(var i = 0; i < allNodes.length; i++){
			if(allNodes[i].vcenteruuid != node.vcenteruuid){     //判断是否为同一个虚拟中心
				//只需要比较两次
				tree.checkAllNodes(false);
				tree.checkNode(node, !node.checked, false, false);
				if (SUB_MODULE_TYPE_PRIVATE_CLOUD == sub_module_type) {
					UIToastr.showInfo(LANG.UI_BACKUP_DIFF_CLOUD_PLATFORM, LANG.UI_BACKUP_DIFF_CLOUD_PLATFORM_TIPS);
				} else {
					UIToastr.showInfo(LANG.UI_BACKUP_DIFF_VCENTER, LANG.UI_BACKUP_DIFF_VCENTER_TIPS);
				}

				//重置备份对象列表
				backupObjList = {};
				
				if(id=="vm_tree_hc"){
					$('#addHCList li').remove();
					$('.diskList').remove();
				}
				
				if(id=="vm_tree_vt"){
					$('#addVTList li').remove();
					$('.diskList').remove();
				}
				
				if(id=="vm_tree"){
					$('#addHVList li').remove();
					$('.diskList').remove();
				}

				addVMList(id,node);
				initApplianceSelect();
				return;
			}else{
				
			}
		}
		
	}
	
	//替换特殊字符
	var clearString = function (s){ 
	    var rs = ""; 
	    for (var i = 0; i < s.length; i++) { 
	        rs = rs+s.substr(i, 1).replace(_VMNAMEREG, '_'); 
	    } 
	    return rs;  
	}
	
	var getObjId = function (node) {
		return currentTreeDivId + node.vcenteruuid + node.id;
	}
	
	//移除已选择备份对象的操作
	var removeObjHandle = function (id, node) {
		var objId = getObjId(node);
		var liId = clearString(id + node.vcenteruuid + node.id);
		var treeObj = $.fn.zTree.getZTreeObj(id);
		$('.popover.in').remove();
		treeObj.checkNode(node, false, false);
		if (backupObjList.hasOwnProperty(objId)) {
			delete backupObjList[objId];
		}
		//右侧移除此节点
		$('#' + escapeJquery(liId)).remove();
		$('#disk' + escapeJquery(liId)).remove();
		// //关联取消勾选对象下的虚拟机
		// $('.' + objId).iCheck('uncheck');
		//重置子项目显示状态
		setDiskStatus(node);
		updateTotalVMCount();
	}

	//设置磁盘显示状态
	var setDiskStatus = function (node, status = false) {
		if ('vm' === node.eventtype) {
			node.initDisk = status;
		} else {
			$.each(node.children, function (i, v) {
				setDiskStatus(v, status);
			});
		}
	}
	
	//获取已选择列表的单条li的html
	var getListItemInfo = function (liId, node) {
		var objCheck = '';
		let listType = 'list-type-vm';
		let vmUuid = node.id;
		if ('' === node.eventtype) {
			listType = 'list-type-obj';
			vmUuid = '';
			objCheck = '<input class="icheck objCheck" data-checkbox="icheckbox_square-blue" type="checkbox" data-checkinit="true">';
		}
		var info =
		'<li class="list-group-item popovers VMTips list-group-item__vm ' + listType + '" id="' + liId + '" data-vmuuid="' + vmUuid + '" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="' + decodeURIComponent(node.path_show) + '">' +
			'<a href="#disk' + liId + '" data-toggle="collapse" aria-expanded="false" id="disHref' + liId + '" style="display:flex;" class="accordion-toggle accordion-toggle-styled collapsed">' +
				'<div class="col1">' +
					'<div class="cont vmDetail">' +
						'<div class="cont-col1" style="padding-top: 5px;">' +
							'<div class="' + node.iconSkin + '"></div>' +
						'</div>' +
						'<div class="cont-col2">' +
							'<div class="desc list-one">' + node.name + `<span class="count-vm" style="margin-left: 8px;color: #999999;font-size: 12px" data-count="0" data-total="0"></span></div>` +
						'</div>' +
					'</div>' +
				'</div>' +
				'<div class="col2  pull-right delete-list" style="position:absolute;right:0;width:30px;padding-top: 5px;">' +
					'<span class="del' + liId + '" >' +
						'<span class="col2-i">' +
							'<i class="viconfont vicon-guanbi"></i>' +
						'</span>' +
					'</span>' +
				'</div>' +
				'<div style="position:absolute;right:35px;width:30px;padding-top: 2px;">' +
					objCheck +
				'</div>' +
			'</a>' +
		'</li>' +
		'<ul id="disk' + liId + '" class="feeds collapse diskList" style="list-style:none;"></ul>';
		return info;
	}
	
	//递归获取备份对象下所有虚拟机节点
	var getVmNodeList = async function(node){
		// var list = [];
		// var children = node.children;
		// if (!children || children.length === 0) {
		// 	return list;
		// }
		// for (var i = 0; i < children.length; i++) {
		// 	if (children[i].eventtype === "vm") {
		// 		list.push(children[i]);
		// 	} else if (children[i].eventtype === "") {
		// 		list = list.concat(getVmNodeList(children[i]));
		// 	}
		// }
		// return list;

		// 改为接口获取递归出来的vm列表
		let p = {
			platform_uuid: node.platform_uuid ? node.platform_uuid.replace(/_$/, '') : node.vcenteruuid,
			platform_flag: node.platform_flag ?? false,
			hypervisor_type: node.hypervisor_type ?? node.hypervisor,
			display_mode: showType,
			refresh_flag: false,
			show_vm_flag: true,
			aws_flag: false,
			allocated_vm_flag: node.allocated_vm_flag ?? false,
			loadall_flag: true, // 递归加载全部
			parent_uuid: node.id
		}
		let res;
		Metronic.blockUI({target: '#tab1', animate: true, cenrerY: true});
		await getVmNodeListData(p).then(data => {res = data});
		return res;
	}

	const getVmNodeListData = (p) => {
		return new Promise(resolve => {
			pAjaxRequest(p, "/api/v1/vm/platforms/vm_tree", "GET", function (data) {
				resolve(data.data);
			});
		})
	}

	//勾选对象操作
	var checkObjHandle = function(el, objId) {
		var thisChecked = $(el).closest('div').find('.objCheck').prop('checked');
		$(el).closest(`li`).attr('data-realCheck', thisChecked); // data-realCheck全选或半选时为true
		$(el).closest('div').find('.objCheck').attr('data-checkinit', false);
		if (!thisChecked && '' == $(el).closest('li').next('ul').html()) {
			//虚拟机列表未展开则展开
			$(el).closest('a').click();
		}
		setTimeout(function(){
			var vmCheck = $(el).closest('li').next('ul').find('.vmCheck');
			$.each(vmCheck, function (i, v) {
				$(this).prop('checked', thisChecked);
				if (thisChecked) {
					$(this).parent('.icheckbox_square-blue').addClass('checked');
				} else {
					$(this).parent('.icheckbox_square-blue').removeClass('checked');
				}
				$(el).closest(`li`).attr('data-excludeAll', !thisChecked); // data-excludeAll全部排除时为true
				checkVmHandle(v);
			});
			let liId = $(el).closest(`li`).attr('id');
			if (thisChecked) {
				backupObjList[objId].exclude_vm_list = [];
			} else {
				backupObjList[objId].exclude_vm_list = vmNodeListCache[liId].map(item => {return {vm_uuid: item.id, vm_path: item.path}});
			}
			updateObjVMCount(vmCheck[0], thisChecked ? 3 : 4);
		}, 300);
	}

	//勾选对象下虚拟机操作
	var checkVmHandle = function(el, updateCountFlag = false) {
		var thisChecked = $(el).parents('.disk-checkbox').find('.vmCheck').prop('checked');
		var thisTextEl = $(el).closest('li').find('.vmName');
		let objId = $(el).closest('li').data('parentobjid');
		var vm_uuid = $(el).parents('.disk-checkbox').find('.vmCheck').attr('data-vmuuid');
		var vm_path = $(el).closest('li').data('content');
		var _parent = $(el).closest('ul').prev('li');
		var _parentCheck = $(el).closest('ul').prev('li').find('.objCheck');

		if (thisChecked) {
			//再次勾选：从上级对象虚拟机排除列表移除
			$.each(backupObjList[objId].exclude_vm_list, function (i, v) {
				if (v && vm_uuid === v.vm_uuid) {
					backupObjList[objId].exclude_vm_list.splice(i, 1);
					return true;
				}
			});
			thisTextEl.find(`.exclude-tips`).remove();
			$(el).closest('li').addClass('list-type-obj-vm');

			//没有排除的则上级全选
			if (0 === backupObjList[objId].exclude_vm_list.length) {
				_parentCheck.iCheck('check');
			}
			// 上级至少有一个vm选中
			_parent.attr('data-realCheck', true);
		} else {
			//取消勾选：加入上级对象虚拟机排除列表
			var excludeListExistFlag = false;
			$.each(backupObjList[objId].exclude_vm_list, function (i, v) {
				if (v && vm_uuid === v.vm_uuid) {
					excludeListExistFlag = true;
					return true;
				}
			});
			if (!excludeListExistFlag) {
				backupObjList[objId].exclude_vm_list.push({vm_uuid: vm_uuid, vm_path: vm_path});
			}
			if (!thisTextEl.find(`.exclude-tips`).length) {
				thisTextEl.append(`<span class="exclude-tips" style="color:#F1416C">(${LANG.UI_BACKUP_EXCLUDED})</span>`);
			}
			$(el).closest('li').removeClass('list-type-obj-vm');
			_parentCheck.iCheck('uncheck'); //上级取消全选
			// 上级没有vm选中，即全部被排除
			if (parseInt(_parent.find(`.count-vm`).attr('data-total')) === backupObjList[objId].exclude_vm_list.length) {
				_parent.attr('data-realCheck', false);
			}
		}
		if (updateCountFlag) {
			// 手动触发勾选、取消勾选的时候更新统计数量
			let addCount = thisChecked ? 1 : -1;
			updateObjVMCount(el, 2, addCount);
		}
		updateTotalVMCount();
	}
	
	//勾选虚拟机下磁盘的操作
	var checkDiskHandle = function(el) {
		var thisChecked = $(el).parents('.disk-checkbox').find('.diskCheck').prop('checked');
		var thisTextEl = $(el).closest('li').find('.disName');
		if (thisChecked) {
			thisTextEl.next(`div`).find(`.disNameExTips`).find(`.exclude-tips`).remove();
		} else {
			thisTextEl.next(`div`).find(`.disNameExTips`).append(`<span class="exclude-tips" style="color:#F1416C">(${LANG.UI_BACKUP_EXCLUDED})</span>`);
			UIToastr.showWarning(LANG.UI_EXCLUDE_DISK, LANG.UI_EXCLUDE_DISK_TIPS);
		}
	}

	// 更新已选vm总数，单选+粒度下勾选的，需根据vm_uuid去重
	const updateTotalVMCount = function () {
		return;
		let uuids = [];
		$(`#${currentListId}`).find(`.list-type-vm`).each(function () {
			uuids.push($(this).attr('data-vmuuid'));
		});
		$(`#${currentListId}`).find(`.list-type-obj-vm`).each(function () {
			uuids.push($(this).find('.vmCheck').attr('data-vmuuid'));
		});
		let count = [...new Set(uuids)].length; // 去重后个数
		let des = SUB_MODULE_TYPE_PRIVATE_CLOUD == sub_module_type ? LANG.UI_BACKUP_CLOUD_TOTAL_INSTANCE_COUNT_DES : LANG.UI_BACKUP_VM_TOTAL_VM_COUNT_DES;
		des = des.replace('%s', count.toString());
		$(`.count-total`).text(`(${des})`);
		if (count > 0) {
			$(`.selectvmtip`).hide();
		}
	}

	// 更新粒度下已选vm数量，type:1-传入count直接赋值，2-自增（count=1/-1）, 3-全部，4-置零
	const updateObjVMCount = function (el, type, count = 0) {
		// console.log(el, type, count)
		// let count = $(el).closest('ul').find(`.list-type-obj-vm`).length;
		let _count = $(el).closest('ul').prev('li').find('.count-vm');
		if (type === 2) {
			let oldCount = parseInt(_count.attr('data-count'));
			count = oldCount + count;
		} else if (type === 3) {
			count = parseInt(_count.attr('data-total'));
		} else if (type === 4) {
			count = 0;
		} else if (type === 1) {
			_count = $(el).find('.count-vm');
		}
		_count.attr('data-count', count);
		let total = _count.attr('data-total') ?? 0;
		let des = SUB_MODULE_TYPE_PRIVATE_CLOUD == sub_module_type ? LANG.UI_BACKUP_CLOUD_SELECTED_INSTANCE_COUNT_DES : LANG.UI_BACKUP_VM_SELECTED_VM_COUNT_DES;
		des = des.replace('%s', count.toString() + ' / ' + total.toString());
		_count.text(`(${des})`);
	}
	
	//勾选添加虚拟机显示列表
	var addVMList = function(id,node){
		var info = "";
		var vminfo = "";
		var diskinfo = "";
		var liId = clearString(id+node.vcenteruuid + node.id); //添加虚拟机每列ID
		var objId = getObjId(node);

		//取消树节点选中
		if (!node.checked) {
			if ('' === node.eventtype || 'vm' === node.eventtype && backupObjList.hasOwnProperty(objId)) {
				removeObjHandle(id, node);
				return;
			}
		}
		
		//树节点选中
		if (!backupObjList.hasOwnProperty(objId)) {
			//添加到备份对象列表
			backupObjList[objId] = {
				object_uuid: node.id,
				vcenter_uuid: node.vcenteruuid,
				type: node.type,
				exclude_vm_list: [],
			};
		}
		info += getListItemInfo(liId, node);
		node.initDisk = false;

		//先清空一次
		$('#disk' + escapeJquery(liId)).remove();
		$('#' + escapeJquery(liId)).remove();

		if(node.checked || 'vm' === node.eventtype && backupObjList.hasOwnProperty(objId)){

			//根据虚拟机树类型添加每一列到列表				
			if (id == "vm_tree_hc") {
				$('#addHCList').append(info);
			} else if (id == "vm_tree_vt") {
				$('#addVTList').append(info);
			} else if (id == "vm_tree") {
				$('#addHVList').append(info);
			}

			$('#' + escapeJquery(liId)).popover();	   //初始化tips

			//初始化对象全选框
			$('#' + escapeJquery(liId)).find('.objCheck').iCheck({
				checkboxClass : 'icheckbox_square-blue',
				radioClass : 'iradio_square-blue',
			}).iCheck('check');
			 
			//移除虚拟机显示
			$('.del'+escapeJquery(liId)).on('click', function(){
				removeObjHandle(id, node);
			});
			
			$('#disHref' + liId).on('click', async function(){
				if(!node.initDisk && 'vm' === node.eventtype){
					//对象是虚拟机则显示磁盘信息
					var p = {};
					p.vm_uuid = node.id;
					p.platform_uuid = node.vcenteruuid;
					Metronic.blockUI({target: '#'+liId,animate: true});
					pAjaxRequest(p, "/api/v1/vm/vms/disks", "GET", function (data) {

						Metronic.unblockUI('#'+liId);
						//加载前先清空上次加载
						$('#disk' + escapeJquery(liId)).empty();
						node.initDisk = true;
						node.detail = data.data;
						//初始化每台虚拟机的磁盘信息
						if(node.detail && node.detail != "" && node.detail.disk_list){
//		    					if(node.hypervisor == 14 || node.hypervisor ==15 || node.hypervisor ==22){
//		    						return;
//		    					}

							if(!node.detail['disk_list']) return;
							var diskCount = node.detail['disk_list'].length;
							var disklist = node.detail['disk_list'];

							for(var i=0;i<diskCount;i++){
								diskinfo +=
									'<li class="list-group-item popovers diskTips" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="' + disklist[i].disk_name + '" id="check_' + disklist[i].disk_uuid + '">' +
										'<div class="col-md-10 disName">' +
											disklist[i].disk_name +
											'<span class="display-none" id="disuuid'+ i + node.id + node.vcenteruuid +'">'+
												disklist[i].disk_uuid +
											'</span>' +
										'</div>'+
										'<div class="col-md-2 disk-checkbox">'+
											'<span class="disNameExTips mt2"></span>' +
											'<input class="icheck diskCheck" data-checkbox="icheckbox_square-blue" type="checkbox" id="disCheck'+ i + node.id + node.vcenteruuid + '" value="' + disklist[i].disk_name + '">' +
										'</div>' +
									'</li>';
							}
							$('#disk' + escapeJquery(liId)).append(diskinfo);
							$('.diskTips').popover();
							$('.diskCheck').iCheck({
								checkboxClass : 'icheckbox_square-blue',
								radioClass : 'iradio_square-blue',
							});
							$('.iCheck-helper').on('mouseover',function(){
								$('.diskTips').popover('hide');
							});
							$('#disk' + escapeJquery(liId) + ' .diskCheck').iCheck('check');
						}else{
							let lang = SUB_MODULE_TYPE_PRIVATE_CLOUD == sub_module_type ? LANG.UI_BACKUP_INSTANCE_NO_DISK : LANG.UI_BACKUP_NO_DISK;
							diskinfo += '<li class="list-group-item">' + lang + '</li>';
							$('#disk' + escapeJquery(liId)).append(diskinfo);
						}

						$('.diskCheck').next('.iCheck-helper').on('click', function () {
							checkDiskHandle(this);
						});
						$('.diskCheck').on('change', function () {
							checkDiskHandle(this);
						});
					});
				}
				if (!node.initDisk && '' === node.eventtype) {
					//加载前先清空上次加载
					$('#disk' + escapeJquery(liId)).empty();
					node.initDisk = true;
					$(this).find('.objCheck').iCheck('enable');
					let objChecked = $(this).find('.objCheck').prop('checked');
					let realObjCheck = 'true' === $('#' + escapeJquery(liId)).attr('data-realCheck');
					//对象是虚拟机上级则显示虚拟机列表
					let vmNodeListData = await getVmNodeList(node);
					let vmNodeList = vmNodeListCache[liId] = vmNodeListData.rows;
					let vmCount = vmNodeListData.total;
					if ([] !== vmNodeList) {
						$('#disk' + escapeJquery(liId)).hide(); //先隐藏，加载完再显示
						loadVmList(objId, liId, 0, objChecked, realObjCheck, vmCount);
						Metronic.unblockUI('#tab1');
					} else {
						let lang = SUB_MODULE_TYPE_PRIVATE_CLOUD == sub_module_type ? LANG.UI_BACKUP_NO_INSTANCE : LANG.UI_BACKUP_NO_VM;
						vminfo += '<li class="list-group-item">' + lang + '</li>';
						$('#disk' + escapeJquery(liId)).append(vminfo);
					}
				}
			});

			// 给每个diskTips加上点击事件联动checkbox的勾选
			$('#disk' + liId).on('click', 'li', function(e) {
				var icheckbox_square = $(this).find('.disk-checkbox .icheckbox_square-blue');
				var icheckbox_square_input = $(this).find('.disk-checkbox .icheckbox_square-blue input');

				// 已禁用的不能联动勾选
				if (!icheckbox_square.hasClass('disabled')) {
					icheckbox_square_input.click();
					if (icheckbox_square.hasClass('checked')) {
						// 已勾选取消勾选
						icheckbox_square.removeClass('checked');
						icheckbox_square_input.prop('checked', false);
					} else {
						// 未勾选则勾选
						icheckbox_square.addClass('checked');
						icheckbox_square_input.prop('checked', true);
					}
				}
			})
		}

		//备份对象全选、全不选
		$('.objCheck').next('.iCheck-helper').on('click', function () {
			checkObjHandle(this, objId);
		});

		// 选中上级粒度时触发展开
		if (node.type != 7) {
			// $('#' + escapeJquery(liId)).find('a').trigger('click');
		}

		updateTotalVMCount();
	}

	// 虚拟机列表分页加载
	const loadVmList = (objId, liId, offset, objChecked, realObjCheck, vmCount = 0) => {
		offset = parseInt(offset);
		if (vmCount) {
			$('#' + escapeJquery(liId)).find('.count-vm').attr('data-count', vmCount);
			$('#' + escapeJquery(liId)).find('.count-vm').attr('data-total', vmCount);
		}
		$('#disk' + escapeJquery(liId)).find(`.load-more-vm`).remove();
		let vmNodeList = vmNodeListCache[liId];
		let totalLength = vmNodeList.length;
		let dinfo = '';
		let nextOffset = offset + LIMIT;
		nextOffset = nextOffset < totalLength ? nextOffset : totalLength;
		for (let start = offset; start < nextOffset; start++) {
			dinfo +=
				'<li class="list-group-item popovers vmTips list-type-obj-vm" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-parentobjid="' + objId + '" data-content="' + decodeURIComponent(vmNodeList[start].path_show) + '" id="check_' + vmNodeList[start].vcenteruuid + vmNodeList[start].id + '">' +
				'<div class="col-md-10 vmName">' +
				vmNodeList[start].name +
				'<span class="display-none" id="vmuuid' + vmNodeList[start].vcenteruuid + vmNodeList[start].id + '">' +
				vmNodeList[start].id +
				'</span>' +
				'</div>' +
				'<div class="col-md-2 disk-checkbox">' +
				'<input class="icheck vmCheck" data-checkbox="icheckbox_square-blue" type="checkbox" id="vmCheck' + vmNodeList[start].vcenteruuid + vmNodeList[start].id + '" value="' + encodeURIComponent(vmNodeList[start].name) + '" data-vmuuid="' + vmNodeList[start].id + '">' +
				'</div>' +
				'</li>';
			if (start === nextOffset - 1 && start < totalLength - 1) {
				// 走到最后一条且没加载完则添加【加载更多】
				dinfo += `<li class="list-group-item load-more-vm" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-objid="${objId}" data-liid="${liId}" data-offset="${nextOffset}" data-objchecked="${objChecked}" data-node>${LANG.UI_VM_LOAD_MORE}...</li>`;
			}
		}

		// delay += 100;
		$('#disk' + escapeJquery(liId)).append(dinfo);

		// 加载更多
		$('.load-more-vm').on('click', function () {
			// 重新获取现有状态
			realObjCheck = 'true' === $('#' + escapeJquery(liId)).attr('data-realCheck');
			loadVmList($(this).attr('data-objid'), $(this).attr('data-liid'), $(this).attr('data-offset'), $(this).closest(`ul`).prev(`li`).find(`.objCheck`).prop('checked'), realObjCheck);
		});

		$('.vmTips').popover();
		$('.vmCheck').iCheck({
			checkboxClass: 'icheckbox_square-blue',
			radioClass: 'iradio_square-blue',
		});
		$('.iCheck-helper').on('mouseover', function () {
			$('.vmTips').popover('hide');
		});

		var vmInputs = $('#disk' + escapeJquery(liId) + ' .vmCheck');
		var checkinit = $('#disk' + escapeJquery(liId) + ' .vmCheck').closest('ul').prev('li').find('.objCheck').data('checkinit');
		//初始化先全部选中，如果是取消全选触发展开的则置零
		if (offset === 0) {
			let setType = checkinit ? 3 : 4;
			updateObjVMCount(vmInputs[0], setType);
		}

		if (selectValue.length) {
			handlerFilterVmListCheck();
		} else {
			for (let i = offset; i < nextOffset; i++) {
				if (offset === 0) {
					vmInputs.eq(i).iCheck(objChecked ? 'check' : 'uncheck');
					checkVmHandle(vmInputs[i]);
				} else {
					// 加载更多
					if (realObjCheck) {
						if (objChecked) {
							// 上级对象全选则勾选加载部分
							vmInputs.eq(i).iCheck('check');
						} else {
							// 上级对象半选时
							if ('true' === $('#' + escapeJquery(liId)).attr('data-excludeAll')) {
								// 排除全部后再勾选某个则排除加载部分
								vmInputs.eq(i).iCheck('uncheck');
							} else {
								// 直接排除某个则勾选加载部分
								vmInputs.eq(i).iCheck('check');
							}
						}
					} else {
						// 上级对象未勾选则排除加载部分
						vmInputs.eq(i).iCheck('uncheck');
					}
					checkVmHandle(vmInputs[i]);
				}
			}
		}

		$('#disk' + escapeJquery(liId)).show();
		$('.vmCheck').next('.iCheck-helper').on('click', function () {
			checkVmHandle(this, true);
		});
		$('.vmCheck').on('change', function () {
			checkVmHandle(this, true);
		});
	}
	
	//检测虚拟机断开状态,选中的时候提示
	var checkVMOnlineAndTips = function(node){
		//如果是虚拟化中心 则不做判断
		if(!node.online && node.checked && node.type != 1){
			if (SUB_MODULE_TYPE_PRIVATE_CLOUD == sub_module_type) {
				UIToastr.showWarning(LANG.UI_BACKUP_SELECT_OFFLINE_INSTANCE_TIPS, LANG.UI_BACKUP_SELECT_OFFLINE_INSTANCE_VALUE);
			} else {
				UIToastr.showWarning(LANG.UI_BACKUP_SELECT_OFFLINE_VM_TIPS, LANG.UI_BACKUP_SELECT_OFFLINE_VM_VALUE);
			}
		}
	}
	
	var nodeSelect = function(treeId, treeNode, clickFlag){
		if('vm' == treeNode.eventtype){
			$.fn.zTree.getZTreeObj(treeId).checkNode(treeNode, !treeNode.checked, true, true);
			if(treeNode.inbackup){
				if (SUB_MODULE_TYPE_PRIVATE_CLOUD == sub_module_type) {
					UIToastr.showWarning(LANG.UI_BACKUP_INSTANCE_IN_TASK, LANG.UI_BACKUP_INSTANCE_IN_TASK_TIPS);
				} else {
					UIToastr.showWarning(LANG.UI_BACKUP_VM_IN_TASK, LANG.UI_BACKUP_VM_IN_TASK_TIPS);
				}
				return;
			}
			if(treeNode.chkDisabled){
				//如果不可用,提示未授权
				UIToastr.showWarning(LANG.UI_BACKUP_SELECT_UNAUTHORIZAED_VM_TIPS, LANG.UI_BACKUP_SELECT_UNAUTHORIZAED_VM_VALUE);
				return;
			}
		} else if ('more' === treeNode.eventtype) {
			// 获取父节点加载更多
			let parentNode = currentTree.getNodeByParam('treeId', treeNode.pid);
			getSyncVcenterInfo(treeId, parentNode, false, false, false,true, treeNode.nextOffset);
		}else{
			if(!treeNode.isParent) return;//不存在子节点不展开
			nodeExpand(treeId, treeNode);
			$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
		}
	}
	
	var nodeExpand = function(treeId, treeNode){
		if(treeNode.clickshow){
			if(treeNode.children) return true;
			getSyncVcenterInfo(treeId, treeNode, false, false, false, true);
		}else{
			return true;
		}
		
	}
	
	//得到当前树
	var getCurrentTree = function(id){
		if(id=="vm_tree_hc"){
			currentTree = zTreeHC;
			}
			
		if(id=="vm_tree_vt"){
			currentTree = zTreeVT;
			}
		
		if(id=="vm_tree"){
			currentTree = zTree;
			}
	}
	//刷新清空对应虚拟化中心的虚拟机列表
	var refreshVMList = function(id,vcenteruuid){
		getCurrentTree(id);
		var allNodes = currentTree.getCheckedNodes(true);
		if(allNodes.length ==0) return;
		if(allNodes[0].vcenteruuid != vcenteruuid) return;
		if(id=="vm_tree_hc"){
			$('#addHCList li').remove();
			$('.diskList').remove();
			}
			
		if(id=="vm_tree_vt"){
			$('#addVTList li').remove();
			$('.diskList').remove();
			}
		
		if(id=="vm_tree"){
			$('#addHVList li').remove();
			$('.diskList').remove();
		}
	}
	
	//异步获取虚拟化中心/宿主机的信息  refreshFlag是否重新刷新
	var getSyncVcenterInfo = function (treeId, treeNode, refreshFlag, expendFlag, loadAllFlag = false, loadMoreFlag = false, offset = 0, keyword = '') {
		var div = ".src-wrap__content";
		showType = parseInt($('#vmshowtype').val());
		let p = {
			platform_uuid: treeNode.platform_uuid ? treeNode.platform_uuid.replace(/_$/, '') : treeNode.vcenteruuid,
			platform_flag: treeNode.platform_flag ?? false,
			hypervisor_type: (treeNode.hypervisor_type ?? treeNode.hypervisor) ?? treeNode.id,
			display_mode: showType,
			refresh_flag: refreshFlag,
			show_vm_flag: true,
			aws_flag: false,
			allocated_vm_flag: treeNode.allocated_vm_flag ?? false,
			loadall_flag: loadAllFlag, // 递归加载全部
			parent_uuid: treeNode.id,
			keyword: keyword
		};
		if (loadMoreFlag) {
			// 加载更多
			p.offset = offset;
			p.limit = LIMIT;
		}
		Metronic.blockUI({target: div, animate: true});
		pAjaxRequest(p, "/api/v1/vm/platforms/vm_tree", "GET", function (data) {
			Metronic.unblockUI(div);
			if (data.data.tenant_flag) {
				// 租户模式通过页面搜索
				var nodes = currentTree.getNodes();
				if(!nodes || nodes.length == 0) return;
				var checkNode =currentTree.getCheckedNodes();
				var checkVmNode = [];
				$.each(checkNode, function (i, v) {
					checkVmNode.push(v);
				});
				var allNode = currentTree.transformToArray(currentTree.getNodes());
				nodeParamList = currentTree.getNodesByParamFuzzy('name', keyword);
				if(nodeParamList.length!=0){
					currentTree.hideNodes(allNode);
					$('.three_tree').show();
					$('#nosearchtips').hide();
				}else{
					$('.three_tree').hide();
					$('#nosearchtips').show();
				}
				//连接搜索的和所勾选的
				nodeParamList =nodeParamList.concat(checkVmNode);
				var nodeParamList1 = currentTree.transformToArray(nodeParamList);
				for(var n in nodeParamList1){
					findParent(currentTree,nodeParamList1[n]);
				}
				currentTree.showNodes(nodeParamList);
				return;
			}

			if (!loadMoreFlag) {
				// 加载更多时不清空
				refreshVMList(treeId, treeNode.vcenteruuid);
			}
			//检测是否为同一事件返回
			if (currentTree != $.fn.zTree.getZTreeObj(treeId)) return;
			if (data.success) {
				let nodes = data.data.rows;
				//刷新同一虚拟化中心清除虚拟机列表显示
				if (refreshFlag) {
					nodeParamList = currentTree.getNodesByParamFuzzy('name', '');
					currentTree.showNodes(nodeParamList);
					$.each(nodeParamList, function (i, v) {
						if (0 == v.pid && treeNode.hypervisor_type == v.id) {
							currentTree.expandNode(v, true);
						}
					});
				}
				var allNodes = currentTree.getCheckedNodes(true);
				if (refreshFlag && allNodes.length != 0 && allNodes[0].hypervisor_type == treeNode.hypervisor_type && allNodes[0].platform_uuid == treeNode.platform_uuid) {
					$('.popover.in').remove();
					$('.addVMList').empty();
					currentTree.checkAllNodes(false);
				}
				if (loadMoreFlag) {
					// 删除已有的【加载更多】节点
					currentTree.removeNode(currentTree.getNodeByParam('id', `${treeNode.id}_loadMore`));
				} else {
					currentTree.removeChildNodes(treeNode);
				}
				currentTree.addNodes(treeNode, nodes, true);
				//如果获取到得长度不为0 则将treenode设置成可选
				if (nodes.length != 0) {
					treeNode.nocheck = false;
					currentTree.setChkDisabled(treeNode, treeNode.chkDisabled);
					$('.three_tree').show();
					$('#nosearchtips').hide();
				} else {
					if (keyword) {
						$('.three_tree').hide();
						$('#nosearchtips').show();
					}
				}

				showSearchInput(true);
				if (expendFlag) {
					currentTree.expandNode(treeNode, true, !keyword, true);
				}
				for (var i = 0; i < nodes.length; i++) {
					let checkNode = currentTree.getNodesByParam("id", nodes[i].id, null)[0];
					if (nodes[i].checked) {
						addVMList(treeId, checkNode);
					}
					if (keyword) {
						// 虚拟化类型不可勾选
						let hypervisorNode = currentTree.getNodesByParam("id", p.hypervisor_type, null)[0];
						hypervisorNode.nocheck = true;
						currentTree.updateNode(hypervisorNode);
						// 搜索时根据是否有子节点确定是否展开
						currentTree.expandNode(checkNode, checkNode.open, false, false);
					}
				}
				if (speedFlag) {
					for (var i = 0; i < nodes.length; i++) {
						if (nodes[i].checked) {
							let checkNode = currentTree.getNodesByParam("id", nodes[i].id, null);
							addVMList(treeId, checkNode[0]);
						}
					}
				}
			} else {
				operateResponseList(data);
			}
		}, true);
	}
	
	//添加没有子节点的非虚拟机和宿主机过滤
	var addNodesFilter = function(treeId, treeNode, msg){
		$.fn.zTree.getZTreeObj(treeId).removeChildNodes(treeNode);
		var nodes =  $.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, msg, true);
		var filterFlag =false;
		var list = [];
		for(var i=0;i<nodes.length;i++){
			if(!nodes[i].isParent && nodes[i].machinetype != 7 && nodes[i].machinetype != 4){
				$.fn.zTree.getZTreeObj(treeId).removeNode(nodes[i]);
				if(!filterFlag){
					filterFlag = true;
				}
			}else{
				list.push(msg[i]);
			}
		}
		if(filterFlag){
    		addNodesFilter(treeId, treeNode, list);
		}
	}

	const initHypervisorTypeList = () => {
		let cloudFlag = false;
		let cloudType = '';
		if (SUB_MODULE_TYPE_PRIVATE_CLOUD == sub_module_type) {
			cloudFlag = true;
			cloudType = 'private';
		}
		pAjaxRequest({display_mode: 1, cloud_flag: cloudFlag, cloud_type: cloudType, only_hypervisor_flag: true}, "/api/v1/vm/platforms/tree", "GET", function (data) {
			if (data.data.info.length) {
				$.each(data.data.info, function (i, v) {
					$(`#hypervisortype`).append(`<option data-icon="${v.iconSkin}" value="${v.id}">${v.name}</option>`);
				});
				$(`#hypervisortype`).selectpicker('refresh');
				hypervisorType = data.data.info[0].id;
			}
		}, false);
	}

	var initTree = function(async = true) {
		let cloudFlag = false;
		let cloudType = '';
		if (SUB_MODULE_TYPE_PRIVATE_CLOUD == sub_module_type) {
			cloudFlag = true;
			cloudType = 'private';
		}
		Metronic.blockUI({target: '.src-wrap__content', animate: true});
		pAjaxRequest({display_mode: 1, cloud_flag: cloudFlag, cloud_type: cloudType, hypervisor_type: hypervisorType}, "/api/v1/vm/platforms/tree", "GET", function (data) {
			Metronic.unblockUI('.src-wrap__content');
			setTree(data.data.info);
		}, async);
		if (CONF.VMTYPE_GROUP.VMWARE.includes(parseInt(hypervisorType))) {
			// pAjaxRequest({display_mode: 2, cloud_flag: cloudFlag, cloud_type: cloudType, hypervisor_type: hypervisorType}, "/api/v1/vm/platforms/tree", "GET", function (data) {
			// 	setOtherTree(data.data.info);
			// }, false);
			// pAjaxRequest({display_mode: 3, cloud_flag: cloudFlag, cloud_type: cloudType, hypervisor_type: hypervisorType}, "/api/v1/vm/platforms/tree", "GET", function (data) {
			// 	setThirdTree(data.data.info);
			// }, false);
		} else {
			// 清空其他两个树
			setOtherTree([]);
			setThirdTree([]);
			zTreeVT = null;
			zTree = null;
		}
	};
	
	var inintDatatimePicker = function(){
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
	
	//显示搜索框
	var showSearchInput = function(flag){
		if(!flag) return;
		if (SUB_MODULE_TYPE_PRIVATE_CLOUD == sub_module_type) {
			$('.src-wrap__content .src-wrap__content__ztree').css('height', 'calc(100% - 93px)');
		} else {
			$('.src-wrap__content .src-wrap__content__ztree').css('height', 'calc(100% - 145px)');
		}
	}
	
	//虚拟机树显示模式发生变化
	var vmShowTypeChange = function(){
		showType = parseInt($('#vmshowtype').val());
		$('.addVMList').hide();
		$('.tree_div').hide();
		$('#searchvm').val('');
//		updateNodes(false);
		let cloudFlag = false;
		let cloudType = '';
		if (SUB_MODULE_TYPE_PRIVATE_CLOUD == sub_module_type) {
			cloudFlag = true;
			cloudType = 'private';
		}
		switch(showType){
			case 1:
				$('#vm_tree_hc').css('display', "block");
				$('#addHCList').show();
				currentTree = zTreeHC;
				currentTreeDivId = 'vm_tree_hc';
				currentListId = 'addHCList';
				showSearchInput(zTreeHCLoad);
				break;
			case 2:
				if (!zTreeVT) {
					Metronic.blockUI({target: '.src-wrap__content', animate: true});
					pAjaxRequest({display_mode: 2, cloud_flag: cloudFlag, cloud_type: cloudType}, "/api/v1/vm/platforms/tree", "GET", function (data) {
						Metronic.unblockUI('.src-wrap__content');
						setOtherTree(data.data.info);
						currentTree = zTreeVT;
					}, true);
				}
				$('#vm_tree_vt').css('display', 'block');
				$('#addVTList').show();
				currentTree = zTreeVT;
				currentTreeDivId = 'vm_tree_vt';
				currentListId = 'addVTList';
				showSearchInput(zTreeVTLoad);
				break;
			case 3:
				if (!zTree) {
					Metronic.blockUI({target: '.src-wrap__content', animate: true});
					pAjaxRequest({display_mode: 3, cloud_flag: cloudFlag, cloud_type: cloudType}, "/api/v1/vm/platforms/tree", "GET", function (data) {
						Metronic.unblockUI('.src-wrap__content');
						setThirdTree(data.data.info);
						currentTree = zTree;
					}, true);
				}
				$('#vm_tree').css('display', 'block');
				$('#addHVList').show();
				currentTree = zTree;
				currentTreeDivId = 'vm_tree';
				currentListId = 'addHVList';
				showSearchInput(zTreeLoad);
				break;
		}
		$("#" + currentTreeDivId).scrollTop(0);
		// backupObjList = {};

		// 切换到未搜索过的树则初始化，否则回填关键词后执行搜索
		if (searchVmTreeId != currentTreeDivId) {
			$('.three_tree').show();
			$('#' + currentTreeDivId).show();
			$('#nodatatips').hide();
			$('#nosearchtips').hide();
		} else {
			$('#searchvm').val(searchVmTreeWord);
			if (searchVmTreeWord) {
				searchVM();
			}
		}

		updateTotalVMCount();
	}
	
	//搜索虚拟机
	var searchVM = function(){
		var value = $('#searchvm').val();
		// 输入验证
		if(!customInputValidate('string',value)){
			return false;
		}
		searchVmTreeId = currentTreeDivId;
		searchVmTreeWord = value;
		// 通过接口搜索
		let treeNode = currentTree.getNodeByParam('id', hypervisorType); // 虚拟化类型的节点
		if (searchVmTreeWord) {
			getSyncVcenterInfo(currentTreeDivId, treeNode, false, true, false, false, 0, searchVmTreeWord);
		} else {
			initTree();
			currentTree.expandAll(false);
			$('#nosearchtips').hide();
		}

		// 通过页面搜索
		// var nodes = currentTree.getNodes();
		// if(!nodes || nodes.length == 0) return;
		// var checkNode =currentTree.getCheckedNodes();
		// var checkVmNode = [];
		// $.each(checkNode, function (i, v) {
		// 	checkVmNode.push(v);
		// });
		// var allNode = currentTree.transformToArray(currentTree.getNodes());
		// nodeParamList = currentTree.getNodesByParamFuzzy('name', value);
		// if(nodeParamList.length!=0){
		// 	currentTree.hideNodes(allNode);
		// 	$('.three_tree').show();
		// 	$('#nosearchtips').hide();
		// }else{
		// 	$('.three_tree').hide();
		// 	$('#nosearchtips').show();
		// }
		// //连接搜索的和所勾选的
		// nodeParamList =nodeParamList.concat(checkVmNode);
		// var nodeParamList1 = currentTree.transformToArray(nodeParamList);
        // for(var n in nodeParamList1){
        //     findParent(currentTree,nodeParamList1[n]);
        // }
        // currentTree.showNodes(nodeParamList);
    }
	
	 //找到父节点
	 var findParent = function(treeObj,node){
		 currentTree.expandNode(node,true,false,false);
		 if(!node.children){
			 nodeParamList.push(node);
			 currentTree.expandNode(node,false,false,false);
		 }
		 var pNode = node.getParentNode();
		 if(pNode != null){
			 nodeParamList.push(pNode);
			 findParent(currentTree, pNode);
		 }
	}
	
	
	//初始化树形展示方式和事件
	var initSelectShowType = function(){
		if (sub_module_type == 1) {
			//初始化BS select控件
			$('#vmshowtype').selectpicker({
				iconBase: 'fa',
				tickIcon: 'fa-check'
			});
			//绑定事件
			$('#vmshowtype').on('change', vmShowTypeChange);
		}

		// 设置树的inline-block以便在小分辨率屏幕初始化时即刻展示横向滚动条
		$('#vm_tree_hc').css('display', 'block');
	}

	// 初始化搜索框
	const initSearch = () => {
		// $('#searchvm').on('propertychange', searchVM).on('input', searchVM);
		// 回车搜索
		$('#searchvm').on('keydown', (event) => {
			if (event.key === 'Enter' || event.keyCode === 13) {
				searchVM();
			}
		});
		// 搜索按钮
		$('#searchvm_btn').off().on('click', () => {
			searchVM();
		});
		// 显示清除按钮
		$('#searchvm').on('input', () => {
			if ($('#searchvm').val()) {
				$('#searchvm_clear_search').removeClass('hide');
			} else {
				$('#searchvm_clear_search').addClass('hide');
			}
		});
		// 清空搜索
		$('#searchvm_clear_search').on('click', () => {
			$('#searchvm').val('');
			searchVmTreeWord = '';
			$('#searchvm_clear_search').addClass('hide');
			initTree();
			currentTree.expandAll(false);
			// searchVM();
			$('#nosearchtips').hide();
		});

		// 设置树的inline-block以便在小分辨率屏幕初始化时即刻展示横向滚动条
		$('#vm_tree_hc').css('display', 'block');
	}
	
	var initMyTree = function(){
		var s_vmuuid = $('#s_vmuuid').val();
		var s_vcenteruuid = $('#s_vcenteruuid').val();
		var s_showtype = $('#s_showtype').val();
		var s_hypervisor = $('#s_hypervisor').val();
		if(s_vmuuid && s_vcenteruuid && s_showtype && s_hypervisor){
			//快速创建备份任务
			speedInitTree(s_showtype, s_vmuuid, s_vcenteruuid, s_hypervisor);
			$('#vmshowtype').val(1).prop("disabled", true);
		}else{
			//常规创建备份任务
			initHypervisorTypeList();
			initTree(false);
		}
		Metronic.unblockUI('.src-wrap__content');
	}
	
	//快速创建备份任务
	var speedInitTree = function(showtype, vmuuid, vcenteruuid, hypervisor){
		speedFlag = true;
		let p ={};
		p.display_mode = showtype;
		p.vm_uuid = vmuuid;
		p.platform_uuid = vcenteruuid;
		p.hypervisor_type = hypervisor;
		pAjaxRequest(p, "/api/v1/vm/jobs/speed_backup/tree", "GET", function (d) {
			setTree(d.data.rows);
			if (SUB_MODULE_TYPE_PRIVATE_CLOUD == sub_module_type) {
				$('.src-wrap__content .src-wrap__content__ztree').css('height', 'calc(100% - 58px)');
			} else {
				$('.src-wrap__content .src-wrap__content__ztree').css('height', 'calc(100% - 108px)');
			}
		}, false);
		nodeLoaded[vcenteruuid + '_'] = true;
	}

	//初始化appliance下拉框
	var initApplianceSelect = function(){
		if(applianceFlag) return; //只加载一次
		$('#transferAgentTree').transferAgent();
		applianceFlag = true;
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
					globalStrategy[data[i].uuid] = data[i].strategy;
					strategyselect.append(option);
				}
				if(!initStrategyFlag){
					//初始化前先清除一遍
					$('.searchable-select').remove();
					$('#strategySelect').searchableSelect();
					$('.searchable-select-item').on('click', strategyHandler);
					initStrategyFlag = true;
				}
			}

		}

        pAjaxRequest({type: SUB_MODULE_TYPE_PRIVATE_CLOUD == sub_module_type ? 22 : CONF.MODULE_TYPE.VM}, '/api/v1/strategies/select', "GET", initStrategyList, true);
	}

	var initDataChangeListeners = function(){
		initVmFilterListeners();
        initHighListeners();
		initVerifyListeners(); //初始化校验策略模块的监听
    }
    
    var initVmFilterListeners = function () {
		//虚拟机过滤确定
		$('#vm_filter_submit').on('click', vmFilterSubmit);
	}

    var initHighListeners = function(){
        $('#snapshottype').on('change', function(){
        	if(this.value == 1){
				$('.preSnapshotDiv').show();
        	}else{
        		$('.preSnapshotDiv').hide();
				$('#preSnapshotcheck').bootstrapSwitch('state', false);
        	}
        });
    }
	//初始化校验策略模块的监听
	var initVerifyListeners =  function(){
		$('#verifyflag').on('switchChange.bootstrapSwitch',function(){
			initVerifyStrategyDes();
		})
		$('#verifytype').on("change",function(){
			initVerifyStrategyDes();
		})
	}
	//加载策略对应描述
	var initStrategyDes = function(){
		initVerifyStrategyDes();//初始化校验策略描述信息
	}

	//修改颜色
	var initStrategyDesStyle = function(div, des, oldDes){
		if(oldDes != des){
			div.addClass('font-green-seagreen');
		}else{
			div.removeClass('font-green-seagreen');
		}
	}

	var initTimeStrategyDes = function(){
		var des = "";
		var diffDes = "";
		//备份
		var backupType = $('#backuptype').val();
		var strategyConfig = $('#backupTimestrategy').getStrategyConfig();
		if(!strategyConfig.fullInfo) return;
		var strategyMode = $('#strategymode').find('input:checked');
		if('strategy' == backupType){
			for(var i=0; i<strategyMode.length; i++){
				if(1 == $(strategyMode[i]).data('mode')){
					des += strategyConfig.fullInfo.des + ". ";
					diffDes += strategyConfig.fullInfo.des + "<br>";
				}else if(2 == $(strategyMode[i]).data('mode')){
					des += strategyConfig.incrInfo.des + ". ";
					diffDes += strategyConfig.incrInfo.des + "<br>";
				}else if(3 == $(strategyMode[i]).data('mode')){
					des += strategyConfig.diffInfo.des + ". ";
					diffDes += strategyConfig.diffInfo.des + "<br>";
				}else if(9 == $(strategyMode[i]).data('mode')){
					des += strategyConfig.pIncrInfo.des + ". ";
					diffDes += strategyConfig.pIncrInfo.des + "<br>";
				}
			}
		}else if('oncetime' == backupType){
			des += LANG.UI_GLOBAL_STRATEGY_ONCE_TIME_START + ": " + $('#oncetime').val();
			diffDes += LANG.UI_GLOBAL_STRATEGY_ONCE_TIME_START + ": " + $('#oncetime').val();
		}
		var strategyIndex = $('#strategySelect').val();
		if(strategyIndex && strategyIndex != "" && editFlag){
			var oldDes = globalStrategy[strategyIndex].time.des;
			initStrategyDesStyle($('.backupTimeDes'), diffDes, oldDes);
		}else{
			$('.backupTimeDes').removeClass('font-green-seagreen');
		}
		$('.backupTimeDes').html(des);
		$('.backupTimeDes').prop('title', des);
	}

    //初始化校验策略描述信息
	var initVerifyStrategyDes = function(){
		var des = "";
		var verifystr = $('.verifyLable').html();
		des +=verifystr+": "+getSwitchDes($('#verifyflag').get(0).checked);
		if($('#verifyflag').get(0).checked){
			des += ","+ $('.verifyTypeLable').html()+": "+$('#verifytype option:selected').text();
		}
		$('.backupVerifyDes').html(des);
		$('.backupVerifyDes').prop('title', des);
	}
    //初始化备份系统节点IP
	var initBackupServerAddr = function(){
		var data = {};
		data.node_uuid_list = backupTargetInfo.node_uuid_list;
		pAjaxRequest(data, "/api/v1/nodes/all_ip", "GET", function (d) {
			let data = d.data;
			$(`.systemIpdivContainer`).empty();
			$.each(data, function (node_uuid, node_info) {
				let ipListOption = ``;
				$.each(node_info.ip_list, function (i, v) {
					ipListOption += `<option value="${v}">${v}</option>`;
				});
				let html = `
					<div class="form-group systemIpdivItem">
						<input type="hidden" name="node_uuid" value="${node_uuid}">
						<!-- 保存输入类型：select/input -->
						<input type="hidden" name="input_type" value="select">
						<label class="control-label col-md-4 serveriplabel form-group-label">${LANG.UI_BACKUP_NODE_TRANSFER_IP}(${node_info.node_ip})</label>
						<div class="col-md-8">
							<div class="selectipdiv">
								<select class="form-control" name="systemIp" data-node_uuid="${node_uuid}">${ipListOption}</select>
								<div>
									<span class="help-block ">
										${LANG.UI_BACKUP_SELECT_BAKNODE_IP}
										<a class="diysystemip">${LANG.UI_BACKUP_DIY_INPUT}</a>
									</span>
								</div>
							</div>
							<div class="input-icon right mb5 display-none inputipdiv">
								<i class="fa"></i>
								<input type="text" maxlength="128" class="form-control" name="inputIp" data-node_uuid="${node_uuid}" onkeyup="customInputValidate('ip', this.value, $(this))"/>
								<div>
									<span class="help-block ">
										${LANG.UI_BACKUP_INPUT_BAKNODE_IP}
										<a class="selectsystemip">${LANG.UI_VM_SETTING_V2_AUTO_SELECT}</a>
									</span>
								</div>
							</div>
						</div>
					</div>
				`;
				$(`.systemIpdivContainer`).append(html);
			});
		}, false);
		$('.inputIp').val(window.location.host);

		//自动选择IP
		$('.diysystemip').on('click', function(){
			$(this).closest('.systemIpdivItem').find('.selectipdiv').hide();
			$(this).closest('.systemIpdivItem').find('.inputipdiv').show();
			$(this).closest('.systemIpdivItem').find('input[name=input_type]').val('input');
		});

		//手动输入IP
		$('.selectsystemip').on('click', function(){
			$(this).closest('.systemIpdivItem').find('.selectipdiv').show();
			$(this).closest('.systemIpdivItem').find('.inputipdiv').hide();
			$(this).closest('.systemIpdivItem').find('input[name=input_type]').val('select');
		});
	}

	var initGFS = function(){
		$("#GFSDiv").initGFSPlug();
	}
	
	//获取虚拟化平台关联备份节点信息
	var initAllocationNodes = function(){
		$('#backupTarget').backupTarget();
	}

    return {
        //main function to initiate the module
        init: function () {
        	initSoftwareVersionDiff(); //版本差异处理
        	wizardInit();         //
			initSelectShowType();
			initSearch();
        	initMyTree();         //
			// initApplianceSelect(); //初始化appliance下拉选择框
        	initListener();       //
        	inintDatatimePicker();//
        	initData();           //初始化备份流程数据
//			initNodeSelect();	  //初始化节点选择
			initDataChangeListeners(); //初始化策略事件
			initStrategySelect(); //初始化策略选择
			initGFS() //初始化GFS策略
			initSpinner();
        }
    };

}();

jQuery(document).ready(function() {
	Metronic.blockUI({target: '.src-wrap__content', animate: true});
	setTimeout(() => {
		VMBackup.init();
	}, 200);
});