# PP分布式锁(php-redis-lock)
php基于redis实现分布式锁、分布式缓存及对redis客户端api操作封装
>使用版本说明：Redis v5.0.5 - PHP >=7.0 - Composer v1.10

# 简要说明：
公司目前正在全面转微服务架构，为了让PHPER在分布式高并发场景下保证数据的准确性，特封装了含有redis分布式锁、分布式缓存及redis操作API功能的插件，目前为version 0.1-dev。

# 分布式锁具备以下四个特性：
* 互斥性（一把锁只能加一次）
* 原子性
* 防止死锁产生（被客户端锁住用不释放，其他客户端没法使用）
* 同一个客户端只能解锁自己加的锁

# 功能简介：
* 获取锁
* 删除锁
* redis(key、string、hash、list、set)API
* 发布/订阅
* 分布式缓存
* 其余特性参考 https://www.redis.net.cn/tutorial/3501.html

# 部署安装
* github下载
```
git clone https://github.com/jamespjz/php-redis.git
```
已经加入对composer支持，根目录下有个composer.json，请不要随意修改其中内容如果你不明白你在做什么操作。
* composer下载
```
composer require jamespi/php-redis dev-master
```

# 使用方式
> 获取redis分布式锁例子

```
//在您项目根目录的入口文件中加入如下代码：
require_once 'vendor/autoload.php';
use Jamespi\Redis\Start;
/**
 * --------------------------------------------------------------
 * 单节点
 * --------------------------------------------------------------
 */
$config = [
    //redis服务器连接信息
    $config = [
        'host' => '192.168.109.58',
        'port' => 6379,
    ];
    //redis锁相关信息（出于性能考虑，acquire_timeout建议设置1s以下，系统默认设置的1s，acquire_number系统默认3次，lock_timeout系统默认60s）
    $param = [
        'token_key' => 'token_key', //必填，key
        'acquire_number' => 3, //选填，限制请求次数
        'lock_timeout' => 60, //选填，锁的超时时间
        'acquire_timeout' => 1000000 //选填，请求锁超时时间(单位微秒)
    ];
];
//业务场景
$type = 2;//1：redis客户端Api 2：分布式锁 3：分布式缓存
//redis环境
$redis_setting = 1; //1：单机环境 2：集群环境
/**
 * --------------------------------------------------------------
 * 集群
 * --------------------------------------------------------------
 */
 //$config = [
 //    'host' => '192.168.109.54',
 //    'port' => 7002,
 //    'auth' => '123456'
 //];
 //$param = [
 //    'token_key' => 'token_key',
 //    'acquire_number' => 2, //限制请求次数
 //    'lock_timeout' => 300, //锁的超时时间
 //    'acquire_timeout' => 1000000 //请求锁超时时间(单位微秒)
 //];
 //$type = 2;//1：redis客户端Api 2：分布式锁 3：分布式缓存
 //$redis_setting = 2; //1：单节点 2：集群


//获取redis分布式锁
echo (new Start())->run($type, $config, $redis_setting)->acquireLock($param);
//释放redis分布式锁
$params = [
    'token_key' => 'token_key',
    'identifier' => '4a3068a14f90554383dcaedf59c367a3',
];
echo (new Start())->run($type, $config, $redis_setting)->unLock($params);
```
> 调用redis分布式缓存例子
```
//获取分布式缓存
use Jamespi\Redis\Start;
$type = 3;//1：redis客户端Api 2：分布式锁 3：分布式缓存
$config = [
    'cache' => [
        'host' => '192.168.109.54',
        'port' => 7002,
        'auth' => '123456',
        'redis_setting' => 2 //redis环境（1：单机 2：集群）
    ],
    'mysql' => [
        //redis缓存持久化时插件回调接入方持久化(查询、删除)方法
        'namespace' => 'App\IndexController',
        'action' => 'test_select', //select表：test_select；add表：test； delete表：test_delete
    ]
];
/****  更新缓存 ****/
//string时msg为字符串；hash、list、set、sorted_set时msg为json_encode；
$params = [
    'cache_mode' => 1,//(目前只支持Aside模式)缓存更新模式 >> 1：Cache Aside模式 2：Through模式 3：Write Back模式
    'key' => 'token_key', //缓存key
    'msg' => '4a3068a14f90554383dcaedf59c367a3', //缓存key值
    'type' => 'string' //数据类型：string、hash、list、set、sorted_set
];
//echo (new Start())->run($type, $config)->write($params);
/****  读取缓存 ****/
$params_read = [
    'cache_mode' => 1,//(目前只支持Aside模式)缓存更新模式 >> 1：Cache Aside模式 2：Through模式 3：Write Back模式
    'key' => 'token_key', //缓存key
    'field' => 'field', //缓存二级key
    'msg' => '4a3068a14f90554383dcaedf59c367a3', //缓存key值
    'type' => 'string', //数据类型：string、hash、list、set、sorted_set
    'cache_timeout' => 50 //缓存失效时间（为防止缓存雪崩尽量时间采用基于时间段随机时间）
];
echo (new Start())->run($type, $config)->read($params_read);
```

***注意：配置数组的下标键名是约定好的，请不要定制个性化名称，如果不想系统报错或系统使用默认配置参数而达不到您想要的结果的话***

# 联系方式
* wechat：james-pi
* email：jianzhongpi@163.com

