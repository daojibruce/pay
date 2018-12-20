<?php
use Symfony\Component\HttpFoundation\File\UploadedFile;
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class toputil_ctl_file {

    public function uploadFile()
    {
        $files = input::file();

        if($files['agreement']){
            $filePath = $this->__doUpload('agreement' , $files);
        }else if($files['certificate']){
            $filePath = $this->__doUpload('certificate' , $files);
        }else{
            $filePath = $this->__doUpload('pricecerti' , $files);
        }

        $result = array('success'=>true, 'data'=>$filePath);
        return json_encode($result);
    }

    private function __doUpload($fileKey , $files)
    {
        $filePathArr = [];
        if(!empty($files[$fileKey])){
            $fileObjList = $files[$fileKey];
            if(!is_array($fileObjList)){
                $fileObjList = [$fileObjList];
            }

            foreach ($fileObjList as $fileObject){
                $tempnam = sha1(time() . mt_rand(10000,99999));
                $dirName = FILES_DIR .'/'. substr($tempnam , 0 , 3) .'/'. substr($tempnam , 3 , 3) .'/';
                $tempnam = substr($tempnam , 8);
                $dirUrl = str_replace(PUBLIC_DIR, '', $dirName) ;
                if(!file_exists($dirName)){
                    mkdir($dirName , 0777 , true);
                }

                $tempFilename = $tempnam .'.'. $fileObject->getClientOriginalExtension();
                $fileObject->move($dirName , $tempFilename);
                array_push($filePathArr, ['url' => $dirUrl . $tempFilename]);
            }
        }

        return $filePathArr;
    }

}
