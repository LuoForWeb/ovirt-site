(function($){
  $.fn.vmConfigValidate = function(options){
	  var defaults = {
		  'vmconfig' : [],		//虚拟机最终配置
		  'hoststorage' : [], 	//宿主机存储信息
		  'hypervisor' : '',    //虚拟化类型
		  'nameLimit': {}, //虚拟机名或磁盘名称限制
		  'instantFlag': false, // 瞬时恢复标志
	  }
	  var option = $.extend(defaults,options);
	  var storageList = [], storageTotalSize = 0;		//存储列表
	  var discList = [];		//磁盘列表
	  var validateResult = function(){
		  //检测
		  if(!options.instantFlag && !checkHostStorage()) return false;
		  if(!checkVMConfg()) return false;
		  storageList = JSON.parse(JSON.stringify(option.hoststorage.storage));
		  for(var i=0; i<storageList.length; i++){
			  storageList[i]['freesize'] = Number(storageList[i]['freesize']);
			  storageTotalSize += storageList[i]['freesize'];
		  }
		  var vmconf = option.vmconfig;
		  var hypervisor = option.hypervisor;

		  var host_name_limit = {
			  "Linux":{
				  'len': '63',
				  'limit':'^(?![0-9]+$)[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?$',
				  'msg':LANG.UI_MACHINE_OS_HOSTNAME_MSG_63
			  },
			  "Windows":{
				  'len': '15',
				  'limit':'^(?![0-9]+$)(?!(CON|AUX|COM[1-9]|LPT[1-9]|PRN|NUL)$)[A-Za-z0-9](?:[A-Za-z0-9-]{0,13}[A-Za-z0-9])?$',
				  'msg':LANG.UI_MACHINE_OS_HOSTNAME_MSG_15
			  }
		  }

		  for (var i = 0; i < vmconf.length; i++) {
			  //判断磁盘名称长度限制
			  if (option.nameLimit.disk_len) {
				  discList = discList.concat(vmconf[i]['storage']);
				  for (var j = 0; j < discList.length; j++) {
					  if (discList[j].disk_name.length > option.nameLimit.disk_len) {
						  UIToastr.showWarning(LANG.UI_VM_SETTING_DISK_NAME, option.nameLimit.disk_msg);
						  return false;
					  }
				  }
			  }

			  // 判断重置主机名是否为空
			  let otherConfig = vmconf[i].other;
			  if (otherConfig.reset_hostname && '' === $.trim(otherConfig.new_hostname)) {
				  UIToastr.showWarning(LANG.UI_VM_RESET_HOST_NAME, LANG.UI_VM_RESET_HOST_NAME_EMPTY_TIPS);
				  return false;
			  } else {
				  // 重置主机名是否符合规则
				  let os_type = vmconf[i].os_type;
				  if (otherConfig.reset_hostname && typeof host_name_limit[os_type] !== 'undefined') {
					  let hostReg = new RegExp(host_name_limit[os_type].limit);
					  if (otherConfig.new_hostname.length > host_name_limit[os_type].len) {
						  UIToastr.showWarning(LANG.UI_OS_DETAILS_HOST_NAME, LANG.UI_OS_DETAILS_HOST_NAME + host_name_limit[os_type].msg);
						  return false;
					  }
					  if (!hostReg.test(otherConfig.new_hostname)) {
						  UIToastr.showWarning(LANG.UI_OS_DETAILS_HOST_NAME, LANG.UI_MACHINE_OS_HOSTNAME_TIPS);
						  return false;
					  }
				  }
			  }
		  }

		  var openstackList = [14,15,22];
		  if($.inArray(hypervisor, openstackList) != -1){
			 return true;
		  }
		  //检测所有存储可用空间是否满足所有磁盘需要
//		  if(!checkTotalSize()){
//			  UIToastr.showWarning(LANG.UI_VM_SETTING_STORAGE_NOT_ENOUGH, LANG.UI_VM_SETTING_STORAGE_NOT_ENOUGH_TIPS);
//			  return false;
//		  }
		  //检测手动选择的存储是否满足空间要求
//		  if(!checkEachStorageSize()){
//			  return false;
//		  }
		  return true;
		  
	  }
	  
	  //检测可用存储
	  var checkHostStorage = function(){
		  var storage = option.hoststorage.storage;
		  if(!storage || storage.length < 1){
			  UIToastr.showWarning(LANG.UI_VM_SETTING_STORAGE_LACK, LANG.UI_VM_SETTING_STORAGE_LACK_TIPS);
			  return false;
		  }
		  return true;
	  }
	  
	  //检测虚拟机配置
	  var checkVMConfg = function(){
		  if(0 == option.vmconfig.length){
			  UIToastr.showWarning(LANG.UI_VM_SETTING_GET_VM_SETTING_FAILURE, LANG.UI_VM_SETTING_GET_VM_SETTING_FAILURE_TIPS);
			  return false;
		  }
		  for(var i=0; i<option.vmconfig.length; i++){
			  if(0 == option.vmconfig[i].storage.length){
//				  UIToastr.showWarning(LANG.UI_VM_SETTING_GET_STORAGE_FAILURE, LANG.UI_VM_SETTING_GET_STORAGE_FAILURE_TIPS);
				  return true;
			  }
			  if(0 == option.vmconfig[i].network.length){
				  return true;//允许网卡为空
				  UIToastr.showWarning(LANG.UI_VM_SETTING_GET_NETWORK_FAILURE, LANG.UI_VM_SETTING_GET_NETWORK_FAILURE_TIPS);
				  return false;
			  }
		  }
		  return true;
	  }
	  
	  //检测所有存储可用空间是否满足所有磁盘需要
	  var checkTotalSize = function(){
		  var discTotalSize = 0;
		  for(var i=0; i<discList.length; i++){
			  discTotalSize += discList[i]['size'];
		  }
		  if(discTotalSize > storageTotalSize){
			  return false;
		  }
		  return true;
	  }
	  
	  //检测手动选择的存储是否满足空间要求
	  var checkEachStorageSize = function(){
		  for(var i=0; i<discList.length; i++){
			  if("" != discList[i]['target_storage_uuid']){
				  //如果是手动选择存储
				  for(var j=0; j<storageList.length; j++){
					  if(discList[i]['target_storage_uuid'] == storageList[j]['uuid']){
						  var residue = storageList[j]['freesize'] - discList[i]['size'];
						  if(residue >= 0){
							  //满足条件,更新剩余空间
							  storageList[j]['freesize'] = residue;
						  }else{
							  UIToastr.showWarning(LANG.UI_VM_SETTING_STORAGE_LACK, storageList[j]['text'] + LANG.UI_VM_SETTING_ON_STORAGE_TIPS);
							  return false;
						  }
					  }
				  }
			  }
		  }
		  return true;
	  }
	  
	  return validateResult();
  }
  
})(jQuery)