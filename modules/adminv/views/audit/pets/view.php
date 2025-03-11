<?php

use yii\helpers\Html;
use yii\web\YiiAsset;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\db\Pets */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Pets', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

YiiAsset::register($this);
?>
<div class="pets-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'reg_expire_date',
            'birthday',
            'name',
            'sex',
            'id_species',
            'id_breed',
            'id_reg_organization',
            'id_reg_expire_reason',
            'photo',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
            'reg_date',
            'guide_dog:boolean',
            'castrated:boolean',
            'date_plan_rabies_vaccination',
            'date_plan_identification',
            'date_plan_lept_vaccination',
            'color:ntext',
            'characteristics:ntext',
            'id_created_organization',
        ],
    ]) ?>

</div>
