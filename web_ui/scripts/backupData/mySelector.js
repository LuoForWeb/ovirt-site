/*
 * @note: 备份数据管理存储筛选插件
 * @author: chenyunfeng@vinchin.com
 * @Description: 
 * @Date: 2024-11-21 16:00:58
 * @LastEditTime: 2025-08-21 11:19:26
 * @Version: 2.0
 * @copyright: Copyright 2024 vinchin.com
 * 
 * 用法：$('.module-tab_storage').mySelector({storageList: storageList});
 * 需要传入参数：storageList: 存储列表
 */
(function($){
    $.fn.mySelector = function (options) {
        const defaults = {};
        const settings = {...defaults, ...options};
        const dom = $(this);
        // 存储类型-》名称
        const STORAGE_TYPE = {
            1: LANG.UI_VIRTUAL_LOCAL_DISK, // 本地磁盘
            2: LANG.UI_VIRTUAL_LOGICAL_VOLUME_LVM, // 逻辑卷LVM
            3: LANG.UI_VIRTUAL_LOCAL_PARTITION,// 本地分区
            4: LANG.UI_STORAGE_TYPE_DETAIL_FC, // Fibre Channel
            5: LANG.UI_STORAGE_TYPE_ISCSI, //iSCSI
            6: LANG.UI_STORAGE_TYPE_NFS, // NFS
            7: LANG.UI_STORAGE_TYPE_CIFS, // CIFS
            8: LANG.UI_STORAGE_TYPE_REMOTE, // 异地存储
            9: LANG.UI_VIRTUAL_CLOUD_STORAGE, // 云存储
            10: LANG.UI_STORAGE_TYPE_TAPE, // 磁带
            11: LANG.UI_STORAGE_TYPE_LOCAL_CATALOGUE, // 本地目录
		    16: LANG.UI_STORAGE_TYPE_16, // DELL Data Domain Boost
        }
        // 生成下拉菜单
        const getSelectorWrapper = () => {
            return `<div class="mySelector-dropdown">
                        <span class="selected-option" value="">${LANG.UI_COPY_LOCAL_STORAGE_ALL}</span><i class="viconfont vicon-a-Upshang"></i>
                    </div>
                    <div class="mySelector-options display-none">${getOptions()}</div>`;
        }
        // 生成菜单选项
        const getOptions = () => {
            let list = settings.storageList;
            let html = '';
            html += `<div class="mySelector-option_li" data-id="" data-type="" data-name="${LANG.UI_COPY_LOCAL_STORAGE_ALL}">
                        <div class="option_li-icon">
                            <i class="viconfont vicon-beifenshuju--bendicunchu"></i></span>
                        </div>
                        <div class="option_li-detail">
                            <span class="option_li-name">${LANG.UI_COPY_LOCAL_STORAGE_ALL}</span>
                        </div>
                    </div>`;
            list.forEach(storage => {
				// 存储类型
				const { storage_type: storageType, storage_nickname, free_size, storage_uuid, node } = storage;
				let remote_ip = '';
                let icon = "bendicunchu"; // 存储图标
                if (storageType == CONF.BD_STORAGE_TYPE.CLOUD) { // 云存储图标
                    icon = 'yuncunchu';
                }
                // 删除异地数据需要使用remote_ip
				if (storageType == CONF.BD_STORAGE_TYPE.REMOTE) {
					remote_ip = node;
				}
                html += `<div class="mySelector-option_li" data-id="${storage_uuid}" data-type="${storageType}" data-ip="${remote_ip ?? ''}" data-name="${storage_nickname}">
							<div class="option_li-icon">
								<i class="viconfont vicon-beifenshuju--${icon}"></i></span>
							</div>
							<div class="option_li-detail">
								<span class="option_li-name">${storage_nickname}</span>
								<div class="option_li-info">
								    <span class="option_li-size">${free_size}${LANG.UI_BACKUP_DATA_LABEL_FREE_SIZE}</span>
									<span class="option_li-type">${STORAGE_TYPE[storageType]}</span>
								</div>
							</div>
						</div>`;
            })
            return html;
        }
        // 初始化渲染dom
        const init = () => {
            let list = settings.storageList;
            if (list.length === 0) {
                return false;
            }
            dom.addClass('mySelector-wrapper');
            dom.append(getSelectorWrapper());
        }
        // 控制下拉菜单显示隐藏
        const openDropdown = function() {
            let _this = $(this);
            if(_this.hasClass('open')) {
                _this.removeClass('open');
                dom.find('.mySelector-options').removeClass('show');
            } else {
                _this.addClass('open');
                dom.find('.mySelector-options').addClass('show');
            }
        }
        // 选中点击事件
        const selectOption = function() {
            let _this = $(this);
            // 切换显示存储
            dom.find('.selected-option').html(_this.data('name'));
            dom.find('.selected-option').attr('data-id', _this.data('id'));
            dom.find('.selected-option').attr('data-type', _this.data('type'));
            dom.find('.selected-option').attr('data-ip', _this.data('ip'));
            dom.find('.mySelector-dropdown').trigger('click');
            // 切换存储视图选中存储
            $('.storage-tab-item').removeClass('active');
            _this.addClass('active');
        };
        // 点击控件之外的区域，关闭菜单
        const closeDropdown = function(event) {
            // 判断点击目标是否在控件内
            if (!dom.is(event.target) && dom.has(event.target).length === 0) {
                dom.find('.mySelector-dropdown').removeClass('open');
                dom.find('.mySelector-options').removeClass('show');
            }
        }
        // 控件点击事件
        const listener = () => {
            // 下拉菜单显示隐藏
            dom.find('.mySelector-dropdown').on('click', openDropdown);
            // 选中选项事件
            dom.find('.mySelector-option_li').on('click', selectOption);
            $(document).on('click', closeDropdown);
        }
        
        return this.each(function () {
            init();
            listener();
        });
    }
})(jQuery)