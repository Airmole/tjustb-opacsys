<?php

namespace Airmole\TjustbOpacsys;

use Airmole\TjustbOpacsys\Exception\Exception;
use DiDom\Document;

class Profile extends Base
{
    /**
     * 获取已登录账户首页数据
     * @throws Exception
     */
    public function loginedIndexProfile(): array
    {
        $url = '/reader/redr_info.php';
        $result = $this->httpRequest('GET', $url, '', $this->cookie);
        if ($result['code'] != self::CODE_SUCCESS) throw new Exception('loginedIndexProfile：' . json_encode($result));

        $content = $this->stripHtmlTagAndBlankspace($result['data']);
        $info = [];
        preg_match("/证件开始日期：(.*?)证件结束日期/", $content, $licenceStart);
        $info['licenceStart'] = $licenceStart ? $licenceStart[1] : '';

        preg_match("/证件结束日期：(.*?)Email/", $content, $licenceEnd);
        $info['licenceEnd'] = $licenceEnd ? $licenceEnd[1] : '';

        preg_match("/Email：(.*?)未验证/", $content, $email);
        $info['email'] = $email ? $email[1] : '';

        preg_match("/点击验证(.*?)最多可借/", $content, $canBorrowMax);
        $info['canBorrowMax'] = $canBorrowMax ? $canBorrowMax[1] : '';

        preg_match("/最多可借(.*?)最多可预约/", $content, $canPreorderMax);
        $info['canPreorderMax'] = $canPreorderMax ? $canPreorderMax[1] : '';

        preg_match("/最多可预约(.*?)最多可委托/", $content, $canEntrustMax);
        $info['canEntrustMax'] = $canEntrustMax ? $canEntrustMax[1] : '';

        preg_match("/最多可委托(.*?)总积分/", $content, $scoreSum);
        $info['scoreSum'] = $scoreSum ? $scoreSum[1] : '';

        preg_match("/总积分(.*?)可用积分/", $content, $scoreUseful);
        $info['scoreUseful'] = $scoreUseful ? $scoreUseful[1] : '';

        preg_match("/超期图书(.*?)委托到书/", $content, $overdated);
        $info['overdated'] = $overdated[1] ?? $overdated;

        preg_match("/委托到书(.*?)预约到书/", $content, $entrusted);
        $info['entrusted'] = $entrusted ? $entrusted[1] : '';

        preg_match("/预约到书(.*?)荐购图书/", $content, $preordered);
        $info['preordered'] = $preordered ? $preordered[1] : '';

        preg_match("/在所有读者中排在(.*?)的人之前/", $content, $rankPercent);
        $info['rankPercent'] = $rankPercent ? $rankPercent[1] : '';

        return [ 'code' => $result['code'], 'data' => $info ];
    }

    /**
     * 获取账户借阅统计数据
     * @param string $type
     * @return mixed|string
     * @throws Exception
     */
    public function readerStatistics(string $type = 'class'): array|string
    {
        $ajaxParaArr = ['class', 'month', 'year'];
        if (!in_array($type, $ajaxParaArr)) throw new Exception('readerStatistics：参数错误');

        $url = "/reader/ajax_{$type}_sort.php";
        $content = $this->httpRequest('GET', $url, '', $this->cookie);
        if ($content['code'] != self::CODE_SUCCESS) throw new Exception('readerStatistics：' . json_encode($content));
        $data = $this->stripHtmlTagAndBlankspace($content['data']);
        if (json_decode($data)) $data = json_decode($data, true);

        return $data;
    }

