<?php

use app\commands\migrate\Migration;

/**
 * Class m210314_171700_3296_new_tmc_balance
 */
class m210314_171700_3296_new_tmc_balance extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('tmc.balance', [
            'id' => $this->primaryKey(),
            'id_tmc' => $this->integer()->notNull(),
            'type_tmc' => 'tmc.tmc_class_list NOT NULL',
            'id_organization' => $this->integer()->notNull(),
            'id_specialist' => $this->integer(),
            'id_production_form' => $this->integer(),

            'count' => $this->decimal(11, 2),
            'expiration_date' => $this->date(),
            'registration_date' => $this->date()->notNull(),
            'inventory_number' => $this->string()->notNull(),
            'price' => $this->decimal(8, 2),
            'equipment_condition' => $this->char(1),
            'manufactured_number' => $this->string(),

            // Старый ID для контроля переливки и корректировки связей. После - удалить
            'old_id' => $this->integer(),

            'created_at' => $this->timestamp(0),
            'created_by' => $this->integer(),
            'updated_at' => $this->timestamp(0),
            'updated_by' => $this->integer(),
        ]);

        $this->addCommentOnTable('tmc.balance', 'Баланс ТМЦ');

        $this->addCommentOnColumn('tmc.balance', 'id', 'id');
        $this->addCommentOnColumn('tmc.balance', 'id_tmc', 'id ТМЦ');
        $this->addCommentOnColumn('tmc.balance', 'type_tmc', 'Тип ТМЦ');
        $this->addCommentOnColumn('tmc.balance', 'id_organization', 'id организации, у которой на балансе стоит ТМЦ');
        $this->addCommentOnColumn('tmc.balance', 'id_specialist', 'id специалиста у которого на балансе стоит ТМЦ');
        $this->addCommentOnColumn('tmc.balance', 'id_production_form', 'id формы выпуска');

        $this->addCommentOnColumn('tmc.balance', 'count', 'Кол-во');
        $this->addCommentOnColumn('tmc.balance', 'expiration_date', 'Годен до');
        $this->addCommentOnColumn('tmc.balance', 'registration_date', 'Дата регистрации');
        $this->addCommentOnColumn('tmc.balance', 'inventory_number', 'Инвентарный номер');
        $this->addCommentOnColumn('tmc.balance', 'price', 'Цена');
        $this->addCommentOnColumn('tmc.balance', 'equipment_condition', 'Состояние (для оборудования)');
        $this->addCommentOnColumn('tmc.balance', 'manufactured_number', 'Заводской номер');

        $this->addCommentOnColumn('tmc.balance', 'created_at', 'Дата создания');
        $this->addCommentOnColumn('tmc.balance', 'created_by', 'Автор добавления');
        $this->addCommentOnColumn('tmc.balance', 'updated_at', 'Дата изменения');
        $this->addCommentOnColumn('tmc.balance', 'updated_by', 'Автор последнего изменения');

        $this->addCommentOnColumn('tmc.balance', 'old_id', 'Старый ID для контроля переливки и корректировки связей. После - удалить');

        // ПРОВЕРКИ
        $this->appendConstraints();

        $this->addForeignKey(
            'fk-tmc_balance-tmc_tmc',
            'tmc.balance',
            ['id_tmc', 'type_tmc'],
            'tmc.tmc',
            ['id', 'type'],
            'NO ACTION',
            'CASCADE'
        );

        $this->copyBalanceDrugs();
        $this->copyBalanceVaccines();
        $this->copyBalanceExpMaterials();
        $this->copyBalanceEquipments();

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('tmc.balance');
    }

    protected function copyBalanceEquipments()
    {
        $sql = "
        INSERT INTO tmc.balance 
        (id_tmc,type_tmc,id_organization,id_specialist,count,expiration_date,registration_date,
         inventory_number,price,equipment_condition,manufactured_number,old_id,created_at,created_by,updated_at,updated_by)
SELECT 
       id_equipment AS id_tmc,
       'equipment'::tmc.tmc_class_list AS type_tmc,
       id_organization,
       NULL AS id_specialist,
       NULL AS count,
       NULL AS expiration_date,
       registration_date,
       inventory_number,
       NULL AS price,
       equipment_condition,
       manufactured_number,
       id AS old_id,
       created_at,
       created_by,
       updated_at,
       updated_by
    FROM public.balance_equipments;";

        $this->execute($sql);
    }


    protected function copyBalanceExpMaterials()
    {
        $sql = "
        INSERT INTO tmc.balance 
        (id_tmc,type_tmc,id_organization,id_specialist,count,expiration_date,registration_date,
         inventory_number,price,equipment_condition,manufactured_number,old_id,created_at,created_by,updated_at,updated_by)
SELECT 
       id_exp_materials AS id_tmc,
       'exp_material'::tmc.tmc_class_list AS type_tmc,
       id_organization,
       NULL AS id_specialist,
       count,
       expiration_date,
       registration_date,
       inventory_number,
       price,
       NULL AS equipment_condition,
       NULL AS manufactured_number,
       id AS old_id,
       created_at,
       created_by,
       updated_at,
       updated_by
    FROM public.balance_exp_materials;";

        $this->execute($sql);
    }

    protected function copyBalanceVaccines()
    {
        $sql = "
        INSERT INTO tmc.balance 
        (id_tmc,type_tmc,id_organization,id_specialist,count,expiration_date,registration_date,
         inventory_number,price,equipment_condition,manufactured_number,old_id,created_at,created_by,updated_at,updated_by)
SELECT 
       id_vaccine AS id_tmc,
       'vaccine'::tmc.tmc_class_list AS type_tmc,
       id_organization,
       NULL AS id_specialist,
       dose_count,
       expiration_date,
       registration_date,
       inventory_number,
       price,
       NULL AS equipment_condition,
       NULL AS manufactured_number,
       id AS old_id,
       created_at,
       created_by,
       updated_at,
       updated_by
    FROM public.balance_vaccines;";

        $this->execute($sql);
    }

    protected function copyBalanceDrugs()
    {
        $sql = "
        INSERT INTO tmc.balance 
        (id_tmc,type_tmc,id_organization,id_specialist,count,expiration_date,registration_date,
         inventory_number,price,equipment_condition,manufactured_number,old_id,created_at,created_by,updated_at,updated_by)
SELECT 
       id_drug AS id_tmc,
       'drug'::tmc.tmc_class_list AS type_tmc,
       id_organization,
       NULL AS id_specialist,
       dose_count,
       expiration_date,
       registration_date,
       inventory_number,
       price,
       NULL AS equipment_condition,
       NULL AS manufactured_number,
       id AS old_id,
       created_at,
       created_by,
       updated_at,
       updated_by
    FROM public.balance_drugs;
";
        $this->execute($sql);
    }

    protected function appendConstraints()
    {
        $this->execute("
ALTER TABLE tmc.balance 
  ADD CONSTRAINT count_check 
    CHECK (
        type_tmc NOT IN ('vaccine'::tmc.tmc_class_list, 'drug'::tmc.tmc_class_list, 'exp_material'::tmc.tmc_class_list)
            OR 
            (
            type_tmc IN ('vaccine'::tmc.tmc_class_list, 'drug'::tmc.tmc_class_list, 'exp_material'::tmc.tmc_class_list)
                AND  
            count IS NOT NULL
            )
        )");

        // оборудование не продается. И что бы не попало в чек случайно
        $this->execute("
ALTER TABLE tmc.balance 
  ADD CONSTRAINT price_check 
    CHECK (
            (
                type_tmc NOT IN ('vaccine'::tmc.tmc_class_list, 'drug'::tmc.tmc_class_list, 'exp_material'::tmc.tmc_class_list)
                AND 
                price IS NULL
            ) 
            OR 
            (
            type_tmc IN ('vaccine'::tmc.tmc_class_list, 'drug'::tmc.tmc_class_list, 'exp_material'::tmc.tmc_class_list)
                AND  
            price >= 0
            )
        )");

        $this->execute("
ALTER TABLE tmc.balance 
  ADD CONSTRAINT equipment_condition_check 
    CHECK (
        (type_tmc <> 'equipment'::tmc.tmc_class_list AND equipment_condition IS NULL)
            OR
        (type_tmc = 'equipment'::tmc.tmc_class_list AND equipment_condition IS NOT NULL)  
        )");

    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210314_171700_3296_new_tmc_balance cannot be reverted.\n";

        return false;
    }
    */
}
