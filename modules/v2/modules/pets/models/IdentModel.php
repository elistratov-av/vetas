<?php


namespace app\modules\v2\modules\pets\models;

use app\models\db\IdentificationTypes;
use app\models\db\PetIdentification;
use app\models\db\Pets;
use app\models\db\ShelterGuests;
use app\modules\v2\modules\gosvetnadzor\models\ViolationModel;
use app\modules\v2\modules\shelter\models\ShelterGuestModel;
use yii\web\BadRequestHttpException;

class IdentModel
{
    /**
     * Массив обьектов типа "Метка" существующих в БД для запрашиваемого животного
     * Ипользуется при save
     *
     * @var PetIdentification[]
     */
    protected $old_indents = [];

    /**
     * Массив массивов меток на сохранение в БД
     * Ипользуется при save
     *
     * @var array
     */
    protected $new_indents = [];

    /**
     * ID записи "чип" в таблице identification_types
     * Ипользуется при save
     *
     * @var integer
     */
    protected $identification_types_chip_id;

    /**
     * Получение идентификационной метки
     *
     * @param int $id
     *
     * @return PetIdentification
     * @throws BadRequestHttpException
     */
    public function get(int $id)
    {
        return PetIdentification::findOne(['id' => $id]);
    }

    /**
     * Возвращает список типов идентификации
     *
     * @return IdentificationTypes[]
     */
    public function getIdentTypes()
    {
        return IdentificationTypes::find()
            ->all();
    }

    /**
     * Сохраняет значения идентификационных меток
     *
     * @param $id_pet
     * @param $indents
     *
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function save($id_pet, $indents)
    {
        if ($indents !== null && !is_array($indents)) {
            throw new BadRequestHttpException('Параметр indents должен быть массивом или NULL');
        }

        if (empty($id_pet) || !is_numeric($id_pet)) {
            throw new BadRequestHttpException('Не указан id_pet или ошибочный формат');
        }

        /*
         * Проверяем, что животное существует и его данные можно редактировать
         */
        $this->checkPet($id_pet);

        if (!empty($indents)) {
            $this->validateAndPrepareIndentsArray($id_pet, $indents);
        }


        PetIdentification::getDb()->beginTransaction();

        /*
         * Ищем id записи "чип" в таблице identification_types
         */
        $this->identification_types_chip_id = IdentificationTypes::findIdentificationTypeId("чип");

        /*
         * Подгружаем старые значения
         */
        $this->loadOldIdentification($id_pet);

        /*
         * Удаляем старые
         */
        $this->delOldIndents($id_pet);

        /*
         * Создаем новые или обновляем старые (пропуская идентичные)
         */
        $this->createOrUpdateIndents();

        /*
         * Закрываем нарушение идентификации
         */
        (new ViolationModel())->checkAndCancelIdentificationViolation([$id_pet]);

