<?php

/**
 * @var View $this
 */

use app\models\db\audit\VisitLog;
use app\models\db\Organizations;
use app\models\db\PetOwners;
use app\models\db\Pets;
use app\modules\audit\models\AuditLogHelper;
use app\modules\audit\models\VisitLogHelper;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

$this->blocks['content-header'] = 'История изменения статуса приема';
$this->render('_part_JSCSS');

?>

<div class="box">
	<div class="box-body">
		<?= GridView::widget([
			'dataProvider' => $dataProvider,
			'columns' => [
				[
					'attribute' => 'id',
					'headerOptions' => ['width' => '5%', 'class' => 'text-center'],
				],
				[
					'format' => 'raw',
					'label' => 'Пользователь, организация, статус',
					'value' => function ($model) {
						/** @var VisitLog $model */
						return
							'<strong>Время создания: </strong>'
							. $model->date
							. '<br>'
							. '<br><strong>Инициатор: </strong> '
							. VisitLogHelper::getInitiatorText($model->initiator)
							. '<br>'
							. '<br><strong>ФИО: </strong> '
							. $model->fio_user
							. '<br><strong>ID: </strong>'
							. $model->id_user
							. '<br>'
                            . '<br><strong>Организация: </strong>'
							. ($model->id_organization ?
                                Organizations::find()
                                    ->select('short_name')
                                    ->where(['=', 'id', $model->id_organization])
                                    ->one()
                                    ->short_name
                                : '-')
							. '<br><strong>ID организации: </strong>'
							. ($model->id_organization ?: '-')
							. '<br>'
							. '<br><strong>Статус: </strong>'
							. VisitLogHelper::getStatusText($model->status_visit)
							. '<br><strong>ID приема: </strong>'
							. $model->id_visit;
					}
				],
				[
					'format' => 'raw',
					'label' => 'Прием',
					'value' => function ($model) {
						/** @var VisitLog $model */
						/** @var array $snapshot */
						$snapshot = $model->snapshot;
						if (empty($snapshot)) {
							return 'Ошибка вывода';
						}

						$key_names = [
							'is_paid' => 'Оплачено',
							'change_reason' => 'Причина отмены',
							'id_organization' => 'ID Организации',
							'created_by' => 'Автор создания',
							'updated_by' => 'Автор последнего изменения',
							'created_at' => 'Дата создания',
							'updated_at' => 'Дата последнего изменения',
							'fact_start_dttm' => 'Фактическое время начала приема',
							'fact_end_dttm' => 'Фактическое время окончания приема',
							'cooldown' => 'Продолжительность перерыва после приема',
							'author' => 'Автор',
							'time_range' => 'Время приема',
							'number' => 'Номер',
							'channel' => 'Тип смены',
							'source' => 'ID Источника',
							'duration' => 'Предполагаемая продолжительность',
                            'ticket_number' => 'Номер талона',
                            'start_dttm' => 'Начало приема',
                            'time_range_without_cooldown' => 'Время приема с перерывом',
                            'type' => 'Тип приема',
                            'visit_to_address' => 'Адрес для выезда',
                            'description' => 'Примечание',
                            'time_signed' => 'Время подписания',
                            'is_signed' => 'Подписан',
                            'id_sign' => ' ID подписи',
						];

						return AuditLogHelper::viewObject(
						        $key_names,
                                VisitLogHelper::prepareVisitSnapshot($snapshot)
                        );
					}
				],
				[
					'format' => 'raw',
					'label' => 'Владелец, питомец',
					'value' => function ($model) {
						/** @var VisitLog $model */
						/** @var array $snapshot */
						$snapshot = $model->snapshot;
						if (empty($snapshot)) {
							return 'Ошибка вывода';
						}

						$key_names = [
							'fio_owner' => 'Владелец',
							'id_owner' => 'ID Владельца',
                            'name_pet' => 'Имя питомеца',
							'id_pet' => 'ID Питомца',
							'is_veteran' => 'Ветеран ВОВ',
							'is_disabled' => 'Инвалид I группы',
							'is_blind' => 'Слабовидящий с животным поводырем',
							'is_orphan' => 'Сирота/ребенок без попечителя',
							'is_large_family' => 'Многодетная семья',
							'is_veteran_of_labour' => 'Ветеран труда',
                            'preferences_document' => 'Номер документа, подтверждающего льготу'
						];

						$data = [
                            'fio_owner' => PetOwners::findOne(['id' => $snapshot['id_owner']])->fullname,
                            'id_owner' => $snapshot['id_owner'],
							'is_veteran' => $snapshot['is_veteran'],
							'is_disabled' => $snapshot['is_disabled'],
							'is_blind' => $snapshot['is_blind'],
                            //VETAIS-3233 старые снапшоты не имеют трех следующих полей, поэтому добавлена проверка
							'is_orphan' => $snapshot['is_orphan'] ?? false,
							'is_large_family' => $snapshot['is_large_family'] ?? false,
							'is_veteran_of_labour' => $snapshot['is_veteran_of_labour'] ?? false,
							'preferences_document' => $snapshot['preferences_document'] ?: '-',
							'name_pet' => Pets::findOne(['id' => $snapshot['id_pet']])->name ?? '',
							'id_pet' => $snapshot['id_pet'] ?? '',
                        ];

						return AuditLogHelper::viewObject($key_names, $data);
					}
				],
				[
					'format' => 'raw',
					'headerOptions' => ['width' => '5%', 'class' => 'text-center'],
					'contentOptions' => ['class' => 'text-center', 'style' => 'white-space: nowrap !important;'],
					'value' => function ($model) {
						/** @var VisitLog $model */
						return Html::a(
							'<span class="glyphicon glyphicon-eye-open">',
							['audit/audit/visit-detail', 'id' => $model->id],
							[
								'class' => 'btn btn-xs btn-success show-visit',
								'title' => 'Подробности'
							]);
					},
				]
			]
		]);
		?>
	</div>
</div>

<!-- Modal -->
<div class="modal fade" id="visitModal" role="dialog">

</div>
