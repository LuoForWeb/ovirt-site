//系统升级
var checkUpdate = function () {
	var grid;
	var refreshFlag = false;  //是否刷新状态标志
	var refreshindex = -1;  //需要刷新的位置
	var refreshInterval //刷新状态计时器
	
	
	
	var addListeners = function(){
	    
	}
	
	
	//开始下载
	var startDown = function(Index){
		
		//得到当前的数据
    	var data = grid.getDataTable().data();
    	var params = data[Index][5];
    	if(!params) return;
    	
		//只有状态为0 即未下载的状态才能下载 其他状态除非删除了后才能下载;
//		if(params.status != 0){
//			UIToastr.showWarning('开始下载', '正在下载中或已下载');
//			return;
//		}
		var data = {};
		data.url = params.download_url;
		data.filename = params.newname;
		data.filesize = params.size;
		data.md5 = params.md5;
		data = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM, f:'downLoadOnline', p:data}, function(d){
	    	if(OPREL(d)){
	    		grid.getRefresh({});
//	    		UIToastr.showSuccess('下载升级包', '开始下载!');
	    	}
		});
	}
	
	//暂停下载
	var stopDown = function(Index){
		
		//得到当前的数据
    	var data = grid.getDataTable().data();
    	var params = data[Index][5];
    	if(!params) return;
    	
		//只有状态为下载中 可暂停任务
//		if(params.status != 2){
//			UIToastr.showWarning('暂停下载', '任务未开始下载或已下载完成!');
//			return;
//		}
		var thisData = {};
    	thisData.file_name = params.newname;
    	thisData = JSON.stringify(thisData);
    	$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM, f:'pauseUpdateOnline', p:thisData}, function(d){
	    	if(OPREL(d)){
	    		grid.getRefresh({});
	    	}
		});
	}
	
	//取消下载 此操作会文件,如果文件信息已经写入数据库即下载已完成 请在上个页面系统升级中去删除安装包
	var cancelDown = function(Index){
		//得到当前的数据
    	var data = grid.getDataTable().data();
    	var params = data[Index][5];
    	if(!params) return;
    	
    	//状态为未下载或者已下载完成
//    	if(params.status == 0 || params.status == 1){
//			UIToastr.showWarning('取消下载', '此安装包未下载或已下载完成!');
//			return;
//		}
    	var thisData = {};
    	thisData.md5 = params.md5;
    	thisData.file_name = params.newname;
    	thisData = JSON.stringify(thisData);
    	$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM, f:'deleteUpdateAPK', p:thisData}, function(d){
	    	if(OPREL(d)){
	    		grid.getRefresh({});
	    	}
		});
	}
	
	//设置当前行的按钮是否可显示
	var setClickShow = function($thisIndex){
		//先获数据
		var Index = $thisIndex.parents('tr').get(0)._DT_RowIndex
		//得到当前的数据
    	var data = grid.getDataTable().data();
    	var params = data[Index][5];
    	if(!params) return;
		//获取到父级
		var parentDiv = $thisIndex.parent();
		//得到对应按钮的三个按钮
		//开始下载
		var startBut = parentDiv.find(".downOpt");
		//取消下载
		var cancelBut = parentDiv.find(".cancelOpt");
		//暂停下载
		var stopBut = parentDiv.find(".stopOpt");
		
		//根据当前的状态设置开始和取消是否可点击
		switch (params.status){
				//未下载
			case 0:
				cancelBut.css("opacity","0.4");
				cancelBut.css("cursor","default");
				cancelBut.unbind('click');
				
				stopBut.css("opacity","0.4");
				stopBut.css("cursor","default");
				stopBut.unbind('click');
				break;
				//已下载完成
			case 1:
				cancelBut.css("opacity","0.4");
				cancelBut.css("cursor","default");
				startBut.css("opacity","0.4");
				startBut.css("cursor","default");
				cancelBut.unbind('click');
				startBut.unbind('click');
				
				stopBut.css("opacity","0.4");
				stopBut.css("cursor","default");
				stopBut.unbind('click');
				break;
				//下载中
			case 2:
				startBut.css("opacity","0.4");
				startBut.css("cursor","default");
				startBut.unbind('click');
				break;
				//下载百分百
			case 3:
				startBut.css("opacity","0.4");
				startBut.css("cursor","default");
				startBut.unbind('click');
				
				stopBut.css("opacity","0.4");
				stopBut.css("cursor","default");
				stopBut.unbind('click');
				break;
				//暂停中
			case 4:
				stopBut.css("opacity","0.4");
				stopBut.css("cursor","default");
				stopBut.unbind('click');
				break;
				
		}
		
		//这里再加一个判断是否有下载链接，如果没有下载链接开始下载置灰不可点击
		if(params.download_url == null || params.download_url =="" || params.download_url == undefined){
			startBut.css("opacity","0.4");
			startBut.css("cursor","default");
			startBut.unbind('click');
		}
		
		
	}
	
	
	var handleRecords = function () {
		Metronic.blockUI({target: '#DownloadMangerDiv',animate: true});
		//表格加载完成后再检测是否有下载资格并返回错误提示
		$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM, f:'getDataFromServer', p:{}}, function(d){
			//锁定表格
			
			
			//先获取表格 然后展示数据
	    	var dataTableOpt = {
				'columnDefs' : [{
	                'orderable': false,
	                'targets': [0, 1, 2, 3]
				}],
				"order": [
	                [3, "desc"]
	            ],
	    	};
	    	grid = new Datatable();
			var data = {m:CONF.M.SYSTEM,f:'getUpdateMsgFromOnline',p:{getData:d}};
			grid.setAjaxParam(data);
			grid.init({src: $("#OnlineTable"), showDetail:false, dataTable:dataTableOpt,onDataLoad:initRow});
			
			
			var datas = JSON.parse(d);
			if(!datas.re){
				$("#hintContent").empty().text(datas.msg);
				$("#hintModal").modal({});
			}
			Metronic.unblockUI('#DownloadMangerDiv');
			
		});
		
	}
	
	//初始化后检测状态
	var checkStatus = function(){
		//得到表格所有数据
		var data = grid.getDataTable().data();
		//得到额外附带信息
//		var paramsList = $('tbody > tr').find('td:eq(5)');
		for(var i=0;i<data.length;i++){
			if(data[i][5].status ==2 || data[i][5].status == 3){
				refreshFlag = true;
				refreshindex = i;
				timeTask();
				break;
			} 
		}
	}
	
	//实时刷新状态
	var timeTask = function(){
		if($("#OnlineTable").length == 0){
			clearTimeout(refreshInterval);
			return;
		}
		if(!refreshFlag){
			clearTimeout(refreshInterval);
			return;
		}
		if(refreshindex == -1){
			clearTimeout(refreshInterval);
			return;
		}
		var timeInterval =  1000;
		var data = grid.getDataTable().data();
		
		var statusDiv = $('#OnlineTable tbody > tr').find('td:eq(4)');
		var paramsList = $('#OnlineTable tbody > tr').find('td:eq(5)');
		var jsondata = {};
		jsondata.filename = data[refreshindex][5].newname;
		jsondata.md5 = data[refreshindex][5].md5;
		jsondata.filesize = data[refreshindex][5].size;
		jsondata = JSON.stringify(jsondata);
		//同步请求
		$.ajax({ 
			type: "post", 
	        url: CONF.AJAXPATH, 
	        async:true, 
	        data:{m:CONF.M.SYSTEM,f:'checkStatusOfOnline', p:jsondata},
	        success: function(data){ 
	        	var datas = JSON.parse(data);
	        	//设置状态
	        	setStatus(statusDiv[refreshindex], datas.status, datas.msg);
	        	if(datas.status == 1){
	        		Metronic.blockUI({target: '#DownloadMangerDiv',animate: true});
					clearTimeout(refreshInterval);
					refreshFlag = false;
					LOCATION('./content/platform/settings/settingstab/system_upgrade.php', 'setting_manager');
					return;
				}
	        	refreshInterval = setTimeout(timeTask,timeInterval);
	        } 
		});
	}
	
	
	
	
	
	//表格初始化
	var initRow =function(){
		var data = grid.getDataTable().data();
		var statusDiv = $('#OnlineTable tbody > tr').find('td:eq(4)');
		var detailsDiv = $('#OnlineTable tbody > tr').find('td:eq(5)');
		//初始化表格状态
		for(var i=0; i<statusDiv.length; i++){
			setStatus(statusDiv[i], data[i][5]['status'],data[i][4]);
		}
		//初始化表格详情
		for(var i=0;i<detailsDiv.length;i++){
			setDetails(detailsDiv[i]);
		}
		
		 //添加点击事件
		//查看详情
        $(".detailsOpt").on('click',function(){
        	modalDeytails($(this).parents('tr').get(0)._DT_RowIndex);
        })
        //开始下载
         $(".downOpt").on('click',function(){
        	 startDown($(this).parents('tr').get(0)._DT_RowIndex);
        })
        //暂停下载
         $(".stopOpt").on('click',function(){
        	 stopDown($(this).parents('tr').get(0)._DT_RowIndex);
        })
        //取消下载
         $(".cancelOpt").on('click',function(){
        	 cancelDown($(this).parents('tr').get(0)._DT_RowIndex);
        })
        //操作按钮是否可点击事件
        $(".dropdown-toggle").on('click',function(){
        	 setClickShow($(this));
        })
        checkStatus();
		
	}
	
	//得到状态标签
	var setStatus = function(div, status, statusStr){
		var labelClass = getStatusClass(status);
		var content = '<span class="label label-sm ' + labelClass + '">' + statusStr + '</span>';
		$(div).html(content);
	}
	//得到状态的显示类型0为未下载 1为已下载 2为已下载百分比 3下载失败
	var getStatusClass = function(status){
		var statusClass = '';
		switch (status){
				//未下载
			case 0:
				statusClass = "label-default";
				break;
				//已下载
			case 1:
				statusClass = "label-success";
				break;
				//下载中
			case 2:
				statusClass = "label-info";
				break;
				//下载百分百
			case 3:
				statusClass = "label-success";
				break;
				//暂停中
			case 4:
				statusClass = "label-warning";
				break;
		}	
		return statusClass;
	}
	
	//得到详情数据
	var setDetails = function(div){
		
		var button = '<div class="btn-group  positionabs">';
		button += '<button type="button" class="btn btn-success btn-sm   dropdown-toggle" data-toggle="dropdown" ' + 
				'data-hover="dropdown" data-delay="1000" data-close-others="true">' + 
				'<i class="glyphicon glyphicon-hand-up"></i> ' + LANG.UI_PUBLIC_OPERATION + ' <i class="fa fa-angle-down"></i>' + 
				'</button>' + 
				'<ul class="dropdown-menu min-width100" role="menu">';
		button += '<li class="downOpt"><a href="javascript:;" ><i class="glyphicon glyphicon-download-alt"></i> ' +  LANG.UI_SETTINGS_UPDATE_START_DOWNLOAD + '</a></li>';
		button += '<li class="stopOpt"><a href="javascript:;" ><i class="glyphicon glyphicon-list-alt"></i> ' + LANG.UI_SETTINGS_UPDATE_PAUSE_DOWNLOAD + '</a></li>';
		button += '<li class="cancelOpt"><a href="javascript:;" ><i class="glyphicon glyphicon-stop"></i> ' + LANG.UI_SETTINGS_UPDATE_CANCEL_DOWNLOAD + '</a></li>';
		button += '<li class="detailsOpt"><a href="javascript:;" ><i class="glyphicon glyphicon-list-alt"></i> ' + LANG.UI_MICROSOFT365_VIEW_DETAILS  + '</a></li>';
		button += '</ul></div>';
		$(div).html(button);
//		var des = '<button class="btn green-haze updateLogDetails"><i class="fa fa-share"></i> 详情</button>';
//    	$(div).html(des);
    	return;
	}
	
