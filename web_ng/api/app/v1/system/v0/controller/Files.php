<?php

namespace app\v1\system\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          文件上传
 * @author       wanggongxi@vinchin.com
 * @date         2024/7/31 15:34
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */
class Files extends AuthBase
{
    /**
    * 上传文件
     * @return json
     */
    public function upload()
    {
        $return = (new \xphp\Files())->upload();
        if ($return['code'] == 0) {
            return $this->success('', $return);
        }
        return $this->error($return['info']);
    }
}
