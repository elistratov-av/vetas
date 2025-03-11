<?php
/**
 * @var View $this
 * @var AuditLog $log_row
 */

use app\models\db\audit\AuditLog;
use app\modules\audit\models\AuditLogHelper;
use yii\web\View;

?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Запись № <?= $log_row->id ?></h4>
        </div>

        <div class="modal-body">
            <p class="text-info"></p>
            <table class="table table-bordered">
                <tr>
                    <td colspan="2" class="bg-info"><?= AuditLogHelper::getDescription($log_row) ?></td>
                </tr>
                <tr>
                    <th colspan="2">Подробности</th>
                </tr>
                <tr>
                    <td colspan="2">
                        <pre><?= json_encode(
                                $log_row->snapshot,
                                JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE
                            )
                            ?></pre>
                    </td>
                </tr>
                <tr>
                    <th>Дата логирования</th>
                    <td><?= $log_row->date ?></td>
                </tr>
                <tr>
                    <th>Логин</th>
                    <td><?= $log_row->login ?></td>
                </tr>
                <tr>
                    <th>ID пользователя</th>
                    <td><?= $log_row->id_user ?></td>
                </tr>
                <tr>
                    <th>Таблица</th>
                    <td><?= $log_row->table_name ?></td>
                </tr>
                <tr>
                    <th>ID строки</th>
                    <td><?= $log_row->entity_id ?></td>
                </tr>
                <tr>
                    <th>Родительская таблица</th>
                    <td><?= $log_row->parent_table_name ?></td>
                </tr>
                <tr>
                    <th>ID родительской строки</th>
                    <td><?= $log_row->parent_entity_id ?></td>
                </tr>
                <tr>
                    <th>Версия создателя снимка</th>
                    <td><?= $log_row->snapshot_generator_version ?></td>
                </tr>
            </table>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
        </div>
    </div>
</div>
