<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 06.06.19
 * Time: 13:09
 */

namespace app\common\components\stat;


use app\common\models\VisitStatus;
use app\models\db\ShiftType;
use app\models\db\Specialists;
use app\models\db\Visits;
use yii\base\Model;
use yii\console\ExitCode;
use yii\helpers\Console;


/**
 * Class AmbulanceStat
 * @package app\common\components\stat
 *
 * @property string $date
 */
class AmbulanceStat extends Model
{
    public $date;
    protected $ambulanceChannelId;
    protected $fullname;

    public function init()
    {
        $this->ambulanceChannelId = ShiftType::find()
            ->select('id')
            ->where(['type' => ShiftType::ASSIGN_SHIFT_TYPE_FOR_AMBULANCE])
            ->scalar();
        if (empty($this->abmulance_channel_id)){
            print("Error. Channel not found.\n");

            return ExitCode::DATAERR;
        }
        return ExitCode::OK;
    }


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
     * @throws \Exception
     * @throws \Throwable
     */
    public function make()
    {
        $statDb = $this->getStatDb();
        $masterDb = $this->getMasterDb();
        $specs = Specialists::find()->all($statDb);


        $sql = <<<SQL
INSERT INTO statistic.ambulance_report (
    id_specialist, spec_name, date, total_calls, total_cancelled, cancelled_by_owner, cancelled_by_org, total_house_calls, 
    total_commercial, total_free_for_blind, total_free_for_the_rest
) VALUES (
    :id_specialist, :spec_name, :date, :total_calls, :total_cancelled, :cancelled_by_owner, :cancelled_by_org, 
    :total_house_calls, :total_commercial, :total_free_for_blind, :total_free_for_the_rest
)
ON CONFLICT (id_specialist, date) DO UPDATE SET 
  id_specialist = :id_specialist,
  spec_name = :spec_name,
  total_calls = :total_calls, 
  total_cancelled = :total_cancelled, 
  cancelled_by_owner = :cancelled_by_owner, 
  cancelled_by_org = :cancelled_by_org, 
  total_house_calls = :total_house_calls, 
  total_commercial = :total_commercial,
  total_free_for_blind = :total_free_for_blind, 
  total_free_for_the_rest = :total_free_for_the_rest
SQL;

        Console::output(Console::ansiFormat("Обновление статистики за {$this->date}", [
            Console::FG_YELLOW, Console::BOLD
        ]));

        foreach ($specs as $spec) {
            $this->fullname = $spec->user->fullname ?? "Неизвестный специалист #" . $spec->id;
            Console::output(Console::ansiFormat("Обновление статистики за {$this->date} для #{$spec->id} {$this->fullname}", [
                Console::FG_GREEN
            ]));
            $masterDb->transaction(function() use ($spec , $sql, $masterDb){
                $masterDb->createCommand($sql, [
                    'id_specialist' => $spec->id,
                    'spec_name' => $this->fullname,
                    'date' => $this->date,
                    'total_calls' => $this->getTotalCalls($spec, $this->date),
                    'total_cancelled' => $this->getTotalCancelled($spec, $this->date),
                    'cancelled_by_owner' => $this->getCancelledByOwner($spec, $this->date),
                    'cancelled_by_org' => $this->getCancelledByOrg($spec, $this->date),
                    'total_house_calls' => $this->getTotalHouseCalls($spec, $this->date),
                    'total_commercial' => $this->getTotalCommercial($spec, $this->date),
                    'total_free_for_blind' => $this->getTotalFreeForBlind($spec, $this->date),
                    'total_free_for_the_rest' => $this->getTotalFreeForTheRest($spec, $this->date),
                ])->execute();
            });
        }
    }

    /**
     * Получение списка имен для специалистов
     *
     * @param Specialists $specialist Специалист, имя которого получаем
     * @return int
     * @throws \yii\db\Exception
     */
    public function getSpecName(Specialists $specialist)
    {
        $sql = <<<SQL
SELECT u.fullname
FROM specialists AS s
JOIN users AS u ON u.id = s.id_user
SQL;
        return (int)$this->getStatDb()->createCommand($sql, [
            'id_specialist' => $specialist->id
        ])->queryScalar();
    }


