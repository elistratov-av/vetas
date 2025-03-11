<?php


namespace app\modules\v2\modules\tmc\controllers;

use app\models\db\Organizations;
use app\common\validators\FullTrimValidator;
use app\modules\admin\models\Organization;
use app\modules\v2\modules\BaseController;
use yii\db\Expression;
use yii\db\Query;
use yii\web\ForbiddenHttpException;

class OrganizationsTreeController extends BaseController
{

    /**
     * Список родственных организаций для пользоватлея (в рамках работы с ТМЦ)
     *
     * @param array $filter
     * @return array
     * @throws ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionRelated($filter = [])
    {
        $query =  (new Query())
            ->select('organizations.*')
            ->from(new Expression('tmc.get_org_tree_ids(:user_org) as tree'))
            ->leftJoin('public.organizations', 'organizations.id = tree.id')
            ->addParams([':user_org' => $this->getSpecialistOrgId()]);

        $this->prepareFilter($filter, $query);

        return $query->all();
    }

    /**
     * Список головных организаций (в рамках работы с ТМЦ)
     * @param array $filter
     * @return array
     * @throws \yii\base\NotSupportedException
     */
    public function actionHeads($filter = [])
    {
        $query =  (new Query())
            ->select('organizations.*')
            ->from(new Expression('tmc.org_tree AS tree'))
            ->leftJoin('public.organizations', 'organizations.id = tree.id')
            ->where([
                'OR',
                [
                    'AND',
                    ['<', 'level', 3],
                    new Expression('path[2] = :mos_vet_union_id')
                ],
                ['tree.id' => Organization::GOS_ROOT_ID]
            ])
            ->addParams([
                ':mos_vet_union_id' => Organizations::MOS_VET_UNION_ID,
            ])
            ->orderBy('level, name')
        ;

        $this->prepareFilter($filter, $query);

        return $query->all();
    }
    /**
     * @param array $filter
     * @param ActiveQuery $query
     * @throws \yii\base\NotSupportedException
     */
    private function prepareFilter($filter, &$query): void
    {
        $fullTrimValidator = new FullTrimValidator();
        if (!empty($filter['name'])) {
            $name = $fullTrimValidator->validateValue($filter['name']);
            $query->andWhere([
                'or',
                ['ilike', 'organizations.name', $name],
                ['ilike', 'organizations.short_name', $name],
            ]);
        }
    }

    /**
     * @throws \Throwable
     * @throws ForbiddenHttpException
     */
    protected function getSpecialistOrgId()
    {
        /* @var \app\common\models\UserModel $user */
        $user = \Yii::$app->user->getIdentity();
        $id_organization = $user->specialist->id_organization;
        if ($id_organization == null) {
            throw new ForbiddenHttpException('Пользователь должен состоять в организации');
        }

        return $id_organization;
    }
}