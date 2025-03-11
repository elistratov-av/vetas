<?php

use app\common\components\rbac\Role;
use app\models\db\admin\AdminUser;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this \yii\web\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $searchModel \app\modules\adminfstek\models\search\LogUsersBlockAutoSearch */
/* @var $targetOptions array */
/* @var $typeOptions array */
/* @var $successOptions array */

$this->title = 'Автоматическая блокировка УЗ';

$this->blocks['content-header'] = $this->title;
?>
<div class="box">
    <div class="box-body">
        <?php echo GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'formatter' => [
                'class' => 'yii\i18n\Formatter',
                'nullDisplay' => '-',
            ],
            'options' => ['class' => 'grid-view table-responsive'],
            'columns' => [
                [
                    'attribute' => 'target',
                    'label' => 'Тип УЗ',
                    'headerOptions' => ['width' => '8%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                    'value' => function ($model) use ($targetOptions) {
                        /* @var $model \app\models\db\audit\LogUsersBlockAuto */
                        return ArrayHelper::getValue($targetOptions, $model->target, '-');
                    },
                    'filter' => $targetOptions,
                ],
                [
                    'attribute' => 'type',
                    'label' => 'Тип действия',
                    'headerOptions' => ['width' => '10%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                    'value' => function ($model) use ($typeOptions) {
                        /* @var $model \app\models\db\audit\LogUsersBlockAuto */
                        return ArrayHelper::getValue($typeOptions, $model->type, '-');
                    },
                    'filter' => $typeOptions,
                ],
                [
                    'attribute' => 'id_user',
                    'label' => 'ID пользователя',
                    'headerOptions' => ['width' => '5%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                ],
                [
                    'attribute' => 'login',
                    'label' => 'Логин',
                    'headerOptions' => ['width' => '12%', 'class' => 'text-center'],
                ],
                [
                    'label' => 'Дата и время',
                    'attribute' => 'created_at',
                    'headerOptions' => ['width' => '10%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                    'filter' => \yii\jui\DatePicker::widget(
                        [
                            'model' => $searchModel,
                            'attribute' => 'created_at',
                            'language' => 'ru',
                            'dateFormat' => 'yyyy-MM-dd',
                        ]),
                ],
                [
                    'label' => 'Заблокирован до',
                    'attribute' => 'block_until',
                    'headerOptions' => ['width' => '10%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                    'filter' => \yii\jui\DatePicker::widget(
                        [
                            'model' => $searchModel,
                            'attribute' => 'block_until',
                            'language' => 'ru',
                            'dateFormat' => 'yyyy-MM-dd',
                        ]),
                ],
                [
                    'attribute' => 'is_success',
                    'label' => 'Результат',
                    'headerOptions' => ['width' => '10%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                    'value' => function ($model) use ($successOptions) {
                        /* @var $model \app\models\db\audit\LogUsersBlockAuto */
                        return ArrayHelper::getValue($successOptions, (int)$model->is_success, '-');
                    },
                    'filter' => $successOptions,
                ],
            ],
        ]);
        ?>
    </div>
</div>

