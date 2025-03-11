<?php

use app\common\components\pdfGenerator\PdfGenerator;
use yii\helpers\Url;
?>

<div class="logo">
    <img src="<?= Url::to('@web/img/logo.png'); ?>" width="71" height="74" />
    <div class="site">www.mos.ru/moskomvet</div>
</div>
<div class="head1">Государственная ветеринарная служба  города Москвы</div>
<div class="head2">Приложение 20 к приказу<br>№ 29 от «19» февраля 2014г.</div>
<div class="head3">штамп организации проводившей исследование</div>

<div class="title title3">Электрокардиографическое исследование<br>
    № <?= PdfGenerator::getValue('P2_SerialServiceNum', $data) ?>
</div>

<div class="clear"></div>

<br>

<div class="t1">
    <div class="tr">
        <div class="t2"><i>Ветеринарное лечебное учреждение (подразделение):</i> <?= PdfGenerator::getValue('P13_Orgshortname', $data) ?></span></div>
    </div>
    <div class="tr">
        <div class="t2" style="width:380px;"><i>Ф. И. О. владельца:</i> <?= PdfGenerator::getValue('P4_Ownername', $data) ?></span></div>
    </div>
    <div class="tr">
        <div class="t2"><i>Адрес, телефон:</i> <?= PdfGenerator::getValue('P5_Owneraddres', $data) ?> <?= PdfGenerator::getValue('P5_Ownercontact', $data) ?></div>
    </div>
    <div class="tr">
        <div class="t2"><i>Сведения о животном:</i></div>
    </div>
    <div class="tr">
        <div class="t2" style="width:290px;"><i>Вид:</i> <?= PdfGenerator::getValue('P6_Speciesname', $data) ?></div>
        <div class="t2" style="width:200px;"><i>Пол:</i> <?= PdfGenerator::petAge($data) ?></div>
    </div>
    <div class="tr">
        <div class="t2" style="width:290px;"><i>Порода:</i> <?= PdfGenerator::getValue('P7_Breedname', $data) ?></div>
        <div class="t2" style="width:200px;"><i>Кличка:</i> <?= PdfGenerator::getValue('P9_Petname', $data) ?></div>
    </div>
    <div class="tr" style="border-bottom:0;">
        <div class="t2" style="width:92px;"><i>Возраст:</i> <?= PdfGenerator::petAge($data) ?></div>
    </div>

</div>


<br>

