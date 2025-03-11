<?php

use yii\helpers\Html;

$this->title = 'Ошибка!';
?>
<section class="content">

    <div class="error-page">
        <h2 class="headline text-info"><i class="fa fa-warning text-yellow"></i></h2>

        <div class="error-content">
            <h3><?= $this->title ?></h3>

            <p>
                <?= nl2br(Html::encode($exception->getMessage())) ?>
            </p>

            <p>
                <a href='<?= Yii::$app->homeUrl ?>'>Вернуться на главную</a>
            </p>
        </div>
    </div>

</section>