;(function($){
	 $.fn.osRecoveryConfig = function(options){
		 var defaults = {
//				//选择时间点磁盘信息
//				 'oldList':[{'os_uuid':'123osuuid',//时间点uuid
//			         'os_name':'windows',//时间点名称
//			         'os_str' :'(192.168.26.10)2012-10-28 14:30:00',
//		             'diskInfo':[['uuid1','A盘','300','1','3MB'],//时间点磁盘详情[uuid,name,size,boot(是否是系统盘1是2不是)]
//				                 ['uuid2','B盘','200','2','3MB'],
//				                 ['uuid3','C盘','200','2','3MB'],]
//			         },
//			         {'os_uuid':'123456osuuid',
//			          'os_name':'windows',//时间点名称
//			          'os_str' :'(192.168.26.11)2012-07-23 14:30:00',
//		             'diskInfo':[['uuid1','D盘','300','1','3MB'],
//				                 ['uuid2','E盘','200','2','3MB'],]
//			         }
//			         ],
//		         //选择代理主机信息
//				 'newList':[{'agent_uuid':'123456agentuuid',//代理主机uuid
//					         'agent_name':'192.168.54.60',
//					         'builtFlag':false,//重建分区 true重建分区 false不重建分区
//			                 'diskInfo':[['uuid1','G盘','500','1','3MB'],//代理主机分区信息
//							             ['uuid2','H盘','500','2','3MB'],
//							             ['uuid3','I盘','200','2','3MB'],],
//							  'diskInfo2':[['uuid4','磁盘0','600','1','3MB'],//代理主机磁盘信息
//							               ['uuid5','磁盘1','200','2','3MB'],],
//	                        },
//	                        {'agent_uuid':'789agentuuid',
//	                         'agent_name':'192.168.1.5',
//					         'builtFlag':true,
//			                 'diskInfo':[['uuid1','J盘','500','1','3MB'],
//							             ['uuid2','K盘','500','2','3MB'],
//							             ['uuid3','L盘','200','2','3MB'],],
//							  'diskInfo2':[['uuid4','磁盘3','600','1','3MB'],
//							               ['uuid5','磁盘4','200','2','3MB'],],
//	                        }
//	                        ],
		 }
		 var thisOption = $.extend(defaults,options);
		 var Alldiv = '';//最终DIV
		 var osDiv = '';
		 var timeSelect = '';//时间点的option
		 
		 //得到表格里时间点的选择选项
		 for(var i=0;i<defaults.oldList.length;i++){
			 timeSelect += '<option value="'+i+'">'+ defaults.oldList[i].os_str +'('+ defaults.oldList[i].os_name+')'+'</option>'
		 }
		 
	 	var getAlldiv = function(ii){
	 		var oneNewInfo = defaults.newList[ii];
	 		//得到初始化时重建分区的状态
	 		var builtFlag = oneNewInfo.builtFlag;
	 		var StrBult = '';
	 		var KlugeDisabled = '';
	 		if(builtFlag){
	 			StrBult = 'checked';
	 			// KlugeDisabled = 'disabled';
	 		}
	 		//由于不管选择几个时间点 默认都是选择第一个,所以异机恢复初始化以第一个时间点为准
	 		var timepoint_os_type = defaults.oldList[0].os_type;
	 		var display_repair_linux = '';
	 		if(timepoint_os_type == 1){
	 			//如果是windows则不显示其按钮
	 			display_repair_linux = "display-none";
	 		}
	 		
	 		Alldiv +='<div class="panel panel-default">' + 
					 	'<div class="panel-heading">' + 
							'<h4 class="panel-title">' + 
							  '<a class="accordion-toggle accordion-toggle-styled" name="' + oneNewInfo.agent_uuid + '" ' + 
							   'data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent="#' + 
							   'osTable' + '" href="#specialDiv' +ii + '">' + 
							   '<i class="fa fa-desktop font-green-seagreen"></i> <span class="font14">' +LANG.UI_OS_PLUG_RECOVERY_GOAL_IP+':' +oneNewInfo.agent_name + '</span></a>' + 
							'</h4>' + 
						'</div>' + 
						'<div id="specialDiv' + ii + '" class="configsdiv panel-collapse collapses collapse in"' + 
							'<div class="panel-body">' + 
								'<div class="tabbable-custom ">' + 
									'<div class="tab-content" style="border: 0px;">' + 
											'<div class="form-group">' +
												'<label class="control-label col-md-2 compresslabel form-group-label col-md-2_en" style="width:100px;text-align:left;padding-left:0;">'+LANG.UI_OS_PLUG_REBUILT_VOL+'</label>' +
												'<div class="col-md-2 form-group-content">' +
													'<input type="checkbox" '+StrBult+' name="BuiltSize" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info"' +
													'data-on-text="' +LANG.UI_PUBLIC_ON_ONE +'"'+
													'data-off-text="'+LANG.UI_PUBLIC_OFF_ONE+'"'+'>' +
												'</div>' +
												
												'<div class="col-md-2 mt5">' +
												'<a class="popovers" data-container="body" data-trigger="hover" data-placement="right" data-content="'+
												LANG.UI_OS_PLUG_REBUILT_VOL_TIPS+'">'+
												'<i class="viconfont vicon-tishi"></i>'+
												'</a>'+
											'</div>'+
											'</div>' +
											
											
											'<div class="form-group '+ display_repair_linux +'">' +

											'<label class="control-label col-md-2 compresslabel form-group-label col-md-2_en" style="width:100px;text-align:left;padding-left:0;">'+LANG.UI_OS_PLUG_REPAIR_DIFF_MACHINE+'</label>' +

											'<div class="col-md-2 form-group-content">' +
												'<input type="checkbox" checked name="repairLinux" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" ' + KlugeDisabled + " "+
												'data-on-text="' +LANG.UI_PUBLIC_ON_ONE+'"'+
												'data-off-text="'+LANG.UI_PUBLIC_OFF_ONE+'">' +
											'</div>' +
											
											'<div class="col-md-2 mt5">' +
												'<a class="popovers" data-container="body" data-trigger="hover" data-placement="right" data-content="'+
												LANG.UI_OS_PLUG_REPAIR_LINUX_TIPS+'">'+
												'<i class="viconfont vicon-tishi"></i>'+
												'</a>'+
											'</div>'+
											'</div>' +
											
											
											
											
											
											'<div class="form-group host_select_ip  display-none">' +
												'<label class="control-label col-md-3" style="text-align: left;width: 15%;">'+LANG.UI_OS_PLUG_CHOOSE_TIMEPOINT+' <span class="required">' +
												'* </span>' +
												'</label>' +
												'<div class="col-md-6 tree_div2">' +
													'<select class="form-control select2me" name="selectAgent">' +
													     timeSelect +
													'</select>' +
												'</div>' +
											'</div>' +
											'<div id="specialTable'+ ii +'">' +
											getTable(ii,0,oneNewInfo.builtFlag) +
											'</div>' +
									'</div>' + 
								'</div>' + 
							'</div>' + 
						'</div>' + 
					'</div>';
				 	}
		 	
	 	
	 	
	 	//ii表示目标主机IP数据索引值,jj表示时间点所在数据的索引值,bulitFlag表示初始化的值
	 	var getTable = function(ii,jj,builtFlag){
	 		var oneNewInfo = defaults.newList[ii];
	 		var tableDiv = '';
	 		//拼接头部
	 		tableDiv +='<table class="table table-striped table-bordered table-advance table-hover table-tac getHoststorage">'
	            + '<thead>'
				+	'<tr>'
				+		'<th width="10%">'
				+			LANG.UI_OS_PLUG_CHECK_VOL
				+		'</th>'
				+		'<th width="30%">'
				+			LANG.UI_OS_PLUG_OLD_VOL_NAME
				+		'</th>'
				+		'<th width="10%">'
				+			LANG.UI_OS_PLUG_SIZE
				+		'</th>'
				+		'<th width="30%">'
				+			LANG.UI_OS_PLUG_GOAL_RECOVERY
				+		'</th>'
				+		'<th width="20%" style="display:none">'
				+			LANG.UI_OS_PLUG_RECOVERY_OS+
								'<a class="popovers" data-container="body" data-trigger="hover" data-placement="right" data-content="'+
								LANG.UI_OS_PLUG_RECOVERY_OS_TIPS+'">'+
								'<i class="viconfont vicon-tishi"></i>'+
								'</a>'
				+		'</th>'
				+	'</tr>'
				+'</thead>'
				+'<tbody name="OSbody">'
			 //拼接内容
				//默认做成1对多的情况 如需多对多 则需要自己选择时间点信息
				+ getContentDiv(ii,jj,builtFlag);
					
				//尾部
	 		tableDiv += '</tbody>'
					 +'</table>';
				
				return 	tableDiv;
	 		
	 	}
	 
	 	//ii 表示目标主机IP的数据索引,jj表示时间点数据索引
	 var getContentDiv = function(ii,jj,builtFlag){
		 var oneNewInfo = defaults.newList[ii];
		 var selectDiv = '';
		 var oldList1 = thisOption.oldList[jj];//默认都显示第一个时间点信息
		 if(oldList1 == '') return; //如果为空则返回
		 for(var i = 0;i<oldList1.diskInfo.length;i++){
			 	//得到关于系统盘操作盘的class名称
			 	var ostype = oldList1.diskInfo[i][5];  //操作系统类型
			 	var system_flag = oldList1.diskInfo[i][3];  //是否是系统盘
			 	var iconName = "datalogo";
			 	var osflag = ''; //得到恢复引导勾选框
		        //先判断操作类型
		        if(ostype == 1 || ostype == "Windows" || ostype == "windows"){
		            if(system_flag == 1){
		            	osflag = '<input type="checkbox" name="OSguide" class="icheck" checked="checked">';
		                iconName = "oslogo";
		            }else{
		            	osflag = '';
		                iconName = "datalogo";
		            }
		        }else{
		            if(system_flag == 1){
		            	osflag = '<input type="checkbox" name="OSguide" class="icheck" checked="checked">';
		                iconName = "linuxlogo";
		            }else{
		            	osflag = '';
		                iconName = "datalogo";
		            }
		        }
			 
			 
//			 	//判断是否是系统盘
//				var osflag = '';
//				var classosflag = '';
//				if(oldList1.diskInfo[i][3] == 1){
//					osflag = '<input type="checkbox" name="OSguide" class="icheck" checked="checked">';
//					classosflag = 'oslogo';
//				}else{
//					osflag = '';
//					classosflag = 'datalogo'
//				}
				selectDiv += '<tr>'
				+ '<td class="">'
				+	'<input type="checkbox" name ="OSchecked" class="icheck" checked="checked" value="'+i+'">'
				+'</td>'
				+'<td class="OSname">'
				+   '<span  class="'+ iconName +'"></span>'
				+	'<span name="diskname">'
				+		oldList1.diskInfo[i][1]
				+	'</span>'
				+'</td>'
				+'<td class="OSsize">'
				+	oldList1.diskInfo[i][4]
				+'</td>'
				+'<td class="OSgoal">'
				+	'<select class="form-control input-sm" name="hoststorage">'
				+		getOldSelectOp(ii,builtFlag)
				+	'</select>'
				+'</td>'
				+'<td  style="display:none">'
			    +	osflag
				+'</td>'
			    +'</tr>'
			 
		 }
		return selectDiv;
	 }
		 	
	 var getOldSelectOp = function(ii,builtFlag){
		 var oneNewInfo = defaults.newList[ii];
		 var selectDivOp = '<option value="-1">'+LANG.UI_OS_PLUG_PLEASE_CHOOSE_GOAL+'</option>';
		 //得到恢复目标存储的选项
		 if(!builtFlag){ //不重建分区 载入diskInfo
			 for(var i=0; i<oneNewInfo.diskInfo.length;i++){
				 var useStr =oneNewInfo.diskInfo[i][1]+'('+LANG.UI_OS_PLUG_TOTAL_SIZE+': '+oneNewInfo.diskInfo[i][4]+')';
				 selectDivOp += '<option value="'+i+'">'+useStr+'</option>';
			 }
		 }else{
			 for(var i=0; i<oneNewInfo.diskInfo2.length;i++){
				 var useStr =oneNewInfo.diskInfo2[i][1]+'('+LANG.UI_OS_PLUG_TOTAL_SIZE+': '+oneNewInfo.diskInfo2[i][4]+')';
				 selectDivOp += '<option value="'+i+'">'+useStr+'</option>';
			 }
		 }
		 
		return selectDivOp;
	 }
		 	
		 	
		 	
		 return this.each(function(){
			 var _this = $(this);
			 for(var i = 0;i<defaults.newList.length;i++){
				 getAlldiv(i)
			 }
			 
			  _this.empty().html(Alldiv);
			//初始化poppvers
				 _this.find('.popovers').popover();
			//初始化ickeck
			  _this.find('.icheck').iCheck({
		    	    checkboxClass: 'icheckbox_square-blue',
		    	    radioClass: 'iradio_square-blue',
//		    	    increaseArea: '20%' // optional
		      });
			  
			  _this.find('input[name=OSchecked]').on('ifToggled',function(){
				  //目前只有1个 ,如果有多个 这还要用specialDiv0去判断第几个数据
				  //得到所有数据
				  var allList = defaults.oldList[0].diskInfo;
				  //得到数据
				  var diskinfoList = defaults.oldList[0].diskInfo[$(this).val()];
				  //得到linux还是windows
				  var os_type= diskinfoList[5];
				  if(os_type != "Linux"){
					  return;
				  }
				  var osIndex = "";
				  var bootIndex = "";
				  
				  //遍历所有数据 找出系统盘和"/boot"分区
				  for(var i in allList){
					  if(allList[i][3] == 1){
						  osIndex = i;
					  }
					  if(allList[i][6] == "/boot"){
						  bootIndex = i;
					  }
				  }
				  if(osIndex == "" || bootIndex == ""){
					  //如果有一个不存在则退出
					  return;
				  }
				  //得到勾选状态
				  var checkflag = $(this).get(0).checked;
				  //得到mount路径
				  var mount_path = diskinfoList[6];
				  //得到是否是系统分区
				  var os_system = diskinfoList[3];
				  //得到所有的选择框
				  var objCheckList = $("#osTable").find('input[name=OSchecked]');
				  //判断是否点击的系统分区
				  if(os_system == 1){ //如果点击的是系统分区
					  //先判断是勾选还是取消
					  //如果是取消则不管 如果是勾选则连同"/boot"分区也要一起勾选
					  if(!checkflag){
						  return
					  }
					  //如果是勾选 则要把"/boot"一起勾选
					  $(objCheckList[bootIndex]).iCheck('check'); 
				  }else{
					  //如果点击的非系统分区 则判断是否点击了"/boot"分区
					  if(mount_path == "/boot"){
						  //如果点击了boot分区
						  if(checkflag){
							  //如果是选中  则不管
							  return
						  }else{
							  //如果是取消boot分区 则同步取消系统分区
							  $(objCheckList[osIndex]).iCheck('uncheck'); 
						  }
					  }
				  }
			  })
			  
			  
			    //初始化开关按钮
			  _this.find('input[name=repairLinux]').bootstrapSwitch({});
			  
			  //初始化开关按钮
			  _this.find('input[name=BuiltSize]').bootstrapSwitch({
				  onSwitchChange: function (event, state) {
					  //获取父节点
					  var parentdiv = $(this).parents("[id^='specialDiv']");
					  //获取父节点的id
					  var parentid = parentdiv.attr('id');
					  //获取父级div的索引值
					  var indexOfagent = parentid.slice(10);
					  var selectOfDiskObjList = parentdiv.find("[name='hoststorage']");
					  selectOfDiskObjList.empty().html(getOldSelectOp(indexOfagent,state));
					  
					  //如果时间点系统类型为linux时 如果重建分区开, 则异机修复也要自动打开,其他情况不影响异机修复开关
					  //得到所选时间点的系统类型
					  //得到所选时间点的索引值
					  var selectTimepoint = parentdiv.find("[name='selectAgent']");
					  var selectTimepointVal = $(selectTimepoint).val();
					  //得到所选时间点的系统类型
					  var oldselectInfo = defaults.oldList[selectTimepointVal].os_type;
					  var statusOfBuitFlag = parentdiv.find('input[name=BuiltSize]').bootstrapSwitch('state');
					  // if(oldselectInfo == 2){
						//   //如果为linux 则打开重建分区开关则同步打开异机修复开关
						//   if(statusOfBuitFlag){
						// 	  parentdiv.find('input[name=repairLinux]').bootstrapSwitch('state', true);
						//   }
					  // }
					  // //如果重建分区开启 则禁用异机修复
					  // if(statusOfBuitFlag){
						//   parentdiv.find('input[name=repairLinux]').bootstrapSwitch('disabled', true);
					  // }else{
						//   parentdiv.find('input[name=repairLinux]').bootstrapSwitch('disabled', false);
					  // }
				  	}
			  });
			  
			
			  
			  
			  //获取所有select  当改变时间点时重新绘制表格
			  $("#osTable").find('select[name="selectAgent"]').on('change',function(){
				  //获取父节点
				  var parentdiv = $(this).parents("[id^='specialDiv']");
				  //获取父节点的id
				  var parentid = parentdiv.attr('id');
				  //获取父级div的索引值
				  var indexOfagent = parentid.slice(10);
				  //获取父级选择的时间点值
				  var indexOfos = $(this).val();
				  //获取父级中的重建分区状态
				  var statusOfBuit = parentdiv.find('input[name=BuiltSize]').bootstrapSwitch('state');
				  //获取父级div中的表格 根据所选择时间点重绘表格
				  var tableDiv = parentdiv.find("[id^='specialTable']");
				  tableDiv.empty().html(getTable(indexOfagent,indexOfos,statusOfBuit));
				  //绘制表格后初始化插件
				//初始化ickeck
				  _this.find('.icheck').iCheck({
			    	    checkboxClass: 'icheckbox_square-blue',
			    	    radioClass: 'iradio_square-blue',
			      });
			  });
			  
			  //获取所有恢复目标,当改变恢复目标后重新载入选择项 排除已经选择了的
			  $("#osTable").find('select[name="hoststorage"]').on('change',function(){
				  //获取父节点
				  var parentdiv = $(this).parents("[id^='specialDiv']");
				  var statusOfBuitCheck = parentdiv.find('input[name=BuiltSize]').bootstrapSwitch('state');
				  //如果是重建分区则不作限制
				  if(statusOfBuitCheck) return;
				  
				  //先获取到所有的select
				  var opselectList = $("#osTable").find('select[name="hoststorage"]');
				  // 声明一个空数组用于存放已经选择了的值
				  var selectedList = [];
				 for(var i = 0;i<opselectList.length; i++){
					  var selectValue = $(opselectList[i]).val();
					  if(selectValue == -1){
						  continue;
					  }
					  selectedList.push(selectValue);
				  }
				  //获取到所有的select ,然后根据情况重构选择的选项
				 for(var i = 0;i<opselectList.length; i++){
					 //先把所有的option开放
					 $(opselectList[i]).find("option").removeAttr("disabled");
					 $(opselectList[i]).find("option").css("color","#333");
					 //得到所有的option
					 var optionList = $(opselectList[i]).find("option");
					 for(var x = 0;x<optionList.length; x++){
						 //得到单个option
						 var optionText = $(optionList[x]).text();
						 $(optionList[x]).text(optionText.replace('('+LANG.UI_OS_PLUG_ALREADY_CHOOSE+')',''));
					 }
					  var selectValue = $(opselectList[i]).val();
						 for(var j = 0; j<selectedList.length; j++){
							 if(selectValue == selectedList[j]){
								//得到text的值
								 var desText = $(opselectList[i]).find("option[value = '"+selectedList[j]+"']").text();
								 $(opselectList[i]).find("option[value = '"+selectedList[j]+"']").text(desText.replace('('+LANG.UI_OS_PLUG_ALREADY_CHOOSE+')',''));
								 continue;
							 }
							 $(opselectList[i]).find("option[value = '"+selectedList[j]+"']").attr("disabled","disabled");
							 $(opselectList[i]).find("option[value = '"+selectedList[j]+"']").css("color","#008457");
							 //得到text的值
							 var desText = $(opselectList[i]).find("option[value = '"+selectedList[j]+"']").text();
							 $(opselectList[i]).find("option[value = '"+selectedList[j]+"']").text(desText+"("+LANG.UI_OS_PLUG_ALREADY_CHOOSE+")");
					  }
				  }
			  });
		 });
		 
	 }
	 
	 //获取最终配置函数
	 $.fn.getosRecoveryData = function(options){
//		 var options = {
//				 //选择时间点磁盘信息
//				 'oldList':[{'os_uuid':'123osuuid',//时间点uuid
//					         'os_name':'windows',//时间点名称
//					         'os_str' :'(192.168.26.10)2012-10-28 14:30:00',
//				             'diskInfo':[['uuid1','A盘','300','1'],//时间点磁盘详情[uuid,name,size,boot(是否是系统盘1是2不是)]
//						                 ['uuid2','B盘','200','2'],
//						                 ['uuid3','C盘','200','2'],]
//					         },
//					         {'os_uuid':'123456osuuid',
//					          'os_name':'windows',//时间点名称
//					          'os_str' :'(192.168.26.11)2012-07-23 14:30:00',
//				             'diskInfo':[['uuid1','D盘','300','1'],
//						                 ['uuid2','E盘','200','2'],]
//					         }
//					         ],
//		         //选择代理主机信息
//				 'newList':[{'agent_uuid':'123456agentuuid',//代理主机uuid
//					         'agent_name':'192.168.54.60',
//					         'builtFlag':false,//重建分区 true重建分区 false不重建分区
//			                 'diskInfo':[['uuid1','G盘','500','1'],//代理主机分区信息
//							             ['uuid2','H盘','500','2'],
//							             ['uuid3','I盘','200','2'],],
//							  'diskInfo2':[['uuid4','磁盘0','600','1'],//代理主机磁盘信息
//							               ['uuid5','磁盘1','200','2'],],
//	                        },
//	                        {'agent_uuid':'789agentuuid',
//	                         'agent_name':'192.168.1.5',
//					         'builtFlag':true,
//			                 'diskInfo':[['uuid1','J盘','500','1'],
//							             ['uuid2','K盘','500','2'],
//							             ['uuid3','L盘','200','2'],],
//							  'diskInfo2':[['uuid4','磁盘3','600','1'],
//							               ['uuid5','磁盘4','200','2'],],
//	                        }
//	                        ],
//		 }
		 
		 var FinalData = {};
		 FinalData.recovery_oss_info = [];
		 FinalData.TableStr = '';
		 
		 
		 var getInfo = function(){
			//获取所有数据集合
			 var listOfObj = $("#osTable").find("[id^='specialDiv']");
			//得到表格数据
			 //得到每个的数据
			 for(var i=0;i<listOfObj.length;i++){
				 var objOfOneTable = {};
				 var obj = $(listOfObj[i]);
				 //获取每个div的id
				 var divId = obj.attr('id');
				 //获取数据的索引值
				 var indexOfagent = parseInt(divId.slice(10));
				 //获取agent的相关信息
				 var agentinfo = options.newList[indexOfagent];
				 //获取重建分区
				 var builtFlag = obj.find('input[name=BuiltSize]').bootstrapSwitch('state');
				 //获取异机修复
				 var repairLinuxFlag = obj.find('input[name=repairLinux]').bootstrapSwitch('state');
				 //得到时间点索引
				 var indexOftime = parseInt(obj.find('select[name="selectAgent"]').val());
				 //得到时间点信息
				 var timeinfo = options.oldList[indexOftime];
				 //开始装数据
				 //得到重建分区
				 objOfOneTable.reparted_flag = boolToNumber(builtFlag);
				 //得到异机修复
				 objOfOneTable.repair_linux_flag = boolToNumber(repairLinuxFlag);
				 //得到系统名称
				 objOfOneTable.os_name = timeinfo.os_name;
				//得到系统名称
				 objOfOneTable.os_timepoint_str = timeinfo.os_str;
				 //得到时间点uuid
				 objOfOneTable.recovery_timepoint_uuid = timeinfo.os_uuid;
				 //得到目标主机uuid
				 objOfOneTable.destination_agent_uuid = agentinfo.agent_uuid;
				 //得到旧的目标主机(恢复选择的时间点)的agent_uuid
				 objOfOneTable.timepoint_agent_uuid = timeinfo.agent_uuid;
				 //得到目标主机的group_uuid
				 objOfOneTable.agent_group_uuid = agentinfo.group_uuid;
				 //得到目标主机IP
				 objOfOneTable.destination_agent_ip = agentinfo.agent_name;
				 //得到恢复主机的操作系统类型
				 objOfOneTable.os_type = timeinfo.os_type;
				 //得到分区信息
				 var tableInfo = getTableInfo(obj,agentinfo,timeinfo,builtFlag);
				 if(!tableInfo){
					 return false;
				 }
				 objOfOneTable.system_recovery_flag = boolToNumber(tableInfo[1]);
				
				 //得到磁盘分区信息
				 if(builtFlag){
					 //开启重建分区
					 objOfOneTable.partition_allocation_strategy = [];
					 objOfOneTable.disk_allocation_strategy = tableInfo[0];
				 }else{
					 //关闭重建分区
					 objOfOneTable.partition_allocation_strategy = tableInfo[0];
					 objOfOneTable.disk_allocation_strategy = [];
				 }
				 
				 FinalData.recovery_oss_info.push(objOfOneTable);
			 }
			 
			 //检查数据合理性
			 var ResultCheck = checkSize(FinalData.recovery_oss_info);
			 if(!ResultCheck){
				 return false;
			 }
			 //得到表格数据
			 FinalData.TableStr = getTableStrMsg(FinalData.recovery_oss_info);
			 return FinalData;
		 }
		 
		//params 
        //obj 每个板块的div的DOM对象 
		//agentinfo每个代理主机的信息  
		//timeinfo为时间点信息  
		//builtFlag重建分区
		 var getTableInfo = function(obj,agentinfo,timeinfo,builtFlag){
			 //得到每个表格的tbody
			 var tableBody = obj.find("[name=OSbody]");
			 //得到td的list
			 var tdList = tableBody.children();
			 var allStrategy = [];
			 var osguidchecked = false;
			 for(var i=0;i<tdList.length;i++){
				 var oneStrategy = {};
				 //得到每个td
				 var tdObj = $(tdList[i]);
				//得到磁盘是否勾选
				 var oschecked = tdObj.find("input[name=OSchecked]").get(0).checked;
				 var osoldindex = parseInt(tdObj.find("input[name=OSchecked]").val());
				 if(!oschecked){  //如果没勾选则跳过
					 continue;
				 }
				 //得到时间点信息 
				 oneStrategy.source = timeinfo.diskInfo[osoldindex][0];
				 oneStrategy.source_name = timeinfo.diskInfo[osoldindex][1];
				 oneStrategy.source_size = timeinfo.diskInfo[osoldindex][2];
				 oneStrategy.source_sizeStr = timeinfo.diskInfo[osoldindex][4];
				 
				 //如果已经勾选 则得到恢复目标卷
				 var selectAgent = parseInt(tdObj.find('[name=hoststorage]').val());
				 if(selectAgent == -1){
					 UIToastr.showInfo(LANG.UI_OS_PLUG_GOAL_RECOVERY, LANG.UI_OS_PLUG_RECOVERY_OS_ERROR);
					 return false;
				 }
				//得到目标卷信息
				 if(!builtFlag){  //先看重建分区是否开启
					 //重建分区未开启
					 oneStrategy.target = agentinfo.diskInfo[selectAgent][0];
					 oneStrategy.target_name = agentinfo.diskInfo[selectAgent][1];
					 oneStrategy.target_size = agentinfo.diskInfo[selectAgent][2];
					 oneStrategy.target_sizeStr = agentinfo.diskInfo[selectAgent][4];
					 oneStrategy.system_flag = agentinfo.diskInfo[selectAgent][3];
				 }else{
					 oneStrategy.target = agentinfo.diskInfo2[selectAgent][0];
					 oneStrategy.target_name = agentinfo.diskInfo2[selectAgent][1];
					 oneStrategy.target_size = agentinfo.diskInfo2[selectAgent][2];
					 oneStrategy.target_sizeStr = agentinfo.diskInfo2[selectAgent][4];
					 oneStrategy.system_flag = agentinfo.diskInfo2[selectAgent][3];
				 }
				 
				 //得到是否有恢复引导
				 var osguid = tdObj.find('[name=OSguide]');
				 if(osguid != '' && osguid.length != 0){ //有恢复引导选项并且不为空
					//得到恢复引导是否被勾选
					 osguidchecked = tdObj.find('[name=OSguide]').get(0).checked;
				 }
				 allStrategy.push(oneStrategy);
			 }
			 //如果一个都没选
			 if(allStrategy.length == 0){
				 UIToastr.showInfo(LANG.UI_OS_PLUG_GOAL_RECOVERY, LANG.UI_OS_PLUG_GOAL_RECOVERY_ONE);
				 return false;
			 }
			 var thisList = [allStrategy,osguidchecked];
			 return thisList;
		 }
		 
		 
		 //得到一个描述 关于一些数据用表格展示
		 //主要用于前端表格直观显示
		 //list为获取到的分区情况信息
//		 "recovery_oss_info": [{
//			                   "reparted_flag": "1",
//			                   "system_recovery_flag": "1",
//			                   "os_name": "Gongquanzhi",
//		                       "os_timepoint_str": "2021-11-22 10:24:30"
//			                   "recovery_timepoint_uuid": "timepoint_uuidxxxxxxxx",
//			                   "disk_allocation_strategy": [{
//			                                 "source": "volume_uuidxxxxxxx",
//		                                     "source_name":"E:",
//		                                     "source_size": "200GB",
//			                                 "target": "disk_uuidxxxxxxx"
//		                                     "target_size": "500GB",
//		                                     "target_name":"H:"
//			                   }, {
//			                             "source": "volume_uuidxxxxxxx",
//       								 "source_size": "200GB",
//		                                 "target": "disk_uuidxxxxxxx"
//	                                     "target_size": "500GB",
//			                   }],
//			                   "partition_allocation_strategy": [{
//			                             "source": "volume_uuidxxxxxxx",
//       								 "source_size": "200GB",
//		                                 "target": "disk_uuidxxxxxxx"
//	                                     "target_size": "500GB",
//			                   }, {
//			                             "source": "volume_uuidxxxxxxx",
//       								 "source_size": "200GB",
//		                                 "target": "disk_uuidxxxxxxx"
//	                                     "target_size": "500GB",
//			                   }],
//			                   "destination_agent_uuid": "agent_uuidxxxxxxx",
//		                       "destination_agent_ip":"192.168.26.10"
//		 
//			     }]
		 var getTableStrMsg = function(list){
			 var strTable = '<div id = "tableList">';
			 //得到每一个的基本信息
			 for(var i = 0; i<list.length;i++){
				 var reparted_flag = list[i].reparted_flag; //获取到重建分区 1是是 2是否
				 var system_recovery_flag = list[i].system_recovery_flag;  //获取到恢复引导
				 var os_timepoint_str = list[i].os_timepoint_str;  //获取到时间点信息
				 var os_name = list[i].os_name;  //获取到系统名称
				 var destination_agent_ip = list[i].destination_agent_ip;  //获取到目标主机IP
				 var disk_allocation_strategy = list[i].disk_allocation_strategy;  //获取到磁盘分区信息
				 var partition_allocation_strategy = list[i].partition_allocation_strategy;  //获取到磁盘分区信息
				 if(reparted_flag == 1){ 
					 //开启
					 allocation_strategy_list = list[i].disk_allocation_strategy;
				 }else{
					 //关闭
					 allocation_strategy_list = list[i].partition_allocation_strategy;
				 }
				 
				 strTable +='<table border="1" class="form-control-static borderTableColorGreen">' +
					    		'<tr>' +
					 				'<th rowspan="0">' + LANG.UI_OS_BASIC_INFO + '</th>' +
					 			'</tr>' +
					 			'<tr>'+
					 				'<td>' + LANG.UI_RECOVERY_TIMEPOINT + ':</td>' +
					 				'<td>'+ os_name + os_timepoint_str +'</td>' +
					 			'</tr>' +
					 			'<tr>' +
					 				'<td>'+ LANG.UI_OS_RESTORE_TO_HOST +':</td>' +
					 				'<td>'+ destination_agent_ip +'</td>' +
					 			'</tr>' +
					 			'<tr>' +
								     '<td>' + LANG.UI_OS_PLUG_REBUILT_VOL + ':</td>' +
								     '<td>'+ flagToStr(reparted_flag) +'</td>' +
							    '</tr>' +
							    '<tr>' +
								     '<td>' + LANG.UI_OS_PLUG_RECOVERY_OS +':</td>' +
								     '<td>'+ flagToStr(system_recovery_flag) +'</td>' +
							    '</tr>' +
							    '<tr>' +
									'<th>' + LANG.UI_VM_SETTING_DISK_NAME +'(' + LANG.UI_OS_PLUG_TOTAL_SIZE +')</th>' +
									'<th>'+ LANG.UI_OS_RESTORE_TO +'(' + LANG.UI_OS_PLUG_TOTAL_SIZE +')</th>' +
					 			'</tr>';
				 for(var j = 0; j<allocation_strategy_list.length;j++){
					 strTable +='<tr>' +
					      			'<td>'+ allocation_strategy_list[j].source_name+'('+allocation_strategy_list[j].source_sizeStr+')' +'</td>' +
					      			'<td>'+ allocation_strategy_list[j].target_name+'('+allocation_strategy_list[j].target_sizeStr+')' +'</td>' +
						 		'</tr>';
				 }
				 strTable += '</table>';
			 }
			 strTable +='</div>';
			 return strTable;
		 }
		 
		 
		 //数据验证 验证大小是否合理 目标卷要大于备份的容量大小
		 var checkSize = function(thislist){
			 
			 //-----------检查空间大小是否分配合理---------------
			 
			 for(var i = 0; i<thislist.length;i++){
				 //得到重建分区
				 var reparted_flag = thislist[i].reparted_flag;
				 var disklist = [];
				 if(reparted_flag == 1){
					 //开启重建分区 disklist为磁盘信息
					 disklist = thislist[i].disk_allocation_strategy;
					 //先获取所有选中的磁盘uuid
					 var diskuuidList = [];
					 for(var j = 0;j<disklist.length;j++){
						 if(!diskuuidList.includes(disklist[j].target)){
							 diskuuidList.push(disklist[j].target);
						 }
					 }
					 for(var h = 0; h<diskuuidList.length;h++){
						 var thisdiskuuid = diskuuidList[h];
						 var thisAlltargetSize = parseInt(0);
						 var thisAllsourceSize = parseInt(0);
						 for(jj = 0;jj<disklist.length;jj++){
							 if(thisdiskuuid == disklist[jj].target){
								 thisAlltargetSize = parseInt(disklist[jj].target_size);
								 thisAllsourceSize = parseInt(thisAllsourceSize) + parseInt(disklist[jj].source_size);
							 }
						 }
						 if(thisAlltargetSize < thisAllsourceSize){
							 UIToastr.showInfo(LANG.UI_OS_PLUG_GOAL_RECOVERY, LANG.UI_OS_PLUG_GOAL_RECOVERY_ERROR1);
							 return false;
						 }
					 }
					 
				 }else{
					 //关闭重建分区 disklist为分区信息
					 disklist = thislist[i].partition_allocation_strategy;
					 for(var j = 0;j<disklist.length;j++){
						 if(parseInt(disklist[j].target_size) < parseInt(disklist[j].source_size)){
							 UIToastr.showInfo(LANG.UI_OS_PLUG_GOAL_RECOVERY, LANG.UI_OS_PLUG_GOAL_RECOVERY_ERROR2);
							 return false;
						 }
					 }
				 }
			 }
			 
			 
			//-----------检查是否系统盘分配合理---------------
			 
			 //无论是那种情况恢复,无论是不是原机恢复 都不能选择系统盘,分区或磁盘同理
			 for(var i = 0; i<thislist.length;i++){
					 //无论是否是原机恢复 不能选择含有系统盘的分区或者磁盘
					 //得到是否重建分区
					 var reparted_flag = thislist[i].reparted_flag; 
					 if(reparted_flag == 1){
						 //重建分区打开时,判断磁盘是否有选择系统盘
						 var disk_allocation_strategy = thislist[i].disk_allocation_strategy;
						 if(disk_allocation_strategy.length != 0){
							 for(var j = 0;j<disk_allocation_strategy.length;j++){
								 if(disk_allocation_strategy[j].system_flag == 1){
									 //如果检测到有存在系统盘的直接退出
									 UIToastr.showInfo(LANG.UI_OS_PLUG_GOAL_RECOVERY, LANG.UI_OS_PLUG_GOAL_TARGET+disk_allocation_strategy[j].target_name);
									 return false;
								 }
							 }
						 }
					 }else{
						//重建分区关闭时,判断磁盘是否有选择系统盘
						 var partition_allocation_strategy = thislist[i].partition_allocation_strategy;
						 if(partition_allocation_strategy.length != 0){
							 for(var j = 0;j<partition_allocation_strategy.length;j++){
								 if(partition_allocation_strategy[j].system_flag == 1){
									 //如果检测到有存在系统盘的直接退出
									 UIToastr.showInfo(LANG.UI_OS_PLUG_GOAL_RECOVERY, LANG.UI_OS_PLUG_GOAL_TARGET+partition_allocation_strategy[j].target_name);
									 return false;
								 }
							 }
						 }
					 }
			 }
			 return true;
		 }
		 
		 
		 
		 
		 
		 //把1转换成开启,2转换成关闭
		 var flagToStr = function(numberFlag){
			 var intStr = "";
			 var numberFlagInt = parseInt(numberFlag);
			 if(numberFlagInt == 1){
				 intStr = LANG.UI_PUBLIC_ON;
			 }else{
				 intStr = LANG.UI_PUBLIC_OFF;
			 }
			 return intStr;
			 
		 }
		 
		 
		 //把布尔类型转换为1或者2
		 var boolToNumber = function(Thisbool){
			 var thisNumber = 2;
			 if(Thisbool){
				 thisNumber = 1;
			 }else{
				 thisNumber = 2;
			 }
			 return thisNumber;
		 }
		 
		 
		 
		 return getInfo();
	 }
	
  
  
})(jQuery)