<?php
/**+----------------------------------------------------------------------
 * JamesPi Redis [php-redis]
 * +----------------------------------------------------------------------
 * Redis Service Interface Configuration file
 * +----------------------------------------------------------------------
 * Copyright (c) 2020-2030 http://www.pijianzhong.com All rights reserved.
 * +----------------------------------------------------------------------
 * Author：PiJianZhong <jianzhongpi@163.com>
 * +----------------------------------------------------------------------
 */

namespace Jamespi\Redis\Api;

interface RedisApiInterface{

    /**
     * 设置key缓存
     * @param string $key
     * @param string $value
     * @param int $timeout
     * @param bool $is_add 是否需要加前缀
     * @return bool
     */
    public function set(string $key, string $value, int $timeout, bool $is_add=false):bool ;

    /**
     * 获取key的缓存
     * @param string $key
     * @param bool $is_add
     * @return mixed
     */
    public function get(string $key, bool $is_add=false);

    /**
     * 删除key
     * @param string $key
     * @return int
     */
    public function del(string $key):int ;

    /**
     * 删除hash中key的值
     * @param string $key
     * @param string $field
     * @return int
     */
    public function hdel(string $key, string $field):int;

    /**
     * 清除队列中第一个元素
     * @param string $key
     * @return int|nil
     */
    public function lpop(string $key);

    /**
     * 清除队列中最后一个元素
     * @param string $key
     * @return int|nil
     */
    public function rpop(string $key);

}