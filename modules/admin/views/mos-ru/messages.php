<?php

/**
 * @var string[] $dataProvider
 * @var string[] $searchModel
 */

use kartik\daterange\DateRangePicker;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\models\search\MessageSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->blocks['content-header'] = 'Записи';
$js = <<<JS
    $('.show-visit').click(function(){
        var link = $(this);
        $.get(link.attr('href'), function(html){
            $('#visitModal').html(html);
            $('#visitModal').modal('show');
            }, 'html');
        return false;
    });    
JS;

$this->registerJs($js, View::POS_READY);
?>

<div class="box">
    <div class="box-body">
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
        [
            'attribute' => 'id',
            'headerOptions' => ['width' => '100px', 'class' => 'text-center'],
        ],
        [
            'label' => 'Сервисный номер',
            'attribute' => 'service_number',
            'value' => function ($model) { //номер некликабелен, если визит уже удален (чтобы не щелкать все подряд номера и упираться в пустую страницу)
                if($model->visit) {
                    return Html::a(Html::encode($model->service_number), Url::to(['visit/info', 'id' => $model->visit_id]), [
                        'target' => '_blank',
                    ]);
                } else {
                    return Html::encode($model->service_number);

                }
            },
            'format' => 'raw'
        ],
        [
            'label' => 'Время создания',
            'value' => 'created_at',
            'filter' => DateRangePicker::widget([
                'model' => $searchModel,
                'attribute'=>'created_at',
                'convertFormat'=>true,
                'startAttribute'=>'from',
                'endAttribute'=>'to',
                'pluginEvents' => [
                    'cancel.daterangepicker' => new JsExpression("
                    function (ev, picker) {
                        picker.element.val('').change();
                    }")
                ],
                'pluginOptions' => [
                    'opens' => 'right',
                    'locale' => [
                        'cancelLabel' => 'Очистить',
                        'format' => 'Y-m-d',
                    ],
                ],
            ])
        ],
        [
            'label' => 'Телефон',
            'attribute' => 'phone',
        ],
        [
            'label' => 'Фамилия',
            'attribute' => 'last_name',
        ],
        [
            'label' => 'Имя',
            'attribute' => 'first_name',
        ],
        [
            'label' => 'Отчество',
            'attribute' => 'middle_name',
        ],
        [
            'format' => 'raw',
            'value' => function ($model) {
                if ($model->visit) {
                    return Html::a(
                        '<span class="glyphicon glyphicon-eye-open">',
                        ['mos-ru/visit-data', 'visit_id' => $model->visit_id],
                        [
                            'class' => 'btn btn-xs btn-success show-visit',
                            'title' => 'Информация о приеме'
                        ]
                    );
                } else {
                    return '';
                }
            },
        ]
    ]
]);
?>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="visitModal" role="dialog">

</div>