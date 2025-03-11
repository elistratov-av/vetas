<?php

use app\commands\migrate\Migration;
use app\models\db\DescriptionTypes;

/**
 * Class m190531_124644_1902_update_description_types_remove
 */
class m190531_124644_1902_update_description_types_remove extends Migration
{
    /**
     * {@inheritdoc}
     * @see \m190515_094748_add_more_visit_descriptions_1750
     */
    public function safeUp()
    {
        $descriptionTypes = DescriptionTypes::find()
            ->where(['entity_type' => 'visit'])
            ->andWhere(['in', 'name', ['Дополнительные исследования', 'Лечебная помощь']])
            ->asArray()
            ->all();

        foreach ($descriptionTypes as $descriptionType) {
            $this->db
                ->createCommand()
                ->delete(
                    'service_types_description_types',
                    [
                        'id_description_type' => $descriptionType['id'],
                    ]
                )
                ->execute();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190531_124644_1902_update_description_types_remove cannot be reverted.\n";

        return false;
    }
}
