<?php

namespace app\modules\elk\models\db;

use app\common\validators\FilterUcwordsValidator;
use app\common\validators\FullTrimValidator;
use app\common\validators\SnilsValidator;
use app\models\db\Contacts;
use app\models\db\ContactTypes;

class PetOwners extends \app\models\db\PetOwners
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['f_fio', 'i_fio', 'o_fio'], 'filter', 'filter' => 'trim'],
            [['f_fio', 'i_fio'], 'required'],
            [['f_fio', 'i_fio', 'o_fio'], FullTrimValidator::class],
            [['f_fio', 'i_fio', 'o_fio'], FilterUcwordsValidator::class],
            [['birthday', 'created_at', 'updated_at'], 'safe'],
            [['id_address', 'created_by', 'updated_by', 'id_fact_address'], 'default', 'value' => null],
            [['id_address', 'created_by', 'updated_by', 'id_fact_address'], 'integer'],
            [['is_legal'], 'boolean'],
            ['f_fio', 'string', 'max' => 150],
            ['i_fio', 'string', 'max' => 50],
            ['o_fio', 'string', 'max' => 50],
            ['jur_name', 'string', 'max' => 150],
            ['inn', 'string', 'max' => 12],
            ['ogrn', 'string', 'max' => 13],
            ['snils', 'filter', 'filter' => function($value){
                return preg_replace("/[^0-9]/", "", $value);
            }],
            [['snils'], 'string', 'min' => 11, 'max' => 11],
            ['snils', SnilsValidator::class],
            ['sso_id', 'safe'],
            //['inn', InnValidator::class],
            //['ogrn', OgrnValidator::class],
            //[['id_address'], 'exist', 'skipOnError' => true, 'targetClass' => Addresses::class, 'targetAttribute' => ['id_address' => 'id']],
            //[['id_fact_address'], 'exist', 'skipOnError' => true, 'targetClass' => Addresses::class, 'targetAttribute' => ['id_fact_address' => 'id']],
        ];
    }

    /**
     * @return PetOwnersQuery|object|\yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public static function find()
    {
        return \Yii::createObject(PetOwnersQuery::class, [get_called_class()]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPhone()
    {
        return $this->hasMany(Contacts::class, ['entity_id' => 'id'])
            ->joinWith('contactType')
            ->where(['contacts.entity_type' => 'pet_owner', 'contact_types.type' => ContactTypes::TYPE_PHONE]);
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
     * @return $this
     */
    public function getMainIfExists(): self
    {
        if ($this->is_main || !$this->id_main_owner) {
            return $this;
        }

        return static::findOne($this->id_main_owner) ?: $this;
    }
}
