<?php

use yii\grid\GridView;

/* @var $dataProvider \yii\data\ArrayDataProvider */
/* @var $this \yii\web\View */
/* @var $auth \app\common\components\rbac\DbManager */

$this->blocks['content-header'] = 'Разрешения';
?>
<div class="box">
    <div class="box-body">
        <?php echo GridView::widget([
            'dataProvider' => $dataProvider,
            //'filterModel' => $searchModel,
            'columns' => [
                //'id',
                [
                    'label' => 'Описание',
                    'attribute' => 'description',
                    'headerOptions' => ['width' => '50%', 'class' => 'text-center'],
                    'format' => 'raw',
                    'value' => function ($model, $key, $index, $widget) {
                        /* @var $model \yii\rbac\Permission */
                        return nl2br($model->description);
                    },
                ],
                [
                    'label' => 'Наименование',
                    'attribute' => 'name',
                    'headerOptions' => ['width' => '25%', 'class' => 'text-center'],
                ],
//                [
//                    'label' => 'Роли',
//                    'headerOptions'  => ['width' => '25%', 'class' => 'text-center'],
//                    'format' => 'raw',
//                    'value'          => function ($model, $key, $index, $widget) use ($auth) {
//                        /* @var $model \yii\rbac\Permission */
//                        // TODO - в штатном нет метода для получения ролей по разрешению
//                        return '';
//                    }
//                ],
            ],
        ]);
        ?>
    </div>
</div>
