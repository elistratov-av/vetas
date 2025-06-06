<?php

namespace app\controllers;

use DateTime;
use Yii;
use yii\db\Query;
use yii\web\BadRequestHttpException;
use yii\web\Response;

class CallcenterController extends AppController
{
    public $layout = 'callcenter';

    // Call Log

    public function actionLogCall()
    {
        $fioOperators = $this->getCallTypeIds();
        return $this->render('logcall');
    }

    public function actionSearchCalls()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $params = $this->bindSearchCalls();
        return $this->findCalls($params);
    }

    // region Helpers

    // region Call Log

    private function bindSearchCalls()
    {
        // datetime_call_start, datetime_call_end, created_by, id_organization, first_call_id_type, second_call_id_type, only_my
        $options = [];
        if (!empty($_GET['datetime_call_start'])) {
            $options['datetime_call_start'] = new DateTime($_GET['datetime_call_start']);
        }
        if (!empty($_GET['datetime_call_end'])) {
            $options['datetime_call_end'] = new DateTime($_GET['datetime_call_end']);
        }
        if (!empty($_GET['only_my'])) {
            $onlyMy = filter_var($_GET['only_my'], FILTER_VALIDATE_BOOLEAN);
            if ($onlyMy !== false) {
                $options['only_my'] = $onlyMy;
            }
        }

        if (empty($options['datetime_call_start'] && empty($options['datetime_call_end']))) {
            throw new BadRequestHttpException('Отсутствует обязательный параметр период даты приема звонка.');
        }

        return $options;
    }

    private function findCalls($params)
    {
        $query = (new Query())
            ->select(['jrn.datetime_call', 'fst.cname first_call_type_name', 'sec.cname second_call_type_name',
                'o.short_name org_name', 'u.fullname', 'jrn.comment'])
            ->from('callcenter_call_journal jrn')
            ->leftJoin('call_classification_first_type fst', 'jrn.first_call_id_type = fst.id')
            ->leftJoin('call_classification_second_type sec', 'jrn.second_call_id_type = sec.id')
            ->leftJoin('organizations o', 'jrn.id_organization = o.id')
            ->leftJoin('users u', 'jrn.created_by = u.id')
            ->orderBy('datetime_call DESC');

        // datetime_call_start, datetime_call_end, created_by, id_organization, first_call_id_type, second_call_id_type, only_my
        if (!empty($params['datetime_call_start']) && !empty($params['datetime_call_end'])) {
            $call_start = $params['datetime_call_start']->format('Y-m-d H:i:s');
            $call_end = $params['datetime_call_end']->format('Y-m-d H:i:s');
            $query->andWhere(['between', 'jrn.datetime_call', $call_start, $call_end]); // DD.MM.YYYY HH24:MI:SS
        }

        if (!empty($params['only_my'])) {
            $userId = $this->getCurrentUserId();
            $query->andWhere(['jrn.created_by' => $userId]);
        }

/*        if (!empty($params['visit_from']) || !empty($params['visit_to'])) {
            if (!empty($params['visit_from'])) {
                $from = $params['visit_from']->format('Y-m-d H:i');
                $query->andWhere("LOWER(v.time_range) >= TO_TIMESTAMP('$from', 'YYYY-MM-DD HH24:MI')");
            }
            if (!empty($params['visit_to'])) {
                $to = $params['visit_to']->format('Y-m-d H:i');
                $query->andWhere("UPPER(v.time_range) <= TO_TIMESTAMP('$to', 'YYYY-MM-DD HH24:MI')");
            }
        }*/

/*        if (!empty($params['status'])) {
            $query->andWhere(['v.status' => $params['status']]);
        }

        if (!empty($params['owner'])) {
            $query->andWhere(['ilike', 'own.fullname', $params['owner']]);
        }*/

        $query->limit(101);

        $sql = $query->createCommand()->getSql();
        return $query->all();
    }

    private function getFioOperators()
    {
        $query = (new Query())
            ->select(['u.id', 'u.fullname'])
            ->from('users u')
            ->innerJoin('specialists s', 'u.id = s.id_user')
            ->innerJoin('auth_assignment a', 'u.id = a.id_user AND s.id = a.id_specialist')
            ->where(['a.item_name' => 'callCenterOperator', 's.expel_date' => null])
            ->orderBy('u.fullname')
            ->distinct();

        $sql = $query->createCommand()->getRawSql();
        return $query->all();
    }

    private function getClinics()
    {
        //(organizations.id_org_type=37 OR organizations.id_org_type=39 OR organizations.id_org_type=40 OR organizations.id_org_type=41 OR organizations.id_org_type=53)
        $query = (new Query())
            ->select(['id', 'short_name'])
            ->from('organizations')
            ->where(['id_org_type' => [37, 39, 40, 41, 53]])
            ->orderBy('short_name');

        $sql = $query->createCommand()->getRawSql();
        return $query->all();
    }

    private function getCallTypeIds()
    {
/*        $query = (new Query())
            ->select(['id', 'cname'])
            ->from('call_classification_first_type')
            ->orderBy('id');*/

        $query = (new Query())
            ->select(['id', 'cname', 'parent_id pid'])
            ->from('call_classification_second_type')
            ->orderBy('id');

/*        $query =(new Query())
            ->from(['q' => $queryFirst->union($querySecond)])
            ->orderBy('id');*/

        $sql = $query->createCommand()->getRawSql();
        return $query->all();
    }

    // endregion

    // endregion
}