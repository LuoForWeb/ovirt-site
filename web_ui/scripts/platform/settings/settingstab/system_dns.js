//DNS解析
var Settings_DNS = function(){
	var initBackupNodeList = function(){
		pAjaxRequest({}, '/api/v1/nodes/select', 'GET', function (d) {
			var data = d.data;
			var nodeselect = $('select[name=dnsnodelist]');
			nodeselect.empty();
			for(var i=0; i<data.length; i++){
				var option = $("<option>").text(data[i].text).val(data[i].uuid);
				nodeselect.append(option);
			}
			
			initDnsList($('select[name=dnsnodelist]').val());
    	});
	}
	
	var initDnsList = function(nodeuuid){
		var data = {'node_uuid':nodeuuid};
		Metronic.blockUI({target: '#dnsform',animate: true});
		pAjaxRequest(data, '/api/v1/system/network/host', 'GET', function (res) {
			Metronic.unblockUI('#dnsform');
			if (res.code == 0) {
				$('#dnslist').val(res.data);
			}
		})
	}
	
	var submitDnsSetting = function(){
		var selectNode = $('select[name=dnsnodelist]').val();
		var dnsSetting = $('#dnslist').val();
		var dnscheck = $('#dnscheck').bootstrapSwitch('state');
		var data = {'node_uuid':selectNode, 'setting':dnsSetting, 'syncnode': dnscheck};
		Metronic.blockUI({target: '#dnsform',animate: true});
		pAjaxRequest(data, '/api/v1/system/network/host', 'POST', function (res) {
			Metronic.unblockUI('#dnsform');
			if (operateResponseList(res, LANG.UI_PLATFORM_SYSTEM_DNS)){
			}
		})
	}
	
	var addListeners = function(){
		$("#dnscancel").click(function(){
        	LOCATION('./content/platform/settings/setting_manager.php?tab=3', 'setting_manager');
        });
		
		$("#dnssubmit").on('click', submitDnsSetting);
		
		$('select[name=dnsnodelist]').on('change', function(){
			initDnsList($(this).val());
		});
	}
	
	return {
        init: function () {
        	//初始化备份节点
        	initBackupNodeList();
        	//添加事件
        	addListeners();
        }
    };
}();

jQuery(document).ready(function(){
	Settings_DNS.init();
});