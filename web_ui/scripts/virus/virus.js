//客户端分组管理
var Virus = function () {
    const VIRUS_VENDOR_DEFINE = {
        1: {  // kav
            img: './img/platform/kav_46x46.svg',
            description: LANG.UI_VIRUS_KAV_DETAIL,
            show_last_update_time: true,
            version_title: LANG.UI_VIRUS_SDK_VERSION,
        },
        2: {  // clamav
            img: './img/platform/clamav_92x92.png',
            description: LANG.UI_VIRUS_CLAMAV_DETAIL,
            show_last_update_time: true,
            version_title: LANG.UI_VIRUS_VERSION,
        }
    };

    const initData = function () {
        var requestData = []
        pAjaxRequest({}, '/api/v1/virus/detail', 'get', function (res) {
            if (res.success) {
                requestData = res.data.rows
            }
        }, false)
        var venderHtml = $('.virus-body')
        $.each(requestData, function (index, item) {
            let vendorInfo = VIRUS_VENDOR_DEFINE[parseInt(item.type)]
            let html = `
			<div class="vender-item ${index === 0 ? 'first-item' : ''} ">
			    <div class="item-head">
			        <div class="item-img">
			           <img src="${vendorInfo.img}" width="46" height="46">
                    </div>
                    <div class="item-title">
                        <div class="title-top">${item.vendor}</div>
                        <div class=" ${item.apply_status === 1 ? 'title-bottom' : 'title-gray'}">${item.apply_status === 1 ? LANG.UI_VIRUS_HAVE_APPLY : LANG.UI_VIRUS_NOT_APPLY}</div>
                    </div>
                </div>
                <div class="item-body">
			         <div class="item-center">
			             <div class="center-title ">${LANG.UI_VIRUS_COUNT}:</div>
			             <div class="center-value">${item.count}</div>
			         </div>
			         <div class="item-center" style="${vendorInfo.show_last_update_time ? '' : 'display: none'}">
			              <div class="center-title">${LANG.UI_VIRUS_LAST_UPDATA_TIME}:</div>
			              <div class="center-value">${item.last_update_time}</div>
			         </div>
			         <div class="item-center">
			             <div class="center-title">${vendorInfo.version_title}:</div>
			             <div class="center-value">${item.version}</div>
                     </div>
			         <div class="center-button">
			             <button type="button" class="btn green-haze detail-button" data-vendor="${item.vendor}" data-virus-type="${item.type}">
                             ${LANG.UI_MICROSOFT365_VIEW_DETAILS}
                         </button>
                     </div>
                </div>
                <div class="item-bottom" title="${vendorInfo.description}">
                    ${vendorInfo.description}
               </div>
			<div>  
           `
            venderHtml.append(html);
        })
    }
    const initListeners = function () {
        $('.detail-button').on('click', toDetail)
    }
    const toDetail = function () {
        var url = `./content/virus/virus_detail.php?vendor=${$(this).data('vendor')}&virus_type=${$(this).data('virus-type')}`;
        LOCATION(url);
    }

    return {
        //main function to initiate the module
        init: function () {
            initData()
            initListeners();	//初始化事件操作
        }
    };

}();

jQuery(document).ready(function () {
    Virus.init();
});