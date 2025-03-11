<?php

use app\commands\migrate\Migration;
use app\models\db\IdentificationTypes;
use app\models\db\PetIdentification;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\Console;

/**
 * Class m190827_083655_set_pet_identification_main_flag
 */
class m190827_083655_set_pet_identification_main_flag extends Migration
{
    /**
     * Задача:
     * Нужно подготовить миграцию на установку флага основной у идентификатора животного,
     * для животных с идентификаторами но без признака "основной" у одного из:
     *   1) Если есть 1 чип - он является основным
     *   2) Если есть 2 чипа - самому "свежему" проставить признак "основной"
     *   3) Нет чипа, но есть другой идентификатор - проставить любому.
     *
     */
    public function safeUp()
    {
        /*
         * ID type ЧИП
         */
        $chip_id = IdentificationTypes::findIdentificationTypeChipId();

        /*
         * Инфо
         */
        Console::output('COUNT "normal" pets: ' . $this->getQueryPetsWithIdent()->count());
        Console::output('COUNT pets for process: ' . $this->getQueryPetsWithoutIdent()->count());


        /*
         * Проставляем всем, у кого есть только один маркер - там и разбираться не надо
         */
        $query_pets_with_one_ident = $this
            ->getQueryPetsWithoutIdent()
            ->having(new Expression('count(*) = 1'));

        $count_pets_with_one_ident = (clone $query_pets_with_one_ident)->count();
        Console::output('COUNT pets [with ONE ident]: ' . $count_pets_with_one_ident);

        // Проставляем флаг
        $count_updated_pets_with_one_ident = PetIdentification::updateAll(
            ['main_flag' => true],
            ['IN', 'id_pet', $query_pets_with_one_ident]
            );

        Console::output('COUNT UPDATED pets [with ONE ident]: ' . $count_updated_pets_with_one_ident);


        /*
         * Разбираемся с чипами
         */

        // Список ID чипов, ранжированных по дате (ранжирование по каждому животному отдельно)
        $id_ranked_chips = (new Query())
            ->select('id')
            ->from(
                [
                    'pr' => $this
                    ->getQueryIndentIdRankedByDatesOverByPets()
                    ->andWhere(['id_ident_type' => $chip_id])
            ])->where([
                'pr.rank' => 1
            ]);

        // Проставляем флаг
        $count_updated_pets_with_chip_ident = PetIdentification::updateAll(
            ['main_flag' => true],
            ['IN', 'id', $id_ranked_chips]
        );

        Console::output('COUNT UPDATED pets [with CHIP ident]: ' . $count_updated_pets_with_chip_ident);

        /*
         * Разбираемся с остальными способами идентификации
         */
        // Список ID остальных способов идентификации, ранжированных по дате (ранжирование по каждому животному отдельно)
        $id_ranked_other_ident = (new Query())
            ->select('id')
            ->from(
                [
                    'pr' => $this
                        ->getQueryIndentIdRankedByDatesOverByPets()
                        ->andWhere(['<>', 'id_ident_type', $chip_id])
                ])->where([
                'pr.rank' => 1
            ]);

        // Проставляем флаг
        $count_updated_pets_with_other_ident = PetIdentification::updateAll(
            ['main_flag' => true],
            ['IN', 'id', $id_ranked_other_ident]
        );

        Console::output('COUNT UPDATED pets [with OTHER ident]: ' . $count_updated_pets_with_other_ident);
    }

    /**
     * Запрос - список животных,
     * у которых есть маркер с признаком "Основной"
     *
     * @return \yii\db\ActiveQuery
     */
    protected function getQueryPetsWithIdent()
    {
        return PetIdentification::find()
            ->select('id_pet')
            ->distinct('id_pet')
            ->where([
                'main_flag' => true
            ]);
    }

    /**
     * Запрос - список животных,
     * у которых нет ни одного маркера с признаком "Основной"
     * @return \yii\db\ActiveQuery
     */
    protected function getQueryPetsWithoutIdent()
    {
        return PetIdentification::find()
            ->select('id_pet')
            // Просто исключаем тех, у кого есть маркер с признаком "Основной"
            ->where(['NOT IN', 'id_pet', $this->getQueryPetsWithIdent()])
            ->groupBy('id_pet');
    }

    /**
     * Запрос - список ID МАРКЕРОВ
     * (для списка животных, у которых нет ни одного маркера с признаком "Основной")
     * ранжированных по дате обновления/создания
     * (ранжирование идет в рамках каждого животного отдельно)
     *
     * @return \yii\db\ActiveQuery
     */
    protected function getQueryIndentIdRankedByDatesOverByPets()
    {
        $query_pets_with_multi_ident = $this
            ->getQueryPetsWithoutIdent()
            ->having(new Expression('count(*) > 1'));

        return PetIdentification::find()
            ->select([
                'id',
                new Expression(
                    'rank() OVER (PARTITION BY id_pet ORDER BY updated_at DESC, created_at DESC) as rank'
                )
            ])
            ->where([
                'IN', 'id_pet', $query_pets_with_multi_ident
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190827_083655_set_pet_identification_main_flag cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190827_083655_set_pet_identification_main_flag cannot be reverted.\n";

        return false;
    }
    */
}
