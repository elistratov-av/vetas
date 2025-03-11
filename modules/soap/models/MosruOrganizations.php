<?php

namespace app\modules\soap\models;

use app\models\db\Organizations;

/**
 * This is the model class for table "mosru.organizations".
 *
 * @property OrgTypes $orgType
 * @property Pets[] $pets
 * @property MosruSpecialists[] $specialists
 * @property Visits[] $visits
 * @property OrganizationsServices[] $organizationServices
 * @property Addresses $address
 */
class MosruOrganizations extends Organizations
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'mosru.organizations';
    }

    public static function primaryKey()
    {
        return ['id'];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'id_address'], 'integer'],
            [['name'], 'string'],
            [['type'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'type' => 'Type',
            'id_address' => 'Id Address',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrgType()
    {
        return $this->hasOne(OrgTypes::class, ['id' => 'id_org_type']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSpecialists()
    {
        return $this->hasMany(MosruSpecialists::class, ['id_organization' => 'id']);
    }

    /**
     * Основной номер телефона клиники
     * @return null|string
     */
    public function getTelephone()
    {
        $contact = Contacts::findOne(['entity_id' => $this->id, 'id_contact_type' => [7, 9, 16]]);
        return $contact ? $contact->name : null;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAddress()
    {
        return $this->hasOne(Addresses::class, ['id' => 'id_address']);
    }
}
