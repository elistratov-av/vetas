<?php

namespace app\modules\v1\models;

use app\models\db\OrganizationType;

class OrganizationTypeActiveDataProvider extends ActiveDataProvider
{
    public function getModels()
    {
        $models = parent::getModels();

        foreach ( OrganizationType::SYSTEM_TYPES as $id => $data){
            $model = new EntityResource();
            $model->setAttributes([
                'id' => $id,
                'name' => $data['name'],
                'description' => $data['description'],
                'is_system' => true,
            ], false);

            array_unshift($models, $model);
        }

        return $models;
    }

}
