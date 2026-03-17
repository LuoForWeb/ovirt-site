<?php

/**
 * app的一些公共必须加载的配置信息
 */

return [

    'lang' => getEnvs() ? 'zh-cn' : '@language@',       //语言文件
    'ext' => '.php',        //文件后缀

    /*系统信息*/
    'SYSTEM_INFO' => [
        'system_name' => '云祺容灾备份系统',     //备份系统名称
        'company' => 'Vinchin ',                          //厂商名
        'copyright' => 'Copyright',                       //版权
        'years' => date('Y'),                                //年份
        'vendor' => getEnvs() ? 'vinchin' : '@VENDOR@',                            //厂商
        'arch_type' => getEnvs() ? 'x86' : '@ARCHTYPE@',                      //底层架构 x86/arm
        'os_type' => getEnvs() ? 'rocky9' : '@OSTYPE@',                          //操作系统 centos7/kylin10
        'version' => '@version@',                         //软件版本号
        'enterprise' => getEnvs() ? 'standard' : '@enterprise@',                   //软件版本
        'recommend' => '', //推荐浏览器和分辨率提示
        //授权处使用
        'company_name' => '成都云祺科技有限公司',              //系统所属公司
        'company_tel' => '+86 400-9955-698',              //联系电话
        'company_email' => 'support@vinchin.com',         //支持邮箱
        'copyright_name' => '成都云祺科技有限公司'
    ],

    // oem 对应的vendor映射
    'VENDOR_LIST' => [
        // 标识 => '打包系统对应的vendor名称'
        'gmp' => 'vdms', // gmp
        'sangfor' => 'sangfor_enterprise', // 深信服
        'inspur' => 'inspur_enterprise', // 浪潮
    ],

    //远程地址
    'REMOTE' => array(
        'website' => 'https://www.vinchin.cn',              //公司官网
        'api_url' => 'https://www.vinchin.com:5786',         //api地址
        'api_user' => 'product',                   //api用户
        'api_pass' => 'yunqi123456789',            //api密码
        'ueiplan' => 'http://www.vinchin.cn/ueiplan.php',  //用户体验改善计划
        'privacy' => 'http://www.vinchin.cn/privacy.php',  //隐私声明
        'release' => 'http://www.vinchin.cn/release.php',  //版本发布记录
    ),

    //软件版本
    'ENTERPRISE' => array(
        /**
         * 这里是打包的版本
         * 英文版本会有1个包,通过授权文件来区分不同版本
         * 中文版会有2个包,一个标准版,一个企业版.
         */
        'standard' => 'vinchin_standard',           //中文标准版
        'enterprise' => 'vinchin_enterprise',       //中文企业版
        'enterprise_en' => 'vinchin_enterprise_en', //英文企业版
    ),

    //节点软件包
    'NODE_SOFT_INFO' => array(
        'uploadfile' => array(
            'name' => 'files',
            'suffixes' => 'tar|gz',
            'size' => 104857600,
        ),
    ),

    //节点类型定义
    'NODETYPE' => array(
        'UNKNOWN' => 0,
        'MASTER' => 1,      //主节点
        'BACKUP' => 2       //备份节点
    ),

    //网卡信息
    'NETWORKCARD' => array(
        'path' => '/etc/sysconfig/network-scripts/',
        'path2' => '/etc/NetworkManager/system-connections/',
        'prefix' => 'ifcfg-',
        'prefix2' => '.nmconnection',
    ),

    //接口魔术
    'API_MAGIC' => '6e24cc40bfdb6963c04a4f1983c8af71',

    //加密密钥
    'SECRET_KEY' => '0e55cf8bfba0fdf4a53353cd0d7e282e727a3c1e504145164ae97aa95542ca9a',

    //平台加密密钥
    'SECRET_KEY_PT' => 'dmluY2hpbjEyMzQ1Njc4c2t5',

    //时间占位符
    'TIMESPACE' => '----',
    //空占位符
    'NULLSPACE' => '--',

    //公共标记 1true 2false
    'FLAG' => array(
        'UNKNOWN' => 0,
        'SET' => 1,
        'UNSET' => 2
    ),

    //操作类型
    'OPCODE_LEVEL' => array(
        'UNKNOWN' => 0,
        'PUBLIC' => 1,
        'PRIVATE' => 2,
    ),

    //访问接口主机名，默认访问本地
    'HOSTNAME' => '',

    //操作状态
    'OP_STATUS' => array(
        'UNKNOWN' => 0,
        'RUNNING' => 1,
        'SUCCEED' => 2,
        'FAILURE' => 3,
    ),

    //软件版本定义,bd_license, software_type字段
    'SOFTWARE_VERSION' => array(
        'UNKNOWN' => 0,
        'STANDARD' => 1,                //标准版*************
        'ENTERPRISE' => 2,              //企业版*************
        'ADVANCE_ENTERPRISE' => 3,      //企业增强版-
        'EN_FREE_EDITION' => 4,         //免费版(英文)*********
        'ESSENTIAL' => 5,               //基础版-
        'STANDARD_EN' => 6,             //标准版(英文)*********
        'ENTERPRISE_EN' => 7,           //企业版(英文)*********
        'ADVANCE_ENTERPRISE_EN' => 8,   //企业增强版(英文)-
        'ESSENTIAL_EN' => 9,            //基础版(英文)*********
        'FREE_EDITION' => 10,            //免费版-
        'ENTERPRISE_EN_LR' => 11,           //企业版(英文)促销*********
        'DIY_VERSION' => 100            //自定义版本
    ),

    //系统配置数据存储定义类型
    'SETTINGS_CONF' => array(
        'UNKNOWN' => 0, //未知
        'NTP' => 1,     //NTP配置
        'VISUAL' => 2,  //大屏配置
        'NIC' => 3,     //网卡聚合配置
        'SERVICE' => 4,  //服务授权
        'UPDATE' => 5,  //系统升级
        'SYSTEM_MONITOR_PROGRESS' => 6, //系统监控报警规则配置
        'SYSTEM_MONITOR_PROGRESS_TIME' => 7,//系统监控报警规则配置检测时间和通道沉默周期
        'SYSTEM_UPDATE_FLAG' => 8,//系统升级更新sql的flag版本信息
        'PLATFORM_SYSTEM_RECOVERY' => 9,//容灾演练平台配置信息
        'SYSTEM_WECHAT_OPENID' => 10,// 微信公众号的配置
        'SYSTEM_ENTERPRISE_WECHAT' => 11,// 企业微信的配置
        'SYSTEM_CONFIG_RECORDS' => 12,// 日志、任务和告警等的记录配置
        //20-29先暂时为页面设计相关占用
        'HOMEPAGE' => 20, //页面首页定制
        'THEME' => 21, //页面主题配色
        'LAYOUT' => 22, //页面布局
        'LOGINPAGE' => 23, //页面登录布局
        'CUSTOM_VERSION' => 24,     //定制版本
        'API_SAFE_CONFIG' => 31,    // api安全开关，是否校验sign，token等安全接口配置
    ),

    'XENSERVER_BACKUP_LEVEL' => array(
        'UNKNOWN' => 0,         // XenServer backup level unknown
        'NORMAL' => 1,          // XenServer backup level normal
        'KEEP_SNAPSHOT' => 2,   // XenServer backup level KEEP_SNAPSHOT
    ),

    //tmp path
    'TMP_PATH' => '/usr/share/nginx/' . (getEnvs() ? 'vinchin' : '@VENDOR@') . '/tmp/',

    'TMP_PATH_LOGO' => '/usr/share/nginx/' . (getEnvs() ? 'vinchin' : '@VENDOR@') . '/tmp/logo.png',

    //tmp path 相对
    'TMP_PATH_RE' => '/tmp/',
    'TMP_PATH_RE_LOG' => '/tmp/logo.png',

    //logo path
    'LOGO_PATH' => '/usr/share/nginx/' . (getEnvs() ? 'vinchin' : '@VENDOR@') . '/img/platform/logo.png',

    //node soft
//         'NODE_SOFT' => '/usr/share/nginx/' . (getEnvs() ? 'vinchin' : '@VENDOR@') . '/tmp/node_soft/',
    'NODE_SOFT' => '/etc/' . (getEnvs() ? 'vinchin' : '@VENDOR@') . '/web/node_soft/',
    'NODE_SOFT_RE' => '/tmp/node_soft/',

    'NTP_SERVERS_FILE' => '/etc/ntp.conf',


    //特殊配置文件地址，主要用作第三方产品配置信息
    'SPECIAL_DIR' => '/usr/share/nginx/' . (getEnvs() ? 'vinchin' : '@VENDOR@') . '/special/',
    'SPECIAL_CONFIG' => 'config.txt',

    'AGENT_PATH' => '/usr/share/nginx/' . (getEnvs() ? 'vinchin' : '@VENDOR@') . '/agent',
    //存放删除租户日志信息
    "DELETE_TENANT_PATH" => '/var/log/' . (getEnvs() ? 'vinchin' : '@VENDOR@') . '/web/delete_tenant.log',
    // 时间策略备份方式
    'TIME_STRATEGY_BACKUP_TYPE' => [
        'unknown' => 0,
        'strategy' => 1,  // 按策略备份
        'oncetime' => 2,  // 一次性备份
        'manual' => 3,    // 无策略备份
    ],
    'TIME_STRATEGY_BACKUP_TYPE_MAP' => [
        1 => 'strategy',  // 按策略备份
        2 => 'oncetime',  // 一次性备份
        3 => 'manual',    // 无策略备份
    ],
    'TRANSPORT_ENCRYPT_METHOD' => array(
        1 => 'RSA',
        2 => 'SM2',
    ),
    //远程控制路径配置
    'UPLOAD_DIR' => '/usr/share/nginx/' . (getEnvs() ? 'vinchin' : '@VENDOR@') . '/tmp/file',
    'TARGET_DIR' => '/usr/share/nginx/' . (getEnvs() ? 'vinchin' : '@VENDOR@') . '/tmp/filetmp',
    //脚本类型
    'SCRIPT_TYPE' => [
        'UNKNOWN' => 0,
        'SHELL_TYPE' => 1,
        'BAT_TYPE' => 2,
        'YAML_TYPE' => 3,
        'PYTHON2_TYPE' => 4,
        'PYTHON3_TYPE' => 5,
    ],
    //登录页路径
    'LOGIN_INFO' => array(
        'login_url' => getEnvs() ? '/login_version/login_professional/' : '@loginpage@',
        'login_layout' => getEnvs() ? 'leftLayout' : '@loginlayout@',
    ),
    //系统名称
    'SYSTEM_NAME_FILE' => '/etc/@VENDOR@/web/systemName.ini',
    'CUSTOM_VERSION' => array(
        'unknown' => '',
        'professional' => '', //专业版
        'basic' => 'vinchin_enterprise_sr',    //基础版
        'special' => '',   //白牌版
        'project' => 'vinchin_enterprise_pr'   //项目版
    ),
    // 驱动库上传目录（临时目录）
    'DRIVER_UPLOAD_DIR' => '/usr/share/nginx/' . (getEnvs() ? 'vinchin' : '@VENDOR@') . '/tmp/driver',
    'DB_VALIDATE_REPORT_DIR' => '/usr/share/nginx/' . (getEnvs() ? 'vinchin' : '@VENDOR@') . '/tmp/db_report',
    'VIRUS_UPLOAD_DIR' => '/usr/share/nginx/' . (getEnvs() ? 'vinchin' : '@VENDOR@') . '/tmp/virus',
    'MIRROR_DOWNLOAD_DIR' => '/usr/share/nginx/' . (getEnvs() ? 'vinchin' : '@VENDOR@') . '/tmp/image',

    //升级包上传路径
    'UPLOAD_PATH' => '/usr/share/nginx/' . (getEnvs() ? 'vinchin' : '@VENDOR@') . '/tmp/upgrade/',
    'UPLOADTMP_PATH' => '/usr/share/nginx/' . (getEnvs() ? 'vinchin' : '@VENDOR@') . '/tmp/upgradetmp/',
    "UPDATE_PATCH_STATUS" => array(
        'UNKNOWN' => 0,
        "UPDATE_ALREADY_UPLOAD" => 1,   //已上传, 等待升级中
        "UPDATE_FAILED" => 2,           //升级失败
        "UPDATE_UPLOAD_FAILED" => 3,    //上传失败
        "UPDATE_SUCCESS" => 4,          //升级成功
        "UPDATE_UPLOAD_TO_NODE" => 5,   //升级包正在上传到节点
        "UPDATE_WAITING" => 6,          //等待升级中
        'UPDATING' => 7,                //升级中
        'PATCH_INVALID' => 8,           //升级包无效
        'PATCH_INVALID_ING' => 9,       //升级包检查中
        'PATCH_INVALID_COMPLETE' => 10, //升级包检查完成
    ),
    // 上传升级包状态描述
    'UPDATE_PATCH_STATUS_DES' => array(
        0 => '未知错误',
        1 => '已上传, 等待升级中',
        2 => '补丁包已同步到子节点，但安装失败',
        3 => '补丁包上传失败',
        4 => '补丁包已安装',
        5 => '正在上传补丁包到节点',
        6 => '补丁包等待升级中',
        7 => '补丁包升级中',
        8 => '补丁包无效',
        9 => '正在检测补丁包',
        10 => '补丁包已验证通过',
        11 => '系统空间不足以完成补丁包解压',
        12 => '系统空间不足以下载补丁包',
        13 => '下载补丁包失败'
    ),
    //lisence info
    'LISENCE_INFO' => array(
        'thumbprintFileName' => 'thumbprint.txt',  //指纹文件名
        //下载文件方式
        'uploadfile' => array(
            'name' => 'files',
            'suffixes' => 'key',
            'size' => 204800,
            'type' => 'application/octet-stream'
        ),
        'authflag' => array(
            'authorized' => 1,      //已授权
            'unauthorized' => 2,    //未授权
            'expire' => 3,          //授权过期
            'invalid' => 4,         //授权异常
        ),
        //授权方式
        'type' => array(
            'host' => 1,   //按主机授权
            'cpu' => 2,    //按CPU
            'storage' => 3,//按容量
            'vm' => 4,     //按虚拟机个数
        ),
        //license文件类型
        'filetype' => array(
            'license' => 1, //系统授权文件后缀
            'service' => 2, //服务授权文件后缀
        ),
        //服务授权文件路径
        'serviceFilePath' => '/opt/' . (getEnvs() ? 'vinchin' : '@VENDOR@') . '/vinsc.service',  //这里取名service,有混淆的意思.
        //服务类型
        'servertype' => array(
            '---',
            '软件标准服务',
            '软件白金服务',
        )
    ),
    //AD域验证CA证书存放目录
    'CERT_PATH' => '/etc/' . (getEnvs() ? 'vinchin' : '@VENDOR@') . '/web/cert/',
	"STORE_ENCRYPT_METHOD" => array(
		1 => "AES-256",
		2 => "SM4",
	),
    
	//通知类型
	'NOTICE_TYPE' => [
		'UNKNOWN' => 0,
		'TASK' => 1,        //任务通知
		'SYSTEM' => 2,      //系统通知
		'VERIFY' =>3 ,
    ],

	//通知方式
	'NOTICE_MODE' => array(
		'UNKNOWN' => 0,
		'EMAIL' => 1,       //邮件通知
		'SMS' => 2,         //短信通知
		'WE_CHAT' => 3,         //微信公众号通知
		'WE_CHAT2' => 4,         //企业微信通知
	),
];
