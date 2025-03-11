<?php

namespace app\modules\v1\models;

use app\common\behaviors\OrganizationBehavior;
use app\common\components\entity\EntityInterface;
use app\common\components\entity\EntityManager;
use app\common\components\entity\EntityRules;
use app\common\events\EntityResourceRelationEvent;
use app\models\db\Aviary;
use app\models\db\Files;
use app\models\db\Organizations;
use app\models\db\OrgTypes;
use app\models\db\PetIdentification;
use app\models\db\PetToSkills;
use app\models\db\Quarantine;
use app\models\db\QuarantineDetourNonVisit;
use app\models\db\ShelterGuests;
use Yii;
use app\common\components\entity\EntityInstance;
use app\common\components\entity\EntityResourceFactory;
use app\models\db\Diseases;
use tuyakhov\jsonapi\LinksInterface;
use tuyakhov\jsonapi\ResourceIdentifierInterface;
use tuyakhov\jsonapi\ResourceInterface;
use yii\base\UnknownMethodException;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveQueryInterface;
use yii\db\Exception;
use yii\db\Query;
use yii\helpers\BaseInflector;
use yii\helpers\Url;
use yii\validators\Validator;
use yii\web\BadRequestHttpException;
use yii\web\Link;
use DateTime;
use yii\db\Expression;

class EntityResource extends BaseResource implements LinksInterface, ResourceInterface
{
    const RELATION_PLURAL = 'plural';
    const RELATION_SINGLE = 'single';
    const RELATION_MANY   = 'many';

    const EVENT_ENTITY_RELATION_ATTACH = 'entity.relation.attach';
    const EVENT_ENTITY_RELATION_DETACH = 'entity.relation.detach';

    /**
     * @var EntityInstance
     */
    public $entityInstance;

    /**
     * @var EntityResource[]
     */
    protected $relationships = [];

    /**
     * @var EntityRules
     */
    protected $entityRules;

    protected $relationshipsMap = [];

    protected $extraFields = [];

    /**
     * @var EntityInstance
     */
    protected $injectedInstance;

    /**
     * EntityResource constructor.
     * @param array $config
     * @param null $injectedInstance
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function __construct(array $config = [], $injectedInstance = null)
    {
        $this->entityRules = Yii::$container->get('entityRules');
        $this->injectedInstance = $injectedInstance;
        parent::__construct($config);
    }

    public function afterFind()
    {
        parent::afterFind();
        switch ($this->getAliasName()) {
            case 'pets':
                $this->additionalFields['age'] = 'age';
                $this->additionalFields['identification'] = 'identification';
                $this->additionalFields['shelter'] = 'shelter';
                $this->additionalFields['shelterGuest'] = 'shelterGuest';
                $this->additionalFields['aviary'] = 'aviary';
                $this->additionalFields['quarantine'] = 'quarantine';
                $this->additionalFields['photos'] = 'photos';
                $this->additionalFields['skillsIds'] = 'skillsIds';
                break;
            case 'diseases':
                $this->additionalFields['code'] = 'code';
                break;
        }
    }

    public function getSkills_ids()
    {
        $skill_ids = PetToSkills::find()->where(['id_pet' => $this->id])->select('id_skill')->asArray()->all();
        $ids = [];

        foreach ($skill_ids as $id)
            $ids[] = $id['id_skill'];

        return $ids;
    }

    public function getAge()
    {
        if ($this->birthday && $birthday = \DateTime::createFromFormat('Y-m-d', $this->birthday)) {
            $interval = (new DateTime())->diff($birthday);
            return $interval->y . ' г. ' . $interval->m . ' мес.';
        }
    }

    public function getIdentification()
    {
        return PetIdentification::findOne(['id_pet' => $this->id]);
    }

    public function getShelter_guest()
    {
        $model = ShelterGuests::find()->where(['id_pet' => $this->id])

            // ВНИМАНИЕ!!! порядок ->with( должен быть именно такой - сначала documents, потом files

            ->with(['documents' => function ($query) {
                /** @var $query ActiveQuery * */
                $query
                    ->select([
                        'documents.*',
                        'document_types.type AS document_type',
                    ])
                    ->leftJoin(
                        'document_types',
                        'documents.type_id = document_types.id'
                    )
                    ->where(['not', ['document_types.type' => null]])
                    ->orderBy('date')
                    ->indexBy('document_type')
                    ->asArray();

