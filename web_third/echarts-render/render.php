<?php
// render.php

$option = [
    'title' => ['text' => '用户访问来源'],
    'tooltip' => ['trigger' => 'item'],
    'legend' => ['orient' => 'vertical', 'left' => 'left'],
    'series' => [
        [
            'name' => '访问来源',
            'type' => 'pie',
            'radius' => '50%',
            'data' => [
                ['value' => 1048, 'name' => '搜索引擎'],
                ['value' => 735, 'name' => '直接访问'],
                ['value' => 580, 'name' => '邮件营销'],
                ['value' => 484, 'name' => '联盟广告'],
                ['value' => 300, 'name' => '视频广告']
            ]
        ]
    ]
];

$uniq = uniqid('chart_');
$configFile = "/tmp/{$uniq}.json";
$imgFile = __DIR__ . "/output/{$uniq}.png";

// 保存配置为 JSON
file_put_contents($configFile, json_encode($option, JSON_UNESCAPED_UNICODE));

// 调用新脚本
$cmd = sprintf(
    'cd %s && /usr/bin/node render-echart.js %s %s 2>&1',
    escapeshellarg(__DIR__),
    escapeshellarg($configFile),
    escapeshellarg($imgFile)
);

exec($cmd, $output, $returnCode);

// 清理临时文件
unlink($configFile);

if ($returnCode === 0 && file_exists($imgFile)) {
    echo "✅ 图片已生成：{$imgFile}\n";
} else {
    echo "❌ 失败！\n";
    echo implode("\n", $output);
}