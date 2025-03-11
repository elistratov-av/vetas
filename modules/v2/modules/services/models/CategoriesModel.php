<?php


namespace app\modules\v2\modules\services\models;


use app\models\db\GovServices;
use app\models\db\tmc\CategoryToGovServices;
use yii\db\ActiveQuery;
use yii\db\Exception;
use yii\web\BadRequestHttpException;

class CategoriesModel
{
    /**
     * @param $id_service
     * @return mixed|null
     * @throws BadRequestHttpException
     */
    public function get($id_service)
    {
        $gov_service = GovServices::find()
            ->where(['id' => $id_service])
            ->with([
                'categories' => function ($query) {
                    /** @var ActiveQuery $query */
                    $query->select([
                        'id',
                        'name',
                        'description'
                    ]);
                },
            ])
            ->one();

        if (empty($gov_service)) {
            throw new BadRequestHttpException('Услуга не найдена');
        }

        return empty($gov_service->categories) ? [] : $gov_service->categories;
    }

    /**
     *
     * @param $id_service
     * @param $category_ids
     * @throws BadRequestHttpException
     * @throws Exception
     */
    public function save($id_service, $category_ids)
    {
        $gov_service = GovServices::findOne(['id' => $id_service]);

        if (empty($gov_service)) {
            throw new BadRequestHttpException('Услуга не найдена');
        }

        $transaction = GovServices::getDb()->beginTransaction();

        $gov_service->unlinkAll('categories', true);

        if (empty($category_ids)) {
            $transaction->commit();
            return; // это была очистка
        }

        foreach ($category_ids as $category_id) {
            $link = new CategoryToGovServices([
                'id_service' => $id_service,
                'id_category' => $category_id
            ]);

            if (!$link->save()) {
                $transaction->rollBack();
                $errors = $link->getErrorSummary(true);
                throw new BadRequestHttpException(empty($errors) ? 'Ошибка при сохранении категории' : implode("\n", array_values($errors)));
            }
        }

        $transaction->commit();
    }
}