<?php

use app\commands\migrate\Migration;
use app\models\db\GovServicesParams;

/**
 * Class m241107_103002_update_gsp_table
 */
class m241107_103002_update_gsp_table extends Migration
{
    const SERVICES = [2070, 2071];
    const PARAM_ID = 1;
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach (static::SERVICES as $service) {
                $govServiceParam = new GovServicesParams();
                $govServiceParam->id_param = static::PARAM_ID;
                $govServiceParam->id_service = $service;
                $govServiceParam->flag_out = true;
                $govServiceParam->req_in = false;
                $govServiceParam->req_out = false;
                $govServiceParam->sort_by = 1;
                if (!$govServiceParam->save()) {
                    $transaction->rollBack();
                    return false;
                }
            }
        } catch (Exception $e) {
            $transaction->rollBack();
            return false;
        }
        $transaction->commit();
        return true;
    }


    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach (static::SERVICES as $service) {
                $govServiceParam = GovServicesParams::findOne([
                    'id_param' => static::PARAM_ID,
                    'id_service' => $service,
                ]);
                if ($govServiceParam !== null && !$govServiceParam->delete()) {
                    $transaction->rollBack();
                    return false;
                }
            }
        } catch (Exception $e) {
            $transaction->rollBack();
            return false;
        }
        $transaction->commit();
        return true;
    }
}
