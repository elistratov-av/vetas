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
                [
                    'attribute' => 'id',
                    'headerOptions' => ['width' => '5%', 'class' => 'text-center'],
                ],
                [
                    'format' => 'raw',
                    'label' => 'Пользователь, действие и объект',
                    'value' => function ($model) {
                        /** @var AuditLog $model */
                        return
                            '<strong>Время создания: </strong>'
                            . $model->date
                            . '<br>'
                            . '<br><strong>Логин: </strong> '
                            . $model->login
                            . '<br><strong>ID: </strong>'
                            . $model->id_user
                            . '<br>'
                            . '<br><strong>Действие: </strong>'
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
                        /** @var array $snapshot */
                        $snapshot = $model->snapshot;
                        if (empty($snapshot)) {
                            return 'Ошибка вывода';
                        }

                        $key_names = [
                            'id' => 'ID',
                            'birthday' => 'Дата рождения',
                            'species_name' => 'Вид',
                            'breeds_name' => 'Порода',
                            'name' => 'Кличка',
                            'sex' => 'Пол',
                            'guide_dog' => 'Поводырь',
                            'castrated' => 'Кастрировано',
                            'date_plan_rabies_vaccination' => 'Дата план. вак. бешенство',
                            'date_plan_identification' => 'Дата план. идентификации',
                            'date_plan_lept_vaccination' => 'Дата план. вак. лептоспироз',
                            'reg_expire_date' => 'Дата снятия с учета',
                            'reg_expire_reason' => 'Причина снятия с учета',
                        ];

                        $text = AuditLogHelper::viewObject($key_names, $snapshot);
                        //В старых логах не было адреса содержания животного, добавляем отдельно
                        if (!empty($snapshot['fias_address'])){
                            $text .= AuditLogHelper::viewObject(['full_address' => 'Адрес содержания'], $snapshot['fias_address']);
                        }
                        return $text;
                    }
                ],
                [
                    'format' => 'raw',
                    'label' => 'Чипы/бирки',
                    'value' => function ($model) {
                        /** @var AuditLog $model */

                        $key_names = [
                            'id' => 'ID',
                            'identification_code' => 'Значение',
                            'main_flag' => 'Главный',
                            'type' => 'Тип',
                        ];

                        return AuditLogHelper::viewArrayObjects(
                            'pet_identification', $key_names, $model->snapshot
                        );
                    }
                ],
                [
                    'format' => 'raw',
                    'label' => 'Вакцинации',
                    'value' => function ($model) {
                        /** @var AuditLog $model */
                        /** @var array $snapshot */
                        $snapshot = $model->snapshot;
                        if (empty($snapshot) || !is_array($snapshot)) {
                            return 'Ошибка вывода';
                        }

                        /*
                         * Тут осторожнее
                         * На данный момент набор полей одинаков у
                         *
                         * pet_rabies_vaccinations = pet_other_vaccinations
                         *  и
                         * pet_dehelmintizations = pet_ectoparasites
                         *
                         * Причем список полей первых  - покрывает список полей вторых
                         * Если измениться - тут будут проблемы
                         */
                        $key_names = [
                            'id' => 'ID',
                            'drug_name' => 'Препарат',
                            'producer_name' => 'Производитель',
                            'batch' => 'Партия',
                            'production_date' => 'Дата производства',
                            'expiry_date' => 'Срок годности',
                            'date' => 'ДАТА ВАКЦИНАЦИИ',
                            'valid_until' => 'ВАЛИДНА ДО',
                        ];

                        $attrs = [
                            'pet_rabies_vaccinations' => 'Вакцинации от бешенства:',
                            'pet_dehelmintizations' => 'Дегельминтизация:',
                            'pet_ectoparasites' => 'Обработки против эктопаразитов:',
                            'pet_other_vaccinations' => 'Другие вакцинации:',
                        ];
                        $text = [];

                        foreach ($attrs as $attr_name => $caption) {
                            if (array_key_exists($attr_name, $snapshot)) {
                                $text[] =
                                    '<h5 class="text-center">'
                                    . $caption . (empty($snapshot[$attr_name]) ? ' <strong>-</strong>' : '')
                                    . '</h5>';

                                foreach ($snapshot[$attr_name] as $vaccinations) {
                                    $text[] = AuditLogHelper::viewObject($key_names, $vaccinations);
                                }
                            }
                        }

                        return implode('<hr>', $text);
                    }
                ],
                [
                    'format' => 'raw',
                    'label' => 'Владельцы',
                    'value' => function ($model) {
                        /** @var AuditLog $model */

                        $key_names = [
                            'id' => 'ID',
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
                            'fact_fias_addresses' => 'Факт. адрес',
                            'pet_owner_type' => 'Статус владельца'
                        ];

                        return AuditLogHelper::viewArrayObjects(
                            'pets_to_owner', $key_names, $model->snapshot
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
