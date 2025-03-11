<?php

namespace app\modules\v2\modules\fias\controllers;

use yii\db\Connection;
use yii\db\Expression;
use yii\db\Query;
use app\modules\v2\modules\BaseController;

class FiasController extends BaseController
{
    /**
     * Поиск адресов ФИАС
     *
     * @param string $q
     * @param string $type
     * @param string|null $parent
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function actionAddressSearch(string $q, string $type = 'all', string $parent = null, int $page = 1, int $limit = 10): array
    {
        if($q && \strlen($q) < 6) {
            return null;
        }

        /** @var Connection $db */
        $db = \Yii::$app->dbFias;

        $q = preg_replace('/[^ а-яА-ЯёЁ\d\-]+/ui', '', $q);
        $q = trim($q, ' ');
        $qarr = preg_split('/\s+/',$q);
        $ts_qarr = implode(' & ', array_map(function ($x){return $x.':*';},$qarr));
        
        $tsq = "full_vect @@ to_tsquery('russian', '$ts_qarr')";

        $query = new Query;

        $query->from('"addrs"')
            //->where($tsq)
            ->orderBy(['lvl' => SORT_ASC, 'rnk' => SORT_DESC])
            ->limit($limit)
            ->offset($limit * ($page - 1))
            ->select(['aoguid as uid',
            '"fullname" as name',
            'aolevel',
            '(addrs_priority_by_level(aolevel)) as lvl',
            new Expression('ts_rank(full_vect, to_tsquery(\'russian\', \''.$ts_qarr.'\'), 2) as rnk')]);

        if($q){
            $query->andWhere($tsq);
        }

        if($parent){
            $query->andWhere(['parentguid' => $parent]);
        }

        if($type === 'city'){
            $query->andWhere(['in', 'aolevel',   [1,2,3,4,5,6,90,65]]);
        }
        elseif ($type === 'street'){
            $query->andWhere( ['in', 'aolevel', [7, 91]]);
        }


        $result = $query->all($db);
        if($parent){
            $q2 = new Query();

            $p_text = $q2->from('addrs')->where(['aoguid' => $parent])->select('fullname')->scalar($db);
            if($p_text){
                $result = array_map(function ($x) use ($p_text) {
                    return str_replace($p_text. ', ', '', $x);
                }, $result);
            }

        }

        $totalCount = $query->count('*', $db);
        return [
            'result' => [
                'pages_count' => ceil($totalCount / $limit),
                'total_count' => $totalCount,
                'list' => $result
            ]
        ];
    }
}
