<?php

namespace app\modules\adminv\models\search;

use app\common\components\rbac\Role;
use app\common\validators\PGIdValidator;
use app\models\db\Organizations;
use app\models\db\OrgTypes;
use app\modules\admin\data\AdminDataProvider;
use yii\db\Query;

/**
 * Class OrganizationSearch
 * @package app\modules\adminv\models\search
 */
class OrganizationSearch extends BaseSearchModel
{
    /**
     * @var int
     */
    public $id;
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $parent_name;
    /**
     * @var int
     */
    public $parent_id;
    /**
     * @var string
     */
    public $root_name;
    /**
     * @var int
     */
    public $id_org_type;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id'], 'trim'],
            [['id'], PGIdValidator::class], // PSQL INTEGER	4 bytes
            [['name', 'parent_name', 'root_name'], 'string'],
            [['parent_id', 'id_org_type'], 'integer'],
        ];
    }

    /**
     * @param array $params
     * @param bool  $isTech
     * @return AdminDataProvider
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     */
    public function search($params, $isTech = false)
    {
        $this->load($params);
        $query = Organizations::find()
            ->joinWith(['parentOrganization'], true)
            ->leftJoin(OrgTypes::tableName() . ' ot', 'organizations.id_org_type = ot.id');
        if ($isTech === true) {
            $query->where(
                ['ot.is_tech' => $isTech]
            );
        } else {
            $query->where([
                'or',
                ['ot.is_tech' => $isTech],
                ['organizations.id_org_type' => null]
            ]);
        }

        if (!\Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS)) {
            /* @var \app\common\models\UserModel $user */
            $user = \Yii::$app->user->getIdentity();
            $query->andWhere(['in', 'organizations.id', Organizations::orgTreeIds($user->specialist->id_organization)]);
        }

        $dataProvider = new AdminDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'id',
                'short_name' => [
                    'asc' => ['short_name' => SORT_ASC],
                    'desc' => ['short_name' => SORT_DESC],
                ],
                'parent_id',
                'addresses',
            ],
            'defaultOrder'=>[
                'short_name'=>SORT_ASC
            ],
        ]);

        if (!$this->validate()) {
            $dataProvider->query->emulateExecution();

            return $dataProvider;
        }

        if (!empty($this->root_name)){
            $subQueryArray = (new Query())->select('root_id')
                ->from('organizations_tree')
                ->andFilterWhere([
                    'AND',
                    ['level' => 0], // только головные организации
                    ['ilike', 'short_name', $this->root_name],
                ])
                ->column();

            if (!empty($subQueryArray)) {
                $query->leftJoin('organizations_tree as root', 'root.id = organizations.id')
                    ->andFilterWhere(['in', 'root.root_id', $subQueryArray]);
            } else { // нет таких организацмй
                $dataProvider->query->emulateExecution();

                return $dataProvider;
            }
        }

        $query->andFilterWhere([
            'organizations.id' => $this->id,
            'organizations.id_org_type' => $this->id_org_type,
        ]);

        $query->andFilterWhere([
            'or',
            ['ilike', 'organizations.name', $this->name],
            ['ilike', 'organizations.short_name', $this->name],
        ])
            ->andFilterWhere([
                'or',
                ['ilike', 'parent.name', $this->parent_name],
                ['ilike', 'parent.short_name', $this->parent_name],
            ]);

        return $dataProvider;
    }

}
