<?php

use app\commands\migrate\Migration;

/**
 * Class m210704_185518_add_system_discount
 */
class m210704_185518_add_system_discount extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $systemUser = \app\models\db\Users::find()->where(['is_system_user' => true])->one()->id;
        $this->addColumn('discount', 'is_system_discount', $this->boolean()->defaultValue(false)->comment('Флаг определяющий скидку как системную'));
        $this->insert('discount', [
            'name' => \app\models\db\Discount::VACCINE_STATION_DISCOUNT_NAME,
            'value' => 100,
            'created_by' => $systemUser,
            'updated_by' => $systemUser,
            'is_system_discount' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        Yii::$app->db->createCommand("DELETE FROM discount WHERE is_system_discount = 'system_user'")->execute();
        $this->dropColumn('discount', 'is_system_discount');
    }
}
