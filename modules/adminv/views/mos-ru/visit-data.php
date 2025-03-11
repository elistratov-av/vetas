<?php
/** @var \app\modules\admin\models\Visits $visit */
use yii\helpers\Html;
use yii\helpers\Url;

?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Прием № <?=$visit->id?></h4>
        </div>
        <div class="modal-body">
            <table>
                <tr>
                    <th>Время приема</th>
                    <td><?= $visit->start_dttm ?></td>
                </tr>
                <tr>
                    <th>Продолжительность приема</th>
                    <td><?= \yii\helpers\Html::encode($visit->duration)?></td>
                </tr>
                <tr>
                    <th>Перерыв после</th>
                    <td><?= \yii\helpers\Html::encode($visit->cooldown)?></td>
                </tr>
                <tr>
                    <th>Специалист</th>
                    <td>
                        <?php if ($visit->specialist) { ?>
                            <?= '#' . $visit->specialist->id . ' ' . $visit->specialist->fullname;?>
                        <?php }?>
                    </td>
                </tr>
                <tr>
                    <th>Услуги</th>
                    <td>
                        <?php /** @var \app\models\db\GovServices $service */?>
                        <?php foreach ($visit->services as $service) {?>
                            <?='#'. $service->id . ' ' . $service->name;?><br>
                        <?php }?>
                    </td>
                </tr>
            </table>
        </div>
        <div class="modal-footer">
            <?= Html::a('Детали', ['visit/info', 'id' => $visit->id], ['class' => 'btn btn-primary btn-flat']) ?>
            <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
        </div>
    </div>

</div>
