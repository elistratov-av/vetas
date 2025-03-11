<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 03.07.19
 * Time: 12:50
 */

namespace app\modules\adminv\models\statistic;

use app\common\models\VisitStatus;
use app\models\db\found_pet\MessageSent;
use app\models\db\ShiftType;
use app\modules\admin\data\AdminDataProvider;
use app\modules\admin\models\Species;
use app\modules\admin\models\Visits;
use app\modules\soap\models\etp\status\Status1053;
use app\modules\soap\models\etp\status\Status8021;
use yii\db\Expression;
use yii\db\Query;

class SearchEventsReport extends Visits
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

        $mainQuery = MessageSent::find()
            ->alias('ms')
            ->select([
                'ms.type',
                'count(*) as total'
            ])
            ->leftJoin('found_pet.ads a', 'a.service_number = ms.service_number')
            ->leftJoin('found_pet.ad_authors aa', 'aa.id = a.id_author')
            ->leftJoin('species s', 'a.id_species = s.id')
            ->andWhere(['ms.response_code' => 200])
            ->andWhere(['in', 'ms.type', ['8021.1', '8021.2', '1075.3']])
            ->andWhere(['between','ms.created_at', $this->from, $this->to])
            ->groupBy(['ms.type'])
            ->orderBy(['ms.type' => SORT_ASC])
            ->indexBy('type')
            ->asArray();

        if(!empty($this->species)) {
            $mainQuery->andWhere(['a.id_species' => $this->species]);
         }

        if(!empty($this->owners)) {
            $mainQuery->andWhere(['a.id_author' => $this->owners]);
        }

        if(!empty($this->senders)) {
            if (in_array('system', $this->senders)) {
                $mainQuery->andWhere(['>', 'ms.id', 0]);
            }
            if (in_array('system', $this->senders) && in_array('inspector', $this->senders)) {
                $mainQuery->andWhere(['>', 'ms.id', 0]);
            }
            if (in_array('inspector', $this->senders) && !in_array('system', $this->senders)) {
                $mainQuery->andWhere(['<', 'ms.id', 0]);
            }
        }

       $data = $mainQuery->all();

        $returnData = [
            'from' => $this->from,
            'to' => $this->to,
            'data' => $data,
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
