(function($) {
	$.fn.wormDefine = {
		worm_type: {
			normal: 1,		// 正常配置WORM
			no_worm: 2,		// 存储无WORM保护
			no_storage: 3,	// 无存储
			p_incr_backup: 4,  // 永久增量备份
		}
	};

	/**
	 * 使用说明
	 * 1、在html界面定义一个dom元素，如: <div id="wormConfig"></div>
	 * 2、初始化组件 $('#wormConfig').wormProtectionBackup(true,'col-md-3', false, false, 1)
	 * 3、获取配置值 var a = $('#wormConfig').getWormProtectionSettings()
	 *
	 * 初始化参数说明
	 * worm_type: worm配置类型【1: 正常配置WORM，2: 存储无WORM保护，3: 无存储】
	 * percent: 文本的宽度
	 * hideWormPeriodFlag: 是否隐藏WORM保护期限配置
	 * enable_worm_flag: 是否开启WORM保护
	 * days: WORM保护期限
	 *
	 * 返回值说明
	 * {
	 *     worm_flag: WORM开关
	 *     worm_protection_time: WORM保护期限
	 * }
	 *
	 * @param flag switch
	 * @param days days
	 * @returns {*}
	 */
	$.fn.wormProtectionBackup = function(
		worm_type = $.fn.wormDefine.worm_type.normal,
		percent = 'col-md-3',
		hideWormPeriodFlag= false,
		enable_worm_flag = false,
		days = 7
	) {
		let id = $(this).attr('id');
		if (typeof worm_type === 'boolean') {
			if (worm_type) {
				worm_type = $.fn.wormDefine.worm_type.normal;
			} else {
				worm_type = $.fn.wormDefine.worm_type.no_worm;
			}
		} else {
			worm_type = parseInt(worm_type);
		}

		const getContent = () => {
			let disableWormMessage = LANG.UI_SAFE_STRATEGY_WORM_PROTECT_DISABLE_TIPS1.replace(
				'%S',
				`<a class="ajaxify" name="storage_manager" href="./content/platform/storage/storage.php">${LANG.UI_REPORT_STORAGE}</a>`
			);
			let remainPercent = 'col-md-' + (12 - percent[percent.length - 1]);
			return `
			<div class="form-group">
				<label class="col-md-3 control-label ${percent}  form-group-label">${LANG.UI_SAFE_STRATEGY_WORM_PROTECT}</label>
				<div class="col-md-3 form-group-content flex-items-center">
				 	<input type="checkbox" id="${id}_check" ${enable_worm_flag && worm_type === $.fn.wormDefine.worm_type.normal ? 'checked' : ''} class="make-switch" data-on-color="primary" data-size="small" data-off-color="info" data-on-text=${LANG.UI_FILE_ENABLE} data-off-text=${LANG.UI_FILE_DISABLE}>
				 	<a class="ml12 mb-5" data-toggle="tooltip" data-placement="right" data-html="true" title="${LANG.WEB_WORM_PROTECT_TIPS}">
						<i class="viconfont vicon-tishi"></i>
				    </a>
			    </div>
			</div>
			<!-- 存储没有开启WORM提示 -->
			<div class="form-group display-none" id="${id}NoWormTipsDiv">
				<label class="col-md-3 control-label ${percent}"></label>
				<div class="col-md-9 ${remainPercent}">
					<div class="alert alert-block alert-info fade in m0">
						<button type="button" class="close" data-dismiss="alert"></button>
						<strong class="alert-ul-head">${LANG.UI_PUBLIC_TIPS}:</strong>
						<ul class="alert-ul">
							<li>${disableWormMessage}</li>
						</ul>
					</div>
				</div>
			</div>
			<!-- 没有选择具体存储提示 -->
			<div class="form-group display-none" id="${id}NoStorageTipsDiv">
				<label class="col-md-3 control-label ${percent}"></label>
				<div class="col-md-9 ${remainPercent}">
					<div class="alert alert-block alert-info fade in m0">
						<button type="button" class="close" data-dismiss="alert"></button>
						<strong class="alert-ul-head">${LANG.UI_PUBLIC_TIPS}:</strong>
						<ul class="alert-ul">
							<li>${LANG.UI_SAFE_STRATEGY_WORM_PROTECT_DISABLE_TIPS2}</li>
						</ul>
					</div>
				</div>
			</div>
			<!-- 时间策略启用了永久增量 -->
			<div class="form-group display-none" id="${id}PIncrBackupTipsDiv">
				<label class="col-md-3 control-label ${percent}"></label>
				<div class="col-md-9 ${remainPercent}">
					<div class="alert alert-block alert-info fade in m0">
						<button type="button" class="close" data-dismiss="alert"></button>
						<strong class="alert-ul-head">${LANG.UI_PUBLIC_TIPS}:</strong>
						<ul class="alert-ul">
							<li>${LANG.UI_SAFE_STRATEGY_WORM_PROTECT_DISABLE_TIPS3}</li>
						</ul>
					</div>
				</div>
			</div>
			<div class="form-group wormDiv timeInput ${enable_worm_flag ? '' : 'display-none'} ${hideWormPeriodFlag ? 'display-none' : ''}">
				<label class="col-md-3 control-label ${percent} ">${LANG.UI_SAFE_STRATEGY_WORM_PROTECT_PERIOD}
			   	</label>
			   	<div class="col-md-3">
					<div id="${id}_spinner">
						<div class="input-group spinner-group" style="justify-content: left">
							<input id="${id}_input" onkeyup="value=value.replace(/[^\\d]/g,'')" class="spinner-input form-control input-sm" style="width: 150px">
					  		<div class="spinner-buttons input-group-btn spinner-group-btn" style="left: 128px">
						 		<button type="button" class="input-sm btn spinner-up default" style="margin-left: -1px">
									<i class="fa fa-angle-up"></i>
						 		</button>
						 		<button type="button" class="input-sm btn spinner-down default" style="border-right: none;border-bottom: none">
									<i class="fa fa-angle-down"></i>
						 		</button>
					   		</div>
							<span style="line-height: 28px;margin-left: 8px">${LANG.UI_STRATEGY_RESERVE_DAY_EN}</span>
						</div>
				  	</div>     
			   	</div>
			</div>
			`;
		};

		const inputValue = () => {
			let aa = `#${id}_check`
			if (days < 1) {
				days = 7;
			}
			$(`#${id}_spinner`).spinner({value: days, min: 1, max: 9999, step: 1})
			$(aa).bootstrapSwitch();
			if(worm_type === $.fn.wormDefine.worm_type.no_worm) {
				$(aa).bootstrapSwitch('disabled', true);
				$(`#${id}NoWormTipsDiv`).show();
				$(`#${id}NoWormTipsDiv button.close`).on('click', () => {
					$(`#${id}NoWormTipsDiv`).remove();
				});
				$(`#${id}NoStorageTipsDiv`).hide();
				$(`#${id}PIncrBackupTipsDiv`).hide();
			} else if (worm_type === $.fn.wormDefine.worm_type.no_storage) {
				$(aa).bootstrapSwitch('disabled', true);
				$(`#${id}NoWormTipsDiv`).hide();
				$(`#${id}NoStorageTipsDiv`).show();
				$(`#${id}NoStorageTipsDiv button.close`).on('click', () => {
					$(`#${id}NoStorageTipsDiv`).remove();
				});
				$(`#${id}PIncrBackupTipsDiv`).hide();
			} else if (worm_type === $.fn.wormDefine.worm_type.p_incr_backup) {
				$(aa).bootstrapSwitch('disabled', true);
				$(`#${id}NoWormTipsDiv`).hide();
				$(`#${id}NoStorageTipsDiv`).hide();
				$(`#${id}PIncrBackupTipsDiv`).show();
				$(`#${id}PIncrBackupTipsDiv button.close`).on('click', () => {
					$(`#${id}PIncrBackupTipsDiv`).remove();
				});
			} else {
				$(`#${id}NoWormTipsDiv`).hide();
				$(`#${id}NoStorageTipsDiv`).hide();
				$(`#${id}PIncrBackupTipsDiv`).hide();
			}
		};

		const wormDaysChange = function () {
			let $wormDays = $(`#${id}_input`);
			let value = parseInt($wormDays.val());
			if (!$wormDays.val() || value < 1) {
				value = 1;
			} else if (value > 9999) {
				value = 9999;
			}
			$(`#${id}_spinner`).spinner('value', value);
		};

		const registerEventListener = () => {
			$(`#${id} [data-toggle="tooltip"]`).tooltip();
			$(`#${id}_check`).on('switchChange.bootstrapSwitch',function() {
				if(!hideWormPeriodFlag){
					if (this.checked) {
						$('.timeInput').show();
					} else {
						$('.timeInput').hide();
					}
				}
			});

			$(`#${id}_input`).on('change', wormDaysChange);
			$(`#${id}_spinner button`).on('click', wormDaysChange);
		};

		$(`#${id}`).html(getContent());
		inputValue()
		registerEventListener();
	};

	// 添加一个方法来获取WORM防护设置
	$.fn.getWormProtectionSettings = function() {
		let id = $(this).attr('id');

		return {
			worm_flag: $(`#${id}_check`).get(0).checked? 1:0,
			worm_protection_time: parseInt($(`#${id}_input`).val()),
		};
	};

})(jQuery);
