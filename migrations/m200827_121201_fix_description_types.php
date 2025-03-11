<?php


use app\models\db\VisitDescriptions;
use app\models\db\DescriptionTypes;
use yii\db\Migration;

class m200827_121201_fix_description_types extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        echo 'Исправление поля "Симптомы" на "Симпотмы"' . PHP_EOL;
        $this->update(
            VisitDescriptions::tableName(),
            ['id_description_type' => 7],
            ['id_description_type' => 2]
        );

        echo 'Исправление поля "Предварительнвй диагноз" на "Предварительный диагноз"' . PHP_EOL;
        $this->update(
            VisitDescriptions::tableName(),
            ['id_description_type' => 8],
            ['id_description_type' => 3]
        );

        echo 'Исправление поля "Рекомендации " на "Рекомендации"' . PHP_EOL;
        $this->update(
            VisitDescriptions::tableName(),
            ['id_description_type' => 6],
            ['id_description_type' => 9]
        );

        echo 'Удаление неиспользуемых типов полей' . PHP_EOL;
        $this->delete(
            DescriptionTypes::tableName(),
            ['id' => [2, 3, 9]]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }

}