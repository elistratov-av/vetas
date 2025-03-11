<?php

/**
 * @var View $this
 */

use app\models\db\audit\AuditLog;
use app\modules\audit\models\AuditLogHelper;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

$this->blocks['content-header'] = 'Аудит';
$this->render('_part_JSCSS');

?>

<div class="box">
    <div class="box-body">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                'id',
                [
                    'label' => 'Время создания',
                    'value' => 'date'
                ],
                [
                    'format' => 'raw',
                    'label' => 'Пользователь',
                    'value' => function ($model) {
                        /** @var AuditLog $model */
                        return
                            '<strong>Логин: </strong> '
                            . $model->login
                            . '<br><strong>ID: </strong>'
                            . $model->id_user;
                    }
                ],
                [
                    'format' => 'raw',
                    'label' => 'Действие и объект',
                    'value' => function ($model) {
                        /** @var AuditLog $model */
                        return
                            '<strong>Действие: </strong>'
                            . AuditLogHelper::getActionText($model->action)
                            . '<br><strong>Объект: </strong>'
                            . AuditLogHelper::getTableText($model->table_name)
                            . '<br><strong>ID: </strong>'
                            . $model->entity_id;
                    }
                ],
                [
                    'format' => 'raw',
                    'label' => 'Объект',
                    'value' => function ($model) {
                        /** @var AuditLog $model */

                        $key_names = [
                            'jur_name' => 'Название юр.лица',
                            'inn' => 'ИНН',
                            'ogrn' => 'ОГРН',
                            'birthday' => 'Дата рождения',
                            'snils' => 'СНИЛС',
                            'fullname' => 'ФИО',
                            'is_deleted' => 'Пользователь удален',
                            'is_legal' => 'Флаг: юрлицо',
                            'entrepreneur' => 'Предприниматель',
                            'fias_addresses' => 'Адрес',
                            'fact_fias_addresses' => 'Факт. адрес'
                        ];

                        return AuditLogHelper::viewObject($key_names, $model->snapshot);
                    }
                ],
                [
                    'format' => 'raw',
                    'label' => 'Контакты',
                    'value' => function ($model) {
                        /** @var AuditLog $model */

                        $key_names = [
                            'id' => 'ID',
                            'name' => 'Значение',
                            'confirmed' => 'Подтвержден',
                            'main_flag' => 'Главный',
                            'type' => 'Тип',
                        ];

                        return AuditLogHelper::viewArrayObjects(
                            'contacts', $key_names, $model->snapshot
                        );
                    }
                ],
                [
                    'format' => 'raw',
                    'label' => 'Животные',
                    'value' => function ($model) {
                        /** @var AuditLog $model */

                        $key_names = [
                            'id' => 'ID',
                            'reg_expire_date' => 'Дата снятия с учета',
                            'birthday' => 'Дата рождения',
                            'name' => 'Кличка',
                            'sex' => 'Пол',
                            'reg_expire_reason' => 'Причина снятия с учета',
                            'species_name' => 'Вид',
                            'breeds_name' => 'Порода',
                            'pet_owner_type' => 'Статус владельца'
                        ];

                        return AuditLogHelper::viewArrayObjects(
                            'pets', $key_names, $model->snapshot
                        );
                    }
                ],
                [
                    'format' => 'raw',
                    'value' => function ($model) {
                        /** @var AuditLog $model */
                        return Html::a(
                            '<span class="glyphicon glyphicon-eye-open">',
                            ['audit/audit/log-detail', 'id' => $model->id],
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
