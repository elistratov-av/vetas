<?php

use app\common\components\rbac\Role;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this \yii\web\View */
/* @var $dataProvider \app\modules\admin\data\AdminDataProvider */
/* @var $searchModel \app\modules\adminv\models\search\UserSearch */
/* @var $auth \app\common\components\rbac\DbManager */

$this->blocks['content-header'] = 'Учетные записи';
?>
<div class="box">
    <div class="box-body">
        <?php
        if (Yii::$app->user->can('admin.users.manage.W')) {
            echo Html::a(
                '<span class="glyphicon glyphicon-plus"></span> Добавить',
                ['/adminv/users/create'],
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
                                    $names[] = Role::humanName($role->name);
                                }
                                $names = array_filter($names);
                                $html .= '<div class="specialist-info">';
                                $html .= '<a href="' . Url::to(['/adminv/specialists/edit', 'id' => $specialist->id]) . '" title="Редактировать место работы специалиста">';
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
                    'template' => '{edit-user} {add-spec} {password-change}',
                    'buttons' => [
                        'edit-user' => function ($url, $model, $key) {
                            return (Yii::$app->user->can('admin.users.manage.W') || true)
                                ? Html::a(
                                    '<span class="glyphicon glyphicon-pencil"></span>',
                                    ['/adminv/users/edit', 'id' => $model->id],
                                    ['class' => 'btn btn-xs btn-warning', 'title' => 'Редактировать учетную запись']
                                )
                                : '';
                        },
                        'add-spec' => function ($url, $model, $key) {
                            return (Yii::$app->user->can('admin.users.manage.W') || true)
                                ? Html::a(
                                    '<span class="glyphicon glyphicon-home"></span>',
                                    ['/adminv/specialists/create', 'id_user' => $model->id],
                                    ['class' => 'btn btn-xs btn-success', 'title' => 'Добавить место работы специалиста']
                                )
                                : '';
                        },
                        'password-change' => function ($url, $model, $key) {
                            return (Yii::$app->user->can('admin.users.change-password') || true)
                                ? Html::a(
                                    '<span class="glyphicon glyphicon-lock"></span>',
                                    ['/adminv/users/password-change', 'id' => $model->id],
                                    ['class' => 'btn btn-xs btn-primary', 'title' => 'Изменить пароль']
                                )
                                : '';
                        },
                    ],
                ],
            ],
        ]);
        ?>
    </div>
</div>

