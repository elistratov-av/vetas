<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 22.01.19
 * Time: 13:27
 */

namespace app\modules\admin\models\forms;

use app\modules\admin\models\PetIdentification;
use app\modules\admin\models\PetsOldIdentification;
use Yii;
use yii\base\Model;

/**
 * Class ChipoldEditForm
 * @package app\modules\admin\models\forms
 */
class ChipoldEditForm extends Model
{
    public $id_pet;
    public $id_ident_type;
    public $identification_code;
    public $moved;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [
                ['identification_code'],
                'unique',
                'targetClass' => PetIdentification::class,
                'when' => function($model) {
                    return $model->id_ident_type == 1;
                }
            ],
            [['id_pet', 'id_ident_type', 'identification_code'], 'required'],
            [['id_pet', 'id_ident_type', 'identification_code', 'moved'], 'safe'],
        ];
    }

    /**
     * @return mixed
     * @throws \Throwable
     */
    public function save()
    {
        return Yii::$app->db->transaction(function(){
            $chipold = new PetsOldIdentification();
            $chipold->setAttributes($this->getAttributes());

            if ($chipold->validate()) {
                $chipold->save();

                return true;
            } else {
                return false;
            }
        });
    }

    /**
     * @param PetsOldIdentification $chipold
     * @return bool
     */
    public function edit(PetsOldIdentification $chipold)
    {

        $attr = Yii::$app->request->post();
        $chipold->id_pet = Yii::$app->request->get('id_pet');
        $chipold->id_ident_type = $attr['ChipoldEditForm']['id_ident_type'];
        $chipold->identification_code = $attr['ChipoldEditForm']['identification_code'];
        $chipold->moved = true;

        $chip = new PetIdentification();
        $chip->id_pet = $chipold->id_pet;
        $chip->id_ident_type = $chipold->id_ident_type;
        $chip->identification_code = $chipold->identification_code;
        $chip->main_flag = false;
        if ($chipold->validate()) {
            $chipold->save();
            $chip->save();
            return true;
        } else {
            return false;
        }
    }


}