<?php

namespace Airmole\TjustbOpacsys;

use Airmole\TjustbOpacsys\Exception\Exception;
use DiDom\Document;

/**
 * 信息发布
 */
class InfoPublish extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 超期催还
     * @param int $page
     * @return array
     * @throws Exception
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function overdue(int $page = 1): array
    {
        $html = $this->httpGet("/info/hasten_return_bulletin.php?page=$page");
        if ($html['code'] != 200) throw new Exception('获取超期催还失败：' . $html['code'] . $html['data']);

        $data = [];
        $dom = new Document($html['data']);
        $trNodes = $dom->first('.table_line')->find('tr');
        foreach ($trNodes as $trIndex => $trNode) {
            if ($trIndex == 0) continue;
            $tdNodes = $trNode->find('td');
            $data[] = [
                'code' => $tdNodes[0]->text(),
                'name' => $tdNodes[1]->text(),
                'department' => $tdNodes[2]->text(),
            ];
            $data[] = [
                'code' => $tdNodes[4]->text(),
                'name' => $tdNodes[5]->text(),
                'department' => $tdNodes[6]->text(),
            ];
        }

        $pageNode = $dom->first('.numstyle')->find('font');
        $currentPage = $pageNode[0]->text();
        $totalPage = $pageNode[1]->text();

        return [
            'pagination' => [
                'current' => intval($currentPage),
                'total' => intval($totalPage)
            ],
            'data' => $data
        ];
    }

    /**
     * 超期欠款
     * @param int $page 页码
     * @return array
     * @throws Exception
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function exceedFine(int $page = 1): array
    {
        $html = $this->httpGet("/info/exceed_fine_bulletin.php?page=$page");
        if ($html['code'] != 200) throw new Exception('获取超期欠款失败：' . $html['code'] . $html['data']);

        $dom = new Document($html['data']);
        $trNodes = $dom->first('.table_line')->find('tr');
        $data = [];
        foreach ($trNodes as $trIndex => $trNode) {
            if ($trIndex == 0) continue;
            $tdNodes = $trNode->find('td');
            $data[] = [
                'code' => $tdNodes[0]->text(),
                'name' => $tdNodes[1]->text(),
                'department' => $tdNodes[2]->text(),
            ];
            $data[] = [
                'code' => $tdNodes[4]->text(),
                'name' => $tdNodes[5]->text(),
                'department' => $tdNodes[6]->text(),
            ];
        }
        $pageNode = $dom->first('.numstyle')->find('font');
        $currentPage = $pageNode[0]->text();
        $totalPage = $pageNode[1]->text();
        return [
            'pagination' => [
                'current' => intval($currentPage),
                'total' => intval($totalPage)
            ],
            'data' => $data
        ];
    }

    /**
     * 查询超期催还
     * @param string $user 证件号或者读者条码号
     * @param string $type 证件类型：certid丨redrid
     * @return array
     * @throws Exception
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function overdueQuery(string $user, string $type = 'certid'): array
    {
        $html = $this->httpGet("/info/info_search.php?s_type=$type&q=$user&submit=%E6%A3%80%E7%B4%A2");
        if ($html['code'] != 200) throw new Exception('查询超期催还失败：' . $html['code'] . $html['data']);
        $dom = new Document($html['data']);


        if (str_contains($dom->first('.panel1')->text(), '该读者不存在')) return [];

        $type = $dom->first('.panel1')->first('p')->firstChild()->text();
        $user = $dom->first('.panel1')->first('p')->first('font')->text();
        $bNodes = $dom->first('.panel1')->find('b');

        return [
            'type' => trim(str_replace('=', '', $type)),
            'user' => $user,
            'fineCount'    => intval($bNodes[0]->text()),
            'fineSum'      => $bNodes[1]->text(),
            'overdueCount' => intval($bNodes[2]->text())
        ];
    }
}