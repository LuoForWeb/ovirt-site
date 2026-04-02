<?php

namespace app\v1\system\v0\logic;

use app\v1\common\logic\Base;
use app\v1\common\logic\JobInfo;
use app\v1\opcode\VolcdpOpcode;
use app\v1\resources\v0\logic\Node;
use app\v1\resources\v0\logic\Storage;

/**
 * note          系统设置之网络设置相关的 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/13 17:02
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Network extends Base
{
    private $cardListsKey = 'system_card_lists';

    /**
     * 获取某块网卡的信息
     * @param array $params 数据
     * @return array
     */
    public function getNetworkCardInfo($params = [])
    {

        $nodeuuid = $params['node_uuid'];
        $cardName = $params['card_name'];
        if (!in_array($cardName, xphp_get_cache($this->cardListsKey) ?? [])) {
            return false;
        }
        $cardHwaddr = $params['card_hwaddr'];

        $cardInfo = array(
            'NAME' => $cardName,
            'IPADDR' => '',
            'IPV6ADDR' => '',
            'IPV6_DEFAULTGW' => '',
            'NETMASK' => '',
            'GATEWAY' => '',
            'DNS' => '',
            'PREFIX' => '',
        );
        $cardName = explode('@', $cardName); //考虑分割挂载的情况
        $networkCard = xphp_get_config('app', 'NETWORKCARD');
        $networkCardPath = $networkCard['path'] . $networkCard['prefix'] . $cardName[0];

        //读取配置文件的信息
        $cmd = 'cat ' . $networkCardPath;
        $opName = 'NODE_SYS_OP_DO_CMD_WITH_DETAIL';
        $msg = array('command' => $cmd);
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true, false);
        if (!$mbResult['result']) {
            return false;
        }
        $info = explode(PHP_EOL, $mbResult['msg']['detail']);

        xphp_set_cache('is_rocky', 0);
        if (empty($info[0])) {
            // 那么可能是rockeylinux系统
            $networkCardPath = $networkCard['path2'] . $cardName[0] . $networkCard['prefix2'];
            //读取配置文件的信息
            $cmd = 'cat ' . $networkCardPath;
            $opName = 'NODE_SYS_OP_DO_CMD_WITH_DETAIL';
            $msg = array('command' => $cmd);
            $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true, false);
            if (!$mbResult['result']) {
                return false;
            }
            $info = explode(PHP_EOL, $mbResult['msg']['detail']);
            xphp_set_cache('is_rocky', 1);
            if (!empty($info)) {
                $ipv4 = $ipv4address = $ipv4dns = $ipv6 = $ipv6address = $ipv6dns = -1;
                $info = array_values(array_filter($info));
                // 处理组装下 以兼容其它操作系统的结构
                foreach ($info as $key => $val) {
                    if (strpos($val, 'ipv4') !== false) {
                        $ipv4 = $key;
                        continue;
                    }
                    if ($ipv4 != -1 && $ipv6 == -1) {
                        // 找出ipv4的ip和dns
                        if (strpos($val, 'address') !== false) {
                            $ipv4address = $key;
                            continue;
                        }
                        if (strpos($val, 'dns') !== false) {
                            $ipv4dns = $key;
                            continue;
                        }
                    }
                    if (strpos($val, 'ipv6') !== false) {
                        $ipv6 = $key;
                        continue;
                    }
                    if ($ipv6 != -1) {
                        // 找出ipv6的ip和dns
                        if (strpos($val, 'address') !== false) {
                            $ipv6address = $key;
                            continue;
                        }
                        if (strpos($val, 'dns') !== false) {
                            $ipv6dns = $key;
                            continue;
                        }
                    }
                }

                $isipv6 = false;
                // 先判断下是否是ipv6
                if ($ipv6 >= 0 && $ipv6address != -1) {
                    // 表示是ipv6
                    $ipaddr = $info[$ipv6address];
                    $dns = $ipv6dns ? strtoupper($info[$ipv6dns]) : '';
                    $isipv6 = true;
                } else {
                    $ipaddr = $info[$ipv4address];
                    $dns = $ipv4dns ? strtoupper($info[$ipv4dns]) : '';
                }
                // 缓存下当前网卡的配置信息，为了修改配置的时候直接读取，然后替换相应的内容即可重新写入
                xphp_set_cache('rockey_card_info', [
                    'array' => $info,
                    'ipv4' => $ipv4,
                    'ipv4_address' => $ipv4address,
                    'ipv4_dns' => $ipv4dns,
                    'ipv6' => $ipv6,
                    'ipv6_address' => $ipv6address,
                    'ipv6_dns' => $ipv6dns,
                ]);
                // 先处理四个信息值
                $dns = implode(',', array_filter(explode(';', $dns)));
                $arr = [
                    $dns
                ];
                $ipArr = explode('=', $ipaddr);
                $ipArr2 = explode(',', $ipArr[1]);
                if (!$isipv6) {
                    $ipArr3 = explode('/', $ipArr2[0]);
                    $arr[] = 'IPADDR=' . $ipArr3[0];
                    if (!empty($ipArr3[1])) {
                        // 计算出子网掩码
                        $mask = 0xffffffff << (32 - $ipArr3[1]);
                        // 返回子网掩码的点分十进制形式
                        $netmask = long2ip($mask);
                        $arr[] = 'NETMASK=' . $netmask;
                    }
                    $arr[] = 'GATEWAY=' . $ipArr2[1];
                } else {
                    $arr[] = 'IPV6ADDR=' . $ipArr2[0];
                    $arr[] = 'IPV6_DEFAULTGW=' . $ipArr2[1];
                }
                $info = $arr;
            }
        }

        //需要查找的关键字
        $keyArr = array('IPADDR', 'IPV6ADDR', 'IPV6_DEFAULTGW', 'NETMASK', 'GATEWAY', 'DNS', 'PREFIX');
        foreach ($info as $each) {
            foreach ($keyArr as $key) {
                if (stristr($each, $key)) {
                    //如果匹配到了关键字
                    $needArr = explode('=', $each);
                    //如果是DNS,特殊处理,主要是处理多个情况
                    if ('DNS' == $key) {
                        if ($key != substr($needArr[0], 0, 3)) {
                            //如果不是DNS配置,排除其他关键字带有DNS三个字符存在的情况
                            break;
                        }
                        if (empty($cardInfo[$key])) {
                            $cardInfo[$key] = $needArr[1];
                        } else {
                            $cardInfo[$key] .= ',' . $needArr[1];
                        }
                    } else {
                        //去除双引号展示
                        $cardInfo[$key] = str_replace('"', '', $needArr[1]);
                    }
                }
            }
        }
        if (!empty($cardInfo['PREFIX'])) {
            $cardInfo['NETMASK'] = $this->createNetmaskAddr(intval($cardInfo['PREFIX']));
        }
        if (!empty($cardInfo['IPV6ADDR'])) {
            // 处理下前缀 PREFIX
            $iPV6ADDR = explode('/', $cardInfo['IPV6ADDR']);
            $cardInfo['IPADDR'] = $iPV6ADDR[0];
            $cardInfo['PREFIX'] = $iPV6ADDR[1];
            $cardInfo['GATEWAY'] = $cardInfo['IPV6_DEFAULTGW'];
        }
        return $cardInfo;
    }

    /**
     * 获取所有网卡名字
     * ManoeuvreHandler->getOrchProxyVMConfig有调用
     * @param array $params 数据
     * @return array
     */
    public function getNetworkCardList($params = []): array
    {
        $nodeuuid = $params['node_uuid'];
        $setIpFlag = $params['setip_flag'];  //配置网卡信息入口标记

        //获取网卡名
        $cmd = "ip link list | awk '{if ($1 ~ \"[0-9]*:\")print $2}'";
        $opName = 'NODE_SYS_OP_DO_CMD_WITH_DETAIL';
        $msg = array('command' => $cmd);
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true, false);
        $cardName = [];
        if (!$mbResult['result']) {
            return $cardName;
        }
        //获取消息成功
        $msgDetail = $mbResult['msg']['detail'];
        $cardNameArr = explode(':' . PHP_EOL, $msgDetail);

        //获取mac
        $cmd = "ip link list | grep link|awk '{print $2}'";
        $opName = 'NODE_SYS_OP_DO_CMD_WITH_DETAIL';
        $msg = array('command' => $cmd);
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true, false);
        if (!$mbResult['result']) {
            return $cardName;
        }
        $cardHwAddrArr = explode(PHP_EOL, $mbResult['msg']['detail']);

        //获取网卡聚合信息
        $settingsHandler = new Settings();
        $data = $settingsHandler->getSettingsInfos(xphp_get_config('app', 'SETTINGS_CONF')['NIC']);
        $nicConfig = json_decode($data[0]['settings_content'], true);

        $nicList = $nicConfig['niclist'];
        $nicInfo = $nicList[$nodeuuid];
        $oldList = explode(',', $nicInfo['cardList']);
        $bondName = $nicInfo['bondname'];

        // 获取所有已经桥接了的网卡-只获取本节点的
        $birdgeArr = $this->getBridgeInfo([
            'offset' => 0,
            'limit' => 50,
            'node_uuid' => $nodeuuid
        ]);
        if (!empty($birdgeArr['rows'])) {
            $birdgeList = array_column($birdgeArr['rows'], 'child');
        } else {
            $birdgeList = [];
        }

        foreach ($cardNameArr as $key => $value) {
            if ('lo' != $value && !empty($value)) {
                $name = $value;
                if (!empty($oldList) && in_array($value, $oldList) && !empty($bondName)) {
                    $name .= '(' . $bondName . ')';
                }
                //添加了网卡聚合的网卡不在单独配置网卡信息
                if ($setIpFlag && in_array($value, $oldList)) {
                    continue;
                }

                // 添加了桥接的网卡不显示
                if (in_array($value, $birdgeList)) {
                    continue;
                }
                $cardName[] = array(
                    'name' => $name,
                    'value' => $value,
                    'hwaddr' => $cardHwAddrArr[$key]
                );
            }
        }
        // 缓存所有的网卡名称 为了设置的时候校验
        xphp_set_cache($this->cardListsKey, array_column($cardName, 'name'));

        return $cardName;
    }

    /**
     * 设置网卡信息
     * @param array $params 数据
     * @return array
     */
    public function setNetworkCardInfo($params = []): array
    {
        if (!in_array($params['NAME'], xphp_get_cache($this->cardListsKey) ?? [])) {
            return $this->muOpResult(false, xphp_get_lang('API_CODE_PARAMS_ERROR'));
        }
        if (xphp_get_cache('is_rocky')) {
            return $this->setNetworkCardInfoRocky($params);
        }

        $nodeuuid = $params['node_uuid'];
        $name = $params['NAME'];
        $ipaddrinit = $params['IPADDR'];
        $netmask = $params['NETMASK'];
        $gateway = $gatewayinit = $params['GATEWAY'];
        $dns = $params['DNS'];

        $params['TYPE'] = 'Ethernet';             //网卡类型
        $params['BOOTPROTO'] = 'static';//配置静态获取IP
        $params['ONBOOT'] = 'yes';      //开机启动
        $params['DEVICE'] = $name;
        $operate = xphp_get_lang('WEB_SYSTEM_SETTING_NETWORK') . '[' . $name . ']' .
            xphp_get_lang('WEB_SYSTEM_SETTING_IP');
        //检测系统任务状态
        $this->checkTaskStatus($operate, $nodeuuid);

        //配置文件内容
        $configFileContent = '';
        $networkCard = xphp_get_config('app', 'NETWORKCARD');
        $networkCardPath = $networkCard['path'] . $networkCard['prefix'] . $name;

        // 判断下当前的 IPADDR 是否是ipv6
        if (filter_var($params['IPADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            // 是 ipv6
            $ipaddr = $ipaddrinit = inet_ntop(inet_pton($params['IPADDR'])); // 获取缩写后的ipv6
            if (!empty($params['PREFIX'])) {
                $ipaddr .= '/' . $params['PREFIX'];
            }
            $params['IPV6ADDR'] = $ipaddr;
            $params['IPV6_DEFAULTGW'] = $gateway;
            $params['IPV6_AUTOCONF'] = 'no';

            $params['IPV6INIT'] = 'yes';
            $params['IPV6_PEERDNS'] = 'yes';
            $params['IPV6_PEERROUTES'] = 'yes';
            $params['IPV6_FAILURE_FATAL'] = 'no';
            $params['IPADDR'] = '';
            $params['GATEWAY'] = '';
            $params['NETMASK'] = '';
        } else {
            $params['IPV6ADDR'] = '';
            $params['IPV6_DEFAULTGW'] = '';
            $params['IPV6_AUTOCONF'] = 'yes';
            $params['IPV6INIT'] = 'no';
            $params['IPV6_PEERDNS'] = 'no';
            $params['IPV6_PEERROUTES'] = 'no';
            $params['IPV6_FAILURE_FATAL'] = 'yes';
        }
        $keyArr = array('NAME', 'DEVICE','IPADDR','GATEWAY', 'IPV6ADDR', 'NETMASK', 'IPV6_DEFAULTGW', 'IPV6_AUTOCONF',
            'IPV6INIT', 'IPV6_PEERDNS','IPV6_PEERROUTES','IPV6_FAILURE_FATAL', 'TYPE', 'BOOTPROTO', 'ONBOOT');

        //读取配置文件的信息
        $cmd = 'cat ' . $networkCardPath;
        $opName = 'NODE_SYS_OP_DO_CMD_WITH_DETAIL';
        $msg = array('command' => $cmd);
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true, false);
        $exitArr = array();
        if ($mbResult['result']) {
            //如果读取到了
            $info = explode(PHP_EOL, $mbResult['msg']['detail']);
            if (!empty($mbResult['msg']['detail'])) {
                //需要查找的关键字,带这些关键字的需要替换,其他的照抄
                foreach ($info as $key) {
                    $flag = true;
                    foreach ($keyArr as $each) {
                        if (stristr($key, $each)) {
                            //如果匹配到了关键字
                            $configFileContent .= $each . '=' . $params[$each] . PHP_EOL;
                            $flag = false;
                            $exitArr[] = $each;
                            break;
                        }
                    }
                    if ($flag) {
                        //添加关键字外除了DNS的选项
                        if (!empty($key) && 'DNS' != substr($key, 0, 3)) {
                            $configFileContent .= $key . PHP_EOL;
                        }
                    }
                }
                //写入剩下的
                foreach ($keyArr as $each) {
                    if (!in_array($each, $exitArr)) {
                        $configFileContent .= $each . '=' . $params[$each] . PHP_EOL;
                    }
                }
            } else {
                //如果网卡配置文件不存在,按模板写入一个配置文件
                foreach ($keyArr as $key) {
                    $configFileContent .= $key . '=' . $params[$key] . PHP_EOL;
                }
            }
        } else {
            //如果网卡配置文件不存在,按模板写入一个配置文件
            foreach ($keyArr as $key) {
                $configFileContent .= $key . '=' . $params[$key] . PHP_EOL;
            }
        }

        //最后处理DNS,添加用户配置的DNS到配置文件
        if (!empty($dns)) {
            //按逗号分隔,处理一下半角和全角标点
            $comma = ',';
            if (stristr($dns, '，')) {
                $comma = '，';
            }
            $dnsArr = explode($comma, $dns);
            $i = 1;
            foreach ($dnsArr as $each) {
                $configFileContent .= 'DNS' . $i++ . '=' . $each . PHP_EOL;
            }
        }

        //写入配置文件
        $cmd = "echo '" . $configFileContent . "'>" . $networkCardPath;
        $opName = 'NODE_SYS_OP_DO_CMD';
        $msg = array('command' => $cmd);
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true);

        $nodeHandler = new Node();
        $nodeName = $nodeHandler->getNodeName($nodeuuid);

        $this->unifyWriteSystemLog($mbResult['result'], 'SYSTEM_SETTING_IP', array($nodeName));

        if ($mbResult['result']) {
            // 重新赋值一下，保存更改生效
            $params['IPADDR'] = $ipaddrinit;
            $params['GATEWAY'] = $gatewayinit;
            //修改网卡聚合的bond网卡需要同步信息到配置
            $this->checkSaveNicInfo($params);
            //$cmd = "/usr/sbin/service network restart || /usr/sbin/service NetworkManager restart;";
            $cmd = "/usr/bin/systemctl restart network || ( /usr/bin/nmcli c reload;/usr/bin/nmcli c up {$name} );";
            //重启服务,连接要断开,无法判断,不管结果
            (new Index())->restartService($nodeuuid, $cmd);
        }

        return $this->muOpResult($mbResult['result'], $operate);
    }

    /**
     * 设置网卡信息 rocky
     * @param array $params 数据
     * @return array
     */
    public function setNetworkCardInfoRocky(array $params)
    {
        $nodeuuid = $params['node_uuid'];
        $name = $params['NAME'];
        $netmask = $params['NETMASK'];
        $gateway = $params['GATEWAY'];
        $dns = $params['DNS'];

        $operate = xphp_get_lang('WEB_SYSTEM_SETTING_NETWORK') . '[' . $name . ']' .
            xphp_get_lang('WEB_SYSTEM_SETTING_IP');
        //检测系统任务状态
        $this->checkTaskStatus($operate, $nodeuuid);

        //配置文件路径
        $networkCard = xphp_get_config('app', 'NETWORKCARD');
        $networkCardPath = $networkCard['path2'] . $name . $networkCard['prefix2'];

        $rocky = xphp_get_cache('rockey_card_info');
        if (empty($rocky['array'])) {
            $uuid = xphp_uuid();
            // 给个默认的网卡配置
            $rocky['array'] = [
                '[connection]',
                'id=' . $name,
                'uuid=' . $uuid,
                'type=ethernet',
                'autoconnect-priority=-999',
                'interface-name=' . $name,
                'timestamp=' . time(),
                '[ethernet]',
                '[ipv4]',
                'method=auto',
                '[ipv6]',
                'addr-gen-mode=eui64',
                'method=auto',
                '[proxy]',
            ];
            $rocky['ipv4'] = 8;
            $rocky['ipv6'] = 10;
        }

        // 判断下当前的 IPADDR 是否是ipv6
        if (filter_var($params['IPADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            // 是 ipv6
            $rockys = array_slice($rocky['array'], 0, $rocky['ipv4']);
            $ipaddr = inet_ntop(inet_pton($params['IPADDR'])); // 获取缩写后的ipv6
            if (!empty($params['PREFIX'])) {
                $ipaddr .= '/' . $params['PREFIX'];
            }
            $array = $rockys;
            // 清空ipv4的配置
            $array[$rocky['ipv4']] = '[ipv4]';
            $array[$rocky['ipv4'] + 1] = 'method=auto';
            $array[$rocky['ipv4'] + 2] = '[ipv6]';
            $array[$rocky['ipv4'] + 3] = 'addr-gen-mode=eui64';

            $array[$rocky['ipv4'] + 4] = 'address1=' . $ipaddr . ',' . $gateway;
            $array[$rocky['ipv4'] + 5] = 'dns=' . str_replace(',', ';', $dns) . ';';
            $array[$rocky['ipv4'] + 6] = 'method=manual';
            $array[$rocky['ipv4'] + 7] = '[proxy]';
        } else {
            // ipv4
            $rockys = array_slice($rocky['array'], 0, $rocky['ipv4']);
            // 处理下子网掩码
            if (!empty($netmask)) {
                $long = ip2long($netmask);

                $base = ip2long('255.255.255.255');
                $netmask = 32 - log(($base - $long) + 1, 2);
            }
            $array = $rockys;
            $array[$rocky['ipv4']] = '[ipv4]';
            $array[$rocky['ipv4'] + 1] = 'address1=' . $params['IPADDR'] . '/' . $netmask . ',' . $gateway;
            $array[$rocky['ipv4'] + 2] = 'dns=' . str_replace(',', ';', $dns) . ';';
            $array[$rocky['ipv4'] + 3] = 'method=manual';
            $array[$rocky['ipv4'] + 4] = '[ipv6]';
            $array[$rocky['ipv4'] + 5] = 'addr-gen-mode=eui64';
            $array[$rocky['ipv4'] + 6] = 'method=auto';
            $array[$rocky['ipv4'] + 7] = '[proxy]';
        }

        $configFileContent = implode(PHP_EOL, $array);
        //写入配置文件
        $cmd = "echo '" . $configFileContent . "'>" . $networkCardPath;
        // 更新下权限为 600，默认写入的是644
        $cmd .= ';chmod 600 ' . $networkCardPath;
        $opName = 'NODE_SYS_OP_DO_CMD';
        $msg = array('command' => $cmd);

        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true);

        $nodeHandler = new Node();
        $nodeName = $nodeHandler->getNodeName($nodeuuid);

        $this->unifyWriteSystemLog($mbResult['result'], 'SYSTEM_SETTING_IP', array($nodeName));

        if ($mbResult['result']) {
            // 重新赋值一下，保存更改生效
            //修改网卡聚合的bond网卡需要同步信息到配置
            $this->checkSaveNicInfo($params);
            // $cmd = "/usr/bin/systemctl restart NetworkManager;";
            $cmd = "nmcli connection reload;nmcli connection up {$name};";
            //重启服务,连接要断开,无法判断,不管结果
            (new Index())->restartService($nodeuuid, $cmd);
        }

        return $this->muOpResult($mbResult['result'], $operate);
    }

    /**
     * 统一写系统日志
     * @param boolean $result           bool
     * @param string  $descriptionKey   key
     * @param array   $descriptionParam desc
     * @return json
     */
    private function unifyWriteSystemLog($result, $descriptionKey, $descriptionParam = array())
    {
        if ($result) {
            $this->systemLog($descriptionKey, $descriptionParam);
        } else {
            $this->systemLog($descriptionKey, $descriptionParam, xphp_get_config('log', 'LOGLEVEL')['WARN']);
        }
    }

    /**
     * 检查如果存在网卡聚合信息同步信息
     * @param unknown $params 数据
     * @return boolean
     */
    private function checkSaveNicInfo($params)
    {
        $nodeuuid = $params['node_uuid'];
        $networkName = $params['NAME'];
        $ipaddr = $params['IPADDR'];
        $netmask = $params['NETMASK'];
        $gateway = $params['GATEWAY'];
        $dns = $params['DNS'];
        //获取网卡聚合信息
        $settingsHandler = new Settings();
        $data = $settingsHandler->getSettingsInfos(xphp_get_config('app', 'SETTINGS_CONF')['NIC']);
        $nicConfig = json_decode($data[0]['settings_content'], true);
        $nicList = $nicConfig['niclist'];
        $nicinfo = $nicList[$nodeuuid];

        if (!empty($info)) {
            //找到网卡聚合的网卡同步信息
            if ($networkName == $nicinfo['bondname']) {
                $info = array(
                    'node_uuid' => $nodeuuid,
                    'nicipaddr' => $ipaddr,
                    'nicnetmask' => $netmask,
                    'nicgateway' => $gateway,
                    'nicdns' => $dns,
                    'nicType' => $nicinfo['nictype'],
                    'cardList' => $nicinfo['cardList'],
                    'num' => $nicinfo['num'],
                    'bondname' => $nicinfo['bondname']
                );
                //保存网卡聚合信息
                $this->saveNicInfo($info);
            }
        }

        return true;
    }

    /**
     * 保存各个节点网卡聚合配置信息
     * @param unknown $params 数据
     * @return number
     */
    public function saveNicInfo($params)
    {
        $nodeuuid = $params['node_uuid'];
        $nicType = $params['nicType'];
        $cardList = $params['cardList'];

        $ipaddr = $params['nicipaddr'];
        $netmask = $params['nicnetmask'];
        $gateway = $params['nicgateway'];
        $dns = $params['nicdns'];
        $prefix = $params['prefix'];
        $num = $params['num'];
        $bondName = $params['bondname'];
        //获取网卡聚合信息
        $settingsHandler = new Settings();
        $data = $settingsHandler->getSettingsInfos(xphp_get_config('app', 'SETTINGS_CONF')['NIC']);
        $nicList = json_decode($data[0]['settings_content'], true);
        $list = $nicList['niclist'];
        $newList = array();
        //如果信息为空初始化数组
        if (!empty($list)) {
            foreach ($list as $key => $l) {
                if ($key != $nodeuuid) {
                    $newList[$key] = $l;
                }
            }
        }
        $info = array(
            'ipaddr' => $ipaddr,
            'netmask' => $netmask,
            'gateway' => $gateway,
            'dns' => $dns,
            'prefix' => $prefix,
            'nictype' => $nicType,
            'cardList' => implode(',', $cardList),
            'num' => intval($num),
            'bondname' => $bondName
        );
        $newList[$nodeuuid] = $info;
        $nicInfo = array(
            'niclist' => $newList
        );
        return $settingsHandler->modifySettingsInfosWithType(
            xphp_get_config('app', 'SETTINGS_CONF')['NIC'],
            json_encode($nicInfo)
        );
    }

    /**
     * 获取节点的hosts文件信息
     * @param array $params 数据
     * @return string
     */
    public function getNodeDnsHosts($params = [])
    {
        $nodeuuid = $params['node_uuid'];

        $opName = 'NODE_SYS_OP_DO_CMD_WITH_DETAIL';
        $cmd = 'cat /etc/hosts';
        $msg = array(
            'command' => $cmd
        );
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true, false);
        $msg = '';
        if ($mbResult['result']) {
            $detail = $mbResult['msg']['detail'];
            $detailArr = explode(PHP_EOL, $detail);
            foreach ($detailArr as $d) {
                //过滤空行
                $d = trim($d);
                if (empty($d)) {
                    continue;
                }
                //过滤127.0.0.1和::1
                if (false === strpos($d, '127.0.0.1') && false === strpos($d, '::1')) {
                    $msg .= $d . PHP_EOL;
                }
            }
            return $msg;
        } else {
            return '';
        }
    }

    /**
     * 配置节点hosts文件信息
     * @param array $params 数据
     * @return json
     */
    public function setNodeDnsHosts($params = [])
    {
        $nodeuuid = $params['node_uuid'];
        $setting = $params['setting'];
        $syncnode = $params['syncnode'];

        $settingNodes = [];
        if ($syncnode) {
            //如果是同步所有节点
            //先得到所有在线节点,然后再一次同步设置
            $nodeHandler = new Node();
            $nodes = $nodeHandler->getAddStorageNodeSelect();
            foreach ($nodes as $node) {
                $settingNodes[] = $node['uuid'];
            }

            //同步所有proxy
            $this->setAllProxyDns($setting);
        } else {
            $settingNodes[] = $nodeuuid;
        }

        return $this->setNodesDnsHosts($settingNodes, $setting);
    }

    /**
     * 获取网卡聚合配置信息
     * @param array $params 数据
     * @return string
     */
    public function getNicOldInfo($params = [])
    {
        $nodeuuid = $params['node_uuid'];
//         $filePath = Xphp::$_config['NIC_INFO_DIR'];
        //获取网卡聚合信息
        $settingsHandler = new Settings();
        $data = $settingsHandler->getSettingsInfos(xphp_get_config('app', 'SETTINGS_CONF')['NIC']);
        $nicConfig = json_decode($data[0]['settings_content'], true);
        $nicList = $nicConfig['niclist'];
        $info = array();
        $nicInfo = $nicList[$nodeuuid];
        if (!empty($nicInfo)) {
            $info = array(
                'ipaddr' => $nicInfo['ipaddr'],
                'netmask' => $nicInfo['netmask'],
                'gateway' => $nicInfo['gateway'],
                'dns' => $nicInfo['dns'],
                'prefix' => $nicInfo['prefix'],
                'nictype' => intval($nicInfo['nictype']),
                'cardList' => explode(',', $nicInfo['cardList'])
            );
        }

        return $info;
    }

    /**
     * 清除网卡聚合信息
     * @param array $params 数据
     * @return string
     */
    public function cleanNicInfo($params = [])
    {
        $nodeuuid = $params['node_uuid'];
        //获取网卡聚合信息
        $settingsHandler = new Settings();
        $data = $settingsHandler->getSettingsInfos(xphp_get_config('app', 'SETTINGS_CONF')['NIC']);
        $nicConfig = json_decode($data[0]['settings_content'], true);
        $nicList = $nicConfig['niclist'];

        $nicInfo = $nicList[$nodeuuid];
        if (empty($nicInfo)) {
            return [
                false,
                xphp_get_lang('UI_PLATFORM_CLEAR_CARD_INFO'),
                xphp_get_lang('UI_PLATFORM_NODE_NOT_ADD_CARD'),
                'warning'
            ];
        }

        //检测系统任务状态
        $this->checkTaskStatus(xphp_get_lang('UI_PLATFORM_CLEAR_CARD_INFO'), $nodeuuid);

        $reList = explode(',', $nicInfo['cardList']);
        $bondName = $nicInfo['bondname'];

        $newList = array();
        //如果信息为空初始化数组
        if (!empty($nicList)) {
            foreach ($nicList as $key => $l) {
                if ($key != $nodeuuid) {
                    $newList[$key] = $l;
                }
            }
        }
        $nicInfo = array(
            'niclist' => $newList
        );

        $cmd = '';
        $networkCard = xphp_get_config('app', 'NETWORKCARD');
        if (xphp_get_cache('is_rocky')) {
            $path1 = $networkCard['path2'] . $bondName . $networkCard['prefix2'];
            $cmd .= 'rm -rf ' . $path1 . ';';
        } else {
            $cmd .= 'rm -rf ' . $networkCard['path'] . $networkCard['prefix'] . $bondName . ';';
        }
        foreach ($reList as $n) {
            if (xphp_get_cache('is_rocky')) {
                $cmd .= 'rm -rf ' . $networkCard['path2'] . 'bond-slave-' . $n . $networkCard['prefix2'] . ';';
                $cmd .= 'mv ' . $networkCard['path2'] . 'old/' . $n .  $networkCard['prefix2'] .
                    ' ' . $networkCard['path2'] . ';';
            } else {
                $cmd .= 'rm -rf ' . $networkCard['path'] . $networkCard['prefix'] . 'bond-slave-' . $n . ';';
                $cmd .= 'mv ' . $networkCard['path'] . 'old/' . $networkCard['prefix'] . $n .
                    ' ' . $networkCard['path'] . ';';
            }
        }

        //清除聚合网卡信息
        $cmd .= 'nmcli c d ' . $bondName . ';';
        //重启网络
        $cmd .= 'nmcli c r;systemctl restart network;';
        $msg = array(
            'command' => $cmd
        );

        $opName = 'NODE_SYS_OP_DO_CMD';
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), false, false);
        $result = $mbResult['result'];

        if ($result) {
            //保存新的信息
            $settingsHandler->modifySettingsInfosWithType(
                xphp_get_config('app', 'SETTINGS_CONF')['NIC'],
                json_encode($nicInfo)
            );
            return [true, xphp_get_lang('UI_PLATFORM_CLEAR_CARD_INFO')];
        } else {
            return [false, xphp_get_lang('UI_PLATFORM_CLEAR_CARD_INFO'), '', 'warning'];
        }
    }

    /**
     * 添加网卡聚合
     * @param array $params 数据
     * @return string
     */
    public function addNicTeaming($params = [])
    {
        $nodeuuid = $params['node_uuid'];
        $nicType = $params['nicType'];
        $cardList = $params['cardList'];
        $ipv4Flag = $params['ipv4flag'];

        $ipaddr = $params['nicipaddr'];
        $netmask = $params['nicnetmask'];
        $gateway = $params['nicgateway'];
        $dns = $params['nicdns'];

        $params['num'] = 0;
        $params['bondname'] = 'bond0';
        //保存配置文件
        $this->saveNicInfo($params);

        //1.生成Bond网卡
        $cmd = '';
        $cmd .= 'nmcli c add type bond con-name bond0 ifname bond0 mode ' . $nicType . ' miimon 100;';
        if ($ipv4Flag) {
            $netmaskmap = $this->getNetmask($netmask);
            //网卡信息
            $cmd .= 'nmcli c modify bond0 ipv4.method manual autoconnect yes ipv4.addresses ' . $ipaddr;
            if (!empty($netmaskmap)) {
                $cmd .= '/' . $netmaskmap;
            }
            if (!empty($gateway)) {
                $cmd .= ' ipv4.gateway ' . $gateway;
            }
            if (!empty($dns)) {
                $cmd .= ' ipv4.dns ' . $dns;
            }
            $cmd .= ';';
        } else {
            $netmaskmap = $params['prefix'];
            //网卡信息
            $cmd .= 'nmcli c modify bond0 ipv6.method manual autoconnect yes ipv6.addresses ' . $ipaddr;
            if (!empty($netmaskmap)) {
                $cmd .= '/' . $netmaskmap;
            }
            if (!empty($gateway)) {
                $cmd .= ' ipv6.gateway ' . $gateway;
            }
            if (!empty($dns)) {
                $cmd .= ' ipv6.dns ' . $dns;
            }
            $cmd .= ';';
        }
        $networkCard = xphp_get_config('app', 'NETWORKCARD');
        foreach ($cardList as $card) {
            //2.保存网卡信息
            if (xphp_get_cache('is_rocky')) {
                // 先判断文件夹old是否存在
                if (!is_dir($networkCard['path2'] . 'old')) {
                    $cmd .= 'mkdir -m 755 ' . $networkCard['path2'] . 'old;';
                }
                $path1 = $networkCard['path2'] . $card . $networkCard['prefix2'];
                $path2 = $networkCard['path2'] . 'old;';
                $cmd .= 'mv ' . $path1 . ' ' . $path2;
            } else {
                $cmd .= 'mv ' . $networkCard['path'] . $networkCard['prefix'] . $card .
                    ' ' . $networkCard['path'] . 'old;';
            }
            //3.bond绑定网卡
            $cmd .= 'nmcli c add type bond-slave ifname ' . $card . ' master bond0;';
        }

        //3.重载网络
        $cmd .= 'nmcli c reload;systemctl restart network;';

        $msg = array(
            'command' => $cmd
        );
        $opName = 'NODE_SYS_OP_DO_CMD';
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), false, false);

        $this->unifyWriteSystemLog($mbResult['result'], 'SYSTEM_LOG_SETTINGS_NIC');
        $operate = xphp_get_lang('UI_PLATFORM_NIC_TEAMING');
        if ($mbResult['result']) {
            // 这里因为要重启后台所有服务，无法获取最终执行命令结果。所以不判断了
            (new Index())->restartService($nodeuuid);

            return [true, $operate];
        } else {
            return [false, $operate, '', 'warning'];
        }
    }

    /**
     * 获取网卡桥接信息
     * @param array $params 数据
     * @return array
     */
    public function getBridgeInfo($params = [])
    {
        $key = $this->cardListsKey . '_bridge'; // 缓存的key
        $sql = 'select node_uuid,ip,host_name from bd_node';
        if (!empty($params['node_uuid'])) {
            // 存在传入的节点
            $nodeUuid = $params['node_uuid'];
            $key .= '_' . $nodeUuid;
            $sql .= " where node_uuid = '{$nodeUuid}'";
        }

        if (($params['offset'] != 0 || !empty($params['search']))  && !empty(cache($key))) {
            // 不是第一页或者有搜索 并且缓存存在
            return $this->getNewArray(cache($key), $params['offset'], $params['limit'], $params['search'], 'name');
        }

        //1，先查询出所有的在线节点列表，然后轮询去请求后台的接口
        $node = $this->dbSelect($sql);
        $return = $nodeCard = [];
        $i = 0;
        $nodes = new Node();
        foreach ($node as $item) {
            $nodeStatus = $nodes->getNodeAllStatus($item['node_uuid']);
            if (empty($nodeStatus['flag'])) {
                continue;
            }
            $returnsArr = $this->service()->mbTempAgentMsgs(
                [],
                'TEMP_AGENT_OP_GET_ALL_NET_DEVICE_INFO',
                $item['node_uuid'],
                true
            );

            if ($returnsArr['result'] && !empty($returnsArr['msg']['detail'])) {
                // 查询出这个节点下面的已经使用了的桥接网卡
                // bd_emd_network 表里 node_uuid 查询出 bridge_name
                $usedList = $this->dbSelect(
                    "select bridge_name from bd_emd_network
                        where bridge_name != '' and node_uuid ='" . $item['node_uuid'] . "'"
                );
                $userArr = array_column($usedList, 'bridge_name');

                $returns = json_decode($returnsArr['msg']['detail'], true);
                $node = $item['host_name'] . "({$item['ip']})";
                $nicType = xphp_get_config('tempagent', 'DEVICE_TYPE_NIC');
                $ipType = xphp_get_config('tempagent', 'IP_TYPE');
                $nullSpace = xphp_get_config('app', 'NULLSPACE');
                foreach ($returns as $items) {
                    if (
                        $items['type'] != $nicType['NIC_DEVICE_TYPE_BRIDGE'] ||
                        strpos($items['name'], 'virbr') !== false
                    ) {
                        // 只显示桥接网卡
                        continue;
                    }
                    $i++;
                    $child = implode(',', $items['sub_device'] ?? []);
                    // $ipaddr = implode(',', array_filter(array_column($items['ip_set'] ?? [], 'ip_addr')));
                    // 只显示ipv4的地址
                    $ipaddr = [];
                    foreach ($items['ip_set'] as $item2) {
                        if ($item2['ip_type'] == $ipType['NET_IP_TYPE_V4']) {
                            $ipaddr[] = $item2['ip_addr'];
                        }
                    }
                    $return[] = [
                        'flag' => in_array($items['name'], $userArr),
                        'num' => $i,
                        'uuid' => $items['name'] . '-!!-' . $item['node_uuid'] . '-!!-' . $child,
                        'name' => $items['name'],
                        'child' => empty($child) ? $nullSpace : $child,
                        'ipaddr' => implode(',', $ipaddr),
                        'gateaway' => $items['gateway_address'],
                        'node' => $node
                    ];
                    // 记录一下已经添加了的网卡桥接
                    $nodeCard[$item['node_uuid']][] = [
                        'name' => $items['name'],
                        'child' => $items['sub_device'],
                    ];
                }
            }
        }
        cache($key, $return); // 缓存网卡列表，方便搜索和翻页
        cache($key . '_node', $nodeCard); // 缓存下已经添加了的网卡和名称
        return $this->getNewArray($return, $params['offset'], $params['limit'], $params['search'], 'name');
    }

    /**
     * 清除网卡桥接信息
     * @param array $params 数据
     * @return array
     */
    public function cleanBridge($params = [])
    {

        // 根据uuid进行解析处理
        $uuidArr = explode('-!!-', $params['uuids'][0]);
        $name = $uuidArr[0];
        $nodeUuid = $uuidArr[1];
        // 物理网卡可能是数组
        $card = explode(',', $uuidArr[2]);

        $opName = 'TEMP_AGENT_OP_DELETE_BRIDGE';
        // 发送消息给后台
        $msg = [
            'name' => $name,
            'slaves' => $card
        ];
        $return = $this->service()->mbTempAgentMsgs($msg, $opName, $nodeUuid);

        if ($return['result']) {
            //成功
            return true;
        } else {
            $volcdpOpcode = new VolcdpOpcode();
            $operate = $volcdpOpcode->getOpcodeDes($opName);
            return $this->muOpResult(false, $operate, '', '', $return['errorCode']);
        }
    }

    /**
     * 添加网卡桥接
     * @param array $params 数据
     * @return array
     */
    public function addBridge($params = [])
    {

        // 这里有个重复校验
        $key = $this->cardListsKey . '_bridge_node'; // 缓存的key
        $nodeCard = cache($key);
        if (!empty($nodeCard) && !empty($nodeCard[$params['node_uuid']])) {
            $array = $nodeCard[$params['node_uuid']];
            if (in_array($params['name'], array_column($array, 'name'))) {
                // 名称重复
                return [
                    'code' => 1,
                    'msg' => xphp_get_lang('UI_PLATFORM_CARD_BRIDEG_ADD_NAME_EXISTS')
                ];
            }
            $cardArr = array_merge(...array_map(function ($item) {
                return $item['child'];
            }, $array));
            if (!empty($params['card']) && in_array($params['card'], $cardArr)) {
                // 网卡重复
                return [
                    'code' => 1,
                    'msg' => xphp_get_lang('UI_PLATFORM_CARD_BRIDEG_ADD_CARD_EXISTS')
                ];
            }
        }
        $opName = 'TEMP_AGENT_OP_CREATE_BRIDGE';
        $params['card'] = empty($params['card']) ? [] : [$params['card']];
        // 发送消息给后台
        $msg = [
            'name' => $params['name'],
            'slaves' => $params['card']
        ];
        $return = $this->service()->mbTempAgentMsgs($msg, $opName, $params['node_uuid']);

        if ($return['result']) {
            //成功
            return [
                'code' => 0,
                'msg' => xphp_get_lang('UI_PLATFORM_CARD_BRIDEG_ADD_SUCCESS')
            ];
        } else {
            $volcdpOpcode = new VolcdpOpcode();
            $operate = $volcdpOpcode->getOpcodeDes($opName);
            return $this->muOpResult(false, $operate, '', '', $return['errorCode']);
        }
    }

    /**
     * 获取隔离网段配置
     * @param array $params 数据
     * @return array
     */
    public function getIsolate($params = [])
    {

        $isolateNetworkArr = xphp_get_config('tempagent', 'ISOLATE_NETWORK_USED');
        $start = $isolateNetworkArr['EMD_ISOLATE_NETWORK_USED_FOR_RANGE_START'];
        $end = $isolateNetworkArr['EMD_ISOLATE_NETWORK_USED_FOR_RANGE_END'];

        // bd_emd_isolate_network 表里的 use_type in 1 2 里面的ip和mask字段
        $sql = "select ip,mask,use_type from bd_emd_isolate_network
                    where use_type in ({$start}, {$end})";

        $db = $this->dbSelect($sql);
        $return = [
            'ip_start' => '',
            'ip_end' => '',
            'ip_mask' => '',
        ];
        if (!empty($db)) {
            foreach ($db as $item) {
                if ($item['use_type'] == $start) {
                    $return['ip_start'] = $item['ip'];
                } else {
                    $return['ip_end'] = $item['ip'];
                    $return['ip_mask'] = $item['mask'];
                }
            }
        }
        return $return;
    }

    /**
     * 保存隔离网段配置
     * @param array $params 数据
     * @return bool|string
     */
    public function saveIsolate($params = [])
    {

        $opName = 'TEMP_AGENT_OP_EDIT_ISOLATE_NETWORK_SEG';
        // 发送消息给后台
        $msg = [
            'isolate_network_ip_start' => $params['ip_start'],
            'isolate_network_ip_end' => $params['ip_end'],
            'isolate_network_ip_mask' => $params['ip_mask'],
        ];
        $nodeuuid = (new Node())->getLocalNodeUUID();
        $return = $this->service()->mbTempAgentMsgs($msg, $opName, $nodeuuid);

        if ($return['result']) {
            //成功
            // 还要给所有在线的节点发送一个消息
            $nodes = $this->dbSelect('select node_uuid from bd_node');
            $nodec = new Node();
            foreach ($nodes as $node) {
                $status = $nodec->getNodeStatus($node['node_uuid']);
                if (!empty($status['online_flag'])) {
                    $opName = 'EMD_VM_OP_RECONFIG_ISOLATE_BRIDGE';
                    $this->service()->mbTempAgentMsgs([], $opName, $node['node_uuid']);
                }
            }
            return true;
        } else {
            $volcdpOpcode = new VolcdpOpcode();
            $operate = $volcdpOpcode->getOpcodeDes($opName);
            return $this->muOpResult(false, $operate, '', '', $return['errorCode']);
        }
    }

    /**
     * 返回指定的二维数组的新数组
     * @param array  $array   数组
     * @param int    $offset  起始数
     * @param int    $limit   数组长度
     * @param string $keyword 关键词
     * @param string $field   什么字段搜索
     * @return array
     */
    private function getNewArray($array = [], $offset = 0, $limit = 10, $keyword = '', $field = '')
    {
        if (empty($array)) {
            return $array;
        }
        if (!empty($keyword) && !empty($field)) {
            $new = [];
            foreach ($array as $item) {
                if (strpos($item[$field], $keyword) !== false) {
                    $new[] = $item;
                }
            }
            $array = $new;
        }
        $total = count($array);
        if ($total < $offset) {
            return [];
        }
        $end = min($total - $offset, $limit);
        $return = [];
        for ($i = 0; $i < $end; $i++) {
            $return[] = $array[$i + $offset];
        }
        return [
            'rows' => $return,
            'total' => $total
        ];
    }

    /**
     * ip段转换为掩码位
     * @param $tmpnetmask ip
     * @return int
     */
    private function getNetmask($tmpnetmask)
    {
        $turn = explode('.', $tmpnetmask);
        $shimask = '';
        for ($i = 0; $i < 4; $i++) {
            $shimask .= decbin($turn[$i]);
        }
        $mask = substr_count($shimask, '1');
        return $mask;
    }

    /**
     * 同步所有proxy DNS
     * @param string $settings 奢姿
     * @return boolean
     */
    private function setAllProxyDns($settings)
    {
        $setting = $this->groupDnsSetting($settings);

        $sql = "select agent_uuid from bd_agent where node_uuid = '' and agent_type = 4";
        $data = $this->dbSelect($sql);
        if (!empty($data)) {
            $nodeHandler = new Node();
            $nodeuuid = $nodeHandler->getLocalNodeUUID();
            foreach ($data as $d) {
                $msg = array(
                    'agent_uuid' => $d['agent_uuid'],
                    'domain_config' => $setting,
                );
                $opName = 'NODE_AGENT_OP_MODIFY_DOMAIN_CONFIG';
                // $operate = $storageHandler->getUnifyOpcodeDes($opName);
                $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg));
            }
        }

        return true;
    }

    /**
     * 配置多个节点的hosts文件信息
     * @param array  $settingNodes 多个节点uuid
     * @param string $setting      设置
     * @return array
     */
    private function setNodesDnsHosts($settingNodes, $setting)
    {
        $setting = $this->groupDnsSetting($setting);
        $opName = 'NODE_SYS_OP_DO_CMD';
        $cmd = "echo '" . $setting . "' > /etc/hosts;mkdir -p /lib/backupsystem.host.conf;echo '" .
            $setting . "' > /lib/backupsystem.host.conf/hosts";
        $msg = array(
            'command' => $cmd
        );
        $result = true;
        foreach ($settingNodes as $nodeuuid) {
            $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true, false);
            $result = $result & $mbResult['result'];
        }

        $this->unifyWriteSystemLog($result, 'SYSTEM_LOG_SETTINGS_DNS');
        return [$result, xphp_get_lang('UI_SETTINGS_DNS_SETTING')];
    }

    /**
     * 根据用户配置组合最终的hosts配置文件
     * @param string $setting 设置
     * @return string
     */
    private function groupDnsSetting($setting)
    {
        $settingHeader = '127.0.0.1   localhost localhost.localdomain localhost4 localhost4.localdomain4' . PHP_EOL;
        $settingHeader .= '::1         localhost localhost.localdomain localhost6 localhost6.localdomain6' . PHP_EOL;
        return $settingHeader . $setting;
    }

    /**
     * 还原踢出聚合的网卡信息
     * @param  $reList   old
     * @param  $nodeuuid new
     * @param  $bondName new
     * @return boolean
     */
    private function reSaveCardInfo($reList, $nodeuuid, $bondName = ''): bool
    {
        if (empty($reList)) {
            return true;
        }
        //还原
        $nerworkcard = xphp_get_config('app', 'NETWORKCARD');
        foreach ($reList as $card) {
            $cardDes .= $card . ' ';

            $cardPath = $nerworkcard['path'] . $nerworkcard['prefix'] . $card;
            $cardPathCopy = $nerworkcard['path'] . $nerworkcard['prefix'] . $card . '.old';

            $opName = 'NODE_SYS_OP_DO_CMD_WITH_DETAIL';
            $cardcmd = '[ -f ' . $cardPath . ' ] && echo yes || echo no;';
            $cardcmd .= '[ -f ' . $cardPathCopy . ' ] && echo yes || echo no';
            $msg = array(
                'command' => $cardcmd
            );
            $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true, false);
            if ($mbResult['result']) {
                $fileFlag = explode("\n", trim($mbResult['msg']['detail']));
                $cmd = '';
                //如果网卡存在旧的文件，把现在文件删除, 把old还原
                if ($fileFlag[0] == 'yes' && $fileFlag[1] == 'yes') {
                    $cmd .= 'rm -rf ' . $cardPath . ';';
                    $cmd .= 'mv ' . $cardPathCopy . ' ' . $cardPath;
                } elseif ($fileFlag[0] == 'yes' && $fileFlag[1] == 'no') {
                    if ($card == $bondName) {
                        //直接清除聚合网卡
                        $cmd .= 'rm -rf ' . $cardPath . ';';
                    } else {
                        //如果只存在网卡文件把绑定聚合信息移除
                        $content = 'DEVICE=' . $card . PHP_EOL . 'ONBOOT=yes';
                        $cmd .= "echo '" . $content . "' > " . $cardPath;
                    }
                }
                $msg = array(
                    'command' => $cmd
                );
                $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true, false);
            }
        }
        return true;
    }

    /**
     * 掩码位转换成子网掩码ip
     * @param int $bitcount 掩码位
     * @return string
     */
    private function createNetmaskAddr(int $bitcount): string
    {

        $netmask = str_split(str_pad(str_pad('', $bitcount, '1'), 32, '0'), 8);

        foreach ($netmask as &$element) {
            $element = bindec($element);
        }

        return join('.', $netmask);
    }

    /**
     * 检测任务状态
     * 有运行中的任务和瞬时恢复的任务时返回失败
     * @param string $operate  操作码
     * @param string $nodeuuid 节点
     * @return boolean|string
     */
    public function checkTaskStatus(string $operate, $nodeuuid = '')
    {
        $taskStatus = xphp_get_config('task', 'TASKSTATUS');
        $moduleType = xphp_get_config('module', 'MODULE_TYPE');
        $flag = xphp_get_config('app', 'FLAG');
        if (!empty($nodeuuid)) {
            $where = " and node_uuid = '{$nodeuuid}'";
        } else {
            $where = '';
        }
        $sql = "select id from bd_task where task_status = ?  and delete_flag = ?";
        $sqlParams = array($taskStatus['RUNNING'], $flag['UNSET']);
        $data = $this->dbSelect($sql . $where, $sqlParams);
        if ($data[0]) {
            return $this->muOpResult(false, $operate, xphp_get_lang('WEB_SYSTEM_SETTING_IP_HAS_TASK'), 'warning');
        }
        // 卷cdp的成功状态任务也不能修改
        $sql = "select id from bd_task where module_type = ? and task_status = ?  and delete_flag = ?";
        $sqlParams = array($moduleType['VOL_CDP'], $taskStatus['SUCCESSED'], $flag['UNSET']);
        $data = $this->dbSelect($sql . $where, $sqlParams);
        if ($data[0]) {
            return $this->muOpResult(false, $operate, xphp_get_lang('WEB_SYSTEM_SETTING_IP_HAS_TASK'), 'warning');
        }
        return true;
    }
}
