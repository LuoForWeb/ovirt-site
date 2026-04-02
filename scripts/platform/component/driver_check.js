/**
 * @author JackC
 * @description 驱动检测组件
 * ////////////////////////////////////
 * 逻辑说明:
 * 1. 异构平台需要检测驱动
 * 2. 非异构平台不需要检测驱动
 * 3. 上传驱动打开新的页面
 * 4. 操作系统选择：Windows有版本和架构，Linux没有版本和架构
 */
(($, Metronic, UIToastr, LANG) => {

    //////////////////// 开始 - 类型定义 //////////

    /**
     * @typedef {Object} DriverOption - 驱动选项
     * @property {DriverWidth} [width={}] 宽度定义
     * @property {Function|null} getCheckObject 获取检测的内容
     * @property {Function|null} afterCheck 获取检测的内容
     * @property {Number} driver_usage 用途
     * @property {Boolean} [show_check_btn=false] 是否显示驱动检测配置
     * @property {Object} [element_set={}] 元素集合
     */

    /**
     * @typedef {Object} DriverWidth - 驱动宽度定义
     * @property {String} [config_label='col-md-3'] 驱动检测标签的宽度
     * @property {String} [config_content='col-md-8'] 驱动检测内容框的宽度
     * @property {String} [check_name='col-md-3'] 驱动检测内容的名称
     * @property {String} [check_platform='col-md-3'] 驱动检测内容的平台
     * @property {String} [check_driver='col-md-6'] 驱动检测内容的驱动
     * @property {String} [result_name='col-md-3'] 驱动检测结果名称
     * @property {String} [result_detail='col-md-7'] 驱动检测结果详情
     * @property {String} [result_upload='col-md-2'] 驱动检测结果上传
     */

    /**
     * @typedef {Object} CheckObject - 检测的对象
     * @property {Array<CheckSource>} source_list 检测源信息
     * @property {CheckTarget} target_info 检测目标信息
     */

    /**
     * @typedef {Object} CheckSource - 检测源信息
     * @property {String} name 主机名称
     * @property {String} timepoint_uuid 备份点uuid
     * @property {String} agent_uuid 客户端uuid
     * @property {String} module_type 模块类型【虚拟化 操作系统】
     * @property {Array<String>} [source_hypervisor_disk_bus_list=[]] 磁盘总线列表
     * @property {Array<String>} [source_hypervisor_net_bus_list=[]] 网卡总线列表
     * @property {Array<String>} target_hypervisor_disk_bus_list 磁盘总线列表
     * @property {Array<String>} target_hypervisor_net_bus_list 网卡总线列表
     */

    /**
     * @typedef {Object} CheckTarget - 检测目标信息
     * @property {Number|String} target_type 检测目标类别【1虚拟化平台 2客户端】
     * @property {VmInfo} [vm_info={}] 虚拟化信息
     * @property {ClientInfo} [client_info={}] 客户端信息
     * @property {String} os_type 操作系统类型【Windows CentOS ...】
     * @property {String} os_version 操作系统版本【RHEL6 RHEL7】
     * @property {String} os_arch 操作系统架构【RHEL6 RHEL7】
     */

    /**
     * @typedef {Object} VmInfo - 虚拟化信息
     * @property {Number|String} vm_type 虚拟化类型【1VMware vSphere 2Microsoft Hyper-V 3Citrix XenServer...】
     * @property {String} vcenter_uuid 虚拟化uuid
     * @property {String} host_uuid 虚拟主机uuid
     * @property {Array<{value: string, name: string}>} disk_bus_list 磁盘总线类型列表
     * @property {String} default_disk_bus 默认的磁盘总线类型
     * @property {Array<{value: string, name: string}>} net_bus_list 网卡类型列表
     * @property {String} default_net_bus 默认的网卡类型
     * @property {Boolean} bus_changeable_flag 是否允许修改总线类型
     * @property {Boolean} show_bus_flag 是否显示总线类型
     */

    /**
     * @typedef {Object} ClientInfo - 客户端信息
     * @property {String} agent_uuid 客户端uuid
     */

    /**
     * @typedef {Object} RenderObject - 渲染对象
     * @property {String} name 显示的名称
     * @property {String} agent_uuid 客户端uuid
     * @property {String} timepoint_uuid 时间点uuid
     * @property {Number} module_type 模块类型
     * @property {Boolean} show_platform 是否显示平台检测结果
     * @property {Boolean} diff_platform_flag 平台检测结果
     * @property {Boolean} show_driver 是否显示驱动检测结果
     * @property {Number} check_driver_status 驱动检测结果【1通过 2未检测到操作系统 3驱动缺失】
     * @property {String} result_detail 驱动结果结果的详情，缺失驱动的硬件
     */

    /**
     * @typedef {Object} DriverRequestData - 请求的数据结构
     * @property {Number} driver_usage 用途
     * @property {Array<DriverRequestCheckOsObject>} check_os_list 检查的驱动列表
     */

    /**
     * @typedef {Object} DriverRequestCheckOsObject - 请求的内容
     * @property {String} agent_uuid 客户端uuid
     * @property {String} timepoint_uuid 时间点uuid
     * @property {Number} target_type 检测目标类别【1虚拟化平台 2客户端】
     * @property {VmInfo} vm 虚拟化信息
     * @property {ClientInfo} client 客户端信息
     * @property {Boolean} manual_config_flag 是否手动配置操作系统
     * @property {Boolean} manual_bus_config_flag 是否手动配置总线
     * @property {String} os_type 操作系统类型【Windows CentOS ...】
     * @property {String} os_version 操作系统版本【RHEL6 RHEL7】
     * @property {String} os_arch 操作系统架构【RHEL6 RHEL7】
     */

    /**
     * @typedef {Object} DriverResponse - 响应结果
     * @property {Number} code
     * @property {Boolean} success
     * @property String message
     * @property {{data: {check_result: Array<DriverResponseCheckResult>}}} data
     */

    /**
     * @typedef {Object} DriverResponseCheckResult - 检测结果
     * @property {Number|String} platform_type 平台类型【0未知 1同平台 2异构平台】
     * @property {Number|String} driver_check_status 驱动检测状态【1通过 2未检测到操作系统 3驱动缺失】
     * @property {String} lack_driver_info 缺失驱动信息
     * @property {String} driver_hw_id_map 驱动uuid与硬件ID的映射
     */

    //////////////////// 结束 - 类型定义 //////////

    /**
     *
     * @type {Array<{req_data: ?DriverRequestData, check_result: ?Array<DriverResponseCheckResult>, check_info: ?CheckObject, options: ?DriverOption}>}
     */
    let cache = [];

    // 枚举定义
    /**
     * 驱动检测目标的类型
     * @type {{VM: number, CLIENT: number}}
     */
    const DRIVER_TARGET_TYPE = {
        VM: 1,  // 虚拟化
        CLIENT: 2,  // 客户端
    };
    /**
     * 平台类型
     */
    const DRIVER_PLATFORM_TYPE = {
        UNKNOWN: 0,
        SAME: 1,
        DIFF: 2,
    };
    /**
     * 驱动检测结果枚举
     */
    const DRIVER_CHECK_RESULT = {
        PASS: 1,  // 检测通过
        NO_OS: 2,  // 未检测到操作系统
        NO_DRIVER: 3,  // 驱动缺失
        NO_TIMEPOINT: 4, // 备份点不存在
        NO_HW: 5,  // 备份点缺少硬件信息
        NO_CLIENT: 6,  // 客户端不存在
        CLIENT_LACK_HW: 7, // 未检测到客户端的硬件信息
        NONSUPPORT_OS_INSTALL_DRIVER: 8,  // 该操作系统版本不支持添加驱动
    };
    const DRIVER_CHECK_RESULT_LANG = {
        1: LANG.UI_DRIVER_CHECK_RESULT_PASS,
        2: LANG.UI_DRIVER_CHECK_RESULT_NO_OS,
        3: LANG.UI_DRIVER_CHECK_RESULT_NO_DRIVER,
        4: LANG.UI_DRIVER_CHECK_RESULT_NO_TIMEPOINT,
        5: LANG.UI_DRIVER_CHECK_RESULT_TIMEPOINT_LACK_HW,
        6: LANG.UI_DRIVER_CHECK_RESULT_NO_CLIENT,
        7: LANG.UI_DRIVER_CHECK_RESULT_CLIENT_LACK_HW,
        8: LANG.UI_DRIVER_CHECK_RESULT_NONSUPPORT_OS_INSTALL_DRIVER,
    };
    /**
     * 操作系统类型
     */
    const DRIVER_OS_TYPE = {
        Windows: 'Windows',
        Linux: 'Linux',
    };
    /**
     * Windows操作系统版本
     */
    const DRIVER_WINDOWS_OS_VERSION = {
        'Windows 11': '10.0',
        'Windows Server 2022': '10.0',
        'Windows Server 2019': '10.0',
        'Windows Server 2016': '10.0',
        'Windows 10': '10.0',
        'Windows Server 2012 R2': '6.3',
        'Windows 8.1': '6.3',
        'Windows Server 2012': '6.2',
        'Windows 8': '6.2',
        'Windows Server 2008 R2': '6.1',
        'Windows 7': '6.1',
        'Windows Server 2008': '6.0',
        'Windows Vista': '6.0',
        'Windows Server 2003 R2': '5.2',
        'Windows Server 2003': '5.2',
        'Windows XP': '5.1',
    };
    /**
     * Windows操作系统架构
     */
    const DRIVER_WINDOWS_OS_ARCH = {
        amd64: 'amd64',
        x86: 'x86',
        arm: 'arm',
        arm64: 'arm64',
    };
    const DRIVER_USAGE_ENUM = {
        BACKUP: 1,   // 备份的源使用agent_uuid
        RECOVERY: 2, // 恢复的源使用timepoint_uuid
        TAKEOVER: 3, // 接管的源使用timepoint_uuid，目标可以是客户端和内嵌虚拟化
    };

    /**
     * 获取驱动的宽度
     * @param {DriverWidth} width - 宽度定义
     * @return {DriverWidth}
     */
    const getDriverWidth = width => {
        return {
            config_label: typeof width.config_label !== 'undefined' ? (width.config_label.length ? width.config_label : 'display-none') : 'col-md-3',
            config_content: typeof width.config_content !== 'undefined' ? width.config_content : 'col-md-8',
            check_name: typeof width.check_name !== 'undefined' ? width.check_name : 'col-md-5',
            check_platform: typeof width.check_platform !== 'undefined' ? width.check_platform : 'col-md-3',
            check_driver: typeof width.check_driver !== 'undefined' ? width.check_driver : 'col-md-4',
            result_name: typeof width.result_name !== 'undefined' ? width.result_name : 'col-md-3',
            result_detail: typeof width.result_detail !== 'undefined' ? width.result_detail : 'col-md-7',
            result_upload: typeof width.result_upload !== 'undefined' ? width.result_upload : 'col-md-2',
        }
    };

    /**
     * 获取配置
     * @param el
     * @param options
     * @return {DriverOption}
     */
    const getDriverOptions = (el, options) => {
        let id = el.attr('id');
        return {
            width: getDriverWidth(typeof options.width !== 'undefined' ? options.width : {}),
            getCheckObject: typeof options.getCheckObject !== 'undefined' ? options.getCheckObject : null,
            afterCheck: typeof options.afterCheck !== 'undefined' ? options.afterCheck : null,
            show_check_btn: typeof options.show_check_btn !== 'undefined' ? options.show_check_btn : false,
            driver_usage: typeof options.driver_usage !== 'undefined' ? parseInt(options.driver_usage) : DRIVER_USAGE_ENUM.RECOVERY,
            element_set: {
                prefix_id: id,
                wrapper_id: id + '_driverCheckDiv',
                check_driver_btn_id: id + '_checkDriver',
                check_content_id: id + '_driverCheckContent',
                check_list_id: id + '_driverCheckList',
                check_modal_id: id + '_driverOsTypeCheckModal',
                os_type_id: id + '_driverOsTypeSelect',
                os_version_id: id + '_driverOsVersionSelect',
                os_arch_id: id + '_driverOsArchSelect',
                change_bus_type_modal_id: id + '_driverChangeBusTypeModal',
                disk_bus_type_id: id + '_driverDiskBusTypeSelect',
                net_bus_type_id: id + '_driverNetBusTypeSelect',
            },
        }
    };

    const initContent = (el, options) => {
        // modal弹窗选项时间设置
        let osTypeOptions = ``;
        for (let key in DRIVER_OS_TYPE) {
            osTypeOptions += `<option value="${DRIVER_OS_TYPE[key]}">${key}</option>`;
        }
        let osVersionOptions = ``;
        for (let key in DRIVER_WINDOWS_OS_VERSION) {
            osVersionOptions += `<option value="${DRIVER_WINDOWS_OS_VERSION[key]}">${key}</option>`;
        }
        let osArchOptions = ``;
        for (let key in DRIVER_WINDOWS_OS_ARCH) {
            osArchOptions += `<option value="${DRIVER_WINDOWS_OS_ARCH[key]}">${key}</option>`;
        }


        let elementSet = options.element_set;
        let widthSet = options.width;
        let html = `
            <div id="${elementSet.wrapper_id}" class="driver-check__wrapper">
            <!-- 驱动检测按钮 -->
            <div class="form-group marginb8 driverCheckBtnDiv ${options.show_check_btn ? '' : 'display-none'}">
                <label class="${widthSet.config_label} text-align-reverse pt7 driver-config__label">
                    ${LANG.UI_DRIVER_CHECK}
                </label>
                <div class="${widthSet.config_content} driver-config__content">
                    <button class="btn btn-light-primary" type="button" id="${elementSet.check_driver_btn_id}">${LANG.UI_DRIVER_CHECK_BTN}</button>
                </div>
            </div>
        
            <!-- 驱动检测的主机 -->
            <div class="form-group" id="${elementSet.check_content_id}">
                <label class="${widthSet.config_label} driver-config__label"></label>
                <div class="${widthSet.config_content} driver-check-content__wrapper driver-config__content">
                    <ul id="${elementSet.check_list_id}" class="driver-check-list"></ul>
                </div>
            </div>
        
            <!-- BEGIN NODAL -->
        
            <!-- BEGIN OS TYPE CHECK MODAL -->
            <div id="${elementSet.check_modal_id}" class="modal xmodal fade form-horizontal driver-check-modal__wrapper" tabindex="-1"
                 data-focus-on="input:first" data-backdrop="static" data-uuid="" data-module-type="0">
                <div class="modal-header ">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title"><i class="viconfont vicon-gaojipeizhi me-8"></i>${LANG.UI_DRIVER_MANUAL_CONFIG_OS}</h4>
                </div>
                <div class="modal-body">
                    <div class="portlet-body">
                        <div class="row pt15">
                            <!-- 操作系统-->
                            <div class="form-group">
                                <label class="control-label col-md-3" for="${elementSet.os_type_id}">
                                    ${LANG.UI_DRIVER_OS_TYPE}
                                </label>
                                <div class="col-md-8">
                                    <select class="form-control select2me" id="${elementSet.os_type_id}">${osTypeOptions}</select>
                                </div>
                            </div>
                            <!--系统版本-->
                            <div class="form-group osVersionDiv">
                                <label class="control-label col-md-3" for="${elementSet.os_version_id}">
                                    ${LANG.UI_DRIVER_OS_VERSION}
                                </label>
                                <div class="col-md-8">
                                    <select class="form-control select2me" id="${elementSet.os_version_id}">${osVersionOptions}</select>
                                </div>
                            </div>
                            <!--系统架构-->
                            <div class="form-group osArchDiv">
                                <label class="control-label col-md-3" for="${elementSet.os_arch_id}">
                                    ${LANG.UI_DRIVER_OS_ARCH}
                                </label>
                                <div class="col-md-8">
                                    <select class="form-control select2me" id="${elementSet.os_arch_id}">${osArchOptions}</select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        
                <!-- modal-footer -->
                <div class="modal-footer">
                    <button type="button" data-dismiss="modal" class="btn btn-default">${LANG.UI_PUBLIC_CANCEL}</button>
                    <button type="button" class="btn btn-primary">${LANG.UI_PUBLIC_CONFIRM}</button>
                </div>
            </div>
            <!-- END OS TYPE CHECK MODAL -->
        
            <!-- BEGIN CHANGE BUS TYPE MODAL -->
            <div id="${elementSet.change_bus_type_modal_id}" class="modal xmodal fade form-horizontal driver-check-modal__wrapper" tabindex="-1"
                 data-focus-on="input:first" data-backdrop="static" data-uuid="">
                <div class="modal-header ">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title"><i class="viconfont vicon-gaojipeizhi me-8"></i>${LANG.UI_DRIVER_CHANGE_BUG_TYPE}</h4>
                </div>
                <div class="modal-body">
                    <div class="portlet-body">
                        <div class="row pt15">
                            <!-- 磁盘总线类型 -->
                            <div class="form-group">
                                <label class="control-label col-md-3" for="${elementSet.disk_bus_type_id}">
                                    ${LANG.UI_DRIVER_DISK_BUG_TYPE}
                                </label>
                                <div class="col-md-8">
                                    <select class="form-control select2me" id="${elementSet.disk_bus_type_id}"></select>
                                </div>
                            </div>
                            <!-- 网卡类型 -->
                            <div class="form-group">
                                <label class="control-label col-md-3" for="${elementSet.net_bus_type_id}">
                                    ${LANG.UI_DRIVER_NET_BUG_TYPE}
                                </label>
                                <div class="col-md-8">
                                    <select class="form-control select2me" id="${elementSet.net_bus_type_id}"></select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        
                <!-- modal-footer -->
                <div class="modal-footer">
                    <button type="button" data-dismiss="modal" class="btn btn-default">${LANG.UI_PUBLIC_CANCEL}</button>
                    <button type="button" class="btn btn-primary">${LANG.UI_PUBLIC_CONFIRM}</button>
                </div>
            </div>
            <!-- END CHANGE BUS TYPE MODAL -->
        
            <!-- END MODAL -->
        </div>
        `;
        $(`#${elementSet.prefix_id}`).html(html);

        // 事件注册
        // 切换操作系统事件
        $(`#${elementSet.os_type_id}`).off('change').on('change', function () {
            if ($(this).val() === DRIVER_OS_TYPE.Windows) {
                $(`#${elementSet.check_modal_id} .osVersionDiv`).show();
                $(`#${elementSet.check_modal_id} .osArchDiv`).show();
            } else {  // Linux不需要选择版本
                $(`#${elementSet.check_modal_id} .osVersionDiv`).hide();
            }
        });
        // 点击检测按钮
        $(`#${elementSet.check_driver_btn_id}`).off('click').on('click', function () {
            check(el);
        });
        // 点击选择操作系统的确认按钮
        $(`#${elementSet.check_modal_id} .modal-footer button.btn-primary`).off('click').on('click', () => {
            driverCheckModalSubmit(el);
        });
        // 隐藏检测内容
        $(`#${elementSet.check_content_id}`).hide();
        // 点击切换总线类型确认按钮
        $(`#${elementSet.change_bus_type_modal_id} .modal-footer button.btn-primary`).off('click').on('click', () => {
            driverChangeBusTypeModalSubmit(el);
        });
    };

    /**
     * 模态框提交
     */
    const driverCheckModalSubmit = (el) => {
        let driverCache = cache[el.attr('id')];
        let dataUuid = $(`#${driverCache.options.element_set.check_modal_id}`).modal('hide').data('uuid');
        let osType = $(`#${driverCache.options.element_set.os_type_id}`).val();
        let osVersion = $(`#${driverCache.options.element_set.os_version_id}`).val();
        let osArch = $(`#${driverCache.options.element_set.os_arch_id}`).val();
        if (osType === DRIVER_OS_TYPE.Linux) {  // Linux不需要选择版本和架构
            osVersion = '';
        }

        let oldReqData = driverCache.req_data;
        let reqData = {
            check_os_list: [],
            driver_usage: driverCache.options.driver_usage,
        };
        let index = -1;
        for (let _index in oldReqData.check_os_list) {
            _index = parseInt(_index);
            const checkOs = oldReqData.check_os_list[_index];
            let tmpCheckOs = {
                agent_uuid: dataUuid,
                timepoint_uuid: dataUuid,
                check_agent_flag: checkOs.check_agent_flag,
                vcenter_uuid: checkOs.vcenter_uuid,
                vm_uuid: checkOs.vm_uuid,
                check_vm_flag: checkOs.check_vm_flag,
                module_type: checkOs.module_type,
                target_type: checkOs.target_type,
                vm: checkOs.vm,
                client: checkOs.client,
                manual_config_flag: true,
                manual_bus_config_flag: checkOs.manual_bus_config_flag,
                os_type: osType,
                os_version: osVersion,
                os_arch: osArch,
                source_hypervisor_disk_bus_list: checkOs.source_hypervisor_disk_bus_list,
                source_hypervisor_net_bus_list: checkOs.source_hypervisor_net_bus_list,
                target_hypervisor_disk_bus: checkOs.target_hypervisor_disk_bus,
                target_hypervisor_net_bus: checkOs.target_hypervisor_net_bus,
                target_hypervisor_disk_bus_list: checkOs.target_hypervisor_disk_bus_list,
                target_hypervisor_net_bus_list: checkOs.target_hypervisor_net_bus_list,
            };
            if (checkOs.timepoint_uuid === dataUuid && driverCache.options.driver_usage === DRIVER_USAGE_ENUM.RECOVERY) {
                tmpCheckOs.agent_uuid = '';
                reqData.check_os_list.push(tmpCheckOs);
                oldReqData.check_os_list[_index] = tmpCheckOs;
                index = _index;
            } else if (checkOs.agent_uuid === dataUuid && driverCache.options.driver_usage === DRIVER_USAGE_ENUM.BACKUP) {
                tmpCheckOs.timepoint_uuid = '';
                reqData.check_os_list.push(tmpCheckOs);
                oldReqData.check_os_list[_index] = tmpCheckOs;
                index = _index;
            } else {
                if (index !== -1 && _index > index) {
                    reqData.check_os_list.push(checkOs);
                }
            }
        }
        if (index !== -1) {
            cache[el.attr('id')].req_data = oldReqData;
            sendDriverCheckRequest(driverCache, reqData).then(result => {
                let oldCheckResult = driverCache.check_result;
                for (let _index in result) {
                    _index = parseInt(_index);
                    oldCheckResult[index + _index] = result[_index];
                }
                initDriverContent(driverCache, driverCache.check_info, oldCheckResult)
                cache[el.attr('id')].check_result = oldCheckResult;

                // 回调
                if (typeof driverCache.options.afterCheck === 'function') {
                    driverCache.options.afterCheck($.fn.driverCheck.validCheckResult(el), $.fn.driverCheck.getCheckResult(el));
                }
            });
        }
    };

    /**
     * 修改配置模态框提交
     */
    const driverChangeBusTypeModalSubmit = (el) => {
        let driverCache = cache[el.attr('id')];
        let dataUuid = $(`#${driverCache.options.element_set.change_bus_type_modal_id}`).modal('hide').data('uuid');
        let diskBus = $(`#${driverCache.options.element_set.disk_bus_type_id}`).val();
        let netBus = $(`#${driverCache.options.element_set.net_bus_type_id}`).val();

        let oldReqData = driverCache.req_data;
        let reqData = {
            check_os_list: [],
            driver_usage: driverCache.options.driver_usage,
        };
        let index = -1;
        for (let _index in oldReqData.check_os_list) {
            _index = parseInt(_index);
            const checkOs = oldReqData.check_os_list[_index];
            let tmpCheckOs = {
                agent_uuid: dataUuid,
                timepoint_uuid: dataUuid,
                check_agent_flag: checkOs.check_agent_flag,
                vcenter_uuid: checkOs.vcenter_uuid,
                vm_uuid: checkOs.vm_uuid,
                check_vm_flag: checkOs.check_vm_flag,
                module_type: checkOs.module_type,
                target_type: checkOs.target_type,
                vm: checkOs.vm,
                client: checkOs.client,
                manual_config_flag: checkOs.manual_config_flag,
                manual_bus_config_flag: true,
                os_type: checkOs.os_type,
                os_version: checkOs.os_version,
                os_arch: checkOs.os_arch,
                source_hypervisor_disk_bus_list: checkOs.source_hypervisor_disk_bus_list,
                source_hypervisor_net_bus_list: checkOs.source_hypervisor_net_bus_list,
                target_hypervisor_disk_bus: diskBus,
                target_hypervisor_net_bus: netBus,
                target_hypervisor_disk_bus_list: checkOs.target_hypervisor_disk_bus_list,
                target_hypervisor_net_bus_list: checkOs.target_hypervisor_net_bus_list,
            };
            if (checkOs.timepoint_uuid === dataUuid && driverCache.options.driver_usage === DRIVER_USAGE_ENUM.RECOVERY) {
                tmpCheckOs.agent_uuid = '';
                reqData.check_os_list.push(tmpCheckOs);
                oldReqData.check_os_list[_index] = tmpCheckOs;
                index = _index;
            } else if (checkOs.agent_uuid === dataUuid && driverCache.options.driver_usage === DRIVER_USAGE_ENUM.BACKUP) {
                tmpCheckOs.timepoint_uuid = '';
                reqData.check_os_list.push(tmpCheckOs);
                oldReqData.check_os_list[_index] = tmpCheckOs;
                index = _index;
            } else {
                if (index !== -1 && _index > index) {
                    reqData.check_os_list.push(checkOs);
                }
            }
        }
        if (index !== -1) {
            cache[el.attr('id')].req_data = oldReqData;
            driverCache = cache[el.attr('id')];
            sendDriverCheckRequest(driverCache, reqData).then(result => {
                let oldCheckResult = driverCache.check_result;
                for (let _index in result) {
                    _index = parseInt(_index);
                    oldCheckResult[index + _index] = result[_index];
                }
                initDriverContent(driverCache, driverCache.check_info, oldCheckResult)
                cache[el.attr('id')].check_result = oldCheckResult;

                // 回调
                if (typeof driverCache.options.afterCheck === 'function') {
                    driverCache.options.afterCheck($.fn.driverCheck.validCheckResult(el), $.fn.driverCheck.getCheckResult(el));
                }
            });
        }
    };

    /**
     * 获取驱动检测的源列表
     * @param {Array<CheckSource>} sourceList
     * @param {DRIVER_USAGE_ENUM} driverUsage
     * @return {Array<CheckSource>}
     */
    const getCheckSourceList = (sourceList, driverUsage) => {
        let checkSourceList = [];
        for (const sourceInfo of sourceList) {
            let timepointUuid = '';
            if (driverUsage === DRIVER_USAGE_ENUM.RECOVERY) {
                timepointUuid = sourceInfo.timepoint_uuid;
            } else if (driverUsage === DRIVER_USAGE_ENUM.TAKEOVER) {
                timepointUuid = sourceInfo.timepoint_uuid;
            }
            if (typeof sourceInfo.source_hypervisor_disk_bus_list === 'undefined') {
                sourceInfo.source_hypervisor_disk_bus_list = [];
            }
            if (typeof sourceInfo.source_hypervisor_net_bus_list === 'undefined') {
                sourceInfo.source_hypervisor_net_bus_list = [];
            }
            if (typeof sourceInfo.target_hypervisor_disk_bus_list === 'undefined') {
                sourceInfo.target_hypervisor_disk_bus_list = [];
            }
            if (typeof sourceInfo.target_hypervisor_net_bus_list === 'undefined') {
                sourceInfo.target_hypervisor_net_bus_list = [];
            }
            if (typeof sourceInfo.os_type === 'undefined') {
                sourceInfo.os_type = '';
            }
            if (typeof sourceInfo.os_version === 'undefined') {
                sourceInfo.os_version = '';
            }
            if (typeof sourceInfo.os_arch === 'undefined') {
                sourceInfo.os_arch = '';
            }
            if (typeof sourceInfo.check_agent_flag === 'undefined') {
                if (
                    driverUsage === DRIVER_USAGE_ENUM.BACKUP ||
                    driverUsage === DRIVER_USAGE_ENUM.TAKEOVER
                ) {
                    sourceInfo.check_agent_flag = true;
                } else {
                    sourceInfo.check_agent_flag = false;
                }
            }
            if (typeof sourceInfo.agent_uuid === 'undefined') {
                sourceInfo.agent_uuid = '';
            }
            if (typeof sourceInfo.check_vm_flag === 'undefined') {
                sourceInfo.check_vm_flag = false;
            }
            if (typeof sourceInfo.vcenter_uuid === 'undefined') {
                sourceInfo.vcenter_uuid = '';
            }
            if (typeof sourceInfo.vm_uuid === 'undefined') {
                sourceInfo.vm_uuid = '';
            }
            checkSourceList.push({
                name: sourceInfo.name,
                timepoint_uuid: timepointUuid,
                module_type: parseInt(sourceInfo.module_type),
                agent_uuid: sourceInfo.agent_uuid,
                vcenter_uuid: sourceInfo.vcenter_uuid,
                vm_uuid: sourceInfo.vm_uuid,
                source_hypervisor_disk_bus_list: sourceInfo.source_hypervisor_disk_bus_list,
                source_hypervisor_net_bus_list: sourceInfo.source_hypervisor_net_bus_list,
                target_hypervisor_disk_bus_list: sourceInfo.target_hypervisor_disk_bus_list,
                target_hypervisor_net_bus_list: sourceInfo.target_hypervisor_net_bus_list,
                os_type: sourceInfo.os_type,
                os_version: sourceInfo.os_version,
                os_arch: sourceInfo.os_arch,
                check_agent_flag: sourceInfo.check_agent_flag,
                check_vm_flag: sourceInfo.check_vm_flag,
            });
        }
        return checkSourceList;
    };

    /**
     * 获取检测目标信息
     * @param {CheckTarget} targetInfo
     * @param {DRIVER_USAGE_ENUM} driverUsage
     * @return {CheckTarget}
     */
    const getCheckTargetInfo = (targetInfo, driverUsage) => {
        let targetType = parseInt(targetInfo.target_type);
        let checkTargetInfo = {
            target_type: targetType,
        };
        if (driverUsage === DRIVER_USAGE_ENUM.TAKEOVER) {
            checkTargetInfo.target_type = DRIVER_TARGET_TYPE.VM;
            checkTargetInfo.vm_info = {
                vm_type: '',
                vcenter_uuid: '',
                host_uuid: '',
                disk_bus_list: ['4'],
                default_disk_bus: '4',
                net_bus_list: ['4'],
                default_net_bus: '4',
                bus_changeable_flag: false,
                show_bus_flag: false,
            };
            return checkTargetInfo;
        }
        if (targetType === DRIVER_TARGET_TYPE.VM) {
            let busChangeableFlag = true;
            let showBusFlag = false;
            let defaultDiskBus = targetInfo.vm_info.default_disk_bus === undefined ? '' : targetInfo.vm_info.default_disk_bus;
            let defaultNetBus = targetInfo.vm_info.default_net_bus === undefined ? '' : targetInfo.vm_info.default_net_bus;
            // 默认值设置
            if (typeof targetInfo.vm_info.disk_bus_list === 'undefined') {
                targetInfo.vm_info.disk_bus_list = [{name: '', value: ''}];
            }
            if (typeof targetInfo.vm_info.net_bus_list === 'undefined') {
                targetInfo.vm_info.net_bus_list = [{name: '', value: ''}];
            }
            if (typeof targetInfo.vm_info.show_bus_flag !== 'undefined') {
                showBusFlag = !!targetInfo.vm_info.show_bus_flag;
            }
            if (typeof targetInfo.vm_info.bus_changeable_flag !== 'undefined') {
                busChangeableFlag = targetInfo.vm_info.bus_changeable_flag;
                if (busChangeableFlag) {
                    let findDiskBusFlag = false;
                    for (const diskBusInfo of targetInfo.vm_info.disk_bus_list) {
                        if (diskBusInfo.value == defaultDiskBus) {
                            findDiskBusFlag = true;
                            break;
                        }
                    }
                    if (!findDiskBusFlag) {
                        defaultDiskBus = targetInfo.vm_info.disk_bus_list[0].value;
                    }
                    let findNetBusFlag = false;
                    for (const netBusInfo of targetInfo.vm_info.net_bus_list) {
                        if (netBusInfo.value == defaultNetBus) {
                            findNetBusFlag = true;
                            break;
                        }
                    }
                    if (!findNetBusFlag) {
                        defaultNetBus = targetInfo.vm_info.net_bus_list[0].value;
                    }
                }
            }
            checkTargetInfo.vm_info = {
                vm_type: parseInt(targetInfo.vm_info.vm_type),
                vcenter_uuid: targetInfo.vm_info.vcenter_uuid,
                host_uuid: targetInfo.vm_info.host_uuid,
                disk_bus_list: targetInfo.vm_info.disk_bus_list,
                default_disk_bus: defaultDiskBus,
                net_bus_list: targetInfo.vm_info.net_bus_list,
                default_net_bus: defaultNetBus,
                bus_changeable_flag: busChangeableFlag,
                show_bus_flag: showBusFlag,
            };
        } else {
            checkTargetInfo.client_info = {
                agent_uuid: targetInfo.client_info.agent_uuid,
            };
        }
        return checkTargetInfo;
    };

    /**
     * 获取检测信息
     * @param {CheckObject} checkInfo
     * @param {DRIVER_USAGE_ENUM} driverUsage
     * @return {CheckObject}
     */
    const getCheckInfo = (checkInfo, driverUsage) => {
        return {
            source_list: getCheckSourceList(checkInfo.source_list, driverUsage),
            target_info: getCheckTargetInfo(checkInfo.target_info, driverUsage),
        };
    };

    const getDiskBusName = (diskBusList, diskType) => {
        for (const diskBusInfo of diskBusList) {
            if (diskBusInfo.value == diskType) {
                return diskBusInfo.name;
            }
        }
        return diskType;
    };

    const getNetBusName = (netBusList, netType) => {
        for (const netBusInfo of netBusList) {
            if (netBusInfo.value == netType) {
                return netBusInfo.name;
            }
        }
        return netType;
    };

    /**
     * 初始化检测内容
     * @param driverCache
     * @param {CheckObject} checkInfo
     * @param {Array<DriverResponseCheckResult>} checkResult
     */
    const initDriverContent = (driverCache, checkInfo, checkResult = null) => {
        // 初始化总线类型
        if (checkInfo.target_info.target_type === DRIVER_TARGET_TYPE.VM) {
            let options = driverCache.options;
            let elements = ``;
            for (const diskBusInfo of checkInfo.target_info.vm_info.disk_bus_list) {
                elements += `<option value="${diskBusInfo.value}">${diskBusInfo.name}</option>`;
            }
            $(`#${options.element_set.disk_bus_type_id}`).html(elements);
            elements = ``;
            for (const netBusInfo of checkInfo.target_info.vm_info.net_bus_list) {
                elements += `<option value="${netBusInfo.value}">${netBusInfo.name}</option>`;
            }
            $(`#${options.element_set.net_bus_type_id}`).html(elements);
        }

        let checkOsList = driverCache.req_data.check_os_list;

        // 显示检测内容
        $(`#${driverCache.options.element_set.check_content_id}`).show();
        /**
         * @type {Array<RenderObject>}
         */
        let renderList = [];
        let selectOsFlag = false;
        for (const index in checkInfo.source_list) {
            let sourceInfo = checkInfo.source_list[index];
            let checkResultInfo = checkResult !== null ? checkResult[index] : null;
            let checkOsInfo = null;
            let dataUuid = sourceInfo.timepoint_uuid;
            for (const checkOs of checkOsList) {
                if (sourceInfo.timepoint_uuid === checkOs.timepoint_uuid && driverCache.options.driver_usage === DRIVER_USAGE_ENUM.RECOVERY) {
                    checkOsInfo = checkOs;
                    break;
                } else if (sourceInfo.agent_uuid === checkOs.agent_uuid && driverCache.options.driver_usage === DRIVER_USAGE_ENUM.BACKUP) {
                    checkOsInfo = checkOs;
                    dataUuid = sourceInfo.agent_uuid;
                    break;
                }
            }
            let diskBus = '';
            let diskBusName = '';
            let netBus = '';
            let netBusName = '';
            let busChangeableFlag = true;
            let showBusFlag = false;
            if (checkInfo.target_info.target_type === DRIVER_TARGET_TYPE.VM) {
                diskBus = checkOsInfo.target_hypervisor_disk_bus;
                diskBusName = getDiskBusName(checkInfo.target_info.vm_info.disk_bus_list, diskBus);
                netBus = checkOsInfo.target_hypervisor_net_bus;
                netBusName = getNetBusName(checkInfo.target_info.vm_info.net_bus_list, netBus);
                busChangeableFlag = !!checkInfo.target_info.vm_info.bus_changeable_flag;
                showBusFlag = !!checkInfo.target_info.vm_info.show_bus_flag;
            }

            renderList.push({
                name: sourceInfo.name,
                data_uuid: dataUuid,
                agent_uuid: sourceInfo.agent_uuid,
                timepoint_uuid: sourceInfo.timepoint_uuid,
                module_type: parseInt(sourceInfo.module_type),
                platform_type: checkResult !== null ? parseInt(checkResultInfo.platform_type) : DRIVER_PLATFORM_TYPE.UNKNOWN,
                show_platform: checkResult !== null && !selectOsFlag,
                diff_platform_flag: checkResultInfo === null ? false : parseInt(checkResultInfo.platform_type) === DRIVER_PLATFORM_TYPE.DIFF,
                show_driver: checkResultInfo !== null && !selectOsFlag,  // 非异构平台才显示
                check_driver_status: checkResultInfo === null ? DRIVER_CHECK_RESULT.PASS : parseInt(checkResultInfo.driver_check_status),
                result_detail: checkResultInfo === null ? '' : checkResultInfo.lack_driver_info,
                disk_bus: diskBus,
                disk_bus_name: diskBusName,
                net_bus: netBus,
                net_bus_name: netBusName,
                target_type: checkInfo.target_info.target_type,
                bus_changeable_flag: busChangeableFlag,
                show_bus_flag: showBusFlag,
            });
            if (
                !selectOsFlag &&
                checkResultInfo !== null &&
                parseInt(checkResultInfo.platform_type) === DRIVER_PLATFORM_TYPE.UNKNOWN &&
                parseInt(checkResultInfo.driver_check_status) === DRIVER_CHECK_RESULT.NO_OS
            ) {  // 表示需要选择操作系统
                selectOsFlag = true;
            }
        }
        renderContent(driverCache, renderList);
    };

    /**
     * 渲染页面数据
     * @param driverCache
     * @param {Array<RenderObject>} renderList
     */
    const renderContent = (driverCache, renderList) => {
        // 配置
        let options = driverCache.options;
        let widthSet = options.width;
        // 移除当前存在的检测对象
        $(`#${options.element_set.check_list_id} li.driver-check-item`).remove();
        let showDriverResult = false;
        let driverResultDetail = [];
        let html = ``;
        for (const renderInfo of renderList) {
            let platformName = ``;
            let platformTitle = ``;
            let driverName = ``;
            let driverTitle = ``;
            if (renderInfo.show_platform) {
                switch (renderInfo.platform_type) {
                    case DRIVER_PLATFORM_TYPE.SAME:
                        platformName = `<i class="viconfont vicon-Frame-15 check-success"></i>${LANG.UI_DRIVER_PLATFORM_SAME}`;
                        platformTitle = LANG.UI_DRIVER_PLATFORM_SAME;
                        break;
                    case DRIVER_PLATFORM_TYPE.DIFF:
                        platformName = `<i class="viconfont vicon-Frame-15 check-success"></i>${LANG.UI_DRIVER_PLATFORM_DIFFERENT}`;
                        platformTitle = LANG.UI_DRIVER_PLATFORM_DIFFERENT;
                        break;
                    default:
                        break;
                }
            }
            if (renderInfo.show_driver) {
                switch (renderInfo.check_driver_status) {
                    case DRIVER_CHECK_RESULT.PASS:
                        driverName = `<i class="viconfont vicon-Frame-15 check-success"></i>${LANG.UI_DRIVER_CHECK_RESULT_PASS}`;
                        driverTitle = LANG.UI_DRIVER_CHECK_RESULT_PASS;
                        break;
                    case DRIVER_CHECK_RESULT.NO_OS:
                        let buttonEle = `<button type="button" class="link btn btn-href-primary" data-uuid="${renderInfo.data_uuid}" data-module-type="${renderInfo.module_type}"><a>${LANG.UI_DRIVER_CHECK_SELECT_OS}</a></button>`;
                        driverName = `<i class="viconfont vicon-Frame6 check-error"></i><span style="padding-right: 8px">${LANG.UI_DRIVER_CHECK_RESULT_NO_OS}</span>${buttonEle}`;
                        driverTitle = LANG.UI_DRIVER_CHECK_RESULT_NO_OS;
                        break;
                    case DRIVER_CHECK_RESULT.NO_TIMEPOINT:
                        driverName = `<i class="viconfont vicon-Frame6 check-error"></i>${LANG.UI_DRIVER_CHECK_RESULT_NO_TIMEPOINT}`;
                        driverTitle = LANG.UI_DRIVER_CHECK_RESULT_NO_TIMEPOINT;
                        break;
                    case DRIVER_CHECK_RESULT.NO_CLIENT:
                        driverName = `<i class="viconfont vicon-Frame6 check-error"></i>${LANG.UI_DRIVER_CHECK_RESULT_NO_CLIENT}`;
                        driverTitle = LANG.UI_DRIVER_CHECK_RESULT_NO_CLIENT;
                        break;
                    case DRIVER_CHECK_RESULT.CLIENT_LACK_HW:
                        driverName = `<i class="viconfont vicon-Frame6 check-error"></i>${LANG.UI_DRIVER_CHECK_RESULT_CLIENT_LACK_HW}`;
                        driverTitle = LANG.UI_DRIVER_CHECK_RESULT_CLIENT_LACK_HW;
                        break;
                    case DRIVER_CHECK_RESULT.NONSUPPORT_OS_INSTALL_DRIVER:
                        driverName = `<i class="viconfont vicon-Frame6 check-error"></i>${LANG.UI_DRIVER_CHECK_RESULT_NONSUPPORT_OS_INSTALL_DRIVER}`;
                        driverTitle = LANG.UI_DRIVER_CHECK_RESULT_NONSUPPORT_OS_INSTALL_DRIVER;
                        break;
                    default:
                        driverName = `<i class="viconfont vicon-Frame6 check-error"></i>${LANG.UI_DRIVER_CHECK_RESULT_NO_DRIVER}`;
                        driverTitle = LANG.UI_DRIVER_CHECK_RESULT_NO_DRIVER;
                        showDriverResult = true;
                        driverResultDetail.push(renderInfo.result_detail);
                        break;
                }
            }
            if (renderInfo.target_type === DRIVER_TARGET_TYPE.VM) {
                let changeLink = `<a class="change-bus-type" data-uuid="${renderInfo.data_uuid}"
                                    data-disk-bus="${renderInfo.disk_bus}" data-net-bus="${renderInfo.net_bus}">${LANG.UI_DRIVER_CHANGE_BUG_TYPE}</a>`;
                if (!renderInfo.bus_changeable_flag) {
                    changeLink = ``;
                }
                let platFormHtml = ``;
                if (renderInfo.show_platform && renderInfo.platform_type !== DRIVER_PLATFORM_TYPE.UNKNOWN) {
                    platFormHtml = `
                    <div class="driver-check-item__platform ${widthSet.check_platform}">
                        <span class="text" title="${platformTitle}">${platformName}</span>
                    </div>
                    `;
                }
                let descriptionHtml = ``;
                if (renderInfo.show_bus_flag) {
                    descriptionHtml += `<span class="description">${LANG.UI_DRIVER_DISK_BUG_TYPE}: ${renderInfo.disk_bus_name}，${LANG.UI_DRIVER_NET_BUG_TYPE}: ${renderInfo.net_bus_name}${changeLink}</span>`;
                }
                html += `
                <li class="driver-check-item gap-20_en">
                    <div class="driver-check-item__name ${widthSet.check_name}">
                        <span class="text" title="${renderInfo.name}">${renderInfo.name}</span>
                        ${descriptionHtml}
                    </div>
                    ${platFormHtml}
                    <div class="driver-check-item__driver ${widthSet.check_driver}">
                        <span class="text" title="${driverTitle}">${driverName}</span>
                    </div>
                </li>
                `;
            } else {
                let platFormHtml = ``;
                if (renderInfo.show_platform && renderInfo.platform_type !== DRIVER_PLATFORM_TYPE.UNKNOWN) {
                    platFormHtml = `
                    <div class="driver-check-item__platform ${widthSet.check_platform}">
                        <span class="text" title="${platformTitle}">${platformName}</span>
                    </div>
                    `;
                }
                html += `
                <li class="driver-check-item gap-20_en">
                    <div class="driver-check-item__name ${widthSet.check_name}">
                        <span class="text" title="${renderInfo.name}">${renderInfo.name}</span>
                    </div>
                    ${platFormHtml}
                    <div class="driver-check-item__driver ${widthSet.check_driver}">
                        <span class="text" title="${driverTitle}">${driverName}</span>
                    </div>
                </li>
                `;
            }
        }
        if (showDriverResult) {
            driverResultDetail = driverResultDetail.join(',');
            html += `
            <div class="driver-check-result">
                <div class="driver-check-result__name ${widthSet.result_name}">
                    <span class="text" title="${LANG.UI_DRIVER_CHECK_RESULT_DETAIL}">${LANG.UI_DRIVER_CHECK_RESULT_DETAIL}</span>
                </div>
                <div class="driver-check-result__detail ${widthSet.result_detail}">
                    <span class="text" title="${driverResultDetail}">${driverResultDetail}</span>
                </div>
                <div class="driver-check-result__upload ${widthSet.result_upload}">
                    <span class="text">
                        <button type="button" class="link btn btn-href-primary"><a>${LANG.UI_DRIVER_CHECK_RESULT_UPLOAD}</a></button>
                    </span>
                </div>
            </div>
            `;
        }
        $(`#${options.element_set.check_list_id}`).html(html);

        // 注册事件
        $(`#${options.element_set.check_list_id} .driver-check-result .driver-check-result__upload button.link`).off('click').on('click', () => {
            // FIXME: 打开新的页面上传驱动
            let routeModule = btoa('resmanagement', true);
            let routeName = btoa('backup_manager', true);
            let subRouteName = btoa('driver_manager', true);
            window.open(`?force_route_jump=1&module=${routeModule}&name=${routeName}&sub_name=${subRouteName}`, '_blank');
        });
        // 选择操作系统
        $(`#${options.element_set.check_list_id} .driver-check-item__driver button.link`).off('click').on('click', function () {
            $(`#${options.element_set.check_modal_id}`)
                .data('uuid', $(this).data('uuid'))
                .data('module-type', $(this).data('module-type'))
                .modal({width: "693px", height: "198px"});
            // 设置当前模态为最顶层
            setTimeout(() => {
                let $parent = $(`#${options.element_set.check_modal_id}`).closest('div.modal-scrollable');
                $parent.css('z-index', '99999');
                let $modalBackdrop = $parent.next('div.modal-backdrop.fade.in');
                $modalBackdrop.css('z-index', '99998');
            }, 0);
            if (parseInt($(this).data('module-type')) === CONF.MODULE_TYPE.VM) {  // 虚拟化可以选择Windows和Linux
                $(`#${options.element_set.os_type_id}`).val(DRIVER_OS_TYPE.Windows).removeAttr('disabled').trigger('change');
                $(`#${options.element_set.os_version_id}`).val(DRIVER_WINDOWS_OS_VERSION["Windows 10"]);
                $(`#${options.element_set.os_arch_id}`).val(DRIVER_WINDOWS_OS_ARCH.amd64);
            } else {  // 操作系统只能选择Windows
                $(`#${options.element_set.os_type_id}`).val(DRIVER_OS_TYPE.Windows).attr('disabled', 'disabled').trigger('change');
                $(`#${options.element_set.os_version_id}`).val(DRIVER_WINDOWS_OS_VERSION["Windows 10"]);
                $(`#${options.element_set.os_arch_id}`).val(DRIVER_WINDOWS_OS_ARCH.amd64);
            }
        });
        // 配置总线类型
        $(`#${options.element_set.check_list_id} .driver-check-item .description a`).off('click').on('click', function () {
            $(`#${options.element_set.disk_bus_type_id}`).val($(this).data('disk-bus'));
            $(`#${options.element_set.net_bus_type_id}`).val($(this).data('net-bus'));
            $(`#${options.element_set.change_bus_type_modal_id}`)
                .data('uuid', $(this).data('uuid'))
                .modal({width: "693px", height: "198px"});
        });
    };

    /**
     * 构建请求的数据
     * @param {CheckObject} checkInfo
     * @param {DRIVER_USAGE_ENUM} driverUsage
     * @return {DriverRequestData}
     */
    const buildRequestData = (checkInfo, driverUsage) => {
        return {
            check_os_list: checkInfo.source_list.map(sourceInfo => {
                let diskBus = '';
                let netBus = '';
                let sourceDiskBusList = [];
                let sourceNetBusList = [];
                let diskBusList = [];
                let netBusList = [];
                let manualConfigFlag = false;
                let vm = {};
                if (driverUsage === DRIVER_USAGE_ENUM.TAKEOVER) {
                    diskBusList = ['4'];
                    netBusList = ['4'];
                } else {
                    if (parseInt(sourceInfo.module_type) === CONF.MODULE_TYPE.VM) {
                        sourceDiskBusList = sourceInfo.source_hypervisor_disk_bus_list;
                        sourceNetBusList = sourceInfo.source_hypervisor_net_bus_list;
                    }
                    if (checkInfo.target_info.target_type === DRIVER_TARGET_TYPE.VM) {
                        diskBus = checkInfo.target_info.vm_info.default_disk_bus;
                        netBus = checkInfo.target_info.vm_info.default_net_bus;
                        diskBusList = sourceInfo.target_hypervisor_disk_bus_list;
                        netBusList = sourceInfo.target_hypervisor_net_bus_list;
                        manualConfigFlag = true;  // 虚拟化目标直接从外面传入系统类型、版本、架构信息
                        vm = {
                            vm_type: checkInfo.target_info.vm_info.vm_type,
                            vcenter_uuid: checkInfo.target_info.vm_info.vcenter_uuid,
                            host_uuid: checkInfo.target_info.vm_info.host_uuid,
                        };
                    }
                }
                return {
                    timepoint_uuid: sourceInfo.timepoint_uuid,
                    agent_uuid: sourceInfo.agent_uuid,
                    check_agent_flag: sourceInfo.check_agent_flag,
                    vcenter_uuid: sourceInfo.vcenter_uuid,
                    vm_uuid: sourceInfo.vm_uuid,
                    check_vm_flag: sourceInfo.check_vm_flag,
                    module_type: sourceInfo.module_type,
                    target_type: checkInfo.target_info.target_type,
                    vm,
                    client: checkInfo.target_info.client_info,
                    manual_config_flag: manualConfigFlag,
                    manual_bus_config_flag: false,
                    os_type: sourceInfo.os_type,
                    os_version: sourceInfo.os_version,
                    os_arch: sourceInfo.os_arch,
                    source_hypervisor_disk_bus_list: sourceDiskBusList,
                    source_hypervisor_net_bus_list: sourceNetBusList,
                    target_hypervisor_disk_bus: diskBus,
                    target_hypervisor_net_bus: netBus,
                    target_hypervisor_disk_bus_list: diskBusList,
                    target_hypervisor_net_bus_list: netBusList,
                };
            }),
            driver_usage: driverUsage,
        };
    };

    /**
     * 发送驱动检测请求
     * @param driverCache
     * @param {DriverRequestData} reqData
     * @return Promise<Array<DriverResponseCheckResult>>
     */
    const sendDriverCheckRequest = (driverCache, reqData) => {
        return new Promise((resolve, reject) => {
            Metronic.blockUI({target: `#${driverCache.options.element_set.check_list_id}`, animate: true});
            pAjaxRequest(reqData, `/api/v1/drivers/check`, 'POST', result /** @param {DriverResponse} result */ => {
                Metronic.unblockUI(`#${driverCache.options.element_set.check_list_id}`);
                if (!result.success) {
                    UIToastr.showWarning(LANG.UI_DRIVER_CHECK, result.message);
                    reject();
                } else {
                    /**
                     * @type {null|?Array<DriverResponseCheckResult>|*}
                     */
                    let checkResult = result.data.check_result;
                    for (const index in checkResult) {
                        let tmp = checkResult[index];
                        let checkOs = reqData.check_os_list[index];
                        let diffPlatformType = DRIVER_PLATFORM_TYPE.DIFF;
                        if (parseInt(tmp.platform_type) === DRIVER_PLATFORM_TYPE.DIFF) {
                            diffPlatformType = DRIVER_PLATFORM_TYPE.SAME;
                        }
                        let vmInfo = {
                            disk_bus_list: [],
                            net_bus_list: [],
                        };
                        if (driverCache.options.driver_usage !== DRIVER_USAGE_ENUM.TAKEOVER) {
                            if (checkOs.target_type === DRIVER_TARGET_TYPE.VM) {
                                vmInfo = driverCache.check_info.target_info.vm_info;
                            }
                        }
                        tmp.check_agent_flag = checkOs.check_agent_flag;
                        tmp.vcenter_uuid = checkOs.vcenter_uuid;
                        tmp.vm_uuid = checkOs.vm_uuid;
                        tmp.check_vm_flag = checkOs.check_vm_flag;
                        tmp.module_type = checkOs.module_type;
                        tmp.target_type = checkOs.target_type;
                        tmp.vm = checkOs.vm;
                        tmp.client = checkOs.client;
                        tmp.manual_bus_config_flag = checkOs.manual_bus_config_flag;
                        tmp.os_type = checkOs.os_type;
                        tmp.os_version = checkOs.os_version;
                        tmp.os_arch = checkOs.os_arch;
                        tmp.source_hypervisor_disk_bus_list = checkOs.source_hypervisor_disk_bus_list;
                        tmp.source_hypervisor_net_bus_list = checkOs.source_hypervisor_net_bus_list;
                        // tmp.target_hypervisor_disk_bus = checkOs.target_hypervisor_disk_bus;
                        // tmp.target_hypervisor_disk_bus_name = getDiskBusName(vmInfo.disk_bus_list, checkOs.target_hypervisor_disk_bus);
                        // tmp.target_hypervisor_net_bus = checkOs.target_hypervisor_net_bus;
                        // tmp.target_hypervisor_net_bus_name = getNetBusName(vmInfo.net_bus_list, checkOs.target_hypervisor_net_bus);
                        tmp.target_hypervisor_disk_bus_list = checkOs.target_hypervisor_disk_bus_list;
                        tmp.target_hypervisor_net_bus_list = checkOs.target_hypervisor_net_bus_list;
                        tmp.agent_uuid = checkOs.agent_uuid;
                        tmp.timepoint_uuid = checkOs.timepoint_uuid;
                        tmp.diff_platform_type = diffPlatformType;
                        checkResult[index] = tmp;
                    }
                    resolve(checkResult);
                }
            });
        });
    };

    /**
     * 检测驱动
     * @param el
     */
    const check = el => {
        let driverCache = cache[el.attr('id')];
        let checkInfo = getCheckInfo(driverCache.options.getCheckObject(), driverCache.options.driver_usage);
        // 整理数据
        let reqData = buildRequestData(checkInfo, driverCache.options.driver_usage);
        if (driverCache.options.driver_usage === DRIVER_USAGE_ENUM.TAKEOVER) {
            // 发送检查请求
            sendDriverCheckRequest(driverCache, reqData).then(result => {
                // 渲染页面
                cache[el.attr('id')].check_result = result;
    
                // 回调
                if (typeof driverCache.options.afterCheck === 'function') {
                    driverCache.options.afterCheck(true, $.fn.driverCheck.getCheckResult(el));
                }
            });
            return;
        } else {
            cache[el.attr('id')].req_data = reqData;
            driverCache = cache[el.attr('id')];
            // 初始化页面
            initDriverContent(driverCache, checkInfo);
            // 发送检查请求
            sendDriverCheckRequest(driverCache, reqData).then(result => {
                // 渲染页面
                initDriverContent(driverCache, checkInfo, result);
                cache[el.attr('id')].check_result = result;
    
                // 回调
                if (typeof driverCache.options.afterCheck === 'function') {
                    driverCache.options.afterCheck($.fn.driverCheck.validCheckResult(el), result);
                }
            });
            cache[el.attr('id')].check_info = checkInfo;
        }
    };

    $.fn.driverCheck = {
        /**
         * 初始化
         * @param {jQuery} el 元素
         * @param {DriverOption} options 配置选项
         */
        init: (el, options) => {
            options = getDriverOptions(el, options);
            if (options.driver_usage !== DRIVER_USAGE_ENUM.TAKEOVER) {
                initContent(el, options);
            }
            cache[options.element_set.prefix_id] = {
                options,
                req_data: null,
                check_result: null,
                check_info: null,
            }
        },
        /**
         * 手动检测驱动
         */
        check,
        /**
         * 获取驱动检查结果
         * @return {null|Array<DriverResponseCheckResult>}
         */
        getCheckResult: (el) => {
            return cache[el.attr('id')].check_result;
        },
        /**
         * 验证驱动检查是否都通过
         */
        validCheckResult: (el) => {
            let checkResult = cache[el.attr('id')].check_result;
            if (checkResult === null) {
                return false;
            }
            for (const checkInfo of checkResult) {
                // if (parseInt(checkInfo.platform_type) !== DRIVER_PLATFORM_TYPE.SAME) {  // 异构平台不能恢复
                //     return false;
                // }
                if (checkInfo.driver_check_status !== DRIVER_CHECK_RESULT.PASS) {
                    return false;
                }
            }
            return true;
        },
        DRIVER_TARGET_TYPE,
        DRIVER_CHECK_RESULT,
        DRIVER_CHECK_RESULT_LANG,
        DRIVER_OS_TYPE,
        DRIVER_WINDOWS_OS_VERSION,
        DRIVER_WINDOWS_OS_ARCH,
        DRIVER_USAGE_ENUM,
        DRIVER_PLATFORM_TYPE,
    };
})(jQuery, Metronic, UIToastr, LANG);