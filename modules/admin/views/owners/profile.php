<?php

use yii\grid\ActionColumn;
use yii\grid\GridView;use yii\helpers\ArrayHelper;
use \yii\helpers\Html;
use yii\helpers\Url;

/* @var $model \app\modules\admin\models\Owners */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $contactTypes array */

$this->blocks['content-header'] = 'Профиль владельца ' . Html::encode($model->fullname) ?>

<div class="box">

    <div class="box-body">
        <table class="table table-bordered table-hover">
            <tr>
                <th>
                    Имя
                </th>
                <td>
                    <?= Html::encode($model->fullname) ?>
                </td>
            </tr>
            <tr>
                <th>
                    Дата рождения
                </th>
                <td>
                    <?= Html::encode($model->birthday) ?>
                </td>
            </tr>
            <tr>
                <th>
                    Адрес
                </th>
                <td>
                    <? if ($model->id_fias_address) {
                        echo Html::encode($model->fiasAddress->full_address);
                    } else {
                        echo '';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th>
                    Питомцы
                </th>
                <td>
                    <? foreach ($model->petsToOwners as $pett) {
                        echo $pett->pet->name;
                        echo "<br>";
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th>
                    Приемы
                </th>
                <td>
                    <?php
                        $tickets = ArrayHelper::map($model->visits, 'id', 'ticket_number');
                        $ids = ArrayHelper::getColumn($model->visits, 'id');
                        foreach ($ids as $id) {
                            echo Html::a($id, Url::to(['visit/info', 'id' => $id]), [
                                'target' => '_blank',
                            ]) . '<br>';
                        }
                    ?>
                </td>
            </tr>
        </table><br>

        <h4>Контакты</h4>
        <?= Html::a(
            '<span class="glyphicon glyphicon-plus"></span> Новый',
            Url::to(['owners/add-contact', 'owner' => $model->id]),
            ['class' => 'btn btn-sm btn-success btn-flat']
        )?>
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'label' => 'Контакт',
            'attribute' => 'name',
        ],
        [
            'label' => 'Тип',
            'attribute' => 'id_contact_type',
            'value' => function ($model) use ($contactTypes) {
                return ArrayHelper::getValue($contactTypes, $model->id_contact_type, '');
            },
        ],
        [
            'class' => ActionColumn::className(),
            'template' => '{edit-contact} {remove-contact}',
            'buttons' => [
                'edit-contact' => function ($url, $model, $key)
                {
                    return Html::a(
                        '<span class="glyphicon glyphicon-pencil"></span>',
                        Url::to(['owners/edit-contact', 'id' => $model->id, 'owner' => $model->entity_id]),
                        ['class' => 'btn btn-xs btn-warning', 'title' => 'Редактировать']
                    );
                },
                'remove-contact' => function ($url, $model, $key)
                {
                    return Html::a(
                        '<span class="glyphicon glyphicon-trash"></span>',
                        Url::to(['owners/remove-contact', 'id' => $model->id, 'owner' => $model->entity_id]),
                        [
                            'class' => 'btn btn-xs btn-danger',
                            'title' => 'Удалить',
                            'data-confirm' => 'Вы действительно хотите удалить контакт? Действие нельзя отменить.',
                        ]
                    );
                },
            ],
        ],

    ]
    ])
?>

    </div>
</div>
