<?php
class topc_ctl_proofing extends topc_controller{

    public $limit;

    public function __construct()
    {
        parent::__construct();
        kernel::single('base_session')->start();
        $this->limit = 10;
		$this->setLayoutFlag('matchmaking');
    }

    public function index()
    {
        $reqMdl = app::get('sysproofing')->model('sample');
        $offerMdl = app::get('sysproofing')->model('offer');
        //最新需求
        $samples = $reqMdl->getList('*', [], 0, 8,'createtime desc');
        foreach ($samples as $key => $sample) {
            $samples[$key]['createtime'] = date('Y/m/d H:i',$sample['createtime']);
            $count = $offerMdl->getRow('count(*) as count',['sample_id' => $sample['sample_id']]);
            $samples[$key]['count'] = $count['count'];
        }

        //优质服务商
        $db = app::get('sysproofing')->database();
        //$sql = "SELECT p.provider_name,p.provider_id,p.provider_desc FROM sysproofing_provider AS p LEFT JOIN sysproofing_offer AS o ON p.provider_id=o.provider_id WHERE o.status=1 LIMIT 8";
        $sql = "SELECT provider_name,provider_id,provider_desc FROM sysproofing_provider limit 0,8";
        $providers = $db->executeQuery($sql)->fetchAll();
        foreach ($providers as $key => $provider) {
            $offInfo = $offerMdl->getRow('count(*) as count',['provider_id' => $provider['provider_id'],'status' => '1']);
            $providers[$key]['count'] = $offInfo['count'];
            $sql = 'SELECT c.cat_name FROM sysproofing_category AS c LEFT JOIN sysproofing_provider_cat AS p ON c.cat_id=p.cat_id WHERE p.provider_id='.$provider['provider_id'];
            $catInfo = $db->executeQuery($sql)->fetchAll();
            foreach($catInfo as $cat) {
                $providers[$key]['catInfo'] .= $cat['cat_name'].' ';
            }
        }

        $pagedata['samples'] = $samples;
        $pagedata['providers'] = $providers;

        //查看用户是否是服务商
        $userId = userAuth::id();
        $proInfo = app::get('sysproofing')->model('provider')->getRow('provider_id',['user_id' => $userId, 'enabled' => '1']);
        if ($proInfo) {
            $pagedata['is_provider'] = 1;
        }

        return $this->page('topc/proofing/index.html', $pagedata);
    }

    public function update()
    {
        $userId = userAuth::id();
        if (!$userId) {
            return redirect::action('topc_ctl_passport@login');
        }
        //判断是否是企业会员
        $info = app::get('sysuser')->model('user_company')->getRow('*',['user_id' => $userId]);
        if (!$info) {
            return redirect::action('topc_ctl_passport@companysignup');
        }
        //查询服务类型
        $cats = app::get('sysproofing')->model('category')->getList('*', [], 0, -1, 'sort asc');
        //判断是否已经申请
        $provider = app::get('sysproofing')->model('provider')->getRow('*',['user_id' => $userId]);
        if ($provider) {
            if ($provider['enabled'] == '1') {
                return redirect::action('topc_ctl_member_proofing@index');
            }
            $selects = app::get('sysproofing')->model('provider_cat')->getList('*', ['provider_id' => $provider['provider_id']]);
            foreach ($cats as $key => $cat) {
                foreach ($selects as $select) {
                    if ($cat['cat_id'] == $select['cat_id']) {
                        $cats[$key]['is_checked'] = 1;
                    }
                }
            }
            $provider['sb_img'] = unserialize($provider['sb_img']);
            $provider['yp_img'] = unserialize($provider['yp_img']);
            $pagedata['provider'] = $provider;
        }


        $pagedata['cats'] = $cats;
        $pagedata['info'] = $info;
        //echo "<pre>";print_r($pagedata);exit;

        $this->setLayoutFlag('proofing_passport');
        return $this->page('topc/proofing/update.html', $pagedata);
    }

