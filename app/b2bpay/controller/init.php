<?php

/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
set_time_limit(0);

class b2bpay_ctl_init extends base_routing_controller {

        public function index() {
                header("Content-type: text/html; charset=utf-8");
                $userType = input::get('usertype');
                $action = 'init_' . $userType;
                if (method_exists($this, $action)) {
                        $this->$action();
                }
        }

        public function init_buyer() {
                $db = app::get('sysuser')->database();
                $itemList = $db->executeQuery('select user_id,login_account,email,mobile from sysuser_user left join sysuser_account using (user_id) where cust_account_id is null')->fetchAll();
                //var_dump($itemList);exit;
                $gw = kernel::single('b2bpay_gateway');
                foreach ($itemList as $key => $v) {
                        //创建子账号
                        $params = [
                            "TranFunc" => "6000",
                            "FuncFlag" => "1",
                            "ThirdCustId" => $v['login_account'],
                            "CustProperty" => "00",
                            "NickName" => $v['login_account'],
                            "MobilePhone" => $v['mobile'] ? $v['mobile'] : '',
                            "Email" => $v['email'] ? $v['email'] : '',
                            "Reserve" => "anything",
                        ];
                        $gw->request($params);
                        $res = $gw->response();

                        if ($res['RspCode'] == '000000') {
                                $db->executeUpdate("update sysuser_user set cust_account_id='{$res['CustAcctId']}',third_cust_id='{$v['login_account']}' where user_id={$v['user_id']}");
                                echo $v['user_id'] . ':' . $res['CustAcctId'] . "\n";
                        } else {
                                //防止有重复的账号
                                $params = [
                                    "TranFunc" => "6037",
                                    "ThirdCustId" => $v['login_account'],
                                    "Reserve" => "anything",
                                ];
                                $gw->request($params);
                                $res1 = $gw->response();
                                if ($res1['RspCode'] == '000000') {
                                        $db->executeUpdate("update sysuser_user set cust_account_id='{$res1['CustAcctId']}',third_cust_id='{$v['login_account']}' where user_id={$v['user_id']}");
                                        echo $v['user_id'] . ':' . $res1['CustAcctId'] . "\n";
                                } else {
                                        echo $v['user_id'] . ':' . $res['RspMsg'] . "\n";
                                }
                        }
                }
        }

        public function init_seller() {
                $db = app::get('sysshop')->database();
                $itemList = $db->executeQuery('select seller_id,login_account,email,mobile from sysshop_seller left join sysshop_account using (seller_id) where cust_account_id is null')->fetchAll();
                //var_dump($itemList);exit;
                $gw = kernel::single('b2bpay_gateway');
                foreach ($itemList as $key => $v) {
                        //创建子账号
                        $params = [
                            "TranFunc" => "6000",
                            "FuncFlag" => "1",
                            "ThirdCustId" => $v['login_account'],
                            "CustProperty" => "00",
                            "NickName" => $v['login_account'],
                            "MobilePhone" => $v['mobile'] ? $v['mobile'] : '',
                            "Email" => $v['email'] ? $v['email'] : '',
                            "Reserve" => "anything",
                        ];
                        $gw->request($params);
                        $res = $gw->response();

                        if ($res['RspCode'] == '000000') {
                                $db->executeUpdate("update sysshop_seller set cust_account_id='{$res['CustAcctId']}',third_cust_id='{$v['login_account']}' where seller_id={$v['seller_id']}");
                                echo $v['seller_id'] . ':' . $res['CustAcctId'] . "\n";
                        } else {
                                //防止有重复的账号
                                $params = [
                                    "TranFunc" => "6037",
                                    "ThirdCustId" => $v['login_account'],
                                    "Reserve" => "anything",
                                ];
                                $gw->request($params);
                                $res1 = $gw->response();
                                if ($res1['RspCode'] == '000000') {
                                        $db->executeUpdate("update sysshop_seller set cust_account_id='{$res1['CustAcctId']}',third_cust_id='{$v['login_account']}' where seller_id={$v['seller_id']}");
                                        echo $v['seller_id'] . ':' . $res1['CustAcctId'] . "\n";
                                } else {
                                        echo $v['seller_id'] . ':' . $res['RspMsg'] . "\n";
                                }
                        }
                }
        }

}
