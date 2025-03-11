<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 29.07.19
 * Time: 13:29
 * @var $dataProvider
 * @var $searchModel
 * @var $fullnames
 */

use app\modules\v2\modules\changeRequest\helpers\RequestEntitiesHelper;
use roboapp\multiselect\MultiSelect;
use yii\grid\ActionColumn;
use yii\helpers\Html;
use yii\helpers\Url;


$this->blocks['content-header'] = 'Запросы изменений'?>
<?= Html::a(
    'Сбросить все фильтры',
    Url::toRoute(['index']),
    [
        'class' => 'btn btn-default btn-sm'
    ]
) ?>
<div class="box">
    <div class="box-body">
<?=\yii\grid\GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'options' => ['class' => 'grid-view table-responsive'],
    'columns' => [
        [
            'attribute' => 'entity_name',
            'format' => 'raw',
            'filter' => MultiSelect::widget([
                'model' => $searchModel,
                'attribute' => 'entity_name',
                'data' => RequestEntitiesHelper::entityList(),
                'options' => [
                    'multiple' => true,
                ],
                'clientOptions' => [
                    'numberDisplayed' => 1,
                    'nSelectedText' => 'выбрано',
                    'nonSelectedText' => 'Не выбрано'
                ]
            ]),
            'value' => /**
             * @param $request
             * @return string
             */
                function ($request) {
                return RequestEntitiesHelper::entityLabel($request->entity_name);
            }
        ],
        [
            'attribute' => 'state',
            'format' => 'raw',
            'value' => /**
             * @param $request
             * @return string
             */
                function($request) {
                return RequestEntitiesHelper::stateLabel($request->state);
            },
            'filter' => MultiSelect::widget([
                'model' => $searchModel,
                'attribute' => 'state',
                'data' => RequestEntitiesHelper::stateList(),
                'options' => [
                    'multiple' => true,
                ],
                'clientOptions' => [
                    'nonSelectedText' => 'Не выбрано'
                ]
            ]),
        ],
        [
            'attribute' => 'type',
            'format' => 'raw',
            'value' => /**
             * @param $request
             * @return string
             */
                function($request) {
                return RequestEntitiesHelper::typeLabel($request->type);
            },
            'filter' => MultiSelect::widget([
                'model' => $searchModel,
                'attribute' => 'type',
                'data' => RequestEntitiesHelper::typeList(),
                'options' => [
                    'multiple' => true,
                ],
                'clientOptions' => [
                    'nonSelectedText' => 'Не выбрано'
                ]
            ]),
        ],
        [
            'attribute' => 'created_at',
        ],
        [
            'attribute' => 'author',
            'filter' => MultiSelect::widget([
                'model' => $searchModel,
                'attribute' => 'author',
                'data' => $fullnames,
                'options' => [
                    'multiple' => true,
                ],
                'clientOptions'=> [
                    'nonSelectedText' => 'Не выбрано',
                    'enableCaseInsensitiveFiltering' => true,
                    'maxHeight' => 450,
                    'numberDisplayed' => 1,
                    'nSelectedText' => 'выбрано'
                ]
            ]),
            'value' => 'requestAuthor.fullname'
        ],
        [
            'attribute' => 'author_org',
            'value' => /**
             * @param $request
             * @return mixed
             */
                function($request) {
                    return $request->organization->short_name;
                },
            'filter' => MultiSelect::widget([
                'model' => $searchModel,
                'attribute' => 'author_org',
                'data' => $organizations,
                'options' => [
                    'multiple' => true,
                ],
                'clientOptions'=> [
                    'nonSelectedText' => 'Не выбрано',
                    'enableCaseInsensitiveFiltering' => true,
                    'maxHeight' => 450,
                    'numberDisplayed' => 1,
                    'nSelectedText' => 'выбрано'
                ]
            ]),
        ],
        [
            'attribute' => 'updated_at',
            'value' => /**
             * @param $request
             * @return string
             */
                function($request) {
                return $request->updated_at ?? "Отсутствует";
            }
        ],
        [
            'attribute' => 'admin',
            'value' => /**
             * @param $request
             * @return mixed|string
             */
                function($request) {
                return $request->processedAdmin === null ? "Отсутствует" : $request->processedAdmin->fullname;
            }
        ],
        [
            'class' => ActionColumn::class,
            'template' => '{request-info}',
            'buttons' => [
                'request-info' => /**
                 * @param $url
                 * @param $model
                 * @param $key
                 * @return string
                 */
                    function ($url, $model, $key) {
                    return Html::a(
                        '<span class="glyphicon glyphicon-eye-open"></span>',
                        Url::to(['requests/info', 'id' => $model->id]),
                        ['class' => 'btn btn-xs btn-info', 'title' => 'Редактировать', 'target' => '_blank']
                    );
                },
            ],
        ],
    ]
]);
