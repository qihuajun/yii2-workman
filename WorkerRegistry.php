<?php
/**
 * Created by PhpStorm.
 * User: qihuajun
 * Date: 2017/5/24
 * Time: 下午4:51
 */

namespace rossoneri\workman;


use yii\base\Component;

class WorkerRegistry extends Component
{
    public $redisComponentId;

    private $redis;

    public function init()
    {
        parent::init();

        $this->redis = \Yii::$app->get($this->redisComponentId);
    }

    public function register(Worker $worker){

    }
}