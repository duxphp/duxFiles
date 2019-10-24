
<p align="center">
  <a href="https://github.com/duxphp/duxphp-files">
   <img alt="DuxShop" src="https://github.com/duxphp/duxphp/raw/master/docs/logo.png?raw=true">
  </a>
</p>

<p align="center">
  为快速开发而生
</p>

<p align="center">
  <a href="https://github.com/duxphp/duxphp-files">
    <img alt="maven" src="https://img.shields.io/badge/DuxFile-v1-blue.svg">
  </a>

  <a href="http://zlib.net/zlib_license.html">
    <img alt="code style" src="https://img.shields.io/badge/zlib-licenses-brightgreen.svg">
  </a>
</p>

# 简介

DuxFile 是一款PHP多存储驱动的文件管理类，支持多种云存储平台，使用 `guzzle6` 做驱动请求，摒弃各类 SDK 的臃肿，统一使用调用方便各类框架、系统使用。

# 支持平台

- 本地服务器
- 阿里云 OSS
- 腾讯云 COS
- 七牛云存储
- 又拍云存储

# 环境支持

- 语言版本：PHP 7.1+

# 讨论

QQ群：131331864

> 本系统非盈利产品，为防止垃圾广告和水群已开启收费入群，收费入群并不代表我们可以无条件回答您的问题，入群之前请仔细查看文档，常见安装等问题通过搜索引擎解决，切勿做伸手党

# bug反馈

[issues反馈](https://github.com/duxphp/duxphp-files/issues)
    
# 版权说明

本项目使用MIT开源协议，您可以在协议允许范围内进行进行商业或非商业项目使用

# 开发团队

湖南聚匠信息科技有限公司


# 安装说明

   ```
   composer require duxphp/duxphp-files
   ```
   
# 使用方法

实例化操作类

    ```
    $config = [
        'max_size' => 1048576, //保存文件大小限制 默认10M
        'allow_exts' => [], //允许的文件后缀 默认全部
        'save_rule' => 'md5', //文件名重置规则 默认文件md5，为空不处理，可回调函数处理
    ];
    $file = new \dux\Files($config, $driverConfig);
    ```
   
驱动配置

    ```
    // 阿里云 Oss
    $driverConfig = [
        'type' => 'oss',
        'access_id' => '',
        'secret_key' => '',
        'bucket' => '',   //存储空间
        'domain' => '',   //访问域名
        'url' => '',      //接口域名
    ];
    ```

    ```
    // 腾讯云 Cos
    $driverConfig = [
        'type' => 'cos',
        'secret_id' => '',
        'secret_key' => '',
        'bucket' => '',   //存储空间
        'domain' => '',   //访问域名
        'url' => '',      //接口域名
    ];
    ```

    ```
    // 七牛云存储
    $driverConfig = [
        'type' => 'qiniu',
        'access_key' => '',
        'secret_key' => '',
        'bucket' => '',   //存储空间
        'domain' => '',   //访问域名
        'url' => '',      //接口域名
    ];
    ```

    ```
    // 又拍云存储
    $driverConfig = [
        'type' => 'upyun',
        'operator' => '', //操作员
        'password' => '', //操作员密码
        'bucket' => '',   //存储空间
        'domain' => '',   //文件域名
        'url' => '',      //接口域名
    ];
    ```

   
保存文件
    
    ```
    // $path 为文件流或者文件路径、Url
    // $name 保存文件路径名，如：\upload\dux.jpg
    $file->save($path, $name);
    ```
    
删除文件
    
    ```
    // $name 保存文件路径名，如：\upload\dux.jpg
    $file->del($name);
    ```
    
异常捕获

   ```
    try {
        $file = new \dux\Files($config, $driverConfig);
        $file->save($path, $name);
    } catch (\Exception $e) {
        echo $e->getMessage();
    }
    ```