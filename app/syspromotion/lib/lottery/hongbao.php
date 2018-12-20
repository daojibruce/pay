<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 */
//定额红包

class syspromotion_lottery_hongbao implements syspromotion_interface_lottery
{
    public $bonusType='hongbao';

    /**
     * 发放红包
     */
    public function issue($data)
    {
        $hongbaoData = array(
            'user_id' => $data['user_id'],
            'hongbao_id' => $data['prizeInfo']['hongbaoid'],
            'money' => $data['prizeInfo']['hongbaomoney'],
            'hongbao_obtain_type' => 'userGet',
        );
        try
        {

            $result = app::get('syspromotion')->rpcCall('user.hongbao.get',$hongbaoData);

            $objMdlLotteryResult = app::get('syspromotion')->model('lottery_result');
            $data['bonusType'] = $data;
            $data['created_time'] = time();
            $data['loginName'] = userAuth::getLoginName();
            $data['bonus_desc'] = $data['prizeInfo']['hongbaomoney'].'元-红包'.$data['prizeInfo']['hongbaoid'];
            $prizeInfo = array(
                'bonus_type'=>'hongbao',
                'bonus_desc'=>'红包',
                'hongbaoid'=>$data['prizeInfo']['hongbaoid'],
                'hongbaomoney'=>$data['prizeInfo']['hongbaomoney'],
            );
            $data['prizeInfo'] = serialize($prizeInfo);

            return $objMdlLotteryResult->save($data);
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }
}
