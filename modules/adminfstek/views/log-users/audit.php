<?php

use yii\grid\GridView;
use yii\grid\ActionColumn;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $searchModel \app\modules\adminfstek\models\search\LogAuditSearch */
/* @var $typeOptions array */
/* @var $successOptions array */

$css = <<<CSS
pre{
    white-space: pre-wrap;
}
CSS;

$this->registerCss($css);

$js = <<<JS
$('body').on('click', 'a.show-detail', function (e) {
    e.preventDefault();
    var url = $(this).attr('href');
    $.ajax({
        url: url,
        type: 'get',
        cache: false,
        success: function(response) {
            $('#audit-modal').html(response);          
            $('#audit-modal').modal('show');          
        },
        error: function() {
            alert('Ошибка запроса');
        }
    });
});
$('#audit-modal').on('hidden.bs.modal', function (e) {
    $(this).html('');
});
JS;

$this->registerJs($js);

$this->title = 'Изменение данных';

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
                    'attribute' => 'id_user',
                    'label' => 'ID автора запроса',
                    'headerOptions' => ['width' => '10%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                ],
                [
                    'attribute' => 'login',
                    'label' => 'Автор запроса',
                    'headerOptions' => ['width' => '20%', 'class' => 'text-center'],
                ],
                [
                    'attribute' => 'action',
                    'label' => 'Тип события',
                    'headerOptions' => ['width' => '15%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                    'value' => function ($model) use ($typeOptions) {
                        /* @var $model \app\models\db\audit\AuditLog */
                        return ArrayHelper::getValue($typeOptions, $model->action, '-');
                    },
                    'filter' => $typeOptions,
                ],
                [
                    'label' => 'Дата и время',
                    'attribute' => 'date',
                    'headerOptions' => ['width' => '10%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                    'filter' => \yii\jui\DatePicker::widget(
                        [
                            'model' => $searchModel,
                            'attribute' => 'date',
                            'language' => 'ru',
                            'dateFormat' => 'yyyy-MM-dd',
                        ]),
                ],
                [
                    'attribute' => 'is_success',
                    'label' => 'Результат',
                    'headerOptions' => ['width' => '10%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                    'value' => function ($model) use ($successOptions) {
                        /* @var $model \app\models\db\audit\AuditLog */
                        $is_success = ($model->action == $model::ACTION_SYSTEM_FAIL) ? 0 : 1;
                        return ArrayHelper::getValue($successOptions, $is_success, '-');
                    },
                    'filter' => $successOptions,
                ],
                [
                    'class' => ActionColumn::class,
                    'headerOptions' => ['width' => '10%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center', 'style' => 'white-space: nowrap !important;'],
                    'template' => '{view}',
                    'buttons' => [
                        'view' => function ($url, $model, $key) {
                            /* @var $model \app\models\db\audit\AuditLog */
                            $html = Html::a(
                                '<span class="fa fa-fw fa-search-plus"></span>',
                                ['log-detail', 'id' => $model->id],
                                ['class' => 'btn btn-xs btn-info show-detail', 'title' => 'Подробнее']
                            );
                            return $html;
                        },
                    ],
                ],
            ],
        ]);
        ?>
    </div>
</div>
<div class="modal fade  bd-example-modal-lg" id="audit-modal" role="dialog"></div>
