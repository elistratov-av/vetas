<?php

namespace app\modules\v2\modules\services\models;

use app\common\validators\FullTrimValidator;
use app\models\db\Organizations;
use app\models\db\ServiceTypes;
use app\modules\v2\common\skeletons\CommonList;
use yii\base\Model;
use yii\db\Expression;
use function foo\func;

/**
 * Class Pricelist
 *
 * @package app\modules\v2\modules\services\models
 * @property Organizations $organization
 */
class Pricelist extends Model
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
            return new CommonList('services', [], 0, $page, $limit);
        }

        $fullTrimValidator = new FullTrimValidator();
        $servicesQuery = $pricelist->getServices();
        $servicesQuery->select([
            'id',
            'name',
            'price',
            'alternative_name',
            'cod',
            'duration',
            'cooldown',
            'id_service_type',
            'id_service_measure',
            'for_broods',
            'for_multiple',
            'once_per_day',
        ])
            ->orderBy('name');
        $servicesQuery->andWhere(['deleted' => false]);
        if (isset($filter['id_service_type'])) {
            $servicesQuery->andWhere(['id_service_type' => $filter['id_service_type']]);
        }

        if (!empty($filter['code'])) {
            if (!is_array($filter['code'])) {
                $filter['code'] = [$filter['code']];
            }
            $codes = [];
            foreach ($filter['code'] as $code) {
                $codes[] = $fullTrimValidator->validateValue($code);
            }
            $servicesQuery->andFilterWhere($this->prepareCodesCondition($codes));
        }

        if (!empty($filter['service_type_ids'])) {
            $servicesQuery->andFilterWhere(['in', 'id_service_type',$filter['service_type_ids']]);
        }

        if (isset($filter['name'])) {
            $name = $fullTrimValidator->validateValue($filter['name']);
            $servicesQuery->andFilterWhere(['ilike', 'name', $name]);
        }

        if (!empty($filter['for_broods'])) {
            if (empty($filter['for_individual']) || $filter['for_individual'] == false) {
                $servicesQuery->andWhere(['not', ['for_broods' => null]]);
            } else {
                $servicesQuery
                    ->addSelect(new Expression("
                        CASE
                            WHEN for_broods is not null THEN 1
                            ELSE 2
                        END
                        AS xsort            
                    "))
                    ->orderBy('xsort, name');
            }
        } elseif (!empty($filter['for_multiple'])) {
            if (empty($filter['for_individual']) || $filter['for_individual'] == false) {
                $servicesQuery->andWhere(['not', ['for_multiple' => null]]);
            } else {
                $servicesQuery
                    ->addSelect(new Expression("
                        CASE
                            WHEN for_multiple is not null THEN 1
                            ELSE 2
                        END
                        AS xsort            
                    "))
                    ->orderBy('xsort, name');
            }
        }

        $servicesCount = clone $servicesQuery;

        $servicesQuery->with([
            'serviceType'     => function ($query) {
                $query->select(['id', 'name']);
            },
            'serviceMeasures' => function ($query) {
                $query->select(['id', 'name', 'count_flag']);
            },
        ])
            ->limit($limit)
            ->offset($limit * ($page - 1));

        return new CommonList(
            'services',
            array_map(function ($item) {
                //удаляем временный xsort, если он есть
                if (array_key_exists('xsort', $item)) {
                    unset($item['xsort']);
                }

                return $item;
            },
                $servicesQuery->asArray()->all()
            ),
            $servicesCount->count(),
            $page,
            $limit
        );
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
     * @param array $filter
     * @param int $page
     * @param int $limit
     * @return \app\modules\v2\common\skeletons\CommonList
     */
    public function getServiceTypes(array $filter = [], int $page = 1, int $limit = 1000)
    {
        if (!$pricelist = $this->getPricelist()) {
            return new CommonList('service_types', [], 0, $page, $limit);
        }

        $query = ServiceTypes::find()
            ->alias('st')
            ->select([
                'st.id',
                'st.name',
                'st.description',
                'st.sort_by',
            ])
            ->orderBy([
                'st.sort_by' => SORT_ASC,
                'st.name'    => SORT_ASC,
            ]);

        if (!empty($filter['for_broods']) || !empty($filter['for_multiple'])) {
            $servicesQuery = $pricelist->getServices()
                ->select('id_service_type');
            if (!empty($filter['for_broods'])) {
                $servicesQuery->andWhere(['not', ['for_broods' => null]]);
            } elseif (!empty($filter['for_multiple'])) {
                $servicesQuery->andWhere(['not', ['for_multiple' => null]]);
            }
            $query->andWhere(['in', 'st.id', $servicesQuery]);
        }

        $types = $query->asArray()->all();

        return new CommonList(
            'service_types',
            $types,
            count($types),
            $page,
            $limit
        );
    }
}
