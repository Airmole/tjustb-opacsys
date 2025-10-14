<?php

namespace Airmole\TjustbOpacsys;

use Airmole\TjustbOpacsys\Exception\Exception;
use DiDom\Document;
use DiDom\Exceptions\InvalidSelectorException;

class Borrow extends Base
{
    /**
     * 当前借阅
     * @return array
     * @throws Exception|InvalidSelectorException
     */
    public function readingBooks(): array
    {
//        $url = '/reader/book_lst.php';
//        $result =$this->httpRequest('GET', $url, '', $this->cookie);
//        if ($result['code'] != self::CODE_SUCCESS) throw new Exception('readingBooks：' . json_encode($result));

        $bookList = [];

        $result['data'] = file_get_contents('/www/tjustb-opacsys/html/test.html');
        $dom = new Document($result['data']);

        $tips = '';
        if ($dom->has('div.alert') && $dom->first('div.alert')->has('strong')) {
            $tips = $dom->first('div.alert')->first('strong')->text();
        }

        $html = $this->stripHtmlTagAndBlankspace($dom->html());
        preg_match('/当前借阅\((.*?)\)/', $html, $nowBorrow);
        $nowBorrow = $nowBorrow[1] ?? 0;
        $nowBorrow = intval($nowBorrow);
        preg_match('/最大借阅\((.*?)\)/', $html, $maxBorrow);
        $maxBorrow = $maxBorrow[1] ?? 0;
        $maxBorrow = intval($maxBorrow);

        $qrcodeValue = '';
        if ($dom->has('a[href^=../opac/ajax_qr.php]')) {
            $qrcodeValue = $dom->first('a[href^=../opac/ajax_qr.php]')->getAttribute('href');
            preg_match('/\.\.\/opac\/ajax_qr\.php\?qrcode=(.*)/', $qrcodeValue, $qrcodeValue);
            $qrcodeValue = $qrcodeValue[1] ?? '';
        }

        if (str_contains($result['data'], '您的该项记录为空')) {
            return [
                'code' => $result['code'] ?? self::CODE_SUCCESS,
                'tips' => $tips,
                'nowBorrow' => $nowBorrow,
                'maxBorrow' => $maxBorrow,
                'bookList' => $bookList,
                'qrcode' => $qrcodeValue,
            ];
        }

        $tableTrs = $dom->first('table.table_line')->find('tr');
        foreach ($tableTrs as $index => $tr) {
            if ($index == 0) continue; // 第一行表头标题跳过
            $tds = $tr->find('td');

            $marcNoUrl = $tds[1]->first('a')->getAttribute('href');
            preg_match('/\?marc_no=(.*)/', $marcNoUrl, $marcNo);
            $marcNo = $marcNo[1] ?? '';

            $author = '';
            if (!empty($tds[1]->lastChild()->text())) $author = $tds[1]->lastChild()->text();
            $author = mb_substr($author, 3);

            $renewParams = [];
            $inputNode = $tds[7]->first('input[onclick^=getInLib]');
            if ($inputNode) {
                $renewParamsString = $inputNode->getAttribute('onclick');
                $renewParamsString = mb_substr($renewParamsString, 9, -2);
                $paramsArray = explode(',', $renewParamsString);
                $renewParams = [
                    'barcode' => mb_substr($paramsArray[0], 1, -1),
                    'check' => mb_substr($paramsArray[1], 1, -1),
                    'num' => mb_substr($paramsArray[2], 1, -1),
                ];
            }

            $bookList[] = [
                'barcode' => $tds[0]->text(),
                'marcNo' => $marcNo,
                'title' => $tds[1]->first('a')->text(),
                'author' => $author,
                'borrowedAt' => $tds[2]->text(),
                'needReturnAt' => $this->stripHtmlTagAndBlankspace($tds[3]->text()),
                'renewCount' => $tds[4]->text(),
                'place' => $tds[5]->text(),
                'attachment' => $tds[6]->text(),
                'renewParams' => $renewParams,
            ];
        }

        return [
            'code' => $result['code'] ?? self::CODE_SUCCESS,
            'tips' => $tips,
            'nowBorrow' => $nowBorrow,
            'maxBorrow' => $maxBorrow,
            'bookList' => $bookList,
            'qrcode' => $qrcodeValue,
        ];
    }

    /**
     * 借阅历史
     * @param int $page 页码
     * @return array
     * @throws Exception
     * @throws InvalidSelectorException
     */
    public function borrowedHistory(int $page = 1): array
    {
        $query = [
            'page' => $page
        ];
        $url = '/reader/book_hist.php?' . http_build_query($query);

        $result =$this->httpRequest('GET', $url, '', $this->cookie);
        if ($result['code'] != self::CODE_SUCCESS) throw new Exception('history：' . json_encode($result));

        $dom = new Document($result['data']);
        $totalPage = $dom->first('font[color=black]')->text();
        $totalPage = $this->stripHtmlTagAndBlankspace($totalPage) ?? 0;
        $totalPage = intval($totalPage);

        $bookList = [];
        $tableTrs = $dom->first('table.table_line')->find('tr');
        foreach ($tableTrs as $index => $tr) {
            if ($index == 0) continue; // 第一行表头标题跳过
            $tds = $tr->find('td');

            $marcNoUrl = $tds[2]->first('a')->getAttribute('href');
            preg_match('/marc_no=(.*)/', $marcNoUrl, $marcNo);
            $marcNo = $marcNo[1] ?? '';
            $bookList[] = [
                'sn' => $this->stripHtmlTagAndBlankspace($tds[0]->text()),
                'barcode' => $this->stripHtmlTagAndBlankspace($tds[1]->text()),
                'marcNo' => $marcNo,
                'title' => html_entity_decode($tds[2]->text()),
                'author' => html_entity_decode($tds[3]->text()),
                'borrowDate' => $tds[4]->text(),
                'returnDate' => $tds[5]->text(),
                'place' => $tds[6]->text(),
            ];
        }

        return ['page' => $page, 'totalPage' => $totalPage, 'bookList' => $bookList];
    }
}