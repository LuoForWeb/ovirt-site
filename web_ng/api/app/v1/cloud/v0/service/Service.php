<?php

namespace app\v1\cloud\v0\service;

use app\v1\common\service\Base;

/**
 * Class Service
 * @package app\v1\cloud\v0\service
 */
class Service extends Base
{
    /**
     * 公有云统一操作
     * @param string $nodeUuid       节点UUID
     * @param int    $hypervisorType 虚拟化类型
     * @param string $opName         操作码
     * @param string $msg            参数json
     * @param bool   $sync           是否是同步消息
     * @param bool   $command        是否命令模式
     * @return array|\xphp\db\Ambigous
     */
    public function unifyCloudService(
        string $nodeUuid,
        int $hypervisorType,
        string $opName,
        string $msg,
        bool $sync = false,
        bool $command = false
    ): array {
        return $this->mbVMMsg($nodeUuid, $hypervisorType, $opName, $msg, $sync, $command);
    }
}
