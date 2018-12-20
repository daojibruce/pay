<?php

class systemai_tasks_stateReset extends base_task_abstract implements base_interface_task {

    public function exec($params=null)
    {
        kernel::single('systemai_command_state')->command_resetState();
    }
}

