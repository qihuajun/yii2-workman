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

    public $timeLimit = 3600;

    public $memoryLimit = 128;

    public $jobLimit = 1000;

    public $jobs = 0;

    public $startTime;

    public $id;

    private $beTerminated = false;

    private $shouldQuit = false;

    /**
     * @var WorkerRegistry
     */
    public $registry;

    public $registryComponentId;

    public function init()
    {
        parent::init();

        $this->queue = \Yii::$app->get($this->queueComponentId);

        if(is_string($this->watches)){
            $this->watches = explode(',',$this->watches);
        }

        $this->startTime = time();

        $this->id =  gethostname().'_'.getmypid();

        $this->registry = \Yii::$app->get($this->registryComponentId);

        $this->registry->register($this);

        $this->setSignalHandler();
    }

    public function stat(){
        $data = [
            'startTime' => $this->startTime,
            'memoryUsage' => $this->getMemoryUsage(),
            'jobs' => $this->jobs,
            'watches' => $this->watches
        ];

        return $data;
    }

    private function shouldExit(){
        if($this->getRunTime() >= $this->timeLimit){
            return true;
        }

        if($this->getMemoryUsage() > $this->memoryLimit){
            return true;
        }

        if($this->jobs > $this->jobLimit){
            return true;
        }

        return $this->beTerminated || $this->shouldQuit || false;
    }

    public function getRunTime(){
        return time() - $this->startTime;
    }

    public function getMemoryUsage(){
        return memory_get_usage(true) / 1024 /1024 ;
    }


    public function work(){
        echo "Working...",PHP_EOL;
        if($this->watches){
            foreach ($this->watches as $watch) {
                $this->queue->watch($watch);
            }
        }

        while (true){
            echo 'Reserving...',PHP_EOL;
            $job = $this->queue->reserve(0);
            if($job){
                $this->jobs++;
                $this->executeJob($job);
            }else{
                if($this->interval){
                    sleep($this->interval);
                }
            }

            $this->registry->updateStat($this);

            pcntl_signal_dispatch();

            if($this->shouldExit()){
                break;
            }
        }

        $this->registry->unregister($this);

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


    /**
     * Enable async signals for the process.
     *
     * @return void
     */
    protected function setSignalHandler()
    {
        pcntl_signal(SIGTERM, function () {
            \Yii::info("Receive SIGTERM Signal, Worker Exit",'yii.workman.worker');
            $this->beTerminated = true;
        });

        pcntl_signal(SIGHUP, function () {
            \Yii::info("Receive SIGHUP Signal, Worker Exit",'yii.workman.worker');
            $this->shouldQuit = true;
        });

        pcntl_signal(SIGUSR2, function () {
            \Yii::info("Receive Quit Signal, Worker Quit",'yii.workman.worker');
            $this->shouldQuit = true;
        });

        pcntl_signal(SIGINT, function () {
            \Yii::info("Receive Interept Signal, Worker Quit",'yii.workman.worker');
            $this->shouldQuit = true;
        });
    }
}