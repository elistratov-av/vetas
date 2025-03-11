<?php

namespace app\modules\v2\modules\vaccinationStation\models;

use app\common\validators\FullTrimValidator;
use app\models\db\FiasAddresses;
use app\models\db\VaccinationStation;
use app\modules\v2\common\skeletons\CommonList;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Throwable;
use yii\base\DynamicModel;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\StaleObjectException;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use Yii;

/**
 * Class VaccinationStationModel
 *
 * @package app\modules\v2\modules\vaccinationStation\models
 */
class VaccinationStationModel
{
    const DAYS_TO_EDIT = 3;

    private static $include = [
        'parent',
        'shifts',
        'kindVc',
        'reasonVc',
        'fiasAddress',
        'fiasAddress.area',
        'fiasAddress.district',
        'specialists',
        'lastVisit',
    ];

    public static function get(int $id): ?array
    {
        $result =  VaccinationStation::find()
            ->with(self::$include)
            ->where(['id' => $id])
            ->asArray()
            ->one();
        /*
        * Нам нужем bti_city_area_code как массив, приходиться использовать как модель, а не массив
        */
        $result['fiasAddress'] = !empty($result['fias_address_id']) ?
            FiasAddresses::findOne(['id' => $result['fias_address_id']]) : null;

        return $result;
    }

    /**
     * @param int   $page
     * @param int   $limit
     * @param array $filter
     *
     * @return CommonList
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     */
    public static function getAll(int $page = 1, int $limit = 10, array $filter = []): CommonList
    {
        $filter = self::validateFilter($filter);

        $query = VaccinationStation::find()
            ->with(self::$include);

        if (!empty($filter)) {
            $query = self::applyFilter($query, $filter);
        }

        $vaccinationStationCount = clone($query);

        $vaccinationStations = $query
            ->limit($limit)
            ->offset(($page - 1) * $limit)
            ->asArray()
            ->andFilterWhere(['>=', 'date', date('Y-01-01')])
            ->all();
        // Поиск FIAS адресов
        foreach ($vaccinationStations as $key => $value) {
            $vaccinationStation = VaccinationStation::findOne($vaccinationStations[$key]['id']);
            $vaccinationStations[$key]['canEdit'] = $vaccinationStation->getCanEdit();
            $vaccinationStations[$key]['fiasAddress'] = !empty($vaccinationStations[$key]['fias_address_id']) ?
                FiasAddresses::findOne(['id' => $vaccinationStations[$key]['fias_address_id']]) : null;
        }

        return new CommonList(
            'vaccination_stations',
            $vaccinationStations,
            $vaccinationStationCount->count(),
            $page,
            $limit
        );
    }

    /**
     * @param array $filter
     *
     * @return array|null
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     */
    private static function validateFilter(array $filter): ?array
    {
        if (empty($filter)) {
            return null;
        }

        $emptyFilter = [
            'name' => null,
            'short_name' => null,
            'parent_id' => null,
            'number' => null,
            'kind_vc_id' => null,
            'date_from' => null,
            'date_till' => null,
            'area_id' => null,
            'district_id' => null,
            'reason_vc_id' => null,
            'fias_address_id' => null,
            'specialist_id' => null,
        ];
        $filter = array_merge($emptyFilter, $filter);

        $rules = [
            [['parent_id', 'kind_vc_id', 'reason_vc_id', 'fias_address_id', 'specialist_id'], 'integer'],
            [['name', 'short_name', 'number', 'area_id', 'district_id'], 'string'],
            [['name', 'short_name', 'number', 'area_id', 'district_id'], FullTrimValidator::class],
            [['date_from', 'date_till'], 'date', 'format' => 'php:Y-m-d'],
        ];

        $model = DynamicModel::validateData($filter, $rules);
        if ($model->hasErrors()) {
            $errors = $model->getErrorSummary(true);

            throw new BadRequestHttpException(
                empty($errors) ? 'Ошибка:' : implode("\n", array_values($errors))
            );
        }

        return $model->attributes;
    }

    /**
     * @param ActiveQuery $query
     * @param array       $filter
     *
     * @return ActiveQuery
     */
    private static function applyFilter(ActiveQuery $query, array $filter): ActiveQuery
    {
        if (!empty($filter['name'])) {
            $query->andWhere(['ILIKE', 'vaccination_stations.name', $filter['name']]);
        }

        if (!empty($filter['short_name'])) {
            $query->andWhere(['ILIKE', 'vaccination_stations.short_name', $filter['short_name']]);
        }

        if (!empty($filter['parent_id'])) {
            $query->andWhere(['vaccination_stations.parent_id' => $filter['parent_id']]);
        }

        if (!empty($filter['number'])) {
            $query->andWhere(['ILIKE', 'vaccination_stations.number', $filter['number']]);
        }

        if (!empty($filter['kind_vc_id'])) {
            $query->andWhere(['vaccination_stations.kind_vc_id' => $filter['kind_vc_id']]);
        }

        if (!empty($filter['date_from']) && !empty($filter['date_till'])) {
            $query->andWhere([
                'BETWEEN',
                'vaccination_stations.date',
                $filter['date_from'],
                $filter['date_till'],
            ]);
        } elseif (!empty($filter['date_from'])) {
            $query->andWhere([
                '>=',
                'vaccination_stations.date',
                $filter['date_from'],
            ]);
        } elseif (!empty($filter['date_till'])) {
            $query->andWhere([
                '<=',
                'vaccination_stations.date',
                $filter['date_till'],
            ]);
        }

        if (!empty($filter['area_id'])) {
            $query->andWhere(['vaccination_stations.area_id' => $filter['area_id']]);
        }

        if (!empty($filter['district_id'])) {
            $query->andWhere(['vaccination_stations.district_id' => $filter['district_id']]);
        }

        if (!empty($filter['reason_vc_id'])) {
            $query->andWhere(['vaccination_stations.reason_vc_id' => $filter['reason_vc_id']]);
        }

        if (!empty($filter['fias_address_id'])) {
            $query->andWhere(['vaccination_stations.fias_address_id' => $filter['fias_address_id']]);
        }

        if (!empty($filter['specialist_id'])) {
            $query->joinWith('specialists')
                ->andWhere(['specialists.id' => $filter['specialist_id']]);
        }

        return $query;
    }

