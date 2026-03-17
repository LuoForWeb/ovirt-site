/*
 * @Author: ChengJiaFu
 * @Date: 2025-04-16 10:55:16
 * @Description: 系统路由配置项
 * @version: 1.1
 */
var ROUTE = {
    // 所有模块对应任务的树形路由数组
	MODULE_TASK_ROUTE_TREE_DATA: [
		{
			id: 'timing_backup',
			name: LANG.UI_VISUAL_BACKUP,
			level: 1,
			route: false,
			routeId: '',
			routeUrl: '',
			children: [
				{
					id: 'vmprotect',
					name: LANG.UI_BACKUP_DATA_MODULE_VM,
					level: 2,
					route: false,
					routeId: '',
					routeUrl: '',
					children: [
						{
							id: 'vmprotect',
							name: LANG.UI_SEARCH_BACKUP_TASK,
							level: 3,
							route: true,
							routeId: 'vmprotect',
							routeUrl: './content/vm/vmbackup.php',
							children: []
						},
						{
							id: 'vmrecover',
							name: LANG.UI_SEARCH_RECOVER_TASK,
							level: 3,
							route: true,
							routeId: 'recovery',
							routeUrl: './content/vm/vmrecover.php',
							children: []
						},
						{
							id: 'copy_protect',
							name: LANG.UI_SEARCH_COPY_TASK,
							level: 3,
							route: true,
							routeId: 'copy_protect',
							routeUrl: './content/copy/copy.php?module_type=2&sub_module_type=1',
							children: []
						},
						{
							id: 'archive_new',
							name: LANG.UI_SEARCH_ARCHIVE_TASK,
							level: 3,
							route: true,
							routeId: 'archive_new',
							routeUrl: './content/archive/addarchive.php?module_type=2&sub_module_type=1',
							children: []
						},
						{
							id: 'vm_instant_recovery',
							name: LANG.UI_VISUAL_INSTANT_TASK_NAME,
							level: 3,
							route: true,
							routeId: 'recovery',
							routeUrl: './content/platform/recovery/instantaneous.php?recovery_type=vm&subtype=1',
							children: []
						},
						{
							id: 'vm_grain_recovery',
							name: LANG.UI_VISUAL_GRAIN_TASK_NAME,
							level: 3,
							route: true,
							routeId: 'recovery',
							routeUrl: './content/platform/recovery/graininess.php?recovery_type=vm&subtype=1',
							children: []
						},
						{
							id: 'data_verification',
							name: LANG.UI_DATA_VERIFY_TASK,
							level: 3,
							route: true,
							routeId: 'add_verification_job',
							routeUrl: './content/platform/dataverification/add_verification_job.php',
							children: []
						},
						{
							id: 'vm_platform_recovery',
							name: LANG.UI_PLATFORM_RECOVERY_NAME,
							level: 3,
							route: true,
							routeId: 'recovery',
							routeUrl: './content/platform/recovery/platform.php?recovery_type=vm&subtype=1',
							children: []
						}
					]
				},
				{
					id: 'prcloud_protect',
					name: LANG.UI_PUBLIC_PRIVATE_CLOUD,
					level: 2,
					route: false,
					routeId: '',
					routeUrl: '',
					children: [
						{
							id: 'prcloud_protect',
							name: LANG.UI_SEARCH_BACKUP_TASK,
							level: 3,
							route: true,
							routeId: 'prcloud_protect',
							routeUrl: './content/vm/vmbackup.php?sub_module_type=2',
							children: []
						},
						{
							id: 'vm_prcloud_recovery',
							name: LANG.UI_SEARCH_RECOVER_TASK,
							level: 3,
							route: true,
							routeId: 'recovery',
							routeUrl: './content/vm/vmrecover.php?sub_module_type=2',
							children: []
						},
						{
							id: 'copy_protect',
							name: LANG.UI_SEARCH_COPY_TASK,
							level: 3,
							route: true,
							routeId: 'copy_protect',
							routeUrl: './content/copy/copy.php?module_type=2&sub_module_type=2',
							children: []
						},
						{
							id: 'archive_new',
							name: LANG.UI_SEARCH_ARCHIVE_TASK,
							level: 3,
							route: true,
							routeId: 'archive_new',
							routeUrl: './content/archive/addarchive.php?module_type=2&sub_module_type=2',
							children: []
						},
						{
							id: 'vm_prcloud_instant_recovery',
							name: LANG.UI_VISUAL_INSTANT_TASK_NAME,
							level: 3,
							route: true,
							routeId: 'recovery',
							routeUrl: './content/platform/recovery/instantaneous.php?recovery_type=vm&subtype=2',
							children: []
						},
						{
							id: 'vm_prcloud_graininess_recovery',
							name: LANG.UI_VISUAL_GRAIN_TASK_NAME,
							level: 3,
							route: true,
							routeId: 'recovery',
							routeUrl: './content/platform/recovery/graininess.php?recovery_type=vm&subtype=2',
							children: []
						},
						{
							id: 'data_verification',
							name: LANG.UI_DATA_VERIFY_TASK,
							level: 3,
							route: true,
							routeId: 'add_verification_job',
							routeUrl: './content/platform/dataverification/add_verification_job.php',
							children: []
						},
						{
							id: 'vm_prcloud_platform_recovery',
							name: LANG.UI_PLATFORM_RECOVERY_NAME,
							level: 3,
							route: true,
							routeId: 'recovery',
							routeUrl: './content/platform/recovery/platform.php?recovery_type=vm&subtype=2',
							children: []
						},
					]
				},
				{
					id: 'awsprotect',
					name: LANG.UI_PUBLIC_PUBLIC_CLOUD,
					level: 2,
					route: false,
					routeId: '',
					routeUrl: '',
					children: [
						{
							id: 'awsprotect',
							name: LANG.UI_SEARCH_BACKUP_TASK,
							level: 3,
							route: true,
							routeId: 'awsprotect',
							routeUrl: './content/aws/awsbackup.php',
							children: []
						},
						{
							id: 'vm_awsprotect_recover',
							name: LANG.UI_SEARCH_RECOVER_TASK,
							level: 3,
							route: true,
							routeId: 'recovery',
							routeUrl: './content/aws/awsrecover.php',
							children: []
						},
						{
							id: 'copy_protect',
							name: LANG.UI_SEARCH_COPY_TASK,
							level: 3,
							route: true,
							routeId: 'copy_protect',
							routeUrl: './content/copy/copy.php?module_type=2&sub_module_type=3',
							children: []
						},
						{
							id: 'archive_new',
							name: LANG.UI_SEARCH_ARCHIVE_TASK,
							level: 3,
							route: true,
							routeId: 'archive_new',
							routeUrl: './content/archive/addarchive.php?module_type=2&sub_module_type=3',
							children: []
						},
						{
							id: 'vm_awsprotect_instant_recovery',
							name: LANG.UI_VISUAL_INSTANT_TASK_NAME,
							level: 3,
							route: true,
							routeId: 'recovery',
							routeUrl: './content/platform/recovery/instantaneous.php?recovery_type=vm&subtype=3',
							children: []
						},
						{
							id: 'vm_awsprotect_grain_recovery',
							name: LANG.UI_VISUAL_GRAIN_TASK_NAME,
							level: 3,
							route: true,
							routeId: 'recovery',
							routeUrl: './content/platform/recovery/graininess.php?recovery_type=vm&subtype=3',
							children: []
						},
						{
							id: 'data_verification',
							name: LANG.UI_DATA_VERIFY_TASK,
							level: 3,
							route: true,
							routeId: 'add_verification_job',
							routeUrl: './content/platform/dataverification/add_verification_job.php',
							children: []
						},
						{
							id: 'vm_awsprotect_platform_recovery',
							name: LANG.UI_PLATFORM_RECOVERY_NAME,
							level: 3,
							route: true,
							routeId: 'recovery',
							routeUrl: './content/platform/recovery/platform.php?recovery_type=vm&subtype=3',
							children: []
						},
					]
				},
				{
					id: 'complete_machine',
					name: LANG.UI_BACKUP_DATA_MODULE_OS,
					level: 2,
					route: false,
					routeId: '',
					routeUrl: '',
					children: [
						{
							id: 'complete_machine',
							name: LANG.UI_SEARCH_BACKUP_TASK,
							level: 3,
							route: true,
							routeId: 'complete_machine',
							routeUrl: './content/complete_machine_os/machine_os_backup.php',
							children: []
						},
						{
							id: 'machine_complete_recovery',
							name: LANG.UI_SEARCH_RECOVER_TASK,
							level: 3,
							route: true,
							routeId: 'recovery',
							routeUrl: './content/complete_machine_os/machine_os_recover.php',
							children: []
						},
						{
							id: 'copy_protect',
							name: LANG.UI_SEARCH_COPY_TASK,
							level: 3,
							route: true,
							routeId: 'copy_protect',
							routeUrl: './content/copy/copy.php?module_type=5&sub_module_type=1',
							children: []
						},
						{
							id: 'archive_new',
							name: LANG.UI_SEARCH_ARCHIVE_TASK,
							level: 3,
							route: true,
							routeId: 'archive_new',
							routeUrl: './content/archive/addarchive.php?module_type=5&sub_module_type=1',
							children: []
						},
						{
							id: 'machine_complete_instant_recovery',
							name: LANG.UI_VISUAL_INSTANT_TASK_NAME,
							level: 3,
							route: true,
							routeId: 'recovery',
							routeUrl: './content/platform/recovery/instantaneous.php?recovery_type=os',
							children: []
						},
						{
							id: 'machine_complete_grain_recovery',
							name: LANG.UI_VISUAL_GRAIN_TASK_NAME,
							level: 3,
							route: true,
							routeId: 'recovery',
							routeUrl: './content/platform/recovery/graininess.php?recovery_type=os',
							children: []
						},
						{
							id: 'data_verification',
							name: LANG.UI_DATA_VERIFY_TASK,
							level: 3,
							route: true,
							routeId: 'add_verification_job',
							routeUrl: './content/platform/dataverification/add_verification_job.php',
							children: []
						},
						{
							id: 'machine_complete_platform_recovery',
							name: LANG.UI_PLATFORM_RECOVERY_NAME,
							level: 3,
							route: true,
							routeId: 'recovery',
							routeUrl: './content/platform/recovery/platform.php?recovery_type=os',
							children: []
						},
					]
				},
				{
					id: 'osbackup',
					name: LANG.UI_VOL_CDP_RECOVER_VOL,
					level: 2,
					route: false,
					routeId: '',
					routeUrl: '',
					children: [
						{
							id: 'osbackup',
							name: LANG.UI_SEARCH_BACKUP_TASK,
							level: 3,
							route: true,
							routeId: 'osbackup',
							routeUrl: './content/os/osbackup.php',
							children: []
						},
						{
							id: 'os_recovery',
							name: LANG.UI_SEARCH_RECOVER_TASK,
							level: 3,
							route: true,
							routeId: 'recovery',
							routeUrl: './content/os/osrecover.php',
							children: []
						},
						{
							id: 'copy_protect',
							name: LANG.UI_SEARCH_COPY_TASK,
							level: 3,
							route: true,
							routeId: 'copy_protect',
							routeUrl: './content/copy/copy.php?module_type=5&sub_module_type=0',
							children: []
						},
						{
							id: 'archive_new',
							name: LANG.UI_SEARCH_ARCHIVE_TASK,
							level: 3,
							route: true,
							routeId: 'archive_new',
							routeUrl: './content/archive/addarchive.php?module_type=5&sub_module_type=0',
							children: []
						},
					]
				},
				{
					id: 'filebackup',
					name: LANG.UI_FILE_FILE,
					level: 2,
					route: false,
					routeId: '',
					routeUrl: '',
					children: [
						{
							id: 'filebackup',
							name: LANG.UI_SEARCH_BACKUP_TASK,
							level: 3,
							route: true,
							routeId: 'filebackup',
							routeUrl: './content/fs/filebackup.php',
							children: []
						},
						{
							id: 'file_recovery',
							name: LANG.UI_SEARCH_RECOVER_TASK,
							level: 3,
							route: true,
							routeId: 'recovery',
							routeUrl: './content/fs/filerecover.php',
							children: []
						},
						{
							id: 'copy_protect',
							name: LANG.UI_SEARCH_COPY_TASK,
							level: 3,
							route: true,
							routeId: 'copy_protect',
							routeUrl: './content/copy/copy.php?module_type=3&sub_module_type=1',
							children: []
						},
						{
							id: 'data_verification',
							name: LANG.UI_DATA_VERIFY_TASK,
							level: 3,
							route: true,
							routeId: 'add_verification_job',
							routeUrl: './content/platform/dataverification/add_verification_job.php',
							children: []
						},
					]
				},
				{
					id: 'nas_protect',
					name: 'NAS',
					level: 2,
					route: false,
					routeId: '',
					routeUrl: '',
					children: [
						{
							id: 'nas_protect',
							name: LANG.UI_SEARCH_BACKUP_TASK,
							level: 3,
							route: true,
							routeId: 'nas_protect',
							routeUrl: './content/nas/nasbackup.php',
							children: []
						},
						{
							id: 'nas_recovery',
							name: LANG.UI_SEARCH_RECOVER_TASK,
							level: 3,
							route: true,
							routeId: 'recovery',
							routeUrl: './content/nas/nasrecover.php',
							children: []
						},
						{
							id: 'copy_protect',
							name: LANG.UI_SEARCH_COPY_TASK,
							level: 3,
							route: true,
							routeId: 'copy_protect',
							routeUrl: './content/copy/copy.php?module_type=11&sub_module_type=2',
							children: []
						},
						{
							id: 'data_verification',
							name: LANG.UI_DATA_VERIFY_TASK,
							level: 3,
							route: true,
							routeId: 'add_verification_job',
							routeUrl: './content/platform/dataverification/add_verification_job.php',
							children: []
						},
					]
				},
				{
					id: 'hadoop_protect',
					name: 'Hadoop',
					level: 2,
					route: false,
					routeId: '',
					routeUrl: '',
					children: [
						{
							id: 'hadoop_protect',
							name: LANG.UI_SEARCH_BACKUP_TASK,
							level: 3,
							route: true,
							routeId: 'hadoop_protect',
							routeUrl: './content/hadoop/hadoop_backup.php',
							children: []
						},
						{
							id: 'hadoop_recovery',
							name: LANG.UI_SEARCH_RECOVER_TASK,
							level: 3,
							route: true,
							routeId: 'recovery',
							routeUrl: './content/hadoop/hadoop_recovery.php',
							children: []
						},
						{
							id: 'copy_protect',
							name: LANG.UI_SEARCH_COPY_TASK,
							level: 3,
							route: true,
							routeId: 'copy_protect',
							routeUrl: './content/copy/copy.php?module_type=3&sub_module_type=3',
							children: []
						},
						{
							id: 'data_verification',
							name: LANG.UI_DATA_VERIFY_TASK,
							level: 3,
							route: true,
							routeId: 'add_verification_job',
							routeUrl: './content/platform/dataverification/add_verification_job.php',
							children: []
						},
					]
				},
				{
					id: 'obs_protect',
					name: LANG.UI_VISUAL_OBS,
					level: 2,
					route: false,
					routeId: '',
					routeUrl: '',
					children: [
						{
							id: 'obs_protect',
							name: LANG.UI_SEARCH_BACKUP_TASK,
							level: 3,
							route: true,
							routeId: 'obs_protect',
							routeUrl: './content/s3/obsbackup.php',
							children: []
						},
						{
							id: 'obs_recovery',
							name: LANG.UI_SEARCH_RECOVER_TASK,
							level: 3,
							route: true,
							routeId: 'recovery',
							routeUrl: './content/s3/obsrecover.php',
							children: []
						},
						{
							id: 'copy_protect',
							name: LANG.UI_SEARCH_COPY_TASK,
							level: 3,
							route: true,
							routeId: 'copy_protect',
							routeUrl: './content/copy/copy.php?module_type=3&sub_module_type=4',
							children: []
						},
						{
							id: 'data_verification',
							name: LANG.UI_DATA_VERIFY_TASK,
							level: 3,
							route: true,
							routeId: 'add_verification_job',
							routeUrl: './content/platform/dataverification/add_verification_job.php',
							children: []
						},
					]
				},
				{
					id: 'office365_protect',
					name: 'Microsoft 365',
					level: 2,
					route: false,
					routeId: '',
					routeUrl: '',
					children: [
						{
							id: 'office365_protect',
							name: LANG.UI_SEARCH_BACKUP_TASK,
							level: 3,
							route: true,
							routeId: 'office365_protect',
							routeUrl: './content/exchange/exchange_backup.php',
							children: []
						},
						{
							id: 'exchange_recovery',
							name: LANG.UI_SEARCH_RECOVER_TASK,
							level: 3,
							route: true,
							routeId: 'recovery',
							routeUrl: './content/exchange/exchange_recover.php',
							children: []
						},
						{
							id: 'copy_protect',
							name: LANG.UI_SEARCH_COPY_TASK,
							level: 3,
							route: true,
							routeId: 'copy_protect',
							routeUrl: './content/copy/copy.php?module_type=14&sub_module_type=0',
							children: []
						},
						{
							id: 'data_verification',
							name: LANG.UI_DATA_VERIFY_TASK,
							level: 3,
							route: true,
							routeId: 'add_verification_job',
							routeUrl: './content/platform/dataverification/add_verification_job.php',
							children: []
						},
					]
				},
				{
					id: 'k8s_protect',
					name: LANG.UI_BACKUP_DATA_MODULE_K8S,
					level: 2,
					route: false,
					routeId: '',
					routeUrl: '',
					children: [
						{
							id: 'k8s_protect',
							name: LANG.UI_SEARCH_BACKUP_TASK,
							level: 3,
							route: true,
							routeId: 'k8s_protect',
							routeUrl: './content/kubernetes/kubernetes_backup.php',
							children: []
						},
						{
							id: 'k8s_recovery',
							name: LANG.UI_SEARCH_RECOVER_TASK,
							level: 3,
							route: true,
							routeId: 'recovery',
							routeUrl: './content/kubernetes/kubernetes_recovery.php',
							children: []
						},
						{
							id: 'copy_protect',
							name: LANG.UI_SEARCH_COPY_TASK,
							level: 3,
							route: true,
							routeId: 'copy_protect',
							routeUrl: './content/copy/copy.php?module_type=28&sub_module_type=0',
							children: []
						},
						// {
						// 	id: 'data_verification',
						// 	name: LANG.UI_DATA_VERIFY_TASK,
						// 	level: 3,
						// 	route: true,
						// 	routeId: 'add_verification_job',
						// 	routeUrl: './content/platform/dataverification/add_verification_job.php',
						// 	children: []
						// },
					]
				},
				{
					id: 'db_protect',
					name: LANG.UI_AGENT_MODULE_DB,
					level: 2,
					route: false,
					routeId: '',
					routeUrl: '',
					children: [
						{
							id: 'db_protect',
							name: LANG.UI_SEARCH_BACKUP_TASK,
							level: 3,
							route: true,
							routeId: 'db_protect',
							routeUrl: './content/dbprotect/dbbackup.php',
							children: []
						},
						{
							id: 'db_recovery',
							name: LANG.UI_SEARCH_RECOVER_TASK,
							level: 3,
							route: true,
							routeId: 'recovery',
							routeUrl: './content/dbprotect/dbrecover.php',
							children: []
						},
						{
							id: 'copy_protect',
							name: LANG.UI_SEARCH_COPY_TASK,
							level: 3,
							route: true,
							routeId: 'copy_protect',
							routeUrl: './content/copy/copy.php?module_type=4&sub_module_type=0',
							children: []
						},
						{
							id: 'db_drill',
							name: LANG.UI_DRILLS_TASK,
							level: 3,
							route: true,
							routeId: 'recovery',
							routeUrl: './content/dbprotect/dbrecover.php?timepoint_recovery_type=2',
							children: []
						},
						{
							id: 'data_verification',
							name: LANG.UI_DATA_VERIFY_TASK,
							level: 3,
							route: true,
							routeId: 'add_verification_job',
							routeUrl: './content/platform/dataverification/add_verification_job.php',
							children: []
						},
					]
				},
                {
					id: 'cbrbackup',
                    name: LANG.UI_ADD_TASK_TYPE_CLOUD_SYNC,
                    level: 3,
                    route: true,
                    routeId: 'cbrbackup',
                    routeUrl: './content/cbr/cbrbackup.php',
                    children: []
                }
			]
		},
		{
			id: 'real_time_protect',
			name: LANG.UI_REPORY_CDP,
			level: 1,
			route: false,
			routeId: '',
			routeUrl: '',
			children: [
				{
					id: 'complete_cdp_backup',
					name: LANG.UI_BACKUP_DATA_MODULE_OS,
					level: 2,
					route: false,
					routeId: '',
					routeUrl: '',
					children: [
						{
							id: 'complete_cdp_backup',
							name: LANG.UI_SEARCH_BACKUP_TASK,
							level: 3,
							route: true,
							routeId: 'complete_cdp_backup',
							routeUrl: './content/complete_machine_volcdp/cm_volcdp_backup.php?task_type=backup',
							children: []
						},
						{
							id: 'machine_complete_volcdp_recovery',
							name: LANG.UI_SEARCH_RECOVER_TASK,
							level: 3,
							route: true,
							routeId: 'recovery',
							routeUrl: './content/complete_machine_volcdp/cm_volcdp_recovery.php',
							children: []
						},
						{
							id: 'vol_cdp_complete_takeover',
							name: LANG.UI_TAKEOVER_TASK,
							level: 3,
							route: true,
							routeId: 'vol_cdp_complete_takeover',
							routeUrl: './content/complete_machine_volcdp/cm_volcdp_takeover.php',
							children: []
						},
						{
							id: 'machine_complete_volcdp_grain_recovery',
							name: LANG.UI_VISUAL_GRAIN_TASK_NAME,
							level: 3,
							route: true,
							routeId: 'recovery',
							routeUrl: './content/platform/recovery/graininess.php?recovery_type=cdp',
							children: []
						},
						{
							id: 'data_verification',
							name: LANG.UI_DATA_VERIFY_TASK,
							level: 3,
							route: true,
							routeId: 'add_verification_job',
							routeUrl: './content/platform/dataverification/add_verification_job.php',
							children: []
						},
						{
							id: 'machine_complete_volcdp_platform_recovery',
							name: LANG.UI_PLATFORM_RECOVERY_NAME,
							level: 3,
							route: true,
							routeId: 'recovery',
							routeUrl: './content/platform/recovery/platform.php?recovery_type=cdp',
							children: []
						}
					]
				},
				{
					id: 'vol_cdp_backup',
					name: LANG.UI_VOL_CDP_RECOVER_VOL,
					level: 2,
					route: false,
					routeId: '',
					routeUrl: '',
					children: [
						{
							id: 'vol_cdp_backup',
							name: LANG.UI_SEARCH_BACKUP_TASK,
							level: 3,
							route: true,
							routeId: 'vol_cdp_backup',
							routeUrl: './content/volcdp/vol_cdp_backup.php?task_type=backup',
							children: []
						},
						{
							id: 'p_vol_cdp_recovery',
							name: LANG.UI_SEARCH_RECOVER_TASK,
							level: 3,
							route: true,
							routeId: 'recovery',
							routeUrl: './content/volcdp/vol_cdp_recover.php',
							children: []
						},
						{
							id: 'vol_cdp_takeover',
							name: LANG.UI_TAKEOVER_TASK,
							level: 3,
							route: true,
							routeId: 'vol_cdp_takeover',
							routeUrl: './content/volcdp/vol_cdp_takeover.php',
							children: []
						}
					]
				}
			]
		},
		{
			id: 'data_copy',
			name: LANG.UI_CM_CDP_REPLICATION,
			level: 1,
			route: false,
			routeId: '',
			routeUrl: '',
			children: [
				{
					id: 'machine_copy',
					name: LANG.UI_BACKUP_DATA_MODULE_OS,
					level: 2,
					route: false,
					routeId: '',
					routeUrl: '',
					children: [
						{
							id: 'machine_copy',
							name: LANG.UI_COPY_TASK,
							level: 3,
							route: true,
							routeId: 'machine_copy',
							routeUrl: './content/complete_machine_volcdp/cm_volcdp_backup.php?task_type=copy',
							children: []
						},
						{
							id: 'machine_complete_volcdp_recovery',
							name: LANG.UI_SEARCH_RECOVER_TASK,
							level: 3,
							route: true,
							routeId: 'recovery',
							routeUrl: './content/complete_machine_volcdp/cm_volcdp_recovery.php',
							children: []
						},
						{
							id: 'machine_copy',
							name: LANG.UI_TAKEOVER_TASK,
							level: 3,
							route: true,
							routeId: 'task',
							routeUrl: './content/complete_machine_volcdp/cm_volcdp_takeover.php',
							children: []
						},
						{
							id: 'machine_complete_volcdp_grain_recovery',
							name: LANG.UI_VISUAL_GRAIN_TASK_NAME,
							level: 3,
							route: true,
							routeId: 'recovery',
							routeUrl: './content/platform/recovery/graininess.php?recovery_type=cdp',
							children: []
						},
						{
							id: 'data_verification',
							name: LANG.UI_DATA_VERIFY_TASK,
							level: 3,
							route: true,
							routeId: 'add_verification_job',
							routeUrl: './content/platform/dataverification/add_verification_job.php',
							children: []
						},
						{
							id: 'machine_complete_volcdp_platform_recovery',
							name: LANG.UI_PLATFORM_RECOVERY_NAME,
							level: 3,
							route: true,
							routeId: 'recovery',
							routeUrl: './content/platform/recovery/platform.php?recovery_type=cdp',
							children: []
						}
					]
				},
				{
					id: 'vol_cdp_copy',
					name: LANG.UI_VOL_CDP_RECOVER_VOL,
					level: 2,
					route: false,
					routeId: '',
					routeUrl: '',
					children: [
						{
							id: 'vol_cdp_copy',
							name: LANG.UI_COPY_TASK,
							level: 3,
							route: true,
							routeId: 'vol_cdp_copy',
							routeUrl: './content/volcdp/vol_cdp_backup.php?task_type=copy',
							children: []
						},
						{
							id: 'p_vol_cdp_recovery',
							name: LANG.UI_SEARCH_RECOVER_TASK,
							level: 3,
							route: true,
							routeId: 'recovery',
							routeUrl: './content/volcdp/vol_cdp_recover.php',
							children: []
						},
						{
							id: 'vol_cdp_copy',
							name: LANG.UI_TAKEOVER_TASK,
							level: 3,
							route: true,
							routeId: 'task',
							routeUrl: './content/volcdp/cm_cdp_job_details.php?type=34',
							children: []
						}
					]
				},
				{
					id: 'dbcdpcopy',
					name: LANG.UI_BACKUP_DATA_MODULE_DB,
					level: 2,
					route: false,
					routeId: '',
					routeUrl: '',
					children: [
						{
							id: 'dbcdpcopy',
							name: LANG.UI_COPY_TASK,
							level: 3,
							route: true,
							routeId: 'dbcdpcopy',
							routeUrl: './content/dbcdp/dbcdp_backup.php',
							children: []
						},
						{
							id: 'dbcdp_recovery',
							name: LANG.UI_SEARCH_RECOVER_TASK,
							level: 3,
							route: true,
							routeId: 'recovery',
							routeUrl: './content/dbcdp/dbcdp_recover.php',
							children: []
						}
					]
				},
				{
					id: 'file_copy_protect',
					name: LANG.UI_FILE_FILE,
					level: 2,
					route: false,
					routeId: '',
					routeUrl: '',
					children: [
						{
							id: 'file_copy_protect',
							name: LANG.UI_COPY_TASK,
							level: 3,
							route: true,
							routeId: 'file_copy_protect',
							routeUrl: './content/filecopy/file_copy.php',
							children: []
						}
					]
				}
			]
		}
	],

	// GMP项目备份任务路由数组
	GMP_BACKUP_ROUTE_TREE_DATA: [
		{
			id: 'vmprotect',
			name: LANG.UI_SETTING_VIRTUAL_MACHINE_PROTECTION,
			level: 2,
			route: true,
			routeId: 'vmprotect',
			routeUrl: './content/vm/vmbackup.php',
			children: []
		},
		{
			id: 'prcloud_protect',
			name: LANG.UI_PUBLIC_PRIVATE_CLOUD_PROTECTED,
			level: 2,
			route: true,
			routeId: 'prcloud_protect',
			routeUrl: './content/vm/vmbackup.php?sub_module_type=2',
			children: []
		},
		{
			id: 'awsprotect',
			name: LANG.UI_VISUAL_PUBLIC_CLOUD,
			level: 2,
			route: true,
			routeId: 'awsprotect',
			routeUrl: './content/aws/awsbackup.php',
			children: []
		},
		{
			id: 'complete_machine',
			name: LANG.UI_BACKUP_DATA_MODULE_OS,
			level: 2,
			route: true,
			routeId: 'complete_machine',
			routeUrl: './content/complete_machine_os/machine_os_backup.php',
			children: []
		},
		{
			id: 'osbackup',
			name: LANG.UI_VOL_CDP_RECOVER_VOL,
			level: 2,
			route: true,
			routeId: 'osbackup',
			routeUrl: './content/os/osbackup.php',
			children: []
		},
		{
			id: 'filebackup',
			name: LANG.UI_SETTING_FILE_PROTECT,
			level: 2,
			route: true,
			routeId: 'filebackup',
			routeUrl: './content/fs/filebackup.php',
			children: []
		},
		{
			id: 'nas_protect',
			name: LANG.UI_SETTING_NAS_PROTECT,
			level: 2,
			route: true,
			routeId: 'nas_protect',
			routeUrl: './content/nas/nasbackup.php',
			children: []
		},
		{
			id: 'hadoop_protect',
			name: LANG.UI_SETTING_HADOOP_PROTECT,
			level: 2,
			route: true,
			routeId: 'hadoop_protect',
			routeUrl: './content/hadoop/hadoop_backup.php',
			children: []
		},
		{
			id: 'obs_protect',
			name: LANG.UI_SETTING_OBS_PROTECT,
			level: 2,
			route: true,
			routeId: 'obs_protect',
			routeUrl: './content/s3/obsbackup.php',
			children: []
		},
		{
			id: 'office365_protect',
			name: LANG.UI_SETTING_EXCHANGE_PROTECT,
			level: 2,
			route: true,
			routeId: 'office365_protect',
			routeUrl: './content/exchange/exchange_backup.php',
			children: []
		},
		{
			id: 'k8s_protect',
			name: LANG.UI_BACKUP_DATA_MODULE_K8S,
			level: 2,
			route: true,
			routeId: 'k8s_protect',
			routeUrl: './content/kubernetes/kubernetes_backup.php',
			children: []
		},
		{
			id: 'db_protect',
			name: LANG.UI_SETTING_DATABASE_PROTECT,
			level: 2,
			route: true,
			routeId: 'db_protect',
			routeUrl: './content/dbprotect/dbbackup.php',
			children: []
		},
		{
			id: 'cbrbackup',
			name: LANG.UI_ADD_TASK_TYPE_CLOUD_SYNC,
			level: 2,
			route: true,
			routeId: 'cbrbackup',
			routeUrl: './content/cbr/cbrbackup.php',
			children: []
		},
	],

	// GMP项目恢复任务路由数组
	GMP_RECOVER_ROUTE_TREE_DATA: [
		{
			id: 'vmprotect',
			name: LANG.UI_SETTING_VIRTUAL_MACHINE_PROTECTION,
			level: 2,
			route: false,
			routeId: '',
			routeUrl: '',
			children: [
				{
					id: 'vmrecover',
					name: LANG.UI_SEARCH_RECOVER_TASK,
					level: 3,
					route: true,
					routeId: 'recovery',
					routeUrl: './content/vm/vmrecover.php',
					children: []
				},
				{
					id: 'vm_instant_recovery',
					name: LANG.UI_VISUAL_INSTANT_TASK_NAME,
					level: 3,
					route: true,
					routeId: 'recovery',
					routeUrl: './content/platform/recovery/instantaneous.php?recovery_type=vm&subtype=1',
					children: []
				},
				{
					id: 'vm_grain_recovery',
					name: LANG.UI_VISUAL_GRAIN_TASK_NAME,
					level: 3,
					route: true,
					routeId: 'recovery',
					routeUrl: './content/platform/recovery/graininess.php?recovery_type=vm&subtype=1',
					children: []
				},
				{
					id: 'vm_platform_recovery',
					name: LANG.UI_PLATFORM_RECOVERY_NAME,
					level: 3,
					route: true,
					routeId: 'recovery',
					routeUrl: './content/platform/recovery/platform.php?recovery_type=vm&subtype=1',
					children: []
				}
			]
		},
		{
			id: 'prcloud_protect',
			name: LANG.UI_PUBLIC_PRIVATE_CLOUD_PROTECTED,
			level: 2,
			route: false,
			routeId: '',
			routeUrl: '',
			children: [
				{
					id: 'vm_prcloud_recovery',
					name: LANG.UI_SEARCH_RECOVER_TASK,
					level: 3,
					route: true,
					routeId: 'recovery',
					routeUrl: './content/vm/vmrecover.php?sub_module_type=2',
					children: []
				},
				{
					id: 'vm_prcloud_instant_recovery',
					name: LANG.UI_VISUAL_INSTANT_TASK_NAME,
					level: 3,
					route: true,
					routeId: 'recovery',
					routeUrl: './content/platform/recovery/instantaneous.php?recovery_type=vm&subtype=2',
					children: []
				},
				{
					id: 'vm_prcloud_graininess_recovery',
					name: LANG.UI_VISUAL_GRAIN_TASK_NAME,
					level: 3,
					route: true,
					routeId: 'recovery',
					routeUrl: './content/platform/recovery/graininess.php?recovery_type=vm&subtype=2',
					children: []
				},
				{
					id: 'vm_prcloud_platform_recovery',
					name: LANG.UI_PLATFORM_RECOVERY_NAME,
					level: 3,
					route: true,
					routeId: 'recovery',
					routeUrl: './content/platform/recovery/platform.php?recovery_type=vm&subtype=2',
					children: []
				}
			]
		},
		{
			id: 'awsprotect',
			name: LANG.UI_VISUAL_PUBLIC_CLOUD,
			level: 2,
			route: false,
			routeId: '',
			routeUrl: '',
			children: [
				{
					id: 'vm_awsprotect_recover',
					name: LANG.UI_SEARCH_RECOVER_TASK,
					level: 3,
					route: true,
					routeId: 'recovery',
					routeUrl: './content/aws/awsrecover.php',
					children: []
				},
				{
					id: 'vm_awsprotect_instant_recovery',
					name: LANG.UI_VISUAL_INSTANT_TASK_NAME,
					level: 3,
					route: true,
					routeId: 'recovery',
					routeUrl: './content/platform/recovery/instantaneous.php?recovery_type=vm&subtype=3',
					children: []
				},
				{
					id: 'vm_awsprotect_grain_recovery',
					name: LANG.UI_VISUAL_GRAIN_TASK_NAME,
					level: 3,
					route: true,
					routeId: 'recovery',
					routeUrl: './content/platform/recovery/graininess.php?recovery_type=vm&subtype=3',
					children: []
				},
				{
					id: 'vm_awsprotect_platform_recovery',
					name: LANG.UI_PLATFORM_RECOVERY_NAME,
					level: 3,
					route: true,
					routeId: 'recovery',
					routeUrl: './content/platform/recovery/platform.php?recovery_type=vm&subtype=3',
					children: []
				},
			]
		},
		{
			id: 'complete_machine',
			name: LANG.UI_BACKUP_DATA_MODULE_OS,
			level: 2,
			route: true,
			routeId: 'complete_machine',
			routeUrl: './content/complete_machine_os/machine_os_backup.php',
			children: [
				{
					id: 'machine_complete_recovery',
					name: LANG.UI_SEARCH_RECOVER_TASK,
					level: 3,
					route: true,
					routeId: 'recovery',
					routeUrl: './content/complete_machine_os/machine_os_recover.php',
					children: []
				},
				{
					id: 'machine_complete_instant_recovery',
					name: LANG.UI_VISUAL_INSTANT_TASK_NAME,
					level: 3,
					route: true,
					routeId: 'recovery',
					routeUrl: './content/platform/recovery/instantaneous.php?recovery_type=os',
					children: []
				},
				{
					id: 'machine_complete_grain_recovery',
					name: LANG.UI_VISUAL_GRAIN_TASK_NAME,
					level: 3,
					route: true,
					routeId: 'recovery',
					routeUrl: './content/platform/recovery/graininess.php?recovery_type=os',
					children: []
				},
				{
					id: 'machine_complete_platform_recovery',
					name: LANG.UI_PLATFORM_RECOVERY_NAME,
					level: 3,
					route: true,
					routeId: 'recovery',
					routeUrl: './content/platform/recovery/platform.php?recovery_type=os',
					children: []
				},
			]
		},
		{
			id: 'osbackup',
			name: LANG.UI_VOL_CDP_RECOVER_VOL,
			level: 2,
			route: true,
			routeId: 'osbackup',
			routeUrl: './content/os/osbackup.php',
			children: [
				{
					id: 'os_recovery',
					name: LANG.UI_SEARCH_RECOVER_TASK,
					level: 3,
					route: true,
					routeId: 'recovery',
					routeUrl: './content/os/osrecover.php',
					children: []
				},
			]
		},
		{
			id: 'filebackup',
			name: LANG.UI_SETTING_FILE_PROTECT,
			level: 2,
			route: true,
			routeId: 'filebackup',
			routeUrl: './content/fs/filebackup.php',
			children: [
				{
					id: 'file_recovery',
					name: LANG.UI_SEARCH_RECOVER_TASK,
					level: 3,
					route: true,
					routeId: 'recovery',
					routeUrl: './content/fs/filerecover.php',
					children: []
				},
			]
		},
		{
			id: 'nas_protect',
			name: LANG.UI_SETTING_NAS_PROTECT,
			level: 2,
			route: true,
			routeId: 'nas_protect',
			routeUrl: './content/nas/nasbackup.php',
			children: [
				{
					id: 'nas_recovery',
					name: LANG.UI_SEARCH_RECOVER_TASK,
					level: 3,
					route: true,
					routeId: 'recovery',
					routeUrl: './content/nas/nasrecover.php',
					children: []
				},
			]
		},
		{
			id: 'hadoop_protect',
			name: LANG.UI_SETTING_HADOOP_PROTECT,
			level: 2,
			route: true,
			routeId: 'hadoop_protect',
			routeUrl: './content/hadoop/hadoop_backup.php',
			children: [
				{
					id: 'hadoop_recovery',
					name: LANG.UI_SEARCH_RECOVER_TASK,
					level: 3,
					route: true,
					routeId: 'recovery',
					routeUrl: './content/hadoop/hadoop_recovery.php',
					children: []
				},
			]
		},
		{
			id: 'obs_protect',
			name: LANG.UI_SETTING_OBS_PROTECT,
			level: 2,
			route: true,
			routeId: 'obs_protect',
			routeUrl: './content/s3/obsbackup.php',
			children: [
				{
					id: 'obs_recovery',
					name: LANG.UI_SEARCH_RECOVER_TASK,
					level: 3,
					route: true,
					routeId: 'recovery',
					routeUrl: './content/s3/obsrecover.php',
					children: []
				},
			]
		},
		{
			id: 'office365_protect',
			name: LANG.UI_SETTING_EXCHANGE_PROTECT,
			level: 2,
			route: true,
			routeId: 'office365_protect',
			routeUrl: './content/exchange/exchange_backup.php',
			children: [
				{
					id: 'exchange_recovery',
					name: LANG.UI_SEARCH_RECOVER_TASK,
					level: 3,
					route: true,
					routeId: 'recovery',
					routeUrl: './content/exchange/exchange_recover.php',
					children: []
				},
			]
		},
		{
			id: 'k8s_protect',
			name: LANG.UI_BACKUP_DATA_MODULE_K8S,
			level: 2,
			route: true,
			routeId: 'k8s_protect',
			routeUrl: './content/kubernetes/kubernetes_backup.php',
			children: [
				{
					id: 'k8s_recovery',
					name: LANG.UI_SEARCH_RECOVER_TASK,
					level: 3,
					route: true,
					routeId: 'recovery',
					routeUrl: './content/kubernetes/kubernetes_recovery.php',
					children: []
				},
			]
		},
		{
			id: 'db_protect',
			name: LANG.UI_SETTING_DATABASE_PROTECT,
			level: 2,
			route: true,
			routeId: 'db_protect',
			routeUrl: './content/dbprotect/dbbackup.php',
			children: [
				{
					id: 'db_recovery',
					name: LANG.UI_SEARCH_RECOVER_TASK,
					level: 3,
					route: true,
					routeId: 'recovery',
					routeUrl: './content/dbprotect/dbrecover.php',
					children: []
				},
			]
		}
	]
}