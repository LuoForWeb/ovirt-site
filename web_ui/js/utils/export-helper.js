/*
 * @Author: ChengJiaFu
 * @Date: 2026-01-28 16:15:40
 * @Description: 导出工具函数
 * @version: 1.0
 */

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