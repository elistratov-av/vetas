<?php

/* @var $this \yii\web\View */
/* @var $model \app\modules\adminv\models\forms\SpecialistForm */
/* @var $organizationsOptions array */
/* @var $districts array */

$this->blocks['content-header'] = 'Добавить новую организацию';
?>
<div class="user-profile-password-change">
    <?php echo $this->render('form', compact('model', 'organizationsOptions', 'districts')); ?>
</div>
