<?php

namespace app\v1\system\v0\logic;

use app\v1\cluster\v0\logic\Cluster;
use app\v1\common\logic\Base;
use app\v1\opcode\NodeOpcode;
use app\v1\opcode\PfOpcode;
use app\v1\resources\v0\logic\Node;
use app\v1\system\v0\service as SystemService;

/**
 * 系统配置 - 系统升级 logic
 */
class Upgrade extends Base
{
    /**
     * 得到补丁包列表
     * @param array $params 参数
     * @return array
     */
    public function getPatches(array $params): array
    {
        $start = $params['offset'];
        $length = $params['limit'];
        $sortColumn = $params['sort'];
        $sortType = $params['order'];
        $sortArr = array('upload_time' => 'upload_time');
        $nodeuuid = $this->getMasterNode();
        $sql = "select uuid, name,level1_md5,upload_time, extra from bd_update_file 
            where node_uuid = ? order by $sortArr[$sortColumn] $sortType limit ? , ? ";
        $sqlCount = "select count(uuid) as total from bd_update_file where node_uuid = ?";

        $data = $this->dbSelect($sql, array($nodeuuid, $start, $length));
        $count = $this->dbSelect($sqlCount, array($nodeuuid));

        $records = array();
        $records["rows"] = array();
        foreach ($data as $d) {
            $fileInfo = json_decode($d['extra'], true);
            $fileSize = $fileInfo['file_size'];
            $fileUpdateInfo = $fileInfo['updateDetails'];
            //得到版本信息
            $versionNum = "5.0.0";
            //得到所有日志
            $log_list = $fileUpdateInfo['info'];
            //得到符合其版本的日志注意事项描述
            $infoLogList = array();
            foreach ($log_list as $each) {
                if ($each['version'] >= $versionNum) {
                    $infoLogList[] = $each;
                }
            }
            $records["rows"][] = array(
                'name' => $d['name'],
                'md5' => $d['level1_md5'],
                'size' => v1_calsize($fileSize, true),
                'upload_time' => $d['upload_time'],
                'log_list' => $infoLogList,
                'uuid' => $d['uuid']
            );
        }

        $records["total"] = $count[0]['total'];
        return $records;
    }

    /**
     * 得到升级历史
     * @param array $params 参数
     * @return array
     */
    public function getPatchHistory(array $params): array
    {
        $start = $params['offset'];
        $length = $params['limit'];
        $sortColumn = $params['sort'];
        $sortType = $params['order'];
        $sortArr = array(
            'log_time' => 'bul.log_time',
            'status' => 'bul.errno'
        );

        $sql = "select bul.id, bul.node_uuid, bul.patch_file_name, bul.log_time, bul.log_file_path, bul.errno, bul.extra 
            from bd_update_log bul, bd_node bn 
            where bul.node_uuid = bn.node_uuid order by $sortArr[$sortColumn] $sortType limit ? , ? ";
        $sqlCount = "select count(bul.id) as total from bd_update_log bul, bd_node bn where bul.node_uuid = bn.node_uuid";

        $data = $this->dbSelect($sql, array($start, $length));
        $count = $this->dbSelect($sqlCount);

        $records = array();
        $records["rows"] = array();
        $id = $start + 1;
        foreach ($data as $d) {
            $nodeInfo = $this->getNodeInfo($d['node_uuid']);

            //得到日志
            $extraLog = "";
            if (!empty($d['extra'])) {
                $extraLog = json_decode($d['extra'], true);
                $extraLog = $extraLog['updateDetails'];
                if (!empty($extraLog)) {
                    $extraLog = $extraLog['info'];
                }
            }

            $records["rows"][] = array(
                'no' => $id++,
                'node_name' => $nodeInfo['node_name'],
                'node_ip' => $nodeInfo['ip'],
                'patch_file_name' => $d['patch_file_name'],
                'log_time' => $d['log_time'],
                'status' => $this->getlogDes($d['errno']),
                'errno' => intval($d['errno']),
                'id' => $d['id'],
                'log_file_path' => $d['log_file_path'],
                'extra_log' => $extraLog,
            );
        }

        $records["total"] = $count[0]['total'];
        return $records;
    }

    /**
     * 删除升级包
     * @param array $params 参数
     * @return string
     */
    public function deletePatches(array $params): string
    {
        $uuids = $params['uuids'];
        $nameList = array();
        foreach ($uuids as $uuid) {
            $nameList[] = $this->getPatchName($uuid);
        }
        $uuidList = implode("','", $uuids);
        //获取需要删除的md5
        $sql = "select distinct level1_md5 from bd_update_file where uuid in ('" . $uuidList . "')";
        $data = $this->dbSelect($sql);
        $md5List = array();
        foreach ($data as $d) {
            $md5List[] = $d['level1_md5'];
        }
        $md5ListDes = implode("','", $md5List);
        //获取md5对应的记录uuid集合
        $sql = "select buf.uuid, buf.node_uuid, bn.node_type from bd_update_file buf, bd_node bn where buf.node_uuid = bn.node_uuid and buf.level1_md5 in ('" . $md5ListDes . "')";
        $data = $this->dbSelect($sql);
        $uuidList = $nodeList = array();
        foreach ($data as $d) {
            $uuidList[] = $d['uuid'];
            $nodeList[$d['node_uuid']] = $d['node_type'];
        }
        //删除对应的升级包
        $uuidListDes = implode("','", $uuidList);
        $sql = "delete from bd_update_file where uuid in ('" . $uuidListDes . "')";
        $result = $this->dbExec($sql, array());
        if ($result) {
            $opName = 'NODE_SYS_OP_DO_CMD_WITH_DETAIL';
            foreach ($nodeList as $nodeuuid => $nodetype) {
                $cmd = '';
                foreach ($nameList as $name) {
                    if (xphp_get_config('app', 'NODETYPE')['MASTER'] == $nodetype) {
                        $findCmd = 'find ' . xphp_get_config('app', 'UPLOAD_PATH') . $name;
                        $subcmd = 'rm -rf ' . xphp_get_config('app', 'UPLOAD_PATH') . $name . ';';
                    } else {
                        $findCmd = 'find /patch/' . $name;
                        $subcmd = 'rm -rf /patch/' . $name . ';';
                    }

                    //检查文件是否存在
                    $mbResult = $this->service('\\app\\v1\\system\\v0\\service\\Service')->mbNodeMsgs($opName, $nodeuuid, ['command' => $findCmd], true, false);
                    if ($mbResult['result'] && $mbResult['msg']['detail']) {
                        $cmd .= $subcmd;
                    }
                }

                $this->service('\\app\\v1\\system\\v0\\service\\Service')->mbNodeMsgs($opName, $nodeuuid, ['command' => $cmd], true, false);
            }
        }
        return $this->muOpResult($result, xphp_get_lang('UI_SETTINGS_UPDATE_DELETE_PATCH'));
    }

