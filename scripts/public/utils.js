/*
 * @Author: ChengJiaFu
 * @Date: 2025-03-11 14:37:35
 * @Description: 工具函数
 * @version: 1.0
 */

const EMAIL_VALID_EXP = /^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/; // 邮箱地址校验正则

/**
 * 给导出的PDF文件添加水印
 * @param {*} pdf
 * @param {*} waterName
 * @param {*} settings
 */
const addWatermark = (pdf, waterName, settings) => {
    let defaults = {
        watermark_font: 'ChineseFont', // 使用jsPDF支持的字体
        watermark_fontsize: 15, // 字体大小使用pt单位
        watermark_color: '#ccc', // 注意jsPDF只支持rgb颜色
        watermark_angle: 120,
        watermark_spacing: 200 // 水印之间的间距
    };

    // 创建一个新的Date对象，它会自动设置为当前日期和时间
    let now = new Date();
    let year = now.getFullYear();
    let month = now.getMonth() + 1;
    month = month < 10 ? '0' + month : month; // 如果月份小于10，前面补0
    let day = now.getDate();
    day = day < 10 ? '0' + day : day; // 如果日期小于10，前面补0
    let hour = now.getHours();
    hour = hour < 10 ? '0' + hour : hour; // 如果小时小于10，前面补0
    let minute = now.getMinutes();
    minute = minute < 10 ? '0' + minute : minute; // 如果分钟小于10，前面补0
    let second = now.getSeconds();
    second = second < 10 ? '0' + second : second; // 如果秒数小于10，前面补0
    let date = year + '-' + month + '-' + day + ' ' + hour + ':' + minute + ':' + second;

    // 水印文字
    let watermarkText = waterName + ' ' + date;

    // 合并默认设置和传入设置
    // eslint-disable-next-line
    settings = Object.assign({}, defaults, settings);
    // 假设 A4 页面尺寸（在pt单位下，因为 jsPDF 默认使用pt）
    // 注意：这里的数值是基于 72 DPI（dots per inch）的转换
    let pageWidth = 595.28; // A4 宽度，单位：pt
    let pageHeight = 841.89; // A4 高度，单位：pt

    // 遍历每一页并添加水印
    for (let i = 1; i <= pdf.internal.getNumberOfPages(); i++) {
        pdf.setPage(i);
        // 计算水印文本的行数和列数
        let watermarkCols = Math.ceil(pageWidth / (settings.watermark_fontsize * 2 + settings.watermark_spacing));
        let watermarkRows = Math.ceil(pageHeight / (settings.watermark_fontsize * 1.5 + settings.watermark_spacing));
        // 遍历行和列来添加水印
        for (let col = 0; col < watermarkCols; col++) {
            for (let row = 0; row < watermarkRows; row++) {
                // 计算水印的位置
                let x = col * (settings.watermark_fontsize * 2 + settings.watermark_spacing);
                let y = pageHeight - (row * (settings.watermark_fontsize * 1.5 + settings.watermark_spacing)); // 从底部开始放置
                // 添加水印文本,这里的字体是引入的字体文件
                pdf.setFont('FeiHuaSongTi-2');
                pdf.setTextColor(settings.watermark_color);
                const rotation = 45; // 整张图片旋转的角度
                pdf.text(watermarkText, x, y, rotation);
            }
        }
    }
};

/**
 * html2canvas捕获需要导出的页面并生成PDF
 * @param {*} element 需要导出页面的JQ对象
 * @param {*} windowHeight 需要导出页面的高度
 * @param {*} filename 导出文件名
 * @param {*} waterName 水印名
 * @param {*} callback 导出成功后的回调
 */
const captureScrollAndGeneratePDF = (element, windowHeight, filename, waterName, callback) => {
    // 设置 html2canvas 的配置
    const options = {
        windowHeight: windowHeight,
		scale: 2,
        backgroundColor: 'white',
    };

    // 使用 html2canvas 捕获整个页面
    html2canvas(element, options).then(canvas => {
        const imgData = canvas.toDataURL('image/svg');
        const { jsPDF } = window.jspdf;

        // 创建 jsPDF 实例
        const pdf = new jsPDF({
            orientation: 'p', // 肖像模式
            unit: 'pt',       // 单位为点
            format: 'a4'      // A4 页面尺寸
        });

        // 计算需要多少页
        const imgWidth = pdf.internal.pageSize.getWidth();
        const imgHeight = pdf.internal.pageSize.getHeight();
        const ratio = imgWidth / canvas.width;
        const imgHeightScaled = canvas.height * ratio;

        // 分页添加图像
        let heightLeft = imgHeightScaled;
        let position = 0;
        while (heightLeft >= 0) {
            pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeightScaled);
            heightLeft -= imgHeight;
            position = heightLeft > 0 ? -imgHeight : 0;

            if (heightLeft >= 0) {
                pdf.addPage();
            }
        }

        // 添加水印（如果需要）
        addWatermark(pdf, waterName);

        // 保存 PDF
        pdf.save(filename);
        callback();
    });
};

/**
 * bootbox的回调防抖
 * @param {*} func 要执行的函数
 * @param {*} wait 延迟的毫秒数
 * @param {*} hideDialogFlag 是否自动隐藏对话框（默认为true，直接隐藏）
 * @returns
 */
