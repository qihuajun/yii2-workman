<?php
/**
 * Created by PhpStorm.
 * User: qihuajun
 * Date: 2017/5/18
 * Time: 下午4:42
 */

namespace rossoneri\workman;


use rossoneri\workman\job\JobInterface;
use rossoneri\workman\queue\BeanstalkdQueue;
use yii\base\Component;
use rossoneri\workman\queue\QueueInterface;

class WorkMan extends Component
{
    /**
     * @var array
     */
    public $queue;

    public $workerOptions = [];

    public $registeryRedisId;

    /**
     * @var BeanstalkdQueue
     */
    private $beanstalkdQueue;




    public function init()
    {
        parent::init();

        $this->beanstalkdQueue = new BeanstalkdQueue($this->queue);
    }


    public function dispatch(JobInterface $job,$tube,$priority=100,$delay=0,$ttr=10){
        $id = $this->beanstalkdQueue->putInTube($job,$tube,$priority,$delay,$ttr);
        $job->setId($id);
    }

    public function work($watches,$options = []){
        $config = [
            'queue' => $this->beanstalkdQueue,
            'watches' => $watches
        ];

        if($this->registeryRedisId){
            $registry  = new WorkerRegistry(['redisComponentId'=>$this->registeryRedisId]);
            $config['registry'] = $registry;
        }

        $config = array_merge($config,$this->workerOptions,$options);

        $worker = new Worker($config);

        $worker->work();
    }


}