<?php

namespace app\models\db;

use app\common\components\inform\SpkService;
use app\common\validators\FilterUcwordsValidator;
use app\common\validators\FullTrimValidator;
use app\common\validators\InnValidator;
use app\common\validators\LessThanNowValidator;
use app\common\validators\OgrnValidator;
use app\common\validators\OnlyNumbersValidator;
use app\common\validators\SnilsValidator;
use app\models\db\elk\ElkOwners;
use app\models\db\elk\ElkPets;
use app\modules\v1\models\FileResource;
use InvalidArgumentException;
use yii\db\Expression;
use yii\db\Query;
use yii\web\BadRequestHttpException;

/**
 * This is the model class for table "pet_owners".
 *
 * @property int $id
 * @property string $f_fio                        Фамилия
 * @property string $i_fio                        Имя
 * @property string $o_fio                        Отчество
 * @property string $jur_name                     Название юр.лица
 * @property string $inn                          ИНН
 * @property string $ogrn                         ОГРН
 * @property string $birthday                     Дата рождения
 * @property string $snils                        СНИЛС
 * @property string $passport_number              Номер паспорта
 * @property string $passport_series              Серия паспорта
 * @property string $passport_issue_date          Дата выдачи паспорта
 * @property string $passport_issuer              Наименование организации выдавшей паспорт
 * @property int $id_address                   Ссылка на адрес
 * @property int $created_by                   Автор добавления (id пользователя)
 * @property int $updated_by                   Автор последнего изменения (id пользователя)
 * @property string $created_at                   Дата создания
 * @property string $updated_at                   Дата изменения
 * @property int $id_fact_address
 * @property bool $is_legal
 * @property string $fullname
 * @property int $id_area
 * @property int $id_district
 * @property int $id_fias_address              Ссылка на адрес ФИАС, таблица fias_address
 * @property int $id_fact_fias_address         Ссылка на адрес ФИАС, таблица fias_address
 * @property bool $addresses_is_equal           Флаг: Адрес регистрации и фактический адрес совпадают
 * @property bool $is_deleted                   Флаг: пользователь удален
 * @property bool $entrepreneur                 Флаг: индивидуальный предприниматель
 * @property string $sso_id                       SsoId владельца в ЕЛК
 * @property bool $is_main                      Признак основной записи
 * @property int $id_main_owner                ID основной записи
 * @property string $duble_validation             Дата проведения проверки на дубли
 * @property string $description
 * @property int|null $id_pet_owner_tmp
 *
 * @property Addresses $address
 * @property Addresses $factAddress
 * @property FiasAddresses $fact_fias_addresses
 * @property FiasAddresses $fias_addresses
 * @property PetsToOwner[] $petsToOwners
 * @property Pets[] $pets
 * @property RegCertificates[] $regCertificates
 * @property Visits[] $visits
 * @property Contacts[] $contacts
 * @property Contacts[] $phoneContacts
 * @property Contacts $phoneMainContact
 * @property Contacts[] $emailContacts
 * @property Contacts[] $subscriptionContacts
 * @property PetOwners[] $duplicates
 * @property PetOwnersLinkHistory[] $linkHistoryMain
 * @property PetOwnersLinkHistory[] $linkHistoryDuplicate
 * @property TmpPetOwners $tmpOwner
 */
