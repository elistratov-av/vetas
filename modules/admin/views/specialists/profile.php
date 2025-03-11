<?php

use app\common\components\rbac\Role;
use \yii\helpers\Html;

/** @var \app\models\db\Specialists $model */
/* @var $auth \app\common\components\rbac\DbManager */

$this->blocks['content-header'] = 'Профиль специалиста ' . Html::encode($model->fullname) ?>

<div class="box">

    <div class="box-body">
        <table class="table table-bordered table-hover">
            <tr>
                <th>
                    Имя
                </th>
                <td>
                    <?= Html::encode($model->fullname) ?>
                </td>
            </tr>
            <tr>
                <th>
                    Организация
                </th>
                <td>
                    <?= Html::encode($model->organization->name) ?>
                </td>
            </tr>
            <tr>
                <th>
                    Дата рождения
                </th>
                <td>
                    <?= Html::encode($model->birthday) ?>
                </td>
            </tr>
            <tr>
                <th>
                    Дата регистрации
                </th>
                <td>
                    <?= Html::encode($model->reg_date) ?>
                </td>
            </tr>
            <tr>
                <th>
                    Дата увольнения
                </th>
                <td>
                    <?= Html::encode($model->expel_date) ?>
                </td>
            </tr>
            <?php if ($model->user) { ?>
                <tr>
                    <th>
                        Дата последнего входа
                    </th>
                    <td>
                        <?= Html::encode($model->user->last_login) ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        Учетная запись
                    </th>
                    <td>
                        <?= Html::encode($model->user->login) ?>
                    </td>
                </tr>
            <?php } ?>
            <tr>
                <th>
                    Роли
                </th>
                <td>
                    <?php
                    if ($model->user !== null) {
                        $roles = $auth->getRolesByUser($model->user->id, $model->id);
                        $names = [];
                        foreach ($roles as $role) {
                            $names[] = Role::humanName($role->name);
                        }
                        $names = array_filter($names);
                        echo implode('<br>', $names);
                    }
                    ?>
                </td>
            </tr>
        </table>
    </div>
</div>
