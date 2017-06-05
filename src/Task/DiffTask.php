<?php

namespace Format_Checker\Task;

class DiffTask extends BaseTask
{

    private $_diff_content = '';

    public function __construct($file_name, $diff_content, $task_handler = null){
        parent::__construct($file_name, $task_handler);
        $this->_diff_content = $diff_content;
    }

    public function getContents(){
        return $this->_diff_content;
    }
}