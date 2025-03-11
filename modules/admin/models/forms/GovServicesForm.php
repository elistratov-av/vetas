<?php

namespace app\modules\admin\models\forms;

use app\modules\admin\models\GovServices;
use yii\base\Model;
use Yii;


/**
 * Class ServiceEditForm
 * @package app\modules\admin\models
 */
class GovServicesForm extends Model
{
    public $id;
    public $name;
    public $price;
    public $sort_by;
    public $id_service_type;
    public $duration;
    public $cooldown;
    public $id_service_goal;
    public $at_home;
    public $type;
    public $id_pricelist;
    public $created_by;
    public $updated_by;
    public $created_at;
    public $updated_at;
    public $id_cabinet_type;
    public $id_specialization;
    public $id_service_measure;
    public $alternative_name;
    public $cod;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id_service_goal', 'at_home', 'type'], 'safe'],
            [['name', 'id_service_type', 'duration', 'cooldown'], 'required'],
            [['sort_by', 'duration', 'cooldown'], 'integer'],
        ];
    }

    /**
     * @return mixed
     * @throws \Throwable
     */
    public function save()
    {
        return Yii::$app->db->transaction(function(){
            $service = new GovServices();
            $service->setAttributes($this->getAttributes());
            $service->price = $service->price ?? 0;
            $service->type = 'mosru';
            if ($service->validate()) {
                $service->save();
                return true;
            } else {
                return false;
            }
        });
    }


    /**
     * @param GovServices $service
     * @return mixed
     * @throws \Throwable
     */
    public function editService(GovServices $service)
    {
        return Yii::$app->db->transaction(function () use ($service) {
            $service->setAttributes($this->getAttributes());
            $service->price = $service->price ?? 0;
            $service->type = 'mosru';
            if ($service->validate()) {
                $service->save();
                return true;
            } else {
                return false;
            }
        });
    }
}