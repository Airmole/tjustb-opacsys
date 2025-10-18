<?php

namespace Airmole\TjustbOpacsys;

use Airmole\TjustbOpacsys\Exception\Exception;

class Login extends Base
{

    /**
     * SSO登录
     * @param string $ticket
     * @return array
     * @throws Exception
     */
    public function ssoLogin(string $ticket = ''): array
    {
        if (empty($ticket)) throw new Exception('Ticket is empty');

        $url = "/reader/hwthau.php?ticket={$ticket}";
        $result = $this->httpRequest('GET', $url, '', '', [], true);
        if ($result['code'] !== self::CODE_REDIRECT) throw new Exception('Login failed' . json_encode($result));

        $cookie = $this->getCookieFromHeader('PHPSESSID', $result['data']);
        if (empty($cookie)) throw new Exception('Cookie is empty');
        $cookie = "PHPSESSID={$cookie}";
        $this->cookie = $cookie;

        $url = '/reader/hwthau.php';
        $result = $this->httpRequest('GET', $url, '', $this->cookie, [], true);

        $url = '/reader/redr_info.php';
        $result = $this->httpRequest('GET', $url, '', $this->cookie);
        if ($result['code'] !== self::CODE_SUCCESS) throw new Exception('Get user info failed' . json_encode($result));

        return [ 'code' => self::CODE_SUCCESS, 'cookie' => $this->cookie, 'data' => $result['data'] ];
    }

}