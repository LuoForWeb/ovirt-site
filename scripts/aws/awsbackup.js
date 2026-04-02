var AWSBackup = function () {
	const BILLING_BANDWIDTH = 'bandwidth', BILLING_TRAFFIC = 'traffic'; // 计费方式
	var _ModifyFlag = false; //是否修改页面
	//提交数据
	var data = {
		src_info: {},
		time_strategy: {},
		storage_strategy: {},
		reserved_strategy: {},
		advanced_strategy: {},
		transport_strategy: {},
		speed_strategy: [],
		job_name: '',
		strategy_group_uuid: '',
		hypervisor_type: 0,
	};
	var nodeParamList;
	var currentTree;
	var currentTreeDivId;	//用于搜索时候定位
	var SETTINGS = null;	//初始数据
	var _timeStamp = '';	//时钟时间戳,全局
	var nodeSelectFlag = false; //自定义节点选择加载
	var applianceRegionUuid = ''; //加载appliance选择的区域uuid
	var applianceIpRegionUuid = ''; //传输代理公有ip选择的区域uuid
	var storageSelectFlag = false;
	var _VMNAMEREG = new RegExp("[ \n\r\t\passwordAutocheckf`~!@#$^&*()=|{}':;',\\[\\].<>/?~！@#￥……&*（）&;|{}【】‘；：”“'。，、？+-]");
	var pageIndex = 0; //轮播索引
	var initSpeedFlag = false;
	var speedList = [];
	var initStrategyFlag = false;
	var globalStrategy = [];
	var defaultStrategy = [];
	var authFun = [];
	var _oldPassword = '';
	var passwordChangeFlag = false; // 是否改变了密码框内容标记
	var initConfigFlag = false;
	var firstInitPageFlag = false; // 首次进入页面标记
	var selectIPFlag = true;
	var nodeLoaded = []; //记录已加载过的虚拟化中心
	var speedFlag = false;//是否是快速创建
	var oldGFSDes = "";
	var currentmax = 0;
	var oldVcenter, _allocationList = [];
	var backupObjList = {}; //备份对象，{objId:{"object_uuid":"","vcenter_uuid":"","type":"","exclude_vm_list":[{"vm_uuid":"","vm_path":""}]}...}
	var globalSelect = {}; //全局筛选条件，{"select_type":"","select_value":["", ""]}
	var selectType = 1; //前缀过滤方式
	var selectValue = []; //前缀
	var delVmTipsShowFlag = false; //移除虚拟机提示是否已显示
	var backupStrategy = {}; //备份策略，修改任务使用
	var cloudTargetFlag = false;
	var originCloudFlag = false; //云存储任务标志
	var _AgentBillingType = $('select[name=agent_billing_type]');
	var _AgentBillingValue = $('input[name=agent_billing_value]');
	var backupTargetInfo; // 备份目的地信息，从插件获取
	var isLicense = false; // 标记下授权是否有效
	var submittedFlag = false; // 是否已提交

	// 传输代理相关
	var _AS = $('#applianceSelect');
	var currentAgentInstanceType = ''; // 当前传输代理实例类型
	var agentNetworkList = []; // 传输代理网络
	var regionUuids = [], regions = [], regionsFlag = []; // 传输代理网络区域是否加载flag

    // 虚拟机名称过滤方式
    const VM_FILTER_PREFIX_EXCLUDE = 0;
    const VM_FILTER_PREFIX_MATCH = 1;
    const VM_FILTER_KEYWORD_EXCLUDE = 2;
    const VM_FILTER_KEYWORD_MATCH = 3;

	var initData = function(){
		//setp1
		data.src_info.instance_info = [];
		//disklist
		data.src_info.exclude_disk_list =[];
		//setp2
		//备份方式:策略/时间
		data.time_strategy.type = 1;
		//完备/增备/差备/永久增量
		data.time_strategy.full_info = [];
		data.time_strategy.incr_info = [];
		data.time_strategy.diff_info = [];
		data.time_strategy.pincr_info = [];
		//按时间备份的时间
		data.time_strategy.datetime = '';
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
		$('#tab_common').backupStrategy(CONF.MODULE_TYPE.PUBLIC_CLOUD, false);

		// 初始化传输代理计费方式
		$('select[name=agent_billing_type]').val('traffic');
		$('input[name=agent_billing_value]').val(300);

		// 初始化重试策略
		$('#retry_config').retryStrategy();

		$('#backupTarget').backupTarget();
	}

	var initModifyData = function () {
		let strategy = {}; //用于构造js插件的参数
		//setp1
		data.src_info.instance_info = [];
		//disklist
		data.src_info.exclude_disk_list = [];
		//hypervisor
		data.src_info.hypervisor_type = SETTINGS.hypervisor;
		//backupObjList
		currentTreeDivId = 'vm_tree_hc';
		$.each(SETTINGS.vm_info, function (i, v) {
			var objId = currentTreeDivId + v.vcuuid + v.object_uuid;
			backupObjList[objId] = {
				"object_uuid": v.object_uuid,
				"vcenter_uuid": v.vcuuid,
				"type": v.type,
				"exclude_vm_list": v.exclude_vms
			}
			//组合instance_info
			data.src_info.instance_info.push({
				instance_uuid: v.object_uuid,
				platform_uuid: v.vcuuid,
				type: v.type,
				path: v.path,
				config: v.vm_config
			});
			data.src_info.exclude_disk_list[i] = {
				disk_list: v.vm_config.disk_list
			};
		});

		//自动加入开关
		data.src_info.auto_join_flag = SETTINGS.auto_join_flag;
		$('#autoJoinFlag').bootstrapSwitch('state', SETTINGS.auto_join_flag);

		//global_select
		if ($.isArray(SETTINGS.global_select)) {
			// 兼容604及以前数组格式的数据
			globalSelect.select_type = SETTINGS.global_select[0].select_type;
			globalSelect.select_value = SETTINGS.global_select.map(item => {return item.select_value});
		} else {
			globalSelect = SETTINGS.global_select;
		}
		// 前缀匹配/排除转为关键词匹配/排除
		if (VM_FILTER_PREFIX_MATCH == globalSelect.select_type) {
			globalSelect.select_type = VM_FILTER_KEYWORD_MATCH;
		}
		if (VM_FILTER_PREFIX_EXCLUDE == globalSelect.select_type) {
			globalSelect.select_type = VM_FILTER_KEYWORD_EXCLUDE;
		}
		data.src_info.global_select = globalSelect;
		//前缀筛选
		if (0 === globalSelect.select_value.length || '' === globalSelect.select_type) {
			//默认关键词匹配
			$('#vmMatchType').val(VM_FILTER_KEYWORD_MATCH);
			$('#vmMatchWildcard').val('');
		} else {
			$('#vmMatchType').val(globalSelect.select_type);
			$('#vmMatchWildcard').val(globalSelect.select_value[0]);
			// 多个
			for (let i = 1; i < globalSelect.select_value.length; i++) {
				let html = `
					<div class="form-group filter-input">
						<div class="col-md-9">
							<input class="form-control inline-block vm_prefix_add" name="vm_prefix" value="${globalSelect.select_value[i]}">
						</div>
						<div class="col-md-3">
							<button type="button" class="btn btn-default delPrefixBtn" style="width: 50px;">
								<i class="viconfont vicon-ge_delete"></i>
							</button>
						</div>
					</div>
				`;
				$(`#vmFilterModal`).find('.filter-input-container').append(html);
			}
		}
		// 删除过滤的前缀，如果只有一个输入框则清空
		$(`.delPrefixBtn`).on('click', function () {
			if ($(`.filter-input`).length > 1) {
				$(this).closest('.filter-input').remove();
			} else {
				$(this).closest('.filter-input').find(`input[name=vm_prefix]`).val('');
			}
		});

		vmFilterSubmit(SETTINGS.auto_join_flag);

		//setp2
		//备份方式:策略/时间
		data.time_strategy.type = SETTINGS.timestrategy.type;
		strategy.backupInfo = {};
		strategy.backupInfo.type = SETTINGS.timestrategy.type;
		//完备/增备/差备/永久增量
		data.time_strategy.full_info = [];
		strategy.backupInfo.fullInfo = {};
		data.time_strategy.incr_info = [];
		strategy.backupInfo.incrInfo = {};
		data.time_strategy.diff_info = [];
		strategy.backupInfo.diffInfo = {};
		data.time_strategy.pincr_info = [];
		strategy.backupInfo.pIncrInfo = {};
		var timestrategy = SETTINGS.timestrategy.data;


		//按时间备份的时间
		data.time_strategy.datetime = '';
		strategy.backupInfo.datetime = null;
		if(SETTINGS.timestrategy.type == "oncetime"){
			data.time_strategy.datetime = SETTINGS.timestrategy.data;
			strategy.backupInfo.datetime = SETTINGS.timestrategy.data;
		}else{
			for(var i=0; i<timestrategy.length; i++){
				var info = {};
				var days = [];
				for (var j=0; j<timestrategy[i].days.length;j++){
					if(timestrategy[i].days.length == 1 && timestrategy[i].days[j] == false){
						days = [];
					}else if(timestrategy[i].days[j] == true){
						timestrategy[i].days[j] = 1;
						days.push(timestrategy[i].days[j]);
					}else if(timestrategy[i].days[j] == false){
						timestrategy[i].days[j] = 0;
						days.push(timestrategy[i].days[j]);
					}
				}
				info.days = days;
				info.mode = timestrategy[i].mode;
				info.type = timestrategy[i].strategy_type;
				info.start_time = timestrategy[i].start_time;
				info.startTime = timestrategy[i].start_time;
				info.roll_flag = timestrategy[i].roll_flag;
				info.rollFlag = timestrategy[i].roll_flag;
				info.roll_interval = timestrategy[i].roll_interval;
				info.rollInterval = timestrategy[i].roll_interval;
				info.roll_end_time = timestrategy[i].roll_end_time;
				info.endTime = timestrategy[i].roll_end_time;
				info.frequency = timestrategy[i].frequency;
				info.full_backup_compensation_flag = timestrategy[i].full_backup_compensation_flag;
				if(timestrategy[i].mode == '1'){
					data.time_strategy.full_info = info;
					strategy.backupInfo.fullInfo = info;
				}else if(timestrategy[i].mode == '2'){
					data.time_strategy.incr_info = info;
					strategy.backupInfo.incrInfo = info;
				}else if(timestrategy[i].mode == '3'){
					data.time_strategy.diff_info = info;
					strategy.backupInfo.diffInfo = info;
				} else if (timestrategy[i].mode == '9'){
					data.time_strategy.pincr_info = info;
					strategy.backupInfo.pIncrInfo = info;
				}
			}
		}
		//setp3
		//传输策略
		data.transport_strategy.mode = 'nbd';
		data.transport_strategy.appliance_uuid = SETTINGS.applianceuuid;
		data.transport_strategy.agent_network = [];
		$.each(SETTINGS.bts.agent_network, function (i, v) {
			data.transport_strategy.agent_network.push(v);
		});
		data.transport_strategy.agent_public_ip = SETTINGS.agent_public_ip;
		data.transport_strategy.encrypt = SETTINGS.bts.encrypt;
		data.transport_strategy.encrypt_method = SETTINGS.bts.encrypt_method;
		data.transport_strategy.reconnect_times = SETTINGS.bts.reconnect_times;
		data.transport_strategy.reconnect_interval = SETTINGS.bts.reconnect_interval;

		//限速策略
		speedList = SETTINGS.speedInfo;
		data.speed_strategy = SETTINGS.speedInfo;
		strategy.speedInfo = SETTINGS.speedInfo;

		//保留策略
		data.reserved_strategy = {};
		data.reserved_strategy.reserved_type = SETTINGS.brs.type;
		data.reserved_strategy.gfs_reserved_flag = SETTINGS.brs.GFS !== [];
		data.reserved_strategy.gfs_reserved_strategy = initGFSData(SETTINGS.brs.GFS);
		data.reserved_strategy.gfs_modify_flag = false;
		data.reserved_strategy.strategyMode = SETTINGS.brs.strategyMode;
		data.reserved_strategy.value = SETTINGS.brs.number;
		strategy.reserveInfo = {};
		strategy.reserveInfo.type = SETTINGS.brs.type;
		strategy.reserveInfo.value = SETTINGS.brs.number;
		strategy.reserveInfo.strategyMode = SETTINGS.brs.strategyMode;
		strategy.reserveInfo.gfs_strategy_item_list = data.reserved_strategy.gfs_reserved_strategy;
		SETTINGS.bss.dataencrypt = SETTINGS.bss.encrypt; // 适配存储策略插件
		strategy.storageInfo = SETTINGS.bss;
		firstInitPageFlag = true;

		//初始化节点信息
		data.advanced_strategy.node = {};
		data.advanced_strategy.node.node_uuid = SETTINGS.node.nodeuuid;
		data.advanced_strategy.node.node_pool_uuid = SETTINGS.node.node_pool_uuid;
		data.advanced_strategy.node.storage_uuid = SETTINGS.node.storageuuid;
		data.advanced_strategy.node.storage_pool_uuid = SETTINGS.node.storage_pool_uuid;
		$('#backupTarget').backupTarget({
			node_uuid: SETTINGS.node.nodeuuid,
			node_pool_uuid: SETTINGS.node.node_pool_uuid,
			storage_uuid: SETTINGS.node.storageuuid,
			storage_pool_uuid: SETTINGS.node.storage_pool_uuid,
			storage_pool_type: SETTINGS.node.storage_pool_type,
		});

		//存储策略
		data.storage_strategy = {};
		data.storage_strategy.compress_method = SETTINGS.bss.compress_method;
		data.storage_strategy.deduplication_flag = SETTINGS.bss.deduplication;
		data.storage_strategy.block_size = SETTINGS.bss.blocksize;
		data.storage_strategy.compress_flag = SETTINGS.bss.compress;
		data.storage_strategy.encrypt_flag = SETTINGS.bss.encrypt;
		data.storage_strategy.encrypt_method = SETTINGS.bss.encrypt_method;
		data.storage_strategy.auto_create_password_flag = SETTINGS.bss.password_auto_flag;
		data.storage_strategy.password = SETTINGS.bss.password;
		data.storage_strategy.data_container_size = SETTINGS.bss.data_container_size;
		data.storage_strategy.redundant_data_proportion = SETTINGS.bss.redundant_data_proportion;

		//初始化高级功能
		data.advanced_strategy.mode = {};
		data.advanced_strategy.mode.snapshot_check = SETTINGS.mode.snapshot;
		data.advanced_strategy.mode.keep_snapshot_check = SETTINGS.mode.keepsnapshot;
		data.advanced_strategy.mode.snapshot_timeout = SETTINGS.mode.snapshottimeout;
		data.advanced_strategy.mode.parse_fs_flag = SETTINGS.mode.parsefsmode;
		data.advanced_strategy.mode.swap_flag = SETTINGS.mode.swapmode;
		data.advanced_strategy.mode.gap_flag = SETTINGS.mode.gapmode;
		data.advanced_strategy.mode.snapshot_type = SETTINGS.mode.snapshotmode;
		data.advanced_strategy.mode.thread_num = SETTINGS.mode.threadnum;
		data.advanced_strategy.mode.pre_snapshot_flag = SETTINGS.mode.presnapshot;
		data.advanced_strategy.mode.ignore_resource_limiting_flag = SETTINGS.mode.ignore_resource_limiting_flag;

		//初始化校验策略
		// data.verifyInfo.ischeck = SETTINGS.verifyInfo.ischeck;
		// data.verifyInfo.verifytype =  SETTINGS.verifyInfo.algorithm;
		//任务信息
		data.job_name = SETTINGS.taskname;
		data.job_uuid = SETTINGS.taskuuid;

		//备份策略
		backupStrategy.time = {};
		backupStrategy.store = {};
		backupStrategy.reserve = {};
		backupStrategy.time.timeInfo = strategy.backupInfo;
		backupStrategy.time.type = SETTINGS.timestrategy.type; // 备份方式
		backupStrategy.speedlimit = strategy.speedInfo;
		backupStrategy.speedlimit.speed = SETTINGS.speedInfo.speedInfo;
		backupStrategy.store.storeInfo = strategy.storageInfo;
		backupStrategy.reserve.reserveInfo = strategy.reserveInfo;
		$('#tab_common').backupStrategy(CONF.MODULE_TYPE.PUBLIC_CLOUD, true, backupStrategy);

		// 保留策略
		oldGFSDes = $(".GFSstrategydes").text();

		// 判断原来的存储是否为云存储
		if(SETTINGS.node.storage_type == CONF.BD_STORAGE_TYPE.CLOUD) {
			originCloudFlag = true;
		}

		// 初始化安全策略
		data.safe_strategy = JSON.parse(JSON.stringify(SETTINGS.safe_strategy));
		data.safe_strategy.virus_scan_config_list = JSON.stringify(SETTINGS.safe_strategy.virus_scan_config_list);

		// 初始化重试策略
		data.retry_strategy = SETTINGS.retry_strategy;
		$('#retry_config').retryStrategy({'retry_strategy': SETTINGS.retry_strategy},'edit');
	}

	//初始化GFS数据
	var initGFSData = function(GFSData){
		var reData = [];
		//数据为空的情况
		if(GFSData == undefined || GFSData == null || GFSData == "" || GFSData.length == 0){
			return reData;
		}
		if(GFSData.week){
			var thisList = {};
			thisList.level1_type = 1;
			thisList.level2_type = GFSData.week[0];
			thisList.retention_num = GFSData.week[1];
			thisList.gfs_reserved_type = 1;
			thisList.gfs_reserved_start = GFSData.week[0];
			thisList.gfs_reserved_value = GFSData.week[1];
			reData.push(thisList);
		}
		if(GFSData.month){
			var thisList = {};
			thisList.level1_type = 2;
			thisList.level2_type = GFSData.month[0];
			thisList.retention_num = GFSData.month[1];
			thisList.gfs_reserved_type = 2;
			thisList.gfs_reserved_start = GFSData.month[0];
			thisList.gfs_reserved_value = GFSData.month[1];
			reData.push(thisList);
		}
		if(GFSData.year){
			var thisList = {};
			thisList.level1_type = 3;
			thisList.level2_type = GFSData.year[0];
			thisList.retention_num = GFSData.year[1];
			thisList.gfs_reserved_type = 3;
			thisList.gfs_reserved_start = GFSData.year[0];
			thisList.gfs_reserved_value = GFSData.year[1];
			reData.push(thisList);
		}
		return reData;

	}

	//初始化监听事件
	var initListener = function(){
		$('#backuptype').on('change', backupTypeHandler);
		$('#resetdate').on('click',function(){
			$('#oncetime').val('');
		});
		$('#toaddvcenter').on('click',function(){
			LOCATION('./content/vm/cloudplatform/add_cloud_platform.php?cloudType=public', 'infrastructure');
		});

		//节点改变
		$('#selectnode').on('change', initStorageSelect);
		//存储改变
		// $('#selectstorage').on('change', initNodeSelect);
		//选择备份策略复选框
		$('#strategymode').find('.icheck').on('ifClicked', strategyModeClick);

		//启动内联数据消重
		$('#parsefscheck').on('switchChange.bootstrapSwitch', parsefsChange);

		//初始化添加限速策略模态框
		$('#addSpeedlimit').on('click', function(){
			$('#speedlimitModal').modal({'width':'800px', 'height':'380px'});
			if(!initSpeedFlag){
				initSpeedTimeStrategy();
			}

		});

		$('#autoJoinFlag').on('switchChange.bootstrapSwitch', autoJoinChange);

		//初始化虚拟机过滤模态框
		$('#vmFilterBtn').on('click', function() {
			$('#vmFilterModal').modal({'width':'800px'});
		});

		//切换限速模式
		$('#speedModeType').on('change', speedModeHandler);


		//策略类型选择
		$('#strategySelect').on('change', strategyHandler);

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

		//自动选择IP
		$('#diysystemip').on('click', function(){
			$('.selectipdiv').hide();
			$('.inputipdiv').show();
			selectIPFlag = false;
		});

		//手动输入IP
		$('#selectsystemip').on('click', function(){
			$('.selectipdiv').show();
			$('.inputipdiv').hide();
			selectIPFlag = true;
		});

		//切换GFS保留策略开关
		//切换存储加密开关
		$('#GFSflag').on('switchChange.bootstrapSwitch', GFSChange);

		// 压缩传输
		$('#compressCheck').on('switchChange.bootstrapSwitch', compressChange);

		//初始化多选下拉框
		$(".selectpicker").selectpicker({
			noneSelectedText: LANG.BILLING_PLEASE_SELECT,
			deselectAllText: LANG.BILLING_DESELECT_ALL,
			selectAllText: LANG.BILLING_SELECT_ALL,
			liveSearchPlaceholder: LANG.BILLING_SEARCH,
			countSelectedText: function(){}
		});

		//传输代理
		$('#applianceSelect').next('.bootstrap-select').find('.dropdown-menu .inner').css({height: '400px', width: 'auto'});
		$('#applianceSelect').next('.bootstrap-select').find('.dropdown-toggle').on('click', function () {
			var nodes = currentTree.getCheckedNodes();
			initApplianceSelect(nodes[0].vcenteruuid, nodes[0].hostuuid ?? nodes[0].regions[0].host_uuid, nodes[0].machineDetail); //通过平台和区域初始化传输代理实例类型
		});

		// 传输策略---加密传输
		$('#encrypttransfer').on('switchChange.bootstrapSwitch', transferEncryptChange);

		// 切换付费方式
		_AgentBillingType.on('change', function () {
			if (BILLING_BANDWIDTH === this.value) {
				_AgentBillingValue.attr('max', 2000);
				_AgentBillingValue.attr('maxlength', 4);
			} else {
				_AgentBillingValue.attr('max', 300);
				_AgentBillingValue.attr('maxlength', 3);
			}
		});

		$('select[name=agent_public_ip]').on('click', function () {
			initPublicIp(this.value);
		});

		// 保留类型是按备份链时，隐藏高级配置-存储-合并模式
		$(`#reserveMode`).on('change', function () {
			if (CONF.RESERVE_STRATEGY_MODE.CHIAN === parseInt(this.value)) {
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

		// 选中或取消永久增量后重新初始化worm
		$('#pincrBackup').on('ifChanged', initWormConfig);
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

	// 显示压缩等级
	var compressChange = function () {
		if (this.checked) {
			$('.compressGradeDiv').show();
		} else {
			$('.compressGradeDiv').hide();
		}
	};

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
		var password = $('#password').val();
		var repassword = $('#repassword').val();
		if(password != repassword){
			$('.passwordTips').show();
		}else{
			$('.passwordTips').hide();
		}
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

	//切换自动选择存储加密密码
	var passwordModeChange = function(){
		if(this.checked){
			$('#password').val('');
			$('#repassword').val('');
			$('.passwordDiv').hide();
		}else{
			if (_ModifyFlag) {
				if (firstInitPageFlag) {
					passwordChangeFlag = false;
				} else {
					passwordChangeFlag = true;
				}
				$('#password').attr('placeholder', LANG.UI_PUBLIC_ENTER_PASSWORD);
				$('#repassword').attr('placeholder', LANG.UI_PUBLIC_ENTER_REPASSWORD);
				$('.passwordDiv').show();
			} else {
				$('.passwordDiv').show();
			}
		}
		firstInitPageFlag = false;
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

	var speedModeHandler = function(){
		if(this.value == 2){
			$('.setSpeedStrategy').hide();
		}else{
			$('.setSpeedStrategy').show();
		}
	}

	//添加虚拟机过滤条件
	var vmFilterSubmit = function (auto_join_flag = true) {
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
				lang = LANG.UI_BACKUP_AWS_INSTANCE_NAME_PREFIX_FILTER;
				break;
			case VM_FILTER_PREFIX_MATCH:
				lang = LANG.UI_BACKUP_AWS_INSTANCE_NAME_PREFIX_MATCH;
				break;
			case VM_FILTER_KEYWORD_EXCLUDE:
				lang = LANG.UI_BACKUP_AWS_INSTANCE_NAME_KEYWORD_FILTER;
				break;
			case VM_FILTER_KEYWORD_MATCH:
				lang = LANG.UI_BACKUP_AWS_INSTANCE_NAME_KEYWORD_MATCH;
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
	}

	//删除虚拟机过滤条件
	var deleteFilterItem = function () {
		selectValue = [];
		globalSelect.select_value = [];
		$.each(backupObjList, function (i, v) {
			v.exclude_vm_list = [];
		});
		$(this).parent('.filter-item').remove();
		handlerFilterVmListCheck(false);
		$('#vmMatchWildcard').val(''); //重置输入框
		$(`.vm_prefix_add`).closest(`.form-group`).remove();
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

	// 应用全局策略并初始化策略信息
	var strategyHandler = function(){
		// 全局策略选中
		var index = $('#strategySelect option:selected').val();
		var strategy = globalStrategy[index];
		if(cloudTargetFlag){
			$('#tab_common').backupStrategy(CONF.MODULE_TYPE.PUBLIC_CLOUD, false, strategy, 'cloud');
		}else{
			$('#tab_common').backupStrategy(CONF.MODULE_TYPE.PUBLIC_CLOUD, false, strategy, 0);
		}
		// 限速策略
		if(index == 0) return false;
		// 初始化存储加密密码
		
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

	//添加限速策略
	var addSpeedList = function(list){
		var des = "";
		var uuid = list.uuid;
		des +=
			'<li class="list-group-item popovers speedTips list-group-item__speed" id="speed'+ uuid +'" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="'+ list.des +'">' +
			'<div class="col1">' +
			'<div class="cont ">' +
			'<div class="cont-col1"></div>' +
			'<div class="cont-col2">' +
			'<div class="desc list-one" style="overflow:hidden;text-overflow: ellipsis;white-space: nowrap">' + list.des + '</div>' +
			'</div>' +
			'</div>' +
			'</div>' +
			'<div class="col2  pull-right delete-list">' +
			'<a class="del'+ uuid +'" >' +
			'<div class="label label-sm label-danger" style="padding:0;">' +
			'<i class="viconfont vicon-cuowu"></i>' +
			'</div>' +
			'</a>' +
			'</div>' +
			'</li>';
		$('#speedList').append(des);
		$('.del'+ uuid).on('click', function(){
			$('.popover.in').remove();
			$('#speed' + uuid).remove();
			for(var j=0;j<speedList.length; j++){
				if(uuid == speedList[j].uuid){
					speedList.splice($.inArray(speedList[j],speedList),1);
				}
			}
			initSpeedStrategyDes();
		});
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
			if(!data.vcbt){
				//设置有效数据提取关闭并不可用再加隐藏
				$('#parsefscheck').bootstrapSwitch('state', false);
				$('#parsefscheck').bootstrapSwitch('disabled', true);
				$('.parsefsDiv').hide();
				$(`#li_high_vcbt`).hide();

				//深度有效数据提取结果显示隐藏
				$('.parsefsmodeshow').hide();
				$('.swapmodeshow').hide();
				$('.gapmodeshow').hide();
			}
			//中文标准版不支持重删
			if(1 == CONF.SOFTWARE){
				//设置重删为关闭并不可用再加隐藏
				$('.deduplicationdiv').hide();
			}
			if(1 == CONF.SOFTWARE || 4 == CONF.SOFTWARE){
				//设置有效数据提取关闭并不可用再加隐藏
				$('#parsefscheck').bootstrapSwitch('state', false);
				$('#parsefscheck').bootstrapSwitch('disabled', true);
				$('.parsefsDiv').hide();
				$(`#li_high_vcbt`).hide();

				//深度有效数据提取结果显示隐藏
				$('.parsefsmodeshow').hide();
				$('.swapmodeshow').hide();
				$('.gapmodeshow').hide();
			}
			if(4 == CONF.SOFTWARE || 9 == CONF.SOFTWARE){
				$('#snapshotcheck').bootstrapSwitch('state', false);
				$('#snapshotcheck').bootstrapSwitch('disabled', true);
				$('#backupmodediv').hide();							//隐藏高速模式
				$('.backupmodeshow1').hide();						//隐藏高速模式显示
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

				data.time_strategy.incr_info = [];
				data.time_strategy.diff_info = [];
			} else {
				//选中完备同时取消永久增量
				$('#strategymode').find('input[data-mode=9]').iCheck('uncheck');
				$('#stragegyaccordion').find('.strategy-panel[data-mode=9]').hide();

				data.time_strategy.pincr_info = [];
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

				data.time_strategy.diff_info = [];
				data.time_strategy.pincr_info = [];
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

				data.time_strategy.incr_info = [];
				data.time_strategy.pincr_info = [];
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

				data.time_strategy.full_info = [];
				data.time_strategy.incr_info = [];
				data.time_strategy.diff_info = [];
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
			$('#gapcheck').bootstrapSwitch('state', true);

			$('.swapdiv').show();
			$('.gapdiv').show();

			$('.swapmodeshow').show();
			$('.gapmodeshow').show();
		}else{
			$('#swapcheck').bootstrapSwitch('state', false);
			$('#gapcheck').bootstrapSwitch('state', false);
			$('.swapdiv').hide();
			$('.gapdiv').hide();

			$('.swapmodeshow').hide();
			$('.gapmodeshow').hide();
		}
	}


	//初始化节点下拉框
	var initNodeSelect = function(){
		if(nodeSelectFlag) return; //加载一次
		pAjaxRequest({}, "/api/v1/nodes/select", "GET", function (d) {
			let data = d.data;
			var softselect = $('#selectnode');
			softselect.empty();
			var oriNodeFlag = false; //计算节点列表是否包含原计算节点
			for(var i=0; i<data.length; i++){
				//
				if(_allocationList.length !=0 && $.inArray(data[i].uuid, _allocationList) == -1) continue;
				var option = $("<option>").text(data[i].text).val(data[i].uuid);
				softselect.append(option);
				if (_ModifyFlag && SETTINGS.node.nodeuuid && SETTINGS.node.nodeuuid == data[i].uuid) {
					$('#selectnode').val(SETTINGS.node.nodeuuid);
					oriNodeFlag = true;
				}
			}
			if (!oriNodeFlag) {
				$('#selectnode').val(data[0].uuid);
			}
			nodeSelectFlag = true;
			initStorageSelect();
		}, false);
		var selectype  = $('#selectstorage').find('option:selected').attr("type");
		// 云存储需要选择计算节点，不支持永久增量，其他存储相反
		if (CONF.BD_STORAGE_TYPE.CLOUD == selectype || CONF.BD_STORAGE_TYPE.TAPE == selectype) {
			// $('.diynodediv').show();
			// $('#nodeinfoshow').show();

			//取消选中永久增量并隐藏策略显示
			$('#strategymode').find('input[data-mode=9]').iCheck('uncheck');
			$('#backupTimestrategy').find('.strategy-panel[data-mode=9]').hide();
			$('.pIncr').hide();
			$('.time-strategy-tips').hide();
			$('.no-pIncr-tips').show();
		} else {
			// $('.diynodediv').hide();
			// $('#nodeinfoshow').hide();
			$('.pIncr').show();
			$('.time-strategy-tips').hide();
			$('.all-strategy-tips').show();
		}
	}

	//初始化存储下拉框
	var initStorageSelect = function(){
		// if(storageSelectFlag) return; //加载一次
		var data = {};
		data.node_uuid = $('#selectnode').val();
		pAjaxRequest(data, "/api/v1/storages/backup", "GET", function (d) {
			let data = d.data;
			var softselect = $('#selectstorage');
			softselect.empty();
			var storageList = [];
			for(var i=0; i<data.length; i++){
				var option = `<option data-type="${data[i].storage_type}" data-name="${data[i].name}" data-worm="${data[i].worm_flag}" value="${data[i].storage_uuid}">${data[i].text}</option>`;
				softselect.append(option);
				storageList.push(data[i].storage_uuid);
			}
			if(_ModifyFlag && $.inArray(SETTINGS.node.storageuuid, storageList) != -1){
				$('#selectstorage').val(SETTINGS.node.storageuuid);
			}
			// storageSelectFlag = true;
			// initNodeSelect();  //初始化计算节点下拉框
		}, false);
	}

	var initSpinner = function(){
		initReserveSpinner($('#spinnerDay'));
		initReserveSpinner($('#spinnerNum'));
		$('#speedSpinnerNum').spinner({value:10, step: 5, min: 1, max: 10000000000});
		$('.backupThreadDiv').spinner({value:3, step: 1, min: 1, max: 8});

		if (_ModifyFlag) {
			if(SETTINGS.brs.type == 2){
				initReserveSpinner($('#spinnerDay'), SETTINGS.brs.number);
			}else{
				initReserveSpinner($('#spinnerNum'), SETTINGS.brs.number);
			}
			$('.backupThreadDiv').spinner({value: SETTINGS.mode.threadnum, step: 1, min: 1, max: 8});
		}
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
			$(".selectvmtip").html(LANG.UI_BACKUP_AWS_SELECT_INSTANCE_TIPS).show();
			// 动态设置tab-pane的高度
			$(".tab-pane__row").css('height', 'calc(100% - 80px)');
			return false;
		}
		var showStr = '';
		var showExcludeVmStr = ''; //排除虚拟机描述
		// data.src_info.exclude_disk_des = [];
		data.src_info.exclude_disk_list = [];
		//添加disk_list和最后一步显示已排除磁盘列表
		for(var i=0;i<nodes.length;i++){
			// data.src_info.exclude_disk_des[i] = "";
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
							// data.srcInfo.disk[i] += "<br>"+ LANG.UI_BACKUP_EXCLUDE_DISK + $('#disCheck' + j + escapeJquery(nodes[i].id) + escapeJquery(nodes[i].vcenteruuid)).val();
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
		data.src_info.global_select = globalSelect;
		data.src_info.instance_info = [];
		showStr += CONF.VM_DES[nodes[0].hypervisor] + LANG.UI_PUBLIC_BACKUP + "<br>";
		var count = 0;
		$.each(nodes, function(i, d){
			if('vm' == d.eventtype){
				data.src_info.instance_info[count] = {
					instance_uuid: d.id,
					platform_uuid: d.vcenteruuid,
					type: d.type,
					path: d.path,
					config: {
						disk_list: data.src_info.exclude_disk_list[count].disk_list
					}
				};
				//虚拟机显示虚拟机+排除列表信息
				// showStr += d.path.replace(d.id, d.name) + data.src_info.exclude_disk_des[count] + "<br>";
				let excludeStr = '';
				$.each(data.src_info.exclude_disk_list[count].disk_list, function (i, v) {
					excludeStr += "<br>"+ LANG.UI_BACKUP_EXCLUDE_DISK + v.disk_name;
				});
				showStr +=  d.path.replace(d.id, d.name) + excludeStr + "<br>";
			} else {
				//其他对象显示对象信息+排除虚拟机信息
				showStr += d.path + "<br>";
				var objId = getObjId(d);
				// console.log(objId);
				var excludeVmList = backupObjList[objId].exclude_vm_list;
				$.each(excludeVmList, function (i, v) {
					showExcludeVmStr += v.vm_path + "<br>";
				});
				data.src_info.instance_info[count] = {
					instance_uuid: d.id,
					platform_uuid: d.vcenteruuid,
					type: d.type,
					path: d.path,
					config: {
						disk_list: data.src_info.exclude_disk_list[count].disk_list
					},
					exclude_vm_list: excludeVmList
				};
			}
			data.hypervisor_type = d.hypervisor;
			count++;
		});

		// 最终选择的vm个数=粒度下vm+单选的vm
		let selectVmCount = $(`.vmCheck:checked`).length + data.src_info.instance_info.filter(item => item.type === 7).length;
		// 关闭自动加入时，判断虚拟机个数不能为0
		if (!data.src_info.auto_join_flag && !selectVmCount) {
			let lang = LANG.UI_BACKUP_AWS_SELECT_INSTANCE_TIPS;
			$(".selectvmtip").html(lang).show();
			// 动态设置tab-pane的高度
			$(".tab-pane__row").css('height', 'calc(100% - 80px)');
			return false;
		}

		$(".selectvmtip").hide();


		$('.showBackupmode').show();						//默认显示备份模式tab
		$('#backupmodeshowdiv').show();                     //默认显示备份模式信息

		// $('.highLi').show();
		// $('.highLi').removeClass('active');
		// $('.commonLi').removeClass('active').addClass('active');
		// $('#tab_common').removeClass('active').addClass('active');
		// $('#tab_other').removeClass('active');


		$('.speedlimitDiv').show(); 			//显示限速策略
		$('.threadDiv').show();					//显示多线程

		//显示数据加密
		$('.encryptStorageDiv').show();
//		$('.passwordModeDiv').hide();
//		$('.passwordDiv').hide();

		$('.systemIpdiv').hide();	//备份系统IP

		if (_ModifyFlag) {
			// 数据文件大小，修改任务时不能变小，去掉比当前值小的选项
			$(`#data_container_size`).val(data.storage_strategy.data_container_size);
			$(`#data_container_size option`).each(function () {
				if (this.value < data.storage_strategy.data_container_size) {
					$(this).remove();
				}
			});
			// 合并冗余数据比例
			$(`#redundant_data_proportion`).val(data.storage_strategy.redundant_data_proportion);
		}

		if(!initConfigFlag && !_ModifyFlag){
			$('.ipSegmentDiv').hide();	//隐藏数据传输网段
			$('#ipSegment').val('');
			$('#leixentransmode').prop('disabled', false);     //初始化传输模式状态
			$('#parsefscheck').bootstrapSwitch('state', false);   	//深度有效提取默认关闭
			$('#preSnapshotcheck').prop('disabled', false);	//提前创建快照
			$('.presnapshotshow').show();

			$('#encryptStorageCheck').bootstrapSwitch('state', false);  //默认关闭数据加密
			initConfigFlag = true;
		}

		if(1 == CONF.SOFTWARE || 4 == CONF.SOFTWARE || 9 == CONF.SOFTWARE){
			$('#backupmodeshowdiv').hide();						//隐藏备份模式确认
		}

		//判断是否有多线程授权
		if(!authFun.multithread){
			$('#backupThreadNum').val(1).attr("disabled", true); //单线程
			$('.backupThreadDiv .spinner-group-btn button').prop('disabled', true);
		}

		if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
			if(4 == CONF.SOFTWARE) {
				$('.ipSegmentDiv').hide();  //免费版隐藏数据传输网段
				$('.encryptStorageDiv').hide();//免费版隐藏数据加密
			}
		}
		switch (data.hypervisor_type) {
			case CONF.VM_TYPE.AWS:
				$('#snapshotTimeoutDiv').show();
				initAgentNetwork(); // 初始化传输代理网络
				$('#tab_high_inc_li').show();
				$('#backupmodediv').show();
				break;
			case CONF.VM_TYPE.HUAWEICLOUD:
				initAgentNetwork(); // 初始化传输代理网络
				// 华为云隐藏高速模式，显示保留快照
				$('#tab_high_inc_li').hide();
				$('#backupmodediv').hide();
				$('#keepsnapshotdiv').show();
				$('#snapshotTimeoutDiv').hide();
				if (!_ModifyFlag) {
					$('#snapshotcheck').bootstrapSwitch('state', false);
					$('#keepsnapshotcheck').bootstrapSwitch('state', true);
				}
				break;
			default:
				$('#backupmodediv').show();
				$('#keepsnapshotdiv').hide();
				$('#snapshotTimeoutDiv').hide();
				if (!_ModifyFlag) {
					$('#snapshotcheck').bootstrapSwitch('state', true);
					$('#keepsnapshotcheck').bootstrapSwitch('state', false);
				}
				break;
		}
		showStep1(showStr, showExcludeVmStr);

		initStrategyDes(); //初始化策略描述
		if (!_ModifyFlag && (!applianceRegionUuid || nodes[0].hostuuid != applianceRegionUuid)) {
			// 创建任务时未加载过或备份源变动则初始化
			initAgentSelect();
		}
		return true;
	}

	const licenseCheck = function () {
		let p = {
			type: 'a',
			module: 'cloud'
		};
		pAjaxRequest(p, '/api/v1/system/auth/base_info', "GET", function (result) {
			isLicense = result.data.license_flag;
		}, false);
	}

	var showStep1 = function(showStr, showExcludeVmStr){
		//初始化计时器
		initServerTime();
		if (!_ModifyFlag) {
			var info = {};
			info.hypervisor_type = data.hypervisor_type;
			pAjaxRequest(info, "/api/v1/vm/jobs/backup/job_name", "GET", function (d) {
				$('#jobname').val(d.data);
			}, false);
		}
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
		if(results){
			showStep2();
			// setStrategyTime(); //设置默认时间策略
		}
		return results;
	}

	var showStep2 = function(){
		//备份目的地(节点)
		$('.nodeinfoshow').html(backupTargetInfo.node_text);
		$('.storeinfoshow').html(backupTargetInfo.storage_text);
		//存储为磁带时，屏蔽保留策略，传输线程禁用，默认是1，屏蔽提示信息
		var storage_type = backupTargetInfo.storage_type;
		var storage_uuid = backupTargetInfo.storage_uuid;
		//判断是否是磁带，参数：存储uuid，存储类型，线程配置项div控制显示隐藏，线程输入框初始化spinner时的dom，初始化备份策略时的dom，模块类型
		getTapeStrategy(storage_uuid, storage_type, '.threadDiv', '.backupThreadDiv', '#tab_common', CONF.MODULE_TYPE.VM, _ModifyFlag);
		initHighStrategyDes();
		initResourceLimit(backupTargetInfo.node_uuid_list);

		initWormConfig();
		if (_ModifyFlag) {
			// 初始化安全策略
			$('#virusConfig').virusDetectionBackup('col-md-3', SETTINGS.safe_strategy.virus_scan_flag, SETTINGS.safe_strategy.virus_scan_config_list[0].interrupt_policy, SETTINGS.safe_strategy.virus_scan_config_list[0].all_timepoints_flag, SETTINGS.safe_strategy.virus_scan_config_list[0]);
			let integrityConfig = SETTINGS.safe_strategy.integrity_check_config;
			$('#completeConfig').completeDetectionBackup('col-md-3', SETTINGS.safe_strategy.integrity_check_flag, CONF.MODULE_TYPE.VM, true, integrityConfig.check_strategy, integrityConfig.full_error_policy, integrityConfig.inc_error_policy);
		} else {
			// 初始化安全策略
			$('#virusConfig').virusDetectionBackup('col-md-3', false, true, false);
			$('#completeConfig').completeDetectionBackup('col-md-3', authFun.integrity, CONF.MODULE_TYPE.VM, true);
		}

		// 云存储隐藏合并模式
		if (CONF.BD_STORAGE_TYPE.CLOUD === storage_type || CONF.BD_STORAGE_TYPE.TAPE === backupTargetInfo.storage_type) {
			$(`.mergemodediv`).hide();
			$(`.mergemodeshow`).hide();
		} else {
			$(`#li_high_storage`).show();
			$(`.mergemodediv`).show();
			$(`.mergemodeshow`).show();
		}
	}

	const initWormConfig = () => {
		if (_ModifyFlag) {
			$('#wormConfig').wormProtectionBackup(getWormType(),'col-md-3', false, SETTINGS.safe_strategy.worm_flag, SETTINGS.safe_strategy.worm_protection_time);
		} else {
			$('#wormConfig').wormProtectionBackup(getWormType(),'col-md-3');
		}
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
		if (!result) {
			return false;
		}
		var result = getSpeedStr() & getReserveStr() & getAchiveStr() & getTransferStr() & getStoreStr() & getSafeStr() &  getHighStr() & getRetryStr() & proxystate();
		if (result) {
			result = result && showStep3();
		}
		return result;
	}

	//设置默认时间策略
	var setStrategyTime =  function(){
		//默认设置为隐藏
		$('.verifyDiv').hide();
		$(".storeDiv").show();
		$('.diff').show();
		$('.perIncrementTips').show();
		$('.icsDiv').hide();
		$('.cbrDiv').hide();
		$('.fullincrDiv').hide();
		$(".deduplicationdiv").show();
		$("#backupHigh .parsefsDiv").show();
		$("#backupHigh .SnapshotDiv").show();
		if (data.advanced_strategy.mode.snapshot_type == 1) {
			// 快照模式为串行才显示提前创建快照
			$("#backupHigh .preSnapshotDiv").show();
		} else {
			$("#backupHigh .preSnapshotDiv").hide();
		}
		if(1 == CONF.SOFTWARE || 4 == CONF.SOFTWARE){
			$('.parsefsDiv').hide();
		}
		if (CONF.BD_STORAGE_TYPE.CLOUD == data.advanced_strategy.node.storage_type) {
			//备份到云存储不支持永久增量
			$("#strategymode > label:last").hide();
			//修改时间策略后的提示信息
			$('.perIncrementTips').hide();
		}
	}

	var proxystate = function(){
		var applianceSelect = $("#applianceSelect").val();
		if(applianceSelect==null || applianceSelect=="" || applianceSelect=='undefined'){
			UIToastr.showWarning(LANG.UI_APPLIANCE_SELECT, LANG.UI_APPLIANCE_SELECT_NO_TIPS);
			return false;
		}else{
			return true;
		}
	}

	var getTimeStr = function(){
		var strategyConfig = $('#backupTimestrategy').getStrategyConfig();
		data.time_strategy.type = $('#backuptype').val();
		var strategyMode = $('#strategymode').find('input:checked');
		if('strategy' == data.time_strategy.type){
			if(0 == strategyMode.length){
				//没有选择时间策略
				if (data.advanced_strategy.node.storage_type == 12 || data.advanced_strategy.node.storage_type == 10) {
					UIToastr.showInfo(LANG.UI_GLOBAL_STRATEGY_TIME, LANG.UI_VM_BACKUP_SET_STRATEGY_TIPS);
				} else {
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
		}else if('oncetime' == data.time_strategy.type){
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
		var backupTypeshow = $('.backupTypeshow'), backupTypeinfoshow = $('.backupTypeinfoshow');
		var backupTypeshowStr = '', backupTypeinfoshowStr = '';
		if('strategy' == data.time_strategy.type){
			backupTypeshowStr = LANG.UI_BACKUP_USE_STRATEGY;
			for(var i=0; i<strategyMode.length; i++){
				if(1 == $(strategyMode[i]).data('mode')){
					backupTypeinfoshowStr += data.time_strategy.full_info.des + "<br>";
				}else if(2 == $(strategyMode[i]).data('mode')){
					backupTypeinfoshowStr += data.time_strategy.incr_info.des;
				}else if(3 == $(strategyMode[i]).data('mode')){
					backupTypeinfoshowStr += data.time_strategy.diff_info.des;
				}else if(9 == $(strategyMode[i]).data('mode') && !cloudTargetFlag){
					backupTypeinfoshowStr += data.time_strategy.pincr_info.des;
				}
			}
		}else if('oncetime' == data.time_strategy.type){
			backupTypeshowStr = LANG.UI_BACKUP_ONCE;
			backupTypeinfoshowStr = LANG.UI_PUBLIC_START_TIME + ": " + data.time_strategy.datetime;
		} else if ('manual' === data.time_strategy.type) {
			backupTypeshowStr = LANG.UI_BACKUP_MANUAL;
			$('.backupTypeInfoDiv').hide();
		}
		backupTypeshow.html(backupTypeshowStr);
		backupTypeinfoshow.html(backupTypeinfoshowStr);

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

		var reserveTypeshow = $('.reserveTypeshow');
		var reserveTypeStr = '';
		// 保留类型
		if(CONF.RESERVE_STRATEGY_MODE.POINT == data.reserved_strategy.strategyMode){
			reserveTypeStr = LANG.UI_RESERVE_RETENTION_TYPE + ': ' + LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_POINT + '<br>';
		}else if(CONF.RESERVE_STRATEGY_MODE.CHIAN == data.reserved_strategy.strategyMode){
			reserveTypeStr = LANG.UI_RESERVE_RETENTION_TYPE + ': ' + LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_CHAIN + '<br>';
		}
		if(CONF.RESERVE_TYPE.NUM == data.reserved_strategy.reserved_type){
			reserveTypeStr += LANG.UI_RESERVE_RETENTION_MODE + ': ' + LANG.UI_BACKUP_NUM + '<br>';
		}else if(CONF.RESERVE_TYPE.DAY == data.reserved_strategy.reserved_type){
			reserveTypeStr += LANG.UI_RESERVE_RETENTION_MODE + ': ' + LANG.UI_BACKUP_DAY + '<br>';
		}
		reserveTypeStr += LANG.UI_STRATEGY_RESERVE_VALUE + ': ' + data.reserved_strategy.value;
		// //确认页面GFS描述
		// var GFSstr = $(".GFSLable").html();
		// reserveTypeStr +="</br>"+ GFSstr + ': ' + getSwitchDes($('#GFSflag').get(0).checked);
		// if($('#GFSflag').get(0).checked){
		// 	reserveTypeStr +="</br>"+ LANG.UI_BACKUP_AWS_GFS_CONFIG + ":" + $("#GFSDiv").getGFSDes();
		// }



		//保留策略
		if($("#selectstorage option:selected").attr("data-type") != 10) {
			//不是磁带，显示以前的保留策略
			reserveTypeshow.html(reserveTypeStr);
		}

		//存储策略
		var deduplicationLabel = $('.deduplicationLabel').html();
		var compressLabel = $('.compressLabel').html();
		var storeInfo = "";
		if(CONF.FUNCTIONS.includes('dedupication') && 1 != CONF.SOFTWARE && 4 != CONF.SOFTWARE){
			storeInfo += deduplicationLabel + ": " + getSwitchDes(data.storage_strategy.deduplication_flag) + "<br>";
		}
		storeInfo += compressLabel + ": " + getSwitchDes(data.storage_strategy.compress_flag);
		// 压缩等级
		if(data.storage_strategy.compress_method){
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
			storeInfo += "<br>" + LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE + ": " + grade;
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
				safeInfo += LANG.UI_VM_INTEGRITY_VERIFY + ": ";
				safeInfo += $('#completeConfig').getCompleteDetectionBackup().str;
			} else {
				safeInfo += LANG.UI_VM_INTEGRITY_VERIFY + ": " + LANG.UI_GLOBAL_STRATEGY_EMPTY; // 未配置
			}
		}
		$('.safeStrategyShow').html(safeInfo);

		//高速模式
		var backupmode1Info = $('.snapshotlabel').html() + ": " + getSwitchDes(data.advanced_strategy.mode.snapshot_check);
		// 保留快照
		var keepsnapshotInfo = $('.keepsnapshotlabel').html() + ": " + getSwitchDes(data.advanced_strategy.mode.keep_snapshot_check);
		// 快照超时时间
		var snapshotTimeoutInfo = $('.snapshotTimeoutLabel').html() + ": " + data.advanced_strategy.mode.snapshot_timeout + LANG.UI_PUBLIC_SECOND;

		//内联数据消重
		var parsefsmodeInfo = $('.parsefslabel').html() + ": " + getSwitchDes(data.advanced_strategy.mode.parse_fs_flag);
		//排除交换文件块
		var swapmodeInfo = $('.swaplabel').html() + ": " + getSwitchDes(data.advanced_strategy.mode.swap_flag);
		//排除分区间隙
		var gapmodeInfo = $('.gaplabel').html() + ": " + getSwitchDes(data.advanced_strategy.mode.gap_flag);
		//线程数量
		var threadnumInfo = $('.threadnumlabel').html() + ": " + $('#backupThreadNum').val();

		//快照模式
		var snapshotmodeInfo = $('.snapshotTypelabel').html() + ": " + $('#snapshottype').find("option:selected").text();
		//提前创建虚拟机快照
		var presnapshotInfo = $('.preSnapshotlabel').html() + ": " + getSwitchDes(data.advanced_strategy.mode.pre_snapshot_flag);
		// 数据块大小
		var blocksizeInfo = $('.blocksizelabel').html() + ": " + $('#blocksize option:selected').text();
		// 数据文件大小
		var datacontainersizeInfo = $('.datacontainersizelabel').html() + ": " + $('#data_container_size option:selected').text();
		// 合并冗余数据比例
		var mergemodeInfo = $('.mergemodelabel').html() + ": " + $(`#redundant_data_proportion option:selected`).text();
		// 忽略资源限制
		var ignoreresourcelimitInfo = $('.ignoreResourceLimitLabel').html() + ": " + getSwitchDes(data.advanced_strategy.mode.ignore_resource_limiting_flag);

		// 重连次数
		var retryInfo = '';
		if(parseInt($('#reconnect_time').val()) == 0){
			retryInfo += $('.reconnect-times-Label').text() + ": " + LANG.UI_GLOBAL_STRATEGY_RECONNECT_TIMES_INFINITE +"<br>";
		}else{
			retryInfo += $('.reconnect-times-Label').text() + ": " + $('#reconnect_time').val() + LANG.UI_VOL_CDP_BACKUP_TIMES + "<br>";
		}
		// 重连时间间隔
		retryInfo += $('.reconnect-Interval-Label').text() + ": " + $('#reconnect_interval').val() + LANG.UI_PUBLIC_SECOND + " <br>";

		$('.presnapshotshow').html(presnapshotInfo);
		if($('#snapshottype').val() == 2){
			$('.presnapshotshow').hide();
		}else{
			$('.presnapshotshow').show();
		}

		// 高速模式/保留快照
		switch (data.hypervisor_type) {
			case CONF.VM_TYPE.AWS:
				$('.backupmodeshow1').html(backupmode1Info);
				$('.snapshottimeoutshow').html(snapshotTimeoutInfo);
				break;
			case CONF.VM_TYPE.HUAWEICLOUD:
				$('.backupmodeshow1').html(keepsnapshotInfo);
				break;
			default:
				break;
		}
		$('.parsefsmodeshow').html(parsefsmodeInfo);
		$('.swapmodeshow').html(swapmodeInfo);
		$('.gapmodeshow').html(gapmodeInfo);
		$('.snapshotmodeshow').html(snapshotmodeInfo);

		$('.threadnumshow').html(threadnumInfo);
		$('.retryshow').html(retryInfo);
		$('.blocksizeshow').html(blocksizeInfo);
		$('.datacontainersizeshow').html(datacontainersizeInfo);
		$('.mergemodeshow').html(mergemodeInfo);
		$('.ignoreresourcelimitshow').html(ignoreresourcelimitInfo);

		var des = "";
		data.transport_strategy.mode = $('#transport_mode').val();
		//传输模式
		// var transferlabel = $('.transferlabel').html();
		// des += transferlabel + ": " + $('#transport_mode').find("option:selected").text();
		// 加密传输开关
		var encryptlabel = $('.encrypttransferlabel').html();
		des += encryptlabel + ": " + getSwitchDes(data.transport_strategy.encrypt);
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
			;
			des += "<br>" + encryptedMethodLabel + ": " + grade;
		}

		//数据加密配置描述
		var encryptStorageLabel = $('.encryptStorageLabel').html();
		var passwordAutoLabel = $('.passwordAutoLabel').html();

		storeInfo += "<br>" + encryptStorageLabel + ": " + getSwitchDes(data.storage_strategy.encrypt_flag);
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
			storeInfo += "<br>" + encryptedMethodLabel + ": " + grade;
		}
		if(data.storage_strategy.encrypt_flag){
			storeInfo += "<br>" + passwordAutoLabel + ": " + getSwitchDes(data.storage_strategy.auto_create_password_flag);
		}

		//传输策略信息展示
		$('.transportinfoshow').html(des);
		var applianceselectlabel = $('.applianceselectlabel').html();
		var applianceName = $('#applianceSelect').find('option:selected').text();
		var applianceInfo = applianceselectlabel + ": " + applianceName;
		$('.applianceshow').html(applianceInfo);
		$('.storageinfoshow').html(storeInfo);

		return true;
	}

	// 初始化传输代理网络信息
	var initAgentNetwork = function () {
		regionUuids = [];
		regions = [];
		regionsFlag = [];
		let allNodes = currentTree.getCheckedNodes();
		if (allNodes.length === 1 && allNodes[0].id === allNodes[0].platform_uuid) {
			// 选中了平台需特殊处理
			$.each(allNodes[0].regions, function (i, v) {
				if ($.inArray(v.host_uuid, regionUuids) < 0) {
					regionUuids.push(v.host_uuid);
					regions.push(v.host_name);
					regionsFlag[v.host_uuid] = false;
				}
			});
		} else {
			$.each(allNodes, function (i, v) {
				if ($.inArray(v.hostuuid, regionUuids) < 0) {
					regionUuids.push(v.hostuuid);
					regions.push(v.hostName);
					regionsFlag[v.hostuuid] = false;
				}
			});
		}

		$('.applianceNetwork').empty();
		for (let i = 0; i < regionUuids.length; i++) {
			let html = `
				<div class="form-group">
					<label class="control-label col-md-3 applianceVpcLabel">${LANG.UI_BACKUP_AWS_APPLIANCE_VPC}(${regions[i]})</label>
					<div class="col-md-4">
						<div class="applianceNetworkDiv_${regionUuids[i]} applianceVpcDiv_${regionUuids[i]}">
							<select class="form-control select2me applianceVpc" id="applianceVpc_${regionUuids[i]}" data-regionuuid="${regionUuids[i]}"></select>
						</div>
					</div>
				</div>
				<div class="form-group applianceSgDiv">
					<label class="control-label col-md-3 applianceSgLabel">${LANG.UI_BACKUP_AWS_APPLIANCE_SG}(${regions[i]})</label>
					<div class="col-md-4">
						<div class="applianceNetworkDiv_${regionUuids[i]}">
							<select class="form-control select2me applianceSg" id="applianceSg_${regionUuids[i]}" data-regionuuid="${regionUuids[i]}"></select>
						</div>
					</div>
				</div>
			`;

			$('.applianceNetwork').append(html);
			$(`#applianceVpc_${regionUuids[i]}`).append(`<option value="">${LANG.UI_BACKUP_AWS_AUTO_SELECT}</option>`);
			if (_ModifyFlag && SETTINGS.bts.agent_network[regionUuids[i]] && SETTINGS.bts.agent_network[regionUuids[i]].vpc_id) {
				let vpc = SETTINGS.bts.agent_network[regionUuids[i]].vpc_id;
				$(`#applianceVpc_${regionUuids[i]}`).append(`<option value="${vpc}" selected>${vpc}</option>`);
			}
			$(`#applianceSg_${regionUuids[i]}`).append(`<option value="">${LANG.UI_BACKUP_AWS_AUTO_SELECT}</option>`);
			if (_ModifyFlag && SETTINGS.bts.agent_network[regionUuids[i]] && SETTINGS.bts.agent_network[regionUuids[i]].security_id
				&& 'aws_agent_security' !== SETTINGS.bts.agent_network[regionUuids[i]].security_id
			) {
				// aws_agent_security 为aws传输代理安全组自动选择标识，需排除掉使用默认的空值
				let sg = SETTINGS.bts.agent_network[regionUuids[i]].security_id;
				$(`#applianceSg_${regionUuids[i]}`).append(`<option value="${sg}" selected>${sg}</option>`);
			}
		}

		// 点击传输代理vpc获取网络配置
		$('.applianceVpc').on('click', function () {
			loadAgentNetwork($(this).attr('data-regionuuid'), _AS.val());
		});

		// 切换传输代理vpc重新加载安全组
		$('.applianceVpc').on('change', function () {
			switch (data.hypervisor_type) {
				case CONF.VM_TYPE.AWS:
					agentNetworkHandler($(this).attr('data-regionuuid'), 2, agentNetworkList, this.value);
			}
		});

		// 点击传输代理安全组获取网络配置
		$('.applianceSg').on('click', function () {
			switch (data.hypervisor_type) {
				case CONF.VM_TYPE.HUAWEICLOUD:
					loadAgentNetwork($(this).attr('data-regionuuid'), _AS.val());
			}
		});
	}

	// 加载传输代理网络信息
	var loadAgentNetwork = function (region_uuid, instance_type = '') {
		if (currentAgentInstanceType == instance_type && regionsFlag[region_uuid]) return; // 只加载一次
		if (!instance_type) {
			UIToastr.showWarning(LANG.UI_BACKUP_AWS_PLEASE_SELECT_APPLIANCE);
			return;
		}
		currentAgentInstanceType = instance_type;
		regionsFlag[region_uuid] = true;
		let allNodes = currentTree.getCheckedNodes();
		let vpc_uuid = _ModifyFlag ? SETTINGS.bts.agent_network.vpc_id : '';
		let sg_name = _ModifyFlag ? SETTINGS.bts.agent_network.security_id : '';
		let p = {
			platform_uuid: allNodes[0].vcenteruuid,
			region_uuid: region_uuid,
			instance_type: instance_type
		};

		let blockUITarget;
		switch (data.hypervisor_type) {
			case CONF.VM_TYPE.AWS:
				blockUITarget = `.applianceVpcDiv_${region_uuid}`;
				break;
			case CONF.VM_TYPE.HUAWEICLOUD:
				blockUITarget = `.applianceNetworkDiv_${region_uuid}`;
				break;
		}
		Metronic.blockUI({target: blockUITarget,animate: true});
		pAjaxRequest(p, "/api/v1/cloud/instances/network_configs", "GET", function (d) {
			Metronic.unblockUI(blockUITarget);
			if (d.success) {
				agentNetworkList = d.data.network_list;
				switch (data.hypervisor_type) {
					case CONF.VM_TYPE.AWS:
						agentNetworkHandler(region_uuid, 1, agentNetworkList, vpc_uuid, sg_name);
						break;
					case CONF.VM_TYPE.HUAWEICLOUD:
						// 华为云vpc和安全组无绑定关系，取第一个vpc的
						agentNetworkHandler(region_uuid, 3, agentNetworkList, agentNetworkList[0].network_vpc, sg_name);
						break;
				}
			} else {
				operateResponseList(d);
			}
		});
	}

	// reloadType:1-仅加载vpc，2-仅加载安全组，3-都加载
	var agentNetworkHandler = function (region_uuid, reloadType, networkList, vpc_uuid = '', sg_name = '') {
		let network = null; // 当前网络

		// vpc
		if (1 == reloadType || 3 == reloadType) {
			$(`#applianceVpc_${region_uuid}`).empty();
			$(`#applianceVpc_${region_uuid}`).append(`<option value="">${LANG.UI_BACKUP_AWS_AUTO_SELECT}</option>`);
			$.each(networkList, function (i, v) {
				let selected = '';
				if (_ModifyFlag && sg_name && v.network_vpc == vpc_uuid) {
					selected = 'selected';
				}
				let option;
				if (v.network_vpc_name) {
					option = `<option value="${v.network_vpc}" ${selected}>${v.network_vpc_name}(${v.network_vpc})</option>`;
				} else {
					option = `<option value="${v.network_vpc}" ${selected}>${v.network_vpc}</option>`;
				}
				$(`#applianceVpc_${region_uuid}`).append(option);
				if (vpc_uuid == v.network_vpc) {
					network = v;
				}
			});
		}
		if (2 == reloadType || 3 == reloadType) {
			$.each(networkList, function (i, v) {
				if (vpc_uuid == v.network_vpc) {
					network = v;
				}
			});

			// 安全组
			$(`#applianceSg_${region_uuid}`).empty();
			$(`#applianceSg_${region_uuid}`).append(`<option value="">${LANG.UI_BACKUP_AWS_AUTO_SELECT}</option>`);
			if (network) {
				$.each(network.network_security, function (i, v) {
					let selected = '';
					if (_ModifyFlag && sg_name && v.network_security_name == sg_name) {
						selected = 'selected';
					}
					let sgValue = '';
					switch (data.hypervisor_type) {
						case CONF.VM_TYPE.AWS:
							sgValue = v.network_security_name;
							break;
						case CONF.VM_TYPE.HUAWEICLOUD:
							sgValue = v.network_security_id;
							break;
					}
					let option = `<option value="${sgValue}" ${selected}>${v.network_security_name}</option>`;
					$(`#applianceSg_${region_uuid}`).append(option);
				});
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
			data.reserved_strategy.value = parseInt($('#spinnerNumInput').val());
		}else if(CONF.RESERVE_TYPE.DAY == data.reserved_strategy.reserved_type){
			data.reserved_strategy.value = parseInt($('#spinnerDayInput').val());
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
			if(_ModifyFlag && oldGFSDes != newGFSDes){
				data.reserved_strategy.gfs_modify_flag = true;
				UIToastr.showInfo(LANG.UI_SETTING_GFS_RETENTION_POLICY_EDIT, LANG.UI_SETTING_GFS_RETENTION_POLICY_EDIT_TIPS);
			}
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
		// 传输模式，固定为网络传输（nbd）
		data.transport_strategy.mode = $('#transport_mode').val();
		// 传输代理实例类型
		data.transport_strategy.appliance_uuid = $('#applianceSelect').find('option:selected').val();
		// 传输代理网络配置
		data.transport_strategy.agent_network = [];
		$.each(regionUuids, function (i, v) {
			let networkItem = {};
			// 传输代理vpc
			networkItem.vpc_id = $(`#applianceVpc_${v}`).find('option:selected').val();
			// 传输代理安全组
			networkItem.security_id = $(`#applianceSg_${v}`).find('option:selected').val();
			// 安全组不是自动选择且vpc是自动选择时需任意传一个vpc，华为云因独立选择vpc和安全组会有此情况
			if ('' !== networkItem.security_id && '' === networkItem.vpc_id) {
				networkItem.vpc_id = $(`#applianceVpc_${v}`).find('option').eq(1).val();
			}
			// 传输代理所在区域uuid
			networkItem.region_uuid = v;
			// 传输代理所在区域
			networkItem.region = regions[i];
			// 传输代理网络设置模式
			networkItem.set_mode = 'default';
			if (networkItem.vpc_id || networkItem.security_id) {
				networkItem.set_mode = 'modify';
			}
			if (CONF.VM_TYPE.AWS == data.hypervisor_type && !networkItem.security_id) {
				// aws安全组为自动选择时传"aws_agent_security"
				networkItem.security_id = 'aws_agent_security';
			}
			data.transport_strategy.agent_network.push(networkItem);
		});
		// 传输代理公有ip
		data.transport_strategy.agent_public_ip = {
			ip_uuid: $('select[name=agent_public_ip]').val(),
			ip_address: $('select[name=agent_public_ip] option:selected').attr('data-address'),
			chargemode: $('select[name=agent_billing_type]').val(),
			size: parseInt($('input[name=agent_billing_value]').val())
		}
		if (CONF.VM_TYPE.HUAWEICLOUD == data.hypervisor_type) {
			// 华为云公有ip计费信息
			if (BILLING_BANDWIDTH === _AgentBillingType.val() && _AgentBillingValue.val() > 2000) {
				UIToastr.showWarning(LANG.UI_RECOVERY_CLOUD_BANDWIDTH_SIZE, LANG.UI_RECOVERY_CLOUD_BILLING_BANDWIDTH_TIPS);
				return false;
			}
			if (BILLING_TRAFFIC === _AgentBillingType.val() && _AgentBillingValue.val() > 300) {
				UIToastr.showWarning(LANG.UI_RECOVERY_CLOUD_BANDWIDTH_SIZE, LANG.UI_RECOVERY_CLOUD_BILLING_TRAFFIC_TIPS);
				return false;
			}
			if (!_AgentBillingValue.val()  || _AgentBillingValue.val() == 0) {
				UIToastr.showWarning(LANG.UI_RECOVERY_CLOUD_BANDWIDTH_SIZE, LANG.UI_RECOVERY_CLOUD_BANDWIDTH_SIZE_EMPTY_TIPS);
				return false;
			}
		}

		// 传输加密开关
		data.transport_strategy.encrypt = $('#encrypttransfer').get(0).checked;
		// 传输加密算法
		data.transport_strategy.encrypt_method = parseInt($('#transferEncryptMethod').val());
		return true;
	}
	//得到存储策略
	var getStoreStr = function(){
		data.storage_strategy.compress_method = 0;
		data.storage_strategy.deduplication_flag = $('#deduplicationCheck').get(0).checked;
		data.storage_strategy.block_size = parseInt($('#blocksize').val());
		data.storage_strategy.compress_flag = $('#compressCheck').get(0).checked;
		// 压缩等级
		if($('#compressCheck').get(0).checked){
			data.storage_strategy.compress_method = parseInt($('#compressGrade').val());
		}
		data.storage_strategy.encrypt_flag = $('#encryptStorageCheck').get(0).checked;
		// 存储加密
		data.storage_strategy.encrypt_method = parseInt($('#storageEncryptMethod').val());
		data.storage_strategy.auto_create_password_flag = $('#passwordAutocheck').get(0).checked;
		// 非法字符串校验
		if (!isNotLatinCode(checkpassword())) {
			return false;
		}
		//得到密码
		data.storage_strategy.password = btoa(checkpassword());
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
	var checkpassword = function(){
		var now_password = $.trim($('#password').val());
		// 备份策略已配置密码时
		if(_oldPassword != ''){
			// 若密码输入框未触发过change事件,现密码和原配置的密码_oldPassword必然相等,则返回解码后的密码
			if (!passwordChangeFlag) {
				return atob(now_password);
			} else { // 若密码输入框触发过change事件,判断现密码和原配置的密码_oldPassword是否相等,相等则返回解码后的密码;不相等则返回现密码
				if (now_password === _oldPassword) {
					return atob(now_password);
				} else {
					return now_password;
				}
			}
		} else {
			// 未应用备份策略且未触发过密码输入框的change事件,则return之前接口返回的密码
			if (!passwordChangeFlag && _ModifyFlag) {
				return atob(SETTINGS.bss.password);
			} else {
				// 触发过密码输入框的change事件则return密码输入框本身的值
				return now_password;
			}
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
			$('#strategymode').find('input[data-mode=9]').iCheck('uncheck');
			$('#stragegyaccordion').find('.strategy-panel[data-mode=9]').hide();
			initTimeStrategyDes();
			if(_ModifyFlag){
				$('#tab_common').backupStrategy(CONF.MODULE_TYPE.PUBLIC_CLOUD, true, backupStrategy, 'cloud');
			}else{
				$('#tab_common').backupStrategy(CONF.MODULE_TYPE.PUBLIC_CLOUD, false, {}, 'cloud');
			}
		}else{
			cloudTargetFlag = false;
			$('.pIncr').show();
			$('#reserveMode').val(1).removeAttr('disabled');
			if(_ModifyFlag){
				$('#tab_common').backupStrategy(CONF.MODULE_TYPE.PUBLIC_CLOUD, true, backupStrategy);
			}
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
		// 云存储任务不允许修改备份方式
		// if(originCloudFlag){
		// 	$('#reserveMode').val(2).prop('disabled', 'true');
		// }
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
		data.advanced_strategy.mode.snapshot_check = $('#snapshotcheck').get(0).checked;
		data.advanced_strategy.mode.keep_snapshot_check = $('#keepsnapshotcheck').get(0).checked;
		data.advanced_strategy.mode.snapshot_timeout = parseInt($('#snapshotTimeout').val());
		data.advanced_strategy.mode.parse_fs_flag = $('#parsefscheck').get(0).checked;
		data.advanced_strategy.mode.swap_flag = $('#swapcheck').get(0).checked;
		data.advanced_strategy.mode.gap_flag = $('#gapcheck').get(0).checked;
		data.advanced_strategy.mode.snapshot_type = parseInt($('#snapshottype').val());
		data.advanced_strategy.mode.thread_num = parseInt($('#backupThreadNum').val());
		data.advanced_strategy.mode.pre_snapshot_flag = $('#preSnapshotcheck').get(0).checked;
		data.advanced_strategy.mode.ignore_resource_limiting_flag = $('#ignore_resource_limit').get(0).checked;
		var thread = parseInt($('#backupThreadNum').val());
		if(!thread || thread > 8 || thread <= 0){
			//重置为默认值
			$('.backupThreadDiv').spinner("value", 3);
			initHighStrategyDes();
			UIToastr.showWarning(LANG.UI_BACKUP_HIGH_SETTING_TITLE, LANG.UI_BACKUP_THREAD_NUM_TIPS);
			return false;
		}
		return true;
	}

	// 得到重试策略
	var getRetryStr = function () {
		data.retry_strategy = $('#retry_config').retryStrategy({} ,'value');
		return false !== data.retry_strategy;
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
			//把最大步骤存入内存中 用于判断提交的按钮显示
			if(current >= currentmax){
				currentmax = current;
			}
			// set wizard title
//            $('.step-title', $('#awsbackupcontent')).text('Step ' + (index + 1) + ' of ' + total);
			// set done steps
			jQuery('li', $('#awsbackupcontent')).removeClass("done");
			var li_list = navigation.find('li');
			for (var i = 0; i < index; i++) {
				jQuery(li_list[i]).addClass("done");
			}

			//如果第一步 上一步按钮隐藏
			if (current == 1) {
				$('#awsbackupcontent').find('.button-previous').css('visibility', 'hidden');
				$('#awsbackupcontent').find('.button-next').addClass('next-btn-margin-left');
			} else {
				$('#awsbackupcontent').find('.button-previous').css('visibility', 'visible');
				$('#awsbackupcontent').find('.button-next').removeClass('next-btn-margin-left');
			}

			//如果是最后一步
			if (current >= total) {
				$('#awsbackupcontent').find('.button-next').hide();
				$('#awsbackupcontent').find('.button-submit').css('visibility', 'visible');
			} else {
				$('#awsbackupcontent').find('.button-next').show();
				$('#awsbackupcontent').find('.button-submit').css('visibility', 'hidden');
			}

			Metronic.scrollTo($('.page-title'));
		}

		// default form wizard
		$('#awsbackupcontent').bootstrapWizard({
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
						pageIndex = 1;
						break;
					case 2:
						if(step2Valid() == false){
							return false;
						}
						pageIndex = 2;
						break;
					case 3:
						if(step3Valid() == false){
							return false;
						}
						pageIndex = 3;
						break;
				}
				handleTitle(tab, navigation, index);
			},

			//上一步
			onPrevious: function (tab, navigation, index) {
				pageIndex = index;
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
				$('#awsbackupcontent').find('.progress-bar').css({
					width: $percent + '%'
				});
			}
		});

		$('#awsbackupcontent').find('.button-previous').css('visibility', 'hidden');
		$('#awsbackupcontent .button-submit').click(debounce(submit, 200)).css('visibility', 'hidden');
	};

	//提交
	var submit = function(){
		if (submittedFlag) {
			return; // 防止重复提交
		}

		if (_ModifyFlag) {
			if(pageIndex == 0){
				var step1 = step1Valid();
				if(!step1) return false;
			}else if(pageIndex == 1){
				var step2 = step2Valid();
				if(!step2) return false;
			}else if(pageIndex == 2){
				var step3 = step3Valid();
				if(!step3) return false;
			}
			// 存储类型变化则清空缓存路径
			data.emptyCachePath = SETTINGS.node.storage_type != data.advanced_strategy.node.storage_type;
		}

		var thread = $('#backupThreadNum').val();
		if(thread == "" || thread > 8 || thread <= 0){
			UIToastr.showWarning(LANG.UI_BACKUP_HIGH_SETTING_TITLE, LANG.UI_BACKUP_THREAD_NUM_TIPS);
			return false;
		}
		let jobName = $.trim($("#jobname").val());
		if('' == jobName){
			$('.jobnametip').html(LANG.UI_BACKUP_NAME_TIPS).show();
			return;
		}
		$('.jobnametip').hide();
		// 输入验证
		if(!customInputValidate('string',jobName)){
			return false;
		}
		data.job_name = jobName;
		data.strategy_group_uuid = $('#strategySelect option:selected').val();
		data.reserved_strategy.strategyMode = parseInt($('#reserveMode').val());
		if (null === data.time_strategy.datetime) {
			data.time_strategy.datetime = '';
		}
		getSpeedStr();
		submittedFlag = true;
		Metronic.blockUI({target: '#awsbackupcontent', animate: true, cenrerY: true});
		let httpType = _ModifyFlag ? 'PUT' : 'POST';
		pAjaxRequest(data, "/api/v1/cloud/jobs/backup", httpType, function (d) {
			Metronic.unblockUI('#awsbackupcontent');
			operateResponseList(d);
			if (d.success) {
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
			$('.VMList-div').hide();
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
		//var nodes = JSON.parse(zNodes);//拿到备份节点
		var nodes = zNodes;
		currentTree = $.fn.zTree.init($("#vm_tree_hc"), setting, nodes);//第一种
		for(var i=0;i<nodes.length;i++){
			if(nodes[i].checked){
				var checkNode = currentTree.getNodesByParam("id", nodes[i].id, null);
				addVMList('vm_tree_hc',checkNode[0]);
				//初始化对应虚拟化平台管理的备份节点
				if(oldVcenter != nodes[i].vcenteruuid){
					oldVcenter = nodes[i].vcenteruuid;
					initAllocationNodes();
				}
			}
		}
		$('#awsbackupcontent').find('.button-next').css('visibility', 'visible');
		$('#awsbackupcontent').find('.button-submit').css('visibility', 'hidden');
		showSearchInput();
	};

	//添加虚拟化中心鼠标指上去事件
	var addHoverDom = function(treeId, treeNode) {
		if (!speedFlag && treeNode.vcenteruuid && treeNode.id != treeNode.vcenteruuid) return;
		if (speedFlag && treeNode.vcenteruuid && treeNode.uuid != treeNode.vcenteruuid) return; //快速添加时id不适用
		if(1 != treeNode.type) return;
		var nodeID = escapeJquery(treeNode.id);
		var nodeTID = escapeJquery(treeNode.tId);
		var aObj = $("#" + nodeTID + "_a");
		if ($("#diyHref_" + nodeTID + nodeID + "_1").length>0) return;
		var str =  '';
		if (CONF.PERMISSION_ARR.includes('p_cloud_platform_manager_sync')) {
			str += '<a id="diyHref_' + treeNode.tId + treeNode.id + '_1" title="' + LANG.UI_VCENTER_SYNC + '" class="treehref">' + LANG.UI_VCENTER_SYNC + '</a>';
		}
		str += '<a id="diyHref_' + treeNode.tId + treeNode.id + '_2" title="' + LANG.UI_BACKUP_TREE_EXPAND_ALL_DES + '" class="treehref">' + LANG.UI_BACKUP_TREE_EXPAND_ALL + '</a>' +
			'<a id="diyHref_' + treeNode.tId + treeNode.id + '_3" title="' + LANG.UI_BACKUP_TREE_COLLAPSE_ALL_DES + '" class="treehref">' + LANG.UI_BACKUP_TREE_COLLAPSE_ALL + '</a>';
		aObj.after(str);
		var hrefRefresh = $("#diyHref_" + nodeTID + nodeID + "_1");
		var hrefExpand = $("#diyHref_" + nodeTID + nodeID + "_2");
		var hrefCollapse = $("#diyHref_" + nodeTID + nodeID + "_3");

		if (hrefRefresh) hrefRefresh.bind("click", function(){
			getSyncVcenterInfo(treeId, treeNode, true, _ModifyFlag);
		});
		if (hrefExpand) hrefExpand.bind("click", function(){
			if(treeNode.children){
				$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true, true, true);
			}else{
				getSyncVcenterInfo(treeId, treeNode, false, true);
			}
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
		var css = {color:"#666666", "font-weight":"normal"};
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
				UIToastr.showInfo(LANG.UI_BACKUP_AWS_DIFF_PLATFORM, LANG.UI_BACKUP_AWS_DIFF_PLATFORM_TIPS);

				//重置备份对象列表
				backupObjList = {};

				$('#addHCList li').remove();
				$('.diskList').remove();

				addVMList(id,node);
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
		if ('vm' === node.eventtype && !delVmTipsShowFlag && _ModifyFlag) {
			UIToastr.showWarning(LANG.UI_BACKUP_AWS_REMOVE_INSTANCE, LANG.UI_BACKUP_AWS_REMOVE_INSTANCE_TIPS);
			delVmTipsShowFlag = true;
		}
		var objId = getObjId(node);
		var liId = id + node.vcenteruuid + node.id;
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
			'<li class="list-group-item popovers VMTips list-group-item__vm ' + listType + '" id="' + liId + '" data-vmuuid="' + vmUuid + '" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="' + decodeURIComponent(node.path_show ?? node.path) + '">' +
			'<a href="#disk' + liId + '" data-toggle="collapse" aria-expanded="false" id="disHref' + liId + '" style="display:flex;" class="accordion-toggle accordion-toggle-styled collapsed">' +
			'<div class="col1">' +
			'<div class="cont vmDetail">' +
			'<div class="cont-col1" style="padding-top: 7px;padding-left: 2px;">' +
			'<div class="' + node.iconSkin + '" style="height: 16px;width: 16px;background-repeat: round"></div>' +
			'</div>' +
			'<div class="cont-col2">' +
			'<div class="desc list-one">' + node.name + `<span class="count-vm" style="margin-left: 8px;color: #999999;font-size: 12px"></span></div>` +
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
	var getVmNodeList = function(node){
		var list = [];
		var children = node.children;
		if (!children || children.length === 0) {
			return list;
		}
		for (var i = 0; i < children.length; i++) {
			if (children[i].eventtype === "vm" && !children[i].chkDisabled) {
				list.push(children[i]);
			} else if (children[i].eventtype === "") {
				list = list.concat(getVmNodeList(children[i]));
			}
		}
		return list;
	}

	//勾选对象操作
	var checkObjHandle = function(el) {
		var thisChecked = $(el).closest('div').find('.objCheck').prop('checked');
		$(el).closest('div').find('.objCheck').attr('data-checkinit', false);
		if ('' == $(el).closest('li').next('ul').html()) {
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
				checkVmHandle(v);
			});
		}, 100);

	}

	//勾选对象下虚拟机操作
	var checkVmHandle = function(el) {
		var thisChecked = $(el).parents('.disk-checkbox').find('.vmCheck').prop('checked');
		var thisTextEl = $(el).closest('li').find('.vmName');
		let objId = $(el).closest('li').data('parentobjid');
		var vm_uuid = $(el).parents('.disk-checkbox').find('.vmCheck').attr('data-vmuuid');
		var vm_path = $(el).closest('li').data('content');
		var parentCheck = $(el).closest('ul').prev('li').find('.objCheck');

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
				parentCheck.iCheck('check');
			}
		} else {
			//取消勾选：加入上级对象虚拟机排除列表
			if (!delVmTipsShowFlag && _ModifyFlag) {
				UIToastr.showWarning(LANG.UI_BACKUP_AWS_REMOVE_INSTANCE, LANG.UI_BACKUP_AWS_REMOVE_INSTANCE_TIPS);
				delVmTipsShowFlag = true;
			}
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
			parentCheck.iCheck('uncheck'); //上级取消全选
		}
		updateObjVMCount(el);
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
		}
	}

	// 更新已选vm总数，单选+粒度下勾选的，需根据instance_uuid去重
	const updateTotalVMCount = function () {
		let uuids = [];
		$(`.addVMList`).find(`.list-type-vm`).each(function () {
			uuids.push($(this).attr('data-vmuuid'));
		});
		$(`.addVMList`).find(`.list-type-obj-vm`).each(function () {
			uuids.push($(this).find('.vmCheck').attr('data-vmuuid'));
		});
		let count = [...new Set(uuids)].length; // 去重后个数
		let des = LANG.UI_BACKUP_CLOUD_TOTAL_INSTANCE_COUNT_DES.replace('%s', count.toString());
		$(`.count-total`).text(`(${des})`);
		if (count > 0) {
			$(`.selectvmtip`).hide();
		}
	}

	// 更新粒度下已选vm数量
	const updateObjVMCount = function (el) {
		let count = $(el).closest('ul').find(`.list-type-obj-vm`).length;
		let des = LANG.UI_BACKUP_CLOUD_SELECTED_INSTANCE_COUNT_DES.replace('%s', count.toString());
		$(el).closest('ul').prev('li').find('.count-vm').text(`(${des})`);
	}

	//勾选添加虚拟机显示列表
	var addVMList = function(id,node){
		var info = "";
		var vminfo = "";
		var diskinfo = "";
		var liId = id + node.vcenteruuid + node.id; //添加虚拟机每列ID
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
		if (!_ModifyFlag) {
			$('#disk' + escapeJquery(liId)).remove();
			$('#' + escapeJquery(liId)).remove();
		}

		if(node.checked || 'vm' === node.eventtype && backupObjList.hasOwnProperty(objId)){

			//根据虚拟机树类型添加每一列到列表
			$('#addHCList').append(info);
			$('#' + escapeJquery(liId)).popover();	   //初始化tips

			//初始化对象全选框
			var fullCheck = 'check';
			if (0 !== backupObjList[objId].exclude_vm_list.length) {
				fullCheck = 'uncheck';
			}
			$('#' + escapeJquery(liId)).find('.objCheck').iCheck({
				checkboxClass : 'icheckbox_square-blue',
				radioClass : 'iradio_square-blue',
			}).iCheck(fullCheck);

			//移除虚拟机显示
			$('.del'+escapeJquery(liId)).on('click', function(){
				removeObjHandle(id, node);
			});

			$('#disHref' + liId).on('click', function(){
				if(!node.initDisk && 'vm' === node.eventtype){
					//对象是虚拟机则显示磁盘信息
					var p = {};
					p.instance_uuid = node.id;
					p.platform_uuid = node.vcenteruuid;
					p.hypervisor = node.hypervisor;
					Metronic.blockUI({target: '#'+liId,animate: true});
					pAjaxRequest(p, "/api/v1/cloud/instance/disks", "GET", function (data) {
						Metronic.unblockUI('#'+liId);
						//加载前先清空上次加载
						$('#disk' + escapeJquery(liId)).empty();
						node.initDisk = true;
						node.detail = data.data;
						// 修改时初始化实例排除的卷
						if (_ModifyFlag && node.diskChecked && node.diskChecked != "") {
							if (!node.diskChecked['disk_list']) return;
							var diskCheckcount = node.diskChecked['disk_list'].length;
							var diskChecklist = node.diskChecked['disk_list'];
							var diskIdlist = [];
							for (var i = 0; i < diskCheckcount; i++) {
								diskIdlist[i] = diskChecklist[i]['disk_uuid'];
							}
						}
						if(node.detail && node.detail != "" && node.detail.disk_list){
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

							//修改使用
							if(node.diskChecked && node.diskChecked !=""){
								for(var j=0;j<diskCount;j++){
									if($.inArray(disklist[j]['disk_uuid'],diskIdlist) !=-1){
										var checkBox = $('#disCheck' + j + escapeJquery(node.id) + escapeJquery(node.vcenteruuid));
										checkBox.iCheck('uncheck');
										if (!checkBox.closest('li').find('.disNameExTips').find(`.exclude-tips`).length) {
											checkBox.closest('li').find('.disNameExTips').append(`<span class="exclude-tips" style="color:#F1416C">(${LANG.UI_BACKUP_EXCLUDED})</span>`);
										}
									}
								}
							}
						}else{
							diskinfo += '<li class="list-group-item">' + LANG.UI_BACKUP_AWS_NO_DISK + '</li>';
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
					var objChecked = $(this).find('.objCheck').prop('checked');
					//对象是虚拟机上级则显示虚拟机列表
					var vmNodeList = getVmNodeList(node);
					if ([] !== vmNodeList) {
						Metronic.blockUI({target: '#'+liId,animate: true});
						var times = Math.ceil(vmNodeList.length / 500);	//需要分几次加载
						var delay = 0;//解锁动画总共加载时间
						$('#disk' + escapeJquery(liId)).hide(); //先隐藏，加载完再显示
						//修改使用
						if(node.vmChecked && node.vmChecked !=""){
							if(!node.vmChecked) return;
							var vmIdlist = [];
							for(var i=0;i<vmNodeList.length;i++){
								vmIdlist[i] = vmNodeList[i].id;
							}
						}
						for(var i =0;i < times;i++){
							var startlength = 0 + (i * 500);	//从第几条开始
							var dinfo = '';
							var length = (i+1) * 500;
							for(var start = startlength;start < length;start ++){
								if(start >= vmNodeList.length){
									//所有虚拟机加载完成直接退出
									break;
								}
								dinfo +=
									'<li class="list-group-item popovers vmTips list-type-obj-vm" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-parentobjid="' + objId + '" data-content="' + decodeURIComponent(vmNodeList[start].path_show) + '" id="check_' + vmNodeList[start].vcenteruuid + vmNodeList[start].id + '">' +
									'<div class="col-md-10 vmName">' +
									vmNodeList[start].name +
									'<span class="display-none" id="vmuuid' + vmNodeList[start].vcenteruuid + vmNodeList[start].id + '">'+
									vmNodeList[start].id +
									'</span>' +
									'</div>'+
									'<div class="col-md-2 disk-checkbox">'+
									'<input class="icheck vmCheck" data-checkbox="icheckbox_square-blue" type="checkbox" id="vmCheck' + vmNodeList[start].vcenteruuid + vmNodeList[start].id + '" value="' + encodeURIComponent(vmNodeList[start].name) + '" data-vmuuid="' + vmNodeList[start].id + '">' +
									'</div>' +
									'</li>';
							}
							delay += 100;
							$('#disk' + escapeJquery(liId)).append(dinfo);

						}
						setTimeout(function(){
							$('.vmTips').popover();
							$('.vmCheck').iCheck({
								checkboxClass : 'icheckbox_square-blue',
								radioClass : 'iradio_square-blue',
							});
							$('.iCheck-helper').on('mouseover',function(){
								$('.vmTips').popover('hide');
							});
							var checkinit = $('#disk' + escapeJquery(liId) + ' .vmCheck').closest('ul').prev('li').find('.objCheck').data('checkinit');
							if (!checkinit) {
								//如果是全选、取消全选触发的
								if (objChecked) {
									$('#disk' + escapeJquery(liId) + ' .vmCheck').iCheck('check');
								} else {
									$('#disk' + escapeJquery(liId) + ' .vmCheck').iCheck('uncheck');
								}
								var vmInputs = $('#disk' + escapeJquery(liId) + ' .vmCheck');
								$.each(vmInputs, function (i, v) {
									checkVmHandle(v);
								});
							} else {
								//初始化先全部选中
								$('#disk' + escapeJquery(liId) + ' .vmCheck').iCheck('check');
								if(node.vmChecked && node.vmChecked !=""){
									for(var j=0;j<node.vmChecked.length;j++){
										if($.inArray(node.vmChecked[j],vmIdlist) !=-1){
											var checkBox = $('#disk' + escapeJquery(liId)).find('#vmCheck' + escapeJquery(node.vcenteruuid) + escapeJquery(node.vmChecked[j]));
											checkBox.iCheck('uncheck');
											if (!checkBox.closest('li').find('.vmName').find(`.exclude-tips`).length) {
												checkBox.closest('li').find('.vmName').append(`<span class="exclude-tips" style="color:#F1416C">(${LANG.UI_BACKUP_EXCLUDED})</span>`);
											}
										}
									}
								}
							}
							if (selectValue.length) {
								handlerFilterVmListCheck();
							} else {
								var vmCheck = $('.vmTips').find('.vmCheck');
								$.each(vmCheck, function (i, v) {
									checkVmHandle(v);
								});
							}

							$('#disk' + escapeJquery(liId)).show();
							$('.vmCheck').next('.iCheck-helper').on('click', function () {
								checkVmHandle(this);
							});
							$('.vmCheck').on('change', function () {
								checkVmHandle(this);
							});
							Metronic.unblockUI('#'+liId);
						}, delay);
					} else {
						vminfo += '<li class="list-group-item">' + LANG.UI_BACKUP_AWS_NO_INSTANCE + '</li>';
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
			checkObjHandle(this);
		});
		$('.objCheck').on('change', function () {
			checkObjHandle(this);
		});

		// 选中上级粒度时触发展开
		if (node.type != 7) {
			$('#' + escapeJquery(liId)).find('a').trigger('click');
		}

		updateTotalVMCount();
	}

	//检测虚拟机断开状态,选中的时候提示
	var checkVMOnlineAndTips = function(node){
		//如果是虚拟化中心 则不做判断
		if(!node.online && node.checked && node.type != 1){
			UIToastr.showWarning(LANG.UI_BACKUP_AWS_SELECT_OFFLINE_INSTANCE_TIPS, LANG.UI_BACKUP_AWS_SELECT_OFFLINE_INSTANCE_VALUE);
		}
	}

	var nodeSelect = function(treeId, treeNode, clickFlag){
		if('vm' == treeNode.eventtype){
			$.fn.zTree.getZTreeObj(treeId).checkNode(treeNode, !treeNode.checked, true, true);
			if(treeNode.inbackup){
				UIToastr.showWarning(LANG.UI_BACKUP_AWS_INSTANCE_IN_TASK, LANG.UI_BACKUP_AWS_INSTANCE_IN_TASK_TIPS);
				return;
			}
			if(treeNode.chkDisabled){
				//如果不可用,提示未授权
				UIToastr.showWarning(LANG.UI_BACKUP_AWS_SELECT_UNAUTHORIZAED_INSTANCE_TIPS, LANG.UI_BACKUP_AWS_SELECT_UNAUTHORIZAED_INSTANCE_VALUE);
				return;
			}
		}else{
			if(!treeNode.isParent) return;//不存在子节点不展开
			nodeExpand(treeId, treeNode);
			$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
		}
	}

	var nodeExpand = function(treeId, treeNode){
		if(1 == treeNode.type){
			if(treeNode.children) return true;
			getSyncVcenterInfo(treeId, treeNode, false, false);
		}else{
			return true;
		}

	}

	//刷新清空对应虚拟化中心的虚拟机列表
	var refreshVMList = function(id,vcenteruuid){
		var allNodes = currentTree.getCheckedNodes(true);
		if(allNodes.length ==0) return;
		if(allNodes[0].vcenteruuid != vcenteruuid) return;
		$('#addHCList li').remove();
		$('.diskList').remove();
	}

	//异步获取虚拟化中心/宿主机的信息  refreshFlag是否重新刷新
	var getSyncVcenterInfo = function(treeId, treeNode, refreshFlag, expendFlag){
		if (_ModifyFlag) {
			treeNode.platform_uuid = treeNode.vcenteruuid;
			treeNode.platform_flag = treeNode.vcenterFlag == 1;
			treeNode.hypervisor_type = treeNode.hypervisor;
		}
		var div = ".src-wrap__content";
		let p = {
			platform_uuid: treeNode.platform_uuid.replace(/_$/, ''),
			platform_flag: treeNode.platform_flag,
			hypervisor_type: treeNode.hypervisor_type,
			display_mode: 1,
			refresh_flag: refreshFlag,
			show_vm_flag: true,
			aws_flag: true,
			allocated_vm_flag: treeNode.allocated_vm_flag ?? false
		};
		// 修改任务时同步云平台
		if (_ModifyFlag && refreshFlag) {
			p.modify_flag = _ModifyFlag;
			p.job_uuid = data.job_uuid;
			expendFlag = true;
		}
		Metronic.blockUI({target: div, animate: true});
		pAjaxRequest(p, "/api/v1/vm/platforms/vm_tree", "GET", function (data) {
			Metronic.unblockUI(div);
			refreshVMList(treeId, treeNode.platform_uuid);
			//检测是否为同一事件返回
			if (!_ModifyFlag && currentTree != $.fn.zTree.getZTreeObj(treeId)) return;
			if (data.success) {
				let nodes = data.data.rows;
				//刷新同一虚拟化中心清除虚拟机列表显示
				if (refreshFlag) {
					nodeParamList = currentTree.getNodesByParamFuzzy('name', '');
					currentTree.showNodes(nodeParamList);
					$.each(nodeParamList, function (i, v) {
						if (0 == v.pid) {
							currentTree.expandNode(v, true);
						}
					});
				}
				var allNodes = currentTree.getCheckedNodes(true);
				if(refreshFlag && allNodes.length!= 0 && allNodes[0].hypervisor_type == treeNode.hypervisor_type && allNodes[0].platform_uuid == treeNode.platform_uuid){
					$('.popover.in').remove();
					$('.addVMList').empty();
					currentTree.checkAllNodes(false);
				}
				currentTree.removeChildNodes(treeNode);
				currentTree.addNodes(treeNode, nodes, true);
				//如果获取到得长度不为0 则将treenode设置成可选
				if (nodes.length != 0) {
					treeNode.nocheck = false;
					currentTree.setChkDisabled(treeNode, treeNode.chkDisabled);
				}

				showSearchInput();
				if(expendFlag == true){
					currentTree.expandNode(treeNode, true, true, true);
				}
				for (var i = 0; i < nodes.length; i++) {
					if (nodes[i].checked) {
						var checkNode = currentTree.getNodesByParam("id", nodes[i].id, null);
						addVMList(treeId, checkNode[0]);
					}
				}
			} else {
				OPREL(data);
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

	var initTree = function(type) {
		let p = {};
		p.display_mode = type;
		p.cloud_flag = true;
		p.cloud_type = 'public';
		pAjaxRequest(p, "/api/v1/vm/platforms/tree", "GET", function (data) {
			setTree(data.data.info);
		}, false);
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

	//初始化新任务
	var initNewSettings = function() {
		$('#awsbackupcontent').find('.button-submit').css('visibility', 'hidden');
		initData();
		initStrategySelect(); //初始化策略选择
		initSpinner();
	}

	//初始化历史任务信息
	var initOldSettings = function(){
		$('#awsbackupcontent').find('.button-next').css('visibility', 'hidden');
		$('#awsbackupcontent').find('.button-submit').css('visibility', 'hidden');
		var data = {};
		data.job_uuid = $('#task_uuid').val();
		pAjaxRequest(data, "/api/v1/vm/jobs/backup/all_info", "GET", function (d) {
			SETTINGS = d.data;
			initModifyData();
			initStrategySelect(); //初始化策略选择
			initStep1Settings();
			initStep2Settings();
			initStep3Settings();
			initSpinner();
		}, false);
	}

	//初始化第一步任务信息
	var initStep1Settings = function(){
		//设置虚拟机树
		let p = {};
		p.display_mode = SETTINGS.display_mode;
		p.hypervisor_type = SETTINGS.hypervisor;
		p.job_uuid = SETTINGS.taskuuid;
		p.platform_uuid = SETTINGS.vm_info[0].vcuuid;
		Metronic.blockUI({target: '.tree_div',animate: true});
		pAjaxRequest(p, "/api/v1/vm/jobs/backup/vm_tree", "GET", function (d) {
			Metronic.unblockUI('.tree_div');
			setTree(d.data.rows);
		}, false);
	}

	//初始化第二步任务信息
	var initStep2Settings = function(){
		//初始化节点
		initAllocationNodes();
	}

	var initStep3Settings = function(){
		//appliance
		$('.applianceselectdiv').show();
		// initApplianceSelect(SETTINGS.vm_info[0].vcuuid, SETTINGS.vm_info[0].hostuuid);
		$('#applianceSelect').empty();
		let agentOption = $("<option>").text(SETTINGS.agentuuid).val(SETTINGS.agentuuid);
		$('#applianceSelect').append(agentOption);
		$('#applianceSelect').val(SETTINGS.agentuuid);
		$('#applianceSelect').selectpicker('refresh');
		//传输代理公有ip
		if (CONF.VM_TYPE.HUAWEICLOUD == data.src_info.hypervisor_type) {
			$('.agent-is-public-ip').show();
			$('.agent-public-ip').show();
			$('.agent-public-ip-billing').show();
			let agentPublicIp = SETTINGS.agent_public_ip;
			// initPublicIp(agentPublicIp.ip_uuid);
			let option = `<option value="${agentPublicIp.ip_uuid}" data-address="${agentPublicIp.ip_address}" selected>${agentPublicIp.ip_address}</option>`;
			$('select[name=agent_public_ip]').empty();
			$('select[name=agent_public_ip]').append(option);
			$('select[name=agent_billing_type]').val(agentPublicIp.chargemode);
			$('input[name=agent_billing_value]').val(agentPublicIp.size);
		} else {
			$('.agent-is-public-ip').hide();
			$('.agent-public-ip').hide();
			$('.agent-public-ip-billing').hide();
		}

		//限速策略
		speedList = SETTINGS.speedInfo;
		for(var i=0;i<speedList.length;i++){
			addSpeedList(speedList[i]);
		}
		//线程数量
		$('#backupThreadNum').val(SETTINGS.mode.threadnum);
		//校验策略
		$('#verifyflag').bootstrapSwitch('state',SETTINGS.verifyInfo.ischeck);
		if(SETTINGS.verifyInfo.ischeck){
			$(".verifytypeDiv").show();
			$("#verifytype").val(SETTINGS.verifyInfo.algorithm);
		}

		//传输策略bts
		$('#transport_mode').val('nbd');		//传输模式
		// 加密传输
		if (SETTINGS.bts.encrypt) {
			$('.transfer-encrypt-method-form').show();
		}
		$('#encrypttransfer').bootstrapSwitch('state', SETTINGS.bts.encrypt);
		// 传输加密算法
		if (SETTINGS.bts.encrypt_method) {
			$('#transferEncryptMethod').val(SETTINGS.bts.encrypt_method);
		}

		//存储策略bss
		$('#deduplicationCheck').bootstrapSwitch('state', SETTINGS.bss.deduplication);
		$('#blocksize').val(SETTINGS.bss.blocksize);
		//数据加密
		$('#encryptStorageCheck').bootstrapSwitch('state', SETTINGS.bss.encrypt);
		$('#passwordAutocheck').bootstrapSwitch('state', SETTINGS.bss.password_auto_flag);
		// 存储加密算法
		if(SETTINGS.bss.encrypt){
			$('.storage-encrypt-div').show();
		}
		$('#storageEncryptMethod').val(SETTINGS.bss.encrypt_method);
		//如果不是自动获取密码
		if(!SETTINGS.bss.password_auto_flag){
			// 修改任务时密码框和确认密码框先不赋值,提示: 修改时填写
			$('#password').attr('placeholder', LANG.UI_PUBLIC_MODIFY_PASSWORD_TIPS);
			$('#repassword').attr('placeholder', LANG.UI_PUBLIC_MODIFY_PASSWORD_TIPS);
		} else {
			// 自动生成密码默认为checked，首次进入页面不会触发change事件，因此手动触发一次从而改变firstInitPageFlag状态
			passwordModeChange();
			$('.passwordDiv').hide();
		}

		//数据传输网段
		$('#ipSegment').val(SETTINGS.transport_ip_segment);

		var vmType = parseInt(SETTINGS.hypervisor);
		//默认带静默快照选项
		$('.snapshotdiv').show();
		$('.backupmodeshow2').show();

		$('.showBackupmode').show();						//默认显示备份模式tab
		$('#backupmodeshowdiv').show();						//默认显示备份模式信息

		//保留策略
		$('#reserveType').val(SETTINGS.brs.type); //保留方式
		$('#spinnerNumInput').val(SETTINGS.brs.number); //保留个数

		$('.highLi').show();
		$('.highLi').removeClass('active');
		$('.commonLi').removeClass('active').addClass('active');
		$('#tab_common').removeClass('active').addClass('active');
		$('#tab_other').removeClass('active');

		$('.speedlimitDiv').show(); 			//显示限速策略
		$('.threadDiv').show();					//显示多线程

		$('.preSnapshotDiv').show();			//显示提前创建快照
		$('.presnapshotshow').show();

		$('.diff').show();
		$('.perIncrementTips').show();
		$('.icsDiv').hide();
		$('.cbrDiv').hide();

		$("#incmodel option[value='3']").remove();
		var option = $("<option>").val(3).text("CBT");
		$('#incmodel').append(option);
		$('#incmodel').prop('disabled', false);
		$('#preSnapshotcheck').prop('disabled', false);	//提前创建快照
		//默认描述为静默快照
		$('.silentsnapshotlabel').html(LANG.UI_BACKUP_SILENTSNAPSHOT);
		$('.silentDiv').show();
		$('.uniformDiv').hide();
		//初始化高速模式描述
		$('.highSpeed').attr('data-content', LANG.UI_BACKUP_MODE_HIGH_SPEED_TIPS);
		//静默快照
		// $('#silentsnapshotcheck').bootstrapSwitch('state', SETTINGS.mode.quiescesnapshot);
		// 时间策略描述
		// $('.all-strategy-tips').show();
		$('.no-diff-tips').hide();

		if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
			if(4 == CONF.SOFTWARE) {
				$('.ipSegmentDiv').hide();  //免费版隐藏数据传输网段
				$('.encryptStorageDiv').hide();//免费版隐藏数据加密
			}
		}
		//数据加密打开
		if(SETTINGS.bss.encrypt){
			$('.passwordModeDiv').show();
			if(!SETTINGS.bss.password_auto_flag){
				$('.passwordDiv').show();
			}
		}else {
			$('.passwordDiv').hide();
		}
		//禁用显示方式
		$('#vmshowtype').prop('disabled', true);
		$('#vmshowtype').selectpicker('refresh');
		if(1 == CONF.SOFTWARE || 4 == CONF.SOFTWARE || 9 == CONF.SOFTWARE){
			$('#backupmodediv').hide();							//隐藏高速模式
			$('#backupmodeshowdiv').hide();						//隐藏备份模式确认
		}

		if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
			//英文版判断不支持高速模式
			if(false === authFun.speedkit){
				$('#backupmodediv').hide();							//隐藏高速模式
			}
		}

		$('#compressCheck').bootstrapSwitch('state', SETTINGS.bss.compress);
		// 压缩等级
		if(SETTINGS.bss.compress){
			$('#compressGrade').val(SETTINGS.bss.compress_method);
		}
		$('#encryptcheck').bootstrapSwitch('state', SETTINGS.bss.encrypt);
		// 存储加密算法
		if(SETTINGS.bss.encrypt){
			$('.storage-encrypt-div').show();
		}
		$('#storageEncryptMethod').val(SETTINGS.bss.encrypt_method);
		//备份模式（高速模式）
		$('#snapshotcheck').bootstrapSwitch('state', SETTINGS.mode.snapshot);
		//保留快照
		$('#keepsnapshotcheck').bootstrapSwitch('state', SETTINGS.mode.keepsnapshot);
		// 快照超时时间
		$('#snapshotTimeout').val(SETTINGS.mode.snapshottimeout);
		$('#incmodel').val(SETTINGS.mode.incmode);

		//内联数据消重（深度有效数据提取）
		$('#parsefscheck').bootstrapSwitch('state', SETTINGS.mode.parsefsmode);
		//排除交换文件块
		$('#swapcheck').bootstrapSwitch('state', SETTINGS.mode.swapmode);
		// //排除删除文件块
		// $('#deletefilecheck').bootstrapSwitch('state', SETTINGS.mode.deletefilemode);
		//排除分区间隙
		$('#gapcheck').bootstrapSwitch('state', SETTINGS.mode.gapmode);

		if(!SETTINGS.mode.parsefsmode){
			$('.swapmodeshow').hide();
			$('.deletefilemodeshow').hide();
			$('.gapmodeshow').hide();
		}
		//快照模式
		$('#snapshottype').val(SETTINGS.mode.snapshotmode);
		//提前创建快照
		$('#preSnapshotcheck').bootstrapSwitch('state', SETTINGS.mode.presnapshot);
		if(SETTINGS.mode.snapshotmode == 1){
			$('.preSnapshotDiv').show();
		}else{
			$('.preSnapshotDiv').hide();
		}
		// 忽略节点资源限制
		$('#ignore_resource_limit').bootstrapSwitch('state', data.advanced_strategy.mode.ignore_resource_limiting_flag);
		//任务名
		$('#jobname').val(SETTINGS.taskname);
		//任务UUID
		data.job_uuid = SETTINGS.taskuuid;

		//添加数据库大小改变事件,当数据块大小变化的时候提示下一次将做完全备份
		$('#blocksize').on('change', function(){
			if(this.value != SETTINGS.bss.blocksize){
				UIToastr.showInfo(LANG.UI_BACKUP_EIDT_BLOCKSIZE_TIP_TITLE, LANG.UI_BACKUP_EIDT_BLOCKSIZE_TIP_VALUE);
			}
		});

		// 按链保留时隐藏合并模式
		if (CONF.RESERVE_STRATEGY_MODE.CHIAN == data.reserved_strategy.strategyMode) {
			$(`.mergemodediv`).hide();
			$(`.mergemodeshow`).hide();
		}
	}

	//显示搜索框
	var showSearchInput = function(){
		$('#searchvm').val('').show();
		$('.src-wrap__content .src-wrap__content__ztree').css('height', 'calc(100% - 60px)');
	}

	//搜索虚拟机
	var searchVM = function(){
		var value = $('#searchvm').val();
		// 输入验证
		if(!customInputValidate('string',value)){
			return false;
		}
		var nodes = currentTree.getNodes();
		if(!nodes || nodes.length == 0) return;
		var checkNode =currentTree.getCheckedNodes();
		var checkVmNode = [];
		$.each(checkNode, function (i, v) {
			if (v.type == 7) {
				checkVmNode.push(v);
			}
		});
		var allNode = currentTree.transformToArray(currentTree.getNodes());
		nodeParamList = currentTree.getNodesByParamFuzzy('name', value);
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
		if ($('#task_uuid').val()) {
			$('#awsbackupcontent .button-submit').css('visibility', 'hidden');
			$('#awsbackupcontent .button-next').css('visibility', 'hidden');
		}
		//绑定事件
		$('#searchvm').on('propertychange', searchVM).on('input', searchVM);
		// 设置树的inline-block以便在小分辨率屏幕初始化时即刻展示横向滚动条
		$('#vm_tree_hc').css('display', 'block');
	}

	var initMyTree = function(){
		var s_vmuuid = $('#s_vmuuid').val();
		var s_vcenteruuid = $('#s_vcenteruuid').val();
		var s_hypervisor = $('#s_hypervisor').val();
		if (s_vmuuid && s_vcenteruuid && s_hypervisor) {
			//快速创建备份任务
			speedInitTree(s_vmuuid, s_vcenteruuid, s_hypervisor);
		} else {
			//常规创建备份任务
			initTree(1);
		}
	}

	//快速创建备份任务
	var speedInitTree = function(vmuuid, vcenteruuid, hypervisor){
		speedFlag = true;
		let p ={};
		p.display_mode = 1;
		p.vm_uuid = vmuuid;
		p.platform_uuid = vcenteruuid;
		p.hypervisor_type = hypervisor;
		pAjaxRequest(p, "/api/v1/vm/jobs/speed_backup/tree", "GET", function (d) {
			setTree(d.data.rows);
		}, false);
		nodeLoaded[vcenteruuid + '_'] = true;
	}

	// 初始化存储策略
	var initStorageStrategy = function () {
		$('#blocksize').val(512).prop('disabled', true);
		$('.blocksizediv').show();
	}

	//初始化appliance下拉框
	var initApplianceSelect = function (platformUuid, regionUuid, zone) {
		let hypervisor = data.hypervisor_type;
		if (!platformUuid || !regionUuid) return;
		if (applianceRegionUuid == regionUuid) return; //只加载一次
		$('#applianceSelect').next('div').find('.open').addClass('applianceMenu');
		Metronic.blockUI({target: '#awsbackupcontent', animate: true});
		Metronic.blockUI({target: '.applianceMenu', animate: true});
		pAjaxRequest({
			platform_uuid: platformUuid,
			region_uuid: regionUuid,
			available_zone: zone
		}, "/api/v1/cloud/instance/types", "GET", function (d) {
			Metronic.unblockUI('#awsbackupcontent');
			Metronic.unblockUI('.applianceMenu');
			applianceRegionUuid = regionUuid;
			let data = d.data;
			if (!data.length) return;
			var applianceSelect = $('#applianceSelect');
			applianceSelect.empty();
			for (var i = 0; i < data.length; i++) {
				let vcpu = data[i].instance_type_vcpu;
				let mem = 0;
				switch (hypervisor) {
					case CONF.VM_TYPE.AWS:
						mem = 1024 * 1024 * parseInt(data[i].instance_type_memry);
						break;
					case CONF.VM_TYPE.HUAWEICLOUD:
						mem = 1024 * 1024 * 1024 * parseInt(data[i].instance_type_memry);
						break;
				}
				let memStr = storageCalculateSize(mem);
				var option = $("<option>").text(data[i].instance_type_name + '(' + vcpu + 'vCPU,' + memStr + ')').val(data[i].instance_type_name);
				applianceSelect.append(option);
			}
			if (_ModifyFlag) {
				applianceSelect.val(SETTINGS.agentuuid);
			}
			applianceSelect.selectpicker('refresh');
		});
	}

	// 显示加密算法
	var transferEncryptChange = function () {
		if (this.checked) {
			$('.transfer-encrypt-method-form').show();
		} else {
			$('.transfer-encrypt-method-form').hide();
		}
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
				// 是否初始化策略
				if(_ModifyFlag && SETTINGS.strategyuuid){
					// 默认选中选中所使用策略组
					strategyselect.val(SETTINGS.strategyuuid);
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

		pAjaxRequest({type: CONF.MODULE_TYPE.PUBLIC_CLOUD}, '/api/v1/strategies/select', "GET", initStrategyList, true);
	}

	var initDataChangeListeners = function(){
		initVmFilterListeners();
		initHighListeners();
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
			initHighStrategyDes();
		});
		$('#preSnapshptcheck').on("switchChange.bootstrapSwitch",function(){
			initHighStrategyDes();
		});
		$('#backupThreadNum').on('input propertychange', function(){
			initHighStrategyDes();
		});

		$('#parsefscheck').on("switchChange.bootstrapSwitch",function(){
			initHighStrategyDes();
		});

		$('#swapcheck').on("switchChange.bootstrapSwitch",function(){
			initHighStrategyDes();
		});

		$('#gapcheck').on("switchChange.bootstrapSwitch",function(){
			initHighStrategyDes();
		});
	}

	//加载策略对应描述
	var initStrategyDes = function(){
		initHighStrategyDes();
	}

	var initAgentSelect = function () {
		$('#applianceSelect').empty();
		switch (data.hypervisor_type) {
			case CONF.VM_TYPE.AWS:
				$('#applianceSelect').append('<option value="t3.small">t3.small(2vCPU,2.00 GB)</option>');
				$('.agent-public-ip').hide();
				$('.agent-public-ip-billing').hide();
				break;
			case CONF.VM_TYPE.HUAWEICLOUD:
				// 华为云需手动选择
				$('#applianceSelect').append('<option value="">' + LANG.UI_JOB_SELECT + '</option>');
				$('.agent-public-ip').show();
				$('.agent-public-ip-billing').show();
				break;
			default:
				break;
		}
		$('#applianceSelect').selectpicker('refresh');
	}

	// 初始化公有ip选择，华为云使用
	var initPublicIp = function (ip_uuid = '') {
		let platform_uuid = '', region_uuid = '';
		let nodes = currentTree.getCheckedNodes();
		platform_uuid = nodes[0].vcenteruuid;
		region_uuid = nodes[0].hostuuid;
		if (applianceIpRegionUuid == region_uuid) {
			return;
		}
		$('select[name=agent_public_ip]').empty();
		$('select[name=agent_public_ip]').append('<option value="" data-address="">' + LANG.UI_JOB_SELECT + '</option>');
		Metronic.blockUI({target: '#awsbackupcontent', animate: true, cenrerY: true});
		pAjaxRequest({platform_uuid: platform_uuid, region_uuid: region_uuid},
			"/api/v1/cloud/platform/ip_list", "GET", function (d) {
				Metronic.unblockUI('#awsbackupcontent');
				let ipList = d.data.ip_list;
				let option = '';
				$.each(ipList, function (i, v) {
					let selected = v.ip_uuid == ip_uuid ? 'selected' : '';
					option += `<option value="${v.ip_uuid}" data-address="${v.ip_address}" ${selected}>${v.ip_name}(${v.ip_address}, ${v.ip_size}Mbps)</option>`;
				});
				$('select[name=agent_public_ip]').append(option);
				applianceIpRegionUuid = region_uuid;
			}, true);
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
		if(strategyIndex && strategyIndex != "" && _ModifyFlag){
			var oldDes = globalStrategy[strategyIndex].time.des;
			initStrategyDesStyle($('.backupTimeDes'), diffDes, oldDes);
		}else{
			$('.backupTimeDes').removeClass('font-green-seagreen');
		}
		$('.backupTimeDes').html(des);
		$('.backupTimeDes').prop('title', des);
	}

	//初始化时间策略描述
	var initSpeedStrategyDes = function(){
		var titleDes = "";
		var des = "";
		if (speedList.length == 0) {
			return
		}

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
			setTimeout(function (){
				$(".strategy-table #table").bootstrapTable('checkBy', {
					field: 'uuid',
					values: [global_speed_limit]
				})
				// 下面的需要初始化全局限速策略表格并携带参数
				let rowData = $(".strategy-table #table").bootstrapTable("getData");

				var datas = [];
				for(var j in rowData) {
					if (rowData[j].uuid == global_speed_limit) {
						datas = rowData[j].detail;
						datas = datas.split('</br>');
					}
				}
				if(datas.length !=0){
					des += LANG.UI_GLOBAL_STRATEGY_SPEED_NUM + ": " + datas.length;
				}

				for(var i in datas){
					titleDes += datas[i] + '. ';
				}

				$('.speedlimitDes').html(des);
				$('.speedlimitDes').prop('title', titleDes);
			},1500);
		} else {
			var speedInfo = speedList.speedInfo;
			// 自定义
			if(speedInfo.length > 0){
				des += LANG.UI_GLOBAL_STRATEGY_SPEED_NUM + ": " + speedInfo.length;
			}

			var strategy_type = speedList.strategy_type;
			for(var i in speedInfo){
				titleDes += speedInfo[i].des + '. ';
			}
			addGlobalStrategy.init({'strategy_type':strategy_type,speedInfo:speedInfo,initSpeedFlag:1});

			setTimeout(function (){
				$('.speedlimitDes').html(des);
				$('.speedlimitDes').prop('title', titleDes);
			}, 1000);
		}
	}

	var initHighStrategyDes = function(){
		var des = "";
		var diffDes = "";
		// 非磁带存储标题显示传输线程
		if (CONF.BD_STORAGE_TYPE.TAPE != data.advanced_strategy.node.storage_type) {
			let threadNum = isNaN(parseInt($('#backupThreadNum').val())) ? '' : parseInt($('#backupThreadNum').val());
			des += LANG.UI_GLOBAL_STRATEGY_THREAD_NUM + ": " + threadNum + ', ';
			diffDes += LANG.UI_GLOBAL_STRATEGY_THREAD_NUM + ": " + threadNum + "<br>";
		}

		des += LANG.UI_GLOBAL_STRATEGY_SNAPSHOT_TYPE + ": " + $('#snapshottype option:selected').text() ;
		diffDes += LANG.UI_GLOBAL_STRATEGY_SNAPSHOT_TYPE + ": " + $('#snapshottype option:selected').text() + "<br>";
		if(authFun.length != 0 && authFun.vcbt){
			des += ", " + LANG.UI_GLOBAL_STRATEGY_PARSE_FS + ": " + getSwitchDes($('#parsefscheck').get(0).checked) + ", ";
			des += LANG.UI_GLOBAL_STRATEGY_SWAP + ": " + getSwitchDes($('#swapcheck').get(0).checked) + ", ";
			des += LANG.UI_GLOBAL_STRATEGY_GAP + ": " + getSwitchDes($('#gapcheck').get(0).checked);

			diffDes += LANG.UI_GLOBAL_STRATEGY_PARSE_FS + ": " + getSwitchDes($('#parsefscheck').get(0).checked) + "<br>";
			diffDes += LANG.UI_GLOBAL_STRATEGY_SWAP + ": " + getSwitchDes($('#swapcheck').get(0).checked) + "<br>";
			diffDes += LANG.UI_GLOBAL_STRATEGY_GAP + ": " + getSwitchDes($('#gapcheck').get(0).checked);
		}
		$('.backupHighDes').html(des);
		$('.backupHighDes').prop('title', des);

	}

	var initGFS = function(){
//		 {
//			 //初始化数值,数组中第一个代表level2_type即周几/第几周/哪月   第二个代表保留数量,第三个代表是否勾选(默认值为checked选中和''不选中)
//			 'week':[3,2,'checked'],
//			 'month':[2,4,''],
//           'year':[4,2,'checked'],
//	 }
		$("#GFSDiv").initGFSPlug();
	}

	//获取虚拟化平台关联备份节点信息
	var initAllocationNodes = function(){
		// var data = {};
		// data.platform_uuid = oldVcenter;
		// pAjaxRequest(data, '/api/v1/nodes/allocation', 'GET', function (d) {
		// 	var jsondata = d.data;
		// 	var nodes = jsondata.node_list;	//备份节点列表
		// 	_allocationList = jsondata.allocation_list;	//已绑定的节点列表
		// 	//初始化备份节点
		// 	initNodeSelect();
		// 	//initStorageSelect();
		// }, false);
	}

	//初始化任务拥挤程度区间
	var initTaskCrowd = function(){
		pAjaxRequest({}, "/api/v1/jobs/time_crow_list", "GET", (d) => {
			var jsonData = d.data;
			if(jsonData.time_list.length != 0){
				$('#backupCrowd').taskCrowd({timeList:jsonData.time_list, showFlag: jsonData.show_flag});
			}
		}, false);
	}

	var handleValidation = function() {
		var Form = $('#submit_form');

		Form.validate({
			errorElement: 'span', //default input error message container
			errorClass: 'help-block help-block-error', // default input error message class
			focusInvalid: false, // do not focus the last invalid input
			ignore: "",  // validate all fields including form hidden input

			invalidHandler: function (event, validator) { //display error alert on form submit
			},

			errorPlacement: function (error, element) { // render error placement for each input type
				var icon = $(element).parent('.input-icon').children('i');
				$(element).closest('.form-group').removeClass('has-success').addClass('has-error');
				icon.removeClass('fa-check').addClass("fa-warning");
				icon.attr("data-original-title", error.text()).tooltip({'container': 'body'});
			},

			highlight: function (element) { // hightlight error inputs

			},

			unhighlight: function (element) { // revert the change done by hightlight

			},

			success: function (label, element) {
				var icon = $(element).parent('.input-icon').children('i');
				$(element).closest('.form-group').removeClass('has-error').addClass('has-success'); // set success class to the control group
				icon.removeClass("fa-warning").addClass("fa-check");
			},

			submitHandler: function (form) {

			}

		});
	};

	return {
		//main function to initiate the module
		init: function () {
			// handleValidation();
			initSoftwareVersionDiff(); //版本差异处理
			wizardInit();         //
			initSelectShowType(); //
			initListener();       //
			inintDatatimePicker();//
			if ($('#task_uuid').val()) {
				_ModifyFlag = true;
				//初始化原始数据
				initTaskCrowd();
				initOldSettings();
			} else {
				initMyTree();         //
				initNewSettings();
				// initStrategy();       //初始化时间策略
				// initStorageStrategy();
				// initGFS() //初始化GFS策略
			}
			initDataChangeListeners(); //初始化策略事件
		}
	};

}();

jQuery(document).ready(function() {
	AWSBackup.init();
});