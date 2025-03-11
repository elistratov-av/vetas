<?php


namespace app\modules\v2\modules\tmc\models;


use app\common\components\rbac\Role;
use app\models\db\Organizations;
use app\models\db\tmc\BalanceAction;
use yii\db\Expression;
use yii\db\Query;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;

/**
 * Class BalanceActionAccessRules
 * @package app\modules\v2\modules\tmc\models
 *
 */
class BalanceActionAccessRules
{

    /**
     * @param BalanceAction[] $list
     * @return BalanceAction[]
     * @throws ForbiddenHttpException
     * @throws \Throwable
     */
    public static function addAccessFlagsToList($list)
    {
        foreach ($list as &$item) {
            $item = (object)$item;
            /** @var BalanceAction $item */

            $item->allow_cancel = self::checkCancelTransfer($item, false);
            $item->allow_transfer = self::checkTransferToRequester($item, false);
            $item->allow_accept = self::checkConfirmTransfer($item, false);
        }

        unset($item);

        return $list;
    }

    /**
     * @param $balanceAction BalanceAction
     * @throws ForbiddenHttpException
     * @throws \Throwable
     */
    public static function checkCreateTransferRequest($balanceAction)
    {
        // нельзя запрашивать на имя чужой организации
        if ($balanceAction->to_id_organization != self::getSpecialistOrgId()) {
            throw new ForbiddenHttpException('Нельзя подавать заявку на имя сторонней организации');
        }
    }

    /**
     * @param $balanceAction
     * @return bool
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws \Throwable
     */
    public static function checkCreateTransferToBalance($balanceAction)
    {
        $user_technic = \Yii::$app->user->can(Role::ROLE_TECHNIC_MTO);
        $different_org = $balanceAction->from_id_organization != $balanceAction->to_id_organization;

        $from_org_balance = empty($balanceAction->from_id_specialist);
        $to_org_balance = empty($balanceAction->to_id_specialist);

        $from_my_balance = $balanceAction->from_id_specialist == self::getSpecialistId() && $balanceAction->from_id_organization == self::getSpecialistOrgId();
        $from_my_org = $balanceAction->from_id_organization == self::getSpecialistOrgId();
        $to_my_org = $balanceAction->to_id_organization == self::getSpecialistOrgId();

        /*
         * Можно работать с балансом своей организации (1)
         */
        if (!$from_my_org) {
            throw new ForbiddenHttpException('Нельзя передавать с баланса чужой организации');
        }

        /*
         * Передача между организациями
         * - передавать между организациями можно только "баланс организации" <> "баланс организации".
         *   Трогать при передачах между организациями балансы врачей - НЕЛЬЗЯ!
         * - передавать с баланса орг на баланс орг может только техник, при этом
         *      - в пределах своей сети
         *      - если работает в головной - то может передать головной другой
         *      - только с баланса своей организации
         */
        if ($different_org) {
            if (!$user_technic) {
                throw new ForbiddenHttpException('Передавать между организацями может только техник');
            }

            if (!$to_org_balance) {
                throw new ForbiddenHttpException('Нельзя передавать на баланс врача иной организации. Передайте сначала на баланс своей организации');
            }

            if (!$from_org_balance) {
                throw new ForbiddenHttpException('Нельзя передавать с баланса врача иной организации');
            }

            if (in_array($balanceAction->to_id_organization, self::getUserOrgList())) {
                return true; // в своей ветви
            }

            if (!self::isRootOrg($balanceAction->from_id_organization) && !self::isRootOrg($balanceAction->to_id_organization)) {
                throw new ForbiddenHttpException('Передавать на сторону можно только с баланса СББЖ на баланс другого СББЖ');
            }

            return true;
        }

        /*
         * С баланса организации может передавать
         * - только техник
         * - только в пределах своей организации (1)
         */
        if ($from_org_balance) {
            if (!$user_technic) {
                throw new ForbiddenHttpException('Передавать с баланса организации может только техник');
            }

            return true;
        } else {
            /*
             * Передача с баланса врача:
             *  - может врач и техник той организации, в которой работает врач
             *  Передача:
             *      - передавать врачу в своей организации
             *      - передавать на баланс своей  организации
             */
            if (!$to_my_org) {
                throw new ForbiddenHttpException('Передавать на баланс врача можно только в пределах своей организации');
            }

            if (!$from_my_balance && !$user_technic) {
                throw new ForbiddenHttpException('Передавать с чужого баланса может только техник');
            }
        }

        return true;
    }

