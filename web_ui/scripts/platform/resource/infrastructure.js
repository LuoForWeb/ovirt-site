/*
 * @Author: ChengJiaFu
 * @Date: 2024-11-27 15:08:27
 * @Description: 基础设施入口界面授权显隐
 * @version: 1.0
 */
var infrastructure = function () {
    const PERMISSION = CONF.PERMISSION; // 获取恢复授权数组

    const INFRASTRUCTURE_GROUP = [
        {
            id: 'vcenter_manager',
            title: LANG.UI_VCENTER_VCENTER,
            des: LANG.UI_RESOURCE_MANAGEMENT_VCENTER_DES,
            icon: 'viconfont vicon-vcenter_manager',
            url: './content/vm/vcenter_manager.php'
        },
        {
            id: 'cloud_platform_private',
            title: LANG.UI_PUBLIC_PRIVATE_CLOUD,
            des: LANG.UI_RESOURCE_MANAGEMENT_PRIVATE_CLOUD_DES,
            icon: 'viconfont vicon-overview-private-cloud',
            url: './content/vm/cloudplatform/cloud_platform_manager.php?cloudType=private'
        },
        {
            id: 'cloud_platform',
            title: LANG.UI_PUBLIC_PUBLIC_CLOUD,
            des: LANG.UI_RESOURCE_MANAGEMENT_PUBLIC_CLOUD_DES,
            icon: 'viconfont vicon-overview-plubic-cloud',
            url: './content/vm/cloudplatform/cloud_platform_manager.php?cloudType=public'
        },
        {
            id: 'client',
            title: LANG.UI_CLIENT_AGENT,
            des: LANG.UI_RESOURCE_MANAGEMENT_CLIENT_DES,
            icon: 'viconfont vicon-client',
            url: './content/client/client.php'
        },
        {
            id: 'nasmanager',
            title: LANG.UI_NAS_BAK_DETAIL_HIS,
            des: LANG.UI_RESOURCE_MANAGEMENT_NAS_DES,
            icon: 'viconfont vicon-nasmanager',
            url: './content/nas/nasmanager.php'
        },
        {
            id: 'obsmanager',
            title: LANG.UI_VISUAL_OBS,
            des: LANG.UI_RESOURCE_MANAGEMENT_OBS_DES,
            icon: 'viconfont vicon-overview-obs',
            url: './content/s3/obsmanager.php'
        },
        {
            id: 'k8s_cluster',
            title: LANG.UI_RESOURCE_MANAGEMENT_K8S_TITLE,
            des: LANG.UI_RESOURCE_MANAGEMENT_K8S_DES,
            icon: 'viconfont vicon-overciew-k8s',
            url: './content/kubernetes/kubernetes_cluster.php'
        },
        {
            id: 'exchange_organization',
            title: 'Microsoft 365',
            des: LANG.UI_RESOURCE_MANAGEMENT_MS365_DES,
            icon: 'viconfont vicon-overview-m365',
            url: './content/exchange/exchange_organization.php'
        },
        {
            id: 'hadoop_cluster',
            title: LANG.UI_TENANT_HADOOP,
            des: LANG.UI_RESOURCE_MANAGEMENT_HADOOP_DES,
            icon: 'viconfont vicon-hadoopmokuai',
            url: './content/hadoop/hadoop_cluster.php'
        },
        {
            id: 'production_storage_manager',
            title: LANG.UI_RESOURCE_MANAGEMENT_LUN_STORAGE,
            des: LANG.UI_RESOURCE_MANAGEMENT_LUN_STORAGE_DES,
            icon: 'viconfont vicon-shengchancunchu1',
            url: './content/platform/storage/lun_storage_manager.php'
        },
        {
            id: 'storage_lanfree',
            title: LANG.UI_RESOURCE_MANAGEMENT_LAN_FREE,
            des: LANG.UI_RESOURCE_MANAGEMENT_LAN_FREE_DES,
            icon: 'viconfont vicon-storage_lanfree',
            url: './content/platform/storage/storage_lanfree.php'
        }
    ]; // 基础设施组

    const AUTHED_INFRASTRUCTURE_GROUP = []; // 实际授权的基础设施组

    /**
     * 渲染路由卡片
     * @param {*} routeItem 
     * @returns 
     */
    const renderRouteCard = (routeItem) => {
        let card =  '<a class="vinchin-wrap__group__card module-card no-btns ajaxify" name="infrastructure" href="' + routeItem.url + '">'  +
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

    const initInfrastructurePage = () => {
        let infrastructureHtml = '';

        INFRASTRUCTURE_GROUP.forEach(item => {
            if (PERMISSION.includes(item.id)) {
                AUTHED_INFRASTRUCTURE_GROUP.push(item);
            }
        });

        if (AUTHED_INFRASTRUCTURE_GROUP.length > 0) {
            infrastructureHtml += '<div class="vinchin-wrap__group">';

            AUTHED_INFRASTRUCTURE_GROUP.forEach(item => {
                infrastructureHtml += renderRouteCard(item);
            });

            infrastructureHtml += '</div>';
        }

        $('#infrastructure').html(infrastructureHtml);
    }
    
    return {
        init: function () {
            initInfrastructurePage();
        }
    }
}();

jQuery(document).ready(function () {
    infrastructure.init();
});