<?php

namespace Format_Checker;

use Format_Checker\Task\BaseTask;
use Format_Checker\Task\FileTask;
use Format_Checker\Parse\DiffParse;
use Format_Checker\Task\Handler\RegexHandler;
use Format_Checker\Task\DiffTask;

class Application{

    public function run(){
        $exit_code = 0;
        $this->init();
        try {
            echo "start format checker...\n\n";
            $tasks = $this->makeTasks();
            foreach($tasks as $task){
                $task->run();
            }
            foreach($tasks as $task){
                if ($task->getResult() == BaseTask::TASK_RESULT_FAILED){
                    $exit_code = 1;
                }
                $task->displayResult();
            }
            echo "\nend format checker...\n";
        } catch (\Exception $e) {
            echo 'format checker process failed with message: ', $e->getMessage(), "\n";
            $exit_code = 1;
        }
        exit($exit_code);
    }

    private function init(){
        $rule_file = __DIR__.'/Ruleset.ini';
        RegexHandler::init($rule_file);
    }

    private function makeTasks(){
        $tasks = array();
        // 优先读取参数中的信息
        $args = $_SERVER['argv'];
        unset($args[0]);
        if (!empty($args)){
            foreach($args as $v){
                if (file_exists($v)){
                    $task = new FileTask($v, RegexHandler::getInstance());
                }
                if ($task){
                    $tasks[] = $task;
                }
            }
        }
        else{
            $content = file_get_contents("php://stdin");
            if ($content){
                $diff = new DiffParse($content);
                foreach($diff->getDiffs() as $v){
                    if (!isset($v['file_name']) || !isset($v['changes']) || empty($v['changes'])){
                        continue;
                    }
                    foreach($v['changes'] as $sv){
                        if (!$sv['add_content']){
                            continue;
                        }
                        $task = new DiffTask($v['file_name'], $sv['add_content'], RegexHandler::getInstance());
                        if ($task){
                            $tasks[] = $task;
                        }
                    }
                }
            }
        }
        return $tasks;
    }
}

?>