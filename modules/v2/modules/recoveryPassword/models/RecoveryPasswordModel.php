<?php

namespace app\modules\v2\modules\recoveryPassword\models;

use yii\db\Exception;
use yii\web\BadRequestHttpException;

class RecoveryPasswordModel
{
    /**
     * @return array
     * @throws Exception
     */
    public function all(): array
    {
        $sql = 'SELECT * FROM public.recovery_password';

        return [
            'requests' => \Yii::$app->db->createCommand($sql)->queryAll(),
        ];
    }

    /**
     * @param string $login
     * @throws BadRequestHttpException
     * @throws \yii\base\Exception
     */
    public function add(string $login): array
    {
        $query='INSERT INTO recovery_password ';
        $query.='(status, login, date) ';
        $query.='VALUES (';
        $query.="0,";
        $query.="'". $login ."',";
        $query.="NOW()::timestamp(0)";#created_at
        $query.=') RETURNING id;';

        return [
            \Yii::$app->db->createCommand($query)->query(),
        ];
    }


}
