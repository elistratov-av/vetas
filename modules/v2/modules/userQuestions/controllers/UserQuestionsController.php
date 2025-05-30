<?php

namespace app\modules\v2\modules\userQuestions\controllers;

use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\userQuestions\models\UserQuestionsModel;
use app\modules\v2\modules\userQuestions\models\QuestionUploadForm;
use yii\web\UploadedFile;
use yii\web\BadRequestHttpException;

class UserQuestionsController extends BaseController
{
    /**
     * Возвращает указанный вопрос
     *
     * @param $id
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionGet($id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new UserQuestionsModel())->get($id)
        ];
    }

    /**
     * Постранично возвращает вопросы по фильтру
     * 
     * @param array $filter = []
     * @param int $page = 1
     * @param int $limit = 10
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionList(
        array $filter = [],
        int $page = 1,
        int $limit = 10,
        string $type = 'all',
        string $attribute_name = 'userQuestions'
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new UserQuestionsModel())->list($filter, $page, $limit, $type, $attribute_name)
        ];
    }

    /**
     * Возвращает отфильтванный список вопросов
     *
     * @param $date_create Дата создания
     * @param $user_login  Автор (выполняется фильтрация по логину пользователя)
     * @param $keywords    Ключевые слова (выполняется фильтрация по заданному ключевому слову)
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionFindsupport(
        ?string $date_create = null,
        ?string $user_login = null,
        ?array $keywords = null,
        int $page = 1,
        int $limit = 10
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $filter = [
            'question_type' => 'support',
            'date_create' => $date_create,
            'user_login' => $user_login,
            'keywords' => $keywords,
            'keywords_or' => 1
        ];

        return [
            'result' => (new UserQuestionsModel())->list($filter, $page, $limit)
        ];
    }

    /**
     * Возвращает вопросы, которые были заданы выбранным пользователем
     *
     * @param $question_user_id
     * @param $status
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionFinduser(
        int $question_user_id,
        string $status,
        int $page = 1,
        int $limit = 10
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $status = strtolower(trim($status));
        if (strlen($status)) {
            if (!($status == 'all' or $status == 'has_answer' or $status == 'wait_answer')) {
                throw new BadRequestHttpException('Неверный параметр status');
            }
        }

        $filter = [
            'question_type' => 'user',
            'question_user_id' => $question_user_id,
            'status' => $status
        ];

        return [
            'result' => (new UserQuestionsModel())->list($filter, $page, $limit, 'basic')
        ];
    }

    /**
     * Поиск по вопросам
     *
     * @param $id_user
     * @param $status
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionFindkeywords(
        array $keywords,
        int $page = 1,
        int $limit = 10
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $filter = [
            'answer_status' => 1,
            'search_status' => 1,
            'keywords' => $keywords,
            'keywords_or' => 1
        ];

        return [
            'result' => (new UserQuestionsModel())->list($filter, $page, $limit, 'answer')
        ];
    }

    /**
     * Создает support вопрос
     *
     * @param string $question
     * @param string $answer
     * @param string $links
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionCreatesupport(
        string $question,
        string $answer,
        array $keywords
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $userQuestion = (new UserQuestionsModel())->create('support', $question, $answer, $keywords);
        return [
            'result' => true,
            'id' => $userQuestion->id
        ];
    }

    /**
     * Создает user вопрос с файлом
     *
     * @param form
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionCreateuser()
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        if (\Yii::$app->request->isPost) {
            $form = new QuestionUploadForm();
            $form->question = $_POST['question'];
            $form->file = UploadedFile::getInstanceByName('file');

            if ($form->validate()) {
                $real_file_name = null;
                $intr_file_name = null;
                if($form->file) {
                    $real_file_name = $form->file->name;
                    $upload_path = \Yii::getAlias('@webroot/upload/questions/');
                    if (!file_exists($upload_path)) { mkdir($upload_path, 0777, true); }
                    $intr_file_name = \Yii::$app->getSecurity()->generateRandomString(32) . '.' . $form->file->getExtension();
                    $uploaded_file = $upload_path . $intr_file_name;
                }

                $userQuestion = (new UserQuestionsModel())->create(
                    'user',
                    $form->question,
                    null,
                    null,
                    $real_file_name,
                    $intr_file_name);
                
                if($form->file) {
                    $form->file->saveAs($uploaded_file);
                }

                return  [
                    'result' => true,
                    'id' => $userQuestion->id
                ];
            } else {
                $errors = $form->getErrorSummary(true);
                throw new BadRequestHttpException(empty($errors) ? 'Ошибка загрузки файла' : implode("\n", array_values($errors)));
            }
        } else {
            return [
                'result' => false,
                'error' => 'POST expected',
            ];
        }
    }

    /**
     * Редактирует вопрос
     *
     * @param int $id id редактируемой записи из user_questions
     * @param ?string $question текст вопроса из формы
     * @param ?string $answer текст ответа из формы
     * @param ?array $keywords ключевые слова из формы
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionEdit(
        int $id,
        ?string $question = null,
        ?string $answer = null,
        ?array $keywords = null,
        ?int $answer_status = null,
        ?int $search_status = null
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        (new UserQuestionsModel())->edit($id, $question, $answer, $keywords, $answer_status, $search_status);

        return [
            'result' => true
        ];
    }
}
