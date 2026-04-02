var FileCDP = function () {
	var configData = {producthostInfo:{},standbyhostInfo:{},highInfo:{}};
	var fileTree, ransomwareTree;
	var backupHostIsServer = null;
	var standbyHostInitFlag = false, jobNameInitFlag = false;
	var fileTypeGrid, initFileTypeGridFlag = false, modalShowFlag = false;
	
	//初始化生产主机列表
	var initProductHostList = function(){
		initHostList('producthost', 1);
	}
	
	//初始化备份主机列表
	var initStandbyHostList = function(){
		initHostList('standbyhost', 2);
	}
	
	//统一初始化生产主机和备份主机下拉列表
	var initHostList = function(id, hosttype){
		var data = {};
		data.hosttype = hosttype;
		data = JSON.stringify(data);
    	$.post(CONF.AJAXPATH, {m:CONF.M.DBCDP,f:'gettHostInfoWithType',p:data}, function(d){
    		var data = JSON.parse(d);
    		if(!data.length) return;
			var hostselect = $('#' + id);
			hostselect.empty();
			var option = $("<option>").text('').val('');
			hostselect.append(option);
			for(var i=0;i<data.length;i++){
				if(!data[i].bsflag){
					//不显示备份服务器
					var option = '<option data-bsflag="' + data[i].bsflag + '" value="' + data[i].uuid + '">' + data[i].value + '</option>';
					hostselect.append(option);
				}
			}
			
			if(2 == hosttype){
				standbyHostInitFlag = true;
			}
    	});
	}
	
	//生产主机选择改变
	var productHostChange = function(){
		var data = {};
		data.hostuuid = $('#producthost').val();
		data.dir = "";
		data.page = 0;
		data.limit = 20;
		data.pid = 0;
		if("" == data.hostuuid) return;
		data = JSON.stringify(data);
    	$.post(CONF.AJAXPATH, {m:CONF.M.FILECDP,f:'getHostFileTree',p:data}, function(d){
    		var data = JSON.parse(d);
    		if(data.re){
    			//success
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
        					}
        				},
        				callback: {
        					beforeClick: nodeSelect,
//        					onCheck: pathOnCheck,
        					beforeExpand: fileNodeExpand,
        				}
        			};
        		fileTree = $.fn.zTree.init($("#file_tree"), setting, data.tree);
        		if(undefined !== data.tree){
        			$('#filediv').show();
        		}else{
        			$('#filediv').hide();
        		}
        		
        		$('#filediv').show();
    		}else{
    			OPREL(d);
    			$('#filediv').hide();
    		}
    		
    	});
	}
	
