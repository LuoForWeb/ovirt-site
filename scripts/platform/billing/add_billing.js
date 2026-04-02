var addBillingManager = function(){
	var grid;
	
	//表单验证
	var validateForm = function(){
		//表单验证
		var form2 = $('#form_add_billing');
        var error2 = $('.alert-danger', form2);
        var success2 = $('.alert-success', form2);
        
      

        form2.validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "",  // validate all fields including form hidden input
            rules: {
            	billing_name: {
                    required: true,
                },
                billing_type: {
                    required: true,
//                    min:1,
                },
//                monetary_unit: {
//                	required: true,
//                },
                billing_num_type: {
                    required: true,
                    min:1,
                },
                billing_vm: {
                	number:true,
                	min: 0,
                },
                billing_fs: {
                	number:true,
                	min: 0,
                },
                billing_db: {
                	number:true,
                	min: 0,
                },
                billing_capacity: {
                	number:true,
                	min: 0,
                },
            },
            
            

            invalidHandler: function (event, validator) { //display error alert on form submit              
                success2.hide();
//                error2.show();
//                Metronic.scrollTo(error2, -200);
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
//                error2.hide();
                
            }
            
        });
        
      //确定
		 $("#submitBut").click(function(){
			 
			 //表单检测
        	if (form2.validate().form()) {
        		if($("#monetary_unit").val() == null || $("#monetary_unit").val() == undefined || $("#monetary_unit").val() == ''){
        			UIToastr.showInfo(LANG.BILLING_NEW,LANG.BILLING_MUST_SELEC_UNIT);
      				 return;
        		}
        		//单价必须填写一个检测
   			 if(!($("#billing_vm").val() !="" || $("#billing_fs").val() !="" || $("#billing_db").val() !="" || $("#billing_capacity").val() !="")){
   				 UIToastr.showInfo(LANG.BILLING_NEW,LANG.BILLING_NEW_INFO);
   				 return;
   			 }
   			 //如果勾选了通知，必须填写通知周期
   			 var inform = $("#inform").bootstrapSwitch('state');
   				if(inform){
   					var val = $("#inform_period").val();
   					if(val == "" || val == 0){
   						UIToastr.showInfo(LANG.BILLING_NEW,LANG.BILLING_NEW_INFROM_INFO);
   						 return;
   					}
   				}
        		submitBnt();
            }
        });
		 
		
	}
	
	//添加监听事件
	var initListeners = function(){
		
		//根据选择不同展示不同输入框-策略模式
		$("#billing_num_type").on('change',function(){
			var num_type = $("#billing_num_type").val();
			if(num_type==1){
				$("#display_capacity").css("display","block");
				$("#display_num_type").css("display","none");
				$("#display_1,#display_2,#display_3").css("display","none");
			}else if(num_type==2){
				$("#display_capacity").css("display","none");
				$("#display_num_type").css("display","block");
				$("#num_selected").val("");
				$("#num_selected").selectpicker('refresh');
			}else{
				$("#display_capacity").css("display","none");
				$("#display_num_type").css("display","none");
			}
		});
		//根据选择不同展示不同输入框-数量类型
		$("#num_selected").on('change',function(){
			//原始数组
			var num_all = ["1","2","3"];
			var num_selected = $("#num_selected").val();
			var num_not_selected = delArrayItem(num_all,num_selected)
			if(num_not_selected.length==0){
				for(var i=0;i<num_all.length;i++){
					$("#display_"+num_all[i]).css("display","none");
				}
			}
			if(num_selected){
				for(var i=0;i<num_selected.length;i++){
					$("#display_"+num_selected[i]).css("display","block");
				}
			}
			for(var i=0;i<num_not_selected.length;i++){
				$("#display_"+num_not_selected[i]).css("display","none");
			}
		})
		//根据不同货币单位实时更新单位
		$("#monetary_unit").on('change',function(){
			$(".update_money").text($("#monetary_unit").find("option:selected").attr('data-tokens'))
		});
		//根据策略类型选择不同显示不同时间单位
		$("#billing_type").on('change',function(){
			var billing_type = $("#billing_type").val();
			if(billing_type==1){
				$(".update_time").text(LANG.BILLING_MONTH)
			}else if(billing_type==2){
				$(".update_time").text(LANG.BILLING_YEAR)
			}else{
				$(".update_time").text(LANG.BILLING_DAY)
			}
			
		});
		
		//取消
		$("#cancelBut").on('click',function(){
			window.history.go(-1);
		})
		//是否通知点击事件
		$("#inform").on('switchChange.bootstrapSwitch',function(event,state){
			if(state){
				$("#display_inform").css("display","block");
			}else{
				$("#display_inform").css("display","none");
			}
		})
	}
	
	//获取填写数据并存入后台
	var submitBnt = function(){
		var list = {};
		list.billing_name = $("#billing_name").val();
		list.billing_description = $("#billing_description").val();
		list.billing_type = $("#billing_type").val();
		list.monetary_unit = $("#monetary_unit").val();
		list.billing_num_type = $("#billing_num_type").val();
		list.num_selected = $("#num_selected").val();
		list.billing_vm = $("#billing_vm").val();
		list.billing_fs = $("#billing_fs").val();
		list.billing_db = $("#billing_db").val();
		list.billing_capacity = $("#billing_capacity").val();
		var inform = $("#inform").bootstrapSwitch('state');
		if(inform){
			list.inform = 1;
		}else{
			list.inform = 2;
		}
		list.inform_period = $("#inform_period").val();
		data = JSON.stringify(list);
		$.post(CONF.AJAXPATH, {m:CONF.M.BILLING, f:"addBilling", p:data}, function(d){
			if(OPREL(d)){
				LOCATION("./content/platform/billing/billing_manager.php",'billing_manager');
    		}
			})
	}
	
	//获取一个费用策略名字
	var initBillingName = function(){
		//getNumOfName
		$.post(CONF.AJAXPATH, {m:CONF.M.BILLING, f:"getNumOfName", p:{}}, function(d){
			var data = JSON.parse(d);
			$("#billing_name").val(LANG.BILLING_STRATEGY+data);
			})
	}
	
	//初始化时间插件
	// var inintDatatimePicker = function(){
	// 	$(".form_datetime").datetimepicker({
	// 		language:  'zh-CN', 
    //         autoclose: true,
    //         isRTL: Metronic.isRTL(),
    //         format: "yyyy-MM-dd hh:ii:ss",
    //         pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left")
    //     });
	// }
	var inintDatatimePicker = function(){
		if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
		 //英文独有的
	  $(".form_datetime").datetimepicker({
	   autoclose: true,
	   isRTL: Metronic.isRTL(),
	   format: "yyyy-mm-dd hh:ii:ss",
	   pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
	  });
		}else{
		 $(".form_datetime").datetimepicker({
		  language:  'zh-CN', 
		  autoclose: true,
		  isRTL: Metronic.isRTL(),
		  format: "yyyy-MM-dd hh:ii:ss",
		  pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
		 });
		}
	}
   
	//清除时间
	$('#resetStartTime').on('click',function(){
		$('#startTime').val('');
	});
	
	//删除数组内不需要的元素
	//传入参数,第一个是初始array,第二个是需要删除的array
	var delArrayItem = function(array,array_del){
		if(array_del==null){
			return [];
		}
		for(var i=0;i<array_del.length;i++){
			array = array.filter(function(item) {	 
				return item != array_del[i]
			 });	
			}
		return array
		
	}
	//初始化货币单位select的option
	var initSelectOption = function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.BILLING, f:"pGetOptionOfUnit", p:{}}, function(d){
			var data = JSON.parse(d);
			document.getElementById("monetary_unit").innerHTML = data;
			initSelect();
		})
	}
	
	
	//初始化数据表格
	var initDataTable = function(){
		
	}
	var initSelect = function(){
		//初始化多选下拉框
		$(".selectpicker").selectpicker({
			noneSelectedText: LANG.BILLING_PLEASE_SELECT,
			liveSearch:true,
			deselectAllText:LANG.BILLING_DESELECT_ALL,
			selectAllText:LANG.BILLING_SELECT_ALL,
			liveSearchPlaceholder:LANG.BILLING_SEARCH,
			countSelectedText: function(){}
		});
    }
	
	
	return{
		init: function(){
			initSelectOption();
			initListeners();
			initDataTable();
//			initSelect();
			inintDatatimePicker();
			validateForm();//表单验证
			initBillingName();
		}
	}; 
}();
jQuery(document).ready(function() {    
	addBillingManager.init();
});