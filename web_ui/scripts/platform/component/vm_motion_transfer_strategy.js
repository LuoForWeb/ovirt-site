var VmMotionTransferStrategy = function () {
    var applianceFlag = false;
    var selectIPFlag = true;

    var authFun = [];
    var windowsFlag = false;
    var platformUuid = '';
    var hypervisor = 0;
    var data = {};

    // 选中宿主机节点事件绑定
    var hostOnCheck = function(){
        //显示隐藏项目
        if(1 == hypervisor || 21 == hypervisor){
            $(".transportdiv").show();
        }
        $('#leixentransmode').prop('disabled', false);     //初始化传输模式状态
        // 传输压缩
        $('.transferCompressSwitchDiv').hide();
        $('.transferCompressDiv').hide();
        $('#transferCompress').val('unzip');
        $(".threadnumdiv").show();
        $('.appliancediv').hide();			     			//隐藏proxy代理模块
        $('.applianceselectdiv').hide();					//隐藏选择proxy代理
        $('#appliancecheck').bootstrapSwitch('state', false);  //proxy默认关闭
        $('.intDiv').hide();	//隐藏数据传输网段
        $('#ipSegment').val('');
        $('#threadNum').prop('disabled', false); //启用线程数选择
        $('#threadNumDiv button').prop('disabled', false);
        switch(hypervisor){
            case CONF.VM_TYPE.VMWARE:
            case CONF.VM_TYPE.CLOUDVIEW:
            case CONF.VM_TYPE.CLOUDVIEWSVM:
                initApplianceSelect();
                $('.appliancediv').show();			     	//隐藏proxy代理模块
                // 传输压缩
                $('.transferCompressSwitchDiv').show();
                $('#transferCompressSwitch').bootstrapSwitch('state', false);
                $('#transferCompress').val('unzip');
                $('.transferCompressDiv').hide();
                break;
            case CONF.VM_TYPE.CITRIX:
            case CONF.VM_TYPE.XCPNG:
                $(".xentransdiv").show();
                $('.ipSegmentDiv').show();	//显示数据传输网段
                break;
            case CONF.VM_TYPE.RHV:
            case CONF.VM_TYPE.OVIRT:
            case CONF.VM_TYPE.ZVIRT:
            case CONF.VM_TYPE.OLVM:
            case CONF.VM_TYPE.HOSTVM:
            case CONF.VM_TYPE.REDVIRT:
            case CONF.VM_TYPE.ROSAVIRT:
                $(".redhattransdiv").show();
                $('.ipSegmentDiv').show();	//显示数据传输网段
                var  mode = $('#redhattransmode').val();
                if ((CONF.VM_TYPE.RHV == hypervisor
                    || CONF.VM_TYPE.HOSTVM == hypervisor
                    || CONF.VM_TYPE.REDVIRT == hypervisor
                    || CONF.VM_TYPE.ROSAVIRT == hypervisor
                    || CONF.VM_TYPE.OVIRT == hypervisor
                ) && 2 == mode) {
                    //ovirt SAN传输隐藏数据传输网段
                    $('.ipSegmentDiv').hide();
                }
                break;
            case CONF.VM_TYPE.INCLOUD:
            case CONF.VM_TYPE.VGATE:
            case CONF.VM_TYPE.WINSERVER:
            case CONF.VM_TYPE.WINDIY:
            case CONF.VM_TYPE.DSERVER:
            case CONF.VM_TYPE.NEOKYLIN:
            case CONF.VM_TYPE.EASTEDVSERVER:
                $(".leixentransdiv").show();
                $('.ipSegmentDiv').show();	//显示数据传输网段
                break;
            case CONF.VM_TYPE.ZSTACK:
            case CONF.VM_TYPE.ZSTACKZSPHERE:
            case CONF.VM_TYPE.NEXAVM:
            case CONF.VM_TYPE.NEXAVMNCSSV:
            case CONF.VM_TYPE.XSKY:
            case CONF.VM_TYPE.CLOUDVIEWKVM:
                $(".leixentransdiv").show();
                $('#leixentransmode').val(2);
                $('.ipSegmentDiv').hide();	//显示数据传输网段
                $('.encrypttransferdiv').hide();			//加密传输
                break;
            case CONF.VM_TYPE.OSEASYVSERVER:
            case CONF.VM_TYPE.WINHONGKVM:
            case CONF.VM_TYPE.SMARTX:
            case CONF.VM_TYPE.PROXMOX:
            case CONF.VM_TYPE.LENOVOAIO:
                $(".leixentransdiv").show();
                $('.ipSegmentDiv').hide();	//隐藏数据传输网段
                $("#leixentransmode").val(2);	//默认SAN传输
                break;
            case CONF.VM_TYPE.FUSIONKVM:
            case CONF.VM_TYPE.XFUSIONKVM:
                $('.huaweikvmtransportdiv').show();					//华为kvm传输模式
                $('#huaweikvmtransport_mode').val('5').prop('disabled', true);			//默认AP_LAN
                $('.applianceshow').hide();
                //NBD传输显示备份系统IP参数
                var  mode = $('#huaweikvmtransport_mode').val();
                if(mode == 5){
                    $('.systemIpdiv').show();	//备份系统IP
                    $('.ipSegmentDiv').hide();	//隐藏数据传输网段
                    $('#ipSegment').val('');
                }else{
                    $('.systemIpdiv').hide();	//备份系统IP
                    $('.ipSegmentDiv').show();	//显示数据传输网段
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
                initApplianceSelect();
                $(".openstacktransdiv").show();
                $('#openstacktransmode').val(4);						//默认lanfree传输
                $('.ipSegmentDiv').hide();
//					$('.appliancediv').show();			     //显示proxy代理模块
                break;
            case CONF.VM_TYPE.HYPERV:
                $('#leixentransmode').prop('disabled', true);       //只支持网络传输
                $(".leixentransdiv").show();
                $(".threadnumdiv").hide();
                $('.ipSegmentDiv').show();
                break;
            case CONF.VM_TYPE.INCLOUDKVM:
            case CONF.VM_TYPE.H3C:
            case CONF.VM_TYPE.H3CCASCVD:
                $(".leixentransdiv").hide();
                $('.ipSegmentDiv').show();	//显示数据传输网段
                if (CONF.VM_TYPE.H3CCASCVD == hypervisor) {
                    // cas_cvd 隐藏数据传输网段
                    $('.ipSegmentDiv').hide();
                }
                $("#leixentransmode").val(1).prop('disabled', true);
                break;
            case CONF.VM_TYPE.FUSIONXEN:
                $("#huaweitransport_mode option[value='san']").remove();
                $(".huaweitransportdiv").show();
                $(".threadnumdiv").hide();
                break;
            case CONF.VM_TYPE.KVM:
            case CONF.VM_TYPE.SANGFOR:
            case CONF.VM_TYPE.SANGFORVVDK:
            case CONF.VM_TYPE.SDCOS:
                $('.ipSegmentDiv').show();
                break;
            case CONF.VM_TYPE.INSPURVVDK:
            case CONF.VM_TYPE.VOLC:
            case CONF.VM_TYPE.KSPHERE:
                initApplianceSelect();
                $(".leixentransdiv").hide();
                $('.ipSegmentDiv').hide();	//隐藏数据传输网段
                $("#leixentransmode").val(1).prop('disabled', true);

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
        }
        $(".vmdiv").show();
        //判断是否有多线程授权
        if(!CONF.FUNCTIONS.includes('multithread')){
            $('#threadNum').val(1).attr("disabled", true); //单线程
            $('.threadNumDiv .spinner-group-btn button').prop('disabled', true);
        }
    }

    //检测用户输入
    var checkInfo = function(){
        data.hypervisor = hypervisor;
        //线程数量
        data.threadNum = $('#threadNum').val();
        var thread = $('#threadNum').val();
        if(thread == "" || thread > 8 || thread <= 0){
            UIToastr.showWarning(LANG.UI_BACKUP_THREAD_NUM_TIPS);
            return false;
        }
        //传输模式
        data.transportmode = $('#transport_mode').val();
        // 传输压缩
        data.transfer_compress = $('#transferCompress').find('option:selected').val();
        // 传输加密
        data.encrypt_flag = $('#encrypttransfer').get(0).checked;
        // 传输加密算法
        data.encrypt_method = parseInt($('#transferEncryptMethod').val());

        data.appliancecheck = $('#appliancecheck').get(0).checked;
        switch(hypervisor){
            case CONF.VM_TYPE.CITRIX:
            case CONF.VM_TYPE.XCPNG:
                //如果是XenServer
                data.transportmode = parseInt($('#xentransmode').val());
                break;
            case CONF.VM_TYPE.INCLOUD:
            case CONF.VM_TYPE.VGATE:
            case CONF.VM_TYPE.WINSERVER:
            case CONF.VM_TYPE.WINDIY:
            case CONF.VM_TYPE.DSERVER:
                data.transportmode = parseInt($('#leixentransmode').val());
                break;
            case CONF.VM_TYPE.OLVM:
            case CONF.VM_TYPE.RHV:
            case CONF.VM_TYPE.OVIRT:
            case CONF.VM_TYPE.ZVIRT:
            case CONF.VM_TYPE.HOSTVM:
            case CONF.VM_TYPE.REDVIRT:
            case CONF.VM_TYPE.ROSAVIRT:
                data.transportmode = parseInt($('#redhattransmode').val());
                break;
            case CONF.VM_TYPE.NEOKYLIN:
            case CONF.VM_TYPE.OSEASYVSERVER:
            case CONF.VM_TYPE.INCLOUDKVM:
            case CONF.VM_TYPE.H3C:
            case CONF.VM_TYPE.H3CCASCVD:
            case CONF.VM_TYPE.ZSTACK:
            case CONF.VM_TYPE.ZSTACKZSPHERE:
            case CONF.VM_TYPE.NEXAVM:
            case CONF.VM_TYPE.NEXAVMNCSSV:
            case CONF.VM_TYPE.EASTEDVSERVER:
            case CONF.VM_TYPE.XSKY:
            case CONF.VM_TYPE.WINHONGKVM:
            case CONF.VM_TYPE.SMARTX:
            case CONF.VM_TYPE.PROXMOX:
            case CONF.VM_TYPE.CLOUDVIEWKVM:
            case CONF.VM_TYPE.LENOVOAIO:
                data.transportmode = parseInt($('#leixentransmode').val());
                break;
            case CONF.VM_TYPE.FUSIONKVM:
            case CONF.VM_TYPE.XFUSIONKVM:
                data.transportmode = parseInt($('#huaweikvmtransport_mode').val());
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
                data.transportmode = parseInt($('#leixentransmode').val());
                if(data.transportmode == 3){
                    data.appliancecheck = true;
                }
                break;
            case CONF.VM_TYPE.FUSIONXEN:
                data.transportmode = $('#huaweitransport_mode').val();
                break;
            case CONF.VM_TYPE.INSPURVVDK:
            case CONF.VM_TYPE.VOLC:
            case CONF.VM_TYPE.KSPHERE:
                data.transportmode = parseInt($('#icsvvdktransport_mode').val());
                if (data.transportmode == 3) {
                    data.appliancecheck = true;
                }
                break;
        }
        //appliance
        data.agent_uuid = "";
        data.agent_pool_uuid = "";
        if (data.appliancecheck) {
            let applianceNode = $('#transferAgentTree').transferAgent('getSelect');
            if (false === applianceNode || !applianceNode.agent_uuid && !applianceNode.agent_pool_uuid) {
                UIToastr.showWarning(LANG.UI_APPLIANCE_SELECT, LANG.UI_APPLIANCE_SELECT_NO_TIPS);
                return false;
            }
            data.agent_uuid = applianceNode.agent_uuid;
            data.agent_pool_uuid = applianceNode.agent_pool_uuid;
        }

        //华为KVM的NBD传输模式
        if((hypervisor == CONF.VM_TYPE.FUSIONKVM || hypervisor == CONF.VM_TYPE.XFUSIONKVM) && data.transportmode == 5){
            if(selectIPFlag){
                //自动选择
                data.backup_server_ip = $('#systemIp').val();
            }else{
                //自定义
                data.backup_server_ip = $.trim($('#inputIp').val());
                if (data.backup_server_ip && !ipV4V6(data.backup_server_ip)) {
                    UIToastr.showWarning(LANG.UI_STRATEGY_TRANSFER, LANG.UI_BACKUP_SERVER_IP_INVALID_TIPS);
                    return false;
                }
            }
            //检查备份系统节点IP
            if("" == data.backup_server_ip){
                UIToastr.showInfo(LANG.UI_STRATEGY_TRANSFER,LANG.UI_BACKUP_SERVER_IP_EMPTY_TIPS);
                return false;
            }
        }else{
            //其余备份系统IP设为空
            data.backup_server_ip = "";
        }
        //数据传输网段
        data.transport_ip_segment = $('#ipSegment').val();
        if(!(data.transport_ip_segment =='' || data.transport_ip_segment == null || data.transport_ip_segment ==undefined)){
            var ipv4CheckRul = new RegExp(/((2(5[0-5]|[0-4]\d))|[0-1]?\d{1,2})(\.((2(5[0-5]|[0-4]\d))|[0-1]?\d{1,2})){3}\/\d{1,2}/);
            if(!ipv4CheckRul.test(data.transport_ip_segment)){
                UIToastr.showInfo(LANG.UI_STRATEGY_TRANSFER,LANG.UI_BACKUP_IPV4_TIPS);
                return false;
            }
        }
        return true;
    }

    var initListener = function(){

        //appliance开关切换回调
        $('#appliancecheck').on('switchChange.bootstrapSwitch', applianceChange);

        //openstack传输模式切换显示appliance
        $('#openstacktransmode').on('change', openstackModeChange);

        //自动选择IP
        $('#diysystemip').on('click', function(){
            $('.selectipdiv').hide();
            $('.inputipdiv').show();
            selectIPFlag = false;
        })

        //手动输入IP
        $('#selectsystemip').on('click', function(){
            $('.selectipdiv').show();
            $('.inputipdiv').hide();
            selectIPFlag = true;
        })

        //华为KVM传输模式切换
        $('#huaweikvmtransport_mode').on('change', huaweikvmChange);

        //红帽传输模式切换
        $('#redhattransmode').on('change', redhatTransChange);

        //切换类xen传输模式
        $('#leixentransmode').on('change', leixentransChange);

        //切换vmware传输模式
        $('#transport_mode').on('change', vmwareTransChange);

        //切换icsvvdk传输模式
        $('#icsvvdktransport_mode').on('change', icsvvdktransChange);

        //输入线程数量检测
        $('#threadNum').blur(threadChange);

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

    var threadChange = function(){
        var num = this.value;
        if(num > 8 || num < 1 || num == ""){
            //还原默认值并给出提示
            $('#threadNum').val(3);
            UIToastr.showWarning(LANG.UI_BACKUP_HIGH_SETTING_TITLE, LANG.UI_BACKUP_THREAD_NUM_TIPS);
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

    var leixentransChange = function(){
        var value = parseInt(this.value);
        //网络传输支持支持数据传输网段和加密传输
        if(value == 1){
            if (hypervisor == CONF.VM_TYPE.SMARTX) {
                $('.ipSegmentDiv').hide();
            } else {
                $('.ipSegmentDiv').show();	//显示数据传输网段
            }
            $('.encrypttransferdiv').show();	//加密传输
        }else{
            $('.ipSegmentDiv').hide();	//隐藏数据传输网段
            $('#ipSegment').val('');
            $('.encrypttransferdiv').hide();	//加密传输
        }
        switch(hypervisor){
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
                $('.ipSegmentDiv').hide();	//显示数据传输网段
                break
        }

        //ICSlanfree恢复只支持单线程数恢复
        if(value == 2 && hypervisor == CONF.VM_TYPE.INCLOUDKVM){
            $('#threadNum').val(1).prop('disabled', true);
            $('#threadNumDiv button').prop('disabled', true);
        }else{
            $('#threadNum').prop('disabled', false);
            $('#threadNumDiv button').prop('disabled', false);
        }
    }

    var redhatTransChange = function(){
        var value = parseInt(this.value);
        //SAN传输不显示传输网段
        if(value == 2){
            $('.ipSegmentDiv').hide();	//隐藏数据传输网段
            $('#ipSegment').val('');
        } else {
            $('.ipSegmentDiv').show();
        }
    }

    var huaweikvmChange = function(){
        var value = parseInt(this.value);
        $('.systemIpdiv').hide();	//备份系统IP
        //API LAN无加密传输
        switch(value){
            case 1:
                $('.ipSegmentDiv').show();				//显示传输网段
                break;
            case 5:
                $('.systemIpdiv').show();	//备份系统IP
                $('.ipSegmentDiv').hide();				//隐藏传输网段
                $('#ipSegment').val('');
                break;
        }
    }

    //openstack传输模式改变
    var openstackModeChange = function(){
        var value = $(this).val();
        if(3 == value){
            $('.applianceselectdiv').show();
            if(!applianceFlag){
                initApplianceSelect();
            }
        }else{
            $('.applianceselectdiv').hide();
        }
        //网络传输支持支持数据传输网段和加密传输
        if(value == 1){
            $('.ipSegmentDiv').show();	//显示数据传输网段
            $('.encrypttransferdiv').show();	//加密传输
        }else{
            $('.ipSegmentDiv').hide();	//隐藏数据传输网段
            $('#ipSegment').val('');
            $('.encrypttransferdiv').hide();	//加密传输
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

    var applianceChange = function(){
        if(this.checked){
            initApplianceSelect();
            $('.applianceselectdiv').show();
            if (CONF.VM_TYPE.VMWARE == hypervisor) {
                // vmware开启传输代理可配置加密传输
                $('.encrypttransferdiv').show();	//加密传输
            }
        }else{
            $('.applianceselectdiv').hide();
            if (CONF.VM_TYPE.VMWARE == hypervisor) {
                $('#encrypttransfer').bootstrapSwitch('state', false);
                $('.encrypttransferdiv').hide();	//加密传输
                $('.transfer-encrypt-method-form').hide();	//加密传输算法
            }
        }
    }
    if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
        if(4 == CONF.SOFTWARE) {
            $('.ipSegmentDiv').hide(); //隐藏数据传输网段
        }
    }

    //初始化appliance下拉框
    var initApplianceSelect = function(){
        if(applianceFlag) return; //只加载一次
        $('#transferAgentTree').transferAgent();
        applianceFlag = true;
    }

    //版本差异处理,主要是处理标准版本功能限制
    // 中文标准版 1
    // 中文企业版 2
    // 英文免费版 4
    // 英文基础版 9
    // 英文标准版 6
    // 英文企业版 7
    var initSoftwareVersionDiff = function(){
        pAjaxRequest({}, "/api/v1/users/auth_func", "GET", function (d) {
            let data = d.data;
            authFun = data;
            if(!data.lanfree){
                //传输模式只支持网络传输
                //删除transport_mode不可用项
                $("#transport_mode option[value='san']").remove();
                //删除xentransmode不可用项
                $("#xentransmode option[value='2']").remove();
                $("#xentransmode option[value='4']").remove();

                //删除类xen不可用项
                $("#leixentransmode option[value='2']").remove();

                //删除华为传输模式不可用项
                $("#huaweitransport_mode option[value='san']").remove();

                //华为KVM
                $("#huaweikvmtransport_mode option[value='2']").remove();
                //红帽传输模式
                $("#redhattransmode option[value='2']").remove();
            }

            if(1 == CONF.SOFTWARE || 4 == CONF.SOFTWARE){
                //传输模式只支持网络传输
                //删除transport_mode不可用项
                $("#transport_mode option[value='san']").remove();
                $("#transport_mode option[value='hotadd']").remove();
                //删除xentransmode不可用项
                $("#xentransmode option[value='2']").remove();
                $("#xentransmode option[value='4']").remove();

                //删除类xen不可用项
                $("#leixentransmode option[value='2']").remove();

                //删除华为传输模式不可用项
                $("#huaweitransport_mode option[value='san']").remove();

            }
        }, false);
    }

    var initSpinner = function(){
        $('#threadNumDiv').spinner({value:3, step: 1, min: 1, max: 8});
    }

    //初始化备份系统节点IP
    var initBackupServerAddr = function(nodeuuid){
        var data = {};
        data.node_uuid = nodeuuid;
        data.job_uuid = $('#task_uuid').val();
        pAjaxRequest(data, "/api/v1/nodes/all_ip", "GET", function (d) {
            var data = d.data.rows;
            var serveripaddr = $('#systemIp');
            serveripaddr.empty();
            for(var i=0; i<data.length; i++){
                var option = $("<option>").text(data[i]).val(data[i]);
                serveripaddr.append(option);
            }
        }, false);
        $('#inputIp').val(window.location.host);
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

    //得到开关的结果描述   开启/关闭
    var getSwitchDes = function(check){
        if(check){
            return LANG.UI_PUBLIC_ON;
        }
        return LANG.UI_PUBLIC_OFF;
    }

    const getDes = function (data) {
        let des = '';
        //传输策略
        //加密传输
        let transferEncrypt = data.transfer.encrypt_flag;
        switch(data.migrate_target_info.hypervisor_type){
            case CONF.VM_TYPE.VMWARE:
            case CONF.VM_TYPE.CLOUDVIEW:
            case CONF.VM_TYPE.CLOUDVIEWSVM:
                // 开启传输代理支持加密传输
                if (data.transfer.appliancecheck) {
                    des += getEncryptDes(transferEncrypt);
                }
                des += LANG.UI_GLOBAL_STRATEGY_TRANSFER_MODE + ": " + $('#transport_mode').find("option:selected").text();
                // NBD传输模式显示传输压缩
                if (['nbd', 'nbdssl'].includes(data.transfer.transportmode)) {
                    des += "<br>" + $('.transferCompressSwitchLabel').html() + ": " + getSwitchDes($('#transferCompressSwitch').bootstrapSwitch('state'));
                    if ($('#transferCompressSwitch').bootstrapSwitch('state')) {
                        des += "<br>" + $('.transferCompressLabel').html() + ": " + $('#transferCompress').find('option:selected').html();
                    }
                }
                break;
            case CONF.VM_TYPE.INSPURVVDK:
            case CONF.VM_TYPE.VOLC:
            case CONF.VM_TYPE.KSPHERE:
                //传输代理支持加密传输
                if(data.transfer.transportmode == 3){
                    des += getEncryptDes(transferEncrypt);
                }
                des += LANG.UI_GLOBAL_STRATEGY_TRANSFER_MODE + ": " + $('#icsvvdktransport_mode').find("option:selected").text();
                break;
            case CONF.VM_TYPE.XHERE:
                des += LANG.UI_GLOBAL_STRATEGY_TRANSFER_MODE + ": " + $('#leixentransmode').find("option:selected").text();
                break;
            case CONF.VM_TYPE.HYPERV:
                // des += leixentranslabel + ": " + $('#leixentransmode').find("option:selected").text()+ "  ";
                des += getEncryptDes(transferEncrypt);
                break;
            case CONF.VM_TYPE.CITRIX:
            case CONF.VM_TYPE.XCPNG:
                //网络传输支持加密传输
                if(data.transfer.transportmode == 1){
                    des += getEncryptDes(transferEncrypt);
                }
                des += "  " + LANG.UI_GLOBAL_STRATEGY_TRANSFER_MODE + ": " +  $('#xentransmode').find("option:selected").text();
                break;
            case CONF.VM_TYPE.OLVM:
            case CONF.VM_TYPE.RHV:
            case CONF.VM_TYPE.OVIRT:
            case CONF.VM_TYPE.ZVIRT:
            case CONF.VM_TYPE.HOSTVM:
            case CONF.VM_TYPE.REDVIRT:
            case CONF.VM_TYPE.ROSAVIRT:
                des += LANG.UI_GLOBAL_STRATEGY_TRANSFER_MODE + ": " +  $('#redhattransmode').find("option:selected").text();
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
            case CONF.VM_TYPE.NEXAVMNCSSV:
            case CONF.VM_TYPE.EASTEDVSERVER:
            case CONF.VM_TYPE.XSKY:
            case CONF.VM_TYPE.WINHONGKVM:
            case CONF.VM_TYPE.PROXMOX:
            case CONF.VM_TYPE.CLOUDVIEWKVM:
            case CONF.VM_TYPE.LENOVOAIO:
                //网络传输支持加密传输
                if(data.transfer.transportmode == 1){
                    des += getEncryptDes(transferEncrypt);
                }
                des += LANG.UI_GLOBAL_STRATEGY_TRANSFER_MODE + ": " +  $('#leixentransmode').find("option:selected").text();
                break;
            case CONF.VM_TYPE.SMARTX:
                // 传输代理支持加密传输
                if (data.transfer.transportmode == 3) {
                    des += getEncryptDes(transferEncrypt);
                }
                des += LANG.UI_GLOBAL_STRATEGY_TRANSFER_MODE + ": " +  $('#smartxtransmode').find("option:selected").text();
                break;
            case CONF.VM_TYPE.FUSIONKVM:
            case CONF.VM_TYPE.XFUSIONKVM:
                if(data.transfer.transportmode != 5){
                    des += getEncryptDes(transferEncrypt);
                }
                var huaweikvmtransferlabel = $('.huaweikvmtransferlabel').html();
                des += huaweikvmtransferlabel + ": " +  $('#huaweikvmtransport_mode').find("option:selected").text();
                if(data.transfer.transportmode == 5){
                    //备份系统IP
                    var serveriplabel = $('.serveriplabel').html();
                    des +=	"<br>" + serveriplabel + ": " + data.transfer.backup_server_ip;
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
                //网络传输支持加密传输
                if(data.transfer.transportmode == 1){
                    des += getEncryptDes(transferEncrypt);
                }
                des += LANG.UI_GLOBAL_STRATEGY_TRANSFER_MODE + ": " +  $('#openstacktransmode').find("option:selected").text();
                break;
            case CONF.VM_TYPE.FUSIONXEN:
                des = LANG.UI_GLOBAL_STRATEGY_TRANSFER_MODE + ": " + $('#huaweitransport_mode').find("option:selected").text();
                break;
            case CONF.VM_TYPE.KVM:
            case CONF.VM_TYPE.SANGFOR:
            case CONF.VM_TYPE.SDCOS:
                //网络传输支持加密传输
                if(data.transfer.transportmode == 1){
                    des += getEncryptDes(transferEncrypt);
                }
                break;
            case CONF.VM_TYPE.SANGFORVVDK:
                // 传输代理支持加密传输
                if (data.transfer.transportmode == 3) {
                    des += getEncryptDes(transferEncrypt);
                }
                des += LANG.UI_GLOBAL_STRATEGY_TRANSFER_MODE + ": " + $('#sangforvvdktransmode').find("option:selected").text() + "  ";
                break;
            case CONF.VM_TYPE.INSPURVVDK:
            case CONF.VM_TYPE.VOLC:
            case CONF.VM_TYPE.KSPHERE:
                // 传输代理支持加密传输
                if (data.transfer.transportmode == 3) {
                    des += getEncryptDes(transferEncrypt);
                }
                des += LANG.UI_GLOBAL_STRATEGY_TRANSFER_MODE + ": " + $('#icsvvdktransport_mode').find("option:selected").text();
                break;
        }
        if ('' !== des) {
            des += "<br>";
        }
        //存在数据传输网段
        if (data.transfer.transport_ip_segment != "") {
            var ipsegmentlabel = $('.ipsegmentlabel').html();
            des += ipsegmentlabel + ": " + data.transfer.transport_ip_segment + "<br>";
        }
        // 传输线程
        des += LANG.UI_GLOBAL_STRATEGY_THREAD_NUM + ':' + data.transfer.threadNum + "<br>";
        // 传输代理
        var appliancelabel = $('.appliancelabel').html();
        var applianceInfo = "<br>" + appliancelabel + ": " + getSwitchDes(data.transfer.appliancecheck);
        if (data.transfer.appliancecheck) {
            let applianceNode = $('#transferAgentTree').transferAgent('getSelect');
            if (false !== applianceNode) {
                if (applianceNode.agent_uuid) {
                    applianceInfo += ', ' + applianceNode.name + '(' + LANG.UI_AGENT_POOL_TABLE_AGENT + ')';
                } else {
                    applianceInfo += ', ' + applianceNode.name + '(' + LANG.UI_AGENT_POOL + ')';
                }
            }
        }
        if (applianceFlag) {
            des += applianceInfo;
        }

        return des;
    }

    return {
        //main function to initiate the module
        init: function (options) {
            hypervisor = options.hypervisor
            platformUuid = options.vcenteruuid
            initSoftwareVersionDiff();
            hostOnCheck(); // 节点改变就需要重新初始化这个组件
            initListener();
            initSpinner();
            initBackupServerAddr(options.nodeuuid);	//初始化备份系统节点IP
        },
        getInfo: function (){
            if (!checkInfo()) {
                return [];
            }

            return data;
        },
        getDes: function (params) {
            return getDes(params);
        }
    };

}();
