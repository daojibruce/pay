<?php
class sysproofing_mdl_offer extends dbeav_model{

    public function saveOne($data, $provider_id)
    {
        $params = [
            'provider_id' => $provider_id,
            'sample_id' => $data['sample_id'],
            'sample_fee' => trim($data['sample_fee']),
            'post_fee' => trim($data['post_fee']),
            'offer_delivery' => strtotime($data['year'].'-'.$data['month'].'-'.$data['day']),
            'post_type' => $data['post_type'],
            'pay_type' => $data['pay_type'],
            'createtime' => time(),
            'status' => 0,
        ];
        $params['total_fee'] = $params['sample_fee'] + $params['post_fee'];
        if ($data['offer_id']) {
            $params['offer_id'] = $data['offer_id'];
        }
        if ($data['pay_type'] == 1) {
            $params['params'] = serialize($data['params']);
        }
        $id = $this->save($params);

        return true;
    }
}
