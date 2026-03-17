<?php

namespace app\v1\system\v0\logic;

use app\v1\common\logic\Base;
use app\v1\resources\v0\logic\Node;

/**
 * 系统配置 - 系统工具 logic
 */
class Tool extends Base
{
    /**
     * 获取各节点系统服务列表
     * @param array $params 参数
     * @return array
     */
    public function getServiceList(array $params): array
    {
        $nodeuuid = $params['node_uuid'];
        $start = intval($params['offset']);
        $length = intval($params['limit']);
        $name = $params['search_value'];
        //获取service列表
        $cmd = "systemctl list-unit-files |grep -E 'enabled|disabled' ";
        if (!empty($name)) {
            if ($name == 'ssh') {
                $cmd = 'systemctl -a list-units --type=service ';
            }

            $cmd .= '|grep ' . $name . ' ';
        }
        $cmd .= "|sort |sed -n '" . ($start + 1) . ',' . ($start + $length) . "p'";
        $opName = 'NODE_SYS_OP_DO_CMD_WITH_DETAIL';
        $msg = array('command' => $cmd);
        if (!$nodeuuid) {
            $nodeuuid = Node::instance()->getMasterNodeUuid();
        }
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true, false);
        $records = array();
        $records['rows'] = array();
        $id = $start * 1 + 1;
        $count = 0;

        if ($mbResult['result']) {
            $details = explode("\n", trim($mbResult['msg']['detail']));
            $count = count($details);
            //如果有服务或搜索到服务
            if (!empty($details[0])) {
                foreach ($details as $key => $value) {
                    $info = trim($value);
                    $info = explode(' ', $info);
                    $info = array_filter($info);
                    if ($info[1] == 'not-found' || $info[3] == 'dead') {
                        continue;    //不显示未找到的或者已经死掉的服务
                    }
                    $newInfo = array();
                    foreach ($info as $i) {
                        $newInfo[] = $i;
                    }
                    $status = $this->getServiceStatus($newInfo[0], $nodeuuid);
                    $records['rows'][] = array(
                        'no' => $id++,
                        'name' => $newInfo[0],
                        'status_des' => $this->getServiceStatusDes($status),
                        'operations' => $status == 0 ? [2, 3] : [1, 3], // 获取服务对应操作 1开启 2关闭 3重启
                        'status' => $status
                    );
                }
            } else {
                $count = 0;
            }
        }