                // $trap = $query->all();
            }])
            ->with(['files' => function ($query) {
                /** @var $query ActiveQuery * */
                $query
                    ->select([
                        'files.id',
                        'files.name',
                        'files.path',
                        'files.entity_id',
                        'files.entity_type',
                        'files.hash',
                        'files.created',
                        'files.created_at',
                        'files.created_by',
                        'files.updated_at',
                        'files.updated_by',
                        'documents.name as full_name',
                        'documents.type_id AS document_type',
                        'document_types.type AS document_type_text',
                        'documents.date AS document_date',
                        'documents.status AS document_status',
                        'documents.number AS document_number',
                        'documents.protected_at AS protected_at',
                    ])
                    ->leftJoin(
                        'documents',
                        'files.id = documents.file_id'
                    )
                    ->leftJoin(
                        'document_types',
                        'documents.type_id = document_types.id'
                    );
            }])
            ->asArray()->one();

        if (!$model) {
            $model = new ShelterGuests();
            $model->setAttributes([
                'id_pet' => $this->id,
                'id_organization' => $this->id_created_organization, // ... но это неточно
                'arrival_date' => date('Y-m-d', strtotime(date('Y-01-01'))),
            ])->save();
        }

        return $model;
    }

    public function getPhotos()
    {
        $results =  (new Query())
            ->select([
                'id', 'hash', 'path', 'name', 'created', 'created_by', 'updated_by', 'created_at', 'updated_at', 'entity_id', 'entity_type',
                new Expression('entity_type = :main as selected', [':main' => 'shelter_main'])
            ])
            ->from(Files::tableName())
            ->where(['entity_id' => $this->id, 'entity_type' => ['pets', 'shelter', 'shelter_main',]])
            ->indexBy('id')->all();

        return $results;
    }

    public function getShelter()
    {
        return $this->hasOne(Organizations::class, ['id' => 'id_organization'])
            ->viaTable(ShelterGuests::tableName(), ['id_pet' => 'id']);
    }

    public function getQuarantine()
    {
        if (($quarantine_hz = QuarantineDetourNonVisit::find()->orderBy('date DESC')->asArray()->one())
            && $quarantine = Quarantine::find()->where(['id' => $quarantine_hz['id_quarantine']])->asArray()->one()
        ) {
            $now = new DateTime();
            $end_date = new DateTime($quarantine['end_date']);
            if ($now < $end_date) {
                $result = array_merge($quarantine_hz, $quarantine);
                $result['until_date'] = $end_date->format('d.m.Y');
            }
        }

        return $result ?? null;
    }

    public function getAviary()
    {
        return $this->hasOne(Aviary::class, ['id' => 'aviary_id'])
            ->viaTable(ShelterGuests::tableName(), ['id_pet' => 'id']);
    }

    /**
     * @return string
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public static function tableName()
    {
        $instance = Yii::$container->get('entityInstance');

        return $instance->getTableName();
    }

    public function getAliasName(): string
    {
        return $this->entityInstance->getAliasName();
    }

    /**
     * @param string $name
     * @return EntityResource|mixed|null
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function __get($name)
    {
        if ($rel = $this->getRelationByPropName($name)) {
            return $rel;
        }

        return parent::__get($name);
    }

    /**
     * @param string $name
     * @param array $params
     * @return mixed|null|ActiveQuery
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function __call($name, $params)
    {
        $pattern = '/get([a-z]+)/i';
        $res = null;

        try {
            $res = parent::__call($name, $params);
        } catch (UnknownMethodException $e) {
            if (preg_match($pattern, $name, $matches)) {
                $relname = strtolower($matches[1]);
                $activeQuery = $this->getActiveQueryForSingleRelation($relname);

                if ($activeQuery !== false) {
                    $res = $activeQuery;
                }
            }
        }

        return $res;
    }


    /**
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function init()
    {
        $this->entityInstance = $this->injectedInstance ?? \Yii::$container->get('entityInstance');
        $this->extraFields = $this->entityInstance->getRelationships();

        parent::init();
    }

    /**
     * @param EntityInstance $entityInstance
     */
    public function setEntityInstance(EntityInstance $entityInstance): void
    {
        $this->entityInstance = $entityInstance;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->entityInstance->getTypeName();
    }

    /**
     * @return array
     */
    public function rules()
    {
        $this->entityRules->init($this->entityInstance);
        $rules = $this->entityRules->getRules();

        return $rules;
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $classname = BaseInflector::camelize($this->getType()) . 'Behavior';
        $classpath = Yii::getAlias('@app/common/behaviors') . DIRECTORY_SEPARATOR . $classname . '.php';

        if (file_exists($classpath)) {
            $behaviors[] = 'app\\common\\behaviors\\' . $classname;
        }

        $behaviors[] = [
            'class' => BlameableBehavior::class,
        ];

        $behaviors[] = [
            'class' => TimestampBehavior::class,
            'value' => date("Y-m-d H:i:s"),
        ];

        Validator::$builtInValidators['unique'] = 'app\common\validators\UniqueValidator';

        return $behaviors;
    }

    /**
     * @param string $name
     * @param bool $throwException
     * @return EntityResource|mixed|ResourceIdentifierInterface|ActiveQuery|\yii\db\ActiveQueryInterface
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function getRelation($name, $throwException = true)
    {
        if ($relation = $this->getRelationshipLink($name)) {
            return $relation;
        }

        return parent::getRelation($name, $throwException);
    }

    /**
     * @param array $linked
     * @return EntityResource[]|array|ResourceIdentifierInterface[]
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function getResourceRelationships(array $linked = [])
    {
        $this->initRelationships();

        return $this->relationships;
    }

    /**
     * @param array $linked
     * @return bool
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function initRelationships(array $linked = [])
    {
        $entityManager = \Yii::$container->get('entityManager');

        $relationships = $this->entityInstance->getRelationships();
        $nestedCollections = $this->entityInstance->getAllNestedCollections();
        $this->relationshipsMap = $relationships;

        if (count($this->relationships)) {
            return true;
        }

        foreach ($relationships as $relationship) {
            $method = 'get' . BaseInflector::camelize($relationship['link']);
            if (method_exists($this, $method)) {
                $this->relationships[$relationship['link']] = $this->$method($this->{$relationship['property']});
            } else {
                $instance = $entityManager->getEntity($relationship['link']);
                \Yii::$container->set('entityInstance', $instance);
                $resource = \Yii::$container->get('entityResource', [[], $instance]);

                if (isset($this->{$relationship['property']}) && $this->{$relationship['property']} != null) {
                    $key = (isset($relationship['usePropname']) && $relationship['usePropname'] === true && isset($relationship['propname']))
                        ? $relationship['propname']
                        : $resource->getType();
                    $this->relationships[$key] = $resource::find()
                        ->where(['id' => $this->{$relationship['property']}])->one();
                }
            }
        }

        foreach ($nestedCollections as $collection) {
            $activeQuery = $this->getActiveQueryForPluralRelation($collection->getEntityCollectionName());
            $this->relationships[$collection->getEntityCollectionName()] = $activeQuery->all();
        }

        return true;
    }

    /**
     * @param string $name
     * @return null|ActiveQuery
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function getRelationshipLink(string $name)
    {
        if ($relation = $this->entityInstance->getRelationByField($name)) {
            $method = 'get' . BaseInflector::camelize($relation['link']);
            if (method_exists($this, $method)) {
                return $this->$method($this->{$relation['property']});
            } else {
                $entityManager = \Yii::$container->get('entityManager');
                $instance = $entityManager->getEntity($relation['link']);
                \Yii::$container->set('entityInstance', $instance);
                $resource = \Yii::$container->get('entityResource', [[], $instance]);

                return $this->hasMany($resource::className(), [$relation['property'] => 'id']);
            }
        }

        if ($collection = $this->entityInstance->getNestedCollection($name)) {
            return $this->getActiveQueryForPluralRelation($collection->getEntityCollectionName());
        }

        return null;
    }

    public function activeAttributes()
    {
        return $this->attributes();
    }

    /**
     * @param $id
     * @return null|TmcResource
     */
    public function getTmc($id)
    {
        return TmcResource::findOne($id);
    }

    /**
     * @return array
     */
    public function extraFields()
    {
        return $this->extraFields;
    }

    /**
     * @return array
     */
    public function getLinks()
    {
        return [
            Link::REL_SELF => Url::to(Url::base(true) . '/v1/' . $this->entityInstance->getAliasName() . '/' . $this->getId()),
        ];
    }

    /**
     * @param $relation
     * @return ActiveQuery
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function getActiveQueryForPluralRelation($relation)
    {
        $result = null;
        $collection = $this->entityInstance->getNestedCollection($relation);

        if ($collection) {
            $relResource = EntityResourceFactory::getResource($relation);

            if ($collection->isPlural()) {
                $query = $this->hasMany($relResource::className(), $collection->getLinkFields());
                if (!empty($collection->getAdditionalFields())) {
                    $query->onCondition($collection->getAdditionalFields());
                }
                $result = $query;
            } else {
                $result = $this->hasMany($relResource::className(), $collection->getLinkFields())
                    ->viaTable(
                        $collection->getJunctionTableName(),
                        $collection->getJunctionFields(),
                        $collection->getJunctionRelationDecorator()
                    );
            }
        }

        return $result;
    }

    /**
     * @param $relname
     * @return null|ActiveQuery
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function getActiveQueryForSingleRelation($relname)
    {
        $result = null;
        $relation = $this->entityInstance->getRelationByField($relname, 'propname');

        if ($relation) {
            $relResource = EntityResourceFactory::getResource($relation['link']);

            $result = $this->hasOne($relResource::className(), ['id' => $relation['property']]);
        }

        return $result;
    }

    /**
     * @param $relation
     * @param $entity_data
     * @throws Exception
     */
    public function attachEntityByRelation($relation, $entity_data)
    {
        $relatedCollection = $this->entityInstance->getNestedCollection($relation);

        if ($relatedCollection) {
            $ins_data = [];

            $ins_data[$relatedCollection->getPrimaryKey()] = $entity_data['id'];
            $ins_data[$relatedCollection->getForeignKey()] = $this->id;

            self::getDb()->createCommand()->insert($relatedCollection->getJunctionTableName(), $ins_data)->execute();

            $event = new EntityResourceRelationEvent();
            $event->parent_entity_id = $this->getId();
            $event->slave_entity_id = $entity_data['id'];
            $event->parent_entity_name = $this->entityInstance->getAliasName();
            $event->slave_entity_name = $relation;
            $this->trigger(self::EVENT_ENTITY_RELATION_ATTACH, $event);
        }
    }

    /**
     * @param $relation
     * @param $id
     * @throws Exception
     */
    public function detachEntityByRelation($relation, $id)
    {
        $relatedCollection = $this->entityInstance->getNestedCollection($relation);

        if ($relatedCollection) {
            $del_cond = [$relatedCollection->getPrimaryKey() => $id, $relatedCollection->getForeignKey() => $this->id];
            self::getDb()->createCommand()->delete($relatedCollection->getJunctionTableName(), $del_cond)->execute();

            $event = new EntityResourceRelationEvent();
            $event->parent_entity_id = $this->getId();
            $event->slave_entity_id = $id;
            $event->parent_entity_name = $this->entityInstance->getAliasName();
            $event->slave_entity_name = $relation;
            $this->trigger(self::EVENT_ENTITY_RELATION_DETACH, $event);
        }
    }

    /**
     * @param $relname
     * @return null|string
     */
    public function getRelationType($relname)
    {
        $type = null;

        if ($col = $this->entityInstance->getNestedCollection($relname)) {
            $type = $col->isPlural() ? self::RELATION_PLURAL : self::RELATION_MANY;
        } else if ($this->entityInstance->getRelationByField($relname)) {
            $type = self::RELATION_SINGLE;
        }

        return $type;
    }

    /**
     * @param $relname
     * @return mixed|null
     */
    public function getRelationLinkPropertyName($relname)
    {
        $res = null;

        if ($col = $this->entityInstance->getNestedCollection($relname)) {
            $res = $col->getForeignKey();
        }

        return $res;
    }

    /**
     * @param $relname
     * @return array
     */
    public function getRelationAdditionalFields($relname)
    {
        $res = [];
        $rel = $this->entityInstance->getNestedCollection($relname);

        if ($rel) {
            $res = $rel->getAdditionalFields();
        }

        return $res;
    }

    /**
     * @param $name
     * @return EntityResource|null
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    protected function getRelationByPropName($name)
    {
        $res = false;

        $relationships = $this->entityInstance->getRelationships();
        $this->relationshipsMap = $relationships;

        foreach ($this->relationshipsMap as $rel) {
            if (isset($rel['propname']) && $rel['propname'] == $name) {
                $relname = $rel['link'];
                $alias = EntityInstance::inflectTypeName($relname);

                $this->initRelationships();

                if (array_key_exists($relname, $this->relationships)) {
                    $res = $this->relationships[$relname];
                    break;
                }

                if (array_key_exists($alias, $this->relationships)) {
                    $res = $this->relationships[$alias];
                    break;
                }
            }
        }

        return $res;
    }

    /**
     * @param EntityResource $resource
     * @return bool
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function isRelatedTo($resource)
    {
        $alias = $resource->getAliasName();
        switch ($this->getRelationType($alias)) {
            case self::RELATION_SINGLE:
                $relation = $this->entityInstance->getRelationByField($alias);
                $field = $relation['property'];
                return $this->$field == $resource->id;

            case self::RELATION_PLURAL:
                $rows = $this->getActiveQueryForPluralRelation($alias)
                    ->select('id')
                    ->indexBy('id')
                    ->asArray()
                    ->all();
                return isset($rows[$resource->getId()]);

            case self::RELATION_MANY:
                $collection = $this->entityInstance->getNestedCollection($alias);
                $query = new Query();
                $query->from($collection->getJunctionTableName())
                    ->where([
                        $collection->getPrimaryKey() => $resource->getId(),
                        $collection->getForeignKey() => $this->getId()
                    ]);
                return $query->exists();

            default:
                return false;
        }
    }

    /**
     * @param $id
     * @param $field
     * @param $params
     * @param $attributes
     * @return $this
     * @throws \yii\base\NotSupportedException
     * @throws \yii\db\IntegrityException
     */
    public function saveRelation($id, $field, $params, $attributes)
    {
        $this->setScenario('insert');
        $this->load($params);
        $this->$field = $id;
        $this->setAttributes($attributes);

        $this->save();
        return $this;
    }

    public function beforeSave($insert)
    {

        if (self::tableName() == 'organizations') {

            $specialIds = OrgTypes::find()->indexBy('id')->where(['in', 'const', array_keys(OrgTypes::SYSTEM_TYPES)])->asArray()->all();
            if (in_array($this->id_org_type, array_keys($specialIds)))
                $this->organization_type_const = $specialIds[$this->id_org_type]['const'];

            else
                $this->organization_type_const = null;
        }

        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        if (self::tableName() == 'diseases') {
            $baseCode = $this->gostDisease ? $this->gostDisease->gost_code : null;
            if ($baseCode) {
                $existingCodes = Yii::$app->db->createCommand("
                    SELECT code 
                    FROM diseases
                    WHERE code ILIKE '%$baseCode%' ORDER BY name ASC")->queryColumn();
                $uniqueCode = $baseCode;
                $suffix = 1;
                while (in_array($uniqueCode, $existingCodes)) {
                    $uniqueCode = "$baseCode.$suffix";
                    $suffix++;
                }
                Diseases::updateAll(['code' => $uniqueCode], ['id' => $this->id]);
                return true;
            }
        }
        return false;
    }
}
