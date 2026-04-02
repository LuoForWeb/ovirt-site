var StrategyEach = function () {
	var isInit = false;
	var initDatePicker = function(){
		if (jQuery().timepicker) {
            $('.starttime').timepicker({
                autoclose: true,
                minuteStep: 5,
                showSeconds: true,
                showMeridian: false,
//                defaultTime:'00:00:00'
            });
            $('.rolltime').timepicker({
                autoclose: true,
                minuteStep: 5,
                showSeconds: true,
                showMeridian: false,
                defaultTime:'01:00:00'
            });
            $('.endtime').timepicker({
                autoclose: true,
                minuteStep: 5,
                showSeconds: true,
                showMeridian: false,
                defaultTime:'23:59:59'
            });

            // handle input group button click
            $('.timepicker').parent('.input-group').on('click', '.input-group-btn', function(e){
                e.preventDefault();
                $(this).parent('.input-group').find('.timepicker').timepicker('showWidget');
            });
        }
	}
	
	var initSwitchHandler = function(){
		$(".rollflag").on("switchChange.bootstrapSwitch",function(e, data){
			var checked = this.checked;
			var rollInterval = $(this).closest(".form-group").next();
			var endTime = $(rollInterval).next();
			if(checked){
				//ON
				rollInterval.show();
				endTime.show();
			}else{
				//OFF
				rollInterval.hide();
				endTime.hide();
			}
		});
	}
	
	var initICheckHandler = function(){
//		$('.icheck').iCheck();
	}

	var initEachButtonHandler = function(){
		$('.each-button').on("click",eachButtonClick);
	}
	
	var setFlag = function(flag){
		isInit = flag;
	}
	
	var getFlag = function(){
		return isInit;
	}
    return {
        //main function to initiate the module
        init: function () {
        	if(getFlag()){
        		//init only once
        		return true;
        	}
        	initDatePicker();
        	initSwitchHandler();
//        	initICheckHandler();
        	setFlag(true);
        },
        
        //如果第一次没有初始成功还可以再初始一次,解决恢复的时候无法显示的BUG
        initDatePickerTwo : function(){
        	initDatePicker();
        }
       
    };
}();
jQuery(document).ready(function() {   
	StrategyEach.init();
});