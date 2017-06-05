<?php

namespace Format_Checker\Task\Handler;

use \Format_Checker\Task\BaseTask;

class RegexHandler
{

    static private $_instance = null;
    private $_ruleset = array();

    private function __construct($rule_file){
        $ruleset = array();
        $rule_str = file_get_contents($rule_file);
        preg_match_all("/^([^#\r\n]+)\n([^#\r\n]+)\n?$/m", $rule_str, $match);
        foreach($match[0] as $k=>$v){
            $ruleset[] = array(
                'pattern'=>"{$match[1][$k]}",
                'msg'=>$match[2][$k],
            );
        }
        $this->_ruleset = $ruleset;
    }

    static public function init($rule_file){
        self::$_instance = new self($rule_file);
    }

    static public function getInstance(){
        if(!self::$_instance){
            throw new \Exception(__NAMESPACE__.', Regex not initilized, call Regex::init before use.');
        }
        return self::$_instance;
    }

    public function runTask(BaseTask $task){
        $result = BaseTask::TASK_RESULT_SUCCEED;
        $detail = array();
        $content = $task->getContents();
        foreach($this->_ruleset as $rule){
            $pattern = $rule['pattern'];
            $msg = $rule['msg'];
            if (preg_match_all($pattern, $content, $match)){
                $result = BaseTask::TASK_RESULT_FAILED;
                foreach($match[0] as $v){
                    $detail[] = array('content'=>$v, 'msg'=>$msg);
                }
            }
        }
        $task->setResult($result, $detail);
        return true;
    }
}