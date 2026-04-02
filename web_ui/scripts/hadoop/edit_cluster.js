var edit_cluster =  function(){
    //初始化基本信息
    //初始数据
    var data = {}
    var applianceFlag =  false;
    var initBasicInfo =  function(){
        var  requestBasicInfo  =  function(d){
            if(d.success){
                //初始化界面
                setBasicInfo(d.data)
                data =  d.data;
            }else{
                UIToastr.showWarning(LANG.UI_HADOOP_GET_CLUSTER_INFO,result.message);
            }
        }
        var params = {};
        params.cluster_uuid =  $("#clusteruuid").val();
        //获取基本数据
        pAjaxRequest(params, "/api/v1/hadoop/cluster", "GET", requestBasicInfo,true);
    }
    //初始化界面  
    var  setBasicInfo =  function(data){
        $('#clustername').val(data.cluster_name);
        //设置验证方式
        $('#verification').val(data.name_nodes[0].verification_type);
        //循环遍历节点添加节点
        for(var i = 0;i<data.name_nodes.length;i++){
            var item =  data.name_nodes[i];
            addNodeClick();
            //找到最新添加的节点框
            var currentform =  $('.addnodeform').eq(i+1);
            //验证方式
            // currentform.find('#verification').val(item.verification_type);
            if(item.verification_type == 1){
                $('.addnodeform').find('.kerberos').hide();
            }else if(item.verification_type == 2){
                $('.addnodeform').find('.kerberos').show();
                //需要设置kerberos文件
                //隐藏未选择的按钮 显示选择的按钮
                var  notchoosediv = currentform.find('#getkrb5FileBut').parents('.notchoosefile');
                var  choosediv = notchoosediv.siblings('.choosedfile');
                choosediv.find(".title").text(LANG.UI_HADOOP_EXIST_FILE_CONTENT);
                notchoosediv.hide();
                choosediv.show().css("display","inline-block"); 
            }
            currentform.find('#realmname').val(item.realm_name);
            currentform.find('#kdcserver').val(item.kdc_server);
            currentform.find('#manageserver').val(item.manager_server);
            // currentform.find('#rpcprincipal').val(item.rpc_api_principal);
            currentform.find('#restprincipal').val(item.rest_api_principal);
            currentform.find('#udpprincipal').val(item.udp_preference_limit);
            //设置主机 用户 端口
            currentform.find('#hostip').val(item.namenode_ip);
            currentform.find('#username').val(item.hadoop_user_name);
            currentform.find('#restapi').val(item.rest_api_port);
            // currentform.find('#rpcapi').val(item.rpc_api_port);
            //设置title
            currentform.find('.panel-title span').eq(0).text(LANG.UI_HADOOP_ADD_NAMENODE+"("+ item.namenode_ip +")");
            //将主机ip禁止修改
            currentform.find('#hostip').attr("disabled",true);
            //设置ssl
            var checkbox = currentform.find('#sslConnect');
            if (item.ssl_verify_flag == CONF.FLAG.UNSET) {
                checkbox.iCheck('uncheck');
                checkbox.prop("checked",false);
            } else {
                checkbox.iCheck('check');
                checkbox.prop("checked",true);
                checkbox.parent('.icheckbox_square-blue').addClass('checked');
            }
        }
        //传输策略
        data.appliancecheck = false;
        if(data.applianceuuid){
            data.appliancecheck = true;
        }
        if(data.applianceuuid){
			$('#appliancecheck').bootstrapSwitch('state', true); //Appliance默认开启
			$('.applianceselectdiv').show();
			initApplianceSelect();
		}else{
			$('#appliancecheck').bootstrapSwitch('state', false); //Appliance默认关闭
			$('.applianceselectdiv').hide();
		}
        
    }

    //数据验证
    var handleValidation =  function(){
        $('.addnodeform:visible').each(
            function(){
                var $form = $(this);
                $form.validate({
                    errorElement: 'span', //default input error message container
                    errorClass: 'help-block help-block-error', // default input error message class
                    focusInvalid: false, // do not focus the last invalid input
                    ignore: "",  // validate all fields including form hidden input
                    rules: {
                        hostip: {
                            required: true,
                        },
                        username:{
                            required: true
                        },
                        restapi:{
                            required: true
                        },
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
                    success: function (label, element) {
                        var icon = $(element).parent('.input-icon').children('i');
                        $(element).closest('.form-group').removeClass('has-error').addClass('has-success'); // set success class to the control group
                        icon.removeClass("fa-warning").addClass("fa-check");
                    }
                });
                
            }
        );
        //IP验证格式
        $.validator.addMethod("ipv4", function(value, element) {
            return this.optional(element) || ipV4V6(value);
        }, LANG.UI_SETTING_INPUT_IP);
    }
    var initListeners =  function(){
        //传输策略---传输代理
		$('#appliancecheck').on('switchChange.bootstrapSwitch', applianceChange);
        //验证方式改变
        $('#verification').on('change',verifyChange);
        //添加方式改变
        // $('body').on('change','#addition',additionChange)
        //krb5.keytab 文件选择监听
        $('body').on('change','#getkrb5File',filechange);
        //主机名字改变
        $('body').on('change','#hostip',hostchange);
        //krb5.keytab 选择文件
        $('.form-body').on('click','#getkrb5FileBut',function(){
            let krb5file =  $(this).parents('.form-group').find('#getkrb5File');
            krb5file.click();
        });
        //删除文件按钮
        $('.form-body').on('click','.deletefile',deletefile);
        //删除按钮监听
        //由于删除节点是动态添加的 后面添加的节点会监听不到 所以这样监听
        $('.form-body').on('click','.deleteNode',deleteNodeClick);
        //点击添加节点按钮
        $('#addNode').on('click',addNodeClick);
        //点击确定按钮
        $('#clustersubmit').on('click',submitEditCluster);
        //点击取消按钮
        $('#clutercancel').on('click',cancelEditCluster);
         //ssl勾选框
         $('body').on('click','#sslConnect',sslClick);
        //collapse标题栏点击
         $('body').on('click', 'form a',function() {
            var targetForm = $(this).closest('form'); // 获取当前点击按钮所在的<form>
            $('form a[data-toggle="collapse"]').not(this).each(function() { 
                var otherform = $(this).closest('form'); //获取该collapse所在的form
                if (!targetForm.is(otherform)) { // 如果按钮所在的<form>与当前点击按钮的<form>不同
                    var targetCollapseId = $(this).attr("href");
                    $(targetCollapseId).collapse('hide'); // 隐藏对应的collapse
                }
            });
        });
    }
    //显示传输代理
    var applianceChange = function(){
        if(this.checked){
            initApplianceSelect();
            $('.applianceselectdiv').show();
        }else{
            $('.applianceselectdiv').hide();
        }
    }
    //初始化传输代理下拉框
    var initApplianceSelect = function(){
        if(applianceFlag) return; //只加载一次
        $.post(CONF.AJAXPATH, {m:CONF.M.NODE,f:'getApplianceSelect',p:{}}, function(d){
            var jsondata = JSON.parse(d);
            if(!jsondata.length) return;
            var applianceSelect = $('#applianceSelect');
            applianceSelect.empty();
            for(var i=0;i<jsondata.length;i++){
                var option = $("<option>").text(jsondata[i].text).val(jsondata[i].value);
                applianceSelect.append(option);
            }
            applianceFlag = true;
            if(data.appliancecheck){
				applianceSelect.val(data.applianceuuid);
			}
        });
    }
     //验证方式改变
     var verifyChange =  function(){
        var value = parseInt(this.value);
        switch(value){
            case 1:
                $('.addnodeform').find('.kerberos').hide();
                break;
            case 2:
                $('.addnodeform').find('.kerberos').show();
                break;    
        }
        //验证方式切换 所有信息都清空
        $('.addnodeform').find($('input[name=realmname]')).val('');
        $('.addnodeform').find($('input[name=kdcserver]')).val('');
        $('.addnodeform').find($('input[name=manageserver]')).val('');
        $('.addnodeform').find($('input[name=restprincipal]')).val('');
        $('.addnodeform').find($('input[name=udpprincipal]')).val('');
        // 前面kerberos的内容清空 但是样式需要还原
        $('.addnodeform:visible').each(function(){
            $(this).each(function(){
                console.log("this",$(this));
                var maualtype =  $(this).find('.manualaddtype')
                console.log("maualtype",maualtype);
                maualtype.find('.fa-check').removeClass('fa-check');
                maualtype.find('.fa-warning').removeClass('fa-warning');
                maualtype.find('.has-success').removeClass('has-success');
                maualtype.find('.has-error').removeClass('has-error');
            });
        });
        //设置文件内容为空
        var choosediv =   $('.addnodeform ').find('.choosedfile');
        var notchoosediv = choosediv.siblings('.notchoosefile');
        var filediv  = $('.addnodeform ').find('input[type="file"]')
        filediv.data("data-text",null);
        filediv.val("");
        choosediv.hide();
        notchoosediv.show();
        changeverifyRules(value);
    }
    function arrayBufferToUnitArray(arrayBuffer) {
        const bytes = new Uint8Array(arrayBuffer);
        const newbytes = [];
        for(let i = 0; i < bytes.length; i++) {
            newbytes.push(bytes[i]);
        }
        return newbytes;
    }
    var filechange =  function(){
        var  file =  $(this);
        const reader = new FileReader();
        reader.readAsArrayBuffer(this.files[0]);
        var filename =  this.files[0].name;
        reader.onload = function(){
            var uint8Array = arrayBufferToUnitArray(reader.result);
            file.data("data-text",uint8Array)
        }
        //修改名字
        var chooseddiv = $(this).parents('.notchoosefile').siblings('.choosedfile');
        //截取filename前十二个字
        var showtitle = "";
        if(filename.length > 11){
            showtitle = filename.substring(0,11)+"...";
        }else{
            showtitle = filename;
        }
        chooseddiv.find('.title').text(showtitle);
        //隐藏未选择的按钮 显示选择的按钮
        $(this).parents('.notchoosefile').hide();
        chooseddiv.show().css("display","inline-block");
    }
    //删除文件
    var deletefile =  function(){
        //设置文件内容为空
        var choosediv =  $(this).parents('.choosedfile');
        var notchoosediv = choosediv.siblings('.notchoosefile');
        var filediv  = notchoosediv.find('input[type="file"]')
        filediv.data("data-text",null);
        filediv.val("");
        choosediv.hide();
        notchoosediv.show();
    }
    //主机名字改变
    var  hostchange  =  function(){
        var title  = $(this).parents('.addnodeform').find('.panel-title span').eq(0);
        title.text(LANG.UI_HADOOP_ADD_NAMENODE+"("+ this.value +")");
    }
    //验证方式改变是修改校验规则
    var  changeverifyRules =  function(value){
        var rules = {
            realmname: {
                required: true,
            },
            kdcserver: {
                required: true,
                ipv4: true,
            },
            manageserver: {
                required: true,
                ipv4: true,
            },
            restprincipal: {
                required: true,
            },
            udpprincipal: {
                required: true,
            },
        }
        var addnodeforms =  $('.addnodeform[id]');
        if(value == 1){
            for (let i = 0; i < addnodeforms.length; i++) {
                $(addnodeforms[i]).find($('input[name=realmname]')).rules('remove');
                $(addnodeforms[i]).find($('input[name=realmname]')).rules('remove');
                $(addnodeforms[i]).find($('input[name=kdcserver]')).rules('remove');;
                $(addnodeforms[i]).find($('input[name=manageserver]')).rules('remove');;
                $(addnodeforms[i]).find($('input[name=restprincipal]')).rules('remove');;
                $(addnodeforms[i]).find($('input[name=udpprincipal]')).rules('remove');;
                // addnodeforms[i].validate();
            }
        }else{
            //认证方式添加
            var addnodeforms =  $('.addnodeform[id]');
            for (let i = 0; i < addnodeforms.length; i++) {
                $(addnodeforms[i]).find($('input[name=realmname]')).rules('add',rules.realmname);
                $(addnodeforms[i]).find($('input[name=realmname]')).rules('add',rules.realmname);
                $(addnodeforms[i]).find($('input[name=kdcserver]')).rules('add',rules.kdcserver);
                $(addnodeforms[i]).find($('input[name=manageserver]')).rules('add',rules.manageserver);
                $(addnodeforms[i]).find($('input[name=restprincipal]')).rules('add',rules.restprincipal);
                $(addnodeforms[i]).find($('input[name=udpprincipal]')).rules('add',rules.udpprincipal);
                // addnodeforms[i].validate();
            }
        }
    }
    //点击添加节点
    var addNodeClick =  function(){
        //获取添加节点表单元素
        let firstform =  $('.addnodeform').eq(0);
        let formhtml =  firstform.prop('outerHTML');
        let lastform = $('.add_hadoop_form .addnodeform:last');;
        let lastid =  lastform.attr("id");
        //获取index
        let index = 0;
        if(lastid != "" && lastid  !=  null){
            let indexarr = lastid.split("_");
            index =  indexarr[1];
        }
        index++;
        lastform.after(formhtml);
        //获取最新的lastform
        lastform =   $('.add_hadoop_form .addnodeform:last');
        lastform.attr("id","nodeform_"+index);
        $('.add_hadoop_form .addnodeform:last a').attr("href","#nodeinfo_"+index);
        $('.add_hadoop_form .addnodeform:last #nodeinfo').attr("id","nodeinfo_"+index);
        lastform.show();
        for (let i = 0; i < index; i++) {
            $('#nodeinfo_'+i).collapse('hide');	//收起
        }
        $('#nodeinfo_'+index).collapse('show');	//展开
        handleValidation();
        changeverifyRules($('#verification').val());
    }
     //点击删除节点
     var deleteNodeClick =  function(e){
        e.preventDefault();
        let href =  $(this).parents('a').attr("href");
        //获取index
        let indexarr = href.split("_");
        let index =  indexarr[1];
        //如果只剩一个 则不能删除
        let forms =  $(".add_hadoop_form").find(".addnodeform");
        let length =  forms.length;
        if(length > 2){
            $('#nodeform_'+index).remove();
            //获取删除的是第几个元素  如果是第一个 则下一个元素要隐藏firstlabel
        }else{
            UIToastr.showWarning(LANG.UI_NODE_DELETE_NODE,LANG.UI_HADOOP_DELETE_NODE_TIPS);
        }
    }
    //ssl证书认证
    var sslClick  = function(){
        var checkflag  = this.checked;
        if(checkflag){
            $(this).parent('.icheckbox_square-blue').addClass('checked');
        }else{
            $(this).parent('.icheckbox_square-blue').removeClass('checked');
        }
    }
    //修改集群
    var  submitEditCluster =  function(){
        var cluster_name =  $('#clustername').val();
         // 输入验证
		if(!customInputValidate('string',cluster_name)){
			return false;
		}
        if(!cluster_name){
            UIToastr.showWarning(LANG.UI_HADOOP_ADD_CLUSTER_TITLE,LANG.UI_HADOOP_ADD_CLUSTER_TIPS);
            return;
        }
        var allformVaild =  true;
        $('.addnodeform:visible').each(function(){
            if(!$(this).valid()){
                allformVaild =  false;
            }
        })
        if(!allformVaild){
            return;
        }
        //组装数据
        //获取删除的数据
        var deletenode = getDeleteNode();
        var curnode =  getNodeList();
        //判断kerberos验证方式是否上传了krb5文件
        // var notselectfile  =  false;
        // for (let i = 0; i < curnode.length; i++) {
        //     if((curnode[i].krb5_keytab.length == 0 || curnode[i].krb5_keytab == undefined)  && curnode[i].verification_type ==  2){
        //         notselectfile =  true;
        //         break;
        //     }
        // }
        // if(notselectfile){
        //     UIToastr.showWarning(LANG.UI_HADOOP_ADD_CLUSTER_TITLE, LANG.UI_HADOOP_SELECT_KRB5_FILE_TIPS);
		// 	return;
        // }
        //获取传输代理
        var appliancecheck = $('#appliancecheck').get(0).checked;
        var applianceuuid = $('#applianceSelect').find('option:selected').val();
        if(appliancecheck){
			if(!applianceuuid){
				UIToastr.showWarning(LANG.UI_APPLIANCE_SELECT, LANG.UI_APPLIANCE_SELECT_NO_TIPS);
				return;
			}
		}else{
			applianceuuid = "";
		}
        var requestdata =  {
            "cluster_id":data.cluster_uuid,
            "hadoop_cluster_name":$('#clustername').val(),
            "deleted_namenode_ip":deletenode,
            "modify_namenodes":curnode,
            "applianceuuid":applianceuuid
        }
        checkOperateAuth({
            type: 2,
            source_uuid: data.cluster_uuid,
            source_type: 60
        },function(){
            //发送数据
            Metronic.blockUI({target: $("#EditClusterDiv"),animate: true});
            pAjaxRequest(requestdata, "/api/v1/hadoop/cluster", "PUT", function(result){
                Metronic.unblockUI($("#EditClusterDiv"));
                if(result.success){
                    UIToastr.showSuccess(LANG.UI_HADOOP_EDIT_CLUSTER,result.message);
                    LOCATION('./content/hadoop/hadoop_cluster.php','infrastructure');
                }else{
                    UIToastr.showWarning(LANG.UI_HADOOP_EDIT_CLUSTER,result.message);
                }
            });
        });
       

    }
    //获取删除的数据
    var getDeleteNode =  function(){
        //获取目前为止所有的数据
        var curnodelist = [];
        var deletenode = [];
        let forms =  $(".add_hadoop_form").find(".addnodeform");
        for (let index = 0; index < forms.length; index++) {
            var formitem = $(forms[index]);
            if(formitem.attr("id") != null){
                var nodeip =  formitem.find("#hostip").val();
                curnodelist.push(nodeip)
            }
        }
        //遍历之前的节点IP 如果不存在新的当中 则这个节点删掉了 
        for(let i = 0;i<data.name_nodes.length;i++){
             if(curnodelist.indexOf(data.name_nodes[i].namenode_ip) == -1){
                deletenode.push(data.name_nodes[i].namenode_ip);
             }
        }
        return deletenode;
    }
    //获取目前界面上的节点（包括新增和修改的）
    var getNodeList = function(){
        var nodelist = [];
        let forms =  $(".add_hadoop_form").find(".addnodeform");
        for (let index = 0; index < forms.length; index++) {
            var formitem = $(forms[index]);
            if(formitem.attr("id") != null){
                var verifytype =  parseInt($("#verification").val());
                //节点IP
                var nodeip =  formitem.find("#hostip").val();
                var restapi  = formitem.find("#restapi").val();
                var username = formitem.find("#username").val();
                var realmname = formitem.find("#realmname").val();
                var kdcserver = formitem.find("#kdcserver").val();
                var manageserver = formitem.find("#manageserver").val();
                var restprincipal= formitem.find("#restprincipal").val();
                var udpprincipal= formitem.find("#udpprincipal").val();
                var krb5 = formitem.find("#getkrb5File").data('data-text');
                if(krb5 == null){
                    krb5 = [];
                }
                var sslflag =  formitem.find('#sslConnect').is(":checked") ? CONF.FLAG.SET : CONF.FLAG.UNSET;
                var nodeitem = {
                    "verification_type":parseInt(verifytype),
                    "namenode_ip":nodeip,
                    "rest_api_port":restapi,
                    "hadoop_user_name":username,
                    "realm_name":realmname,
                    "kdc_server":kdcserver,
                    "manager_server":manageserver,
                    "rest_api_principal":restprincipal,
                    "udp_preference_limit":parseInt(udpprincipal),
                    "ssl_verify_flag":sslflag,
                    "krb5_keytab":krb5,
                }
                nodelist.push(nodeitem);
            }
        }
        return nodelist;
    }
    //取消修改集群
    var cancelEditCluster =  function(){
        LOCATION('./content/hadoop/hadoop_cluster.php','infrastructure');
    }
    
    return{
        init:function(){
            // handleInit(); //初始化界面
            initListeners(); //初始化监听
            initBasicInfo(); //初始化基本信息
            handleValidation(); //初始化表单验证
        
        }
    }
}();

jQuery(document).ready(function(){
	edit_cluster.init();
});