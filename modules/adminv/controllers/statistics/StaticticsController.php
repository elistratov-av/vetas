<?php

namespace app\modules\adminv\controllers\statistics;

use app\common\components\rbac\Role;
use app\models\db\Areas;
use app\models\db\Districts;
use app\models\db\efsp\EfspDistricts;
use app\models\db\found_pet\AdAuthor;
use app\models\db\Organizations;
use app\models\db\RegExpireReasons;
use app\models\db\ServiceTypes;
use app\models\db\Specialists;
use app\models\db\Species;
use app\models\db\Users;
use app\models\db\Visits;
use app\modules\admin\models\GovServices;
use app\modules\admin\models\Organization;
use app\modules\adminv\controllers\AdminController;
use yii\db\Expression;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;

/**
 * Class StaticticsController
 * @package app\modules\adminv\controllers\statistics
 */
abstract class StaticticsController extends AdminController
{
    /**
     * @var string
     */
    public $from;
    /**
     * @var string
     */
    public $to;
    /**
     * @var array IDs of organizations to query (from filters)
     */
    public $organizations;
    /**
     * @var array
     */
    public $userOrganizations;

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => [Role::ROLE_SYSADMIN_GOS, Role::ROLE_MANAGEMENT_GOS],
                    ],
                ],
            ],
        ];
    }

    /**
     * Убрал инициализацию переменных из init()
     * так как на тот момент еще нет данных по ролевке
     * @inheritDoc
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            $this->initVars();
            return true;
        }

        return false;
    }

    /**
     * @throws \Throwable
     */
    protected function initVars()
    {
        $this->from = \Yii::$app->request->get('from');
        $this->to = \Yii::$app->request->get('to');
        $this->organizations = \Yii::$app->request->get('id_organization', []);

        $this->userOrganizations = $this->getUserOrganizationsIds();
        if (!empty($this->userOrganizations)) {
            $this->organizations = empty($this->organizations)
                ? $this->userOrganizations
                : array_intersect($this->organizations, $this->userOrganizations);
        }

        if (empty($this->from)) {
            $date = new \DateTime(date("Y") . '-' . date("M") . '-01');
            $this->from = $date->format('Y-m-d');
        }
        if (empty($this->to)) {
            $date = new \DateTime();
            $this->to = $date->format('Y-m-d');
        }
    }

    /**
     * @return array
     * @throws \Throwable
     * @throws \yii\web\ForbiddenHttpException
     */
    protected function getUserOrganizationsIds()
    {
        if (\Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS)) {
            return [];
        }

        // для администрации гос получаем id организации и дочерних организаций
        /* @var $user \app\common\models\UserModel */
        $user = \Yii::$app->user->getIdentity();
        $organizations = $user->specialist->getAllOrganizations();
        $ids = ArrayHelper::getColumn($organizations, 'id');

        if (empty($ids)) {
            throw new ForbiddenHttpException();
        }

        return $ids;
    }

    /**
     * @return array
     */
    protected function servicesOptions()
    {
        return GovServices::find()
            ->select('name')
            ->orderBy(['name' => SORT_ASC])
            ->indexBy('id')
            ->asArray()
            ->column();
    }

    /**
     * @return array
     */
    protected function specialistsOptions()
    {
        return Specialists::find()
            ->joinWith('user')
            ->select('users.fullname')
            ->orderBy(['fullname' => SORT_ASC])
            ->indexBy('id')
            ->asArray()
            ->column();
    }

    /**
     * @return array
     */
    protected function usersOptions()
    {
        return Users::find()
            ->select('fullname')
            ->orderBy(['fullname' => SORT_ASC])
            ->indexBy('id')
            ->asArray()
            ->column();
    }

    /**
     * @return array
     */
    protected function organizationsOptions()
    {
        $query = Organization::find()
            ->select('short_name')
            ->orderBy(['name' => SORT_ASC])
            ->indexBy('id')
            ->asArray();
        if (!empty($this->userOrganizations)) {
            $query->andWhere(['in', 'id', $this->userOrganizations]);
        }

        return $query->column();
    }

    /**
     * @return array
     */
    protected function organizationsOptionsNoShelters()
    {
        $query = Organizations::find()
            ->select('short_name')
            ->joinWith('org_type')
            ->andWhere([
                'or',
                new Expression('org_types.is_tech = false'),
                new Expression('org_types.is_tech is null'),
                ])
            ->orderBy(['short_name' => SORT_ASC])
            ->indexBy('id')
            ->asArray();
        if (!empty($this->userOrganizations)) {
            $query->andWhere(['in', 'organizations.id', $this->userOrganizations]);
        }

        return $query->column();
    }

    /**
     * @deprecated
     * @return array
     */
    protected function areasOptions()
    {
        return Areas::find()
            ->select('name')
            ->orderBy(['name' => SORT_ASC])
            ->indexBy('id')
            ->asArray()
            ->column();
    }

    /**
     * @deprecated
     * @return array
     */
    protected function districtsOptions()
    {
        return Districts::find()
            ->select('name')
            ->orderBy(['name' => SORT_ASC])
            ->indexBy('id')
            ->asArray()
            ->column();
    }

    /**
     * Массив данных для MultiSelect выбора bti_city_area_code
     * @return array
     */
    protected function btiCityAreaCodeOptions()
    {
        $districts = EfspDistricts::find()
            ->select([
                'd.bti_city_area_code',
                new Expression("d.name || ' ' || d.type AS name"),
                new Expression("parent.name || ' ' || parent.type AS area"),
            ])
            ->from('efsp.districts AS d')
            ->innerJoin('efsp.districts AS parent', 'parent.id = d.parent_id')
            ->asArray()
            ->all();

        return ArrayHelper::map($districts, 'bti_city_area_code', 'name', 'area');
    }

    /**
     * @return array
     */
    protected function speciesOptions()
    {
        return Species::find()
            ->select('name')
            ->orderBy(['name' => SORT_ASC])
            ->indexBy('id')
            ->asArray()
            ->column();
    }

    /**
     * @return array
     */
    protected function adAuthorsOptions()
    {
        return AdAuthor::find()
            ->select(['concat(last_name, \' \', first_name, \' \', middle_name) as fullname'])
            ->orderBy([
                'last_name' => SORT_ASC,
                'first_name' => SORT_ASC,
                'middle_name' => SORT_ASC,
                ])
            ->indexBy('id')
            ->asArray()
            ->column();
    }

    /**
     * @return array
     */
    protected function catDogOptions()
    {
        return Species::find()
            ->select('name')
            ->orWhere(['tech_name' => 'CAT'])
            ->orWhere(['tech_name' => 'DOG'])
            ->orderBy(['name' => SORT_ASC])
            ->indexBy('id')
            ->asArray()
            ->column();
    }

    /**
     * @return array
     */
    protected function serviceTypesOptions()
    {
        return ServiceTypes::find()
            ->select('name')
            ->orderBy(['name' => SORT_ASC])
            ->indexBy('id')
            ->asArray()
            ->column();
    }

    /**
     * @return array
     */
    protected function regExpireReasonsOptions()
    {
        return RegExpireReasons::find()
            ->select('name')
            ->orderBy(['name' => SORT_ASC])
            ->indexBy('id')
            ->asArray()
            ->column();
    }

    /**
     * @return array|string[]
     */
    protected function visitTypesOptions()
    {
        return Visits::typeOptions();
    }
}
