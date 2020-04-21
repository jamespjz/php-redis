# 皮皮分布式锁(php-redis-lock)
php基于redis实现分布式锁、分布式缓存及对redis客户端api操作封装
>使用版本说明：Redis v5.0.5 - PHP v7.4.1 - Composer v1.10

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
* redis(key)API
* redis(string)API
* redis(hash)API
* redis(list)API
* redis(set)API
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

$config = [
    //redis服务器连接信息
    $config = [
        'host' => '192.168.109.58',
        'port' => 6379,
    ];
    //redis锁相关信息
    $param = [
        'token_key' => 'token_key',
        'acquire_number' => 3,
        'lock_timeout' => 20,
    ];
];
//业务场景
$type = 2;//1：redis客户端Api 2：分布式锁 3：分布式缓存
//redis环境
$redis_setting = 1; //1：单机环境 2：集群环境
//获取redis分布式锁
echo (new Start())->run($type, $config, $redis_setting)->acquireLock($param);
//释放redis分布式锁
$params = [
    'token_key' => 'token_key',
    'identifier' => '4a3068a14f90554383dcaedf59c367a3',
];
echo (new Start())->run($type, $config, $redis_setting)->unLock($params);
```
***注意：配置数组的下标键名是约定好的，请不要定制个性化名称，如果不想系统报错或系统使用默认配置参数而达不到您想要的结果的话***

# 联系方式
* wechat：james-pi
* email：jianzhongpi@163.com

