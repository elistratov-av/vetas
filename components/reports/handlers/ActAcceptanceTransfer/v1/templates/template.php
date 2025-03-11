<?php
/* @var \app\common\components\reports\handlers\ActAcceptanceTransfer\v1\dto\ReportDto $dto */
/* @var \app\common\components\reports\handlers\ActAcceptanceTransfer\v1\dto\ReportTmcDto $tmcDto */
?>
<div class="body">
    <div class="head1">Утверждаю</div>
    <div class="clear"></div>
    <div class="head1">Главный ветеринарный врач</div>
    <div class="clear"></div>
    <div class="head1"><?=$dto->fromOrganizationNameHeader?></div>
    <div class="clear"></div>
    <div class="head1">__________________________________</div>
    <div class="clear"></div>
    <br><br>
    <div class="title1">Акт №<?=$dto->number?><br>Приёма-передачи ТМЦ</div>
    <div class="clear"></div>
    <br><br>
    <div style="text-indent:30px"><?=($dto->fromSpecialistName ? 'Ветеринарный врач ' . $dto->fromOrganizationName : $dto->fromOrganizationName) ?> <?=$dto->fromSpecialistName?> <?=$dto->acceptorDate->format('d.m.Y')?>г. передал(а),
        <?= ($dto->toSpecialistName ? 'ветеринарный врач ' . $dto->toOrganizationName : $dto->toOrganizationName) ?> <?=$dto->toSpecialistName?> получил(а) следующие товарно-материальные ценности:
    </div>
    <br>
    <div>
        <ul>
            <?php foreach ($dto->tmc as $tmcDto) { ?>
                <li><?=$tmcDto->name?> в количестве <?=$tmcDto->count;?></li>
            <?php } ?>
        </ul>
    </div>
    <div class="clear"></div>
    <br><br>
    <div class="head2"><?=$dto->fromSpecialistName?></div>
    <div class="head2">Сдал</div>
    <br><br>
    <div class="head2"><?=$dto->toSpecialistName?></div>
    <div class="head2">Принял</div>
</div>