const debounce = (func, wait, hideDialogFlag = true) => {
    // 闭包内部变量，用来保存计时器的引用
    let timeout = null;

    // 返回一个新的函数，这个新函数会在一个延迟时间后执行原始函数func
    return function() {
        // 保存当前上下文和参数，以便稍后使用
        // eslint-disable-next-line consistent-this
        const context = this;
        const args = arguments;
        let $dialog = $(this);

        // 取消按钮不需要抖动
        if ($dialog.hasClass('bootbox-confirm') && args.length && args[0] === false) {  // confirm的取消按钮的参数是false
            $dialog.modal('hide');
        } else if ($dialog.hasClass('bootbox-prompt') && args.length && args[0] === null) {  // prompt的取消按钮的参数是null
            $dialog.modal('hide');
        }

        // 清除现有计时器，如果存在的话
        clearTimeout(timeout);

        // 设置新的计时器，延迟执行func
        timeout = setTimeout(function() {
            // 执行被防抖的函数，并传入之前保存的上下文和参数
            func.apply(context, args);
            if (hideDialogFlag) {
                $dialog.modal('hide');
            }
        }, wait);
        return false;
    };
};

/**
 * 防抖函数
 * @param {*} func 要执行的函数
 * @param {*} wait 延迟的毫秒数
 */
const antiShake = (func, wait) => {
    // 闭包内部变量，用来保存计时器的引用
    let timeout = null;

    // 返回一个新的函数，这个新函数会在一个延迟时间后执行原始函数func
    return function() {
        // 保存当前上下文和参数，以便稍后使用
        const context = this;
        const args = arguments;

        // 清除现有计时器，如果存在的话
        clearTimeout(timeout);

        // 设置新的计时器，延迟执行func
        timeout = setTimeout(function() {
            // 执行被防抖的函数，并传入之前保存的上下文和参数
            func.apply(context, args);
        }, wait);
    };
}

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
 * 获取表格过滤器参数
 * @param {*} key 过滤器选项组中指定的field，如 'module_type' 'task_type' 'task_status'
 * @param {*} data 过滤器勾选的数组
 * @returns
 */
// eslint-disable-next-line no-unused-vars
const getTableFilterParams = (key, data) => {
    const item = data.find(i => i.key === key);
    return item ? item : null;
};

