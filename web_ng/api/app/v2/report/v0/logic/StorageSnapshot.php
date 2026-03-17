<?php
/**
 * @FileName: StorageSnapshot.php
 * @Description: 定时采集脚本 - 获取并记录每个存储资源的每日已用空间快照。
 * @Author: Gemini
 * @Date: 2023-10-28
 * @Usage: 通过定时任务每日执行一次。
 */

// --- 脚本配置 ---
// 数据库连接信息
const DB_HOST = '192.168.45.200';
const DB_NAME = 'vinchin_db';
const DB_USER = 'vinchin';
const DB_PASS = 'Database@2015';

// 数据表名
const SOURCE_TABLE = 'bd_storage_resource';
const HISTORY_TABLE = 'bd_storage_usage_history';

// --- 脚本执行 ---
echo "==================================================\n";
echo "Starting Storage Usage Snapshot Task...\n";
echo "Task Run Time: " . date('Y-m-d H:i:s') . "\n";
echo "--------------------------------------------------\n";

$pdo = null;
try {
    // 1. 建立数据库连接
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Database connection successful.\n";

    // 2. 从源表获取所有在线存储资源
    $sqlSelect = "SELECT storage_uuid, total_size, free_size FROM " . SOURCE_TABLE . " WHERE status = 1";
    $stmtSelect = $pdo->query($sqlSelect);
    $storages = $stmtSelect->fetchAll(PDO::FETCH_ASSOC);

    if (empty($storages)) {
        echo "Notice: No online storage resources found. Exiting.\n";
        exit;
    }
    echo "Found " . count($storages) . " online storage(s) to process.\n";

    $today = date('Y-m-d');

    // 3. 准备SQL语句 (预处理)
    $sqlCheck = "SELECT COUNT(*) FROM " . HISTORY_TABLE . " WHERE storage_uuid = :storage_uuid AND record_date = :record_date";
    $sqlInsert = "INSERT INTO " . HISTORY_TABLE . " (storage_uuid, used_size, record_date) VALUES (:storage_uuid, :used_size, :record_date)";
    $sqlUpdate = "UPDATE " . HISTORY_TABLE . " SET used_size = :used_size WHERE storage_uuid = :storage_uuid AND record_date = :record_date";

    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtInsert = $pdo->prepare($sqlInsert);
    $stmtUpdate = $pdo->prepare($sqlUpdate);

    // 4. 遍历每个存储资源，执行更新或插入
    $pdo->beginTransaction();
    foreach ($storages as $storage) {
        $storageUuid = $storage['storage_uuid'];
        $usedSize = $storage['total_size'] - $storage['free_size'];

        // 检查当天是否已有记录
        $stmtCheck->execute([
            ':storage_uuid' => $storageUuid,
            ':record_date' => $today
        ]);
        $exists = $stmtCheck->fetchColumn() > 0;

        if ($exists) {
            // 如果存在，则更新
            $stmtUpdate->execute([
                ':used_size' => $usedSize,
                ':storage_uuid' => $storageUuid,
                ':record_date' => $today
            ]);
            echo "  -> Updated snapshot for storage UUID: {$storageUuid}\n";
        } else {
            // 如果不存在，则插入
            $stmtInsert->execute([
                ':storage_uuid' => $storageUuid,
                ':used_size' => $usedSize,
                ':record_date' => $today
            ]);
            echo "  -> Inserted new snapshot for storage UUID: {$storageUuid}\n";
        }
    }
    $pdo->commit();

} catch (Exception $e) {
    if ($pdo && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $error_msg = "ERROR: An exception occurred during processing: " . $e->getMessage() . "\n";
    error_log($error_msg);
    die($error_msg);
} finally {
    $pdo = null;
}

echo "--------------------------------------------------\n";
echo "Storage usage snapshot task completed successfully.\n";
echo "==================================================\n";
?>