<?php

use yii\grid\GridView;
use yii\helpers\ArrayHelper;

/* @var $this \yii\web\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $searchModel \app\modules\adminfstek\models\search\LogExternalAuthSearch */
/* @var $successOptions array */

$this->title = 'Авторизация';

$this->blocks['content-header'] = $this->title;
?>
<div class="box">
    <div class="box-body">
        <?php echo GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'formatter' => [
                'class' => 'yii\i18n\Formatter',
                'nullDisplay' => '-',
            ],
            'options' => ['class' => 'grid-view table-responsive'],
            'columns' => [
                [
                    'attribute' => 'created_at',
                    'headerOptions' => ['width' => '10%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                    'filter' => \yii\jui\DatePicker::widget(
                        [
                            'model' => $searchModel,
                            'attribute' => 'created_at',
                            'language' => 'ru',
                            'dateFormat' => 'yyyy-MM-dd',
                        ]),
                ],
                [
                    'attribute' => 'service_name',
                    'headerOptions' => ['width' => '12%', 'class' => 'text-center'],
                    'filter' => $searchModel::serviceOptions(),
                ],
                [
                    'attribute' => 'is_success',
                    'headerOptions' => ['width' => '10%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                    'value' => function ($model) use ($successOptions) {
                        /* @var $model \app\models\db\audit\LogExternalAuth */
                        return ArrayHelper::getValue($successOptions, (int)$model->is_success, '-');
                    },
                    'filter' => $successOptions,
                ],
                [
                    'attribute' => 'protocol',
                    'headerOptions' => ['width' => '10%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                ],
                [
                    'attribute' => 'interface',
                    'headerOptions' => ['width' => '15%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center small'],
                ],
                [
                    'attribute' => 'ip',
                    'headerOptions' => ['width' => '10%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                ],
            ],
        ]);
        ?>
    </div>
</div>