// 用于 业务类型 - 模块类型 - 任务类型的级联树
const MODULE_TASK_TYPE_CASCADER_TREE_DATA = [
    {
        id: 'timing_backup',
        value: "timing_backup",
        label: LANG.UI_VISUAL_BACKUP,
        children: [
            {
                id: 'timing_backup',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.DATA_BACKUP_ALL,
                label: LANG.UI_PUBLIC_ALL
            },
            {
                id: 'vmprotect',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.VM,
                label: LANG.UI_BACKUP_DATA_MODULE_VM,
                children: [
                    {
                        id: 'vmprotect',
                        value: "2-1-0",
                        label: LANG.UI_PUBLIC_ALL
                    },
                    {
                        id: 'vmprotect',
                        value: "2-1-1",
                        label: LANG.UI_VISUAL_BACKUP,
                    },
                    {
                        id: 'vmrecover',
                        value: "2-1-2",
                        label: LANG.UI_VISUAL_RECOVERY,
                    },
                    {
                        id: 'copy_protect',
                        value: "2-1-17",
                        label: LANG.UI_VISUAL_COPY,
                    },
                    {
                        id: 'archive_new',
                        value: "2-1-19",
                        label: LANG.UI_VISUAL_ARCHIVE,
                    },
                    {
                        id: 'vm_instant_recovery',
                        value: "2-1-54",
                        label: LANG.UI_VISUAL_MIGRATION,
                    },
                    {
                        id: 'vm_instant_recovery',
                        value: "2-1-53",
                        label: LANG.UI_VISUAL_INSTANT_NAME,
                    },
                    {
                        id: 'vm_grain_recovery',
                        value: "2-1-55",
                        label: LANG.UI_VISUAL_RECOVERY_GRAIN,
                    },
                    {
                        id: 'data_verification',
                        value: "2-1-37",
                        label: LANG.UI_VISUAL_DATA_VERTIFY,
                    },
                    {
                        id: 'vm_platform_recovery',
                        value: "2-1-52",
                        label: LANG.UI_FILE_CROSS_RESTORE,
                    }
                ]
            },
            {
                id: 'prcloud_protect',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.PRIVATE_CLOUD,
                label: LANG.UI_PUBLIC_PRIVATE_CLOUD,
                children: [
                    {
                        id: 'prcloud_protect',
                        value: "2-2-0",
                        label: LANG.UI_PUBLIC_ALL
                    },
                    {
                        id: 'prcloud_protect',
                        value: "2-2-1",
                        label: LANG.UI_VISUAL_BACKUP,
                    },
                    {
                        id: 'vm_prcloud_recovery',
                        value: "2-2-2",
                        label: LANG.UI_VISUAL_RECOVERY,
                    },
                    {
                        id: 'copy_protect',
                        value: "2-2-17",
                        label: LANG.UI_VISUAL_COPY,
                    },
                    {
                        id: 'archive_new',
                        value: "2-2-19",
                        label: LANG.UI_VISUAL_ARCHIVE,
                    },
                    {
                        id: 'vm_prcloud_instant_recovery',
                        value: "2-2-54",
                        label: LANG.UI_VISUAL_MIGRATION,
                    },
                    {
                        id: 'vm_prcloud_instant_recovery',
                        value: "2-2-53",
                        label: LANG.UI_VISUAL_INSTANT_NAME,
                    },
                    {
                        id: 'vm_prcloud_graininess_recovery',
                        value: "2-2-55",
                        label: LANG.UI_VISUAL_RECOVERY_GRAIN,
                    },
                    {
                        id: 'data_verification',
                        value: "2-2-37",
                        label: LANG.UI_VISUAL_DATA_VERTIFY,
                    },
                    {
                        id: 'vm_prcloud_platform_recovery',
                        value: "2-2-52",
                        label: LANG.UI_FILE_CROSS_RESTORE,
                    }
                ]
            },
            {
                id: 'awsprotect',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.PUBLIC_CLOUD,
                label: LANG.UI_PUBLIC_PUBLIC_CLOUD,
                children: [
                    {
                        id: 'awsprotect',
                        value: "2-3-0",
                        label: LANG.UI_PUBLIC_ALL
                    },
                    {
                        id: 'awsprotect',
                        value: "2-3-1",
                        label: LANG.UI_VISUAL_BACKUP,
                    },
                    {
                        id: 'vm_awsprotect_recover',
                        value: "2-3-2",
                        label: LANG.UI_VISUAL_RECOVERY,
                    },
                    {
                        id: 'copy_protect',
                        value: "2-3-17",
                        label: LANG.UI_VISUAL_COPY,
                    },
                    {
                        id: 'archive_new',
                        value: "2-3-19",
                        label: LANG.UI_VISUAL_ARCHIVE,
                    },
                    {
                        id: 'vm_awsprotect_instant_recovery',
                        value: "2-3-54",
                        label: LANG.UI_VISUAL_MIGRATION,
                    },
                    {
                        id: 'vm_awsprotect_instant_recovery',
                        value: "2-3-53",
                        label: LANG.UI_VISUAL_INSTANT_NAME,
                    },
                    {
                        id: 'vm_awsprotect_grain_recovery',
                        value: "2-3-55",
                        label: LANG.UI_VISUAL_RECOVERY_GRAIN,
                    },
                    {
                        id: 'data_verification',
                        value: "2-3-37",
                        label: LANG.UI_VISUAL_DATA_VERTIFY,
                    },
                    {
                        id: 'vm_awsprotect_platform_recovery',
                        value: "2-3-52",
                        label: LANG.UI_FILE_CROSS_RESTORE,
                    }
                ]
            },
            {
                id: 'complete_machine',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.COMPLETE_MACHINE_DISK,
                label: LANG.UI_BACKUP_DATA_MODULE_OS,
                children: [
                    {
                        id: 'complete_machine',
                        value: "5-1-0",
                        label: LANG.UI_PUBLIC_ALL
                    },
                    {
                        id: 'complete_machine',
                        value: "5-1-35",
                        label: LANG.UI_VISUAL_BACKUP,
                    },
                    {
                        id: 'machine_complete_recovery',
                        value: "5-1-36",
                        label: LANG.UI_VISUAL_RECOVERY,
                    },
                    {
                        id: 'copy_protect',
                        value: "5-1-17",
                        label: LANG.UI_VISUAL_COPY,
                    },
                    {
                        id: 'archive_new',
                        value: "5-1-19",
                        label: LANG.UI_VISUAL_ARCHIVE,
                    },
                    {
                        id: 'machine_complete_instant_recovery',
                        value: "5-1-54",
                        label: LANG.UI_VISUAL_MIGRATION,
                    },
                    {
                        id: 'machine_complete_instant_recovery',
                        value: "5-1-49",
                        label: LANG.UI_VISUAL_INSTANT_NAME,
                    },
                    {
                        id: 'machine_complete_grain_recovery',
                        value: "5-1-55",
                        label: LANG.UI_VISUAL_RECOVERY_GRAIN,
                    },
                    {
                        id: 'data_verification',
                        value: "5-1-37",
                        label: LANG.UI_VISUAL_DATA_VERTIFY,
                    },
                    {
                        id: 'machine_complete_platform_recovery',
                        value: "5-1-52",
                        label: LANG.UI_FILE_CROSS_RESTORE,
                    }
                ]
            },
            {
                id: 'osbackup',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.COMPLETE_MACHINE_VOLUME,
                label: LANG.UI_VOL_CDP_RECOVER_VOL,
                children: [
                    {
                        id: 'osbackup',
                        value: "5-0-0",
                        label: LANG.UI_PUBLIC_ALL
                    },
                    {
                        id: 'osbackup',
                        value: "5-0-35",
                        label: LANG.UI_VISUAL_BACKUP,
                    },
                    {
                        id: 'os_recovery',
                        value: "5-0-36",
                        label: LANG.UI_VISUAL_RECOVERY,
                    },
                    {
                        id: 'copy_protect',
                        value: "5-0-17",
                        label: LANG.UI_VISUAL_COPY,
                    },
                    {
                        id: 'archive_new',
                        value: "5-0-19",
                        label: LANG.UI_VISUAL_ARCHIVE,
                    },
                    // {
                    //     value: "5-0-54",
                    //     label: LANG.UI_VISUAL_MIGRATION,
                    // },
                    // {
                    //     value: "5-0-53",
                    //     label: LANG.UI_VISUAL_INSTANT_NAME,
                    // }
                ]
            },
            {
                id: 'filebackup',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.FILE,
                label: LANG.UI_FILE_FILE,
                children: [
                    {
                        id: 'filebackup',
                        value: "3-1-0",
                        label: LANG.UI_PUBLIC_ALL
                    },
                    {
                        id: 'filebackup',
                        value: "3-1-1",
                        label: LANG.UI_VISUAL_BACKUP,
                    },
                    {
                        id: 'file_recovery',
                        value: "3-1-2",
                        label: LANG.UI_VISUAL_RECOVERY,
                    },
                    {
                        id: 'copy_protect',
                        value: "3-1-17",
                        label: LANG.UI_VISUAL_COPY,
                    },
                    {
                        id: 'data_verification',
                        value: "3-1-37",
                        label: LANG.UI_VISUAL_DATA_VERTIFY,
                    },
                ]
            },
            {
                id: 'nas_protect',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.NAS,
                label: "NAS",
                children: [
                    {
                        id: 'nas_protect',
                        value: "11-2-0",
                        label: LANG.UI_PUBLIC_ALL
                    },
                    {
                        id: 'nas_protect',
                        value: "11-2-1",
                        label: LANG.UI_VISUAL_BACKUP,
                    },
                    {
                        id: 'nas_recovery',
                        value: "11-2-2",
                        label: LANG.UI_VISUAL_RECOVERY,
                    },
                    {
                        id: 'copy_protect',
                        value: "11-2-17",
                        label: LANG.UI_VISUAL_COPY,
                    },
                    {
                        id: 'data_verification',
                        value: "11-2-37",
                        label: LANG.UI_VISUAL_DATA_VERTIFY,
                    },
                ]
            },
            {
                id: 'hadoop_protect',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.HADOOP,
                label: "Hadoop",
                children: [
                    {
                        id: 'hadoop_protect',
                        value: "3-3-0",
                        label: LANG.UI_PUBLIC_ALL
                    },
                    {
                        id: 'hadoop_protect',
                        value: "3-3-1",
                        label: LANG.UI_VISUAL_BACKUP,
                    },
                    {
                        id: 'hadoop_recovery',
                        value: "3-3-2",
                        label: LANG.UI_VISUAL_RECOVERY,
                    },
                    {
                        id: 'copy_protect',
                        value: "3-3-17",
                        label: LANG.UI_VISUAL_COPY,
                    },
                    {
                        id: 'data_verification',
                        value: "3-3-37",
                        label: LANG.UI_VISUAL_DATA_VERTIFY,
                    },
                ]
            },
            {
                id: 'obs_protect',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.OBS,
                label: LANG.UI_VISUAL_OBS,
                children: [
                    {
                        id: 'obs_protect',
                        value: "3-4-0",
                        label: LANG.UI_PUBLIC_ALL
                    },
                    {
                        id: 'obs_protect',
                        value: "3-4-1",
                        label: LANG.UI_VISUAL_BACKUP,
                    },
                    {
                        id: 'obs_recovery',
                        value: "3-4-2",
                        label: LANG.UI_VISUAL_RECOVERY,
                    },
                    {
                        id: 'copy_protect',
                        value: "3-4-17",
                        label: LANG.UI_VISUAL_COPY,
                    },
                    {
                        id: 'data_verification',
                        value: "3-4-37",
                        label: LANG.UI_VISUAL_DATA_VERTIFY,
                    },
                ]
            },
            {
                id: 'db_protect',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.DB,
                label: LANG.UI_AGENT_MODULE_DB,
                children: [
                    {
                        id: 'db_protect',
                        value: "4-0-0",
                        label: LANG.UI_PUBLIC_ALL
                    },
                    {
                        id: 'db_protect',
                        value: "4-0-28",
                        label: LANG.UI_VISUAL_BACKUP,
                    },
                    {
                        id: 'db_recovery',
                        value: "4-0-29",
                        label: LANG.UI_VISUAL_RECOVERY,
                    },
                    {
                        id: 'copy_protect',
                        value: "4-0-17",
                        label: LANG.UI_VISUAL_COPY,
                    },
                    {
                        id: 'db_drill',
                        value: "4-0-64",
                        label: LANG.UI_RECOVERY_DB_PROTECT_CREATE_DRILL
                    },
                    {
                        id: 'data_verification',
                        value: "4-0-37",
                        label: LANG.UI_VISUAL_DATA_VERTIFY,
                    },
                ]
            },
            {
                id: 'office365_protect',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.M365,
                label: LANG.UI_BACKUP_DATA_MODULE_M365,
                children: [
                    {
                        id: 'office365_protect',
                        value: "14-0-0",
                        label: LANG.UI_PUBLIC_ALL
                    },
                    {
                        id: 'office365_protect',
                        value: "14-0-1",
                        label: LANG.UI_VISUAL_BACKUP,
                    },
                    {
                        id: 'exchange_recovery',
                        value: "14-0-2",
                        label: LANG.UI_VISUAL_RECOVERY,
                    },
                    {
                        id: 'copy_protect',
                        value: "14-0-17",
                        label: LANG.UI_VISUAL_COPY,
                    },
                    {
                        id: 'data_verification',
                        value: "14-0-37",
                        label: LANG.UI_VISUAL_DATA_VERTIFY,
                    },
                ]
            },
            {
                id: 'k8s_protect',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.KUBERNETES,
                label: LANG.UI_BACKUP_DATA_MODULE_K8S,
                children: [
                    {
                        id: 'k8s_protect',
                        value: "28-0-0",
                        label: LANG.UI_PUBLIC_ALL
                    },
                    {
                        id: 'k8s_protect',
                        value: "28-0-56",
                        label: LANG.UI_VISUAL_BACKUP,
                    },
                    {
                        id: 'k8s_recovery',
                        value: "28-0-57",
                        label: LANG.UI_VISUAL_RECOVERY,
                    },
                    {
                        id: 'copy_protect',
                        value: "28-0-17",
                        label: LANG.UI_VISUAL_COPY,
                    },
                    // {
                    //     id: 'data_verification',
                    //     value: "28-37",
                    //     label: LANG.UI_VISUAL_DATA_VERTIFY,
                    // },
                ]
            }
        ]
    },
    {
        id: 'real_time_protect',
        value: "real_time_protect",
        label: LANG.UI_REPORY_CDP,
        children: [
            {
                id: 'real_time_protect',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.REAL_TIME_DATA_PROTECT_ALL,
                label: LANG.UI_PUBLIC_ALL
            },
            {
                id: 'complete_cdp_backup',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.REAL_TIME_COMPLETE_MACHINE_DISK,
                label: LANG.UI_BACKUP_DATA_MODULE_OS,
                children: [
                    {
                        id: 'complete_cdp_backup',
                        value: "10-1-2-0",
                        label: LANG.UI_PUBLIC_ALL
                    },
                    {
                        id: 'complete_cdp_backup',
                        value: "10-1-2-32",
                        label: LANG.UI_VISUAL_BACKUP,
                    },
                    {
                        id: 'machine_complete_volcdp_recovery',
                        value: "10-1-2-33",
                        label: LANG.UI_VISUAL_RECOVERY,
                    },
                    {
                        id: 'vol_cdp_complete_takeover',
                        value: "10-1-2-34",
                        label: LANG.UI_VISUAL_TAKEOVER,
                    },
                    {
                        id: 'machine_complete_volcdp_grain_recovery',
                        value: "10-1-2-55",
                        label: LANG.UI_VISUAL_RECOVERY_GRAIN,
                    },
                    {
                        id: 'data_verification',
                        value: "10-1-2-37",
                        label: LANG.UI_VISUAL_DATA_VERTIFY,
                    },
                    {
                        id: 'machine_complete_volcdp_platform_recovery',
                        value: "10-1-2-52",
                        label: LANG.UI_FILE_CROSS_RESTORE,
                    }
                ]
            },
            {
                id: 'vol_cdp_backup',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.REAL_TIME_COMPLETE_MACHINE_VOLUME,
                label: LANG.UI_VOL_CDP_RECOVER_VOL,
                children: [
                    {
                        id: 'vol_cdp_backup',
                        value: "10-1-1-0",
                        label: LANG.UI_PUBLIC_ALL
                    },
                    {
                        id: 'vol_cdp_backup',
                        value: "10-1-1-32",
                        label: LANG.UI_VISUAL_BACKUP,
                    },
                    {
                        id: 'p_vol_cdp_recovery',
                        value: "10-1-1-33",
                        label: LANG.UI_VISUAL_RECOVERY,
                    },
                    {
                        id: 'vol_cdp_takeover',
                        value: "10-1-1-34",
                        label: LANG.UI_PUBLIC_TAKEOVER,
                    }
                ]
            }
        ]
    },
    {
        id: 'data_copy',
        value: "data_copy",
        label: LANG.UI_CM_CDP_REPLICATION,
        children: [
            {
                id: 'data_copy',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.DATA_COPY_ALL,
                label: LANG.UI_PUBLIC_ALL
            },
            {
                id: 'machine_copy',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.DATA_COPY_COMPLETE_MACHINE_DISK,
                label: LANG.UI_BACKUP_DATA_MODULE_OS,
                children: [
                    {
                        id: 'machine_copy',
                        value: "10-3-4-0",
                        label: LANG.UI_PUBLIC_ALL
                    },
                    {
                        id: 'machine_copy',
                        value: "10-3-4-65",
                        label: LANG.UI_CM_CDP_REPLICATION,
                    },
                    {
                        id: 'machine_complete_volcdp_recovery',
                        value: "10-3-4-33",
                        label: LANG.UI_VISUAL_RECOVERY,
                    },
                    {
                        id: 'machine_copy',
                        value: "10-3-4-34",
                        label: LANG.UI_PUBLIC_TAKEOVER,
                    },
                    {
                        id: 'machine_complete_volcdp_grain_recovery',
                        value: "10-3-4-55",
                        label: LANG.UI_VISUAL_RECOVERY_GRAIN,
                    },
                    {
                        id: 'data_verification',
                        value: "10-3-4-37",
                        label: LANG.UI_VISUAL_DATA_VERTIFY,
                    },
                    {
                        id: 'machine_complete_volcdp_platform_recovery',
                        value: "10-3-4-52",
                        label: LANG.UI_FILE_CROSS_RESTORE,
                    }
                ]
            },
            {
                id: 'vol_cdp_copy',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.DATA_COPY_COMPLETE_MACHINE_VOLUME,
                label: LANG.UI_VOL_CDP_RECOVER_VOL,
                children: [
                    {
                        id: 'vol_cdp_copy',
                        value: "10-3-3-0",
                        label: LANG.UI_PUBLIC_ALL
                    },
                    {
                        id: 'vol_cdp_copy',
                        value: "10-3-3-65",
                        label: LANG.UI_CM_CDP_REPLICATION,
                    },
                    {
                        id: 'p_vol_cdp_recovery',
                        value: "10-3-3-33",
                        label: LANG.UI_VISUAL_RECOVERY,
                    },
                    {
                        id: 'vol_cdp_copy',
                        value: "10-3-3-34",
                        label: LANG.UI_PUBLIC_TAKEOVER,
                    }
                ]
            },
            {
                id: 'dbcdpcopy',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.DATA_COPY_DB,
                label: LANG.UI_AGENT_MODULE_DB,
                children: [
                    {
                        id: 'dbcdpcopy',
                        value: "12-0-0",
                        label: LANG.UI_PUBLIC_ALL
                    },
                    {
                        id: 'dbcdpcopy',
                        value: "12-0-46",
                        label: LANG.UI_CM_CDP_REPLICATION,
                    },
                    {
                        id: 'dbcdp_recovery',
                        value: "12-0-47",
                        label: LANG.UI_VISUAL_RECOVERY,
                    },
                ]
            },
            {
                id: 'file_copy_protect',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.DATA_COPY_FILE,
                label: LANG.UI_FILE_FILE,
                children: [
                    {
                        id: 'file_copy_protect',
                        value: "26-0-0",
                        label: LANG.UI_PUBLIC_ALL
                    },
                    {
                        id: 'file_copy_protect',
                        value: "26-0-62",
                        label: LANG.UI_CM_CDP_REPLICATION,
                    },
                    {
                        id: 'file_copy_protect',
                        value: "26-0-63",
                        label: LANG.UI_FILE_COPY_TASK_TYPE_COMPARE,
                    },
                ]
            }
        ]
    },
];

