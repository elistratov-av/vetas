<?php

use app\common\components\rbac\Role;
use app\models\db\admin\AdminUser;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this \yii\web\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $searchModel \app\modules\adminfstek\models\search\UsersSearch */
/* @var $auth \app\common\components\rbac\DbManager */

$this->blocks['content-header'] = 'Учетные записи';
?>
<div class="box">
    <div class="box-body">
        <?php
        if (\Yii::$app->user->can(AdminUser::ROLE_ADMIN) || \Yii::$app->user->can(AdminUser::ROLE_ACCOUNTS_MANAGER)) {
            echo Html::a(
                '<span class="glyphicon glyphicon-plus"></span> Добавить',
                ['create'],
                ['class' => 'btn btn-sm btn-success btn-flat']
            );
        }
        ?>
        <?php echo GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'formatter'        => [
                'class'       => 'yii\i18n\Formatter',
                'nullDisplay' => '-',
            ],
            'options' => ['class' => 'grid-view table-responsive'],
            'columns' => [
                [
                    'attribute' => 'id',
                    'headerOptions' => ['width' => '4%', 'class' => 'text-center'],
                ],
                [
                    'label' => 'Логин',
                    'attribute' => 'login',
                    'headerOptions'  => ['width' => '12%', 'class' => 'text-center'],
                ],
                [
                    'attribute' => 'email',
                    'label' => 'Email',
                    'headerOptions' => ['width' => '12%', 'class' => 'text-center'],
                ],
                [
                    'label' => 'Ф.И.О.',
                    'attribute' => 'fullname',
                    'headerOptions'  => ['width' => '18%', 'class' => 'text-center'],
                ],
                [
                    'label' => 'Организации',
                    'attribute' => 'short_name',
                    'headerOptions'  => ['width' => '25%', 'class' => 'text-center'],
                    'format' => 'raw',
                    'value' => function ($model, $key, $index, $widget) use ($auth) {
                        /* @var $model \app\common\models\UserModel */
                        $html = '<div class="specialists-list-wrapper">';
                        foreach ($model->specialists as $specialist) {
                            if ($specialist->organization !== null && !$specialist->isExpelledAtDate()) {
                                $roles = $auth->getRolesByUser($model->id, $specialist->id);
                                $names = [];
                                foreach ($roles as $role) {
                                    $names[] = Role::humanName($role->name, $auth);
                                }
                                $names = array_filter($names);
                                $html .= '<div class="specialist-info">';
                                $html .= '<a href="' . Url::to(['specialists/edit', 'id' => $specialist->id]) . '" title="Редактировать место работы специалиста">';
                                $html .= $specialist->organization->short_name;
                                $html .= '</a>';
                                $html .= '<p class="small">';
                                if (!empty($names)) {
                                    $html .= '(' . implode(', ', $names) . ')';
                                }
                                $html .= '</p>';
                                $html .= '</div>';
                            }
                        }
                        $html .= '</div>';

                        return $html;
                    },
                ],
                [
                    'label' => 'Последний вход',
                    'attribute' => 'last_login',
                    'headerOptions'  => ['width' => '8%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                    'filter' => \yii\jui\DatePicker::widget(
                        [
                            'model' => $searchModel,
                            'attribute' => 'last_login',
                            'language' => 'ru',
                            'dateFormat' => 'yyyy-MM-dd',
                        ]),
                ],
                [
                    'label' => 'Заблокирован постоянно',
                    'headerOptions'  => ['width' => '8%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                    'value' => function ($model) {
                        return ($model->is_blocked == 1) ? "Да" : "Нет";
                    },
                ],
                [
                    'label' => 'Временный блок',
                    'attribute' => 'temp_block',
                    'headerOptions'  => ['width' => '7%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                    'value' => function ($model) {
                        if (($model->block_until != null) && ($model->block_until > date("Y-m-d H:i:s"))) {
                            return "Да";
                        } else {
                            return "Нет";
                        }
                    },
                ],
                [
                    'label' => 'Заблокирован до',
                    'headerOptions'  => ['width' => '7%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                    'value' => function ($model) {
                        if (($model->block_until != null) && ($model->block_until > date("Y-m-d H:i:s"))) {
                            return $model->block_until;
                        } else {
                            return "";
                        }
                    },
                ],
                [
                    'class' => ActionColumn::class,
                    'headerOptions'  => ['width' => '10%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center', 'style' => 'white-space: nowrap !important;'],
                    'template' => '{edit-user} {add-spec} {block} {password-change} {delete}',
                    'buttons' => [
                        'edit-user' => function ($url, $model, $key) {
                            /* @var $model \app\common\models\UserModel */
                            return Html::a(
                                    '<span class="glyphicon glyphicon-pencil"></span>',
                                    ['edit', 'id' => $model->id],
                                    ['class' => 'btn btn-xs btn-warning', 'title' => 'Редактировать учетную запись']
                                );
                        },
                        'add-spec' => function ($url, $model, $key) {
                            /* @var $model \app\common\models\UserModel */
                            if ($model->is_deleted) {
                                return '';
                            }
                            if (\Yii::$app->user->can(AdminUser::ROLE_SECURITY)) {
                                return '';
                            }
                            return Html::a(
                                    '<span class="glyphicon glyphicon-home"></span>',
                                    ['specialists/create', 'id_user' => $model->id],
                                    ['class' => 'btn btn-xs btn-success', 'title' => 'Добавить место работы специалиста']
                                );
                        },
                        'block' => function ($url, $model, $key) {
                            /* @var $model \app\common\models\UserModel */
                            if ($model->is_deleted) {
                                return '';
                            }
                            if (\Yii::$app->user->can(AdminUser::ROLE_ADMIN)) {
                                return '';
                            }
                            return ($model->is_blocked !== true
                                && (empty($model->block_until)
                                    || (!empty($model->block_until) &&$model->block_until < date("Y-m-d H:i:s"))))
                                ? Html::a(
                                    '<span class="glyphicon glyphicon-ban-circle text-danger"></span>',
                                    ['block', 'id' => $model->id],
                                    ['class' => 'btn btn-xs btn-default', 'title' => 'Заблокировать пользователя']
                                )
                                : Html::a(
                                    '<span class="glyphicon glyphicon-check text-success"></span>',
                                    ['unblock', 'id' => $model->id],
                                    ['class' => 'btn btn-xs btn-default', 'title' => 'Разблокировать пользователя']
                                );
                        },
                        'password-change' => function ($url, $model, $key) {
                            /* @var $model \app\common\models\UserModel */
                            if ($model->is_deleted) {
                                return '';
                            }
                            if (\Yii::$app->user->can(AdminUser::ROLE_SECURITY)) {
                                return '';
                            }
                            return Html::a(
                                    '<span class="glyphicon glyphicon-lock"></span>',
                                    ['password-change', 'id' => $model->id],
                                    ['class' => 'btn btn-xs btn-primary', 'title' => 'Сбросить пароль']
                                );
                        },
                        'delete' => function ($url, $model, $key) {
                            /* @var $model \app\common\models\UserModel */
                            if (!\Yii::$app->user->can(AdminUser::ROLE_ACCOUNTS_MANAGER)) {
                                return '';
                            }
                            return $model->is_deleted
                                ? ''
                                : Html::a(
                                    '<span class="glyphicon glyphicon-trash"></span>',
                                    ['mark-deleted', 'id' => $model->id],
                                    [
                                        'class' => 'btn btn-xs btn-danger',
                                        'title' => 'Удалить пользователя',
                                        'data-method' => 'post',
                                        'data-confirm' => 'Вы уверены что хотите удалить пользователя? Все персональные данные пользователя будут удалены и дальнейшее редактирование пользователя и работа с привязанными специалистами будут невозможны!',
                                    ]
                                );
                        },
                    ],
                ],
            ],
        ]);
        ?>
    </div>
</div>

