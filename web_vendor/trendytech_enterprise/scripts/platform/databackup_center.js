var DataBackupCenter =  function(){
    var networkLineChart,cpuChart,memoryChart,consumpChart,capacityCircle;
    var initNetworkFlag = true;
	  var initCPUFlag = true;
    var initMemoryFlag =  true;
    var _TASKINTERVAL = 5000;		//任务获取延迟时间	5秒钟
    var timerTask = {};
    var timedata = [];
    var netOutdata = [];
    var netIndata = [];
    var cpuUsagedata = [];
    var cputimedata = [];
    var memoryUsagedata = [];
    var memorytimedata = [];
    var showTime;//系统时间
    var initListener = function(){
        $("#node_uuid").on('change',function(){
          initNetworkFlag = true;
          initCPUFlag = true;
          initMemoryFlag =  true;
          getSystemData(); //获取系统数据
		    });
        $("#backupdetail").on('click',function(){
          LOCATION('./content/platform/storage/storage_manager.php', 'storage_manager');
        });
        //当前任务点击跳转
        $("#currenttask").on('click',function(){
          LOCATION('./content/platform/jobs/jobs.php', 'task');
        });
        $("#historytask").on('click',function(){
          LOCATION('./content/platform/jobs/jobs.php?tab=1', 'task');
        });
        //历史任务点击跳转
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
	    	LOCATION('./content/platform/alarm/alarm.php?tab=1', 'alarm');
    	});
        
    }
    //初始化节点信息
    var initNodeUUID = function(){
      $.post(CONF.AJAXPATH, {m:CONF.M.SYSTEMMONITOR, f:'getNodeUUid', p:{}}, function(d){
        var data = JSON.parse(d);
        var nodeselect = $('#node_uuid');
        nodeselect.empty();
        for(var i=0; i<data.length; i++){
          var option = $("<option>").text(data[i].name).val(data[i].node_uuid);
          nodeselect.append(option);
        }
        getSystemData();
      });
    }
    //更新告警信息
    var updateAlarmTips = function(){
      $.post(CONF.AJAXPATH, {m:CONF.M.ALARM,f:'getSurveyNoticeInfo',p:{}}, function(d){
        setAlarmInfo(d);
      });
    }
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
    //设置系统备份存储echart
    var setSystemStorageData =  function(data){
        var sum = data.storageInfo.bakStorageCapacity.size;
            if(sum == 0) {
                var usedPercent = 0 + "%";
                var remainPercent = 0 + "%";
            }else {
                var usedPercent = Math.round(data.storageInfo.usedCapacity.size / (sum) * 10000) / 100 + "%";
                var remainPercent = Math.round(data.storageInfo.remainingCapacity.size / (sum) * 10000) / 100 + "%";
            }
            $(".usedPercent").html(usedPercent);
            $(".usedNum").html(data.storageInfo.usedCapacity.des);
            $(".remainPercent").html(remainPercent);
            $(".remianNum").html(data.storageInfo.remainingCapacity.des);
            initStorageChart(data);
    }
    var initStorageChart =  function(data){
        var usedCapacity = data.storageInfo.usedCapacity.size;
        var bakStorageCapacity = data.storageInfo.bakStorageCapacity.size;
        var bakdes = data.storageInfo.bakStorageCapacity.des;
        //存储饼图
        var chartDom = document.getElementById('capacityCircle');
        capacityCircle = echarts.init(chartDom);
        var option;
        option = {};
          //如果内容为空 则显示暂无数据
          if(usedCapacity == 0){
            option = {
                title: {
                        text: LANG.UI_SYSTEM_MONITOR_NULL_DATA,
                        x: "center",
                        y: "center",
                        textStyle: {
                          color: "black",
                          fontWeight: "normal",
                          fontSize: 16,
                        },
                      },
            }
            if(capacityCircle != undefined){
              capacityCircle.clear(); //清空实例 否则会与上一个合并 数据残留
            }
          }else{
            delete option['title'];
            option['series'] = [              
              {
              type: 'gauge',
              radius:'100%',
              center:['50%','50%'],
              startAngle: 180,
              endAngle: 0,
              pointer:{ 
                show:false
              },
              min:0,
              max:bakStorageCapacity,
              splitNumber:0, //仪表盘刻度的分隔段数,
              axisTick:{ //刻度样式
                show:false  //不显示刻度
              },
              axisLabel:{   //刻度标签
                show:false //不显示标签
              },
              splitLine:{ //分割线
                show:false //不展示分割线 用于隐藏开头第一根线
              },
              axisLine:{ //仪表盘轴线的相关配置
                roundCap:true, //在两端显示成圆形
                lineStyle:{
                  width:23
                }
              },
              progress:{ //展示当前进度
                show:true,
                width:23,
                roundCap:true,
                itemStyle:{
                  color:"#6194FD"
                }

              },
              detail:{ //仪表盘详情，用于显示数据
                offsetCenter:[0,'-25%'],
                formatter:function(value){
                   return  '{name|总容量}'+'\n'+'{des|'+bakdes +'}';
                },
                rich:{
                  name:{
                    color:"#777790"
                  },
                  des:{
                    color:"#3F4254",
                    fontSize:26,
                    fontWeight:"bold"
                  }
                },
              },
              data:[
                {
                  value:usedCapacity,
                },
              ]
            }];
          }
          capacityCircle.setOption(option);
    }
    var setCpuMemoryData =  function(data){
        setCpuData(data);
        setMemoryData(data);
    }
    //设置CPU使用率echart
    var setCpuData =  function(data){
      if(!initCPUFlag) {
        cputimedata.shift();
        cpuUsagedata.shift();
        cputimedata.push(data[0].time);
        cpuUsagedata.push(data[0].cpumemorydata.cpuUsedRate);
      }else {
        initCPUFlag = false;
        cputimedata = [];
        cpuUsagedata = [];
        for(var i = 0; i < data.length; i++) {
          cputimedata.push(data[i].time);
          cpuUsagedata.push(data[i].cpumemorydata.cpuUsedRate);
        }
      }
        //CPU使用率折线
        cpuChart = echarts.init(document.getElementById('cpuChart'));
        var option = {
          title: {
            text: "CPU使用率",
            textStyle: { // 标题样式
                color: "#464E5F",
                fontSize: "14",
                fontWeight:'bold',
              },
            
          },
          tooltip: {
            trigger: "axis",
            icon: "circle", 
            axisPointer: {
                icon: "circle", 
                lineStyle: {
                    color: "#5F93FA"
                }
            },
            formatter: function (params) {
                var relVal = params[0].name
                for (var i = 0, l = params.length; i < l; i++) {
                    var pvalue = params[i].value;
                    relVal += '<div style="background: rgba(255,255,255,0.8);height:20px;line-height:20px;font-size:12px;margin-top:4px">'+ 'CPU使用率<span style="margin-left:6px;font-weight:bold;color:'+params[i].color+';">'+ pvalue+'%</span></div>';
                }
                return relVal;
              }
          },
          grid: {
            left: "10",
            top: "40",
            right: "28",
            bottom: "10",
            containLabel: true,
          },
          xAxis: [
            {
              type: "category",
              boundaryGap: false,
              axisLabel: {
                textStyle: {
                  color: "#86909C",
                  fontSize: 12
                }
              },
              axisLine: {
                lineStyle: {
                  color: "rgba(255,255,255,.2)"
                },
              },
              data: cputimedata,
              splitLine: {//分割线
                show: true,
                lineStyle: {
                    color: "rgba(229, 232, 239, 0.40)"
                  }
              }
            },
            {
              axisPointer: { show: false },
              axisLine: { show: false },
              position: "bottom",
              offset: 20
            },
          ],
          yAxis: [
            {
                type: "value",
                //最小值
                min:0,
                //最大值
                max:100,
                axisTick: { show: false },//y轴刻度
                axisLine: {
                    lineStyle: {
                        color: "#E6E9F4"
                    }
                },
                axisLabel: {
                textStyle: {
                    color: "#7C7D8B",
                    fontSize: 12
                },
                formatter: function(value, index){
                    return Math.round(value) + "%";
                }
                },
                splitLine: {//不显示分割线
                    show: false,
                }
            }
          ],
          series: [
            {
              name: "",
              type: "line",
              smooth: true,
              symbol: "circle",
              symbolSize: 5,
              showSymbol: false,
              lineStyle: {
                normal: {
                color: "#5F93FA",
                width: 2
                }
              },
              areaStyle: {
                normal: {
                    color: new echarts.graphic.LinearGradient(0,0,0,1,
                      [
                        {
                          offset: 0,
                          // color: "#02e3a33c"
                          color:"rgba(17,126,255,0.16)"
                        },
                        {
                          offset: 1,
                          color: "rgba(255, 255, 255, 0.3)"
                        }
                      ],
                      false
                    ),
                    shadowColor: "rgba(0, 0, 0, 0.1)"
                  }
              },
              itemStyle: {
                normal: {
                  color: "#5F93FA",
                  borderColor: "rgba(0,178,255, .1)",
                  borderWidth: 12
                }
              },
              data: cpuUsagedata,
            }
          ]
        };
        if(cpuUsagedata.length == 0){
          option = {
              title: {
                      text: LANG.UI_SYSTEM_MONITOR_NULL_DATA,
                      x: "center",
                      y: "center",
                      textStyle: {
                        color: "black",
                        fontWeight: "normal",
                        fontSize: 16,
                      },
                    },
          }
          if(cpuChart != undefined){
            cpuChart.clear(); //清空实例 否则会与上一个合并 数据残留
          }
        }else{
          delete option['title'];
          option['title'] = {
            text: "CPU使用率",
            textStyle: { // 标题样式
                color: "#464E5F",
                fontSize: "14",
                fontWeight:'bold',
              },
            
          }
        };
      cpuChart.setOption(option);
    }
    //设置内存使用率echart
    var setMemoryData =  function(data){
      if(!initMemoryFlag) {
        memorytimedata.shift();
        memoryUsagedata.shift();
        memorytimedata.push(data[0].time);
        memoryUsagedata.push(data[0].cpumemorydata.memoryUsedRate);
      }else {
        initMemoryFlag = false;
        memorytimedata = [];
        memoryUsedRate = [];
        for(var i = 0; i < data.length; i++) {
          memorytimedata.push(data[i].time);
          memoryUsagedata.push(data[i].cpumemorydata.memoryUsedRate);
        }
      }
		//内存使用率折线
      memoryChart = echarts.init(document.getElementById('memeryChart'));
      var option = {
        title: {
          text: "内存使用率",
          textStyle: { // 标题样式
              color: "#464E5F",
              fontSize: "14",
              fontWeight:'bold',
            },
        },
        tooltip: {
          trigger: "axis",
          icon: "circle", 
          axisPointer: {
              icon: "circle", 
              lineStyle: {
              color: "#6DBB73"
            }
          },
          formatter: function (params) {
              var relVal = params[0].name
              for (var i = 0, l = params.length; i < l; i++) {
                  var pvalue = params[i].value;
                  relVal += '<div style="background: rgba(255,255,255,0.8);height:20px;line-height:20px;font-size:12px;margin-top:4px">'+ '内存使用率<span style="margin-left:6px;font-weight:bold;color:'+params[i].color+';">'+ pvalue+'%</span></div>';
              }
              return relVal;
            }
        },
        grid: {
          left: "10",
          top: "40",
          right: "28",
          bottom: "10",
          containLabel: true,
        },
        xAxis: [
          {
            type: "category",
            boundaryGap: false,
            axisLabel: {
              textStyle: {
                color: "#86909C",
                fontSize: 12
              }
            },
            axisLine: {
              lineStyle: {
                color: "rgba(255,255,255,.2)"
              },
            },
            data: memorytimedata,
            splitLine: {//分割线
              show: true,
              lineStyle: {
                  color: "rgba(229,232,239,0.4)"
                }
            }
          },
          {
            axisPointer: { show: false },
            axisLine: { show: false },
            position: "bottom",
            offset: 20
          },
        ],
        yAxis: [
          {
              type: "value",
              min:0,
              max:100,
              axisTick: { show: false },//y轴刻度
                axisLine: {
                    lineStyle: {
                        color: "#E6E9F4"
                    }
                },
                axisLabel: {
                  textStyle: {
                    color: "#7C7D8B",
                    fontSize: 12
                  },
                  formatter: function(value, index){
                    return Math.round(value) + "%";
                  }
                },
              splitLine: {//不显示分割线
                  show: false,
              }
          }
        ],
        series: [
          {
            name: "",
            type: "line",
            smooth: true,
            symbol: "circle",
            symbolSize: 5,
            showSymbol: false,
            lineStyle: {
              normal: {
                color: "#6DBB73",
                width: 2
              }
            },
            areaStyle: {
              normal: {
                  color: new echarts.graphic.LinearGradient(0,0,0,1,
                    [
                      {
                        offset: 0,
                        color: "rgba(109, 187, 115, 0.3)"
                      },
                      {
                        offset: 1,
                        color: "rgba(255,255,255,0)"
                      }
                    ],
                    false
                  ),
                }
            },
            itemStyle: {
              normal: {
                color: "#6DBB73",
                borderColor: "rgba(0,178,255, .1)",
                borderWidth: 12
              }
            },
            data: memoryUsagedata,
          }
        ]
      };
      if(memoryUsagedata.length == 0){
        option = {
            title: {
                    text: LANG.UI_SYSTEM_MONITOR_NULL_DATA,
                    x: "center",
                    y: "center",
                    textStyle: {
                      color: "black",
                      fontWeight: "normal",
                      fontSize: 16,
                    },
                  },
        }
        if(memoryChart != undefined){
          memoryChart.clear(); //清空实例 否则会与上一个合并 数据残留
        }
      }else{
        delete option['title'];
        option['title'] =  {
          text: "内存使用率",
          textStyle: { // 标题样式
              color: "#464E5F",
              fontSize: "14",
              fontWeight:'bold',
            },
        }
      };
      memoryChart.setOption(option);
    }

    //设置网络流量echart
    var setNetworkData =  function(data){
      if(!initNetworkFlag) {
        timedata.shift();
        netOutdata.shift();
        netIndata.shift();
        timedata.push(data[0].time);
        netOutdata.push(data[0].network.transmit);
        netIndata.push(data[0].network.receive);
			
      }else {
        initNetworkFlag = false;
        timedata = [];
        netOutdata = [];
        netIndata = [];
        for(var i = 0; i < data.length; i++) {
          timedata.push(data[i].time);
          netOutdata.push(data[i].network.transmit);
          netIndata.push(data[i].network.receive);
        }
      }
		  //网络流量折线
		 networkLineChart = echarts.init(document.getElementById('networkTrafficChart'));
      var option = {
        title: {
          text: "网络流量",
          textStyle: { // 标题样式
            color: "#464E5F",
            fontSize: "14",
            fontWeight:'bold',
            },
          
        },
        tooltip: {
          trigger: "axis",
          icon: "circle", 
          axisPointer: {
            icon: "circle", 
            lineStyle: {
            color: "#6DBB73"
            }
          },
          formatter: function (params) {
            var relVal = params[0].name
            for (var i = 0, l = params.length; i < l; i++) {
              var pvalue = params[i].value;
              if(pvalue >= 1024){
                pvalue =  Math.round(pvalue * 10 / 1024) / 10 + "MB/s";
              }else{
                pvalue = pvalue + "KB/s";
              }
              relVal += '<div style="background: rgba(255,255,255,0.8);height:20px;line-height:20px;font-size:12px;">'+ '<span style="display:inline-block;margin-right:2px;width:8px;height:8px;border-radius: 50%!important;background-color:'+ params[i].color +';"></span>' + '<span style="color:#4E5969;margin-right:14px">'+params[i].seriesName +'</span>'+'<span style = "font-weight:bold;color:'+ params[i].color+';">'+ pvalue+'</span>'+'</div>';
            }
            return relVal;
            }
        },
        legend: {
          icon: "circle",//形状  类型包括 circle，rect,line，roundRect，triangle，diamond，pin，arrow，none
          top: "0%",
          right: "0%",
          itemWidth: 10,  // 设置宽度
              itemHeight: 8, // 设置高度
              itemGap: 20, // 设置图例间距
          textStyle: {
            color: "#4E5969",
            fontSize: "12"
          },
        },
        grid: {
          left: "10",
          top: "40",
          right: "22",
          bottom: "10",
          containLabel: true,
        },
        xAxis: [
        {
          type: "category",
          boundaryGap: false,
          axisLabel: {
          textStyle: {
            color: "#86909C",
            fontSize: 12
          }
          },
          axisLine: {
          lineStyle: {
            color: "rgba(255,255,255,.2)"
          },
          },
          data: timedata,
                splitLine: {//分割线
                  show: true,
                  lineStyle: {
                      color: "rgba(229, 232, 239, 0.40)"
                    }
                }
        },
        {
          axisPointer: { show: false },
          axisLine: { show: false },
          position: "bottom",
          offset: 20
        },
        ],
        yAxis: [
        {
          type: "value",
          splitNumber: 3,
          axisTick: { show: false },//y轴刻度
          axisLine: {
          lineStyle: {
            color: "#E6E9F4"
          }
          },
          axisLabel: {
            textStyle: {
              color: "#86909C",
              fontSize: 12
            },
            formatter: function(value, index){
              if(value >= 1024){
                return Math.round(value * 10 / 1024) / 10 + "MB/s";
              }else{
                return value + "KB/s";
              }
            }
            },
          splitLine: {//不显示分割线
            show: false,
          }
        }
        ],
        series: [
          {
            name: "流入",
            type: "line",
            smooth: true,
            symbol: "circle",
            symbolSize: 5,
            showSymbol: false,
            lineStyle: {
            normal: {
              color: "#6DBB73",
              width: 2
            }
            },
            areaStyle: {
                    normal: {
                        color: new echarts.graphic.LinearGradient(0,0,0,1,
                          [
                              {
                                offset: 0,
                                color: "rgba(109, 187, 115, 0.3)"
                              },
                              {
                                offset: 1,
                                color: "rgba(255,255,255,0)"
                              }
                          ],
                          false
                        ),
                        shadowColor: "rgba(0, 0, 0, 0.1)"
                      }
            },
            itemStyle: {
            normal: {
              color: "#6DBB73",
              borderColor: "rgba(0,178,255, .1)",
              borderWidth: 12
            }
            },
            data: netIndata,
          },
          {
            name: "流出",
            type: "line",
            smooth: true,
            symbol: "circle",
            symbolSize: 5,
            showSymbol: false,
            lineStyle: {
            normal: {
              color: "#6194FD",
              width: 2
            }
            },
            areaStyle: {
                    normal: {
                        color: new echarts.graphic.LinearGradient(0,0,0,1,
                          [
                            {
                              offset: 0,
                              color:"rgba(17,126,255,0.16)"
                            },
                            {
                              offset: 1,
                              color: "rgba(255, 255, 255, 0.3)"
                            }
                          ],
                          false
                        ),
                        shadowColor: "rgba(0, 0, 0, 0.1)"
                      }
            },
            itemStyle: {
              normal: {
                color: "#6194FD",
                borderColor: "rgba(112,66,251, .1)",
                borderWidth: 12
              }
            },
            data: netOutdata,
          }
        ]
      };
      if(netIndata.length == 0 && netOutdata.length == 0){
        option = {
            title: {
                    text: LANG.UI_SYSTEM_MONITOR_NULL_DATA,
                    x: "center",
                    y: "center",
                    textStyle: {
                      color: "black",
                      fontWeight: "normal",
                      fontSize: 16,
                    },
                  },
        }
        if(networkLineChart != undefined){
          networkLineChart.clear(); //清空实例 否则会与上一个合并 数据残留
        }
      }else{
        delete option['title'];
        option['title'] = {
          text: "网络流量",
          textStyle: { // 标题样式
            color: "#464E5F",
            fontSize: "14",
            fontWeight:'bold',
            },
        }
      };
      networkLineChart.setOption(option);
    }
    //设置最近七日用量统计数据
    var setSevenDayConsumpData =  function(data){
        var dateArr = [];
        var backup = []; //备份
        var backup_point = [];
        for(var i = 0;i<data.list.length;i++){
            dateArr.push(data.list[i].date);
            backup.push({value:data.list[i].total_data,unit:data.list[i].total_data_des_unit,des:data.list[i].total_data_des});
            backup_point.push(data.list[i].total_data);
        }
        //初始化echart图
        consumpChart = echarts.init(document.getElementById('consumpChart'));
        var option = {
            color: ["#6DBB73"],
            legend: {
                icon: "circle",
                show:true,
                left:'46%',
                bottom: 60,
                itemWidth: 8,
                itemHeight:8,
                textStyle: {
                color: "#4E5969",
                fontSize: "12",
                }
            },
            grid: {
                left: "33",
                top: "57",
                right: "32",
                bottom: "106",
                containLabel: true
            },
            tooltip: {
                trigger: "axis",
                icon: "circle", 
                formatter: function (params) {
                    var relVal = params[0].name
                    for (var i = 0, l = params.length; i < l; i++) {
                        relVal += '<div style="background: rgba(255,255,255,0.8);height:36px;line-height:36px;font-size:12px;border-top:1px solid #E4E6EF;">'+ '<span style="color:#4E5969;margin-right:43px">'+'当日用量' +'</span>'+'<span style="font-weight:bold;color:#6DBB73">'+ params[i].data.des+params[i].data.unit+'</span></div>';
                    }
                    return relVal;
                },
                showDelay: 0, // 显示延迟，添加显示延迟可以避免频繁切换，单位ms
                axisPointer: {
                  type: 'shadow',
                  shadowStyle: {
                    width: '1',
                    color:{
                        type: 'linear',
                        x: 0,
                        y: 0,
                        x2: 0,
                        y2: 1,
                        colorStops: [{
                            offset: 0,color: 'rgba(109,187,115,0)'  // 0% 处的颜色
                        }, {
                            offset: 1,color: 'rgba(109,187,115,0.2)'// 100% 处的颜色
                        }],
                    }
                  }
                }
            },
            xAxis: [
            {
                type: "category",
                data: dateArr,
                axisTick: {
                alignWithLabel: true
                },
                axisLabel: {
                textStyle: {
                    color: "#86909C",
                    fontSize: "12"
                }
                },
                axisLine: {
                show: false
                }
            }
            ],
            yAxis: [
                {
                type: "value",
                splitNumber: 5, // 横线数
                axisLabel: {
                    textStyle: {
                    color: "#4E5969",
                    fontSize: "12"
                    },
                    formatter: function(value, index){
                    var arrayUnit = ["B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB", "BB", "NB", "DB"];
                    var j = 0 ;
                    while(value >= 1024 ){
                        value = value / 1024;
                        j++;
                    }
                    if(j >= 1){
                        return value.toFixed(1) + arrayUnit[j];
                    }else{
                        return value + arrayUnit[j];
                    }
                    }
                },
                axisLine: {
                    lineStyle: {
                    show:false
                    }
                },
                splitLine: {
                    lineStyle: {
                    color: "#F2F3F5"
                    }
                }
                }
            ],
            series: [
                {
                name: "最近7日用量统计",
                type: "bar",
                barWidth: "15",
                data: backup,
                itemStyle: {
                    barBorderRadius: [15,15,0,0]
                }
                }
            ]
        };
        if(backup.length == 0){
          option = {
              title: {
                      text: LANG.UI_SYSTEM_MONITOR_NULL_DATA,
                      x: "center",
                      y: "center",
                      textStyle: {
                        color: "black",
                        fontWeight: "normal",
                        fontSize: 16,
                      },
                    },
          }
          if(consumpChart != undefined){
            consumpChart.clear(); //清空实例 否则会与上一个合并 数据残留
          }
        }else{
          delete option['title'];
        };
        consumpChart.setOption(option);
    }
    //设置系统概览数据
    var setSystemDataCenter = function(data){
        // 设置系统运行时间
        $("#year").html(data.systemRunningTimeInfo.runningDay.year);
        $("#day").html(data.systemRunningTimeInfo.runningDay.day);
        $("#hour").html(data.systemRunningTimeInfo.runningDay.hour);
        if(data.systemRunningTimeInfo.runningDay.year !=  null){
          $(".yearspan").show();
        }
        else{
          $(".yearspan").hide();
        }
        //设置累计保护数据
        $("#protectDataAll").html(data.accumulateData.value+data.accumulateData.unit);
   
        //设置当前任务数
        $("#currenttaskNum").html(data.currentTaskNum);
        //设置历史任务数
        $("#historytaskNum").html(data.historyTaskNum);
    }
    //设置当前任务
    var setCurrentTask =  function(currentdata){
      //设置不同状态对应的数值
      var successNum = 0;
      var otherNum = 0;
      var waitNum = 0;
      // 0:"未知",         		//未知的任务状态
      //   1:"等待",       		//任务等待运行
      //   2:"运行",         		//任务正在运行
      //   3:"暂停",          		//任务暂停
      //   4:"停止",         		//任务停止
      //   5:"停止中",        		//任务停止中
      //   6:"网络错误",
      //   7:"异常",
      //   8:"错误",
      //   9:"同步",
      //   10:"准备中", 
      //   11:"暂停中",
      //   12:"启动中",
      //   13:"完成",
      //   14:"接管中",
      //   15:"接管启动中",
      //   16:"接管停止中",
      //   17:"成功"


//       	//任务状态
// 	TASK_STATUS :{
//     UNKNOWN:0,         		//未知的任务状态
//     WAITTING:1,       		//任务等待运行
//     RUNNING:2,         		//任务正在运行
//     PAUSED:3,          		//任务暂停
//     STOPPED:4,         		//任务停止
//     STOPPING:5,        		//任务停止中
//     NETWORK_FAULT:6,   		//网络故障
//     ABNORMAL:7,        		//任务已完成但异常
//     ERROR:8,           		//错误
//     SYNC:9,					//任务同步
//     PREPARING:10,			//准备中
//     PAUSING:11,	    		//任务暂停中
//     STARTING:12,        	//启动中
//     FINISHED:13,       		//已完成
//     TAKEOVER:14,       		//接管
//     TAKEOVER_STARTING:15,   //启动接管
//     TAKEOVER_STOPPING:16,   //停止接管
//     SUCCESSED:17,			//任务成功
// },
      for (let index = 0; index < currentdata.data.length; index++) {
        var element =currentdata.data[index];
        //任务等待运行 已完成 任务成功
        if(element[4] ==  17 || element[4] ==  2 || element[4] == 13 ){
          successNum ++;
        }
        //任务等待运行 准备中 
        else if(element[4] == 1 || element[4] == 10){
          waitNum++;
        }
        else{
          otherNum++;
        }
      }
      $(".databox3 #successnum").html(successNum);
      $(".databox3 #waitnum").html(waitNum);
      $(".databox3 #othernum").html(otherNum);
    }
    //设置历史任务
    var setHistoryTask =  function(historydata){
      var successNum = 0;
      var failNum = 0;
      var abnormalNum = 0;
      var cancelNum = 0;
      for (let index = 0; index < historydata.data.length; index++) {
        var element = historydata.data[index];
        switch  (element[5]){
          case "成功":
            successNum++;
            break;
          case "失败":
            failNum++;
            break;
          case "异常":
            abnormalNum++;
            break;
          case "中止":
            cancelNum++;
            break;
        }
      }
      $(".databox4 #successnum").html(successNum);
      $(".databox4 #failnum").html(failNum);
      $(".databox4 #abnormalnum").html(abnormalNum);
      $(".databox4 #cancelnum").html(cancelNum);
    }
    //获取系统数据
    var getSystemData =  function(){
        // clearTimeout(timerTask.getSystemData);
        if(0 == $('.datacenter-xf').size()) {
          clearTimeout(timerTask.getSystemData);
          return;
        }else {
          clearTimeout(timerTask.getSystemData);
        }
        var updateInterval = 2000;	
        var p = {}
        p.node_uuid = $("#node_uuid").val();
        if(initNetworkFlag) {
          p.count = 100;
        }else {
          p.count = 1;
        }
        p = JSON.stringify(p);
        $.post(CONF.AJAXPATH, {m:CONF.M.HOMEPAGE,f:'getXfSystemData',p:p}, function(d){
            $('.datacenter-xf .data').show();
            var data =  JSON.parse(d);
            var systemViewData =  data.systemViewData; //系统数据概览
            setSystemDataCenter(systemViewData); //设置系统概览数据
            var cpuMemoryData = data.cpumemory;
            var systemStorageData =  data.systemStorageData;//系统存储数据 
            var staticData  = data.staticData; //最近七日统计数据
            var netWorkData =  JSON.parse(data.netWorkData); 
            var currenttask = data.currenttask; //获取当前任务
            var historytask =  data.historytask; //获取历史任务
            //倒转后的新数组
            netWorkData = $.map(netWorkData, function (item, index) {// map方法匿名函数传的值v是值、i是索引。
                return netWorkData[netWorkData.length - 1 - index];
            });
            cpuMemoryData =  $.map(netWorkData, function (item, index) {// map方法匿名函数传的值v是值、i是索引。
                return cpuMemoryData[cpuMemoryData.length - 1 - index];
            });
            setCurrentTask(currenttask);//设置当前任务
            setHistoryTask(historytask);//设置历史任务
            setSystemStorageData(systemStorageData); //设置系统存储率
            setNetworkData(netWorkData); //设置网络流量
            setCpuMemoryData(cpuMemoryData);//设置cpu 内存使用率
            setSevenDayConsumpData(staticData.data_statistics); //设置最近七日用量统计
            window.onresize  =  function(){
                cpuChart.resize();
                networkLineChart.resize();
                memoryChart.resize();
                consumpChart.resize();
                capacityCircle.resize();
            }
        }).complete(function() {timerTask.getSystemData = setTimeout(function(){getSystemData();}, updateInterval);});
    }
    //获取系统时间
    var getSysTime = function(){
      var p = {}
      p = JSON.stringify(p);
      $.post(CONF.AJAXPATH, {m:CONF.M.HOMEPAGE,f:'getSystemTime',p:p}, function(d){
          var data =  JSON.parse(d);
          setSysTime(data.systime);
      });
    }
    var setSysTime =  function(time){
      var now = new Date(time);
      var year = now.getFullYear();
      var month = getTwoNum(now.getMonth()+1);
      var day = getTwoNum(now.getDate());
      var hours = getTwoNum(now.getHours());
      var minutes = getTwoNum(now.getMinutes());
      var seconds = getTwoNum(now.getSeconds());
      showTime=year+"-"+month+"-"+day+" "+hours+":"+minutes+":"+seconds+"";
      $("#databoxtime").html(showTime);
        showTime = Date.parse(showTime) + 1000;
    }
    setInterval(function () { setSysTime(showTime); },1000);
      //时间补全成两位数
    var getTwoNum = function (num) { 
      if(num<10){
        num='0'+num;
      }
      return num;
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
    return {
        init:function(){
          getSysTime();
          initNodeUUID();
          initListener();
          initAlarmTips();
          initLoginHistory();
		  History.pushState({url:"./content/platform/databackup_center.php"}, document.title, "?homepage");
        },
        //定义更新告警信息接口,添加虚拟化中心成功,添加存储成功后调用
        updateTopAlarmTips: function(){
          updateAlarmTips();
        }
    }
}();
jQuery(document).ready(function(){
    DataBackupCenter.init();
})