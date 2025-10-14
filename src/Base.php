<?php

namespace Airmole\TjustbOpacsys;

use Airmole\TjustbOpacsys\Exception\Exception;

/**
 * Base
 * @package Airmole\TjustbOpacsys
 */
class Base
{
/**
     * @var string 默认OPAC系统URL
     */
    public const DEFAULT_OPACSYS_URL = 'http://10.1.254.98:82';
    /**
     * @var string OPAC系统 URL域名
     */
    public string $opacsysUrl;
    /**
     * @var string 代理地址
     */
    public string $proxy;
    /**
     * @var string 配置文件路径
     */
    public string $configPath;  // http://10.1.254.98:82

    /**
     * @var string 用户账号
     */
    public string $usercode;

    /**
     * @var string 用户已登录cookie
     */
    public string $cookie;

    /**
     * @var int 默认请求成功响应代码
     */
    public const CODE_SUCCESS = 200;

    /**
     * @var int 默认请求重定向响应代码
     */
    public const CODE_REDIRECT = 302;

    public function __construct()
    {
        // 设置默认配置文件
        if (empty($this->configPath)) $this->setConfigPath();
        // 未配置OPAC URL 自动配置
        if (empty($this->opacsysUrl)) $this->setOpacsysUrl();
        // 设置代理
        $this->proxy = $this->getConfig('OPACSYS_PROXY', '');
    }

    /**
     * 设置配置文件路径
     * @param string $path
     * @return void
     */
    public function setConfigPath(string $path = ''): void
    {
        $defaultPath = $_SERVER['DOCUMENT_ROOT'] . '/../.env';
        if ($path === '') $path = $defaultPath;
        $this->configPath = $path;
    }

    /**
     * 设置可用教务URL
     * @param string $url
     * @return void
     */
    public function setOpacsysUrl(string $url = self::DEFAULT_OPACSYS_URL): void
    {
        if (empty($url)) $url = self::DEFAULT_OPACSYS_URL;
        $configOpacsysUrl = $this->getConfig('OPACSYS_URL', '');
        if (!empty($configOpacsysUrl)) $url = $configOpacsysUrl;
        $this->opacsysUrl = $url;
    }

    /**
     * 获取配置项
     * @param string $key
     * @param $default
     * @param string $path
     * @return string
     */
    public function getConfig(string $key, $default = null, string $path = ''): string
    {
        $configs = $this->configPath;
        if (!file_exists($configs) && $path === '') return $default;
        preg_match("/{$key}=(.*?)\n/", file_get_contents($configs), $matchedConfig);
        if (empty($matchedConfig) && $path === '') return $default;
        return $matchedConfig[1] ?: $default;
    }

    /**
     * 清除空格换行以及HTML标签
     * @param string $html
     * @return string
     */
    public function stripHtmlTagAndBlankspace(string $html): string
    {
        $str = trim($html);
        $str = preg_replace("/\r\n/", "", $str);
        $str = preg_replace("/\r/", "", $str);
        $str = preg_replace("/\n/", "", $str);
        $str = preg_replace("/\t/", "", $str);
        $str = preg_replace("/ /", "", $str);
        $str = trim($str);
        $str = strip_tags($str);
        $str = preg_replace("/&nbsp;/", "", $str);
        return preg_replace("/&nbsp/", "", $str);
    }

    /**
     * 清除换行、空格
     * @param string $html
     * @return string
     */
    public function stripBlankspace(string $html): string
    {
        $str = trim($html);
        $str = preg_replace("/\r\n/", "", $str);
        $str = preg_replace("/\r/", "", $str);
        $str = preg_replace("/\n/", "", $str);
        $str = preg_replace("/\t/", "", $str);
        $str = trim($str);
        return preg_replace("/&nbsp;/", "", $str);
    }

