<?php

namespace app\models\db;

use yii\helpers\ArrayHelper;

/**
 * Class PasswordHistory
 * @package app\models\db
 *
 * @property int    $id
 * @property int    $id_user
 * @property int    $target
 * @property string $password
 * @property string $created_at
 * @property string $updated_at
 */
class PasswordHistory extends ActiveRecord
{
    const TARGET_FRONTEND = 1;
    const TARGET_ADMIN = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'public.password_history';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['password', 'string'],
            [['target', 'id_user'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * @param int $id_user
     * @param int $target
     * @return static[]
     */
    public static function findLast($id_user, $target)
    {
        $limit = ArrayHelper::getValue(\Yii::$app->params, 'password_compare_previous', 1);

        return static::find()
            ->where([
                'target' => $target,
                'id_user' => $id_user,
            ])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit($limit)
            ->all();
    }
}
