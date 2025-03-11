<?php

use app\modules\admin\models\GovServices;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

$this->blocks['content-header'] = 'Услуги';
?>

<div class="box">
    <div class="box-body">
        <?= Html::a(
            '<span class="glyphicon glyphicon-plus"></span> Добавить',
            Url::to(['mos-ru/add']),
            ['class' => 'btn btn-sm btn-success btn-flat']
        )?>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'options' => ['class' => 'grid-view table-responsive'],
    'columns' => [
        'id',
        [
            'label' => 'Название услуги',
            'attribute' => 'name',
        ],
        [
            'label' => 'Доступны дома',
            'attribute' => 'at_home',
            'value' => function($model) {
                return ($model->at_home == 1) ? "Да" : "";
            }
        ],
        [
            'format' => 'raw',
            'value' =>  function(GovServices $model)
            {
                return Html::a(
                    '<span class="glyphicon glyphicon-pencil">',
                    Url::to(['mos-ru/edit', 'id' => $model->id]),
                    ['class' => 'btn btn-xs btn-warning', 'title' => 'Редактировать']
                );
            }
        ],
    ],
]);
?>
    </div>
</div>
