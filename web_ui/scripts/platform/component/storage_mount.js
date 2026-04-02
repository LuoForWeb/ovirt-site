var storageMount = function (){
    var selectServiceIpFlag = true; // 是否不自定义挂载ip或域名,
    var ItemIp; // 定义一个增加ip的默认样式
    var protocolLists = [{'name':'NFS', 'value': 1},{'name':'iSCSI','value': 2}]; // 默认协议
    var protocolList = []; // 默认协议
    var initData = {};// 初始化编辑数据
    var ipDomain = []; // 挂载点ip或者域名数组
    var showLimit = true; // 是否显示配置访问地址
    var showDiy = true; // 是否显示自定义挂载点和ip
    var chooseLimit = ''; // 已经设置了的限制ip列表
    var chooseType = 0; // 已经设置了的限制模式
    var checkIsOk = true; // 校验是否OK
    var isGrain = false; // 标记是否是细粒度任务过来
    var instant = false; // 标记是否是瞬时恢复任务过来
    // 编辑的时候携带了数据
    var initsData = function (){
        if (initData.ip_domain != undefined) {
            // 那么初始化
            // 挂载ip或域名没有匹配的，那么表示是自定义的
            if ($.inArray(initData.ip_domain, ipDomain) !== -1) {
                // 是自动获取的
                $('#storagemount_component_body .serveripaddr').val(initData.ip_domain);
            } else {
                // 是手动输入的
                $('#storagemount_component_body .ip_domain').val(initData.ip_domain);
                $('#storagemount_component_body .selectipdiv').hide();
                $('#storagemount_component_body .inputipdiv').show();
                selectServiceIpFlag = false;
            }

            $('#storagemount_component_body .amount_service').val(initData.protocol);
        }
        // 渲染限制ip列表
        if (initData.limit_ip != '' && initData.limit_ip != undefined) {
            chooseType = 1;
            chooseLimit = initData.limit_ip;
            var limit = initData.limit_ip;
            var limit_ip = limit.split(',');
            // 渲染弹窗
            $('#storagemount_component_modal .storagemount_component_limit_mode').val(1);
            $('#storagemount_component_modal .show-limit-ip').show();
            $('#storagemount_component_modal input[name="limit_ip[]"]').eq(0).val(limit_ip[0]);
            for (var i = 1; i < limit_ip.length; i ++) {
                addLimitIp(limit_ip[i]);
            }
            // 渲染列表
            showListIp();
        }
    }
    // 监听是否是ip或域名
    var checkIsDomain = function (){
        // 监听输入事件
        $('#storagemount_component_body .ip_domain').on('blur', (event) => {
            if (selectServiceIpFlag) {
                // 下拉选择
                return;
            }
            const inputValue = event.target.value;
            checkIsOk = true;
            $('#storagemount_component_body .inputipdiv').find('i').remove();
            $('#storagemount_component_body .ip_domain').css('border', '1px solid #E6E6E6');
            if (!checkDomain(inputValue)) {
                checkIsOk = false;
                // 输入无效时显示错误信息并清空输入框
                // $('#storagemount_component_body .ip_domain').val(''); // 清空输入框
                $('#storagemount_component_body .ip_domain').before('<i class="fa fa-warning" title="' +
                    LANG.UI_TOOLS_IP_OR_DOMAIN +'" style="color: red;"></i>').css('border', '1px solid #F1416C');
                //UIToastr.showWarning(LANG.UI_PLATFORM_RECOVERY_MOUNT_CONFIG, LANG.UI_INSTANT_NFS_NO_IP_TIPS);
                return false;
            } else {
                $('#storagemount_component_body .ip_domain').before('<i class="fa fa-check" style="color:#45B6AF;"></i>').css('border-color', 'rgb(213, 233, 197)');
            }
        });

    }

    // 检测是否域名或ip
    var checkDomain = function (value) {
        // 正则表达式定义
        const domainRegex = /^[a-zA-Z0-9]+([-.][a-zA-Z0-9]+)*\.[a-zA-Z]{2,}$/; // 匹配域名
        var domain = /^(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i;
        return ipV4V6(value) || domain.test(value);
    }

    // 提取 URL 中的主机名
    function extractHostname(url) {
        let hostname;
        if (url.indexOf("://") > -1) {
            hostname = url.split('/')[2];
        } else {
            hostname = url.split('/')[0];
        }

        // 去掉端口号
        hostname = hostname.split(':')[0];
        return hostname;
    }

    //事件监听
    var initListener = function(){
        //手动输入IP
        $('#storagemount_component_body .diyserverip').on('click', function(){
            $('#storagemount_component_body .selectipdiv').hide();
            $('#storagemount_component_body .inputipdiv').show();
            selectServiceIpFlag = false;
            $('#storagemount_component_body .ip_domain').focus();
        })

        //自动选择IP
        $('#storagemount_component_body .selectserverip').on('click', function(){
            $('#storagemount_component_body .selectipdiv').show();
            $('#storagemount_component_body .inputipdiv').hide();
            selectServiceIpFlag = true;
            $('#storagemount_component_body .ip_domain').val(window.location.host);
        })
        rememberIp();
        delLimitIp();
        checkIsDomain();
        //跳转到添加虚拟化中心
        $('#toaddvcenter').on('click',function(){
            LOCATION('./content/vm/add_vcenter.php', 'vcenter_manager');
        });

    }
    $('.btn-service').click(function (){
        // 打开模态
        getReal();
        $('#storagemount_component_modal').modal({'width': '693px', 'height': '282px'});
        $('#storagemount_component_modal').parent().css('z-index', '10086');
    })

    // 动态更改
    var getReal = function () {
        $('#storagemount_component_modal .limit-ip-item').html('');
        $('.storagemount_component_limit_mode').val(parseInt(chooseType));
        if (chooseLimit != '' && chooseLimit != undefined) {
            // 渲染限制ip列表
            var limit_ip = chooseLimit.split(',');
            // 渲染弹窗
            $('#storagemount_component_modal .storagemount_component_limit_mode').val(1);
            $('#storagemount_component_modal .show-limit-ip').show();
            for (var i = 0; i < limit_ip.length; i ++) {
                addLimitIp(limit_ip[i]);
            }
        }
        $('.storagemount_component_limit_mode').change();
    }

    // 限制模式改变事件
    $('#storagemount_component_modal .storagemount_component_limit_mode').change(function (){
        if ($(this).val() == 1) {
            $('#storagemount_component_modal .show-limit-ip').show();
        } else {
            $('#storagemount_component_modal .show-limit-ip').hide();
        }
    })

    // 添加ip地址
    $('#storagemount_component_modal .service-add-limit-ip').find('span').click(function (){
        addLimitIp();
    })

    function addLimitIp(val = '') {
        // 这里进行个判断。如果页面上一个都没有，此时添加，需要把名称也赋值上
        var num = $('#storagemount_component_modal .limit-ip-item .list-option').length;
        var html = ItemIp.clone();
        if (num == 0) {
            html.find('.row label').html('IP');
        }
        html.find('input[name="limit_ip[]"]').val(val);
        $('#storagemount_component_modal .limit-ip-item').append(html);
        delLimitIp();
    }

    // 删除ip地址
    function delLimitIp() {
        $('#storagemount_component_modal .limit-ip-item .item-service-ip button').on('click', function (){
            $(this).parent().parent().parent().parent().parent().remove();
            // 如果此时的第一个的label没有名称，那么手动加一个
            var dom = $('#storagemount_component_modal .limit-ip-item .list-option').eq(0).find('.row label');
            var label = dom.html();
            if (label == '' || label == undefined) {
                dom.html('IP');
            }
            // 需要重新赋值 chooseLimit 的值
            var nows = getLimitIpItem();
            chooseLimit = nows.join(',');
        })

        $('#storagemount_component_body .item-service-ip button').on('click', function (){
            var index = $(this).closest('.item-service-ip').index();
            $(this).parent().remove();
            // console.log('index', index)
            $('#storagemount_component_modal .limit-ip-item .list-option').eq(index).find('.item-service-ip button').click();

        })
    }

    // 记录第一个ip的样式
    function rememberIp() {
        var html = $('#storagemount_component_modal .limit-ip-item .list-option').eq(0).html();
        html = '<div class="list-option">' + html + '</div>';
        var $html = $(html); // 将获取到的 HTML 内容转换为 jQuery 对象
        $html.find('.row label').html('');
        $html.find('.row input').val('');
        ItemIp = $html;
    }

    //初始化存储挂载点IP或域名
    var initBackupServerAddr = function(nodeuuid){
        var data = {};
        data.offset = 0;
        data.limit = 100;
        pAjaxRequest(data, "/api/v1/nodes/"+nodeuuid+"/network", "GET", function (result) {
            var data = [];
            if (result.code == 200) {
                var datas = result.data.rows;
                for (var k in datas) {
                    if (datas[k].network_type == 1) {
                        data.push(datas[k].network_ip);
                    }
                }
            } else {
                data = [window.location.host];
            }
            var serveripaddr = $('#storagemount_component_body .serveripaddr');
            serveripaddr.empty();
            for(var i=0; i<data.length; i++){
                var option = $("<option>").text(data[i]).val(data[i]);
                serveripaddr.append(option);
                ipDomain.push(data[i]);
            }
            initService();
            initsData(); // 初始化数据
        });

        $('#storagemount_component_body .ip_domain').val(window.location.host);
    }

    // 初始化挂载协议
    var initService = function (){
        var service = $('#storagemount_component_body .amount_service');
        service.empty();
        for(var i=0; i<protocolList.length; i++){
            var option = $("<option>").text(protocolList[i]['name']).val(protocolList[i]['value']);
            service.append(option);
        }
        // 根据协议列表的长度决定是否禁用选择框
        service.prop('disabled', protocolList.length === 1);
    }

    // 获取配置的ip地址
    function getLimitIpItem() {
        var arr = [];
        // 遍历每个input元素并获取配置信息
        $('#storagemount_component_modal input[name="limit_ip[]"]').each(function() {
            var val = $(this).val();
            if (!ipV4V6(val)) {
                arr = false;
                return false;
            }
            if ($.inArray(val, arr) !== -1) {
                arr = 1;
                return false;
            }
            arr.push(val);
        });
        return arr;
    }

    // 渲染列表的ip限制
    function showListIp() {
        var document = '';
        // 如果设置了ip，那么就带入过去，否则就清空
        var dom = $('#storagemount_component_body .show-item-service-ip');
        if ($('.storagemount_component_limit_mode').val() == 1) {
            // 自定义ip
            var arr = getLimitIpItem();
            if (arr == false) {
                UIToastr.showWarning(LANG.UI_PLATFORM_RECOVERY_MOUNT_CONFIG, LANG.UI_DB_ADD_AGENT_INPUT_CORRECT_IP);
                return false;
            }
            if (arr == 1) {
                UIToastr.showWarning(LANG.UI_PLATFORM_RECOVERY_MOUNT_CONFIG, LANG.UI_SETTING_INPUT_IP_REPEAT);
                return false;
            }
            var htmll = '<div class="item-service-ip">'+
                '<input type="text" value="';
            var htmlr = '" disabled>'+
                '<button type="button"><i class="viconfont vicon-a-Reduce-onejianshao"></i></button>'+
                '</div>';
            for(var i in arr) {
                document += htmll + arr[i] + htmlr;
            }
            dom.show();
        } else {
            dom.hide();
        }
        dom.html(document);
        delLimitIp();
        // 访问限制地址
        if ($('.storagemount_component_limit_mode').val() == 1) {
            // 自定义ip
            var arr = getLimitIpItem();
            chooseLimit = arr.join(',');
        } else {
            chooseLimit = '';
        }
        return true;
    }

    // 点击限制ip的确定按钮
    $('.storagemount_component_modal_submit').click(function (){
        if (showListIp()) {
            $('#storagemount_component_modal').modal('hide');
        }
    })


    return {
        init: function (options) {
            if (options.protocol != undefined) {
                // 自定义协议
                protocolList = options.protocol
            } else {
                protocolList = protocolLists;
            }
            if (options.data != undefined) {
                // 自定义协议
                initData = options.data
            } else {
                initData = {};
            }
            if (options.class != undefined) {
                // 自定义右边的宽度比例
                $('#storagemount_component_body .auto-change-scale').removeClass().addClass(options.class);
            }
            if (options.showLimit != undefined) {
                showLimit = options.showLimit
            } else {
                showLimit = true;
            }
            if (options.showDiy != undefined) {
                showDiy = options.showDiy
            } else {
                showDiy = true;
            }
            if (options.is_grain != undefined) {
                isGrain = options.is_grain
            } else {
                isGrain = false;
            }

            if (options.instant != undefined) {
                instant = options.instant
            } else {
                instant = false;
            }
            if (isGrain) {
                $('.show_domain_ip_point').hide();
                $('.show_domain_ip_point1').show();
                $('.normal_tips_').hide();
                $('.normal_tips2_').show();
                $('.grain_job_detail_').addClass('mt-20');
            } else {
                $('.show_domain_ip_point1').hide();
                $('.show_domain_ip_point').show();
                $('.normal_tips_').show();
                $('.normal_tips2_').hide();
                $('.grain_job_detail_').removeClass('mt-20');
            }
            if (!showLimit) {
                $('.amount_limit').hide();
                $('.amount_service').attr('style', 'width: 100% !important');
                $('.show-item-service-ip').hide();
            } else {
                $('.amount_limit').show();
                $('.amount_service').attr('style', '');
                $('.show-item-service-ip').show();
            }

            if (!showDiy) {
                // 不显示自定义
                $('#storagemount_component_body').find('.show-diy').hide();
                $('#storagemount_component_body').find('.not-show-diy').show();
            } else {
                $('#storagemount_component_body').find('.show-diy').show();
                $('#storagemount_component_body').find('.not-show-diy').hide();
            }

            if (instant) {
                $('.show_domain_ip_point').hide();
                $('.show_domain_ip_point2').hide();
                $('.show_domain_ip_point1').show();
            } else {
                $('.show_domain_ip_point').show();
                $('.show_domain_ip_point2').hide();
                $('.show_domain_ip_point1').hide();
            }

            console.log('initData', initData)
            // 初始化监听事件
            initListener();
            // 初始化方法,接受入参
            initBackupServerAddr(options.node_uuid);
        },
        // 提供一个对外获取所有配置的接口
        getAmountInfo: function () {
            if (checkIsOk == false) {
                return false;
            }
            // 挂载ip
            if (selectServiceIpFlag) {
                var ip = $('#storagemount_component_body .serveripaddr').val();
            } else {
                var ip = $('#storagemount_component_body .ip_domain').val();
            }
            getReal();
            // 挂载协议
            var service = $('#storagemount_component_body .amount_service').val();
            var service_txt = $('#storagemount_component_body .amount_service option:selected').text();
            // 访问限制地址
            var limit_ip = '';
            var allow_access_ips = [];
            if ($('.storagemount_component_limit_mode').val() == 1) {
                // 自定义ip
                var arr = getLimitIpItem();
                limit_ip = arr.join(',');
                allow_access_ips = arr;
            }
            return {'ip_domain':ip, 'ip': ip, 'protocol':service, 'service_txt': service_txt, 'limit_ip':limit_ip, 'allow_access_ips': allow_access_ips};
        }
    }
}();
