/**
 * 流量图插件
 * 调用方式：$('#speedchart').speedChart({
 *   job_uuid: '任务UUID',       // 必选：任务UUID
 *   updateInterval: 2000,      // 可选：数据更新间隔（毫秒，默认2000）
 *   dataPointCount: 100        // 可选：显示的数据点数量（默认100）
 * });
 */
(function($) {
    // 插件核心函数
    $.fn.speedChart = function(options) {
        // 默认配置
        const defaultOptions = {
            job_uuid: '',
            updateInterval: 2000,
            dataPointCount: 100
        };

        // 合并配置
        const opts = $.extend({}, defaultOptions, options);

        // 校验必选参数
        if (!opts.job_uuid) {
            return;
        }

        // 流量图容器
        let chartContainerId = this.first()[0].id;
        if (!$('#' + chartContainerId).length) {
            return;
        }

        // 全局变量（作用域限制在插件内部，避免污染全局）
        let myChart = null;
        let initChartFlag = false;
        let timerTask = {};
        let data = [];
        let nowTime = [];
        let option = null;

        // 初始化图表尺寸
        function initChartSize() {
            const $parent = $('.portlet-charts__body__speedchart');
            const chartWidth = $parent.width();
            const chartHeight = $parent.height();
            $('#' + chartContainerId).css({
                width: chartWidth + 'px',
                height: chartHeight + 'px'
            });
        }

        // 数字补零
        function parseNum(num) {
            num = parseInt(num);
            return num >= 10 ? num : "0" + num;
        }

        // 获取显示时间
        function getShowTime(timeStamp) {
            const myDate = new Date(parseInt(timeStamp));
            const hours = myDate.getHours();
            const minutes = myDate.getMinutes();
            const seconds = myDate.getSeconds();
            return parseNum(hours) + ":" + parseNum(minutes) + ":" + parseNum(seconds);
        }

        // 初始化任务速度图表
        function initTaskSpeed(d) {
            if (initChartFlag) return;

            const serverTime = d.timestamp * 1000;
            // 根据配置的 dataPointCount 初始化数据
            for (let i = opts.dataPointCount; i > 0; i--) {
                data.push(0);
                nowTime.push(getShowTime(serverTime - i * 3000));
            }

            option.series[0].data = data;
            option.xAxis.data = nowTime;
            myChart.setOption(option);
            initChartFlag = true;
        }

        // 更新图表数据
        function updateChart() {
            // 容器不存在时清除定时器
            if (!$('#' + chartContainerId).length) {
                clearTimeout(timerTask.Speed_Chart);
                return;
            }
            pAjaxRequest({}, `/api/v1/jobs/${opts.job_uuid}/flow`, "GET", function(result) {
                initTaskSpeed(result.data);

                // 更新数据（保持数据点数量为 dataPointCount）
                if (data.length >= opts.dataPointCount) {
                    data.shift();
                }
                let speed_value = parseInt(result.data.speed_value);
                data.push(isNaN(speed_value) ? 0 : speed_value);
                option.series[0].data = data;

                // 更新时间轴
                if (nowTime.length >= opts.dataPointCount) {
                    nowTime.shift();
                }
                nowTime.push(result.data.nowTime);
                option.xAxis.data = nowTime;

                myChart.setOption(option);
            }, true);

            // 清除旧定时器，设置新定时器
            clearTimeout(timerTask.Speed_Chart);
            timerTask.Speed_Chart = setTimeout(updateChart, opts.updateInterval);
        }

        // 初始化图表配置
        function initChartOption() {
            return {
                tooltip: {
                    trigger: 'axis',
                    formatter: function(params) {
                        const value = params[0].data;
                        if (value >= 1048576) {
                            return Math.ceil(value / 1048576) + " MB/s";
                        } else if (value >= 1024) {
                            return Math.ceil(value / 1024) + " KB/s";
                        } else {
                            return Math.ceil(value) + " KB/s";
                        }
                    }
                },
                grid: {
                    top: 15,
                    left: 5,
                    right: 5,
                    bottom: 5,
                    containLabel: true,
                    show: false,
                    borderWidth: 0,
                },
                xAxis: {
                    type: 'category',
                    boundaryGap: false,
                    splitNumber: 6,
                    splitLine: { show: false },
                    axisLabel: {
                        show: true,
                        interval: 12,
                        color: '#86909C'
                    },
                    axisLine: {
                        show: true,
                        lineStyle: { color: '#C9CDD4' }
                    },
                    data: []
                },
                yAxis: {
                    type: 'value',
                    splitLine: {
                        show: true,
                        lineStyle: { type: 'dashed' }
                    },
                    axisLine: { show: false },
                    axisTick: { show: false },
                    axisLabel: {
                        formatter: function(value) {
                            if (value >= 1048576) {
                                return Math.ceil(value / 1048576) + " MB/s";
                            } else if (value >= 1024) {
                                return Math.ceil(value / 1024) + " KB/s";
                            } else {
                                return Math.ceil(value) + " KB/s";
                            }
                        },
                        color: '#86909C'
                    }
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
                            color: CONF.VENDOR == 'vdms' 
                                ? '#2A87C8' 
                                : new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                                    { offset: 0, color: 'rgba(59, 179, 70, 0.2)' },
                                    { offset: 1, color: 'rgba(59, 179, 70, 0)' }
                                ]),
                            opacity: CONF.VENDOR == 'vdms' ? 0.2 : 1
                        }
                    },
                    data: []
                }],
                color: CONF.VENDOR == 'vdms' ? ['#2A87C8'] : ['#44b6ae']
            };
        }

        // 监听尺寸变化
        function watchSizeChange() {
            // 窗口 resize 事件
            window.addEventListener('resize', function() {
                initChartSize();
                if (myChart) myChart.resize();
            });

            // 自定义 echart-resize 事件
            $(window).on('echart-resize', function() {
                setTimeout(() => {
                    initChartSize();
                    if (myChart) myChart.resize();
                }, 300);
            });
        }

        // 插件初始化主流程
        function init() {
            // 1. 初始化图表尺寸
            initChartSize();
            // 2. 初始化 echarts 实例
            if (typeof echarts === 'undefined') {
                console.error('speedChart is undefine');
                return;
            }
            myChart = echarts.init($('#' + chartContainerId)[0]);
            // 3. 初始化图表配置
            option = initChartOption();
            // 4. 启动数据更新
            updateChart();
            // 5. 监听尺寸变化
            watchSizeChange();
            // 6. 暴露销毁方法
            $('#' + chartContainerId).data('speedChartDestroy', function() {
                clearTimeout(timerTask.Speed_Chart);
                if (myChart) myChart.dispose();
                initChartFlag = false;
                data = [];
                nowTime = [];
            });
        }
        // 执行初始化
        init();
        // 支持链式调用
        return this;
    };
})(jQuery);