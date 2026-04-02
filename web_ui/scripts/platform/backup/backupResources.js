/*
 * @Author: ChengJiaFu
 * @Date: 2024-11-18 18:12:30
 * @Description: 备份资源入口界面授权显隐
 * @version: 1.0
 */
var backupResources = function () {
    const PERMISSION = CONF.PERMISSION; // 获取恢复授权数组

    const BACKUP_RESOURCES_GROUP = [
        {
            id: 'node',
            title: LANG.UI_BACKUP_NODE,
            des: LANG.UI_BACKUP_RESOURCE_NODE_DES,
            icon: 'viconfont vicon-beifenjiedian',
            url: './content/platform/node/node.php'
        },
        {
            id: 'cluster_manager',
            title: LANG.UI_BACKUP_RESOURCE_CLUSTER_MANAGEMENT,
            des: LANG.UI_BACKUP_RESOURCE_CLUSTER_MANAGEMENT_DES,
            icon: 'viconfont vicon-jiqun1',
            url: './content/platform/cluster/cluster_manager.php'
        },
        {
            id: 'driver_manager',
            title: LANG.UI_BACKUP_RESOURCE_DRIVER_MANAGEMENT,
            des: LANG.UI_BACKUP_RESOURCE_DRIVER_MANAGEMENT_DES,
            icon: 'viconfont vicon-a-Hunting-gearcongdongzhuangzhi',
            url: './content/driver/driver_manager.php'
        },
        {
            id: 'scripts_manager',
            title: LANG.UI_BACKUP_RESOURCE_SCRIPT_MANAGEMENT,
            des: LANG.UI_BACKUP_RESOURCE_SCRIPT_MANAGEMENT_DES,
            icon: 'viconfont vicon-jiaobenguanli',
            url: './content/platform/scripts/scripts_manager.php'
        },
        {
            id: 'global_strategy',
            title: LANG.UI_GLOBAL_STRATEGY_DES,
            des: LANG.UI_BACKUP_RESOURCE_BACKUP_STRATEGY_DES,
            icon: 'viconfont vicon-beifencelve',
            url: './content/platform/strategy/global_strategy.php'
        },
        {
            id: 'global_speed_strategy',
            title: LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_TITLE,
            des: LANG.UI_BACKUP_RESOURCE_SPEED_LIMIT_DES,
            icon: 'viconfont vicon-xiansucelve2',
            url: './content/platform/global_strategy/global_strategy.php'
        },
        {
            id: 'resource_group',
            title: LANG.UI_KUBE_RESOURCE_GROUP,
            des: LANG.UI_BACKUP_RESOURCE_GROUP_DES,
            icon: 'viconfont vicon-a-Cube-fourmofang',
            url: './content/platform/resource/resource_group.php'
        },
        {
            id: 'virus',
            title: LANG.UI_RESOURCE_MANAGEMENT_VIRUS,
            des: LANG.UI_RESOURCE_MANAGEMENT_VIRUS_DES,
            icon: 'viconfont vicon-bingdukuguanli',
            url: './content/virus/virus.php'
        },
    ]; // 备份资源群组

    const AUTHED_BACKUP_RESOURCES_GROUP = []; // 实际授权的备份资源群组

    /**
     * 渲染路由卡片
     * @param {*} routeItem 
     * @returns 
     */
    const renderRouteCard = (routeItem) => {
        let card =  '<a class="vinchin-wrap__group__card module-card no-btns ajaxify" name="backup_manager" href="' + routeItem.url + '">'  +
                        '<div class="upper-region">' + 
                            '<div class="upper-region__left">' + 
                                '<div class="upper-region__left__title mb-8">' + routeItem.title + '</div>' + 
                                '<span class="upper-region__left__des">' + routeItem.des + '</span>' + 
                            '</div>' + 
                            '<div class="upper-region__right">' + 
                                '<i class="' + routeItem.icon + '"></i>' + 
                            '</div>' + 
                        '</div>' +
                    '</a>';
        
        return card;
    }

    const initBackupResourcesPage = () => {
        let backupResourcesHtml = '';
        
        BACKUP_RESOURCES_GROUP.forEach(item => {
            if (PERMISSION.includes(item.id)) {
                AUTHED_BACKUP_RESOURCES_GROUP.push(item);
            }
        });

        if (AUTHED_BACKUP_RESOURCES_GROUP.length > 0) {
            backupResourcesHtml += '<div class="vinchin-wrap__group">';

            AUTHED_BACKUP_RESOURCES_GROUP.forEach(item => {
                backupResourcesHtml += renderRouteCard(item);
            });

            backupResourcesHtml += '</div>';
        }

        $('#backup_resources').html(backupResourcesHtml);
    }

    return {
        init: function () {
            initBackupResourcesPage();
        }
    }
}();

jQuery(document).ready(function () {
    backupResources.init();
});