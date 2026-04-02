var addResourceGroup = function(){
	
	var initListeners = function(){
	
	}
	
	//添加资源组表单验证
	// validation using icons
    var handleValidation = function() {
        // for more info visit the official plugin documentation: 
            // http://docs.jquery.com/Plugins/Validation

        var form2 = $('#form_addResourcegroup');
        var error2 = $('.alert-danger', form2);
        var success2 = $('.alert-success', form2);
        
        form2.validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "",  // validate all fields including form hidden input
            rules: {
            	resourcegroupname: {
                    required: true,
                    nameAvailable: true,
                },
                description: {
                	required: false
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
        
   	 
   	 	//提交添加角色表单
        $("#submitBut").click(function(){
        	if (form2.validate().form()) {
        		submit();
            }
        });
        
        //取消返回资源组列表
        $("#cancelBut").click(function(){
        	LOCATION('./content/platform/resource/resource_group.php','resource_group');
        });
    }
    
    $.validator.addMethod(
        	"nameAvailable",
        	function(value, element, param) {
        		var data = JSON.stringify({name:value});
        		var result = false;
        		$.ajax({ 
        			type: "post", 
        	        url: CONF.AJAXPATH, 
        	        async:false, 
        	        data:{m:CONF.M.RESOURCE,f:'resourceGroupAvailable',p:data},
        	        success: function(data){ 
        	        	result = JSON.parse(data);
        	        } 
        		});
    	    	return result;
    	    },
        	LANG.UI_RESOURCE_GROUP_NAME_EXIST
        );
    
    //提交添加资源组表单
    var submit = function(){
    	var data = {};
    	data.resourcegroupname = $('#resourcegroupname').val();
    	if(data.resourcegroupname == ""){
			return UIToastr.showWarning(LANG.UI_RESOURCE_GROUP_ADD, LANG.UI_RESOURCE_GROUP_NAME_MUST);
		}
    	data.description = $('#description').val();
    	var p = JSON.stringify(data);
    	$.post(CONF.AJAXPATH,{m: CONF.M.RESOURCE, f:"addResourceGroup", p: p}, function(d){
    		if(OPREL(d)){
    			LOCATION('./content/platform/resource/resource_group.php','resource_group');
    		}
    	});
    }
	
	return {
		init: function(){
			handleValidation();
			initListeners();
		}
	}
}();

jQuery(document).ready(function(){
	addResourceGroup.init();
})