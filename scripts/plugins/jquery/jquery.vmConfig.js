(function($){
  $.fn.vmConfig = function(options){
	  var defaults = {
		  //多维数组,每一项表示每一个虚拟机的配置
		  'config' : [],
	  }
	  var option = $.extend(defaults,options);
	  var parentID = '';
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
									'<li class="">' + 
										'<a href="#tab_' + num + '_2" data-toggle="tab" aria-expanded="false">' + 
										LANG.UI_VM_SETTING_STORAGE + ' </a>' + 
									'</li>' + 
									'<li class="">' + 
										'<a href="#tab_' + num + '_3" data-toggle="tab" aria-expanded="false">' + 
										LANG.UI_VM_SETTING_NETWORK + ' </a>' + 
									'</li>';
			
		  if(config.hypervisor == 14 || config.hypervisor == 15 || config.hypervisor == 22){
			  div +=  '<li class="">' + 
						  '<a href="#tab_' + num + '_4" data-toggle="tab" aria-expanded="false">' + 
						  LANG.UI_PUBLIC_USE_DOMAIN + ' </a>' + 
					  '</li>';
		  }
		  div += '</ul>' + '</div>';
		  
		  
		  //名称和状态
		  div += '<div class="col-md-9 col-sm-9 col-xs-9">' + 
					'<div class="tab-content">' + 
						'<div class="tab-pane active in" id="tab_' + num + '_1">' + 
							'<div class="form-group col-md-12">' + 
								'<label class="control-label mb5">' + LANG.UI_VM_SETTING_REC_NAME + '：<span class="required">*</span></label>' + 
								'<div class="input-icon right">' + 
									'<i class="fa"></i>' + 
									'<input type="text" maxlength="60" class="form-control" value="' + config['vmname'] + '" name="vmname"/> ' + 
								'</div> ' + 
							'</div>' + 
							'<div class="form-group col-md-12">' + 
								'<label class="control-label mb5">' + LANG.UI_VM_SETTING_REC_POWER + '：<span class="required">' + 
								'* </span></label>' + 
								'<div>' + 
									'<input type="checkbox" name="vmpower" class="make-switch" data-on-color="primary" data-off-color="info" ' + 
									'data-on-text="' + LANG.UI_PUBLIC_ON_ONE + '" ' + 
									'data-off-text="' + LANG.UI_PUBLIC_OFF_ONE + '">' + 
								'</div>' + 
							'</div>' + 
						'</div>';
		  //存储
//		  if(config.hypervisor == 14 || config.hypervisor == 15 || config.hypervisor == 22){
//			  div += getOpenstackStorageDiv(config, num);
//		  }else{
//			  div += getStorageDiv(config, num);
//		  }
		  div += getStorageDiv(config, num);
		  //网络
		  div += getNetworkDiv(config, num);

		  //可用域
		  if(config.hypervisor == 14 || config.hypervisor == 15 || config.hypervisor == 22){
			  div += getUsedDomain(config, num);
 
		  }
		  
		  //封顶
		  div += '</div></div></div></div></div></div>';
		  
		  return div;
	  }
	  
	  var getUsedDomain = function(config, num){
		  var div = '<div class="tab-pane fade" id="tab_' + num + '_4">' + 
						'<div class="portlet-body">' + 
							'<label class="control-label mb5">' + LANG.UI_PUBLIC_USE_DOMAIN + '：<span class="required">*</span></label>' + 
							'<select class="form-control select2me width200" name="usedomain"></select>' +
						'</div>' +
					'</div>';
		  return div;
	  }
	  
	  
	  //得到openstack的存储列表
	  var getOpenstackStorageDiv = function(config, num){
		  var div = '<div class="tab-pane fade" id="tab_' + num + '_2">' + 
			'<div class="portlet-body">' + 
				'<div class="table-scrollable">' + 
					'<table class="table table-striped table-bordered table-advance table-hover table-tac getHoststorage">' + 
					'<thead>' + 
					'<tr>' + 
						'<th>' + LANG.UI_VM_SETTING_DISK_NAME + '</th>' + 
						'<th class="hidden-xs">' + LANG.UI_PUBLIC_TOTAL_SIZE + '</th>' + 
						'<th>' + LANG.UI_VM_SETTING_DIS_STORAGE + '</th>' + 
					'</tr>' + 
					'</thead>' + 
					'<tbody>';
		  for(var i=0; i<config.storage.length; i++){
			  var disableTips = "";
			  if(config.storage[i]['bootable'] == true && i == 0|| config.storage[i]['storage_type'] == 'eph0' && i== 1){
				  disableTips = 'disabled = "disabled"';
			  }
			  div += '<tr id="' + config.storage[i]['size'] + '">' + 
			  '<input type="checkbox" class="display-none" checked="checed" id="' + config.storage[i]['vdi_uuid'] +'" value="'+ config.storage[i]['vdi_name'] + '">'+
						'<td class="highlight">' + config.storage[i]['vdi_name'] + '</td>' + 
						'<td class="hidden-xs">' + config.storage[i]['virtual_size'] + '</td>' + 
						'<td class="vmstorage" id="' + config.storage[i]['vdi_uuid'] + '"><select class="form-control select2me" name="hoststorage" '+disableTips+'></select></td>' + 
					'</tr>';
		  }
			
		  div += '</tbody></table></div></div></div>';
			
		  return div;
	  }
	  //得到存储DIV
	  var getStorageDiv = function(config, num){
		  var div = '<div class="tab-pane fade" id="tab_' + num + '_2">' + 
						'<div class="portlet-body" >' + 
							'<div class="table-scrollable">' + 
								'<table id="vmstorage'+config.vmuuid+'" class="table table-striped table-bordered table-advance table-hover table-tac getHoststorage">' + 
								'<thead>' + 
								'<tr>' + 
									'<th width="5%">' + LANG.UI_VM_SETTING_DISK_CHECK + '</th>' +
									'<th width="25%">' + LANG.UI_VM_SETTING_DISK_NAME + '</th>' + 
									'<th class="hidden-xs" width="5%">' + LANG.UI_PUBLIC_TOTAL_SIZE + '</th>' + 
									'<th width="35%">' + LANG.UI_VM_SETTING_DIS_STORAGE + '</th>' + 
									'<th width="30%" class="diskth display-none">' + LANG.UI_VM_SETTING_DISK_TYPE + '</th>' +
								'</tr>' + 
								'</thead>' + 
								'<tbody>';
		  for(var i=0; i<config.storage.length; i++){
			  div += '<tr id="' + config.storage[i]['size'] + '">' + 
			  			'<td class="hidden-xs"><input type="checkbox" class="icheck" checked="checed" id="' + config.storage[i]['vdi_uuid'] +'" value="'+ config.storage[i]['vdi_name'] + '"></td>' +
						'<td class="highlight">' + config.storage[i]['vdi_name'] + '</td>' + 
						'<td class="hidden-xs">' + config.storage[i]['virtual_size'] + '</td>' + 
						'<td class="vmstorage storage-active" id="' + config.storage[i]['vdi_uuid'] + '"><select class="form-control select2me" name="hoststorage"></select></td>' + 
						'<td class="vmdisktype display-none" id="' + config.storage[i]['vdi_uuid'] + '"><select class="form-control select2me ' + config.storage[i]['vdi_uuid'] +'" name="disktype"></select></td>' +
				  	 '</tr>';
		  }
		  
		  div += '</tbody></table></div></div></div>';
		  
		  return div;
				
	  }
	  
	  //得到网络DIV
	  var getNetworkDiv = function(config, num){
		  var div = '<div class="tab-pane fade" id="tab_' + num + '_3">' + 
						'<div class="portlet-body">' + 
							'<div class="table-scrollable">' + 
								'<table class="table table-striped table-bordered table-advance table-hover table-tac getHostnetwork">' + 
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
		  
	  });
  }
  
  $.fn.getvmConfig = function(options){
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
		  vm.vmname = div.find('input[name=vmname]').val();
		  vm.power = div.find('input[name=vmpower]').bootstrapSwitch('state');
		  vm.storage = getStorageConfig(div);
		  vm.network = getNetworkConfig(div);
		  vm.domain = getDomainConfig(div);
		  return vm;
	  }
	  
	  //得到可用域
	  var getDomainConfig = function(div){
		  var data = {};
		  var domainDiv = div.find('select[name=usedomain]');
		  for(var i=0;i<domainDiv.length;i++){
			  var domain = $(domainDiv[i]).val();
			  if("0" == domain){
			      //自动选择存储
				  data.auto_conf_flag = 1;
				  data.zone_name = '';
			  }else{
				  data.auto_conf_flag = 2;
				  data.zone_name = domain;
			  }
		  }
		  return data;
	  }
	  
	  //得到存储配置
	  var getStorageConfig = function(div){
		  var storage = [];
		  var vmstorage = div.find('.vmstorage');
		  var vmdisktype = div.find('.vmdisktype');
		  for(var i=0; i<vmstorage.length; i++){
			  var data = {};
			  var sId = escapeJquery(vmstorage[i].id);
			  if(!$('#'+ sId).is(":checked")){
				  continue;
			  }
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
			  data.disk_type = parseInt($(vmdisktype[i]).find('select[name=disktype]').val());
			  storage.push(data);
		  }
		  return storage;
	  }
	  
	  //得到网络配置
	  var getNetworkConfig = function(div){
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
	  
	  return getResultInfo($(this));
  }
  
})(jQuery)