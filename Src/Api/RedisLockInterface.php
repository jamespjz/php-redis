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
     * @param array $arguments 请求参数
     * @return mixed
     */
    public function acquireLock(array $arguments);

    /**
     * 释放分布式锁
     * @param array $arguments 请求参数
     * @return mixed
     */
    public function unLock(array $arguments);

}