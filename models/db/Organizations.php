<?php

namespace app\models\db;

use app\common\validators\FullTrimValidator;
use app\common\validators\InnValidator;
use app\common\validators\KppValidator;
use app\common\validators\OgrnValidator;
use app\common\validators\OrganizationsLoopValidator;
use yii\base\InvalidArgumentException;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "public.organizations".
 *
 * @property integer       $id
 * @property integer       $parent_id
 * @property integer       $id_org_type
 * @property string        $organization_type_const Идентификатор системных типов
 * @property string        $name
 * @property string        $short_name
 * @property string        $inn
 * @property string        $kpp
 * @property string        $ogrn
 * @property integer       $id_address
 * @property string        $schedule
 * @property integer       $id_area
 * @property integer       $id_district
 * @property integer       $created_by
 * @property integer       $updated_by
 * @property string        $created_at
 * @property string        $updated_at
 * @property integer       $reg_number
 * @property string        $id_fias_address
 * @property bool          $mark_up_flag
 * @property string        $mark_up_ratio
 * @property string        $mark_up_from_time
 * @property string        $mark_up_to_time
 * @property double        $latitude
 * @property double        $longitude
 * @property bool          $reseption_corpses Флаг: является пунктом приема трупов животных
 * @property bool          $free_vaccination  Флаг: является центром бесплатной вакцинации
 * @property bool          $pet_registration  Флаг: является пунктом регистрации животных
 * @property string        $comment
 * @property string        $bti_adm_area_code
 * @property string[]      $bti_adm_district_codes
 * @property integer       $id_pet_owner    ID представителя для организаций типа Приют
 *
 * @property string        $phone
 * @property string        $email
 * @property Pricelists    $pricelist
 * @property Organizations $rootOrganization
 * @property Organizations $parentOrganization
 * @property FiasAddresses $fias_addresses
 * @property Addresses     $addresses
 * @property Contacts[]    $contacts
 * @property OrgTypes      $org_type
 * @property PetOwners     $representative
 */
class Organizations extends ActiveRecord
{
    /**
     * Комитет ветеринарии города Москвы
     */
    const GOS_ROOT_ID = 445;

    /**
     * ГБУ «Мосветобъединение»"
     */
    const MOS_VET_UNION_ID = 456;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'public.organizations';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'parent_id',
                    'id_org_type',
                    'id_address',
                    'id_fias_address',
                    'id_area',
                    'id_district',
                    'created_by',
                    'updated_by',
                    'id_pet_owner',
                ],
                'integer'
            ],
            [['id_org_type'], 'required'],
            [['name'], 'required'],
            [['chief_name', 'chief_position', 'name'], FullTrimValidator::class],
