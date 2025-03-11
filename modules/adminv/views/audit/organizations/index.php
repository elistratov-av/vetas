<?php

use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $dataProvider \app\modules\admin\data\AdminDataProvider */
/* @var $searchModel \app\modules\adminv\models\search\OrganizationSearch */

$this->blocks['content-header'] = 'Организации';
?>
<div class="box">
    <div class="box-body">
        <?php echo GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'options' => ['class' => 'grid-view table-responsive'],
            'columns' => [
                [
                    'attribute' => 'id',
                    'headerOptions' => ['width' => '5%', 'class' => 'text-center'],
                ],
                [
                    'label' => 'Название',
                    'attribute' => 'name',
                    'headerOptions' => ['width' => '25%', 'class' => 'text-center'],
                    'value' => function ($model, $key, $index, $widget) {
                        /* @var $model \app\models\db\Organizations */
                        return $model->short_name;
                    },
                ],
                [
                    'label' => 'Родительская организация',
                    'attribute' => 'parent_name',
                    'headerOptions' => ['width' => '25%', 'class' => 'text-center'],
                    'value' => function ($model, $key, $index, $widget) {
                        /* @var $model \app\models\db\Organizations */
                        return $model->parentOrganization === null ? '-' : $model->parentOrganization->short_name;
                    },
                ],
                [
                    'label' => 'Головная организация',
                    'attribute' => 'root_name',
                    'headerOptions' => ['width' => '25%', 'class' => 'text-center'],
                    'value' => function ($model, $key, $index, $widget) {
                        /* @var $model \app\models\db\Organizations */
                        return $model->rootOrganization === null ? '-' : $model->rootOrganization->short_name;
                    },
                ],
                [
                    'class' => ActionColumn::class,
                    'headerOptions' => ['width' => '10%', 'class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center', 'style' => 'white-space: nowrap !important;'],
                    'template' => '{balance-flow} {audit}',
                    'buttons' =>
                        [
                            'balance-flow' => function ($url, $model, $key) {
                                /* @var $model \app\models\db\Organizations */
                                return
                                    //Yii::$app->user->can('data.organizations.manage.W', ['model' => $model])?
                                    Html::a(
                                        '<span class="glyphicon glyphicon-th-large"></span>',
                                        ['audit/organizations/balance-flow', 'id_organization' => $model->id],
                                        ['class' => 'btn btn-xs btn-info', 'title' => 'Аудит']
                                    );
                                //: '';
                            },
                            'audit' => function ($url, $model, $key) {
                                /* @var $model \app\models\db\Organizations */
                                return
                                    //Yii::$app->user->can('data.organizations.manage.W', ['model' => $model])?
                                    Html::a(
                                        '<span class="glyphicon glyphicon-sunglasses"></span>',
                                        ['audit/audit/organizations', 'id' => $model->id],
                                        ['class' => 'btn btn-xs btn-info', 'title' => 'Аудит']
                                    );
                                //: '';
                            },
                        ],
                ],
            ],
        ]);
        ?>
    </div>
</div>
