<?php
/**
 * @var View $this
 * @var VisitLog $visit_log_row
 */

use app\models\db\audit\VisitLog;
use app\modules\audit\models\VisitLogHelper;
use yii\web\View;

?>
<div class="modal-dialog">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal">&times;</button>
			<h4 class="modal-title">Запись № <?= $visit_log_row->id ?></h4>
		</div>

		<div class="modal-body">
			<p class="text-info"></p>
			<table class="table table-bordered">
				<tr>
					<td colspan="2" class="bg-info"><?= VisitLogHelper::getDescription($visit_log_row) ?></td>
				</tr>
				<tr>
					<th colspan="2">Подробности</th>
				</tr>
				<tr>
					<td colspan="2">
                        <pre><?= json_encode(
								$visit_log_row->snapshot,
								JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE
							)
							?></pre>
					</td>
				</tr>
				<tr>
					<th>Дата логирования</th>
					<td><?= $visit_log_row->date ?></td>
				</tr>
				<tr>
					<th>ID пользователя</th>
					<td><?= $visit_log_row->id_user ?></td>
				</tr>
                <tr>
                    <th>ФИО пользователя</th>
                    <td><?= $visit_log_row->fio_user ?></td>
                </tr>
                <tr>
                    <th>ID организации</th>
                    <td><?= $visit_log_row->id_organization ?></td>
                </tr>
				<tr>
					<th>ID приема</th>
					<td><?= $visit_log_row->id_visit ?></td>
				</tr>
				<tr>
					<th>Версия создателя снимка</th>
					<td><?= $visit_log_row->snapshot_generator_version ?></td>
				</tr>
			</table>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
		</div>
	</div>
</div>
