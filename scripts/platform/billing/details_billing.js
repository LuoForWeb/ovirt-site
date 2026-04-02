var detailsBillingManager = function(){
	var grid;
	var billing_type;
	var billing_vm;//虚拟机单价
	var billing_fs;
	var billing_db;
	var billing_capacity;
	var monetary_unit;//货币单位
	var billing_mode;
	var MonetaryStr;
	
	//表单验证
	var validateForm = function(){
		//表单验证
		var form2 = $('#form_add_tenant');
        var error2 = $('.alert-danger', form2);
        var success2 = $('.alert-success', form2);

        form2.validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "",  // validate all fields including form hidden input
            rules: {
            	tenant_list: {
                    required: true,
                },
                num_capacity: {
                	number:true,
                	min: 0,
                },
                num_vm: {
                	number:true,
                	min: 0,
                },
                num_fs: {
                	number:true,
                	min: 0,
                },
                num_db: {
                	number:true,
                	min: 0,
                },
                billing_time: {
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
            }
        });
        
      //确定
		 $("#submit_allocation").click(function(){
			 var billing_time = $("#billing_time").val();
			 if((billing_type ==1 || billing_type ==2) && billing_time == ""){
				 UIToastr.showInfo(LANG.BILLING_EDIT_TIME,LANG.BILLING_EDIT_TIME_INFO);
				 return;
			 }
			 //表单检测
        	if (form2.validate().form()) {
   				sub_data();
            }
        });
		
	}
	
	//添加监听事件;
	var addListeners = function(){
		//分配租户到策略
		$("#allocation_tenant").on('click',function(){
			initTenantList();//初始化租户信息
			$("#allocation_modal").modal({"width": "700px","height":"400px"});
			$("#allocation_modal").modal("show");
		});
		
		$("#calculate").on('click',function(){
			count_total();
		})
		//续费取消按钮
		$("#concel_update_billing").on('click',function(){
			$("#update_time_input").val("");
		});
		//费用实时更新
		$("#num_vm").bind('input propertychange',function(){
			count_total();
		});
		$("#billing_time").bind('input propertychange',function(){
			count_total();
		});
		$("#num_db").bind('input propertychange',function(){
			count_total();
		});
		$("#num_fs").bind('input propertychange',function(){
			count_total();
		});
		$("#num_capacity").bind('input propertychange',function(){
			count_total();
		});
		//取消分配
		$("#consel_submit_allocation").on('click',function(){
			$("#num_capacity").val("");
			$("#num_vm").val("");
			$("#num_fs").val("");
			$("#num_db").val("");
			$("#billing_time").val("");
			$("#string_text").text("");
    		$("#total_billing").text("");
		})
	}
	
	//初始化数据表格
	var initDataTable = function(){
		var dataTableOpt = {
    			'columnDefs' : [{
	                'orderable': false,
	                'targets': [0,1,2,3,4,5,6]
    			}],
    			"order": [
//                    [1, "desc"]
                ],
    	};
		grid = new Datatable();
		var billing_uuid = $("#billing_uuid").val();
		var data = {m:CONF.M.BILLING,f:'getBillingDetails',p:{billing_uuid:billing_uuid}};
		grid.setAjaxParam(data);
    	grid.init({src: $("#billing_details_table"), dataTable:dataTableOpt,onDataLoad:initRows});
	}
	//加载表格前初始化操作按钮
	var initRows = function(){
		var all_list = grid.getDataTable().data();
		var name = $('#billing_details_table tbody > tr').find('td:eq(6)');
		for(var i=0; i<name.length;i++){
			setDes(name[i], all_list[i]);
		}
		
	};
	
	//添加按钮样式
	var setDes = function(div, data){
		var li ="";
		if(billing_type==1 || billing_type==2){
			li = '<li class="update_billig"><a href="javascript:;"><i class="fa fa-shopping-bag"></i> ' + LANG.BILLING_RENEW + '</a></li>';
			li +='<li class="details_tenant"><a href="javascript:;"><i class="fa fa-list"></i> ' + LANG.BILLING_DETAILS + '</a></li>';
			li +='<li class="delete_tenant"><a href="javascript:;"><i class="viconfont vicon-ge_delete"></i> ' + LANG.BILLING_DELETE + '</a></li>';
		}else{
			li ='<li class="details_tenant"><a href="javascript:;"><i class="fa fa-list"></i> ' + LANG.BILLING_DETAILS + '</a></li>';
			li +='<li class="delete_tenant"><a href="javascript:;"><i class="viconfont vicon-ge_delete"></i> ' + LANG.BILLING_DELETE + '</a></li>';
		}
		
		var des = '<div class="btn-group positionabs"><button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown" ' + 
		'data-hover="dropdown" data-delay="1000" data-close-others="true">' + 
		'<i class="glyphicon glyphicon-hand-up"></i> ' + LANG.BILLING_HANDLE + ' <i class="fa fa-angle-down"></i>' + 
		'</button>' + 
		'<ul class="dropdown-menu min-width100" role="menu">' + li + '</ul></div>'
		$(div).html(des)
		initAddButtonListener();
	}
	//操作按钮时间
	var initAddButtonListener = function(){
		$('.update_billig').unbind().on('click', updateBilling);
		$('.details_tenant').unbind().on('click', detailsTenant);
		$('.delete_tenant').unbind().on('click', deleteTenant);
	}
	//操作-续费
	var updateBilling = function(){
		var data = grid.getDataTable().data();
		var row = $(this).parents('tr').get(0)._DT_RowIndex;
		var tenant_uuid = data[row][7].tenant_uuid;
		var start_time = data[row][3];
		var end_time = data[row][4];
		
		var use_num = data[row][7].use_num;
		var capacity_size = data[row][7].capacity_size;
		var start_total = data[row][7].start_total;
		
		
		$("#start_time_input").val(start_time);
		$("#end_time_input").val(end_time);
		$("#update_new_time_input").val("");
		$("#update_new_total_input").val("");
		
		
		$("#update_billing_modal").modal("show");
		//实时输入时间数据计算时间
		$("#update_time_input").bind('input propertychange',function(){
			var update_time_val = parseInt($("#update_time_input").val());
			var end_total = add_billing_total(use_num,capacity_size,start_total,update_time_val)
			$("#update_new_total_input").val(end_total);
			var params_time = {};
			params_time.time_unit = billing_type;
			params_time.value = update_time_val;
			params_time.end_time = end_time;
			params_time = JSON.stringify(params_time);
			$.post(CONF.AJAXPATH, {m:CONF.M.BILLING,f:'pGetNewTime',p:params_time}, function(data){
				var time = JSON.parse(data);
				$("#update_new_time_input").val(time);
				
			})
		});
		
		//确定修改续费操作按钮
		$("#submit_update_billing").unbind().on('click',function(){
			var update_time = $("#update_time_input").val()
			if(update_time == "" || update_time == null || update_time == undefined){
				UIToastr.showInfo(LANG.BILLING_RENEW,LANG.BILLING_RENEW_HITE);
				return;
			}
			var params = {};
			params.tenant_uuid = tenant_uuid;
			params.new_time = $("#update_new_time_input").val();
			params.new_total = $("#update_new_total_input").val();
			params = JSON.stringify(params);
			$.post(CONF.AJAXPATH, {m:CONF.M.BILLING,f:'updateBillingDetailsTime',p:params}, function(data){
				if(OPREL(data)){
					$("#update_billing_modal").modal("hide");
					$("#update_time_input").val("");//清空续费时间
					grid.getRefresh({});
	    		}
			})
			$("#update_time_input").val("");//清空续费时间
		});
	}
	
	//操作-详细信息
	var detailsTenant = function(){
		var data = grid.getDataTable().data();
		var row = $(this).parents('tr').get(0)._DT_RowIndex;
		var tenant_uuid = data[row][7].tenant_uuid;
		var url = "./content/platform/billing/details_tenant_billing.php?tenant_uuid="+tenant_uuid+"&billing_uuid="+$("#billing_uuid").val();
		LOCATION(url);	
	}
	//操作-删除
	var deleteTenant = function(){
		var data = grid.getDataTable().data();
		var row = $(this).parents('tr').get(0)._DT_RowIndex;
		var tenant_uuid_one = data[row][7].tenant_uuid;
		var params = {};
		params.tenant_uuid = tenant_uuid_one;
		params = JSON.stringify(params);
		bootbox.confirm({
            title: LANG.BILLING_DELETE,
            message: LANG.BILLING_DELETE_TENANT_INFO,
            callback: function(r) {
                if(!r) return;
                deleteAgain(params);
            }
        });
	}
	
	var deleteAgain = function(params){
		$.post(CONF.AJAXPATH, {m:CONF.M.BILLING,f:'deleteBillingOfTenant',p:params}, function(data){
			if(OPREL(data)){
				grid.getRefresh({});
			}
		})
		
	}
	
	
	
	var initSelect = function(){
		//初始化多选下拉框
		$(".selectpicker").selectpicker({
			noneSelectedText: LANG.BILLING_PLEASE_SELECT,
			deselectAllText:LANG.BILLING_DESELECT_ALL,
			selectAllText:LANG.BILLING_SELECT_ALL,
			liveSearchPlaceholder:LANG.BILLING_SEARCH,
			countSelectedText: function(){}
		});
    }
	 //初始化租户选项
    var initTenantList = function(){
    	$.post(CONF.AJAXPATH, {m:CONF.M.BILLING,f:'getTenantList',p:{}}, function(d){
    		var data = JSON.parse(d);
    		var tenant = $("#tenant_list");
    		tenant.empty();
			for(var i=0;i<data.length;i++){
				var option = $("<option>").text(data[i].tenant_name).val(data[i].tenant_uuid);
				tenant.append(option);
				tenant.selectpicker('refresh')
			}
    		
		});
    }
    //获取策略信息,根据策略不同,展示不同的信息
    var initDisplay = function(){
    	//获取到需要修改的billing_uuid
		var billing_uuid = $("#billing_uuid").val();
		var data = JSON.stringify({'billing_uuid':billing_uuid});
    	$.post(CONF.AJAXPATH, {m:CONF.M.BILLING,f:'getEditBillingList',p:data}, function(d){
    		var Data = JSON.parse(d);
    		billing_type = Data['billing_type'];
    		billing_mode = Data['billing_mode'];
    		MonetaryStr = Data['MonetaryStr'];
    		var num_type = JSON.parse(Data['num_type']);
    		var billing_name = Data['name'];
    		//得到单价 json
    		var unit_cost = JSON.parse(Data['unit_cost']);
			billing_vm =unit_cost['billing_vm'];
			billing_fs = unit_cost['billing_fs'];
			billing_db = unit_cost['billing_db'];
			billing_capacity = unit_cost['billing_capacity'];
			monetary_unit = Data['monetary_unit'];
    		//title显示
    		$("#billing_name").text(billing_name)
    		
    		//列表数量-容量字段改变显示
    		if(billing_mode ==1){
    			$("#use_type").text(LANG.BILLING_USE_CAPACITY);
    		}else{
    			$("#use_type").text(LANG.BILLING_USE_NUM);
    		}
    		//初始化表格
    		initDataTable();
    		
    		if(billing_type == 3){
    			return;
    		}else if(billing_type ==1 ||billing_type ==2){
    			$("#display_total").css("display","block");
    			if(billing_type==1){
    				$("#time_year").text(LANG.BILLING_MONTH)
    				$("#time_update_unit").text(LANG.BILLING_MONTH)
    				
    			}
    			if(billing_type==2){
    				$("#time_year").text(LANG.BILLING_YEAR)
    				$("#time_update_unit").text(LANG.BILLING_YEAR)
    			}
    			//展示时间
    			$("#display_time").css("display","block");
    			if(billing_mode==1){
    				$("#display_capacity").css("display","block");
    			}else{
    				for(var i=0;i<num_type.length;i++){
    					//虚拟机
    					if(num_type[i]==1){
    						$("#display_vm").css("display","block");
    					}
    					//文件
    					if(num_type[i]==2){
    						$("#display_fs").css("display","block");
    					}
    					//数据库
    					if(num_type[i]==3){
    						$("#display_db").css("display","block");
    					}
    				}
    			}
    		}
    		
    	})
    	
    };
