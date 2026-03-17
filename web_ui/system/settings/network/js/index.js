var networkIndex = function () {

    function initListener() {
        // 定义 Tab 顺序：[权限标识, block选择器, tab选择器]
        const tabs = [
            ['set_ip',        '#ipdiv-block',        '#ipdiv',          'ip'], // ip配置
            ['system_dns',    '#dnsdiv-block',       '#dnsdiv',         'dns'], // 域名解析
            ['nic_teaming',   '#nicteamingdiv-block','#nicteamingdiv',  'teaming'], // 网卡聚合
            ['card_bridge',   '#cardbridgediv-block','#cardbridgediv',  'bridge'] // 网络桥接
        ];

        const availableTabs = [];

        // 1. 移除无权限的 tab，并收集可用的
        tabs.forEach(([perm, blockSel, tabSel, key]) => {
            if (CONF.PERMISSION.includes(perm)) {
                availableTabs.push({ block: blockSel, tab: tabSel, key });
            } else {
                $(blockSel).remove();
                $(tabSel).remove();
            }
        });

        if (availableTabs.length === 0) {
            // 可选：显示无权限提示
            return;
        }

        // 2.安全获取 tab 参数
        var targetKey = $('#tabHref').val();
        // 如果 hash 为空，或目标 tab 不在可用列表中，则 fallback 到第一个
        const hasTarget = availableTabs.some(t => t.key === targetKey);
        const finalTab = hasTarget ? availableTabs.find(t => t.key === targetKey) : availableTabs[0];

        // 3. 清除所有已有的 active 类（安全起见）
        $('.nav-tabs li a').removeClass('active');      // 假设 tab 导航在 .nav-tabs 下
        $('.tab-content > div').removeClass('active'); // 假设内容区域是 .tab-content 的直接子 div
        $('.tab-content > div').removeClass('show');

        // 4. 激活目标 tab
        $(finalTab.block).addClass('active');
        $(finalTab.tab).addClass('active');
        $(finalTab.tab).addClass('show');
    }

    return {
        //main function to initiate the module
        init: function () {
            console.log('lang', LANG.WEB_PAGE_SET_NAME)
            console.log('all-lang', LANG);
            // 登录未实现，暂时注释改方法
            // initListener();
        }
    };

}();

jQuery(document).ready(function () {
    networkIndex.init();
});
