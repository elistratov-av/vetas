<?php

namespace app\modules\v3\modules\shelters\controllers;

use Yii;
use yii\caching\Cache;
use app\modules\v3\modules\BaseController as Controller;

class BaseController extends Controller
{
    protected static function getDeathReasons() {
        $deathReasons = Yii::$app->cache->get("deathReasons");
        if (!$deathReasons) {
            $deathReasons = Yii::$app->db->createCommand("SELECT id, name, description FROM death_reason")->queryAll();
            Yii::$app->cache->set("deathReasons", $areas, 3600 * 24);
        }
        return $deathReasons;
    }
    
    public function actionGetDictionary()
    {
        return [
            'is_success' => true,
            'data' => [
                'death_reasons' => self::getDeathReasons()
            ]
        ];
    }


}
