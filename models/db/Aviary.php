<?php

namespace app\models\db;

use app\common\validators\FullTrimValidator;
use app\models\db\elk\ElkPets;
use app\modules\v2\modules\gosvetnadzor\models\ViolationChangeStateModel;
use yii\db\ActiveQuery;
use yii\db\Query;

/**
 * This is the model class for table "aviary".
 *
 * @property int                         $id
 * @property int                         $aviary_number
 * @property string                      $pets_moniker
 * @property int                         $pets_card
 * @property int                         $pets_chip
 * @property string                      $created_by
 * @property string                      $updated_by
 */
class Aviary extends ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'aviary';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title'], 'required'],
            [['title'], 'string', 'max' => 255],
            [['description'], 'string', 'max' => 255],
            //
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'description' => 'Description'
        ];
    }

    public function getOrganization()
    {
        return $this->hasOne(Organizations::class, ['id' => 'organization_id']);
    }

}