    /**
     * @param $balanceAction BalanceAction
     * @throws ForbiddenHttpException
     * @throws \Throwable
     */
    public static function checkWriteOff($balanceAction)
    {
        /*
         * Списать можно
         * - со своего баланса
         * - если я техник, то с баланса организации
         * - если я техник, то с баланса врача в данной оранизации
         */
        $result = false;

        if ($balanceAction->from_id_specialist == self::getSpecialistId()) {
            $result = true;
        }

        if ($balanceAction->from_id_organization == self::getSpecialistOrgId() && \Yii::$app->user->can(Role::ROLE_TECHNIC_MTO)) {
            $result = true;
        }

        if ($result != true && !empty($balanceAction->from_id_specialist)) {
            throw new ForbiddenHttpException('С баланса врача может списать он сам или техник в организации врача');
        }

        if ($result != true && empty($balanceAction->from_id_specialist)) {
            throw new ForbiddenHttpException('С баланса организации может списать только техник в этой организации');
        }
    }

    /**
     * @param $balanceAction BalanceAction
     * @param $throw boolean
     * @return boolean
     * @throws ForbiddenHttpException
     * @throws \Throwable
     */
    public static function checkConfirmTransfer($balanceAction, $throw = true)
    {
        if ($balanceAction->status != BalanceAction::STATUS_WAITING_CONFIRMATION) {
            if ($throw) {
                throw new BadRequestHttpException("Заявка на прием-передачу ТМЦ должна быть в статусе 'Ожидание подтверждения'");
            }
            return false;
        }

        /*
         * Могу подтвердить
         * - я запросил
         * - я техник в организации того, кто запросил
         */
        $result = false;
        if ($balanceAction->to_id_specialist == self::getSpecialistId()) {
            $result = true;
        }

        if ($balanceAction->to_id_organization == self::getSpecialistOrgId() && \Yii::$app->user->can(Role::ROLE_TECHNIC_MTO)) {
            $result = true;
        }

        if ($throw && $result != true) {
            throw new ForbiddenHttpException('Подтвердить может получатель или техник организации получателя');
        }
        return $result;
    }

    /**
     * @param $balanceAction BalanceAction
     * @param $throw boolean
     * @return boolean
     * @throws ForbiddenHttpException
     * @throws \Throwable
     */
    public static function checkTransferToRequester($balanceAction, $throw = true)
    {
        if ($balanceAction->status != BalanceAction::STATUS_WAITING_EXECUTION) {
            if ($throw) {
                throw new BadRequestHttpException("Заявка на передачу ТМЦ должна быть в статусе 'Ожидание исполнения'");
            }
            return false;
        }

        /*
         * Могу передать если
         * - это заявка ко мне
         * - я техник в организации, у которой запросили
         */
        $result = false;

        if ($balanceAction->from_id_specialist == self::getSpecialistId()) {
            $result = true;
        }

        if ($balanceAction->from_id_organization == self::getSpecialistOrgId() && \Yii::$app->user->can(Role::ROLE_TECHNIC_MTO)) {
            $result = true;
        }

        if ($throw && $result != true) {
            throw new ForbiddenHttpException('Передать может адресат или техник организации адресата');
        }

        return $result;
    }

