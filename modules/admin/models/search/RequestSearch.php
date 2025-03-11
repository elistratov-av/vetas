<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 29.07.19
 * Time: 12:05
 */

namespace app\modules\admin\models\search;

use app\models\db\ChangeRequest;
use app\modules\admin\data\AdminDataProvider;
use yii\base\Model;
use yii\db\Expression;

/**
 * Class RequestSearch
 * @package app\modules\admin\models\search
 */
class RequestSearch extends Model
{
    /**
     * @var int
     */
    public $id;
    /**
     * @var string
     */
    public $entity_name;
    /**
     * @var string
     */
    public $fullname;
    /**
     * @var int
     */
    public $author;
    /**
     * @var int
     */
    public $author_org;
    /**
     * @var string
     */
    public $short_name;
    /**
     * @var string
     */
    public $type;
    /**
     * @var string
     */
    public $state;
    /**
     * @var string
     */
    public $last_update;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id', 'entity_name', 'author', 'fullname', 'author_org', 'short_name', 'type', 'state'], 'safe']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return (new ChangeRequest())->attributeLabels();
    }

    /**
     * @param $params
     * @return AdminDataProvider
     * @throws \yii\base\InvalidConfigException
     */
    public function search($params)
    {
        $query = ChangeRequest::find()
            ->joinWith(['organization', 'requestAuthor'], true);

        $dataProvider = new AdminDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'last_update' => [
                    'asc' => new Expression('coalesce(change_request.updated_at, change_request.created_at) asc'),
                    'desc' => new Expression('coalesce(change_request.updated_at, change_request.created_at) desc')
                ],
            ],
            'defaultOrder'=>[
                'last_update' => new Expression('coalesce(change_request.updated_at, change_request.created_at) desc')
            ]
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

            $query->andFilterWhere(['in', 'entity_name', $this->entity_name]);
            $query->andFilterWhere(['in', 'users.id', $this->author]);
            $query->andFilterWhere(['in', 'organizations.id', $this->author_org]);
            $query->andFilterWhere(['in', 'type', $this->type]);
            $query->andFilterWhere(['in', 'state', $this->state]);

        return $dataProvider;
    }
}
