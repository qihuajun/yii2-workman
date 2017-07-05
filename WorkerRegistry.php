<?php
/**
 * Created by PhpStorm.
 * User: qihuajun
 * Date: 2017/5/24
 * Time: 下午4:51
 */

namespace rossoneri\workman;


use yii\base\Component;
use yii\helpers\Json;
use yii\redis\Connection;

class WorkerRegistry extends Component
{

    /**
     *
     * the component id of redis
     *
     * @var string
     */
    public $redisComponentId;

    /**
     * Redis connection component
     *
     * @var Connection
     */
    private $redis;

    /**
     * redis key fo worker ids
     */
    const WORKER_ID_KEY = 'Yii2Workers';

    /**
     * redis key that records which queues are listened in a worker
     */
    const WORKER_QUEUES_KEY = 'Yii2WorkerQueue';

    /**
     * redis key that records which workers are listening the queue
     */
    const WORKER_QUEUE_WATCHLIST_KEY  = 'WatchQueueWorkers';

    /**
     * redis key that records id of workers which has no queue specified
     */
    const WATCH_ALL_WORKERS_KEY = 'WatchAallWorkers';


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->redis = \Yii::$app->get($this->redisComponentId);
    }

    /**
     * Register a worker
     *
     * @param Worker $worker
     */
    public function register(Worker $worker){
        $id = $worker->getId();
        $stat = $worker->stat();

        $this->redis->hset(self::WORKER_ID_KEY,$id,Json::encode($stat));

        $watches = $stat['watches'];
        if($watches){
            foreach ($watches as $watch) {
                $this->redis->hincrby(self::WORKER_QUEUES_KEY,$watch,1);
                $this->redis->sadd($watch,$id);
            }
        }else{
            $this->redis->sadd(self::WATCH_ALL_WORKERS_KEY,$id);
        }
    }

    /**
     * update a worker's stat
     *
     * @param Worker $worker
     */
    public function updateStat(Worker $worker){
        $id = $worker->getId();
        $stat = $worker->stat();

        $this->redis->hset(self::WORKER_ID_KEY,$id,Json::encode($stat));
    }

    /**
     * unregister a worker
     *
     * @param Worker $worker
     */
    public function unregister(Worker $worker){
        $id = $worker->getId();

        if($worker->watches){
            foreach ($worker->watches as $watch) {
                $this->redis->srem($watch,$id);
                $this->redis->hincrby(self::WORKER_QUEUES_KEY,$watch,-1);
            }
        }else{
            $this->redis->srem(self::WATCH_ALL_WORKERS_KEY,$id);
        }

        $this->redis->hdel(self::WORKER_ID_KEY,$id);
    }

    /**
     * return count of workers
     *
     * @return mixed
     */
    public function countWorkers(){
        return $this->redis->hlen(self::WORKER_ID_KEY);
    }

    /**
     * return count of tubes being watched
     *
     * @return mixed
     */
    public function countTubes(){
        return $this->redis->hlen(self::WORKER_QUEUES_KEY);
    }

    /**
     * get worker list
     *
     * @return array
     */
    public function listWorkers(){
        $workerList = $this->redis->hgetall(WorkerRegistry::WORKER_ID_KEY);
        $workers = [];
        $length = count($workerList);

        for ($i=0;$i<$length;$i=$i+2){
            $workers[$workerList[$i]] = Json::decode($workerList[$i+1]);
            $workers[$workerList[$i]]['id'] = $workerList[$i];
            $workers[$workerList[$i]]['watches'] = implode(',',$workers[$workerList[$i]]['watches']);
        }

        return $workers;
    }
}