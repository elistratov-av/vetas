<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 22.01.19
 * Time: 13:24
 */

use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

$this->blocks['content-header'] = 'новые метки';
 ?>

    <div class="box">
        <div class="box-body">
            <table class="table table-bordered table-hover">
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
        'id',
        'id_pet',
        [
            'label' => 'Тип метки',
            'attribute' => 'ident_type',
            'value' => 'ident_type.name',
        ],
        'identification_code',
        [
            'label' => 'Питомец',
            'attribute' => 'pet',
            'value' => function ($model) {
                return Html::a(Html::encode($model->pet->name), Url::to(['pets/profile', 'id' => $model->pet->id]), [
                    'target' => '_blank',
                ]);
            },
            'format' => 'raw',
        ],
        [
            'class' => ActionColumn::class,
            'template' => '{chip-edit} {chip_remove}',
            'buttons' => [
                'chip-edit' => function ($url, $model, $key)
                {
                    if($model->pet['id']) {
                        return Html::a(
                            '<span class="glyphicon glyphicon-pencil"></span>',
                            Url::to(['pets/chip-edit', 'id' => $model->id, 'id_pet' => $model->pet['id']]),
                            ['class' => 'btn btn-xs btn-warning', 'title' => 'Редактировать']
                        );
                    }
                },
                'chip_remove' => function ($url, $model, $key)
                {
                    return Html::a(
                        '<span class="glyphicon glyphicon-trash"></span>',
                        Url::to(['pets/chip-remove', 'id' => $model->id]),
                        [
                            'class' => 'btn btn-xs btn-danger',
                            'title' => 'Удалить',
                            'data-confirm' => 'Вы действительно хотите удалить метку? Действие нельзя отменить.',
                        ]
                    );
                },
            ],
        ],
    ]
    ])?>
            </table>
        </div>
    </div>
111;