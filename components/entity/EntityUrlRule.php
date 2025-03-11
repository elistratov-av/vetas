<?php

namespace app\common\components\entity;


use yii\rest\UrlRule;

class EntityUrlRule extends UrlRule
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    public $tokens = [
        '{id}' => '<id:\\d[\\d,]*>',
        '{parent_id}' => '<parent_id:\\d[\\d,]*>',
        '{entity}' => '<entity>',
        '{entity_id}' => '<entity_id:\d+>',
    ];

    public function init()
    {
        $this->entityManager = \Yii::$container->get('entityManager');

        $this->loadRoutes();
        return parent::init();
    }

    protected function loadRoutes()
    {
        $routes = [];

        foreach ($this->entityManager->getAllEntitiesNames() as $entity) {
            $entity = 'v1/' . $entity;
            $routes[$entity] = 'v1/entity';
        }

        $routes = array_merge($this->getStaticRoutes(), $routes);

        $this->controller = $routes;
    }

    /**
     * @return mixed
     */
    protected function getStaticRoutes()
    {

        if(isset(\Yii::$app->params['static_routes'])){
            $static_routes = \Yii::$app->params['static_routes'];
            $static_routes = array_combine($static_routes, $static_routes);
            return $static_routes;
        }
        return [];
    }

    public function createUrl($manager, $route, $params)
    {
        //return parent::createUrl($manager, $route, $params);
        foreach ($this->getStaticRoutes() as $static_route){

            $static_route = str_replace('-', '_', $static_route);
            $route1 = str_replace('-', '_', $route);
            if(strpos($route1, $static_route) !== false){
                return parent::createUrl($manager, $route, $params);
            }
        }

        $route = \Yii::$container->get('entityInstance')->getAliasName();
        if (!empty($params) && ($query = http_build_query($params)) !== '') {
            $route .= '?' . $query;
        }
        $route = ltrim($route, '/');
        return 'v1/'.$route;
    }
}
