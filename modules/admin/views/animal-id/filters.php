<?php

use yii\grid\GridView;

$this->blocks['content-header'] = 'Фильтры';
?>
<div class="box">

    <div class="box-body">
        <table class="table table-bordered table-hover">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'columns' => [
                    'id',
                    [
                        'label' => 'Тип',
                        'attribute' => 'type',
                    ],
                    [
                        'label' => 'Поле',
                        'attribute' => 'field',
                    ],
                    [
                        'label' => 'Значение',
                        'attribute' => 'value',
                    ],
                    [
                        'label' => 'Направление',
                        'attribute' => 'dir',
                    ],
                ]

            ]);
            ?>
        </table>
    </div>
</div>
