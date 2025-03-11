<?php

use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this \yii\web\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $searchModel \app\modules\adminfstek\models\search\SessionSearch */

$targetOptions = $searchModel::targetOptions();
?>
<?php echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'formatter' => [
        'class' => 'yii\i18n\Formatter',
        'nullDisplay' => '-',
    ],
    'options' => ['class' => 'grid-view table-responsive ajax-list-wrapper'],
    'layout' => '{summary}{items}<div class="col-lg-12 text-center">{pager}<a id="currentUrl" style="display: none;" href="' . Url::current() . '"></a></div>',
    'headerRowOptions' => ['class' => 'sortable headings'],
    'columns' => [
        [
            'attribute' => 'id_user',
            'headerOptions' => ['width' => '4%', 'class' => 'text-center'],
        ],
        [
            'attribute' => 'target',
            'label' => 'Цель',
            'headerOptions' => ['width' => '8%', 'class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center'],
            'value' => function ($model) use ($targetOptions) {
                return ArrayHelper::getValue($targetOptions, $model['target'], '-');
            },
            'filter' => $targetOptions,
        ],
        [
            'label' => 'Логин',
            'attribute' => 'login',
            'headerOptions' => ['width' => '12%', 'class' => 'text-center'],
        ],
        [
            'label' => 'Ф.И.О.',
            'attribute' => 'fullname',
            'headerOptions' => ['width' => '18%', 'class' => 'text-center'],
        ],
        [
            'label' => 'Последняя активность',
            'attribute' => 'last_active_at',
            'headerOptions' => ['width' => '8%', 'class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center'],
            'filter' => \yii\jui\DatePicker::widget(
                [
                    'model' => $searchModel,
                    'attribute' => 'last_active_at',
                    'language' => 'ru',
                    'dateFormat' => 'yyyy-MM-dd',
                ]),
        ],
        [
            'label' => 'Срок действия до',
            'attribute' => 'valid_until',
            'headerOptions' => ['width' => '8%', 'class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center'],
            'filter' => \yii\jui\DatePicker::widget(
                [
                    'model' => $searchModel,
                    'attribute' => 'valid_until',
                    'language' => 'ru',
                    'dateFormat' => 'yyyy-MM-dd',
                ]),
        ],
        [
            'label' => 'IP',
            'attribute' => 'ip',
            'headerOptions' => ['width' => '8%', 'class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center'],
        ],
        [
            'label' => 'User Agent',
            'attribute' => 'ua',
            'headerOptions' => ['width' => '15%', 'class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center small'],
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'headerOptions' => ['width' => '5%', 'class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center', 'style' => 'white-space: nowrap !important;'],
            'template' => '{delete}',
            'buttons' => [
                'delete' => function ($url, $model) use ($searchModel) {
                    if ($model['target'] == $searchModel::TARGET_ADMIN && $model['id_user'] == \Yii::$app->user->id) {
                        $url = ['site/logout'];
                    } else {
                        $url = ['delete', 'id' => $model['id'], 'target' => $model['target']];
                    }
                    return Html::a('<i class="fa fa-fw fa-trash"></i>', $url, [
                        'title' => 'Прервать сессию',
                        'data-confirm' => 'Вы уверены что хотите прервать сессию пользователя?',
                        'data-method' => 'post',
                        'data-pjax' => '0',
                        'class' => 'btn btn-xs btn-danger tt',
                    ]);
                },
            ],
        ],
    ],
]); ?>
