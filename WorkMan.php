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

class WorkMan extends Component
{
    /**
     * queue config array
     *
     * please refer to Queue class to see details
     *
     * @var array
     */
    public $queue;

    /**
     * worker options array
     *
     * please refer to Worker class to see details
     *
     * @var array
     */
    public $workerOptions = [];

    /**
     * @var Registry Redis Id, If you want to enable registry function, this must be specified
     */
    public $registeryRedisId;

    /**
     * @var BeanstalkdQueue
     */
    private $beanstalkdQueue;


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->beanstalkdQueue = new BeanstalkdQueue($this->queue);
    }

    /**
     * Dispatch a job
     *
     * @param JobInterface $job
     * @param $tube
     * @param int $priority
     * @param int $delay
     * @param int $ttr
     */
    public function dispatch(JobInterface $job,$tube,$priority=100,$delay=0,$ttr=10){
        $id = $this->beanstalkdQueue->putInTube($job,$tube,$priority,$delay,$ttr);
        $job->setId($id);
    }

    /**
     * Start to work on a tube
     *
     * @param $watches
     * @param array $options
     */
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

    /**
     * return worker registry
     *
     * @return null|WorkerRegistry
     */
    public function getRegistry(){
        if($this->registeryRedisId){
            $registry  = new WorkerRegistry(['redisComponentId'=>$this->registeryRedisId]);
            return $registry;
        }

        return null;
    }

    /**
     * get the queue
     *
     * @return BeanstalkdQueue
     */
    public function getQueue(){
        return $this->beanstalkdQueue;
    }


}