//演练详细报告分组详情内容生成
(function($){
  $.fn.planList = function(options){
	  var defaults = {
		  //多维数组,每一项表示每一个分组预案
		  'plan' : [],
	  }
	  var option = $.extend(defaults,options);
	  
	  //得到总预案的信息
	  var getPlanDiv = function(plan){
		  var groupInfo = "";
		  for(var i=0; i<plan.group.length; i++){
			  groupInfo += getGroupDiv(plan.group[i], i);
		  }
		  var div = '<div class="mt-element-list">' + 
	                    '<div class="mt-list-head list-todo blue-hoki">' + 
	                        '<div class="list-head-title-container">' + 
	                            '<h3 class="list-title">' + plan.name + '</h3>' + 
	                            '<div class="list-head-count">' + 
	                                '<div class="list-head-count-item">' + 
	                                    '<i class="iconfont icon-bumen1"></i> ' + LANG.UI_DRILLS_TASK_GROUP_PLAN + plan.groupnum + '</div>' + 
	                                '<div class="list-head-count-item">' + 
	                                    '<i class="iconfont icon-bumen"></i> ' + LANG.UI_DRILLS_TASK_CHILD_PLAN + plan.childnum + '</div>' + 
	                                '<div class="list-head-count-item">' + 
	                                    '<i class="fa fa-desktop"></i> ' + LANG.UI_DRILLS_TASK_VM + plan.vmnum + '</div>' + 
	                            '</div>' + 
	                        '</div>' + 
	                    '</div>' + 
	                    '<div class="mt-list-container list-todo">' + 
	                        '<div class="list-todo-line red"></div>' + 
	                        '<ul>' + 
	                        groupInfo + 
	                        '</ul>' + 
	                    '</div>' + 
	                '</div>';
		  return div;
	  }
	  
	  //得到每个分组预案的内容
	  var getGroupDiv = function(group, gindex){
		  var child = "";
		  for(var i=0; i<group.child.length; i++){
			  child += getChildDiv(group.child[i], gindex, i);
		  }
		  var li = '<li class="mt-list-item">' + 
                        '<div class="list-todo-icon bg-white font-blue-steel">' + 
                            '<i class="iconfont icon-bumen1"></i> ' + group.name + 
                        '</div>' + 
                        child + 
                   '</li>';
		  return li;
	  }
	  
	  //得到每个子预案的内容
	  var getChildDiv = function(child, gindex, cindex){
		  var id = "child-" + gindex + "-" + cindex;
		  var vm = "";
		  for(var i=0; i<child.vm.length; i++){
			  vm += getVMDiv(child.vm[i]);
		  }
		  var div = '<div class="list-todo-item blue-steel">' + 
                        '<a class="list-toggle-container font-white" data-toggle="collapse" href="#' + id + '" aria-expanded="false">' + 
                            '<div class="list-toggle done uppercase">' + 
                                '<div class="list-toggle-title bold">' + 
                                    '<i class="iconfont icon-bumen"></i> ' + child.name + '</div>' + 
                                '<div class="badge badge-default pull-right bold">' + child.vmnum + '</div>' + 
                            '</div>' + 
                        '</a>' + 
                        '<div class="task-list panel-collapse collapse in" id="' + id + '">' + 
                            '<ul>' + 
                            vm + 
                            '</ul>' + 
                        '</div>' + 
		  			'</div>';
		  return div;
	  }
	  
	  //得到每个虚拟机的内容
	  var getVMDiv = function(vm){
		  var colour = getVMColour(vm.result);
		  var icon = getVMResultIcon(vm.result);
		  var li = '<li class="task-list-item done">' + 
                        '<div class="task-icon icon30">' + 
                             '<i class="fa fa-desktop ' + colour + '"></i>' + 
                        '</div>' + 
                        '<div class="task-status icon30">' + 
                             '<i class="fa ' + icon + ' ' + colour + '"></i>' + 
                        '</div>' + 
                        '<div class="task-content">' + 
                            '<h4 class="bold">' + 
                                vm.name + 
                            '</h4>' + 
                            '<p>' + LANG.UI_DRILLS_TASK_CPU_SIZE + vm.cpu + 
                            	'<br>' + LANG.UI_DRILLS_TASK_STORAGE + vm.memory + 
								'<br>' + LANG.UI_DRILLS_TASK_SIZE + vm.size + 
								'<br>' + LANG.UI_DRILLS_TASK_BACKUP_TIMEPOINT + vm.timepoint + 
								'<br>' + LANG.UI_EMERGENCY_RECOVERY_DESTINATION_HOST + vm.host;
							
		  //如果有描述,就添加描述
		  if(vm.des){
			  li += '<br>' + LANG.UI_DRILLS_TASK_DESCRIBE + vm.des;
		  }
		  li += '</p>' + '</div>' + '</li>';
		  return li;
	  }
	  
	  //根据虚拟机状态得到显示的颜色,虚拟机图标和结果
	  var getVMColour = function(result){
		  return result ? "font-green-seagreen" : "font-red";
	  }
	  
	  //根据虚拟机状态得到√还是X
	  var getVMResultIcon = function(result){
		  return result ? "fa-check" : "fa-close";
	  }
	  
	  return this.each(function(){
		  var _this = $(this);
		  var planDiv = getPlanDiv(option.plan);
		  _this.empty().html(planDiv);
	  });
  }
})(jQuery)