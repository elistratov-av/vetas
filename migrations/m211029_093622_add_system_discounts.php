<?php

use app\commands\migrate\Migration;

/**
 * Class m211029_093622_add_system_discounts
 */
class m211029_093622_add_system_discounts extends Migration
{
    private $newDiscounts = [
        'Приюты',
        'Поквартирные обходы'
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $systemUser = \app\models\db\Users::find()->where(['is_system_user' => true])->one()->id;
        foreach ($this->newDiscounts as $discountName) {
            $this->insert('discount', [
                'name' => $discountName,
                'value' => 100,
                'created_by' => $systemUser,
                'updated_by' => $systemUser,
                'is_system_discount' => true,
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        foreach($this->newDiscounts as $discountName) {
            Yii::$app->db->createCommand("DELETE FROM discount WHERE name = '$discountName'")->execute();
        }
    }
}