    /**
     * 删除升级包历史
     * @param array $params 参数
     * @return string
     */
    public function deletePatchHistory(array $params): string
    {
        $ids = $params['ids'];
        $nameList = array();
        foreach ($ids as $id) {
            $this->checkDeleteHistory($id);
        }
        $idsList = implode("','", $ids);
        $sql = "delete from bd_update_log where id in ('" . $idsList . "')";
        $result = $this->dbExec($sql, array());
        return $this->muOpResult($result, xphp_get_lang('UI_SETTINGS_UPDATE_DELETE_HISTORY'));
    }

    /**
     * 下载升级历史日志
     * @param array $params 参数
     * @return string
     */
    public function downloadPatchHistory(array $params): string
    {
        $id = $params['uuid'];
        $info = $this->getUpdateHistory($id);
        $path = $info['path'];
        $time = date("Y-m-d_H-i-s", strtotime($info['log_time']));
        $fileName = $info['node_name'] . '_' . $time . '_' . 'update_log.txt';
        $content = '';
        $opName = 'BD_SYSTEM_OP_READ_FILE';
        $msg = array('file_path' => $path);
        $msg = $this->service()->mbNodeMsg($opName, $info['node_uuid'], json_encode($msg), true);
        if ($msg['result']) {
            $content = $msg['msg']['file_content'];
        } else {
            return $this->muOpResult(false, xphp_get_lang('UI_SETTINGS_UPDATE_GET_HISTORY_LOG'), xphp_get_lang('UI_SETTINGS_UPDATE_GET_HISTORY_LOG_ERROR'), "warning");
        }
        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        Header("Accept-Length: " . strlen($content));
        Header("Content-Disposition: attachment; filename=" . $fileName);
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: public");
        return $content;
    }

    /**
     * 上传升级包检查系统空间是否足够满足上传，文件名是否冲突
     * @param array $params
     * @return string
     */
    public function checkSystemSpaceEnough(array $params): string
    {
        $fileSize = $params['file_size'];
        $fileName = $params['file_name'];
        $filePath = xphp_get_config('app', 'UPLOAD_PATH');
        $this->checkPatchExist($fileName); //检查文件是否存在
        $nodeuuid = Node::instance()->getLocalNodeUUID();
        $msg = array(
            'file_size' => $fileSize,
            'upload_path' => $filePath
        );
        $opName = "NODE_SYS_OP_PATCH_UPLOAD_CHECK";
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true);
        $opcodeDes = (new NodeOpcode())->getOpcodeDes($opName);
        if (!$mbResult['result']) {
            return $this->muOpResult(false, $opcodeDes, "", "warning", $mbResult['errorCode']);
        } else {
            return $this->muOpResult(true, $opcodeDes);
        }
    }

    /**
     * 上传升级包
     * @return string
     */
    public function uploadPatches(): string
    {
        // Support CORS
        // header("Access-Control-Allow-Origin: *");
        // other CORS headers if any...
        //检查上传升级包格式
        $this->checkUpgradeFile();

        //检查请求类型是否有效
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            exit; // finish preflight CORS requests here
        }

        //调试模式
        if (!empty($_REQUEST['debug'])) {
            $random = rand(0, intval($_REQUEST['debug']));
            if ($random === 0) {
                header("HTTP/1.0 500 Internal Server Error");
                exit;
            }
        }


        // 5 minutes execution time，设置函数执行最大时间，0为无限制
        @set_time_limit(5 * 60);


        // Settings
        // $targetDir = ini_get("upload_tmp_dir") . DIRECTORY_SEPARATOR . "plupload";
        $uploadDir = xphp_get_config('app', 'UPLOAD_PATH');
        $targetDir = xphp_get_config('app', 'UPLOADTMP_PATH');
        $cleanupTargetDir = true; // Remove old files
        $maxFileAge = 5 * 3600; // Temp file age in seconds

