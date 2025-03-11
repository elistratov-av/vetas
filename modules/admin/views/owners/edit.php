<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 20.12.18
 * Time: 13:22
 */

$this->blocks['content-header'] = "Редактирование владельца: {$owner->fullname}";
?>

<div class="user-profile-password-change">
    <?=$this->render('form', ['model' => $model]) ?>
</div>