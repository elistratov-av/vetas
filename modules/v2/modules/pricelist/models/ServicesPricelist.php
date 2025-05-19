<?php

namespace app\modules\v2\modules\pricelist\models;

use app\common\validators\FullTrimValidator;
use app\models\db\GovServices;
use app\models\db\GovServicesHistory;
use app\models\db\Organizations;
use app\models\db\Pricelists;
use app\models\db\ServiceMeasures;
use app\models\db\ServiceTypes;
use app\modules\v2\common\skeletons\CommonList;
use app\modules\v2\modules\pricelist\skeletons\services\Lists;
use yii\base\Model;
use yii\web\BadRequestHttpException;

/**
 * Class ServicesPricelist
 * @package app\modules\v2\modules\pricelist\models
 *
 * @property Organizations $organization
 */
class ServicesPricelist extends Model
{
    /** @var Organizations */
    public $organization;

    /**
     * Возвращает парйслист
     *
     * @return \app\models\db\Pricelists
     */
    public function getPricelist()
    {
        if ($this->organization->isRoot()) {
            return $this->organization->pricelist;
        } else {
            return $this->organization->rootOrganization->pricelist;
        }
    }

    /**
     * Возвращает список услуг в прайслисте
     *
     * @param array $filter
     * @param int $page
     * @param int $limit
     * @return CommonList
     */
    public function getServices(array $filter = [], int $page = 1, int $limit = 10)
    {
        if (!$pricelist = $this->getPricelist()) {
            return new Lists('services', [], 0, $page, $limit);
        }
	
        $services = $pricelist->getServices();
        $services->andWhere(['deleted' => false]);
        if (isset($filter['id_service_type'])) {
            $services->andWhere(['id_service_type' => $filter['id_service_type']]);
        }

        $fullTrimValidator = new FullTrimValidator();
        if (!empty($filter['code'])) {
            if (!is_array($filter['code'])) {
                $filter['code'] = [$filter['code']];
            }
            $codes = [];
            foreach ($filter['code'] as $code) {
                $codes[] = $fullTrimValidator->validateValue($code);
            }
            $services->andFilterWhere($this->prepareCodesCondition($codes));
        }

        if (isset($filter['name'])) {
            $fullTrim = new FullTrimValidator();
            $name = $fullTrim->validateValue($filter['name']);
            $services->andWhere(['ilike', 'name', $name]);
        }

        $servicesCount = clone $services;

        $services->with(['serviceType', 'serviceMeasures'])
            ->limit($limit)
            ->offset($limit * ($page - 1));
		
        return new Lists(
            'services',
            $services->all(),
            $servicesCount->count(),
            $page,
            $limit
        );
    }

    /**
     * Возвращает историю по услуге
     *
     * @param int $id_service
     * @return array|ActiveRecord[]
     */
    public function getHistList($id_service)
    {
        if (!$history = GovServicesHistory::find()
            ->select(['action_type','action_date'])    
            ->where(['gov_service_id' => $id_service])
            ->all()) {
            throw new BadRequestHttpException("История по услуге не найдена");
        }

        return $history;
    }

    /**
     * @param array $codes
     * @return array
     */
    private function prepareCodesCondition($codes)
    {
        $codes = array_values(array_filter(array_unique($codes)));
        $condition = [];

        if (count($codes) == 1) {
            $condition = ['ilike', 'cod', $codes[0]];
        } elseif (count($codes) > 1) {
            $condition[] = 'or';
            foreach ($codes as $code) {
                $condition[] = ['ilike', 'cod', $code];
            }
        }

        return $condition;
    }

    /**
     * @param int $id
     * @return array
     * @throws BadRequestHttpException
     */
    public function getService(int $id)
    {
        if (!$pricelist = $this->getPricelist()) {
            throw new BadRequestHttpException("Прейскрунат не найден");
        }

        $service = $this->findService($id);

        return [
            'id' => $service->id,
            'code' => $service->cod,
            'name' => $service->name,
            'price' => $service->price,
            'duration' => $service->duration,
            'cooldown' => $service->cooldown,
            'id_service_measure' => $service->id_service_measure,
            'service_measure' => $service->serviceMeasures->name,
            'at_home' => $service->at_home,
            'at_clinic' => $service->at_clinic,
            'id_service_type' => $service->id_service_type,
            'service_type' => $service->serviceType->name,
            'service_types' => ServiceTypes::find()->select(['id', 'name'])->asArray()->all(),
            'service_measures' => ServiceMeasures::find()->select(['id', 'name'])->asArray()->all(),
        ];
    }

