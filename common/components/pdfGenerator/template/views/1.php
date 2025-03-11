<?php

use app\common\components\pdfGenerator\PdfGenerator;
use yii\helpers\Url;

?>
<style>
    @media all {
        .page-break {
            display: none;
        }
    }

    @media print {
        .page-break {
            display: block;
            page-break-before: always;
        }
    }
    .table1 {
        border-collapse: collapse;
        border: 2px solid black;
        width: 100%;
    }
    .table__row1 {
        border-bottom: 2px solid black;
    }

    .table-key1 {
        border-right: 2px solid black;
    }
    .table-value-description1 {
        border-right: 2px solid black;
    }
</style>
<div class="logo">
    <img src="<?=Url::to('@web/img/logo.png')?>" width="71" height="74"/>
    <div class="site">www.mos.ru/moskomvet</div>
</div>
<div class="head1">Государственная ветеринарная служба города Москвы</div>
<div class="head2">Приложение 2 к приказу<br>№ 29 от «19» февраля 2014г.</div>
<div class="head3">штамп организации проводившей вакцинацию</div>

<div class="clear"></div>
<br><br>


<div class="tr2">
    <div class="t7" style="width: 353px;">Ветеринарное лечебное учреждение (подразделение):</div>
    <div class="t7"><span class="value"><?=PdfGenerator::getValue('P13_Orgshortname', $data)?></span></div>
</div>
<div class="tr2">
    <div class="t7" style="width: 133px;">Ф.И.О. владельца</div>
    <div class="t7"><span class="value"><?=PdfGenerator::getValue('P4_Ownername', $data)?></span></div>
</div>
<div class="tr2">
    <div class="t8" style="width: 133px;">Адрес, телефон</div>
    <div class="t8"><span class="value"><?=$data['P5_Owneraddres'] ?? ''?>, <?=$data['P5_Ownercontact'] ?? ''?></span></div>
</div>
<div><i>Сведения о животном:</i></div>

<div class="t4">
    <div class="tr2">
        <div class="t3" style="width: 27px;">Вид:</div>
        <div class="t5"><span class="value"><?=PdfGenerator::getValue('P6_Speciesname', $data)?></span></div>
    </div>
    <div class="tr2">
        <div class="t3" style="width: 47px;">Порода:</div>
        <div class="t5"><span class="value"><?=PdfGenerator::getValue('P7_Breedname', $data)?></span></div>
    </div>
    <div class="tr2">
        <div class="t3" style="width: 52px;">Возраст:</div>
        <div class="t5"><span class="value"><?=PdfGenerator::petAge($data)?></span></div>
    </div>
</div>
<div class="t4">
    <div class="tr2">
        <div class="t3" style="width: 27px;">Пол:</div>
        <div class="t5"><span class="value"><?=PdfGenerator::petSex($data)?></span></div>
    </div>
    <div class="tr2">
        <div class="t3" style="width: 48px;">Кличка:</div>
        <div class="t5"><span class="value"><?=PdfGenerator::getValue('P9_Petname', $data)?></span></div>
    </div>
</div>


<br>

<div class="title">Регистрация</div>

<div class="t6">
    <div class="t4">
        <div class="tr2">
            <div class="t3" style="width: 15px;">№</div>
            <div class="t5"><span class="value"><?=PdfGenerator::getValue('P0_Petregnum', $data)?></span></div>
        </div>
        <div class="tr2">
            <?php
            if (isset($data['P0_Petregdate'])) {
                $P0_Petregdate = PdfGenerator::dateFromFormat($data['P0_Petregdate'], 'd.m.Y');
                ?>
                <div class="t3" style="width: 45px;">от «<?=$P0_Petregdate['d']?>»</div>
                <div class="t5" style="width: 90px;"><span class="value"><?=$P0_Petregdate['M']?></span></div>
                <div style="width: 60px;"><?=$P0_Petregdate['Y']?> года</div>
            <?php } else { ?>
                <div class="t3" style="width: 60px;">от «&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;»</div>
                <div class="t5" style="width: 180px;"><span class="value"></span></div>
                <div style="width: 60px;">20___ года</div>

            <?php } ?>
        </div>
    </div>

    <div class="clear"></div>
    <br>

    <table class="table1" border="1" bordercolor="#a9a9a9">
        <thead>
        <tr class="table__row1">
            <th class="table-key1">Наименование ТМЦ</th>
            <th class="table-key1">Серийный номер</th>
            <th class="table-key1">Срок годности</th>
            <th class="table-key1">Перечень заболеваний, от<br>которых привито животное</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (!empty($data['tmc'])) {
            foreach ($data['tmc'] as $tmc) {
                ?>
                <tr class="table__row1">
                    <td class="table-value-description1"><?=$tmc['name']?></td>
                    <td class="table-value-description1"><?=$tmc['inventory_number']?></td>
                    <td class="table-value-description1"><?=$tmc['vacexpiration_date']?></td>
                    <td class="table-value-description1"><?=$tmc['diseases']?></td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr class="table__row1">
                <td colspan="4" class="table-value-description1">-</td>
            </tr>
        <?php
        }
        ?>
        </tbody>
    </table>
    <br><br>
    <div>
        <div class="t32" style="width: 120px;">Ветеринарный врач</div>
        <div class="t5" style="width: 300px;"><span class="value"><?=PdfGenerator::getValue('P33_SpecialistFIO', $data)?></span></div>
    </div>
</div>
<br>
<?php if (isset($data['numbering']['page']) < isset($data['numbering']['pages'])) { ?>
    <div class="page-break"></div>
<?php } ?>