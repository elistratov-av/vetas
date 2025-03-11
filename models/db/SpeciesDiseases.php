<?php


namespace app\models\db;


class SpeciesDiseases extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'species_diseases';
    }
}
