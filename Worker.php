<?php

namespace rossoneri\workman;

use rossoneri\workman\job\JobInterface;
use rossoneri\workman\queue\QueueInterface;

/**
 * Created by PhpStorm.
 * User: qihuajun
 * Date: 2017/5/18
 * Time: 下午3:47
 */
class Worker
{
    /**
     * @var QueueInterface
     */
    public $queue;


    public $watches;

    public $interval;

    public $watchOnly;


    public function work(){
        if($this->watchOnly){
            $this->queue->watchOnly($this->watchOnly);
        }else{
            foreach ($this->watches as $watch) {
                $this->queue->watch($watch);
            }
        }

        while ($job = $this->queue->reserve()){
            $this->executeJob($job);

            if($this->interval){
                sleep($this->interval);
            }
        }
    }

    public function executeJob(JobInterface $job){
        try {
            $job->run();
            $this->queue->delete($job);
        } catch (\Exception $e) {
            \Yii::error("Job Executed Faile With Exception: ".$e->getMessage(),'yii.workman.worker');

            if($job->canBeReleased()){
                $this->queue->release($job);
            }elseif($job->canBeBuried()){
                $this->queue->bury($job);
            }else{
                $this->queue->delete($job);
            }
        }
    }
}