    /**
     * Подсчет общего кол-ва обращений для бригады (специалиста) по определенной дате
     * Определяется по дате создания обращения
     *
     * @param Specialists $specialist Специалист, имя которого получаем
     * @param string $date Дата в формате Y-m-d
     * @return int
     * @throws \yii\db\Exception
     */
    public function getTotalCalls(Specialists $specialist, string $date)
    {
        $sql = <<<SQL
SELECT count(*) 
FROM visits AS v, visits_specialists AS vs
WHERE 
    v.id = vs.id_visit
    AND vs.id_specialist = :id_specialist
    AND v.channel = :channel
    AND v.fact_start_dttm::date = :date
SQL;
        return (int)$this->getStatDb()->createCommand($sql, [
            ':id_specialist' => $specialist->id,
            ':channel' => $this->ambulanceChannelId,
            ':date' => $date
        ])->queryScalar();
    }


    /**
     * @param Specialists $specialist
     * @param string $date
     * @return int
     * @throws \yii\db\Exception
     */
    public function getTotalCancelled(Specialists $specialist, string $date)
    {
        $sql = <<<SQL
SELECT count(*) 
FROM visits AS v, visits_specialists AS vs
WHERE 
    v.id = vs.id_visit
    AND vs.id_specialist = :id_specialist
    AND v.channel = :channel 
    AND v.status = :status
    AND v.fact_start_dttm::date = :date
SQL;
        return (int)$this->getStatDb()->createCommand($sql, [
            ':channel' => $this->ambulanceChannelId,
            ':status' => VisitStatus::CANCELED,
            ':id_specialist' => $specialist->id,
            ':date' => $date
        ])->queryScalar();
    }

    /**
     * Подсчет общего кол-ва отмененных по инициативе пользователя обращений для бригады (специалиста) по определенной дате
     * Определяется по дате создания обращения
     *
     * @param Specialists $specialist Специалист, имя которого получаем
     * @param string $date Дата в формате Y-m-d
     * @return int
     * @throws \yii\db\Exception
     */
    public function getCancelledByOwner(Specialists $specialist, string $date)
    {
        $sql = <<<SQL
SELECT count(*) 
FROM visits AS v, visits_specialists AS vs
WHERE 
    v.id = vs.id_visit
    AND vs.id_specialist = :id_specialist
    AND v.channel = :channel
    AND v.status = :status
    AND v.cancel_initiator = :initiator
    AND v.fact_start_dttm::date = :date
SQL;
        return (int)$this->getStatDb()->createCommand($sql, [
            ':id_specialist' => $specialist->id,
            ':channel' => $this->ambulanceChannelId,
            ':status' => VisitStatus::CANCELED,
            ':initiator' => Visits::INITIATOR_IS_OWNER,
            'date' => $date
        ])->queryScalar();
    }

    /**
     * Подсчет общего кол-ва отмененных по инициативе клиники обращений для бригады (специалиста) по определенной дате
     * Определяется по дате создания обращения
     *
     * @param Specialists $specialist Специалист, имя которого получаем
     * @param string $date Дата в формате Y-m-d
     * @return int
     * @throws \yii\db\Exception
     */
    public function getCancelledByOrg(Specialists $specialist, string $date)
    {
        $sql = <<<SQL
SELECT count(*) 
FROM visits AS v, visits_specialists AS vs
WHERE 
    v.id = vs.id_visit
    AND vs.id_specialist = :id_specialist
    AND v.channel = :channel
    AND v.status = :status
    AND v.cancel_initiator = :initiator
    AND v.fact_start_dttm::date = :date
SQL;
        return (int)$this->getStatDb()->createCommand($sql, [
            ':id_specialist' => $specialist->id,
            ':channel' => $this->ambulanceChannelId,
            ':status' => VisitStatus::CANCELED,
            ':initiator' => Visits::INITIATOR_IS_CLINIC,
            ':date' => $date
        ])->queryScalar();
    }

    /**
     * Подсчет общего кол-ва выездов на дом для бригады (специалиста) по определенной дате
     * Определяется по дате создания обращения
     *
     * @param Specialists $specialist Специалист, имя которого получаем
     * @param string $date Дата в формате Y-m-d
     * @return int
     * @throws \yii\db\Exception
     */
    public function getTotalHouseCalls(Specialists $specialist, string $date)
    {
        $sql = <<<SQL
SELECT count(*) 
FROM visits AS v, visits_specialists AS vs
WHERE 
    v.id = vs.id_visit
    AND vs.id_specialist = :id_specialist
    AND v.channel = :channel
    AND v.status = :status
    AND v.fact_start_dttm::date = :date
SQL;
        return (int)$this->getStatDb()->createCommand($sql, [
            ':id_specialist' => $specialist->id,
            ':channel' => $this->ambulanceChannelId,
            ':status' => VisitStatus::FINISHED,
            ':date' => $date
        ])->queryScalar();
    }

