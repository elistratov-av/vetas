<?php

namespace app\modules\v2\modules\visit\models;

use app\models\db\VisitPets;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use app\common\models\VisitStatus;
use app\models\db\DescriptionTypes;
use app\models\db\VisitDescriptions;
use app\models\db\VisitsGovServices;

/**
 * Class VisitDescriptionsModel
 * @package app\modules\v2\modules\visit\models
 *
 * @see https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=102769088
 */
class VisitDescriptionsModel extends Model
{
    use VisitTrait;

    const SCENARIO_VALIDATE_WHEN_FINISH_VISIT = 'validate_when_finish_visit';

    /**
     * @var array
     */
    public $descriptions;
    /**
     * @var \app\models\db\Visits
     */
    public $visit;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!isset($this->visit)) {
            throw new InvalidConfigException();
        }
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['visit', 'validateVisit', 'on' => [self::SCENARIO_DEFAULT]],
            [
                'descriptions',
                'validateDescriptions',
                'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_VALIDATE_WHEN_FINISH_VISIT],
                'skipOnError' => true,
                'skipOnEmpty' => !($this->visit->status == VisitStatus::FINISHED || $this->scenario == self::SCENARIO_VALIDATE_WHEN_FINISH_VISIT),
            ],
        ];
    }

    /**
     * @param string $attribute is the name of the attribute to be validated
     * @param array $params contains the value of [[params]] that you specify when declaring the inline validation rule
     * @param \yii\validators\InlineValidator $validator is a reference to related [[InlineValidator]] object (since 2.0.11)
     * @see \yii\validators\InlineValidator
     */
    public function validateVisit($attribute, $params, $validator)
    {
        // проверим статус приема
        if (!in_array($this->visit->status, [VisitStatus::IN_WORK, VisitStatus::FINISHED])) {
            $this->addError($attribute, 'Недопустимый статус приема');
            return;
        }

        // проверим возможность редактирования для завершенного приема
        if ($this->visit->status == VisitStatus::FINISHED && !$this->canEditFinishedVisit()) {
            $this->addError($attribute, 'Редактирование приема возможно в течение 24 часов после завершения');
            return;
        }
    }

    /**
     * @param string $attribute is the name of the attribute to be validated
     * @param array $params contains the value of [[params]] that you specify when declaring the inline validation rule
     * @param \yii\validators\InlineValidator $validator is a reference to related [[InlineValidator]] object (since 2.0.11)
     * @see \yii\validators\InlineValidator
     */
    public function validateDescriptions($attribute, $params, $validator)
    {
        if (!is_array($this->$attribute)) {
            $this->addError($attribute, 'Некорректный параметр "descriptions"');
            return;
        }

        // проверяем, есть ли доступные описания
        $types = self::findAvailableDescriptionTypes($this->visit->id);

        // для всех должно быть доступно описание ЗАБОЛЕВАНИЕ
        $types[] = [
            'id' => 87,
            'name' => 'Заболевание',
            'sort_by' => null,
            'tech_name' => 'DISEASE_NAME',
            'required' => null
        ];

        if (empty($types)) {
            if ($this->scenario == self::SCENARIO_VALIDATE_WHEN_FINISH_VISIT) {
                // проверка при завершении приема
                if (!empty($this->$attribute)) {
                    // для приема вообще не предусмотрены описания, но они заполнены
                    $this->addError($attribute, 'Некорректный тип данных приема');
                }
            } else {
                $this->addError($attribute, 'Отсутствуют доступные типы описаний для приема');
            }

            return;
        }

        // валидация заполненности обязательных описаний будет проводиться при завершении приема и для завершенного приема
        // для приема в работе не будем проверять заполненность всех описаний, чтобы можно было сохранять промежуточные данные,
        // @see \app\modules\v2\modules\visit\models\VisitChangeStatusModel::finishVisit
        if ($this->visit->status == VisitStatus::FINISHED || $this->scenario == self::SCENARIO_VALIDATE_WHEN_FINISH_VISIT) {
            $emptyRequiredDescriptions = self::getEmptyRequiredDescriptions($this->visit->id);
            if (!empty($emptyRequiredDescriptions)) {
                $this->addError($attribute, 'Не заполнены обязательные данные приема: ' . implode(', ', $emptyRequiredDescriptions));
                return;
            }
        }

        // проверяем, соответствуют ли переданные в запросе описания доступным для данного приема
        $availableIds = ArrayHelper::getColumn($types, 'id');

        // делаем описание заболевания доступным для данного приема
        $availableIds[] = 87;

        $passedIds = ArrayHelper::getColumn($this->descriptions, 'id_description_type');
        $unnecessaryDescriptions = array_diff($passedIds, $availableIds);

        if (!empty($unnecessaryDescriptions)) {
            // есть описания, тип которых не предусмотрен для данных услуг
            if ($this->visit->status == VisitStatus::FINISHED
                || $this->scenario == self::SCENARIO_VALIDATE_WHEN_FINISH_VISIT
                || $this->scenario == self::SCENARIO_DEFAULT ) {
                VisitDescriptions::deleteAll([
                    'id_description_type' => $unnecessaryDescriptions,
                    'id_visit' => $this->visit->id
                ]);
            } else {
                $this->addError($attribute, 'Некорректный тип данных приема');
            }
        }
    }

    /**
     * @return bool
     */
    public function save()
    {

        if (!$this->validate()) {
            return false;
        }

        $models = [];
        $to_delete = [];

        $descs = [];
        foreach ($this->descriptions as $data) {
            if (is_array($data['description'])) {
                foreach ($data['description'] as $desc) {
                    $descs[] = [
                        'id_description_type' => $data['id_description_type'],
                        'description' => $desc,
                        'id_pet' => $data['id_pet'] ?? null
                    ];
                }
            }else{
                $descs[] = $data;
            }
        }

        $tests = [];
        foreach ($descs as $desc) {
            if (empty($tests[$desc['id_pet']][$desc['id_description_type']])) {
                $tests[$desc['id_pet']][$desc['id_description_type']] = 1;
            }else{
                $tests[$desc['id_pet']][$desc['id_description_type']]++;
            }
        }

        $this->descriptions = $descs;

        foreach ($this->descriptions as $data) {
            if (isset($data['id'])) {
                $id = ArrayHelper::remove($data, 'id');
                $model = VisitDescriptions::findOne([
                    'id' => $id,
                    'id_visit' => $this->visit->id,
                ]);
            } else {
                // проверяем на случай, если это редактирование, но с фронта не передан id
                // Nikita, 13:07
                // "Сейчас можно создать еще описания, если не передавать id уже имеющегося описания,
                // то есть обычный запрос на создание новых"
                $where = [
                    'id_visit' => $this->visit->id,
                    'id_description_type' => $data['id_description_type'],
                ];
                if (!empty($data['id_pet'])) {
                    $where['id_pet'] = $data['id_pet'];
                }

                $model = VisitDescriptions::findAll($where);

                if (count($model) > 1 || !empty($tests[$data['id_pet']][$data['id_description_type']]) && $tests[$data['id_pet']][$data['id_description_type']]  > 1) {
                    VisitDescriptions::deleteAll($where);
                }

                $model = VisitDescriptions::findOne($where);
            }
            if ($model === null) {
                $model = new VisitDescriptions(['id_visit' => $this->visit->id]);
            } else {
                $model->setScenario($this->visit->isFinished() ? VisitDescriptions::SCENARIO_UPDATE_WHEN_VISIT_FINISHED : VisitDescriptions::SCENARIO_UPDATE);
            }
            $model->load($data, '');
            if (!$model->validate()) {
                $this->addError('descriptions', implode("\n", array_values($model->getErrorSummary(true))));

                return false;
            }

            if (empty($model->description) && $model->scenario != VisitDescriptions::SCENARIO_UPDATE_WHEN_VISIT_FINISHED) {
                // не сохраняем пустые описания для незавершенных приемов
                // Павел, 12:18
                // Отсутствует возможность удаления, очистки описания после того, как его заполнили...
                // ...А если описание не передавать и оно уже создано, то оно не изменится.
                // Mariah.Key, 16:15
                // Закладывайте удаление
                if (!empty($model->id)) {
                    $to_delete[] = $model->id;
                }

                continue;
            }

            $models[] = $model;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($models as $model) {
                /* @var $model \app\models\db\VisitDescriptions */
                $result = $model->save(false);
                if ($result === false) {
                    $this->addError('descriptions', empty($model->errors) ? 'Ошибка при сохранении' : implode("\n", array_values($model->getErrorSummary(true))));
                    $transaction->rollBack();

                    return false;
                }
            }

            // удаляем пустые описания для незавершенных приемов
            if ($this->visit->isFinished() === false && !empty($to_delete)) {
                Yii::$app->db
                    ->createCommand()
                    ->delete(
                        VisitDescriptions::tableName(),
                        ['id' => $to_delete]
                    )
                    ->execute();
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        return true;
    }

    /**
     * @param int $id_visit
     * @param int|null $id_pet
     *
     * @return array
     */
    public static function findAvailableDescriptionTypes($id_visit, $id_pet = null)
    {
        $q = (new Query())
            ->from(['vgs' => VisitsGovServices::tableName()])
            ->select([
                'dt.id',
                'dt.name',
                'dt.sort_by',
                'dt.tech_name',
                new Expression('BOOL_OR(sdt.required) as required'),
            ])
            ->innerJoin(
                'services_description_types sdt',
                'sdt.id_service = vgs.id_service'
            )
            ->innerJoin(
                DescriptionTypes::tableName() . ' dt',
                'dt.id = sdt.id_description_type'
            )
            ->innerJoin(
                VisitPets::tableName() . ' vp',
                'vp.id_visit = vgs.id_visit'
            )
            ->where(['vgs.id_visit' => $id_visit])
            ->groupBy('dt.id')
            ->orderBy(['dt.sort_by' => SORT_ASC]);
        if (null !== $id_pet) {
            //  $q->andWhere(['vgs.id_pet' => $id_pet]);
            $q->andWhere(['vp.id_pet' => $id_pet])
                ->andWhere(['vgs.id_pet' => $id_pet])//                ->orWhere(['dt.id_description_type' => 87])
            ;
        }

        return $q->all();
    }

    /**
     * @param $id_visit
     * @return array
     */
    public static function getEmptyRequiredDescriptions($id_visit)
    {
        return (new Query())
            ->select('dt.name')
            ->from('visits_gov_services vgs')
            ->innerJoin(
                'services_description_types sdt',
                'sdt.id_service = vgs.id_service'
            )
            ->innerJoin(
                DescriptionTypes::tableName() . ' dt',
                'dt.id = sdt.id_description_type'
            )
            ->leftJoin(
                VisitDescriptions::tableName() . ' vd',
                'vd.id_visit = vgs.id_visit AND vd.id_description_type = dt.id'
            )
            ->where(['vgs.id_visit' => $id_visit])
            ->andWhere(['sdt.required' => true])
            ->andWhere(['vd.id' => null])
            ->groupBy('dt.id')
            ->column();
    }

    /**
     * Удаляет из БД несоответсвующие списку услуг описания
     * @param $id_visit
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public static function deleteExcessVisitDescriptionsFromDB($id_visit)
    {
        $available = self::findAvailableDescriptionTypes($id_visit);
        $ids = ArrayHelper::getColumn($available, 'id');

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!empty($ids)) {
                Yii::$app
                    ->db
                    ->createCommand()
                    ->delete(
                        VisitDescriptions::tableName(),
                        [
                            'AND',
                            ['id_visit' => $id_visit],
                            ['NOT IN', 'id_description_type', $ids],
                        ]
                    )
                    ->execute();
            } else {
                Yii::$app
                    ->db
                    ->createCommand()
                    ->delete(
                        VisitDescriptions::tableName(),
                        ['id_visit' => $id_visit]
                    )
                    ->execute();
            }
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
        return true;
    }

}
