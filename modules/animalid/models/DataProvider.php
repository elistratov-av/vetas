<?php

namespace app\modules\animalid\models;

use app\modules\animalid\models\db\Id_Map;
use yii\db\Expression;
use yii\db\Query;

/**
 * DataProvider для работы экспорта AnimalID
 * @package app\modules\animalid\models
 */
class DataProvider
{

    /**
     * @var string Текущая модель
     */
    protected $model_name;

    /**
     * @var Query
     */
    protected $query;

    /**
     * @var array
     */
    protected $current_batch;

    /**
     * @var int
     */
    protected $current_offset = 0;

    /**
     * Модели для экспорта
     * Порядок важен
     */
    const MODELS_TO_EXPORT = [
        'companies',
        'pets',
    ];

    const BATCH_SIZE = 100;

    public function __construct($model_name)
    {
        $this->model_name = $model_name;
        $this->query = $this->getQuery();
    }

    /**
     * Следующий элемент для синхронизации
     * @return array|bool|mixed
     * @throws \app\modules\animalid\skeletons\exceptions\IntegrationException
     */
    public function getNext()
    {
        $item = $this->getNextInternal();

        if (empty($item)) {
            return false; // Закончились
        }

        /*
         * К стандартному виду
         */
        $item = $this->formatQueryResult($item);

        if (!DataPreProcessor::filterMessageData($item, DataPreProcessor::DIRECTION_OUT)) {
            return false;
        }

        $item = DataPreProcessor::convertMessageData($item, DataPreProcessor::DIRECTION_OUT);

        return $item;
    }

    /**
     * Возвращает следующий элемент из БД или из 100 рапрошенных ранее
     * @return bool|array
     */
    protected function getNextInternal()
    {
        /*
         * Забираем по 100 из бд
         * и складываем в приватный аттрибут
         * Когда попросят новый - отдаем следующий оттуда
         * Если закончились - запрашиваем из бд снова
         */
        if (empty($this->current_batch)) {
            $this->current_batch = $this
                ->query
                ->offset($this->current_offset)
                ->all();
            $this->current_offset += self::BATCH_SIZE;
        }

        if (!empty($this->current_batch) && is_array($this->current_batch)) {
            return array_pop($this->current_batch);
        }

        return false;
    }

    /**
     * Пытаемся найти маппинг/создать или обновить маппинг
     *
     * @param $formatted_data
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function mappingStore($formatted_data)
    {
        $id_map = self::_mappingFind($formatted_data);

        if (!$id_map) {
            self::_mappingCreate($formatted_data);
        } else {
            self::_mappingUpdateTimestamp($id_map);
        }
    }

    /**
     * Обновляем таймштамп маппинга
     * @param Id_Map $id_map
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    protected function _mappingUpdateTimestamp($id_map)
    {
        $id_map->timestamp = new Expression('now()');
        if (!$id_map->update()) {
            $errors = $id_map->getErrorSummary(true);
            $message = 'Ошибка при обновлении Id_Map ' . implode("\n", array_values($errors));
            \Yii::error($message, 'animalid_output');
        }
    }

    /**
     * Создаем маппинг
     * На данном этапе мы не знаем их ID
     *
     * @param $formatted_data
     */
    protected function _mappingCreate($formatted_data)
    {
        $map = new Id_Map();
        if ($this->model_name === 'pets') {
            $map->our = $formatted_data['id'];
            $map->type = $this->model_name;
        } else if ($formatted_data['id'] < 0) {
            $map->our = $formatted_data['id'] * (-1); //убираем минус у негосов
            $map->type = $this->model_name;
        } else if ($formatted_data['id'] > 0) {
            $map->our = $formatted_data['id'];
            $map->type = 'organizations';
        }

        $map->timestamp = new Expression('now()');

        if (!$map->save()) {
            $errors = $map->getErrorSummary(true);
            $message = 'Ошибка при сохранении Id_Map ' . implode("\n", array_values($errors));
            \Yii::error($message, 'animalid_output');
        }
    }

    /**
     * Пытается найти маппинг в бд
     *
     * @param $formatted_data
     * @return Id_Map|bool|null
     */
    public function _mappingFind($formatted_data)
    {
        if ($this->model_name == 'companies') {
            /*
             * из-за отсутствия вьюхи для госов приходится перебирать все варианты, отсеивая госов
             * один да вернет
             */
            $id_map = Id_Map::findOne(['our' => $formatted_data['id'], 'type' => 'organizations']);
            if ($id_map === null) {
                $id_map = Id_Map::findOne(['our' => $formatted_data['id'] * (-1), 'type' => 'companies']);
            }
        } else {
            $id_map = Id_Map::findOne(['our' => $formatted_data['id'], 'type' => $this->model_name]);
        }

        return $id_map;
    }

    /**
     * Формирмируем стандартный ответ
     * @param $data
     * @return array
     */
    protected function formatQueryResult($data)
    {
        /*
         * из массива вида [ 'id' => 1, 'name' = 'cat']
         * формируем массив вида
         *    [ 'id' => 1,
         *     'type' = 'кошки',
         *     'data' => [
         *        'name' = 'cat'
         *     ]
         *   ]
         */
        $id = $data['id'];
        unset($data['id']);
        $res = ['id' => $id, 'type' => $this->model_name, 'data' => $data];
        return $res;
    }

    /**
     * Возвращает подготовленный запрос к БД
     * @return Query
     */
    protected function getQuery()
    {
        return (new Query())
            ->from('animalid.view_' . $this->model_name . ' as m')
            ->select('DISTINCT ON ("m".id) "m".*')
            ->where([
                'OR',
                [
                    'NOT IN',
                    'm.id',
                    (new Query())
                        ->select('our')
                        ->from('animalid.id_mapping')
                        ->where(['type' => $this->model_name])
                ],
                [
                    '>',
                    'm.upddate',
                    (new Query())
                        ->select('timestamp')
                        ->from('animalid.id_mapping')
                        ->where([
                            'AND',
                            ['id_mapping.type' => $this->model_name],
                            new Expression('id_mapping.our = m.id')
                        ])
                ]
            ])
            ->limit(self::BATCH_SIZE)
            ->orderBy('"m".id');
    }
}
