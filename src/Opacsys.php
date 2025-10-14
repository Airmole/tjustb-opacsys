<?php

namespace Airmole\TjustbOpacsys;

use Airmole\TjustbOpacsys\Exception\Exception;
use DiDom\Exceptions\InvalidSelectorException;

/**
 * Opacsys 主类
 */
class Opacsys
{
    /**
     * @var string 用户账号
     */
    public string $usercode;

    /**
     * @var string 系统访问Cookie
     */
    public string $cookie;

    /**
     * 获取热门借阅和热门图书top10
     * @return array
     * @throws Exception
     * @throws InvalidSelectorException
     */
    public function lendAndPopularTopTen(): array
    {
        $popular = new Popular();
        return $popular->lendAndPopularTopTen();
    }

    /**
     * 获取关键词云
     * @return array
     * @throws Exception
     * @throws InvalidSelectorException
     */
    public function keywordCloud(): array
    {
        $popular = new Popular();
        return $popular->keywordCloud();
    }

    /**
     * 获取检索关键词top10
     * @return array
     * @throws Exception
     * @throws InvalidSelectorException
     */
    public function topTenKeyword(): array
    {
        $popular = new Popular();
        return $popular->topTenKeyword();
    }

    /**
     * 获取热门关键词榜
     * @return array
     * @throws Exception
     * @throws InvalidSelectorException
     */
    public function topKeyword(): array
    {
        $popular = new Popular();
        return $popular->topKeyword();
    }

    /**
     * 借阅榜
     * @param string $class
     * @return array
     * @throws Exception
     * @throws InvalidSelectorException
     */
    public function topLend(string $class = ''): array
    {
        $popular = new Popular();
        return $popular->topLend($class);
    }

    /**
     * 评分榜
     * @param string $class
     * @param string $sort
     * @return array
     * @throws Exception
     * @throws InvalidSelectorException
     */
    public function topScore(string $class = '', string $sort = ''): array
    {
        $popular = new Popular();
        return $popular->topScore($class, $sort);
    }

    /**
     * 收藏榜
     * @param string $class
     * @return array
     * @throws Exception
     * @throws InvalidSelectorException
     */
    public function topStar(string $class = ''): array
    {
        $popular = new Popular();
        return $popular->topStar($class);
    }

    /**
     * 浏览榜
     * @param string $class
     * @return array
     * @throws Exception
     * @throws InvalidSelectorException
     */
    public function topBook(string $class = ''): array
    {
        $popular = new Popular();
        return $popular->topBook($class);
    }

    /**
     * 高级检索参数
     * @return array
     * @throws Exception
     */
    public function advancedSearchParams(): array
    {
        $searchClass = new Search();
        return $searchClass->advancedSearchParams();
    }

    /**
     * 高级检索
     * @param string $search
     * @param int $page
     * @param int $pageSize
     * @return array
     * @throws Exception
     */
    public function advancedSearch(string $search = '', int $page = 1, int $pageSize = 20): array
    {
        $searchClass = new Search();
        return $searchClass->advancedSearch($search, $page, $pageSize);
    }

    /**
     * 高级检索参数
     * @return array
     * @throws Exception
     * @throws InvalidSelectorException
     */
    public function searchParams(): array
    {
        $searchClass = new Search();
        return $searchClass->searchParams();
    }

