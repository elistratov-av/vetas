<?php

namespace app\common\components\reports\handlers\RequirementOrder\v1;

use app\common\components\reports\interfaces\AbctractDataProvider;
use app\models\db\tmc\BalanceAction;
use app\models\db\tmc\BalanceActionTmcList;
use DateTime;
use yii\db\Exception;

/**
 * Провайдер данных отчета.
 * Здесь должны буть основные методы, что подготавилают и возвращаю данные для отчета
 * Class DataProvider
 *
 * @package app\common\components\reports\handlers\RequirementOrder\v1
 */
class DataProvider extends AbctractDataProvider
{
    //ToDO перенести id в OrgTypes
    private static $SBBJ_ORG_TYPE_ID = 39;

    /**
     * @var  BalanceAction
     */
    private $balance_action;

    /**
     * @throws Exception
     */
    protected function init()
    {
        $this->balance_action =
            BalanceAction::find()
                ->where(['id' => $this->requestDto->id_balance_action,])
                ->one();

        if (!$this->balance_action) {
            throw new Exception('Данные для отчета с указанным id не найдены');
        }
    }

    /**
     * @return void
     */
    protected function initHash()
    {
        $this->hash = null;
    }

    /**
     * Дата отпуска ТМЦ
     *
     * @return  \DateTime
     */
    public function getInitiatorDate()
    {
        return $this->balance_action->initiator_date ? new DateTime($this->balance_action->initiator_date) : null;
    }

    /**
     * Название структурного подразделения
     *
     * @return  string
     */
    public function getToOrganizationName()
    {
        if ($this->balance_action->toOrganization->id_org_type === self::$SBBJ_ORG_TYPE_ID) {
            return '';
        } else {
            return $this->balance_action->toOrganization->name;
        }


    }

    /**
     * Список ТМЦ действия
     *
     * @return  BalanceActionTmcList[]
     */
    public function getActionTmcList()
    {
        return $this->balance_action->actionTmcList;
    }

    /**
     * Имя затребовавшего специалиста
     *
     * @return string
     */
    public function getInitiatorSpecialistName()
    {
        return $this->balance_action->initiatorSpecialist->getFullnameInitials();
    }
}
