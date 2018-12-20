<?php
if(! function_exists('curl_init')){
	echo "CURL 不被支持";exit;
}
if (!ini_get('safe_mode') && strpos(ini_get('disable_functions'), 'set_time_limit') === FALSE) {
	set_time_limit(0);
}else{
	echo "can not set time limit for this script!\n";
}
ini_set("memory_limit","2000M");
defined("APP_ROOT") || define("APP_ROOT",substr(__FILE__,0,-32)."caijidir");

class caijiCore{
	public static $_self;
	protected $caijiDataInObj;

	protected function __construct(){
		$this->verbose = false;
		$this->cacheTime=0;//缓存时间
		$this->charset="gbk";
		$this->pageCharset="gbk";
		$this->referer='';
		$this->followlocation=true;
		$this->showReferer=true;
        $this->userAgent='';
		$this->data='';
		$this->cTimeOut=90;
		$this->imageTimeOut=180;
		$this->textTimeOut=120;
		$this->timeOut=$this->textTimeOut;
        $this->sleepTime=2;
        $this->useCookie = true;
		$this->cookie='';
		$this->curloptHeader=false;
		$this->curloptNobody=false;
		$this->imgDir=APP_ROOT.DIRECTORY_SEPARATOR."cache".DIRECTORY_SEPARATOR."images";
		$this->cacheHeaderFile=APP_ROOT.DIRECTORY_SEPARATOR."cache".DIRECTORY_SEPARATOR."header".DIRECTORY_SEPARATOR."caiji";
		$this->cacheBodyFile=APP_ROOT.DIRECTORY_SEPARATOR."cache".DIRECTORY_SEPARATOR."caiji";
		$this->cacheFile=$this->cacheBodyFile;
		$this->errorLog=APP_ROOT.DIRECTORY_SEPARATOR."logCaiji".DIRECTORY_SEPARATOR."errorLog.txt";
		$this->cookieFile=APP_ROOT.DIRECTORY_SEPARATOR."logCaiji".DIRECTORY_SEPARATOR."cookieFile.txt";
		$this->httpHeader = array();
		$this->proxyip = '';
		$this->proxyport = '';
        $this->userAgentList = array(
            "Firefox"=>"User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:43.0) Gecko/20100101 Firefox/43.0",
            "Safari"=>"User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 8_3 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Version/8.0 Mobile/12F70 Safari/600.1.4",
            "itunesstored"=>"User-Agent: itunesstored/1.0 iOS/9.0 model/iPhone5,3 hwp/s5l8950x build/13A344 (6; dt:97)"
        );
	}

	public function setFollowlocation($bool = true){
		$this->followlocation = $bool;
	}
	
	public function setShowReferer($bool = true){
		$this->showReferer = $bool;
	}
    
    public function setUseCookie($bool = true){
		$this->useCookie = $bool;
	}

    public function setReferer($referer){
		$this->referer = $referer;
	}
    
    public function setUserAgent($userAgent){
		$this->userAgent = isset($this->userAgentList[$userAgent]) ? $this->userAgentList[$userAgent] : '';
	}

	public function setVerbose($verbose){
		$this->verbose = $verbose;
	}

	public function setProxyInfo($proxyip,$proxyport){
		$this->proxyip = $proxyip;
		$this->proxyport = $proxyport;
	}
	
	public function setCookieFile($fileName){
		if(!empty($fileName)){
			$this->cookieFile=APP_ROOT.DIRECTORY_SEPARATOR."logCaiji".DIRECTORY_SEPARATOR.$fileName.".txt";
			return true;
		}

		return false;
	}

	public function setCacheTime($cacheTime){
		$this->cacheTime = $cacheTime;
	}

	public function getCookieValueByName($domain ,$name){
		$contents = file_get_contents($this->cookieFile);

		if(preg_match('#^'.$domain.'\s+[a-z]+\s+[/a-z_\-]+\s+[a-z]+\s+[a-z0-9\.]+\s+'.$name.'\s+([\w\.\-]+)$#im',$contents , $match)){
			return ($match[1]);
		}

		return false;
	}

	public function clearCookie(){
		file_put_contents($this->cookieFile , '');
		return true;
	}

	public static function init(){
		if(!empty(self::$_self)){
			return self::$_self;
		}

		self::$_self = new self();

		return self::$_self;
	}

	public function __set($key,$value){
		$this->caijiDataInObj[$key]=$value;
	}

