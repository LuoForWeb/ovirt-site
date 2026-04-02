
    function captureScrollAndGeneratePDF(element, filename,waterName,callback) {
        // 获取元素的总高度和视口高度
        const totalHeight = element.get(0).scrollHeight; //元素总高度
        const pageHeight = window.innerHeight || document.documentElement.clientHeight;//视口高度
        const pageWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
        var orentation = ''
        if(totalHeight>pageWidth*0.7){
            orentation = 'portrait'; //竖屏展示
        } else {
            orentation = 'landscape'; //横屏展示
        }
        // 初始化 jsPDF 和设置相关参数
        const pdf = new jsPDF({
            orientation: orentation
        });
        // 捕获页面并添加到 PDF 的函数
        const capturePage = (yPos = 0) => {
            // 滚动到特定位置
            element.scrollTop(yPos);

            // 使用 html2canvas 捕获当前视口的内容
            html2canvas(element).then(canvas => {
                const imgData = canvas.toDataURL('image/png');
                const imgProps = pdf.getImageProperties(imgData);

                // 计算 PDF 页面上图片的高度，保持图片的宽高比
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfImgHeight = (imgProps.height * pdfWidth) / imgProps.width;

                // 添加图片到 PDF
                pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfImgHeight);
                addWatermark(pdf,waterName); // 调用添加水印函数
                // 如果还有剩余内容未捕获，则继续捕获下一页
                if (yPos + pageHeight < totalHeight) {
                    capturePage(yPos + pageHeight);
                } else {
                    // 所有页面都已捕获完成，保存 PDF
                    pdf.save(filename);
                    // 调用回调函数
                    if (callback && typeof callback === 'function') {
                        callback();
                    }
                }
            });
        };
        // 开始捕获第一页
        capturePage();
    }
    // function captureScrollAndGeneratePDF(element, filename, waterName, callback) {
    //     // eslint-disable-next-line
    //     html2canvas(element, {
    //         windowHeight: element.scrollHeight, // 导出的高度
    //         backgroundColor: 'white',
    //         width: element.scrollWidth
    //     }).then(canvas => {
    //         const imgData = canvas.toDataURL('image/png');
    //         const { jsPDF } = window.jspdf;
    //         // eslint-disable-next-line
    //         const pdf = new jsPDF({
    //             orientation: 'p', // portrait(肖像)/landscape(景观)
    //             unit: 'pt', // pt、mm、cm、in
    //             format: 'a4' //
    //         });
    //         const cavasHeight = (1400 * 500) / canvas.width;
    //         // 添加图片到 PDF，注意这里的 x 和 y 是起始位置
    //         pdf.addImage(imgData, 'PNG', 50, 50, 495, cavasHeight);// 图片离A4纸的距离以及图片的宽度和高度
    //         addWatermark(pdf, waterName); // 调用添加水印函数
    //         pdf.save(filename);
    //         callback();
    //     });
    // }
    function addWatermark(pdf , waterName,settings) {
        var defaults = {
            watermark_font: 'ChineseFont', // 使用jsPDF支持的字体
            watermark_fontsize: 10, // 字体大小使用pt单位
            watermark_color: '#F0F2F4', // 注意jsPDF只支持rgb颜色
            watermark_angle: 120,
            watermark_spacing: 80 // 水印之间的间距
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
        var date = year + '-' +month + '-'+ day + ' '+ hour + ':' + minute + ':' + second
        //水印文字
        var watermarkText = waterName + ' ' + date
        // 合并默认设置和传入设置
        settings = Object.assign({}, defaults, settings);
        // 假设 A4 页面尺寸（在pt单位下，因为 jsPDF 默认使用pt）
        // 注意：这里的数值是基于 72 DPI（dots per inch）的转换
        var pageWidth = 595.28; // A4 宽度，单位：pt
        var pageHeight = 841.89; // A4 高度，单位：pt
        // 遍历每一页并添加水印
        for (let i = 1; i <= pdf.internal.getNumberOfPages(); i++) {
            pdf.setPage(i);
            // 计算水印文本的行数和列数
            var watermarkCols = Math.ceil(pageWidth / (settings.watermark_fontsize * 5 + settings.watermark_spacing));
            var watermarkRows = Math.ceil(pageHeight / (settings.watermark_fontsize * 1.5 + settings.watermark_spacing));
            // 遍历行和列来添加水印
            for (var col = 0; col < watermarkCols; col++) {
                for (var row = 0; row < watermarkRows; row++) {
                    // 计算水印的位置
                    var x = col * (settings.watermark_fontsize * 5 + settings.watermark_spacing);
                    var y = pageHeight - (row * (settings.watermark_fontsize * 1.5 + settings.watermark_spacing)); // 从底部开始放置
                    // 添加水印文本,这里的字体是引入的字体文件
                    pdf.setFont('FeiHuaSongTi-2');
                    pdf.setTextColor(settings.watermark_color);
                    const rotation = 45;
                    pdf.text(watermarkText, x, y, rotation);
                }
            }
        }
    }


    // function addWatermark(pdf, waterName, settings) {
    //     var defaults = {
    //         watermark_font: 'ChineseFont', // 使用jsPDF支持的字体
    //         watermark_fontsize: 12, // 字体大小使用pt单位
    //         watermark_color: 'red', // 注意jsPDF只支持rgb颜色
    //         watermark_angle: 120,
    //         watermark_spacing: 200 // 水印之间的间距
    //     };
    //     // 创建一个新的Date对象，它会自动设置为当前日期和时间
    //     let now = new Date();
    //     let year = now.getFullYear();
    //     let month = now.getMonth() + 1;
    //     month = month < 10 ? '0' + month : month; // 如果月份小于10，前面补0
    //     let day = now.getDate();
    //     day = day < 10 ? '0' + day : day; // 如果日期小于10，前面补0
    //     let hour = now.getHours();
    //     hour = hour < 10 ? '0' + hour : hour; // 如果小时小于10，前面补0
    //     let minute = now.getMinutes();
    //     minute = minute < 10 ? '0' + minute : minute; // 如果分钟小于10，前面补0
    //     let second = now.getSeconds();
    //     second = second < 10 ? '0' + second : second; // 如果秒数小于10，前面补0
    //     let date = year + '-' + month + '-' + day + ' ' + hour + ':' + minute + ':' + second;
    //     // 水印文字
    //     let watermarkText = waterName + ' ' + date;
    //     // 合并默认设置和传入设置
    //     // eslint-disable-next-line
    //     settings = Object.assign({}, defaults, settings);
    //     // 假设 A4 页面尺寸（在pt单位下，因为 jsPDF 默认使用pt）
    //     // 注意：这里的数值是基于 72 DPI（dots per inch）的转换
    //     let pageWidth = 595.28; // A4 宽度，单位：pt
    //     let pageHeight = 841.89; // A4 高度，单位：pt
    //     // 遍历每一页并添加水印
    //     for (let i = 1; i <= pdf.internal.getNumberOfPages(); i++) {
    //         pdf.setPage(i);
    //         // 计算水印文本的行数和列数
    //         let watermarkCols = Math.ceil(pageWidth / (settings.watermark_fontsize * 2 + settings.watermark_spacing));
    //         let watermarkRows = Math.ceil(pageHeight / (settings.watermark_fontsize * 1.5 + settings.watermark_spacing));
    //         // 遍历行和列来添加水印
    //         for (let col = 0; col < watermarkCols; col++) {
    //             for (let row = 0; row < watermarkRows; row++) {
    //                 // 计算水印的位置
    //                 let x = col * (settings.watermark_fontsize * 2 + settings.watermark_spacing);
    //                 let y = pageHeight - (row * (settings.watermark_fontsize * 1.5 + settings.watermark_spacing)); // 从底部开始放置
    //                 // 添加水印文本,这里的字体是引入的字体文件
    //                 pdf.setFont('FeiHuaSongTi-2');
    //                 pdf.setTextColor(settings.watermark_color);
    //                 const rotation = 45; // 整张图片旋转的角度
    //                 pdf.text(watermarkText, x, y, rotation);
    //             }
    //         }
    //     }
    // }
