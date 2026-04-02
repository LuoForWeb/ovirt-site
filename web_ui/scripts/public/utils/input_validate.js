/*
 * @note: 
 * @author: chenyunfeng@vinchin.com
 * @Description: 
 * @Date: 2025-03-05 15:23:06
 * @LastEditTime: 2025-04-25 17:44:31
 * @Version: 2.0
 * @copyright: Copyright 2024 vinchin.com
 */
// 普通输入框验证单引号（'）双引号（"）反斜杠（\）分号（;）ASCII控制符（空格、制表符）尖括号（<>）与符号（&）百分号（%）
const STRING_REGEX_CFG = {
    regex_s: /^(?!.*['"\\\;?<>%]).{0,256}$/,
    tip: LANG.UI_INPUT_REGEX_TIPS_STRING,
};
// 邮箱（user.name+tag+sorting@example.com）
const EMAIL_REGEX = {
    regex_s: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
    tip: LANG.UI_INPUT_REGEX_TIPS_EMAIL
};
// 高强度密码（包含数字、小写字母、大写字母和特殊字符(@, $, !, %, *, ?, &, +, -)，且长度至少为8个字符）
const STRONG_PASSWORD_REGEX = {
    regex_s: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*.?&=+-]).{8,16}$/,
    tip: LANG.UI_INPUT_REGEX_TIPS_PASSWORD
};
// ipv4地址
const IPV4_REGEX = {
    regex_s: /^(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/,
    tip: LANG.UI_INPUT_REGEX_TIPS_IPV4
};
// ipv6地址
const IPV6_REGEX = {
    regex_s: /^(?:(?:[a-fA-F0-9]{1,4}:){7}[a-fA-F0-9]{1,4}|(?:[a-fA-F0-9]{1,4}:){1,7}:|(?:[a-fA-F0-9]{1,4}:){1,6}:[a-fA-F0-9]{1,4}|(?:[a-fA-F0-9]{1,4}:){1,5}(?::[a-fA-F0-9]{1,4}){1,2}|(?:[a-fA-F0-9]{1,4}:){1,4}(?::[a-fA-F0-9]{1,4}){1,3}|(?:[a-fA-F0-9]{1,4}:){1,3}(?::[a-fA-F0-9]{1,4}){1,4}|(?:[a-fA-F0-9]{1,4}:){1,2}(?::[a-fA-F0-9]{1,4}){1,5}|[a-fA-F0-9]{1,4}:(?:(?::[a-fA-F0-9]{1,4}){1,6})|:(?:(?::[a-fA-F0-9]{1,4}){1,7}|:)|fe80:(?::[a-fA-F0-9]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(?::0{1,4}){0,1}:){0,1}(?:25[0-5]|(?:2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.(?:25[0-5]|(?:2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.(?:25[0-5]|(?:2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.(?:25[0-5]|(?:2[0-4]|1{0,1}[0-9]){0,1}[0-9])|(?:[a-fA-F0-9]{1,4}:){1,4}:(?:25[0-5]|(?:2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.(?:25[0-5]|(?:2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.(?:25[0-5]|(?:2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.(?:25[0-5]|(?:2[0-4]|1{0,1}[0-9]){0,1}[0-9]))$/,
    tip: LANG.UI_INPUT_REGEX_TIPS_IPV6
};
// 端口号0-65535
const PORT_REGEX = {
    regex_s: /^(6553[0-5]|655[0-2][0-9]|65[0-4][0-9]{2}|6[0-4][0-9]{3}|[1-5][0-9]{4}|[1-9][0-9]{0,3}|0)$/,
    tip: LANG.UI_INPUT_REGEX_TIPS_PORT
};
// 文件路径Windows、linux相对/绝对路径
const PATH_REGEX = {
    regex_s: /^(?:[a-zA-Z]:\\|\\\\|\/|\.\/|\.\.\/)(?:[^\\/:*?"<>|\r\n]+\\|[^\/]*\/)*[^\\/:*?"<>|\r\n\/]*$/,
    tip: LANG.UI_INPUT_REGEX_TIPS_FILE_PATH
};
// 网站地址https、http
const URL_REGEX = {
    regex_s: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+(\[[0-9a-fA-F:]+\])?(\.[a-zA-Z]{2,})?$/,
    tip: LANG.UI_INPUT_REGEX_TIPS_URL
};
// 纯数字
const NUMBER_REGEX = {
    regex_s: /^\d+$/,
    tip: LANG.UI_INPUT_REGEX_TIPS_NUMBER
};
// 联系电话（国内手机号（11位以1开头的数字）国内座机号码（区号+电话号码） 国际电话号码（国际区号和本地号码））
const PHONE_REGEX = {
    regex_s: /^(1[3-9]\d{9})|(0\d{2,3}-?\d{7,8})|(\+?\d{1,3}[-.\s]?\(?\d{1,4}\)?[-.\s]?\d{1,4}[-.\s]?\d{1,4}[-.\s]?\d{1,9})$/,
    tip: LANG.UI_INPUT_REGEX_TIPS_PHONE
};
// ip地址校验   ipv4和ipv6都允许输入
const IPV4_IPV6_REGEX = {
    regex_s: /^(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$|^(?:(?:[a-fA-F0-9]{1,4}:){7}[a-fA-F0-9]{1,4}|(?:[a-fA-F0-9]{1,4}:){1,7}:|(?:[a-fA-F0-9]{1,4}:){1,6}:[a-fA-F0-9]{1,4}|(?:[a-fA-F0-9]{1,4}:){1,5}(?::[a-fA-F0-9]{1,4}){1,2}|(?:[a-fA-F0-9]{1,4}:){1,4}(?::[a-fA-F0-9]{1,4}){1,3}|(?:[a-fA-F0-9]{1,4}:){1,3}(?::[a-fA-F0-9]{1,4}){1,4}|(?:[a-fA-F0-9]{1,4}:){1,2}(?::[a-fA-F0-9]{1,4}){1,5}|[a-fA-F0-9]{1,4}:(?:(?::[a-fA-F0-9]{1,4}){1,6})|:(?:(?::[a-fA-F0-9]{1,4}){1,7}|:)|fe80:(?::[a-fA-F0-9]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(?::0{1,4}){0,1}:){0,1}(?:25[0-5]|(?:2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.(?:25[0-5]|(?:2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.(?:25[0-5]|(?:2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.(?:25[0-5]|(?:2[0-4]|1{0,1}[0-9]){0,1}[0-9])|(?:[a-fA-F0-9]{1,4}:){1,4}:(?:25[0-5]|(?:2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.(?:25[0-5]|(?:2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.(?:25[0-5]|(?:2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.(?:25[0-5]|(?:2[0-4]|1{0,1}[0-9]){0,1}[0-9]))$/,
    tip: LANG.UI_INPUT_REGEX_TIPS_IPV4_IPV6
};
const TYPE_REGEX_MAP = {
    'string': STRING_REGEX_CFG,
    'email': EMAIL_REGEX,
    'password': STRONG_PASSWORD_REGEX,
    'ipv4': IPV4_REGEX,
    'ipv6': IPV6_REGEX,
    'port': PORT_REGEX,
    'path': PATH_REGEX,
    'url': URL_REGEX,
    'number': NUMBER_REGEX,
    'phone': PHONE_REGEX,
    'ip': IPV4_IPV6_REGEX,
}
/**
 * 自定义输入验证
 * @param {string} validateType 
 * @param {*} validateValue 
 * @param {*} $this 
 * @returns {boolean} 输入的内容是否合理
 * 用法：
 * 1.绑定输入框<input type="text" onchange="customInputValidate('ipv4',this.value, $(this))" />
 * 2.直接校验内容：customInputValidate('ipv4', '192.168.1.1')
 */
const customInputValidate = function(validateType, validateValue, $_dom = null) {
    // 输入为空不验证
    if(validateValue.trim() == ''){
        if($_dom){
            initPrimaryDom($_dom);
        }
        return true;
    }
    // 匹配成功
    if(TYPE_REGEX_MAP[validateType].regex_s.test(validateValue)){
        if ($_dom){
            initValidDom($_dom);
        }
        return true
    // 匹配失败
    } else {
        if ($_dom){
            initInvalidDom(TYPE_REGEX_MAP[validateType].tip, $_dom);
        }
        return false
    }
    /**
     * 验证失败
     * 输入框border修改为红色
     * 增加输入框内图标与提示信息
     * @param {*} tips 
     * @param {*} dom 
     */
    function initInvalidDom(tips, _dom){
        let parentEl = _dom.parent();
        parentEl.removeClass('input-valid_wrap').addClass('input-invalid_wrap');
        _dom.removeClass('is-valid').addClass('is-invalid');
        if(parentEl.find('.fa.fa-warning').length == 0){
            let errorFeedback = `<i class="fa fa-warning" title="${tips}"></i>`;
            parentEl.append(errorFeedback);
        }
    }
    /**
     * 验证成功
     * 输入框border修改为绿色
     * 去掉输入框内图标与提示信息
     * @param {*} _dom 
     */
    function initValidDom(_dom){
        let parentEl = _dom.parent();
        parentEl.removeClass('input-invalid_wrap').addClass('input-valid_wrap');
        _dom.removeClass('is-invalid').addClass('is-valid');
        parentEl.find('.fa.fa-warning').remove();
    }
    /**
     * 删除提示
     * 删除输入框颜色和图标信息
     * @param {*} _dom 
     */
    function initPrimaryDom(_dom){
        let parentEl = _dom.parent();
        parentEl.removeClass('input-invalid_wrap').removeClass('input-valid_wrap');
        _dom.removeClass('is-invalid').removeClass('is-valid');
        parentEl.find('.fa.fa-warning').remove();
    }
}

const host_name_limit = {
    "Linux":{
        'len': '63',
        'limit': {
            pattern: '^(?!-)[a-zA-Z](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?$',
            flags: 'i' // 不区分大小写
        },
        'msg':LANG.UI_MACHINE_OS_HOSTNAME_MSG_63
    },
    "Windows":{
        'len': '15',
        'limit':{
            'pattern': "^(?![0-9]+$)(?=.{1,15}$)(?!(CON|PRN|AUX|NUL|COM[1-9]|LPT[1-9])$)[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$",
            'flags': "i", // 不区分大小写
        },
        'msg':LANG.UI_MACHINE_OS_HOSTNAME_MSG_15
    }
}
/**
 * 重置主机名格式校验
 * @param {*} _dom
 */
function validateHostName(validateType, validateValue, os_type) {
    let hostReg = new RegExp(host_name_limit[os_type].limit['pattern'],host_name_limit[os_type].limit['flags']);
    if(validateValue.length> host_name_limit[os_type].len){
        UIToastr.showWarning(LANG.UI_OS_DETAILS_HOST_NAME, LANG.UI_OS_DETAILS_HOST_NAME + host_name_limit[os_type].msg);
        return false;
    }
    if(validateValue == "" || !hostReg.test(validateValue)){
        UIToastr.showWarning(LANG.UI_OS_DETAILS_HOST_NAME, LANG.UI_MACHINE_OS_HOSTNAME_TIPS);
        return false;
    }
    return true;
}