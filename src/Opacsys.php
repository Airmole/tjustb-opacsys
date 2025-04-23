<?php

namespace Airmole\TjustbOpacsys;

/**
 * TjustbOpacsys 主类
 */
class Opacsys
{
    /**
     * 获取热门借阅和热门图书top10
     * @return array
     * @throws Exception\Exception
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function lendAndPopularTopTen(): array
    {
        $popular = new Popular();
        return $popular->lendAndPopularTopTen();
    }

    /**
     * 获取关键词云
     * @return array|mixed
     * @throws Exception\Exception
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function keywordCloud()
    {
        $popular = new Popular();
        return $popular->keywordCloud();
    }

    /**
     * 获取检索关键词top10
     * @return array
     * @throws Exception\Exception
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function topTenKeyword()
    {
        $popular = new Popular();
        return $popular->topTenKeyword();
    }

    /**
     * 获取热门关键词榜
     * @return array
     * @throws Exception\Exception
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function topKeyword()
    {
        $popular = new Popular();
        return $popular->topKeyword();
    }

    /**
     * 借阅榜
     * @param string $class
     * @return array
     * @throws Exception\Exception
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function topLend(string $class = '')
    {
        $popular = new Popular();
        return $popular->topLend($class);
    }

    /**
     * 评分榜
     * @param string $class
     * @param string $sort
     * @return array
     * @throws Exception\Exception
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function topScore(string $class = '', string $sort = '')
    {
        $popular = new Popular();
        return $popular->topScore($class, $sort);
    }

    /**
     * 收藏榜
     * @param string $class
     * @return array
     * @throws Exception\Exception
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function topStar(string $class = '')
    {
        $popular = new Popular();
        return $popular->topStar($class);
    }

    /**
     * 浏览榜
     * @param string $class
     * @return array
     * @throws Exception\Exception
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function topBook(string $class = '')
    {
        $popular = new Popular();
        return $popular->topBook($class);
    }

    /**
     * 高级检索参数
     * @return array
     * @throws Exception\Exception
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
     * @throws Exception\Exception
     */
    public function advancedSearch(string $search = '', int $page = 1, int $pageSize = 20): array
    {
        $searchClass = new Search();
        return $searchClass->advancedSearch($search, $page, $pageSize);
    }

    /**
     * 高级检索参数
     * @return array
     * @throws Exception\Exception
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function searchParams(): array
    {
        $searchClass = new Search();
        return $searchClass->searchParams();
    }

    /**
     * 多字段检索参数
     * @return array
     * @throws Exception\Exception
     * @throws \DiDom\Exceptions\InvalidSelectorException
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
     * @throws Exception\Exception
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function search(
        mixed $search,
        int $page = 1,
        int $pageSize = 20,
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
     * @throws Exception\Exception
     * @throws \DiDom\Exceptions\InvalidSelectorException
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
     * @throws Exception\Exception
     * @throws \DiDom\Exceptions\InvalidSelectorException
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
     * @throws Exception\Exception
     * @throws \DiDom\Exceptions\InvalidSelectorException
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
     * @throws Exception\Exception
     * @throws \DiDom\Exceptions\InvalidSelectorException
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
     * @throws Exception\Exception
     * @throws \DiDom\Exceptions\InvalidSelectorException
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
     * @throws Exception\Exception
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function relatedFavour(string $marcNo, string $callNo): array
    {
        $search = new Search();
        return $search->relatedFavour($marcNo, $callNo);
    }

    /**
     * 借阅趋势
     * @param string $marcNo
     * @return array
     * @throws Exception\Exception
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
     * @throws Exception\Exception
     * @throws \DiDom\Exceptions\InvalidSelectorException
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
     * @throws Exception\Exception
     * @throws \DiDom\Exceptions\InvalidSelectorException
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
     * @throws Exception\Exception
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function exceedFine(int $page = 1): array
    {
        $infoPublish = new InfoPublish();
        return $infoPublish->exceedFine($page);
    }

}