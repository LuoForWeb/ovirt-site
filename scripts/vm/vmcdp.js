var VMCDP = function () {
	var data = {srcInfo:{},backupInfo:{},highInfo:{}};
	//定义三棵树:主机和虚拟机,主机和集群,虚拟机和模板,搜索到的树节点
	var zTree, zTreeHC, zTreeVT, nodeParamList;
	var currentTree, zTreeLoad = zTreeHCLoad = zTreeVTLoad = false;	//当前显示的树,三棵树是否已经加载过的标志
	var currentTreeDivId;	//用于搜索时候定位
	var _timeStamp = '';	//时钟时间戳,全局
	var nodeSelectFlag = false; //自定义节点选择加载标志
	var _VMNAMEREG = new RegExp("[ \n\r\t\f`~!@#$^&*()=|{}':;',\\[\\].<>/?~！@#￥……&*（）&;|{}【】‘；：”“'。，、？+-]");
	
	var initData = function(){
		//setp1
		data.srcInfo.vminfo = [];
		//disklist
		data.srcInfo.disklist =[];
		//setp2
		//备份方式:策略/时间
		data.backupInfo.type = '1';
		//按时间备份的时间
		data.backupInfo.datetime = null;
		//setp3
		//保留策略
		data.highInfo.reserve = {};
		data.highInfo.reserve.type = 2;
		data.highInfo.transfer = {};
		data.highInfo.store = {};
		data.highInfo.node = {};
		data.highInfo.mode = {};
		
	}
	
	//初始化监听事件
	var initListener = function(){
		$('#backuptype').on('change', backupTypeHandler);
		$('#reservetype').on('change', reserveTypeHandler);
		$('#resetdate').on('click',function(){
			$('#oncetime').val('');
		});
		$('#toaddvcenter').on('click',function(){
	    	LOCATION('./content/vm/add_vcenter.php', 'vcenter_manager');
		});
		
		//选择节点
		$('#autonodecheck').on('switchChange.bootstrapSwitch', autoNodeCheckChange);
		//选择存储
		$('#autostoragecheck').on('switchChange.bootstrapSwitch', autostorageCheckChange);
		//节点改变
		$('#selectnode').on('change', initStorageSelect);
		//选择备份策略复选框
		$('#strategymode').find('.icheck').on('ifClicked', strategyModeClick);
		//启动内联数据消重
		$('#parsefscheck').on('switchChange.bootstrapSwitch', parsefsChange);
		//XenServer传输模式和增量模式关联
		$('#xentransmode').on('change', xentransModeChange);
	}
	
	//版本差异处理,主要是处理标准版本功能限制
	//标准版本限制功能重复数据删除,深度有效数据提取
	var initSoftwareVersionDiff = function(){
		if(1 != CONF.SOFTWARE) return true;
		//设置重删为关闭并不可用
		$('#deduplicationcheck').bootstrapSwitch('state', false);
		$('#deduplicationcheck').bootstrapSwitch('disabled', true);
		//设置有效数据提取关闭并不可用
		$('#parsefscheck').bootstrapSwitch('state', false);
		$('#parsefscheck').bootstrapSwitch('disabled', true);
	}
	
	//XenServer传输模式改变
	var xentransModeChange = function(){
		var mode = $(this).val();
		if(3 == mode || 4 == mode){
			$('#incmodel').val(3).prop('disabled', true);
		}else{
			$('#incmodel').val(2).prop('disabled', false);
		}
	}
	
	//选中或取消某个时间策略
	var strategyModeClick = function(event){
		var mode = $(this).data('mode');
		var setDisabled = "disable";
		if(event.target.checked){
			//如果是取消选中
			$('#stragegyaccordion').find('.strategy-panel[data-mode=' + mode + ']').hide();
			setDisabled = "enable";
		}else{
			//如果是选中
			$('#stragegyaccordion').find('.strategy-panel[data-mode=' + mode + ']').show();
			setDisabled = "disable";
		}
		if(2 == mode){
			//增量备份
			$('#strategymode').find('input[data-mode=3]').iCheck(setDisabled);
		}else if(3 == mode){
			//差异备份
			$('#strategymode').find('input[data-mode=2]').iCheck(setDisabled);
		}
	}
	
	//是否启动V-CBT
	var parsefsChange = function(){
		if(this.checked){
			$('#swapcheck').bootstrapSwitch('state', true);  
			$('#deletefilecheck').bootstrapSwitch('state', true);  
			$('#gapcheck').bootstrapSwitch('state', true);  
			
			$('.swapdiv').show();
			$('.deletefilediv').show();
			$('.gapdiv').show();
			
			$('.swapmodeshow').show();
			$('.deletefilemodeshow').show();
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
	
	//自动选择节点switch
	var autoNodeCheckChange = function(){
		if(this.checked){
			//自动选择节点
			$('.diynodediv').hide();
			$('.autostoragediv').hide();
			$('.diystoragediv').hide();
		}else{
			//自定义节点
			initNodeSelect();
			$('.diynodediv').show();
			$('.autostoragediv').show();
			$('#autostoragecheck').bootstrapSwitch('state', true);
			$('.diystoragediv').hide();
		}
	}
	//自动选择存储switch
	var autostorageCheckChange = function(){
		if(this.checked){
			$('.diystoragediv').hide();
		}else{
		    initStorageSelect();
			$('.diystoragediv').show();
		}
	}
	
	//初始化节点下拉框
	var initNodeSelect = function(){
		if(nodeSelectFlag) return; //加载一次
		$.post(CONF.AJAXPATH, {m:CONF.M.NODE,f:'getAddStorageNodeSelect',p:{}}, function(d){
    		var data = JSON.parse(d);
    		if(!data.length){
    			$('#autostoragecheck').bootstrapSwitch('disabled', true);
    		}else{
    			$('#autostoragecheck').bootstrapSwitch('disabled', false);
    			$('#autostoragecheck').bootstrapSwitch('state', true);
    		}
    		var softselect = $('#selectnode');
    		softselect.empty();
			for(var i=0; i<data.length; i++){
				var option = $("<option>").text(data[i].text).val(data[i].uuid);
				softselect.append(option);
			}
			nodeSelectFlag = true;
			initStorageSelect();  //初始化存储下拉框
    	});
	}
	
	//初始化存储下拉框
	var initStorageSelect = function(){
		var data = {};
		data.nodeuuid = $('#selectnode').val();
		var jsonData = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.STORAGE,f:'getBackupStorageList',p:jsonData}, function(d){
    		var data = JSON.parse(d);
    		var softselect = $('#selectstorage');
    		softselect.empty();
			for(var i=0; i<data.length; i++){
				var option = $("<option>").text(data[i].text).val(data[i].uuid);
				softselect.append(option);
			}
    	});
	}
	
	var initSpinner = function(){
		$('#spinnerDay').spinner({value:30, step: 5, min: 1, max: 999});
        $('#spinnerNum').spinner({value:30, step: 5, min: 1, max: 999});
	}
	
	var reserveTypeHandler = function(){
		if(CONF.RESERVE_TYPE.NUM == this.value){
			$('.reserveNum').show();
			$('.reserveDay').hide();
		}else if(CONF.RESERVE_TYPE.DAY == this.value){
			$('.reserveNum').hide();
			$('.reserveDay').show();
		}
		data.highInfo.reserve.type = parseInt(this.value);
	}
	
	//备份类型
	var backupTypeHandler = function(){
		if('1' == this.value){
			//按策略备份
			$('.setOnceTime').hide();
		}else if('2' == this.value){
			//一次性备份
			$('.setOnceTime').show();
		}
		data.backupInfo.type = this.value;
		$('.settimetip').hide();
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
		var nodes = currentTree.getCheckedNodes();
		if(!nodes.length){
			$(".selectvmtip").html(LANG.UI_BACKUP_SELECT_VM_TIPS).show();
			return false;
		}
		var showStr = '';
		data.srcInfo.disk = new Array();
		//添加disk_list和最后一步显示已排除磁盘列表
		for(var i=0;i<nodes.length;i++){
			data.srcInfo.disk[i] = "";
			data.srcInfo.disklist[i] = [];
			if(!nodes[i].detail || nodes[i].detail == "" || !nodes[i].detail['disk_list']) continue;
			var diskCount =nodes[i].detail['disk_list'].length;
			var disk_list = [];
			for(var j=0;j<diskCount;j++){
				var diskInfo = {};
				if($('#disCheck'+j+ escapeJquery(nodes[i].id) + escapeJquery(nodes[i].vcenteruuid)).bootstrapSwitch('state') == false){
					data.srcInfo.disk[i] += "<br>"+ LANG.UI_BACKUP_EXCLUDE_DISK + $('#disCheck' + j + escapeJquery(nodes[i].id) + escapeJquery(nodes[i].vcenteruuid)).val();
					diskInfo.disk_name = $('#disCheck' + j + escapeJquery(nodes[i].id) + escapeJquery(nodes[i].vcenteruuid)).val();
					diskInfo.disk_uuid = $('#disuuid' +  j + escapeJquery(nodes[i].id) + escapeJquery(nodes[i].vcenteruuid)).text();
					disk_list.push(diskInfo);
				}
			}
			data.srcInfo.disklist[i] ={
					'disk_list' : disk_list,
			};
		}
		data.srcInfo.vminfo = [];
		$.each(nodes, function(i, d){
			if('vm' == d.eventtype){
				data.srcInfo.vminfo[i] = {vmuuid:d.id, vcuuid:d.vcenteruuid , path:d.path , vm_config: data.srcInfo.disklist[i]};
				showStr += d.path + data.srcInfo.disk[i] + "<br>";
				data.srcInfo.vmType = d.hypervisor;
			}
			i++;
		})
		$(".selectvmtip").hide();
		
		//默认带静默快照选项
		
		$('.snapshotdiv').show();	
		$('.backupmodeshow2').show();
		$('#cbtcheck').bootstrapSwitch('state', true);  //cbt默认开启
		
		$('#parsefscheck').bootstrapSwitch('state', true); //内联数据消重默认开启
		
		$('.showBackupmode').show();						//默认显示备份模式tab
		$('#backupmodeshowdiv').show();                     //默认显示备份模式信息
		
		$('.parsefsDiv').show();                           //显示VCBT
		$('.parsefsmodeshow').show();                      //显示VCBT描述
		
		//先隐藏传输模块
		$('.huaweitransportdiv').hide();                    //华为传输模式类型
		$('.leixentransdiv').hide();                           //类xen传输模式
		$('.xentransdiv').hide();                           //XenServer传输模式
		
		$('.cbtdiv').hide();								//隐藏cbt
		$('.cbtmodeshow').hide();                            //隐藏cbt信息 
		$('#deletefilecheck').bootstrapSwitch('state', false);  //排除删除文件块默认关闭
		$('#incmodeldiv').hide();
		
//		$('#fullBackup').iCheck('check');             //由于除了vmware模块以外的虚拟化不支持永久增量备份，强制勾选完备
//		$('#fullBackup').iCheck('disable');
		switch(data.srcInfo.vmType){
			case CONF.VM_TYPE.VMWARE:
			case CONF.VM_TYPE.CLOUDVIEW:
			case CONF.VM_TYPE.CLOUDVIEWSVM:
			case CONF.VM_TYPE.HYPERV:
			case CONF.VM_TYPE.XHERE:
//				$('#fullBackup').iCheck('uncheck');             //由于除了vmware模块以外的虚拟化不支持永久增量备份，强制勾选完备
//				$('#fullBackup').iCheck('enable');
				$('#backupmodediv').hide();							//隐藏备份模式
				$('.encrypttransferdiv').hide();					//隐藏加密传输
				$('.transportdiv').show();							//显示传输模式
				$('.backupmodeshow1').hide();						//隐藏高速模式
				$('.blocksizediv').show();							//允许修改数据块大小
				$('.cbtdiv').show();                     //显示cbt
				$('.cbtmodeshow').show();                //显示cbt信息 
				break;
			case CONF.VM_TYPE.CITRIX:
				$('#backupmodediv').hide();							//隐藏高速模式
				$('#incmodeldiv').show();							//显示增量模式
				$('#backupmodeshowdiv').show();						//显示备份模式确认
				$('.encrypttransferdiv').show();					//显示加密传输
				$('.transportdiv').hide();							//隐藏传输模式
				$('.backupmodeshow1').show();						//显示高速模式
				$('.blocksizediv').hide();							//隐藏修改数据块大小
				$('.xentransdiv').show();                           //XenServer传输模式
				break;
			case CONF.VM_TYPE.INCLOUD:
			case CONF.VM_TYPE.VGATE:
			case CONF.VM_TYPE.WINSERVER:
			case CONF.VM_TYPE.WINDIY:
			case CONF.VM_TYPE.DSERVER:
				$('#backupmodediv').show();							//显示高速模式
				$('#backupmodeshowdiv').show();						//显示备份模式确认
				$('.encrypttransferdiv').show();					//显示加密传输
				$('.transportdiv').hide();							//隐藏传输模式
				$('.backupmodeshow1').show();						//显示高速模式
				$('.blocksizediv').hide();							//隐藏修改数据块大小
				$('.leixentransdiv').show();                           //传输模式
				break;
			case CONF.VM_TYPE.FUSIONXEN:
				$('.transportdiv').hide();							//隐藏vmware传输模式
				$('.huaweitransportdiv').show();                    //华为传输模式类型
				$('#backupmodediv').hide();                          //隐藏高速模式
				$('.backupmodeshow1').hide();
				$('.blocksizediv').hide();							//隐藏修改数据块大小
				break;
			case CONF.VM_TYPE.H3C:
			case CONF.VM_TYPE.H3CCASCVD:
			case CONF.VM_TYPE.INCLOUDKVM:
			case CONF.VM_TYPE.INSPURVVDK:
				$('#backupmodediv').show();							//显示高速模式
				$('#backupmodeshowdiv').show();						//显示备份模式确认
				$('.encrypttransferdiv').show();					//显示加密传输
				$('.transportdiv').hide();							//隐藏传输模式
				$('.backupmodeshow1').show();						//显示高速模式
				$('.blocksizediv').hide();							//隐藏修改数据块大小
				$('.leixentransdiv').show();                           //传输模式
				
				$('#silentsnapshotcheck').bootstrapSwitch('state', true);  //静默快照默认开启
				$('.snapshotdiv').hide();							//隐藏静默快照选项
				$('.backupmodeshow2').hide();						//隐藏静默快照的描述
				break;
			case CONF.VM_TYPE.KVM:
			case CONF.VM_TYPE.SDCOS:
			case CONF.VM_TYPE.SANGFOR:
			case CONF.VM_TYPE.SANGFORVVDK:
				$('#backupmodediv').show();							//显示高速模式
				$('#backupmodeshowdiv').show();						//显示备份模式确认
				$('.encrypttransferdiv').show();					//显示加密传输
				$('.transportdiv').hide();							//隐藏传输模式
				$('.backupmodeshow1').show();						//显示高速模式
				$('.blocksizediv').hide();							//隐藏修改数据块大小
				
				$('#silentsnapshotcheck').bootstrapSwitch('state', true);  //静默快照默认开启
				$('.snapshotdiv').hide();							//隐藏静默快照选项
				$('.backupmodeshow2').hide();						//隐藏静默快照的描述
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
				$('#backupmodediv').show();							//显示高速模式
				$('#backupmodeshowdiv').show();						//显示备份模式确认
				$('.encrypttransferdiv').show();					//显示加密传输
				$('.transportdiv').hide();							//隐藏传输模式
				$('.leixentransdiv').show();                           //传输模式
				$('.backupmodeshow1').show();						//显示高速模式
				$('.blocksizediv').hide();							//隐藏修改数据块大小
				$('#parsefscheck').bootstrapSwitch('state', false); //内联数据消重默认关闭
				$('#silentsnapshotcheck').bootstrapSwitch('state', true);  //静默快照默认开启
				$('.snapshotdiv').hide();							//隐藏静默快照选项
				$('.backupmodeshow2').hide();						//隐藏静默快照的描述
				break;
			case CONF.VM_TYPE.NEOKYLIN:
			case CONF.VM_TYPE.RHV:
			case CONF.VM_TYPE.OVIRT:
			case CONF.VM_TYPE.ZVIRT:
			case CONF.VM_TYPE.OSEASYVSERVER:
			case CONF.VM_TYPE.HOSTVM:
			case CONF.VM_TYPE.REDVIRT:
			case CONF.VM_TYPE.ROSAVIRT:
				$('#backupmodediv').show();							//显示高速模式
				$('#backupmodeshowdiv').show();						//显示备份模式确认
				$('.encrypttransferdiv').show();					//显示加密传输
				$('.transportdiv').hide();							//隐藏传输模式
				$('.backupmodeshow1').show();						//显示高速模式
				$('.blocksizediv').hide();							//隐藏修改数据块大小
				$('.leixentransdiv').show();                           //传输模式
				
				$('#silentsnapshotcheck').bootstrapSwitch('state', true);  //静默快照默认开启
				$('.snapshotdiv').hide();							//隐藏静默快照选项
				$('.backupmodeshow2').hide();						//隐藏静默快照的描述
				break;
		}
		
		showStr += CONF.VM_DES[data.srcInfo.vmType] + LANG.UI_PUBLIC_BACKUP + "<br>";
		showStep1(showStr);
		return true;
	}
	
	var showStep1 = function(showStr){
		//初始化计时器
		initServerTime();
		var info = {};
		info.type = data.srcInfo.vmType;
		info = JSON.stringify(info);
		$.post(CONF.AJAXPATH, {m:CONF.M.VM,f:'getVMCDPTaskName',p:info}, function(d){
			$('#jobname').val(d);
		});
		$('.vmshow').html(showStr);
	}
	
	var step2Valid = function(){
		
		var backuptype = $('#backuptype').val();
		if("2" == backuptype){
			var onceTime = $('#oncetime').val();
			if("" != onceTime){
				$('.settimetip').hide();
				var systemTime = $('#servertime').text();
				var onceTimeSize = new Date(onceTime).getTime();
				var systemTimeSize = new Date(systemTime).getTime();
				if(onceTimeSize <= systemTimeSize){
					$('.settimetip').html(LANG.UI_BACKUP_SET_CORRENT_TIME_TIPS).show();
					return false;
				}
				data.backupInfo.datetime = onceTime;
				showStep2();
				return true;
			}else{
				$('.settimetip').html(LANG.UI_BACKUP_SET_TIME_TIPS).show();
				return false;
			}
			data.backupInfo.datetime = onceTime;
		}else{
			data.backupInfo.datetime = "";
		}
		
		data.backupInfo.type = backuptype;
		showStep2();
		return true;
		
	}
	
	var showStep2 = function(){
		var backuptypeshow = $('.backuptypeshow');
		var backuptypeshowStr = '';
		if("1" == data.backupInfo.type){
			backuptypeshowStr = LANG.UI_VM_MANUAL_START;
		}else if("2" == data.backupInfo.type){
			backuptypeshowStr = LANG.UI_VM_TIMING_START + ": " + data.backupInfo.datetime;
		}
		backuptypeshow.html(backuptypeshowStr);
	}
	
	
	var step3Valid = function(){
		var result = getReserveStr() & getAchiveStr() & getTransferStr() & getStoreStr() & getNodeStr() & getModeStr();
		if(result){
			showStep3();
		}
		return result;
	}
	
	var showStep3 = function(){
		var reservetypeshow = $('.reservetypeshow');
		var reservetypeStr = '';
		if(CONF.RESERVE_TYPE.NUM == data.highInfo.reserve.type){
			reservetypeStr = LANG.UI_STRATEGY_RESERVE_NUM;
		}else if(CONF.RESERVE_TYPE.DAY == data.highInfo.reserve.type){
			reservetypeStr = LANG.UI_STRATEGY_RESERVE_DAY;
		}
		reservetypeStr = reservetypeStr + "," + LANG.UI_STRATEGY_RESERVE_VALUE + data.highInfo.reserve.value;
		//保留策略
		reservetypeshow.html(reservetypeStr);
		
		//备份目的地(节点)
		var nodeInfo = '';
		if(data.highInfo.node.nodecheck){
			nodeInfo = $('.autonodelabel').html() + ": " + LANG.UI_PUBLIC_YES;
		}else{
			nodeInfo = $('.diynodelabel').html() + ": " + $('#selectnode option:selected').text();
			if(data.highInfo.node.storagecheck){
				nodeInfo += "<br>" + $('.autostoragelabel').html() + ": " + LANG.UI_PUBLIC_YES;
			}else{
				nodeInfo += "<br>" + $('.diystoragelabel').html() + ": " + $('#selectstorage option:selected').text();;
			}
		}
		$('.nodeinfoshow').html(nodeInfo);
		
		//存储策略
		var deduplicationlabel = $('.deduplicationlabel').html();
		var blocksizelabel = $('.blocksizelabel').html();
		var compresslabel = $('.compresslabel').html();
		var encryptlabel = $('.encryptlabel').html();
		var storeInfo = deduplicationlabel + ": " + getSwitchDes(data.highInfo.store.deduplication);
		storeInfo += "<br>" + compresslabel + ": " + getSwitchDes(data.highInfo.store.compress);
		
		
		//备份模式
		var backupmode1Info = $('.snapshotlabel').html() + ": " + getSwitchDes(data.highInfo.mode.snapshotcheck);
		var backupmode2Info = $('.silentsnapshotlabel').html() + ": " + getSwitchDes(data.highInfo.mode.silentsnapshotcheck);
		var incmodeInfo = $('.incmodellabel').html() + ": " + $('#incmodel option:selected').text();
		//CBT
		var cbtmodeInfo = $('.cbtlabel').html() + ": " + getSwitchDes(data.highInfo.mode.cbtcheck);
		
		//内联数据消重
		var parsefsmodeInfo = $('.parsefslabel').html() + ": " + getSwitchDes(data.highInfo.mode.parsefscheck);
		//排除交换文件块
		var swapmodeInfo = $('.swaplabel').html() + ": " + getSwitchDes(data.highInfo.mode.swapcheck);
		//排除删除文件块
		var deletefilemodeInfo = $('.deletefilelabel').html() + ": " + getSwitchDes(data.highInfo.mode.deletefilecheck);
		//排除分区间隙
		var gapmodeInfo = $('.gaplabel').html() + ": " + getSwitchDes(data.highInfo.mode.gapcheck);
		
		//快照模式
		var snapshotmodeInfo = $('.snapshotTypelabel').html() + ": " + $('#snapshottype').find("option:selected").text();
		$('.incmodeshow').html(incmodeInfo);
		$('.incmodeshow').hide();
		
		$('.backupmodeshow1').html(backupmode1Info);
		$('.backupmodeshow2').html(backupmode2Info);
		$('.cbtmodeshow').html(cbtmodeInfo);
		
		$('.parsefsmodeshow').html(parsefsmodeInfo);
		$('.swapmodeshow').html(swapmodeInfo);
		$('.deletefilemodeshow').html(deletefilemodeInfo);
		$('.gapmodeshow').html(gapmodeInfo);
		$('.snapshotmodeshow').html(snapshotmodeInfo);
		switch(data.srcInfo.vmType){
			case CONF.VM_TYPE.VMWARE:
			case CONF.VM_TYPE.CLOUDVIEW:
			case CONF.VM_TYPE.CLOUDVIEWSVM:
			case CONF.VM_TYPE.HYPERV:
			case CONF.VM_TYPE.XHERE:
				//传输模式
				var transferlabel = $('.transferlabel').html();
				$('.transportinfoshow').html(transferlabel + ": " + $('#transport_mode').find("option:selected").text());
				storeInfo += "<br>" + blocksizelabel + ": " + $('#blocksize option:selected').text();
				break;
			case CONF.VM_TYPE.CITRIX:
				data.highInfo.transfer.mode = parseInt($('#xentransmode').val());
				//加密传输
				var encryptlabel = $('.encrypttransferlabel').html();
				var xentranslabel = $('.xentranslabel').html();
				var des =	encryptlabel + ": " + getSwitchDes(data.highInfo.transfer.encrypt);
				des += "  " + xentranslabel + ": " +  $('#xentransmode').find("option:selected").text();
				$('.transportinfoshow').html(des);
				$('.backupmodeshow1').hide();						//隐藏高速模式
				$('.incmodeshow').show();							//显示增量模式
				break;
			case CONF.VM_TYPE.FUSIONXEN:
				//传输模式
				data.highInfo.transfer.mode = $('#huaweitransport_mode').val();
				var transferlabel = $('.huaweitransferlabel').html();
				$('.transportinfoshow').html(transferlabel + ": " + $('#huaweitransport_mode').find("option:selected").text());
				break;
			case CONF.VM_TYPE.INCLOUD:
			case CONF.VM_TYPE.VGATE:
			case CONF.VM_TYPE.WINSERVER:
			case CONF.VM_TYPE.WINDIY:
			case CONF.VM_TYPE.DSERVER:
			case CONF.VM_TYPE.NEOKYLIN:
			case CONF.VM_TYPE.RHV:
			case CONF.VM_TYPE.OVIRT:
			case CONF.VM_TYPE.ZVIRT:
			case CONF.VM_TYPE.HOSTVM:
			case CONF.VM_TYPE.FLEXCLOUD:
			case CONF.VM_TYPE.OPENSTACK:
			case CONF.VM_TYPE.FLEXHCS:
			case CONF.VM_TYPE.OSEASYVSERVER:
			case CONF.VM_TYPE.H3C:
			case CONF.VM_TYPE.H3CCASCVD:
			case CONF.VM_TYPE.INCLOUDKVM:
			case CONF.VM_TYPE.INSPURVVDK:
			case CONF.VM_TYPE.INCLOUDOPENSTACK:
			case CONF.VM_TYPE.SUGONCLOUDVIEW: //34
			case CONF.VM_TYPE.INSPURCLOUDPLATFORM: //35
			case CONF.VM_TYPE.EASYSTACK: //36
			case CONF.VM_TYPE.FIBERHOMEOPENSTACK: //37
			case CONF.VM_TYPE.CTSIOPENSTACK: //38
			case CONF.VM_TYPE.AWCLOUD: //39
			case CONF.VM_TYPE.REDVIRT:
			case CONF.VM_TYPE.ROSAVIRT:
				data.highInfo.transfer.mode = parseInt($('#leixentransmode').val());
				//加密传输
				var encryptlabel = $('.encrypttransferlabel').html();
				var xentranslabel = $('.xentranslabel').html();
				var des =	encryptlabel + ": " + getSwitchDes(data.highInfo.transfer.encrypt);
				des += "  " + xentranslabel + ": " +  $('#leixentransmode').find("option:selected").text();
				$('.transportinfoshow').html(des);
				$('.backupmodeshow1').show();						//显示高速模式
				break;
			case CONF.VM_TYPE.KVM:
			case CONF.VM_TYPE.SANGFOR:
			case CONF.VM_TYPE.SANGFORVVDK:
			case CONF.VM_TYPE.SDCOS:
				//加密传输
				var encryptlabel = $('.encrypttransferlabel').html();
				$('.transportinfoshow').html(encryptlabel + ": " + getSwitchDes(data.highInfo.transfer.encrypt));
				break;
		}
		
		$('.storageinfoshow').html(storeInfo);
		
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
		if(CONF.RESERVE_TYPE.NUM == data.highInfo.reserve.type){
			data.highInfo.reserve.value = $('#spinnerNumInput').val();
		}else if(CONF.RESERVE_TYPE.DAY == data.highInfo.reserve.type){
			data.highInfo.reserve.value = $('#spinnerDayInput').val();
		}
		if(data.highInfo.reserve.value > 0){
			$('.setreservtip').hide();
			return true;
		}else{
			$('.setreservtip').html(LANG.UI_BACKUP_RESERVE_TIPS).show();
			return false;
		}
	}
	//得到归档策略
	var getAchiveStr = function(){return true;}
	//得到传输策略
	var getTransferStr = function(){
		data.highInfo.transfer.encrypt =  $('#encrypttransfer').get(0).checked;
		data.highInfo.transfer.mode = $('#transport_mode').val();
		return true;
	}
	//得到存储策略
	var getStoreStr = function(){
		data.highInfo.store.deduplication = $('#deduplicationcheck').get(0).checked;
		data.highInfo.store.blocksize = $('#blocksize').val();
		data.highInfo.store.compress = $('#compresscheck').get(0).checked;
		data.highInfo.store.encrypt = $('#encryptcheck').get(0).checked;
		data.highInfo.store.valid = $('#validcheck').get(0).checked;
		return true;
	}
	
	//得到备份节点信息
	var getNodeStr = function(){
		data.highInfo.node.nodecheck = true;
		data.highInfo.node.nodeuuid = '';
		data.highInfo.node.storagecheck = true;
		data.highInfo.node.storageuuid = '';
		var autoNodeCheck = $('#autonodecheck').get(0).checked;
		if(autoNodeCheck){
			//自动选择节点
			return true;
		}
		data.highInfo.node.nodecheck = false;
		data.highInfo.node.nodeuuid = $('#selectnode').val();
		if(!data.highInfo.node.nodeuuid){
			UIToastr.showWarning(LANG.UI_BACKUP_SELECT_NODE, LANG.UI_BACKUP_SELECT_NODE_TIPS);
			return false;
		}
		var autoStorageCheck = $('#autostoragecheck').get(0).checked;
		if(autoStorageCheck){
			//自动选择存储先判断是否有可用存储
			if(!$('#selectstorage').val()){
				UIToastr.showWarning(LANG.UI_BCACKUP_NO_STORAGE, LANG.UI_BCACKUP_NO_STORAGE_TIPS);
				return false;
			}
			return true;
		}
		data.highInfo.node.storagecheck = false;
		data.highInfo.node.storageuuid = $('#selectstorage').val();
		if(!data.highInfo.node.storageuuid){
			UIToastr.showWarning(LANG.UI_BACKUP_SELECT_STORAGE, LANG.UI_BACKUP_SELECT_STORAGE_TIPS);
			return false;
		}
		return true;
	}
	
	//得到备份模式
	var getModeStr = function(){
		data.highInfo.mode.snapshotcheck = $('#snapshotcheck').get(0).checked;
		data.highInfo.mode.incmode = $('#incmodel').val();
		data.highInfo.mode.silentsnapshotcheck = $('#silentsnapshotcheck').get(0).checked;
		data.highInfo.mode.cbtcheck = $('#cbtcheck').get(0).checked;
		data.highInfo.mode.parsefscheck = $('#parsefscheck').get(0).checked;
		data.highInfo.mode.swapcheck = $('#swapcheck').get(0).checked;
		data.highInfo.mode.deletefilecheck = $('#deletefilecheck').get(0).checked;
		data.highInfo.mode.gapcheck = $('#gapcheck').get(0).checked;
		data.highInfo.mode.snapshottype = $('#snapshottype').val();
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
                $('#vmbackupcontent').find('.button-previous').hide();
                $('#vmbackupcontent').find('.button-next').addClass('next-btn-margin-left');
            } else {
                $('#vmbackupcontent').find('.button-previous').show();
                $('#vmbackupcontent').find('.button-next').removeClass('next-btn-margin-left');
            }

            
            //如果是最后一步
            if (current >= total) {
                $('#vmbackupcontent').find('.button-next').hide();
                $('#vmbackupcontent').find('.button-submit').show();
            } else {
                $('#vmbackupcontent').find('.button-next').show();
                $('#vmbackupcontent').find('.button-submit').hide();
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

        $('#vmbackupcontent').find('.button-previous').hide();
        $('#vmbackupcontent .button-submit').click(submit).hide();
	};
	
	//提交
	var submit = function(){
		if('' == $.trim($("#jobname").val())){
			$('.jobnametip').html(LANG.UI_BACKUP_NAME_TIPS).show();
			return;
		}
		$('.jobnametip').hide();
		data.taskName = $.trim($("#jobname").val());
		
		//TODO提交
    	var jsonData = JSON.stringify(data);
    	Metronic.blockUI({target: '#vmbackupcontent',animate: true,cenrerY: true,});
    	$.post(CONF.AJAXPATH, {m:CONF.M.VM,f:'createCDPJob',p:jsonData}, function(data){
    		Metronic.unblockUI('#vmbackupcontent');
    		if(OPREL(data)){
    			LOCATION('./content/platform/jobs/jobs.php', 'task');
        	}
    	});
	}
	
	
	//初始化主机和集群模式
	var setTree = function(zNodes){
		if(zNodes == "[]"){
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
					nocheckInherit: false
				},
				data: {
					simpleData: {
						enable: true,
						idKey: "id",
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
		var nodes = JSON.parse(zNodes);//拿到备份节点
		zTreeHC = $.fn.zTree.init($("#vm_tree_hc"), setting, nodes);//第一种
		for(var i=0;i<nodes.length;i++){
			if(nodes[i].checked){
				var checkNode = zTreeHC.getNodesByParam("id", nodes[i].id, null);
				addVMList('vm_tree_hc',checkNode[0]);
			}
		}
		currentTree = zTreeHC;
		currentTreeDivId = 'vm_tree_hc';
	};
	
	//初始化其他模式
	var setOtherTree = function(zNodes){
		var setting = {
				check: {
					enable: true,
					nocheckInherit: false
				},
				data: {
					simpleData: {
						enable: true,
						idKey: "id",
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
		var nodes = JSON.parse(zNodes);
		zTreeVT = $.fn.zTree.init($("#vm_tree_vt"), setting, nodes);//第二种
	}
	
	//初始化其他模式
	var setThirdTree = function(zNodes){
		var setting = {
				check: {
					enable: true,
					nocheckInherit: false
				},
				data: {
					simpleData: {
						enable: true,
						idKey: "id",
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
		var nodes = JSON.parse(zNodes);
		zTree = $.fn.zTree.init($("#vm_tree"), setting, nodes);//第三种
	}
	
	//添加虚拟化中心鼠标指上去事件
	var addHoverDom = function(treeId, treeNode) {
		if(1 != treeNode.type) return;
		var nodeID = escapeJquery(treeNode.id);
		var nodeTID = escapeJquery(treeNode.tId);
		var aObj = $("#" + nodeTID + "_a");
		if ($("#diyHref_" + nodeTID + nodeID + "_1").length>0) return;
		var str = '<a id="diyHref_' + treeNode.tId + treeNode.id + '_1" title="' + LANG.UI_BACKUP_TREE_REFRESH_DES + '" class="treehref">' + LANG.UI_BACKUP_TREE_REFRESH + '</a>' + 
				  '<a id="diyHref_' + treeNode.tId + treeNode.id + '_2" title="' + LANG.UI_BACKUP_TREE_EXPAND_ALL_DES + '" class="treehref">' + LANG.UI_BACKUP_TREE_EXPAND_ALL + '</a>' + 
				  '<a id="diyHref_' + treeNode.tId + treeNode.id + '_3" title="' + LANG.UI_BACKUP_TREE_COLLAPSE_ALL_DES + '" class="treehref">' + LANG.UI_BACKUP_TREE_COLLAPSE_ALL + '</a>';
		aObj.after(str);
//		aObj.append(str);
		var hrefRefresh = $("#diyHref_" + nodeTID + nodeID + "_1");
		var hrefExpand = $("#diyHref_" + nodeTID + nodeID + "_2");
		var hrefCollapse = $("#diyHref_" + nodeTID + nodeID + "_3");
		
		if (hrefRefresh) hrefRefresh.bind("click", function(){
			getSyncVcenterInfo(treeId, treeNode, true, false);
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
	
	//勾选虚拟机
	var vmOnCheck = function(e, id, node){
		checkVMOnlineAndTips(node);
		var tree = $.fn.zTree.getZTreeObj(id);
		var allNodes = tree.getCheckedNodes(true);
	    addVMList(id,node);
		for(var i = 0; i < allNodes.length; i++){
			if(allNodes[i].vcenteruuid != node.vcenteruuid){     //判断是否为同一个虚拟中心
				//只需要比较两次
				tree.checkAllNodes(false);
				tree.checkNode(node, !node.checked, false, false);
				UIToastr.showInfo(LANG.UI_BACKUP_DIFF_VCENTER, LANG.UI_BACKUP_DIFF_VCENTER_TIPS);
//				$('#addVTList li').remove();
				
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
				return;
			}else{
				if(i == 1) return;
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
	
	//勾选添加虚拟机显示列表
	var addVMList = function(id,node){
    		var info = "";
    		var diskinfo = "";
    		var liId = clearString(id+node.vcenteruuid + node.id); //添加虚拟机每列ID
    		if(node.checked){
    			if(node.hypervisor == 14 || node.hypervisor ==15 || node.hypervisor ==22){
    				info += '<li class="list-group-item popovers VMTips" id="' + liId + '" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="'+ node.path +
    				'"><div class="col1"><div class="cont contdetail"><div class="cont-col1"><div class="'+node.iconSkin+'">'
    				+'</div></div><div class="cont-col2"><div class="desc list-one">' + node.name + '</div></div></div></div><div class="col2  pull-right delete-list" style="margin-left:-35px;width:35px;padding-top: 7px;"><a class="del'+liId+'" >'
    				+'<div class="label label-sm label-danger" style="padding:0;"><i class="fa fa-times"></i></div></a></div></li>';
    			}else{
    				info += '<li class="list-group-item popovers VMTips" id="' + liId + '" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="'+ node.path +
    				'"><a href="#disk' + liId + '" data-toggle="collapse" aria-expanded="true" id="disHref' + liId + '" style="display:flex;" class="accordion-toggle accordion-toggle-styled collapsed"><div class="col1"><div class="cont contdetail"><div class="cont-col1"><div class="'+node.iconSkin+'">'
    				+'</div></div><div class="cont-col2"><div class="desc list-one">' + node.name + '</div></div></div></div><div class="col2  pull-right delete-list" style="margin-left:-50px;width:35px;padding-top: 10px;"><span class="del'+liId+'" >'
    				+'<div class="label label-sm label-danger" style="padding:0;"><i class="fa fa-times"></i></div></span></div></a>'
    				+ '</li><ul id="disk' + liId + '" class="feeds collapse diskList" style="list-style:none;"></ul>';
    			}
    			//根据虚拟机树类型添加每一列到列表				
				 if(id=="vm_tree_hc"){
					$('#addHCList').append(info);
					}else if(id=="vm_tree_vt"){
						$('#addVTList').append(info);
					}else if(id=="vm_tree"){
						$('#addHVList').append(info);
				}
				 $('#' + escapeJquery(liId)).popover();	   //初始化tips
				 
				//移除虚拟机显示
	    		$('.del'+escapeJquery(liId)).on('click', function(){
	    			var treeObj = $.fn.zTree.getZTreeObj(id);
	    			$('.popover.in').remove();
	        		treeObj.checkNode(node,false,false);
	        		$('#' + escapeJquery(liId)).remove();
	        		$('#disk' + escapeJquery(liId)).remove();
	    		});
				//初始化每台虚拟机的磁盘信息
				if(node.detail && node.detail != "" && node.detail.disk_list){
					if(node.hypervisor == 14 || node.hypervisor ==15 || node.hypervisor ==22){
						return;
					}
					if(!node.detail['disk_list']) return;
					var diskCount = node.detail['disk_list'].length;
			    	var disklist = node.detail['disk_list'];
			    	
					for(var i=0;i<diskCount;i++){
						diskinfo += '<li class="list-group-item popovers diskTips" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="' + disklist[i].disk_name + '">'
					 + '<div class="col1 disName" >' + disklist[i].disk_name +'<span class="display-none" id="disuuid'+ i + node.id + node.vcenteruuid +'">'+ disklist[i].disk_uuid +'</span></div>'+
					 '<input class="col2 diskCheck" type="checkbox" id="disCheck'+ i + node.id + node.vcenteruuid + '" checked  class="make-switch" data-on-color="primary" data-off-color="info" value="' + disklist[i].disk_name + '"></li>';
					} 
					$('#disk' + escapeJquery(liId)).append(diskinfo);
					$('.diskTips').popover();
					$('.diskCheck').bootstrapSwitch({
						onText: LANG.UI_BACKUP_DISK_MODE,
						offText: LANG.UI_BACKUP_EXCLUDE_DISK_MODE
					});
				}else{
					diskinfo += '<li class="list-group-item">' + LANG.UI_BACKUP_NO_DISK + '</li>';
					$('#disk' + escapeJquery(liId)).append(diskinfo);
				}
    		}else{
    			//检查node.id是否在虚拟化里面，如果在就要把对应的li移除
    			$('#disk' + escapeJquery(liId)).remove();
    			$('#' + escapeJquery(liId)).remove();
    		}
    	}
	
	//检测虚拟机断开状态,选中的时候提示
	var checkVMOnlineAndTips = function(node){
		if(!node.online && node.checked){
			UIToastr.showWarning(LANG.UI_BACKUP_SELECT_OFFLINE_VM_TIPS, LANG.UI_BACKUP_SELECT_OFFLINE_VM_VALUE);
		}
	}
	
	var nodeSelect = function(treeId, treeNode, clickFlag){
		if('vm' == treeNode.eventtype){
			$.fn.zTree.getZTreeObj(treeId).checkNode(treeNode, !treeNode.checked, false, true);
			if(treeNode.inbackup){
				UIToastr.showWarning(LANG.UI_BACKUP_VM_IN_TASK, LANG.UI_BACKUP_VM_IN_TASK_TIPS);
				return;
			}
			if(treeNode.chkDisabled){
				//如果不可用,提示未授权
				UIToastr.showWarning(LANG.UI_BACKUP_SELECT_UNAUTHORIZAED_VM_TIPS, LANG.UI_BACKUP_SELECT_UNAUTHORIZAED_VM_VALUE);
			}
		}else{
			nodeExpand(treeId, treeNode);
			$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
		}
	}
	
	var nodeExpand = function(treeId, treeNode){
		if(treeNode.clickshow){
			if(treeNode.children) return true;
			getSyncVcenterInfo(treeId, treeNode, false, false);
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
	var getSyncVcenterInfo = function(treeId, treeNode, refreshFlag, expendFlag){
		var div = ".three_tree";
		var showType = parseInt($('#vmshowtype').val());
		var data = JSON.stringify({id:treeNode.id, vcflag:treeNode.vcenterFlag,hypervisor:treeNode.hypervisor, 
			showtype:showType, refresh:refreshFlag, modifyflag:false});
		Metronic.blockUI({target: div,animate: true});
		$.ajax({ 
			type: "post", 
	        url: CONF.AJAXPATH, 
	        async:true, 
	        data:{m:CONF.M.VCENTER,f:'getBackupSyncVcenter',p:data},
	        success: function(data){ 
	        	Metronic.unblockUI(div);
	        	result = JSON.parse(data);
	        	refreshVMList(treeId,treeNode.vcenteruuid);
	        	if(result.re){
	        		//success
	        		$.fn.zTree.getZTreeObj(treeId).removeChildNodes(treeNode);
	        		$.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, result.msg, true);
	        		$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
	        		setTreeLoadFlag();
	        		if(expendFlag == true){
	        			$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true, true, true);
	        		}
	        	}else{
	        		OPREL(data);
	        	}
	        } 
		});
	}
	
	var initTree = function(type) {
		var data = {};
		data.type = type;
		data = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.VCENTER,f:'getVcenterDetailsTree',p:data}, setTree);
		data = {};
		data.type = 2;
		var data2 = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.VCENTER,f:'getVcenterDetailsTree',p:data2}, setOtherTree);
		data.type = 3;
		var data3 = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.VCENTER,f:'getVcenterDetailsTree',p:data3}, setThirdTree);
	};
	// var inintDatatimePicker = function(){
	// 	$(".form_datetime").datetimepicker({
	// 		language:  'zh-CN', 
    //         autoclose: true,
    //         isRTL: Metronic.isRTL(),
    //         format: "yyyy-MM-dd hh:ii:ss",
    //         pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
    //         startDate: new Date()
    //     });
	// }

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
   
	
	//设置树的加载标志
	var setTreeLoadFlag = function(){
		if(currentTree == zTree){
			zTreeLoad = true;
		}else if(currentTree == zTreeHC){
			zTreeHCLoad = true;
		}else if(currentTree == zTreeVT){
			zTreeVTLoad = true;
		}
		showSearchInput(true);
	}
	
	//显示搜索框
	var showSearchInput = function(flag){
		if(!flag) return;
		$('#searchvm').val('').show();
	}
	
	//虚拟机树显示模式发生变化
	var vmShowTypeChange = function(){
		var showType = parseInt($('#vmshowtype').val());
		$('.addVMList').hide();
		$('.tree_div').hide();
		$('#searchvm').val('').hide();
//		updateNodes(false);
		switch(showType){
			case 1:
				$('#vm_tree_hc').show();
				$('#addHCList').show();
				currentTree = zTreeHC;
				currentTreeDivId = 'vm_tree_hc';
				showSearchInput(zTreeHCLoad);
				break;
			case 2:
				$('#vm_tree_vt').show();
				$('#addVTList').show();
				currentTree = zTreeVT;
				currentTreeDivId = 'vm_tree_vt';
				showSearchInput(zTreeVTLoad);
				break;
			case 3:
				$('#vm_tree').show();
				$('#addHVList').show();
				currentTree = zTree;
				currentTreeDivId = 'vm_tree';
				showSearchInput(zTreeLoad);
				break;
		}
		$("#" + currentTreeDivId).scrollTop(0);
	}
	
	//搜索虚拟机
	var searchVM = function(){
		var value = $('#searchvm').val();
		var checkNode =currentTree.getCheckedNodes();
		var allNode = currentTree.transformToArray(currentTree.getNodes());
		nodeParamList = currentTree.getNodesByParamFuzzy('name', value);
		if(nodeParamList.length!=0){
			currentTree.hideNodes(allNode);
		}
		//连接搜索的和所勾选的
		nodeParamList =nodeParamList.concat(checkNode);
		var nodeParamList1 = currentTree.transformToArray(nodeParamList);
        for(var n in nodeParamList1){
            findParent(currentTree,nodeParamList1[n]);
        }
            currentTree.showNodes(nodeParamList);
    }
	
	 //找到父节点
	 var findParent = function(treeObj,node){
		 currentTree.expandNode(node,true,false,false);
		 var pNode = node.getParentNode();
		 if(pNode != null){
			 nodeParamList.push(pNode);
			 findParent(currentTree, pNode);
		 }
	}
	
	
	//初始化树形展示方式和事件
	var initSelectShowType = function(){
		//初始化BS select控件
		$('#vmshowtype').selectpicker({
            iconBase: 'fa',
            tickIcon: 'fa-check'
        });
		//绑定事件
		$('#vmshowtype').on('change', vmShowTypeChange);
		$('#searchvm').on('propertychange', searchVM).on('input', searchVM);
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
			initTree(1);
		}
	}
	
	//快速创建备份任务
	var speedInitTree = function(showtype, vmuuid, vcenteruuid, hypervisor){
		var data = {};
		data.showtype = showtype;
		data.vmuuid = vmuuid;
		data.vcenteruuid = vcenteruuid;
		data.type = hypervisor;
		data = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.VCENTER,f:'getBackupTreeSpeed',p:data}, setTree);
	}
	
	//初始化时间策略
	var initStrategy = function(){
		var strategy = [];
		strategy[0] = {
			mode: 1,
			strategy_type: 2,
			days: [0, 0, 0, 0, 1, 0, 0],
			start_time: '23:00:00',
			roll_flag: false,
			roll_interval: '01:00:00',
			roll_end_time: '23:59:59'
		};
		strategy[1] = {
			mode: 2,
			strategy_type: 1,
			days: [],
			start_time: '23:00:00',
			roll_flag: false,
			roll_interval: '01:00:00',
			roll_end_time: '23:59:59'	
		};
		strategy[2] = {
			mode: 3,
			strategy_type: 1,
			days: [],
			start_time: '23:00:00',
			roll_flag: false,
			roll_interval: '01:00:00',
			roll_end_time: '23:59:59'	
		};
		//延迟设置,因为这里icheck会默认修改里面的选中事件
		setTimeout(function(){
			$('#timestrategy').strategy({config: strategy, display:['display-none', 'display-none', 'display-none']});
		}, 2000);
	}

	
    return {
        //main function to initiate the module
        init: function () {
        	wizardInit();         //
        	initMyTree();         //
        	initSelectShowType(); //
        	initListener();       //
        	initSoftwareVersionDiff(); //版本差异处理
        	inintDatatimePicker();//
        	initData();           //初始化备份流程数据
        	initSpinner();        //
        	initStrategy();       //初始化时间策略
        }
    };

}();

jQuery(document).ready(function() {   
	VMCDP.init();
});