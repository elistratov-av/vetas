<?php

namespace app\common\components;

use app\common\components\entity\EntityResourceCache;
use tuyakhov\jsonapi\Inflector;
use tuyakhov\jsonapi\LinksInterface;
use tuyakhov\jsonapi\ResourceIdentifierInterface;
use tuyakhov\jsonapi\ResourceInterface;
use yii\base\Model;
use yii\data\DataProviderInterface;
use yii\helpers\BaseInflector;
use yii\web\Link;
use yii\web\Linkable;

class Serializer extends \tuyakhov\jsonapi\Serializer
{
    /**
     * @inheritdoc
     */
    public function serialize($data)
    {
        if ($data instanceof Model && $data->hasErrors()) {
            return $this->serializeModelErrors($data);
        } elseif ($data instanceof ResourceInterface) {
            $data = $this->serializeResource($data);
            $this->toCache($data);
        } elseif ($data instanceof DataProviderInterface) {
            $data = $this->serializeDataProvider($data);
            $this->toCache($data);
        }

        return $data;
    }

    /**
     * @param array $data
     */
    private function toCache($data)
    {
        if (\Yii::$app->request->isGet && is_array($data)) {
            EntityResourceCache::set($data);
        }
    }

    /**
     * Fixed bag in serialisation.
     *
     * @param array|\tuyakhov\jsonapi\ResourceInterface $resources
     * @param array $included
     * @param bool $assoc
     * @return array
     */
    protected function serializeIncluded($resources, array $included = [], $assoc = false)
    {
        if (empty($included)) {
            return [];
        }

        $resources = is_array($resources) ? $resources : [$resources];
        $data = [];

        $inclusion = [];
        foreach ($included as $path) {
            if (($pos = strrpos($path, '.')) === false) {
                $inclusion[$path] = [];
                continue;
            }
            $name = substr($path, $pos + 1);
            $key = substr($path, 0, $pos);
            $inclusion[$key][] = $name;
        }

        foreach ($resources as $resource) {
            if (!$resource instanceof  ResourceInterface) {
                continue;
            }
            $relationships = $resource->getResourceRelationships(array_keys($inclusion));
            foreach ($relationships as $name => $relationship) {
                $name = Inflector::member2var($name);
                if ($relationship === null) {
                    continue;
                }
                if (!is_array($relationship)) {
                    $relationship = [$relationship];
                }
                foreach ($relationship as $model) {
                    if (!$model instanceof ResourceInterface) {
                        continue;
                    }

                    if (!array_key_exists($name, $inclusion)) {
                        continue;
                    }

                    $uniqueKey = $model->getType() . '/' . $model->getId();
                    if (!isset($data[$uniqueKey])) {
                        $data[$uniqueKey] = $this->serializeModel($model, $inclusion[$name]);
                    }
                    if (!empty($inclusion[$name])) {
                        $data = array_merge($data, $this->serializeIncluded($model, $inclusion[$name], true));
                    }
                }
            }
        }

        return $assoc ? $data : array_values($data);
    }

    /**
     * Format member names according to recommendations for JSON API implementations.
     * For example, both 'firstName' and 'first_name' will be converted to 'first-name'.
     * @link http://jsonapi.org/format/#document-member-names
     * @param $var string
     * @return string
     */
    public static function var2member($var)
    {
        return BaseInflector::camel2id(Inflector::variablize($var), '_');
    }

    /**
     * @inheritdoc
     */
    protected function serializeModelErrors($model)
    {
        $result = parent::serializeModelErrors($model);

        try {
            $monitoring = \Yii::$app->get('monitoring');
            if ($monitoring !== null) {
                $result = $monitoring->formatErrorData($result);
            }
        } catch (\Throwable $e) {
            // do nothing
        }

        return $result;
    }

    /**
     * @param array $included
     * @param ResourceInterface $model
     * @return array
     */
    protected function serializeModel(ResourceInterface $model, array $included = [])
    {
        $fields = $this->getRequestedFields();
        $type = $this->pluralize ? Inflector::pluralize($model->getType()) : $model->getType();
        $fields = isset($fields[$type]) ? $fields[$type] : [];

        $topLevel = array_map(function($item) {
            if (($pos = strrpos($item, '.')) !== false) {
                return substr($item, 0, $pos);
            }
            return $item;
        }, $included);

        $attributes = $model->getResourceAttributes($fields);
        $attributes = array_combine($this->prepareMemberNames(array_keys($attributes)), array_values($attributes));

        $data = array_merge($this->serializeIdentifier($model), [
            'attributes' => $attributes,
        ]);

        $relationships = ($this->shouldReturnRelations($type) === true) ? $model->getResourceRelationships($topLevel) : [];

        if (!empty($relationships)) {
            foreach ($relationships as $name => $items) {
                $relationship = [];
                if (is_array($items)) {
                    foreach ($items as $item) {
                        if ($item instanceof ResourceIdentifierInterface) {
                            $relationship[] = $this->serializeIdentifier($item);
                        }
                    }
                } elseif ($items instanceof ResourceIdentifierInterface) {
                    $relationship = array_merge($this->serializeIdentifier($items), [
                        'attributes' => $items->getResourceAttributes(),
                    ]);
                }
                $memberName = $this->prepareMemberNames([$name]);
                $memberName = reset($memberName);
                if (!empty($relationship)) {
                    $data['relationships'][$memberName]['data'] = $relationship;
                }
                if ($model instanceof LinksInterface) {
                    $links = $model->getRelationshipLinks($memberName);
                    if (!empty($links)) {
                        $data['relationships'][$memberName]['links'] = Link::serialize($links);
                    }
                }
            }
        }

        if ($model instanceof Linkable) {
            $data['links'] = Link::serialize($model->getLinks());
        }

        return $data;
    }

    /**
     * @param string $type
     * @return bool
     */
    private function shouldReturnRelations(string $type)
    {
        $actionId = \Yii::$app->controller->action->getUniqueId();

        if ($type == 'description_type' && $actionId == 'v1/visits/service-types-description') {
            /* @see https://jira.altarix.ru/browse/VETAIS-1129 */
            // нехватка памяти в GET v1/visits/{id}/relationships/service-types-description
            return false;
        }

        return true;
    }
}
