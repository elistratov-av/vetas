<?php

namespace app\modules\v2\modules\changeRequest\models;

use app\models\db\ChangeRequest;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;

/**
 * Created by PhpStorm.
 * User: user
 * Date: 25.07.19
 * Time: 12:18
 */
class ChangeRequestModel
{
    public $author_org;

    /**
     * DiscountModel constructor.
     * @throws ForbiddenHttpException
     * @throws \Throwable
     */
    public function __construct()
    {
        $user = \Yii::$app->user->getIdentity();
        $this->author_org = $user->specialist->id_organization;
        if ($this->author_org == null) {
            throw new ForbiddenHttpException('Пользователь должен состоять в организации');
        }
    }

    /**
     * Создает запрос
     *
     * @param $entity_name
     * @param $type
     * @param $description
     * @return ChangeRequest
     * @throws BadRequestHttpException
     * @throws \Throwable
     */
    public function create($entity_name, $type, $description)
    {
        $request = new ChangeRequest();

        $user = \Yii::$app->user->getIdentity();

        $request->entity_name = $entity_name;
        $request->author = $user->getId();
        $request->author_org = $this->author_org;
        $request->type = $type;
        $request->description = $description;
        $request->state = 'N';

        if (!$request->save()) {
            $errors = $request->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при создании запроса' : implode("\n", array_values($errors)));
        }

        return $request;
    }
}
