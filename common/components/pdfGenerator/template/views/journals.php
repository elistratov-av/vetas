<?php

use app\common\components\pdfGenerator\PdfGenerator;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/* @var $data array */

$headers = $data['meta']['headers'];
$values = $data['data'][0];

$visitstartdate = ArrayHelper::getValue($values, 'P3_Visitstartdate');
$P3_Visitstartdate = empty($visitstartdate) ? null : PdfGenerator::dateFromFormat($visitstartdate, 'd.m.Y H:i:s');
?>
<header class="registry-paid-services__header">
    <div class="registry-paid-services__logo">
        <img class="registry-paid-services__logo-img" src="<?php echo Url::to('@web/img/ic-journal-reg-logo.png'); ?>"/>
        <div class="registry-paid-services__logo-text">
            Государственная ветеринарная служба города Москвы
        </div>
    </div>
</header>
<h1 class="registry-paid-services__title">
    <?php echo $data['title']; ?><br>от «<?php echo ($P3_Visitstartdate === null) ? '___' : $P3_Visitstartdate['d']; ?>» <?php echo ($P3_Visitstartdate === null) ? '__________' :  $P3_Visitstartdate['M']; ?> <?php echo ($P3_Visitstartdate === null) ? '____' :  $P3_Visitstartdate['Y']; ?> г
</h1>
<div class="registry-paid-services__container">
    <table class="registry-paid-services__table">
        <?php foreach ($headers as $header): ?>
        <?php if ($header['prop_path'] === null) {
            continue;
            } ?>
            <tr>
                <td class="registry-paid-services__key"><?php echo $header['label']; ?></td>
                <td class="registry-paid-services__value"><?php echo ArrayHelper::getValue($values, $header['prop_path'], ''); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <!--<table class="registry-paid-services__table">
        <caption class="registry-paid-services__caption">Данные приема:</caption>
    </table>-->
    <div class="registry-paid-services__footer">
        <p class="registry-paid-services__footer-title">
            Владелец ознакомлен и согласен с данными приема
        </p>
        <table class="registry-paid-services__footer-table">
            <tr>
                <td class="registry-paid-services__footer-left">
                    <div class="registry-paid-services__footer-sign">
                        <div class="registry-paid-services__footer-sign-owner">
                            Подпись владельца
                        </div>
                        <div class="registry-paid-services__footer-name">
                            <hr>

                            <?php echo ArrayHelper::getValue($values, 'P4_Ownername', '&nbsp;'); ?>
                        </div>
                    </div>
                </td>

                <td class="registry-paid-services__footer-right">
                    <div class="registry-paid-services__footer-sign">
                        <div class="registry-paid-services__footer-sign-owner">
                            Подпись вет. специалиста
                        </div>
                        <hr>
                        <div class="registry-paid-services__footer-name">
                            <?php echo ArrayHelper::getValue($values, 'P33_SpecialistFIO', '&nbsp;'); ?>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>
