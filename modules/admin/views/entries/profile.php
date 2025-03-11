<?php

use yii\helpers\Html;

$this->blocks['content-header'] = 'Профиль пользователя' . ' ' . Html::encode($admin->login);
?>
<div class="box">

    <div class="box-body">
        <table class="table table-bordered table-hover">
            <tr>
                <th>
                    Дата последнего входа
                </th>
                <td>
                    <?= Html::encode($admin->last_login) ?>
                </td>
            </tr>
            <tr>
                <th>
                    IP
                </th>
                <td>
                    <?= Html::encode($admin->ip) ?>
                </td>
            </tr>
        </table>
    </div>
</div>
<div class="col-xs-4">
    <?= Html::a('Сменить пароль', ['/admin/entries/password-change'], ['class' => 'btn btn-primary btn-flat']) ?>
</div>