    /**
     * 获取已登录账户资料&借阅规则
     */
    public function profileRule(): array
    {
        $url = '/reader/redr_info_rule.php';
        $result = $this->httpRequest('GET', $url, '', $this->cookie);
        if ($result['code'] != self::CODE_SUCCESS) throw new Exception('profileRule：' . json_encode($result));
        $content = $this->stripBlankspace($result['data']);

        preg_match("/姓名：<\/span>(.*?)<\/TD>/", $content, $name);
        $info['name'] = $name ? $name[1] : '';
        preg_match("/证件号： <\/span>(.*?)<\/TD>/", $content, $cardNO);
        $info['cardNO'] = $cardNO ? $cardNO[1] : '';
        preg_match("/条码号：<\/span>(.*?)<\/TD>/", $content, $barCodeNO);
        $info['barCodeNO'] = $barCodeNO ? $barCodeNO[1] : '';
        preg_match("/失效日期：<\/span>(.*?)<\/TD>/", $content, $expirationDate);
        $info['expirationDate'] = $expirationDate ? $expirationDate[1] : '';
        preg_match("/办证日期：<\/span>(.*?)<\/TD>/", $content, $certificateDate);
        $info['certificateDate'] = $certificateDate ? $certificateDate[1] : '';
        preg_match("/生效日期：<\/span>(.*?)<\/TD>/", $content, $effectiveDate);
        $info['effectiveDate'] = $effectiveDate ? $effectiveDate[1] : '';
        preg_match("/最大可借图书：<\/span>(\d{0,5}?)<\/TD>/", $content, $maxBorrowableBookNum);
        $info['maxBorrowableBookNum'] = $maxBorrowableBookNum ? $maxBorrowableBookNum[1] : '';
        preg_match("/最大可预约图书：<\/span>(\d{0,5}?)<\/TD>/", $content, $maxOrderBookNum);
        $info['maxOrderBookNum'] = $maxOrderBookNum ? $maxOrderBookNum[1] : '';
        preg_match("/最大可委托图书：<\/span>(\d{0,5}?)<\/TD>/", $content, $maxCommissionBookNum);
        $info['maxCommissionBookNum'] = $maxCommissionBookNum ? $maxCommissionBookNum[1] : '';
        preg_match("/读者类型：<\/span>(.*?)<\/TD>/", $content, $readerType);
        $info['readerType'] = $readerType ? $readerType[1] : '';
        preg_match("/借阅等级：<\/span>(.{0,5}?)<\/TD>/", $content, $borrowRank);
        $info['borrowRank'] = $borrowRank ? $borrowRank[1] : '';
        preg_match("/累计借书：<\/span>(.*?)册次<\/TD>/", $content, $borrowedBooksSum);
        $info['borrowedBooksSum'] = $borrowedBooksSum ? $borrowedBooksSum[1] : '';
        preg_match("/违章次数：<\/span>(\d{0,5}?)<\/TD>/", $content, $violationNum);
        $info['violationNum'] = $violationNum ? $violationNum[1] : '';
        preg_match("/欠款金额：<\/span>(.*?)<\/TD>/", $content, $overdraft);
        $info['overdraft'] = $overdraft ? $overdraft[1] : '';
        preg_match("/系别：<\/span>(.*?)<\/TD>/", $content, $college);
        $info['college'] = $college ? $college[1] : '';
        preg_match("/Email：<\/span>(.*?)<\/TD>/", $content, $email);
        $info['email'] = $email ? $email[1] : '';
        preg_match("/身份证号：<\/span>(.*?)<\/TD>/", $content, $idCardNO);
        $info['idCardNO'] = $idCardNO ? $idCardNO[1] : '';
        preg_match("/工作单位：<\/span>(.*?)<\/TD>/", $content, $workDept);
        $info['workDept'] = $workDept ? $workDept[1] : '';
        preg_match("/职业\/职称：<\/span>(.*?)<\/TD>/", $content, $majorProfession);
        $info['majorProfession'] = $majorProfession ? $majorProfession[1] : '';
        preg_match("/性别：<\/span>(.{0,3}?)<\/TD>/", $content, $gender);
        $info['gender'] = $gender ? $gender[1] : '';

        $document = new Document($result['data']);
        $tableTrs = $document->first('#rule')->find('tr');

        $rules = [];
        foreach ($tableTrs as $index => $tr) {
            if ($index == 0) continue; // 跳过第一行标题
            $tds = $tr->find('td');

            $ruleNoUrl = $tds[7]->first('a')->getAttribute('href');
            preg_match('/\?rule_no=(.*)/', $ruleNoUrl, $ruleNo);
            $ruleNo = $ruleNo ? $ruleNo[1] : '';
            $rules[] = [
                'name' => $tds[0]->text(),
                'place' => $tds[1]->text(),
                'bookType' => $tds[2]->text(),
                'canBorrowMax' => $this->stripHtmlTagAndBlankspace($tds[3]->text()),
                'day' => $tds[4]->text(),
                'preorder' => $tds[5]->text(),
                'renew' => $tds[6]->text(),
                'ruleNo' => $ruleNo,
            ];
        }

        return [ 'code' => $result['code'], 'data' => [ 'profile' => $info, 'rules' => $rules ] ];
    }

    /**
     * 获取借阅规则详情
     * @throws Exception|\DiDom\Exceptions\InvalidSelectorException
     */
    public function ruleDetail(string $ruleNo = ''): array
    {
        if (empty($ruleNo)) throw new Exception('ruleDetail：ruleNo不能为空');

        $url = '/reader/redr_rule_form.php?rule_no=' . $ruleNo;
        $result = $this->httpRequest('GET', $url, '', $this->cookie);
        if ($result['code'] != self::CODE_SUCCESS) throw new Exception('ruleDetail：' . json_encode($result));

        $detail = [];
        $dom =  new Document($result['data']);
        $tableTds = $dom->first('table.whitetalbe')->find('td');
        foreach ($tableTds as $index => $td) {
            if ($index == 0) continue;
            $title = '';
            if ($td->has('span')) $title = $td->first('span')->text();
            $content = trim($td->lastChild()->text());
            $content = $this->stripBlankspace($content);
            $content = mb_substr($content, 2);
            if (empty($title)) continue;
            if (str_starts_with($content, '.') && is_numeric($content)) $content = '0' . $content;
            $detail[] = [
                'title' => $title,
                'content' => $content,
            ];
        }

        return [ 'code' => $result['code'], 'data' => $detail ];
    }

}