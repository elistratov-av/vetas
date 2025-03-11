<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 27.02.19
 * Time: 12:59
 */
$this->blocks['content-header'] = 'Редактирование услуги ';
?>

<div class="user-profile-password-change">
    <?=$this->render('form', [
        'model' => $model,
        'service_type' => $service_type,
        'service_goal' => $service_goal
    ]) ?>
</div>