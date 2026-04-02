var DataBackupCenter = function(){
    var _TASKINTERVAL = 5000;		//任务获取延迟时间	5秒钟
    var echarts3;



    //初始化监听事件
    const initListener = () =>{
        $("#backup_days").on('change',()=>{
            initData();
        })
        $("#current_task").on('click',function(){
            LOCATION('./content/platform/jobs/jobs.php', 'task', { tabId: 'currentLi' });
        })
        $("#history_task").on('click',function(){
            LOCATION('./content/platform/jobs/jobs.php', 'task', { tabId: 'historyLi' } );
        })

    }




    const initBackupChart = (info) =>{
        const initechart = () =>{
            var chartDom = document.querySelector('#backup_chart');
            echarts3 = echarts.init(chartDom);


            //初始化数据
            var data_x = [];
            var data_y_backup_data = [];
            for(let i=0;i<info.length;i++){
                data_x.push(info[i].date);
                data_y_backup_data.push(info[i].backup_data);
            }




            var option;
            option = {
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'shadow'
                    },
                    formatter: function(params) {
                        var des = params[0].name + "<br/>";
                        for(var i=0; i<params.length; i++){
                            let dataVal = Number(params[i].value) ? params[i].value : 0;
                            let unit = "B";
                            if(dataVal >= 1024*1024*1024*1024){
                                dataVal /= 1024*1024*1024*1024;
                                unit = "TB";
                            } else if(dataVal >= 1024*1024*1024){
                                dataVal /= 1024*1024*1024;
                                unit = "GB";
                            } else if(dataVal >= 1024*1024){
                                dataVal /= 1024*1024;
                                unit = "MB";
                            } else if(dataVal >= 1024){
                                dataVal /= 1024;
                                unit = "KB";
                            }
                            des += params[i].seriesName +": "+ dataVal.toFixed(2) + " " + unit + "<br/>";
                        }
                        return des;
                    }
                    
                },
                grid: {
                    left: '3%',
                    right: '4%',
                    bottom: '15%',
                    top:'10%',
                    containLabel: true
                },
                xAxis: [
                    {
                        type: 'category',
                        data: data_x,
                        axisTick: {
                            alignWithLabel: true
                        },
                        axisLine: {
                            show: false // 隐藏 x 轴线条
                        }
                    }
                ],
                yAxis: [
                    {
                        type: 'value',
                        splitLine: {
                            lineStyle: {
                                type: 'dashed' // 这里将刻度线设置为虚线
                            }
                        },
                        axisLabel: {
                            formatter: function(value, index){
                                value = Number(value);
                                let unit = "B";
                                if(value >= 1024*1024*1024*1024){
                                    value /= 1024*1024*1024*1024;
                                    unit = "TB";
                                } else if(value >= 1024*1024*1024){
                                    value /= 1024*1024*1024;
                                    unit = "GB";
                                } else if(value >= 1024*1024){
                                    value /= 1024*1024;
                                    unit = "MB";
                                } else if(value >= 1024){
                                    value /= 1024;
                                    unit = "KB";
                                }
                                value = value.toFixed(2);
                                return value + unit;
                            },
                        }
                    }
                ],
                series: [
                    {
                        name: LANG.UI_HOMEPAGEPRO_BACKUP_DATA,
                        type: 'bar',
                        barWidth: '25%',
                        itemStyle: {
                            color: '#2CCE8B',
                            borderRadius: 50 // 设置柱状图的圆角
                        },
                        data: data_y_backup_data,
                    },

                ],
                legend: {
                    bottom: "5%", // 将图例放在最下面
                    icon: 'circle',
                    padding: [0, 0, 0, 20] // 设置图例文字左边的间距
                }
            };
            echarts3.setOption(option);
        }
        //初始化echarts
        initechart();
        window.onresize = function(event) {
            echarts3.resize();
        }
      
    }

    const initDataProtect = (info) =>{
        const getModuleName = (name) =>{
            let eachModule = {};
            eachModule.name = LANG.UI_PUBLIC_UNKNOWN;
            eachModule.icon = "";
            switch(name){
                case "db":
                    eachModule.name = LANG.UI_VISUAL_MODULE_DB;
                    eachModule.icon = "vicon-shujuku";
                    break;
                case "fs":
                    eachModule.name = LANG.UI_DATA_FILE_FILE;
                    eachModule.icon = "vicon-ge_backup_file";
                    break;
                case "m365":
                    eachModule.name = LANG.UI_TENANT_M365_USER;
                    eachModule.icon = "vicon-windows-view";
                    break;
                case "nas":
                    eachModule.name = LANG.UI_NAS_BAK_DETAIL_HIS;
                    eachModule.icon = "vicon-nasmanager";
                    break;
                case "os":
                    eachModule.name = LANG.UI_BACKUP_DATA_MODULE_OS;
                    eachModule.icon = "vicon-os_protect";
                    break;
                case "vm":
                    eachModule.name = LANG.UI_BACKUP_DATA_MODULE_VM;
                    eachModule.icon = "vicon-xuniji";
                    break;
                case "hadoop":
                    eachModule.name = LANG.UI_VISUAL_HADOOP_BACKUP;
                    eachModule.icon = "vicon-hadoop";
                    break;
                case "obs":
                    eachModule.name = LANG.UI_PLATFORM_DES_OBS;
                    eachModule.icon = "vicon-obs";
                    break;
                case "private_cloud":
                    eachModule.name = LANG.UI_PUBLIC_PRIVATE_CLOUD;
                    eachModule.icon = "vicon-overview-private-cloud";
                    break;
                case "public_cloud":
                    eachModule.name = LANG.UI_PUBLIC_PUBLIC_CLOUD;
                    eachModule.icon = "vicon-overview-plubic-cloud";
                    break;
                case "kubernetes":
                    eachModule.name = 'Kubernetes';
                    eachModule.icon = "vicon-overciew-k8s";
                    break;
                case "os_copy":
                    eachModule.name = LANG.UI_LICENSE_WHOLE_MACHINE_COPY;
                    eachModule.icon = "vicon-overview-complete-machine";
                    break;
                case "fs_copy":
                    eachModule.name = LANG.UI_FILE_COPY_DES;
                    eachModule.icon = "vicon-fuzhiliebiao";
                    break;
                case "nas_copy":
                    eachModule.name = LANG.UI_LICENSE_NAS_COPY;
                    eachModule.icon = "vicon-overview-nas";
                    break;
                case "db_copy":
                    eachModule.name = LANG.UI_BACKUP_DATA_MODULE_DB_REAL;
                    eachModule.icon = "vicon-tongbu";
                    break;
                case "exchange_online":
                    eachModule.name = LANG.UI_TENANT_M365_USER2;
                    eachModule.icon = "vicon-windows-view";
                    break;
            }
            return eachModule;

        }




        var htmlStr = '';
        //权限有无
        for(let key in info){
            if (info.hasOwnProperty(key)) {  
                //得到每个模块的数据
                let moduleInfo =  info[key];
                if(!moduleInfo.flag) continue;

                htmlStr +='<div class="item">'+
                '<div>'+getModuleName(key)['name']+'<i class="viconfont '+getModuleName(key)['icon']+'"></i></div>'+
                '<div><span>'+moduleInfo.backup_size_int+'</span><span>'+moduleInfo.bakcup_size_unit+'</span></div>'+
                '<div>'+ LANG.UI_TENANT_USED_TOTAL +'</div>'+
                '<div><span>'+moduleInfo.auth_used+'</span><span>/</span><span>'+moduleInfo.auth_total+'</span></div>'+
                '</div>'
            }  
        }
        $(".tenant_data_center_container").html(htmlStr);
    }



    const initInfo = (data) =>{
        //初始化页面数据
        //初始化基本数据
        $("#tenant_all_backup_size").text(data.backup_total_size);
        $("#tenant_all_backup_size_unit").text(data.backup_total_size_unit);
        $("#tenant_current_job_num").text(data.current_task);
        $("#tenant_history_job_num").text(data.history_task);
        //初始化数据保护
        initDataProtect(data.module_info);
        //初始化备份数据
        initBackupChart(data.backup_chart);

    }

    
    //初始化数据
    const initData = () =>{
        var dataList = {};
        dataList.backup_days = $("#backup_days").val();
        const requestSyncThis = (value) =>{
           
            if(value.success){
                initInfo(value.data);
            }else{
                //如果获取失败则直接给出提示
                toastr.info(LANG.UI_TENANT_INIT_HOMEPAGE, value.message);
            }
        }
        //获取所有信息后发送后端
        pAjaxRequest(dataList, "/api/v1/tenant/homepage_info", "get", requestSyncThis,true);

        

    }
	




    
  









































    //--------------------以下为首页公有-------------------------------------------------
     //初始化告警
     var initAlarmTips = function(){
        var getAlarmInfo = function(){
            $.post(CONF.AJAXPATH, {m:CONF.M.ALARM,f:'getSurveyNoticeInfo',p:{}}, function(d){
                setAlarmInfo(d);
            })
                .complete(function() {
                    setTimeout(getAlarmInfo, _TASKINTERVAL);});
        }
        getAlarmInfo();

    }
    //更新告警信息
    var updateAlarmTips = function(){
        $.post(CONF.AJAXPATH, {m:CONF.M.ALARM,f:'getSurveyNoticeInfo',p:{}}, function(d){
            setAlarmInfo(d);
        });
    }
    /**
     * 设置顶部告警ICON的样式
     * 无告警:	label-info
     * 有警告告警:	label-warning
     * 有错误告警:	label-danger
     */
    var setAlarmLabelIconClour = function(id, data){
        var clourSytel = "label-info";
        if(data.warn > 0){
            clourSytel = "label-warning";
        }
        if(data.error > 0){
            clourSytel = "label-danger";
        }
        $(id).find('.label-icon').removeClass().addClass("label label-sm label-icon " + clourSytel);
    }
    //设置告警信息
    var setAlarmInfo = function(d){
        var d = JSON.parse(d);
        $('.badgemark').remove();
        //初始化告警提示
        $('#alarmtask').html(d.alarm.task.error + d.alarm.task.warn);
        $('#alarmsystem').html(d.alarm.system.error + d.alarm.system.warn);

        var total = d.alarm.task.error + d.alarm.task.warn + d.alarm.system.error + d.alarm.system.warn;
        var errorTotal = d.alarm.task.error + d.alarm.system.error;
        var badgeType = "badge-warning";
        var tips = "";
        if(errorTotal > 0){
            badgeType = "badge-danger";
        }
        if(total > 0){
            var tips = '<span class="badge ' + badgeType + ' badgemark">' + total + '</span>';
        }
        $('#alarmtotal').find('span').remove();
        $('#alarmtotal').append(tips);

        setAlarmLabelIconClour(".alarmhreftask", d.alarm.task);
        setAlarmLabelIconClour(".alarmhrefsystem", d.alarm.system);


        //初始化任务提示
        $('#topcurrenttask').html(d.task.current);
        $('#tophistorytask').html(d.task.history);

        //初始化菜单提示
        var storage_manager = $('.page-sidebar-menu').find('a[name=storage_manager]');
        var authorization_module = $('.page-sidebar-menu').find('a[name=authorization_module]');
        var resource_manager = $('.page-sidebar-menu').find('a[name=resmanagement]');
        var system_manager = $('.page-sidebar-menu').find('a[name=sysmanagement]');

        if(d.storage){
            //管理备份存储
            var tips = '<span class="badge badge-warning badgemark">' + d.storage + '</span>';
            storage_manager.append(tips);
            //父级-系统管理
            resource_manager.append(tips);
        }
        if(d.lisence){
            //系统授权
            var tips = '<span class="badge badge-danger badgemark">' + d.lisence + '</span>';
            authorization_module.append(tips);
            //父级-系统管理
            system_manager.append(tips);
        }

    }
    /**
     * 设置顶部告警ICON的样式
     * 无告警:	label-info
     * 有警告告警:	label-warning
     * 有错误告警:	label-danger
     */
    var setAlarmLabelIconClour = function(id, data){
        var clourSytel = "label-info";
        if(data.warn > 0){
            clourSytel = "label-warning";
        }
        if(data.error > 0){
            clourSytel = "label-danger";
        }
        $(id).find('.label-icon').removeClass().addClass("label label-sm label-icon " + clourSytel);
    }
    //密码即将过期提示
    var initLoginHistory = function(){
        $.post(CONF.AJAXPATH, {m:CONF.M.USER,f:'getLoginHistory'}, function(d){
            var data = JSON.parse(d);
            if(data.tips !=""){
                UIToastr.showWarning(LANG.UI_TENANT_HOME_PASSWORD_EXPIRE, data.tips);
            }
        });
    }
    //初始化是否为租户内部,并设置任务窗口事件
    var initUserType = function(){
        $.post(CONF.AJAXPATH, {m:CONF.M.USER,f:'getUserExtendInfo',p:{}}, function(d){
            var data = JSON.parse(d);
            if(data.tenantuuid == "" && data.useruuid == "a508b813-19c7-eb4e-d6fa-bb61b25a4de9"){
                addManagerListener();
            }else if(data.tenantuuid == ""){
                addOperatorListener();
            }
        });

        $('.taskhrefcurrent').off().on('click', function(){
            LOCATION('./content/platform/jobs/jobs.php', 'task');
        });
        $('.taskhrefhistory').off().on('click', function(){
            LOCATION('./content/platform/jobs/jobs.php?tab=1', 'task');
        });
        $('.alarmhreftask').off().on('click', function(){
            LOCATION('./content/platform/alarm/alarm.php', 'alarm');
        });
        $('.alarmhrefsystem').off().on('click', function(){
            LOCATION('./content/platform/alarm/alarm.php?tab=1', 'alarm', { tabId: 'system_alarm' });
        });

    }
    //添加管理员事件
    var addManagerListener = function(){
        $('#moretaskinfo').on('click', function(){
            CTLHORMENU('bakandrec');
            var url = './content/platform/jobs/history_job.php';
            var urlMark = '?history_job';
            if($('#currenttaskli').hasClass("active")){
                url = './content/platform/jobs/jobs.php';
                urlMark = '?current_job';
            }
            LOCATION(url);
            History.pushState({url:url}, "", urlMark);
        });
        $('.currentjob').off().on('click', function(){
            CTLHORMENU('bakandrec');
        })
    }

    //添加操作员事件
    var addOperatorListener = function(){
        $('#moretaskinfo').on('click', function(){
            CTLHORMENU('bakandrec');
            var url = './content/platform/jobs/history_job.php';
            var urlMark = '?history_job';
            if($('#currenttaskli').hasClass("active")){
                url = './content/platform/jobs/jobs.php';
                urlMark = '?current_job';
            }
            LOCATION(url);
            History.pushState({url:url}, "", urlMark);
        });
        $('.currentjob').off().on('click', function(){
            CTLHORMENU('bakandrec');
        })
    }

   
	
	return {
		init: function(){
            initData();
           initListener();


            //----以下为每个首页都有的-------
            initUserType();
			initLoginHistory();		
            //初始化告警
        	initAlarmTips();
            const title = document.title;
            History.pushState({url:"./content/platform/tenant_center.php"}, title, "?homepage");
            requestAnimationFrame(() => {
                document.title = title;
            });
            //定义更新告警信息接口,添加虚拟化中心成功,添加存储成功后调用
		},
		updateTopAlarmTips: function(){
        	updateAlarmTips();
        }
	}
	
}();

jQuery(document).ready(function(){
	DataBackupCenter.init();
});