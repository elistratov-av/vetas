<?php
use yii\helpers\Html;
use app\modules\subscription\assets\SubscriptionAsset;

/* @var $this \yii\web\View */
/* @var $content string */

$this->registerAssetBundle(SubscriptionAsset::class);
$assetsPath = $this->assetManager->getBundle(SubscriptionAsset::class)->baseUrl;
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title>Управление подписками</title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="container">
    <div class="content">
        <div class="header">
            <img class="header--img" src="<?="{$assetsPath}/img/ic-logo-32.png"?>" alt="">
            <div class="header--title">Комитет ветеринарии</div>
        </div>
        <?= $content?>
    </div>
</div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
