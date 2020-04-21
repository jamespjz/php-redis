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

use Jamespi\Redis\Api\RedisLockInterface;
class RedisLock extends redisBasic implements RedisLockInterface
{
    /**
     * 获取分布式锁
     * @param $instance 链接redis实例化对象
     * @param string $token_key 分布式锁key
     * @param string $identifier 分布式锁key值
     * @param int $lock_timeout 分布式锁过期时间
     * @return bool
     */
    public function acquireLock($instance, string $token_key, string $identifier, int $lock_timeout)
    {
        if (time() < (time()+$lock_timeout)) {
            $script = <<<luascript
                local result = redis.call('setnx',KEYS[1],ARGV[1])
                if result == 1 then
                    if redis.call('expire',KEYS[1],ARGV[2]) == 1 then
                        return 1
                    else
                        return 0
                    end
                elseif redis.call('ttl',KEYS[1]) == -1 then
                   redis.call('expire',KEYS[1],ARGV[2])
                   return 0
                end
                return 0
luascript;
            $result = $instance->eval($script, array($token_key, $identifier, $lock_timeout), 1);
            if ($result == 1) {
                return $identifier;
            }
        }
        return false;
    }

    public function unLock(array $arguments)
    {

    }

    public function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
    }
}