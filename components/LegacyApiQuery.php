<?php
namespace app\common\components;


class LegacyApiQuery
{
    public static $queryParams = [];

    public static function getSortField($default = null) {
        $result = self::$queryParams['sort'] ?? $default;

        if ($result) {
            $result = preg_replace('/^\-/m', '', $result);
        }

        return $result;
    }

    public static function getSortDirection() {
        $sort_direction = SORT_ASC;

        if (isset(self::$queryParams['sort'])) {
            if (substr(self::$queryParams['sort'], 0, 1) == '-') {
                $sort_direction = SORT_DESC;
            }
        }

        return $sort_direction;
    }

    public static function getPaginationLimit() {
        $default_limit = \Yii::$app->params['pagination_limit'];
        $limit = self::$queryParams['page']['limit'] ?? $default_limit;

        return $limit;
    }

    public static function getPaginationOffset() {
        $default_offset = \Yii::$app->params['pagination_offset'];
        $limit = self::$queryParams['page']['offset'] ?? $default_offset;

        return $limit;
    }
}