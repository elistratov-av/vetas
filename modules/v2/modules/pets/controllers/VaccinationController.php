<?php


namespace app\modules\v2\modules\pets\controllers;

use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\pets\models\BroodModel;
use app\modules\v2\modules\pets\models\VaccinationModel;
use DateTime;
use yii\web\BadRequestHttpException;

class VaccinationController extends BaseController
{
    /**
     * Возвращает список всех вакцинаций животного
     *
     * @param $id_pet
     * @return array
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionAll($id_pet)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        if (is_array($id_pet)) {
            return $this->listByPet($id_pet);
        }

        return [
            'result' => (new VaccinationModel())->getAll($id_pet),
        ];
    }

    /**
     * Возвращает список вакцинаций для указанных животных, сгруппированный по животным.
     * Предназначен для вызова из экшена.
     *
     * @param int[] $id_pet
     *
     * @return array
     *
     * @throws \yii\web\BadRequestHttpException
     */
    private function listByPet(array $ids_pets = [])
    {
        $max = BroodModel::MAX_BROOD_COUNT + 5;
        /* так, на всякий случай... */

        if (1 > count($ids_pets) || $max < count($ids_pets)) {
            throw new \yii\web\BadRequestHttpException("Размер id_pet должен быть не меньше 1 и не больше {$max}");
        }

        foreach ($ids_pets as $idx => $id_pet) {
            if (!VaccinationModel::isIdPetValid($id_pet)) {
                $msg = is_scalar($id_pet)
                    ? "id_pet[{$idx}] имеет неверный формат: {$id_pet}"
                    : "id_pet[{$idx}] имеет неверный формат";
                throw new \yii\web\BadRequestHttpException($msg);
            }
        }

        return [
            'result' => [
                'by_pet' => VaccinationModel::getAllByIdPet($ids_pets, true),
            ],
        ];
    }

    /**
     * Сохранение
     *
     * @param $id_pet
     * @param $vaccinations
     * @return array
     * @throws \Throwable
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionSave($id_pet, $vaccinations)
    {
        $today = new DateTime();
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);
        foreach ($vaccinations as $vaccination_type => $data) {
            foreach ($data as $item) {
                if (new DateTime($item['date']) > $today->modify('+1 day')) {
                    throw new BadRequestHttpException('Ввод актуальных сведений по вакцинациям и / или обработки необходимо осуществлять через прием.');
                }
            }
        }
        (new VaccinationModel())->save($id_pet, $vaccinations);

        return [
            'result' => true,
        ];
    }
}
