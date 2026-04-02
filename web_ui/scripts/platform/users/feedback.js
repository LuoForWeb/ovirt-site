var Feedback = function () {
	
	
	var initChecker = function(){
		
    	$('#yesback').iCheck('check');
    	//定义iCheck样式
    	$('input').iCheck({
    	    checkboxClass: 'icheckbox_square-blue',
    	    radioClass: 'iradio_square-blue',
    	    increaseArea: '20%' // optional
    	});
	}
	
	var addListeners = function(){
		$('#feedbackcancel').on('click', function(){
			CTLSIDEBAR('survey');
			//判断是默认账户还是租户内部账户
			var url = "./content/platform/databackup_center.php";
			if(CONF.TENANTUUID != ""){
				url = "./content/platform/tenant_center.php";
			}
        	LOCATION(url);
		});
		$('#feedbacksubmit').on('click', function(){
			var msg = $.trim($('textarea[name=msg]').val());
			if("" != msg){
				submit(msg);
			}else{
				UIToastr.showInfo(LANG.UI_PLATFORM_USR_INPUT_FEEDBACK, LANG.UI_PLATFORM_USR_INPUT_FEEDBACK_TIPS);
			}
		});
	}
	
	var submit = function(){
		var data = {};
		data.info = $('textarea[name=msg]').val();
		data.phone = $('input[name=phone]').val();
		data.email = $('input[name=email]').val();
		data = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.USER,f:'feedback',p:data}, function(data){
    		if(OPREL(data)){
    			
    		}
    	});
	}
	

    return {
        //main function to initiate the module
        init: function () {
            initChecker();
            addListeners();
        }

    };

}();

jQuery(document).ready(function() {    
	Feedback.init();
});