///-----------------------	
    //分配租户-获取勾选租户列表和填写数据并提交后台
    var sub_data = function(){
    	var list = {}; 
    	list.tenant_uuid_list = $("#tenant_list").val();
    	list.billing_uuid = $("#billing_uuid").val();
    	list.num_capacity = $("#num_capacity").val();
    	list.num_vm = $("#num_vm").val();
    	list.num_fs = $("#num_fs").val();
    	list.num_db = $("#num_db").val();
    	if(list.num_vm == ""){
    		list.num_vm =0;
    	}
    	if(list.num_fs == ""){
    		list.num_fs =0;
    	}
    	if(list.num_db == ""){
    		list.num_db =0;
    	}
    	list.billing_time = $("#billing_time").val();
    	list.billing_type_get = billing_type;
    	list.billing_total = count_total();
    	data = JSON.stringify(list);
    	$.post(CONF.AJAXPATH, {m:CONF.M.BILLING, f:"createBillingOrder", p:data}, function(d){
    		if(OPREL(d)){
    			$("#allocation_modal").modal("hide");
    			grid.getRefresh({});
    		}
    	})
    }
    
    
    
   
    
    //计算总价--只针对包月包年计算总价
    var count_total = function(){
    	var num_capacity = $("#num_capacity").val();
    	var num_vm = $("#num_vm").val();
    	var num_fs = $("#num_fs").val();
    	var num_db = $("#num_db").val();
		var billing_time = $("#billing_time").val();
    	var time_unit = $("#time_year").text();
    	var total_billing ="";
		num_vm = "" ? num_vm=0 : num_vm;
        num_fs = "" ? num_fs=0 : num_fs;
        num_db = "" ? num_db=0 : num_db;
        billing_time = "" ? billing_time=0 : billing_time;
        billing_capacity = "" ? billing_capacity=0 : billing_capacity;
        num_capacity = "" ? num_capacity=0 : num_capacity;
    	if(!isNaN($("#billing_time").val()) && !isNaN(num_vm) && !isNaN(num_fs) && !isNaN(num_db)) {
			//包月包年--按容量
			if(billing_mode ==1){
				var total_unit = keepTwoDecimalFull(billing_capacity*num_capacity);
				var string_text = total_unit+MonetaryStr+"/"+time_unit;
				total_billing = keepTwoDecimalFull(total_unit*billing_time)
				var total_billing_text = total_billing+MonetaryStr;
				$("#string_text").text(string_text);
				$("#total_billing").text(total_billing_text);
			}
			if(billing_mode ==2){
				var total_unit = keepTwoDecimalFull(num_vm*billing_vm+num_fs*billing_fs+num_db*billing_db);
				var string_text = total_unit+MonetaryStr+"/"+time_unit;
				total_billing = keepTwoDecimalFull(total_unit*billing_time)
				var total_billing_text = keepTwoDecimalFull(total_unit*billing_time)+MonetaryStr;
				
				$("#string_text").text(string_text);
				$("#total_billing").text(total_billing_text);
			}
			return total_billing;
		}else {
			$("#total_billing").text('');
			$("#string_text").text('');
			return;
	    }
	}
    
    //计算总价-用于续费用
    //购买数量,容量,开始时候的总价,结束的总价
    var add_billing_total = function(use_num,capacity_size,start_total,time){
    	var num_capacity = capacity_size;
    	var num_vm = use_num.num_vm;
    	var num_fs = use_num.num_fs;
    	var num_db = use_num.num_db;
		var billing_time = time
    	var total_billing ="";
    	//包月包年--按容量
    	if(billing_mode ==1){
    		var total_unit = keepTwoDecimalFull(billing_capacity*num_capacity);
    		total_billing = keepTwoDecimalFull(total_unit*billing_time)
    	}
    	if(billing_mode ==2){
    		var total_unit = keepTwoDecimalFull(num_vm*billing_vm+num_fs*billing_fs+num_db*billing_db);
    		total_billing = keepTwoDecimalFull(total_unit*billing_time)
    	}
    	total_billing = parseFloat(start_total)+parseFloat(total_billing);
    	return keepTwoDecimalFull(total_billing);
    }
    
    
  //保留2为小数，不够用0填充
    var keepTwoDecimalFull = function(num){
    	var result = parseFloat(num);
//        if (isNaN(result)) {
//            UIToastr.showInfo("传递参数错误","请检查");
//            return false;
//        }
        result = Math.round(num * 100) / 100;
        var s_x = result.toString(); //将数字转换为字符串

        var pos_decimal = s_x.indexOf('.'); //小数点的索引值


        // 当整数时，pos_decimal=-1 自动补0
        if (pos_decimal < 0) {
            pos_decimal = s_x.length;
            s_x += '.';
        }

        // 当数字的长度< 小数点索引+2时，补0
        while (s_x.length <= pos_decimal + 2) {
            s_x += '0';
        }
        return s_x;
    }
    
    
    
	return{
		init: function(){
			initSelect();
			addListeners();
//			initDataTable();
			initDisplay();
			validateForm();
		}
	}; 
}();
jQuery(document).ready(function() {    
	detailsBillingManager.init();
});