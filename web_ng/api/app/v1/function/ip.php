<?php

/**
 * v1 版本 IP操作公共函数
 */

/**
 * 检查IP地址是否可达（支持IPv4和IPv6）
 * @param string $ip        要检查的IP地址
 * @param int    $timeout   超时时间（秒），默认为3秒
 * @param int    $retries   重试次数，默认为3次
 * @param string $interface 用于Ping的源IP或网口
 * @return bool true表示可达，false表示不可达
 */
function v1_check_ip_exists(string $ip, int $timeout = 1, int $retries = 3, string $interface = ''): bool
{
    // 验证输入是否是有效的IP地址
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return false;
    }

    // 根据IP版本决定ping命令参数
    $isIPv6 = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);

    /**
     * 构建ping命令
     *  -6 表示对ipv6进行操作
     *  -c 表示发送n个ping请求
     *  -W 表示等待n秒的响应时间
     *  escapeshellarg 函数用于安全地将字符串传递给 shell 命令，防注入
     */
    $command = 'ping ';
    // 设置ping的源IP
    if ($interface) {
        $command .= " -I $interface ";
    }
    // IPv6加-6
    if ($isIPv6) {
        $command .= ' -6 ';
    }
    $command .= " -c $retries -W $timeout " . escapeshellarg($ip) . " 2>&1";

    // 执行ping命令
    exec($command, $output, $returnCode);

    // 检查返回状态和输出
    if ($returnCode === 0) {
        // 检查输出中是否有TTL或time=字样（不同系统ping输出可能不同）
        foreach ($output as $line) {
            // 这里检查到有只要一个有TTL或time=字样的行就认为是可达的
            if (
                strpos($line, 'ttl=') !== false ||
                strpos($line, 'TTL=') !== false ||
                strpos($line, 'time=') !== false
            ) {
                return true;
            }
        }
    }

    return false;
}
