<?php

namespace app\v1\system\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          系统配置之时间配置
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/14 10:51
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Time extends AuthBase
{
    /**
     * 获取所有时区配置文件
     * @return json
     */
    public function getAllTimezone()
    {
        $records = $this->logic()->getAllTimezone($this->param);

        $this->success('', $records);
    }

    /**
     * 获取系统默认的时区和时间
     * @return json
     */
    public function getDefaultTimeInfo()
    {
        $records = $this->logic()->getDefaultTimeInfo($this->param);

        $this->success('', $records);
    }

    /**
     * 立即同步ntp时间
     * @return json
     */
    public function syncNtpTime()
    {
        // 参数验证
        $this->checkParams('init');

        $result = $this->logic()->syncNtpTime($this->param);

        $this->muOpResult(
            $result[0],
            $result[1],
            $result[2] ?? '',
            $result[3] ?? '',
            $result[4] ?? 0,
            $result[5] ?? ''
        );
    }

    /**
     * 设置时间
     * @return json
     */
    public function setTimeInfo()
    {
        // 参数验证
        $this->checkParams('submit');

        $result = $this->logic()->setTimeInfo($this->param);

        $this->muOpResult($result[0], $result[1], $result[2] ?? '', $result[3] ?? '');
    }

    /**
     * 获取系统时间
     * @return json
     */
    public function getSystemTime()
    {
        $records = $this->logic()->getSystemTime($this->param);

        $this->success('', ['value' => $records]);
    }
}
