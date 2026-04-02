<?php

namespace app\v1\virus\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          病毒库处理类 验证器
 * @author       chenchao@vinchin.com
 * @date         2025/06/19 18:32
 * @version      1.0.0
 * @copyright    Copyright 2025 vinchin.com
 */
class Virus extends Base
{
    /**
     * 构造方法
     */
    public function __construct()
    {

        parent::__construct();

        $allVirusType = xphp_get_config('virus', 'VIRUS_TYPE', 'virus');
        unset($allVirusType['UNKNOWN']);
        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'key_file_upload_path' => ['require'],
            'key_file_name' => ['require'],
            'lib_type' => ['require', 'in:' . implode(',', $allVirusType)],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            // 获取客户端列表
            'upload_authorization_file' => ['key_file_upload_path', 'key_file_name', 'lib_type'],
        ];
    }
}