var NodeEdit = function () {
	
	var initSpinner = function(){
        $('#spinnerNum').spinner({value:22, step: 1, min: 0, max: 65535});
	}
	
	// validation using icons
    var handleValidation = function() {
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
                    port:{
                    	required: true,
                    	port: true,
                    },
                    username: {
                    	required: true,
                    },
                    password: {
                        required: true,
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
            
            $("#addsubmit").click(function(){
            	if (form2.validate().form()) {
//            		success2.show();
                    error2.hide();
                    submit();
                }
            });
    };
    
    $('input[name=ipaddr]').on('change',function(){
    	$('input[name=rname]').val(this.value);
    });
    
    $('#cancelBut').on('click', function(){
    	href2NodeManager();
    });
    
    var href2NodeManager = function(){
    	LOCATION('./content/platform/node/node_manager.php', 'node_manager');
    }
    
    var submit = function(){
    	Metronic.blockUI({target: '#addcontent',animate: true});
    	var data = {};
    	data.nodeuuid = $('#uuid').val();
    	data.ip = $('input[name=ipaddr]').val();
    	data.port = $('input[name=port]').val();
    	data.username = $("input[name=username]").val();
    	data.password = btoa($("input[name=password]").val());
    	data.rname = $('input[name=rname]').val();
    	data = JSON.stringify(data);
    	$.post(CONF.AJAXPATH, {m:CONF.M.NODE,f:'editNode',p:data}, registerResult);
    };
    
    var registerResult = function(data){
    	Metronic.unblockUI('#addcontent');
    	if(OPREL(data)){
    		href2NodeManager();
    	}
    	return;
    };

	$.validator.addMethod("ipv4Ordomain", function(value, element) {
		var domain = this.optional( element ) || /^(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test( value );
		var ipv4 = this.optional(element) || ipV4V6(value) || /^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$/i.test(value);
		return domain || ipv4;
    }, LANG.UI_TOOLS_IP_OR_DOMAIN);
		
	$.validator.addMethod("port", function(value, element) {
		if(value >= 0 && value <= 65535){
			return true;
		}
		return false;
    }, LANG.UI_TOOLS_IP_OR_DOMAIN);
	
	//初始化历史信息
	var initNodeInfo = function(){
		var nodeuuid = $('#uuid').val();
    	var data = JSON.stringify({nodeuuid:nodeuuid});
        $.post(CONF.AJAXPATH, {m:CONF.M.NODE,f:'getEditNodeInfo',p:data}, function(d){
        	var info = JSON.parse(d);
        	$('input[name=ipaddr]').val(info.ip);
        	$('input[name=port]').val(info.port);
        	$('input[name=username]').val(info.username);
        	$('input[name=password]').val(info.password);
        	$('input[name=rname]').val(info.rname);
        });
	}
	
    return {
        //main function to initiate the module
        init: function () {
        	initNodeInfo();
        	initSpinner();
        	handleValidation();
        }

    };

}();

jQuery(document).ready(function() {    
	NodeEdit.init();
});