/**
 * 根据权限过滤得到有权限的模块树形数组，并移除没有权限或子项全被过滤掉的节点
 * @param {*} tree 模块树形数组
 * @param {*} permissions 权限数组
 * @returns 
 */
const filterMenuTree = (tree, permissions) => {
    // 递归过滤函数
    const filterNode = (node) => {
        if (node.children && node.children.length > 0) {
            const hasPermission = permissions.includes(node.id);
            if (!hasPermission) return null;

            const filteredChildren = node.children
                .map(filterNode)
                .filter(child => child !== null);

            // 返回新节点时带上标记表示它原本有 children
            return { ...node, children: filteredChildren, hasOriginalChildren: true };
        } else {
            return permissions.includes(node.id) ? { ...node } : null;
        }
    };

    // 第一阶段：递归过滤整棵树
    const partiallyFilteredTree = tree.map(filterNode).filter(node => node !== null);

    // 第二阶段：再次过滤顶层节点，去掉那些原本有 children 但被过滤成空数组的情况
    return partiallyFilteredTree.filter(node => {
        // 如果原本有 children 但过滤后为空数组，则移除
        if (node.hasOriginalChildren && node.children && node.children.length === 0) {
            return false;
        }
        return true;
    });
};

/**
 * 根据权限过滤得到有权限且未过期的菜单树形数组，并移除没有权限或子项全被过滤掉的节点
 * @param {Array} tree - 原始树形数组
 * @param {Array} permissions - 权限ID数组
 * @returns {Array} - 过滤后的树形数组
 */
