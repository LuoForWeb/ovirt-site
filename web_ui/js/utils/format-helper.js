/*
 * @Author: ChengJiaFu
 * @Date: 2026-01-28 17:16:14
 * @Description: 格式化工具函数
 * @version: 1.0
 */
/**
 * 数据大小单位匹配
 * @param {*} size 数据大小
 * @returns 返回数据和单位的对象
 */
const unitConver = (size) => {
    let result = { size: 0, unit: ''};
    if (size < 0.1 * 1024) {
        // 小于0.1KB，则转化成B
        result.size = size.toFixed(2);
        result.unit = 'B';
    } else if (size < 0.1 * 1024 * 1024) {
        // 小于0.1MB，则转化成KB
        result.size = (size / 1024).toFixed(2);
        result.unit = 'KB';
    } else if (size < 0.1 * 1024 * 1024 * 1024) {
        // 小于0.1GB，则转化成MB
        result.size = (size / (1024 * 1024)).toFixed(2);
        result.unit = 'MB';
    } else if (size < 0.1 * 1024 * 1024 * 1024 * 1024) {
        // 小于0.1TB，则转化成GB
        result.size = (size / (1024 * 1024 * 1024)).toFixed(2);
        result.unit = 'GB';
    } else {
        // 其他转化成TB
        result.size = (size / (1024 * 1024 * 1024 * 1024)).toFixed(2);
        result.unit = 'TB';
    }

    // 转成字符串
    let sizeStr = String(result.size),
        // 获取小数点处的索引
        index = sizeStr.indexOf('.'),
        // 获取小数点后两位的值
        dou = sizeStr.substring(index + 1, index + 3);

    // 判断后两位是否为00，如果是则删除00
    if (dou === '00') {
        result.size = sizeStr.substring(0, index);
    }

    return result;
};

/**
  * 存储单位换算,入参单位为字节
  */
const storageCalculateSize = function (size) {
    let data = "";
    if (size < 0.1 * 1024) { //如果小于0.1KB转化成B
        data = size.toFixed(2) + " B";
    } else if (size < 0.1 * 1024 * 1024) { //如果小于0.1MB转化成KB
        data = (size / 1024).toFixed(2) + " KB";
    } else if (size < 1 * 1024 * 1024 * 1024) { //如果小于0.1GB转化成MB
        data = (size / (1024 * 1024)).toFixed(2) + " MB";
    } else if (size < 1 * 1024 * 1024 * 1024 * 1024) { //如果小于0.1TB转化成GB
        data = (size / (1024 * 1024 * 1024)).toFixed(2) + " GB";
    } else if (size < 1 * 1024 * 1024 * 1024 * 1024 * 1024) { //如果小于0.1PB转化成TB
        data = (size / (1024 * 1024 * 1024 * 1024)).toFixed(2) + " TB";
    } else if (size < 1 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024) { //如果小于0.1EB转化成PB
        data = (size / (1024 * 1024 * 1024 * 1024 * 1024)).toFixed(2) + " PB";
    } else if (size < 1 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024) { //如果小于0.1ZB转化成EB
        data = (size / (1024 * 1024 * 1024 * 1024 * 1024 * 1024)).toFixed(2) + " EB";
    } else if (size < 1 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024) { //如果小于0.1YB转化成ZB
        data = (size / (1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024)).toFixed(2) + " ZB";
    }
    let sizestr = data + "";
    let len = sizestr.indexOf("\.");
    let dec = sizestr.substr(len + 1, 4);
    if (dec == "00") { //当小数点后为00时 去掉小数部分
        return sizestr.substring(0, len) + sizestr.substr(len + 3, 2);
    }
    return sizestr;
}

/**
 * 秒数转换成天时分秒
 */
const secondsToTime = (totalSeconds) => {
	const days = Math.floor(totalSeconds / 86400);
	const hours = Math.floor((totalSeconds % 86400) / 3600);
	const minutes = Math.floor((totalSeconds % 3600) / 60);
	const seconds = totalSeconds % 60;
	return { days, hours, minutes, seconds };
}

/**
 * 格式化日期为yyyy-MM-dd格式
 * @param {*} date 要格式化的日期对象
 * @returns 格式化后的日期字符串，格式为yyyy-MM-dd
 */
const formatDate = (date) => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    
    return `${year}-${month}-${day}`;
};

/**
 * 处理和转换数据大小工具对象
 */
const sizeConverter = {
    units: ['B', 'KB', 'MB', 'GB', 'TB', 'PB'],
    divisors: [1, 1024, 1024 * 1024, 1024 * 1024 * 1024, 1024 * 1024 * 1024 * 1024, 1024 * 1024 * 1024 * 1024 * 1024], // 转化为对应units中的单位需要除的除数

    /**
     * 将一个给定的字节数转换为最合适的单位
     * @param {*} bytes 要转换的字节数
     * @returns 返回一个对象，包含转换后的值和单位
     */
    convert: function(bytes) {
        bytes = Number(bytes);

        if (isNaN(bytes) || bytes === 0) {
            return { value: 0, unit: 'B', divisor: 1 };
        }

        let i = 0;
        while (bytes >= 1024 && i < this.units.length - 1) {
            bytes /= 1024;
            i++;
        }

        return { value: bytes.toFixed(2), unit: this.units[i], divisor: this.divisors[i] };
    },

    /**
     * 根据一个数据集中的最大值，来确定整个数据集最适合使用的统一单位
     * @param {*} maxBytes 数据集中的最大值
     * @returns 返回一个对象，包含最适合的单位和对应的除数
     */
    getBestUnit: function(maxBytes) {
        maxBytes = Number(maxBytes);

        if (isNaN(maxBytes) || maxBytes === 0) {
            return { unit: 'GB', divisor: this.divisors[3] }; // 默认返回GB
        }

        let i = 0;
        // 找到合适的单位，使得最大值在1到1024之间
        while (maxBytes >= 1024 && i < this.units.length - 1) {
            maxBytes /= 1024;
            i++;
        }

        return { unit: this.units[i], divisor: this.divisors[i] };
    }
};