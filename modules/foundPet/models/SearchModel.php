<?php

namespace app\modules\foundPet\models;

use app\models\db\FiasAddresses;
use app\models\db\found_pet\Ad;
use app\models\db\found_pet\AdAddress;
use app\models\db\found_pet\AdAuthor;
use app\models\db\Organizations;
use app\models\db\PetIdentification;
use app\models\db\ShelterGuests;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * Class SearchModel
 *
 * @package app\modules\foundPet\models
 */
class SearchModel extends Model
{
    const SCENARIO_GET_FOUND = 'get-found';

    const SCENARIO_GET_LOST = 'get-lost';

    const SCENARIO_SEARCH = 'search';

    const SCENARIO_GET_AD = 'get-ad';

    const SCENARIO_USER_ADS = 'user-ads';

    const SCENARIO_GET_CONTACT = 'get-contact';

    const SCENARIO_GET_SUBSCRIBE = 'get-subscribe';

    const SCENARIO_SUBSCRIPTION = 'subscription';

    /**
     * @var array
     */
    public $data;

    /**
     * @inheritDoc
     * TODO
     */
    public function rules()
    {
        return [
            ['data', 'safe'],
        ];
    }

    /**
     * @return array
     */
    public function findAds()
    {
        if ($this->scenario == self::SCENARIO_SEARCH) {
            if (!isset($this->data['catalogue']) || !in_array($this->data['catalogue'], ['lost', 'found'], true)) {
                return [];
            }
            $shelter = (!isset($this->data['chip']) && !isset($this->data['stamp']))
                ? null
                : $this->findShelter(ArrayHelper::getValue($this->data, 'chip'), ArrayHelper::getValue($this->data, 'stamp'));
            if (!empty($shelter)) {
                if (!is_array($shelter)) {
                    $shelter = [$shelter];
                }
                $message = [];
                foreach ($shelter as $organization) {
                    $message[] = 'Животное с указанными параметрами находится в приюте ' . $organization->name
                        . '. Подробную информацию можно узнать по телефону ' . $organization->phone
                        . ' или по e-mail ' . $organization->email . '.';
                }

                return [
                    'items' => [],
                    'total' => 0,
                    'message' => implode(" \n", $message),
                    'is_shelter' => true,
                ];
            }
        }

        $query = Ad::find()
            ->alias('a');

        $author = null;

        if ($this->scenario == self::SCENARIO_GET_FOUND) {
            $query->andWhere(['type' => Ad::TYPE_FOUND]);
        } elseif ($this->scenario == self::SCENARIO_GET_LOST) {
            $query->andWhere(['type' => Ad::TYPE_LOST]);
        } elseif ($this->scenario == self::SCENARIO_SEARCH) {
            $this->prepareFilterQuery($query);
        } elseif ($this->scenario == self::SCENARIO_USER_ADS) {
            if (!isset($this->data['sso_id'])) {
                return [];
            } else {
                $author = AdAuthor::findBySsoId($this->data['sso_id']);
                if ($author === null) {
                    return [];
                }
                $query->innerJoin(AdAuthor::tableName() . ' ao', 'ao.id = a.id_author')
                    ->andWhere(['ao.sso_id' => $this->data['sso_id']]);
                // по просьбе mos.ru (Алексей Довгань) возвращаем только активные, хотя в спецификации сказано
                // "Возвращает из БД ВетАС все объявления созданные пользователем, с указанием статуса объявления (Активное | Закрытое)"
                $query->andWhere(['is_active' => true]);
            }
        }

        if (isset($this->data['closed'])) {
            $query->andWhere(['is_active' => !($this->data['closed'])]);
        }

        if (isset($this->data['sort']) && $this->data['sort'] === true) {
            $query->orderBy(['a.id' => SORT_DESC]);
        } else {
            $query->orderBy(['a.id' => SORT_ASC]);
        }

        if (isset($this->data['page']) && isset($this->data['limit'])) {
            [$page, $limit] = $this->validatePagination();
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => $limit,
                    'pageSizeLimit' => false,
                    'page' => ($page - 1),
                ],
            ]);
            $models = $dataProvider->getModels();
            $total = $dataProvider->getTotalCount();
        } else {
            $models = $query->all();
            $total = count($models);
        }

        if (empty($models)) {
            return [];
        }

        $items = [];

        foreach ($models as $model) {
            $items[] = $this->formatAd($model, $author);
        }

        $result = [
            'items' => $items,
            'total' => $total,
        ];

        if ($this->scenario == self::SCENARIO_SEARCH) {
            $result['is_shelter'] = false;
        }

        return $result;
    }

    /**
     * @return array|false
     */
    public function findAd()
    {
        $ad = Ad::findOne(['id' => $this->data['id']]);

        if ($ad === null) {
            return false;
        }

        return $this->formatAd($ad);
    }

    /**
     * @return array|false
     */
    public function findContact()
    {
        $ad = Ad::findOne(['id' => $this->data['id']]);

        if ($ad === null || $ad->author === null) {
            return false;
        }

        return [
            'id' => $ad->id,
            'phone' => $ad->author->phone,
            'email' => $ad->author->email,
            'name' => trim($ad->author->last_name . ' ' . $ad->author->first_name . ' ' . $ad->author->middle_name),
        ];
    }

    /**
     * @return array
     */
    public function findSubscriptions()
    {
        $author = AdAuthor::findBySsoId($this->data['sso_id']);

        if ($author === null) {
            return [];
        }

        $query = Ad::find()
            ->where(['id_author' => $author->id])
            ->andWhere(['is_active' => true])
            ->andWhere(['not', ['subscriptions' => null]])
            ->orderBy(['id' => SORT_ASC]);

        if (isset($this->data['page']) && isset($this->data['limit'])) {
            [$page, $limit] = $this->validatePagination();
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => $limit,
                    'pageSizeLimit' => false,
                    'page' => ($page - 1),
                ],
            ]);
            $models = $dataProvider->getModels();
            $total = $dataProvider->getTotalCount();
        } else {
            $models = $query->all();
            $total = count($models);
        }

        if (empty($models)) {
            return [];
        }

        $items = [];

        foreach ($models as $model) {
            /* @var $model \app\models\db\found_pet\Ad */
            $items[] = array_merge(
                $model->subscriptions,
                ['id_ad' => $model->id],
                ['service_number' => $model->service_number]
            );
        }

        return [
            'items' => $items,
            'total' => $total,
        ];
    }

    /**
     * @return array|false
     */
    public function findSubscription()
    {
        $expression = new Expression('"subscriptions"::jsonb @> \'{"subscribe_id":' . (int)$this->data['subscribe_id'] . '}\'::jsonb');

        /* @var $model \app\models\db\found_pet\Ad */
        $model = Ad::find()
            ->where(['not', ['subscriptions' => null]])
            ->andWhere(['is_active' => true])
            ->andWhere($expression)
            ->one();

        if ($model === null) {
            return false;
        }

        return array_merge(
            $model->subscriptions,
            ['sso_id' => $model->author->sso_id],
            ['service_number' => $model->service_number]
        );
    }

    /**
     * @param \app\models\db\found_pet\Ad $ad
     * @param \app\models\db\found_pet\AdAuthor|null $author
     * @return array
     */
    private function formatAd(Ad $ad, ?AdAuthor $author = null)
    {
        $result = [
            'id' => $ad->id,
            'date_ad' => substr($ad->created_at, 0, 10),
            'time_ad' => substr($ad->created_at, 11),
            'is_active' => $ad->is_active,
            'type' => $this->convertType($ad->type),
            'date_event' => $ad->date_event,
            'time_event' => $ad->time_event,
            'chip' => $ad->chip,
            'stamp' => $ad->stamp,
            'species' => ($ad->species === null ? null : $ad->species->name),
            'id_species' => $ad->id_species,
            'breed' => ($ad->breed === null ? null : $ad->breed->name),
            'id_breed' => $ad->id_breed,
            'sex' => $ad->sex,
            'color' => ($ad->color === null ? null : $ad->color->name),
            'id_color' => $ad->id_color,
            'age' => $ad->age,
            'notice' => $ad->notice,
            'photo' => $ad->photo,
            'stamp_photo' => $ad->stamp_photo,
            'address' => [
                'city' => ($ad->address === null ? null : $ad->address->city),
                'settlement' => ($ad->address === null ? null : $ad->address->settlement),
                'area' => ($ad->address === null ? null : $ad->address->area),
                'street' => ($ad->address === null ? null : $ad->address->street),
                'house' => ($ad->address === null ? null : $ad->address->house),
                'geo_lat' => ($ad->address === null ? null : $ad->address->geo_lat),
                'geo_lon' => ($ad->address === null ? null : $ad->address->geo_lon),
                'postcode' => ($ad->address === null ? null : $ad->address->postcode),
                'radius' => ($ad->address === null ? null : $ad->address->radius),
                'fias_id' => ($ad->address === null ? null : $ad->address->fias_id),
                'kladr_id' => ($ad->address === null ? null : $ad->address->kladr_id),
                'is_map' => ($ad->address === null ? null : $ad->address->is_map),
                'is_manually_set' => ($ad->address === null ? null : $ad->address->is_manually_set),
                'pobox' => ($ad->address === null ? null : $ad->address->pobox),
            ],
            'service_number' => $ad->service_number,
        ];

        if ($this->scenario == self::SCENARIO_GET_AD) {
            $result['ad_url'] = $this->createAdUrl($ad->type, $ad->id);
        }

        if ($this->scenario == self::SCENARIO_GET_FOUND || $this->scenario == self::SCENARIO_GET_LOST) {
            $shelter = $this->findShelter($result['chip'], $result['stamp']);
            $result['is_shelter'] = !empty($shelter);
        }

        if ($this->scenario == self::SCENARIO_USER_ADS) {
            $result['subscribe_id'] = (empty($ad->subscriptions) || !is_array($ad->subscriptions)) ? null : ArrayHelper::getValue($ad->subscriptions, 'subscribe_id');
            if ($author !== null) {
                $result['sso_id'] = $author->sso_id;
                $result['first_name'] = $author->first_name;
                $result['middle_name'] = $author->middle_name;
                $result['last_name'] = $author->last_name;
                $result['phone'] = $author->phone;
                $result['email'] = $author->email;
            }
        }

        return $result;
    }

    /**
     * @param string $type
     * @return string
     */
    private function convertType($type)
    {
        switch ($type) {
            case Ad::TYPE_LOST:
                return 'lost';
            case Ad::TYPE_FOUND:
                return 'found';
            default:
                return $type;
        }
    }

    /**
     * @param \yii\db\ActiveQuery $query
     */
    private function prepareFilterQuery($query)
    {
        $type = $this->data['catalogue'];
        if ($type == 'lost') {
            $query->andWhere(['type' => Ad::TYPE_LOST]);
            $suffix = '_loss';
        } elseif ($type == 'found') {
            $query->andWhere(['type' => Ad::TYPE_FOUND]);
            $suffix = '_found';
        } else {
            return;
        }

        if (isset($this->data['sso_id'])) {
            $query->innerJoin(AdAuthor::tableName() . ' ao', 'ao.id = a.id_author')
                ->andWhere(['ao.sso_id' => $this->data['sso_id']]);
        }

        $fields = [
            'chip',
            'stamp',
            'id_species',
            'id_breed',
            'sex',
            'id_color',
            'age',
        ];

        foreach ($fields as $field) {
            if (!empty($this->data[$field])) {
                $query->andWhere([$field => $this->data[$field]]);
            }
        }

        if (!empty($this->data['notice'])) {
            $query->andWhere(['ilike', 'notice', $this->data['notice']]);
        }

        // Обработка даты/времени
        $evtDate = $this->data['date' . $suffix] ?? $this->data['date_event'] ?? null;
        $evtTime = $this->data['time' . $suffix] ?? $this->data['time_event'] ?? null;
        /**
         * Если нам пришёл запрос на об-ния о потере, значит конечный пользователь животинку нашёл,
         * и нужно вернуть об-ния, созданные до этого момента.
         * И - наоборот.
         * 
         * @see https://jira.altarix.ru/browse/VETAIS-3407
         * TODO:found-pet:ads-date-index Добавить индекс(ы) для поля date_event (и time_event)
        */
        $op = 'lost' == $type
            ? '<'
            : '>'
        ;
        if (!empty($evtTime)) {
            // В таблице два отдельных поля для даты и времени - ищем либо в "эту" дату позднее(/ранее), либо в последующие(/предыдущие).
            // Полагаем, что если в запросе есть время, то дата УЖ ТОЧНО есть. В любом случае, валидация должна находиться не здесь.
            $query->andWhere([
                'OR',
                [
                    'AND',
                    ['=', 'date_event', $evtDate],
                    [$op, 'time_event', $evtTime],
                ],
                [$op, 'date_event', $evtDate],
            ]);
        } elseif (!empty($evtDate)) {
            $query->andWhere(["{$op}=", 'date_event', $evtDate]);
        }

        $address = $this->data['place' . $suffix] ?? null;
        if (!is_array($address) || !count($address)) {
            return;
        }
        $query->innerJoin(AdAddress::tableName() . ' aa', 'a.id_address = aa.id');
        $radius = (int)($address['radius'] ?? 0) * 1000;

        $fid = $address['fias_id'] ?? null;
        if ($fid) {
            $where = ['fias_id' => $fid];
            $coords = (new Query())
                ->select(['lat', 'lon'])
                ->from(FiasAddresses::tableName())
                ->where([
                    'and',
                    ['houseguid' => $fid],
                    ['is not', 'coords', null],
                ])
                ->all();
            if (count($coords)) {
                $coords = array_unique(array_map(static function (array $geo) {
                    return implode(' ', [$geo['lon'], $geo['lat']]);
                }, $coords));
                $geo = count($coords) === 1 ? sprintf("ST_GeomFromText('POINT(%s)')", $coords[0]) : sprintf("ST_Centroid('MULTIPOINT(%s)')", implode(',', $coords));
                $where = [
                    'or',
                    $where,
                    [
                        'in',
                        'fias_id',
                        (new Query())
                            ->select(new Expression('"houseguid"::varchar'))
                            ->from(FiasAddresses::tableName())
                            ->groupBy('houseguid')
                            ->where([
                                'and',
                                ['is not', 'coords', null],
                                ['<=', new Expression(sprintf('ST_DistanceSphere(coords, %s)', $geo)), $radius],
                            ]),
                    ],
                ];
            }
            $query->andWhere($where);
        }
        $lat = $address['geo_lat'] ?? null;
        $lon = $address['geo_lon'] ?? null;
        if ($lat && $lon) {
            // Incoming geo points has EPSG:3857 format, transform to 4326
            $geo = sprintf(
                "ST_GeomFromText('%s')",
                (new Query())
                    ->select(new Expression(sprintf("ST_AsText(ST_Transform(ST_GeomFromText('SRID=3857;POINT(%s)'), 4326))", implode(' ', [$lat, $lon]))))
                    ->scalar()
            );
            $query->andWhere([
                'or',
                [
                    'and',
                    ['is not', 'geo_lon', null],
                    ['is not', 'geo_lat', null],
                    ['<=', new Expression(sprintf("ST_DistanceSphere(ST_Transform(ST_SetSRID(ST_Point(geo_lat::float, geo_lon::float), 3857), 4326), %s)", $geo)), $radius]
                ],
                [
                    'in',
                    'fias_id',
                    (new Query())
                        ->select(new Expression('"houseguid"::varchar'))
                        ->from(FiasAddresses::tableName())
                        ->groupBy('houseguid')
                        ->where([
                            'and',
                            ['is not', 'coords', null],
                            ['<=', new Expression(sprintf('ST_DistanceSphere(coords, %s)', $geo)), $radius],
                        ]),
                ]
            ]);
        }
    }

    /**
     * @return int[]
     */
    private function validatePagination(): array
    {
        $page = (int)$this->data['page'];
        $page = ($page > 0) ? $page : 1;
        $limit = (int)$this->data['limit'];
        $limit = ($limit >= 0) ? $limit : 20;

        return [$page, $limit];
    }

    /**
     * @param string $type
     * @param int $id
     * @return string
     */
    private function createAdUrl(string $type, int $id)
    {
        $module = \Yii::$app->getModule('foundPet');
        $env = ArrayHelper::getValue($module->params, 'mosru_env');
        $url = ArrayHelper::getValue($module->params, 'mosru_urls.' . $env . '.single_ad');
        $type = ($type == Ad::TYPE_LOST) ? 'lost' : 'found';

        return empty($url) ? '' : strtr($url, ['{type}' => $type, '{id}' => $id]);
    }

    /**
     * @param string|null $chipNumber
     * @param string|null $stampNumber
     * @return \app\models\db\Organizations|\app\models\db\Organizations[]|array|null
     */
    private function findShelter($chipNumber = null, $stampNumber = null)
    {
        $chip = empty($chipNumber) ? null : PetIdentification::findOne([
            'identification_code' => $chipNumber,
            'id_ident_type' => 1,
        ]);

        $stamps = empty($stampNumber) ? [] : PetIdentification::find()
            ->where([
                'identification_code' => $stampNumber,
                'id_ident_type' => [2, 4],
            ])
            ->all();

        if (empty($chip) && empty($stamps)) {
            return null;
        }

        if (!empty($chip)) {
            $id_organization = ShelterGuests::find()
                ->select('id_organization')
                ->where([
                    'id_pet' => $chip->id_pet,
                    'departure_date' => null,
                ])
                ->orderBy(['created_at' => SORT_DESC])
                ->limit(1)
                ->scalar();

            if (!empty($id_organization)) {
                return Organizations::findOne(['id' => $id_organization]);
            }
        }

        if (!empty($stamps)) {
            $ids = ShelterGuests::find()
                ->select('id_organization')
                ->where([
                    'id_pet' => array_slice(ArrayHelper::getColumn($stamps, 'id_pet'), 0, 65534),
                    'departure_date' => null,
                ])
                ->orderBy(['created_at' => SORT_DESC])
                ->column();

            if (!empty($ids)) {
                $ids = array_unique($ids);

                return Organizations::findAll(['id' => $ids]);
            }
        }

        return null;
    }
}
