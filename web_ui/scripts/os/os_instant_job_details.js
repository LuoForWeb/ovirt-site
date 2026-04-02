var OSInstantJobDetails = function () {
    //初始化任务图示基本信息
    var initJobImgInfo = function(){
        var data = {};
		data.taskuuid = $('#taskuuid').val();
		var jsonData = JSON.stringify(data);
		var updateInterval = 2000;
        var update = function(){
            if(0 == $('#instantflag').size()){
        		clearTimeout(timerTask.OSInstantJobDetails_img);
        		return;
        	}
            $.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:'getOSIntantBaseInfo',p:jsonData}, function(d){
                var data =  JSON.parse(d);
                if(51 == data.task_type){
					//如果是迁移,直接跳转到迁移任务监控
					clearTimeout(timerTask.OSInstantJobDetails_img);
					LOCATION('./content/os/os_motion_job_details.php?uuid=' + $('#taskuuid').val(),'task');
	        		return;
				}
                //设置服务器IP
                $('#serverip').html(data.server_ip);
                //设置主机IP
				$('#hostip').html(data.host_ip);
                //设置主机状态
                if(data.online_flag){
                    $("#oshostabnormal").hide();
                    $("#oshostnormal").show();
                }else{
                    $("#oshostabnormal").show();
                    $("#oshostnormal").hide();
                }
                //设置任务状态
                // UNKNOWN:0,         		//未知的任务状态
                // WAITTING:1,       		//任务等待运行
                // RUNNING:2,         		//任务正在运行
                // PAUSED:3,          		//任务暂停
                // STOPPED:4,         		//任务停止
                // STOPPING:5,        		//任务停止中
                // NETWORK_FAULT:6,   		//网络故障
                // ABNORMAL:7,        		//任务已完成但异常
                // ERROR:8,           		//错误
                // SYNC:9,					//任务同步
                // PREPARING:10,			//准备中
                // PAUSING:11,	    		//任务暂停中
                // STARTING:12,        	//启动中
                // FINISHED:13,       		//已完成
                // TAKEOVER:14,       		//接管
                // TAKEOVER_STARTING:15,   //启动接管
                // TAKEOVER_STOPPING:16,   //停止接管
                // SUCCESSED:17,			//任务成功
                switch(data.task_status){

                    case CONF.TASK_STATUS.RUNNING: //运行
                    case CONF.TASK_STATUS.STOPPING://停止中
                    case CONF.TASK_STATUS.PREPARING://准备中
                    case CONF.TASK_STATUS.PAUSING://任务暂停中
                    case CONF.TASK_STATUS.STARTING://启动中
                    case CONF.TASK_STATUS.FINISHED://已完成
                        $('#taskabnormal').hide();
                        $('#tasknormal').show();
                        // $('#total-progress').css({width: '0%'});
                    break;
                    case CONF.TASK_STATUS.WAITTING: //等待
                    case CONF.TASK_STATUS.PAUSED: //暂停
                    case CONF.TASK_STATUS.STOPPED: //停止
                    case CONF.TASK_STATUS.NETWORK_FAULT: //网络故障
                    case CONF.TASK_STATUS.ABNORMAL://任务已经完成但异常
                    case CONF.TASK_STATUS.ERROR://错误
                    case CONF.TASK_STATUS.SUCCESSED://任务成功
                        $('#tasknormal').hide();
                        $('#taskabnormal').show();
                        break;
                }
                timerTask.OSInstantJobDetails_img = setTimeout(update, updateInterval);
               
            });
        };
        update();
    }
    //初始化日志表格
    var initLogGrid = function(){
    	var updateInterval = 5000;
    	var getLiInfo = function(d){
    		var d = JSON.parse(d);
    		var info = "";
    		for(var i=0; i<d.length; i++){
				info += '<li><div class="col1"><div class="cont contdetail"><div class="cont-col1">' + 
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
        		icon = '<div class="label label-sm label-success"><i class="fa fa-check"></i></div>';
        	}else if(3 == level){
        		icon = '<div class="label label-sm label-danger"><i class="fa fa-times"></i></div>';
        	}else{
        		icon = '<div class="label label-sm label-warning"><i class="fa fa-exclamation"></i></div>';
        	}
        	return icon;
        }
		
		var getlog = function(){
			var data = {};
			data.uuid = $("#taskuuid").val();
			var jsonData = JSON.stringify(data);
			$.post(CONF.AJAXPATH, {m:CONF.M.LOG,f:'getRunningJobLog',p:jsonData}, function(d){
				if(0 == $('#runninglog').size()){
            		clearTimeout(timerTask.OSInstantJobDetails_logGrid);
            		return;
            	}
        		getLiInfo(d);
	    	})
	    	.complete(function() {timerTask.OSInstantJobDetails_logGrid = setTimeout(getlog, updateInterval);});
		}
		getlog(); //得到日志
    }
	
    return {
        init:function(){
            initJobImgInfo();
        	initLogGrid();
        }
    }

}();
jQuery(document).ready(function(){
    OSInstantJobDetails.init();
});