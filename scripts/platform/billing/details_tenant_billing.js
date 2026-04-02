var billingTenantManager = function(){
	var grid;
	var tenant_uuid
	//添加监听事件
	var addListeners = function(){
		tenant_uuid = $("#tenant_uuid").val();
		$('#exportExcel').on('click', function(){
//			var select = grid.getSelectedRows();
			var data = JSON.stringify({list: tenant_uuid});
			window.location.href = CONF.AJAXPATH + '?m=' + CONF.M.BILLING + '&f=exportExcel&p=' + data;
		});
	}
	
	//初始化数据表格
	var initDataTable = function(){
//		getBillingDetails
		var dataTableOpt = {
    			'columnDefs' : [{
	                'orderable': false,
	                'targets': [0,1,2,3,4]
    			}],
    			"order": [
//                    [1, "desc"]
                ],
    	};
		grid = new Datatable();
		var data = {m:CONF.M.BILLING,f:'getTenantBillingDetails',p:{'tenant_uuid':tenant_uuid}};
		grid.setAjaxParam(data);
    	grid.init({src: $("#billing_details_table"), dataTable:dataTableOpt,onDataLoad:initRows});
    	initDisplay();
	}
	var initRows = function(){
		
	}
	//初始化展示
	var initDisplay = function(){
		list = {}
		list.tenant_uuid = $("#tenant_uuid").val();
		data = JSON.stringify(list);
		$.post(CONF.AJAXPATH, {m:CONF.M.BILLING, f:"getMsgOfTenant", p:data}, function(d){
			var Data = JSON.parse(d);
			var billing_type = Data['billing_type'];
			var billing_mode = Data['billing_mode'];
			var start_time = Data['start_time'];
			var end_time = Data['end_time'];
			var use_day = Data['use_day'];
			var use_capacity = Data['use_capacity'];
			var use_num = Data['use_num'];
			var billing_total = Data['billing_total'];
			var companyName = Data['nick_name'];
			//根据类型不同展示不同文字信息
			var billing_type_str = "";
			if(billing_type ==1){
				billing_type_str += LANG.BILLING_PAY_MONTH +"-" ;
				}else if( billing_type ==2){
					billing_type_str += LANG.BILLING_PAY_YEAR +"-";
				}else{
					billing_type_str += LANG.BILLING_SIZE+"-";
				}
			if(billing_mode ==1){
				billing_type_str += LANG.BILLING_SIZE_CAPACITY;
				
			}else{
				billing_type_str += LANG.BILLING_SIZE_NUM;
			}
			$("#companyName").html(companyName);
			$("#update_pay").text(billing_type_str);
			$("#update_start_time").text(start_time);
			$("#update_end_time").text(end_time);
			$("#update_day").text(use_day);
			$("#update_capacity").text(use_capacity);
			$("#update_num").text(use_num);
			$("#update_billing").text(billing_total);
			//字段显示
			if(billing_type ==3){
				if(billing_mode ==1){
					$("#use_type").text(LANG.BILLING_USE_CAPACITY);
				}else{
					$("#use_type").text(LANG.BILLING_USE_NUM);
				}
			}else{
				if(billing_mode ==1){
					$("#use_type").text(LANG.BILLING_PAY_CAPACITY);
				}else{
					$("#use_type").text(LANG.BILLING_PAY_NUM);
				}
			};
			
			})
			
	}
	
	
	return{
		init: function(){
			addListeners();
			initDataTable();
		}
	}; 
}();
jQuery(document).ready(function() {    
	billingTenantManager.init();
});