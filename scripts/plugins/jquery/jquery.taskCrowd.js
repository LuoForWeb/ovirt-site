(function($){
  $.fn.taskCrowd = function(options){
	  var defaults = {
		  //多维数组,每一项表示每一个虚拟机的配置
		  'timeList' : [],	//0-23时段
		  'level': [0,1,2,3],//拥挤程度
		  'showFlag': false	//是否显示
	  }
	  var option = $.extend(defaults,options);
	  var parentID = '';
	  
	  //获取拥挤程度描述
	  var getLevelDiv = function(level){
		  var info = '<div class="unitDiv">';
		  for(var i=0; i<level.length; i++){
			  info += '<div class="unit '+ getLevelClass(level[i]) +'"><span class="unit-text">'+ getLevelDes(level[i]) +'</span></div>';
		  }
		  
		  info += '</div>';
		  return info;
	  }
	  
	  //获取每个小时的任务拥挤量显示
	  var getTimeCrowd = function(timeInfo, flag){
		  var info = '';
		  var title = LANG.UI_JOB_CROWD_TIME + '：'+ timeInfo.date;
		  if(flag){
			  title += ', ' + LANG.UI_JOB_CROWD_NUM + '：' + timeInfo.num;
		  }
		  info += '<div title="'+ title +'" class="timecrowd '+ getTimeCrowdLevel(timeInfo.num) +'"><span class="timecrowd-text">'+timeInfo.hour+'</span></div>'
		  return info;
	  }
	  
	  //根据任务个数获取拥挤程度
	  var getTimeCrowdLevel = function(num){
		  //初始化拥挤等级
		  var level = 0;
		  
		  /*空闲*/
		  if(num == 0){
			  level = 0;
		  }
		  
		  /*正常*/
		  if(0 < num && num < 10){
			  level = 1;
		  }
		  
		  /*拥挤*/
		  if(10 <= num && num < 30){
			  level = 2;
		  }
		  
		  /*繁忙*/
		  if(num >= 30){
			  level = 3;
		  }
		  
		  
		  return getLevelClass(level);
	  }
	  
	  //获取拥挤程度样式
	  var getLevelClass = function(level){
		  var levelClass = "leisure";
		  switch(level){
			  case 0:
				  levelClass = "leisure";
				  break;
			  case 1:
				  levelClass = "normal";
				  break;
			  case 2:
				  levelClass = "crowd";
				  break;
			  case 3:
				  levelClass = "saturation";
				  break;
		  }
		  
		  return levelClass;
	  }
	  
	  //获取拥挤程度描述
	  var getLevelDes = function(level){
		  var levelDes = LANG.UI_JOB_CROWD_LEISURE;
		  switch(level){
			  case 0:
				  levelDes = LANG.UI_JOB_CROWD_LEISURE;
				  break;
			  case 1:
				  levelDes = LANG.UI_JOB_CROWD_NORMAL;
				  break;
			  case 2:
				  levelDes = LANG.UI_JOB_CROWD_CROWD;
				  break;
			  case 3:
				  levelDes = LANG.UI_JOB_CROWD_BUSY;
				  break;
		  }
		  
		  return levelDes;
	  }
	  
	  return this.each(function(){
		  var _this = $(this);
		  parentID = _this.get(0).id;
		  var info = '';
		  for(var i=0; i<option.timeList.length; i++){
			  info += getTimeCrowd(option.timeList[i], option.showFlag);
		  }
		  info += getLevelDiv(option.level);
		  _this.empty().html(info);
		  
	  });
  }
  
})(jQuery)