<?php

namespace Airmole\TjustbOpacsys;

use Airmole\TjustbOpacsys\Exception\Exception;
use DiDom\Document;

class Shelf extends Base
{
    /**
     * 获取用户书架
     * @return array
     * @throws Exception|\DiDom\Exceptions\InvalidSelectorException
     */
    public function myShelf(): array
    {
        $url = '/reader/book_shelf.php';
        $result = $this->httpRequest('GET', $url, '', $this->cookie);
        if ($result['code'] != self::CODE_SUCCESS) throw new Exception('myShelf：' . json_encode($result));

        $dom = new Document($result['data']);
        $shelfNodes = $dom->find('a[href^="?classid="]');

        $shelf = [];
        foreach ($shelfNodes as $shelfNode) {
            $title = $shelfNode->getAttribute('title');
            $shelfIdUrl = $shelfNode->getAttribute('href');
            preg_match('/\?classid=(.*)/', $shelfIdUrl, $shelfId);
            $shelfId = $shelfId[1] ?? $shelfIdUrl;
            preg_match('/\((\d*)\)$/', $shelfNode->text(), $count);
            $count = $count[1] ?? 0;
            $shelf[] = [
                'id' => $shelfId,
                'title' => $title,
                'count' => $count
            ];
        }

        return ['code' => $result['code'], 'data' => $shelf];
    }

    /**
     * 获取书架内的书
     * @param string $shelfId 书架id
     * @return array
     * @throws Exception|\DiDom\Exceptions\InvalidSelectorException
     */
    public function myShelfBooks(string $shelfId = ''): array
    {
        if (empty($shelfId)) throw new Exception('shelfId不能为空');

        $url = '/reader/book_shelf.php?classid=' . $shelfId;
        $result = $this->httpRequest('GET', $url, '', $this->cookie);
        if ($result['code'] != self::CODE_SUCCESS) throw new Exception('shelfBooks：' . json_encode($result));

        $dom = new Document($result['data']);

        $html = $this->stripHtmlTagAndBlankspace($dom->html());

        $shelfName = '';
        preg_match('/当前选择:(.*?)共/', $html, $shelfName);
        $shelfName = $shelfName[1] ?? '';

        preg_match('/共(.*?)条记录/', $html, $count);
        $count = $count[1] ?? 0;
        $count = intval($count);

        if (!$dom->has('table.table_line')) {
            return [
                'code' => self::CODE_SUCCESS,
                'data' => [
                    'title' => $shelfName,
                    'count' => $count,
                    'books' => []
                ]
            ];
        }

        $books = [];
        $tableTrs = $dom->first('table.table_line')->find('tr');
        foreach ($tableTrs as $index => $tableTr) {
            if ($index == 0) continue;
            $tds = $tableTr->find('td');

            $marcNo = '';
            if ($tds[1]->has('a')) {
                $marcNoUrl = $tds[1]->first('a')->getAttribute('href');
                preg_match('/\?marc_no=(.*)/', $marcNoUrl, $marcNo);
                $marcNo = $marcNo[1] ?? '';
            }

            $books[] = [
                'sn' => $tds[0]->text(),
                'title' => html_entity_decode($tds[1]->first('a')->text()),
                'marcNo' => $marcNo,
                'author' => html_entity_decode($tds[2]->text()),
                'publisher' => html_entity_decode($tds[3]->text()),
                'publishAt' => html_entity_decode($tds[4]->text()),
                'callNo' => html_entity_decode($tds[5]->text()),
            ];
        }

        return [
            'code' => $result['code'],
            'data' => [
                'title' => $shelfName,
                'count' => $count,
                'books' => $books
            ]
        ];
    }
}