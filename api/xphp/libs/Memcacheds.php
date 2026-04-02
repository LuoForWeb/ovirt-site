<?php
// phpcs:ignoreFile -- 框架类
/*********************************************************************************
 *  扩展类库-memcached分片类
 ***********************************************************************************/

namespace xphp;

class Memcacheds
{
    private $memcached;
    private $maxChunkSize = 1000 * 1024; // 1000KB 每片

    public function __construct(\Memcached $memcached) {
        $this->memcached = $memcached;
    }

    // 写入带分片的数据
    public function set($key, $value, $expiration = 0) {
        if (is_string($value)) {
            // 如果是字符串，直接存储
            $serialized = $value;
        } else {
            // 否则序列化后存储
            $serialized = serialize($value);
        }

        // 如果数据大小小于限制，直接写入
        if (strlen($serialized) <= $this->maxChunkSize) {
            return $this->memcached->set($key, $serialized, $expiration);
        }

        // 分片写入
        $numChunks = ceil(strlen($serialized) / $this->maxChunkSize);
        $chunks = str_split($serialized, $this->maxChunkSize);

        // 存储每个分片
        foreach ($chunks as $i => $chunk) {
            $this->memcached->set($key . "_chunk_$i", $chunk, $expiration);
        }

        // 存储元信息（分片数量）
        return $this->memcached->set($key . "_meta", ['chunks' => $numChunks], $expiration);
    }

    // 读取并合并数据
    public function get($key) {
        // 尝试直接读取原始 key
        $data = $this->memcached->get($key);
        if ($this->memcached->getResultCode() == \Memcached::RES_SUCCESS) {
            // 如果是字符串，尝试反序列化
            if (is_string($data)) {
                $unser = @unserialize($data);
                if ($unser !== false || $data === 'b:0;' || $data === serialize(false)) {
                    return $unser;
                }
                // 如果不是序列化数据，返回原始字符串
                return $data;
            } else {
                // 如果不是字符串（如 Memcached 自动反序列化成了数组），直接返回
                return $data;
            }
        }

        // 如果失败，检查是否存在分片
        $meta = $this->memcached->get($key . "_meta");
        if ($this->memcached->getResultCode() != \Memcached::RES_SUCCESS || !isset($meta['chunks'])) {
            return false;
        }

        // 合并所有分片
        $totalChunks = $meta['chunks'];
        $recovered = '';

        for ($i = 0; $i < $totalChunks; $i++) {
            $chunk = $this->memcached->get($key . "_chunk_$i");
            if ($this->memcached->getResultCode() != \Memcached::RES_SUCCESS) {
                return false; // 任意分片丢失，视为失败
            }
            // 确保 chunk 是字符串
            if (!is_string($chunk)) {
                return false;
            }
            $recovered .= $chunk;
        }

        // 检查是否是序列化数据（尝试 unserialize）
        if (is_string($recovered)) {
            $unser = @unserialize($recovered);
            if ($unser !== false || $recovered === 'b:0;' || $recovered === serialize(false)) {
                return $unser;
            }
        }

        // 否则返回原始字符串
        return $recovered;
    }

    // 删除分片数据
    public function delete($key) {
        $this->memcached->delete($key);

        $meta = $this->memcached->get($key . "_meta");
        if (isset($meta['chunks'])) {
            $totalChunks = $meta['chunks'];
            for ($i = 0; $i < $totalChunks; $i++) {
                $this->memcached->delete($key . "_chunk_$i");
            }
        }
        $this->memcached->delete($key . "_meta");
    }
}
