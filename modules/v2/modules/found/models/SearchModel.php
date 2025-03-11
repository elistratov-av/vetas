<?php

namespace app\modules\v2\modules\found\models;

use app\common\validators\FullTrimValidator;
use app\common\validators\PGIdValidator;
use app\models\db\found_pet\Ad;
use app\models\db\found_pet\AdAddress;
use app\models\db\found_pet\AdAuthor;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * Class SearchModel
 * @package app\modules\v2\modules\found\models
 */
class SearchModel extends Model
{
    const SCENARIO_LIST_ADS = 'list_ads';
    const SCENARIO_AD_PHOTOS = 'ad_photos';

    const STATUS_ACTIVE = 'ACTIVE';
    const STATUS_MODERATION = 'MODERATION';
    const STATUS_CLOSED = 'CLOSED';
    const STATUS_REJECTED = 'REJECTED';

    // статусы для отображения на фронте
    const IS_BEENG_MODERATED = 1;       // На модерации
    const IS_APPROVED = 2;              // Одобрено модератором
    const IS_CLOSED_BY_MODERATOR = 3;   // Закрыто модератором
    const IS_CLOSED_BY_AUTHOR = 4;      // Закрыто пользователем
    const IS_CLOSED_AUTO = 5;           // Истек срок публикации объявления
    const IS_CLOSED_AUTO_CENSOR = 6;    // Авто отклонение для нецензурных объявлений

