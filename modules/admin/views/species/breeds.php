<?php

use yii\grid\GridView;

$this->blocks['content-header'] = 'Породы';
?>

<div class="box">
    <div class="box-body">
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        'id',
        [
            'label' => 'Название',
            'attribute' => 'name',
        ],
        [
            'label' => 'Описание',
            'attribute' => 'description',
        ],
    ],
]);
?>
    </div>
</div>

