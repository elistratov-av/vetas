<?php

namespace app\modules\foundPet\controllers;

use app\common\efsp\EfspWrapper;
use app\models\db\Breeds;
use app\models\db\Color;
use app\models\db\found_pet\Message;
use app\models\db\found_pet\Ad;
use app\models\db\Species;
use app\modules\foundPet\models\SaveModel;
use app\modules\foundPet\models\SearchModel;
use app\modules\foundPet\Module;
use app\modules\foundPet\queue\JobHandler;
use yii\db\Query;
use yii\filters\ContentNegotiator;
use yii\web\Controller;
use yii\web\Response;
use yii\web\ServerErrorHttpException;
use yii\helpers\Json;

/**
 * Class DefaultController
 *
 * @package app\modules\foundPet\controllers
 */
class DefaultController extends Controller
{
    /**
     * @var \app\models\db\found_pet\Message
     */
    private $message;

    /**
     * @var string
     */
    private $connectionId;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = [];

        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'HEAD', 'OPTIONS'],
                'Access-Control-Allow-Credentials' => true,
            ],

        ];

        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::class,
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
                'application/vnd.api+json' => Response::FORMAT_JSON,
            ],
        ];

        return $behaviors;
    }

    protected function getConnectionId(): string
    {
        if (!$this->connectionId) {
            $this->connectionId = str_replace('.', '', uniqid('', true));
        }

        return $this->connectionId;
    }

    /**
     * @inheritDoc
     */
    public function beforeAction($action): bool
    {
        $result = parent::beforeAction($action);
        $rq = \Yii::$app->getRequest();
        $type = $action->id;
        \Yii::info(sprintf(
            "[connection-id: %s][class: %s] Start processing route '%s'\nHeaders:\n%s\nBody:\n%s",
            $this->getConnectionId(),
            static::class,
            $rq->getUrl(),
            Json::encode($rq->getHeaders()->toArray()),
            $rq->getRawBody() ?: '[EMPTY BODY]'
        ), Module::LOG_CATEGORY);

        if (!$result || !in_array($type, ['create-ad', 'update-ad', 'close-ad', 'extend-ad', 'subscribe', 'update-subscribe', 'unsubscribe'], true)) {
            return $result;
        }
        $rq = \Yii::$app->request;
        $this->message = new Message([
            'type' => $type,
            'service_number' => $rq->getBodyParam('service_number'),
            'body' => $rq->getBodyParams(),
            'headers' => $rq->getHeaders()->toArray(),
        ]);
        if (!$this->message->save()) {
            throw new ServerErrorHttpException();
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function afterAction($action, $result)
    {
        $before = parent::afterAction($action, $result);
        $rq = \Yii::$app->getRequest();
        $rs = \Yii::$app->getResponse();
        $payload = is_string($result) ? trim($result) : Json::encode($result);
        \Yii::info(sprintf(
            "[connection-id: %s][class: %s] Finish processing route '%s'\nHeaders:\n%s\nBody:\n%s",
            $this->getConnectionId(),
            static::class,
            $rq->getUrl(),
            Json::encode($rs->getHeaders()->toArray()),
            $payload ?: '[EMPTY BODY]'
        ), Module::LOG_CATEGORY);

        return $before;
    }

    /**
     * Передача реестра обнаруженных животных
     *
     * @return array
     */
    public function actionGetFound()
    {
        $model = new SearchModel([
            'data' => \Yii::$app->request->getBodyParams(),
            'scenario' => $this->action->id,
        ]);

        $result = $model->findAds();

        return empty($result) ? [] : $result;
    }

    /**
     * Передача реестра потерянных животных
     *
     * @return array
     */
    public function actionGetLost()
    {
        $model = new SearchModel([
            'data' => \Yii::$app->request->getBodyParams(),
            'scenario' => $this->action->id,
        ]);

        $result = $model->findAds();

        return empty($result) ? [] : $result;
    }

    /**
     * Поиск объявлений по параметрам
     *
     * @return array
     */
    public function actionSearch()
    {
        $model = new SearchModel([
            'data' => \Yii::$app->request->getBodyParams(),
            'scenario' => $this->action->id,
        ]);

        $result = $model->findAds();

        return empty($result) ? [] : $result;
    }

    /**
     * Передача объявления по номеру
     *
     * @return array
     */
    public function actionGetAd()
    {
        $model = new SearchModel([
            'data' => \Yii::$app->request->getBodyParams(),
            'scenario' => $this->action->id,
        ]);

        $result = $model->findAd();

        return ($result === false) ? ['error' => 'Объявление не найдено'] : $result;
    }

    /**
     * Просмотр объявлений пользователя
     *
     * @return array
     */
    public function actionUserAds()
    {
        $model = new SearchModel([
            'data' => \Yii::$app->request->getBodyParams(),
            'scenario' => $this->action->id,
        ]);

        $result = $model->findAds();

        return empty($result) ? [] : $result;
    }

    /**
     * Просмотр контактной информации по объявлению
     *
     * @return array
     */
    public function actionGetContact()
    {
        $model = new SearchModel([
            'data' => \Yii::$app->request->getBodyParams(),
            'scenario' => $this->action->id,
        ]);

        $result = $model->findContact();

        return ($result === false) ? ['error' => 'Контактные данные объявления не найдены'] : $result;
    }

    /**
     * Создание объявления
     *
     * @return array
     */
    public function actionCreateAd()
    {
        $model = new SaveModel([
            'data' => \Yii::$app->request->getBodyParams(),
            'scenario' => $this->action->id,
        ]);

        if (!$model->create()) {
            $this->updateMessage(false, $model->getErrors());
            $this->getJobHandler()->adCreateError($model->data);

            return [
                'error' => 'Ошибка при создании объявления',
            ];
        }

        $this->checkAddressCoords($model->ad);

        $this->updateMessage();
        $this->getJobHandler()->adCreateSuccess($model->ad, $model->data);
        if (!$model->ad->is_active) {
            sleep(1);
            $this->getJobHandler()->adModerationCloseSuccess($model->ad, $model->data);
        }

        return [
            'id_ad' => $model->ad->id,
        ];
    }

    /**
     * Редактирование объявления
     *
     * @return array
     */
    public function actionUpdateAd()
    {
        $model = new SaveModel([
            'data' => \Yii::$app->request->getBodyParams(),
            'scenario' => $this->action->id,
        ]);

        if (!$model->update()) {
            $this->updateMessage(false, $model->getErrors());
            $this->getJobHandler()->adUpdateError($model->data);

            return [
                'error' => 'Ошибка при редактировании объявления',
            ];
        }
        
        $this->checkAddressCoords($model->ad);

        $this->updateMessage();
        $this->getJobHandler()->adUpdateSuccess($model->ad, $model->data);
        if (!$model->ad->is_active) {
            sleep(1);
            $this->getJobHandler()->adModerationCloseSuccess($model->ad, $model->data);
        } else if (is_array($model->ad->subscriptions)) {
            sleep(1);
            $this->getJobHandler()->sendStatus80212($model->ad, $model->data);
        }

        return [
            'id' => $model->ad->id,
            'service_number' => $model->ad->service_number,
        ];
    }

    /**
     * Удаления объявления
     *
     * @return array
     */
    public function actionCloseAd()
    {
        $model = new SaveModel([
            'data' => \Yii::$app->request->getBodyParams(),
            'scenario' => $this->action->id,
        ]);

        if (!$model->close()) {
            $this->updateMessage(false, $model->getErrors());
            $this->getJobHandler()->adCloseError($model->data);

            return [
                'error' => 'Ошибка при удалении объявления',
            ];
        }

        $this->updateMessage();
        $this->getJobHandler()->adCloseSuccess($model->ad, $model->data);

        return [
            'id' => $model->ad->id,
            'service_number' => $model->ad->service_number,
        ];
    }

    /**
     * Продление объявления
     *
     * @return array
     */
    public function actionExtendAd()
    {
        $model = new SaveModel([
            'data' => \Yii::$app->request->getBodyParams(),
            'scenario' => $this->action->id,
        ]);

        if (!$model->extend()) {
            $this->updateMessage(false, $model->getErrors());
            $this->getJobHandler()->adExtendError($model->data);

            return [
                'error' => 'Ошибка при продлении объявления',
            ];
        }

        $this->updateMessage();
        $this->getJobHandler()->adExtendSuccess($model->ad, $model->data);

        return [
            'id' => $model->ad->id,
            'service_number' => $model->ad->service_number,
        ];
    }

    /**
     * Подписка на рассылку
     *
     * @return array
     */
    public function actionSubscribe()
    {
        $model = new SaveModel([
            'data' => \Yii::$app->request->getBodyParams(),
            'scenario' => $this->action->id,
        ]);

        if (!$model->subscribe()) {
            $this->updateMessage(false, $model->getErrors());
            $this->getJobHandler()->adUpdateError($model->data);

            return [
                'error' => 'Ошибка при подписке на рассылку',
            ];
        }

        $this->updateMessage();
        $this->getJobHandler()->adUpdateSuccess($model->ad, $model->data);
        sleep(1);
        $this->getJobHandler()->sendStatus80212($model->ad, $model->data);

        return array_merge($model->ad->subscriptions, ['sso_id' => $model->ad->author->sso_id]);
    }

    /**
     * Редактирование данных подписки
     *
     * @return array
     */
    public function actionUpdateSubscribe()
    {
        $model = new SaveModel([
            'data' => \Yii::$app->request->getBodyParams(),
            'scenario' => $this->action->id,
        ]);

        if (!$model->subscribe(false)) {
            $this->updateMessage(false, $model->getErrors());
            $this->getJobHandler()->adUpdateError($model->data);

            return [
                'error' => 'Ошибка при обновлении подписки на рассылку',
            ];
        }

        $this->updateMessage();
        $this->getJobHandler()->adUpdateSuccess($model->ad, $model->data);
        sleep(1);
        $this->getJobHandler()->sendStatus80212($model->ad, $model->data);

        return array_merge($model->ad->subscriptions, ['sso_id' => $model->ad->author->sso_id]);
    }

    /**
     * Отписка от рассылки
     *
     * @return array
     */
    public function actionUnsubscribe()
    {
        $model = new SaveModel([
            'data' => \Yii::$app->request->getBodyParams(),
            'scenario' => $this->action->id,
        ]);

        if (!$model->unsubscribe()) {
            $this->updateMessage(false, $model->getErrors());
            $this->getJobHandler()->adUpdateError($model->data);

            return [
                'error' => 'Ошибка при отподписке от рассылки',
            ];
        }

        $this->updateMessage();
        $this->getJobHandler()->adUpdateSuccess($model->ad, $model->data);

        return [
            'result' => 'OK',
        ];
    }

    /**
     * Получение информации о рассылке
     *
     * @return array
     */
    public function actionGetSubscribe()
    {
        $model = new SearchModel([
            'data' => \Yii::$app->request->getBodyParams(),
            'scenario' => $this->action->id,
        ]);

        $result = $model->findSubscription();

        return empty($result) ? ['error' => 'Подписка не найдена'] : $result;
    }

    /**
     * Получение всех рассылок пользователя
     *
     * @return array
     */
    public function actionSubscription()
    {
        $model = new SearchModel([
            'data' => \Yii::$app->request->getBodyParams(),
            'scenario' => $this->action->id,
        ]);

        $result = $model->findSubscriptions();

        return empty($result) ? [] : $result;
    }

    /**
     * Получения справочника “Виды животных”
     *
     * @return array
     */
    public function actionGetSpecies()
    {
        $rows = (new Query())
            ->select(['id', 'name'])
            ->from(Species::tableName())
            ->where([
                'or',
                ['tech_name' => Species::TECH_NAME_CAT],
                ['tech_name' => Species::TECH_NAME_DOG],
            ])
            ->orderBy(['name' => SORT_ASC])
            ->all();

        return $rows;
    }

    /**
     * Получения справочника “Породы животных”
     *
     * @return array
     */
    public function actionGetBreeds()
    {
        $rows = (new Query())
            ->select(['b.id', 'b.species_id AS id_species', 'b.name'])
            ->from(Breeds::tableName() . ' b')
            ->leftJoin(Species::tableName() . ' s', 'b.species_id = s.id')
            ->where([
                'or',
                ['s.tech_name' => Species::TECH_NAME_CAT],
                ['s.tech_name' => Species::TECH_NAME_DOG],
            ])
            ->orderBy([
                'b.species_id' => SORT_ASC,
                'b.name' => SORT_ASC,
            ])
            ->all();

        return $rows;
    }

    /**
     * Получения справочника “Окрас животного”
     *
     * @return array
     */
    public function actionGetColors(): array
    {
        return (new Query())
            ->select([
                'id' => 'c.id',
                'name' => 'c.name',
                'id_species' => 'c.species_id',
            ])
            ->from(Color::tableName() . ' c')
            ->leftJoin(Species::tableName() . ' s', 'c.species_id = s.id')
            ->where(['in', 's.tech_name', [Species::TECH_NAME_CAT, Species::TECH_NAME_DOG]])
            ->orderBy([
                'c.species_id' => SORT_ASC,
                'c.name' => SORT_ASC,
            ])
            ->all();
    }

    /**
     * @param bool $isSuccess
     * @param array $errors
     */
    private function updateMessage(bool $isSuccess = true, array $errors = []): void
    {
        $this->message->is_success = $isSuccess;
        $this->message->ad_errors = count($errors) ? $errors : null;

        $this->message->save();
    }

    /**
     * @return \app\modules\foundPet\queue\JobHandler
     */
    private function getJobHandler()
    {
        return new JobHandler();
    }

    /**
     * При отсутствии в адресе об-ния координат и наличии идентификатора ФИАС
     * запрашивает координаты у ЕФСП
     * 
     * @param Ad $ad
     */
    private function checkAddressCoords(Ad $ad): void
    {
        $address = $ad->address;
        if (empty($address->fias_id) || !(empty($address->geo_lat) || empty($address->geo_lon))) {
            return;
        }

        try {
            // TODO:DI
            $efspWrapper =  new EfspWrapper();
            if (null === $addressCoords = $efspWrapper->getAddressCoords($address->fias_id)) {
                return;
            }
        } catch (\Exception $e) {
            \Yii::error("Exception while trying to get coordinates from EFSP for the Ad #{$ad->id}: {$e->getMessage()}");
            return;
        }

        $address->geo_lat = (string) $addressCoords[0];
        $address->geo_lon = (string) $addressCoords[1];
        
        if (false == $address->update(true, ['geo_lat', 'geo_lon'])) {
            $errorsStr = implode("\n", $address->getErrorSummary(true));
            \Yii::error("Errors while trying to save address coordinates from EFSP for the Ad #{$ad->id}:\n{$errorsStr}");
            return;
        }
    }
}
