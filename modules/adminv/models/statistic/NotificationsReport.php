<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 03.07.19
 * Time: 12:50
 */

namespace app\modules\adminv\models\statistic;

use app\common\models\VisitStatus;
use app\models\db\ShiftType;
use app\modules\admin\data\AdminDataProvider;
use app\modules\admin\models\Species;
use app\modules\admin\models\Visits;
use app\modules\soap\models\etp\status\Status1053;
use app\modules\soap\models\etp\status\Status8021;
use yii\db\Expression;
use yii\db\Query;

class NotificationsReport extends Visits
{
    protected $from;
    protected $to;
    protected $species;
    protected $owners;
    protected $senders;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['from', 'date', 'format' => 'php:Y-m-d'],
            ['to', 'date', 'format' => 'php:Y-m-d'],
        ];
    }


    /**
     * @return array
     */
    public function search()
    {
        if (empty($this->from)) {
            $this->from = date("Y-m") . '-01';
        }
        if (empty($this->to)) {
            $this->to = date('Y-m-d');
        }

        $mainQuery = (new Query())
            ->select([
                'count(case when l.event_code = \'initial_info_identification_and_vaccin\' then l.id else null end) as init_vacc_and_ident',
                'count(case when l.event_code = \'initial_info_vaccination\' then l.id else null end) as init_vacc',
                'count(case when l.event_code = \'initial_info_identification\' then l.id else null end) as init_ident',
                'count(case when l.event_code = \'remind_vaccination\' then l.id else null end) as rabies',
                'count(case when l.event_code = \'remind_vaccination_lepto\' then l.id else null end) as lepto',
                'count(case when l.event_code = \'remind_identification\' then l.id else null end) as ident',
                'count(case when l.event_code = \'quarantine\' then l.id else null end ) as notif_qua',
                'count(case when l.event_code = \'animal_found\' then l.id else null end) as found',
                'count(case when l.event_code = \'research\' then l.id else null end) as research',
                'count(case when vt.tech_name = \'V01_IDENT_LACK\' then v.id_violation else null end) as no_ident',
                'count(case when vt.tech_name = \'V03_VACCINATION_DEADLINE\' then v.id_violation else null end) as no_vacc',
                'count(case when vt.tech_name = \'V02_IDENT_REJECTION\' then v.id_violation else null end) as rej_ident',
                'count(case when vt.tech_name = \'V04_VACCINATION_REJECTION\' then v.id_violation else null end) as rej_vacc',
            ])
            ->from(['subscription.log l'])
            ->leftJoin('violation v', 'v.id_violation = l.id_violation')
            ->leftJoin('violation_type vt', 'v.id_type = vt.id_type')
            ->leftJoin('pet_owners po', 'l.id_owner = po.id')
            ->andWhere(['and',
                ['between', 'l.log_time', $this->from, $this->to],
            ])
            ->andWhere(['l.is_success' => true]) //Отображать успешно отправленные
        ;

        $recordedOwners = (new Query())
            ->select(['po.id', 'po.fullname'])
            ->from(['subscription.log l'])
            ->innerJoin('pet_owners po', 'po.id = l.id_owner')
            ->andWhere(['and',
                ['between', 'l.log_time', $this->from, $this->to],
            ])
            ->orderBy(['po.fullname' => SORT_ASC])
            ->distinct()
            ->all();
        $ownersOptions = [];
        foreach ($recordedOwners as $recordedOwner) {
            $ownersOptions[$recordedOwner['id']] = $recordedOwner['fullname'];
        }
        if (empty($ownersOptions)) {
            $ownersOptions = [0 => '(здесь некого выбирать)'];
        }

        if (!empty($this->species)) {
            $species = implode(',', $this->species);
            //На одно уведомление может приходиться несколько животных
            $mainQuery->andWhere(
                ['exists',
                    (new Query())
                        ->from('subscription.log l')
                        ->leftJoin('subscription.log_pets slp', 'l.id = slp.id_log')
                        ->leftJoin('pets p', 'slp.id_pet = p.id')
                        ->leftJoin('species s', 'p.id_species = s.id')
                        ->andWhere("(s.id IN ($species))")
            ]);

        }

        if (!empty($this->owners)) {
            $mainQuery->andWhere(['po.id' => $this->owners]);
        }

        if (!empty($this->senders)) {
            //Система
            if (in_array('system', $this->senders) && !in_array('inspector', $this->senders)) {
                $mainQuery->andWhere(['l.id_initiator' => null]);
            }
            //Госинспектор
            if (in_array('inspector', $this->senders) && !in_array('system', $this->senders)) {
                $mainQuery->andWhere(['not', ['l.id_initiator' => null]]);
            }
            if (in_array('system', $this->senders) && in_array('inspector', $this->senders)) {
                // Выбраны все значения в мультиселекте
            }
        }

        $data = $mainQuery->all();
        $dataProvider = new AdminDataProvider([
            'pagination' => false,
            'query' => $data,
        ]);

        $returnData = [
            'dataProvider' => $dataProvider,
            'from' => $this->from,
            'to' => $this->to,
            'data' => $data,
            'ownersOptions' => $ownersOptions
        ];

        return $returnData;
    }

    /**
     * @param mixed $from
     */
    public function setFrom($from): void
    {
        $this->from = $from;
    }

    /**
     * @param mixed $to
     */
    public function setTo($to): void
    {
        $this->to = $to;
    }

    /**
     * @param mixed $species
     */
    public function setSpecies($species): void
    {
        $this->species = $species;
    }

    /**
     * @param mixed $owners
     */
    public function setOwners($owners): void
    {
        $this->owners = $owners;
    }

    /**
     * @param mixed $senders
     */
    public function setSenders($senders): void
    {
        $this->senders = $senders;
    }
}
