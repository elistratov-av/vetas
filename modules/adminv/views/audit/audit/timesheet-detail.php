<?php

use app\models\db\audit\TimesheetLog;
use yii\web\View;

/**
 * @var View $this
 * @var TimesheetLog $timesheet_log
 */
?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Запись № <?= $timesheet_log->id ?></h4>
        </div>

        <div class="modal-body">
            <p class="text-info"></p>
            <table class="table table-bordered">
                <tr>
                    <th colspan="2">Подробности</th>
                </tr>
                <tr>
                    <td colspan="2">
                        <pre><?= json_encode(
                                $timesheet_log->snapshot,
                                JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE
                            )
                            ?></pre>
                    </td>
                </tr>
                <tr>
                    <th>Дата удаления</th>
                    <td><?= $timesheet_log->date ?></td>
                </tr>
                <tr>
                    <th>ID Инициатора</th>
                    <td><?= $timesheet_log->id_initiator ?></td>
                </tr>
                <tr>
                    <th>ФИО Инициатора</th>
                    <td><?= $timesheet_log->fio_initiator ?></td>
                </tr>
                <tr>
                    <th>ID Специалиста</th>
                    <td><?= $timesheet_log->id_specialist ?></td>
                </tr>
                <tr>
                    <th>Версия создателя снимка</th>
                    <td><?= $timesheet_log->snapshot_generator_version ?></td>
                </tr>
            </table>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
        </div>
    </div>
</div>
