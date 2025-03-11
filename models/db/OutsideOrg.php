<?php

namespace app\models\db;
/**
 * This is the model class for table "outside_org".
 *
 * @property int $id
 * @property string $name Наименование огранизации
 * @property string $adm_area
 * @property string $district
 * @property string $address
 * @property string $assigned_registration_number_certificate
 * @property string $validity_certificate
 * @property int $global_id
 * @property bool $is_deleted
 */
class OutsideOrg extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'outside_org';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string'],
            [['name'], 'unique'],
            // IS_DELETED
            [['is_deleted'], 'boolean'],
            [['is_deleted'], 'default', 'value' => false],
        ];
    }

    /**
     * Проверяем, использовалось ли эта запись из справочника где-либо
     * @return bool TRUE если использовалась
     */
    public function checkUsage()
    {
        $where = ['id' => $this->id, 'is_out_org' => true];

        return PetRabiesVaccination::find()->where($where)->exists()
            || PetDehelmintization::find()->where($where)->exists()
            || PetEctoparasites::find()->where($where)->exists()
            || PetDehelmintization::find()->where($where)->exists();
    }

    /**
     * This method is invoked before deleting a record.
     * @return bool
     */
    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        if ($this->checkUsage()) {
            $this->addError('id', 'Организация уже используется в данных по вакцинациям. Нельзя удалить используемую организацию');
            return false;
        }

        return true;
    }
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'adm_area' => 'Adm Area',
            'district' => 'District',
            'address' => 'Address',
            'assigned_registration_number_certificate' => 'Assigned Registration Number Certificate',
            'validity_certificate' => 'Validity Certificate',
            'global_id' => 'Global ID',
            'is_deleted' => 'Is Deleted',
        ];
    }
//    /**
//     * This method is called at the beginning of inserting or updating a record.
//     *
//     * @param bool $insert
//     * @return bool
//     */
//    public function beforeSave($insert)
//    {
//        if (!parent::beforeSave($insert)) {
//            return false;
//        }
//        if (!$this->isNewRecord && $this->checkUsage()) {
//            $this->addError('id', 'Организация уже используется в данных по вакцинациям. Нельзя редактировать используемую организацию');
//            return false;
//        }
//        return true;
//    }
}
