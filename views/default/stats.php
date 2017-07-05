<?php
/**
 * Created by PhpStorm.
 * User: qihuajun
 * Date: 2017/5/31
 * Time: 下午5:54
 */
use yii\grid\GridView;
use yii\helpers\Html;


$this->params['breadcrumbs'][] = [
    'url' => '/workman',
    'label'=> 'Workman'
];
$this->params['breadcrumbs'][] = $this->title;
?>


<div class="test-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'key',
            'value',
        ],
    ]); ?>
</div>