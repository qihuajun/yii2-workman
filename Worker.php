<?php

namespace rossoneri\workman;

use rossoneri\workman\job\JobInterface;
use rossoneri\workman\queue\QueueInterface;
use yii\base\Component;

/**
 * Created by PhpStorm.
 * User: qihuajun
 * Date: 2017/5/18
 * Time: 下午3:47
 */
class Worker extends Component
{
    /**
     * @var QueueInterface
     */
    public $queue;

    public $queueComponentId;

    public $watches;

    public $interval;

    public $watchOnly;

    public $timeLimit = 3600;

    public $memoryLimit = 128;

    public $jobLimit = 1000;

    public $jobs = 0;

    public $startTime;

    public $id;


    public function init()
    {
        parent::init();

        $this->queue = \Yii::$app->get($this->queueComponentId);

        if(is_string($this->watches)){
            $this->watches = explode(',',$this->watches);
        }

        $this->startTime = time();

        $this->id = gethostname().getmypid();
    }

    private function shouldExit(){
        if(time() - $this->startTime >= $this->timeLimit){
            return true;
        }

        if(memory_get_usage(true) > $this->memoryLimit * 1024 * 1024){
            return true;
        }

        if($this->jobs > $this->jobLimit){
            return true;
        }

        return false;
    }


    public function work(){
        echo "Working...",PHP_EOL;
        if($this->watchOnly){
            $this->queue->watchOnly($this->watchOnly);
        }else{
            foreach ($this->watches as $watch) {
                $this->queue->watch($watch);
            }
        }

        while (true){
            echo 'Reserving...',PHP_EOL;
            $job = $this->queue->reserve();
            if($job){
                $this->executeJob($job);
            }else{
                if($this->interval){
                    sleep($this->interval);
                }
            }

            if($this->shouldExit()){
                break;
            }
        }

        return ;
    }

    public function executeJob(JobInterface $job){
        try {
            \Yii::info("Receive And Executed Job #{$job->getId()} {$job->getName()}",'yii.workman.worker');

            $job->run();
            $this->queue->delete($job);
        } catch (\Exception $e) {
            \Yii::error("Job Executed Failed With Exception: ".$e->getMessage(),'yii.workman.worker');

            if($this->queue->getAttempted($job) < $job->getMaxTries()){
                $this->queue->release($job);
            }elseif($job->canBeBuried()){
                $this->queue->bury($job);
            }else{
                $this->queue->delete($job);
            }
        }
    }
}