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

    /**
     * the tubes needs to be watched
     *
     * @var array
     */
    public $watches;

    /**
     * the time in seconds a worker sleeps when no job arrived
     *
     * @var int
     */
    public $interval = 5;

    /**
     * the time in seconds a worker can run at max
     *
     * @var int
     *
     */
    public $timeLimit = 3600;


    /**
     * Memory limit in MB of a worker
     *
     * @var int
     */
    public $memoryLimit = 128;


    /**
     * Job limit in MB of a worker, if the worker handled $jobLimit jobs, it will exit
     *
     * @var int
     */
    public $jobLimit = 1000;

    /**
     * Jobs count that a worker has run
     *
     * @var int
     */
    private $jobs = 0;

    /**
     * start timestamp of a worker
     *
     * @var int
     */
    private $startTime;

    /**
     * identifier of a worker
     *
     * @var string
     */
    private $id;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * flag represents whether the worker process is terminated
     *
     * @var bool
     */
    private $beTerminated = false;


    /**
     * flag represents whether the worker shuold quit
     *
     * @var bool
     */
    private $shouldQuit = false;

    /**
     * @var WorkerRegistry
     */
    public $registry;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if(is_string($this->watches)){
            $this->watches = explode(',',$this->watches);
        }

        $this->startTime = time();

        $this->id =  gethostname().'_'.getmypid();

        if($this->registry){
            $this->registry->register($this);
        }

        $this->setSignalHandler();
    }


    /**
     *  return stat of a worker
     *
     * @return array
     *
     */
    public function stat(){
        $data = [
            'startTime' => $this->startTime,
            'memoryUsage' => $this->getMemoryUsage(),
            'jobs' => $this->jobs,
            'watches' => $this->watches
        ];

        return $data;
    }


    /**
     * check whether the worker should exit
     *
     * @return bool
     */
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

    /**
     * return the worker's uptime
     *
     * @return int
     */
    public function getRunTime(){
        return time() - $this->startTime;
    }

    /**
     * return the worker's memory usage in MB
     *
     * @return float
     */
    public function getMemoryUsage(){
        return memory_get_usage(true) / 1024 /1024 ;
    }

    /**
     * Receive and Execute jobs
     */
    public function work(){
        \Yii::info("Worer {$this->id} Start working ",'yii.workman.worker');
        if($this->watches){
            foreach ($this->watches as $watch) {
                $this->queue->watch($watch);
            }
        }

        while (true){
            $job = $this->queue->reserve(0);
            if($job){
                $this->jobs++;
                $this->executeJob($job);
            }else{
                if($this->interval){
                    sleep($this->interval);
                }
            }

            if($this->registry){
                $this->registry->updateStat($this);
            }


            pcntl_signal_dispatch();

            if($this->shouldExit()){
                \Yii::info("Worer {$this->id} should exit ",'yii.workman.worker');
                break;
            }
        }

        if($this->registry){
            $this->registry->unregister($this);
        }


        return ;
    }

    /**
     * Execute the job
     *
     * @param JobInterface $job
     */
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