<div style="padding-left:30px;">
    <div class="tr21">
        <div class="t32" style="width: 25px;">з. P</div>
        <div class="t5" style="width: 55px;"><span class="value"><?= PdfGenerator::getValue('P12_Pc', $data) ?></span></div>
        <div class="t32" style="width: 25px;">с;</div>
        <div class="t5" style="width: 55px;"><span class="value"><?= PdfGenerator::getValue('P13_Pmv', $data) ?></span></div>
        <div class="t32" style="width: 45px;">мВ</div>
        <div class="t32" style="width: 35px;text-align:right;">Р (I)</div>
        <div class="t5" style="width: 45px;"><span class="value"><?= PdfGenerator::getValue('P14_Р1', $data) ?></span></div>
        <div class="t32" style="width: 35px;text-align:right;">P(II)</div>
        <div class="t5" style="width: 45px;"><span class="value"><?= PdfGenerator::getValue('P15_P2', $data) ?></span></div>
        <div class="t32" style="width: 35px;text-align:right;">P(III)</div>
        <div class="t5" style="width: 45px;"><span class="value"><?= PdfGenerator::getValue('P16_P3', $data) ?></span></div>
    </div>

    <div class="tr21">
        <div class="t32" style="width: 38px;">и. P-Q</div>
        <div class="t5" style="width: 95px;"><span class="value"><?= PdfGenerator::getValue('P17_Pq', $data) ?></span></div>
        <div class="t32" style="width: 45px;">с</div>
    </div>

    <div class="tr21">
        <div class="t32" style="width: 38px;">к. QRS</div>
        <div class="t5" style="width: 95px;"><span class="value"><?= PdfGenerator::getValue('P18_Qrs', $data) ?></span></div>
        <div class="t32" style="width: 25px;">с;</div>
        <div class="t5" style="width: 320px;"><span class="value"><?= PdfGenerator::getValue('P19_Qrsdesc', $data) ?></span></div>
    </div>

    <div class="tr21">
        <div class="t32" style="width: 30px;">з. R</div>
        <div class="t5" style="width: 95px;"><span class="value"><?= PdfGenerator::getValue('P20_Rmv', $data) ?></span></div>
        <div class="t32" style="width: 25px;">мВ</div>
        <div class="t5" style="width: 320px;"><span class="value"><?= PdfGenerator::getValue('P21_Rdesc', $data) ?></span></div>
    </div>

    <div class="tr21">
        <div class="t32" style="width: 30px;">з. T</div>
        <div class="t5" style="width: 95px;"><span class="value"><?= PdfGenerator::getValue('P22_Tmv', $data) ?></span></div>
        <div class="t32" style="width: 25px;">мВ</div>
        <div class="t5" style="width: 320px;"><span class="value"><?= PdfGenerator::getValue('P23_Tdesc', $data) ?></span></div>
    </div>

    <div class="tr21">
        <div class="t32" style="width: 35px;">с. S-T</div>
        <div class="t5" style="width: 95px;"><span class="value"><?= PdfGenerator::getValue('P24_St', $data) ?></span></div>
        <div class="t32" style="width: 25px;">с</div>
        <div class="t32" style="width: 320px;">
            Депрессия<br>
            Подъём
        </div>
    </div>

    <div class="tr21">
        <div class="t32" style="width: 35px;">и. Q-T</div>
        <div class="t5" style="width: 95px;"><span class="value"><?= PdfGenerator::getValue('P25_Qt', $data) ?></span></div>
        <div class="t32" style="width: 25px;">c</div>
    </div>

    <div class="tr21">
        <div class="t32" style="width: 30px;">ЭОС</div>
        <div class="t5" style="width: 135px;"><span class="value"><?= PdfGenerator::getValue('P26_Eos', $data) ?></span></div>
    </div>

    <div class="tr21">
        <div class="t32" style="width: 30px;">ЧСС</div>
        <div class="t5" style="width: 95px;"><span class="value"><?= PdfGenerator::getValue('P27_Chss', $data) ?></span></div>
        <div class="t32" style="width: 45px;">уд/мин</div>
    </div>

    <div class="tr21">
        <div class="t32" style="width: 35px;">Ритм</div>
        <div class="t5" style="width: 595px;"><span class="value"><?= PdfGenerator::getValue('P28_Ritm', $data) ?></span></div>
    </div>

    <div class="tr21">
        <div class="t32" style="width: 95px;">Экстрасистолы:</div>
        <div class="t5" style="width: 515px;"><span class="value"><?= PdfGenerator::getValue('P29_Ekstrasistoly', $data) ?></span></div>
    </div>


    <br>
    <div>
        <div style="padding-bottom:10px;">Заключение</div>
        <?php if (isset($data['P30_Serviceresult'])) {
            echo $data['P30_Serviceresult'];
        }
        else {
        ?>
        <div class="hr">&nbsp;</div>
        <div class="hr">&nbsp;</div>
        <div class="hr">&nbsp;</div>
        <div class="hr">&nbsp;</div>
        <div class="hr">&nbsp;</div>
        <?php } ?>
    </div>

    <br>
    <div style="float:left;width: 300px;">
    <?php
        if (isset($data['P3_Visitstartdate'])) {
            $P3_Visitstartdate = PdfGenerator::dateFromFormat($data['P3_Visitstartdate'], 'd.m.Y');
    ?>
    «<?= $P3_Visitstartdate['d'] ?>» <?= $P3_Visitstartdate['M'] ?> <?= $P3_Visitstartdate['Y'] ?> г.
    <?php } else { ?>
    «&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;»____________________201__г.
    <?php } ?>
    </div>
    <div style="float:right;min-width: 300px;"><div class="t3" style="width: 120px;">Ветеринарный врач</div> <div class="t5"><?=PdfGenerator::getValue('P33_SpecialistFIO', $data)?></div></div>
</div>