        //计算可用服务总数
        $cmd = "systemctl list-unit-files |grep -E 'enabled|disabled' ";
        if (!empty($name)) {
            $cmd .= '|grep ' . $name . ' ';
        }
        $opName = 'NODE_SYS_OP_DO_CMD_WITH_DETAIL';
        $msg = array('command' => $cmd);
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true, false);
        if ($mbResult['result']) {
            $details = explode("\n", trim($mbResult['msg']['detail']));
            $count = count($details);
            if (empty($details[0])) {
                $count = 0;
            }
        }
        $records['total'] = $count;

        return $records;
    }

    /**
     * 系统服务管理
     * @param array $params 参数
     * @return array
     */
    public function operateService($params = [])
    {
        $nodeuuid = $params['node_uuid'];
        $name = str_replace(' ', '', $params['name']);

        $serviceArr = [
            1 => ['lang' => 'UI_SETTINGS_SERVICE_START', 'cmd' => 'start'], // 启动
            2 => ['lang' => 'UI_SETTINGS_SERVICE_STOP', 'cmd' => 'stop'], // 停止
            3 => ['lang' => 'UI_SETTINGS_SERVICE_RESTART', 'cmd' => 'restart'], // 重启
        ];

        $opName = 'NODE_SYS_OP_DO_CMD_WITH_DETAIL';
        $service = $serviceArr[intval($params['operate'])];
        $cmd = $service['cmd'];
        $msg = array('command' => 'systemctl ' . $cmd . ' ' . $name);

        $mbResult = $this->service()->mbNodeMsgs($opName, $nodeuuid, $msg, true, false);
        $msg = xphp_get_lang($service['lang']);
        if ($mbResult['result']) {
            return [
                'code' => 0,
                'msg' => $msg
            ];
        } else {
            return $this->muOpResult(false, $msg, '', 'warning', $mbResult['errorCode']);
        }
    }

    /**
    * 检测是否是有效的 域名或ip
     * @param string $input 输入
     * @return boolean
     */
    private function isValidHost(string $input)
    {
        // 尝试解析URL以获取主机部分
        $parsedUrl = parse_url($input);
        $domainPattern = '/^(?:[a-zA-Z0-9](?:[a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}$/';
        // 检查是否成功解析了URL，并且存在主机部分
        if ($parsedUrl && isset($parsedUrl['host'])) {
            $host = $parsedUrl['host'];

            // 检查是否为纯IP地址（IPv4或IPv6）
            if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) || filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                return true; // 是有效的IP地址
            }

            // 检查是否为有效的域名（这里使用简单的检查，可以根据需要替换为更复杂的检查）
            // 注意：这个正则表达式可能需要根据你的具体需求进行调整
            if (preg_match($domainPattern, $host)) {
                return true; // 是有效的域名
            }

            // 如果到这里还没有返回true，说明主机部分既不是IP地址也不是有效的域名格式
            return false;
        }

        // 如果输入的不是一个URL（即没有协议部分），我们直接尝试验证它作为域名或IP
        if (filter_var($input, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) || filter_var($input, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return true; // 是有效的IP地址
        }

        // 使用与上面相同的正则表达式来验证纯域名
        if (preg_match($domainPattern, $input)) {
            return true; // 是有效的域名
        }

        // 如果所有检查都失败，返回false
        return false;
    }

    /**
     * 测试网络连接
     * @param array $params 参数
     * @return json
     */
    public function testConnectTool($params = [])
    {

        $ip = $params['ip'];
        if (!$this->isValidHost($ip)) {
            return [
                'code' => 1,
                'msg' => xphp_get_lang('UI_SETTINGS_TOOL_IP_OR_DOMAIN_ERROR')
            ];
        }

        // 判断下当前的 IPADDR 是否是ipv6
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            // 是 ipv6手动更改 tooltype 的值为ping6或者telnet6
            $params['tool_type'] = $params['tool_type'] . 6;
        }
        $port = $params['port'];
        $type = $params['tool_type'];
        $nodeuuid = $params['node_uuid'];
        $result = true;
        $opName = '';
        if (in_array($type, ['ping', 'ping6'])) {
            $opName = 'NODE_SYS_OP_DO_CMD_WITH_DETAIL';
            $cmd = $type . ' -c 1 ' . $ip . ' -W 3 2>&1>/dev/null;echo $?';
            $msg = array('command' => $cmd);
            $mbResult = $this->service()->mbNodeMsgs($opName, $nodeuuid, $msg, true, false);
            $result = trim($mbResult['msg']['detail']) == '0';
            $opName = $type . ' ' . $ip;
        } elseif (in_array($type, ['telnet', 'telnet6'])) {
            //socket连接
            if ($type == 'telnet') {
                $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            } else {
                $socket = socket_create(AF_INET6, SOCK_STREAM, SOL_TCP);
            }

            if ($socket === false) {
                return [
                    'code' => 1,
                    'msg' => xphp_get_lang('UI_SETTINGS_TOOL_TEST_SOCKET_ERROR')
                ];
            } else {
                socket_set_block($socket);
                socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => 1, 'usec' => 0 ));
                socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, array('sec' => 3, 'usec' => 0 ));
            }
            $result = socket_connect($socket, $ip, $port);
            socket_close($socket);
            $opName = xphp_get_lang('UI_VCENTER_ENGINE_BACKUP_TEST_CONNECT').':';
            if($result){
                $opName .= $type . ' ' . $ip . ' : ' . $port.xphp_get_lang('UI_VCENTER_ENGINE_BACKUP_TEST_CONNECT_SUCCESS');
            }else{
                $opName .= $type . ' ' . $ip . ' : ' . $port.xphp_get_lang('UI_VCENTER_ENGINE_BACKUP_TEST_CONNECT_FAIL');
            }
        }
        return [
            'code' => $result ? 0 : 1,
            'msg' => $opName
        ];
    }

    /**
     * 上传文件到指定目录
     * @param array $params 参数
     * @return json
     */
    public function uploadToSystem($params = [])
    {
        //检查文件名，主要是检查后缀
        $checkFileExt = preg_match('/^.*\.(zip|tar|tar.gz|rar)$/i', $_FILES['file']['name']);
        if (0 === $checkFileExt) {
            return [
                'code' => 1,
                'msg' => xphp_get_lang('UIS_SETTINGS_UPLOAD_HINT_2')
            ];
        }

        // 5 minutes execution time
        @set_time_limit(5 * 60);

        // Settings
        // $targetDir = ini_get("upload_tmp_dir") . DIRECTORY_SEPARATOR . "plupload";
        $uploadDir = xphp_get_config('app', 'UPLOAD_DIR');
        $targetDir = xphp_get_config('app', 'TARGET_DIR');

        $cleanupTargetDir = true; // Remove old files
        $maxFileAge = 5 * 3600; // Temp file age in seconds

        //      @file_put_contents('./a.txt',print_r($_FILES,true),FILE_APPEND); //上传文件写入日志到a.txt

        // Get a file name
        if (isset($params['name'])) {
            $fileName = $params['name'];
        } elseif (!empty($_FILES)) {
            $fileName = $_FILES['file']['name'];
        } else {
            $fileName = uniqid('file_');
        }

        $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;
        $uploadPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;
        // Chunking might be enabled
        $chunk = isset($_REQUEST['chunk']) ? intval($_REQUEST['chunk']) : 0;
        $chunks = isset($_REQUEST['chunks']) ? intval($_REQUEST['chunks']) : 1;
        //         var_dump($_FILES);

        // Remove old temp files
        if ($cleanupTargetDir) {
            if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
                die(json_encode([
                        'jsonrpc' => '2.0',
                        'error' => [
                            'code' => 100,
                            'message' => 'Failed to open temp directory.',
                        ],
                        'id' => 'id'
                    ]));
            }

            while (($file = readdir($dir)) !== false) {
                $tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;

                // If temp file is current file proceed to the next
                if ($tmpfilePath == "{$filePath}_{$chunk}.part" || $tmpfilePath == "{$filePath}_{$chunk}.parttmp") {
                    continue;
                }

                // Remove temp file if it is older than the max age and is not the current file
                if (preg_match('/\.(part|parttmp)$/', $file) && (@filemtime($tmpfilePath) < time() - $maxFileAge)) {
                    @unlink($tmpfilePath);
                }
            }
            closedir($dir);
        }


        // Open temp file
        if (!$out = @fopen("{$filePath}_{$chunk}.parttmp", 'wb')) {
            die(json_encode([
                'jsonrpc' => '2.0',
                'error' => [
                    'code' => 102,
                    'message' => 'Failed to open output stream.',
                ],
                'id' => 'id'
            ]));
        }

        if (!empty($_FILES)) {
            if ($_FILES['file']['error'] || !is_uploaded_file($_FILES['file']['tmp_name'])) {
                die(json_encode([
                    'jsonrpc' => '2.0',
                    'error' => [
                        'code' => 103,
                        'message' => 'Failed to move uploaded file.',
                    ],
                    'id' => 'id'
                ]));
            }

            // Read binary input stream and append it to temp file
            if (!$in = @fopen($_FILES['file']['tmp_name'], 'rb')) {
                die(json_encode([
                    'jsonrpc' => '2.0',
                    'error' => [
                        'code' => 101,
                        'message' => 'Failed to open input stream.',
                    ],
                    'id' => 'id'
                ]));
            }
        } else {
            if (!$in = @fopen('php://input', 'rb')) {
                die(json_encode([
                    'jsonrpc' => '2.0',
                    'error' => [
                        'code' => 101,
                        'message' => 'Failed to open input stream.',
                    ],
                    'id' => 'id'
                ]));
            }
        }
        while ($buff = fread($in, 4096)) {
            fwrite($out, $buff);
        }

        @fclose($out);
        @fclose($in);

        rename("{$filePath}_{$chunk}.parttmp", "{$filePath}_{$chunk}.part");

        $done = true;
        for ($index = 0; $index < $chunks; $index++) {
            if (!file_exists("{$filePath}_{$index}.part")) {
                $done = false;
                break;
            }
        }
        if ($done) {
            if (!$out = @fopen($uploadPath, 'wb')) {
                die(json_encode([
                    'jsonrpc' => '2.0',
                    'error' => [
                        'code' => 102,
                        'message' => 'Failed to open output stream.',
                    ],
                    'id' => 'id'
                ]));
            }

            if (flock($out, LOCK_EX)) {
                for ($index = 0; $index < $chunks; $index++) {
                    if (!$in = @fopen("{$filePath}_{$index}.part", 'rb')) {
                        break;
                    }

                    while ($buff = fread($in, 4096)) {
                        fwrite($out, $buff);
                    }

                    @fclose($in);
                    @unlink("{$filePath}_{$index}.part");
                }

                flock($out, LOCK_UN);
            }
            @fclose($out);
        }
        die($fileName);
    }

    /**
     * 删除临时文件
     * @param array $params 参数
     * @return void
     */
    public function delTempFile($params = [])
    {

        $targetDir = xphp_get_config('app', 'TARGET_DIR');
        if (file_exists($targetDir)) {
            $cmd = 'rm -rf ' . $targetDir . '/*';
            shell_exec($cmd);
        }
    }

    /**
     * 获取服务运行状态 / safe 逻辑有调用
     * @param string $name     服务名称
     * @param string $nodeuuid 所属节点
     * @return int
     */
    public function getServiceStatus(string $name, string $nodeuuid): int
    {
        // 整形的那么就是端口
        if ($name == '' . intval($name) && intval($name)) {
            // $cmd = "firewall-cmd --permanent --list-ports | grep " . $name . "/tcp 2>&1 >/dev/null;echo $?";
            $cmd = "systemctl status firewalld  2>&1 >/dev/null && grep -E $name /etc/firewalld/zones/public.xml | grep tcp 2>&1 >/dev/null;echo $?";
        } else {
            $cmd = 'systemctl status ' . $name . ' 2>&1 >/dev/null;echo $?';
        }

        $opName = 'NODE_SYS_OP_DO_CMD_WITH_DETAIL';
        $msg = array('command' => $cmd);
        $mbResult = $this->service()->mbNodeMsgs($opName, $nodeuuid, $msg, true, false);
        if ($mbResult['result']) {
            return intval($mbResult['msg']['detail']);
        }
        return 0;
    }

    /**
     * 获取状态描述
     * @param int $status 状态值
     * @return string
     */
    private function getServiceStatusDes(int $status): string
    {
        return $status === 0 ? xphp_get_lang('WEB_PLATFORM_DES_RUNNING')
            : xphp_get_lang('WEB_PLATFORM_DES_STOP');
    }
}
