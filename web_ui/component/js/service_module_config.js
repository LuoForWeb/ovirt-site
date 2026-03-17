/*
 * @note: 
 * @author: chenyunfeng@vinchin.com
 * @Description: 业务模块类型配置
 * @Date: 2025-04-15 17:13:50
 * @LastEditTime: 2025-06-20 15:59:20
 * @Version: 2.0
 * @copyright: Copyright 2025 vinchin.com
 */
// 备份业务
const SERVICE_BACKUP = [
    {
        "id": "vmprotect", // 虚拟化
        "name": LANG.UI_BACKUP_DATA_MODULE_VM,
        "icon": "vicon-overview-vm",
        "type": CONF.MODULE_TYPE.VM,
        "sub_type": 1,
    },
    {
        "id": "prcloud_protect", // 私有云
        "name": LANG.UI_BACKUP_DATA_MODULE_PRIVATE_CLOUD,
        "icon": "vicon-overview-private-cloud",
        "type": CONF.MODULE_TYPE.VM,
        "sub_type": 2,
    },
    {
        "id": "awsprotect", // 公有云
        "name": LANG.UI_BACKUP_DATA_MODULE_PUBLIC_CLOUD,
        "icon": "vicon-overview-plubic-cloud",
        "type": CONF.MODULE_TYPE.VM,
        "sub_type": 3,
    },
    {
        "id": "k8s_protect", // k8s
        "name": LANG.UI_BACKUP_DATA_MODULE_K8S,
        "icon": "vicon-overciew-k8s",
        "type": CONF.MODULE_TYPE.KUBERNETES,
        "sub_type": 0,
    },
    {
        "id": "fileprotect", // 文件
        "name": LANG.UI_BACKUP_DATA_MODULE_FS,
        "icon": "vicon-overview-file",
        "type": CONF.MODULE_TYPE.FS,
        "sub_type": 1,
    },
    {
        "id": "nas_protect", // nas
        "name": LANG.UI_BACKUP_DATA_MODULE_NAS,
        "icon": "vicon-overview-nas",
        "type": CONF.MODULE_TYPE.NAS,
        "sub_type": 2,
    },
    {
        "id": "hadoop_protect", // hadoop
        "name": LANG.UI_BACKUP_DATA_MODULE_HADOOP,
        "icon": "vicon-overview-hadoop",
        "type": CONF.MODULE_TYPE.FS,
        "sub_type": 3,
    },
    {
        "id": "obs_protect", // obs
        "name": LANG.UI_BACKUP_DATA_MODULE_OBS,
        "icon": "vicon-overview-obs",
        "type": CONF.MODULE_TYPE.FS,
        "sub_type": 4,
    },
    {
        "id": "db_protect", // 数据库
        "name": LANG.UI_BACKUP_DATA_MODULE_DB,
        "icon": "vicon-overview-database",
        "type": CONF.MODULE_TYPE.DB,
        "sub_type": 0,
    },
    {
        "id": "office365_protect", // M365
        "name": LANG.UI_BACKUP_DATA_MODULE_M365,
        "icon": "vicon-overview-m365",
        "type": CONF.MODULE_TYPE.M365,
        "sub_type": 0,
    },
    {
        "id": "complete_machine", // 整机(磁盘)
        "name": LANG.UI_COPY_MODULE_LABEL_COMPLETE_OS,
        "icon": "vicon-overview-complete-machine",
        "type": CONF.MODULE_TYPE.OS,
        "sub_type": 1,
    },
    {
        "id": "osbackup", // 整机(卷)
        "name": LANG.UI_COPY_MODULE_LABEL_REEL_OS,
        "icon": "vicon-overview-volume",
        "type": CONF.MODULE_TYPE.OS,
        "sub_type": 0,
    },
];
// 连续数据保护
const SERVICE_CDP = [
    {
        "id": "complete_cdp_backup", // 整机(磁盘)
        "name": LANG.UI_COPY_MODULE_LABEL_COMPLETE_OS,
        "icon": "vicon-overview-complete-machine",
        "type": CONF.MODULE_TYPE.VOL_CDP,
        "storage_location": 1,
        "dev_type": 2,
        "task_type": CONF.TASK_TYPE.VOL_CDP_BACKUP,
        "sub_type": 0,
    },
    {
        "id": "vol_cdp_backup", // 整机(卷)
        "name": LANG.UI_COPY_MODULE_LABEL_REEL_OS,
        "icon": "vicon-overview-volume",
        "type": CONF.MODULE_TYPE.VOL_CDP,
        "storage_location": 1,
        "dev_type": 1,
        "task_type": CONF.TASK_TYPE.VOL_CDP_BACKUP,
        "sub_type": 0,
    },
];
const SERVICE_REPLICATION = [
    {
        "id": "machine_copy", // 整机(磁盘)
        "name": LANG.UI_COPY_MODULE_LABEL_COMPLETE_OS,
        "icon": "vicon-overview-complete-machine",
        "type": CONF.MODULE_TYPE.VOL_CDP,
        "storage_location": 2,
        "dev_type": 2,
        "task_type": CONF.TASK_TYPE.VOL_CDP_REPLICATION,
        "sub_type": 0,
    },
    {
        "id": "vol_cdp_copy", // 整机(卷)
        "name": LANG.UI_COPY_MODULE_LABEL_REEL_OS,
        "icon": "vicon-overview-database",
        "type": CONF.MODULE_TYPE.VOL_CDP,
        "storage_location": 2,
        "dev_type": 1,
        "task_type": CONF.TASK_TYPE.VOL_CDP_REPLICATION,
        "sub_type": 0,
    },
    {
    	"id": "file_copy_protect",  // 文件复制
    	"name": LANG.UI_BACKUP_DATA_MODULE_FS,
    	"icon": "vicon-fuzhi",
    	"type": CONF.MODULE_TYPE.FILE_COPY,
        "sub_type": 0,
        "task_type": CONF.TASK_TYPE.FILE_COPY,
    },
    {
        "id": "dbcdpcopy", // 数据库复制
        "name": LANG.UI_BACKUP_DATA_MODULE_DB,
        "icon": "vicon-tongbu",
        "type": CONF.MODULE_TYPE.DB_CDP,
        "task_type": CONF.TASK_TYPE.DB_CDP_SYN,
        "sub_type": 0,
    },
];