<?php

namespace rossoneri\workman\controllers;
use rossoneri\workman\WorkerRegistry;
use yii\base\InvalidConfigException;
use yii\data\ArrayDataProvider;

/**
 * Created by PhpStorm.
 * User: qihuajun
 * Date: 2017/5/31
 * Time: 下午5:27
 */
class DefaultController extends \yii\web\Controller
{

    /**
     * Index, display workers info
     *
     * @return string
     * @throws InvalidConfigException
     */
    public function actionIndex(){
        /**
         * @var WorkerRegistry
         */
        $registry =\Yii::$app->workman->getRegistry();

        if(!$registry){
            throw new InvalidConfigException("Registry must be enabled for workman");
        }

        $workers =  $registry->countWorkers();
        $tubes =  $registry->countTubes();
        $workerList = $registry->listWorkers();

        $dataProvider = new ArrayDataProvider([
            'allModels' => $workerList,
        ]);

        return $this->render('index',['dataProvider'=>$dataProvider,'workers'=>$workers,'tubes'=>$tubes]);
    }

    /**
     * Queue Stats
     *
     * @return string
     */
    public function actionQueue(){
        $queue = \Yii::$app->workman->getQueue();
        $info = $queue->stats();

        $data = [];

        foreach ($info as $key => $value) {
            $item = [
                'key' => $key,
                'value' => $value
            ];

            $data[] = $item;
        }

        $dataProvider = new ArrayDataProvider([
            'allModels' => $data,
        ]);

        $this->view->title = "Queue";

        return $this->render('stats',['dataProvider'=>$dataProvider]);
    }

    /**
     * Tubes list page
     *
     * @return string
     */
    public function actionTubes(){
        $queue = \Yii::$app->workman->getQueue();
        $tubes = $queue->listTubes();
        $infos = [];
        foreach ($tubes as $tube) {
            $infos[] = $queue->statsTube($tube);
        }

        $dataProvider = new ArrayDataProvider([
            'allModels' => $infos,
        ]);

        $this->view->title = "Tubes";

        return $this->render('tubes',['dataProvider'=>$dataProvider]);
    }


    /**
     * Tube stats
     *
     * @param string $tube
     * @return string
     */
    public function actionTube($tube='default'){
        $queue = \Yii::$app->workman->getQueue();
        $info = $queue->statsTube($tube);

        $data = [];

        foreach ($info as $key => $value) {
            $item = [
                'key' => $key,
                'value' => $value
            ];

            $data[] = $item;
        }

        $dataProvider = new ArrayDataProvider([
            'allModels' => $data,
        ]);

        $this->view->title = "Tube: $tube";

        return $this->render('stats',['dataProvider'=>$dataProvider]);
    }
}