    /**
     * Подсчет общего кол-ва платных выездов на дом для бригады (специалиста) по определенной дате
     * Определяется по дате создания обращения
     *
     * @param Specialists $specialist Специалист, имя которого получаем
     * @param string $date Дата в формате Y-m-d
     * @return int
     * @throws \yii\db\Exception
     */
    public function getTotalCommercial(Specialists $specialist, string $date)
    {
        $sql = <<<SQL
SELECT count(isWithDiscount) from (
  SELECT 
    vs.id_specialist, 
    max(vg.apply_discount::integer) as isWithDiscount
  FROM visits v
  LEFT JOIN visits_gov_services vg ON vg.id_visit = v.id
  LEFT JOIN visits_specialists vs ON vs.id_visit = v.id
  WHERE 
        vs.id_specialist = :id_specialist
        AND v.channel = :channel
        AND v.status = :status
        AND v.fact_start_dttm::date = :date
    GROUP BY v.id, vs.id_specialist) t
WHERE 
  isWithDiscount = 0
GROUP BY t.id_specialist
SQL;
        return (int)$this->getStatDb()->createCommand($sql, [
            ':id_specialist' => $specialist->id,
            ':channel' => $this->ambulanceChannelId,
            ':status' => VisitStatus::FINISHED,
            ':date' => $date
        ])->queryScalar();
    }

    /**
     * Подсчет общего кол-ва льготных выездов на дом к незрячим для бригады (специалиста) по определенной дате
     * Определяется по дате создания обращения
     *
     * @param Specialists $specialist Специалист, имя которого получаем
     * @param string $date Дата в формате Y-m-d
     * @return int
     * @throws \yii\db\Exception
     */
    public function getTotalFreeForBlind(Specialists $specialist, string $date)
    {
        $sql = <<<SQL
SELECT count(isWithBlindDiscount) FROM(
    SELECT 
      vs.id_specialist, v.id,
      coalesce(max(d.is_for_blind::integer), 0) AS isWithBlindDiscount
    FROM visits v
    LEFT JOIN visits_gov_services vg ON vg.id_visit = v.id
    LEFT JOIN visits_specialists vs ON vs.id_visit = v.id
    LEFT JOIN visit_price vp ON vp.id_visit = v.id
    LEFT JOIN discount d ON d.id = vp.id_discount
    WHERE 
        v.channel = :channel
        AND v.status = :status
        AND v.fact_start_dttm::date = :date
        AND vs.id_specialist = :id_specialist
    GROUP BY vs.id_specialist, v.id
    ORDER BY vs.id_specialist) t
WHERE 
  isWithBlindDiscount > 0 
GROUP BY t.id_specialist
SQL;
        return (int)$this->getStatDb()->createCommand($sql, [
            ':id_specialist' => $specialist->id,
            ':channel' => $this->ambulanceChannelId,
            ':status' => VisitStatus::FINISHED,
            ':date' => $date
        ])->queryScalar();
    }


    /**
     * Подсчет общего кол-ва льготных выездов на дом ко всем остальным для бригады (специалиста) по определенной дате
     * Определяется по дате создания обращения
     *
     * @param Specialists $specialist Специалист, имя которого получаем
     * @param string $date Дата в формате Y-m-d
     * @return int
     * @throws \yii\db\Exception
     */
    public function getTotalFreeForTheRest(Specialists $specialist, string $date)
    {
        $sql = <<<SQL
SELECT count(t.isWithDiscounts)
FROM (
    SELECT 
      vs.id_specialist, 
      max(vg.apply_discount::integer) AS isWithDiscounts, 
      coalesce(max(d.is_for_blind::integer), 0) AS isWithBlindDiscounts
    FROM visits v
    LEFT JOIN visits_gov_services vg ON vg.id_visit = v.id
    LEFT JOIN visits_specialists vs ON vs.id_visit = v.id
    LEFT JOIN visit_price vp ON vp.id_visit = v.id
    LEFT JOIN discount d ON d.id = vp.id_discount
    WHERE 
        v.channel = :channel
        AND v.status = :status
        AND v.fact_start_dttm::date = :date
        AND vs.id_specialist = :id_specialist
    GROUP BY vs.id_specialist, v.id
    ORDER BY vs.id_specialist ) t
WHERE 
    isWithDiscounts > 0
    AND isWithBlindDiscounts = 0
GROUP BY t.id_specialist, t.isWithDiscounts
SQL;
        return (int)$this->getStatDb()->createCommand($sql, [
            ':id_specialist' => $specialist->id,
            ':channel' => $this->ambulanceChannelId,
            ':status' => VisitStatus::FINISHED,
            ':date' => $date
        ])->queryScalar();
    }
}