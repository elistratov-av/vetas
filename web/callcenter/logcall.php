<?php

use yii\db\Query;
use yii\web\BadRequestHttpException;

require_once 'helpers.php';
include_once 'functions.php';

function getCurrentUserOrgId() {
    return $_COOKIE['organization'];
}

function getCurrentUserId() {
    return getUserId($_COOKIE['login']);
}

function getUserId($login) {
    $query = 'SELECT id FROM users WHERE login=\''.string_formating_for_sql($login).'\' LIMIT 1';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    $id_user = pg_fetch_result($result, 0);
    pg_free_result($result);
    return $id_user;
}

function fetchAll($sql) {
    $result = pg_query($sql) or die('Ошибка запроса: ' . pg_last_error());
    $data = pg_fetch_all($result);
    pg_free_result($result);
    return $data;
}

function getFioOperators() {
    $sql = <<<SQL
select
	distinct "u"."id",
	"u"."fullname"
from
	"users" "u"
inner join "specialists" "s" on
	u.id = s.id_user
inner join "auth_assignment" "a" on
	u.id = a.id_user
	and s.id = a.id_specialist
where
	("a"."item_name" = 'callCenterOperator')
	and ("s"."expel_date" is null)
order by
	"u"."fullname"
SQL;
    return fetchAll($sql);
}

function getClinics() {
    $sql = <<<SQL
SELECT "id", "short_name" as "name" FROM "organizations" WHERE "id_org_type" IN (37, 39, 40, 41, 53) ORDER BY "name"
SQL;
    return fetchAll($sql);
}

function getFirstCallTypes() {
    $sql = <<<SQL
SELECT "id", "cname" FROM "call_classification_first_type" ORDER BY "id"
SQL;
    return fetchAll($sql);
}

function getSecondCallTypes() {
    $sql = <<<SQL
SELECT "id", "cname", "parent_id" AS "pid" FROM "call_classification_second_type" ORDER BY "id"
SQL;
    return fetchAll($sql);
}

function viewLogCall($config) {
    $calls = findCalls([]);
    $operators = getFioOperators();
    $clinics = getClinics();
    $firstCallTypes = getFirstCallTypes();
    $secondCallTypes = getSecondCallTypes();

    echo include_template('logcall_template.php', [
        'orgId' => getCurrentUserOrgId(),
        'operators' => $operators,
        'clinics' => $clinics,
        'firstCallTypes' => $firstCallTypes,
        'secondCallTypes' => $secondCallTypes,
    ]);
}

function bindSearchCalls() {
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

function findCalls($params)
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
        $userId = getCurrentUserId();
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
