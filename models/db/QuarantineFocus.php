<?php

namespace app\models\db;

use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * Class QuarantineFocus
 *
 * @property int                     $id
 * @property int                     $id_quarantine
 * @property int                     $id_locality
 * @property string                  $address
 * @property string                  $description
 * @property string                  $identification_date
 * @property int                     $id_pet
 * @property string                  $coords
 * @property int                     $created_by
 * @property int                     $updated_by
 * @property string                  $created_at
 * @property string                  $updated_at
 *
 * @property-read Quarantine         $quarantine
 * @property-read Pets               $pet
 * @property-read QuarantineLocality $locality
 *
 * @package app\models\db
 */
class QuarantineFocus extends ActiveRecord
{
    /**
     * @var string
     */
    private $geometry;

    /**
     * @inheritDoc
     */
    public static function tableName()
    {
        return 'quarantines_focuses';
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['id_quarantine', 'address', 'identification_date'], 'required'],
            [['id_quarantine', 'id_pet', 'id_locality'], 'integer'],
            ['description', 'filter', 'filter' => 'trim'],
            ['description', 'string', 'max' => 255],
            [['coords', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'safe'],
        ];
    }

    public function getQuarantine(): ActiveQuery
    {
        return $this->hasOne(Quarantine::class, ['id' => 'id_quarantine']);
    }

    public function getPet(): ActiveQuery
    {
        return $this->hasOne(Pets::class, ['id' => 'id_pet']);
    }

    public function getLocality(): ActiveQuery
    {
        return $this->hasOne(QuarantineLocality::class, ['id' => 'id_locality']);
    }

    /**
     * {@inheritdoc}
     */
    public function extraFields()
    {
        $fields = parent::extraFields();
        $fields['geometry'] = 'geometry';
        $fields['pet'] = 'pet';

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

    public function getGeometry(): string
    {
        return $this->geometry;
    }

    public function setGeometry(string $geometry): void
    {
        $this->geometry = $geometry;
    }
}