const filterTaskMenuTree = (tree, permissions) => {
    // 当授权过期时，需要被过滤掉的任务类型：备份、高级恢复（跨平台恢复、细粒度恢复、瞬时恢复）、副本、归档、验证、接管、复制
    const restrictedTypesWhenExpired = ['backup', 'advancedRecover', 'copy', 'archive', 'verify', 'takeover', 'replication'];

    // 深度克隆原树，避免修改原始数据
    const treeCopy = JSON.parse(JSON.stringify(tree));

    // 第一阶段：递归过滤函数
    const filterNode = (node) => {
        if (!permissions.includes(node.id)) {
            return null;
        }

        // 授权过期过滤：如果授权已过期，则过滤掉指定类型的任务节点
        if (CONF.AUTH_NOT_EXPIRED === false && node.type && restrictedTypesWhenExpired.includes(node.type)) {
            return null;
        }

        // 3递归子节点：如果节点有子节点，则递归过滤
        if (node.children && node.children.length > 0) {
            const filteredChildren = node.children
                .map(filterNode)
                .filter(child => child !== null);

            // 返回带有过滤后子节点的新节点，并标记它原本有子节点
            return { ...node, children: filteredChildren, hasOriginalChildren: true };
        }

        // 保留叶子节点：如果节点通过所有过滤且没有子节点，则保留
        return { ...node };
    };

    // 第二阶段：递归地移除因所有子节点被过滤而变空的父节点
    const removeEmptyParents = (nodes) => {
        return nodes.filter(node => {
            // 如果节点有子节点，先递归地对子节点进行剪枝
            if (node.children && node.children.length > 0) {
                node.children = removeEmptyParents(node.children);
            }

            // 如果节点原本有子节点，但经过层层过滤后，子节点数组变空，则移除该父节点
            if (node.hasOriginalChildren && node.children && node.children.length === 0) {
                return false;
            }
            return true;
        });
    };

    const partiallyFilteredTree = treeCopy.map(filterNode).filter(node => node !== null);

    return removeEmptyParents(partiallyFilteredTree);
};

