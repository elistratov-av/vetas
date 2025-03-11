<?php

namespace app\controllers\wtf;

use Yii;
use app\interfaces\ActiveRecordInterface;
use app\models\BaseActiveRecord;
use app\components\ActiveDataFilter;
use yii\base\ActionEvent;
use yii\data\ActiveDataProvider;
use yii\filters\Cors;
use yii\rest\ActiveController;
use yii\rest\Serializer;
use yii\web\BadRequestHttpException;
use yii\web\Response;

class BaseApiController extends ActiveController
{
    const PARAM_FILTER = '@filter'; // для фильтрации с использованием ActiveDataFilter
    const PARAM_FIELDS = '@fields'; // какие поля модели необходимо отобразить. Например для FormData, чтобы показывать не всю форму
    const PARAM_EXPAND = '@expand'; // для разворачивания связанных моделей
    const PARAM_LOAD = '@load'; // загрузка полей формы предварительными данными, например для формы модели связанной по внешнему ключу

    /** @var bool */
    public $enableCsrfValidation = false;

    /** @var yii\db\Connection */
    public $connection;

    /** @var string */
    protected $_fieldsParam;

    /** @var string */
    protected $_expandParam;

    /** @var Serializer */
    public $serializer = [
        'class' => Serializer::class,
    ];

    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);

        $this->serializer['fieldsParam'] = $this->_fieldsParam = $config['fieldsParam'] ?? self::PARAM_FIELDS;
        $this->serializer['expandParam'] = $this->_expandParam = $config['expandParam'] ?? self::PARAM_EXPAND;

        $this->serializer = Yii::createObject($this->serializer);

        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");

        Yii::$app->response->format = Response::FORMAT_JSON;
        $this->connection = Yii::$app->db;
    }

    public function actions()
    {
        $actions = parent::actions();

        foreach ($actions as $key => $action) {
            if (is_string($action) && class_exists($action)) {
                $actions[$key] = $action = [
                    'class' => $action,
                ];
            }

            if (is_array($action)) {
                $actions[$key]['modelClass'] = $action['modelClass'] ?? $this->modelClass;
                $actions[$key]['checkAccess'] = $action['checkAccess'] ?? [$this, 'checkAccess'];
            }
        }

        return $actions;
    }

    /**
     * @param $action
     * @return bool
     * @throws BadRequestHttpException
     */
    public function beforeAction($action)
    {
        if (property_exists($action, 'dataFilter') && $action->dataFilter === null) {
            if ($searchModelClass = BaseActiveRecord::getModelClass(ActiveRecordInterface::MODEL_CLASS_SEARCH_MODEL, $this->modelClass)) {
                $action->dataFilter = [
                    'class' => ActiveDataFilter::class,
                    'filterAttributeName' => static::PARAM_FILTER,
                    'searchModel' => $searchModelClass,
                ];
            }
        }

        return parent::beforeAction($action);
    }

    /**
     * We need to rewrite parent tree because of serialisation with index by primary keys
     * {@inheritdoc}
     */
    public function afterAction($action, $result)
    {
        $event = new ActionEvent($action);
        $event->result = $result;
        $this->trigger(self::EVENT_AFTER_ACTION, $event); // @TODO разобраться и прокомментировать для чего это

        if($result instanceof ActiveDataFilter && $result->hasErrors()){
            throw new BadRequestHttpException('Wrong filters format');
        }

        switch ($action->id) {
            case 'index':
                $result->setSort(['enableMultiSort' => true]);
                /** @var ActiveDataProvider $result */
                if (!($perPage = (int)Yii::$app->getRequest()->get('per-page')) || $perPage === -1) {
                    // если в запросе не задан пейджинг, отключаем pager т.к. на текущий момент все осуществляется силами фронта
                    $result->setPagination(false);
                } elseif ((int)$perPage > 0) {
                    $result->setPagination(['pageSize' => $perPage]);
                }
                /*
                 * @TODO
                 * здесь потенциальный косяк, т.к. в IndexAction кривая реализация, которая вместо
                 * ожидаемого ActiveDataProvider при криво заданном фильтре может вернуть ActiveDataFilter
                 */
                $total = $result->getTotalCount();

                /*
                 * если запрашивают сортировку по какому-то полю, то необходимо отключить индексацию по ключу
                 * т.к. индексированный кастомными ключами массив в JS будет расцениваться как объект, а не как массив
                 * а в JS-объекте свойства всегда следуют в алфавитном порядке, что сводит кастомную сортировку на нет
                 *
                 * индексация же по ключу необходима для быстрого связываения related-объектов
                 */
                $result = $this->serializeData($result, Yii::$app->getRequest()->get('sort') ? false : 'id');
                $result['@itemsTotal'] = $total;
                break;
            default:
                $result = $this->serializeData($result, false);
        }

        return $result;
    }

    /**
     * @param $value
     * @param bool $combine
     * @return array
     */
    private function _explode($value, bool $combine = false): array
    {
        if (is_numeric($value)) {
            $value = (array)$value;
        } else {
            if (is_string($value)) {
                $value = array_map('trim', explode(',', $value));
            } else {
                if (!is_array($value)) {
                    $value = [];
                }
            }
        }

        return $combine ? array_combine($value, $value) : $value;
    }

    /**
     * @param BaseActiveRecord $model
     * @return array
     */
    protected function _getRequestedExpandedFields(BaseActiveRecord $model): array
    {
        if ($fields = $this->_explode(Yii::$app->request->get($this->_expandParam), true)) {
            return array_intersect_key($model->extraFields(), $fields);
        }
        return [];
    }

    public function behaviors()
    {
        return [
            'corsFilter' => [
                'class' => Cors::class,
                'cors' => [
                    // restrict access to domains:
                    'Origin' => [
                        '*',
                    ],
                    'Access-Control-Request-Method' => ['POST', 'GET', 'DELETE', 'PUT', 'PATCH'],
                    'Access-Control-Allow-Credentials' => true,
                    'Access-Control-Max-Age' => 1,// Cache (seconds)
                ],
            ]
        ];
    }

    /**
     * индексация по ID для того, чтобы по внешнему ключу в JS можно было получить related object
     * например в EditPageEmbedded для dModules[item.module_type] ? dModules[item.module_type].title : '...'
     *
     * Но эта индескация также создает проблему с декодированием JSON т.к. любой массив начинающийся не с 0
     * JS автоматически интерпретирует как объект, а в JS-объекте свойства всегда сортируются в алфавитном порядке
     * и такие свойства теряют любую кастомную сортировку
     *
     * @param $data
     * @param string|null $indexedBy
     * @return array|mixed
     */
    protected function serializeData($data, string $indexedBy = null)
    {
        $result = null;
        $data = $this->serializer->serialize($data);

        if ($indexedBy) {
            foreach ((array)$data as $key => $item) {
                // reindex all. not only numeric keys
                if (isset($item[$indexedBy])) {
                    $result[$item[$indexedBy]] = $item;
                } else {
                    $result[$key] = $item;
                }
            }
        }

        return $result ?? $data;
    }
}
