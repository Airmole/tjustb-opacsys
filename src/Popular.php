<?php

namespace Airmole\TjustbOpacsys;

use Airmole\TjustbOpacsys\Exception\Exception;
use DiDom\Document;

/**
 * 热门推荐
 */
class Popular extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取热门借阅和热门图书top10
     * @return array[]
     * @throws Exception
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function lendAndPopularTopTen(): array
    {
        $html = $this->httpGet('/opac/ajax_top_lend_shelf.php');
        if ($html['code'] !== 200) throw new Exception('获取失败：'. $html['code'] . $html['data']);
        $document = new Document($html['data']);

        $popularLendBooks = [];
        // 热门借阅
        if ($document->has('#search_container_right')) {
            $popularLendTitle = $document->first('#search_container_right')->first('h3')->text();
            $popularLendItems = $document->first('#search_container_right')->find('li');
            foreach ($popularLendItems as $item) {
                $url = $item->first('a')->getAttribute('href');
                preg_match('/marc_no=(.*)?/', $url, $marcNo);
                $popularLendBooks[] = [
                    'title' => $item->first('a')->text(),
                    'marcNo' => $marcNo[1] ?: '',
                ];
            }
        }

        // 热门图书
        $popularBooks = [];
        if ($document->has('#search_container_center')) {
            $popularBookTitle = $document->first('#search_container_center')->first('h3')->text();
            $popularBookItems = $document->first('#search_container_center')->find('li');
            foreach ($popularBookItems as $item) {
                $url = $item->first('a')->getAttribute('href');
                preg_match('/marc_no=(.*)?/', $url, $marcNo);
                $popularBooks[] = [
                    'title' => $item->first('a')->text(),
                    'marcNo' => $marcNo[1] ?: '',
                ];
            }
        }

        return [
            'lend' => [
                'title' => $popularLendTitle ?: '',
                'books' => $popularLendBooks,
            ],
            'book' => [
                'title' => $popularBookTitle ?: '',
                'books' => $popularBooks,
            ],
        ];
    }

    /**
     * 获取关键词云
     * @return array|mixed
     * @throws Exception
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function keywordCloud()
    {
        $html = $this->httpGet('/opac/ajax_topkeywords_js_adv.php');
        if ($html['code'] !== 200) throw new Exception('获取失败：'. $html['code'] . $html['data']);
        $document = new Document($html['data']);

        $script = $document->find('script')[1]->text();
        preg_match('/eval\(({.*?})\);/s', $script, $jsonString);
        $jsonString = $jsonString[1] ?: '';
        if (empty($jsonString)) return [];
        // 处理 JavaScript 的 function 语法，使其符合 JSON 格式
        $jsonString = preg_replace('/"click":\s*function\([^)]*\)\s*{[^}]*}/', '"click": null', $jsonString);
        // 将单引号替换为双引号以符合 JSON 标准
        $jsonString = str_replace("'", '"', $jsonString);
        $jsonString = $this->stripHtmlTagAndBlankspace($jsonString);
        $jsonString = str_replace('],]', ']]', $jsonString);
        // 解码 HTML 实体
        $jsonString = html_entity_decode($jsonString, ENT_QUOTES, 'UTF-8');
        $data = json_decode($this->stripHtmlTagAndBlankspace($jsonString), true);
        return empty($data) ? [] : $data;
    }

    /**
     * 获取检索关键词top10
     * @return array
     * @throws Exception
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function topTenKeyword()
    {
        $html = $this->httpGet('/opac/ajax_topten_adv.php');
        if ($html['code'] !== 200) throw new Exception('获取失败：'. $html['code'] . $html['data']);
        $document = new Document($html['data']);

        $links = $document->find('a');
        $keywords = [];
        foreach ($links as $link) {
            $keywords[] = [
                'keyword' => $link->text(),
                'url' => $link->getAttribute('href'),
            ];
        }
        return $keywords;
    }

    /**
     * 获取最近30天热门关键词top100
     * @return array
     * @throws Exception
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function topKeyword(): array
    {
        $html = $this->httpGet('/opac/top100_adv.php');
        if ($html['code'] !== 200) throw new Exception('获取失败：'. $html['code'] . $html['data']);
        $document = new Document($html['data']);
        $node = $document->first('.thinBorder')->find('a');
        $keywords = [];
        foreach ($node as $link) {
            $text = $link->text();
            $keyword = preg_replace('/\(\d+\)$/', '', $text);
            preg_match('/\((\d+)\)$/', $text, $count);
            $count = $count[1] ?: 0;
            $keywords[] = [
                'keyword' => trim($keyword),
                'count' => $count,
                'url' => $link->getAttribute('href'),
            ];
        }

        return $keywords;
    }

    /**
     * 获取热门借阅榜
     * @param string $class 书目类别
     * @return array
     * @throws Exception
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function topLend(string $class = ''): array
    {
        $query = '';
        if (!empty($class)) $query = "?cls_no={$class}";
        $html = $this->httpGet("/top/top_lend.php{$query}");
        if ($html['code'] !== 200) throw new Exception('获取失败：'. $html['code'] . $html['data']);
        $document = new Document($html['data']);

        $noteNode = $document->first('.note')->find('strong');
        $noteValues = [];
        foreach ($noteNode as $node) {
            $noteValues[] = $node->text();
        }
        $range = $document->first('.note')->text();
        preg_match('/统计范围：(.*?)统计方式/', $range, $range);
        $range = $range[1] ?: '';
        $range = $this->stripHtmlTagAndBlankspace($range);
        $statistics = [ 'range' => $range, 'sortBy' => $noteValues[1], 'class' => $noteValues[2] ];

        $classNode = $document->first('#underlinemenu')->find('a');
        $classList = [];
        foreach ($classNode as $node) {
            $value = $node->getAttribute('href');
            preg_match('/cls_no=(\w{1,3})/', $value, $value);
            $value = $value[1] ?: '';
            $classList[] = [
                'name' => $node->text(),
                'value' => $value
            ];
        }

        $tableNode = $document->first('.table_line')->find('tr');
        $books = [];
        foreach ($tableNode as $index => $node) {
            if ($index == 0) continue;
            $marcNo = $node->find('td')[1]->first('a')->getAttribute('href');
            preg_match('/marc_no=(.*)?/', $marcNo, $marcNo);
            $marcNo = $marcNo[1] ?: '';
            $publish = $node->find('td')[3]->text();
            $publish = explode(' ', $publish);
            $publisher = $publish[0] ?: '';
            $publishDate = $publish[1] ?: '';
            $books[] = [
                'no' => $node->first('td')->text(),
                'title' => $node->find('td')[1]->text(),
                'marcNo' => $marcNo,
                'publisher' => $publisher,
                'publishDate' => $publishDate,
                'author' => $node->find('td')[2]->text(),
                'callNo' => $node->find('td')[4]->text(), // 索书号
                'collectionNum' => $node->find('td')[5]->text(), // 馆藏数量
                'lendCount' => $node->find('td')[6]->text(), // 借阅册次
                'lendRatio' => $node->find('td')[7]->text(), // 借阅比
            ];
        }

        return [
            'statistics' => $statistics,
            'class' => $classList,
            'books' => $books
        ];
    }

    /**
     * 获取热门评分榜
     * @param string $class 书目类别
     * @param string $sort 显示方式
     * @return array
     * @throws Exception
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function topScore(string $class = '', string $sort = '')
    {
        $query = [];
        if (!empty($sort)) $query['sort'] = $sort;
        if (!empty($class)) $query['cls_no'] = $class;
        $query = '?' . http_build_query($query);
        $html = $this->httpGet("/top/top_score.php{$query}");
        if ($html['code'] !== 200) throw new Exception('获取失败：'. $html['code'] . $html['data']);
        $document = new Document($html['data']);

        $currentClass = $document->first('.note')->first('strong')->text();

        $classNode = $document->first('#underlinemenu')->find('a');
        $classList = [];
        foreach ($classNode as $node) {
            $value = $node->getAttribute('href');
            preg_match('/cls_no=(\w{1,3})/', $value, $value);
            $value = $value[1] ?: '';
            $classList[] = [
                'name' => $node->text(),
                'value' => $value
            ];
        }

        $sortOptionsNode = $document->first('form')->first('select')->find('option');
        $sortOptions = [];
        foreach ($sortOptionsNode as $node) {
            $sortOptions[] = [
                'name' => $node->text(),
                'value' => $node->getAttribute('value'),
                'checked' => $node->hasAttribute('selected')
            ];
        }

        $tableNode = $document->first('.table_line')->find('tr');
        $books = [];
        foreach ($tableNode as $index => $node) {
            if ($index == 0) continue;

            $marcNo = $node->find('td')[1]->first('a')->getAttribute('href');
            preg_match('/marc_no=(.*)?/', $marcNo, $marcNo);
            $marcNo = $marcNo[1] ?: '';
            $publish = $node->find('td')[3]->text();
            $publish = explode(' ', $publish);
            $publisher = $publish[0] ?: '';
            $publishDate = $publish[1] ?: '';
            $score = $node->find('td')[5]->first('img')->getAttribute('src');
            preg_match('/star(\d)\.gif/', $score, $score);
            $score = $score[1] ?: '';

            $books[] = [
                'no' => $node->first('td')->text(),
                'title' => $node->find('td')[1]->text(),
                'marcNo' => $marcNo,
                'author' => $node->find('td')[2]->text(),
                'callNo' => $node->find('td')[4]->text(),
                'publisher' => $publisher,
                'publishDate' => $publishDate,
                'score' => $score,
                'evaluatorNum' => $node->find('td')[6]->text(),
            ];
        }

        return [
            'class' => [
                'current' => $this->stripBlankspace($currentClass),
                'list' => $classList,
            ],
            'sort' => $sortOptions,
            'books' => $books
        ];
    }

    /**
     * 获取热门收藏榜
     * @param string $class 书目类别
     * @return array
     * @throws Exception
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function topStar(string $class = '')
    {
        $query = [];
        if (!empty($class)) $query['cls_no'] = $class;
        $query = '?' . http_build_query($query);

        $html = $this->httpGet("/top/top_shelf.php{$query}");
        if ($html['code'] !== 200) throw new Exception('获取失败：'. $html['code'] . $html['data']);
        $document = new Document($html['data']);
        $currentClass = $document->first('.note')->first('strong')->text();

        $classNode = $document->first('#underlinemenu')->find('a');
        $classList = [];
        foreach ($classNode as $node) {
            $value = $node->getAttribute('href');
            preg_match('/cls_no=(\w{1,3})/', $value, $value);
            $value = $value[1] ?: '';
            $classList[] = [
                'name' => $node->text(),
                'value' => $value
            ];
        }

        $tableNode = $document->first('.table_line')->find('tr');
        $books = [];
        foreach ($tableNode as $index => $node) {
            if ($index == 0) continue;
            $marcNo = $node->find('td')[1]->first('a')->getAttribute('href');
            preg_match('/marc_no=(.*)?/', $marcNo, $marcNo);
            $marcNo = $marcNo[1] ?: '';
            $publish = $node->find('td')[3]->text();
            $publish = explode(' ', $publish);
            $publisher = $publish[0]?: '';
            $publishDate = $publish[1]?: '';
            $books[] = [
                'no' => $node->first('td')->text(),
                'title' => $node->find('td')[1]->text(),
                'marcNo' => $marcNo,
                'author' => $node->find('td')[2]->text(),
                'callNo' => $node->find('td')[4]->text(),
                'publisher' => $publisher,
                'publishDate' => $publishDate,
                'starNum' => $node->find('td')[5]->text(),
            ];
        }

        return [
            'class' => [
                'current' => $this->stripBlankspace($currentClass),
                'list' => $classList,
            ],
            'books' => $books
        ];
    }

    /**
     * 热门图书
     * @param string $class 书目类别
     * @return array
     * @throws Exception
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function topBook(string $class = '')
    {
        $query = [];
        if (!empty($class)) $query['cls_no'] = $class;
        $query = '?' . http_build_query($query);
        $html = $this->httpGet("/top/top_book.php{$query}");
        if ($html['code'] !== 200) throw new Exception('获取失败：'. $html['code'] . $html['data']);
        $document = new Document($html['data']);

        $currentClass = $document->first('.note')->first('strong')->text();
        $classNode = $document->first('#underlinemenu')->find('a');
        $classList = [];
        foreach ($classNode as $node) {
            $value = $node->getAttribute('href');
            preg_match('/cls_no=(\w{1,3})/', $value, $value);
            $value = $value[1] ?: '';
            $classList[] = [
                'name' => $node->text(),
                'value' => $value
            ];
        }

        $tableNode = $document->first('.table_line')->find('tr');
        $books = [];
        foreach ($tableNode as $index => $node) {
            if ($index == 0) continue;
            $marcNo = $node->find('td')[1]->first('a')->getAttribute('href');
            preg_match('/marc_no=(.*)?/', $marcNo, $marcNo);
            $marcNo = $marcNo[1] ?: '';
            $publish = $node->find('td')[3]->text();
            $publish = explode(' ', $publish);
            $publisher = $publish[0]?: '';
            $publishDate = $publish[1]?: '';
            $books[] = [
                'no' => $node->first('td')->text(),
                'title' => $node->find('td')[1]->text(),
                'marcNo' => $marcNo,
                'author' => $node->find('td')[2]->text(),
                'callNo' => $node->find('td')[4]->text(),
                'publisher' => $publisher,
                'publishDate' => $publishDate,
                'viewNum' => $node->find('td')[5]->text(),
            ];
        }

        return [
            'class' => [
                'current' => $this->stripBlankspace($currentClass),
                'list' => $classList,
            ],
            'books' => $books
        ];
    }
}