// 任务类型常量，键代表包含多个任务类型的module_type，比如：4代表数据库，其value 数组包含多个任务类型，即：数据库备份 数据库恢复 数据库实时恢复 数据库副本
const JOB_TYPE_CONSTANTS = {
    "2": ['52', '53', '54', '55'],
    "4": ['28', '29', '47', '30'],
    "5": ['32', '35', '33', '36', '38', '40', '49']
};

/**
 * 根据勾选的模块过滤最终的任务类型
 * @param {*} jobTypeArray 勾选的任务类型数组
 * @param {*} moduleTypeArr 模块类型集合
 * @param {*} jobTypeConstants 任务类型常量
 * @returns 
 */
const filterJobTypes = (jobTypeArray, moduleTypeArr, jobTypeConstants) => {
    return jobTypeArray.filter(jobType => {
        for (const [key, values] of Object.entries(jobTypeConstants)) {
            if (!moduleTypeArr.has(key) && values.includes(jobType)) {
                return false;
            }
        }
        return true;
    });
}

/**
 * 处理当前任务/历史任务中 业务类型、对象类型、任务类型和任务状态的过滤器组件传参
 * @param {*} filterData 
 */
const handleUpdateFilterParams = (filterData) => {

    let checkedFilterParams = { module_type: [], sub_module_type: [], job_type: [], job_status: [], storage_location: [], dev_type: [] };
    let moduleTypeArr = [], subModuleTypeArr = [], storageLocationArr = [], devTypeArr = [];

    if (filterData.length > 0) {
        const timingProtectData = filterData.find(i => i.key === "timing_data_protect")?.value || []; // 定时保护勾选数据
        const realTimeProtectData = filterData.find(i => i.key === "real_time_data_protect")?.value || []; // 实时保护勾选数据
        const dataCopyData = filterData.find(i => i.key === "data_copy")?.value || []; // 数据复制勾选数据
        const taskTypeData = filterData.find(i => i.key === "task_type")?.value || []; // 任务类型勾选数据
        const taskStatusData = filterData.find(i => i.key === "task_status")?.value || []; // 任务状态勾选数据
        
        // 勾选了定时保护或实时保护或数据复制时，处理 module_type 和 sub_module_type 
        if (!timingProtectData.length && !realTimeProtectData.length && !dataCopyData.length) {
            checkedFilterParams = { module_type: [], sub_module_type: [], job_type: [], job_status: [], storage_location: [], dev_type: [] };
        } else {
            if (timingProtectData.length > 0) { // 勾选了定时保护列的数据
                timingProtectData.forEach(item => {
                    const itemData = item.split('-');

                    // 定时备份模块不含子模块的模块，sub_module_type默认给0，所以length总是2
                    moduleTypeArr.push(itemData[0]);
                    subModuleTypeArr.push(itemData[1]);
                });
            }

            if (realTimeProtectData.length > 0) { // 勾选了实时保护列的数据，split分隔后，第一个元素表示 module_type,第二个元素表示storage_location（1：备份 3：复制），第三个元素表示 dev_type（1：卷 2：磁盘）
                realTimeProtectData.forEach(item => {
                    const itemData = item.split('-');

                    moduleTypeArr.push(itemData[0]);
                    storageLocationArr.push(itemData[1]);
                    devTypeArr.push(itemData[2]);
                });
            }

            if (dataCopyData.length > 0) { // 勾选了数据复制列的数据
                dataCopyData.forEach(item => {
                    const itemData = item.split('-');

                    if (itemData.length === 2) { // 勾选的文件复制或数据库复制
                        moduleTypeArr.push(itemData[0]);
                        subModuleTypeArr.push(itemData[1]);
                    } else { // 勾选的整机或卷时：split分隔后，第一个元素表示 module_type,第二个元素表示sub_module_type（默认给0），第三个元素表示 dev_type（1：卷 2：磁盘）
                        moduleTypeArr.push(itemData[0]);
                        storageLocationArr.push(itemData[1]);
                        devTypeArr.push(itemData[2]);
                    }
                });
            }

            checkedFilterParams = { 
                module_type: moduleTypeArr, 
                sub_module_type: subModuleTypeArr, 
                job_type: [], 
                job_status: [], 
                storage_location: storageLocationArr, 
                dev_type: devTypeArr 
            };
        }
        
        // 勾选了任务类型数据
        if (taskTypeData.length > 0) {
            checkedFilterParams.job_type = taskTypeData.join(',').split(',');
            
            // 过滤任务类型
            if (moduleTypeArr.size > 0) {
                checkedFilterParams.job_type = filterJobTypes(
                    checkedFilterParams.job_type,
                    moduleTypeArr,
                    JOB_TYPE_CONSTANTS
                );
            }
        }

        // 勾选了任务状态数据
        if (taskStatusData.length > 0) {
            checkedFilterParams.job_status = taskStatusData;
        }
    } else {
        checkedFilterParams = {
            module_type: [],
            sub_module_type: [],
            job_type: [],
            job_status: [],
            storage_location: [],
            dev_type: []
        }
    }

    return checkedFilterParams;
}

