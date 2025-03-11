<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 30.11.18
 * Time: 17:48
 */

use app\common\components\rbac\Role;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;

/* @var $this \yii\web\View */
/* @var $dataProvider \app\modules\admin\data\AdminDataProvider */
/* @var $searchModel \app\modules\adminv\models\search\OrganizationSearch */

$this->blocks['content-header'] = 'Приюты';

$isSysAdmin = Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS);
?>
<div class="box">
    <div class="box-body">
        <?php echo $isSysAdmin ? Html::a(
            '<span class="glyphicon glyphicon-plus"></span> Добавить',
            ['/adminv/shelters/create'],
            ['class' => 'btn btn-sm btn-success btn-flat']
        ) : ''; ?>
        <?php echo GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
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
                    'template' => '{edit} {delete}',
                    'buttons' =>
                        [
                            'edit'   => function ($url, $model, $key) use ($isSysAdmin) {
                                /* @var $model \app\models\db\Organizations */
                                return $isSysAdmin
                                    ? Html::a(
                                        '<span class="glyphicon glyphicon-pencil"></span>',
                                        ['shelters/update', 'id' => $model->id],
                                        ['class' => 'btn btn-xs btn-warning', 'title' => 'Редактировать']
                                    )
                                    : '';
                            },
                            'delete' => function ($url, $model, $key) use ($isSysAdmin) {
                                /* @var $model \app\models\db\Organizations */
                                return $isSysAdmin
                                    ? Html::a(
                                        '<span class="glyphicon glyphicon-trash"></span>',
                                        ['shelters/delete', 'id' => $model->id],
                                        [
                                            'class'        => 'btn btn-xs btn-danger',
                                            'title'        => 'Удалить',
                                            'data-method'  => 'post',
                                            'data-confirm' => 'Вы уверены, что хотите удалить организацию?',
                                        ]
                                    )
                                    : '';
                            },
                        ],
                ],
            ],
        ]);
        ?>
    </div>
</div>