//	var modalDeytails = function(Index){
//		//得到当前的数据
//    	var data = grid.getDataTable().data();
//    	var params = data[Index][5];
//    	if(!params) return;
//    	
//    	//得到详情数据
//		var detailsData = params.log_attention;
//		//得到日志列表和注意事项列表
//		var logList = [];
//		var attentionList = [];
//		for(var i =0;i<detailsData.length;i++){
//			logList = logList.concat(detailsData[i].log)
//			attentionList = attentionList.concat(detailsData[i].attention)
//		}
//		//得到有序标签
//		var loghtml = createOl(logList);
//		var attentionhtml = createOl(attentionList);
//		
//		//清空标签并载入标签
//		$("#logContent").empty().html(loghtml);
//		$("#attentionContent").empty().html(attentionhtml);
//		//展示模态框
//		$("#detailsModal").modal({"width":"70%","height":"500px"});
//		
//		
//	}
	
	
	var modalDeytails = function(Index){
    	//得到当前的数据
    	var data = grid.getDataTable().data();
    	var params = data[Index][5];
    	var log_attention = params.log_attention;
    	var htmlStr = "";
    	//得到日志数据
    	if(log_attention == null || log_attention == undefined || log_attention == ""){
    		$(".timeLine").empty().html(LANG.UI_SETTINGS_LOG_NULL);
        	$('#detailsModal').modal({'width': '1200px','height': '700px'});
        	return;
    	}
    	
    	for(var i=0; i<log_attention.length;i++){
    		var logStr = "";
    		for(var il=0;il<log_attention[i].log.length;il++){
    			if(log_attention[i].log[il] == ""){continue;}
    			logStr += '<span">'+(il+1)+'</span>'+". "+ log_attention[i].log[il]+"<br/>";
    		}
    		var attentionStr = "";
    		for(var ia=0;ia<log_attention[i].attention.length;ia++){
    			if(log_attention[i].attention[ia] == ""){continue;}
    			attentionStr += '<span">'+(ia+1)+'</span>'+". "+ log_attention[i].attention[ia]+"<br/>";
    		}
    		var extraname = "";
    		if(log_attention[i].extraname == "" || log_attention[i].extraname == undefined || log_attention[i].extraname == null || log_attention[i].extraname == "[]"){
    			extraname = "";
    		}else{
    			extraname = log_attention[i].extraname;
    		}
    		
    		var logdisplay = (logStr == "")? "display-none": "";
    		var attentiondisplay = (attentionStr == "")? "display-none": "";
    		
    		htmlStr += '<li>'+
			            	'<p>'+log_attention[i].version+"  "+extraname+'<span>'+log_attention[i].put_time+'</span></p>'+
			            	'<div class="con">'+
			            		'<h2 class="'+logdisplay+'">'+LANG.UI_SETTINGS_UPDATE_PUT_LOG+'</h2>'+
			            			'<span>'+logStr+'</span>'+
			            		'<h2 class="'+attentiondisplay+'">'+LANG.UI_SETTINGS_UPDATE_ATTENTION+'</h2>'+
			            			'<span>'+attentionStr+'</span>'+
			            	'</div>'+
			            '</li>';
    	}
    	
    	$(".timeLine").empty().html(htmlStr);
    	
    	$('#detailsModal').modal({'width': '1300px','height': '90%'});
    	
    }
	
	
	//传入数组生成有序排序html标签
	var createOl = function(list){
		var str = '<ol>';
		for(var i=0;i<list.length;i++){
			str += '<li>'+list[i]+'</li>'
		}
		str += '</ol>'
		return str;
	}
	
	
	
	
	
	
	
    return {
        init: function () {
        	//添加事件
        	addListeners();
        	handleRecords();
//        	initNodeSelect();
        }
    };

}();

jQuery(document).ready(function(){
	checkUpdate.init();
})