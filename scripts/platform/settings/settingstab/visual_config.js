//初始化可视化配置
var Settings_Visual_Config = function(){
	
	var addListeners = function(){
		
		var visualform = $('#visualform');
		visualform.validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "",  // validate all fields including form hidden input
            rules: {
            	pushTitle: {
                    required: true,
                    title: false,
                },
            },

            invalidHandler: function (event, validator) { //display error alert on form submit              
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
                
            }
            
        });
        
        //IP域名验证格式
        $.validator.addMethod("title", function(value, element) {
        	var title = this.optional( element ) || /^(([^\^\.<>',;"':\]\[{}\\/`\|]))$/i.test( value );
        	return title;
        }, LANG.UI_VISUAL_SETTING_TITLE);
        	
    	$("#visualsubmit").click(function(){
			if (visualform.validate().form()) {
				submit();
			}
        });
		$("#visualcancel").click(function(){
        	LOCATION('./content/platform/settings/setting_manager.php?tab=7', 'setting_manager');
        });
        
	}
	
	var submit = function(){
		Metronic.blockUI({target: '#visualform',animate: true});
		var data = {};
		data.title = $('#visualTitle').val();
		data.taskAlertCheck = $('#taskAlertCheck').bootstrapSwitch('state');
		data.systemAlertCheck = $('#systemAlertCheck').bootstrapSwitch('state');
		pAjaxRequest(data,'/api/v1/system/visual_setting','PUT',function (d){
			Metronic.unblockUI('#visualform');
			operateResponseList(d);
		})
	}
	
	var initOldInfo = function(){
		pAjaxRequest({},'/api/v1/system/visual_setting','GET',function (d){
			var data  = d.data;
			if(data.length == 0) return;
			//可视化配置初始化
			$('#visualTitle').val(data.config.title);
			$('#taskAlertCheck').bootstrapSwitch('state', data.config.taskAlertCheck);
			$('#systemAlertCheck').bootstrapSwitch('state', data.config.systemAlertCheck);
		})
	}
	
	 return {
	        init: function () {
	        	//添加事件
	        	addListeners();
	        	initOldInfo();
	        }
	    };
}();

jQuery(document).ready(function(){
	Settings_Visual_Config.init();
});