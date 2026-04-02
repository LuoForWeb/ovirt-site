var DbCDPEDIT = function () {
	var configData = {producthostInfo:{},standbyhostInfo:{},highInfo:{}};
	var dbTree;
	var networkGrid, networkGridModal,serviceGrid, initNetworkGridFlag = false, initServiceGridFlag = false, initNetworkGridModalFlag = false;
	var networkInfo, takeovertd, currentNetwork, modifyFirstSettingFlag = false;
	var nodeSelectFlag = false, backupHostIsServer = null;
	var SETTINGS, setServiceGridOldDataFlag = false, setCardOldDataFlag = false;//旧的配置信息
	
	//初始化生产主机列表
	var initProductHostList = function(){
		initHostList('producthost', 1);
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
				var option = '<option data-bsflag="' + data[i].bsflag + '" value="' + data[i].uuid + '">' + data[i].value + '</option>';
				hostselect.append(option);
			}
			
			//初始化备份主机列表后,请求旧数据
			if(2 == hosttype){
				initOldSettings();
			}
    	});
	}
	
	//生产主机选择改变
	var productHostChange = function(){
		var producthost = $('#producthost').val();
		if("" != producthost){
			$('#dbtypediv').show();
			$('#dbtype').val("");
			
		}else{
			$('#dbtypediv').hide();
		}
		
		$('#instancediv').hide();
		$('#databasediv').hide();
		uncheckAllNode();
	}
	
	var dbtypeChange = function(){
		var data = {};
		data.hostuuid = $('#producthost').val();
		data.dbtype = $('#dbtype').val();
		if("" == data.hostuuid) return;
		data = JSON.stringify(data);
    	$.post(CONF.AJAXPATH, {m:CONF.M.DBCDP,f:'getHostInstance',p:data}, function(d){
    		var data = JSON.parse(d);
    		if(data.re){
    			data = data.ext;
    			if(undefined == data){
    				$('#instancetable').html("");
    				return UIToastr.showInfo('获取数据库实例', '没有获取到数据库实例,请检查生产主机数据库服务是否开启');
    			}
    			var tableHtml = '';
        		for(var i=0; i<data.length; i++){
        			tableHtml += getInstanceTableTrHtml(data[i]);
        		}
        		$('#instancetable').html(tableHtml);
        		$('.intanceCheck').iCheck({
    				checkboxClass : 'icheckbox_square-blue', 
    				radioClass : 'iradio_square-blue', 
    			});
        		
        		$('#instancediv').show();
    		}else{
    			OPREL(d);
    			$('#instancediv').hide();
    		}
    		
    	});
    	
    	uncheckAllNode();
    	$('#databasediv').hide();
	}
	
	//得到数据库实例表的一行html
	/**
	 * 0 sqlserver       账号  密码
		2 Oracle 数据库  账号  密码
		5 mysql
		3 sybase         账号  密码
		1 db2	数据库    账号  密码
	 */
	var getInstanceTableTrHtml = function(data){
		var dbshow = "", usershow = "", passshow = "";
		switch(parseInt(data.dbtype)){
			case 0://sqlserver 账号  密码
			case 3://sybase    账号  密码
			case 10: //达梦		账号  密码
				dbshow = "display-none";
				break;
			case 1://db2 数据库    账号  密码
			case 2://Oracle 数据库  账号  密码
			case 8://人大金仓	数据库  账号  密码
			case 9://神州通用	数据库  账号  密码
				break;
			case 5://mysql 什么都不要
				dbshow = "display-none";
				usershow = "display-none";
				passshow = "display-none";
				break;
			default:
				break;
		}
		
		var tr = '<tr style="height: 40px;"><td><label style="padding-right: 60px;margin-bottom: 0;">';
		tr += '<input type="checkbox" data-checkbox="icheckbox_square-blue" data-type="' + data.dbtype;
		tr += '" data-id="' + btoa(data.instancename) + '"';
		tr += '" class="intanceCheck">' + data.instancename + '</label></td>'; 
		tr += '<td style="padding-right: 20px;" class="' + dbshow + '"><label>数据库</label> <input style="width: 120px;" type="text" maxlength="64" class="" name="idatabase" />';
		tr += '<td style="padding-right: 20px;" class="' + usershow + '">';
		tr += '<label style="padding-right: 5px;">用户名</label><input type="text" style="padding-left: 5px;width: 120px;" maxlength="64" name="iusername" value="' + data.username;
		tr += '" /></td><td class="' + passshow + '"><label style="padding-right: 5px;">密码</label><input style="padding-left: 5px;width: 120px;" type="password" maxlength="64" ';
		tr += 'name="ipassword" value="' + data.password + '"/></td></tr>';
        return tr;
	}
	
	var nodeSelect = function(treeId, treeNode, clickFlag){
		var treeObj = $.fn.zTree.getZTreeObj(treeId);
		treeObj.checkNode(treeNode, !treeNode.checked, true);
	}
	
	//获取选中的实例信息
	var getCheckedInstance = function(){
		//获取选中的实例信息
		var checkInstance = $(".icheckbox_square-blue.checked").find(".intanceCheck");
		if(0 == checkInstance.length){
			return UIToastr.showInfo('扫描数据库', '请至少选择一个您需要扫描的实例');
		}
		var instanceInfo = [];
		for(var i=0; i<checkInstance.length; i++){
			var info = {};
			var parentTr = $(checkInstance[i]).closest('tr');
			info.dbsrv = atob(checkInstance[i].dataset.id);
			info.dbtype = checkInstance[i].dataset.type;
			info.dbname = parentTr.find('input[name=idatabase]').val();
			info.dbuser  = parentTr.find('input[name=iusername]').val();
			info.dbpass  = parentTr.find('input[name=ipassword]').val();
			var tipFlag = false;
			switch(parseInt(info.dbtype)){
				case 0://sqlserver 账号  密码
					if("" == $.trim(info.dbuser) || "" == $.trim(info.dbpass)){
						tipFlag = true;
					}
					break;
				case 10://达梦 账号  密码
					if("" == $.trim(info.dbuser) || "" == $.trim(info.dbpass)){
						tipFlag = true;
					}
					info.dbname = "dm";	//不带dbname api第一次扫描不出来,随便带一个数据库名才行fix bug
					break;
				case 1://db2 数据库    账号  密码
				case 2://Oracle 数据库  账号  密码
				case 8://人大金仓	数据库  账号  密码
				case 9://神州通用	数据库  账号  密码
					if("" == $.trim(info.dbname) || "" == $.trim(info.dbuser) || "" == $.trim(info.dbpass)){
						tipFlag = true;
					}
					break;
				case 3://sybase    账号  密码
					if("" == $.trim(info.dbuser) || "" == $.trim(info.dbpass)){
						tipFlag = true;
					}
					info.dbname = "master";
					break;
				case 5://mysql 什么都不要
					tipFlag = false;
					info.dbname = "mysql";
					break;
				default:
					break;
			}
			
			if(tipFlag){
				return UIToastr.showInfo('扫描数据库', '请填写您选择实例的必要信息用于扫描数据库');
			}
			
			instanceInfo[i] = info;
		}
		return instanceInfo;
	}
	
	//扫描实例下的数据库
	var scanDB = function(){
		event.preventDefault();
		var checkInstance = $(".icheckbox_square-blue.checked").find(".intanceCheck");
		if(0 == checkInstance.length){
			return UIToastr.showInfo('扫描数据库', '请至少选择一个您需要扫描的实例');
		}
		
		//获取选中的实例信息
		var instanceInfo = getCheckedInstance();
		
		//发送消息到后台
		var data = {};
		data.hostuuid = $('#producthost').val();
		data.instance = instanceInfo;
		data = JSON.stringify(data);
    	$.post(CONF.AJAXPATH, {m:CONF.M.DBCDP,f:'scanHostDB',p:data}, function(d){
    		var data = JSON.parse(d);
        	if(data.re){
        		//success
        		var setting = {
        				check: {
        					enable: true,
        					nocheckInherit: false
        				},
        				data: {
        					simpleData: {
        						enable: true,
        						idKey: "id",
        						pIdKey: "pid",
        						rootPId: 0
        					}
        				},
        				callback: {
        					beforeClick: nodeSelect,
//        					beforeExpand: nodeExpand
        				}
        			};
        		dbTree = $.fn.zTree.init($("#db_tree"), setting, data.ext);
        		if(undefined !== data.ext){
        			$('#databasediv').show();
        		}else{
        			$('#databasediv').hide();
        		}
        		
        		groupOldTree();
        	}else{
        		OPREL(d);
        		$('#databasediv').hide();
        	}
    	});
		
	}
	
	//选中之前选中的数据库
	var groupOldTree = function(){
		var allNode = dbTree.transformToArray(dbTree.getNodes());//所有节点
		var dbInfo = SETTINGS.producthostInfo.dbInfo;	//之前所有选择的数据库
		if(null == dbInfo[0]) return;	//fix之前暂时没有找到数据库存储dbinfo为空的情况的bug
		Metronic.blockUI({target: '#db_tree',animate: true});
		for(var i=0; i<allNode.length; i++){
			//遍历所有节点
			for(var j=0; j<dbInfo.length; j++){
				//遍历所有之前选择的数据库
				if(allNode[i].name == dbInfo[j].dbname){
					//如果这个节点是之前选中的数据库
					dbTree.checkNode(allNode[i], true, true, true);
				}
			}
		}
		Metronic.unblockUI('#db_tree');
	}
	
	//取消选中所有节点
	var uncheckAllNode = function(){
		if(undefined == dbTree) return;
		var nodes = dbTree.getCheckedNodes();
		for(var i=0;i<nodes.length;i++){
			if(nodes[i].checked){
				dbTree.checkNode(nodes[i], false, false, true);
			}
		}
	}
	
	var initListener = function(){
		initProductHostList();
		initStandbyHostList();
		$('#producthost').on('change', productHostChange);
		$('#standbyhost').on('change', standbyHostChange);
		$('#dbtype').on('change', dbtypeChange);
		$('#scandb').on('click', scanDB);
		$('#takeovercheck').on('switchChange.bootstrapSwitch', function(){
			if(this.checked){
				//初始化接管网卡列表和服务器列表
				$('.tkdivs').show();
				getNetworkCardInfo();
				initStandbyHostGrid();
				initServiceGrid();
			}else{
				$('.tkdivs').hide();
			}
		});
		
		//备份类型改变
		$('#backuptype').on('change', backuptypeChange);
		
		$('#addtakeovercard').on('click', selectProductCard);
		
		//节点改变
		$('#selectnode').on('change', initStorageSelect);
		//日志管理方式改变
		$('#logtype').on('change', logtypeChange);
		
		//备份数据目录改变
		$('#backupdir').on('change', backupdirChange);
		
		//历史数据目录改变
		$('#historydir').on('change', historydirChange);
	}
	
	//备份数据目录改变
	var backupdirChange = function(){
		if($.trim(this.value) != SETTINGS.standbyhostInfo.backupdir){
			$('#backupdirtip').show();
		}else{
			$('#backupdirtip').hide();
		}
	}
	
	//历史数据目录改变
	var historydirChange = function(){
		if($.trim(this.value) != SETTINGS.standbyhostInfo.historydir){
			$('#historydirtip').show();
		}else{
			$('#historydirtip').hide();
		}
	}
	
	
	
	var backuptypeChange = function(){
		var type = $('#backuptype').val();
		//历史数据目录跟到实时备份走,有实时备份才有
		if(type == "2"){
			//只有业务接管不用选择历史目录和历史份数
			$('.hisdiv').hide();
		}else{
			if(!backupHostIsServer.flag){
				//不是备份服务器才显示
				$('.hisdiv').show();
			}else{
				$('#hisdirdiv').hide();
				$('#hisnumdiv').show();
			}
		}
	}
	
	var logtypeChange = function(){
		var type = $('#logtype').val();
		$('.logdiv').hide();
		switch(type){
			case "0"://按步数
				$('.lognumdiv').show();
				break;
			case "1"://按磁盘空间大小
				$('.logsizediv').show();
				break;
			case "2"://自动按分配的磁盘自适应,磁盘有多大,就用多大
				
				break;
			case "3"://按时间,单位小时
				$('.logdaydiv').show();
				break;
		}
	}
	
	//根据选择主站的网卡id获取网卡详情
	var getSelectNetworkCardInfo = function(id){
		var card = networkInfo.productNetwork;
		for(var i=0; i<card.length; i++){
			if(id == card[i].adaptername){
				return card[i];
			}
		}
	}
	
	//检查网卡是否已经选择了接管
	var checkTakeoverCard = function(select){
		var standbyCard = networkInfo.standbyNetwork;
		for(var i=0; i<select.length; i++){
			for(var j=0; j<standbyCard.length; j++){
				if(currentNetwork == standbyCard[j].adaptername){
					//不检查当前这个网卡的接管信息
					break;
				}
				var takeover = standbyCard[j].takeover;
				if(undefined == takeover) break;
				for(var k=0; k<takeover.length; k++){
					if(takeover[k] == select[i]){
						//已经配置了
						return false;
					}
				}
			}
		}
		return true;
	}
	
	//选择生产主机网卡确认
	var selectProductCard = function(){
		var select = networkGridModal.getSelectedRows();
		if(!checkTakeoverCard(select)){
			return UIToastr.showWarning('接管勾选的网卡', '您选择的网卡已经在接管列表中,请重新选择!');
		}
		var tdhtml = '';
		for(var i=0; i<select.length; i++){
			var info = getSelectNetworkCardInfo(select[i]);
			tdhtml += info.ipaddress + "<br>";
		}
		takeovertd.html(tdhtml);
		$('#modalnetwordcard').modal('hide');
		updateNetworkInfo(select);
	}
	
	//设置之前的网卡
	var initOldTakeoverCard = function(){
		if(setCardOldDataFlag) return true;
		if(!SETTINGS.highInfo.takeover.check) return true;
		var network = SETTINGS.highInfo.takeover.network;
		//遍历从站网卡,找到有接管的主站网卡,然后将表格对应位置设置成主站的IP地址
		var standbyNetwork = network.standbyNetwork;
		//设置上次选择的内容
		var tabledata = networkGrid.getDataTable().data();
		for(var i=0; i<tabledata.length; i++){
			for(var j=0; j<standbyNetwork.length; j++){
				if(tabledata[i][0] == standbyNetwork[j].address){
					var html = "";
					var takeover = standbyNetwork[j].takeover;
					for(var k=0; k<takeover.length; k++){
						html += getTakeoverCardIpaddr(takeover[k]);
					}
					var inputStr = "button[data-name=" + standbyNetwork[j].address + "]";
					var thisInput = $('#takeovernetworktable').find(inputStr);
					$(thisInput[0].parentNode).prev().html(html);
				}
			}
		}
		
		setCardOldDataFlag = true;
	}
	
	//通过网卡名得到网卡的IP地址
	var getTakeoverCardIpaddr = function(adaptername){
		var ipaddrName = "";
		var productNetwork = SETTINGS.highInfo.takeover.network.productNetwork;
		for(var i=0; i<productNetwork.length; i++){
			if(productNetwork[i].adaptername == adaptername){
				ipaddrName = productNetwork[i].ipaddress + "<br>";
			}
		}
		return ipaddrName;
	}
	
	//更新网卡接管对应关系
	var updateNetworkInfo = function(select){
		var standbyCard = networkInfo.standbyNetwork;
		for(var i=0; i<standbyCard.length; i++){
			if(currentNetwork == standbyCard[i].adaptername){
				standbyCard[i].takeover = select;
				return;
			}
		}
	}
	
	//操作
	var opButton = function(div, data){
		var btn = '<button type="button" class="btn green-haze btn-sm takeovernwbt" data-name="' + data[3].address
					+ '" name="' + data[3].adaptername + '">' + 
				  '<i class="fa fa-search"></i> ' + '选择接管网卡' + '</button>';
		$(div).html(btn);
	}
	
	var addOpButton = function(){
		var data = networkGrid.getDataTable().data();
		if(0 == data.length) return;
		var opDiv = $('#takeovernetworktable tbody > tr').find('td:eq(3)');
		for(var i=0; i<opDiv.length; i++){
			opButton(opDiv[i], data[i]);
		}
		//opButton
		addOpButtonListener();
		initOldTakeoverCard();
	}
	var addOpButtonListener = function(){
		$('.takeovernwbt').unbind().on('click', selectProductNetworkCard);
	}
	
	//初始化生产主站网卡表格
	var selectProductNetworkCard = function(){
		event.preventDefault();
		var data = {};
		data.producthostuuid = $('#producthost').val();
		var data = {m:CONF.M.DBCDP,f:'getProductHostNetworkCard',p:data};
		if(!initNetworkGridModalFlag){
			var dataTableOpt = {
	    			'columnDefs' : [{
		                'orderable': false,
		                'targets': [0, 1, 2, 3]
	    			}],
	    	};
			networkGridModal = new Datatable();
			networkGridModal.setAjaxParam(data);
			networkGridModal.init({src: $("#standbyNetworkTable"),  dataTable:dataTableOpt});
			initNetworkGridModalFlag = true;
		}else{
			networkGridModal.setAjaxParam(data);
			networkGridModal.getRefresh({});
			//更新后滚动到顶部
			$(".table-scrollable").animate({scrollTop:0},10);
		}
		takeovertd = $(this).parent().prev();
		currentNetwork = this.name;
		$('#modalnetwordcard').modal({'width':'800px', 'height':'500px'});
	}
	
	//初始化从站网卡列表
	var initStandbyHostGrid = function(){
		var data = {};
		data.standbyhostuuid = $('#standbyhost').val();
		var data = {m:CONF.M.DBCDP,f:'getStandbyHostNetworkCard',p:data};
		if(!initNetworkGridFlag){
			var dataTableOpt = {
	    			'columnDefs' : [{
		                'orderable': false,
		                'targets': [0, 1, 2, 3]
	    			}],
	    			"paging":false,
	    			"info":false
	    	};
			networkGrid = new Datatable();
			networkGrid.setAjaxParam(data);
			networkGrid.init({src: $("#takeovernetworktable"), checkbox:false,  dataTable:dataTableOpt, onDataLoad:addOpButton});
			initNetworkGridFlag = true;
		}else{
			networkGrid.setAjaxParam(data);
			networkGrid.getRefresh({});
			//更新后滚动到顶部
			$(".table-scrollable").animate({scrollTop:0},10);
		}
	}
	
	//初始化
	var initServiceGrid = function(){
		var data = {};
		data.standbyhostuuid = $('#standbyhost').val();
		var data = {m:CONF.M.DBCDP,f:'getHostService',p:data};
		if(!initServiceGridFlag){
			var dataTableOpt = {
	    			'columnDefs' : [{
		                'orderable': false,
		                'targets': [0, 1, 2, 3, 4]
	    			}],
	    			"paging":false,
	    			"info":false
	    	};
			serviceGrid = new Datatable();
			serviceGrid.setAjaxParam(data);
			serviceGrid.init({src: $("#takeoverservicetable"),  dataTable:dataTableOpt,onDataLoad:setServiceGridOldData});
			initServiceGridFlag = true;
		}else{
			serviceGrid.setAjaxParam(data);
			serviceGrid.getRefresh({});
			//更新后滚动到顶部
			$(".table-scrollable").animate({scrollTop:0},10);
		}
	}
	
	//通过回调来设置上次选择的内容,只设置一次
	var setServiceGridOldData = function(){
		if(setServiceGridOldDataFlag) return true;
		var service = SETTINGS.highInfo.takeover.service;
		//设置上次选择的内容
		var tabledata = serviceGrid.getDataTable().data();
		for(var i=0; i<tabledata.length; i++){
			for(var j=0; j<service.length; j++){
				if(tabledata[i][5] == service[j]){
					var inputStr = "input[data-name=" + tabledata[i][5] + "]";
					var thisInput = $('#takeoverservicetable').find(inputStr);
					$(thisInput[0].parentNode).prop("class", "checked");
					$(thisInput).prop("checked", true)
				}
			}
		}
		
		setServiceGridOldDataFlag = true;
	}
	
	//得到网卡信息,包括生产主机和备份主机  接管用
	var getNetworkCardInfo = function(){
		var data = {};
		data.producthostuuid = $('#producthost').val();
		data.standbyhostuuid = $('#standbyhost').val();
		data = JSON.stringify(data);
    	$.post(CONF.AJAXPATH, {m:CONF.M.DBCDP,f:'getTakeoverNetworkCardInfo',p:data}, function(d){
    		var data = JSON.parse(d);
    		networkInfo = data;
    		if(!modifyFirstSettingFlag){
    			//如果是第一次加载,设置之前的接管参数
    			var takeover = SETTINGS.highInfo.takeover;
    			if(takeover.check){
    				var standbyNetwork = takeover.network.standbyNetwork;
    				for(var i=0; i<networkInfo.standbyNetwork.length; i++){
    					for(var j=0; j<standbyNetwork.length; j++){
    						if(networkInfo.standbyNetwork[i].address == standbyNetwork[j].address){
    							networkInfo.standbyNetwork[i].takeover = standbyNetwork[j].takeover;
    						}
    					}
    				}
    			}
    			modifyFirstSettingFlag = true;
    		}
    	});
	}
	
	//得到备份主机的服务信息   接管用
	var getStandbyHostService = function(){
		var data = {};
		data.hostuuid = $('#standbyhost').val();
		data = JSON.stringify(data);
    	$.post(CONF.AJAXPATH, {m:CONF.M.DBCDP,f:'getHostService',p:data}, function(d){
    		var data = JSON.parse(d);
    	});
	}
	
	//备份任务步骤
	var wizardInit = function(){
		if (!jQuery().bootstrapWizard) {
            return;
        }
        var form = $('#submit_form');
        var error = $('.alert-danger', form);
        var success = $('.alert-success', form);
        var handleTitle = function(tab, navigation, index) {
            var total = navigation.find('li').length;//总共的步骤数
            var current = index + 1;      //当前步骤
            // set wizard title
//            $('.step-title', $('#vmbackupcontent')).text('Step ' + (index + 1) + ' of ' + total);
            // set done steps
            jQuery('li', $('#dbcdpcontent')).removeClass("done");
            var li_list = navigation.find('li');
            for (var i = 0; i < index; i++) {
                jQuery(li_list[i]).addClass("done");
            }
            
            //如果第一步 上一步按钮隐藏
            if (current == 1) {
                $('#dbcdpcontent').find('.button-previous').hide();
                $('#dbcdpcontent').find('.button-next').addClass('next-btn-margin-left');
            } else {
                $('#dbcdpcontent').find('.button-previous').show();
                $('#dbcdpcontent').find('.button-next').removeClass('next-btn-margin-left');
            }
            
            //如果是最后一步
            if (current >= total) {
                $('#dbcdpcontent').find('.button-next').hide();
                $('#dbcdpcontent').find('.button-submit').show();
            } else {
                $('#dbcdpcontent').find('.button-next').show();
                $('#dbcdpcontent').find('.button-submit').hide();
            }
            
            
            Metronic.scrollTo($('.page-title'));
        }

        // default form wizard
        $('#dbcdpcontent').bootstrapWizard({
            'nextSelector': '.button-next',
            'previousSelector': '.button-previous',
            onTabClick: function (tab, navigation, index, clickedIndex) {
                return false;
            },
            
            //下一步
            onNext: function (tab, navigation, index) {
                success.hide();
                error.hide();
                switch(index){
                	case 1:
                		if(step1Valid() == false){
                			return false;
                		}
                		break;
                	case 2:
                		if(step2Valid() == false){
                			return false;
                		}
                		break;
                	case 3:
                		if(step3Valid() == false){
                			return false;
                		}
                		break;
                }
                handleTitle(tab, navigation, index);
            },
            
            //上一步
            onPrevious: function (tab, navigation, index) {
                success.hide();
                error.hide();

                handleTitle(tab, navigation, index);
            },
            
            //进度条显示
            onTabShow: function (tab, navigation, index) {
                var total = navigation.find('li').length;
                var current = index + 1;
                var $percent = (current / total) * 100;
                $('#vmbackupcontent').find('.progress-bar').css({
                    width: $percent + '%'
                });
            }
        });

        $('#dbcdpcontent').find('.button-previous').hide();
        $('#dbcdpcontent .button-submit').click(submit).hide();
	};
	
	//提交
	var submit = function(){
		if('' == $.trim($("#jobname").val())){
			$('.jobnametip').html(LANG.UI_BACKUP_NAME_TIPS).show();
			return;
		}
		$('.jobnametip').hide();
		configData.jobname = $.trim($("#jobname").val());
		configData.taskuuid = $('#task_uuid').val();
    	var jsonData = JSON.stringify(configData);
    	Metronic.blockUI({target: '#dbcdpcontent',animate: true,cenrerY: true,});
    	$.post(CONF.AJAXPATH, {m:CONF.M.DBCDP,f:'modifyBackupJob',p:jsonData}, function(data){
    		Metronic.unblockUI('#dbcdpcontent');
    		if(OPREL(data)){
    			LOCATION('./content/platform/jobs/jobs.php', 'task');
        	}
    	});
	}
	
	var step1Tips = function(){
		UIToastr.showInfo('选择数据库', '1.选择需要备份数据库所在的生产主机<br>2.选择需要备份的数据库类型<br>3.填写实例用户名密码,扫描数据库<br>4.选择需要备份的数据库');
		return false;
	}
	
	//备份主机选择改变
	var standbyHostChange = function(){
		var data = {};
		data.hostuuid = $('#standbyhost').val();
		if("" == $('#standbyhost').val()){
			$('.standbyhostchangediv').hide();
			$('.storagediv').hide();
			return true;
		}else{
			$('.standbyhostchangediv').show();
		}
		
		//如果是备份主机是备份服务器，隐藏备份数据目录,历史数据目录
		var sbFlag = $('#standbyhost option:selected').data('bsflag');
		if(sbFlag){
			$('.dirdiv').hide();
		}else{
			$('.dirdiv').show();
		}
		
//		data = JSON.stringify(data);
//    	$.post(CONF.AJAXPATH, {m:CONF.M.DBCDP,f:'getBackupDirInfo',p:data}, function(d){
//    		var data = JSON.parse(d);
//    		$('#backupdir').val(data.backupdir);
//    		$('#historydir').val(data.historydir);
//    	});
    	
    	initStorage($('#standbyhost').val());
    	
    	//设置备份类型为初始值:实时备份
    	$('#backuptype').val("0");
	}
	
	//检查备份机器类型,如果备份主机就是备份服务器,要初始化存储
	var initStorage = function(hostuuid){
		var data = {};
		data.hostuuid = hostuuid;
		data = JSON.stringify(data);
    	$.post(CONF.AJAXPATH, {m:CONF.M.DBCDP,f:'checkBackupHostIsServer',p:data}, function(d){
    		var data = JSON.parse(d);
    		backupHostIsServer = data;
    		if(data.flag){
    			initStorageSelect();
    			$('.storagediv').show();
    		}else{
    			$('#selectstorage').empty();
    			$('.storagediv').hide();
    		}
    	});
	}
	
	//初始化存储下拉框
	var initStorageSelect = function(){
		var data = {};
		data.nodeuuid = backupHostIsServer.nodeuuid;
		var jsonData = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.STORAGE,f:'getBackupStorageList',p:jsonData}, function(d){
    		var data = JSON.parse(d);
    		var softselect = $('#selectstorage');
    		softselect.empty();
			for(var i=0; i<data.length; i++){
				var option = $("<option>").text(data[i].text).val(data[i].uuid);
				softselect.append(option);
			}
    	});
	}
	
	//初始化任务名
	var initJobName = function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.DBCDP,f:'getBackupTaskName',p:{}}, function(d){
			$('#jobname').val(d);
		});
	}
	
	var step1Valid = function(){
		if('' == $('#producthost').val() || '' == $('#dbtype').val() || !dbTree){
			return step1Tips();
		}
		var nodes = dbTree.getCheckedNodes();
		if(!nodes.length){
			return step1Tips();
		}
		var dbInfo = [], j = 0;
		var dbshow = '';
		for(var i=0;i<nodes.length;i++){
			if(3 == nodes[i].type){
				var dbNode = {
					dbname : nodes[i].name,
					dbtype : nodes[i].vendortype,
					dbsrv : nodes[i].instantname,
				};
				dbInfo[j++] = dbNode;
				dbshow += nodes[i].vendorname + " > " + nodes[i].instantname + " > " + nodes[i].name + "<br>";
			}
		}
		configData.producthostInfo.dbInfo = dbInfo;
		configData.producthostInfo.hostuuid = $('#producthost').val();
		configData.producthostInfo.dbtype = $('#dbtype').val();
		configData.producthostInfo.instanceInfo = getCheckedInstance();
		if(undefined == configData.producthostInfo.instanceInfo){
			return false;
		}
		showStep1(dbshow);
	}
	
	var showStep1 = function(dbshow){
//		initStandbyHostList();
//		initJobName();
		
		//第四步数据展示
		$('.producthostshow').html($('#producthost').find("option:selected").text());
		$('.dbshow').html(dbshow);
	}
	
	
	var step2Valid = function(){
		if('' == $('#standbyhost').val() || '' == $('#backuptype').val() || 
		   '' == $.trim($('#backupdir').val()) || '' == $.trim($('#historydir').val()) || 
		   '' == $.trim($('#historycopys').val())){
			UIToastr.showInfo('选择填写备份主机信息', '请按提示选择和填写备份目的地信息');
			return false;
		}
		
		//如果是备份服务器，检查是否选择了备份存储
		var sbFlag = $('#standbyhost option:selected').data('bsflag');
		var selectstorage = $('#selectstorage').val();
		if(sbFlag == true && null == selectstorage){
			UIToastr.showInfo('选择目标存储', '请选择目标存储，用于存放备份数据');
			return false;
		}
		
		//验证目录
		var data = {};
		data.backuptype = $('#backuptype').val();
		data.standbyhost = $('#standbyhost').val();
		data.backupdir = $.trim($('#backupdir').val());
		data.historydir = $.trim($('#historydir').val());
		if(backupHostIsServer.flag){
			data.storageuuid = $.trim($('#selectstorage').val());
		}else{
			data.storageuuid = '';
		}
		data = JSON.stringify(data);
		
		var checkResult = true;
    	$.ajax({ 
			type: "post", 
	        url: CONF.AJAXPATH, 
	        async:false, 
	        data:{m:CONF.M.DBCDP,f:'standbyHostDirCheck',p:data},
	        success: function(data){ 
	        	var d = JSON.parse(data);
	        	if(!d.re){
	        		//如果是检查失败
	        		checkResult = false;
	        		return OPREL(data);
	        	}
	        } 
		});
    	
    	//验证是否可以做接管
    	var data = {};
    	data.producthost = $('#producthost').val();
		data.standbyhost = $('#standbyhost').val();
		data.backuptype = $('#backuptype').val();
		if("0" != data.backuptype){
			data = JSON.stringify(data);
			$.ajax({ 
				type: "post", 
		        url: CONF.AJAXPATH, 
		        async:false, 
		        data:{m:CONF.M.DBCDP,f:'takeoverEnvironmentCheck',p:data},
		        success: function(data){ 
		        	var d = JSON.parse(data);
		        	if(!d.re){
		        		//如果是检查失败
		        		checkResult = false;
		        		return OPREL(data);
		        	}
		        } 
			});
		}
		
    	
		configData.standbyhostInfo.standbyhost = $('#standbyhost').val();
		configData.standbyhostInfo.backuptype = $('#backuptype').val();
		configData.standbyhostInfo.storageuuid = $.trim($('#selectstorage').val());
		configData.standbyhostInfo.backupdir = $.trim($('#backupdir').val());
		configData.standbyhostInfo.historydir = $.trim($('#historydir').val());
		configData.standbyhostInfo.historycopys = $.trim($('#historycopys').val());
		configData.standbyhostInfo.backupHostIsServer = backupHostIsServer;
		
		showStep2();
		
		//检查任务类型,隐藏和显示接管信息
		if(data.backuptype == "0"){
			//实时备份
			$('#tabtakeover').hide();
			$('#takeoverdes').hide();
		}else{
			//有接管
			$('#tabtakeover').show();
			$('#takeoverdes').show();
		}
		
		return checkResult;
	}
	
	var showStep2 = function(){
		if(backupHostIsServer.flag){
			//如果是备份系统,隐藏第四步的目录
			$('.backupdiv').hide();
			$('#hisdivshow').hide();
			if($('#backuptype') != "2"){
				//如果不是业务接管,显示历史分数
				$('#hisnumdivshow').show();
			}
		}else{
			$('.backupdiv').show();
			if("2" == $('#backuptype').val()){
				//如果是业务接管,隐藏历史目录
				$('#hisdivshow').hide();
			}else{
				$('#hisdivshow').show();
			}
		}
		
		$('.backuphostshow').html($('#standbyhost').find("option:selected").text());
		$('.backuptypeshow').html($('#backuptype').find("option:selected").text());
		$('.backupdirshow').html($.trim($('#backupdir').val()));
		$('.historydirshow').html($.trim($('#historydir').val()));
		$('.historycopysshow').html($.trim($('#historycopys').val()));
		
		
		//fix bug 接管只有在有接管的时候才展示,防止上一步,下一步出现
		var activeLi = $('#tab3ul>li.active');
		if(activeLi[0].id == "tabtakeover" && $('#backuptype').val() == "0"){
			//如果之前展示的是历史数据tab,然后这一次是"实时备份",设置第一个tab为active状态
			setFirstTabActive('tab3ul', 'tabcontentdiv');
		}
	}
	
	//设置第一个tab为active状态
	var setFirstTabActive = function(ulid, tabcontentdiv){
		//移除所有tab的active状态,移除所有tabcontentdiv的active状态
		$('#' + ulid + ">li.active").removeClass("active");
		$('#' + tabcontentdiv).children('.active').removeClass("active");
		
		//给第一个tab加active状态,给第一个tabcontentdiv添加active状态
		$($('#' + ulid).children("li").get(0)).addClass("active");
		$($('#' + tabcontentdiv).children(".tab-pane").get(0)).addClass("active");
	}
	
	var getLogUnits = function(value){
		var value = parseInt(value);
		var unit = "";
		switch(value){
			case 0:
				unit = "MB";
				break;
			case 1:
				unit = "GB";
				break;
			case 2:
				unit = "TB";
				break;
		}
		return unit;
	}

	var step3Valid = function(){
		//日志
		var logshow = '';
		var logtype = parseInt($('#logtype').val());
		switch(logtype){
			case 0:
				logshow = "按恢复条数保留日志," + " 恢复条数:" +  $('#recoverystep').val();
				if($.trim($('#recoverystep').val()) < 10000){
					UIToastr.showInfo('选择填写恢复条数', '请填写按恢复条数保留日志的值,恢复条数不能小于10000');
					return false;
				}
				break;
			case 1:
				logshow = "按容量大小保留日志," + " 日志容量: " +  $('#logsize').val() + " " + getLogUnits($('#logsizeunits').val());
				break;
			case 2:
				logshow = "按容量大小保留日志," + " 日志容量: 自适应";
				break;
			case 3:
				//必须为大于0的整数
				var regexp = /^[1-9]\d*$/;
			    if (!regexp.test($('#hours').val())) {
			    	UIToastr.showInfo('请检查保留时间', '保留时间需要是大于0的整数');
			        return false;
			    }
				logshow = "按时间保留日志," + " 保留时间: " + $('#hours').val() + " 小时";
				break;
		}
		
		$('.logshow').html(logshow);
		
		configData.highInfo.log = {
				logmode : $('#logtype').val(),
				maxstep : $('#recoverystep').val(),
				maxsize : $('#logsize').val(),
				maxhour : $('#hours').val(),
				sizeunits : $('#logsizeunits').val()
		};
		
		//接管
		var takeover = {};
		takeover.check = $('#takeovercheck').bootstrapSwitch('state');
		takeover.type = $('#takeovertype').val();
		takeover.step = $('#takeoverstep').val();
		takeover.network = {};
		takeover.service = [];
		if(takeover.check){
			takeover.network = networkInfo;
			takeover.service = serviceGrid.getSelectedRows();
		}
		
		configData.highInfo.takeover = takeover;
		
		//检查接管配置,如果开启了接管,连接次数大于等于1,必须配置接管网卡
		if(takeover.check){
			//检查连接次数
			if(1 > parseInt(takeover.step)){
				UIToastr.showInfo('配置业务接管', '请检查业务接管连接次数,连接次数必须不小于1次');
				return false;
			}
			//检查接管网卡
			var networkNum = 0;
			var network = networkInfo.standbyNetwork;
			for(var i=0; i<network.length; i++){
				if(network[i].takeover){
					networkNum += network[i].takeover.length;
				}
			}
			if(0 == networkNum){
				UIToastr.showInfo('配置业务接管', '请检查业务接管网卡,备份主机业务接管时,接管的生产主机网卡');
				return false;
			}
		}
		
		var takeovershow = '';
		if(configData.highInfo.takeover.check){
			takeovershow = getSwitchDes(true) + ", " + $('#takeoverstep').val() + "次失败后,启动接管";
		}else{
			takeovershow = getSwitchDes(false);
		}
		
		takeovershow += getTakeoverShowInfo(takeover);
		$('.takeovershow').html(takeovershow);
	}
	
	//获取接管第四步的确认信息,先简单点
	var getTakeoverShowInfo = function(takeover){
		var str = "";
		if(!takeover.check) return str;
		var network = networkInfo.standbyNetwork;
		var networkNum = 0;
		for(var i=0; i<network.length; i++){
			if(network[i].takeover){
				networkNum += network[i].takeover.length;
			}
		}
		str += "<br>" + "接管网卡: " + networkNum + "个" + "<br>";
		var serviceNum = takeover.service.length;
		str += "接管自动启动服务: " + serviceNum + "个" + "<br>";
		return str;
	}
	
	//得到开关的结果描述   开启/关闭
	var getSwitchDes = function(check){
		if(check){
			return LANG.UI_PUBLIC_ON;
		}
		return LANG.UI_PUBLIC_OFF;
	}
	
	var initSpinner = function(){
        $('.spinnerNum').spinner({value:100, step: 10, min: 0, max: 10000});
        $('.spinnertakeover').spinner({value:5, step: 10, min: 1, max: 1000});
	}
	
	
	//初始化旧的任务信息
	var initOldSettings = function(){
		var data = {};
		data.taskuuid = $('#task_uuid').val();
		data = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.DBCDP,f:'getBackupTaskAllInfo',p:data}, function(d){
			SETTINGS = JSON.parse(d);
			//检查生产主机和备份主机是否在线,如果不在线,直接提示返回
			if(1 != SETTINGS.producthostInfo.hoststatus){
				var tips = '生产主机' + SETTINGS.producthostInfo.hostname + "(" + SETTINGS.producthostInfo.hostip + ")" + '不在线,请检查环境后重试!'
				$('#producthost').prop("disabled","disabled");
				return UIToastr.showWarning('生产主机离线', tips);
			}
			if(1 != SETTINGS.standbyhostInfo.hoststatus){
				$('#producthost').prop("disabled","disabled");
				var tips = '备份主机' + SETTINGS.standbyhostInfo.hostname + "(" + SETTINGS.standbyhostInfo.hostip + ")" + '不在线,请检查环境后重试!'
				return UIToastr.showWarning('备份主机离线', tips);
			}
			initStep1Settings();
			initStep2Settings();
			initStep3Settings();
		});
	}
	
	//设置第一步数据
	var initStep1Settings = function(){
		//设置生产主机,并禁用
		$('#producthost').val(SETTINGS.producthostInfo.hostuuid).prop("disabled","disabled");
		productHostChange();
		//设置数据库类型,并禁用
		$('#dbtype').val(SETTINGS.producthostInfo.dbtype).prop("disabled","disabled");
		dbtypeChange();
	}
	
	//设置第二步数据
	var initStep2Settings = function(){
		backupHostIsServer = SETTINGS.standbyhostInfo.backupHostIsServer;
		//设置备份主机,并禁用
		$('#standbyhost').val(SETTINGS.standbyhostInfo.standbyhost).prop("disabled","disabled");
		standbyHostChange();
		//设置备份类型
		$('#backuptype').val(SETTINGS.standbyhostInfo.backuptype);
		backuptypeChange();
		//设置备份数据目录和历史数据目录
		$('#backupdir').val(SETTINGS.standbyhostInfo.backupdir);
		$('#historydir').val(SETTINGS.standbyhostInfo.historydir);
		//设置历史数据保留分数
		$('#historycopys').val(SETTINGS.standbyhostInfo.historycopys);
	}
	
	//设置第三步数据
	var initStep3Settings = function(){
		//设置日志
		var logInfo = SETTINGS.highInfo.log;
		$('#logtype').val(logInfo.logmode);
		$('#hours').val(logInfo.maxhour);
		$('#recoverystep').val(logInfo.maxstep);
		$('#logsize').val(logInfo.maxsize);
		$('#logsizeunits').val(logInfo.sizeunits);
		logtypeChange();
		//设置接管
		var takeover = SETTINGS.highInfo.takeover;
		$('#takeovercheck').bootstrapSwitch('state', takeover.check);
		$('#takeovertype').val(takeover.type);
		$('#takeoverstep').val(takeover.step);
		
		//设置任务名
		$('#jobname').val(SETTINGS.jobname);
	}
	

    return {
        //main function to initiate the module
        init: function () {
        	wizardInit();
        	initSpinner();
        	initListener();
        }

    };

}();


jQuery(document).ready(function() {    
	DbCDPEDIT.init();
});