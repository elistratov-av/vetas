<?php

namespace app\modules\v2\modules\pets\models;

use app\common\components\FileService;
use app\models\db\Contacts;
use app\models\db\ContactTypes;
use app\models\db\Files;
use app\models\db\PetOwners;
use app\models\db\PetsToOwner;
use app\models\db\RegCertificatesHistory;
use app\models\db\Species;
use app\modules\v1\models\FileResource;
use kartik\mpdf\Pdf;
use app\models\db\PetOwnerType;
use app\models\db\RegCertificates;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;

/**
 * Class RegCertificateModel
 * @package app\modules\v2\modules\pets\models
 */
class RegCertificateModel extends Model
{
    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';
    const SCENARIO_DELETE = 'delete';
    const SCENARIO_AUTOCREATION = 'autocreation'; // VETAIS-3263

    /**
     * @var \app\models\db\Pets
     */
    public $pet;
    /**
     * @var \app\models\db\PetOwners
     */
    public $owner;
    /**
     * @var int
     */
    public $id_certificate;
    /**
     * @var \app\models\db\RegCertificates
     */
    public $reg_certificate;

    /**
     * @var array
     */
    public static $allowedSpecies = [
        Species::TECH_NAME_CAT => 'Кошки',
        Species::TECH_NAME_DOG => 'Собаки',
    ];

