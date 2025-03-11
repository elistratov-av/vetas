<?php

use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var \app\modules\adminv\models\search\PetOwnersSearch $searchModel
 */
$this->blocks['content-header'] = 'Питомцы' ?>

<div class="box">
    <div class="box-body">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'options' => ['class' => 'grid-view table-responsive'],
            'columns' => [
                [
                    'attribute' => 'id',
                    'headerOptions' => ['width' => '5%', 'class' => 'text-center'],
                ],
                [
                    'label' => 'Имя',
                    'attribute' => 'name',
                    'value' => 'name',
                ],
                [
                    'label' => 'Дата рождения',
                    'attribute' => 'birthday',
                    'value' => 'birthday',
                    'filter' => \yii\jui\DatePicker::widget(
                        [
                            'model' => $searchModel,
                            'attribute' => 'birthday',
                            'language' => 'ru',
                            'dateFormat' => 'yyyy-MM-dd',
                            'clientOptions' => [
                                'yearRange' => date('Y') - 75 . ':' . date('Y'),
                                'changeMonth' => 'true',
                                'changeYear' => 'true',
                                'firstDay' => '1',
                            ],
                        ]),
                ],
                [
                    'label' => 'Пол',
                    'attribute' => 'sex',
                    'value' => function ($model) {
                        switch ($model->sex) {
                            case 'f':
                                return 'Ж';
                            case 'm':
                                return 'M';
                            default:
                                return '';
                        }
                    },
                ],
                [
                    'label' => 'Вид',
                    'attribute' => 'species',
                    'value' => 'species.name',
                ],
                [
                    'label' => 'Порода',
                    'attribute' => 'breeds',
                    'value' => 'breeds.name',
                ],
                [
                    'label' => 'Владелец',
                    'attribute' => 'fullname',
                    'format' => 'raw',
                    'value' => function ($pet) {

                        if (empty($pet->owner)) {
                            return '';
                        }
                        return Html::a(
                            Html::encode($pet->owner->fullname),
                            Url::to([
                                'audit/pet-owners/profile',
                                'id' => $pet->owner->id
                            ]),
                            [
                                'target' => '_blank',
                            ]);
                    }
                ],
                [
                    'label' => 'Идентификационный номер',
                    'attribute' => 'identification_code',
                    'value' => function ($pet) {
                        $allCodes = array_map(function ($pet_identification) {
                            return $pet_identification->identification_code;
                        }, $pet->pet_identification);
                        return implode('<br>', $allCodes);
                    },
                    'format' => 'raw',
                ],
                [
                    'class' => ActionColumn::class,
                    'headerOptions' => ['width' => '10%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center', 'style' => 'white-space: nowrap !important;'],
                    'template' => '{audit}',
                    'buttons' =>
                        [
                            'audit' => function ($url, $model, $key) {
                                /* @var $model \app\models\db\Organizations */
                                return
                                    //Yii::$app->user->can('data.organizations.manage.W', ['model' => $model])?
                                    Html::a(
                                        '<span class="glyphicon glyphicon-sunglasses"></span>',
                                        ['audit/audit/pets', 'id' => $model->id],
                                        ['class' => 'btn btn-xs btn-info', 'title' => 'Аудит']
                                    );
                                //: '';
                            },
                        ],
                ],
            ]
        ])
        ?>
    </div>
</div>
