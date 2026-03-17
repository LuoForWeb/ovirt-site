<?php

namespace app\v1\virus\v0\logic;

use app\v1\common\logic\Base;
use app\v1\opcode\ToolOpcode;

class Virus extends Base
{
    private $toolOpcode;

    public function __construct()
    {
        parent::__construct();
        $this->toolOpcode = new ToolOpcode();
    }

    /**
     * 根据功能授权获取允许的病毒类型
     * @return array
     */
    private function getAllowVirusTypeByFunctionLicense(): array
    {
        $allVirusType = xphp_get_config('virus', 'VIRUS_TYPE', 'virus');
        $allowVirusTypeList = [];
        $funcLicense = v1_license_get_func('f', 1);
        if (in_array('virusKill', $funcLicense)) {  // CLAMAV
            $allowVirusTypeList[] = $allVirusType['CLAMAV'];
        }
        if (in_array('virusKillKaspersky', $funcLicense)) {  // 卡巴斯基
            $allowVirusTypeList[] = $allVirusType['KAV'];
        }
        return $allowVirusTypeList;
    }

    /**
     * 获取病毒库列表
     * @param array $params 数组
     * @return array 镜像管理列表
     */
    public function getVirusList($params)
    {

        $sortFields = [
            'type' => 'type',
            'verison' => 'verison',
            'register_time' => 'register_time',
            'last_update_time' => 'last_update_time',
            'authorized_flag' => 'authorized_flag',
            'apply_status' => 'apply_status',
            'authorized_expire_time' => 'authorized_expire_time',
        ];
        $offset = ((int)$params['offset']) ?: 0;
        $limit = ((int)$params['limit']) ?: 10;
        $search = $params['name'];
        $sort = $sortFields[$params['sort']] ?? 'register_time';
        $order = $params['order'] ?: 'desc';
        $sql = "select distinct uuid, name, type, vendor, version, register_time,
                last_update_time, authorized_flag, description, apply_status, details, authorized_expire_time
                from bd_virus_library
                WHERE 1=1 ";
        $sqlParams = array();
        $sqlCountParams = array();
        // 按名字搜索
        if ($this->checkEmpty($search)) {
            $sql .= ' AND name like ? ';
            $sqlParams = array_merge($sqlParams, array('%' . $search . '%'));
            $sqlCountParams = array_merge($sqlCountParams, array('%' . $search . '%'));
        }
        if (isset($params['type']) && $this->checkEmpty($params['type'])) {
            $sql .= ' AND type = ? ';
            $sqlParams = array_merge($sqlParams, [$params['type']]);
            $sqlCountParams = array_merge($sqlCountParams, [$params['type']]);
        }

        // 功能授权
        $allowVirusTypeList = $this->getAllowVirusTypeByFunctionLicense();
        $allowVirusTypes = "'" . implode("','", $allowVirusTypeList) . "'";
        $sql .= " AND type IN ($allowVirusTypes) ";

        $sqlCount = $sql;
        $sql .= " ORDER BY $sort $order LIMIT $offset, $limit ";
        $data = $this->dbSelect($sql, $sqlParams);
        $countData = $this->dbSelect($sqlCount, $sqlCountParams);
        $allVirusType = xphp_get_config('virus', 'VIRUS_TYPE', 'virus');
        $count = count($countData);
        $rows = array_map(function ($row) use ($allVirusType) {
            $authorizedExpireTime = $row['authorized_expire_time'];
            if ($authorizedExpireTime) {  // 精确到天
                $authorizedTimestamp = strtotime($authorizedExpireTime);
                if ($authorizedTimestamp < 0) {
                    $authorizedExpireTime = '----';
                } else {
                    $authorizedExpireTime = date('Y-m-d', $authorizedTimestamp);
                }
            }
            $opList = [1, 2, 3, 4];  // 1设为默认 2刷新 3更新病毒库 4上传授权文件

            if ($row['type'] == $allVirusType['KAV']) {
                $opList = [1, 2, 3];
            } else if ($row['type'] == $allVirusType['CLAMAV']) {
                $opList = [1, 2, 3];
            }

            return [
                'uuid' => $row['uuid'],
                'name' => $row['name'],
                'type' => $row['type'],
                'vendor' => $row['vendor'],
                'version' => $row['version'],
                'register_time' => $row['register_time'],
                'last_update_time' => $row['last_update_time'],
                'authorized_flag' => $row['authorized_flag'],
                'description' => $row['description'] ?: '',
                'apply_status' => $row['apply_status'],
                'detail' => $row['detail'] ?: '',
                'authorized_expire_time' => $authorizedExpireTime,
                'op_list' => $opList,
            ];
        }, $data);

        return array(
            'total' => $count,
            'rows' => $rows
        );
    }

