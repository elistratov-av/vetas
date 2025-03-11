<?php

use app\common\components\rbac\Role;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this \yii\web\View */
/* @var $dataProvider \app\modules\admin\data\AdminDataProvider */
/* @var $searchModel \app\modules\admin\models\search\UserSearch */
/* @var $auth \app\common\components\rbac\DbManager */

$css = <<<CSS
input.hasDatepicker {
    max-width: 100px !important;
    display: block;
    width: 100%;
    height: 34px;
    padding: 6px 2px;
    font-size: 14px;
    line-height: 1.42857143;
}
CSS;

$this->registerCss($css);

$this->blocks['content-header'] = 'Специалисты';
?>

<div class="box">
    <div class="box-body">
        <?php /*echo  Html::a(
            '<span class="glyphicon glyphicon-plus"></span> Добавить',
            Url::to(['specialist/add']),
            ['class' => 'btn btn-sm btn-success btn-flat']
        ) */?>

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' =>
                [
                    [
                        'attribute' => 'id',
                        'headerOptions' => ['width' => '4%', 'class' => 'text-center'],
                    ],
                    [
                        'label' => 'Ф.И.О.',
                        'headerOptions'  => ['width' => '18%', 'class' => 'text-center'],
                        'value' => function ($model) {
                            return Html::a(Html::encode($model->fullname),
                                Url::to(['specialists/profile', 'id' => $model->id]));
                        },
                        'format' => 'raw',
                        'attribute' => 'fullname',
                    ],
                    [
                        'label' => 'Дата регистрации',
                        'attribute' => 'reg_date',
                        'headerOptions'  => ['width' => '10%', 'class' => 'text-center'],
                        'filter' => \yii\jui\DatePicker::widget(
                            [
                                'model' => $searchModel,
                                'attribute' => 'reg_date',
                                'language' => 'ru',
                                'dateFormat' => 'yyyy-MM-dd',
                            ]),
                    ],
                    [
                        'label' => 'Дата рождения',
                        'attribute' => 'birthday',
                        'headerOptions'  => ['width' => '10%', 'class' => 'text-center'],
                        'filter' => \yii\jui\DatePicker::widget(
                            [
                                'model' => $searchModel,
                                'attribute' => 'birthday',
                                'language' => 'ru',
                                'dateFormat' => 'yyyy-MM-dd',
                            ]),
                    ],
                    [
                        'label' => 'Дата увольнения',
                        'attribute' => 'expel_date',
                        'headerOptions'  => ['width' => '10%', 'class' => 'text-center'],
                        'filter' => \yii\jui\DatePicker::widget(
                            [
                                'model' => $searchModel,
                                'attribute' => 'expel_date',
                                'language' => 'ru',
                                'dateFormat' => 'yyyy-MM-dd',
                            ]),
                    ],
                    [
                        'label' => 'Пол',
                        'attribute' => 'sex',
                        'headerOptions'  => ['width' => '4%', 'class' => 'text-center'],
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
                        'label' => 'Организации',
                        'attribute' => 'short_name',
                        'headerOptions' => ['width' => '25%', 'class' => 'text-center'],
                        'format' => 'raw',
                        'value' => function ($model, $key, $index, $widget) use ($auth) {
                            /* @var $model \app\models\db\Specialists */
                            $html = '<div class="specialists-list-wrapper">';
                            if ($model->organization !== null && $model->user !== null) {
                                $roles = $auth->getRolesByUser($model->user->id, $model->id);
                                $names = [];
                                foreach ($roles as $role) {
                                    $names[] = Role::humanName($role->name);
                                }
                                $names = array_filter($names);
                                $html .= '<div class="specialist-info">';
                                $html .= $model->organization->short_name;
                                $html .= '<p class="small text-muted">';
                                if (!empty($names)) {
                                    $html .= '(' . implode(', ', $names) . ')';
                                }
                                $html .= '</p>';
                                $html .= '</div>';
                            }
                            $html .= '</div>';

                            return $html;
                        },
                    ],
                    [
                        'label' => 'Учетная запись',
                        'attribute' => 'login',
                        'headerOptions'  => ['width' => '15%', 'class' => 'text-center'],
                        'value' => function ($specialists) {
                            if (empty($specialists->id_user)) {
                                return "";
                            } else {
                                return $specialists->user->login;
                            }
                        },
                    ],
                    [
                        'class' => ActionColumn::class,
                        'headerOptions'  => ['width' => '10%', 'class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center', 'style' => 'white-space: nowrap !important;'],
                        'template' => '{edit} {remove}',
                        'buttons' => [
                            'edit' => function ($url, $model, $key) {
                                return Html::a(
                                    '<span class="glyphicon glyphicon-pencil"></span>',
                                    Url::to(['specialists/edit', 'id' => $model->id]),
                                    ['class' => 'btn btn-xs btn-warning', 'title' => 'Редактировать']
                                );
                            },
                            'remove' => function ($url, $model, $key) {
                                return Html::a(
                                    '<span class="glyphicon glyphicon-trash"></span>',
                                    Url::to(['specialists/remove', 'id' => $model->id]),
                                    [
                                        'class' => 'btn btn-xs btn-danger',
                                        'title' => 'Удалить',
                                        'data-confirm' => 'Вы действительно хотите удалить специалиста? Действие нельзя отменить.',
                                    ]
                                );
                            },
                        ],
                    ],
                ],
        ]) ?>
    </div>
</div>
