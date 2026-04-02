<?php
// phpcs:ignoreFile -- 框架类
/*********************************************************************************
 *  扩展类库-邮件发送类--outlook
 ***********************************************************************************/

namespace xphp;

use League\OAuth2\Client\Provider\GenericProvider;
use xphp\db\Op;

class Outlook
{
    /**
     * 发送邮件（支持多出口 IP 自动切换）
     * @param string    $clientId       应用程序(客户端) ID
     * @param string    $clientSecret   客户端密钥
     * @param string    $tenantId       Azure 租户 ID
     * @param string    $userEmail      发件人邮箱
     * @param array     $recEmail       收件人Email地址(可以多个)
     * @param string    $subject        主题
     * @param string    $body           内容
     * @param int       $back           是否返回错误详情 (0/1)
     * @return array|false
     */
    public function sendmail(string $clientId, string $clientSecret, string $tenantId, string $userEmail, array $recEmail, string $subject, string $body, int $back = 0)
    {
        $baseDir = DATA_PATH;
        $tokenUrl = $baseDir . '/email/refresh_token.txt';
        $refreshToken = @file_get_contents($tokenUrl);
        if (!$refreshToken) {
            $msg = 'not found refresh_token，please run register_outlook.php get token';
            (new Op())->writeLog($msg);
            return $back ? ['code' => -1, 'msg' => $msg, 'error' => $msg, 'info' => $msg] : false;
        }

        // 获取所有主节点出口 IP
        $localIps = $this->getAllBaseUrls();
        if (empty($localIps)) {
            $msg = 'No valid export IPs found for sending email.';
            (new Op())->writeLog($msg);
            return $back ? ['code' => -1, 'msg' => $msg, 'error' => $msg, 'info' => $msg] : false;
        }

        $allErrors = [];
        foreach ($localIps as $localIp) {
            $result = $this->sendViaIp($clientId, $clientSecret, $tenantId, $userEmail, $recEmail, $subject, $body, $refreshToken, $localIp, $tokenUrl, $back);
            if (($back && $result['code'] == 0) || (!$back && !empty($result))) {
                return $result; // 成功立即返回
            }
            if ($back) {
                $allErrors[] = "Via {$localIp}: " . ($result['info'] ?? 'Unknown error');
            }
        }

        // 全部失败
        $finalMsg = "All local export IPs failed to send Outlook email.";
        return $back ? [
            'code' => -1,
            'msg' => $finalMsg,
            'error' => $finalMsg,
            'info' => implode("\n", $allErrors)
        ] : false;
    }

    /**
     * 使用指定 IP 发送邮件（单次尝试）
     */
    protected function sendViaIp(string $clientId, string $clientSecret, string $tenantId, string $userEmail, array $recEmail, string $subject, string $body, string $refreshToken, string $localIp, string $tokenPath, int $back)
    {
        try {
            $redirectUri = $this->buildRedirectUri($localIp);

            $provider = new GenericProvider([
                'clientId'                => $clientId,
                'clientSecret'            => $clientSecret,
                'redirectUri'             => $redirectUri,
                'urlAuthorize'            => "https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/authorize",
                'urlAccessToken'          => "https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/token",
                'urlResourceOwnerDetails' => 'https://graph.microsoft.com/v1.0/me',
            ]);

            // 获取 Access Token
            $token = $provider->getAccessToken('refresh_token', [
                'refresh_token' => $refreshToken,
                'scope' => 'https://graph.microsoft.com/Mail.Send offline_access'
            ]);

            // 仅当 refresh_token 更新时才写入
            $newRefreshToken = $token->getRefreshToken();
            if ($newRefreshToken && $newRefreshToken !== $refreshToken) {
                file_put_contents($tokenPath, $newRefreshToken);
            }

            // 构建邮件数据
            $toRecipients = array_map(fn($email) => [
                "emailAddress" => ["address" => $email]
            ], $recEmail);

            $mailData = [
                "message" => [
                    "subject" => $subject,
                    "body" => [
                        "contentType" => "HTML",
                        "content" => $body
                    ],
                    "toRecipients" => $toRecipients
                ],
                "saveToSentItems" => true
            ];

            // 发送请求
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => "https://graph.microsoft.com/v1.0/users/{$userEmail}/sendMail",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_HTTPHEADER     => [
                    "Authorization: Bearer " . $token->getToken(),
                    "Content-Type: application/json"
                ],
                CURLOPT_POSTFIELDS     => json_encode($mailData),
                CURLOPT_TIMEOUT        => 15,
                CURLOPT_CONNECTTIMEOUT => 10,
                // 关键：绑定出口 IP
                CURLOPT_INTERFACE      => $localIp,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($httpCode >= 200 && $httpCode < 300) {
                return $back ? ['code' => 0, 'msg' => 'success', 'error' => '', 'info' => "Sent successfully via {$localIp}"] : true;
            } else {
                $errorMsg = "HTTP {$httpCode}";
                if ($response) {
                    $json = json_decode($response, true);
                    if (isset($json['error']['message'])) {
                        $errorMsg .= ": " . $json['error']['message'];
                    }
                }
                if ($curlError) {
                    $errorMsg .= " | cURL: " . $curlError;
                }
                return $back ? ['code' => -1, 'msg' => 'error', 'error' => $errorMsg, 'info' => $errorMsg] : false;
            }
        } catch (\Exception $e) {
            return $back ? [
                'code' => -1,
                'msg' => 'exception',
                'error' => "Exception via {$localIp}: " . $e->getMessage(),
                'info' => "Exception via {$localIp}: " . $e->getMessage(),
            ] : false;
        }
    }

    /**
     * 获取所有主节点出口 IP（与 Email 类一致）
     */
    private function getAllBaseUrls(): array
    {
        $ipList = dbSelect('SELECT bnn.ip FROM bd_node bn JOIN bd_node_network bnn ON bn.node_uuid = bnn.node_uuid
                                    WHERE bn.node_type = 1 AND bnn.type = 1 order by bnn.network_order asc');
        $ips = array_column($ipList, 'ip');
        // 过滤有效 IP
        return array_filter($ips, fn($ip) => filter_var($ip, FILTER_VALIDATE_IP));
    }

    /**
     * 根据 IP 构造 redirect_uri（保留协议+IP+端口）
     */
    private function buildRedirectUri(string $ip): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
        $port = $_SERVER['SERVER_PORT'] ?? ($protocol === 'https' ? 443 : 80);

        // 判断是否为 IPv6 地址
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            // IPv6 必须用 [ ] 包裹
            $host = "[{$ip}]";
        } else {
            // IPv4 或主机名
            $host = $ip;
        }

        // 端口部分：省略标准端口
        $portPart = '';
        if (($protocol === 'http' && $port != 80) || ($protocol === 'https' && $port != 443)) {
            $portPart = ":{$port}";
        }

        return "{$protocol}://{$host}{$portPart}/oauth2callback.php";
    }
}