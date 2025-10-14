<?php

namespace Airmole\TjustbOpacsys;

use Airmole\TjustbOpacsys\Exception\Exception;
use DiDom\Document;

class Preorder extends Base
{
    /**
     * 预约图书列表 - 未测试
     * @return array
     * @throws Exception|\DiDom\Exceptions\InvalidSelectorException
     */
    public function preorderList(): array
    {
        $url = '/reader/preg.php';
        $result = $this->httpRequest('GET', $url, '', $this->cookie);
        if ($result['status'] != self::CODE_SUCCESS) throw new Exception('preorderList：' . json_encode($result));

        $list = [];

        $dom = new Document($result['data']);
        $tableTrs = $dom->first('table.table_line')->find('tr');
        foreach ($tableTrs as $index => $tr) {
            if ($index == 0) continue;
            $tds = $tr->find('td');
            $list[] = [
                'callNo' => $tds[0]->text(),
                'author' => $tds[1]->text(),
                'place' => $tds[2]->text(),
                'expectDate' => $tds[3]->text(),
                'stopDate' => $tds[4]->text(),
                'pickup' => $tds[5]->text(),
                'status' => $tds[6]->text(),
                'cancel' => $tds[7]->text(), // TODO 可能需要获取取消操作URL，待有数据测试时优化
            ];
        }

        return ['code' => $result['code'], 'data' => $list];
    }

}