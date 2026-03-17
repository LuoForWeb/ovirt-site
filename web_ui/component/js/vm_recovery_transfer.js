/**
 * 虚拟机恢复第三步传输策略
 */
(function($){
    var _sourceHypervisor; //时间点虚拟化类型
    var _hypervisor;		//恢复目标虚拟化类型
    var appliancecheckFlag = false;
    var applianceFlag = false;
    var initHighFlag = false;
    var windowsFlag = false;

    //初始化appliance下拉框
    var initApplianceSelect = function(currentPlatformUuid){
        if(applianceFlag) return; //只加载一次
        $('#transferAgentTree').transferAgent();
        applianceFlag = true;
    }
    $.fn.initApplianceSelect = initApplianceSelect;

    var applianceChange = function(){
        appliancecheckFlag = true;
        if(this.checked){
            initApplianceSelect();
            $('.applianceselectdiv').show();
            if (CONF.VM_TYPE.VMWARE == _hypervisor) {
                // vmware开启传输代理可配置加密传输
                $('.encrypttransferdiv').show();	//加密传输
                if ($('#encrypttransfer').bootstrapSwitch('state')) {
                    $('.transfer-encrypt-method-form').show();
                }
            }
        }else{
            $('.applianceselectdiv').hide();
            if (CONF.VM_TYPE.VMWARE == _hypervisor) {
                $('#encrypttransfer').bootstrapSwitch('state', false);
                $('.encrypttransferdiv').hide();	//加密传输
                $('.transfer-encrypt-method-form').hide();	//加密传输算法
            }
        }
    }

    //openstack传输模式改变
    var openstackModeChange = function(){
        var value = $(this).val();
        if(3 == value){
            $('.applianceselectdiv').show();
            $('.applianceshow').show();
            if(!applianceFlag){
                initApplianceSelect();
            }
            $('.keep-recovery-volumes-div').show();
        }else{
            $('.applianceselectdiv').hide();
            $('.applianceshow').hide();
            $('.keep-recovery-volumes-div').hide();
            $('#keepRecoveryVolumes').bootstrapSwitch('state', false); //默认关闭
        }

        //网络传输支持支持数据传输网段和加密传输
        if(value == 1){
            $('.ipSegmentDiv').show();	//显示数据传输网段
            $('.encrypttransferdiv').show();	//加密传输
            if ($('#encrypttransfer').bootstrapSwitch('state')) {
                $('.transfer-encrypt-method-form').show();
            }
        }else{
            $('.ipSegmentDiv').hide();	//隐藏数据传输网段
            $('#ipSegment').val('');
            $('.encrypttransferdiv').hide();	//加密传输
            $('.transfer-encrypt-method-form').hide();
        }
    }

    var huaweikvmChange = function(){
        var value = parseInt(this.value);
        $('.systemIpdiv').hide();	//备份系统IP
        //API LAN无加密传输
        switch(value){
            case 1:
                $('.encrypttransferdiv').show();				//显示加密传输
                if ($('#encrypttransfer').bootstrapSwitch('state')) {
                    $('.transfer-encrypt-method-form').show();
                }
                $('.ipSegmentDiv').show();				//显示传输网段
                break;
            case 5:
                $('.encrypttransferdiv').hide();				//隐藏加密传输
                $('.transfer-encrypt-method-form').hide();
                $('.systemIpdiv').show();	//备份系统IP
                $('.ipSegmentDiv').hide();				//隐藏传输网段
                $('#ipSegment').val('');
                break;
        }
    }

    var redhatTransChange = function(){
        var value = parseInt(this.value);
        $('.ipSegmentDiv').show();				//显示传输网段
        if(value == 5 || value == 2){
            $('.encrypttransferdiv').hide();	//加密传输
            $('.transfer-encrypt-method-form').hide();
        }else{
            $('.encrypttransferdiv').show();	//加密传输
            if ($('#encrypttransfer').bootstrapSwitch('state')) {
                $('.transfer-encrypt-method-form').show();
            }
        }

        //SAN传输不显示传输网段
        if(value == 2){
            $('.ipSegmentDiv').hide();	//隐藏数据传输网段
            $('#ipSegment').val('');
        }
    }

    var leixentransChange = function(){
        var value = parseInt(this.value);
        //网络传输支持支持数据传输网段和加密传输
        if(value == 1){
            $('.ipSegmentDiv').show();	//显示数据传输网段
            $('.encrypttransferdiv').show();	//加密传输
            if ($('#encrypttransfer').bootstrapSwitch('state')) {
                $('.transfer-encrypt-method-form').show();
            }
        }else{
            $('.ipSegmentDiv').hide();	//隐藏数据传输网段
            $('#ipSegment').val('');
            $('.encrypttransferdiv').hide();	//加密传输
            $('.transfer-encrypt-method-form').hide();
        }
        //ICSlanfree恢复只支持单线程数恢复
        if(value == 2 && parseInt(_sourceHypervisor) == CONF.VM_TYPE.INCLOUDKVM){
            $('#recoveryThreadNum').val(1).prop('disabled', true);
            $('#recoveryThreadDiv button').prop('disabled', true);
        }else{
            $('#recoveryThreadNum').prop('disabled', false);
            $('#recoveryThreadDiv button').prop('disabled', false);
        }
    }

    var smartxtransChange = function () {
        var value = parseInt(this.value);
        $('.ipSegmentDiv').hide();  //smartx屏蔽数据传输网段
        $('#ipSegment').val('');
        if(3 == value){
            $('.applianceselectdiv').show();
            $('.applianceshow').show();
            if(!applianceFlag){
                initApplianceSelect();
            }
            $('.encrypttransferdiv').show();	//加密传输
            $('#encrypttransfer').bootstrapSwitch('state', false);
        }else{
            $('.applianceselectdiv').hide();
            $('.applianceshow').hide();
            $('.encrypttransferdiv').hide();	//加密传输
            $('.transfer-encrypt-method-form').hide();	//加密传输算法
        }
    }

    var icsvvdktransChange = function () {
        var value = parseInt(this.value);
        $('.ipSegmentDiv').hide();  //icsvvdk屏蔽数据传输网段
        $('#ipSegment').val('');
        if(3 == value){
            $('.applianceselectdiv').show();
            $('.applianceshow').show();
            if(!applianceFlag){
                initApplianceSelect();
            }
            $('.encrypttransferdiv').show();	//加密传输
            $('#encrypttransfer').bootstrapSwitch('state', false);
        }else{
            $('.applianceselectdiv').hide();
            $('.applianceshow').hide();
            $('.encrypttransferdiv').hide();	//加密传输
            $('.transfer-encrypt-method-form').hide();	//加密传输算法
        }
    }

    var sangforvvdktransChange = function () {
        var value = parseInt(this.value);
        $('.ipSegmentDiv').hide();  //sangforvvdk屏蔽数据传输网段
        $('#ipSegment').val('');
        if(3 == value){
            $('.applianceselectdiv').show();
            $('.applianceshow').show();
            if(!applianceFlag){
                initApplianceSelect();
            }
            $('.encrypttransferdiv').show();
            $('#encrypttransfer').bootstrapSwitch('state', false);
        }else{
            $('.applianceselectdiv').hide();
            $('.applianceshow').hide();
            $('.encrypttransferdiv').hide();	//加密传输
            $('.transfer-encrypt-method-form').hide();	//加密传输算法
        }
    }

    var vmwareTransChange = function () {
        var value = $(this).val();
        if ('hotadd' == value && windowsFlag) {
            $('#vmware_hotadd_tips').show();
        } else {
            $('#vmware_hotadd_tips').hide();
        }
        if (['nbd', 'nbdssl'].includes(this.value)) {
            // nbd模式显示传输压缩并开启异步传输
            $('.transferCompressSwitchDiv').show();
            $('#transferCompressSwitch').bootstrapSwitch('state', false);
            $('.transferCompressDiv').hide();
            $('#transferCompress').val('unzip');
            $('#asyncTransfer').bootstrapSwitch('state', true);
        } else {
            $('.transferCompressSwitchDiv').hide();
            $('.transferCompressDiv').hide();
            $('#transferCompress').val('unzip');
            $('#asyncTransfer').bootstrapSwitch('state', false);
        }
    }

    // 显示加密算法
    var transferEncryptChange = function(){
        if(this.checked){
            $('.transfer-encrypt-method-form').show();
        }else{
            $('.transfer-encrypt-method-form').hide();
        }
    }

    //得到开关的结果描述   开启/关闭
    var getSwitchDes = function(check){
        if(check){
            return LANG.UI_PUBLIC_ON;
        }
        return LANG.UI_PUBLIC_OFF;
    }

    // 获取确认配置的加密传输描述
    var getEncryptDes = function (transferEncrypt) {
        let encryptDes = $('.encrypttransferlabel').html() + ": " + getSwitchDes(transferEncrypt) + "<br>";
        // 传输加密算法
        if ($('#encrypttransfer').get(0).checked) {
            let encryptedMethodLabel = $('.transfer-encrypt-method-label').html();
            let method = parseInt($('#transferEncryptMethod').val());
            let grade = '';
            switch (method) {
                case 1:
                    grade = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_RSA;
                    break;
                case 2:
                    grade = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_SM;
                    break;
            }
            encryptDes += encryptedMethodLabel + ": " + grade + "<br>";
        }
        return encryptDes;
    }

    /**
     * 恢复第三步配置的显示和隐藏
     * options对象结构如下：
     * {
     *     _sourceHypervisor: 1, // 源（时间点）虚拟化类型
     *     _hypervisor: 1, // 目标虚拟化类型
     *     storage_type: 11 // 存储类型
     * }
     */
    const step3show = function (options) {
        _sourceHypervisor = options._sourceHypervisor;
        _hypervisor = options._hypervisor;
        windowsFlag = options.windowsFlag;
        let authFun = options.authFun;
        let storage_type = options.storage_type;

        $('#leixentransmode').prop('disabled', false);     //初始化传输模式状态
        $('#usedomainDiv').hide(); 							//隐藏统一可用域配置
        $('.appliancediv').hide();			     			//隐藏proxy代理模块
        $('.xentransdiv').hide()                         //XenServer传输模式
        $('.leixentransdiv').hide();                       //类XenServer传输模式
        $('.smartxtransdiv').hide();                       //smartx传输模式
        $('.openstacktransdiv').hide();                       //openstack传输模式
        $('.huaweitransportdiv').hide();					//华为传输模式
        $('.huaweikvmtransportdiv').hide();					//华为kvm传输模式
        $('.systemIpdiv').hide();
        $('.redhattransdiv').hide();						//红帽传输模式
        $('.icsvvdktransportdiv').hide();					//icsvvdk传输模式
        $('.sangforvvdktransdiv').hide();					//scp传输模式

        // 传输压缩
        $('.transferCompressSwitchDiv').hide();
        $('.transferCompressDiv').hide();
        $('#transferCompress').val('unzip');
        // 异步传输
        $('#asyncTransfer').bootstrapSwitch('state', false);
        // 并行传输
        $('.parallelTransferDiv').hide();

        if (!applianceFlag && !appliancecheckFlag) {
            $('#appliancecheck').bootstrapSwitch('state', false);  //proxy默认关闭
            $('.applianceselectdiv').hide();					//隐藏选择proxy代理
        }

        $('#highstrategyshowdiv').show();
        $('.transferLi').show();

        $('.speedlimitDiv').show(); 			//显示限速策略
        $('#highstrategyshowdiv').show();		//显示限速策略描述
        $('#speedstrategyshowdiv').show();		//显示多线程描述
        //初始化高级配置tab
        $('.commonLi').removeClass('active').addClass('active');
        $('.transferLi').removeClass('active');
        $('.safeLi').removeClass('active');
        $('.highLi').removeClass('active');
        $('#tab_common').removeClass('active').addClass('active');
        $('#tab_transfer').removeClass('active');
        $('#tab_safe').removeClass('active');
        $('#tab_other').removeClass('active');
        $('.applianceshow').hide();	//隐藏proxy
        $('#transportmodeshowdiv').show();					//显示传输策略确认
        $('.storageHighLoadDiv').hide();
        $('#storageHighLoad').bootstrapSwitch('state', false); //默认关闭
        $('#recoveryThreadNum').prop('disabled', false); //启用线程数选择
        $('#recoveryThreadDiv button').prop('disabled', false);
        $('#otherverifytips').show(); //默认显示虚拟机数据验证的提示信息
        $('#cbrverifytips').hide();
        if(!initHighFlag){
            $('.ipSegmentDiv').hide();	//隐藏数据传输网段
            $('#ipSegment').val('');
            $('.encrypttransferdiv').hide();		//隐藏加密传输
            $('.transfer-encrypt-method-form').hide();
        }
        switch(_hypervisor){
            case CONF.VM_TYPE.VMWARE:
            case CONF.VM_TYPE.CLOUDVIEW:
            case CONF.VM_TYPE.CLOUDVIEWSVM:
                $('.transportmodediv').show();						//显示传输策略
                $('#transportmodeshowdiv').show();					//显示传输策略确认
                $('.appliancediv').show();			     			//显示proxy代理模块
                $('.applianceshow').show();							//显示proxy
                // 传输压缩
                $('.transferCompressSwitchDiv').show();
                $('#transferCompressSwitch').bootstrapSwitch('state', false);
                $('#transferCompress').val('unzip');
                $('.transferCompressDiv').hide();
                // 异步传输
                $('#asyncTransfer').bootstrapSwitch('state', true);
                //如果前面的存储介质是华为CBR 因为目前华为CBR宿主机只能恢复到vmware 传输策略只能是网络传输 把其他的隐藏掉
                if(storage_type == 12){
                    $('.appliancediv').hide();
                    $("#transport_mode option").hide();
                    $("#transport_mode option[value = 'nbd']").show();
                    //修改提示信息
                    $('.alltips').hide();
                    $('.nbdtips').show();
                    $('#otherverifytips').hide();
                    $('#cbrverifytips').show();
                }else{
                    $("#transport_mode option").show();
                    $('.alltips').show();
                    $('.nbdtips').hide();
                    $('#otherverifytips').show();
                    $('#cbrverifytips').hide();
                }
                break;
            case CONF.VM_TYPE.XHERE:
                $('.transportmodediv').hide();
                $('#leixentransmode').val('2').prop('disabled', true); //锁定为SAN传输
                $('.transferLi').hide(); // 隐藏传输策略tab
                break;
            case CONF.VM_TYPE.CITRIX:
            case CONF.VM_TYPE.XCPNG:
                $('.xentransdiv').show()                         	//XenServer传输模式
                $('.transportmodediv').hide();						//隐藏传输策略

                var mode = $('#xentransmode').val();
                if (mode == 1) {
                    $('.encrypttransferdiv').show();					//显示加密传输
                    if ($('#encrypttransfer').bootstrapSwitch('state')) {
                        $('.transfer-encrypt-method-form').show();
                    }
                    $('.ipSegmentDiv').show();	//显示数据传输网段
                }
                break;
            case CONF.VM_TYPE.INCLOUD:
            case CONF.VM_TYPE.VGATE:
            case CONF.VM_TYPE.WINSERVER:
            case CONF.VM_TYPE.WINDIY:
            case CONF.VM_TYPE.DSERVER:
                $('.leixentransdiv').show();                       	//XenServer传输模式
                $('.transportmodediv').hide();						//隐藏传输策略
                $('.encrypttransferdiv').show();					//显示加密传输
                if ($('#encrypttransfer').bootstrapSwitch('state')) {
                    $('.transfer-encrypt-method-form').show();
                }
                $('.ipSegmentDiv').show();	//显示数据传输网段
                break;
            case CONF.VM_TYPE.HYPERV:
                $('#leixentransmode').prop('disabled', true);     	//初始化传输模式状态
                $('.leixentransdiv').hide();                       	//XenServer传输模式
                $('.transportmodediv').hide();						//隐藏传输策略
                $('.speedlimitDiv').show(); 						//显示限速策略
                $('#highstrategyshowdiv').hide();					//隐藏多线程描述
                $('#speedstrategyshowdiv').show();					//显示限速策略描述
                $('.encrypttransferdiv').show();					//显示加密传输
                if ($('#encrypttransfer').bootstrapSwitch('state')) {
                    $('.transfer-encrypt-method-form').show();
                }

                //hyperv只能单线程
                $('#recoveryThreadNum').val(1).prop('disabled', true);
                $('#recoveryThreadDiv .spinner-group-btn button').prop('disabled', true);
                $('.ipSegmentDiv').show();	//显示数据传输网段
                break;
            case CONF.VM_TYPE.FUSIONXEN:
                $('.transportmodediv').hide();                      //隐藏传输策略
                $('.huaweitransportdiv').show();					//华为传输模式
                $("#huaweitransport_mode option[value='san']").remove();
                $('#highstrategyshowdiv').hide();
                break;
            case CONF.VM_TYPE.RHV:
            case CONF.VM_TYPE.OVIRT:
            case CONF.VM_TYPE.ZVIRT:
            case CONF.VM_TYPE.OLVM:
            case CONF.VM_TYPE.HOSTVM:
            case CONF.VM_TYPE.REDVIRT:
            case CONF.VM_TYPE.ROSAVIRT:
                $('.transportmodediv').hide();						//隐藏传输策略
                $('.redhattransdiv').show();						//红帽传输模式
                $('.ipSegmentDiv').show();	//显示数据传输网段
                //ImageIO传输隐藏加密传输
                var  mode = $('#redhattransmode').val();
                if(mode == 5 || mode == 2){
                    $('.encrypttransferdiv').hide();					//隐藏加密传输
                    $('.transfer-encrypt-method-form').hide();
                }else{
                    $('.encrypttransferdiv').show();					//显示加密传输
                    if ($('#encrypttransfer').bootstrapSwitch('state')) {
                        $('.transfer-encrypt-method-form').show();
                    }
                }
                if ((CONF.VM_TYPE.RHV == _hypervisor
                    || CONF.VM_TYPE.HOSTVM == _hypervisor
                    || CONF.VM_TYPE.REDVIRT == _hypervisor
                    || CONF.VM_TYPE.ROSAVIRT == _hypervisor
                    || CONF.VM_TYPE.OVIRT == _hypervisor
                ) && 2 == mode) {
                    //ovirt SAN传输隐藏数据传输网段
                    $('.ipSegmentDiv').hide();
                }
                break;
            case CONF.VM_TYPE.NEOKYLIN:
            case CONF.VM_TYPE.OSEASYVSERVER:
            case CONF.VM_TYPE.EASTEDVSERVER:
                $('.transportmodediv').hide();						//隐藏传输策略
                $('.leixentransdiv').show();                        //XenServer传输模式
                $('.encrypttransferdiv').show();					//显示加密传输
                if ($('#encrypttransfer').bootstrapSwitch('state')) {
                    $('.transfer-encrypt-method-form').show();
                }
                $('.ipSegmentDiv').show();	//显示数据传输网段
                break;
            case CONF.VM_TYPE.ZSTACK:
            case CONF.VM_TYPE.ZSTACKZSPHERE:
            case CONF.VM_TYPE.NEXAVM:
            case CONF.VM_TYPE.XSKY:
            case CONF.VM_TYPE.CLOUDVIEWKVM:
                $('.transportmodediv').hide();						//隐藏传输策略
                $('.leixentransdiv').show();                        //XenServer传输模式
                if(!initHighFlag){
                    $('#leixentransmode').val(2);
                    $('.ipSegmentDiv').hide();	//数据传输网段
                    $('.encrypttransferdiv').hide();			//加密传输
                    $('.transfer-encrypt-method-form').hide();
                }
                break;
            case CONF.VM_TYPE.WINHONGKVM:
                $('.transportmodediv').hide();						//隐藏传输策略
                $('.leixentransdiv').show();                        //XenServer传输模式
                $('.encrypttransferdiv').show();					//显示加密传输
                if ($('#encrypttransfer').bootstrapSwitch('state')) {
                    $('.transfer-encrypt-method-form').show();
                }
                if(!initHighFlag){
                    $("#leixentransmode").val(2);	//默认SAN传输
                    $('.encrypttransferdiv').hide();					//隐藏加密传输
                    $('.transfer-encrypt-method-form').hide();
                    $('.ipSegmentDiv').hide();	//数据传输网段
                }
                var mode = $('#leixentransmode').val();
                if (mode == 2) {
                    $('.encrypttransferdiv').hide();					//SAN传输隐藏加密传输
                    $('.transfer-encrypt-method-form').hide();
                } else {
                    $('.encrypttransferdiv').show();
                    if ($('#encrypttransfer').bootstrapSwitch('state')) {
                        $('.transfer-encrypt-method-form').show();
                    }
                }
                break;
            case CONF.VM_TYPE.PROXMOX:
            case CONF.VM_TYPE.LENOVOAIO:
                $('.transportmodediv').hide();						//隐藏传输策略
                $('.leixentransdiv').show();                        //XenServer传输模式
                $('.encrypttransferdiv').show();					//显示加密传输
                if ($('#encrypttransfer').bootstrapSwitch('state')) {
                    $('.transfer-encrypt-method-form').show();
                }
                if(!initHighFlag){
                    $("#leixentransmode").val(1);	//默认网络传输
                    $('.encrypttransferdiv').hide();					//隐藏加密传输
                    $('.transfer-encrypt-method-form').hide();
                    $('.ipSegmentDiv').show();	//数据传输网段
                }
                var mode = $('#leixentransmode').val();
                if (mode == 2) {
                    $('.encrypttransferdiv').hide();					//SAN传输隐藏加密传输
                    $('.transfer-encrypt-method-form').hide();
                } else {
                    $('.encrypttransferdiv').show();
                    if ($('#encrypttransfer').bootstrapSwitch('state')) {
                        $('.transfer-encrypt-method-form').show();
                    }
                }
                break;
            case CONF.VM_TYPE.SMARTX:
            case CONF.VM_TYPE.ARCFRA:
                $('.transportmodediv').hide();						//隐藏传输策略
                $('.smartxtransdiv').show();                        //smartx传输模式
                $('.ipSegmentDiv').hide();	//数据传输网段
                $('#smartxtransmode').val(2); //传输模式默认SAN

                var mode = $('#smartxtransmode').val();
                if (3 == mode) {
                    $('.applianceselectdiv').show();
                    $('.applianceshow').show();
                    $('.encrypttransferdiv').show();
                } else {
                    $('.applianceselectdiv').hide();
                    $('.applianceshow').hide();
                    $('.encrypttransferdiv').hide();					//隐藏加密传输
                    $('.transfer-encrypt-method-form').hide();	//加密传输算法
                }
                break;
            case CONF.VM_TYPE.FUSIONKVM:
            case CONF.VM_TYPE.XFUSIONKVM:
                $('.transportmodediv').hide();						//隐藏传输策略
                $('.huaweikvmtransportdiv').show();					//华为kvm传输模式
                $('.encrypttransferdiv').hide();				//隐藏加密传输
                $('.transfer-encrypt-method-form').hide();
                if(!initHighFlag){
                    $('#huaweikvmtransport_mode').val('5').prop('disabled', true);			//默认AP_LAN
                }
                //NBD传输显示备份系统IP参数
                var  mode = $('#huaweikvmtransport_mode').val();
                if(mode == 5){
                    $('.systemIpdiv').show();	//备份系统IP
                    $('.ipSegmentDiv').hide();	//显示数据传输网段
                    $('#ipSegment').val('');
                }else{
                    $('.systemIpdiv').hide();	//备份系统IP
                    $('.ipSegmentDiv').show();	//显示数据传输网段
                }
                // 并行传输
                $('.parallelTransferDiv').show();
                $('.threadNumDiv').hide();
                break;
            case CONF.VM_TYPE.H3C:
            case CONF.VM_TYPE.H3CCASCVD:
                $('#transportmodeshowdiv').show();
                $('.transportmodediv').hide();						//隐藏传输策略
                $('.leixentransdiv').hide();                        //XenServer传输模式
                $('.encrypttransferdiv').show();					//显示加密传输
                if ($('#encrypttransfer').bootstrapSwitch('state')) {
                    $('.transfer-encrypt-method-form').show();
                }
                $('.ipSegmentDiv').show();	//显示数据传输网段
                break;
            case CONF.VM_TYPE.INCLOUDKVM:
                $('#transportmodeshowdiv').show();
                $('.transportmodediv').hide();						//隐藏传输策略
                $('.leixentransdiv').show();                        //XenServer传输模式
                $('.encrypttransferdiv').show();					//显示加密传输
                if ($('#encrypttransfer').bootstrapSwitch('state')) {
                    $('.transfer-encrypt-method-form').show();
                }
                $('.ipSegmentDiv').show();	//显示数据传输网段
                $("#leixentransmode").val(1).prop('disabled', true);
                break;
            case CONF.VM_TYPE.KVM:
            case CONF.VM_TYPE.SANGFOR:
            case CONF.VM_TYPE.SDCOS:
                $('.transportmodediv').hide();						//隐藏传输策略
                $('#transportmodeshowdiv').show();					//显示传输策略确认
                $('.encrypttransferdiv').show();					//显示加密传输
                if ($('#encrypttransfer').bootstrapSwitch('state')) {
                    $('.transfer-encrypt-method-form').show();
                }
                $('.ipSegmentDiv').show();	//显示数据传输网段
                break;
            case CONF.VM_TYPE.SANGFORVVDK:
                $('.transportmodediv').hide();						//隐藏传输策略
                $('.sangforvvdktransdiv').show();						//sangfor vvdk传输策略
                $('#transportmodeshowdiv').show();					//显示传输策略确认
                var mode = $('#sangforvvdktransmode').val();
                if (3 == mode) {
                    $('.applianceselectdiv').show();
                    $('.applianceshow').show();
                    $('.encrypttransferdiv').show();
                } else {
                    $('.applianceselectdiv').hide();
                    $('.applianceshow').hide();
                    $('.encrypttransferdiv').hide();					//隐藏加密传输
                    $('.transfer-encrypt-method-form').hide();	//加密传输算法
                }
                if (!initHighFlag) {
                    $('#sangforvvdktransmode').val(1); 			//默认网络传输
                    $('.encrypttransferdiv').hide();					//隐藏加密传输
                    $('.transfer-encrypt-method-form').hide();	//加密传输算法
                }
                break;
            case CONF.VM_TYPE.FLEXCLOUD:
            case CONF.VM_TYPE.FLEXHCS:
            case CONF.VM_TYPE.OPENSTACK:
            case CONF.VM_TYPE.INCLOUDOPENSTACK:
            case CONF.VM_TYPE.SUGONCLOUDVIEW: //34
            case CONF.VM_TYPE.INSPURCLOUDPLATFORM: //35
            case CONF.VM_TYPE.EASYSTACK: //36
            case CONF.VM_TYPE.FIBERHOMEOPENSTACK: //37
            case CONF.VM_TYPE.CTSIOPENSTACK: //38
            case CONF.VM_TYPE.AWCLOUD: //39
            case CONF.VM_TYPE.HUAWEICLOUDSTACK: //54
                $('.transportmodediv').hide();						//隐藏传输策略
                $('.openstacktransdiv').show();                     //openstack传输模式
                $('#usedomainDiv').show(); 							//显示统一可用域配置
                if(!initHighFlag){
                    $('#openstacktransmode').val(4);					//默认lanfree传输
                    $('.encrypttransferdiv').hide();			//数据加密
                    $('.transfer-encrypt-method-form').hide();
                    $('.ipSegmentDiv').hide();	//数据传输网段
                }
                if (CONF.VM_TYPE.INSPURCLOUDPLATFORM == _hypervisor) {
                    $('.highDiv').show();
                    $('.storageHighLoadDiv').show();
                }
                if (options.specificFlag) {
                    // 指定实例恢复隐藏传输代理模式
                    $('#openstacktransmode').find(`option[value=3]`).hide();
                } else {
                    $('#openstacktransmode').find(`option[value=3]`).show();
                }
                break;
            case CONF.VM_TYPE.INSPURVVDK:
            case CONF.VM_TYPE.KSPHERE:
                $('.transportmodediv').hide();						//隐藏传输策略
                $('.icsvvdktransportdiv').show();					//icsvvdk传输模式
                $('#icsvvdktransport_mode').val(1);
                $('#transportmodeshowdiv').show();

                var mode = $('#icsvvdktransport_mode').val();
                if (3 == mode) {
                    $('.applianceselectdiv').show();
                    $('.applianceshow').show();
                    $('.encrypttransferdiv').show();
                } else {
                    $('.applianceselectdiv').hide();
                    $('.applianceshow').hide();
                    $('.encrypttransferdiv').hide();					//隐藏加密传输
                    $('.transfer-encrypt-method-form').hide();	//加密传输算法
                }

                $('.ipSegmentDiv').hide();	//隐藏数据传输网段

                break;
            case CONF.VM_TYPE.HUAWEICBR:
                break;
        }

        if(CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw"){
            initHighFlag = true;
        }else{
            if(4 == CONF.SOFTWARE) {
                $('.ipSegmentDiv').hide();  //免费版隐藏数据传输网段
            }
        }

        //判断是否有多线程授权
        if (!CONF.FUNCTIONS.includes('multithread')) {
            $('#recoveryThreadNum').val(1).attr("disabled", true); //单线程
            $('#recoveryThreadDiv .spinner-group-btn button').prop('disabled', true);
        }

        if (storage_type == 10) { //磁带只限制一条线程
            $('#recoveryThreadNum').val(1).prop('disabled', true);
            $('#recoveryThreadDiv .spinner-group-btn button').prop('disabled', true);
            $('.threadNumDiv').hide();

            // 时间点存储为磁带时，并行传输的线程数全部固定为1
            $(`#ptDiskCount`).val(1).prop('disabled', true);
            $(`#ptDiskCount`).next().find(`button`).prop('disabled', true);
            $(`#ptSegmentCount`).val(1).prop('disabled', true);
            $(`#ptSegmentCount`).next().find(`button`).prop('disabled', true);
            $(`.parallelTransferDiv`).hide();
        } else {
            $(`#ptDiskCount`).prop('disabled', false);
            $(`#ptDiskCount`).next().find(`button`).prop('disabled', false);
            $(`#ptSegmentCount`).prop('disabled', false);
            $(`#ptSegmentCount`).next().find(`button`).prop('disabled', false);
            if (CONF.VM_TYPE.FUSIONKVM == _hypervisor || CONF.VM_TYPE.XFUSIONKVM == _hypervisor) {
                $(`.parallelTransferDiv`).show();
                $('.threadNumDiv').hide();
            }
        }
    }

    // 初始化事件
    const initListeners = function () {
        //appliance开关切换回调
        $('#appliancecheck').on('switchChange.bootstrapSwitch', applianceChange);

        //openstack传输模式切换显示appliance
        $('#openstacktransmode').on('change', openstackModeChange);

        //华为KVM传输模式切换
        $('#huaweikvmtransport_mode').on('change', huaweikvmChange);

        //红帽传输模式切换
        $('#redhattransmode').on('change', redhatTransChange);

        //切换类xen传输模式
        $('#leixentransmode').on('change', leixentransChange);

        //切换smartx传输模式
        $('#smartxtransmode').on('change', smartxtransChange);

        //XenServer传输模式
        $('#xentransmode').on('change', leixentransChange);

        //切换icsvvdk传输模式
        $('#icsvvdktransport_mode').on('change', icsvvdktransChange);

        //切换sangforvvdk传输模式
        $('#sangforvvdktransmode').on('change', sangforvvdktransChange);

        //切换vmware传输模式
        $('#transport_mode').on('change', vmwareTransChange);

        // 传输策略---加密传输
        $('#encrypttransfer').on('switchChange.bootstrapSwitch', transferEncryptChange);

        // 传输压缩开关
        $(`#transferCompressSwitch`).on('switchChange.bootstrapSwitch', function () {
            if (this.checked) {
                $('#transferCompress').val('fastlz');
                $(`.transferCompressDiv`).show();
            } else {
                // 关闭传输压缩选中unzip
                $(`#transferCompress`).val('unzip');
                $(`.transferCompressDiv`).hide();
            }
        });
    }

    const initSpinner = function () {
        $('.ptDiskInputDiv').spinner({value:1, step: 1, min: 1});
        $('.ptSegmentInputDiv').spinner({value:3, step: 1, min: 1, max: 8});
    }

    // 初始化
    $.fn.vmRecoveryStep3Show = function (options) {
        step3show(options);
        initListeners();
        initSpinner();
    }

    // 重置flag
    $.fn.vmRecoveryResetOptions = function () {
        initHighFlag = false;	//切换虚拟化类型重置高级配置默认选项
        appliancecheckFlag = false;
        applianceFlag = false;
    }

    /**
     * 获取传输策略数据
     * @param data
     * @param options
     * options对象结构如下：
     * {
     *     _hypervisor: 1, // 目标虚拟化类型
     *     selectIPFlag: false, // 是否自动选择备份系统IP,华为KVM使用
     * }
     * @returns {*|boolean}
     */
    $.fn.getVmRecoveryTransferData = function (data, options) {
        let _hypervisor = options._hypervisor;
        let selectIPFlag = options.selectIPFlag;

        let transferData = {};
        transferData.mode = $('#transport_mode').val();
        transferData.encrypt = $('#encrypttransfer').get(0).checked;
        // 传输加密算法
        transferData.encrypt_method = parseInt($('#transferEncryptMethod').val());

        //appliance
        transferData.appliancecheck = $('#appliancecheck').get(0).checked;

        //并行传输
        transferData.single_vm_parallel_disk_transfer_count = parseInt($('#ptDiskCount').val());
        transferData.vm_single_disk_parallel_transfer_count = parseInt($('#ptSegmentCount').val());
        if (!transferData.single_vm_parallel_disk_transfer_count) {
            UIToastr.showWarning(LANG.UI_STRATEGY_TRANSFER, LANG.UI_VM_BACKUP_PARALLEL_TRANSFER_DISK_COUNT_ERROR_TIPS);
            return false;
        }
        if (!transferData.vm_single_disk_parallel_transfer_count) {
            UIToastr.showWarning(LANG.UI_STRATEGY_TRANSFER, LANG.UI_VM_BACKUP_PARALLEL_TRANSFER_SEGMENT_COUNT_ERROR_TIPS);
            return false;
        }

        switch (_hypervisor) {
            case CONF.VM_TYPE.CITRIX:
            case CONF.VM_TYPE.XCPNG:
                transferData.mode = parseInt($('#xentransmode').val());
                break;
            case CONF.VM_TYPE.INCLOUD:
            case CONF.VM_TYPE.VGATE:
            case CONF.VM_TYPE.WINSERVER:
            case CONF.VM_TYPE.WINDIY:
            case CONF.VM_TYPE.DSERVER:
            case CONF.VM_TYPE.XHERE:
                transferData.mode = parseInt($('#leixentransmode').val());
                break;
            case CONF.VM_TYPE.OLVM:
            case CONF.VM_TYPE.RHV:
            case CONF.VM_TYPE.OVIRT:
            case CONF.VM_TYPE.ZVIRT:
            case CONF.VM_TYPE.HOSTVM:
            case CONF.VM_TYPE.REDVIRT:
            case CONF.VM_TYPE.ROSAVIRT:
                transferData.mode = parseInt($('#redhattransmode').val());
                break;
            case CONF.VM_TYPE.NEOKYLIN:
            case CONF.VM_TYPE.OSEASYVSERVER:
            case CONF.VM_TYPE.INCLOUDKVM:
            case CONF.VM_TYPE.H3C:
            case CONF.VM_TYPE.H3CCASCVD:
            case CONF.VM_TYPE.HYPERV:
            case CONF.VM_TYPE.ZSTACK:
            case CONF.VM_TYPE.ZSTACKZSPHERE:
            case CONF.VM_TYPE.NEXAVM:
            case CONF.VM_TYPE.EASTEDVSERVER:
            case CONF.VM_TYPE.XSKY:
            case CONF.VM_TYPE.WINHONGKVM:
            case CONF.VM_TYPE.PROXMOX:
            case CONF.VM_TYPE.CLOUDVIEWKVM:
            case CONF.VM_TYPE.LENOVOAIO:
                transferData.mode = parseInt($('#leixentransmode').val());
                break;
            case CONF.VM_TYPE.SMARTX:
            case CONF.VM_TYPE.ARCFRA:
                transferData.mode = parseInt($('#smartxtransmode').val());
                if (transferData.mode == 3) {
                    transferData.appliancecheck = true;
                }
                break;
            case CONF.VM_TYPE.FUSIONKVM:
            case CONF.VM_TYPE.XFUSIONKVM:
                transferData.mode = parseInt($('#huaweikvmtransport_mode').val());
                break;
            case CONF.VM_TYPE.FLEXCLOUD:
            case CONF.VM_TYPE.OPENSTACK:
            case CONF.VM_TYPE.FLEXHCS:
            case CONF.VM_TYPE.INCLOUDOPENSTACK:
            case CONF.VM_TYPE.SUGONCLOUDVIEW: //34
            case CONF.VM_TYPE.INSPURCLOUDPLATFORM: //35
            case CONF.VM_TYPE.EASYSTACK: //36
            case CONF.VM_TYPE.FIBERHOMEOPENSTACK: //37
            case CONF.VM_TYPE.CTSIOPENSTACK: //38
            case CONF.VM_TYPE.AWCLOUD: //39
            case CONF.VM_TYPE.HUAWEICLOUDSTACK: //54
                transferData.mode = parseInt($('#openstacktransmode').val());
                if (transferData.mode == 3) {
                    transferData.appliancecheck = true;
                }
                break;
            case CONF.VM_TYPE.KVM:
            case CONF.VM_TYPE.SANGFOR:
            case CONF.VM_TYPE.SDCOS:
                transferData.mode = 1;
                break;
            case CONF.VM_TYPE.SANGFORVVDK:
                transferData.mode = parseInt($('#sangforvvdktransmode').val());
                if (transferData.mode == 3) {
                    transferData.appliancecheck = true;
                }
                break;
            case CONF.VM_TYPE.FUSIONXEN:
                transferData.mode = $('#huaweitransport_mode').val();
                break;
            case CONF.VM_TYPE.INSPURVVDK:
            case CONF.VM_TYPE.KSPHERE:
                transferData.mode = parseInt($('#icsvvdktransport_mode').val());
                if (transferData.mode == 3) {
                    transferData.appliancecheck = true;
                }
                break;
        }
        transferData.threadnum = $('#recoveryThreadNum').val();
        transferData.agent_uuid = "";
        transferData.agent_pool_uuid = "";
        if (transferData.appliancecheck) {
            let applianceNode = $('#transferAgentTree').transferAgent('getSelect');
            if (false === applianceNode || !applianceNode.agent_uuid && !applianceNode.agent_pool_uuid) {
                UIToastr.showWarning(LANG.UI_APPLIANCE_SELECT, LANG.UI_APPLIANCE_SELECT_NO_TIPS);
                return false;
            }
            transferData.agent_uuid = applianceNode.agent_uuid;
            transferData.agent_pool_uuid = applianceNode.agent_pool_uuid;
        }
        //华为KVM的NBD传输模式
        if ((_hypervisor == CONF.VM_TYPE.FUSIONKVM || _hypervisor == CONF.VM_TYPE.XFUSIONKVM) && transferData.mode == 5) {
            if (selectIPFlag) {
                //自动选择
                transferData.backup_server_ip = $('#systemIp').val();
            } else {
                //自定义
                transferData.backup_server_ip = $.trim($('#inputIp').val());
                if (transferData.backup_server_ip && !ipV4V6(transferData.backup_server_ip)) {
                    UIToastr.showWarning(LANG.UI_STRATEGY_TRANSFER, LANG.UI_BACKUP_SERVER_IP_INVALID_TIPS);
                    return false;
                }
            }
            //检查备份系统节点IP
            if ("" == transferData.backup_server_ip) {
                UIToastr.showInfo(LANG.UI_STRATEGY_TRANSFER,LANG.UI_BACKUP_SERVER_IP_EMPTY_TIPS);
                return false;
            }
        } else {
            //其余备份系统IP设为空
            transferData.backup_server_ip = "";
        }

        //数据传输网段
        transferData.transport_ip_segment = $('#ipSegment').val();
        //正则检查数据传输网段内容,如果不为空则进行正则检查
        if (!(transferData.transport_ip_segment == '' || transferData.transport_ip_segment == null || transferData.transport_ip_segment == undefined)) {
            var ipv4CheckRul = new RegExp(/((2(5[0-5]|[0-4]\d))|[0-1]?\d{1,2})(\.((2(5[0-5]|[0-4]\d))|[0-1]?\d{1,2})){3}\/\d{1,2}/);
            if (!ipv4CheckRul.test(transferData.transport_ip_segment)) {
                UIToastr.showInfo(LANG.UI_STRATEGY_TRANSFER, LANG.UI_BACKUP_IPV4_TIPS);
                return false;
            }
        }
        transferData.transfer_compress = $('#transferCompress').find('option:selected').val();
        transferData.async_transfer = $('#asyncTransfer').get(0).checked;
        transferData.keep_recovery_volumes = $('#keepRecoveryVolumes').get(0).checked;

        return transferData;
    }

    $.fn.getVmRecoveryTransferDes = function (data, tapeStorageFlag = false) {
        //传输策略
        var transferlabel = $('.transferlabel').html();
        var xentranslabel = $('.xentranslabel').html();
        var leixentranslabel = $('.leixentranslabel').html();
        var redhattranslabel = $('.redhattranslabel').html();
        var openstacktranslabel = $('.openstacktranslabel').html();
        var huaweitranslabel = $('.huaweitransferlabel').html();
        var huaweikvmtransferlabel = $('.huaweikvmtransferlabel').html();
        var encryptlabel = $('.encrypttransferlabel').html();
        var des = "";
        let transferEncrypt = data.typeInfo.high.trasfer.encrypt;
        //加密传输
        switch(_hypervisor){
            case CONF.VM_TYPE.VMWARE:
            case CONF.VM_TYPE.CLOUDVIEW:
            case CONF.VM_TYPE.CLOUDVIEWSVM:
                // 开启传输代理支持加密传输
                if (data.appliancecheck) {
                    des += getEncryptDes(transferEncrypt);
                }
                des += transferlabel + ": " + $('#transport_mode').find("option:selected").text();
                // NBD传输模式显示传输压缩
                if (['nbd', 'nbdssl'].includes(data.typeInfo.high.trasfer.mode)) {
                    des += "<br>" + $('.transferCompressSwitchLabel').html() + ": " + getSwitchDes($('#transferCompressSwitch').bootstrapSwitch('state'));
                    if ($('#transferCompressSwitch').bootstrapSwitch('state')) {
                        des += "<br>" + $('.transferCompressLabel').html() + ": " + $('#transferCompress').find('option:selected').html();
                    }
                }
                break;
            case CONF.VM_TYPE.INSPURVVDK:
            case CONF.VM_TYPE.XHERE:
            case CONF.VM_TYPE.KSPHERE:
                des += transferlabel + ": " + $('#leixentransmode').find("option:selected").text();
                break;
            case CONF.VM_TYPE.HYPERV:
                // des += leixentranslabel + ": " + $('#leixentransmode').find("option:selected").text()+ "  ";
                des += getEncryptDes(transferEncrypt);
                break;
            case CONF.VM_TYPE.CITRIX:
            case CONF.VM_TYPE.XCPNG:
                //网络传输支持加密传输
                if(data.typeInfo.high.trasfer.mode == 1){
                    des += getEncryptDes(transferEncrypt);
                }
                des += "  " + xentranslabel + ": " +  $('#xentransmode').find("option:selected").text();
                break;
            case CONF.VM_TYPE.OLVM:
            case CONF.VM_TYPE.RHV:
            case CONF.VM_TYPE.OVIRT:
            case CONF.VM_TYPE.ZVIRT:
            case CONF.VM_TYPE.HOSTVM:
            case CONF.VM_TYPE.REDVIRT:
            case CONF.VM_TYPE.ROSAVIRT:
                if(data.typeInfo.high.trasfer.mode != 5 && data.typeInfo.high.trasfer.mode != 2){
                    des += getEncryptDes(transferEncrypt);
                }
                des += redhattranslabel + ": " +  $('#redhattransmode').find("option:selected").text();
                break;
            case CONF.VM_TYPE.H3C:
            case CONF.VM_TYPE.H3CCASCVD:
                des += getEncryptDes(transferEncrypt);
                break;
            case CONF.VM_TYPE.INCLOUD:
            case CONF.VM_TYPE.VGATE:
            case CONF.VM_TYPE.WINSERVER:
            case CONF.VM_TYPE.WINDIY:
            case CONF.VM_TYPE.DSERVER:
            case CONF.VM_TYPE.NEOKYLIN:
            case CONF.VM_TYPE.OSEASYVSERVER:
            case CONF.VM_TYPE.INCLOUDKVM:
            case CONF.VM_TYPE.ZSTACK:
            case CONF.VM_TYPE.ZSTACKZSPHERE:
            case CONF.VM_TYPE.NEXAVM:
            case CONF.VM_TYPE.EASTEDVSERVER:
            case CONF.VM_TYPE.XSKY:
            case CONF.VM_TYPE.WINHONGKVM:
            case CONF.VM_TYPE.PROXMOX:
            case CONF.VM_TYPE.CLOUDVIEWKVM:
            case CONF.VM_TYPE.LENOVOAIO:
                //网络传输支持加密传输
                if(data.typeInfo.high.trasfer.mode == 1){
                    des += getEncryptDes(transferEncrypt);
                }
                des += xentranslabel + ": " +  $('#leixentransmode').find("option:selected").text();
                break;
            case CONF.VM_TYPE.SMARTX:
            case CONF.VM_TYPE.ARCFRA:
                // 传输代理支持加密传输
                if (data.typeInfo.high.trasfer.mode == 3) {
                    des += getEncryptDes(transferEncrypt);
                }
                des += xentranslabel + ": " +  $('#smartxtransmode').find("option:selected").text();
                break;
            case CONF.VM_TYPE.FUSIONKVM:
            case CONF.VM_TYPE.XFUSIONKVM:
                if(data.typeInfo.high.trasfer.mode != 5){
                    des += getEncryptDes(transferEncrypt);
                }
                des += huaweikvmtransferlabel + ": " +  $('#huaweikvmtransport_mode').find("option:selected").text();
                if(data.typeInfo.high.trasfer.mode == 5){
                    //备份系统IP
                    var serveriplabel = $('.serveriplabel').html();
                    des +=	"<br>" + serveriplabel + ": " + data.backup_server_ip;
                }
                if (!tapeStorageFlag) {
                    des += "<br>" + $('.ptDiskLabel').html() + ": " + $('#ptDiskCount').val();
                    des += "<br>" + $('.ptSegmentLabel').html() + ": " + $('#ptSegmentCount').val();
                }
                break;
            case CONF.VM_TYPE.FLEXCLOUD:
            case CONF.VM_TYPE.OPENSTACK:
            case CONF.VM_TYPE.FLEXHCS:
            case CONF.VM_TYPE.INCLOUDOPENSTACK:
            case CONF.VM_TYPE.SUGONCLOUDVIEW: //34
            case CONF.VM_TYPE.INSPURCLOUDPLATFORM: //35
            case CONF.VM_TYPE.EASYSTACK: //36
            case CONF.VM_TYPE.FIBERHOMEOPENSTACK: //37
            case CONF.VM_TYPE.CTSIOPENSTACK: //38
            case CONF.VM_TYPE.AWCLOUD: //39
            case CONF.VM_TYPE.HUAWEICLOUDSTACK: //54
                //网络传输支持加密传输
                if(data.typeInfo.high.trasfer.mode == 1){
                    des += getEncryptDes(transferEncrypt);
                }
                des += openstacktranslabel + ": " +  $('#openstacktransmode').find("option:selected").text();
                break;
            case CONF.VM_TYPE.FUSIONXEN:
                des = huaweitranslabel + ": " + $('#huaweitransport_mode').find("option:selected").text();
                break;
            case CONF.VM_TYPE.KVM:
            case CONF.VM_TYPE.SANGFOR:
            case CONF.VM_TYPE.SDCOS:
                //网络传输支持加密传输
                if(data.typeInfo.high.trasfer.mode == 1){
                    des += getEncryptDes(transferEncrypt);
                }
                break;
            case CONF.VM_TYPE.SANGFORVVDK:
                // 传输代理支持加密传输
                if (data.typeInfo.high.trasfer.mode == 3) {
                    des += getEncryptDes(transferEncrypt);
                }
                des += transferlabel + ": " + $('#sangforvvdktransmode').find("option:selected").text() + "  ";
                break;
            case CONF.VM_TYPE.INSPURVVDK:
            case CONF.VM_TYPE.KSPHERE:
                // 传输代理支持加密传输
                if (data.typeInfo.high.trasfer.mode == 3) {
                    des += getEncryptDes(transferEncrypt);
                }
                des += transferlabel + ": " + $('#icsvvdktransport_mode').find("option:selected").text();
                break;
        }
        var threadnumlabel = $('.threadnumlabel').html();
        // 非磁带存储标题显示传输线程
        if (!tapeStorageFlag && CONF.VM_TYPE.FUSIONKVM != _hypervisor && CONF.VM_TYPE.XFUSIONKVM != _hypervisor) {
            des += "<br>" + threadnumlabel + ": " + $('#recoveryThreadNum').val();
        }
        //存在数据传输网段
        if(data.transport_ip_segment != ""){
            var ipsegmentlabel = $('.ipsegmentlabel').html();
            des += "<br>" + ipsegmentlabel + ": " + data.transport_ip_segment;
        }
        $('.transportinfoshow').html(des);

        //proxy代理
        var appliancelabel = $('.appliancelabel').html();
        var applianceInfo = appliancelabel + ": " + getSwitchDes(data.appliancecheck);
        if(data.appliancecheck){
            let applianceNode = $('#transferAgentTree').transferAgent('getSelect');
            if (false !== applianceNode) {
                if (applianceNode.agent_uuid) {
                    applianceInfo += ', ' + applianceNode.name + '(' + LANG.UI_AGENT_POOL_TABLE_AGENT + ')';
                } else {
                    applianceInfo += ', ' + applianceNode.name + '(' + LANG.UI_AGENT_POOL + ')';
                }
            }
        }
        $('.applianceshow').html(applianceInfo);
    }
})(jQuery)
