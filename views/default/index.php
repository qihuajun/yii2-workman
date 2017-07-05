<?php
/**
 * Created by PhpStorm.
 * User: qihuajun
 * Date: 2017/5/31
 * Time: 下午5:32
 */
use yii\grid\GridView;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Workman');
$this->params['breadcrumbs'][] = $this->title;

?>


<div class="test-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <p>
        There are <?= $workers ?> workers watching <?= Html::a($tubes,'/workman/default/tubes') ?> tubes now.

        <?= Html::a('Queue Stats','/workman/default/queue') ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            'startTime:datetime',
            'jobs',
            'memoryUsage',
            'watches',
        ],
    ]); ?>
</div>
