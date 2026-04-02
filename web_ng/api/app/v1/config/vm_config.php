<?php
/**
 * 虚拟机高级恢复配置
 */
return  array
(
    /* middle cpu type */
    'BdMiddleCpuArch' => [
        'BD_MIDDLE_CPU_ARCH_UNKNOWN' => 0,
        'BD_MIDDLE_CPU_ARCH_X86' => 1,
        'BD_MIDDLE_CPU_ARCH_X64' => 2,
        'BD_MIDDLE_CPU_ARCH_ARM' => 3,
        'BD_MIDDLE_CPU_ARCH_AARCH64' => 4,
        'BD_MIDDLE_CPU_ARCH_MIPS64EL' => 5,
        'BD_MIDDLE_CPU_ARCH_LOONGARCH_64' => 6,
        'BD_MIDDLE_CPU_ARCH_SW_64' => 7,
        'BD_MIDDLE_CPU_ARCH_S390X' => 8,
        'BD_MIDDLE_CPU_ARCH_PPC64' => 9,
    ],
    'BdMiddleCpuArchDes' => [
        'Unknow',
        'x86',
        'x86_64',
        'ARM',
        'ARM64',
        'MIPS64EL',
        'LOONGARCH_64',
        'SW_64',
        'S390X',
        'PPC64',
    ],
    /* middle operating system type */
    'BdMiddleOsTypeV2' => [
        'BD_MIDDLE_OS_TYPE_V2_UNKNOWN',
        'BD_MIDDLE_OS_TYPE_V2_MACOS',
        'BD_MIDDLE_OS_TYPE_V2_WINDOWS',
        'BD_MIDDLE_OS_TYPE_V2_LINUX',
        'BD_MIDDLE_OS_TYPE_V2_OTHER',
        'BD_MIDDLE_OS_TYPE_V2_VXWORKS',
        'BD_MIDDLE_OS_TYPE_V2_ANDROID',
    ],
    'BdMiddleOsTypeV2Des' => [
        'Unknow',
        'Mac OS',
        'Windows',
        'Linux',
        'Other',
        'Vxworks',
        'Android'
    ],
    /* middle operating system version */
    'BdMiddleOsVersion' => [
        'BD_MIDDLE_OS_VERSION_UNKNOWN',

        /* mac os serials */
        1 => 'BD_MIDDLE_OS_VERSION_MACOS',
        'BD_MIDDLE_OS_VERSION_DARWIN',					// Mac OS 10.5
        'BD_MIDDLE_OS_VERSION_DARWIN_64',					// Mac OS 10.5 64 bit
        'BD_MIDDLE_OS_VERSION_DARWIN10',					// Mac OS 10.6
        'BD_MIDDLE_OS_VERSION_DARWIN10_64',				// Mac OS 10.6 64 bit
        'BD_MIDDLE_OS_VERSION_DARWIN11',					// Mac OS 10.7 
        'BD_MIDDLE_OS_VERSION_DARWIN11_64',				// Mac OS 10.7 64 bit
        'BD_MIDDLE_OS_VERSION_DARWIN12_64',				// Mac OS 10.8 64 bit
        'BD_MIDDLE_OS_VERSION_DARWIN13_64',				// Mac OS 10.9 64 bit
        'BD_MIDDLE_OS_VERSION_DARWIN14_64',				// Mac OS 10.10 64 bit
        'BD_MIDDLE_OS_VERSION_DARWIN15_64',				// Mac OS 10.11 64 bit
        'BD_MIDDLE_OS_VERSION_DARWIN16_64',				// Mac OS 10.12 64 bit
        'BD_MIDDLE_OS_VERSION_DARWIN17_64',				// Mac OS 10.12 64 bit
        'BD_MIDDLE_OS_VERSION_DARWIN18_64',				// Mac OS 10.12 64 bit
        'BD_MIDDLE_OS_VERSION_DARWIN19_64',				// Mac OS 10.12 64 bit
        'BD_MIDDLE_OS_VERSION_DARWIN20_64',				// Mac OS 10.12 64 bit
        'BD_MIDDLE_OS_VERSION_DARWIN21_64',				// Mac OS 10.12 64 bit
        'BD_MIDDLE_OS_VERSION_DARWIN22_64',				// Mac OS 10.12 64 bit
        'BD_MIDDLE_OS_VERSION_DARWIN23_64',				// Mac OS 10.12 64 bit

        2000 => 'BD_MIDDLE_OS_VERSION_WINDOWS',
        'BD_MIDDLE_OS_VERSION_WIN',                   // windows
        'BD_MIDDLE_OS_VERSION_DOS',                   // MS-DOS
        'BD_MIDDLE_OS_VERSION_WIN31',                 // Windows 3.1
        'BD_MIDDLE_OS_VERSION_WIN95',                 // Windows 95
        'BD_MIDDLE_OS_VERSION_WIN98',                 // Windows 98
        'BD_MIDDLE_OS_VERSION_WIN_ME',                // Windows ME
        'BD_MIDDLE_OS_VERSION_WIN_NT',                // Windows NT 4.0
        'BD_MIDDLE_OS_VERSION_WIN2000_SERV',          // windows server 2000
        'BD_MIDDLE_OS_VERSION_WIN2000_PRO',           // windows 2000 pro
        'BD_MIDDLE_OS_VERSION_WIN2000_ADVANCED_SERV', // windows 2000 advanced server
        'BD_MIDDLE_OS_VERSION_WIN2003_BUSINESS',      // Windows Business Server 2003
        'BD_MIDDLE_OS_VERSION_WIN2003_DATACENTER',    // Windows DataCenter Server 2003
        'BD_MIDDLE_OS_VERSION_WIN2003_DATACENTER_64', // Windows DataCenter Server 2003 64 bit
        'BD_MIDDLE_OS_VERSION_WIN2003_DATACENTER_SP1_32',	//Windows Server 2003 DataCenter SP1 32bit
        'BD_MIDDLE_OS_VERSION_WIN2003_DATACENTER_SP2_32', //Windows Server 2003 DataCenter SP2 32bit
        'BD_MIDDLE_OS_VERSION_WIN2003_STANDAR',       // Windows Stander Server 2003
        'BD_MIDDLE_OS_VERSION_WIN2003_STANDAR_64',    // Windows Stander Server 2003 64 bit
        'BD_MIDDLE_OS_VERSION_WIN2003_STANDARD_SP1_32',	//Windows Server 2003 Standard SP1 32bit
        'BD_MIDDLE_OS_VERSION_WIN2003_STANDARD_SP2_32',	//Windows Server 2003 Standard SP2 32bit
        'BD_MIDDLE_OS_VERSION_WIN2003_STANDARD_SP2_64',	//Windows Server 2003 Standard SP1 64bit
        'BD_MIDDLE_OS_VERSION_WIN2003_ENTERPRISE',    // Windows Enterprise Server 2003
        'BD_MIDDLE_OS_VERSION_WIN2003_ENTERPRISE_64', // Windows Enterprise Server 2003 64 bit
        'BD_MIDDLE_OS_VERSION_WIN2003_ENTERPRISE_SP1_32',	//Windows Server 2003 Enterprise SP1 32bit
        'BD_MIDDLE_OS_VERSION_WIN2003_ENTERPRISE_SP2_32',	//Windows Server 2003 Enterprise SP2 32bit
        'BD_MIDDLE_OS_VERSION_WIN2003_WEB',           // Windows WEB Server 2003
        'BD_MIDDLE_OS_VERSION_WIN2008_SERV_32',       // windows server 32bit
        'BD_MIDDLE_OS_VERSION_WIN2008_SERV',          // windows 2008 server (Win8Serv in vmware)
        'BD_MIDDLE_OS_VERSION_WIN2008_SERV_R2',       // windows 2008 server R2
        'BD_MIDDLE_OS_VERSION_WIN2008_DATACENTER_R2_SP1_64',  // Windows Server 2008 Datacenter R2 SP1 64bit
        'BD_MIDDLE_OS_VERSION_WIN2008_DATACENTER_SP1_32',  // Windows Server 2008 Datacenter SP1 32bit
        'BD_MIDDLE_OS_VERSION_WIN2008_DATACENTER_SP1_64',  // Windows Server 2008 Datacenter SP1 64bit
        'BD_MIDDLE_OS_VERSION_WIN2008_DATACENTER_SP2_32',  // Windows Server 2008 Datacenter SP2 32bit
        'BD_MIDDLE_OS_VERSION_WIN2008_DATACENTER_SP2_64',  // Windows Server 2008 Datacenter SP2 64bit
        'BD_MIDDLE_OS_VERSION_WIN2008_ENTERPRISE_R2_SP1_64',  // Windows Server 2008 Enterprise R2 SP1 64bit
        'BD_MIDDLE_OS_VERSION_WIN2008_ENTERPRISE_SP1_32',  // Windows Server 2008 Enterprise SP1 32bit
        'BD_MIDDLE_OS_VERSION_WIN2008_ENTERPRISE_SP1_64',  // Windows Server 2008 Enterprise SP1 64bit
        'BD_MIDDLE_OS_VERSION_WIN2008_R2_DATACENTER_64',  // Windows Server 2008 R2 Datacenter 64bit
        'BD_MIDDLE_OS_VERSION_WIN2008_R2_DATACENTER_SP1_64',  // Windows Server 2008 R2 Datacenter SP1 64bit
        'BD_MIDDLE_OS_VERSION_WIN2008_R2_ENTERPRISE_64',  // Windows Server 2008 R2 Enterprise 64bit
        'BD_MIDDLE_OS_VERSION_WIN2008_R2_ENTERPRISE_SP1_64',  // Windows Server 2008 R2 Enterprise SP1 64bit
        'BD_MIDDLE_OS_VERSION_WIN2008_R2_STANDARD_64',  // Windows Server 2008 R2 Standard 64bit
        'BD_MIDDLE_OS_VERSION_WIN2008_R2_STANDARD_SP1_64',  // Windows Server 2008 R2 Standard SP1 64bit
        'BD_MIDDLE_OS_VERSION_WIN2008_STANDARD_R2_SP1_64',  // Windows Server 2008 Standard R2 SP1 64bit
        'BD_MIDDLE_OS_VERSION_WIN2008_STANDARD_SP1_32',  // Windows Server 2008 Standard SP1 32bit
        'BD_MIDDLE_OS_VERSION_WIN2008_STANDARD_SP1_64',  // Windows Server 2008 Standard SP1 64bit
        'BD_MIDDLE_OS_VERSION_WIN2008_STANDARD_SP2_32',  // Windows Server 2008 Standard SP2 32bit
        'BD_MIDDLE_OS_VERSION_WIN2008_STANDARD_SP2_64',  // Windows Server 2008 Standard SP2 64bit
        'BD_MIDDLE_OS_VERSION_WIN2008_WEB_R2_64',  // Windows Server 2008 Web R2 64bit
        'BD_MIDDLE_OS_VERSION_WIN2012_SERV',          // windows 2012 server (Win9Serv)
        'BD_MIDDLE_OS_VERSION_WIN2012_SERV_R2',       // windows 2012 server R2
        'BD_MIDDLE_OS_VERSION_WIN2012_DATACENTER_64',   // Windows Server 2012 Datacenter 64bit
        'BD_MIDDLE_OS_VERSION_WIN2012_ESSENTIALS_R2_64',  // Windows Server 2012 Essentials R2 64bit
        'BD_MIDDLE_OS_VERSION_WIN2012_R2_DATACENTER_64',  // Windows Server 2012 R2 Datacenter 64bit
        'BD_MIDDLE_OS_VERSION_WIN2012_R2_ESSENTIALS_64',  // Windows Server 2012 R2 Essentials 64bit
        'BD_MIDDLE_OS_VERSION_WIN2012_R2_STANDARD_64',  // Windows Server 2012 R2 Standard 64bit
        'BD_MIDDLE_OS_VERSION_WIN2012_STANDARD_64',  // Windows Server 2012 Standard 64bit
        'BD_MIDDLE_OS_VERSION_WIN2016_SERV',          // windows 2016 server
        'BD_MIDDLE_OS_VERSION_WIN2016_DATACENTER',    // windows 2016 datacenter version 
        'BD_MIDDLE_OS_VERSION_WIN2016_ESSENTIAL',     // windows 2016 essential version 
        'BD_MIDDLE_OS_VERSION_WIN2016_STANDARD_64',	// Windows Server 2016 Standard 64bit
        'BD_MIDDLE_OS_VERSION_WIN_HYPERV',            // HyperV
        'BD_MIDDLE_OS_VERSION_WIN2019',               // windows server 2019
        'BD_MIDDLE_OS_VERSION_WIN2019_STANDARD',      // windows server 2019 standar
        'BD_MIDDLE_OS_VERSION_WIN2019_ESSENTIALS',    // windows server 2019 essentials
        'BD_MIDDLE_OS_VERSION_WIN2019_DATACENTER_64',   // Windows Server 2019 Datacenter 64bit
        'BD_MIDDLE_OS_VERSION_WIN2019_NEXT_64',       // windows server 2022
        'BD_MIDDLE_OS_VERSION_WIN2022_64',              // Windows Server 2022 64bit
        'BD_MIDDLE_OS_VERSION_WIN2022_NEXT_64',       // windows server 2025
        'BD_MIDDLE_OS_VERSION_WIN_XP_HOME',           // windows xp home edition
        'BD_MIDDLE_OS_VERSION_WIN_XP_PRO',            // windows xp pro
        'BD_MIDDLE_OS_VERSION_WIN_XP_PRO_64',         // windows xp pro x64
        'BD_MIDDLE_OS_VERSION_WIN_VISTA',             // windows vista
        'BD_MIDDLE_OS_VERSION_WIN_VISTA_64',          // windows vista 64 bit
        'BD_MIDDLE_OS_VERSION_WIN7',                  // windows 7
        'BD_MIDDLE_OS_VERSION_WIN7_64',               // windows 7 64 bit
        'BD_MIDDLE_OS_VERSION_WIN7_PROFESSIONAL_32',  // Windows 7 Professional 32bit
        'BD_MIDDLE_OS_VERSION_WIN7_PROFESSIONAL_64',  // Windows 7 Professional 64bit
        'BD_MIDDLE_OS_VERSION_WIN7_SP1_64',  // Windows 7 SP1 64bit
        'BD_MIDDLE_OS_VERSION_WIN7_SP1_32',  // Windows 7 SP1 32bit
        'BD_MIDDLE_OS_VERSION_WIN7_SP1_PROFESSIONAL_32',  // Windows 7 SP1 Professional 32bit
        'BD_MIDDLE_OS_VERSION_WIN7_SP1_PROFESSIONAL_64',  // Windows 7 SP1 Professional 64bit
        'BD_MIDDLE_OS_VERSION_WIN7_SP1_ULTIMATE_64',  // Windows 7 SP1 Ultimate 64bit
        'BD_MIDDLE_OS_VERSION_WIN7_SP1_ULTIMATE_32',  // Windows 7 SP1 Ultimate 32bit
        'BD_MIDDLE_OS_VERSION_WIN7_ULTIMATE_64',  // Windows 7 Ultimate 64bit
        'BD_MIDDLE_OS_VERSION_WIN7_ULTIMATE_32',  // Windows 7 Ultimate 32bit
        'BD_MIDDLE_OS_VERSION_WIN8',                  // windows 8
        'BD_MIDDLE_OS_VERSION_WIN8_64',               // windows 8 64 bit
        'BD_MIDDLE_OS_VERSION_WIN10',                 // windows 10
        'BD_MIDDLE_OS_VERSION_WIN10_64',              // windows 10 64 bit
        'BD_MIDDLE_OS_VERSION_WIN10_CMGE_V2020_L_64',  // Windows 10 CMGE V2020 L 64bit
        'BD_MIDDLE_OS_VERSION_WIN10_ENTERPRISE_32',  // Windows 10 Enterprise 32bit
        'BD_MIDDLE_OS_VERSION_WIN10_ENTERPRISE_64',  // Windows 10 Enterprise 64bit
        'BD_MIDDLE_OS_VERSION_WIN10_ENTERPRISE_2016_LTSB_32',  // Windows 10 Enterprise 2016 LTSB 32bit
        'BD_MIDDLE_OS_VERSION_WIN10_ENTERPRISE_2016_LTSB_64',  // Windows 10 Enterprise 2016 LTSB 64bit
        'BD_MIDDLE_OS_VERSION_WIN10_ENTERPRISE_2019_LTSC_64',  // Windows 10 Enterprise 2019 LTSC 64bit
        'BD_MIDDLE_OS_VERSION_WIN10_PROFESSIONAL_32',  // Windows 10 Professional 32bit
        'BD_MIDDLE_OS_VERSION_WIN10_PROFESSIONAL_64',  // Windows 10 Professional 64bit
        'BD_MIDDLE_OS_VERSION_WIN10_PROFESSIONAL_FOR_EDUCATION_32',  // Windows 10 Professional for Education 32bit
        'BD_MIDDLE_OS_VERSION_WIN10_PROFESSIONAL_FOR_EDUCATION_64',  // Windows 10 Professional for Education 64bit
        'BD_MIDDLE_OS_VERSION_WIN11_64',              // windows 11 64 bit
        'BD_MIDDLE_OS_VERSION_WIN12_64',              // windows 12 64 bit
        'BD_MIDDLE_OS_VERSION_WIN_XP',                // windows xp
        'BD_MIDDLE_OS_VERSION_WIN_NT_4',              // windows NT 4.0

        /* linux serials */
        30000 => 'BD_MIDDLE_OS_VERSION_LINUX',
        // asianux (red flag linux)
        'BD_MIDDLE_OS_VERSION_ASIANUX2',    // asianux 2 32 bit
        'BD_MIDDLE_OS_VERSION_ASIANUX2_64', // asianux 2 64 bit
        'BD_MIDDLE_OS_VERSION_ASIANUX3',    // asianux 3 32 bit(red flag linux)
        'BD_MIDDLE_OS_VERSION_ASIANUX3_64', // asianux 3 64 bit
        'BD_MIDDLE_OS_VERSION_ASIANUX4',    // asianux 4 32 bit(red flag linux)
        'BD_MIDDLE_OS_VERSION_ASIANUX4_64', // asianux 4 64 bit
        'BD_MIDDLE_OS_VERSION_ASIANUX5',    // asianux 5 32 bit(red flag linux)
        'BD_MIDDLE_OS_VERSION_ASIANUX5_64', // asianux 5 64 bit
        'BD_MIDDLE_OS_VERSION_ASIANUX7_64', // asianux 7 64 bit
        'BD_MIDDLE_OS_VERSION_ASIANUX7_6_64', // asianux 7.6 64 bit
        'BD_MIDDLE_OS_VERSION_ASIANUX8_64', // asianux 8 64 bit
        'BD_MIDDLE_OS_VERSION_ASIANUX9_64', // asianux 9 64 bit
        'BD_MIDDLE_OS_VERSION_ASIANUX_SERVER_4_SP2_64',  // Asianux Server 4 SP2 64bit
        'BD_MIDDLE_OS_VERSION_ASIANUX_SERVER_4_SP4_64',  // Asianux Server 4 SP4 64bit
        'BD_MIDDLE_OS_VERSION_ASIANUX_SERVER_4_5_64',  // Asianux Server 4.5 64bit
        'BD_MIDDLE_OS_VERSION_ASIANUX_SERVER_7_3_64',  // Asianux Server 7.3 64bit

        // centos serials
        'BD_MIDDLE_OS_VERSION_CENTOS',     // centos 4/5 32 bit
        'BD_MIDDLE_OS_VERSION_CENTOS_64',  // centos 4/5 64 bit

        // centos sub version
        'BD_MIDDLE_OS_VERSION_CENTOS4',       // CentOS 4
        'BD_MIDDLE_OS_VERSION_CENTOS4_64',    // CentOS 4 64 bit
        'BD_MIDDLE_OS_VERSION_CENTOS5',       // CentOS 5
        'BD_MIDDLE_OS_VERSION_CENTOS5_64',    // CentOS 5 64 bit
        'BD_MIDDLE_OS_VERSION_CENTOS5_1',     // CentOS 5.1
        'BD_MIDDLE_OS_VERSION_CENTOS5_1_64',  // CentOS 5.1 64 bit
        'BD_MIDDLE_OS_VERSION_CENTOS5_2',     // CentOS 5.2
        'BD_MIDDLE_OS_VERSION_CENTOS5_2_64',  // CentOS 5.2 64 bit
        'BD_MIDDLE_OS_VERSION_CENTOS5_3',     // CentOS 5.3
        'BD_MIDDLE_OS_VERSION_CENTOS5_3_64',  // CentOS 5.3 64 bit
        'BD_MIDDLE_OS_VERSION_CENTOS5_4',     // CentOS 5.4
        'BD_MIDDLE_OS_VERSION_CENTOS5_4_64',  // CentOS 5.4 64 bit
        'BD_MIDDLE_OS_VERSION_CENTOS5_5',     // CentOS 5.5
        'BD_MIDDLE_OS_VERSION_CENTOS5_5_64',  // CentOS 5.5 64 bit
        'BD_MIDDLE_OS_VERSION_CENTOS5_6',     // CentOS 5.6
        'BD_MIDDLE_OS_VERSION_CENTOS5_6_64',  // CentOS 5.6 64 bit
        'BD_MIDDLE_OS_VERSION_CENTOS5_7',     // CentOS 5.7
        'BD_MIDDLE_OS_VERSION_CENTOS5_7_64',  // CentOS 5.7 64 bit
        'BD_MIDDLE_OS_VERSION_CENTOS5_8',     // CentOS 5.8
        'BD_MIDDLE_OS_VERSION_CENTOS5_8_64',  // CentOS 5.8 64 bit
        'BD_MIDDLE_OS_VERSION_CENTOS5_9',     // CentOS 5.9
        'BD_MIDDLE_OS_VERSION_CENTOS5_9_64',  // CentOS 5.9 64 bit
        'BD_MIDDLE_OS_VERSION_CENTOS5_10',    // CentOS 5.10
        'BD_MIDDLE_OS_VERSION_CENTOS5_10_64', // CentOS 5.10 64 bit
        'BD_MIDDLE_OS_VERSION_CENTOS5_11',    // CentOS 5.11
        'BD_MIDDLE_OS_VERSION_CENTOS5_11_64', // CentOS 5.11 64 bit
        'BD_MIDDLE_OS_VERSION_CENTOS6',    // centos 6 32 bit
        'BD_MIDDLE_OS_VERSION_CENTOS6_64', // centos 6 64 bit
        'BD_MIDDLE_OS_VERSION_CENTOS6_0_32',  // CentOS 6.0 32bit
        'BD_MIDDLE_OS_VERSION_CENTOS6_0_64',  // CentOS 6.0 64bit
        'BD_MIDDLE_OS_VERSION_CENTOS6_1_32',  // CentOS 6.1 32bit
        'BD_MIDDLE_OS_VERSION_CENTOS6_1_64',  // CentOS 6.1 64bit -- 30050
        'BD_MIDDLE_OS_VERSION_CENTOS6_10_32', // CentOS 6.10 32bit
        'BD_MIDDLE_OS_VERSION_CENTOS6_10_64', // CentOS 6.10 64bit
        'BD_MIDDLE_OS_VERSION_CENTOS6_2_32',  // CentOS 6.2 32bit
        'BD_MIDDLE_OS_VERSION_CENTOS6_2_64',  // CentOS 6.2 64bit
        'BD_MIDDLE_OS_VERSION_CENTOS6_3_32',  // CentOS 6.3 32bit
        'BD_MIDDLE_OS_VERSION_CENTOS6_3_64',  // CentOS 6.3 64bit
        'BD_MIDDLE_OS_VERSION_CENTOS6_4_32',  // CentOS 6.4 32bit
        'BD_MIDDLE_OS_VERSION_CENTOS6_4_64',  // CentOS 6.4 64bit
        'BD_MIDDLE_OS_VERSION_CENTOS6_5_32',  // CentOS 6.5 32bit
        'BD_MIDDLE_OS_VERSION_CENTOS6_5_64',  // CentOS 6.5 64bit
        'BD_MIDDLE_OS_VERSION_CENTOS6_6_32',  // CentOS 6.6 32bit
        'BD_MIDDLE_OS_VERSION_CENTOS6_6_64',  // CentOS 6.6 64bit
        'BD_MIDDLE_OS_VERSION_CENTOS6_7_32',  // CentOS 6.7 32bit
        'BD_MIDDLE_OS_VERSION_CENTOS6_7_64',  // CentOS 6.7 64bit
        'BD_MIDDLE_OS_VERSION_CENTOS6_8_32',  // CentOS 6.8 32bit
        'BD_MIDDLE_OS_VERSION_CENTOS6_8_64',  // CentOS 6.8 64bit
        'BD_MIDDLE_OS_VERSION_CENTOS6_9_32',  // CentOS 6.9 32bit
        'BD_MIDDLE_OS_VERSION_CENTOS6_9_64',  // CentOS 6.9 64bit
        'BD_MIDDLE_OS_VERSION_CENTOS7',    // centos 7 32 bit
        'BD_MIDDLE_OS_VERSION_CENTOS7_64', // centos 7 64 bit
        'BD_MIDDLE_OS_VERSION_CENTOS7_0_64',  // CentOS 7.0 64bit
        'BD_MIDDLE_OS_VERSION_CENTOS7_1_32',  // CentOS 7.1 32bit
        'BD_MIDDLE_OS_VERSION_CENTOS7_1_64',  // CentOS 7.1 64bit
        'BD_MIDDLE_OS_VERSION_CENTOS7_2_32',  // CentOS 7.2 32bit
        'BD_MIDDLE_OS_VERSION_CENTOS7_2_64',  // CentOS 7.2 64bit
        'BD_MIDDLE_OS_VERSION_CENTOS7_3_64',  // CentOS 7.3 64bit
        'BD_MIDDLE_OS_VERSION_CENTOS7_4_64',  // CentOS 7.4 64bit
        'BD_MIDDLE_OS_VERSION_CENTOS7_5_64',  // CentOS 7.5 64bit
        'BD_MIDDLE_OS_VERSION_CENTOS7_6_64',  // CentOS 7.6 64bit
        'BD_MIDDLE_OS_VERSION_CENTOS7_7_64',  // CentOS 7.7 64bit
        'BD_MIDDLE_OS_VERSION_CENTOS7_8_64',  // CentOS 7.8 64bit
        'BD_MIDDLE_OS_VERSION_CENTOS7_9_64',  // CentOS 7.9 64bit
        'BD_MIDDLE_OS_VERSION_CENTOS8',    // centos 8 32 bit
        'BD_MIDDLE_OS_VERSION_CENTOS8_64', // centos 8 64 bit
        'BD_MIDDLE_OS_VERSION_CENTOS8_0_64',  // CentOS 8.0 64bit
        'BD_MIDDLE_OS_VERSION_CENTOS8_1_64',  // CentOS 8.1 64bit
        'BD_MIDDLE_OS_VERSION_CENTOS8_2_64',  // CentOS 8.2 64bit
        'BD_MIDDLE_OS_VERSION_CENTOS8_3_64',  // CentOS 8.3 64bit
        'BD_MIDDLE_OS_VERSION_CENTOS8_4_64',  // CentOS 8.4 64bit
        'BD_MIDDLE_OS_VERSION_CENTOS8_5_64',  // CentOS 8.5 64bit
        'BD_MIDDLE_OS_VERSION_CENTOS9',    // centos 9 32 bit
        'BD_MIDDLE_OS_VERSION_CENTOS9_64', // centos 9 64 bit

        // coreos serials
        'BD_MIDDLE_OS_VERSION_CORE_OS_64', // coreos 64 bit
        'BD_MIDDLE_OS_VERSION_CORE_OS_1122_2_0_64', //CoreOS 1122.2.0 64bit
        'BD_MIDDLE_OS_VERSION_CORE_OS_1298_5_0_64',  // CoreOS 1298.5.0 64bit
        'BD_MIDDLE_OS_VERSION_CORE_OS_1465_8_0_64',  // CoreOS 1465.8.0 64bit
        'BD_MIDDLE_OS_VERSION_CORE_OS_1520_8_0_64',  // CoreOS 1520.8.0 64bit
        'BD_MIDDLE_OS_VERSION_CORE_OS_1632_0_0_64',  // CoreOS 1632.0.0 64bit
        'BD_MIDDLE_OS_VERSION_CORE_OS_1745_2_0_64',  // CoreOS 1745.2.0 64bit
        'BD_MIDDLE_OS_VERSION_CORE_OS_1800_1_0_64',  // CoreOS 1800.1.0 64bit -- 30100

        // CtyunOS
        'BD_MIDDLE_OS_VERSION_CTYUN_OS_2_X_64', //CtyunOS 2.x 64bit

        // Debian OS serials
        'BD_MIDDLE_OS_VERSION_DEBIAN',      // Debian bit
        'BD_MIDDLE_OS_VERSION_DEBIAN_64',   // Debian 64 bit
        'BD_MIDDLE_OS_VERSION_DEBIAN4',     // Debian 4
        'BD_MIDDLE_OS_VERSION_DEBIAN4_64',  // Debian 4 64 bit
        'BD_MIDDLE_OS_VERSION_DEBIAN5',     // Debian 5
        'BD_MIDDLE_OS_VERSION_DEBIAN5_64',  // Debian 5 64 bit
        'BD_MIDDLE_OS_VERSION_DEBIAN6',     // Debian 6
        'BD_MIDDLE_OS_VERSION_DEBIAN6_64',  // Debian 6 64 bit
        'BD_MIDDLE_OS_VERSION_DEBIAN7',     // Debian 7
        'BD_MIDDLE_OS_VERSION_DEBIAN7_64',  // Debian 7 64 bit
        'BD_MIDDLE_OS_VERSION_DEBIAN7_0_0_32',  // Debian GNU/Linux 7.0.0 32bit
        'BD_MIDDLE_OS_VERSION_DEBIAN7_0_0_64',  // Debian GNU/Linux 7.0.0 64bit
        'BD_MIDDLE_OS_VERSION_DEBIAN7_1_0_32',  // Debian GNU/Linux 7.1.0 32bit
        'BD_MIDDLE_OS_VERSION_DEBIAN7_1_0_64',  // Debian GNU/Linux 7.1.0 64bit
        'BD_MIDDLE_OS_VERSION_DEBIAN7_10_0_32', // Debian GNU/Linux 7.10.0 32bit
        'BD_MIDDLE_OS_VERSION_DEBIAN7_10_0_64', // Debian GNU/Linux 7.10.0 64bit
        'BD_MIDDLE_OS_VERSION_DEBIAN7_11_0_64', // Debian GNU/Linux 7.11.0 64bit
        'BD_MIDDLE_OS_VERSION_DEBIAN7_2_0_32',  // Debian GNU/Linux 7.2.0 32bit
        'BD_MIDDLE_OS_VERSION_DEBIAN7_2_0_64',  // Debian GNU/Linux 7.2.0 64bit
        'BD_MIDDLE_OS_VERSION_DEBIAN7_3_0_32',  // Debian GNU/Linux 7.3.0 32bit
        'BD_MIDDLE_OS_VERSION_DEBIAN7_3_0_64',  // Debian GNU/Linux 7.3.0 64bit
        'BD_MIDDLE_OS_VERSION_DEBIAN7_4_0_32',  // Debian GNU/Linux 7.4.0 32bit
        'BD_MIDDLE_OS_VERSION_DEBIAN7_4_0_64',  // Debian GNU/Linux 7.4.0 64bit
        'BD_MIDDLE_OS_VERSION_DEBIAN7_5_0_32',  // Debian GNU/Linux 7.5.0 32bit
        'BD_MIDDLE_OS_VERSION_DEBIAN7_5_0_64',  // Debian GNU/Linux 7.5.0 64bit
        'BD_MIDDLE_OS_VERSION_DEBIAN7_6_0_32',  // Debian GNU/Linux 7.6.0 32bit
        'BD_MIDDLE_OS_VERSION_DEBIAN7_6_0_64',  // Debian GNU/Linux 7.6.0 64bit
        'BD_MIDDLE_OS_VERSION_DEBIAN7_7_0_32',  // Debian GNU/Linux 7.7.0 32bit
        'BD_MIDDLE_OS_VERSION_DEBIAN7_7_0_64',  // Debian GNU/Linux 7.7.0 64bit
        'BD_MIDDLE_OS_VERSION_DEBIAN7_8_0_32',  // Debian GNU/Linux 7.8.0 32bit
        'BD_MIDDLE_OS_VERSION_DEBIAN7_8_0_64',  // Debian GNU/Linux 7.8.0 64bit
        'BD_MIDDLE_OS_VERSION_DEBIAN8',     // Debian 8
        'BD_MIDDLE_OS_VERSION_DEBIAN8_64',  // Debian 8 64 bit
        'BD_MIDDLE_OS_VERSION_DEBIAN8_0_0_32',  // Debian GNU/Linux 8.0.0 32bit
        'BD_MIDDLE_OS_VERSION_DEBIAN8_0_0_64',  // Debian GNU/Linux 8.0.0 64bit
        'BD_MIDDLE_OS_VERSION_DEBIAN8_0_0_4_9_18_1_64',  // Debian GNU/Linux 8.0.0(4.9.18-1) 64bit
        'BD_MIDDLE_OS_VERSION_DEBIAN8_10_0_64', // Debian GNU/Linux 8.10.0 64bit
        'BD_MIDDLE_OS_VERSION_DEBIAN8_11_0_64', // Debian GNU/Linux 8.11.0 64bit
        'BD_MIDDLE_OS_VERSION_DEBIAN8_2_0_32',  // Debian GNU/Linux 8.2.0 32bit
        'BD_MIDDLE_OS_VERSION_DEBIAN8_2_0_64',  // Debian GNU/Linux 8.2.0 64bit
        'BD_MIDDLE_OS_VERSION_DEBIAN8_4_0_32',  // Debian GNU/Linux 8.4.0 32bit
        'BD_MIDDLE_OS_VERSION_DEBIAN8_4_0_64',  // Debian GNU/Linux 8.4.0 64bit
        'BD_MIDDLE_OS_VERSION_DEBIAN8_5_0_32',  // Debian GNU/Linux 8.5.0 32bit
        'BD_MIDDLE_OS_VERSION_DEBIAN8_5_0_64',  // Debian GNU/Linux 8.5.0 64bit
        'BD_MIDDLE_OS_VERSION_DEBIAN8_6_0_32',  // Debian GNU/Linux 8.6.0 32bit
        'BD_MIDDLE_OS_VERSION_DEBIAN8_6_0_64',  // Debian GNU/Linux 8.6.0 64bit
        'BD_MIDDLE_OS_VERSION_DEBIAN8_7_0_64',  // Debian GNU/Linux 8.7.0 64bit

        'BD_MIDDLE_OS_VERSION_DEBIAN8_8_0_64',  // Debian GNU/Linux 8.8.0 64bit
        'BD_MIDDLE_OS_VERSION_DEBIAN8_9_0_64',  // Debian GNU/Linux 8.9.0 64bit -- 30150
        'BD_MIDDLE_OS_VERSION_DEBIAN9',     // Debian GNU/Linux 9
        'BD_MIDDLE_OS_VERSION_DEBIAN9_64',  // Debian GNU/Linux 9 64 bit
        'BD_MIDDLE_OS_VERSION_DEBIAN9_0_0_64',  // Debian GNU/Linux 9.0.0 64bit
        'BD_MIDDLE_OS_VERSION_DEBIAN9_11_0_64',  // Debian GNU/Linux 9.11.0 64bit
        'BD_MIDDLE_OS_VERSION_DEBIAN9_12_0_64',  // Debian GNU/Linux 9.12.0 64bit
        'BD_MIDDLE_OS_VERSION_DEBIAN9_13_0_64',  // Debian GNU/Linux 9.13.0 64bit
        'BD_MIDDLE_OS_VERSION_DEBIAN9_3_0_64',  // Debian GNU/Linux 9.3.0 64bit
        'BD_MIDDLE_OS_VERSION_DEBIAN9_4_0_64',  // Debian GNU/Linux 9.4.0 64bit
        'BD_MIDDLE_OS_VERSION_DEBIAN9_5_0_64',  // Debian GNU/Linux 9.5.0 64bit
        'BD_MIDDLE_OS_VERSION_DEBIAN9_6_0_64',  // Debian GNU/Linux 9.6.0 64bit
        'BD_MIDDLE_OS_VERSION_DEBIAN9_7_0_64',  // Debian GNU/Linux 9.7.0 64bit
        'BD_MIDDLE_OS_VERSION_DEBIAN9_8_0_64',  // Debian GNU/Linux 9.8.0 64bit
        'BD_MIDDLE_OS_VERSION_DEBIAN9_9_0_64',  // Debian GNU/Linux 9.9.0 64bit
        'BD_MIDDLE_OS_VERSION_DEBIAN10',    // Debian GNU/Linux 10
        'BD_MIDDLE_OS_VERSION_DEBIAN10_64', // Debian GNU/Linux 10 64 bit
        'BD_MIDDLE_OS_VERSION_DEBIAN10_0_0_64',  // Debian GNU/Linux 10.0.0 64bit
        'BD_MIDDLE_OS_VERSION_DEBIAN10_1_0_64',  // Debian GNU/Linux 10.1.0 64bit
        'BD_MIDDLE_OS_VERSION_DEBIAN10_2_0_64',  // Debian GNU/Linux 10.2.0 64bit
        'BD_MIDDLE_OS_VERSION_DEBIAN10_3_0_64',  // Debian GNU/Linux 10.3.0 64bit
        'BD_MIDDLE_OS_VERSION_DEBIAN10_4_0_64',  // Debian GNU/Linux 10.4.0 64bit
        'BD_MIDDLE_OS_VERSION_DEBIAN10_5_0_64',  // Debian GNU/Linux 10.5.0 64bit
        'BD_MIDDLE_OS_VERSION_DEBIAN10_7_0_64',  // Debian GNU/Linux 10.7.0 64bit
        'BD_MIDDLE_OS_VERSION_DEBIAN11',    // Debian GNU/Linux 11
        'BD_MIDDLE_OS_VERSION_DEBIAN11_64', // Debian GNU/Linux 11 64 bit
        'BD_MIDDLE_OS_VERSION_DEBIAN11_3_64',  // Debian GNU/Linux 11.3 64bit
        'BD_MIDDLE_OS_VERSION_DEBIAN11_5_64',  // Debian GNU/Linux 11.5 64bit
        'BD_MIDDLE_OS_VERSION_DEBIAN12_X_64',  // Debian GNU/Linux 12.x 64bit
        'BD_MIDDLE_OS_VERSION_DEBIAN12',    // Debian GNU/Linux 12
        'BD_MIDDLE_OS_VERSION_DEBIAN12_64', // Debian GNU/Linux 12 64 bit
        'BD_MIDDLE_OS_VERSION_DEEPIN_SERVER_15_64',  // Deepin GNU/Linux (Server 15) 64bit

        // eComStation (eCS)
        'BD_MIDDLE_OS_VERSION_ECS1', // eComStation 1
        'BD_MIDDLE_OS_VERSION_ECS2', // eComStation 1

        //Fusion
        'BD_MIDDLE_OS_VERSION_FUSION_OS_22_0_4_64',  //FusionOS 22.0.4 64bit

        // fedora serials
        'BD_MIDDLE_OS_VERSION_FEDORA',    // Fedora Linux
        'BD_MIDDLE_OS_VERSION_FEDORA_64', // Fedora Linux 64 bit
        'BD_MIDDLE_OS_VERSION_FEDORA25_64', // Fedora 25 64
        'BD_MIDDLE_OS_VERSION_FEDORA24_64', // Fedora 24 64
        'BD_MIDDLE_OS_VERSION_FEDORA23_64', // Fedora 23 64
        'BD_MIDDLE_OS_VERSION_FEDORA22_64', // Fedora 22 64
        'BD_MIDDLE_OS_VERSION_FEDORA21_64', // Fedora 21 64
        'BD_MIDDLE_OS_VERSION_FEDORA20_64', // Fedora 20 64
        'BD_MIDDLE_OS_VERSION_FEDORA19_64', // Fedora 19 64
        'BD_MIDDLE_OS_VERSION_FEDORA18_64', // Fedora 18 64
        'BD_MIDDLE_OS_VERSION_FEDORA17_64', // Fedora 17 64
        'BD_MIDDLE_OS_VERSION_FEDORA16_64', // Fedora 16 64
        'BD_MIDDLE_OS_VERSION_FEDORA15_64', // Fedora 15 64
        'BD_MIDDLE_OS_VERSION_FEDORA14_64', // Fedora 14 64
        'BD_MIDDLE_OS_VERSION_FEDORA13_64', // Fedora 13 64
        'BD_MIDDLE_OS_VERSION_FEDORA12',    // Fedora 12 32bit
        'BD_MIDDLE_OS_VERSION_FEDORA12_64', // Fedora 12 64 -- 30200
        'BD_MIDDLE_OS_VERSION_FEDORA11',    // Fedora 11 32bit
        'BD_MIDDLE_OS_VERSION_FEDORA11_64', // Fedora 11 64
        'BD_MIDDLE_OS_VERSION_FEDORA10',    // Fedora 10 32bit
        'BD_MIDDLE_OS_VERSION_FEDORA10_64', // Fedora 10 64
        'BD_MIDDLE_OS_VERSION_FEDORA9',     // Fedora 9 32bit
        'BD_MIDDLE_OS_VERSION_FEDORA9_64',  // Fedora 9 64
        'BD_MIDDLE_OS_VERSION_FEDORA8',     // Fedora 8 32bit
        'BD_MIDDLE_OS_VERSION_FEDORA8_64',  // Fedora 8 64
        'BD_MIDDLE_OS_VERSION_FEDORA7',     // Fedora 7 32bit
        'BD_MIDDLE_OS_VERSION_FEDORA7_64',  // Fedora 7 64
        'BD_MIDDLE_OS_VERSION_FEDORA6',     // Fedora 6 32bit
        'BD_MIDDLE_OS_VERSION_FEDORA6_64',  // Fedora 6 64
        'BD_MIDDLE_OS_VERSION_FEDORA26_SERVER_EDITION_64',  // Fedora 26 (Server Edition) 64bit
        'BD_MIDDLE_OS_VERSION_FEDORA26_WORKSTATION_EDITION_64',  // Fedora 26 (Workstation Edition) 64bit
        'BD_MIDDLE_OS_VERSION_FEDORA27_SERVER_EDITION_64',  // Fedora 27 (Server Edition) 64bit
        'BD_MIDDLE_OS_VERSION_FEDORA28_SERVER_EDITION_64',  // Fedora 28 (Server Edition) 64bit
        'BD_MIDDLE_OS_VERSION_FEDORA28_WORKSTATION_EDITION_64',  // Fedora 28 (Workstation Edition) 64bit
        'BD_MIDDLE_OS_VERSION_FEDORA29_SERVER_EDITION_64',  // Fedora 29 (Server Edition) 64bit
        'BD_MIDDLE_OS_VERSION_FEDORA30_SERVER_EDITION_64',  // Fedora 30 (Server Edition) 64bit
        'BD_MIDDLE_OS_VERSION_FEDORA31_SERVER_EDITION_64',  // Fedora 31 (Server Edition) 64bit
        'BD_MIDDLE_OS_VERSION_FEDORA32_SERVER_EDITION_64',  // Fedora 32 (Server Edition) 64bit
        'BD_MIDDLE_OS_VERSION_FEDORA33_SERVER_EDITION_64',  // Fedora 33 (Server Edition) 64bit
        'BD_MIDDLE_OS_VERSION_FEDORA34_SERVER_EDITION_64',  // Fedora 34 (Server Edition) 64bit
        'BD_MIDDLE_OS_VERSION_FEDORA35_SERVER_EDITION_64',  // Fedora 35 (Server Edition) 64bit
        'BD_MIDDLE_OS_VERSION_FEDORA36_SERVER_EDITION_64',  // Fedora 36 (Server Edition) 64bit
        'BD_MIDDLE_OS_VERSION_FEDORA37_SERVER_EDITION_64',  // Fedora 37 (Server Edition) 64bit
        'BD_MIDDLE_OS_VERSION_FEDORA38_SERVER_EDITION_64',  // Fedora 38 (Server Edition) 64bit
        'BD_MIDDLE_OS_VERSION_FEDORA39_SERVER_EDITION_64',  // Fedora 39 (Server Edition) 64bit
        'BD_MIDDLE_OS_VERSION_FEDORACORE_OS_33_64',  // Fedora CoreOS 33 64bit

        // FreeBSD serials
        'BD_MIDDLE_OS_VERSION_FREEBSD',      // FreeBSD
        'BD_MIDDLE_OS_VERSION_FREEBSD_64',   // FreeBSD 64 bit
        'BD_MIDDLE_OS_VERSION_FREEBSD11',    // FreeBSD11
        'BD_MIDDLE_OS_VERSION_FREEBSD11_64', // FreeBSD11 64 bit
        'BD_MIDDLE_OS_VERSION_FREEBSD12',    // FreeBSD12
        'BD_MIDDLE_OS_VERSION_FREEBSD12_64', // FreeBSD12 64 bit
        'BD_MIDDLE_OS_VERSION_FREEBSD13',    // FreeBSD13
        'BD_MIDDLE_OS_VERSION_FREEBSD13_64', // FreeBSD13 64 bit
        'BD_MIDDLE_OS_VERSION_FREEBSD14',    // FreeBSD14
        'BD_MIDDLE_OS_VERSION_FREEBSD14_64', // FreeBSD14 64 bit

        // generic linux
        'BD_MIDDLE_OS_VERSION_GENERIC_LINUX', // generic linux

        // mandrake linux serials
        'BD_MIDDLE_OS_VERSION_MANDRAKE',    // mandrake linux
        'BD_MIDDLE_OS_VERSION_MANDRAKE_64', // mandrake linux 64 bit
        'BD_MIDDLE_OS_VERSION_MANDRIVA',    // mandriva linux

        // novall serials
        'BD_MIDDLE_OS_VERSION_NETWARE4', // Novell Netware 4
        'BD_MIDDLE_OS_VERSION_NETWARE5', // Novell Netware 5
        'BD_MIDDLE_OS_VERSION_NETWARE6', // Novell Netware 6
        'BD_MIDDLE_OS_VERSION_NLD9',     // Novell Linux Desktop 9

        // Open Enterprise Server serials
        'BD_MIDDLE_OS_VERSION_OES', // Open Enterprise Server

        // SCO OpenServer serials
        'BD_MIDDLE_OS_VERSION_OPEN_SERVER5', // SCO OpenServer 5
        'BD_MIDDLE_OS_VERSION_OPEN_SERVER6', // SCO OpenServer 6 -- 30250

        // OpenSUSE serials
        'BD_MIDDLE_OS_VERSION_OPEN_SUSE',    // OpenSUSE
        'BD_MIDDLE_OS_VERSION_OPEN_SUSE_64', // OpenSUSE 64 bit
        'BD_MIDDLE_OS_VERSION_OPEN_SUSE_LEAP_15_0_64',  // openSUSE Leap 15.0 64bit
        'BD_MIDDLE_OS_VERSION_OPEN_SUSE_LEAP_15_1_64',  // openSUSE Leap 15.1 64bit
        'BD_MIDDLE_OS_VERSION_OPEN_SUSE_LEAP_42_1_64',  // openSUSE Leap 42.1 64bit
        'BD_MIDDLE_OS_VERSION_OPEN_SUSE_LEAP_42_2_64',  // openSUSE Leap 42.2 64bit
        'BD_MIDDLE_OS_VERSION_OPEN_SUSE_LEAP_42_3_64',  // openSUSE Leap 42.3 64bit


        // oracle linux serials
        'BD_MIDDLE_OS_VERSION_ORACLE_LINUX',     // Oracle Linux 4/5
        'BD_MIDDLE_OS_VERSION_ORACLE_LINUX64',  // Oracle Linux 4/5 64 bit
        'BD_MIDDLE_OS_VERSION_ORACLE_LINUX5_6_64',  // Oracle Linux 5.6 64bit
        'BD_MIDDLE_OS_VERSION_ORACLE_LINUX5_7_64',  // Oracle Linux 5.7 64bit
        'BD_MIDDLE_OS_VERSION_ORACLE_LINUX5_8_64',  // Oracle Linux 5.8 64bit
        'BD_MIDDLE_OS_VERSION_ORACLE_LINUX5_9_64',  // Oracle Linux 5.9 64bit
        'BD_MIDDLE_OS_VERSION_ORACLE_LINUX5_10_64',  // Oracle Linux 5.10 64bit
        'BD_MIDDLE_OS_VERSION_ORACLE_LINUX5_11_32',  // Oracle Linux 5.11 32bit
        'BD_MIDDLE_OS_VERSION_ORACLE_LINUX5_11_64',  // Oracle Linux 5.11 64bit
        'BD_MIDDLE_OS_VERSION_ORACLE_LINUX6',    // Oracle Linux 6
        'BD_MIDDLE_OS_VERSION_ORACLE_LINUX6_64', // Oracle Linux 6 64 bit
        'BD_MIDDLE_OS_VERSION_ORACLE_LINUX6_3_32',  // Oracle Linux 6.3 32bit
        'BD_MIDDLE_OS_VERSION_ORACLE_LINUX6_3_64',  // Oracle Linux 6.3 64bit
        'BD_MIDDLE_OS_VERSION_ORACLE_LINUX6_4_64',  // Oracle Linux 6.4 64bit
        'BD_MIDDLE_OS_VERSION_ORACLE_LINUX6_5_32',  // Oracle Linux 6.5 32bit
        'BD_MIDDLE_OS_VERSION_ORACLE_LINUX6_5_64',  // Oracle Linux 6.5 64bit
        'BD_MIDDLE_OS_VERSION_ORACLE_LINUX6_6_32',  // Oracle Linux 6.6 32bit
        'BD_MIDDLE_OS_VERSION_ORACLE_LINUX6_6_64',  // Oracle Linux 6.6 64bit
        'BD_MIDDLE_OS_VERSION_ORACLE_LINUX6_7_32',  // Oracle Linux 6.7 32bit
        'BD_MIDDLE_OS_VERSION_ORACLE_LINUX6_7_64',  // Oracle Linux 6.7 64bit
        'BD_MIDDLE_OS_VERSION_ORACLE_LINUX6_8_32',  // Oracle Linux 6.8 32bit
        'BD_MIDDLE_OS_VERSION_ORACLE_LINUX6_8_64',  // Oracle Linux 6.8 64bit
        'BD_MIDDLE_OS_VERSION_ORACLE_LINUX6_9_64',  // Oracle Linux 6.9 64bit
        'BD_MIDDLE_OS_VERSION_ORACLE_LINUX6_10_64',  // Oracle Linux 6.10 64bit
        'BD_MIDDLE_OS_VERSION_ORACLE_LINUX7',    // Oracle Linux 7
        'BD_MIDDLE_OS_VERSION_ORACLE_LINUX7_64', // Oracle Linux 7 64 bit
        'BD_MIDDLE_OS_VERSION_ORACLE_LINUX7_0_64',  // Oracle Linux 7.0 64 bit
        'BD_MIDDLE_OS_VERSION_ORACLE_LINUX7_1_64',  // Oracle Linux 7.1 64 bit
        'BD_MIDDLE_OS_VERSION_ORACLE_LINUX7_2_64',  // Oracle Linux 7.2 64 bit
        'BD_MIDDLE_OS_VERSION_ORACLE_LINUX7_3_64',  // Oracle Linux 7.3 64 bit
        'BD_MIDDLE_OS_VERSION_ORACLE_LINUX7_4_64',  // Oracle Linux 7.4 64 bit
        'BD_MIDDLE_OS_VERSION_ORACLE_LINUX7_5_64',  // Oracle Linux 7.5 64 bit
        'BD_MIDDLE_OS_VERSION_ORACLE_LINUX7_6_64',  // Oracle Linux 7.6 64 bit
        'BD_MIDDLE_OS_VERSION_ORACLE_LINUX8_64', // Oracle Linux 8 64 bit
        'BD_MIDDLE_OS_VERSION_ORACLE_LINUX8_6_64',  // Oracle Linux 8.6 64 bit
        'BD_MIDDLE_OS_VERSION_ORACLE_LINUX8_X_64',  // Oracle Linux 8.X 64 bit
        'BD_MIDDLE_OS_VERSION_ORACLE_LINUX9_64', // Oracle Linux 9 64 bit
        'BD_MIDDLE_OS_VERSION_ORACLE_LINUX9_X_64',  // Oracle Linux 9.X 64 bit

        // OS/2 
        'BD_MIDDLE_OS_VERSION_OS2', // OS/2

        // other linux serials
        'BD_MIDDLE_OS_VERSION_OTHER_LINUX',        // Linux 2.2X Kernel
        'BD_MIDDLE_OS_VERSION_OTHER_LINUX_64',     // Linux 64 bit
        'BD_MIDDLE_OS_VERSION_OTHER_24X_LINUX',    // Linux 2.4 Kernel
        'BD_MIDDLE_OS_VERSION_OTHER_24X_LINUX_64', // Linux 2.4 Kernel 64 bit -- 30300
        'BD_MIDDLE_OS_VERSION_OTHER_26X_LINUX',    // Linux 2.6 Kernel
        'BD_MIDDLE_OS_VERSION_OTHER_26X_LINUX_64', // Linux 2.6 Kernel 64 bit
        'BD_MIDDLE_OS_VERSION_OTHER_3X_LINUX',     // Linux 3.0 Kernel
        'BD_MIDDLE_OS_VERSION_OTHER_3X_LINUX_64',  // Linux 3.0 Kernel 64 bit
        'BD_MIDDLE_OS_VERSION_OTHER_4X_LINUX',     // Linux 4.0 Kernel
        'BD_MIDDLE_OS_VERSION_OTHER_4X_LINUX_64',  // Linux 4.0 Kernel 64 bit
        'BD_MIDDLE_OS_VERSION_OTHER_5X_LINUX',     // Linux 5.0 Kernel
        'BD_MIDDLE_OS_VERSION_OTHER_5X_LINUX_64',  // Linux 5.0 Kernel 64 bit
        'BD_MIDDLE_OS_VERSION_OTHER_6X_LINUX',     // Linux 6.0 Kernel
        'BD_MIDDLE_OS_VERSION_OTHER_6X_LINUX_64',  // Linux 6.0 Kernel 64 bit

        // Red Hat Enterprise Linux serials
        'BD_MIDDLE_OS_VERSION_RED_HAT',     // Red Hat Linux 2.1
        'BD_MIDDLE_OS_VERSION_RHEL2',       // Red Hat Enterprise Linux 2
        'BD_MIDDLE_OS_VERSION_RHEL3',       // Red Hat Enterprise Linux 3
        'BD_MIDDLE_OS_VERSION_RHEL3_64',    // Red Hat Enterprise Linux 3 64 bit
        'BD_MIDDLE_OS_VERSION_RHEL4',       // Red Hat Enterprise Linux 4
        'BD_MIDDLE_OS_VERSION_RHEL4_64',    // Red Hat Enterprise Linux 4 64 bit
        'BD_MIDDLE_OS_VERSION_RHEL5',       // Red Hat Enterprise Linux 5
        'BD_MIDDLE_OS_VERSION_RHEL5_64',    // Red Hat Enterprise Linux 5 64 bit
        'BD_MIDDLE_OS_VERSION_RHEL5_1',     // Red Hat Enterprise Linux 5.1
        'BD_MIDDLE_OS_VERSION_RHEL5_1_64',  // Red Hat Enterprise Linux 5.1 64 bit
        'BD_MIDDLE_OS_VERSION_RHEL5_2',     // Red Hat Enterprise Linux 5.2
        'BD_MIDDLE_OS_VERSION_RHEL5_2_64',  // Red Hat Enterprise Linux 5.2 64 bit
        'BD_MIDDLE_OS_VERSION_RHEL5_3',     // Red Hat Enterprise Linux 5.3
        'BD_MIDDLE_OS_VERSION_RHEL5_3_64',  // Red Hat Enterprise Linux 5.3 64 bit
        'BD_MIDDLE_OS_VERSION_RHEL5_4',     // Red Hat Enterprise Linux 5.4
        'BD_MIDDLE_OS_VERSION_RHEL5_4_64',  // Red Hat Enterprise Linux 5.4 64 bit
        'BD_MIDDLE_OS_VERSION_RHEL5_5',     // Red Hat Enterprise Linux 5.5
        'BD_MIDDLE_OS_VERSION_RHEL5_5_64',  // Red Hat Enterprise Linux 5.5 64 bit
        'BD_MIDDLE_OS_VERSION_RHEL5_6',     // Red Hat Enterprise Linux 5.6
        'BD_MIDDLE_OS_VERSION_RHEL5_6_64',  // Red Hat Enterprise Linux 5.6 64 bit
        'BD_MIDDLE_OS_VERSION_RHEL5_7',     // Red Hat Enterprise Linux 5.7
        'BD_MIDDLE_OS_VERSION_RHEL5_7_64',  // Red Hat Enterprise Linux 5.7 64 bit
        'BD_MIDDLE_OS_VERSION_RHEL5_8',     // Red Hat Enterprise Linux 5.8
        'BD_MIDDLE_OS_VERSION_RHEL5_8_64',  // Red Hat Enterprise Linux 5.8 64 bit
        'BD_MIDDLE_OS_VERSION_RHEL5_9',     // Red Hat Enterprise Linux 5.9
        'BD_MIDDLE_OS_VERSION_RHEL5_9_64',  // Red Hat Enterprise Linux 5.9 64 bit
        'BD_MIDDLE_OS_VERSION_RHEL5_10',    // Red Hat Enterprise Linux 5.10
        'BD_MIDDLE_OS_VERSION_RHEL5_10_64', // Red Hat Enterprise Linux 5.10 64 bit
        'BD_MIDDLE_OS_VERSION_RHEL5_11',    // Red Hat Enterprise Linux 5.11
        'BD_MIDDLE_OS_VERSION_RHEL5_11_64', // Red Hat Enterprise Linux 5.11 64 bit
        'BD_MIDDLE_OS_VERSION_RHEL6',       // Red Hat Enterprise Linux 6
        'BD_MIDDLE_OS_VERSION_RHEL6_64',    // Red Hat Enterprise Linux 6 64 bit
        'BD_MIDDLE_OS_VERSION_RHEL6_0_32',          // Red Hat Enterprise Linux 6.0 32 bit
        'BD_MIDDLE_OS_VERSION_RHEL6_0_64',          // Red Hat Enterprise Linux 6.0 64 bit
        'BD_MIDDLE_OS_VERSION_RHEL6_1_32',          // Red Hat Enterprise Linux 6.1 32 bit
        'BD_MIDDLE_OS_VERSION_RHEL6_1_64',          // Red Hat Enterprise Linux 6.1 64 bit
        'BD_MIDDLE_OS_VERSION_RHEL6_2_32',          // Red Hat Enterprise Linux 6.2 32 bit
        'BD_MIDDLE_OS_VERSION_RHEL6_2_64',          // Red Hat Enterprise Linux 6.2 64 bit
        'BD_MIDDLE_OS_VERSION_RHEL6_3_32',          // Red Hat Enterprise Linux 6.3 32 bit
        'BD_MIDDLE_OS_VERSION_RHEL6_3_64',          // Red Hat Enterprise Linux 6.3 64 bit -- 30350
        'BD_MIDDLE_OS_VERSION_RHEL6_4_32',          // Red Hat Enterprise Linux 6.4 32 bit
        'BD_MIDDLE_OS_VERSION_RHEL6_4_64',          // Red Hat Enterprise Linux 6.4 64 bit
        'BD_MIDDLE_OS_VERSION_RHEL6_5_32',          // Red Hat Enterprise Linux 6.5 32 bit
        'BD_MIDDLE_OS_VERSION_RHEL6_5_64',          // Red Hat Enterprise Linux 6.5 64 bit
        'BD_MIDDLE_OS_VERSION_RHEL6_6_32',          // Red Hat Enterprise Linux 6.6 32 bit
        'BD_MIDDLE_OS_VERSION_RHEL6_6_64',          // Red Hat Enterprise Linux 6.6 64 bit
        'BD_MIDDLE_OS_VERSION_RHEL6_7_32',          // Red Hat Enterprise Linux 6.7 32 bit
        'BD_MIDDLE_OS_VERSION_RHEL6_7_64',          // Red Hat Enterprise Linux 6.7 64 bit
        'BD_MIDDLE_OS_VERSION_RHEL6_8_32',          // Red Hat Enterprise Linux 6.8 32 bit
        'BD_MIDDLE_OS_VERSION_RHEL6_8_64',          // Red Hat Enterprise Linux 6.8 64 bit
        'BD_MIDDLE_OS_VERSION_RHEL6_9_64',          // Red Hat Enterprise Linux 6.9 64 bit
        'BD_MIDDLE_OS_VERSION_RHEL6_10_32',         // Red Hat Enterprise Linux 6.10 32 bit
        'BD_MIDDLE_OS_VERSION_RHEL6_10_64',         // Red Hat Enterprise Linux 6.10 64 bit
        'BD_MIDDLE_OS_VERSION_RHEL7',       // Red Hat Enterprise Linux 7
        'BD_MIDDLE_OS_VERSION_RHEL7_64',    // Red Hat Enterprise Linux 7 64 bit
        'BD_MIDDLE_OS_VERSION_RHEL7_0_64',          // Red Hat Enterprise Linux 7.0 64 bit
        'BD_MIDDLE_OS_VERSION_RHEL7_1_64',          // Red Hat Enterprise Linux 7.1 64 bit
        'BD_MIDDLE_OS_VERSION_RHEL7_2_64',          // Red Hat Enterprise Linux 7.2 64 bit
        'BD_MIDDLE_OS_VERSION_RHEL7_3_64',          // Red Hat Enterprise Linux 7.3 64 bit
        'BD_MIDDLE_OS_VERSION_RHEL7_4_64',          // Red Hat Enterprise Linux 7.4 64 bit
        'BD_MIDDLE_OS_VERSION_RHEL7_5_64',          // Red Hat Enterprise Linux 7.5 64 bit
        'BD_MIDDLE_OS_VERSION_RHEL7_6_64',          // Red Hat Enterprise Linux 7.6 64 bit
        'BD_MIDDLE_OS_VERSION_RHEL7_7_64',          // Red Hat Enterprise Linux 7.7 64 bit
        'BD_MIDDLE_OS_VERSION_RHEL7_9_64',          // Red Hat Enterprise Linux 7.9 64 bit
        'BD_MIDDLE_OS_VERSION_RHEL8',       // Red Hat Enterprise Linux 8
        'BD_MIDDLE_OS_VERSION_RHEL8_64',    // Red Hat Enterprise Linux 8 64 bit
        'BD_MIDDLE_OS_VERSION_RHEL8_0_64',          // Red Hat Enterprise Linux 8.0 64 bit
        'BD_MIDDLE_OS_VERSION_RHEL8_1_64',          // Red Hat Enterprise Linux 8.1 64 bit
        'BD_MIDDLE_OS_VERSION_RHEL8_2_64',          // Red Hat Enterprise Linux 8.2 64 bit
        'BD_MIDDLE_OS_VERSION_RHEL8_3_64',          // Red Hat Enterprise Linux 8.3 64 bit
        'BD_MIDDLE_OS_VERSION_RHEL8_4_64',          // Red Hat Enterprise Linux 8.4 64 bit
        'BD_MIDDLE_OS_VERSION_RHEL8_5_64',          // Red Hat Enterprise Linux 8.5 64 bit
        'BD_MIDDLE_OS_VERSION_RHEL8_6_64',          // Red Hat Enterprise Linux 8.6 64 bit
        'BD_MIDDLE_OS_VERSION_RHEL8_7_64',          // Red Hat Enterprise Linux 8.7 64 bit
        'BD_MIDDLE_OS_VERSION_RHEL8_X_64',          // Red Hat Enterprise Linux 8.X 64 bit
        'BD_MIDDLE_OS_VERSION_RHEL9',       // Red Hat Enterprise Linux 9
        'BD_MIDDLE_OS_VERSION_RHEL9_64',    // Red Hat Enterprise Linux 9 64 bit
        'BD_MIDDLE_OS_VERSION_RHEL9_X_64',          // Red Hat Enterprise Linux 9.X 64 bit
        'BD_MIDDLE_OS_VERSION_RHELATOMIC_HOST_7_5_64', //Red Hat Enterprise Linux Atomic Host 7.5 6



        // Rocky Linux serials
        'BD_MIDDLE_OS_VERSION_ROCKY_LINUX',     // Rocky Linux
        'BD_MIDDLE_OS_VERSION_ROCKY_LINUX_64',  // Rocky Linux 64 bit
        'BD_MIDDLE_OS_VERSION_ROCKY_LINUX8',    // Rocky Linux 8
        'BD_MIDDLE_OS_VERSION_ROCKY_LINUX8_64', // Rocky Linux 8 64 bit
        'BD_MIDDLE_OS_VERSION_ROCKY_LINUX8_7_64',   // Rocky Linux 8.7 64 bit
        'BD_MIDDLE_OS_VERSION_ROCKY_LINUX9',    // Rocky Linux 9
        'BD_MIDDLE_OS_VERSION_ROCKY_LINUX9_64', // Rocky Linux 9 64 bit
        'BD_MIDDLE_OS_VERSION_ROCKY_LINUX9_X_64',   // Rocky Linux 9.X 64 bit
        'BD_MIDDLE_OS_VERSION_ROCKY_SECURE_SERVER_VERSION_6_0_80_64',  // Rocky Secure Server Version 6.0.80 64 bit
        'BD_MIDDLE_OS_VERSION_ROCKY_SECURE_SERVER_VERSION_6_0_80_4_9_0_0_BPO_1_LINX_SECURITY_AMD64_64', //Rocky Secure Server Version 6.0.80(4.9.0-0.bpo.1-linx-security-amd64)
        'BD_MIDDLE_OS_VERSION_ROCKY_VERSION_6_0_42_41_64', //Rocky Version 6.0.42.41



        // NeutonOS Linux serials
        'BD_MIDDLE_OS_VERSION_NEUTONOS_LINUX',     // NeutonOS Linux
        'BD_MIDDLE_OS_VERSION_NEUTONOS_LINUX_64',  // NeutonOS Linux 64 bit
        'BD_MIDDLE_OS_VERSION_NEUTONOS_LINUX8',    // NeutonOS Linux 8
        'BD_MIDDLE_OS_VERSION_NEUTONOS_LINUX8_64', // NeutonOS Linux 8 64 bit

        // Sun Java Desktop system
        'BD_MIDDLE_OS_VERSION_SJDS', // Sun Java Desktop System

        // Suse Linux Enterprise Server serials
        'BD_MIDDLE_OS_VERSION_SLES',      // Suse Linux Enterprise Server 9
        'BD_MIDDLE_OS_VERSION_SLES_64',   // Suse Linux Enterprise Server 9 64 bit
        'BD_MIDDLE_OS_VERSION_SLES10',    // Suse Linux Enterprise Server 10
        'BD_MIDDLE_OS_VERSION_SLES10_64', // Suse Linux Enterprise Server 10 64 bit
        'BD_MIDDLE_OS_VERSION_SLES11',    // Suse Linux Enterprise Server 11
        'BD_MIDDLE_OS_VERSION_SLES11_64', // Suse Linux Enterprise Server 11 64 bit
        'BD_MIDDLE_OS_VERSION_SLES11_SP3_32',      // Suse Linux Enterprise Server 11 SP3 32 bit
        'BD_MIDDLE_OS_VERSION_SLES11_SP3_64',      // Suse Linux Enterprise Server 11 SP3 64 bit
        'BD_MIDDLE_OS_VERSION_SLES11_SP4_32',      // Suse Linux Enterprise Server 11 SP4 32 bit
        'BD_MIDDLE_OS_VERSION_SLES11_SP4_64',      // Suse Linux Enterprise Server 11 SP4 64 bit
        'BD_MIDDLE_OS_VERSION_SLES11_SP4_SLESB1HANA_64', // Suse Linux Enterprise Server 11 SP4(slesb1hana) 64 bit
        'BD_MIDDLE_OS_VERSION_SLES12',    // Suse Linux Enterprise Server 12
        'BD_MIDDLE_OS_VERSION_SLES12_64', // Suse Linux Enterprise Server 12 64 bit
        'BD_MIDDLE_OS_VERSION_SLES12_SP1_64', // Suse Linux Enterprise Server 12 SP1 64 bit
        'BD_MIDDLE_OS_VERSION_SLES12_SP2_64', // Suse Linux Enterprise Server 12 SP2 64 bit
        'BD_MIDDLE_OS_VERSION_SLES12_SP3_64', // Suse Linux Enterprise Server 12 SP3 64 bit
        'BD_MIDDLE_OS_VERSION_SLES12_SP4_64', // Suse Linux Enterprise Server 12 SP4 64 bit
        'BD_MIDDLE_OS_VERSION_SLES12_SP5_64', // Suse Linux Enterprise Server 12 SP5 64 bit
        'BD_MIDDLE_OS_VERSION_SLES15_64', // Suse Linux Enterprise Server 15 64 bit
        'BD_MIDDLE_OS_VERSION_SLES15_SP1_64', // Suse Linux Enterprise Server 15 SP1 64 bit
        'BD_MIDDLE_OS_VERSION_SLES15_SP2_64', // Suse Linux Enterprise Server 15 SP2 64 bit
        'BD_MIDDLE_OS_VERSION_SLES15_SP3_64', // Suse Linux Enterprise Server 15 SP3 64 bit
        'BD_MIDDLE_OS_VERSION_SLES15_SP4_64', // Suse Linux Enterprise Server 15 SP4 64 bit
        'BD_MIDDLE_OS_VERSION_SLES16_64', // Suse Linux Enterprise Server 16 64 bit

        // Solaris serials
        'BD_MIDDLE_OS_VERSION_SOLARIS6',     // Solaris 6
        'BD_MIDDLE_OS_VERSION_SOLARIS7',     // Solaris 7
        'BD_MIDDLE_OS_VERSION_SOLARIS8',     // Solaris 8
        'BD_MIDDLE_OS_VERSION_SOLARIS9',     // Solaris 9
        'BD_MIDDLE_OS_VERSION_SOLARIS10',    // Solaris 10
        'BD_MIDDLE_OS_VERSION_SOLARIS10_64', // Solaris 10 64 bit
        'BD_MIDDLE_OS_VERSION_SOLARIS11_64', // Solaris 11 64 bit

        // Suse serials
        'BD_MIDDLE_OS_VERSION_SUSE',    // Suse Linux
        'BD_MIDDLE_OS_VERSION_SUSE_64', // Suse Linux 64 bit

        //ScientificLinux
        'BD_MIDDLE_OS_VERSION_SCIENTIFIC_LINUX_RELEASE_6_3_32', // Scientific Linux release 6.3 32 bit
        'BD_MIDDLE_OS_VERSION_SCIENTIFIC_LINUX_RELEASE_6_5_32', // Scientific Linux release 6.5 32 bit
        'BD_MIDDLE_OS_VERSION_SCIENTIFIC_LINUX_RELEASE_6_5_64', // Scientific Linux release 6.5 64 bit

        //Slackware
        'BD_MIDDLE_OS_VERSION_SLACKWARE_14_2_64', // Slackware 14.2 64 bit

        //TencentOSServer
        'BD_MIDDLE_OS_VERSION_TENCENT_OS_SERVER_3_1_FINAL_64', // TencentOS Server 3.1 (Final) 64 bit


        // Turbo Linux serials
        'BD_MIDDLE_OS_VERSION_TURBO_LINUX',    // Turbo Linux
        'BD_MIDDLE_OS_VERSION_TURBO_LINUX_64', // Turbo Linux 64 bit
        'BD_MIDDLE_OS_VERSION_TURBO_LINUX_16_LTS_64', // Turbo Linux 16 LTS 64 bit
        'BD_MIDDLE_OS_VERSION_TURBO_LINUX_ENTERPRISE_SERVER_15_64', // TurboLinux Enterprise Server 15 64 bit
        'BD_MIDDLE_OS_VERSION_TURBO_LINUX_ENTERPRISE_SERVER_16_64', // TurboLinux Enterprise Server 16 64 bit

        // Ubuntu Linux serials
        'BD_MIDDLE_OS_VERSION_UBUNTU',      // Ubuntu
        'BD_MIDDLE_OS_VERSION_UBUNTU_64',   // Ubuntu 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU16',    // Ubuntu 16
        'BD_MIDDLE_OS_VERSION_UBUNTU16_64', // Ubuntu 16 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU18',    // Ubuntu 18
        'BD_MIDDLE_OS_VERSION_UBUNTU18_64', // Ubuntu 18 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU20',    // Ubuntu 20
        'BD_MIDDLE_OS_VERSION_UBUNTU20_64', // Ubuntu 20 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU22',    // Ubuntu 22
        'BD_MIDDLE_OS_VERSION_UBUNTU22_64', // Ubuntu 22 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_DESKTOP_14_04_32', // Ubuntu Desktop 14.04 32 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_DESKTOP_14_04_64', // Ubuntu Desktop 14.04 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_DESKTOP_14_04_3_32', // Ubuntu Desktop 14.04.3 32 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_DESKTOP_14_04_3_64', // Ubuntu Desktop 14.04.3 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_DESKTOP_14_04_4_32', // Ubuntu Desktop 14.04.4 32 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_DESKTOP_14_04_4_64', // Ubuntu Desktop 14.04.4 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_DESKTOP_16_04_32', // Ubuntu Desktop 16.04 32 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_DESKTOP_16_04_64', // Ubuntu Desktop 16.04 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_DESKTOP_16_04_1_32', // Ubuntu Desktop 16.04.1 32 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_DESKTOP_16_04_1_64', // Ubuntu Desktop 16.04.1 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_DESKTOP_16_04_3_32', // Ubuntu Desktop 16.04.3 32 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_DESKTOP_16_04_3_64', // Ubuntu Desktop 16.04.3 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_DESKTOP_16_04_4_64', // Ubuntu Desktop 16.04.4 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_DESKTOP_16_10_32', // Ubuntu Desktop 16.10 32 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_DESKTOP_16_10_64', // Ubuntu Desktop 16.10 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_DESKTOP_17_04_64', // Ubuntu Desktop 17.04 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_DESKTOP_17_10_64', // Ubuntu Desktop 17.10 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_DESKTOP_18_04_64', // Ubuntu Desktop 18.04 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_DESKTOP_18_04_1_64', // Ubuntu Desktop 18.04.1 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_DESKTOP_18_04_4_LTS_64', // Ubuntu Desktop 18.04.4 LTS 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_DESKTOP_20_04_2_LTS_64', // Ubuntu Desktop 20.04.2 LTS 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_DESKTOP_22_04_X_64', // Ubuntu Desktop 22.04.x 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_KYLIN_16_04_6_64', // Ubuntu Kylin 16.04.6 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_KYLIN_18_10_64', // Ubuntu Kylin 18.10 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_SERVER_14_04_32', // Ubuntu Server 14.04 32 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_SERVER_14_04_64', // Ubuntu Server 14.04 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_SERVER_14_04_1_32', // Ubuntu Server 14.04.1 32 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_SERVER_14_04_1_64', // Ubuntu Server 14.04.1 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_SERVER_14_04_2_32', // Ubuntu Server 14.04.2 32 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_SERVER_14_04_2_64', // Ubuntu Server 14.04.2 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_SERVER_14_04_3_32', // Ubuntu Server 14.04.3 32 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_SERVER_14_04_3_64', // Ubuntu Server 14.04.3 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_SERVER_14_04_4_32', // Ubuntu Server 14.04.4 32 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_SERVER_14_04_4_64', // Ubuntu Server 14.04.4 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_SERVER_14_04_5_32', // Ubuntu Server 14.04.5 32 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_SERVER_14_04_5_64', // Ubuntu Server 14.04.5 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_SERVER_16_04_32', // Ubuntu Server 16.04 32 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_SERVER_16_04_64', // Ubuntu Server 16.04 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_SERVER_16_04_1_32', // Ubuntu Server 16.04.1 32 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_SERVER_16_04_1_64', // Ubuntu Server 16.04.1 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_SERVER_16_04_2_64', // Ubuntu Server 16.04.2 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_SERVER_16_04_3_32', // Ubuntu Server 16.04.3 32 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_SERVER_16_04_3_64', // Ubuntu Server 16.04.3 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_SERVER_16_04_4_64', // Ubuntu Server 16.04.4 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_SERVER_16_04_5_64', // Ubuntu Server 16.04.5 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_SERVER_16_04_6_64', // Ubuntu Server 16.04.6 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_SERVER_16_10_32', // Ubuntu Server 16.10 32 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_SERVER_16_10_64', // Ubuntu Server 16.10 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_SERVER_17_04_64', // Ubuntu Server 17.04 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_SERVER_17_10_64', // Ubuntu Server 17.10 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_SERVER_18_04_64', // Ubuntu Server 18.04 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_SERVER_18_04_1_LTS_64', // Ubuntu Server 18.04.1 LTS 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_SERVER_18_04_2_64', // Ubuntu Server 18.04.2 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_SERVER_18_04_3_64', // Ubuntu Server 18.04.3 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_SERVER_18_04_4_64', // Ubuntu Server 18.04.4 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_SERVER_18_04_6_64', // Ubuntu Server 18.04.6 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_SERVER_19_04_64', // Ubuntu Server 19.04 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_SERVER_19_10_64', // Ubuntu Server 19.10 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_SERVER_20_04_64', // Ubuntu Server 20.04 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_SERVER_22_04_64', // Ubuntu Server 22.04 64 bit
        'BD_MIDDLE_OS_VERSION_UBUNTU_SERVER_23_04_64', // Ubuntu Server 23.04 64 bit

        // SCO UnixWare 7
        'BD_MIDDLE_OS_VERSION_UNIX_WARE7', // SCO UnixWare 7

        // VMWare ESX
        'BD_MIDDLE_OS_VERSION_VM_KERNEL',        // VMWare ESX 4
        'BD_MIDDLE_OS_VERSION_VM_KERNEL5',       // VMWare ESX 5
        'BD_MIDDLE_OS_VERSION_VM_KERNEL6',       // VMWare ESX 6
        'BD_MIDDLE_OS_VERSION_VM_KERNEL65',      // VMWare ESX 6.5
        'BD_MIDDLE_OS_VERSION_VM_KERNEL7',       // VMWare ESX 7
        'BD_MIDDLE_OS_VERSION_VMWARE_PHOTON_64', // VMWare Photon 64 bit
        'BD_MIDDLE_OS_VERSION_VM_KERNEL8',       // VMWare ESX 8

        //////////////// other chinese operating system /////////
        // neokylin serials
        'BD_MIDDLE_OS_VERSION_NEOKYLIN7_64',     // NeoKylin Linux 7 64bit
        'BD_MIDDLE_OS_VERSION_NEOKYLIN65_64',    // NeoKylin Linux 6.5 64bit
        'BD_MIDDLE_OS_VERSION_NEOKYLIN6_64',     // NeoKylin Linux 6 64bit
        'BD_MIDDLE_OS_VERSION_NEOKYLIN6_32',     // NeoKylin Linux 6 32bit
        'BD_MIDDLE_OS_VERSION_NEOKYLIN5_64',     // NeoKylin Linux 5 64bit
        'BD_MIDDLE_OS_VERSION_NEOKYLIN5_32',     // NeoKylin Linux 5 32bit
        'BD_MIDDLE_OS_VERSION_NEOKYLIN3_64',     // NeoKylin Linux 3 64
        'BD_MIDDLE_OS_VERSION_NEOKYLIN_OS_6_64', // NeoKylin Linux 6 os
        'BD_MIDDLE_OS_VERSION_NEOKYLIN_DESKTOP_RELEASE_6_0_64', // NeoKylin Linux Desktop release 6.0 64bit
        'BD_MIDDLE_OS_VERSION_NEOKYLIN_TRUSTED_OS_V6_64', // NeoKylin Linux Trusted OS V6 64bit
        'BD_MIDDLE_OS_VERSION_NEOKYLIN_ADVANCED_SERVER_OPERATING_SYSTEM_V7_UPDATE_2_64', // Neokylin Linux Advanced Server Operating System V7 Update 2 64bit
        'BD_MIDDLE_OS_VERSION_NEOKYLIN_ADVANCED_SERVER_OPERATING_SYSTEM_V7_UPDATE_4_64', // Neokylin Linux Advanced Server Operating System V7 Update 4 64bit
        'BD_MIDDLE_OS_VERSION_NEOKYLIN_ADVANCED_SERVER_OPERATING_SYSTEM_V7_UPDATE_6_64', // Neokylin Linux Advanced Server Operating System V7 Update 6 64bit
        'BD_MIDDLE_OS_VERSION_NEOKYLIN_ADVANCED_SERVER_RELEASE_6_0_64', // Neokylin Linux Advanced Server release 6.0 64bit
        'BD_MIDDLE_OS_VERSION_NEOKYLIN_ADVANCED_SERVER_RELEASE_6_5_64', // Neokylin Linux Advanced Server release 6.5 64bit
        'BD_MIDDLE_OS_VERSION_NEOKYLIN_ADVANCED_SERVER_RELEASE_6_7_64', // Neokylin Linux Advanced Server release 6.7 64bit

        // neoshine serials
        'BD_MIDDLE_OS_VERSION_NEOSHINE_SERV_5_64', // neoshine server 5 64bit
        'BD_MIDDLE_OS_VERSION_NEOSHINE_SERV_4_64', // neoshine server 4 64bit
        'BD_MIDDLE_OS_VERSION_NEOSHINE_DESK_4_32', // neoshine desktop 4 64bit
        'BD_MIDDLE_OS_VERSION_NEOSHINE_3_64',      // neoshine server 3 64bit

        // linx serials
        'BD_MIDDLE_OS_VERSION_LINX8_64',  // linx 8.0 64bit
        'BD_MIDDLE_OS_VERSION_LINX6_64',  // linx 6.0 64bit
        'BD_MIDDLE_OS_VERSION_LINX42_64', // linx 4.2 64bit
        'BD_MIDDLE_OS_VERSION_LINX',      // linx

        // huawei serials
        'BD_MIDDLE_OS_VERSION_HUAWEI20_SP2_64', // huawei 20 sp2 64bit
        'BD_MIDDLE_OS_VERSION_HUAWEI20_SP1_64', // huawei 20 sp1 64bit

        //Greenbone
        'BD_MIDDLE_OS_VERSION_GREENBONE_OS_64', //Greenbone OS 

        //HopeStageEnterpriselinux
        'BD_MIDDLE_OS_VERSION_HOPE_STAGE_ENTERPRISE_LINUX_V1_0_64', //HopeStage Enterprise linux V1.0

        //HuNanKylin
        'BD_MIDDLE_OS_VERSION_HU_NAN_KYLIN_3_2_64', //HuNan Kylin 3.2
        'BD_MIDDLE_OS_VERSION_HU_NAN_KYLIN_3_3_64', //HuNan Kylin 3.3 

        //HuaweiCloudEulerOS
        'BD_MIDDLE_OS_VERSION_HUAWEI_CLOUD_EULER_OS_1_0_64', //Huawei Cloud EulerOS 1.0 

        // zhongxin serials
        'BD_MIDDLE_OS_VERSION_ZHONGXIN_V4_64', // zhongxin V4 64
        'BD_MIDDLE_OS_VERSION_ZHONGXIN_V3_64', // zhongxin V3 64

        // yimin serials
        'BD_MIDDLE_OS_VERSION_YIMIN_V7_64', // yimin V7 64
        'BD_MIDDLE_OS_VERSION_YIMIN_V4_64', // yimin V4 64
        'BD_MIDDLE_OS_VERSION_YIMIN_OS_64', // yimin os 64
        'BD_MIDDLE_OS_VERSION_YIMIN_OS_32', // yimin os 32

        // deepin serials
        'BD_MIDDLE_OS_VERSION_DEEPIN_15_2_64', // Deepin Server 15.2 64
        'BD_MIDDLE_OS_VERSION_DEEPIN_15_1_64', // Deepin Server 15.1 64
        'BD_MIDDLE_OS_VERSION_DEEPIN_15_64',   // Deepin Server 15 64
        'BD_MIDDLE_OS_VERSION_DEEPIN_15_32',   // Deepin Server 15 64

        // iSoft serials
        'BD_MIDDLE_OS_VERSION_ISOFT_4', // iSoft Server 4.0

        //YITU 64bit
        'BD_MIDDLE_OS_VERSION_YITU_64', //YITU 64bit

        //BC linux
        'BD_MIDDLE_OS_VERSION_BC_LINUX_21_10_64',        //BC_linux 21.10 64bit
        'BD_MIDDLE_OS_VERSION_BC_LINUX_7_6_64',			//BC_linux 7.6 64bit
        'BD_MIDDLE_OS_VERSION_BC_LINUX_7_8_64',			//BC_linux 7.8 64bit
        'BD_MIDDLE_OS_VERSION_BC_LINUX_8_X_64',			//BC_linux 8.x 64bit
        'BD_MIDDLE_OS_VERSION_BC_LINUX_OE22_10_64',      //BC_linux oe22.10

        //Astra
        'BD_MIDDLE_OS_VERSION_ASTRA_LINUX_1_4_64',		// Astra Linux 1.4 64bit

        //Wind River Linux
        'BD_MIDDLE_OS_VERSION_WIND_RIVER_LINUX_6_0_64', // Wind River Linux 6.0 64bit

        // new start serials
        'BD_MIDDLE_OS_VERSION_NEW_START_5_04', // new start linux 5 04
        'BD_MIDDLE_OS_VERSION_NEW_START_CGS_LINUX_V4_64',	//new start CGS Linux V4
        'BD_MIDDLE_OS_VERSION_NEW_START_CGS_LINUX_V5_64', //new start CGS Linux V5 64bit

        // Hygon linux
        'BD_MIDDLE_OS_VERSION_HYGON_LINUX',					// Hygon linux

        // EulerOS
        'BD_MIDDLE_OS_VERSION_EULEROS_2_1_64',			// EulerOS 2.1 64
        'BD_MIDDLE_OS_VERSION_EULEROS_2_2_64',			// EulerOS 2.2 64
        'BD_MIDDLE_OS_VERSION_EULEROS_2_3_64',			// EulerOS 2.3 64
        'BD_MIDDLE_OS_VERSION_EULEROS_2_3_DOCKER_64',		// EulerOS 2.3 Docker 64
        'BD_MIDDLE_OS_VERSION_EULEROS_2_3_UVP_KVM_2_3_RC3_B020D_FSO_64', // EulerOS 2.3(UVP-KVM-2.3.RC3.B020d_FSO) 64bit
        'BD_MIDDLE_OS_VERSION_EULEROS_2_3_UVP_KVM_2_5_RC3_B020_VRM_64', // EulerOS 2.3(UVP-KVM-2.5.RC3.B020_VRM) 64bit
        'BD_MIDDLE_OS_VERSION_EULEROS_2_3_UVP_KVM_2_5_RC5_B030_FSO_64', // EulerOS 2.3(UVP-KVM-2.5.RC5.B030_FSO) 64bit
        'BD_MIDDLE_OS_VERSION_EULEROS_2_5_64',			// EulerOS 2.5 Docker 64
        'BD_MIDDLE_OS_VERSION_EULEROS_2_9_64', // EulerOS 2.9 64bit
        'BD_MIDDLE_OS_VERSION_EULEROS_2_10_64', // EulerOS 2.10 64bit
        'BD_MIDDLE_OS_VERSION_EULEROS_2_11_64', // EulerOS 2.11 64bit
        'BD_MIDDLE_OS_VERSION_EULEROS_2_12_64', // EulerOS 2.12 64bit

        // Kylin serials
        'BD_MIDDLE_OS_VERSION_KYLIN',         // Kylin
        'BD_MIDDLE_OS_VERSION_KYLIN_64',      // Kylin 64 bit
        'BD_MIDDLE_OS_VERSION_KYLIN4',        // Kylin 4
        'BD_MIDDLE_OS_VERSION_KYLIN4_64',     // Kylin 4 64 bit
        'BD_MIDDLE_OS_VERSION_KYLIN7',        // Kylin 7
        'BD_MIDDLE_OS_VERSION_KYLIN7_64',     // Kylin 7 64 bit
        'BD_MIDDLE_OS_VERSION_KYLIN10',       // Kylin 10
        'BD_MIDDLE_OS_VERSION_KYLIN10_64',    // Kylin 10 64 bit
        'BD_MIDDLE_OS_VERSION_KYLIN4_0_1E_64', //Kylin 4.0.1E 64bit
        'BD_MIDDLE_OS_VERSION_KYLIN4_0_2_64', // kylin 4.0.2 64bit
        'BD_MIDDLE_OS_VERSION_KYLIN_DESKTOP_V10_64', // Kylin Desktop V10 64bit
        'BD_MIDDLE_OS_VERSION_KYLIN_DESKTOP_V10_GFB_64', // Kylin Desktop V10 GFB 64bit
        'BD_MIDDLE_OS_VERSION_KYLIN_DESKTOP_V10_SP1_64', // Kylin Desktop V10 SP1 64bit
        'BD_MIDDLE_OS_VERSION_KYLIN_OS_3_2_64', // Kylin OS 3.2 64bit
        'BD_MIDDLE_OS_VERSION_KYLIN_OS_4_0_2_SP2_64', // Kylin OS 4.0.2 SP2 64bit
        'BD_MIDDLE_OS_VERSION_KYLIN_OS_4_2_64', // Kylin OS 4.2 64bit
        'BD_MIDDLE_OS_VERSION_KYLIN_SERVER_V10_64', // Kylin Server V10 64bit
        'BD_MIDDLE_OS_VERSION_KYLIN_SERVER_V10_SP1_64', // Kylin Server V10 SP1 64bit
        'BD_MIDDLE_OS_VERSION_KYLIN_SERVER_V10_SP2_64', // Kylin Server V10 SP2 64bit
        'BD_MIDDLE_OS_VERSION_KYLIN_SERVER_V10_SP3_64', // Kylin Server V10 SP3 64bit
        'BD_MIDDLE_OS_VERSION_KYLIN_SEC_OS_3_4_64', // KylinSEC OS 3.4 64bit
        'BD_MIDDLE_OS_VERSION_KYLIN_SEC_SERVER_OS_3_4_X_64', // KylinSEC server OS 3.4-x 64bit
        'BD_MIDDLE_OS_VERSION_KYLIN_SEC_OS_SERVER_LINUX_3_5_X_64', // KylinSec OS server Linux 3.5.X 64bit

        // Linux Mint
        'BD_MIDDLE_OS_VERSION_LINUX_MINT_18_1_SERENA_KDE_32', // Linux Mint 18.1 Serena KDE 32bit
        'BD_MIDDLE_OS_VERSION_LINUX_MINT_18_1_SERENA_KDE_64', // Linux Mint 18.1 Serena KDE 64bit
        'BD_MIDDLE_OS_VERSION_LINUX_MINT_18_1_SERENA_MATE_32', // Linux Mint 18.1 Serena MATE 32bit
        'BD_MIDDLE_OS_VERSION_LINUX_MINT_18_1_SERENA_MATE_64', // Linux Mint 18.1 Serena MATE 64bit
        'BD_MIDDLE_OS_VERSION_LINUX_MINT_18_1_SERENA_XFCE_32', // Linux Mint 18.1 Serena Xfce 32bit
        'BD_MIDDLE_OS_VERSION_LINUX_MINT_18_1_SERENA_XFCE_64', // Linux Mint 18.1 Serena Xfce 64bit
        'BD_MIDDLE_OS_VERSION_LINUX_MINT_19_TARA_CINNAMO_64', // Linux Mint 19 Tara Cinnamo 64bit
        'BD_MIDDLE_OS_VERSION_LINUX_MINT_19_TARA_MATE_64', // Linux Mint 19 Tara MATE 64bit
        'BD_MIDDLE_OS_VERSION_LINUX_MINT_19_TARA_XFCE_64', // Linux Mint 19 Tara Xfce 64bit
        'BD_MIDDLE_OS_VERSION_LINX_OS_6_0_100_64', // LinxOS 6.0.100 64bit
        'BD_MIDDLE_OS_VERSION_LINX_OS_SERVER_6_0_FOR_EULER_64', // LinxOS server 6.0 for Euler 64bit

        //NFS
        'BD_MIDDLE_OS_VERSION_NFS_4_0_64', //NFS 4.0 

        // Open Euler serials
        'BD_MIDDLE_OS_VERSION_OPEN_EULER',         // Open Euler
        'BD_MIDDLE_OS_VERSION_OPEN_EULER_64',      // Open Euler 64 bit
        'BD_MIDDLE_OS_VERSION_OPEN_EULER20_03',    // Open Euler 20.03
        'BD_MIDDLE_OS_VERSION_OPENEULER_20_03',    // openEuler 20.03
        'BD_MIDDLE_OS_VERSION_OPEN_EULER20_03_64', // Open Euler 20.03 64 bit
        'BD_MIDDLE_OS_VERSION_OPEN_EULER22',       // Open Euler 22
        'BD_MIDDLE_OS_VERSION_OPEN_EULER22_64',    // Open Euler 22 64 bit
        'BD_MIDDLE_OS_VERSION_OPENEULER_22_03',    // Open Euler 22.03
        'BD_MIDDLE_OS_VERSION_OPENEULER_20_03_64', // Open Euler 20.03 64bit
        'BD_MIDDLE_OS_VERSION_OPENEULER_20_03_LTS_SP1_64', // Open Euler 20.03 LTS SP1 64bit
        'BD_MIDDLE_OS_VERSION_OPENEULER_20_03_LTS_SP2_64', // Open Euler 20.03 LTS SP2 64bit
        'BD_MIDDLE_OS_VERSION_OPENEULER_20_03_LTS_SPX_64', // Open Euler 20.03 LTS SPX 64bit
        'BD_MIDDLE_OS_VERSION_OPENEULER_21_03_64', // Open Euler 21.03 64bit
        'BD_MIDDLE_OS_VERSION_OPENEULER_22_03_LTS_64', // Open Euler 22.03 LTS 64bit
        'BD_MIDDLE_OS_VERSION_OPENEULER_22_03_LTS_SPX_64', // Open Euler 22.03 LTS SPX 64bit

        // UOS serials
        'BD_MIDDLE_OS_VERSION_UOS', // UOS
        'BD_MIDDLE_OS_VERSION_UOS_V20_DESKTOP_64', // UOS V20 desktop 64bit
        'BD_MIDDLE_OS_VERSION_UOS_V20_SERVER_1050U1A_64', // UOS V20 server 1050u1a 64bit
        'BD_MIDDLE_OS_VERSION_UOS_V20_SERVER_1060A_64', // UOS V20 server 1060a 64bit
        'BD_MIDDLE_OS_VERSION_UOS_V20_SERVER_1060E_64', // UOS V20 server 1060e 64bit
        'BD_MIDDLE_OS_VERSION_UOS_V20_SERVER_INDUSTRY_EDITION_64', // UOS V20 server Industry Edition 64bit
        'BD_MIDDLE_OS_VERSION_UOS_V20_SERVER_1050E_64', // UOS V20 server(1050e) 64bit
        'BD_MIDDLE_OS_VERSION_UOS_V20_DESKTOP_1050_UPDATE3_64', // UOS v20 desktop(1050 update3) 64bit


        // Anolis serials
        'BD_MIDDLE_OS_VERSION_ANOLIS_OS',     // Anolis OS
        'BD_MIDDLE_OS_VERSION_ANOLIS_OS_64',  // Anolis OS 64 bit
        'BD_MIDDLE_OS_VERSION_ANOLIS_OS7',    // Anolis OS 7
        'BD_MIDDLE_OS_VERSION_ANOLIS_OS7_64', // Anolis OS 7 64 bit
        'BD_MIDDLE_OS_VERSION_ANOLIS_OS7_9_64', // Anolis OS 7.9 64 bit
        'BD_MIDDLE_OS_VERSION_ANOLIS_OS8',    // Anolis OS 8
        'BD_MIDDLE_OS_VERSION_ANOLIS_OS8_64', // Anolis OS 8 64 bit
        'BD_MIDDLE_OS_VERSION_ANOLIS_OS8_X_64', // Anolis OS 8.X 64 bit

        // OpenCloudOS serials
        'BD_MIDDLE_OS_VERSION_OPEN_CLOUD_OS',     // OpenCloudOS
        'BD_MIDDLE_OS_VERSION_OPEN_CLOUD_OS_64',  // OpenCloudOS 64 bit
        'BD_MIDDLE_OS_VERSION_OPEN_CLOUD_OS8',    // OpenCloudOS 8
        'BD_MIDDLE_OS_VERSION_OPEN_CLOUD_OS8_64', // OpenCloudOS 8 64 bit

        // Amazon linux
        'BD_MIDDLE_OS_VERSION_AMAZON2_LINUX_64', // Amazon linux3 64
        'BD_MIDDLE_OS_VERSION_AMAZON3_LINUX_64', // Amazon linux2 64

        // Alma Linux
        'BD_MIDDLE_OS_VERSION_ALMA_LINUX_64',		// Alma Linux 64
        'BD_MIDDLE_OS_VERSION_ALMA_LINUX_8_3_64',                       // Almalinux 8.3 64bit
        'BD_MIDDLE_OS_VERSION_ALMA_LINUX_8_4_64',                       // Almalinux 8.4 64bit
        'BD_MIDDLE_OS_VERSION_ALMA_LINUX_8_6_64',                       // Almalinux 8.6 64bit
        'BD_MIDDLE_OS_VERSION_ALMA_LINUX_8_X_64',                       // Almalinux 8.X 64bit
        'BD_MIDDLE_OS_VERSION_ALMA_LINUX_9_X_64',                       // Almalinux 9.X 64bit

        // Other
        500000 => 'BD_MIDDLE_OS_VERSION_OTHER_32', //Other (32 bit) 32bit
        'BD_MIDDLE_OS_VERSION_OTHER_64',			//Other (64 bit) 64bit
        'BD_MIDDLE_OS_VERSION_WINDOWS_OTHER_32',	//Other Windows(32 bit) 32bit
        'BD_MIDDLE_OS_VERSION_WINDOWS_OTHER_64',	//Other Windows(64 bit) 64bit
        'BD_MIDDLE_OS_VERSION_LINUX_OTHER_32',	//Other Linux(32 bit) 32bit
        'BD_MIDDLE_OS_VERSION_LINUX_OTHER_64',	//Other Linux(64 bit) 64bit
    ],
    'BdMiddleOsVersionDes' => [
        'Unknow',

        // Mac OS serials
        'Mac OS General',
        'Mac OS 10.5',
        'Mac OS 10.5 64 bit',
        'Mac OS 10.6',
        'Mac OS 10.6 64 bit',
        'Mac OS 10.7',
        'Mac OS 10.7 64 bit',
        'Mac OS 10.8 64 bit',
        'Mac OS 10.9 64 bit',
        'Mac OS 10.10 64 bit',
        'Mac OS 10.11 64 bit',
        'Mac OS 10.12 64 bit',
        'Mac OS 10.12 64 bit',
        'Mac OS 10.12 64 bit',
        'Mac OS 10.12 64 bit',
        'Mac OS 10.12 64 bit',
        'Mac OS 10.12 64 bit',
        'Mac OS 10.12 64 bit',
        'Mac OS 10.12 64 bit',

        2000 => 'Windows General',
        'Windows Win',
        'MS-DOS',
        'Windows 3.1',
        'Windows 95',
        'Windows 98',
        'Windows ME',
        'Windows NT 4.0',
        'windows server 2000',
        'windows 2000 pro',
        'windows 2000 advanced server',
        'Windows Business Server 2003',
        'Windows DataCenter Server 2003',
        'Windows DataCenter Server 2003 64 bit',
        'Windows Server 2003 DataCenter SP1 32bit',
        'Windows Server 2003 DataCenter SP2 32bit',
        'Windows Stander Server 2003',
        'Windows Stander Server 2003 64 bit',
        'Windows Server 2003 Standard SP1 32bit',
        'Windows Server 2003 Standard SP2 32bit',
        'Windows Server 2003 Standard SP1 64bit',
        'Windows Enterprise Server 2003',
        'Windows Enterprise Server 2003 64 bit',
        'Windows Server 2003 Enterprise SP1 32bit',
        'Windows Server 2003 Enterprise SP2 32bit',
        'Windows WEB Server 2003',
        'windows server 32bit',
        'windows 2008 server (Win8Serv in vmware)',
        'windows 2008 server R2',
        'Windows Server 2008 Datacenter R2 SP1 64bit',
        'Windows Server 2008 Datacenter SP1 32bit',
        'Windows Server 2008 Datacenter SP1 64bit',
        'Windows Server 2008 Datacenter SP2 32bit',
        'Windows Server 2008 Datacenter SP2 64bit',
        'Windows Server 2008 Enterprise R2 SP1 64bit',
        'Windows Server 2008 Enterprise SP1 32bit',
        'Windows Server 2008 Enterprise SP1 64bit',
        'Windows Server 2008 R2 Datacenter 64bit',
        'Windows Server 2008 R2 Datacenter SP1 64bit',
        'Windows Server 2008 R2 Enterprise 64bit',
        'Windows Server 2008 R2 Enterprise SP1 64bit',
        'Windows Server 2008 R2 Standard 64bit',
        'Windows Server 2008 R2 Standard SP1 64bit',
        'Windows Server 2008 Standard R2 SP1 64bit',
        'Windows Server 2008 Standard SP1 32bit',
        'Windows Server 2008 Standard SP1 64bit',
        'Windows Server 2008 Standard SP2 32bit',
        'Windows Server 2008 Standard SP2 64bit',
        'Windows Server 2008 Web R2 64bit',
        'windows 2012 server (Win9Serv)',
        'windows 2012 server R2',
        'Windows Server 2012 Datacenter 64bit',
        'Windows Server 2012 Essentials R2 64bit',
        'Windows Server 2012 R2 Datacenter 64bit',
        'Windows Server 2012 R2 Essentials 64bit',
        'Windows Server 2012 R2 Standard 64bit',
        'Windows Server 2012 Standard 64bit',
        'windows 2016 server',
        'windows 2016 datacenter version',
        'windows 2016 essential version',
        'Windows Server 2016 Standard 64bit',
        'HyperV',
        'windows server 2019',
        'windows server 2019 standard',
        'windows server 2019 essentials',
        'Windows Server 2019 Datacenter 64bit',
        'windows server 2022',
        'Windows Server 2022 64bit',
        'windows server 2025',
        'windows xp home edition',
        'windows xp pro',
        'windows xp pro x64',
        'windows vista',
        'windows vista 64 bit',
        'windows 7',
        'windows 7 64 bit',
        'Windows 7 Professional 32bit',
        'Windows 7 Professional 64bit',
        'Windows 7 SP1 64bit',
        'Windows 7 SP1 32bit',
        'Windows 7 SP1 Professional 32bit',
        'Windows 7 SP1 Professional 64bit',
        'Windows 7 SP1 Ultimate 64bit',
        'Windows 7 SP1 Ultimate 32bit',
        'Windows 7 Ultimate 64bit',
        'Windows 7 Ultimate 32bit',
        'windows 8',
        'windows 8 64 bit',
        'windows 10',
        'windows 10 64 bit',
        'Windows 10 CMGE V2020 L 64bit',
        'Windows 10 Enterprise 32bit',
        'Windows 10 Enterprise 64bit',
        'Windows 10 Enterprise 2016 LTSB 32bit',
        'Windows 10 Enterprise 2016 LTSB 64bit',
        'Windows 10 Enterprise 2019 LTSC 64bit',
        'Windows 10 Professional 32bit',
        'Windows 10 Professional 64bit',
        'Windows 10 Professional for Education 32bit',
        'Windows 10 Professional for Education 64bit',
        'windows 11 64 bit',
        'windows 12 64 bit',
        'windows xp',
        'windows NT 4.0',

        30000 => 'Linux General',
        // asianux (red flag linux)
        'asianux 2 32 bit',
        'asianux 2 64 bit',
        'asianux 3 32 bit(red flag linux)',
        'asianux 3 64 bit',
        'asianux 4 32 bit(red flag linux)',
        'asianux 4 64 bit',
        'asianux 5 32 bit(red flag linux)',
        'asianux 5 64 bit',
        'asianux 7 64 bit',
        'asianux 7.6 64 bit',
        'asianux 8 64 bit',
        'asianux 9 64 bit',
        'Asianux Server 4 SP2 64bit',
        'Asianux Server 4 SP4 64bit',
        'Asianux Server 4.5 64bit',
        'Asianux Server 7.3 64bit',

        // centos serials
        'centos 4/5 32 bit',
        'centos 4/5 64 bit',

        // centos sub version
        'CentOS 4',
        'CentOS 4 64 bit',
        'CentOS 5',
        'CentOS 5 64 bit',
        'CentOS 5.1',
        'CentOS 5.1 64 bit',
        'CentOS 5.2',
        'CentOS 5.2 64 bit',
        'CentOS 5.3',
        'CentOS 5.3 64 bit',
        'CentOS 5.4',
        'CentOS 5.4 64 bit',
        'CentOS 5.5',
        'CentOS 5.5 64 bit',
        'CentOS 5.6',
        'CentOS 5.6 64 bit',
        'CentOS 5.7',
        'CentOS 5.7 64 bit',
        'CentOS 5.8',
        'CentOS 5.8 64 bit',
        'CentOS 5.9',
        'CentOS 5.9 64 bit',
        'CentOS 5.10',
        'CentOS 5.10 64 bit',
        'CentOS 5.11',
        'CentOS 5.11 64 bit',
        'centos 6 32 bit',
        'centos 6 64 bit',
        'CentOS 6.0 32bit',
        'CentOS 6.0 64bit',
        'CentOS 6.1 32bit',
        'CentOS 6.1 64bit', // 30050
        'CentOS 6.10 32bit',
        'CentOS 6.10 64bit',
        'CentOS 6.2 32bit',
        'CentOS 6.2 64bit',
        'CentOS 6.3 32bit',
        'CentOS 6.3 64bit',
        'CentOS 6.4 32bit',
        'CentOS 6.4 64bit',
        'CentOS 6.5 32bit',
        'CentOS 6.5 64bit',
        'CentOS 6.6 32bit',
        'CentOS 6.6 64bit',
        'CentOS 6.7 32bit',
        'CentOS 6.7 64bit',
        'CentOS 6.8 32bit',
        'CentOS 6.8 64bit',
        'CentOS 6.9 32bit',
        'CentOS 6.9 64bit',
        'centos 7 32 bit',
        'centos 7 64 bit',
        'CentOS 7.0 64bit',
        'CentOS 7.1 32bit',
        'CentOS 7.1 64bit',
        'CentOS 7.2 32bit',
        'CentOS 7.2 64bit',
        'CentOS 7.3 64bit',
        'CentOS 7.4 64bit',
        'CentOS 7.5 64bit',
        'CentOS 7.6 64bit',
        'CentOS 7.7 64bit',
        'CentOS 7.8 64bit',
        'CentOS 7.9 64bit',
        'centos 8 32 bit',
        'centos 8 64 bit',
        'CentOS 8.0 64bit',
        'CentOS 8.1 64bit',
        'CentOS 8.2 64bit',
        'CentOS 8.3 64bit',
        'CentOS 8.4 64bit',
        'CentOS 8.5 64bit',
        'centos 9 32 bit',
        'centos 9 64 bit',

        // coreos serials
        'coreos 64 bit',
        'CoreOS 1122.2.0 64bit',
        'CoreOS 1298.5.0 64bit',
        'CoreOS 1465.8.0 64bit',
        'CoreOS 1520.8.0 64bit',
        'CoreOS 1632.0.0 64bit',
        'CoreOS 1745.2.0 64bit',
        'CoreOS 1800.1.0 64bit', // 30100

        // CtyunOS
        'CtyunOS 2.x 64bit',

        // Debian OS serials
        'Debian bit',
        'Debian 64 bit',
        'Debian 4',
        'Debian 4 64 bit',
        'Debian 5',
        'Debian 5 64 bit',
        'Debian 6',
        'Debian 6 64 bit',
        'Debian 7',
        'Debian 7 64 bit',
        'Debian GNU/Linux 7.0.0 32bit',
        'Debian GNU/Linux 7.0.0 64bit',
        'Debian GNU/Linux 7.1.0 32bit',
        'Debian GNU/Linux 7.1.0 64bit',
        'Debian GNU/Linux 7.10.0 32bit',
        'Debian GNU/Linux 7.10.0 64bit',
        'Debian GNU/Linux 7.11.0 64bit',
        'Debian GNU/Linux 7.2.0 32bit',
        'Debian GNU/Linux 7.2.0 64bit',
        'Debian GNU/Linux 7.3.0 32bit',
        'Debian GNU/Linux 7.3.0 64bit',
        'Debian GNU/Linux 7.4.0 32bit',
        'Debian GNU/Linux 7.4.0 64bit',
        'Debian GNU/Linux 7.5.0 32bit',
        'Debian GNU/Linux 7.5.0 64bit',
        'Debian GNU/Linux 7.6.0 32bit',
        'Debian GNU/Linux 7.6.0 64bit',
        'Debian GNU/Linux 7.7.0 32bit',
        'Debian GNU/Linux 7.7.0 64bit',
        'Debian GNU/Linux 7.8.0 32bit',
        'Debian GNU/Linux 7.8.0 64bit',
        'Debian 8',
        'Debian 8 64 bit',
        'Debian GNU/Linux 8.0.0 32bit',
        'Debian GNU/Linux 8.0.0 64bit',
        'Debian GNU/Linux 8.0.0(4.9.18-1) 64bit',
        'Debian GNU/Linux 8.10.0 64bit',
        'Debian GNU/Linux 8.11.0 64bit',
        'Debian GNU/Linux 8.2.0 32bit',
        'Debian GNU/Linux 8.2.0 64bit',
        'Debian GNU/Linux 8.4.0 32bit',
        'Debian GNU/Linux 8.4.0 64bit',
        'Debian GNU/Linux 8.5.0 32bit',
        'Debian GNU/Linux 8.5.0 64bit',
        'Debian GNU/Linux 8.6.0 32bit',
        'Debian GNU/Linux 8.6.0 64bit',
        'Debian GNU/Linux 8.7.0 64bit',

        'Debian GNU/Linux 8.8.0 64bit',
        'Debian GNU/Linux 8.9.0 64bit',
        'Debian GNU/Linux 9',
        'Debian GNU/Linux 9 64 bit',
        'Debian GNU/Linux 9.0.0 64bit',
        'Debian GNU/Linux 9.11.0 64bit',
        'Debian GNU/Linux 9.12.0 64bit',
        'Debian GNU/Linux 9.13.0 64bit',
        'Debian GNU/Linux 9.3.0 64bit',
        'Debian GNU/Linux 9.4.0 64bit',
        'Debian GNU/Linux 9.5.0 64bit',
        'Debian GNU/Linux 9.6.0 64bit',
        'Debian GNU/Linux 9.7.0 64bit',
        'Debian GNU/Linux 9.8.0 64bit',
        'Debian GNU/Linux 9.9.0 64bit',
        'Debian GNU/Linux 10',
        'Debian GNU/Linux 10 64 bit',
        'Debian GNU/Linux 10.0.0 64bit',
        'Debian GNU/Linux 10.1.0 64bit',
        'Debian GNU/Linux 10.2.0 64bit',
        'Debian GNU/Linux 10.3.0 64bit',
        'Debian GNU/Linux 10.4.0 64bit',
        'Debian GNU/Linux 10.5.0 64bit',
        'Debian GNU/Linux 10.7.0 64bit',
        'Debian GNU/Linux 11',
        'Debian GNU/Linux 11 64 bit',
        'Debian GNU/Linux 11.3 64bit',
        'Debian GNU/Linux 11.5 64bit',
        'Debian GNU/Linux 12.x 64bit',
        'Debian GNU/Linux 12',
        'Debian GNU/Linux 12 64 bit',
        'Deepin GNU/Linux (Server 15) 64bit',

        // eComStation (eCS)
        'eComStation 1',
        'eComStation 1',

        //Fusion
        'FusionOS 22.0.4 64bit',

        // fedora serials
        'Fedora Linux',
        'Fedora Linux 64 bit',
        'Fedora 25 64bit',
        'Fedora 24 64bit',
        'Fedora 23 64bit',
        'Fedora 22 64bit',
        'Fedora 21 64bit',
        'Fedora 20 64bit',
        'Fedora 19 64bit',
        'Fedora 18 64bit',
        'Fedora 17 64bit',
        'Fedora 16 64bit',
        'Fedora 15 64bit',
        'Fedora 14 64bit',
        'Fedora 13 64bit',
        'Fedora 12 32bit',
        'Fedora 12 64bit',
        'Fedora 11 32bit',
        'Fedora 11 64bit',
        'Fedora 10 32bit',
        'Fedora 10 64bit',
        'Fedora 9 32bit',
        'Fedora 9 64bit',
        'Fedora 8 32bit',
        'Fedora 8 64bit',
        'Fedora 7 32bit',
        'Fedora 7 64bit',
        'Fedora 6 32bit',
        'Fedora 6 64bit',
        'Fedora 26 (Server Edition) 64bit',
        'Fedora 26 (Workstation Edition) 64bit',
        'Fedora 27 (Server Edition) 64bit',
        'Fedora 28 (Server Edition) 64bit',
        'Fedora 28 (Workstation Edition) 64bit',
        'Fedora 29 (Server Edition) 64bit',
        'Fedora 30 (Server Edition) 64bit',
        'Fedora 31 (Server Edition) 64bit',
        'Fedora 32 (Server Edition) 64bit',
        'Fedora 33 (Server Edition) 64bit',
        'Fedora 34 (Server Edition) 64bit',
        'Fedora 35 (Server Edition) 64bit',
        'Fedora 36 (Server Edition) 64bit',
        'Fedora 37 (Server Edition) 64bit',
        'Fedora 38 (Server Edition) 64bit',
        'Fedora 39 (Server Edition) 64bit',
        'Fedora CoreOS 33 64bit',

        // FreeBSD serials
        'FreeBSD',
        'FreeBSD 64 bit',
        'FreeBSD11',
        'FreeBSD11 64 bit',
        'FreeBSD12',
        'FreeBSD12 64 bit',
        'FreeBSD13',
        'FreeBSD13 64 bit',
        'FreeBSD14',
        'FreeBSD14 64 bit',

        // generic linux
        'generic linux',

        // mandrake linux serials
        'mandrake linux',
        'mandrake linux 64 bit',
        'mandriva linux',

        // novall serials
        'Novell Netware 4',
        'Novell Netware 5',
        'Novell Netware 6',
        'Novell Linux Desktop 9',

        // Open Enterprise Server serials
        'Open Enterprise Server',

        // SCO OpenServer serials
        'SCO OpenServer 5',
        'SCO OpenServer 6',

        // OpenSUSE serials
        'OpenSUSE',
        'OpenSUSE 64 bit',
        'openSUSE Leap 15.0 64bit',
        'openSUSE Leap 15.1 64bit',
        'openSUSE Leap 42.1 64bit',
        'openSUSE Leap 42.2 64bit',
        'openSUSE Leap 42.3 64bit',

        // oracle linux serials
        'Oracle Linux 4/5',
        'Oracle Linux 4/5 64bit',
        'Oracle Linux 5.6 64bit',
        'Oracle Linux 5.7 64bit',
        'Oracle Linux 5.8 64bit',
        'Oracle Linux 5.9 64bit',
        'Oracle Linux 5.10 64bit',
        'Oracle Linux 5.11 32bit',
        'Oracle Linux 5.11 64bit',
        'Oracle Linux 6',
        'Oracle Linux 6 64bit',
        'Oracle Linux 6.3 32bit',
        'Oracle Linux 6.3 64bit',
        'Oracle Linux 6.4 64bit',
        'Oracle Linux 6.5 32bit',
        'Oracle Linux 6.5 64bit',
        'Oracle Linux 6.6 32bit',
        'Oracle Linux 6.6 64bit',
        'Oracle Linux 6.7 32bit',
        'Oracle Linux 6.7 64bit',
        'Oracle Linux 6.8 32bit',
        'Oracle Linux 6.8 64bit',
        'Oracle Linux 6.9 64bit',
        'Oracle Linux 6.10 64bit',
        'Oracle Linux 7',
        'Oracle Linux 7 64bit',
        'Oracle Linux 7.0 64bit',
        'Oracle Linux 7.1 64bit',
        'Oracle Linux 7.2 64bit',
        'Oracle Linux 7.3 64bit',
        'Oracle Linux 7.4 64bit',
        'Oracle Linux 7.5 64bit',
        'Oracle Linux 7.6 64bit',
        'Oracle Linux 8 64bit',
        'Oracle Linux 8.6 64bit',
        'Oracle Linux 8.X 64bit',
        'Oracle Linux 9 64bit',
        'Oracle Linux 9.X 64bit',

        // OS/2
        'OS/2',

        // other linux serials
        'Linux 2.2X Kernel',
        'Linux 64 bit',
        'Linux 2.4 Kernel',
        'Linux 2.4 Kernel 64 bit',
        'Linux 2.6 Kernel',
        'Linux 2.6 Kernel 64 bit',
        'Linux 3.0 Kernel',
        'Linux 3.0 Kernel 64 bit',
        'Linux 4.0 Kernel',
        'Linux 4.0 Kernel 64 bit',
        'Linux 5.0 Kernel',
        'Linux 5.0 Kernel 64 bit',
        'Linux 6.0 Kernel',
        'Linux 6.0 Kernel 64 bit',

        // Red Hat Enterprise Linux serials
        'Red Hat Linux 2.1',
        'Red Hat Enterprise Linux 2',
        'Red Hat Enterprise Linux 3',
        'Red Hat Enterprise Linux 3 64 bit',
        'Red Hat Enterprise Linux 4',
        'Red Hat Enterprise Linux 4 64 bit',
        'Red Hat Enterprise Linux 5',
        'Red Hat Enterprise Linux 5 64 bit',
        'Red Hat Enterprise Linux 5.1',
        'Red Hat Enterprise Linux 5.1 64 bit',
        'Red Hat Enterprise Linux 5.2',
        'Red Hat Enterprise Linux 5.2 64 bit',
        'Red Hat Enterprise Linux 5.3',
        'Red Hat Enterprise Linux 5.3 64 bit',
        'Red Hat Enterprise Linux 5.4',
        'Red Hat Enterprise Linux 5.4 64 bit',
        'Red Hat Enterprise Linux 5.5',
        'Red Hat Enterprise Linux 5.5 64 bit',
        'Red Hat Enterprise Linux 5.6',
        'Red Hat Enterprise Linux 5.6 64 bit',
        'Red Hat Enterprise Linux 5.7',
        'Red Hat Enterprise Linux 5.7 64 bit',
        'Red Hat Enterprise Linux 5.8',
        'Red Hat Enterprise Linux 5.8 64 bit',
        'Red Hat Enterprise Linux 5.9',
        'Red Hat Enterprise Linux 5.9 64 bit',
        'Red Hat Enterprise Linux 5.10',
        'Red Hat Enterprise Linux 5.10 64 bit',
        'Red Hat Enterprise Linux 5.11',
        'Red Hat Enterprise Linux 5.11 64 bit',
        'Red Hat Enterprise Linux 6',
        'Red Hat Enterprise Linux 6 64 bit',
        'Red Hat Enterprise Linux 6.0 32bit',
        'Red Hat Enterprise Linux 6.0 64bit',
        'Red Hat Enterprise Linux 6.1 32bit',
        'Red Hat Enterprise Linux 6.1 64bit',
        'Red Hat Enterprise Linux 6.2 32bit',
        'Red Hat Enterprise Linux 6.2 64bit',
        'Red Hat Enterprise Linux 6.3 32bit',
        'Red Hat Enterprise Linux 6.3 64bit',
        'Red Hat Enterprise Linux 6.4 32bit',
        'Red Hat Enterprise Linux 6.4 64bit',
        'Red Hat Enterprise Linux 6.5 32bit',
        'Red Hat Enterprise Linux 6.5 64bit',
        'Red Hat Enterprise Linux 6.6 32bit',
        'Red Hat Enterprise Linux 6.6 64bit',
        'Red Hat Enterprise Linux 6.7 32bit',
        'Red Hat Enterprise Linux 6.7 64bit',
        'Red Hat Enterprise Linux 6.8 32bit',
        'Red Hat Enterprise Linux 6.8 64bit',
        'Red Hat Enterprise Linux 6.9 64bit',
        'Red Hat Enterprise Linux 6.10 32bit',
        'Red Hat Enterprise Linux 6.10 64bit',
        'Red Hat Enterprise Linux 7',
        'Red Hat Enterprise Linux 7 64bit',
        'Red Hat Enterprise Linux 7.0 64bit',
        'Red Hat Enterprise Linux 7.1 64bit',
        'Red Hat Enterprise Linux 7.2 64bit',
        'Red Hat Enterprise Linux 7.3 64bit',
        'Red Hat Enterprise Linux 7.4 64bit',
        'Red Hat Enterprise Linux 7.5 64bit',
        'Red Hat Enterprise Linux 7.6 64bit',
        'Red Hat Enterprise Linux 7.7 64bit',
        'Red Hat Enterprise Linux 7.9 64bit',
        'Red Hat Enterprise Linux 8',
        'Red Hat Enterprise Linux 8 64bit',
        'Red Hat Enterprise Linux 8.0 64bit',
        'Red Hat Enterprise Linux 8.1 64bit',
        'Red Hat Enterprise Linux 8.2 64bit',
        'Red Hat Enterprise Linux 8.3 64bit',
        'Red Hat Enterprise Linux 8.4 64bit',
        'Red Hat Enterprise Linux 8.5 64bit',
        'Red Hat Enterprise Linux 8.6 64bit',
        'Red Hat Enterprise Linux 8.7 64bit',
        'Red Hat Enterprise Linux 8.X 64bit',
        'Red Hat Enterprise Linux 9',
        'Red Hat Enterprise Linux 9 64bit',
        'Red Hat Enterprise Linux 9.X 64bit',
        'Red Hat Enterprise Linux Atomic Host 7.5 64bit',

        // Rocky Linux serials
        'Rocky Linux',
        'Rocky Linux 64 bit',
        'Rocky Linux 8',
        'Rocky Linux 8 64 bit',
        'Rocky Linux 8.7 64 bit',
        'Rocky Linux 9',
        'Rocky Linux 9 64 bit',
        'Rocky Linux 9.X 64 bit',
        'Rocky Secure Server Version 6.0.80 64 bit',
        'Rocky Secure Server Version 6.0.80(4.9.0-0.bpo.1-linx-security-amd64)',
        'Rocky Version 6.0.42.41',

        // NeutonOS Linux serials
        'NeutonOS Linux',
        'NeutonOS Linux 64 bit',
        'NeutonOS Linux 8',
        'NeutonOS Linux 8 64 bit',

        // Sun Java Desktop system
        'Sun Java Desktop System',

        // Suse Linux Enterprise Server serials
        'Suse Linux Enterprise Server 9',
        'Suse Linux Enterprise Server 9 64 bit',
        'Suse Linux Enterprise Server 10',
        'Suse Linux Enterprise Server 10 64 bit',
        'Suse Linux Enterprise Server 11',
        'Suse Linux Enterprise Server 11 64 bit',
        'Suse Linux Enterprise Server 11 SP3 32bit',
        'Suse Linux Enterprise Server 11 SP3 64bit',
        'Suse Linux Enterprise Server 11 SP4 32bit',
        'Suse Linux Enterprise Server 11 SP4 64bit',
        'Suse Linux Enterprise Server 11 SP4 (slesb1hana) 64bit',
        'Suse Linux Enterprise Server 12',
        'Suse Linux Enterprise Server 12 64bit',
        'Suse Linux Enterprise Server 12 SP1 64bit',
        'Suse Linux Enterprise Server 12 SP2 64bit',
        'Suse Linux Enterprise Server 12 SP3 64bit',
        'Suse Linux Enterprise Server 12 SP4 64bit',
        'Suse Linux Enterprise Server 12 SP5 64bit',
        'Suse Linux Enterprise Server 15 64bit',
        'Suse Linux Enterprise Server 15 SP1 64bit',
        'Suse Linux Enterprise Server 15 SP2 64bit',
        'Suse Linux Enterprise Server 15 SP3 64bit',
        'Suse Linux Enterprise Server 15 SP4 64bit',
        'Suse Linux Enterprise Server 16 64bit',

        // Solaris serials
        'Solaris 6',
        'Solaris 7',
        'Solaris 8',
        'Solaris 9',
        'Solaris 10',
        'Solaris 10 64 bit',
        'Solaris 11 64 bit',

        // Suse serials
        'Suse Linux',
        'Suse Linux 64 bit',

        //ScientificLinux
        'Scientific Linux release 6.3 32 bit',
        'Scientific Linux release 6.5 32 bit',
        'Scientific Linux release 6.5 64 bit',

        //Slackware
        'Slackware 14.2 64 bit',

        //TencentOSServer
        'TencentOS Server 3.1 (Final) 64 bit',

        // Turbo Linux serials
        'Turbo Linux',
        'Turbo Linux 64 bit',
        'Turbo Linux 16 LTS 64 bit',
        'TurboLinux Enterprise Server 15 64 bit',
        'TurboLinux Enterprise Server 16 64 bit',

        // Ubuntu Linux serials
        'Ubuntu',
        'Ubuntu 64 bit',
        'Ubuntu 16',
        'Ubuntu 16 64 bit',
        'Ubuntu 18',
        'Ubuntu 18 64 bit',
        'Ubuntu 20',
        'Ubuntu 20 64 bit',
        'Ubuntu 22',
        'Ubuntu 22 64 bit',
        'Ubuntu Desktop 14.04 32bit',
        'Ubuntu Desktop 14.04 64bit',
        'Ubuntu Desktop 14.04.3 32bit',
        'Ubuntu Desktop 14.04.3 64bit',
        'Ubuntu Desktop 14.04.4 32bit',
        'Ubuntu Desktop 14.04.4 64bit',
        'Ubuntu Desktop 16.04 32bit',
        'Ubuntu Desktop 16.04 64bit',
        'Ubuntu Desktop 16.04.1 32bit',
        'Ubuntu Desktop 16.04.1 64bit',
        'Ubuntu Desktop 16.04.3 32bit',
        'Ubuntu Desktop 16.04.3 64bit',
        'Ubuntu Desktop 16.04.4 64bit',
        'Ubuntu Desktop 16.10 32bit',
        'Ubuntu Desktop 16.10 64bit',
        'Ubuntu Desktop 17.04 64bit',
        'Ubuntu Desktop 17.10 64bit',
        'Ubuntu Desktop 18.04 64bit',
        'Ubuntu Desktop 18.04.1 64bit',
        'Ubuntu Desktop 18.04.4 LTS 64bit',
        'Ubuntu Desktop 20.04.2 LTS 64bit',
        'Ubuntu Desktop 22.04.x 64bit',
        'Ubuntu Kylin 16.04.6 64bit',
        'Ubuntu Kylin 18.10 64bit',
        'Ubuntu Server 14.04 32bit',
        'Ubuntu Server 14.04 64bit',
        'Ubuntu Server 14.04.1 32bit',
        'Ubuntu Server 14.04.1 64bit',
        'Ubuntu Server 14.04.2 32bit',
        'Ubuntu Server 14.04.2 64bit',
        'Ubuntu Server 14.04.3 32bit',
        'Ubuntu Server 14.04.3 64bit',
        'Ubuntu Server 14.04.4 32bit',
        'Ubuntu Server 14.04.4 64bit',
        'Ubuntu Server 14.04.5 32bit',
        'Ubuntu Server 14.04.5 64bit',
        'Ubuntu Server 16.04 32bit',
        'Ubuntu Server 16.04 64bit',
        'Ubuntu Server 16.04.1 32bit',
        'Ubuntu Server 16.04.1 64bit',
        'Ubuntu Server 16.04.2 64bit',
        'Ubuntu Server 16.04.3 32bit',
        'Ubuntu Server 16.04.3 64bit',
        'Ubuntu Server 16.04.4 64bit',
        'Ubuntu Server 16.04.5 64bit',
        'Ubuntu Server 16.04.6 64bit',
        'Ubuntu Server 16.10 32bit',
        'Ubuntu Server 16.10 64bit',
        'Ubuntu Server 17.04 64bit',
        'Ubuntu Server 17.10 64bit',
        'Ubuntu Server 18.04 64bit',
        'Ubuntu Server 18.04.1 LTS 64bit',
        'Ubuntu Server 18.04.2 64bit',
        'Ubuntu Server 18.04.3 64bit',
        'Ubuntu Server 18.04.4 64bit',
        'Ubuntu Server 18.04.6 64bit',
        'Ubuntu Server 19.04 64 bit',
        'Ubuntu Server 19.10 64 bit',
        'Ubuntu Server 20.04 64 bit',
        'Ubuntu Server 22.04 64 bit',
        'Ubuntu Server 23.04 64 bit',

        // SCO UnixWare 7
        'SCO UnixWare 7',

        // VMWare ESX
        'VMWare ESX 4',
        'VMWare ESX 5',
        'VMWare ESX 6',
        'VMWare ESX 6.5',
        'VMWare ESX 7',
        'VMWare Photon 64 bit',
        'VMWare ESX 8',

        //////////////// other chinese operating system /////////
        // neokylin serials
        'neokylin 7 64bit',
        'neokylin 6.5 64bit',
        'neokylin 6 64bit',
        'neokylin 6 32bit',
        'neokylin 5 64bit',
        'neokylin 5 32bit',
        'neokylin 3 64',
        'neokylin 6 os',
        'NeoKylin Linux Desktop release 6.0 64bit',
        'NeoKylin Linux Trusted OS V6 64bit',
        'Neokylin Linux Advanced Server Operating System V7 Update 2 64bit',
        'Neokylin Linux Advanced Server Operating System V7 Update 4 64bit',
        'Neokylin Linux Advanced Server Operating System V7 Update 6 64bit',
        'Neokylin Linux Advanced Server release 6.0 64bit',
        'Neokylin Linux Advanced Server release 6.5 64bit',
        'Neokylin Linux Advanced Server release 6.7 64bit',

        // neoshine serials
        'neoshine server 5 64bit',
        'neoshine server 4 64bit',
        'neoshine desktop 4 64bit',
        'neoshine server 3 64bit',

        // linx serials
        'linx 8.0 64bit',
        'linx 6.0 64bit',
        'linx 4.2 64bit',
        'linx',

        // huawei serials
        'huawei 20 sp2 64bit',
        'huawei 20 sp1 64bit',

        //Greenbone
        'Greenbone OS',

        //HopeStageEnterpriselinux
        'HopeStage Enterprise linux V1.0',

        //HuNanKylin
        'HuNan Kylin 3.2',
        'HuNan Kylin 3.3',

        //HuaweiCloudEulerOS
        'Huawei Cloud EulerOS 1.0 ',

        // zhongxin serials
        'zhongxin V4 64',
        'zhongxin V3 64',

        // yimin serials
        'yimin V7 64',
        'yimin V4 64',
        'yimin os 64',
        'yimin os 32',

        // deepin serials
        'Deepin Server 15.2 64',
        'Deepin Server 15.1 64',
        'Deepin Server 15 64',
        'Deepin Server 15 64',

        // iSoft serials
        'iSoft Server 4.0',

        //YITU 64bit
        'YITU 64bit',

        //BC_linux
        'BC_linux 21.10 64bit',
        'BC_linux 7.6 64bit',
        'BC_linux 7.8 64bit',
        'BC_linux 8.x 64bit',
        'BC_linux oe22.10',

        //Astra
        'Astra Linux 1.4 64bit',

        //Wind River Linux
        'Wind River Linux 6.0 64bit',

        // new start serials
        'new start linux 5 04',
        'new start CGS Linux V4',
        'new start CGS Linux V5 64bit',

        // Hygon linux
        'Hygon linux',

        // EulerOS
        'EulerOS 2.1 64',
        'EulerOS 2.2 64',
        'EulerOS 2.3 64',
        'EulerOS 2.3 Docker 64',
        'EulerOS 2.3(UVP-KVM-2.3.RC3.B020d_FSO) 64bit',
        'EulerOS 2.3(UVP-KVM-2.5.RC3.B020_VRM) 64bit',
        'EulerOS 2.3(UVP-KVM-2.5.RC5.B030_FSO) 64bit',
        'EulerOS 2.5 Docker 64',
        'EulerOS 2.9 64bit',
        'EulerOS 2.10 64bit',
        'EulerOS 2.11 64bit',
        'EulerOS 2.12 64bit',

        // Kylin serials
        'Kylin',
        'Kylin 64 bit',
        'Kylin 4',
        'Kylin 4 64 bit',
        'Kylin 7',
        'Kylin 7 64 bit',
        'Kylin 10',
        'Kylin 10 64 bit',
        'Kylin 4.0.1E 64bit',
        'kylin 4.0.2 64bit',
        'Kylin Desktop V10 64bit',
        'Kylin Desktop V10 GFB 64bit',
        'Kylin Desktop V10 SP1 64bit',
        'Kylin OS 3.2 64bit',
        'Kylin OS 4.0.2 SP2 64bit',
        'Kylin OS 4.2 64bit',
        'Kylin Server V10 64bit',
        'Kylin Server V10 SP1 64bit',
        'Kylin Server V10 SP2 64bit',
        'Kylin Server V10 SP3 64bit',
        'KylinSEC OS 3.4 64bit',
        'KylinSEC server OS 3.4-x 64bit',
        'KylinSec OS server Linux 3.5.X 64bit',

        // Linux Mint
        'Linux Mint 18.1 Serena KDE 32bit',
        'Linux Mint 18.1 Serena KDE 64bit',
        'Linux Mint 18.1 Serena MATE 32bit',
        'Linux Mint 18.1 Serena MATE 64bit',
        'Linux Mint 18.1 Serena Xfce 32bit',
        'Linux Mint 18.1 Serena Xfce 64bit',
        'Linux Mint 19 Tara Cinnamo 64bit',
        'Linux Mint 19 Tara MATE 64bit',
        'Linux Mint 19 Tara Xfce 64bit',
        'LinxOS 6.0.100 64bit',
        'LinxOS server 6.0 for Euler 64bit',

        //NFS
        'NFSChina Server V4.0 64bit',

        // Open Euler serials
        'Open Euler',
        'Open Euler 64 bit',
        'Open Euler 20.03',
        'openEuler 20.03',
        'Open Euler 20.03 64 bit',
        'Open Euler 22',
        'Open Euler 22 64 bit',
        'openEuler 22.03',
        'Open Euler 20.03 64bit',
        'Open Euler 20.03 LTS SP1 64bit',
        'Open Euler 20.03 LTS SP2 64bit',
        'Open Euler 20.03 LTS SPX 64bit',
        'Open Euler 21.03 64bit',
        'Open Euler 22.03 LTS 64bit',
        'Open Euler 22.03 LTS SPX 64bit',

        // UOS serials
        'UOS',
        'UOS V20 desktop 64bit',
        'UOS V20 server 1050u1a 64bit',
        'UOS V20 server 1060a 64bit',
        'UOS V20 server 1060e 64bit',
        'UOS V20 server Industry Edition 64bit',
        'UOS V20 server(1050e) 64bit',
        'UOS v20 desktop(1050 update3) 64bit',

        // Anolis serials
        'Anolis OS',
        'Anolis OS 64 bit',
        'Anolis OS 7',
        'Anolis OS 7 64 bit',
        'Anolis OS 7.9 64 bit',
        'Anolis OS 8',
        'Anolis OS 8 64 bit',
        'Anolis OS 8.X 64 bit',

        // OpenCloudOS serials
        'OpenCloudOS',
        'OpenCloudOS 64 bit',
        'OpenCloudOS 8',
        'OpenCloudOS 8 64 bit',

        // Amazon linux
        'Amazon linux3 64',
        'Amazon linux2 64',

        // Alma Linux
        'Alma Linux 64',
        'Almalinux 8.3 64bit',
        'Almalinux 8.4 64bit',
        'Almalinux 8.6 64bit',
        'Almalinux 8.X 64bit',
        'Almalinux 9.X 64bit',

        // Other
        500000 => 'Other (32 bit) 32bit',
        'Other (64 bit) 64bit',
        'Other Windows(32 bit) 32bit',
        'Other Windows(64 bit) 64bit',
        'Other Linux(32 bit) 32bit',
        'Other Linux(64 bit) 64bit',
    ],

    /* middle disk controller type */
    'VmMiddleControllerType' => array(
        'VM_MIDDLE_CONTROLLER_TYPE_UNKNOWN',
        'VM_MIDDLE_CONTROLLER_TYPE_IDE',				// IDE
        'VM_MIDDLE_CONTROLLER_TYPE_SCSI',				// SCSI
        'VM_MIDDLE_CONTROLLER_TYPE_VSCSI',			// virtio SCSI
        'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO',			// virtio
        'VM_MIDDLE_CONTROLLER_TYPE_SATA',				// sata, for ovirt
        'VM_MIDDLE_CONTROLLER_TYPE_E1000',			// e1000, for network
        'VM_MIDDLE_CONTROLLER_TYPE_RTL8139',			// rtl8139, for network

        //add for vmware
        'VM_MIDDLE_CONTROLLER_TYPE_LSI_LOGIC',		// scsi: lsi logic
        'VM_MIDDLE_CONTROLLER_TYPE_LSI_LOGIC_SAS',	// scsi: lsi logic sas
        'VM_MIDDLE_CONTROLLER_TYPE_BUS_LOGIC',		// scsi: bus logic
        'VM_MIDDLE_CONTROLLER_TYPE_PARA_VIRTUAL_SCSI',// scsi: para virtual scsi
        'VM_MIDDLE_CONTROLLER_TYPE_AHCI',				// sata
        'VM_MIDDLE_CONTROLLER_TYPE_USBXAHCI',			// usbxhci
        'VM_MIDDLE_CONTROLLER_TYPE_USB',				// usb
        'VM_MIDDLE_CONTROLLER_TYPE_NVME',				// nvme

        'VM_MIDDLE_CONTROLLER_TYPE_PCI',				// pci
        'VM_MIDDLE_CONTROLLER_TYPE_SIO',				// sio

        //VM_MIDDLE_CONTROLLER_TYPE_E1000',			// e1000
        'VM_MIDDLE_CONTROLLER_TYPE_E1000E',			// e1000e
        'VM_MIDDLE_CONTROLLER_TYPE_PCNET32',			// pcnet32
        'VM_MIDDLE_CONTROLLER_TYPE_SRIOV',			// sriov
        'VM_MIDDLE_CONTROLLER_TYPE_VMXNET',			// vmxnet
        'VM_MIDDLE_CONTROLLER_TYPE_VMXNET2',			// vmxnet2
        'VM_MIDDLE_CONTROLLER_TYPE_VMXNET3',			// vmxnet3
        'VM_MIDDLE_CONTROLLER_TYPE_VMXNET3_VRDMA',        //VRDMA

        //add for openstack
        'VM_MIDDLE_CONTROLLER_TYPE_UML',				// uml
        'VM_MIDDLE_CONTROLLER_TYPE_XEN',				// xen
        'VM_MIDDLE_CONTROLLER_TYPE_FDC',				// fdc
        'VM_MIDDLE_CONTROLLER_TYPE_LXC',				// lxc
        'VM_MIDDLE_CONTROLLER_TYPE_NE2K_PCI',			// ne2k_pci
        'VM_MIDDLE_CONTROLLER_TYPE_PCNET',			// pcnet
        'VM_MIDDLE_CONTROLLER_TYPE_NETFRONT',			// netfront
        'VM_MIDDLE_CONTROLLER_TYPE_SPAPR_VLAN',		// spapr_vlan

        // add for ovirt rtl8139_virtio
        'VM_MIDDLE_CONTROLLER_TYPE_RTL8139_VIRTIO',	// rtl8139_virtio

        'BD_MIDDLE_CONTROLLER_TYPE_HYPERV_SCSI', // hyperv scsi - 34
        'BD_MIDDLE_CONTROLLER_TYPE_HYPERV_NET', // hyperv net - 35

        'BD_MIDDLE_CONTROLLER_TYPE_ENA', // aws ena - 36
        'BD_MIDDLE_CONTROLLER_TYPE_AWS_NVME', // aws nvme - 37
    ),



    'VmMiddleControllerTypeDes' => array(
        'UNKNOWN',
        'ide',      //1
        'scsi',
        'virtio scsi',
        'virtio',
        'sata',     //5
        'e1000',
        'rtl8139',

        //add for vmware
        'lsi logic',
        'lsi logic sas',
        'bus logic',    //10
        'para virtual scsi',
        'sata',
        'usbxhci',
        'usb',
        'nvme', //15
        'pci',
        'sio',


        'e1000e',
        'pcnet32',
        'sriov',    //20
        'vmxnet',
        'vmxnet2',
        'vmxnet3',
        'vrdma',

        //add for openstack
        'uml',
        'xen',
        'fdc',
        'lxc',
        'ne2k_pci',
        'pcnet',
        'netfront',
        'spapr_vlan',

        // add for ovirt rtl8139_virtio
        xphp_get_lang('UI_RECOVERY_VM_MIDDLE_CONTROLLER_TYPE_RTL8139_VIRTIO'),	// rtl8139_virtio

        'hyper-v scsi',
        'hyper-v network adapter',
        'ena',
        'nvme',
    ),

    // middle boot mode type
    'VmMiddleBootModeType' => array(
        'UNKNOWN',
        'BIOS',
        'UEFI',
    ),

    'EmdVmInterfaceMode' => [
        'EMD_VM_INTERFACE_MODE_UNKNOW',
        'EMD_VM_INTERFACE_MODE_VIRTIO',
        'EMD_VM_INTERFACE_MODE_E1000',
        'EMD_VM_INTERFACE_MODE_RTL8139',
        'EMD_VM_INTERFACE_MODE_NE2K_PCI',
        'EMD_VM_INTERFACE_MODE_VIRTIO_NET_PCI',
        'EMD_VM_INTERFACE_MODE_PCNET',
        'EMD_VM_INTERFACE_MODE_VMXNET3'
    ],
    'EmdVmInterfaceModeDes' => [
        'UNKNOWN',
        'virtio',
        'e1000',
        'rtl8139',
        'ne2k_pci',
        'virtio_net_pci', // 5
        'pcnet',
        'vmxnet3'
    ],

    'EmdVmTargetBus' => [
        'EMD_VM_TARGET_BUS_UNKNOW',
        'EMD_VM_TARGET_BUS_IDE',
        'EMD_VM_TARGET_BUS_VIRTIO',
        'EMD_VM_TARGET_BUS_SATA',
        'EMD_VM_TARGET_BUS_SCSI',
        'EMD_VM_TARGET_BUS_FDC',
        'EMD_VM_TARGET_BUS_PCI',
        'EMD_VM_TARGET_BUS_USB',
    ],
    'EmdVmTargetBusDes' => [
        'UNKNOWN',
        'ide',
        'virtio',
        'scsi',
        'fdc',
        'virtio_net_pci', // 5
        'pcnet',
        'vmxnet3'
    ],
    'BdMiddleCpuModeType' => [
        'BD_MIDDLE_CPU_MODE_TYPE_UNKNOWN' => 0,
        'BD_MIDDLE_CPU_MODE_TYPE_DEFAULT' => 1,          // default
        'BD_MIDDLE_CPU_MODE_TYPE_NONE' => 2,             // none
        'BD_MIDDLE_CPU_MODE_TYPE_HOST_MODEL' => 3,       // host-model
        'BD_MIDDLE_CPU_MODE_TYPE_HOST_PASSTHROUGH' => 4, // host_passthrough
        'BD_MIDDLE_CPU_MODE_TYPE_HYGON_CUSTOMIZED' => 5, // hygon_customized
        'BD_MIDDLE_CPU_MODE_TYPE_CUSTOM' => 6,           // custom
        'BD_MIDDLE_CPU_MODE_TYPE_DHYANA' => 7,           // dhyana
        'BD_MIDDLE_CPU_MODE_TYPE_EPYC' => 8,             // epyc
        'BD_MIDDLE_CPU_MODE_TYPE_EPYC_IBPB' => 9,        // epyc_ibpb
        'BD_MIDDLE_CPU_MODE_TYPE_HASWELL' => 10,         // haswell
        'BD_MIDDLE_CPU_MODE_TYPE_HASWELL_NOTSX' => 11,   // haswell_notsx
        'BD_MIDDLE_CPU_MODE_TYPE_BROADWELL' => 12,       // broadwell
        'BD_MIDDLE_CPU_MODE_TYPE_BROADWELL_NOTSX' => 13, // broadwell_notsx
        'BD_MIDDLE_CPU_MODE_TYPE_SANDYBRIDGE' => 14,     // sandybridge
        'BD_MIDDLE_CPU_MODE_TYPE_IVYBRIDGE' => 15,       // ivybridge
        'BD_MIDDLE_CPU_MODE_TYPE_CONROE' => 16,          // conroe
        'BD_MIDDLE_CPU_MODE_TYPE_PENRYN' => 17,          // penryn
        'BD_MIDDLE_CPU_MODE_TYPE_NEHALEM' => 18,         // nehalem
        'BD_MIDDLE_CPU_MODE_TYPE_WESTMERE' => 19,        // westmere
        'BD_MIDDLE_CPU_MODE_TYPE_OPTERON_G1' => 20,      // opteron_g1
        'BD_MIDDLE_CPU_MODE_TYPE_OPTERON_G2' => 21,      // opteron_g2
        'BD_MIDDLE_CPU_MODE_TYPE_OPTERON_G3' => 22,      // opteron_g3
        'BD_MIDDLE_CPU_MODE_TYPE_OPTERON_G4' => 23,      // opteron_g4
        'BD_MIDDLE_CPU_MODE_TYPE_PENTIUM' => 24,         // pentium
        'BD_MIDDLE_CPU_MODE_TYPE_PENTIUM2' => 25,        // pentium2
        'BD_MIDDLE_CPU_MODE_TYPE_PENTIUM3' => 26,        // pentiu3
        'BD_MIDDLE_CPU_MODE_TYPE_KUNPENG_920' => 27,     // kunpeng_920
        'BD_MIDDLE_CPU_MODE_TYPE_FT_2000' => 28,         // ft_2000+
        'BD_MIDDLE_CPU_MODE_TYPE_TENGYUN_S2500' => 29,   // tengyun_s2500
        'BD_MIDDLE_CPU_MODE_TYPE_LOONGSON_3A5000' => 30, // loongson_3a5000
        'BD_MIDDLE_CPU_MODE_TYPE_OTHER' => 31,           // other
        'BD_MIDDLE_CPU_MODE_TYPE_SKYLAKE_CLIENT' => 32,  // skylake client
        'BD_MIDDLE_CPU_MODE_TYPE_SKYLAKE_SERVER' => 33,  // skylake server
        'BD_MIDDLE_CPU_MODE_TYPE_SKYLAKE_SERVER_IBRS' => 34,  // skylake server ibrs
        'BD_MIDDLE_CPU_MODE_TYPE_SKYLAKE_CLIENT_NOTSX' => 35,  // skylake client notsx
        'BD_MIDDLE_CPU_MODE_TYPE_SKYLAKE_SERVER_NOTSX' => 36,  // skylake serve notTSX
        'BD_MIDDLE_CPU_MODE_TYPE_CASCADELAKE_SERVER' => 37,  // cascadeLake serve
        'BD_MIDDLE_CPU_MODE_TYPE_CASCADELAKE_SERVER_NOTSX' => 38,  // cascadeLake serve notTSX
        'BD_MIDDLE_CPU_MODE_TYPE_SANDYBRIDGE_IBRS' => 39,   // sandybridge ibrs
        'BD_MIDDLE_CPU_MODE_TYPE_SKYLAKE_SERVER_V4' => 40,  // skylake-server-v4
        'BD_MIDDLE_CPU_MODE_TYPE_SKYLAKE_SERVER_V5' => 41,  // skylake-server-v5
        'BD_MIDDLE_CPU_MODE_TYPE_SKYLAKE_CLIENT_IBRS' => 42,  // skylake-client-ibrs
        'BD_MIDDLE_CPU_MODE_TYPE_SKYLAKE_CLIENT_NOTSX_IBRS' => 43,  // skylake-client-notsx-ibrs
        'BD_MIDDLE_CPU_MODE_TYPE_SKYLAKE_CLIENT_V4' => 44,  // skylake-client-v4
        'BD_MIDDLE_CPU_MODE_TYPE_SKYLAKE_SERVER_NOTSX_IBRS' => 45,  // skylake-server-notsx-ibrs
        'BD_MIDDLE_CPU_MODE_TYPE_IVYBRIDGE_IBRS' => 46,  // ivybridge-ibrs
        'BD_MIDDLE_CPU_MODE_TYPE_HASWELL_IBRS' => 47,  // haswell-ibrs
        'BD_MIDDLE_CPU_MODE_TYPE_HASWELL_NOTSX_IBRS' => 48,  // haswell-notsx-ibrs
        'BD_MIDDLE_CPU_MODE_TYPE_BROADWELL_IBRS' => 49,  // broadwell-ibrs
        'BD_MIDDLE_CPU_MODE_TYPE_BROADWELL_NOTSX_IBRS' => 50,  // broadwell-notsx-ibrs
        'BD_MIDDLE_CPU_MODE_TYPE_WESTMERE_IBRS' => 51,  // westmere-ibrs
        'BD_MIDDLE_CPU_MODE_TYPE_NEHALEM_IBRS' => 52,  // nehalem-ibrs
        'BD_MIDDLE_CPU_MODE_TYPE_ICELAKE_CLIENT' => 53,  // icelake-client
        'BD_MIDDLE_CPU_MODE_TYPE_ICELAKE_CLIENT_NOTSX' => 54,  // icelake-client-notsx
        'BD_MIDDLE_CPU_MODE_TYPE_ICELAKE_SERVER' => 55,  // icelake-server
        'BD_MIDDLE_CPU_MODE_TYPE_ICELAKE_SERVER_NOTSX' => 56,  // icelake-server-notsx
        'BD_MIDDLE_CPU_MODE_TYPE_ICELAKE_SERVER_V3' => 57,  // icelake-server-v3
        'BD_MIDDLE_CPU_MODE_TYPE_ICELAKE_SERVER_V4' => 58,  // icelake-server-v4
        'BD_MIDDLE_CPU_MODE_TYPE_ICELAKE_SERVER_V5' => 59,  // icelake-server-v5
        'BD_MIDDLE_CPU_MODE_TYPE_ICELAKE_SERVER_V6' => 60,  // icelake-server-v6
        'BD_MIDDLE_CPU_MODE_TYPE_CASCADELAKE_SERVER_V2' => 61,  // cascadelake-server-v2
        'BD_MIDDLE_CPU_MODE_TYPE_CASCADELAKE_SERVER_V4' => 62,  // cascadelake-server-v4
        'BD_MIDDLE_CPU_MODE_TYPE_CASCADELAKE_SERVER_V5' => 63,  // cascadelake-server-v5
        'BD_MIDDLE_CPU_MODE_TYPE_COOPERLAKE' => 64,  // cooperlake
        'BD_MIDDLE_CPU_MODE_TYPE_COOPERLAKE_V2' => 65,  // cooperlake-v2
        'BD_MIDDLE_CPU_MODE_TYPE_EPYC_GENOA' => 66,  // epyc-genoa
        'BD_MIDDLE_CPU_MODE_TYPE_EPYC_MILAN' => 67,  // epyc-milan
        'BD_MIDDLE_CPU_MODE_TYPE_EPYC_MILAN_V2' => 68,  // epyc-milan-v2
        'BD_MIDDLE_CPU_MODE_TYPE_EPYC_ROME' => 69,  // epyc-rome
        'BD_MIDDLE_CPU_MODE_TYPE_EPYC_ROME_V2' => 70,  // epyc-rome-v2
        'BD_MIDDLE_CPU_MODE_TYPE_EPYC_ROME_V3' => 71,  // epyc-rome-v3
        'BD_MIDDLE_CPU_MODE_TYPE_EPYC_ROME_V4' => 72,  // epyc-rome-v4
        'BD_MIDDLE_CPU_MODE_TYPE_EPYC_V3' => 73,  // epyc-v3
        'BD_MIDDLE_CPU_MODE_TYPE_EPYC_V4' => 74,  // epyc-v4
        'BD_MIDDLE_CPU_MODE_TYPE_SAPPHIRERAPIDS' => 75,  // sapphirerapids
        'BD_MIDDLE_CPU_MODE_TYPE_SAPPHIRERAPIDS_V2' => 76,  // sapphirerapids-v2
        'BD_MIDDLE_CPU_MODE_TYPE_GRANITERAPIDS' => 77, // graniterapids
        'BD_MIDDLE_CPU_MODE_TYPE_KNIGHTSMILL' => 78, // knightsmill
        'BD_MIDDLE_CPU_MODE_TYPE_OPTERON_G5' => 79,  // opteron_g5
        'BD_MIDDLE_CPU_MODE_TYPE_PHENOM' => 80,  // phenom
        'BD_MIDDLE_CPU_MODE_TYPE_ATHLON' => 81,  // athlon
        'BD_MIDDLE_CPU_MODE_TYPE_COREDUO' => 82,  // coreduo
        'BD_MIDDLE_CPU_MODE_TYPE_CORE2DUO' => 83,  // core2duo
        'BD_MIDDLE_CPU_MODE_TYPE_QEMU32' => 84,  // qemu32
        'BD_MIDDLE_CPU_MODE_TYPE_QEMU64' => 85,  // qemu64
        'BD_MIDDLE_CPU_MODE_TYPE_KVM32' => 86,  // kvm32
        'BD_MIDDLE_CPU_MODE_TYPE_KVM64' => 87,  // kvm64
        'BD_MIDDLE_CPU_MODE_TYPE_MAX' => 88,  // max
        'BD_MIDDLE_CPU_MODE_TYPE_486' => 89,  // 486
    ],
    'BdMiddleCpuModeTypeDes' => [
        "unknown",
        "default",
        "none",
        "host-model",
        "host_passthrough",
        "hygon_customized",
        "custom",
        "dhyana",
        "epyc",
        "epyc_ibpb",
        "haswell",
        "haswell_notsx",
        "broadwell",
        "broadwell_notsx",
        "sandybridge",
        "ivybridge",
        "conroe",
        "penryn",
        "nehalem",
        "westmere",
        "opteron_g1",
        "opteron_g2",
        "opteron_g3",
        "opteron_g4",
        "pentium",
        "pentium2",
        "pentiu3",
        "kunpeng_920",
        "ft_2000+",
        "tengyun_s2500",
        "loongson_3a5000",
        "other",
        "skylake client",
        "skylake server",
        "skylake server ibrs",
        "skylake client notsx",
        "skylake server notTSX",
        "cascadeLake server",
        "cascadeLake server notTSX",
        'sandybridge ibrs',
        'skylake-server-v4',
        'skylake-server-v5',
        'skylake-client-ibrs',
        'skylake-client-notsx-ibrs',
        'skylake-client-v4',
        'skylake-server-notsx-ibrs',
        'ivybridge-ibrs',
        'haswell-ibrs',
        'haswell-notsx-ibrs',
        'broadwell-ibrs',
        'broadwell-notsx-ibrs',
        'westmere-ibrs',
        'nehalem-ibrs',
        'icelake-client',
        'icelake-client-notsx',
        'icelake-server',
        'icelake-server-notsx',
        'icelake-server-v3',
        'icelake-server-v4',
        'icelake-server-v5',
        'icelake-server-v6',
        'cascadelake-server-v2',
        'cascadelake-server-v4',
        'cascadelake-server-v5',
        'cooperlake',
        'cooperlake-v2',
        'epyc-genoa',
        'epyc-milan',
        'epyc-milan-v2',
        'epyc-rome',
        'epyc-rome-v2',
        'epyc-rome-v3',
        'epyc-rome-v4',
        'epyc-v3',
        'epyc-v4',
        'sapphirerapids',
        'sapphirerapids-v2',
        'graniterapids',
        'knightsmill',
        'opteron_g5',
        'phenom',
        'athlon',
        'coreduo',
        'core2duo',
        'qemu32',
        'qemu64',
        'kvm32',
        'kvm64',
        'max',
        '486',
    ],

    //用于获取虚拟机类型的时候其相关的名称限制
    //     array(  //虚拟机类型
    //         'len' => '80',//字符限制 中文2字符 英文1字符
    //         'limit' => '', //用于js端的正则表达式
    //         'msg' =>'UI_NAME_MSG_80', //用于js端的语言包名称
    //     ),
    'VmNameCheck' => array(
        0 => 'Unknown',
        1 => array(  //VMware vSphere
            'len' => '80',
            'limit' => '',
            'msg' => Xphp::$_lang['UI_NAME_MSG_80'],
            'vmware_flag' => true,
        ),
        2 => array(  //Microsoft Hyper-V
            'len' => '100',
            'limit' => '^[^\\\\:*?"<>|/]+$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_HYPERV'],
        ),
        3 => array(  //Citrix XenServer
            'len' => '32767',
            'limit' => '[^\s]+',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_XENSERVER'],
            'disk_len' => '32767',
            'disk_limit' => '[^\s]+',
            'disk_msg' =>Xphp::$_lang['UI_NAME_MSG_XENSERVER']
        ),
        8 => array(  //InCloud Sphere Xen
            'len' => '128',
            'limit' => '',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_128'],
        ),
        9 => array(  //Halsign vGate
            'len' => '128',
            'limit' => '',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_128'],
        ),
        10 => array(  //NeoKylin
            'len' => '',
            'limit' => '',
            'msg' =>'',
        ),
        11 => array(  //H3C
            'len' => '64',
            'limit' => '^[\u4e00-\u9fa5a-zA-Z0-9 _/./-]+[\u4e00-\u9fa5a-zA-Z0-9 _/-]$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_64_H'],
            'disk_len' => '100',
            'disk_limit' => '^(?![-.])(?!\d+$)[a-zA-Z0-9\-_.]+$',
            'disk_msg' =>Xphp::$_lang['UI_NAME_MSG_H3C']
        ),
        12 => array(  //SANGFOR HCI
            'len' => '64',
            'limit' => '^[\u4e00-\u9fa5a-zA-Z0-9 _@+().（）【】\-]+$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_SANGFOR'],
        ),
        14 => array(  //FlexCloud
            'len' => '255',
            'limit' => '',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_255'],
        ),
        15 => array(  //OpenStack
            'len' => '255',
            'limit' => '',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_255'],
        ),
        16 => array(  //Huawei FusionSphere Kvm
            'len' => '256',
            'limit' => '^((?!\\\\n)[^<>&|$!;`])+$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_HUAWEIKVM'],
            'disk_len' => '256',
            'disk_limit' => '[^\s]+',
            'disk_msg' =>Xphp::$_lang['UI_NAME_MSG_HUAWEIKVM_DISK']
        ),
        17 => array(  //Huawei FusionCompute
            'len' => '256',
            'limit' => '',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_256'],
        ),
        18 => array(  //Winhong CNware
            'len' => '128',
            'limit' => '^[\u4e00-\u9fa5a-zA-Z0-9_.\-]+$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_WINHONG'],
            'disk_len' => '80',
            'disk_limit' => '^[a-zA-Z_][a-zA-Z0-9_.\-]+$',
            'disk_msg' =>Xphp::$_lang['UI_NAME_MSG_WINHONG_DISK'],
        ),
        19 => array(  //Redhat RHV
            'len' => '64',
            'limit' => '^[\u4e00-\u9fa5a-zA-Z0-9_.\-]+$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_OVIRT'],
            'disk_len' => '255',
            'disk_limit' => '^[\u4e00-\u9fa5a-zA-Z0-9_.\-]+$',
            'disk_msg' =>Xphp::$_lang['UI_NAME_MSG_OVIRT_DISK'],
        ),
        20 => array(  //D-Server
            'len' => '128',
            'limit' => '',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_128'],
        ),
        21 => array(  //SVM CloudVirtual
            'len' => '80',
            'limit' => '',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_80'],
            'vmware_flag' => true,
        ),
        22 => array(  //Flex HCS
            'len' => '255',
            'limit' => '',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_255'],
        ),
        23 => array(  //Os Easy V-server
            'len' => '128',
            'limit' => '',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_128'],
        ),
        24 => array(  //InCloud Sphere KVM
            'len' => '128',
            'limit' => '^[\u4e00-\u9fa5a-zA-Z0-9_/./-]+$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_ICS'],
            'disk_len' => '128',
            'disk_limit' => '',
            'disk_msg' =>Xphp::$_lang['UI_NAME_MSG_128'],
        ),
        25 => array(  //V-Server
            'len' => '128',
            'limit' => '',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_128'],
        ),
        26 => array(  //ZStack
            'len' => '128',
            'limit' => '^[\u4e00-\u9fa5a-zA-Z0-9 _:+().\-]+$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_ZSTACK'],
            'disk_len' => '128',
            'disk_limit' => '^[\u4e00-\u9fa5a-zA-Z0-9 _:+().\-]+$',
            'disk_msg' =>Xphp::$_lang['UI_NAME_MSG_ZSTACK'],
        ),
        27 => array(  //Easted vServer
            'len' => '64',
            'limit' => '^[a-zA-Z0-9_/./-]+$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_64_L'],
        ),
        28 => array(  //XCP-ng
            'len' => '128',
            'limit' => '',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_128'],
        ),
        29 => array(  //Oracle Linux Virtualization Manager(OLVM)
            'len' => '64',
            'limit' => '^[\u4e00-\u9fa5a-zA-Z0-9_.\-]+$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_OVIRT'],
            'disk_len' => '255',
            'disk_limit' => '^[\u4e00-\u9fa5a-zA-Z0-9_.\-]+$',
            'disk_msg' =>Xphp::$_lang['UI_NAME_MSG_OVIRT_DISK'],
        ),
        30 => array(  //XSKY XECCP
            'len' => '128',
            'limit' => '^[\u4e00-\u9fa5a-zA-Z0-9_@:/+/(/)/./-]+$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_128_L'],
        ),
        31 => array(  //Inspur Openstack
            'len' => '255',
            'limit' => '',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_255'],
        ),
        32 => array(  //Winhong KVM
            'len' => '128',
            'limit' => '^[\u4e00-\u9fa5a-zA-Z0-9_.\-]+$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_WINHONG'],
            'disk_len' => '80',
            'disk_limit' => '^[a-zA-Z0-9_.][a-zA-Z0-9_.\-]+$',
            'disk_msg' =>Xphp::$_lang['UI_NAME_MSG_WINHONG_KVM_DISK'],
        ),

        33 => array(  //SmartX
            'len' => '255',
            'limit' => '^[\u4e00-\u9fa5a-zA-Z0-9 ^()_+\-=\[\]{},.！￥……（）——【】、；‘’：“”，。、《》]+$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_SMARTX'],
            'disk_len' => '255',
            'disk_limit' => '^[\u4e00-\u9fa5a-zA-Z0-9 ^()_+\-=\[\]{},.！￥……（）——【】、；‘’：“”，。、《》]+$',
            'disk_msg' =>Xphp::$_lang['UI_NAME_MSG_SMARTX'],
        ),
        34 => array(  //Sugon CloudView
            'len' => '255',
            'limit' => '',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_255'],
        ),
        35 => array(  //Inspur Cloud Platform
            'len' => '255',
            'limit' => '',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_255'],
        ),
        36 => array(  //EasyStack
            'len' => '255',
            'limit' => '',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_255'],
        ),
        37 => array(  //Fiberhome Openstack
            'len' => '255',
            'limit' => '',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_255'],
        ),
        38 => array(  //CTSI Openstack
            'len' => '255',
            'limit' => '',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_255'],
        ),
        39 => array(  //AWCloud
            'len' => '255',
            'limit' => '',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_255'],
        ),
        40 => array(  //Inspur VVDK
            'len' => '128',
            'limit' => '^[\u4e00-\u9fa5a-zA-Z0-9_/./-]+$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_ICS'],
            'disk_len' => '128',
            'disk_limit' => '^[\u4e00-\u9fa5a-zA-Z0-9_/./-]+$',
            'disk_msg' =>Xphp::$_lang['UI_NAME_MSG_ICS'],
        ),
        41 => array(  //zVirt
            'len' => '64',
            'limit' => '^[\u4e00-\u9fa5a-zA-Z0-9_.\-]+$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_OVIRT'],
            'disk_len' => '255',
            'disk_limit' => '^[\u4e00-\u9fa5a-zA-Z0-9_.\-]+$',
            'disk_msg' =>Xphp::$_lang['UI_NAME_MSG_OVIRT_DISK'],
        ),
        42 => array(  //Proxmox
            'len' => '128',
            'limit' => '^[a-zA-Z0-9][a-zA-Z0-9_-]*[a-zA-Z0-9]$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_PROXMOX'],
        ),
        43 => array(  //Xfusion Kvm
            'len' => '256',
            'limit' => '[^\s]+',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_HUAWEIKVM'],
            'disk_len' => '256',
            'disk_limit' => '[^\s]+',
            'disk_msg' =>Xphp::$_lang['UI_NAME_MSG_HUAWEIKVM']
        ),
        44 => array(  //xhere
            'len' => '256',
            'limit' => '',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_256'],
        ),
        45 => array(  //hostvm
            'len' => '64',
            'limit' => '^[\u4e00-\u9fa5a-zA-Z0-9_.\-]+$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_OVIRT'],
            'disk_len' => '255',
            'disk_limit' => '^[\u4e00-\u9fa5a-zA-Z0-9_.\-]+$',
            'disk_msg' =>Xphp::$_lang['UI_NAME_MSG_OVIRT_DISK'],
        ),
        46 => array(  //cbr
            'len' => '256',
            'limit' => '',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_256'],
        ),
        47 => array(  //SANGFOR VVDK
            'len' => '70',
            'limit' => '^[\u4e00-\u9fa5a-zA-Z0-9 _+().（）【】\-]+$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_SCP'],
            'scp_flag' => true,
        ),
        48 => array(  //CloudView KVM
            'len' => '128',
            'limit' => '^[\u4e00-\u9fa5a-zA-Z0-9_:/+/(/)/./-]+$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_128_L'],
        ),
        49 => array(  //red virt
            'len' => '64',
            'limit' => '^[\u4e00-\u9fa5a-zA-Z0-9_.\-]+$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_OVIRT'],
            'disk_len' => '255',
            'disk_limit' => '^[\u4e00-\u9fa5a-zA-Z0-9_.\-]+$',
            'disk_msg' =>Xphp::$_lang['UI_NAME_MSG_OVIRT_DISK'],
        ),
        50 => array(  //rosa virt
            'len' => '64',
            'limit' => '^[\u4e00-\u9fa5a-zA-Z0-9_.\-]+$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_OVIRT'],
            'disk_len' => '255',
            'disk_limit' => '^[\u4e00-\u9fa5a-zA-Z0-9_.\-]+$',
            'disk_msg' =>Xphp::$_lang['UI_NAME_MSG_OVIRT_DISK'],
        ),
        51 => array(  //H3C CAS CVD
            'len' => '64',
            'limit' => '^[\u4e00-\u9fa5a-zA-Z0-9 _/./-]+[\u4e00-\u9fa5a-zA-Z0-9 _/-]$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_64_H'],
            'disk_len' => '100',
            'disk_limit' => '^(?![-.])(?!\d+$)[a-zA-Z0-9\-_.]+$',
            'disk_msg' =>Xphp::$_lang['UI_NAME_MSG_H3C']
        ),
        52 => array(  //oVirt
            'len' => '64',
            'limit' => '^[\u4e00-\u9fa5a-zA-Z0-9_.\-]+$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_OVIRT'],
            'disk_len' => '255',
            'disk_limit' => '^[\u4e00-\u9fa5a-zA-Z0-9_.\-]+$',
            'disk_msg' =>Xphp::$_lang['UI_NAME_MSG_OVIRT_DISK'],
        ),
        53 => array(  //Lenovo AIO
            'len' => '85',
            'limit' => '^[\u4e00-\u9fa5a-zA-Z0-9][\u4e00-\u9fa5a-zA-Z0-9_.:+\-]*$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_LENOVO_AIO'],
        ),
        54 => array(  //Huawei Cloud Stack
            'len' => '255',
            'limit' => '',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_255'],
        ),
        55 => array(  //Volcano Cloud
            'len' => '128',
            'limit' => '^[\u4e00-\u9fa5a-zA-Z0-9_/./-]+$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_VOLC'],
            'disk_len' => '128',
            'disk_limit' => '',
            'disk_msg' =>Xphp::$_lang['UI_NAME_MSG_128'],
        ),
        56 => array(  //ZStack ZSphere
            'len' => '128',
            'limit' => '^[\u4e00-\u9fa5a-zA-Z0-9 _:+().\-]+$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_ZSTACK'],
            'disk_len' => '128',
            'disk_limit' => '^[\u4e00-\u9fa5a-zA-Z0-9 _:+().\-]+$',
            'disk_msg' =>Xphp::$_lang['UI_NAME_MSG_ZSTACK'],
        ),
        57 => array(  //KSphere
            'len' => '128',
            'limit' => '^[\u4e00-\u9fa5a-zA-Z0-9_/./-]+$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_KSPHERE'],
            'disk_len' => '128',
            'disk_limit' => '',
            'disk_msg' =>Xphp::$_lang['UI_NAME_MSG_128'],
        ),
        58 => array(  //Arcfra
            'len' => '255',
            'limit' => '^[\u4e00-\u9fa5a-zA-Z0-9 ^()_+\-=\[\]{},.！￥……（）——【】、；‘’：“”，。、《》]+$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_SMARTX'],
            'disk_len' => '255',
            'disk_limit' => '^[\u4e00-\u9fa5a-zA-Z0-9 ^()_+\-=\[\]{},.！￥……（）——【】、；‘’：“”，。、《》]+$',
            'disk_msg' =>Xphp::$_lang['UI_NAME_MSG_SMARTX'],
        ),
        59 => array(  //NexaVM nSSV
            'len' => '128',
            'limit' => '^[\u4e00-\u9fa5a-zA-Z0-9 _:+().\-]+$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_ZSTACK'],
            'disk_len' => '128',
            'disk_limit' => '^[\u4e00-\u9fa5a-zA-Z0-9 _:+().\-]+$',
            'disk_msg' =>Xphp::$_lang['UI_NAME_MSG_ZSTACK'],
        ),
        60 => array(  //NexaVM nCSSV
            'len' => '128',
            'limit' => '^[\u4e00-\u9fa5a-zA-Z0-9 _:+().\-]+$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_ZSTACK'],
            'disk_len' => '128',
            'disk_limit' => '^[\u4e00-\u9fa5a-zA-Z0-9 _:+().\-]+$',
            'disk_msg' =>Xphp::$_lang['UI_NAME_MSG_ZSTACK'],
        ),

        100 => array(  //aws
            'len' => '256',
            'limit' => '',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_256'],
        ),
        101 => array(  //huawei cloud
            'len' => '256',
            'limit' => '',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_256'],
        ),
        108 => array(  //emd
            'len' => '256',
            'limit' => '^[a-zA-Z0-9_-]+$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_EMD'],
        ),
    ),
);
