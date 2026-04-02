var DataBackupCenter = function () {
    var timerTask = {};
    var timedata = [];
    var netOutdata = [];
    var netIndata = [];
    var cpuChart = echarts.init(document.getElementById('cpuCircle'));
    var memeryChart = echarts.init(document.getElementById('memeryCircle'));
    var barChart = echarts.init(document.getElementById('client_bar'),null,{
        renderer: 'svg'
    });
    var pieChart = echarts.init(document.getElementById('verify-device-chart'));
    var pieChartDevice = echarts.init(document.getElementById('total-device-chart'));
    var lineChart = echarts.init(document.getElementById('report_line'));
    var pieChartReport = echarts.init(document.getElementById('report_pie'));
    let isLoading = []; // 标记已经加载完的接口数量

    const STATUS = {
        PENDING: 0,
        ARCHIVED: 1,
        APPROVALING: 2,
        REJECTED: 3,
        REVOKE: 4,
        GENERATED: 9
    };
    var initNetworkFlag = true;
    _TASKINTERVAL = 5000; //任务获取延迟时间	5秒钟
    var showTime; //系统时间
    var initConfig = function () {
        $.post(CONF.AJAXPATH, {
            m: CONF.M.PLATFORM,
            f: 'getConfig',
            p: {}
        }, function (d) {
            var config = JSON.parse(d);
            CONF.SYSTEMNAME = config.SYSTEMNAME;
            CONF.VM_TYPE = config.VM_TYPE;
            CONF.VM_DES = config.VM_DES;
            CONF.SOFTWARE = config.SOFTWARE;
            CONF.DB_TYPE = config.DB_TYPE;
            CONF.DB_DES = config.DB_DES;
            CONF.IDLETIMEOUT = config.IDLETIMEOUT;
            CONF.PASS_LENGTH = config.PASS_LENGTH;
            CONF.PASS_COMPLEXITY = config.PASS_COMPLEXITY;
            //权限是object，需要转为array
            var permission = config.PERMISSION;
            var list = [];
            for (var i in permission) {
                list.push(permission[i]);
            }
            CONF.PERMISSION = list;
            CONF.PERMISSION_ARR = Object.values(config.PERMISSION_ARR);
            CONF.TASK_TYPE = config.TASK_TYPE;
            CONF.TASK_TYPE_DES = config.TASK_TYPE_DES;
            CONF.TASK_STATUS = config.TASK_STATUS;
            CONF.TASK_STATUS_DES = config.TASK_STATUS_DES;
            //初始化碳排放
            initCarbonLocation();
        });
    }
    //历史登录提示
    var initLoginHistorys = function () {
        if (!localStorage.getItem('visited')) {
            isLoading[0] = false;
            pAjaxRequest({}, "/api/v1/users/history", "GET", function (result) {
                isLoading[0] = true;
                localStorage.setItem('visited', true);
                if (result.code == 0 && result.data.values != '') {
                    UIToastr.showSuccess(result.data.title, result.data.values);
                };
            });
        }
    }
    //检查系统授权
    var checkLisence = function () {
        $.post(CONF.AJAXPATH, {
            m: CONF.M.PLATFORM,
            f: 'getLisenceInfo'
        }, function (data) {
            var survey = JSON.parse(data);
            if (!survey.status) {
                UIToastr.showWarning(survey.title, survey.info);
            }
        });
    }
    //初始化告警
    var initAlarmTips = function () {
        var getAlarmInfo = function () {
            $.post(CONF.AJAXPATH, {
                m: CONF.M.ALARM,
                f: 'getSurveyNoticeInfo',
                p: JSON.stringify({
                    no_surebackup_flag:1
                })
            }, function (d) {
                setAlarmInfo(d);
            })
                .complete(function () {
                    setTimeout(getAlarmInfo, _TASKINTERVAL);
                });
        }
        getAlarmInfo();
    }
    var initListener = function () {
        //GMP更多设备和消息
        $("#more_device").on('click', () => {
            LOCATION('./content/client/client.php', 'clients');
        });
        $("#more_msg").on('click', () => {
            LOCATION('./content/platform/todo_list.php', 'todo_list');
        });
        $("#more_verify").on('click', () => {
            LOCATION('./content/platform/jobs/verify_jobs.php?tab=1', 'verification_job');
        });
        // gmp首页echarts图切换
        $('#line_select').on('change', function () {
            getReportCountInfo();
        })
        $('#bar_select').on('change', function () {
            getClientInfo();
        })
        $('#go_approve').on('click', () => {
            LOCATION('./content/platform/industry/report_list.php', 'industry_report');
        })
        //未授权任务模块不跳转
        if ($("#curtask .arrow").length != 0) {
            $("#curtask").click(function () {
                LOCATION('./content/platform/jobs/jobs.php', 'task');
            });
        } else {
            $("#curtask .card").css("cursor", "default");
        }
        if ($("#curtask .arrow").length != 0) {
            $("#histask").click(function () {
                LOCATION('./content/platform/jobs/jobs.php?tab=1', 'task');
            });
        } else {
            $("#histask .card").css("cursor", "default");
        }
        $("#node_uuid").on('change', function () {
            initNetworkFlag = true;
            //得到cpu、内存使用率、bps、iops读写速度
            getSystemMonitorData();
            //得到网络流量图表数据
            // getNetworkChartData();
        });
        intChartSize();
        // if (0 != $('#networkTrafficChart').size()) {
        //     intChartSize();
        // }
        //报告跳转
        $('.report-line-info .total-val').parent().click(function () {
            LOCATION('./content/platform/industry/report_list.php', 'industry_report');
        })
        $('.report-line-info .success-val').parent().click(function () {
            LOCATION('./content/platform/industry/report_list.php', 'industry_report');
        })
        $('.report-line-info .abnormal-val').parent().click(function () {
            LOCATION('./content/platform/industry/report_list.php', 'industry_report');
        })
        //设备总个数
        $('.device-info .total-val').parent().click(function () {
            LOCATION('./content/client/client.php', 'clients');
        })
        //验证计划
        $('.device-info .plan-val').parent().click(function () {
            LOCATION('./content/platform/jobs/verify_jobs.php', 'verification_job');
        })
        $('.device-info .unverified-val').parent().click(function () {
            LOCATION('./content/platform/jobs/verify_jobs.php', 'verification_job');
        })
        $('.datacenter-content .total-device-info > div').click(function () {
            LOCATION('./content/client/client.php', 'clients');
        })
        $('.datacenter-content .verify-device-info > div').click(function () {
            LOCATION('./content/client/client.php', 'clients');
        })

    }
    var tabanimite = function (classname, currentel) {
        var right = 15;
        if (classname == ".dataprotectdiv") {
            var allLis = $(classname + ' .nav-tabs li[show="true"]');
        } else {
            var allLis = $(classname + ' .nav-tabs li');
        }
        if (allLis.length < 2) return;
        //获取滑块位置
        for (var i = 0; i < allLis.length; i++) {
            if (i < allLis.index(currentel)) {
                right += $(allLis[i]).width();
            }
        }
        $(classname + " .dataSlider").html(currentel.find("a").html())
        $(classname + " .dataSlider").css({
            "width": currentel.width(),
            "right": right + "px",
        });
    }
    //系统时间
    var showLeftTime = function (systime) {
        var now = new Date(systime);
        var year = now.getFullYear();
        var month = getTwoNum(now.getMonth() + 1);
        var day = getTwoNum(now.getDate());
        var hours = getTwoNum(now.getHours());
        var minutes = getTwoNum(now.getMinutes());
        var seconds = getTwoNum(now.getSeconds());
        showTime = year + "-" + month + "-" + day + " " + hours + ":" + minutes + ":" + seconds + "";
        $("#systemtime").html(showTime);
        showTime = Date.parse(showTime) + 1000;
    }
    setInterval(function () {
        showLeftTime(showTime);
    }, 1000);
    //时间补全成两位数
    var getTwoNum = function (num) {
        if (num < 10) {
            num = '0' + num;
        }
        return num;
    }
    //得到概览数据
    var initDataCenterView = function () {
        if (0 == $('.datacenter-content').size()) {
            clearTimeout(timerTask.initDataCenterView);
            return;
        } else {
            clearTimeout(timerTask.initDataCenterView);
        }
        $.post(CONF.AJAXPATH, {
            m: CONF.M.HOMEPAGE,
            f: 'getDataCenterView',
            p: {}
        }, function (d) {
            var data = JSON.parse(d);
            showLeftTime(data.systemRunningTimeInfo.runningTime);
            $(".serverRunTime").html(data.systemRunningTimeInfo.runningDay);
            $(".serverProtectData").html(data.accumulateData.value + data.accumulateData.unit);
            initfontSize(".serverProtectData", 8, 18, 1920);
            $(".serverCurrentTask").html(data.currentTaskNum);
            $(".serverHisTask").html(data.historyTaskNum);
        }).complete(function () {
            timerTask.initDataCenterView = setTimeout(function () {
                initDataCenterView();
            }, 2000);
        });
        //数据概览样式
        $('.allInfo .cardbox .card').mouseenter(function () {
            $(this).addClass("active");
        });
        $('.allInfo .cardbox .card').mouseleave(function () {
            $(this).removeClass("active");
        });
    }
    const initCarbonLocation = function () {
        //碳排放
        if ($("#carbonMonitor").length != 0) {
            $.post(CONF.AJAXPATH, {
                m: CONF.M.SYSTEM,
                f: 'getCarbonMonitorInfo',
                p: {}
            }, function (d) {
                var data = JSON.parse(d);
                if ($.inArray('carbon_monitor_platform', CONF.PERMISSION) != -1 && data.config_carbon_flag) {
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
                    success: function (response) {
                        $('.carbonMonitorNum').html(response.data + '<span class="co2">CO<sub>2</sub></span>');
                    },
                    error: function (xhr, status, error) {
                        $('.carbonMonitorNum').html('--');
                    }
                });
                $("#carbonMonitor").click(function () {
                    window.open(lastIp + ':443/carbon?apikey=PCo87AuFXHGjnf7PfKsjYYtpGsni9jlS', '_bank');
                });
            });
        } else {
            $("#carbonMonitor").css("cursor", "default");
        }
    }
    // 任务概览
    var initTaskStatus = function () {
        $.post(CONF.AJAXPATH, {
            m: CONF.M.HOMEPAGE,
            f: 'getTaskStatusView',
            p: {}
        }, function (d) {
            var data = JSON.parse(d);
            $(".runNum").html(data.runningNum);
            $(".waitNum").html(data.waitingNum);
            $(".successNum").html(data.successNum);
            $(".abnormalNum").html(data.abnormalNum);
            $(".failNum").html(data.failNum);
        });
    }
    //初始化授权基本信息
    var initLisenceInfo = function () {
        $.post(CONF.AJAXPATH, {
            m: CONF.M.HOMEPAGE,
            f: 'getModuleAuth',
            p: {}
        }, function (d) {
            var data = JSON.parse(d);
            // hostModuleCount统计的是主机模块授权 vmModuleCount统计的是虚拟机模块授权
            var hostModuleCount = data.fileinfo.showFlag + data.dbinfo.showFlag + data.osinfo.showFlag + data.cdpInfo.showFlag;
            var vmModuleCount = data.vminfo.showFlag + data.cloudInfo.showFlag + data.CDMInfo.showFlag;

            //根据授权判断标签页是否显示
            //tab显示的情况:除了自己还有其他模块被授权
            if (vmModuleCount > 0 && (hostModuleCount > 0 || data.nasInfo.showFlag || data.volcdpInfo.showFlag || data.dbcdpInfo.showFlag || data.awsInfo.showFlag || data.m365Info.showFlag ||
                data.hadoopInfo.showFlag || data.obsInfo.showFlag)) {
                $(".dataprotect .nav-tabs>li.vmProtect").css("cssText", "visibility: unset !important;");
                $(".dataprotect .nav-tabs>li.vmProtect").attr("show", "true"); //设置一个属性用于设置圆角
                $(".dataprotect .tab-pane#vmProtect").attr("show", "true");
            }
            if (hostModuleCount > 0 && (vmModuleCount > 0 || data.nasInfo.showFlag || data.volcdpInfo.showFlag || data.dbcdpInfo.showFlag || data.awsInfo.showFlag || data.m365Info.showFlag ||
                data.hadoopInfo.showFlag || data.obsInfo.showFlag)) {
                $(".dataprotect .nav-tabs>li.hostProtect").show();
                $(".dataprotect .nav-tabs>li.hostProtect").attr("show", "true");
                $(".dataprotect .tab-pane#hostProtect").attr("show", "true");
            }
            if (data.nasInfo.showFlag && (vmModuleCount > 0 || hostModuleCount > 0 || data.volcdpInfo.showFlag || data.dbcdpInfo.showFlag || data.awsInfo.showFlag || data.m365Info.showFlag ||
                data.hadoopInfo.showFlag || data.obsInfo.showFlag)) {
                $(".dataprotect .nav-tabs>li.nasProtect").show();
                $(".dataprotect .nav-tabs>li.nasProtect").attr("show", "true");
                $(".dataprotect .tab-pane#nasProtect").attr("show", "true");
            }
            if (data.volcdpInfo.showFlag && (vmModuleCount > 0 || hostModuleCount > 0 || data.nasInfo.showFlag || data.dbcdpInfo.showFlag || data.awsInfo.showFlag || data.m365Info.showFlag ||
                data.hadoopInfo.showFlag || data.obsInfo.showFlag)) {
                $(".dataprotect .nav-tabs>li.realTimeSync").show();
                $(".dataprotect .nav-tabs>li.realTimeSync").attr("show", "true");
                $(".dataprotect .tab-pane#realTimeSync").attr("show", "true");
            }
            if (data.dbcdpInfo.showFlag && (vmModuleCount > 0 || hostModuleCount > 0 || data.nasInfo.showFlag || data.volcdpInfo.showFlag || data.awsInfo.showFlag || data.m365Info.showFlag ||
                data.hadoopInfo.showFlag || data.obsInfo.showFlag)) {
                $(".dataprotect .nav-tabs>li.dbrealTime").show();
                $(".dataprotect .nav-tabs>li.dbrealTime").attr("show", "true");
                $(".dataprotect .tab-pane#dbrealTime").attr("show", "true");
            }
            //公有云
            if (data.awsInfo.showFlag && (vmModuleCount > 0 || hostModuleCount > 0 || data.nasInfo.showFlag || data.volcdpInfo.showFlag || data.dbcdpInfo.showFlag || data.m365Info.showFlag ||
                data.hadoopInfo.showFlag || data.obsInfo.showFlag)) {
                $(".dataprotect .nav-tabs>li.aws").show();
                $(".dataprotect .nav-tabs>li.aws").attr("show", "true");
                $(".dataprotect .tab-pane#aws").attr("show", "true");
            }
            //m365
            if (data.m365Info.showFlag && (vmModuleCount > 0 || hostModuleCount > 0 || data.nasInfo.showFlag || data.volcdpInfo.showFlag || data.dbcdpInfo.showFlag || data.awsInfo.showFlag)) {
                $(".dataprotect .nav-tabs>li.m365").show();
                $(".dataprotect .nav-tabs>li.m365").attr("show", "true");
                $(".dataprotect .tab-pane#m365").attr("show", "true");
            }
            //obs
            if (data.obsInfo.showFlag && (vmModuleCount > 0 || hostModuleCount > 0 || data.nasInfo.showFlag || data.volcdpInfo.showFlag || data.dbcdpInfo.showFlag || data.awsInfo.showFlag ||
                data.m365Info.showFlag || data.hadoopInfo.showFlag)) {
                $(".dataprotect .nav-tabs>li.obs").show();
                $(".dataprotect .nav-tabs>li.obs").attr("show", "true");
                $(".dataprotect .tab-pane#obs").attr("show", "true");
            }
            //hadoop
            if (data.hadoopInfo.showFlag && (vmModuleCount > 0 || hostModuleCount > 0 || data.nasInfo.showFlag || data.volcdpInfo.showFlag || data.dbcdpInfo.showFlag || data.awsInfo.showFlag ||
                data.m365Info.showFlag || data.obsInfo.showFlag)) {
                $(".dataprotect .nav-tabs>li.hadoop").show();
                $(".dataprotect .nav-tabs>li.hadoop").attr("show", "true");
                $(".dataprotect .tab-pane#hadoop").attr("show", "true");
            }
            //content显示的情况:授权了就设置show属性
            if (vmModuleCount > 0) {
                $(".dataprotect .tab-pane#vmProtect").attr("show", "true");
            }
            if (hostModuleCount > 0) {
                $(".dataprotect .tab-pane#hostProtect").attr("show", "true");
            }
            if (data.nasInfo.showFlag) {
                $(".dataprotect .tab-pane#nasProtect").attr("show", "true");
            }
            if (data.volcdpInfo.showFlag) {
                $(".dataprotect .tab-pane#realTimeSync").attr("show", "true");
            }
            if (data.dbcdpInfo.showFlag) {
                $(".dataprotect .tab-pane#dbrealTime").attr("show", "true");
            }
            if (data.awsInfo.showFlag) {
                $(".dataprotect .tab-pane#aws").attr("show", "true");
            }
            if (data.m365Info.showFlag) {
                $(".dataprotect .tab-pane#m365").attr("show", "true");
            }
            if (data.hadoopInfo.showFlag) {
                $(".dataprotect .tab-pane#hadoop").attr("show", "true");
            }
            if (data.obsInfo.showFlag) {
                $(".dataprotect .tab-pane#obs").attr("show", "true");
            }
            //li超过五个，最后一个显示更多下拉框
            toShowMore()
            // 获取显示出来的tab 设置第一个和最后一个样式为圆角
            var index = $('.dataprotect .nav-tabs li[show="true"]').length - 1;
            $('.dataprotect .nav-tabs li[show="true"]').eq(0).css("cssText", "border-radius: 0 30px 30px 0 !important;");
            $('.dataprotect .nav-tabs li[show="true"]').eq(index).css("cssText", "border-radius: 30px 0 0 30px !important;").addClass("active");
            $('.dataprotect .tab-content div[show="true"]').eq(0).addClass("active");
            //tab切换动画
            $(".dataprotectdiv .nav-tabs > li").click(function () {
                tabanimite(".dataprotectdiv", $(this));
            });
            $(".storagecenterdiv .nav-tabs > li").click(function () {
                tabanimite(".storagecenterdiv", $(this));
            });
            //初始化tab滑块位置
            tabanimite(".dataprotectdiv", $('.dataprotectdiv .nav-tabs li[show="true"]').eq($('.dataprotectdiv .nav-tabs li[show="true"]').length - 1));
            tabanimite(".storagecenterdiv", $('.storagecenterdiv .nav-tabs li').eq(1));
            //根据授权判断虚拟机标签页显示模块
            if ((data.vminfo.showFlag && data.cloudInfo.showFlag && data.CDMInfo.showFlag) || (!data.vminfo.showFlag && data.cloudInfo.showFlag && data.CDMInfo.showFlag)) {
                //vm、云平台、CDM
                $(".allVMProtect .top").show();
                $(".allVMProtect .one-vm-top").hide();
                $(".allVMProtect .data-bottom").show();
            } else if ((data.vminfo.showFlag && !data.cloudInfo.showFlag && data.CDMInfo.showFlag) || (!data.vminfo.showFlag && !data.cloudInfo.showFlag && data.CDMInfo.showFlag)) {
                // vm、CDM
                $(".allVMProtect .top").hide();
                $(".allVMProtect .one-vm-top").show();
                $(".allVMProtect .data-bottom").show();
            } else if ((data.vminfo.showFlag && data.cloudInfo.showFlag && !data.CDMInfo.showFlag) || (!data.vminfo.showFlag && data.cloudInfo.showFlag && !data.CDMInfo.showFlag)) {
                // vm、云平台
                $(".allVMProtect .top").hide();
                $(".allVMProtect .one-vm-top").show();
                $(".allVMProtect .data-bottom").hide();
                $(".allVMProtect .one-clound-bottom").show();
            } else if ((data.vminfo.showFlag && !data.cloudInfo.showFlag && !data.CDMInfo.showFlag) || (hostModuleCount == 0 && vmModuleCount == 0 && !data.volcdpInfo.showFlag && !data.dbcdpInfo.showFlag && !data.nasInfo.showFlag)) {
                // 只有vm 和什么都没有时显示虚拟机
                $("#vmProtect").addClass("active")
                $(".allVMProtect").hide();
                $(".oneVmProtect").show();
            }
            //根据授权判断主机标签页显示模块
            if (data.fileinfo.showFlag && data.dbinfo.showFlag && data.osinfo.showFlag && data.cdpInfo.showFlag) {
                // 文件、数据库、操作系统、实时
                $("#hostProtect .allVMProtect .top").show();
                $("#hostProtect .allVMProtect .one-vm-top").hide();
            } else if (data.fileinfo.showFlag && !data.dbinfo.showFlag && !data.osinfo.showFlag && !data.cdpInfo.showFlag) {
                // 只有文件
                $("#hostProtect .allHost").hide();
                $("#hostProtect .one-host").show();
                $("#hostProtect .one-host .littleLabel").html(LANG.UI_SETTING_FILE_PROTECT);
                $("#hostProtect .one-host .oneHostBox").removeClass().addClass("oneHostBox fileInfo");
                getPicPosition(-593, -782, -73, -475, -627, -58);
            } else if (!data.fileinfo.showFlag && data.dbinfo.showFlag && !data.osinfo.showFlag && !data.cdpInfo.showFlag) {
                // 只有数据库
                $("#hostProtect .allHost").hide();
                $("#hostProtect .one-host").show();
                $("#hostProtect .one-host .littleLabel").html(LANG.UI_SETTING_DATABASE_PROTECT);
                $("#hostProtect .one-host .oneHostBox").removeClass().addClass("oneHostBox dbInfo");
                getPicPosition(-530, -782, -73, -424, -627, -58);
            } else if (!data.fileinfo.showFlag && !data.dbinfo.showFlag && data.osinfo.showFlag && !data.cdpInfo.showFlag) {
                // 只有操作系统
                $("#hostProtect .allHost").hide();
                $("#hostProtect .one-host").show();
                $("#hostProtect .one-host .littleLabel").html(LANG.UI_HOMEPAGE_OS_PROTECT);
                $("#hostProtect .one-host .oneHostBox").removeClass().addClass("oneHostBox osInfo");
                getPicPosition(-656, -782, -73, -526, -627, -58);
            } else if (!data.fileinfo.showFlag && !data.dbinfo.showFlag && !data.osinfo.showFlag && data.cdpInfo.showFlag) {
                // 只有实时
                $("#hostProtect .allHost").hide();
                $("#hostProtect .one-host").show();
                $("#hostProtect .one-host .littleLabel").html(LANG.UI_HOMEPAGE_CDP_PROTECT);
                $("#hostProtect .one-host .oneHostBox").removeClass().addClass("oneHostBox cdpInfo");
                getPicPosition(-719, -782, -73, -576, -627, -58);
            } else if (hostModuleCount == 2) {
                //主机有两个模块授权
                $("#hostProtect .allHost").hide();
                $("#hostProtect .two-host").show();
                //六种情况
                if (!data.fileinfo.showFlag && !data.dbinfo.showFlag && data.osinfo.showFlag && data.cdpInfo.showFlag) {
                    //只有os和cdp
                    $("#hostProtect .two-host .module1 .littleLabel").html(LANG.UI_HOMEPAGE_OS_PROTECT);
                    $("#hostProtect .two-host .module2 .littleLabel").html(LANG.UI_HOMEPAGE_CDP_PROTECT);
                    $("#hostProtect .two-host .module1").parent().removeClass().addClass("col-md-6 col-sm-6 pl0 littleScreen-pr osInfo");
                    $("#hostProtect .two-host .module2").parent().removeClass().addClass("col-md-6 col-sm-6 pl0 littleScreen-pr cdpInfo");
                } else if (!data.fileinfo.showFlag && data.dbinfo.showFlag && !data.osinfo.showFlag && data.cdpInfo.showFlag) {
                    //只有db和cdp
                    $("#hostProtect .two-host .module1 .littleLabel").html(LANG.UI_SETTING_DATABASE_PROTECT);
                    $("#hostProtect .two-host .module2 .littleLabel").html(LANG.UI_HOMEPAGE_CDP_PROTECT);
                    $("#hostProtect .two-host .module1").parent().removeClass().addClass("col-md-6 col-sm-6 pl0 littleScreen-pr dbInfo");
                    $("#hostProtect .two-host .module2").parent().removeClass().addClass("col-md-6 col-sm-6 pl0 littleScreen-pr cdpInfo");
                } else if (data.fileinfo.showFlag && !data.dbinfo.showFlag && !data.osinfo.showFlag && data.cdpInfo.showFlag) {
                    //只有fs和cdp
                    $("#hostProtect .two-host .module1 .littleLabel").html(LANG.UI_SETTING_FILE_PROTECT);
                    $("#hostProtect .two-host .module2 .littleLabel").html(LANG.UI_HOMEPAGE_CDP_PROTECT);
                    $("#hostProtect .two-host .module1").parent().removeClass().addClass("col-md-6 col-sm-6 pl0 littleScreen-pr fileInfo");
                    $("#hostProtect .two-host .module2").parent().removeClass().addClass("col-md-6 col-sm-6 pl0 littleScreen-pr cdpInfo");
                } else if (data.fileinfo.showFlag && !data.dbinfo.showFlag && data.osinfo.showFlag && !data.cdpInfo.showFlag) {
                    //只有fs和os
                    $("#hostProtect .two-host .module1 .littleLabel").html(LANG.UI_SETTING_FILE_PROTECT);
                    $("#hostProtect .two-host .module2 .littleLabel").html(LANG.UI_HOMEPAGE_OS_PROTECT);
                    $("#hostProtect .two-host .module1").parent().removeClass().addClass("col-md-6 col-sm-6 pl0 littleScreen-pr fileInfo");
                    $("#hostProtect .two-host .module2").parent().removeClass().addClass("col-md-6 col-sm-6 pl0 littleScreen-pr osInfo");
                } else if (data.fileinfo.showFlag && data.dbinfo.showFlag && !data.osinfo.showFlag && !data.cdpInfo.showFlag) {
                    //只有fs和db
                    $("#hostProtect .two-host .module1 .littleLabel").html(LANG.UI_SETTING_FILE_PROTECT);
                    $("#hostProtect .two-host .module2 .littleLabel").html(LANG.UI_SETTING_DATABASE_PROTECT);
                    $("#hostProtect .two-host .module1").parent().removeClass().addClass("col-md-6 col-sm-6 pl0 littleScreen-pr fileInfo");
                    $("#hostProtect .two-host .module2").parent().removeClass().addClass("col-md-6 col-sm-6 pl0 littleScreen-pr dbInfo");
                } else if (!data.fileinfo.showFlag && data.dbinfo.showFlag && data.osinfo.showFlag && !data.cdpInfo.showFlag) {
                    //只有db和os
                    $("#hostProtect .two-host .module1 .littleLabel").html(LANG.UI_SETTING_DATABASE_PROTECT);
                    $("#hostProtect .two-host .module2 .littleLabel").html(LANG.UI_HOMEPAGE_OS_PROTECT);
                    $("#hostProtect .two-host .module1").parent().removeClass().addClass("col-md-6 col-sm-6 pl0 littleScreen-pr dbInfo");
                    $("#hostProtect .two-host .module2").parent().removeClass().addClass("col-md-6 col-sm-6 pl0 littleScreen-pr osInfo");
                }

            } else if (hostModuleCount == 3) {
                //主机有三个模块授权
                $("#hostProtect .allHost").hide();
                $("#hostProtect .three-host").show();
                //有四种情况
                if (!data.fileinfo.showFlag && data.dbinfo.showFlag && data.osinfo.showFlag && data.cdpInfo.showFlag) {
                    //没有文件
                    $("#hostProtect .three-host .module1 .littleLabel").html(LANG.UI_SETTING_DATABASE_PROTECT);
                    $("#hostProtect .three-host .module2 .littleLabel").html(LANG.UI_HOMEPAGE_CDP_PROTECT);
                    $("#hostProtect .three-host .module3 .littleLabel").html(LANG.UI_HOMEPAGE_OS_PROTECT);
                    $("#hostProtect .three-host .module1").parent().removeClass().addClass("col-md-6 col-sm-6 pl0 littleScreen-pr dbInfo");
                    $("#hostProtect .three-host .module2").parent().removeClass().addClass("col-md-6 col-sm-6 pl0 littleScreen-pr cdpInfo");
                    $("#hostProtect .three-host .module3").parent().removeClass().addClass("col-md-12 col-sm-12 pl0 littleScreen-pr osInfo");
                } else if (data.fileinfo.showFlag && !data.dbinfo.showFlag && data.osinfo.showFlag && data.cdpInfo.showFlag) {
                    //没有数据库
                    $("#hostProtect .three-host .module1 .littleLabel").html(LANG.UI_SETTING_FILE_PROTECT);
                    $("#hostProtect .three-host .module2 .littleLabel").html(LANG.UI_HOMEPAGE_CDP_PROTECT);
                    $("#hostProtect .three-host .module3 .littleLabel").html(LANG.UI_HOMEPAGE_OS_PROTECT);
                    $("#hostProtect .three-host .module1").parent().removeClass().addClass("col-md-6 col-sm-6 pl0 littleScreen-pr fileInfo");
                    $("#hostProtect .three-host .module2").parent().removeClass().addClass("col-md-6 col-sm-6 pl0 littleScreen-pr cdpInfo");
                    $("#hostProtect .three-host .module3").parent().removeClass().addClass("col-md-12 col-sm-12 pl0 littleScreen-pr osInfo");
                } else if (data.fileinfo.showFlag && data.dbinfo.showFlag && !data.osinfo.showFlag && data.cdpInfo.showFlag) {
                    //没有操作系统
                    $("#hostProtect .three-host .module1 .littleLabel").html(LANG.UI_SETTING_FILE_PROTECT);
                    $("#hostProtect .three-host .module2 .littleLabel").html(LANG.UI_SETTING_DATABASE_PROTECT);
                    $("#hostProtect .three-host .module3 .littleLabel").html(LANG.UI_HOMEPAGE_CDP_PROTECT);
                    $("#hostProtect .three-host .module1").parent().removeClass().addClass("col-md-6 col-sm-6 pl0 littleScreen-pr fileInfo");
                    $("#hostProtect .three-host .module2").parent().removeClass().addClass("col-md-6 col-sm-6 pl0 littleScreen-pr dbInfo");
                    $("#hostProtect .three-host .module3").parent().removeClass().addClass("col-md-12 col-sm-12 pl0 littleScreen-pr cdpInfo");
                } else if (data.fileinfo.showFlag && data.dbinfo.showFlag && data.osinfo.showFlag && !data.cdpInfo.showFlag) {
                    //没有实时
                    $("#hostProtect .three-host .module1 .littleLabel").html(LANG.UI_SETTING_FILE_PROTECT);
                    $("#hostProtect .three-host .module2 .littleLabel").html(LANG.UI_SETTING_DATABASE_PROTECT);
                    $("#hostProtect .three-host .module3 .littleLabel").html(LANG.UI_HOMEPAGE_OS_PROTECT);
                    $("#hostProtect .three-host .module1").parent().removeClass().addClass("col-md-6 col-sm-6 pl0 littleScreen-pr fileInfo");
                    $("#hostProtect .three-host .module2").parent().removeClass().addClass("col-md-6 col-sm-6 pl0 littleScreen-pr dbInfo");
                    $("#hostProtect .three-host .module3").parent().removeClass().addClass("col-md-12 col-sm-12 pl0 littleScreen-pr osInfo");
                }
            }
            //加载数据
            // getProtectData();
        });

    }
    var toClickLi = function (e) {
        $(".dataprotectdiv .nav-tabs li").removeClass('active');
        $(this).addClass('active');
        $('.more-a').html(e.text());
    }
    //处理不同分辨率的图片位置
    var getPicPosition = function (lgw1, lgw2, lgh, smw1, smw2, smh) { //大图1 2的宽高  小图1  2的宽高
        if (document.body.clientWidth > 1600) {
            $("#hostProtect .one-host .col-md-3:nth-child(2) .eachdata .img1").css("background-position", lgw1 + "px" + " " + lgh + "px");
            $("#hostProtect .one-host .col-md-3:nth-child(3) .eachdata .img2").css("background-position", lgw2 + "px" + " " + lgh + "px");
            $("#hostProtect .one-host .col-md-3:nth-child(4) .eachdata .img3").css("background-position", "-236px -70px");
            $("#hostProtect .one-host .col-md-3:nth-child(5) .eachdata .img4").css("background-position", "-725px -10px");
        } else { //1600以下图片缩小  位置发生改变
            $("#hostProtect .one-host .col-md-3:nth-child(2) .eachdata .img1").css("background-position", smw1 + "px" + " " + smh + "px");
            $("#hostProtect .one-host .col-md-3:nth-child(3) .eachdata .img2").css("background-position", smw2 + "px" + " " + smh + "px");
            $("#hostProtect .one-host .col-md-3:nth-child(4) .eachdata .img3").css("background-position", "-189px -56px");
            $("#hostProtect .one-host .col-md-3:nth-child(5) .eachdata .img4").css("background-position", "-581px -8px");
        }
        window.addEventListener("resize", function () {
            if (document.body.clientWidth > 1600) {
                $("#hostProtect .one-host .col-md-3:nth-child(2) .eachdata .img1").css("background-position", lgw1 + "px" + " " + lgh + "px");
                $("#hostProtect .one-host .col-md-3:nth-child(3) .eachdata .img2").css("background-position", lgw2 + "px" + " " + lgh + "px");
                $("#hostProtect .one-host .col-md-3:nth-child(4) .eachdata .img3").css("background-position", "-236px -70px");
                $("#hostProtect .one-host .col-md-3:nth-child(5) .eachdata .img4").css("background-position", "-725px -10px");
            } else { //1600以下图片缩小  位置发生改变
                $("#hostProtect .one-host .col-md-3:nth-child(2) .eachdata .img1").css("background-position", smw1 + "px" + " " + smh + "px");
                $("#hostProtect .one-host .col-md-3:nth-child(3) .eachdata .img2").css("background-position", smw2 + "px" + " " + smh + "px");
                $("#hostProtect .one-host .col-md-3:nth-child(4) .eachdata .img3").css("background-position", "-189px -56px");
                $("#hostProtect .one-host .col-md-3:nth-child(5) .eachdata .img4").css("background-position", "-581px -8px");
            }
        });
    }
    //处理字体--避免数据过长时 文字超出
    //参数1：类名  参数2：字符串限制的长度  参数3：如果超出限制长度的字体大小 参数4：分辨率
    var initfontSize = function (selectname, limitNum, fontSize, screenSize) {
        if ($(window).width() <= screenSize) {
            var str = $(selectname).html();
            if (str && str.length > limitNum) {
                $(selectname).css("font-size", fontSize + "px");
            }
        }
    }
    //是否显示更多
    var toShowMore = function () {
        if ($('.dataprotectdiv .nav-tabs li[show="true"]').length > 5) {
            var div = '';
            var $list = $('.dataprotectdiv .nav-tabs');
            var $lis = $list.find('li[show="true"]');
            // 隐藏后面几个li元素，只显示五个
            var selectLi = $lis.slice(0, $('.dataprotectdiv .nav-tabs li[show="true"]').length - 5);
            selectLi.attr("show", false).hide();
            // 创建一个下拉框
            var $dropdown = $('<li>', {
                class: 'more-li'
            }).attr("show", true).append($('<a>', {
                class: 'more-a',
                href: '#',
                text: LANG.UI_PUBLIC_MORE
            }), $('<div>', {
                class: 'form-control select-more select-more_en display-none'
            }));
            $dropdown.prependTo($list);
            // 遍历倒数第五个及之后的li元素，并将它们添加到下拉框中
            $('.select-more').html(selectLi).find('li').show();
            var totalHeight = 0;
            $('.select-more').find('li').each(function () {
                // 累加每个li的外边距、边框、内边距和高度
                totalHeight += $(this).outerHeight(true);
            });
            // 设置div的高度为li元素高度之和
            $('.select-more').height(totalHeight);
            selectLi.on('click', function () {
                toClickLi($(this))
            });
            $('.dataSlider').hover(function () { // 当鼠标进入
                var sliderText = $(this).text().trim();
                if (selectLi.filter(function () { // 检查是否有列表项的文本与.dataSlider匹配
                    return $(this).text().trim() === sliderText || sliderText === LANG.UI_PUBLIC_MORE;
                }).length > 0) {
                    $('.select-more').show();
                }
            }, function () { // 当鼠标离开
                $('.select-more').hide();
            });
            $('.more-li').hover(function () {
                $('.select-more').show();
            }, function () { // 当鼠标离开
                $('.select-more').hide();
            });
            $('.select-more').hover(function () {
                $('.select-more').show();
            }, function () { // 当鼠标离开
                $('.select-more').hide();
            });
            $('.more-a').click(function (event) {
                event.stopPropagation(); // 阻止事件冒泡
                event.preventDefault(); // 阻止默认行为
            });
        }
    }
    // 数据保护
    var getProtectData = function () {
        $.post(CONF.AJAXPATH, {
            m: CONF.M.HOMEPAGE,
            f: 'getProtectData',
            p: {}
        }, function (d) {
            var data = JSON.parse(d);
            //虚拟机
            $(".vmTotalNum").html(data.vmInfo.totalNum);
            initfontSize(".vmTotalNum", 4, 14, 1920);
            $(".vmProtectedNum").html(data.vmInfo.protectedNum);
            initfontSize(".vmProtectedNum", 4, 14, 1920);
            $(".vmBakData").html(data.vmInfo.backupData.des);
            initfontSize(".vmBakData", 8, 14, 1920);
            initfontSize(".vmBakData", 6, 14, 1440);
            $(".vmOnline").html(data.vmInfo.onlineNum);
            $(".vmOffline").html(data.vmInfo.offlineNum);
            //CDM
            $(".laboratoryNum").html(data.vmInfo.CDMInfo.laboratoryNum);
            $(".verifyNum").html(data.vmInfo.CDMInfo.verifyNum);
            $(".verifyingNum").html(data.vmInfo.CDMInfo.verifyingNum);
            $(".verifyTimes").html(data.vmInfo.CDMInfo.verifyTimes);
            $(".falieTimes").html(data.vmInfo.CDMInfo.falieTimes);
            // 云平台
            $(".cloudOnline").html(data.vmInfo.cloudInfo.onlineNum);
            $(".cloudOffline").html(data.vmInfo.cloudInfo.offlineNum);
            // fs
            $(".fileInfo .protectedNum").html(data.hostInfo.fileInfo.protectedNum);
            $(".fileInfo .backupDataSize").html(data.hostInfo.fileInfo.backupDataSize.des);
            $(".fileInfo .taskNum").html(data.hostInfo.fileInfo.taskNum);
            $(".fileInfo .backupPoint").html(data.hostInfo.fileInfo.backupPointNum);
            //db
            $(".dbInfo .protectedNum").html(data.hostInfo.DBInfo.protectedNum);
            $(".dbInfo .backupDataSize").html(data.hostInfo.DBInfo.backupDataSize.des);
            $(".dbInfo .taskNum").html(data.hostInfo.DBInfo.taskNum);
            $(".dbInfo .backupPoint").html(data.hostInfo.DBInfo.backupPointNum);
            //os
            $(".osInfo .protectedNum").html(data.hostInfo.OSInfo.protectedNum);
            $(".osInfo .backupDataSize").html(data.hostInfo.OSInfo.backupDataSize.des);
            $(".osInfo .taskNum").html(data.hostInfo.OSInfo.taskNum);
            $(".osInfo .backupPoint").html(data.hostInfo.OSInfo.backupPointNum);
            //cdp
            $(".cdpInfo .protectedNum").html(data.hostInfo.CDPInfo.protectedNum);
            $(".cdpInfo .backupDataSize").html(data.hostInfo.CDPInfo.backupDataSize.des);
            $(".cdpInfo .taskNum").html(data.hostInfo.CDPInfo.taskNum);
            $(".cdpInfo .backupPoint").html(data.hostInfo.CDPInfo.backupPointNum);
            initfontSize(".fileInfo .backupDataSize", 6, 14, 1920);
            initfontSize(".dbInfo .backupDataSize", 6, 14, 1920);
            initfontSize(".fileInfo .backupDataSize", 5, 12, 1440);
            initfontSize(".dbInfo .backupDataSize", 5, 12, 1440);
            //dbcdp
            $(".dbcdpInfo .protectedNum").html(data.hostInfo.DBCDPInfo.protectedNum);
            $(".dbcdpInfo .taskNum").html(data.hostInfo.DBCDPInfo.taskNum);
            $(".dbcdpInfo .taskRunNum").html(data.hostInfo.DBCDPInfo.taskRunNum);
            $(".dbcdpInfo .productOnline").html(data.hostInfo.DBCDPInfo.productHost.online);
            $(".dbcdpInfo .productOffline").html(data.hostInfo.DBCDPInfo.productHost.offline);
            $(".dbcdpInfo .bakOnline").html(data.hostInfo.DBCDPInfo.standbyHost.online);
            $(".dbcdpInfo .bakOffline").html(data.hostInfo.DBCDPInfo.standbyHost.offline);
            // NAS
            $(".nasProtectedNum").html(data.nasInfo.protectedNum);
            $(".nasBackupDataSize").html(data.nasInfo.backupDataSize.des);
            $(".nastaskNum").html(data.nasInfo.taskNum);
            $(".nasbackupPoint").html(data.nasInfo.backupPointNum);
            //公有云
            $(".awsInfo .cloud .online").html(data.awsInfo.cloudInfo.online);
            $(".awsInfo .cloud .awsoffline").html(data.awsInfo.cloudInfo.offline);
            $(".awsInfo .example .totalNum").html(data.awsInfo.exampleInfo.totalNum);
            $(".awsInfo .example .protectNum").html(data.awsInfo.exampleInfo.protectNum);
            $(".awsInfo .aws-data .taskNum").html(data.awsInfo.taskNum);
            $(".awsInfo .aws-data .pointNum").html(data.awsInfo.backupPointNum);
            $(".awsInfo .aws-data .dataNum").html(data.awsInfo.backupDataSize.des);
            //m365
            $("#m365 .m365online").html(data.m365Info.onlineNum);
            $("#m365 .m365offline").html(data.m365Info.offlineNum);
            $("#m365 .m365protectedNum").html(data.m365Info.protectedNum);
            $("#m365 .m365taskNum").html(data.m365Info.taskNum);
            $("#m365 .m365backupPoint").html(data.m365Info.backupPointNum);
            $("#m365 .m365BackupDataSize").html(data.m365Info.backupDataSize.des);
            //obs
            $("#obs .obsonline").html(data.obsInfo.onlineNum);
            $("#obs .obsoffline").html(data.obsInfo.offlineNum);
            $("#obs .obsprotectedNum").html(data.obsInfo.protectedNum);
            $("#obs .obstaskNum").html(data.obsInfo.taskNum);
            $("#obs .obsbackupPoint").html(data.obsInfo.backupPointNum);
            $("#obs .obsBackupDataSize").html(data.obsInfo.backupDataSize.des);
            //hadoop
            $("#hadoop .hadooponline").html(data.hadoopInfo.onlineNum);
            $("#hadoop .hadoopoffline").html(data.hadoopInfo.offlineNum);
            $("#hadoop .hadoopprotectedNum").html(data.hadoopInfo.protectedNum);
            $("#hadoop .hadooptaskNum").html(data.hadoopInfo.taskNum);
            $("#hadoop .hadoopbackupPoint").html(data.hadoopInfo.backupPointNum);
            $("#hadoop .hadoopBackupDataSize").html(data.hadoopInfo.backupDataSize.des);
            initHostChart(data); //数据获取完后加载图表
        });
    }
    var initHostChart = function (data) {
        //主机饼图数据
        var hostOnlineNum = data.hostInfo.hostOnlineNum;
        var hostOfflineNum = data.hostInfo.hostOfflineNum;
        var hostTotalNum = data.hostInfo.hostTotalNum;
        // nas饼图数据
        var nasonlineNum = data.nasInfo.onlineNum;
        var nasofflineNum = data.nasInfo.offlineNum;
        //主机环形图
        var hostChart = echarts.init(document.getElementById('hostcard'));
        // 实时容灾主机环形图
        var cdphostChart = echarts.init(document.getElementById('cdphostcard'));
        option = {
            title: [{
                text: `{val|${hostTotalNum}}\n{name|${LANG.UI_PUBLIC_HOST}}`,
                top: '28%',
                left: 'center',
                textStyle: {
                    rich: {
                        name: {
                            fontSize: 12,
                            color: '#3D3D46',
                            padding: [5, 0]
                        },
                        val: {
                            fontSize: 24,
                            fontWeight: 'bold',
                            color: '#20C99A'
                        }
                    }
                }
            }, ],
            tooltip: {
                trigger: "item",
                formatter: "{a} <br/>{b}: {c} ({d}%)",
                position: function (p) {
                    //其中p为当前鼠标的位置
                    return [p[0] + 10, p[1] - 10];
                }
            },
            legend: {
                top: "80%",
                itemWidth: 10,
                itemHeight: 10,
                itemGap: 40, // 设置图例间距
                //自定义图例后面的数字样式
                formatter: function (name) {
                    let target;
                    if (LANG.UI_VOL_CDP_JOB_DETAILS_ONLINE === name) {
                        target = hostOnlineNum;
                    } else {
                        target = hostOfflineNum;
                    }
                    let arr = `{b|${name}}` + `{a|${target}}`
                    return arr;
                },
                textStyle: {
                    rich: {
                        a: {
                            fontSize: 20,
                            color: "#3D3D46",
                            padding: 10,
                            fontWeight: 700
                        },
                        b: {
                            color: "#76767B",
                            fontSize: "12",
                        }
                    },
                }
            },
            series: [{
                name: LANG.UI_PUBLIC_HOST,
                type: "pie",
                center: ["50%", "40%"],
                radius: ["45%", "60%"],
                color: ["#20C899", "#9AAAC1"],
                label: {
                    show: false
                },
                labelLine: {
                    show: false
                },
                data: [{
                    value: hostOnlineNum,
                    name: LANG.UI_VOL_CDP_JOB_DETAILS_ONLINE
                },
                    {
                        value: hostOfflineNum,
                        name: LANG.UI_VOL_CDP_JOB_DETAILS_OFF_LINE
                    },
                ]
            },
                {
                    name: 'bg-host',
                    type: 'pie',
                    silent: true,
                    center: ["50%", "40%"],
                    radius: [0, '45%'],
                    hoverAnimation: false,
                    labelLine: {
                        normal: {
                            show: false
                        }
                    },
                    data: [{
                        value: 0,
                        name: ''
                    }],
                    color: {
                        type: 'linear',
                        x: 0,
                        y: 0,
                        x2: 0,
                        y2: 1,
                        colorStops: [{
                            offset: 0,
                            color: 'white' // 0% 处的颜色
                        }, {
                            offset: 1,
                            color: '#E7FBF2' // 100% 处的颜色
                        }],
                        globalCoord: false // 缺省为 false
                    }
                },
            ]
        };
        hostChart.setOption(option);
        cdphostChart.setOption(option);
        //nas环形图
        var nasChart = echarts.init(document.getElementById('nascard'));
        option = {
            title: [{
                text: `{val|${nasonlineNum + nasofflineNum}}\n{name|${LANG.UI_HOMEPAGE_NAS_DEVICE}}`,
                top: '28%',
                left: 'center',
                textStyle: {
                    rich: {
                        name: {
                            fontSize: 12,
                            color: '#3D3D46',
                            padding: [5, 5]
                        },
                        val: {
                            fontSize: 24,
                            fontWeight: 'bold',
                            color: '#10AEFF'
                        }
                    }
                }
            }, ],
            tooltip: {
                trigger: "item",
                formatter: function (params) {
                    var tips = "";
                    if (CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw") {
                        tips = params.seriesName + "<br/>" + params.data.name + LANG.UI_HOMEPAGE_DEVICE_NUM + ": " + params.data.value
                    } else {
                        tips = params.seriesName + "<br/>" + params.data.name + ": " + params.data.value
                    }
                    return tips;
                },
                position: function (p) {
                    //其中p为当前鼠标的位置
                    return [p[0] + 10, p[1] - 10];
                }
            },
            legend: {
                top: "80%",
                itemWidth: 10,
                itemHeight: 10,
                itemGap: 40, // 设置图例间距
                //自定义图例后面的数字样式
                formatter: function (name) {
                    let target;
                    if (LANG.UI_VOL_CDP_JOB_DETAILS_ONLINE === name) {
                        target = nasonlineNum;
                    } else {
                        target = nasofflineNum;
                    }
                    let arr = `{b|${name}}` + `{a|${target}}`
                    return arr;
                },
                textStyle: {
                    rich: {
                        a: {
                            fontSize: 20,
                            color: "#3D3D46",
                            padding: 10,
                            fontWeight: 700
                        },
                        b: {
                            color: "#76767B",
                            fontSize: "12",
                        }
                    },
                }
            },
            series: [{
                name: LANG.UI_HOMEPAGE_NAS_DEVICE,
                type: "pie",
                center: ["50%", "40%"],
                radius: ["45%", "60%"],
                color: ["#0EAAFF", "#9AAAC1", ],
                label: {
                    show: false
                },
                labelLine: {
                    show: false
                },
                data: [{
                    value: nasonlineNum,
                    name: LANG.UI_VOL_CDP_JOB_DETAILS_ONLINE
                },
                    {
                        value: nasofflineNum,
                        name: LANG.UI_VOL_CDP_JOB_DETAILS_OFF_LINE
                    },
                ]
            },
                {
                    name: 'bg-nas',
                    type: 'pie',
                    silent: true,
                    center: ["50%", "40%"],
                    radius: [0, '45%'],
                    hoverAnimation: false,
                    labelLine: {
                        normal: {
                            show: false
                        }
                    },
                    data: [{
                        value: 0,
                        name: ''
                    }],
                    color: {
                        type: 'linear',
                        x: 0,
                        y: 0,
                        x2: 0,
                        y2: 1,
                        colorStops: [{
                            offset: 0,
                            color: 'white' // 0% 处的颜色
                        }, {
                            offset: 1,
                            color: '#DBF0FB' // 100% 处的颜色
                        }],
                        globalCoord: false // 缺省为 false
                    }
                },
            ]
        };
        nasChart.setOption(option);
    }
    //存储统计
    var getSystemStorageData = function () {
        $.post(CONF.AJAXPATH, {
            m: CONF.M.HOMEPAGE,
            f: 'getSystemStorageData',
            p: {}
        }, function (d) {
            var data = JSON.parse(d);
            //备份
            var sum = data.allStorageInfo.usedCapacity.size + data.allStorageInfo.remainingCapacity.size;
            if(sum == 0) {
                var usedPercent = 0 + "%";
                var remainPercent = 0 + "%";
            }else {
                var usedPercent = Math.round(data.allStorageInfo.usedCapacity.size / (sum) * 10000) / 100 + "%";
                var remainPercent = Math.round(data.allStorageInfo.remainingCapacity.size / (sum) * 10000) / 100 + "%";
                 if (data.allStorageInfo.remainingCapacity.size / (sum) < 0.05) { //剩余容量不足5%时,提示容量不足
                    $("#storageTips").show();
                } else {
                    $("#storageTips").hide();
                }
            }
            $(".storageNum").html(data.allStorageInfo.storageNum + LANG.UI_PUBLIC_NUM);
            $(".storageCap").html(data.allStorageInfo.totalCapacity.des);
            initfontSize(".storageCap", 8, 14, 1920);
            $(".usedPercent").html(usedPercent);
            $(".usedNum").html(data.allStorageInfo.usedCapacity.des);
            $(".remainPercent").html(remainPercent);
            $(".remianNum").html(data.allStorageInfo.remainingCapacity.des);
            // // 副本改成了副本+归档
            // $(".copyStorageNum").html(data.copyAndArchive.bakStorageNum + LANG.UI_PUBLIC_NUM);
            // $(".copyStorageCap").html(data.copyAndArchive.usedCapacity.des);
            // initfontSize(".copyStorageCap", 8, 12, 1920);
            initStorageChart(data);
        });


    }
    var initStorageChart = function (data) {
        var usedCapacity = data.allStorageInfo.usedCapacity.size;
        var remainingCapacity = data.allStorageInfo.remainingCapacity.size;
        var bakStorageCapacity = data.allStorageInfo.remainingCapacity.des;
        var useddes = data.allStorageInfo.usedCapacity.des;
        var remaindes = data.allStorageInfo.remainingCapacity.des;
        var percent = (usedCapacity / data.allStorageInfo.totalCapacity.size) * 100;
        $('.progress-line-used').css({
            'width': percent + '%'
        });
        $('.storage-value').empty().html(data.allStorageInfo.remainingCapacity.des);
        $('.free-size .value').empty().html(data.allStorageInfo.usedCapacity.des);
        $('.total-size .value').empty().html(data.allStorageInfo.totalCapacity.des);
    }
    var getNetworkChartData = function () {
        if (0 == $('#networkTrafficChart').size()) {
            clearTimeout(timerTask.networkChartData);
            return;
        } else {
            clearTimeout(timerTask.networkChartData);
        }
        var updateInterval = 2000;
        var p = {}
        if (initNetworkFlag) {
            p.count = 100;
        } else {
            p.count = 1;
        }

        p.node_uuid = $("#node_uuid").val();
        p = JSON.stringify(p);
        $.post(CONF.AJAXPATH, {
            m: CONF.M.HOMEPAGE,
            f: 'getNetworkChartData',
            p: p
        }, function (d) {
            var networkdata = JSON.parse(d);
            //倒转后的新数组
            networkdata = $.map(networkdata, function (item, index) { // map方法匿名函数传的值v是值、i是索引。
                return networkdata[networkdata.length - 1 - index];
            });
            initNetWorkChart(networkdata)
        }).complete(function () {
            timerTask.networkChartData = setTimeout(function () {
                getNetworkChartData()
            }, updateInterval);
        });
    }
    var initNetWorkChart = function (data) {
        if (!initNetworkFlag) {
            timedata.shift();
            netOutdata.shift();
            netIndata.shift();
            timedata.push(data[0].time);
            netOutdata.push(data[0].network.transmit);
            netIndata.push(data[0].network.receive);

        } else {
            initNetworkFlag = false;
            timedata = [];
            netOutdata = [];
            netIndata = [];
            for (var i = 0; i < data.length; i++) {
                timedata.push(data[i].time);
                netOutdata.push(data[i].network.transmit);
                netIndata.push(data[i].network.receive);
            }
        }
        //网络流量折线
        var polyLineChart = echarts.init(document.getElementById('networkTrafficChart'));
        option = {
            title: {
                text: LANG.UI_SYSTEM_MONITOR_NET_PERCENTAGE,
                textStyle: { // 标题样式
                    color: "#3D3D46",
                    fontSize: "16",
                    fontWeight: 'normal',
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
                        if (pvalue >= 1024) {
                            pvalue = Math.round(pvalue * 10 / 1024) / 10 + "MB/s";
                        } else {
                            pvalue = pvalue + "KB/s";
                        }
                        relVal += '<br/>' + ' <span style="display:inline-block;margin-right:5px;width:10px;height:10px;border-radius: 50%!important;background-color:' + params[i].color + ';"></span>' + '  ' + params[i].seriesName + '  ' + pvalue;
                    }
                    return relVal;
                }
            },
            legend: {
                icon: "circle", //形状  类型包括 circle，rect,line，roundRect，triangle，diamond，pin，arrow，none
                top: "0%",
                right: "0%",
                itemWidth: 10, // 设置宽度
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

            xAxis: [{
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
                splitLine: { //分割线
                    show: true,
                    lineStyle: {
                        color: "#E6E9F4"
                    }
                }
            },
                {
                    axisPointer: {
                        show: false
                    },
                    axisLine: {
                        show: false
                    },
                    //   axisLine: {
                    // 	lineStyle: {
                    // 	  color: "#E6E9F4"
                    // 	}
                    //   },
                    position: "bottom",
                    offset: 20
                },
            ],

            yAxis: [{
                type: "value",
                splitNumber: 3,
                axisTick: {
                    show: false
                }, //y轴刻度
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
                    formatter: function (value, index) {
                        if (value >= 1024) {
                            return Math.round(value * 10 / 1024) / 10 + "MB/s";
                        } else {
                            return value + "KB/s";
                        }
                    }
                },
                splitLine: { //不显示分割线
                    show: false,
                }
            }],
            series: [{
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
                        color: new echarts.graphic.LinearGradient(0, 0, 0, 1,
                            [{
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
                                [{
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
    var getUnit = function (value) {
        if (value) {
            if (value >= 1024) {
                return Math.round(value * 10 / 1024) / 10 + "MB/s";
            } else {
                return value + "KB/s";
            }
        } else {
            return 0 + "KB/s";
        }

    }
    var getSystemMonitorData = function () {
        clearTimeout(timerTask.getSystemMonitorData);
        var updateInterval = 2000;
        var p = {}
        p.node_uuid = $("#node_uuid").val();
        p = JSON.stringify(p);
        $.post(CONF.AJAXPATH, {
            m: CONF.M.HOMEPAGE,
            f: 'getSystemMonitorData',
            p: p
        }, function (d) {
            var data = JSON.parse(d);
            $(".bpsread").html(getUnit(data.bpsReadSpeed));
            $(".bpswrite").html(getUnit(data.bpsWriteSpeed));
            $(".iopsread").html(data.iopsReadSpeed + data.iopsReadUnit);
            $(".iopswrite").html(data.iopsWriteSpeed + data.iopsWriteUnit);
            initCPUMemerryChart(data);
        }).complete(function () {
            timerTask.getSystemMonitorData = setTimeout(function () {
                getSystemMonitorData();
            }, updateInterval);
        });
    }

    var initCPUMemerryChart = function (data) {
        var cpuUsedRate = data.cpuUsedRate;
        var cpuRemainRate = 100 - cpuUsedRate;
        var memoryUsedRate = data.memoryUsedRate;
        var memoryRemainRate = 100 - memoryUsedRate;
        var cpuUsedPercent = Math.round(cpuUsedRate) + "%";
        var memoryUsedPercent = Math.round(memoryUsedRate) + "%";

        // 圆形滑块位置计算
        var total = 100; // 总比例
        var sliderPosition = 180; // 初始滑块位置（角度，单位为度）
        var sliderRadius = 105.5 * 0.68; // 环形图半径
        var centerX = 105.5; // 环形图中心X坐标
        var centerY = 155 * 0.75; // 环形图中心Y坐标

        // 计算滑块位置对应的比例
        function calculateProportion(angle) {
            return (angle / 360) * total;
        }

        // cpu
        option = {
            title: [{
                text: `{val|${cpuUsedPercent}}\n{name|CPU使用率}`,
                top: '48%',
                left: 'center',
                textStyle: {
                    rich: {
                        val: {
                            fontSize: 24,
                            fontWeight: 500,
                            color: '#2A87C8'
                        },
                        name: {
                            fontSize: 12,
                            fontWeight: 400,
                            color: '#999999'
                        }
                    }
                }
            }, ],
            tooltip: {
                show: false
            },
            legend: {
                show: false,
            },
            // graphic: [{
            //     type: 'circle',
            //     shape: {
            //         cx: centerX + sliderRadius * Math.cos(sliderPosition * Math.PI / 180),
            //         cy: centerY + sliderRadius * Math.sin(sliderPosition * Math.PI / 180),
            //         r: 10
            //     },
            //     style: {
            //         fill: 'red',
            //         stroke: 'white',
            //         lineWidth: 2 // 边框宽度
            //     },
            //     draggable: false
            // }],
            series: [{
                name: LANG.UI_DATACENTER_CPU_USED,
                type: "pie",
                hoverAnimation: false, // 鼠标移入变大
                radius: ["78%", "98%"],
                center: ['50%', '75%'], // 饼图中心位置设置为容器的中心点
                startAngle: 180,
                endAngle: 360,
                color: [
                    "#2A87C8",
                    "#D4E7F4",
                ],
                label: {
                    show: false
                },
                labelLine: {
                    show: false
                },
                data: [{
                    value: cpuUsedRate,
                    name: LANG.UI_SETTING_USED_NUM
                },
                    {
                        value: cpuRemainRate,
                        name: LANG.UI_VISUAL_STORAGE_UNUSED
                    },
                ]
            }]
        };

        cpuChart.setOption(option);
        // 内存
        option = {
            title: [{
                text: `{val|${memoryUsedPercent}}\n{name|内存使用率}`,
                top: '48%',
                left: 'center',
                textStyle: {
                    rich: {
                        val: {
                            fontSize: 24,
                            fontWeight: 500,
                            color: '#51CBFF'
                        },
                        name: {
                            fontSize: 12,
                            fontWeight: 400,
                            color: '#999999'
                        }
                    }
                }
            }],
            tooltip: {
                show: false,
            },
            legend: {
                show: false,
            },
            series: [{
                name: LANG.UI_PUBLIC_MEMORY_RATE,
                type: "pie",
                hoverAnimation: false, // 鼠标移入变大
                center: ['50%', '75%'], // 饼图中心位置设置为容器的中心点
                radius: ["78%", "98%"],
                color: [
                    "#51CBFF",
                    "#DCF5FF",
                ],
                startAngle: 180,
                endAngle: 360,
                label: {
                    show: false
                },
                labelLine: {
                    show: false
                },
                data: [{
                    value: memoryUsedRate,
                    name: LANG.UI_SETTING_USED_NUM
                },
                    {
                        value: memoryRemainRate,
                        name: LANG.UI_VISUAL_STORAGE_UNUSED
                    },
                ]
            }]
        };

        // 3. 配置项和数据给我们的实例化对象
        memeryChart.setOption(option);
    }
    //初始化节点信息
    var initNodeUUID = function () {
        $.post(CONF.AJAXPATH, {
            m: CONF.M.SYSTEMMONITOR,
            f: 'getNodeUUid',
            p: {}
        }, function (d) {
            var data = JSON.parse(d);
            var nodeselect = $('#node_uuid');
            nodeselect.empty();
            for (var i = 0; i < data.length; i++) {
                var option = $("<option>").text(data[i].name).val(data[i].node_uuid);
                nodeselect.append(option);
            }
            //得到cpu、内存使用率、bps、iops读写速度
            getSystemMonitorData();
            //得到网络流量图表数据
            // getNetworkChartData();
        });

    }

    //页面一开始获取不到隐藏的标签页下的dom宽度,切换时重新设置宽度
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        $("#hostcard").width($("#hostcard").parent().width());
        $("#nascard").width($("#nascard").parent().width());
        $("#copyChart").width($("#copyChart").parent().width());
        $("#capacityCircle").width($("#capacityCircle").parent().width());
        $("#cdphostcard").width($("#cdphostcard").parent().width());
        if (e.target.id == "nasTab" || e.target.id == "hostTab" ||
            e.target.id == "copyTab" || e.target.id == "capacityCircleTab" || e.target.id == "rtsyncTab") {
            var allChart = getAllChart();
            for (var i = 0; i < allChart.length; i++) {
                allChart[i].resize();
            }
        }
    });
    //浏览器缩放的时候，图表也等比例缩放
    var intChartSize = function () {
        window.addEventListener("resize", function () {
            if ($('.datacenter-content').length == 0) {
                return;
            }
            $("#hostcard").width($("#hostcard").parent().width());
            $("#nascard").width($("#nascard").parent().width());
            $("#copyChart").width($("#copyChart").parent().width());
            $("#capacityCircle").width($("#capacityCircle").parent().width());
            $("#cdphostcard").width($("#cdphostcard").parent().width());
            var allChart = getAllChart();
            for (var i = 0; i < allChart.length; i++) {
                allChart[i].resize();
            }
        });

    }
    var getAllChart = function () {
        var allChart = [];
        var a = barChart;
        var b = pieChartReport;
        var c = lineChart;
        var d = pieChartDevice;
        var e = pieChart;
        var g = memeryChart;
        var h = cpuChart;
        allChart = [a, b, c, d, e, g, h];
        return allChart;
    }

    //更新告警信息
    var updateAlarmTips = function () {
        $.post(CONF.AJAXPATH, {
            m: CONF.M.ALARM,
            f: 'getSurveyNoticeInfo',
            p: JSON.stringify({
                no_surebackup_flag:1
            })
        }, function (d) {
            setAlarmInfo(d);
        });
    }
    /**
     * 设置顶部告警ICON的样式
     * 无告警:	label-info
     * 有警告告警:	label-warning
     * 有错误告警:	label-danger
     */
    var setAlarmLabelIconClour = function (id, data) {
        var clourSytel = "label-info";
        if (data.warn > 0) {
            clourSytel = "label-warning";
        }
        if (data.error > 0) {
            clourSytel = "label-danger";
        }
        $(id).find('.label-icon').removeClass().addClass("label label-sm label-icon " + clourSytel);
    }
    //设置告警信息
    var setAlarmInfo = function (d) {
        var d = JSON.parse(d);
        $('.badgemark').remove();
        //初始化告警提示
        $('#alarmtask').html(d.alarm.task.error + d.alarm.task.warn);
        $('#alarmsystem').html(d.alarm.system.error + d.alarm.system.warn);

        var total = d.alarm.task.error + d.alarm.task.warn + d.alarm.system.error + d.alarm.system.warn;
        var errorTotal = d.alarm.task.error + d.alarm.system.error;
        var badgeType = "badge-warning";
        var tips = "";
        if (errorTotal > 0) {
            badgeType = "badge-danger";
        }
        if (total > 0) {
            var tips = '<span class="badge ' + badgeType + ' badgemark">' + total + '</span>';
        }
        $('#alarmtotal').find('span').remove();
        $('#alarmtotal').append(tips);

        setAlarmLabelIconClour(".alarmhreftask", d.alarm.task);
        setAlarmLabelIconClour(".alarmhrefsystem", d.alarm.system);


        //初始化任务提示
        $('#topcurrenttask').html(d.task.current);
        $('#tophistorytask').html(d.task.history);
        $('#topverifycurtask').html(d.task.verifycurrent);
        $('#topverifyhistask').html(d.task.verifyhistory);

        //初始化菜单提示
        var storage_manager = $('.page-sidebar-menu').find('a[name=storage_manager]');
        var authorization_module = $('.page-sidebar-menu').find('a[name=authorization_module]');
        var resource_manager = $('.page-sidebar-menu').find('a[name=resmanagement]');
        var system_manager = $('.page-sidebar-menu').find('a[name=sysmanagement]');

        if (d.storage) {
            //管理备份存储
            var tips = '<span class="badge badge-warning badgemark">' + d.storage + '</span>';
            storage_manager.append(tips);
            //父级-系统管理
            resource_manager.append(tips);
        }
        if (d.lisence) {
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
    var setAlarmLabelIconClour = function (id, data) {
        var clourSytel = "label-info";
        if (data.warn > 0) {
            clourSytel = "label-warning";
        }
        if (data.error > 0) {
            clourSytel = "label-danger";
        }
        $(id).find('.label-icon').removeClass().addClass("label label-sm label-icon " + clourSytel);
    }
    //密码即将过期提示
    var initLoginHistory = function () {
        $.post(CONF.AJAXPATH, {
            m: CONF.M.USER,
            f: 'getLoginHistory'
        }, function (d) {
            var data = JSON.parse(d);
            if (data.tips != "") {
                UIToastr.showWarning(LANG.UI_TENANT_HOME_PASSWORD_EXPIRE, data.tips);
            }
        });
    }
    //初始化是否为租户内部,并设置任务窗口事件
    var initUserType = function () {
        $.post(CONF.AJAXPATH, {
            m: CONF.M.USER,
            f: 'getUserExtendInfo',
            p: {}
        }, function (d) {
            var data = JSON.parse(d);
            if (data.tenantuuid == "" && data.useruuid == "a508b813-19c7-eb4e-d6fa-bb61b25a4de9") {
                addManagerListener();
            } else if (data.tenantuuid == "") {
                addOperatorListener();
            }
        });

        $('.taskhrefcurrent').off().on('click', function () {
            LOCATION('./content/platform/jobs/jobs.php', 'task');
        });
        $('.taskhrefhistory').off().on('click', function () {
            LOCATION('./content/platform/jobs/jobs.php?tab=1', 'task');
        });
        $('.verifytaskhrefcurrent').off().on('click', function () {
            LOCATION('./content/platform/jobs/verify_jobs.php','verification_job');
        });
        $('.verifytaskhrefhistory').off().on('click', function () {
            LOCATION('./content/platform/jobs/verify_jobs.php?tab=1', 'verification_job');
        });
        $('.alarmhreftask').off().on('click', function () {
            LOCATION('./content/platform/alarm/alarm.php', 'alarm');
        });
        $('.alarmhrefsystem').off().on('click', function () {
            LOCATION('./content/platform/alarm/alarm.php?tab=1', 'alarm');
        });

    }
    //添加管理员事件
    var addManagerListener = function () {
        $('#moretaskinfo').on('click', function () {
            CTLHORMENU('bakandrec');
            var url = './content/platform/jobs/history_job.php';
            var urlMark = '?history_job';
            if ($('#currenttaskli').hasClass("active")) {
                url = './content/platform/jobs/jobs.php';
                urlMark = '?current_job';
            }
            LOCATION(url);
            History.pushState({
                url: url
            }, "", urlMark);
        });
        $('.currentjob').off().on('click', function () {
            CTLHORMENU('bakandrec');
        })
    }
    //获取服务器时间
    var initSystime = function () {
        $('.page-header.navbar .top-menu .navbar-nav > li.dropdown-time').show();
    }
    //添加操作员事件
    var addOperatorListener = function () {
        $('#moretaskinfo').on('click', function () {
            CTLHORMENU('bakandrec');
            var url = './content/platform/jobs/history_job.php';
            var urlMark = '?history_job';
            if ($('#currenttaskli').hasClass("active")) {
                url = './content/platform/jobs/jobs.php';
                urlMark = '?current_job';
            }
            LOCATION(url);
            History.pushState({
                url: url
            }, "", urlMark);
        });
        $('.currentjob').off().on('click', function () {
            CTLHORMENU('bakandrec');
        })
    }

    // 初始化客户端信息
    var getClientInfo = function () {
        var type = $('#bar_select').val();
        isLoading[1] = false;
        pAjaxRequest({
            type: type
        }, '/api/v1/industry/client_summary', 'GET', function (res) {
            isLoading[1] = true;
            initClientBar(res.data, type);
        })
    }

    var initClientBar = function (data, type) {
        var lebel = ``;
        var label2 = ``;
        var plan_data = [];
        switch (type) {
            case 'year':
                lebel = LANG.UI_PLATFORM_INDUSTRY_REPORT_VERIFY_COUNT_YEAR;
                label2 = LANG.UI_PLATFORM_INDUSTRY_REPORT_PLAN_VERIFY_COUNT_YEAR;
                break;
            case 'season':
                lebel = LANG.UI_PLATFORM_INDUSTRY_REPORT_VERIFY_COUNT_SEASON;
                label2 = LANG.UI_PLATFORM_INDUSTRY_REPORT_PLAN_VERIFY_COUNT_SEASON;
                break;
            case 'week':
                lebel = LANG.UI_PLATFORM_INDUSTRY_REPORT_VERIFY_COUNT_WEEK;
                label2 = LANG.UI_PLATFORM_INDUSTRY_REPORT_PLAN_VERIFY_COUNT_WEEK;
                break;
            default:
                break;
        }
        var option = {
            tooltip: {
                trigger: 'axis',
                className: 'echarts-tooltip',
                axisPointer: {
                    type: 'shadow'
                }
            },
            legend: {
                icon: "rect", //形状  类型包括 circle，rect,line，roundRect，triangle，diamond，pin，arrow，none
                bottom: "0%",
                left: "center",
                itemWidth: 12, // 设置宽度
                itemHeight: 4, // 设置高度
                selectedMode: false, // 禁用图例的点击切换功能
                textStyle: {
                    color: "#666666",
                    fontSize: 14
                }
            },
            grid: {
                left: '0%',
                right: '4%',
                bottom: '10%',
                containLabel: true
            },
            xAxis: {
                type: 'value',
                minInterval: 1,
                axisLine: {
                    lineStyle: {
                        color: '#EBEBEB' // 可以是颜色值，支持 RGB、RGBA、十六进制等格式
                    },
                    show: true // 显示 x 轴线
                },
                axisLabel: {
                    color: '#999999', // 文字颜色
                    fontSize: 14, // 文字大小
                    fontWeight: 500 // 字体粗细
                },
                boundaryGap: [0, 1]
            },
            yAxis: {
                type: 'category',
                axisLine: {
                    lineStyle: {
                        color: '#EBEBEB' // 可以是颜色值，支持 RGB、RGBA、十六进制等格式
                    },
                    show: true // 显示 x 轴线
                },
                axisLabel: {
                    color: '#999999', // 文字颜色
                    fontSize: 12, // 文字大小
                    fontWeight: 400, // 字体粗细
                    formatter: function(value){
                        if (value.length > 16) {
                            return value.substring(0, 16)+ '...';
                        }
                        return value;
                    }
                },
                axisTick: {
                    show: false // 不显示 y 轴刻度
                },
                data: data.agent_name
            },
            series: [{
                name: lebel,
                type: 'bar',
                stack: 'total',
                data: data.data,
                barWidth: '18%',
                itemStyle: {
                    color: '#2A87C8' // 设置柱子的颜色
                },
            },
                // {
                //     name: label2,
                //     type: 'bar',
                //     stack: 'total',
                //     barWidth: '18%',
                //     // 开启系列中的标签
                //     label: {
                //         show: true,
                //         position: 'right', // 标签的位置
                //         color: '#2A87C8',
                //         fontWeight: 600,
                //         fontFamily: 'Roboto, Roboto',
                //         fontSize: 14,
                //         // 格式化标签内容
                //         formatter: function (params) {
                //             // params.value 是当前数据项的值
                //             return `${data.data[params.dataIndex] ?? 0}/${plan_data[params.dataIndex] ?? 0}`;
                //         }
                //     },
                //     itemStyle: {
                //         color: '#B6E9FF' // 设置柱子的颜色
                //     },
                //     data: plan_data
                // },
            ]
        };
        barChart.setOption(option);
        // 获取自定义提示框的 DOM 元素
        // 获取自定义提示框的 DOM 元素
        var customTooltip = document.getElementById('tooltip');

        // 监听图表的鼠标移动事件
        barChart.getZr().on('mousemove', function (params) {
            // 获取鼠标在坐标系中的位置
            const pointInGrid = barChart.convertFromPixel({ seriesIndex: 0 }, [params.offsetX, params.offsetY]);
            var element = params.event.srcElement;
            var $element = $(element);
            if (pointInGrid) {
                const [x, y] = pointInGrid;

                // 判断鼠标是否在纵坐标标签区域
                if (x < 0 && y >= 0 && y < option.yAxis.data.length) {
                    // 获取完整的纵轴标签
                    const fullText = option.yAxis.data[y];
                    // 显示自定义提示框
                    if (element.tagName.toLowerCase() === 'text' && ($element.text() != fullText)) {
                        $element.attr("data-original-title", fullText).tooltip({'container': 'body'})
                            .tooltip('show');
                        // 自定义提示框样式
                        $element.on('inserted.bs.tooltip', function () {
                            $('.tooltip-inner').css({
                                'background-color':'#5A5F68',
                                'border-radius':'3px',
                                'padding':'5px 9px',
                            });
                            $('.tooltip-arrow').css({
                                'border-top-color':'#5A5F68',
                            });
                        });
                    }
                } else {
                    // 如果不在纵坐标标签上，隐藏提示框
                    $element.tooltip('hide');
                }
            } else {
                // 如果不在坐标系内，隐藏提示框
                $element.tooltip('hide');
            }
        });

        // 监听鼠标移出事件，隐藏提示框
        barChart.getZr().on('mouseout', function (params) {
            var element = $(params.event.srcElement);
            element.tooltip('hide');
        });
    }

    //初始化报告统计
    var getReportInfo = function () {
        isLoading[2] = false;
        pAjaxRequest({
            count: true
        }, '/api/v1/industry/report_summary', 'GET', function (res) {
            isLoading[2] = true;
            initReportPieChart(res.data);
        });
    }

    var initReportPieChart = function (data) {
        $('.complete_num').empty().text(data.archived);
        $('.approving_num').empty().text(data.approving);
        $('.reject_num').empty().text(data.rejected);

        var option = {
            tooltip: {
                className: 'echarts-tooltip',
                trigger: 'item',
                formatter: function (params) {
                    return `<span class="tool-tip-title" style="margin-bottom:12px">${params.seriesName}</span><br><span class="circle" style="background-color: ${params.color}; margin-bottom:1.75px"></span><span>${params.data.name}${LANG.UI_PLATFORM_INDUSTRY_REPORT_PERCENT}</span><span class="tool-tip-num" style="margin-left:32px">${params.percent}%</span>
                            <br><span style="margin-left:16px">${params.data.name}${LANG.UI_PLATFORM_INDUSTRY_REPORT_COUNT}</span><span style="margin-left:32px" class="tool-tip-num">${params.data.value}</span>`;
                },
            },
            legend: {
                show: false
            },
            series: [{
                name: LANG.UI_PLATFORM_INDUSTRY_REPORT_PLAN_VERIFY_COUNT,
                type: 'pie',
                radius: ['84px', '50px'],
                center: ['50%', '50%'], // 饼图中心位置设置为容器的中心点
                color: ["#2A87C8", "#51CBFF", "#B6E9FF"],
                padAngle: 0.5,
                avoidLabelOverlap: false,
                hoverAnimation: false, // 鼠标移入变大
                label: {
                    show: true,
                    position: 'center',
                    formatter: [
                        `{val|${data.archived + data.approving + data.rejected}}`,
                        `{name|${LANG.UI_PLATFORM_INDUSTRY_REPORT_TOTAL_COUNT}}`
                    ].join('\n'),

                    rich: {
                        name: {
                            fontSize: 12,
                            fontWeight: 400,
                            color: '#76767B'
                        },
                        val: {
                            fontSize: 24,
                            fontWeight: 'bold',
                            color: '#2D3748'
                        }
                    }
                },
                emphasis: {
                    label: {
                        show: true,
                        fontSize: 40,
                        fontWeight: 'bold'
                    }
                },
                labelLine: {
                    show: false
                },
                data: [{
                    value: data.archived,
                    name: LANG.UI_PLATFORM_INDUSTRY_APPROVE_COMPLETE
                },
                    {
                        value: data.approving,
                        name: LANG.UI_PLATFORM_INDUSTRY_APPROVAL_PENDING
                    },
                    {
                        value: data.rejected,
                        name: LANG.UI_PLATFORM_INDUSTRY_APPROVAL_REJECT
                    }
                ]
            }]
        };
        pieChartReport.setOption(option);
    }

    var getReportCountInfo = function () {
        var type = $('#line_select').val();
        isLoading[3] = false;
        pAjaxRequest({
            type: type
        }, '/api/v1/industry/report_summary', 'GET', function (res) {
            isLoading[3] = true;
            initReportLineChart(res.data);
        });
    }

    var initReportLineChart = function (data) {
        // 先计算总和用于更新echarts图上方的总和
        var total = 0;
        var totalNormal = 0;
        var totalAbnormal = 0;
        for (var i = 0; i < data.total.length; i++) {
            total += data.total[i];
        }
        for (var i = 0; i < data.normal.length; i++) {
            totalNormal += data.normal[i];
        }
        for (var i = 0; i < data.abnormal.length; i++) {
            totalAbnormal += data.abnormal[i];
        }
        $('.report-line-info .total-val').text(total);
        $('.report-line-info .success-val').text(totalNormal);
        $('.report-line-info .abnormal-val').text(totalAbnormal);

        var option = {
            tooltip: {
                className: 'echarts-tooltip',
                trigger: 'axis',
                formatter: function (params) {
                    // params 是一个数组，包含了所有系列的数据信息
                    // 假设柱状图系列的 index 为 0
                    var result = [];
                    var title = '';
                    params.forEach(function (item) {
                        if (item.seriesType !== 'bar') { // 如果不是柱状图系列，则添加到结果中
                            result.push(item);
                        }
                    });
                    // 构建 tooltip 显示的内容
                    var tooltipContent = result.map(function (item) {
                        title = item.axisValue;
                        var html = `<div class="blueLine" style="background: ${item.color}"></div><span style="margin-right:34px">${item.seriesName}</span><span>${item.value}</span>`;
                        return html;
                    }).join('<br>');
                    var titleHtml = `<span style="font-weight: 400;font-size: 14px;color: #718096;">${title}</span><br>`;

                    return titleHtml + tooltipContent;
                }
            },
            legend: {
                icon: "rect", //形状  类型包括 circle，rect,line，roundRect，triangle，diamond，pin，arrow，none
                bottom: "0%",
                left: "center",
                itemWidth: 12, // 设置宽度
                itemHeight: 4, // 设置高度
                itemGap: 20, // 设置图例间距
                width: 372,
                height: 22,
                selectedMode: false, // 禁用图例的点击切换功能
                textStyle: {
                    color: "#666666",
                    fontSize: "14"
                },
                data: [LANG.UI_PLATFORM_INDUSTRY_REPORT_SUBMITED_COUNT, LANG.UI_PLATFORM_INDUSTRY_REPORT_NORMAL_COUNT, LANG.UI_PLATFORM_INDUSTRY_REPORT_ABNORMAL_COUNT]
            },
            grid: {
                left: '3%',
                right: '4%',
                top: '10%', // 上边距
                bottom: '10%', // 下边距
                containLabel: true // 确保标签不超出图表的可视区域
            },
            xAxis: {
                type: 'category',
                boundaryGap: false,
                axisTick: {
                    show: false
                },
                axisLine: {
                    show: false
                },
                axisLabel: {
                    fontSize: 14, // 标签字体大小
                    fontWeight: 400,
                    color: '#999999', // 标签颜色
                },
                axisPointer: {
                    type: 'line', // 使用线作为指示器
                    lineStyle: {
                        color: '#2A87C8', // 线的颜色
                        width: 1 // 线的宽度
                    }
                },
                boundaryGap: ['10%'],
                data: data.date
            },
            yAxis: [{
                minInterval: 1,
                splitLine: {
                    show: false
                },
                axisLine: {
                    show: false
                },
                axisLabel: {
                    fontSize: 14, // 标签字体大小
                    fontWeight: 400,
                    color: '#999999', // 标签颜色
                },

                type: 'value'
            },
                {
                    type: 'value',
                    minInterval: 1,
                    splitLine: {
                        show: false
                    },
                    axisLabel: {
                        show: false // 不显示 y 轴的刻度值
                    },
                    axisLine: {
                        show: false // 不显示 y 轴的刻度线
                    },
                    min: 100,
                    max: 100 // 假设柱状图的高度固定100满，只是为了做一个样式
                },
            ],

            series: [{
                type: 'bar',
                selectedMode: 'single',
                animation: false,
                barWidth: 46,
                itemStyle: {
                    color: 'rgba(42,135,200,0.04)' // 系列颜色
                },
                emphasis: {
                    focus: 'none',
                    itemStyle: {
                        color: 'rgba(255, 255, 255, 1)', // 鼠标悬停时的柱子颜色
                        shadowColor: '#E3EDF3',
                        shadowBlur: 20
                    }
                },
                yAxisIndex: 1, // 使用第一个y轴
                data: [100, 100, 100, 100, 100, 100, 100, 100, 100, 100, 100, 100] // 柱状图的高度数据
            },
                {
                    name: LANG.UI_PLATFORM_INDUSTRY_REPORT_SUBMITED_COUNT,
                    type: 'line',
                    yAxisIndex: 0, // 使用第二个y轴
                    lineStyle: {
                        color: '#51CBFF' // 系列1的线条颜色
                    },
                    itemStyle: {
                        color: '#51CBFF', // 系列1的数据点颜色
                    },
                    data: data.total
                },
                {
                    name: LANG.UI_PLATFORM_INDUSTRY_REPORT_NORMAL_COUNT,
                    type: 'line',
                    yAxisIndex: 0, // 使用第二个y轴
                    lineStyle: {
                        color: '#2A87C8' // 线条颜色
                    },
                    itemStyle: {
                        color: '#2A87C8' // 数据点颜色
                    },
                    data: data.normal
                },
                {
                    name: LANG.UI_PLATFORM_INDUSTRY_REPORT_ABNORMAL_COUNT,
                    type: 'line',
                    yAxisIndex: 0, // 使用第二个y轴
                    lineStyle: {
                        color: '#EAA40E' // 线条颜色
                    },
                    itemStyle: {
                        color: '#EAA40E' // 数据点颜色
                    },
                    data: data.abnormal
                }
            ]
        };
        lineChart.setOption(option);
    }

    var getReportMessage = function () {
        isLoading[4] = false;
        pAjaxRequest({
            offset: 0,
            limit: 5,
            sort: 'report_time',
            order: 'desc',
            type: 4
        }, '/api/v1/industry/report', 'GET', function (res) {
            isLoading[4] = true;
            initReportMessage(res.data.rows);
        });
    }

    var initReportMessage = function (data) {
        var html = '';
        var count = 0;
        for (let i = 0; i < data.length; i++) {
            var approvalFlag = data[i].approval_flag;
            if (approvalFlag) {
                count++
                var content = JSON.parse(data[i].approval_list);
                var status = data[i].status;
                var statusDes = LANG.UI_PLATFORM_INDUSTRY_APPROVAL_PENDING;
                var statusClass = 'pending-item';
                var statusLabel = `label label-sm label-info`;

                html += `<div class="item mt8">
                            <div class="item-info"><div>${data[i].name}${LANG.UI_PLATFORM_INDUSTRY_APPROVAL_PENDING_YOURSELF}</div><span class="approve-time" style="color:#666666;margin-left:auto;white-space:nowrap">${data[i].create_time}</span></div>
                            <div class="item-label"><span class='${statusLabel} ${statusClass}'>${statusDes}</span></div>
                        </div>`;
            }
        }
        $('.message-content').empty().append(html);
        if (count < 5) {
            // 如果不够5调待审批内容,查询待发起报告的内容
            getWittingReportMessage(count);
        }
        //初始化item点击事件监听 因为如果放在initListener里面的话会监听不到
        $('.datacenter-content .message-content > .item').click(function() {
            //如果能找到revoke-item  那么就是待发起 如果不能就是待审批
            if($(this).find('> .item-label .revoke-item').length > 0){
                var uuid = $(this).attr('data-uuid');
                //LOCATION('./content/platform/jobs/verify_jobs.php','verification_job');
                var url = './content/platform/dataverification/verification_job_details.php?type=37&uuid=' + uuid;
                LOCATION(url, 'verification_job');
            }else{
                LOCATION('./content/platform/industry/report_list.php', 'industry_report');
            }
        })
    }

    var getWittingReportMessage = function (count) {
        var limit = 5 - count;
        isLoading[5] = false;
        pAjaxRequest({
            offset: 0,
            limit: limit,
            sort: 'report_time',
            order: 'desc',
            type: 4,
            status: 9
        }, '/api/v1/industry/report', 'GET', function (res) {
            isLoading[5] = true;
            initWaitingGenerateReportMessage(res.data.rows);
        },false);
    }

    var initWaitingGenerateReportMessage = function (data) {
        var html = '';
        for (let i = 0; i < data.length; i++) {
            var status = data[i].status;
            var statusDes = LANG.UI_PLATFORM_INDUSTRY_APPROVAL_PENDING_START_REPORT;
            var statusClass = 'revoke-item';
            var statusLabel = `label label-sm label-default`;
            statusDes = LANG.UI_PLATFORM_INDUSTRY_APPROVAL_PENDING_START_REPORT_STATUS;

            html += `<div class="item mt8" data-uuid="${data[i].job_uuid}">
                        <div class="item-info"><div>${data[i].name}${LANG.UI_PLATFORM_INDUSTRY_APPROVAL_PENDING_START_REPORT}</div><span class="approve-time" style="color:#666666;margin-left:auto;white-space:nowrap">${data[i].create_time}</span></div>
                        <div class="item-label"><span class='${statusLabel} ${statusClass}'>${statusDes}</span></div>
                    </div>`;
        }
        $('.message-content').append(html);
    }

    var getDeviceInfo = function () {
        isLoading[6] = false;
        pAjaxRequest({}, '/api/v1/industry/device_summary', 'GET', function (res) {
            isLoading[6] = true;
            initTotalDeviceInfo(res.data);
            initVerifyDeviceInfo(res.data);
        });
    }

    var initTotalDeviceInfo = function (data) {
        $('.total-device-info .backed').empty().text(data.backed);
        $('.total-device-info .not-backed').empty().text(data.not_backed);
        $('.total-content .total-val').empty().text(data.total);
        $('.plan-yearly .plan-val').empty().text(data.year_plan);
        $('.total-content .unverified-val').empty().text(data.year_not_verify);
        var option = {
            title: {
                text: `{val|${data.total}}\n{name|${LANG.UI_PLATFORM_INDUSTRY_REPORT_DEVICE_TOTAL_COUNT}}`,
                top: '38%',
                left: 'center',
                textStyle: {
                    rich: {
                        val: {
                            fontSize: 20,
                            fontWeight: 600,
                            color: '#2D3748'
                        },
                        name: {
                            fontSize: 14,
                            fontWeight: 400,
                            color: '#76767B'
                        }
                    }
                }
            },
            tooltip: {
                className: 'echarts-tooltip',
                trigger: 'item',
            },
            legend: {
                show: false
            },
            series: [{
                name: LANG.UI_PLATFORM_INDUSTRY_REPORT_DEVICE_COUNT,
                type: 'pie',
                radius: ['60%', '70%'],
                center: ['50%', '50%'], // 饼图中心位置设置为容器的中心点
                color: ["#2A87C8", "#E2E7ED"],
                avoidLabelOverlap: false,
                hoverAnimation: false, // 鼠标移入变大
                label: {
                    show: false // 鼠标悬停时也不显示标签
                },
                labelLine: {
                    show: false
                },
                data: [{
                    value: data.backed,
                    name: LANG.UI_PLATFORM_INDUSTRY_REPORT_BACKED_DEVICE_COUNT
                },
                    {
                        value: data.not_backed,
                        name: LANG.UI_PLATFORM_INDUSTRY_REPORT_NOT_BACKED_DEVICE_COUNT
                    }
                ]
            }]
        };
        pieChartDevice.setOption(option);
    }

    var initVerifyDeviceInfo = function (data) {
        $('.verify-device-info .verify').empty().text(data.verified);
        $('.verify-device-info .not-verify').empty().text(data.not_verified);
        var option = {
            title: {
                text: `{val|${data.backed}}\n{name|${LANG.UI_PLATFORM_INDUSTRY_REPORT_CAN_VERIFY_DEVICE_COUNT}}`,
                top: '38%',
                left: 'center',
                textStyle: {
                    rich: {
                        val: {
                            fontSize: 20,
                            fontWeight: 600,
                            color: '#2D3748'
                        },
                        name: {
                            fontSize: 14,
                            fontWeight: 400,
                            color: '#76767B'
                        }
                    }
                }
            },
            tooltip: {
                className: 'echarts-tooltip',
                trigger: 'item',
            },
            legend: {
                show: false
            },
            series: [{
                name: LANG.UI_PLATFORM_INDUSTRY_REPORT_TOTAL_VERIFIED_DEVICE_COUNT,
                type: 'pie',
                radius: ['60%', '70%'],
                center: ['50%', '50%'], // 饼图中心位置设置为容器的中心点
                color: ["#51CBFF", "#E2E7ED"],
                avoidLabelOverlap: false,
                hoverAnimation: false, // 鼠标移入变大
                label: {
                    show: false // 鼠标悬停时也不显示标签
                },
                labelLine: {
                    show: false
                },
                data: [{
                    value: data.verified,
                    name: LANG.UI_PLATFORM_INDUSTRY_REPORT_HAS_VERIFIED_DEVICE_COUNT
                },
                    {
                        value: data.not_verified,
                        name: LANG.UI_PLATFORM_INDUSTRY_REPORT_HAS_NOT_VERIFIED_DEVICE_COUNT
                    }
                ]
            }]
        };
        pieChart.setOption(option);
    }

    var initSize = function () {
        window.addEventListener("resize", resizeLayout);
    }

    var resizeLayout = function () {
        if (document.body.clientWidth <= 1440) { //1440以下图片缩小  位置发生改变
            $('.allInfo').addClass('low-resolution');
        } else { //1440以上图片回到默认位置
            $('.allInfo').removeClass('low-resolution');
        }
        var allChart = getAllChart();
        for (var i = 0; i < allChart.length; i++) {
            allChart[i].resize();
        }
    }

    var initPendingTips = function () {
        // 一开始要处理下类，避免多次点点击首页的时候tip闪烁
        $('.pending-tip').removeClass('tip-show');
        $('.pending-tip').removeClass('tip-hide');
        $('.pending-tip-div').hide(); //外层隐藏，不然透明了还是可以点
        isLoading[7] = false;
        pAjaxRequest({
            offset: 0,
            type: 4
        }, '/api/v1/industry/report', 'GET', function (res) {
            isLoading[7] = true;
            // 获取approval_flag值为 true 的数量,表示需要当前用户审批
            const trueCount = res.data.rows.filter(item => item.approval_flag).length;
            if (trueCount > 0) {
                $('#pending_count').text(trueCount);
                $('.pending-tip-div').show();
                $('.pending-tip').removeClass('tip-hide');
                $('.pending-tip').addClass('tip-show');
                setTimeout(() => {
                    $('.pending-tip').addClass('tip-hide');
                    $('.pending-tip').removeClass('tip-show');
                    setTimeout(() => {
                        $('.pending-tip-div').hide();
                    }, 1000);
                }, 5000);

            }
        })
    }

    var initCustomSysName = function () {
        var oldName = $('.custom-sys-name').text();
        isLoading[8] = false;
        pAjaxRequest({}, '/api/v1/system/setting', 'GET', (res)=>{
            isLoading[8] = true;
            setCustomName(res, oldName);
        });

        var setCustomName = function (res, oldName) {
            if (oldName != res.data.sys_name && res.data.sys_name != '') {
                $('.custom-sys-name').text(res.data.sys_name);
            }
            if($('.custom-sys-name').text() == ''){
                $('.custom-sys-name').css('border','none')
            }
        }
    }

    // 监听页面的接口是否请求完毕
    var initLoading = function (){
        // 在调用 blockUI 之前设置一个短暂的延迟，以确保元素已创建
        setTimeout(function() {
            $('.blockUI').css('z-index', 10052); // 强制设置 z-index 为 2000
        }, 100);
        Metronic.blockUI({
            target: '.page-container',
            animate: true,
            overlayCSS: {
                backgroundColor: '#fff', // 可选：背景颜色
                opacity: 0.1            // 可选：背景透明度
            }
        });
        // 启动定时器，每隔500毫秒检查一次 isLoading 数组的状态
        var interval = setInterval(function() {
            try {
                // 检查 isLoading 数组是否满足条件
                if (isLoading.length === 9 && isLoading.every(element => element === true)) {
                    // 解除页面阻塞
                    Metronic.unblockUI('.page-container');
                    $('#gmp_homepage_shadow_box').hide();
                    // 清除定时器
                    clearInterval(interval);
                } else {
                    //  console.log('Still loading...');
                }
            } catch (error) {
                //  console.error('Error during checking isLoading array:', error);
                clearInterval(interval); // 发生错误时清除定时器
            }
        }, 500);
    }
    return {
        //main function to initiate the module
        init: function () {
            initLoading(); // 监听页面的接口是否请求完毕
            initCustomSysName(); //首页来初始化自定义系统名称
            // initConfig();
            initSystime();
            initLoginHistorys();
            checkLisence(); //检查系统授权
            initUserType();
            initAlarmTips();
            initListener();
            // initDataCenterView();
            // initTaskStatus();
            initLisenceInfo();
            getSystemStorageData(); //GMP存储统计
            getClientInfo(); //新增GMP客户端统计
            getReportInfo(); //新增GMP报告统计
            getReportCountInfo(); //GMP报告数目统计
            getDeviceInfo(); //GMP设备信息
            getReportMessage(); //GMP待办事项
            resizeLayout(); // 初始化判断分辨率
            initSize(); //适配小分辨率
            initPendingTips(); //初始化待审批报告数的提示tip
            initNodeUUID();
            initLoginHistory();
            History.pushState({
                url: "./content/platform/databackup_center_vinchin.php"
            }, document.title, "?homepage");
        },
        //定义更新告警信息接口,添加虚拟化中心成功,添加存储成功后调用
        updateTopAlarmTips: function () {
            updateAlarmTips();
        }
    };

}();

jQuery(document).ready(function () {
    DataBackupCenter.init();
});