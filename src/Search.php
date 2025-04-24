<?php

namespace Airmole\TjustbOpacsys;

use Airmole\TjustbOpacsys\Exception\Exception;
use DiDom\Document;

/**
 * 图书检索相关
 */
class Search extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 高级检索参数
     * @return array
     * @throws Exception
     */
    public function advancedSearchParams(): array
    {
        $json = $this->httpGet('/opac/ajax_adv_info.php');
        if ($json['code'] !== 200) throw new Exception('获取失败：' . $json['code'] . $json['data']);
        return json_decode($json['data'], true);
    }

    /**
     * 高级检索
     * @param string $search 检索关键字或检索JSON参数
     * @param int $page 页码（覆盖JSON参数）
     * @param int $pageSize 每页数量（覆盖JSON参数）
     * @return array
     * @throws Exception
     */
    public function advancedSearch(string $search, int $page = 1, int $pageSize = 20): array
    {
        if (empty($search)) throw new Exception('检索关键字或检索JSON参数不能为空');
        // 传入检索参数是否为JSON
        // 检索JSON参数实例值：https://gist.github.com/Airmole/3d60535ecfda867f9ed1be553daaa787
        json_decode($search);
        // 传入检索关键字，非检索JSON
        if (json_last_error() !== JSON_ERROR_NONE) {
            $search = [
                'searchWords' => [ ['fieldList' => [ ['fieldCode' => '', 'fieldValue' => $search] ] ] ],
                'filters' => [],
                'limiter' => [],
                'sortField' => 'relevance',
                'sortType' => 'desc',
                'pageSize' => $pageSize,
                'pageCount' => $page,
                'locale' => '',
                'first' => true
            ];
        } else {
            $search = json_decode($search, true);
            $search['pageCount'] = $page;
            $search['pageSize'] = $pageSize;
        }

        $json = $this->httpPost('/opac/ajax_search_adv.php', $search);
        if ($json['code'] !== 200) throw new Exception('获取失败：' . $json['code'] . $json['data']);
        return json_decode($json['data'], true);
    }

    /**
     * 检索参数
     * @return array
     * @throws Exception
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function searchParams(): array
    {
        $html = $this->httpGet('/opac/search.php');
        if ($html['code'] !== 200) throw new Exception('获取失败：' . $html['code'] . $html['data']);
        $dom = new Document($html['data']);

        $form = $dom->first('form');
        $selectNodes = $form->find('select');
        $selects = [];
        foreach ($selectNodes as $selectNode) {
            $options = $selectNode->find('option');
            foreach ($options as $option) {
                $selects[$selectNode->attr('name')][] = [
                    'value' => $option->attr('value'),
                    'text' => $this->stripHtmlTagAndBlankspace($option->text())
                ];
            }
        }

        $historyCountNode = $dom->first('#historyCount');
        $historyCount = [
            'name' => $historyCountNode->attr('name'),
            'value' => $historyCountNode->attr('value')
        ];

        $docTypeNode = $dom->find('input[name=doctype]');
        $docType = [];
        foreach ($docTypeNode as $node) {
            $docType[] = [
                'name' => $node->attr('name'),
                'value' => $node->attr('value'),
                'checked' => $node->hasAttribute('checked') ?? false
            ];
        }

        $ebookNode = $dom->first('input[name=with_ebook]');
        $ebook = [
            'name' => $ebookNode->attr('name'),
            'checked' => $ebookNode->hasAttribute('checked') ?? false
        ];

        $showModeNode = $dom->find('input[name=showmode]');
        $showMode = [];
        foreach ($showModeNode as $node) {
            $showMode[] = [
                'name' => $node->attr('name'),
                'value' => $node->attr('value'),
                'checked' => $node->hasAttribute('checked') ?? false
            ];
        }

        $orderByNode = $dom->find('input[name=orderby]');
        $orderBy = [];
        foreach ($orderByNode as $node) {
            $orderBy[] = [
                'name' => $node->attr('name'),
                'value' => $node->attr('value'),
                'checked' => $node->hasAttribute('checked') ?? false
            ];
        }

        $csrfTokenNode = $dom->first('#csrf_token');
        $csrfToken = [
            'name' => $csrfTokenNode->attr('name'),
            'value' => $csrfTokenNode->attr('value')
        ];


        return array_merge([
            'historyCount' => $historyCount,
            'docType' => $docType,
            'ebook' => $ebook,
            'showMode' => $showMode,
            'orderBy' => $orderBy,
            'csrfToken' => $csrfToken
        ], $selects);
    }

    /**
     * 多字段检索参数
     * @return array
     * @throws Exception
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function searchParamsMore(): array
    {
        $html = $this->httpGet('/opac/search_more.php');
        if ($html['code'] !== 200) throw new Exception('获取失败：' . $html['code'] . $html['data']);
        $dom = new Document($html['data']);

        $form = [];
        $formNode = $dom->first('form');

        // text、select、radio、hidden 四种类型
        // text
        $textNodes = $formNode->find('input[type=text]');
        foreach ($textNodes as $textNode) {
            $form[] = [
                'type' => 'text',
                'name' => $textNode->attr('name'),
                'value' => $textNode->attr('value') ?: ''
            ];
        }
        // select
        $selectNodes = $formNode->find('select');
        foreach ($selectNodes as $selectNode) {
            $selectName = $selectNode->attr('name');
            $optionNodes = $selectNode->find('option');
            $options = [];
            foreach ($optionNodes as $optionNode) {
                $options[] = [
                    'value' => $optionNode->attr('value'),
                    'text' => $this->stripHtmlTagAndBlankspace($optionNode->text())
                ];
            }
            $form[] = [
                'type' => 'select',
                'name' => $selectName,
                'options' => $options
            ];
        }
        // radio
        $radioNodes = $formNode->find('input[type=radio]');
        $radios = [];
        foreach ($radioNodes as $radioNode) {
            $radioName = $radioNode->attr('name');
            $radios[$radioName][] = [
                'value' => $radioNode->attr('value'),
                'checked' => $radioNode->hasAttribute('checked') ?? false
            ];
        }
        foreach ($radios as $radioName => $radio) {
            $form[] = [
                'type' => 'radio',
                'name' => $radioName,
                'options' => $radio
            ];
        }
        // hidden
        $hiddenNodes = $formNode->find('input[type=hidden]');
        foreach ($hiddenNodes as $hiddenNode) {
            $form[] = [
                'type' => 'hidden',
                'name' => $hiddenNode->attr('name'),
                'value' => $hiddenNode->attr('value')
            ];
        }

        return $form;
    }

    /**
     * 简单检索
     * @param mixed $search 检索关键字或检索结构
     * @param int $page 页码
     * @param int $pageSize 每页数量
     * @param string $secondaryKeyword 二次检索关键字
     * @param string $secondarySearchType 二次检索方式
     * @return array
     * @throws Exception
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function search(
        mixed $search,
        int $page = 1,
        int $pageSize = 20,
        string $secondaryKeyword = '',
        string $secondarySearchType = 'title'
    )
    {
        if (is_string($search)) {
            $search = [
                'strSearchType' => 'title',
                'match_flag' => 'forward',
                'historyCount' => 1,
                'strText' => $search,
                'doctype' => 'ALL',
                'displaypg' => $pageSize,
                'showmode' => 'list',
                'sort' => 'CATA_DATE',
                'orderby' => 'desc',
                'location' => 'ALL',
                'page' => $page
            ];
        }
        if (is_array($search)) {
            $search = array_merge([ 'displaypg' => $pageSize, 'page' => $page ], $search);
            // 在结果中二次检索
            if ($secondaryKeyword !== '') {
                $search = array_merge($search, [
                    's2_text' => $secondaryKeyword,
                    's2_type' => $secondarySearchType,
                    'search_bar' => 'result',
                    'title' => $search['strText']
                ]);
                unset($search['strText']);
                unset($search['page']);
                unset($search['displaypg']);
                unset($search['strSearchType']);
            }
            $search = http_build_query($search);
        }

        $html = $this->httpGet("/opac/openlink.php?$search");
        if ($html['code'] !== 200) throw new Exception('获取失败：' . $html['code'] . $html['data']);

        $dom = new Document($html['data']);
        $dlNodes = $dom->find('dl');
        $range = [];
        foreach ($dlNodes as $dlIndex => $dlNode) {
            $key = 'class';
            if ($dlIndex == 1) $key = 'doctype';
            if ($dlIndex == 2) $key = 'location';
            if ($dlIndex == 3) $key = 'subject';
            $ddNodes = $dlNode->find('dd');
            foreach ($ddNodes as $ddNode) {
                $link = $ddNode->first('a');
                if (empty($link)) continue;
                $range[$key][] = [
                    'text' => $this->stripHtmlTagAndBlankspace($ddNode->text()),
                    'value' => $link->attr('href')
                ];
            }
        }

        $pagination = [];
        if ($dom->has('.book_article') && $dom->has('.pagination>font')) {
            $total = $dom->first('.book_article')->first('.red')->text();
            $currentPage = $dom->first('.pagination')->first('font[color=red]')->text();
            $totalPage = $dom->first('.pagination')->first('font[color=black]')->text();
            $pagination = [
                'total' => $total,
                'currentPage' => $currentPage,
                'totalPage' => $totalPage,
            ];
        }

        $listNodes = [];
        if ($dom->has('#search_book_list')) {
            $listNodes = $dom->first('#search_book_list')->find('.book_list_info');
        }
        $books = [];
        foreach ($listNodes as $listNode) {
            $docutype = $listNode->first('h3')->first('span')->text();
            $url = $listNode->first('h3')->first('a')->attr('href');
            preg_match('/marc_no=(.*?)&/', $url, $marcNo);
            $marcNo = $marcNo[1] ?? '';
            $title = $listNode->first('h3')->first('a')->text();
            preg_match('/(\d+)\./', $title, $no);
            $no = intval(trim($no[1] ?? ''));
            preg_match('/\d+\.(.*?)$/', $title, $title);
            $title = trim($title[1] ?? '');
            $callNo = $listNode->first('h3')->lastChild()->text();
            $NumNode = $listNode->first('p')->first('span')->text();
            preg_match('/馆藏复本：(.*?) /', $NumNode, $collection);
            $collection = $collection[1] ?? '';
            preg_match('/可借复本：(.*?)$/', $NumNode, $lendable);
            $lendable = $lendable[1] ?? '';
            $author = $listNode->first('p')->first('span')->nextSibling()->text();
            $publish = $listNode->first('p')->first('span')->nextSibling()->nextSibling()->nextSibling()->text();
            $publish = urlencode($this->stripHtmlTagAndBlankspace($publish));
            $publish = str_replace('%C2%A0', '丨', $publish);
            $publish = explode('丨', $publish);
            $publisher = urldecode($publish[0] ?? '');
            $publishDate = urldecode($publish[1] ?? '');
            $score = $listNode->first('p')->first('img')->attr('src');
            preg_match('/star(\d).gif/', $score, $score);
            $score = intval($score[1] ?? 0);
            $scorePeople = $listNode->first('p')->first('img')->nextSibling()->text();
            $scorePeople = intval($scorePeople);
            $books[] = [
                'no' => $no,                   // 序号
                'marcNo' => $marcNo,           // Marc机器码
                'title' => $title,             // 标题
                'callNo' => trim($callNo),     // 索书号
                'docutype' => $docutype,       // 书目类别
                'collection' => $collection,   // 馆藏复本
                'lendable' => $lendable,       // 可借复本
                'author' => trim($author),     // 编著者
                'publisher' => $publisher,     // 出版社
                'publishDate' => $publishDate, // 出版时间
                'score' =>$score,              // 评分
                'scorePeople' => $scorePeople  // 评分人数
            ];
        }

        return [
            'range' => $range,
            'pagination' => $pagination,
            'books' => $books
        ];
    }

    /**
     * 根据marcNo获取图书借阅信息
     * @param string $marcNo 图书marcNo码
     * @return array
     * @throws Exception
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function bookLendInfo(string $marcNo): array
    {
        $html = $this->httpGet("/opac/ajax_item.php?marc_no=$marcNo");
        if ($html['code'] !== 200) throw new Exception('获取失败：' . $html['code'] . $html['data']);
        $dom = new Document($html['data']);
        $trNodes = $dom->find('tr');
        $lendInfo = [];
        foreach ($trNodes as $index => $trNode) {
            if ($index === 0) continue;
            $tdNodes = $trNode->find('td');
            if (count($tdNodes) <= 0) continue;
            $lendInfo[] = [
                'callNo' => trim($tdNodes[0]->text()),
                'barCode' => $tdNodes[1]->text(),
                'volume' => trim($tdNodes[2]->text()),
                'location' => trim($tdNodes[3]->text()),
                'status' => $tdNodes[4]->text(),
            ];
        }
        return $lendInfo;
    }

    /**
     * 获取图书详情
     * @param string $marcNo
     * @return array
     * @throws Exception
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function detail(string $marcNo): array
    {
        $html = $this->httpGet("/opac/item.php?marc_no=$marcNo");
        if ($html['code'] !== 200) throw new Exception('获取失败：' . $html['code'] . $html['data']);
        $dom = new Document($html['data']);

        // 二维码内容
        $qrcode = '';
        $qrcodeNode = $dom->first('a[href*=ajax_qr.php]');
        if (!empty($qrcodeNode)) {
            $qrcode = $qrcodeNode->attr('href');
            $qrcode = str_replace('ajax_qr.php?qrcode=', '', $qrcode);
        }
        // 相关书架
        $shelf = [];
        $shelfNodes = $dom->find('a[href*=show_user_shelf.php]');
        foreach ($shelfNodes as $shelfNode) {
            $text = $shelfNode->text();
            $link = $shelfNode->attr('href');
            $name = '';
            preg_match('/(.*?)\(/', $text, $name);
            $name = $name[1] ?? '';
            $count = 0;
            preg_match('/\((\d*?)\)/', $text, $count);
            $count = intval($count[1] ?? 0);
            $shelfId = '';
            preg_match('/show_user_shelf\.php\?classid=(.*?)$/', $link, $shelfId);
            $shelf[] = [
                'name' => $name,
                'count' => $count,
                'id' => $shelfId[1] ?? '',
            ];
        }
        // marcStatus
        $marcNode = $dom->first('#marc');
        $marcText = $this->stripHtmlTagAndBlankspace($marcNode->text());
        $marcStatus = '';
        preg_match('/MARC状态：(.*?)文献类型/', $marcText, $marcStatus);
        $marcStatus = $marcStatus[1] ?? '';
        $docType = '';
        preg_match('/文献类型：(.*?)浏览次数/', $marcText, $docType);
        $docType = $docType[1] ?? '';
        if (empty($docType)) return [];
        $viewed = 0;
        preg_match('/浏览次数：(.*?)$/', $marcText, $viewed);
        $viewed = intval($viewed[1] ?? 0);
        // marc_format
        $marcFormatNode = $dom->first('#tabs1')->first('a[href*=show_format_marc.php]');
        $marcFormatNo = '';
        if (!empty($marcFormatNode)) {
            $marcFormatNo = $marcFormatNode->attr('href');
            $marcFormatNo = str_replace('show_format_marc.php?marc_no=', '', $marcFormatNo);
        }
        // marcNo
        $marcNo = $dom->first('#marc_no')->attr('value');
        // isbn
        preg_match('/"ajax_douban.php\?isbn=(.*?)"/m', $html['data'], $isbn);
        $isbn = $isbn[1] ?? '';
        // intro
        $intro = [];
        $introNode = $dom->first('#item_detail');
        $dlNodes = $introNode->find('dl');
        foreach ($dlNodes as $dlNode) {
            $title = $dlNode->first('dt')->text();
            $content = $dlNode->first('dd')->text();
            if (empty($this->stripHtmlTagAndBlankspace($title)) || empty($this->stripHtmlTagAndBlankspace($content))) continue;
            $intro[] = [
                'title' => trim($title),
                'content' => trim($content)
            ];
        }
        // 期刊列表
        $journals = [];
        $journalNode = $dom->first('#accordion');
        $journalListNodes = $journalNode->find('h3');
        foreach ($journalListNodes as $journalListNode) {
            $listNode = $journalListNode->nextSibling()->nextSibling();
            $trNodes = $listNode->find('tr');
            $list = [];
            foreach ($trNodes as $trIndex => $trNode) {
                if ($trIndex === 0) continue;
                $tdNodes = $trNode->find('td');
                $list[] = [
                    'location' => $tdNodes[0]->text(),
                    'volume' => $tdNodes[1]->text(),
                    'period' => $tdNodes[2]->text(),
                    'totalPeriod' => $tdNodes[3]->text(),
                    'shelfPosition' => $tdNodes[4]->text(),
                    'status' => $tdNodes[5]->text()
                ];
            }
            $journals[] = [
                'name' => str_replace('(点击展开详细)', '', $journalListNode->text()),
                'list' => $list
            ];
        }

        // 馆藏信息
        $collection = [];
        $collectionNode = $dom->first('#item');
        $trNodes = $collectionNode->find('tr');
        foreach ($trNodes as $trIndex => $trNode) {
            if ($trIndex === 0) continue;
            $tdNodes = $trNode->find('td');
            $collection[] = [
                'callNo' => $tdNodes[0]->text(),
                'barCode' => $tdNodes[1]->text(),
                'volume' => $tdNodes[2]->text(),
                'location' => $this->stripHtmlTagAndBlankspace($tdNodes[3]->text()),
                'status' => trim($tdNodes[4]->text()),
                'returnLocation' => count($tdNodes) >= 6 ? $tdNodes[5]->text() : '',
            ];
        }

        return [
            'marcNo' => $marcNo,
            'marcStatus' => $marcStatus,
            'marcFormatNo' => $marcFormatNo,
            'isbn' => $isbn,
            'docType' => $docType,
            'viewed' => $viewed,
            'intro' => $intro,
            'collection' => $collection,
            'shelf' => $shelf,
            'journals' => $journals,
            'qrcode' => $qrcode // base64编码后再url编码
        ];
    }

    /**
     * MARC机读格式
     * @param string $marcFormatNo
     * @return array
     * @throws Exception
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function marcFormat(string $marcFormatNo): array
    {
        $html = $this->httpGet("/opac/show_format_marc.php?marc_no=$marcFormatNo");
        if ($html['code'] !== 200) throw new Exception('获取失败：' . $html['code'] . $html['data']);
        $dom = new Document($html['data']);
        $marcFormat = [];
        $ulNodes = $dom->find('ul');
        foreach ($ulNodes as $ulNode) {
            $title = $ulNode->first('li')->first('b')->text();
            $marcFormat[] = [
                'title' => $title,
                'content' => str_replace($title, '', $ulNode->first('li')->text())
            ];
        }

        return $marcFormat;
    }

    /**
     * 获取图书评论
     * @param string $marcNo
     * @return array
     * @throws Exception
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function bookComment(string $marcNo): array
    {
        $html = $this->httpGet("/opac/ajax_review_list.php?marc_no=$marcNo");
        if ($html['code'] !== 200) throw new Exception('获取失败：' . $html['code'] . $html['data']);
        $dom = new Document($html['data']);

        $comment = [];
        $commentNodes = $dom->first('.comment_line')->parent()->parent()->find('li');
        foreach ($commentNodes as $commentNode) {
            $likeNum = $commentNode->first('span[id*=support]')->text();
            $dislikeNum = $commentNode->first('span[id*=against]')->text();
            $comment[] = [
                'nickname' => $commentNode->first('.comment_name')->text(),
                'content' => $commentNode->first('.comment_line')->text(),
                'likeNum' => intval($likeNum),
                'dislikeNum' => intval($dislikeNum),
                'time' => $commentNode->first('h5')->find('span')[5]->text()
            ];
        }

        return $comment;
    }

    /**
     * 相关借阅
     * @param string $marcNo
     * @param string $callNo
     * @return array
     * @throws Exception
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function relatedLend(string $marcNo, string $callNo): array
    {
        $html = $this->httpGet("/opac/ajax_lend_related.php?marc_no=$marcNo&call_no=$callNo");
        if ($html['code'] !== 200) throw new Exception('获取失败：' . $html['code'] . $html['data']);
        $dom = new Document($html['data']);

        $books = [];
        $trNodes = $dom->find('tr');
        foreach ($trNodes as $trIndex => $trNode) {
            if ($trIndex === 0) continue;
            $tdNodes = $trNode->find('td');
            $link = $tdNodes[0]->first('a')->attr('href');
            $books[] = [
                'title' => $tdNodes[0]->text(),
                'marcNo' => str_replace('item.php?marc_no=', '', $link),
                'author' => $tdNodes[1]->text(),
                'publisher' => $tdNodes[2]->text(),
                'callNo' => $tdNodes[3]->text(),
            ];
        }
        return $books;
    }

    /**
     * 相关收藏
     * @param string $marcNo
     * @param string $callNo
     * @return array
     * @throws Exception
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function relatedFavour(string $marcNo, string $callNo): array
    {
        $html = $this->httpGet("/opac/ajax_shelf_related.php?marc_no=$marcNo&call_no=$callNo");
        if ($html['code'] !== 200) throw new Exception('获取失败：' . $html['code'] . $html['data']);
        $dom = new Document($html['data']);

        $books = [];
        $trNodes = $dom->find('tr');
        foreach ($trNodes as $trIndex => $trNode) {
            if ($trIndex === 0) continue;
            $tdNodes = $trNode->find('td');
            $link = $tdNodes[0]->first('a')->attr('href');
            $books[] = [
                'title' => $tdNodes[0]->text(),
                'marcNo' => str_replace('item.php?marc_no=', '', $link),
                'author' => $tdNodes[1]->text(),
                'publisher' => $tdNodes[2]->text(),
                'callNo' => $tdNodes[3]->text(),
            ];
        }

        return $books;
    }

    /**
     * 同作者图书
     * @param string $marcNo
     * @return array
     * @throws Exception
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function sameAuthor(string $marcNo):array
    {
        $html = $this->httpGet("/opac/ajax_same_author.php?marc_no=$marcNo");
        if ($html['code'] !== 200) throw new Exception('获取失败：' . $html['code'] . $html['data']);
        $dom = new Document($html['data']);

        $books = [];
        $trNodes = $dom->find('tr');
        foreach ($trNodes as $trIndex => $trNode) {
            if (str_contains($trNode->text(), '暂无数据')) continue;
            if ($trIndex === 0) continue;
            $tdNodes = $trNode->find('td');
            $link = $tdNodes[0]->first('a')->attr('href');
            $books[] = [
                'title' => $tdNodes[0]->text(),
                'marcNo' => str_replace('item.php?marc_no=', '', $link),
                'publisher' => $tdNodes[1]->text(),
                'publishDate' => $tdNodes[2]->text(),
                'callNo' => $tdNodes[3]->text(),
            ];
        }
        return $books;
    }

    /**
     * 借阅趋势
     * @param string $marcNo
     * @return array
     * @throws Exception
     */
    public function lendTrend(string $marcNo): array
    {
        $json = $this->httpGet("/opac/ajax_lend_trend.php?id=$marcNo");
        if ($json['code'] !== 200) throw new Exception('获取失败：' . $json['code'] . $json['data']);
        return json_decode($json['data'], true);
    }

}