/**
 * 获取资源限制描述
 */
const getResourceLimitDesHtml = (timeType, timeList) => {
    const WEEK_DES_MAP = {
        1: LANG.UI_PUBLIC_NUMBER_ONE,
        2: LANG.UI_PUBLIC_NUMBER_TWO,
        3: LANG.UI_PUBLIC_NUMBER_THREE,
        4: LANG.UI_PUBLIC_NUMBER_FOUR,
        5: LANG.UI_PUBLIC_NUMBER_FIVE,
        6: LANG.UI_PUBLIC_NUMBER_SIX,
        7: LANG.UI_PUBLIC_NUMBER_SEVEN,
    }

    let des = '';
    switch (timeType) {
        case 1: // 每天
            des = timeList.map(item => { 
                return `
                        ${LANG.UI_STRATEGY_DAY}${item.prohibit_start_time}${LANG.UI_STRATEGY_START}，
                        ${item.prohibit_end_time}${LANG.UI_STRATEGY_END};`}).join('<br>');
            break;
        case 2: // 每周
            des = timeList.map(item => {
                let days = item.days;
                if (typeof days === 'string') {
                    days = days.split('').map(v => parseInt(v));
                }
                return `
                        ${LANG.UI_STRATEGY_WEEK}
                        ${days.map((v, i) => v === 1 ? WEEK_DES_MAP[i + 1] : null).filter(day => day !== null).join('，')}
                        ${item.prohibit_start_time}${LANG.UI_STRATEGY_START}，
                        ${item.prohibit_end_time}${LANG.UI_STRATEGY_END}
                        ;` }).join('<br>');
            break;
        case 3: // 每月
            des = timeList.map(item => {
                let days = item.days;
                if (typeof days === 'string') {
                    days = days.split('').map(v => parseInt(v));
                }
                return `
                        ${LANG.UI_STRATEGY_MONTH}
                        ${days.map((v, i) => v === 1 ? i + 1 : null).filter(day => day !== null).join(',')}
                        ${item.prohibit_start_time}${LANG.UI_STRATEGY_START}，
                        ${item.prohibit_end_time}${LANG.UI_STRATEGY_END}
                        ;` }).join('<br>');
            break;
        case 4: // 自定义
            des = timeList.map(item => { 
                return `
                        ${item.prohibit_start_time}${LANG.UI_STRATEGY_START}，
                        ${item.prohibit_end_time}${LANG.UI_STRATEGY_END};` }).join('<br>');
            break;
        default:
            des = '--';
            break;
    }

    return des;
}

/**
 * 多个电子邮箱校验（换行符分隔）
 * @param {*} val email str
 * @returns result
 */
