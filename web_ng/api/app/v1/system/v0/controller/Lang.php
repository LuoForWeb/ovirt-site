<?php

namespace app\v1\system\v0\controller;

use app\v1\common\controller\Base;

/**
 * note          语言包管理
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/2 16:02
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Lang extends Base
{
    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
    * 获取语言包
     * @return json
     */
    public function getLang()
    {
        // 可能存在 模块 key 两种参数
        $result = xphp_get_lang($this->param['key'] ?? '', $this->param['module'] ?? '');

        if (!empty($this->param['key'])) {
            $results[$this->param['key']] = $result;
        } else {
            $results = $result;
        }

        $this->success('', $results);
    }
}
