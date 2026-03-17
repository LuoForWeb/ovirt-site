/**
 * @author JackC
 * @description 网络配置组件
 * ////////////////////////////////////
 * 依赖插件
 * <script src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js" type="text/javascript"></script>
 * <script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
 */
(($, LANG) => {
    /**
     * @typedef Option - 选项
     * @property {String} [label_width=col-md-3] 标签的长度
     * @property {String} [input_width=col-md-6] 文本框的长度
     * @property {String} [input_table_width=col-md-8] 文本框的长度
     * @property {IpConfig} [ip_config={}] ip配置
     * @property {Object} [element_set={}] 页面ID设置
     */

    /**
     * @typedef IpConfig - IP配置
     * @property {Ipv4v6} [ipv4={}] ipv4配置
     * @property {Ipv4v6} [ipv6={}] ipv6配置
     */

    /**
     * @typedef Ipv4v6 - IP配置
     * @property {Number|String} [config_type=1] 配置类型【1自动 2手动】
     * @property {Array<IpSet>} [ip_set=[]] IP设置
     * @property {String} [gateway=''] 默认网关
     * @property {String} [dns1=''] DNS1
     * @property {String} [dns2=''] DNS2
     */

    /**
     * @typedef IpSet - IP设置
     * @property {String} [ip=''] ip地址
     * @property {String} [netmask=''] 子网掩码
     */

    let optionCaches = [];

    // 枚举定义
    const CONFIG_TYPE_ENUM = {
        auto: 1,  // 自动
        manual: 2,  // 手动
    };

    // 用途
    const USAGE_TYPE_ENUM = {
        DEFAULT: 1,  // 默认
        CLIENT: 2,  // 恢复到客户端
        VM: 3,  // 虚拟机
        CUSTOM_VM: 4,  // 内嵌虚拟化
    };

    // MAC类型
    const MAC_TYPE_ENUM = {
        AUTO: 1, // 自动生成
        CUSTOM: 2,  // 自定义MAC
        REMAIN: 3,  // 保留MAC
    };
    // MAC类型映射
    const MAC_TYPE_LANG_MAP = {
        1: LANG.UI_VM_SETTING_V2_AUTO_PRODUCT,
        2: LANG.UI_VM_SETTING_DIY_MAC,
        3: LANG.UI_VM_SETTING_SAVE_MAC,
    };

    /**
     * IP类型
     */
    const IP_TYPE_ENUM = {
        IPv4: 1,
        IPv6: 2,
    };

    //////////////////////////////// 枚举定义结束 ////////////////////////////////

    //////////////////////////////// 开始-IP验证定义 ////////////////////////////////

    /**
     * 验证IPv4子网掩码格式（点分十进制或CIDR表示，不含/）
     */
    const validateIPv4SubnetMask = mask => {
        const decimalMaskPattern = /^(255|254|252|248|240|224|192|128|0)\.(255|254|252|248|240|224|192|128|0)\.(255|254|252|248|240|224|192|128|0)\.(255|254|252|248|240|224|192|128|0)$/;
        const cidrPattern = /^([0-9]|[12][0-9]|3[0-2])$/;
        return decimalMaskPattern.test(mask) || cidrPattern.test(mask);
    };

    /**
     * 将IPv4子网掩码转换为点分十进制表示
     */
    const convertToIPv4Decimal = mask => {
        if (!validateIPv4SubnetMask(mask)) {
            return '';
        }

        if (/^\d+$/.test(mask)) {
            const cidr = parseInt(mask, 10);
            let binaryMask = '1'.repeat(cidr) + '0'.repeat(32 - cidr);
            let decimalMask = [];

            for (let i = 0; i < 4; i++) {
                decimalMask.push(parseInt(binaryMask.slice(i * 8, i * 8 + 8), 2));
            }

            return decimalMask.join('.');
        }

        return mask; // Already in dot-decimal format
    };

    /**
     * 验证IPv6子网掩码格式
     */
    const validateIPv6SubnetMask = mask => {
        // Validate CIDR notation
        if (validateIPv6SubnetMaskCIDR(mask)) {
            return true;
        }
        return validateIpv6(mask);
    };

    /**
     * 验证IPv6子网掩码CIDR形式
     */
    const validateIPv6SubnetMaskCIDR = mask => {
        // Validate CIDR notation
        const cidrPattern = /^([0-9]|[1-9]\d|1[0-1]\d|12[0-8])$/;
        if (cidrPattern.test(mask)) {
            return true;
        }
        return false;
    };

    /**
     * 将IPv6子网掩码转换为标准表示（缩写形式）
     */
    const convertToIPv6String = mask => {
        if (!validateIPv6SubnetMask(mask)) {
            return '';
        }

        if (validateIpv6(mask)) {
            return mask;
        }

        const cidr = parseInt(mask, 10);
        let binaryMask = '1'.repeat(cidr) + '0'.repeat(128 - cidr);
        let hexMask = [];

        for (let i = 0; i < 8; i++) {
            hexMask.push(parseInt(binaryMask.slice(i * 16, i * 16 + 16), 2).toString(16));
        }

        // Return in compressed form (replace consecutive sections of "0" with "::")
        return hexMask.join(':').replace(/(^|:)0(:0)*(:|$)/, '::').replace(/:{3,}/g, '::');
    };

    /**
     * 将标准形式的IPv6子网掩码转换为CIDR形式
     * @param {string} mask - IPv6子网掩码（如 "ffff:ffff:ffff:ffff::"）
     * @returns {number} CIDR表示（如 64）
     */
    const convertIPv6MaskToCIDR = mask => {
        if (!mask) return 0;

        // 将IPv6地址展开为完整的8段形式
        const expandIPv6 = (ip) => {
            const parts = ip.split('::');
            let left = parts[0] ? parts[0].split(':') : [];
            let right = parts[1] ? parts[1].split(':') : [];

            const missing = 8 - (left.length + right.length);
            return left.concat(Array(missing).fill('0')).concat(right);
        };

        // 将16进制段转换为16位二进制字符串
        const hexToBinary = (hex) => {
            return parseInt(hex, 16).toString(2).padStart(16, '0');
        };

        try {
            const segments = expandIPv6(mask);
            let binaryStr = '';

            for (const segment of segments) {
                binaryStr += hexToBinary(segment);
            }

            // 计算连续1的个数
            const cidr = binaryStr.indexOf('0');
            return cidr === -1 ? 128 : cidr;
        } catch (e) {
            console.error('Invalid IPv6 subnet mask:', e);
            return 0;
        }
    };

    /**
     * 验证IPv4地址格式
     */
    const validateIpv4 = ip => {
        // Regular expression for IPv4
        const ipv4Pattern = /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;

        return ipv4Pattern.test(ip);
    };

    /**
     * 验证IPv6地址格式
     */
    const validateIpv6 = ip => {
        // Regular expression for IPv6
        const ipv6Pattern = /^(([0-9a-fA-F]{1,4}:){7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:))$/;

        return ipv6Pattern.test(ip);
    };

    //////////////////////////////// 结束-IP验证定义 ////////////////////////////////

    //////////////////// 开始-获取配置 ////////////////////

    /**
     * 获取默认配置
     * @param {Ipv4v6} ipSet
     */
    const getIpSetConfig = (ipSet) => {
        if (ipSet === undefined || !ipSet) {
            return {
                config_type: 1,
                ip_set: [],
                gateway: '',
                dns1: '',
                dns2: '',
            };
        }
        return {
            config_type: typeof ipSet.config_type !== 'undefined' ? parseInt(ipSet.config_type) : 1,
            ip_set: typeof ipSet.ip_set !== 'undefined' ? ipSet.ip_set.map(row => {
                return {
                    ip: typeof row.ip !== 'undefined' ? row.ip : '',
                    netmask: typeof row.netmask !== 'undefined' ? row.netmask : '',
                };
            }) : [],
            gateway: typeof ipSet.gateway !== 'undefined' ? ipSet.gateway : '',
            dns1: typeof ipSet.dns1 !== 'undefined' ? ipSet.dns1 : '',
            dns2: typeof ipSet.dns2 !== 'undefined' ? ipSet.dns2 : '',
        };
    };

    /**
     * 获取默认配置
     * @param {Ipv4v6} ipSet
     * @param {Boolean} convert_netmask_flag 是否转换netmask为cidr
     */
    const getIpv6SetConfig = (ipSet, convert_netmask_flag = true) => {
        if (ipSet === undefined || !ipSet) {
            return {
                config_type: 1,
                ip_set: [],
                gateway: '',
                dns1: '',
                dns2: '',
            };
        }
        return {
            config_type: typeof ipSet.config_type !== 'undefined' ? parseInt(ipSet.config_type) : 1,
            ip_set: typeof ipSet.ip_set !== 'undefined' ? ipSet.ip_set.map(row => {
                let netmask = typeof row.netmask !== 'undefined' ? row.netmask : '';
                if (netmask.length) {
                    if (validateIpv6(netmask)) {
                        if (convert_netmask_flag) {
                            netmask = convertIPv6MaskToCIDR(netmask);
                        }
                    }
                }
                return {
                    ip: typeof row.ip !== 'undefined' ? row.ip : '',
                    netmask,
                };
            }) : [],
            gateway: typeof ipSet.gateway !== 'undefined' ? ipSet.gateway : '',
            dns1: typeof ipSet.dns1 !== 'undefined' ? ipSet.dns1 : '',
            dns2: typeof ipSet.dns2 !== 'undefined' ? ipSet.dns2 : '',
        };
    };

    /**
     * 获取默认类型的配置选项
     */
    const getDefaultTypeOptions = (prefixId, options) => {
        return {
            ipv4_set: getIpSetConfig(options.ipv4_set),
            source_ipv4_set: getIpSetConfig(options.ipv4_set),
            ipv6_set: getIpv6SetConfig(options.ipv6_set),
            source_ipv6_set: getIpv6SetConfig(options.ipv6_set, false),
            label_width: typeof options.label_width !== 'undefined' ? options.label_width : 'col-md-2',
            input_width: typeof options.input_width !== 'undefined' ? options.input_width : 'col-md-6',
            input_table_width: typeof options.input_table_width !== 'undefined' ? options.input_table_width : 'col-md-9',
            element_set: {
                // 各个ID设置
                prefix_id: prefixId,
                form_id: prefixId + '_form',
                ipv4_tab_id: prefixId + '_ipv4_tab',
                ipv6_tab_id: prefixId + '_ipv6_tab',
                // IPv4定义
                ipv4_form_id: prefixId + '_ipv4_form',
                ipv4_auto_id: prefixId + '_ipv4_config_type1',
                ipv4_manual_id: prefixId + '_ipv4_config_type2',
                ipv4_add_ip_set_id: prefixId + '_ipv4_add_ip_set',
                ipv4_ip_set_ul_id: prefixId + '_ipv4_ip_set_ul',
                ipv4_gateway_id: prefixId + '_ipv4_gateway',
                ipv4_dns1_id: prefixId + '_ipv4_dns1',
                ipv4_dns2_id: prefixId + '_ipv4_dns2',
                ipv4_ip_config_type_name: prefixId + 'IPv4ConfigType',
                ipv4_manual_config_class: prefixId + 'IPv4ManualConfig',
                ipv4_validator: null,
                // IPv6定义
                ipv6_form_id: prefixId + '_ipv6_form',
                ipv6_auto_id: prefixId + '_ipv6_config_type1',
                ipv6_manual_id: prefixId + '_ipv6_config_type2',
                ipv6_add_ip_set_id: prefixId + '_ipv6_add_ip_set',
                ipv6_ip_set_ul_id: prefixId + '_ipv6_ip_set_ul',
                ipv6_gateway_id: prefixId + '_ipv6_gateway',
                ipv6_dns1_id: prefixId + '_ipv6_dns1',
                ipv6_dns2_id: prefixId + '_ipv6_dns2',
                ipv6_ip_config_type_name: prefixId + 'IPv6ConfigType',
                ipv6_manual_config_class: prefixId + 'IPv6ManualConfig',
                ipv6_validator: null,
            }
        };
    };

    /**
     * 生成uuid
     */
    const getUuid = () => {
        let len = 36;//36长度
        let radix = 16;//16进制
        let chars = '0123456789abcdefghijklmnopqrstuvwxyz'.split('');
        let uuid = [];
        radix = radix || chars.length;
        if (len) {
            for (let i = 0; i < len; i++) {
                uuid[i] = chars[0 | Math.random() * radix];
            }
        } else {
            let r;
            uuid[8] = uuid[13] = uuid[18] = uuid[23] = '-';
            uuid[14] = '4';
            for (let i = 0; i < 36; i++) {
                if (!uuid[i]) {
                    r = 0 | Math.random() * 16;
                    uuid[i] = chars[(i === 19) ? (r & 0x3) | 0x8 : r];
                }
            }
        }
        return uuid.join('');
    };

    /**
     * 获取客户端配置
     * @param {String} prefixId id
     * @param {Object} options
     */
    const getClientOptions = (prefixId, options) => {
        let clientNetList = [];
        let showAdvanceFlag = true;
        if (typeof options.show_advance_flag !== 'undefined') {
            showAdvanceFlag = options.show_advance_flag;
        }
        if (typeof options.client_net_list !== 'undefined' && options.client_net_list.length > 0) {
            for (const clientNet of options.client_net_list) {
                let netUuid = getUuid();
                clientNetList.push({
                    checked: clientNet.checked,
                    chk_disabled: clientNet.chk_disabled,
                    net_uuid: netUuid,
                    net_name: clientNet.net_name,
                    net_mac: clientNet.net_mac,
                    mac_address: clientNet.mac_address,
                    expand_advance_flag: clientNet.expand_advance_flag,
                    enable_change_net_flag:  typeof clientNet.enable_change_net_flag !== 'undefined' ? clientNet.enable_change_net_flag : true,
                    change_net_flag: clientNet.change_net_flag,
                    show_disable_network_card_flag: typeof clientNet.show_disable_network_card_flag !== 'undefined' ? clientNet.show_disable_network_card_flag : false,
                    disable_network_card: clientNet.disable_network_card,
                    show_ip_switch_nav_tabs_flag: typeof clientNet.show_ip_switch_nav_tabs_flag !== 'undefined' ? clientNet.show_ip_switch_nav_tabs_flag : true,
                    show_config_type_flag: typeof clientNet.show_config_type_flag !== 'undefined' ? clientNet.show_config_type_flag : true,
                    ipv4_set: getIpSetConfig(clientNet.ipv4_set),
                    source_ipv4_set: getIpSetConfig(clientNet.ipv4_set),
                    ipv6_set: getIpv6SetConfig(clientNet.ipv6_set),
                    source_ipv6_set: getIpv6SetConfig(clientNet.ipv6_set, false),
                    // 各个ID定义
                    change_net_id: prefixId + '_' + netUuid + '_change_net',
                    disable_network_card_id: prefixId + '_' + netUuid + '_disable_network_card',
                    ip_config_id: prefixId + '_' + netUuid + '_ip_config',
                    ipv4_tab_id: prefixId + '_' + netUuid + '_ipv4_tab',
                    ipv6_tab_id: prefixId + '_' + netUuid + '_ipv6_tab',
                    // IPv4定义
                    ipv4_form_id: prefixId + '_' + netUuid + '_ipv4_form',
                    ipv4_auto_id: prefixId + '_' + netUuid + '_ipv4_config_type1',
                    ipv4_manual_id: prefixId + '_' + netUuid + '_ipv4_config_type2',
                    ipv4_add_ip_set_id: prefixId + '_' + netUuid + '_ipv4_add_ip_set',
                    ipv4_ip_set_ul_id: prefixId + '_' + netUuid + '_ipv4_ip_set_ul',
                    ipv4_gateway_id: prefixId + '_' + netUuid + '_ipv4_gateway',
                    ipv4_dns1_id: prefixId + '_' + netUuid + '_ipv4_dns1',
                    ipv4_dns2_id: prefixId + '_' + netUuid + '_ipv4_dns2',
                    ipv4_ip_config_type_name: prefixId + '_' + netUuid + 'IPv4IpConfigType',
                    ipv4_manual_config_class: prefixId + '_' + netUuid + 'IPv4ManualConfig',
                    ipv4_change_net_class: prefixId + '_' + netUuid + 'IPv4ChangeNet',
                    ipv4_validator: null,
                    // IPv6定义
                    ipv6_form_id: prefixId + '_' + netUuid + '_ipv6_form',
                    ipv6_auto_id: prefixId + '_' + netUuid + '_ipv6_config_type1',
                    ipv6_manual_id: prefixId + '_' + netUuid + '_ipv6_config_type2',
                    ipv6_add_ip_set_id: prefixId + '_' + netUuid + '_ipv6_add_ip_set',
                    ipv6_ip_set_ul_id: prefixId + '_' + netUuid + '_ipv6_ip_set_ul',
                    ipv6_gateway_id: prefixId + '_' + netUuid + '_ipv6_gateway',
                    ipv6_dns1_id: prefixId + '_' + netUuid + '_ipv6_dns1',
                    ipv6_dns2_id: prefixId + '_' + netUuid + '_ipv6_dns2',
                    ipv6_ip_config_type_name: prefixId + '_' + netUuid + 'IPv6IpConfigType',
                    ipv6_manual_config_class: prefixId + '_' + netUuid + 'IPv6ManualConfig',
                    ipv6_change_net_class: prefixId + '_' + netUuid + 'IPv6ChangeNet',
                    ipv6_validator: null,
                });
            }
        }
        let tableHeight = '500px';
        if (typeof options.height !== 'undefined') {
            if (typeof options.height.table_height !== 'undefined') {
                tableHeight = options.height.table_height;
            }
        }
        return {
            client_net_list: clientNetList,
            element_set: {
                prefix_id: prefixId,
                table_id: prefixId + '_table',
            },
            height: {
                table_height: tableHeight,
            },
            show_advance_flag: showAdvanceFlag,
        };
    };

    /**
     * 获取虚拟化配置
     * @param {String} prefixId id
     * @param {Object} options
     */
    const getVmOptions = (prefixId, options) => {
        let vmNetList = [];
        let showAdvanceFlag = true;
        if (typeof options.show_advance_flag !== 'undefined') {
            showAdvanceFlag = options.show_advance_flag;
        }
        if (typeof options.vm_net_list !== 'undefined' && options.vm_net_list.length > 0) {
            for (const vmNet of options.vm_net_list) {
                let netUuid = getUuid();
                let network = vmNet.network;
                let findNetworkFlag = false;
                for (const networkInfo of vmNet.network_list) {
                    if (networkInfo.value === network) {
                        findNetworkFlag = true;
                        break;
                    }
                }
                if (!findNetworkFlag) {
                    network = vmNet.network_list[0].value;
                }
                let netBus = vmNet.net_bus;
                let findNetBusFlag = false;
                for (const netBusInfo of vmNet.net_bus_list) {
                    if (netBusInfo.value === netBus) {
                        findNetBusFlag = true;
                        break;
                    }
                }
                if (!findNetBusFlag) {
                    netBus = vmNet.net_bus_list[0].value;
                }
                let macType = parseInt(vmNet.mac_type);
                if (typeof MAC_TYPE_LANG_MAP[macType] === 'undefined') {
                    macType = MAC_TYPE_ENUM.AUTO;
                }

                vmNetList.push({
                    checked: vmNet.checked,
                    chk_disabled: vmNet.chk_disabled,
                    net_uuid: netUuid,
                    net_name: vmNet.net_name,
                    net_mac: vmNet.net_mac,
                    network_list: vmNet.network_list,
                    network: network,
                    expand_advance_flag: vmNet.expand_advance_flag,
                    net_bus_list: vmNet.net_bus_list,
                    net_bus: netBus,
                    change_net_bus_flag: vmNet.change_net_bus_flag,
                    mac_type: macType,
                    mac_address: vmNet.mac_address,
                    enable_change_net_flag:  typeof vmNet.enable_change_net_flag !== 'undefined' ? vmNet.enable_change_net_flag : true,
                    change_net_flag: vmNet.change_net_flag,
                    show_disable_network_card_flag: typeof vmNet.show_disable_network_card_flag !== 'undefined' ? vmNet.show_disable_network_card_flag : false,
                    disable_network_card: vmNet.disable_network_card,
                    show_ip_switch_nav_tabs_flag: typeof vmNet.show_ip_switch_nav_tabs_flag !== 'undefined' ? vmNet.show_ip_switch_nav_tabs_flag : true,
                    show_config_type_flag: typeof vmNet.show_config_type_flag !== 'undefined' ? vmNet.show_config_type_flag : true,
                    ipv4_set: getIpSetConfig(vmNet.ipv4_set),
                    source_ipv4_set: getIpSetConfig(vmNet.ipv4_set),
                    ipv6_set: getIpv6SetConfig(vmNet.ipv6_set),
                    source_ipv6_set: getIpv6SetConfig(vmNet.ipv6_set, false),
                    // 各个ID定义
                    network_id: prefixId + '_' + netUuid + '_network',
                    net_bus_id: prefixId + '_' + netUuid + '_net_bus',
                    mac_type_id: prefixId + '_' + netUuid + '_mac_type',
                    custom_mac_id: prefixId + '_' + netUuid + '_custom_mac',
                    change_net_id: prefixId + '_' + netUuid + '_change_net',
                    disable_network_card_id: prefixId + '_' + netUuid + '_disable_network_card',
                    ip_config_id: prefixId + '_' + netUuid + '_ip_config',
                    ipv4_tab_id: prefixId + '_' + netUuid + '_ipv4_tab',
                    ipv6_tab_id: prefixId + '_' + netUuid + '_ipv6_tab',
                    // IPv4定义
                    ipv4_form_id: prefixId + '_' + netUuid + '_ipv4_form',
                    ipv4_auto_id: prefixId + '_' + netUuid + '_ipv4_config_type1',
                    ipv4_manual_id: prefixId + '_' + netUuid + '_ipv4_config_type2',
                    ipv4_add_ip_set_id: prefixId + '_' + netUuid + '_ipv4_add_ip_set',
                    ipv4_ip_set_ul_id: prefixId + '_' + netUuid + '_ipv4_ip_set_ul',
                    ipv4_gateway_id: prefixId + '_' + netUuid + '_ipv4_gateway',
                    ipv4_dns1_id: prefixId + '_' + netUuid + '_ipv4_dns1',
                    ipv4_dns2_id: prefixId + '_' + netUuid + '_ipv4_dns2',
                    ipv4_ip_config_type_name: prefixId + '_' + netUuid + 'IPv4IpConfigType',
                    ipv4_manual_config_class: prefixId + '_' + netUuid + 'IPv4ManualConfig',
                    ipv4_change_net_class: prefixId + '_' + netUuid + 'IPv4ChangeNet',
                    ipv4_validator: null,
                    // IPv6定义
                    ipv6_form_id: prefixId + '_' + netUuid + '_ipv6_form',
                    ipv6_auto_id: prefixId + '_' + netUuid + '_ipv6_config_type1',
                    ipv6_manual_id: prefixId + '_' + netUuid + '_ipv6_config_type2',
                    ipv6_add_ip_set_id: prefixId + '_' + netUuid + '_ipv6_add_ip_set',
                    ipv6_ip_set_ul_id: prefixId + '_' + netUuid + '_ipv6_ip_set_ul',
                    ipv6_gateway_id: prefixId + '_' + netUuid + '_ipv6_gateway',
                    ipv6_dns1_id: prefixId + '_' + netUuid + '_ipv6_dns1',
                    ipv6_dns2_id: prefixId + '_' + netUuid + '_ipv6_dns2',
                    ipv6_ip_config_type_name: prefixId + '_' + netUuid + 'IPv6IpConfigType',
                    ipv6_manual_config_class: prefixId + '_' + netUuid + 'IPv6ManualConfig',
                    ipv6_change_net_class: prefixId + '_' + netUuid + 'IPv6ChangeNet',
                    ipv6_validator: null,
                });
            }
        }
        let tableHeight = '500px';
        if (typeof options.height !== 'undefined') {
            if (typeof options.height.table_height !== 'undefined') {
                tableHeight = options.height.table_height;
            }
        }
        return {
            vm_net_list: vmNetList,
            element_set: {
                prefix_id: prefixId,
                table_id: prefixId + '_table',
            },
            height: {
                table_height: tableHeight,
            },
            show_advance_flag: showAdvanceFlag,
        };
    };

    /**
     * 获取内嵌虚拟化配置
     * @param {String} prefixId id
     * @param {Object} options
     */
    const getCustomVmOptions = (prefixId, options) => {
        let customVmNetList = [];
        let showAdvanceFlag = true;
        if (typeof options.show_advance_flag !== 'undefined') {
            showAdvanceFlag = options.show_advance_flag;
        }
        if (typeof options.custom_vm_net_list !== 'undefined' && options.custom_vm_net_list.length > 0) {
            for (const customVmNet of options.custom_vm_net_list) {
                let netUuid = getUuid();
                let network = customVmNet.network;
                let findNetworkFlag = false;
                for (const networkInfo of customVmNet.network_list) {
                    if (networkInfo.value === network) {
                        findNetworkFlag = true;
                        break;
                    }
                }
                if (!findNetworkFlag) {
                    network = customVmNet.network_list[0].value;
                }
                let ipv4Set = getIpSetConfig(customVmNet.ipv4_set);
                ipv4Set.config_type = CONFIG_TYPE_ENUM.manual;  // 暂时只有手动
                let sourceIpv4Set = getIpSetConfig(customVmNet.ipv4_set);
                let ipv6Set = getIpv6SetConfig(customVmNet.ipv6_set);
                ipv6Set.config_type = CONFIG_TYPE_ENUM.manual;  // 暂时只有手动
                let sourceIpv6Set = getIpv6SetConfig(customVmNet.ipv6_set, false);

                customVmNetList.push({
                    checked: customVmNet.checked,
                    chk_disabled: customVmNet.chk_disabled,
                    net_uuid: netUuid,
                    net_name: customVmNet.net_name,
                    net_mac: customVmNet.net_mac,
                    network_list: customVmNet.network_list,
                    network: network,
                    expand_advance_flag: customVmNet.expand_advance_flag,
                    enable_change_net_flag:  typeof customVmNet.enable_change_net_flag !== 'undefined' ? customVmNet.enable_change_net_flag : true,
                    change_net_flag: customVmNet.change_net_flag,
                    show_disable_network_card_flag: typeof customVmNet.show_disable_network_card_flag !== 'undefined' ? customVmNet.show_disable_network_card_flag : false,
                    disable_network_card: customVmNet.disable_network_card,
                    show_ip_switch_nav_tabs_flag: typeof customVmNet.show_ip_switch_nav_tabs_flag !== 'undefined' ? customVmNet.show_ip_switch_nav_tabs_flag : true,
                    show_config_type_flag: typeof customVmNet.show_config_type_flag !== 'undefined' ? customVmNet.show_config_type_flag : false,
                    ipv4_set: ipv4Set,
                    source_ipv4_set: sourceIpv4Set,
                    ipv6_set: ipv6Set,
                    source_ipv6_set: sourceIpv6Set,
                    // 各个ID设置
                    network_id: prefixId + '_' + netUuid + '_network',
                    net_bus_id: prefixId + '_' + netUuid + '_net_bus',
                    mac_type_id: prefixId + '_' + netUuid + '_mac_type',
                    custom_mac_id: prefixId + '_' + netUuid + '_custom_mac',
                    change_net_id: prefixId + '_' + netUuid + '_change_net',
                    disable_network_card_id: prefixId + '_' + netUuid + '_disable_network_card',
                    ip_config_id: prefixId + '_' + netUuid + '_ip_config',
                    ipv4_tab_id: prefixId + '_' + netUuid + '_ipv4_tab',
                    ipv6_tab_id: prefixId + '_' + netUuid + '_ipv6_tab',
                    // IPv4定义
                    ipv4_form_id: prefixId + '_' + netUuid + '_ipv4_form',
                    ipv4_auto_id: prefixId + '_' + netUuid + '_ipv4_config_type1',
                    ipv4_manual_id: prefixId + '_' + netUuid + '_ipv4_config_type2',
                    ipv4_add_ip_set_id: prefixId + '_' + netUuid + '_ipv4_add_ip_set',
                    ipv4_ip_set_ul_id: prefixId + '_' + netUuid + '_ipv4_ip_set_ul',
                    ipv4_gateway_id: prefixId + '_' + netUuid + '_ipv4_gateway',
                    ipv4_dns1_id: prefixId + '_' + netUuid + '_ipv4_dns1',
                    ipv4_dns2_id: prefixId + '_' + netUuid + '_ipv4_dns2',
                    ipv4_ip_config_type_name: prefixId + '_' + netUuid + 'IPv4IpConfigType',
                    ipv4_manual_config_class: prefixId + '_' + netUuid + 'IPv4ManualConfig',
                    ipv4_change_net_class: prefixId + '_' + netUuid + 'IPv4ChangeNet',
                    ipv4_validator: null,
                    // IPv6定义
                    ipv6_form_id: prefixId + '_' + netUuid + '_ipv6_form',
                    ipv6_auto_id: prefixId + '_' + netUuid + '_ipv6_config_type1',
                    ipv6_manual_id: prefixId + '_' + netUuid + '_ipv6_config_type2',
                    ipv6_add_ip_set_id: prefixId + '_' + netUuid + '_ipv6_add_ip_set',
                    ipv6_ip_set_ul_id: prefixId + '_' + netUuid + '_ipv6_ip_set_ul',
                    ipv6_gateway_id: prefixId + '_' + netUuid + '_ipv6_gateway',
                    ipv6_dns1_id: prefixId + '_' + netUuid + '_ipv6_dns1',
                    ipv6_dns2_id: prefixId + '_' + netUuid + '_ipv6_dns2',
                    ipv6_ip_config_type_name: prefixId + '_' + netUuid + 'IPv6IpConfigType',
                    ipv6_manual_config_class: prefixId + '_' + netUuid + 'IPv6ManualConfig',
                    ipv6_change_net_class: prefixId + '_' + netUuid + 'IPv6ChangeNet',
                    ipv6_validator: null,
                });
            }
        }
        let tableHeight = '500px';
        if (typeof options.height !== 'undefined') {
            if (typeof options.height.table_height !== 'undefined') {
                tableHeight = options.height.table_height;
            }
        }
        return {
            custom_vm_net_list: customVmNetList,
            element_set: {
                prefix_id: prefixId,
                table_id: prefixId + '_table',
            },
            height: {
                table_height: tableHeight,
            },
            show_advance_flag: showAdvanceFlag,
        };
    };

    /**
     * 获取选项
     * @param {jQuery} obj jquery对象
     * @param {Option} options
     * @param {Number} usage_type 用途
     */
    const getOptions = (obj, options, usage_type) => {
        let prefixId = obj.attr('id');
        let retOptions = {};
        switch (usage_type) {
            case USAGE_TYPE_ENUM.DEFAULT:
                retOptions = getDefaultTypeOptions(prefixId, options);
                break;
            case USAGE_TYPE_ENUM.CLIENT:
                retOptions = getClientOptions(prefixId, options);
                break;
            case USAGE_TYPE_ENUM.VM:
                retOptions = getVmOptions(prefixId, options);
                break;
            case USAGE_TYPE_ENUM.CUSTOM_VM:
                retOptions = getCustomVmOptions(prefixId, options);
                break;
            default:
                break;
        }
        retOptions.usage_type = usage_type;
        return retOptions;
    };

    //////////////////// 结束-获取配置 ////////////////////

    /**
     * 获取默认类型内容
     * @param {Object} options 配置
     */
    const getDefaultTypeContent = options => {
        return `
        <div class="networkConfigWrapper">
            <ul class="nav nav-tabs">
                <li class="ipv4Li active" data-toggle="tooltip" data-placement="top" data-html="true" title="${LANG.UI_PUBLIC_IPV4_CONFIG}">
                    <a href="#${options.element_set.ipv4_tab_id}" data-toggle="tab">${LANG.UI_PUBLIC_IPV4_CONFIG}</a>
                </li>
                <li class="ipv6Li" data-toggle="tooltip" data-placement="top" data-html="true" title="${LANG.UI_PUBLIC_IPV6_CONFIG}">
                    <a href="#${options.element_set.ipv6_tab_id}" data-toggle="tab">${LANG.UI_PUBLIC_IPV6_CONFIG}</a>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="${options.element_set.ipv4_tab_id}">
                    <div class="panel-body">
                        <div class="row network-config-component" id="${options.element_set.ipv4_form_id}">
                            <!-- IP配置方式 -->
                            <div class="form-group">
                                <label class="${options.label_width} control-label text-align-reverse mb-0 networkConfigLabel pt0" style="margin-top: -1px;padding-top: 0;">${LANG.UI_PUBLIC_IP_CONFIG_TYPE}</label>
                                <div class="${options.input_width} flex-items-center networkConfigContent">
                                    <label class="radio-item">
                                        <input type="radio"
                                            data-radio="iradio_square-blue"
                                            value="${CONFIG_TYPE_ENUM.auto}"
                                            ${options.ipv4_set.config_type === CONFIG_TYPE_ENUM.auto ? 'checked' : ''}
                                            name="${options.element_set.ipv4_ip_config_type_name}"
                                            class="icheck" id="${options.element_set.ipv4_auto_id}">${LANG.UI_PUBLIC_IP_CONFIG_TYPE_AUTO}
                                    </label>
                                    <label class="radio-item">
                                        <input type="radio"
                                            data-radio="iradio_square-blue"
                                            value="${CONFIG_TYPE_ENUM.manual}"
                                            ${options.ipv4_set.config_type === CONFIG_TYPE_ENUM.manual ? 'checked' : ''}
                                            name="${options.element_set.ipv4_ip_config_type_name}"
                                            class="icheck" id="${options.element_set.ipv4_manual_id}">${LANG.UI_PUBLIC_IP_CONFIG_TYPE_MANUAL}
                                    </label>
                                </div>
                            </div>
                            <!-- IP设置 -->
                            <div class="form-group ${options.element_set.ipv4_manual_config_class}">
                                <label class="${options.label_width} control-label pt7 text-align-reverse networkConfigLabel">
                                    ${LANG.UI_PUBLIC_IP_SET}
                                </label>
                                <div class="${options.input_width} networkConfigContent">
                                    <button type="button" class="btn btn-light-primary addIpSetBtn" id="${options.element_set.ipv4_add_ip_set_id}">
                                        <i class="viconfont vicon-biaogetianjia"></i>
                                        <span>${LANG.UI_PUBLIC_ADD_IP_SET}</span>
                                    </button>
                                </div>
                            </div>
                            <div class="form-group ${options.element_set.ipv4_manual_config_class}">
                                <label class="${options.label_width} control-label pt7 text-align-reverse networkConfigLabel">
                                </label>
                                <div class="${options.input_table_width} networkConfigIpSetUlWrapper">
                                    <ul class="networkConfigIpSetUl" id="${options.element_set.ipv4_ip_set_ul_id}">
                                        <li class="ip-set-head">
                                            <div class="ip-set-head__ip">${LANG.UI_PUBLIC_IP_ADDRESS}</div>
                                            <div class="ip-set-head__netmask">${LANG.UI_PUBLIC_IP_NETMASK}</div>
                                            <div class="ip-set-head__operate">${LANG.UI_PUBLIC_OPERATION}</div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <!-- 默认网关 -->
                            <div class="form-group ${options.element_set.ipv4_manual_config_class}">
                                <label class="${options.label_width} control-label pt7 text-align-reverse networkConfigLabel">
                                    ${LANG.UI_PUBLIC_IP_GATEWAY}
                                </label>
                                <div class="${options.input_width} networkConfigContent">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input class="form-control"
                                            name="gateway_v4"
                                            id="${options.element_set.ipv4_gateway_id}"
                                            value="${options.ipv4_set.gateway}"
                                            data-validate-gateway_v4="true"
                                            data-restrict-no_whitespace="true" />
                                    </div>
                                </div>
                            </div>
                            <!-- DNS1 -->
                            <div class="form-group ${options.element_set.ipv4_manual_config_class}">
                                <label class="${options.label_width} control-label pt7 text-align-reverse networkConfigLabel">
                                    ${LANG.UI_PUBLIC_IP_DNS1}
                                </label>
                                <div class="${options.input_width} networkConfigContent">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input class="form-control" name="dns1" id="${options.element_set.ipv4_dns1_id}" value="${options.ipv4_set.dns1}" />
                                    </div>
                                </div>
                            </div>
                            <!-- DNS2 -->
                            <div class="form-group ${options.element_set.ipv4_manual_config_class}">
                                <label class="${options.label_width} control-label pt7 text-align-reverse networkConfigLabel">
                                    ${LANG.UI_PUBLIC_IP_DNS2}
                                </label>
                                <div class="${options.input_width} networkConfigContent">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input class="form-control" name="dns2" id="${options.element_set.ipv4_dns2_id}" value="${options.ipv4_set.dns2}" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="${options.element_set.ipv6_tab_id}">
                    <div class="panel-body">
                        <div class="row network-config-component" id="${options.element_set.ipv6_form_id}">
                            <!-- IP配置方式 -->
                            <div class="form-group">
                                <label class="${options.label_width} control-label text-align-reverse mb-0 networkConfigLabel pt0" style="margin-top: -1px;padding-top: 0;">${LANG.UI_PUBLIC_IP_CONFIG_TYPE}</label>
                                <div class="${options.input_width} flex-items-center networkConfigContent">
                                    <label class="radio-item">
                                        <input type="radio"
                                            data-radio="iradio_square-blue"
                                            value="${CONFIG_TYPE_ENUM.auto}"
                                            ${options.ipv6_set.config_type === CONFIG_TYPE_ENUM.auto ? 'checked' : ''}
                                            name="${options.element_set.ipv6_ip_config_type_name}"
                                            class="icheck" id="${options.element_set.ipv6_auto_id}">${LANG.UI_PUBLIC_IP_CONFIG_TYPE_AUTO}
                                    </label>
                                    <label class="radio-item">
                                        <input type="radio"
                                            data-radio="iradio_square-blue"
                                            value="${CONFIG_TYPE_ENUM.manual}"
                                            ${options.ipv6_set.config_type === CONFIG_TYPE_ENUM.manual ? 'checked' : ''}
                                            name="${options.element_set.ipv6_ip_config_type_name}"
                                            class="icheck" id="${options.element_set.ipv6_manual_id}">${LANG.UI_PUBLIC_IP_CONFIG_TYPE_MANUAL}
                                    </label>
                                </div>
                            </div>
                            <!-- IP设置 -->
                            <div class="form-group ${options.element_set.ipv6_manual_config_class}">
                                <label class="${options.label_width} control-label pt7 text-align-reverse networkConfigLabel">
                                    ${LANG.UI_PUBLIC_IP_SET}
                                </label>
                                <div class="${options.input_width} networkConfigContent">
                                    <button type="button" class="btn btn-light-primary addIpSetBtn" id="${options.element_set.ipv6_add_ip_set_id}">
                                        <i class="viconfont vicon-biaogetianjia"></i>
                                        <span>${LANG.UI_PUBLIC_ADD_IP_SET}</span>
                                    </button>
                                </div>
                            </div>
                            <div class="form-group ${options.element_set.ipv6_manual_config_class}">
                                <label class="${options.label_width} control-label pt7 text-align-reverse networkConfigLabel">
                                </label>
                                <div class="${options.input_table_width} networkConfigIpSetUlWrapper">
                                    <ul class="networkConfigIpSetUl" id="${options.element_set.ipv6_ip_set_ul_id}">
                                        <li class="ip-set-head">
                                            <div class="ip-set-head__ip">${LANG.UI_PUBLIC_IP_ADDRESS}</div>
                                            <div class="ip-set-head__netmask">${LANG.UI_PUBLIC_PREFIX}</div>
                                            <div class="ip-set-head__operate">${LANG.UI_PUBLIC_OPERATION}</div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <!-- GATEWAY -->
                            <div class="form-group ${options.element_set.ipv6_manual_config_class}">
                                <label class="${options.label_width} control-label pt7 text-align-reverse networkConfigLabel">
                                    ${LANG.UI_PUBLIC_IP_GATEWAY}
                                </label>
                                <div class="${options.input_width} networkConfigContent">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input class="form-control"
                                            name="gateway_v6"
                                            id="${options.element_set.ipv6_gateway_id}"
                                            value="${options.ipv6_set.gateway}"
                                            data-validate-gateway_v6="true"
                                            data-restrict-no_whitespace="true" />
                                    </div>
                                </div>
                            </div>
                            <!-- DNS1 -->
                            <div class="form-group ${options.element_set.ipv6_manual_config_class}">
                                <label class="${options.label_width} control-label pt7 text-align-reverse networkConfigLabel">
                                    ${LANG.UI_PUBLIC_IP_DNS1}
                                </label>
                                <div class="${options.input_width} networkConfigContent">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input class="form-control" name="dns1" id="${options.element_set.ipv6_dns1_id}" value="${options.ipv6_set.dns1}" />
                                    </div>
                                </div>
                            </div>
                            <!-- DNS2 -->
                            <div class="form-group ${options.element_set.ipv6_manual_config_class}">
                                <label class="${options.label_width} control-label pt7 text-align-reverse networkConfigLabel">
                                    ${LANG.UI_PUBLIC_IP_DNS2}
                                </label>
                                <div class="${options.input_width} networkConfigContent">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input class="form-control" name="dns2" id="${options.element_set.ipv6_dns2_id}" value="${options.ipv6_set.dns2}" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        `;
    };

    /**
     * 获取客户端内容
     * @param {Option} options 配置
     */
    const getRecoveryContent = options => {
        return `
        <div class="network-config-table__wrapper">
            <table class="table network-config-table" id="${options.element_set.table_id}"></table>
        </div>
        `;
    };

    /**
     * 初始化页面
     * @param {jQuery} obj jquery对象
     * @param {Object} options 配置
     */
    const initContent = (obj, options) => {
        let html = ``;
        switch (options.usage_type) {
            case USAGE_TYPE_ENUM.DEFAULT:
                html = getDefaultTypeContent(options);
                break;
            case USAGE_TYPE_ENUM.CLIENT:
            case USAGE_TYPE_ENUM.VM:
            case USAGE_TYPE_ENUM.CUSTOM_VM:
                html = getRecoveryContent(options);
                break;
            default:
                break;
        }
        obj.html(html);
        obj.find(`[data-toggle="tooltip"]`).tooltip();
        showContent(options);
    };

    /**
     * 显示内容
     * @param options
     */
    const showContent = options => {
        switch (options.usage_type) {
            case USAGE_TYPE_ENUM.DEFAULT:
                $(`#${options.element_set.ipv4_form_id}`).show();
                if (options.ipv4_set.config_type === CONFIG_TYPE_ENUM.manual) {
                    $(`.${options.element_set.ipv4_manual_config_class}`).show();
                } else {
                    $(`.${options.element_set.ipv4_manual_config_class}`).hide();
                }
                if (options.ipv6_set.config_type === CONFIG_TYPE_ENUM.manual) {
                    $(`.${options.element_set.ipv6_manual_config_class}`).show();
                } else {
                    $(`.${options.element_set.ipv6_manual_config_class}`).hide();
                }
                break;
            case USAGE_TYPE_ENUM.CLIENT:
                break;
            case USAGE_TYPE_ENUM.VM:
                break;
            default:
                break;
        }
    };

    /**
     * 隐藏内容
     * @param options
     */
    const hideContent = options => {
        switch (options.usage_type) {
            case USAGE_TYPE_ENUM.DEFAULT:
                $(`#${options.element_set.form_id}`).hide();
                break;
            case USAGE_TYPE_ENUM.CLIENT:
                break;
            case USAGE_TYPE_ENUM.VM:
                break;
            default:
                break;
        }
    };

    /**
     * 验证器规则
     */
    const validatorRules = {
        ipv4: {
            required: true,
            ipv4: true,
        },
        netmask_v4: {
            required: true,
            netmask_v4: true,
        },
        gateway_v4: {
            required: false,
            ipv4: true,
        },
        ipv6: {
            required: true,
            ipv6: true,
        },
        netmask_v6: {
            required: true,
            netmask_v6: true,
        },
        gateway_v6: {
            required: false,
            ipv6: true,
        },
        dns1: {
            required: false,
        },
        dns2: {
            required: false,
        },
        customMac: {
            customMac: true,
        },
    };

    /**
     * 获取验证器信息
     */
    const getValidatorInfo = () => {
        let info = {
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "",  // validate all fields including form hidden input
            onsubmit: false,
            // onfocusout: false,
            // onkeyup: true,
            onclick: false,
            focusCleanup: false,
            rules: validatorRules,

            invalidHandler: function (event, validator) { //display error alert on form submit
            },

            errorPlacement: function (error, element) { // render error placement for each input type
                let icon = $(element).parent('.input-icon').children('i');
                icon.removeClass('fa-check').addClass("fa-warning");
                icon.attr("data-original-title", error.text()).tooltip({'container': 'body'}).show();
                if ($(element).attr('name') === 'customMac') {
                    if ($(element).attr('readonly') && $(element).css('display') === 'none') {
                        icon.hide();
                    }
                }
            },

            highlight: function (element) { // hightlight error inputs
                $(element)
                    .closest('.form-group').removeClass("has-success").addClass('has-error'); // set error class to the control group
            },

            unhighlight: function (element) { // revert the change done by hightlight
            },

            success: function (label, element) {
                let icon = $(element).parent('.input-icon').children('i');
                $(element).closest('.form-group').removeClass('has-error').addClass('has-success'); // set success class to the control group
                icon.removeClass("fa-warning").addClass("fa-check");
                if ($(element).attr('name') === 'customMac') {
                    if ($(element).attr('readonly')) {
                        icon.hide();
                    }
                }
            },

            submitHandler: function (form) {
            }
        };

        // 追加验证器
        $.validator.addMethod('required', function (value, element) {
            return value.length;
        }, LANG.UI_PUBLIC_FIELD_REQUIRED);
        // 自定义MAC地址
        $.validator.addMethod('customMac', function (value, element) {
            if ($(element).attr('readonly')) {
                return true;
            }
            return value.length;
        }, LANG.UI_VM_SETTING_MAC_EMPTY_TIPS);
        // IPv4验证格式
        $.validator.addMethod('ipv4', function (value, element) {
            return this.optional(element) || validateIpv4(value);
        }, LANG.UI_SETTING_INPUT_IP);
        // 子网掩码v4验证格式
        $.validator.addMethod('netmask_v4', function (value, element) {  // 子网掩码
            return this.optional(element) || validateIPv4SubnetMask(value);
        }, LANG.UI_SETTING_INPUT_NETMASK);
        // 默认网关v4验证格式
        $.validator.addMethod('gateway_v4', function (value, element) {
            return this.optional(element) || validateIpv4(value);
        }, LANG.UI_SETTING_INPUT_GATEWAY);
        // IPv6验证格式
        $.validator.addMethod('ipv6', function (value, element) {
            return this.optional(element) || validateIpv6(value);
        }, LANG.UI_SETTING_INPUT_IP);
        // 子网掩码v6验证格式
        $.validator.addMethod('netmask_v6', function (value, element) {  // 子网掩码
            return this.optional(element) || validateIPv6SubnetMaskCIDR(value);
        }, LANG.UI_SETTING_INPUT_PREFIX);
        // 默认网关v6验证格式
        $.validator.addMethod('gateway_v6', function (value, element) {
            return this.optional(element) || validateIpv6(value);
        }, LANG.UI_SETTING_INPUT_GATEWAY);
        return info;
    };

    /**
     * 注册验证器
     * @param {Option} options 配置
     */
    const initValidator = options => {
        // let info = getValidatorInfo();
        //
        // $(`#${options.element_set.ipv4_form_id}`).validate(info);
        // $(`#${options.element_set.ipv6_form_id}`).validate(info);
        // return;
        // 初始化表单验证
        options.element_set.ipv4_validator = $(`#${options.element_set.ipv4_form_id}`).formValidate();
        options.element_set.ipv6_validator = $(`#${options.element_set.ipv6_form_id}`).formValidate();
    };

    //////////////////// 开始-事件监听 //////////////////

    /**
     * 获取IP设置li
     */
    const getIpSetLi = ipSet => {
        let ipInput = `<input class="ipSetItemInput" required name="ipv4" value="${ipSet.ip}" data-validate-required="true" data-validate-ipv4="true" />`;
        let netmaskInput = `<input class="ipSetItemInput" name="netmask_v4" value="${ipSet.netmask}" data-validate-required="true" data-validate-netmask_v4="true" />`;
        if (!ipSet.ipv4_flag) {
            ipInput = `<input class="ipSetItemInput" name="ipv6" value="${ipSet.ip}" data-validate-required="true" data-validate-ipv6="true" />`;
            netmaskInput = `<input class="ipSetItemInput" type="number" name="netmask_v6" value="${ipSet.netmask}" data-validate-required="true" data-validate-netmask_v6="true" />`;
        }
        return `
        <li class="ip-set-item">
            <div class="form-group ip-set-item__ip">
                <div class="input-icon right">
                    <i class="fa"></i>
                    ${ipInput}
                </div>
            </div>
            <div class="form-group ip-set-item__netmask">
                <div class="input-icon right">
                    <i class="fa"></i>
                    ${netmaskInput}
                </div>
            </div>
            <div class="form-group ip-set-item__operate">
                <a class="ipSetDelete"><i class="viconfont vicon-a-Deleteshanchu2"></i></a>
            </div>
        </li>
        `;
    };

    /**
     * 初始化默认类型IP设置列表
     */
    const initDefaultIpSetUl = options => {
        let ipSetLi = ``;
        if (!options.ipv4_set.ip_set.length) {
            $(`#${options.element_set.ipv4_ip_set_ul_id}`).append(getIpSetLi({
                ip: '',
                netmask: '',
                ipv4_flag: true,
            }));
        } else {
            for (const ipSet of options.ipv4_set.ip_set) {
                ipSetLi += getIpSetLi({
                    ip: ipSet.ip,
                    netmask: ipSet.netmask,
                    ipv4_flag: true,
                });
            }
            $(`#${options.element_set.ipv4_ip_set_ul_id}`).append(ipSetLi);
        }

        // 添加IP设置
        $(`#${options.element_set.ipv4_add_ip_set_id}`).on('click', () => {
            $(`#${options.element_set.ipv4_ip_set_ul_id}`).append(getIpSetLi({
                ip: '',
                netmask: '',
                ipv4_flag: true,
            }));
            $(`#${options.element_set.ipv4_ip_set_ul_id} .ip-set-item:last input[name="ipv4"]`).focus();
            options.element_set.ipv4_validator.validateElement($(`#${options.element_set.ipv4_ip_set_ul_id} .ip-set-item:last input.ipSetItemInput`).get(0));
        });
        // 删除IP设置
        $(`#${options.element_set.ipv4_ip_set_ul_id}`).on('click', '.ipSetDelete', function () {
            let ipSetItem = $(this).closest('.ip-set-item');
            ipSetItem.remove();
            let ipSetItems = $(`#${options.element_set.ipv4_ip_set_ul_id}`).find('.ip-set-item');
            if (!ipSetItems.length) {
                $(`#${options.element_set.ipv4_ip_set_ul_id}`).append(getIpSetLi({
                    ip: '',
                    netmask: '',
                    ipv4_flag: true,
                }));
                $(`#${options.element_set.ipv4_ip_set_ul_id} .ip-set-item:last input[name="ipv4"]`).focus();
                options.element_set.ipv4_validator.validateElement($(`#${options.element_set.ipv4_ip_set_ul_id} .ip-set-item:last input.ipSetItemInput`).get(0));
            }
        });
        $(`#${options.element_set.ipv4_ip_set_ul_id} .ip-set-item:first input[name="ipv4"]`).focus();
        if (!options.ipv4_set.ip_set.length) {
            setTimeout(() => {
                options.element_set.ipv4_validator.validateElement($(`#${options.element_set.ipv4_ip_set_ul_id} .ip-set-item:first input.ipSetItemInput`).get(0));
            }, 0);
        }

        // IPv6配置
        ipSetLi = ``;
        if (!options.ipv6_set.ip_set.length) {
            $(`#${options.element_set.ipv6_ip_set_ul_id}`).append(getIpSetLi({
                ip: '',
                netmask: '',
                ipv4_flag: false,
            }));
        } else {
            for (const ipSet of options.ipv6_set.ip_set) {
                ipSetLi += getIpSetLi({
                    ip: ipSet.ip,
                    netmask: ipSet.netmask,
                    ipv4_flag: false,
                });
            }
            $(`#${options.element_set.ipv6_ip_set_ul_id}`).append(ipSetLi);
        }

        // 添加IP设置
        $(`#${options.element_set.ipv6_add_ip_set_id}`).on('click', () => {
            $(`#${options.element_set.ipv6_ip_set_ul_id}`).append(getIpSetLi({
                ip: '',
                netmask: '',
                ipv4_flag: false,
            }));
            $(`#${options.element_set.ipv6_ip_set_ul_id} .ip-set-item:last input[name="ipv6"]`).focus();
            options.element_set.ipv6_validator.validateElement($(`#${options.element_set.ipv6_ip_set_ul_id} .ip-set-item:last input.ipSetItemInput`).get(0));
        });
        // 删除IP设置
        $(`#${options.element_set.ipv6_ip_set_ul_id}`).on('click', '.ipSetDelete', function () {
            let ipSetItem = $(this).closest('.ip-set-item');
            ipSetItem.remove();
            let ipSetItems = $(`#${options.element_set.ipv6_ip_set_ul_id}`).find('.ip-set-item');
            if (!ipSetItems.length) {
                $(`#${options.element_set.ipv6_ip_set_ul_id}`).append(getIpSetLi({
                    ip: '',
                    netmask: '',
                    ipv4_flag: false,
                }));
                $(`#${options.element_set.ipv6_ip_set_ul_id} .ip-set-item:last input[name="ipv6"]`).focus();
                options.element_set.ipv6_validator.validateElement($(`#${options.element_set.ipv6_ip_set_ul_id} .ip-set-item:last input.ipSetItemInput`).get(0));
            }
        });
        $(`#${options.element_set.ipv6_ip_set_ul_id} .ip-set-item:first input[name="ipv6"]`).focus();
        if (!options.ipv6_set.ip_set.length) {
            setTimeout(() => {
                options.element_set.ipv6_validator.validateElement($(`#${options.element_set.ipv6_ip_set_ul_id} .ip-set-item:first input.ipSetItemInput`).get(0));
            }, 0);
        }
    };

    /**
     * 初始化默认类型事件监听
     * @param {Option} options 配置
     */
    const initDefaultTypeListener = options => {
        // 初始化表格
        initDefaultIpSetUl(options);
        $(`#${options.element_set.ipv4_form_id} .icheck`).iCheck({
            radioClass: 'iradio_square-blue'
        });
        $(`#${options.element_set.ipv6_form_id} .icheck`).iCheck({
            radioClass: 'iradio_square-blue'
        });
        $(`#${options.element_set.prefix_id} .icheck`).iCheck({
            radioClass: 'iradio_square-blue'
        });
        setTimeout(() => {
            $(`input[name="${options.element_set.ipv4_ip_config_type_name}"]`).on('ifChecked', function () {
                if (parseInt($(this).val()) === CONFIG_TYPE_ENUM.auto) {
                    $(`.${options.element_set.ipv4_manual_config_class}`).hide();
                } else {
                    $(`.${options.element_set.ipv4_manual_config_class}`).show();
                }
            });
            $(`input[name="${options.element_set.ipv6_ip_config_type_name}"]`).on('ifChecked', function () {
                if (parseInt($(this).val()) === CONFIG_TYPE_ENUM.auto) {
                    $(`.${options.element_set.ipv6_manual_config_class}`).hide();
                } else {
                    $(`.${options.element_set.ipv6_manual_config_class}`).show();
                }
            });
        }, 100);
    };

    /**
     * 初始化恢复表格验证器
     * @param {Object} row 行数据
     */
    const initRecoveryValidator = row => {
        // let info = getValidatorInfo();
        //
        // $(`#${row.ipv4_form_id}`).validate(info);
        // $(`#${row.ipv6_form_id}`).validate(info);
        row.ipv4_validator = $(`#${row.ipv4_form_id}`).formValidate();
        row.ipv6_validator = $(`#${row.ipv6_form_id}`).formValidate();
    };

    /**
     * 设置恢复表格默认值
     * @param {Object} options 配置
     * @param {Object} row 行数据
     */
    const setRecoveryDetailDefault = (options, row) => {
        if (options.usage_type === USAGE_TYPE_ENUM.VM) {
            // 网卡类型
            $(`#${row.mac_type_id}`).val(row.mac_type).trigger('change');
            // MAC地址
            $(`#${row.custom_mac_id}`).val(row.mac_address);
        }
        // 隐藏禁用网卡
        if (!row.show_disable_network_card_flag) {
            $(`#${row.disable_network_card_id}Div`).hide();
        } else {
            $(`#${row.disable_network_card_id}Div`).show();
        }
        // 隐藏IPv4与IPv6的切换导航
        if (!row.show_ip_switch_nav_tabs_flag) {
            $(`#${row.ip_config_id}__nav_tabs`).hide();
            switchIpTab(row.ipv6_tab_id, row.ipv4_tab_id);  // 默认切换到IPv4
            $(`#${row.ipv6_auto_id}`).iCheck('check').trigger('ifChecked');
        } else {
            $(`#${row.ip_config_id}__nav_tabs`).show();
        }
        if (!row.show_config_type_flag) {
            $(`.${row.ipv4_change_net_class}`).hide();
            $(`.${row.ipv6_change_net_class}`).hide();
        } else {
            $(`.${row.ipv4_change_net_class}`).show();
            $(`.${row.ipv6_change_net_class}`).show();
        }
        // 网卡名称
        // 修改网络配置
        $(`#${row.change_net_id}`).bootstrapSwitch('state', row.change_net_flag).trigger('switchChange.bootstrapSwitch');
        if (!row.enable_change_net_flag) {
            $(`#${row.change_net_id}`).bootstrapSwitch('disabled', true);
        }
        // IPv4配置方式
        $(`input[name="${row.ipv4_ip_config_type_name}"]:checked`).trigger('ifChecked');
        // gateway
        $(`#${row.ipv4_gateway_id}`).val(row.ipv4_set.gateway);
        // DNS1
        $(`#${row.ipv4_dns1_id}`).val(row.ipv4_set.dns1);
        // DNS2
        $(`#${row.ipv4_dns2_id}`).val(row.ipv4_set.dns2);
        // IPv6配置方式
        $(`input[name="${row.ipv6_ip_config_type_name}"]:checked`).trigger('ifChecked');
        // gateway
        $(`#${row.ipv6_gateway_id}`).val(row.ipv6_set.gateway);
        // DNS1
        $(`#${row.ipv6_dns1_id}`).val(row.ipv6_set.dns1);
        // DNS2
        $(`#${row.ipv6_dns2_id}`).val(row.ipv6_set.dns2);
    };

    /**
     * 初始化恢复类型IP设置列表
     */
    const initRecoveryDetailIpSetUl = (options, row) => {
        // IPv4配置
        let ipv4SetLi = ``;
        if (!row.ipv4_set.ip_set.length) {
            $(`#${row.ipv4_ip_set_ul_id}`).append(getIpSetLi({
                ip: '',
                netmask: '',
                ipv4_flag: true,
            }));
        } else {
            for (const ipv4Set of row.ipv4_set.ip_set) {
                ipv4SetLi += getIpSetLi({
                    ip: ipv4Set.ip,
                    netmask: ipv4Set.netmask,
                    ipv4_flag: true,
                });
            }
            $(`#${row.ipv4_ip_set_ul_id}`).append(ipv4SetLi);
        }

        // 添加IP设置
        $(`#${row.ipv4_add_ip_set_id}`).on('click', () => {
            $(`#${row.ipv4_ip_set_ul_id}`).append(getIpSetLi({
                ip: '',
                netmask: '',
                ipv4_flag: true,
            }));
            $(`#${row.ipv4_ip_set_ul_id} .ip-set-item:last input[name="ip"]`).focus();
            row.ipv4_validator.validateElement($(`#${row.ipv4_ip_set_ul_id} .ip-set-item:last input.ipSetItemInput`).get(0));
        });
        // 删除IP设置
        $(`#${row.ipv4_ip_set_ul_id}`).on('click', '.ipSetDelete', function () {
            let ipSetItem = $(this).closest('.ip-set-item');
            ipSetItem.remove();
            let ipSetItems = $(`#${row.ipv4_ip_set_ul_id}`).find('.ip-set-item');
            if (!ipSetItems.length) {
                $(`#${row.ipv4_ip_set_ul_id}`).append(getIpSetLi({
                    ip: '',
                    netmask: '',
                    ipv4_flag: true,
                }));
                $(`#${row.ipv4_ip_set_ul_id} .ip-set-item:last input[name="ip"]`).focus();
                row.ipv4_validator.validateElement($(`#${row.ipv4_ip_set_ul_id} .ip-set-item:last input.ipSetItemInput`).get(0));
            }
        });
        $(`#${row.ipv4_ip_set_ul_id} .ip-set-item:first input[name="ip"]`).focus();
        if (!row.ipv4_set.ip_set.length) {
            setTimeout(() => {
                row.ipv4_validator.validateElement($(`#${row.ipv4_ip_set_ul_id} .ip-set-item:first input.ipSetItemInput`).get(0));
            }, 0);
        }

        // IPv6配置
        let ipv6SetLi = ``;
        if (!row.ipv6_set.ip_set.length) {
            $(`#${row.ipv6_ip_set_ul_id}`).append(getIpSetLi({
                ip: '',
                netmask: '',
                ipv4_flag: false,
            }));
        } else {
            for (const ipv6Set of row.ipv6_set.ip_set) {
                ipv6SetLi += getIpSetLi({
                    ip: ipv6Set.ip,
                    netmask: ipv6Set.netmask,
                    ipv4_flag: false,
                });
            }
            $(`#${row.ipv6_ip_set_ul_id}`).append(ipv6SetLi);
        }

        // 添加IP设置
        $(`#${row.ipv6_add_ip_set_id}`).on('click', () => {
            let ipSetItem = $('.ip-set-item:last');
            if (ipSetItem.length) {
                // let ip = ipSetItem.find(`input[name="ip"]`).val();
                // let netmask = ipSetItem.find(`input[name="netmask"]`).val();
                // if (!ip.length && !netmask.length) {
                //     return;
                // }
            }
            $(`#${row.ipv6_ip_set_ul_id}`).append(getIpSetLi({
                ip: '',
                netmask: '',
                ipv4_flag: false,
            }));
            $(`#${row.ipv6_ip_set_ul_id} .ip-set-item:last input[name="ip"]`).focus();
            row.ipv6_validator.validateElement($(`#${row.ipv6_ip_set_ul_id} .ip-set-item:last input.ipSetItemInput`).get(0));
        });
        // 删除IP设置
        $(`#${row.ipv6_ip_set_ul_id}`).on('click', '.ipSetDelete', function () {
            let ipSetItem = $(this).closest('.ip-set-item');
            ipSetItem.remove();
            let ipSetItems = $(`#${row.ipv6_ip_set_ul_id}`).find('.ip-set-item');
            if (!ipSetItems.length) {
                $(`#${row.ipv6_ip_set_ul_id}`).append(getIpSetLi({
                    ip: '',
                    netmask: '',
                    ipv4_flag: false,
                }));
                $(`#${row.ipv6_ip_set_ul_id} .ip-set-item:last input[name="ip"]`).focus();
                row.ipv6_validator.validateElement($(`#${row.ipv6_ip_set_ul_id} .ip-set-item:last input.ipSetItemInput`).get(0));
            }
        });
        $(`#${row.ipv6_ip_set_ul_id} .ip-set-item:first input[name="ip"]`).focus();
        if (!row.ipv6_set.ip_set.length) {
            setTimeout(() => {
                row.ipv6_validator.validateElement($(`#${row.ipv6_ip_set_ul_id} .ip-set-item:first input.ipSetItemInput`).get(0));
            }, 0);
        }
    };

    /**
     * 初始化恢复表格
     * @param {Object} options 配置
     * @param {Object} row
     */
    const initRecoveryDetailListener = (options, row) => {
        return new Promise(resolve => {
            // 初始化组件
            $(`#${row.change_net_id}`).bootstrapSwitch();
            $(`#${row.disable_network_card_id}`).bootstrapSwitch();
            $(`#${options.element_set.table_id} tr.detail-view .icheck`).iCheck({
                radioClass: 'iradio_square-blue'
            });

            setTimeout(() => {
                if (options.show_advance_flag) {
                    // 初始化IP配置
                    initRecoveryDetailIpSetUl(options, row);
                }
                if (options.usage_type === USAGE_TYPE_ENUM.VM) {
                    // MAC地址
                    $(`#${row.mac_type_id}`).on('change', function () {
                        let macType = parseInt(this.value);
                        let formGroupTag = $(this).parents().closest('.form-group');
                        formGroupTag.removeClass('has-error').removeClass('has-success');
                        formGroupTag.find('i.fa').hide();
                        if (macType === MAC_TYPE_ENUM.AUTO) {
                            $(`#${row.custom_mac_id}`).prop('readonly', 'readonly').hide();
                        } else if (macType === MAC_TYPE_ENUM.CUSTOM) {
                            $(`#${row.custom_mac_id}`).removeAttr('readonly').show();
                        } else {
                            $(`#${row.custom_mac_id}`).val(row.mac_address).prop('readonly', 'readonly').show();
                        }
                    });
                    $(`#${row.custom_mac_id}`).on('change', function () {
                        $.formValidateElement(this);
                    });
                }
                // 修改网络配置
                $(`#${row.change_net_id}`).on('switchChange.bootstrapSwitch', function () {
                    if (this.checked) {
                        // 隐藏禁用网卡
                        if (!row.show_disable_network_card_flag) {
                            $(`#${row.disable_network_card_id}Div`).hide();
                            $(`#${row.disable_network_card_id}`).bootstrapSwitch('state', false);
                        } else {
                            $(`#${row.disable_network_card_id}Div`).show();
                        }
                    } else {
                        $(`#${row.disable_network_card_id}Div`).hide();
                    }
                    $(`#${row.disable_network_card_id}`).trigger('switchChange.bootstrapSwitch');
                });
                // 禁用网卡
                $(`#${row.disable_network_card_id}`).on('switchChange.bootstrapSwitch', function () {
                    if (!this.checked && $(`#${row.change_net_id}`).get(0).checked) {
                        $(`#${row.ip_config_id}`).show();
                        if (row.show_config_type_flag) {
                            $(`.${row.ipv4_change_net_class}`).show();
                            $(`.${row.ipv6_change_net_class}`).show();
                        }
                        // IPv4
                        let ipv4ConfigType = parseInt($(`input[name="${row.ipv4_ip_config_type_name}"]:checked`).val())
                        if (ipv4ConfigType === CONFIG_TYPE_ENUM.auto) {
                            $(`.${row.ipv4_manual_config_class}`).hide();
                        } else {
                            $(`.${row.ipv4_manual_config_class}`).show();
                        }
                        // IPv6
                        let ipv6ConfigType = parseInt($(`input[name="${row.ipv6_ip_config_type_name}"]:checked`).val())
                        if (ipv6ConfigType === CONFIG_TYPE_ENUM.auto) {
                            $(`.${row.ipv6_manual_config_class}`).hide();
                        } else {
                            $(`.${row.ipv6_manual_config_class}`).show();
                        }
                    } else {
                        $(`#${row.ip_config_id}`).hide();
                        $(`.${row.ipv4_change_net_class}`).hide();
                        $(`.${row.ipv4_manual_config_class}`).hide();
                        $(`.${row.ipv6_change_net_class}`).hide();
                        $(`.${row.ipv6_manual_config_class}`).hide();
                    }
                });
                // IPv4配置方式
                $(`input[name="${row.ipv4_ip_config_type_name}"]`).on('ifChecked', function () {
                    if (parseInt($(this).val()) === CONFIG_TYPE_ENUM.auto) {
                        $(`.${row.ipv4_manual_config_class}`).hide();
                    } else {
                        $(`.${row.ipv4_manual_config_class}`).show();
                    }
                });
                // IPv6配置方式
                $(`input[name="${row.ipv6_ip_config_type_name}"]`).on('ifChecked', function () {
                    if (parseInt($(this).val()) === CONFIG_TYPE_ENUM.auto) {
                        $(`.${row.ipv6_manual_config_class}`).hide();
                    } else {
                        $(`.${row.ipv6_manual_config_class}`).show();
                    }
                });
                resolve();
            }, 0);
        });
    };

    /**
     * 设置恢复详情
     * @param {Number} index 索引
     * @param {Object} row 行数据
     * @param {Object} options 配置
     * @param {Number} columnsLength 列数
     */
    const setRecoveryDetail = (index, row, options, columnsLength) => {
        let extendHtml = ``;
        if (options.usage_type === USAGE_TYPE_ENUM.VM) {
            let netBusOptions = ``;
            for (const netBusInfo of row.net_bus_list) {
                let selected = '';
                if (row.net_bus === netBusInfo.value) {
                    selected = 'selected';
                }
                netBusOptions += `<option value="${netBusInfo.value}" ${selected}>${netBusInfo.name}</option>`;
            }
            let macTypeOptions = ``;
            for (const i in MAC_TYPE_ENUM) {
                let macType = MAC_TYPE_ENUM[i];
                let selected = '';
                if (row.mac_type === macType) {
                    selected = 'selected';
                }
                macTypeOptions += `<option value="${macType}" ${selected}>${MAC_TYPE_LANG_MAP[macType]}</option>`;
            }
            extendHtml = `
            <!-- 网卡类型 -->
            <div class="form-group">
                <label class="control-label col-md-3 col-md-4_en" for="${row.net_bus_id}">${LANG.UI_PUBLIC_NET_BUS_TYPE}</label>
                <div class="col-md-6 control-item">
                    <select class="form-control" id="${row.net_bus_id}" ${row.change_net_bus_flag ? '' : 'disabled'}>
                        ${netBusOptions}
                    </select>
                </div>
            </div>
            <!-- MAC地址 -->
            <div class="form-group">
                <label class="control-label col-md-3 col-md-4_en" for="${row.mac_type_id}">${LANG.UI_VM_SETTING_MAC}</label>
                <div class="col-md-6 control-item text-item net-mac-config">
                    <select class="form-control" id="${row.mac_type_id}">
                        ${macTypeOptions}
                    </select>

                    <div class="input-icon right">
                        <i class="fa"></i>
                        <input type="text"
                            class="form-control"
                            name="customMac"
                            id="${row.custom_mac_id}"
                            value="${row.mac_address}"
                            data-validate-required="true" />
                    </div>
                </div>
            </div>
            `;
        }
        let html = `<tr class="detail-view display-hide" data-detail-index="${index}">
            <td colspan="${columnsLength}">
                <div class="form-wrapper">
                    ${extendHtml}
                    <!-- 修改网络配置 -->
                    <div class="form-group with-help-block">
                        <label class="control-label col-md-3 col-md-4_en" for="${row.change_net_id}">${LANG.UI_PUBLIC_CHANGE_NETWORK_CONFIG}</label>
                        <div class="col-md-6 control-item">
                            <input type="checkbox" id="${row.change_net_id}" class="make-switch" data-on-color="primary" data-off-color="info" data-size="small">
                            <span class="help-block">${LANG.UI_PUBLIC_CHANGE_NETWORK_CONFIG_TIPS}</span>
                        </div>
                    </div>
                    <div class="form-group" id="${row.disable_network_card_id}Div">
                        <label class="control-label col-md-3 col-md-4_en" for="${row.disable_network_card_id}">${LANG.UI_PUBLIC_DISABLE_NETWORK_CARD}</label>
                        <div class="col-md-6 control-item">
                            <input type="checkbox" id="${row.disable_network_card_id}" class="make-switch" data-on-color="primary" data-off-color="info" data-size="small">
                        </div>
                    </div>
                    <div class="form-group" id="${row.ip_config_id}">
                        <label class="control-label col-md-3 col-md-4_en"></label>
                        <div class="col-md-9 control-item networkConfigWrapper__parent">
                            <div class="networkConfigWrapper">
                                <ul class="nav nav-tabs" id="${row.ip_config_id}__nav_tabs">
                                    <li class="ipv4Li active" data-toggle="tooltip" data-placement="top" data-html="true" title="${LANG.UI_PUBLIC_IPV4_CONFIG}">
                                        <a href="#${row.ipv4_tab_id}" data-toggle="tab">${LANG.UI_PUBLIC_IPV4_CONFIG}</a>
                                    </li>
                                    <li class="ipv6Li" data-toggle="tooltip" data-placement="top" data-html="true" title="${LANG.UI_PUBLIC_IPV6_CONFIG}">
                                        <a href="#${row.ipv6_tab_id}" data-placement="top" data-toggle="tab">${LANG.UI_PUBLIC_IPV6_CONFIG}</a>
                                    </li>
                                </ul>
                                <div class="tab-content detail-table">
                                    <div class="tab-pane active" id="${row.ipv4_tab_id}">
                                        <div class="panel-body">
                                            <div class="row network-config-component" id="${row.ipv4_form_id}">
                                                <!-- IP配置方式 -->
                                                <div class="form-group ${row.ipv4_change_net_class} ipConfigChangeNet">
                                                    <label class="control-label col-md-3 col-md-4_en">${LANG.UI_PUBLIC_IP_CONFIG_TYPE}</label>
                                                    <div class="col-md-6 control-item">
                                                        <label class="radio-item">
                                                            <input type="radio"
                                                                data-radio="iradio_square-blue"
                                                                value="${CONFIG_TYPE_ENUM.auto}"
                                                                ${row.ipv4_set.config_type === CONFIG_TYPE_ENUM.auto ? 'checked' : ''}
                                                                name="${row.ipv4_ip_config_type_name}"
                                                                class="icheck" id="${row.ipv4_auto_id}">${LANG.UI_PUBLIC_IP_CONFIG_TYPE_AUTO}
                                                        </label>
                                                        <label class="radio-item">
                                                            <input type="radio"
                                                                data-radio="iradio_square-blue"
                                                                value="${CONFIG_TYPE_ENUM.manual}"
                                                                ${row.ipv4_set.config_type === CONFIG_TYPE_ENUM.manual ? 'checked' : ''}
                                                                name="${row.ipv4_ip_config_type_name}"
                                                                class="icheck" id="${row.ipv4_manual_id}">${LANG.UI_PUBLIC_IP_CONFIG_TYPE_MANUAL}
                                                        </label>
                                                    </div>
                                                </div>
                                                <!-- IP设置 -->
                                                <div class="form-group ${row.ipv4_manual_config_class}">
                                                    <label class="col-md-3 control-label text-align-reverse networkConfigLabel col-md-4_en">
                                                        ${LANG.UI_PUBLIC_IP_SET}
                                                    </label>
                                                    <div class="col-md-6 networkConfigContent">
                                                        <button type="button" class="btn btn-light-primary addIpSetBtn" id="${row.ipv4_add_ip_set_id}">
                                                            <i class="viconfont vicon-biaogetianjia"></i>
                                                            <span>${LANG.UI_PUBLIC_ADD_IP_SET}</span>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="form-group ${row.ipv4_manual_config_class}">
                                                    <label class="col-md-3 control-label text-align-reverse networkConfigLabel col-md-4_en">
                                                    </label>
                                                    <div class="col-md-8 networkConfigIpSetUlWrapper">
                                                        <ul class="networkConfigIpSetUl" id="${row.ipv4_ip_set_ul_id}">
                                                            <li class="ip-set-head">
                                                                <div class="ip-set-head__ip">${LANG.UI_PUBLIC_IP_ADDRESS}</div>
                                                                <div class="ip-set-head__netmask">${LANG.UI_PUBLIC_IP_NETMASK}</div>
                                                                <div class="ip-set-head__operate">${LANG.UI_PUBLIC_OPERATION}</div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <!-- 默认网关 -->
                                                <div class="form-group ${row.ipv4_manual_config_class}">
                                                    <label class="control-label col-md-3 text-label col-md-4_en" for="${row.ipv4_gateway_id}">
                                                        ${LANG.UI_PUBLIC_IP_GATEWAY}
                                                    </label>
                                                    <div class="col-md-6 control-item text-item gatewayWrapper">
                                                        <div class="input-icon right">
                                                            <i class="fa"></i>
                                                            <input type="text"
                                                                class="form-control"
                                                                name="gateway_v4"
                                                                id="${row.ipv4_gateway_id}"
                                                                data-validate-gateway_v4="true"
                                                                data-restrict-no_whitespace="true" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- 首选DNS -->
                                                <div class="form-group ${row.ipv4_manual_config_class}">
                                                    <label class="control-label col-md-3 text-label col-md-4_en" for="${row.ipv4_dns1_id}">
                                                        ${LANG.UI_PUBLIC_IP_DNS1}
                                                    </label>
                                                    <div class="col-md-6 control-item text-item">
                                                        <div class="input-icon right">
                                                            <i class="fa"></i>
                                                            <input type="text" class="form-control" name="dns1" id="${row.ipv4_dns1_id}" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- 备用DNS -->
                                                <div class="form-group ${row.ipv4_manual_config_class}">
                                                    <label class="control-label col-md-3 text-label col-md-4_en" for="${row.ipv4_dns2_id}">
                                                        ${LANG.UI_PUBLIC_IP_DNS2}
                                                    </label>
                                                    <div class="col-md-6 control-item text-item">
                                                        <div class="input-icon right">
                                                            <i class="fa"></i>
                                                            <input type="text" class="form-control" name="dns2" id="${row.ipv4_dns2_id}" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div><!-- // END ipv4_tab_id -->
                                    <div class="tab-pane" id="${row.ipv6_tab_id}">
                                        <div class="panel-body">
                                            <div class="row network-config-component" id="${row.ipv6_form_id}">
                                                <!-- IP配置方式 -->
                                                <div class="form-group ${row.ipv6_change_net_class} ipConfigChangeNet">
                                                    <label class="control-label col-md-3 col-md-4_en">${LANG.UI_PUBLIC_IP_CONFIG_TYPE}</label>
                                                    <div class="col-md-6 control-item">
                                                        <label class="radio-item">
                                                            <input type="radio"
                                                                data-radio="iradio_square-blue"
                                                                value="${CONFIG_TYPE_ENUM.auto}"
                                                                ${row.ipv6_set.config_type === CONFIG_TYPE_ENUM.auto ? 'checked' : ''}
                                                                name="${row.ipv6_ip_config_type_name}"
                                                                class="icheck" id="${row.ipv6_auto_id}">${LANG.UI_PUBLIC_IP_CONFIG_TYPE_AUTO}
                                                        </label>
                                                        <label class="radio-item">
                                                            <input type="radio"
                                                                data-radio="iradio_square-blue"
                                                                value="${CONFIG_TYPE_ENUM.manual}"
                                                                ${row.ipv6_set.config_type === CONFIG_TYPE_ENUM.manual ? 'checked' : ''}
                                                                name="${row.ipv6_ip_config_type_name}"
                                                                class="icheck" id="${row.ipv6_manual_id}">${LANG.UI_PUBLIC_IP_CONFIG_TYPE_MANUAL}
                                                        </label>
                                                    </div>
                                                </div>
                                                <!-- IP设置 -->
                                                <div class="form-group ${row.ipv6_manual_config_class}">
                                                    <label class="col-md-3 control-label text-align-reverse networkConfigLabel col-md-4_en">
                                                        ${LANG.UI_PUBLIC_IP_SET}
                                                    </label>
                                                    <div class="col-md-6 networkConfigContent">
                                                        <button type="button" class="btn btn-light-primary addIpSetBtn" id="${row.ipv6_add_ip_set_id}">
                                                            <i class="viconfont vicon-biaogetianjia"></i>
                                                            <span>${LANG.UI_PUBLIC_ADD_IP_SET}</span>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="form-group ${row.ipv6_manual_config_class}">
                                                    <label class="col-md-3 control-label text-align-reverse networkConfigLabel col-md-4_en">
                                                    </label>
                                                    <div class="col-md-8 networkConfigIpSetUlWrapper">
                                                        <ul class="networkConfigIpSetUl" id="${row.ipv6_ip_set_ul_id}">
                                                            <li class="ip-set-head">
                                                                <div class="ip-set-head__ip">${LANG.UI_PUBLIC_IP_ADDRESS}</div>
                                                                <div class="ip-set-head__netmask">${LANG.UI_PUBLIC_PREFIX}</div>
                                                                <div class="ip-set-head__operate">${LANG.UI_PUBLIC_OPERATION}</div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <!-- 默认网关 -->
                                                <div class="form-group ${row.ipv6_manual_config_class}">
                                                    <label class="control-label col-md-3 text-label col-md-4_en" for="${row.ipv6_gateway_id}">
                                                        ${LANG.UI_PUBLIC_IP_GATEWAY}
                                                    </label>
                                                    <div class="col-md-6 control-item text-item gatewayWrapper">
                                                        <div class="input-icon right">
                                                            <i class="fa"></i>
                                                            <input type="text"
                                                                class="form-control"
                                                                name="gateway_v6"
                                                                id="${row.ipv6_gateway_id}"
                                                                data-validate-gateway_v6="true"
                                                                data-restrict-no_whitespace="true" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- 首选DNS -->
                                                <div class="form-group ${row.ipv6_manual_config_class}">
                                                    <label class="control-label col-md-3 text-label col-md-4_en" for="${row.ipv6_dns1_id}">
                                                        ${LANG.UI_PUBLIC_IP_DNS1}
                                                    </label>
                                                    <div class="col-md-6 control-item text-item">
                                                        <div class="input-icon right">
                                                            <i class="fa"></i>
                                                            <input type="text" class="form-control" name="dns1" id="${row.ipv6_dns1_id}" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- 备用DNS -->
                                                <div class="form-group ${row.ipv6_manual_config_class}">
                                                    <label class="control-label col-md-3 text-label col-md-4_en" for="${row.ipv6_dns2_id}">
                                                        ${LANG.UI_PUBLIC_IP_DNS2}
                                                    </label>
                                                    <div class="col-md-6 control-item text-item">
                                                        <div class="input-icon right">
                                                            <i class="fa"></i>
                                                            <input type="text" class="form-control" name="dns2" id="${row.ipv6_dns2_id}" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div><!-- //END ipv6_tab_id -->
                                </div>
                            </div><!-- //END networkConfigWrapper -->
                        </div>
                    </div>
                </div><!-- // END form-wrapper -->
            </td>
        </tr>`;
        $(`#${options.element_set.table_id} tr[data-index="${index}"]`).after(html);

        $(`#${options.element_set.table_id} tr[data-index="${index}"] [data-toggle="tooltip"]`).tooltip();

        // 初始化监听
        initRecoveryDetailListener(options, row).then(() => {
            // 设置默认值
            setRecoveryDetailDefault(options, row);
            // 设置验证器
            initRecoveryValidator(row);
        });
    };

    /**
     * 显示恢复详情
     * @param {String} index 索引
     * @param {String} tableId 表格ID
     */
    const showRecoveryDetail = (index, tableId) => {
        let _index = parseInt(index);
        let detailTag = $(`#${tableId} tr[data-detail-index="${_index}"]`);
        if (detailTag.length && !detailTag.hasClass('display-hide')) { // 显示了当前详情
            $(`#${tableId} tr[data-index="${_index}"] a.netAdvanceConfig i.viconfont`)
                .removeClass('vicon-zhediekuanganniu-1')
                .addClass('vicon-zhediekuanganniu');
            detailTag.addClass('display-hide');
            return;
        }
        $(`#${tableId} tr[data-index="${_index}"] a.netAdvanceConfig i.viconfont`)
            .addClass('vicon-zhediekuanganniu-1')
            .removeClass('vicon-zhediekuanganniu');
        detailTag.removeClass('display-hide');
        let otherDetailTag = $(`#${tableId} tr.detail-view`);
        if (otherDetailTag.length) {  // 表示展开了其他详情
            for (const element of otherDetailTag) {
                let otherIndex = parseInt($(element).data('detail-index'));
                if (otherIndex === _index) {
                    continue;
                }
                $(`#${tableId} tr[data-index="${otherIndex}"] a.netAdvanceConfig i.viconfont`)
                    .removeClass('vicon-zhediekuanganniu-1')
                    .addClass('vicon-zhediekuanganniu');
                $(element).addClass('display-hide');
            }
        }
    };

    /**
     * 获取恢复表格列
     * @returns {Array} 列
     */
    const getRecoveryTableColumns = (options) => {
        /**
         * @type {Object}
         */
        let advance_width = '15';
        let network_width = '50'
        if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
            advance_width = '30'
            network_width = '35' 
        }
        let columns = [{
            checkbox: true,
            width: '2',
            widthUnit: '%',
            formatter: (value, row) => {
                let ret = {};
                if (row.checked) {
                    ret.checked = true;
                }
                if (row.chk_disabled) {
                    ret.disabled = true;
                }
                return ret;
            },
        }, {
            title: LANG.UI_PUBLIC_NET_CARD,
            field: 'net_name',
            width: '30',
            widthUnit: '%',
        }];
        switch (options.usage_type) {
            case USAGE_TYPE_ENUM.CLIENT:
                columns.push({
                    title: LANG.UI_PUBLIC_MAC_ADDRESS,
                    field: 'mac_address',
                    width: '30',
                    widthUnit: '%',
                });
                break;
            case USAGE_TYPE_ENUM.VM:
            case USAGE_TYPE_ENUM.CUSTOM_VM:
                columns.push({
                    title: LANG.UI_PUBLIC_RECOVERY_NETWORK,
                    field: 'network_list',
                    width: network_width,
                    widthUnit: '%',
                    class: 'networkConfig_recoveryToNetwork',
                    clickToSelect: false,
                    formatter: (networkList, row) => {
                        let items = ``;
                        for (const networkInfo of networkList) {
                            let selected = '';
                            if (row.network === networkInfo.value) {
                                selected = 'selected';
                            }
                            items += `<option value="${networkInfo.value}" ${selected}>${networkInfo.name}</option>`;

                        }
                        return `<select class="form-control" id="${row.network_id}" style="width: 200px">
                            ${items}
                        </select>`;
                    },
                });
                break;
        }
        if (options.show_advance_flag) {
            columns.push({
                title: LANG.UI_PUBLIC_ADVANCED_CONFIG,
                width: advance_width,
                widthUnit: '%',
                clickToSelect: false,
                formatter: (value, row) => {
                    return `<a style="color: #00A3FF; cursor: pointer" class="netAdvanceConfig">
                        <span>${LANG.UI_PUBLIC_ADVANCED_CONFIG}</span>
                        <i class="viconfont vicon-zhediekuanganniu"></i>
                    </a>`;
                },
                events: {
                    'click .netAdvanceConfig': (e, value, row, index) => {
                        $(`#${options.element_set.table_id}`).bootstrapTable('expandRow', index)
                    },
                }
            });

        }
        return columns;
    };

    /**
     * 初始化恢复表格
     * @param {Object} options 配置
     */
    const initRecoveryTable = options => {
        let columns = getRecoveryTableColumns(options);
        let data = options.client_net_list;
        if (options.usage_type === USAGE_TYPE_ENUM.VM) {
            data = options.vm_net_list;
        } else if (options.usage_type === USAGE_TYPE_ENUM.CUSTOM_VM) {
            data = options.custom_vm_net_list;
        }
        let tableOption = {
            buttonsToolbar: '', // 自定义按钮工具栏class
            rightToolbarClass: '',
            tableContentWrapper: '',
            toolbarId: '',
            vin_toolbar: '',
            // 搜索
            searchInput: false,
            showSearchButton: false,
            searchAlign: 'right',
            // 排序
            sortable: false,
            showButtonText: false,
            clickToSelect: true,
            // 分页
            pagination: false,
            sidePagination: 'client',
            showExport: false,
            showColumns: false,
            // detailView: true,
            detailFormatter: (index, row) => {
                showRecoveryDetail(index, options.element_set.table_id);
            },
            columns,
            data,
        };
        $(`#${options.element_set.table_id}`).bootstrapTable('destroy').baseTableConfig().init(tableOption);

        // 设置高度
        $(`.network-config-table__wrapper .bootstrap-table .fixed-table-container .fixed-table-body`).css('height', 'auto');

        setTimeout(() => {
            if (options.show_advance_flag) {
                let rows = $(`#${options.element_set.table_id}`).bootstrapTable('getData');
                // 初始化详情
                for (const index in rows) {
                    setRecoveryDetail(index, rows[index], options, columns.length);
                }

                // 设置默认值
                for (const index in rows) {
                    if (rows[index].expand_advance_flag) {
                        $(`#${options.element_set.table_id}`).bootstrapTable('expandRow', index)
                    }
                }
            }
        }, 0);
    };

    /**
     * 监听事件
     * @param {Object} options 配置
     */
    const initListener = options => {
        switch (options.usage_type) {
            case USAGE_TYPE_ENUM.DEFAULT:
                initDefaultTypeListener(options);
                break;
            case USAGE_TYPE_ENUM.CLIENT:
            case USAGE_TYPE_ENUM.VM:
            case USAGE_TYPE_ENUM.CUSTOM_VM:
                setTimeout(() => {  // 这里需要延迟设置，不然checkbox会被uniform覆盖
                    initRecoveryTable(options);
                }, 0);
                break;
            default:
                break;
        }
    };

    //////////////////// 结束-事件监听 //////////////////

    //////////////////// 开始-获取数据 //////////////////

    /**
     * 获取默认类型数据
     * @param {Option} options 配置
     */
    const getDefaultTypeData = options => {
        let data = {
            ipv4_set: {
                config_type: parseInt($(`input[name="${options.element_set.ipv4_ip_config_type_name}"]:checked`).val()),
                ip_set: [],
                gateway: '',
                dns1: '',
                dns2: ''
            },
            ipv6_set: {
                config_type: parseInt($(`input[name="${options.element_set.ipv6_ip_config_type_name}"]:checked`).val()),
                ip_set: [],
                gateway: '',
                dns1: '',
                dns2: ''
            },
            source_ipv4_set: options.source_ipv4_set,
            source_ipv6_set: options.source_ipv6_set,
        };

        // 获取IPv4配置
        if (data.ipv4_set.config_type === CONFIG_TYPE_ENUM.manual) {
            let ipv4SetList = [];
            let ipv4SetLis = $(`#${options.element_set.ipv4_ip_set_ul_id} .ip-set-item`);
            if (ipv4SetLis.length) {
                for (const ipv4SetLi of ipv4SetLis) {
                    ipv4SetList.push({
                        ip: $(ipv4SetLi).find('input[name="ipv4"]').val().trim(),
                        netmask: convertToIPv4Decimal($(ipv4SetLi).find('input[name="netmask_v4"]').val().trim()),
                    });
                }
            }
            data.ipv4_set.ip_set = ipv4SetList;
            data.ipv4_set.gateway = $(`#${options.element_set.ipv4_gateway_id}`).val();
            data.ipv4_set.dns1 = $(`#${options.element_set.ipv4_dns1_id}`).val();
            data.ipv4_set.dns2 = $(`#${options.element_set.ipv4_dns2_id}`).val();
        }
        // 获取IPv6配置
        if (data.ipv6_set.config_type === CONFIG_TYPE_ENUM.manual) {
            let ipv6SetList = [];
            let ipv6SetLis = $(`#${options.element_set.ipv6_ip_set_ul_id} .ip-set-item`);
            if (ipv6SetLis.length) {
                for (const ipv6SetLi of ipv6SetLis) {
                    ipv6SetList.push({
                        ip: $(ipv6SetLi).find('input[name="ipv6"]').val().trim(),
                        netmask: convertToIPv6String($(ipv6SetLi).find('input[name="netmask_v6"]').val().trim()),
                    });
                }
            }
            data.ipv6_set.ip_set = ipv6SetList;
            data.ipv6_set.gateway = convertToIPv6String($(`#${options.element_set.ipv6_gateway_id}`).val());
            data.ipv6_set.dns1 = $(`#${options.element_set.ipv6_dns1_id}`).val();
            data.ipv6_set.dns2 = $(`#${options.element_set.ipv6_dns2_id}`).val();
        }
        return data;
    };

    /**
     * 获取客户端类型数据
     * @param {Object} options 配置
     */
    const getClientTypeData = options => {
        let rows = $(`#${options.element_set.table_id}`).bootstrapTable('getData');
        let checkRows = $(`#${options.element_set.table_id}`).bootstrapTable('getSelections');
        let ipData = [];
        for (const index in rows) {
            let row = rows[index];
            let rowChecked = false;
            for (const checkRow of checkRows) {
                if (checkRow.net_uuid === row.net_uuid) {
                    rowChecked = true;
                    break;
                }
            }

            let changeNetFlag = false;
            if (options.show_advance_flag) {
                changeNetFlag = $(`#${row.change_net_id}`).get(0).checked;
            }
            let ipv4Set = {
                config_type: row.ipv4_set.config_type,
                ip_set: [],
                gateway: '',
                dns1: '',
                dns2: '',
            };
            let ipv6Set = {
                config_type: row.ipv6_set.config_type,
                ip_set: [],
                gateway: '',
                dns1: '',
                dns2: '',
            };
            if (changeNetFlag) {
                // IPv4配置
                ipv4Set.config_type = parseInt($(`input[name="${row.ipv4_ip_config_type_name}"]:checked`).val());
                if (ipv4Set.config_type === CONFIG_TYPE_ENUM.manual) {
                    let ipSetLis = $(`#${row.ipv4_ip_set_ul_id} .ip-set-item`);
                    if (ipSetLis.length) {
                        for (const ipSetLi of ipSetLis) {
                            ipv4Set.ip_set.push({
                                ip: $(ipSetLi).find('input[name="ipv4"]').val().trim(),
                                netmask: convertToIPv4Decimal($(ipSetLi).find('input[name="netmask_v4"]').val().trim()),
                            });
                        }
                    }
                    ipv4Set.gateway = $(`#${row.ipv4_gateway_id}`).val();
                    ipv4Set.dns1 = $(`#${row.ipv4_dns1_id}`).val();
                    ipv4Set.dns2 = $(`#${row.ipv4_dns2_id}`).val();
                }
                // IPv6配置
                ipv6Set.config_type = parseInt($(`input[name="${row.ipv6_ip_config_type_name}"]:checked`).val());
                if (ipv6Set.config_type === CONFIG_TYPE_ENUM.manual) {
                    let ipSetLis = $(`#${row.ipv6_ip_set_ul_id} .ip-set-item`);
                    if (ipSetLis.length) {
                        for (const ipSetLi of ipSetLis) {
                            ipv6Set.ip_set.push({
                                ip: $(ipSetLi).find('input[name="ipv6"]').val().trim(),
                                netmask: convertToIPv6String($(ipSetLi).find('input[name="netmask_v6"]').val().trim()),
                            });
                        }
                    }
                    ipv6Set.gateway = $(`#${row.ipv6_gateway_id}`).val();
                    ipv6Set.dns1 = $(`#${row.ipv6_dns1_id}`).val();
                    ipv6Set.dns2 = $(`#${row.ipv6_dns2_id}`).val();
                }
            } else {
                ipv4Set = row.ipv4_set;
                ipv6Set = row.ipv6_set;
            }
            ipData.push({
                checked: rowChecked,
                net_uuid: row.net_uuid,
                net_name: row.net_name,
                net_mac: row.net_mac,
                mac_address: row.mac_address,
                change_net_flag: changeNetFlag,
                ipv4_set: ipv4Set,
                ipv6_set: ipv6Set,
                source_ipv4_set: row.source_ipv4_set,
                source_ipv6_set: row.source_ipv6_set,
                ipv4_form_id: row.ipv4_form_id,
                ipv6_form_id: row.ipv6_form_id,
                ipv4_tab_id: row.ipv4_tab_id,
                ipv6_tab_id: row.ipv6_tab_id,
                ipv4_validator: row.ipv4_validator,
                ipv6_validator: row.ipv6_validator,
                show_advance_flag: options.show_advance_flag,
                show_ip_switch_nav_tabs_flag: row.show_ip_switch_nav_tabs_flag,
                target_net_name: `${row.net_name}${parseInt(index) + 1}`,
                mac_type_id: row.mac_type_id,
                custom_mac_id: row.custom_mac_id,
            });
        }
        return ipData;
    };

    /**
     * 获取虚拟化类型数据
     * @param {Object} options 配置
     */
    const getVmTypeData = options => {
        let rows = $(`#${options.element_set.table_id}`).bootstrapTable('getData');
        let checkRows = $(`#${options.element_set.table_id}`).bootstrapTable('getSelections');
        let ipData = [];
        for (const index in rows) {
            let row = rows[index];
            let rowChecked = false;
            for (const checkRow of checkRows) {
                if (checkRow.net_uuid === row.net_uuid) {
                    rowChecked = true;
                    break;
                }
            }

            let network = $(`#${row.network_id}`).val();
            let networkName = $(`#${row.network_id} option:selected`).html().trim();
            let netBus = '0';
            let macType = MAC_TYPE_ENUM.AUTO;
            let macAddress = '';
            let changeNetFlag = false;
            if (options.show_advance_flag) {
                netBus = $(`#${row.net_bus_id}`).val();
                macType = parseInt($(`#${row.mac_type_id}`).val());
                macAddress = $(`#${row.custom_mac_id}`).val();
                changeNetFlag = $(`#${row.change_net_id}`).get(0).checked;
            }
            let ipv4Set = {
                config_type: row.ipv4_set.config_type,
                ip_set: [],
                gateway: '',
                dns1: '',
                dns2: '',
            };
            let ipv6Set = {
                config_type: row.ipv6_set.config_type,
                ip_set: [],
                gateway: '',
                dns1: '',
                dns2: '',
            };
            if (changeNetFlag) {
                // IPv4配置
                ipv4Set.config_type = parseInt($(`input[name="${row.ipv4_ip_config_type_name}"]:checked`).val());
                if (ipv4Set.config_type === CONFIG_TYPE_ENUM.manual) {
                    let ipSetLis = $(`#${row.ipv4_ip_set_ul_id} .ip-set-item`);
                    if (ipSetLis.length) {
                        for (const ipSetLi of ipSetLis) {
                            ipv4Set.ip_set.push({
                                ip: $(ipSetLi).find('input[name="ipv4"]').val().trim(),
                                netmask: convertToIPv4Decimal($(ipSetLi).find('input[name="netmask_v4"]').val().trim()),
                            });
                        }
                    }
                    ipv4Set.gateway = $(`#${row.ipv4_gateway_id}`).val();
                    ipv4Set.dns1 = $(`#${row.ipv4_dns1_id}`).val();
                    ipv4Set.dns2 = $(`#${row.ipv4_dns2_id}`).val();
                }
                // IPv6配置
                ipv6Set.config_type = parseInt($(`input[name="${row.ipv6_ip_config_type_name}"]:checked`).val());
                if (ipv6Set.config_type === CONFIG_TYPE_ENUM.manual) {
                    let ipSetLis = $(`#${row.ipv6_ip_set_ul_id} .ip-set-item`);
                    if (ipSetLis.length) {
                        for (const ipSetLi of ipSetLis) {
                            ipv6Set.ip_set.push({
                                ip: $(ipSetLi).find('input[name="ipv6"]').val().trim(),
                                netmask: convertToIPv6String($(ipSetLi).find('input[name="netmask_v6"]').val().trim()),
                            });
                        }
                    }
                    ipv6Set.gateway = $(`#${row.ipv6_gateway_id}`).val();
                    ipv6Set.dns1 = $(`#${row.ipv6_dns1_id}`).val();
                    ipv6Set.dns2 = $(`#${row.ipv6_dns2_id}`).val();
                }
            } else {
                ipv4Set = row.ipv4_set;
                ipv6Set = row.ipv6_set;
            }
            ipData.push({
                checked: rowChecked,
                net_name: row.net_name,
                net_uuid: row.net_uuid,
                net_mac: row.net_mac,
                network,
                network_name: networkName,
                net_bus: netBus,
                mac_type: macType,
                mac_address: macAddress,
                change_net_flag: changeNetFlag,
                ipv4_set: ipv4Set,
                ipv6_set: ipv6Set,
                source_ipv4_set: row.source_ipv4_set,
                source_ipv6_set: row.source_ipv6_set,
                ipv4_form_id: row.ipv4_form_id,
                ipv6_form_id: row.ipv6_form_id,
                ipv4_tab_id: row.ipv4_tab_id,
                ipv6_tab_id: row.ipv6_tab_id,
                ipv4_validator: row.ipv4_validator,
                ipv6_validator: row.ipv6_validator,
                show_advance_flag: options.show_advance_flag,
                show_ip_switch_nav_tabs_flag: row.show_ip_switch_nav_tabs_flag,
                target_net_name: `${row.net_name}${parseInt(index) + 1}`,
                mac_type_id: row.mac_type_id,
                custom_mac_id: row.custom_mac_id,
            });
        }
        return ipData;
    };

    /**
     * 获取内嵌虚拟化类型数据
     * @param {Object} options 配置
     */
    const getCustomVmTypeData = options => {
        let rows = $(`#${options.element_set.table_id}`).bootstrapTable('getData');
        let checkRows = $(`#${options.element_set.table_id}`).bootstrapTable('getSelections');
        let ipData = [];
        for (const index in rows) {
            let row = rows[index];
            let rowChecked = false;
            for (const checkRow of checkRows) {
                if (checkRow.net_uuid === row.net_uuid) {
                    rowChecked = true;
                    break;
                }
            }

            let network = $(`#${row.network_id}`).val();
            let networkName = $(`#${row.network_id} option:selected`).html().trim();
            let changeNetFlag = false;
            if (options.show_advance_flag) {
                changeNetFlag = $(`#${row.change_net_id}`).get(0).checked;
            }
            let ipv4Set = {
                config_type: row.ipv4_set.config_type,
                ip_set: [],
                gateway: '',
                dns1: '',
                dns2: '',
            };
            let ipv6Set = {
                config_type: row.ipv6_set.config_type,
                ip_set: [],
                gateway: '',
                dns1: '',
                dns2: '',
            };
            if (changeNetFlag) {
                // IPv4配置
                ipv4Set.config_type = parseInt($(`input[name="${row.ipv4_ip_config_type_name}"]:checked`).val());
                if (ipv4Set.config_type === CONFIG_TYPE_ENUM.manual) {
                    let ipSetLis = $(`#${row.ipv4_ip_set_ul_id} .ip-set-item`);
                    if (ipSetLis.length) {
                        for (const ipSetLi of ipSetLis) {
                            ipv4Set.ip_set.push({
                                ip: $(ipSetLi).find('input[name="ipv4"]').val().trim(),
                                netmask: convertToIPv4Decimal($(ipSetLi).find('input[name="netmask_v4"]').val().trim()),
                            });
                        }
                    }
                    ipv4Set.gateway = $(`#${row.ipv4_gateway_id}`).val();
                    ipv4Set.dns1 = $(`#${row.ipv4_dns1_id}`).val();
                    ipv4Set.dns2 = $(`#${row.ipv4_dns2_id}`).val();
                }
                // IPv6配置
                ipv6Set.config_type = parseInt($(`input[name="${row.ipv6_ip_config_type_name}"]:checked`).val());
                if (ipv6Set.config_type === CONFIG_TYPE_ENUM.manual) {
                    let ipSetLis = $(`#${row.ipv6_ip_set_ul_id} .ip-set-item`);
                    if (ipSetLis.length) {
                        for (const ipSetLi of ipSetLis) {
                            ipv6Set.ip_set.push({
                                ip: $(ipSetLi).find('input[name="ipv6"]').val().trim(),
                                netmask: convertToIPv6String($(ipSetLi).find('input[name="netmask_v6"]').val().trim()),
                            });
                        }
                    }
                    ipv6Set.gateway = $(`#${row.ipv6_gateway_id}`).val();
                    ipv6Set.dns1 = $(`#${row.ipv6_dns1_id}`).val();
                    ipv6Set.dns2 = $(`#${row.ipv6_dns2_id}`).val();
                }
            } else {
                ipv4Set = row.ipv4_set;
                ipv6Set = row.ipv6_set;
            }
            ipData.push({
                checked: rowChecked,
                net_name: row.net_name,
                net_uuid: row.net_uuid,
                net_mac: row.net_mac,
                network,
                network_name: networkName,
                change_net_flag: changeNetFlag,
                ipv4_set: ipv4Set,
                ipv6_set: ipv6Set,
                source_ipv4_set: row.source_ipv4_set,
                source_ipv6_set: row.source_ipv6_set,
                ipv4_form_id: row.ipv4_form_id,
                ipv6_form_id: row.ipv6_form_id,
                ipv4_tab_id: row.ipv4_tab_id,
                ipv6_tab_id: row.ipv6_tab_id,
                ipv4_validator: row.ipv4_validator,
                ipv6_validator: row.ipv6_validator,
                show_advance_flag: options.show_advance_flag,
                show_ip_switch_nav_tabs_flag: row.show_ip_switch_nav_tabs_flag,
                target_net_name: `${row.net_name}${parseInt(index) + 1}`,
                mac_type_id: row.mac_type_id,
                custom_mac_id: row.custom_mac_id,
            });
        }
        return ipData;
    };

    /**
     * 获取数据
     * @param {Object} options 配置
     */
    const getData = options => {
        switch (options.usage_type) {
            case USAGE_TYPE_ENUM.DEFAULT:
                return getDefaultTypeData(options);
            case USAGE_TYPE_ENUM.CLIENT:
                return getClientTypeData(options);
            case USAGE_TYPE_ENUM.VM:
                return getVmTypeData(options);
            case USAGE_TYPE_ENUM.CUSTOM_VM:
                return getCustomVmTypeData(options);
            default:
                break;
        }
    };

    //////////////////// 结束-获取数据 //////////////////

    const switchIpTab = (oldTabId, newTabId) => {
        $(`#${oldTabId}`).removeClass('active');
        $(`#${newTabId}`).addClass('active');
        $(`a[href="#${oldTabId}"]`).closest('li').removeClass('active');
        $(`a[href="#${newTabId}"]`).closest('li').addClass('active');
    };

    const expandRow = (index, tableId) => {
        let expandIcon = $(`#${tableId} tr[data-index="${index}"] a.netAdvanceConfig i.viconfont`);
        if (!expandIcon.hasClass('vicon-zhediekuanganniu-1')) {  // 这里判断是否展开过，展开了就不用再展开了
            $(`#${tableId}`).bootstrapTable('expandRow', index);
        }
    }

    $.fn.NetworkConfig = {
        /**
         * 初始化
         * @param {jQuery} obj
         * @param {Object} options
         * @param {Number} usage_type
         */
        init: function (obj, options = {}, usage_type = 1) {
            if (!obj || !obj.length) {
                return false;
            }
            options = getOptions(obj, options, usage_type);
            // 初始化页面
            initContent(obj, options);
            // 注册事件
            initListener(options);
            if (options.usage_type === USAGE_TYPE_ENUM.DEFAULT) {
                // 注册验证器
                initValidator(options);
            }
            optionCaches[obj.attr('id')] = options;
        },
        valid: function (obj) {
            let options = this.getOptions(obj);
            let ipData = getData(options);
            switch (options.usage_type) {
                case USAGE_TYPE_ENUM.DEFAULT:
                    // ipv4验证
                    if (ipData.ipv4_set.config_type === CONFIG_TYPE_ENUM.manual) {
                        if (!ipData.ipv4_set.ip_set.length) {  // 没有设置任何的IP
                            switchIpTab(options.element_set.ipv6_tab_id, options.element_set.ipv4_tab_id);
                            return false;
                        }
                        if (!options.element_set.ipv4_validator.validateAll()) {
                            switchIpTab(options.element_set.ipv6_tab_id, options.element_set.ipv4_tab_id);
                            return false;
                        }
                    }
                    // ipv6验证
                    if (ipData.ipv6_set.config_type === CONFIG_TYPE_ENUM.manual) {
                        if (!ipData.ipv6_set.ip_set.length) {  // 没有设置任何的IP
                            switchIpTab(options.element_set.ipv4_tab_id, options.element_set.ipv6_tab_id);
                            return false;
                        }
                        if (!options.element_set.ipv6_validator.validateAll()) {
                            switchIpTab(options.element_set.ipv4_tab_id, options.element_set.ipv6_tab_id);
                            return false;
                        }
                    }
                    return true;
                case USAGE_TYPE_ENUM.CLIENT:
                case USAGE_TYPE_ENUM.VM:
                case USAGE_TYPE_ENUM.CUSTOM_VM:
                    let validateFlag = true;
                    for (const index in ipData) {
                        let ipRow = ipData[index];
                        // mac地址验证
                        if (options.usage_type === USAGE_TYPE_ENUM.VM) {
                            if (ipRow.mac_type === MAC_TYPE_ENUM.CUSTOM) {
                                $.formValidateElement($(`#${ipRow.custom_mac_id}`).get(0));
                                if (!ipRow.mac_address) {
                                    validateFlag = false;
                                    switchIpTab(ipRow.ipv6_tab_id, ipRow.ipv4_tab_id);
                                    expandRow(index, options.element_set.table_id);
                                    break;
                                }
                            }
                        }
                        if (!ipRow.show_advance_flag) {
                            continue;
                        }
                        if (!ipRow.checked) {
                            continue;
                        }
                        if (!ipRow.change_net_flag) {
                            continue;
                        }
                        // IPv4验证
                        if (ipRow.ipv4_set.config_type === CONFIG_TYPE_ENUM.manual) {
                            if (!ipRow.ipv4_validator.validateAll()) {
                                validateFlag = false;
                                switchIpTab(ipRow.ipv6_tab_id, ipRow.ipv4_tab_id);
                                expandRow(index, options.element_set.table_id);
                                break;
                            }
                        }
                        if (ipRow.show_ip_switch_nav_tabs_flag) {
                            // IPv6验证
                            if (ipRow.ipv6_set.config_type === CONFIG_TYPE_ENUM.manual) {
                                if (!ipRow.ipv6_validator.validateAll()) {
                                    validateFlag = false;
                                    switchIpTab(ipRow.ipv4_tab_id, ipRow.ipv6_tab_id);
                                    expandRow(index, options.element_set.table_id);
                                    break;
                                }
                            }
                        }
                    }
                    return validateFlag;
            }

        },
        getOptions: function (obj) {
            return optionCaches[obj.attr('id')];
        },
        getData: function (obj) {
            if (!this.valid(obj)) {
                return false;
            }
            return getData(this.getOptions(obj));
        },
        show: function (obj) {
            showContent(this.getOptions(obj));
        },
        hide: function (obj) {
            hideContent(this.getOptions(obj));
        },
        // 枚举定义
        CONFIG_TYPE_ENUM,
        USAGE_TYPE_ENUM,
        MAC_TYPE_ENUM,
        IP_TYPE_ENUM,
    };

})(jQuery, LANG);