    //提交注册验证
    public function doUpdate()
    {
        $data = input::get();
        $userId = userAuth::id();
        if (!$userId) {
            return response::json(['status' => 'error', 'url' => url::action('topc_ctl_passport@signin')]);
        }
        $flag = $this->_check($data);
        if ($flag) return response::json($flag);
        $res = app::get('sysproofing')->model('provider')->saveOne($data, $userId);
        if ($res) {
            return response::json(['status' => 'success']);
        } else {
            return response::json(['status' => 'error', 'message' => '提交失败，请重试']);
        }
    }

    //检查信息
    private function _check($data)
    {
        $return['status'] = 'error';
        if ($data['license'] != 'on') {
            $return['message'] = '';
            return $return;
        }

        $validator = validator::make(
            [
                'name' => $data['company_name'],
                'tax_id' => $data['tax_id'],
                'mobile' => $data['company_contact'],
                'desc' => $data['company_desc'],
                'cats' => $data['cats'],
                'sb' => $data['sb'],
                'yp' => $data['yp'],
            ],
            [
                'name' => 'required',
                'tax_id' => 'required',
                'mobile' => 'required|mobile',
                'desc' => 'required',
                'cats' => 'required',
                'sb' => 'required',
                'yp' => 'required',
            ],
            [
                'name' => '企业名称不能为空',
                'tax_id' => '企业证件不能为空',
                'mobile' => '联系方式不能为空|请输入正确的手机号',
                'desc' => '描述不能为空',
                'cats' => '请选择服务类型',
                'sb' => '请上传服务撮合设备图片',
                'yp' => '请上传服务撮合样品图片',
            ]
        );
        if ($validator->fails())
        {
            $messages = $validator->messagesInfo();
            foreach( $messages as $error )
            {
                $return['message'] = $error[0];
                return $return;
            }
        }
        if (!$data['provider_id']) {
            $result = app::get('sysproofing')->model('provider')->getRow('provider_id', ['provider_name' => trim($data['company_name'])]);
            if ($result) {
                $return['message'] = '该企业名已被注册';
                return $return;
            }
            $userId = userAuth::id();
            $result2 = app::get('sysproofing')->model('provider')->getRow('provider_id', ['user_id' => $userId]);
            if ($result2) {
                $return['message'] = '该账号已注册';
                return $return;
            }
        }

        return false;
    }

    //提交注册验证
    public function imageUpload()
    {
        $objLibImage = kernel::single('image_data_image');
        try {
            $imageData = $objLibImage->store($_FILES['file'],null,'admin',true);
            $objLibImage->rebuild($imageData['ident'],'admin');
            $pagedata['image_id'] = $imageData['id'];
            $pagedata['image_scr'] = base_storager::modifier($imageData['url']);
        }catch( Exception $e ) {
            $pagedata['error_msg'] = $e->getMessage();
        }
        echo json_encode($pagedata);exit;
    }

    //发布需求页面
    public function release()
    {
        $userId = userAuth::id();
        if (!$userId) {
            return redirect::action('topc_ctl_passport@login');
        }
        $pagedata['cats'] = app::get('sysproofing')->model('category')->getList('*', [], 0, -1, 'sort asc');

        $this->setLayoutFlag('proofing_release');
        return $this->page('topc/proofing/release.html', $pagedata);
    }

    //发布需求
    public function doRelease()
    {
        $data = input::get();
        $userId = userAuth::id();
        if (!$userId) {
            return response::json(['status' => 'error', 'url' => url::action('topc_ctl_passport@signin')]);
        }
        $flag = $this->_checkRelease($data);
        if ($flag) return response::json($flag);

        $reqMdl = app::get('sysproofing')->model('requirement');
        $res = $reqMdl->saveOne($data, $userId);
        if ($res) {
            return response::json(['status' => 'success']);
        } else {
            return response::json(['status' => 'error', 'message' => '提交失败，请重试']);
        }
    }

