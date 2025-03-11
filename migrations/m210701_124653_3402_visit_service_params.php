<?php

use app\commands\migrate\Migration;
use app\models\db\DescriptionTypes;
use yii\db\Expression;

/**
 * Class m210701_124653_3402_visit_service_params
 */
class m210701_124653_3402_visit_service_params extends Migration
{
    const VISIT_SIMPTOMY_2 = 'VISIT_SIMPTOMY_2';
    const VISIT_KLINICHESKIE_PRIZNAKI = 'VISIT_KLINICHESKIE_PRIZNAKI';

    /**
     * {@inheritdoc}
     * @throws \yii\db\Exception
     */
    public function safeUp()
    {
        /*
         * Задача
         *
         * 1) убрать в настройках услуг у всех услуг поля СИМПТОМЫ, КЛИНИЧЕСКИЕ ПРИЗНАКИ, что бы у новых не появлялось
         * 1.1) ДОБАВИТЬ тем услугам, у которых БЫЛИ поля СИМПТОМЫ ИЛИ КЛИН ПРИЗНАКИ поле ДАННЫЕ КЛИНИЧЕСКОГО ОСМОТРА
         * 1.1.1) Если у услуги ХОТЯ БЫ ОДНО из полей Симптомы или Клин признаки было обязательным, поле Данные клин осмотра должно быть обязательным
         *
         * 2) у старых ОБЪЕДИНИТЬ ПОЛЯ СИМПТОМЫ, КЛИНИЧЕСКИЕ ПРИЗНАКИ перенести в ДАННЫЕ КЛИНИЧЕСКОГО ОСМОТРА
         * (или скопоновать их, если было несколько/уже был ДАННЫЕ КЛИНИЧЕСКОГО ОСМОТРА)
         */

        $new = (new DescriptionTypes([
            'name' => 'Данные клинического осмотра',
            'entity_type' => 'visit',
            'sort_by' => '1',
            'tech_name' => 'VISIT_CLINICAL_DATA'
        ]));

        if (!$new->save()){
            var_dump($new->getErrors());
            throw new \yii\db\Exception('Cant save new description_type');
        }
        $new_id = $new->id;
        if (empty($new_id)){
            throw new \yii\db\Exception('Cant get ID for new description_type');
        }

        /*
         * Ищем все услуги у которых уже есть один из указанных параметров
         */
        $old_services_description_types  = (new \yii\db\Query())
            ->select([
                'sdt.id_service',
                new Expression('BOOL_OR(sdt.required) as required'),
            ])
            ->from('public.services_description_types AS sdt')
            ->leftJoin('description_types dt', 'dt.id = sdt.id_description_type')
            ->where([
                'IN', 'tech_name', [
                    self::VISIT_KLINICHESKIE_PRIZNAKI,
                    self::VISIT_SIMPTOMY_2
                ]
            ])
            ->groupBy('sdt.id_service')
            ->all()
        ;

        /*
         * Связываем услуги с новым description_type
         */
        foreach ($old_services_description_types AS $sdt) {
            $new_sdt = (new \app\models\db\ServicesDescriptionTypes([
                'id_service' => $sdt['id_service'],
                'id_description_type' => $new_id,
                'required' => $sdt['required'],
            ]));
            if (!$new_sdt->save()){
                var_dump($new_sdt->getErrors());
                throw new \yii\db\Exception('Cant save new services_description_types');
            }
        }

        /*
         * Вставляем записи с новым id
         */
        $sql = "INSERT INTO visit_descriptions (description, id_visit, id_description_type, created_by, updated_by, created_at, updated_at, id_pet)
SELECT
       coalesce(vd.description::text, ' '::text) || ' ' || coalesce(vd_k.description::text, ' '::text) AS description,
       coalesce(vd.id_visit, vd_k.id_visit) AS id_visit,
        " . $new_id." AS id_description_type,
       coalesce(vd.created_by, vd_k.created_by) AS created_by,
       coalesce(vd.updated_by,vd_k.updated_by) AS updated_by,
       coalesce(vd.created_at,vd_k.created_at) AS created_at,
       coalesce(vd.updated_at,vd_k.updated_at) AS updated_at,
       coalesce(vd.id_pet,vd_k.id_pet) AS id_pet
FROM visit_descriptions vd
LEFT JOIN description_types dt on dt.id = vd.id_description_type
LEFT JOIN (
    SELECT vd_k.*
    FROM visit_descriptions vd_k
             LEFT JOIN description_types dt on dt.id = vd_k.id_description_type
    WHERE tech_name = 'VISIT_KLINICHESKIE_PRIZNAKI'
    ) as vd_k ON vd_k.id_visit = vd.id_visit AND vd_k.id_pet = vd.id_pet
WHERE tech_name = 'VISIT_SIMPTOMY_2';";

        $this->execute($sql);

        /*
         * Удаляем старые
         */
        $sql = <<<SQL
DELETE FROM
    visit_descriptions

WHERE id IN (SELECT visit_descriptions.id FROM visit_descriptions
LEFT JOIN description_types dt on visit_descriptions.id_description_type = dt.id
WHERE tech_name IN ('VISIT_SIMPTOMY_2', 'VISIT_KLINICHESKIE_PRIZNAKI'))
SQL;
        $this->execute($sql);

        /*
         * Удаляем привязку к старым
         */
        $sql = <<<SQL
DELETE FROM services_description_types WHERE id IN (
    SELECT sdt.id
    FROM public.services_description_types AS sdt
             LEFT JOIN description_types dt on dt.id = sdt.id_description_type
    WHERE tech_name IN ('VISIT_SIMPTOMY_2',
                        'VISIT_KLINICHESKIE_PRIZNAKI')
)
SQL;
        $this->execute($sql);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210701_124653_3402_visit_service_params cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210701_124653_3402_visit_service_params cannot be reverted.\n";

        return false;
    }
    */
}
