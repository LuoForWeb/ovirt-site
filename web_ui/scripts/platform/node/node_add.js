var NodeAdd = function () {
    var _masterNodeIpIsCustom = false;
    const NODE_FUNCTION_ENUM = {
        MANAGEMENT: 1,
        CALCULATION: 2,
        MANAGEMENT_AND_CALCULATION: 3,
    };

    var initSpinner = function () {
        //获取url中的ip地址
        getMasterNode();
        $('#spinnerNum').spinner({value: 22710, step: 1, min: 0, max: 65535});
        $('#remoteNum').spinner({value: 22711, step: 1, min: 0, max: 65535});
        $('#switchToInputAddNodeIp').unbind('click').click(switchMasterNodeIpInput);
        $('#switchToSelectAddNodeIp').unbind('click').click(switchMasterNodeIpSelect);
    }

    const initListener = () => {
        //定义iCheck样式
        $('input[name="node_function"]').iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue',
            increaseArea: '20%' // optional
        });
    };

    /**
     *  获取主节点IP信息
     */
    var getMasterNode = function () {
        let ipAddress = getUrlIpAddress();
        $('#nodeAddInput').val(ipAddress)
        let reqData = {
            offset: 0,
            limit: 100,  // 100个网络
        };
        Metronic.blockUI({target: '.addcontent', animate: true});
        pAjaxRequest(reqData, `/api/v1/nodes/${$('#masterNodeUuid').val()}/network`, 'GET', res => {
            Metronic.unblockUI({target: '.addcontent', animate: true});
            if (!res.success) {
                UIToastr.showWarning(LANG.UI_NODE_ADD_SUB_NODE, res.message);
                return;
            }
            let options = ``;
            for (const /** @type {{network_ip: string}} */ row of res.data.rows) {
                let selected = row.network_ip === ipAddress;
                options += `<option value="${row.network_ip}" ${selected}>${row.network_ip}</option>`;
            }
            $('#nodeAddSelect').html(options);
        });
    }
    /**
     * 获取url中的ip地址
     * @returns {string}
     */
    var getUrlIpAddress = function () {
        var url = window.location.href;
        const ipAddressRegex = /https:\/\/([\d.]+)\//;
        const match = url.match(ipAddressRegex);
        var ipAddress = '';
        if (match) {
            ipAddress = match[1];
        }
        return ipAddress;
    }
    /**
     * 切换主节点ip为输入
     */
    var switchMasterNodeIpInput = function () {
        $('.selectipaddr').hide();
        $('.inputipaddr').show();
        _masterNodeIpIsCustom = false;
    }
    /**
     * 切换主节点IP为选择
     */
    var switchMasterNodeIpSelect = function () {
        $('.inputipaddr').hide();
        $('.selectipaddr').show();
        _masterNodeIpIsCustom = true;
        var ipAddress = getUrlIpAddress();
        $('#nodeAddInput').val(ipAddress);
        $('.masternodeform').removeClass("has-error");
    }
    // validation using icons
    var handleValidation = function () {
        var form2 = $('#addnodeform');
        var error2 = $('.alert-danger', form2);
        var success2 = $('.alert-success', form2);

        form2.validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "",  // validate all fields including form hidden input
            rules: {
                ipaddr: {
                    required: true,
                    ipv4Ordomain: true,
                },
                port: {
                    required: true,
                    port: true,
                },
                remoteip: {
                    required: true,
                    ipv4Ordomain: true,
                },
                remoteport: {
                    required: true,
                    port: true,
                }
            },

            invalidHandler: function (event, validator) { //display error alert on form submit
                success2.hide();
                error2.show();
                Metronic.scrollTo(error2, -200);
            },

            errorPlacement: function (error, element) { // render error placement for each input type
                var icon = $(element).parent('.input-icon').children('i');
                icon.removeClass('fa-check').addClass("fa-warning");
                icon.attr("data-original-title", error.text()).tooltip({'container': 'body'});
            },

            highlight: function (element) { // hightlight error inputs
                $(element)
                    .closest('.form-group').removeClass("has-success").addClass('has-error'); // set error class to the control group
            },

            unhighlight: function (element) { // revert the change done by hightlight

            },

            success: function (label, element) {
                var icon = $(element).parent('.input-icon').children('i');
                $(element).closest('.form-group').removeClass('has-error').addClass('has-success'); // set success class to the control group
                icon.removeClass("fa-warning").addClass("fa-check");
            },

            submitHandler: function (form) {
                success2.show();
                error2.hide();

            }

        });

        $("#addsubmit").click(function () {
            if (form2.validate().form()) {
//            		success2.show();
                error2.hide();
                submit();
            }
        });
    };

    $('input[name=ipaddr]').on('change', function () {
        $('input[name=rname]').val(this.value);
    });
    //取消事件
    $('#cancelBut').on('click', function () {
        href2NodeManager();
    });

    var href2NodeManager = function () {
        LOCATION('./content/platform/node/node.php', 'backup_manager');
    }

    var submit = function () {
        var server_ip = $('#nodeAddInput').val();
        if (_masterNodeIpIsCustom) {
            server_ip = $('#nodeAddSelect').val();
        }

        let managementFlag = $('#nodeFunctionManagement:checked');
        let calculationFlag = $('#nodeFunctionCalculation:checked');
        if (!managementFlag.length && !calculationFlag.length) {
            UIToastr.showWarning(LANG.UI_NODE_ADD_SUB_NODE, LANG.UI_NODE_ADD_SUB_NODE_NO_NODE_FUNCTION);
            return;
        }
        let nodeFunction = 0;
        if (managementFlag.length && calculationFlag.length) {
            nodeFunction = NODE_FUNCTION_ENUM.MANAGEMENT_AND_CALCULATION;
        } else if (managementFlag.length) {
            nodeFunction = NODE_FUNCTION_ENUM.MANAGEMENT;
        } else if (calculationFlag.length) {
            nodeFunction = NODE_FUNCTION_ENUM.CALCULATION;
        }

        let reqData = {
            common_server_info: {
                database_ip: $('input[name=ipaddr]').val(),
                database_name: '',
                database_passwd: '',
                database_port: 3306,
                database_user: '',
                server_ip: server_ip,
                server_port: $('input[name=port]').val(),
            },
            remote_ip: $("input[name=remoteip]").val(),
            remote_port: $("input[name=remoteport]").val(),
            node_function: nodeFunction,
            force_flag: false,
        };
        Metronic.blockUI({target: '#addcontent', animate: true});
        pAjaxRequest(reqData, `/api/v1/nodes`, 'POST', res => {
            Metronic.unblockUI('#addcontent');
            if (!res.success) {
                if (parseInt(res.code) === 11002) {
                    showForceAddDialog(reqData);
                    return;
                }
                UIToastr.showWarning(LANG.UI_NODE_ADD_SUB_NODE, res.message);
                return;
            }
            UIToastr.showSuccess(LANG.UI_NODE_ADD_SUB_NODE, res.message);
            href2NodeManager();
        });
    };

    /**
     * 显示强制添加节点的弹窗
     * @param reqData
     */
    const showForceAddDialog = (reqData) => {
        bootbox.dialog({
            title: LANG.UI_NODE_ADD_SUB_NODE,
            message: LANG.UI_NODE_ADD_SUB_NODE_TIPS1,
            buttons: {
                cancel: {
                    label: LANG.UI_PUBLIC_CANCEL,
                    className: 'btn-default',
                    callback: function () {
                    }
                },
                ok: {
                    label: LANG.UI_NODE_BTN_FORCE_ADD,
                    className: 'btn-success',
                    callback: debounce(function () {
                        reqData.force_flag = true;
                        Metronic.blockUI({target: '#addcontent', animate: true});
                        pAjaxRequest(reqData, `/api/v1/nodes`, 'POST', res => {
                            Metronic.unblockUI('#addcontent');
                            if (!res.success) {
                                UIToastr.showWarning(LANG.UI_NODE_ADD_SUB_NODE, res.message);
                                return;
                            }
                            UIToastr.showSuccess(LANG.UI_NODE_ADD_SUB_NODE, res.message);
                            href2NodeManager();
                        });
                    }, 300),
                }
            }
        });
    };

    //输入框ip地址规范
    $.validator.addMethod("ipv4Ordomain", function (value, element) {
        var domain = this.optional(element) || /^(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(value);
        var ipv4 = this.optional(element) || ipV4V6(value) || /^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$/i.test(value);
        return domain || ipv4;
    }, LANG.UI_TOOLS_IP);
    //端口规范
    $.validator.addMethod("port", function (value, element) {
        return value >= 0 && value <= 65535;
    }, LANG.UI_NODE_PORT_TIPS);

    return {
        //main function to initiate the module
        init: function () {
            initSpinner();
            initListener();
            handleValidation();
        }

    };

}();

jQuery(document).ready(function () {
    NodeAdd.init();
});