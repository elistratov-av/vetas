<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 30.11.18
 * Time: 17:48
 */

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;

$this->blocks['content-header'] = 'Организации';
?>

<div class="box">
    <div class="box-body">
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
        'id',
        [
            'label' => 'Название',
            'attribute' => 'short_name',

        ],
        [
            'label' => 'Родительская организация',
            'attribute' => 'parent_id',
            'value' => function($organization)
            {
                return $organization->parentOrg['short_name'];
            }
        ],
        [
            'label' => 'Контакты',
            'attribute' => 'contacts',
            'format' => 'html',
            'value' => function($organization)
            {
                $allContacts = array_map(function($contacts)
                {
                    return $contacts->name;
                }, $organization->contacts);
                return implode('<br>', $allContacts);
            }
        ],
        [
            'label' => 'Адрес',
            'attribute' => 'addresses',
            'format' => 'raw',
            'value' => function($organization)
            {
                $addr = $organization->addresses;
                if($addr && $addr->name && $addr->latitude && $addr->longitude) {
                    return Html::a($addr->name . '<br>' . $addr->latitude . '<br>' . $addr->longitude,
                        Url::to('http://maps.yandex.ru/?text=' . $addr->latitude . ',' . $addr->longitude),[
                                'target' => '_blank',
                        ]);
                } else {
                    return "";
                }
            }
        ],
        [
            'class' => ActionColumn::className(),
            'template' => '{edit-org}',
            'buttons' =>
                [
                    'edit-org' => function ($url, $model, $key)
                    {
                        return Html::a(
                            '<span class="glyphicon glyphicon-pencil"></span>',
                            Url::to(['organization/edit', 'id' => $model->id]),
                            ['class' => 'btn btn-xs btn-warning', 'title' => 'Редактировать']
                        );
                    },
                ]
        ],
    ]
    ]);
?>
    </div>
</div>
