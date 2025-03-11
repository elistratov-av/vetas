<?php

use yii\grid\GridView;

$this->blocks['content-header'] = 'Ошибки';
?>
<div class="box">

    <div class="box-body">
        <table class="table table-bordered table-hover">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'columns' => [
                    'id',
                    [
                        'label' => 'Данные',
                        'attribute' => 'data',
                        'headerOptions' => ['width' => '17%', 'class' => 'text-center'],
                        'value' => function($model){
                            return json_encode($model->data, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                        }
                    ],
                    [
                        'label' => 'Действие',
                        'headerOptions' => ['width' => '17%', 'class' => 'text-center'],
                        'attribute' => 'action',
                    ],
                    [
                        'label' => 'Тип',
                        'headerOptions' => ['width' => '17%', 'class' => 'text-center'],
                        'attribute' => 'type',
                    ],
                    [
                        'label' => 'Причина',
                        'headerOptions' => ['width' => '30%'],
                        'attribute' => 'reason',
                    ],
                    [
                        'label' => 'Время',
                        'headerOptions' => ['width' => '17%', 'class' => 'text-center'],
                        'attribute' => 'timestamp',
                        'value' => function($model){
                            return \Yii::$app->formatter->asDatetime($model->timestamp, "php:d-m-Y H:i:s");
                        }
                    ],
                ]

            ]);
            ?>
        </table>
    </div>
</div>
