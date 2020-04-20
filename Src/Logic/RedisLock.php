<?php
/**+----------------------------------------------------------------------
 * JamesPi Redis [php-redis]
 * +----------------------------------------------------------------------
 * Redis Logic Configuration file
 * +----------------------------------------------------------------------
 * Copyright (c) 2020-2030 http://www.pijianzhong.com All rights reserved.
 * +----------------------------------------------------------------------
 * Author：PiJianZhong <jianzhongpi@163.com>
 * +----------------------------------------------------------------------
 */

namespace Jamespi\Redis\Logic;

use Jamespi\Redis\Api\RedisLockInterface;
class RedisLock
{
    protected $redisLock;

    public function __construct(RedisLockInterface $redisLock)
    {
        $this->redisLock = $redisLock;
    }

    /**
     * 获取分布式锁
     * @param $instance 链接redis实例化对象
     * @param string $token_key 分布式锁key
     * @param int $acquire_number 获取分布式锁次数
     * @param int $lock_timeout 分布式锁过期时间
     * @return bool
     */
    public function acquireLock($instance, string $token_key, int $acquire_number, int $lock_timeout):bool
    {

        $this->redisLock->acquireLock();
    }
}