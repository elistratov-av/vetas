<?php

use yii\grid\GridView;

/* @var $dataProvider \yii\data\ArrayDataProvider */
/* @var $this \yii\web\View */
/* @var $auth \app\common\components\rbac\DbManager */

\app\common\assets\StickyTableHeadersAsset::register($this);

$this->blocks['content-header'] = 'Разрешения';

$this->registerJs('$("table").stickyTableHeaders();');

/*
 * Колонки
 */
$columns = [[
    'label' => 'Описание',
    'attribute' => 'description',
    'headerOptions' => [
        'width' => '50%',
        'class' => 'text-center',
        'style' => 'background-color: lightgray',
    ],
    'format' => 'raw',
    'value' => function ($model, $key, $index, $widget) {
        return nl2br($model['description']);
    },
]];
/** @var \app\common\components\rbac\Role[] $roles */
foreach ($roles as $role){
    $columns[] =[
        'label' => $role->name,
        'format' => 'raw',
        'value' => function (){
            return '';
        },
        'headerOptions' => [
            'style' => 'background-color: lightgray',
        ],
        'contentOptions' => function ($model, $key, $index, $column) use ($role) {
            return ['style' => 'background-color: '
            . (in_array($role->name, $model['roles'])
                ? 'green' : 'LightSalmon')];
        },

    ];
}


?>
<div class="box">
    <div class="box-body">
        <?php echo GridView::widget([
            'dataProvider' => $dataProvider,
            //'filterModel' => $searchModel,
            'columns' => $columns
        ]);
        ?>
    </div>
</div>
