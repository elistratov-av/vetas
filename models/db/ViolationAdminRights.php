<?php

namespace app\models\db;

use app\common\validators\FullTrimValidator;
use yii\db\Expression;

/**
 * This is the model class for table "violation_admin_rights".
 *
 * @property int $id_ARV
 * @property string $short_name
 * @property string $full_name
 * @property string $description
 * @property bool $is_deleted Флаг: удалено
 * @property string $created_at
 * @property string $updated_at
 * @property int $created_by
 * @property int $updated_by
 *
 * @property Violation[] $violations
 * @property Files[] $files
 */
class ViolationAdminRights extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'violation_admin_rights';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['short_name'], 'required'],
            [['short_name', 'full_name', 'description'], 'string'],
            [['full_name', 'description'], FullTrimValidator::class],
            [['is_deleted'], 'boolean'],
            [['created_at', 'updated_at'], 'safe'],
            [['created_by', 'updated_by'], 'default', 'value' => null],
            [['created_by', 'updated_by'], 'integer'],
            /*
             * Уникальность для неудаленных по full_name
             */
            ['full_name', function ($attribute, $params, $validator) {
                if (($this->isNewRecord || $this->isAttributeChanged('full_name') && $this->is_deleted !== true)) {
                    $check = self::find()
                        ->where([
                            'AND',
                            ['full_name' => $this->full_name],
                            ['is_deleted' => false],
                        ])->exists();
                    if ($check) {
                        $this->addError($attribute, "АПН с таким же полным названием уже есть в системе");
                    }
                }
            }, 'when' => function ($model) {
                /* @var $model \app\models\db\ViolationAdminRights */
                return $model->is_deleted != true;
            }],
            /*
             * Уникальность для неудаленных по short_name
             */
            ['short_name', function ($attribute, $params, $validator) {
                if (($this->isNewRecord || $this->isAttributeChanged('short_name') && $this->is_deleted !== true)) {
                    $check = self::find()
                        ->where([
                            'AND',
                            ['short_name' => $this->short_name],
                            ['is_deleted' => false],
                        ])->exists();
                    if ($check) {
                        $this->addError($attribute, "АПН с таким же названием уже есть в системе");
                    }
                }
            }, 'when' => function ($model) {
                /* @var $model \app\models\db\ViolationAdminRights */
                return $model->is_deleted != true;
            }],
            /*
             * Запрет на редактирование значимых полей удаленных/используемых
             */
            [['full_name', 'short_name', 'description'], function ($attribute, $params, $validator) {

                if (!$this->isAttributeChanged('full_name')
                    || !$this->isAttributeChanged('short_name'
                    || !$this->isAttributeChanged('description'))){
                    return;
                }

                if ($this->getOldAttribute('is_deleted') == true){
                    $this->addError($attribute,'Редактирование АПН не доступно. АПН удалено ранее');
                    return;
                }

                if ($this->isReadOnly()) {
                    $this->addError($attribute,'Редактирование АПН не доступно. АПН используется Госветнадзором');
                }
            }]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_ARV' => 'Id ARV',
            'short_name' => 'Short Name',
            'full_name' => 'Full Name',
            'description' => 'Description',
            'is_deleted' => 'Флаг: удалено',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getViolations()
    {
        return $this->hasMany(Violation::class, ['id_ARV' => 'id_ARV']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFiles()
    {
        return $this->hasMany(Files::class, ['entity_id' => 'id_ARV'])
            ->where(['entity_type' => static::tableName()]);
    }

    /**
     * @return bool
     */
    public function isReadOnly()
    {
        if ($this->isNewRecord == TRUE) {
            return false;
        }

        if ($this->getOldAttribute('is_deleted') == TRUE) {
            return true;
        }

        /*
         * Проверяем, не использована ли уже в нарушениях
         */
        $check = Violation::find()
            ->where(['id_ARV' => $this->id_ARV])
            ->exists();

        return $check;
    }

    /**
     * Возвращает выражение для SQL запроса на проверку read-only записей
     * @return Expression
     */
    public static function getExpressionIsReadonly()
    {
        return new Expression("
        (SELECT EXISTS(
            SELECT 1 FROM violation 
            WHERE
                violation.\"id_ARV\" = violation_admin_rights.\"id_ARV\"
                    OR 
                is_deleted = true
            LIMIT 1
            )
        ) AS is_readonly");
    }
}
