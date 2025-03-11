<?php

use yii\grid\GridView;
use app\modules\admin\models\StatusLog;
use \yii\helpers\StringHelper;
use yii\helpers\Html;
use yii\helpers\Url;

$this->blocks['content-header'] = 'Логи';
?>
<div class="box">

    <div class="box-body">
        <table class="table table-bordered table-hover">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                'id',
                [
                    'label' => 'Время',
                    'attribute' => 'log_time',
                    'filter' => \yii\jui\DatePicker::widget(
                        [
                            'model' => $searchModel,
                            'attribute' => 'log_time',
                            'language' => 'ru',
                            'dateFormat' => 'yyyy-MM-dd',
                        ]),

                ],
                [
                    'label' => 'Номер сообщения',
                    'attribute' => 'service_number',

                ],
                [
                    'label' => 'Визит',
                    'attribute' => 'visit_id',
                    'format' => 'raw',
                    'value' => function (StatusLog $model) {
                        return Html::a(Html::encode($model->visit_id), Url::to(['visit/info', 'id' => $model->visit_id]));
                    }
                ],
                [
                    'label' => 'ETP статус',
                    'attribute' => 'etp_status',

                ],
                [
                    'label' => 'Сообщение',
                    'attribute' => 'message',
                    'format' => 'raw',
                    'value' => function($model) {
                        if (mb_strlen($model->message) > 150) {
                            $truncated = Yii::$app->formatter->asNtext(StringHelper::truncate($model->message, 150));
                            $full = Yii::$app->formatter->asNtext($model->message);
                            $onclick = "onclick=\"$('.log-{$model->id}') . toggle()\"";
                            $html  = "<div style='display: block' class='log-{$model->id}'>{$truncated}<a href='javascript:void(0);' {$onclick}>Подробнее</a> </div>";
                            $html .= "<div style='display: none' class='log-{$model->id}'>{$full}<br/><a href='javascript:void(0);' {$onclick}>Скрыть</a></div>";
                            return $html;
                        }

                        return Yii::$app->formatter->asNtext($model->message);
                    }

                ],
            ]

        ]);
        ?>
        </table>
    </div>
</div>
