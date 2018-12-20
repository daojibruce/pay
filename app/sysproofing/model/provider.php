<?php
class sysproofing_mdl_provider extends dbeav_model{

    var $defaultOrder = array('modified_time', 'desc');

    public function saveOne($data, $userId)
    {
        $params = [
            'user_id' => $userId,
            'provider_name' => trim($data['company_name']),
            'provider_cert' => trim($data['tax_id']),
            'provider_mobile' => trim($data['company_contact']),
            'provider_desc' => trim($data['company_desc']),
            'sb_img' => serialize($data['sb']),
            'yp_img' => serialize($data['yp']),
            'createtime' => time(),
            'status' => '0'
        ];
        $proCatMdl = app::get('sysproofing')->model('provider_cat');
        if (!$data['provider_id']) {
            $id = $this->insert($params);
            if ($id) {
                foreach ($data['cats'] as $cat_id) {
                    $insertData = ['cat_id' => $cat_id, 'provider_id' => $id];
                    $proCatMdl->insert($insertData);
                }
            }
        } else {
            $params['provider_id'] = $data['provider_id'];
            $this->save($params);
            $proCatMdl->delete(['provider_id' => $data['provider_id']]);
            foreach ($data['cats'] as $cat_id) {
                $insertData = ['cat_id' => $cat_id, 'provider_id' => $data['provider_id']];
                $proCatMdl->insert($insertData);
            }
        }

        return true;
    }
}
