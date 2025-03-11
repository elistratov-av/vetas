<?php

namespace app\modules\elk\models;

use app\models\db\elk\ElkPets;
use yii\base\Model;

class DeletePet extends Model
{
    /** @var integer */
    public $id;

    public function rules()
    {
        return [
            ['id', 'required'],
            ['id', 'string'],
            ['id', 'exist', 'skipOnError' => true, 'targetClass' => ElkPets::class, 'targetAttribute' => ['id' => 'ext_id']]
        ];
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function handle()
    {
        $elkPet = ElkPets::findOne(['ext_id' => $this->id]);
        if(($elkPet->pet->createdOrganization === null || !$elkPet->pet->createdOrganization->org_type->is_tech)
            && !$elkPet->hasVisits() && !$elkPet->hasViolations()) {
            $this->deletePetToOwner($elkPet->id_pet_owner, $elkPet->id_pet);
            if (!$elkPet->hasAnothePetLinks()) {
                $elkPet->pet->delete();
            }
        }
        $elkPet->delete();
    }

    /**
     * @param int $id_owner
     * @param int $id_pet
     * @throws \yii\db\Exception
     */
    protected function deletePetToOwner(int $id_owner, int $id_pet)
    {
        \Yii::$app->db->createCommand('delete from pets_to_owner where id_owner = :id_owner and id_pet = :id_pet', [
            ':id_owner' => $id_owner,
            ':id_pet' => $id_pet
        ])->execute();
    }
}
