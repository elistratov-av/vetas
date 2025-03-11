<?php

namespace app\components;

use yii\db\ActiveRecord;

class Tree
{
    /* @var ActiveRecord */
    public $item;

    public static function getPointers(array $items, $sort = false)
    {
        $pointers = [];

        /*
         * первый раз сортируем для того, чтобы элементы обрабатывались в правильном порядке
         * и в этом же порядке добавлялись в массив
         * НО паренты все-равно будут добавляться раньше и в случайном порядке по мере обработки parent_id
         */
        if ($sort) {
            static::sortItems($items);
        }

        foreach ($items as $item) {
            $item = (object)$item;

            if (!isset($pointers[$item->id])) {
                $pointers[$item->id] = ['children' => []];
            }

            $pointers[$item->id] = array_merge((array)$item, $pointers[$item->id]);

            if ($item instanceof TreeModel) {
                $pointers[$item->id]['item'] = $item;
            }

            if ($parent_id = ((object)$item)->parent_id) {
                if (!isset($pointers[$parent_id])) {
                    $pointers[$parent_id] = ['children' => []];
                }

                $pointers[$parent_id]['children'][] = &$pointers[$item->id];
            }
        }

        /*
         * после построения $pointers паренты будут находится не на своих местах,
         * т.к. они добавлялись по мере обработки parent_id
         * поэтому необходимо заново отсортировать все поинтеры
         * НО при этом порядок вхождения поинтеров в ['children'] был определен при первой сортировке в начале блока
         */
        if ($sort) {
            static::sortItems($pointers);
        }

        return $pointers;
    }

    // позволяет вернуть все корни всех деревьев, например для документов
    public static function buildTree(array $models, $root_id = null, $sort = false)
    {
        $pointers = static::getPointers($models, $sort);

        if ($pointers[$root_id] ?? false) {
            return $pointers[$root_id]['children'];
        }

        $roots = [];
        /** @var TreeModel $pointer */
        foreach ($pointers as $id => &$pointer) {
            if (!((object)$pointer)->parent_id ?? false) {
                $roots[$id] = $pointer;
            }
        }

        return $roots;
    }

    public static function getNestedChildren($items, $include_empty = false)
    {
        $result = [];
        foreach ($items as $item) {
            if (!empty($item['model']->name) || $include_empty) {
                $result[$item['id']] = $item;
            }
            $result = $result + (count($item['children'])
                    ? static::getNestedChildren($item['children'], $include_empty)
                    : []
                );
        }

        return $result;
    }

    protected static function sortItems(&$items)
    {
        uasort($items, function ($a, $b) {
            $a = (object)$a;
            $b = (object)$b;

            if ($a->sort === $b->sort) {
                return ($a->id < $b->id) ? -1 : 1;
            }
            return ($a->sort < $b->sort) ? -1 : 1;
        });
    }
}