        PetIdentification::getDb()->transaction->commit();
    }

    /**
     * Проверяет уникальности номера чипа
     *
     * @param PetIdentification $model
     * @param string            $temp_uid
     *
     * @return bool
     * @throws \Exception
     */
    protected function checkUniqChipId($model, $temp_uid): bool
    {
        if ($model->id_ident_type !== $this->identification_types_chip_id) {
            return true;
        }

        /** @var PetIdentification $check */
        $check = PetIdentification::find()
            ->where([
                'id_ident_type' => $this->identification_types_chip_id,
                'identification_code' => $model->identification_code
            ])->one();



        if (!empty($check)) {

            /** @var Pets $check_pets_expire */
            $check_pets_expire = Pets::find()
                ->where(['id' => $check->id_pet])
                ->andWhere('id_reg_expire_reason IS NOT NULL')
                ->andWhere('reg_expire_date IS NOT NULL')
                ->one();
            if (!empty($check_pets_expire)) {
                throw new \Exception('Животное ( id '. $check->id_pet .') с указанным чипом снято с учёта');
            }

            /** @var ShelterGuests $check_shelter_guest */
            $check_shelter_guest = ShelterGuests::find()->where(['id_pet' => $check->id_pet])
                ->andWhere(['NOT IN', 'status', ['DEACTIVATED', 'DEPARTURED']])->one();
            if (!empty($check_shelter_guest)) {
                throw new \Exception('Животное ( id '. $check->id_pet .') с указанным чипом находится в приюте');
            }

            throw new \Exception('Животное ( id '. $check->id_pet .') с указанным чипом уже есть в системе');
        }

        return true;
    }

    /**
     * Создает новые или обновляем старые (пропуская идентичные)
     *
     * @throws BadRequestHttpException
     */
    protected function createOrUpdateIndents()
    {
        if (empty($this->new_indents)) {
            return;
        }

        foreach ($this->new_indents as $key => $new_indent) {

            /*
             * Подготавливаем на сохранение как новый
             * В азвисимотси от результата проверок,
             * его могут переписать на уже существующий (с обновленным полем)
             * либо выставить в FALSE, если есть такой же один-в-один
             */
            $for_save = new PetIdentification($new_indent);

            foreach ($this->old_indents as $old_indent) {
                $compare = ($old_indent->id_ident_type === $new_indent['id_ident_type']) &&
                    ($old_indent->identification_code === $new_indent['identification_code']);


                if ($compare == true && $old_indent->main_flag == $new_indent['main_flag']) {
                    // Ничего не изменилось, пропускаем
                    $for_save = false;
                    break;
                } elseif ($compare == true && $old_indent->main_flag != $new_indent['main_flag']) {
                    // надо просто обновить старый
                    $old_indent->main_flag = $new_indent['main_flag'];
                    $for_save = $old_indent;
                }
            }

            // Нечего сохранять - продолжаем разбор
            if (empty($for_save)) {
                continue;
            }

            // Для новой метки стоит проверить ее уникальность в рамках БД
            // (на данный момент тут проверяется только для типа "чип"
            if ($for_save->isNewRecord) {
                $user = \Yii::$app->user->getIdentity();
                $for_save->identif_org = $user->specialist->id_organization;
                $this->checkUniqChipId($for_save, $key);
            }

            if (!$for_save->save()) {
                $errors = $for_save->getErrorSummary(true);
                throw new BadRequestHttpException(empty($errors) ? '[' . $key . '] Ошибка ' : '[' . $key . '] Ошибка ' . implode("\n",
                        array_values($errors)));
            }

            // При добавлении чипа формируем рег. удостоверение
            // https://jira.altarix.ru/browse/VETAIS-3263
            if ($for_save->id_ident_type === $this->identification_types_chip_id) {
                $this->createRegCertificate($for_save->id_pet);
            }
        }
    }

    /**
     * Удаляет старые метки
     *
     * @param $id_pet
     *
     * @return null
     * @throws BadRequestHttpException
     * @throws \Throwable
     */
    protected function delOldIndents($id_pet)
    {
        /*
         * Пытаются удалить все
         */
        if (empty($this->new_indents)) {
            PetIdentification::deleteAll(['id_pet' => $id_pet]);
            return null;
        }

        /*
         * Перебираем старые и те, которых нет в новом списке
         */
        foreach ($this->old_indents as $old_indent) {
            $exist = false;
            foreach ($this->new_indents as $new_indent) {

                // тут нам флаг не важен, ибо отбираются на удаление
                $compare = ($old_indent->id_ident_type === $new_indent['id_ident_type']) &&
                    ($old_indent->identification_code === $new_indent['identification_code']);

                if ($compare) {
                    $exist = true;
                    break;
                }
            }

            /*
             * Удаляем старые значения
             */
            if ($exist == false) {
                if ($old_indent->delete() === false) {
                    throw new BadRequestHttpException('Неизвестная ошибка при удалении идентификационной метки');
                }
            }
        }
    }

    /**
     * Первично валидирем входной массив новых меток
     *
     * @param $id_pet
     * @param $indents
     *
     * @throws BadRequestHttpException
     */
    protected function validateAndPrepareIndentsArray($id_pet, $indents)
    {
        $main_ident = false;
        $check_duplicates = [];

        foreach ($indents as $indent) {
            if (!is_array($indent)) {
                throw new BadRequestHttpException('Ошибочный формат indents');
            }

            if (empty($indent['__TEMP_UID__']) || !is_string($indent['__TEMP_UID__'])) {
                throw new BadRequestHttpException('Ошибочный формат indents: __TEMP_UID__ должен указан и быть строкой');
            }

            $current_key = $indent['__TEMP_UID__'];

            unset($indent['__TEMP_UID__']);
            unset($indent['id']); // может помешать, если попадет

            if (array_key_exists($current_key, $this->new_indents)) {
                throw new BadRequestHttpException('Ошибочный формат indents: повторяющийся __TEMP_UID__');
            }

            if (!array_key_exists('main_flag', $indent) || !is_bool($indent['main_flag'])) {
                throw new BadRequestHttpException('[' . $current_key . '] Ошибочный формат indents: не указан main_flag');
            }

            if (empty($indent['identification_code']) || !is_string($indent['identification_code'])) {
                throw new BadRequestHttpException('Необходимо заполнить поле идентификационный номер');
            }

            if (empty($indent['id_ident_type']) || !is_numeric($indent['id_ident_type'])) {
                throw new BadRequestHttpException('Ошибочный формат indents: id_ident_type должен указан и быть числом');
            }

            if ($main_ident !== false && $indent['main_flag'] == true) {
                throw new BadRequestHttpException('У животного может быть только один главный идентификатор');
            } elseif ($indent['main_flag'] == true) {
                $main_ident = $current_key;
            }

            $indent['id_pet'] = $id_pet;
            $this->new_indents[$current_key] = $indent;
            $check_duplicates[$indent['id_ident_type'] . '_' . $indent['identification_code']] = true;
        }

        if ($main_ident == false) {
            throw new BadRequestHttpException('У животного должен быть главный идентификатор');
        }

        if (count($check_duplicates) != count($this->new_indents)) {
            throw new BadRequestHttpException('Идентификаторы не уникальны');
        }
    }

    /**
     * Проверка на существование и возможность редактирования животного
     *
     * @param $id_pet
     *
     * @throws BadRequestHttpException
     */
    protected function checkPet($id_pet)
    {
        $pet = Pets::findOne($id_pet);

        if (empty($pet)) {
            throw new BadRequestHttpException('Указанное животное не найдено');
        }

        if ($pet->isReadOnly()) {
            throw new BadRequestHttpException('Животное снято с учета. Редактирование запрещено');
        }
    }

    /**
     * Подгружаем "старые" метки
     *
     * @param $id_pet
     */
    protected function loadOldIdentification($id_pet)
    {
        $this->old_indents = PetIdentification::find()
            ->where(['id_pet' => $id_pet])
            ->all();
    }

    /**
     * Создание регистрационного удостоверения
     *
     * @param $id_pet
     */
    private function createRegCertificate($id_pet)
    {
        $pet = Pets::findOne($id_pet);
        if (!empty($pet)) {
            try {
                $model = new RegCertificateModel(['pet' => $pet]);
                $model->createCertificate(true);
            } catch (\Throwable $e) {
                \Yii::error($e->getMessage());
            }
        }
    }
}
