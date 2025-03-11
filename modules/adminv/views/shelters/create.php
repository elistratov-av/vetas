<?php

/* @var $this \yii\web\View */
/* @var $model \app\models\db\Organizations */
/* @var $contactForm \app\modules\adminv\models\forms\ShelterContactsForm */
/* @var $addressForm \app\modules\adminv\models\forms\OrganizationAddressForm */
/* @var $representativeForm \app\modules\adminv\models\forms\ShelterRepresentativeForm */
/* @var $organizationsOptions array */

$this->blocks['content-header'] = 'Добавить приют';
?>
<div class="row">
    <?php echo $this->render('form', compact('model', 'contactForm', 'addressForm', 'representativeForm', 'organizationsOptions')); ?>
</div>
