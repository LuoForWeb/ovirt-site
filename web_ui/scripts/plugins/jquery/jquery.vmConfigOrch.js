(function($){
  $.fn.vmConfigOrch = function(options){
	  var defaults = {
		  //多维数组,每一项表示每一个虚拟机的配置
		  'config' : [],
		  'hide' : [],	//是否影藏某项
		  'type' : 2,	//配置类型   2瞬时恢复/3迁移
	  }
	  var option = $.extend(defaults,options);
	  var parentID = '';
	  
	  //得到隐藏的类 type:(string)   storage, network, cpu, memory
	  var getHideClass = function(type){
		  for(var i=0; i<option.hide.length; i++){
			  if(type == option.hide[i]){
				  return "displaynoneimp";
			  }
		  }
		  return '';
	  }
	  
	  //得到任务类型描述
	  var getTaskTypeDes = function(){
		  var des = '';
		  if(2 == option.type){
			  des = LANG.UI_PUBLIC_RECOVERY;
		  }else if(3 == option.type){
			  des = LANG.UI_MOTION_NAME;
		  }
		  return des;
	  }
	  
	  //得到标题栏num(第几个虚拟机),name(虚拟机名字)
	  var getVMDiv = function(config, num){
		  //顶部
		  var div = '<div class="panel panel-default">' + 
			  		 	'<div class="panel-heading">' + 
							'<h4 class="panel-title">' + 
							  '<a class="accordion-toggle accordion-toggle-styled collapsed" name="' + config['vmuuid'] + '" ' + 
							   'data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent="#' + 
							   parentID + '" href="#vm' + num + '">' + 
							   '<i class="fa fa-desktop font-green-seagreen"></i> <span class="font14">' + config['oldname'] + '</span></a>' + 
							'</h4>' + 
						'</div>';
		  //左边
		  div += '<div id="vm' + num + '" class="portlet box panel-collapse collapse configsdiv">' + 
					'<div class="portlet-body">' + 
						'<div class="row">' + 
							'<div class="col-md-3 col-sm-3 col-xs-3">' + 
								'<ul class="nav nav-tabs tabs-left">' + 
									'<li class="active">' + 
										'<a href="#tab_' + num + '_1" data-toggle="tab" aria-expanded="true">' + 
										LANG.UI_VM_SETTING_NAME + ' </a>' + 
									'</li>' + 
									'<li class="' + getHideClass('cpu') + '">' + 
										'<a href="#tab_' + num + '_2" data-toggle="tab" aria-expanded="false">' + 
										'CPU' + ' </a>' + 
									'</li>' + 
									'<li class="' + getHideClass('memory') + '">' + 
										'<a href="#tab_' + num + '_3" data-toggle="tab" aria-expanded="false">' + 
										LANG.UI_PUBLIC_MEMORY + ' </a>' + 
									'</li>' + 
									'<li class="' + getHideClass('storage') + '">' + 
										'<a href="#tab_' + num + '_4"  data-toggle="tab" aria-expanded="false">' + 
										LANG.UI_VM_SETTING_STORAGE + ' </a>' + 
									'</li>' + 
									'<li class="' + getHideClass('network') + '">' + 
										'<a href="#tab_' + num + '_5" data-toggle="tab" aria-expanded="false">' + 
										LANG.UI_VM_SETTING_NETWORK + ' </a>' + 
									'</li>' + 
									'<li class="' + getHideClass('host') + '">' + 
										'<a href="#tab_' + num + '_6" data-toggle="tab" aria-expanded="false">' + 
										LANG.UI_PUBLIC_DESTINATION_HOST + ' </a>' + 
									'</li>' + 
								'</ul>' + 
							'</div>';
		  //名称和状态
		  div += '<div class="col-md-9 col-sm-9 col-xs-9">' + 
					'<div class="tab-content">' + 
						'<div class="tab-pane active in" id="tab_' + num + '_1">' + 
							'<div class="form-group col-md-12">' + 
								'<label class="control-label mb5">' + getTaskTypeDes() + LANG.UI_VM_SETTING_REC_NAME_END + '：<span class="required">*</span></label>' + 
								'<div class="input-icon right">' + 
									'<i class="fa"></i>' + 
									'<input type="text" class="display-none" value="' + config['vmuuid'] + '" name="vmuuid"/> ' +
									'<input type="text" class="display-none" value="' + config['vcenteruuid'] + '" name="vcenteruuid"/> ' +
									'<input type="text" class="display-none" value="' + config['childuuid'] + '" name="childuuid"/> ' +
									'<input type="text" maxlength="60" class="form-control" value="' + config['vmname'] + '" name="vmname"/> ' + 
								'</div> ' + 
							'</div>' + 
							'<div class="form-group col-md-12">' + 
								'<label class="control-label mb5">' + getTaskTypeDes() + LANG.UI_VM_SETTING_REC_POWER_END + '<span class="required">' + 
								'* </span></label>' + 
								'<div>' + 
									'<input type="checkbox" name="vmpower" ' + getPowerStatus(config) + ' class="make-switch" data-on-color="primary" data-off-color="info" ' + 
									'data-on-text="' + LANG.UI_PUBLIC_ON_ONE + '" ' + 
									'data-off-text="' + LANG.UI_PUBLIC_OFF_ONE + '">' + 
								'</div>' + 
							'</div>' + 
						'</div>';
		  
		  //cpu
		  div += getCpuDiv(config, num);
		  
		  //内存
		  div += getMemoryDiv(config, num);
		  
		  //存储
		  div += getStorageDiv(config, num);
		  
		  //网络
		  div += getNetworkDiv(config, num);
		  
		  //目的宿主机
		  div += getHostDiv(config, num);
		  
		  //封顶
		  div += '</div></div></div></div></div></div>';
		  
		  return div;
	  }
	  
	  //得到开关机状态
	  var getPowerStatus = function(config){
		  if(config['power']){
			  return "checked";
		  }
		  return "";
	  }
	  
	  //得到CPU DIV
	  var getCpuDiv = function(config, num){
		  var div = '<div class="tab-pane fade" id="tab_' + num + '_2">' + 
		  				'<div class="form-group col-md-12">' + 
		  					'<label class="control-label mb5">' + LANG.UI_EMERGENCY_RECOVERY_CPU + '<span class="required">*</span></label>' + 
							'<div class="spinnerCPU">' + 
								'<div class="input-group" style="width:150px;">' + 
									'<input type="text" class="spinner-input form-control" maxlength="3" >' + 
									'<div class="spinner-buttons input-group-btn">' + 
										'<button type="button" class="btn spinner-up default">' + 
										'<i class="fa fa-angle-up"></i>' + 
										'</button>' + 
										'<button type="button" class="btn spinner-down default">' + 
										'<i class="fa fa-angle-down"></i>' + 
										'</button>' + 
									'</div>' + 
								'</div>' + 
							'</div>' + 
						'</div>' + 
						'<div class="form-group col-md-12">' + 
		  					'<label class="control-label mb5">' + LANG.UI_EMERGENCY_RECOVERY_CPU_CORE + '<span class="required">*</span></label>' + 
							'<div class="spinnerCPUCore">' + 
								'<div class="input-group" style="width:150px;">' + 
									'<input type="text" class="spinner-input form-control spinnerCPUInput" maxlength="3" >' + 
									'<div class="spinner-buttons input-group-btn">' + 
										'<button type="button" class="btn spinner-up default">' + 
										'<i class="fa fa-angle-up"></i>' + 
										'</button>' + 
										'<button type="button" class="btn spinner-down default">' + 
										'<i class="fa fa-angle-down"></i>' + 
										'</button>' + 
									'</div>' + 
								'</div>' + 
							'</div>' + 
						'</div>' + 
		  			'</div>';
		  				
		  return div;
	  }
	  //得到内存DIV
	  var getMemoryDiv = function(config, num){
		  var div = '<div class="tab-pane fade" id="tab_' + num + '_3">' + 
						'<div class="form-group col-md-12">' + 
							'<label class="control-label mb5">' + LANG.UI_PUBLIC_MEMORY_SIZE + '(MB)：<span class="required">*</span></label>' + 
							'<div class="spinnerMemory">' + 
								'<div class="input-group" style="width:150px;">' + 
									'<input type="text" class="spinner-input form-control">' + 
									'<div class="spinner-buttons input-group-btn">' + 
										'<button type="button" class="btn spinner-up default">' + 
										'<i class="fa fa-angle-up"></i>' + 
										'</button>' + 
										'<button type="button" class="btn spinner-down default">' + 
										'<i class="fa fa-angle-down"></i>' + 
										'</button>' + 
									'</div>' + 
									'<div></div>' + 
								'</div>' + 
							'</div>' + 
						'</div>' + 
					'</div>';
			
		  return div;
	  }
	  //得到存储DIV
	  var getStorageDiv = function(config, num){
		  var div = '<div class="tab-pane fade" id="tab_' + num + '_4">' + 
						'<div class="portlet-body">' + 
							'<div class="table-scrollable">' + 
								'<table class="table table-striped table-bordered table-advance table-hover table-tac">' + 
								'<thead>' + 
								'<tr>' + 
									'<th>' + LANG.UI_VM_SETTING_DISK_NAME + '</th>' + 
									'<th class="hidden-xs">' + LANG.UI_PUBLIC_TOTAL_SIZE + '</th>' + 
									'<th>' + LANG.UI_VM_SETTING_DIS_STORAGE + '</th>' + 
								'</tr>' + 
								'</thead>' + 
								'<tbody>';
		  for(var i=0; i<config.storage.length; i++){
			  div += '<tr id="' + config.storage[i]['size'] + '">' + 
						'<td class="highlight">' + config.storage[i]['vdi_name'] + '</td>' + 
						'<td class="hidden-xs">' + config.storage[i]['virtual_size'] + '</td>' + 
						'<td class="vmstorage" id="' + config.storage[i]['vdi_uuid'] + '"><select class="form-control select2me" name="hoststorage"></select></td>' + 
				  	 '</tr>';
		  }
		  
		  div += '</tbody></table></div></div></div>';
		  
		  return div;
				
	  }
	  
	  //得到网络DIV
	  var getNetworkDiv = function(config, num){
		  var div = '<div class="tab-pane fade" id="tab_' + num + '_5">' + 
						'<div class="portlet-body">' + 
							'<div class="table-scrollable">' + 
								'<table class="table table-striped table-bordered table-advance table-hover table-tac">' + 
								'<thead>' + 
								'<tr>' + 
									'<th>' + LANG.UI_VM_SETTING_CARD + '</th>' + 
									'<th class="hidden-xs">' + LANG.UI_VM_SETTING_MAC + '</th>' + 
									'<th>' + LANG.UI_VM_SETTING_SAVE_MAC + '</th>' + 
									'<th>' + LANG.UI_VM_SETTING_DIS_NETWORK + '</th>' + 
								'</tr>' + 
								'</thead>' + 
								'<tbody>';
		  for(var i=0; i<config.network.length; i++){
			  div += '<tr class="vmnetwork">' + 
						'<td class="highlight nwname">' + config.network[i]['net_num'] + '</td>' + 
						'<td class="hidden-xs nwmac">' + config.network[i]['mac'] + '</td>' + 
						'<td><input type="checkbox" class="icheck" id=""></td>' + 
						'<td><select class="form-control" name="hostnetwork"></select></td>' +
				  	 '</tr>';
		  }
		  
		  div += '</tbody></table></div></div></div>';
		  
		  return div;
	  }
	  
	  //得到目的宿主机
	  var getHostDiv = function(config, num){
		  var option = '';
		  for(var i=0; i<config.host.length; i++){
			  option += '<option value="' + config.host[i].value + '">' + config.host[i].text + '</option>';
		  }
		  var div = '<div class="tab-pane fade" id="tab_' + num + '_6">' + 
						'<div class="form-group col-md-12">' + 
							'<label class="control-label mb5">' + LANG.UI_PUBLIC_DESTINATION_HOST + '：<span class="required">*</span></label>' + 
							'<div>' + 
								'<select class="form-control" name="orchdeshost">' + option + '</select>' + 
							'</div>' + 
						'</div>' + 
					'</div>';
		  
		  return div;
	  }
	  
	  
	  return this.each(function(){
		  var _this = $(this);
		  parentID = _this.get(0).id;
		  var vmInfo = '';
		  for(var i=0; i<option.config.length; i++){
			  vmInfo += getVMDiv(option.config[i], i);
		  }
		  _this.empty().html(vmInfo);
		  //初始化bootstrap-switch
		  _this.find('input[name=vmpower]').bootstrapSwitch();
		  //初始化ickeck
		  _this.find('.icheck').iCheck({
	    	    checkboxClass: 'icheckbox_square-blue',
	    	    radioClass: 'iradio_square-blue',
	    	    increaseArea: '20%' // optional
	      });
		  //初始化spinner
		  var spinnerCPU = _this.find('.spinnerCPU');
		  var spinnerCPUCore = _this.find('.spinnerCPUCore');
		  var spinnerMemory = _this.find('.spinnerMemory');
		  for(var i=0; i<option.config.length; i++){
			  $(spinnerCPU[i]).spinner({value:option.config[i].cpu.cpunum, step: 1, min: 1, max: option.config[i].cpu.maxnum});
			  $(spinnerCPUCore[i]).spinner({value:option.config[i].cpu.cpucore, step: 1, min: 1, max: option.config[i].cpu.maxcore});
			  $(spinnerMemory[i]).spinner({value:option.config[i].memory.memorynum, step: 512, min: 512, max: option.config[i].memory.maxmemory});
		  }
		  
	  });
  }
  
  $.fn.getvmConfigOrch = function(option){
	  //得到是否影藏了某项
	  var getHideResult = function(type){
		  for(var i=0; i<option.hide.length; i++){
			  if(type == option.hide[i]){
				  return true;
			  }
		  }
		  return false;
	  }
	  //获取最终配置信息
	  var getResultInfo = function(_this){
		  var configsdiv = _this.find('.configsdiv');
		  var info = [];
		  for(var i=0; i<configsdiv.length;i++){
			  info.push(getEachVMConfig(configsdiv[i]));
		  }
		  return info;
	  }
	  
	  //得到每台虚拟机配置
	  var getEachVMConfig = function(configsdiv){
		  var div = $(configsdiv);
		  var vm = {};
		  vm.vmname = $.trim(div.find('input[name=vmname]').val());
		  vm.vmuuid = $.trim(div.find('input[name=vmuuid]').val());
		  vm.vcenteruuid = $.trim(div.find('input[name=vcenteruuid]').val());
		  vm.childuuid = $.trim(div.find('input[name=childuuid]').val());
		  vm.power = div.find('input[name=vmpower]').bootstrapSwitch('state');
		  vm.storage = getStorageConfig(div);
		  vm.network = getNetworkConfig(div);
		  vm.cpu = getCPUConfig(div);
		  vm.memory = getMemoryConfig(div);
		  vm.host = getHostConfig(div);
		  return vm;
	  }
	  
	  //得到CPU配置
	  var getCPUConfig = function(div){
		  if(getHideResult('cpu')) return '';
		  var cpuInfo = {};
		  cpuInfo.cpunum = div.find('.spinnerCPU').spinner('value');
		  cpuInfo.cpucore = div.find('.spinnerCPUCore').spinner('value');
		  return cpuInfo;
	  }
	  
	  //得到内存配置
	  var getMemoryConfig = function(div){
		  if(getHideResult('memory')) return '';
		  return div.find('.spinnerMemory').spinner('value');
	  }
	  
	  //得到存储配置
	  var getStorageConfig = function(div){
		  if(getHideResult('storage')) return '';
		  var storage = [];
		  var vmstorage = div.find('.vmstorage');
		  for(var i=0; i<vmstorage.length; i++){
			  var data = {};
			  //磁盘uuid,磁盘大小,选择存储uuid,是否自动选择存储
			  data.src_disk_uuid = vmstorage[i].id;
			  var tr = $(vmstorage[i]).parent().get(0);
			  data.size = Number(tr.id);
			  var storageuuid = $(vmstorage[i]).find('select[name=hoststorage]').val();
			  if("0" == storageuuid){
				  //自动选择存储
				  data.auto_conf_flag = 1;
				  data.target_storage_uuid = '';
			  }else{
				  data.auto_conf_flag = 2;
				  data.target_storage_uuid = storageuuid;
			  }
			  storage.push(data);
		  }
		  return storage;
	  }
	  
	  //得到网络配置
	  var getNetworkConfig = function(div){
		  if(getHideResult('network')) return '';
		  var network = [];
		  var vmnetwork = div.find('.vmnetwork');
		  for(var i=0; i<vmnetwork.length; i++){
			  var data = {};
			  var vnw = $(vmnetwork[i]);
			  //网卡,mac,保留mac标志,选择网卡uuid,是否自动选择
			  data.src_network_name = vnw.find('.nwname').html();
			  data.mac_addr = vnw.find('.nwmac').html();
			  data.keep_mac_flag = vnw.find('input').get(0).checked;
			  data.keep_mac_flag ? data.keep_mac_flag = 1 : data.keep_mac_flag = 2;
			  data.target_network_uuid = vnw.find('select[name=hostnetwork]').val();
			  if("0" == data.target_network_uuid){
				  //自动选择存储
				  data.auto_conf_flag = 1;
				  data.target_network_uuid = '';
			  }else{
				  data.auto_conf_flag = 2;
			  }
			  network.push(data);
		  }
		  return network;
	  }
	  
	  //得到宿主机配置
	  var getHostConfig = function(div){
		  if(getHideResult('host')) return '';
		  var host = {};
		  var deshost = div.find('select[name=orchdeshost]');
		  var value = $(deshost).val();
		  var text = $(deshost).find("option:selected").text();
		  if(!value) value = '';
		  host = {value:value, text:text};
		  return host;
	  }
	  
	  
	  return getResultInfo($(this));
  }
  
})(jQuery)