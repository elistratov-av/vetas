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
/* @var $searchModel \app\modules\adminfstek\models\search\AdminUsersSearch */
/* @var $roleOptions array */
/* @var $statusOptions array */

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
            'formatter' => [
                'class' => 'yii\i18n\Formatter',
                'nullDisplay' => '-',
            ],
            'options' => ['class' => 'grid-view table-responsive'],
            'columns' => [
                [
                    'attribute' => 'id',
                    'headerOptions' => ['width' => '4%', 'class' => 'text-center'],
                ],
                [
                    'attribute' => 'login',
                    'label' => 'Логин',
                    'headerOptions' => ['width' => '12%', 'class' => 'text-center'],
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
                    'attribute' => 'role',
                    'label' => 'Роль',
                    'headerOptions' => ['width' => '10%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                    'value' => function ($model) use ($roleOptions) {
                        /* @var $model \app\models\db\admin\AdminUser */
                        return ArrayHelper::getValue($roleOptions, $model->role, '-');
                    },
                    'filter' => $roleOptions,
                ],
                [
                    'attribute' => 'is_blocked',
                    'label' => 'Заблокирован постоянно',
                    'headerOptions' => ['width' => '8%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                    'value' => function ($model) use ($statusOptions) {
                        /* @var $model \app\models\db\admin\AdminUser */
                        return ArrayHelper::getValue($statusOptions, $model->is_blocked, '-');
                    },
                    'filter' => $statusOptions,
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
                    'filter' => $statusOptions,
                ],
                [
                    'label' => 'Последний вход',
                    'attribute' => 'last_login',
                    'headerOptions' => ['width' => '8%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                    'filter' => \yii\jui\DatePicker::widget(
                        [
                            'model' => $searchModel,
                            'attribute' => 'last_login',
                            'language' => 'ru',
                            'dateFormat' => 'yyyy-MM-dd',
                        ]),
                ],
                // [
                //     'label' => 'Создан',
                //     'attribute' => 'created_at',
                //     'headerOptions' => ['width' => '8%', 'class' => 'text-center'],
                //     'contentOptions' => ['class' => 'text-center'],
                // ],
                // [
                //     'label' => 'Обновлен',
                //     'attribute' => 'updated_at',
                //     'headerOptions' => ['width' => '8%', 'class' => 'text-center'],
                //     'contentOptions' => ['class' => 'text-center'],
                // ],
                [
                    'class' => ActionColumn::class,
                    'headerOptions' => ['width' => '10%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center', 'style' => 'white-space: nowrap !important;'],
                    'template' => '{edit} {block} {password-change} {delete}',
                    'buttons' => [
                        'edit' => function ($url, $model, $key) {
                            /* @var $model \app\models\db\admin\AdminUser */
                            return Html::a(
                                '<span class="glyphicon glyphicon-pencil"></span>',
                                ['edit', 'id' => $model->id],
                                ['class' => 'btn btn-xs btn-warning', 'title' => 'Редактировать учетную запись']
                            );
                        },
                        'block' => function ($url, $model, $key) {
                            /* @var $model \app\models\db\admin\AdminUser */
                            if ($model->is_deleted) {
                                return '';
                            }
                            if (\Yii::$app->user->can(AdminUser::ROLE_ADMIN)) {
                                return '';
                            }
                            if ($model->id == Yii::$app->user->id) {
                                return '';
                            }
                            return ($model->is_blocked == AdminUser::STATUS_NOT_BLOCKED)
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
                            /* @var $model \app\models\db\admin\AdminUser */
                            if ($model->is_deleted) {
                                return '';
                            }
                            if (\Yii::$app->user->can(AdminUser::ROLE_SECURITY)) {
                                return '';
                            }
                            if (Yii::$app->user->id == $model->id) {
                                $link = ['profile/password-change'];
                                $title = 'Изменить пароль';
                            } else {
                                $link = ['password-change', 'id' => $model->id];
                                $title = 'Сбросить пароль';
                            }
                            return Html::a(
                                '<span class="glyphicon glyphicon-lock"></span>',
                                $link,
                                ['class' => 'btn btn-xs btn-primary', 'title' => $title]
                            );
                        },
                        'delete' => function ($url, $model, $key) {
                            /* @var $model \app\models\db\admin\AdminUser */
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
                                        'data-confirm' => 'Вы уверены что хотите удалить пользователя? Все персональные данные пользователя будут удалены и дальнейшее редактирование пользователя будет невозможны!',
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

