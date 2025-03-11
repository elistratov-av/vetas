<?php

namespace app\modules\adminv\models\search;

use app\common\components\rbac\Role;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Class BaseSearchModel
 * @package app\modules\adminv\models\search
 */
abstract class BaseSearchModel extends Model
{
    /**
     * @return array
     */
    protected function currentUserOrganizations()
    {
        $ids = [];

        /* @var $user \app\common\models\UserModel */
        $user = \Yii::$app->user->getIdentity();

        if (\Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS)) {
            return $ids;
        }

        if (\Yii::$app->user->can(Role::ROLE_MANAGEMENT_GOS)) {
            $organizations = $user->specialist->getAllOrganizations();
            if (empty($organizations)) {
                return false;
            }

            return ArrayHelper::getColumn($organizations, 'id');
        }

        if (\Yii::$app->user->can(Role::ROLE_SHELTER_MANAGEMENT)) {
            $ids[] = $user->specialist->id_organization;
        }

        return $ids;
    }
}
