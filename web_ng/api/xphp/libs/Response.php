<?php
// phpcs:ignoreFile -- 框架类
declare (strict_types=1);

namespace xphp;

/**
 * note          数据返回类
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/2 16:28
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Response
{

    /**
     * 封装底层返回
     */
    public function returns($data, $header = [], $type = 'json')
    {
        if (!empty($header)) {
            foreach ($header as $key => $value) {
                header($key . ': ' . $value);
            }
        }

        if (empty(xphp_get_cache('__token__'))) {
            // session只有销毁后才会再次生成
            $token = token();
            xphp_set_cache('__token_pool__', [
                $token => 1,
            ]);
            xphp_set_cache('__token__', $token);
        }

        header('__TOKEN__: ' . xphp_get_cache('__token__'));

        exit($this->back($data, $type));
    }

    /**
     * 根据类型返回json或者xml
     */
    private function back($result = [], $type = 'json')
    {
        if ($type == 'xml') {
            return $this->toXML($result);
        } else {
            header('Content-Type: application/json');
            return json_encode($result);
        }
    }

    /***
     * 解析结果数组转为对应的xml节点
     */
    private function toXML($data)
    {
        # 临时存储xml数据
        $xml = $id = '';

        # 遍历并拼接字符串
        foreach ($data as $key => $value) {

            # 如果key是数字(即非关联数组 => [hello,2,true] )
            # 以 <item id='?'></item> 展示,id为key
            if (is_numeric($key)) {//如果是数字
                $id = "id='{$key}'";//将key作为id属性
                $key = 'item ';//将item作为节点名
            }

            $xml .= "<{$key}{$id}>";//开始节点

            # 如果是数组则递归(否则直接返回value值)
            $xml .= is_array($value) ? self::toXML($value) : $value;

            $xml .= "</{$key}>";//结束节点
        }

        # 更改头部(为了更清晰的展示XML节点)
        header('Content-type: text/xml');//xml

        # 生成XML(字符串拼接方式)
        $return = '<?xml version="1.0" encoding="UTF-8"?>';//xml head
        $return .= '<root>';//拼接根节点(开始)

        $return .= $xml;

        $return .= '</root>';//拼接根节点(结束)

        return $return;
    }

}
