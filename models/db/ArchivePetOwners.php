<?php

namespace app\models\db;

use Yii;

class ArchivePetOwners extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'archive.pet_owners';
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
    public function getFact_address()
    {
        return $this->hasOne(Addresses::class, ['id' => 'id_fact_address']);
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
    public function getFias_addresses()
    {
        return $this->hasOne(FiasAddresses::class, ['id' => 'id_fias_address']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContacts()
    {
        return $this->hasMany(Contacts::class, ['entity_id' => 'id'])
            ->where(['contacts.entity_type' => 'pet_owner']);
    }
}