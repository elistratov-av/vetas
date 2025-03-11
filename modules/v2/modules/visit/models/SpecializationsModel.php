<?php

namespace app\modules\v2\modules\visit\models;

use app\models\db\Specializations;
use app\modules\v2\modules\visit\skeletons\specializations\Lists;

/**
 * Class SpecializationsModel
 * @package app\modules\v2\modules\visit\models
 */
class SpecializationsModel
{
    /**
     * По состоянию на 14.03.2019 фактически не используется на фронте, пользуются v1
     * После удаления specialists_specializations не будет работать!!!
     *
     * @param int $idOrganization
     * @param int $page
     * @param int $limit
     * @return Lists
     * @throws \yii\db\Exception
     */
    public function list(int $idOrganization, int $page = 1, int $limit = 10): Lists
    {
        $connection = \Yii::$app->getDb();
        $query = $connection->createCommand("
            SELECT
                   specializations.id,
                   specializations.name,
                   specializations.description,
                   count(*) AS specialist_count
            FROM
                specialists
            JOIN specialists_specializations ON specialists_specializations.id_specialist = specialists.id
            JOIN specializations ON specializations.id = specialists_specializations.id_specialization
            WHERE
                (specialists.expel_date IS NULL OR  specialists.expel_date > NOW())
                AND
                specialists.id_organization = {$idOrganization}
             
            GROUP BY specializations.id, specializations.name, specializations.description, service_count
            ORDER BY specializations.name
        ")->queryAll();
        $result = new Lists($query);
        $result->customPagination($page, $limit);
        return $result;
    }

    /**
     * @return array
     * @throws \yii\db\Exception
     */
    public function all(): array
    {
        return Specializations::find()->orderBy(['name' => SORT_ASC])->all();
    }
}
