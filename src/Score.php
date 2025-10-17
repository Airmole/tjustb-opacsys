<?php

namespace Airmole\TjustbOpacsys;

use Airmole\TjustbOpacsys\Exception\Exception;
use DiDom\Document;

class Score extends Base
{
    /**
     * 获取积分记录
     * @param int $page 页码
     * @return array
     * @throws Exception|\DiDom\Exceptions\InvalidSelectorException
     */
    public function scoreRecord(int $page = 1): array
    {
        $query = [
            'page' => $page
        ];
        $url = '/reader/credit_detail.php?' . http_build_query($query);
        $result = $this->httpRequest('GET', $url, '', $this->cookie);
        if ($result['code'] != self::CODE_SUCCESS) throw new Exception('scoreRecord' . json_encode($result));

        $records = [];
        $dom = new Document($result['data']);

        $totalPage = $dom->first('font[color=black]')->text();
        $totalPage = $this->stripHtmlTagAndBlankspace($totalPage) ?? 0;
        $totalPage = intval($totalPage);

        $tableTrs = $dom->first('table.table_line')->find('tr');
        foreach ($tableTrs as $index => $tr) {
            if ($index == 0) continue;
            $tds = $tr->find('td');
            $records[] = [
                'sn' => $tds[0]->text(),
                'typeName' => $tds[1]->text(),
                'action' => $tds[2]->text(),
                'value' => $tds[3]->text(),
                'comment' => $tds[4]->text(),
                'datetime' => date('Y-m-d H:i:s', strtotime($tds[5]->text())),
            ];
        }

        return [
            'code' => $result['code'],
            'page' => $page,
            'totalPage' => $totalPage,
            'data' => $records
        ];
    }

}