    /**
     * @param $balanceAction BalanceAction
     * @param $throw boolean
     * @return boolean
     * @throws ForbiddenHttpException
     * @throws \Throwable
     */
    public static function checkCancelTransfer($balanceAction, $throw = true)
    {
        /*
         * Отмена
         *
         * Кнопка "отменить" должна отображаться для операции в статусах "Ожидание исполнения", "Ожидание подтверждения".
         * Отменить запрос на ТМЦ и передачу или прием ТМЦ может любая сторона.
         */
        $result = false;

        if ($balanceAction->status != BalanceAction::STATUS_WAITING_CONFIRMATION && $balanceAction->status != BalanceAction::STATUS_WAITING_EXECUTION) {
            if ($throw) {
                throw new ForbiddenHttpException(
                    'Отменять заявки можно в статусах "Ожидание исполнения", "Ожидание подтверждения"'
                );
            }
            return false;
        }

        /*
         * Пользователь техник МТО мосветобъединения может отменять за всех в организациях подконтрольным мосветобъединению
         */
        if (
            \Yii::$app->user->can(Role::ROLE_TECHNIC_MTO)
            && self::getSpecialistOrgId() == Organizations::MOS_VET_UNION_ID
            && in_array($balanceAction->to_id_organization, self::getUserOrgList())
        ) {
            return true;
        }

        if (self::isFromMeOrToMe($balanceAction)) {
            $result = true;
        }

        if (self::isFromMyOrgOrToMyOrg($balanceAction) && \Yii::$app->user->can(Role::ROLE_TECHNIC_MTO)) {
            $result = true;
        }

        if ($throw && $result != true) {
            throw new ForbiddenHttpException('Отменить может владелец, адресат, техник организации адресата или техник Мосветобъединения');
        }

        return $result;
    }

    /**
     * @param BalanceAction $balanceAction
     * @return bool
     * @throws ForbiddenHttpException
     * @throws \Throwable
     */
    protected static function isFromMeOrToMe($balanceAction)
    {
        return $balanceAction->from_id_specialist == self::getSpecialistId() || $balanceAction->to_id_specialist == self::getSpecialistId();
    }

    /**
     * @param BalanceAction $balanceAction
     * @return bool
     * @throws ForbiddenHttpException
     * @throws \Throwable
     */
    protected static function isFromMyOrgOrToMyOrg($balanceAction)
    {
        return $balanceAction->from_id_organization == self::getSpecialistOrgId() || $balanceAction->to_id_organization == self::getSpecialistOrgId();
    }

    /**
     * @throws \Throwable
     * @throws ForbiddenHttpException
     */
    protected static function getSpecialistId()
    {
        /* @var \app\common\models\UserModel $user */
        $user = \Yii::$app->user->getIdentity();
        $id = $user->specialist->id;
        if ($id == null) {
            throw new ForbiddenHttpException('Пользователь должен состоять в организации');
        }

        return $id;
    }

    /**
     * @throws \Throwable
     * @throws ForbiddenHttpException
     */
    protected static function getSpecialistOrgId()
    {
        /* @var \app\common\models\UserModel $user */
        $user = \Yii::$app->user->getIdentity();
        $id_organization = $user->specialist->id_organization;
        if ($id_organization == null) {
            throw new ForbiddenHttpException('Пользователь должен состоять в организации');
        }

        return $id_organization;
    }

    /**
     * Список доступных пользователю организаций
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     */
    protected static function getUserOrgList()
    {
        $user_org_list = (new Query())
            ->select('*')
            ->from(new Expression('tmc.get_org_tree_ids(:user_org)'))
            ->addParams([':user_org' => self::getSpecialistOrgId()])
            ->column();

        if (empty($user_org_list)) {
            throw new ForbiddenHttpException('У вас нет доступа к данному разделу');
        }

        return $user_org_list;
    }

    /**
     * @param $id_organization
     * @return bool
     */
    protected static function isRootOrg($id_organization)
    {
        return (new Query())
            ->select('id')
            ->from('tmc.org_tree')
            ->where([
                'OR',
                new Expression('path[1] = :id_org'),
                new Expression('path[2] = :id_org'),
                new Expression('path[3] = :id_org'),
            ])
            ->addParams([
                ':id_org' => $id_organization,
            ])
            ->exists();
    }
}
