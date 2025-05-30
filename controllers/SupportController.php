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
        $answer['answer_user_id'] = $this->getUserId($_COOKIE['login']);
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

    // region Helpers

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

    function getUserId($login)
    {
        $query = (new Query())
            ->select(['id'])
            ->from('users')
            ->where(['login' => $login]);
        $u = $query->one();
        if ($u !== false) {
            return $u['id'];
        }
        return null;
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
}