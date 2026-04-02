var DbRecovery = function () {
	
	var _instance, initPointFlag = false, grid, selectTimepoint = '', _selectdb = null;
	var initListener = function(){
		initStandbyHostList();
		$('#standbyhost').on('change', standbyHostChange);
		$('#producthost').on('change', productHostChange);
		$('#dbinstance').on('change', dbinstanceChange);
		$('#dbselect').on('change', dbselectChange);
		$('#timeperiod').on('change', timeperiodselectChange);
		$('#scantimepoint').on('click', scanTimepoint);
		$('#recoveryhost').on('change', recoveryHostChange);
		$('#testconnect').on('click', testConnect);
		
		$('#attachdb').bootstrapSwitch('onSwitchChange', function (e, data) {
			if(data){
				$('.dinstancediv').show();
				$('#submitbtn').prop('disabled', true);
			}else{
				$('.dinstancediv').hide();
				$('#submitbtn').prop('disabled', false);
			}
		});
		
		initRecoveryHostList();
		initJobName();
		
		handleValidation();
		
	}
	
	//测试连接数据库实例
	var testConnect = function(){
		event.preventDefault();
		$('#submitbtn').prop('disabled', false);
		var checkInstance = $(".icheckbox_square-blue.checked").find(".intanceCheck");
		if(1 != checkInstance.length){
			return UIToastr.showInfo('测试连接', '请选择一个您需要测试连接的实例');
		}
		
		//获取选中的实例信息
		var instanceInfo = getCheckedInstance();
		//发送消息到后台
		var data = {};
		data.standbyhostuuid = $('#standbyhost').val();
		data.hostuuid = $('#recoveryhost').val();
		data.instance = instanceInfo;
		data.dbtype = _selectdb.dbtype;
		data = JSON.stringify(data);
    	$.post(CONF.AJAXPATH, {m:CONF.M.DBCDP,f:'recoveryInstanceTestCon',p:data}, function(d){
    		var data = JSON.parse(d);
    		OPREL(d);
        	if(!data.re){
        		$('#submitbtn').prop('disabled', true);
        	}else{
        		$('#submitbtn').prop('disabled', false);
        	}
    	});
	}
	
	//获取选中的实例信息
	var getCheckedInstance = function(){
		var instanceInfo = [];
		var attachdb = $('#attachdb').bootstrapSwitch('state');
		if(!attachdb){
			return instanceInfo;
		}
		//获取选中的实例信息
		var checkInstance = $(".icheckbox_square-blue.checked").find(".intanceCheck");
		if(1 != checkInstance.length){
			return UIToastr.showInfo('测试连接', '请选择一个您需要测试连接的实例');
		}
		
		for(var i=0; i<checkInstance.length; i++){
			var info = {};
			var parentTr = $(checkInstance[i]).closest('tr');
			info.instance = checkInstance[i].dataset.id;
			info.dbtype = checkInstance[i].dataset.type;
			info.dbname = parentTr.find('input[name=idatabase]').val();
			info.iusername  = parentTr.find('input[name=iusername]').val();
			info.ipassword  = parentTr.find('input[name=ipassword]').val();
			if("" == $.trim(info.iusername)){
				return UIToastr.showInfo('扫描数据库', '请填写您选择实例的登录用户名和密码');
			}
			instanceInfo[i] = info;
		}
		return instanceInfo;
	}
	
	var handleValidation = function() {
        var dbrecoveryform = $('#dbrecoveryform');

        dbrecoveryform.validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "",  // validate all fields including form hidden input
            rules: {
            	standbyhost:{
                	required: true,
                },
                producthost:{
                	required: true,
                },
                dbinstance: {
                    required: true,
                },
                dbselect: {
                    required: true,
                },
//                timeperiod: {
//                	required: true,
//                },
                timeinput: {
                    required: true,
                },
                recoveryhost: {
                    required: true,
                },
                jobname: {
                    required: true,
                }
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
        
		//提交按钮
		$('#submitbtn').on('click',function(){
        	if (dbrecoveryform.validate().form()) {
        		submitCheck();
            }
		});
		
		//取消按钮事件
		$('#cancelbtn').on('click',function(){
	    	LOCATION('./content/db/dbrecovery.php', 'dbdataRecovery');
		});
        
	};
	
	var submitCheck = function(){
		//检测是否选择备份时间点
		var select = grid.getSelectedRows();
		if(0 == select.length){
			return UIToastr.showWarning("选择恢复时间点", "请选择一个时间点进行恢复");
		}
		var data = {};
		data.standbyhost = $('#standbyhost').val();
		data.step = select[0];
		data.recoveryhost = $('#recoveryhost').val();
		data.jobname = $('#jobname').val();
		
		//源主机名,目的主机名,数据库类型,实例名,数据库名,时间点      需要记录到数据库,主要用作恢复任务详情显示
		data.standbyhostname = $('#standbyhost').find("option:selected").text();
		data.recoveryhostname = $('#recoveryhost').find("option:selected").text();
		data.standbyhostuuid = $('#standbyhost').val();
		data.recoveryhostuuid = $('#recoveryhost').val();
		data.vendortype = $('#dbselect').find("option:selected").attr('data-vendor');
		data.instancename = $('#dbinstance').find("option:selected").text();
		data.databasename = $('#dbselect').find("option:selected").text();
		data.timeperiod = $('#timeperiod').val();
		data.timepointname = selectTimepoint;
		data.dbinfo = _selectdb;
		data.recoveryinstance = getCheckedInstance();
		data.attachdb = $('#attachdb').bootstrapSwitch('state');
		
		data = JSON.stringify(data);
		Metronic.blockUI({target: '#recovercontent',animate: true, cenrerY: true,});
		$.post(CONF.AJAXPATH, {m:CONF.M.DBCDP,f:'createRecoveryJob',p:data}, function(d){
			Metronic.unblockUI('#recovercontent');
    		if(OPREL(d)){
    			LOCATION('./content/platform/jobs/jobs.php', 'task');
        	}
		});
	}
	
	//目的地选择
	var recoveryHostChange = function(){
		var dbtype = $('#dbinstance').find("option:selected").data('dbtype');
		var recoveryhost = $('#recoveryhost').val();
		if('' == recoveryhost){
			$('#submitbtn').prop('disabled', true);
		}else{
			$('#submitbtn').prop('disabled', false);
		}
		if(0 == dbtype){
			//只有sqlserver要显示数据库附加,开启附加的时候需要测试数据库连接,关闭附加的时候不测试数据库连接\
			$('.attachdbdiv').show();
		}else{
			$('.attachdbdiv').hide();
			$('#submitbtn').prop('disabled', false);
		}
		
//		var dbSelect = $('#dbselect').val();
//		if(null == dbSelect){
//			return UIToastr.showWarning("选择恢复数据库", "请选择一个您需要恢复的数据库");
//		}
		var data = {};
		data.standbyhostuuid = $('#standbyhost').val();
		data.hostuuid = this.value;
		data.dbtype = _selectdb.dbtype;
		if("" == data.hostuuid) return;
		data = JSON.stringify(data);
    	$.post(CONF.AJAXPATH, {m:CONF.M.DBCDP,f:'getHostInstance',p:data}, function(d){
    		var data = JSON.parse(d);
    		if(data.re){
    			data = data.ext;
    			var tableHtml = '';
        		for(var i=0; i<data.length; i++){
        			tableHtml += getInstanceTableTrHtml(data[i]);
        		}
        		$('#instancetable').html(tableHtml);
        		$('.intanceCheck').iCheck({
    				checkboxClass : 'icheckbox_square-blue', 
    				radioClass : 'iradio_square-blue', 
    			});
        		
        		addIcheckboxListener();
    		}else{
    			if($('#attachdb').bootstrapSwitch('state')){
    				//如果附加数据库开关打开才提示
    				OPREL(d);
    			}
    		}
    		
    	});
	}
	
	//添加复选框事件
	var addIcheckboxListener = function(){
		$('.intanceCheck').on('ifChecked', function(e){
			var checkInstance = $(".icheckbox_square-blue.checked").find(".intanceCheck");
			if(checkInstance.length == 1){
				$(checkInstance[0]).iCheck('uncheck');
			}
		})
	}
	
	//得到数据库实例表的一行html
	var getInstanceTableTrHtml = function(data){
        var td1Class = "";
		if(0 == data.dbtype){
			td1Class = "display-none";
		}
		var tr = '<tr style="height: 40px;"><td><label style="padding-right: 60px;margin-bottom: 0;">';
		tr += '<input type="checkbox" data-checkbox="icheckbox_square-blue" data-type="' + data.dbtype;
		tr += '" data-id="' + data.instancename + '"';
		tr += '" class="intanceCheck">' + data.instancename + '</label></td>'; 
		tr += '<td style="padding-right: 20px;" class="' + td1Class + '"><label>数据库</label> <input style="width: 120px;" type="text" maxlength="64" class="" name="idatabase" />';
		tr += '<td style="padding-right: 20px;">';
		tr += '<label style="padding-right: 5px;">用户名</label><input type="text" style="padding-left: 5px;width: 120px;" maxlength="64" name="iusername" value="' + data.username;
		tr += '" /></td><td><label style="padding-right: 5px;">密码</label><input style="padding-left: 5px;width: 120px;" type="password" maxlength="64" ';
		tr += 'name="ipassword" value="' + data.password + '"/></td></tr>';
        return tr;
	}
	
	//初始化任务名
	var initJobName = function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.DBCDP,f:'getRecoveryTaskName',p:{}}, function(d){
			$('#jobname').val(d);
		});
	}
	
	//初始化可以用做恢复的主机
	var initRecoveryHostList = function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.DBCDP,f:'gettRecoveryHostInfo',p:{}}, function(d){
    		var data = JSON.parse(d);
    		if(!data.length) return;
			var recoveryhost = $('#recoveryhost');
			recoveryhost.empty();
			var option = $("<option>").text('').val('');
			recoveryhost.append(option);
			for(var i=0;i<data.length;i++){
				var option = $("<option>").text(data[i].value).val(data[i].uuid);
				recoveryhost.append(option);
			}
    	});
	}
	
	//扫描时间点详情
	var scanTimepoint = function(){
		event.preventDefault();
		var timepoint = $('input[name=timeinput]').val();
		var data={};
		data.timepoint = timepoint;
		data.hostip = _selectdb.hostip;
		data.dbtype = _selectdb.dbtype;
		data.instance = _selectdb.instance;
		data.db = _selectdb.name;
		data.timeperiod = $('#timeperiod').val();
		data.hostuuid = $('#standbyhost').val();
		data.checkbox = true;
		$('.scanshowdiv').show();
		$('#jobNamediv').show();
    	initResourcetable(data);
	}
	
	var initRow = function(){
		var data = grid.getDataTable().data();
		if(!data || 0 == data.length) return;
		
		initGridRowSelectListener('timepoint');
	}
	
	//初始化和更新时间点表格
	var initResourcetable = function(data){
		var data = {m:CONF.M.DBCDP,f:'scanBackupTimepoint',p:data};
		if(!initPointFlag){
			var dataTableOpt = {
	    			'columnDefs' : [{
		                'orderable': false,
		                'targets': [0, 1]
	    			}],
	    			"paging":false,
	    			"info":false
	    	};
	    	grid = new Datatable();
			grid.setAjaxParam(data);
	    	grid.init({src: $("#timepoint"), checkbox:false,  dataTable:dataTableOpt, onDataLoad:initRow});
	    	initPointFlag = true;
		}else{
			grid.setAjaxParam(data);
			grid.getRefresh({});
			//更新后滚动到顶部
			$(".table-scrollable").animate({scrollTop:0},10);
		}
	}
	
	//绑定单选选定事件
	var initGridRowSelectListener = function(tableID){
		//这里说明一下,因为表格是动态生成的需要用到LIVE方法,但是需要注意的是表格的ID号不要和所有的ID号冲突
		$('#' + tableID).find('tbody > tr').off().on('click', function(){
			//1.取消所有选中项
			$(this).parent().find('input').prop("checked", false);
			//2.选中本行
			$(this).find('input').prop("checked", true);
			//额外设置一下选中的时间点名称(全局变量),方便传回后台
			var td = $(this).find('td');
			selectTimepoint = td[2].textContent;
		});
		
		$('#' + tableID).find('tbody > tr').find('input[type="checkbox"]').off().on('click', function(){
			//取消所有其他选中的
			var checkBoxs = $('#' + tableID).find('tbody > tr').find('input[type="checkbox"]');
			for(var i=0; i<checkBoxs.length; i++){
				var span = checkBoxs[i].parentNode;
	    	    $(span).prop("class", "");
		        $(checkBoxs[i]).prop("checked", false);
			}
			var checkBox = $(this);
	        var checked = checkBox[0].checked;
	        if(checked){
	    		var span = checkBox[0].parentNode;
	    	    $(span).prop("class", "");
		        $(checkBox).prop("checked", false);
	    	}else{
	    		var span = checkBox[0].parentNode;
	    	    $(span).prop("class", "checked");
		        $(checkBox).prop("checked", true);
	    	}
		})
	}
	
	//数据库选择改变
	var dbselectChange = function(){
		var timeperiod = $('#timeperiod');
		timeperiod.empty();
		var dbselect = $('#dbselect').val();
//		var option = $("<option>").text('').val('');
//		dbselect.append(option);
		for(var i=0;i<_instance.length;i++){
			if($('#dbinstance').val() == _instance[i].id){
				var database = _instance[i].database;
				for(var j=0;j<database.length;j++){
					if(database[j].id == dbselect){
						var timeperiodArr = database[j].timeperiod;
						//时间段
						for(var k=0; k<timeperiodArr.length; k++){
							var option = $("<option>").text(timeperiodArr[k].des).val(timeperiodArr[k].histime);
							timeperiod.append(option);
						}
					}
					
				}
			}
		}
		timeperiodselectChange();
		$('.stimeperioddiv').show();
	}
	
	//时间段选择改变
	var timeperiodselectChange = function(){
		$('.stimepointdiv').show();
		$('.scanshowdiv').hide();
		$('.attachdbdiv').hide();
		$('#recoveryhost').val('');
		$('#submitbtn').prop('disabled', true);
		var data = {};
		for(var i=0;i<_instance.length;i++){
			var database = _instance[i].database;
			for(var j=0;j<database.length;j++){
				if(database[j].id == $('#dbselect').val()){
					var timeperiodArr = database[j].timeperiod;
					for(var k=0; k<timeperiodArr.length; k++){
						if(timeperiodArr[k].histime == $('#timeperiod').val()){
							data = {
									inputTime:timeperiodArr[k].endtime,
									startTime:timeperiodArr[k].starttime,
									endTime:timeperiodArr[k].endtime
							};
							_selectdb = database[j];
							return initDatetimePicker(data);
						}
					}
				}
			}
		}
	}
	
	//初始化日期控件,按开始和结束时间初始
	var initDatetimePicker = function(atime){
		$('input[name=timeinput]').val(atime.inputTime);
		$(".form_datetime").datetimepicker({
			language:  'zh-CN', 
            autoclose: true,
            isRTL: Metronic.isRTL(),
            format: "yyyy-MM-dd hh:ii:ss",
            pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
            forceParse:true,
            startDate: atime.startTime,
            endDate: atime.endTime,
        });
		$('.form_datetime').datetimepicker('setStartDate', atime.startTime);
		$('.form_datetime').datetimepicker('setEndDate', atime.endTime);
	}
	
	//备份源选择改变
	var productHostChange = function(){
		initInstanceList();
	}
	
	//实例选择改变
	var dbinstanceChange = function(){
		var dbselect = $('#dbselect');
		dbselect.empty();
//		var option = $("<option>").text('').val('');
//		dbselect.append(option);
		for(var i=0;i<_instance.length;i++){
			if($('#dbinstance').val() == _instance[i].id){
				var database = _instance[i].database;
				for(var j=0;j<database.length;j++){
					var option = $("<option>").text(database[j].name).val(database[j].id);
					dbselect.append(option);
				}
			}
		}
		dbselectChange();
		$('.sdatabasediv').show();
	}
	
	//主机选择改变
	var standbyHostChange = function(){
		var data = {};
		data.hostuuid = this.value;
		if("" == this.value){
			$('.sinstancediv').hide();
			$('.sdatabasediv').hide();
			$('.stimepointdiv').hide();
			$('.stimeperioddiv').hide();
			return;
		}
		data = JSON.stringify(data);
		Metronic.blockUI({target: '#dbrecoveryform',animate: true,cenrerY: true,});
    	$.post(CONF.AJAXPATH, {m:CONF.M.DBCDP,f:'scanBackupDatabase',p:data}, function(d){
    		Metronic.unblockUI('#dbrecoveryform');
    		var data = JSON.parse(d);
    		if(0 == data.length){
    			$('.sinstancediv').hide();
    			return UIToastr.showInfo('获取备份数据信息', '没有找到可用作恢复的备份数据');
    		}
    		_instance = data;
    		initProductHostList();
    		$('.sinstancediv').show();
    	});
	}
	
	//初始化备份源
	var initProductHostList = function(){
		var producthost = $('#producthost');
		producthost.empty();
		var hostipArr = [];
		for(var i=0;i<_instance.length;i++){
			if(jQuery.inArray(_instance[i].hostip, hostipArr) == -1){
				//防止IP地址重复
				var option = $("<option>").text(_instance[i].hostip).val(_instance[i].hostip);
				producthost.append(option);
				hostipArr.push(_instance[i].hostip);
			}
		}
		initInstanceList();
	}
	
	//初始化实例列表
	var initInstanceList = function(){
		var dbinstance = $('#dbinstance');
		dbinstance.empty();
//		var option = $("<option>").text('').val('');
//		dbinstance.append(option);
		var producthost = $('#producthost').val();
		for(var i=0;i<_instance.length;i++){
			if(_instance[i].hostip == producthost){
				var option = $("<option>").text(_instance[i].name).val(_instance[i].id).attr("data-dbtype", _instance[i].dbtype);
				dbinstance.append(option);
			}
		}
		dbinstanceChange();
	}
	
	
	//初始化备份主机列表
	var initStandbyHostList = function(){
		initHostList('standbyhost', 2);
	}
	
	//统一初始化生产主机和备份主机下拉列表
	var initHostList = function(id, hosttype){
		var data = {};
		data.hosttype = hosttype;
		data = JSON.stringify(data);
    	$.post(CONF.AJAXPATH, {m:CONF.M.DBCDP,f:'gettHostInfoWithType',p:data}, function(d){
    		var data = JSON.parse(d);
    		if(!data.length) return;
			var hostselect = $('#' + id);
			hostselect.empty();
			var option = $("<option>").text('').val('');
			hostselect.append(option);
			for(var i=0;i<data.length;i++){
				var option = $("<option>").text(data[i].value).val(data[i].uuid);
				hostselect.append(option);
			}
    	});
	}

    return {
        //main function to initiate the module
        init: function () {
        	initListener();
        }

    };

}();


jQuery(document).ready(function() {    
	DbRecovery.init();
});