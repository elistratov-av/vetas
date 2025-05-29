<?php

namespace app\controllers;

use DateTime;
use Yii;
use yii\db\Expression;
use yii\db\Query;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class SupportController extends AppController
{
    public $layout = 'support';

    // Question

    public function actionQuestion()
    {
        return $this->render('question');
    }

    public function actionSearchQuestions(string $date1, string $date2, string $status, string $login, string $keyword, bool $use_search = false)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $data = $this->findQuestions([
            'date1' => !empty($date1) ? new DateTime($date1) : null,
            'date2' => !empty($date2) ? new DateTime($date2) : null,
            'status' => $status,
            'login' => $login,
            'keyword' => $keyword,
            'use_search' => $use_search]);
        return $data;
    }

    public function actionAnswer(int $id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $data = $this->getQuestionAnswer($id);
        return $data;
    }

    public function actionSaveAnswer()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $answer = $this->bindAnswer();
        $answer['answer_user_id'] = $this->getCurrentUserId();
        $this->updateQuestionAnswer($answer);

        return true;
    }

    public function actionDownloadFile(int $id)
    {
        Yii::$app->response->format = Response::FORMAT_RAW;

        [$filePath, $originalName] = $this->getQuestionFileInfo($id);
        $options = [];
        $stat = stat($filePath);
        if ($stat !== false) {
            $options['fileSize'] = $stat->size;
        }
        if (function_exists('mime_content_type')) {
            $mimeType = mime_content_type($filePath);
            if (!empty($mimeType)) {
                $options['mimeType'] = $mimeType;
            }
        }
        return Yii::$app->response->sendFile($filePath, $originalName, $options);
    }

    // Visit

    public function actionAutocompletePetOwner(string $term = '')
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->autocompletePetOwner($term);
    }

    public function actionAutocompleteSpecialist(string $term = '')
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->autocompleteSpecialist($term);
    }

    public function actionAutocompleteOrganization(string $term = '')
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->autocompleteOrganization($term);
    }

    public function actionVisit()
    {
        return $this->render('visit');
    }

    public function actionSearchVisits()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $params = $this->bindSearchVisits();
        return $this->findVisitsDetail($params);
    }

    public function actionCloseVisit(int $id)
    {
        $this->closeVisit($id);
    }

    public function actionWorkVisit(int $id)
    {
        $this->workVisit($id);
    }

    public function actionCancelVisit(int $id)
    {
        $this->cancelVisit($id);
    }

    public function actionCancelVisitPaid(int $id)
    {
        $this->cancelVisitPaid($id);
    }

    public function actionCancelVisitList()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $params = $this->bindCancelVisitList();
        $failed = $this->cancelVisitList($params);
        return $failed ?? [];
    }

    // region Helpers

    // region Question

    private function getQuestionFileStream($id)
    {
        $query = (new Query())
            ->select(['file'])
            ->from('user_questions')
            ->where(['id' => $id]);
        $row = $query->one();
        if ($row === false)
            throw new NotFoundHttpException('Файл не найден');

        return $row['file'];
    }

    private function getQuestionFileInfo($id)
    {
        $query = (new Query())
            ->select(['real_file_name', 'intr_file_name'])
            ->from('user_questions')
            ->where(['id' => $id]);
        $row = $query->one();
        if ($row === false) {
            throw new NotFoundHttpException('Файл #' . $id . ' не найден');
        }

        $filePath = Yii::getAlias('@webroot/upload/questions/' . $row['intr_file_name']);
        if (!file_exists($filePath)) {
            throw new NotFoundHttpException('Файл#' . $id . ' не найден');
        }
        return [
            $filePath,
            $row['real_file_name'] ?? $row['intr_file_name'],
        ];
    }

    private function bindAnswer()
    {
        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
        if ($id === false) {
            throw new BadRequestHttpException('Отсутствует обязательное поле id.');
        }
        $answer = $_POST['answer'];
        $keywords = $_POST['keywords'];
        $give_answer = isset($_POST['give_answer']) ? filter_var($_POST['give_answer'], FILTER_VALIDATE_BOOL) : false;
        $use_search = isset($_POST['use_search']) ? filter_var($_POST['use_search'], FILTER_VALIDATE_BOOL) : false;
        return [
            'id' => $id,
            'answer' => $answer,
            'keywords' => $keywords,
            'give_answer' => $give_answer,
            'use_search' => $use_search,
        ];
    }

    private const ANSWER_GIVE = 'G';
    private const ANSWER_WAIT = 'W';

    private const KEYWORDS_SEP = ';';

    private function findQuestions($options)
    {
        $query = (new Query())
            ->select(['q.id', 'TO_CHAR(create_date, \'DD.MM.YYYY\') create_date', '(intr_file_name IS NOT NULL) has_file', 'question', 'answer_status', 'answer', 'search_status', 'keywords', 'fullname', 'login'])
            ->from('user_questions as q')
            ->where(['question_type' => 'user'])
            ->leftJoin('users', 'question_user_id = users.id')
            ->orderBy('create_date DESC');
        if (!empty($options['date1']) || !empty($options['date2'])) {
            if (!empty($options['date1'])) {
                $query->andWhere(['>=', 'DATE_TRUNC(\'day\', create_date)', $options['date1']->format('Y-m-d')]);
            }
            if (!empty($options['date2'])) {
                $query->andWhere(['<=', 'DATE_TRUNC(\'day\', create_date)', $options['date2']->format('Y-m-d')]);
            }
        }
        if (!empty($options['status'])) {
            if ($options['status'] == self::ANSWER_GIVE) {
                $query->andWhere(['answer_status' => 1]);
            } else if ($options['status'] == self::ANSWER_WAIT) {
                $query->andWhere(['answer_status' => 0]);
            }
        }
        if (!empty($options['login'])) {
            $query->andWhere(['ilike', 'login', $options['login']]);
        }
        if (!empty($options['keyword'])) {
            $keywords = $this->explodeKeywords($options['keyword']);
            if (!empty($keywords)) {
                $query->andWhere(['or ilike', 'keywords', $keywords]);
            }
        }
        if (isset($options['use_search'])) {
            if ($options['use_search']) {
                $query->andWhere(['search_status' => 1]);
            }
        }
        //$sql = $query->createCommand()->getSql();
        return $query->all();
    }

    private function getQuestionAnswer($id)
    {
        $query = (new Query())
            ->select(['id', 'answer', 'keywords', 'answer_status', 'search_status'])
            ->from('user_questions')
            ->where(['id' => $id]);
        return $query->one();
    }

    private function updateQuestionAnswer($answer)
    {
        Yii::$app->db->createCommand()
            ->update('user_questions', [
                'answer' => $answer['answer'],
                'answer_user_id' => $answer['answer_user_id'],
                'keywords' => $this->normalizeKeywords($answer['keywords']),
                'answer_date' => new Expression('NOW()::timestamp(0)'),
                'answer_status' => $answer['give_answer'],
                'search_status' => $answer['use_search'],
            ], ['id' => $answer['id']])->execute();
    }

    private function explodeKeywords($keywords)
    {
        if (empty($keywords)) {
            return [];
        }

        $keys = [];
        foreach (explode(self::KEYWORDS_SEP, $keywords) as $keyword) {
            $k = trim($keyword);
            if (!empty($k)) {
                $keys[] = self::KEYWORDS_SEP . $k . self::KEYWORDS_SEP;
            }
        }

        return $keys;
    }

    private function normalizeKeywords($keywords)
    {
        if (empty($keywords)) {
            return $keywords;
        }

        $keys = [];
        foreach (explode(self::KEYWORDS_SEP, $keywords) as $keyword) {
            $k = trim($keyword);
            if (!empty($k)) {
                $keys[] = $k;
            }
        }

        if (empty($keys)) {
            return '';
        }

        $keywords = implode(self::KEYWORDS_SEP, $keys);
        if (empty($keywords)) {
            return '';
        }
        return self::KEYWORDS_SEP . $keywords . self::KEYWORDS_SEP;
    }

    // endregion

    // region Visit

    private function autocompletePetOwner(string $term)
    {
        if (empty($term)) {
            return [];
        }

        $query = (new Query())
            ->select(['fullname'])
            ->from('pet_owners')
            ->where(['ilike', 'fullname', $term . '%', false])
            ->orderBy('fullname')
            ->limit(100);

        $sql = $query->createCommand()->getRawSql();
        return $query->column();
    }

    private function autocompleteSpecialist(string $term)
    {
        if (empty($term)) {
            return [];
        }

        $query = (new Query())
            ->select(['fullname'])
            ->from('specialists as s')
            ->leftJoin('users u', 's.id_user = u.id')
            ->where(['ilike', 'fullname', $term . '%', false])
            ->orderBy('fullname')
            ->limit(100);

        //$sql = $query->createCommand()->getRawSql();
        return $query->column();
    }

    private function autocompleteOrganization(string $term)
    {
        if (empty($term)) {
            return [];
        }

        $query = (new Query())
            ->select(['short_name'])
            ->from('organizations')
            ->where(['ilike', 'short_name', $term . '%', false])
            ->orderBy('short_name')
            ->limit(100);

        //$sql = $query->createCommand()->getRawSql();
        return $query->column();
    }

    private function bindSearchVisits()
    {
        $options = [];
        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
        if ($id !== false) {
            $options['id'] = $id;
        }
        if (!empty($_GET['creation_from'])) {
            $options['creation_from'] = new DateTime($_GET['creation_from']);
        }
        if (!empty($_GET['creation_to'])) {
            $options['creation_to'] = new DateTime($_GET['creation_to']);
        }
        if (!empty($_GET['visit_from'])) {
            $options['visit_from'] = new DateTime($_GET['visit_from']);
        }
        if (!empty($_GET['visit_to'])) {
            $options['visit_to'] = new DateTime($_GET['visit_to']);
        }
        if (!empty($_GET['status'])) {
            $options['status'] = $_GET['status'];
        }
        if (!empty($_GET['channel'])) {
            $options['channel'] = $_GET['channel'];
        }
        if (!empty($_GET['owner'])) {
            $options['owner'] = $_GET['owner'];
        }
        if (!empty($_GET['specialist'])) {
            $options['specialist'] = $_GET['specialist'];
        }
        if (!empty($_GET['organization'])) {
            $options['organization'] = $_GET['organization'];
        }

        return $options;
    }

    private function findVisitsDetail($params)
    {
        $results = [];
        if (empty($params)) {
            return $results;
        }

        foreach ($this->findVisits($params) as $v) {
            $obj = [
                'id' => intval($v['id']),
                'status' => $v['status'],
                'channel' => $v['channel'],
                'paid' => $v['paid'],
                'org_name' => $v['org_name'],
                'org_address' => $v['org_address'],
                'specialist' => $v['specialist'],
                'owner_name' => $v['owner_name'],
                'start_date' => $v['start_date'],
                'start_time' => $v['start_time'],
                'end_time' => $v['end_time'],
            ];

            $services = $this->getServices($obj['id']);
            $obj['services'] = array_map(function($s) {
                return $s['name'];
            }, $services);

            $ids = explode(',', $v['contacts']);
            if (!empty($ids)) {
                $ids = array_map('intval', $ids);
                $obj['contacts'] = $this->getContacts(array_unique($ids));
            }

            $ids = explode(',', $v['pets']);
            if (!empty($ids)) {
                $ids = array_map('intval', $ids);
                $obj['pets'] = $this->getPets(array_unique($ids));
            }

            $results[] = $obj;
        }

        return $results;
    }

    private function findVisits($params)
    {
        $query = (new Query())
            ->select(['vsp.id_specialist AS specialist_id', 'u.fullname AS specialist', 'v.id', 'v.is_paid AS paid', 'v.status', 'v.channel',
                'v.time_range', 'v.id_owner', 'v.id_pet', 'o.short_name AS org_name', 'a.name AS org_address',
                'v.ticket_number', 'own.fullname AS owner_name',
                '(LOWER(v.time_range)::time) AS start_time', '(UPPER(v.time_range)::time) AS end_time',
                '(LOWER(v.time_range)::date) AS start_date',
                'string_agg(c.id::character varying, \',\') AS contacts',
                'string_agg(pets.id::character varying, \',\') AS pets'])
            ->from('visits as v')
            ->leftJoin('pet_owners own', 'v.id_owner = own.id')
            ->leftJoin('contacts c', 'own.id = c.entity_id')
            ->leftJoin('organizations o', 'v.id_organization = o.id')
            ->leftJoin('visits_specialists vsp', 'v.id = vsp.id_visit')
            ->leftJoin('specialists sp', 'vsp.id_specialist = sp.id')
            ->leftJoin('users u', 'sp.id_user = u.id')
            ->leftJoin('addresses a', 'o.id_address = a.id')
            ->leftJoin('visit_pets vpet', 'v.id = vpet.id_visit')
            ->leftJoin('pets', 'vpet.id_pet = pets.id')
            ->orderBy('v.time_range');

        //$params['id'], $params['date'], $params['status'], $params['channel']
        if (!empty($params['id'])) {
            $query->andWhere(['v.id' => $params['id']]);
        }

        if (!empty($params['creation_from']) || !empty($params['creation_to'])) {
            if (!empty($params['creation_from'])) {
                $query->andWhere(['>=', '(v.created_at::DATE)', $params['creation_from']->format('Y-m-d')]);
            }
            if (!empty($params['creation_to'])) {
                $query->andWhere(['<=', '(v.created_at::DATE)', $params['creation_to']->format('Y-m-d')]);
            }
        }

        if (!empty($params['visit_from']) || !empty($params['visit_to'])) {
            if (!empty($params['visit_from'])) {
                $from = $params['visit_from']->format('Y-m-d H:i');
                $query->andWhere("LOWER(v.time_range) >= TO_TIMESTAMP('$from', 'YYYY-MM-DD HH24:MI')");
            }
            if (!empty($params['visit_to'])) {
                $to = $params['visit_to']->format('Y-m-d H:i');
                $query->andWhere("UPPER(v.time_range) <= TO_TIMESTAMP('$to', 'YYYY-MM-DD HH24:MI')");
            }
        }

        if (!empty($params['status'])) {
            $query->andWhere(['v.status' => $params['status']]);
        }

        if (!empty($params['channel'])) {
            $query->andWhere(['v.channel' => $params['channel']]);
        }

        if (!empty($params['owner'])) {
            $query->andWhere(['ilike', 'own.fullname', $params['owner']]);
        }

        if (!empty($params['specialist'])) {
            $query->andWhere(['ilike', 'u.fullname', $params['specialist']]);
        }

        if (!empty($params['organization'])) {
            $query->andWhere(['ilike', 'o.short_name', $params['organization']]);
        }

        $query->groupBy(['own.id', 'vsp.id_specialist', 'u.id', 'v.id', 'o.short_name', 'a.name']);
        $query->limit(100);

        //$sql = $query->createCommand()->getSql();
        return $query->all();
    }

    private function getServices($visitId)
    {
        $query = (new Query())
            ->select(['s.id', 's.name'])
            ->from('visits_gov_services as vs')
            ->leftJoin('gov_services s', 'vs.id_service = s.id')
            ->where(['vs.id_visit' => $visitId]);

        //$sql = $query->createCommand()->getSql();
        return $query->all();
    }

    private function getContacts($contacts)
    {
        if (empty($contacts)) {
            return [];
        }

        $query = (new Query())
            ->select(['c.name','c.main_flag AS main','c.confirmed','t.name AS type_title', 'c.id_contact_type AS type_id'])
            ->from('contacts as c')
            ->leftJoin('contact_types t', 'c.id_contact_type = t.id')
            ->where(['c.id' => $contacts]);

        //$sql = $query->createCommand()->getSql();
        return $query->all();
    }

    private function getPets($pets)
    {
        if (empty($pets)) {
            return [];
        }

        $query = (new Query())
            ->select(['p.id', 'p.name', 'p.sex', 'b.name AS breed', 's.name AS species'])
            ->from('pets as p')
            ->leftJoin('breeds b', 'p.id_breed = b.id')
            ->leftJoin('species s', 'p.id_species = s.id')
            ->where(['p.id' => $pets])
            ->andWhere(['or', 'p.is_main = true', 'p.is_main IS NULL']);

        //$sql = $query->createCommand()->getSql();
        return $query->all();
    }

    private function closeVisit($id)
    {
        $this->updateVisitStatus($id, 'F');
    }

    private function workVisit($id) {
        $this->updateVisitStatus($id, 'W');
    }

    private function cancelVisit($id) {
        $this->updateVisitStatus($id, 'A');
    }

    private function cancelVisitPaid($id) {
        $userId = $this->getCurrentUserId();
        $cmd = Yii::$app->db->createCommand()->update('visits', [
            'is_paid' => false,
            'updated_by' => $userId,
            'updated_at' => new Expression('NOW()::timestamp(0)'),
        ], ['id' => $id]);
        //$sql = $cmd->getSql();
        $cmd->execute();

        $cmd = Yii::$app->db->createCommand()->delete('visit_price', ['id_visit' => $id]);
        //$sql = $cmd->getSql();
        $cmd->execute();
    }

    private function updateVisitStatus($id, $status)
    {
        $userId = $this->getCurrentUserId();
        // UPDATE (table name, column values, condition)
        $cmd = Yii::$app->db->createCommand()->update('visits', [
            'status' => $status,
            'updated_by' => $userId,
            'updated_at' => new Expression('NOW()::timestamp(0)'),
        ], ['id' => $id]);
        //$sql = $cmd->getSql();
        $cmd->execute();
    }

    private function bindCancelVisitList()
    {
        $options = [];
        if (!empty($_POST['ids'])) {
            $ids = $_POST['ids'];
            $options['ids'] = explode(',', $ids);
        }
        if (!empty($_POST['reason'])) {
            $options['reason'] = $_POST['reason'];
        }

        return $options;
    }

    private function cancelVisitList($params)
    {
        ['ids' => $ids, 'reason' => $reason] = $params;
        if (empty($ids)) {
            return;
        }

        $failed = $this->getFailedCancelVisitList($ids);
        if ($failed) {
            return [
                'failed' => $failed,
            ];
        }

        $userId = $this->getCurrentUserId();
        $cmd = Yii::$app->db->createCommand()->update('visits', [
            'status' => 'A',
            'change_reason' => $reason,
            'updated_by' => $userId,
            'updated_at' => new Expression('NOW()::timestamp(0)'),
        ], ['id' => $ids]);
        //$sql = $cmd->getSql();
        $cmd->execute();
    }

    private function getFailedCancelVisitList($ids)
    {
        $query = (new Query())
            ->select(['v.id', 'v.status'])
            ->from('visits as v')
            ->where([
                'v.id' => $ids,
                'v.status' => ['A', 'F'],
            ]);
        //$sql = $query->createCommand()->getSql();
        return $query->all();
    }

    // endregion

    // endregion
}