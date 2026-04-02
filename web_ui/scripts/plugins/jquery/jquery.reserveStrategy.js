(function ($) {
    // 保留策略
    $.fn.reserve = function (isOnce, isCloud, moduleType, ispIncr, strategy = {}, specialType = null) {
        let strategyMode = strategy ? strategy.strategyMode : 1;
        let type = strategy ? strategy.type : 1;
        let value = strategy ? strategy.value : 30;

        // 初始化数据
        $('#reserveType').removeAttr("disabled");
        $('#GFSflag').bootstrapSwitch('disabled', false);
        let initData = function (_this) {
            $('#spinnerNum').spinner('enable');
            $('#spinnerDay').spinner('enable');
            $('#reserveType').removeAttr("disabled");
            $(_this).find('#reserveType').val(type);
            if (isCloud) {
                $(_this).find('#reserveMode').val(2).prop('disabled', true);
            } else {
                if (strategyMode) {
                    // 数据库除了MongoDB，其他都只能按链保留
                    if(moduleType == CONF.MODULE_TYPE.DB){
                        if(specialType != CONF.DB_TYPE.MONGODB){
                            $(_this).find('#reserveMode').val(2).removeAttr('disabled');
                        } else {
                            $(_this).find('#reserveMode').val(strategyMode).removeAttr('disabled');
                        }
                    } else {
                        $(_this).find('#reserveMode').val(strategyMode).removeAttr('disabled');
                    }
                } else {
                    // 数据库的MongoDB支持选择保留类型
                    if(moduleType == CONF.MODULE_TYPE.DB){
                        if(specialType != CONF.DB_TYPE.MONGODB){
                            $(_this).find('#reserveMode').val(2).removeAttr('disabled');
                        } else {
                            $(_this).find('#reserveMode').val(1).removeAttr('disabled');
                        }
                    } else {
                        $(_this).find('#reserveMode').val(1).removeAttr('disabled');
                        if (moduleType == CONF.MODULE_TYPE.M365){
                            $(_this).find('#reserveMode').val(2).removeAttr('disabled');
                        }
                    }
                }
            }
            // 按个数保留
            if (CONF.RESERVE_TYPE.NUM == type) {
                $(_this).find('#spinnerNum').spinner('value', value);
                $(_this).find('#spinnerDay').spinner('value', 30);
                $('.reserveNum').show();
                $('.reserveDay').hide();
                // 按天数保留
            } else if (CONF.RESERVE_TYPE.DAY == type) {
                $(_this).find('#spinnerDay').spinner('value', value);
                $(_this).find('#spinnerNum').spinner('value', 30);
                $('.reserveDay').show();
                $('.reserveNum').hide();
            }
            if (isOnce) {
                //一次性备份只是按个数保留
                $('#reserveType').prop("disabled", "disabled");
                $('#reserveType').val(1);
                $('.reserveDay').hide();
                $('.reserveNum').show();
                $('#spinnerNum').spinner('value', 1);
                $('#spinnerDay').spinner('value', 1);
                $('#spinnerNum').spinner('disable');
                $('#spinnerDay').spinner('disable');
                $('#GFSflag').bootstrapSwitch('state', false);
                $('#GFSflag').bootstrapSwitch('disabled', true);
            }
            // gfs
            if (strategy && strategy.gfs_strategy_item_list && strategy.gfs_strategy_item_list.length && (moduleType == CONF.MODULE_TYPE.VM || moduleType == CONF.MODULE_TYPE.PUBLIC_CLOUD)) {
                $(_this).find("#GFSDiv").initGFSPlug(initThisGFS(strategy.gfs_strategy_item_list));
                $(_this).find('#GFSflag').bootstrapSwitch('state', true);
                $(_this).find("#GFSDiv").show();
            } else {
                $(_this).find('#GFSflag').bootstrapSwitch('state', false);
                $(_this).find("#GFSDiv").initGFSPlug();
                $(_this).find("#GFSDiv").hide();
            }

            if (moduleType == CONF.MODULE_TYPE.M365 && ispIncr && !isOnce) {
                //保留策略只能选永久增量
                $('.reserveNum').hide();
                $('.reserveDay').hide();
                // 默认保留策略为永久保留
                $('#reserveType').append('<option value="3" selected>' + LANG.UI_FILE_PERMANENT + '</option>');
                $('#reserveType').prop("disabled", "disabled");
            }
            // 数据库只有MongoDB支持按点保留  m365只能按链保留
            if((moduleType == CONF.MODULE_TYPE.DB && specialType !== CONF.DB_TYPE.MONGODB) || moduleType == CONF.MODULE_TYPE.M365){
                $('.reserveModeDiv #reserveMode option[value=1]').remove();
                $('.reserveModeDiv #reserveMode').val(2);
            } else {
                if($('.reserveModeDiv #reserveMode option[value=1]').length == 0){
                    $('.reserveModeDiv #reserveMode').prepend('<option value="1">' + LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_POINT + '</option>');
                }
            }
            initReserveDes(_this);
        };

        // 初始化gfs
        var initThisGFS = function (data) {
            if (data == "") {
                return false;
            }
            var info = {};
            for (each in data) {
                switch (data[each].level1_type) {
                    case 1:
                        info.week = [data[each].level2_type, data[each].retention_num, 'checked'];
                        break;
                    case 2:
                        info.month = [data[each].level2_type, data[each].retention_num, 'checked'];
                        break;
                    case 3:
                        info.year = [data[each].level2_type, data[each].retention_num, 'checked'];
                        break;
                }
            }
            return info;
        }

        // 设置描述
        let initReserveDes = function () {
            let des = "";
            let type = parseInt($('#reserveType').val());
            let value = 0;
            let reserveMode = $('#reserveMode').val();
            if (reserveMode == 1) {
                des += LANG.UI_RESERVE_RETENTION_TYPE + ': ' + LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_POINT + ', ';
            } else {
                des += LANG.UI_RESERVE_RETENTION_TYPE + ': ' + LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_CHAIN + ', ';
            }
            // 按个数保留
            if (CONF.RESERVE_TYPE.NUM == type) {
                des += LANG.UI_RESERVE_RETENTION_MODE + ': ' + $('#reserveType option:selected').text();
                if ($('#spinnerNumInput').val()) {
                    value = isNaN(parseInt($('#spinnerNumInput').val())) ? '' : parseInt($('#spinnerNumInput').val());
                    if(isNaN(parseInt($('#spinnerNumInput').val()))){
                        $('#spinnerDayInput').val('');
                    }
                } else {
                    value = '';
                }
                // 保留天数
            } else if (CONF.RESERVE_TYPE.DAY == type) {
                des += LANG.UI_RESERVE_RETENTION_MODE + ': ' + $('#reserveType option:selected').text();
                if ($('#spinnerDayInput').val()) {
                    value = isNaN(parseInt($('#spinnerDayInput').val())) ? '' : parseInt($('#spinnerDayInput').val());
                    if(isNaN(parseInt($('#spinnerDayInput').val()))){
                        $('#spinnerDayInput').val('');
                    }
                } else {
                    value = '';
                }
            }
            value = value < 0 ? 1 : value;
            let methoddes = LANG.UI_STRATEGY_VALUE
            if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw" && type == CONF.RESERVE_TYPE.DAY){
                methoddes = LANG.UI_GLOBAL_STRATEGY_RESERVE_DAY
            }
            des += ", " + methoddes + ': ' + value;
            // gfs
            if (moduleType == CONF.MODULE_TYPE.VM) {
                des += ", " + LANG.UI_SETTING_GFS_RETENTION_POLICY + ': ' + getSwitchDes($('#GFSflag').get(0).checked);
            }
            if (moduleType == CONF.MODULE_TYPE.M365 && ispIncr && !isOnce) {
                des = LANG.UI_FILE_PERMANENT;
            }
            $('.reserveDes').html(des);
            $('.reserveDes').attr('title', des);
        }

        //得到开关的结果描述   开启/关闭
        let getSwitchDes = function (check) {
            if (check) {
                return LANG.UI_PUBLIC_ON;
            }
            return LANG.UI_PUBLIC_OFF;
        }

        // 更改事件监听
        let listeners = function (_this) {

            $(_this).find('#reserveType').on('change', reserveTypeHandler);

            $(_this).find('#reserveMode').on('change', initReserveDes);

            $(_this).find('#spinnerNumInput').on('input propertychange', initReserveDes);

            $(_this).find('#spinnerDayInput').on('input propertychange', initReserveDes);

            $(_this).find('#GFSflag').on('switchChange.bootstrapSwitch', GFSChange);

            $(_this).find('.spinner-up').on('click', initReserveDes);

            $(_this).find('.spinner-down').on('click', initReserveDes);

            $('#spinnerNum').spinner({ value: 30, step: 5, min: 1, max: 9999 });
            $('#spinnerDay').spinner({ value: 30, step: 5, min: 1, max: 9999 });
        };

        let reserveTypeHandler = function () {
            // 个数保留
            if (CONF.RESERVE_TYPE.NUM == this.value) {
                $('.reserveNum').show();
                $('.reserveDay').hide();
                // 天数保留
            } else if (CONF.RESERVE_TYPE.DAY == this.value) {
                $('.reserveNum').hide();
                $('.reserveDay').show();
            }
            initReserveDes();
        };

        // gfs改变事件
        var GFSChange = function () {
            //如果勾选
            if (this.checked) {
                $("#GFSDiv").show();
            } else {
                $("#GFSDiv").hide();
            }
            initReserveDes();
        }

        let initReserve = function (_this) {
            listeners(_this);
            // 初始化描述
            initData(_this);
        };
        return initReserve($(this));
    }
})(jQuery)