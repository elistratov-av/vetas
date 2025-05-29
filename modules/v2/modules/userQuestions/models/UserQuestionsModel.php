<?php

namespace app\modules\v2\modules\userQuestions\models;

use app\models\db\UserQuestions;
use app\models\db\Users;
use app\modules\v2\modules\userQuestions\skeletons\QuestionsList;
use yii\web\BadRequestHttpException;
//use yii\web\ForbiddenHttpException;

class UserQuestionsModel
{
    public $userQuestionsTable;
    private $usersTable;

    /**
     * UserQuestionsModel constructor
     */
    public function __construct()
    {
        $this->userQuestionsTable = UserQuestions::tableName();
        $this->usersTable = Users::tableName();
    }

    /**
     * Возвращает выбранный вопрос
     * 
     * @param int $id
     * @return UserQuestions
     * @throws BadRequestHttpException
     */
    public function get(int $id)
    {
        $UserQuestions = UserQuestions::findOne(['id' => $id]);

        if (empty($UserQuestions)) {
            throw new BadRequestHttpException('Указанный вопрос не найден');
        }

        return $UserQuestions;
    }

    /**
     * Постранично возвращает вопросы по фильтру
     * 
     * @param array $filter = []
     * @param int $page = 1
     * @param int $limit = 10
     * @param string $type = 'all',
     * @param string $attribute_name = 'userQuestions'
     * @return CommonList
     */
    public function list(
        array $filter = [],
        int $page = 1,
        int $limit = 10,
        string $type = 'all',
        string $attribute_name = 'userQuestions'
    )
    {
        $query = UserQuestions::find()
            ->select([
                "{$this->userQuestionsTable}.id",
                "TO_CHAR({$this->userQuestionsTable}.create_date, 'dd.mm.yyyy') AS create_date",
                "TO_CHAR({$this->userQuestionsTable}.create_date, 'dd.mm.yy') AS create_date_short",  
                "{$this->usersTable}.login",
                "{$this->usersTable}.f_fio",
                "{$this->usersTable}.i_fio",
                "{$this->usersTable}.o_fio",
                "{$this->userQuestionsTable}.question",
                "{$this->userQuestionsTable}.answer",
                "{$this->userQuestionsTable}.keywords"
            ])
            ->joinWith('questionUser', false);

        if (isset($filter['status'])) {
            if ($filter['status'] == 'all') {
                $query->andWhere("(({$this->userQuestionsTable}.answer IS NOT NULL AND {$this->userQuestionsTable}.answer_status = 1) OR ({$this->userQuestionsTable}.answer IS NULL AND {$this->userQuestionsTable}.answer_status = 0))");
            } elseif ($filter['status'] == 'has_answer') {
                $query->andWhere(['NOT', ['answer' => null]]);
                $query->andWhere(['answer_status' => 1]);
            } elseif ($filter['status'] == 'wait_answer') {
                $query->andWhere(['answer' => null]);
                $query->andWhere(['answer_status' => 0]);
            }
        }
        if (isset($filter['question_type'])) {
            $query->andWhere(["{$this->userQuestionsTable}.question_type" => $filter['question_type']]);
        }
        if (isset($filter['question_user_id'])) {
            $query->andWhere(["{$this->userQuestionsTable}.question_user_id" => $filter['question_user_id']]);
        }
        if (isset($filter['date_create'])) {
            $query->andWhere(["TO_CHAR({$this->userQuestionsTable}.create_date, 'dd.mm.yyyy')" => $filter['date_create']]);
        }
        if (isset($filter['user_login'])) {
            $query->andWhere(["{$this->usersTable}.login" => $filter['user_login']]);
        }
        if (isset($filter['answer_status'])) {
            $query->andWhere(["{$this->userQuestionsTable}.answer_status" => $filter['answer_status']]);
        }
        if (isset($filter['search_status'])) {
            $query->andWhere(["{$this->userQuestionsTable}.search_status" => $filter['search_status']]);
        }
        if (!empty($filter['keywords']) && is_array($filter['keywords'])) {
            if (isset($filter['keywords_or'])) {
                $where = ['OR'];
            } else {
                $where = ['AND'];
            }
            foreach ($filter['keywords'] as $keyword) {
                $where[] = ['ILIKE', "{$this->userQuestionsTable}.keywords", ';' . $keyword . ';'];
            }
            $query->andFilterWhere($where);
        }

        $queryCount = clone $query;

        $query->orderBY( "{$this->userQuestionsTable}.create_date")->limit($limit)->offset($limit * ($page - 1));
		
        return new QuestionsList(
            $type,
            $attribute_name,
            $query->asArray()->all(),
            $queryCount->count(),
            $page,
            $limit
        );
    }

