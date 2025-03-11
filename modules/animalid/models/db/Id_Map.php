<?php

namespace app\modules\animalid\models\db;

use app\models\db\ActiveRecord;
use app\modules\animalid\skeletons\interfaces\IdMapInterface;

/**
 * @property int    $id
 * @property int    $our
 * @property int    $their
 * @property string $type
 * @property string $timestamp
 */
class Id_Map extends ActiveRecord implements IdMapInterface
{
    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['our', 'their', 'type'], 'safe'],
        ];
    }

    /**
     * @inheritDoc
     */
    public static function tableName()
    {
        return 'animalid.id_mapping';
    }

    /**
     * @param int    $their
     * @param string $type
     * @return int|null
     */
    public static function getOurByTheir(int $their, string $type)
    {
        $model = self::findOne(['their' => $their, 'type' => $type]);

        return !empty($model->our) ? $model->our : null;
    }

    /**
     * @param int    $our
     * @param string $type
     * @return int|null
     */
    public static function getTheirByOur(int $our, string $type)
    {
        $model = self::findOne(['our' => $our, 'type' => $type]);

        return !empty($model->their) ? $model->their : null;
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        //если есть маппинг отправленных нами записей с пустым внешним ид
        //то мы не создаем новый мап, а добовляем в существующий внешний ид
        if (($map = self::findOne(['our' => $this->our, 'their' => null, 'type' => $this->type])) && $this->their) {
            $map->their = $this->their;
            $map->save();

            return false;
        }

        return true;
    }
}
