<?php
/**
 * @author Serge Postrash <jexy.ru@gmail.com>
 */

namespace app\common\components\visitServiceReport\helpers;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use app\models\db\Breeds;
use app\models\db\FiasAddresses;
use app\models\db\IdentificationTypes;
use app\models\db\Organizations;
use app\models\db\Params;
use app\models\db\PetOwners;
use app\models\db\Pets;
use app\models\db\RegCertificates;
use app\models\db\Specialists;
use app\models\db\Species;
use app\models\db\VisitsSpecialists;

/**
 * Class VisitParamsDataHelper
 * @package app\modules\v1\models\reports
 */
class VisitParamsDataHelper extends Model
{
    use ParamsTrait;

    /**
     * @param string $indexBy
     * @return array
     */
    public static function findReportParamsForVisit($indexBy = 'id')
    {
        $q = new Query();
        $q->select('*')
            ->from(Params::tableName())
            ->where(['[[visit_flag]]' => true]);

        $q->orderBy(['[[id]]' => SORT_ASC])
            ->indexBy($indexBy);

        return $q->all();
    }

    /**
     * @param int $id_pet
     * @return array
     */
    private static function findPet($id_pet)
    {
        $q = new Query();
        $q->select('p.*')
            ->addSelect('[[s]].[[name]] species_name')
            ->addSelect('[[b]].[[name]] breed_name')
            ->from(Pets::tableName() . ' p')
            ->leftJoin(Species::tableName() . ' s', '[[s]].[[id]] = [[p]].[[id_species]]')
            ->leftJoin(Breeds::tableName() . ' b', '[[b]].[[id]] = [[p]].[[id_breed]]')
            ->where(['[[p]].[[id]]' => $id_pet]);

        return $q->one();
    }

    /**
     * @param int $id_owner
     * @return array
     */
    private static function findOwner($id_owner)
    {
        $q = new Query();
        $q->select('p.*')
            ->addSelect('[[cct]].[[phone]], [[cct]].[[owner_id]]')
            ->addSelect('[[addr]].[[full_address]] as address')
            ->from(PetOwners::tableName() . ' p')
            ->leftJoin(
                ['cct' => (new Query())
                    ->select('[[c]].[[name]] phone, [[c]].[[entity_id]] owner_id')
                    ->from('contacts c')
                    ->leftJoin('contact_types ct', '[[ct]].[[id]] = [[c]].[[id_contact_type]]')
                    ->where(['[[c]].[[entity_type]]' => 'pet_owner'])
                    ->andWhere(['[[ct]].[[type]]' => 'phone']),
                    '[[cct]].[[entity_id]] = [[p]].[[id]]',
                ],
                '[[p]].[[id]] = [[cct]].[[owner_id]]'
            )
            ->leftJoin(FiasAddresses::tableName() . ' addr', '[[addr]].[[id]] = [[p]].[[id_fias_address]]')
            ->where(['[[p]].[[id]]' => $id_owner]);

        return $q->one();
    }

    /**
     * @param int $id_visit
     * @return array
     */
    private static function findSpecialist($id_visit)
    {
        $q = new Query();
        $q->select('sp.*')
            ->addSelect('[[vs]].[[id_visit]]')
            ->from(Specialists::tableName() . ' sp')
            ->leftJoin(VisitsSpecialists::tableName() . ' vs', '[[vs]].[[id_specialist]] = [[sp]].[[id]]')
            ->where(['[[vs]].[[id_visit]]' => $id_visit])
        ;

        return $q->one();
    }

    /**
     * @param int $id_organization
     * @return array
     */
    private static function findOrganization($id_organization)
    {
        $q = new Query();
        $q->select('*')
            ->from(Organizations::tableName())
            ->where(['id' => $id_organization])
        ;

        return $q->one();
    }

    /**
     * @return array
     */
    private static function identificationTypes()
    {
        $rows = (new Query())
            ->from(IdentificationTypes::tableName())
            ->orderBy(['id' => SORT_ASC])
            ->all();

        return ArrayHelper::map($rows, 'id', 'name');
    }

    /**
     * VETAIS-2038
     * № и от – необходимо брать данные из регистрационного документа животного
     * @param int $id_pet
     * @return string
     */
    private static function findRegNumber($id_pet)
    {
        $row = (new Query())
            ->from(RegCertificates::tableName())
            ->where(['id_pet' => $id_pet])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(1)
            ->one();

        return empty($row) ? null : $row['number'];
    }

    /**
     * VETAIS-2038
     * № и от – необходимо брать данные из регистрационного документа животного
     * @param int $id_pet
     * @return string
     */
    private static function findRegDate($id_pet)
    {
        $row = (new Query())
            ->from(RegCertificates::tableName())
            ->where(['id_pet' => $id_pet])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(1)
            ->one();

        return empty($row) ? null : $row['date'];
    }
}