class PetOwners extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'public.pet_owners';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['f_fio', 'i_fio', 'o_fio', 'description'], 'filter', 'filter' => 'trim'],
            [['f_fio', 'i_fio'], 'required'],
            [['f_fio'], 'string', 'max' => 150],
            [['i_fio', 'o_fio'], 'string', 'max' => 50],
            [['f_fio', 'i_fio', 'o_fio', 'jur_name'], FullTrimValidator::class],
            [['f_fio', 'i_fio', 'o_fio'], FilterUcwordsValidator::class],
            [['birthday', 'created_at', 'updated_at'], 'safe'],
            [['id_address', 'created_by', 'updated_by', 'id_fact_address', 'id_area', 'id_district', 'id_fias_address', 'id_fact_fias_address'], 'default', 'value' => null],
            [['entrepreneur', 'addresses_is_equal'], 'default', 'value' => false],
            [['id_address', 'created_by', 'updated_by', 'id_fact_address', 'id_area', 'id_district', 'id_fias_address', 'id_fact_fias_address'], 'integer'],
            [['is_legal', 'is_deleted', 'entrepreneur', 'addresses_is_equal'], 'boolean'],
            [['f_fio', 'jur_name'], 'string', 'max' => 150],
            [['i_fio', 'o_fio'], 'string', 'max' => 50],
            [['ogrn'], 'string', 'min' => 13, 'max' => 13],
            [['snils'], 'string', 'min' => 11, 'max' => 11],
            [['passport_number'], 'string', 'min' => 6, 'max' => 6],
            [['passport_series'], 'string', 'min' => 4, 'max' => 4],
            [['passport_number', 'passport_series'], OnlyNumbersValidator::class],
            [['passport_issuer'], 'string', 'max' => 150],
            [['passport_number'], 'unique', 'targetAttribute' => ['passport_number', 'passport_series']],
            [['passport_issue_date'], 'date', 'format' => 'php:Y-m-d'],
            [['passport_issue_date'], LessThanNowValidator::class],
            [['passport_issuer'], 'string'],
            // Все поля паспорта обязательны если хотя бы одно поле паспорта заполнено
            [['passport_number', 'passport_series', 'passport_issue_date', 'passport_issuer'], 'required', 'when' => function ($model) {
                return !empty($model->passport_number)
                    || !empty($model->passport_series)
                    || !empty($model->passport_issue_date)
                    || !empty($model->passport_issuer);
            }],
            [['fullname'], 'string', 'max' => 255],
            [['description'], 'string', 'max' => 400],
            [['id_address'], 'exist', 'skipOnError' => true, 'targetClass' => Addresses::class, 'targetAttribute' => ['id_address' => 'id']],
            [['id_fact_address'], 'exist', 'skipOnError' => true, 'targetClass' => Addresses::class, 'targetAttribute' => ['id_fact_address' => 'id']],
            [['id_fias_address'], 'exist', 'skipOnError' => true, 'targetClass' => FiasAddresses::class, 'targetAttribute' => ['id_fias_address' => 'id']],
            [['id_fact_fias_address'], 'exist', 'skipOnError' => true, 'targetClass' => FiasAddresses::class, 'targetAttribute' => ['id_fact_fias_address' => 'id']],
            [
                ['jur_name', 'ogrn', 'inn'],
                'required',
                'when' => function ($model) {
                    return !empty($model->is_legal);
                },
                'enableClientValidation' => false,
            ],
            ['jur_name', function ($attribute, $params, $validator) {
                if ($this->is_legal == false && $this->jur_name !== null) {
                    $this->addError($attribute, "Поле jur_name можно заполнить только для юр лиц");
                }
            }],
            [
                ['inn'],
                'required',
                'when' => function ($model) {
                    return !empty($model->entrepreneur);
                },
                'enableClientValidation' => false,
            ],
            ['snils', function ($attribute, $params, $validator) {
                if (($this->isNewRecord || $this->isAttributeChanged('snils') && $this->is_deleted !== true)) {
                    $check = self::find()
                        ->where([
                            'AND',
                            ['snils' => $this->snils],
                            ['is_deleted' => false],
                        ])->exists();
                    if ($check) {
                        $this->addError($attribute, "СНИЛС уже зарегистрирован в системе");
                    }
                }
            }],
            ['snils', SnilsValidator::class, 'skipOnEmpty' => true, 'skipOnError' => false],
            ['ogrn', OgrnValidator::class, 'skipOnEmpty' => true, 'skipOnError' => false],
            ['inn', InnValidator::class, 'skipOnEmpty' => true, 'skipOnError' => false],
            ['sso_id', 'safe'],
            [['is_main', 'id_main_owner', 'duble_validation'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        // Шлют "", срабатывает ограничение в БД на uniq
        if (empty($this->snils)) {
            $this->snils = null;
        }

        return parent::beforeSave($insert);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'f_fio' => 'Фамилия',
            'i_fio' => 'Имя',
            'o_fio' => 'Отчество',
            'jur_name' => 'Название юр.лица',
            'inn' => 'ИНН',
            'ogrn' => 'ОГРН',
            'birthday' => 'Дата рождения',
            'snils' => 'СНИЛС',
            'id_address' => 'Ссылка на адрес',
            'created_by' => 'Автор добавления (id пользователя)',
            'updated_by' => 'Автор последнего изменения (id пользователя)',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата изменения',
            'id_fact_address' => 'Id Fact Address',
            'is_legal' => 'Is Legal',
            'fullname' => 'Fullname',
            'id_area' => 'Id Area',
            'id_district' => 'Id District',
            'id_fias_address' => 'Ссылка на адрес ФИАС, таблица fias_address',
            'id_fact_fias_address' => 'Ссылка на адрес ФИАС, таблица fias_address',
            'is_deleted' => 'Флаг: пользователь удален',
            'addresses_is_equal' => 'Флаг: aдрес регистрации и фактический адрес совпадают',
            'entrepreneur' => 'Флаг: индивидуальный предприниматель',
            'description' => 'Описание',
            'passport_number' => 'Номер паспорта',
            'passport_series' => 'Серия паспорта',
            'passport_issue_date' => 'Дата выдачи паспорта',
            'passport_issuer' => 'Наименование организации выдавшей паспорт',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAddress()
    {
        return $this->hasOne(Addresses::class, ['id' => 'id_address']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFact_address()
    {
        return $this->hasOne(Addresses::class, ['id' => 'id_fact_address']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFias_addresses()
    {
        return $this->hasOne(FiasAddresses::class, ['id' => 'id_fias_address']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFact_fias_addresses()
    {
        return $this->hasOne(FiasAddresses::class, ['id' => 'id_fact_fias_address']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPetsToOwners()
    {
        return $this->hasMany(PetsToOwner::class, ['id_owner' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPets()
    {
        return $this->hasMany(Pets::class, ['id' => 'id_pet'])->viaTable('pets_to_owner', ['id_owner' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRegCertificates()
    {
        return $this->hasMany(RegCertificates::class, ['id_owner' => 'id']);
    }


    /**
     * @return ActiveQuery
     */
    public function getFiles()
    {
        return $this->hasMany(FileResource::class, ['entity_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVisits()
    {
        return $this->hasMany(Visits::class, ['id_owner' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContacts()
    {
        return $this->hasMany(Contacts::class, ['entity_id' => 'id'])
            ->where(['contacts.entity_type' => 'pet_owner']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPhoneContacts()
    {
        return $this->hasMany(Contacts::class, ['entity_id' => 'id'])
            ->where([
                'and',
                ['contacts.entity_type' => 'pet_owner'],
                ['in', 'id_contact_type', (new Query())->select('id')->from(ContactTypes::tableName())->where(['type' => ContactTypes::TYPE_PHONE])],
            ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPhoneMainContact()
    {
        return $this->hasOne(Contacts::class, ['entity_id' => 'id'])
            ->where([
                'and',
                ['contacts.entity_type' => 'pet_owner'],
                ['in', 'id_contact_type', (new Query())->select('id')->from(ContactTypes::tableName())->where(['type' => ContactTypes::TYPE_PHONE])],
            ])
            ->orderBy([
                'main_flag' => SORT_DESC,
                'created_at' => SORT_DESC
            ])
            ->limit(1);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEmailContacts()
    {
        return $this->hasMany(Contacts::class, ['entity_id' => 'id'])
            ->where([
                'and',
                ['contacts.entity_type' => ContactTypes::ENTITY_TYPE_PET_OWNER],
                ['in', 'id_contact_type', (new Query())->select('id')->from(ContactTypes::tableName())->where(['type' => ContactTypes::TYPE_EMAIL])],
            ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEmailMainContact()
    {
        return $this->hasOne(Contacts::class, ['entity_id' => 'id'])
            ->where([
                'and',
                ['contacts.entity_type' => ContactTypes::ENTITY_TYPE_PET_OWNER],
                ['in', 'id_contact_type', (new Query())->select('id')->from(ContactTypes::tableName())->where(['type' => ContactTypes::TYPE_EMAIL])],
            ])
            ->orderBy([
                'main_flag' => SORT_DESC,
                'created_at' => SORT_DESC
            ])
            ->limit(1);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubscriptionContacts()
    {
        return $this->hasMany(Contacts::class, ['entity_id' => 'id'])
            ->joinWith(['subscriptions'])
            ->where(['contacts.entity_type' => 'pet_owner'])
            ->andWhere(['subscriptions.subscribed' => true]);
    }

    /**
     * @return PetOwnersLinkHistory
     */
    public function getLinkHistoryMain()
    {
        return $this->hasMany(PetOwnersLinkHistory::class, ['id_pet_owner_main' => 'id']);
    }

    /**
     * @return PetOwnersLinkHistory
     */
    public function getLinkHistoryDuplicate()
    {
        return $this->hasMany(PetOwnersLinkHistory::class, ['id_pet_owner_duplicate' => 'id']);
    }

    /**
     * @return bool
     * @throws \app\common\components\inform\InformException
     */
    public function hasSubscriptions()
    {
        /** @var \app\models\db\Contacts[] $contacts */
        $contacts = Contacts::find()
            ->joinWith(['contactType'])
            ->where(['contacts.entity_id' => $this->id])
            ->andWhere(['contacts.entity_type' => 'pet_owner'])
            ->andWhere(['contact_types.type' => [ContactTypes::TYPE_PHONE, ContactTypes::TYPE_EMAIL]])
            ->all();

        if (empty($contacts)) {
            return false;
        }

        /** @var SpkService $spkService */
        $spkService = \Yii::$app->spkService;
        foreach ($contacts as $contact) {
            $subscriptions = $spkService->getSubscriptionsForContact($contact->getTypeForInformation(), $contact->name, $this->sso_id);
            if (!empty($subscriptions)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string
     */
    public function getNameForInformation()
    {
        return $this->i_fio . ' ' . $this->o_fio;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDuplicates()
    {
        return $this->hasMany(ArchivePetOwners::class, ['result_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAgreement_pers()
    {
        return $this->hasOne(Agreements::class, ['id_pet_owner' => 'id'])
            ->andOnCondition(['is_agree' => true])
            ->andOnCondition(['id_type' => AgreementTypes::PD_PROCESSING]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTmpOwner()
    {
        return $this->hasOne(TmpPetOwners::class, ['id' => 'id_pet_owner_tmp']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getElkOwner()
    {
        return $this->hasOne(ElkOwners::class, ['id' => 'id_owner']);
    }

        /**
     * @return \yii\db\ActiveQuery
     */
    public function getElkPets()
    {
        return $this->hasMany(ElkPets::class, ['id' => 'id_pet_owner']);
    }

    /**
     * Переопределяет родительский метод для подмены имени в случае наличия временных данных
     *
     * {@inheritdoc}
     */
    public function __get($name)
    {
        $maskedAttrs = [
            'f_fio',
            'i_fio',
            'o_fio',
            'fullname',
            'snils',
            'birthday',
        ];
        if (!in_array($name, $maskedAttrs, false)) {
            return parent::__get($name);
        }

        if (!$tmpOwner = $this->tmpOwner) {
            return parent::__get($name);
        }

        return $tmpOwner->{$name};
    }

    public function create(
        $i_fio,
        $o_fio,
        $f_fio,
        $address = null
    ): int {
        $owner = new PetOwners();

        $owner->i_fio = $i_fio;
        $owner->o_fio = $o_fio;
        $owner->f_fio = $f_fio;
        $owner->id_fias_address = $address;

        if (!$owner->save()) {
            $errors = $owner->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при создании владельца' : implode("\n", array_values($errors)));
        }

        return $owner->id;
    }

    /**
     * @throws BadRequestHttpException
     */
    public function edit(
        $id_owner,
        $i_fio = null,
        $o_fio = null,
        $f_fio = null,
        $address = null
    ) {
        $owner = PetOwners::findOne(['id' => $id_owner]);
        if (!empty($owner)) {
            $owner->i_fio = $i_fio ?? $owner->i_fio;
            $owner->o_fio = $o_fio ?? $owner->o_fio;
            $owner->f_fio = $f_fio ?? $owner->f_fio;
            $owner->id_fias_address = $address ?? $owner->id_fias_address;
            if (!$owner->save()) {
                $errors = $owner->getErrorSummary(true);
                throw new BadRequestHttpException(empty($errors) ? 'Ошибка при модификации владельца' : implode("\n", array_values($errors)));
            }
        }
    }


    /**
     * Проверяет, есть ли хотя бы один общий контакт между двумя объектами PetOwners.
     *
     * @param PetOwners $owner1 Первая модель PetOwners.
     * @param PetOwners $owner2 Вторая модель PetOwners.
     * @return bool true, если есть хотя бы один общий контакт, иначе false.
     * @throws InvalidArgumentException
     */
    public static function haveCommonContacts(PetOwners $owner1, PetOwners $owner2): bool
    {
        if (!$owner1->isRelationPopulated('contacts') || !$owner2->isRelationPopulated('contacts')) throw new InvalidArgumentException('Обе модели должны быть загружены с контактами.');
        if (!$owner1->isRelationPopulated('visits') || !$owner2->isRelationPopulated('visits')) throw new InvalidArgumentException('Обе модели должны быть загружены с визитами.');
        if (!isset($owner1->sso_id) && $owner1->sso_id !== null || !isset($owner2->sso_id) && $owner2->sso_id !== null) throw new InvalidArgumentException('Обе модели должны иметь поле sso_id.');

        $extractContactsFromVisits = function ($owner) {
            $contacts = [];
            $pattern = '/\d+/';
            foreach ($owner->visits as $visit) {
                if (str_contains($visit->description, 'Контактный номер:')) {
                    preg_match_all($pattern, $visit->description, $matches);
                    $contacts = array_merge($contacts, $matches[0]);
                }
            }
            return $contacts;
        };

        $normalizeContact = function ($contact) {
            $contact = preg_replace('/^(\+7|8)/', '', $contact);
            return str_replace(' ', '', $contact);
        };

        $contacts1 = $owner1->sso_id != 'unauthorized' ? array_column($owner1->contacts, 'name') : $extractContactsFromVisits($owner1);
        $contacts2 = $owner2->sso_id != 'unauthorized' ? array_column($owner2->contacts, 'name') : $extractContactsFromVisits($owner2);

        $contacts1 = array_map($normalizeContact, $contacts1);
        $contacts2 = array_map($normalizeContact, $contacts2);

        foreach ($contacts1 as $contact) {
            if (in_array($contact, $contacts2)) return true;
        }

        return false;
    }



    /**
     * Извлекает и нормализует контакты для данного владельца.
     *
     * @return array Массив нормализованных контактов.
     * @throws InvalidArgumentException
     */
    public function extractContacts(): array
    {
        if (!$this->isRelationPopulated('contacts') || !$this->isRelationPopulated('visits')) throw new InvalidArgumentException('Модель должна быть загружена с контактами и визитами.');
        $contacts = $this->sso_id != 'unauthorized' ? array_column($this->contacts, 'name') : $this->extractContactsFromVisits();
        return array_unique(array_map([$this, 'normalizeContact'], $contacts));
    }


    /**
     * Извлекает номера телефонов из визитов, если sso_id 'unauthorized'.
     *
     * @return array Массив извлеченных номеров телефонов.
     */
    private function extractContactsFromVisits(): array
    {
        $contacts = [];
        foreach ($this->visits as $visit) {
            if (str_contains($visit->description, 'Контактный номер:')) {
                preg_match_all('/\d+/', $visit->description, $matches);
                $contacts = array_merge($contacts, $matches[0]);
            }
        }
        return $contacts;
    }


    /**
     * Нормализует контактный номер (удаляет префиксы и пробелы).
     *
     * @param string $contact Контактный номер.
     * @return string Нормализованный контактный номер.
     */
    private function normalizeContact(string $contact): string
    {
        $contact = preg_replace('/^(\+7|8)/', '', $contact);
        return str_replace(' ', '', $contact);
    }
}
