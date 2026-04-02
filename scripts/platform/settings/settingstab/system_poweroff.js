//关机/重启
var Settings_Poweroff = function(){

	var initBackupNodeList = function(){
		pAjaxRequest({}, '/api/v1/nodes/select', 'GET', function (d) {
			var data = d.data;
			var nodeselect = $('select[name=powernodelist]');
			nodeselect.empty();
			for(var i=0; i<data.length; i++){
				var option = $("<option>").text(data[i].text).val(data[i].uuid);
				nodeselect.append(option);
			}
		});
	}

	//初始化正在运行的任务名
	var initRunningTaskName = function(data){
		$('#runningtaskdiv').html('');
		var taskname = '';
		for(var i=0; i<data.length; i++){
			taskname += data[i].taskname + "<br><br>";
		}
		$('#runningtaskdiv').html(taskname);
	}

	//重启
	var reboot = function(){
		var data = {};
		data.nodeuuid = $('select[name=powernodelist]').val();
		data = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.NODE,f:'getNodeRunningTaskList',p:data}, function(d){
			var data = JSON.parse(d);
			var nodeText = $('select[name=powernodelist] option:selected').text();

			if(data.length > 0){
				//如果有任务正在运行,提示后再提交
				var titel = '<i class="fa  fa-circle-o-notch"></i> ' + LANG.UI_SETTING_POWEROFF_RESTART + nodeText;
				$('#powermodaldiv').find('.modal-title').html(titel);
				$('input[name=powertype]').val(1);
				initRunningTaskName(data);
				$('#powermodaldiv').modal();
			}else{
				//没有在运行的任务,直接确认提示
				bootbox.prompt({
					title: LANG.UI_SETTING_POWEROFF_RESTART_TIPS + nodeText + "?<br>" + LANG.UI_SETTING_POWEROFF_PASSWORD_TIPS + ":",
					inputType: 'password',
					value: '',
					callback: function (password) {
						if (password) {
							submit(1, password, '#powermodaldiv');
						}
					}
				});
			}
		});
	}

	//关机
	var poweroff = function(){
		var data = {};
		data.nodeuuid = $('select[name=powernodelist]').val();
		data = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.NODE,f:'getNodeRunningTaskList',p:data}, function(d){
			var data = JSON.parse(d);
			var nodeText = $('select[name=powernodelist] option:selected').text();

			if(data.length > 0){
				//如果有任务正在运行,提示后再提交
				var titel = '<i class="fa fa-power-off"></i> ' + LANG.UI_SETTING_POWEROFF_POWER + nodeText;
				$('#powermodaldiv').find('.modal-title').html(titel);
				$('input[name=powertype]').val(2);
				initRunningTaskName(data);
				$('#powermodaldiv').modal();
			}else{
				//没有在运行的任务,直接确认提示
				bootbox.prompt({
					title: LANG.UI_SETTING_POWEROFF_POWER_TIPS + nodeText + "?<br>" + LANG.UI_SETTING_POWEROFF_PASSWORD_TIPS + ":",
					inputType: 'password',
					value: '',
					callback: function (password) {
						if (password) {
							submit(2, password, '#powermodaldiv');
						}
					}
				});
			}
		});
	}

	var powersubmit = function(){
		var type = $('input[name=powertype]').val();
		var password = $('input[name=password]').val();
		var blockID = '#powerform';
		submit(type, password, blockID);
	}

	var submit = function (type, password, blockID) {
		var data = {};
		data.powertype = type;
		data.password = hex_md5(password);
		data.nodeuuid = $('select[name=powernodelist]').val();
		data = JSON.stringify(data);
		Metronic.blockUI({target: blockID,animate: true});
		$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM,f:'powerSubmit',p:data}, function(data){
			Metronic.unblockUI(blockID);
			if(OPREL(data)){
				$('#powermodaldiv').modal('hide');
			}
		});
	}

	var addListeners = function(){
		$('#powerreboot').on('click', reboot);
		$('#poweroff').on('click', poweroff);
		$('#powersubmit').on('click', powersubmit);
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
	Settings_Poweroff.init();
})