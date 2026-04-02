<?php

namespace app\v1\opcode;

use xphp\OpcodeHandler;

class KubeOpcode extends OpcodeHandler
{
    private static $instance = null;

    /**
     * 构造方法
     */
    public function __construct()
    {
        $this->initOpcodeLevel();
        $this->initOpcodeType();
        $this->initOpcode();
        $this->initOpcodeDes();
    }

    /**
     * 获取单例实例
     * @return KubeOpcode|null
     */
    public static function instance(): ?KubeOpcode
    {
        if (null == static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * 初始化opcode level
     * @return void
     */
    private function initOpcodeLevel()
    {
        $this->opcodeLevel = xphp_get_config('app')['OPCODE_LEVEL']['PRIVATE'];
    }

    /**
     * 初始化opcode type
     * @return void
     */
    private function initOpcodeType()
    {
        $this->opcodeType = array(
            'KUBE_OP_TYPE_UNKNOWN',
            'KUBE_OP_TYPE_KUBE_CLUSTER',
            'KUBE_OP_TYPE_KUBE_SCRIPT',
            'KUBE_OP_TYPE_KUBE_TASK_BACKUP',
            'KUBE_OP_TYPE_KUBE_TASK_RESTORE',
            'KUBE_OP_TYPE_KUBE_TASK_VIEW',
        );
    }

    /**
     * 初始化opcode
     * @return void
     */
    private function initOpcode()
    {
        $this->opcode = array(
            'KUBE_OP_TYPE_UNKNOWN' => array(),
            'KUBE_OP_TYPE_KUBE_CLUSTER' => array(
                'KUBE_CLUSTER_OP_CODE_UNKNOWN',
                'KUBE_CLUSTER_OP_CODE_ADD_CLUSTER',
                'KUBE_CLUSTER_OP_CODE_REMOTE_DEPLOYMENT',
                'KUBE_CLUSTER_OP_CODE_DELETE_CLUSTERS',
                'KUBE_CLUSTER_OP_CODE_REFRESH_CLUSTER',
                'KUBE_CLUSTER_OP_CODE_REFRESH_ALL_CLUSTERS',
            ),
            'KUBE_OP_TYPE_KUBE_SCRIPT' => array(
                'KUBE_SCRIPT_OP_CODE_UNKNOWN',
            ), // empty
            'KUBE_OP_TYPE_KUBE_TASK_BACKUP' => array(
                'KUBE_TASK_BACKUP_OP_CODE_UNKNOWN',
                'KUBE_TASK_BACKUP_OP_CODE_EXPLORER_CLUSTER_NAMESPACES',
                'KUBE_TASK_BACKUP_OP_CODE_EXPLORER_CLUSTER_RESOURCE_CATEGORIES',
                'KUBE_TASK_BACKUP_OP_CODE_EXPLORER_CLUSTER_APPS',
                'KUBE_TASK_BACKUP_OP_CODE_EXPLORER_CLUSTER_RESOURCES_KINDS',
                'KUBE_TASK_BACKUP_OP_CODE_EXPLORER_CLUSTER_RESOURCES_OF_KIND',
                'KUBE_TASK_BACKUP_OP_CODE_EXPLORER_CLUSTER_VIEW_RESOURCE',
            ),
            'KUBE_OP_TYPE_KUBE_TASK_RESTORE' => array(
                'KUBE_TASK_RESTORE_OP_CODE_UNKNOWN',
                'KUBE_TASK_RESTORE_OP_CODE_GET_BACKUP_TIMEPOINT_RESOURCES',
                'KUBE_TASK_RESTORE_OP_CODE_VIEW_BACKUP_TIMEPOINT_RESOURCE_YAML',
                'KUBE_TASK_RESTORE_OP_CODE_EXPLORER_CLUSTER_PVC_AND_STORAGE_CLASSES',
            ),
            'KUBE_OP_TYPE_KUBE_TASK_VIEW' => array(
                'KUBE_TASK_VIEW_OP_CODE_UNKNOWN',
            ), // empty
        );
    }

    /**
     * 初始化操作描述
     * @return void
     */
    private function initOpcodeDes()
    {
        $this->opDes = array(
            'KUBE_CLUSTER_OP_CODE_ADD_CLUSTER' => 'WEB_KUBE_CLUSTER_OP_CODE_ADD_CLUSTER',
            'KUBE_CLUSTER_OP_CODE_REMOTE_DEPLOYMENT' => 'WEB_KUBE_CLUSTER_OP_CODE_REMOTE_DEPLOYMENT',
            'KUBE_CLUSTER_OP_CODE_DELETE_CLUSTERS' => 'WEB_KUBE_CLUSTER_OP_CODE_DELETE_CLUSTERS',
            'KUBE_CLUSTER_OP_CODE_REFRESH_CLUSTER' => 'WEB_KUBE_CLUSTER_OP_CODE_REFRESH_CLUSTER',
            'KUBE_CLUSTER_OP_CODE_REFRESH_ALL_CLUSTERS' => 'WEB_KUBE_CLUSTER_OP_CODE_REFRESH_ALL_CLUSTERS',
            'KUBE_TASK_BACKUP_OP_CODE_EXPLORER_CLUSTER_NAMESPACES' => 'WEB_KUBE_TASK_BACKUP_OP_CODE_EXPLORER_CLUSTER_NAMESPACES',
            'KUBE_TASK_BACKUP_OP_CODE_EXPLORER_CLUSTER_RESOURCE_CATEGORIES' => 'WEB_KUBE_TASK_BACKUP_OP_CODE_EXPLORER_CLUSTER_RESOURCE_CATEGORIES',
            'KUBE_TASK_BACKUP_OP_CODE_EXPLORER_CLUSTER_APPS' => 'WEB_KUBE_TASK_BACKUP_OP_CODE_EXPLORER_CLUSTER_APPS',
            'KUBE_TASK_BACKUP_OP_CODE_EXPLORER_CLUSTER_RESOURCES_KINDS' => 'WEB_KUBE_TASK_BACKUP_OP_CODE_EXPLORER_CLUSTER_RESOURCES_KINDS',
            'KUBE_TASK_BACKUP_OP_CODE_EXPLORER_CLUSTER_RESOURCES_OF_KIND' => 'WEB_KUBE_TASK_BACKUP_OP_CODE_EXPLORER_CLUSTER_RESOURCES_OF_KIND',
            'KUBE_TASK_BACKUP_OP_CODE_EXPLORER_CLUSTER_VIEW_RESOURCE' => 'WEB_KUBE_TASK_BACKUP_OP_CODE_EXPLORER_CLUSTER_VIEW_RESOURCE',
            'KUBE_TASK_RESTORE_OP_CODE_GET_BACKUP_TIMEPOINT_RESOURCES' => 'WEB_KUBE_TASK_RESTORE_OP_CODE_GET_BACKUP_TIMEPOINT_RESOURCES',
            'KUBE_TASK_RESTORE_OP_CODE_VIEW_BACKUP_TIMEPOINT_RESOURCE_YAML' => 'WEB_KUBE_TASK_RESTORE_OP_CODE_VIEW_BACKUP_TIMEPOINT_RESOURCE_YAML',
            'KUBE_TASK_RESTORE_OP_CODE_EXPLORER_CLUSTER_PVC_AND_STORAGE_CLASSES' => 'WEB_KUBE_TASK_RESTORE_OP_CODE_EXPLORER_CLUSTER_PVC_AND_STORAGE_CLASSES',
        );
    }
}
