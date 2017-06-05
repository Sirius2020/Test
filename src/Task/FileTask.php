<?php

namespace Format_Checker\Task;

class FileTask extends BaseTask
{

    private $_file = '';

    public function __construct($file_name, $task_handler = null){
        parent::__construct($file_name, $task_handler);
        $this->_file = $file_name;
    }

    public function getContents(){
        return file_get_contents($this->_file);
    }
}