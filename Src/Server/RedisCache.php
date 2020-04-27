<?php
/**+----------------------------------------------------------------------
 * JamesPi Redis [php-redis]
 * +----------------------------------------------------------------------
 * Redis Service Configuration file
 * +----------------------------------------------------------------------
 * Copyright (c) 2020-2030 http://www.pijianzhong.com All rights reserved.
 * +----------------------------------------------------------------------
 * Author：PiJianZhong <jianzhongpi@163.com>
 * +----------------------------------------------------------------------
 */

namespace Jamespi\Redis\Server;

use Jamespi\Redis\Api\RedisApiInterface;
class RedisCache extends redisBasic implements RedisApiInterface
{

    /**
     * 设置key缓存
     * @param string $key
     * @param string $value
     * @param int $timeout 过期时间
     * @param bool $is_add 是否需要加前缀
     * @return bool
     */
    public function set(string $key, string $value, int $timeout, bool $is_add=false):bool
    {
        if (!empty($key) && $is_add)   $key = "cache:".$key;
        return $this->redisInstance->set($key, $value, $timeout);
    }

    /**
     * 获取key的缓存
     * @param string $key
     * @param bool $is_add 是否需要加前缀
     * @return mixed
     */
    public function get(string $key, bool $is_add=false)
    {
        if (!empty($key) && $is_add)   $key = "cache:".$key;
        return $this->redisInstance->get($key);
    }

    /**
     * 删除key
     * @param string $key
     * @return int
     */
    public function del(string $key):int
    {
        if (!empty($key))   $key = "cache:".$key;
        return $this->redisInstance->del($key);
    }

    /**
     * 删除hash中key的值
     * @param string $key
     * @param string $field
     * @return int
     */
    public function hdel(string $key, string $field):int
    {
        if (!empty($key))   $key = "cache:".$key;
        return $this->redisInstance->hdel($key, $field);
    }

    /**
     * 清除队列中第一个元素
     * @param string $key
     * @return int|nil
     */
    public function lpop(string $key)
    {
        if (!empty($key))   $key = "cache:".$key;
        return $this->redisInstance->lpop($key);
    }

    /**
     * 清除队列中最后一个元素
     * @param string $key
     * @return int|nil
     */
    public function rpop(string $key)
    {
        if (!empty($key))   $key = "cache:".$key;
        return $this->redisInstance->rpop($key);
    }

    public function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
    }
}