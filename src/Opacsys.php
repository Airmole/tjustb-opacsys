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
    public function keywordTopTen()
    {
        $popular = new Popular();
        return $popular->keywordTopTen();
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
}