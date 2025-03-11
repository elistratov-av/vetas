<?php

namespace app\common\components\entity;

use Yii;
use yii\caching\TagDependency;
use yii\db\ActiveRecord;
use yii\web\Request;
use app\modules\v1\models\EntityResource;

/**
 * Class EntityResourceCache
 * @package app\common\components\entity
 */
class EntityResourceCache
{
    /**
     * @return \yii\caching\FileCache
     */
    public static function getCache()
    {
        return null;
        //return Yii::$app->get('entityCache', false);
    }

    /**
     * @return mixed
     */
    public static function get()
    {
        $time = microtime(true);

        $cache = static::getCache();
        if ($cache === null) {
            return false;
        }

        $key = static::prepareKey();
        if ($key === false) {
            return false;
        }

        $data = $cache->get($key);
        self::log($time, $key, $data !== false);

        return $data;
    }

    /**
     * @param mixed $value
     * @param int   $duration
     * @return bool
     */
    public static function set($value, $duration = null)
    {
        $cache = static::getCache();
        if ($cache === null) {
            return true;
        }

        $key = static::prepareKey();
        if ($key === false) {
            return true;
        }

        $tags = static::prepareTags($key);

        return $cache->set($key, $value, $duration, new TagDependency(['tags' => $tags]));
    }

    /**
     * @return array|false
     */
    protected static function prepareKey()
    {
        $params = Yii::$app->request->getQueryParams();

        $entityName = self::getEntityNameFromRequest(Yii::$app->request);
        if (!empty($entityName)) {
            // N.b.: 'entity' уже используется в getrel для обозначения зависимой сущности
            $params['model'] = $entityName;
        }

        $name = isset($params['entity']) ? $params['entity'] : $entityName;

        if (empty($name)) {
            return false;
        }

        /** @var EntityManager $entityManager */
        $entityManager = \Yii::$container->get('entityManager');
        try {
            $entityInstance = $entityManager->getEntity($name);
            if (!($entityInstance instanceof EntityInstance)) {
                return false;
            }
        } catch (\Throwable $e) {
            return false;
        }

        if (!empty($params['include'])) {
            $includes = explode(',', $params['include']);
            foreach ($includes as $key => $include) {
                $includes[$key] = trim($include);
            }
            asort($includes);
            $params['include'] = $includes;
        }

        ksort($params);

        return array_merge(['route' => Yii::$app->requestedRoute, 'action' => Yii::$app->requestedAction->id], $params);
    }

    /**
     * @param array $key
     * @return array
     */
    protected static function prepareTags($key)
    {
        $tags = [];

        if (isset($key['action'])) {
            if ($key['action'] == 'getrel') {
                $tags[] = 'view-' . $key['entity'];
                $tags[] = 'relationships-' . $key['entity'];
            } else {
                $tags[] = $key['action'] . '-' . $key['model'] . (isset($key['id']) ? ('-' . $key['id']) : '');
            }
        }

        if (isset($key['include'])) {
            foreach ($key['include'] as $include) {
                $tags[] = 'include-' . $include;
            }
        }

        /** @var EntityManager $entityManager */
        $entityManager = \Yii::$container->get('entityManager');
        $name = isset($key['entity']) ? $key['entity'] : $key['model'];
        $entityInstance = $entityManager->getEntity($name);

        if (!($entityInstance instanceof EntityInstance)) {
            return $tags;
        }

        foreach ($entityInstance->getRelationships() as $relationship) {
            $tags[] = 'relationships-' . $relationship['link'];
        }

        foreach ($entityInstance->getAllNestedCollections() as $collection) {
            $relName = $collection->getEntityCollectionName();
            $tags[] = 'relationships-' . $relName;
        }

        return $tags;
    }

    /**
     * @param \yii\base\ModelEvent $event
     */
    public static function invalidateAfterModelEvent($event)
    {
        $cache = static::getCache();
        if ($cache === null) {
            return;
        }

        /* @var $model \yii\db\ActiveRecord */
        $model = $event->sender;
        if ($model instanceof EntityResource) {
            $entityName = $model->entityInstance->getAliasName();
            $tags = [
                'index-' . $entityName,
                'include-' . $entityName,
                'relationships-' . $entityName,
            ];
            switch ($event->name) {
                case ActiveRecord::EVENT_AFTER_INSERT:
                    break;
                case ActiveRecord::EVENT_AFTER_UPDATE:
                case ActiveRecord::EVENT_AFTER_DELETE:
                    $tags[] = 'view-' . $entityName . '-' . $model->getId();
                    break;
                default:
                    $tags = [];
                    break;
            }

            if (!empty($tags)) {
                static::invalidate($tags);
            }
        }
    }

    /**
     * @param string $name
     * @param int $id
     * @return string []
     */
    public static function invalidateByEntity(string $name, int $id = null)
    {
        if(!$name)
            return [];

        $tags = [
            'index-' . $name,
            'include-' . $name,
            'relationships-' . $name,
        ];

        if($id){
            $tags[] = 'view-' . $name . '-' . $id;
        }
        static::invalidate($tags);
    }

    /**
     * @param string|array $tags
     */
    public static function invalidate($tags)
    {
        $cache = static::getCache();
        if ($cache === null) {
            return;
        }

        TagDependency::invalidate($cache, $tags);
    }

    /**
     * @return bool
     */
    public static function flush()
    {
        $cache = static::getCache();
        if ($cache === null) {
            return true;
        }

        return $cache->flush();
    }

    /**
     * TODO - позже подключить трейт из MR 465
     * @param Request $req
     * @return null
     * @throws \yii\base\InvalidConfigException
     */
    protected static function getEntityNameFromRequest(Request $req)
    {
        $module = \Yii::$app->controller->module;
        $module_id = $module->id;

        $path = $req->getPathInfo();
        preg_match('/' . $module_id . '\/([a-z-]+)/m', $path, $matches);

        $result = $matches[1] ?? null;

        return $result;
    }

    /**
     * @param float $time
     * @param array $key
     * @param bool  $found
     */
    protected static function log($time, $key, $found)
    {
        try {
            /* @see http://php.net/manual/en/datetime.createfromformat.php#119362 */
            $datetime = \DateTime::createFromFormat('U.u', number_format($time, 6, '.', ''))->format('Y-m-d H:i:s.u');
            Yii::$app->db
                ->createCommand()
                ->insert('entity_cache_log', [
                    'datetime'  => $datetime,
                    'microtime' => $time,
                    'url'       => Yii::$app->request->absoluteUrl,
                    'cache_key' => $key,
                    'found'     => $found,
                ])
                ->execute();
        } catch (\Exception $e) {
            Yii::error('Failed to write caching log');
        }
    }
}
