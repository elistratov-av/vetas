<?php

use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

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
                    <?php if ($model->id_fias_address) {
                        echo Html::encode($model->fias_addresses->full_address);
                    } else {
                        echo '';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th>
                    Факт адрес
                </th>
                <td>
                    <?php if ($model->id_fact_fias_address) {
                        echo Html::encode($model->fact_fias_addresses->full_address);
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
                    <?php foreach ($model->petsToOwners as $pett) {
                        echo $pett->pet->name;
                        echo "<br>";
                    }
                    ?>
                </td>
            </tr>
        </table>
        <br>

        <h4>Контакты</h4>

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
            ]
        ])
        ?>

    </div>
</div>
