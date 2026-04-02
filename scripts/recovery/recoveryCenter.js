/*
 * @Author: ChengJiaFu
 * @Date: 2024-11-26 15:07:01
 * @Description: 恢复中心入口界面授权模块显隐
 * @version: 1.1
 */
var recoveryCenter = function () {
    const PERMISSION = CONF.PERMISSION; // 获取恢复授权数组
    let DISPLAYED_SECONDARY_GROUP_CRITICAL_VALUE = 4; // 分两个vinchin-wrap__group临界值，超过4即开始下一个vinchin-wrap__group，保证一个vinchin-wrap__group只有四个子项
    
    const VM_RECOVER_GROUP = [
        {
            id: 'vmprotect',
            title: LANG.UI_COPY_MODULE_LABEL_VM, 
            des: LANG.UI_COPY_MODULE_LABEL_VM_DES,
            icon: 'viconfont vicon-overview-vm',
            firstLineRoutes: [
                {
                    id: 'vmrecover',
                    name: LANG.UI_VISUAL_RECOVERY,
                    type: 'ordinary',
                    url: './content/vm/vmrecover.php'
                },
                {
                    id: 'vm_grain_recovery',
                    name: LANG.UI_RECOVERY_GRAIN,
                    type: 'advanced',
                    url: './content/platform/recovery/graininess.php?recovery_type=vm&subtype=1'
                }
            ], // 第一行路由按钮组
            secondLineRoutes: [
                {
                    id: 'vm_instant_recovery',
                    name: LANG.UI_VISUAL_INSTANT_NAME,
                    type: 'advanced',
                    url: './content/platform/recovery/instantaneous.php?recovery_type=vm&subtype=1'
                },
                {
                    id: 'vm_platform_recovery',
                    name: LANG.UI_FILE_CROSS_RESTORE,
                    type: 'advanced',
                    url: './content/platform/recovery/platform.php?recovery_type=vm&subtype=1'
                }
            ] // 第二行路由按钮组
        },
        {
            id: 'prcloud_protect',
            title: LANG.UI_BACKUP_DATA_MODULE_PRIVATE_CLOUD,
            des: LANG.UI_PUBLIC_PRIVATE_CLOUD_RECOVERY_DES,
            icon: 'viconfont vicon-overview-private-cloud',
            firstLineRoutes: [
                {
                    id: 'vm_prcloud_recovery',
                    name: LANG.UI_VISUAL_RECOVERY,
                    type: 'ordinary',
                    url: './content/vm/vmrecover.php?sub_module_type=2'
                },
                {
                    id: 'vm_prcloud_graininess_recovery',
                    name: LANG.UI_RECOVERY_GRAIN,
                    type: 'advanced',
                    url: './content/platform/recovery/graininess.php?recovery_type=vm&subtype=2'
                }
            ], // 第一行路由按钮组
            secondLineRoutes: [
                {
                    id: 'vm_prcloud_instant_recovery',
                    name: LANG.UI_VISUAL_INSTANT_NAME,
                    type: 'advanced',
                    url: './content/platform/recovery/instantaneous.php?recovery_type=vm&subtype=2'
                },
                {
                    id: 'vm_prcloud_platform_recovery',
                    name: LANG.UI_FILE_CROSS_RESTORE,
                    url: './content/platform/recovery/platform.php?recovery_type=vm&subtype=2'
                }
            ] // 第二行路由按钮组
        },
        {
            id: 'awsprotect',
            title: LANG.UI_PUBLIC_PUBLIC_CLOUD,
            des: LANG.UI_PUBLIC_CLOUD_RECOVERY_DES,
            icon: 'viconfont vicon-overview-plubic-cloud',
            firstLineRoutes: [
                {
                    id: 'vm_awsprotect_recover',
                    name: LANG.UI_VISUAL_RECOVERY,
                    type: 'ordinary',
                    url: './content/aws/awsrecover.php'
                },
                {
                    id: 'vm_awsprotect_grain_recovery',
                    name: LANG.UI_RECOVERY_GRAIN,
                    type: 'advanced',
                    url: './content/platform/recovery/graininess.php?recovery_type=vm&subtype=3'
                }
            ], // 第一行路由按钮组
            secondLineRoutes: [
                {
                    id: 'vm_awsprotect_instant_recovery',
                    name: LANG.UI_VISUAL_INSTANT_NAME,
                    type: 'advanced',
                    url: './content/platform/recovery/instantaneous.php?recovery_type=vm&subtype=3'
                },
                {
                    id: 'vm_awsprotect_platform_recovery',
                    name: LANG.UI_FILE_CROSS_RESTORE,
                    type: 'advanced',
                    url: './content/platform/recovery/platform.php?recovery_type=vm&subtype=3'
                }
            ] // 第二行路由按钮组
        },
        {
            id: 'k8s_protect',
            title: 'Kubernetes',
            des: LANG.UI_PUBLIC_K8S_RECOVERY_DES,
            icon: 'viconfont vicon-overciew-k8s',
            firstLineRoutes: [
                {
                    id: 'k8s_recovery',
                    name: LANG.UI_VISUAL_RECOVERY,
                    type: 'ordinary',
                    url: './content/kubernetes/kubernetes_recovery.php'
                }
            ], // 第一行路由按钮组
            secondLineRoutes: [] // 第二行路由按钮组
        }
    ]; // 虚拟机恢复组

    const FILE_RECOVER_GROUP = [
        {
            id: 'filebackup',
            title: LANG.UI_FILE_FILE,
            des: LANG.UI_PUBLIC_FS_RECOVERY_DES,
            icon: 'viconfont vicon-overview-file',
            firstLineRoutes: [
                {
                    id: 'file_recovery',
                    type: 'ordinary',
                    name: LANG.UI_VISUAL_RECOVERY,
                    url: './content/fs/filerecover.php'
                }
            ], // 第一行路由按钮组
            secondLineRoutes: [] // 第二行路由按钮组
        },
        {
            id: 'nas_protect',
            title: LANG.UI_REPORT_NAS,
            des: LANG.UI_PUBLIC_NAS_RECOVERY_DES,
            icon: 'viconfont vicon-overview-nas',
            firstLineRoutes: [
                {
                    id: 'nas_recovery',
                    name: LANG.UI_VISUAL_RECOVERY,
                    type: 'ordinary',
                    url: './content/nas/nasrecover.php'
                }
            ], // 第一行路由按钮组
            secondLineRoutes: [] // 第二行路由按钮组
        },
        {
            id: 'obs_protect',
            title: LANG.UI_VISUAL_OBS,
            des: LANG.UI_PUBLIC_OBS_RECOVERY_DES,
            icon: 'viconfont vicon-overview-obs',
            firstLineRoutes: [
                {
                    id: 'obs_recovery',
                    name: LANG.UI_VISUAL_RECOVERY,
                    type: 'ordinary',
                    url: './content/s3/obsrecover.php'
                }
            ], // 第一行路由按钮组
            secondLineRoutes: [] // 第二行路由按钮组
        },
        {
            id: 'hadoop_protect',
            title: 'Hadoop HDFS',
            des: LANG.UI_PUBLIC_HADOOP_RECOVERY_DES,
            icon: 'viconfont vicon-overview-hadoop',
            firstLineRoutes: [
                {
                    id: 'hadoop_recovery',
                    name: LANG.UI_VISUAL_RECOVERY,
                    type: 'ordinary',
                    url: './content/hadoop/hadoop_recovery.php'
                }
            ], // 第一行路由按钮组
            secondLineRoutes: [] // 第二行路由按钮组
        }
    ]; // 文件恢复组

    const APPLICATION_DATA_RECOVER_GROUP = [
        {
            id: 'db_protect',
            title: LANG.UI_AGENT_MODULE_DB,
            des: LANG.UI_PUBLIC_DB_RECOVERY_DES,
            icon: 'viconfont vicon-overview-database',
            firstLineRoutes: [
                {
                    id: 'db_recovery',
                    name: LANG.UI_VISUAL_RECOVERY,
                    type: 'ordinary',
                    url: './content/dbprotect/dbrecover.php'
                },
                {
                    id: 'db_drill',
                    name: LANG.UI_RECOVERY_DB_PROTECT_CREATE_DRILL,
                    type: 'advanced',
                    url: './content/dbprotect/dbrecover.php?timepoint_recovery_type=2'
                }
            ], // 第一行路由按钮组
            secondLineRoutes: [] // 第二行路由按钮组
        },
        {
            id: 'office365_protect',
            title: 'Microsoft 365',
            des: LANG.UI_PUBLIC_MS365_RECOVERY_DES,
            icon: 'viconfont vicon-overview-m365',
            firstLineRoutes: [
                {
                    id: 'exchange_recovery',
                    name: LANG.UI_VISUAL_RECOVERY,
                    type: 'ordinary',
                    url: './content/exchange/exchange_recover.php'
                }
            ], // 第一行路由按钮组
            secondLineRoutes: [] // 第二行路由按钮组
        }
    ]; // 应用数据恢复组

    const COMPLETE_MACHINE_RECOVER_GROUP = [
        {
            id: 'complete_machine',
            title: LANG.UI_COPY_MODULE_LABEL_COMPLETE_OS,
            des: LANG.UI_PUBLIC_COMPLETE_OS_RECOVERY_DES,
            icon: 'viconfont vicon-overview-complete-machine',
            firstLineRoutes: [
                {
                    id: 'machine_complete_recovery',
                    name: LANG.UI_VISUAL_RECOVERY,
                    type: 'ordinary',
                    url: './content/complete_machine_os/machine_os_recover.php'
                },
                {
                    id: 'machine_complete_grain_recovery',
                    name: LANG.UI_RECOVERY_GRAIN,
                    type: 'advanced',
                    url: './content/platform/recovery/graininess.php?recovery_type=os'
                }
            ], // 第一行路由按钮组
            secondLineRoutes: [
                {
                    id: 'machine_complete_instant_recovery',
                    name: LANG.UI_VISUAL_INSTANT_NAME,
                    type: 'advanced',
                    url: './content/platform/recovery/instantaneous.php?recovery_type=os'
                },
                {
                    id: 'machine_complete_platform_recovery',
                    name: LANG.UI_FILE_CROSS_RESTORE,
                    type: 'advanced',
                    url: './content/platform/recovery/platform.php?recovery_type=os'
                }
            ] // 第二行路由按钮组
        },
        {
            id: 'osbackup',
            title: LANG.UI_COPY_MODULE_LABEL_REEL_OS,
            des: LANG.UI_PUBLIC_REEL_OS_RECOVERY_DES,
            icon: 'viconfont vicon-overview-volume',
            firstLineRoutes: [
                {
                    id: 'os_recovery',
                    name: LANG.UI_VISUAL_RECOVERY,
                    type: 'ordinary',
                    url: './content/os/osrecover.php'
                }
            ], // 第一行路由按钮组
            secondLineRoutes: [] // 第二行路由按钮组
        }
    ]; // 整机数据恢复组

    const CONTINUOUS_DATA_RECOVER_GROUP = [
        {
            id: 'complete_cdp_backup',
            title: LANG.UI_COPY_MODULE_LABEL_COMPLETE_OS,
            des: LANG.UI_PUBLIC_REAL_TIME_DISK_RECOVERY_DES,
            icon: 'viconfont vicon-zhengjishishi',
            firstLineRoutes: [
                {
                    id: 'machine_complete_volcdp_recovery',
                    name: LANG.UI_VISUAL_RECOVERY,
                    type: 'ordinary',
                    url: './content/complete_machine_volcdp/cm_volcdp_recovery.php'
                },
                {
                    id: 'machine_complete_volcdp_grain_recovery',
                    name: LANG.UI_RECOVERY_GRAIN,
                    type: 'advanced',
                    url: './content/platform/recovery/graininess.php?recovery_type=cdp'
                }
            ], // 第一行路由按钮组
            secondLineRoutes: [
                {
                    id: 'machine_complete_volcdp_platform_recovery',
                    name: LANG.UI_FILE_CROSS_RESTORE,
                    type: 'advanced',
                    url: './content/platform/recovery/platform.php?recovery_type=cdp'
                }
            ] // 第二行路由按钮组
        },
        {
            id: 'vol_cdp_backup',
            title: LANG.UI_COPY_MODULE_LABEL_REEL_OS,
            des: LANG.UI_PUBLIC_REAL_TIME_VOLUME_RECOVERY_DES,
            icon: 'viconfont vicon-zhengjidingshi',
            firstLineRoutes: [
                {
                    id: 'p_vol_cdp_recovery',
                    name: LANG.UI_VISUAL_RECOVERY,
                    type: 'ordinary',
                    url: './content/volcdp/vol_cdp_recover.php'
                }
            ], // 第一行路由按钮组
            secondLineRoutes: [] // 第二行路由按钮组
        },
        {
            id: 'dbcdpcopy',
            title: LANG.UI_AGENT_MODULE_DB,
            des: LANG.UI_PUBLIC_REAL_TIME_DB_RECOVERY_DES,
            icon: 'viconfont vicon-overview-database',
            firstLineRoutes: [
                {
                    id: 'dbcdp_recovery',
                    name: LANG.UI_VISUAL_RECOVERY,
                    type: 'ordinary',
                    url: './content/dbcdp/dbcdp_recover.php'
                }
            ], // 第一行路由按钮组
            secondLineRoutes: [] // 第二行路由按钮组
        }
    ]; // 连续数据恢复组

    const RECOVER_GROUPS = [
        { group: 'VmRecoverGroup', data: VM_RECOVER_GROUP },
        { group: 'FileRecoverGroup', data: FILE_RECOVER_GROUP },
        { group: 'AppDataRecoverGroup', data: APPLICATION_DATA_RECOVER_GROUP },
        { group: 'CompleteMachineGroup', data: COMPLETE_MACHINE_RECOVER_GROUP },
        { group: 'ContinuousDataRecoverGroup', data: CONTINUOUS_DATA_RECOVER_GROUP }
    ]; // 合并为json数组方便遍历

    const realRecoverGroups = {
        realVmRecoverGroup: [],
        realFileRecoverGroup: [],
        realAppDataRecoverGroup: [],
        realCompleteMachineGroup: [],
        realContinuousDataRecoverGroup: []
    }; // 定义实际的授权恢复组

    /**
     * 渲染单个路由按钮的卡片
     * @param {*} routeItem 
     * @returns 
     */
    const renderSingleRouteCard = (routeItem) => {
        let card = '<div class="vinchin-wrap__group__card module-card">' + 
                        '<div class="upper-region">' + 
                            '<div class="upper-region__left">' + 
                                '<div class="upper-region__left__title">' + routeItem.title + '</div>' + 
                                '<span class="upper-region__left__des">' + routeItem.des + '</span>' + 
                            '</div>' + 
                            '<div class="upper-region__right">' + 
                                '<i class="' + routeItem.icon + '"></i>' +
                            '</div>' + 
                        '</div>' + 
                        '<div class="lower-region">' + 
                            '<div class="lower-region__group single-item">' + 
                                '<a class="recover-center btn route-btn" name="' + routeItem.firstLineRoutes[0].type + '" href="' + routeItem.firstLineRoutes[0].url + '"><span class="route-btn__text">' + `${LANG.UI_VISUAL_RECOVERY}` + '</span></a>' +
                            '</div>' + 
                            '<div class="lower-region__group single-item"></div>' + 
                        '</div>' + 
                    '</div>';
        
        return card;
    }

    /**
     * 渲染包含多个路由按钮的卡片
     * @param {*} routeItem 路由项
     * @returns 
     */
    const renderMutipleRoutesCard = (routeItem) => {
        let firstLineRoutesLen = routeItem.firstLineRoutes.length; // 第一行路由组length
        let secondLineRoutesLen = routeItem.secondLineRoutes.length; // 第二行路由组length
        let firstLineRouteHtml = '';
        let secondLineRouteHtml = '';
        let lowerRegionRouteHtml = '';

        switch (`${firstLineRoutesLen}${secondLineRoutesLen}`) {
            case '01':
                firstLineRouteHtml +=   '<div class="lower-region__group single-item">' + 
                                            `<button type="button" class="btn route-btn level1" ${!routeItem.secondLineRoutes[0].hasBackupData ? 'disabled' : ''} title="${routeItem.secondLineRoutes[0].moduleDes}">` + 
                                                '<a class="recover-center" name="' + routeItem.secondLineRoutes[0].type + '" ' + (!routeItem.secondLineRoutes[0].hasBackupData ? '' : ' href="' + routeItem.secondLineRoutes[0].url + '"') + '>' + 
                                                    '<span class="route-btn__text">' + routeItem.secondLineRoutes[0].name + '</span>' + 
                                                '</a>' + 
                                            '</button>' +
                                        '</div>';
                secondLineRouteHtml += '<div class="lower-region__group single-item"></div>';
                break;
            case '10':
                firstLineRouteHtml +=   '<div class="lower-region__group single-item">' + 
                                            `<button type="button" class="btn route-btn level1" ${!routeItem.firstLineRoutes[0].hasBackupData ? 'disabled' : ''} title="${routeItem.firstLineRoutes[0].moduleDes}">` + 
                                                '<a class="recover-center" name="' + routeItem.firstLineRoutes[0].type + '" ' + (!routeItem.firstLineRoutes[0].hasBackupData ? '' : ' href="' + routeItem.firstLineRoutes[0].url + '"') + '>' + 
                                                    '<span class="route-btn__text">' + routeItem.firstLineRoutes[0].name + '</span>' + 
                                                '</a>' + 
                                            '</button>' +
                                        '</div>';
                secondLineRouteHtml += '<div class="lower-region__group single-item"></div>';
                break;
            case '02':
                firstLineRouteHtml +=   '<div class="lower-region__group multiple-items">' + 
                                            `<button type="button" class="btn route-btn level1" ${!routeItem.secondLineRoutes[0].hasBackupData ? 'disabled' : ''} title="${routeItem.secondLineRoutes[0].moduleDes}">` + 
                                                '<a class="recover-center" name="' + routeItem.secondLineRoutes[0].type + '" ' + (!routeItem.secondLineRoutes[0].hasBackupData ? '' : ' href="' + routeItem.secondLineRoutes[0].url + '"') + '>' + 
                                                    '<span class="route-btn__text">' + routeItem.secondLineRoutes[0].name + '</span>' + 
                                                '</a>' +
                                            '</button>' +
                                            `<button type="button" class="btn route-btn level1" ${!routeItem.secondLineRoutes[1].hasBackupData ? 'disabled' : ''} title="${routeItem.secondLineRoutes[1].moduleDes}">` + 
                                                '<a class="recover-center" name="' + routeItem.secondLineRoutes[1].type + '" ' + (!routeItem.secondLineRoutes[1].hasBackupData ? '' : ' href="' + routeItem.secondLineRoutes[1].url + '"') + '>' + 
                                                    '<span class="route-btn__text">' + routeItem.secondLineRoutes[1].name + '</span>' + 
                                                '</a>' +
                                            '</button>' +
                                        '</div>';
                secondLineRouteHtml += '<div class="lower-region__group single-item"></div>';
                break;
            case '03':
                firstLineRouteHtml +=   '<div class="lower-region__group multiple-items">' + 
                                            `<button type="button" class="btn route-btn level1" ${!routeItem.secondLineRoutes[0].hasBackupData ? 'disabled' : ''} title="${routeItem.secondLineRoutes[0].moduleDes}">` + 
                                                '<a class="recover-center" name="' + routeItem.secondLineRoutes[0].type + '" ' + (!routeItem.secondLineRoutes[0].hasBackupData ? '' : ' href="' + routeItem.secondLineRoutes[0].url + '"') + '>' + 
                                                    '<span class="route-btn__text">' + routeItem.secondLineRoutes[0].name + '</span>' + 
                                                '</a>' +
                                            '</button>' +
                                            `<button type="button" class="btn route-btn level1" ${!routeItem.secondLineRoutes[1].hasBackupData ? 'disabled' : ''} title="${routeItem.secondLineRoutes[1].moduleDes}">` + 
                                                '<a class="recover-center" name="' + routeItem.secondLineRoutes[1].type + '" ' + (!routeItem.secondLineRoutes[1].hasBackupData ? '' : ' href="' + routeItem.secondLineRoutes[1].url + '"') + '>' + 
                                                    '<span class="route-btn__text">' + routeItem.secondLineRoutes[1].name + '</span>' + 
                                                '</a>' +
                                            '</button>' +
                                        '</div>';
                secondLineRouteHtml +=  '<div class="lower-region__group multiple-items">' + 
                                            `<button type="button" class="btn route-btn level1" ${!routeItem.secondLineRoutes[2].hasBackupData ? 'disabled' : ''} title="${routeItem.secondLineRoutes[2].moduleDes}">` + 
                                                '<a class="recover-center" name="' + routeItem.secondLineRoutes[2].type + '" ' + (!routeItem.secondLineRoutes[2].hasBackupData ? '' : ' href="' + routeItem.secondLineRoutes[2].url + '"') + '>' + 
                                                    '<span class="route-btn__text">' + routeItem.secondLineRoutes[2].name + '</span>' + 
                                                '</a>' +
                                            '</button>' +
                                        '</div>';
                break;
            case '20':
                firstLineRouteHtml +=   '<div class="lower-region__group multiple-items">' + 
                                            `<button type="button" class="btn route-btn level1" ${!routeItem.firstLineRoutes[0].hasBackupData ? 'disabled' : ''} title="${routeItem.firstLineRoutes[0].moduleDes}">` + 
                                                '<a class="recover-center" name="' + routeItem.firstLineRoutes[0].type + '" ' + (!routeItem.firstLineRoutes[0].hasBackupData ? '' : ' href="' + routeItem.firstLineRoutes[0].url + '"') + '>' + 
                                                    '<span class="route-btn__text">' + routeItem.firstLineRoutes[0].name + '</span>' + 
                                                '</a>' +
                                            '</button>' + 
                                            `<button type="button" class="btn route-btn level1" ${!routeItem.firstLineRoutes[1].hasBackupData ? 'disabled' : ''} title="${routeItem.firstLineRoutes[1].moduleDes}">` + 
                                                '<a class="recover-center" name="' + routeItem.firstLineRoutes[1].type + '" ' + (!routeItem.firstLineRoutes[1].hasBackupData ? '' : ' href="' + routeItem.firstLineRoutes[1].url + '"') + '>' + 
                                                    '<span class="route-btn__text">' + routeItem.firstLineRoutes[1].name + '</span>' + 
                                                '</a>' +
                                            '</button>' +
                                        '</div>';
                secondLineRouteHtml += '<div class="lower-region__group single-item"></div>'; 
                break;
            case '11':
                firstLineRouteHtml +=   '<div class="lower-region__group multiple-items">' + 
                                            `<button type="button" class="btn route-btn level1" ${!routeItem.firstLineRoutes[0].hasBackupData ? 'disabled' : ''} title="${routeItem.firstLineRoutes[0].moduleDes}">` + 
                                                '<a class="recover-center" name="' + routeItem.firstLineRoutes[0].type + '" ' + (!routeItem.firstLineRoutes[0].hasBackupData ? '' : ' href="' + routeItem.firstLineRoutes[0].url + '"') + '>' + 
                                                    '<span class="route-btn__text">' + routeItem.firstLineRoutes[0].name + '</span>' + 
                                                '</a>' +
                                            '</button>' + 
                                            `<button type="button" class="btn route-btn level1" ${!routeItem.secondLineRoutes[0].hasBackupData ? 'disabled' : ''} title="${routeItem.secondLineRoutes[0].moduleDes}">` + 
                                                '<a class="recover-center" name="' + routeItem.secondLineRoutes[0].type + '" ' + (!routeItem.secondLineRoutes[0].hasBackupData ? '' : ' href="' + routeItem.secondLineRoutes[0].url + '"') + '>' + 
                                                    '<span class="route-btn__text">' + routeItem.secondLineRoutes[0].name + '</span>' + 
                                                '</a>' +
                                            '</button>' +
                                        '</div>';
                secondLineRouteHtml += '<div class="lower-region__group single-item"></div>';
                break;
            case '12':
                firstLineRouteHtml +=   '<div class="lower-region__group multiple-items">' + 
                                            `<button type="button" class="btn route-btn level1" ${!routeItem.firstLineRoutes[0].hasBackupData ? 'disabled' : ''} title="${routeItem.firstLineRoutes[0].moduleDes}">` + 
                                                '<a class="recover-center" name="' + routeItem.firstLineRoutes[0].type + '" ' + (!routeItem.firstLineRoutes[0].hasBackupData ? '' : ' href="' + routeItem.firstLineRoutes[0].url + '"') + '>' + 
                                                    '<span class="route-btn__text">' + routeItem.firstLineRoutes[0].name + '</span>' + 
                                                '</a>' +
                                            '</button>' +
                                            `<button type="button" class="btn route-btn level1" ${!routeItem.secondLineRoutes[0].hasBackupData ? 'disabled' : ''} title="${routeItem.secondLineRoutes[0].moduleDes}">` + 
                                                '<a class="recover-center" name="' + routeItem.secondLineRoutes[0].type + '" ' + (!routeItem.secondLineRoutes[0].hasBackupData ? '' : ' href="' + routeItem.secondLineRoutes[0].url + '"') + '>' + 
                                                    '<span class="route-btn__text">' + routeItem.secondLineRoutes[0].name + '</span>' + 
                                                '</a>' +
                                            '</button>' +
                                        '</div>';
                secondLineRouteHtml +=  '<div class="lower-region__group multiple-items">' + 
                                            `<button type="button" class="btn route-btn level1" ${!routeItem.secondLineRoutes[1].hasBackupData ? 'disabled' : ''} title="${routeItem.secondLineRoutes[1].moduleDes}">` + 
                                                '<a class="recover-center" name="' + routeItem.secondLineRoutes[1].type + '" ' + (!routeItem.secondLineRoutes[1].hasBackupData ? '' : ' href="' + routeItem.secondLineRoutes[1].url + '"') + '>' + 
                                                    '<span class="route-btn__text">' + routeItem.secondLineRoutes[1].name + '</span>' + 
                                                '</a>' +
                                            '</button>' + 
                                        '</div>';
                break;
            case '13':
                firstLineRouteHtml +=   '<div class="lower-region__group multiple-items">' + 
                                            `<button type="button" class="btn route-btn level1" ${!routeItem.firstLineRoutes[0].hasBackupData ? 'disabled' : ''} title="${routeItem.firstLineRoutes[0].moduleDes}">` + 
                                                '<a class="recover-center" name="' + routeItem.firstLineRoutes[0].type + '" ' + (!routeItem.firstLineRoutes[0].hasBackupData ? '' : ' href="' + routeItem.firstLineRoutes[0].url + '"') + '>' + 
                                                    '<span class="route-btn__text">' + routeItem.firstLineRoutes[0].name + '</span>' + 
                                                '</a>' +
                                            '</button>' +
                                            `<button type="button" class="btn route-btn level1" ${!routeItem.secondLineRoutes[0].hasBackupData ? 'disabled' : ''} title="${routeItem.secondLineRoutes[0].moduleDes}">` + 
                                                '<a class="recover-center" name="' + routeItem.secondLineRoutes[0].type + '" ' + (!routeItem.secondLineRoutes[0].hasBackupData ? '' : ' href="' + routeItem.secondLineRoutes[0].url + '"') + '>' + 
                                                    '<span class="route-btn__text">' + routeItem.secondLineRoutes[0].name + '</span>' + 
                                                '</a>' +
                                            '</button>' +
                                        '</div>';
                secondLineRouteHtml +=  '<div class="lower-region__group multiple-items">' + 
                                            `<button type="button" class="btn route-btn level1" ${!routeItem.secondLineRoutes[1].hasBackupData ? 'disabled' : ''} title="${routeItem.secondLineRoutes[1].moduleDes}">` + 
                                                '<a class="recover-center" name="' + routeItem.secondLineRoutes[1].type + '" ' + (!routeItem.secondLineRoutes[1].hasBackupData ? '' : ' href="' + routeItem.secondLineRoutes[1].url + '"') + '>' + 
                                                    '<span class="route-btn__text">' + routeItem.secondLineRoutes[1].name + '</span>' + 
                                                '</a>' +
                                            '</button>' + 
                                            `<button type="button" class="btn route-btn level1" ${!routeItem.secondLineRoutes[2].hasBackupData ? 'disabled' : ''} title="${routeItem.secondLineRoutes[2].moduleDes}">` + 
                                                '<a class="recover-center" name="' + routeItem.secondLineRoutes[2].type + '" ' + (!routeItem.secondLineRoutes[2].hasBackupData ? '' : ' href="' + routeItem.secondLineRoutes[2].url + '"') + '>' + 
                                                    '<span class="route-btn__text">' + routeItem.secondLineRoutes[2].name + '</span>' + 
                                                '</a>' +
                                            '</button>' +
                                        '</div>';
                break;
            case '21':
                firstLineRouteHtml +=   '<div class="lower-region__group multiple-items">' + 
                                            `<button type="button" class="btn route-btn level1" ${!routeItem.firstLineRoutes[0].hasBackupData ? 'disabled' : ''} title="${routeItem.firstLineRoutes[0].moduleDes}">` + 
                                                '<a class="recover-center" name="' + routeItem.firstLineRoutes[0].type + '" ' + (!routeItem.firstLineRoutes[0].hasBackupData ? '' : ' href="' + routeItem.firstLineRoutes[0].url + '"') + '>' + 
                                                    '<span class="route-btn__text">' + routeItem.firstLineRoutes[0].name + '</span>' + 
                                                '</a>' +
                                            '</button>' +
                                            `<button type="button" class="btn route-btn level1" ${!routeItem.firstLineRoutes[1].hasBackupData ? 'disabled' : ''} title="${routeItem.firstLineRoutes[1].moduleDes}">` + 
                                                '<a class="recover-center" name="' + routeItem.firstLineRoutes[1].type + '" ' + (!routeItem.firstLineRoutes[1].hasBackupData ? '' : ' href="' + routeItem.firstLineRoutes[1].url + '"') + '>' + 
                                                    '<span class="route-btn__text">' + routeItem.firstLineRoutes[1].name + '</span>' + 
                                                '</a>' +
                                            '</button>' +
                                        '</div>';
                secondLineRouteHtml +=  '<div class="lower-region__group multiple-items">' + 
                                            `<button type="button" class="btn route-btn level1" ${!routeItem.secondLineRoutes[0].hasBackupData ? 'disabled' : ''} title="${routeItem.secondLineRoutes[0].moduleDes}">` + 
                                                '<a class="recover-center" name="' + routeItem.secondLineRoutes[0].type + '" ' + (!routeItem.secondLineRoutes[0].hasBackupData ? '' : ' href="' + routeItem.secondLineRoutes[0].url + '"') + '>' + 
                                                    '<span class="route-btn__text">' + routeItem.secondLineRoutes[0].name + '</span>' + 
                                                '</a>' +
                                            '</button>' +
                                        '</div>';
                break;
            case '22':
                firstLineRouteHtml +=   '<div class="lower-region__group multiple-items">' + 
                                            `<button type="button" class="btn route-btn level1" ${!routeItem.firstLineRoutes[0].hasBackupData ? 'disabled' : ''} title="${routeItem.firstLineRoutes[0].moduleDes}">` + 
                                                '<a class="recover-center" name="' + routeItem.firstLineRoutes[0].type + '" ' + (!routeItem.firstLineRoutes[0].hasBackupData ? '' : ' href="' + routeItem.firstLineRoutes[0].url + '"') + '>' + 
                                                    '<span class="route-btn__text">' + routeItem.firstLineRoutes[0].name + '</span>' + 
                                                '</a>' +
                                            '</button>' +
                                            `<button type="button" class="btn route-btn level1" ${!routeItem.firstLineRoutes[1].hasBackupData ? 'disabled' : ''} title="${routeItem.firstLineRoutes[1].moduleDes}">` + 
                                                '<a class="recover-center" name="' + routeItem.firstLineRoutes[1].type + '" ' + (!routeItem.firstLineRoutes[1].hasBackupData ? '' : ' href="' + routeItem.firstLineRoutes[1].url + '"') + '>' + 
                                                    '<span class="route-btn__text">' + routeItem.firstLineRoutes[1].name + '</span>' + 
                                                '</a>' +
                                            '</button>' +
                                        '</div>';
                secondLineRouteHtml +=  '<div class="lower-region__group multiple-items">' + 
                                            `<button type="button" class="btn route-btn level1" ${!routeItem.secondLineRoutes[0].hasBackupData ? 'disabled' : ''} title="${routeItem.secondLineRoutes[0].moduleDes}">` + 
                                                '<a class="recover-center" name="' + routeItem.secondLineRoutes[0].type + '" ' + (!routeItem.secondLineRoutes[0].hasBackupData ? '' : ' href="' + routeItem.secondLineRoutes[0].url + '"') + '>' + 
                                                    '<span class="route-btn__text">' + routeItem.secondLineRoutes[0].name + '</span>' + 
                                                '</a>' +
                                            '</button>' +
                                            `<button type="button" class="btn route-btn level1" ${!routeItem.secondLineRoutes[1].hasBackupData ? 'disabled' : ''} title="${routeItem.secondLineRoutes[1].moduleDes}">` + 
                                                '<a class="recover-center" name="' + routeItem.secondLineRoutes[1].type + '" ' + (!routeItem.secondLineRoutes[1].hasBackupData ? '' : ' href="' + routeItem.secondLineRoutes[1].url + '"') + '>' + 
                                                    '<span class="route-btn__text">' + routeItem.secondLineRoutes[1].name + '</span>' + 
                                                '</a>' +
                                            '</button>' + 
                                        '</div>';
                break;
            case '23':
                firstLineRouteHtml +=   '<div class="lower-region__group multiple-items">' + 
                                            `<button type="button" class="btn route-btn level1" ${!routeItem.firstLineRoutes[0].hasBackupData ? 'disabled' : ''} title="${routeItem.firstLineRoutes[0].moduleDes}">` + 
                                                '<a class="recover-center" name="' + routeItem.firstLineRoutes[0].type + '" ' + (!routeItem.firstLineRoutes[0].hasBackupData ? '' : ' href="' + routeItem.firstLineRoutes[0].url + '"') + '>' + 
                                                    '<span class="route-btn__text">' + routeItem.firstLineRoutes[0].name + '</span>' + 
                                                '</a>' +
                                            '</button>' +
                                            `<button type="button" class="btn route-btn level1" ${!routeItem.firstLineRoutes[1].hasBackupData ? 'disabled' : ''} title="${routeItem.firstLineRoutes[1].moduleDes}">` + 
                                                '<a class="recover-center" name="' + routeItem.firstLineRoutes[1].type + '" ' + (!routeItem.firstLineRoutes[1].hasBackupData ? '' : ' href="' + routeItem.firstLineRoutes[1].url + '"') + '>' + 
                                                    '<span class="route-btn__text">' + routeItem.firstLineRoutes[1].name + '</span>' + 
                                                '</a>' +
                                            '</button>' + 
                                        '</div>';
                secondLineRouteHtml +=  '<div class="lower-region__group multiple-items">' + 
                                            '<div class="btn-group recover-dropdown-wrapper">' + 
                                                `<button type="button" class="btn dropdown-toggle" data-toggle="dropdown" data-click="dropdown" data-delay="1000" data-close-others="true" ${!routeItem.secondLineRoutes[0].hasBackupData ? 'disabled' : ''} title="${routeItem.secondLineRoutes[0].moduleDes}">` + 
                                                    `${LANG.UI_VISUAL_INSTANT_NAME} <i class="fa fa-angle-down"></i>` + 
                                                '</button>' + 
                                                '<ul class="dropdown-menu" role="menu">' + 
                                                    '<li>' + 
                                                        `<button type="button" class="btn route-btn level2" ${!routeItem.secondLineRoutes[0].hasBackupData ? 'disabled' : ''}>` + 
                                                            '<a class="recover-center" name="' + routeItem.secondLineRoutes[0].type + '" ' + (!routeItem.secondLineRoutes[0].hasBackupData ? '' : ' href="' + routeItem.secondLineRoutes[0].url + '"') + '>' + 
                                                                '<span class="route-btn__text">' + routeItem.secondLineRoutes[0].name + '</span>' + 
                                                            '</a>' +
                                                        '</button>' + 
                                                    '</li>' +
                                                    '<li>' + 
                                                        `<button type="button" class="btn route-btn level2" ${!routeItem.secondLineRoutes[1].hasBackupData ? 'disabled' : ''}>` + 
                                                            '<a class="recover-center" name="' + routeItem.secondLineRoutes[1].type + '" ' + (!routeItem.secondLineRoutes[1].hasBackupData ? '' : ' href="' + routeItem.secondLineRoutes[1].url + '"') + '>' + 
                                                                '<span class="route-btn__text">' + routeItem.secondLineRoutes[1].name + '</span>' + 
                                                            '</a>' +
                                                        '</button>' + 
                                                    '</li>' +
                                                '</ul>' + 
                                            '</div>' + 
                                            `<button type="button" class="btn route-btn level1" ${!routeItem.secondLineRoutes[2].hasBackupData ? 'disabled' : ''} title="${routeItem.secondLineRoutes[2].moduleDes}">` + 
                                                '<a class="recover-center" name="' + routeItem.secondLineRoutes[2].type + '" ' + (!routeItem.secondLineRoutes[2].hasBackupData ? '' : ' href="' + routeItem.secondLineRoutes[2].url + '"') + '>' + 
                                                    '<span class="route-btn__text">' + routeItem.secondLineRoutes[2].name + '</span>' + 
                                                '</a>' +
                                            '</button>' + 
                                        '</div>';
                break;
            default:
                break;
        }

        firstLineRouteHtml = addTooltipToCrossRestore(firstLineRouteHtml);
        secondLineRouteHtml = addTooltipToCrossRestore(secondLineRouteHtml);

        lowerRegionRouteHtml = firstLineRouteHtml + secondLineRouteHtml;

        // </flag> 的作用是合并所有卡片HTML字符串之后，用于正则匹配单个卡片结尾所用，勿删！
        let card = '<div class="vinchin-wrap__group__card module-card">' + 
                        '<div class="upper-region">' + 
                            '<div class="upper-region__left">' + 
                                '<div class="upper-region__left__title">' + routeItem.title + '</div>' + 
                                '<div class="upper-region__left__des">' + '<span class="ellipsis-text" title="' + routeItem.des + '">' + routeItem.des + '</span>' + '</div>' + 
                            '</div>' + 
                            '<div class="upper-region__right">' + 
                                '<i class="' + routeItem.icon + '"></i>' +
                            '</div>' + 
                        '</div>' + 
                        '<div class="lower-region">' + 
                            lowerRegionRouteHtml +
                        '</div>' + 
                        '</flag>' + 
                    '</div>';
        
        return card;
    }

    /**
     * 英文环境下，给未禁用的 Cross-Platform Restore 按钮添加tooltip
     * @param {*} htmlString 
     * @returns 
     */
    const addTooltipToCrossRestore = (htmlString) => {
        if (!htmlString) return '';
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = htmlString;
        const buttons = tempDiv.querySelectorAll('.route-btn');
        buttons.forEach(button => {
            if (!button.disabled) {
                const textSpan = button.querySelector('.route-btn__text');
                if (textSpan && textSpan.textContent === 'Cross-Platform Restore') {
                    const aTag = button.querySelector('a.recover-center');
                    if (aTag) {
                        aTag.setAttribute('data-toggle', 'tooltip');
                        aTag.setAttribute('data-placement', 'bottom-start');
                        aTag.setAttribute('title', textSpan.textContent);
                    }
                }
            }
        });

        return tempDiv.innerHTML;
    };

    /**
     * 初始化虚拟机恢复组
     * @returns html
     */
    const initMutipleRoutesRecoverGroup = (group) => {
        if (group.length === 0) {
            return '';
        }

        let resultHtml = '';
        
        group.forEach(item => {
            resultHtml += renderMutipleRoutesCard(item);
        });

        return resultHtml;
    }

    /**
     * 初始化整机数据恢复组
     * @returns 
     */
    const initCompleteMachineRecoverGroup = (group) => {
        if (group.length === 0) {
            return '';
        }

        let resultHtml = '';

        // 如果授权的整机数据恢复组中 整机（磁盘）complete_machine 和 整机（卷）osbackup 只含有其中一个，则展示的title要改为 整机
        let needRename = false;
        let hasCompleteMachine = PERMISSION.includes('complete_machine');
        let hasOsbackup = PERMISSION.includes('osbackup');

        if (hasCompleteMachine !== hasOsbackup) {
            needRename = true;
        }
                    
        group.forEach(item => {
            if (item.id === 'complete_machine') { // 如果是整机（磁盘）恢复，则需要考虑多个二级授权
                item.title = needRename ? LANG.UI_BACKUP_DATA_MODULE_OS : LANG.UI_COPY_MODULE_LABEL_COMPLETE_OS;
            } else { // 如果是整机（卷）恢复，那二级就只有一个授权恢复
                item.title = needRename ? LANG.UI_BACKUP_DATA_MODULE_OS : LANG.UI_COPY_MODULE_LABEL_REEL_OS;
            }
            
            resultHtml += renderMutipleRoutesCard(item);
        });

        return resultHtml;
    }

    /**
     * 初始化备份数据恢复群组
     */
    const initBackupDataRecoverGroups = () => {
        let resultHtml = ''; // 备份数据恢复群组HTML
    
        // 获取屏幕宽度
        const screenWidth = window.innerWidth;
        DISPLAYED_SECONDARY_GROUP_CRITICAL_VALUE = screenWidth > 1440 ? 4 : 3;
    
        // 计算总长度
        let len = realRecoverGroups.realVmRecoverGroup.length + 
                  realRecoverGroups.realFileRecoverGroup.length + 
                  realRecoverGroups.realAppDataRecoverGroup.length + 
                  realRecoverGroups.realCompleteMachineGroup.length;
    
        if (len === 0) {
            resultHtml = '';
        } else {
            // 初始化虚拟机恢复组
            let vmRoutesCardHtml = initMutipleRoutesRecoverGroup(realRecoverGroups.realVmRecoverGroup);
    
            // 初始化文件恢复组
            let fileRoutesCardHtml = initMutipleRoutesRecoverGroup(realRecoverGroups.realFileRecoverGroup);
    
            // 初始化应用恢复组
            let appRoutesCardHtml = initMutipleRoutesRecoverGroup(realRecoverGroups.realAppDataRecoverGroup);
    
            // 初始化整机恢复组
            let completeMachineRoutesCardHtml = initCompleteMachineRecoverGroup(realRecoverGroups.realCompleteMachineGroup);
    
            // 合并所有卡片HTML
            let allCardsHtml = vmRoutesCardHtml + fileRoutesCardHtml + appRoutesCardHtml + completeMachineRoutesCardHtml;
    
            // /<div class="vinchin-wrap__group__card module-card">[\s\S]*?<\/flag><\/div>/g：确保匹配到每个完整的 vinchin-wrap__group__card 结构，包括其所有的子元素和结束标签
            // [\s\S]*?：非贪婪匹配任意字符（包括换行符），确保匹配到整个卡片内容
            // <\/flag><\/div>：匹配卡片的结束标签
            const cardsArray = allCardsHtml.match(/<div class="vinchin-wrap__group__card module-card">[\s\S]*?<\/flag><\/div>/g) || [];

            let groupHtml = '';
            let currentGroup = '';
            let cardCount = 0;
    
            cardsArray.forEach(card => {
                if (cardCount === 0) {
                    currentGroup = '<div class="vinchin-wrap__group">';
                }
                currentGroup += card;
                cardCount++;
    
                if (cardCount === DISPLAYED_SECONDARY_GROUP_CRITICAL_VALUE) { // 等于临界值DISPLAYED_SECONDARY_GROUP_CRITICAL_VALUE，则添加到groupHtml中，并重置currentGroup和cardCount
                    groupHtml += currentGroup + '</div>';
                    currentGroup = '';
                    cardCount = 0;
                }
            });
    
            // 处理剩余的卡片
            if (cardCount > 0) {
                groupHtml += currentGroup + '</div>';
            }
    
            if (groupHtml !== '') {
                resultHtml +=   '<div class="vinchin-wrap__header">' + 
                                    '<span class="decoration me-8"></span>' + 
                                    `<span class="vinchin-wrap__header__text">${LANG.UI_BACKUP_DATA_RECOVERY}</span>` + 
                                '</div>';
                resultHtml += groupHtml;
            }
        }
    
        return resultHtml;
    }

    /**
     * 初始化连续数据保护恢复群组
     */
    const initContinuousDataRecoverGroups = () => {
        let resultHtml = '<div class="vinchin-wrap__group">';

        // 如果授权的连续数据保护恢复组中 整机（磁盘）complete_cdp_backup 和 整机（卷）vol_cdp_backup 只含有其中一个，则展示的title要改为 整机
        let needRename = false;
        let hasCompleteMachine = PERMISSION.includes('complete_cdp_backup');
        let hasOsbackup = PERMISSION.includes('vol_cdp_backup');

        if (hasCompleteMachine !== hasOsbackup) {
            needRename = true;
        }

        realRecoverGroups.realContinuousDataRecoverGroup.forEach(item => {
            if (item.id === 'complete_cdp_backup') { // 如果是整机（磁盘）连续数据恢复，则需要考虑多个二级授权
                item.title = needRename ? LANG.UI_BACKUP_DATA_MODULE_OS_REAL : LANG.UI_BACKUP_DATA_MODULE_OS;
            } else if (item.id === 'vol_cdp_backup') { // 如果是整机（卷）或数据库连续数据恢复，那二级就只有一个授权恢复
                item.title = needRename ? LANG.UI_BACKUP_DATA_MODULE_OS_REAL : LANG.UI_COPY_MODULE_LABEL_REEL_OS;
            } 

            resultHtml += renderMutipleRoutesCard(item);
        });

        return resultHtml += '</div>';
    }

    /**
     * 动态调整备份数据恢复群组和连续数据恢复群组HTML
     */
    const dynamicAdjustRecoverGroupHtml = () => {
        let wholeBackupDataRecoverHtml = ''; // 整个备份数据恢复群组HTML
        let wholeContinuousDataRecoverHtml = ''; // 整个连续数据保护恢复群组HTML

        // 初始化备份数据恢复群组
        wholeBackupDataRecoverHtml += initBackupDataRecoverGroups();

        let groupDes = '';
        let authedCmCdpFlag = CONF.PERMISSION.includes('complete_cdp_backup');
        let authVolCdpFlag = CONF.PERMISSION.includes('vol_cdp_backup');
        let authedDbCdpFlag = CONF.PERMISSION.includes('dbcdpbackup');

        if (authedCmCdpFlag || authVolCdpFlag) { // 授权整机或卷实时其中一个，描述加上  实时保护
            groupDes = LANG.UI_REAL_TIME_PROTECT_RECOVERY;

            if (authedDbCdpFlag) {
                groupDes = LANG.UI_CONTINUOUS_DATA_PROTECT_REPLICATION_DATA_RECOVERY;
            }
        } else { // 整机和卷实时都没有授权
            if (authedDbCdpFlag) {
                groupDes = LANG.UI_REPLICATION_DISASTER_RECOVERY;
            }
        }
                    
        // 初始化连续数据恢复群组
        if (realRecoverGroups.realContinuousDataRecoverGroup.length > 0) {
            wholeContinuousDataRecoverHtml +=   '<div class="vinchin-wrap__header continuous-recover-group-header">' + 
                                                    '<span class="decoration me-8"></span>' + 
                                                    `<span class="vinchin-wrap__header__text">${groupDes}</span>` + 
                                                '</div>';
            wholeContinuousDataRecoverHtml += initContinuousDataRecoverGroups();
        }
        
        $('#recovery_center').html(`${wholeBackupDataRecoverHtml}${wholeContinuousDataRecoverHtml}`);

        $('[data-toggle="tooltip"]').tooltip();

        $('.recover-center').on('click', function(e) {
            e.preventDefault();
            let url = $(this).attr("href");
            let navigation = 'recovery';
            let type = $(this).attr("name");

            // 如果是高级恢复 并且授权已过期，提示用户 且不能跳转
            if (type === 'advanced' && !CONF.AUTH_NOT_EXPIRED) {
                UIToastr.showWarning(LANG.UI_LICENSE_AUTH_INFO_TITLE, LANG.UI_LICENSE_AUTH_INFO_EXPIRED);
                return;
            }
            
            LOCATION(url, navigation);
        });
    }

    /**
	 * 初始化整个恢复中心页面 
	 */
	const initRecoverCenterPage = () => {
        Metronic.blockUI({target: '.vinchin-wrap',animate: true});
		pAjaxRequest({}, '/api/v1/system/config/recover_permission', 'GET', (res) => {
            try {
                if (res.success) {
                    let permissions = Object.values(res.data.permissions);
                    let permissionToHasDatas = res.data.permissionToHasDatas;
                    
                    // 创建 PERMISSION 的映射对象
                    const permissionMap = {};
                    permissionToHasDatas.forEach(permission => {
                        permissionMap[permission.id] = `${permission.hasBackupData}|${permission.moduleDes}`;
                    });

                    // 根据授权的PERMISSION获取各个组的实际数据项 
                    RECOVER_GROUPS.forEach(groupInfo => {
                        const groupName = groupInfo.group;
                        const groupData = groupInfo.data;
                        const realGroup = realRecoverGroups[`real${groupName}`];

                        groupData.forEach(item => {
                            // 一级权限过滤
                            if (permissions.includes(item.id)) {
                                // 深拷贝 item 以避免修改原始数据
                                const newItem = { ...item };
                                newItem.firstLineRoutes = item.firstLineRoutes.filter(route => {
                                    // 二级权限过滤
                                    if (permissions.includes(route.id) && permissionMap[route.id] !== undefined) {
                                        route.hasBackupData = permissionMap[route.id].split('|')[0] === 'true' ? true : false; // 使用 permissionMap 设置 hasBackupData 属性
                                        route.moduleDes = permissionMap[route.id].split('|')[0] !== 'true' ? 
                                            `${LANG.UI_NO_AVALIABLE_BACKUP_TIMEPOINT}${permissionMap[route.id].split('|')[1]}${LANG.UI_PUBLIC_BACKUP}` : 
                                            ''; // 使用 permissionMap 设置 moduleDes 属性
                                        return true;
                                    }
                                    return false;
                                });

                                newItem.secondLineRoutes = item.secondLineRoutes.filter(route => {
                                    // 二级权限过滤
                                    if (permissions.includes(route.id) && permissionMap[route.id] !== undefined) {
                                        route.hasBackupData = permissionMap[route.id].split('|')[0] === 'true' ? true : false; // 使用 permissionMap 设置 hasBackupData 属性
                                        route.moduleDes = permissionMap[route.id].split('|')[0] !== 'true' ? 
                                            `${LANG.UI_NO_AVALIABLE_BACKUP_TIMEPOINT}${permissionMap[route.id].split('|')[1]}${LANG.UI_PUBLIC_BACKUP}` : 
                                            ''; // 使用 permissionMap 设置 moduleDes 属性
                                        return true;
                                    }
                                    return false;
                                });

                                realGroup.push(newItem);
                            }
                        });
                    });

                    dynamicAdjustRecoverGroupHtml();
                } else {
                    UIToastr.showWarning(LANG.UI_GET_RECOVER_CENTER_DATA_ERROR);
                }
            } catch (error) {
                UIToastr.showWarning(LANG.UI_GET_RECOVER_CENTER_DATA_ERROR)
            } finally {
                Metronic.unblockUI('.vinchin-wrap');
            }
		});
	}

    return {
        init: function () {
            initRecoverCenterPage();

            // 分辨率动态调整每行卡片个数
            window.addEventListener('resize', dynamicAdjustRecoverGroupHtml);
        }
    };
}();

jQuery(document).ready(function () {
    recoveryCenter.init();
});
