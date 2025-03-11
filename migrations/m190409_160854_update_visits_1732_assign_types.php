<?php

use app\commands\migrate\Migration;
use app\models\db\GovServices;
use app\models\db\Params;
use app\models\db\Visits;
use app\models\db\VisitServiceParamValues;
use app\models\db\VisitsGovServices;
use yii\db\Query;

/**
 * Class m190409_160854_update_visits_1732_assign_types
 */
class m190409_160854_update_visits_1732_assign_types extends Migration
{
    private $tableName = 'visits';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('{{%' . $this->tableName . '}}', ['type' => Visits::TYPE_VISIT]);

        $param = Params::findOne(['tech_name' => 'P0_Juraddress']);

        $query = (new Query())
            ->from('{{%' . $this->tableName . '}}')
            ->orderBy(['id' => SORT_ASC]);

        foreach ($query->each(100) as $record) {
            $magic = (new Query())
                ->select(['vgs.id', 'gs.cod'])
                ->from(VisitsGovServices::tableName() . ' vgs')
                ->leftJoin(GovServices::tableName() . ' gs', 'gs.id = vgs.id_service')
                ->where([
                    'gs.cod' => '0365',
                    'id_visit' => $record['id']
                ])
            ->one();

            if ($magic === false) {
                continue;
            }

            $columns = ['type' => Visits::TYPE_AT_HOME];

            $address = (new Query())
                ->from(VisitServiceParamValues::tableName())
                ->where([
                    'id_param' => $param->id,
                    'id_visitservice' => $magic['id']
                ])
                ->one();

            if ($address && !empty($address['char_value'])) {
                $columns['visit_to_address'] = $address['char_value'];
            }

            $this->update('{{%' . $this->tableName . '}}', $columns, ['id' => $record['id']]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->update('{{%' . $this->tableName . '}}', [
            'type' => null,
            'visit_to_address' => null,
        ]);
    }
}