    /**
     * Создание услуги
     *
     * @param string $name
     * @param int $id_service_type
     * @param int $id_service_measure
     * @param float $price
     * @param int $duration
     * @param int $cooldown
     * @param bool $at_clinic
     * @param bool $at_home
     * @param null|string $code
     * @return array
     * @throws BadRequestHttpException
     */
    public function create(
        string  $name,
        int     $id_service_type,
        int     $id_service_measure,
        float   $price,
        int     $duration,
        int     $cooldown,
        bool    $at_clinic,
        bool    $at_home,
        ?string $code
    )
    {
        if (!$pricelist = $this->getPricelist()) {
            $pricelist = new Pricelists();
            $pricelist->id_organization = $this->organization->id;
            $pricelist->save(false);
        }

        $service = new GovServices();
        $service->name = $name;
        $service->id_service_type = $id_service_type;
        $service->id_service_measure = $id_service_measure;
        $service->duration = $duration;
        $service->cooldown = $cooldown;
        $service->at_home = $at_home;
        $service->at_clinic = $at_clinic;
        $service->cod = $code;
        $service->id_pricelist = $pricelist->id;
        $service->price = $price;

        if ($service->validate() === false) {
            $errors = $service->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ?
                'Ошибка валидации' :
                implode("\n", array_values($errors)));
        }

        $service->save(false);
		
		$servicehist = new GovServicesHistory();
        $servicehist->gov_service_id = $service->id;
        $servicehist->action_type = 0; 
        $servicehist->load_type = 0;
        $servicehist->save(false);
		
        return ['result' => true, 'id' => $service->id];
    }

    /**
     * Редактирование услуги
     *
     * @param int $id
     * @param string $name
     * @param int $id_service_type
     * @param int $id_service_measure
     * @param float $price
     * @param int $duration
     * @param int $cooldown
     * @param bool $at_clinic
     * @param bool $at_home
     * @param null|string $code
     * @return array
     * @throws BadRequestHttpException
     */
    public function edit(
        int     $id,
        string  $name,
        int     $id_service_type,
        int     $id_service_measure,
        float   $price,
        int     $duration,
        int     $cooldown,
        bool    $at_clinic,
        bool    $at_home,
        ?string $code
    )
    {
        $service = $this->findService($id);
        $service->name = $name;
        $service->id_service_type = $id_service_type;
        $service->id_service_measure = $id_service_measure;
        $service->duration = $duration;
        $service->cooldown = $cooldown;
        $service->at_home = $at_home;
        $service->at_clinic = $at_clinic;
        $service->cod = $code;
        $service->price = $price;

        if ($service->validate() === false) {
            $errors = $service->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ?
                'Ошибка валидации' :
                implode("\n", array_values($errors)));
        }

        $service->save(false);
		
		$servicehist = new GovServicesHistory();
        $servicehist->gov_service_id = $id;
        $servicehist->action_type = 1; 
        $servicehist->load_type = 0;
        $servicehist->save(false);
		
        return ['result' => true];
    }

    /**
     * Удаление услуги
     *
     * @param int $id
     * @return array
     * @throws BadRequestHttpException
     */
    public function delete(int $id)
    {
        $service = $this->findService($id);
        $service->deleted = true;
        $service->save(false);
		
        $servicehist = new GovServicesHistory();
        $servicehist->gov_service_id = $id;
        $servicehist->action_type = 2; 
        $servicehist->load_type = 0;
        $servicehist->save(false);
		
        return ['result' => true];
    }

    /**
     * Возвращает услугу по id, если она не удалена
     *
     * @param int $id
     * @return null|GovServices
     * @throws BadRequestHttpException
     */
    protected function findService(int $id)
    {
        if (!$service = GovServices::findOne(['id' => $id,
//            'id_pricelist' => $this->getPricelist()->id,
            'deleted' => false
        ])) {
            throw new BadRequestHttpException("Услуга не найдена");
        }

        return $service;
    }
}