    /**
     * 多字段检索参数
     * @return array
     * @throws Exception
     * @throws InvalidSelectorException
     */
    public function searchParamsMore(): array
    {
        $searchClass = new Search();
        return $searchClass->searchParamsMore();
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
     * @throws InvalidSelectorException
     */
    public function search(
        mixed  $search,
        int    $page = 1,
        int    $pageSize = 20,
        string $secondaryKeyword = '',
        string $secondarySearchType = 'title'
    ): array
    {
        $searchClass = new Search();
        return $searchClass->search($search, $page, $pageSize, $secondaryKeyword, $secondarySearchType);
    }

    /**
     * 获取图书借阅信息
     * @param string $marcNo
     * @return array
     * @throws Exception
     * @throws InvalidSelectorException
     */
    public function bookLendInfo(string $marcNo): array
    {
        $search = new Search();
        return $search->bookLendInfo($marcNo);
    }

    /**
     * 获取图书详情
     * @param string $marcNo
     * @return array
     * @throws Exception
     * @throws InvalidSelectorException
     */
    public function detail(string $marcNo): array
    {
        $search = new Search();
        return $search->detail($marcNo);
    }

    /**
     * MARC机读格式
     * @param string $marcFormatNo
     * @return array
     * @throws Exception
     * @throws InvalidSelectorException
     */
    public function marcFormat(string $marcFormatNo): array
    {
        $search = new Search();
        return $search->marcFormat($marcFormatNo);
    }

    /**
     * 获取图书评论
     * @param string $marcNo
     * @return array
     * @throws Exception
     * @throws InvalidSelectorException
     */
    public function bookComment(string $marcNo): array
    {
        $search = new Search();
        return $search->bookComment($marcNo);
    }


    /**
     * 相关借阅
     * @param string $marcNo 图书marcNo码
     * @param string $callNo 索书号
     * @return array
     * @throws Exception
     * @throws InvalidSelectorException
     */
    public function relatedLend(string $marcNo, string $callNo): array
    {
        $search = new Search();
        return $search->relatedLend($marcNo, $callNo);
    }

    /**
     * 相关收藏
     * @param string $marcNo
     * @param string $callNo
     * @return array
     * @throws Exception
     * @throws InvalidSelectorException
     */
    public function relatedFavour(string $marcNo, string $callNo): array
    {
        $search = new Search();
        return $search->relatedFavour($marcNo, $callNo);
    }

    /**
     * 同作者图书
     * @param string $marcNo
     * @return array
     * @throws Exception
     * @throws InvalidSelectorException
     */
    public function sameAuthor(string $marcNo): array
    {
        $search = new Search();
        return $search->sameAuthor($marcNo);
    }

    /**
     * 借阅趋势
     * @param string $marcNo
     * @return array
     * @throws Exception
     */
    public function lendTrend(string $marcNo): array
    {
        $search = new Search();
        return $search->lendTrend($marcNo);
    }

    /**
     * 超期催还
     * @param int $page 页码
     * @return array
     * @throws Exception
     * @throws InvalidSelectorException
     */
    public function overdue(int $page = 1): array
    {
        $infoPublish = new InfoPublish();
        return $infoPublish->overdue($page);
    }


    /**
     * 超期催还查询
     * @param string $user 证件号或者读者条码号
     * @param string $type 证件类型：certid丨redrid
     * @return array
     * @throws Exception
     * @throws InvalidSelectorException
     */
    public function overdueQuery(string $user, string $type = 'certid'): array
    {
        $infoPublish = new InfoPublish();
        return $infoPublish->overdueQuery($user, $type);
    }

    /**
     * 超期欠款
     * @param int $page 页码
     * @return array
     * @throws Exception
     * @throws InvalidSelectorException
     */
    public function exceedFine(int $page = 1): array
    {
        $infoPublish = new InfoPublish();
        return $infoPublish->exceedFine($page);
    }

    /**
     * 书架详情
     * @param string $shelfId 书架ID
     * @return array
     * @throws Exception
     * @throws InvalidSelectorException
     */
    public function shelf(string $shelfId): array
    {
        $search = new Search();
        return $search->shelf($shelfId);
    }

    /**
     * 通过ISBN获取豆瓣ID
     * @param string $isbn
     * @return string
     * @throws Exception
     */
    public function getDoubanIdByISBN(string $isbn): string
    {
        $search = new Search();
        return $search->getDoubanIdByISBN($isbn);
    }

    /**
     * SSO登录
     * @param string $ticket
     * @return array
     * @throws Exception
     */
    public function ssoLogin(string $ticket): array
    {
        $login = new Login();
        $result = $login->ssoLogin($ticket);
        if ($result['code'] == Base::CODE_SUCCESS) {
            $this->cookie = $result['cookie'];
        }
        return $result;
    }

    /**
     * 登录后的首页信息
     * @return array
     * @throws Exception
     */
    public function loginedIndexProfile(): array
    {
        if (empty($this->cookie)) throw new Exception('未登录设置Cookie');
        $login = new Profile();
        $login->usercode = $this->usercode;
        $login->cookie = $this->cookie;
        return $login->loginedIndexProfile();
    }

    /**
     * 获取已登录账户资料&借阅规则
     * @param string $type
     * @return array
     * @throws Exception
     */
    public function readerStatistics(string $type = 'class'): array
    {
        if (empty($this->cookie)) throw new Exception('未登录设置Cookie');
        $login = new Profile();
        $login->usercode = $this->usercode;
        $login->cookie = $this->cookie;
        return $login->readerStatistics($type);
    }

    /**
     * 获取已登录账户资料&借阅规则
     * @return array
     * @throws Exception
     */
    public function profileRule(): array
    {
        if (empty($this->cookie)) throw new Exception('未登录设置Cookie');
        $login = new Profile();
        $login->usercode = $this->usercode;
        $login->cookie = $this->cookie;
        return $login->profileRule();
    }

    /**
     * 获取借阅规则详情
     * @param string $ruleNo 规则编号
     * @return array
     * @throws InvalidSelectorException|Exception
     */
    public function ruleDetail(string $ruleNo): array
    {
        if (empty($this->cookie)) throw new Exception('未设置Cookie');
        $login = new Profile();
        $login->usercode = $this->usercode;
        $login->cookie = $this->cookie;
        return $login->ruleDetail($ruleNo);
    }

    /**
     * 获取正在借阅的图书
     * @return array
     * @throws Exception|InvalidSelectorException
     */
    public function readingBooks(): array
    {
        if (empty($this->cookie)) throw new Exception('未登录设置Cookie');
        $borrow = new Borrow();
        $borrow->cookie = $this->cookie;
        return $borrow->readingBooks();
    }

    /**
     * 获取借阅历史
     * @param int $page 页码
     * @throws Exception
     * @throws InvalidSelectorException
     */
    public function borrowedHistory(int $page = 1): array
    {
        if (empty($this->cookie)) throw new Exception('未登录设置Cookie');
        $borrow = new Borrow();
        $borrow->cookie = $this->cookie;
        return $borrow->borrowedHistory($page);
    }

    /**
     * 获取积分记录
     * @param int $page 页码
     * @throws Exception
     * @throws InvalidSelectorException
     */
    public function scoreRecord(int $page = 1): array
    {
        if (empty($this->cookie)) throw new Exception('未登录设置Cookie');
        $score = new Score();
        $score->cookie = $this->cookie;
        return $score->scoreRecord($page);
    }

    /**
     * 获取我的书架
     * @throws Exception
     * @throws InvalidSelectorException
     */
    public function myShelf(): array
    {
        if (empty($this->cookie)) throw new Exception('未登录设置Cookie');
        $shelf = new Shelf();
        $shelf->cookie = $this->cookie;
        return $shelf->myShelf();
    }

    /**
     * 获取我的书架内的图书列表
     * @param string $shelfId
     * @return array
     * @throws Exception|InvalidSelectorException
     */
    public function myShelfBooks(string $shelfId = ''): array
    {
        if (empty($this->cookie)) throw new Exception('未登录设置Cookie');
        $shelf = new Shelf();
        $shelf->cookie = $this->cookie;
        return $shelf->myShelfBooks($shelfId);
    }

    /**
     * 获取二维码
     * @param string $qrcode
     * @return string Base64二维码图片
     * @throws Exception
     */
    public function qrcode(string $qrcode = ''): string
    {
        $base = new Base();
        return $base->qrcode($qrcode);
    }
}