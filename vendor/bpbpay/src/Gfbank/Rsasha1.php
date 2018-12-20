<?php
namespace Bpbpay\Gfbank;

/**
 * Manages query string variables and can aggregate them into a string
 */
class Rsasha1
{
    public static $thisInstance;
    protected $pukInfo = '';
    protected $pvkInfo = '';
    protected $gfsInfo = '';

    public static function getInstance()
    {
        if(self::$thisInstance){
            return self::$thisInstance;
        }

        self::$thisInstance = new self();

        return self::$thisInstance;
    }

    private function __construct(){
        $signedInfo = \config::get('rsasm2code.guangFa');
        if(empty($signedInfo)){
            throw new \Exception("请配置广发见证宝的密钥对(请同时配置好公钥与私钥参数)");
        }

        $this->pukInfo = $signedInfo['puk'];
        $this->pvkInfo = $signedInfo['pvk'];
        $this->gfsInfo = $signedInfo['gfs'];
    }

    public function getSign($msgContents)
    {
        $pemStr = $this->pvkInfo;
        $pemStr = chunk_split($pemStr,64,"\n");//转换为pem格式的私钥
        $pemStr = "-----BEGIN PRIVATE KEY-----\n".$pemStr."-----END PRIVATE KEY-----\n";
        $privateKey = openssl_get_privatekey($pemStr);
        openssl_sign($msgContents, $signedMsg, $privateKey,OPENSSL_ALGO_SHA1);
        // free the key from memory
        openssl_free_key($privateKey);
        $signedMsg = base64_encode($signedMsg);

        return $signedMsg;
    }

    public function signVerify($msgContents , $signedMsg)
    {
        $pemStr = $this->pukInfo;
        $pemStr = chunk_split($pemStr,64,"\n");//转换为pem格式的公钥
        $pemStr = "-----BEGIN PUBLIC KEY-----\n".$pemStr."-----END PUBLIC KEY-----\n";
        $publicKey = openssl_get_publickey($pemStr);
        $signedMsg = base64_decode($signedMsg);
        $status = openssl_verify($msgContents, $signedMsg, $publicKey , OPENSSL_ALGO_SHA1);
        openssl_free_key($publicKey);

        return $status;
    }
    
}