//            [['schedule'], 'string'],
            ['parent_id', OrganizationsLoopValidator::class],
            [['created_at', 'updated_at',], 'safe'],
            [['name', 'latitude', 'longitude', 'bti_adm_area_code', 'organization_type_const'], 'string', 'max' => 255],
            [['bti_adm_district_codes'], 'each', 'rule' => ['string']],
            [['bti_adm_district_codes'], 'required', 'when' => function (Organizations $model) {
                return $model->org_type->is_tech === false;
            }],
            [['id_pet_owner'], 'required', 'when' => function (Organizations $model) {
                return $model->org_type->is_tech === true;
            }],
            [['short_name'], 'string', 'max' => 120],
            ['inn', InnValidator::class, 'is_legal' => true],
            ['kpp', KppValidator::class],
            ['ogrn', OgrnValidator::class],
            [['inn', 'ogrn'], 'required'],
            [['mark_up_flag', 'reseption_corpses', 'free_vaccination', 'pet_registration'], 'boolean'],
            [['mark_up_ratio'], 'number'],
            [
                ['id_org_type'],
                'exist',
                'skipOnError' => true,
                'targetClass' => OrgTypes::class,
                'targetAttribute' => ['id_org_type' => 'id']
            ],
            ['reg_number', 'integer', 'max' => 9999999999],
            [['id_pet_owner'], 'exist', 'skipOnError' => true, 'targetClass' => PetOwners::class, 'targetAttribute' => ['id_pet_owner' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'parent_id' => 'Родительская организация',
            'id_org_type' => 'Тип организации',
            'organization_type_const' => 'Системный тип организации',
            'name' => 'Полное наименование',
            'short_name' => 'Сокращенное наименование',
            'inn' => 'ИНН',
            'kpp' => 'КПП',
            'ogrn' => 'ОГРН',
            'id_address' => 'Id Address',
            'schedule' => 'Schedule',
            'id_area' => 'Id Area',
            'id_district' => 'Id District',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'mark_up_flag' => 'Mark Up Flag',
            'mark_up_ratio' => 'Mark Up Ratio',
            'mark_up_from_time' => 'Mark Up From Time',
            'mark_up_to_time' => 'Mark Up To Time',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'reg_number' => 'Регистрационный номер',
            'id_fias_address' => 'Ссылка на адрес ФИАС, таблица fias_address',
            'reseption_corpses' => 'Является пунктом приема трупов животных',
            'free_vaccination' => 'Является центром бесплатной вакцинации',
            'pet_registration' => 'Является пунктом регистрации животных',
            'bti_adm_area_code' => 'Значения обслуживаемого округа',
            'bti_adm_district_codes' => 'Обслуживаемые округа',
            'id_pet_owner' => 'Представитель организации (Приюты)',
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
    public function getFias_addresses()
    {
        return $this->hasOne(FiasAddresses::class, ['id' => 'id_fias_address']);
    }

    /**
     * @return bool
     */
    public function isRoot()
    {
        return $this->parent_id === 0;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRootOrganization()
    {
        return Organizations::find()
            ->innerJoin('organizations_tree', 'organizations_tree.root_id = organizations.id')
            ->where(['organizations_tree.id' => $this->id]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParentOrganization()
    {
        return $this->hasOne(self::class, ['id' => 'parent_id'])
            ->from([self::tableName() . ' AS parent']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPricelist()
    {
        return $this->hasOne(Pricelists::class, ['id_organization' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getRepresentative()
    {
        return $this->hasOne(PetOwners::class, ['id' => 'id_pet_owner']);
    }

    /**
     * @return ActiveQuery
     */
    public function getArea()
    {
        return $this->hasOne(Areas::class, ['id' => 'id_area']);
    }

    /**
     * @param bool $withFiasAddresses
     * @param bool $fixShelters
     *
     * @return \app\models\db\Organizations[]
     */
    public function getTree($withFiasAddresses = false, $fixShelters = false)
    {
        $query = $this->prepareTreeQuery($withFiasAddresses, $fixShelters);

        return $query->all();
    }

    /**
     * @param bool $withFiasAddresses
     * @param bool $fixShelters
     *
     * @return \yii\db\ActiveQuery
     */
    public function prepareTreeQuery($withFiasAddresses = false, $fixShelters = false)
    {
        $root = $this->isRoot() ? $this : ($this->rootOrganization ?? $this);

        $query = Organizations::find()
            ->innerJoin('organizations_tree', 'organizations_tree.id = organizations.id')
            ->where(['organizations_tree.root_id' => $root->id])
            ->andWhere(['hidden' => false])
            ->orderBy([
                'organizations_tree.path' => SORT_ASC,
                'organizations.short_name' => SORT_ASC,
            ]);

        if ($withFiasAddresses === true) {
            $query->joinWith('fias_addresses');
        }
        if ($fixShelters === true) {
            $this->excludeShelters($query);
        }

        return $query;
    }

    /**
     * Не показываем приюты в общем списке
     *
     * @param \yii\db\ActiveQuery $query
     */
    private function excludeShelters(\yii\db\ActiveQuery &$query): void
    {
        $query->leftJoin(OrgTypes::tableName() . ' ot', 'ot.id = organizations.id_org_type')
            ->andWhere([
                'or',
                ['=', 'ot.is_tech', false],
                ['ot.is_tech' => null],
            ]);
    }

    /**
     * @param int  $id_organization
     * @param bool $throwException
     *
     * @return array
     */
    public static function orgTreeIds($id_organization, $throwException = true)
    {
        $organization = static::findOne(['id' => $id_organization]);
        if ($organization === null) {
            if ($throwException === false) {
                return [];
            } else {
                throw new InvalidArgumentException('Организация с id ' . $id_organization . ' не найдена');
            }
        }
        $tree = $organization->getTree();

        return ArrayHelper::getColumn($tree, 'id', []);
    }

    /**
     * @return bool
     */
    public function isGos()
    {
        // для приютов не проверяем подчиненность комитету
        // TODO - переделать, когда будет отдельный признак для гос/негос
        if ($this->org_type !== null && $this->org_type->is_tech === true) {
            return true;
        }

        $root = $this->isRoot() ? $this : ($this->rootOrganization ?? $this);

        return $root->id == self::GOS_ROOT_ID;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        $contact = Contacts::find()
            ->where(['entity_id' => $this->id])
            ->andWhere(['entity_type' => 'organization'])
            ->andWhere(['id_contact_type' => [7, 8, 9, 11, 13, 15, 16, 17, 18]])
            ->orderBy(['updated_at' => SORT_DESC])
            ->one();

        if (!$contact) {
            return '';
        }

        return $contact->name;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContacts()
    {
        return $this->hasMany(Contacts::class, ['entity_id' => 'id'])
            ->where(['contacts.entity_type' => ContactTypes::ENTITY_TYPE_ORGANIZATION]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrg_type()
    {
        return $this->hasOne(OrgTypes::class, ['id' => 'id_org_type']);
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        $contact = Contacts::find()
            ->where(['entity_id' => $this->id])
            ->andWhere(['entity_type' => ContactTypes::ENTITY_TYPE_ORGANIZATION])
            ->andWhere(['id_contact_type' => [6, 20]])
            ->orderBy(['main_flag' => SORT_DESC])
            ->orderBy(['updated_at' => SORT_DESC])
            ->one();

        if (!$contact) {
            return '';
        }

        return $contact->name;
    }

    /**
     * @return string
     */
    public function getPhoneMain()
    {
        $contact = Contacts::find()
            ->where(['entity_id' => $this->id])
            ->andWhere(['entity_type' => ContactTypes::ENTITY_TYPE_ORGANIZATION])
            ->andWhere(['id_contact_type' => [7, 8, 9, 11, 13, 15, 16, 17, 18]])
            ->orderBy(['main_flag' => SORT_DESC])
            ->orderBy(['updated_at' => SORT_DESC])
            ->one();

        if (!$contact) {
            return '';
        }

        return $contact->name;
    }

    public static function getOrganizationTree($id = null)
    {
        $pointers = static::getPointers(static::find()->all());

        return $pointers[$id] ?? $pointers;
    }

    public static function getManagingOrganizationTree($id = null)
    {
        $models = [];
        foreach (static::find()->indexBy('id')->all() as $model)
        {
            if($model->id == $id){
                $trap = 1;
            }
            $models[$model->id] = array_merge($model->getAttributes(), [
                'parent_id' => $model->managing_organization_id,
            ]);
        }

        $pointers = static::getPointers($models);

        return $pointers[$id] ?? $pointers;
    }

    public static function getPointers(array $items)
    {
        $pointers = [];
        foreach ($items as $item) {
            $item = (object)$item;

            if (!isset($pointers[$item->id])) {
                $pointers[$item->id] = [
                    'id' => $item->id,
                    'children' => [],
                ];
            }

            $pointers[$item->id]['model'] = $item;

            if ($parent_id = $item->parent_id) {
                if (!isset($pointers[$parent_id])) {
                    $pointers[$parent_id] = [
                        'id' => $parent_id,
                        'children' => [],
                    ];
                }

                $pointers[$parent_id]['children'][$item->id] = &$pointers[$item->id];
            }
        }

        return $pointers;
    }

    public static function getNestedChildren($items)
    {
        $indexedChildren = [];
        $nestedChildren = [];
        foreach ($items as $item) {
            $indexedChildren[$item['id']] = $item;
            $nestedChildren = array_merge($nestedChildren, static::getNestedChildren($item['children']));
        }

        return $indexedChildren + $nestedChildren;
    }
}