    private function _checkRelease($data)
    {
        $return['status'] = 'error';
        $validator = validator::make(
            [
                'name' => $data['user_name'],
                'province' => $data['province'],
                'detail_addr' => $data['detail_addr'],
            ],
            [
                'name' => 'required',
                'province' => 'required',
                'detail_addr' => 'required',
            ],
            [
                'name' => '联系人不能为空',
                'province' => '交货地点不能为空',
                'detail_addr' => '详细地址不能为空',
            ]
        );
        if ($validator->fails())
        {
            $messages = $validator->messagesInfo();
            foreach( $messages as $error )
            {
                $return['message'] = $error[0];
                return $return;
            }
        }

        if ($data['start_day'] == '0' || $data['end_day'] == '0') {
            $return['message'] = '报价截止时间不能为空！';
            return $return;
        }
        $time = strtotime($data['start_year'].'-'.$data['start_month'].'-'.$data['start_day']) + 86400;
        if (time() > $time) {
            $return['message'] = '报价开始时间不能小于当前时间！';
            return $return;
        }
        if ($data['start_year'] > $data['end_year']) {
            $return['message'] = '报价开始时间不能大于截止时间！';
            return $return;
        } elseif ($data['start_year'] == $data['end_year'] && $data['start_month'] > $data['end_month']) {
            $return['message'] = '报价开始时间不能大于截止时间！';
            return $return;
        } elseif ($data['start_year'] == $data['end_year'] && $data['start_month'] == $data['end_month'] && $data['start_day'] > $data['end_day']) {
            $return['message'] = '报价开始时间不能大于截止时间！';
            return $return;
        }

        //检查样品参数
        foreach ($data['sample'] as $sample) {
            $validator = validator::make(
                [
                    'name' => $sample['sample_name'],
                    'quantity' => $sample['quantity'],
                    'unit' => $sample['unit'],
                    'material' => $sample['material'],
                    'cat_id' => $sample['cat_id'],
                    'desc' => $sample['desc'],
                    //'pay_type' => $sample['pay_type'],
                    'drawing' => $sample['drawing'],
                ],
                [
                    'name' => 'required',
                    'quantity' => 'required|Numeric',
                    'unit' => 'required',
                    'material' => 'required',
                    'cat_id' => 'required',
                    'desc' => 'required',
                    //'pay_type' => 'required',
                    'drawing' => 'required',
                ],
                [
                    'name' => '样品名称不能为空',
                    'quantity' => '样品数量不能为空|样品数量为数字',
                    'unit' => '样品单位不能为空',
                    'material' => '样品材质不能为空',
                    'cat_id' => '样品类型不能为空',
                    'desc' => '样品描述不能为空',
                    //'pay_type' => '支付方式不能为空',
                    'drawing' => '请上传图纸',
                ]
            );
            if ($validator->fails())
            {
                $messages = $validator->messagesInfo();
                foreach( $messages as $error )
                {
                    $return['message'] = $error[0];
                    return $return;
                }
            }

            if ($sample['day'] == '0') {
                $return['message'] = '交货时间不能为空！';
                return $return;
            }
            $ktime = strtotime($sample['year'].'-'.$sample['month'].'-'.$sample['day']);
            if (time() > $ktime) {
                $return['message'] = '交货时间不能小于当前时间！';
                return $return;
            }
        }

        return false;
    }

    //发布需求页面
    public function getSampleHtml()
    {
        $pagedata['cats'] = app::get('sysproofing')->model('category')->getList('*');
        $index = input::get('index');
        $pagedata['index'] = $index;
        return view::make('topc/proofing/sampleHtml.html', $pagedata)->render();
    }

    //上传图纸
    public function saveDrawing() {

        $userId = userAuth::id();
        $files = $_FILES;
        foreach ($files as $file) {
            if ($file['error'] > 0) {
                echo json_encode(array('error' => '666', 'message' => '文件上传失败！'));exit;
            }
        }

        //上传文件
        $dir = ROOT_DIR . '/public/images/drawing/' . $userId;
        //先删除目录
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $upfiles = array();
        foreach ($files as $key => $file) {
            $new_name = $dir . '/'.time().'-' . $file['name'];
            //$new_name = $dir . '/' .time().'-'. iconv("UTF-8", "GBK", $file['name']);
            if (!move_uploaded_file($file['tmp_name'], $new_name)) {
                echo json_encode(array('error' => '666', 'message' => '文件上传失败！'));exit;
            }
            $upfiles['new_name'] = $new_name;
        }
        echo json_encode($upfiles);exit;
    }

