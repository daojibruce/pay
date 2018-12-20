<?php
class thirdparty_mdl_provider extends dbeav_model{

    public function saveOne($data)
    {
        $params = [
            'provider_name' => trim($data['provider_name']),
            'mobile' => trim($data['mobile']),
            'desc' => trim($data['desc']),
            'contact' => trim($data['contact']),
            'addr' => trim($data['addr']),
            'createtime' => time(),
            'status' => '1'
        ];
        $proCatMdl = app::get('thirdparty')->model('cate_rel');
        if (!$data['provider_id']) {
            $id = $this->insert($params);
            if ($id) {
                foreach ($data['cate'] as $cat_id) {
                    $insertData = ['cat_id' => $cat_id, 'provider_id' => $id, 'createtime' => time()];
                    $proCatMdl->insert($insertData);
                }
            }
        } else {
            $params['provider_id'] = $data['provider_id'];
            $this->save($params);
            $proCatMdl->delete(['provider_id' => $data['provider_id']]);
            foreach ($data['cate'] as $cat_id) {
                $insertData = ['cat_id' => $cat_id, 'provider_id' => $data['provider_id'], 'createtime' => time()];
                $proCatMdl->insert($insertData);
            }
        }

        return true;
    }
}
