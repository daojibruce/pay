<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/16
 * Time: 14:12
 */
namespace Bpbpay\Gfbank;
use GuzzleHttp\Client as httpClient;
require "caijiCore.php";

class transaction
{
    public static $thisObj;
    private $isDebugModel = true;
    private $serviceSignDebugUrl = 'http://113.108.207.154:38083/corporbank/CGBLHttpServiceSign';
    protected $serviceSignUrl = 'https://ebank.cgbchina.com.cn/corporbank/CGBLHttpServiceSign';

    private function __construct()
    {
        if($this->isDebugModel){
            $this->serviceSignUrl = $this->serviceSignDebugUrl;
        }
    }

    public function init()
    {
        if(self::$thisObj){
            return self::$thisObj;
        }else{
            self::$thisObj = new self();
        }

        return self::$thisObj;
    }

    public function subaccountList()
    {
        $para = [
            'tranCode' => '0078',
            'cifMaster' => '1000194445',
            'entSeqNo' => '',
            'tranDate' => date("Ymd"),
            'tranTime' => date("His"),
            'retCode' => '000',
            'entUserId' => '100001',
            'password' => 'i6q2e5n6s7',
            'account' => '9550880200323900115',
            'state' => 'N',
            'pagerow' => '25',
            'querypage' => '0',
            'backup1' => '',
            'backup2' => '',
            'backup3' => '',
            'backup4' => '',
        ];
        $xmlData = $this->array2xml($para);
        $res = $this->postData($this->serviceSignUrl , $xmlData);
        echo $res;
    }

    private function array2xml($params)
    {
        //处理commHead节点数据
        $commStrArr = [];
        array_push($commStrArr, '<commHead>');
        $commHeadList = ['tranCode','cifMaster','entSeqNo','tranDate','tranTime','retCode','entUserId','password'];
        foreach($commHeadList as $key){
            if(! isset($params[$key])){
                continue;
            }
            $tmpStr = '<'.$key.'>' . trim($params[$key]) . '</'.$key.'>';
            unset($params[$key]);
            array_push($commStrArr, $tmpStr);
        }
        array_push($commStrArr, '</commHead>');
        if(count($commStrArr) < 3){
            return false;
        }

        //处理签名部分，过滤掉参数里的签名数据
        $signatureList = ['signatureMethod' , 'digestMethod','signedInfo'];
        foreach ($signatureList as $key){
            if(! isset($params[$key])){
                continue;
            }
            unset($params[$key]);
        }

        //处理body节点数据
        $bodyStrArr = [];
        array_push($bodyStrArr, '<Body>');
        foreach ($params as $key => $val){
            $tmpStr = '<'.$key.'>' . trim($val) . '</'.$key.'>';
            unset($params[$key]);
            array_push($bodyStrArr, $tmpStr);
        }
        array_push($bodyStrArr, '</Body>');

        //处理message节点数据
        $messageStrArr = [];
        array_push($messageStrArr, '<Message>');
        array_push($messageStrArr, implode('', $commStrArr));
        array_push($messageStrArr, implode('', $bodyStrArr));
        array_push($messageStrArr, '</Message>');

        //二次处理签名
        $signatureStrArr = [];
        $signedInfo = $this->getSignature($messageStrArr);
        array_push($signatureStrArr, '<Signature>');
        array_push($signatureStrArr, '<signatureMethod>RSA</signatureMethod>');
        array_push($signatureStrArr, '<signatureMethod>SHA1</signatureMethod>');
        array_push($signatureStrArr, '<signatureMethod>'.$signedInfo.'</signatureMethod>');
        array_push($signatureStrArr, '</Signature>');

        $xmlStrArr = [];
        array_push($xmlStrArr, '<?xml version="1.0" encoding = "GBK"?><BEDC>');
        array_push($xmlStrArr, implode('', $messageStrArr));
        array_push($xmlStrArr, implode('', $signatureStrArr));
        array_push($xmlStrArr, '</BEDC>');

        $xmlStr = implode('', $xmlStrArr);

        return $xmlStr;
    }

    public function getSignature($messageXmlArr)
    {
        $messageXmlStr = implode('', $messageXmlArr);
        $signedStr = with(Rsasha1::getInstance())->getSign($messageXmlStr);

        return $signedStr;
    }

    public function doSigne()
    {
        $str = "I'm Jken";
        $signedStr = with(Rsasha1::getInstance())->getSign($str);
        $rs = with(Rsasha1::getInstance())->signVerify($str , $signedStr);

        echo $str;
        echo "<br>";
        echo "<br>";
        echo $signedStr;
        echo "<br>";
        echo "<br>";
        var_dump($rs);
        exit;
    }

    public function postData($postUrl , $bodyStr)
    {
        $curl = \caijiCore::init();
        $xmlStr = $curl->pickDataCURL($postUrl , $bodyStr);
        return $xmlStr;


        $xmlStr = '';
        $response = $curl->action('post' , $postUrl , ['body' => $bodyStr]);
        if(200 == $response->getStatusCode()){
            $stream = $response->getBody();
            $xmlStr = $stream->getContents();
        }

        return $xmlStr;
    }

    public function postData2($postUrl , $bodyStr)
    {
    $baseUri = $this->getUrlPrefix($postUrl);
    $client = new httpClient([
        // Base URI is used with relative requests
        'base_uri' => $baseUri,
        // You can set any number of default request options.
        'timeout'  => 2.0,
    ]);

    $xmlStr = '';
    $response = $client->post($postUrl , ['body' => $bodyStr]);
    if(200 == $response->getStatusCode()){
        $stream = $response->getBody();
        $xmlStr = $stream->getContents();
    }

    return $xmlStr;
}

    private function getUrlPrefix($url)
    {
        $p = strpos($url , '/' , 8);
        if($p){
            $p +=1;
            return substr($url , 0 , $p);
        }

        return $url;
    }

    
}