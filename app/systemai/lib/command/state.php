<?php
/**
 * ShopEx licence
 *
 * @category ecos
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 * @version 0.1
 */

class systemai_command_state extends base_shell_prototype
{
    /**
     * @var 定义名称
     */
    public $command_resetState = '平台展销商品到期自动下架，库存为零自动下架';

    /**
     * @param null
     * @return null
     */
    public function command_resetState()
    {
        $filter = ['state' => 1];
        $sortBy = 'temai_id asc';
        $temaiModel = app::get('systemai')->model('temai');
        $count = $temaiModel->count($filter);
        $increase = 5000 ;

        $dataRow = ['state' => 2];
        $timeNow = date('Ymd');
        for($offset=0; $offset < $count; $offset += $increase){
            $temaiList = $temaiModel->getList("temai_id , store , state , tm_starttime , tm_endtime" , $filter, $offset , $increase ,$sortBy);
            foreach($temaiList as $row){
                if($timeNow > date('Ymd',$row['tm_endtime'])){
                    $subFilter = ['temai_id' => $row['temai_id']];

                    //平台展销商品过期后，自动下架
                    $temaiModel->update($dataRow , $subFilter);
                }
            }
        }

        logger::info('平台展销商品到期自动下架，库存为零自动下架成功');
    }//End Function
}//End Class

