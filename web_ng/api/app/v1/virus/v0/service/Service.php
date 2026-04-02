<?php

namespace app\v1\virus\v0\service;

use app\v1\common\service\Base;
use app\v1\resources\v0\logic\Node;

/**
 * note           通信 service
 * @author       @vinchin.com
 * @date         2023/4/10 10:52
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Service extends Base
{
    /**
     * wu
     * @param string $taskUuid  任务uuid
     * @param int    $subModule 子模块编号
     * @param string $opName    操作码
     * @param array  $msg       消息
     * @param bool   $sync      是否同步
     * @param bool   $command   是否命令
     * @return void
     */
    /**
     * mbFSMsg
     * @param $nodeUuid 节点uuid
     * @param $opName   操作码
     * @param $msg      内容
     * @param bool $sync     异步
     * @return array
     */
    public function applyVirusService(array $params): array
    {
        $opcodeName = 'TOOL_VIRUS_OP_MANAGE_VIRUS_LIB';
        $msg = [
            "manage_virus_lib_op" => $params['manage_virus_lib_op'],
            "lib_type" => $params['lib_type']
        ];
        return $this->mbToolMsg($this->getLocalNodeUuid(), $opcodeName, json_encode($msg), true);
    }
    public function updateVirusService(array $params): array
    {
        $opcodeName = 'TOOL_VIRUS_OP_OFFLINE_UPDATE_VIRUS_LIB';
        $msg = [
            "virus_lib_upload_path" => $params['virus_lib_upload_path'],		// 病毒库上传路径
            "virus_lib_file_name" => $params['virus_lib_file_name'],			// 病毒库名称
            "lib_type" => $params['lib_type'],
        ];
        return $this->mbToolMsg($this->getLocalNodeUuid(), $opcodeName, json_encode($msg), true);
    }

    /**
     * @param string $keyLibUploadPath  授权文件上传路径
     * @param string $keyLibFileName    授权文件名称
     * @param int    $libType           库类型
     * @return array
     */
    public function uploadAuthorizationFileService(string $keyLibUploadPath, string $keyLibFileName, int $libType): array
    {
        $opcodeName = 'TOOL_VIRUS_OP_UPLOAD_AUTHORIZATION_FILE';
        $msg = [
            "key_file_upload_path" => $keyLibUploadPath,		// 授权文件上传路径
            "key_file_name" => $keyLibFileName,			// 授权文件名称
            "lib_type" => $libType,
        ];
        return $this->mbToolMsg($this->getLocalNodeUuid(), $opcodeName, json_encode($msg), true);
    }
}
