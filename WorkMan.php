<?php
/**
 * Created by PhpStorm.
 * User: qihuajun
 * Date: 2017/5/18
 * Time: 下午4:42
 */

namespace rossoneri\workman;


use yii\base\Component;
use rossoneri\workman\queue\QueueInterface;

class WorkMan extends Component
{
    /**
     * @var QueueInterface
     */
    public $queue;

    public $queueComponentId;

    public function init()
    {
        parent::init();

        $this->queue = \Yii::$app->get($this->queueComponentId);
    }

    public function dispatch($job,$tube,$priority=100,$delay=0,$ttr=10){
        $this->queue->putInTube($job,$tube,$priority,$delay,$ttr);
    }


}