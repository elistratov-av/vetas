<?php

namespace app\models\db;

use app\models\db\subscription\SubscriptionLog;
use app\modules\admin\models\Drugs;
use app\modules\v2\modules\pets\models\PassportModel;
use app\modules\v2\modules\tmc\models\VaccinesModel;
use app\modules\v2\modules\vaccinationJournal\models\ShelterModel;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "public.files".
 *
 * @property integer $id
 * @property string $hash
 * @property string $path
 * @property string $name
 * @property string $created
 * @property integer $created_by
 * @property integer $updated_by
 * @property string $created_at
 * @property string $updated_at
 * @property integer $entity_id
 * @property string $entity_type
 *
 * @property-read Documents[]        $documents
 *
 * @property \app\models\db\ActiveRecord $entity
 */
class Files extends ActiveRecord
{

    public $filesize;

    public static $entity_types = [
        'drug' => Drugs::class,
        'pet' => Pets::class,
        'reg_certificate' => RegCertificates::class,
        'specialist' => Specialists::class,
        'vaccine' => Vaccines::class,
        'visit_service_param_value' => VisitServiceParamValues::class,
        'visits_gov_service' => VisitsGovServices::class,
        'specialists_update_reason' => SpecialistsUpdateReasons::class,
        'faq' => Faqs::class,
        'violation' => Violation::class,
        'quarantine' => Quarantine::class,
        'violation_admin_rights' => ViolationAdminRights::class,
        'help' => Help::class,
        'passport' => PassportModel::class,
        'owner_feedback' => OwnerFeedback::class,
        'subscription_log' => SubscriptionLog::class,
        'shelter' => ShelterModel::class,
        'shelter_guests' => ShelterGuests::class,
        'visit' => Visits::class
    ];

    public static $entity_primary_keys = [
        'violation' => 'id_violation',
        'violation_admin_rights' => 'id_ARV',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'public.files';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['hash'], 'required'],
            [['path'], 'required', 'when' => function(Files $file) {
                return $file->entity_type !== 'visit-mos-ru';
            }],
            [['created', 'created_at', 'updated_at'], 'safe'],
            [['created_by', 'updated_by', 'entity_id'], 'integer'],
            [['hash', 'path', 'name'], 'string', 'max' => 255],
            [['entity_type'], 'string', 'max' => 30],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'hash' => 'Hash',
            'path' => 'Path',
            'name' => 'Name',
            'created' => 'Created',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'entity_id' => 'Entity ID',
            'entity_type' => 'Entity Type',
        ];
    }

    /**
     * @param string $entity_type
     * @return string
     */
    public static function modelClassFromEntityType($entity_type)
    {
        return ArrayHelper::getValue(static::$entity_types, $entity_type);
    }

    /**
     * По-умолчанию - id
     *
     * @param string $entity_type
     * @return string
     */
    public static function modelPrimaryKeyFromEntityType($entity_type)
    {
        return ArrayHelper::getValue(static::$entity_primary_keys, $entity_type, 'id');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEntity()
    {
        $class = static::modelClassFromEntityType($this->entity_type);
        $primary_key =  static::modelPrimaryKeyFromEntityType($this->entity_type);

        return $this->hasOne($class, [$primary_key => 'entity_id']);
    }

        /**
     * Документы, относящиеся к животному
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDocuments()
    {
        return $this->hasMany(Documents::class, ['file_id' => 'id']);
    }
}
