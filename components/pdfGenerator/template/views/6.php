<?php

use app\common\components\pdfGenerator\PdfGenerator;
use yii\helpers\Url;
?>

<div class="logo">
    <img src="<?= Url::to('@web/img/logo.png') ?>" width="71" height="74" />
    <div class="site">www.mos.ru/moskomvet</div>
</div>
<div class="head1">Государственная ветеринарная служба  города Москвы</div>
<div class="head2">Приложение 7 к приказу<br>№ 29 от «19» февраля 2014г.</div>
<div class="head3">штамп организации проводившей исследование</div>
<div class="title">Результат люминесцентной диагностики</div>
<div class="title2">Экспертиза № <?= $data['P2_SerialServiceNum'] ?? '' ?></div>


<div class="t6">
    <div class="tr2">
        <div class="t7" style="width: 383px;">Ветеринарное лечебное учреждение (подразделение):</div>
        <div class="t7"><span class="value"><?= $data['P13_Orgshortname'] ?? '&nbsp;' ?></span></div>
    </div>
    <div class="tr2">
        <div class="t7" style="width: 383px;">Ф.И.О. владельца</div>
        <div class="t7"><span class="value"><?= $data['P4_Ownername'] ?? '&nbsp;' ?></span></div>
    </div>
    <div class="tr2">
        <div class="t8" style="width: 383px;">Адрес, телефон</div>
        <div class="t8"><span class="value"><?= $data['P5_Owneraddres'] ?? '' ?>, <?= $data['P5_Ownercontact'] ?? '' ?></span></div>
    </div>
    <div><i>Сведения о животном:</i></div>

    <div class="t4">
        <div class="tr2">
            <div class="t3" style="width: 27px;">Вид:</div>
            <div class="t5"><span class="value"><?= $data['P6_Speciesname'] ?? '&nbsp;' ?></span></div>
        </div>
        <div class="tr2">
            <div class="t3" style="width: 47px;">Порода:</div>
            <div class="t5"><span class="value"><?= $data['P7_Breedname'] ?? '&nbsp;' ?></span></div>
        </div>
        <div class="tr2">
            <div class="t3" style="width: 52px;">Возраст:</div>
            <div class="t5"><span class="value"><?= PdfGenerator::petAge($data) ?></span></div>
        </div>
    </div>
    <div class="t4">
        <div class="tr2">
            <div class="t3" style="width: 27px;">Пол:</div>
            <div class="t5"><span class="value"><?= PdfGenerator::petSex($data) ?></span></div>
        </div>
        <div class="tr2">
            <div class="t3" style="width: 48px;">Кличка:</div>
            <div class="t5"><span class="value"><?= $data['P9_Petname'] ?? '&nbsp;' ?></span></div>
        </div>
    </div>

</div>

<br>

<div><b><i>Метод исследования:</i></b></div>
<div>Люминесцентная диагностика на микроспорию с применением лампы Вуда</div>

<br>
<div>
    <div><b><i>Результат исследования:</i></b></div>
    <?php if (isset($data['P12_Serviceresultvalue'])) {
        echo $data['P12_Serviceresultvalue'];
    }
    else {
    ?>
    <div class="hr">&nbsp;</div>
    <?php } ?>
</div>

<br>
<div>
    <div><b><i>Примечание:</i></b></div>
    <?php if (isset($data['P13_Serviceresultdesc'])) {
        echo $data['P13_Serviceresultdesc'];
    }
    else {
    ?>
    <div class="hr">&nbsp;</div>
    <div class="hr">&nbsp;</div>
    <div class="hr">&nbsp;</div>
    <?php } ?>
</div>

<br>
<br>
<div style="float:left;width: 300px;"><?= $data['P3_Visitstartdate'] ?? '«____» __________ 201___' ?> г.</div>
<div style="float:right;min-width: 300px;"><div class="t3" style="width: 120px;">Ветеринарный врач</div> <div class="t5"><?= $data['P33_SpecialistFIO'] ?? '&nbsp;' ?></div></div>
