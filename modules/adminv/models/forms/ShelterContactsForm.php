<?php

namespace app\modules\adminv\models\forms;

use app\models\db\Contacts;
use app\models\db\ContactTypes;
use app\models\db\Organizations;
use yii\base\InvalidConfigException;
use yii\base\Model;

/**
 * Class ShelterContactsForm
 * @package app\modules\adminv\models\forms
 */
class ShelterContactsForm extends Model
{
    const ENTITY_TYPE_ORGANIZATION = 'organization';
    const ENTITY_TYPE_PET_OWNER = 'pet_owner';

    /**
     * @var string
     */
    public $phone;
    /**
     * @var string
     */
    public $email;
    /**
     * @var int
     */
    public $contact_id_phone;
    /**
     * @var int
     */
    public $contact_id_email;

    /**
     * @var int
     */
    private $id_contact_type_phone;
    /**
     * @var int
     */
    private $id_contact_type_email;
    /**
     * @var int
     */
    private $id_representative_contact_type_phone;
    /**
     * @var int
     */
    private $id_representative_contact_type_email;

    /**
     * @inheritDoc
     */
    public function init()
    {
        $this->id_contact_type_phone = $this->idContactTypePhone();
        $this->id_contact_type_email = $this->idContactTypeEmail();
        $this->id_representative_contact_type_phone = $this->idRepresentativeContactTypePhone();
        $this->id_representative_contact_type_email = $this->idRepresentativeContactTypeEmail();
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            ['phone', 'required'],
            ['phone', 'match', 'pattern' => Contacts::PHONE_REG_EXP, 'message' => 'Телефон должен быть в формате +71234567890'],
            ['email', 'email', 'message' => 'Введенный email имеет ошибочный формат'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return [
            'phone' => 'Телефон',
            'email' => 'Email',
        ];
    }

    /**
     * @param int $id_organization
     * @return \app\modules\adminv\models\forms\ShelterContactsForm
     */
    public function prepare($id_organization = null)
    {
        if (!empty($id_organization)) {
            $phone = $this->findContact($id_organization, $this->id_contact_type_phone, self::ENTITY_TYPE_ORGANIZATION);
            if ($phone !== null) {
                $this->phone = $phone->name;
                $this->contact_id_phone = $phone->id;
            }
            $email = $this->findContact($id_organization, $this->id_contact_type_email, self::ENTITY_TYPE_ORGANIZATION);
            if ($email !== null) {
                $this->email = $email->name;
                $this->contact_id_email = $email->id;
            }
        }

        return $this;
    }

    /**
     * @param int $id_organization
     * @return bool
     */
    public function save($id_organization)
    {
        if (!$this->validate()) {
            return false;
        }

        $result = true;

        /** @var Organizations $organization */
        $organization = Organizations::find()->where(['id' => $id_organization])->one();
        $representativeId = $organization->id_pet_owner;

        if (!empty($this->phone)) {
            $phone = empty($this->contact_id_phone)
                ? new Contacts([
                    'id_contact_type' => $this->id_contact_type_phone,
                    'entity_type' => self::ENTITY_TYPE_ORGANIZATION,
                    'entity_id' => $id_organization,
                ])
                : Contacts::findOne(['id' => $this->contact_id_phone]);
            $phoneRepresentative = $this->findContact($representativeId, $this->id_representative_contact_type_phone, self::ENTITY_TYPE_PET_OWNER)
                ?? new Contacts([
                    'id_contact_type' => $this->id_representative_contact_type_phone,
                    'entity_type' => self::ENTITY_TYPE_PET_OWNER,
                    'entity_id' => $representativeId
                ]);
            $phone->name = $this->phone;
            $phoneRepresentative->name = $this->phone;
            $result = $result && $phone->save() && $phoneRepresentative->save();
        }
        if (!empty($this->email)) {
            $email = empty($this->contact_id_email)
                ? new Contacts([
                    'id_contact_type' => $this->id_contact_type_email,
                    'entity_type' => self::ENTITY_TYPE_ORGANIZATION,
                    'entity_id' => $id_organization,
                ])
                : Contacts::findOne(['id' => $this->contact_id_email]);
            $emailRepresentative = $this->findContact($representativeId, $this->id_representative_contact_type_email, self::ENTITY_TYPE_PET_OWNER)
                ?? new Contacts([
                    'id_contact_type' => $this->id_representative_contact_type_email,
                    'entity_type' => self::ENTITY_TYPE_PET_OWNER,
                    'entity_id' => $representativeId,
                ]);
            $email->name = $this->email;
            $emailRepresentative->name = $this->email;
            $result = $result && $email->save() && $emailRepresentative->save();
        }

        return $result;
    }

    /**
     * @return int
     * @throws \yii\base\InvalidConfigException
     */
    private function idContactTypePhone()
    {
        $type = ContactTypes::findOne([
            'name' => 'Телефон',
            'entity_type' => self::ENTITY_TYPE_ORGANIZATION,
        ]);

        if ($type === null) {
            throw new InvalidConfigException('Не найден тип контакта "Телефон"');
        }

        return $type->id;
    }

    /**
     * @return int
     * @throws \yii\base\InvalidConfigException
     */
    private function idContactTypeEmail()
    {
        $type = ContactTypes::findOne([
            'name' => 'Электронная почта организации',
            'entity_type' => self::ENTITY_TYPE_ORGANIZATION,
        ]);

        if ($type === null) {
            throw new InvalidConfigException('Не найден тип контакта "Электронная почта организации"');
        }

        return $type->id;
    }

    /**
     * @return int
     * @throws InvalidConfigException
     */
    private function idRepresentativeContactTypePhone()
    {
        $type = ContactTypes::findOne([
            'name' => 'Рабочий телефон',
            'entity_type' => self::ENTITY_TYPE_PET_OWNER,
        ]);

        if ($type === null) {
            throw new InvalidConfigException('Не найден тип контакта "Рабочий телефон"');
        }

        return $type->id;
    }

    private function idRepresentativeContactTypeEmail()
    {
        $type = ContactTypes::findOne([
            'name' => 'Электронная почта',
            'entity_type' => self::ENTITY_TYPE_PET_OWNER,
        ]);

        if ($type === null) {
            throw new InvalidConfigException('Не найден тип контакта "Электронная почта"');
        }

        return $type->id;
    }

    /**
     * @param int $id_organization
     * @param int $id_contact_type
     * @param string $entity_type
     * @return \app\models\db\Contacts|null
     */
    private function findContact(int $id_organization, int $id_contact_type, string $entity_type)
    {
        $contact = Contacts::findOne([
            'id_contact_type' => $id_contact_type,
            'entity_type' => $entity_type,
            'entity_id' => $id_organization,
        ]);

        return $contact;
    }
}
