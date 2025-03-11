<?php

use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $searchModel \app\modules\adminfstek\models\search\LogExternalDataSearch */

$typeOptions = $searchModel::typeOptions();
$serviceOptions = $searchModel::serviceOptions();

$css = <<<CSS
pre{
    white-space: pre-wrap;
}
CSS;

$this->registerCss($css);

$js = <<<JS
$('body').on('click', 'a.show-detail', function (e) {
    e.preventDefault();
    var html = $(this).closest('td').find('.payload-detail').clone(true);
    $('#payload-detail-modal').find('.modal-body > .row').html(html);
    $('#payload-detail-modal').find('.payload-detail').show();
    $('#payload-detail-modal').modal('show');
});
$('#payload-detail-modal').on('hidden.bs.modal', function (e) {
    $(this).find('.modal-body > .row').html('');
});
JS;

$this->registerJs($js);

$this->title = 'Обмен данными';

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
                    'attribute' => 'date_time',
                    'headerOptions' => ['width' => '10%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                    'filter' => \yii\jui\DatePicker::widget(
                        [
                            'model' => $searchModel,
                            'attribute' => 'date_time',
                            'language' => 'ru',
                            'dateFormat' => 'yyyy-MM-dd',
                        ]),
                ],
                [
                    'attribute' => 'service_name',
                    'headerOptions' => ['width' => '25%', 'class' => 'text-center'],
                    'filter' => $serviceOptions,
                ],
                [
                    'attribute' => 'type',
                    'headerOptions' => ['width' => '20%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                    'value' => function ($model) use ($typeOptions) {
                        /* @var $model array */
                        return ArrayHelper::getValue($typeOptions, (int)$model['type'], '-');
                    },
                    'filter' => $typeOptions,
                ],
                [
                    'class' => ActionColumn::class,
                    'headerOptions' => ['width' => '10%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center', 'style' => 'white-space: nowrap !important;'],
                    'template' => '{view}',
                    'buttons' => [
                        'view' => function ($url, $model, $key) {
                            /* @var $model array */
                            $html = '<div class="payload-detail col-md-12" style="display: none"><pre>' . Html::encode($model['payload'], false) . '</pre></div>';
                            $html .= Html::a(
                                '<span class="fa fa-fw fa-search-plus"></span>',
                                '#',
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
<div class="modal fade bd-example-modal-lg" id="payload-detail-modal" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Данные запроса</h4>
            </div>
            <div class="modal-body">
                <div class="row"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>
