var addAgentGroup = function(){
	var agentTree;
	
	//加载代理端树
	var initAgentTree = function(){
		$.post(CONF.AJAXPATH,{m:CONF.M.AGENT, f:"getAgentSelectTree", p:{}},setTree);
	}
	
	 //初始化代理树
    var setTree = function(zNodes){
    	if(zNodes == "[]"){
			$("#noagenttips").show();
			$('.agentDiv').hide();
			return false;
		}else{
			$("#noagenttips").hide();
			$('.agentDiv').show();
		}
    	var setting = {
				check: {
					enable: true,
					nocheckInherit: false,
					chkStyle: "checkbox",
					chkboxType: { "Y": "ps", "N": "ps" }
				},
				data: {
					simpleData: {
						enable: true,
					},
					key: {
						title: "title"
					}
				},
				view: {
					showIcon : false,
					nameIsHTML: true
				},
				callback: {
					beforeClick: nodeSelect,
				}
			};
		
		agentTree = $.fn.zTree.init($("#agentTree"), setting, JSON.parse(zNodes));
    }
	
    var nodeSelect = function(treeId, treeNode, clickFlag){
    	$.fn.zTree.getZTreeObj(treeId).checkNode(treeNode, !treeNode.checked, true, true);
    }
    
	
	//添加代理分组表单验证
    var handleValidation = function() {

        var form2 = $('#form_addAgentGroup');
        var error2 = $('.alert-danger', form2);
        var success2 = $('.alert-success', form2);
        
        form2.validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "",  // validate all fields including form hidden input
            rules: {
            	groupname: {
                    required: true,
                    groupnameAvailable: true,
                },
                description: {
                	required: false
                }
            },

            invalidHandler: function (event, validator) { //display error alert on form submit              
                success2.hide();
                error2.show();
                Metronic.scrollTo(error2, -200);
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
                error2.hide();
            }
            
        });
        
   	 
   	 	//提交添加角色表单
        $("#submitBut").click(function(){
        	if (form2.validate().form()) {
        		submit();
            }
        });
        
        //取消返回资源组列表
        $("#cancelBut").click(function(){
        	LOCATION('./content/platform/agent/agent.php?tab=2','client');
        });
    }
    
    $.validator.addMethod(
        	"groupnameAvailable",
        	function(value, element, param) {
        		var data = JSON.stringify({name:value});
        		var result = false;
        		$.ajax({ 
        			type: "post", 
        	        url: CONF.AJAXPATH, 
        	        async:false, 
        	        data:{m:CONF.M.AGENT,f:'groupnameAvailable',p:data},
        	        success: function(data){ 
        	        	result = JSON.parse(data);
        	        } 
        		});
    	    	return result;
    	    },
        	LANG.UI_AGENT_GROUP_NAME_EXIST_TIPS
        );
    
    //提交添加资源组表单
    var submit = function(){
    	var data = {};
    	var nodes = agentTree.getCheckedNodes(true);
    	if(nodes.length ==0){
    		 return UIToastr.showInfo(LANG.UI_AGENT_GROUP_ADD, LANG.UI_AGENT_GROUP_ADD_NO_AGENT_TIPS);
    	}
    	var agentList = [];
    	for(var i=0;i<nodes.length;i++){
    		if(nodes[i].type == 1){
    			agentList.push(nodes[i].agentuuid);
        	}
    	}
    	data.groupname = $('#groupname').val();
    	data.description = $('#description').val();
    	data.agentList = agentList;
    	var p = JSON.stringify(data);
    	$.post(CONF.AJAXPATH,{m: CONF.M.AGENT, f:"addAgentGroup", p: p}, function(d){
    		if(OPREL(d)){
    			LOCATION('./content/platform/agent/agent.php?tab=2','client');
    		}
    	});
    }
    
    //初始化分组名
    var initGroupName = function(){
    	$.post(CONF.AJAXPATH,{m:CONF.M.AGENT, f:"getAgentGroupName", p:{}},function(d){
    		var jsonData = JSON.parse(d);
    		$('#groupname').val(jsonData.groupname);
    	});
    }
    
    var addListeners = function(){
    	
	}
	
	return {
		init: function(){
			initGroupName();
			initAgentTree();
			handleValidation();
			addListeners();
		}
	}
}();

jQuery(document).ready(function(){
	addAgentGroup.init();
})