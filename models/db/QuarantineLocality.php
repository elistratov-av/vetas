<?php

namespace app\models\db;

use yii\db\Expression;

/**
 * Class QuarantineLocality
 * @package app\models\db
 *
 * @property int          $id
 * @property int          $id_quarantine
 * @property array|string $territory
 * @property string       $coords
 * @property int          $created_by
 * @property int          $updated_by
 * @property string       $created_at
 * @property string       $updated_at
 * @property string       $description_locality
 *
 * @property \app\models\db\Quarantine        $quarantine
 * @property \app\models\db\QuarantineFocus[] $focuses
 */
class QuarantineLocality extends ActiveRecord
{
    /**
     * @var string
     */
    private $geometry;
    private $description_locality;
    /**
     * @inheritDoc
     */
    public static function tableName()
    {
        return 'quarantines_localities';
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['id_quarantine', 'territory'], 'required'],
            [['territory', 'coords', 'created_at', 'updated_at', 'created_by', 'updated_by', 'description_locality'], 'safe'],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFocuses()
    {
        return $this->hasMany(QuarantineFocus::class, ['id_locality' => 'id']);
    }

    /**
     * {@inheritdoc}
     */
    public function extraFields()
    {
        $fields = parent::extraFields();
        $fields['geometry'] = 'geometry';

        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public static function find()
    {
        return parent::find()
            ->select('*')
            ->addSelect(new Expression('ST_AsText(coords) AS geometry'));
    }

    /**
     * @return mixed
     */
    public function getGeometry()
    {
        return $this->geometry;
    }

    /**
     * @param mixed $geometry
     */
    public function setGeometry($geometry): void
    {
        $this->geometry = $geometry;
    }

    /**
     * @return mixed
     */
    public function getDescriptionLocality()
    {
        return $this->description_locality;
    }
    /**
     * @param mixed $description_locality
     */
    public function setDescriptionLocality(string $description_locality): void
    {
        $this->description_locality = $description_locality;
    }

}
