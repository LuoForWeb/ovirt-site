<?php

namespace app\v2\system\v0\controller;

use app\v2\common\controller\Base;

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
    * 获取语言包
     * @return json
     */
    public function getLang()
    {
        $module = $this->param['module'] ?? 'common';
        // 可能存在 模块 key 两种参数
        $result = xphp_get_web_lang($this->param['key'] ?? '', $module);

        if (!empty($this->param['key'])) {
            $results[$this->param['key']] = $result;
        } else {
            $results = $result;
        }

        $this->success('', $results);
    }
}
