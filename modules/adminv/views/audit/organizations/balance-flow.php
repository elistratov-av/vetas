<?php

use app\models\db\BalanceFlow;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\adminv\models\search\BalanceFlowSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $organization \app\models\db\Organizations */

$this->blocks['content-header'] = 'История баланса ' . $organization->short_name;
/**
 * Текстовое описание констант
 */
$balance_flow_tmc_type = [
    'balance_drugs' => 'Лекарство',
    'balance_exp_materials' => 'Расходный материал',
    'balance_vaccines' => 'Вакцина',
];
?>
<div class="box">
    <div class="box-body">

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            //'filterModel' => $searchModel,
            'columns' => [
                'id',
                [
                    'label' => 'Дата',
                    'attribute' => 'created_at',
                ],
                [
                    'label' => 'Пользователь',
                    'value' => function ($model, $key, $index, $widget) {
                        /* @var $model \app\models\db\BalanceFlow */
                        if (!empty($model->userCreator)) {
                            return $model->userCreator->login;
                        }
                        return '';
                    },
                ],
                [
                    'label' => 'Тип',
                    'attribute' => 'balance_tmc_type',
                    'value' => function ($model, $key, $index, $widget) use ($balance_flow_tmc_type) {
                        /* @var $model \app\models\db\BalanceFlow */
                        if (array_key_exists($model->balance_tmc_type, $balance_flow_tmc_type)) {
                            return $balance_flow_tmc_type[$model->balance_tmc_type];
                        }
                        return '';
                    },
                ],
                'id_balance_tmc_type',
                //'id_visitservice',
                [
                    'label' => 'Приход/расход',
                    'attribute' => 'flow_type',
                    'value' => function ($model, $key, $index, $widget) {
                        /* @var $model \app\models\db\BalanceFlow */
                        if ($model->flow_type == BalanceFlow::FLOW_TYPE_INCOME) {
                            return 'Приход';
                        } elseif ($model->flow_type == BalanceFlow::FLOW_TYPE_EXPENSE) {
                            return 'Расход';
                        }
                        return '';
                    },
                ],
                [
                    'label' => 'Описание',
                    'value' => function ($model, $key, $index, $widget) {
                        /* @var $model \app\models\db\BalanceFlow */
                        if (!empty($model->balanceItemInfo->name)) {
                            return $model->balanceItemInfo->name;
                        }
                        return '';
                    },
                ],
                [
                    'label' => 'Кол-во',
                    'attribute' => 'count',
                ],
                [
                    'label' => 'Прием',
                    'format' => 'raw',
                    'value' => function ($model, $key, $index, $widget) {
                        /* @var $model \app\models\db\BalanceFlow */
                        if (!empty($model->visitservice->id_visit)) {
                            return Html::a(
                                $model->visitservice->id_visit,
                                Url::to(['visit/info', 'id' => $model->visitservice->id_visit]), [
                                'target' => '_blank',
                            ]);
                        }
                        return '';
                    },
                ],
                //'updated_by',
                //'updated_at',
            ],
        ]); ?>
    </div>
</div>