    /**
     * @param int         $parentId
     * @param string      $number
     * @param int         $kindVcId
     * @param string      $date
     * @param string      $time_from
     * @param string      $time_to
     * @param string      $area_id
     * @param string      $district_id
     * @param array       $fiasAddress
     * @param int|null    $reasonVcId
     * @param string|null $name
     * @param string|null $shortName
     * @param string|null $addressComment
     * @param int|null    $id
     *
     * @return VaccinationStation
     * @throws BadRequestHttpException
     * @throws StaleObjectException
     * @throws Throwable
     */
    public static function saveOrUpdate(
        int $parentId,
        string $number,
        int $kindVcId,
        string $date,
        string $time_from,
        string $time_to,
        string $area_id,
        string $district_id,
        array $fiasAddress,
        int $reasonVcId = null,
        string $name = null,
        string $shortName = null,
        string $addressComment = null,
        int $id = null,
        bool $ignoreSpecialist = false
    ): VaccinationStation {
        if ($id) {
            $vaccinationStation = VaccinationStation::findOne($id);
        } else {
            $vaccinationStation = new VaccinationStation();
        }

        if (self::isNumberOfDaysPast($vaccinationStation, self::DAYS_TO_EDIT)) {
            throw new BadRequestException('Невозможно редактирование ПП с момента проведения которой прошло более 3 дней');
        }

        if (!self::isCurrentUserOrganizationSpecialist($vaccinationStation, $parentId) && !$ignoreSpecialist) {
            throw new BadRequestException('Невозможно редактирование/создание ПП относящееся к другой организации');
        }

        $vaccinationStation->parent_id = $parentId;
        $vaccinationStation->number = $number;
        $vaccinationStation->kind_vc_id = $kindVcId;
        $vaccinationStation->date = $date;
        $vaccinationStation->time_from = $time_from;
        $vaccinationStation->time_to = $time_to;
        $vaccinationStation->area_id = $area_id;
        $vaccinationStation->district_id = $district_id;
        $vaccinationStation->reason_vc_id = $reasonVcId;
        $vaccinationStation->name = $name;
        $vaccinationStation->short_name = $shortName;
        $vaccinationStation->address_comment = $addressComment;

        // FIAS
        if (!empty($fiasAddress)) {
            $vaccinationStation->fias_address_id = FiasAddresses::findOrCreateFiasAddress($fiasAddress);
        } else {
            throw new BadRequestHttpException('Необходимо указать адрес.');
        }

        if (!$vaccinationStation->save()) {
            $errors = $vaccinationStation->getErrorSummary(true);

            throw new BadRequestHttpException(
                empty($errors) ?
                    'Ошибка при создании прививочного пункта' :
                    implode("\n", array_values($errors)));
        }

        return $vaccinationStation;
    }

    /**
     * @param int $id
     * @throws StaleObjectException
     * @throws Throwable
     */
    public static function delete(int $id)
    {
        /** @var VaccinationStation $vaccinationStation */
        $vaccinationStation = VaccinationStation::findOne($id);
        if ($vaccinationStation->lastVisit) {
            throw new BadRequestException('Невозможно удалить ПП в которой произведены вакцинации');
        }
        if (self::isNumberOfDaysPast($vaccinationStation, self::DAYS_TO_EDIT)) {
            throw new BadRequestException('Невозможно удаление ПП с момента проведения которой прошло более 3 дней');
        }
        if (!self::isCurrentUserOrganizationSpecialist($vaccinationStation)) {
            throw new BadRequestException('Невозможно удалить ПП относящееся к другой организации');
        }

        $vaccinationStation->delete();
    }

    /**
     * @param int $vaccinationStationId
     * @return array
     */
    public static function getAllSpecialists(int $vaccinationStationId): array
    {
        $vaccinationStation = VaccinationStation::findOne($vaccinationStationId);

        $formattedSpecialists = [];
        foreach ($vaccinationStation->specialist2VaccinationStation as $item) {
            $formattedSpecialists[$item->organization_id][] = $item->getSpecialist()->with('organization')->one();
        }

        return $formattedSpecialists;
    }

    /**
     * @param VaccinationStation $vaccinationStation
     * @param int $days
     * @return bool
     */
    public static function isNumberOfDaysPast(VaccinationStation $vaccinationStation, int $days): bool
    {
        if (!$vaccinationStation->date)
            return false;

        $numberOfDaysEarlierDate = Date('Y-m-d', strtotime("-$days days"));
        $stationDate = Date('Y-m-d', strtotime($vaccinationStation->date));
        return $stationDate <= $numberOfDaysEarlierDate;
    }

    /**
     * @param VaccinationStation $vaccinationStation
     * @return bool
     * @throws Throwable
     */
    public static function isCurrentUserOrganizationSpecialist(VaccinationStation $vaccinationStation, $parent_id_on_create = null)
    {
        $isSpecialist = false;
        $parent_id = $vaccinationStation->parent_id ?? $parent_id_on_create;
        foreach (Yii::$app->user->getIdentity()->specialists as $specialist) {
            $isSpecialist = $specialist->organization->id === $parent_id;
            if ($isSpecialist) {
                break;
            }
        }
        return $isSpecialist;
    }
}
