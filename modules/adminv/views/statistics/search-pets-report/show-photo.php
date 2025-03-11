<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 28.01.19
 * Time: 16:01
 */


use yii\helpers\Html;
use yii\helpers\Url;

$this->blocks['content-header'] = 'Фото питомца из объявления ' . Html::encode($model->id);
?>

<div class="box">
    <div class="box-body">
        <img style="width: 1000px" src="<?= $photo ?>" alt="Если вы видите эту надпись, значит картинка куда-то делась или была удалена :(" />
    </div>
</div>
