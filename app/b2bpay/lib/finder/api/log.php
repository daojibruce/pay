<?php

class b2bpay_finder_api_log {

        function detail_log($log_id) {
                $oApilog = &app::get('b2bpay')->model("api_log");
                $apilog = $oApilog->dump($log_id);
                //var_dump($apilog);
                $apilog['params'] = unserialize($apilog['params']);
                $apilog['send_api_name'] = $apilog['method']; // API名称
                if (is_array($apilog['params'])) {
                        foreach ($apilog['params'] as $key => $val) {
                                if (is_array($val)) {
                                        $params .= $key . '=' . serialize($val);
                                } else {
                                        $params .= $key . "=" . $val . "<br/>";
                                }
                        }
                } else {
                        $params = $apilog['params'];
                }
                $apilog['send_api_params'] = $params;

                $apilog_msg = unserialize($apilog['msg']);
                $msg = '';
                if (is_array($apilog_msg)) {
                        $msg = '-';
                        if (is_array($apilog_msg)) {
                                foreach ($apilog_msg as $key => $val) {
                                        $msg .= $key . "=" . urldecode($val) . "<br/>";
                                }
                        }
                } else {
                        $msg = htmlspecialchars($apilog['msg']);
                }
                $apilog['msg'] = $msg;
                $pagedata['apilog'] = $apilog;
                return view::make('b2bpay/admin/apidetail.html', $pagedata)->render();
        }

}

?>