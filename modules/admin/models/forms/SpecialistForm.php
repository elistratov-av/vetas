<?php

namespace app\modules\admin\models\forms;

use app\common\components\rbac\Role;
use app\models\db\Organizations;
use app\modules\admin\models\Specialist;
use yii\base\Model;
use yii\db\Query;

/**
 * Class SpecialistForm
 * @package app\modules\admin\models\forms
 *
 * @property \app\common\models\UserModel $user
 */
class SpecialistForm extends Model
{
    public $id_user;
    public $id_organization;
    public $reg_date;
    public $expel_date;
    public $roles = [];

    /**
     * @var \app\common\models\UserModel
     */
    private $_user;
    /**
     * @var \app\models\db\Specialists
     */
    private $specialist;

    /**
     * @return \app\common\models\UserModel
     */
    public function getUser()
    {
        return $this->_user;
    }

    /**
     * @param \app\common\models\UserModel $user
     */
    public function setUser($user): void
    {
        $this->_user = $user;

        $this->id_user = $user;

        if ($this->_user !== null) {
            $this->id_user = $this->_user->id;
        }
    }
    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id_user', 'id_organization', 'reg_date'], 'required'],
            [['id_user', 'id_organization', 'reg_date'], 'required'],
            ['id_organization', 'validateOrganization'],
            [['reg_date', 'expel_date'], 'date', 'format' => 'php:Y-m-d'],
            ['expel_date', 'compare', 'compareAttribute' => 'reg_date', 'operator' => '>'],
            ['roles', 'validateRoles'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'reg_date' => 'Дата приема',
            'expel_date' => 'Дата увольнения',
            'id_organization' => 'Организация',
            'id_user' => 'Пользователь',
            'roles' => 'Роли',
        ];
    }

    /**
     * @return bool
     */
    public function createSpecialist()
    {
        $this->specialist = new Specialist();

        if ($this->load(\Yii::$app->request->post()) && $this->validate()) {
            $this->specialist->load($this->attributes, '');
            if ($this->specialist->save()) {
                $this->saveRoles($this->specialist->id);
                return true;
            }
        }

        return false;
    }

    /**
     * @param \app\models\db\Specialists $specialist
     * @return bool
     */
    public function updateSpecialist($specialist)
    {
        $this->specialist = $specialist;
        $this->load($this->specialist->attributes, '');

        if ($this->load(\Yii::$app->request->post()) && $this->validate()) {
            $this->specialist->load($this->attributes, '');
            Specialist::getDb()->beginTransaction();
            if ($this->specialist->save()) {
                try {
                    $this->saveRoles($this->specialist->id, false);
                } catch (\Throwable $e){
                    $this->addError('roles', $e->getMessage());
                    return false;
                }

                Specialist::getDb()->transaction->commit();
                return true;
            }
            $this->addErrors($this->specialist->getErrors());
        }

        return false;
    }

    /**
     * @param string                          $attribute is the name of the attribute to be validated
     * @param array                           $params    contains the value of [[params]] that you specify when declaring the inline validation rule
     * @param \yii\validators\InlineValidator $validator is a reference to related [[InlineValidator]] object (since 2.0.11)
     * @see \yii\validators\InlineValidator
     */
    public function validateOrganization($attribute, $params, $validator)
    {
        if ($this->specialist === null) {
            return;
        }

        if (!$this->specialist->isNewRecord) {
            if ($this->specialist->id_organization == $this->id_organization) {
                return;
            }
            // не допускаем смену организации если у специалиста есть приемы или расписания
            if (!empty($this->specialist->id_organization) && $this->specialist->organization !== null) {
                $visitsExist = (new Query())
                    ->select('vs.*')
                    ->addSelect('v.id_organization')
                    ->from('visits_specialists vs')
                    ->leftJoin('visits v', '[[v]].[[id]] = [[vs]].[[id_visit]]')
                    ->where([
                        'id_specialist' => $this->specialist->id,
                        'id_organization' => $this->specialist->id_organization,
                    ])
                    ->exists();
                if ($visitsExist === true) {
                    $this->addError($attribute, 'Невозможно изменить организацию: у пользователя уже есть приемы в организации "' . $this->specialist->organization->short_name . '"');
                    return;
                }
                $timesheetsExist = (new Query())
                    ->select('ts.*')
                    ->addSelect('sh.id_organization')
                    ->from('timesheets ts')
                    ->leftJoin('shifts sh', '[[sh]].[[id]] = [[ts]].[[id_shift]]')
                    ->where([
                        'id_specialist' => $this->specialist->id,
                        'id_organization' => $this->specialist->id_organization,
                    ])
                    ->exists();
                if ($timesheetsExist === true) {
                    $this->addError($attribute, 'Невозможно изменить организацию: у пользователя уже есть расписания в организации "' . $this->specialist->organization->short_name . '"');
                    return;
                }
            }
        }

        foreach ($this->_user->specialists as $specialist) {
            if ($specialist->id_organization == $this->id_organization && !$specialist->isExpelledAtDate()) {
                $this->addError($attribute, 'Пользователь уже работает в организации "' . $specialist->organization->short_name . '"');
                return;
            }
        }
    }

    /**
     * @param string                          $attribute is the name of the attribute to be validated
     * @param array                           $params    contains the value of [[params]] that you specify when declaring the inline validation rule
     * @param \yii\validators\InlineValidator $validator is a reference to related [[InlineValidator]] object (since 2.0.11)
     * @see \yii\validators\InlineValidator
     */
    public function validateRoles($attribute, $params, $validator)
    {
        if ($this->hasErrors()) {
            return;
        }

        if ($this->specialist === null) {
            $this->addError($attribute, 'Пользователю не назначен специалист');

            return;
        }
        if (empty($this->id_organization)) {
            $this->addError($attribute, 'Специалисту не назначена организация');

            return;
        }
        if (empty($this->$attribute) || !is_array($this->$attribute)) {
            $this->addError($attribute, 'Пользователю необходимо назначить роль');

            return;
        }

        $organization = Organizations::findOne(['id' => $this->id_organization]);
        $isGos = $organization->isGos();

        $rolesGos = Role::gosOrgRoles();
        $rolesPrivate = Role::privateOrgRoles();
        $all = array_merge($rolesGos, $rolesPrivate);

        foreach ($this->$attribute as $roleName) {
            if (!in_array($roleName, $all, true)) {
                $this->addError($attribute, 'Неизвестная роль ' . $roleName);
                return;
            }
            if ($isGos === false && in_array($roleName, $rolesGos, true)) {
                $this->addError($attribute, 'Роль ' . $roleName . ' может назначаться только сотрудникам государственных организаций');
                return;
            } elseif ($isGos === true && in_array($roleName, $rolesPrivate, true)) {
                $this->addError($attribute, 'Роль ' . $roleName . ' может назначаться только сотрудникам частных организаций');
                return;
            }
            if (($roleName == Role::ROLE_SYSADMIN_GOS || $roleName == Role::ROLE_SYSADMIN_PRIVATE_FULL) && $organization->isRoot() !== true) {
                $this->addError($attribute, 'Роль ' . $roleName . ' может назначаться только сотрудникам головной организации');
                return;
            }
        }
    }

    /**
     * @param int $id_specialist
     * @param bool $isNew
     * @throws \yii\base\InvalidConfigException
     */
    private function saveRoles($id_specialist, $isNew = true)
    {
        /* @var $auth \app\common\components\rbac\DbManager */
        $auth = \Yii::$app->get('authManager');

        // Новый, у которого нет ролей - нам не интересен
        if ($isNew && (empty($this->roles) || !is_array($this->roles))) {
            return;
        }

        // Для удобства
        $old_roles = $auth->getRolesByUser($this->_user->id, $id_specialist);
        if (empty($this->roles)){
            $this->roles = [];
        }

        // Отозвали все роли
        if (empty($this->roles) || !is_array($this->roles)) {
            if (!empty($old_roles)) {
                $auth->revokeAll($this->_user->id, $id_specialist);
            }
            return;
        }

        $existing = [];
        foreach ($old_roles as $role) {
            $existing[] = $role->name;
        }

        $for_revoke = array_diff($existing, $this->roles);
        $for_assign = array_diff($this->roles, $existing);
//        \Yii::debug($for_assign, 'RULES');
//        \Yii::debug($for_revoke, 'RULES');

        /*
         * Из-за проверки на баланс приходится сначала назначать, потом отзывать
         */
        foreach ($for_assign as $roleName) {
            $role = $auth->getRole($roleName);
            if ($role !== null) {
                $auth->assign($role, $this->_user->id, $id_specialist);
            }
        }

        foreach ($for_revoke as $roleName) {
            $role = $auth->getRole($roleName);
            if ($role !== null) {
                $auth->revoke($role, $this->_user->id, $id_specialist);
            }
        }
    }
}
