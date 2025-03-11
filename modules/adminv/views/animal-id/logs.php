<?php

use yii\grid\GridView;

$this->blocks['content-header'] = 'Логи';
?>
<div class="box">

    <div class="box-body">
        <table class="table table-bordered table-hover">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'pager' => [
                    'lastPageLabel' => '>>>',
                ],
                'columns' => [
                    'id',
                    [
                        'label' => 'Сообщение',
                        'attribute' => 'message',
                    ],
                    [
                        'label' => 'Время',
                        'attribute' => 'log_time',
                        'value' => function($model){
                            return \Yii::$app->formatter->asDatetime($model->log_time, "php:d-m-Y H:i:s");
                        }
                    ],
                ]
            ]);
            ?>
        </table>
    </div>
</div>
