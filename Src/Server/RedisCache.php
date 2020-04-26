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
    public function del(string $key):int
    {
        $this->redisInstance->del($key);
    }

    public function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
    }
}