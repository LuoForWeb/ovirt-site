<?php

namespace app\v1\system\v0\validate;

use app\v1\common\validate\Base;

class Index extends Base
{
    /**
     * 构造方法
     */
    public function __construct()
    {

        parent::__construct();
        $allMessageType = xphp_get_config('system', 'MESSAGE_TYPE');

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'filepath' => ['require'],
            'p' => ['require'],
            'ip_list' => ['require', 'array'],
            'message_type' => ['require', 'in:' . implode(',', array_values($allMessageType))],
            'object_id' => ['require'],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            'generate_download_filepath' => ['filepath'],
            'download_file' => ['p'],
            'batch_check_ip_reachable' => ['ip_list'],
            'send_messages' => ['message_type', 'object_id'],
        ];
    }
}