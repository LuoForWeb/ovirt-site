var VMInstantJobDetails = function () {
	var logGrid;
	var initLogFlag = false;	//任务日志初始化标志
	var _scrollHeight = 0; //任务日志全局高度
	//初始化任务图示基本信息
	var initJobImgInfo = function(){
		var imgPath = "./img/vm/instant/";
		var nfsState = ['white.png', 'nfs-state-on-b.gif', 'nfs-state-off-b.png', 'nfs-state-online-b.png'];
		var hostState = ['host.png', 'host-on.png', 'host-off.png'];
		var vmState = ['vm.png', 'vm-off.png', 'vm-on.png', 'vm-suspend.png', 'vm-suspend.png'];
		var data = {};
		data.taskuuid = $('#taskuuid').val();
		var jsonData = JSON.stringify(data);
		var updateInterval = 5000;
		var update = function(){
			if(0 == $('#instantflag').size()){
        		clearTimeout(timerTask.VMInstantJobDetails_img);
        		return;
        	}
			$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:'getIntantBaseInfo',p:jsonData}, function(d){
				var info = JSON.parse(d);
				if(8 == info.task_type){
					//如果是迁移,直接跳转到迁移任务监控
					clearTimeout(timerTask.VMInstantJobDetails_img);
					LOCATION('./content/vm/vm_motion_job_details.php?uuid=' + $('#taskuuid').val(),'task');
	        		return;
				}
				$('#serverip').html(info.server_ip);
				$('#hostip').html(info.host_ip);
				$('#vmname').html(info.vm_name);
				$('#instantnfsimg').attr('src', imgPath + nfsState[info.nfs_state]);
				$('#instanthostimg').attr('src', imgPath + hostState[info.host_state]);
				$('#instantvmimg').attr('src', imgPath + vmState[info.vm_state]);
				timerTask.VMInstantJobDetails_img = setTimeout(update, updateInterval);
	    	});
		}
		update();
	}
	
	//日志表格滚动到.. 并重新设置表格样式
	var scroll = function(scrollHeight){
		$('#log').find(".dataTables_scrollBody").scrollTop(scrollHeight);
		$('#logtable').find('tbody > tr > td').css({border: "0px solid #ddd"});
	}
	//设置全局的滚动高度
	var setScrollHeight = function(scrollTop){
		_scrollHeight = scrollTop;
	}
	
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
			data.uuid = $("#taskuuid").val();
			var jsonData = JSON.stringify(data);
			$.post(CONF.AJAXPATH, {m:CONF.M.LOG,f:'getRunningJobLog',p:jsonData}, function(d){
				if(0 == $('#runninglog').size()){
            		clearTimeout(timerTask.VMInstantJobDetails_logGrid);
            		return;
            	}
        		getLiInfo(d);
	    	})
	    	.complete(function() {timerTask.VMInstantJobDetails_logGrid = setTimeout(getlog, updateInterval);});
		}
		getlog(); //得到日志
    }
	
    return {
        //main function to initiate the module
        init: function () {
        	initJobImgInfo();
        	initLogGrid();
        }

    };

}();

jQuery(document).ready(function() {    
	VMInstantJobDetails.init();
});