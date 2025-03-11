<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 19.12.18
 * Time: 17:08
 */

use yii\grid\GridView;
use \app\modules\admin\models\Visits;
use  \app\modules\admin\helpers\VisitStatusHelper;
use \roboapp\multiselect\MultiSelect;
use yii\helpers\Html;
use yii\helpers\Url;

$this->blocks['content-header'] = 'Питомцы'?>

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
                        'yearRange' =>  date('Y')-75 . ':' . date('Y'),
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
            'attribute' => 'pet_owner',
            'value' => function ($pet) {
                foreach($pet->owners as $petOwner) {
                    if ($petOwner->petsToOwners[0]->id_owner_type == 1) {
                        return Html::a(Html::encode($petOwner->fullname), Url::to(['owners/profile', 'id' => $petOwner->petsToOwners[0]->id_owner]), [
                            'target' => '_blank',
                        ]);
                    }
                }
            },
            'format' => 'raw',
        ],
        [
            'label' => 'Идентификационный номер',
            'attribute' => 'id_code',
            'value' => function($pet) {
                $allCodes = array_map(function($pet_identification)
                {
                    return $pet_identification->identification_code;
                }, $pet->pet_identification);
                return implode('<br>', $allCodes);
            },
            'format' => 'raw',
        ],
    ]
    ])
    ?>
            </table>
        </div>
    </div>
