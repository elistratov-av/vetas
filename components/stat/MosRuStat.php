<?php

namespace app\common\components\stat;

use app\models\db\Organizations;
use app\models\db\Visits;
use app\modules\soap\models\etp\status\Status1053;
use app\modules\soap\models\etp\status\Status1075;
use app\modules\soap\models\etp\status\Status1080_1;
use app\modules\soap\models\etp\status\Status1090;
use app\modules\soap\models\etp\status\Status8021;
use yii\base\Model;
use yii\helpers\Console;

/**
 * Class MosRuStat
 * @package app\common\components\stat
 *
 * @property string $date
 */
class MosRuStat extends Model
{
    public $date;

    public function rules()
    {
        return [
            ['date', 'date', 'format' => 'php:Y-m-d']
        ];
    }

    /**
     * TODO: Изменить на соединение с БД статистики
     * Соединение с БД
     * @return \yii\db\Connection
     */
    public function getStatDb()
    {
        return \Yii::$app->db;
    }

    /**
     * @return \yii\db\Connection
     */
    public function getMasterDb()
    {
        return \Yii::$app->db;
    }

    /**
     * @return Organizations[]
     */
    protected function findOrganizations()
    {
        $orgs = Organizations::find()
            ->joinWith('org_type')
            ->where([
                'org_types.is_tech' => false, // @see Organizations::excludeShelters
            ])
            ->all($this->getStatDb());

        return $orgs;
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function make()
    {
        $sql = <<<SQL
INSERT INTO statistic.mosru_organizations_stat (
    id_organization, date, total, canceled_by_owner, canceled_by_org, moved, 
    cats_visits, dogs_visits, other_visits, cats_finished_visits, dogs_finished_visits, other_finished_visits 
) VALUES (
    :id_organization, :date, :total, :canceled_by_owner, :canceled_by_org, :moved, 
    :cats_visits, :dogs_visits, :other_visits, :cats_finished_visits, :dogs_finished_visits, :other_finished_visits
)
ON CONFLICT (id_organization, date) DO UPDATE SET 
  total = :total,
  canceled_by_owner = :canceled_by_owner, 
  canceled_by_org = :canceled_by_org, 
  moved = :moved, 
  cats_visits = :cats_visits, 
  dogs_visits = :dogs_visits, 
  other_visits = :other_visits,
  cats_finished_visits = :cats_finished_visits, 
  dogs_finished_visits = :dogs_finished_visits,
  other_finished_visits = :other_finished_visits
SQL;

        Console::output(Console::ansiFormat("Обновление статистики за {$this->date}", [
            Console::FG_YELLOW, Console::BOLD
        ]));

        $masterDb = $this->getMasterDb();
        $orgs = $this->findOrganizations();

        foreach ($orgs as $org) {
            Console::output(Console::ansiFormat("Обновление статистики для #{$org->id} {$org->short_name}", [
                Console::FG_GREEN
            ]));

            $masterDb->transaction(function() use ($org, $sql, $masterDb){
                $masterDb->createCommand($sql, [
                    'id_organization' => $org->id,
                    'date' => $this->date,
                    'total' => $this->getTotalVisits($org, $this->date),
                    'canceled_by_owner' => $this->getCanceledByOwnerVisits($org, $this->date),
                    'canceled_by_org' => $this->getCanceledByOrganizationVisits($org, $this->date),
                    'moved' => $this->getMovedVisits($org, $this->date),
                    'cats_visits' => $this->getCatsVisits($org, $this->date),
                    'dogs_visits' => $this->getDogsVisits($org, $this->date),
                    'other_visits' => $this->getOtherVisits($org, $this->date),
                    'cats_finished_visits' => $this->getCatsFinishedVisits($org, $this->date),
                    'dogs_finished_visits' => $this->getDogsFinishedVisits($org, $this->date),
                    'other_finished_visits' => $this->getOtherFinishedVisits($org, $this->date),
                ])->execute();
            });
        }
    }

    /**
     * Подсчет общего кол-ва записей с mos.ru для организации по определенной дате
     * Определяется по дате подачи заявления с mos.ru
     *
     * @param Organizations $organization Организация для которой идет подсчет
     * @param string $date Дата в формате Y-m-d
     * @return int
     * @throws \yii\db\Exception
     */
    public function getTotalVisits(Organizations $organization, string $date)
    {
        $sql = <<<SQL
SELECT count(*) FROM etp.message AS e
JOIN visits AS v ON v.id = e.visit_id
WHERE 
    v.id_organization = :id_organization
    AND e.created_at::date = :date
SQL;
        return (int)$this->getStatDb()->createCommand($sql, [
            'id_organization' => $organization->id,
            'date' => $date
        ])->queryScalar();
    }

    /**
     * Подсчет статистики по записям с mos.ru отмененным по инициативе пользователя
     *
     * После введения на фронте возможности выбрать, кем была инициирована отмена,
     * определяется по наличию:
     * - статуса 1090 в логах (отменены с мос ру)
     * - 1080.1 (отмена из клиники) + поле cancel_initiator = Visits::INITIATOR_IS_OWNER у визита
     *
     * Примечание: при отмене пользователем из клиники - статусы все равно 1080.1
     *
     * Под датой статистики подразумевается дата отмены приема (дата когда отправили статус 1090/1080.1)
     *
     * @param Organizations $organization
     * @param string $date
     * @return int
     * @throws \yii\db\Exception
     */
    public function getCanceledByOwnerVisits(Organizations $organization, string $date)
    {
        $sql = <<<SQL
SELECT count(*)
FROM etp.status_log AS log
         JOIN visits AS v ON v.id = log.visit_id
WHERE v.id_organization = :id_organization
  AND (
        log.etp_status = :status_by_user
        OR
        (log.etp_status = :status_by_org AND v.cancel_initiator = :cancel_initiator)
    )
  AND log.log_time::date = :date
SQL;

        $ogr_status = new Status1080_1();

        return (int)$this->getStatDb()->createCommand($sql, [
            'id_organization' => $organization->id,
            'status_by_user' => Status1090::CODE,
            'status_by_org' => $ogr_status->getCode() . $ogr_status->getReasonCode(),
            'date' => $date,
            'cancel_initiator' => Visits::INITIATOR_IS_OWNER,
        ])->queryScalar();
    }

    /**
     * Подсчет статистики по записям с mos.ru отмененным по инициативе организации
     * Определяется по наличию статуса 1080.1 в логах.
     * Однако, после введения на фронте возможности выбрать, кем была инициирована отмена,
     * приходиться дополнительно проверять поле cancel_initiator у визита
     *
     * Под датой статистики подразумевается дата отмены приема (дата когда отправили статус 1080.1)
     *
     * @param Organizations $organization
     * @param string $date
     * @return int
     * @throws \yii\db\Exception
     */
    public function getCanceledByOrganizationVisits(Organizations $organization, string $date)
    {
        $sql = <<<SQL
SELECT count(*) FROM etp.status_log AS log
JOIN visits AS v ON v.id = log.visit_id
WHERE 
    v.id_organization = :id_organization
    AND v.cancel_initiator = :cancel_initiator
    AND log.etp_status = :status
    AND log.log_time::date = :date
SQL;

        $status = new Status1080_1();
        return (int)$this->getStatDb()->createCommand($sql, [
            'id_organization' => $organization->id,
            'status' => $status->getCode() . $status->getReasonCode(),
            'date' => $date,
            'cancel_initiator' => Visits::INITIATOR_IS_CLINIC,
        ])->queryScalar();
    }

    /**
     * Подсчет статистики по перенесенным(измененным) записям с mos.ru
     * Определяется по наличию статуса 8021 (по инициативе клиники) или 1053 (по инициативе пользователя) в логах.
     * Под датой статистики подразумевается дата изменения приема (дата когда отправили статус 8021 или 1168)
     *
     * @param Organizations $organization
     * @param string $date
     * @return int
     * @throws \yii\db\Exception
     */
    public function getMovedVisits(Organizations $organization, string $date)
    {
        $sql = <<<SQL
SELECT count(DISTINCT visit_id) FROM etp.status_log AS log
JOIN visits AS v ON v.id = log.visit_id
WHERE 
    v.id_organization = :id_organization
    AND (log.etp_status = :status8021 OR log.etp_status = :status1053)
    AND log.log_time::date = :date
SQL;
        return (int)$this->getStatDb()->createCommand($sql, [
            'id_organization' => $organization->id,
            'status8021' => Status8021::CODE,
            'status1053' => Status1053::CODE,
            'date' => $date
        ])->queryScalar();
    }

    /**
     * Подсчет общего кол-ва записей с mos.ru по кошкам для организации по определенной дате
     * Определяется по дате подачи заявления с mos.ru
     *
     * @param Organizations $organization
     * @param string $date
     * @return int
     * @throws \yii\db\Exception
     */
    public function getCatsVisits(Organizations $organization, string $date)
    {
        return $this->getTotalVisitsBySpeciesId($organization, $date, \Yii::$app->params['mosru']['species_ids']['cats']);
    }

    /**
     * Подсчет общего кол-ва записей с mos.ru по собакам для организации по определенной дате
     * Определяется по дате подачи заявления с mos.ru
     *
     * @param Organizations $organization
     * @param string $date
     * @return int
     * @throws \yii\db\Exception
     */
    public function getDogsVisits(Organizations $organization, string $date)
    {
        return $this->getTotalVisitsBySpeciesId($organization, $date, \Yii::$app->params['mosru']['species_ids']['dogs']);
    }

    /**
     * Подсчет общего кол-ва записей с mos.ru по иным животным для организации по определенной дате
     * Определяется по дате подачи заявления с mos.ru
     *
     * @param Organizations $organization
     * @param string $date
     * @return int
     * @throws \yii\db\Exception
     */
    public function getOtherVisits(Organizations $organization, string $date)
    {
        return $this->getTotalVisitsBySpeciesId($organization, $date, \Yii::$app->params['mosru']['species_ids']['other']);
    }

    /**
     * @param Organizations $organization
     * @param string $date
     * @param int $id_species
     * @return int
     * @throws \yii\db\Exception
     */
    protected function getTotalVisitsBySpeciesId(Organizations $organization, string $date, int $id_species)
    {
        $sql = <<<SQL
SELECT count(*) FROM etp.message AS e
JOIN visits AS v ON v.id = e.visit_id
JOIN pets AS p ON v.id_pet = p.id
WHERE 
    v.id_organization = :id_organization
    AND e.created_at::date = :date
    AND p.id_species = :id_species
SQL;
        return (int)$this->getStatDb()->createCommand($sql, [
            'id_organization' => $organization->id,
            'id_species' => $id_species,
            'date' => $date
        ])->queryScalar();
    }

    /**
     * Подсчет кол-ва завершенных приемов по кошкам, записанных с mos.ru
     * Определяется по наличию статуса 1075 в логах.
     * Под датой статистики подразумевается дата взятия приема в работу (дата когда отправили статус 1075)
     *
     * @param Organizations $organization
     * @param string $date
     * @return int
     * @throws \yii\db\Exception
     */
    public function getCatsFinishedVisits(Organizations $organization, string $date)
    {
        return $this->getFinishedVisitsBySpeciesId($organization, $date, \Yii::$app->params['mosru']['species_ids']['cats']);
    }

    /**
     * Подсчет кол-ва завершенных приемов по собакам, записанных с mos.ru
     * Определяется по наличию статуса 1075 в логах.
     * Под датой статистики подразумевается дата взятия приема в работу (дата когда отправили статус 1075)
     *
     * @param Organizations $organization
     * @param string $date
     * @return int
     * @throws \yii\db\Exception
     */
    public function getDogsFinishedVisits(Organizations $organization, string $date)
    {
        return $this->getFinishedVisitsBySpeciesId($organization, $date, \Yii::$app->params['mosru']['species_ids']['dogs']);
    }

    /**
     * Подсчет кол-ва завершенных приемов по иным животным, записанных с mos.ru
     * Определяется по наличию статуса 1075 в логах.
     * Под датой статистики подразумевается дата взятия приема в работу (дата когда отправили статус 1075)
     *
     * @param Organizations $organization
     * @param string $date
     * @return int
     * @throws \yii\db\Exception
     */
    public function getOtherFinishedVisits(Organizations $organization, string $date)
    {
        return $this->getFinishedVisitsBySpeciesId($organization, $date, \Yii::$app->params['mosru']['species_ids']['other']);
    }

    /**
     * @param Organizations $organization
     * @param string $date
     * @param int $id_species
     * @return int
     * @throws \yii\db\Exception
     */
    protected function getFinishedVisitsBySpeciesId(Organizations $organization, string $date, int $id_species)
    {
        $sql = <<<SQL
SELECT count(*) FROM etp.status_log AS log
JOIN visits AS v ON v.id = log.visit_id
JOIN pets AS p ON v.id_pet = p.id
WHERE 
    v.id_organization = :id_organization
    AND p.id_species = :id_species
    AND log.log_time::date = :date
    AND log.etp_status = :status
SQL;
        return (int)$this->getStatDb()->createCommand($sql, [
            'id_organization' => $organization->id,
            'id_species' => $id_species,
            'status' => Status1075::CODE,
            'date' => $date
        ])->queryScalar();
    }
}
