<?php
/**
 * Created by PhpStorm.
 * User: FHF
 * Date: 16/3/22
 * Time: 上午10:11
 */

class toptemai_ctl_upload_uploadDoc extends toptemai_controller{


    //上传合同文件
    public function index(){
        $trade_id = input::get('trade_id');
        $trade = app::get('systrade')->model('trade');
        $contract = $trade->getRow('contract_id,temai_user_id,user_id',array('tid'=>$trade_id));
        if($contract['contract_id'] == 0){
            $msg = "此订单不需要合同";
            $url = url::action('toptemai_ctl_trade_list@index');
            return $this->splash('error',$url,$msg,true);
        }else{
            $upload = kernel::single('toptemai_upload_uploadHandler',array('trade_id'=>$trade_id));
            $url = $upload->response['files'][0];
            $contractMdl = app::get('systrade')->model('contract');

//            $temaiAccountUser = app::get("sysuser")->model("account")->getRow("mobile" , array('user_id'=>$contract['temai_user_id']));
//            $temaiUser = app::get("sysuser")->model("user")->getRow("name" , array('user_id'=>$contract['temai_user_id']));
//
//            $seller = array(
//                'name' => $temaiUser['name'],
//                'mobile' => $temaiAccountUser['mobile']
//            );

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
                    'temai_user_id' => $contract['temai_user_id']
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