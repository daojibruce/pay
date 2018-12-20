<?php

/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2bpay_ctl_demo extends base_routing_controller {

        public function index() {

                $gw = kernel::single('b2bpay_gateway');
                //创建子账号
				$params = [
                    "TranFunc" => "6000",
                    "FuncFlag" => "1",
                    "ThirdCustId" => "test",
                    "CustProperty" => "00",
                    "NickName" => "test",
                    "MobilePhone" => "18202171735",
                    "Email" => "d@123.com",
                    "Reserve" => "anything",
                ];

				/*
                //查询银行子账户余额【6010】
                $params = [
                    "TranFunc" => "6010",
                    "SelectFlag" => "3",
                    "PageNum" => "1",
                ];

                //查询资金混总账号余额【6011】
                $params = [
                    "TranFunc" => "6011",
                ];

                //查询会员子账号【6037】
				$params = [
                    "TranFunc" => "6037",
                    "ThirdCustId" => "d",
                ];
				*/

				//$params = 'a:10:{s:8:"TranFunc";s:4:"6064";s:10:"CustAcctId";s:15:"888100008059901";s:11:"ThirdCustId";s:16:"554157247@qq.com";s:6:"AcctId";s:16:"6225882129072761";s:10:"TranAmount";s:4:"0.01";s:7:"CcyCode";s:3:"RMB";s:7:"Reserve";s:9:"youlikeaa";s:4:"Qydm";s:4:"3037";s:9:"SupAcctId";s:14:"11014892524004";s:10:"ThirdLogNo";s:20:"CBAE7FDA9E7167111630";}';
				//$params = 'a:15:{s:8:"TranFunc";s:4:"6055";s:10:"CustAcctId";s:15:"888100008059901";s:11:"ThirdCustId";s:16:"554157247@qq.com";s:8:"CustName";s:9:"李技道";s:6:"IdType";s:1:"1";s:6:"IdCode";s:18:"371833198012072111";s:6:"AcctId";s:16:"6225882129072761";s:8:"BankType";s:1:"2";s:8:"BankName";s:54:"招商银行股份有限公司上海南方商城支行";s:8:"BankCode";s:12:"308290003724";s:11:"MobilePhone";s:11:"18200000001";s:7:"Reserve";s:6:"little";s:4:"Qydm";s:4:"3037";s:9:"SupAcctId";s:14:"11014892524004";s:10:"ThirdLogNo";s:20:"3BE47496FD0E66F72FF1";}';
				//$params = unserialize($params);
				//$params['CustAcctId'] = '888100008059901';
				//$params['ThirdCustId'] = '554157247@qq.com';
				//$params['AcctId'] = '6225882129072763';
				//$params['MobilePhone'] = '18202171735';

				//dump($params,1);
                $gw->request($params);
                $res = $gw->response();

                header("Content-type: text/html; charset=utf-8");
                echo '<pre>';
                echo 'Request params:<br>';
                print_r($params);
                echo 'Response message:<br>';
                print_r($res);

                /*
               ini_set('memory_limit', '500m');
            $db = app::get('b2bpay')->database();
            $itemList = $db->executeQuery('select * from b2bpay_pay_node')->fetchAll();
            $fp = fopen('data.txt', 'w');


            foreach ($itemList as $key => $value)
            {
                    echo serialize($value)."\n";
                   // fwrite($fp,  serialize($value)."\n");
            }
            fclose($fp);

            */


        }
public function aa(){
    //同步见证宝
    $rspCode=kernel::single('b2bpay_data_6034');
    $rspCode->__sync_pay_1('1607041606190078');

}
    public function bb(){
        $rspCode=kernel::single('b2bpay_data_6005');
        $rspCode->__sync_payshop('42','6217730201753818');
    }


}