	public function __get($key){
		$value=null;
		if(isset($this->caijiDataInObj[$key])){
			$value=$this->caijiDataInObj[$key];
		}

		return($value);
	}

	public function __isset($key){
		$value=null;
		if(isset($this->caijiDataInObj[$key])){
			$value = $this->caijiDataInObj[$key];
		}

		return($value);
	}

	public function __unset($key){
		if(isset($this->caijiDataInObj[$key])){
			unset($this->caijiDataInObj[$key]);
		}
	}

	/*
		正则匹配一次
	*/
	public function parseDataOnce($parseReg){
		$matches = false;
		$matchCount=preg_match($parseReg,$this->data,$matches);
		if($matchCount){
			return ($matches);
		}

		return (false);
	}

	/*
		正则匹配全部
	*/
	public function parseDataAll($parseReg){
		$matches = false;
		$matchCount=preg_match_all($parseReg,$this->data,$matches);
		if($matchCount){
			return ($matches);
		}

		return (false);
	}

	/*
		链接URL
	*/
	public function convertPostData($postArr){
		$postStr='';
		foreach($postArr as $key => $value){
			$value = urlencode($value);
			$postStr.="&".$key."=".$value;
		}
		$postStr=ltrim($postStr,"&");

		return ($postStr);
	}

	/*
		登录操作
	*/
	public function toLogin($loginUrl,$postArr,$succesReg){
		$postStr=$this->convertPostData($postArr);
		$data = $this->pickDataCURL($loginUrl,$postStr);
		if(strcasecmp($this->pageCharset,$this->charset) != 0){
			$data = mb_convert_encoding($data , $this->charset , $this->pageCharset);
		}
		$logined=false;
		if(preg_match($succesReg,$data)){
			$logined=true;
		}

		return ($logined);
	}

	/*
		页数
	*/
	public function getTotalPages($endPageReg){
		$this->totalpages=0;
		$pageStr=preg_match($endPageReg,$this->data,$matches);
		if(!empty ($matches[1])){
			$this->totalpages=intval($matches[1]);
		}

		return($this->totalpages);
	}

	/*
		http 头部信息
	*/
	public function pickHeaderData($url,$postArr=false){
		$this->curloptHeader=true;
		$this->curloptNobody=true;
		$this->cacheFile=$this->cacheHeaderFile;

		$this->data = $this->pickData($url,$postArr);

		$this->cacheFile=$this->cacheBodyFile;
		$this->curloptNobody=false;
		$this->curloptHeader=false;

		return ($this->data);
	}

    private function generateCacheFilepath($url , $postStr=''){
		$md5=md5($url.$postStr);
		$subDir=substr($md5,0,1).DIRECTORY_SEPARATOR.substr($md5,1,1);
		$md5File=substr($md5,2);
		$localDir=$this->cacheFile.DIRECTORY_SEPARATOR.$subDir;
		if(!realpath($localDir)){
			mkdir($localDir,0777,true);
		}
		$localFile = $localDir.DIRECTORY_SEPARATOR.$md5File.".txt";

        return $localFile;
    }

	/*
		http 内容信息
	*/
	public function pickData($url , $postArr=false,$binary=false){
        $postStr='';
		if($postArr){
			$postStr=$this->convertPostData($postArr);
		}
		$localFile = $this->generateCacheFilepath($url , $postStr);
		if($this->cacheTime!==false){
			if(is_file($localFile)){
				if(0==$this->cacheTime){
					$this->data = file_get_contents($localFile);
					return $this->data;
				}else{
					if((filemtime($localFile)+$this->cacheTime)>time()){
						$this->data = file_get_contents($localFile);
						return $this->data;
					}
				}
			}
		}
		$this->data = $this->pickDataCURL($url , $postStr);
		if(!empty($this->data)){
			if(!$binary){
				if(strcasecmp($this->pageCharset,$this->charset) != 0){
					$this->data = mb_convert_encoding($this->data , $this->charset , $this->pageCharset);
				}
			}
			if($this->cacheTime!==false){
				$fp=fopen($localFile,"w+");
				fwrite($fp,$this->data);
				fclose($fp);
				chmod($localFile,0777);
			}
		}

		return $this->data;
	}

    public function delCacheFile($url , $postArr = false){
        $postStr='';
		if($postArr){
			$postStr=$this->convertPostData($postArr);
		}

        $localFile = $this->generateCacheFilepath($url , $postStr);
        if(file_exists($localFile)){
            unlink($localFile);
        }

        return true;
    }

