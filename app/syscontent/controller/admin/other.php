<?php

use Symfony\Component\DomCrawler\Crawler;

class syscontent_ctl_admin_other extends desktop_controller
{
    var $workground = 'site.wrokground.theme';
    var $platforms = array('pc'=>'电脑端','wap'=>'移动端');
    const DEFAULT_TYPE = 1;

    public function others()
    {
        return $this->finder('syscontent_mdl_others', array(
            'title'=>app::get('syscontent')->_('抓取文章列表'),
            'use_buildin_filter' => false,
            'use_buildin_setcol' => false,
            'use_buildin_delete'=>true,
            'actions'=>array(
                array(
                    'label'=>app::get('syscontent')->_('抓取文章'),
                    'href'=>'?app=syscontent&ctl=admin_other&act=scratch','target'=>'dialog::{title:\''.app::get('syscontent')->_('抓取文章').'\',width:400,height:100}'
                ),
                array(
                    'label'=>app::get('syscontent')->_('添加文章'),
                    'href'=>'?app=syscontent&ctl=admin_other&act=addOther','target'=>'dialog::{title:\''.app::get('syscontent')->_('添加文章').'\',width:800,height:500}'
                ),
            )
        ));
    }

    //文章添加
    public function addOther()
    {

        return $this->page('syscontent/admin/other/other.html',$pagedata);
    }

    //文章保存
    public function saveOther()
    {
        $this->begin("?app=syscontent&ctl=admin_other&act=others");
        $data = input::get('article');
        $otherMdl = app::get('syscontent')->model('others');
        $info = $otherMdl->getRow('article_id',['title' => $data['title']]);
        if ($info) {
            $this->end(false,'该文章已存在');
        }
        $data['ifpub'] = 1;
        $data['modified'] = time();

        $otherMdl->save($data);
        $this->end(true);
    }

    public function scratch()
    {
        return $this->page('syscontent/admin/other/scratch.html',$pagedata);
    }

    public function ScratchJC35()
    {
        $this->begin("?app=syscontent&ctl=admin_other&act=others");
        $url = 'http://www.jc35.com/interview';
        $crawler = $this->_getBody($url);
        //抓取
        $lis = $crawler->filter('.main>.left>.Switch>ul>li>a');
        foreach ($lis as $index => $li) {
            $href = $lis->eq($index)->attr('href');
            $href = 'http://www.jc35.com'.$href;
            $this->getJC35Content($href);
        }
        $this->end(true);
    }

    //采集文章
    private function getJC35Content($url)
    {
        try{
            $url = 'http://www.jc35.com/news_People/Detail/965.html';
            $crawler = $this->_getBody($url);
            $title = $crawler->filter('.mainLeft .dTop H3')->first()->text();
            if ($crawler->filter('.mainLeft .dTop .Author')->count()) {
                $author = $crawler->filter('.mainLeft .dTop .Author')->first()->text();
                $author = mb_substr($author,3);
            }
            $contentElement = $crawler->filter('#newsContent>div');
            $content = '';
            foreach ($contentElement as $index => $element) {
                if ($contentElement->eq($index)->attr('align')) {
                    $src = $contentElement->eq($index)->filter('img')->first()->attr('src');
                    $content .= '<p><img src="'.$src.'"></p>';
                } else {
                    $content .='<p>'. $contentElement->eq($index)->text().'</p>';
                }
            }

            //检查文章是否存在
            if (app::get('syscontent')->model('others')->getRow('article_id',['title' => $title])) return false;

            $params = [
                'title' => $title,
                'author' => trim($author) ?  $author : '中国机床商务网',
                'content' => $content,
                'ifpub' => '1',
                'modified' => time(),
                'url' => $url
            ];
            app::get('syscontent')->model('others')->save($params);
        }catch (Exception $e) {}
    }

    //根据url获取页面内容并解析，返回一个解析对象
    private function _getBody($url)
    {
        $client = new GuzzleHttp\Client();
        $response = $client->get($url);
        $body = $response->getBody()->__toString();
        $coding = $this->ws_mb_detect_encoding($body);
        $body = iconv($coding,'UTF-8',$body);
        $crawler = new Crawler();
        $crawler->addHtmlContent($body);
        return $crawler;
    }

    private function ws_mb_detect_encoding ($string, $enc=null, $ret=null) {
        static $enclist = array(
            'UTF-8', 'GBK', 'GB2312', 'GB18030'
        );
        $result = false;
        foreach ($enclist as $item) {
            //$sample = iconv($item, $item, $string);
            $sample = mb_convert_encoding($string,$item, $item);
            if (md5($sample) == md5($string)) {
                if ($ret === NULL) { $result = $item; } else { $result = true; }
                break;
            }
        }
        return $result;
    }
}