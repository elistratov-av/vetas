<?php

namespace app\modules\v2\modules\visit\controllers;

use app\common\components\pdfGenerator\PdfGenerator;
use app\common\models\VisitStatus;
use app\models\db\Contacts;
use app\models\db\FiasAddresses;
use app\models\db\VisitDescriptions;
use app\models\db\VisitsGovServices;
use app\models\ext\ExtVisitsGovServices;
use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\visit\models\VisitDescriptionsModel;
use app\models\db\Visits;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * Методы для работы с описаниями приема (вкладка "Данные приема")
 *
 * Class DescriptionsController
 * @package app\modules\v2\modules\visit\controllers
 */
class DescriptionsController extends BaseController
{
    use VisitTrait;

    /**
     * Выводит список доступных для приема типов описаний.
     * Доступные типы описаний формируются исходя из набора услуг приема.
     * Для этого используется таблица services_description_types
     *
     * @param int $id_visit
     * @param int|null $id_pet
     *
     * @return array
     *
     * @todo Не помешает требование id_pet, если это множественный приём
     */
    public function actionTypes($id_visit, $id_pet = null)
    {
        $visit = $this->findVisit($id_visit);

        $this->checkAccess($this->action->getUniqueId(), $visit, $this->actionParams);
        if (empty('description_types')) {
            $model = VisitDescriptionsModel::findAvailableGovService($id_visit, $id_pet);
        } else {
            $model = VisitDescriptionsModel::findAvailableDescriptionTypes($id_visit, $id_pet);
        }

        $model[] = [
            'id' => 87,
            'name' => 'Заболевание',
            'sort_by' => 2,
            'tech_name' => 'DISEASE_NAME',
            'required' => false,
        ];

        return [
            'result' => [
                'description_types' => $model,
            ],
        ];
    }

    /**
     * Получение сохраненных ранее описаний приема из таблицы visit_descriptions
     *
     * @param int $id_visit
     * @return array
     */
    public function actionGet($id_visit)
    {
        $visit = $this->findVisit($id_visit);

        $this->checkAccess($this->action->getUniqueId(), $visit, $this->actionParams);

        return [
            'result' => [
                'descriptions' => $this->getVisitDescriptions($visit, true, true),
            ],
        ];
    }

    /**
     * Сохранение описаний приема.
     * Производится перед завершением приема для приемов, взятых в работу.
     *
     * @param int $id_visit
     * @param array $descriptions
     * @return array
     */
    public function actionSave($id_visit, $descriptions)
    {
        $visit = $this->findVisit($id_visit);

        $this->checkAccess($this->action->getUniqueId(), $visit, $this->actionParams);

        $model = new VisitDescriptionsModel(compact('visit', 'descriptions'));

        if (!$model->save()) {
            $this->errorResponse($model, 'Ошибка при сохранении данных приема');
        }

        return [
            'descriptions' => $this->getVisitDescriptions($visit, true, true),
        ];
    }

    /**
     * Формирование pdf файла для печати данных приема
     *
     * @param int $id_visit
     * @param string $format
     * @return array
     */
    public function actionFile($id_visit, $format = PdfGenerator::FORMAT_A4, $noAccessCheck = false)
    {
        $visit = $this->findVisit($id_visit);

        if ($format !== PdfGenerator::FORMAT_A4 && $format !== PdfGenerator::FORMAT_A5) {
            $this->errorResponse($visit, 'Неподдерживаемый формат печати');
        }
        if ($visit->status != VisitStatus::IN_WORK && $visit->status != VisitStatus::FINISHED && $visit->status != VisitStatus::FINISHED_UNPAYED) {
            $this->errorResponse($visit, 'Печать данных приема возможна для приемов в работе и завершенных');
        }

        if (!$noAccessCheck) $this->checkAccess($this->action->getUniqueId(), $visit, $this->actionParams);

        $visitDescriptions = $visit->getVisitDescriptions()->all();

        if (count($visitDescriptions)>0){
            foreach ($visitDescriptions as $item) {
                $descriptions[$item->id_pet][$item->id_description_type] = $item;
            }
        }
        else {
            $descriptions = [];
        }

        $user = \Yii::$app->user->getIdentity();

        $organizations = $user ? $user->specialist->getAllOrganizations() : [];
        $description_types = VisitDescriptionsModel::findAvailableDescriptionTypes($id_visit);
        $source_visit = Visits::find()->where(['source' => $visit->id])->one();
        $pet_services = ExtVisitsGovServices::findPetServisesForVisit($id_visit);
        $contacts = Contacts::find()
            ->leftJoin('contact_types', 'contact_types.id = contacts.id_contact_type')
            ->where(['contacts.entity_id' => $visit['id_organization']])
            ->andWhere(['contact_types.entity_type' => 'organization'])
            ->all();

        $fias_address = [];
        if (!empty($organizations[0]) && !empty($organizations[0]['id_fias_address'])) {
            $fias_address = FiasAddresses::find()->where(['id' => $organizations[0]['id_fias_address']])->one();
        }


        /* @var $generator PdfGenerator */
        $generator = \Yii::$app->get('pdfGenerator');
        try {
            $path = $generator->createVisitDescription(
                compact(
                    'visit',
                    'description_types',
                    'descriptions',
                    'source_visit',
                    'organizations',
                    'contacts',
                    'fias_address',
                    'pet_services'
                ),
                $format
            );
        } catch (\Exception $e) {
            $this->errorResponse($visit, $e->getMessage());
        }

        return [
            'result' => [
                'url' => \Yii::getAlias('@web') . '/upload/pdf/' . $path[1] . '.' . $path[2],
            ],
        ];
    }

    /**
     * @param \app\models\db\Visits $visit
     * @param bool $withPets
     * @param bool $asArray
     *
     * @return array|\app\models\db\Visits[]
     */
    private function getVisitDescriptions(\app\models\db\Visits $visit, bool $withPets = false, bool $asArray = false)
    {
        return $this->prepareDescriptionsQuery($visit->getVisitDescriptions(), $withPets)
            ->asArray($asArray)
            ->all();
    }

    /**
     * Настраивает выбираемые данные
     *
     * @param \yii\db\ActiveQuery $query Объект запроса
     * @param bool $withPets Вложить объекты животных
     *
     * @return \yii\db\ActiveQuery
     */
    private function prepareDescriptionsQuery(\yii\db\ActiveQuery $query, bool $withPets = false)
    {
        $with = [];

        if ($withPets) {
            $with['pets'] = function ($q) {
                return $q
                    ->select([
                        'id',
                        'name',
                        'id_species',
                        'id_breed',
                    ]);
            };
        }

        return $query
            ->with($with)
            ->select([
                'id',
                'id_pet',
                'id_description_type',
                'description',
            ]);
    }
}
