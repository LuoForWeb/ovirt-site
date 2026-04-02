<?php

namespace app\v1\common\logic;

class Agent extends Base
{
    /**
     * 得到系统所有的安装包信息(包括代理插件、虚拟机插件)
     * TODO: 拆分获取逻辑
     * @link \app\v1\resources\v0\logic\Client::getClientPackages()
     * @link \app\v1\resources\v0\logic\Client::getDownloadPackage()
     * @link \app\v1\resources\v0\logic\Client::getAgentVersions()
     * @return array
     */
    /**
     * 得到系统所有的安装包信息
     * return array
     */
    public function getAllPackagesInfo()
    {
        $agent = array(
            'WINDOWS' => '#',
            'RHEL5' => '#',
            'RHEL6' => '#',
            'RHEL7' => '#',
            'RHEL8' => '#',
            'RHEL9' => '#',
            'UBUNTU' => '#',
            'DEBIAN' => '#',
            'KYLIN' => '#',
            'UNIONTECH' => '#',
            'WINPE' => '#',
            'xe.6.2' => '#',
            'xe.6.5' => '#',
            'RHEL.6' => '#',
            'RHEL.7' => '#',
            'RHEL.8' => '#',
            'Ubuntu.12' => '#',
            'whrelease' => '#',
            'release' => '#',
            'stack-cloud.RHEL' => '#',
            'stack-cloud.Ubuntu' => '#',
            'stack-docker.RHEL' => '#',
            'stack-docker.Ubuntu' => '#',
            "vinchin-agent" => '#',
            "dbcdp-agent.windows" => '#',
            "dbcdp-agent.linux" => '#',
            'el7' => '#',
            'el8' => '#',
            'ARM-RHEL7' => '#',
            'ARM-RHEL8' => '#',
            'ARM-el7' => '#',
            'KYLINX86' => '#',
            'ZKFD-V4' => '#',
            'UNIONTECHX86' => '#',
            'ANOLISOSX64' => '#',
            //add
            'EulerX86' => '#',
            'EulerAARCH64' => '#',
            'SUSE' => '#',
            'ROCKYLINUX8' => '#',
            'ROCKYLINUX9' => '#',
            'ORACLELINUX6' => '#',
            'ORACLELINUX7' => '#',
            'ORACLELINUX8' => '#',
            'ORACLELINUX9' => '#',
            'ANOLIS7OSX64' => '#',
            'ANOLIS8OSX64' => '#',
            'ASTRALINUX' => '#',
            'REDOS' => '#',
            'LINX' => '#',
            'Linx'=> '#',
            'ZKRedFlagX86' => '#',
            'ZKRedFlagAARCH64' => '#'
        );
        //按时间排序,时间从前到后,防止升级的时候有多个匹配成功也可以返回最新的
        $cmdStr = 'ls -tr ' . xphp_get_config('app', 'AGENT_PATH');
        exec($cmdStr, $agentInfo);
        //统一换成大写字母做判断,避免写错,排错要一个个字母去看,比较麻烦

        // 使用正则表达式匹配包名中的操作系统、Linux 版本和架构
        $systemVendor = '';
        $enterpriseVersions = xphp_get_config('app', 'ENTERPRISE');
        $enterpriseVersions['enterprise_ak'] = 'vinchin_enterprise_ak';  // 新创麒麟arm版
        $enterpriseVersions['enterprise_ck'] = 'vinchin_enterprise_ck';  // 新创麒麟x86版
        if ('vdms' == xphp_get_config('app','SYSTEM_INFO')['vendor']) {
            // GMP采用vinchin头进行匹配
            $systemVendor = 'vinchin';
        } elseif (!in_array(xphp_get_config('app', 'SYSTEM_INFO')['enterprise'], $enterpriseVersions)){
            //oem统一用backup-system
            $systemVendor = 'backup-system';
        } else {
            // 其他使用自己的vendor作为头进行匹配
            $systemVendor = xphp_get_config('app','SYSTEM_INFO')['vendor'];
        }
        foreach ($agentInfo as $name){

            // 匹配操作系统子项的正则表达式
            $pattern = '/' . $systemVendor . '-backup-agent-(.*)-AGENT\.([^\.]+)(?:\.(\d+))?-(.+)\.tar\.gz/';

            preg_match($pattern, $name, $matches);
            $packageNames = [];
            if (!empty($matches)){
                $package_version = $matches[1];
                $operating_systems = explode('-', $matches[2]);
                $linux_version = isset($matches[3]) ? $matches[3] : '';
                $architecture = $matches[4];

                foreach ($operating_systems as $os) {
                    $packageNames[] = strtoupper($os) . $linux_version . '-'.$architecture;
                    $packageNames['linux_version'] = $linux_version;
                    $packageNames['architecture'] = $architecture;
                }
            }

            if(strpos(strtoupper($name), strtoupper('dbcdp-agent.windows'))){
                $agent['dbcdp-agent.windows'] = "/agent/" . $name;
                continue;
            }
            if(strpos(strtoupper($name), strtoupper('dbcdp-agent.linux')) === 0){
                $agent['dbcdp-agent.linux'] = "/agent/" . $name;
                continue;
            }
            if(strpos(strtoupper($name), strtoupper('hyper-v-agent'))){
                $agent['vinchin-agent'] = "/agent/" . $name;
                continue;
            }

            //客户端   示例:RHEL8-x86_64
            if(strpos(strtoupper($name), strtoupper('backup-agent.windows'))){
                $agent['WINDOWS'] = array();
                $agent['WINDOWS']['fileName'] = "/agent/" . $name;
                $agent['WINDOWS']['showName'] = $name;
                continue;
            }
            if(in_array('RHEL5-Based-x86_64',$packageNames)){
                $agent['RHEL5'] = array();
                $agent['RHEL5']['fileName'] = "/agent/" . $name;
                $agent['RHEL5']['showName'] = $name;
                $agent['RHEL5']['linux_version'] = $packageNames['linux_version'];
                $agent['RHEL5']['architecture'] = $packageNames['architecture'];
                continue;
            }
            if(in_array('RHEL6-Based-x86_64',$packageNames)){
                $agent['RHEL6'] = "/agent/" . $name;
                $agent['ORACLELINUX6'] = "/agent/" . $name;
                $systems = ['RHEL6', 'ORACLELINUX6'];
                foreach ($systems as $system) {
                    $agent[$system] = array();
                    $agent[$system]['fileName'] = "/agent/" . $name;
                    $agent[$system]['showName'] = $name;
                    $agent[$system]['linux_version'] = $packageNames['linux_version'];
                    $agent[$system]['architecture'] = $packageNames['architecture'];
                }
                continue;
            }
            if(in_array('RHEL7-Based-x86_64',$packageNames)){
                $agent['RHEL7'] = "/agent/" . $name;
                $agent['ORACLELINUX7'] = "/agent/" . $name;
                $agent['REDOS'] = "/agent/" . $name;
                $agent['ANOLIS7OSX64'] = "/agent/" . $name;
                $systems = ['RHEL7', 'ORACLELINUX7','REDOS','ANOLIS7OSX64'];
                foreach ($systems as $system) {
                    $agent[$system] = array();
                    $agent[$system]['fileName'] = "/agent/" . $name;
                    $agent[$system]['showName'] = $name;
                    $agent[$system]['linux_version'] = $packageNames['linux_version'];
                    $agent[$system]['architecture'] = $packageNames['architecture'];
                }
                continue;
            }
            if(in_array('RHEL8-Based-x86_64',$packageNames)){
                // 下载的是同一个包

                $systems = ['RHEL8', 'ORACLELINUX8','ANOLIS8OSX64','ROCKYLINUX8','EulerX86','ZKRedFlagX86'];
                foreach ($systems as $system) {
                    $agent[$system] = array();
                    $agent[$system]['fileName'] = "/agent/" . $name;
                    $agent[$system]['showName'] = $systemVendor.'-'.'backup-agent-'.$package_version.'-AGENT.'.$system.'-'.$packageNames['architecture'].'.tar'.'.gz';
                    $agent[$system]['linux_version'] = $packageNames['linux_version'];
                    $agent[$system]['architecture'] = $packageNames['architecture'];
                }
                continue;
            }
            if(in_array('RHEL9-Based-x86_64',$packageNames)){
                // 下载的是同一个包
                $agent['RHEL9'] = "/agent/" . $name;
                $agent['ORACLELINUX9'] = "/agent/" . $name;
                $agent['ROCKYLINUX9'] = "/agent/" . $name;
                $systems = ['RHEL9', 'ORACLELINUX9','ROCKYLINUX9'];
                foreach ($systems as $system) {
                    $agent[$system] = array();
                    $agent[$system]['fileName'] = "/agent/" . $name;
                    $agent[$system]['showName'] = $name;
                    $agent[$system]['linux_version'] = $packageNames['linux_version'];
                    $agent[$system]['architecture'] = $packageNames['architecture'];
                }
                continue;
            }
            if(in_array('DEBIAN-x86_64',$packageNames) && in_array('BASED-x86_64', $packageNames)){
                // 下载的是同一个包
                $agent['DEBIAN'] = "/agent/" . $name;
                $agent['UBUNTU'] = "/agent/" . $name;
                $agent['SUSE'] = "/agent/" . $name;
                $agent['LINX'] = "/agent/" . $name;
                $agent['ASTRALINUX'] = "/agent/" . $name;
                $systems = ['DEBIAN', 'UBUNTU', 'SUSE', 'LINX', 'ASTRALINUX'];
                foreach ($systems as $system) {
                    $agent[$system] = array();
                    $agent[$system]['fileName'] = "/agent/" . $name;
                    $agent[$system]['showName'] = $name;
                    $agent[$system]['linux_version'] = $packageNames['linux_version'];
                    $agent[$system]['architecture'] = $packageNames['architecture'];
                }
                continue;
            }
            if(in_array('KYLIN-aarch64',$packageNames)){
                // 下载的是同一个包
                $agent['KYLIN'] = "/agent/" . $name;
                $agent['Linx'] = "/agent/" . $name;
                $systems = ['KYLIN','Linx'];
                foreach ($systems as $system){
                    $agent[$system] = array();
                    $agent[$system]['fileName'] = "/agent/" . $name;
                    $agent[$system]['showName'] = $systemVendor.'-'.'backup-agent-'.$package_version.'-AGENT.'.$system.'-'.$packageNames['architecture'].'.tar'.'.gz';;
                    $agent[$system]['linux_version'] = $packageNames['linux_version'];
                    $agent[$system]['architecture'] = $packageNames['architecture'];
                }
                continue;
            }
            if(in_array('KYLIN-x86_64',$packageNames)){
                $agent['KYLINX86'] = array();
                $agent['KYLINX86']['fileName'] = "/agent/" . $name;
                $agent['KYLINX86']['showName'] = $name;
                $agent['KYLINX86']['linux_version'] = $packageNames['linux_version'];
                $agent['KYLINX86']['architecture'] = $packageNames['architecture'];
                continue;
            }
            //统信x86
            if(in_array('UOS-x86_64',$packageNames)){
                $agent['UNIONTECHX86'] = array();
                $agent['UNIONTECHX86']['fileName'] = "/agent/" . $name;
                $agent['UNIONTECHX86']['showName'] = $name;
                $agent['UNIONTECHX86']['linux_version'] = $packageNames['linux_version'];
                $agent['UNIONTECHX86']['architecture'] = $packageNames['architecture'];
                continue;
            }

            if(strpos(strtoupper($name), strtoupper('winpe-os-agent'))){
                $agent['WINPE'] = array();
                $agent['WINPE']['fileName'] = "/agent/" . $name;
                $agent['WINPE']['showName'] = $name;
                $agent['WINPE']['linux_version'] = $packageNames['linux_version'];
                $agent['WINPE']['architecture'] = $packageNames['architecture'];
                continue;
            }

            if(strpos(strtoupper($name), strtoupper('xe.6.2'))){
                $agent['xe.6.2'] = "/agent/" . $name;
                continue;
            }
            if(strpos(strtoupper($name), strtoupper('xe.6.5'))){
                $agent['xe.6.5'] = "/agent/" . $name;
                continue;
            }
            if(strpos(strtoupper($name), strtoupper('RHEL.6'))){
                $agent['RHEL.6'] = "/agent/" . $name;
                continue;
            }
            if(strpos(strtoupper($name), strtoupper('Ubuntu.12'))){
                $agent['Ubuntu.12'] = "/agent/" . $name;
                continue;
            }
            if(strpos(strtoupper($name), strtoupper('RHEL.7-x86'))){
                $agent['RHEL.7'] = "/agent/" . $name;
                continue;
            }
            if(strpos(strtoupper($name), strtoupper('RHEL.8-aarch64'))){
                $agent['ARM-RHEL8'] = "/agent/" . $name;
                continue;
            }
            if(strpos(strtoupper($name), strtoupper('RHEL.7-aarch64'))){
                $agent['ARM-RHEL7'] = "/agent/" . $name;
                continue;
            }
            if(strpos(strtoupper($name), strtoupper('Debian.7-x86_64'))){
                $agent['Debian.7'] = "/agent/" . $name;
                continue;
            }
            //中科v4
            if(in_array('NFSCHINA-x86_64',$packageNames)){
                $agent['ZKFD-V4'] = array();
                $agent['ZKFD-V4']['fileName'] = "/agent/" . $name;
                $agent['ZKFD-V4']['showName'] = $name;
                $agent['ZKFD-V4']['linux_version'] = $packageNames['linux_version'];
                $agent['ZKFD-V4']['architecture'] = $packageNames['architecture'];
                continue;
            }

            //云宏kvm arm版本
            if(strpos(strtoupper($name), strtoupper('el7.aarch64'))){
                $agent['ARM-el7'] = "/agent/" . $name;
                continue;
            }

            if(strpos(strtoupper($name), strtoupper('stack-patch-cloud')) && strpos(strtoupper($name), strtoupper('el7'))){
                $agent['el7'] = "/agent/" . $name;
                $agent['stack-cloud.RHEL'] = "/agent/" . $name;
                continue;
            }

            if(strpos(strtoupper($name), strtoupper('stack-patch-cloud')) && strpos(strtoupper($name), strtoupper('el8'))){
                $agent['el8'] = "/agent/" . $name;
                continue;
            }

            if(strpos(strtoupper($name), strtoupper('stack-patch-cloud'))){
                //合并处理5.0.8的OpenStack插件,需要两次判断
                $suffixStr = substr($name, -3, 3);  //获取后缀
                if($suffixStr == "deb"){
                    $agent['stack-cloud.Ubuntu'] = "/agent/" . $name;
                }
                if($suffixStr == "rpm"){
                    $agent['stack-cloud.RHEL'] = "/agent/" . $name;
                }
                continue;
            }

            if(strpos(strtoupper($name), strtoupper('stack-patch-docker'))){
                //合并处理5.0.8的OpenStack插件,需要两次判断
                $suffixStr = substr($name, -3, 3);  //获取后缀
                if($suffixStr == "deb"){
                    $agent['stack-docker.Ubuntu'] = "/agent/" . $name;
                }
                if($suffixStr == "rpm" && strpos(strtoupper($name), strtoupper('el7'))){
                    $agent['stack-docker.RHEL'] = "/agent/" . $name;
                }
                continue;
            }
            if(in_array('RHEL6-X86_64',$packageNames)){
                $agent['Ubuntu.6'] = array();
                $agent['Ubuntu.6']['fileName'] = "/agent/" . $name;
                $agent['Ubuntu.6']['showName'] = $name;
                $agent['Ubuntu.6']['linux_version'] = $packageNames['linux_version'];
                $agent['Ubuntu.6']['architecture'] = $packageNames['architecture'];
                continue;
            }
            if(in_array('RHEL7-X86_64',$packageNames)){
                $agent['Ubuntu.7'] = array();
                $agent['Ubuntu.7']['fileName'] = "/agent/" . $name;
                $agent['Ubuntu.7']['showName'] = $name;
                $agent['Ubuntu.7']['linux_version'] = $packageNames['linux_version'];
                $agent['Ubuntu.7']['architecture'] = $packageNames['architecture'];
                continue;
            }
            if(in_array('RHEL8-X86_64',$packageNames)){
                $agent['Ubuntu.8'] = array();
                $agent['Ubuntu.8']['fileName'] = "/agent/" . $name;
                $agent['Ubuntu.8']['showName'] = $name;
                $agent['Ubuntu.8']['linux_version'] = $packageNames['linux_version'];
                $agent['Ubuntu.8']['architecture'] = $packageNames['architecture'];
                continue;
            }
            if(in_array('UBUNTU12-X86_64',$packageNames)){
                $agent['Ubuntu.12'] = array();
                $agent['Ubuntu.12']['fileName'] = "/agent/" . $name;
                $agent['Ubuntu.12']['showName'] = $name;
                $agent['Ubuntu.12']['linux_version'] = $packageNames['linux_version'];
                $agent['Ubuntu.12']['architecture'] = $packageNames['architecture'];
                continue;
            }
            if(in_array('DEBIAN7-X86_64',$packageNames)){
                $agent['Debian.7'] = array();
                $agent['Debian.7']['fileName'] = "/agent/" . $name;
                $agent['Debian.7']['showName'] = $name;
                $agent['Debian.7']['linux_version'] = $packageNames['linux_version'];
                $agent['Debian.7']['architecture'] = $packageNames['architecture'];
                continue;
            }
            if(strpos(strtoupper($name), strtoupper('whrelease'))){
                $agent['whrelease'] = "/agent/" . $name;
                continue;
            }

            if(strpos(strtoupper($name), strtoupper('wh7.5release'))){
                $agent['release'] = "/agent/" . $name;
                continue;
            }
            //add
            if(in_array('OPENEULER-aarch64',$packageNames)){
                $systems = ['EulerAARCH64', 'UNIONTECH','ZKRedFlagAARCH64'];
                foreach ($systems as $system) {
                    $agent[$system] = array();
                    $agent[$system]['fileName'] = "/agent/" . $name;
                    $agent[$system]['showName'] = $systemVendor.'-'.'backup-agent-'.$package_version.'-AGENT.'.$system.'-'.$packageNames['architecture'].'.tar'.'.gz';
                    $agent[$system]['linux_version'] = $packageNames['linux_version'];
                    $agent[$system]['architecture'] = $packageNames['architecture'];
                }
                continue;
            }
            if(in_array('REDOS7-x86_64',$packageNames)){
                $agent['REDOS'] = array();
                $agent['REDOS']['fileName'] = "/agent/" . $name;
                $agent['REDOS']['showName'] = $name;
                $agent['REDOS']['linux_version'] = $packageNames['linux_version'];
                $agent['REDOS']['architecture'] = $packageNames['architecture'];
                continue;
            }

        }
        return $agent;
    }
}
