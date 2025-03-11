<?php

namespace app\modules\v2\modules\organization\controllers;

use app\models\db\OutsideOrg;
use app\models\db\OwnerFeedback;
use app\models\db\Violation;
use app\modules\v2\common\skeletons\CommonList;
use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\organization\models\OutsideOrganizationModel;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

class OutsideOrganizationController extends BaseController
{
    /**
     * @return array
     */
    // public function behaviors(): array
    // {
    //     return ArrayHelper::merge(
    //         parent::behaviors(),
    //         [
    //             'http_authenticator' => [
    //                 'except' => [
    //                     'list',
    //                     'create',
    //                 ],
    //             ],
    //         ]
    //     );
    // }

    /**
     * @param $id
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionGet($id)
    {
        return [
            'result' => (new OutsideOrganizationModel())->get($id)
        ];
    }
    /**
     * @param int $page
     * @param int $limit
     * @param null $filter
     * @return array|null[]
     */
    public function actionList($page = 1, $limit = 10, $filter = null)
    {
        if($limit > 500){$limit=500;}
        //ограничиваю доступ, но возможно используется для внешнего доступа
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $query = OutsideOrg::find()
            ->select('*')
            ->orderBy('name');

        $query = $this->applyFilter($query, $filter);

        $query->limit($limit)
            ->offset(($page - 1) * $limit);

        return [
            'result' => new CommonList('outside_organizations', $query->all(), $query->count(), $page, $limit)
        ];
    }

    /**
     * @param ActiveQuery $query
     * @param array $filter
     * @return ActiveQuery
     */
    protected function applyFilter(ActiveQuery $query, $filter)
    {
        if ((is_array($filter) && (!array_key_exists('with_archived', $filter)) || $filter['with_archived'] == false)) { // от обратного
            $query->andWhere([
                'is_deleted' => false,
            ]);
        }
        if (!empty($filter['name'])) {
            $query->andWhere(['ILIKE', 'name', $filter['name']]);
        }

        return $query;
    }

    /**
     * Метод для создания OutsideOrg для владельцев питомцев
     * Валидируем по feedBackToken с отсуствием не обработанных записей OwnerFeedback
     *
     * @param string $name
     * @param string $feedbackToken
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionCreate(string $name, string $feedbackToken)
    {
        if (!$violation = Violation::find()->where(['feedback_token' => $feedbackToken])->one()) {
            throw new BadRequestHttpException('Переданные токен не валиден');
        }
        if (OwnerFeedback::find()->where(['id_violation' => $violation->id_violation, 'is_processed' => false])->one()) {
            throw new BadRequestHttpException('Форма уже заполнена, создание организации не доступно');
        }

        return [
            'result' => (new OutsideOrganizationModel())->create($name)
        ];
    }

    /**
     * Удаление организации
     * @param $id
     * @return bool[]
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete(int $id)
    {
        (new OutsideOrganizationModel())->delete($id);
        return [
            'result' => true
        ];
    }

    /**
     * @param int $id
     * @param string $name
     * @return bool[]
     * @throws BadRequestHttpException
     */
    public function actionEdit(int $id, string $name)
    {
        (new OutsideOrganizationModel())->edit($id, $name);
        return [
            'result' => true
        ];
    }

    /**
     * Метод для создания OutsideOrg для создания внешней организации пользователем системы
     *
     * @param string $name
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionCreateByAdmin(string $name)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new OutsideOrganizationModel())->create($name)
        ];
    }
}
