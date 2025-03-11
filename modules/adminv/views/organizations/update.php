<?php

/* @var $this \yii\web\View */
/* @var $model \app\models\db\Organizations */
/* @var $organizationsOptions array */
/* @var $districts array */

$this->blocks['content-header'] = "Редактирование организации: {$model->name}";
?>
<div class="user-profile-password-change">
    <?php echo $this->render('form', compact('model', 'organizationsOptions', 'districts')); ?>
</div>
