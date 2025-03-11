<?php

use app\commands\migrate\Migration;

/**
 * Class m190214_105033_mosru_services
 */
class m190214_105033_mosru_services extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE services ALTER COLUMN id_pricelist DROP NOT NULL ");
        $this->addColumn('gov_services', 'id_service_goal', $this->integer());
        $this->addColumn('gov_services', 'at_home', $this->boolean()->defaultValue(false));
        $this->addColumn('gov_services', 'type', $this->string(20));

        $mosruServices = $this->db->createCommand("SELECT * FROM mosru_services ORDER BY id")->queryAll();
        foreach ($mosruServices as $service) {
            $mappedService = $this->getMappedService($service['id']);
            if (!$mappedService) {
                throw new Exception("Не найдено связанных услуг");
            }

            // переносим услугу из mosru_services в gov_services
            $this->insert('gov_services', [
                'name' => $service['name'],
                'price' => $mappedService['price'],
                'sort_by' => $service['sort_by'],
                'id_service_type' => $service['id_service_type'],
                'id_service_goal' => $service['id_service_goal'],
                'duration' => $mappedService['duration'],
                'cooldown' => $mappedService['cooldown'],
                'at_home' => $service['at_home'],
                'type' => 'mosru'
            ]);

            $serviceId = $this->db->getLastInsertID();

            // создаем связь созданной услуги с нужными видами животных (кошки, собаки)
            $species = $this->db->createCommand("
                select distinct id_species from species_services
                where 
                    id_service in (select distinct id_gov_services from mosru_services_gov_services where id_mosru_service = :id_mosru_service)
                    and id_species in (9, 25) --услуги mos.ru только для кошек/собак на данные момент. остальное лишнее
            ", [
                'id_mosru_service' => $service['id']
            ])->queryAll();

            foreach ($species as $row) {
                $this->insert('species_services', [
                    'id_species' => $row['id_species'],
                    'id_service' => $serviceId
                ]);
            }
        }

        $sql = <<<SQL
CREATE OR REPLACE VIEW public.mosru_services_view AS
 SELECT 
    id, id_service_goal, id_service_type, name, at_home, true AS at_clinic, sort_by
  FROM gov_services
  WHERE type = 'mosru'
  ORDER BY id;
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON VIEW public.mosru_services_view IS 'Услуги mos.ru'");
    }

    /**
     * @param $mosruServiceId
     * @return array|false
     * @throws \yii\db\Exception
     */
    protected function getMappedService($mosruServiceId)
    {
        return $this->db->createCommand("
                    select * from gov_services
                    where id in (
                        select id_gov_services 
                        from mosru_services_gov_services 
                        where id_mosru_service = :id_mosru_service and id_gov_services != 173 
                    )
                    order by duration desc limit 1
                ", [
                    'id_mosru_service' => $mosruServiceId
                ])->queryOne();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }
}
