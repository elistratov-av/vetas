<?php


namespace app\modules\v2\modules\pets\models;


use app\models\db\Organizations;
use app\models\db\PetOwners;
use app\models\db\PetOwnersHistory;
use app\models\db\PetOwnerType;
use yii\web\BadRequestHttpException;

class PetOwnersHistoryModel
{
    /**
     * Возвращает историю изменений владельцев
     *
     * @param int $id_pet
     * @return array
     */
    public function history(int $id_pet)
    {
        return PetOwnersHistory::find()
            ->where(['id_pet' => $id_pet])
            ->orderBy('id DESC')
            ->asArray()
            ->all()
            ;
    }

    public function saveHistory($id_pet, $id_owner, $id_old_owner_type, $id_new_owner_type)
    {
        $user = \Yii::$app->user->getIdentity();

        $history_record = new PetOwnersHistory();

        $history_record->id_pet = $id_pet;
        $history_record->owner_type_old = $this->getOwnerTypeName($id_old_owner_type);
        $history_record->owner_type_new = $this->getOwnerTypeName($id_new_owner_type);
        $history_record->owner_name = $this->getOwnerName($id_owner);

        $history_record->created_by = $user->login;

        // Название организации
        if (!empty($user->specialist) && !empty($user->specialist->id_organization)){
            $name = Organizations::find()
                ->select('name')
                ->where(['id' => $user->specialist->id_organization])
                ->scalar();

            $history_record->organization_name = (!empty($name)) ? $name : NULL; // FALSE нам не нужен
        }

        if (!$history_record->save()){
            $errors = $history_record->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при сохранении истории изменений' : implode("\n", array_values($errors)));
        }

    }

    /**
     * Возвращает название типа владельца
     *
     * @param $id
     * @return string
     * @throws BadRequestHttpException
     */
    protected function getOwnerTypeName($id)
    {
        if (empty($id)){
            return NULL;
        }

        $type = PetOwnerType::findOne($id);

        if (empty($type)){
            throw new BadRequestHttpException('Указан неизвестный id_owner_type');
        }

        return $type->name;
    }

    /**
     * Возвращает имя владельца / представителя животного
     *
     * @param $id
     * @return string
     * @throws BadRequestHttpException
     */
    protected function getOwnerName($id)
    {
        $pet_owner = PetOwners::findOne(['id' => $id]);

        if (empty($pet_owner)){
            throw new BadRequestHttpException('Не удалось найти указанного владельца');
        }
        return $pet_owner->fullname;
    }
}