    public function providerList()
    {
        $page = input::get('pages') ? input::get('pages') : 1;
        $proMdl = app::get('sysproofing')->model('provider');
        $count = $proMdl->count(['enabled' => 1]);
        $providers = $proMdl->getList('*',['enabled' => 1],($page-1)*$this->limit,$this->limit,'modified_time desc');
        foreach ($providers as $key => $provider) {
             $imgs = unserialize($provider['yp_img']);
             foreach ($imgs as $img) {
                 $providers[$key]['yp_img'] = $img;
             }

        }
        $pagedata['providers'] = $providers;
        $pagedata['pagers'] = $this->__pager($page,$count);
        $this->setLayoutFlag('proofing_more');
        return $this->page('topc/proofing/providerList.html', $pagedata);

    }

    public function sampleList()
    {
        $page = input::get('pages') ? input::get('pages') : 1;
        $reqMdl = app::get('sysproofing')->model('sample');
        $offerMdl = app::get('sysproofing')->model('offer');
        //最新需求
        $count = $reqMdl->count(['status' => '0']);
        $samples = $reqMdl->getList('*', ['status' => '0'], $this->limit*($page-1), $this->limit,'createtime desc');
        $pagedata['pagers'] = $this->__pagers($page,$count);
        foreach ($samples as $key => $sample) {
            $samples[$key]['createtime'] = date('Y/m/d H:i',$sample['createtime']);
            $count = $offerMdl->getRow('count(*) as count',['sample_id' => $sample['sample_id']]);
            $samples[$key]['count'] = $count['count'];
        }
        $pagedata['samples'] = $samples;
        $this->setLayoutFlag('proofing_more');
        return $this->page('topc/proofing/sampleList.html', $pagedata);

    }

    public function detail()
    {
        $sample_id = input::get('sample_id');
        if (!$sample_id) return redirect::action('topc_ctl_proofing@index');
        $db = app::get('sysproofing')->database();
        $sql = 'SELECT s.*,r.user_name,r.start_time,r.end_time,r.addr FROM sysproofing_sample AS s LEFT JOIN sysproofing_requirement AS r ON s.requirement_id=r.requirement_id WHERE s.sample_id='.$sample_id.' AND s.status=0 ';
        $samples = $db->executeQuery($sql)->fetchAll();
        if (!$samples) return redirect::action('topc_ctl_proofing@index');

        $sample = $samples[0];
        $cat = app::get('sysproofing')->model('category')->getRow('cat_name',['cat_id' => $sample['cat_id']]);
        $sample['cat_name'] = $cat['cat_name'];
        $pagedata['sample'] = $sample;
        $pagedata['sample']['drawing'] = $_SERVER['DOCUMENT_ROOT'] . '/images/drawing/' . $this->userId.'/'.$sample_id.'drawing.zip';
        //echo "<pre>";print_r($pagedata);exit;

        return $this->page('topc/proofing/detail.html',$pagedata);

    }

    private function __pager($page,$count)
    {
        $postFilter['pages'] = time();
        $total = ceil($count/$this->limit);
        $pagers = array(
            'link'=>url::action('topc_ctl_proofing@providerList',$postFilter),
            'current'=>$page,
            'total'=>$total,
            'token'=>time(),
        );
        return $pagers;
    }

    private function __pagers($page,$count)
    {
        $postFilter['pages'] = time();
        $total = ceil($count/$this->limit);
        $pagers = array(
            'link'=>url::action('topc_ctl_proofing@sampleList',$postFilter),
            'current'=>$page,
            'total'=>$total,
            'token'=>time(),
        );
        return $pagers;
    }
}
