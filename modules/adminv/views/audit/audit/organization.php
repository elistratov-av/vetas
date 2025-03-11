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
                        /** @var array $snapshot */
                        $snapshot = $model->snapshot;
                        if (empty($snapshot)) {
                            return 'Ошибка вывода';
                        }

                        $key_names = [
                            'name' => 'Название',
                            'short_name' => 'Короткое название',
                            'inn' => 'ИНН',
                            'kpp' => 'КПП',
                            'ogrn' => 'ОГРН',
                            'id' => 'ID',
                        ];

                        $address = '';
                        if (!empty($snapshot['fias_addresses'])) {
                            $key_names_address = [
                                'full_address' => 'Адрес'
                            ];
                            $address = AuditLogHelper::viewObject($key_names_address, $snapshot['fias_addresses']);
                        }

                        return AuditLogHelper::viewObject($key_names, $snapshot) . $address;
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
