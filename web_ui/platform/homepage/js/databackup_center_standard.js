var DataBackupCenter = function () {
    let swiperlist = [];
    let timerTask = {
        initOverviewData: null //概览数据定时器
    };
    _TASKINTERVAL = 5000;		//任务获取延迟时间	5秒钟
    const initLisener = () => {
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            if(e.target.id =="data_recent_week" || e.target.id =="data_recent_month"
                || e.target.id =="data_recent_year" || e.target.id =="storage_recent_week" || e.target.id =="storage_recent_month"
                || e.target.id =="storage_recent_year"){
                var allChart = getAllChart();
                for(var i = 0; i < allChart.length; i++) {
                    allChart[i].resize();
                }
            }
        }) ;
        intChartSize(); 
        //加上跳转链接
        $("#op-log-card .title-href").off().on('click', function(){
            LOCATION('./content/platform/logs/logs.php', 'log')
        });
        $("#alarm-detail-card .title-href").off().on('click', function(){
             LOCATION('./content/platform/alarm/alarm.php', 'alarm');
        });
        $("#task-alarm-box .alarm-href").off().on('click', function(){
             LOCATION('./content/platform/alarm/alarm.php', 'alarm');
        });
        $("#system-alarm-box .alarm-href").off().on('click', function(){
             LOCATION('./content/platform/alarm/alarm.php?tab=1', 'alarm');
        });
    }
    const getAllChart = () => {
        var allChart = [];
        let weeklineChart = echarts.init(document.getElementById('data_week_line'));
        let monthlineChart = echarts.init(document.getElementById('data_month_line'));
        let yearlineChart = echarts.init(document.getElementById('data_year_line'));
        let weekstorageChart = echarts.init(document.getElementById('storage_week_bar'));
        let monthstorageChart = echarts.init(document.getElementById('storage_month_bar'));
        let yearstorageChart = echarts.init(document.getElementById('storage_year_bar'));
        allChart = [weeklineChart,monthlineChart,yearlineChart,weekstorageChart,monthstorageChart,yearstorageChart];
        return allChart;
    }

    //浏览器缩放的时候，图表也等比例缩放
    var intChartSize = function () {
        window.addEventListener("resize", function() {
            resizeChart();
        });
        const resizeObserver = new ResizeObserver((entries) => {
             resizeChart();
            
        });

        resizeObserver.observe($("#data-protect-trend-card")[0]);
    }

    const resizeChart = function () {
        var allChart = getAllChart();
        for(var i = 0; i < allChart.length; i++) {
            allChart[i].resize();
        }
    }

    // 初始化项目
    const initializeItems = (data) => {
        const backupSwiperWrapper = $('#backup_pane .swiper-wrapper');
        const cdpSwiperWrapper = $('#vol_cdp_protect_pane .swiper-wrapper');
        const copySwiperWrapper = $('#copy_pane .swiper-wrapper');
        backupSwiperWrapper.empty();
        cdpSwiperWrapper.empty();
        copySwiperWrapper.empty();
        //这里需要判断实时模块是有一个还是多个需要展示
        // 获取show为true的个数
        const count = Object.values(data.cdpmodule).filter(item => item.show == true).length;
        for (let modulekey in data) {
            for (let key in data[modulekey]) {
                if (!data[modulekey][key].show) {
                    continue;
                }
                let itemhtml = `<div class="swiper-slide">
                                            <div class="swiper-slide-content">
                                                <div class="title">
                                                    <div class="title-img">
                                                        <img src="/img/themeSkin/standardSkin/data-protect/${key}.svg" alt="">
                                                    </div>
                                                    ${setItemName(key)}
                                                </div>
                                                <div class="content">
                                                    ${modulekey != 'cdpmodule' || count != 1 ? '' : `
                                                        <div>
                                                            <div id="hostPie">
                                                            </div>
                                                            
                                                        </div>`
                    }
                                            
                                                    <div>
                                                        <div class="content-title">
                                                            ${modulekey != 'cdpmodule' || count != 1 ? LANG.UI_VCENTER_AUTH_TOTAL : LANG.UI_REPORT_AGENT_ALL}
                                                        </div>
                                                        <div class="content-num">
                                                            ${data[modulekey][key].total}
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div class="content-title">
                                                            ${LANG.UI_CLIENT_PROTECTED}
                                                        </div>
                                                        <div class="content-num">
                                                            ${data[modulekey][key].protected}
                                                        </div>
                                                    </div>
                                                    ${modulekey != 'cdpmodule' ? '' : `
                                                        <div>
                                                            <div class="content-title">
                                                                ${LANG.UI_VM_REPORT_NOIN_BACKUP}
                                                            </div>
                                                            <div class="content-num">
                                                                ${data[modulekey][key].total - data[modulekey][key].protected}
                                                            </div>
                                                        </div>`
                    }   

                                                    ${modulekey != 'cdpmodule' || count != 1 ? '' : `
                                                        <div>
                                                            <div class="content-title">
                                                                ${LANG.UI_CLOUD_PLATFORM_ONLINE}
                                                            </div>
                                                            <div class="content-num">
                                                                ${data[modulekey][key].onlineNum}
                                                            </div>
                                                        </div>`
                    }

                                                    ${modulekey != 'cdpmodule' || count != 1 ? '' : `
                                                        <div>
                                                            <div class="content-title">
                                                                ${LANG.UI_CLOUD_PLATFORM_OFFLINE}
                                                            </div>
                                                            <div class="content-num">
                                                                ${data[modulekey][key].offlineNum}
                                                            </div>
                                                        </div>`
                    }

                                                    ${modulekey != 'cdpmodule' || count != 1 ? '' : `
                                                        <div>
                                                            <div class="content-title">
                                                                ${LANG.UI_VIRTUAL_REAL_TIME_SYN_TASK_TOTAL}
                                                            </div>
                                                            <div class="content-num">
                                                                ${data[modulekey][key].taskNum}
                                                            </div>
                                                        </div>`
                    }

                                                    ${modulekey != 'cdpmodule' ? '' : `
                                                        <div>
                                                            <div class="content-title">
                                                                 ${LANG.UI_REPORT_CDP_BACKUP_SET}
                                                            </div>
                                                            <div class="content-num">
                                                                ${data[modulekey][key].backupSet}
                                                            </div>
                                                        </div>`
                    }
                                                    <div>
                                                        <div class="content-title">
                                                            ${LANG.UI_VOL_CDP_RECOVER_BACKUP_DATA}
                                                        </div>
                                                        <div class="content-num">
                                                            <span>${data[modulekey][key].protectData.value}</span><span class="unit">${data[modulekey][key].protectData.unit}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>`
                if (modulekey == "timemodule") {
                    backupSwiperWrapper.append(itemhtml)
                } else if (modulekey == "cdpmodule") {
                    cdpSwiperWrapper.append(itemhtml)
                } else if (modulekey == "copymodule") {
                    copySwiperWrapper.append(itemhtml)
                }

                //如果是实时容灾并且只有一个显示 那么需要初始化饼图
                if (modulekey == 'cdpmodule' && count == 1) {
                    initHostPie(data[modulekey][key]);
                }
            }
        }
        //这里需要统计各模块showflag的个数 如果为0则隐藏该模块
        let backupnoauthflag =  Object.values(data["timemodule"]).every(item => item.show == false); //如果为true 代表没有授权
        let cdpnoauthflag =  Object.values(data["cdpmodule"]).every(item => item.show == false); //如果为true 代表没有授权
        for (let modulekey in data) {
            let allshowflag =  true;
            allshowflag = Object.values(data[modulekey]).every(item => item.show == false);
            //这里需要动态加上active
            if(allshowflag == false){ //代表授权了 就直接显示
                if (modulekey == "timemodule") {
                    $("#data-protect-card .title .backup").addClass("active").show();
                    $("#backup_pane").addClass("active");
                } else if (modulekey == "cdpmodule") {
                    $("#data-protect-card .title .vol_cdp_protect").show();
                    if(backupnoauthflag){
                        $("#data-protect-card .title .vol_cdp_protect").addClass("active");
                        $("#vol_cdp_protect_pane").addClass("active");
                    }
                    //如果定时模块没授权 则加上active
                } else if (modulekey == "copymodule") {
                    $("#data-protect-card .title .copy").show();
                    if(backupnoauthflag && cdpnoauthflag){
                        $("#data-protect-card .title .copy").addClass("active");
                        $("#copy_pane").addClass("active");
                    }
                    
                }
            }
        }
      
        // 如果Swiper实例已存在，先销毁它
        if (swiperlist.length > 0) {
            swiperlist.forEach((swiper) => {
                swiper.destroy(true, true);
            })
        }
        // 重新初始化Swiper
        initSwiper();
    }
    // 初始化Swiper
    const initSwiper = () => {
        document.querySelectorAll('.mySwiper').forEach((container, index) => {
            const swiper = new Swiper(container, {
                slidesPerView: 'auto',
                loop: false,
                freeMode: false,
                resistanceRatio: 0, // 禁止边缘回弹
                navigation: {
                    nextEl: $(container).parent().find('.custom-button-next'),
                    prevEl: $(container).parent().find('.custom-button-prev'),
                },
                observer: true,
                observeParents: true,
                on: {
                    init: function () {
                        updateNavigationVisibility(container, index);
                    }
                }
            });
            swiperlist.push(swiper);
        });
        swiperlist.forEach(swiper => {
            swiper.update();
        });
    }

    // 设置每个swiperitem的宽度
    const updateNavigationVisibility = (container, index) => {
        const totalItems = $(container).find('.swiper-slide').length;
        if (totalItems <= 4) {
            // 设置幻灯片宽度为等宽
            $(container).find('.swiper-slide').css('width', `calc((100% - ${(totalItems - 1) * 16}px) / ${totalItems})`);
        } else {
            // 设置幻灯片宽度为固定宽度（显示4个）
            $(container).find('.swiper-slide').css('width', `calc((100% - 48px) / 4)`);
        }
    }
    //设置数据保护每个item的名字
    const setItemName = (key,usedecahrt = false) => {
        let itemname = '';
        switch (key) {
            case 'vm':
                itemname = LANG.UI_BACKUP_DATA_MODULE_VM;
                break;
            case 'private_cloud':
                itemname = LANG.UI_BACKUP_DATA_MODULE_PRIVATE_CLOUD;
                break;
            case 'public_cloud':
                itemname = LANG.UI_BACKUP_DATA_MODULE_PUBLIC_CLOUD;
                break;
            case 'file':
                itemname = LANG.UI_FILE_FILE;
                break;
            case 'nas':
                itemname = 'NAS';
                break;
            case 'hadoop':
                itemname = 'Hadoop HDFS';
                if(usedecahrt){
                    itemname = "Hadoop"
                }
                break;
            case 'obs':
                itemname = LANG.UI_VISUAL_OBS;
                break;
            case 'machine':
                itemname = LANG.UI_BACKUP_DATA_MODULE_OS;
                break;
            case 'vol':
                itemname = LANG.UI_VOL_CDP_RECOVER_VOL;
                break;
            case 'k8s':
                itemname = 'Kubernetes';
                if(usedecahrt){
                    itemname = LANG.UI_HOMEPAGE_STANDARD_CONTAINER
                }
                break;
            case 'db':
                itemname = LANG.UI_AGENT_MODULE_DB;
                break;
            case 'm365':
                itemname = 'Microsoft 365';
                if(usedecahrt){
                    itemname = "M365"
                }
                break;
        }
        return itemname;

    }

    //初始化数据保护中的实时模块饼图
    const initHostPie = (data) => {
        var hostPie = echarts.init(document.getElementById('hostPie'));
        var option = {
            color: ["#0FBF98", "#ECF0EE"],
            series: [
                {
                    name: '',
                    type: 'pie',
                    radius: ['70%', '100%'],
                    avoidLabelOverlap: false,
                    hoverAnimation: false, // 禁用鼠标悬停时的动画效果

                    label: {
                        show: false,
                        position: 'center'
                    },
                    data: [
                        { value: data.protected },
                        { value: data.total - data.protected },
                    ]
                }
            ]
        };
        hostPie.setOption(option);
    }



    //得到概览数据
    const initOverviewData = () => {
        updateOverviewData();
        if (timerTask.initOverviewData) {
            clearInterval(timerTask.initOverviewData); //清除旧定时器
        }
        timerTask.initOverviewData = setInterval(updateOverviewData, 900000); //每五秒请求一次
    }

    //更新概览数据
    const updateOverviewData = () => {
        pAjaxRequest({}, "/api/v1/homepage/datacenter_view", "GET", function (result) {
            $('.overview-item.current-task .item-text-num').html(result.data.current_task_num);
            $('.overview-item.history-task .item-text-num').html(result.data.history_task_num);
            $('.overview-item.total-data .item-text-num').html(result.data.accumulate_data.value);
            $('.overview-item.total-data .unit').html(result.data.accumulate_data.unit);
        });
    }

    //获取存储统计
    const getSystemStorageData = () => {
        $.post(CONF.AJAXPATH, { m: CONF.M.HOMEPAGE, f: 'getSystemStorageData', p: {} }, function (d) {
            var data = JSON.parse(d);
            var sum = data.allStorageInfo.usedCapacity.size + data.allStorageInfo.remainingCapacity.size;
            let usedPercent = 0 + "%";
            if (sum != 0) {
                usedPercent = Math.round(data.allStorageInfo.usedCapacity.size / (sum) * 10000) / 100 + "%";
            }
            $('.overview-item.storage-percent .item-text-num').html(usedPercent);
            $('.overview-item.remain-storage .item-text-num').html(data.allStorageInfo.remainingCapacity.value);
            $('.overview-item.remain-storage .unit').html(data.allStorageInfo.remainingCapacity.unit);
        });
    }
    //获取日志数据
    const getLogData = () => {
        pAjaxRequest({}, "/api/v1/homepage/log", "GET", function (result) {
            if (result.success) {
                var data = result.data;
                $('#task-log-num').text(data.job);
                $('#system-log-num').text(data.system);
            }

        });
    }
    //获取告警数据
    const getAlaramData = () => {
        pAjaxRequest({}, "/api/v1/homepage/alarm", "GET", function (result) {
            if (result.success) {
                var data = result.data;
                //任务告警
                $('#task-alarm-total-num').text(data.job.total);
                $('#task-alarm-warn-num').text(data.job.warn);
                $('#task-alarm-error-num').text(data.job.error);
                //系统告警
                $('#system-alarm-total-num').text(data.system.total);
                $('#system-alarm-warn-num').text(data.system.warn);
                $('#system-alarm-error-num').text(data.system.error);
                //任务告警列表显示
                setJobAlarmList(data.job.list);
            }

        });
    }
    //设置任务告警列表显示
    const setJobAlarmList = (data) => {
        var html = '';
        if(data.length == 0){
            $('.alarm-box').hide();
            $('#alarm-detail-card .nodata-box').show();
            return;
        }
        $('.alarm-box').show();
        $('#alarm-detail-card .nodata-box').hide();
        for (var i = 0; i < data.length; i++) {
            //设置告警图标
            var statusClass = '';
            if (data[i].status == 2) {
                statusClass = 'warn-status'
            } else if (data[i].status == 3) {
                statusClass = 'error-status'
            }
            html += `<div class="alarm-item">
                        <div class="d-flex">
                            <div class="status-box">
                                <div class="status-icon ${statusClass}">
                                </div>
                            </div>
                            <div>
                                <div class="taskname">
                                    ${data[i].job_name}
                                </div>
                                <div class="alarm-des">
                                    ${data[i].description}
                                </div>
                            </div>
                            
                        </div>
                        <div class="alarm-item-time">
                            ${data[i].alarm_time}
                        </div>
                    </div>`
        }
        $('.alarm-box').html(html);
    }
    //获取每个模块保护数据       
    const getProtectData = () => {
        pAjaxRequest({}, "/api/v1/homepage/pretected/data", "GET", function (result) {
            if (result.success) {
                //设置受保护数据
                initializeItems(result.data);
            }

        });
    }
    //获取保护数据echarts图
    const getProtectDataDetail = () => {
        pAjaxRequest({}, "/api/v1/homepage/pretected/data/trend", "GET", function (result) {
            if (result.success) {
                //设置受保护数据趋势
                initProtectEcharts(result.data);
                
            }

        });
    }
    //获取受保护数据echarts图
    const initProtectEcharts = (data) => {
        let weekdata = data.allmodule.data.slice(0, 7).reverse()
        let monthdata = data.allmodule.data.slice(0, 30).reverse()
        let yeardata = data.allmodule.data.slice(0, 365).reverse()
        //如果该模块数据为空 那么显示空模块
        let iszero_week = weekdata.every(num => num == 0); //如果全为0则返回true
        let iszero_month = monthdata.every(num => num == 0); //如果全为0则返回true
        let iszero_year = yeardata.every(num => num == 0); //如果全为0则返回true
        if (iszero_week) {
            $('.data_week_line').hide();
            $('.week_line_nodata').show();
        }else{
            $('.data_week_line').show();
            $('.week_line_nodata').hide();
            let weeklineChart = echarts.init(document.getElementById('data_week_line'));
            let weekmaxNum = Math.max(...weekdata);
            let weekinterval = weekmaxNum / 5;
            var weekoption = getoption(weekdata,weekinterval,7,weekmaxNum,data,"week");
            weeklineChart.setOption(weekoption);
        }
        if (iszero_month) {
            $('.data_month_line').hide();
            $('.month_line_nodata').show();
        }else{
            $('.data_month_line').show();
            $('.month_line_nodata').hide();
            let monthlineChart = echarts.init(document.getElementById('data_month_line'));
            let monthmaxNum = Math.max(...monthdata);
            let monthinterval = monthmaxNum / 5;
            var monthoption = getoption(monthdata,monthinterval,30,monthmaxNum,data,"month");
            monthlineChart.setOption(monthoption);
        }
        if(iszero_year){
            $('.data_year_line').hide();
            $('.year_line_nodata').show();
        }else{
            $('.data_year_line').show();
            $('.year_line_nodata').hide()
            let yearlineChart = echarts.init(document.getElementById('data_year_line'));
            let yearmaxNum = Math.max(...yeardata);
            let yearinterval = yearmaxNum / 5;
            //设置单位
            var yearoption = getoption(yeardata,yearinterval,365,yearmaxNum,data,"year");
            yearlineChart.setOption(yearoption);
        }
    }

    //获取option
    const getoption = (dataitem,interval,dateNum, maxNum,data,key) => {
        var lineoption = {
            // title: {
            //     text: "单位:",
            //     textStyle: {
            //         color: "#666666",
            //         fontSize: 12,
            //         fontWeight: "normal"
            //     }
            // },
            grid: {
                left: "0",
                right: "0",
                top: "23",
                bottom: "59",
                containLabel: true,
            },
            tooltip: {
                trigger: "axis",
                icon: "circle",
                axisPointer: {
                    icon: "line",
                    lineStyle: {
                        color: "#0FBF98",
                        width: 1,
                        type: "dashed",
                    },
                },
                 // 将 tooltip 渲染到 body 下，避免被 overflow 遮挡
                appendToBody: true,

                extraCssText: 'z-index: 99999;',
                formatter: function (a) {
                    getToolTipsContent(a[0].name, data);
                    return $('.protect-tooltip-content').html();
                }

            },
            xAxis: [
                {
                    type: "category",
                    boundaryGap: true,
                    axisLabel: {
                        textStyle: {
                            color: "#999999",
                            fontSize: 12
                        },
                    },
                    axisLine: {
                        lineStyle: {
                            color: "#EAEAEA"
                        },
                    },
                    axisTick: {
                        show: false,
                    },
                    data: getLastSevenDaysPure(dateNum),
                    splitLine: {//分割线
                        show: false,
                    }
                },
                {
                    axisPointer: { show: false },
                    axisLine: { show: false },
                    position: "bottom",
                },
            ],
            yAxis: [
                {
                    type: "value",
                    min: 0,
                    max: maxNum,
                    interval: interval,
                    axisTick: { show: false },//y轴刻度
                    axisLine: {
                        show: false
                    },
                    axisLabel: {
                        textStyle: {
                            color: "#999999",
                            fontSize: 12
                        },
                        formatter: function (value, index) {
                            if (value >= 1024 && value <= 1024 * 1024) {
                                return Math.round(value * 10 / 1024) / 10 + "KB";
                            } else if (value >= 1024 * 1024 && value <= 1024 * 1024 * 1024) {
                                return Math.round(value * 10 / (1024 * 1024)) / 10 + "MB";
                            } else if (value > 1024 * 1024 * 1024 && value <= 1024 * 1024 * 1024 * 1024) {
                                return Math.round(value * 10 / (1024 * 1024 * 1024)) / 10 + "GB";
                            } else if (value > 1024 * 1024 * 1024 * 1024) {
                                return Math.round(value * 10 / (1024 * 1024 * 1024 * 1024)) / 10 + "TB";
                            }
                            else {
                                return value + "B";
                            }
                        }
                    },
                    splitLine: {//不显示分割线
                        show: true,
                        lineStyle: {
                            type: 'dashed',
                            color: "#F0F2F5"
                        }
                    }
                }
            ],
            series: [
                {
                    type: "line",
                    smooth: true,
                    symbol: "circle",
                    symbolSize: 10,
                    itemStyle: {
                        color: 'rgba(0,0,0,0)', // 正常状态下使用透明颜色
                        borderColor: 'transparent', // 正常状态下透明边框
                        borderWidth: 3, // 设置边框宽度
                        emphasis: { // 鼠标悬停时的状态
                            color: '#0FBF98', // 强调状态下填充颜色
                            borderColor: "#FFF", // 强调状态下边框颜色
                            borderWidth: 3 // 强调状态下边框宽度
                        }
                    },
                    lineStyle: {
                        color: "#0FBF98",
                        width: 3
                    },
                    areaStyle: {
                        normal: {
                            color: new echarts.graphic.LinearGradient(0, 0, 0, 1,
                                [
                                    {
                                        offset: 0,
                                        color: "rgba(15, 191, 152, 0.3)"
                                    },
                                    {
                                        offset: 1,
                                        color: "rgba(15, 191, 152, 0.05)"
                                    }
                                ],
                                false
                            ),
                        }

                    },
                    data: dataitem,

                }
            ],
        }
        if(key == 'month' || key == 'year'){
            const startIndex = dataitem.length - 6; // 开始显示的索引
            const endIndex = dataitem.length;   // 结束显示的索引
            // 如果数据不足7个，显示全部
            const startIdx = Math.max(0, startIndex);
            const endIdx = Math.max(0, endIndex);
            lineoption['dataZoom']= [{
                bottom: '20px',
                right: '10px',
                xAxisIndex: 0,
                start: (startIdx / dataitem.length) * 100, // 起始百分比
                end: (endIdx / dataitem.length) * 100,     // 结束百分比
                borderColor: '#DEEDEAFF', // 边框颜色
                handleColor: '#FFFFFF',
                // 手柄的边框颜色
                handleBorderColor: '#BDE4DC',
                dataBackground: {
                    areaStyle: {
                        color: '#E7F5F1'
                    },
                    lineStyle: {
                        color: '#BBE3DA'
                    }
                },
                fillerColor: 'rgba(15, 191, 152, 0.10)', // 推荐使用透明色，如 rgba 或 hsla
            }];
        }else{
            lineoption.grid.bottom = '30';
        }
        return lineoption;
    }
    

    //组装受保护数据趋势toolTips内容
    const getToolTipsContent = (date, data) => {
        //找到顶部日期
        let result = {};
        let fulldate= '';
        let timeindex = 0;
        for (let firstmodule in data) {
            if (firstmodule == "allmodule") {
                continue;
            }
            result[firstmodule] = {};
            for (let secondemodule in data[firstmodule]) {
                if (!data[firstmodule][secondemodule].showflag) {
                    continue;
                }
                data[firstmodule][secondemodule].data.forEach((iteminfo,index) => {
                    if (iteminfo.date == date) {
                        if(fulldate == '' || timeindex == 0){
                            fulldate = iteminfo.fullDate
                            timeindex = index;
                        }
                        result[firstmodule][secondemodule] = {
                            des: iteminfo.des
                        }
                    }
                });
            }
        }
        //设置需要将存储显示重置下
        let datades = data.allmodule.datades[timeindex];
        //设置顶部显示
        $('.top-title-time').text(fulldate);
        $('.total-des').text(datades);
        let modulecount = 0;
        //设置备份模块显示
        for(let firstmodule in result){
            if(Object.keys(result[firstmodule]).length == 0){
                $(`.${firstmodule}`).hide();
                continue;
            }
            modulecount ++;
            let modulestr = ``;
            $(`.${firstmodule}`).show();
            $(`.${firstmodule} .sub-module`).html('');
            for(let secondemodule in result[firstmodule]){
                let moduletitle = setItemName(secondemodule,true);
                modulestr += `
                    <div class="sub-module">
                        <span>${moduletitle}</span>
                        <span class="protect-des">${result[firstmodule][secondemodule].des}</span>
                    </div>
                `;
                
            }
            $(`.${firstmodule} .module-content`).html(modulestr);
            
            
        }
        //如果是备份模块多，则分两列进行显示
        if(Object.keys(result['timemodule']).length > 4){
            $('.timemodule').addClass('double-column');
        }else{
            $('.timemodule').removeClass('double-column');
        }
        if(modulecount == 1 ){
            $('.module-title').hide();
            $('.module-item').addClass('single-column')
        }else{
            $('.module-title').show();
            $('.module-item').removeClass('single-column')
        }
    }

    const getLastSevenDaysPure = (num) => {
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

    //获取存储数据echart
    const getStorageDataDetail = ()=>{
         pAjaxRequest({}, "/api/v1/homepage/storage/data/trend", "GET", function (result) {
            if (result.success) {
                //设置受保护数据趋势
                initStorageEcharts(result.data);
                
            }

        });
    }
    //获取存储数据echart
    const initStorageEcharts = (data) => {
        const storages = data.map(item => item.size);
        const weekdata = storages.slice(0, 7).reverse();
        const monthdata = storages.slice(0, 30).reverse();
        const yeardata = storages.slice(0, 365).reverse();
         //如果该模块数据为空 那么显示空模块
        let iszero_week = weekdata.every(num => num == 0); //如果全为0则返回true
        let iszero_month = monthdata.every(num => num == 0); //如果全为0则返回true
        let iszero_year = yeardata.every(num => num == 0); //如果全为0则返回true
        if (iszero_week) {
            $('.storage_week_bar').hide();
            $('.week_bar_nodata').show();
        }else{
            $('.storage_week_bar').show();
            $('.week_bar_nodata').hide();
            let weekbarChart = echarts.init(document.getElementById('storage_week_bar'));
            let weekmaxNum = Math.max(...weekdata);
            let weekinterval = weekmaxNum / 5;
            var weekoption = getstorageoption(weekdata,weekinterval,7,weekmaxNum,data,"week");
            weekbarChart.setOption(weekoption);
        }

        if (iszero_month) {
            $('.storage_month_bar').hide();
            $('.month_bar_nodata').show();
        }else{
            $('.storage_month_bar').show();
            $('.month_bar_nodata').hide();
            let monthbarChart = echarts.init(document.getElementById('storage_month_bar'));
            let monthmaxNum = Math.max(...monthdata);
            let monthinterval = monthmaxNum / 5;
            var monthoption = getstorageoption(monthdata,monthinterval,30,monthmaxNum,data,"month");
            monthbarChart.setOption(monthoption);
        }

        if (iszero_year) {
            $('.storage_year_bar').hide();
            $('.year_bar_nodata').show();
        }else{
            $('.storage_year_bar').show();
            $('.year_bar_nodata').hide();
            let yearbarChart = echarts.init(document.getElementById('storage_year_bar'));
            let yearmaxNum = Math.max(...yeardata);
            let yearinterval = yearmaxNum / 5;
            var yearoption = getstorageoption(yeardata,yearinterval,365,yearmaxNum,data,"year"); 
            yearbarChart.setOption(yearoption);
        }
    }
    const getstorageoption = (dataitem,interval,dateNum, maxNum,data,key)=>{
        var option = {
            grid: {
                left: "0",
                right: "0",
                top: "23",
                bottom: "59",
                containLabel: true,
            },
            xAxis: {
                type: 'category',
                axisLabel: {
                    textStyle: {
                        color: "#999999",
                        fontSize: 12
                    },
                },
                axisLine: {
                    lineStyle: {
                        color: "#EAEAEA"
                    },
                },
                axisTick: {
                    show: false,
                },
                data: getLastSevenDaysPure(dateNum),
            },
            yAxis: {
                type: 'value',
                min: 0,
                max: maxNum,
                interval: interval,
                axisTick: { show: false },//y轴刻度
                axisLine: {
                    show: false
                },
                splitLine: {
                    show: true,
                    lineStyle: {
                        type: 'dashed',
                        color: "#F0F2F5"
                    }
                },
                axisLabel: {
                    textStyle: {
                        color: "#999999",
                        fontSize: 12
                    },
                    formatter: function (value, index) {
                        if (value >= 1024 && value <= 1024 * 1024) {
                            return Math.round(value * 10 / 1024) / 10 + "KB";
                        } else if (value >= 1024 * 1024 && value <= 1024 * 1024 * 1024) {
                            return Math.round(value * 10 / (1024 * 1024)) / 10 + "MB";
                        } else if (value > 1024 * 1024 * 1024 && value <= 1024 * 1024 * 1024 * 1024) {
                            return Math.round(value * 10 / (1024 * 1024 * 1024)) / 10 + "GB";
                        } else if (value > 1024 * 1024 * 1024 * 1024) {
                            return Math.round(value * 10 / (1024 * 1024 * 1024 * 1024)) / 10 + "TB";
                        }
                        else {
                            return value + "B";
                        }
                    }
                },
            },
            series:[{
                data: dataitem,
                type: 'bar',
                itemStyle: {
                    color: new echarts.graphic.LinearGradient(
                        0, 0, 0, 1,
                        [
                            {offset: 0, color: 'rgba(15, 191, 152, 0.5)'}, // 渐变起始颜色
                            {offset: 1, color: 'rgba(15, 191, 152, 1)'} // 渐变结束颜色
                        ]
                    ),
                    barBorderRadius: [10, 10, 10, 10] // 上下圆角半径，分别对应[左上角，右上角，右下角，左下角]
                },
                barWidth: '13px', // 柱子宽度
                barGap: '64px' // 柱子间隔
            }],
            tooltip: {
                trigger: "axis",
                axisPointer: {
                    type: "shadow"
                },
                formatter:function(a){
                    //找到对应信息返回
                    let result = getStorageToolTipsContent(a[0].name,data)
                    return ` 
                        <div class="protect-tooltip-box">
                            <div class="top-title-time">
                                ${result.fulldate}
                            </div>
                            <div class="top-total-info">
                                <div>
                                    <div class="circle">
                                    </div>
                                    <span>${LANG.UI_REPORT_STORAGE_USAGE}</span>
                                </div>
                                <span class="total-des">${result.des}</span>
                            </div>
                        </div>`
                }
            },
           
            
        };
         if(key == 'month' || key == 'year'){
            const startIndex = dataitem.length - 6; // 开始显示的索引
            const endIndex = dataitem.length;   // 结束显示的索引
            // 如果数据不足7个，显示全部
            const startIdx = Math.max(0, startIndex);
            const endIdx = Math.max(0, endIndex);

            option['dataZoom']= [{
                bottom: '20px',
                right: '10px',
                xAxisIndex: 0,
                start: (startIdx / dataitem.length) * 100, // 起始百分比
                end: (endIdx / dataitem.length) * 100,     // 结束百分比
                borderColor: '#DEEDEAFF', // 边框颜色
                handleColor: '#FFFFFF',
                // 手柄的边框颜色
                handleBorderColor: '#BDE4DC',
                dataBackground: {
                    areaStyle: {
                        color: '#E7F5F1'
                    },
                    lineStyle: {
                        color: '#BBE3DA'
                    }
                },
                fillerColor: 'rgba(15, 191, 152, 0.10)', // 推荐使用透明色，如 rgba 或 hsla
            }];
        }else{
            option.grid.bottom = '30';
        }
        return option;
    }
    const getStorageToolTipsContent = (date,data)=>{
        let result = {
            fulldate: '',
            des: ''
        }
        data.some(item => {
            if(item.date === date){
                result.fulldate = item.fullDate
                result.des = item.des;
                return true; // ✅ 返回 true 会终止遍历
            }
            return false;
        });
        return result;
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


    return {
        init: function () {
            initLisener();
            initOverviewData(); //得到概览数据--当期任务、历史任务、累计保护数据
            getSystemStorageData(); //获取存储容量
            getLogData(); //获取日志数据
            getAlaramData(); //获取告警数据
            getProtectData(); //获取保护数据
            getProtectDataDetail(); //获取保护数据echart
            getStorageDataDetail(); //获取存储数据echart
            initAlarmTips();
            const title = document.title;
            History.pushState({url:"./content/platform/databackup_center_standard.php",routeName: 'homepage'}, title, "?homepage");
            requestAnimationFrame(() => {
                document.title = title;
            });
        },
        //定义更新告警信息接口,添加虚拟化中心成功,添加存储成功后调用
        updateTopAlarmTips: function(){
            updateAlarmTips();
        }
    }
}();
jQuery(document).ready(function () {
    DataBackupCenter.init();
});
