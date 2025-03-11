<?php

namespace app\modules\v2\modules\visit\controllers;

use app\models\db\Diseases;
use app\models\db\GovServices;
use app\models\db\Pets;
use app\models\db\Violation;
use app\models\db\ViolationCancellation;
use app\models\db\ViolationType;
use app\models\db\VisitPets;
use app\models\db\Visits;
use app\models\db\VisitsGovServices;
use app\modules\v2\modules\gosvetnadzor\models\ViolationChangeStateModel;
use app\modules\v2\modules\gosvetnadzor\models\ViolationModel;
use app\modules\v2\modules\visit\models\ServicesModel;
use app\modules\v2\modules\visit\models\ServicesListModel;
use app\modules\v2\modules\visit\models\VisitServiceModel;
use yii\base\BaseObject;
use yii\web\BadRequestHttpException;
use app\modules\v2\modules\BaseController;
use yii\web\NotFoundHttpException;
use Yii;
use yii\db\Expression;
use yii\web\ServerErrorHttpException;

/**
 * Class ServicesController
 * работа с записью услуг
 * https://jira.altarix.ru/browse/VETAIS-827
 *
 * @package app\modules\v2\modules\visit\controllers
 */
class ServicesController extends BaseController
{
    use VisitTrait;

    /**
     * работа с записью услуг
     * https://jira.altarix.ru/browse/VETAIS-827
     *
     * @param int|null $id_organization
     * @param int|null $id_visit
     * @param integer|array|null $id_pet
     * @param string|null $code
     * @param string|null $name
     * @return ServicesListModel
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionList(
        int    $id_organization = null,
        int    $id_visit = null,
               $id_pet = null,
        string $code = null,
        string $name = null
    ): ServicesListModel
    {
        if (is_numeric($id_visit)) {
            $visit = $this->findVisit($id_visit);
            $this->checkAccess($this->action->getUniqueId(), $visit, $this->actionParams);
            $serviceModel = new ServicesModel();
            $result = $serviceModel->editList($visit->id_organization, $id_visit, $id_pet, $code, $name);
        } else {
            if (!is_numeric($id_organization)) {
                throw new BadRequestHttpException('Для новой записи услуг переданы не все обязательные параметры');
            }

            $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);
            $serviceModel = new ServicesModel();
            $result = $serviceModel->newList($id_organization);
        }

        return $result;
    }

    /**
     * Добавление услуги приема после взятия приема в работу (вкладка "Услуги приема")
     *
     * @param int $id_visit
     * @param array|int $id_service
     * @param array|int $id_pet
     * @param int|null $count
     * @param array|null $params
     * @return array
     * @see https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=102768848
     */
    public function actionAdd(int $id_visit, $id_service, $id_pet = null, int $count = null, array $params = null)
    {
        $visit = $this->findVisit($id_visit);

        $this->checkAccess($this->action->getUniqueId(), $visit, $this->actionParams);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $attributes = $this->actionParams;

            //id_pet может быть: null (все животные приема), int или array - для персональных приемов
            if (!$id_pet) {
                $id_pet = $this->getPetIdsFromVisit($visit, $id_service);
            }
            $idPets = $id_pet ? (array)$id_pet : [null];

            if (is_array($attributes['id_service'])) {
                for ($i = 0; $i < count($attributes['id_service']); $i++) {
                    $newAttributes = $attributes;
                    $newAttributes['id_service'] = $attributes['id_service'][$i];

                    foreach ($idPets as $petId) {
                        $model = new VisitServiceModel([
                            'scenario' => VisitServiceModel::SCENARIO_CREATE,
                            'visit' => $visit,
                            'visitService' => (new VisitsGovServices()),
                        ]);
                        $newAttributes['id_pet'] = $petId;
                        if ($model->load($newAttributes, '') && !$model->save()) {
                            $errors = $model->getErrorSummary(true);
                            throw new BadRequestHttpException(!$errors ? 'Ошибка при сохранении услуги' : implode(";", array_values($errors)));
                        }
                    }
                }
            } else {
                foreach ($idPets as $petId) {
                    $model = new VisitServiceModel([
                        'scenario' => VisitServiceModel::SCENARIO_CREATE,
                        'visit' => $visit,
                        'visitService' => (new VisitsGovServices()),
                    ]);
                    $attributes['id_pet'] = $petId;

                    if ($model->load($attributes, '') && !$model->save()) {
                        $errors = $model->getErrorSummary(true);
                        throw new BadRequestHttpException(!$errors ? 'Ошибка при сохранении услуги' : implode(";", array_values($errors)));
                    }
                }
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        // Закрываем нарушения по вакцинации от бешенства, если внесены соответствующие вакцины

        //предпрод ломается тут
//        (new ViolationModel())->checkAndCancelPetsRabiesViolation($idPets);

        return (new ServicesModel())->editList($visit->id_organization, $id_visit, $id_pet);
    }

    /**
     * Редактирование услуги приема после взятия приема в работу (вкладка "Услуги приема")
     *
     * @param int $id
     * @param int $id_visit
     * @param int $id_service
     * @param int|null $count
     * @param array|null $params
     * @return array
     * @see https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=102768848
     */
    public function actionEdit(int $id, int $id_visit, int $id_service, int $count = null, array $params = null)
    {
        $visit = $this->findVisit($id_visit);
        $this->checkAccess($this->action->getUniqueId(), $visit, $this->actionParams);
        $visitService = $this->findVisitService($id, $id_visit, $id_service);

        $model = new VisitServiceModel([
            'scenario' => VisitServiceModel::SCENARIO_UPDATE,
            'visit' => $visit,
            'visitService' => $visitService
        ]);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($model->load($this->actionParams, '') && !$model->save()) {
                $errors = $model->getErrorSummary(true);
                throw new BadRequestHttpException(!$errors ? 'Ошибка при сохранении услуги' : implode(";", array_values($errors)));
            }
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        return (new ServicesModel())->editList($visit->id_organization, $id_visit, $id_visit);
    }

    /**
     * Удаление услуги приема после взятия приема в работу (вкладка "Услуги приема")
     *
     * @param int $id
     * @param int $id_visit
     * @param int $id_service
     * @return array
     * @see https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=102768848
     */
    public function actionDelete(int $id, int $id_visit, int $id_service)
    {
        $visit = $this->findVisit($id_visit);

        $this->checkAccess($this->action->getUniqueId(), $visit, $this->actionParams);

        $visitService = $this->findVisitService($id, $id_visit, $id_service);

        $model = new VisitServiceModel([
            'scenario' => VisitServiceModel::SCENARIO_DELETE,
            'visit' => $visit,
            'visitService' => $visitService
        ]);

        if (!$model->validate()) {
            $this->errorResponse($model, 'Ошибка при удалении услуги');
        }

        if ($visitService->getBalanceFlow()->exists()) {
            $this->errorResponse($visitService, 'Для удаления услуги сначала необходимо удалить связанные ТМЦ');
        }

        // Note: при удалении visits_gov_services каскадно удаляются visit_service_param_values (constraint в БД)
        if (!$visitService->delete()) {
            $this->errorResponse($visitService, 'Ошибка при удалении услуги');
        }

        return (new ServicesModel())->editList($visit->id_organization, $id_visit, $id_visit);
    }

    /**
     * @param int $id
     * @param int $id_visit
     * @param int $id_service
     * @return \app\models\db\VisitsGovServices|null
     * @throws \yii\web\NotFoundHttpException
     */
    private function findVisitService(int $id, int $id_visit, int $id_service)
    {
        $model = VisitsGovServices::findOne([
            'id' => $id,
            'id_visit' => $id_visit,
            'id_service' => $id_service,
        ]);

        if ($model === null) {
            throw new NotFoundHttpException('Указанная услуга не найдена для данного приема');
        }

        return $model;
    }

    /**
     * Если услуга на голову возвращает id всех животных приёма, в противном случае null
     *
     * @param Visits $visit
     * @param int $id_service
     * @return array|null
     */
    private function getPetIdsFromVisit(Visits $visit, int $id_service): ?array
    {
        /** @var GovServices $govService */
        if ($govService = GovServices::find()->where(['id' => $id_service])->one()) {
            if (
                ($visit->variety === Visits::VISIT_BROOD && $govService->for_broods === GovServices::FOR_HEAD)
                || ($visit->variety === Visits::VISIT_MULTIPLE && $govService->for_multiple === GovServices::FOR_HEAD)
            ) {
                return array_column($visit->pets, 'id');
            }
        }
        return null;
    }

    public function actionGetMapServicesMosru($idServiceMosru)
    {
        //             \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $rows = (new \yii\db\Query())
            ->select(['id', 'name'])
            ->from('gov_services')
            ->where([
                'id_service_mosru' => $idServiceMosru,
            ])
            ->all();

        return ['response' => $rows];
    }

    public function actionSetMapServicesMosru($idOldService, $idNewService, $idVisit)
    {
        try {
            $visit_gov_services = '';
            $visit_gov_services = VisitsGovServices::findOne(['id_visit' => $idVisit, 'id_service' => $idOldService]);
            $visit_gov_services->id_service = $idNewService;
            $visit_gov_services->update();
            return [
                'response' => "success",
            ];
        } catch (\Throwable $e) {

            throw new ServerErrorHttpException('Ошибка изменения услуги Мосру');
        }
    }
}