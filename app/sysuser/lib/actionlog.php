<?php

class sysuser_actionlog {
    /**
     * 子账号操作日志
     * @param type $params
     * @param type $status
     * @return type
     */
    public function actionlog($params = array(), $status = 1) {
        $subUserId = userAuth::subid();
        if (!$subUserId) {
            return;
        }
        $queue_params = array(
            'user_id' => $subUserId,
            'master_user_id' => userAuth::id(),
            'created_time' => time(),
            'memo' => $params['memo'],
            'action_id' => $params['action_id'],
            'status' => ($status ? 1 : 0),
            'router' => request::fullurl(),
            'ip' => request::getClientIp(),
        );
        //return system_queue::instance()->publish('system_tasks_actionlog', 'system_tasks_actionlog', $queue_params);
        app::get('sysuser')->model('actionlog')->insert($queue_params);
    }

}
