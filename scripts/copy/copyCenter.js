/*
 * @Author: ChengJiaFu
 * @Date: 2025-05-26 10:19:28
 * @Description: 副本 | 归档 中心入口页面（授权模块显隐）
 * @version: 1.0
 */
var copyCenter = function () {

    let DISPLAYED_SECONDARY_GROUP_CRITICAL_VALUE = 4; // 分两个vinchin-wrap__group临界值，超过4即开始下一个vinchin-wrap__group，保证一个vinchin-wrap__group只有四个子项

    let COPY_GROUP = [
        {
            id: "vmprotect",
            type: CONF.MODULE_TYPE.VM,
            name: LANG.UI_COPY_MODULE_LABEL_VM,
            icon: 'vicon-overview-vm',
            des: LANG.UI_COPY_MODULE_DESCRIPTION_VM,
            firstUrl: '',
            firstName: '',
            secondUrl: '',
            secondName: ''
        },
        {
            id: "prcloud_protect",
            type: CONF.MODULE_TYPE.VM,
            name: LANG.UI_PUBLIC_PRIVATE_CLOUD,
            icon: 'vicon-overview-private-cloud',
            des: LANG.UI_COPY_MODULE_DESCRIPTION_PRIVATE_CLOUD,
            firstUrl: '',
            firstName: '',
            secondUrl: '',
            secondName: ''
        },
        {
            id: "awsprotect",
            type: CONF.MODULE_TYPE.VM,
            name: LANG.UI_PUBLIC_PUBLIC_CLOUD,
            icon: 'vicon-overview-plubic-cloud',
            des: LANG.UI_COPY_MODULE_DESCRIPTION_PUBLIC_CLOUD,
            firstUrl: '',
            firstName: '',
            secondUrl: '',
            secondName: ''
        },
        {
            id: "k8s_protect",
            type: CONF.MODULE_TYPE.KUBERNETES,
            name: LANG.UI_BACKUP_DATA_MODULE_K8S,
            icon: 'vicon-overciew-k8s',
            des: LANG.UI_COPY_MODULE_DESCRIPTION_K8S,
            firstUrl: '',
            firstName: '',
            secondUrl: '',
            secondName: ''
        },
        {
            id: "fileprotect",
            type: CONF.MODULE_TYPE.FS,
            name: LANG.UI_FILE_FILE,
            icon: 'vicon-overview-file',
            des: LANG.UI_COPY_MODULE_DESCRIPTION_FILE,
            firstUrl: '',
            firstName: '',
            secondUrl: '',
            secondName: ''
        },
        {
            id: "nas_protect",
            type: CONF.MODULE_TYPE.NAS,
            name: LANG.UI_BACKUP_DATA_MODULE_NAS,
            icon: 'vicon-overview-nas',
            des: LANG.UI_COPY_MODULE_DESCRIPTION_NAS,
            firstUrl: '',
            firstName: '',
            secondUrl: '',
            secondName: ''
        },
        {
            id: "obs_protect",
            type: CONF.MODULE_TYPE.FS,
            name: LANG.UI_BACKUP_DATA_MODULE_OBS,
            icon: 'vicon-overview-obs',
            des: LANG.UI_COPY_MODULE_DESCRIPTION_OBS,
            firstUrl: '',
            firstName: '',
            secondUrl: '',
            secondName: ''
        },
        {
            id: "hadoop_protect",
            type: CONF.MODULE_TYPE.FS,
            name: LANG.UI_BACKUP_DATA_MODULE_HADOOP,
            icon: 'vicon-overview-hadoop',
            des: LANG.UI_COPY_MODULE_DESCRIPTION_HADOOP,
            firstUrl: '',
            firstName: '',
            secondUrl: '',
            secondName: ''
        },
        {
            id: "db_protect",
            type: CONF.MODULE_TYPE.DB,
            name: LANG.UI_BACKUP_DATA_MODULE_DB,
            icon: 'vicon-overview-database',
            des: LANG.UI_COPY_MODULE_DESCRIPTION_DB,
            firstUrl: '',
            firstName: '',
            secondUrl: '',
            secondName: ''
        },
        {
            id: "office365_protect",
            type: CONF.MODULE_TYPE.M365,
            name: LANG.UI_BACKUP_DATA_MODULE_M365,
            icon: 'vicon-overview-m365',
            des: LANG.UI_COPY_MODULE_DESCRIPTION_M365,
            firstUrl: '',
            firstName: '',
            secondUrl: '',
            secondName: ''
        },
        {
            id: "complete_machine",
            type: CONF.MODULE_TYPE.OS,
            name: LANG.UI_COPY_MODULE_LABEL_COMPLETE_OS,
            icon: 'vicon-overview-complete-machine',
            des: LANG.UI_COPY_MODULE_DESCRIPTION_COMPLETE_OS,
            firstUrl: '',
            firstName: '',
            secondUrl: '',
            secondName: ''
        },
        {
            id: "osbackup",
            type: CONF.MODULE_TYPE.OS,
            name: LANG.UI_COPY_MODULE_LABEL_REEL_OS,
            icon: 'vicon-overview-volume',
            des: LANG.UI_COPY_MODULE_DESCRIPTION_REEL_OS,
            firstUrl: '',
            firstName: '',
            secondUrl: '',
            secondName: ''
        }
    ];

    const PAGE_TYPE = $('#page_type').val();

    const PAGE_URL = {
        COPY: './content/copy/copy.php',
        COPY_BACK: './content/copy/copyback.php',
        ARCHIVE: './content/archive/addarchive.php',
        ARCHIVE_BACK: './content/archive/archiveback.php',
    }

    const SUB_MPDULE_TYPES = {
        'vmprotect': CONF.VM_SUB_MODULE.VM,
        'prcloud_protect': CONF.VM_SUB_MODULE.PRIVATE_CLOUD,
        'awsprotect': CONF.VM_SUB_MODULE.PUBLIC_CLOUD,
        'fileprotect': CONF.SUBMODULE_TYPE.FS,
        'nas_protect': CONF.SUBMODULE_TYPE.NAS,
        'obs_protect': CONF.SUBMODULE_TYPE.OBS,
        'hadoop_protect': CONF.SUBMODULE_TYPE.HADOOP,
        'osbackup': 0,
        'complete_machine': 1
    }

    /**
     * 渲染路由卡片
     * @param {*} routeItem 
     * @returns 
     */
    const renderMutipleRoutesCard = (routeItem) => {

        // </flag> 的作用是合并所有卡片HTML字符串之后，用于正则匹配单个卡片结尾所用，勿删！
        let card = '<div class="vinchin-wrap__group__card h-210px module-card">' + 
                        '<div class="upper-region">' + 
                            '<div class="upper-region__left">' + 
                                '<div class="upper-region__left__title">' + routeItem.name + '</div>' + 
                                '<div class="upper-region__left__des">' + '<span class="ellipsis-text" title="' + routeItem.des + '">' + routeItem.des + '</span>' + '</div>' + 
                            '</div>' + 
                            '<div class="upper-region__right">' + 
                                '<i class="viconfont ' + routeItem.icon + '"></i>' +
                            '</div>' + 
                        '</div>' + 
                        '<div class="lower-region">' + 
                            '<div class="lower-region__group multiple-items">' + 
                                '<a class="copy-center btn route-btn" name="' + routeItem.pageName + '" href="' + routeItem.firstUrl + '"><span class="route-btn__text">' + routeItem.firstName + '</span></a>' + 
                                '<a class="copy-center btn route-btn" name="' + routeItem.pageName + '" href="' + routeItem.secondUrl + '"><span class="route-btn__text">' + routeItem.secondName + '</span></a>' + 
                            '</div>' + 
                        '</div>' + 
                        '</flag>' + 
                    '</div>';
        
        return card;
    }

    /**
     * 动态调整 副本 | 归档 群组HTML
     */
    const dynamicAdjustCopyGroupHtml = () => {
        let resultHtml = '';

        // 获取屏幕宽度
        const screenWidth = window.innerWidth;
        DISPLAYED_SECONDARY_GROUP_CRITICAL_VALUE = screenWidth > 1440 ? 4 : 3;

        // 计算总长度
        let len = COPY_GROUP.length;

        if (len === 0) {
            resultHtml = '';
        } else {
            let allCardsHtml = '';

            COPY_GROUP.forEach(item => {
                // 深拷贝 item 以避免修改原始数据
                const newItem = { ...item };

                if (PAGE_TYPE === 'archive_new') {
                    newItem.pageName = 'archive_new';
                    newItem.des = newItem.des.replaceAll(LANG.UI_VISUAL_COPY, LANG.UI_ARCHIVE);
                    newItem.firstUrl = `${PAGE_URL.ARCHIVE}?module_type=${item.type}&sub_module_type=${SUB_MPDULE_TYPES[item.id] ? SUB_MPDULE_TYPES[item.id] : 0}`;
                    newItem.secondUrl = `${PAGE_URL.ARCHIVE_BACK}?module_type=${item.type}&sub_module_type=${SUB_MPDULE_TYPES[item.id] ? SUB_MPDULE_TYPES[item.id] : 0}`;
                    newItem.firstName = LANG.UI_ARCHIVE;
                    newItem.secondName = `${LANG.UI_ARCHIVE}${LANG.UI_COPY_LABEL_BACK}`;
                } else {
                    newItem.pageName = 'copy_protect';
                    newItem.firstUrl = `${PAGE_URL.COPY}?module_type=${item.type}&sub_module_type=${SUB_MPDULE_TYPES[item.id] ? SUB_MPDULE_TYPES[item.id] : 0}`;
                    newItem.secondUrl = `${PAGE_URL.COPY_BACK}?module_type=${item.type}&sub_module_type=${SUB_MPDULE_TYPES[item.id] ? SUB_MPDULE_TYPES[item.id] : 0}`;
                    newItem.firstName = LANG.UI_VISUAL_COPY;
                    newItem.secondName = `${LANG.UI_VISUAL_COPY}${LANG.UI_COPY_LABEL_BACK}`;
                }

                allCardsHtml += renderMutipleRoutesCard(newItem);
            });

            // /<div class="vinchin-wrap__group__card h-210px module-card">[\s\S]*?<\/flag><\/div>/g：确保匹配到每个完整的 vinchin-wrap__group__card 结构，包括其所有的子元素和结束标签
            // [\s\S]*?：非贪婪匹配任意字符（包括换行符），确保匹配到整个卡片内容
            // <\/flag><\/div>：匹配卡片的结束标签
            const cardsArray = allCardsHtml.match(/<div class="vinchin-wrap__group__card h-210px module-card">[\s\S]*?<\/flag><\/div>/g) || [];

            let currentGroup = '';
            let cardCount = 0;

            cardsArray.forEach(card => {
                if (cardCount === 0) {
                    currentGroup = '<div class="vinchin-wrap__group">';
                }
                currentGroup += card;
                cardCount++;
    
                if (cardCount === DISPLAYED_SECONDARY_GROUP_CRITICAL_VALUE) { // 等于临界值DISPLAYED_SECONDARY_GROUP_CRITICAL_VALUE，则添加到groupHtml中，并重置currentGroup和cardCount
                    resultHtml += currentGroup + '</div>';
                    currentGroup = '';
                    cardCount = 0;
                }
            });
    
            // 处理剩余的卡片
            if (cardCount > 0) {
                resultHtml += currentGroup + '</div>';
            }
        }

        PAGE_TYPE === 'archive_new' ? $('#archive_center').html(resultHtml) : $('#copy_center').html(resultHtml);

        $('.copy-center').on('click', function(e) {
            e.preventDefault();
            let url = $(this).attr("href");
            let navigation = PAGE_TYPE === 'archive_new' ? '' : 'copy_protect';

            // 如果授权已过期，提示用户 且不能跳转
            if (!CONF.AUTH_NOT_EXPIRED) {
                UIToastr.showWarning(LANG.UI_LICENSE_AUTH_INFO_TITLE, LANG.UI_LICENSE_AUTH_INFO_EXPIRED);
                return;
            }
            
            LOCATION(url, navigation);
        });
    }

    /**
     * 初始化 副本 | 归档 入口页面
     */
    const initCopyCenterPage = () => {
        Metronic.blockUI({target: '.vinchin-wrap',animate: true});

        let permissions = CONF.PERMISSION;

        let authedArchiveModules = ['vmprotect', 'prcloud_protect', 'awsprotect', 'complete_machine', 'osbackup'].filter(i => permissions.includes(i));

        // 权限过滤
        COPY_GROUP = PAGE_TYPE === 'archive_new' ? 
                        // 虚拟机和整机模块才有归档
                        COPY_GROUP.filter(item => authedArchiveModules.includes(item.id)) : 
                        COPY_GROUP.filter(item => permissions.includes(item.id));

        dynamicAdjustCopyGroupHtml();

        Metronic.unblockUI('.vinchin-wrap');
    }

    return {
        init: function () {
            initCopyCenterPage();

            // 分辨率动态调整每行卡片个数
            window.addEventListener('resize', dynamicAdjustCopyGroupHtml);
        }
    }
}();

jQuery(document).ready(function () {
    copyCenter.init();
});