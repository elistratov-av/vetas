<?php


namespace app\modules\v1\models;


use app\common\models\UserModel;
use tuyakhov\jsonapi\LinksInterface;
use tuyakhov\jsonapi\ResourceInterface;
use yii\helpers\Inflector;
use yii\helpers\Url;
use yii\web\Link;

/**
 * Class UserResource
 * @package app\modules\v1\models
 *
 * @property array $links
 * @property string $type
 */
class UserResource extends UserModel implements ResourceInterface, LinksInterface
{

    /**
     * @var array
     */
    protected $excludedFields = [
        'password','id', 'created_at', 'updated_at', 'created_by', 'updated_by', 'AdrID', 'OkrugID', 'RayonID',
    ];

    protected $relationships = ['specialists' => 'specialists'];

    /**
     * @return string
     */
    public function getType()
    {
        return 'user';
    }

    /**
     * @param array $fields
     * @return array
     */
    public function getResourceAttributes(array $fields = [])
    {
        $attributes = array_diff($this->fields(), $this->excludedFields);

        foreach ($attributes as $key => $attribute) {
            $attribute = Inflector::camel2id(Inflector::variablize($attribute), '_');

            if (!empty($fields) && !in_array($attribute, $fields, true)) {
                unset($attributes[$key]);
            } else {
                $attributes[$key] = $this->$attribute;
            }
        }

        return $attributes;
    }

    /**
     * @return array
     */
    public function getLinks()
    {
        return [
            Link::REL_SELF => Url::to(Url::base(true).'/v1/users/'.$this->getId()),
        ];
    }

    /**
     * @param $name
     * @return array
     */
    public function getRelationshipLinks($name)
    {
        $primaryLinks = $this->getLinks();
        if (!array_key_exists(Link::REL_SELF, $primaryLinks)) {
            return [];
        }

        $resourceLink = is_string($primaryLinks[Link::REL_SELF]) ? rtrim($primaryLinks[Link::REL_SELF], '/') : null;
        if (!$resourceLink) {
            return [];
        }

        return [
            Link::REL_SELF => "{$resourceLink}/relationships/{$name}",
            'related'      => "{$resourceLink}/{$name}",
        ];
    }

    /**
     * @param array $linked
     * @return array|\tuyakhov\jsonapi\ResourceIdentifierInterface[]
     */
    public function getResourceRelationships(array $linked = [])
    {
        return $this->relationships;
    }

    /**
     * @param $name
     * @param $relationship
     */
    public function setResourceRelationship($name, $relationship)
    {
        $this->relationships[$name] = $relationship;
        return $this;
    }


}
