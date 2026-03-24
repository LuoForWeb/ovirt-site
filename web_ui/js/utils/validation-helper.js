/*
 * @Author: ChengJiaFu
 * @Date: 2026-01-28 16:15:40
 * @Description: 校验工具函数
 * @version: 1.0
 */
const EMAIL_VALID_EXP = /^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/; // 邮箱地址校验正则

/**
 * 多个电子邮箱校验（换行符分隔）
 * @param {*} val email str
 * @returns result
 */
const illeagalEmailsCheck = (val) => {
    let result = {
        flag: false,
        index: 0
    };

    let emails = val.split('\n');

    emails = emails.filter(Boolean).map(i => {
        // 空格替换为空字符串
        return i.replace(/\s/g, '');
    }).filter(Boolean);

    for (let i = 0; i < emails.length; i++) {
        if (!EMAIL_VALID_EXP.test(emails[i])) {
            result.flag = true;
            result.index = i + 1;
            break;
        } else {
            result.flag = false;
        }
    }

    return result;
};

/**
 * 监测输入路径是否符合规则
 */
const checkPath = function (value, flag) {
    let newValue = value.replace(/\//gi, "/"); //正则替换  把输入的\替换成/

    let re1 = '((?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?))(?![\\d])'; // IPv4 IP Address 1
    let re2 = '(:)'; // Any Single Character 1
    let re3 = '((?:\\/[\\w\\.\\-]+)+)'; // Unix Path 1
    let re4 = '(.*)';
    let path = /^([a-zA-Z]:|\\\\[^\\\\/:*?"<>|]+)(\\[^\\\\/:*?"<>|]+)*\\?$/;
    let linux_path = '^(\\/[\\w\\-]+)+\\/?$';//linux路径检测
    let cn_word = '[\u4e00-\u9fa5]'; //中文检测
    let p1 = new RegExp(re1 + re2 + re3, ["i"]);
    //	let p2 = new RegExp(re4+re2+re3, ["i"]);
    let p2 = new RegExp(path);
    let p3 = new RegExp(linux_path);
    let p4 = new RegExp(cn_word, ["g"]);
    if (flag == 'Linux') { //如果为linux系统则只能输入linux系统目录
        return !!(p3.exec(newValue) && !p4.exec(newValue))
    } else if (flag == 'Windows') {
        let dd = p2.exec(newValue);
        return !!(p2.exec(newValue) && !p4.exec(newValue))
    }
    return !!((p2.exec(newValue) || p3.exec(newValue)) && !p4.exec(newValue));

}

const checkFilePath = function (value, flag) {
    let newValue = value.replace(/\//gi, "/"); //正则替换  把输入的\替换成/

    let re1 = '((?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?))(?![\\d])'; // IPv4 IP Address 1
    let re2 = '(:)'; // Any Single Character 1
    let re3 = '((?:\\/[\\w\\.\\-]+)+)'; // Unix Path 1
    let re4 = '(.*)';
    let path = /^([a-zA-Z]:|\\\\[^\\\\/:*?"<>|]+)(\\[^\\\\/:*?"<>|]+)*\\?$/;
    let linux_path = '^\\/([\\w\\.\\-]+\\/?)+$';//linux路径检测
    let cn_word = '[\u4e00-\u9fa5]'; //中文检测
    let p1 = new RegExp(re1 + re2 + re3, ["i"]);
    //	let p2 = new RegExp(re4+re2+re3, ["i"]);
    let p2 = new RegExp(path);
    let p3 = new RegExp(linux_path);
    let p4 = new RegExp(cn_word, ["g"]);
    if (flag == 'Linux') { //如果为linux系统则只能输入linux系统目录
        return !!(p3.exec(newValue) && !p4.exec(newValue));
    } else if (flag == 'Windows') {
        let dd = p2.exec(newValue);
        return !!(p2.exec(newValue));
    }
    return !!((p2.exec(newValue) || p3.exec(newValue)) && !p4.exec(newValue));

}

//判断数组中是否有相同元素存在
const arrayIsRepeat = function (arr) {
    let hash = {};
    for (let i in arr) {
        if (hash[arr[i]]) {
            return true;
        }
        hash[arr[i]] = true; // 不存在该元素，则赋值为true，可以赋任意值，相应的修改if判断条件即可
    }
    return false;
}

const ipV4V6 = function (value) {
    //let returns = /^(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)$/i.test(value);
    //let returns2 = /^([\da-fA-F]{1,4}:){6}((25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(25[0-5]|2[0-4]\d|[01]?\d\d?)$|^::([\da-fA-F]{1,4}:){0,4}((25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(25[0-5]|2[0-4]\d|[01]?\d\d?)$|^([\da-fA-F]{1,4}:):([\da-fA-F]{1,4}:){0,3}((25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(25[0-5]|2[0-4]\d|[01]?\d\d?)$|^([\da-fA-F]{1,4}:){2}:([\da-fA-F]{1,4}:){0,2}((25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(25[0-5]|2[0-4]\d|[01]?\d\d?)$|^([\da-fA-F]{1,4}:){3}:([\da-fA-F]{1,4}:){0,1}((25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(25[0-5]|2[0-4]\d|[01]?\d\d?)$|^([\da-fA-F]{1,4}:){4}:((25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(25[0-5]|2[0-4]\d|[01]?\d\d?)$|^([\da-fA-F]{1,4}:){7}[\da-fA-F]{1,4}$|^:((:[\da-fA-F]{1,4}){1,6}|:)$|^[\da-fA-F]{1,4}:((:[\da-fA-F]{1,4}){1,5}|:)$|^([\da-fA-F]{1,4}:){2}((:[\da-fA-F]{1,4}){1,4}|:)$|^([\da-fA-F]{1,4}:){3}((:[\da-fA-F]{1,4}){1,3}|:)$|^([\da-fA-F]{1,4}:){4}((:[\da-fA-F]{1,4}){1,2}|:)$|^([\da-fA-F]{1,4}:){5}:([\da-fA-F]{1,4})?$|^([\da-fA-F]{1,4}:){6}:$/i.test(value);
    let ipRule = /^(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)$|^([\da-fA-F]{1,4}:){6}((25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(25[0-5]|2[0-4]\d|[01]?\d\d?)$|^::([\da-fA-F]{1,4}:){0,4}((25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(25[0-5]|2[0-4]\d|[01]?\d\d?)$|^([\da-fA-F]{1,4}:):([\da-fA-F]{1,4}:){0,3}((25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(25[0-5]|2[0-4]\d|[01]?\d\d?)$|^([\da-fA-F]{1,4}:){2}:([\da-fA-F]{1,4}:){0,2}((25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(25[0-5]|2[0-4]\d|[01]?\d\d?)$|^([\da-fA-F]{1,4}:){3}:([\da-fA-F]{1,4}:){0,1}((25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(25[0-5]|2[0-4]\d|[01]?\d\d?)$|^([\da-fA-F]{1,4}:){4}:((25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(25[0-5]|2[0-4]\d|[01]?\d\d?)$|^([\da-fA-F]{1,4}:){7}[\da-fA-F]{1,4}$|^:((:[\da-fA-F]{1,4}){1,6}|:)$|^[\da-fA-F]{1,4}:((:[\da-fA-F]{1,4}){1,5}|:)$|^([\da-fA-F]{1,4}:){2}((:[\da-fA-F]{1,4}){1,4}|:)$|^([\da-fA-F]{1,4}:){3}((:[\da-fA-F]{1,4}){1,3}|:)$|^([\da-fA-F]{1,4}:){4}((:[\da-fA-F]{1,4}){1,2}|:)$|^([\da-fA-F]{1,4}:){5}:([\da-fA-F]{1,4})?$|^([\da-fA-F]{1,4}:){6}:$/;
    let ipv6c = /^(([a-f0-9]{1,4}:){1,6}:|:(:[a-f0-9]{1,4}){1,6}|::)$/; // 校验简写的ipv6
    return ipRule.test(value) || ipv6c.test(value);
    //return returns || returns2;
}

/**
 * 校验时间输入并返回详细结果
 * @param {number|string} d 天数
 * @param {number|string} daysLimit 天数限制
 * @param {number|string} h 小时数
 * @param {number|string} m 分钟数
 * @param {number|string} s 秒数
 * @returns {object} { isValid: boolean, message: string }
 */
const validateTimeInputDetailed = (d,daysLimit, h, m, s) => {
	const result = { isValid: true, message: LANG.UI_PUBLIC_TIME_IS_VALID };
	const validateUnit = (value, unitName, max) => {
		const num = Number(value);

		if (value === '' || value === null || value === undefined) {
			return 0; // 空输入视为0
		}

		if (isNaN(num)) {
			result.isValid = false;
			result.message = `${unitName}`+ LANG.UI_PUBLIC_NUMERIC_ONLY;
			return NaN;
		}

		if (!Number.isInteger(num)) {
			result.isValid = false;
			result.message = `${unitName}` + LANG.UI_PUBLIC_INTEGERS_ONLY;
			return NaN;
		}

		if (num < 0) {
			result.isValid = false;
			result.message = `${unitName}` + LANG.UI_PUBLIC_NO_NEGATIVE_VALUES;
			return NaN;
		}

		if (max !== undefined && num >= max) {
			result.isValid = false;
			result.message = `${unitName}`+ LANG.UI_PUBLIC_VALUE_MUST_BE_LESS_THAN +`${max}`;
			return NaN;
		}

		return num;
	};

	const days = validateUnit(d, LANG.UI_BACKUP_DAY,daysLimit);
	const hours = validateUnit(h, LANG.UI_PUBLIC_HOURS, 24);
	const minutes = validateUnit(m, LANG.UI_PUBLIC_MINUTES, 60);
	const seconds = validateUnit(s, LANG.UI_PUBLIC_SECONDS, 60);

	// 如果前面已经有错误，直接返回
	if (!result.isValid) return result;

	// 检查所有值为0的情况
	if (days === 0 && hours === 0 && minutes === 0 && seconds === 0) {
		result.isValid = false;
		result.message = LANG.UI_PUBLIC_REQUIRE_NON_ZERO_TIME_UNIT;
	}

	return result;
}