    /**
     * @var string
     */
    public $status;
    /**
     * @var int
     */
    public $id;
    /**
     * @var string
     */
    public $type;
    /**
     * @var string
     */
    public $created_at;
    /**
     * @var string
     */
    public $date_event;
    /**
     * @var int
     */
    public $id_species;
    /**
     * @var int
     */
    public $id_breed;
    /**
     * @var string
     */
    public $chip;
    /**
     * @var string
     */
    public $stamp;
    /**
     * @var string
     */
    public $author;
    /**
     * @var string
     */
    public $address;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['status', 'type', 'chip', 'stamp', 'author', 'address'], FullTrimValidator::class, 'on' => [self::SCENARIO_LIST_ADS]],
            [['status', 'type', 'chip', 'stamp', 'author', 'address'], 'filter', 'filter' => 'strip_tags', 'on' => [self::SCENARIO_LIST_ADS]],
            [['status', 'type', 'chip', 'stamp', 'author', 'address'], 'string', 'max' => 255, 'on' => [self::SCENARIO_LIST_ADS]],
            [['author', 'address'], 'string', 'min' => 3, 'on' => [self::SCENARIO_LIST_ADS]],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_MODERATION, self::STATUS_CLOSED, self::STATUS_REJECTED], 'on' => [self::SCENARIO_LIST_ADS]],
            ['type', 'in', 'range' => [Ad::TYPE_LOST, Ad::TYPE_FOUND], 'on' => [self::SCENARIO_LIST_ADS]],
            [['id'], 'integer', 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_LIST_ADS, self::SCENARIO_AD_PHOTOS]],
            [['id'], PGIdValidator::class, 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_LIST_ADS, self::SCENARIO_AD_PHOTOS]],
            [['id'], 'required', 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_AD_PHOTOS]],
            [['id_species', 'id_breed'], 'integer', 'on' => [self::SCENARIO_LIST_ADS]],
            [['id_species', 'id_breed'], PGIdValidator::class, 'on' => [self::SCENARIO_LIST_ADS]],
            [['date_event', 'created_at'], 'date', 'format' => 'php:Y-m-d', 'on' => [self::SCENARIO_LIST_ADS]],
        ];
    }

    /**
     * @return array|false|null
     */
    public function findAd()
    {
        if (!$this->validate()) {
            return false;
        }

        $model = $this->prepareQuery()
            ->one();

        return ($model === null) ? null : $this->formatAd($model);
    }

    /**
     * @param int $page
     * @param int $limit
     * @return array|false
     */
    public function findAds(int $page = 1, int $limit = 10)
    {
        $this->setScenario(self::SCENARIO_LIST_ADS);

        if (!$this->validate()) {
            return false;
        }

        $query = $this->prepareQuery();

        if (!empty($this->status) && $this->status === self::STATUS_MODERATION) {
            $query->orderBy([
                'ads.is_priority_for_moderation' => SORT_DESC,
                'ads.created_at' => SORT_DESC,
            ]);
        } else {
            $query->orderBy(['ads.created_at' => SORT_DESC]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'defaultPageSize' => $limit,
                'page' => ($page - 1),
            ],
        ]);

        $items = [];
        $models = $dataProvider->getModels();
        foreach ($models as $model) {
            $items[] = $this->formatAd($model);
        }

        return [
            'items' => $items,
            'total_count' => $dataProvider->getTotalCount(),
            'pages_count' => $dataProvider->getPagination()->getPageCount(),
        ];
    }

    /**
     * @return array|false|null
     */
    public function findAdPhotos()
    {
        $this->setScenario(self::SCENARIO_AD_PHOTOS);

        if (!$this->validate()) {
            return false;
        }

        $model = Ad::findOne(['id' => $this->id]);

        if ($model === null) {
            return null;
        }

        if (empty($model->photo) && empty($model->stamp_photo)) {
            return [];
        }

        $arr = $this->formatAd($model, true);

        return [
            'photo' => ArrayHelper::getValue($arr, 'photo', []),
            'stamp_photo' => ArrayHelper::getValue($arr, 'stamp_photo', []),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    private function prepareQuery()
    {
        $query = Ad::find()
            ->with('species')
            ->with('breed')
            ->with('color')
            ->with('address')
            ->with('author')
            ->with(['moderator' => function ($q) {
                /* @var $q \yii\db\ActiveQuery */
                $q->with('organization');
            }]);

        if ($this->scenario == self::SCENARIO_DEFAULT) {
            $query->andWhere(['ads.id' => $this->id]);
        } elseif ($this->scenario == self::SCENARIO_LIST_ADS && !empty($this->id)) {
            $query->andWhere(new Expression('ads.id::text ilike \'' . $this->id . '%\''));
        }

        if (!empty($this->status)) {
            // для разделения по вкладкам списка объъявлений:
            // - "Список активных объявлений": все активные объявления, любых типов, в статусах "На модерации" и "Одобрено модератором"
            // - Вкладка "На модерации";
            // - Вкладка "Архив объявлений";
            // - Вкладка "Отклонённые";
            switch ($this->status) {
                case self::STATUS_ACTIVE:
                    $query->andWhere(['ads.is_active' => true]);
                    break;
                case self::STATUS_MODERATION:
                    $query->andWhere([
                        'ads.is_active' => true,
                        'ads.verify_status' => null,
                    ]);
                    break;
                case self::STATUS_CLOSED:
                    $query->andWhere(['ads.is_active' => false]);
                    break;
                case self::STATUS_REJECTED:
                    $query->andWhere([
                        'ads.is_active' => false,
                        'ads.verify_status' => false,
                        'ads.closed_by' => Ad::CLOSED_AUTO_CENSOR
                    ]);
                    break;
                default:
                    break;
            }
        }

        $query->andFilterWhere(['ads.type' => $this->type]);
        $query->andFilterWhere(['ads.date_event' => $this->date_event]);
        $query->andFilterWhere(['ads.id_species' => $this->id_species]);
        $query->andFilterWhere(['ads.id_breed' => $this->id_breed]);

        if (!empty($this->chip)) {
            $query->andWhere(['ilike', 'ads.chip', $this->chip . '%', false]);
        }
        if (!empty($this->stamp)) {
            $query->andWhere(['ilike', 'ads.stamp', $this->stamp . '%', false]);
        }

        if (!empty($this->created_at)) {
            $query->andWhere(new Expression('ads.created_at::date = \'' . $this->created_at . '\''));
        }

        if (!empty($this->author)) {
            $query->leftJoin(AdAuthor::tableName() . ' ao', 'ao.id = ads.id_author')
                ->andWhere([
                    'or',
                    ['ilike', 'ao.last_name', $this->author . '%', false],
                    ['ilike', 'ao.first_name', $this->author . '%', false],
                    ['ilike', 'ao.middle_name', $this->author . '%', false]
                ]);
        }
        if (!empty($this->address)) {
            $query->leftJoin(AdAddress::tableName() . ' addr', 'addr.id = ads.id_address')
                ->andWhere([
                    'or',
                    ['ilike', 'addr.pobox',      '%' . $this->address . '%', false],
                    ['ilike', 'addr.city',       '%' . $this->address . '%', false],
                    ['ilike', 'addr.settlement', '%' . $this->address . '%', false],
                    ['ilike', 'addr.area',       '%' . $this->address . '%', false],
                    ['ilike', 'addr.street',     '%' . $this->address . '%', false],
                    ['ilike', 'addr.house',      '%' . $this->address . '%', false],
                ]);
        }

        return $query;
    }

    /**
     * @param \app\models\db\found_pet\Ad $model
     * @param bool                        $withPhotos
     * @return array
     */
    private function formatAd($model, $withPhotos = false)
    {
        $result = $model->toArray([], ['species', 'breed', 'color', 'address', 'author', 'moderator']);
        $result['sex'] = $this->convertSex($model);
        // статус для отображения на фронте
        $result['status'] = $this->resolveStatus($model);

        if ($withPhotos === false) {
            return $result;
        }

        if (!empty($result['photo'])) {
            $photos = $result['photo'];
            foreach ($photos as $key => $photo) {
                if (!empty($photo['id'])) {
                    $url = $this->prepareImage($photo['id']);
                    if ($url !== null) {
                        $photos[$key]['url'] = $url;
                    }
                }
            }
            $result['photo'] = array_values($photos);
        }

        if (!empty($result['stamp_photo'])) {
            $uid = $result['stamp_photo'];
            $result['stamp_photo'] = [
                'id' => $uid,
            ];
            $url = $this->prepareImage($uid);
            if ($url !== null) {
                $result['stamp_photo']['url'] = $url;
            }
        }

        return $result;
    }

    /**
     * @param string $uid
     * @return string|null
     */
    private function prepareImage($uid)
    {
        $module = \Yii::$app->getModule('foundPet');
        $env = ArrayHelper::getValue($module->params, 'mosru_env');
        $serviceUrl = ArrayHelper::getValue($module->params, ['mosru_urls', $env, 'file_service_url']);
        if (!$serviceUrl) {
            return null;
        }
        $path = strtr($serviceUrl, ['{uid}' => $uid]);

        try {
            $contents = file_get_contents($path);
            if (empty($contents)) {
                return null;
            }
            $finfo = new \finfo(FILEINFO_MIME);
            $mime = $finfo->buffer($contents);
            if (empty($mime)) {
                return null;
            }
            $arr = explode(';', $mime);
            $mimeType = $arr[0];
            $url = 'data: ' . $mimeType . ';base64,' . base64_encode($contents);
        } catch (\Throwable $e) {
            return null;
        }

        return $url;
    }

    /**
     * @param \app\models\db\found_pet\Ad $model
     * @return int
     */
    private function resolveStatus($model)
    {
        switch ($model->closed_by) {
            case Ad::CLOSED_BY_MODERATOR:
                return self::IS_CLOSED_BY_MODERATOR;
            case Ad::CLOSED_BY_AUTHOR:
                return self::IS_CLOSED_BY_AUTHOR;
            case Ad::CLOSED_AUTO:
                return self::IS_CLOSED_AUTO;
            case Ad::CLOSED_AUTO_CENSOR:
                return self::IS_CLOSED_AUTO_CENSOR;
            default:
                break;
        }

        if ($model->is_active === true) {
            if ($model->verify_status === null) {
                return self::IS_BEENG_MODERATED;
            }
            if ($model->verify_status === true) {
                return self::IS_APPROVED;
            }
        }

        return null;
    }

    /**
     * @param \app\models\db\found_pet\Ad $model
     * @return string|null
     */
    private function convertSex($model)
    {
        switch ($model->sex) {
            case 1:
                return 'мужской';
            case 2:
                return 'женский';
            default:
                return null;
        }
    }
}
