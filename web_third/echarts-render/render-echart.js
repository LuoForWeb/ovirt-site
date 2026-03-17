// render-echart.js
const fs = require('fs');
const echarts = require('echarts');
const { createCanvas } = require('canvas');

// 从命令行读取配置文件路径和输出路径
const [configPath, outputPath] = process.argv.slice(2);

if (!configPath || !outputPath) {
  console.error('Usage: node render-echart.js <config.json> <output.png>');
  process.exit(1);
}

// 读取 ECharts 配置（JSON 文件）
const option = JSON.parse(fs.readFileSync(configPath, 'utf8'));

// 创建 canvas（800x600）
const width = 800;
const height = 600;
const canvas = createCanvas(width, height);
const ctx = canvas.getContext('2d');

// 初始化 ECharts（虚拟 DOM 模式）
const chart = echarts.init(canvas, null, {
  renderer: 'canvas',
  width: width,
  height: height,
  devicePixelRatio: 1
});

// 渲染图表
chart.setOption(option);
chart.resize();

// 导出 PNG
const buffer = canvas.toBuffer('image/png');
fs.writeFileSync(outputPath, buffer);

console.log('✅ ECharts image saved to:', outputPath);