const illeagalEmailsCheck = (val) => {
    let result = {
        flag: false,
        message: ''
    };

    if (!val) {
        result.flag = true;
        result.message = LANG.UI_EMAIL_ADDRESS_CANNOT_EMPTY;
    } else {
        let emails = val.split('\n');

        emails = emails.filter(Boolean).map(i => {
            // 空格替换为空字符串
            return i.replace(/\s/g, '');
        }).filter(Boolean);

        for (let i = 0; i < emails.length; i++) {
            if (!EMAIL_VALID_EXP.test(emails[i])) {
                result.flag = true;
                result.message = `${LANG.UI_EMERGENCY_RECOVERY_THE}${i + 1}${LANG.UI_EMAILS_FORMAT_UNVALID_TIPS}`;
                break;
            } else {
                result.flag = false;
            }
        }
    }

    return result;
};

/**
 * 过滤掉对象中的空数组
 * @param {*} obj 
 * @returns 
 */
const filterEmptyArrayProperties = (obj) => {
    return Object.keys(obj).reduce((acc, key) => {
        if (!(Array.isArray(obj[key]) && obj[key].length === 0)) {
            acc[key] = obj[key];
        }
        return acc;
    }, {});
};

/**
 * 生成tooltip中带标题的文案内容
 * @param {*} textArr JSON文本数组，[{title: 'xxx', description: 'xxx'}]
 * @returns 
 */
const getWithTitleCopyWrite = (textArr) => {
    if (!Array.isArray(textArr) || textArr.length === 0) return;

    let html = `<div class="tooltips-with-title-wrap">`;

    for (let i = 0; i < textArr.length; i++) {
        html += `<div class="tooltips-with-title-wrap__item">
                    <span class="title">${textArr[i].title}${CONF.LANGUAGE === 'zh-cn' ? '：' : ':'}</span>
                    ${textArr[i].description}
                </div>`;

        if (i !== textArr.length - 1) { // 最后一个不加分割线
            html += `<div class="separator--dashed my-8"></div>`;
        }
    }

    return html;
}

/**
 * 生成tooltip中带序号的文案内容
 * @param {*} textArr 字符串文本数组，['XXX', 'XXXX']
 * @returns 
 */
const getWithSerialNumbersCopyWrite = (textArr) => {
    if (!Array.isArray(textArr) || textArr.length === 0) return;

    let html = `<ol class="tooltips-with-numbers-wrap">`;

    for (let i = 0; i < textArr.length; i++) {
        html += `<li class="tooltips-with-numbers-wrap__item">${textArr[i]}</li>`;
    }

    html += `</ol>`;

    return html;
}

/**
 * 获取表格过滤器组件参数
 * @param {*} filterBtnId
 * @returns 过滤器参数对象（不含空数组）
 */
const getFilterParams = (filterBtnId) => {
    let checkedResult = [];

    let dropdownMenuContents = $(`#${filterBtnId}_dropdown_menu .filter-dropdown-menu__content`).children('.checkbox-wrapper');

    for (let i = 0; i < dropdownMenuContents.length; i++) {
        let checkboxLabel = $(dropdownMenuContents[i]).find('.checkbox-wrapper__label');
        let item = { key: $(checkboxLabel).attr('name'), value: [] };
        let checkboxContent = $(dropdownMenuContents[i]).find('.checkbox-wrapper__content').find('.checkbox-wrapper__content__item');

        for (let j = 0; j < checkboxContent.length; j++) {
            let checkedBox = $(checkboxContent[j]).find('.form-check-input:checked');
            if (checkedBox.length > 0) {
                item.value.push($(checkedBox).prop('value'));
            }
        }

        checkedResult.push(item);
    }

    if (checkedResult.every(i => i.value.length === 0)) {
        checkedResult = [];
    }

    return handleUpdateFilterParams(checkedResult);
}

/**
 * 监听导出全部表格数据
 * @param {*} exportOptions 导出配置对象
 */
const exportAllTableData = (exportOptions) => {
    $(`#${exportOptions.toolbarId} .export .dropdown-menu`).off('click', '.export-all-excel').on('click', '.export-all-excel', function() {
        const exportUrl = exportOptions.url;
        const exportFileName = exportOptions.fileName;

        let exportParams = { offset: 0, limit: undefined };

        if (exportOptions.filterBtnId) { // 如果配置了过滤器，还需要获取过滤参数
            let checkCountStr = $(`#${exportOptions.filterBtnId} .check-count`).text();
            let checkedCount = parseInt(checkCountStr.split('/')[0]);

            if (checkedCount > 0) { // 带过滤参数
                let checkedFilterParams = getFilterParams(exportOptions.filterBtnId);

                exportParams = Object.assign(exportParams , filterEmptyArrayProperties(checkedFilterParams));
            }
        }

        if (exportOptions.dateRangePickerId) { // 如果配置了时间范围选择器，还需要获取时间范围参数
            let daterangepickerText = $(`#${exportOptions.dateRangePickerId} .daterangepicker-text`).text();

            if (daterangepickerText && daterangepickerText !== LANG.UI_DATERANGEPICKER_NO_TIME) {
                exportParams.start_time = daterangepickerText.split(' - ')[0];
                exportParams.end_time = daterangepickerText.split(' - ')[1];
            }
        }

        $.ajax({
            url: exportUrl,
            beforeSend: function (XMLHttpRequest) {
                //设置headers
                // XMLHttpRequest.setRequestHeader("X-Csrf-Token", _Token); //接口认证
                XMLHttpRequest.setRequestHeader("x-api-version", "1.0-rev0"); //当前接口版本
                // XMLHttpRequest.setRequestHeader("Authorization", _AuthToken); //身份认证
            },
            method: 'POST',
            data: exportParams ,
            xhrFields: {
                responseType: 'blob',
            },
            success: (response) => {
                let currentDate = new Date();
                let currentDateTime = currentDate.toLocaleString();
                let url = URL.createObjectURL(response);
                let a = document.createElement('a');
                a.href = url;
                a.download = '' + exportFileName + '(' + currentDateTime + ').xlsx';
                a.click();
                URL.revokeObjectURL(url);
            }
        });
    });
}