<?php
$this->blocks['content-header'] = 'Редактирование услуги ';
?>

<div class="user-profile-password-change">
    <?=$this->render('form', [
        'model' => $model,
        'service_type' => $service_type,
        'service_goal' => $service_goal
    ]) ?>
</div>
