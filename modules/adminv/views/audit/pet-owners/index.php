<?php

use app\models\db\PetOwners;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\jui\DatePicker;

/**
 * @var \app\modules\adminv\models\search\PetOwnersSearch $searchModel
 */

$this->blocks['content-header'] = 'Владельцы';
?>

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
                    'attribute' => 'fullname',
                    'value' => function (PetOwners $model) {
                        return Html::a(Html::encode($model->fullname), Url::to(['owners/profile', 'id' => $model->id]), [
                            'target' => '_blank',
                        ]);
                    },
                    'format' => 'raw',
                ],
                [
                    'label' => 'Дата рождения',
                    'attribute' => 'birthday',
                    'filter' => DatePicker::widget(
                        [
                            'model' => $searchModel,
                            'attribute' => 'birthday',
                            'language' => 'ru',
                            'dateFormat' => 'yyyy-MM-dd',
                            'clientOptions' => [
                                'yearRange' => date('Y') - 100 . ':' . date('Y'),
                                'changeYear' => true,
                                'changeMonth' => true,
                            ]
                        ]),
                    'value' => function ($owner) {
                        return $owner->birthday ?? '';
                    }
                ],
                [
                    'label' => 'Адрес / Факт адрес',
                    'attribute' => 'id_fias_address',
                    'format' => 'raw',
                    'value' => function ($owner) {

                        $fias_addresses = empty($owner->fias_addresses->full_address)
                            ? '' : '<b>Адрес: </b>' . htmlentities($owner->fias_addresses->full_address);
                        $fact_fias_addresses = empty($owner->fact_fias_addresses->full_address)
                            ? '' : '<b>Факт. адрес: </b>' . htmlentities($owner->fact_fias_addresses->full_address);

                        $glue = (!empty($fias_addresses) && !empty($fact_fias_addresses)) ? '<br>' : '';
                        return $fias_addresses . $glue . $fact_fias_addresses;
                    }
                ],
                [
                    'label' => 'Контакты',
                    'attribute' => 'contacts',
                    'format' => 'html',
                    'value' => function ($owner) {
                        $allContacts = array_map(function ($contacts) {
                            return $contacts->name;
                        }, $owner->contacts);
                        return implode('<br>', $allContacts);
                    }
                ],
                [
                    'class' => ActionColumn::className(),
                    'template' => '{audit}',
                    'buttons' => [
                        'audit' => function ($url, $model, $key) {
                            /* @var $model \app\models\db\Organizations */
                            return
                                //Yii::$app->user->can('data.organizations.manage.W', ['model' => $model])?
                                Html::a(
                                    '<span class="glyphicon glyphicon-sunglasses"></span>',
                                    ['audit/audit/pet-owners', 'id' => $model->id],
                                    ['class' => 'btn btn-xs btn-info', 'title' => 'Аудит']
                                );
                            //: '';
                        },
                    ],
                ],

            ]
        ]) ?>
    </div>
</div>
