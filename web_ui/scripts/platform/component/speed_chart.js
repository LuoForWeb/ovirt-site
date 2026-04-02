/**
 * 流量图插件
 * 调用方式：$('#speedchart').speedChart({
 *   job_uuid: '任务UUID',       // 必选：任务UUID
 *   updateInterval: 2000,      // 可选：数据更新间隔（毫秒，默认3000）
 *   dataPointCount: 100        // 可选：显示的数据点数量（默认100）
 * });
 */
(function($) {
    $.fn.speedChart = function(options) {
        // 默认配置
        const defaultOptions = {
            job_uuid: '',
            updateInterval: 3000,
            dataPointCount: 300,
            type: 'current'
        };

        // 合并配置
        const opts = $.extend({}, defaultOptions, options);

        // 校验必选参数
        if (!opts.job_uuid) return;

        // 流量图容器
        let $container = this.first();
        let chartContainerId = $container.attr('id');
        let code = 0;
        let maxNum = 0;
        let nextTaskFlag = false;//是否开始了下次任务
        if (!chartContainerId || !$container.length) return;

        // 插件内部变量
        let myChart = null;
        let initChartFlag = false;
        let timerTask = {};
        let data = [];
        let nowTime = [];
        let option = null;
        let isFirstLoad = true;
        let latestSpeed;
        let latestTime;

        // 初始化图表尺寸
        function initChartSize() {
            $container.css({
                width: '100%',
                height: '100%',
                minWidth: '300px',
                minHeight: '200px',
                boxSizing: 'border-box',
                padding: '5px 10px',
                margin: 0
            });
            if (myChart) {
                myChart.resize({ width: 'auto', height: 'auto' });
            }
        }

        // 提取时间字符串中的时分秒（HH:mm:ss）
        function getTimeFromStr(timeStr) {
            if (!timeStr || typeof timeStr !== 'string') return '';
            const parts = timeStr.split(' ');
            return parts.length >= 2 ? parts[1] : timeStr;
        }
        // 字节单位换算
        const formatBytes = function (value) {
            const units = ['B/s', 'KB/s', 'MB/s', 'GB/s', 'TB/s'];
            let index = 0;
            let num = Number(value); // 确保是数字类型
            while (num >= 1024 && index < units.length - 1) {
              num /= 1024;
              index++;
            }
            return `${num.toFixed(1)}${units[index]}`;
          }

        // 初始化图表数据（按时间排序，限制数据长度）
        function initTaskSpeed(dList) {
            if (initChartFlag) return;
            data = [];
            nowTime = [];
            if (dList.length == 0) {
                data = [0,0,0,0,0,0,0,0,0];
                nowTime = getTimeArr();
            } else {
                // 按时间正序排列数据
                for (let i = 0; i < dList.length; i++) {
                    data.push(parseInt(dList[i].speed) || 0);
                    nowTime.push(getTimeFromStr(dList[i].speed_time));
                }
                // 限制current模式数据长度
                if (opts.type === 'current' && data.length > opts.dataPointCount) {
                    const startIndex = data.length - opts.dataPointCount;
                    data = data.slice(startIndex);
                    nowTime = nowTime.slice(startIndex);
                }
            }
            option.series[0].data = data;
            option.xAxis.data = nowTime;
            initChartFlag = true;
            //y轴固定显示六个
            maxNum = Math.max(...data);
            if (maxNum <= 0) {
                maxNum = 25;
            }
            option.yAxis.max = maxNum;
            option.yAxis.interval = maxNum / 5;
            myChart.setOption(option);
        }

        // 更新图表数据
        function updateChart() {
            // 容器不存在时清除定时器
            if (!$('#' + chartContainerId).length || !$container.length) {
                clearTimeout(timerTask.Speed_Chart);
                return;
            }

            // 构建请求参数, code=12表示任务此时还没有跑出数据，需要继续请求
            let params = { 
                all: opts.type === 'current' && (isFirstLoad || code == 12),
                type: opts.type !== 'current' 
            };

            // 请求数据并更新图表
            pAjaxRequest(params, `/api/v1/jobs/${opts.job_uuid}/flow`, "GET", function(result) {
                code = result.code;
                if (!result.success) {
                    operateResponseList(result, result.title); 
                }
                let dList = result.data || [];
                // 非current模式：仅初始化一次
                if (opts.type !== 'current') {
                    initTaskSpeed(dList);
                } else {//当前任务
                    //dList为空之后，下一次dList不为空相当于任务重新开始运行，需要重新初始化数据
                    if (dList.length == 0) {
                        nextTaskFlag = true;
                    }
                    if(dList.length > 0 && nextTaskFlag) {
                        isFirstLoad = true;
                        nextTaskFlag = false;
                        initChartFlag = false;
                        let tempArr = [];
                        let timeArr = getTimeArr(dList[0].speed_time);
                        for (let i = 0; i < timeArr.length; i++) {
                            tempArr.push({
                                'speed': 0,
                                'speed_time': timeArr[i]
                            });
                        }
                        tempArr.push(dList[0]);
                        dList = tempArr;
                    }
                    // current模式：首次加载初始化，后续仅更新最新数据
                    if (isFirstLoad) {
                        initTaskSpeed(dList);
                        isFirstLoad = false;
                    } else if (dList.length > 0) {
                        // 取最新一条数据
                        if (latestTime != getTimeFromStr(dList[0].speed_time)) {
                            latestSpeed = parseInt(dList[0].speed) || 0;
                            latestTime = getTimeFromStr(dList[0].speed_time);
                            // 移除最旧数据，添加最新数据（避免重复）
                            if (data.length >= opts.dataPointCount) {
                                data.shift();
                                nowTime.shift();
                            }
                            data.push(latestSpeed);
                            nowTime.push(latestTime);
                            option.series[0].data = data;
                            option.xAxis.data = nowTime;
                            //更新之前记住手动选择的范围，赋值给option
                            option.dataZoom = myChart.getOption().dataZoom;
                            //y轴固定显示六个
                            maxNum = Math.max(...data);
                            if (maxNum <= 0) {
                                maxNum = 25;
                            }
                            option.yAxis.max = maxNum;
                            option.yAxis.interval = maxNum / 5;
                            myChart.setOption(option);
                        }
                    }
                    // 持续刷新
                    clearTimeout(timerTask.Speed_Chart);
                    timerTask.Speed_Chart = setTimeout(updateChart, opts.updateInterval);
                }
            }, true);
        }

        // 初始化图表配置
        function initChartOption() {
            const _chartStartPercent = 0;
            const _chartEndPercent = 100;
            return {
                tooltip: {
                    trigger: 'axis',
                    formatter: function(params) {
                        return `<div class="speed-echarts-tooltip">` +
                                    `<div class="speed-echarts-tooltip-title">`+ params[0].name +`</div>` +
                                    `<div class="speed-echarts-tooltip-info">` +
                                        `<div class="speed-echarts-tooltip-lable">任务流量</div>` +
                                        `<div class="speed-echarts-tooltip-num">`+ formatBytes(params[0].data) +`</div>` +
                                     `</div>` + 
                                `</div>`;
                    },
                    confine: true //防止tooltip溢出
                    
                },
                grid: {
                    top: 15,
                    left: 5,
                    right: 15,
                    bottom: 30,
                    containLabel: true,
                    show: false,
                    borderWidth: 0
                },
                dataZoom: [
                    {
                        type: "slider",
                        height: 18,
                        fillerColor: "rgba(15,191,152,0.1)",
                        borderColor:"#D9E5E3",//边框颜色
                        dataBackground: {
                            areaStyle: { color: '#D1F0E8' },
                            lineStyle: { color: '#AFE2D7' }
                        },
                        moveHandleSize: 2,
                        moveHandleStyle: {
                            color: '#89CFC0', 
                        },
                        handleStyle: {
                            borderColor: '#BDE4DC', //小方块边框色
                        },
                        selectedDataBackground: {
                            lineStyle: {
                              color: '#BDE4DC', //选区内部的边框色
                              width: 1, //边框宽度
                              type: 'solid' //
                            },
                            areaStyle: {
                              color: '#D1F0E8' //选区内部背景
                            },
                        },
                        brushSelect: true,
                        start: _chartStartPercent,
                        end: _chartEndPercent,
                        left: 5,
                        right: 5,
                        bottom: 3,
                        emphasis: { //hover样式
                            moveHandleStyle: {
                                color: '#72c7b4', 
                            },
                            handleStyle: {
                                borderColor: '#72c7b4', //小方块边框色
                            },
                        }
                    },
                    {
                        type: 'inside',
                        start: _chartStartPercent,
                        end: _chartEndPercent,
                    }
                ],
                xAxis: {
                    type: 'category',
                    boundaryGap: false,
                    splitLine: { show: false },
                    axisTick: { show: false },
                    axisLabel: {
                        show: true,
                        interval: 'auto',
                        fontSize: 12,
                        color: '#999999',
                        margin: 10,
                        width: 60
                    },
                    axisLine: {
                        show: true,
                        lineStyle: { color: '#C9CDD4' },
                        onZero: true
                    },
                    triggerEvent: true,
                    data: getTimeArr()
                },
                yAxis: {
                    type: 'value',
                    axisLine: { show: false },
                    axisTick: { show: false },
                    axisLabel: {
                        show: true,
                        interval: 5,
                        fontSize: 12,
                        color: '#999999',
                        margin: 15,
                        width: 50,
                        formatter: function(value) {
                            return formatBytes(value);
                        }
                    },
                    splitLine: {
                        lineStyle: {
                          type: 'dashed', 
                          color: '#EBEBEB'
                        }
                    },
                },
                series: [{
                    name: 'net',
                    type: 'line',
                    stack: 'total',
                    showSymbol: false,
                    hoverAnimation: false,
                    smoothMonotone: 'x',
                    animation: false,
                    smooth: true,
                    areaStyle: {
                        normal: {
                            color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                                { offset: 0, color: 'rgba(15, 191, 152, 0.18)' },
                                { offset: 0.8, color: 'rgba(15, 191, 152, 0.08)' },
                                { offset: 1, color: 'rgba(255, 255, 255, 0.06)' }
                            ]),
                            opacity: 1
                        }
                    },
                    data: [0,0,0,0,0,0,0,0,0],
                    lineStyle: { width: 2, color: '#0FBF98' }
                }],
                color: ['rgba(15,191,152,1)'],
                responsive: true,
                maintainAspectRatio: false
            };
        }

        // 获取最近10个时间，只返回前9个
        const getTimeArr = (time = "") => {
            const now = time !== "" ? new Date(time).getTime() : Date.now();
            return Array.from({ length: 10 }, (_, i) => {
                const offset = 9 - i; // 修改偏移量以生成10个时间点
                const d = new Date(now - offset * 3000);
                return [d.getHours(), d.getMinutes(), d.getSeconds()]
                    .map(n => n.toString().padStart(2, '0'))
                    .join(':');
            }).slice(0, 9);
        }

        // 监听尺寸变化（防抖处理）
        function watchSizeChange() {
            let resizeTimer;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(() => {
                    initChartSize();
                    if (myChart) myChart.resize();
                }, 100);
            });

            $(window).on('echart-resize', function() {
                setTimeout(() => {
                    initChartSize();
                    if (myChart) myChart.resize();
                }, 300);
            });

            // 初始化后强制适配
            setTimeout(() => {
                initChartSize();
                myChart.setOption(option);
            }, 200);
            
            initChartSize();
        }

        // 插件初始化主流程
        function init() {
            initChartSize();
            
            // 校验ECharts是否加载
            if (typeof echarts === 'undefined') {
                console.error('speedChart: echarts library not loaded');
                return;
            }
            // 初始化ECharts实例
            myChart = echarts.init($container[0]);
            option = initChartOption();
            myChart.setOption(option, true);
            
            // 启动数据更新和尺寸监听
            updateChart();
            watchSizeChange();
            
            // 暴露销毁方法
            $container.data('speedChartDestroy', function() {
                clearTimeout(timerTask.Speed_Chart);
                if (myChart) myChart.dispose();
                initChartFlag = false;
                data = [];
                nowTime = [];
                isFirstLoad = true;
            });
        }
        
        // 执行初始化
        init();
        
        // 支持链式调用
        return this;
    };
})(jQuery);