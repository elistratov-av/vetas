<?php

use \app\modules\admin\models\Species;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

$this->blocks['content-header'] = 'Виды животных';
?>

<div class="box">
    <div class="box-body">
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        'id',
        [
            'attribute' => 'name',
            'label' => 'Название',
            'value' => function (Species $model) {
                return Html::a(Html::encode($model->name), Url::to(['species/breeds', 'id' => $model->id]));
            },
            'format' => 'raw',

        ],
        [
            'attribute' => 'description',
            'label' => 'Описание',
        ],
        [
            'attribute' => 'flag_mos_ru',
            'label' => 'Флаг Мос.ру',
            'value' => function ($model) {
                return ($model->flag_mos_ru == 1) ? 'Да' : 'Нет';
            },
        ],
    ]
]);
?>
    </div>
</div>
