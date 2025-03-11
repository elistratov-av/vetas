<?php

use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var \app\modules\adminv\models\search\NotificationsSearch $searchModel
 */
$this->blocks['content-header'] = 'Уведомления' ?>

<div class="box">
    <div class="box-body">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'options' => ['class' => 'grid-view table-responsive'],
            'columns' => [
                [
                    'label' => 'Id',
                    'attribute' => 'id',
                    'headerOptions' => ['width' => '5%', 'class' => 'text-center'],
                ],
                [
                    'label' => 'Код события',
                    //'attribute' => 'event_code',
                    'value' => 'event_code',
                ],
                [
                    'label' => 'Время отправки',
                    'attribute' => 'log_time',
                    'value' => function ($model) {
                        return $model->log_time ?? '';
                    },
                    'filter' => \yii\jui\DatePicker::widget(
                        [
                            'model' => $searchModel,
                            'attribute' => 'log_time',
                            'language' => 'ru',
                            'dateFormat' => 'yyyy-MM-dd',
                            'clientOptions' => [
                                'yearRange' => date('Y') - 75 . ':' . date('Y'),
                                'changeMonth' => true,
                                'changeYear' => true,
                                'firstDay' => '1',
                            ],
                        ]),
                ],
                [
                    'label' => 'Отправлено',
                    'attribute' => 'to',
                    'value' => function ($model) {
                        // В таблице есть записи, где значение поля строка
                        // В новых записях формат {"key" => value}
                        $to = json_decode($model->to, true);
                        return isset($to) ? Html::encode(implode("\n", array_values($to))) : $model->to;
                    },
                ],
                [
                    'label' => 'Статус',
                    'attribute' => 'is_success',
                    'value' => function ($model) {
                        if ($model->is_success) {
                            return 'Отправлено';
                        }
                        return 'Ошибка при отправке';
                    },
                ]
            ]
        ])
        ?>
    </div>
</div>
