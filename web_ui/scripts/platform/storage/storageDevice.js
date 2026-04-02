/*
 * @Author: ChengJiaFu
 * @Date: 2024-11-27 16:12:27
 * @Description: 存储管理入口界面授权显隐
 * @version: 1.0
 */
var storageDevice = function () {
    const PERMISSION = CONF.PERMISSION; // 获取恢复授权数组

    const STORAGE_DEVICE_GROUP = [
        {
            id: 'manager',
            title: LANG.UI_REPORT_STORAGE,
            des: LANG.UI_RESOURCE_MANAGEMENT_STORAGE_DES,
            icon: 'viconfont vicon-beifencunchu1',
            url: './content/platform/storage/storage.php'
        },
        {
            id: 'tape_manage',
            title: LANG.UI_RESOURCE_MANAGEMENT_TAPE,
            des: LANG.UI_RESOURCE_MANAGEMENT_TAPE_DES,
            icon: 'viconfont vicon-a-Mail-packageyoujianbao',
            url: './content/platform/resource/tape_equipment.php'
        },
    ]; // 存储管理组

    const AUTHED_STORAGE_DEVICE_GROUP = []; // 实际授权的存储设备数组

    /**
     * 渲染单个路由的卡片
     * @param {*} routeItem 
     * @returns 
     */
    const renderSingleRouteCard = (routeItem) => {
        let card =  '<a class="vinchin-wrap__group__card module-card no-btns ajaxify" name="storage_manager" href="' + routeItem.url + '">' + 
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
    };

    /**
     * 渲染多个路由的卡片
     * @param {*} routeItem 
     */
    const renderMutipleRouteCard = (routeItem) => {
        let len = routeItem.secondRoutes.length;
        let className= '';
        let secondRoutesHtml = '';

        switch (len) {
            case 1:
                className = 'single-item';
                secondRoutesHtml = '<a class="ajaxify btn route-btn" name="storage_manager" href="' + routeItem.secondRoutes[0].url + '"><span class="route-btn__text">' + routeItem.secondRoutes[0].name + '</span></a>';
                break;
            case 2:
                className = 'double-item';
                secondRoutesHtml =  '<a class="ajaxify btn route-btn" name="storage_manager" href="' + routeItem.secondRoutes[0].url + '"><span class="route-btn__text">' + routeItem.secondRoutes[0].name + '</span></a>' + 
                                    '<a class="ajaxify btn route-btn" name="storage_manager" href="' + routeItem.secondRoutes[1].url + '"><span class="route-btn__text">' + routeItem.secondRoutes[1].name + '</span></a>';
                break;
            case 3:
                className = 'triple-item';
                secondRoutesHtml =  '<a class="ajaxify btn route-btn" name="storage_manager" href="' + routeItem.secondRoutes[0].url + '"><span class="route-btn__text">' + routeItem.secondRoutes[0].name + '</span></a>' + 
                                    '<a class="ajaxify btn route-btn" name="storage_manager" href="' + routeItem.secondRoutes[1].url + '"><span class="route-btn__text">' + routeItem.secondRoutes[1].name + '</span></a>' + 
                                    '<a class="ajaxify btn route-btn" name="storage_manager" href="' + routeItem.secondRoutes[2].url + '"><span class="route-btn__text">' + routeItem.secondRoutes[2].name + '</span></a>';
                break;
            default:
                break;
        }

        let card =  '<div class="vinchin-wrap__group__card module-card part-have-btns">' + 
                        '<div class="upper-region">' + 
                            '<div class="upper-region__left">' + 
                                '<div class="upper-region__left__title mb-8">' + routeItem.title + '</div>' + 
                                '<span class="upper-region__left__des">' + routeItem.des + '</span>' + 
                            '</div>' + 
                            '<div class="upper-region__right">' + 
                                '<i class="' + routeItem.icon + '"></i>' + 
                            '</div>' + 
                        '</div>' + 
                        '<div class="lower-region">' + 
                            '<div class="lower-region__group ' + className + '">' + 
                                secondRoutesHtml +
                            '</div>' + 
                        '</div>' + 
                    '</div>';

        return card;
    };

    const initStorageDevicePage = () => {
        let storageDeviceHtml = '';

        STORAGE_DEVICE_GROUP.forEach(item => {
            if (PERMISSION.includes(item.id)) {
                AUTHED_STORAGE_DEVICE_GROUP.push(item);
            }
        });

        if (AUTHED_STORAGE_DEVICE_GROUP.length > 0) {
            storageDeviceHtml += '<div class="vinchin-wrap__group">';

            AUTHED_STORAGE_DEVICE_GROUP.forEach(item => {
                storageDeviceHtml += renderSingleRouteCard(item);
            });

            storageDeviceHtml += '</div>';
        }

        $('#storage_device').html(storageDeviceHtml);
    }

    return {
        init: function () {
            initStorageDevicePage();
        }
    }
}();

jQuery(document).ready(function () {
    storageDevice.init();
});