// 		@file_put_contents('./a.txt',print_r($_FILES,true),FILE_APPEND); //上传文件写入日志到a.txt

        // Get a file name
        if (isset($_REQUEST["name"])) {
            $fileName = $_REQUEST["name"];
        } elseif (!empty($_FILES)) {
            $fileName = $_FILES["file"]["name"];
        } else {
            $fileName = uniqid("file_");
        }

        $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;
        $uploadPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;

        // Chunking might be enabled
        $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
        $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 1;


        // Remove old temp files
        if ($cleanupTargetDir) {
            if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
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
        if (!$out = @fopen("{$filePath}_{$chunk}.parttmp", "wb")) {
            die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
        }

        if (!empty($_FILES)) {
            if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
            }

            // Read binary input stream and append it to temp file
            if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
            }
        } else {
            if (!$in = @fopen("php://input", "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
            }
        }


        while ($buff = fread($in, 5 * 1024 * 1024)) {
            fwrite($out, $buff);
        }

        @fclose($out);
        @fclose($in);

        rename("{$filePath}_{$chunk}.parttmp", "{$filePath}_{$chunk}.part");

        // 检查是否是最后一个分片，并判断是否已经完成所有分片上传，且中间分片等于分片大小5M
        $maxChunk = $chunks - 1;
        $done = true;
        for ($index = 0; $index < $maxChunk; $index++) {
            if (!file_exists("{$filePath}_{$index}.part") || filesize("{$filePath}_{$index}.part") != 5 * 1024 * 1024) {
                $done = false;
            }
        }
        if ($chunk != $maxChunk || !file_exists("{$filePath}_{$maxChunk}.part")) {
            $done = false;
        }

        if ($done) {
            if (!$out = @fopen($uploadPath, "wb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
            }

            if (flock($out, LOCK_EX)) {
                for ($index = 0; $index < $chunks; $index++) {
                    if (!$in = @fopen("{$filePath}_{$index}.part", "rb")) {
                        //赋予文件读写执行权限，重新获取一次
                        chmod("{$filePath}_{$index}.part", 0755);
                        $in = @fopen("{$filePath}_{$index}.part", "rb");
                    }
                    if (!$in) {
                        die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
                    }
                    while ($buff = fread($in, 5 * 1024 * 1024)) {
                        fwrite($out, $buff);
                    }

                    @fclose($in);
                    @unlink("{$filePath}_{$index}.part");
                }

                flock($out, LOCK_UN);
            }
            @fclose($out);
        }
        die($_SESSION['fileName']);
    }

    /**
     * 上传升级包成功更新记录
     * @param array $params 参数
     * @return string
     */
    public function updatePatchList(array $params): string
    {
        $uuid = xphp_uuid();
        $name = $params['name'];
        $fileSize = $params['size'];
        $filePath = xphp_get_config('app', 'UPLOAD_PATH') . $name;
        $md5file = $params['md5'];
        if (empty($md5file)) {
            $md5file = md5_file($filePath);
        }

        $fileUrl = "/tmp/upgrade/" . $name;
        $nodeuuid = $this->getMasterNode();

        //屏蔽解压升级包获取升级日志信息，解决上次升级包太大界面崩溃问题
        $extraData = [];
        $extraDataList = array(
            'file_size' => $fileSize,
            'updateDetails' => $this->getExtraDataUpdate($extraData),
        );

        $status = xphp_get_config('app', 'UPDATE_PATCH_STATUS')['UPDATE_WAITING'];
        $uploadTime = date("Y-m-d H:i:s");
        //插入之前检测该信息是否存在 避免重复插入
        $sqlcheck = "select id from bd_update_file where level1_md5 = ?";
        $resultcheck = $this->dbSelect($sqlcheck, array($md5file));
        if (!empty($resultcheck)) {
            exit ($this->muOpResult(false, xphp_get_lang('UI_SETTINGS_UPDATE_FILE_UPLOAD'), xphp_get_lang('UI_SETTINGS_UPDATE_UPLOAD_CHECK_ERROR'), "warning"));
        }
        $sql = "insert into bd_update_file (uuid, level1_md5, node_uuid, name, path, status, controller_file_url, upload_time, extra) values (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $result = $this->dbQuery($sql, array($uuid, $md5file, $nodeuuid, $name, $filePath, $status, $fileUrl, $uploadTime, json_encode($extraDataList)));
        if ($result == 1) {
            return $this->muOpResult(true, xphp_get_lang('UI_SETTINGS_UPDATE_FILE_UPLOAD'));
        } else {
            return $this->muOpResult(false, xphp_get_lang('UI_SETTINGS_UPDATE_FILE_UPLOAD'), xphp_get_lang('UI_SETTINGS_UPDATE_FILE_UPLOAD_ERROR'), "warning");
        }
    }

    /**
     * 获取选中的升级包的信息
     * @param array $params
     * @return array
     */
    public function getSelectPatch(array $params): array
    {
        $sql = "select name,path from bd_update_file where uuid = ?";
        $data = $this->dbSelect($sql, array($params['uuid']));
        //获取上一次升级选项
        $sqlOption = "select patch_options from bd_update_log order by id desc limit 0,1";
        $dataOption = $this->dbSelect($sqlOption);
        return array(
            'name' => $data[0]['name'],
            'path' => $data[0]['path'],
            'options' => !empty($dataOption[0]['patch_options']) ? json_decode($dataOption[0]['patch_options'], true) : array()
        );
    }

    /**
     * 检测是否有升级中的升级包
     * @return array
     */
    public function checkIsUpgrading(): array
    {
        $sql = "select id from bd_update_file where status = 5 limit 1";
        $data = $this->dbSelect($sql);
        return ['isUpgrading' => !empty($data)];
    }

    /**
     * 启动升级检查
     * @param array $params
     * @return string
     */
    public function upgradeCheck(array $params): string
    {
        $this->checkLabStatus();//检查是否有虚拟实验室部署中/或者修改中
        $nodeuuids = $params['node_uuids'];
        $this->checkRunningJob($nodeuuids);//检查是否有运行任务
        $this->checkRunningCluster();
        $masterFlag = $params['master_flag'];
        $name = $params['name'];
        $md5 = $params['md5'];
        //检查升级包版本是否是对应版本
        $this->checkUpgradeVersion($name);

        //检查节点是否已经在升级
        $this->checkNodeInUpgrad($nodeuuids, $md5);

        if ($masterFlag) {
            $this->checkReUpdate($nodeuuids[0], $md5);
        } else {
            foreach ($nodeuuids as $nodeuuid) {
                $this->checkReUpdate($nodeuuid, $md5);
            }
        }

        return $this->muOpResult(true, xphp_get_lang('UI_SETTINGS_UPDATE_START'));
    }

    /**
     * 得到升级可用的备份节点列表
     * @param array $params
     * @return array
     */
    public function getUpdateNodeList(array $params): array
    {
        $patchuuid = $params['uuid'];
        $md5 = $this->getMd5ByUUID($patchuuid);
        $sql = "select ip, host_name, node_nickname, node_uuid from bd_node order by node_type asc";
        $data = $this->dbSelect($sql, array());

        $info = array();
        $nodeHandler = Node::instance();
        foreach ($data as $d) {
            $info[] = array(
                "node_name" => $nodeHandler->getNodeShowName($d['ip'], $d['node_nickname'], $d['host_name']),
                "node_uuid" => $d['node_uuid'],
                "ip" => $d['ip'],
                "update_flag" => $this->getUpdateFlag($d['node_uuid'], $md5),
                "use_flag" => $this->getChildNodeStatus($d['node_uuid']),
            );
        }
        return $info;
    }

    /**
     * 检查主节点是否已经升级
     * @param array $params
     * @return string
     */
    public function checkMasterUpdate(array $params): string
    {
        $nodeuuid = $params['node_uuid'];
        $patchuuid = $params['patch_uuid'];
        $md5 = $this->getMd5ByUUID($patchuuid);
        $sql = "select bul.log_file_path from bd_update_log bul, bd_update_file buf where bul.level1_md5 = buf.level1_md5 and bul.level1_md5 = ? and bul.node_uuid= ?";
        $data = $this->dbSelect($sql, array($md5, $nodeuuid));
        if (empty($data)) {
            return $this->muOpResult(false, xphp_get_lang('UI_PLATFORM_SYSTEM_UPDATE'), xphp_get_lang('UI_SETTINGS_UPDATE_MASTER_CHECK'));
        }
        return $this->muOpResult(true, xphp_get_lang('UI_PLATFORM_SYSTEM_UPDATE'));
    }

    /**
     * 执行升级
     * @param array $params 参数
     * @return string
     */
    public function upgradeStart(array $params): string
    {
        $this->checkLabStatus();//检查是否有虚拟实验室部署中/或者修改中
        $nodeuuids = $params['node_uuids'];
        $this->checkRunningJob($nodeuuids);//检查是否有运行任务
        $masterFlag = $params['master_flag'];
        $name = $params['name'];
        $md5 = $params['md5'];
        $patch_options = array(
            'update_web_config' => $params['patch_options']['update_web_config']?"true":"false",
            'update_web_cert' => $params['patch_options']['update_web_cert']?"true":"false",
            'update_firewall_rule' => $params['patch_options']['update_firewall_rule']?"true":"false"
        );
        //检查升级包版本是否是对应版本
//        $this->checkUpgradeVersion($name);

        //检查节点是否已经在升级
//        $this->checkNodeInUpgrad($nodeuuids, $md5);

        if ($masterFlag) {
            $this->checkReUpdate($nodeuuids[0], $md5);
        } else {
            foreach ($nodeuuids as $nodeuuid) {
                $this->checkReUpdate($nodeuuid, $md5);
            }
        }

        //开始升级
        $nodeuuids = $params['node_uuids'];
        $path = xphp_get_config('app', 'UPLOAD_PATH') . $name;
        $url = "/tmp/upgrade/" . $name;
        $patchuuid = $params['uuid'];
        $errNodes = [];

        if ($masterFlag) {
            //主节点，检查升级包和开始升级
//            $checkResult = $this->checkPatchValidity($nodeuuids[0], $patchuuid, $path);
            $opName = 'NODE_SYS_OP_DO_MASTER_UPDATE';
//            if ($checkResult) {
                $msg = array("patch_uuid" => $patchuuid, "patch_file_path" => $path, "patch_options" => $patch_options);
                $mbResult = $this->service('\\app\\v1\\system\\v0\\service\\Service')->mbNodeMsgs($opName, $nodeuuids[0], $msg, false, true);
//            } else {
//                return $this->muOpResult(false, (new PfOpcode())->getOpcodeDes('NODE_SYS_OP_CHECK_PATCH_VALIDITY'));
//            }
        } else {
            //子节点，下载升级包
//            $md5 = md5_file($path);
            $fileSize = filesize($path);
            $sqlParams = array(
                "file_url" => $url,
                "file_name" => $name,
                "file_md5" => $md5,
                "file_path" => $path,
                "file_size" => $fileSize
            );
            foreach ($nodeuuids as $nodeuuid) {
                $this->insertNodePatch($sqlParams, $nodeuuid);
            }
            $opName = 'NODE_SYS_OP_DOWNLOAD';
            foreach ($nodeuuids as $nodeUUID) {    //插入备份节点补丁包信息导数据库
                $nodeInfo = $this->getChildNodeStatus($nodeUUID);
                if (!$nodeInfo['flag']) {
                    $errNodes[] = $nodeUUID;
                    continue;
                }
                // 子节点可能已经关机但未更新状态，此时也需要跳过发送，通过ping命令返回的状态码判断是否开机
                $ipList = $this->getChildNodeIpList($nodeUUID);
                $pingFlag = false;
                foreach ($ipList as $ip) {
                    $cmd = sprintf('ping -c 4 %s', $ip);
                    exec($cmd, $output, $statusCode);
                    if ($statusCode == 0) {
                        $pingFlag = true; // 有ip能ping通代表已开机
                    }
                }
                if (!$pingFlag) {
                    $errNodes[] = $nodeUUID;
                    continue;
                }
                $patchInfo = $this->getNodePatchInfo($nodeUUID, $name);
                $patchuuid = $patchInfo['uuid'];
                $msg = array(
                    "patch_uuid" => $patchuuid,
                    "file_url" => $url,
                    "file_name" => $name,
                    "file_md5" => $md5,
                    "file_size" => $fileSize
                );

                $mbResult = $this->service('\\app\\v1\\system\\v0\\service\\Service')->mbNodeMsgs($opName, $nodeUUID, $msg, false, true);
            }
        }
        $operate = (new PfOpcode())->getOpcodeDes($opName);
        if (empty($mbResult)) {
            // 节点异常
            return $this->muOpResult(false, $operate, 'All Node Exception', 'error', -1, $errNodes);
        }
        $result = $mbResult['result'];
        $operate = (new PfOpcode())->getOpcodeDes($opName);
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, '', '', 0, $errNodes);
        } else {
            return $this->muOpResult($result, $operate, '', '', $mbResult['errorCode'], $errNodes);
        }
    }

    /**
     * 升级子节点
     * @param array $params 参数
     * @return void
     */
    public function upgradeChildNode(array $params): void
    {
        $nodeuuids = $params['node_uuids'];
        $name = $params['name'];
        $patch_options = array(
            'update_web_config' => $params['patch_options']['update_web_config']?"true":"false",
            'update_web_cert' => $params['patch_options']['update_web_cert']?"true":"false",
            'update_firewall_rule' => $params['patch_options']['update_firewall_rule']?"true":"false"
        );
        $updatePatchConf = xphp_get_config('app', 'UPDATE_PATCH_STATUS');
        //正在升级中的状态
        $upgradeRuningArr = array(
            $updatePatchConf['UPDATE_UPLOAD_TO_NODE'],
            $updatePatchConf['UPDATING'],
            $updatePatchConf['PATCH_INVALID_ING'],
        );
        foreach ($nodeuuids as $nodeUUID) {    //插入备份节点补丁包信息导数据库
            $patchInfo = $this->getNodePatchInfo($nodeUUID, $name);
            $patchuuid = $patchInfo['uuid'];
            $path = $patchInfo['path'];
            $status = $patchInfo['status'];
            //如果这个节点正在升级,跳过本次消息
            if (in_array($status, $upgradeRuningArr)) {
                continue;
            }
            //启动升级
            $opName = 'NODE_SYS_OP_DO_NODE_UPDATE';
            $msg = array("patch_uuid" => $patchuuid, "patch_file_path" => $path,  "patch_options" => $patch_options);
            $this->service('\\app\\v1\\system\\v0\\service\\Service')->mbNodeMsgs($opName, $nodeUUID, $msg, false, true);
        }
    }

    /**
     * 获取升级信息
     * @param array $params 参数
     * @return array
     */
    public function getUpgradeInfo(array $params): array
    {
        $initFlag = $params['init_flag'];
        $uuid = $params['uuid'];
        $fileMd5 = $this->getMd5ByUUID($uuid);
        $nodeuuids = $params['node_uuids'];
        $nodeArray = implode("','", $nodeuuids);
        $sql = "select bn.node_uuid, bn.ip, bn.host_name, bn.node_nickname, bpf.update_progress,bpf.status from bd_update_file bpf,bd_node bn where bpf.node_uuid = bn.node_uuid and bpf.level1_md5 = ? and bpf.node_uuid in ('" . $nodeArray . "')";
        $data = $this->dbSelect($sql, array($fileMd5));
        if (!$data) {
            return array('flag' => 2);
        }
        $info = array();
        foreach ($data as $d) {
            $status = intval($d['status']);
            if (!$initFlag) {
                $status = xphp_get_config('app', 'UPDATE_PATCH_STATUS')['UPDATE_WAITING'];
            }
            $info[] = array(
                "node_uuid" => $d['node_uuid'],
                "node_name" => $this->getNodeShowName($d['ip'], $d['node_nickname'] ?? '', $d['host_name']),
                "progress" => $d['update_progress'] . "%",
                "status" => $status,
                "statusDes" => $this->getUpdateStatusDes($status)
            );
            //如果升级成功，杀掉所有web监控进程，并由看门狗重启
            if ($status == 4) {
                $this->restartWebProcess($d['node_uuid']);
            }
        }

        return $info;
    }

    /**
     * 获取升级包状态
     * @param array $params
     * @return array
     */
    public function getPacketStatus(array $params): array
    {
        $nodeUUIDS = $params['node_uuids'];

        $arr = [];

        foreach ($nodeUUIDS as $uuid) {
            $sql = 'SELECT status FROM bd_update_file WHERE node_uuid = ?';
            $sqldData = $this->dbSelect($sql, array($uuid));

            if (!empty($sqldData)) {
                $arr[] = array(
                    'node_uuid' => $uuid,
                    'status' => $sqldData[0]['status']
                );
            }
        }

        if (!empty($arr)) {
            $info = array(
                'success' => true,
                'code' => 0,
                'message' => xphp_get_lang('WEB_UPLOAD_GET_UPGRADE_STATUS_SUCCESS'),
                'data' => $arr
            );
        } else {
            $info = array(
                'success' => false,
                'code' => 1,
                'message' => xphp_get_lang('WEB_UPLOAD_NO_UPGRADE_STATUS'),
                'data' => array(),
            );
        }

        return $info;
    }

    /**
     * 获取升级日志 根据node_uuid查出对应的升级日志
     * @param array $params
     * @return array
     */
    public function getUpgradeLog(array $params): array
    {
        $logSystem = xphp_get_config('log_system');
        $errorCode = xphp_get_config('error', 'errorCode');
        $errorCodeDes = xphp_get_config('error', 'errorCodeDes');
        $node_uuid = $params['node_uuid'];

        $sql = 'SELECT * FROM bd_update_running_log WHERE node_uuid = ? ORDER BY id DESC';
        $sqldData = $this->dbSelect($sql, array($node_uuid));

        if (!empty($sqldData)) {
            $logData = [];
            foreach ($sqldData as $d) {
                // 不符合更新条件时显示错误码的描述
                if ('BD_SYSTEMLOG_DESC_KEY_CAN_NOT_TO_UPDATE' == $d['log_key'] && $d['error_code']) {
                    $des = $errorCodeDes[$errorCode[$d['error_code']]];
                } else {
                    $des = $logSystem[$d['log_key']];
                }
                $logData[] = array(
                    'id' => $d['id'],
                    'logTime' => $d['log_time'],
                    'progress' => $d['update_progress'],
                    'description' => $des,
                    'errorCode' => $d['error_code']
                );
            }

            $info = array(
                'success' => true,
                'code' => 0,
                'message' => xphp_get_lang('WEB_UPLOAD_GET_UPGRADE_LOG_SUCCESS'),
                'data' => $logData
            );
        } else {
            $info = array(
                'success' => false,
                'code' => -1,
                'message' => xphp_get_lang('WEB_UPLOAD_NO_UPGRADE_LOG'),
                'data' => []
            );
        }

        return $info;
    }

    // ----- 内部调用方法 start ----

    /**
     * 获取主节点
     * @return string
     */
    public function getMasterNode(): string
    {
        $sqlNode = "select node_uuid from bd_node where node_type = ?";
        $dataNode = $this->dbSelect($sqlNode, array(xphp_get_config('app', 'NODETYPE')['MASTER']));
        return $dataNode[0]['node_uuid'];
    }

    /**
     * 获取节点信息
     * @param string $nodeuuid 节点uuid
     * @return array
     */
    public function getNodeInfo(string $nodeuuid): array
    {
        $sql = "select node_uuid, ip,host_name, node_nickname from bd_node ";
        $sqlParams = array();
        if ($nodeuuid) {
            $sql .= " where node_uuid = ?";
            $sqlParams = array($nodeuuid);
        }
        $data = $this->dbSelect($sql, $sqlParams);
        $nodeHandler = Node::instance();
        return array(
            'node_uuid' => $data[0]['node_uuid'],
            'ip' => $data[0]['ip'],
            "node_name" => $nodeHandler->getNodeShowName($data[0]['ip'], $data[0]['node_nickname'], $data[0]['host_name'])
        );
    }

    /**
     * 获取升级历史状态描述
     * @param int $error 错误码
     * @return string
     */
    public function getlogDes(int $error): string
    {
        return $error == 0 ? xphp_get_lang('WEB_PUBLIC_SUCCESS') : xphp_get_lang('WEB_PUBLIC_FAILURE');
    }

    /**
     * 上传系统升级包检查
     * @return void
     */
    private function checkUpgradeFile(): void
    {
        $files = $_FILES['file'];
        if (!empty($files)) {
//             $this->checkUploadStatus($files['error']);
            $this->checkUpgradeFileName($files['name'], "tar.gz");
            //开启存储保护后的检查
            $sql = "select data_protect_flag from bd_system";
            $data = $this->dbSelect($sql);
            if (!empty($data) && intval($data[0]['data_protect_flag']) == xphp_get_config('app', 'FLAG')['SET']) {
                exit($this->muOpResult(false, xphp_get_lang('WEB_SYSTEM_UPLOAD_FILE'), xphp_get_lang('WEB_SYSTEM_UPLOAD_DATA_PROTECT_ERROR')));
            }
        } else {
            exit($this->muOpResult(false, xphp_get_lang('WEB_SYSTEM_UPLOAD_FILE')));
        }
    }

    /**
     * 检测上传升级包文件名字
     * @param string $name 客户端文件的原名称
     * @param string $suffixes 上传文件后缀
     */
    private function checkUpgradeFileName(string $name, string $suffixes): void
    {
        $typedes = substr($name, -6);   //tar.gz
        if ($typedes == $suffixes) {
            return;
        }
        exit($this->muOpResult(false, xphp_get_lang('WEB_SYSTEM_UPLOAD_FILE'), xphp_get_lang('WEB_SYSTEM_UPLOAD_FILE_TYPE_TAR_GZ_ERROR'), 'error'));
    }

    /**
     * 获取升级包名字
     * @param string $uuid
     * @return string
     */
    public function getPatchName(string $uuid): string
    {
        $sql = "select name from bd_update_file where uuid = ?";
        $data = $this->dbSelect($sql, array($uuid));
        if (empty($data[0])) {
            exit($this->muOpResult(false, xphp_get_lang('UI_SETTINGS_UPDATE_DELETE_PATCH'), "Invalid UUID: $uuid", "warning"));
        }
        return $data[0]['name'];
    }

    /**
     * 检查是否为失败的升级历史
     * @param int $id
     */
    public function checkDeleteHistory(int $id)
    {
        $sql = "select errno from bd_update_log where id = ?";
        $data = $this->dbSelect($sql, array($id));
        $errno = intval($data[0]['errno']);
        if ($errno == 0) {
            exit($this->muOpResult(false, xphp_get_lang('UI_SETTINGS_UPDATE_DELETE_HISTORY'), xphp_get_lang('UI_SETTINGS_UPDATE_DELETE_HISTORY_ERROR_TIPS'), "warning"));
        }
    }

    /**
     * 检查虚拟实验室状态，部署中/修改中不能升级
     */
    private function checkLabStatus(): void
    {
        $sql = "select proxy_status from sr_virtual_lab ";
        $data = $this->dbSelect($sql);
        if (!empty($data)) {
            foreach ($data as $d) {
                //状态部署中不能升级
                if (intval($d['proxy_status']) == 1 || intval($d['proxy_status']) == 6) {
                    exit($this->muOpResult(false, xphp_get_lang('UI_SETTINGS_UPDATE_START'), xphp_get_lang('UI_VIRTUAL_LAB_DEPLOY_UPGRADE_TIPS'), "warning"));
                }
            }
        }
    }

    /**
     * 检查升级包版本是否匹配
     * @param string $name
     */
    private function checkUpgradeVersion(string $name)
    {
        $list = explode("-update-package-", $name);
        //获取升级包版本号
        $listTwo = explode("-to-", $list[1]);
        $listNum = explode("-", $listTwo[1]);
        $listOne = explode(".", $listNum[0]);
        //获取当前版本号
        $version = explode("build: ", xphp_get_config('app', 'SYSTEM_INFO')['version']);
        $versionNum = explode("-", $version[1]);
        $versionList = explode(".", $versionNum[0]);
        //组装当前版本号
        $currentVersion = $versionList[0] . '.' . $versionList[1] . "." . $versionList[2];
        //组装升级包版本号
        $upgradeVersion = $listOne[0] . '.' . $listOne[1] . "." . $listOne[2];
        //如果操作系统和底层架构参数存在
        if (!empty(xphp_get_config('app', 'SYSTEM_INFO')['os_type']) && !empty(xphp_get_config('app', 'SYSTEM_INFO')['arch_type'])) {
            //当前操作系统+底层架构
            $currentOs = xphp_get_config('app', 'SYSTEM_INFO')['os_type'] . "-" . xphp_get_config('app', 'SYSTEM_INFO')['arch_type'];
            //检查当前版本操作系统和底层架构和升级包是否匹配
            if (!strpos($name, $currentOs)) {
                exit($this->muOpResult(false, xphp_get_lang('UI_SETTINGS_UPDATE_START_ERROR'), xphp_get_lang('UI_SETTINGS_PATCH_ARCH_OR_OS_NOT_MATCH'), "warning"));
            }
        }
        // 检查包名是否一致
        if ($list[0] != xphp_get_config('app', 'SYSTEM_INFO')['enterprise']) {
            if (strpos(xphp_get_config('app', 'SYSTEM_INFO')['enterprise'], 'divs') === 0 && strpos($list[0], 'vdms') === 0) {
                // 包名不一致，如果是从divs升级到vdms则放开
                return;
            } else {
                exit($this->muOpResult(false, xphp_get_lang('UI_SETTINGS_UPDATE_START_ERROR'), xphp_get_lang('UI_SETTINGS_PATCH_NAME_NOT_MATCH'), "warning"));
            }
        }
        //检查版本是否匹配，版本号是否大于等于当前版本
        if ($upgradeVersion < $currentVersion) {
            exit($this->muOpResult(false, xphp_get_lang('UI_SETTINGS_UPDATE_START_ERROR'), xphp_get_lang('UI_SETTINGS_PATCH_NOT_MATCH'), "warning"));
        }
    }

    /**
     * 检查节点是否正在升级
     * @param array $nodeuuids
     * @param string $md5
     */
    private function checkNodeInUpgrad(array $nodeuuids, string $md5)
    {
        $updatePatchConf = xphp_get_config('app', 'UPDATE_PATCH_STATUS');
        //正在升级中的状态
        $upgradeRuningArr = array(
            $updatePatchConf['UPDATE_UPLOAD_TO_NODE'],
            $updatePatchConf['UPDATING'],
            $updatePatchConf['PATCH_INVALID_ING'],
        );
        foreach ($nodeuuids as $nodeuuid) {
            $sql = "select status from bd_update_file where node_uuid = ? and level1_md5 = ? and error_code = ?";
            $data = $this->dbSelect($sql, array($nodeuuid, $md5, 0));
            if (in_array($data[0]['status'], $upgradeRuningArr)) {
                //如果检测到有正在升级中的状态,直接返回.
                exit($this->muOpResult(false, xphp_get_lang('UI_SETTINGS_UPDATE_START_ERROR'), xphp_get_lang('UI_SETTINGS_UPDATE_CHECK_ERROR_TIPS'), "warning"));
            }
        }
    }

    /**
     * 重复升级检查
     * @param string $nodeuuid
     * @param string $md5
     */
    public function checkReUpdate(string $nodeuuid, string $md5)
    {
        $sql = "select log_file_path from bd_update_log where level1_md5 = ? and node_uuid = ? and errno = ?";
        $data = $this->dbSelect($sql, array($md5, $nodeuuid, 0));
        if ($data) {
            exit($this->muOpResult(false, xphp_get_lang('UI_SETTINGS_UPDATE_START_ERROR'), xphp_get_lang('UI_SETTINGS_UPDATE_START_ERROR_TIPS'), "warning"));
        }
    }

    /**
     * 升级补丁检查是否存在正在运行中的任务
     */
    private function checkRunningJob($nodeuuids)
    {
        $taskStatusConf = xphp_get_config('task', 'TASKSTATUS');
        $nodeuuidsDes = implode("','", $nodeuuids);
        $sql = "select task_uuid from bd_task where task_status in ("
            . $taskStatusConf['RUNNING'] . ","
            . $taskStatusConf['PAUSED'] . ","
            . $taskStatusConf['NETWORK_FAULT'] . ","
            . $taskStatusConf['ABNORMAL'] . ","
            . $taskStatusConf['PREPARING'] . ","
            . $taskStatusConf['STARTING'] . ","
            . $taskStatusConf['STOPPING'] . ","
            . $taskStatusConf['TAKEOVER'] . ","
            . $taskStatusConf['TAKEOVER_STARTING'] . ","
            . $taskStatusConf['TAKEOVER_STOPPING'] . ","
            . $taskStatusConf['CREATING'] . ","
            . $taskStatusConf['DELETING'] . ","
            . $taskStatusConf['CLEANING'] . ","
            . $taskStatusConf['SUCCESSED']
            . ") and node_uuid in ('" . $nodeuuidsDes . "')";
        $data = $this->dbSelect($sql);
        if (!empty($data)) {
            exit($this->muOpResult(false, xphp_get_lang('UI_SETTINGS_UPDATE_START_ERROR'), xphp_get_lang('UI_SETTINGS_UPDATE_RUNNING_JOB_EXIST'), "warning"));
        }
    }

    /**
     * 升级包检查，超时时间三分钟，直到检查成功(1分钟有可能还是会失败)
     * @param $nodeUUID
     * @param $patchUUID
     * @param $patchFilePath
     * @return bool
     */
    private function checkPatchValidity($nodeUUID, $patchUUID, $patchFilePath): bool
    {
        //检查升级包是否有效，先发消息到后台检查，如果检查成功直接返回，如果检查超时，查询数据库检查
        $opName = "NODE_SYS_OP_CHECK_PATCH_VALIDITY";
        $msg = array("patch_uuid" => $patchUUID, "patch_file_path" => $patchFilePath);
        $mbResult = $this->service('\\app\\v1\\system\\v0\\service\\Service')->mbNodeMsgs($opName, $nodeUUID, $msg);
        if ($mbResult['result']) {
            //检查成功，直接返回
            return true;
        } else {
            //检查失败，查询数据库
            $i = 0;
            while ($i < 900) {
                $sql = "select status from bd_update_file where uuid = ?";
                $data = $this->dbSelect($sql, array($patchUUID));
                if (empty($data)) {
                    return false;
                }
                if ($data[0]['status'] == xphp_get_config('app', 'UPDATE_PATCH_STATUS')['PATCH_INVALID_COMPLETE']) {
                    //检查完成
                    return true;
                }
                if ($data[0]['status'] != xphp_get_config('app', 'UPDATE_PATCH_STATUS')['PATCH_INVALID_ING']) {
                    //如果不是检查中,直接返回
                    return false;
                }
                sleep(1);
                $i++;
            }
            return false;
        }
    }


    /**
     * 插入备份节点升级记录信息
     * @param array $sqlParams
     * @param string $nodeuuid
     */
    private function insertNodePatch(array $sqlParams, string $nodeuuid): void
    {
        $md5file = $sqlParams['file_md5'];
        //检查是否已经插入数据到数据库
        $sql = "select uuid from bd_update_file where level1_md5 = ? and node_uuid = ?";
        $data = $this->dbSelect($sql, array($md5file, $nodeuuid));
        if (!empty($data)) {
            return;
        }
        $uuid = xphp_uuid();
        $name = $sqlParams['file_name'];
        $filepath = $sqlParams['file_path'];
        $fileUrl = $sqlParams['file_url'];
        $status = xphp_get_config('app', 'UPDATE_PATCH_STATUS')['UPDATE_WAITING'];
        $uploadTime = date('Y-m-d H:i:s');
        $sql = "insert into bd_update_file (uuid, level1_md5, node_uuid, name, path, status, controller_file_url, upload_time) values (?, ?, ?, ?, ?, ?, ?, ?)";
        $this->dbQuery($sql, array($uuid, $md5file, $nodeuuid, $name, $filepath, $status, $fileUrl, $uploadTime));
    }

    /**
     * 得到安装包的uuid
     * @param string $nodeuuid
     * @param string $name
     * @return mixed
     */
    private function getNodePatchInfo(string $nodeuuid, string $name)
    {
        $sql = "select uuid, level1_md5, node_uuid, status, update_progress, name, path, controller_file_url from bd_update_file where name = ? and node_uuid = ?";
        $data = $this->dbSelect($sql, array($name, $nodeuuid));
        return $data[0];
    }

    /**
     * 杀掉php.monitor监控进程
     * @param string $nodeuuid
     * @return boolean
     */
    public function restartWebProcess(string $nodeuuid): bool
    {
        //获取php定义进程
        $cmd = "ps aux|grep monitor";
        $opName = 'NODE_SYS_OP_DO_CMD_WITH_DETAIL';
        $msg = array('command' => $cmd);
        $mbResult = $this->service('\\app\\v1\\system\\v0\\service\\Service')->mbNodeMsgs($opName, $nodeuuid, $msg, true, false);
        if (!$mbResult['result']) {
            return true;
        }
        //获取消息成功
        $info = $mbResult['msg']['detail'];
        $info = explode(PHP_EOL, $info);
        //如果有对应查询php进程存在
        if (!empty($info)) {
            $cmd = '';
            foreach ($info as $d) {
                $i = 0;
                $list = explode(" ", trim($d));
                //依次杀掉对应php监控进程
                foreach ($list as $l) {
                    //如果不是root权限下的不管
                    if ($l == "nginx") break;
                    //转换成数组后会有空格字符串，排除这些项
                    if (!empty($l)) {
                        $i++;
                    }
                    //进程号
                    if ($i == 2) {
                        $cmd .= 'kill -9 ' . $l . ';';
                        exec($cmd);
                        break;
                    }
                }
            }
            $msg = array('command' => $cmd);
            $mbResult = $this->service('\\app\\v1\\system\\v0\\service\\Service')->mbNodeMsgs($opName, $nodeuuid, $msg, true, false);
        }

        return $mbResult['result'];
    }

    /**
     * 得到节点展示名称
     * @param string $ip
     * @param string $nickname
     * @param string $hostname
     * @return string
     */
    public function getNodeShowName(string $ip, string $nickname, string $hostname): string
    {
        //如果IP和昵称一样,显示主机名+IP,否则,显示昵称+IP
        if ($ip == $nickname || empty($nickname)) {
            $name = $hostname . '(' . $ip . ')';
        } else {
            $name = $nickname . '(' . $ip . ')';
        }
        return $name;
    }

    /**
     * 获取升级状态描述
     * @param int $status
     * @return mixed
     */
    public function getUpdateStatusDes(int $status)
    {
        return xphp_get_desc('Pf', 'UPDATE_PATCH_DES')[$status];
    }

    /**
     * 获取升级包第一级MD5
     * @param string $uuid
     * @return mixed
     */
    public function getMd5ByUUID(string $uuid)
    {
        $sqlmd5 = "select level1_md5 from bd_update_file where uuid =?";
        $datamd5 = $this->dbSelect($sqlmd5, array($uuid));
        return $datamd5[0]['level1_md5'];
    }

    /**
     * 已下载完成 存在数据库的更新日志为目前版本符合的日志
     * @param array $extraData
     * @return array
     */
    public function getExtraDataUpdate(array $extraData): array
    {
        //如果为空则返回空
        if (empty($extraData)) {
            return array();
        }
        $versionNum = $this->getVersionNum(xphp_get_config('app', 'SYSTEM_INFO')['version']);
        $getversionList = explode(".", $versionNum);
        $getversionNum = sprintf('%03s', $getversionList[0]) . sprintf('%03s', $getversionList[1]) . sprintf('%03s', $getversionList[2]) . sprintf('%07s', $getversionList[3]);
        $infoLogList = array();
        foreach ($extraData['info'] as $op) {
            //这里需要处理下版本比如5.1.1.12589=> 0050010010012589
            $eachVersion = $op['version'];
            $versionList = explode(".", $eachVersion);
            $eachNum = sprintf('%03s', $versionList[0]) . sprintf('%03s', $versionList[1]) . sprintf('%03s', $versionList[2]) . sprintf('%07s', $versionList[3]);
            if ($eachNum >= $getversionNum) {
                $infoLogList[] = $op;
            }
        }
        //把新得到的日志覆盖掉原来的日志
        $extraData['info'] = $infoLogList;
        return $extraData;
    }

    /**
     * 根据版本号处理成能识别的版本号  现在是//build: 5.0.21.18676 -> 5.0.21.18676
     * @param string $version
     * @return string|void
     */
    private function getVersionNum(string $version)
    {
        //先去掉build
        $versionList = explode(":", $version);
        $versionList = explode("-", $versionList[1]);
        if (empty($versionList[0])) {
            return;
        }
        return trim($versionList[0]);
    }

    /**
     * 获取升级历史日志文件名字信息
     * @param int $id
     * @return array
     */
    public function getUpdateHistory(int $id): array
    {
        $sql = "select node_uuid, log_time, log_file_path from bd_update_log where id = ?";
        $data = $this->dbSelect($sql, array($id));
        $nodeInfo = $this->getNodeInfo($data[0]['node_uuid']);
        return array(
            'node_name' => $nodeInfo['node_name'],
            'log_time' => $data[0]['log_time'],
            'node_uuid' => $data[0]['node_uuid'],
            'path' => $data[0]['log_file_path'],
        );
    }

    /**
     * 检查升级包是否存在
     * @param string $fileName
     */
    public function checkPatchExist(string $fileName)
    {
        $nodeuuid = $this->getMasterNode();
        $sql = "select * from bd_update_file where name = ? and node_uuid = ?";
        $data = $this->dbSelect($sql, array($fileName, $nodeuuid));
        if ($data) {
            exit($this->muOpResult(false, xphp_get_lang('UI_SETTINGS_UPDATE_UPLOAD_CHECK'), xphp_get_lang('UI_SETTINGS_UPDATE_UPLOAD_CHECK_ERROR'), "warning"));
        }
    }

    /**
     * 获取升级标志
     * @param string $nodeuuid
     * @param string $md5
     * @return bool
     */
    public function getUpdateFlag(string $nodeuuid, string $md5): bool
    {
        $sql = "select log_file_path from bd_update_log where node_uuid = ? and level1_md5 = ?";
        $data = $this->dbSelect($sql, array($nodeuuid, $md5));
        if ($data) {
            return true;
        }
        return false;
    }

    /**
     * 获取所有子节点状态
     */
    private function getChildNodeStatus($nodeuuid): array
    {
        $sql = "select module_type, online_flag from bd_module_server where node_uuid = ?";
        $data = $this->dbSelect($sql, array($nodeuuid));
        $flag = true;
        $module = array();

        foreach ($data as $d) {
            // 只要node_server不在线，子节点升级就不可用
            if ($d['module_type'] === 1000 && $d['online_flag'] == xphp_get_config('app', 'FLAG')['UNSET']) {
                $flag = false;
                $module[] = $d['module_type'];
            }
        }

        if (empty($data))
            $flag = false; //如果没有记录
        return array(
            'flag' => $flag,
            'module' => $module
        );
    }

    /**
     * 获取子节点的全部ip
     */
    private function getChildNodeIpList($nodeuuid): array
    {
        $sql = "select ip from bd_node_network where node_uuid = ?";
        $data = (array)$this->dbSelect($sql, array($nodeuuid));
        return array_column($data, 'ip');
    }

    /**
     * 升级补丁检查是否存在正在运行中的集群
     */
    private function checkRunningCluster()
    {
        $clusterRes = Cluster::instance()->getClusterConfig();
        $clusterData = $clusterRes['data'];
        // 配置了集群且状态不是停止或未知，则提示
        $allowStatus = [0, 1];
        if ($clusterData['config_flag'] && !in_array($clusterData['cluster_status'], $allowStatus)) {
            exit($this->muOpResult(false, xphp_get_lang('UI_SETTINGS_UPDATE_START_ERROR'), xphp_get_lang('UI_SETTINGS_UPDATE_RUNNING_CLUSTER_EXIST'), "warning"));
        }
    }
}
