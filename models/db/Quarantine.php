<?php

namespace app\models\db;

use app\common\validators\FullTrimValidator;

/**
 * Class Quarantine
 * @package app\models\db
 *
 * @property int    $id
 * @property int    $id_disease
 * @property string $threatened_area
 * @property string $start_date
 * @property string $end_date
 * @property string $fact_end_date
 * @property string $comments
 * @property int    $created_by
 * @property int    $updated_by
 * @property string $created_at
 * @property string $updated_at
 *
 * @property \app\models\db\QuarantineFocus[]    $focuses
 * @property \app\models\db\QuarantineLocality[] $localities
 * @property \app\models\db\Diseases             $disease
 * @property \app\models\db\Files[]              $files
 */
class Quarantine extends ActiveRecord
{
    /**
     * @inheritDoc
     */
    public static function tableName()
    {
        return 'quarantines';
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['id_disease', 'threatened_area', 'start_date'], 'required'],
            ['id_disease', 'integer'],
            [['threatened_area', 'comments'], 'string'],
            [['threatened_area', 'comments'], FullTrimValidator::class],
            [['start_date', 'end_date', 'fact_end_date'], 'date', 'format' => 'php:Y-m-d'],
            [['created_at', 'updated_at', 'created_by', 'updated_by'], 'safe'],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFocuses()
    {
        return $this->hasMany(QuarantineFocus::class, ['id_quarantine' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLocalities()
    {
        return $this->hasMany(QuarantineLocality::class, ['id_quarantine' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDisease()
    {
        return $this->hasOne(Diseases::class, ['id' => 'id_disease']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFiles()
    {
        return $this->hasMany(Files::class, ['entity_id' => 'id'])
            ->andWhere(['entity_type' => 'quarantine']);
    }

    /**
     * {@inheritdoc}
     */
    public function extraFields()
    {
        $fields = parent::extraFields();
        $fields['disease'] = 'disease';
        $fields['files'] = 'files';

        return $fields;
    }
}
