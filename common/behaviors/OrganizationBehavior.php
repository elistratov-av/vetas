<?php
namespace app\common\behaviors;

use app\common\components\entity\EntityResourceFactory;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Class OrganizationBehavior
 * @package app\common\behaviors
 */
class OrganizationBehavior extends EntityBehavior
{
    use FiasTrait;

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_VALIDATE => function($event) {
                $this->validateMarkUp();
                $this->validateParentId();
            },
            ActiveRecord::EVENT_BEFORE_INSERT => function($event) {
                $this->setRelations();
            },
            ActiveRecord::EVENT_BEFORE_UPDATE => function($event) {
                $this->setRelations();
                // временный хак, чтобы не удалять адреса, т.к. используются на mos.ru
                if (!$this->owner->id_address) {
                    $this->owner->id_address = $this->owner->getOldAttribute('id_address');
                }
            },
            ActiveRecord::EVENT_AFTER_FIND => function($event) {
                $this->setNestedOrgs();
            },
        ];
    }

    /**
     *
     */
    public function setNestedOrgs()
    {
        $this->owner->additionalFields['child'] = 'child';
        $this->owner->additionalFields['address'] = 'address';
    }

    /**
     * @return array
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function getChild()
    {
        $childs = [];
        $resource = EntityResourceFactory::getResource('organizations');

        $models = $resource::find()->where(['parent_id' => $this->owner->id])->all();

        foreach ($models as $model) {
            $childs[] = $model->getAttributes();
        }

        return $childs;
    }

    /**
     * @return bool
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function setRelations()
    {
        $post = \Yii::$app->request->post();


        if(ArrayHelper::getValue($post, 'Organization.id_fias_address'))
            return true;

        if ($fiasAddress = $this->getFiasAddresses($post['Organization'])) {
            $this->owner->id_fias_address = $fiasAddress->id;
            //$this->owner->id_address = null;
            $this->owner->id_area = $fiasAddress->id_area;
            $this->owner->id_district = $fiasAddress->id_district;
        } else {
            $address = EntityResourceFactory::getResource('addresses');
//            var_dump($address); die();
            $addr = $address::findOne($this->owner->id_address);

            if ($addr) {
                $this->owner->id_area = $addr->id_area;
                $this->owner->id_district = $addr->id_district;
            }
        }

        EntityResourceFactory::getResource('organizations');

        return true;
    }

    public function validateParentId()
    {
        if (!empty($this->owner->parent_id) && $this->owner->parent_id == $this->owner->id) {
            $this->owner->addError('parent_id', 'Невозможно указать саму организацию в качестве своей родительской организации');
        }
    }

    public function validateMarkUp()
    {
        // Для отключенного флага нет необходимости валидировать
        if ($this->owner->mark_up_flag == FALSE){
            return;
        }

        if (empty($this->owner->mark_up_ratio)){
            $this->owner->addError('mark_up_ratio', 'Укажите коэффициент');
        }

        if (empty($this->owner->mark_up_from_time)){
            $this->owner->addError('mark_up_from_time', 'Укажите время начала действия тарифа');
        }

        if (empty($this->owner->mark_up_to_time)){
            $this->owner->addError('mark_up_to_time', 'Укажите время окончания действия тарифа');
        }

        if ($this->owner->mark_up_from_time == $this->owner->mark_up_to_time){
            $this->owner->addError('mark_up_from_time', 'Время начала действия тарифа и его окончания не должны совпадать');
        }
    }
}
