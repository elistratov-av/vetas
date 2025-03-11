<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 18.12.18
 * Time: 16:37
 */

use app\modules\admin\models\Owners;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use app\modules\admin\models\MosruServicesAdmin;
use yii\helpers\Html;
use yii\helpers\Url;

$this->blocks['content-header'] = 'Владельцы';
?>

    <div class="box">
        <div class="box-body">
            <table class="table table-bordered table-hover">

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
        'id',
        [
            'label' => 'Имя',
            'attribute' => 'fullname',
            'value' => function (Owners $model) {
                return Html::a(Html::encode($model->fullname), Url::to(['owners/profile', 'id' => $model->id]), [
                    'target' => '_blank',
                ]);
            },
            'format' => 'raw',
        ],
        [
            'label' => 'Дата рождения',
            'attribute' => 'birthday',
            'filter' => \yii\jui\DatePicker::widget(
                [
                    'model' => $searchModel,
                    'attribute' => 'birthday',
                    'language' => 'ru',
                    'dateFormat' => 'yyyy-MM-dd',
                    'clientOptions' => [
                        'yearRange' =>  date('Y')-100 . ':' . date('Y'),
                        'changeYear' => true,
                        'changeMonth' => true,
                    ]
                ]),
            'value' => function($owner) {
                return $owner->birthday ?? '';
            }
        ],
        [
            'label' => 'Адрес',
            'attribute' => 'id_fias_address',
            'value' =>  function($owner) {
                return $owner->fiasAddress->full_address ?? '';
            }
        ],
        [
            'label' => 'Контакты',
            'attribute' => 'contacts',
            'format' => 'html',
            'value' => function($owner)
            {
                $allContacts = array_map(function($contacts)
                {
                    return $contacts->name;
                }, $owner->contacts);
                return implode('<br>', $allContacts);
            }
        ],
        [
            'class' => ActionColumn::className(),
            'template' => '{own-edit}',
            'buttons' => [
                'own-edit' => function ($url, $model, $key)
                {
                    return Html::a(
                        '<span class="glyphicon glyphicon-pencil"></span>',
                        Url::to(['owners/edit', 'id' => $model->id]),
                        ['class' => 'btn btn-xs btn-warning', 'title' => 'Редактировать']
                    );
                },
            ],
        ],

    ]
    ])?>
            </table>
        </div>
    </div>