    /**
     * @var string
     */
    private $identification_code;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!isset($this->pet)) {
            throw new InvalidConfigException();
        }

        $this->owner = $this->findOwner();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                'pet',
                'validatePet',
                'except' => [self::SCENARIO_DEFAULT, self::SCENARIO_DELETE],
            ],
            [
                'pet',
                'validateIdentification',
                'on' => [self::SCENARIO_AUTOCREATION]
            ],
            [
                'owner',
                'validateOwner',
                'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE, self::SCENARIO_AUTOCREATION],
                'except' => [self::SCENARIO_DEFAULT, self::SCENARIO_DELETE],
                'skipOnEmpty' => false,
                'skipOnError' => true,
            ],
            [
                'reg_certificate',
                'validateRegCertificate',
                'skipOnEmpty' => false,
                'skipOnError' => true,
                'except' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE, self::SCENARIO_AUTOCREATION], // для этих сценариев валидация будет вызвана отдельно
            ],
        ];
    }

    /**
     * @param string                          $attribute is the name of the attribute to be validated
     * @param array                           $params    contains the value of [[params]] that you specify when declaring the inline validation rule
     * @param \yii\validators\InlineValidator $validator is a reference to related [[InlineValidator]] object (since 2.0.11)
     * @see \yii\validators\InlineValidator
     */
    public function validateIdentification($attribute, $params, $validator){
        /* @var $ident \app\models\db\PetIdentification */
        $ident = $this->pet->getPet_identification()
            ->andWhere(['main_flag' => true])
            ->limit(1)
            ->one();

        // Идентификация обязательна при автоматическом формировании.
        // https://jira.altarix.ru/browse/VETAIS-3263
        if ($ident === null || empty($ident->identification_code)) {
            $this->addError($attribute, 'Не указаны данные по идентификации животного');
            return;
        }
    }

    /**
     * @param string                          $attribute is the name of the attribute to be validated
     * @param array                           $params    contains the value of [[params]] that you specify when declaring the inline validation rule
     * @param \yii\validators\InlineValidator $validator is a reference to related [[InlineValidator]] object (since 2.0.11)
     * @see \yii\validators\InlineValidator
     */
    public function validatePet($attribute, $params, $validator)
    {
        if ($this->pet->id_species === null || $this->pet->species === null) {
            $this->addError($attribute, 'Не заполнен вид животного');
        }
        if (!in_array($this->pet->species->tech_name, array_keys(self::$allowedSpecies))) {
            $this->addError(
                $attribute,
                'Регистрационное удостоверение можно создавать только для следующих видов животных: '
                . implode(', ', array_values(self::$allowedSpecies))
            );
            return;
        }
        if ($this->pet->id_breed === null || $this->pet->breeds === null) {
            $this->addError($attribute, 'Не заполнена порода животного');
        }
        if (empty($this->pet->name)) {
            $this->addError($attribute, 'Не заполнена кличка животного');
        }
        if (empty($this->pet->sex)) {
            $this->addError($attribute, 'Не заполнен пол животного');
        }
        if (empty($this->pet->birthday)) {
            $this->addError($attribute, 'Не заполнена дата рождения животного');
        }

        /* @var $ident \app\models\db\PetIdentification */
        $ident = $this->pet->getPet_identification()
            ->andWhere(['main_flag' => true])
            ->limit(1)
            ->one();

        if ($ident !== null) {
            $this->identification_code = $ident->identification_code;
        }
    }

    /**
     * @param string                          $attribute is the name of the attribute to be validated
     * @param array                           $params    contains the value of [[params]] that you specify when declaring the inline validation rule
     * @param \yii\validators\InlineValidator $validator is a reference to related [[InlineValidator]] object (since 2.0.11)
     * @see \yii\validators\InlineValidator
     */
    public function validateOwner($attribute, $params, $validator)
    {
        if ($this->owner === null) {
            $this->addError($attribute, 'Формирование сертификата возможно только при наличии у животного владельца');
            return;
        }

        $this->checkOwnerContacts();

        if ($this->owner->fias_addresses === null) {
            $this->addError($attribute, 'Не заполнен адрес владельца животного');
        }
    }

    /**
     * @param string                          $attribute is the name of the attribute to be validated
     * @param array                           $params    contains the value of [[params]] that you specify when declaring the inline validation rule
     * @param \yii\validators\InlineValidator $validator is a reference to related [[InlineValidator]] object (since 2.0.11)
     * @see \yii\validators\InlineValidator
     */
    public function validateRegCertificate($attribute, $params, $validator)
    {
        if ($this->reg_certificate === null && $this->scenario != self::SCENARIO_DELETE) {
            $this->addError(
                $attribute,
                $this->scenario == self::SCENARIO_DEFAULT
                    ? 'Удостоверение для животного с указанным id не найдено'
                    : 'Удостоверение не найдено или ошибка при создании удостоверения'
            );

            return;
        }

        if ($this->scenario == self::SCENARIO_DEFAULT) {
            return;
        }

        // пока уберем совсем
        // if ($this->reg_certificate->file === null) {
        //     $this->addError($attribute, 'Файл удостоверения не найден');
        //     return;
        // }

        if ($this->pet !== null && $this->reg_certificate->id_pet != $this->pet->id) {
            // при методе get здесь может отсутствовать pet (т.к. валидация отключена)
            $this->addError($attribute, 'Удостоверение не принадлежит животному с id ' . $this->reg_certificate->id_pet);
        }

        if ($this->scenario == self::SCENARIO_DELETE) {
            // дальше ничего не проверяем - либо возвращаем либо удаляем
            return;
        }

        if ($this->owner !== null && $this->reg_certificate->id_owner != $this->owner->id) {
            // при методе get здесь может отсутствовать owner (т.к. валидация отключена)
            $this->addError($attribute, 'Удостоверение не принадлежит владельцу с id ' . $this->reg_certificate->id_owner);
        }
    }

    /**
     * @param $id_pets
     * @return array
     */
    public static function getRegCertificates($id_pets)
    {
        return RegCertificates::find()
            ->with('file')
            ->where(['IN', 'id_pet', $id_pets])
            ->asArray()
            ->all();
    }

    /**
     * @param int $id
     * @return array
     */
    public function returnCertificate(int $id = null)
    {
        $this->scenario = self::SCENARIO_DEFAULT;

        $this->reg_certificate = $this->findRegCertificate($this->pet->id, $id, false);

        if ($this->reg_certificate === null) {
            return null;
        }

        // UPD: в /get отключаем валидацию и автоматическое обновление:
        // Юлия, 12:21 2019-01-21
        // ...пока надо вернуться к тому что обновление по требованию.
        // закомментить часть по автоматическое изменение...
        // пусть сами решают когда их обновлять...
        // Юлия, 13:16 2019-01-22
        // ...Необходимо дать возможность получить удостоверение даже если нет каких либо обязательных данных.
        // Текущее сформированное должно быть доступно...

        // if ($this->reg_certificate->to_update === true) {
        //     // данные животного или владельца были изменены, нужно заново сгенерировать удостоверение
        //     return $this->updateCertificate($id);
        // }

        if (!$this->validate()) {
            return false;
        }

        return $this->formatOutput();
    }

    /**
     * @param bool $autocreation false при ручном формировании. true при автоформировании системой. VETAIS-3263
     * @return array
     */
    public function createCertificate($autocreation = false)
    {
        $existing = $this->findRegCertificate($this->pet->id, null, false);
        if (!empty($existing)) {
            $this->addError('reg_certificate', 'У животного уже есть регистрационное удостоверение');

            return false;
        }

        $permission = $this->checkRegPermission();
        if (empty($permission)){
            $this->addError('reg_certificate', 'Организация не может выдавать удостоверения');

            return false;
        }

        if ($autocreation){
            $this->scenario = self::SCENARIO_AUTOCREATION;
        }
        else {
            $this->scenario = self::SCENARIO_CREATE;
        }

        if (!$this->validate()) {
            return false;
        }
        $this->reg_certificate = new RegCertificates();
        if (!$this->fillCertificate() || !$this->reg_certificate->save()) {
            $this->addErrors($this->reg_certificate->getErrors());

            return false;
        }

        if (!$this->generatePdf()) {
            $this->reg_certificate->delete();

            return false;
        }

        return $this->formatOutput();
    }

    /**
     * @param int $id
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function updateCertificate(int $id, string $reason = 'Не указано')
    {
        $this->scenario = self::SCENARIO_UPDATE;

        if ($this->reg_certificate === null) {
            // может быть уже получено, если мы пришли сюда из get
            $this->reg_certificate = $this->findRegCertificate($this->pet->id, $id, false);
        }
        if ($this->reg_certificate === null) {
            return null;
        }

        $permission = $this->checkRegPermission();
        if (empty($permission)){
            $this->addError('reg_certificate', 'Организация не может выдавать удостоверения');

            return false;
        }

        if (!$this->validate()) {
            return false;
        }

        $snapshot = $this->reg_certificate->getAttributes();
        if (!$this->fillCertificate() || !$this->reg_certificate->save()) {
            $this->addErrors($this->reg_certificate->getErrors());

            return false;
        }

        if (!$this->generatePdf()) {
            $this->reg_certificate->delete();

            return false;
        }

        $this->recordChange($reason, $snapshot, RegCertificatesHistory::ACTION_UPDATE);

        $this->reg_certificate->refresh();

        return $this->formatOutput();
    }

    /**
     * @param int $id
     * @return bool
     */
    public function deleteCertificate(int $id, string $reason = 'Не указано')
    {
        $this->scenario = self::SCENARIO_DELETE;

        $this->reg_certificate = $this->findRegCertificate($this->pet->id, $id, false);

        if ($this->reg_certificate === null) {
            // просто возвращаем что все OK
            return true;
        }

        $permission = $this->checkRegPermission();
        if (empty($permission)){
            $this->addError('reg_certificate', 'Организация не может выдавать удостоверения');

            return false;
        }

        if (!$this->validate()) {
            return false;
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {
            $snapshot = $this->reg_certificate->getAttributes();
            if (!$this->reg_certificate->delete()) {
                $this->addError('reg_certificate', 'Ошибка при удалении удостоверения');
                $transaction->rollBack();

                return false;
            }
            if ($this->reg_certificate->file !== null && !$this->reg_certificate->file->delete()) {
                $this->addError('reg_certificate', 'Ошибка при удалении файла удостоверения');
                $transaction->rollBack();

                return false;
            }
            $this->recordChange($reason, $snapshot, RegCertificatesHistory::ACTION_DELETE);
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        return true;
    }

    /**
     * @return array
     */
    private function formatOutput(): array
    {
        if ($this->reg_certificate->file === null) {
            return array_merge($this->reg_certificate->toArray(), ['file' => null]);
        }

        return $this->reg_certificate->toArray([], ['file']);
    }

    /**
     * @return \yii\db\ActiveRecord|null
     * @throws \yii\base\InvalidConfigException
     */
    private function findOwner()
    {
        if (empty($this->pet->owners)) {
            return null;
        }

        $id_owner_type = PetOwnerType::findOwnerTypeId();
        if (empty($id_owner_type)) {
            throw new InvalidConfigException('Не удалось найти значение справочника pet_owner_type для владельца животного');
        }

        $owners = PetOwners::find()
            ->innerJoin(PetsToOwner::tableName(), PetsToOwner::tableName() . '.id_owner = ' . PetOwners::tableName() . '.id')
            ->select([PetOwners::tableName() . '.*', PetsToOwner::tableName() . '.id_owner_type'])
            ->where(['id_pet' => $this->pet->id])
            ->andWhere(['id_owner_type' => $id_owner_type])
            ->all();

        if (empty($owners)) {
            return null;
        }

        if (count($owners) > 1) {
            throw new InvalidConfigException('У животного должен быть только один основной владелец');
        }

        return $owners[0];
    }

    /**
     * @param int  $id_pet
     * @param int  $id
     * @param bool $throw
     * @return \app\models\db\RegCertificates|\app\models\db\RegCertificates[]|null
     * @throws \yii\base\InvalidConfigException
     */
    private function findRegCertificate(int $id_pet, int $id = null, $throw = false)
    {
        $condition = ['id_pet' => $id_pet];

        if ($id !== null) {
            $condition['id'] = $id;
            $model = RegCertificates::findOne($condition);
        } else {
            $models = RegCertificates::findAll($condition);
            $count = count($models);
            if ($count == 1) {
                $model = array_shift($models);
            } else {
                if ($throw === true) {
                    throw new InvalidConfigException(
                        $count == 0
                            ? 'Удостоверение для животного с указанным id не найдено'
                            : 'У животного должно быть только одно удостоверение'
                    );
                }
                $model = ($count == 0) ? null : $models;
            }
        }

        return $model;
    }

    /**
     * @return bool
     */
    private function fillCertificate()
    {
        if ($this->hasErrors()) {
            return false;
        }

        $contacts = $this->findOwnerContacts();
        if (empty($contacts)) {
            $this->addError('reg_certificate', 'Заполнены не все контактные данные владельца животного');

            return false;
        }

        $phone = ArrayHelper::getValue($contacts, ContactTypes::TYPE_PHONE);
        $mail = ArrayHelper::getValue($contacts, ContactTypes::TYPE_EMAIL);

        if (empty($phone)) {
            $this->addError('reg_certificate', 'У владельца животного отсутствует номер телефона');

            return false;
        }

        $updating = $this->scenario == self::SCENARIO_UPDATE;
        $number = ($updating) ? $this->reg_certificate->number : $this->generateRegNumber();
        if ($number === false) {
            return false;
        }

        $attributes = [
            'date' => date('Y-m-d'),
            'number' => $number,
            'id_pet' => $this->pet->id,
            'id_owner' => $this->owner->id,
            'phone' => $phone['id'],
            'mail' => empty($mail) ? null : $mail['id'],
            'to_update' => false,
        ];
        if ($updating) {
            // фикс - TimestampBehavior не отрабатывает, если не менялись остальные атрибуты
            $attributes['updated_at'] = date('Y-m-d H:i:s');
        }

        if (!$this->reg_certificate->load($attributes, '')) {
            $this->addError('reg_certificate', 'Заполнены не все данные удостоверения');

            return false;
        }

        $this->validateRegCertificate('reg_certificate', [], null);

        return !$this->hasErrors();
    }

    /**
     * @return \app\modules\v1\models\FileResource|bool
     * @throws \Mpdf\MpdfException
     * @throws \yii\base\Exception
     */
    private function generatePdf()
    {
        if ($this->hasErrors()) {
            return false;
        }

        if ($this->owner->fias_addresses) {
            $address_city = $this->owner->fias_addresses->city;
            $address_district = '';
            $address_street = $this->owner->fias_addresses->street;
            $address_house = $this->owner->fias_addresses->house;
            $address_room = $this->owner->fias_addresses->room;
        } else {
            $address_city = '';
            $address_district = '';
            $address_street = '';
            $address_house = '';
            $address_room = '';
        }

        $phone = Contacts::findOne(['id' => $this->reg_certificate->phone]);
        $mail = empty($this->reg_certificate->mail) ? null : Contacts::findOne(['id' => $this->reg_certificate->mail]);
        $date = date_create_from_format('Y-m-d', $this->reg_certificate->date)->format('d.m.Y');
        $pet_birthday = date_create_from_format('Y-m-d', $this->pet->birthday)->format('d.m.Y');

        $data = [
            ['x' => 20, 'y' => 27, 'text' => $date],
            ['x' => 60, 'y' => 27, 'text' => $this->reg_certificate->number],
            ['x' => 62, 'y' => 47, 'text' => $this->pet->name],
            ['x' => 62, 'y' => 55, 'text' => $this->pet->species->name],
            ['x' => 62, 'y' => 62, 'text' => $this->pet->breeds->name],
            ['x' => 62, 'y' => 70, 'text' => $pet_birthday],
            ['x' => 62, 'y' => 77, 'text' => $this->pet->sex],
            ['x' => 62, 'y' => 85, 'text' => $this->identification_code],
            ['x' => 62, 'y' => 101, 'text' => $this->owner->fullname],
            ['x' => 62, 'y' => 107, 'text' => (empty($mail) ? '' : $mail->name)],
            ['x' => 62, 'y' => 114, 'text' => $phone->name],
            ['x' => 62, 'y' => 129, 'text' => $address_city],
            ['x' => 62, 'y' => 133, 'text' => $address_district],
            ['x' => 62, 'y' => 137, 'text' => $address_street],
            ['x' => 62, 'y' => 142, 'text' => $address_house],
            ['x' => 62, 'y' => 146, 'text' => $address_room],
        ];

        $sourceFile = Yii::getAlias('@app/common/components/pdfGenerator/template/views/reg_certificate_template.pdf');

        $pdf = new Pdf([
            // set to use core fonts only
            'mode' => Pdf::MODE_UTF8,
            // A4 paper format
            'format' => Pdf::FORMAT_A4,
            // portrait orientation
            'orientation' => Pdf::ORIENT_LANDSCAPE,
            // stream to browser inline
            'destination' => Pdf::DEST_STRING,
            'marginLeft' => 10,
            'marginRight' => 10,
            'marginTop' => 10,
            'defaultFont' => 'dejavusans',
        ]);

        $pdf->defaultFontSize = 9;
        $fpdf = $pdf->getApi();
        $fpdf->SetImportUse();
        $fpdf->SetSourceFile($sourceFile);
        $tpl = $fpdf->ImportPage(1);
        $fpdf->addPage();
        $fpdf->useTemplate($tpl);

        foreach ($data as $item) {
            $fpdf->SetXY($item['x'], $item['y']);
            //$fpdf->WriteCell(60, 0, $item['text']);
            $fpdf->AutosizeText($item['text'], 50, '', '', 10);
        }

        $tpl = $fpdf->ImportPage(2);

        $fpdf->addPage();
        $fpdf->useTemplate($tpl);

        $dir = Yii::getAlias('@webroot') . '/upload/pdf';
        FileHelper::createDirectory($dir);
        $filename = Yii::$app->getSecurity()->generateRandomString(32) . '.pdf';

        $pdf->getApi()->output($dir . '/' . $filename, \Mpdf\Output\Destination::FILE);

        /** @var FileService $fileService */
        $fileService = \Yii::$app->fileService;
        $path = $dir . DIRECTORY_SEPARATOR . $filename;
        $hash = $fileService->generateHash($filename);

        $type = 'reg_certificate';

        try {
            $this->deleteFiles($type);
            $fileResource = new FileResource();
            $fileResource->hash = $hash;
            $fileResource->path = '/upload/pdf/' . $filename;
            $fileResource->name = $filename;
            $fileResource->entity_id = $this->reg_certificate->id;
            $fileResource->entity_type = $type;
            $fileResource->save();
            $fileService->attach($fileResource);
        } catch (\Throwable $e) {
            $fileService->repository->delete($path);
            $this->addError('reg_certificate', "Ошибка при сохранении файла: {$e->getMessage()}");

            return false;
        }

        return $fileResource;
    }

    /**
     * @return bool|string
     */
    public function generateRegNumber()
    {
        /** @var \app\common\models\UserModel $user */
        $user = \Yii::$app->user->getIdentity();
        if ($user->specialist === null) {
            $this->addError('reg_certificate', 'Пользователю ' . $user->login . ' не назначен специалист');

            return false;
        }

        if ($user->specialist->organization === null) {
            $this->addError('reg_certificate', 'Пользователю ' . $user->login . ' не назначена организация');

            return false;
        }

        $org_reg_number = $user->specialist->organization->reg_number;
        if ($org_reg_number === null) {
            $this->addError('reg_certificate', 'Данной организации не присвоен регистрационный номер. Выдача регистрационного удостоверения невозможна.');

            return false;
        }

        $lastRegId = (new Query())
            ->select('id')
            ->from(RegCertificates::tableName())
            ->orderBy(['id' => SORT_DESC])
            ->limit(1)
            ->scalar();

        return str_pad($org_reg_number, 6, '0', STR_PAD_LEFT) . str_pad((int)($lastRegId + 1), 7, '0', STR_PAD_LEFT);
    }

    /**
     * @param string $type
     */
    private function deleteFiles(string $type)
    {
        $files = Files::find()
            ->where([
                'entity_id' => $this->reg_certificate->id,
                'entity_type' => $type,
            ])
            ->all();
        if (!empty($files)) {
            foreach ($files as $file) {
                try {
                    $file->delete();
                } catch (\Throwable $e) {
                    Yii::error('Failed to delete pdf file for $type ' . $this->reg_certificate->id . "\n" . $e->getMessage());
                }
            }
        }
    }

    /**
     * @return bool
     */
    private function checkOwnerContacts()
    {
        // Юлия, 12:00 2019-01-21
        // ...почту владельца надо бы сделать необязательной...
        $contactTypes = [
            ContactTypes::TYPE_PHONE => 'номер телефона',
            // ContactTypes::TYPE_EMAIL => 'адрес электронной почты',
        ];

        $contacts = $this->findOwnerContacts();
        foreach ($contactTypes as $type => $name) {
            if (!array_key_exists($type, $contacts)) {
                $this->addError('owner', 'У владельца животного отсутствует основной ' . $name);

                return false;
            }
        }

        return true;
    }

    /**
     * @return array
     */
    private function findOwnerContacts()
    {
        $contactTypes = [
            ContactTypes::TYPE_PHONE,
            ContactTypes::TYPE_EMAIL,
        ];

        $records = (new Query())
            ->select(['cnt.id', 'cnt.name', 'cnt.id_contact_type', 'ct.type'])
            ->from(Contacts::tableName() . ' cnt')
            ->leftJoin(ContactTypes::tableName() . ' ct', 'ct.id = cnt.id_contact_type')
            ->where(['=', 'cnt.entity_id', $this->owner->id])
            ->andWhere(['=', 'cnt.main_flag', true])
            ->andWhere(['in', 'ct.type', $contactTypes])
            ->all();

        return empty($records) ? [] : ArrayHelper::index($records, 'type');
    }

    /**
     * Проверка на разрешение выдачи удостоверений залогиненого пользователя
     * @see https://jira.altarix.ru/browse/VETAIS-3263
     * @return bool
     */
    private function checkRegPermission()
    {
        /** @var $user \app\common\models\UserModel */
        $user = Yii::$app->user->getIdentity();

        if ($user->specialist === null || empty($user->specialist->id_organization) || $user->specialist->organization === null) {
            return false;
        }

        if ($user->specialist->organization->pet_registration === true){
            return true;
        }

        return false;
    }

    private function recordChange(string $reason,  array $snapshot, string $action){
        $record = new RegCertificatesHistory([
            'id_reg'      => $this->reg_certificate->id,
            'id_pet'      => $this->reg_certificate->id_pet,
            'reason'      => $reason,
            'action'      => $action,
            'snapshot'    => $snapshot,
        ]);
        $record->save();
    }
}
