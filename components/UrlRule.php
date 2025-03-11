<?php

namespace app\components;

use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\helpers\Inflector;
use yii\web\UrlRule as WebUrlRule;
use yii\web\UrlRuleInterface;

class UrlRule extends \yii\rest\UrlRule
{
    public function init()
    {
        $this->prefix = trim($this->prefix, '/');

        $controllers = [];
        foreach ((array)$this->controller as $urlName => $controller) {
            if (is_int($urlName)) {
                $urlName = $this->pluralize ? Inflector::pluralize($controller) : $controller;

                // prefix добавляется только к НЕименованым маршрутам
                if($this->prefix){
                    $urlName =  $this->prefix . '/' . $urlName;
                }

                // при формировании списка контроллеров, в правой части убираем значение, т.к. далее там должен быть паттерн
                $controllers[$urlName] = null;

            } else {
                $controllers[$urlName] = $controller;
            }
        }

        $defaultRoute = '/';
        if($this->prefix){
            $defaultRoute =  $this->prefix . $defaultRoute;
        }

        if(!isset($controllers[$defaultRoute])){
            $controllers[$defaultRoute] = [
                'pattern' => $defaultRoute . '<model:[\w-]+>/<action:[\w-]+>',
                'route' => $defaultRoute . 'base-api/<action>',
            ];
        }

        $this->controller = $controllers;

        parent::init();
    }

    protected function createRules()
    {
        $only = array_flip($this->only);
        $except = array_flip($this->except);
        $patterns = $this->extraPatterns + $this->patterns;
        $rules = [];
        foreach ($this->controller as $controller => $configOrPattern) {

            if($configOrPattern['pattern'] ?? false) {
                $patterns[$configOrPattern['pattern']] = $configOrPattern['route'];
            }

            foreach ($patterns as $pattern => $action) {
                if (!isset($except[$action]) && (empty($only) || isset($only[$action]))) {
                    // костыль к базовому решению
                    $rules[$controller][] = $this->createRule($pattern, $controller,
                        is_array($configOrPattern) ? $configOrPattern : ['route' => $controller . '/' . $action]);
                }
            }
        }

        return $rules;
    }

    /**
     * Creates a URL rule using the given pattern and action.
     * @param string $pattern
     * @param string $patternPrefix
     * @param string $action
     * @return UrlRuleInterface
     */
    protected function createRule($pattern, $patternPrefix, $config)
    {
        $verbs = 'GET|HEAD|POST|PUT|PATCH|DELETE|OPTIONS';
        if (preg_match("/^((?:($verbs),)*($verbs))(?:\\s+(.*))?$/", $pattern, $matches)) {
            $verbs = explode(',', $matches[1]);
            $pattern = isset($matches[4]) ? $matches[4] : '';
        } else {
            $verbs = [];
        }

        $defaultConfig = $this->ruleConfig;
        $defaultConfig['verb'] = $verbs;
        $defaultConfig['pattern'] = rtrim($patternPrefix . '/' . strtr($pattern, $this->tokens), '/');
        if (!empty($verbs) && !in_array('GET', $verbs)) {
            $defaultConfig['mode'] = WebUrlRule::PARSING_ONLY;
        }
        $defaultConfig['suffix'] = $this->suffix;

        return Yii::createObject(array_merge($defaultConfig, $config));
    }

    public function createUrl($manager, $route, $params)
    {
        return parent::createUrl($manager, $route, $params);
    }
}
