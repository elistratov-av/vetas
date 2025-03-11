<?php

namespace app\modules\v1\models;
use app\common\components\entity\EntityInterface;
use app\common\events\FileAttachEvent;
use app\common\events\FileDeleteEvent;
use tuyakhov\jsonapi\LinksInterface;
use tuyakhov\jsonapi\ResourceInterface;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;

/**
 * Class FileResource
 * @package app\modules\v1\models
 *
 * @property string $hash
 * @property string $path
 * @property string $name
 * @property integer $entity_id
 * @property string $entity_type
 *
 * @property TmcResource $tmc
 */
class FileResource extends BaseResource implements LinksInterface, ResourceInterface, EntityInterface
{
    const
        EVENT_FILE_ATTACH = 'file.attach',
        EVENT_FILE_DELETE = 'file.delete'
    ;

    protected $alias = 'files';

    protected $excludedFields = ['id', 'hash', 'created_by', 'updated_by', 'created_at', 'updated_at'];

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors[] = [
            'class' => TimestampBehavior::class,
            'createdAtAttribute' => 'created',
            'updatedAtAttribute' => null,
            'value' => date('Y-m-d H:i:s')
        ];

        return $behaviors;
    }

    public function rules()
    {
        return [
            [['hash', 'name', 'path', 'created', 'entity_id', 'entity_type'], 'safe']
        ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'files';
    }

    //TODO: объединить с tableName
    public function getTableName() : string
    {
        return 'files';
    }

    public function getTypeName() : string
    {
        return 'file';
    }

    public function getAliasName() : string
    {
        return $this->alias;
    }

    public function getRelationships() : array
    {
        return [];
    }

    public function getType()
    {
        return 'file';
    }

    /**
     * @return TmcResource|\yii\db\ActiveQuery
     */
    public function getTmc()
    {
        return $this->hasOne( TmcResource::class, ['id' => 'id_tmc']);
    }

    /**
     * @param $id
     * @param $field
     * @param $params
     * @param $attributes
     * @return null|static
     * @throws BadRequestHttpException
     * @throws \yii\base\NotSupportedException
     * @throws \yii\db\IntegrityException
     */
    public function saveRelation($id, $field, $params, $attributes)
    {
        if (!isset($params['File']['id'])) {
            throw new BadRequestHttpException();
        }

        /** @var FileResource $fil */
        if (!$file = self::findOne($params['File']['id'])) {
            throw new BadRequestHttpException("Файл не найден");
        }

        if (!empty($file->$field)) {
            throw new BadRequestHttpException("Файл уже привязан к ресурсу {$file->entity_type} #{$file->$field}");
        }

        $file->$field = $id;
        $file->setAttributes($attributes);
        $file->save();

        $event = new FileAttachEvent();
        $event->file = $file;
        \Yii::$app->trigger(self::EVENT_FILE_ATTACH, $event);

        return $file;
    }

    public function afterDelete()
    {
        parent::afterDelete();

        /** @var  $event */
        $event = new FileDeleteEvent();
        $event->id = $this->entity_id;
        $event->type = $this->entity_type;
        $event->path = $this->path;
        \Yii::$app->trigger(self::EVENT_FILE_DELETE, $event);
    }

    /**
     * @inheritdoc
     */
    public function getLinks()
    {
        $links = parent::getLinks();

        if (!empty($this->path)) {
            $links['related'] = $this->convertPathToUrl();
        }

        return $links;
    }

    /**
     * @return string
     */
    private function convertPathToUrl()
    {
        return Url::to(str_replace('\\', '/', ltrim(FileHelper::normalizePath($this->path), '/\\')), true);
    }
}
