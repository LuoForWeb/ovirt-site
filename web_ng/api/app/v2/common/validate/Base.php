<?php

namespace app\v2\common\validate;

use xphp\Validate;

/**
 * note          基础的验证器
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/2 16:27
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Base extends Validate
{
    protected $lang;
    protected $title;

    protected $rule = [];

    protected $message = [];

    protected $scene = [];

    /**
     * 构造方法
     */
    public function __construct()
    {
        // 定义的语言包 不够后续再加类型
        $this->lang = [
            'require' => xphp_get_lang('WEB_OPHANDLER_PARAMS_NULL'), // 参数不能为空
            'number' => xphp_get_lang('WEB_OPHANDLER_PARAMS_NUMBER'), // 参数必须为数字
            'regex' => xphp_get_lang('WEB_OPHANDLER_PARAMS_TYPE'), // 参数类型不符合
            'max' => xphp_get_lang('WEB_OPHANDLER_PARAMS_LENGTH'), // 参数长度不对
            'min' => xphp_get_lang('WEB_OPHANDLER_PARAMS_LENGTH'), // 参数长度不对
            'between' => xphp_get_lang('WEB_OPHANDLER_PARAMS_LENGTH'), // 参数长度不对
            'length' => xphp_get_lang('WEB_OPHANDLER_PARAMS_LENGTH'), // 参数长度不对
            'email' => xphp_get_lang('WEB_OPHANDLER_PARAMS_EMAIL'), // 邮箱格式不对
            'mobile' => xphp_get_lang('WEB_OPHANDLER_PARAMS_TYPE'), // 手机格式不对
            'array' => xphp_get_lang('WEB_OPHANDLER_PARAMS_TYPE'), // 数组格式不对
            'ip' => xphp_get_lang('WEB_OPHANDLER_PARAMS_TYPE'), // ip格式不对
            'idCard' => xphp_get_lang('WEB_OPHANDLER_PARAMS_TYPE'), // idCard格式不对
            'gt' => xphp_get_lang('WEB_OPHANDLER_PARAMS_GT_VALUE'),//参数小于可用范围
            'lt' => xphp_get_lang('WEB_OPHANDLER_PARAMS_LT_VALUE'),//参数大于可用范围
            'integer' => xphp_get_lang('WEB_OPHANDLER_PARAMS_INTEGER'), //参数必须是整数
        ];
    }

    // phpcs:disable
    /**
     * 封装规则返回提示信息
     * @param array $rule 规则
     * @return array
     */
    protected function make_message($rule = [])
    {
        if (empty($rule)) {
            return false;
        }
        $debug = getEnvs();
        $message = [];
        foreach ($rule as $key => $val) {
            if (is_string($val)) {
                $message[$key][$val] = ($debug ? $key : '') . $this->lang[$val];
            }
            if (is_array($val)) {
                foreach ($val as $key2 => $val2) {
                    if (is_numeric($key2)) {
                        $message[$key][$val2] = ($debug ? $key : '') . $this->lang[$val2];
                    } else {
                        $message[$key][$key2] = ($debug ? $key : '') . $this->lang[$key2];
                    }
                }
            }
        }
        return $message;
    }
    // phpcs:enable

    /**
     * 验证限速策略
     * @author JackC
     * @description 限速策略验证，用于创建(修改)备份(恢复)任务
     * @see \app\v2\db\v0\validate\DbBackUp
     * @see \app\v2\db\v0\validate\DbRecover
     * @param array  $speedStrategy 限速策略
     * @param string $rule          验证规则
     * @param array  $data          全部请求数据
     * @param string $field         字段名
     * @paramExample
     * {
     *   "level": "1",
     *   "type": 2,
     *   "speed": [
     *     {
     *       "mode": 1,
     *       "type": 2,
     *       "uuid": "8682970e091aeb7e37586e093a87b2b965fb",
     *       "speedUnit": 1048576,
     *       "speedNum": 10,
     *       "value": 10485760,
     *       "unit": "MB/s",
     *       "startTime": "23:00:00",
     *       "endTime": "23:30:00",
     *       "days": [0, 0, 0, 0, 1, 0, 0],
     *       "des": "限速策略 (每周5, 23:00:00开始, 23:30:00结束), 限速大小:10MB/s"
     *     },
     *   "uuid": "",
     *   "name": "",
     *   "strategy_type": 1
     *   ]
     * }
     * @return bool|string
     */
    protected function checkSpeedStrategy(array $speedStrategy, string $rule, array $data, string $field)
    {
        if (!isset($speedStrategy['type'])) {  // 限速类别【1全局限速策略 2自定义限速策略】
            return true;  // 可以不配置限速策略
        }
        if ($speedStrategy['type'] == 1) {  // 全局限速策略
            if (!isset($speedStrategy['level'])) {
                if (!isset($item['type'])) {
                    return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'level');
                }
            }
        } else {  // 自定义限速策略
            if (!isset($speedStrategy['speed']) || !is_array($speedStrategy['speed'])) {  // 可以不配置限速策略
                return true;
            }
            foreach ($speedStrategy['speed'] as $index => $item) {
                if (!is_array($item)) {
                    return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT'), $field . 'speed' . "[$index]");
                }
                $ret = $this->checkSpeedStrategyItem($item);
                if (true !== $ret) {
                    return sprintf($ret, $field . "[$index]");
                }
            }
        }
        return true;
    }

    /**
     * 验证限速策略-单个策略
     * @param array $item 单个策略
     * @return bool|string
     */
    private function checkSpeedStrategyItem(array $item)
    {
        if (!isset($item['type'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'type');
        }

        if (!isset($item['mode'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'mode');
        }

        if (!isset($item['speedUnit'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'speedUnit');
        }

        if (!isset($item['speedNum'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'speedNum');
        }

        if (!isset($item['value'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'value');
        }

        if (!isset($item['unit'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'unit');
        }

        if (!isset($item['startTime'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'startTime');
        }

        if (!isset($item['endTime'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'startTime');
        }

        if (!isset($item['days'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'startTime');
        }

        if (!isset($item['des'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'startTime');
        }
        return true;
    }

    /**
     * 将一天的时间转成秒
     * @param string $dayTime 一天的时间H:i:s
     * @return int
     */
    protected function convertDayTimeToSecond(string $dayTime): int
    {
        $times = explode(':', $dayTime);
        return $times[0] * 3600 + $times[1] * 60 + $times[2];
    }

    /**
     * 验证ip地址(ipv4和ipv6)
     * @param ?string $ip    ipv4或ipv6
     * @param string  $rules 规则
     * @param array   $data  全部数据
     * @param string  $field 验证字段名
     * @return bool|string
     */
    protected function validateIp(?string $ip, string $rules, array $data, string $field)
    {
        if (false === $this->filter($ip, [FILTER_VALIDATE_IP])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_IP'), $field);
        }
        return true;
    }

    /**
     * 验证ip地址(ipv4和ipv6)或域名
     * @param ?string $ip    ipv4或ipv6
     * @param string $rules 规则
     * @param array $data 全部数据
     * @param string  $field 验证字段名
     * @return bool|string
     */
    protected function validateIpDomain(?string $ip, string $rules, array $data, string $field)
    {
        if (false === $this->filter($ip, [FILTER_VALIDATE_IP]) && !preg_match("/[a-zA-Z0-9][-a-zA-Z0-9]{0,62}(\.[a-zA-Z0-9][-a-zA-Z0-9]{0,62})+\.?/", $ip)) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_IP_DOMAIN'), $field);
        }
        return true;
    }
}
