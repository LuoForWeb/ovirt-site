var FileCDPRecovery = function () {
	
	var srcfileTree, distfileTree;
	var initListener = function(){
		initStandbyHostList();
		$('#standbyhost').on('change', standbyHostChange);
		$('#recoveryhost').on('change', productHostChange);
		
		initJobName();
		
		handleValidation();
	}
	
	//备份源主机改变
	var standbyHostChange = function(){
		var data = {};
		data.hostuuid = this.value;
		data.dir = "";
		data.page = 0;
		data.limit = 20;
		data.pid = 0;
		if("" == data.hostuuid) return;
		data = JSON.stringify(data);
    	$.post(CONF.AJAXPATH, {m:CONF.M.FILECDP,f:'getRecoveryTaskTree',p:data}, function(d){
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
        						rootPId: 0,
        					},
        					key: {
        						title: "title"
        					}
        				},
        				callback: {
        					beforeClick: nodeSelect,
//        					onCheck: pathOnCheck,
        					beforeExpand: fileNodeExpand,
        				}
        			};
        		srcfileTree = $.fn.zTree.init($("#file_tree"), setting, data.tree);
        		if(undefined !== data.tree){
        			$('#srcfilediv').show();
        			$('#disthostdiv').show();
        			
        			
        		}else{
        			$('#srcfilediv').hide();
        			$('#disthostdiv').hide();
        		}
        		
        		$('#srcfilediv').show();
        		$('#disthostdiv').show();
        		
        		
        		initRecoveryHostList();
    		}else{
    			OPREL(d);
    			$('#srcfilediv').hide();
    			$('#disthostdiv').hide();
    		}
    		
    	});
	}
	
	var nodeSelect = function(treeId, treeNode, clickFlag){
		if(treeNode.more){
			// 显示更多
			var hostuuid = $('#standbyhost').val();
			if(!hostuuid)	return false;
			var parentNode = treeNode.getParentNode();
			var checkStatus = parentNode.getCheckStatus();
			var parentCheck = false;
			if(checkStatus.checked && !checkStatus.half){
				//如果父节点选中
				parentCheck = true;
			}
			var p = {hostuuid:hostuuid, page:treeNode.page, limit:20, dir:treeNode.dir, pid:treeNode.pid, checked:parentCheck};
			p = JSON.stringify(p);
			Metronic.blockUI({target: '#file_tree',animate: true});
			$.ajax({ 
				type: "post", 
		        url: CONF.AJAXPATH, 
		        async:true, 
		        data:{m:CONF.M.FILECDP,f:'getHostFileTree',p:p},
		        success: function(data){ 
		        	Metronic.unblockUI('#file_tree');
		        	result = JSON.parse(data);
		        	if(result.re){
		        		//success
		        		srcfileTree.removeNode(treeNode);
		        		srcfileTree.addNodes(treeNode.getParentNode(), result.tree, true);
		        	}else{
		        		OPREL(data);
		        	}
		        } 
			});
		}else{
			srcfileTree.checkNode(treeNode, !treeNode.checked, true, true);
		}
		
		if(treeNode.isParent){
			//如果是目录,展开
			fileNodeExpand(treeId, treeNode);
			srcfileTree.expandNode(treeNode, true);
		}
	}
	
	//路径展开
	var fileNodeExpand = function(treeId, treeNode){
		if(treeNode.children) return true;
		var hostuuid = $('#standbyhost').val();
		if(!hostuuid)	return false;
		var p = {hostuuid:hostuuid, page:0, limit:20, dir:treeNode.dir, pid:treeNode.id, checked:treeNode.checked};
		p = JSON.stringify(p);
		Metronic.blockUI({target: '#file_tree',animate: true});
		$.ajax({
			type: "post", 
	        url: CONF.AJAXPATH, 
	        async:true, 
	        data:{m:CONF.M.FILECDP,f:'getHostFileTree',p:p},
	        success: function(data){
	        	Metronic.unblockUI('#file_tree');
	        	result = JSON.parse(data);
	        	if(result.re){
	        		//success
	        		srcfileTree.addNodes(treeNode, result.tree, true);
	        	}else{
	        		OPREL(data);
	        	}
	        } 
		});
	}
	
	
	var handleValidation = function() {
        var filerecoveryform = $('#filerecoveryform');

        filerecoveryform.validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "",  // validate all fields including form hidden input
            rules: {
            	standbyhost:{
                	required: true,
                },
                recoveryhost:{
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
        	if (filerecoveryform.validate().form()) {
        		submitCheck();
            }
		});
		
		//取消按钮事件
		$('#cancelbtn').on('click',function(){
	    	LOCATION('./content/fs/filecdprecovery.php', 'filedataRecovery');
		});
        
	};
	
	var submitCheck = function(){
		//检查恢复源文件/文件夹
		var srcNodes = srcfileTree.getCheckedNodes();
		if(!srcNodes.length){
			return UIToastr.showWarning("请选择恢复文件/文件夹", "请选择您需要恢复的文件或文件夹");
		}
		//检查恢复目的文件夹
		var distNodes = distfileTree.getCheckedNodes();
		if(!distNodes.length){
			return UIToastr.showWarning("请选择目的文件夹", "请选择您需要恢复的文件或文件夹");
		}
		//获取恢复源文件/文件夹
		var srcFileInfo = [], pathList = [];
		for(var i=0;i<srcNodes.length;i++){
			var checkStatus = srcNodes[i].getCheckStatus();
			if(!checkStatus.half){
				//只要全选的
				var data = {};
				data.path = srcNodes[i].dir;
				data.isParent = srcNodes[i].isParent;
				data.name = srcNodes[i].name
				srcFileInfo.push(data);
				pathList.push(srcNodes[i].dir);
			}
		}
		
		var data = {};
		//源主机名,目的主机名,数据库类型,实例名,数据库名,时间点      需要记录到数据库,主要用作恢复任务详情显示
		data.standbyhostname = $('#standbyhost').find("option:selected").text();
		data.recoveryhostname = $('#recoveryhost').find("option:selected").text();
		data.standbyhostuuid = $('#standbyhost').val();
		data.recoveryhostuuid = $('#recoveryhost').val();
		data.jobname = $('#jobname').val();
		data.srcFileInfo = srcFileInfo;
		data.pathList = pathList;
		data.distDir = distNodes[0].dir;
		
		data = JSON.stringify(data);
		Metronic.blockUI({target: '#recovercontent',animate: true, cenrerY: true,});
		$.post(CONF.AJAXPATH, {m:CONF.M.FILECDP,f:'createRecoveryJob',p:data}, function(d){
			Metronic.unblockUI('#recovercontent');
    		if(OPREL(d)){
    			LOCATION('./content/platform/jobs/jobs.php', 'task');
        	}
		});
	}
	
	//初始化任务名
	var initJobName = function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.FILECDP,f:'getRecoveryTaskName',p:{}}, function(d){
			$('#jobname').val(d);
		});
	}
	
	//初始化可以用做恢复的主机
	var initRecoveryHostList = function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.DBCDP,f:'gettRecoveryHostInfo',p:{}}, function(d){
    		var data = JSON.parse(d);
    		if(!data.length) return;
			var recoveryhost = $('#recoveryhost');
			var standbyhost = $('#standbyhost').val();
			recoveryhost.empty();
			var option = $("<option>").text('').val('');
			recoveryhost.append(option);
			for(var i=0;i<data.length;i++){
				if(standbyhost == data[i].uuid){
					//排除备份主机,不能恢复到自己
					continue;
				}
				var option = $("<option>").text(data[i].value).val(data[i].uuid);
				recoveryhost.append(option);
			}
    	});
	}
	
	
	//目标主机选择改变
	var productHostChange = function(){
		var data = {};
		data.hostuuid = this.value;
		data.dir = "";
		data.page = 0;
		data.limit = 20;
		data.pid = 0;
		data.dirFlag = true;
		if("" == data.hostuuid) return;
		data = JSON.stringify(data);
    	$.post(CONF.AJAXPATH, {m:CONF.M.FILECDP,f:'getHostFileTree',p:data}, function(d){
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
        						rootPId: 0,
        					},
        					key: {
        						title: "title"
        					}
        				},
        				callback: {
        					beforeClick: distNodeSelect,
        					onCheck: pathOnCheck,
        					beforeExpand: distFileNodeExpand,
        				}
        			};
        		distfileTree = $.fn.zTree.init($("#dist_tree"), setting, data.tree);
        		if(undefined !== data.tree){
        			$('.dndiv').show();
        		}else{
        			$('.dndiv').hide();
        		}
        		
        		$('.dndiv').show();
    		}else{
    			OPREL(d);
    			$('.dndiv').hide();
    		}
    		
    	});
    	
	}
	
	var distNodeSelect = function(treeId, treeNode, clickFlag){
		if(treeNode.more){
			// 显示更多
			var hostuuid = $('#recoveryhost').val();
			if(!hostuuid)	return false;
			var p = {hostuuid:hostuuid, page:treeNode.page, limit:20, dir:treeNode.dir, pid:treeNode.pid, dirFlag:treeNode.dirFlag};
			p = JSON.stringify(p);
			Metronic.blockUI({target: '#dist_tree',animate: true});
			$.ajax({ 
				type: "post", 
		        url: CONF.AJAXPATH, 
		        async:true, 
		        data:{m:CONF.M.FILECDP,f:'getHostFileTree',p:p},
		        success: function(data){ 
		        	Metronic.unblockUI('#dist_tree');
		        	result = JSON.parse(data);
		        	if(result.re){
		        		//success
		        		distfileTree.removeNode(treeNode);
		        		distfileTree.addNodes(treeNode.getParentNode(), result.tree, true);
		        	}else{
		        		OPREL(data);
		        	}
		        } 
			});
		}else{
			distfileTree.checkNode(treeNode, !treeNode.checked, false, true);
		}
		
		if(treeNode.isParent){
			//如果是目录,展开
			distFileNodeExpand(treeId, treeNode);
	    	distfileTree.expandNode(treeNode, true);
		}
	}
	
	//选中路径
	var pathOnCheck = function(e, id, node){
		//单选
		var allNodes = distfileTree.getCheckedNodes(true);
		for(var i = 0; i < allNodes.length; i++){
			distfileTree.checkNode(allNodes[i], false, false, false);	
		}
		distfileTree.checkNode(node, true, false, false);	
	}
	
	//路径展开
	var distFileNodeExpand = function(treeId, treeNode){
		if(treeNode.children) return true;
		var hostuuid = $('#recoveryhost').val();
		if(!hostuuid)	return false;
		var p = {hostuuid:hostuuid, page:0, limit:20, dir:treeNode.dir, pid:treeNode.id, dirFlag:treeNode.dirFlag};
		p = JSON.stringify(p);
		Metronic.blockUI({target: '#dist_tree',animate: true});
		$.ajax({
			type: "post", 
	        url: CONF.AJAXPATH, 
	        async:true, 
	        data:{m:CONF.M.FILECDP,f:'getHostFileTree',p:p},
	        success: function(data){
	        	Metronic.unblockUI('#dist_tree');
	        	result = JSON.parse(data);
	        	if(result.re){
	        		//success
	        		distfileTree.addNodes(treeNode, result.tree, true);
	        	}else{
	        		OPREL(data);
	        	}
	        } 
		});
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
	FileCDPRecovery.init();
});