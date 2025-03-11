<?php

use app\commands\migrate\Migration;
use app\models\db\GovServices;
use app\models\db\Species;
use app\modules\soap\models\SpeciesServices;

/**
 * Class m240820_081152_update_species_services_table
 */
class m240820_081152_update_species_services_table extends Migration
{

    const TELEVETENARY_TYPE_ID = 18;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $services = GovServices::findAll(['id_service_type' => static::TELEVETENARY_TYPE_ID]);
        $species = Species::find()->all();
        Yii::$app->db->transaction->begin();
        try {
            foreach ($services as $service) {
                foreach ($species as $sp) {
                    $speciesServices = new SpeciesServices();
                    $speciesServices->id_service = $service->id;
                    $speciesServices->id_species = $sp->id;
                    if (!$speciesServices->save()) {
                        Yii::$app->db->transaction->rollBack();
                        return false;
                    }
                }
            }
        } catch (Exception $e) {
            Yii::$app->db->transaction->rollBack();
            return false;
        }
        Yii::$app->db->transaction->commit();
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $services = GovServices::findAll(['id_service_type' => static::TELEVETENARY_TYPE_ID]);
        Yii::$app->db->transaction->begin();
        try {
            foreach ($services as $service) {
                SpeciesServices::deleteAll(['id_service' => $service->id]);
            }
        } catch (Exception $e) {
            Yii::$app->db->transaction->rollBack();
            return false;
        }
        Yii::$app->db->transaction->commit();
        return true;
    }
}
