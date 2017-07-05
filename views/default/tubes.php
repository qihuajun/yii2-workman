<?php
/**
 * Created by PhpStorm.
 * User: qihuajun
 * Date: 2017/5/31
 * Time: 下午5:32
 */
use yii\grid\GridView;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Tubes');
$this->params['breadcrumbs'][] = $this->title;

?>


<div class="test-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <p>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                    'class' => \yii\grid\DataColumn::className(),
                    'label' => 'Name',
                    'format' => 'html',
                    'value' => function ($model, $key, $index, $column){
                        return Html::a($model['name'],['/workman/default/tube','tube'=>$model['name']]);
                    }
            ],
            'total-jobs',
            'current-jobs-urgent',
            'current-jobs-ready',
            'current-jobs-reserved',
            'current-jobs-delayed',
        ],
    ]); ?>
</div>
