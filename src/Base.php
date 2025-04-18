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
     * @var string OPAC系统 URL域名
     */
    public string $opacsysUrl;

    /**
     * @var string 配置文件路径
     */
    public string $configPath;

    /**
     * @var string 默认OPAC系统URL
     */
    public const DEFAULT_OPACSYS_URL = 'http://opac.bkty.top';  // http://10.1.254.98:82

    public function __construct()
    {
        // 未配置教务URL 自动配置
        if (empty($this->opacsysUrl)) $this->setOpacsysUrl();
        // 设置默认配置文件
        if (empty($this->configPath)) $this->setConfigPath();
    }

    /**
     * 设置可用教务URL
     * @param string $url
     * @return void
     */
    public function setOpacsysUrl(string $url = self::DEFAULT_OPACSYS_URL): void
    {
        if (empty($url)) $url = self::DEFAULT_OPACSYS_URL;
        $this->opacsysUrl = $url;
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
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => 'gzip, deflate',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'GET',
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_HEADER         => $showHeader,
        ));
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode == 0 || $httpCode == 56) throw new Exception('请求超时');
        return ['code' => (int)$httpCode, 'data' => $response];
    }

}