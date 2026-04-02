var DataBackupCenter = function () {
  var timer = {};
  var _TASKINTERVAL = 5000;		//任务获取延迟时间	5秒钟
  var initNetworkFlag = true;
  var initCPUFlag = true;
  var initMemoryFlag = true;
  var timerTask = {};
  var timedata = [];
  var netOutdata = [];
  var netIndata = [];
  var cpuUsagedata = [];
  var cputimedata = [];
  var memoryUsagedata = [];
  var memorytimedata = [];
  var curentgrid, gridCurrentInitFlag = false;
  var historygrid, gridHistoryInitFlag = false;
  var showTime;//系统时间
  var cpuChart, memoryChart, networkLineChart, pieChart, consumpChart;
  var initListener = function () {
    $("#node_uuid").on('change', function () {
      initNetworkFlag = true;
      initCPUFlag = true;
      initMemoryFlag = true;
      getSystemData();
    });


  }
  //设置系统系统时间格式
  var showLeftTime = function (systime) {
    var now = new Date(systime);
    var year = now.getFullYear();
    var month = getTwoNum(now.getMonth() + 1);
    var day = getTwoNum(now.getDate());
    var hours = getTwoNum(now.getHours());
    var minutes = getTwoNum(now.getMinutes());
    var seconds = getTwoNum(now.getSeconds());
    showTime = year + "-" + month + "-" + day + " " + hours + ":" + minutes + ":" + seconds + "";
    var sysdate = day + "/" + month + "/" + year;
    var systime = hours + ":" + minutes + ":" + seconds;
    $(".sysdate").html(sysdate);
    $(".systime").html(systime);
    showTime = Date.parse(showTime) + 1000;
  }
  var setSystemDataCenter = function (data) {
    showLeftTime(data.systemRunningTimeInfo.runningTime);
    $("#currentTaskNum").html(data.currentTaskNum);
    $("#historyTaskNum").html(data.historyTaskNum);
    // 设置系统运行时间
    $("#year").html(data.systemRunningTimeInfo.runningDay.year);
    $("#day").html(data.systemRunningTimeInfo.runningDay.day);
    if (data.systemRunningTimeInfo.runningDay.year != null) {
      $("#yearspan").show()
    }
  }

  setInterval(function () { showLeftTime(showTime); }, 1000);
  //时间补全成两位数
  var getTwoNum = function (num) {
    if (num < 10) {
      num = '0' + num;
    }
    return num;
  }

  //初始化节点信息
  var initNodeUUID = function () {
    $.post(CONF.AJAXPATH, { m: CONF.M.SYSTEMMONITOR, f: 'getNodeUUid', p: {} }, function (d) {
      var data = JSON.parse(d);
      var nodeselect = $('#node_uuid');
      nodeselect.empty();
      for (var i = 0; i < data.length; i++) {
        var option = $("<option>").text(data[i].name).val(data[i].node_uuid);
        nodeselect.append(option);
      }
      getSystemData();

    });

  }
  var setCpuMemoryData = function (data) {
    setCpuData(data);
    setMemoryData(data);
  }
  var setCpuData = function (data) {
    if (!initCPUFlag) {
      cputimedata.shift();
      cpuUsagedata.shift();
      cputimedata.push(data[0].time);
      cpuUsagedata.push(data[0].cpumemorydata.cpuUsedRate);

    } else {
      initCPUFlag = false;
      cputimedata = [];
      cpuUsagedata = [];
      for (var i = 0; i < data.length; i++) {
        cputimedata.push(data[i].time);
        cpuUsagedata.push(data[i].cpumemorydata.cpuUsedRate);
      }
    }
    //CPU使用率折线
    cpuChart = echarts.init(document.getElementById('cpuChart'));
    option = {
      title: {
        text: LANG.UI_SYSTEM_MONITOR_CPU_PERCENTAGE,
        textStyle: { // 标题样式
          color: "#1D2129",
          fontSize: "14",
          fontWeight: 'bold',
        },

      },
      tooltip: {
        trigger: "axis",
        icon: "circle",
        axisPointer: {
          icon: "circle",
          lineStyle: {
            color: "#1EB2FF"
          }
        },
        formatter: function (params) {
          // console.log("formatter",params);
          var relVal = params[0].name
          for (var i = 0, l = params.length; i < l; i++) {
            var pvalue = params[i].value;
            relVal += '<div style="background: rgba(255,255,255,0.9);height:32px;line-height:32px;box-shadow: 6px 0px 20px 0px rgba(34,87,188,0.1);border-radius:4px;font-size:12px;padding-left:9px;padding-right:12px;margin-top:4px">' + 'CPU使用率<span style="margin-left:11px;font-weight:bold">' + pvalue + '%</span></div>';
          }
          return relVal;
        }
      },
      grid: {
        left: "10",
        top: "40",
        right: "28",
        bottom: "10",
        containLabel: true,
      },

      xAxis: [
        {
          type: "category",
          boundaryGap: false,
          axisLabel: {
            textStyle: {
              color: "#7C7D8B",
              fontSize: 12
            }
          },
          axisLine: {
            lineStyle: {
              color: "rgba(255,255,255,.2)"
            },
          },
          data: cputimedata,
          splitLine: {//分割线
            show: true,
            lineStyle: {
              color: "#E6E9F4"
            }
          }
        },
        {
          axisPointer: { show: false },
          axisLine: { show: false },
          position: "bottom",
          offset: 20
        },
      ],
      yAxis: [
        {
          type: "value",
          //最小值
          min: 0,
          //最大值
          max: 100,
          axisTick: { show: false },//y轴刻度
          axisLine: {
            lineStyle: {
              color: "#E6E9F4"
            }
          },
          axisLabel: {
            textStyle: {
              color: "#7C7D8B",
              fontSize: 12
            },
            formatter: function (value, index) {
              return Math.round(value) + "%";
            }
          },
          splitLine: {//不显示分割线
            show: false,
          }
        }
      ],
      series: [
        {
          name: "",
          type: "line",
          smooth: true,
          symbol: "circle",
          symbolSize: 5,
          showSymbol: false,
          lineStyle: {
            normal: {
              // color: "#00B2FF",
              color: new echarts.graphic.LinearGradient(0, 0, 1, 0,
                [
                  {
                    offset: 0,
                    color: "#00B2FF"
                  },
                  {
                    offset: 1,
                    color: "#7042FB"
                  }
                ],
                false
              ),
              width: 2
            }
          },
          areaStyle: {
            normal: {
              color: new echarts.graphic.LinearGradient(0, 0, 0, 1,
                [
                  {
                    offset: 0,
                    // color: "#02e3a33c"
                    color: "rgba(17,126,255,0.16)"
                  },
                  {
                    offset: 1,
                    color: "rgba(255, 255, 255, 0.3)"
                  }
                ],
                false
              ),
              shadowColor: "rgba(0, 0, 0, 0.1)"
            }

          },
          itemStyle: {
            normal: {
              color: "#00B2FF",
              borderColor: "rgba(0,178,255, .1)",
              borderWidth: 12
            }
          },
          data: cpuUsagedata,
        }
      ]
    };
    cpuChart.setOption(option);

  }
  var setMemoryData = function (data) {
    if (!initMemoryFlag) {
      memorytimedata.shift();
      memoryUsagedata.shift();
      memorytimedata.push(data[0].time);
      memoryUsagedata.push(data[0].cpumemorydata.memoryUsedRate);

    } else {
      initMemoryFlag = false;
      memorytimedata = [];
      memoryUsedRate = [];
      for (var i = 0; i < data.length; i++) {
        memorytimedata.push(data[i].time);
        memoryUsagedata.push(data[i].cpumemorydata.memoryUsedRate);
      }
    }
    //内存使用率折线
    memoryChart = echarts.init(document.getElementById('memeryChart'));
    option = {
      title: {
        text: LANG.UI_SYSTEM_MONITOR_RAM_PERCENTAGE,
        textStyle: { // 标题样式
          color: "#1D2129",
          fontSize: "14",
          fontWeight: 'bold',
        },

      },
      tooltip: {
        trigger: "axis",
        icon: "circle",
        axisPointer: {
          icon: "circle",
          lineStyle: {
            color: "#1EB2FF"
          }
        },
        formatter: function (params) {
          var relVal = params[0].name
          for (var i = 0, l = params.length; i < l; i++) {
            var pvalue = params[i].value;
            //   if(pvalue >= 1024){
            // 	  pvalue =  Math.round(pvalue * 10 / 1024) / 10 + "MB/s";
            //   }else{
            // 	  pvalue = pvalue + "KB/s";
            //   }
            // relVal += '<br/>' + ' <span style="display:inline-block;margin-right:5px;width:10px;height:10px;border-radius: 50%!important;background-color:'+ params[i].color +';"></span>' +'  '+ params[i].seriesName +'  '+ pvalue;
            relVal += '<div style="background: rgba(255,255,255,0.9);height:32px;line-height:32px;box-shadow: 6px 0px 20px 0px rgba(34,87,188,0.1);border-radius:4px;font-size:12px;padding-left:9px;padding-right:12px;margin-top:4px">' + '内存使用率<span style="margin-left:11px;font-weight:bold">' + pvalue + '%</span></div>';
          }
          return relVal;
        }
      },
      grid: {
        left: "10",
        top: "40",
        right: "28",
        bottom: "10",
        containLabel: true,

      },

      xAxis: [
        {
          type: "category",
          boundaryGap: false,
          axisLabel: {
            textStyle: {
              color: "#7C7D8B",
              fontSize: 12
            }
          },
          axisLine: {
            lineStyle: {
              color: "rgba(255,255,255,.2)"
            },
          },

          data: memorytimedata,
          splitLine: {//分割线
            show: true,
            lineStyle: {
              color: "#E6E9F4"
            }
          }
        },
        {
          axisPointer: { show: false },
          axisLine: { show: false },
          position: "bottom",
          offset: 20
        },
      ],

      yAxis: [
        {
          type: "value",
          //最小值
          min: 0,
          //最大值
          max: 100,
          axisTick: { show: false },//y轴刻度
          axisLine: {
            lineStyle: {
              color: "#E6E9F4"
            }
          },
          axisLabel: {
            textStyle: {
              color: "#7C7D8B",
              fontSize: 12
            },
            formatter: function (value, index) {
              return Math.round(value) + "%";
            }
          },
          splitLine: {//不显示分割线
            show: false,
          }
        }
      ],
      series: [
        {
          name: "",
          type: "line",
          smooth: true,
          symbol: "circle",
          symbolSize: 5,
          showSymbol: false,
          lineStyle: {
            normal: {
              // color: "#00B2FF",
              color: new echarts.graphic.LinearGradient(0, 0, 1, 0,
                [
                  {
                    offset: 0,
                    color: "#00B2FF"
                  },
                  {
                    offset: 1,
                    color: "#7042FB"
                  }
                ],
                false
              ),
              width: 2
            }
          },
          areaStyle: {
            normal: {
              color: new echarts.graphic.LinearGradient(0, 0, 0, 1,
                [
                  {
                    offset: 0,
                    color: "rgba(17, 126, 255, 0.16)"
                  },
                  {
                    offset: 1,
                    color: "rgba(17,128,255,0)"
                  }
                ],
                false
              ),
              // shadowColor: "rgba(17, 128, 255, 0)"
            }

          },
          itemStyle: {
            normal: {
              color: "#00B2FF",
              borderColor: "rgba(0,178,255, .1)",
              borderWidth: 12
            }
          },
          data: memoryUsagedata,
        }
      ]
    };
    memoryChart.setOption(option);
    window.onresize = function () {
      memoryChart.resize();
    }
  }
  var setNetworkData = function (data) {
    if (!initNetworkFlag) {
      timedata.shift();
      netOutdata.shift();
      netIndata.shift();
      timedata.push(data[0].time);
      netOutdata.push(data[0].network.transmit);
      netIndata.push(data[0].network.receive);

    } else {
      initNetworkFlag = false;
      timedata = [];
      netOutdata = [];
      netIndata = [];
      for (var i = 0; i < data.length; i++) {
        timedata.push(data[i].time);
        netOutdata.push(data[i].network.transmit);
        netIndata.push(data[i].network.receive);
      }
    }
    //网络流量折线
    networkLineChart = echarts.init(document.getElementById('networkTrafficChart'));
    option = {
      title: {
        text: LANG.UI_SYSTEM_MONITOR_NET_PERCENTAGE,
        textStyle: { // 标题样式
          color: "#1D2129",
          fontSize: "14",
          fontWeight: 'bold',
        },

      },
      tooltip: {
        trigger: "axis",
        icon: "circle",
        axisPointer: {
          icon: "circle",
          lineStyle: {
            color: "#1EB2FF"
          }
        },
        formatter: function (params) {
          var relVal = params[0].name
          for (var i = 0, l = params.length; i < l; i++) {
            var pvalue = params[i].value;
            if (pvalue >= 1024) {
              pvalue = Math.round(pvalue * 10 / 1024) / 10 + "MB/s";
            } else {
              pvalue = pvalue + "KB/s";
            }
            relVal += '<div style="background: rgba(255,255,255,0.9);height:32px;line-height:32px;box-shadow: 6px 0px 20px 0px rgba(34,87,188,0.1);border-radius:4px;font-size:12px;padding-left:9px;padding-right:12px;margin-top:4px">' + ' <span style="display:inline-block;margin-right:4px;margin-top:11px;width:10px;height:10px;border-radius: 50%!important;background-color:' + params[i].color + ';"></span>' + '  ' + '<span style="color:#4E5969;margin-right:14px">' + params[i].seriesName + '</span>' + '  ' + pvalue + '</div>';
          }
          return relVal;
        }
      },
      legend: {
        icon: "circle",//形状  类型包括 circle，rect,line，roundRect，triangle，diamond，pin，arrow，none
        top: "0%",
        right: "0%",
        itemWidth: 10,  // 设置宽度
        itemHeight: 8, // 设置高度
        itemGap: 35, // 设置图例间距
        textStyle: {
          color: "#3D3D46",
          fontSize: "12"
        },
      },
      grid: {
        left: "10",
        top: "40",
        right: "28",
        bottom: "10",
        containLabel: true,

      },
      xAxis: [
        {
          type: "category",
          boundaryGap: false,
          axisLabel: {
            textStyle: {
              color: "#7C7D8B",
              fontSize: 12
            }
          },
          axisLine: {
            lineStyle: {
              color: "rgba(255,255,255,.2)"
            },
          },
          data: timedata,
          splitLine: {//分割线
            show: true,
            lineStyle: {
              color: "#E6E9F4"
            }
          }
        },
        {
          axisPointer: { show: false },
          axisLine: { show: false },
          position: "bottom",
          offset: 20
        },
      ],
      yAxis: [
        {
          type: "value",
          splitNumber: 3,
          axisTick: { show: false },//y轴刻度
          axisLine: {
            lineStyle: {
              color: "#E6E9F4"
            }
          },
          axisLabel: {
            textStyle: {
              color: "#7C7D8B",
              fontSize: 12
            },
            formatter: function (value, index) {
              if (value >= 1024) {
                return Math.round(value * 10 / 1024) / 10 + "MB/s";
              } else {
                return value + "KB/s";
              }
            }
          },
          splitLine: {//不显示分割线
            show: false,
          }
        }
      ],
      series: [
        {
          name: LANG.UI_PUBLIC_INFLOW,
          type: "line",
          smooth: true,
          symbol: "circle",
          symbolSize: 5,
          showSymbol: false,
          lineStyle: {
            normal: {
              color: "#00B2FF",
              width: 2
            }
          },
          areaStyle: {
            normal: {
              color: new echarts.graphic.LinearGradient(0, 0, 0, 1,
                [
                  {
                    offset: 0,
                    // color: "#02e3a33c"
                    color: "rgba(17,126,255,0.16)"
                  },
                  {
                    offset: 1,
                    color: "rgba(255, 255, 255, 0.3)"
                  }
                ],
                false
              ),
              shadowColor: "rgba(0, 0, 0, 0.1)"
            }

          },
          itemStyle: {
            normal: {
              color: "#00B2FF",
              borderColor: "rgba(0,178,255, .1)",
              borderWidth: 12
            }
          },
          data: netIndata,
        },
        {
          name: LANG.UI_PUBLIC_OUTFLOW,
          type: "line",
          smooth: true,
          symbol: "circle",
          symbolSize: 5,
          showSymbol: false,
          lineStyle: {
            normal: {
              color: "#7042FB",
              width: 2
            }
          },
          areaStyle: {
            normal: {
              color: new echarts.graphic.LinearGradient(0, 0, 0, 1,
                [
                  {
                    offset: 0,
                    color: "rgba(0,178,255,0.1)"
                  },
                  {
                    offset: 1,
                    color: "rgba(17,128,255,0)"
                  }
                ],
                false
              ),
              // shadowColor: "rgba(17, 128, 255, 0)"
            }
          },
          itemStyle: {
            normal: {
              color: "#7042FB",
              borderColor: "rgba(112,66,251, .1)",
              borderWidth: 12
            }
          },
          data: netOutdata,
        }
      ]
    };
    networkLineChart.setOption(option);

  }
  //设置系统剩余容量echart
  var setSystemStorageData = function (data) {
    // var sum = data.backupInfo.usedCapacity.size + data.backupInfo.remainingCapacity.size;
    var sum = data.storageInfo.bakStorageCapacity.size;
    if (sum == 0) {
      var usedPercent = 0 + "%";
      var remainPercent = 0 + "%";
    } else {
      var usedPercent = Math.round(data.storageInfo.usedCapacity.size / (sum) * 10000) / 100 + "%";
      var remainPercent = Math.round(data.storageInfo.remainingCapacity.size / (sum) * 10000) / 100 + "%";
    }
    $(".usedPercent").html(usedPercent);
    $(".usedNum").html(data.storageInfo.usedCapacity.des);
    $(".remainPercent").html(remainPercent);
    $(".remianNum").html(data.storageInfo.remainingCapacity.des);
    initStorageChart(data);
  }
  //初始化存储容量饼图
  var initStorageChart = function (data) {
    var usedCapacity = data.storageInfo.usedCapacity.size;
    var remainingCapacity = data.storageInfo.remainingCapacity.size;
    var bakStorageCapacity = data.storageInfo.remainingCapacity.des;
    var useddes = data.storageInfo.usedCapacity.des;
    var remaindes = data.storageInfo.remainingCapacity.des;
    //存储饼图
    pieChart = echarts.init(document.getElementById('capacityCircle'));
    option = {
      title: [
        {
          text: `{name|${LANG.UI_HOMEPAGE_REMAIN_STORAGE}}\n{val|${bakStorageCapacity}}`,
          top: 'center',
          left: 'center',
          textStyle: {
            rich: {
              name: {
                fontSize: 12,
                color: '#4E5969',
                padding: [7, 0]
              },
              val: {
                fontSize: 16,
                fontWeight: 'bold',
                color: '#1D2129'
              }
            }
          }
        },
      ],
      tooltip: {
        trigger: "item",
        position: function (p) {
          //其中p为当前鼠标的位置
          return [p[0] + 10, p[1] - 10];
        },
        formatter: function (params) {
          var relVal = "";
          if (params.name == LANG.UI_HOMEPAGE_REMAIN_STORAGE) {
            relVal = params.name + "<br>" + '<span style="display:inline-block;margin-right:4px;border-radius:50% !important;width:10px;height:10px;background-color:' + params.color + ';"></span>' + " " + remaindes + " " + "(" + params.percent + "%)";
          } else {
            relVal = params.name + "<br>" + '<span style="display:inline-block;margin-right:4px;border-radius:50% !important;width:10px;height:10px;background-color:' + params.color + ';"></span>' + " " + useddes + " " + "(" + params.percent + "%)";
          }
          return relVal;
        }
      },
      legend: {
        show: false
      },
      itemStyle: {
        borderColor: '#fff',
        borderWidth: 1
      },
      series: [
        {
          name: LANG.UI_VISUAL_SIZE,
          type: "pie",
          minAngle: 1,
          radius: ["45%", "70%"],
          color: ["#249EFF", "#313CA9"],
          label: {
            rotate: 0,
            show: true,
            position: "outer",
            alignTo: "none",
            distanceToLabelLine: 5,
            formatter: function (params) {
              var val = Math.round(params.percent) + '%';
              return val;
            }
          },
          labelLine: {
            show: true,
            smooth: false,
            length: 8,
            length2: 10,
            minTurnAngle: 90,
            maxSurfaceAngle: 90,
            lineStyle: {
              width: 1,
              type: "solid"
            },
          },
          data: [
            { value: remainingCapacity, name: LANG.UI_HOMEPAGE_REMAIN_STORAGE },
            { value: usedCapacity, name: LANG.UI_HOMEPAGE_USED_STORAGE },
          ]
        },
      ]
    };
    pieChart.setOption(option);
    window.onresize = function () {
      pieChart.resize();
    }
  }
  //设置最近七日用量统计数据
  var setSevenDayConsumpData = function (data) {
    var dateArr = [];
    var backup = []; //备份
    var backup_point = [];
    for (var i = 0; i < data.list.length; i++) {
      dateArr.push(data.list[i].date);
      backup.push({ value: data.list[i].total_data, unit: data.list[i].total_data_des_unit, des: data.list[i].total_data_des });
      backup_point.push(data.list[i].total_data);
    }
    //初始化echart图
    consumpChart = echarts.init(document.getElementById('consumpChart'));
    var option = {
      color: ["#00B2FF"],
      legend: {
        icon: "circle",
        show: true,
        left: '50%',
        bottom: 0,
        itemWidth: 8,
        itemHeight: 8,
        textStyle: {
          color: "#4E5969",
          fontSize: "12",
        }
      },
      grid: {
        left: "40",
        top: "33",
        right: "20",
        bottom: "26",
        containLabel: true
      },
      tooltip: {
        trigger: "axis",
        icon: "circle",
        axisPointer: {
          icon: "circle",
        },
        formatter: function (params) {
          var relVal = params[0].name
          for (var i = 0, l = params.length; i < l; i++) {
            relVal += '<div style="background: rgba(255,255,255,0.9);height:32px;line-height:32px;box-shadow: 6px 0px 20px 0px rgba(34,87,188,0.1);border-radius:4px;font-size:12px;padding-left:9px;padding-right:12px;margin-top:4px">' + ' <span style="display:inline-block;margin-right:4px;margin-top:11px;width:10px;height:10px;border-radius: 50%!important;background-color:' + params[i].color + ';"></span>' + '  ' + '<span style="color:#4E5969;margin-right:43px">' + '当日用量' + '</span>' + '<span style="font-weight:bold">' + params[i].data.des + params[i].data.unit + '</span></div>';
          }
          return relVal;
        }
      },
      xAxis: [
        {
          type: "category",
          data: dateArr,
          axisTick: {
            alignWithLabel: true
          },
          axisLabel: {
            textStyle: {
              color: "#86909C",
              fontSize: "12"
            }
          },
          axisLine: {
            show: false
          }
        }
      ],
      yAxis: [
        {
          type: "value",
          splitNumber: 6, // 横线数
          axisLabel: {
            textStyle: {
              color: "#4E5969",
              fontSize: "12"
            },
            formatter: function (value, index) {
              var arrayUnit = ["B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB", "BB", "NB", "DB"];
              var j = 0;
              while (value >= 1024) {
                value = value / 1024;
                j++;
              }
              if (j >= 1) {
                return value.toFixed(1) + arrayUnit[j];
              } else {
                return value + arrayUnit[j];
              }
            }
          },
          axisLine: {
            lineStyle: {
              show: false
            }
          },
          splitLine: {
            lineStyle: {
              color: "#F2F3F5"
            }
          }
        }
      ],
      series: [
        {
          name: LANG.UI_PUBLIC_LATEST_USED_OVERVIEW,
          type: "bar",
          barWidth: "13",
          data: backup,
          itemStyle: {
            barBorderRadius: 2
          }
        }
      ]
    };
    consumpChart.setOption(option);
  }
  //初始化当前任务表格
  var initCurrentTable = function () {
    var updateInterval = 10000;
    var dataTableOpt = {
      'scrollY': 200,
      'paging': false,
      'showLoading': false,
      'columnDefs': [{
        'orderable': false,
        'targets': [0, 4]
      }
      ],
    };
    var setData = function (div, data) { //设置表格中的数据
      $(div).html(data);
    }
    var setStatus = function (div, data) { //设置表格中的状态
      var color = "";
      //状态按钮颜色
      switch (data) {
        case "等待":
        case "同步":
        case "准备中":
        case "停止中":
        case "暂停中":
        case "启动中":
          color = "#00B2FF";  //蓝
          break;
        case "停止":
          color = "#A1A5B7" //灰
          break;
        case "异常":
          color = "#FCC04A" //黄
          break;
        case "网络错误":
        case "错误":
          color = "#F97474" //红
          break;
        case "成功":
        case "暂停":
        case "运行":
          color = "#2FD3C0" //绿
          break;
        default:
          color = "#00B2FF";  //蓝  完成 接管中 接管启动中 接管停止中 创建中 挂起 删除中 清理中
          break;
      }

      var content = '<div  class="status" style=background-color:' + color + '>' + data + '</div>'
      // var content = '<div  class="status">'+ data +'</div>'
      $(div).html(content);
    }
    var setcurPic = function (div, object_type) { //设置表格中第一列的图片
      var content = '<img  src="../../img/platform/datacenter-spur/' + object_type + '.png"/>'
      $(div).html(content);
    }
    var initRow = function () {
      var data = curentgrid.getDataTable().data();
      if (!data || 0 == data.length) return;
      //  console.log("data",data);
      var picDiv = $('#currentTaskTable tbody > tr').find('td:eq(0)');
      var tasknameDiv = $('#currentTaskTable tbody > tr').find('td:eq(1)');
      var moduletypeDiv = $('#currentTaskTable tbody > tr').find('td:eq(2)');
      var tasktypeDiv = $('#currentTaskTable tbody > tr').find('td:eq(3)');
      var statusDiv = $('#currentTaskTable tbody > tr').find('td:eq(4)');
      for (var i = 0; i < picDiv.length; i++) {
        setcurPic(picDiv[i], data[i][5]); //设置图片
      }
      for (var i = 0; i < picDiv.length; i++) {
        setData(tasknameDiv[i], data[i][1]);
      }
      for (var i = 0; i < moduletypeDiv.length; i++) {
        //setModuleType(moduletypeDiv[i], data[i]);
        setData(moduletypeDiv[i], data[i][2]);//模块类型
      }
      for (var i = 0; i < tasktypeDiv.length; i++) {
        // setTaskType(tasktypeDiv[i], data[i]);
        setData(tasktypeDiv[i], data[i][3]);//任务类型
      }
      for (var i = 0; i < statusDiv.length; i++) {
        setStatus(statusDiv[i], data[i][4]);//状态
      }

      // initGridRowSelectListener();	//初始化成单选
    }
    var initGrid = function () {
      if (0 == $('#currentTaskTable').size()) {
        clearTimeout(timerTask.CurrentJob_data);
        return;
      }
      // clearTimeout(timerTask.CurrentJob_data);
      if (!gridCurrentInitFlag) {
        curentgrid = new Datatable();
        var data = { m: CONF.M.HOMEPAGE, f: 'getCurrentTaskList', p: {} };
        curentgrid.setAjaxParam(data);
        curentgrid.init({ src: $("#currentTaskTable"), showDetail: false, checkbox: false, dataTable: dataTableOpt, onDataLoad: initRow });
        gridCurrentInitFlag = true;

      } else {
        curentgrid.getRefresh();
      }

      timerTask.CurrentJob_data = setTimeout(initGrid, updateInterval);
    }
    initGrid();
    return;
  }
  var initHistoryTable = function () {
    var updateInterval = 10000;
    var dataTableOpt = {
      'scrollY': 200,
      'paging': false,
      'showLoading': false,
      'columnDefs': [{
        'orderable': false,
        'targets': [0, 5]
      }
      ],
    };
    var setData = function (div, data) { //设置表格中的数据
      $(div).html(data);
    }
    var setStatus = function (div, data) { //设置表格中的状态
      var color = "";
      //状态按钮颜色
      switch (data) {
        case "失败":
          color = "#F97474"
          break;
        case "异常":
          color = "#FCC04A";
          break;
        case "成功":
          color = "#2FD3C0"
          break;
        case "中止":
          color = "#00B2FF"
      }
      var content = '<div  class="status" style=background-color:' + color + '>' + data + '</div>'
      $(div).html(content);
    }
    var setPic = function (div, object_type) { //设置表格中第一列的图片 //如果是副本
      var content = '<img  src="../../img/platform/datacenter-spur/' + 'his-' + object_type + '.png"/>'
      $(div).html(content);

    }
    var initRow = function () {
      var data = historygrid.getDataTable().data();
      if (!data || 0 == data.length) return;

      //  console.log("data",data);
      var picDiv = $('#historyTable tbody > tr').find('td:eq(0)');
      var tasknameDiv = $('#historyTable tbody > tr').find('td:eq(1)');
      var moduletypeDiv = $('#historyTable tbody > tr').find('td:eq(2)');
      var tasktypeDiv = $('#historyTable tbody > tr').find('td:eq(3)');
      var timeDiv = $('#historyTable tbody > tr').find('td:eq(4)');
      var statusDiv = $('#historyTable tbody > tr').find('td:eq(5)');
      for (var i = 0; i < picDiv.length; i++) {
        setPic(picDiv[i], data[i][6]); //设置图片
      }
      for (var i = 0; i < picDiv.length; i++) {
        setData(tasknameDiv[i], data[i][1]);
      }
      for (var i = 0; i < moduletypeDiv.length; i++) {
        //setModuleType(moduletypeDiv[i], data[i]);
        setData(moduletypeDiv[i], data[i][2]);//模块类型
      }
      for (var i = 0; i < tasktypeDiv.length; i++) {
        // setTaskType(tasktypeDiv[i], data[i]);
        setData(tasktypeDiv[i], data[i][3]);//任务类型
      }
      for (var i = 0; i < tasktypeDiv.length; i++) {
        // setTaskType(tasktypeDiv[i], data[i]);
        setData(timeDiv[i], data[i][4]);//时间
      }
      for (var i = 0; i < statusDiv.length; i++) {
        setStatus(statusDiv[i], data[i][5]);
      }
    }
    var initHistoryGrid = function () {
      if (0 == $('#historyTable').size()) {
        clearTimeout(timerTask.HistoryJob_data);
        return;
      }
      if (!gridHistoryInitFlag) {
        historygrid = new Datatable();
        var data = { m: CONF.M.HOMEPAGE, f: 'getHistoryTaskList', p: {} };
        historygrid.setAjaxParam(data);
        historygrid.init({ src: $("#historyTable"), showDetail: false, checkbox: false, dataTable: dataTableOpt, onDataLoad: initRow });
        gridHistoryInitFlag = true;

      } else {
        historygrid.getRefresh();
      }
      timerTask.HistoryJob_data = setTimeout(initHistoryGrid, updateInterval);
    }
    initHistoryGrid();
    return;

  }
  var getSystemData = function () {
    if (0 == $('.datacenter-content').size()) {
      clearTimeout(timerTask.getSystemData);
      return;
    } else {
      clearTimeout(timerTask.getSystemData);
    }
    var updateInterval = 2000;
    var p = {}
    p.node_uuid = $("#node_uuid").val();
    if (initNetworkFlag) {
      p.count = 100;
    } else {
      p.count = 1;
    }
    p = JSON.stringify(p);
    $.post(CONF.AJAXPATH, { m: CONF.M.HOMEPAGE, f: 'getSystemData', p: p }, function (d) {
      var data = JSON.parse(d);
      var cpuMemoryData = data.cpumemory;
      var systemViewData = data.systemViewData; //系统数据概览
      var systemStorageData = data.systemStorageData;//系统存储数据 
      var staticData = data.staticData; //最近七日统计数据
      // console.log("staticData",staticData);
      var netWorkData = JSON.parse(data.netWorkData);
      //倒转后的新数组
      netWorkData = $.map(netWorkData, function (item, index) {// map方法匿名函数传的值v是值、i是索引。
        return netWorkData[netWorkData.length - 1 - index];
      });
      cpuMemoryData = $.map(netWorkData, function (item, index) {// map方法匿名函数传的值v是值、i是索引。
        return cpuMemoryData[cpuMemoryData.length - 1 - index];
      });
      setSystemDataCenter(systemViewData); //设置系统概览数据
      setSystemStorageData(systemStorageData); //设置系统存储率
      setNetworkData(netWorkData); //设置网络流量
      setCpuMemoryData(cpuMemoryData);//设置cpu 内存使用率
      setSevenDayConsumpData(staticData.data_statistics); //设置最近七日用量统计
      window.onresize = function () {
        cpuChart.resize();
        memoryChart.resize();
        networkLineChart.resize();
        pieChart.resize();
        consumpChart.resize();
      }
    }).complete(function () { timerTask.getSystemData = setTimeout(function () { getSystemData(); }, updateInterval); });
  }
  //更新告警信息
  var updateAlarmTips = function () {
    $.post(CONF.AJAXPATH, { m: CONF.M.ALARM, f: 'getSurveyNoticeInfo', p: {} }, function (d) {
      setAlarmInfo(d);
    });
  }
  //初始化是否为租户内部,并设置任务窗口事件
  var initUserType = function () {
    $.post(CONF.AJAXPATH, { m: CONF.M.USER, f: 'getUserExtendInfo', p: {} }, function (d) {
      var data = JSON.parse(d);
      if (data.tenantuuid == "" && data.username == "admin") {
        addManagerListener();
      } else if (data.tenantuuid == "") {
        addOperatorListener();
      }
    });

    $('.taskhrefcurrent').off().on('click', function () {
      LOCATION('./content/platform/jobs/jobs.php', 'task', { tabId: 'currentLi' });
    });
    $('.taskhrefhistory').off().on('click', function () {
      LOCATION('./content/platform/jobs/jobs.php?tab=1', 'task', { tabId: 'historyLi' });
    });
    $('.alarmhreftask').off().on('click', function () {
      LOCATION('./content/platform/alarm/alarm.php', 'alarm');
    });
    $('.alarmhrefsystem').off().on('click', function () {
      LOCATION('./content/platform/alarm/alarm.php?tab=1', 'alarm', { tabId: 'system_alarm' });
    });

  }
  //添加管理员事件
  var addManagerListener = function () {
    $('#moretaskinfo').on('click', function () {
      CTLHORMENU('bakandrec');
      var url = './content/platform/jobs/history_job.php';
      var urlMark = '?history_job';
      if ($('#currenttaskli').hasClass("active")) {
        url = './content/platform/jobs/jobs.php';
        urlMark = '?current_job';
      }
      LOCATION(url);
      History.pushState({ url: url }, "", urlMark);
    });
    $('.currentjob').off().on('click', function () {
      CTLHORMENU('bakandrec');
    })
  }

  //添加操作员事件
  var addOperatorListener = function () {
    $('#moretaskinfo').on('click', function () {
      CTLHORMENU('bakandrec');
      var url = './content/platform/jobs/history_job.php';
      var urlMark = '?history_job';
      if ($('#currenttaskli').hasClass("active")) {
        url = './content/platform/jobs/jobs.php';
        urlMark = '?current_job';
      }
      LOCATION(url);
      History.pushState({ url: url }, "", urlMark);
    });
    $('.currentjob').off().on('click', function () {
      CTLHORMENU('bakandrec');
    })
  }

  //初始化告警
  var initAlarmTips = function () {
    var getAlarmInfo = function () {
      $.post(CONF.AJAXPATH, { m: CONF.M.ALARM, f: 'getSurveyNoticeInfo', p: {} }, function (d) {
        setAlarmInfo(d);
      })
        .complete(function () {
          setTimeout(getAlarmInfo, _TASKINTERVAL);
        });
    }
    getAlarmInfo();

  }

  //设置告警信息
  var setAlarmInfo = function (d) {
    var d = JSON.parse(d);
    $('.badgemark').remove();
    //初始化告警提示
    $('#alarmtask').html(d.alarm.task.error + d.alarm.task.warn);
    $('#alarmsystem').html(d.alarm.system.error + d.alarm.system.warn);

    var total = d.alarm.task.error + d.alarm.task.warn + d.alarm.system.error + d.alarm.system.warn;
    var errorTotal = d.alarm.task.error + d.alarm.system.error;
    var badgeType = "badge-warning";
    var tips = "";
    if (errorTotal > 0) {
      badgeType = "badge-danger";
    }
    if (total > 0) {
      var tips = '<span class="badge ' + badgeType + ' badgemark">' + total + '</span>';
    }
    $('#alarmtotal').find('span').remove();
    $('#alarmtotal').append(tips);

    setAlarmLabelIconClour(".alarmhreftask", d.alarm.task);
    setAlarmLabelIconClour(".alarmhrefsystem", d.alarm.system);


    //初始化任务提示
    $('#topcurrenttask').html(d.task.current);
    $('#tophistorytask').html(d.task.history);

    //初始化菜单提示
    var storage_manager = $('.page-sidebar-menu').find('a[name=storage_manager]');
    var authorization_module = $('.page-sidebar-menu').find('a[name=authorization_module]');
    var resource_manager = $('.page-sidebar-menu').find('a[name=resmanagement]');
    var system_manager = $('.page-sidebar-menu').find('a[name=sysmanagement]');

    if (d.storage) {
      //管理备份存储
      var tips = '<span class="badge badge-warning badgemark">' + d.storage + '</span>';
      storage_manager.append(tips);
      //父级-系统管理
      resource_manager.append(tips);
    }
    if (d.lisence) {
      //系统授权
      var tips = '<span class="badge badge-danger badgemark">' + d.lisence + '</span>';
      authorization_module.append(tips);
      //父级-系统管理
      system_manager.append(tips);
    }

  }
  /**
 * 设置顶部告警ICON的样式
 * 无告警:	label-info
 * 有警告告警:	label-warning
 * 有错误告警:	label-danger
 */
  var setAlarmLabelIconClour = function (id, data) {
    var clourSytel = "label-info";
    if (data.warn > 0) {
      clourSytel = "label-warning";
    }
    if (data.error > 0) {
      clourSytel = "label-danger";
    }
    $(id).find('.label-icon').removeClass().addClass("label label-sm label-icon " + clourSytel);

  }
  return {
    //main function to initiate the module
    init: function () {
      initListener();
      initUserType();
      initNodeUUID();//初始化节点信息 
      initCurrentTable();//初始化当前任务表格
      initHistoryTable();//初始化历史任务表格
      initAlarmTips();
      History.pushState({ url: "./content/platform/databackup_center.php" }, document.title, "?homepage");


    },
    //定义更新告警信息接口,添加虚拟化中心成功,添加存储成功后调用
    updateTopAlarmTips: function () {
      updateAlarmTips();
    }
  };
}();

jQuery(document).ready(function () {
  DataBackupCenter.init();
});
