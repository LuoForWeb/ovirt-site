(function ($) {
    // 存储策略
    $.fn.store = function (moduleType, hasParams, strategy) {
        let deduplicationCheck = strategy ? strategy.deduplication : false;
        let compress_flag = strategy ? (!!strategy.compress_flag || !!strategy.compress) : true;
        let compress_method = strategy ? strategy.compress_method : 1;
        let encrypt = strategy ? (strategy.dataencrypt || strategy.encrypt) : false;
        let encrypt_method = strategy ? strategy.encrypt_method : 1;
        let password_auto_flag = strategy ? strategy.password_auto_flag : false;
        let password = strategy ? strategy.password : '';
        let initData = function () {
            // 重复数据删除
            $('#deduplicationCheck').bootstrapSwitch('state', deduplicationCheck);
            // 压缩存储
            $('#compressCheck').bootstrapSwitch('state', compress_flag);
            // 压缩等级
            if(compress_flag && compress_method){
                $('#compressGrade').val(compress_method);
            }
            // 数据加密
            $('#encryptStorageCheck').bootstrapSwitch('state', encrypt);
            if(encrypt && encrypt_method){
                // 数据加密
                $('#storageEncryptMethod').val(encrypt_method);
            }
            // 自动生成密码
            $('#passwordAutocheck').bootstrapSwitch('state', password_auto_flag);
            if(!password_auto_flag && encrypt){
                $('.passwordDiv').show();
            }else{
                $('.passwordDiv').hide();
            }
            // 修改任务时密码框和确认密码框先不赋值,提示: 修改时填写
            if(hasParams){
                if (!encrypt || password_auto_flag) {  // 没有开启加密或者自动加密不显示“填写时修改”
                    $('#password').removeAttr('placeholder');
                    $('#repassword').removeAttr('placeholder');
                } else {
                    $('#password').attr('placeholder', LANG.UI_PUBLIC_MODIFY_PASSWORD_TIPS);
                    $('#repassword').attr('placeholder', LANG.UI_PUBLIC_MODIFY_PASSWORD_TIPS);
                }
            }else{
                $('#password').val(password);
                $('#repassword').val(password);
            }
            initStoreDes();
        };

        let initStoreDes = function () {
            let des = "";
            // 重复数据删除
            if (CONF.FUNCTIONS.includes('dedupication') && (moduleType == CONF.MODULE_TYPE.VM || moduleType == CONF.MODULE_TYPE.DB || moduleType == CONF.MODULE_TYPE.OS || moduleType == CONF.MODULE_TYPE.PUBLIC_CLOUD)) {
                des += LANG.UI_GLOBAL_STRATEGY_DEDUPULICATION + ": " + getSwitchDes($('#deduplicationCheck').get(0).checked) + ", ";
            }
            // 压缩传输
            des += LANG.UI_GLOBAL_STRATEGY_COMPRESS + ": " + getSwitchDes($('#compressCheck').get(0).checked);
            // 压缩等级
            if ($('#compressCheck').get(0).checked) {
                var gradeValue = $('#compressGrade').val();
                var grade = '';
                switch (parseInt(gradeValue, 10)) {
                    case 1:
                        grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_FAST;
                        break;
                    case 2:
                        grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_NORMAL;
                        break;
                    case 3:
                        grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_BETTER;
                        break;
                    case 4:
                        grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_BEST;
                        break;
                };
                des += "," + LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE + ": " + grade;
            }
            // 存储加密
            des += "," + LANG.UI_BACKUP_DATA_ENCRYPT + ": " + getSwitchDes($('#encryptStorageCheck').get(0).checked);
            // 存储加密算法
            if ($('#encryptStorageCheck').get(0).checked) {
                var encryptedMethodLabel = $('.storage-encrypt-label').html();
                let method = $('#storageEncryptMethod').val();
                var grade = '';
                switch (parseInt(method)) {
                    case 1:
                        grade = LANG.UI_GLOBAL_STRATEGY_STORAGE_ENCRYPT_AES;
                        break;
                    case 2:
                        grade = LANG.UI_GLOBAL_STRATEGY_STORAGE_ENCRYPT_SM;
                        break;
                };
                des += "," + encryptedMethodLabel + ": " + grade;
            }
            $('.storeDes').html(des);
            $('.storeDes').attr('title', des);
        };

        //得到开关的结果描述   开启/关闭
        let getSwitchDes = function (check) {
            if (check) {
                return LANG.UI_PUBLIC_ON;
            }
            return LANG.UI_PUBLIC_OFF;
        };

        let compressChange = function () {
            if (this.checked) {
                $('.compressGradeDiv').show();
            } else {
                $('.compressGradeDiv').hide();
            }
            initStoreDes();
        };

        let listeners = function (_this) {
            $(_this).find('#deduplicationCheck').on('switchChange.bootstrapSwitch', initStoreDes);

            $(_this).find('#compressCheck').on('switchChange.bootstrapSwitch', compressChange);

            $(_this).find('#compressGrade').on('change', initStoreDes);

            $(_this).find('#storageEncryptMethod').on('change', initStoreDes);

            $(_this).find('#encryptStorageCheck').on('switchChange.bootstrapSwitch', encryptChange);

            $(_this).find('#passwordAutocheck').on('switchChange.bootstrapSwitch', passwordModeChange);

            $(_this).find('#repassword').on('input propertychange', checkPassword);

            $(_this).find('#password').on('input propertychange', checkPassword);
            
            $(_this).find('#GFSflag').on('switchChange.bootstrapSwitch', GFSChange);
            $(_this).find('.show-password-btn').on('click', showPassword);
            $(_this).find('.show-rePassword-btn').on('click', showPassword);
        };
        /**
         * 点击小眼睛显示隐藏密码
         */
        let showPassword = function (e) {
            e.stopImmediatePropagation();
            let $input = $(this).parent().find('input');
            let $btn = $(this);
            let inputType = $input.attr('type');
            // 修改输入框的类型，控制显示隐藏
            if (inputType === 'password') {
                $input.attr('type', 'text');
                $btn.find('i').removeClass('vicon-a-lujing8232').addClass('vicon-a-lianhe1');
            } else {
                $input.attr('type', 'password');
                $btn.find('i').removeClass('vicon-a-lianhe1').addClass('vicon-a-lujing8232');
            }
        }

        // gfs改变事件
        var GFSChange = function () {
            //如果勾选
            if (this.checked) {
                $("#GFSDiv").show();
            } else {
                $("#GFSDiv").hide();
            }
        }

        //数据加密密码确认检测
        var checkPassword = function(){
            $('#password').attr('placeholder', '');
            $('#repassword').attr('placeholder', '');
            var password = $.trim($('#password').val());
            var repassword = $.trim($('#repassword').val());
            if(password != repassword){
                $('.passwordTips').show();
            }else{
                $('.passwordTips').hide();
            }
        }

        //存储加密切换
        var encryptChange = function () {
            if (this.checked) {
                $('#passwordAutocheck').bootstrapSwitch('state', true); //自动密码
                $('.passwordModeDiv').show();
                $('.storage-encrypt-div').show();
            } else {
                $('.passwordModeDiv').hide();
                $('.passwordDiv').hide();
                $('.storage-encrypt-div').hide();
            }
            initStoreDes();
        }

        // 切换自动选择存储加密密码
        var passwordModeChange = function () {
             //切换自动生成密码时清空密码
            if (this.checked) {
                $('.passwordDiv').hide();
            } else {
                $('.passwordDiv').show();
            }
        }

        let initStore = function (_this) {
            listeners(_this);
            // 初始化描述
            initData(_this);
        };
        return initStore($(this));
    }
})(jQuery)