	/*
		切割文本
	*/
	public function cutData($from,$to='',$direct='out'){
		$frompos=0;
		if($from){
			$frompos = strpos($this->data,$from);
		}

		if($frompos && $to!=''){
			$topos = strpos($this->data,$to,$frompos+strlen($from));
		}

		if(!$frompos || !$topos){
			return $this->data;
		}
		if($direct == 'in'){
			$start = $frompos+strlen($from);
			if($to!='')
				$end = $topos-$start;
		}else{
			$start = $frompos;
			if($to!='')
				$end   = $topos+strlen($to)-$frompos;
		}
		if($to!='')
			$txt = substr($this->data,$start,$end);
		else
			$txt = substr($this->data,$start);

		return $txt;
	}

	/*
		根据文字内容，生成图片
	*/
	public function generateImg($msg , $extName='png'){
		if(empty($msg)){
			return false;
		}

		$baseImgName=$this->generateName();
		if(!file_exists($this->imgDir)){
			@mkdir($this->imgDir,0777,true);
		}
		$localFile = $this->imgDir . DIRECTORY_SEPARATOR . $baseImgName . "." . $extName;

		$im = imagecreate(260, 30);
		$background_color = imagecolorallocate($im, 200, 200, 100);
		$text_color = imagecolorallocate($im, 255, 10, 10);
		$fontFile=dirname(__FILE__).DIRECTORY_SEPARATOR.'song.ttf';
		imagettftext($im, 12, 0, 8, 19, $text_color, $fontFile, $msg);
		imagepng($im , $localFile);
		imagedestroy($im);
		chmod($localFile,0777);

		return ($localFile);
	}

	/*
		保存图片至本地硬盘
	*/
	public function saveImg2Local($remoteImg , $prefix='' , $extName=''){
		$this->timeOut=$this->imageTimeOut;
		$tmpOptHeader = $this->curloptHeader;
		$this->curloptHeader = false;
		$data=$this->pickDataCURL($remoteImg,'');
		$this->curloptHeader = $tmpOptHeader;
		if(empty($data)){
			return false;
		}

		$baseImgName=$this->combin2PicName($remoteImg,$prefix);
		if(!file_exists($this->imgDir)){
			@mkdir($this->imgDir,0777,true);
		}
		$localFile = $this->imgDir . DIRECTORY_SEPARATOR . $baseImgName;
		if(!empty($extName)){
			$localFile .= "." . $extName;
		}
		file_put_contents($localFile , $data);
		chmod($localFile,0777);

		if(getimagesize($localFile)===false){
			unlink($localFile);
			return false;
		}
		$this->timeOut=$this->textTimeOut;

		return ($localFile);
	}

	/*
		保存图片
	*/
	public function saveImg($remoteImg,$localImgName=false,$dirName=false,$makeThumb=false,$thumbDir=''){
		//end of get image
		if($localImgName===false){
			$localImgName=$this->generateName();
			$ext=substr($remoteImg,strrpos($remoteImg,"."));
			$localImgName.=$ext;
		}

		$localDir=$dirName ? APP_ROOT.DIRECTORY_SEPARATOR.$dirName : $this->imgDir;
		if(!realpath($localDir)){
			@mkdir($localDir,0777,true);
		}
		$imgFile=$localDir.DIRECTORY_SEPARATOR.$localImgName;
		$imgURI=str_replace(APP_ROOT,'',$imgFile);
		$imgURI=str_replace(DIRECTORY_SEPARATOR,'/',$imgURI);

		if(!file_exists($imgFile)){
			//return false;
			//to get image
			$this->timeOut=$this->imageTimeOut;
			$data=false;
			$data=$this->pickDataCURL($remoteImg,'');
			if(empty($data)){
				return false;
			}

			$fp=fopen($imgFile,"w+");
			if($fp){
				fwrite($fp,$data);
				fclose($fp);
			}
			chmod($imgFile,0777);
			if(getimagesize($imgFile)===false){
				unlink($imgFile);
				return false;
			}

			if($makeThumb){
				$thumbDir=APP_ROOT.DIRECTORY_SEPARATOR.$thumbDir;
				if(!realpath($thumbDir)){
					@mkdir($thumbDir,0777,true);
				}
				$this->makethumb($imgFile,$thumbDir."/",115,80);
			}
			$this->timeOut=$this->textTimeOut;
		}

		return ($imgURI);
	}

