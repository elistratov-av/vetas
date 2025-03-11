<?php

use yii\grid\GridView;
use yii\grid\ActionColumn;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\VarDumper;

/* @var $this \yii\web\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $searchModel \app\modules\adminfstek\models\search\LogUsersAccessChangeSearch */
/* @var $targetOptions array */
/* @var $typeOptions array */
/* @var $successOptions array */

$this->title = 'Управление доступами УЗ';

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
                        /* @var $model \app\models\db\audit\LogUsersAccessChange */
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
                        /* @var $model \app\models\db\audit\LogUsersAccessChange */
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
                    'headerOptions' => ['width' => '10%', 'class' => 'text-center'],
                ],
                [
                    'attribute' => 'created_by',
                    'label' => 'ID автора запроса',
                    'headerOptions' => ['width' => '10%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                ],
                [
                    'attribute' => 'creator_login',
                    'label' => 'Автор запроса',
                    'headerOptions' => ['width' => '10%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                    'value' => function ($model) use ($typeOptions) {
                        /* @var $model \app\models\db\audit\LogUsersAccessChange */
                        return empty($model->creator) ? '-' : $model->creator->login;
                    },
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
                    'attribute' => 'is_success',
                    'label' => 'Результат',
                    'headerOptions' => ['width' => '7%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                    'value' => function ($model) use ($successOptions) {
                        /* @var $model \app\models\db\audit\LogUsersAccessChange */
                        return ArrayHelper::getValue($successOptions, (int)$model->is_success, '-');
                    },
                    'filter' => $successOptions,
                ],
                [
                    'class' => ActionColumn::class,
                    'headerOptions' => ['width' => '5%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center', 'style' => 'white-space: nowrap !important;'],
                    'template' => '{view}',
                    'buttons' => [
                        'view' => function ($url, $model, $key) {
                            /* @var $model \app\models\db\audit\LogUsersAccessChange */
                            $html = '<div class="changes-diff" style="display: none">';
                            $html .= '<div class="col-md-6">';
                            $html .= '<h5>До изменений</h5><pre>';
                            $html .= (empty($model->before) ? '-' : VarDumper::dumpAsString($model->before));
                            $html .= '</pre></div>';
                            $html .= '<div class="col-md-6">';
                            $html .= '<h5>После изменений</h5><pre>';
                            $html .= (empty($model->after) ? '-' : VarDumper::dumpAsString($model->after));
                            $html .= '</pre></div>';
                            $html .= '</div>';
                            $html .= Html::a(
                                '<span class="fa fa-fw fa-search-plus"></span>',
                                '#',
                                ['class' => 'btn btn-xs btn-info show-diff', 'title' => 'Подробнее']
                            );
                            return $html;
                        },
                    ],
                ],
            ],
        ]);
        ?>
    </div>
</div>
<?php echo $this->render('_modal');
