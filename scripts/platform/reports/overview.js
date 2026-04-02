var overview = function () {
    let taskRunningTendencyQueryParams = {
        startTime: '',
        endTime: ''
    }
    let alarmQueryParams = {
        startTime: '',
        endTime: ''
    }
    let protectedPercent = 0;
    let onlinePercent = 0;
    let vmProtected = 0;
    let cloudProtected = 0;
    let USED_STORAGE_SIZE = 0;
    let UNUSED_STORAGE_SIZE = 0;
    let obs_total = 0;
    let obPencent = 0;
    let TOTAL_STORAGE_SIZE = 0;
    let agentEchart = null;
    let vcenterEchart = null;
    let cloudEchart = null;
    let privateEchart = null;
    let obsEchart = null;
    let storageEchart = null;
    let alarmEchart = null;
    let copyObjEchart = null;
    let copyTargetEchart = null;
    let dbCopyHostEchart = null;
    let dbCopyBackupHostEchart = null;
    let taskRunningChart = null;
    let alarmChart = null;

    /**
     * 初始化客户端报表echart
     */
    const initClientEchart = () => {
        agentEchart = echarts.init(document.getElementById('agent_echarts'));
        let series = [];
        let pieDatas = [
            {
                "value": onlinePercent,
                "name": LANG.UI_REPORT_ONLINE
            },
            {
                "value": protectedPercent,
                "name": LANG.UI_REPORT_PROTECTING
            },
        ];
        let maxRadius = 90,
            barWidth = 20,
            barGap = 20; //配置圆环大小
        let showValue = true, showPercent = true;
        let barColor = ["#4CC695", "#199FE4"];
        let noBarColor = ["#E9EEF3", "#F2FAFD"];
        pieDatas.map((item, i) => {
            series.push({
                type: 'pie',
                clockwise: true,
                hoverAnimation: false, //鼠标移入变大
                radius: [(maxRadius - i * (barGap + barWidth)) + '%', (maxRadius - (i + 1) * barWidth - i * barGap) + '%'],
                center: ["50%", "50%"],
                label: {
                    show: false
                },
                itemStyle: {
                    label: {
                        show: false,
                    },
                    labelLine: {
                        show: false
                    },
                    borderWidth: 5,
                },
                data: [{
                    value: item.value,
                    name: item.name,
                    itemStyle: {
                        color: barColor[i]
                    }
                }, {
                    value: 100 - item.value,
                    name: '',
                    itemStyle: {
                        color: noBarColor[i],
                    },
                    tooltip: {
                        show: false
                    },
                    hoverAnimation: false
                }]
            })
            series.push({
                name: '',
                type: 'pie',
                silent: true,
                z: 0,
                clockwise: true,
                hoverAnimation: false, //鼠标移入变大
                radius: [(maxRadius - i * (barGap + barWidth)) + '%', (maxRadius - (i + 1) * barWidth - i * barGap) + '%'],
                center: ["50%", "50%"],
                label: {
                    show: false
                },
                itemStyle: {
                    label: {
                        show: false,
                    },
                    labelLine: {
                        show: false
                    },
                },
                data: [{
                    value: 1,
                    itemStyle: {
                        color: "transparent",
                    },
                    tooltip: {
                        show: false
                    },
                    hoverAnimation: false
                }]
            });
        })
        option = {
            grid: {
                top: '33%',
                bottom: '54%',
                left: "30%",
                containLabel: false
            },
            tooltip: {
                show: false,
            },
            xAxis: [{
                show: false
            }],
            yAxis: [{
                type: 'category',
                inverse: true,
                axisLine: {
                    show: false
                },
                axisTick: {
                    show: false
                },
                axisLabel: {
                    inside: true,
                    textStyle: {
                        color: "#fff",
                        fontSize: 14,
                    },
                    margin: 195,
                    show: true
                },
                data: pieDatas.map(x => x.value + '%')
            }],
            legend: {
                show: false,
            },
            series: series,
        };
        agentEchart.setOption(option);
        $(window).resize(function () { // 监控屏幕大小变化，重新加载echart图
            agentEchart.resize();
        });
    }

    /**
     * 初始化客户端报表
     */
    const initClientReport = () => {
        Metronic.blockUI({target: ".client-card",animate: true});
        pAjaxRequest({}, '/api/v1/report/template/agentOverview', 'get', function (res) {
            try {
                if (res.success) {
                    let onlineNumber = res.data.online_host
                    let offlineNumber = res.data.offline_host
                    let protectedNumber = res.data.protected_client
                    let unprotected = res.data.unprotected_client
                    $('#onlineNumber').text(onlineNumber);
                    $('#offlineNumber').text(offlineNumber);
                    $('#protectedNumber').text(protectedNumber);
                    $('#unprotectedNumber').text(unprotected);
                    let  allProtected = protectedNumber + unprotected
                    let allLine = onlineNumber + offlineNumber
                    protectedPercent = (protectedNumber * 100 / allProtected).toFixed(2);
                    onlinePercent = (onlineNumber * 100 / allLine).toFixed(2);
    
                    initClientEchart();
                } else {
                    UIToastr.showWarning(LANG.UI_GET_CLIENT_REPORT_ERROR);
                }
            } catch (error) {
                UIToastr.showWarning(LANG.UI_GET_CLIENT_REPORT_ERROR);
            } finally {
                Metronic.unblockUI(".client-card");
            }
        });
    }

    /**
     * 初始化VM echart
     * @param {*} vmTotal 虚拟机总数
     * @param {*} vmProtectedPercent 受保护虚拟机数
     */
    const initVcenterEchart = (vmTotal, vmProtectedPercent) => {
        vcenterEchart = echarts.init(document.getElementById('vcenter_echarts'));
        let option = {
            graphic: [ // 为环形图中间添加文字
                {
                    type: 'text',
                    left: 'center',
                    top: '36%',
                    style: {
                        text: LANG.UI_VISUAL_CLOUD_PLATFORM_VM,
                        textAlign: 'center',
                        fill: '#4C5563',
                        fontSize: 14
                    }
                },
                {
                    type: 'text',
                    left: 'center',
                    top: '54%',
                    style: {
                        text: vmTotal.toString(),
                        textAlign: 'center',
                        fill: '#4C5563',
                        fontSize: 14,
                        fontWeight: 'bold'
                    }
                }
            ],
            series: [
                {
                    type: 'gauge',
                    radius: '95%',
                    center: ['50%', '50%'],
                    startAngle: 90,
                    endAngle: -270,
                    pointer: {
                        show: false
                    },
                    detail: {
                        color: 'black',
                        show: false
                    },
                    progress: {
                        show: true,
                        overlap: false,
                        roundCap: true,
                        clip: false,
                        itemStyle: {
                            color: '#4CC695'
                        }
                    },
                    axisLine: {
                        lineStyle: {
                            width: 14,
                            color: [
                                [1, '#F0F2F4'] // 设置圆环底色为灰色
                            ]
                        }
                    },
                    splitLine: {
                        show: false,
                        distance: 0,
                        length: 10
                    },
                    axisTick: {
                        show: false
                    },
                    axisLabel: {
                        show: false,
                        distance: 50
                    },
                    data: [{value:vmProtectedPercent}]
                }
            ]
        };
        vcenterEchart.setOption(option);
        $(window).resize(function() { // 监控屏幕大小变化，重新加载echart
            vcenterEchart.resize();
        });
    }

    /**
     * 初始化VM报表
     */
    const initVcenterReport = () => {
        Metronic.blockUI({target: ".vm-overview-card",animate: true});

        pAjaxRequest({}, '/api/v1/report/template/vmOverview', 'get', function (res) {
            try {
                if (res.success) {
                    let vmNumber = res.data.vm_total;
                    let vmPlatform = res.data.vm_platform_total;
                    let protectedNumber = res.data.vm_protected_total;
                    let unprotected = res.data.vm_unprotected_total;

                    $('#vm_number').text(vmNumber);
                    $('#vm_platform').text(vmPlatform);
                    $('#vm_protected').text(protectedNumber);
                    $('#vm_unprotected').text(unprotected);
                    let allProtected = protectedNumber + unprotected;
                    let vmProtectedPercent = (protectedNumber * 100 / allProtected).toFixed(2);
                    
                    initVcenterEchart(vmNumber, vmProtectedPercent);
                } else {
                    UIToastr.showWarning(LANG.UI_GET_VM_REPORT_ERROR);
                }
            } catch (error) {
                UIToastr.showWarning(LANG.UI_GET_VM_REPORT_ERROR);
            } finally {
                Metronic.unblockUI(".vm-overview-card");
            }
        });
    }

    /**
     * 初始化公有云echart
     */
    const initPublicCloudEchart = (publicCloudVmTotal, publicCloudProtectedVmPercent) => {
        cloudEchart = echarts.init(document.getElementById('public_echarts'));
        let option = {
            graphic: [ // 为环形图中间添加文字
                {
                    type: 'text',
                    left: 'center',
                    top: '36%',
                    style: {
                        text: LANG.UI_GRAIN_AWS_INSTANCE,
                        textAlign: 'center',
                        fill: '#4C5563',
                        fontSize: 14
                    }
                },
                {
                    type: 'text',
                    left: 'center',
                    top: '54%',
                    style: {
                        text: publicCloudVmTotal.toString(),
                        textAlign: 'center',
                        fill: '#4C5563',
                        fontSize: 14,
                        fontWeight: 'bold'
                    }
                }
            ],
            series: [
                {
                    type: 'gauge',
                    radius: '95%',
                    center: ['50%', '50%'],
                    startAngle: 90,
                    endAngle: -270,
                    pointer: {
                        show: false
                    },
                    detail: {
                        color: 'black',
                        show: false
                    },
                    progress: {
                        show: true,
                        overlap: false,
                        roundCap: true,
                        clip: false,
                        itemStyle: {
                            color: '#22BC7D'
                        }
                    },
                    axisLine: {
                        lineStyle: {
                            width: 14,
                            color: [
                                [1, '#F0F2F4'] // 设置圆环底色为灰色
                            ]
                        }
                    },
                    splitLine: {
                        show: false,
                        distance: 0,
                        length: 10
                    },
                    axisTick: {
                        show: false
                    },
                    axisLabel: {
                        show: false,
                        distance: 50
                    },
                    data: [{ value: publicCloudProtectedVmPercent }]
                }
            ]
        };
        cloudEchart.setOption(option);
        $(window).resize(function() { // 监控屏幕大小变化，重新加载echart图
            cloudEchart.resize();
        });
    }

    /**
     * 初始化私有云echart
     */
    const initPrivateCloudEchart = (privateCloudVmTotal, privateCloudProtectedPencent) => {
        privateEchart = echarts.init(document.getElementById('private_echarts'));
        let option = {
            graphic: [ // 为环形图中间添加文字
                {
                    type: 'text',
                    left: 'center',
                    top: '36%',
                    style: {
                        text: LANG.UI_GRAIN_AWS_INSTANCE,
                        textAlign: 'center',
                        fill: '#4C5563',
                        fontSize: 14
                    }
                },
                {
                    type: 'text',
                    left: 'center',
                    top: '54%',
                    style: {
                        text: privateCloudVmTotal.toString(),
                        textAlign: 'center',
                        fill: '#4C5563',
                        fontSize: 14,
                        fontWeight: 'bold'
                    }
                }
            ],
            series: [
                {
                    type: 'gauge',
                    radius: '95%',
                    center: ['50%', '50%'],
                    startAngle: 90,
                    endAngle: -270,
                    pointer: {
                        show: false
                    },
                    detail: {
                        color: 'black',
                        show: false
                    },
                    progress: {
                        show: true,
                        overlap: false,
                        roundCap: true,
                        clip: false,
                        itemStyle: {
                            color: '#22BC7D'
                        }
                    },
                    axisLine: {
                        lineStyle: {
                            width: 14,
                            color: [
                                [1, '#F0F2F4'] // 设置圆环底色为灰色
                            ]
                        }
                    },
                    splitLine: {
                        show: false,
                        distance: 0,
                        length: 10
                    },
                    axisTick: {
                        show: false
                    },
                    axisLabel: {
                        show: false,
                        distance: 50
                    },
                    data: [{ value: privateCloudProtectedPencent } ]
                }
            ]
        };
        privateEchart.setOption(option);
        $(window).resize(function() { // 监控屏幕大小变化，重新加载echart图
            privateEchart.resize();
        });
    }

    /**
     * 初始化虚拟化报表
     */
    const initCloudReport = () => {
        Metronic.blockUI({target: ".vm-pubpri-overview-card",animate: true});

        pAjaxRequest({}, '/api/v1/report/template/cloudOverview', 'get', function (res) {
            try {
                if (res.success) {
                    let publicCloudTotal = res.data.public_cloud_total;
                    let privateCloudTotal = res.data.private_cloud_total;
                    let publicCloudVmTotal = res.data.public_cloud_vm_total;
                    let privateCloudVmTotal = res.data.private_cloud_vm_total;
                    let publicCloudVmProtectedTotal = res.data.public_cloud_vm_protected_total;
                    let publicCloudVmUnprotectedTotal = res.data.public_cloud_vm_unprotected_total;
                    let privateprotected = res.data.private_cloud_vm_protected_total;
                    let privateUnprotected = res.data.private_cloud_vm_unprotected_total;

                    $('#public_cloud').text(publicCloudTotal);
                    $('#private_cloud').text(privateCloudTotal);
                    $('#protected_public_cloud').text(publicCloudVmProtectedTotal);
                    $('#unprotected_public_cloud').text(publicCloudVmUnprotectedTotal);
                    $('#protected_private_cloud').text(privateprotected);
                    $('#unprotected_private_cloud').text(privateUnprotected);

                    let allPublicProtected = publicCloudVmProtectedTotal + publicCloudVmUnprotectedTotal;
                    let publicCloudProtectedVmPercent = (publicCloudVmProtectedTotal * 100 / allPublicProtected).toFixed(2);

                    let allPrivateProtected =  privateUnprotected + privateprotected;
                    let privateCloudProtectedPencent = (privateprotected * 100 / allPrivateProtected).toFixed(2);
    
                    initPublicCloudEchart(publicCloudVmTotal, publicCloudProtectedVmPercent);
                    initPrivateCloudEchart(privateCloudVmTotal, privateCloudProtectedPencent);
                } else {
                    UIToastr.showWarning(LANG.UI_GET_VIRTUAL_REPORT_ERROR);
                }
            } catch (error) {
                UIToastr.showWarning(LANG.UI_GET_VIRTUAL_REPORT_ERROR);
            } finally {
                Metronic.unblockUI(".vm-pubpri-overview-card");
            } 
        });
    }

    /**
     * 初始化nas报表
     */
    const initNasReport = () => {
        Metronic.blockUI({target: ".nas-card",animate: true});

        pAjaxRequest({}, '/api/v1/report/template/nasOverview', 'get', function (res) {
            try {
                if (res.success) {
                    let nasDevice = res.data.device_number;
                    let nasOnline = res.data.online_number;
                    let nasOffline = res.data.offline_number;
                    let nasProtect = res.data.protected_number;
                    let nasTask = res.data.nas_task;
                    let nasBackup = `${unitConver(res.data.backup_data).size}${unitConver(res.data.backup_data).unit}`;
                    $('#nas_device').text(nasDevice);
                    $('#nas_online').text(nasOnline);
                    $('#nas_offline').text(nasOffline);
                    $('#nas_protect').text(nasProtect);
                    $('#nas_task').text(nasTask);
                    $('#nas_backup').text(nasBackup);
                } else {
                    UIToastr.showWarning(LANG.UI_GET_NAS_REPORT_ERROR);
                }
            } catch (error) {
                UIToastr.showWarning(LANG.UI_GET_NAS_REPORT_ERROR);
            } finally {
                Metronic.unblockUI(".nas-card");
            }
        });
    }

    /**
     * 初始化cdp报表
     */
    const initCdpReport = () => {
        Metronic.blockUI({target: ".volcdp-card",animate: true});

        pAjaxRequest({}, '/api/v1/report/template/cdpOverview', 'get', function (res) {
            try {
                if (res.success) {
                    let cdpDevice = res.data.host_number;
                    let cdpBackup = `${unitConver(res.data.backup_data).size}${unitConver(res.data.backup_data).unit}`;
                    let cdpTask = res.data.task_number;
                    let nasBackupSet = res.data.backup_set_number;
                    $('#cdp_device').text(cdpDevice);
                    $('#cdp_backup').text(cdpBackup);
                    $('#cdp_task').text(cdpTask);
                    $('#cdp_backup_set').text(nasBackupSet);
                } else {
                    UIToastr.showWarning(LANG.UI_GET_VOLCDP_REPORT_ERROR);
                }   
            } catch (error) {
                UIToastr.showWarning(LANG.UI_GET_VOLCDP_REPORT_ERROR);
            } finally {
                Metronic.unblockUI(".volcdp-card");
            }
        });
    }

    /**
     * 初始化应用报表
     */
    const initAppReport = () => {
        Metronic.blockUI({target: ".exchange-card",animate: true});

        pAjaxRequest({}, '/api/v1/report/template/appOverview', 'get', function (res) {
            try {
                if (res.success) {
                    let appDevice = res.data.app_number;
                    let appBackup = `${unitConver(res.data.backup_data).size}${unitConver(res.data.backup_data).unit}`;
                    let appTask = res.data.app_task;
                    let appOnline = res.data.app_online;
                    let appOffline = res.data.app_offline;
                    let appProtected = res.data.app_protected;
                    $('#app_device').text(appDevice);
                    $('#app_online').text(appOnline);
                    $('#app_offline').text(appOffline);
                    $('#app_protected').text(appProtected);
                    $('#app_task').text(appTask);
                    $('#app_backup').text(appBackup);
                } else {
                    UIToastr.showWarning(LANG.UI_GET_EXCHANGE_REPORT_ERROR);
                }
            } catch (error) {
                UIToastr.showWarning(LANG.UI_GET_EXCHANGE_REPORT_ERROR);
            } finally {
                Metronic.unblockUI(".exchange-card");
            }
        });
    }

    /**
     * 初始化对象存储echart
     */
    const initObsEchart = () => {
        obsEchart = echarts.init(document.getElementById('obs_echart'));
        let option = {
            graphic: [ // 为环形图中间添加文字
                {
                    type: 'text',
                    left: 'center',
                    top: '38%',
                    style: {
                        text: obs_total,
                        textAlign: 'center',
                        fill: '#4CC695',
                        fontSize: 24
                    }
                },

                {
                    type: 'text',
                    left: 'center',
                    top: '55%',
                    style: {
                        text: LANG.UI_VCENTER_AUTH_TOTAL,
                        textAlign: 'center',
                        fill: '#98A2B2',
                        fontSize: 16
                    }
                }
            ],
            series: [
                {
                    type: 'gauge',
                    radius: '95%',
                    center: ['50%', '50%'],
                    startAngle: 90,
                    endAngle: -270,
                    pointer: {
                        show: false
                    },
                    detail: {
                        color: 'black',
                        show: false
                    },
                    progress: {
                        show: true,
                        overlap: false,
                        roundCap: true,
                        clip: false,
                        itemStyle: {
                            color: '#22BC7D'
                        }
                    },
                    axisLine: {
                        lineStyle: {
                            width: 17,
                            color: [
                                [1, '#F0F2F4'] // 设置圆环底色为灰色
                            ]
                        }
                    },
                    splitLine: {
                        show: false,
                        distance: 0,
                        length: 10
                    },
                    axisTick: {
                        show: false
                    },
                    axisLabel: {
                        show: false,
                        distance: 50
                    },
                    data: [{value:obPencent}]
                }
            ]
        };
        obsEchart.setOption(option);
        $(window).resize(function() { // 监控屏幕大小变化，重新加载echart图
            obsEchart.resize();
        });
    }

    /**
     * 初始化对象存储报表
     */
    const initObsReport = () => {
        const OVERVIEW_MODULE_TYPE = '34';
        let obs_normal = 0;
        let obs_offline = 0;

        Metronic.blockUI({target: ".obs-card",animate: true});
        pAjaxRequest({}, '/api/v1/report/template/overview/' + OVERVIEW_MODULE_TYPE, 'get', function (res) {
            try {
                if (res.success) {
                    Metronic.unblockUI(".obs-card");
                    let d = res.data.overviewList
                    obs_total = d.obs_total
                    obs_normal = d.obs_normal
                    obs_offline = d.obs_offline
                    obPencent = (obs_normal * 100 / obs_total).toFixed(2);
                    $('#obs_online').text(obs_normal);
                    $('#obs_offline').text(obs_offline);
            
                    initObsEchart();
                } else {
                    UIToastr.showWarning(LANG.UI_GET_OBS_REPORT_ERROR);
                }
            } catch (error) {
                UIToastr.showWarning(LANG.UI_GET_OBS_REPORT_ERROR);
            } finally {
                Metronic.unblockUI(".obs-card");
            }
        });
    }

    /**
     * 初始化hadoop报表
     */
    const initHadoopReport = () => {
        const OVERVIEW_MODULE_TYPE = '11';

        Metronic.blockUI({target: ".hadoop-card",animate: true});
        pAjaxRequest({}, '/api/v1/report/template/overview/' + OVERVIEW_MODULE_TYPE, 'get', function (res) {
            try {
                if (res.success) {
                    let hadoopDevice = res.data.overviewList.hadoop_total;
                    let hadoopOnline = res.data.overviewList.hadoop_online;
                    let hadoopOffline = res.data.overviewList.hadoop_offline;
                    let hadoopProtect = res.data.overviewList.hadoop_auth;
                    let hadoopTask = res.data.overviewList.task;
                    let hadoopBackup = !res.data.overviewList.backupData ? '0B' :`${unitConver(res.data.overviewList.backupData).size}${unitConver(res.data.overviewList.backupData).unit}`;
                    $('#hadoop_device').text(hadoopDevice);
                    $('#hadoop_online').text(hadoopOnline);
                    $('#hadoop_offline').text(hadoopOffline);
                    $('#hadoop_auth').text(hadoopProtect);
                    $('#hadoop_task').text(hadoopTask);
                    $('#hadoop_backup').text(hadoopBackup);
                } else {
                    UIToastr.showWarning(LANG.UI_GET_HADOOP_REPORT_ERROR);
                }
            } catch (error) {
                UIToastr.showWarning(LANG.UI_GET_HADOOP_REPORT_ERROR);
            } finally {
                Metronic.unblockUI(".hadoop-card");
            }
        });
    }

    const initFileCopySourceEchart = (online, totalClient) => {
        copyObjEchart = echarts.init(document.getElementById('copy_obj_chart'));
        let option = {
            graphic: [ // 为环形图中间添加文字
                {
                    type: 'text',
                    left: 'center',
                    top: '35%',
                    style: {
                        text: LANG.UI_COPY_OBJECT_ECHART,
                        textAlign: 'center',
                        fill: '#4C5563',
                        fontSize: 12
                    }
                },
                {
                    type: 'text',
                    left: 'center',
                    top: '55%', // 新增一行显示 totalClient
                    style: {
                        text: totalClient.toString(),
                        textAlign: 'center',
                        fill: '#4C5563',
                        fontSize: 12
                    }
                }
            ],
            series: [
                {
                    type: 'gauge',
                    radius: '95%',
                    center: ['50%', '50%'],
                    startAngle: 90,
                    endAngle: -270,
                    pointer: {
                        show: false
                    },
                    detail: {
                        color: 'black',
                        show: false
                    },
                    progress: {
                        show: true,
                        overlap: false,
                        roundCap: true,
                        clip: false,
                        itemStyle: {
                            color: '#199FE4'
                        }
                    },
                    axisLine: {
                        lineStyle: {
                            width: 14,
                            color: [
                                [1, '#F0F2F4'] // 设置圆环底色为灰色
                            ]
                        }
                    },
                    splitLine: {
                        show: false,
                        distance: 0,
                        length: 10
                    },
                    axisTick: {
                        show: false
                    },
                    axisLabel: {
                        show: false,
                        distance: 50
                    },
                    max: totalClient,
                    data: [{ value: online }]
                }
            ]
        };
        copyObjEchart.setOption(option);
        $(window).resize(function() { // 监控屏幕大小变化，重新加载echart
            copyObjEchart.resize();
        });
    }

    const initFileCopyTargetEchart = (online, totalClient) => {
        copyTargetEchart = echarts.init(document.getElementById('copy_target_chart'));
        let option = {
            graphic: [ // 为环形图中间添加文字
                {
                    type: 'text',
                    left: 'center',
                    top: '35%',
                    style: {
                        text: LANG.UI_COPY_TARGET_ECHART,
                        textAlign: 'center',
                        fill: '#4C5563',
                        fontSize: 12
                    }
                },
                {
                    type: 'text',
                    left: 'center',
                    top: '55%', // 新增一行显示 totalClient
                    style: {
                        text: totalClient.toString(),
                        textAlign: 'center',
                        fill: '#4C5563',
                        fontSize: 12
                    }
                }
            ],
            series: [
                {
                    type: 'gauge',
                    radius: '95%',
                    center: ['50%', '50%'],
                    startAngle: 90,
                    endAngle: -270,
                    pointer: {
                        show: false
                    },
                    detail: {
                        color: 'black',
                        show: false
                    },
                    progress: {
                        show: true,
                        overlap: false,
                        roundCap: true,
                        clip: false,
                        itemStyle: {
                            color: '#0FBF98'
                        }
                    },
                    axisLine: {
                        lineStyle: {
                            width: 14,
                            color: [
                                [1, '#F0F2F4'] // 设置圆环底色为灰色
                            ]
                        }
                    },
                    splitLine: {
                        show: false,
                        distance: 0,
                        length: 10
                    },
                    axisTick: {
                        show: false
                    },
                    axisLabel: {
                        show: false,
                        distance: 50
                    },
                    max: totalClient,
                    data: [{ value: online }]
                }
            ]
        };
        copyTargetEchart.setOption(option);
        $(window).resize(function() { // 监控屏幕大小变化，重新加载echart
            copyTargetEchart.resize();
        });
    }

    /**
     * 初始化文件复制报表
     */
    const initFileCopyReport = () => {
        const OVERVIEW_MODULE_TYPE = '26';

        Metronic.blockUI({ target: ".filecopy-card", animate: true });
        pAjaxRequest({}, '/api/v1/report/template/overview/' + OVERVIEW_MODULE_TYPE, 'get', function (res) {
            try {
                if (res.success) {
                    let { online, offline, totalClient, protectedNum, taskNum } = { ...res.data.overviewList };
                    let filecopyTotalData = !res.data.overviewList.totalCopyData ? '0B' : `${unitConver(parseInt(res.data.overviewList.totalCopyData)).size}${unitConver(parseInt(res.data.overviewList.totalCopyData)).unit}`;
                    $('#filecopy_source_client_online').text(online);
                    $('#filecopy_source_client_offline').text(offline);
                    $('#filecopy_target_client_online').text(online);
                    $('#filecopy_target_client_offline').text(offline);
                    $('#filecopy_protected').text(protectedNum);
                    $('#filecopy_tasks').text(taskNum);
                    $('#filecopy_copy_data').text(filecopyTotalData);

                    initFileCopySourceEchart(online, totalClient);
                    initFileCopyTargetEchart(online, totalClient);
                } else {
                    UIToastr.showWarning(LANG.UI_GET_FILECOPY_REPORT_ERROR);
                }
            } catch (error) {
                UIToastr.showWarning(LANG.UI_GET_FILECOPY_REPORT_ERROR);
            } finally {
                Metronic.unblockUI(".filecopy-card");
            }
        });
    }

    /**
     * 初始化K8s数据报表
     */
    const initK8sReport = () => {
        const OVERVIEW_MODULE_TYPE = '28';

        Metronic.blockUI({ target: ".k8s-card", animate: true });
        pAjaxRequest({}, '/api/v1/report/template/overview/' + OVERVIEW_MODULE_TYPE, 'get', function (res) {
            try {
                if (res.success) {
                    let { online, offline, total, protectedNum, taskNum, backupDataSize } = { ...res.data.overviewList };
                    $('#kubernets_total').text(total);
                    $('#kubernets_online').text(online);
                    $('#kubernets_offline').text(offline);
                    $('#kubernets_protected').text(protectedNum);
                    $('#kubernets_tasks').text(taskNum);
                    $('#kubernets_backup_data').text(backupDataSize.des);    
                } else {
                    UIToastr.showWarning(LANG.UI_GET_K8S_REPORT_ERROR);
                }
            } catch (error) {
                UIToastr.showWarning(LANG.UI_GET_K8S_REPORT_ERROR);
            } finally {
                Metronic.unblockUI(".k8s-card");
            }
        });
    }

    /**
     * 初始化数据库数据报表
     */
    const initDbProtectReport = () => {
        const OVERVIEW_MODULE_TYPE = '29';

        Metronic.blockUI({ target: ".dbprotect-card", animate: true });
        pAjaxRequest({}, '/api/v1/report/template/overview/' + OVERVIEW_MODULE_TYPE, 'get', function (res) {
            try {
                if (res.success) {
                    let { online_instances, offline_instances, total_instances, protected_num, task_num, backup_data } = { ...res.data.overviewList };
                    $('#db_total').text(total_instances);
                    $('#db_online').text(online_instances);
                    $('#db_offline').text(offline_instances);
                    $('#db_protected').text(protected_num);
                    $('#db_tasks').text(task_num);
                    $('#db_backup_data').text(backup_data.des);    
                } else {
                    UIToastr.showWarning(LANG.UI_GET_K8S_REPORT_ERROR);
                }
            } catch (error) {
                UIToastr.showWarning(LANG.UI_GET_K8S_REPORT_ERROR);
            } finally {
                Metronic.unblockUI(".dbprotect-card");
            }
        });
    }

    const initDbCopySourceEchart = (online, totalClient) => {
        dbCopyHostEchart = echarts.init(document.getElementById('dbcopy_host_chart'));
        let option = {
            graphic: [ // 为环形图中间添加文字
                {
                    type: 'text',
                    left: 'center',
                    top: '35%',
                    style: {
                        text: LANG.UI_PUBLIC_HOST,
                        textAlign: 'center',
                        fill: '#4C5563',
                        fontSize: 12
                    }
                },
                {
                    type: 'text',
                    left: 'center',
                    top: '55%', // 新增一行显示 totalClient
                    style: {
                        text: totalClient.toString(),
                        textAlign: 'center',
                        fill: '#4C5563',
                        fontSize: 12
                    }
                }
            ],
            series: [
                {
                    type: 'gauge',
                    radius: '95%',
                    center: ['50%', '50%'],
                    startAngle: 90,
                    endAngle: -270,
                    pointer: {
                        show: false
                    },
                    detail: {
                        color: 'black',
                        show: false
                    },
                    progress: {
                        show: true,
                        overlap: false,
                        roundCap: true,
                        clip: false,
                        itemStyle: {
                            color: '#199FE4'
                        }
                    },
                    axisLine: {
                        lineStyle: {
                            width: 14,
                            color: [
                                [1, '#F0F2F4'] // 设置圆环底色为灰色
                            ]
                        }
                    },
                    splitLine: {
                        show: false,
                        distance: 0,
                        length: 10
                    },
                    axisTick: {
                        show: false
                    },
                    axisLabel: {
                        show: false,
                        distance: 50
                    },
                    max: totalClient,
                    data: [{ value: online }]
                }
            ]
        };
        dbCopyHostEchart.setOption(option);
        $(window).resize(function() { // 监控屏幕大小变化，重新加载echart
            dbCopyHostEchart.resize();
        });
    }

    const initDbCopyTargetEchart = (online, totalClient) => {
        dbCopyBackupHostEchart = echarts.init(document.getElementById('dbcopy_backup_host_chart'));
        let option = {
            graphic: [ // 为环形图中间添加文字
                {
                    type: 'text',
                    left: 'center',
                    top: '35%',
                    style: {
                        text: LANG.UI_COPY_TARGET_ECHART,
                        textAlign: 'center',
                        fill: '#4C5563',
                        fontSize: 12
                    }
                },
                {
                    type: 'text',
                    left: 'center',
                    top: '55%', // 新增一行显示 totalClient
                    style: {
                        text: totalClient.toString(),
                        textAlign: 'center',
                        fill: '#4C5563',
                        fontSize: 12
                    }
                }
            ],
            series: [
                {
                    type: 'gauge',
                    radius: '95%',
                    center: ['50%', '50%'],
                    startAngle: 90,
                    endAngle: -270,
                    pointer: {
                        show: false
                    },
                    detail: {
                        color: 'black',
                        show: false
                    },
                    progress: {
                        show: true,
                        overlap: false,
                        roundCap: true,
                        clip: false,
                        itemStyle: {
                            color: '#0FBF98'
                        }
                    },
                    axisLine: {
                        lineStyle: {
                            width: 14,
                            color: [
                                [1, '#F0F2F4'] // 设置圆环底色为灰色
                            ]
                        }
                    },
                    splitLine: {
                        show: false,
                        distance: 0,
                        length: 10
                    },
                    axisTick: {
                        show: false
                    },
                    axisLabel: {
                        show: false,
                        distance: 50
                    },
                    max: totalClient,
                    data: [{ value: online }]
                }
            ]
        };
        dbCopyBackupHostEchart.setOption(option);
        $(window).resize(function() { // 监控屏幕大小变化，重新加载echart
            dbCopyBackupHostEchart.resize();
        });
    }

    /**
     * 初始化数据库复制报表
     */
    const initDbCopyReport = () => {
        const OVERVIEW_MODULE_TYPE = '10';

        Metronic.blockUI({ target: ".dbcopy-card", animate: true });
        pAjaxRequest({}, '/api/v1/report/template/overview/' + OVERVIEW_MODULE_TYPE, 'get', function (res) {
            try {
                if (res.success) {
                    let { hostOnline, hostOffline, totalHost, bakOnline, bakOffline, totalBackupHost, protectedNum, taskNum, totalCopyData } = { ...res.data.overviewList };
                    $('#dbcopy_host_online').text(hostOnline);
                    $('#dbcopy_host_offline').text(hostOffline);
                    $('#dbcopy_backup_host_online').text(bakOnline);
                    $('#dbcopy_backup_host_offline').text(bakOffline);
                    $('#dbcopy_protected').text(protectedNum);
                    $('#dbcopy_tasks').text(taskNum);
                    $('#dbcopy_copy_data').text(totalCopyData.des);  
                    
                    initDbCopySourceEchart(hostOnline, totalHost);
                    initDbCopyTargetEchart(bakOnline, totalBackupHost);
                } else {
                    UIToastr.showWarning(LANG.UI_GET_DBCOPY_REPORT_ERROR);
                }
            } catch (error) {
                UIToastr.showWarning(LANG.UI_GET_DBCOPY_REPORT_ERROR);
            } finally {
                Metronic.unblockUI(".dbcopy-card");
            }
        });
    }

    /**
     * 初始化节点报表
     */
    const initNodeReport = () => {
        Metronic.blockUI({ target: ".node-card", animate: true });

        pAjaxRequest({}, '/api/v1/report/template/nodeOverview', 'get', function (res) {
            try {
                if (res.success) {
                    let runTime = res.data.run_rime;
                    let nodeProtected = `${unitConver(res.data.backup_data).size}${unitConver(res.data.backup_data).unit}`;
                    let childNode = res.data.node_number;
                    let normalNode = res.data.normal_number;
                    let abnormalNode = res.data.abnormal_number;
                    const hostPart = window.location.href.split('//')[1];
                    const ipEndIndex = hostPart.indexOf('/') > 0 ? hostPart.indexOf('/') : (hostPart.indexOf('?') > 0 ? hostPart.indexOf('?') : hostPart.length);
                    const ip = hostPart.substring(0, ipEndIndex);
                    $('#mainNode').text(ip);
                    $('#run_time').text(runTime + LANG.UI_PUBLIC_UNIT_DAY);
                    $('#node_protected').text(nodeProtected);
                    $('#child_node').text(childNode);
                    $('#normal_node').text(normalNode);
                    $('#abnormal_node').text(abnormalNode);
                } else {
                    UIToastr.showWarning(LANG.UI_GET_NODE_REPORT_ERROR);
                }
            } catch (error) {
                UIToastr.showWarning(LANG.UI_GET_NODE_REPORT_ERROR);
            } finally {
                Metronic.unblockUI(".node-card");
            }
        });
    }

    /**
     * 初始化存储报表echart
     */
    const initStorageEchart = () => {
        storageEchart = echarts.init(document.getElementById('storage_echarts'));
        option = {
            tooltip: {
                trigger: 'item'
            },
            legend: {
                show: false
            },
            graphic: [ // 为环形图中间添加文字
                {
                    type: 'text',
                    left: 'center',
                    top: '42%',
                    style: {
                        text: TOTAL_STORAGE_SIZE ,
                        textAlign: 'center',
                        fill: '#2D3748',
                        fontSize: 20
                    }
                },
                {
                    type: 'text',
                    left: 'center',
                    top: '58%',
                    style: {
                        text: LANG.UI_REPORT_STORAGE_CAPACITY,
                        textAlign: 'center',
                        fill: '#647080',
                        fontSize: 14
                    }

                }
            ],
            series: [
                {
                    name: LANG.UI_VM_SETTING_STORAGE,
                    type: 'pie',
                    radius: ['65%', '90%'],
                    avoidLabelOverlap: false,
                    label: {
                        show: false,
                        position: 'center'
                    },
                    labelLine: {
                        show: false
                    },
                    data: [
                        { value: USED_STORAGE_SIZE, name: LANG.UI_HOMEPAGE_USED_STORAGE, itemStyle: { color: '#31D395' } },
                        { value: UNUSED_STORAGE_SIZE, name: LANG.UI_HOMEPAGE_REMAIN_STORAGE, itemStyle: { color: '#E9EEF3' } }
                    ]
                }
            ]
        };
        storageEchart.setOption(option);
        $(window).resize(function() { // 监控屏幕大小变化，重新加载echart图
            storageEchart.resize();
        });
    }

    /**
     * 初始化存储报表
     */
    const initStorageReport = () => {
        Metronic.blockUI({target: ".storage-card",animate: true});
        pAjaxRequest({}, '/api/v1/report/template/storageOverview', 'get', function (res) {
            try {
                if (res.success) {
                    let copyStorage = res.data.copy_storage_num;
                    let usedSize = `${unitConver(res.data.used_storage).size}${unitConver(res.data.used_storage).unit}`;
                    let usedCopySize = `${unitConver(res.data.used_copy_storage).size}${unitConver(res.data.used_copy_storage).unit}`;
                    let unusedSize = `${unitConver(res.data.unused_storage).size}${unitConver(res.data.unused_storage).unit}`;

                    let totalStorageSize = res.data.total_storage_size;
                    TOTAL_STORAGE_SIZE = `${unitConver(totalStorageSize).size}${unitConver(totalStorageSize).unit}`;
                    let usedSizePrenctent = (res.data.used_storage * 100 / totalStorageSize ).toFixed(2);
                    let unusedSizePrenctent = (res.data.unused_storage * 100 / totalStorageSize).toFixed(2);

                    USED_STORAGE_SIZE = res.data.used_storage;
                    UNUSED_STORAGE_SIZE = res.data.unused_storage;

                    $('#copy_storage').text(copyStorage);
                    $('#used_size').text(usedCopySize);
                    $('#used_size_number').text(usedSize);
                    $('#unused_size_number').text(unusedSize);
                    $('#used_size_precent').text(usedSizePrenctent + '%');
                    $('#unused_size_precent').text(unusedSizePrenctent + '%');
        
                    initStorageEchart();
                } else {
                    UIToastr.showWarning(LANG.UI_GET_STORAGE_REPORT_ERROR);
                }
            } catch (error) {
                UIToastr.showWarning(LANG.UI_GET_STORAGE_REPORT_ERROR);
            } finally {
                Metronic.unblockUI(".storage-card");
            }
        });
    }

    /**
     * 初始化任务运行趋势echart
     */
    const initTaskRunningTendencyEchart = (xData, yData, taskCountData, echartId, unit) => {
        if ($(`#${echartId}`).children().length > 0) {
            // 销毁上一个echart
            echarts.dispose(document.getElementById(echartId));
        }

        taskRunningChart = echarts.init(document.getElementById(echartId));

        let option = {
            tooltip: {
                trigger: 'axis',
                icon: 'circle',
                enterable: false,
                backgroundColor: '#fff',
                extraCssText: 'box-shadow:0px 5px 25px 0px rgba(4, 13, 45, 0.15)',
                borderWidth: 1,
                borderColor: '#fff',
                axisPointer: {
                    show: true,
                    icon: 'circle',
                    lineStyle: {
                        type: 'dashed'
                    }
                },
                textStyle:{
                    color: '#718096'
                },
                formatter: function(params) {
                    let relVal = params[0].name;
                    for (let i = 0, l = params.length; i < l; i++) {
                        let pvalue = `${params[i].value.toFixed(2)}${unit}`;
                        let relName = params[i].seriesName;
                        let NumberValue = params[i].value;
                        if(relName == 'backup') {
                            relVal += '<br/>' + ' <span style="display:inline-block;margin-right:5px;margin-top:17px;width:12px;height:12px;border-radius: 50%!important;background-color:#13C4B6;"></span>' + ' <span style="color: #2D3748!important">'+LANG.UI_HOMEPAGEPRO_BACKUP_DATA + pvalue + '</span> ' ;
                        } else {
                            relVal += '<br/>' + ' <span style="display:inline-block;margin-right:5px;margin-top:17px;width:12px;height:12px;border-radius: 50%!important;background-color:#10AEFF;"></span>' + ' <span style="color: #2D3748!important">'+LANG.UI_REPORT_TASK_NUMBER + NumberValue + '</span> ' ;
                        }
                    }

                    return relVal;
                }
            },
            grid: {
                left: '4%',
                right: '4%',
                top: '10%',
                bottom: '4%',
                containLabel: true // 确保标签不会被裁剪
            },
            xAxis: {
                type: 'category',
                boundaryGap: false,
                data: xData,
                axisLine: {
                    lineStyle: {
                        type: 'solid',
                        color: '#C9CDD4', // 左边线的颜色
                        width:'1'// 坐标线的宽度
                    }
                },
                axisLabel: {
                    color:'#86909C'
                }
            },
            yAxis: [
                {
                    type: 'value',
                    name: `(${unit})`,
                    nameTextStyle: {
                        color: '#86909C',
                        fontSize:14,
                        padding: [0, 0, 0, -40]
                    },
                    splitLine:{ // 虚线
                        show:true,
                        lineStyle:{
                            type:'dashed'
                        }
                    },
                    axisLine: {
                        lineStyle: {
                            type: 'solid',
                            color: '#86909C', // 左边线的颜色
                            width:'1'// 坐标线的宽度
                        }
                    },
                    axisLabel: {
                        color:'#86909C'
                    }
                },
                {
                    type: 'value',
                    name: LANG.UI_BACKUP_NUM,
                    minInterval: 1,
                    nameTextStyle: {
                        color: '#86909C',
                        fontSize:14,
                        padding: [0, -40, 0, 0]
                    },
                    splitLine:{ // 虚线
                        show:false
                    },
                    axisLine: {
                        lineStyle: {
                            type: 'solid',
                            color: '#86909C', // 右边线的颜色
                            width:'1'// 坐标线的宽度
                        }
                    },
                    axisLabel: {
                        color:'#86909C'
                    }
                }
            ],
            series: [
                {
                    name: 'backup',
                    yAxisIndex: 0,
                    lineStyle: { // 设置线条的style等
                        normal: {
                            color: 'aquamarine' // 折线线条颜色:绿色
                        }
                    },
                    areaStyle: { // 渐变色
                        color: {
                            x: 0,
                            y: 0,
                            x2: 0,
                            y2: 1,
                            colorStops: [{
                                offset: 0, color: 'aquamarine' // 0% 处的颜色
                            }, {
                                offset: 1, color: 'white' //
                            }]

                        }
                    },
                    smooth: true, // 平滑
                    symbol: 'none',
                    data: yData,
                    itemStyle: {
                        emphasis: {
                            areaStyle: {
                                color: {
                                    x: 0,
                                     y: 0,
                                    x2: 0,
                                    y2: 1,
                                    colorStops: [{
                                        offset: 0, color: 'aquamarine' // 0% 处的颜色
                                    }, {
                                        offset: 1, color: 'white' //
                                    }]

                                }
                            }
                        }
                    },
                    type: 'line',
                },
                {
                    name: 'taskNumber',
                    yAxisIndex: 1,
                    lineStyle: { // 设置线条的style等
                        normal: {
                            color: '#10AEFF' // 折线线条颜色:蓝色
                        }
                    },
                    areaStyle: { // 渐变色
                        color: {
                            x: 0,
                            y: 0,
                            x2: 0,
                            y2: 1,
                            colorStops: [{
                                offset: 0, color: '#10AEFF' // 0% 处的颜色
                            }, {
                                offset: 1, color: 'white' //
                            }]

                        }
                    },
                    smooth: true, // 平滑
                    symbol: 'none',
                    data: taskCountData,
                    itemStyle: {
                        emphasis: {
                            areaStyle: {
                                color: {
                                    x: 0,
                                    y: 0,
                                    x2: 0,
                                    y2: 1,
                                    colorStops: [{
                                        offset: 0, color: '#10AEFF' // 0% 处的颜色
                                    }, {
                                        offset: 1, color: 'white' //
                                    }]

                                }
                            }
                        }
                    },
                    type: 'line',
                }
            ]
        };

        if (xData.length === 1) {
            option.series.forEach(s => {
                s.symbol = 'circle';
                s.symbolSize = 8;
            });
        }

        taskRunningChart.setOption(option);
        $(window).resize(function() { // 监控屏幕大小变化，重新加载echart图
            taskRunningChart.resize();
        });
    }

    /**
     * 初始化任务运行趋势报表
     */
    const initTaskRunningTendencyReport = () => {
        let today = new Date();
        let sevenDaysAgo = new Date();
        sevenDaysAgo.setDate(today.getDate() - 6);

        let params = {
            startTime: taskRunningTendencyQueryParams.startTime ? taskRunningTendencyQueryParams.startTime : formatDateTime(sevenDaysAgo),
            endTime: taskRunningTendencyQueryParams.endTime ? taskRunningTendencyQueryParams.endTime : formatDateTime(today)
        }

        Metronic.blockUI({target: ".running-tendency-content",animate: true});
        pAjaxRequest(params, '/api/v1/report/template/taskTendencyOverview', 'get', function (res) {
            try {
                if (res.success) {
                    let resData = res.data;
                    
                    if (resData.every(i => Number(i.total) === 0)) { // 无数据
                        $('.running-tendency-content').addClass('display-none');
                        $('.running-tendency-nodata').removeClass('display-none');

                        $('#task_days').html(0);
                        $('#task_backup_data').html(`0B`);
                        $('#task_nums').html(0);
                    } else {
                        let xData = [], yData = [], taskCountData = [];
                        let maxValue = 0;
                        resData.forEach(item => {
                            xData.push(item.finishTime);

                            let value = Number(item.total);
                            yData.push(Number(item.total));
                            maxValue = Math.max(maxValue, value);

                            taskCountData.push(item.taskCount);
                        });

                        // 确定合适的单位
                        let unit = 'B';
                        if (maxValue >= 1024) {
                            unit = 'KB';
                            yData = yData.map(value => value / 1024);
                        }
                        if (maxValue >= 1024 * 1024) {
                            unit = 'MB';
                            yData = yData.map(value => value / 1024);
                        }
                        if (maxValue >= 1024 * 1024 * 1024) {
                            unit = 'GB';
                            yData = yData.map(value => value / 1024);
                        }
                        if (maxValue >= 1024 * 1024 * 1024 * 1024) {
                            unit = 'TB';
                            yData = yData.map(value => value / 1024);
                        }

                        // 使用 reduce 方法累加 yData 和 taskCountData 数组中的所有值
                        let totalBackupData = yData.reduce((acc, val) => acc + val, 0);
                        let totalTaskCountData = taskCountData.reduce((acc, val) => acc + val, 0);

                        $('#task_days').html(resData.length);
                        $('#task_backup_data').html(`${Math.round(totalBackupData)}${unit}`);
                        $('#task_nums').html(totalTaskCountData);

                        $('.running-tendency-content').removeClass('display-none');
                        $('.running-tendency-nodata').addClass('display-none');

                        initTaskRunningTendencyEchart(xData, yData, taskCountData, 'task_running_tendency_echart', unit);
                    }
                } else {
                    UIToastr.showWarning(LANG.UI_GET_TASK_RUNNING_TENDENCY_REPORT_ERROR);
                }
            } catch (error) {
                UIToastr.showWarning(LANG.UI_GET_TASK_RUNNING_TENDENCY_REPORT_ERROR);
            } finally {
                Metronic.unblockUI(".running-tendency-content");
            }
        });
    }

    /**
     * 初始化告警echart
     * @param {*} xData 横坐标数据
     * @param {*} systemAlarmData 系统告警数据
     * @param {*} taskAlarmData 任务告警数据
     * @param {*} echartId 
     */
    const initAlarmEchart = (xData, systemAlarmData, taskAlarmData, echartId) => {
        if ($(`#${echartId}`).children().length > 0) {
            // 销毁上一个echart
            echarts.dispose(document.getElementById(echartId));
        }

        alarmChart = echarts.init(document.getElementById(echartId));

        let option = {
            tooltip: {
                trigger: 'axis',
                axisPointer: {
                    type: 'cross',
                    crossStyle: {
                        color: '#999'
                    }
                },
                formatter: function(params) {
                    let relVal = params[0].name;
                    for (let i = 0, l = params.length; i < l; i++) {
                        let relName = params[i].seriesName;
                        let pvalue = params[i].value;
                        pvalue = pvalue + LANG.UI_PUBLIC_NUM;
                        if(relName == 'system')
                            relVal += '<br/>' + ' <span style="display:inline-block;margin-right:5px;margin-top:17px;width:12px;height:12px;border-radius: 50%!important;background-color:#10AEFF;"></span>' + ' <span style="color: #2D3748!important">'+ LANG.UI_REPORT_SYSTEM_ALARM + pvalue + '</span> ' ;
                        else
                            relVal += '<br/>' + ' <span style="display:inline-block;margin-right:5px;margin-top:17px;width:12px;height:12px;border-radius: 50%!important;background-color:#F3C857;"></span>' + ' <span style="color: #2D3748!important">'+LANG.UI_REPORT_TASK_ALARM + pvalue + '</span> ' ;
                    }
                    return relVal;
                }
            },
            grid: {
                left: '4%',
                right: '4%',
                top: '10%',
                bottom: '4%',
                containLabel: true // 确保标签不会被裁剪
            },
            toolbox: {
                show: false,
            },
            legend: {
                show: false
            },
            xAxis: {
                type: 'category',
                data: xData,
                axisLine: {
                    lineStyle: {
                        type: 'solid',
                        color: '#C9CDD4', // 左边线的颜色
                    }
                },
                axisTick: {
                    show: false // 不显示x轴的刻度线
                },
                axisLabel: {
                    textStyle: {
                        color:'#86909C'
                    }
                }
            },
            yAxis: {
                type: 'value',
                name: `(${LANG.UI_PUBLIC_STRIP})`,
                nameTextStyle: {
                    color: '#86909C',
                    fontSize:14,
                    padding: [0, 0, 0, -40]
                },
                splitLine:{ // 虚线
                    show: true,
                    lineStyle:{
                        type:'solid'
                    }
                },
                axisLine: {
                    lineStyle: {
                        type: 'solid',
                        color: '#86909C', // 左边线的颜色
                    }
                },
                axisLabel: {
                    textStyle: {
                        color:'#86909C'
                    }
                }
            },
            series: [
                {
                    name: 'system',
                    type: 'bar',
                    barWidth: 16,
                    itemStyle: {
                        normal: {
                            color: '#10AEFF',
                            barBorderRadius: [10, 10, 0, 0], // 顶部左右两个角是圆角，底部左右两个角是直角
                        }
                    },
                    data: systemAlarmData
                },
                {
                    name: 'task',
                    type: 'bar',
                    barWidth: 16,
                    itemStyle: {
                        normal: {
                            color: '#F3C857', // 您可以改变这里的颜色代码来设置您想要的颜色
                            barBorderRadius: [10, 10, 0, 0], // 顶部左右两个角是圆角，底部左右两个角是直角
                        }
                    },
                    data: taskAlarmData
                },
            ]
        };

        alarmChart.setOption(option);
        $(window).resize(function() { // 监控屏幕大小变化，重新加载echart图
            alarmChart.resize();
        });
    }

    /**
     * 初始化告警报表
     */
    const initAlarmReport = () => {
        let today = new Date();
        let sevenDaysAgo = new Date();
        sevenDaysAgo.setDate(today.getDate() - 6);

        let params = {
            startTime: alarmQueryParams.startTime ? alarmQueryParams.startTime : formatDateTime(sevenDaysAgo),
            endTime: alarmQueryParams.endTime ? alarmQueryParams.endTime : formatDateTime(today),
        }

        Metronic.blockUI({target: ".alarm-content",animate: true});
        pAjaxRequest(params, '/api/v1/report/template/alarmOverview', 'get', function (res) {
            try {
                if (res.success) {
                    let systemAlarmList = res.data.systemAlarm;
                    let taskAlarmList = res.data.taskAlarm;
                    if (systemAlarmList.every(i => i.value === 0) && taskAlarmList.every(i => i.value === 0)) { // 无告警数据
                        $('.alarm-content').addClass('display-none');
                        $('.alarm-nodata').removeClass('display-none');

                        $('#alarm_days').html(0);
                        $('#alarm_task_nums').html(0);
                        $('#alarm_system_nums').html(0);
                    } else {
                        let xData = [], systemAlarmData = [], taskAlarmData = [];

                        systemAlarmList.forEach(item => {
                            xData.push(item.time);
                            systemAlarmData.push(item.value)
                        });

                        taskAlarmList.forEach(item => {
                            taskAlarmData.push(item.value)
                        });

                        // 使用 reduce 方法累加 systemAlarmList 和 taskAlarmList 数组中的所有值
                        let totalSystemAlarmData = systemAlarmData.reduce((acc, val) => acc + val, 0);
                        let totalTaskAlarmData = taskAlarmData.reduce((acc, val) => acc + val, 0);

                        $('#alarm_days').html(systemAlarmList.length);
                        $('#alarm_task_nums').html(totalTaskAlarmData);
                        $('#alarm_system_nums').html(totalSystemAlarmData);

                        $('.alarm-content').removeClass('display-none');
                        $('.alarm-nodata').addClass('display-none');

                        initAlarmEchart(xData, systemAlarmData, taskAlarmData, 'alarm_echart');
                    }
                } else {
                    UIToastr.showWarning(LANG.UI_GET_ALARM_REPORT_ERROR);
                }
            } catch (error) {
                UIToastr.showWarning(LANG.UI_GET_ALARM_REPORT_ERROR);
            } finally {
                Metronic.unblockUI(".alarm-content");
            } 
        });
    }
    
    /**
     * 报表概览导出
     */
    const reportOverviewExport = () => {
       $('#report_overview_export').attr('data-bs-indicator', 'on');

        // 使用按钮防抖1.5秒执行一次导出
        debounce(captureScrollAndGeneratePDF($('.report-overview__content').get(0), 2300, 'report.pdf',  LANG.UI_VINCHIN_REPORT_WATER_NAME, function() {
            $('#report_overview_export').removeAttr('data-bs-indicator');
        }), 1500);
    }

    const initDateSelect =  () => {
        $('.taskdateItem').click(function() {
            $('.taskdateItem').removeClass('active');
            $(this).addClass('active');
            taskSelectValue = $(this).data('value');
            initTaskRunningTendencyReport()
        });
    }

    const inintDatatimePicker = () => {
        //初始化日期时间选择控件
        $('#system_alarm_daterangepicker').daterangepicker({
            "autoUpdateInput": false,											//是否自动填充input
            "startDate": moment().subtract(6, 'days').startOf('day'),           // 默认开始时间为7天前的开始（0点）
            "endDate": moment().subtract(1, 'days').endOf('day'),  		//默认开始时间
            "maxDate": moment({hour: 23, minute: 59}),												//最大可用时间
            "timePicker": false,													//是否显示时间,时分
            "timePicker24Hour": true,											//是否是24小时制
            "alwaysShowCalendars": true,										//是否总是显示日期选择
            "ranges": DateRangePickerLocales.getRangesConfig(CONF.LANGUAGE),	//根据语言定义默认ranges,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
            "locale": DateRangePickerLocales.getLocalConfig(CONF.LANGUAGE),		//根据语言定义默认local,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
        }, function(start, end, label) {
//			console.log('New date range selected: ' + start.format('YYYY-MM-DD HH:mm:ss') + ' to ' + end.format('YYYY-MM-DD HH:mm:ss') + ' predefined range: ' + label);
        });

        //如果不是选择后自动填充input(autoUpdateInput:true),需要监听下面两个方法apply.daterangepicker和cancel.daterangepicker
        $('#system_alarm_daterangepicker').on('apply.daterangepicker', function(ev, picker) {
            //给全局变量赋值,然后设置input
            alarmQueryParams.startTime = picker.startDate.format('YYYY-MM-DD');
            alarmQueryParams.endTime = picker.endDate.format('YYYY-MM-DD');
            _daterangepicker_range = picker.chosenLabel;
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
            $(this).html('<i class="icon-time"></i>' + picker.startDate.format('YYYY/MM/DD') + ' - ' + picker.endDate.format('YYYY/MM/DD'));
            initAlarmReport()
        });

        $('#system_alarm_daterangepicker').on('cancel.daterangepicker', function(ev, picker) {
            //清除全局变量,然后设置input
            alarmQueryParams.startTime = "";
            alarmQueryParams.endTime = "";
            _daterangepicker_range = "";
            $(this).val('');
            initAlarmReport()
        });

        //input右侧的图标事件
        $('.daterangepickerdiv i').click(function() {
            $(this).parent().find('input').click();
        });

        //初始化日期时间选择控件
        $('#task_running_tendency_daterangepicker').daterangepicker({
            "autoUpdateInput": false,											//是否自动填充input
            "startDate": moment().subtract(6, 'days').startOf('day'),           // 默认开始时间为7天前的开始（0点）
            "endDate": moment().subtract(1, 'days').endOf('day'), 
            "maxDate": moment({hour: 23, minute: 59}),												//最大可用时间
            "timePicker": false,													//是否显示时间,时分
            "timePicker24Hour": true,											//是否是24小时制
            "alwaysShowCalendars": true,										//是否总是显示日期选择
            "ranges": DateRangePickerLocales.getRangesConfig(CONF.LANGUAGE),	//根据语言定义默认ranges,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
            "locale": DateRangePickerLocales.getLocalConfig(CONF.LANGUAGE),		//根据语言定义默认local,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
        }, function(start, end, label) {
//			console.log('New date range selected: ' + start.format('YYYY-MM-DD HH:mm:ss') + ' to ' + end.format('YYYY-MM-DD HH:mm:ss') + ' predefined range: ' + label);
        });

        //如果不是选择后自动填充input(autoUpdateInput:true),需要监听下面两个方法apply.daterangepicker和cancel.daterangepicker
        $('#task_running_tendency_daterangepicker').on('apply.daterangepicker', function(ev, picker) {
            //给全局变量赋值,然后设置input
            taskRunningTendencyQueryParams.startTime = picker.startDate.format('YYYY-MM-DD');
            taskRunningTendencyQueryParams.endTime = picker.endDate.format('YYYY-MM-DD');
            _taskrangepicker_range = picker.chosenLabel;
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
            $(this).html('<i class="icon-time"></i>' + picker.startDate.format('YYYY/MM/DD') + ' - ' + picker.endDate.format('YYYY/MM/DD'));
            initTaskRunningTendencyReport()
        });

        $('#task_running_tendency_daterangepicker').on('cancel.daterangepicker', function(ev, picker) {
            //清除全局变量,然后设置input
            taskRunningTendencyQueryParams.startTime = "";
            taskRunningTendencyQueryParams.endTime = "";
            _taskrangepicker_range = "";
            $(this).val('');
            initTaskRunningTendencyReport()
        });

        //input右侧的图标事件
        $('.daterangepickerdiv i').click(function() {
            $(this).parent().find('input').click();
        });
    }

    const formatDateTime = (date) => {
        let year = date.getFullYear();
        let month = (date.getMonth() + 1).toString().padStart(2, '0');
        let day = date.getDate().toString().padStart(2, '0');
        
        return year + '-' + month + '-' + day;
    };
    
    const initListener = function() {

        initClientReport();

        initVcenterReport();

        initCloudReport();

        initNasReport();

        initCdpReport();

        initAppReport();

        initObsReport();

        initHadoopReport();

        initFileCopyReport();

        initK8sReport();

        initDbProtectReport();

        initDbCopyReport();

        initNodeReport();

        initStorageReport();

        initTaskRunningTendencyReport();

        initAlarmReport();

        // 监听导出
        $('#report_overview_export').off().on('click', reportOverviewExport);
    }

    /**
     * echart图自适应
     */
    const watchEchartSizeChange = () => {
        $(window).on('echart-resize', function() {
            // 设置300毫秒延迟后再重绘是考虑导航栏折叠或展开场景，其page-content-wrapper过渡时间设置的ransition: margin 0.5s ease;，因此要等300毫秒后拿到展开/缩放后的宽高再重绘
            setTimeout(() => {
                // 任务运行趋势图有数据时，导航栏折叠/展开时，echart图要重绘
                if (!$('.running-tendency-content').hasClass('display-none') && taskRunningChart) {
                    let chartWidth = $('.running-tendency-content').width();
                    let chartHeight = $('.running-tendency-content').height();

                    $('#task_running_tendency_echart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});

                    taskRunningChart.resize();
                }

                // 告警图有数据时，导航栏折叠/展开时，echart图要重绘
                if (!$('.alarm-content').hasClass('display-none') && alarmChart) {
                    let chartWidth = $('.alarm-content').width();
                    let chartHeight = $('.alarm-content').height();

                    $('#alarm_echart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});

                    alarmChart.resize();
                }
            }, 300);
        });
    }

    return{
        init: function () {
            initListener();
            initDateSelect();
            inintDatatimePicker();
            watchEchartSizeChange();
        }
    };
}();
jQuery(document).ready(function () {
    overview.init();
});