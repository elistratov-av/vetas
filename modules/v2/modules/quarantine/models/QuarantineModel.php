<?php

namespace app\modules\v2\modules\quarantine\models;

use app\models\db\Diseases;
use app\models\db\Quarantine;
use app\models\db\QuarantineFocus;
use app\models\db\QuarantineLocality;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Class QuarantineModel
 * @package app\modules\v2\modules\quarantine\models
 */
class QuarantineModel extends Model
{
    const SCENARIO_ADD_TERRITORY = 'add_territory';
    const SCENARIO_EDIT_TERRITORY = 'edit_territory';

    /**
     * @var int
     */
    public $id_disease;
    /**
     * @var string
     */
    public $threatened_area;
    /**
     * @var string
     */
    public $start_date;
    /**
     * @var string
     */
    public $end_date;
    /**
     * @var string
     */
    public $comments;
    /**
     * @var array
     */
    public $focuses;
    /**
     * @var array
     */
    public $locality;

    /**
     * @var \app\models\db\Quarantine
     */
    private $quarantine;
    /**
     * @var \app\models\db\QuarantineLocality
     */
    private $quarantineLocality;
    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['id_disease', 'threatened_area', 'start_date'], 'required', 'on' => [self::SCENARIO_DEFAULT]],
            ['id_disease', 'integer', 'on' => [self::SCENARIO_DEFAULT]],
            [
                'id_disease',
                'exist',
                'targetClass' => Diseases::class,
                'targetAttribute' => 'id',
                'message' => 'Указанная болезнь не найдена',
                'on' => [self::SCENARIO_DEFAULT],
            ],
            [['threatened_area', 'comments'], 'string', 'on' => [self::SCENARIO_DEFAULT]],
            [['start_date', 'end_date'], 'date', 'format' => 'php:Y-m-d', 'on' => [self::SCENARIO_DEFAULT]],
            ['end_date', 'validateEndDate', 'skipOnEmpty' => true, 'message' => 'Некорректная плановая дата окончания карантина', 'on' => [self::SCENARIO_DEFAULT]],
            ['focuses', 'safe', 'on' => [self::SCENARIO_ADD_TERRITORY, self::SCENARIO_EDIT_TERRITORY]],
            ['locality', 'safe', 'on' => [self::SCENARIO_ADD_TERRITORY, self::SCENARIO_EDIT_TERRITORY]],
        ];
    }

    /**
     * @param int $id
     * @return \app\models\db\Quarantine|false
     */
    public function find($id)
    {
        $this->quarantine = Quarantine::find()
            ->with('disease')
            ->where(['id' => $id])
            ->one();

        return $this->quarantine;
    }

    /**
     * @return bool
     */
    public function create()
    {
        if (!$this->validate()) {
            return false;
        }

        $model = new Quarantine();
        $model->load($this->toArray(['id_disease', 'threatened_area', 'start_date', 'end_date', 'comments']), '');

        if ($model->save()) {
            $this->quarantine = $model;

            return true;
        }

        $this->addErrors($model->getErrors());

        return false;
    }

    /**
     * @return bool
     */
    public function update()
    {
        if (!empty($this->quarantine->fact_end_date)) {
            $this->addError('fact_end_date', 'Редактирование завершенного карантина не допускается');

            return false;
        }

        if (!$this->validate()) {
            return false;
        }

        $this->quarantine->load($this->toArray(['id_disease', 'threatened_area', 'start_date', 'end_date', 'comments']), '');

        if ($this->quarantine->save()) {
            return true;
        }

        $this->addErrors($this->quarantine->getErrors());

        return false;
    }
    public function createDescriptionLocality()
    {
        if (!$this->validate()) {
            return false;
        }

        $locality_model = new QuarantineLocality();
        $locality_model->load(Yii::$app->request->post('description_locality'));
        if ($locality_model->save()) {
            $this->quarantineLocality = $locality_model;

            return true;
        }

        $this->addErrors($locality_model->getErrors());

        return false;
    }

    public function updateDescriptionLocality()
    {
        if (!$this->validate()) {
            return false;
        }
        
        $this->quarantineLocality->load(Yii::$app->request->post('description_locality'));

        if ($this->quarantineLocality->save()) {
            return true;
        }

        $this->addErrors($this->quarantineLocality->getErrors());

        return false;
    }
    /**
     * @return bool
     */
    public function createTerritory()
    {
        if (!empty($this->quarantine->fact_end_date)) {
            $this->addError('fact_end_date', 'Редактирование завершенного карантина не допускается');

            return false;
        }

        if (!$this->validate()) {
            return false;
        }

        $transaction = \Yii::$app->db->beginTransaction();

        try {
            $id_locality = null;
            if (!empty($this->locality) && is_array($this->locality)) {
                $localityData = $this->locality;
                $geometry = ArrayHelper::remove($localityData, 'geometry');
                $localityModel = new QuarantineLocality([
                    'id_quarantine' => $this->quarantine->id,
                ]);
                $localityModel->load($localityData, '');
                $localityModel->coords = $geometry;
                if (!$localityModel->save()) {
                    $this->addErrors($localityModel->getErrors());
                    $transaction->rollBack();

                    return false;
                }
                $id_locality = $localityModel->id;
            }
            if (!empty($this->focuses) && is_array($this->focuses)) {
                foreach ($this->focuses as $focusData) {
                    $geometry = ArrayHelper::remove($focusData, 'geometry');
                    $focusModel = new QuarantineFocus([
                        'id_quarantine' => $this->quarantine->id,
                        'id_locality' => $id_locality,
                    ]);
                    $focusModel->load($focusData, '');
                    $focusModel->coords = $geometry;
                    if (!$focusModel->save()) {
                        $this->addErrors($focusModel->getErrors());
                        $transaction->rollBack();

                        return false;
                    }
                }
            }
            $transaction->commit();

            return true;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            if ($e instanceof \yii\db\Exception) {
                $message = $e->getMessage();
                if (strpos($message, 'geometry') !== false) {
                    \Yii::error($message);
                    $attribute = (strpos($message, 'quarantines_localities') !== false) ? 'locality' : 'focuses';
                    $this->addError(
                        $attribute,
                        'Переданы некорректные координаты ' . ($attribute == 'locality' ? 'неблагополучного пункта' : 'эпизоотического очага'));

                    return false;
                }
            }
            throw $e;
        }
    }

    /**
     * @return bool
     */
    public function updateTerritory()
    {
        if (!empty($this->quarantine->fact_end_date)) {
            $this->addError('fact_end_date', 'Редактирование завершенного карантина не допускается');
            return false;
        }

        if (!$this->validate()) {
            return false;
        }

        $transaction = \Yii::$app->db->beginTransaction();

        try {
            $id_locality = null;
            if (!empty($this->locality) && is_array($this->locality)) {
                $localityData = $this->locality;
                $id_locality = ArrayHelper::remove($localityData, 'id');
                $geometry = ArrayHelper::remove($localityData, 'geometry');
                if (!empty($id_locality)) {
                    $localityModel = QuarantineLocality::findOne(['id' => $id_locality]);
                    if ($localityModel === null) {
                        $this->addError('focuses', 'Не найден неблагополучный пункт с ID ' . $id_locality);
                        $transaction->rollBack();

                        return false;
                    }
                } else {
                    $localityModel = new QuarantineLocality([
                        'id_quarantine' => $this->quarantine->id,
                    ]);
                }
                $localityModel->load($localityData, '');
                $localityModel->coords = $geometry;
                if (!$localityModel->save()) {
                    $this->addErrors($localityModel->getErrors());
                    $transaction->rollBack();

                    return false;
                }
                $id_locality = $localityModel->id;
            }

            // удалим очаги, если на фронте при редактировании был очищен список
            $removeCondition = [
                'id_quarantine' => $this->quarantine->id,
                'id_locality' => $id_locality,
            ];
            if (!empty($this->focuses) && is_array($this->focuses)) {
                $ids = ArrayHelper::getColumn($this->focuses, 'id');
                $ids = array_filter($ids);
                if (!empty($ids)) {
                    $removeCondition = [
                        'and',
                        ['id_quarantine' => $this->quarantine->id],
                        ['id_locality' => $id_locality],
                        ['not in', 'id', $ids]
                    ];
                }
            }
            \Yii::$app->db
                ->createCommand()
                ->delete(QuarantineFocus::tableName(), $removeCondition)
                ->execute();

            if (!empty($this->focuses) && is_array($this->focuses)) {
                foreach ($this->focuses as $focusData) {
                    $id_focus = ArrayHelper::remove($focusData, 'id');
                    $geometry = ArrayHelper::remove($focusData, 'geometry');
                    if ($id_focus !== null) {
                        $focusModel = QuarantineFocus::findOne(['id' => $id_focus]);
                        if ($focusModel === null) {
                            $this->addError('focuses', 'Не найден эпизоотический очаг с ID ' . $id_focus);
                            $transaction->rollBack();

                            return false;
                        }
                    } else {
                        $focusModel = new QuarantineFocus([
                            'id_quarantine' => $this->quarantine->id,
                        ]);
                    }
                    $focusModel->load($focusData, '');
                    if ($focusModel->id_locality != $id_locality) {
                        $focusModel->id_locality = $id_locality;
                    }
                    $focusModel->coords = $geometry;
                    if (!$focusModel->save()) {
                        $this->addErrors($focusModel->getErrors());
                        $transaction->rollBack();

                        return false;
                    }
                }
            }
            $transaction->commit();

            return true;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * @return bool
     */
    public function finish($endDate = '')
    {

        if (!empty($this->quarantine->fact_end_date)) {
            $this->addError('fact_end_date', 'Дата окончания карантина уже установлена');
            return false;
        }

        $this->quarantine->fact_end_date = date('Y-m-d');
        $this->quarantine->end_date = date('Y-m-d');

        if (!empty($endDate)) {
            $this->quarantine->fact_end_date = date('Y-m-d', strtotime($endDate));
            $this->quarantine->end_date = date('Y-m-d', strtotime($endDate));
        }

        if ($this->quarantine->save()) {
            return true;
        }

        $this->addErrors($this->quarantine->getErrors());
        return false;
    }

    /**
     * @return array
     */
    public function prepareOutput()
    {
        $this->quarantine->refresh();

        $response = $this->quarantine->toArray(['*'], ['disease', 'files']);
        $response['localities'] = [];
        foreach ($this->quarantine->localities as $locality) {
            $data = $locality->toArray(['*'], ['geometry']);
            unset($data['coords']);
            $response['localities'][] = $data;
        }
        $response['focuses'] = [];
        foreach ($this->quarantine->focuses as $focus) {
            $data = $focus->toArray(['*'], ['geometry', 'pet']);
            unset($data['coords']);
            $response['focuses'][] = $data;
        }

        return $response;
    }

    /**
     * @param string                          $attribute is the name of the attribute to be validated
     * @param array                           $params    contains the value of [[params]] that you specify when declaring the inline validation rule
     * @param \yii\validators\InlineValidator $validator is a reference to related [[InlineValidator]] object (since 2.0.11)
     * @see \yii\validators\InlineValidator
     */
    public function validateEndDate($attribute, $params, $validator)
    {
        if ($this->hasErrors('start_date') || $this->hasErrors('end_date')) {
            // не будем проводить дальнейшую валидацию
            return;
        }

        try {
            $dateStart = date_create_from_format('Y-m-d', $this->start_date);
        } catch (\Throwable $e) {
            $dateStart = false;
        }
        if ($dateStart === false) {
            $this->addError('start_date', 'Некорректная дата начала карантина');
            return;
        }
        try {
            $dateEnd = date_create_from_format('Y-m-d', $this->end_date);
        } catch (\Throwable $e) {
            $dateEnd = false;
        }
        if ($dateEnd === false) {
            $this->addError('end_date', 'Некорректная плановая дата окончания карантина');
            return;
        }
/*
        if ($dateEnd < $dateStart) {
            $this->addError('end_date', 'Плановая дата окончания карантина не может быть меньше даты начала карантина');
            return;
        }

        $dateCurrent = new \DateTime();
        if ($dateEnd < $dateCurrent) {
            $this->addError('end_date', 'Плановая дата окончания карантина не может быть меньше текущей даты');
            return;
        }
*/
    }
}
