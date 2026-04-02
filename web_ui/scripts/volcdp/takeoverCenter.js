/*
 * @Author: ChengJiaFu
 * @Date: 2025-05-19 14:57:48
 * @Description: 数据管理 - 接管入口界面
 * @version: 1.0
 */
var takeoverCenter = function () {
    const PERMISSION = CONF.PERMISSION; // 获取授权数组

    const VOLCDP_TAKEOVER_GROUP = [
        {
            id: 'vol_cdp_complete_takeover',
            title: LANG.UI_COPY_MODULE_LABEL_COMPLETE_OS,
            des: LANG.UI_VOL_CDP_TAKEOVER_CENTER_COMPLATE_MACHINE_DESC,
            icon: 'viconfont vicon-complete-machine-takeover',
            url: './content/complete_machine_volcdp/cm_volcdp_takeover.php'
        },
        {
            id: 'vol_cdp_takeover',
            title: LANG.UI_COPY_MODULE_LABEL_REEL_OS,
            des: LANG.UI_VOL_CDP_TAKEOVER_CENTER_REEL_DESC,
            icon: 'viconfont vicon-volume-takeover',
            url: './content/volcdp/vol_cdp_takeover.php'
        },
    ];

    const AUTHED_VOLCDP_TAKEOVER_GROUP = [];

    /**
     * 渲染路由卡片
     * @param {*} routeItem 
     * @returns 
     */
    const renderRouteCard = (routeItem) => {
        let card =  '<a class="vinchin-wrap__group__card module-card no-btns" name="cdp_takeover" href="' + routeItem.url + '">'  +
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

    /**
     * 初始化实时保护接管入口页面
     */
    const initVolcdpTakeoverCenterPage = () => {
        let volcdpTakeoverCenterHtml = '';

        VOLCDP_TAKEOVER_GROUP.forEach(item => {
            if (PERMISSION.includes(item.id)) {
                AUTHED_VOLCDP_TAKEOVER_GROUP.push(item);
            }
        });

        if (AUTHED_VOLCDP_TAKEOVER_GROUP.length > 0) {
            volcdpTakeoverCenterHtml += '<div class="vinchin-wrap__group">';

            AUTHED_VOLCDP_TAKEOVER_GROUP.forEach(item => {
                volcdpTakeoverCenterHtml += renderRouteCard(item);
            });

            volcdpTakeoverCenterHtml += '</div>';
        }

        $('#volcdp_takeover_center').html(volcdpTakeoverCenterHtml);

        $('.vinchin-wrap__group__card').on('click', function(e) {
            e.preventDefault();
            let url = $(this).attr("href");
            let navigation = 'cdp_takeover';

            // 如果授权已过期，提示用户 且不能跳转
            if (!CONF.AUTH_NOT_EXPIRED) {
                UIToastr.showWarning(LANG.UI_LICENSE_AUTH_INFO_TITLE, LANG.UI_LICENSE_AUTH_INFO_EXPIRED);
                return;
            }
            
            LOCATION(url, navigation);
        });
    }

    return {
        init: function () {
            initVolcdpTakeoverCenterPage();
        }
    }
}();

jQuery(document).ready(function () {
    takeoverCenter.init();
});