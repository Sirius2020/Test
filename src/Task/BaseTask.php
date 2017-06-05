<?php

namespace Format_Checker\Task;

abstract class BaseTask
{

    const TASK_RESULT_SUCCEED = 1;
    const TASK_RESULT_FAILED = 0;

    protected $_file_name = '';
    protected $_result = 0;
    protected $_result_detail = array();
    protected $_task_handler = null;

    public function __construct($file_name, $task_handler = null){
        $this->_file_name = $file_name;
        if ($task_handler){
            $this->_task_handler = $task_handler;
        }
        else{
            $this->_task_handler = Handler\Regex::getInstance();
        }
    }

    public function getContents(){}

    public function setResult($result, $detail){
        $this->_result = $result;
        $this->_result_detail = $detail;
    }

    public function getResult(){
        return $this->_result;
    }

    public function getResultDetail(){
        return $this->_result_detail;
    }

    public function displayResult(){
        if ($this->_result == self::TASK_RESULT_SUCCEED){
            echo $this->_file_name, " [ok]\n";
        }
        else{
            echo $this->_file_name, " [failed]\n";
            foreach($this->_result_detail as $v){
                echo $v['content'], "\n", $v['msg'], "\n";
            }
        }
    }

    public function run(){
        $this->_task_handler->runTask($this);
    }
}

