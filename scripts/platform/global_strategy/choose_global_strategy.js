var chooseGlobalStrategy = function () {

	//初始化表格
	var initDataTable = function () {
		var options = {
			pagination:true,
			pageList: [5,10,25,50],
			detailView:true,
			resizable: false,
			detailFormatter: current_detail, //详情展开
			singleSelect:true,
			vin_url:"/api/v1/storages/global_speed",
			vin_method:"GET",
			columns:[{
				checkbox:true,
				sortable: false,
			},
				{
					field: 'name',
					title: LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_NAME,
					sortable: true,
					align: 'center',
				},
				{
					field: 'strategy_type',
					title: LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_TYPE,
					sortable: true,
					align: 'center',
				},
				{
					field: 'detail',
					title: LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_SETTING,
					sortable: false,
					align: 'center',
					formatter: function (value, row, index){
						var newStr = value.replace(/<\/br>/g, '\n');
						return '<span title="'+newStr+'">' + value + '</span>';
					}
				},
			],
			onCheck: function (rowdata) {
				modifyDelStyle();
			},
			onUncheck: function (rowdata) {
				modifyDelStyle();
			},
			PostBody: function () {
				$('.strategy-table #table th[data-field="strategy_type"]').css('width','20%');
				$('.strategy-table #table th[data-field="name"]').css('width','30%');
				$('.strategy-table #table th[data-field="detail"]').css('width','50%');
			}
		}

		$('.strategy-table #table').baseTableConfig().init(options);
	}

	// 策略管理详情显示
	var lastIndex = [-1, -1];
	var current_detail = function (index, data, element) {
		// 控制只显示一个
		if (index != lastIndex[1]) {
			lastIndex.push(index);
			$('.strategy-table #table').bootstrapTable('collapseRow', lastIndex[lastIndex.length - 2]);
			lastIndex.splice(0, 1);
		}
		var content = '<table><tbody>';
		content += '<tr><td style="width:100px;">'+LANG.UI_PLATFORM_ASSOCIA_TASK+'：</td><td>' + data.job_list + '</td></tr>';
		content += '<tr><td style="width:100px;">'+LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_TYPE+'：</td><td>' + data.strategy_type + '</td></tr>';
		content += '<tr><td style="width:100px;">'+LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_SETTING+'：</td><td>' + data.detail + '</td></tr>';
		content += '</tbody></table>';
		$(element).append(content);
	}

	var modifyDelStyle = function () {
		var selectedRow = $('.strategy-table #table').bootstrapTable('getSelections');
		$('.speedTips').popover();	   //初始化tips
		var titleDes = "";
		var des = "";
		if (selectedRow.length == 1) {
			// 选中复选框
			var datas = selectedRow[0].detail;
			datas = datas.split('</br>');
			if(datas.length !=0){
				des += LANG.UI_GLOBAL_STRATEGY_SPEED_NUM + ": " + datas.length;
			}

			for(var i in datas){
				titleDes += datas[i] + '. ';
			}

			$('.speedlimitDes').html(des);
			$('.speedlimitDes').prop('title', titleDes);
		} else {
			$('.speedlimitDes').html(des);
			$('.speedlimitDes').prop('title', titleDes);
		}
	}


	return {
		//main function to initiate the module
		init: function () {
			initDataTable();
		}
	};

}();

jQuery(document).ready(function () {
	chooseGlobalStrategy.init();
});
