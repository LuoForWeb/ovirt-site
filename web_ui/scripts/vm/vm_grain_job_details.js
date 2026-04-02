var VMGrainJobDetails = function () {
	
	
	var _pageSize = 200;	//文件每次请求条数
	//当前文件路径, 目录是否完成 true未完成, 下次开始, 路径数组,时间点UUID,缓存文件目录,搜索
	var _path, _isFinished, _nextStart, _tmpFilePath, _firstSearchFlag = true, flushLock = false, _device = "";
	var _sclass = 's';		//文件类型大小
	//模块类型,任务类型,子模块类型
	var _module, _taskType, _subModule, _taskStatus;
	
	var setLevel = function(div, data){
		var labelClass = getLevelClass(data[4].level);
		var content = '<span class="label ' + labelClass + '">' + data[2] + '</span>';
		$(div).html(content);
	}
	//得到日志的显示类型
	var getLevelClass = function(level){
		var levelClass = '';
		switch(level){
			case 1:
				levelClass = "label-success";
				break;
			case 2:
				levelClass = "label-warning";
				break;
			case 3:
				levelClass = "label-danger";
				break;
			default:
				levelClass = "label-info";
				break;
		}
		return levelClass;
	}
	
//	//日志表格滚动到.. 并重新设置表格样式
//	var scroll = function(scrollHeight){
//		$('#log').find(".dataTables_scrollBody").scrollTop(scrollHeight);
//		$('#logtable').find('tbody > tr > td').css({border: "0px solid #ddd"});
//	}
	
    //初始化日志表格
    var initLogGrid = function(){
    	var updateInterval = 5000;
    	var getLiInfo = function(d){
    		var d = JSON.parse(d);
    		var info = "";
    		for(var i=0; i<d.length; i++){
				info += '<li class="list-group-item__log"><div class="col1"><div class="cont contdetail"><div class="cont-col1">' + 
				getIcon(d[i][3].level) + '</div><div class="cont-col2"><div class="desc">' + d[i][2] + '</div>' + 
				'</div></div></div><div class="col2 logtimecol"><div class="date">' + d[i][0] + '</div></div></li>';
    		}
    		if(0 == d.length){
    			info = '<li><div class="col textalignc">' + LANG.UI_TOOLS_NO_DATA + '</div></li>';
    		}
    		$('#runninglog').html(info);
    	}
    	
    	//得到任务ICON CSS
        var getIcon = function(level){
        	if(1 == level){
        		//文件
        		icon = '<div class="label label-success" style="background-color: transparent"><i class="viconfont vicon-wancheng1"></i></div>';
        	}else if(3 == level){
        		icon = '<div class="label label-danger" style="background-color: transparent"><i class="viconfont vicon-cuowu"></i></div>';
        	}else{
        		icon = '<div class="label label-warning" style="background-color: transparent"><i class="viconfont vicon-yichang"></i></div>';
        	}
        	return icon;
        }
		
		var getlog = function(){
			var data = {};
			data.uuid = $("#task_uuid").val();
			var jsonData = JSON.stringify(data);
			$.post(CONF.AJAXPATH, {m:CONF.M.LOG,f:'getVMRunningJobLog',p:jsonData}, function(d){
				if(0 == $('#runninglog').size()){
            		clearTimeout(timerTask.VMJobDetails_logGrid);
            		return;
            	}
        		getLiInfo(d);
	    	})
	    	.complete(function() {timerTask.VMJobDetails_logGrid = setTimeout(getlog, updateInterval);});
		}
		getlog();
    }
    
    var setJobStatus = function(data){
		var labelClass = getLevelClass(data.status);
		var content = '<span class="label label-sm ' + labelClass + '">' + data.statusdes + '</span>';
		$('#status').html(content);
	}
	
	//得到状态的显示类型
	var getLevelClass = function(level){
		var levelClass = '';
		switch(level){
			case 1:
			case 5:
			case 10:
				levelClass = "label-info";
				break;
			case 2:
			case 3:
				levelClass = "label-success";
				break;
			case 4:
				levelClass = "label-default";
				break;
			case 7:
				levelClass = "label-warning";
				break;
			case 6:
			case 8:
				levelClass = "label-danger";
				break;
			default:
				levelClass = "label-info";
				break;
		}
		return levelClass;
	}
    
    //得到任务详细信息
    var initTaskDetails = function(){
    	var updateInterval = 5000;
    	var getDetails = function(){
			var data = {};
			data.uuid = $("#task_uuid").val();
			var jsonData = JSON.stringify(data);
			$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:'getVMGrainJobDetails',p:jsonData}, function(d){
				if(0 == $('#detailsbody').size()){
            		clearTimeout(timerTask.VMJobDetails_taskDetails);
            		return;
            	}
				setTaskDetailsInfo(d);
	    	})
	    	.complete(function() {timerTask.VMJobDetails_taskDetails = setTimeout(getDetails, updateInterval);});
		}
    	getDetails();
    }
    
    //设置任务详情
    var setTaskDetailsInfo = function(d){
    	var data = JSON.parse(d);
    	$('#vmname').html(data.vmname);
		$('#vmname').attr('title', data.vmname);
    	$('#timepoint').html(data.timepoint);
    	setJobStatus(data);
    	_module = data.module;
    	_taskType = data.taskType;
    	_subModule = data.subModule;
    	_taskStatus = data.status;

		if (CONF.VMTYPE_GROUP.PUBLICCLOUD.includes(_subModule)) {
			//AWS的显示
			$('#detailsbody .static-info').eq(1).find('.name').text(LANG.UI_GRAIN_AWS_INSTANCE + ':');
		}
    	
    	if(2 == data.status){
    		var nodataTD = $('#nodatatd');
    		if(1 == nodataTD.length && "" == _path && undefined == _isFinished && 0 == _nextStart){
    			//如果是第一次加载,并且右边初始化的时候没有数据
    			$('#filetbody').empty();
    			initFileList($('#fileshowtype').val());
    		}
    	}
    	setBtnStatus();
    }
    
    //根据任务状态设置按钮权限
    var setBtnStatus = function(){
    	switch(_taskStatus){
	    	case 2:
			case 5:
	    	case 10:
	    		//运行和准备中停止中,禁用运行
	    		setControlBtn('start', false);
	    		setControlBtn('stop', true);
				//停止中状态，变为强制停止
				if (CONF.TASK_STATUS.STOPPING == _taskStatus) {
					$('#stop').html('<a href="javascript:;"><i class="viconfont vicon-ge_suspend-copy"></i> ' + LANG.UI_JOB_FORCE_STOP + '</a>');
				}
	    		break;
	    	case 4:
	    		//停止,禁用停止
	    		setControlBtn('start', true);
	    		setControlBtn('stop', false);
				$('#stopJob').html('<a href="javascript:;"><i class="viconfont vicon-ge_suspend-copy"></i> ' + LANG.UI_JOB_STOP + '</a>');
	    		break;
    		default:
    			//其他状态,开启控制
    			setControlBtn('start', true);
    			setControlBtn('stop', true);
    			break;
    	}
    }
    
    //设置按钮是否可用
    var setControlBtn = function(id, available){
    	if(available){
    		$("#" + id).find('a').removeClass('disablebtn');
    	}else{
    		$("#" + id).find('a').addClass('disablebtn');
    	}
    }
    
    
    
    //事件监听
	var initListener = function(){
		//链接进入文件夹
//		$('.intofile').off().on('click', intoFolder);
		//后退
		$('.backup').on('click', backup);
		
//		$('#fileScrollerDiv').unbind().scroll(scrollMore);
		
		$('#fileScrollerDiv').slimScroll({ height: '470px', color: '#D9DADB' });
		
		$('#fileScrollerDiv').slimScroll().bind('slimscrolling', function(e, pos){
			if(this.scrollHeight - this.clientHeight == this.scrollTop){
				scrollMore();
			}
		});
		
		
		
		$('#start').on('click', function(){
			if($(this).find('a').hasClass('disablebtn')){
				return true;
			}
			var params = {uuid:$('#task_uuid').val(), module:_module, taskType: _taskType, subModule:_subModule, };
			params = JSON.stringify(params);
	    	$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:'startJob',p:params}, function(data){
	    		if(OPREL(data)){
	    		}
	    	});
		});
		
		$('#stop').on('click', function(){
			if($(this).find('a').hasClass('disablebtn')){
				return true;
			}
			var params = {uuid:$('#task_uuid').val(), module:_module, taskType: _taskType, subModule:_subModule, };
			params = JSON.stringify(params);
	    	$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:'stopJob',p:params}, function(data){
	    		if(OPREL(data)){
	    			$('#filetbody').empty();
	    			initFileList();
	    		}
	    	});
		});
		
		$('#searchinput').bind('keydown',function(event){
		    if(event.keyCode == "13") {
		        searchFile(this.value);
		    }
		});
	}
	
	/**
	 * 搜索文件
	 */
	var searchFile = function(value){
		//获取可以跳转路径个数
		var list = $('.filepath .intofile');
		var root_flag = 0;
		//判断当前是否搜索的是根目录
		if(list.length == 1){
			//如果是所有文件(到跟路径)
			root_flag = 1;
		}
		var params = {task_uuid: $('#task_uuid').val(), root_flag:root_flag, start:0, number:_pageSize,
				path:_path,  sclass:_sclass, search_name: value, tmp_file_path: _tmpFilePath, device:_device, 
				first_search_flag: true, showtype:$('#fileshowtype').val()};
		var params = JSON.stringify(params);
//		$('#fileScrollerDiv').scrollTop(0);
//		$('.slimScrollBar').css("top","0");
		Metronic.blockUI({target: '#filediv',animate: true, cenrerY: true,});
		$.post(CONF.AJAXPATH, {m:CONF.M.VM,f:'getVmGrainRecoveryFileDir',p:params}, function(data){
			Metronic.unblockUI('#filediv');
			$('#filetbody').empty();
			_firstSearchFlag = false;
			setGrainFileList(data, true);
			setRootTableDescription(0);
			setFilePath();
		});
	}
	
	//下载细粒度文件
	var grainFileDownload = function(){
		var dataset = this.dataset;
		var isfile = dataset.isfile;
		if('true' == isfile){
			//如果是文件
			var checkFunction = "downloadGrainRecoveryFileCheck";
			var downloadFunction = "downloadGrainRecoveryFile";
		}else if('false' == isfile){
			//如果是目录
			var checkFunction = "downloadGrainRecoveryDirCheck";
			var downloadFunction = "downloadGrainRecoveryDir";
		}
		var params = {taskuuid: $('#task_uuid').val(), path:dataset.path, size:dataset.size, name:dataset.name, 
				device:dataset.device, showtype:$('#fileshowtype').val()};
		var params = JSON.stringify(params);
		$.post(CONF.AJAXPATH, {m:CONF.M.VM,f:checkFunction,p:params}, function(data){
			var arr = JSON.parse(data);
			if(arr.re){
				var href = CONF.AJAXPATH + '?m=' + CONF.M.VM + '&taskuuid=' + 
				$('#task_uuid').val() + '&path=' + encodeURIComponent(dataset.path) + '&size=' + dataset.size + 
				'&name='+ encodeURIComponent(dataset.name)+ '&device=' + encodeURIComponent(dataset.device)
				+ '&showtype=' + $('#fileshowtype').val() + '&f=' + downloadFunction;
				if('false' == isfile){
					//如果是目录，加上操作uuid
					href += '&uuid=' + arr.ext.uuid;
				}
				window.location.href = href;
			}else{
				OPREL(data);
			}
		});
	}
	
	
	//初始化文件列表表格
	var initrBackupFileListDatatable = function(){
		if(_rBackupFileListDatatable == undefined){
			_rBackupFileListDatatable =  $('#datatable').DataTable(getTableDefaultsOpt());
		}
	}
	
	//下拉加载更多
	var scrollMore = function(){
		if(flushLock) return true;
		if(false == _isFinished){
			flushLock = true;
			var params = {task_uuid: $('#task_uuid').val(), root_flag:0, start:_nextStart, number:_pageSize,
					path:_path,  device:_device, sclass:_sclass, tmp_file_path: _tmpFilePath, showtype:$('#fileshowtype').val()};
			if("" != $.trim($('#searchinput').val())){
				//如果有搜索，带着搜索选项
				params.search_name = $.trim($('#searchinput').val());
				params.first_search_flag = _firstSearchFlag;
			}
			var params = JSON.stringify(params);
			Metronic.blockUI({target: '#filediv',animate: true, cenrerY: true,});
			$.post(CONF.AJAXPATH, {m:CONF.M.VM,f:'getVmGrainRecoveryFileDir',p:params}, function(data){
				setGrainFileList(data);
				Metronic.unblockUI('#filediv');
				flushLock = false;
			});
		}
	}
	
	//切换展示模式
	var fileShowTypeChange = function(){
		$('#filetbody').empty();
		initFileList(this.value);
	}
	
	//初始化展示模式选择框
	var initFileShowType = function(){
		var params = {task_uuid: $('#task_uuid').val()};
		var params = JSON.stringify(params);
		$.post(CONF.AJAXPATH, {m:CONF.M.VM,f:'getVmGrainRecoveryShowType',p:params}, function(data){
			var arr = JSON.parse(data);
			$.each(arr, function(index, value){
				//移除不对应的展示模式
				$("#fileshowtype option[value='" + value + "']").remove();
			});
			$('#fileshowtype').selectpicker({
	            iconBase: 'fa',
	            tickIcon: 'fa-check'
	        });
			//绑定事件
			$('#fileshowtype').on('change', fileShowTypeChange);
			//初始化文件列表, ZSTACK默认按物理磁盘设备显示
			if(_subModule == CONF.VM_TYPE.ZSTACK || _subModule == CONF.VM_TYPE.XSKY){
	    		$('#fileshowtype').selectpicker('val', "2");
	    		$('#fileshowtype').selectpicker('refresh');
	    	}
			initFileList($('#fileshowtype').val());
		});
	}
	
	//初始化文件列表
	var initFileList = function(showtype){
		_pathArr = [];
		var params = {task_uuid: $('#task_uuid').val(), root_flag:1, start:0, number:_pageSize,
					path:'',  sclass:_sclass, tmp_file_path: '',device:'', showtype:showtype};
		var params = JSON.stringify(params);
		$.post(CONF.AJAXPATH, {m:CONF.M.VM,f:'getVmGrainRecoveryFileDir',p:params}, function(data){
			setGrainFileList(data);
			setRootTableDescription(1);
			setFilePath();
		});
	}
	
	//设置文件列表
	var setGrainFileList = function(data, intoDir){
		var arr = JSON.parse(data);
		if(!arr['re']){
			//如果获取消息失败,返回错误提示
			var listDiv = '<tr><td class="tacenter" id="nodatatd">' + LANG.UI_TOOLS_NO_DATA + '</td></tr>';
			$('#filetbody').append(listDiv);
			return OPREL(data);
		}
		if(arr['filelist'].length > 0 && arr['filelist'][0][3]['path'] != "/"){
			//如果有数据,设置device
			_device = arr['filelist'][0][3]['device'];
		}
		_path = arr['path'];
		_isFinished = arr['isFinished'];
		_nextStart = arr['nextStart'];
		_tmpFilePath = arr['tmpFilePath'];
		var fileList = arr['filelist'];
		var listDiv = "";
		for(var i=0; i<fileList.length; i++){
			if(fileList[i][3].isfile){
				//文件直接显示
				listDiv += '<tr><td><div><i class="filetype-small ' + fileList[i][3].sclass + '"></i> <span class="lh24">' + 
				'<p class="margin0 ellipsis170" data-type="' + fileList[i][3].type + '" data-device="' + 
				fileList[i][3].device + '" title="' + htmlEncode(fileList[i][3].path) + '">' + fileList[i][0] + '</p>' 
				+ '</span></div></td>';
				listDiv += '<td>' + fileList[i][1] + '</td>';
				listDiv += '<td>' + fileList[i][2] + '</td>';
				if("0" == fileList[i][3].size || 4 == fileList[i][3].btype){
					//如果文件没有内容  或  是没有目标的link文件
					listDiv += '<td></td></tr>';
				}else{
					listDiv += '<td><a class="grainfiledownload" title="' + LANG.UI_PUBLIC_DOWNLOAD + 
					 '" data-device="' + htmlEncode(fileList[i][3].device) + 
					 '" data-isfile="' + fileList[i][3].isfile + 
					 '" data-path="' + 
					htmlEncode(fileList[i][3].path) + '" data-size="' + fileList[i][3].size + '" data-name="' + htmlEncode(fileList[i][0]) +
					'">' + '<i class="viconfont vicon-ge_download"></i></a></td></tr>';
				}
				
			}else{  
				//如果是目录,增加超链接显示
				listDiv += '<tr><td style="width: 45%;"><a data-path="' + htmlEncode(fileList[i][3].path) +  '" data-device="' + 
				fileList[i][3].device + '" name="'  + fileList[i][3].name + '" class="intofile" title="' + htmlEncode(fileList[i][3].path) + '">' + 
				'<div class="ellipsis170"><i class="filetype-small ' + fileList[i][3].sclass + '"></i> <span class="lh24">' +
				htmlEncode(fileList[i][0]) + '</span></div></a></td>';
				listDiv += '<td style="width:12%;">' + "--" + '</td>';
				listDiv += '<td style="width:25%;">' + fileList[i][2] + '</td>';
//				listDiv += '<td style="width:18%;"></td></tr>';
				listDiv += '<td><a class="grainfiledownload" title="' + LANG.UI_PUBLIC_DOWNLOAD + 
				'" data-isfile="' + fileList[i][3].isfile + 
				 '" data-device="' + htmlEncode(fileList[i][3].device) + '" data-path="' + 
				htmlEncode(fileList[i][3].path) + '" data-size="' + fileList[i][3].size + '" data-name="' + htmlEncode(fileList[i][0]) +
				'">' + '<i class="viconfont vicon-ge_download"></i></a></td></tr>';
			}
		}
		//如果没有数据
		if(0 == fileList.length){
			listDiv = '<tr><td class="tacenter" id="nodatatd" style="width: 100%;">' + LANG.UI_TOOLS_NO_DATA + '</td></tr>';
		}
		
		$('#filetbody').append(listDiv);
		$('#fileScrollerDiv').slimScroll({});
		if($('#filetbody').height() > 35000){
			//如果已经加载了很多,滚动条自动往上面移动1px
			$('.slimScrollBar').css("top","500px");
//			
		}
		if(intoDir){
			$('.slimScrollBar').css("top","0");
		}
		
		//下载文件
		$('.grainfiledownload').off().on('click', grainFileDownload);
		
		//链接进入文件夹
		$('.intofile').off().on('click', intoFolder);
		
	}
	
	//进入文件夹事件
	var intoFolder = function(){
		var dataset = this.dataset;
		var root_flag = 0;
		_firstSearchFlag = true;
		if(dataset.root){
			//如果是所有文件(到跟路径)
			root_flag = 1;
		}
//		3.进入目录
//		$('#fileScrollerDiv').scrollTop(0);
//		$('.slimScrollBar').css("top","0");
		var device = _device;
		if(undefined != dataset.device){
			//列表里面有device,路径里面没有
			device = dataset.device;
		}
		var params = {task_uuid: $('#task_uuid').val(), root_flag:root_flag, start:0, number:_pageSize,
				path:dataset.path,  sclass:_sclass, tmp_file_path: '',device:device, showtype:$('#fileshowtype').val()};
		var params = JSON.stringify(params);
		Metronic.blockUI({target: '#filediv',animate: true, cenrerY: true,});
		$.post(CONF.AJAXPATH, {m:CONF.M.VM,f:'getVmGrainRecoveryFileDir',p:params}, function(data){
			Metronic.unblockUI('#filediv');
			var arr = JSON.parse(data);
			if(!arr['re']){
				return OPREL(data);
			}
			$('#filetbody').empty();
			$('#searchinput').val('');
			setGrainFileList(data, true);
			setRootTableDescription(root_flag);
			setFilePath();
		});
	}
	
	//根据展示模式和跟路径
	var setRootTableDescription = function(rootFlag){
		var showtype = $('#fileshowtype').val();
		if(0 == rootFlag){
			//如果不是root
			$('#thirdcolumn').html(LANG.UI_GRAIN_TABLE_MODIFY_TIME);
		}else{
			//如果是root
			if("2" == showtype || "3" == showtype){
				//并且展示模式是 物理磁盘或逻辑卷
				$('#thirdcolumn').html(LANG.UI_GRAIN_TABLE_FILESYSTEM);
			}
		}
		
	}
	
	//设置路径显示
	var setFilePath = function(){
		var filepathDiv = $('.filepath');
		var aStr = '<a class="intofile" data-root="true" title="">' + LANG.UI_BACKUP_FILE_ALL + '</a> > ';
		var pathArr = _path.split("/");
		//如果是主目录
		if("" == _path){
			filepathDiv.html(aStr);
			//链接进入文件夹
			$('.intofile').off().on('click', intoFolder);
			return;
		}
		//如果是根目录 /
		if("/" == _path){
			aStr += '<a class="intofile" data-path="/" data-root="false" title="/">' + _path + '</a>';
			filepathDiv.html(aStr);
			//链接进入文件夹
			$('.intofile').off().on('click', intoFolder);
			return;
		}
		
		if("" == pathArr[0]){
			//特殊处理一下根目录,linux适用
			pathArr[0] = "/";
		}
		
		//得到显示的值,这里主要是长度截取
		var getValue = function(value){
			var key = '';
			if(value.length > 14){
				key = value.substring(0, 14) + '...';
			}else{
				key = value;
			}
			return htmlEncode(key);
		}
		
		var getValuePath = function(pathArr, num){
			var pathValue = "";
			for(var i=0; i<= num; i++){
				pathValue += pathArr[i] + "/";
			}
			var pathValue = pathValue.substr(0, pathValue.length - 1);
			if("//" == pathValue.substr(0, 2)){
				pathValue = pathValue.substr(1);
			}
			return htmlEncode(pathValue);
		}
		
		var forLength = pathArr.length;
		if(forLength >= 3){
			var end = forLength - 1;	//最后一项
			var send = forLength - 2;	//倒数第二项
			var endPath = getValuePath(pathArr, end);
			var sendPath = getValuePath(pathArr, send);
			//如果路径过长,只显示后面两层
			aStr += '... > ';
			aStr += '<a  data-path="' + sendPath +  '" class="intofile" title="' + sendPath + '">' + 
					getValue(pathArr[send])+ '</a> > ';
			aStr += '<a  data-path="' + endPath +  '" class="intofile" title="' + endPath + '">' + 
					getValue(pathArr[end])+ '</a>';
			
			filepathDiv.html(aStr);
			//链接进入文件夹
			$('.intofile').off().on('click', intoFolder);
			return;
		}
		
		for(var i=0; i<pathArr.length; i++){
			var eachPath = getValuePath(pathArr, i);
			aStr += '<a  data-path="' + eachPath +  '" class="intofile" title="' + eachPath + '">' + 
					getValue(pathArr[i])+ '</a> > ';
		}
		aStr = aStr.substring(0, aStr.length - 4);
		filepathDiv.html(aStr);
		//链接进入文件夹
		$('.intofile').off().on('click', intoFolder);
		return;
	}
	//后退
	var backup = function(){
		if("" == _path){
			//是否在根目录
			UIToastr.showInfo(LANG.UI_BACKUP_FILE_NO_UP_PATH_TITLE, LANG.UI_BACKUP_FILE_NO_UP_PATH_VALUE);
			return;
		}
		if("/" == _path){
			//如果是所有文件(到跟路径)
			path = '';
			root_flag = 1;
		}else{
			var pathArr = _path.split("/");
			if(1 == pathArr.length){
				//windows 第一层
				path = '';
				root_flag = 1;
			}else{
				var pathValue = "";
				for(var i=0; i< pathArr.length - 1; i++){
					pathValue += pathArr[i] + "/";
				}
				var pathValue = pathValue.substr(0, pathValue.length - 1);
				if("" == pathValue){
					pathValue = "/";
				}
				root_flag = 0;
				path = pathValue;
			}
			
		}
//		3.进入目录
		var params = {task_uuid: $('#task_uuid').val(), root_flag:root_flag, start:0, number:_pageSize,
				path:path,  sclass:_sclass, device:_device, tmp_file_path: '',showtype:$('#fileshowtype').val()};
		var params = JSON.stringify(params);
		Metronic.blockUI({target: '#filediv',animate: true, cenrerY: true,});
		$.post(CONF.AJAXPATH, {m:CONF.M.VM,f:'getVmGrainRecoveryFileDir',p:params}, function(data){
			Metronic.unblockUI('#filediv');
			$('#filetbody').empty();
			$('#searchinput').val('');
			setGrainFileList(data, true);
			setRootTableDescription(root_flag);
			setFilePath();
		});
	}
	
	var htmlEncode = function (str) {
		var s = "";
        if(str.length == 0) return "";
        s = str.replace(/&/g,"&amp;");
        s = s.replace(/</g,"&lt;");
        s = s.replace(/>/g,"&gt;");
//        s = s.replace(/ /g,"&nbsp;");
        s = s.replace(/\'/g,"&#39;");
        s = s.replace(/\"/g,"&quot;");
        return s; 
	}
	var htmlDecode = function (str) {
		var s = "";
        if(str.length == 0) return "";
        s = str.replace(/&amp;/g,"&");
        s = s.replace(/&lt;/g,"<");
        s = s.replace(/&gt;/g,">");
        s = s.replace(/&nbsp;/g," ");
        s = s.replace(/&#39;/g,"\'");
        s = s.replace(/&quot;/g,"\"");
        return s; 
	}
	
	
    
    return {
        //main function to initiate the module
        init: function () {
            initLogGrid();
            initTaskDetails();
//            initFileList();
            initFileShowType();
            initListener();
        }

    };

}();

jQuery(document).ready(function() {    
	VMGrainJobDetails.init();
});