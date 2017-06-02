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

class WorkerRegistry extends Component
{
    public $redisComponentId;

    private $redis;

    const WORKER_ID_KEY = 'Yii2Workers';

    const WORKER_QUEUES_KEY = 'Yii2WorkerQueue';

    const WORKER_QUEUE_WATCHLIST_KEY  = 'WatchQueueWorkers';

    const WATCH_ALL_WORKERS_KEY = 'WatchAallWorkers';

    public function init()
    {
        parent::init();

        $this->redis = \Yii::$app->get($this->redisComponentId);
    }

    public function register(Worker $worker){
        $id = $worker->id;
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

    public function updateStat(Worker $worker){
        $id = $worker->id;
        $stat = $worker->stat();

        $this->redis->hset(self::WORKER_ID_KEY,$id,Json::encode($stat));
    }

    public function unregister(Worker $worker){
        $id = $worker->id;

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
}