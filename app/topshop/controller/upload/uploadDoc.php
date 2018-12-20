<?php
/**
 * Created by PhpStorm.
 * User: FHF
 * Date: 16/3/22
 * Time: 上午10:11
 */

class topshop_ctl_upload_uploadDoc extends topshop_controller{


    //上传合同文件
    public function index(){
        $trade_id = input::get('trade_id');
        $trade = app::get('systrade')->model('trade');
        $contract = $trade->getRow('contract_id,shop_id,user_id',array('tid'=>$trade_id));
        if($contract['contract_id'] == 0){
            $msg = "此订单不需要合同";
            $url = url::action('topshop_ctl_trade_list@index');
            return $this->splash('error',$url,$msg,true);
        }else{
            $upload = kernel::single('topshop_upload_uploadHandler',array('trade_id'=>$trade_id));
            $url = $upload->response['files'][0];
            $contractMdl = app::get('systrade')->model('contract');

            $seller = app::get('sysshop')->model('seller');
            $sellerInfo = $seller->getRow('name,mobile',array('shop_id'=>$contract['shop_id']));

            $con = $contractMdl->getRow('*',array('contract_id'=>$contract['contract_id']));
            $contract_img =array();

            if($con['contract_img'] && is_array(unserialize($con['contract_img']))){
                $contract_img =  unserialize($con['contract_img']);
            }
            if($contract_img){
                $contract_img = serialize(array_merge($contract_img,array($url->url)));
            }else{
                $contract_img = serialize(array($url->url));
            }
            try{
                $data = array(
                    'user_id' => $contract['user_id'],
                    // 'contacts' => $sellerInfo['name'],
                    // 'phone' => $sellerInfo['mobile'],
                    'contract_img'=>   $contract_img,
                    'shop_id' => $contract['shop_id']
                );
                //将图片插入和图表
                $contractMdl->update($data,array('contract_id' => $contract['contract_id']));
            }
            catch(Exception $e){
                $msg = $e->getMessage();
                return $this->splash('error',"",$msg,true);
            }
            //将订单合同状态改为已上传
            $tradeMdl = app::get('systrade')->model('trade');
            try{
                //更改订单表合同数据
                $tradedata['tid']=$trade_id;
                $tradedata['update_status']=1;
                $tradedata['checkup_status']=1;
                $tradeMdl->save($tradedata);
            }catch(Exception $e){
                $msg = $e->getMessage();
                return $this->splash('error',"",$msg,true);
            }
        }
    }

}