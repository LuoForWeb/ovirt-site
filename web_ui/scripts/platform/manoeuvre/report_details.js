var OrchReportDetails = function () {
	
	//初始化顶部统计
	var initStatistics = function(statistics){
		//注意此处不能用$.data,因为通过他设置的data不会反映到html中,而后面counterup要通过html来读取data
		$('.tresult').html(statistics.result);
		$('.thosts').attr("data-value", statistics.hosts);
		$('.tvms').attr("data-value", statistics.vms);
		$('.ttimes').attr("data-value", statistics.times);
		// Handles counterup plugin wrapper
	    var handleCounterup = function() {
	        if (!$().counterUp) {
	            return;
	        }
	        $("[data-counter='counterup']").counterUp({
	            delay: 10,
	            time: 1000
	        });
	    };
	    handleCounterup();
	}
	
	//初始化echart
	var initCharts = function(charts){
		//初始化雷达图
		var vmresource = echarts.init(document.getElementById('vmresource'));
		var initRadar = function(radar){
			
			//转化时间为时分秒格式
			var getTimesDes = function(seconds){
				var timeDes = seconds + LANG.UI_PUBLIC_SECOND;
				if(seconds >= 3600){
					timeDes = Math.floor(seconds/3600) + LANG.UI_PUBLIC_HOUR;
					timeDes += Math.floor((seconds % 3600) / 60) + LANG.UI_PUBLIC_MINUTE;
					timeDes += (seconds % 3600 % 60) + LANG.UI_PUBLIC_SECOND;
					return timeDes;
				}
				if(seconds >= 60){
					timeDes = Math.floor(seconds/60) + LANG.UI_PUBLIC_MINUTE;
					timeDes += seconds % 60 + LANG.UI_PUBLIC_SECOND;
					return timeDes;
				}
				return timeDes;
			}
			//转化内存格式
			var getMemoryDes = function(memory){
				var des = memory + "MB";
				if(memory >= 1024){
					des = memory/1024 + "GB";
				}
				if(memory >= 1232896){
					des = memory/1024/1024 + "TB";
				}
				return des;
			}
			var option = {
				    title: {
//				        text: '基础雷达图'
				    },
				    tooltip: {
				    	trigger: 'item',
				    	formatter: function (params, ticket, callback) {
				    		var indicator = option.radar.indicator;
				    		var tips = params.name + "<br>";
				    		tips += indicator[0].name + " : " + getTimesDes(params.value[0]) + "<br>";
				    		tips += indicator[1].name + " : " + getMemoryDes(params.value[1]) + "<br>";
				    		tips += indicator[2].name + " : " + params.value[2] + "<br>";
				    		tips += indicator[3].name + " : " + getMemoryDes(params.value[3]) + "<br>";
				    		tips += indicator[4].name + " : " + params.value[4] + "<br>";
				    		return tips;
	        	        }
//				    	confine: true
				    },
				    legend: {
				    	x: 'center',
				        y: 'bottom',
				        data: radar.legenddata
				    },
				    radar: {
//				        shape: 'circle',
				        indicator: [
				           { name: LANG.UI_PUBLIC_CONSUME_HOUR, max: radar.radarindicatormax[0]},
				           { name: LANG.UI_PUBLIC_MEMORY, max: radar.radarindicatormax[1]},
				           { name: LANG.UI_VM_SETTING_NETWORK, max: radar.radarindicatormax[2]},
				           { name: LANG.UI_VM_SETTING_STORAGE, max: radar.radarindicatormax[3]},
				           { name: LANG.UI_PUBLIC_CPU, max: radar.radarindicatormax[4]},
				        ]
				    },
				    series: [{
				        type: 'radar',
				        radius : '75%',
				        center: ['50%', '50%'],
				        // areaStyle: {normal: {}},
				        data : radar.seriesdata
				    }]
				};
			vmresource.setOption(option, true);
		}
		//初始化饼图
		var vmsuccess = echarts.init(document.getElementById('vmsuccess'));
		var initPie = function(pie){
			var option = {
				    title : {
				        text: '',
				        subtext: '',
				        x:'center'
				    },
				    tooltip : {
				        trigger: 'item',
				        formatter: "{a} <br/>{b} : {c} ({d}%)"
				    },
				    legend: {
				        x: 'center',
				        y: 'bottom',
				        data: [LANG.UI_DATACENTER_SUCCESS,LANG.UI_DATACENTER_FAILURE]
				    },
				    color:['#45B6AF', '#F3565D'],
				    series : [
				        {
				            name: '',
				            type: 'pie',
				            radius : '75%',
				            center: ['50%', '50%'],
				            data:[
				                {value:pie.seriesdata.success, name:LANG.UI_DATACENTER_SUCCESS},
				                {value:pie.seriesdata.failure, name:LANG.UI_DATACENTER_FAILURE},
				            ],
				            label:{
	        	            	normal:{
	        	            		show:true,
	        	            		position:"inside",
	        	            		formatter: function (params, ticket, callback) {
	        	            			if(!params.percent) return '';
	        	        	        	return params.value + " (" + params.percent + "%)";
	        	        	        }
	        	            	}
	        	            },
				            itemStyle: {
				                emphasis: {
				                    shadowBlur: 10,
				                    shadowOffsetX: 0,
				                    shadowColor: 'rgba(0, 0, 0, 0.5)'
				                }
				            }
				        }
				    ]
				};
			vmsuccess.setOption(option, true);
		}
		
		//初始化窗口改变时调整饼图大小
	    var initResizeListener = function(){
	        window.onresize = function(){
	        	vmresource.resize();
	        	vmsuccess.resize();
	        }
	    }
	    
		initRadar(charts.radar);
		initPie(charts.pie);
		initResizeListener();
	}

	//初始化宿主机列表
	var initHostList = function(hosts){
		var groupHostRow = function(host){
			var row = "<tr>";
				row += "<td>" + host.id + "</td>";
				row += "<td>" + host.name + "</td>";
				row += "<td>" + host.ip + "</td>";
			if(host.segment.length > 1){
				row += "<td><table>"
				for(var i=0; i<host.segment.length; i++){
					var index = i+1;
					row += '<tr><td>'+ LANG.UI_VM_SETTING_NETWORK +'#' + index + '</td><td class="pd20">'+ LANG.UI_DRILLS_IP_ADDRESS +': ' + host.segment[i].old_segment + ' <br>'+ LANG.UI_DRILLS_NETMASK +': ' + host.segment[i].old_netmask + '<br>'+ LANG.UI_DRILLS_NETWORK_WAY +': ' + host.segment[i].old_gateway + '</td></tr>';
				}
				row += '</table></td>';
				
				row += "<td><table>"
				for(var i=0; i<host.segment.length; i++){
					var index = i+1;
					row += '<tr><td>'+ LANG.UI_VM_SETTING_NETWORK +'#' + index + '</td><td class="pd20">'+ LANG.UI_DRILLS_IP_ADDRESS +': ' + host.segment[i].verify_segment + ' <br>'+ LANG.UI_DRILLS_NETMASK +': ' + host.segment[i].verify_netmask + '<br>'+ LANG.UI_DRILLS_NETWORK_WAY +': ' + host.segment[i].verify_gateway + '</td></tr>';
				}
				row += '</table></td>';
				
			}else{
				row += '<td class="hidden-480">'+ LANG.UI_DRILLS_IP_ADDRESS +': ' + host.segment[0].old_segment + ' <br>'+ LANG.UI_DRILLS_NETMASK +': ' + host.segment[0].old_netmask + '<br>'+ LANG.UI_DRILLS_NETWORK_WAY +': ' + host.segment[0].old_gateway + "</td>";
				row += '<td class="hidden-480">'+ LANG.UI_DRILLS_IP_ADDRESS +': ' + host.segment[0].verify_segment + ' <br>'+ LANG.UI_DRILLS_NETMASK +': ' + host.segment[0].verify_netmask + '<br>'+ LANG.UI_DRILLS_NETWORK_WAY +': ' + host.segment[0].verify_gateway + "</td>";
			}
				
				
			row += "</tr>";
			return row;
		}
		var rows = '';
		for(var i=0; i<hosts.length; i++){
			rows += groupHostRow(hosts[i]);
		}
		$("#hosttbody").html(rows);
	}

	//初始化预案和虚拟机
	var initPlanAndVM = function(plan){
		$('#planlist').planList({plan: plan});
	}
	
	//获取报告信息,并调用初始化项目
	var initAll = function(){
        var data = {};
		data.uuid = $("#report_uuid").val();
		data = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.MANOEUVRE,f:'getOrchReportDetails',p:data}, function(d){
			var data = JSON.parse(d);
			initStatistics(data.statistics);
	        initCharts(data.charts);
	        initHostList(data.hostList);
	        initPlanAndVM(data.planAndVM);
		});
	}
	
    return {
        //main function to initiate the module
        init: function () {
        	initAll();
        }

    };

}();

jQuery(document).ready(function() {    
	OrchReportDetails.init();
});