    /**
     * GET 请求
     * @param string $url 请求URL
     * @param string $cookie 携带cookie
     * @param string $referer header->referer
     * @param int $timeout 请求超时时间（秒）
     * @param bool $showHeader 返回信息包含Header
     * @return array
     * @throws Exception
     */
    public function httpGet(
        string $url,
        string $cookie = '',
        string $referer = '',
        int    $timeout = 10,
        bool   $showHeader = false
    ): array
    {
        if (!str_contains($url, 'http://') && !str_contains($url, 'https://')) {
            $url = $this->opacsysUrl . $url;
        }
        $headers = [
            'Connection: keep-alive',
            'Upgrade-Insecure-Requests: 1',
            'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36 Edg/135.0.0.0',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
            'Accept-Encoding: gzip, deflate',
            'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
        ];
        if (!empty($referer)) $headers[] = "Referer: {$referer}";
        if (!empty($cookie)) $headers[] = "Cookie: {$cookie}";
        $timeout = $this->getConfig('OPACSYS_TIMEOUT', $timeout);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_ENCODING, 'gzip, deflate');
        curl_setopt($curl, CURLOPT_NOSIGNAL, 1);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout); //设置超时时间
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers); //设置请求头
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); //不验证证书
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($curl, CURLOPT_HEADER, $showHeader);
        if (!empty($this->proxy)) curl_setopt($curl, CURLOPT_PROXY, $this->proxy);
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if ($httpCode == 0 || $httpCode == 56) throw new Exception('请求超时' . $httpCode);
        return ['code' => (int)$httpCode, 'data' => $response];
    }

    /**
     * POST 请求
     * @param string $url 请求URL
     * @param mixed $body 请求体
     * @param string $cookie 携带cookie
     * @param string $referer header->referer
     * @param int $timeout 请求超时时间（秒）
     * @return array
     * @throws Exception
     */
    public function httpPost(
        string $url,
        mixed  $body = '',
        string $cookie = '',
        string $referer = '',
        int    $timeout = 10
    ): array
    {
        if (!str_contains($url, 'http://') && !str_contains($url, 'https://')) {
            $url = $this->opacsysUrl . $url;
        }

        $headers = [
            'Accept: application/json, text/javascript, */*; q=0.01',
            'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
            'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36 Edg/135.0.0.0',
            'X-Requested-With: XMLHttpRequest',
        ];

        // body传入对象或数组，则自动转换为json格式
        if (is_object($body) || is_array($body)) {
            $body = json_encode($body);
            $headers[] = 'Content-Type: application/json';
        }
        // 判断字符串是否为json格式
        json_decode($body);
        if (json_last_error() === JSON_ERROR_NONE) {
            $headers[] = 'Content-Type: application/json';
        }

        if (!empty($referer)) $headers[] = "Referer: {$referer}";
        if (!empty($cookie)) $headers[] = "Cookie: {$cookie}";

        $timeout = $this->getConfig('OPACSYS_TIMEOUT', $timeout);
        $curlOption = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => 'gzip, deflate',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headers
        ];
        if (!empty($this->proxy)) $curlOption[CURLOPT_PROXY] = $this->proxy;
        $curl = curl_init();
        curl_setopt_array($curl, $curlOption);
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if ($httpCode == 0 || $httpCode == 56) throw new Exception('请求超时');
        return ['code' => (int)$httpCode, 'data' => $response];
    }

    /**
     * HTTP请求
     * @param string $method 请求方式
     * @param string $url 请求URL
     * @param mixed $body 请求体
     * @param mixed $cookie Cookie
     * @param array $headers 请求头
     * @param bool $showHeaders 是否返回请求头
     * @param bool $followLocation 是否跟随跳转
     * @param int $timeout 超时时间
     * @return array 响应结果
     */
    public function httpRequest(
        string $method = 'GET',
        string $url = '',
        mixed  $body = '',
        mixed  $cookie = '',
        array  $headers = [],
        bool   $showHeaders = false,
        bool   $followLocation = false,
        int    $timeout = 20
    ): array
    {
        if (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
            $url = $this->opacsysUrl . (str_starts_with($url, '/') ? $url : "/{$url}");
        }
        $url = trim($url);

        $defaultHeaders = [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.150 Safari/537.36',
            'Accept-Encoding: gzip, deflate',
            'Accept-Language: zh',
        ];
        $headers = array_merge($defaultHeaders, $headers);

        if (is_string($cookie) && !empty($cookie)) {
            $cookie = trim($cookie);
            $headers[] = !str_starts_with($cookie, 'Cookie:') ? "Cookie: {$cookie}" : $cookie;
        }

        $timeout = (int)$this->getConfig('OPACSYS_TIMEOUT', $timeout);

        $requestOptions = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => 'gzip, deflate',
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FOLLOWLOCATION => $followLocation,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_HEADER => $showHeaders,
        ];

        if (!empty($body)) {
            $requestOptions[CURLOPT_POSTFIELDS] = is_array($body) ? http_build_query($body) : $body;
        }

        if (!empty($this->proxy)) $requestOptions[CURLOPT_PROXY] = $this->proxy;

        $ch = curl_init();
        curl_setopt_array($ch, $requestOptions);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            return ['code' => 0, 'data' => 'cURL Error: ' . $error];
        }

        curl_close($ch);

        return ['code' => (int)$httpCode, 'data' => $response];
    }

    /**
     * 从响应头中获取Cookie
     * @param string $key Cookie名称
     * @param string $headerString 响应头字符串
     * @return string Cookie值
     */
    public function getCookieFromHeader(string $key, string $headerString = ''): string
    {
        preg_match("/Set-Cookie: {$key}=(.*?);/", $headerString, $cookieValue);
        return $cookieValue[1] ?? '';
    }

    /**
     * 从响应头中获取跳转地址
     * @param string $header 响应头字符串
     * @return string 跳转地址
     */
    public function getLocationFromRedirectHeader(string $header = ''): string
    {
        preg_match('/Location: (.*)/', $header, $nextUrl);
        $nextUrl = $nextUrl[1] ?? '';
        return trim($nextUrl);
    }

    /**
     * 获取二维码
     * @param string $baseString 二维码内容Base64编码
     * @return string Base64二维码图片
     * @throws Exception
     */
    public function qrcode(string $baseString = ''): string
    {
        $url = '/opac/ajax_qr.php?qrcode=' . $baseString;
        $result = $this->httpRequest('GET', $url);
        if ($result['code'] != self::CODE_SUCCESS) throw new Exception('获取二维码失败');
        return base64_encode($result['data']);
    }

}