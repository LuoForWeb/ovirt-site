var billingManager = function(){
	var grid;
	//添加监听事件
	var addListeners = function(){
		//新建
		$('#newBut').on('click',function(){
			LOCATION('./content/platform/billing/add_billing.php','billing_manager');
		});
		//修改
		$('#modifyBut').on('click',function(){
			funcEditBilling();
		});
		//启用
		$('#unlockBut').on('click',function(){
			var select = grid.getSelectedRows();
			if(select.length == 0){
				UIToastr.showInfo(LANG.BILLING_ON_LOCK,LANG.BILLING_ON_LOCK_INFO);
			}else{
				bootbox.confirm({
		            title: LANG.BILLING_ON_LOCK,
		            message: LANG.BILLING_ON_LOCK_HITE,
		            callback: function(r) {
		                if(!r) return;
		                funcUnlockBut();
		            }
		        });
			}
		});
		//禁用
		$('#lockBut').on('click',function(){
			var select = grid.getSelectedRows();
			if(select.length == 0){
				UIToastr.showInfo(LANG.BILLING_OFF_LOCK,LANG.BILLING_OFF_LOCK_INFO);
			}else{
				bootbox.confirm({
		            title: LANG.BILLING_OFF_LOCK,
		            message: LANG.BILLING_OFF_LOCK_HITE,
		            callback: function(r) {
		                if(!r) return;
		                funcLockBut();
		            }
		        });
			}
			
		});
		//删除
		$('#deleteBut').on('click',function(){
			var select = grid.getSelectedRows();
			if(select.length == 0){
				UIToastr.showInfo(LANG.BILLING_DELETE,LANG.BILLING_DELETE_INFO);
			}else{
				bootbox.confirm({
		            title: LANG.BILLING_DELETE,
		            message: LANG.BILLING_DELETE_HITE,
		            callback: function(r) {
		                if(!r) return;
		                funcDeleteBilling();
		            }
		        });
			}
		});
	}
	
	
	//启用func
	var funcUnlockBut = function(){
		var select = grid.getSelectedRows();
		var data = JSON.stringify({'uuid_list':select});
		$.post(CONF.AJAXPATH, {m:CONF.M.BILLING, f:"onBilling", p:data}, function(d){
			if(OPREL(d)){
				grid.getRefresh({});
    		}
			})
		
	}
	//禁用func
	var funcLockBut = function(){
		var select = grid.getSelectedRows();
		var data = JSON.stringify({'uuid_list':select});
		$.post(CONF.AJAXPATH, {m:CONF.M.BILLING, f:"offBilling", p:data}, function(d){
			if(OPREL(d)){
				grid.getRefresh({});
    		}
			})
		
	}
	//删除func
	var funcDeleteBilling = function(){
		var select = grid.getSelectedRows();
		var data = JSON.stringify({'uuid_list':select});
		$.post(CONF.AJAXPATH, {m:CONF.M.BILLING, f:"deleteBinlling", p:data}, function(d){
			if(OPREL(d)){
				grid.getRefresh({});
    		}
			})
	}
	
	
	
	//修改func
	var funcEditBilling = function(){
		var select = grid.getSelectedRows();
		if(select.length==0){
			UIToastr.showInfo(LANG.BILLING_MODIFY,LANG.BILLING_MODIFY_INFO_1);
		}else if(select.length>1){
			UIToastr.showInfo(LANG.BILLING_MODIFY,LANG.BILLING_MODIFY_INFO_2);
		}else{
			bootbox.confirm({
	            title: LANG.BILLING_MODIFY,
	            message: LANG.BILLING_MODIFY_HITE,
	            callback: function(r) {
	                if(!r) return;
	                var select = grid.getSelectedRows();
	    			LOCATION('./content/platform/billing/edit_billing.php?billing_uuid='+select[0],'billing_manager');
	            }
	        });
		}
	}
	
	
	//初始化数据表格
	var initDataTable = function(){
		var dataTableOpt = {
    			'columnDefs' : [{
	                'orderable': false,
	                'targets': [0,3,6,8]
    			}],
    			"order": [
                    [5, "desc"]
                ],
    	};
		grid = new Datatable();
		var data = {m:CONF.M.BILLING,f:'getBillingList',p:{}};
		grid.setAjaxParam(data);
    	grid.init({src: $("#billing_table"), dataTable:dataTableOpt,onDataLoad:initRows});
	}
	//重新渲染列表
	var initRows = function(){
		var all_list = grid.getDataTable().data();
		var name = $('#billing_table tbody > tr').find('td:eq(1)');
		for(var i=0; i<name.length;i++){
			setDes(name[i], all_list[i]);
		}
		//设置单价栏自动换行
		var name = $('#billing_table tbody > tr').find('td:eq(6)').css('word-break','break-all');
	}
	
	var setDes = function(div, data){
		var url = './content/platform/billing/details_billing.php?billing_uuid='+data[10]; 
		var des = '<a href="'+url+'" class="ajaxify" name="billing_manager" >'+data[1]+'</a>'
		$(div).html(des)
	}
	
	
	
	return{
		init: function(){
			addListeners();
			initDataTable();
		}
	}; 
}();
jQuery(document).ready(function() {    
	billingManager.init();
});