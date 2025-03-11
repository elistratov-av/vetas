<?php

use app\modules\adminv\models\AnimalIdHelper;
use app\modules\animalid\models\db\Id_Map;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $dataProvider \app\modules\admin\data\AdminDataProvider */

$this->blocks['content-header'] = 'Конфликты';
?>
<div class="box">
    <div class="box-body">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'options' => ['class' => 'grid-view table-responsive'],
            'columns' => [
                [
                    'label' => 'Тип сообщения',
                    'attribute' => 'type',
                    'value' => function ($model) {
                        return $model->type == 'pet' ? 'Животное' : 'Организация';
                    }
                ],
                [
                    'label' => 'Исходные данные',
                    'headerOptions' => ['width' => '40%', 'class' => 'text-center'],
                    'attribute' => 'data',
                    'format' => 'raw',
                    'value' => function ($model) {
                        $html = '';
                        $inner = '';
                        $sex = '';
                        $data = $model->data;
                        switch ($model->type) {
                            case 'pet':
                                $inner = AnimalIdHelper::formatOurPet($model->our_id);
                                break;
                            case 'company':
                                $inner = AnimalIdHelper::formatOurOrg($model->our_id);
                                break;
                        }

                        $html = '<div class="conflictsrc-list-wrapper">';
                        $html .= '<div class="conflictsrc-list">';
                        $html .= $inner;
                        $html .= '</div>';
                        $html .= '</div>';
                        return $html;
                    }
                ],
                [
                    'label' => 'Внешние данные',
                    'headerOptions' => ['width' => '40%', 'class' => 'text-center'],
                    'attribute' => 'data',
                    'format' => 'raw',
                    'value' => function ($model) {

                        $data = $model->data;
                        $html = '';
                        switch ($model->type) {
                            case 'pet' :
                                $inner = AnimalIdHelper::formatTheirPet($data);
                                break;
                            case 'company':
                                $inner = AnimalIdHelper::formatTheirOrg($data);
                                break;
                        }

                        $html = '<div class="conflictsrc-list-wrapper">';
                        $html .= '<div class="conflictsrc-list">';
                        $html .= $inner;
                        $html .= '</div>';
                        $html .= '</div>';
                        return $html;
                    }
                ],
                [
                    'class' => ActionColumn::class,
                    'headerOptions' => ['width' => '10%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center', 'style' => 'white-space: nowrap !important;'],
                    'template' => '{merge} {skip}',
                    'buttons' => [
                        'merge' => function ($url, $model, $key) {
                            /* @var $model \app\modules\animalid\models\db\ConflictsModel */
                            return
                                Html::a(
                                    '<span class="glyphicon glyphicon-backward"></span>',
                                    ['animal-id/merge', 'id' => $model->id],
                                    ['class' => 'btn btn-xs btn-info', 'title' => 'Обновить исходную сущность']
                                );
                        },
                        'skip' => function ($url, $model, $key) {
                            /* @var $model \app\modules\animalid\models\db\ConflictsModel */
                            return
                                Html::a(
                                    '<span class="glyphicon glyphicon-remove-sign"></span>',
                                    ['animal-id/skip', 'id' => $model->id],
                                    ['class' => 'btn btn-xs btn-info', 'title' => 'Пропустить']
                                );
                        },
                    ],
                ],
            ],
        ]);
        ?>
    </div>
</div>
