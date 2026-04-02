var DataBackupCenter = function () {
    var timedata = [];
    var netOutdata = [];
    var netIndata = [];

    var initNetworkFlag = true;
    _TASKINTERVAL = 5000;		//任务获取延迟时间	5秒钟
    var showTime;//系统时间
    //历史登录提示
    var initLoginHistorys = function(){
        if(!localStorage.getItem('visited')){
            pAjaxRequest({}, "/api/v1/users/history", "GET", function (result) {
                localStorage.setItem('visited', true);
                if (result.code == 0 && result.data.values != '') {
                    UIToastr.showSuccess(result.data.title, result.data.values);
                };
            });
        }
    }
    //检查系统授权
    var checkLisence = function(){
        $.post(CONF.AJAXPATH, {m:CONF.M.PLATFORM,f:'getLisenceInfo'}, function(data){
            var survey = JSON.parse(data);
            if(!survey.status){
                UIToastr.showWarning(survey.title, survey.info);
            }
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
    var initListener = function () {
        //未授权任务模块不跳转
        if($("#curtask .arrow").length != 0) {
            $("#curtask").click(function () {
                LOCATION('./content/platform/jobs/jobs.php', 'task', { tabId: 'currentLi' });
            });
        }else {
            $("#curtask .card").css("cursor","default");
        }
        if($("#curtask .arrow").length != 0) {
            $("#histask").click(function () {
                LOCATION('./content/platform/jobs/jobs.php', 'task', { tabId: 'historyLi' } );
            });
        }else {
            $("#histask .card").css("cursor","default");
        }
        $("#node_uuid").on('change',function(){
            initNetworkFlag = true;
            //得到cpu、内存使用率、bps、iops读写速度
            getSystemMonitorData();
            //得到网络流量图表数据
            getNetworkChartData();
        });
        intChartSize();
        //tab切换动画
        $(".dataprotectdiv .nav-tabs > li").click(function () {
            tabanimite(".dataprotectdiv",$(this));
        });
        //初始化碳排放
        initCarbonLocation();
        $(".addAuth").click(function () {
            LOCATION('./content/platform/settings/authorization_module.php', 'authorization_module');
        });
    }
    var tabanimite = function(classname,currentel) {
        var right = 15;
        var allLis = $(classname + ' .nav-tabs li');
        if(allLis.length < 2) return;
        //获取滑块位置
        for(var i = 0; i < allLis.length; i++) {
            if(i < allLis.index(currentel)){
                right += $(allLis[i]).width();
            }
        }
        $(classname + " .dataSlider").html(currentel.find("a").html())
        $(classname + " .dataSlider").css({
            "width":currentel.width(),
            "right": right + "px",
        });

    }
    //系统时间
    var showLeftTime = function (systime) {
        var now = new Date(systime);
        var year = now.getFullYear();
        var month = getTwoNum(now.getMonth()+1);
        var day = getTwoNum(now.getDate());
        var hours = getTwoNum(now.getHours());
        var minutes = getTwoNum(now.getMinutes());
        var seconds = getTwoNum(now.getSeconds());
        showTime=year+"-"+month+"-"+day+" "+hours+":"+minutes+":"+seconds+"";
        $("#systemtime").html(showTime);
        showTime = Date.parse(showTime) + 1000;
    }
    setInterval(function () { showLeftTime(showTime); },1000);
    //时间补全成两位数
    var getTwoNum = function (num) {
        if(num<10){
            num='0'+num;
        }
        return num;
    }
    //得到概览数据
    var initDataCenterView = function(){
        function update() {
            pAjaxRequest({}, "/api/v1/homepage/datacenter_view", "GET", function (result) {
                showLeftTime(result.data.system_running_time_info.running_time);
                $(".serverRunTime").html(result.data.system_running_time_info.running_day);
                $(".serverProtectData").html(result.data.accumulate_data.value + result.data.accumulate_data.unit);
                initfontSize(".serverProtectData",8,18,1920);
                $(".serverCurrentTask").html(result.data.current_task_num);
                $(".serverHisTask").html(result.data.history_task_num);
            });
        }
        update();
        if (timerTask.initDataCenterView) {
            clearInterval(timerTask.initDataCenterView); //清除旧定时器
        }
        timerTask.initDataCenterView = setInterval(update, 5000); //每两秒请求一次
        //数据概览样式
        $('.allInfo .cardbox .card').mouseenter(function(){
            $(this).addClass("active");
        });
        $('.allInfo .cardbox .card').mouseleave(function(){
            $(this).removeClass("active");
        });
    }
    const initCarbonLocation = function () {
        //碳排放
        if($("#carbonMonitor").length != 0) {
            $.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM, f:'getCarbonMonitorInfo', p:{}}, function(d){
                var data  = JSON.parse(d);
                if($.inArray('carbon_monitor_platform', CONF.PERMISSION) != -1 && data.config_carbon_flag) {
                    $('#carbonMonitor').show();
                    $('#histask').hide();
                } else {
                    $('#histask').show();
                    $('#histask .datades').text(LANG.UI_HOMEPAGEPRO_HISTORY_DES);
                    $('#carbonMonitor').hide();
                    return;
                }
                lastIp = data.httpType + data.ipaddr;
                $.ajax({
                    url: lastIp + '/YQ',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        "apikey": "PCo87AuFXHGjnf7PfKsjYYtpGsni9jlS"
                    }),
                    referrerPolicy: 'no-referrer',
                    success: function(response) {
                        $('.carbonMonitorNum').html(response.data + '<span class="co2">CO<sub>2</sub></span>');
                    },
                    error: function(xhr, status, error) {
                        $('.carbonMonitorNum').html('--');
                    }
                });
                $("#carbonMonitor").click(function () {
                    window.open(lastIp + ':443/carbon?apikey=PCo87AuFXHGjnf7PfKsjYYtpGsni9jlS', '_bank');
                });
            });
        }else {
            $("#carbonMonitor").css("cursor","default");
        }
    }
    // 任务概览
    var initTaskStatus = function () {
        pAjaxRequest({}, "/api/v1/homepage/job_status_view", "GET", function (result) {
            let data = result.data;
            $(".runNum").html(data.running_num);
            $(".waitNum").html(data.waiting_num);
            $(".stopNum").html(data.stop_num);
            $(".abnormalNum").html(data.abnormal_num);
            $(".failNum").html(data.fail_num);
        });
    }

    // 动态生成模块
    function generateModules(authorizationData) {
        let showNum = 0;//大分类受保护数量
        let showMouduleNum = 0; //显示几个大分类
        for (let i in authorizationData) {
            if (authorizationData[i].show) {//四个大分类是否显示
                showMouduleNum++;
                showNum = 0;//清零
                $('.' + i).removeClass('hidden');
                let module = authorizationData[i].module;
                for (let j in module) {//小模块是否显示
                    if (module[j].show) {
                        $('.' + j + ' .number').html(module[j].protected + '/' +  module[j].total);
                        showNum += module[j].protected;
                    }
                }
                $('.' + i + ' dt .number').html(showNum);
            }
        }
        //初始化tab滑块位置
        let showElement = $('.dataprotectdiv .nav-tabs li:not(.hidden)');
        let index = showElement.length - 1;
        tabanimite(".dataprotectdiv",$(showElement).eq(index));
        // 获取显示出来的tab 设置第一个和最后一个样式为圆角
        $(showElement).eq(0).css("cssText","border-radius: 0 30px 30px 0 !important;");
        $(showElement).eq(index).css("cssText","border-radius: 30px 0 0 30px !important;").addClass("active");
        $('.dataprotect .tab-content div').eq(0).addClass("active");
        switch(showMouduleNum) {
            case 1://两个模块的时候，和顶部距离变大
                break;
            case 2://两个模块的时候，和顶部距离变大
                break;
        }
    }

    
    //处理字体--避免数据过长时 文字超出
    //参数1：类名  参数2：字符串限制的长度  参数3：如果超出限制长度的字体大小 参数4：分辨率
    var initfontSize = function(selectname,limitNum,fontSize,screenSize) {
        if($(window).width() <= screenSize) {
            var str = $(selectname).html();
            if(str && str.length > limitNum) {
                $(selectname).css("font-size", fontSize + "px");
            }
        }
    }
  
    //存储统计
    var getSystemStorageData = function () {
        pAjaxRequest({}, "/api/v1/homepage/storage_info", "GET", function (result) {
            let data = result.data;
            var sum = data.used_capacity.size + data.remaining_capacity.size;
            if(sum == 0) {
                var usedPercent = 0 + "%";
                var remainPercent = 0 + "%";
            }else {
                var usedPercent = Math.round(data.used_capacity.size / (sum) * 10000) / 100 + "%";
                var remainPercent = Math.round(data.remaining_capacity.size / (sum) * 10000) / 100 + "%";
            }
            $(".storageNum").html(data.storage_num);
            $(".storageCap").html(data.total_capacity.value);
            $(".storageCapUnit").html(data.total_capacity.unit);
            initfontSize(".storageCap",8,14,1920);
            $(".usedPercent").html(usedPercent);
            $(".usedNum").html(data.used_capacity.value);
            $(".usedUnit").html(data.used_capacity.unit);
            $(".remainPercent").html(remainPercent);
            $(".remianNum").html(data.remaining_capacity.value);
            $(".remianUnit").html(data.remaining_capacity.unit);
            initfontSize(".copyStorageCap",8,12,1920);
            initStorageChart(usedPercent);
        });
    }
    var initStorageChart = function (usedPercent) {
        let used = parseFloat(usedPercent.replace('%', ''))/100;
        let value = used; // 0~1之间
        let startAngle = 220; //开始角度
        let endAngle = -40; // 结束角度
        let splitCount = 35; // 刻度数量
        let length = 12;//刻度线长度
        let width = 3;//刻度线宽度
        let pointerAngle = (startAngle - endAngle) * (1 - value) + endAngle; // 当前指针（值）角度
        var myChart = echarts.init(document.getElementById('capacityCircle'));
        var option = {
          title: {
                text: usedPercent,
                left: 'center',
                top: '37%',
                fontFamily: 'Roboto, Roboto',
                textStyle: {
                  color: '#415058',
                  fontSize: 20
                }
              },
          series: [
            {
              type: 'gauge',
              radius: '100%',
              startAngle: pointerAngle,
              endAngle: endAngle,
              splitNumber: 1,
              axisLine: {
                show: false,
                lineStyle: {
                  width: width,
                  opacity: 0
                }
              },
              title: { show: false },
              detail: {
                    offsetCenter: [0, 14],
                    color: '#818181',
                    fontSize: 14,
                    fontWeight: 400,
                    formatter: function (value) {
                      return LANG.UI_PUBLIC_VM_USED;
                    }
                  },
              splitLine: { show: false },
              axisTick: {
                length: length,
                splitNumber: Math.ceil((1 - value) * splitCount),
                lineStyle: {
                  color: '#CCCCCC',//灰色刻度
                  width: width
                }
              },
              axisLabel: { 
                show: false,
              },
              pointer: { show: false },
              itemStyle: {},
              data: [
                {
                  value: value,
                  name: 'grey'
                }
              ]
            },
            
            {
              type: 'gauge',
              radius: '100%',
              startAngle: startAngle,
              endAngle: pointerAngle,
              splitNumber: 1,
              axisLine: {
                show: false,
                lineStyle: {
                  width: width,
                  opacity: 0
                }
              },
              title: { show: false },
              detail: { show: false },
              splitLine: { show: false },
              axisTick: {
                length: length,
                splitNumber: Math.ceil(value * splitCount),
                lineStyle: {
                    color: '#20C99A',//灰色刻度
                    width: width
                }
              },
              axisLabel: { show: false },
              pointer: { show: false },
              itemStyle: {},
              data: [
                {
                  value: value,
                  name: 'blue'
                }
              ]
            },
            
          ]
        };
        myChart.setOption(option);
    }
    var getNetworkChartData = function () {
        if (timerTask.networkChartData) {
            clearTimeout(timerTask.networkChartData);
        }
        var updateInterval = 2500;
        //获取节点
		var node_uuid = $("#node_uuid").val();
		var info = {};
		info.node_uuid = node_uuid;
		info.time_range = '';
		info.range_start_time = '';
		info.range_end_time = '';
		info.cpu_alarm = '';
		info.ram_alarm = '';
		info.root_alarm = '';
		
		info = JSON.stringify(info);
		//同步执行ajax
		$.ajax({ 
			type: "post", 
	        url: CONF.AJAXPATH, 
	        async:true, 
	        data:{m:CONF.M.SYSTEMMONITOR,f:'initDataFunc',p:info},
	        success: function(d){ 
	        	var data = JSON.parse(d);
                
                initNetWorkChart(data['netWorkMsg']);
	        } 
		}).complete(function() {timerTask.networkChartData = setTimeout(function(){getNetworkChartData()}, updateInterval);});
    }

    //把所有out、in数据分组，方便后面计算
    const groupOutInData = function (data) {
        const outArrays = [];
        const inArrays = [];
        //将out数据和in的数据分类
        for (const key in data) {
            if (key.endsWith("out")) {//以out结尾的
                outArrays.push(data[key]);
            } else if (key.endsWith("in")) {//以in结尾的
                inArrays.push(data[key]);
            }
        }
        const sumOut = sumOutInData(outArrays);
        const sumIn = sumOutInData(inArrays);
        return {sumOut, sumIn};
    }
     
    //将输入/输出数据数组转为数字 按列相加 保留两位小数
    const sumOutInData = function (data) {
        if (data.length === 0) return [];
        // 将数组中的字符串转为数字
        const intArrays = data.map(arr => arr.map(Number));
        const minLength = Math.min(...intArrays.map(arr => arr.length));
        const result = new Array(minLength).fill(0);
        // 按列相加
        for (const arr of intArrays) {
            for (let i = 0; i < minLength; i++) {
                result[i] += arr[i];
            }
        }
        //保留两位小数
        return result.map(num => Number(num.toFixed(2)));
    }

    var initNetWorkChart = function (data) {
        if(!initNetworkFlag && timedata && netOutdata && netIndata && data.x_time.length != 0 && data.y_val.length != 0) {
            timedata.shift();
            netOutdata.shift();
            netIndata.shift();
            var temp_time = data.x_time[data.x_time.length - 1]
            timedata.push(temp_time.split(' ')[1]);
            let outInData = groupOutInData(data.y_val);
            netOutdata.push(outInData['sumOut'][outInData['sumOut'].length - 1]);
            netIndata.push(outInData['sumIn'][outInData['sumIn'].length - 1]);

        }else {
            let outInData = groupOutInData(data.y_val);
            initNetworkFlag = false;
            timedata = [];
            netOutdata = [];
            netIndata = [];
            timedata = data.x_time.map(function(item) {
                // 使用 split 方法按空格分割字符串，然后取第二个元素（时间部分）
                return item.split(' ')[1];
            });
            if (data.y_val == []) {
                netOutdata = [];
                netIndata = [];
            } else {
                netOutdata = outInData['sumOut'];
                netIndata = outInData['sumIn'];
            }
        }
        //网络流量折线
        if (!document.getElementById('networkTrafficChart')) {
            return;
        }
        let mergedArr = netOutdata.concat(netIndata);
        let maxNum = Math.max(...mergedArr);
        let interval = maxNum / 4;
        var polyLineChart = echarts.init(document.getElementById('networkTrafficChart'));
        option = {
            title: {
                text: LANG.UI_SYSTEM_MONITOR_NET_PERCENTAGE,
                textStyle: { // 标题样式
                    color: "#3D3D46",
                    fontSize: "16",
                    fontWeight:'normal',
                },

            },
            tooltip: {
                trigger: "axis",
                icon: "circle",
                axisPointer: {
                    icon: "circle",
                    lineStyle: {
                        color: "#1EB2FF"
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
                        relVal += '<br/>' + ' <span style="display:inline-block;margin-right:5px;width:10px;height:10px;border-radius: 50%!important;background-color:'+ params[i].color +';"></span>' +'  '+ params[i].seriesName +'  '+ pvalue;
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
                itemGap: 35, // 设置图例间距
                textStyle: {
                    color: "#3D3D46",
                    fontSize: "12"
                },
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
                            color: "#7C7D8B",
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
                            color: "#E6E9F4"
                        }
                    }
                },
                {
                    axisPointer: { show: false },
                    axisLine: { show: false },
                    //   axisLine: {
                    // 	lineStyle: {
                    // 	  color: "#E6E9F4"
                    // 	}
                    //   },
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
                    min: 0,
                    max: maxNum,
                    interval: interval,
                    axisLabel: {
                        textStyle: {
                            color: "#7C7D8B",
                            fontSize: 12
                        },
                        formatter: function(value, index){
                            const units = ['KB/s', 'MB/s', 'GB/s', 'TB/s'];
                            let i = 0;
                            let num = Number(value); // 确保是数字类型
                            while (num >= 1024 && i < units.length - 1) {
                              num /= 1024;
                              i++;
                            }
                            return `${num.toFixed(1)}${units[i]}`;
                        },
                    },
                    splitLine: {//不显示分割线
                        show: false,
                    }
                }
            ],
            series: [
                {
                    name: LANG.UI_PUBLIC_INFLOW,
                    type: "line",
                    smooth: true,
                    symbol: "circle",
                    symbolSize: 5,
                    showSymbol: false,
                    lineStyle: {
                        normal: {
                            color: "#20C99A",
                            width: 2
                        }
                    },
                    areaStyle: {
                        normal: {
                            color: new echarts.graphic.LinearGradient(0,0,0,1,
                                [
                                    {
                                        offset: 0,
                                        color: "#02e3a33c"
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
                            color: "#20C99A",
                            borderColor: "rgba(221, 220, 107, .1)",
                            borderWidth: 12
                        }
                    },
                    data: netIndata,
                },
                {
                    name: LANG.UI_PUBLIC_OUTFLOW,
                    type: "line",
                    smooth: true,
                    symbol: "circle",
                    symbolSize: 5,
                    showSymbol: false,
                    lineStyle: {
                        normal: {
                            color: "#10AEFF",
                            width: 2
                        }
                    },
                    areaStyle: {
                        normal: {
                            color: new echarts.graphic.LinearGradient(
                                0,
                                0,
                                0,
                                1,
                                [
                                    {
                                        offset: 0,
                                        color: "#1eb0f94e"
                                    },
                                    {
                                        offset: 1,
                                        color: "rgba(255, 255, 255, 0.3)"
                                    },
                                ],
                                false
                            ),
                            shadowColor: "rgba(0, 0, 0, 0.1)"
                        }
                    },
                    itemStyle: {
                        normal: {
                            color: "#10AEFF",
                            borderColor: "rgba(221, 220, 107, .1)",
                            borderWidth: 12
                        }
                    },
                    data: netOutdata,
                }
            ]
        };

        // 使用刚指定的配置项和数据显示图表。
        polyLineChart.setOption(option);
    }
    var getUnit = function(value) {
        if (value) {
            if(value >= 1024){
                return Math.round(value * 10 / 1024) / 10 + "MB/s";
            }else{
                return value + "KB/s";
            }
        } else {
            return 0 + "KB/s";
        }

    }
    var getSystemMonitorData = function () {
        if (timerTask.getSystemMonitorData) {
            clearTimeout(timerTask.getSystemMonitorData);
        }
        var updateInterval = 2000;
        var p = {}
        p.node_uuid = $("#node_uuid").val();
        p = JSON.stringify(p);
        $.post(CONF.AJAXPATH, {m:CONF.M.HOMEPAGE,f:'getSystemMonitorData',p:p}, function(d){
            var data = JSON.parse(d);
            $(".bpsread").html(getUnit(data.bpsReadSpeed));
            $(".bpswrite").html(getUnit(data.bpsWriteSpeed));
            $(".iopsread").html(data.iopsReadSpeed + data.iopsReadUnit);
            $(".iopswrite").html(data.iopsWriteSpeed + data.iopsWriteUnit);
            initCPUMemerryChart(data);
        }).complete(function() {timerTask.getSystemMonitorData = setTimeout(function(){getSystemMonitorData();}, updateInterval);});
    }
    var initCPUMemerryChart = function  (data) {
        var cpuUsedRate = data.cpuUsedRate;
        var cpuRemainRate = 100 - cpuUsedRate;
        var memoryUsedRate = data.memoryUsedRate;
        var memoryRemainRate = 100 - memoryUsedRate;
        var cpuUsedPercent = Math.round(cpuUsedRate) + "%";
        var memoryUsedPercent = Math.round(memoryUsedRate) + "%";
        // cpu
        if(!document.getElementById('cpuCircle')) {
            return;
        }
        var cpuChart = echarts.init(document.getElementById('cpuCircle'));
        option = {
            title: [
                {
                    text: `{val|${cpuUsedPercent}}`,
                    top: '33%',
                    left: 'center',
                    textStyle: {
                        rich: {
                            val: {
                                fontSize: 13,
                                fontWeight: 'bold',
                                color: '#3D3D46'
                            }
                        }
                    }
                },
            ],
            tooltip: {
                show:false
            },
            legend: {
                show:false,
            },
            series: [
                {
                    name: LANG.UI_DATACENTER_CPU_USED,
                    type: "pie",
                    hoverAnimation: false, // 鼠标移入变大
                    radius: ["90%", "100%"],
                    color: [
                        "#10AEFF",
                        "#E2E9F3",
                    ],
                    label: { show: false },
                    labelLine: { show: false },
                    data: [
                        { value: cpuUsedRate, name: LANG.UI_SETTING_USED_NUM },
                        { value: cpuRemainRate, name: LANG.UI_VISUAL_STORAGE_UNUSED },
                    ]
                }
            ]
        };
        cpuChart.setOption(option);
        // 内存
        if(!document.getElementById('memeryCircle')) {
            return;
        }
        var memeryChart = echarts.init(document.getElementById('memeryCircle'));
        option = {
            title: [
                {
                    text: `{val|${memoryUsedPercent}}`,
                    top: '33%',
                    left: 'center',
                    textStyle: {
                        rich: {
                            val: {
                                fontSize: 13,
                                fontWeight: 'bold',
                                color: '#3D3D46'
                            }
                        }
                    }
                },
            ],
            tooltip: {
                show:false,
            },
            legend: {
                show:false,
            },
            series: [
                {
                    name: LANG.UI_PUBLIC_MEMORY_RATE,
                    type: "pie",
                    hoverAnimation: false, // 鼠标移入变大
                    radius: ["90%", "100%"],
                    color: [
                        "#20C99A",
                        "#E2E9F3",
                    ],
                    label: { show: false },
                    labelLine: { show: false },
                    data: [
                        { value: memoryUsedRate, name: LANG.UI_SETTING_USED_NUM },
                        { value: memoryRemainRate, name: LANG.UI_VISUAL_STORAGE_UNUSED },
                    ]
                }
            ]
        };

        // 3. 配置项和数据给我们的实例化对象
        memeryChart.setOption(option);
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
            //得到cpu、内存使用率、bps、iops读写速度
            getSystemMonitorData();
            //得到网络流量图表数据
            getNetworkChartData();
        });

    }

    //页面一开始获取不到隐藏的标签页下的dom宽度,切换时重新设置宽度
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        resizeWidth();
        if(e.target.id =="vol_cdp_protect_tab" || e.target.id =="backup_tab"
            || e.target.id =="copy_tab"){
            var allChart = getAllChart();
            for(var i = 0; i < allChart.length; i++) {
                allChart[i].resize();
            }
        }
    }) ;
    //浏览器缩放的时候，图表也等比例缩放
    var intChartSize = function () {
        //重新计算图形宽度
        resizeWidth()
        window.addEventListener("resize", function() {
            if ($("#copyChart").length == 0) {
                return;
            }
            var allChart = getAllChart();
            for(var i = 0; i < allChart.length; i++) {
                resizeWidth();
                allChart[i].resize();
            }
            initDataProtectBar();//让柱状图显示条数不同分辨率不一样
        });
    }

    var resizeWidth = function () {
        let dataProtectWidth = $('.dataprotectdiv').width();
        let storageWidth = $('.storagecenterdiv').width();
        const windowWidth = window.innerWidth;
        if (windowWidth > 1365 && windowWidth < 1600) {
            $("#taskBarChart").width(dataProtectWidth * 0.328);
            $("#volCdpChart").width(dataProtectWidth * 0.56);
            $("#copyChart").width(dataProtectWidth * 0.56);
            $('.storage-circle-content').hide();
            // $(".storage-des-content").css({"width": "95%"});
        } else { 
            $("#taskBarChart").width(dataProtectWidth * 0.428);
            $("#volCdpChart").width(dataProtectWidth * 0.574);
            $("#copyChart").width(dataProtectWidth * 0.574);
            $("#capacityCircle").width(storageWidth * 0.32);
            $('.storage-circle-content').show();
            // $(".storage-des-content").css({"width": "58.33333333%"});
        }
        $("#taskPie").width(dataProtectWidth * 0.136);
        $("#volCdpPie").width(dataProtectWidth * 0.136);
        $("#copyPie").width(dataProtectWidth * 0.136);
    }

    var getAllChart = function () {
        var allChart = [];
        var taskPie = echarts.init(document.getElementById('taskPie'));
        var volCdpPie = echarts.init(document.getElementById('volCdpPie'));
        var copyPie = echarts.init(document.getElementById('copyPie'));
        var capacityCircle = echarts.init(document.getElementById('capacityCircle'));
        var taskBarChart = echarts.init(document.getElementById('taskBarChart'));
        var volCdpChart = echarts.init(document.getElementById('volCdpChart'));
        var copyChart = echarts.init(document.getElementById('copyChart'));
        var networkTrafficChart = echarts.init(document.getElementById('networkTrafficChart'));
        var memeryCircle = echarts.init(document.getElementById('memeryCircle'));
        var cpuCircle = echarts.init(document.getElementById('cpuCircle'));
        allChart = [taskPie,volCdpPie,copyPie,
        taskBarChart,volCdpChart,copyChart,
        capacityCircle,networkTrafficChart,memeryCircle,cpuCircle];
        return allChart;
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
            // var tips = '<span class="badge ' + badgeType + ' badgemark">' + total + '</span>';

            // 增加 blinking 动画
            $('.top-menu .nav .dropdown-notification .dropdown-toggle').addClass('has-data');
        } else {
            // 移除 blinking 动画
            $('.top-menu .nav .dropdown-notification .dropdown-toggle').removeClass('has-data');
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
            var tips = '<span class="badge badge-warning badgemark line-height-18px w-18px ms-8 p-0">' + d.storage + '</span>';
            storage_manager.append(tips);
            //父级-系统管理
            resource_manager.append(tips);
        }
        if(d.lisence){
            //系统授权
            var tips = '<span class="badge badge-danger badgemark line-height-18px w-18px ms-8 p-0">' + d.lisence + '</span>';
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
            LOCATION('./content/platform/jobs/jobs.php', 'task', { tabId: 'currentLi' });
        });
        $('.taskhrefhistory').off().on('click', function(){
            LOCATION('./content/platform/jobs/jobs.php', 'task', { tabId: 'historyLi' });
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
    //获取服务器时间
    var  initSystime = function (){
        $('.page-header.navbar .top-menu .navbar-nav > li.dropdown-time').show();
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
    var initDataProtect = function (auth_status) {
        //获取受保护设备数据
        Metronic.blockUI({target: '.datacenter-content',animate: true});
        pAjaxRequest({}, "/api/v1/homepage/protected_device", "GET", function (result) {
            Metronic.unblockUI('.datacenter-content');
            if (result.success) {
                generateModules(result.data);
                //数据备份任务饼图
                if(auth_status == 1) {
                    initTaskPie(result.data);
                }
                //连续数据保护任务饼图
                initVolCdpTaskPie(result.data.vol_cdp_protect);
                //数据复制任务饼图
                initCopyTaskPie(result.data.copy);
            }
        });
        initDataProtectBar();
    }
    var initDataProtectBar = function () {
        pAjaxRequest({}, "/api/v1/homepage/backup_data", "GET", function (result) {
            if (result.success) {
                //备份数据增长趋势柱状图
                initBackDataBar(result.data);
                //连续保护数据增长趋势柱状图
                initVolCdpDataBar(result.data);
                //复制数据增长趋势柱状图
                initCopyDataBar(result.data);
            }
        });
    }
    var initTaskPie = function (data) {
        const topModules = ["cloud", "file", "machine", "application"];
        let totalMouduleProtectedNum = {};
        topModules.forEach(module => {
            let totalProtected = 0;
            const modules = data[module].module;
            // 遍历子模块
            for (const subModule in modules) {
                if (modules.hasOwnProperty(subModule)) {
                    totalProtected += modules[subModule].protected;
                }
            }
            totalMouduleProtectedNum[module] = totalProtected;
        });
        let totalNum = totalMouduleProtectedNum.cloud +  totalMouduleProtectedNum.file +  totalMouduleProtectedNum.application +  totalMouduleProtectedNum.machine;
        var taskPie = echarts.init(document.getElementById('taskPie'));
        var option;
        option = {
            title: [
                {
                    text: `{val|${totalNum}}\n{name|${LANG.UI_HOMEPAGE_PROTECTED_DEVICE}}`,
                    top: '35%',
                    left: 'center',
                    textStyle: {
                        rich: {
                            name: {
                                fontSize: 12,
                                color: '#76767B',
                                padding: [7, 0]
                            },
                            val: {
                                fontSize: 20,
                                fontWeight: 'bold',
                                color: '#3D3D46'
                            }
                        }
                    }
                },
            ],
            tooltip: {
              trigger: 'item'
            },
            legend: {
                show: true,
                left: -9999, //移出屏幕
                top: -9999  //移出屏幕
            },
           color:["#62CBF8", "#5CDEB8", "#519AF2", "#6D7FF2"],
            series: [
              {
                name: LANG.UI_HOMEPAGE_PROTECTED_DEVICE,
                type: 'pie',
                radius: ['75%', '100%'],
                avoidLabelOverlap: false,
                hoverAnimation: false, // 禁用鼠标悬停时的动画效果
                itemStyle: {
                  borderRadius: 0,
                  borderColor: '#fff',
                  borderWidth: 2.3
                },
                label: {
                  show: false,
                  position: 'center'
                },
                labelLine: {
                  show: false
                },
                data: [
                  { value: totalMouduleProtectedNum.file, name: LANG.UI_FILE_FILE},
                  { value: totalMouduleProtectedNum.cloud, name: LANG.UI_HOMEPAGE_CLOUD  },
                  { value: totalMouduleProtectedNum.machine, name: LANG.UI_BACKUP_DATA_MODULE_OS },
                  { value: totalMouduleProtectedNum.application, name: LANG.UI_KUBE_APPLICATION },
                ]
              }
            ]
          };
        option && taskPie.setOption(option);
        toBindEvent('.each-module dt .square', taskPie);
    }

    var toBindEvent = function (div,chartName) {
        $(div).off().on('click', function(){
            chartName.dispatchAction({
                type: 'legendToggleSelect',
                name: $(this).parent().text().trim()
            });
        });
        chartName.on('legendselectchanged', function (params) {
            let selectFlag = ''; //是否选中
            switch (chartName._dom.id) {
                case 'taskPie':
                    switch (params.name) {
                        case LANG.UI_HOMEPAGE_CLOUD:
                            selectFlag = params.selected[params.name];
                            $('.each-module .cloud .square').css('background-color', selectFlag ? '#5CDEB8' : '#ccc');
                            $('.each-module dt .cloud').css('color', selectFlag ? '#4D4D4D' : '#ccc');
                            $('.each-module.cloud dt .number').css('color', selectFlag ? '#4D4D4D' : '#ccc');
                            break;
                        case LANG.UI_FILE_FILE:
                            selectFlag = params.selected[params.name];
                            $('.each-module .file .square').css('background-color', selectFlag ? '#62CBF8' : '#ccc');
                            $('.each-module dt .file').css('color', selectFlag ? '#4D4D4D' : '#ccc');
                            $('.each-module.file dt .number').css('color', selectFlag ? '#4D4D4D' : '#ccc');
                            break;
                        case LANG.UI_BACKUP_DATA_MODULE_OS:
                            selectFlag = params.selected[params.name];
                            $('.each-module .machine .square').css('background-color', selectFlag ? '#519AF2' : '#ccc');
                            $('.each-module dt .machine').css('color', selectFlag ? '#4D4D4D' : '#ccc');
                            $('.each-module.machine dt .number').css('color', selectFlag ? '#4D4D4D' : '#ccc');
                            break;
                        case LANG.UI_KUBE_APPLICATION:
                            selectFlag = params.selected[params.name];
                            $('.each-module .application .square').css('background-color', selectFlag ? '#6D7FF2' : '#ccc');
                            $('.each-module dt .application').css('color', selectFlag ? '#4D4D4D' : '#ccc');
                            $('.each-module.application dt .number').css('color', selectFlag ? '#4D4D4D' : '#ccc');
                            break;
                    }
                    break;
                case 'volCdpPie': 
                    switch (params.name) {
                        case LANG.UI_BACKUP_DATA_MODULE_OS:
                            selectFlag = params.selected[params.name];
                            $('.each-module.vol_cdp .complete_cdp_backup .square').css('background-color', selectFlag ? '#5CDEB8' : '#ccc');
                            $('.each-module.vol_cdp dd.complete_cdp_backup span').css('color', selectFlag ? '#4D4D4D' : '#ccc');
                            $('.each-module.vol_cdp dd.complete_cdp_backup span.number').css('color', selectFlag ? '#4D4D4D' : '#ccc');
                            break;
                        case LANG.UI_VOL_CDP_RECOVER_VOL:
                            selectFlag = params.selected[params.name];
                            $('.each-module.vol_cdp .vol_cdp_backup .square').css('background-color', selectFlag ? '#62CBF8' : '#ccc');
                            $('.each-module.vol_cdp dd.vol_cdp_backup span').css('color', selectFlag ? '#4D4D4D' : '#ccc');
                            $('.each-module.vol_cdp dd.vol_cdp_backup span.number').css('color', selectFlag ? '#4D4D4D' : '#ccc');
                            break;
                    }
                    break;
                case 'copyPie': 
                    switch (params.name) {
                        case LANG.UI_BACKUP_DATA_MODULE_OS:
                            selectFlag = params.selected[params.name];
                            $('.each-module.copy-class dd.machine_copy .square').css('background-color', selectFlag ? '#5CDEB8' : '#ccc');
                            $('.each-module.copy-class dd.machine_copy span').css('color', selectFlag ? '#4D4D4D' : '#ccc');
                            $('.each-module.copy-class dd.machine_copy .number').css('color', selectFlag ? '#4D4D4D' : '#ccc');
                            break;
                        case LANG.UI_VOL_CDP_RECOVER_VOL:
                            selectFlag = params.selected[params.name];
                            $('.each-module.copy-class dd.vol_cdp_copy .square').css('background-color', selectFlag ? '#62CBF8' : '#ccc');
                            $('.each-module.copy-class dd.vol_cdp_copy span').css('color', selectFlag ? '#4D4D4D' : '#ccc');
                            $('.each-module.copy-class dd.vol_cdp_copy .number').css('color', selectFlag ? '#4D4D4D' : '#ccc');
                            break;
                        case LANG.UI_FILE_FILE:
                            selectFlag = params.selected[params.name];
                            $('.each-module.copy-class dd.file_copy .square').css('background-color', selectFlag ? '#519AF2' : '#ccc');
                            $('.each-module.copy-class dd.file_copy span').css('color', selectFlag ? '#4D4D4D' : '#ccc');
                            $('.each-module.copy-class dd.file_copy .number').css('color', selectFlag ? '#4D4D4D' : '#ccc');
                            break;
                        case LANG.UI_AGENT_MODULE_DB:
                            selectFlag = params.selected[params.name];
                            $('.each-module.copy-class dd.dbcdpcopy .square').css('background-color', selectFlag ? '#6D7FF2' : '#ccc');
                            $('.each-module.copy-class dd.dbcdpcopy span').css('color', selectFlag ? '#4D4D4D' : '#ccc');
                            $('.each-module.copy-class dd.dbcdpcopy .number').css('color', selectFlag ? '#4D4D4D' : '#ccc');
                            break;
                    }
                    break;
            }
            
        });
    }

    var getLastSevenDaysPure = function (num) {
        const dates = [];
        const today = new Date();
        for (let i = 0; i < num; i++) {
          const date = new Date(today);
          date.setDate(today.getDate() - i);
          const month = String(date.getMonth() + 1).padStart(2, '0');
          const day = String(date.getDate()).padStart(2, '0');
          dates.unshift(`${month}-${day}`);
        }
        return dates;
    }

    var fillMissingDates = function (data,num = 7) {
        // 获取当前日期
        const today = new Date();
        num = num - 1;
        // 获取最近七天的日期字符串数组（格式为MM-DD）
        const lastSevenDays = [];
        for (let i = num; i >= 0; i--) {
            const date = new Date(today);
            date.setDate(today.getDate() - i);
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            lastSevenDays.push(`${month}-${day}`);
        }
     
        // 创建一个映射，用于快速查找日期对应的size
        const dateToSize = {};
        data.forEach(item => {
            dateToSize[item.date] = item.size;
        });
     
        // 初始化结果数组，全部设为0
        const result = new Array(7).fill(0);
     
        // 填充存在的size
        lastSevenDays.forEach((date, index) => {
            if (dateToSize[date] !== undefined) {
                result[index] = parseInt(dateToSize[date]);
            }
        });
     
        return result;
    }

    const fillDataDes = function (fixedDate,data) { 
        for (const module in data) {
            const moduleData = data[module];
            // // 跳过空数组
            // if (Array.isArray(moduleData) && moduleData.length === 0) continue;
            let divClassName = module + '_des';
            let $divs = $(`.${divClassName}`);
            // 如果没有找到对应的div，跳过
            if ($divs.length === 0) continue;
            $divs.each(function() {
                const $div = $(this);
                // 在模块数据中查找匹配日期的项
                const dataItem = moduleData.find(item => item.date === fixedDate);
                if (dataItem) {
                    // 找到匹配项，设置des内容
                    $div.text(dataItem.des);
                } else {
                    // 未找到匹配项，设置为0B
                    $div.text('0B');
                }
            });
        }
    }

    var initBackDataBar = function (data) {
        var taskBar = echarts.init(document.getElementById('taskBarChart'));
        var option;
        //四个大模块七天的数据
        const cloud = fillMissingDates(data.cloud);
        const file = fillMissingDates(data.file);
        const machine = fillMissingDates(data.machine);
        const application = fillMissingDates(data.application);
        let rawData = [//y轴数据
            cloud, //云
            file, //文件
            machine, //整机
            application, //应用
        ];
        let dateNum = 7;//x轴显示几天的数据
        const windowWidth = window.innerWidth;
        if (windowWidth > 1365 && windowWidth < 1600) {
            rawData = rawData.map(arr => arr.slice(-4));
            dateNum = 4;
        }
        let maxNum = findMaxSumData(rawData,dateNum);
        let interval = maxNum / 5;
        const totalData = [];
        for (let i = 0; i < rawData[0].length; ++i) {
          let sum = 0;
          for (let j = 0; j < rawData.length; ++j) {
            sum += rawData[j][i];
          }
          totalData.push(sum);
        }
        const colors = ["#5CDEB8", "#62CBF8", "#519AF2", "#6D7FF2"]; // 自定义颜色数组
        const series = [LANG.UI_HOMEPAGE_CLOUD, LANG.UI_FILE_FILE, LANG.UI_BACKUP_DATA_MODULE_OS, LANG.UI_KUBE_APPLICATION].map((name, sid) => {
          return {
            name,
            type: 'bar',
            stack: 'total',
            barWidth: '14px',
            itemStyle: {
              // 柱子颜色
              normal: {
                color: colors[sid], // 使用颜色数组中的颜色
                borderColor: 'transparent',
                borderWidth: 1.2,
              }
            },
            label: {
              show: false,
            },
            data: rawData[sid].map((d, did) =>
              totalData[did] <= 0 ? 0 : d 
            )
          };
        });
        option = {
            title: {
                text: LANG.UI_HOMEPAGE_DATA_INCRESE_CHART, 
                left: 'left',
                top: 'top',
                textStyle: {
                    color: '#3D3D46',
                    fontSize: 14, 
                    fontWeight: '400'
                },
                padding: 0 
            },
            color: ["#20C99A"],
            // backgroundColor: 'pink',
            tooltip: {
                trigger: "axis",
                axisPointer: {
                    type: "shadow"
                },
                className: 'data-bar-tooltip', // 自定义类名
                formatter: function (a) {
                    fillDataDes(a[0].name,data);
                    return $('.back-bar-tooltip-content').html();
                }
            },
            legend: {
                icon: "square",
                show:true,
                right:0,
                top: 20,
                itemWidth: 10,
                itemHeight:10,
                itemGap: 12, // 设置图例间距
                textStyle: {
                    color: "#666666",
                    fontSize: 12,
                    rich: {
                        a: {
                            verticalAlign: 'middle',
                        },
                    },
                    padding:[0,-2,-2,1],
                },
            },
            grid: {
                left: "5",
                top: "55",
                right: "0",
                bottom: "0",
                containLabel: true,
            },
            xAxis: [
                {
                    type: "category",
                    data: getLastSevenDaysPure(dateNum),
                    z: 10,
                    axisLabel: {
                        textStyle: {
                            color: "#7C7D8B",
                            fontSize: "12"
                        },
                        interval: 0,
                    },
                    axisLine: {
                        show: true,
                        lineStyle: {
                            color: "#F1F1F5" 
                        }
                    },
                    axisTick: {
                        show: false
                    },
                }
            ],
            yAxis: [
                {
                    type: "value",
                    min: 0,
                    max: maxNum,
                    interval: interval,
                    axisLabel: {
                        textStyle: {
                            color: "#7C7D8B",
                            fontSize: "12"
                        },
                        formatter: function(value, index){
                            if(value >= 1024 && value <= 1024*1024){
                                return Math.round(value * 10 / 1024) / 10 + "KB";
                            }else if(value >= 1024*1024 && value <= 1024*1024*1024){
                                return Math.round(value * 10 / (1024*1024)) / 10 + "MB";
                            }else if(value > 1024 *1024*1024 && value <= 1024 * 1024 * 1024*1024){
                                return Math.round(value * 10 /(1024 * 1024 * 1024) ) / 10 + "GB";
                            }else if(value > 1024*1024*1024*1024){
                                return Math.round(value * 10 /(1024 *1024 * 1024 * 1024) ) / 10 + "TB";
                            }
                            else{
                                return value + "B";
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
                            color: "#F1F1F5"
                        }
                    },
                }
            ],
            series
        };
        option && taskBar.setOption(option);
    }

    const findMaxSumData = function (rawData,num) {
        // 每一列的和
        const columnSums = new Array(num).fill(0);
        // 累加到相应的列
        rawData.forEach(arr => {
            for (let i = 0; i < num; i++) {
                columnSums[i] += arr[i];
            }
        });
        // 找到列和的最大值
        let maxSum = Math.max(...columnSums);
        maxSum = maxSum != 0 ? maxSum : 1;
        return maxSum;
    }
    var initVolCdpTaskPie = function (data) {
        let totalMouduleProtectedNum = {};
        const modules = data.module;
        // 遍历子模块
        for (const subModule in modules) {
            let totalProtected = 0;
            if (modules.hasOwnProperty(subModule)) {
                totalProtected += modules[subModule].protected;
            }
            totalMouduleProtectedNum[subModule] = totalProtected;
        }
        let totalNum = totalMouduleProtectedNum.complete_cdp_backup +  totalMouduleProtectedNum.vol_cdp_backup;
        var volCdpPie = echarts.init(document.getElementById('volCdpPie'));
        var option;
        option = {
            title: [
                {
                    text: `{val|${totalNum}}\n{name|${LANG.UI_HOMEPAGE_PROTECTED_DEVICE}}`,
                    top: '35%',
                    left: 'center',
                    textStyle: {
                        rich: {
                            name: {
                                fontSize: 12,
                                color: '#76767B',
                                padding: [7, 0]
                            },
                            val: {
                                fontSize: 20,
                                fontWeight: 'bold',
                                color: '#3D3D46'
                            }
                        }
                    }
                },
            ],
            tooltip: {
              trigger: 'item'
            },
            legend: {
                show: true,
                left: -9999, //移出屏幕
                top: -9999  //移出屏幕
            },
           color:["#5CDEB8", "#62CBF8"],
            series: [
              {
                name: LANG.UI_HOMEPAGE_PROTECTED_DEVICE,
                type: 'pie',
                radius: ['75%', '100%'],
                avoidLabelOverlap: false,
                hoverAnimation: false, // 禁用鼠标悬停时的动画效果
                itemStyle: {
                  borderRadius: 0,
                  borderColor: '#fff',
                  borderWidth: 2.3
                },
                label: {
                  show: false,
                  position: 'center'
                },
                labelLine: {
                  show: false
                },
                data: [
                  { value: totalMouduleProtectedNum.complete_cdp_backup, name: LANG.UI_BACKUP_DATA_MODULE_OS },
                  { value: totalMouduleProtectedNum.vol_cdp_backup, name: LANG.UI_VOL_CDP_RECOVER_VOL },
                
                ]
              }
            ]
          };
        option && volCdpPie.setOption(option);
        toBindEvent('.each-module.vol_cdp .square', volCdpPie);
    }
    var initVolCdpDataBar = function (data) {
        var taskBar = echarts.init(document.getElementById('volCdpChart'));
        var option;
        const complete_cdp_backup_data = fillMissingDates(data.complete_cdp_backup_data,10);
        const vol_cdp_backup_data = fillMissingDates(data.vol_cdp_backup_data,10);
        let rawData = [
            complete_cdp_backup_data, //整机实时
            vol_cdp_backup_data, //卷实时
        ];
        let dateNum = 10;//x轴显示几天的数据
        const windowWidth = window.innerWidth;
        if (windowWidth > 1365 && windowWidth < 1600) {
            rawData = rawData.map(arr => arr.slice(-7));
            dateNum = 7;
        }
        let maxNum = findMaxSumData(rawData,dateNum);
        let interval = maxNum / 5;
        const totalData = [];
        for (let i = 0; i < rawData[0].length; ++i) {
          let sum = 0;
          for (let j = 0; j < rawData.length; ++j) {
            sum += rawData[j][i];
          }
          totalData.push(sum);
        }
        const colors = ["#5CDEB8", "#62CBF8"]; // 自定义颜色数组
        const series = [LANG.UI_BACKUP_DATA_MODULE_OS, LANG.UI_VOL_CDP_RECOVER_VOL].map((name, sid) => {
          return {
            name,
            type: 'bar',
            stack: 'total',
            barWidth: '14px',
            itemStyle: {
              // 柱子颜色
              normal: {
                color: colors[sid], // 使用颜色数组中的颜色
                borderColor: 'transparent',
                borderWidth: 1.2,
              }
            },
            label: {
              show: false,
            },
            data: rawData[sid].map((d, did) =>
              totalData[did] <= 0 ? 0 : d 
            )
          };
        });
        option = {
            title: {
                text: LANG.UI_HOMEPAGE_DATA_INCRESE_CHART, 
                left: 'left',
                top: 'top',
                textStyle: {
                    color: '#3D3D46',
                    fontSize: 14, 
                    fontWeight: '400'
                },
                padding: 0 
            },
            color: ["#20C99A"],
            tooltip: {
                trigger: "axis",
                axisPointer: {
                    type: "shadow"
                },
                className: 'data-bar-tooltip', // 自定义类名
                formatter: function (a) {
                    fillDataDes(a[0].name,data);
                    return $('.cdp-bar-tooltip-content').html();
                }
            },
            legend: {
                icon: "square",
                show:true,
                right:0,
                top: 20,
                itemWidth: 10,
                itemHeight:10,
                itemGap: 12, // 设置图例间距
                textStyle: {
                    color: "#666666",
                    fontSize: "12",
                    rich: {
                        a: {
                            verticalAlign: 'middle',
                        },
                    },
                    padding:[0,-2,-2,1],
                }
            },
            grid: {
                left: "5",
                top: "55",
                right: "0",
                bottom: "0",
                containLabel: true
            },
            xAxis: [
                {
                    type: "category",
                    data: getLastSevenDaysPure(dateNum),
                    z: 10,
                    axisLabel: {
                        textStyle: {
                            color: "#7C7D8B",
                            fontSize: "12"
                        }
                    },
                    axisLine: {
                        show: true,
                        lineStyle: {
                            color: "#F1F1F5" 
                        }
                    },
                    axisTick: {
                        show: false
                    },
                }
            ],
            yAxis: [
                {
                    type: "value",
                    min: 0,
                    max: maxNum,
                    interval: interval,
                    axisLabel: {
                        textStyle: {
                            color: "#7C7D8B",
                            fontSize: "12"
                        },
                        formatter: function(value, index){
                            if(value >= 1024 && value <= 1024*1024){
                                return Math.round(value * 10 / 1024) / 10 + "KB";
                            }else if(value >= 1024*1024 && value <= 1024*1024*1024){
                                return Math.round(value * 10 / (1024*1024)) / 10 + "MB";
                            }else if(value > 1024 *1024*1024 && value <= 1024 * 1024 * 1024*1024){
                                return Math.round(value * 10 /(1024 * 1024 * 1024) ) / 10 + "GB";
                            }else if(value > 1024*1024*1024*1024){
                                return Math.round(value * 10 /(1024 *1024 * 1024 * 1024) ) / 10 + "TB";
                            }
                            else{
                                return value + "B";
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
                            color: "#F1F1F5"
                        }
                    }
                }
            ],
            series
        };
        option && taskBar.setOption(option);
    }
    var initCopyTaskPie = function (data) {
        let totalMouduleProtectedNum = {};
        const modules = data.module;
        // 遍历子模块
        for (const subModule in modules) {
            let totalProtected = 0;
            if (modules.hasOwnProperty(subModule)) {
                totalProtected += modules[subModule].protected;
            }
            totalMouduleProtectedNum[subModule] = totalProtected;
        }
        let totalNum = totalMouduleProtectedNum.machine_copy +  totalMouduleProtectedNum.vol_cdp_copy +  totalMouduleProtectedNum.file_copy +  totalMouduleProtectedNum.dbcdpcopy;
        var copyPie = echarts.init(document.getElementById('copyPie'));
        var option;
        option = {
            title: [
                {
                    text: `{val|${totalNum}}\n{name|${LANG.UI_HOMEPAGE_PROTECTED_DEVICE}}`,
                    top: '35%',
                    left: 'center',
                    textStyle: {
                        rich: {
                            name: {
                                fontSize: 12,
                                color: '#76767B',
                                padding: [7, 0]
                            },
                            val: {
                                fontSize: 20,
                                fontWeight: 'bold',
                                color: '#3D3D46'
                            }
                        }
                    }
                },
            ],
            tooltip: {
              trigger: 'item'
            },
            legend: {
                show: true,
                left: -9999, //移出屏幕
                top: -9999  //移出屏幕
            },
           color:["#5CDEB8", "#62CBF8", "#519AF2", "#6D7FF2"],
            series: [
              {
                name: LANG.UI_HOMEPAGE_PROTECTED_DEVICE,
                type: 'pie',
                radius: ['75%', '100%'],
                avoidLabelOverlap: false,
                hoverAnimation: false, // 禁用鼠标悬停时的动画效果
                itemStyle: {
                  borderRadius: 0,
                  borderColor: '#fff',
                  borderWidth: 2.3
                },
                label: {
                  show: false,
                  position: 'center'
                },
                labelLine: {
                  show: false
                },
                data: [
                  { value: totalMouduleProtectedNum.machine_copy, name: LANG.UI_BACKUP_DATA_MODULE_OS },
                  { value: totalMouduleProtectedNum.vol_cdp_copy, name: LANG.UI_VOL_CDP_RECOVER_VOL },
                  { value: totalMouduleProtectedNum.file_copy, name: LANG.UI_FILE_FILE },
                  { value: totalMouduleProtectedNum.dbcdpcopy, name: LANG.UI_AGENT_MODULE_DB },
                ]
              }
            ]
          };
        option && copyPie.setOption(option);
        toBindEvent('.each-module.copy-class .square', copyPie);
    }
    var initCopyDataBar = function (data) {
        var taskBar = echarts.init(document.getElementById('copyChart'));
        var option;
        const machine_copy_data = fillMissingDates(data.machine_copy_data, 10);
        const vol_cdp_copy_data = fillMissingDates(data.vol_cdp_backup_data, 10);
        const file_copy_data = fillMissingDates(data.file_copy_data, 10);
        const dbcdpcopy_data = fillMissingDates(data.dbcdpcopy_data, 10);
        let rawData = [
            machine_copy_data, //整机复制
            vol_cdp_copy_data, //卷复制
            file_copy_data, //文件复制
            dbcdpcopy_data, //数据库复制
        ];
        let dateNum = 10;//x轴显示几天的数据
        const windowWidth = window.innerWidth;
        if (windowWidth > 1365 && windowWidth < 1600) {
            rawData = rawData.map(arr => arr.slice(-7));
            dateNum = 7;
        }
        let maxNum = findMaxSumData(rawData,dateNum);
        let interval = maxNum / 5;
        const totalData = [];
        for (let i = 0; i < rawData[0].length; ++i) {
          let sum = 0;
          for (let j = 0; j < rawData.length; ++j) {
            sum += rawData[j][i];
          }
          totalData.push(sum);
        }
        const colors = ["#5CDEB8", "#62CBF8", "#519AF2", "#6D7FF2"]; // 自定义颜色数组
        const series = [LANG.UI_BACKUP_DATA_MODULE_OS, LANG.UI_VOL_CDP_RECOVER_VOL, LANG.UI_FILE_FILE, LANG.UI_VISUAL_DB].map((name, sid) => {
          return {
            name,
            type: 'bar',
            stack: 'total',
            barWidth: '14px',
            itemStyle: {
              // 柱子颜色
              normal: {
                color: colors[sid], // 使用颜色数组中的颜色
                borderColor: 'transparent',
                borderWidth: 1.2,
              }
            },
            label: {
              show: false,
            },
            data: rawData[sid].map((d, did) =>
              totalData[did] <= 0 ? 0 : d
            )
          };
        });
        option = {
            title: {
                text: LANG.UI_HOMEPAGE_DATA_INCRESE_CHART, 
                left: 'left',
                top: 'top',
                textStyle: {
                    color: '#3D3D46',
                    fontSize: 14, 
                    fontWeight: '400'
                },
                padding: 0 
            },
            color: ["#20C99A"],
            tooltip: {
                trigger: "axis",
                axisPointer: {
                    type: "shadow"
                },
                className: 'data-bar-tooltip', // 自定义类名
                formatter: function (a) {
                    fillDataDes(a[0].name,data);
                    return $('.copy-bar-tooltip-content').html();
                }
            },
            legend: {
                icon: "square",
                show: true,
                right: 0,
                top: 20,
                itemWidth: 10,
                itemHeight: 10,
                itemGap: 12,
                textStyle: {
                    color: "#666666",
                    fontSize: "12",
                    rich: {
                        a: {
                            verticalAlign: 'middle',
                        },
                    },
                    padding: [0, -2, -2, 1],
                }
            },
            grid: {
                left: "5",
                top: "55",
                right: "0",
                bottom: "0",
                containLabel: true
            },
            xAxis: [
                {
                    type: "category",
                    data: getLastSevenDaysPure(dateNum),
                    z: 10,
                    axisLabel: {
                        textStyle: {
                            color: "#7C7D8B",
                            fontSize: "12"
                        },
                    },
                    axisLine: {
                        show: true,
                        lineStyle: {
                            color: "#F1F1F5" 
                        }
                    },
                    axisTick: {
                        show: false
                    },
                }
            ],
            yAxis: [
                {
                    type: "value",
                    min: 0,
                    max: maxNum,
                    interval: interval,
                    axisLabel: {
                        textStyle: {
                            color: "#7C7D8B",
                            fontSize: "12"
                        },
                        formatter: function(value, index){
                            if(value >= 1024 && value <= 1024*1024){
                                return Math.round(value * 10 / 1024) / 10 + "KB";
                            }else if(value >= 1024*1024 && value <= 1024*1024*1024){
                                return Math.round(value * 10 / (1024*1024)) / 10 + "MB";
                            }else if(value > 1024 *1024*1024 && value <= 1024 * 1024 * 1024*1024){
                                return Math.round(value * 10 /(1024 * 1024 * 1024) ) / 10 + "GB";
                            }else if(value > 1024*1024*1024*1024){
                                return Math.round(value * 10 /(1024 *1024 * 1024 * 1024) ) / 10 + "TB";
                            }
                            else{
                                return value + "B";
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
                            color: "#F1F1F5"
                        }
                    }
                }
            ],
            series,
        };
        option && taskBar.setOption(option);
    }

    var getSystemAuthStatus = function () { 
        pAjaxRequest({}, "/api/v1/homepage/auth_status", "GET", function (result) {
            if(result.success) {//1系统已授权  2系统未授权
                if (result.data == 1) {//1系统已授权  2系统未授权
                    $('.no-auth-system').hide();
                    $('.auth-system').show();
                    //tab中只有一个li的时候，隐藏tab
                    if ($(".dataprotectdiv .nav-tabs > li").length == 1) {
                        $(".dataprotectdiv .nav-tabs").hide();
                    } else {
                        $(".dataprotectdiv .nav-tabs").show();
                    }
                } else {
                    $('.auth-system').hide();
                    $('.no-auth-system').show();
                    //tab中只有一个li的时候，隐藏tab
                    $(".dataprotectdiv .nav-tabs").hide();
                }
                initDataProtect(result.data);
            }
        });
    };
    return {
        //main function to initiate the module
        init: function () {
            getSystemAuthStatus();
            initSystime();
            initLoginHistorys();
            checkLisence(); //检查系统授权
            initUserType();
            initAlarmTips();
            initListener();
            initDataCenterView();
            initTaskStatus();
            getSystemStorageData();
            initNodeUUID();
            initLoginHistory();
            const title = document.title;
            History.pushState({url:"./content/platform/databackup_center_vinchin.php",routeName: 'homepage'}, title, "?homepage");
            requestAnimationFrame(() => {
                document.title = title;
            });
        },
        //定义更新告警信息接口,添加虚拟化中心成功,添加存储成功后调用
        updateTopAlarmTips: function(){
            updateAlarmTips();
        }
    };

}();

jQuery(document).ready(function() {
    DataBackupCenter.init();
});
