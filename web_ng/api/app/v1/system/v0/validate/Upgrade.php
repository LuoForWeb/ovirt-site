<?php

namespace app\v1\system\v0\validate;

use app\v1\common\validate\Base;

/**
 * 系统配置 - 系统升级 validate
 */
class Upgrade extends Base
{
    /**
     * 构造方法
     */
    public function __construct()
    {

        parent::__construct();

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'offset' => ['integer', 'min' => 0],
            'limit' => ['integer', 'min' => 1],
            'sort' => [],
            'order' => ['in' => ['asc', 'desc']],
            'node_uuids' => ['require', 'array'],
            'node_uuid' => ['require'],
            'name' => ['require'],
            'size' => ['integer','require'],
            'uuid' => ['require'],
            'md5' => ['require'],
            'uuids' => ['require', 'array'],
            'ids' => ['require'],
            'master_flag' => ['require', 'boolean'],
            'init_flag' => ['require', 'boolean'],
            'file_name' => ['require'],
            'file_size' => ['require', 'number'],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            // 获取升级包列表
            'getPatches' => ['offset', 'limit', 'sort', 'order'],
            // 获取升级历史
            'getHistory' => ['offset', 'limit', 'sort', 'order'],
            // 获取升级包状态日志
            'getPacketStatus' => ['node_uuids'],
            // 获取升级日志
            'getUpgradeLog' => ['node_uuid'],
            // 上传前检查
            'checkSystemSpaceEnough' => ['file_name', 'file_size'],
            // 上传升级包成功更新记录
            'updatePatchList' => ['name','size'],
            // 获取选中的升级包的信息
            'getSelectPatch' => ['uuid'],
            // 升级前检查
            'upgradeCheck' => ['node_uuids', 'master_flag', 'name', 'md5'],
            // 得到升级可用的备份节点列表
            'getUpdateNodeList' => ['uuid'],
            // 检查主节点是否已经升级
            'checkMasterUpdate' => ['node_uuid', 'patch_uuid'],
            // 执行升级
            'upgrade' => ['node_uuids', 'master_flag', 'name', 'uuid', 'md5'],
            // 删除升级包
            'deletePatches' => ['uuids'],
            // 删除升级历史
            'deleteHistory' => ['ids'],
            // 下载升级历史日志
            'downloadHistory' => ['uuid'],
            // 升级子节点
            'upgradeChild' => ['node_uuids', 'name'],
            // 获取升级信息
            'getUpgradeInfo' => ['uuid'],
        ];
    }
}