    /*
		保存图片至指定路径
	*/
	public function saveImgByRealPath($remoteImg , $imgRealPath){
        if(empty($remoteImg) || empty($imgRealPath)){
            return false;
        }

        $localDir = dirname($imgRealPath);
        if(!realpath($localDir)){
			mkdir($localDir,0777,true);
		}

		if(!file_exists($imgRealPath)){
			$this->timeOut=$this->imageTimeOut;
			$data=false;
			$data=$this->pickDataCURL($remoteImg,'');
			if(empty($data)){
				return false;
			}

			$fp=fopen($imgRealPath,"w+");
			if($fp){
				fwrite($fp,$data);
				fclose($fp);
			}
			chmod($imgRealPath,0777);
			if(getimagesize($imgRealPath)===false){
				unlink($imgRealPath);
				return false;
			}

			$this->timeOut=$this->textTimeOut;
		}

		return ($imgRealPath);
	}

	/*
		应用Curl拉取数据
	*/
	public function pickDataCURL($url,$postStr){
		//设置referer
		$this->referer = empty($this->referer) ? $url : $this->referer;
		$header = array();
		#$header[] ="Accept: */*";
		$header[] ="Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
        //手机版头部标识
		//$header[] = "User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 8_3 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Version/8.0 Mobile/12F70 Safari/600.1.4";
        //PC版头部标识
        if(empty($this->userAgent)){
            $header[] = $this->userAgentList["Firefox"];
        }else{
            $header[] = $this->userAgent;
        }
		$header[] = "Connection: Keep-Alive";
		
		$header[] ="Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3";
		$header[] ="Cache-Control: no-cache";
		$header[] ="X-AjaxPro-Method:ShowList";
		$header[] ="Content-Type: application/xml; charset=utf-8";
		$header[] ="Content-Length:" . strlen($postStr);
		if($this->showReferer){
			$header[] ="Referer: ".$this->referer;
		}
		if($this->useCookie && !empty($this->cookie)){
			$header[]="Cookie: ".$this->cookie;
		}
		//Expect:100-continue
		$header[]="Expect: ";
		if(!empty($this->httpHeader)){
			foreach($this->httpHeader as $tmpStr){
				array_push($header , $tmpStr);
			}
		}

		$ch = curl_init();
		curl_setopt($ch,CURLOPT_ENCODING,"gzip, deflate");
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_VERBOSE, $this->verbose);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch,CURLOPT_TIMEOUT,$this->timeOut);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$this->cTimeOut);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		if('HTTPS'==strtoupper(substr($url,0,5))){
			curl_setopt($ch,CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
		}
		curl_setopt($ch,CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		if(!empty($this->proxyip)&&!empty($this->proxyport)){
			curl_setopt($ch , CURLOPT_PROXY , $this->proxyip);
			curl_setopt($ch , CURLOPT_PROXYPORT , $this->proxyport);
		}
		curl_setopt($ch,CURLOPT_HEADER,$this->curloptHeader);
		curl_setopt($ch,CURLOPT_NOBODY,$this->curloptNobody);
		if($this->showReferer){
			curl_setopt($ch,CURLOPT_REFERER,$this->referer);
		}
		curl_setopt($ch,CURLOPT_FOLLOWLOCATION,($this->followlocation ? 1 : 0));
		if($this->useCookie && empty($this->cookie)){
			curl_setopt($ch,CURLOPT_COOKIEJAR,$this->cookieFile);
			curl_setopt($ch,CURLOPT_COOKIEFILE,$this->cookieFile);
		}
		if(!empty($postStr)){
			curl_setopt($ch,CURLOPT_POST,1);
			curl_setopt($ch,CURLOPT_POSTFIELDS,$postStr);
		}

		//设置自定义http头部
		curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
		$data=curl_exec($ch);
		$errno = curl_errno($ch);

		if($errno >0){
			$errorInfo=curl_error($ch);
			$url=empty($postStr) ? $url : $url.'?'.$postStr;
			$this->writeLog($errorInfo,$url);
		}
		curl_close($ch);
		$this->tosleep();

		return $data;
	}

	/*
		重新组装名称
	*/
	public function combin2PicName($url , $prefix=''){
		$tmpName = $prefix.basename($url);

		return $tmpName;
	}

	public function generateName(){
		$localImgName=date("YmdHis").rand(10,99);

		return ($localImgName);
	}

    public function generateNameByUri($remoteImg , $preName = '' , $match=false){
        $imgBaseName = empty($preName) ? $this->generateName() : $preName;
        if($match){
            preg_match("#\\.jpg|\\.png|\\.gif|\\.bmp|\\.jpeg|\\.tiff|\\.ico|\\.webp#is",$remoteImg , $match);
            $imgBaseName .= $match[0];
        }else{
            $imgBaseName .= substr($remoteImg,strrpos($remoteImg,"."));
        }
        

		return ($imgBaseName);
	}

	protected function writeLog($errorInfo,$url){
		@mkdir(dirname($this->errorLog) , 0777 , true);
		$fp=fopen($this->errorLog,"a+");
		fwrite($fp,date('Y-m-d H:i:s')."\t".$errorInfo."\t".$url."\n");
		fclose($fp);
	}

	protected function tosleep(){
		if(!file_exists($this->cookieFile)){
			file_put_contents($this->cookieFile , '');
		}
		/*
		$fileTimeName = $this->cookieFile.'_time';
		if(!file_exists($fileTimeName)){
			file_put_contents($fileTimeName , time());
		}
		$fileTime=file_get_contents($fileTimeName);
		if(time() - $fileTime >= 1800){
			file_put_contents($fileTimeName , time());
			$this->clearCookie();
		}
		*/

		if($this->sleepTime){
			echo "+ sleeping ". $this->sleepTime ."s + ";
			sleep($this->sleepTime);
		}
	}

	/*
		生成目录
	*/
	public function mkdirp($target){
		if(file_exists($target)){
			if (!is_dir($target))
				return false;
			else
				return true;
		}

		if(mkdir($target))
			return true;

		if($this->mkdirp(dirname($target)))
			return $this->mkdirp($target);

		return false;
	}

	/*
		生成缩略图
	*/
	public function makethumb($srcfile,$dir,$thumbwidth,$thumbheight,$ratio=0){
		if (!file_exists($srcfile)){
			return false;
		}

		$imgname=explode('/',$srcfile);
		$arrcount=count($imgname);
		$dstfile = $dir.$imgname[$arrcount-1];
		$tow = $thumbwidth;
		$toh = $thumbheight;
		if($tow < 40) $tow = 40;
		if($toh < 45) $toh = 45;

		$im ='';
		if($data = getimagesize($srcfile)) {
			if($data[2] == 1) {
				$make_max = 0;//gif 图片
				if(function_exists("imagecreatefromgif")) {
					$im = imagecreatefromgif($srcfile);
				}
			} elseif($data[2] == 2) {
				if(function_exists("imagecreatefromjpeg")) {
					$im = imagecreatefromjpeg($srcfile);
				}
			} elseif($data[2] == 3) {
				if(function_exists("imagecreatefrompng")) {
					$im = imagecreatefrompng($srcfile);
				}
			}
		}
		if(!$im) return '';
		$srcw = imagesx($im);
		$srch = imagesy($im);
		$towh = $tow/$toh;
		$srcwh = $srcw/$srch;
		if($towh <= $srcwh){
			$ftow = $tow;
			$ftoh = $ftow*($srch/$srcw);
		} else {
			$ftoh = $toh;
			$ftow = $ftoh*($srcw/$srch);
		}
		if($ratio){
			$ftow = $tow;
			$ftoh = $toh;
		}
		//等比
		if($srcw > $tow || $srch > $toh || $ratio) {
			if(function_exists("imagecreatetruecolor") && function_exists("imagecopyresampled") && @$ni = imagecreatetruecolor($ftow, $ftoh)) {
				imagecopyresampled($ni, $im, 0, 0, 0, 0, $ftow, $ftoh, $srcw, $srch);
			} elseif(function_exists("imagecreate") && function_exists("imagecopyresized") && @$ni = imagecreate($ftow, $ftoh)) {
				imagecopyresized($ni, $im, 0, 0, 0, 0, $ftow, $ftoh, $srcw, $srch);
			} else {
				return '';
			}
			if(function_exists('imagejpeg')) {
				imagejpeg($ni, $dstfile);
			} elseif(function_exists('imagepng')) {
				imagepng($ni, $dstfile);
			}
		}else {
			copy($srcfile,$dstfile);
		}
		imagedestroy($im);
		if(!file_exists($dstfile)) {
			return '';
		} else {
			return $dstfile;
		}
	}
}
?>