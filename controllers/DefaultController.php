<?php

namespace rossoneri\workman\controllers;
use Pheanstalk\Pheanstalk;
use rossoneri\workman\WorkerRegistry;
use yii\data\ArrayDataProvider;
use yii\helpers\Json;

/**
 * Created by PhpStorm.
 * User: qihuajun
 * Date: 2017/5/31
 * Time: 下午5:27
 */
class DefaultController extends \yii\web\Controller
{


    public function actionIndex(){

//        $queue = new Pheanstalk('172.24.39.35');
//
//        $stat = $queue->stats();
//
//        return print_r($stat,true);


        return $this->render('index');
    }

    public function actionWorkers(){
        $workerList = \Yii::$app->redis->hgetall(WorkerRegistry::WORKER_ID_KEY);
        $workers = [];
        $length = count($workerList);

        for ($i=0;$i<$length;$i=$i+2){
            $workers[$workerList[$i]] = Json::decode($workerList[$i+1]);
            $workers[$workerList[$i]]['id'] = $workerList[$i];
            $workers[$workerList[$i]]['watches'] = implode(',',$workers[$workerList[$i]]['watches']);
        }

        $dataProvider = new ArrayDataProvider([
            'allModels' => $workers,
        ]);

        return $this->render('workers',['dataProvider'=>$dataProvider]);
    }
}