    /**
     * Создает вопрос
     *
     * @param string $question_type
     * @param string $question
     * @param ?string $answer
     * @param ?array $keywords
     * @param ?string $real_file_name
     * @param ?string $intr_file_name 
     * @return UserQuestions
     * @throws BadRequestHttpException
     */
    public function create(
        string $question_type,
        string $question,
        ?string $answer = null,
        ?array $keywords = null,
        ?string $real_file_name = null,
        ?string $intr_file_name = null)
    {
        $userQuestion = new UserQuestions();

        $id_user = \Yii::$app->user->getId();
        $userQuestion->question_user_id = $id_user;
        $userQuestion->question_type = $question_type;
        $userQuestion->question = $question;
        if (isset($answer)) {
            $userQuestion->answer = $answer;
            $userQuestion->answer_user_id = $id_user;
            $userQuestion->answer_date = date('Y-M-d H:i:s');
            $userQuestion->answer_status = 1;
            $userQuestion->search_status = 1;
        } else {
            $userQuestion->answer_status = 0;
            $userQuestion->search_status = 0;
        }
        if (isset($keywords)) {
            array_map('trim', $keywords);
            $userQuestion->keywords = ';' . implode(';', $keywords) . ';';
        }
        $userQuestion->real_file_name = $real_file_name;
        $userQuestion->intr_file_name = $intr_file_name;

        if (!$userQuestion->save()) {
            $errors = $userQuestion->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при создании справки' : implode("\n", array_values($errors)));
        }

        return $userQuestion;
    }

    /**
     * Редактирует вопрос
     *
     * @param int $id
     * @param ?string $question
     * @param ?string $answer
     * @param ?array $keywords
     * @return UserQuestions
     * @throws BadRequestHttpException
     */
    public function edit(
        int $id,
        ?string $question = null,
        ?string $answer = null,
        ?array $keywords = null,
        ?int $answer_status = null,
        ?int $search_status = null)
    {
        if (!isset($question) && !isset($answer) && !isset($keywords) && !isset($answer_status) && !isset($search_status)) {
            throw new BadRequestHttpException('Не передан ни один из параметров: question, answer, keywords, answer_status, search_status');
        }

        if (isset($answer_status) && $answer_status != 0 && $answer_status != 1) {
            throw new BadRequestHttpException('Статус ответа может иметь значение 0 или 1');
        }

        if (isset($search_status) && $search_status != 0 && $search_status != 1) {
            throw new BadRequestHttpException('Статус использования вопроса для поиска может иметь значение 0 или 1');
        }

        $userQuestion = $this->get($id);

        if (isset($question)) {
            $userQuestion->question = $question;
        }
        if (isset($answer)) {
            if (!isset($userQuestion->answer) || $userQuestion->answer != $answer) {
                $userQuestion->answer = $answer;
                $userQuestion->answer_user_id = \Yii::$app->user->getId();
                $userQuestion->answer_date = date('Y-M-d H:i:s');
            }
        }
        if (isset($answer_status)) {
            $userQuestion->answer_status = $answer_status;
        }
        if (isset($search_status)) {
            $userQuestion->search_status = $search_status;
        }
        if (isset($keywords)) {
            array_map('trim', $keywords);
            $userQuestion->keywords = ';' . implode(';', $keywords) . ';';
        }

        if (!$userQuestion->save()) {
            $errors = $userQuestion->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при редактировании справки' : implode("\n", array_values($errors)));
        }

        return $userQuestion;
    }
}
