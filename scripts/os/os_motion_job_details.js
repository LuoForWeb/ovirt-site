var OSMotionJobDetails = function () {
    var logGrid;
	//控制详情刷新的全局变量 插入到第几条后、插入信息、详情的页码
	var detailsIndex = 0,detailsInfo = null;
    var errorflag  =  false;
    //初始化基本信息
    var initBasicInfo = function(){
        var  updateInterval = 2000;
        var init =  function(){
            if(0 == $('#taskuuid').size()){
        		clearTimeout(timerTask.OSJobDetails_taskRunningInfo);
        		return;
        	}
            var data = {};
            data.uuid =  $("#taskuuid").val();
            data = JSON.stringify(data);
            $.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:'getOSMotionBaseInfo',p:data}, function(d){setBasicInfo(d, timerTask.OSJobDetails_taskRunningInfo)});
			timerTask.OSJobDetails_taskRunningInfo = setTimeout(init, updateInterval);
        }
        init();
    }
    //设置基本信息
    var setBasicInfo =  function(data, timeoutID){
        data  = JSON.parse(data);
        //设置图片
        initJobImgInfo(data);
        //tab1
        $('#taskName').html(data.taskName);
        $('#moduleType').html(data.moduleType);
        $('#taskType').html(data.taskType);		
        if(data.status){
			$('#status').html('<span class="label ' + getStatusLevelClass(data.status_num) + '" >' + data.status + '</span>');
		}
		$('#totalSize').html(data.totalSize);
		$('#currentSize').html(data.currentSize);
        $('#speed').html(data.speed);
		$('#progress').html(data.progress);
        $('#startTime').html(data.startTime);
        $('#intervalTime').html(data.intervalTime);
        //tab2
        $("#transportEncrypt").html(getFlagLevelInfo(data.transportStrategy.encrypt));
		// 传输加密算法
		if(data.transportStrategy.encrypt){
			$('.transfer-encrypt-method-div').show();
			let encryptMethod = data.transportStrategy.encrypt_method;
			let method = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_RSA;
			if(encryptMethod == 2){
				method = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_SM;
			}
			$('#transferEncryptMethod').html(method);
		}else{
			$('.transfer-encrypt-method-div').hide();
		}
		$('#speedlimit').html(data.speed_limit.value);
		$('#speedlimit').prop('title', data.speed_limit.des);
		$('#createTime').html(data.createTime);
        //tab3
        $('#threadNum').html(data.thread_num);
        //传输网络
        $("#transferNetwork").html(data.transportStrategy.network);
        let reconnect_times = data.transportStrategy.reconnect_times + LANG.UI_VOL_CDP_BACKUP_TIMES;
		let reconnect_interval = data.transportStrategy.reconnect_interval + LANG.UI_PUBLIC_SECOND;
		// 重连次数
		if(parseInt(data.transportStrategy.reconnect_times) == 0){
			$("#reconnectTimes").html(LANG.UI_GLOBAL_STRATEGY_RECONNECT_TIMES_INFINITE);
		}else{
            $('#reconnectTimes').html(reconnect_times);
		}
		// 重连时间间隔
		$('#reconnectInterval').html(reconnect_interval);
        //重建分区
		$("#builtSize").html(getFlagLevelInfo(data.reparted_flag));
        //引导恢复
        if(data.osType ==  "1"){
            $('.recoverOsFlag').hide();
        }else{
            $('.recoverOsFlag').show();
            $("#recoverOS").html(getFlagLevelInfo(data.repair_linux_flag));
        }
        $('#total-progress').css({width: data.totalprogress});
		$('#progressright').html(data.progress);

        //如果任务类型变成了瞬时恢复
        if(data.tasytype == CONF.TASK_TYPE.OS_INSTANT_RECOVERY){
            clearTimeout(timeoutID);
            if(errorflag){
                //错误
                UIToastr.showWarning(LANG.UI_OS_TASK_FAIL, LANG.UI_OS_TASK_FAIL_JUMP_TO_PAGE_TIPS);
            }else{
                //正确
                $('#total-progress').css({width: '100%'});
                $('#progressright').html('100%');
                UIToastr.showSuccess(LANG.UI_JOB_OVER_TITLE, LANG.UI_JOB_MOTION_OVER_VALUE);
            }
            setTimeout(function(){
                LOCATION('./content/os/os_instant_job_details.php?uuid=' + $('#taskuuid').val(),'task');
            }, 5000);
            return;
        }
        
    }
    //设置图片 
    var initJobImgInfo =  function(data){
        //设置服务器IP
        $('#serverip').html(data.server_ip);
        //设置主机IP
        $('#hostip').html(data.host_ip);
        //设置瞬时恢复原主机和迁移主机ip
        if(data.recoverhostname != '()'){
            $('#recoverhostname').html(data.recoverhostname);
        }
        if(data.motionhostname != '()'){
            $('#motionhostname').html(data.motionhostname);
        }
        //设置主机状态
        if(data.online_flag){
            $("#oshostabnormal").hide();
            $("#oshostnormal").show();
        }else{
            $("#oshostabnormal").show();
            $("#oshostnormal").hide();
        }
        //设置任务状态
        switch(data.task_status){
            
            case CONF.TASK_STATUS.RUNNING: //运行
            case CONF.TASK_STATUS.STOPPING://停止中
            case CONF.TASK_STATUS.PREPARING://准备中
            case CONF.TASK_STATUS.PAUSING://任务暂停中
            case CONF.TASK_STATUS.STARTING://启动中
            case CONF.TASK_STATUS.FINISHED://已完成
            case CONF.TASK_STATUS.SUCCESSED://任务成功
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
                $('#tasknormal').hide();
                $('#taskabnormal').show();
                break;
        }
    }
    //得到开启和关闭的HTML内容
	var getFlagLevelInfo = function(flag){
		var html = '<span class="label label-success">' + LANG.UI_PUBLIC_ON + '</span>';
		if(!flag){
			html = '<span class="label label-warning">' + LANG.UI_PUBLIC_OFF + '</span>';
		}
		return html;
	}
    //得到状态的显示类型
	var getStatusLevelClass = function(level){
		var levelClass = '';
		switch(level){
			case 1:
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
    //初始化日志表格
    var initLogGrid = function(){
        var updateInterval = 1500;
        logGrid = new Datatable();
        
        var init = function(){
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
                    icon = '<div class="label label-success"><i class="fa fa-check"></i></div>';
                }else if(3 == level){
                    //设置错误标志位为true
                    icon = '<div class="label label-danger"><i class="fa fa-times"></i></div>';
                    errorflag =  true;
                }else{
                    icon = '<div class="label label-warning"><i class="fa fa-exclamation"></i></div>';
                }
                return icon;
            }
            
            var getlog = function(){
                var data = {};
                data.uuid = $("#taskuuid").val();
                var jsonData = JSON.stringify(data);
                $.post(CONF.AJAXPATH, {m:CONF.M.LOG,f:'getRunningJobLog',p:jsonData}, function(d){
                    if(0 == $('#runninglog').size()){
                        clearTimeout(timerTask.OSJobDetails_logGrid);
                        return;
                    }
                    getLiInfo(d);
                })
                .complete(function() {timerTask.OSJobDetails_logGrid = setTimeout(getlog, updateInterval);});
            }
            getlog(); //得到日志
        }
        init();
    }
    var  osGridLoad =  function(){
        if(detailsInfo){
			var openTr = $('#ostable').find('tbody > tr')[detailsIndex];
			$(openTr).after(detailsInfo);
			$(openTr).find('.row-details-close').addClass("row-details-open").removeClass("row-details-close");
		}
    }
    //初始化主机列表
    var initOSGrid = function(){
        var updateInterval = 10000;
    	var initFlag = false;
    	var grid = new Datatable();
        var init = function(){
    		if(0 == $('#taskuuid').size() ){
        		clearTimeout(timerTask.OSJobDetails_osGrid);
        		return;
        	}
    		if(!initFlag){
    	    	var data = {};
    			data.uuid = $("#taskuuid").val();
    			data = {m:CONF.M.JOB,f:'getDetailsOS',p:data};
    			grid.setAjaxParam(data);
    			grid.init({src: $("#ostable"), showDetail:true, onDataLoad:osGridLoad, dataTable:{"paging":false,"info":false}});
    	    	initFlag = true;
    		}else{
    			grid.getRefresh({});
    		}
    		timerTask.OSJobDetails_osGrid = setTimeout(init, updateInterval);
    	}
        init();
        $('#ostable').on('click','tbody td .row-details',function(){
            var data =  grid.getDataTable().data();
            var nTr  = $(this).parents('tr')[0];
            if($(this).hasClass('row-details-open')){
                //如果是展开的
            	//收起所有展开项
                $(this).addClass("row-details-close").removeClass("row-details-open");
                $(this).parent().parent().next().remove();
            	detailsInfo = null;
            }else{
                //如果是收起的
                $('#os').find('tr .details').parent().remove();
            	$('#os').find('.row-details-open').addClass("row-details-close").removeClass("row-details-open");
            	$(this).addClass("row-details-open").removeClass("row-details-close");
            	var row = $(this).parent().parent().prevAll().length;
            	addDetails(nTr, data[row]);
            	detailsIndex = $(this).parents('tr')[0].rowIndex - 1;
            	detailsInfo = $('#os').find('tbody tr .details').parents('tr')[0];
            }
            return;
        });

        //添加详情信息
        var addDetails = function(nTr, data){
            if(!data){
    			return;
    		}
        	var sOut = '<tr class="details"><td class="details" colspan="12">';
        	sOut += '<table>';
            sOut += getOSDetails(data[10]);
            sOut += '</table></td></tr>';
    		$(nTr).after(sOut);
        }
        var getOSDetails =  function(data){
            if(!data) return;
            return getRecoveryOSDetails(data);
        }

        //恢复虚拟机详情
    	var getRecoveryOSDetails = function(data){
            console.log("data10",data);
    		var details = "<tr><td>" + LANG.UI_MOTION_TIMEPOINT + ":</td><td>";
    		details += data.taskname + " => " + data.timepoint;
    		details += "</td></tr>";
    		
    		details += "<tr><td>" + LANG.UI_OS_MIGRATE_ORIGINAL_HOST + ":</td>";
    		details += "<td>" + data.source_name + "</td>";
    		details += "</tr>";
    		
    		details += "<tr><td>" + LANG.UI_OS_MIGRATE_TARGET_HOST + ":</td>";
    		details += "<td>" + data.target_name + "</td>";
    		details += "</tr>";
    		
    		return details;
    	}

    }
	var initSwiper = function(){
		//先给swiper插件里面的元素加上class
		$('.swiper-detail').addClass('swiper');
		$('.swiper-detail').attr('style','overflow: hidden');
		$('.swiper-detail').find('ul.nav.nav-tabs ').addClass('swiper-wrapper');
		$('.swiper-detail').find('ul.nav.nav-tabs > li').addClass('swiper-slide widthauto');
		var mySwiper = new Swiper ('.swiper',{
			slidesPerView :'auto',
			freeMode: false,	//惯性滑动且不会贴合
            navigation: {
                nextEl: '.swiper-button-next_detail',
                prevEl: '.swiper-button-prev_detail',
				disabledClass: 'display-none',
            },
            allowTouchMove:false
		});
		var predisable = mySwiper.navigation.prevEl.ariaDisabled == 'true' ? true : false;
		var nextdisable = mySwiper.navigation.nextEl.ariaDisabled == 'true' ? true : false;
		if(predisable && nextdisable){
			$('.swiper-detail').addClass('swiper-no-swiping')
		}
	}
    return{
        init:function(){
            initSwiper();
            initBasicInfo(); //初始化基本信息
            initLogGrid();   //初始化日志表格
            initOSGrid();   //初始化主机列表
        
        }
    }

}();

jQuery(document).ready(function(){
    OSMotionJobDetails.init();
})
