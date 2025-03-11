<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 29.01.19
 * Time: 13:49
 */

namespace app\modules\admin\models\forms;


use app\modules\admin\models\PetIdentification;
use Yii;
use yii\base\Model;

/**
 * Class ChipsEditForm
 * @package app\modules\admin\models\forms
 */
class ChipsEditForm extends Model
{
    public $id_pet;
    public $id_ident_type;
    public $identification_code;


    /**
     * @return array
     */
    public function rules()
    {
        return [[
            ['identification_code'],
            'unique',
            'targetClass' => PetIdentification::class,
            'targetAttribute' => ['id_ident_type', 'identification_code'],
            'when' => function($model) {
                return $model->id_ident_type == 1;
            }
            ],
            [['id_pet', 'id_ident_type', 'identification_code'], 'required'],
            [['id_pet', 'id_ident_type', 'identification_code'], 'safe'],

        ];
    }


    /**
     * @return mixed
     * @throws \Throwable
     */
    public function save()
    {
        return Yii::$app->db->transaction(function(){
            $chip = new PetIdentification();
            $chip->setAttributes($this->getAttributes());

            if ($chip->validate()) {
                $chip->save();

                return true;
            } else {
                return false;
            }
        });
    }


    /**
     * @param PetIdentification $chip
     * @return bool
     */
    public function edit(PetIdentification $chip)
    {
        $attr = Yii::$app->request->post();
        $chip->id_pet = Yii::$app->request->get('id_pet');
        $chip->id_ident_type = $attr['ChipsEditForm']['id_ident_type'];
        $chip->identification_code = $attr['ChipsEditForm']['identification_code'];
        if ($chip->validate()) {
            $chip->save();
            return true;
        } else {
            return false;
        }
    }

}