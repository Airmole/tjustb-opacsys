# tjustb-opacsys

tjustb图书馆OPAC系统客户端（http://opac.bkty.top）

# Requirement

- PHP >= 8.0

# Config

引用项目根目录下`.env`文件可配置以下配置项参数：

| 参数名 | 默认值             | 说明 |
| --- |-----------------| --- |
| OPACSYS_URL | http://10.1.254.98:82 | OPAC系统地址 |
| OPACSYS_TIMEOUT | 10              | 请求超时时间（秒） |
| OPACSYS_PROXY | null            | 请求代理 |


# Usage

```php
<?php
use Airmole\TjustbOpacsys\Opacsys;
class Test
{
    public function test()
    {
        $opacsys = new Opacsys();
        $result = $opacsys->lendAndPopularTopTen(); // 获取热门借阅和热门图书top10
        print_r($result);
    }
}
```


## Suitable

以本校2江苏汇文OPAC`v5.6.1.220715`抓包分析开发而来，其余院校版本未测试可用性无法保证。各功能代码略有不同，如果您有类似需求，可[联系我](mailto:admin@airmole.cn)有偿开发专用特供版本。