    /**
     * 获取病毒库厂商
     * @param array $params 数组
     * @return array 镜像管理列表
     */
    public function getVirus($params)
    {


        $sql = "SELECT count(type) as count, name, vendor, version, type,
                    last_update_time, IF( apply_status = 1, 1, 0 ) as apply_status
                FROM bd_virus_library  
                WHERE 1=1 ";
        $sqlParams = array();

        // 功能授权
        $allowVirusTypeList = $this->getAllowVirusTypeByFunctionLicense();
        $allowVirusTypes = "'" . implode("','", $allowVirusTypeList) . "'";
        $sql .= " AND type IN ($allowVirusTypes) ";

        $sql .= "GROUP BY vendor ORDER BY apply_status DESC ";

        $data = $this->dbSelect($sql, $sqlParams);
        $rows = array_map(function ($row) {
            return [
                'count' => $row['count'],
                'name' => $row['name'],
                'vendor' => $row['vendor'],
                'version' => $row['version'],
                'last_update_time' => $row['last_update_time'],
                'apply_status' => $row['apply_status'],
                'type' => $row['type'],
            ];
        }, $data);

        return array(
            'rows' => $rows
        );
    }



    /**
     * 更新病毒库
     * @param array $params 数组
     * @return array
     */
    public function updateVirus(array $params): array
    {
        $opcodeName = 'TOOL_VIRUS_OP_OFFLINE_UPDATE_VIRUS_LIB';
        $operate = $this->toolOpcode->getOpcodeDes($opcodeName);

        $sql = "SELECT name FROM bd_virus_library WHERE type = ? ";
        $virusData = $this->dbSelect($sql, [$params['lib_type']]);
        $descriptionParams = [$virusData[0]['name']];

        $mbResult = $this->service()->updateVirusService($params);
        $result = $mbResult['result'];
        if (!$result) {
            $this->systemLog('VIRUS_SYSTEMLOG_DESC_KEY_UPGRADE_VIRUS', $descriptionParams, xphp_get_config('log', 'LOGLEVEL')['ERROR'], $mbResult['errorCode']);
            $this->muOpResult(false, $operate, $mbResult['errorMsg'], 0, $mbResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_TOOL_VIRUS_OP_OFFLINE_UPDATE_VIRUS_LIB'), false, 0);
        }
        $this->systemLog('VIRUS_SYSTEMLOG_DESC_KEY_UPGRADE_VIRUS', $descriptionParams);
        return $this->sendResult(xphp_get_lang('WEB_TOOL_VIRUS_OP_OFFLINE_UPDATE_VIRUS_LIB'), true, 200);
    }

    /**
     * 病毒库操作
     * @param array params
     * @return string
     */
    public function applyVirusList($params): array
    {

        $opcodeName = 'TOOL_VIRUS_OP_MANAGE_VIRUS_LIB';
        $operate = $this->toolOpcode->getOpcodeDes($opcodeName);
        $descriptionKey = 'VIRUS_SYSTEMLOG_DESC_KEY_SET_DEFAULT_VIRUS';
        if (xphp_get_config('virus', 'VIRUS_OP_TYPE', 'virus')['REFRESH'] == $params['manage_virus_lib_op']) {
            $descriptionKey = 'VIRUS_SYSTEMLOG_DESC_KEY_REFRESH_VIRUS';
        }

        $sql = "SELECT name FROM bd_virus_library WHERE type = ? ";
        $virusData = $this->dbSelect($sql, [$params['lib_type']]);
        $descriptionParams = [$virusData[0]['name']];

        $mbResult = $this->service()->applyVirusService($params);
        $result = $mbResult['result'];
        if (!$result) {
            $this->systemLog($descriptionKey, $descriptionParams, xphp_get_config('log', 'LOGLEVEL')['ERROR'], $mbResult['errorCode']);
            $this->muOpResult(false, $operate, $mbResult['errorMsg'], 0, $mbResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_TOOL_VIRUS_OP_MANAGE_VIRUS_LIB'), false, 0);
        }
        $this->systemLog($descriptionKey, $descriptionParams);
        return $this->sendResult(xphp_get_lang('WEB_TOOL_VIRUS_OP_MANAGE_VIRUS_LIB'), true, 200);
    }
    /**
     * 上傳病毒库操作
     * @param array params
     * @return string
     */
    public function getVirusFile($params = []): array
    {
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
            $fileName = $_FILES['files']['name'];
        } else {
            $fileName = uniqid('file_');
        }

        $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;
        $tmpPath = xphp_get_config('app', 'VIRUS_UPLOAD_DIR');
        $this->deleteDir($tmpPath);  // 清除之前的目录，保证每次只有一个上传
        mkdir($tmpPath, 0777, true);  // umask 022, 因此创建的目录是755
        chmod($tmpPath, 0777);
        $uploadPath = $tmpPath . '/' . $_FILES['files']['name'];
        // Chunking might be enabled
        $chunk = isset($_REQUEST['dzchunkindex']) ? intval($_REQUEST['dzchunkindex']) : 0;
        $chunks = isset($_REQUEST['dztotalchunkcount']) ? intval($_REQUEST['dztotalchunkcount']) : 1;
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
            if ($_FILES['files']['error'] || !is_uploaded_file($_FILES['files']['tmp_name'])) {
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
            if (!$in = @fopen($_FILES['files']['tmp_name'], 'rb')) {
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
        if ($done) {  // 上传完成合并分片
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

            $sql = "SELECT name FROM bd_virus_library WHERE type = ? ";
            $virusData = $this->dbSelect($sql, [$_POST['lib_type']]);
            $descriptionParams = [
                $virusData[0]['name'],
                $fileName,
            ];

            $this->systemLog('VIRUS_SYSTEMLOG_DESC_KEY_UPLOAD_VIRUS', $descriptionParams);
        }
//        move_uploaded_file($file['tmp_name'], $uploadPath);

        return $this->sendResult('', true, 200, [
            'name' => $_FILES['files']['name'],
            'path' => $tmpPath,
        ]);
    }
    /**
     * 删除目录
     * @param string $dirPath 目录
     * @return void
     */
    private function deleteDir(string $dirPath)
    {
        if (!is_dir($dirPath)) {
            return;
        }
        $dirHandle = opendir($dirPath);
        while (false !== ($file = readdir($dirHandle))) {
            if ($file != '.' && $file != '..') {
                $filePath = $dirPath . '/' . $file;
                if (is_dir($filePath)) {
                    $this->deleteDir($filePath);
                } else {
                    unlink($filePath);
                }
            }
        }
        closedir($dirHandle);
        rmdir($dirPath);
    }

    /**
     * 上传病毒库授权文件
     * @param array $params 参数
     * @return array
     */
    public function uploadAuthorizationFile(array $params): array
    {
        $virusAuthorizationFileUploadPath = $params['key_file_upload_path'] . '/' . $params['key_file_name'];
        if (!is_file($virusAuthorizationFileUploadPath)) {
            return $this->sendResult(sprintf(xphp_get_lang('WEB_TOOL_VIRUS_OP_AUTHORIZATION_FILE_NOT_EXISTS'), $virusAuthorizationFileUploadPath), false, 0);
        }
        $uploadRet = $this->service()->uploadAuthorizationFileService(
            $params['key_file_upload_path'],
            $params['key_file_name'],
            intval($params['lib_type'])
        );
        $opcodeName = 'TOOL_VIRUS_OP_UPLOAD_AUTHORIZATION_FILE';
        $operate = $this->toolOpcode->getOpcodeDes($opcodeName);
        if (!$uploadRet['result']) {
            $this->muOpResult(false, $operate, $uploadRet['errorMsg'], 0, $uploadRet['errorCode']);
            return $this->sendResult('', false, 0);
        }
        return $this->sendResult('');
    }
}