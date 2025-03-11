<?php foreach(Yii::$app->session->getAllFlashes() as $type => $messages): ?>
    <?php foreach($messages as $message): ?>
        <div class="alert alert-<?= $type ?>" role="alert"><?= $message ?></div>
    <?php endforeach ?>
<?php endforeach ?>