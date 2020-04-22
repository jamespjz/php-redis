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

interface RedisLockInterface{

    /**
     * 获取分布式锁
     * @param $instance 链接redis实例化对象
     * @param string $token_key 分布式锁key
     * @param string $identifier 分布式锁key值
     * @param int $acquire_time 请求分布式锁时间
     * @param int $lock_timeout 分布式锁过期时间
     * @return bool
     */
    public function acquireLock($instance, string $token_key, string $identifier, int $acquire_time, int $lock_timeout);

    /**
     * 释放分布式锁
     * @param $instance 链接redis实例化对象
     * @param string $token_key 分布式锁key
     * @param string $identifier 分布式锁key值
     * @return mixed
     */
    public function unLock($instance, string $token_key, string $identifier);

}