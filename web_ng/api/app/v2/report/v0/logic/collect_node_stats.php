<?php
/*
 * @Author: ChengJiaFu
 * @Date: 2025-12-17 15:16:41
 * @Description: 收集节点资源脚本，每小时执行一次，负责从实时监控表 bd_node_monitor 提取所有在线节点的资源使用率，并存入 bd_node_usage_history 表
 * @version: 1.0
 */

// ---- 数据库连接 ----
try {
    $pdo = new PDO('mysql:host=192.168.45.200;dbname=vinchin_db;charset=utf8mb4', 'vinchin', 'Database@2015');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("数据库连接失败: " . $e->getMessage());
    exit(1); // 致命错误，退出
}

try {
    // 1. 从实时监控表获取所有在线节点的当前使用率
    $stmt_select = $pdo->query("SELECT node_uuid, cpu_usage_rate, memory_usage_rate FROM bd_node_monitor");
    $nodes_data = $stmt_select->fetchAll(PDO::FETCH_ASSOC);

    if (empty($nodes_data)) {
        echo "没有找到在线节点，无需记录。\n";
        exit(0);
    }

    // 2. 准备批量插入的 SQL 语句
    $sql_insert = "INSERT INTO bd_node_usage_history (node_uuid, cpu_usage_rate, memory_usage_rate, record_time) VALUES (:node_uuid, :cpu_usage_rate, :memory_usage_rate, :record_time)";
    $stmt_insert = $pdo->prepare($sql_insert);

    $current_time = date('Y-m-d H:i:s');
    $records_inserted = 0;

    // 3. 遍历所有节点并插入历史记录
    $pdo->beginTransaction(); // 开始事务，提高批量插入性能
    foreach ($nodes_data as $node) {
        $stmt_insert->execute([
            ':node_uuid' => $node['node_uuid'],
            ':cpu_usage_rate' => $node['cpu_usage_rate'],
            ':memory_usage_rate' => $node['memory_usage_rate'],
            ':record_time' => $current_time
        ]);
        $records_inserted++;
    }
    $pdo->commit(); // 提交事务

    echo "在 {$current_time} 成功插入 {$records_inserted} 条记录。\n";

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // 记录执行过程中发生的任何错误
    error_log("节点状态收集中发生错误: " . $e->getMessage());
    exit(1);
}

?>