//	var ransomwareNodeSelect = function(treeId, treeNode, clickFlag){
//		ransomwareTree.checkNode(treeNode, !treeNode.checked, false, true);
//		ransomwareTree.expandNode(treeNode, true);
//	}
	
	
	var nodeSelect = function(treeId, treeNode, clickFlag){
		if(treeNode.more){
			// 显示更多
			var hostuuid = $('#producthost').val();
			if(!hostuuid)	return false;
			var parentNode = treeNode.getParentNode();
			
			var parentCheck = false;
			
			if(null != parentNode){
				//顶层目录会找不到父节点
				var checkStatus = parentNode.getCheckStatus();
				if(checkStatus.checked && !checkStatus.half){
					//如果父节点选中
					parentCheck = true;
				}
			}
			var p = {hostuuid:hostuuid, page:treeNode.page, limit:20, dir:treeNode.dir, pid:treeNode.pid, checked:parentCheck};
			p = JSON.stringify(p);
			Metronic.blockUI({target: '#file_tree',animate: true});
			$.ajax({ 
				type: "post", 
		        url: CONF.AJAXPATH, 
		        async:true, 
		        data:{m:CONF.M.FILECDP,f:'getHostFileTree',p:p},
		        success: function(data){ 
		        	Metronic.unblockUI('#file_tree');
		        	result = JSON.parse(data);
		        	if(result.re){
		        		//success
		        		fileTree.removeNode(treeNode);
		        		fileTree.addNodes(treeNode.getParentNode(), result.tree, true);
		        	}else{
		        		OPREL(data);
		        	}
		        } 
			});
		}else{
			fileTree.checkNode(treeNode, !treeNode.checked, false, true);
		}
	}
	
	//路径展开
	var fileNodeExpand = function(treeId, treeNode){
		if(treeNode.children) return true;
		var hostuuid = $('#producthost').val();
		if(!hostuuid)	return false;
		var p = {hostuuid:hostuuid, page:0, limit:20, dir:treeNode.dir, pid:treeNode.id, checked:treeNode.checked};
		p = JSON.stringify(p);
		Metronic.blockUI({target: '#file_tree',animate: true});
		$.ajax({
			type: "post", 
	        url: CONF.AJAXPATH, 
	        async:true, 
	        data:{m:CONF.M.FILECDP,f:'getHostFileTree',p:p},
	        success: function(data){
	        	Metronic.unblockUI('#file_tree');
	        	result = JSON.parse(data);
	        	if(result.re){
	        		//success
	        		fileTree.addNodes(treeNode, result.tree, true);
	        	}else{
	        		OPREL(data);
	        	}
	        } 
		});
	}
	
	
	var initListener = function(){
		initProductHostList();
		$('#producthost').on('change', productHostChange);
		$('#standbyhost').on('change', standbyHostChange);
		
		//备份类型改变
		$('#backuptype').on('change', backuptypeChange);
		
		//节点改变
		$('#selectnode').on('change', initStorageSelect);
		
		//防勒索病毒
		$('#ransomwaretype').on('change', ransomwaretypeChange);
		
		//时间策略方式改变
		$('#strategytype').on('change', strategytypeChange);
		//时间过滤
		$('#timefilter').bootstrapSwitch('onSwitchChange', timefilterChange);
		//名称过滤
		$('#namefilter').bootstrapSwitch('onSwitchChange', namefilterChange);
		
		//立即扫描
		$('#openscan').on('click', openscanModal);
		
		//开始扫描
		$('#startscan').on('click', startscan);
		
		//停止扫描
		$('#stopscan').on('click', stopscan);
		
		//模态框隐藏
		$('.modalclosebtn').on('click', function(){
			modalShowFlag = false;
			initRansomwareTable();
		});
	}
	
	//打开扫描modal
	var openscanModal = function(){
		var data = {};
		data.producthostuuid = $('#producthost').val();
		data.pathlist = configData.producthostInfo.pathList;
		var jsonData = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.FILECDP,f:'getScanHostFileTypeInfo',p:jsonData}, function(d){
			setScanInfo(d);
			$('#modalransomware').modal({'width':'600px', 'height':'500px'});
			modalShowFlag = true;
			updateScanInfos();
//			console.log(Date.parse(new Date()) + "o9k");
    	});
		var html = "";
		for(var i=0; i<data.pathlist.length; i++){
			html += data.pathlist[i] + "<br>";
		}
		$('#currentfilelist').html(html);
	}
	
	//设置扫描结果
	var setScanInfo = function(d){
		var data = JSON.parse(d);
		//设置扫描状态
		$('#scanstatus').html('<span class="label label-sm ' + getStatusLevelClass(data.status) + '" >' + data.statusdes + '</span>');
		//设置扫描统计
		var html = "扫描文件: " + data.fcount + "个" + ", " + "扫描文件夹: " + data.dircount + "个";
		$('#scanstatistics').html(html);
		
		//设置历史列表
		var html = "";
		for(var i=0; i<data.hisdir.length; i++){
			html += data.hisdir[i] + "<br>";
		}
		$('#historyfilelist').html(html);
	}
	
	//得到状态的显示类型
	var getStatusLevelClass = function(level){
		var levelClass = '';
		switch(level){
			case 1:
				levelClass = "label-default";
				break;
			case 2:
				levelClass = "label-success";
				break;
			case 3:
				levelClass = "label-info";
				break;
			default:
				levelClass = "label-info";
				break;
				
		}
		return levelClass;
	}
	
	//更新扫描内容
	var updateScanInfos = function(){
		var updateInterval = 3000;
		var init = function(){
			if(!modalShowFlag){
				//如果模态框关闭
        		clearTimeout(timerTask.FileCDPScanFile);
//        		console.log(Date.parse(new Date()) + "clearn");
        		return;
        	}
//			console.log(Date.parse(new Date()) + "ok");
			var data = {};
			data.producthostuuid = $('#producthost').val();
			data.pathlist = configData.producthostInfo.pathList;
			var jsonData = JSON.stringify(data);
			$.post(CONF.AJAXPATH, {m:CONF.M.FILECDP,f:'getScanHostFileTypeInfo',p:jsonData}, function(d){setScanInfo(d)});
//			
			timerTask.FileCDPScanFile = setTimeout(init, updateInterval);
		}
		init();
	}
	
	//modal开始扫描按钮事件
	var startscan = function(){
		var data = {};
		data.producthostuuid = $('#producthost').val();
		data.pathlist = configData.producthostInfo.pathList;
		var jsonData = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.FILECDP,f:'startScanHostFileType',p:jsonData}, function(d){
			OPREL(d);
    	});
	}
	
	//停止扫描
	var stopscan = function(){
		var data = {};
		data.producthostuuid = $('#producthost').val();
		var jsonData = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.FILECDP,f:'stopScanHostFileType',p:jsonData}, function(d){
			OPREL(d);
    	});
	}
	
	var ransomwaretypeChange = function(){
		var ransomwaretype = $('#ransomwaretype').val();
		if("0" == ransomwaretype){
			//关闭
			$('#ransomwarediv').hide();
		}else{
			//指定文件类型备份/不备份
			$('#ransomwarediv').show();
			initRansomwareTable();
		}
	}
	
	//初始化防勒索文件类型表
	var initRansomwareTable = function(){
		var data = {};
		data.producthostuuid = $('#producthost').val();
		var data = {m:CONF.M.FILECDP,f:'getRansomwareFileTypeTable',p:data};
		if(!initFileTypeGridFlag){
			var dataTableOpt = {
	    			'columnDefs' : [{
		                'orderable': false,
		                'targets': [0, 1, 2]
	    			}],
	    			"paging":false,
	    			"info":false
	    	};
			fileTypeGrid = new Datatable();
			fileTypeGrid.setAjaxParam(data);
			fileTypeGrid.init({src: $("#ransomwaretable"),  dataTable:dataTableOpt});
			initFileTypeGridFlag = true;
		}else{
			fileTypeGrid.setAjaxParam(data);
			fileTypeGrid.getRefresh({});
			//更新后滚动到顶部
			$(".table-scrollable").animate({scrollTop:0},10);
		}
	}
	
	var timefilterChange = function(e, data){
		if(data){
			$('.timefilterdiv').show();
		}else{
			$('.timefilterdiv').hide();
		}
	}
	
	var namefilterChange = function(e, data){
		if(data){
			$('.namefilterdiv').show();
		}else{
			$('.namefilterdiv').hide();
		}
	}
	
	var strategytypeChange = function(){
		var type = $('#strategytype').val();
		if(type == "0"){
			$('#strategytimediv').hide();
		}else{
			$('#strategytimediv').show();
		}
	}
	
	var backuptypeChange = function(){
		var type = $('#backuptype').val();
		//历史数据目录跟到数据回退走,有数据回退才有
		if(type == "0"){
			$('.hisdiv').hide();
			$('.hisdivtab').hide();
		}else{
			var sbFlag = $('#standbyhost option:selected').data('bsflag');
			if(!sbFlag){
				//如果不是备份系统
				$('.hisdiv').show();
			}
			$('.hisdivtab').show();
		}
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
            jQuery('li', $('#filecdpcontent')).removeClass("done");
            var li_list = navigation.find('li');
            for (var i = 0; i < index; i++) {
                jQuery(li_list[i]).addClass("done");
            }
            
            //如果第一步 上一步按钮隐藏
            if (current == 1) {
                $('#filecdpcontent').find('.button-previous').hide();
                $('#filecdpcontent').find('.button-next').addClass('next-btn-margin-left');
            } else {
                $('#filecdpcontent').find('.button-previous').show();
                $('#filecdpcontent').find('.button-next').removeClass('next-btn-margin-left');
            }
            
            //如果是最后一步
            if (current >= total) {
                $('#filecdpcontent').find('.button-next').hide();
                $('#filecdpcontent').find('.button-submit').show();
            } else {
                $('#filecdpcontent').find('.button-next').show();
                $('#filecdpcontent').find('.button-submit').hide();
            }
            
            
            Metronic.scrollTo($('.page-title'));
        }

        // default form wizard
        $('#filecdpcontent').bootstrapWizard({
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

        $('#filecdpcontent').find('.button-previous').hide();
        $('#filecdpcontent .button-submit').click(submit).hide();
	};
	
	//提交
	var submit = function(){
		if('' == $.trim($("#jobname").val())){
			$('.jobnametip').html(LANG.UI_BACKUP_NAME_TIPS).show();
			return;
		}
		$('.jobnametip').hide();
		configData.jobname = $.trim($("#jobname").val());
    	var jsonData = JSON.stringify(configData);
    	Metronic.blockUI({target: '#filecdpcontent',animate: true,cenrerY: true,});
    	$.post(CONF.AJAXPATH, {m:CONF.M.FILECDP,f:'createBackupJob',p:jsonData}, function(data){
    		Metronic.unblockUI('#filecdpcontent');
    		if(OPREL(data)){
    			LOCATION('./content/platform/jobs/jobs.php', 'task');
        	}
    	});
	}
	
	var step1Tips = function(){
		UIToastr.showInfo('选择文件/文件夹', '1.选择需要实时同步的文件/文件夹所在的生产主机<br>2.选择需要实时同步的文件/文件夹');
		return false;
	}
	
	//备份主机选择改变
	var standbyHostChange = function(){
		var data = {};
		data.hostuuid = $('#standbyhost').val();
		if("" == this.value){
			$('.standbyhostchangediv').hide();
			$('.storagediv').hide();
			return true;
		}else{
			$('.standbyhostchangediv').show();
		}
		
		//如果是备份主机是备份服务器，隐藏备份数据目录,历史数据目录
		var sbFlag = $('#standbyhost option:selected').data('bsflag');
		if(sbFlag){
			$('.dirdiv').hide();
		}else{
			$('.dirdiv').show();
		}
		
//		data = JSON.stringify(data);
//    	$.post(CONF.AJAXPATH, {m:CONF.M.DBCDP,f:'getBackupDirInfo',p:data}, function(d){
//    		var data = JSON.parse(d);
//    		$('#backupdir').val(data.backupdir);
//    		$('#historydir').val(data.historydir);
//    	});
    	
    	initStorage(this.value);
    	backuptypeChange();
	}
	
	//检查备份机器类型,如果备份主机就是备份服务器,要初始化存储
	var initStorage = function(hostuuid){
		var data = {};
		data.hostuuid = hostuuid;
		data = JSON.stringify(data);
    	$.post(CONF.AJAXPATH, {m:CONF.M.DBCDP,f:'checkBackupHostIsServer',p:data}, function(d){
    		var data = JSON.parse(d);
    		backupHostIsServer = data;
    		if(data.flag){
    			initStorageSelect();
    			$('.storagediv').show();
    		}else{
    			$('#selectstorage').empty();
    			$('.storagediv').hide();
    		}
    	});
	}
	
	//初始化存储下拉框
	var initStorageSelect = function(){
		var data = {};
		data.nodeuuid = backupHostIsServer.nodeuuid;
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
	
	//初始化任务名
	var initJobName = function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.FILECDP,f:'getBackupTaskName',p:{}}, function(d){
			$('#jobname').val(d);
			jobNameInitFlag = true;
		});
	}
	
	var step1Valid = function(){
		if('' == $('#producthost').val() || !fileTree){
			return step1Tips();
		}
		var nodes = fileTree.getCheckedNodes();
		if(!nodes.length){
			return step1Tips();
		}
		var fileInfo = [], pathList = [];
		var fileshow = '';
		for(var i=0;i<nodes.length;i++){
			var checkStatus = nodes[i].getCheckStatus();
			if(!checkStatus.half){
				//只要全选的
				var data = {};
				data.path = nodes[i].dir;
				data.isParent = nodes[i].isParent;
				data.name = nodes[i].name
				pathList.push(nodes[i].dir);
				fileInfo.push(data);
				fileshow += nodes[i].title + "<br>";
			}
		}
		configData.producthostInfo.fileInfo = fileInfo;
		configData.producthostInfo.pathList = pathList;
		configData.producthostInfo.hostuuid = $('#producthost').val();
		showStep1(fileshow);
	}
	
	var showStep1 = function(fileshow){
		if(!standbyHostInitFlag){
			initStandbyHostList();
		}
		if(!jobNameInitFlag){
			initJobName();
		}
		
		
		//第四步数据展示
		$('.producthostshow').html($('#producthost').find("option:selected").text());
		$('#filelist').html(fileshow);
	}
	
	
	var step2Valid = function(){
		var backuptype = $('#backuptype').val();
		if("0" == backuptype){
			//如果是实时同步,检查备份目录
			if('' == $.trim($('#backupdir').val())){
				UIToastr.showInfo('选择填写备份主机信息', '请按提示选择和填写备份目的地信息');
				return false;
			}
		}else if("1" == backuptype){
			//如果是实时同步+历史数据,检查历史目录
			if('' == $.trim($('#backupdir').val()) || '' == $.trim($('#historydir').val())){
				UIToastr.showInfo('选择填写备份主机信息', '请按提示选择和填写备份目的地信息');
				return false;
			}
		}
		
		//如果是备份服务器，检查是否选择了备份存储
		var sbFlag = $('#standbyhost option:selected').data('bsflag');
		var selectstorage = $('#selectstorage').val();
		if(sbFlag == true && null == selectstorage){
			UIToastr.showInfo('选择目标存储', '请选择目标存储，用于存放实时同步数据');
			return false;
		}
		
		//验证目录
		var data = {};
		data.backuptype = $('#backuptype').val();
		data.standbyhost = $('#standbyhost').val();
		data.backupdir = $.trim($('#backupdir').val());
		data.historydir = $.trim($('#historydir').val());
		if(backupHostIsServer.flag){
			data.storageuuid = $.trim($('#selectstorage').val());
		}else{
			data.storageuuid = '';
		}
		data = JSON.stringify(data);
		
		var checkResult = true;
    	$.ajax({ 
			type: "post", 
	        url: CONF.AJAXPATH, 
	        async:false, 
	        data:{m:CONF.M.FILECDP,f:'standbyHostDirCheck',p:data},
	        success: function(data){ 
	        	var d = JSON.parse(data);
	        	if(!d.re){
	        		//如果是检查失败
	        		checkResult = false;
	        		return OPREL(data);
	        	}
	        } 
		});
    	
		configData.standbyhostInfo.standbyhost = $('#standbyhost').val();
		configData.standbyhostInfo.backuptype = $('#backuptype').val();
		configData.standbyhostInfo.storageuuid = $.trim($('#selectstorage').val());
		configData.standbyhostInfo.backupdir = $.trim($('#backupdir').val());
		configData.standbyhostInfo.historydir = $.trim($('#historydir').val());
		
		showStep2();
//		initRansomwareTable();
//		initRansomwareTree();
		
		return checkResult;
	}
	
	var showStep2 = function(){
		$('.backuphostshow').html($('#standbyhost').find("option:selected").text());
		$('.backuptypeshow').html($('#backuptype').find("option:selected").text());
		$('.backupdirshow').html($.trim($('#backupdir').val()));
		$('.historydirshow').html($.trim($('#historydir').val()));
		
		//如果是实时备份,无数据回退,隐藏历史显示
		if("0" == $('#backuptype').val()){
			$('.historyshowdiv').hide();
		}else{
			$('.historyshowdiv').show();
		}
		
		//fix bug 历史数据只有在有数据回退的时候才展示,防止上一步,下一步出现
		var activeLi = $('#tab3ul>li.active');
		if(activeLi[0].id == "tabhistory" && $('#backuptype').val() == "0"){
			//如果之前展示的是历史数据tab,然后这一次是"实时备份",设置第一个tab为active状态
			setFirstTabActive('tab3ul', 'tabcontentdiv');
		}
	}
	
	//设置第一个tab为active状态
	var setFirstTabActive = function(ulid, tabcontentdiv){
		//移除所有tab的active状态,移除所有tabcontentdiv的active状态
		$('#' + ulid + ">li.active").removeClass("active");
		$('#' + tabcontentdiv).children('.active').removeClass("active");
		
		//给第一个tab加active状态,给第一个tabcontentdiv添加active状态
		$($('#' + ulid).children("li").get(0)).addClass("active");
		$($('#' + tabcontentdiv).children(".tab-pane").get(0)).addClass("active");
	}
	

	var step3Valid = function(){
		//常规配置
		configData.highInfo.general = {
				multithreading : $('#multithreading').bootstrapSwitch('state'),
				mirrorimage : $('#mirrorimage').bootstrapSwitch('state'),
				emptydir : $('#emptydir').bootstrapSwitch('state'),
		};
		//防勒索
		
		var selectTypes = [];
		if(undefined != fileTypeGrid){
			var filetype = fileTypeGrid.getSelectedRows();
			var tabledata = fileTypeGrid.getDataTable().data();
			$.each(filetype, function(i, v){
				selectTypes.push(tabledata[v][3]);
			});
		}
		configData.highInfo.ransomware = {
				ransomwaretype : $('#ransomwaretype').val(),
				selectTypes : selectTypes,
		};
		if($('#ransomwaretype').val() != "0"){
			if(selectTypes.length == 0){
				UIToastr.showInfo('选择防勒索文件类型', '您选择了防勒索病毒选项,请选择文件类型!');
				return false;
			}
		}
		
		//文件过滤
		configData.highInfo.filter = {
				timefilter : $('#timefilter').bootstrapSwitch('state'),
				timefiltertype : $('#timefiltertype').val(),
				timefiltervalue : $('#timefiltervalue').val(),
				namefilter : $('#namefilter').bootstrapSwitch('state'),
				namefilterlist : $('#namefilterlist').val(),
		};
		//时间策略
		configData.highInfo.strategy = {
				strategytype : $('#strategytype').val(),
				starttime : $('.starttime').val(),
				endtime : $('.endtime').val(),
		};
		//历史数据
		configData.highInfo.history = {
				historytime : $('#historytime').val(),
				historydel : $('#historydel').val(),
				historymod : $('#historymod').val(),
				historytimeinterval : $('#historytimeinterval').val(),
		};
		
		showStep3(configData.highInfo);
	}
	
	var showStep3 = function(highInfo){
		//常规配置
		var html = "";
		html += $('#multithreadinglable').html() + ": " + getSwitchDes(highInfo.general.multithreading);
		html += " ; " + $('#mirrorimagelable').html() + ": " + getSwitchDes(highInfo.general.mirrorimage);
		html += " ; " + $('#emptydirlabel').html() + ": " + getSwitchDes(highInfo.general.emptydir);
		$('.conventionshow').html(html);
		
		//防勒索
		$('.ransomwareshow').html($('#ransomwaretype').find("option:selected").text());
		
		//文件过滤
		var html = "";
		html += $('#timefilterlabel').html() + ": " + getSwitchDes(highInfo.filter.timefilter);
		html += " ; " + $('#namefilterlabel').html() + ": " + getSwitchDes(highInfo.filter.namefilter);
		$('.filtershow').html(html);
		
		//时间策略
		var html = "";
		html += $('#strategytype').find("option:selected").text();
		if("1" == $('#strategytype').val()){
			//如果是指定时间段备份
			html += ": " + highInfo.strategy.starttime + " - " + highInfo.strategy.endtime;
		}
		$('.strategyshow').html(html);
		
		//历史数据
		var html = "";
		html += $('#historytimelabel').html() + ": " + highInfo.history.historytime + $('#historytimeunit').html();
		html += " ; " + $('#historydellabel').html() + ": " + highInfo.history.historydel + $('#historydelunit').html();
		html += " ; " + $('#historymodlabel').html() + ": " + highInfo.history.historymod + $('#historymodunit').html();
		html += " ; " + $('#historytimeintervallabel').html() + ": " + highInfo.history.historytimeinterval + $('#historytimeintervalunit').html();
		$('.historyshow').html(html);
		
	}
	
	//得到开关的结果描述   开启/关闭
	var getSwitchDes = function(check){
		if(check){
			return LANG.UI_PUBLIC_ON;
		}
		return LANG.UI_PUBLIC_OFF;
	}
	
	var initSpinner = function(){
        $('.timefiltervalue').spinner({value:30, step: 1, min: 1, max: 99});
        $('.historytime').spinner({value:90, step: 10, min: 1, max: 10000});
        $('.spinnerNum').spinner({value:100, step: 10, min: 1, max: 86400});
        $('.historytimeinterval').spinner({value:600, step: 100, min: 1, max: 86400});
        $('.starttime').timepicker({
            autoclose: true,
            minuteStep: 5,
            showSeconds: true,
            showMeridian: false,
            defaultTime:'00:00:00'
        });
        $('.endtime').timepicker({
            autoclose: true,
            minuteStep: 5,
            showSeconds: true,
            showMeridian: false,
            defaultTime:'23:59:59'
        });
	}
	
	var initSwitch = function(){
		$('#multithreading').bootstrapSwitch('state', true);
		$('#emptydir').bootstrapSwitch('state', true);
	}
	

    return {
        //main function to initiate the module
        init: function () {
        	wizardInit();
        	initSpinner();
        	initSwitch();
        	initListener();
        }

    };

}();


jQuery(document).ready(function() {    
	FileCDP.init();
});