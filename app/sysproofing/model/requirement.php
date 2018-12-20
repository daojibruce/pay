<?php
class sysproofing_mdl_requirement extends dbeav_model{

    public function saveOne($data, $userId)
    {
        $params = [
            'user_id' => $userId,
            'user_name' => trim($data['user_name']),
            'addr' => implode('-',[$data['province'],$data['city'],$data['district']]),
            'detail_addr' => trim($data['detail_addr']),
            'start_time' => strtotime($data['start_year'].'-'.$data['start_month'].'-'.$data['start_day']),
            'end_time' => strtotime($data['end_year'].'-'.$data['end_month'].'-'.$data['end_day']),
            'createtime' => time(),
            'status' => '0',
        ];
        $id = $this->insert($params);
        $sampleMdl = app::get('sysproofing')->model('sample');
        if ($id) {
            foreach ($data['sample'] as $sample) {
                $insertData = [
                    'requirement_id' => $id,
                    'sample_name' => $sample['sample_name'],
                    'quantity' => $sample['quantity'],
                    'unit' => $sample['unit'],
                    'material' => $sample['material'],
                    'cat_id' => $sample['cat_id'],
                    'desc' => $sample['desc'],
                    'drawing' => serialize($sample['drawing']),
                    'delivery' => strtotime($sample['year'].'-'.$sample['month'].'-'.$sample['day']),
                    //'pay_type' => $sample['pay_type'],
                    'createtime' => time(),
                    'status' => '0',
                ];
                $sampleMdl->insert($insertData);
            }
        }
        return true;
    }
}
