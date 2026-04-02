var CONF = {
	/**模块定义**/
	M : {
		PLATFORM : 0,
		VM: 1,
		FILE: 2,
		DB: 3,
		USER: 4,
		JOB: 5,
		DATA: 6,
		LOG: 7,
		SYSTEM: 8,
		AGENT: 9,
		STORAGE: 10,
		ALARM: 11,
		ARCHIVE: 12,
		REPORT: 13,
		MANOEUVRE: 14,
		VCENTER: 15,
		NODE: 16
	},
	
	STRATEGY_TYPE : {
		DAY: 1,
		WEEK: 2,
		MONTH: 3,
		GLOBAL: 4
	},
	
	RESERVE_TYPE : {
		NUM: 1,
		DAY: 2
	},
	
	MODULE_TYPE : {
		VM: 2,
        FS: 3,
        DB: 4
	},
	
	VM_TYPE:{
		VMWARE: 1,
		HYPERV: 2,
		CITRIX: 3,
		KVM: 4,
		XEN: 5,
		ORACLEVM: 6,
		CLOUDVIEW: 7,
		INCLOUD: 8,
		VGATE: 9,
		NEOKYLIN: 10,
		H3C: 11,
		SANGFOR: 12,
		SDCOS: 13,
		FLEXCLOUD: 14,
		OPENSTACK: 15,
		FUSIONKVM: 16,
		FUSIONXEN: 17,
		WINSERVER: 18,
		RHV: 19,
		DSERVER: 20,
		CLOUDVIEWSVM: 21,
		FLEXHCS: 22
	},
	
	VM_DES:['UNKNOWN', 'VMware vSphere', 'Microsoft Hyper-V', 'Citrix XenServer', 'KVM', 'XEN', 
	        'Oracle VM', 'Cloudview SVM', 'InCloud Sphere', 'Halsign vGate', 'NeoKylin', 'H3C CAS', 
	        'SANGFOR HCI', 'SDC OS', 'FlexCloud', 'OpenStack', 'FusionSphere(kvm)', 'FusionSphere(xen)', 
	        'Winhong CNware', 'Redhat RHV/Ovirt', 'D-Server', 'Cloudview SVM', 'Flex HCS'],
	
	AJAXPATH : "./api/",
	AJAXMETHOD: 'POST',
	IDLETIMEOUT: 900,
	SYSTEMNAME:"易通云服务管理平台",
	
	LANG_CONF : {
		"zh-cn" : "zh_CN",
		"zh-tw" : "zh_TW",
		"en-us" : "en",
	},
	VMTYPE_GROUP : {
		VMWARE : [1, 7, 21],
		XENSERVER : [3, 8, 9, 18, 20],
		KVM : [4, 10, 11, 12, 13, 14, 15, 19, 22],
	},
};