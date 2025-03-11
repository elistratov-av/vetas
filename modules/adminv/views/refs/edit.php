<?php

/* @var $title string */
/* @var $crud_id int */
/* @var $model \app\models\db\ActiveRecord */

$this->blocks['content-header'] = $title;
?>
<?php echo $this->render('form', [
    'model' => $model,
    'crud_id' => $crud_id,
]); ?>
